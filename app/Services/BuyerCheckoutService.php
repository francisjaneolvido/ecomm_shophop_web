<?php

namespace App\Services;

use App\Models\Buyer;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Buyer\Order\ManualCashlessPayment;
use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Order\OrderItem;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BuyerCheckoutService
{
    public const COD_FEE = 20;
    public const STANDARD_SHIPPING_FEE = 58;
    public const EXPRESS_SHIPPING_SURCHARGE = 70;

    /**
     * Build checkout data from the same persisted cart, product, seller, stock,
     * voucher, and Buyer address records used by the web checkout.
     *
     * $selectedIds can be empty for the web page, which intentionally means
     * "all current cart lines". Mobile always supplies an explicit selection.
     */
    public function preview(
        Buyer $buyer,
        array $selectedIds = [],
        array $quantityOverrides = [],
        string $voucherCode = '',
        array $shippingMethods = [],
        string $paymentMethod = 'cod',
        bool $strictVoucher = false,
    ): array {
        if (! in_array($paymentMethod, ['cod', 'online'], true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'Choose a valid payment method.',
            ]);
        }

        $selectedIds = collect($selectedIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $itemsQuery = CartItem::with(['product.seller', 'variant'])
            ->where('buyer_id', $buyer->id);

        if ($selectedIds->isNotEmpty()) {
            $itemsQuery->whereIn('id', $selectedIds);
        }

        $cartItems = $itemsQuery->get();

        if ($selectedIds->isNotEmpty() && $cartItems->count() !== $selectedIds->count()) {
            throw ValidationException::withMessages([
                'items' => 'Some selected cart items are no longer available.',
            ]);
        }

        if ($cartItems->contains(fn (CartItem $item) => ! $this->isAvailable($item))) {
            throw ValidationException::withMessages([
                'items' => 'Your selected cart contains an unavailable product or variant.',
            ]);
        }

        $cartItems->each(function (CartItem $item) use ($quantityOverrides) {
            $override = (int) ($quantityOverrides[(string) $item->id]
                ?? $quantityOverrides[$item->id]
                ?? 0);

            if ($override <= 0) {
                return;
            }

            $stock = $item->availableStock();
            if ($stock <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'A selected product or variant is no longer available.',
                ]);
            }

            // The existing web page treats query quantity as display input and
            // caps it to current stock. Placement still performs the locked,
            // authoritative validation below.
            $item->quantity = min($stock, $override);
        });

        $address = $this->addressForBuyer($buyer);
        $groups = $this->groupByShop($cartItems);
        $availableVouchers = $this->vouchersForItems($cartItems);
        $voucherCode = strtoupper(trim($voucherCode));

        $selectedVoucher = $voucherCode === ''
            ? null
            : $availableVouchers->first(fn (array $voucher) => $voucher['code'] === $voucherCode);

        if ($strictVoucher && $voucherCode !== '' && ! $selectedVoucher) {
            throw ValidationException::withMessages([
                'voucher_code' => 'This voucher is invalid or unavailable for the selected products.',
            ]);
        }

        $voucherApplied = false;
        $merchandiseTotal = 0.0;
        $shippingTotal = 0.0;
        $codFeeTotal = 0.0;
        $voucherDiscountTotal = 0.0;

        $groups = $groups->map(function (array $group) use (
            $shippingMethods,
            $paymentMethod,
            $selectedVoucher,
            $strictVoucher,
            &$voucherApplied,
            &$merchandiseTotal,
            &$shippingTotal,
            &$codFeeTotal,
            &$voucherDiscountTotal,
        ) {
            $sellerId = (int) $group['shop']['id'];
            $shippingMethod = $shippingMethods[$sellerId]
                ?? $shippingMethods[(string) $sellerId]
                ?? 'standard';

            if (! in_array($shippingMethod, ['standard', 'express'], true)) {
                throw ValidationException::withMessages([
                    'shipping_method' => 'Choose shipping for every shop.',
                ]);
            }

            $merchandiseSubtotal = collect($group['items'])
                ->sum(fn (array $item) => (float) $item['price'] * (int) $item['qty']);

            $shippingFee = self::STANDARD_SHIPPING_FEE
                + ($shippingMethod === 'express' ? self::EXPRESS_SHIPPING_SURCHARGE : 0);
            $codFee = $paymentMethod === 'cod' ? self::COD_FEE : 0;
            $voucherDiscount = 0.0;

            if ($selectedVoucher && (int) $selectedVoucher['seller_id'] === $sellerId) {
                $eligibleProductIds = collect($selectedVoucher['product_ids'])->map(fn ($id) => (int) $id);
                $eligibleSubtotal = collect($group['items'])
                    ->filter(fn (array $item) => $eligibleProductIds->contains((int) $item['product_id']))
                    ->sum(fn (array $item) => (float) $item['price'] * (int) $item['qty']);

                if ($eligibleSubtotal <= 0 || $eligibleSubtotal < (float) $selectedVoucher['min_spend']) {
                    if ($strictVoucher) {
                        throw ValidationException::withMessages([
                            'voucher_code' => 'This voucher does not qualify for the selected products.',
                        ]);
                    }
                } else {
                    $voucherDiscount = $selectedVoucher['type'] === 'percent'
                        ? round($eligibleSubtotal * ((float) $selectedVoucher['value'] / 100), 2)
                        : (float) $selectedVoucher['value'];
                    $voucherDiscount = min($voucherDiscount, $eligibleSubtotal);
                    $voucherApplied = true;
                }
            }

            $groupTotal = max(0, $merchandiseSubtotal + $shippingFee + $codFee - $voucherDiscount);

            $merchandiseTotal += $merchandiseSubtotal;
            $shippingTotal += $shippingFee;
            $codFeeTotal += $codFee;
            $voucherDiscountTotal += $voucherDiscount;

            return [
                ...$group,
                'selected_shipping_method' => $shippingMethod,
                'merchandise_subtotal' => round($merchandiseSubtotal, 2),
                'shipping_fee' => round($shippingFee, 2),
                'cod_fee' => round($codFee, 2),
                'voucher_discount' => round($voucherDiscount, 2),
                'group_total' => round($groupTotal, 2),
            ];
        })->values();

        if ($strictVoucher && $selectedVoucher && ! $voucherApplied) {
            throw ValidationException::withMessages([
                'voucher_code' => 'This voucher does not apply to the selected products.',
            ]);
        }

        $grandTotal = max(0, $merchandiseTotal + $shippingTotal + $codFeeTotal - $voucherDiscountTotal);

        return [
            'address' => $address,
            'cart_groups' => $groups,
            'available_vouchers' => $availableVouchers,
            'initial_voucher_code' => $selectedVoucher['code'] ?? '',
            'item_count' => $cartItems->count(),
            'quantity_count' => (int) $cartItems->sum('quantity'),
            'shop_count' => $groups->count(),
            'cod_fee_per_shop' => self::COD_FEE,
            'payment_method' => $paymentMethod,
            'summary' => [
                'merchandise_subtotal' => round($merchandiseTotal, 2),
                'shipping_fee' => round($shippingTotal, 2),
                'cod_fee' => round($codFeeTotal, 2),
                'voucher_discount' => round($voucherDiscountTotal, 2),
                'grand_total' => round($grandTotal, 2),
            ],
        ];
    }

    /**
     * Authoritative order placement. The transaction rereads and locks cart,
     * inventory, and voucher records so no client-computed amount is trusted.
     */
    public function placeOrder(Buyer $buyer, array $data): array
    {
        $this->assertCompleteAddress($buyer);

        return DB::transaction(function () use ($buyer, $data) {
            $cartItemIds = array_map('intval', array_keys($data['items']));
            $cartItems = CartItem::where('buyer_id', $buyer->id)
                ->whereIn('id', $cartItemIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->count() !== count($cartItemIds)) {
                throw ValidationException::withMessages([
                    'items' => 'Your selected items could not be found in your cart.',
                ]);
            }

            foreach ($cartItems as $item) {
                $product = Product::with('seller')->whereKey($item->product_id)->lockForUpdate()->first();
                $variant = $item->product_variant_id
                    ? ProductVariant::whereKey($item->product_variant_id)->lockForUpdate()->first()
                    : null;

                $item->setRelation('product', $product);
                $item->setRelation('variant', $variant);

                if (! $this->isAvailable($item)) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected product or variant is no longer available.',
                    ]);
                }

                $requestedQty = (int) $data['items'][$item->id]['quantity'];
                if ($requestedQty > $item->availableStock()) {
                    throw ValidationException::withMessages([
                        'items' => "\"{$product->name}\" only has {$item->availableStock()} left in stock.",
                    ]);
                }

                $item->quantity = $requestedQty;
            }

            $groupedBySeller = $cartItems->groupBy(fn (CartItem $item) => $item->product->seller->id);
            $voucherCode = strtoupper(trim((string) ($data['voucher_code'] ?? '')));
            $voucher = null;
            $eligibleProductIds = collect();

            if ($voucherCode !== '') {
                $voucher = Voucher::where('code', $voucherCode)->lockForUpdate()->first();
                if (! $voucher
                    || $voucher->status !== 'active'
                    || ($voucher->starts_at && $voucher->starts_at->isFuture())
                    || ($voucher->ends_at && $voucher->ends_at->isPast())
                    || ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit)
                    || (float) $voucher->value <= 0
                    || ! in_array($voucher->type, ['percent', 'fixed'], true)
                    || ($voucher->type === 'percent' && (float) $voucher->value > 100)) {
                    throw ValidationException::withMessages([
                        'voucher_code' => 'This voucher is invalid or unavailable.',
                    ]);
                }
                $eligibleProductIds = $voucher->products()->pluck('products.id');
            }

            $checkoutGroupId = (string) Str::uuid();
            $voucherApplied = false;
            $createdOrderIds = [];
            $grandTotal = 0.0;

            foreach ($groupedBySeller as $sellerId => $items) {
                $shippingMethod = $data['shipping_method'][$sellerId] ?? null;
                if (! in_array($shippingMethod, ['standard', 'express'], true)) {
                    throw ValidationException::withMessages([
                        'shipping_method' => 'Choose shipping for every shop.',
                    ]);
                }

                $merchandiseSubtotal = $items->sum(fn ($item) => $item->unitPrice() * $item->quantity);
                $shippingFee = self::STANDARD_SHIPPING_FEE
                    + ($shippingMethod === 'express' ? self::EXPRESS_SHIPPING_SURCHARGE : 0);
                $codFee = $data['payment_method'] === 'cod' ? self::COD_FEE : 0;

                $voucherDiscount = 0;
                $appliedVoucherId = null;

                if ($voucher && (int) $voucher->seller_id === (int) $items->first()->product->seller->user_id) {
                    $eligibleSubtotal = $items
                        ->filter(fn ($item) => $eligibleProductIds->contains($item->product_id))
                        ->sum(fn ($item) => $item->unitPrice() * $item->quantity);

                    if ($eligibleSubtotal <= 0 || $eligibleSubtotal < (float) $voucher->min_order_amount) {
                        throw ValidationException::withMessages([
                            'voucher_code' => 'This voucher does not qualify for the selected products.',
                        ]);
                    }

                    $voucherDiscount = $voucher->type === 'percent'
                        ? round($eligibleSubtotal * ((float) $voucher->value / 100), 2)
                        : (float) $voucher->value;
                    $voucherDiscount = min($voucherDiscount, $eligibleSubtotal);
                    $appliedVoucherId = $voucher->id;
                    $voucherApplied = true;
                }

                $totalAmount = $merchandiseSubtotal + $shippingFee + $codFee - $voucherDiscount;

                $order = Order::create([
                    'buyer_id' => $buyer->id,
                    'checkout_group_id' => $checkoutGroupId,
                    'seller_id' => $sellerId,
                    'status' => Order::STATUS_TO_SHIP,
                    'total_amount' => max(0, $totalAmount),
                    'shipping_method' => $shippingMethod,
                    'shipping_fee' => $shippingFee,
                    'payment_method' => $data['payment_method'],
                    'cod_fee' => $codFee,
                    'voucher_id' => $appliedVoucherId,
                    'voucher_discount' => $voucherDiscount,
                    'note' => $data['groups'][$sellerId]['note'] ?? null,
                    'delivery_name' => trim($buyer->first_name . ' ' . $buyer->last_name),
                    'delivery_phone' => $buyer->contact_no,
                    'delivery_address' => trim(
                        $buyer->street_address . ', ' .
                        $buyer->barangay_name . ', ' .
                        $buyer->municipality_name . ', ' .
                        $buyer->province_name
                    ),
                ]);

                $createdOrderIds[] = (int) $order->id;
                $grandTotal += (float) $order->total_amount;

                foreach ($items as $item) {
                    $stockQuery = $item->variant
                        ? ProductVariant::whereKey($item->variant->id)
                        : Product::whereKey($item->product_id);

                    if ($stockQuery->where('stock', '>=', $item->quantity)->decrement('stock', $item->quantity) !== 1) {
                        throw ValidationException::withMessages([
                            'items' => 'A selected item no longer has enough stock.',
                        ]);
                    }

                    if ($item->variant && Product::whereKey($item->product_id)
                        ->where('stock', '>=', $item->quantity)
                        ->decrement('stock', $item->quantity) !== 1) {
                        throw ValidationException::withMessages([
                            'items' => 'A selected item no longer has enough stock.',
                        ]);
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'price' => $item->unitPrice(),
                    ]);
                }
            }

            if ($voucher && ! $voucherApplied) {
                throw ValidationException::withMessages([
                    'voucher_code' => 'This voucher does not apply to the selected products.',
                ]);
            }

            if ($voucherApplied) {
                $voucher->increment('used_count');
            }

            CartItem::where('buyer_id', $buyer->id)
                ->whereIn('id', $cartItemIds)
                ->delete();

            $paymentId = null;
            if ($data['payment_method'] === 'online') {
                $payment = ManualCashlessPayment::create([
                    'buyer_id' => $buyer->id,
                    'checkout_group_id' => $checkoutGroupId,
                    'status' => ManualCashlessPayment::AWAITING_PROOF,
                    'expires_at' => now()->addDay(),
                ]);

                Order::where('buyer_id', $buyer->id)
                    ->where('checkout_group_id', $checkoutGroupId)
                    ->update(['manual_cashless_payment_id' => $payment->id]);

                $paymentId = (int) $payment->id;
            }

            return [
                'checkout_group_id' => $checkoutGroupId,
                'order_ids' => $createdOrderIds,
                'payment_id' => $paymentId,
                'payment_method' => $data['payment_method'],
                'grand_total' => round($grandTotal, 2),
            ];
        });
    }

    public function addressForBuyer(Buyer $buyer): array
    {
        return [
            'name' => trim($buyer->first_name . ' ' . $buyer->last_name),
            'phone' => $buyer->contact_no,
            'line' => $buyer->street_address,
            'city' => trim($buyer->barangay_name . ', ' . $buyer->municipality_name . ', ' . $buyer->province_name),
            'is_default' => true,
            // Registration stores this address, but there is no separate address-verification record.
            'verified' => false,
            'complete' => (bool) (
                $buyer->street_address
                && $buyer->contact_no
                && $buyer->barangay_name
                && $buyer->municipality_name
                && $buyer->province_name
            ),
        ];
    }

    private function assertCompleteAddress(Buyer $buyer): void
    {
        if (! $buyer->street_address
            || ! $buyer->contact_no
            || ! $buyer->barangay_name
            || ! $buyer->municipality_name
            || ! $buyer->province_name) {
            throw ValidationException::withMessages([
                'address' => 'Complete your delivery address in your profile before ordering.',
            ]);
        }
    }

    private function isAvailable(CartItem $item): bool
    {
        $product = $item->product;
        if (! $product
            || $product->compliance_status !== 'approved'
            || $product->status !== 'active'
            || ! $product->seller
            || (int) $product->stock <= 0) {
            return false;
        }

        if ($item->product_variant_id === null) {
            return ! $product->has_variants && (float) $product->price >= 0;
        }

        $variant = $item->variant;
        return $product->has_variants
            && $variant
            && (int) $variant->product_id === (int) $product->id
            && $variant->status === 'active'
            && (int) $variant->stock > 0
            && $variant->price !== null
            && (float) $variant->price >= 0;
    }

    private function groupByShop(Collection $cartItems): Collection
    {
        return $cartItems
            ->groupBy(fn (CartItem $item) => $item->product->seller_id)
            ->map(function (Collection $items) {
                $seller = $items->first()->product->seller;

                return [
                    'shop' => [
                        'id' => (int) $seller->id,
                        'name' => $seller->business_name,
                        'municipality' => $seller->municipality_name,
                    ],
                    'shipping_fee_standard' => self::STANDARD_SHIPPING_FEE,
                    'shipping_fee_express' => self::STANDARD_SHIPPING_FEE + self::EXPRESS_SHIPPING_SURCHARGE,
                    'items' => $items->map(fn (CartItem $item) => [
                        'id' => (int) $item->id,
                        'line_key' => (string) $item->id,
                        'product_id' => (int) $item->product_id,
                        'variant_id' => $item->product_variant_id ? (int) $item->product_variant_id : null,
                        'name' => $item->product->name,
                        'image' => $item->product->image
                            ? Storage::url($item->product->image)
                            : asset('images/products/placeholder.png'),
                        'variant' => $item->variantLabel(),
                        'price' => round((float) $item->unitPrice(), 2),
                        'original_price' => $item->originalPrice(),
                        'qty' => (int) $item->quantity,
                        'stock' => $item->availableStock(),
                        'line_total' => round((float) $item->unitPrice() * (int) $item->quantity, 2),
                    ])->values()->all(),
                ];
            })
            ->values();
    }

    private function vouchersForItems(Collection $cartItems): Collection
    {
        $productIds = $cartItems->pluck('product_id')->unique()->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Voucher::whereHas('products', function ($query) use ($productIds) {
                $query->whereIn('products.id', $productIds);
            })
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->get()
            ->map(fn (Voucher $voucher) => [
                'code' => $voucher->code,
                'title' => $voucher->type === 'percent'
                    ? $voucher->value . '% Off'
                    : '₱' . number_format($voucher->value) . ' Off',
                'description' => 'On ₱' . number_format($voucher->min_order_amount) . ' minimum spend from this seller',
                'type' => $voucher->type,
                'value' => (float) $voucher->value,
                'min_spend' => (float) $voucher->min_order_amount,
                'seller_id' => Seller::where('user_id', $voucher->seller_id)->value('id'),
                'product_ids' => $voucher->products()->pluck('products.id')->map(fn ($id) => (int) $id)->values()->all(),
            ])
            ->values();
    }
}
