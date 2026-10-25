<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\LinehaulTrip;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\SortingCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** One authoritative physical receiving rule for Web and Mobile Logistics scanners. */
class ParcelScanService
{
    public function inspect(int $partnerId, int $centerId, string $trackingCode): array
    {
        $code = trim($trackingCode);
        if (! preg_match('/^SHP-[0-9]{8}-[0-9]{6,}$/', $code)) {
            throw ValidationException::withMessages(['tracking_code' => 'Scan a valid ShopHop shipping label.']);
        }

        $center = SortingCenter::where('logistics_partner_id', $partnerId)
            ->where('status', 'active')->findOrFail($centerId);

        $delivery = Delivery::with('order')
            ->where('logistics_partner_id', $partnerId)
            ->where('tracking_code', $code)
            ->first();

        if (! $delivery) {
            throw ValidationException::withMessages(['tracking_code' => 'Parcel not found in your Logistics network.']);
        }
        if (! $delivery->order || $delivery->order->status !== Order::STATUS_TO_RECEIVE) {
            throw ValidationException::withMessages(['tracking_code' => 'Parcel is not eligible for center receipt.']);
        }

        if ($delivery->status === Delivery::PICKED_UP
            && (int) $delivery->origin_sorting_center_id === $centerId) {
            $action = 'Receive pickup from Rider';
            $event = 'pickup_received';
        } elseif ($delivery->status === Delivery::SORTED) {
            $transfer = ParcelTransfer::with('linehaulTrip')
                ->where('delivery_id', $delivery->id)
                ->where('status', ParcelTransfer::IN_TRANSIT)
                ->latest('id')
                ->first();

            if (! $transfer || (int) $transfer->to_sorting_center_id !== $centerId) {
                throw ValidationException::withMessages(['tracking_code' => 'No transfer is awaiting receipt at this center.']);
            }

            $this->assertTransferArrived($transfer);

            $action = 'Receive Linehaul transfer';
            $event = 'transfer_received';
        } else {
            throw ValidationException::withMessages(['tracking_code' => 'Wrong center, duplicate scan, or parcel not ready for receipt.']);
        }

        return [
            'tracking_code' => $delivery->tracking_code,
            'branch' => $center->name,
            'delivery_status' => $delivery->status,
            'event' => $event,
            'next_action' => $action,
            'can_receive' => true,
        ];
    }

    public function receive(
        int $partnerId,
        int $centerId,
        int $operatorId,
        string $trackingCode,
        ?int $staffId = null
    ): array {
        $code = trim($trackingCode);
        if (! preg_match('/^SHP-[0-9]{8}-[0-9]{6,}$/', $code)) {
            throw ValidationException::withMessages(['tracking_code' => 'Scan a valid ShopHop parcel label, not an external URL.']);
        }

        $result = DB::transaction(function () use ($partnerId, $centerId, $operatorId, $code, $staffId) {
            $center = SortingCenter::where('logistics_partner_id', $partnerId)
                ->where('status', 'active')->findOrFail($centerId);

            $delivery = Delivery::with('order')
                ->where('logistics_partner_id', $partnerId)
                ->where('tracking_code', $code)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $delivery->order || $delivery->order->status !== Order::STATUS_TO_RECEIVE) {
                throw ValidationException::withMessages(['tracking_code' => 'Parcel is not currently eligible for center receipt.']);
            }

            $linehaulTripId = null;
            $manifestId = null;

            if ($delivery->status === Delivery::PICKED_UP) {
                if (! $delivery->origin_sorting_center_id || (int) $delivery->origin_sorting_center_id !== $centerId) {
                    throw ValidationException::withMessages(['sorting_center_id' => 'This parcel belongs to a different pickup branch.']);
                }

                $delivery->update([
                    'status' => Delivery::AT_SORTING_CENTER,
                    'current_sorting_center_id' => $centerId,
                    'at_sorting_center_at' => now(),
                ]);

                PickupRequest::where('order_id', $delivery->order_id)->update([
                    'status' => PickupRequest::RECEIVED,
                    'received_at' => now(),
                ]);

                $type = 'pickup_received';
                $transferId = null;
            } elseif ($delivery->status === Delivery::SORTED) {
                $transfer = ParcelTransfer::with('linehaulTrip')
                    ->where('delivery_id', $delivery->id)
                    ->where('status', ParcelTransfer::IN_TRANSIT)
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                if (! $transfer || (int) $transfer->to_sorting_center_id !== $centerId) {
                    throw ValidationException::withMessages([
                        'sorting_center_id' => 'No transfer is awaiting receipt at this center.',
                    ]);
                }

                $this->assertTransferArrived($transfer);

                $transfer->update([
                    'status' => ParcelTransfer::RECEIVED,
                    'received_at' => now(),
                ]);

                $delivery->update(['current_sorting_center_id' => $centerId]);

                $type = 'transfer_received';
                $transferId = $transfer->id;
                $linehaulTripId = $transfer->linehaul_trip_id;
                $manifestId = $transfer->manifest_id;

                if ($manifestId) {
                    DB::table('parcel_manifest_items')
                        ->where('manifest_id', $manifestId)
                        ->where('delivery_id', $delivery->id)
                        ->update([
                            'received_at' => now(),
                            'received_by_user_id' => $operatorId,
                            'received_by_staff_id' => $staffId,
                            'updated_at' => now(),
                        ]);
                }
            } else {
                throw ValidationException::withMessages([
                    'tracking_code' => 'Parcel already received or currently at a different workflow stage.',
                ]);
            }

            DB::table('parcel_scan_events')->insert([
                'delivery_id' => $delivery->id,
                'sorting_center_id' => $center->id,
                'operator_user_id' => $operatorId,
                'operator_staff_id' => $staffId,
                'operator_rider_id' => null,
                'operator_linehaul_driver_id' => null,
                'parcel_transfer_id' => $transferId,
                'event_type' => $type,
                'scanned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'delivery_id' => $delivery->id,
                'tracking_code' => $delivery->tracking_code,
                'event' => $type,
                'delivery_status' => $delivery->status,
                'branch' => $center->name,
                '_linehaul_trip_id' => $linehaulTripId,
            ];
        }, 3);

        if (! empty($result['_linehaul_trip_id'])) {
            app(LinehaulService::class)->completeTripIfReceived((int) $result['_linehaul_trip_id']);
        }

        unset($result['_linehaul_trip_id']);
        return $result;
    }

    private function assertTransferArrived(ParcelTransfer $transfer): void
    {
        if ($transfer->linehaul_trip_id) {
            $trip = $transfer->linehaulTrip
                ?: LinehaulTrip::find($transfer->linehaul_trip_id);

            if (! $trip || ! in_array($trip->status, [LinehaulTrip::ARRIVED, LinehaulTrip::COMPLETED], true) || ! $trip->arrived_at) {
                throw ValidationException::withMessages([
                    'tracking_code' => 'The destination Branch/Hub has not scanned and acknowledged this Manifest QR yet.',
                ]);
            }
            return;
        }

        // Legacy Transit Rider records remain receivable only after Rider departure.
        if ($transfer->transit_rider_id && ! $transfer->departed_at) {
            throw ValidationException::withMessages([
                'tracking_code' => 'The legacy Transit Rider has not confirmed departure yet.',
            ]);
        }
    }
}
