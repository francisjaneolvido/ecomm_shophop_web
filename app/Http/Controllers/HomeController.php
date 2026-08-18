<?php
// Path in your project: app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the ShopHop landing page.
     */
    public function index()
    {
        // Later: pull these from the database (Category, Product models)
        $categories = [
            ['name' => 'Fashion',       'icon' => 'shirt'],
            ['name' => 'Electronics',   'icon' => 'cpu'],
            ['name' => 'Beauty',        'icon' => 'sparkles'],
            ['name' => 'Home & Living', 'icon' => 'home'],
            ['name' => 'Sports',        'icon' => 'dumbbell'],
            ['name' => 'Accessories',   'icon' => 'watch'],
            ['name' => 'Grocery',       'icon' => 'shopping-basket'],
            ['name' => 'Gadgets',       'icon' => 'smartphone'],
        ];

        // TEMPORARY placeholder images (via placehold.co) so the layout looks
        // complete while you don't have real product photos yet.
        // Once you export real images from Figma, drop them into
        // public/images/products/ and change 'image' back to a filename like
        // 'products/sneakers.jpg' — the Blade view already knows how to
        // handle both a full URL and a local filename (see home.blade.php).
        $trendingProducts = [
            [
                'name' => 'Minimalist Canvas Sneakers',
                'category' => 'Fashion',
                'price' => 899,
                'original_price' => 1199,
                'rating' => 4.6,
                'reviews' => 128,
                'image' => 'https://placehold.co/400x400/F3F5F7/0F1B3D?text=Sneakers',
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'category' => 'Electronics',
                'price' => 1299,
                'original_price' => null,
                'rating' => 4.8,
                'reviews' => 342,
                'image' => 'https://placehold.co/400x400/F3F5F7/0F1B3D?text=Earbuds',
            ],
            [
                'name' => 'Everyday Tote Bag',
                'category' => 'Accessories',
                'price' => 599,
                'original_price' => 799,
                'rating' => 4.5,
                'reviews' => 96,
                'image' => 'https://placehold.co/400x400/F3F5F7/0F1B3D?text=Tote+Bag',
            ],
            [
                'name' => 'Smart Fitness Watch',
                'category' => 'Gadgets',
                'price' => 1799,
                'original_price' => null,
                'rating' => 4.7,
                'reviews' => 210,
                'image' => 'https://placehold.co/400x400/F3F5F7/0F1B3D?text=Fitness+Watch',
            ],
        ];

        return view('home', compact('categories', 'trendingProducts'));
    }
}