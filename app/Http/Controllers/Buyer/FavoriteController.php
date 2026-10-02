<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Favorite\Favorite;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $buyer = Auth::user()->buyer;

        $favoriteRows = Favorite::query()
            ->with(['product.seller'])
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->get();

        $favorites = $favoriteRows
            ->filter(fn (Favorite $favorite) => $favorite->product !== null)
            ->map(fn (Favorite $favorite) => $this->formatProduct($favorite->product))
            ->values();

        $likedProductIds = $favoriteRows
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return view('buyer.likes.index', [
            'favorites' => $favorites,
            'favoriteCount' => count($likedProductIds),
            'likedProductIds' => $likedProductIds,
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $buyer = Auth::user()->buyer;

        abort_unless(
            Product::query()->publiclyDiscoverable()->whereKey($product->id)->exists(),
            404,
            'This product is no longer available.'
        );

        Favorite::firstOrCreate([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'ok' => true,
            'liked' => true,
            'product_id' => (int) $product->id,
            'favorites_count' => $this->favoriteCount($buyer->id),
        ]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $buyer = Auth::user()->buyer;

        Favorite::query()
            ->where('buyer_id', $buyer->id)
            ->where('product_id', $product->id)
            ->delete();

        return response()->json([
            'ok' => true,
            'liked' => false,
            'product_id' => (int) $product->id,
            'favorites_count' => $this->favoriteCount($buyer->id),
        ]);
    }

    private function favoriteCount(int $buyerId): int
    {
        return Schema::hasTable('favorites')
            ? Favorite::where('buyer_id', $buyerId)->count()
            : 0;
    }

    private function formatProduct(Product $product): array
    {
        $price = (float) $product->price;
        $discount = (int) ($product->discount ?? 0);
        $finalPrice = $discount > 0
            ? round($price - ($price * $discount / 100), 2)
            : $price;

        $isAvailable = $product->status === 'active'
            && $product->compliance_status === 'approved'
            && (int) $product->stock > 0;

        $imagePath = $product->image
            ? 'storage/' . ltrim($product->image, '/')
            : 'images/placeholder-product.jpg';

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
            'image' => asset($imagePath),
            'rating' => $rating,
            'reviews' => $reviews,
            'sold' => $sold,
            'is_available' => $isAvailable,
            'seller_name' => $product->seller?->business_name ?? 'ShopHop Seller',
            'seller_location' => $product->seller?->municipality_name ?? '',
        ];
    }
}
