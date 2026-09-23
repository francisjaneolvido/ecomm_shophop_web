<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Order\OrderItem;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
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

        if ($cartItems->isEmpty()) {
            $cartItems = CartItem::with(['product.seller', 'variant'])
                ->where('buyer_id', $buyer->id)
                ->get();
        }

        $cartItems->each(function (CartItem $item) use ($queryQty) {
            $override = (int) $queryQty->get((string) $item->id, 0);

            if ($override > 0) {
                $item->quantity = max(1, min($item->availableStock(), $override));
            }
        });

        $address = [
            'name'       => trim($buyer->first_name . ' ' . $buyer->last_name),
            'phone'      => $buyer->contact_no,
            'line'       => $buyer->street_address,
            'city'       => trim($buyer->barangay_name . ', ' . $buyer->municipality_name . ', ' . $buyer->province_name),
            'is_default' => true,
            'verified'   => true,
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

        $data = $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.quantity'          => 'required|integer|min:1',
            'shipping_method'           => 'required|array',
            'shipping_method.*'         => 'required|in:standard,express',
            'payment_method'            => 'required|in:cod,gcash',
            'voucher_code'              => 'nullable|string',
            'groups'                    => 'nullable|array',
            'groups.*.note'             => 'nullable|string|max:500',
            'gcash_reference'           => 'required_if:payment_method,gcash|nullable|string|max:50',
            'gcash_proof'               => 'required_if:payment_method,gcash|nullable|image|max:4096',
        ]);

        $cartItemIds = array_keys($data['items']);

        $cartItems = CartItem::with(['product.seller', 'variant'])
            ->where('buyer_id', $buyer->id)
            ->whereIn('id', $cartItemIds)
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->withErrors(['items' => 'Your selected items could not be found in your cart.']);
        }

        foreach ($cartItems as $item) {
            $requestedQty = (int) ($data['items'][$item->id]['quantity'] ?? $item->quantity);
            $stock = $item->availableStock();

            if ($requestedQty > $stock) {
                return back()->withErrors([
                    'items' => "\"{$item->product->name}\" only has {$stock} left in stock.",
                ]);
            }

            $item->quantity = max(1, $requestedQty);
        }

        $groupedBySeller = $cartItems->groupBy(fn ($item) => $item->product->seller_id);

        $voucherCode = strtoupper(trim((string) ($data['voucher_code'] ?? '')));
        $voucher = $voucherCode ? Voucher::where('code', $voucherCode)->where('status', 'active')->first() : null;

        $gcashProofPath = null;

        if ($data['payment_method'] === 'gcash' && $request->hasFile('gcash_proof')) {
            $gcashProofPath = $request->file('gcash_proof')->store('gcash-proofs', 'public');
        }

        $checkoutGroupId = (string) Str::uuid();
        $createdOrders = [];

        DB::transaction(function () use (
            $groupedBySeller,
            $data,
            $buyer,
            $voucher,
            $gcashProofPath,
            $checkoutGroupId,
            &$createdOrders
        ) {
            foreach ($groupedBySeller as $sellerId => $items) {
                $merchandiseSubtotal = $items->sum(fn ($item) => $item->unitPrice() * $item->quantity);

                $shippingMethod = $data['shipping_method'][$sellerId] ?? 'standard';
                $shippingFee = self::STANDARD_SHIPPING_FEE
                    + ($shippingMethod === 'express' ? self::EXPRESS_SHIPPING_SURCHARGE : 0);

                $codFee = $data['payment_method'] === 'cod' ? self::COD_FEE : 0;

                $voucherDiscount = 0;
                $appliedVoucherId = null;

                if ($voucher && (int) $voucher->seller_id === (int) $sellerId) {
                    $eligibleProductIds = $voucher->products()->pluck('products.id');

                    $eligibleSubtotal = $items
                        ->filter(fn ($item) => $eligibleProductIds->contains($item->product_id))
                        ->sum(fn ($item) => $item->unitPrice() * $item->quantity);

                    if ($eligibleSubtotal >= (float) $voucher->min_order_amount) {
                        $voucherDiscount = $voucher->type === 'percent'
                            ? round($eligibleSubtotal * ((float) $voucher->value / 100), 2)
                            : (float) $voucher->value;

                        $voucherDiscount = min($voucherDiscount, $eligibleSubtotal);
                        $appliedVoucherId = $voucher->id;
                    }
                }

                $totalAmount = $merchandiseSubtotal + $shippingFee + $codFee - $voucherDiscount;

                $order = Order::create([
                    'buyer_id'          => $buyer->id,
                    'checkout_group_id' => $checkoutGroupId,
                    'seller_id'         => $sellerId,
                    'status'            => $data['payment_method'] === 'cod'
                        ? Order::STATUS_TO_SHIP
                        : Order::STATUS_TO_PAY,
                    'total_amount'      => max(0, $totalAmount),
                    'shipping_method'   => $shippingMethod,
                    'shipping_fee'      => $shippingFee,
                    'payment_method'    => $data['payment_method'],
                    'cod_fee'           => $codFee,
                    'voucher_id'        => $appliedVoucherId,
                    'voucher_discount'  => $voucherDiscount,
                    'note'              => $data['groups'][$sellerId]['note'] ?? null,
                    'delivery_name'     => trim($buyer->first_name . ' ' . $buyer->last_name),
                    'delivery_phone'    => $buyer->contact_no,
                    'delivery_address'  => trim($buyer->street_address . ', ' . $buyer->barangay_name . ', ' . $buyer->municipality_name . ', ' . $buyer->province_name),
                    'gcash_reference'   => $data['gcash_reference'] ?? null,
                    'gcash_proof_path'  => $gcashProofPath,
                ]);

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_id'         => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity'           => $item->quantity,
                        'price'              => $item->unitPrice(),
                    ]);

                    if ($item->variant) {
                        $item->variant->decrement('stock', $item->quantity);
                    } else {
                        $item->product->decrement('stock', $item->quantity);
                    }
                }

                if ($appliedVoucherId) {
                    $voucher->increment('used_count');
                }

                $createdOrders[] = $order->id;
            }

            CartItem::where('buyer_id', $buyer->id)
                ->whereIn('id', $groupedBySeller->flatten()->pluck('id'))
                ->delete();
        });

        return redirect()
            ->route('buyer.orders')
            ->with('status', 'Order placed successfully!');
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
            ->where('status', 'active')
            ->get()
            ->map(fn ($voucher) => [
                'code'        => $voucher->code,
                'title'       => $voucher->type === 'percent' ? $voucher->value . '% Off' : '₱' . number_format($voucher->value) . ' Off',
                'description' => 'On ₱' . number_format($voucher->min_order_amount) . ' minimum spend from this seller',
                'type'        => $voucher->type,
                'value'       => (float) $voucher->value,
                'min_spend'   => (float) $voucher->min_order_amount,
                'seller_id'   => $voucher->seller_id,
                'product_ids' => $voucher->products()->pluck('products.id'),
            ]);
    }
}