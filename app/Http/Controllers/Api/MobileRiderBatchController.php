<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Rider;
use App\Models\Logistics\RiderBatch;
use App\Services\RiderBatchService;
use Illuminate\Http\Request;

class MobileRiderBatchController extends Controller
{
    private function rider(Request $request): Rider
    {
        $actor = $request->user();
        abort_unless($actor instanceof Rider, 403);
        $rider = $actor->fresh(['partner.user', 'sortingCenter']);
        abort_unless($rider && $rider->status === 'active' && $rider->email_verified_at
            && $rider->partner?->user?->status === 'approved' && $rider->sortingCenter?->status === 'active'
            && ! $rider->sortingCenter?->archived_at, 403);

        return $rider;
    }

    public function index(Request $request, RiderBatchService $service)
    {
        $rider = $this->rider($request);
        if (! $service->available()) {
            return response()->json(['success' => true, 'data' => ['enabled' => false, 'batches' => []]]);
        }
        $query = RiderBatch::where('logistics_partner_id', $rider->logistics_partner_id)->where('rider_id', $rider->id);
        $history = $request->query('history') === '1';
        $query->whereIn('status', $history ? ['completed', 'cancelled'] : ['queued', 'in_progress']);
        $page = $query->with(['rider', 'branch', 'items.delivery.order'])->orderByRaw("CASE WHEN status='in_progress' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_start')->paginate(20);

        return response()->json(['success' => true, 'data' => ['enabled' => true,
            'batches' => $page->getCollection()->map(fn ($b) => $service->data($b)),
            'next_page' => $page->hasMorePages() ? $page->currentPage() + 1 : null]]);
    }

    public function show(Request $request, int $batch, RiderBatchService $service)
    {
        $rider = $this->rider($request);
        abort_unless($service->available(), 503);
        $owned = RiderBatch::where('logistics_partner_id', $rider->logistics_partner_id)->where('rider_id', $rider->id)->findOrFail($batch);

        return response()->json(['success' => true, 'data' => ['batch' => $service->data($owned)]]);
    }

    public function pickupFailure(Request $request, int $batch, int $item, RiderBatchService $service)
    {
        $rider = $this->rider($request);
        $data = $request->validate(['reason' => 'required|string|min:3|max:1000']);
        abort_unless($service->available(), 503);
        $service->pickupFailure($rider, $batch, $item, $data['reason']);

        return $this->show($request,$batch,$service);
    }
}
