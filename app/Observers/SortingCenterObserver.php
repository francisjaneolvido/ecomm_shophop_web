<?php

namespace App\Observers;

use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Facades\DB;

class SortingCenterObserver
{
    /**
     * Every city has its own station hub, so a new station hub covers the city in its own address.
     * Province hubs only move parcels between hubs and never deliver to a Buyer, so they get no coverage.
     */
    public function created(SortingCenter $center): void
    {
        if ($center->type !== 'station_hub' || ! filled($center->municipality)) {
            return;
        }

        // Respect coverage that was already saved for this branch (never create duplicates).
        if (DB::table('logistics_coverage_areas')->where('sorting_center_id', $center->id)->exists()) {
            return;
        }

        $province = trim((string) $center->province);
        // NCR has no province, so it is covered at region level.
        $areaType = $province !== '' ? 'province' : 'region';
        $areaName = $province !== '' ? $province : trim((string) $center->region);

        if ($areaName === '') {
            return;
        }

        DB::table('logistics_coverage_areas')->insert([
            'logistics_partner_id' => $center->logistics_partner_id,
            'sorting_center_id' => $center->id,
            'area_type' => $areaType,
            'area_name' => $areaName,
            'cities' => trim((string) $center->municipality),
            'created_at' => now(),
        ]);
    }
}