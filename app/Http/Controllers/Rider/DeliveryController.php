<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\DeliveryFailureReport;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $rider = $request->user('rider');
        $deliveries = Delivery::with(['order.seller', 'codSettlement', 'destinationArea', 'failureReports.rider'])
            ->where('logistics_partner_id', $rider->logistics_partner_id)
            ->where(function ($query) use ($rider) {
                $query->where('rider_id', $rider->id)
                    ->orWhere('pickup_rider_id', $rider->id)
                    ->orWhere('delivery_rider_id', $rider->id)
                    ->orWhere('delivered_rider_id', $rider->id);
            })
            ->latest()->get();

        return view('rider.deliveries', compact('deliveries'));
    }

    public function show(Request $request, int $delivery): View
    {
        $owned = $this->visible($request, $delivery)
            ->load(['order.seller', 'codSettlement', 'destinationArea', 'pickupRider', 'deliveryRider', 'failureReports.rider']);

        return view('rider.show', ['delivery' => $owned]);
    }

    public function acceptPickup(Request $request, int $delivery): RedirectResponse
    {
        return DB::transaction(function () use ($request, $delivery) {
            $rider = $request->user('rider');
            $owned = $this->current($request, $delivery, true);
            $this->assertActive($rider->id, $owned->logistics_partner_id);

            if ($owned->status !== Delivery::PICKUP_ASSIGNED
                || $owned->order?->status !== Order::STATUS_READY_FOR_PICKUP) {
                return $this->stale();
            }

            if (Delivery::whereKey($owned->id)->where('status', Delivery::PICKUP_ASSIGNED)
                ->where('rider_id', $rider->id)
                ->update(['status' => Delivery::PICKUP_ACCEPTED, 'pickup_accepted_at' => now()]) !== 1) {
                return $this->stale();
            }

            return back()->with('status', 'Pickup assignment accepted.');
        });
    }

    public function pickup(Request $request, int $delivery): RedirectResponse
    {
        return DB::transaction(function () use ($request, $delivery) {
            $rider = $request->user('rider');
            $owned = $this->current($request, $delivery, true);
            $this->assertActive($rider->id, $owned->logistics_partner_id);

            if ($owned->status !== Delivery::PICKUP_ACCEPTED
                || $owned->order?->status !== Order::STATUS_READY_FOR_PICKUP
                || ! $owned->order->isPaymentEligible()) {
                return $this->stale();
            }

            if (Order::whereKey($owned->order_id)->where('status', Order::STATUS_READY_FOR_PICKUP)
                    ->paymentEligible()->update(['status' => Order::STATUS_TO_RECEIVE]) !== 1
                || Delivery::whereKey($owned->id)->where('status', Delivery::PICKUP_ACCEPTED)
                    ->where('rider_id', $rider->id)->update([
                        'status' => Delivery::PICKED_UP,
                        'picked_up_at' => now(),
                        'pickup_rider_id' => $rider->id,
                    ]) !== 1) {
                throw ValidationException::withMessages([
                    'delivery' => 'Delivery changed. Refresh and review it.',
                ]);
            }

            PickupRequest::where('order_id', $owned->order_id)->update([
                'status' => PickupRequest::PICKED_UP,
                'picked_up_at' => now(),
            ]);

            return back()->with('status', 'Parcel pickup confirmed. Bring it to the sorting center.');
        });
    }

    public function outForDelivery(Request $request, int $delivery): RedirectResponse
    {
        return DB::transaction(function () use ($request, $delivery) {
            $rider = $request->user('rider');
            $owned = $this->current($request, $delivery, true);
            $this->assertActive($rider->id, $owned->logistics_partner_id);

            if ($owned->status !== Delivery::DELIVERY_ASSIGNED
                || (int) $owned->delivery_rider_id !== (int) $rider->id
                || $owned->order?->status !== Order::STATUS_TO_RECEIVE
                || ! $owned->order->isPaymentEligible()) {
                return $this->stale();
            }

            if (Delivery::whereKey($owned->id)->where('status', Delivery::DELIVERY_ASSIGNED)
                ->where('rider_id', $rider->id)->where('delivery_rider_id', $rider->id)
                ->update([
                    'status' => Delivery::OUT_FOR_DELIVERY,
                    'out_for_delivery_at' => now(),
                    'in_transit_at' => now(),
                    'transit_rider_id' => $rider->id,
                ]) !== 1) {
                return $this->stale();
            }

            return back()->with('status', 'Parcel marked out for delivery.');
        });
    }

    /** Backward-compatible route name for old links; the legal stage is now final-delivery dispatch. */
    public function transit(Request $request, int $delivery): RedirectResponse
    {
        return $this->outForDelivery($request, $delivery);
    }

    public function complete(Request $request, int $delivery): RedirectResponse
    {
        $order = $this->current($request, $delivery)->order;
        if (! $order?->isPaymentEligible()) {
            return $this->stale();
        }

        $request->validate([
            'cash_collected' => $order->payment_method === 'cod' ? ['required', 'accepted'] : ['prohibited'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = null;
        try {
            return DB::transaction(function () use ($request, $delivery, &$path) {
                $rider = $request->user('rider');
                $owned = $this->current($request, $delivery, true);
                $this->assertActive($rider->id, $owned->logistics_partner_id);

                if ($owned->status !== Delivery::OUT_FOR_DELIVERY
                    || (int) $owned->delivery_rider_id !== (int) $rider->id
                    || $owned->order?->status !== Order::STATUS_TO_RECEIVE
                    || ! $owned->order->isPaymentEligible()
                    || $owned->proof_path || $owned->delivered_at
                    || CodSettlement::where('delivery_id', $owned->id)->exists()) {
                    return $this->stale();
                }

                $path = $request->file('proof')->store('delivery-proofs', 'local');
                if (! $path) {
                    throw ValidationException::withMessages(['proof' => 'Proof could not be stored.']);
                }

                // Rider delivery ends at DELIVERED. Buyer confirmation separately moves the Order to COMPLETED.
                if (Delivery::whereKey($owned->id)->where('status', Delivery::OUT_FOR_DELIVERY)
                    ->where('rider_id', $rider->id)->whereNull('proof_path')
                    ->update([
                        'status' => Delivery::DELIVERED,
                        'delivered_at' => now(),
                        'delivered_rider_id' => $rider->id,
                        'proof_path' => $path,
                    ]) !== 1) {
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

                return back()->with('status', $owned->order->payment_method === 'cod'
                    ? 'Delivery proof saved and COD collection recorded. Waiting for Buyer confirmation.'
                    : 'Delivery proof saved. Waiting for Buyer confirmation.');
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function fail(Request $request, int $delivery): RedirectResponse
    {
        $data = $request->validate([
            'failure_reason' => ['required', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($request, $delivery, $data) {
            $rider = $request->user('rider');
            $owned = $this->current($request, $delivery, true);
            $this->assertActive($rider->id, $owned->logistics_partner_id);

            if ($owned->status !== Delivery::OUT_FOR_DELIVERY
                || (int) $owned->delivery_rider_id !== (int) $rider->id
                || $owned->order?->status !== Order::STATUS_TO_RECEIVE) {
                return $this->stale();
            }

            $reportedAt = now();
            if (Delivery::whereKey($owned->id)->where('status', Delivery::OUT_FOR_DELIVERY)
                ->where('rider_id', $rider->id)->update([
                    'status' => Delivery::DELIVERY_FAILED,
                    'delivery_failed_at' => $reportedAt,
                    'failure_reason' => trim($data['failure_reason']),
                ]) !== 1) {
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

            return back()->with('status', 'Delivery failure report submitted to Logistics.');
        });
    }

    private function visible(Request $request, int $deliveryId): Delivery
    {
        $rider = $request->user('rider');
        return Delivery::whereKey($deliveryId)
            ->where('logistics_partner_id', $rider->logistics_partner_id)
            ->where(function ($query) use ($rider) {
                $query->where('rider_id', $rider->id)
                    ->orWhere('pickup_rider_id', $rider->id)
                    ->orWhere('delivery_rider_id', $rider->id)
                    ->orWhere('delivered_rider_id', $rider->id);
            })->firstOrFail();
    }

    private function current(Request $request, int $deliveryId, bool $lock = false): Delivery
    {
        $rider = $request->user('rider');
        $query = Delivery::with('order')->whereKey($deliveryId)
            ->where('rider_id', $rider->id)
            ->where('logistics_partner_id', $rider->logistics_partner_id);

        return ($lock ? $query->lockForUpdate() : $query)->firstOrFail();
    }

    private function stale(): RedirectResponse
    {
        return back()->withErrors(['delivery' => 'Delivery changed. Refresh and review it.']);
    }

    private function assertActive(int $riderId, int $partnerId): void
    {
        $rider = Rider::whereKey($riderId)->where('logistics_partner_id', $partnerId)
            ->lockForUpdate()->first();
        abort_unless($rider && $rider->status === 'active' && $rider->email && $rider->password
            && $rider->partner?->user?->status === 'approved', 403);
    }
}
