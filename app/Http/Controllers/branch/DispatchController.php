<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use App\Models\Logistics\SortingCenter;
use App\Services\RiderServiceAreaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DispatchController extends Controller
{
    public function index(Request $request, RiderServiceAreaService $serviceAreas): View
    {
        $center = $this->center($request);
        $riders = $this->activeRiders($center)->with('operationalServiceAreas')->get();

        // 1) Pickup requests ng branch na naghihintay ng pickup rider
        $pickups = PickupRequest::with('order.seller')
            ->where('origin_sorting_center_id', $center->id)
            ->where('status', PickupRequest::REQUESTED)
            ->oldest('requested_at')
            ->paginate(25, ['*'], 'pickup_page')->withQueryString()
            ->through(function (PickupRequest $p) use ($riders, $serviceAreas, $center) {
                $order = $p->order;
                $seller = $order?->seller;
                $eligible = $seller ? $serviceAreas->matchingRiders($riders, (int) $center->id,
                    $seller->province_name, $seller->municipality_name,
                    $seller->barangay_name, 'pickup') : collect();

                return [
                    'id'        => $p->id,
                    'tracking'  => $order?->tracking_code,
                    'seller'    => $order?->seller?->business_name ?? 'Seller',
                    'address'   => $p->originAddressLabel(),
                    'requested' => $p->requested_at?->diffForHumans(),
                    'ready'     => $order
                        && $order->status === Order::STATUS_READY_FOR_PICKUP
                        && $order->isPaymentEligible(),
                    'suggested' => $eligible->first()?->id,
                    'riders' => $eligible->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->all(),
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
            ->paginate(25, ['*'], 'delivery_page')->withQueryString()
            ->through(function (Delivery $d) use ($riders, $serviceAreas, $center) {
                $order = $d->order;
                // Exact barangay rider first, citywide second. Legacy coverage-area
                // assignment only applies when operational service areas are not configured.
                $eligible = $serviceAreas->matchingRiders($riders, (int) $center->id,
                    $order?->delivery_province_name, $order?->delivery_municipality_name,
                    $order?->delivery_barangay_name, 'delivery');
                if (! $serviceAreas->configured((int) $center->id)) {
                    $eligible = $eligible->filter(fn ($r) => (int) $r->coverage_area_id === (int) $d->destination_area_id)->values();
                }

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
                    'suggested' => $eligible->first()?->id,
                    'local' => (int) $d->origin_sorting_center_id === (int) $center->id,
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
    public function assignPickup(Request $request, int $pickup, RiderServiceAreaService $serviceAreas): RedirectResponse
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
            return DB::transaction(function () use ($center, $rider, $pickup, $serviceAreas) {
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

                if (! $serviceAreas->pickupEligible($rider, (int) $center->id, $order->seller)) {
                    return $this->fail('This rider does not cover the Seller barangay or is not available. Check Rider Service Areas.');
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
    public function assignDelivery(Request $request, int $delivery, RiderServiceAreaService $serviceAreas): RedirectResponse
    {
        $center = $this->center($request);
        $data = $request->validate(['rider_id' => ['required', 'integer']], [
            'rider_id.required' => 'Pumili ng rider.',
        ]);

        $rider = $this->activeRiders($center)->find($data['rider_id']);
        if (! $rider) {
            return $this->fail('Hindi valid ang rider na napili.');
        }

        return DB::transaction(function () use ($center, $rider, $delivery, $serviceAreas) {
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

            if (! $serviceAreas->configured((int) $center->id)
                && (int) $rider->coverage_area_id !== (int) $owned->destination_area_id) {
                return $this->fail('Choose a Rider assigned to this destination coverage area.');
            }
            if (! $serviceAreas->deliveryEligible($rider, (int) $center->id, $owned->order)) {
                return $this->fail('This rider does not cover the Buyer barangay or is unavailable. Check Rider Service Areas.');
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