<?php
// Path: app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the ShopHop landing page.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | SHOP CATEGORIES
        |--------------------------------------------------------------------------
        | Temporary static data.
        | Later, these can be fetched from the database using a Category model.
        |
        | "Group 9", "Group 12", etc. are intentionally NOT included
        | in the displayed category names.
        */

        $categories = [
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
        | TRENDING PRODUCTS
        |--------------------------------------------------------------------------
        | Temporary placeholder data.
        | Replace these with database products later.
        */

        $trendingProducts = [
            [
                'name' => 'Minimalist Canvas Sneakers',
                'category' => "Women's Apparel",
                'price' => 899,
                'original_price' => 1199,
                'rating' => 4.6,
                'reviews' => 128,
                'image' => 'https://placehold.co/400x400/F3F5F7/0F1B3D?text=Sneakers',
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'category' => 'Electronics and Gadgets',
                'price' => 1299,
                'original_price' => null,
                'rating' => 4.8,
                'reviews' => 342,
                'image' => 'https://placehold.co/400x400/F3F5F7/0F1B3D?text=Earbuds',
            ],
            [
                'name' => 'Everyday Tote Bag',
                'category' => "Women's Apparel",
                'price' => 599,
                'original_price' => 799,
                'rating' => 4.5,
                'reviews' => 96,
                'image' => 'https://placehold.co/400x400/F3F5F7/0F1B3D?text=Tote+Bag',
            ],
            [
                'name' => 'Smart Fitness Watch',
                'category' => 'Electronics and Gadgets',
                'price' => 1799,
                'original_price' => null,
                'rating' => 4.7,
                'reviews' => 210,
                'image' => 'https://placehold.co/400x400/F3F5F7/0F1B3D?text=Fitness+Watch',
            ],
        ];


        return view('home', compact(
            'categories',
            'trendingProducts'
        ));
    }
}