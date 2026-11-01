<?php

namespace App\Services;

use App\Models\Logistics\Delivery;
use App\Models\Logistics\SortingCenter;

/**
 * Read-only route explanation shared by Web and the Mobile API.
 * Actual dispatch/receiving remains validated by LogisticsRoutingService,
 * manifests, scan permissions, and physical handoff. No auto-routing.
 */
class LogisticsJourneyService
{
    public function __construct(private readonly LogisticsRoutingService $routing,
        private readonly ConnectedLogisticsRoutePlanner $connections)
    {
    }

    /** @return array<string, mixed> */
    public function preview(Delivery $delivery): array
    {
        $delivery->loadMissing('currentSortingCenter', 'destinationSortingCenter', 'originSortingCenter');
        $current = $delivery->currentSortingCenter;
        $destination = $delivery->destinationSortingCenter;
        $origin = $delivery->originSortingCenter;

        $start = $current ?: $origin;
        $label = $this->routeType($start, $destination);
        $plan = $this->routing->transferPlan($delivery);
        $complete = (bool) ($plan['complete'] ?? false);
        $valid = (bool) ($plan['valid'] ?? false);
        $reason = $plan['reason'] ?? null;

        $stops = [];
        foreach ([$start, $current] as $center) {
            $this->appendCenter($stops, $center);
        }
        if ($valid && ! $complete) {
            $next = $plan['next'] ?? null;
            $this->appendCenter($stops, $next);
            if ($next && $destination) {
                // This is a *preview* of the current required path, not a
                // reservation, assignment, or guaranteed physical transport.
                $destHub = $destination->parent_id ? $this->activeParent($destination) : null;
                if ($next->type === 'station_hub' && $next->id !== $destination->id) {
                    $this->appendCenter($stops, $this->activeParent($next));
                }
                if ($next->type === 'province_hub' && $destHub && $next->id !== $destHub->id) {
                    $this->appendCenter($stops, $destHub);
                }
                $this->appendCenter($stops, $destination);
            }
        } elseif ($valid && $complete) {
            $this->appendCenter($stops, $destination);
        }

        $state = $complete ? 'destination_reached' : ($valid ? 'planned' : 'not_ready');
        $note = match ($state) {
            'destination_reached' => 'Parcel is at the destination station. Last-mile assignment is handled separately.',
            'planned' => ($plan['leg'] ?? '') === 'verified_direct_station_road'
                ? 'Verified direct branch ROAD link. Manifest, assigned road driver, physical scans and destination receipt are still required.'
                : 'Required next transfer under the manifest rules. Vehicle/driver or carrier, verified scans and destination receipt are still required.',
            default => $reason ?: 'Route is awaiting an assigned active origin and destination sorting center.',
        };
        if ($label === 'Cross-province') {
            $note .= ' If this is an inter-island journey, a real gateway and transport connection must be configured before dispatch; passing through Visayas is not always required.';
        }

        $network = ($start && $destination)
            ? $this->connections->find((int)$delivery->logistics_partner_id,(int)$start->id,(int)$destination->id)
            : ['status'=>'unavailable','stops'=>[],'legs'=>[],'note'=>'Sorting centers not assigned.'];

        return [
            'network_path' => $network,
            'status' => $state,
            'route_type' => $label,
            'next_hop' => $valid && ! $complete ? ($plan['next']?->name) : null,
            'next_leg' => $valid && ! $complete ? ($plan['leg'] ?? null) : null,
            'stops' => $stops,
            'note' => $note,
            'dispatchable' => $valid && ! $complete,
            'route_policy' => ($plan['leg'] ?? '') === 'verified_direct_station_road' ? 'verified_direct_branch' : 'standard_custody',
        ];
    }

    private function routeType(?SortingCenter $origin, ?SortingCenter $destination): string
    {
        if (! $origin || ! $destination) return 'Unassigned';
        $aProvince = mb_strtolower(trim((string) $origin->province));
        $bProvince = mb_strtolower(trim((string) $destination->province));
        $aTown = mb_strtolower(trim((string) $origin->municipality));
        $bTown = mb_strtolower(trim((string) $destination->municipality));
        if ($aProvince === '' || $bProvince === '') return 'Unclassified';
        if ($aProvince !== $bProvince) return 'Cross-province';
        if ($aTown !== '' && $bTown !== '' && $aTown === $bTown) return 'Same municipality';
        return 'Within province';
    }

    private function activeParent(SortingCenter $center): ?SortingCenter
    {
        if (! $center->parent_id) return null;
        return SortingCenter::query()
            ->whereKey($center->parent_id)
            ->where('logistics_partner_id', $center->logistics_partner_id)
            ->where('type', 'province_hub')
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->first();
    }

    /** @param array<int, array{id:int,name:string,type:string}> $stops */
    private function appendCenter(array &$stops, ?SortingCenter $center): void
    {
        if (! $center || (count($stops) && $stops[count($stops)-1]['id'] === (int) $center->id)) return;
        $stops[] = ['id' => (int) $center->id, 'name' => (string) $center->name,
            'type' => (string) $center->type];
    }
}
