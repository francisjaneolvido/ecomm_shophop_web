<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\BranchStaff;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\ParcelTransfer;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use App\Models\Logistics\RiderBatch;
use App\Models\Logistics\RiderBatchItem;
use App\Models\Logistics\RiderBatchVehicleProfile;
use App\Models\Logistics\SortingCenter;
use App\Services\RiderBatchService;
use App\Services\RiderServiceAreaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DispatchController extends Controller
{
    public function index(Request $request, RiderServiceAreaService $serviceAreas): View|JsonResponse
    {
        $center = $this->center($request);
        $service = app(RiderBatchService::class);
        $dispatchEnabled = $service->available();
        $routePrefix = $request->routeIs('logistics.staff.*') ? 'logistics.staff.dispatch' : 'branch.dispatch';
        $riders = $this->activeRiders($center)->with(['operationalServiceAreas', 'lifecycleProfile'])->get();
        $profiles = $dispatchEnabled
            ? RiderBatchVehicleProfile::whereIn('rider_id', $riders->pluck('id'))->get()->keyBy('rider_id')
            : collect();
        $riderDetails = $riders->mapWithKeys(function ($rider) use ($profiles) {
            $profile = $profiles->get($rider->id);

            return [$rider->id => ['id' => $rider->id, 'name' => $rider->name,
                'capacity_configured' => $profile && $profile->vehicle_type === $rider->vehicle_type && $profile->plate_number === $rider->plate_number,
                'limits' => $profile?->limits ?? []]];
        });
        $batches = $dispatchEnabled ? RiderBatch::where('logistics_partner_id', $center->logistics_partner_id)
            ->where('sorting_center_id', $center->id)->with(['rider', 'items.delivery.order', 'branch'])
            ->latest('id')->paginate(15, ['*'], 'route_page')->withQueryString() : null;
        $batchData = $batches ? $batches->getCollection()->map(fn ($batch) => $service->data($batch)) : collect();
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'data' => ['html' => view('branch.dispatch.routes',
                compact('batchData', 'batches', 'routePrefix'))->render()]]);
        }

        $pickups = PickupRequest::with(['order.seller', 'order.financial'])
            ->where('logistics_partner_id', $center->logistics_partner_id)
            ->where('origin_sorting_center_id', $center->id)->where('status', PickupRequest::REQUESTED)
            ->oldest('requested_at')->paginate(25, ['*'], 'pickup_page')->withQueryString()
            ->through(function (PickupRequest $pickup) use ($riders, $riderDetails, $serviceAreas, $center) {
                $order = $pickup->order;
                $seller = $order?->seller;
                $eligible = $seller ? $serviceAreas->matchingRiders($riders, (int) $center->id,
                    $seller->province_name, $seller->municipality_name, $seller->barangay_name, 'pickup') : collect();

                return ['order_id' => $order?->id, 'tracking' => $order?->tracking_code,
                    'name' => $seller?->business_name ?? 'Seller', 'address' => $pickup->originAddressLabel(),
                    'note' => $pickup->requested_at?->diffForHumans(),
                    'ready' => $order && $order->status === Order::STATUS_READY_FOR_PICKUP && $order->isPaymentEligible(),
                    'weight_kg' => $order?->financial?->shipping_weight_kg,
                    'riders' => $eligible->map(fn ($rider) => $riderDetails[$rider->id])->values()->all()];
            });
        $deliveries = Delivery::with('order.financial')->where('logistics_partner_id', $center->logistics_partner_id)
            ->where('destination_sorting_center_id', $center->id)->where('current_sorting_center_id', $center->id)
            ->whereIn('status', [Delivery::SORTED, Delivery::DELIVERY_FAILED])
            ->whereDoesntHave('transfers', fn ($query) => $query->where('status', ParcelTransfer::IN_TRANSIT))
            ->whereDoesntHave('returnCase')
            ->when($dispatchEnabled, fn ($query) => $query->whereNotIn('id', RiderBatchItem::whereNotNull('reserved_delivery_id')->select('reserved_delivery_id')))
            ->latest('updated_at')->paginate(25, ['*'], 'delivery_page')->withQueryString()
            ->through(function (Delivery $delivery) use ($riders, $riderDetails, $serviceAreas, $center) {
                $order = $delivery->order;
                $eligible = $serviceAreas->matchingRiders($riders, (int) $center->id,
                    $order?->delivery_province_name, $order?->delivery_municipality_name, $order?->delivery_barangay_name, 'delivery');

                return ['order_id' => $order?->id, 'tracking' => $delivery->tracking_code,
                    'name' => $order?->delivery_name ?: 'Buyer', 'address' => $order?->delivery_address,
                    'note' => $delivery->status === Delivery::DELIVERY_FAILED ? 'Retry delivery' : 'Ready for delivery',
                    'ready' => $order && $order->status === Order::STATUS_TO_RECEIVE && $order->isPaymentEligible(),
                    'weight_kg' => $order?->financial?->shipping_weight_kg,
                    'riders' => $eligible->map(fn ($rider) => $riderDetails[$rider->id])->values()->all()];
            });

        return view('branch.dispatch.index', compact('center', 'pickups', 'deliveries', 'riderDetails',
            'dispatchEnabled', 'batchData', 'batches', 'routePrefix'));
    }

    public function assign(Request $request, RiderBatchService $service): JsonResponse|RedirectResponse
    {
        $center = $this->center($request);
        abort_unless($service->available(), 503, 'Rider assignment is temporarily unavailable.');
        $immediate = $request->boolean('dispatch_now', true);
        $data = $request->validate([
            'rider_id' => ['required', 'integer'], 'type' => ['required', 'in:pickup,delivery'],
            'dispatch_now' => ['sometimes', 'boolean'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'scheduled_start' => [$immediate ? 'nullable' : 'required', 'date'],
            'scheduled_end' => [$immediate ? 'nullable' : 'required', 'date', 'after:scheduled_start'],
            'parcels' => ['required', 'array', 'min:1', 'max:100'],
            'parcels.*.order_id' => ['required', 'integer', 'distinct'],
            'parcels.*.weight_kg' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'parcels.*.volume_m3' => ['required', 'numeric', 'min:0.000001', 'max:999999'],
        ]);
        $staff = $request->routeIs('logistics.staff.*') ? Auth::guard('logistics_staff')->user() : null;
        $actorType = $staff ? 'staff' : 'user';
        $actorId = (int) ($staff?->id ?? $request->user()->id);
        if ($immediate) {
            $start = now()->startOfSecond();
            $data['scheduled_start'] = $start->toIso8601String();
            $data['scheduled_end'] = $start->copy()->addMinutes($data['estimated_minutes'])->toIso8601String();
        }
        // A failed dispatch rolls back route creation and all parcel reservations.
        $batch = DB::transaction(function () use ($service, $center, $data, $actorType, $actorId, $immediate) {
            $batch = $service->create($center, $data, $actorType, $actorId);

            return $immediate ? $service->transition($center, $batch->id, 'dispatch', $actorType, $actorId) : $batch;
        }, 3);
        $message = $immediate ? 'Parcels assigned and dispatched to the rider.' : 'Rider route scheduled. Dispatch it from this page when its schedule starts.';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message, 'data' => ['route' => $service->data($batch)]], 201)
            : back()->with('status', $message);
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
                app(RiderBatchService::class)->guardLegacyAssignment($rider);
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
                    'order_id' => $order->id,
                    'tracking_code' => $order->tracking_code ?: $this->trackingCode($order),
                    'logistics_partner_id' => $center->logistics_partner_id,
                    'origin_sorting_center_id' => $center->id,
                    'rider_id' => $rider->id,
                    'status' => Delivery::PICKUP_ASSIGNED,
                    'assigned_at' => now(),
                ]);

                $pickupRequest->update([
                    'status' => PickupRequest::ASSIGNED,
                    'origin_sorting_center_id' => $center->id,
                    'assigned_at' => now(),
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
            app(RiderBatchService::class)->guardLegacyAssignment($rider);
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

            app(RiderBatchService::class)->guardLegacyAssignment($rider, $owned);
            $owned->update([
                'rider_id' => $rider->id,
                'delivery_rider_id' => $rider->id,
                'status' => Delivery::DELIVERY_ASSIGNED,
                'delivery_assigned_at' => now(),
                'delivery_attempts' => $owned->delivery_attempts + 1,
            ]);

            return redirect()->route('branch.dispatch.index')
                ->with('status', "Na-assign si {$rider->name} sa delivery.");
        });
    }

    // ---------------------------------------------------------------------

    private function center(Request $request): SortingCenter
    {
        if ($request->routeIs('logistics.staff.*')) {
            $staff = Auth::guard('logistics_staff')->user();
            abort_unless($staff instanceof BranchStaff && $staff->role === 'manager'
                && $staff->activeForBranch($staff->logistics_partner_id, $staff->sorting_center_id), 403);
            $center = $staff->sortingCenter;
        } else {
            $user = $request->user();
            abort_unless($user && $user->account_type === 'branch' && $user->status === 'approved', 403);
            $center = SortingCenter::where('user_id', $user->id)->first();
        }
        abort_unless($center && $center->type === 'station_hub' && ! $center->is_main
            && $center->status === 'active' && ! $center->archived_at
            && $center->partner?->user?->status === 'approved', 403);

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
