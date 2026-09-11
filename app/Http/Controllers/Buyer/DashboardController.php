<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Seller\Manage_inventory\Product;

class DashboardController extends Controller
{
    /**
     * Display the buyer dashboard.
     */
    public function index()
    {
        $buyerName = auth()->user()?->first_name
            ?? auth()->user()?->name
            ?? 'Buyer';

        $categories = $this->getCategories();
        $trendingProducts = $this->getTrendingProducts();
        $vouchers = $this->getVouchers();
        $orderSummary = $this->getOrderSummary();
        $activeOrder = $this->getActiveOrder();
        $recentlyViewed = $this->getRecentlyViewed();
        $recommendedProducts = $this->getRecommendedProducts();
        $dealProducts = $this->getDealProducts();
        $newArrivals = $this->getNewArrivals();

        return view('buyer.dashboard.dashboard', compact(
            'buyerName',
            'categories',
            'trendingProducts',
            'vouchers',
            'orderSummary',
            'activeOrder',
            'recentlyViewed',
            'recommendedProducts',
            'dealProducts',
            'newArrivals'
        ));
    }


    /**
     * Base query: only products that sellers have published/approved
     * and that still have stock.
     *
     * NOTE: change 'active' below if your status column uses a
     * different value (e.g. 'approved', 'published', 1, etc.)
     */
    private function activeProductsQuery()
    {
        return Product::query()
            ->where('status', 'active')
            ->where('stock', '>', 0);
    }


    /**
     * Transform a Product model into the array shape the
     * dashboard blade views expect.
     *
     * NOTE: there is no ratings/reviews table yet, so rating and
     * reviews are placeholder values for now. Once you have a
     * reviews system, swap these two lines for real aggregates
     * (e.g. $product->reviews_avg_rating, $product->reviews_count).
     */
    private function formatProduct(Product $product): array
    {
        $price = (float) $product->price;
        $discount = (int) ($product->discount ?? 0);

        $finalPrice = $discount > 0
            ? round($price - ($price * $discount / 100))
            : $price;

        $imagePath = $product->image
            ? 'storage/' . ltrim($product->image, '/')
            : 'images/placeholder-product.jpg';

        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'price' => $finalPrice,
            'original_price' => $discount > 0 ? $price : null,
            'rating' => 4.5,   // placeholder until reviews exist
            'reviews' => 0,    // placeholder until reviews exist
            'image' => asset($imagePath),
        ];
    }


    /**
     * Categories.
     *
     * Kept as a static list (for the icon mapping), but you could
     * instead pull distinct categories from products if you want
     * it fully dynamic:
     *
     *   Product::where('status', 'active')->distinct()->pluck('category');
     */
    private function getCategories(): array
    {
        return [
            ['name' => 'Pet Supplies', 'icon' => 'paw-print'],
            ['name' => 'Electronics and Gadgets', 'icon' => 'smartphone'],
            ['name' => "Women's Apparel", 'icon' => 'shirt'],
            ['name' => "Men's Apparel", 'icon' => 'shirt'],
            ['name' => 'Kids and Baby', 'icon' => 'baby'],
            ['name' => 'Home and Garden', 'icon' => 'house'],
            ['name' => 'Sports and Outdoors', 'icon' => 'dumbbell'],
            ['name' => 'Health and Beauty', 'icon' => 'heart-pulse'],
            ['name' => 'Books and Media', 'icon' => 'book-open'],
            ['name' => 'Food and Gourmet', 'icon' => 'utensils'],
            ['name' => 'Automotive & Motorcycle', 'icon' => 'car-front'],
            ['name' => 'Furniture and Office Equipment', 'icon' => 'armchair'],
            ['name' => 'Jewelry and Watches', 'icon' => 'gem'],
            ['name' => 'Office and School Supplies', 'icon' => 'notebook-pen'],
        ];
    }


    /**
     * Trending products — newest active products, most recently added first.
     * (Swap the orderBy for a real "trending" metric like order_count once you track it.)
     */
    private function getTrendingProducts(): array
    {
        return $this->activeProductsQuery()
            ->latest()
            ->take(4)
            ->get()
            ->map(fn ($product) => $this->formatProduct($product))
            ->toArray();
    }


    /**
     * Recommended products.
     * (Simple placeholder logic: random active products.
     * Later you can base this on buyer's order/category history.)
     */
    private function getRecommendedProducts(): array
    {
        return $this->activeProductsQuery()
            ->inRandomOrder()
            ->take(5)
            ->get()
            ->map(fn ($product) => $this->formatProduct($product))
            ->toArray();
    }


    /**
     * Deal products — products that currently have a discount.
     */
    private function getDealProducts(): array
    {
        return $this->activeProductsQuery()
            ->where('discount', '>', 0)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($product) => $this->formatProduct($product))
            ->toArray();
    }


    /**
     * New arrivals — most recently added active products.
     */
    private function getNewArrivals(): array
    {
        return $this->activeProductsQuery()
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($product) => $this->formatProduct($product))
            ->toArray();
    }


    /**
     * Recently viewed products.
     *
     * NOTE: this requires tracking what the buyer actually viewed
     * (e.g. a `product_views` table with buyer_id + product_id + viewed_at).
     * Left as sample/fallback data for now — let me know if you want
     * me to build the view-tracking table + logic.
     */
    private function getRecentlyViewed(): array
    {
        return $this->activeProductsQuery()
            ->inRandomOrder()
            ->take(5)
            ->get()
            ->map(fn ($product) => $this->formatProduct($product))
            ->toArray();
    }


    /**
     * Available vouchers.
     */
    private function getVouchers(): array
    {
        return [
            [
                'title' => '₱100 Off',
                'code' => 'SHOPHOP100',
                'description' => '₱100 off when you spend at least ₱1,000.',
                'icon' => 'ticket',
            ],
            [
                'title' => 'Free Shipping',
                'code' => 'FREESHIP',
                'description' => 'Enjoy free shipping on eligible orders.',
                'icon' => 'truck',
            ],
            [
                'title' => '10% Off',
                'code' => 'WELCOME10',
                'description' => 'Save 10% on selected ShopHop products.',
                'icon' => 'badge-percent',
            ],
        ];
    }


    /**
     * Buyer order summary.
     *
     * NOTE: still static — needs an Order model/table to compute
     * real counts per status for the logged-in buyer.
     */
    private function getOrderSummary(): array
    {
        return [
            ['label' => 'To Pay', 'count' => 1, 'icon' => 'wallet'],
            ['label' => 'To Ship', 'count' => 2, 'icon' => 'package'],
            ['label' => 'To Receive', 'count' => 1, 'icon' => 'truck'],
            ['label' => 'Completed', 'count' => 5, 'icon' => 'circle-check'],
        ];
    }


    /**
     * Current active order.
     *
     * NOTE: still static — needs an Order model/table to fetch the
     * buyer's actual in-progress order.
     */
    private function getActiveOrder(): array
    {
        return [
            'order_number' => '#SHP-2026-00125',
            'product_name' => 'Wireless Earbuds Pro',
            'variant' => 'Black',
            'quantity' => 1,
            'price' => 1299,
            'status' => 'In Transit',
            'image' => 'images/products/wireless-earbuds.jpg',
            'estimated_delivery' => 'September 2 - 3',
            'steps' => [
                ['label' => 'Placed', 'icon' => 'shopping-bag', 'done' => true],
                ['label' => 'Confirmed', 'icon' => 'circle-check', 'done' => true],
                ['label' => 'Packed', 'icon' => 'package', 'done' => true],
                ['label' => 'Shipped', 'icon' => 'truck', 'done' => true],
                ['label' => 'Delivered', 'icon' => 'house', 'done' => false],
            ],
        ];
    }
}