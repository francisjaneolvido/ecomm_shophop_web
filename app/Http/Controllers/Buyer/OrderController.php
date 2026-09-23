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

        $orderCounts = [
            'all'        => $orders->count(),
            'to-pay'     => $orders->where('status', Order::STATUS_TO_PAY)->count(),
            'to-ship'    => $orders->where('status', Order::STATUS_TO_SHIP)->count(),
            'to-receive' => $orders->where('status', Order::STATUS_TO_RECEIVE)->count(),
            'completed'  => $orders->where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled'  => $orders->where('status', Order::STATUS_CANCELLED)->count(),
            'reported'   => 0,
        ];

        return view('buyer.orders.index', [
            'orders'      => $orders,
            'orderCounts' => $orderCounts,
        ]);
    }

    /**
     * Map a real Order model into the array shape the order card view expects.
     *
     * NOTE: Courier / rider / tracking-events / delivery-proof fields are
     * TEMPORARY HARDCODED PLACEHOLDERS. There are no matching columns in the
     * `orders` table yet. Once courier/rider integration exists, replace the
     * placeholder blocks below with real relations/columns.
     */
    private function mapOrder(Order $order): array
    {
        $displayId = 'SHP-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

        $isTrackable = in_array($order->status, [Order::STATUS_TO_RECEIVE, Order::STATUS_COMPLETED], true);

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

            // --- PLACEHOLDER: no courier/tracking columns yet ---
            'tracking_no' => $isTrackable ? 'SPXPH0' . str_pad((string) $order->id, 9, '0', STR_PAD_LEFT) : null,
            'courier' => $isTrackable ? 'SPX Express' : null,
            'estimated_delivery' => $this->estimatedDeliveryLabel($order),

            'rider' => $order->status === Order::STATUS_TO_RECEIVE ? [
                'name' => 'Rider assignment pending',
                'phone' => '—',
                'vehicle' => 'Motorcycle',
                'plate' => '—',
                'status' => 'Out for delivery',
            ] : null,
            // --- END PLACEHOLDER ---

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

            // --- PLACEHOLDER: static demo tracking history ---
            'tracking_events' => $this->placeholderTrackingEvents($order),

            'delivery_proof' => $order->status === Order::STATUS_COMPLETED ? [
                'photo' => optional($order->items->first()?->product)->image
                    ? Storage::url($order->items->first()->product->image)
                    : asset('images/products/placeholder.png'),
                'delivered_at' => $order->updated_at->format('M j, Y · g:i A'),
                'received_by' => 'Buyer / Household Member',
                'delivery_note' => 'Parcel handed over at the delivery address.',
                'uploaded_by' => 'Courier partner',
            ] : null,
            // --- END PLACEHOLDER ---

            'can_report' => $order->canReport(),
        ];
    }

    private function paymentLabel(Order $order): string
    {
        if ($order->payment_method === 'cod') {
            return 'Cash on Delivery';
        }

        return $order->status === Order::STATUS_TO_PAY
            ? 'GCash · Pending Verification'
            : 'GCash · Verified';
    }

    private function estimatedDeliveryLabel(Order $order): string
    {
        return match ($order->status) {
            Order::STATUS_TO_PAY => 'After payment verification',
            Order::STATUS_TO_SHIP => 'Awaiting courier pickup',
            Order::STATUS_TO_RECEIVE => 'Estimated within 2–5 days',
            Order::STATUS_COMPLETED => 'Delivered ' . $order->updated_at->format('M j'),
            Order::STATUS_CANCELLED => 'Cancelled',
            default => '—',
        };
    }

    private function placeholderTrackingEvents(Order $order): array
    {
        $placed = [
            'type' => 'done',
            'title' => 'Order placed',
            'description' => 'Your order was created successfully.',
            'location' => 'ShopHop',
            'date' => $order->created_at->format('M j, Y'),
            'time' => $order->created_at->format('g:i A'),
        ];

        return match ($order->status) {
            Order::STATUS_TO_PAY => [
                [
                    'type' => 'current',
                    'title' => 'Payment verification pending',
                    'description' => 'ShopHop is checking your submitted GCash reference and proof of payment.',
                    'location' => 'ShopHop Payment Review',
                    'date' => $order->created_at->format('M j, Y'),
                    'time' => $order->created_at->format('g:i A'),
                ],
                $placed,
            ],
            Order::STATUS_TO_SHIP => [
                [
                    'type' => 'current',
                    'title' => 'Seller is preparing your parcel',
                    'description' => 'Your order is being packed for courier pickup.',
                    'location' => $order->seller->business_name ?? 'Seller',
                    'date' => $order->created_at->format('M j, Y'),
                    'time' => $order->created_at->format('g:i A'),
                ],
                $placed,
            ],
            Order::STATUS_TO_RECEIVE => [
                [
                    'type' => 'current',
                    'title' => 'Out for delivery',
                    'description' => 'Your parcel has left the local delivery hub and is on the way to your address.',
                    'location' => 'Local Delivery Hub',
                    'date' => now()->format('M j, Y'),
                    'time' => now()->format('g:i A'),
                ],
                [
                    'type' => 'done',
                    'title' => 'Parcel picked up from seller',
                    'description' => 'Courier collected the parcel from the seller.',
                    'location' => $order->seller->business_name ?? 'Seller',
                    'date' => $order->created_at->format('M j, Y'),
                    'time' => $order->created_at->format('g:i A'),
                ],
                $placed,
            ],
            Order::STATUS_COMPLETED => [
                [
                    'type' => 'delivered',
                    'title' => 'Parcel delivered',
                    'description' => 'Delivery completed successfully.',
                    'location' => 'Delivery Address',
                    'date' => $order->updated_at->format('M j, Y'),
                    'time' => $order->updated_at->format('g:i A'),
                ],
                $placed,
            ],
            Order::STATUS_CANCELLED => [
                [
                    'type' => 'cancelled',
                    'title' => 'Order cancelled',
                    'description' => 'This order was cancelled.',
                    'location' => 'ShopHop',
                    'date' => $order->updated_at->format('M j, Y'),
                    'time' => $order->updated_at->format('g:i A'),
                ],
                $placed,
            ],
            default => [$placed],
        };
    }
}