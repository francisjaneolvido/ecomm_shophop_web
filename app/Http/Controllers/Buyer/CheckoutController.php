<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Order\OrderItem;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    // These published checkout charges are fixed per seller order; no carrier quote is claimed.
    private const COD_FEE = 20;
    private const STANDARD_SHIPPING_FEE = 58;
    private const EXPRESS_SHIPPING_SURCHARGE = 70;

    public function index(Request $request)
    {
        $buyer = Auth::user()->buyer;

        $selectedIds = collect($request->query('items', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $queryQty = collect($request->query('qty', []));
        $queryVoucher = strtoupper(trim((string) $request->query('voucher', '')));

        $itemsQuery = CartItem::with(['product.seller', 'variant'])
            ->where('buyer_id', $buyer->id);

        if ($selectedIds->isNotEmpty()) {
            $itemsQuery->whereIn('id', $selectedIds);
        }

        $cartItems = $itemsQuery->get();

        // An explicit selection must not silently become a purchase of the whole cart.
        if ($selectedIds->isNotEmpty() && $cartItems->count() !== $selectedIds->unique()->count()) {
            return redirect()->route('buyer.cart')->withErrors(['items' => 'Some selected cart items are no longer available.']);
        }

        // Render only current, active merchandise; stale cart references require buyer review.
        if ($cartItems->contains(fn (CartItem $item) => ! $this->isAvailable($item))) {
            return redirect()->route('buyer.cart')->withErrors(['items' => 'Your cart contains an unavailable product or variant.']);
        }

        // Query quantity is display input only; placement rereads and validates the requested quantity.
        $cartItems->each(function (CartItem $item) use ($queryQty) {
            $override = (int) $queryQty->get((string) $item->id, 0);

            if ($override > 0) {
                $item->quantity = min($item->availableStock(), $override);
            }
        });

        $address = [
            'name'       => trim($buyer->first_name . ' ' . $buyer->last_name),
            'phone'      => $buyer->contact_no,
            'line'       => $buyer->street_address,
            'city'       => trim($buyer->barangay_name . ', ' . $buyer->municipality_name . ', ' . $buyer->province_name),
            'is_default' => true,
            // Registration stores this address, but there is no address verification record.
            'verified'   => false,
        ];

        $cartGroups = $this->groupByShop($cartItems);

        $availableVouchers = $this->vouchersForItems($cartItems);

        $initialVoucher = $availableVouchers
            ->first(fn ($voucher) => $voucher['code'] === $queryVoucher);

        $itemCount = $cartItems->count();
        $shopCount = $cartGroups->count();
        $codFee = self::COD_FEE;

        return view('buyer.checkout.cart-checkout', [
            'address'            => $address,
            'cartGroups'         => $cartGroups,
            'availableVouchers'  => $availableVouchers,
            'initialVoucherCode' => $initialVoucher['code'] ?? '',
            'itemCount'          => $itemCount,
            'shopCount'          => $shopCount,
            'codFee'             => $codFee,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $buyer = Auth::user()->buyer;

        // COD is the only supported payment contract; an uploaded reference cannot verify GCash.
        $data = $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.quantity'          => 'required|integer|min:1',
            'shipping_method'           => 'required|array',
            'shipping_method.*'         => 'required|in:standard,express',
            'payment_method'            => 'required|in:cod',
            'voucher_code'              => 'nullable|string',
            'groups'                    => 'nullable|array',
            'groups.*.note'             => 'nullable|string|max:500',
        ]);

        // The registration address is the only persisted delivery address; reject incomplete profiles.
        if (! $buyer->street_address || ! $buyer->contact_no || ! $buyer->barangay_name
            || ! $buyer->municipality_name || ! $buyer->province_name) {
            throw ValidationException::withMessages(['address' => 'Complete your delivery address in your profile before ordering.']);
        }

        // Lock selected lines and canonical inventory in one transaction so totals, stock, orders, and cart removal agree.
        DB::transaction(function () use ($buyer, $data) {
            $cartItemIds = array_keys($data['items']);
            $cartItems = CartItem::where('buyer_id', $buyer->id)
                ->whereIn('id', $cartItemIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // Missing or already consumed lines must never fall back to another cart selection.
            if ($cartItems->count() !== count($cartItemIds)) {
                throw ValidationException::withMessages(['items' => 'Your selected items could not be found in your cart.']);
            }

            foreach ($cartItems as $item) {
                $product = Product::with('seller')->whereKey($item->product_id)->lockForUpdate()->first();
                $variant = $item->product_variant_id
                    ? ProductVariant::whereKey($item->product_variant_id)->lockForUpdate()->first()
                    : null;
                $item->setRelation('product', $product);
                $item->setRelation('variant', $variant);

                // Cart identity cannot authorize a deleted, archived, mismatched, or out-of-stock row.
                if (! $this->isAvailable($item)) {
                    throw ValidationException::withMessages(['items' => 'A selected product or variant is no longer available.']);
                }

                $requestedQty = (int) $data['items'][$item->id]['quantity'];
                if ($requestedQty > $item->availableStock()) {
                    throw ValidationException::withMessages([
                        'items' => "\"{$product->name}\" only has {$item->availableStock()} left in stock.",
                    ]);
                }
                $item->quantity = $requestedQty;
            }

            // Products and vouchers use seller users.id; orders use sellers.id and group mixed carts by that profile ID.
            $groupedBySeller = $cartItems->groupBy(fn (CartItem $item) => $item->product->seller->id);
            $voucherCode = strtoupper(trim((string) ($data['voucher_code'] ?? '')));
            $voucher = null;
            $eligibleProductIds = collect();

            if ($voucherCode !== '') {
                $voucher = Voucher::where('code', $voucherCode)->lockForUpdate()->first();
                if (! $voucher || $voucher->status !== 'active'
                    || ($voucher->starts_at && $voucher->starts_at->isFuture())
                    || ($voucher->ends_at && $voucher->ends_at->isPast())
                    || ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit)
                    || (float) $voucher->value <= 0
                    || ! in_array($voucher->type, ['percent', 'fixed'], true)
                    || ($voucher->type === 'percent' && (float) $voucher->value > 100)) {
                    throw ValidationException::withMessages(['voucher_code' => 'This voucher is invalid or unavailable.']);
                }
                $eligibleProductIds = $voucher->products()->pluck('products.id');
            }

            $checkoutGroupId = (string) Str::uuid();
            $voucherApplied = false;
            foreach ($groupedBySeller as $sellerId => $items) {
                // Every seller needs an explicit shipping choice; server rates override any displayed client total.
                $shippingMethod = $data['shipping_method'][$sellerId] ?? null;
                if (! in_array($shippingMethod, ['standard', 'express'], true)) {
                    throw ValidationException::withMessages(['shipping_method' => 'Choose shipping for every shop.']);
                }

                $merchandiseSubtotal = $items->sum(fn ($item) => $item->unitPrice() * $item->quantity);
                $shippingFee = self::STANDARD_SHIPPING_FEE
                    + ($shippingMethod === 'express' ? self::EXPRESS_SHIPPING_SURCHARGE : 0);
                $codFee = self::COD_FEE;

                $voucherDiscount = 0;
                $appliedVoucherId = null;

                // A voucher applies only to assigned products owned by its seller, never the whole shop subtotal.
                if ($voucher && (int) $voucher->seller_id === (int) $items->first()->product->seller->user_id) {
                    $eligibleSubtotal = $items
                        ->filter(fn ($item) => $eligibleProductIds->contains($item->product_id))
                        ->sum(fn ($item) => $item->unitPrice() * $item->quantity);

                    if ($eligibleSubtotal <= 0 || $eligibleSubtotal < (float) $voucher->min_order_amount) {
                        throw ValidationException::withMessages(['voucher_code' => 'This voucher does not qualify for the selected products.']);
                    }

                    $voucherDiscount = $voucher->type === 'percent'
                        ? round($eligibleSubtotal * ((float) $voucher->value / 100), 2)
                        : (float) $voucher->value;
                    $voucherDiscount = min($voucherDiscount, $eligibleSubtotal);
                    $appliedVoucherId = $voucher->id;
                    $voucherApplied = true;
                }

                // Persist the locked merchandise price and the published per-seller fees as the order total.
                $totalAmount = $merchandiseSubtotal + $shippingFee + $codFee - $voucherDiscount;

                $order = Order::create([
                    'buyer_id'          => $buyer->id,
                    'checkout_group_id' => $checkoutGroupId,
                    'seller_id'         => $sellerId,
                    'status'            => Order::STATUS_TO_SHIP,
                    'total_amount'      => max(0, $totalAmount),
                    'shipping_method'   => $shippingMethod,
                    'shipping_fee'      => $shippingFee,
                    'payment_method'    => 'cod',
                    'cod_fee'           => $codFee,
                    'voucher_id'        => $appliedVoucherId,
                    'voucher_discount'  => $voucherDiscount,
                    'note'              => $data['groups'][$sellerId]['note'] ?? null,
                    'delivery_name'     => trim($buyer->first_name . ' ' . $buyer->last_name),
                    'delivery_phone'    => $buyer->contact_no,
                    'delivery_address'  => trim($buyer->street_address . ', ' . $buyer->barangay_name . ', ' . $buyer->municipality_name . ', ' . $buyer->province_name),
                ]);

                foreach ($items as $item) {
                    // Conditional stock updates guard against negatives; variant sales also reduce Seller Inventory's Product aggregate.
                    $stockQuery = $item->variant
                        ? ProductVariant::whereKey($item->variant->id)
                        : Product::whereKey($item->product_id);
                    if ($stockQuery->where('stock', '>=', $item->quantity)->decrement('stock', $item->quantity) !== 1) {
                        throw ValidationException::withMessages(['items' => 'A selected item no longer has enough stock.']);
                    }
                    if ($item->variant && Product::whereKey($item->product_id)
                        ->where('stock', '>=', $item->quantity)
                        ->decrement('stock', $item->quantity) !== 1) {
                        throw ValidationException::withMessages(['items' => 'A selected item no longer has enough stock.']);
                    }

                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_id'         => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity'           => $item->quantity,
                        'price'              => $item->unitPrice(),
                    ]);
                }
            }

            // An invalid code must fail the entire checkout, including any earlier seller orders in this transaction.
            if ($voucher && ! $voucherApplied) {
                throw ValidationException::withMessages(['voucher_code' => 'This voucher does not apply to the selected products.']);
            }
            if ($voucherApplied) {
                $voucher->increment('used_count');
            }

            // Purchased lines disappear only with the successful atomic order and stock commit.
            CartItem::where('buyer_id', $buyer->id)
                ->whereIn('id', $cartItemIds)
                ->delete();
        });

        return redirect()
            ->route('buyer.orders')
            ->with('status', 'Order placed successfully!');
    }

    private function isAvailable(CartItem $item): bool
    {
        // Product variant mode and the chosen variant must agree, remain active, related, priced, and stocked.
        $product = $item->product;
        if (! $product || $product->status !== 'active' || ! $product->seller
            || (int) $product->stock <= 0) {
            return false;
        }

        if ($item->product_variant_id === null) {
            return ! $product->has_variants && (float) $product->price >= 0;
        }

        $variant = $item->variant;
        return $product->has_variants && $variant
            && (int) $variant->product_id === (int) $product->id
            && $variant->status === 'active' && (int) $variant->stock > 0
            && $variant->price !== null && (float) $variant->price >= 0;
    }

    private function groupByShop($cartItems): \Illuminate\Support\Collection
    {
        return $cartItems
            ->groupBy(fn ($item) => $item->product->seller_id)
            ->map(function ($items) {
                $seller = $items->first()->product->seller;

                return [
                    'shop' => [
                        'id'   => $seller->id,
                        'name' => $seller->business_name,
                    ],
                    'shipping_fee_standard' => self::STANDARD_SHIPPING_FEE,
                    'shipping_fee_express'  => self::STANDARD_SHIPPING_FEE + self::EXPRESS_SHIPPING_SURCHARGE,
                    'items' => $items->map(fn ($item) => [
                        'id'             => $item->id,
                        'product_id'     => $item->product_id,
                        'name'           => $item->product->name,
                        'image'          => $item->product->image
                            ? Storage::url($item->product->image)
                            : asset('images/products/placeholder.png'),
                        'variant'        => $item->variantLabel(),
                        'price'          => $item->unitPrice(),
                        'original_price' => $item->originalPrice(),
                        'qty'            => $item->quantity,
                        'stock'          => $item->availableStock(),
                    ])->values()->all(),
                ];
            })
            ->values();
    }

    private function vouchersForItems($cartItems): \Illuminate\Support\Collection
    {
        $productIds = $cartItems->pluck('product_id')->unique()->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Voucher::whereHas('products', function ($query) use ($productIds) {
                $query->whereIn('products.id', $productIds);
            })
            // Display only vouchers that can still pass the same date and usage gates as placement.
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
            ->map(fn ($voucher) => [
                'code'        => $voucher->code,
                'title'       => $voucher->type === 'percent' ? $voucher->value . '% Off' : '₱' . number_format($voucher->value) . ' Off',
                'description' => 'On ₱' . number_format($voucher->min_order_amount) . ' minimum spend from this seller',
                'type'        => $voucher->type,
                'value'       => (float) $voucher->value,
                'min_spend'   => (float) $voucher->min_order_amount,
                // Checkout shop keys are sellers.id, while Voucher stores the owning users.id.
                'seller_id'   => Seller::where('user_id', $voucher->seller_id)->value('id'),
                'product_ids' => $voucher->products()->pluck('products.id'),
            ]);
    }
}
