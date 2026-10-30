<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Logistics\Delivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** Read the same Order records used by the web Buyer Orders page. */
class MobileBuyerOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);
        $orders = Order::query()
            ->where('buyer_id', $buyer->id)
            ->with(['seller', 'items.product', 'items.variant', 'delivery', 'returnCase'])
            ->latest('id')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders->getCollection()->map(fn (Order $order) => $this->resource($order))->values(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'has_more' => $orders->hasMorePages(),
            ],
        ]);
    }

    public function confirmReceipt(Request $request, int $order): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);
        DB::transaction(function () use ($buyer, $order) {
            $owned = Order::with('delivery')
                ->where('buyer_id', $buyer->id)
                ->whereKey($order)
                ->lockForUpdate()
                ->firstOrFail();

            if ($owned->status !== Order::STATUS_TO_RECEIVE
                || $owned->delivery?->status !== Delivery::DELIVERED
                || ! $owned->isPaymentEligible()) {
                throw ValidationException::withMessages([
                    'order' => 'This order is not yet eligible for receipt confirmation.',
                ]);
            }

            $updated = Order::whereKey($owned->id)->where('buyer_id', $buyer->id)
                ->where('status', Order::STATUS_TO_RECEIVE)
                ->update(['status' => Order::STATUS_COMPLETED]);

            if ($updated !== 1) {
                throw ValidationException::withMessages(['order' => 'Order changed. Refresh and review it.']);
            }
        });

        return response()->json(['success' => true, 'message' => 'Order received and completed.']);
    }

    private function resource(Order $order): array
    {
        $delivery = $order->delivery;
        $timeline = [
            ['label' => 'Order placed', 'at' => $order->created_at?->toIso8601String()],
        ];
        if ($delivery) {
            foreach ([
                'assigned_at' => 'Pickup rider assigned',
                'picked_up_at' => 'Picked up from seller',
                'at_sorting_center_at' => 'At sorting center',
                'sorted_at' => 'Sorted',
                'delivery_assigned_at' => 'Delivery rider assigned',
                'out_for_delivery_at' => 'Out for delivery',
                'delivered_at' => 'Delivered',
                'delivery_failed_at' => 'Delivery attempt failed',
                'returned_at' => 'Returned',
            ] as $field => $label) {
                if ($delivery->{$field}) {
                    $timeline[] = ['label' => $label, 'at' => $delivery->{$field}->toIso8601String()];
                }
            }
        }

        return [
            'id' => (int) $order->id,
            'display_id' => 'SHP-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'seller_name' => $order->seller?->business_name ?? 'ShopHop Seller',
            'status' => $order->status,
            'status_label' => $order->statusLabel(),
            'status_note' => $order->statusNote(),
            'delivery_status' => $delivery?->status,
            'tracking_code' => $delivery?->tracking_code,
            'return_status' => $order->returnCase?->status,
            'refund_status' => $order->returnCase?->refund_status,
            'refund_amount' => (float) ($order->returnCase?->refund_amount ?? 0),
            'failure_reason' => $delivery?->status === Delivery::DELIVERY_FAILED ? $delivery->failure_reason : null,
            'total_amount' => (float) $order->total_amount,
            'shipping_fee' => (float) $order->shipping_fee,
            'voucher_discount' => (float) $order->voucher_discount,
            'payment_method' => $order->payment_method,
            'delivery_address' => $order->delivery_address,
            'placed_at' => $order->created_at?->toIso8601String(),
            'can_confirm_receipt' => $order->status === Order::STATUS_TO_RECEIVE
                && $delivery?->status === Delivery::DELIVERED
                && $order->isPaymentEligible(),
            'items' => $order->items->map(fn ($item) => [
                'product_id' => (int) $item->product_id,
                'name' => $item->product?->name ?? 'Product',
                'variant' => $item->variantLabel(),
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
                'image' => $item->product?->image ? asset('storage/' . ltrim($item->product->image, '/')) : null,
            ])->values(),
            'timeline' => $timeline,
        ];
    }

    private function approvedBuyer(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401, 'Unauthenticated.');
        abort_unless($user->account_type === 'buyer', 403, 'Buyer account required.');
        abort_unless($user->email_verified_at && $user->status === 'approved' && $user->buyer,
            403, 'Approved verified Buyer account required.');
        return $user->buyer;
    }
}