<?php

namespace App\Http\Controllers;

use App\Models\Logistics\SortingCenter;
use App\Models\User;
use App\Services\CodDiscrepancyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CodDiscrepancyController extends Controller
{
    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User && $actor->fresh()?->status === 'approved'
            && in_array($actor->account_type, ['admin', 'logistics', 'branch'], true), 403);

        return $actor;
    }

    public function index(Request $request)
    {
        $actor = $this->actor($request);
        CodDiscrepancyService::ready();
        $query = DB::table('cod_discrepancy_cases as c')
            ->join('cod_settlements as cash', 'cash.id', '=', 'c.cod_settlement_id')
            ->leftJoin('sorting_centers as center', 'center.id', '=', 'c.sorting_center_id')
            ->select('c.*', 'cash.order_id', 'cash.expected_amount', 'cash.collected_amount',
                'cash.received_amount', 'center.name as facility_name');
        if ($actor->account_type === 'logistics') {
            $partner = DB::table('logistics_partners')->where('user_id', $actor->id)->value('id');
            abort_unless($partner, 403);
            $query->where('c.logistics_partner_id', $partner);
        } elseif ($actor->account_type === 'branch') {
            $query->whereIn('c.sorting_center_id', SortingCenter::where('user_id', $actor->id)->select('id'))
                ->whereColumn('center.logistics_partner_id', 'c.logistics_partner_id');
        }
        $filter = $request->string('status', 'active')->toString();
        if (! in_array($filter, ['active', 'open', 'pending_approval', 'approved', 'resolved', 'all'], true)) {
            $filter = 'active';
        }
        if ($filter === 'active') {
            $query->where('c.status', '!=', 'resolved');
        } elseif ($filter !== 'all') {
            $query->where('c.status', $filter);
        }
        $selectedCase = $request->validate(['case' => ['nullable', 'integer', 'min:1']])['case'] ?? null;
        if ($selectedCase) {
            $query->where('c.id', $selectedCase);
        }
        $cases = $query->orderByDesc('c.id')->paginate(20)->withQueryString();
        $proposals = DB::table('cod_discrepancy_resolutions')->whereIn('case_id', $cases->pluck('id'))
            ->orderByDesc('id')->get()->groupBy('case_id');
        $events = DB::table('financial_audit_events')->where('subject_type', 'cod_discrepancy')
            ->whereIn('subject_id', $cases->pluck('id'))->orderBy('id')->get()->groupBy('subject_id');
        $adjustments = DB::table('cod_cash_adjustments')
            ->whereIn('resolution_id', $proposals->flatten()->pluck('id'))->get()->keyBy('resolution_id');
        $routePrefix = $request->routeIs('logistics.*') ? 'logistics.hybrid.discrepancies' : 'hybrid.discrepancies';

        return view('finance.discrepancies', compact('actor', 'cases', 'proposals', 'events', 'adjustments', 'filter', 'routePrefix'));
    }

    public function note(Request $request, int $case, CodDiscrepancyService $service)
    {
        $service->note($this->actor($request), $case, $request->only('reason', 'evidence_reference'));

        return back()->with('status', 'Evidence added to the case history.');
    }

    public function propose(Request $request, int $case, CodDiscrepancyService $service)
    {
        $service->propose($this->actor($request), $case, $request->only('kind', 'reason', 'evidence_reference', 'recipient'));

        return back()->with('status', 'Resolution proposed. ShopHop Admin must independently review it.');
    }

    public function review(Request $request, int $case, int $resolution, CodDiscrepancyService $service)
    {
        $data = $request->validate(['decision' => ['required', 'in:approve,reject'], 'notes' => ['required', 'string', 'min:10', 'max:2000']]);
        $service->review($this->actor($request), $case, $resolution, $data['decision'], $data['notes']);

        return back()->with('status', $data['decision'] === 'approve'
            ? 'Review approved. Approved excess returns still need actual payment confirmation by Logistics.'
            : 'Proposal rejected. The case remains open for investigation.');
    }

    public function recordReturn(Request $request, int $case, int $resolution, CodDiscrepancyService $service)
    {
        $service->recordReturn($this->actor($request), $case, $resolution,
            $request->only('returned_amount', 'reference', 'recipient_acknowledgment', 'cash_returned'));

        return back()->with('status', 'Actual excess return recorded and case resolved. Original counted receipts remain in the history.');
    }
}
