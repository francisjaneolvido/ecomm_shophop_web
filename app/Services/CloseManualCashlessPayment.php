<?php

namespace App\Services;

use App\Models\Buyer\Order\ManualCashlessPayment;
use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Order\OrderItem;
use App\Models\Logistics\Delivery;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CloseManualCashlessPayment
{
    public function close(int $paymentId, string $terminalStatus, ?int $actorUserId = null, ?string $reason = null): bool
    {
        // Payment is the serialization point for proof, review, cancellation, expiry, and Seller release.
        return DB::transaction(function () use ($paymentId, $terminalStatus, $actorUserId, $reason) {
            $payment = ManualCashlessPayment::whereKey($paymentId)->lockForUpdate()->firstOrFail();
            if ($terminalStatus === ManualCashlessPayment::EXPIRED) {
                // A submitted proof pauses expiry; another worker may have already closed the row.
                if (! in_array($payment->status, [ManualCashlessPayment::AWAITING_PROOF, ManualCashlessPayment::REJECTED], true)
                    || ! $payment->expires_at || $payment->expires_at->isFuture()) {
                    return false;
                }
            } elseif ($terminalStatus !== ManualCashlessPayment::CANCELLED
                || ! in_array($payment->status, [ManualCashlessPayment::AWAITING_PROOF,
                    ManualCashlessPayment::PENDING_REVIEW, ManualCashlessPayment::REJECTED], true)
                || ! $actorUserId) {
                throw $this->stale();
            }

            // Explicit links define the group; the UUID query detects missing or foreign links before any write.
            $orders = Order::where('manual_cashless_payment_id', $payment->id)
                ->orderBy('id')->lockForUpdate()->get();
            $group = Order::where('checkout_group_id', $payment->checkout_group_id)
                ->orderBy('id')->lockForUpdate()->get();
            if ($orders->isEmpty() || $orders->pluck('id')->all() !== $group->pluck('id')->all()
                || $orders->contains(fn (Order $order) => (int) $order->buyer_id !== (int) $payment->buyer_id
                    || $order->checkout_group_id !== $payment->checkout_group_id
                    || $order->payment_method !== 'online' || $order->status !== Order::STATUS_TO_SHIP)
                || Delivery::whereIn('order_id', $orders->pluck('id'))->exists()) {
                throw $this->stale();
            }

            // Checkout's saved OrderItem quantities are the only inventory deltas; Cart remains consumed.
            $items = OrderItem::whereIn('order_id', $orders->pluck('id'))
                ->orderBy('id')->lockForUpdate()->get();
            if ($items->isEmpty() || $orders->pluck('id')->diff($items->pluck('order_id'))->isNotEmpty()
                || $items->contains(fn (OrderItem $item) => (int) $item->quantity < 1)) {
                throw $this->stale();
            }
            $products = Product::whereIn('id', $items->pluck('product_id')->unique())
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $variants = ProductVariant::whereIn('id', $items->pluck('product_variant_id')->filter()->unique())
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($items as $item) {
                if (! $products->has($item->product_id)
                    || ($item->product_variant_id && (! $variants->has($item->product_variant_id)
                        || (int) $variants[$item->product_variant_id]->product_id !== (int) $item->product_id))) {
                    throw $this->stale();
                }
            }

            // Checkout increments one Voucher per group, recorded on its discounted Seller Order.
            $discountedOrders = $orders->filter(fn (Order $order) => $order->voucher_id !== null);
            $voucherIds = $discountedOrders->pluck('voucher_id')->unique()->values();
            if ($discountedOrders->count() > 1 || $orders->contains(fn (Order $order) =>
                ($order->voucher_id === null) !== ((float) $order->voucher_discount === 0.0))) {
                throw $this->stale();
            }
            $voucher = $voucherIds->isEmpty() ? null
                : Voucher::whereKey($voucherIds->first())->lockForUpdate()->first();
            // Voucher ownership is sellers' users.id in Checkout, not the Order's sellers.id.
            $discountedSeller = $discountedOrders->isEmpty() ? null
                : Seller::whereKey($discountedOrders->first()->seller_id)->first();
            if ($voucherIds->isNotEmpty() && (! $voucher || $voucher->used_count < 1
                || ! $discountedSeller || (int) $voucher->seller_id !== (int) $discountedSeller->user_id)) {
                throw $this->stale();
            }

            foreach ($items->groupBy('product_id') as $productId => $productItems) {
                if (Product::whereKey($productId)->increment('stock', $productItems->sum('quantity')) !== 1) {
                    throw $this->stale();
                }
            }
            foreach ($items->whereNotNull('product_variant_id')->groupBy('product_variant_id') as $variantId => $variantItems) {
                if (ProductVariant::whereKey($variantId)->increment('stock', $variantItems->sum('quantity')) !== 1) {
                    throw $this->stale();
                }
            }
            if ($voucher) {
                if (Voucher::whereKey($voucher->id)->where('used_count', '>', 0)->decrement('used_count') !== 1) {
                    throw $this->stale();
                }
            }
            if (Order::whereIn('id', $orders->pluck('id'))->update(['status' => Order::STATUS_CANCELLED]) !== $orders->count()) {
                throw $this->stale();
            }
            $payment->update([
                'status' => $terminalStatus, 'expires_at' => null, 'closed_at' => now(),
                'closed_by_user_id' => $actorUserId, 'closure_reason' => $reason,
            ]);
            return true;
        });
    }

    private function stale(): ValidationException
    {
        return ValidationException::withMessages(['payment' => 'Payment or related Orders changed. Refresh and review them.']);
    }
}
