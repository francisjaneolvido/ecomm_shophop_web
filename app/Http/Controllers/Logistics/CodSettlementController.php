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
        // The existing Delivery owner scopes collected, remitted, reconciled, and legacy records alike.
        $deliveries = Delivery::with(['order.seller', 'rider', 'codSettlement.collector', 'codSettlement.remitter'])
            ->where('logistics_partner_id', $partner->id)
            ->whereHas('order', fn ($order) => $order->where('payment_method', 'cod'))
            ->where('status', 'delivered')->latest()->get();

        return view('logistics.settlements', compact('deliveries'));
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

    private function partner(Request $request): LogisticsPartner
    {
        // Approved role middleware and profile lookup jointly establish the receiving authority.
        return LogisticsPartner::where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
    }
}
