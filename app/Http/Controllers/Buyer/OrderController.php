<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Services\BuyerParcelJourney;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(BuyerParcelJourney $journey)
    {
        $buyer = Auth::user()->buyer;

        // Buyer cards read persisted delivery and collection facts scoped to the authenticated Buyer.
        $realOrders = Order::with(['items.product', 'items.variant', 'seller', 'delivery.rider', 'delivery.deliveredRider', 'delivery.partner', 'codSettlement'])
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->get();

        $journey->load($realOrders);
        $orders = $realOrders->map(function (Order $order) use ($journey) {
            return $this->mapOrder($order, $journey);
        });

        // Seller preparation remains in the Buyer's To Ship group while its exact persisted label stays visible.
        $orderCounts = [
            'all'        => $orders->count(),
            'to-pay'     => $orders->where('status_group', Order::STATUS_TO_PAY)->count(),
            'to-ship'    => $orders->where('status_group', Order::STATUS_TO_SHIP)->count(),
            'to-receive' => $orders->where('status', Order::STATUS_TO_RECEIVE)->count(),
            'completed'  => $orders->where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled'  => $orders->where('status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('buyer.orders.index', [
            'orders'      => $orders,
            'orderCounts' => $orderCounts,
        ]);
    }

    /** Map Buyer-owned order and parcel facts without altering fulfillment. */
    private function mapOrder(Order $order, BuyerParcelJourney $journey): array
    {
        $displayId = 'SHP-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

        $delivery = $order->delivery;
        // The current final-delivery handler differs from pickup and transit handlers.
        $rider = in_array($delivery?->status, ['delivery_assigned', 'out_for_delivery', 'delivery_failed'], true)
            ? $delivery?->rider
            : ($delivery?->status === 'delivered' ? $delivery->deliveredRider : null);

        return [
            'id' => $displayId,
            'raw_id' => $order->id,
            'status' => $order->status,
            // The Order owns the status mapping; no second Buyer fulfillment state is stored.
            'status_group' => $order->buyerStatusGroup(),
            'status_label' => $order->statusLabel(),
            'status_note' => $order->statusNote(),

            'shop' => $order->seller->business_name ?? 'Shop',
            'shop_slug' => $order->seller_id,
            'shop_preferred' => false,

            'placed_at' => BuyerParcelJourney::displayTime($order->getRawOriginal('created_at')),
            'payment' => $this->paymentLabel($order),
            // Kept as null so existing views reading this key do not break; there is no payment page anymore.
            'payment_url' => null,
            'shipping' => $order->shipping_method === 'express' ? 'Express Delivery' : 'Standard Delivery',

            // Only assigned Rider name and vehicle are exposed; no contact, plate, or ETA is inferred.
            'tracking_no' => $order->delivery?->tracking_code,
            'delivery_status' => $order->delivery?->status,
            'delivery_status_label' => $delivery?->statusLabel() ?? 'Awaiting logistics assignment',
            'delivered_at' => $delivery?->getRawOriginal('delivered_at')
                ? BuyerParcelJourney::displayTime($delivery->getRawOriginal('delivered_at')) : null,
            'can_confirm_receipt' => $order->status === Order::STATUS_TO_RECEIVE
                && $order->delivery?->status === \App\Models\Logistics\Delivery::DELIVERED,
            'courier' => $order->delivery?->partner?->company_name,
            'estimated_delivery' => 'Delivery estimate unavailable',
            'rider' => $rider ? [
                'name' => $rider->name,
                'vehicle' => $rider->vehicle_type,
                'status' => $delivery->statusLabel(),
            ] : null,

            'total' => (float) $order->total_amount,
            'shipping_fee' => (float) $order->shipping_fee,
            'voucher_discount' => (float) $order->voucher_discount,

            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product->name ?? 'Product',
                    'image' => ($item->variant_image_path ?? $item->product?->image)
                        ? Storage::url($item->variant_image_path ?? $item->product?->image)
                        : asset('images/products/placeholder.png'),
                    'variant' => $item->variantLabel(),
                    'price' => (float) $item->price,
                    'qty' => $item->quantity,
                ];
            })->values()->all(),

            'progress' => $journey->progress($order),

            'tracking_events' => $journey->events($order),
            // Private proof URLs work only for this Order's Buyer after server-side authorization.
            'delivery_proof' => $order->delivery?->proof_path ? [
                'photo' => route('delivery.proof', $order->delivery),
                'delivered_at' => BuyerParcelJourney::displayTime($delivery->getRawOriginal('delivered_at')),
                'uploaded_by' => $order->delivery->deliveredRider?->name ?? 'Rider',
                'received_by' => $order->status === Order::STATUS_COMPLETED
                    ? ($order->delivery_name ?: 'Buyer confirmed')
                    : 'Awaiting Buyer confirmation',
                'delivery_note' => 'Rider-submitted delivery photo.',
            ] : null,
            'can_report' => $order->canReport(),
        ];
    }

    public function confirmReceipt(Request $request, int $order): RedirectResponse
    {
        $buyer = $request->user()->buyer ?? abort(403, 'Buyer profile unavailable.');

        return DB::transaction(function () use ($buyer, $order) {
            $owned = Order::with('delivery')->where('buyer_id', $buyer->id)
                ->whereKey($order)->lockForUpdate()->firstOrFail();

            if ($owned->status !== Order::STATUS_TO_RECEIVE
                || $owned->delivery?->status !== \App\Models\Logistics\Delivery::DELIVERED
                || ! $owned->isPaymentEligible()) {
                return redirect()->route('buyer.orders')
                    ->withErrors(['order' => 'This order is not ready for receipt confirmation.']);
            }

            if (Order::whereKey($owned->id)->where('buyer_id', $buyer->id)
                ->where('status', Order::STATUS_TO_RECEIVE)
                ->update(['status' => Order::STATUS_COMPLETED]) !== 1) {
                return redirect()->route('buyer.orders')
                    ->withErrors(['order' => 'Order changed. Refresh and review it.']);
            }

            return redirect()->route('buyer.orders')->with('status', 'Order received and completed.');
        });
    }

    private function paymentLabel(Order $order): string
    {
        if ($order->payment_method === 'cod') {
            // Buyer sees only recorded collection, never internal remittance or inferred legacy payment.
            if ($order->codSettlement) {
                return 'Cash on Delivery · ₱'.number_format((float) $order->codSettlement->collected_amount, 2)
                    .' collected '.BuyerParcelJourney::displayTime($order->codSettlement->getRawOriginal('collected_at'));
            }
            if ($order->status === Order::STATUS_COMPLETED) {
                return 'Cash on Delivery · collection not recorded';
            }
            return 'Cash on Delivery';
        }

        // Historical non-COD rows have no verified payment record in this schema.
        return 'Online payment (no longer supported)';
    }
}
