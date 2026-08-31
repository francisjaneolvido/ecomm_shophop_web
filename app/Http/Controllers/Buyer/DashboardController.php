<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;

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
     * Categories.
     */
    private function getCategories(): array
    {
        return [
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
    }


    /**
     * Trending products.
     */
    private function getTrendingProducts(): array
    {
        return [
            [
                'name' => 'Minimalist Canvas Sneakers',
                'category' => "Women's Apparel",
                'price' => 899,
                'original_price' => 1199,
                'rating' => 4.6,
                'reviews' => 128,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Sneakers',
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'category' => 'Electronics and Gadgets',
                'price' => 1299,
                'original_price' => null,
                'rating' => 4.8,
                'reviews' => 342,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Earbuds',
            ],
            [
                'name' => 'Everyday Tote Bag',
                'category' => "Women's Apparel",
                'price' => 599,
                'original_price' => 799,
                'rating' => 4.5,
                'reviews' => 96,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Tote+Bag',
            ],
            [
                'name' => 'Smart Fitness Watch',
                'category' => 'Electronics and Gadgets',
                'price' => 1799,
                'original_price' => null,
                'rating' => 4.7,
                'reviews' => 210,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Fitness+Watch',
            ],
        ];
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
     */
    private function getOrderSummary(): array
    {
        return [
            [
                'label' => 'To Pay',
                'count' => 1,
                'icon' => 'wallet',
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
                'count' => 5,
                'icon' => 'circle-check',
            ],
        ];
    }


    /**
     * Current active order.
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
                [
                    'label' => 'Placed',
                    'icon' => 'shopping-bag',
                    'done' => true,
                ],
                [
                    'label' => 'Confirmed',
                    'icon' => 'circle-check',
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
                    'label' => 'Delivered',
                    'icon' => 'house',
                    'done' => false,
                ],
            ],
        ];
    }


    /**
     * Recently viewed products.
     */
    private function getRecentlyViewed(): array
    {
        return [
            [
                'name' => 'Minimalist Canvas Sneakers',
                'category' => "Women's Apparel",
                'price' => 899,
                'original_price' => 1199,
                'rating' => 4.6,
                'reviews' => 128,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Sneakers',
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'category' => 'Electronics and Gadgets',
                'price' => 1299,
                'original_price' => null,
                'rating' => 4.8,
                'reviews' => 342,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Earbuds',
            ],
            [
                'name' => 'Everyday Tote Bag',
                'category' => "Women's Apparel",
                'price' => 599,
                'original_price' => 799,
                'rating' => 4.5,
                'reviews' => 96,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Tote+Bag',
            ],
            [
                'name' => 'Smart Fitness Watch',
                'category' => 'Electronics and Gadgets',
                'price' => 1799,
                'original_price' => null,
                'rating' => 4.7,
                'reviews' => 210,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Fitness+Watch',
            ],
            [
                'name' => 'Classic Backpack',
                'category' => 'Office and School Supplies',
                'price' => 749,
                'original_price' => 999,
                'rating' => 4.4,
                'reviews' => 82,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Backpack',
            ],
        ];
    }


    /**
     * Recommended products.
     */
    private function getRecommendedProducts(): array
    {
        return [
            [
                'name' => 'Portable Bluetooth Speaker',
                'category' => 'Electronics and Gadgets',
                'price' => 999,
                'original_price' => 1299,
                'rating' => 4.7,
                'reviews' => 183,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Speaker',
            ],
            [
                'name' => 'Premium Water Bottle',
                'category' => 'Sports and Outdoors',
                'price' => 499,
                'original_price' => null,
                'rating' => 4.8,
                'reviews' => 276,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Water+Bottle',
            ],
            [
                'name' => 'Casual Everyday Shirt',
                'category' => "Men's Apparel",
                'price' => 699,
                'original_price' => 899,
                'rating' => 4.6,
                'reviews' => 156,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Shirt',
            ],
            [
                'name' => 'Desk Lamp Pro',
                'category' => 'Furniture and Office Equipment',
                'price' => 799,
                'original_price' => 1099,
                'rating' => 4.7,
                'reviews' => 103,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Desk+Lamp',
            ],
            [
                'name' => 'Wireless Charging Pad',
                'category' => 'Electronics and Gadgets',
                'price' => 599,
                'original_price' => null,
                'rating' => 4.5,
                'reviews' => 221,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Wireless+Charger',
            ],
        ];
    }


    /**
     * Deal products.
     */
    private function getDealProducts(): array
    {
        return [
            [
                'name' => 'Mechanical Keyboard',
                'category' => 'Electronics and Gadgets',
                'price' => 1499,
                'original_price' => 1999,
                'rating' => 4.8,
                'reviews' => 315,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Keyboard',
            ],
            [
                'name' => 'Running Shoes',
                'category' => 'Sports and Outdoors',
                'price' => 1199,
                'original_price' => 1599,
                'rating' => 4.6,
                'reviews' => 208,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Running+Shoes',
            ],
            [
                'name' => 'Smart LED Lamp',
                'category' => 'Home and Garden',
                'price' => 699,
                'original_price' => 999,
                'rating' => 4.5,
                'reviews' => 151,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Smart+Lamp',
            ],
            [
                'name' => 'Travel Backpack',
                'category' => 'Sports and Outdoors',
                'price' => 899,
                'original_price' => 1299,
                'rating' => 4.7,
                'reviews' => 187,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Travel+Backpack',
            ],
            [
                'name' => 'Digital Watch',
                'category' => 'Jewelry and Watches',
                'price' => 799,
                'original_price' => 1199,
                'rating' => 4.4,
                'reviews' => 129,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Digital+Watch',
            ],
        ];
    }


    /**
     * New arrival products.
     */
    private function getNewArrivals(): array
    {
        return [
            [
                'name' => 'Compact Wireless Mouse',
                'category' => 'Electronics and Gadgets',
                'price' => 499,
                'original_price' => null,
                'rating' => 4.7,
                'reviews' => 74,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Wireless+Mouse',
            ],
            [
                'name' => 'Minimalist Desk Organizer',
                'category' => 'Office and School Supplies',
                'price' => 399,
                'original_price' => null,
                'rating' => 4.6,
                'reviews' => 63,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Desk+Organizer',
            ],
            [
                'name' => 'Classic Wrist Watch',
                'category' => 'Jewelry and Watches',
                'price' => 1299,
                'original_price' => null,
                'rating' => 4.8,
                'reviews' => 91,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Wrist+Watch',
            ],
            [
                'name' => 'Portable Mini Fan',
                'category' => 'Home and Garden',
                'price' => 349,
                'original_price' => null,
                'rating' => 4.5,
                'reviews' => 118,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Mini+Fan',
            ],
            [
                'name' => 'Everyday Crossbody Bag',
                'category' => "Women's Apparel",
                'price' => 649,
                'original_price' => null,
                'rating' => 4.7,
                'reviews' => 86,
                'image' => 'https://placehold.co/500x375/F3F5F7/0F1B3D?text=Crossbody+Bag',
            ],
        ];
    }
}