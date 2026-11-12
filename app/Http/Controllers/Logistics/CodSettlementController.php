<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\LogisticsPartner;
use App\Models\User;
use App\Services\FinancialAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CodSettlementController extends Controller
{
    public function index(Request $request): View
    {
        $partner = $this->partner($request);
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:collected,remitted,reconciled,admin_pending,admin_received,review,none']]);
        $search = trim($data['q'] ?? '');
        $status = $data['status'] ?? '';
        $base = Delivery::where('logistics_partner_id', $partner->id)
            ->whereHas('order', fn ($q) => $q->where('payment_method', 'cod'))
            ->where('status', Delivery::DELIVERED);
        $cashBase = CodSettlement::where('logistics_partner_id', $partner->id)
            ->whereIn('delivery_id', (clone $base)->select('id'));
        $stats = ['all' => (clone $base)->count()];
        $amounts = [];
        foreach (['collected', 'remitted', 'reconciled', 'admin_pending', 'admin_received', 'review'] as $stage) {
            $cashQuery = clone $cashBase;
            $this->filterStage($cashQuery, $stage);
            $stats[$stage] = (clone $cashQuery)->count();
            $amounts[$stage] = (string) $cashQuery->sum(match ($stage) {
                'reconciled', 'admin_pending' => 'received_amount',
                'admin_received' => 'admin_received_amount',
                'review' => 'expected_amount',
                default => 'collected_amount',
            });
        }
        if ($status === 'none') {
            $base->whereDoesntHave('codSettlement');
        } elseif ($status !== '') {
            $base->whereHas('codSettlement', fn ($query) => $this->filterStage($query, $status));
        }
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', '%'.$search.'%')
                    ->orWhereHas('order.seller', fn ($s) => $s->where('business_name', 'like', '%'.$search.'%'))
                    ->orWhereHas('rider', fn ($r) => $r->where('name', 'like', '%'.$search.'%'));
                if (ctype_digit($search)) {
                    $q->orWhere('order_id', (int) $search);
                }
            });
        }
        $deliveries = $base->with(['order.seller', 'rider', 'codSettlement.collector', 'codSettlement.remitter'])
            ->latest()->paginate(25)->withQueryString();

        $openCases = \Illuminate\Support\Facades\DB::table('cod_discrepancy_cases')
            ->whereIn('cod_settlement_id', $deliveries->pluck('codSettlement')->filter()->pluck('id'))
            ->where('status', '!=', 'resolved')->get()->keyBy('cod_settlement_id');

        $handovers = DB::table('cod_remittance_items as item')
            ->join('cod_remittance_batches as batch', 'batch.id', '=', 'item.batch_id')
            ->leftJoin('sorting_centers as center', 'center.id', '=', 'batch.sorting_center_id')
            ->where('batch.logistics_partner_id', $partner->id)
            ->whereIn('item.cod_settlement_id', $deliveries->pluck('codSettlement')->filter()->pluck('id'))
            ->select('item.cod_settlement_id', 'batch.id as batch_id', 'batch.status as batch_status', 'batch.verified_at', 'center.name as branch_name')
            ->orderByDesc('batch.id')->get()->unique('cod_settlement_id')->keyBy('cod_settlement_id');

        return view('logistics.settlements.index', compact('deliveries', 'stats', 'amounts', 'search', 'status', 'openCases', 'handovers'));
    }

    /** Read-only queue filters; approval and custody actions retain their own checks. */
    private function filterStage(\Illuminate\Database\Eloquent\Builder $query, string $stage): void
    {
        $case = fn ($q) => $q->selectRaw('1')->from('cod_discrepancy_cases')
            ->whereColumn('cod_discrepancy_cases.cod_settlement_id', 'cod_settlements.id')
            ->where('cod_discrepancy_cases.status', '!=', 'resolved');
        if ($stage === 'review') {
            $query->whereExists($case);
            return;
        }
        $pendingHandover = fn ($q) => $q->selectRaw('1')->from('cod_remittance_items as pending_item')
            ->join('cod_remittance_batches as pending_batch', 'pending_batch.id', '=', 'pending_item.batch_id')
            ->whereColumn('pending_item.cod_settlement_id', 'cod_settlements.id')
            ->whereColumn('pending_batch.logistics_partner_id', 'cod_settlements.logistics_partner_id')
            ->whereNull('pending_batch.verified_at');
        $query->whereNotExists($case);
        if ($stage === 'remitted') {
            $query->where(fn ($q) => $q->where('status', CodSettlement::REMITTED)->orWhereExists($pendingHandover));
            return;
        }
        $query->whereNotExists($pendingHandover);
        match ($stage) {
            'admin_pending' => $query->whereNotNull('admin_remitted_at')->whereNull('admin_received_at'),
            'admin_received' => $query->whereNotNull('admin_received_at'),
            'reconciled' => $query->where('status', CodSettlement::RECONCILED)->whereNull('admin_remitted_at'),
            default => $query->where('status', $stage),
        };
    }

    public function reconcile(Request $request, int $delivery): RedirectResponse
    {
        $partner = $this->partner($request);

        return DB::transaction(function () use ($request, $partner, $delivery) {
            // Partner ownership is derived from the authenticated User, never a submitted partner or amount.
            abort_unless(User::whereKey($request->user()->id)->where('account_type', 'logistics')
                ->where('status', 'approved')->lockForUpdate()->first(), 403);
            Delivery::whereKey($delivery)->where('logistics_partner_id', $partner->id)->firstOrFail();
            $cash = CodSettlement::where('delivery_id', $delivery)
                ->where('logistics_partner_id', $partner->id)->lockForUpdate()->first();
            if ($cash && DB::table('cod_remittance_items')->where('cod_settlement_id', $cash->id)->exists()) {
                return back()->withErrors(['settlement' => 'Verify this cash through its remittance batch in the financial workspace.']);
            }
            if (! $cash || $cash->status !== CodSettlement::REMITTED || ! $cash->remitted_at
                || $cash->reconciled_at || $cash->expected_amount !== $cash->collected_amount) {
                return back()->withErrors(['settlement' => 'Cash is not ready for receipt confirmation. Refresh and review it.']);
            }
            if (CodSettlement::whereKey($cash->id)->where('status', CodSettlement::REMITTED)
                ->whereNull('reconciled_at')->update([
                    'status' => CodSettlement::RECONCILED,
                    'received_amount' => $cash->collected_amount,
                    'reconciled_by_user_id' => $request->user()->id,
                    'reconciled_at' => now(),
                ]) !== 1) {
                return back()->withErrors(['settlement' => 'Cash receipt changed. Refresh and review it.']);
            }

            return redirect()->route('logistics.settlements.index')->with('status', 'Recorded COD cash receipt confirmed.');
        });
    }

    /** Logistics records manual ShopHop remittance; Admin must independently confirm receipt. */
    public function remitToAdmin(Request $request, int $delivery): RedirectResponse
    {
        $data = $request->validate(['reference' => ['required', 'string', 'min:4', 'max:120']]);
        $partner = $this->partner($request);
        DB::transaction(function () use ($partner, $delivery, $data, $request) {
            $cash = CodSettlement::where('delivery_id', $delivery)
                ->where('logistics_partner_id', $partner->id)->lockForUpdate()->firstOrFail();
            \App\Services\CodDiscrepancyService::assertCollectionClear((int) $cash->id);
            if ($cash->status !== CodSettlement::RECONCILED || ! $cash->reconciled_at
                || $cash->admin_remitted_at || $cash->admin_received_at
                || abs((float) $cash->received_amount - (float) $cash->expected_amount) > .001) {
                throw ValidationException::withMessages([
                    'settlement' => 'COD must be reconciled and not already remitted to ShopHop.']);
            }
            $cash->update(['admin_remittance_reference' => trim($data['reference']),
                'admin_remitted_at' => now()]);
            FinancialAudit::record('cod_collection', $cash->id, 'custody_sent_to_platform', 'user', $request->user()->id, ['amount' => $cash->received_amount, 'reference' => $data['reference']]);
        });

        return back()->with('status', 'Remittance to ShopHop recorded; pending Admin receipt confirmation.');
    }

    private function partner(Request $request): LogisticsPartner
    {
        // Approved role middleware and profile lookup jointly establish the receiving authority.
        return LogisticsPartner::where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
    }
}
