<?php

// Path: app/Http/Controllers/Buyer/DashboardController.php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the Buyer Dashboard.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | SHOP CATEGORIES
        |--------------------------------------------------------------------------
        */

        $categories = [
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


        /*
        |--------------------------------------------------------------------------
        | BUYER ORDER SUMMARY
        |--------------------------------------------------------------------------
        */

        $orderSummary = [
            [
                'label' => 'To Pay',
                'count' => 1,
                'icon' => 'credit-card',
            ],
            [
                'label' => 'To Ship',
                'count' => 2,
                'icon' => 'package',
            ],
            [
                'label' => 'To Receive',
                'count' => 1,
                'icon' => 'truck',
            ],
            [
                'label' => 'Completed',
                'count' => 8,
                'icon' => 'check',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | ACTIVE ORDER
        |--------------------------------------------------------------------------
        */

        $activeOrder = [
            'order_number' => 'SHP-10234',
            'product_name' => 'Wireless Earbuds Pro',
            'variant' => 'White',
            'quantity' => 1,
            'price' => 1299,
            'image' => 'images/hero/earbuds.jpg',
            'status' => 'Shipped',
            'estimated_delivery' => 'Aug 30 - Sep 1',

            'steps' => [
                [
                    'label' => 'Order Placed',
                    'icon' => 'shopping-bag',
                    'done' => true,
                ],
                [
                    'label' => 'Packed',
                    'icon' => 'package',
                    'done' => true,
                ],
                [
                    'label' => 'Shipped',
                    'icon' => 'truck',
                    'done' => true,
                ],
                [
                    'label' => 'Out for Delivery',
                    'icon' => 'map-pin',
                    'done' => false,
                ],
                [
                    'label' => 'Delivered',
                    'icon' => 'home',
                    'done' => false,
                ],
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | RECENTLY VIEWED PRODUCTS
        |--------------------------------------------------------------------------
        | Five items so the xl:grid-cols-5 layout is filled on desktop.
        */

        $recentlyViewed = [
            [
                'name' => 'Minimalist Canvas Sneakers',
                'category' => "Women's Apparel",
                'price' => 899,
                'original_price' => 1199,
                'rating' => 4.6,
                'reviews' => 128,
                'image' => 'images/hero/sneaker.jpg',
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'category' => 'Electronics and Gadgets',
                'price' => 1299,
                'original_price' => null,
                'rating' => 4.8,
                'reviews' => 342,
                'image' => 'images/hero/earbuds.jpg',
            ],
            [
                'name' => 'Smart Fitness Watch',
                'category' => 'Electronics and Gadgets',
                'price' => 1799,
                'original_price' => 2199,
                'rating' => 4.7,
                'reviews' => 210,
                'image' => 'images/hero/watch.jpg',
            ],
            [
                'name' => 'Everyday Tote Bag',
                'category' => "Women's Apparel",
                'price' => 599,
                'original_price' => 799,
                'rating' => 4.5,
                'reviews' => 96,
                'image' => 'https://placehold.co/600x450/F3F5F7/0B1B33?text=Tote+Bag',
            ],
            [
                'name' => 'Classic Everyday Backpack',
                'category' => "Men's Apparel",
                'price' => 1199,
                'original_price' => 1499,
                'rating' => 4.9,
                'reviews' => 264,
                'image' => 'https://placehold.co/600x450/E9F8F4/0B1B33?text=Backpack',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | RECOMMENDED PRODUCTS
        |--------------------------------------------------------------------------
        */

        $recommendedProducts = [
            [
                'name' => 'Portable Bluetooth Speaker',
                'category' => 'Electronics and Gadgets',
                'price' => 1099,
                'original_price' => 1499,
                'rating' => 4.8,
                'reviews' => 187,
                'image' => 'https://placehold.co/600x450/E9F8F4/0B1B33?text=Bluetooth+Speaker',
            ],
            [
                'name' => 'Daily Skin Care Set',
                'category' => 'Health and Beauty',
                'price' => 749,
                'original_price' => null,
                'rating' => 4.7,
                'reviews' => 154,
                'image' => 'https://placehold.co/600x450/F3F5F7/0B1B33?text=Skin+Care',
            ],
            [
                'name' => 'Modern Desk Lamp',
                'category' => 'Home and Garden',
                'price' => 899,
                'original_price' => 1099,
                'rating' => 4.6,
                'reviews' => 83,
                'image' => 'https://placehold.co/600x450/E9F8F4/0B1B33?text=Desk+Lamp',
            ],
            [
                'name' => 'Classic Everyday Backpack',
                'category' => "Men's Apparel",
                'price' => 1199,
                'original_price' => 1499,
                'rating' => 4.9,
                'reviews' => 264,
                'image' => 'https://placehold.co/600x450/F3F5F7/0B1B33?text=Backpack',
            ],
            [
                'name' => 'Stainless Travel Tumbler',
                'category' => 'Home and Garden',
                'price' => 649,
                'original_price' => 849,
                'rating' => 4.8,
                'reviews' => 119,
                'image' => 'https://placehold.co/600x450/E9F8F4/0B1B33?text=Travel+Tumbler',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | BUYER VOUCHERS
        |--------------------------------------------------------------------------
        */

        $vouchers = [
            [
                'title' => 'Free Shipping',
                'code' => 'FREESHIP',
                'description' => 'No minimum spend',
                'icon' => 'truck',
            ],
            [
                'title' => '₱100 OFF',
                'code' => 'SHOP100',
                'description' => 'Minimum spend ₱1,000',
                'icon' => 'ticket',
            ],
            [
                'title' => '20% OFF',
                'code' => 'ELEC20',
                'description' => 'Selected electronics',
                'icon' => 'badge-percent',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | DEALS FOR YOU
        |--------------------------------------------------------------------------
        | Five items to match the compact five-column desktop product grid.
        */

        $dealProducts = [
            [
                'name' => 'Smart Fitness Watch',
                'category' => 'Electronics',
                'price' => 1799,
                'original_price' => 2299,
                'rating' => 4.7,
                'reviews' => 210,
                'image' => 'images/hero/watch.jpg',
            ],
            [
                'name' => 'Minimalist Canvas Sneakers',
                'category' => "Women's Apparel",
                'price' => 899,
                'original_price' => 1199,
                'rating' => 4.6,
                'reviews' => 128,
                'image' => 'images/hero/sneaker.jpg',
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'category' => 'Electronics',
                'price' => 1099,
                'original_price' => 1499,
                'rating' => 4.8,
                'reviews' => 342,
                'image' => 'images/hero/earbuds.jpg',
            ],
            [
                'name' => 'Premium Water Bottle',
                'category' => 'Sports and Outdoors',
                'price' => 499,
                'original_price' => 699,
                'rating' => 4.5,
                'reviews' => 75,
                'image' => 'https://placehold.co/600x450/E9F8F4/0B1B33?text=Water+Bottle',
            ],
            [
                'name' => 'Portable Bluetooth Speaker',
                'category' => 'Electronics and Gadgets',
                'price' => 999,
                'original_price' => 1399,
                'rating' => 4.8,
                'reviews' => 187,
                'image' => 'https://placehold.co/600x450/F3F5F7/0B1B33?text=Bluetooth+Speaker',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | NEW ARRIVALS
        |--------------------------------------------------------------------------
        */

        $newArrivals = [
            [
                'name' => 'Wireless Mechanical Keyboard',
                'category' => 'Electronics and Gadgets',
                'price' => 1599,
                'original_price' => null,
                'rating' => 4.8,
                'reviews' => 45,
                'image' => 'https://placehold.co/600x450/F3F5F7/0B1B33?text=Keyboard',
            ],
            [
                'name' => 'Everyday Crossbody Bag',
                'category' => "Women's Apparel",
                'price' => 699,
                'original_price' => null,
                'rating' => 4.7,
                'reviews' => 39,
                'image' => 'https://placehold.co/600x450/E9F8F4/0B1B33?text=Crossbody+Bag',
            ],
            [
                'name' => 'Minimal Table Organizer',
                'category' => 'Office and School Supplies',
                'price' => 399,
                'original_price' => null,
                'rating' => 4.6,
                'reviews' => 24,
                'image' => 'https://placehold.co/600x450/F3F5F7/0B1B33?text=Organizer',
            ],
            [
                'name' => 'Portable Mini Fan',
                'category' => 'Home and Garden',
                'price' => 499,
                'original_price' => null,
                'rating' => 4.7,
                'reviews' => 61,
                'image' => 'https://placehold.co/600x450/E9F8F4/0B1B33?text=Mini+Fan',
            ],
            [
                'name' => 'Compact Power Bank',
                'category' => 'Electronics and Gadgets',
                'price' => 899,
                'original_price' => null,
                'rating' => 4.8,
                'reviews' => 52,
                'image' => 'https://placehold.co/600x450/F3F5F7/0B1B33?text=Power+Bank',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        | Your current project structure is:
        | resources/views/buyer/dashboard/dashboard.blade.php
        */

        return view('buyer.dashboard.dashboard', compact(
            'categories',
            'orderSummary',
            'activeOrder',
            'recentlyViewed',
            'recommendedProducts',
            'vouchers',
            'dealProducts',
            'newArrivals'
        ));
    }
}