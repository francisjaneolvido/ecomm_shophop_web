<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MobileBuyerCartController extends Controller
{
    /**
     * Return the same persisted cart_items used by the Buyer web cart.
     */
    public function index(Request $request): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);

        $items = CartItem::with(['product.seller', 'variant'])
            ->where('buyer_id', $buyer->id)
            ->get();

        $groups = $this->groupByShop($items);
        $vouchers = $this->vouchersForItems($items);

        $subtotal = collect($groups)
            ->flatMap(fn (array $group) => $group['items'])
            ->sum(fn (array $item) => $item['is_available'] ? $item['line_total'] : 0);

        $unavailableCount = collect($groups)
            ->flatMap(fn (array $group) => $group['items'])
            ->where('is_available', false)
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Cart loaded.',
            'data' => [
                'groups' => $groups,
                'vouchers' => $vouchers,
                'cart_count' => $items->count(),
                'quantity_count' => (int) $items->sum('quantity'),
                'subtotal' => round((float) $subtotal, 2),
                'unavailable_count' => $unavailableCount,
            ],
        ]);
    }

    /**
     * Mirrors Buyer\CartController@add so web and mobile persist the same cart line.
     */
    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $buyer = $this->approvedBuyer($request);
        $product = Product::with('seller')->findOrFail($data['product_id']);
        $variant = isset($data['variant_id'])
            ? ProductVariant::findOrFail($data['variant_id'])
            : null;

        $stock = $variant
            ? min((int) $variant->stock, (int) $product->stock)
            : (int) $product->stock;

        if ($product->compliance_status !== 'approved'
            || $product->status !== 'active'
            || ! $product->seller
            || (int) $product->stock <= 0
            || ($product->has_variants && ! $variant)
            || (! $product->has_variants && $variant)
            || ($variant && (
                (int) $variant->product_id !== (int) $product->id
                || $variant->status !== 'active'
                || $variant->price === null
            ))
            || $stock <= 0) {
            throw ValidationException::withMessages([
                'product_id' => 'This product or variant is unavailable.',
            ]);
        }

        $existing = CartItem::where('buyer_id', $buyer->id)
            ->where('product_id', $data['product_id'])
            ->where('product_variant_id', $data['variant_id'] ?? null)
            ->first();

        $qty = (int) ($data['qty'] ?? 1);

        if ($qty + ($existing?->quantity ?? 0) > $stock) {
            throw ValidationException::withMessages([
                'qty' => "Only {$stock} left in stock.",
            ]);
        }

        if ($existing) {
            $existing->quantity += $qty;
            $existing->save();
            $cartItem = $existing;
        } else {
            $cartItem = CartItem::create([
                'buyer_id' => $buyer->id,
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['variant_id'] ?? null,
                'quantity' => $qty,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Added to cart.',
            'data' => [
                'line_key' => (string) $cartItem->id,
                'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
            ],
        ]);
    }

    public function update(Request $request, string $lineKey): JsonResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $buyer = $this->approvedBuyer($request);

        $cartItem = CartItem::with(['product', 'variant'])
            ->where('buyer_id', $buyer->id)
            ->findOrFail($lineKey);

        if (! $cartItem->product
            || $cartItem->product->compliance_status !== 'approved'
            || $cartItem->product->status !== 'active'
            || (int) $cartItem->product->stock <= 0
            || ($cartItem->product->has_variants !== (bool) $cartItem->product_variant_id)
            || ($cartItem->product_variant_id && (
                ! $cartItem->variant
                || (int) $cartItem->variant->product_id !== (int) $cartItem->product_id
                || $cartItem->variant->status !== 'active'
            ))
            || $data['qty'] > $cartItem->availableStock()) {
            throw ValidationException::withMessages([
                'qty' => 'This quantity is no longer available.',
            ]);
        }

        $cartItem->quantity = $data['qty'];
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart quantity updated.',
            'data' => [
                'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
            ],
        ]);
    }

    public function remove(Request $request, string $lineKey): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);

        CartItem::where('buyer_id', $buyer->id)
            ->where('id', $lineKey)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from cart.',
            'data' => [
                'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
            ],
        ]);
    }

    public function removeMany(Request $request): JsonResponse
    {
        $data = $request->validate([
            'keys' => ['required', 'array'],
            'keys.*' => ['integer'],
        ]);

        $buyer = $this->approvedBuyer($request);

        CartItem::where('buyer_id', $buyer->id)
            ->whereIn('id', $data['keys'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected cart items removed.',
            'data' => [
                'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
            ],
        ]);
    }

    public function count(Request $request): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);

        return response()->json([
            'success' => true,
            'message' => 'Cart count loaded.',
            'data' => [
                'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
            ],
        ]);
    }

    private function approvedBuyer(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 401, 'Unauthenticated.');
        abort_unless($user->account_type === 'buyer', 403, 'Buyer account required.');
        abort_unless($user->email_verified_at, 403, 'Email verification required.');
        abort_unless($user->status === 'approved', 403, 'Approved buyer account required.');
        abort_unless($user->buyer, 403, 'Buyer profile not found.');

        return $user->buyer;
    }

    private function groupByShop($items): array
    {
        $groups = [];

        foreach ($items as $item) {
            if (! $item->product || ! $item->product->seller) {
                continue;
            }

            $seller = $item->product->seller;
            $shopId = (int) $seller->id;
            $stock = $item->availableStock();
            $isAvailable = $stock > 0 && (int) $item->quantity <= $stock;
            $price = $item->unitPrice();

            if (! isset($groups[$shopId])) {
                $groups[$shopId] = [
                    'shop' => [
                        'id' => $shopId,
                        'name' => $seller->business_name,
                        'municipality' => $seller->municipality_name,
                    ],
                    'items' => [],
                ];
            }

            $groups[$shopId]['items'][] = [
                'line_key' => (string) $item->id,
                'product_id' => (int) $item->product_id,
                'variant_id' => $item->product_variant_id
                    ? (int) $item->product_variant_id
                    : null,
                'variant' => $item->variantLabel(),
                'name' => $item->product->name,
                'image' => $this->productImageUrl($item->product->image),
                'price' => round((float) $price, 2),
                'original_price' => $item->originalPrice(),
                'qty' => (int) $item->quantity,
                'stock' => $stock,
                'is_available' => $isAvailable,
                'line_total' => round((float) $price * (int) $item->quantity, 2),
            ];
        }

        return array_values($groups);
    }

    private function vouchersForItems($items): array
    {
        $productIds = $items->pluck('product_id')->unique()->values();

        if ($productIds->isEmpty()) {
            return [];
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
                'title' => $voucher->name,
                'description' => $this->describeVoucher($voucher),
                'type' => $voucher->type,
                'value' => (float) $voucher->value,
                'min_spend' => (float) $voucher->min_order_amount,
                'max_discount' => null,
                'seller_id' => Seller::where('user_id', $voucher->seller_id)->value('id'),
                'product_ids' => $voucher->products()->pluck('products.id')->all(),
            ])
            ->values()
            ->all();
    }

    private function describeVoucher(Voucher $voucher): string
    {
        $amount = $voucher->type === 'percent'
            ? $voucher->value . '% off'
            : '₱' . number_format($voucher->value) . ' off';

        return $amount . ' on ₱' . number_format($voucher->min_order_amount) . ' minimum spend';
    }

    private function productImageUrl(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return asset('images/placeholder-product.jpg');
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['storage/', 'images/'])) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
