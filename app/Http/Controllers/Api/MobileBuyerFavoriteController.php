<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Favorite\Favorite;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MobileBuyerFavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);

        $favorites = Favorite::query()
            ->with(['product.seller'])
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->get()
            ->filter(fn (Favorite $favorite) => $favorite->product !== null)
            ->map(fn (Favorite $favorite) => $this->productCard($favorite->product))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Likes loaded.',
            'data' => [
                'items' => $favorites,
                'count' => $favorites->count(),
            ],
        ]);
    }

    public function store(Request $request, int $product): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);

        $item = Product::query()
            ->publiclyDiscoverable()
            ->findOrFail($product);

        Favorite::firstOrCreate([
            'buyer_id' => $buyer->id,
            'product_id' => $item->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to My Likes.',
            'data' => [
                'product_id' => (int) $item->id,
                'is_liked' => true,
                'favorites_count' => Favorite::where('buyer_id', $buyer->id)->count(),
            ],
        ]);
    }

    public function destroy(Request $request, int $product): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);

        Favorite::query()
            ->where('buyer_id', $buyer->id)
            ->where('product_id', $product)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from My Likes.',
            'data' => [
                'product_id' => $product,
                'is_liked' => false,
                'favorites_count' => Favorite::where('buyer_id', $buyer->id)->count(),
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

    private function productCard(Product $product): array
    {
        $price = (float) $product->price;
        $discount = (int) ($product->discount ?? 0);
        $finalPrice = $discount > 0
            ? round($price - ($price * $discount / 100), 2)
            : $price;

        $rating = 0;
        $reviews = 0;
        $sold = 0;

        if (Schema::hasTable('reviews')) {
            $rating = round((float) $product->reviews()->avg('rating'), 1) ?: 0;
            $reviews = $product->reviews()->count();
        }

        if (Schema::hasTable('order_items')) {
            $sold = (int) $product->orderItems()->sum('quantity');
        }

        return [
            'id' => (int) $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'price' => $finalPrice,
            'original_price' => $discount > 0 ? $price : null,
            'discount_percent' => $discount,
            'stock' => (int) $product->stock,
            'image' => $this->productImageUrl($product->image),
            'rating' => $rating,
            'reviews' => $reviews,
            'sold' => $sold,
            'has_variants' => (bool) $product->has_variants,
            'is_liked' => true,
            'is_available' => $product->status === 'active'
                && $product->compliance_status === 'approved'
                && (int) $product->stock > 0,
            'seller' => [
                'id' => $product->seller?->id,
                'business_name' => $product->seller?->business_name ?? 'ShopHop Seller',
                'municipality' => $product->seller?->municipality_name,
            ],
        ];
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
