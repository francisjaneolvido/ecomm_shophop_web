<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logistics\CodSettlement;
use App\Services\FinancialAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CodRemittanceController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $search = trim($data['q'] ?? '');
        $status = in_array($request->query('status'), ['awaiting', 'with_logistics', 'received'], true) ? $request->query('status') : 'all';
        $query = CodSettlement::with(['order.settlements', 'order.financial', 'partner', 'remitter', 'reconciler'])
            ->where('status', CodSettlement::RECONCILED);
        $counts = [
            'all' => (clone $query)->count(),
            'awaiting' => (clone $query)->whereNotNull('admin_remitted_at')->whereNull('admin_received_at')->count(),
            'with_logistics' => (clone $query)->whereNull('admin_remitted_at')->whereNull('admin_received_at')->count(),
            'received' => (clone $query)->whereNotNull('admin_received_at')->count(),
        ];
        if ($status === 'awaiting') $query->whereNotNull('admin_remitted_at')->whereNull('admin_received_at');
        if ($status === 'with_logistics') $query->whereNull('admin_remitted_at')->whereNull('admin_received_at');
        if ($status === 'received') $query->whereNotNull('admin_received_at');
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('order_id', 'like', '%'.$search.'%')
                ->orWhere('admin_remittance_reference', 'like', '%'.$search.'%')
                ->orWhereHas('partner', fn ($partner) => $partner->where('company_name', 'like', '%'.$search.'%')));
        }
        $settlements = $query->orderByDesc('id')->paginate(30)->withQueryString();
        $openCases = DB::table('cod_discrepancy_cases')->whereIn('cod_settlement_id', $settlements->pluck('id'))
            ->where('status', '!=', 'resolved')->get()->keyBy('cod_settlement_id');

        return view('admin.finance.cod-remittances', compact('settlements', 'openCases', 'status', 'counts', 'search'));
    }

    /** Distinct Admin confirmation. Screenshot/reference alone is not proof of receipt. */
    public function confirm(Request $request, int $settlement): RedirectResponse
    {
        $data = $request->validate(['reference' => ['required', 'string', 'max:120'],
            'received_amount' => ['required', 'decimal:0,2', 'min:0', 'max:999999999']]);
        DB::transaction(function () use ($request, $settlement, $data) {
            $cash = CodSettlement::whereKey($settlement)->lockForUpdate()->firstOrFail();
            \App\Services\CodDiscrepancyService::assertCollectionClear((int) $cash->id);
            if ($cash->status !== CodSettlement::RECONCILED || ! $cash->admin_remitted_at
                || ! $cash->admin_remittance_reference || $cash->admin_received_at
                || $cash->admin_remittance_reference !== trim($data['reference'])
                || abs((float) $cash->expected_amount - (float) $cash->received_amount) > .001
                || abs((float) $data['received_amount'] - (float) $cash->expected_amount) > .001) {
                throw ValidationException::withMessages([
                    'settlement' => 'Remittance reference, amount or state differs. Check the actual received funds first.']);
            }
            $cash->update(['admin_received_amount' => $cash->expected_amount,
                'admin_received_at' => now(), 'admin_received_by_user_id' => $request->user()->id]);
            FinancialAudit::record('cod_collection', $cash->id, 'custody_received_by_platform', 'user', $request->user()->id, ['amount' => $cash->expected_amount, 'reference' => $data['reference']]);
        });

        return back()->with('status', 'ShopHop Admin confirmed COD receipt. Next: prepare and distribute the settlement; no beneficiary payout is automatic.');
    }
}
