<?php

namespace App\Services;

use App\Models\Buyer;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Order\OrderItem;
use App\Models\Finance\PlatformVoucher;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BuyerCheckoutService
{
    /**
     * Build checkout data from the same persisted cart, product, seller, stock,
     * voucher, and Buyer address records used by the web checkout.
     *
     * $selectedIds can be empty for the web page, which intentionally means
     * "all current cart lines". Mobile always supplies an explicit selection.
     *
     * $logisticsChoices is [sellerId => logisticsPartnerId]; it only preselects an option in the preview.
     */
    public function preview(
        Buyer $buyer,
        array $selectedIds = [],
        array $quantityOverrides = [],
        string $voucherCode = '',
        array $shippingMethods = [],
        string $paymentMethod = 'cod',
        bool $strictVoucher = false,
        string $platformVoucherCode = '',
        string $shippingVoucherCode = '',
        array $logisticsChoices = [],
        array $sellerVoucherCodes = [],
    ): array {
        // Cash on Delivery is the only supported payment method.
        if ($paymentMethod !== 'cod') {
            throw ValidationException::withMessages([
                'payment_method' => 'Choose a valid payment method.',
            ]);
        }

        $selectedIds = collect($selectedIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $itemsQuery = CartItem::with(['product.seller', 'variant.image'])
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
        $availableVouchers = $this->vouchersForItems($cartItems, (int) $buyer->id);
        $voucherCode = strtoupper(trim($voucherCode));
        $allowedShopIds = $groups->pluck('shop.id')->map(fn ($id) => (int) $id)->all();
        if (array_diff(array_map('intval', array_keys($sellerVoucherCodes)), $allowedShopIds)) {
            throw ValidationException::withMessages(['seller_vouchers' => 'A selected Seller voucher does not belong to this checkout.']);
        }

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
        $voucherDiscountTotal = 0.0;
        $sellerShippingDiscountTotal = 0.0;

        $groups = $groups->map(function (array $group) use (
            $buyer,
            $shippingMethods,
            $logisticsChoices,
            $selectedVoucher,
            $sellerVoucherCodes,
            $availableVouchers,
            &$voucherApplied,
            &$merchandiseTotal,
            &$shippingTotal,
            &$voucherDiscountTotal,
            &$sellerShippingDiscountTotal,
        ) {
            $sellerId = (int) $group['shop']['id'];
            $shippingMethod = $shippingMethods[$sellerId]
                ?? $shippingMethods[(string) $sellerId]
                ?? 'standard';

            if (! in_array($shippingMethod, ['standard'], true)) {
                throw ValidationException::withMessages([
                    'shipping_method' => 'Choose shipping for every shop.',
                ]);
            }

            // Couriers that can pick up from this Seller and deliver to the Buyer's address.
            $logisticsOptions = $this->logisticsOptionsFor($buyer, $sellerId);
            $chosenPartner = (int) ($logisticsChoices[$sellerId] ?? $logisticsChoices[(string) $sellerId] ?? 0);
            if ($chosenPartner > 0 && ! $logisticsOptions->contains('id', $chosenPartner)) {
                $chosenPartner = 0;
            }
            if ($chosenPartner <= 0 && $logisticsOptions->isNotEmpty()) {
                $chosenPartner = (int) $logisticsOptions->first()['id'];
            }

            $merchandiseSubtotal = collect($group['items'])
                ->sum(fn (array $item) => (float) $item['price'] * (int) $item['qty']);

            $seller = Seller::find($sellerId);
            if (! $seller) {
                throw ValidationException::withMessages(['items' => 'Seller profile is unavailable for this checkout group.']);
            }
            $shippingQuote = app(ShippingRateService::class)->quote(
                $seller, $buyer, collect($group['items']), $shippingMethod
            );
            $shippingFee = (float) $shippingQuote['selected_fee'];
            $voucherDiscount = 0.0;
            $sellerShippingDiscount = 0.0;

            // Exactly one Seller voucher per shop; older single-code clients remain supported.
            $code = app(SellerVoucherPricing::class)->selectedCode($sellerId, $sellerVoucherCodes,
                ($selectedVoucher['seller_id'] ?? null) == $sellerId ? ($selectedVoucher['code'] ?? '') : '');
            $offer = $code === '' ? null : $availableVouchers->first(fn (array $v) => $v['code'] === $code && (int) $v['seller_id'] === $sellerId);
            if ($code !== '' && ! $offer) {
                throw ValidationException::withMessages(['seller_vouchers' => 'Invalid Seller voucher for this shop.']);
            }
            if ($offer) {
                $voucher = app(SellerVoucherPricing::class)->find($code, (int) $seller->user_id, (int) $buyer->id);
                $eligibleIds = $voucher->products()->pluck('products.id');
                $eligibleSubtotal = collect($group['items'])
                    ->filter(fn (array $item) => $eligibleIds->contains((int) $item['product_id']))
                    ->sum(fn (array $item) => (float) $item['price'] * (int) $item['qty']);
                $discount = app(SellerVoucherPricing::class)->discounts($voucher, $eligibleSubtotal,
                    $merchandiseSubtotal, $shippingFee, (float) app(PlatformFinancialPolicyService::class)->active()['product_percent']);
                $voucherDiscount = $discount['merchandise'];
                $sellerShippingDiscount = $discount['shipping'];
                $voucherApplied = true;
            }

            $groupTotal = max(0, $merchandiseSubtotal + $shippingFee - $voucherDiscount - $sellerShippingDiscount);

            $merchandiseTotal += $merchandiseSubtotal;
            $shippingTotal += $shippingFee;
            $voucherDiscountTotal += $voucherDiscount;
            $sellerShippingDiscountTotal += $sellerShippingDiscount;

            return [
                ...$group,
                'selected_shipping_method' => $shippingMethod,
                'shipping_fee_standard' => (float) $shippingQuote['standard_fee'],
                'shipping_fee_express' => (float) $shippingQuote['express_fee'],
                'shipping_quote' => $shippingQuote,
                'logistics_options' => $logisticsOptions->all(),
                'selected_logistics_partner_id' => $chosenPartner > 0 ? $chosenPartner : null,
                'merchandise_subtotal' => round($merchandiseSubtotal, 2),
                'shipping_fee' => round($shippingFee, 2),
                'cod_fee' => 0.0,
                'voucher_discount' => round($voucherDiscount, 2),
                'seller_shipping_discount' => round($sellerShippingDiscount, 2),
                'seller_voucher_code' => $offer['code'] ?? '',
                'group_total' => round($groupTotal, 2),
            ];
        })->values();

        if ($strictVoucher && $selectedVoucher && ! $voucherApplied) {
            throw ValidationException::withMessages([
                'voucher_code' => 'This voucher does not apply to the selected products.',
            ]);
        }

        // Apply platform-funded vouchers AFTER seller discounts, without subtracting
        // them from seller's future settlement base or from logistics earnings.
        $pricing = app(PlatformVoucherPricing::class)->price(
            $groups->mapWithKeys(fn ($group) => [$group['shop']['id'] => [
                'merchandise' => round($group['merchandise_subtotal'] - $group['voucher_discount'], 2),
                'shipping' => max(0, round($group['shipping_fee'] - $group['seller_shipping_discount'], 2)),
            ]])->all(), $platformVoucherCode, $shippingVoucherCode, false, (int) $buyer->id
        );
        $groups = $groups->map(function ($group) use ($pricing) {
            $alloc = $pricing['allocations'][$group['shop']['id']] ?? ['platform_discount' => 0, 'shipping_discount' => 0];

            return [...$group, ...$alloc,
                'group_total' => round($group['group_total'] - $alloc['platform_discount'] - $alloc['shipping_discount'], 2)];
        });
        $grandTotal = max(0, $merchandiseTotal + $shippingTotal - $voucherDiscountTotal - $sellerShippingDiscountTotal
            - $pricing['merchandise_discount'] - $pricing['shipping_discount']);

        return [
            'address' => $address,
            'cart_groups' => $groups,
            'available_vouchers' => $availableVouchers,
            'seller_vouchers' => $groups->mapWithKeys(fn ($g) => [$g['shop']['id'] => $g['seller_voucher_code']])->all(),
            'available_platform_vouchers' => PlatformVoucher::available()->orderBy('kind')->get()
                ->filter(function ($v) use ($buyer) {
                    if ($v->per_buyer_limit === null) {
                        return true;
                    }
                    $column = $v->kind === 'shipping' ? 'shipping_voucher_id' : 'platform_voucher_id';

                    return Order::where('buyer_id', $buyer->id)->where($column, $v->id)
                        ->distinct('checkout_group_id')->count('checkout_group_id') < $v->per_buyer_limit;
                })
                ->map(fn ($v) => ['code' => $v->code, 'name' => $v->name, 'kind' => $v->kind,
                    'type' => $v->type, 'value' => (float) $v->value,
                    'min_spend' => (float) $v->min_spend,
                    'max_discount' => $v->max_discount === null ? null : (float) $v->max_discount,
                    'remaining_budget' => $v->budget_amount === null ? null : max(0, round((float) $v->budget_amount - (float) $v->spent_amount, 2)),
                    'per_buyer_limit' => $v->per_buyer_limit])->values()->all(),
            'platform_voucher_code' => strtoupper(trim($platformVoucherCode)),
            'shipping_voucher_code' => strtoupper(trim($shippingVoucherCode)),
            'initial_voucher_code' => $selectedVoucher['code'] ?? '',
            'item_count' => $cartItems->count(),
            'quantity_count' => (int) $cartItems->sum('quantity'),
            'shop_count' => $groups->count(),
            'payment_method' => $paymentMethod,
            'summary' => [
                'merchandise_subtotal' => round($merchandiseTotal, 2),
                'shipping_fee' => round($shippingTotal, 2),
                'cod_fee' => 0.0,
                'voucher_discount' => round($voucherDiscountTotal, 2),
                'seller_shipping_discount' => round($sellerShippingDiscountTotal, 2),
                'platform_discount' => $pricing['merchandise_discount'],
                'shipping_discount' => $pricing['shipping_discount'],
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
            // Serialize concurrent checkouts by this Buyer for per-buyer voucher limits.
            Buyer::whereKey($buyer->id)->lockForUpdate()->firstOrFail();
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

            $groupedBySeller = $cartItems->groupBy(fn (CartItem $item) => $item->product->seller->id)->sortKeys();
            $legacyVoucherCode = strtoupper(trim((string) ($data['voucher_code'] ?? '')));
            $legacyVoucher = $legacyVoucherCode !== ''
                ? Voucher::where('code', $legacyVoucherCode)->first()
                : null;
            if ($legacyVoucherCode !== '' && ! $legacyVoucher) {
                throw ValidationException::withMessages(['voucher_code' => 'Invalid Seller voucher code.']);
            }
            $sellerVoucherCodes = (array) ($data['seller_vouchers'] ?? []);
            if (array_diff(array_map('intval', array_keys($sellerVoucherCodes)),
                $groupedBySeller->keys()->map(fn ($id) => (int) $id)->all())) {
                throw ValidationException::withMessages(['seller_vouchers' => 'A selected Seller voucher does not belong to this checkout.']);
            }
            $sellerPricing = [];
            $sellerVouchers = [];
            $checkoutGroupId = (string) Str::uuid();
            $voucherApplied = false;
            $createdOrderIds = [];
            $grandTotal = 0.0;
            $pricingGroups = [];
            foreach ($groupedBySeller as $shopId => $shopItems) {
                $method = $data['shipping_method'][$shopId] ?? null;
                if (! in_array($method, ['standard'], true)) {
                    throw ValidationException::withMessages(['shipping_method' => 'Choose shipping for every shop.']);
                }
                $subtotal = round($shopItems->sum(fn ($item) => $item->unitPrice() * $item->quantity), 2);
                $sellerSaving = 0.0;
                $sellerShippingSaving = 0.0;
                $sellerProfile = $shopItems->first()->product->seller;
                $shippingQuote = app(ShippingRateService::class)->quote($sellerProfile, $buyer, $shopItems, $method);
                $grossShipping = (float) $shippingQuote['selected_fee'];
                $legacyForThisShop = $legacyVoucher && (int) $legacyVoucher->seller_id === (int) $sellerProfile->user_id
                    ? $legacyVoucherCode : '';
                $code = app(SellerVoucherPricing::class)->selectedCode((int) $shopId, $sellerVoucherCodes, $legacyForThisShop);
                $offer = app(SellerVoucherPricing::class)->find($code, (int) $sellerProfile->user_id,
                    (int) $buyer->id, true);
                $sellerVouchers[$shopId] = $offer;
                if ($offer) {
                    $eligibleIds = $offer->products()->pluck('products.id');
                    $eligible = $shopItems->filter(fn ($item) => $eligibleIds->contains($item->product_id))
                        ->sum(fn ($item) => $item->unitPrice() * $item->quantity);
                    $savings = app(SellerVoucherPricing::class)->discounts($offer, (float) $eligible,
                        (float) $subtotal, $grossShipping, (float) app(PlatformFinancialPolicyService::class)->active()['product_percent']);
                    $sellerSaving = $savings['merchandise'];
                    $sellerShippingSaving = $savings['shipping'];
                    $voucherApplied = true;
                }
                $sellerPricing[$shopId] = ['merchandise' => $sellerSaving, 'shipping' => $sellerShippingSaving];
                $pricingGroups[$shopId] = [
                    'merchandise' => round($subtotal - $sellerSaving, 2),
                    'shipping' => max(0, round($grossShipping - $sellerShippingSaving, 2)),
                ];
            }
            $platformPricing = app(PlatformVoucherPricing::class)->price(
                $pricingGroups, (string) ($data['merchandise_voucher_code'] ?? ''),
                (string) ($data['shipping_voucher_code'] ?? ''), true, (int) $buyer->id);

            foreach ($groupedBySeller as $sellerId => $items) {
                $shippingMethod = $data['shipping_method'][$sellerId] ?? null;
                if (! in_array($shippingMethod, ['standard'], true)) {
                    throw ValidationException::withMessages([
                        'shipping_method' => 'Choose shipping for every shop.',
                    ]);
                }

                // Every Seller order must have an actual eligible courier. If the client
                // did not explicitly choose one (older mobile clients), use the first
                // eligible partner rather than persisting a "no preference" order.
                $logisticsOptions = $this->logisticsOptionsFor($buyer, (int) $sellerId);
                if ($logisticsOptions->isEmpty()) {
                    throw ValidationException::withMessages([
                        'logistics_partner' => 'No approved courier has pickup/delivery coverage and a valid delivery path for this shop. Try another address or contact support.',
                    ]);
                }

                $logisticsPartnerId = (int) ($data['logistics_partner'][$sellerId] ?? 0);
                if ($logisticsPartnerId <= 0) {
                    $logisticsPartnerId = (int) $logisticsOptions->first()['id'];
                } elseif (! $logisticsOptions->contains('id', $logisticsPartnerId)) {
                    throw ValidationException::withMessages([
                        'logistics_partner' => 'The selected courier no longer has a valid delivery path for this shop. Refresh checkout and choose an available courier.',
                    ]);
                }

                $merchandiseSubtotal = $items->sum(fn ($item) => $item->unitPrice() * $item->quantity);
                $sellerProfile = $items->first()->product->seller;
                $shippingQuote = app(ShippingRateService::class)->quote($sellerProfile, $buyer, $items, $shippingMethod);
                $shippingFee = (float) $shippingQuote['selected_fee'];
                $codFee = 0.0;

                $voucherDiscount = (float) ($sellerPricing[$sellerId]['merchandise'] ?? 0);
                $sellerShippingDiscount = (float) ($sellerPricing[$sellerId]['shipping'] ?? 0);
                $appliedVoucherId = $sellerVouchers[$sellerId]?->id;

                $alloc = $platformPricing['allocations'][$sellerId] ?? ['platform_discount' => 0, 'shipping_discount' => 0];
                $totalAmount = $merchandiseSubtotal + $shippingFee + $codFee - $voucherDiscount - $sellerShippingDiscount
                    - $alloc['platform_discount'] - $alloc['shipping_discount'];

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
                    'logistics_partner_id' => $logisticsPartnerId > 0 ? $logisticsPartnerId : null,
                    'voucher_id' => $appliedVoucherId,
                    'voucher_discount' => $voucherDiscount,
                    'seller_shipping_discount' => $sellerShippingDiscount,
                    'platform_voucher_id' => $platformPricing['merchandise_voucher']?->id,
                    'shipping_voucher_id' => $platformPricing['shipping_voucher']?->id,
                    'platform_discount' => $alloc['platform_discount'],
                    'shipping_discount' => $alloc['shipping_discount'],
                    'note' => $data['groups'][$sellerId]['note'] ?? null,
                    'delivery_name' => trim($buyer->first_name.' '.$buyer->last_name),
                    'delivery_phone' => $buyer->contact_no,
                    'delivery_address' => trim(
                        $buyer->street_address.', '.
                        $buyer->barangay_name.', '.
                        $buyer->municipality_name.
                        ($buyer->province_name ? ', '.$buyer->province_name : '')
                    ),
                    'delivery_region_code' => $buyer->region_code,
                    'delivery_region_name' => $buyer->region_name,
                    'delivery_province_code' => $buyer->province_code,
                    'delivery_province_name' => $buyer->province_name,
                    'delivery_municipality_code' => $buyer->municipality_code,
                    'delivery_municipality_name' => $buyer->municipality_name,
                    'delivery_barangay_code' => $buyer->barangay_code,
                    'delivery_barangay_name' => $buyer->barangay_name,
                    'delivery_street' => $buyer->street_address,
                ]);

                app(ShopHopFinance::class)->snapshot(
                    $order,
                    (float) $merchandiseSubtotal,
                    (float) $voucherDiscount,
                    (float) $sellerShippingDiscount,
                    (float) $alloc['platform_discount'],
                    (float) $alloc['shipping_discount'],
                    $shippingQuote
                );
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

                    $orderItemData = [
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'price' => $item->unitPrice(),
                    ];
                    if (Schema::hasColumn('order_items', 'variant_label')) {
                        $orderItemData['variant_label'] = $item->variantLabel();
                        $orderItemData['variant_image_path'] = $item->variant?->image?->image_path;
                    }
                    OrderItem::create($orderItemData);
                }
            }

            if ($legacyVoucherCode !== '' && ! $voucherApplied && $sellerVoucherCodes === []) {
                throw ValidationException::withMessages(['voucher_code' => 'This Seller voucher is not eligible for the selected shops.']);
            }
            // Once for each used Seller voucher, and once per platform campaign per checkout group.
            foreach ($sellerVouchers as $applied) {
                if ($applied) {
                    $applied->increment('used_count');
                }
            }
            foreach (['merchandise' => 'merchandise_discount', 'shipping' => 'shipping_discount'] as $kind => $key) {
                $applied = $platformPricing[$kind.'_voucher'];
                if (! $applied) {
                    continue;
                }
                $applied->increment('used_count');
                $applied->increment('spent_amount', (float) $platformPricing[$key]);
            }

            CartItem::where('buyer_id', $buyer->id)
                ->whereIn('id', $cartItemIds)
                ->delete();

            return [
                'checkout_group_id' => $checkoutGroupId,
                'order_ids' => $createdOrderIds,
                'payment_method' => $data['payment_method'],
                'grand_total' => round($grandTotal, 2),
            ];
        });
    }

    public function addressForBuyer(Buyer $buyer): array
    {
        return [
            'name' => trim($buyer->first_name.' '.$buyer->last_name),
            'phone' => $buyer->contact_no,
            'line' => $buyer->street_address,
            'city' => implode(', ', array_filter([
                $buyer->barangay_name, $buyer->municipality_name, $buyer->province_name ?: $buyer->region_name,
            ])),
            'is_default' => true,
            // Registration stores this address, but there is no separate address-verification record.
            'verified' => false,
            'complete' => (bool) (
                $buyer->street_address
                && $buyer->contact_no
                && $buyer->barangay_name
                && $buyer->municipality_name
                && ($buyer->province_name || $this->isNcrBuyer($buyer))
            ),
        ];
    }

    /**
     * Couriers that can pick up from this Seller and deliver to the Buyer's saved address.
     * Preview and placement share this one rule so the list shown is the list enforced.
     *
     * @return Collection<int, array{id:int, name:string}>
     */
    private function logisticsOptionsFor(Buyer $buyer, int $sellerId): Collection
    {
        $seller = Seller::find($sellerId);
        if (! $seller) {
            return collect();
        }

        return app(LogisticsRoutingService::class)
            ->eligiblePartnersFor($seller, [
                'delivery_region_name' => $buyer->region_name,
                'delivery_province_name' => $buyer->province_name,
                'delivery_municipality_name' => $buyer->municipality_name,
                'delivery_address' => trim(
                    $buyer->street_address.', '.
                    $buyer->barangay_name.', '.
                    $buyer->municipality_name.
                    ($buyer->province_name ? ', '.$buyer->province_name : '')
                ),
            ])
            ->map(fn ($partner) => ['id' => (int) $partner->id, 'name' => (string) $partner->company_name])
            ->values();
    }

    private function assertCompleteAddress(Buyer $buyer): void
    {
        if (! $buyer->street_address
            || ! $buyer->contact_no
            || ! $buyer->barangay_name
            || ! $buyer->municipality_name
            || (! $buyer->province_name && ! $this->isNcrBuyer($buyer))) {
            throw ValidationException::withMessages([
                'address' => 'Complete your delivery address in your profile before ordering.',
            ]);
        }
    }

    private function isNcrBuyer(Buyer $buyer): bool
    {
        return strtoupper(trim((string) $buyer->region_code)) === '130000000'
            || str_contains(strtoupper((string) $buyer->region_name), 'NATIONAL CAPITAL REGION')
            || strtoupper(trim((string) $buyer->region_code)) === 'NCR';
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
                    'items' => $items->map(fn (CartItem $item) => [
                        'id' => (int) $item->id,
                        'line_key' => (string) $item->id,
                        'product_id' => (int) $item->product_id,
                        'variant_id' => $item->product_variant_id ? (int) $item->product_variant_id : null,
                        'name' => $item->product->name,
                        'image' => ($item->variant?->image?->image_path ?? $item->product->image)
                            ? Storage::url($item->variant?->image?->image_path ?? $item->product->image)
                            : asset('images/products/placeholder.png'),
                        'variant' => $item->variantLabel(),
                        'price' => round((float) $item->unitPrice(), 2),
                        'weight_kg' => round((float) ($item->variant?->weight_kg ?? $item->product->weight_kg ?? 0.5), 3),
                        'original_price' => $item->originalPrice(),
                        'qty' => (int) $item->quantity,
                        'stock' => $item->availableStock(),
                        'line_total' => round((float) $item->unitPrice() * (int) $item->quantity, 2),
                    ])->values()->all(),
                ];
            })
            ->values();
    }

    private function vouchersForItems(Collection $cartItems, int $buyerId): Collection
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
            ->filter(fn (Voucher $v) => $v->per_buyer_limit === null
                || Order::where('buyer_id', $buyerId)->where('voucher_id', $v->id)->count() < $v->per_buyer_limit)
            ->map(fn (Voucher $voucher) => [
                'code' => $voucher->code,
                'title' => ($voucher->kind ?? 'merchandise') === 'shipping'
                    ? ($voucher->type === 'percent' && (float) $voucher->value >= 100 ? 'Free Shipping'
                        : ($voucher->type === 'percent' ? $voucher->value.'% Shipping Off' : '₱'.number_format($voucher->value).' Shipping Off'))
                    : ($voucher->type === 'percent' ? $voucher->value.'% Off' : '₱'.number_format($voucher->value).' Off'),
                'description' => (($voucher->kind ?? 'merchandise') === 'shipping' ? 'Seller-funded shipping voucher · ' : '')
                    .'Min. merchandise ₱'.number_format($voucher->min_order_amount)
                    .($voucher->max_discount === null ? '' : ' · Max ₱'.number_format((float) $voucher->max_discount))
                    .($voucher->per_buyer_limit === null ? '' : ' · '.$voucher->per_buyer_limit.' use(s) per Buyer'),
                'kind' => $voucher->kind ?? 'merchandise',
                'type' => $voucher->type,
                'value' => (float) $voucher->value,
                'min_spend' => (float) $voucher->min_order_amount,
                'max_discount' => $voucher->max_discount === null ? null : (float) $voucher->max_discount,
                'per_buyer_limit' => $voucher->per_buyer_limit,
                'seller_id' => Seller::where('user_id', $voucher->seller_id)->value('id'),
                'product_ids' => $voucher->products()->pluck('products.id')->map(fn ($id) => (int) $id)->values()->all(),
            ])
            ->values();
    }
}
