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
    public function __construct(private readonly PhilippineDeliveryCoverage $coverage)
    {
    }

    public function eligiblePartners(Order $order): Collection
    {
        return LogisticsPartner::query()
            ->whereHas('user', fn ($query) => $query->where('status', 'approved'))
            ->with(['user', 'coverageAreas.sortingCenter', 'sortingCenters'])
            ->orderBy('company_name')
            ->get()
            ->filter(fn (LogisticsPartner $partner) => $this->destinationArea($partner, $order) !== null
                && $order->seller
                && $this->originCenter($partner, $order->seller) !== null)
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

    public function destinationArea(LogisticsPartner $partner, Order $order): ?LogisticsCoverageArea
    {
        return $partner->coverageAreas->first(
            fn (LogisticsCoverageArea $area) => $this->coverage->coversOrder($area, $order)
                && $this->coverage->availableForPartner($area, $partner)
        );
    }

    public function originCenter(LogisticsPartner $partner, Seller $seller): ?SortingCenter
    {
        $areas = $partner->coverageAreas()->with('sortingCenter')->get();
        $matched = $areas->first(fn (LogisticsCoverageArea $area) => $this->coverage->matches(
            $area,
            $seller->province_name,
            (string) $seller->municipality_name,
            $seller->region_name,
        ) && $this->coverage->availableForPartner($area, $partner));

        if (! $matched) {
            return null;
        }

        // A matching inactive assigned branch must never reroute to the main hub.
        // Only legacy, unassigned coverage belongs to the active main hub.
        return $matched->sorting_center_id !== null
            ? $matched->sortingCenter
            : $partner->sortingCenters()->where('status', 'active')->where('is_main', true)->first();
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

            return [
                'valid' => true,
                'complete' => false,
                'next' => $destinationHub,
                'leg' => 'province_hub_to_province_hub',
                'reason' => null,
                'current' => $current,
                'destination' => $destination,
            ];
        }

        return $this->invalidTransferPlan('Unsupported sorting-center type for parcel transfer.', $current, $destination);
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