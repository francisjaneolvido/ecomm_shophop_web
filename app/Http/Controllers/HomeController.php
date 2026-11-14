<?php
// Path: app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;


class HomeController extends Controller
{
    /**
     * Public marketplace homepage. Every product section draws exclusively from
     * the same approved, active, in-stock catalogue as public Search.
     */
    public function index()
    {
        $sections = app(\App\Services\MarketplaceCatalogService::class)->sections();
        $flashDealProducts = $sections['deals'];
        $topProducts = $sections['top'];
        $discoverProducts = $sections['discover'];
        $videoProducts = app(\App\Services\MarketplaceCatalogService::class)->query()
            ->whereNotNull('video_path')->where('video_path', '!=', '')
            ->latest('products.created_at')->take(6)->get();
        $hasCompletedSales = $sections['has_completed_sales'];

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
            'videoProducts',
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
