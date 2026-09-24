<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductVariant;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $buyer = Auth::user()->buyer;

        $items = CartItem::with(['product.seller', 'variant'])
            ->where('buyer_id', $buyer->id)
            ->get();

        $cartGroups = $this->groupByShop($items);
        $vouchers = $this->vouchersForItems($items);
        $cartItemCount = $items->count();
        $buyNowLineKey = $request->query('buy_now');

        return view('buyer.cart.cart', compact(
            'cartGroups',
            'vouchers',
            'cartItemCount',
            'buyNowLineKey'
        ));
    }

    public function add(Request $request)
    {
        // Only canonical active merchandise may become a persisted cart line; client price and stock are ignored.
        $data = $request->validate([
            'product_id'         => 'required|exists:products,id',
            'variant_id'         => 'nullable|exists:product_variants,id',
            'qty'                => 'nullable|integer|min:1',
        ]);

        $buyer = Auth::user()->buyer;
        $product = Product::with('seller')->findOrFail($data['product_id']);
        $variant = isset($data['variant_id']) ? ProductVariant::findOrFail($data['variant_id']) : null;
        // Seller Inventory's Product stock is the aggregate cap for variant additions.
        $stock = $variant
            ? min((int) $variant->stock, (int) $product->stock)
            : (int) $product->stock;

        // Variant identity must match the Product's variant mode and owner; both inventory levels follow its active gate.
        if ($product->status !== 'active' || ! $product->seller || (int) $product->stock <= 0
            || ($product->has_variants && ! $variant)
            || (! $product->has_variants && $variant)
            || ($variant && ((int) $variant->product_id !== (int) $product->id
                || $variant->status !== 'active' || $variant->price === null))
            || $stock <= 0) {
            throw ValidationException::withMessages(['product_id' => 'This product or variant is unavailable.']);
        }

        $existing = CartItem::where('buyer_id', $buyer->id)
            ->where('product_id', $data['product_id'])
            ->where('product_variant_id', $data['variant_id'] ?? null)
            ->first();

        $qty = (int) ($data['qty'] ?? 1);

        // Reject excess quantity instead of reporting a successful add with a silently reduced quantity.
        if ($qty + ($existing?->quantity ?? 0) > $stock) {
            throw ValidationException::withMessages(['qty' => "Only {$stock} left in stock."]);
        }

        if ($existing) {
            $existing->quantity += $qty;
            $existing->save();
            $cartItem = $existing;
        } else {
            $cartItem = CartItem::create([
                'buyer_id'           => $buyer->id,
                'product_id'         => $data['product_id'],
                'product_variant_id' => $data['variant_id'] ?? null,
                'quantity'           => $qty,
            ]);
        }

        return response()->json([
            'ok'         => true,
            'line_key'   => (string) $cartItem->id,
            'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
        ]);
    }

    public function update(Request $request, string $lineKey)
    {
        $data = $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        $buyer = Auth::user()->buyer;

        $cartItem = CartItem::with(['product', 'variant'])
            ->where('buyer_id', $buyer->id)
            ->findOrFail($lineKey);

        // An owned Cart line still needs current active Product, variant mode, and stock; no fallback is invented.
        if (! $cartItem->product || $cartItem->product->status !== 'active'
            || (int) $cartItem->product->stock <= 0
            || ($cartItem->product->has_variants !== (bool) $cartItem->product_variant_id)
            || ($cartItem->product_variant_id && (! $cartItem->variant
                || (int) $cartItem->variant->product_id !== (int) $cartItem->product_id
                || $cartItem->variant->status !== 'active'))
            || $data['qty'] > $cartItem->availableStock()) {
            throw ValidationException::withMessages(['qty' => 'This quantity is no longer available.']);
        }
        $cartItem->quantity = $data['qty'];
        $cartItem->save();

        return response()->json([
            'ok'         => true,
            'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
        ]);
    }

    public function remove(string $lineKey)
    {
        $buyer = Auth::user()->buyer;

        CartItem::where('buyer_id', $buyer->id)->where('id', $lineKey)->delete();

        return response()->json([
            'ok'         => true,
            'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
        ]);
    }

    public function removeMany(Request $request)
    {
        $data = $request->validate([
            'keys'   => 'required|array',
            'keys.*' => 'integer',
        ]);

        $buyer = Auth::user()->buyer;

        CartItem::where('buyer_id', $buyer->id)
            ->whereIn('id', $data['keys'])
            ->delete();

        return response()->json([
            'ok'         => true,
            'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
        ]);
    }

    public function count()
    {
        $buyer = Auth::user()->buyer;

        return response()->json([
            'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
        ]);
    }

    /**
     * Group cart items by seller ("shop"), shaping each row the way the
     * Blade views expect (line_key, name, image, price, variant label, etc).
     */
    private function groupByShop($items): array
    {
        $groups = [];

        foreach ($items as $item) {
            $seller = $item->product->seller;
            $shopId = $seller->id;

            if (! isset($groups[$shopId])) {
                $groups[$shopId] = [
                    'shop' => [
                        'id'            => $seller->id,
                        'name'          => $seller->business_name,
                        'response_rate' => null,
                        'preferred'     => false,
                    ],
                    'items' => [],
                ];
            }

            $groups[$shopId]['items'][] = [
                'line_key'       => (string) $item->id,
                'product_id'     => $item->product_id,
                'variant_id'     => $item->product_variant_id,
                'variant'        => $item->variantLabel(),
                'name'           => $item->product->name,
                'image'          => $item->product->image
                    ? Storage::url($item->product->image)
                    : asset('images/products/placeholder.png'),
                'price'          => $item->unitPrice(),
                'original_price' => $item->originalPrice(),
                'qty'            => $item->quantity,
                'stock'          => $item->availableStock(),
            ];
        }

        return array_values($groups);
    }

    /**
     * Only vouchers that apply to at least one product currently in the cart.
     */
    private function vouchersForItems($items): \Illuminate\Support\Collection
    {
        $productIds = $items->pluck('product_id')->unique()->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Voucher::whereHas('products', function ($query) use ($productIds) {
                $query->whereIn('products.id', $productIds);
            })
            // Cart suggestions must not advertise exhausted or out-of-window codes.
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
                'code'         => $voucher->code,
                'title'        => $voucher->name,
                'description'  => $this->describeVoucher($voucher),
                'type'         => $voucher->type,
                'value'        => (float) $voucher->value,
                'min_spend'    => (float) $voucher->min_order_amount,
                'max_discount' => null,
                // Voucher ownership stores users.id while grouped cart shops use sellers.id.
                'seller_id'    => Seller::where('user_id', $voucher->seller_id)->value('id'),
                // Assignment IDs keep the Cart preview within this Voucher's eligible Products.
                'product_ids'  => $voucher->products()->pluck('products.id')->all(),
            ]);
    }

    private function describeVoucher(Voucher $voucher): string
    {
        $amount = $voucher->type === 'percent'
            ? $voucher->value . '% off'
            : '₱' . number_format($voucher->value) . ' off';

        return $amount . ' on ₱' . number_format($voucher->min_order_amount) . ' minimum spend';
    }
}
