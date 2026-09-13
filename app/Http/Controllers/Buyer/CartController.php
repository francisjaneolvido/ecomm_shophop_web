<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Session-based shopping cart.
 *
 * No database table required — cart lines live in the session under
 * `shophop_cart`, keyed by a line_key (product_id + variant_id).
 * Swap the storage layer for a real `cart_items` table later without
 * touching the Blade views if you move to a persistent cart.
 */
class CartController extends Controller
{
    private const SESSION_KEY = 'shophop_cart';

    public function index(Request $request)
    {
        $cart = $this->getCart();

        $cartGroups = $this->groupByShop($cart);
        $vouchers = $this->vouchers();
        $cartItemCount = count($cart);
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
        $data = $request->validate([
            'product_id'          => 'required',
            'variant_id'          => 'nullable',
            'variant_label'       => 'nullable|string',
            'name'                => 'required|string',
            'image'               => 'nullable|string',
            'price'               => 'required|numeric',
            'original_price'      => 'nullable|numeric',
            'stock'               => 'nullable|integer',
            'qty'                 => 'nullable|integer|min:1',
            'shop_id'             => 'nullable',
            'shop_name'           => 'nullable|string',
            'shop_response_rate'  => 'nullable|string',
            'shop_preferred'      => 'nullable|boolean',
        ]);

        $cart = $this->getCart();

        $lineKey = $this->lineKey($data['product_id'], $data['variant_id'] ?? null);
        $qty = max(1, (int) ($data['qty'] ?? 1));
        $stock = (int) ($data['stock'] ?? 99);
        $stock = $stock > 0 ? $stock : 99;

        if (isset($cart[$lineKey])) {
            $cart[$lineKey]['qty'] = min($stock, $cart[$lineKey]['qty'] + $qty);
        } else {
            $cart[$lineKey] = [
                'line_key'           => $lineKey,
                'product_id'         => $data['product_id'],
                'variant_id'         => $data['variant_id'] ?? null,
                'variant'            => $data['variant_label'] ?? 'Standard',
                'name'               => $data['name'],
                'image'              => $data['image'] ?? 'images/products/placeholder.png',
                'price'              => (float) $data['price'],
                'original_price'     => isset($data['original_price']) ? (float) $data['original_price'] : null,
                'qty'                => min($stock, $qty),
                'stock'              => $stock,
                'shop_id'            => $data['shop_id'] ?? 0,
                'shop_name'          => $data['shop_name'] ?? 'ShopHop Seller',
                'shop_response_rate' => $data['shop_response_rate'] ?? null,
                'shop_preferred'     => (bool) ($data['shop_preferred'] ?? false),
            ];
        }

        $this->saveCart($cart);

        return response()->json([
            'ok'         => true,
            'line_key'   => $lineKey,
            'cart_count' => count($cart),
        ]);
    }

    public function update(Request $request, string $lineKey)
    {
        $data = $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();

        if (isset($cart[$lineKey])) {
            $max = $cart[$lineKey]['stock'] ?: 99;
            $cart[$lineKey]['qty'] = max(1, min($max, $data['qty']));
            $this->saveCart($cart);
        }

        return response()->json([
            'ok'         => true,
            'cart_count' => count($cart),
        ]);
    }

    public function remove(string $lineKey)
    {
        $cart = $this->getCart();
        unset($cart[$lineKey]);
        $this->saveCart($cart);

        return response()->json([
            'ok'         => true,
            'cart_count' => count($cart),
        ]);
    }

    public function removeMany(Request $request)
    {
        $data = $request->validate([
            'keys'   => 'required|array',
            'keys.*' => 'string',
        ]);

        $cart = $this->getCart();

        foreach ($data['keys'] as $key) {
            unset($cart[$key]);
        }

        $this->saveCart($cart);

        return response()->json([
            'ok'         => true,
            'cart_count' => count($cart),
        ]);
    }

    public function count()
    {
        return response()->json([
            'cart_count' => count($this->getCart()),
        ]);
    }

    private function getCart(): array
    {
        return session(self::SESSION_KEY, []);
    }

    private function saveCart(array $cart): void
    {
        session([self::SESSION_KEY => $cart]);
    }

    private function lineKey($productId, $variantId = null): string
    {
        return Str::slug($productId . '-' . ($variantId ?: 'default'), '_');
    }

    private function groupByShop(array $cart): array
    {
        $groups = [];

        foreach ($cart as $lineKey => $item) {
            $shopId = $item['shop_id'] ?: 0;

            if (! isset($groups[$shopId])) {
                $groups[$shopId] = [
                    'shop' => [
                        'id'            => $shopId,
                        'name'          => $item['shop_name'],
                        'response_rate' => $item['shop_response_rate'],
                        'preferred'     => $item['shop_preferred'],
                    ],
                    'items' => [],
                ];
            }

            $groups[$shopId]['items'][] = array_merge($item, ['line_key' => $lineKey]);
        }

        return array_values($groups);
    }

    private function vouchers(): \Illuminate\Support\Collection
    {
        return collect([
            [
                'code'          => 'SHOPHOP100',
                'title'         => '₱100 Off',
                'description'   => 'Save ₱100 when you spend at least ₱1,000.',
                'type'          => 'fixed',
                'value'         => 100,
                'min_spend'     => 1000,
                'max_discount'  => null,
            ],
            [
                'code'          => 'WELCOME10',
                'title'         => '10% Off',
                'description'   => '10% off orders ₱500+, up to ₱200.',
                'type'          => 'percent',
                'value'         => 10,
                'min_spend'     => 500,
                'max_discount'  => 200,
            ],
            [
                'code'          => 'SAVE50',
                'title'         => '₱50 Off',
                'description'   => 'Save ₱50 when you spend at least ₱699.',
                'type'          => 'fixed',
                'value'         => 50,
                'min_spend'     => 699,
                'max_discount'  => null,
            ],
        ]);
    }
}