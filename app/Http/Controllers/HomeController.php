<?php
// Path: app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Buyer\Order\Order;
use App\Models\Seller\Manage_inventory\Product;

class HomeController extends Controller
{
    /**
     * Public marketplace homepage. Every product section draws exclusively from
     * the same approved, active, in-stock catalogue as public Search.
     */
    public function index()
    {
        $publicCatalogue = fn () => Product::query()
            ->publiclyDiscoverable()
            ->with('images');

        // Seller-configured discounts, NOT a fabricated timed sale.
        $flashDealProducts = $publicCatalogue()
            ->where('discount', '>', 0)
            ->orderByDesc('discount')
            ->latest('products.created_at')
            ->take(12)
            ->get();

        // Ranking uses completed-order units only; a zero-sales marketplace
        // gracefully shows current products without invented popularity figures.
        $topProducts = $publicCatalogue()
            ->withSum([
                'orderItems as completed_units_sold' => function ($query) {
                    $query->whereHas('order', function ($orderQuery) {
                        $orderQuery->where('status', Order::STATUS_COMPLETED);
                    });
                },
            ], 'quantity')
            ->orderByDesc('completed_units_sold')
            ->latest('products.created_at')
            ->take(12)
            ->get();

        $hasCompletedSales = $topProducts->contains(
            fn (Product $product) => (int) ($product->completed_units_sold ?? 0) > 0
        );

        $discoverProducts = $publicCatalogue()
            ->latest('products.created_at')
            ->take(24)
            ->get();

        // The existing category browser has real public routes. Use its
        // configured slugs instead of guessing category URLs.
        $landingCategories = collect(config('shophop_categories', []))
            ->values();

        // Legacy home-view variables are retained for compatibility with
        // any custom view extension that reads them.
        $categories = $landingCategories->all();
        $trendingProducts = $discoverProducts->take(6);

        // The guest and logged-in Buyer share the same marketplace view.
        // Favorite state is read only for the authenticated Buyer.
        $isShopBuyer = auth()->check() && auth()->user()->account_type === 'buyer';
        $favoriteProductIds = $isShopBuyer && auth()->user()->buyer?->id && \Illuminate\Support\Facades\Schema::hasTable('favorites')
            ? \App\Models\Buyer\Favorite\Favorite::query()
                ->where('buyer_id', auth()->user()->buyer->id)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all()
            : [];

        $cartItemCount = $isShopBuyer && auth()->user()->buyer?->id && \Illuminate\Support\Facades\Schema::hasTable('cart_items')
            ? \App\Models\Buyer\Cart\CartItem::query()->where('buyer_id', auth()->user()->buyer->id)->count()
            : 0;

        return view('home', compact(
            'flashDealProducts',
            'topProducts',
            'discoverProducts',
            'landingCategories',
            'hasCompletedSales',
            'categories',
            'trendingProducts',
            'isShopBuyer',
            'favoriteProductIds',
            'cartItemCount'
        ));
    }
}
