<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use App\Models\Logistics\Rider;
use App\Models\LogisticsPartner;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function board(Request $request): View
    {
        $partner = $this->partner($request);
        // Unclaimed COD Orders are visible only inside registered destination coverage; claimed work is partner-owned.
        $ready = self::readyFor($partner);
        // Actor relations render the event performer, not a potentially later assignment label.
        $deliveries = Delivery::with(['order.seller', 'order.items.product', 'rider', 'pickupRider', 'deliveredRider'])
            ->where('logistics_partner_id', $partner->id)->latest()->get();
        // Assignment offers only Riders with active, provisioned identities capable of owning the next event.
        $riders = Rider::where('logistics_partner_id', $partner->id)->where('status', 'active')
            ->whereNotNull('email')->whereNotNull('password')->orderBy('name')->get();

        return view('logistics.delivery-board', compact('ready', 'deliveries', 'riders'));
    }

    public function assign(Request $request, int $order): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate(['rider_id' => ['required', 'integer']]);
        // Submitted Rider IDs are scoped to this partner and must still be available at assignment time.
        $rider = Rider::where('logistics_partner_id', $partner->id)
            ->where('status', 'active')->whereNotNull('email')->whereNotNull('password')
            ->findOrFail($data['rider_id']);

        try {
            return DB::transaction(function () use ($partner, $rider, $order) {
                // The unique order_id constraint closes concurrent claims across partners and checkout groups grant no access.
                $ready = Order::whereKey($order)->lockForUpdate()->firstOrFail();
                if ($ready->status !== Order::STATUS_READY_FOR_PICKUP || $ready->payment_method !== 'cod'
                    || ! self::covers($partner, $ready) || Delivery::where('order_id', $order)->exists()
                    || ! Rider::whereKey($rider->id)->where('status', 'active')
                        ->whereNotNull('email')->whereNotNull('password')->exists()) {
                    return $this->stale();
                }
                Delivery::create([
                    'order_id' => $ready->id, 'logistics_partner_id' => $partner->id,
                    'rider_id' => $rider->id, 'status' => 'assigned', 'assigned_at' => now(),
                ]);

                return redirect()->route('logistics.deliveries.board')->with('status', 'Rider assigned.');
            });
        } catch (QueryException $exception) {
            // A competing insert can win after the eligibility read; report the conflict without replacing it.
            if (Delivery::where('order_id', $order)->exists()) {
                return $this->stale();
            }
            throw $exception;
        }
    }

    public static function readyFor(LogisticsPartner $partner): Collection
    {
        // Dashboard and Board share one eligibility rule so their waiting counts cannot diverge.
        return Order::with(['seller', 'items.product'])
            ->where('status', Order::STATUS_READY_FOR_PICKUP)->where('payment_method', 'cod')
            ->whereNotIn('id', Delivery::select('order_id'))->latest()->get()
            ->filter(fn (Order $order) => self::covers($partner, $order));
    }

    private static function covers(LogisticsPartner $partner, Order $order): bool
    {
        // Checkout snapshots a comma-separated address; compare its final province/city against registration coverage.
        $parts = array_map('trim', explode(',', (string) $order->delivery_address));
        if (count($parts) < 3) {
            return false;
        }
        $province = $parts[count($parts) - 1];
        $city = $parts[count($parts) - 2];

        return $partner->coverageAreas->contains(function ($area) use ($province, $city) {
            $cities = trim((string) $area->cities);
            return $area->area_type === 'province' && strcasecmp($area->area_name, $province) === 0
                && ($cities === '' || strcasecmp($cities, 'ALL') === 0
                    || collect(explode('|', $cities))->contains(fn ($value) => strcasecmp(trim($value), $city) === 0));
        });
    }

    private function partner(Request $request): LogisticsPartner
    {
        // Role middleware and profile ownership are separate checks; no request body may choose a partner.
        return LogisticsPartner::with('coverageAreas')->where('user_id', $request->user()->id)->first()
            ?? abort(403, 'Logistics profile unavailable.');
    }

    private function stale(): RedirectResponse
    {
        return redirect()->route('logistics.deliveries.board')
            ->withErrors(['delivery' => 'This delivery changed or is not eligible. Refresh and review it.']);
    }
}
