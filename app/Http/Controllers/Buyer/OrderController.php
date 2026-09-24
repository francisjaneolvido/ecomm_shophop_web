<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index()
    {
        $buyer = Auth::user()->buyer;

        $realOrders = Order::with(['items.product', 'items.variant', 'seller'])
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->get();

        $orders = $realOrders->map(function (Order $order) {
            return $this->mapOrder($order);
        });

        // Count only persisted Order statuses; issue reports have no backend state.
        $orderCounts = [
            'all'        => $orders->count(),
            'to-pay'     => $orders->where('status', Order::STATUS_TO_PAY)->count(),
            'to-ship'    => $orders->where('status', Order::STATUS_TO_SHIP)->count(),
            'to-receive' => $orders->where('status', Order::STATUS_TO_RECEIVE)->count(),
            'completed'  => $orders->where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled'  => $orders->where('status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('buyer.orders.index', [
            'orders'      => $orders,
            'orderCounts' => $orderCounts,
        ]);
    }

    /** Map persisted order facts into the card; fulfillment and reporting have no backend records yet. */
    private function mapOrder(Order $order): array
    {
        $displayId = 'SHP-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

        return [
            'id' => $displayId,
            'raw_id' => $order->id,
            'status' => $order->status,
            'status_label' => $order->statusLabel(),
            'status_note' => $order->statusNote(),

            'shop' => $order->seller->business_name ?? 'Shop',
            'shop_slug' => $order->seller_id,
            'shop_preferred' => false,

            'placed_at' => $order->created_at->format('M j, Y · g:i A'),
            'payment' => $this->paymentLabel($order),
            'shipping' => $order->shipping_method === 'express' ? 'Express Delivery' : 'Standard Delivery',

            // No courier, rider, or delivery estimate is persisted by this milestone.
            'tracking_no' => null,
            'courier' => null,
            'estimated_delivery' => 'Delivery estimate unavailable',
            'rider' => null,

            'total' => (float) $order->total_amount,
            'shipping_fee' => (float) $order->shipping_fee,
            'voucher_discount' => (float) $order->voucher_discount,

            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product->name ?? 'Product',
                    'image' => $item->product && $item->product->image
                        ? Storage::url($item->product->image)
                        : asset('images/products/placeholder.png'),
                    'variant' => $item->variantLabel(),
                    'price' => (float) $item->price,
                    'qty' => $item->quantity,
                ];
            })->values()->all(),

            'progress' => $order->progressSteps(),

            // Only order creation has an observed timestamp; tracking, proof, and reporting remain unavailable.
            'tracking_events' => [[
                'type' => 'done',
                'title' => 'Order placed',
                'description' => 'Your order was created successfully.',
                'location' => 'ShopHop',
                'date' => $order->created_at->format('M j, Y'),
                'time' => $order->created_at->format('g:i A'),
            ]],
            'delivery_proof' => null,
            'can_report' => $order->canReport(),
        ];
    }

    private function paymentLabel(Order $order): string
    {
        if ($order->payment_method === 'cod') {
            return 'Cash on Delivery';
        }

        // Historical GCash rows have no verified provider result in this schema.
        return 'GCash · Verification unavailable';
    }
}
