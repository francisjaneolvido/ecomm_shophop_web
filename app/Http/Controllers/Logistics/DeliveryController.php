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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function board(Request $request): View
    {
        $partner = $this->partner($request);
        // Unclaimed COD Orders are visible only inside registered destination coverage; claimed work is partner-owned.
        $ready = self::readyFor($partner);
        $deliveries = Delivery::with(['order.seller', 'order.items.product', 'rider'])
            ->where('logistics_partner_id', $partner->id)->latest()->get();
        $riders = Rider::where('logistics_partner_id', $partner->id)->where('status', 'active')->orderBy('name')->get();

        return view('logistics.delivery-board', compact('ready', 'deliveries', 'riders'));
    }

    public function assign(Request $request, int $order): RedirectResponse
    {
        $partner = $this->partner($request);
        $data = $request->validate(['rider_id' => ['required', 'integer']]);
        // Submitted Rider IDs are scoped to this partner and must still be available at assignment time.
        $rider = Rider::where('logistics_partner_id', $partner->id)
            ->where('status', 'active')->findOrFail($data['rider_id']);

        try {
            return DB::transaction(function () use ($partner, $rider, $order) {
                // The unique order_id constraint closes concurrent claims across partners and checkout groups grant no access.
                $ready = Order::whereKey($order)->lockForUpdate()->firstOrFail();
                if ($ready->status !== Order::STATUS_READY_FOR_PICKUP || $ready->payment_method !== 'cod'
                    || ! self::covers($partner, $ready) || Delivery::where('order_id', $order)->exists()
                    || ! Rider::whereKey($rider->id)->where('status', 'active')->exists()) {
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

    public function pickup(Request $request, int $order): RedirectResponse
    {
        return $this->transition($request, $order, 'assigned', 'picked_up', Order::STATUS_READY_FOR_PICKUP,
            Order::STATUS_TO_RECEIVE, 'picked_up_at');
    }

    public function transit(Request $request, int $order): RedirectResponse
    {
        return $this->transition($request, $order, 'picked_up', 'in_transit', Order::STATUS_TO_RECEIVE,
            Order::STATUS_TO_RECEIVE, 'in_transit_at');
    }

    public function complete(Request $request, int $order): RedirectResponse
    {
        return $this->transition($request, $order, 'in_transit', 'delivered', Order::STATUS_TO_RECEIVE,
            Order::STATUS_COMPLETED, 'delivered_at');
    }

    private function transition(Request $request, int $orderId, string $from, string $to, string $orderFrom,
        string $orderTo, string $timestamp): RedirectResponse
    {
        $partner = $this->partner($request);

        return DB::transaction(function () use ($partner, $orderId, $from, $to, $orderFrom, $orderTo, $timestamp) {
            // Only the assigned partner may establish pickup, transit, or completion after Seller readiness.
            $delivery = Delivery::where('logistics_partner_id', $partner->id)
                ->where('order_id', $orderId)->lockForUpdate()->firstOrFail();
            if ($delivery->status !== $from || ($from === 'assigned'
                && ! Rider::whereKey($delivery->rider_id)->where('logistics_partner_id', $partner->id)
                    ->where('status', 'active')->exists())) {
                return $this->stale();
            }
            // Both records change atomically; conditional predicates reject repeat and out-of-order requests.
            if (Order::whereKey($orderId)->where('status', $orderFrom)->where('payment_method', 'cod')
                    ->update(['status' => $orderTo]) !== 1
                || Delivery::whereKey($delivery->id)->where('status', $from)
                    ->update(['status' => $to, $timestamp => now()]) !== 1) {
                // Throwing rolls back an Order update if a competing request changed the Delivery row.
                throw ValidationException::withMessages(['delivery' => 'This delivery changed. Refresh and review it.']);
            }

            return redirect()->route('logistics.deliveries.board')->with('status', 'Delivery updated.');
        });
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
