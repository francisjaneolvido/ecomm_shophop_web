<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\DeliveryFailureReport;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class MobileRiderDeliveryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rider = $this->activeRider($request);

        $deliveries = Delivery::with([
            'order.seller',
            'order.items.product',
            'order.items.variant',
            'destinationArea.sortingCenter',
            'pickupRider.sortingCenter',
            'deliveryRider.sortingCenter',
            'codSettlement',
            'originSortingCenter',
            'currentSortingCenter',
            'destinationSortingCenter',
            'transfers.fromCenter',
            'transfers.toCenter',
            'failureReports.rider',
        ])
            ->where('logistics_partner_id', $rider->logistics_partner_id)
            ->where(function ($query) use ($rider) {
                $query->where('rider_id', $rider->id)
                    ->orWhere('pickup_rider_id', $rider->id)
                    ->orWhere('delivery_rider_id', $rider->id)
                    ->orWhere('delivered_rider_id', $rider->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'status' => 'ok',
            'message' => 'Rider assignments loaded.',
            'data' => [
                'assignments' => $deliveries->map(fn (Delivery $delivery) => $this->deliveryData($delivery, $rider))->values(),
            ],
        ]);
    }

    public function show(Request $request, int $delivery): JsonResponse
    {
        $rider = $this->activeRider($request);
        $owned = $this->visible($rider, $delivery)->load([
            'order.seller',
            'order.items.product',
            'order.items.variant',
            'destinationArea.sortingCenter',
            'pickupRider.sortingCenter',
            'deliveryRider.sortingCenter',
            'codSettlement',
            'originSortingCenter',
            'currentSortingCenter',
            'destinationSortingCenter',
            'transfers.fromCenter',
            'transfers.toCenter',
            'failureReports.rider',
        ]);

        return response()->json([
            'success' => true,
            'status' => 'ok',
            'message' => 'Rider assignment loaded.',
            'data' => [
                'assignment' => $this->deliveryData($owned, $rider),
            ],
        ]);
    }

    public function acceptPickup(Request $request, int $delivery): JsonResponse
    {
        $rider = $this->activeRider($request);

        return DB::transaction(function () use ($rider, $delivery) {
            $owned = $this->current($rider, $delivery, true);

            if ($owned->status !== Delivery::PICKUP_ASSIGNED
                || $owned->order?->status !== Order::STATUS_READY_FOR_PICKUP) {
                return $this->stale();
            }

            $updated = Delivery::whereKey($owned->id)
                ->where('status', Delivery::PICKUP_ASSIGNED)
                ->where('rider_id', $rider->id)
                ->update([
                    'status' => Delivery::PICKUP_ACCEPTED,
                    'pickup_accepted_at' => now(),
                ]);

            if ($updated !== 1) {
                return $this->stale();
            }

            return $this->freshSuccess($rider, $owned->id, 'Pickup assignment accepted.');
        });
    }

    public function pickup(Request $request, int $delivery): JsonResponse
    {
        $rider = $this->activeRider($request);

        return DB::transaction(function () use ($rider, $delivery) {
            $owned = $this->current($rider, $delivery, true);

            if ($owned->status !== Delivery::PICKUP_ACCEPTED
                || $owned->order?->status !== Order::STATUS_READY_FOR_PICKUP
                || ! $owned->order->isPaymentEligible()) {
                return $this->stale();
            }

            $orderUpdated = Order::whereKey($owned->order_id)
                ->where('status', Order::STATUS_READY_FOR_PICKUP)
                ->paymentEligible()
                ->update(['status' => Order::STATUS_TO_RECEIVE]);

            $deliveryUpdated = Delivery::whereKey($owned->id)
                ->where('status', Delivery::PICKUP_ACCEPTED)
                ->where('rider_id', $rider->id)
                ->update([
                    'status' => Delivery::PICKED_UP,
                    'picked_up_at' => now(),
                    'pickup_rider_id' => $rider->id,
                ]);

            if ($orderUpdated !== 1 || $deliveryUpdated !== 1) {
                throw ValidationException::withMessages([
                    'delivery' => 'Delivery changed. Refresh and review it.',
                ]);
            }

            PickupRequest::where('order_id', $owned->order_id)->update([
                'status' => PickupRequest::PICKED_UP,
                'picked_up_at' => now(),
            ]);

            return $this->freshSuccess(
                $rider,
                $owned->id,
                'Parcel pickup confirmed. Bring it to the sorting center.',
            );
        });
    }

    public function outForDelivery(Request $request, int $delivery): JsonResponse
    {
        $rider = $this->activeRider($request);

        return DB::transaction(function () use ($rider, $delivery) {
            $owned = $this->current($rider, $delivery, true);

            if ($owned->status !== Delivery::DELIVERY_ASSIGNED
                || (int) $owned->delivery_rider_id !== (int) $rider->id
                || $owned->order?->status !== Order::STATUS_TO_RECEIVE
                || ! $owned->order->isPaymentEligible()
                || ($owned->destination_sorting_center_id
                    && (int) $owned->destination_sorting_center_id !== (int) $rider->sorting_center_id)
                || ($owned->current_sorting_center_id
                    && (int) $owned->current_sorting_center_id !== (int) $rider->sorting_center_id)) {
                return $this->stale();
            }

            $updated = Delivery::whereKey($owned->id)
                ->where('status', Delivery::DELIVERY_ASSIGNED)
                ->where('rider_id', $rider->id)
                ->where('delivery_rider_id', $rider->id)
                ->update([
                    'status' => Delivery::OUT_FOR_DELIVERY,
                    'out_for_delivery_at' => now(),
                    'in_transit_at' => now(),
                    'transit_rider_id' => $rider->id,
                ]);

            if ($updated !== 1) {
                return $this->stale();
            }

            return $this->freshSuccess($rider, $owned->id, 'Parcel marked out for delivery.');
        });
    }

    public function complete(Request $request, int $delivery): JsonResponse
    {
        $rider = $this->activeRider($request);
        $owned = $this->current($rider, $delivery);
        $order = $owned->order;

        if (! $order?->isPaymentEligible()) {
            return $this->stale();
        }

        $request->validate([
            'cash_collected' => $order->payment_method === 'cod' ? ['required', 'accepted'] : ['prohibited'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = null;
        try {
            return DB::transaction(function () use ($request, $delivery, $rider, &$path) {
                $owned = $this->current($rider, $delivery, true);

                if ($owned->status !== Delivery::OUT_FOR_DELIVERY
                    || (int) $owned->delivery_rider_id !== (int) $rider->id
                    || $owned->order?->status !== Order::STATUS_TO_RECEIVE
                    || ! $owned->order->isPaymentEligible()
                    || $owned->proof_path
                    || $owned->delivered_at
                    || CodSettlement::where('delivery_id', $owned->id)->exists()) {
                    return $this->stale();
                }

                $path = $request->file('proof')->store('delivery-proofs', 'local');
                if (! $path) {
                    throw ValidationException::withMessages([
                        'proof' => 'Proof could not be stored.',
                    ]);
                }

                $updated = Delivery::whereKey($owned->id)
                    ->where('status', Delivery::OUT_FOR_DELIVERY)
                    ->where('rider_id', $rider->id)
                    ->whereNull('proof_path')
                    ->update([
                        'status' => Delivery::DELIVERED,
                        'delivered_at' => now(),
                        'delivered_rider_id' => $rider->id,
                        'proof_path' => $path,
                    ]);

                if ($updated !== 1) {
                    throw ValidationException::withMessages([
                        'delivery' => 'Delivery changed. Refresh and review it.',
                    ]);
                }

                if ($owned->order->payment_method === 'cod') {
                    CodSettlement::create([
                        'order_id' => $owned->order_id,
                        'delivery_id' => $owned->id,
                        'logistics_partner_id' => $owned->logistics_partner_id,
                        'expected_amount' => $owned->order->total_amount,
                        'collected_amount' => $owned->order->total_amount,
                        'status' => CodSettlement::COLLECTED,
                        'collected_by_rider_id' => $rider->id,
                        'collected_at' => now(),
                    ]);
                }

                return $this->freshSuccess(
                    $rider,
                    $owned->id,
                    $owned->order->payment_method === 'cod'
                        ? 'Delivery proof saved and COD collection recorded. Waiting for Buyer confirmation.'
                        : 'Delivery proof saved. Waiting for Buyer confirmation.',
                );
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function fail(Request $request, int $delivery): JsonResponse
    {
        $rider = $this->activeRider($request);
        $data = $request->validate([
            'failure_reason' => ['required', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($rider, $delivery, $data) {
            $owned = $this->current($rider, $delivery, true);

            if ($owned->status !== Delivery::OUT_FOR_DELIVERY
                || (int) $owned->delivery_rider_id !== (int) $rider->id
                || $owned->order?->status !== Order::STATUS_TO_RECEIVE) {
                return $this->stale();
            }

            $reportedAt = now();
            $updated = Delivery::whereKey($owned->id)
                ->where('status', Delivery::OUT_FOR_DELIVERY)
                ->where('rider_id', $rider->id)
                ->update([
                    'status' => Delivery::DELIVERY_FAILED,
                    'delivery_failed_at' => $reportedAt,
                    'failure_reason' => trim($data['failure_reason']),
                ]);

            if ($updated !== 1) {
                return $this->stale();
            }

            $lastAttempt = (int) DeliveryFailureReport::where('delivery_id', $owned->id)->max('attempt_no');
            $attemptNo = max(1, (int) $owned->delivery_attempts, $lastAttempt + 1);

            DeliveryFailureReport::create([
                'delivery_id' => $owned->id,
                'order_id' => $owned->order_id,
                'logistics_partner_id' => $owned->logistics_partner_id,
                'rider_id' => $rider->id,
                'attempt_no' => $attemptNo,
                'reason' => trim($data['failure_reason']),
                'reported_at' => $reportedAt,
            ]);

            return $this->freshSuccess(
                $rider,
                $owned->id,
                'Delivery failure report submitted to Logistics.',
            );
        });
    }

    private function activeRider(Request $request): Rider
    {
        $actor = $request->user();
        abort_unless($actor instanceof Rider, 403, 'Rider account required.');

        $rider = $actor->fresh(['partner.user', 'coverageArea.sortingCenter', 'sortingCenter']);
        abort_unless(
            $rider
                && $rider->status === 'active'
                && $rider->email_verified_at
                && $rider->partner?->user?->status === 'approved',
            403,
            'Rider account is not active.',
        );

        return $rider;
    }

    private function visible(Rider $rider, int $deliveryId): Delivery
    {
        return Delivery::whereKey($deliveryId)
            ->where('logistics_partner_id', $rider->logistics_partner_id)
            ->where(function ($query) use ($rider) {
                $query->where('rider_id', $rider->id)
                    ->orWhere('pickup_rider_id', $rider->id)
                    ->orWhere('delivery_rider_id', $rider->id)
                    ->orWhere('delivered_rider_id', $rider->id);
            })
            ->firstOrFail();
    }

    private function current(Rider $rider, int $deliveryId, bool $lock = false): Delivery
    {
        $query = Delivery::with(['order.manualCashlessPayment'])
            ->whereKey($deliveryId)
            ->where('rider_id', $rider->id)
            ->where('logistics_partner_id', $rider->logistics_partner_id);

        return ($lock ? $query->lockForUpdate() : $query)->firstOrFail();
    }

    private function freshSuccess(Rider $rider, int $deliveryId, string $message): JsonResponse
    {
        $delivery = $this->visible($rider, $deliveryId)->load([
            'order.seller',
            'order.items.product',
            'order.items.variant',
            'destinationArea.sortingCenter',
            'pickupRider.sortingCenter',
            'deliveryRider.sortingCenter',
            'codSettlement',
            'originSortingCenter',
            'currentSortingCenter',
            'destinationSortingCenter',
            'transfers.fromCenter',
            'transfers.toCenter',
            'failureReports.rider',
        ]);

        return response()->json([
            'success' => true,
            'status' => 'ok',
            'message' => $message,
            'data' => [
                'assignment' => $this->deliveryData($delivery, $rider),
            ],
        ]);
    }

    private function stale(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status' => 'stale',
            'message' => 'This delivery changed or is no longer eligible. Refresh and review it.',
        ], 409);
    }

    private function deliveryData(Delivery $delivery, Rider $rider): array
    {
        $order = $delivery->order;
        $seller = $order?->seller;
        $isPickupRider = (int) $delivery->rider_id === (int) $rider->id
            && in_array($delivery->status, [Delivery::PICKUP_ASSIGNED, Delivery::PICKUP_ACCEPTED, Delivery::PICKED_UP], true);
        $isDeliveryRider = (int) $delivery->delivery_rider_id === (int) $rider->id;

        return [
            'id' => $delivery->id,
            'tracking_code' => $delivery->tracking_code,
            'status' => $delivery->status,
            'status_label' => $delivery->statusLabel(),
            'assignment_type' => $isPickupRider ? 'pickup' : ($isDeliveryRider ? 'delivery' : 'history'),
            'can_accept_pickup' => $isPickupRider && $delivery->status === Delivery::PICKUP_ASSIGNED,
            'can_confirm_pickup' => $isPickupRider && $delivery->status === Delivery::PICKUP_ACCEPTED,
            'can_start_delivery' => $isDeliveryRider && $delivery->status === Delivery::DELIVERY_ASSIGNED,
            'can_complete_delivery' => $isDeliveryRider && $delivery->status === Delivery::OUT_FOR_DELIVERY,
            'can_fail_delivery' => $isDeliveryRider && $delivery->status === Delivery::OUT_FOR_DELIVERY,
            'destination_area' => $delivery->destinationArea?->area_name,
            'origin_sorting_center' => $this->sortingCenterData($delivery->originSortingCenter),
            'current_sorting_center' => $this->sortingCenterData($delivery->currentSortingCenter),
            'destination_sorting_center' => $this->sortingCenterData($delivery->destinationSortingCenter),
            'pickup_dropoff_center' => $this->sortingCenterData($delivery->originSortingCenter),
            'delivery_pickup_center' => $this->sortingCenterData(
                $delivery->destinationSortingCenter ?: $delivery->currentSortingCenter
            ),
            'center_transfers' => $delivery->transfers->map(fn ($transfer) => [
                'id' => $transfer->id,
                'status' => $transfer->status,
                'from' => $this->sortingCenterData($transfer->fromCenter),
                'to' => $this->sortingCenterData($transfer->toCenter),
                'dispatched_at' => optional($transfer->dispatched_at)?->toIso8601String(),
                'received_at' => optional($transfer->received_at)?->toIso8601String(),
            ])->values(),
            'failure_reason' => $delivery->failure_reason,
            'failure_reports' => $delivery->failureReports->map(fn ($report) => [
                'id' => $report->id,
                'attempt_no' => $report->attempt_no,
                'reason' => $report->reason,
                'reported_at' => optional($report->reported_at)?->toIso8601String(),
                'rider' => $report->rider?->name,
            ])->values(),
            'delivery_attempts' => $delivery->delivery_attempts,
            'order' => $order ? [
                'id' => $order->id,
                'status' => $order->status,
                'total_amount' => (string) $order->total_amount,
                'payment_method' => $order->payment_method,
                'delivery_name' => $order->delivery_name,
                'delivery_phone' => $order->delivery_phone,
                'delivery_address' => $order->delivery_address,
                'seller' => $seller ? [
                    'name' => trim(($seller->first_name ?? '').' '.($seller->last_name ?? '')),
                    'business_name' => $seller->business_name,
                    'contact_no' => $seller->contact_no,
                    'address' => collect([
                        $seller->street_address,
                        $seller->barangay_name,
                        $seller->municipality_name,
                        $seller->province_name,
                    ])->filter()->implode(', '),
                ] : null,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->product?->name ?? 'Product',
                    'quantity' => $item->quantity,
                    'price' => (string) $item->price,
                    'variant' => $item->variantLabel(),
                ])->values(),
            ] : null,
        ];
    }

    private function sortingCenterData($center): ?array
    {
        if (! $center) {
            return null;
        }

        return [
            'id' => $center->id,
            'code' => $center->code,
            'name' => $center->name,
            'address' => $center->addressLabel(),
            'is_main' => (bool) $center->is_main,
        ];
    }

}
