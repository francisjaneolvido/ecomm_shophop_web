<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\RiderBatch;
use App\Models\LogisticsPartner;
use App\Services\RiderBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiderBatchMonitoringController extends Controller
{
    public function index(Request $request, RiderBatchService $service)
    {
        $partner = LogisticsPartner::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($service->available(), 503, 'Batch monitoring is temporarily unavailable.');
        $branches = $partner->sortingCenters()->where('type', 'station_hub')->orderBy('name')->get();
        $query = RiderBatch::where('logistics_partner_id', $partner->id);
        if ($request->filled('branch')) {
            $branch = $branches->firstWhere('id', (int) $request->query('branch'));
            abort_unless($branch, 404);
            $query->where('sorting_center_id', $branch->id);
        }
        if (in_array($request->query('status'), ['queued', 'in_progress', 'completed', 'cancelled'], true)) {
            $query->where('status', $request->query('status'));
        }
        $totals = (clone $query)->select('sorting_center_id', 'status', DB::raw('COUNT(*) AS total'))->groupBy('sorting_center_id', 'status')->get();
        $batches = $query->with(['rider', 'branch', 'items.delivery.order'])->latest('id')->paginate(15)->withQueryString();
        $batchData = $batches->getCollection()->map(fn ($b) => $service->data($b));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'data' => [
                'html' => view('logistics.partials.rider-batch-routes', ['batchData' => $batchData, 'batches' => $batches, 'canManage' => false])->render(),
                'totals' => $totals->groupBy('status')->map(fn ($rows) => $rows->sum('total'))]]);
        }

        return view('logistics.rider-batches.index', compact('partner', 'branches', 'totals', 'batches', 'batchData'));
    }
}
