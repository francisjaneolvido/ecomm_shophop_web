{{-- Path: resources/views/buyer/store/show.blade.php --}}

@extends('layouts.app')

@php
    /*
    |--------------------------------------------------------------------------
    | TEMPORARY STORE PREVIEW DATA
    |--------------------------------------------------------------------------
    | Replace these arrays with controller / DB data later.
    */

    $store = $store ?? [
        'id' => 1,
        'slug' => 'shophop-tech-store',
        'name' => 'ShopHop Tech Store',
        'username' => 'shophoptech',
        'logo' => null,
        'banner' => null,
        'is_preferred' => true,
        'is_mall' => true,
        'active_text' => 'Active 2 minutes ago',
        'followers' => 354200,
        'following' => 4,
        'rating' => 4.8,
        'rating_count' => 330000,
        'product_count' => 126,
        'response_rate' => 96,
        'response_time' => 'within minutes',
        'joined' => '2 years ago',
        'description' => 'Official ShopHop technology store for gadgets, audio, wearables, and everyday electronics.',
    ];

    $storeCategories = $storeCategories ?? [
        'All Products',
        'Audio & Earbuds',
        'Smart Watches',
        'Mobile Accessories',
        'Computer Accessories',
        'Home Electronics',
    ];

    $vouchers = $vouchers ?? [
        [
            'title' => '₱100 OFF',
            'code' => 'TECH100',
            'description' => 'Min. spend ₱1,000',
            'valid_until' => 'Sep 30',
        ],
        [
            'title' => '10% OFF',
            'code' => 'TECH10',
            'description' => 'Min. spend ₱699 · Max ₱200',
            'valid_until' => 'Sep 30',
        ],
        [
            'title' => 'FREE SHIPPING',
            'code' => 'SHIPFREE',
            'description' => 'Min. spend ₱499',
            'valid_until' => 'Sep 30',
        ],
        [
            'title' => '₱50 OFF',
            'code' => 'WELCOME50',
            'description' => 'New followers only',
            'valid_until' => 'Oct 15',
        ],
    ];

    $recommendedProducts = $recommendedProducts ?? collect([
        [
            'id' => 1,
            'name' => 'Wireless Earbuds Pro with ENC Noise Reduction',
            'category' => 'Audio & Earbuds',
            'image' => 'images/hero/earbuds.jpg',
            'price' => 1299,
            'original_price' => 1699,
            'rating' => 4.9,
            'sold' => 1200,
            'is_new' => false,
        ],
        [
            'id' => 2,
            'name' => 'ShopHop Fitness Watch with Heart Rate Tracking',
            'category' => 'Smart Watches',
            'image' => 'images/hero/watch.jpg',
            'price' => 2499,
            'original_price' => 2899,
            'rating' => 4.8,
            'sold' => 940,
            'is_new' => true,
        ],
        [
            'id' => 3,
            'name' => 'Everyday Wireless Earbuds Lite',
            'category' => 'Audio & Earbuds',
            'image' => 'images/hero/earbuds.jpg',
            'price' => 899,
            'original_price' => 1099,
            'rating' => 4.7,
            'sold' => 720,
            'is_new' => false,
        ],
        [
            'id' => 4,
            'name' => 'Active Smart Watch Series 5',
            'category' => 'Smart Watches',
            'image' => 'images/hero/watch.jpg',
            'price' => 1899,
            'original_price' => 2199,
            'rating' => 4.9,
            'sold' => 680,
            'is_new' => true,
        ],
        [
            'id' => 5,
            'name' => 'Pocket Bluetooth Earbuds',
            'category' => 'Audio & Earbuds',
            'image' => 'images/hero/earbuds.jpg',
            'price' => 749,
            'original_price' => null,
            'rating' => 4.6,
            'sold' => 510,
            'is_new' => false,
        ],
    ]);

    $allProducts = $allProducts ?? collect([
        ...$recommendedProducts->toArray(),
        [
            'id' => 6,
            'name' => 'Noise Cancelling Wireless Buds',
            'category' => 'Audio & Earbuds',
            'image' => 'images/hero/earbuds.jpg',
            'price' => 1599,
            'original_price' => 1999,
            'rating' => 4.9,
            'sold' => 610,
            'is_new' => false,
        ],
        [
            'id' => 7,
            'name' => 'Fitness Watch Mini',
            'category' => 'Smart Watches',
            'image' => 'images/hero/watch.jpg',
            'price' => 1499,
            'original_price' => 1799,
            'rating' => 4.7,
            'sold' => 440,
            'is_new' => true,
        ],
        [
            'id' => 8,
            'name' => 'Bluetooth Earphones Basic',
            'category' => 'Audio & Earbuds',
            'image' => 'images/hero/earbuds.jpg',
            'price' => 599,
            'original_price' => 799,
            'rating' => 4.5,
            'sold' => 350,
            'is_new' => false,
        ],
        [
            'id' => 9,
            'name' => 'Smart Watch Active Plus',
            'category' => 'Smart Watches',
            'image' => 'images/hero/watch.jpg',
            'price' => 2199,
            'original_price' => 2599,
            'rating' => 4.8,
            'sold' => 520,
            'is_new' => true,
        ],
        [
            'id' => 10,
            'name' => 'Compact TWS Earbuds',
            'category' => 'Audio & Earbuds',
            'image' => 'images/hero/earbuds.jpg',
            'price' => 999,
            'original_price' => 1299,
            'rating' => 4.8,
            'sold' => 790,
            'is_new' => false,
        ],
    ]);
@endphp

@section('title', $store['name'] . ' - ShopHop')
@section('hideChrome', true)

@section('content')

{{-- =========================================================
    MAIN SHOPHOP NAVBAR
    Keep the global buyer navbar for consistency.
========================================================= --}}
@include('buyer.partials.navbar-buyer')


{{-- =========================================================
    BREADCRUMB
========================================================= --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <nav class="flex items-center gap-1.5 text-[10px] sm:text-[10.5px] text-navy/45">
            <a href="{{ route('buyer.dashboard') }}" class="hover:text-teal-dark transition">Home</a>
            <x-lucide-chevron-right class="w-3 h-3 text-navy/25" />
            <span class="text-navy font-medium">{{ $store['name'] }}</span>
        </nav>
    </div>
</div>


{{-- =========================================================
    STORE HERO
========================================================= --}}
<section class="bg-gray-bg/75 pt-4 sm:pt-5">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid lg:grid-cols-[360px_minmax(0,1fr)] gap-3 sm:gap-4">

            {{-- Store identity --}}
            <div class="relative overflow-hidden rounded-2xl border border-gray-border bg-navy text-white min-h-44.5 shadow-sm">

                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(33,195,166,.32),transparent_42%),linear-gradient(135deg,#0F1B3D_0%,#152951_100%)]"></div>

                <div class="absolute -right-12 -bottom-16 w-40 h-40 rounded-full bg-teal/15"></div>
                <div class="absolute right-16 -top-16 w-32 h-32 rounded-full bg-white/5"></div>

                <div class="relative h-full p-4 flex flex-col justify-between">

                    <div class="flex items-center gap-3">

                        <div class="relative w-14 h-14 rounded-full bg-white border-[3px] border-white/80 flex items-center justify-center overflow-hidden shrink-0 shadow-md">
                            @if (! empty($store['logo']))
                                <img src="{{ asset($store['logo']) }}" alt="{{ $store['name'] }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-[17px] font-black text-teal-dark">
                                    {{ strtoupper(mb_substr($store['name'], 0, 1)) }}
                                </span>
                            @endif

                            @if (! empty($store['is_mall']))
                                <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-teal text-white text-[6.5px] font-bold px-1.5 py-0.5 rounded whitespace-nowrap">
                                    MALL
                                </span>
                            @endif
                        </div>


                        <div class="min-w-0">

                            <div class="flex items-center gap-1.5 min-w-0">
                                <p role="heading" aria-level="1"
                                   class="text-[16px] sm:text-[18px] font-bold truncate">
                                    {{ $store['name'] }}
                                </p>

                                @if (! empty($store['is_preferred']))
                                    <x-lucide-badge-check class="w-4 h-4 text-teal shrink-0" />
                                @endif
                            </div>

                            <p class="text-[9px] text-white/55 mt-0.5">
                                {{ $store['active_text'] }}
                            </p>

                            <p class="text-[8px] text-white/40 mt-0.5">
                                @{{ $store['username'] }}
                            </p>
                        </div>
                    </div>


                    <div class="grid grid-cols-2 gap-2 mt-4">

                        <button type="button"
                                id="followStoreBtn"
                                data-following="false"
                                class="h-8 rounded-lg border border-white/45 hover:border-teal hover:bg-teal
                                       text-[9.5px] font-semibold flex items-center justify-center gap-1.5 transition">
                            <x-lucide-plus class="w-3 h-3" />
                            <span data-follow-label>Follow</span>
                        </button>

                        <button type="button"
                                class="h-8 rounded-lg border border-white/45 hover:border-teal hover:bg-white/10
                                       text-[9.5px] font-semibold flex items-center justify-center gap-1.5 transition">
                            <x-lucide-message-circle class="w-3 h-3" />
                            Chat
                        </button>

                    </div>
                </div>
            </div>


            {{-- Store metrics / search --}}
            <div class="bg-white border border-gray-border rounded-2xl shadow-sm p-4">

                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-3 flex-1">

                        <div>
                            <p class="text-[8.5px] text-navy/35">Products</p>
                            <p class="text-[11px] font-bold text-navy mt-0.5">
                                {{ number_format($store['product_count']) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[8.5px] text-navy/35">Followers</p>
                            <p id="followerCount"
                               data-base-count="{{ $store['followers'] }}"
                               class="text-[11px] font-bold text-teal-dark mt-0.5">
                                {{ number_format($store['followers']) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[8.5px] text-navy/35">Following</p>
                            <p class="text-[11px] font-bold text-navy mt-0.5">
                                {{ number_format($store['following']) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[8.5px] text-navy/35">Rating</p>
                            <p class="text-[11px] font-bold text-teal-dark mt-0.5">
                                {{ $store['rating'] }}
                                <span class="font-normal text-[8px] text-navy/35">
                                    ({{ number_format($store['rating_count']) }})
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-[8.5px] text-navy/35">Chat Performance</p>
                            <p class="text-[11px] font-bold text-teal-dark mt-0.5">
                                {{ $store['response_rate'] }}%
                                <span class="font-normal text-[8px] text-navy/35">
                                    {{ $store['response_time'] }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-[8.5px] text-navy/35">Joined</p>
                            <p class="text-[11px] font-bold text-navy mt-0.5">
                                {{ $store['joined'] }}
                            </p>
                        </div>
                    </div>


                    <div class="w-full md:w-70 shrink-0">

                        <label for="storeSearch" class="text-[8.5px] font-medium text-navy/40">
                            Search in this shop
                        </label>

                        <div class="relative mt-1.5">
                            <x-lucide-search class="w-3.5 h-3.5 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />

                            <input id="storeSearch"
                                   type="search"
                                   placeholder="Search products..."
                                   class="w-full h-9 rounded-xl border border-gray-border bg-gray-bg/70
                                          pl-8.5 pr-3 text-[9.5px] text-navy
                                          placeholder:text-navy/30 outline-none
                                          focus:border-teal focus:bg-white focus:ring-1 focus:ring-teal/10 transition">
                        </div>

                        <p class="text-[8px] text-navy/30 mt-1.5">
                            Search only products from {{ $store['name'] }}.
                        </p>

                    </div>
                </div>


                <div class="mt-4 pt-3 border-t border-gray-border/80">

                    <p class="text-[9px] leading-relaxed text-navy/45">
                        {{ $store['description'] }}
                    </p>

                </div>
            </div>

        </div>
    </div>
</section>


{{-- =========================================================
    STORE NAVBAR / SUBNAV
========================================================= --}}
<div class="sticky top-0 z-30 bg-white border-y border-gray-border/70 shadow-sm shadow-navy/2 mt-4">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-center gap-1 overflow-x-auto [&::-webkit-scrollbar]:hidden">

            @foreach ([
                ['label' => 'Home', 'target' => 'store-home'],
                ['label' => 'All Products', 'target' => 'store-products'],
                ['label' => 'Best Sellers', 'target' => 'store-recommended'],
                ['label' => 'Vouchers', 'target' => 'store-vouchers'],
            ] as $index => $tab)

                <a href="#{{ $tab['target'] }}"
                   class="store-nav-link relative shrink-0 px-4 py-3 text-[9.5px] font-semibold
                          {{ $index === 0 ? 'text-teal-dark' : 'text-navy/55 hover:text-teal-dark' }}
                          transition"
                   data-store-nav="{{ $tab['target'] }}">

                    {{ $tab['label'] }}

                    @if ($index === 0)
                        <span class="store-nav-indicator absolute left-3 right-3 bottom-0 h-0.5 bg-teal rounded-full"></span>
                    @else
                        <span class="store-nav-indicator hidden absolute left-3 right-3 bottom-0 h-0.5 bg-teal rounded-full"></span>
                    @endif
                </a>

            @endforeach

        </div>
    </div>
</div>


{{-- =========================================================
    STORE HOME / PROMO
========================================================= --}}
<section id="store-home" class="bg-gray-bg/75 py-5 sm:py-6 scroll-mt-14">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid lg:grid-cols-[1.35fr_.65fr] gap-3">

            {{-- Main promo --}}
            <div class="relative min-h-55 sm:min-h-61.25 rounded-2xl overflow-hidden border border-teal/10 bg-[#EAF9F5]">

                <div class="absolute -right-16 -top-20 w-56 h-56 rounded-full bg-teal/12"></div>
                <div class="absolute -left-12 -bottom-20 w-52 h-52 rounded-full bg-white/70"></div>

                <div class="relative h-full grid sm:grid-cols-[1fr_260px] items-center">

                    <div class="p-5 sm:p-6 lg:p-7">

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white
                                     text-[8px] font-bold text-teal-dark shadow-sm">
                            <x-lucide-sparkles class="w-3 h-3" />
                            STORE EXCLUSIVE
                        </span>

                        <p role="heading" aria-level="2"
                           class="text-[22px] sm:text-[26px] font-bold tracking-tight leading-[1.08] text-navy mt-3 max-w-lg">
                            Tech deals made
                            <span class="text-teal-dark">simpler.</span>
                        </p>

                        <p class="text-[10px] sm:text-[11px] text-navy/50 leading-relaxed mt-2 max-w-md">
                            Shop trusted gadgets, wearables, and accessories from the official {{ $store['name'] }}.
                        </p>

                        <a href="#store-products"
                           class="inline-flex items-center gap-1.5 h-9 px-4 mt-4 rounded-xl
                                  bg-teal hover:bg-teal-dark text-white text-[9.5px] font-semibold transition">
                            Shop All Products
                            <x-lucide-arrow-right class="w-3 h-3" />
                        </a>

                    </div>


                    <div class="relative hidden sm:block h-full min-h-55">

                        <div class="absolute right-8 top-6 w-40 h-40 rounded-3xl overflow-hidden
                                    bg-white shadow-xl shadow-navy/10 rotate-[5deg]">
                            <img src="{{ asset('images/hero/earbuds.jpg') }}"
                                 alt=""
                                 class="w-full h-full object-cover">
                        </div>

                        <div class="absolute right-36 bottom-5 w-24 h-24 rounded-2xl overflow-hidden
                                    bg-white shadow-lg shadow-navy/10 -rotate-6">
                            <img src="{{ asset('images/hero/watch.jpg') }}"
                                 alt=""
                                 class="w-full h-full object-cover">
                        </div>

                    </div>
                </div>
            </div>


            {{-- Shopping process --}}
            <div class="bg-white border border-gray-border rounded-2xl p-4 shadow-sm">

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-bold text-teal-dark uppercase tracking-widest">
                            Easy Shopping
                        </p>

                        <p class="text-[13px] font-bold text-navy mt-1">
                            From cart to doorstep
                        </p>
                    </div>

                    <x-lucide-package-check class="w-5 h-5 text-teal-dark" />
                </div>


                <div class="grid grid-cols-4 gap-1.5 mt-5">

                    @foreach ([
                        ['icon' => 'shopping-cart', 'label' => 'Order'],
                        ['icon' => 'wallet-cards', 'label' => 'Pay'],
                        ['icon' => 'truck', 'label' => 'Ship'],
                        ['icon' => 'package-check', 'label' => 'Receive'],
                    ] as $step)

                        <div class="text-center">

                            <span class="w-9 h-9 mx-auto rounded-xl bg-teal-light text-teal-dark
                                         flex items-center justify-center">
                                <x-dynamic-component :component="'lucide-' . $step['icon']" class="w-4 h-4" />
                            </span>

                            <p class="text-[8px] font-semibold text-navy/55 mt-1.5">
                                {{ $step['label'] }}
                            </p>

                        </div>

                    @endforeach

                </div>


                <div class="rounded-xl bg-gray-bg mt-4 p-3">

                    <div class="flex items-start gap-2">
                        <x-lucide-headphones class="w-4 h-4 text-teal-dark shrink-0 mt-0.5" />

                        <div>
                            <p class="text-[9px] font-semibold text-navy">
                                Store Support
                            </p>

                            <p class="text-[8px] text-navy/40 mt-0.5 leading-relaxed">
                                Monday–Sunday · 8:00 AM–10:00 PM
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>


{{-- =========================================================
    VOUCHERS
========================================================= --}}
<section id="store-vouchers" class="bg-white py-5 sm:py-6 scroll-mt-14">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">

            <div>
                <p class="text-[8.5px] font-bold text-teal-dark uppercase tracking-widest">
                    Save More
                </p>

                <p role="heading" aria-level="2"
                   class="text-[17px] sm:text-[18px] font-bold text-navy mt-0.5">
                    Store Vouchers
                </p>
            </div>

            <p class="text-[8.5px] text-navy/35 hidden sm:block">
                Claim before checkout
            </p>

        </div>


        <div class="grid grid-flow-col auto-cols-[85%] sm:auto-cols-[45%] lg:auto-cols-[30%]
                    gap-2.5 overflow-x-auto snap-x snap-mandatory pb-1 [&::-webkit-scrollbar]:hidden">

            @foreach ($vouchers as $voucher)

                <article class="voucher-card snap-start relative overflow-hidden bg-[#FFF8F7]
                                border border-red-100 rounded-xl p-3">

                    <div class="absolute -left-2 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-white border-r border-red-100"></div>
                    <div class="absolute -right-2 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-white border-l border-red-100"></div>

                    <div class="flex items-center justify-between gap-3">

                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-red-500">
                                {{ $voucher['title'] }}
                            </p>

                            <p class="text-[8.5px] text-navy/50 mt-1">
                                {{ $voucher['description'] }}
                            </p>

                            <div class="flex items-center gap-1.5 mt-2">
                                <span class="text-[7.5px] font-mono font-bold bg-white border border-red-100
                                             text-red-500 px-1.5 py-0.5 rounded">
                                    {{ $voucher['code'] }}
                                </span>

                                <span class="text-[7px] text-navy/30">
                                    Until {{ $voucher['valid_until'] }}
                                </span>
                            </div>
                        </div>


                        <button type="button"
                                class="claim-voucher-btn shrink-0 h-8 px-3 rounded-lg
                                       bg-teal hover:bg-teal-dark text-white text-[8.5px] font-bold transition"
                                data-code="{{ $voucher['code'] }}">
                            Claim
                        </button>

                    </div>

                </article>

            @endforeach

        </div>

    </div>
</section>


{{-- =========================================================
    RECOMMENDED
========================================================= --}}
<section id="store-recommended" class="bg-gray-bg/75 py-5 sm:py-6 scroll-mt-14">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">

            <div>
                <p class="text-[8.5px] font-bold text-teal-dark uppercase tracking-widest">
                    Popular Picks
                </p>

                <p role="heading" aria-level="2"
                   class="text-[17px] sm:text-[18px] font-bold text-navy mt-0.5">
                    Recommended For You
                </p>
            </div>

            <a href="#store-products"
               class="inline-flex items-center gap-1 text-[9px] font-semibold text-teal-dark hover:text-navy transition">
                See all
                <x-lucide-arrow-right class="w-3 h-3" />
            </a>

        </div>


        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">

            @foreach ($recommendedProducts->take(5) as $product)

                <article class="store-product-card group bg-white border border-gray-border rounded-xl overflow-hidden
                                hover:border-teal/30 hover:-translate-y-0.5 hover:shadow-lg
                                transition-all duration-300"
                         data-product-name="{{ strtolower($product['name'] . ' ' . $product['category']) }}"
                         data-category="{{ $product['category'] }}"
                         data-price="{{ $product['price'] }}"
                         data-sold="{{ $product['sold'] }}"
                         data-new="{{ !empty($product['is_new']) ? 1 : 0 }}">

                    <a href="#"
                       class="relative block aspect-square bg-white overflow-hidden">

                        <img src="{{ asset($product['image']) }}"
                             alt="{{ $product['name'] }}"
                             class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300">

                        @if ($product['original_price'])
                            <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded
                                         bg-teal text-white text-[7.5px] font-bold">
                                SALE
                            </span>
                        @endif

                    </a>


                    <div class="p-2.5">

                        <p class="text-[7.5px] text-navy/35 truncate">
                            {{ $product['category'] }}
                        </p>

                        <p class="text-[10px] sm:text-[10.5px] font-semibold text-navy leading-[1.35]
                                  line-clamp-2 min-h-[2.7em] mt-0.5">
                            {{ $product['name'] }}
                        </p>

                        <div class="flex flex-wrap items-baseline gap-1.5 mt-2">

                            <span class="text-[12px] font-bold text-teal-dark">
                                ₱{{ number_format($product['price']) }}
                            </span>

                            @if ($product['original_price'])
                                <span class="text-[8px] text-navy/30 line-through">
                                    ₱{{ number_format($product['original_price']) }}
                                </span>
                            @endif

                        </div>


                        <div class="flex items-center justify-between gap-2 mt-2">

                            <span class="inline-flex items-center gap-1 text-[8px] text-navy/40">
                                <x-lucide-star class="w-2.5 h-2.5 fill-amber-400 text-amber-400" />
                                {{ $product['rating'] }}
                            </span>

                            <span class="text-[8px] text-navy/35">
                                {{ number_format($product['sold']) }} sold
                            </span>

                        </div>

                    </div>
                </article>

            @endforeach

        </div>

    </div>
</section>


{{-- =========================================================
    ALL PRODUCTS
========================================================= --}}
<section id="store-products" class="bg-white py-5 sm:py-6 scroll-mt-14">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid lg:grid-cols-[190px_minmax(0,1fr)] gap-4 items-start">

            {{-- Category filter --}}
            <aside class="hidden lg:block sticky top-14 bg-white border border-gray-border rounded-xl overflow-hidden">

                <div class="px-3 py-2.5 border-b border-gray-border">
                    <div class="flex items-center gap-1.5">
                        <x-lucide-list class="w-3.5 h-3.5 text-teal-dark" />

                        <p class="text-[10.5px] font-bold text-navy">
                            Categories
                        </p>
                    </div>
                </div>


                <nav class="py-1.5" id="storeCategoryFilter">

                    @foreach ($storeCategories as $index => $category)

                        <button type="button"
                                class="store-category-filter w-full flex items-center gap-1.5 px-3 py-2 text-left
                                       text-[9.5px] transition
                                       {{ $index === 0 ? 'text-teal-dark font-semibold bg-teal-light/40' : 'text-navy/55 hover:text-teal-dark hover:bg-gray-bg' }}"
                                data-category-filter="{{ $category }}">

                            <x-lucide-chevron-right class="w-2.5 h-2.5 shrink-0" />

                            <span class="truncate">
                                {{ $category }}
                            </span>
                        </button>

                    @endforeach

                </nav>
            </aside>


            {{-- Products --}}
            <div class="min-w-0">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5
                            bg-gray-bg/70 border border-gray-border rounded-xl px-3 py-2.5 mb-3">

                    <div class="flex items-center gap-2 overflow-x-auto [&::-webkit-scrollbar]:hidden">

                        <span class="text-[8.5px] text-navy/40 shrink-0">
                            Sort by
                        </span>

                        @foreach ([
                            ['key' => 'popular', 'label' => 'Popular'],
                            ['key' => 'latest', 'label' => 'Latest'],
                            ['key' => 'top-sales', 'label' => 'Top Sales'],
                        ] as $index => $sort)

                            <button type="button"
                                    class="store-sort-btn shrink-0 h-7 px-3 rounded-lg text-[8.5px] font-semibold transition
                                           {{ $index === 0 ? 'bg-teal text-white' : 'bg-white border border-gray-border text-navy/55 hover:text-teal-dark' }}"
                                    data-sort="{{ $sort['key'] }}">
                                {{ $sort['label'] }}
                            </button>

                        @endforeach


                        <select id="storePriceSort"
                                class="h-7 rounded-lg border border-gray-border bg-white
                                       px-2 text-[8.5px] text-navy/55 outline-none">
                            <option value="">Price</option>
                            <option value="price-asc">Low to High</option>
                            <option value="price-desc">High to Low</option>
                        </select>

                    </div>


                    <p class="text-[8.5px] text-navy/35 shrink-0">
                        <span id="storeProductCount">{{ $allProducts->count() }}</span> products
                    </p>
                </div>


                <div id="storeProductsGrid"
                     class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-3">

                    @foreach ($allProducts as $product)

                        <article class="store-product-card group bg-white border border-gray-border rounded-xl overflow-hidden
                                        hover:border-teal/30 hover:-translate-y-0.5 hover:shadow-lg
                                        transition-all duration-300"
                                 data-product-name="{{ strtolower($product['name'] . ' ' . $product['category']) }}"
                                 data-category="{{ $product['category'] }}"
                                 data-price="{{ $product['price'] }}"
                                 data-sold="{{ $product['sold'] }}"
                                 data-new="{{ !empty($product['is_new']) ? 1 : 0 }}">

                            <a href="#"
                               class="relative block aspect-square bg-gray-bg overflow-hidden">

                                <img src="{{ asset($product['image']) }}"
                                     alt="{{ $product['name'] }}"
                                     class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300">

                                @if (! empty($product['is_new']))
                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded
                                                 bg-navy/90 text-white text-[7.5px] font-bold">
                                        NEW
                                    </span>
                                @elseif ($product['original_price'])
                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded
                                                 bg-teal text-white text-[7.5px] font-bold">
                                        SALE
                                    </span>
                                @endif


                                <button type="button"
                                        class="absolute top-1.5 right-1.5 w-6 h-6 rounded-lg
                                               bg-white/95 shadow-sm flex items-center justify-center
                                               text-navy/45 hover:text-teal-dark transition">
                                    <x-lucide-heart class="w-3 h-3" />
                                </button>

                            </a>


                            <div class="p-2.5">

                                <p class="text-[7.5px] text-navy/35 truncate">
                                    {{ $product['category'] }}
                                </p>

                                <p class="text-[10px] sm:text-[10.5px] font-semibold text-navy
                                          leading-[1.35] line-clamp-2 min-h-[2.7em] mt-0.5">
                                    {{ $product['name'] }}
                                </p>

                                <div class="flex flex-wrap items-baseline gap-1.5 mt-2">

                                    <span class="text-[12px] font-bold text-teal-dark">
                                        ₱{{ number_format($product['price']) }}
                                    </span>

                                    @if ($product['original_price'])
                                        <span class="text-[8px] text-navy/30 line-through">
                                            ₱{{ number_format($product['original_price']) }}
                                        </span>
                                    @endif

                                </div>


                                <div class="flex items-center justify-between gap-2 mt-2">

                                    <span class="inline-flex items-center gap-1 text-[8px] text-navy/40">
                                        <x-lucide-star class="w-2.5 h-2.5 fill-amber-400 text-amber-400" />
                                        {{ $product['rating'] }}
                                    </span>

                                    <span class="text-[8px] text-navy/35">
                                        {{ number_format($product['sold']) }} sold
                                    </span>

                                </div>


                                <button type="button"
                                        class="w-full h-8 mt-2.5 rounded-lg bg-teal hover:bg-teal-dark
                                               text-white text-[8.5px] font-semibold transition">
                                    Add to Cart
                                </button>

                            </div>
                        </article>

                    @endforeach

                </div>


                <div id="storeNoResults"
                     class="hidden py-12 text-center">

                    <x-lucide-package-search class="w-8 h-8 text-navy/20 mx-auto" />

                    <p class="text-[11px] font-semibold text-navy/55 mt-2">
                        No products found
                    </p>

                    <p class="text-[9px] text-navy/35 mt-1">
                        Try another search or category.
                    </p>

                </div>

            </div>
        </div>

    </div>
</section>


{{-- Floating chat --}}
<button type="button"
        class="fixed right-4 bottom-4 z-40 h-10 px-4 rounded-xl
               bg-white border border-gray-border shadow-lg
               text-[10px] font-semibold text-teal-dark
               flex items-center gap-1.5 hover:bg-teal hover:text-white transition">
    <x-lucide-message-square class="w-3.5 h-3.5" />
    Chat
</button>


{{-- Toast --}}
<div id="storeToast"
     class="fixed left-1/2 bottom-5 z-50 -translate-x-1/2 translate-y-6 opacity-0 pointer-events-none
            bg-navy text-white text-[10px] font-medium px-3.5 py-2.5 rounded-xl
            shadow-xl transition-all duration-300">
</div>


@include('partials.footer')

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const storeToast = document.getElementById('storeToast');

    function showStoreToast(message) {
        if (!storeToast) return;

        storeToast.textContent = message;

        storeToast.classList.remove('translate-y-6', 'opacity-0');
        storeToast.classList.add('translate-y-0', 'opacity-100');

        clearTimeout(showStoreToast.timer);

        showStoreToast.timer = setTimeout(function () {
            storeToast.classList.remove('translate-y-0', 'opacity-100');
            storeToast.classList.add('translate-y-6', 'opacity-0');
        }, 2200);
    }


    /*
    |--------------------------------------------------------------------------
    | Follow store
    |--------------------------------------------------------------------------
    */
    const followButton = document.getElementById('followStoreBtn');
    const followLabel = followButton?.querySelector('[data-follow-label]');
    const followerCount = document.getElementById('followerCount');

    followButton?.addEventListener('click', function () {
        const currentlyFollowing =
            followButton.dataset.following === 'true';

        const nextFollowing =
            !currentlyFollowing;

        followButton.dataset.following =
            String(nextFollowing);

        if (followLabel) {
            followLabel.textContent =
                nextFollowing ? 'Following' : 'Follow';
        }

        followButton.classList.toggle(
            'bg-teal',
            nextFollowing
        );

        followButton.classList.toggle(
            'border-teal',
            nextFollowing
        );

        if (followerCount) {
            const base =
                Number(followerCount.dataset.baseCount || 0);

            followerCount.textContent =
                (base + (nextFollowing ? 1 : 0))
                    .toLocaleString('en-PH');
        }

        showStoreToast(
            nextFollowing
                ? 'You are now following this store.'
                : 'Store unfollowed.'
        );
    });


    /*
    |--------------------------------------------------------------------------
    | Voucher claim
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.claim-voucher-btn').forEach(function (button) {
        button.addEventListener('click', function () {

            const claimed =
                button.dataset.claimed === 'true';

            if (claimed) return;

            button.dataset.claimed = 'true';
            button.textContent = 'Claimed';

            button.classList.remove(
                'bg-teal',
                'hover:bg-teal-dark'
            );

            button.classList.add(
                'bg-teal-light',
                'text-teal-dark'
            );

            showStoreToast(
                button.dataset.code + ' added to your vouchers.'
            );
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Store navigation
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.store-nav-link').forEach(function (link) {
        link.addEventListener('click', function () {

            document.querySelectorAll('.store-nav-link').forEach(function (item) {
                item.classList.remove('text-teal-dark');
                item.classList.add('text-navy/55');

                item.querySelector('.store-nav-indicator')
                    ?.classList.add('hidden');
            });

            link.classList.remove('text-navy/55');
            link.classList.add('text-teal-dark');

            link.querySelector('.store-nav-indicator')
                ?.classList.remove('hidden');
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Product filter / search / sort
    |--------------------------------------------------------------------------
    */
    const storeSearch = document.getElementById('storeSearch');
    const productsGrid = document.getElementById('storeProductsGrid');
    const noResults = document.getElementById('storeNoResults');
    const productCount = document.getElementById('storeProductCount');

    let activeCategory = 'All Products';
    let activeSort = 'popular';
    let searchTerm = '';

    const originalCards =
        Array.from(
            productsGrid?.querySelectorAll('.store-product-card') || []
        );

    function visibleCards() {
        return originalCards.filter(function (card) {

            const matchesSearch =
                !searchTerm ||
                (card.dataset.productName || '').includes(searchTerm);

            const matchesCategory =
                activeCategory === 'All Products' ||
                card.dataset.category === activeCategory;

            return matchesSearch && matchesCategory;
        });
    }


    function sortCards(cards) {
        const sorted = [...cards];

        if (activeSort === 'latest') {
            sorted.sort(function (a, b) {
                return Number(b.dataset.new || 0) -
                       Number(a.dataset.new || 0);
            });
        }

        if (activeSort === 'top-sales') {
            sorted.sort(function (a, b) {
                return Number(b.dataset.sold || 0) -
                       Number(a.dataset.sold || 0);
            });
        }

        if (activeSort === 'price-asc') {
            sorted.sort(function (a, b) {
                return Number(a.dataset.price || 0) -
                       Number(b.dataset.price || 0);
            });
        }

        if (activeSort === 'price-desc') {
            sorted.sort(function (a, b) {
                return Number(b.dataset.price || 0) -
                       Number(a.dataset.price || 0);
            });
        }

        if (activeSort === 'popular') {
            sorted.sort(function (a, b) {
                return Number(b.dataset.sold || 0) -
                       Number(a.dataset.sold || 0);
            });
        }

        return sorted;
    }


    function renderProducts() {
        if (!productsGrid) return;

        const matched =
            sortCards(visibleCards());

        originalCards.forEach(function (card) {
            card.classList.add('hidden');
        });

        matched.forEach(function (card) {
            card.classList.remove('hidden');
            productsGrid.appendChild(card);
        });

        if (productCount) {
            productCount.textContent =
                matched.length;
        }

        noResults?.classList.toggle(
            'hidden',
            matched.length !== 0
        );
    }


    storeSearch?.addEventListener('input', function () {
        searchTerm =
            storeSearch.value.trim().toLowerCase();

        renderProducts();

        if (searchTerm) {
            document.getElementById('store-products')
                ?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
        }
    });


    document.querySelectorAll('.store-category-filter').forEach(function (button) {
        button.addEventListener('click', function () {

            activeCategory =
                button.dataset.categoryFilter;

            document.querySelectorAll('.store-category-filter').forEach(function (item) {
                item.classList.remove(
                    'text-teal-dark',
                    'font-semibold',
                    'bg-teal-light/40'
                );

                item.classList.add(
                    'text-navy/55'
                );
            });

            button.classList.remove(
                'text-navy/55'
            );

            button.classList.add(
                'text-teal-dark',
                'font-semibold',
                'bg-teal-light/40'
            );

            renderProducts();
        });
    });


    document.querySelectorAll('.store-sort-btn').forEach(function (button) {
        button.addEventListener('click', function () {

            activeSort =
                button.dataset.sort;

            document.querySelectorAll('.store-sort-btn').forEach(function (item) {
                item.classList.remove(
                    'bg-teal',
                    'text-white'
                );

                item.classList.add(
                    'bg-white',
                    'border',
                    'border-gray-border',
                    'text-navy/55'
                );
            });

            button.classList.remove(
                'bg-white',
                'border',
                'border-gray-border',
                'text-navy/55'
            );

            button.classList.add(
                'bg-teal',
                'text-white'
            );

            const priceSort =
                document.getElementById('storePriceSort');

            if (priceSort) {
                priceSort.value = '';
            }

            renderProducts();
        });
    });


    document.getElementById('storePriceSort')
        ?.addEventListener('change', function (event) {

            if (!event.target.value) {
                return;
            }

            activeSort =
                event.target.value;

            document.querySelectorAll('.store-sort-btn').forEach(function (item) {
                item.classList.remove(
                    'bg-teal',
                    'text-white'
                );

                item.classList.add(
                    'bg-white',
                    'border',
                    'border-gray-border',
                    'text-navy/55'
                );
            });

            renderProducts();
        });


    renderProducts();

});
</script>
@endpush
