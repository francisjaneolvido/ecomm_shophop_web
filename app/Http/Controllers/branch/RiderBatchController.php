<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\BranchStaff;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\PickupRequest;
use App\Models\Logistics\Rider;
use App\Models\Logistics\RiderBatch;
use App\Models\Logistics\RiderBatchItem;
use App\Models\Logistics\RiderBatchVehicleProfile;
use App\Models\Logistics\SortingCenter;
use App\Services\RiderBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiderBatchController extends Controller
{
    private function scope(Request $request): array
    {
        $staff = $request->routeIs('logistics.staff.*') ? Auth::guard('logistics_staff')->user() : null;
        if ($staff instanceof BranchStaff) {
            abort_unless($staff->role === 'manager' && $staff->activeForBranch($staff->logistics_partner_id, $staff->sorting_center_id), 403);
            $center = $staff->sortingCenter;
            $actor = ['staff', (int) $staff->id];
        } else {
            $user = $request->user();
            abort_unless($user && $user->account_type === 'branch' && $user->status === 'approved', 403);
            $center = SortingCenter::where('user_id', $user->id)->firstOrFail();
            $actor = ['user', (int) $user->id];
        }
        abort_unless($center->type === 'station_hub' && ! $center->is_main && $center->status === 'active' && ! $center->archived_at
            && $center->partner?->user?->status === 'approved', 403);

        return [$center, ...$actor];
    }

    public function index(Request $request, RiderBatchService $service)
    {
        [$center] = $this->scope($request);
        abort_unless($service->available(), 503, 'Batch planning is temporarily unavailable.');
        $type = $request->query('type') === 'delivery' ? 'delivery' : 'pickup';
        $riders = Rider::where('logistics_partner_id', $center->logistics_partner_id)->where('sorting_center_id', $center->id)->where('status', 'active')->orderBy('name')->get();
        $profiles = RiderBatchVehicleProfile::whereIn('rider_id', $riders->pluck('id'))->get()->keyBy('rider_id');
        $batches = RiderBatch::where('logistics_partner_id', $center->logistics_partner_id)->where('sorting_center_id', $center->id)
            ->with(['rider', 'items.delivery.order', 'branch'])->latest('id')->paginate(15, ['*'], 'batch_page')->withQueryString();
        $batchData = $batches->getCollection()->map(fn ($batch) => $service->data($batch));
        $routePrefix = $request->routeIs('logistics.staff.*') ? 'logistics.staff.rider-batches' : 'branch.rider-batches';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'data' => ['html' => view('logistics.partials.rider-batch-routes',
                ['batchData' => $batchData, 'batches' => $batches, 'canManage' => true, 'routePrefix' => $routePrefix])->render()]]);
        }
        $orders = Order::where('logistics_partner_id', $center->logistics_partner_id)->with(['seller', 'delivery', 'pickupRequest', 'financial'])
            ->whereNotIn('id', Delivery::whereIn('id', RiderBatchItem::whereNotNull('reserved_delivery_id')->select('reserved_delivery_id'))->select('order_id'));
        if ($type === 'pickup') {
            $orders->where('status', Order::STATUS_READY_FOR_PICKUP)->whereHas('pickupRequest', fn ($q) => $q
                ->where('origin_sorting_center_id', $center->id)->whereIn('status', [PickupRequest::REQUESTED, PickupRequest::ASSIGNED]))
                ->where(fn ($q) => $q->whereDoesntHave('delivery')->orWhereHas('delivery', fn ($d) => $d->whereIn('status', [Delivery::PICKUP_ASSIGNED, Delivery::PICKUP_ACCEPTED])));
        } else {
            $orders->where('status', Order::STATUS_TO_RECEIVE)->whereHas('delivery', fn ($q) => $q
                ->where('destination_sorting_center_id', $center->id)->where('current_sorting_center_id', $center->id)
                ->whereIn('status', [Delivery::SORTED, Delivery::DELIVERY_FAILED, Delivery::DELIVERY_ASSIGNED]));
        }
        $term = trim((string) $request->query('q', ''));
        if ($term !== '') {
            $orders->where('tracking_code', 'like', '%'.substr($term, 0, 80).'%');
        }
        $orders = $orders->orderBy('id')->paginate(30, ['*'], 'parcel_page')->withQueryString();
        $routePrefix = $request->routeIs('logistics.staff.*') ? 'logistics.staff.rider-batches' : 'branch.rider-batches';

        return view('branch.rider-batches.index', compact('center', 'riders', 'profiles', 'batches', 'batchData', 'orders', 'type', 'routePrefix'));
    }

    public function configure(Request $request, int $rider, RiderBatchService $service)
    {
        [$branch,$actorType,$actorId] = $this->scope($request);
        abort_unless($service->available(), 503);
        $data = $request->validate(['max_weight_kg' => 'required|numeric|min:0.001|max:999999',
            'max_volume_m3' => 'required|numeric|min:0.000001|max:999999', 'max_stops' => 'required|integer|min:1|max:4294967295',
            'max_duration_minutes' => 'required|integer|min:1|max:4294967295', 'max_parcels' => 'nullable|integer|min:1|max:4294967295']);
        $service->configure($branch, $rider, $data, $actorType, $actorId);

        return $request->expectsJson() ? response()->json(['success' => true]) : back()->with('status', 'Rider vehicle capacity saved.');
    }

    public function store(Request $request, RiderBatchService $service)
    {
        [$branch,$actorType,$actorId] = $this->scope($request);
        $data = $request->validate(['rider_id' => 'required|integer', 'type' => 'required|in:pickup,delivery',
            'scheduled_start' => 'required|date', 'scheduled_end' => 'required|date|after:scheduled_start',
            'estimated_minutes' => 'required|integer|min:1|max:4294967295', 'parcels' => 'required|array|min:1',
            'parcels.*.order_id' => 'required|integer|distinct', 'parcels.*.weight_kg' => 'required|numeric|min:0.001|max:999999',
            'parcels.*.volume_m3' => 'required|numeric|min:0.000001|max:999999']);
        $batch = $service->create($branch, $data, $actorType, $actorId);

        return $request->expectsJson() ? response()->json(['success' => true, 'data' => ['batch' => $service->data($batch)]], 201) : back()->with('status', 'Batch queued. Branch dispatch is required before parcel scans.');
    }

    public function transition(Request $request, int $batch, RiderBatchService $service)
    {
        [$branch,$actorType,$actorId] = $this->scope($request);
        $data = $request->validate(['action' => 'required|in:dispatch,complete,cancel']);
        $result = $service->transition($branch, $batch, $data['action'], $actorType, $actorId);

        return $request->expectsJson() ? response()->json(['success' => true, 'data' => ['batch' => $service->data($result)]]) : back()->with('status', 'Batch '.$data['action'].' recorded.');
    }
}
