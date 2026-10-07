<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index()
    {
        $buyer = Auth::user()->buyer;

        // Buyer cards read persisted delivery and collection facts scoped to the authenticated Buyer.
        $realOrders = Order::with(['items.product', 'items.variant', 'seller', 'delivery.rider', 'delivery.deliveredRider', 'delivery.partner', 'codSettlement', 'manualCashlessPayment'])
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->get();

        $orders = $realOrders->map(function (Order $order) {
            return $this->mapOrder($order);
        });

        // Seller preparation remains in the Buyer's To Ship group while its exact persisted label stays visible.
        $orderCounts = [
            'all'        => $orders->count(),
            // Cashless payment state, not only Order.status, controls the Buyer's To Pay tab.
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

    /** Map persisted order facts into the card; fulfillment and reporting have no backend records yet. */
    private function mapOrder(Order $order): array
    {
        $displayId = 'SHP-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

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

            'placed_at' => $order->created_at->format('M j, Y · g:i A'),
            'payment' => $this->paymentLabel($order),
            // Buyer returns to the same group payment for proof, rejection, and review status.
            'payment_url' => $order->payment_method === 'online' && $order->manualCashlessPayment
                ? route('buyer.payments.show', $order->manualCashlessPayment) : null,
            'shipping' => $order->shipping_method === 'express' ? 'Express Delivery' : 'Standard Delivery',

            // Only assigned Rider name and vehicle are exposed; no contact, plate, or ETA is inferred.
            'tracking_no' => $order->delivery?->tracking_code,
            'delivery_status' => $order->delivery?->status,
            'can_confirm_receipt' => $order->status === Order::STATUS_TO_RECEIVE
                && $order->delivery?->status === \App\Models\Logistics\Delivery::DELIVERED,
            'courier' => $order->delivery?->partner?->company_name,
            'estimated_delivery' => 'Delivery estimate unavailable',
            'rider' => $order->delivery?->rider ? [
                'name' => $order->delivery->rider->name,
                'vehicle' => $order->delivery->rider->vehicle_type,
                'plate' => 'Unavailable', 'phone' => 'Unavailable',
                'status' => ucfirst(str_replace('_', ' ', $order->delivery->status)),
            ] : null,

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
            'tracking_events' => $this->trackingEvents($order),
            // Private proof URLs work only for this Order's Buyer after server-side authorization.
            'delivery_proof' => $order->delivery?->proof_path ? [
                'photo' => route('delivery.proof', $order->delivery),
                'delivered_at' => $order->delivery->delivered_at?->format('M j, Y g:i A'),
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

    private function trackingEvents(Order $order): array
    {
        $events = [[
            'type' => 'done',
            'title' => 'Order placed',
            'description' => 'Your order was created successfully.',
            'location' => 'ShopHop',
            'date' => $order->created_at->format('M j, Y'),
            'time' => $order->created_at->format('g:i A'),
        ]];

        $delivery = $order->delivery;
        if (! $delivery) {
            return $events;
        }

        $milestones = [
            ['at' => $delivery->assigned_at, 'title' => 'Pickup Rider assigned', 'description' => 'Logistics assigned a Rider to collect the parcel.'],
            ['at' => $delivery->picked_up_at, 'title' => 'Picked up from Seller', 'description' => 'The parcel was collected for transfer to the sorting center.'],
            ['at' => $delivery->at_sorting_center_at, 'title' => 'At sorting center', 'description' => 'The parcel was scanned and received by Logistics.'],
            ['at' => $delivery->sorted_at, 'title' => 'Sorted', 'description' => 'The parcel was sorted according to its destination area.'],
            ['at' => $delivery->delivery_assigned_at, 'title' => 'Delivery Rider assigned', 'description' => 'A Rider was assigned for final delivery.'],
            ['at' => $delivery->out_for_delivery_at, 'title' => 'Out for delivery', 'description' => 'The parcel is on the way to the delivery address.'],
            ['at' => $delivery->delivered_at, 'title' => 'Delivered', 'description' => 'The Rider submitted delivery proof. Confirm receipt to complete the order.'],
            ['at' => $delivery->delivery_failed_at, 'title' => 'Delivery failed', 'description' => $delivery->failure_reason ?: 'A delivery attempt was unsuccessful.'],
            ['at' => $delivery->returned_at, 'title' => 'Returned', 'description' => 'The parcel was marked for return to the Seller.'],
        ];

        foreach ($milestones as $milestone) {
            if (! $milestone['at']) {
                continue;
            }
            $events[] = [
                'type' => 'done',
                'title' => $milestone['title'],
                'description' => $milestone['description'],
                'location' => 'ShopHop Logistics',
                'date' => $milestone['at']->format('M j, Y'),
                'time' => $milestone['at']->format('g:i A'),
            ];
        }

        return $events;
    }

    private function paymentLabel(Order $order): string
    {
        if ($order->payment_method === 'cod') {
            // Buyer sees only recorded collection, never internal remittance or inferred legacy payment.
            if ($order->codSettlement) {
                return 'Cash on Delivery · ₱'.number_format((float) $order->codSettlement->collected_amount, 2)
                    .' collected '.$order->codSettlement->collected_at->format('M j, Y g:i A');
            }
            if ($order->status === Order::STATUS_COMPLETED) {
                return 'Cash on Delivery · collection not recorded';
            }
            return 'Cash on Delivery';
        }

        // Only a persisted Admin-reviewed group payment can describe Online Payment as verified.
        if ($order->payment_method === 'online' && $order->manualCashlessPayment
            && (int) $order->manualCashlessPayment->buyer_id === (int) $order->buyer_id) {
            return $order->manualCashlessPayment->status === 'verified'
                ? 'Online Payment · Verified by ShopHop Admin'
                : 'Online Payment · '.$order->statusLabel();
        }
        // Historical GCash rows have no verified provider result in this schema.
        return 'GCash · Verification unavailable';
    }
}
