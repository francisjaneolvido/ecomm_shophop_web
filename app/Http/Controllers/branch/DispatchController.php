<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DispatchController extends Controller
{
    public function index(Request $request): View
    {
        $center = $this->center($request);
        $riders = $this->activeRiders($center)->get();

        // 1) Pickup requests ng branch na naghihintay ng pickup rider
        $pickups = PickupRequest::with('order.seller')
            ->where('origin_sorting_center_id', $center->id)
            ->where('status', PickupRequest::REQUESTED)
            ->oldest('requested_at')
            ->get()
            ->map(function (PickupRequest $p) use ($riders) {
                $order = $p->order;

                return [
                    'id'        => $p->id,
                    'tracking'  => $order?->tracking_code,
                    'seller'    => $order?->seller?->business_name ?? 'Seller',
                    'address'   => $p->originAddressLabel(),
                    'requested' => $p->requested_at?->diffForHumans(),
                    'ready'     => $order
                        && $order->status === Order::STATUS_READY_FOR_PICKUP
                        && $order->isPaymentEligible(),
                    'suggested' => $this->suggest($riders, $p->origin_barangay_code, $p->origin_barangay_name),
                ];
            });

        // 2) Naka-sort na sa branch na ito, wala pang delivery rider (o nag-fail at kailangan ng retry)
        $deliveries = Delivery::with('order')
            ->where('logistics_partner_id', $center->logistics_partner_id)
            ->where('destination_sorting_center_id', $center->id)
            ->where('current_sorting_center_id', $center->id)
            ->whereIn('status', [Delivery::SORTED, Delivery::DELIVERY_FAILED])
            ->whereDoesntHave('transfers', fn ($q) => $q->where('status', ParcelTransfer::IN_TRANSIT))
            ->whereDoesntHave('returnCase')
            ->latest('updated_at')
            ->get()
            ->map(function (Delivery $d) use ($riders) {
                $order = $d->order;
                // Riders lang na naka-assign sa destination area ng parcel ang pwede.
                $eligible = $riders
                    ->filter(fn ($r) => (int) $r->coverage_area_id === (int) $d->destination_area_id)
                    ->values();

                return [
                    'id'        => $d->id,
                    'tracking'  => $d->tracking_code,
                    'receiver'  => $order?->delivery_name,
                    'barangay'  => $order?->delivery_barangay_name ?: $order?->delivery_address,
                    'cod'       => $order && $order->payment_method === 'cod' ? (float) $order->total_amount : null,
                    'failed'    => $d->status === Delivery::DELIVERY_FAILED,
                    'attempts'  => (int) $d->delivery_attempts,
                    'reason'    => $d->failure_reason,
                    'riders'    => $eligible->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->all(),
                    'suggested' => $this->suggest($eligible, $order?->delivery_barangay_code, $order?->delivery_barangay_name),
                ];
            });

        return view('branch.dispatch.index', [
            'center'     => $center,
            'pickups'    => $pickups,
            'deliveries' => $deliveries,
            'riders'     => $riders->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->all(),
        ]);
    }

    /** I-assign ang rider na kukuha ng parcel sa seller papunta sa branch na ito. */
    public function assignPickup(Request $request, int $pickup): RedirectResponse
    {
        $center = $this->center($request);
        $data = $request->validate(['rider_id' => ['required', 'integer']], [
            'rider_id.required' => 'Pumili ng rider.',
        ]);

        $rider = $this->activeRiders($center)->find($data['rider_id']);
        if (! $rider) {
            return $this->fail('Hindi valid ang rider na napili.');
        }

        try {
            return DB::transaction(function () use ($center, $rider, $pickup) {
                $pickupRequest = PickupRequest::whereKey($pickup)->lockForUpdate()->first();

                if (! $pickupRequest
                    || (int) $pickupRequest->origin_sorting_center_id !== (int) $center->id
                    || (int) $pickupRequest->logistics_partner_id !== (int) $center->logistics_partner_id
                    || $pickupRequest->status !== PickupRequest::REQUESTED) {
                    return $this->stale();
                }

                $order = Order::whereKey($pickupRequest->order_id)->lockForUpdate()->first();

                if (! $order
                    || $order->status !== Order::STATUS_READY_FOR_PICKUP
                    || ! $order->isPaymentEligible()
                    || Delivery::where('order_id', $order->id)->exists()
                    || ! $this->activeRiders($center)->whereKey($rider->id)->exists()) {
                    return $this->stale();
                }

                Delivery::create([
                    'order_id'                 => $order->id,
                    'tracking_code'            => $order->tracking_code ?: $this->trackingCode($order),
                    'logistics_partner_id'     => $center->logistics_partner_id,
                    'origin_sorting_center_id' => $center->id,
                    'rider_id'                 => $rider->id,
                    'status'                   => Delivery::PICKUP_ASSIGNED,
                    'assigned_at'              => now(),
                ]);

                $pickupRequest->update([
                    'status'                   => PickupRequest::ASSIGNED,
                    'origin_sorting_center_id' => $center->id,
                    'assigned_at'              => now(),
                ]);

                return redirect()->route('branch.dispatch.index')
                    ->with('status', "Na-assign si {$rider->name} para sa pickup.");
            });
        } catch (QueryException $e) {
            if (Delivery::where('order_id', PickupRequest::whereKey($pickup)->value('order_id'))->exists()) {
                return $this->stale();
            }
            throw $e;
        }
    }

    /** I-assign ang rider na magdedeliver sa buyer (o mag-retry kapag failed). */
    public function assignDelivery(Request $request, int $delivery): RedirectResponse
    {
        $center = $this->center($request);
        $data = $request->validate(['rider_id' => ['required', 'integer']], [
            'rider_id.required' => 'Pumili ng rider.',
        ]);

        $rider = $this->activeRiders($center)->find($data['rider_id']);
        if (! $rider) {
            return $this->fail('Hindi valid ang rider na napili.');
        }

        return DB::transaction(function () use ($center, $rider, $delivery) {
            $owned = Delivery::with(['order', 'transfers'])
                ->where('logistics_partner_id', $center->logistics_partner_id)
                ->where('destination_sorting_center_id', $center->id)
                ->where('current_sorting_center_id', $center->id)
                ->whereKey($delivery)
                ->lockForUpdate()
                ->first();

            if (! $owned
                || ! in_array($owned->status, [Delivery::SORTED, Delivery::DELIVERY_FAILED], true)
                || $owned->order?->status !== Order::STATUS_TO_RECEIVE
                || $owned->returnCase()->exists()
                || ! $owned->destination_area_id
                || $owned->transfers->contains(fn ($t) => $t->status === ParcelTransfer::IN_TRANSIT)
                || ! $this->activeRiders($center)->whereKey($rider->id)->exists()) {
                return $this->stale();
            }

            if ((int) $rider->coverage_area_id !== (int) $owned->destination_area_id) {
                return $this->fail('Pumili ng rider na naka-assign sa delivery area ng parcel na ito.');
            }

            $owned->update([
                'rider_id'             => $rider->id,
                'delivery_rider_id'    => $rider->id,
                'status'               => Delivery::DELIVERY_ASSIGNED,
                'delivery_assigned_at' => now(),
                'delivery_attempts'    => $owned->delivery_attempts + 1,
            ]);

            return redirect()->route('branch.dispatch.index')
                ->with('status', "Na-assign si {$rider->name} sa delivery.");
        });
    }

    // ---------------------------------------------------------------------

    private function center(Request $request): SortingCenter
    {
        $center = SortingCenter::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->first();

        abort_unless($center, 403, 'Walang aktibong branch na naka-link sa account mo.');

        return $center;
    }

    /** Active riders ng branch na ito na may login (kapareho ng rule ng Logistics board). */
    private function activeRiders(SortingCenter $center)
    {
        return Rider::where('logistics_partner_id', $center->logistics_partner_id)
            ->where('sorting_center_id', $center->id)
            ->where('status', 'active')
            ->whereNotNull('email')
            ->whereNotNull('password')
            ->orderBy('name');
    }

    /** Rider na tugma sa barangay (by PSGC code, o by pangalan kung magkaiba ang code format). */
    private function suggest(Collection $riders, ?string $code, ?string $name): ?int
    {
        $norm = fn (?string $v) => Str::of(Str::ascii((string) $v))
            ->lower()
            ->replaceMatches('/^(barangay|brgy\.?)\s+/', '')
            ->squish()
            ->toString();

        $wanted = $norm($name);

        return $riders->first(function ($r) use ($code, $wanted, $norm) {
            return ($code && $r->barangay_code && $r->barangay_code === $code)
                || ($wanted !== '' && $norm($r->barangay_name) === $wanted);
        })?->id;
    }

    private function trackingCode(Order $order): string
    {
        $date = ($order->created_at ?? now())->format('Ymd');

        return 'SHP-'.$date.'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->route('branch.dispatch.index')->withErrors(['dispatch' => $message]);
    }

    private function stale(): RedirectResponse
    {
        return $this->fail('Nagbago na ang parcel o hindi na ito eligible. I-refresh at tingnan ulit.');
    }
}