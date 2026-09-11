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
        // Optional: only allow viewing products that are actually
        // published. Adjust 'active' if your status value differs.
        abort_if($product->status !== 'active', 404);

        $product->load('seller');

        $price = (float) $product->price;
        $discount = (int) ($product->discount ?? 0);

        $finalPrice = $discount > 0
            ? round($price - ($price * $discount / 100))
            : $price;

        $imagePath = $product->image
            ? 'storage/' . ltrim($product->image, '/')
            : 'images/placeholder-product.jpg';

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
            'image' => asset($imagePath),
            'rating' => 4.5,   // placeholder until reviews exist
            'reviews' => 0,    // placeholder until reviews exist
            'seller_name' => $product->seller?->name
                ?? $product->seller?->first_name
                ?? 'ShopHop Seller',
        ];

        $relatedProducts = Product::query()
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
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
                    'rating' => 4.5,
                    'reviews' => 0,
                    'image' => asset($rImagePath),
                ];
            })
            ->toArray();

        // NOTE: the view lives at resources/views/buyer/show-product-details.blade.php
        // (no 'product' subfolder), so the dot-path below is 'buyer.show-product-details'.
        return view('buyer.show-product-details', [
            'product' => $productData,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}