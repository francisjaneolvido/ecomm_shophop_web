<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelManifest;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManifestLoadingService
{
    public function summary(int $partnerId, int $centerId, string $manifestCode): array
    {
        $manifest = ParcelManifest::with(['fromCenter', 'toCenter', 'linehaulTrip.vehicle', 'linehaulTrip.driver'])
            ->where('logistics_partner_id', $partnerId)
            ->where('code', trim($manifestCode))
            ->first();

        if (! $manifest) {
            throw ValidationException::withMessages(['manifest_code' => 'Manifest not found in your Logistics network.']);
        }

        if ((int) $manifest->from_sorting_center_id !== $centerId && (int) $manifest->to_sorting_center_id !== $centerId) {
            throw ValidationException::withMessages(['manifest_code' => 'This manifest does not belong to this center.']);
        }

        $counts = DB::table('parcel_manifest_items')
            ->where('manifest_id', $manifest->id)
            ->selectRaw('COUNT(*) as expected')
            ->selectRaw('COUNT(loaded_at) as loaded')
            ->selectRaw('COUNT(received_at) as received')
            ->first();

        return [
            'id' => $manifest->id,
            'code' => $manifest->code,
            'status' => $manifest->status,
            'from' => $manifest->fromCenter?->name,
            'to' => $manifest->toCenter?->name,
            'expected' => (int) ($counts->expected ?? 0),
            'loaded' => (int) ($counts->loaded ?? 0),
            'received' => (int) ($counts->received ?? 0),
            'vehicle' => $manifest->linehaulTrip?->vehicle?->code,
            'plate_number' => $manifest->linehaulTrip?->vehicle?->plate_number,
            'driver' => $manifest->linehaulTrip?->driver?->name,
            'trip_code' => $manifest->linehaulTrip?->code,
        ];
    }

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

            DB::table('parcel_manifest_items')->where('id', $item->id)->update([
                'loaded_at' => now(),
                'loaded_by_user_id' => $operatorUserId,
                'loaded_by_staff_id' => $staffId,
                'updated_at' => now(),
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
                'scanned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $expected = DB::table('parcel_manifest_items')->where('manifest_id', $manifest->id)->count();
            $loaded = DB::table('parcel_manifest_items')->where('manifest_id', $manifest->id)->whereNotNull('loaded_at')->count();

            $manifest->forceFill([
                'status' => $expected > 0 && $loaded >= $expected ? ParcelManifest::READY : ParcelManifest::LOADING,
            ])->save();

            return [
                'manifest_id' => $manifest->id,
                'manifest_code' => $manifest->code,
                'tracking_code' => $delivery->tracking_code,
                'expected' => $expected,
                'loaded' => $loaded,
                'missing' => max(0, $expected - $loaded),
                'manifest_status' => $manifest->status,
                'ready' => $expected > 0 && $loaded >= $expected,
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
}
