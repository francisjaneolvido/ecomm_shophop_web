<?php
// Path: app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Seller\Manage_inventory\Product;

class HomeController extends Controller
{
    /**
     * Show the ShopHop public landing page.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | ALL SHOP CATEGORIES
        |--------------------------------------------------------------------------
        */

        $allCategories = [
            [
                'name' => 'Pet Supplies',
                'icon' => 'paw-print',
            ],
            [
                'name' => 'Electronics and Gadgets',
                'icon' => 'smartphone',
            ],
            [
                'name' => "Women's Apparel",
                'icon' => 'shirt',
            ],
            [
                'name' => "Men's Apparel",
                'icon' => 'shirt',
            ],
            [
                'name' => 'Kids and Baby',
                'icon' => 'baby',
            ],
            [
                'name' => 'Home and Garden',
                'icon' => 'house',
            ],
            [
                'name' => 'Sports and Outdoors',
                'icon' => 'dumbbell',
            ],
            [
                'name' => 'Health and Beauty',
                'icon' => 'heart-pulse',
            ],
            [
                'name' => 'Books and Media',
                'icon' => 'book-open',
            ],
            [
                'name' => 'Food and Gourmet',
                'icon' => 'utensils',
            ],
            [
                'name' => 'Automotive & Motorcycle',
                'icon' => 'car-front',
            ],
            [
                'name' => 'Furniture and Office Equipment',
                'icon' => 'armchair',
            ],
            [
                'name' => 'Jewelry and Watches',
                'icon' => 'gem',
            ],
            [
                'name' => 'Office and School Supplies',
                'icon' => 'notebook-pen',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | CATEGORY DISPLAY
        |--------------------------------------------------------------------------
        |
        | Guest = first 7 categories
        | Logged in = all categories
        |
        */

        $categories = auth()->check()
            ? $allCategories
            : array_slice($allCategories, 0, 7);


        // Guest cards and Search must draw from the same available persisted catalogue.
        $trendingProducts = Product::query()
            ->publiclyDiscoverable()
            ->latest()
            ->take(6)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view('home', compact(
            'categories',
            'trendingProducts'
        ));
    }
}
