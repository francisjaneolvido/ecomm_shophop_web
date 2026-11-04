<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\LogisticsPartner;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CodSettlementController extends Controller
{
    public function index(Request $request): View
    {
        $partner = $this->partner($request);
        $data = $request->validate(['q' => ['nullable','string','max:100'],
            'status' => ['nullable','in:collected,remitted,reconciled,admin_pending,admin_received,none']]);
        $search = trim($data['q'] ?? '');
        $status = $data['status'] ?? '';
        $base = Delivery::where('logistics_partner_id', $partner->id)
            ->whereHas('order', fn ($q) => $q->where('payment_method', 'cod'))
            ->where('status', Delivery::DELIVERED);
        $stats = [
            'collected' => (clone $base)->whereHas('codSettlement', fn ($q) => $q->where('status', CodSettlement::COLLECTED))->count(),
            'remitted' => (clone $base)->whereHas('codSettlement', fn ($q) => $q->where('status', CodSettlement::REMITTED))->count(),
            'reconciled' => (clone $base)->whereHas('codSettlement', fn ($q) => $q->where('status', CodSettlement::RECONCILED)->whereNull('admin_remitted_at'))->count(),
            'admin_pending' => (clone $base)->whereHas('codSettlement', fn ($q) => $q->whereNotNull('admin_remitted_at')->whereNull('admin_received_at'))->count(),
            'admin_received' => (clone $base)->whereHas('codSettlement', fn ($q) => $q->whereNotNull('admin_received_at'))->count(),
        ];
        if ($status === 'none') $base->whereDoesntHave('codSettlement');
        elseif ($status === 'admin_pending') $base->whereHas('codSettlement', fn ($q) => $q->whereNotNull('admin_remitted_at')->whereNull('admin_received_at'));
        elseif ($status === 'admin_received') $base->whereHas('codSettlement', fn ($q) => $q->whereNotNull('admin_received_at'));
        elseif ($status === 'reconciled') $base->whereHas('codSettlement', fn ($q) => $q->where('status', CodSettlement::RECONCILED)->whereNull('admin_remitted_at'));
        elseif ($status !== '') $base->whereHas('codSettlement', fn ($q) => $q->where('status', $status));
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', '%'.$search.'%')
                    ->orWhereHas('order.seller', fn ($s) => $s->where('business_name', 'like', '%'.$search.'%'))
                    ->orWhereHas('rider', fn ($r) => $r->where('name', 'like', '%'.$search.'%'));
                if (ctype_digit($search)) $q->orWhere('order_id', (int) $search);
            });
        }
        $deliveries = $base->with(['order.seller','rider','codSettlement.collector','codSettlement.remitter'])
            ->latest()->paginate(25)->withQueryString();
        return view('logistics.settlements.index', compact('deliveries','stats','search','status'));
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
        $data = $request->validate(['reference' => ['required','string','min:4','max:120']]);
        $partner = $this->partner($request);
        DB::transaction(function () use ($partner, $delivery, $data) {
            $cash = CodSettlement::where('delivery_id', $delivery)
                ->where('logistics_partner_id', $partner->id)->lockForUpdate()->firstOrFail();
            if ($cash->status !== CodSettlement::RECONCILED || ! $cash->reconciled_at
                || $cash->admin_remitted_at || $cash->admin_received_at
                || abs((float) $cash->received_amount - (float) $cash->expected_amount) > .001) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'settlement' => 'COD must be reconciled and not already remitted to ShopHop.']);
            }
            $cash->update(['admin_remittance_reference' => trim($data['reference']),
                'admin_remitted_at' => now()]);
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
