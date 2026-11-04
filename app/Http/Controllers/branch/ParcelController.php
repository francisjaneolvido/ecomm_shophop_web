<?php

namespace App\Http\Controllers\branch;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\SortingCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Branch-scoped read-only parcel register; custody-changing actions live in validated Dispatch/Scanner services. */
class ParcelController extends Controller
{
    private const FILTERS = [
        'at_branch' => [Delivery::AT_SORTING_CENTER, Delivery::SORTED],
        'assigned' => [Delivery::DELIVERY_ASSIGNED],
        'out_for_delivery' => [Delivery::OUT_FOR_DELIVERY],
        'delivered' => [Delivery::DELIVERED],
        'failed' => [Delivery::DELIVERY_FAILED],
        'returns' => [Delivery::RETURN_REQUESTED, Delivery::RETURN_AT_BRANCH, Delivery::RETURN_ASSIGNED, Delivery::RETURN_IN_TRANSIT, Delivery::RETURN_AWAITING_SELLER, Delivery::RETURNED],
    ];

    public function index(Request $request): View
    {
        $center = SortingCenter::query()->where('user_id', $request->user()->id)
            ->where('status', 'active')->whereNull('archived_at')->firstOrFail();

        $status = (string) $request->query('status', 'all');
        if ($status !== 'all' && ! array_key_exists($status, self::FILTERS)) $status = 'all';
        $search = trim(substr((string) $request->query('q', ''), 0, 120));

        // Current physical custody + last-mile history for this destination branch only.
        // Avoid showing unreceived parcels from other centers destined for this branch.
        $base = Delivery::query()
            ->where('logistics_partner_id', $center->logistics_partner_id)
            ->where(function ($q) use ($center) {
                $q->where('current_sorting_center_id', $center->id)
                    ->orWhere(function ($done) use ($center) {
                        $done->where('destination_sorting_center_id', $center->id)
                            ->whereIn('status', [Delivery::DELIVERY_ASSIGNED, Delivery::OUT_FOR_DELIVERY, Delivery::DELIVERED, Delivery::DELIVERY_FAILED, Delivery::RETURNED]);
                    });
            });

        $statusCounts = (clone $base)->select('status', DB::raw('COUNT(*) AS total'))
            ->groupBy('status')->pluck('total', 'status');
        $counts = ['all' => $statusCounts->sum()];
        foreach (self::FILTERS as $key => $values) {
            $counts[$key] = collect($values)->sum(fn ($value) => (int) ($statusCounts[$value] ?? 0));
        }

        $parcels = $base->with(['order', 'deliveryRider', 'destinationSortingCenter', 'transfers.toCenter'])
            ->when($status !== 'all', fn ($q) => $q->whereIn('status', self::FILTERS[$status]))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($matching) use ($search) {
                    $matching->where('tracking_code', 'like', '%'.$search.'%')
                        ->orWhereHas('order', fn ($o) => $o->where('delivery_name', 'like', '%'.$search.'%')
                            ->orWhere('tracking_code', 'like', '%'.$search.'%'));
                });
            })
            ->latest('updated_at')->paginate(25)->withQueryString();

        return view('branch.parcels.index', compact('center', 'parcels', 'counts', 'status', 'search'));
    }

    /** Deprecated unsafe bulk-assign entry point. Assignments must use Branch Dispatch service validation. */
    public function assign(Request $request): RedirectResponse
    {
        return redirect()->route('branch.dispatch.index')
            ->with('status', 'Use Dispatch to assign eligible riders. Physical QR handoff is required before Out for Delivery.');
    }
}
