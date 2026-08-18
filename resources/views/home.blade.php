{{-- Path: resources/views/home.blade.php --}}

@extends('layouts.app')

@section('title', 'ShopHop — Hop In. Shop More.')

@section('content')

    {{-- =========================================================
        HERO SECTION
    ========================================================== --}}
    <section class="bg-gray-bg overflow-hidden">
        <div class="max-w-310 mx-auto px-6 py-16 lg:py-20">

            <div class="grid lg:grid-cols-2 gap-10 items-center">

                {{-- =========================
                    HERO LEFT CONTENT
                ========================== --}}
                <div>

                    {{-- Small badge --}}
                    <div class="inline-flex items-center gap-2 bg-teal/10 text-teal-dark px-4 py-2 rounded-full text-xs font-medium mb-6">
                        <span class="text-teal">✦</span>
                        New arrivals every day
                    </div>

                    {{-- Main heading --}}
                    <h1 class="font-serif text-5xl lg:text-[58px] leading-[1.05] text-navy">
                        Everything You Love,
                        <span class="block text-teal">
                            Just a Hop Away.
                        </span>
                    </h1>

                    {{-- Description --}}
                    <p class="text-navy/65 text-base leading-7 max-w-120 mt-6">
                        Discover everyday essentials, trending finds,
                        and products you'll love — all in one place.
                    </p>

                    {{-- Buttons --}}
                    <div class="flex flex-wrap gap-3 mt-7">

                        <a
                            href="#products"
                            class="bg-teal hover:bg-teal-dark text-white font-semibold text-sm px-7 py-3.5 rounded-full shadow-md transition"
                        >
                            Shop Now
                        </a>

                        <a
                            href="#categories"
                            class="border border-navy text-navy font-semibold text-sm px-7 py-3.5 rounded-full hover:bg-navy hover:text-white transition"
                        >
                            Explore Categories
                        </a>

                    </div>

                    {{-- Statistics --}}
                    <div class="flex items-center gap-6 mt-8">

                        <div>
                            <strong class="block text-xl font-bold text-navy">
                                50K+
                            </strong>

                            <span class="text-xs text-navy/50">
                                Products
                            </span>
                        </div>

                        <div class="w-px h-8 bg-navy/10"></div>

                        <div>
                            <strong class="block text-xl font-bold text-navy">
                                2M+
                            </strong>

                            <span class="text-xs text-navy/50">
                                Happy Shoppers
                            </span>
                        </div>

                        <div class="w-px h-8 bg-navy/10"></div>

                        <div>
                            <strong class="block text-xl font-bold text-navy">
                                4.9★
                            </strong>

                            <span class="text-xs text-navy/50">
                                App Rating
                            </span>
                        </div>

                    </div>

                </div>


                {{-- =========================
                    HERO RIGHT VISUAL
                ========================== --}}
                <div class="relative min-h-105 w-full">

                    {{-- Decorative glow --}}
                    <div class="absolute w-72 h-72 bg-teal/10 rounded-full blur-3xl right-10 top-16"></div>


                    {{-- =========================
                        DISCOUNT BADGE
                    ========================== --}}
                    <div
                        class="absolute right-1 top-0 z-30
                               w-14 h-14 rounded-full
                               bg-navy text-white
                               flex flex-col items-center justify-center
                               text-[8px] leading-2.5
                               font-bold text-center
                               shadow-lg"
                    >
                        <span>UP TO</span>
                        <span class="text-teal text-[10px]">50%</span>
                        <span>OFF</span>
                    </div>


                    {{-- =========================
                        EARBUDS PRODUCT CARD
                    ========================== --}}
                    <div
                        class="absolute left-[4%] top-5 z-20
                               w-32 bg-white rounded-xl overflow-hidden
                               shadow-lg border border-white"
                    >

                        <div class="h-24 bg-gray-100 overflow-hidden">

                            <img
                                src="{{ asset('images/hero/earbuds.jpg') }}"
                                alt="Earbuds Pro"
                                class="w-full h-full object-cover"
                                onerror="this.style.display='none'"
                            >

                        </div>

                        <div class="px-2.5 py-2">

                            <p class="text-[10px] font-semibold text-navy">
                                Earbuds Pro
                            </p>

                            <p class="text-[10px] font-bold text-teal">
                                ₱1,299
                            </p>

                        </div>

                    </div>


                    {{-- =========================
                        MAIN SNEAKER
                    ========================== --}}
                    <div
                        class="absolute left-[26%] top-16 z-10
                               w-61.25-[245px]
                               bg-[#111111]
                               rounded-2xl
                               border-4 border-white
                               shadow-2xl
                               overflow-hidden"
                    >

                        <img
                            src="{{ asset('images/hero/sneaker.jpg') }}"
                            alt="Sneakers"
                            class="w-full h-full object-cover"
                            onerror="this.style.display='none'"
                        >

                    </div>


                    {{-- =========================
                        RATING BADGE
                    ========================== --}}
                    <div
                        class="absolute left-[18%] bottom-8 z-30
                               bg-white rounded-full
                               px-4 py-2.5
                               shadow-lg
                               text-xs
                               border border-gray-100"
                    >

                        <span class="text-orange-400 tracking-tight">
                            ★★★★★
                        </span>

                        <strong class="text-navy ml-1">
                            4.9
                        </strong>

                    </div>


                    {{-- =========================
                        FITNESS WATCH CARD
                    ========================== --}}
                    <div
                        class="absolute right-[1%] bottom-2 z-20
                               w-32 bg-white rounded-xl overflow-hidden
                               shadow-lg border border-white"
                    >

                        <div class="h-24 bg-gray-100 overflow-hidden">

                            <img
                                src="{{ asset('images/hero/watch.jpg') }}"
                                alt="Fitness Watch"
                                class="w-full h-full object-cover"
                                onerror="this.style.display='none'"
                            >

                        </div>

                        <div class="px-2.5 py-2">

                            <p class="text-[10px] font-semibold text-navy">
                                Fitness Watch
                            </p>

                            <p class="text-[10px] font-bold text-teal">
                                ₱1,799
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </section>


    {{-- =========================================================
        CATEGORIES
    ========================================================== --}}
    <section
        id="categories"
        class="py-16 bg-white"
    >

        <div class="max-w-310 mx-auto px-6">

            <div class="mb-8">

                <h2 class="font-serif text-3xl text-navy mb-1">
                    Shop by Category
                </h2>

                <p class="text-navy/60">
                    Find what you need, faster.
                </p>

            </div>


            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">

                @foreach ($categories as $category)

                    <a
                        href="#"
                        class="group bg-white border border-gray-border rounded-2xl p-6 text-center font-medium hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
                    >

                        <span
                            class="inline-flex items-center justify-center
                                   w-12 h-12 rounded-full
                                   bg-teal/10 text-teal-dark
                                   mb-3
                                   group-hover:bg-teal group-hover:text-white
                                   transition"
                        >

                            <x-dynamic-component
                                :component="'lucide-' . $category['icon']"
                                class="w-5 h-5"
                            />

                        </span>

                        <span class="block text-sm text-navy">
                            {{ $category['name'] }}
                        </span>

                    </a>

                @endforeach

            </div>

        </div>

    </section>


    {{-- =========================================================
        TRENDING PRODUCTS
    ========================================================== --}}
    <section
        id="products"
        class="py-16 bg-gray-bg"
    >

        <div class="max-w-310 mx-auto px-6">

            <div class="flex items-end justify-between mb-8">

                <div>

                    <h2 class="font-serif text-3xl text-navy mb-1">
                        Trending Now
                    </h2>

                    <p class="text-navy/60">
                        Popular picks that shoppers are loving.
                    </p>

                </div>

                <a
                    href="#"
                    class="hidden sm:flex items-center gap-1 text-sm font-semibold text-teal-dark hover:text-teal transition"
                >
                    View All
                    <span>→</span>
                </a>

            </div>


            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

                @foreach ($trendingProducts as $product)

                    <div
                        class="group bg-white rounded-2xl overflow-hidden
                               border border-gray-border
                               hover:-translate-y-1
                               hover:shadow-xl
                               transition-all duration-300"
                    >

                        {{-- Product Image --}}
                        <div class="relative aspect-square bg-gray-100 overflow-hidden">

                            <img
                                src="{{ str_starts_with($product['image'], 'http')
                                    ? $product['image']
                                    : asset('images/' . $product['image']) }}"
                                alt="{{ $product['name'] }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            >


                            {{-- Wishlist --}}
                            <button
                                class="absolute top-3 right-3
                                       w-9 h-9 rounded-full
                                       bg-white/95
                                       flex items-center justify-center
                                       text-navy
                                       hover:text-teal-dark
                                       shadow-sm
                                       transition"
                                title="Add to wishlist"
                            >

                                <x-lucide-heart class="w-4 h-4" />

                            </button>


                            {{-- Sale --}}
                            @if ($product['original_price'])

                                <span
                                    class="absolute top-3 left-3
                                           bg-teal
                                           text-white
                                           text-[10px]
                                           font-bold
                                           px-2.5 py-1
                                           rounded-full"
                                >
                                    SALE
                                </span>

                            @endif

                        </div>


                        {{-- Product Information --}}
                        <div class="p-4">

                            <span class="text-xs text-navy/50">
                                {{ $product['category'] }}
                            </span>

                            <h3 class="text-sm font-semibold text-navy mt-1 mb-2 line-clamp-1">
                                {{ $product['name'] }}
                            </h3>


                            {{-- Rating --}}
                            <div class="text-xs text-navy/60 mb-2">

                                <span class="text-orange-400">
                                    ★
                                </span>

                                {{ $product['rating'] }}

                                <span class="text-navy/40">
                                    ({{ $product['reviews'] }})
                                </span>

                            </div>


                            {{-- Price --}}
                            <div class="font-bold text-navy mb-3">

                                ₱{{ number_format($product['price']) }}

                                @if ($product['original_price'])

                                    <span
                                        class="line-through
                                               text-navy/40
                                               font-normal
                                               text-xs
                                               ml-2"
                                    >
                                        ₱{{ number_format($product['original_price']) }}
                                    </span>

                                @endif

                            </div>


                            {{-- Add to Cart --}}
                            <button
                                class="w-full
                                       bg-teal
                                       hover:bg-teal-dark
                                       text-white
                                       text-sm
                                       font-semibold
                                       py-2.5
                                       rounded-full
                                       transition"
                            >
                                Add to Cart
                            </button>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    </section>


    {{-- =========================================================
        DEALS BANNER
    ========================================================== --}}
    <section
        id="deals"
        class="py-12 bg-white"
    >

        <div class="max-w-310 mx-auto px-6">

            <div
                class="relative overflow-hidden
                       bg-navy rounded-3xl
                       px-8 py-12 md:px-14
                       flex flex-col md:flex-row
                       items-center justify-between
                       gap-8"
            >

                {{-- Decorative circles --}}
                <div
                    class="absolute -right-20 -top-20
                           w-64 h-64
                           rounded-full
                           bg-teal/10"
                ></div>

                <div
                    class="absolute right-32 -bottom-24
                           w-48 h-48
                           rounded-full
                           bg-teal/10"
                ></div>


                <div class="relative z-10">

                    <span class="text-teal text-xs font-semibold uppercase tracking-wider">
                        Limited-time offers
                    </span>

                    <h2 class="font-serif text-4xl text-white mt-2 mb-3">
                        Hop Into Great Deals.
                    </h2>

                    <p class="text-white/70 max-w-md">
                        Save more on your everyday favorites.
                        Discover exclusive deals available on ShopHop.
                    </p>

                </div>


                <a
                    href="#"
                    class="relative z-10 shrink-0
                           bg-teal
                           hover:bg-teal-dark
                           text-white
                           font-semibold
                           text-sm
                           px-7 py-3.5
                           rounded-full
                           transition"
                >
                    View Deals
                </a>

            </div>

        </div>

    </section>


    {{-- =========================================================
        WHY SHOPHOP
    ========================================================== --}}
    <section class="py-16 bg-white">

        <div class="max-w-310 mx-auto px-6">

            <div class="text-center mb-10">

                <h2 class="font-serif text-3xl text-navy mb-2">
                    Why ShopHop?
                </h2>

                <p class="text-navy/60">
                    Shopping made simple, secure, and enjoyable.
                </p>

            </div>


            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                {{-- Fast --}}
                <div class="text-center p-6">

                    <div
                        class="mx-auto w-14 h-14
                               rounded-full
                               bg-teal/10
                               text-teal-dark
                               flex items-center justify-center
                               mb-4"
                    >
                        <x-lucide-truck class="w-6 h-6" />
                    </div>

                    <h3 class="font-semibold text-navy mb-2">
                        Fast & Reliable
                    </h3>

                    <p class="text-sm text-navy/60 leading-6">
                        Get your orders delivered with ease.
                    </p>

                </div>


                {{-- Secure --}}
                <div class="text-center p-6">

                    <div
                        class="mx-auto w-14 h-14
                               rounded-full
                               bg-teal/10
                               text-teal-dark
                               flex items-center justify-center
                               mb-4"
                    >
                        <x-lucide-shield-check class="w-6 h-6" />
                    </div>

                    <h3 class="font-semibold text-navy mb-2">
                        Secure Shopping
                    </h3>

                    <p class="text-sm text-navy/60 leading-6">
                        Shop confidently with secure transactions.
                    </p>

                </div>


                {{-- Deals --}}
                <div class="text-center p-6">

                    <div
                        class="mx-auto w-14 h-14
                               rounded-full
                               bg-teal/10
                               text-teal-dark
                               flex items-center justify-center
                               mb-4"
                    >
                        <x-lucide-tag class="w-6 h-6" />
                    </div>

                    <h3 class="font-semibold text-navy mb-2">
                        Everyday Deals
                    </h3>

                    <p class="text-sm text-navy/60 leading-6">
                        Discover great prices on products you love.
                    </p>

                </div>


                {{-- Variety --}}
                <div class="text-center p-6">

                    <div
                        class="mx-auto w-14 h-14
                               rounded-full
                               bg-teal/10
                               text-teal-dark
                               flex items-center justify-center
                               mb-4"
                    >
                        <x-lucide-shopping-bag class="w-6 h-6" />
                    </div>

                    <h3 class="font-semibold text-navy mb-2">
                        Everything in One Place
                    </h3>

                    <p class="text-sm text-navy/60 leading-6">
                        From essentials to trending finds.
                    </p>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
    FINAL CTA
========================================================== --}}
<section class="bg-gray-bg py-16 px-6">

    <div class="max-w-250 mx-auto">

        <div
            class="relative overflow-hidden
                   bg-white
                   border border-[#E4E8EC]
                   rounded-3xl
                   px-8 py-12 md:px-14
                   text-center
                   shadow-sm"
        >

            {{-- Decorative teal circle --}}
            <div
                class="absolute -top-20 -right-20
                       w-52 h-52
                       rounded-full
                       bg-teal/10"
            ></div>

            {{-- Decorative small circle --}}
            <div
                class="absolute -bottom-12 -left-12
                       w-32 h-32
                       rounded-full
                       bg-teal/5"
            ></div>


            {{-- Content --}}
            <div class="relative z-10">

                <span
                    class="inline-flex items-center gap-2
                           bg-teal/10
                           text-teal-dark
                           text-xs
                           font-semibold
                           px-4 py-2
                           rounded-full
                           mb-5"
                >
                    <span>✦</span>
                    Your next favorite find is waiting
                </span>


                <h2
                    class="font-serif
                           text-4xl md:text-5xl
                           text-navy
                           mb-3"
                >
                    Ready to Hop In?
                </h2>


                <p
                    class="text-navy/60
                           max-w-md
                           mx-auto
                           mb-7
                           text-sm md:text-base
                           leading-6"
                >
                    Discover amazing products, great deals,
                    and everyday essentials — all just a hop away.
                </p>


                <a
                    href="#products"
                    class="inline-flex items-center gap-2
                           bg-teal
                           hover:bg-teal-dark
                           text-white
                           font-semibold
                           text-sm
                           px-8 py-3.5
                           rounded-full
                           shadow-md
                           hover:shadow-lg
                           transition-all"
                >
                    Start Shopping

                    <span class="text-base">
                        →
                    </span>
                </a>

            </div>

        </div>

    </div>

</section>


@endsection

