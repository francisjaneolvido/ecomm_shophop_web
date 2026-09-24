<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        // Seller Order ownership uses sellers.id; a shared checkout_group_id never grants another shop access.
        $orders = $this->ordersFor($this->seller($request))
            ->with(['buyer', 'items.product', 'items.variant'])
            ->latest()->get();

        return view('seller.orders.notifications', compact('orders'));
    }

    public function show(Request $request, int $order): View
    {
        // Resolve through the authenticated Seller profile before exposing any Order or Buyer details.
        $ownedOrder = $this->ownedOrder($request, $order);
        $ownedOrder->load(['buyer', 'items.product', 'items.variant']);

        return view('seller.orders.show', ['order' => $ownedOrder]);
    }

    public function prepare(Request $request): View
    {
        // The preparation queue contains only new and in-progress Seller-owned Orders.
        $orders = $this->ordersFor($this->seller($request))
            ->whereIn('status', [Order::STATUS_TO_SHIP, Order::STATUS_PREPARING])
            ->with(['buyer', 'items.product', 'items.variant'])
            ->latest()->get();

        return view('seller.orders.prepare', compact('orders'));
    }

    public function courier(Request $request): View
    {
        // Ready means Seller preparation ended; assignment, pickup, and tracking require Logistics state.
        $orders = $this->ordersFor($this->seller($request))
            ->where('status', Order::STATUS_READY_FOR_PICKUP)
            ->with(['buyer', 'items.product', 'items.variant'])
            ->latest()->get();

        return view('seller.orders.courier', compact('orders'));
    }

    public function confirm(Request $request): View
    {
        // Even a read-only Seller page requires the real profile; delivery proof remains Logistics-owned.
        $this->seller($request);
        return view('seller.orders.confirm');
    }

    public function dashboard(Request $request): View
    {
        // Only fulfillment indicators backed by this Seller's persisted Orders replace dashboard defaults.
        $orders = $this->ordersFor($this->seller($request));
        $counts = (clone $orders)->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');
        // The existing monthly Order count also has a direct Seller-owned persisted source.
        $monthlyOrderCount = (clone $orders)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $recentOrders = (clone $orders)->with('buyer')->withCount('items')
            ->latest()->limit(6)->get()->map(fn (Order $order) => [
                'id' => $order->id,
                'buyer_name' => trim(($order->buyer?->first_name ?? '') . ' ' . ($order->buyer?->last_name ?? '')),
                'items_count' => $order->items_count,
                'total' => $order->total_amount,
                'status' => $order->status,
                // Dashboard text follows the Order's shared Buyer/Seller status contract.
                'status_label' => $order->statusLabel(),
                'placed_at' => $order->created_at->format('M j, Y g:i A'),
            ]);

        return view('seller.dashboard', [
            'newOrders' => (int) ($counts[Order::STATUS_TO_SHIP] ?? 0),
            'ordersToPrepare' => (int) ($counts[Order::STATUS_PREPARING] ?? 0),
            'readyForPickup' => (int) ($counts[Order::STATUS_READY_FOR_PICKUP] ?? 0),
            'monthlyOrderCount' => $monthlyOrderCount,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function startPreparation(Request $request, int $order): RedirectResponse
    {
        // COD placement begins at to-ship; preparation is the first Seller-controlled step.
        return $this->transition($request, $order, Order::STATUS_TO_SHIP, Order::STATUS_PREPARING);
    }

    public function markReady(Request $request, int $order): RedirectResponse
    {
        // Readiness ends the Seller-owned lifecycle; pickup and delivery belong to Logistics.
        return $this->transition($request, $order, Order::STATUS_PREPARING, Order::STATUS_READY_FOR_PICKUP);
    }

    private function transition(Request $request, int $orderId, string $expected, string $next): RedirectResponse
    {
        $order = $this->ownedOrder($request, $orderId);

        // GCash lacks verified payment, and a guarded update rejects repeats, skips, and stale competing requests.
        if ($order->payment_method !== 'cod' || $this->ordersFor($this->seller($request))
            ->whereKey($order->id)->where('status', $expected)
            ->where('payment_method', 'cod')->update(['status' => $next]) !== 1) {
            return redirect()->route('seller.orders.show', $order)
                ->withErrors(['status' => 'This Order cannot move from its current persisted state. Refresh and review it.']);
        }

        // Checkout already deducted Product and Variant stock and recorded prices, totals, vouchers, and Cart state.
        return redirect()->route('seller.orders.show', $order)
            ->with('status', 'Order status updated.');
    }

    private function ownedOrder(Request $request, int $orderId): Order
    {
        // The primary key is scoped by sellers.id, never Product's users.id or checkout_group_id.
        return $this->ordersFor($this->seller($request))->findOrFail($orderId);
    }

    private function ordersFor(Seller $seller)
    {
        // The Seller profile is the authoritative Order owner for all reads and mutations.
        return Order::where('seller_id', $seller->id);
    }

    private function seller(Request $request): Seller
    {
        // Approved role middleware alone does not guarantee a Seller profile exists.
        return $request->user()->seller ?? abort(403, 'Seller profile unavailable.');
    }
}
