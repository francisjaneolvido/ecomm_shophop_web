<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;
use App\Models\Seller;
use Illuminate\Support\Collection;

class LogisticsRoutingService
{
    public function __construct(private readonly PhilippineDeliveryCoverage $coverage,
        private readonly ConnectedLogisticsRoutePlanner $connections)
    {
    }

    public function eligiblePartners(Order $order): Collection
    {
        return LogisticsPartner::query()
            ->whereHas('user', fn ($query) => $query->where('status', 'approved'))
            // Do not eagerly load all nationwide branch rows into every checkout.
            // The route eligibility check retrieves only the centers it needs.
            ->with(['user', 'coverageAreas.sortingCenter'])
            ->orderBy('company_name')
            ->get()
            ->filter(fn (LogisticsPartner $partner) => $order->seller
                && $this->checkoutRouteAvailable($partner, $order->seller, $order))
            ->values();
    }

    /**
     * Partners that can pick up from this Seller and deliver to a destination, before any Order exists.
     * Used by Checkout so the Buyer only sees couriers that really cover the address.
     *
     * @param array<string, mixed> $destination delivery_* attributes (region/province/municipality names, address)
     */
    public function eligiblePartnersFor(Seller $seller, array $destination): Collection
    {
        // An unsaved Order only carries the destination fields the coverage rule reads.
        $draft = new Order();
        $draft->forceFill($destination);
        $draft->setRelation('seller', $seller);

        return $this->eligiblePartners($draft);
    }

    /**
     * Checkout authorization for one Seller and one destination address.
     *
     * Coverage alone does not mean that parcels can move between branches.
     * This mirrors the safe physical route hierarchy used at dispatch:
     *   - same active station: local delivery, no manifest/linehaul required;
     *   - verified direct same-island station ROAD link; or
     *   - station -> parent hub -> VERIFIED directional hub route -> destination
     *     parent hub -> station. The two local branch/hub handoffs are part of
     *     normal manifest custody, not inferred road connections across islands.
     *
     * This is an availability check only. Driver/rider assignments, manifest
     * scans, real cargo bookings and final delivery remain separate safeguards.
     */
    public function checkoutRouteAvailable(LogisticsPartner $partner, Seller $seller, Order $destinationOrder): bool
    {
        $partnerId = (int) $partner->id;
        $area = $this->destinationArea($partner, $destinationOrder);
        if (! $area) {
            return false;
        }

        $origin = $this->originCenter($partner, $seller);
        // Legacy coverage without a station may resolve to the main facility;
        // never treat a province hub as a final-mile destination station.
        $destination = $area->sorting_center_id
            ? ($area->sortingCenter ?: SortingCenter::find($area->sorting_center_id))
            : $partner->mainSortingCenter()->first();
        if (! $this->isActiveNetworkCenter($origin, $partnerId)
            || ! $this->isActiveNetworkCenter($destination, $partnerId)
            || $destination->type !== 'station_hub') {
            return false;
        }

        // Actual local pickup and last-mile delivery still require a rider.
        // Matching stations do not need transport routes or a parent hub.
        if ((int) $origin->id === (int) $destination->id) {
            return true;
        }

        $destinationHub = $this->activeParentHub($partnerId, $destination->parent_id);
        if (! $destinationHub) {
            return false;
        }

        if ($origin->type === 'station_hub') {
            // A direct branch shortcut may bypass hubs only if explicitly
            // verified, directional, ROAD and in the same known island group.
            // This mirrors transferPlan(): a missing origin parent is relevant
            // only to the standard hub custody route, not to a direct link.
            if ($this->verifiedDirectStationRoadFor($partnerId, $origin, $destination)) {
                return true;
            }

            $originHub = $this->activeParentHub($partnerId, $origin->parent_id);
            if (! $originHub) {
                return false;
            }
        } elseif ($origin->type === 'province_hub') {
            $originHub = $origin;
        } else {
            return false;
        }

        if ((int) $originHub->id === (int) $destinationHub->id) {
            return true;
        }

        $connected = $this->connections->find(
            $partnerId,
            (int) $originHub->id,
            (int) $destinationHub->id,
            true // Hub-only; inter-hub legs must be verified transport links.
        );
        return ($connected['status'] ?? '') === 'connected' && ! empty($connected['legs']);
    }

    private function activeParentHub(int $partnerId, ?int $parentId): ?SortingCenter
    {
        if (! $parentId) {
            return null;
        }
        return SortingCenter::query()
            ->whereKey($parentId)
            ->where('logistics_partner_id', $partnerId)
            ->where('type', 'province_hub')
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->first();
    }

    private function isActiveNetworkCenter(?SortingCenter $center, int $partnerId): bool
    {
        return $center !== null
            && (int) $center->logistics_partner_id === $partnerId
            && $center->status === 'active'
            && $center->archived_at === null;
    }

    private function verifiedDirectStationRoadFor(int $partnerId, SortingCenter $from, SortingCenter $to): bool
    {
        if ($from->type !== 'station_hub' || $to->type !== 'station_hub') {
            return false;
        }
        $islands = app(PhilippineIslandGroup::class);
        if (! $islands->forCenter($from) || ! $islands->forCenter($to)
            || $islands->isCrossGroup($from, $to)) {
            return false;
        }
        return \App\Models\Logistics\RouteLink::query()
            ->where('logistics_partner_id', $partnerId)
            ->where('from_center_id', $from->id)
            ->where('to_center_id', $to->id)
            ->where('mode', 'road')
            ->where('status', 'verified')
            ->whereNotNull('verified_at')
            ->exists();
    }

    /**
     * A city-specific active branch outranks the old province-wide demo station.
     * This ONLY applies to selecting a center for NEW work; existing parcel
     * origin/destination IDs and custody records are never changed.
     */
    private function preferredArea(LogisticsPartner $partner, Collection $areas, ?string $province, ?string $city,
        ?string $region): ?LogisticsCoverageArea
    {
        if (! filled($city)) return null;
        return $areas->filter(fn (LogisticsCoverageArea $area) =>
            $this->coverage->matches($area, $province, (string) $city, $region)
            && $this->coverage->availableForPartner($area, $partner)
        )->sortByDesc(function (LogisticsCoverageArea $area) {
            $cityNames = collect(preg_split('/[|,]+/', (string) $area->cities))
                ->map(fn ($name) => trim((string) $name))->filter();
            $specific = $cityNames->isNotEmpty() && !$cityNames->contains(fn ($name) => strcasecmp($name, 'all') === 0);
            $code = (string) $area->sortingCenter?->code;
            $manual = !str_starts_with($code, 'NW-');
            $dedicated = str_starts_with($code, 'NW-C-');
            return ($specific ? 100 : 0) + ($manual ? 20 : 0) + ($dedicated ? 10 : 0);
        })->first();
    }

    public function destinationArea(LogisticsPartner $partner, Order $order): ?LogisticsCoverageArea
    {
        [$province, $city, $region] = $this->coverage->destination($order);
        return $this->preferredArea($partner, $partner->coverageAreas, $province, $city, $region);
    }

    public function originCenter(LogisticsPartner $partner, Seller $seller): ?SortingCenter
    {
        $areas = $partner->coverageAreas()->with('sortingCenter')->get();
        $matched = $this->preferredArea($partner, $areas, $seller->province_name,
            $seller->municipality_name, $seller->region_name);
        if (! $matched) return null;
        return $matched->sorting_center_id !== null
            ? $matched->sortingCenter
            : $partner->sortingCenters()->where('status','active')->where('is_main', true)->first();
    }


    /**
     * Build the only valid next physical hop for a sorted parcel.
     *
     * Hierarchy:
     * - same station: no transfer (ready for last-mile dispatch)
     * - station -> its parent Province Hub
     * - Province Hub -> destination Province Hub (cross-province)
     * - destination Province Hub -> final destination station
     *
     * @return array{valid: bool, complete: bool, next: ?SortingCenter, leg: ?string, reason: ?string, current: ?SortingCenter, destination: ?SortingCenter}
     */
    public function transferPlan(Delivery $delivery): array
    {
        $currentId = (int) ($delivery->current_sorting_center_id ?? 0);
        $destinationId = (int) ($delivery->destination_sorting_center_id ?? 0);

        if (! $currentId || ! $destinationId) {
            return $this->invalidTransferPlan('Current and destination sorting centers must be set before transfer.');
        }

        $centers = SortingCenter::query()
            ->where('logistics_partner_id', $delivery->logistics_partner_id)
            ->whereIn('id', [$currentId, $destinationId])
            ->get()
            ->keyBy('id');

        /** @var SortingCenter|null $current */
        $current = $centers->get($currentId);
        /** @var SortingCenter|null $destination */
        $destination = $centers->get($destinationId);

        if (! $current || ! $destination) {
            return $this->invalidTransferPlan('The parcel references a sorting center outside this Logistics network.', $current, $destination);
        }

        if ($current->status !== 'active' || $current->archived_at) {
            return $this->invalidTransferPlan('The parcel is currently assigned to an inactive sorting center.', $current, $destination);
        }

        if ($destination->status !== 'active' || $destination->archived_at) {
            return $this->invalidTransferPlan('The final destination station is inactive.', $current, $destination);
        }

        if ($currentId === $destinationId) {
            return [
                'valid' => true,
                'complete' => true,
                'next' => null,
                'leg' => 'destination_station_reached',
                'reason' => null,
                'current' => $current,
                'destination' => $destination,
            ];
        }

        if ($destination->type !== 'station_hub' || ! $destination->parent_id) {
            return $this->invalidTransferPlan(
                'The final delivery destination must be an active station hub linked to a Province Hub.',
                $current,
                $destination
            );
        }

        $destinationHub = SortingCenter::query()
            ->whereKey($destination->parent_id)
            ->where('logistics_partner_id', $delivery->logistics_partner_id)
            ->where('type', 'province_hub')
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->first();

        if (! $destinationHub) {
            return $this->invalidTransferPlan('The destination station has no active Province Hub.', $current, $destination);
        }

        if ($current->type === 'station_hub') {
            // Phase 2: an explicitly VERIFIED directional road link may bypass the
            // Province Hub. No inferred city adjacency and no unverified shortcut.
            // The receiving station still requires a manifest, driver departure,
            // destination QR arrival and physical parcel receipt.
            if ($this->verifiedDirectStationRoad($delivery, $current, $destination)) {
                return [
                    'valid' => true,
                    'complete' => false,
                    'next' => $destination,
                    'leg' => 'verified_direct_station_road',
                    'reason' => null,
                    'current' => $current,
                    'destination' => $destination,
                ];
            }
            if (! $current->parent_id) {
                return $this->invalidTransferPlan('The current station has no Province Hub parent.', $current, $destination);
            }

            $originHub = SortingCenter::query()
                ->whereKey($current->parent_id)
                ->where('logistics_partner_id', $delivery->logistics_partner_id)
                ->where('type', 'province_hub')
                ->where('status', 'active')
                ->whereNull('archived_at')
                ->first();

            if (! $originHub) {
                return $this->invalidTransferPlan('The current station has no active Province Hub parent.', $current, $destination);
            }

            return [
                'valid' => true,
                'complete' => false,
                'next' => $originHub,
                'leg' => 'station_to_origin_hub',
                'reason' => null,
                'current' => $current,
                'destination' => $destination,
            ];
        }

        if ($current->type === 'province_hub') {
            if ((int) $destinationHub->id === $currentId) {
                return [
                    'valid' => true,
                    'complete' => false,
                    'next' => $destination,
                    'leg' => 'destination_hub_to_station',
                    'reason' => null,
                    'current' => $current,
                    'destination' => $destination,
                ];
            }

            // Region metadata is mandatory for responsible inter-province transport.
            $islandGroups = app(PhilippineIslandGroup::class);
            if (! $islandGroups->forCenter($current) || ! $islandGroups->forCenter($destinationHub)) {
                return $this->invalidTransferPlan('Hub region is missing or not recognized. Correct the PSGC region before planning transport.', $current, $destination);
            }
            // All Province Hub transfers require verified connected transport, never a guessed road leg.
            {
                $network = $this->connections->find(
                    (int) $delivery->logistics_partner_id,
                    $currentId,
                    (int) $destinationHub->id,
                    true // Hub-only: station branch transfer still uses existing guarded process.
                );
                if (($network['status'] ?? '') !== 'connected' || empty($network['legs'])) {
                    return $this->invalidTransferPlan(
                        'No verified connected transport path. An inter-island journey needs Sea/Air links; configure the required hubs before dispatch.',
                        $current,
                        $destination
                    );
                }
                $nextHub = SortingCenter::query()
                    ->where('logistics_partner_id', $delivery->logistics_partner_id)
                    ->where('type', 'province_hub')->where('status','active')->whereNull('archived_at')
                    ->find((int) $network['legs'][0]['to_id']);
                if (! $nextHub) {
                    return $this->invalidTransferPlan('Verified next hub became unavailable.', $current, $destination);
                }
                return [
                    'valid' => true,
                    'complete' => false,
                    'next' => $nextHub,
                    'leg' => 'province_hub_to_province_hub',
                    'reason' => null,
                    'current' => $current,
                    'destination' => $destination,
                ];
            }

        }

        return $this->invalidTransferPlan('Unsupported sorting-center type for parcel transfer.', $current, $destination);
    }

    /** No guessed local shortcut: only a verified road connection, same partner,
     * known island groups, and an active origin/destination station are eligible. */
    private function verifiedDirectStationRoad(Delivery $delivery, SortingCenter $from, SortingCenter $to): bool
    {
        if ($from->type !== 'station_hub' || $to->type !== 'station_hub') return false;
        $islands = app(PhilippineIslandGroup::class);
        if (! $islands->forCenter($from) || ! $islands->forCenter($to)
            || $islands->isCrossGroup($from, $to)) return false;

        return \App\Models\Logistics\RouteLink::query()
            ->where('logistics_partner_id', $delivery->logistics_partner_id)
            ->where('from_center_id', $from->id)
            ->where('to_center_id', $to->id)
            ->where('mode', 'road')->where('status', 'verified')
            ->whereNotNull('verified_at')->exists();
    }

    private function invalidTransferPlan(
        string $reason,
        ?SortingCenter $current = null,
        ?SortingCenter $destination = null
    ): array {
        return [
            'valid' => false,
            'complete' => false,
            'next' => null,
            'leg' => null,
            'reason' => $reason,
            'current' => $current,
            'destination' => $destination,
        ];
    }

    public function trackingCode(Order $order): string
    {
        if ($order->tracking_code) {
            return $order->tracking_code;
        }

        $date = ($order->created_at ?? now())->format('Ymd');
        return 'SHP-'.$date.'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }

}