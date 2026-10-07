<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\SortingCenter;
use App\Models\LogisticsCoverageArea;
use App\Models\LogisticsPartner;
use App\Models\Seller;
use Illuminate\Support\Collection;

class LogisticsRoutingService
{
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

    public function destinationArea(LogisticsPartner $partner, Order $order): ?LogisticsCoverageArea
    {
        [$province, $city, $region] = $this->destinationParts($order);
        if (! $city) {
            return null;
        }

        return $partner->coverageAreas->first(
            fn (LogisticsCoverageArea $area) => $this->areaMatches($area, $province, $city, $region)
        );
    }

    public function originCenter(LogisticsPartner $partner, Seller $seller): ?SortingCenter
    {
        $areas = $partner->coverageAreas()->with('sortingCenter')->get();
        $matched = $areas->first(fn (LogisticsCoverageArea $area) => $this->areaMatches(
            $area,
            (string) $seller->province_name,
            (string) $seller->municipality_name,
            null,
        ));

        if (! $matched) {
            return null;
        }

        if ($matched->sortingCenter?->status === 'active') {
            return $matched->sortingCenter;
        }

        // Legacy partner coverage created before branch assignment belongs to the main active center.
        return $partner->sortingCenters()->where('status', 'active')->where('is_main', true)->first();
    }

    public function trackingCode(Order $order): string
    {
        if ($order->tracking_code) {
            return $order->tracking_code;
        }

        $date = ($order->created_at ?? now())->format('Ymd');
        return 'SHP-'.$date.'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }

    private function destinationParts(Order $order): array
    {
        $province = trim((string) $order->delivery_province_name);
        $city = trim((string) $order->delivery_municipality_name);
        $region = trim((string) $order->delivery_region_name);

        if ($province && $city) {
            return [$province, $city, $region ?: null];
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', (string) $order->delivery_address))));
        if (count($parts) < 2) {
            return [null, null, null];
        }

        return [$parts[count($parts) - 1], $parts[count($parts) - 2], null];
    }

    private function areaMatches(LogisticsCoverageArea $area, ?string $province, string $city, ?string $region): bool
    {
        if (! in_array($area->area_type, ['province', 'region'], true)) {
            return false;
        }

        $cities = collect(explode('|', trim((string) $area->cities)))
            ->map(fn ($value) => trim($value))
            ->filter();
        $cityAllowed = $cities->isEmpty()
            || $cities->contains(fn ($value) => strcasecmp($value, 'ALL') === 0)
            || $cities->contains(fn ($value) => strcasecmp($value, $city) === 0);

        if (! $cityAllowed) {
            return false;
        }

        if ($area->area_type === 'province') {
            return $province && strcasecmp(trim($area->area_name), trim($province)) === 0;
        }

        // Region coverage uses the structured region snapshot when available. For old Orders,
        // an explicitly enumerated city list is enough to preserve compatibility without guessing.
        // NCR and similar PSGC regions may be stored in the province snapshot because they have no province level.
        if ($region && strcasecmp(trim($area->area_name), trim($region)) === 0) {
            return true;
        }
        if (! $region && $province && strcasecmp(trim($area->area_name), trim($province)) === 0) {
            return true;
        }

        // Legacy Orders may not have a region snapshot. An explicit city list can still prove coverage,
        // but an unqualified ALL cannot safely infer which region the Order belongs to.
        return ! $region
            && ! $cities->isEmpty()
            && ! $cities->contains(fn ($value) => strcasecmp($value, 'ALL') === 0);
    }
}
