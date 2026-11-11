<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Logistics\BranchStaff;
use App\Models\Logistics\RiderBatch;
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
        if (! $request->expectsJson()) {
            return redirect()->route($request->routeIs('logistics.staff.*') ? 'logistics.staff.dispatch.index' : 'branch.dispatch.index', $request->only(['q', 'type']));
        }
        abort_unless($service->available(), 503, 'Rider assignment is temporarily unavailable.');
        $batches = RiderBatch::where('logistics_partner_id', $center->logistics_partner_id)->where('sorting_center_id', $center->id)
            ->with(['rider', 'items.delivery.order', 'branch'])->latest('id')->paginate(15, ['*'], 'route_page')->withQueryString();
        $batchData = $batches->getCollection()->map(fn ($batch) => $service->data($batch));
        $routePrefix = $request->routeIs('logistics.staff.*') ? 'logistics.staff.dispatch' : 'branch.dispatch';

        return response()->json(['success' => true, 'data' => ['html' => view('branch.dispatch.routes',
            compact('batchData', 'batches', 'routePrefix'))->render()]]);
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

        return $request->expectsJson() ? response()->json(['success' => true, 'data' => ['batch' => $service->data($result)]]) : back()->with('status', 'Rider route action recorded: '.$data['action'].'.');
    }
}
