<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\LinehaulTrip;
use App\Models\Logistics\ParcelManifest;
use App\Models\Logistics\ParcelTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManifestLoadingService
{
    public function summary(int $partnerId, int $centerId, string $manifestCode): array
    {
        $manifest = ParcelManifest::with(['fromCenter', 'toCenter', 'linehaulTrip.vehicle', 'linehaulTrip.driver'])
            ->where('logistics_partner_id', $partnerId)
            ->where('code', strtoupper(trim($manifestCode)))
            ->first();

        if (! $manifest) {
            throw ValidationException::withMessages(['manifest_code' => 'Manifest not found in your Logistics network.']);
        }

        if ((int) $manifest->from_sorting_center_id !== $centerId && (int) $manifest->to_sorting_center_id !== $centerId) {
            throw ValidationException::withMessages(['manifest_code' => 'This manifest does not belong to this center.']);
        }

        $counts = $this->counts($manifest->id);

        return [
            'id' => $manifest->id,
            'code' => $manifest->code,
            'status' => $manifest->status,
            'from' => $manifest->fromCenter?->name,
            'to' => $manifest->toCenter?->name,
            'expected' => $counts['expected'],
            'loaded' => $counts['loaded'],
            'received' => $counts['received'],
            'vehicle' => $manifest->linehaulTrip?->vehicle?->code,
            'plate_number' => $manifest->linehaulTrip?->vehicle?->plate_number,
            'driver' => $manifest->linehaulTrip?->driver?->name,
            'trip_code' => $manifest->linehaulTrip?->code,
            'trip_status' => $manifest->linehaulTrip?->status,
        ];
    }

    /**
     * Normal loading path: staff physically counts the consolidated load once,
     * then confirms the whole manifest. Individual parcel scans are reserved for
     * exception/reconciliation cases.
     */
    public function confirmBatchLoad(
        int $partnerId,
        int $centerId,
        int $manifestId,
        int $physicalCount,
        int $operatorUserId,
        ?int $staffId = null
    ): array {
        return DB::transaction(function () use ($partnerId, $centerId, $manifestId, $physicalCount, $operatorUserId, $staffId) {
            $manifest = ParcelManifest::with(['fromCenter', 'toCenter'])
                ->where('logistics_partner_id', $partnerId)
                ->whereKey($manifestId)
                ->where('from_sorting_center_id', $centerId)
                ->whereIn('status', [ParcelManifest::PREPARED, ParcelManifest::LOADING])
                ->lockForUpdate()
                ->first();

            if (! $manifest) {
                throw ValidationException::withMessages(['manifest' => 'Manifest is no longer open for loading.']);
            }

            $items = DB::table('parcel_manifest_items')
                ->where('manifest_id', $manifest->id)
                ->lockForUpdate()
                ->get();

            $expected = $items->count();
            if ($expected < 1) {
                throw ValidationException::withMessages(['manifest' => 'Manifest has no parcels.']);
            }
            if ($physicalCount !== $expected) {
                throw ValidationException::withMessages([
                    'physical_count' => "Physical count is {$physicalCount}, but this manifest expects {$expected}. Use individual Parcel QR receiving/loading for exception reconciliation before dispatch.",
                ]);
            }

            $deliveryIds = $items->pluck('delivery_id')->map(fn ($id) => (int) $id)->all();
            $deliveries = Delivery::with('order')
                ->where('logistics_partner_id', $partnerId)
                ->whereIn('id', $deliveryIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($deliveries->count() !== $expected) {
                throw ValidationException::withMessages(['manifest' => 'One or more manifest parcels no longer exist in this Logistics network.']);
            }

            foreach ($items as $item) {
                $delivery = $deliveries->get((int) $item->delivery_id);
                if (! $delivery) {
                    throw ValidationException::withMessages(['manifest' => 'Manifest parcel data changed. Refresh before loading.']);
                }
                $this->assertLoadableDelivery($delivery, $centerId);
            }

            $now = now();
            $newlyLoaded = $items->filter(fn ($item) => ! $item->loaded_at);

            DB::table('parcel_manifest_items')
                ->where('manifest_id', $manifest->id)
                ->whereNull('loaded_at')
                ->update([
                    'loaded_at' => $now,
                    'loaded_by_user_id' => $operatorUserId,
                    'loaded_by_staff_id' => $staffId,
                    'updated_at' => $now,
                ]);

            foreach ($newlyLoaded as $item) {
                DB::table('parcel_scan_events')->insert([
                    'delivery_id' => $item->delivery_id,
                    'sorting_center_id' => $centerId,
                    'operator_user_id' => $operatorUserId,
                    'operator_staff_id' => $staffId,
                    'operator_rider_id' => null,
                    'operator_linehaul_driver_id' => null,
                    'parcel_transfer_id' => null,
                    'event_type' => 'manifest_loaded',
                    'scanned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $manifest->forceFill(['status' => ParcelManifest::READY])->save();

            return [
                'manifest_id' => $manifest->id,
                'manifest_code' => $manifest->code,
                'expected' => $expected,
                'loaded' => $expected,
                'missing' => 0,
                'manifest_status' => $manifest->status,
                'ready' => true,
            ];
        }, 3);
    }

    /** Destination-side manifest preview. Preview is read-only. */
    public function inspectInbound(int $partnerId, int $centerId, string $manifestCode): array
    {
        $manifest = ParcelManifest::with(['fromCenter', 'toCenter', 'linehaulTrip.vehicle', 'linehaulTrip.driver'])
            ->where('logistics_partner_id', $partnerId)
            ->where('code', strtoupper(trim($manifestCode)))
            ->first();

        if (! $manifest || (int) $manifest->to_sorting_center_id !== $centerId || ! $manifest->linehaulTrip) {
            throw ValidationException::withMessages(['manifest_code' => 'No inbound Linehaul manifest is assigned to this destination center.']);
        }

        $trip = $manifest->linehaulTrip;
        if (! in_array($trip->status, [LinehaulTrip::IN_TRANSIT, LinehaulTrip::ARRIVED, LinehaulTrip::COMPLETED], true)) {
            throw ValidationException::withMessages(['manifest_code' => 'This manifest has not departed its origin center yet.']);
        }

        $counts = $this->counts($manifest->id);
        $transfers = ParcelTransfer::where('manifest_id', $manifest->id)
            ->where('linehaul_trip_id', $trip->id)
            ->count();

        if ($transfers !== $counts['expected']) {
            throw ValidationException::withMessages(['manifest_code' => 'Manifest transfer records are incomplete. Refresh the origin dispatch before receiving.']);
        }

        return [
            'manifest_id' => $manifest->id,
            'manifest_code' => $manifest->code,
            'manifest_status' => $manifest->status,
            'trip_id' => $trip->id,
            'trip_code' => $trip->code,
            'trip_status' => $trip->status,
            'from' => $manifest->fromCenter?->name,
            'to' => $manifest->toCenter?->name,
            'vehicle_code' => $trip->vehicle?->code,
            'plate_number' => $trip->vehicle?->plate_number,
            'driver' => $trip->driver?->name,
            'expected' => $counts['expected'],
            'loaded' => $counts['loaded'],
            'received' => $counts['received'],
            'arrival_acknowledged' => in_array($trip->status, [LinehaulTrip::ARRIVED, LinehaulTrip::COMPLETED], true),
            'can_acknowledge_arrival' => $trip->status === LinehaulTrip::IN_TRANSIT,
            'can_receive_all' => $trip->status === LinehaulTrip::ARRIVED && $counts['received'] < $counts['expected'],
            'completed' => $trip->status === LinehaulTrip::COMPLETED,
        ];
    }

    /** Destination center, not Driver, is the authority that acknowledges arrival. */
    public function acknowledgeArrival(
        int $partnerId,
        int $centerId,
        string $manifestCode,
        int $operatorUserId,
        ?int $staffId = null
    ): array {
        DB::transaction(function () use ($partnerId, $centerId, $manifestCode, $operatorUserId, $staffId) {
            $manifest = ParcelManifest::where('logistics_partner_id', $partnerId)
                ->where('code', strtoupper(trim($manifestCode)))
                ->where('to_sorting_center_id', $centerId)
                ->lockForUpdate()
                ->first();

            if (! $manifest || ! $manifest->linehaul_trip_id) {
                throw ValidationException::withMessages(['manifest_code' => 'Inbound manifest not found for this destination center.']);
            }

            $trip = LinehaulTrip::whereKey($manifest->linehaul_trip_id)->lockForUpdate()->firstOrFail();
            if ($trip->status === LinehaulTrip::COMPLETED || $trip->status === LinehaulTrip::ARRIVED) {
                return;
            }
            if ($trip->status !== LinehaulTrip::IN_TRANSIT) {
                throw ValidationException::withMessages(['manifest_code' => 'This Linehaul trip is not currently in transit.']);
            }

            $now = now();
            $trip->forceFill(['status' => LinehaulTrip::ARRIVED, 'arrived_at' => $now])->save();

            $transfers = ParcelTransfer::where('manifest_id', $manifest->id)
                ->where('linehaul_trip_id', $trip->id)
                ->where('status', ParcelTransfer::IN_TRANSIT)
                ->get();

            foreach ($transfers as $transfer) {
                DB::table('parcel_scan_events')->insert([
                    'delivery_id' => $transfer->delivery_id,
                    'sorting_center_id' => $centerId,
                    'operator_user_id' => $operatorUserId,
                    'operator_staff_id' => $staffId,
                    'operator_rider_id' => null,
                    'operator_linehaul_driver_id' => null,
                    'parcel_transfer_id' => $transfer->id,
                    'event_type' => 'manifest_arrived',
                    'scanned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }, 3);

        return $this->inspectInbound($partnerId, $centerId, $manifestCode);
    }

    /** Fast normal receiving path: one manifest confirmation receives the intact batch. */
    public function receiveAllIntact(
        int $partnerId,
        int $centerId,
        string $manifestCode,
        int $physicalCount,
        int $operatorUserId,
        ?int $staffId = null
    ): array {
        $tripId = DB::transaction(function () use ($partnerId, $centerId, $manifestCode, $physicalCount, $operatorUserId, $staffId) {
            $manifest = ParcelManifest::where('logistics_partner_id', $partnerId)
                ->where('code', strtoupper(trim($manifestCode)))
                ->where('to_sorting_center_id', $centerId)
                ->lockForUpdate()
                ->first();

            if (! $manifest || ! $manifest->linehaul_trip_id) {
                throw ValidationException::withMessages(['manifest_code' => 'Inbound manifest not found for this destination center.']);
            }

            $trip = LinehaulTrip::whereKey($manifest->linehaul_trip_id)->lockForUpdate()->firstOrFail();
            if ($trip->status !== LinehaulTrip::ARRIVED || ! $trip->arrived_at) {
                throw ValidationException::withMessages(['manifest_code' => 'Scan and acknowledge the Manifest QR at this destination before receiving the load.']);
            }

            $items = DB::table('parcel_manifest_items')->where('manifest_id', $manifest->id)->lockForUpdate()->get();
            $expected = $items->count();
            if ($expected < 1) {
                throw ValidationException::withMessages(['manifest_code' => 'Manifest has no parcels.']);
            }
            if ($physicalCount !== $expected) {
                throw ValidationException::withMessages([
                    'physical_count' => "Physical count is {$physicalCount}, but the manifest expects {$expected}. Do not Receive All. Use the individual Parcel QR scanner for exception reconciliation.",
                ]);
            }

            $transfers = ParcelTransfer::where('manifest_id', $manifest->id)
                ->where('linehaul_trip_id', $trip->id)
                ->lockForUpdate()
                ->get();

            if ($transfers->count() !== $expected) {
                throw ValidationException::withMessages(['manifest_code' => 'Manifest transfer records do not match the expected parcel count.']);
            }

            $now = now();
            foreach ($transfers as $transfer) {
                if ($transfer->status === ParcelTransfer::RECEIVED) {
                    continue;
                }
                if ($transfer->status !== ParcelTransfer::IN_TRANSIT || (int) $transfer->to_sorting_center_id !== $centerId) {
                    throw ValidationException::withMessages(['manifest_code' => 'One or more parcels are not eligible for receipt at this center.']);
                }

                $transfer->update(['status' => ParcelTransfer::RECEIVED, 'received_at' => $now]);
                Delivery::whereKey($transfer->delivery_id)->update(['current_sorting_center_id' => $centerId]);

                DB::table('parcel_manifest_items')
                    ->where('manifest_id', $manifest->id)
                    ->where('delivery_id', $transfer->delivery_id)
                    ->update([
                        'received_at' => $now,
                        'received_by_user_id' => $operatorUserId,
                        'received_by_staff_id' => $staffId,
                        'updated_at' => $now,
                    ]);

                DB::table('parcel_scan_events')->insert([
                    'delivery_id' => $transfer->delivery_id,
                    'sorting_center_id' => $centerId,
                    'operator_user_id' => $operatorUserId,
                    'operator_staff_id' => $staffId,
                    'operator_rider_id' => null,
                    'operator_linehaul_driver_id' => null,
                    'parcel_transfer_id' => $transfer->id,
                    'event_type' => 'transfer_received',
                    'scanned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return (int) $trip->id;
        }, 3);

        app(LinehaulService::class)->completeTripIfReceived($tripId);
        return $this->inspectInbound($partnerId, $centerId, $manifestCode);
    }

    /* Legacy individual loading remains available for exception/reconciliation. */
    public function inspectLoad(int $partnerId, int $centerId, int $manifestId, string $trackingCode): array
    {
        $manifest = $this->originManifest($partnerId, $centerId, $manifestId);
        $code = $this->trackingCode($trackingCode);

        $delivery = Delivery::with(['order', 'destinationSortingCenter'])
            ->where('logistics_partner_id', $partnerId)
            ->where('tracking_code', $code)
            ->first();

        if (! $delivery) {
            throw ValidationException::withMessages(['tracking_code' => 'Parcel not found in your Logistics network.']);
        }

        $item = DB::table('parcel_manifest_items')
            ->where('manifest_id', $manifest->id)
            ->where('delivery_id', $delivery->id)
            ->first();

        if (! $item) {
            throw ValidationException::withMessages(['tracking_code' => 'This parcel is not part of the selected manifest.']);
        }
        if ($item->loaded_at) {
            throw ValidationException::withMessages(['tracking_code' => 'This parcel is already marked loaded on this manifest.']);
        }

        $this->assertLoadableDelivery($delivery, $centerId);

        return [
            'manifest_id' => $manifest->id,
            'manifest_code' => $manifest->code,
            'tracking_code' => $delivery->tracking_code,
            'from' => $manifest->fromCenter?->name,
            'to' => $manifest->toCenter?->name,
            'receiver' => $delivery->order?->delivery_name,
            'final_destination' => $delivery->destinationSortingCenter?->name,
            'can_load' => true,
        ];
    }

    public function load(
        int $partnerId,
        int $centerId,
        int $manifestId,
        string $trackingCode,
        int $operatorUserId,
        ?int $staffId = null
    ): array {
        $code = $this->trackingCode($trackingCode);

        return DB::transaction(function () use ($partnerId, $centerId, $manifestId, $code, $operatorUserId, $staffId) {
            $manifest = ParcelManifest::with(['fromCenter', 'toCenter'])
                ->where('logistics_partner_id', $partnerId)
                ->whereKey($manifestId)
                ->where('from_sorting_center_id', $centerId)
                ->whereIn('status', [ParcelManifest::PREPARED, ParcelManifest::LOADING])
                ->lockForUpdate()
                ->first();

            if (! $manifest) {
                throw ValidationException::withMessages(['manifest' => 'Manifest is no longer open for loading.']);
            }

            $delivery = Delivery::with('order')
                ->where('logistics_partner_id', $partnerId)
                ->where('tracking_code', $code)
                ->lockForUpdate()
                ->first();

            if (! $delivery) {
                throw ValidationException::withMessages(['tracking_code' => 'Parcel not found in your Logistics network.']);
            }

            $this->assertLoadableDelivery($delivery, $centerId);

            $item = DB::table('parcel_manifest_items')
                ->where('manifest_id', $manifest->id)
                ->where('delivery_id', $delivery->id)
                ->lockForUpdate()
                ->first();

            if (! $item) {
                throw ValidationException::withMessages(['tracking_code' => 'This parcel is not part of the selected manifest.']);
            }
            if ($item->loaded_at) {
                throw ValidationException::withMessages(['tracking_code' => 'This parcel is already marked loaded.']);
            }

            $now = now();
            DB::table('parcel_manifest_items')->where('id', $item->id)->update([
                'loaded_at' => $now,
                'loaded_by_user_id' => $operatorUserId,
                'loaded_by_staff_id' => $staffId,
                'updated_at' => $now,
            ]);

            DB::table('parcel_scan_events')->insert([
                'delivery_id' => $delivery->id,
                'sorting_center_id' => $centerId,
                'operator_user_id' => $operatorUserId,
                'operator_staff_id' => $staffId,
                'operator_rider_id' => null,
                'operator_linehaul_driver_id' => null,
                'parcel_transfer_id' => null,
                'event_type' => 'manifest_loaded',
                'scanned_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $counts = $this->counts($manifest->id);
            $manifest->forceFill([
                'status' => $counts['expected'] > 0 && $counts['loaded'] >= $counts['expected']
                    ? ParcelManifest::READY : ParcelManifest::LOADING,
            ])->save();

            return [
                'manifest_id' => $manifest->id,
                'manifest_code' => $manifest->code,
                'tracking_code' => $delivery->tracking_code,
                'expected' => $counts['expected'],
                'loaded' => $counts['loaded'],
                'missing' => max(0, $counts['expected'] - $counts['loaded']),
                'manifest_status' => $manifest->status,
                'ready' => $counts['expected'] > 0 && $counts['loaded'] >= $counts['expected'],
            ];
        }, 3);
    }

    private function originManifest(int $partnerId, int $centerId, int $manifestId): ParcelManifest
    {
        $manifest = ParcelManifest::with(['fromCenter', 'toCenter'])
            ->where('logistics_partner_id', $partnerId)
            ->whereKey($manifestId)
            ->where('from_sorting_center_id', $centerId)
            ->whereIn('status', [ParcelManifest::PREPARED, ParcelManifest::LOADING])
            ->first();

        if (! $manifest) {
            throw ValidationException::withMessages(['manifest' => 'Manifest is not open for loading at this center.']);
        }

        return $manifest;
    }

    private function assertLoadableDelivery(Delivery $delivery, int $centerId): void
    {
        if (! $delivery->order || $delivery->order->status !== Order::STATUS_TO_RECEIVE
            || $delivery->status !== Delivery::SORTED
            || (int) $delivery->current_sorting_center_id !== $centerId
            || ParcelTransfer::where('delivery_id', $delivery->id)->where('status', ParcelTransfer::IN_TRANSIT)->exists()) {
            throw ValidationException::withMessages([
                'tracking_code' => 'Parcel is no longer physically available for loading at this center.',
            ]);
        }
    }

    private function trackingCode(string $trackingCode): string
    {
        $code = strtoupper(trim($trackingCode));
        if (! preg_match('/^SHP-[0-9]{8}-[0-9]{6,}$/', $code)) {
            throw ValidationException::withMessages(['tracking_code' => 'Scan a valid ShopHop parcel QR.']);
        }
        return $code;
    }

    private function counts(int $manifestId): array
    {
        $counts = DB::table('parcel_manifest_items')
            ->where('manifest_id', $manifestId)
            ->selectRaw('COUNT(*) as expected, COUNT(loaded_at) as loaded, COUNT(received_at) as received')
            ->first();

        return [
            'expected' => (int) ($counts->expected ?? 0),
            'loaded' => (int) ($counts->loaded ?? 0),
            'received' => (int) ($counts->received ?? 0),
        ];
    }
}
