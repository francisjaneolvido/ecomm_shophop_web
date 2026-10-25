<?php

namespace App\Services;

use App\Models\Logistics\FleetVehicle;
use App\Models\Logistics\LinehaulDriver;
use App\Models\Logistics\LinehaulTrip;
use App\Models\Logistics\ParcelManifest;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Finance\LinehaulDriverEarning;
use App\Models\Finance\MarketplaceRateSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LinehaulService
{
    public function assignManifest(
        ParcelManifest $manifest,
        int $driverId,
        int $vehicleId,
        ?int $assignedByUserId = null,
        ?string $note = null
    ): LinehaulTrip {
        return DB::transaction(function () use ($manifest, $driverId, $vehicleId, $assignedByUserId, $note) {
            $locked = ParcelManifest::whereKey($manifest->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ParcelManifest::READY || $locked->linehaul_trip_id) {
                throw ValidationException::withMessages([
                    'linehaul' => 'Manifest must be fully loaded and unassigned before a Linehaul Trip can be created.',
                ]);
            }

            $driver = LinehaulDriver::whereKey($driverId)
                ->where('logistics_partner_id', $locked->logistics_partner_id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            $vehicle = FleetVehicle::whereKey($vehicleId)
                ->where('logistics_partner_id', $locked->logistics_partner_id)
                ->where('status', FleetVehicle::AVAILABLE)
                ->lockForUpdate()
                ->first();

            if (! $driver) {
                throw ValidationException::withMessages(['driver_id' => 'Selected Linehaul Driver is not active.']);
            }
            if (! $vehicle) {
                throw ValidationException::withMessages(['vehicle_id' => 'Selected vehicle is not available.']);
            }

            $from = $locked->fromCenter()->firstOrFail();
            $requiredHomeHubId = $from->type === 'province_hub' ? $from->id : $from->parent_id;
            if ($requiredHomeHubId && (int) $vehicle->home_hub_id !== (int) $requiredHomeHubId) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'Selected vehicle is assigned to a different Province Hub.',
                ]);
            }

            $activeStatuses = [LinehaulTrip::SCHEDULED, LinehaulTrip::IN_TRANSIT, LinehaulTrip::ARRIVED];
            if (LinehaulTrip::where('driver_id', $driver->id)->whereIn('status', $activeStatuses)->exists()) {
                throw ValidationException::withMessages(['driver_id' => 'Driver already has an active Linehaul Trip.']);
            }
            if (LinehaulTrip::where('vehicle_id', $vehicle->id)->whereIn('status', $activeStatuses)->exists()) {
                throw ValidationException::withMessages(['vehicle_id' => 'Vehicle already has an active Linehaul Trip.']);
            }

            $expected = DB::table('parcel_manifest_items')->where('manifest_id', $locked->id)->count();
            $loaded = DB::table('parcel_manifest_items')->where('manifest_id', $locked->id)->whereNotNull('loaded_at')->count();
            if ($expected < 1 || $loaded !== $expected) {
                throw ValidationException::withMessages(['linehaul' => 'The origin center must confirm the complete physical manifest load before assigning transport.']);
            }

            $trip = LinehaulTrip::create([
                'code' => $this->tripCode((int) $locked->logistics_partner_id),
                'logistics_partner_id' => $locked->logistics_partner_id,
                'from_sorting_center_id' => $locked->from_sorting_center_id,
                'to_sorting_center_id' => $locked->to_sorting_center_id,
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'assigned_by_user_id' => $assignedByUserId,
                'status' => LinehaulTrip::SCHEDULED,
                'note' => $note,
            ]);

            $locked->forceFill(['linehaul_trip_id' => $trip->id])->save();

            return $trip->load(['fromCenter', 'toCenter', 'vehicle', 'driver', 'manifests']);
        }, 3);
    }

    public function previewForDriver(LinehaulDriver $driver, string $manifestCode): array
    {
        $manifest = $this->driverManifest($driver, $manifestCode);
        $trip = $manifest->linehaulTrip;

        $counts = DB::table('parcel_manifest_items')
            ->where('manifest_id', $manifest->id)
            ->selectRaw('COUNT(*) as expected, COUNT(loaded_at) as loaded, COUNT(received_at) as received')
            ->first();

        $action = $trip->status === LinehaulTrip::SCHEDULED ? 'depart' : 'none';

        return [
            'manifest_id' => $manifest->id,
            'manifest_code' => $manifest->code,
            'trip_id' => $trip->id,
            'trip_code' => $trip->code,
            'trip_status' => $trip->status,
            'action' => $action,
            'from' => $trip->fromCenter?->name,
            'to' => $trip->toCenter?->name,
            'vehicle_code' => $trip->vehicle?->code,
            'plate_number' => $trip->vehicle?->plate_number,
            'vehicle_type' => $trip->vehicle?->vehicle_type,
            'driver' => $driver->name,
            'expected' => (int) ($counts->expected ?? 0),
            'loaded' => (int) ($counts->loaded ?? 0),
            'received' => (int) ($counts->received ?? 0),
            'confirm_label' => $action === 'depart'
                ? 'Accept load & depart'
                : ($trip->status === LinehaulTrip::IN_TRANSIT
                    ? 'Waiting for destination Manifest scan'
                    : 'No action required'),
        ];
    }

    public function depart(LinehaulDriver $driver, string $manifestCode): array
    {
        return DB::transaction(function () use ($driver, $manifestCode) {
            $manifest = ParcelManifest::with(['linehaulTrip.vehicle', 'linehaulTrip.fromCenter', 'linehaulTrip.toCenter'])
                ->where('logistics_partner_id', $driver->logistics_partner_id)
                ->where('code', strtoupper(trim($manifestCode)))
                ->lockForUpdate()
                ->first();

            if (! $manifest || ! $manifest->linehaul_trip_id) {
                throw ValidationException::withMessages(['manifest_code' => 'No assigned Linehaul Trip was found for this manifest.']);
            }

            $trip = LinehaulTrip::whereKey($manifest->linehaul_trip_id)->lockForUpdate()->firstOrFail();
            if ((int) $trip->driver_id !== (int) $driver->id || $trip->status !== LinehaulTrip::SCHEDULED) {
                throw ValidationException::withMessages(['manifest_code' => 'This manifest is not awaiting departure by your Driver account.']);
            }

            $items = DB::table('parcel_manifest_items')->where('manifest_id', $manifest->id)->lockForUpdate()->get();
            if ($items->isEmpty() || $items->contains(fn ($item) => ! $item->loaded_at)) {
                throw ValidationException::withMessages(['manifest_code' => 'Manifest loading is incomplete. The origin Branch/Hub must confirm the complete physical batch before departure.']);
            }

            $now = now();
            $ownerUserId = (int) ($driver->partner()->value('user_id') ?? 0);
            if ($ownerUserId <= 0) {
                throw ValidationException::withMessages(['manifest_code' => 'Logistics owner account is unavailable.']);
            }

            foreach ($items as $item) {
                if (ParcelTransfer::where('delivery_id', $item->delivery_id)->where('status', ParcelTransfer::IN_TRANSIT)->exists()) {
                    throw ValidationException::withMessages(['manifest_code' => 'One or more parcels already have an active transfer. Refresh the trip.']);
                }

                $transfer = ParcelTransfer::create([
                    'delivery_id' => $item->delivery_id,
                    'manifest_id' => $manifest->id,
                    'from_sorting_center_id' => $manifest->from_sorting_center_id,
                    'to_sorting_center_id' => $manifest->to_sorting_center_id,
                    'status' => ParcelTransfer::IN_TRANSIT,
                    'transit_rider_id' => null,
                    'linehaul_trip_id' => $trip->id,
                    'departed_at' => $now,
                    'dispatched_at' => $now,
                ]);

                DB::table('parcel_scan_events')->insert([
                    'delivery_id' => $item->delivery_id,
                    'sorting_center_id' => $manifest->from_sorting_center_id,
                    'operator_user_id' => $ownerUserId,
                    'operator_staff_id' => null,
                    'operator_rider_id' => null,
                    'operator_linehaul_driver_id' => $driver->id,
                    'parcel_transfer_id' => $transfer->id,
                    'event_type' => 'linehaul_departed',
                    'scanned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $manifest->forceFill([
                'status' => ParcelManifest::DISPATCHED,
                'dispatched_at' => $now,
            ])->save();

            $trip->forceFill([
                'status' => LinehaulTrip::IN_TRANSIT,
                'departed_at' => $now,
            ])->save();

            FleetVehicle::whereKey($trip->vehicle_id)->update(['status' => FleetVehicle::IN_TRANSIT]);

            return $this->previewForDriver($driver, $manifest->code);
        }, 3);
    }

    public function completeTripIfReceived(int $tripId): void
    {
        $trip = LinehaulTrip::with('manifests')->whereKey($tripId)->first();
        if (! $trip || ! in_array($trip->status, [LinehaulTrip::ARRIVED, LinehaulTrip::IN_TRANSIT], true)) {
            return;
        }

        $total = ParcelTransfer::where('linehaul_trip_id', $trip->id)->count();
        $received = ParcelTransfer::where('linehaul_trip_id', $trip->id)->where('status', ParcelTransfer::RECEIVED)->count();
        if ($total < 1 || $received < $total) {
            return;
        }

        DB::transaction(function () use ($trip) {
            $locked = LinehaulTrip::whereKey($trip->id)->lockForUpdate()->first();
            if (! $locked || $locked->status === LinehaulTrip::COMPLETED) {
                return;
            }

            $locked->forceFill([
                'status' => LinehaulTrip::COMPLETED,
                'completed_at' => now(),
                'arrived_at' => $locked->arrived_at ?: now(),
            ])->save();

            ParcelManifest::where('linehaul_trip_id', $locked->id)
                ->where('status', ParcelManifest::DISPATCHED)
                ->update(['status' => ParcelManifest::RECEIVED, 'updated_at' => now()]);

            FleetVehicle::whereKey($locked->vehicle_id)
                ->where('status', FleetVehicle::IN_TRANSIT)
                ->update(['status' => FleetVehicle::AVAILABLE]);

            $from = $locked->fromCenter()->first();
            $to = $locked->toCenter()->first();
            $rates = MarketplaceRateSetting::current();
            $isHubToHub = $from?->type === 'province_hub' && $to?->type === 'province_hub';
            $driverFee = $isHubToHub
                ? (float) $rates->linehaul_hub_hub_driver_fee
                : (float) $rates->linehaul_branch_hub_driver_fee;

            if ($driverFee > 0) {
                LinehaulDriverEarning::firstOrCreate(
                    ['linehaul_trip_id' => $locked->id],
                    [
                        'logistics_partner_id' => (int) $locked->logistics_partner_id,
                        'driver_id' => (int) $locked->driver_id,
                        'amount' => round($driverFee, 2),
                        'status' => 'available',
                        'available_at' => now(),
                    ]
                );
            }
        }, 3);
    }

    private function driverManifest(LinehaulDriver $driver, string $manifestCode): ParcelManifest
    {
        $manifest = ParcelManifest::with([
            'linehaulTrip.vehicle', 'linehaulTrip.driver', 'linehaulTrip.fromCenter', 'linehaulTrip.toCenter',
        ])
            ->where('logistics_partner_id', $driver->logistics_partner_id)
            ->where('code', strtoupper(trim($manifestCode)))
            ->first();

        if (! $manifest || ! $manifest->linehaulTrip || (int) $manifest->linehaulTrip->driver_id !== (int) $driver->id) {
            throw ValidationException::withMessages(['manifest_code' => 'This manifest is not assigned to your Linehaul Driver account.']);
        }

        return $manifest;
    }

    private function tripCode(int $partnerId): string
    {
        $prefix = 'LH-'.now()->format('Ymd').'-'.str_pad((string) $partnerId, 3, '0', STR_PAD_LEFT);
        $number = LinehaulTrip::where('code', 'like', $prefix.'-%')->count() + 1;

        do {
            $code = $prefix.'-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
            $number++;
        } while (LinehaulTrip::where('code', $code)->exists());

        return $code;
    }
}
