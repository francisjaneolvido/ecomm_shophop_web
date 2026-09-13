<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Seller\Manage_inventory\Product;

class ShowProductDetails_Controller extends Controller
{
    /**
     * Show full details of a single product.
     */
    public function show(Product $product)
    {
        abort_if($product->status !== 'active', 404);

        $product->load(['seller', 'images', 'variants', 'vouchers']);

        $price = (float) $product->price;
        $discount = (int) ($product->discount ?? 0);

        $finalPrice = $discount > 0
            ? round($price - ($price * $discount / 100))
            : $price;

        // ---- Gallery: real images, fallback to single product image ----
        $galleryPaths = $product->images
            ->sortByDesc('is_primary')
            ->pluck('image_path')
            ->filter()
            ->values();

        if ($galleryPaths->isEmpty() && $product->image) {
            $galleryPaths = collect([$product->image]);
        }

        $gallery = $galleryPaths
            ->map(fn ($path) => asset('storage/' . ltrim($path, '/')))
            ->toArray();

        $mainImage = $gallery[0] ?? asset('images/placeholder-product.jpg');

        // ---- Reviews & rating ----
        $reviewRows = $product->reviews()->with('buyer')->latest()->take(20)->get();
        $reviewsCount = $product->reviews()->count();
        $avgRating = round((float) $product->reviews()->avg('rating'), 1) ?: 0;

        $ratingBreakdown = [];
        for ($stars = 5; $stars >= 1; $stars--) {
            $ratingBreakdown[$stars] = $product->reviews()->where('rating', $stars)->count();
        }

        $reviews = $reviewRows->map(function ($review) {
            $buyerName = $review->buyer?->first_name
                ? $review->buyer->first_name . ' ' . mb_substr($review->buyer->last_name ?? '', 0, 1) . '.'
                : 'ShopHop Buyer';

            return [
                'name' => $buyerName,
                'rating' => $review->rating,
                'variant' => $review->variant_label ?? '—',
                'date' => optional($review->created_at)->format('Y-m-d'),
                'comment' => $review->comment,
                'image' => $review->image_path
                    ? asset('storage/' . ltrim($review->image_path, '/'))
                    : null,
                'helpful' => $review->helpful_count,
            ];
        })->values();

        // ---- Sold count ----
        $soldCount = (int) $product->orderItems()->sum('quantity');

        // ---- Variants (flat list; group labels come from the 'attributes' JSON) ----
        $variants = $product->variants->map(function ($variant) {
            $attributes = $variant->attributes ?? [];
            $label = ! empty($attributes)
                ? implode(' · ', array_values($attributes))
                : $variant->name;

            return [
                'id' => $variant->id,
                'label' => $label,
                'price' => (float) $variant->price,
                'stock' => (int) $variant->stock,
                'in_stock' => $variant->stock > 0,
            ];
        })->values()->toArray();

        // ---- Vouchers (active only) ----
        $shopVouchers = $product->vouchers
            ->where('status', 'active')
            ->map(function ($voucher) {
                $label = $voucher->type === 'percentage'
                    ? $voucher->value . '% off'
                    : '₱' . number_format($voucher->value) . ' off';

                return [
                    'code' => $voucher->code,
                    'label' => $label,
                    'min' => $voucher->min_order_amount
                        ? 'Min. spend ₱' . number_format($voucher->min_order_amount)
                        : 'No minimum spend',
                ];
            })->values()->toArray();

        // ---- Seller stats ----
        $seller = $product->seller;
        $sellerName = $seller?->business_name ?? 'ShopHop Seller';
        $sellerProductsCount = $seller?->products()->count() ?? 0;
        $sellerJoined = $seller?->created_at?->diffForHumans() ?? '—';

        $sellerRating = $seller
            ? round((float) \App\Models\Buyer\Review\Review::whereHas('product', function ($q) use ($seller) {
                $q->where('seller_id', $seller->id);
            })->avg('rating'), 1) ?: 0
            : 0;

        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'description' => $product->description,
            'price' => $finalPrice,
            'original_price' => $discount > 0 ? $price : null,
            'discount' => $discount,
            'stock' => $product->stock,
            'in_stock' => $product->stock > 0,
            'image' => $mainImage,
            'rating' => $avgRating,
            'reviews' => $reviewsCount,
            'sold' => $soldCount,
            'ships_from' => $seller?->municipality_name ?? 'Metro Manila',
            'condition' => 'New',
            'seller_name' => $sellerName,
            'seller_rating' => $sellerRating,
            'seller_products' => $sellerProductsCount,
            'seller_joined' => $sellerJoined,
        ];

        // ---- Related products (same category, active, in stock) ----
        $relatedProducts = Product::query()
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as reviews_count')
            ->withSum('orderItems as sold_count', 'quantity')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($related) {
                $rPrice = (float) $related->price;
                $rDiscount = (int) ($related->discount ?? 0);

                $rFinalPrice = $rDiscount > 0
                    ? round($rPrice - ($rPrice * $rDiscount / 100))
                    : $rPrice;

                $rImagePath = $related->image
                    ? 'storage/' . ltrim($related->image, '/')
                    : 'images/placeholder-product.jpg';

                return [
                    'id' => $related->id,
                    'name' => $related->name,
                    'category' => $related->category,
                    'price' => $rFinalPrice,
                    'original_price' => $rDiscount > 0 ? $rPrice : null,
                    'rating' => round((float) $related->avg_rating, 1) ?: 0,
                    'reviews' => $related->reviews_count,
                    'sold' => (int) $related->sold_count,
                    'image' => asset($rImagePath),
                ];
            })
            ->toArray();

        return view('buyer.product.show-product-details', [
            'product' => $productData,
            'gallery' => $gallery,
            'variants' => $variants,
            'shopVouchers' => $shopVouchers,
            'reviews' => $reviews,
            'ratingBreakdown' => $ratingBreakdown,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}