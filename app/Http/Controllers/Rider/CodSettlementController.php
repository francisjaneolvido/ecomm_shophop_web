<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Logistics\CodSettlement;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CodSettlementController extends Controller
{
    public function index(Request $request): View
    {
        $rider = $this->eligible($request);
        // A former assignment cannot expose another Rider's recorded collection after reassignment.
        $deliveries = Delivery::with(['order.seller', 'codSettlement.collector'])
            ->where('logistics_partner_id', $rider->logistics_partner_id)
            ->whereHas('order', fn ($order) => $order->where('payment_method', 'cod'))
            ->where(function ($query) use ($rider) {
                $query->whereHas('codSettlement', fn ($cash) => $cash->where('collected_by_rider_id', $rider->id))
                    ->orWhere(function ($assigned) use ($rider) {
                        $assigned->where('rider_id', $rider->id)->whereDoesntHave('codSettlement');
                    });
            })->latest()->get();

        return view('rider.settlements', compact('deliveries'));
    }

    public function remit(Request $request, int $delivery): RedirectResponse
    {
        $rider = $this->eligible($request);

        return DB::transaction(function () use ($rider, $delivery) {
            // The collector and partner are locked from persisted records; browser money fields are ignored.
            $cash = CodSettlement::where('delivery_id', $delivery)
                ->where('collected_by_rider_id', $rider->id)
                ->where('logistics_partner_id', $rider->logistics_partner_id)
                ->lockForUpdate()->firstOrFail();
            $current = Rider::with('partner.user')->whereKey($rider->id)->lockForUpdate()->first();
            abort_unless($current && in_array($current->status, ['active', 'suspended'], true)
                && $current->logistics_partner_id === $cash->logistics_partner_id
                && $current->email && $current->password
                && $current->partner?->user?->status === 'approved', 403);
            if ($cash->status !== CodSettlement::COLLECTED || $cash->remitted_at) {
                return back()->withErrors(['settlement' => 'Remittance already changed. Refresh and review it.']);
            }
            if (CodSettlement::whereKey($cash->id)->where('status', CodSettlement::COLLECTED)
                ->whereNull('remitted_at')->update([
                    'status' => CodSettlement::REMITTED,
                    'remitted_by_rider_id' => $rider->id, 'remitted_at' => now(),
                ]) !== 1) {
                return back()->withErrors(['settlement' => 'Remittance already changed. Refresh and review it.']);
            }

            return redirect()->route('rider.settlements.index')->with('status', 'COD remittance submitted to Logistics.');
        });
    }

    private function eligible(Request $request): Rider
    {
        // Suspended Riders retain only this cash return lane; delivery actions still require active.rider.
        abort_if(Auth::guard('web')->check(), 403);
        $identity = $request->user('rider');
        abort_unless($identity instanceof Rider, 403);
        $rider = $identity->fresh(['partner.user']);
        abort_unless($rider && in_array($rider->status, ['active', 'suspended'], true)
            && $rider->email && $rider->password && $rider->partner?->user?->status === 'approved', 403);

        return $rider;
    }
}
