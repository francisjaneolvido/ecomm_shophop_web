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

    public function trackingCode(Order $order): string
    {
        if ($order->tracking_code) {
            return $order->tracking_code;
        }

        $date = ($order->created_at ?? now())->format('Ymd');
        return 'SHP-'.$date.'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }

}
