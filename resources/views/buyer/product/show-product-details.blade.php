{{-- Path: resources/views/buyer/product/show-product-details.blade.php --}}

@extends('layouts.app')

@php
    $gallery = $gallery ?? [];
    if (empty($gallery)) {
        $gallery = [$product['image'] ?? asset('images/placeholder-product.jpg')];
    }

    $variants = $variants ?? [];
    $shopVouchers = $shopVouchers ?? [];
    $ratingBreakdown = $ratingBreakdown ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    $reviews = $reviews instanceof \Illuminate\Support\Collection ? $reviews : collect($reviews ?? []);
    $relatedProducts = collect($relatedProducts ?? []);

    $productImageUrl = function ($path) {
        $path = trim((string) $path);

        if ($path === '') {
            return asset('images/products/placeholder.png');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    };

    $ratingTotal = max(1, array_sum($ratingBreakdown));
    $categorySlug = \Illuminate\Support\Str::slug($product['category']);
    $categoryUrl = \Illuminate\Support\Facades\Route::has('buyer.category.show')
        ? route('buyer.category.show', $categorySlug)
        : '#';

    $firstVariant = $variants[0] ?? null;
@endphp

@section('title', $product['name'] . ' - ShopHop')
@section('hideChrome', true)

@section('content')

@include('buyer.partials.navbar-buyer')

{{-- BREADCRUMB --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-3 sm:px-4 lg:px-5 py-2">
        <nav class="flex items-center gap-1.5 text-[10px] sm:text-[10.5px] text-navy/40 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('buyer.dashboard') }}" class="hover:text-teal-dark transition">Home</a>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0 text-navy/20" />
            <a href="{{ $categoryUrl }}" class="hover:text-teal-dark transition">{{ $product['category'] }}</a>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0 text-navy/20" />
            <span class="text-navy/75 font-medium truncate max-w-52 sm:max-w-md lg:max-w-2xl">{{ $product['name'] }}</span>
        </nav>
    </div>
</div>

{{-- PRODUCT DETAIL --}}
<section class="bg-gray-bg/80 py-4 sm:py-5 pb-20 lg:pb-6">
    <div class="max-w-310 mx-auto px-3 sm:px-4 lg:px-5">

        <div class="bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm product-reveal">
            <div class="grid lg:grid-cols-[0.9fr_1.1fr]">

                {{-- GALLERY --}}
                <div class="p-3 sm:p-4 lg:p-5 border-b lg:border-b-0 lg:border-r border-gray-border/90">
                    <div class="relative aspect-square rounded-xl overflow-hidden bg-gray-bg/70">
                        <img
                            id="mainProductImage"
                            src="{{ $productImageUrl($gallery[0]) }}"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-contain bg-white p-4 sm:p-6 transition duration-300"
                        >

                        @if (($product['discount'] ?? 0) > 0)
                            <span class="absolute top-2.5 left-2.5 bg-teal text-white text-[9px] font-bold px-2 py-1 rounded-md shadow-sm">
                                -{{ $product['discount'] }}%
                            </span>
                        @endif

                        <button
                            type="button"
                            data-wishlist-toggle
                            aria-label="Add product to wishlist"
                            class="wishlist-toggle absolute top-2.5 right-2.5 w-8 h-8 rounded-lg bg-white/95 border border-gray-border shadow-sm flex items-center justify-center text-navy/45 hover:text-teal-dark hover:border-teal/30 transition"
                        >
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    @if (count($gallery) > 1)
                        <div class="grid grid-cols-5 gap-1.5 sm:gap-2 mt-2.5">
                            @foreach ($gallery as $index => $image)
                                <button
                                    type="button"
                                    data-gallery-thumb="{{ $productImageUrl($image) }}"
                                    class="gallery-thumb aspect-square rounded-lg overflow-hidden border {{ $index === 0 ? 'border-teal ring-1 ring-teal/20' : 'border-gray-border hover:border-teal/40' }} bg-gray-bg transition"
                                >
                                    <img src="{{ $productImageUrl($image) }}" alt="Product thumbnail {{ $index + 1 }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-border/70 text-[10px]">
                        <button type="button" data-share-product class="inline-flex items-center gap-1.5 text-navy/45 hover:text-teal-dark transition">
                            <x-lucide-share-2 class="w-3.5 h-3.5" />
                            Share product
                        </button>

                        <button type="button" data-wishlist-toggle class="wishlist-toggle inline-flex items-center gap-1.5 text-navy/45 hover:text-teal-dark transition">
                            <x-lucide-heart class="w-3.5 h-3.5" />
                            Wishlist
                        </button>
                    </div>
                </div>

                {{-- PRODUCT INFO --}}
                <div class="p-4 sm:p-5 lg:p-6 lg:sticky lg:top-3 self-start">
                    <div class="flex flex-wrap items-center gap-1.5 mb-2.5">
                        <span class="bg-teal text-white text-[9px] font-bold px-2 py-1 rounded-md">Mall Pick</span>
                        <a href="{{ $categoryUrl }}" class="bg-teal-light text-teal-dark text-[9px] font-semibold px-2 py-1 rounded-md hover:bg-teal/15 transition">
                            {{ $product['category'] }}
                        </a>
                        @if ($product['in_stock'])
                            <span class="inline-flex items-center gap-1 text-[9px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                In stock
                            </span>
                        @endif
                    </div>

                    <p role="heading" aria-level="1" class="text-[21px] sm:text-[25px] lg:text-[29px] font-bold text-navy leading-[1.18] tracking-tight max-w-3xl">
                        {{ $product['name'] }}
                    </p>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 mt-3 pb-3 border-b border-gray-border/80">
                        <a href="#ratings" class="flex items-center gap-1.5 hover:opacity-80 transition">
                            <span class="text-[11px] font-bold text-teal-dark underline underline-offset-4">{{ $product['rating'] }}</span>
                            <div class="flex">
                                @for ($i = 0; $i < 5; $i++)
                                    <x-lucide-star class="w-3 h-3 fill-amber-400 text-amber-400" />
                                @endfor
                            </div>
                        </a>
                        <span class="w-px h-4 bg-gray-border"></span>
                        <a href="#ratings" class="text-[10.5px] text-navy/50 hover:text-teal-dark transition">
                            <strong class="text-navy">{{ number_format($product['reviews']) }}</strong> Ratings
                        </a>
                        <span class="w-px h-4 bg-gray-border"></span>
                        <span class="text-[10.5px] text-navy/50">
                            <strong class="text-navy">{{ number_format($product['sold']) }}</strong> Sold
                        </span>
                    </div>

                    {{-- PRICE --}}
                    <div class="mt-3 bg-[#EAF9F5] border border-teal/10 rounded-xl px-3.5 sm:px-4 py-3">
                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                            <span id="productPriceDisplay" class="text-[24px] sm:text-[27px] font-bold leading-none text-teal-dark">₱{{ number_format($product['price']) }}</span>
                            @if (! empty($product['original_price']))
                                <span class="text-[11px] text-navy/30 line-through">₱{{ number_format($product['original_price']) }}</span>
                            @endif
                            @if (($product['discount'] ?? 0) > 0)
                                <span class="text-[9px] font-bold bg-teal text-white px-1.5 py-1 rounded-md">{{ $product['discount'] }}% OFF</span>
                            @endif
                        </div>
                        <p class="text-[9.5px] text-navy/40 mt-1">Limited-time ShopHop deal · VAT inclusive</p>
                    </div>

                    {{-- SHOP VOUCHERS --}}
                    @if (count($shopVouchers) > 0)
                        <div class="mt-3 grid sm:grid-cols-[92px_1fr] gap-2 sm:gap-3 items-start">
                            <span class="text-[10px] text-navy/40 pt-1.5">Shop vouchers</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($shopVouchers as $voucher)
                                    <button
                                        type="button"
                                        title="{{ $voucher['code'] }} · {{ $voucher['min'] }}"
                                        class="group inline-flex items-center gap-1.5 border border-dashed border-teal/50 bg-teal-light/35 text-teal-dark rounded-md px-2 py-1.5 text-[9px] font-semibold hover:bg-teal-light transition"
                                    >
                                        <x-lucide-ticket class="w-3 h-3" />
                                        {{ $voucher['label'] }}
                                        <span class="text-[8px] opacity-60 group-hover:opacity-100">{{ $voucher['code'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 space-y-3.5">
                        {{-- SHIPPING --}}
                        <div class="grid sm:grid-cols-[92px_1fr] gap-2 sm:gap-3">
                            <span class="text-[10px] text-navy/40 pt-0.5">Shipping</span>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex gap-2 text-[10px] text-navy min-w-0">
                                    <x-lucide-truck class="w-3.5 h-3.5 text-teal-dark shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-semibold">Free shipping eligible</p>
                                        <p class="text-navy/40 mt-0.5">Ships from {{ $product['ships_from'] }} · Delivery in 2–5 days</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- VARIANTS (flat list from product_variants table) --}}
                        @if (count($variants) > 0)
                            <div class="grid sm:grid-cols-[92px_1fr] gap-2 sm:gap-3">
                                <span class="text-[10px] text-navy/40 pt-2">Options</span>
                                <div class="flex flex-wrap gap-1.5" data-variant-list>
                                    @foreach ($variants as $index => $variant)
                                        <button
                                            type="button"
                                            data-variant-option
                                            data-variant-id="{{ $variant['id'] }}"
                                            data-variant-label="{{ $variant['label'] }}"
                                            data-variant-price="{{ $variant['price'] }}"
                                            data-variant-stock="{{ $variant['stock'] }}"
                                            {{ ! $variant['in_stock'] ? 'disabled' : '' }}
                                            class="variant-option min-w-20 px-3 py-2 rounded-lg border text-[10px] font-medium transition
                                                   {{ $index === 0 ? 'border-teal text-teal-dark bg-teal-light/45' : 'border-gray-border text-navy/55 hover:border-teal/50 hover:text-teal-dark' }}
                                                   {{ ! $variant['in_stock'] ? 'opacity-40 cursor-not-allowed' : '' }}"
                                        >
                                            {{ $variant['label'] }}
                                            @if (! $variant['in_stock'])
                                                <span class="block text-[8px] text-red-500">Out of stock</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- QUANTITY --}}
                        <div class="grid sm:grid-cols-[92px_1fr] gap-2 sm:gap-3 items-center">
                            <span class="text-[10px] text-navy/40">Quantity</span>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <div class="flex border border-gray-border rounded-lg overflow-hidden bg-white">
                                    <button type="button" data-qty-decrease class="w-8 h-8 flex items-center justify-center hover:bg-teal-light transition" aria-label="Decrease quantity">
                                        <x-lucide-minus class="w-3 h-3" />
                                    </button>
                                    <input
                                        type="number"
                                        data-qty-input
                                        value="1"
                                        min="1"
                                        max="{{ $firstVariant['stock'] ?? $product['stock'] }}"
                                        class="w-10 h-8 text-center border-x border-gray-border text-[11px] font-semibold focus:outline-none"
                                    >
                                    <button type="button" data-qty-increase class="w-8 h-8 flex items-center justify-center hover:bg-teal-light transition" aria-label="Increase quantity">
                                        <x-lucide-plus class="w-3 h-3" />
                                    </button>
                                </div>
                                <span data-stock-label class="text-[9.5px] text-navy/35">{{ $firstVariant['stock'] ?? $product['stock'] }} pieces available</span>
                            </div>
                        </div>

                        {{-- SELECTION SUMMARY --}}
                        <div class="grid sm:grid-cols-[92px_1fr] gap-2 sm:gap-3">
                            <span class="text-[10px] text-navy/40">Selected</span>
                            <div id="selectedVariantSummary" class="text-[10px] font-medium text-navy/65 bg-gray-bg rounded-lg px-2.5 py-2">
                                {{ $firstVariant['label'] ?? 'Standard' }} · Qty 1
                            </div>
                        </div>
                    </div>

                    {{-- DESKTOP ACTIONS --}}
                    <div class="hidden lg:grid grid-cols-2 gap-2 mt-5">
                        <button
                            type="button"
                            data-add-to-cart="{{ $product['id'] }}"
                            data-selected-variant="{{ $firstVariant['id'] ?? '' }}"
                            class="h-10 rounded-lg border border-teal bg-teal-light/55 text-teal-dark font-semibold text-[11px] flex items-center justify-center gap-1.5 hover:bg-teal-light active:scale-[0.99] transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                            Add to Cart
                        </button>

                        <a
                            id="buyNowLink"
                            data-buy-now-base="{{ url('/buyer/cart') }}?buy_now={{ $product['id'] }}"
                            href="{{ url('/buyer/cart') }}?buy_now={{ $product['id'] }}&qty=1"
                            class="h-10 rounded-lg bg-teal hover:bg-teal-dark text-white font-semibold text-[11px] flex items-center justify-center gap-1.5 shadow-sm active:scale-[0.99] transition"
                        >
                            Buy Now
                            <x-lucide-arrow-right class="w-3.5 h-3.5" />
                        </a>
                    </div>

                    {{-- TRUST --}}
                    <div class="grid grid-cols-3 gap-1.5 mt-4 pt-3 border-t border-gray-border/80">
                        <div class="flex items-center gap-1.5 text-[8.5px] sm:text-[9px] text-navy/45">
                            <x-lucide-shield-check class="w-3.5 h-3.5 text-teal-dark shrink-0" />
                            Buyer Protection
                        </div>
                        <div class="flex items-center gap-1.5 text-[8.5px] sm:text-[9px] text-navy/45">
                            <x-lucide-rotate-ccw class="w-3.5 h-3.5 text-teal-dark shrink-0" />
                            Easy Returns
                        </div>
                        <div class="flex items-center gap-1.5 text-[8.5px] sm:text-[9px] text-navy/45">
                            <x-lucide-badge-check class="w-3.5 h-3.5 text-teal-dark shrink-0" />
                            Authenticity
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SELLER CARD --}}
        <section class="bg-white border border-gray-border rounded-2xl mt-3 p-4 sm:p-5 product-reveal">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-teal-light text-teal-dark flex items-center justify-center shrink-0">
                        <x-lucide-store class="w-5 h-5" />
                    </div>

                    <div class="min-w-0">
                        <p role="heading" aria-level="2" class="text-[15px] sm:text-[17px] font-bold text-navy truncate">{{ $product['seller_name'] }}</p>
                        <p class="text-[9.5px] text-navy/35 mt-0.5">Joined {{ $product['seller_joined'] }}</p>
                        <div class="flex gap-1.5 mt-2">
                            <button class="px-2.5 py-1.5 rounded-lg bg-teal text-white text-[9.5px] font-semibold hover:bg-teal-dark transition">
                                Chat Now
                            </button>
                            <button class="px-2.5 py-1.5 rounded-lg border border-gray-border text-navy/65 text-[9.5px] font-semibold hover:border-teal/40 hover:text-teal-dark transition">
                                View Shop
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 sm:gap-5 lg:gap-8 text-center lg:text-left">
                    <div>
                        <span class="text-[8.5px] text-navy/35">Ratings</span>
                        <p class="font-bold text-[11px] text-teal-dark mt-0.5">{{ $product['seller_rating'] }}</p>
                    </div>
                    <div>
                        <span class="text-[8.5px] text-navy/35">Products</span>
                        <p class="font-bold text-[11px] text-teal-dark mt-0.5">{{ $product['seller_products'] }}</p>
                    </div>
                    <div>
                        <span class="text-[8.5px] text-navy/35">Joined</span>
                        <p class="font-bold text-[11px] text-navy mt-0.5 whitespace-nowrap">{{ $product['seller_joined'] }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- DETAILS / DESCRIPTION --}}
        <section class="bg-white border border-gray-border rounded-2xl mt-3 p-4 sm:p-5 product-reveal">
            <div class="grid lg:grid-cols-[0.9fr_1.1fr] gap-5 lg:gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                            <x-lucide-list-checks class="w-3.5 h-3.5" />
                        </span>
                        <p role="heading" aria-level="2" class="text-[15px] sm:text-[16px] font-bold text-navy">Product Specifications</p>
                    </div>

                    <dl class="divide-y divide-gray-border/70 border-y border-gray-border/70">
                        <div class="grid grid-cols-[105px_1fr] gap-3 py-2.5 text-[10px]">
                            <dt class="text-navy/35">Category</dt>
                            <dd><a href="{{ $categoryUrl }}" class="text-teal-dark font-medium hover:underline">{{ $product['category'] }}</a></dd>
                        </div>
                        <div class="grid grid-cols-[105px_1fr] gap-3 py-2.5 text-[10px]">
                            <dt class="text-navy/35">Condition</dt>
                            <dd class="text-navy/70 font-medium">{{ $product['condition'] }}</dd>
                        </div>
                        <div class="grid grid-cols-[105px_1fr] gap-3 py-2.5 text-[10px]">
                            <dt class="text-navy/35">Stock</dt>
                            <dd class="text-navy/70 font-medium">{{ $product['stock'] }} units</dd>
                        </div>
                        <div class="grid grid-cols-[105px_1fr] gap-3 py-2.5 text-[10px]">
                            <dt class="text-navy/35">Ships From</dt>
                            <dd class="text-navy/70 font-medium">{{ $product['ships_from'] }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                            <x-lucide-align-left class="w-3.5 h-3.5" />
                        </span>
                        <p role="heading" aria-level="2" class="text-[15px] sm:text-[16px] font-bold text-navy">Product Description</p>
                    </div>

                    <p class="text-[10.5px] sm:text-[11px] text-navy/55 leading-6 whitespace-pre-line">{{ $product['description'] }}</p>
                </div>
            </div>
        </section>

        {{-- RATINGS & REVIEWS --}}
        <section class="bg-white border border-gray-border rounded-2xl mt-3 p-4 sm:p-5 product-reveal" id="ratings">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <p class="text-[8.5px] uppercase tracking-[0.12em] font-bold text-teal-dark">Verified buyer feedback</p>
                    <p role="heading" aria-level="2" class="text-[15px] sm:text-[17px] font-bold text-navy mt-0.5">Product Ratings & Reviews</p>
                </div>
                <span class="text-[9.5px] text-navy/35">{{ number_format($product['reviews']) }} ratings</span>
            </div>

            @if ($product['reviews'] > 0)
                <div class="mt-3 rounded-xl bg-[#EAF9F5] border border-teal/10 p-3.5 sm:p-4">
                    <div class="grid md:grid-cols-[135px_210px_1fr] gap-4 items-center">
                        <div class="text-center md:text-left">
                            <div class="flex items-end justify-center md:justify-start gap-1">
                                <span class="text-[30px] font-bold leading-none text-teal-dark">{{ $product['rating'] }}</span>
                                <span class="text-[10px] text-teal-dark/70 pb-0.5">/ 5</span>
                            </div>
                            <div class="flex justify-center md:justify-start mt-1.5">
                                @for ($i=0; $i<5; $i++)
                                    <x-lucide-star class="w-3 h-3 fill-amber-400 text-amber-400" />
                                @endfor
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            @foreach ($ratingBreakdown as $stars => $count)
                                @php $percent = ($count / $ratingTotal) * 100; @endphp
                                <div class="grid grid-cols-[30px_1fr_28px] items-center gap-2 text-[8.5px]">
                                    <span class="flex items-center gap-0.5 text-navy/45">{{ $stars }}<x-lucide-star class="w-2.5 h-2.5 fill-amber-400 text-amber-400" /></span>
                                    <div class="h-1.5 rounded-full bg-white overflow-hidden">
                                        <div class="h-full rounded-full bg-teal" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="text-navy/35 text-right">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" data-review-filter="all" class="review-filter px-2.5 py-1.5 rounded-lg border border-teal bg-white text-teal-dark text-[9px] font-semibold">All ({{ $product['reviews'] }})</button>
                            @foreach ($ratingBreakdown as $stars => $count)
                                <button type="button" data-review-filter="{{ $stars }}" class="review-filter px-2.5 py-1.5 rounded-lg border border-gray-border bg-white text-navy/55 text-[9px] hover:border-teal/40 hover:text-teal-dark transition">{{ $stars }} Star</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-3">
                    <p class="text-[9px] text-navy/35">
                        Showing <span id="reviewVisibleCount" class="font-semibold text-navy">{{ $reviews->count() }}</span> reviews
                    </p>
                    <button type="button" id="clearReviewFilter" class="hidden text-[9px] font-semibold text-teal-dark hover:text-navy transition">Clear filter</button>
                </div>

                <div class="divide-y divide-gray-border/80 mt-1">
                    @foreach ($reviews as $review)
                        <article class="review-item py-4" data-review-rating="{{ $review['rating'] }}" data-review-media="{{ $review['image'] ? '1' : '0' }}">
                            <div class="flex gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-gray-bg flex items-center justify-center text-[9px] font-bold text-navy/45 shrink-0">
                                    {{ mb_substr($review['name'], 0, 1) }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <p class="text-[10.5px] font-semibold text-navy">{{ $review['name'] }}</p>
                                        <span class="inline-flex items-center gap-1 text-[8px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">
                                            <x-lucide-badge-check class="w-2.5 h-2.5" /> Verified Purchase
                                        </span>
                                    </div>

                                    <div class="flex mt-1">
                                        @for ($i=1; $i<=5; $i++)
                                            <x-lucide-star class="w-3 h-3 {{ $i <= $review['rating'] ? 'fill-amber-400 text-amber-400' : 'text-gray-border' }}" />
                                        @endfor
                                    </div>

                                    <p class="text-[8.5px] text-navy/30 mt-1">{{ $review['date'] }} · Variation: {{ $review['variant'] }}</p>
                                    <p class="text-[10px] sm:text-[10.5px] text-navy/60 leading-5 mt-2">{{ $review['comment'] }}</p>

                                    @if ($review['image'])
                                        <button class="mt-2.5 w-16 h-16 rounded-lg overflow-hidden border border-gray-border hover:border-teal/40 transition">
                                            <img src="{{ $review['image'] }}" alt="Review photo" class="w-full h-full object-cover">
                                        </button>
                                    @endif

                                    <div class="flex items-center gap-4 mt-3 text-[9px] text-navy/35">
                                        <button class="flex items-center gap-1 hover:text-teal-dark transition">
                                            <x-lucide-thumbs-up class="w-3 h-3" />
                                            Helpful ({{ $review['helpful'] }})
                                        </button>
                                        <button class="hover:text-teal-dark transition">Report</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div id="reviewEmptyState" class="hidden py-8 text-center">
                    <x-lucide-message-square-off class="w-6 h-6 text-navy/20 mx-auto" />
                    <p class="text-[10px] font-semibold text-navy/50 mt-2">No reviews match this filter.</p>
                </div>
            @else
                <div class="py-10 text-center">
                    <x-lucide-message-square-off class="w-7 h-7 text-navy/20 mx-auto" />
                    <p class="text-[11px] font-semibold text-navy/50 mt-3">No reviews yet</p>
                    <p class="text-[9.5px] text-navy/35 mt-1">Be the first to leave a review after purchasing this product.</p>
                </div>
            @endif
        </section>

        {{-- RECOMMENDATIONS --}}
        @if ($relatedProducts->count() > 0)
            <section class="mt-5 mb-3 product-reveal">
                <div class="flex items-end justify-between gap-3 mb-3">
                    <div>
                        <p class="text-[8.5px] uppercase tracking-[0.12em] font-bold text-teal-dark">Recommended for you</p>
                        <p role="heading" aria-level="2" class="text-[15px] sm:text-[17px] font-bold text-navy mt-0.5">You May Also Like</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3 product-stagger">
                    @foreach ($relatedProducts as $related)
                        <article class="group bg-white border border-gray-border rounded-xl overflow-hidden hover:border-teal/30 hover:-translate-y-0.5 hover:shadow-md transition-all duration-300">
                            <a href="{{ route('buyer.product.show', $related['id']) }}" class="block aspect-4/3 bg-gray-bg overflow-hidden">
                                <img src="{{ $productImageUrl($related['image']) }}" alt="{{ $related['name'] }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500">
                            </a>

                            <div class="p-2.5">
                                <p class="text-[8px] text-navy/35 truncate">{{ $related['category'] }}</p>
                                <p role="heading" aria-level="3" class="text-[10.5px] font-semibold text-navy leading-4 line-clamp-2 min-h-8 mt-0.5">{{ $related['name'] }}</p>

                                <div class="flex flex-wrap items-baseline gap-1.5 mt-1.5">
                                    <span class="text-[12px] font-bold text-teal-dark">₱{{ number_format($related['price']) }}</span>
                                    @if (! empty($related['original_price']))
                                        <span class="text-[8.5px] text-navy/25 line-through">₱{{ number_format($related['original_price']) }}</span>
                                    @endif
                                </div>

                                <div class="flex justify-between items-center mt-1.5 text-[8.5px] text-navy/35">
                                    <span class="flex items-center gap-0.5"><x-lucide-star class="w-2.5 h-2.5 fill-amber-400 text-amber-400"/>{{ $related['rating'] }}</span>
                                    <span>{{ $related['sold'] ?? 0 }} sold</span>
                                </div>

                                <button
                                    type="button"
                                    data-add-related-to-cart
                                    data-related-id="{{ $related['id'] }}"
                                    data-related-name="{{ $related['name'] }}"
                                    data-related-image="{{ $productImageUrl($related['image']) }}"
                                    data-related-price="{{ $related['price'] }}"
                                    data-related-original-price="{{ $related['original_price'] ?? '' }}"
                                    data-related-stock="{{ $related['stock'] ?? '' }}"
                                    class="w-full mt-2 py-1.5 rounded-lg bg-teal hover:bg-teal-dark text-white text-[9px] font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    Add to Cart
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</section>

{{-- MOBILE PURCHASE BAR --}}
<div class="lg:hidden fixed inset-x-0 bottom-0 z-50 bg-white/95 backdrop-blur border-t border-gray-border px-3 py-2 shadow-[0_-8px_24px_rgba(15,27,61,.08)]">
    <div class="max-w-310 mx-auto grid grid-cols-[auto_1fr_1fr] gap-2">
        <button type="button" data-wishlist-toggle class="wishlist-toggle w-10 h-10 rounded-lg border border-gray-border flex items-center justify-center text-navy/45 hover:text-teal-dark transition" aria-label="Wishlist">
            <x-lucide-heart class="w-4 h-4" />
        </button>
        <button type="button" data-add-to-cart="{{ $product['id'] }}" data-selected-variant="{{ $firstVariant['id'] ?? '' }}" class="h-10 rounded-lg border border-teal bg-teal-light text-teal-dark text-[10px] font-semibold flex items-center justify-center gap-1.5 disabled:opacity-60 disabled:cursor-not-allowed">
            <x-lucide-shopping-cart class="w-3.5 h-3.5" /> Add to Cart
        </button>
        <a id="mobileBuyNowLink" data-buy-now-base="{{ url('/buyer/cart') }}?buy_now={{ $product['id'] }}" href="{{ url('/buyer/cart') }}?buy_now={{ $product['id'] }}&qty=1" class="h-10 rounded-lg bg-teal text-white text-[10px] font-semibold flex items-center justify-center">Buy Now</a>
    </div>
</div>

{{-- TOAST --}}
<div id="productToast" class="fixed left-1/2 bottom-16 lg:bottom-6 -translate-x-1/2 z-60 pointer-events-none opacity-0 translate-y-2 transition-all duration-200">
    <div class="bg-navy text-white text-[9.5px] font-medium px-3 py-2 rounded-lg shadow-lg flex items-center gap-1.5 whitespace-nowrap">
        <x-lucide-check-circle-2 class="w-3.5 h-3.5 text-teal" />
        <span data-toast-message>Added to cart</span>
    </div>
</div>

<style>
    .product-reveal { opacity: 0; transform: translateY(12px); transition: opacity .55s ease var(--product-delay, 0ms), transform .55s cubic-bezier(.22, 1, .36, 1) var(--product-delay, 0ms); }
    .product-reveal.is-visible { opacity: 1; transform: translateY(0); }
    .product-stagger > * { opacity: 0; transform: translateY(10px); transition: opacity .45s ease, transform .45s cubic-bezier(.22, 1, .36, 1); }
    .product-stagger.is-visible > * { opacity: 1; transform: translateY(0); }
    .product-stagger.is-visible > *:nth-child(2) { transition-delay: 35ms; }
    .product-stagger.is-visible > *:nth-child(3) { transition-delay: 70ms; }
    .product-stagger.is-visible > *:nth-child(4) { transition-delay: 105ms; }
    .product-stagger.is-visible > *:nth-child(5) { transition-delay: 140ms; }
    .wishlist-toggle.is-wished { color: #18A98E; border-color: rgba(33, 195, 166, .35); background: #D4F5EE; }
    .wishlist-toggle.is-wished svg { fill: currentColor; }
    #productToast.is-visible { opacity: 1; transform: translate(-50%, 0); }
    @media (prefers-reduced-motion: reduce) {
        .product-reveal, .product-stagger > * { opacity: 1 !important; transform: none !important; transition: none !important; }
    }
</style>

@include('partials.footer')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput = document.querySelector('[data-qty-input]');
    const decreaseButton = document.querySelector('[data-qty-decrease]');
    const increaseButton = document.querySelector('[data-qty-increase]');
    const selectedSummary = document.getElementById('selectedVariantSummary');
    const stockLabel = document.querySelector('[data-stock-label]');
    const priceDisplay = document.getElementById('productPriceDisplay');
    const basePrice = {{ (float) $product['price'] }};
    const baseStock = {{ (int) $product['stock'] }};
    const addToCartButtons = document.querySelectorAll('[data-add-to-cart]');
    const buyNowLinks = [
        document.getElementById('buyNowLink'),
        document.getElementById('mobileBuyNowLink')
    ].filter(Boolean);

    // NOTE: assumes <meta name="csrf-token" content="{{ csrf_token() }}">
    // is present in the shared layout head.
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const CART_ADD_URL = @json(route('buyer.cart.add'));

    // Base info for THIS product, used to build the cart payload when
    // clicking either of the main "Add to Cart" buttons.
    const productBase = {
        id: @json($product['id']),
        name: @json($product['name']),
        image: @json($productImageUrl($gallery[0])),
        price: {{ (float) $product['price'] }},
        originalPrice: {{ ! empty($product['original_price']) ? (float) $product['original_price'] : 'null' }},
        stock: {{ (int) $product['stock'] }},
        shopId: @json($product['shop_id'] ?? $product['seller_id'] ?? null),
        shopName: @json($product['seller_name'] ?? null),
        shopResponseRate: @json($product['shop_response_rate'] ?? null),
        shopPreferred: {{ ! empty($product['shop_preferred']) ? 'true' : 'false' }},
    };

    let selectedVariant = null;
    const firstVariantBtn = document.querySelector('[data-variant-option]');
    if (firstVariantBtn) {
        selectedVariant = {
            id: firstVariantBtn.dataset.variantId,
            label: firstVariantBtn.dataset.variantLabel,
            price: parseFloat(firstVariantBtn.dataset.variantPrice),
            stock: parseInt(firstVariantBtn.dataset.variantStock, 10),
        };
    }

    function currentMaxStock() {
        return selectedVariant ? selectedVariant.stock : baseStock;
    }

    function clampQuantity() {
        if (!qtyInput) return 1;
        const min = 1;
        const max = Math.max(1, currentMaxStock());
        let value = parseInt(qtyInput.value, 10) || min;
        value = Math.max(min, Math.min(max, value));
        qtyInput.value = value;
        return value;
    }

    function updateSelectionSummary() {
        const qty = clampQuantity();
        const label = selectedVariant ? selectedVariant.label : 'Standard';
        if (selectedSummary) selectedSummary.textContent = label + ' · Qty ' + qty;
        if (stockLabel) stockLabel.textContent = currentMaxStock() + ' pieces available';
        if (priceDisplay) {
            const price = selectedVariant ? selectedVariant.price : basePrice;
            priceDisplay.textContent = '₱' + price.toLocaleString();
        }

        buyNowLinks.forEach(function (link) {
            let href = link.dataset.buyNowBase + '&qty=' + qty;
            if (selectedVariant) href += '&variant_id=' + selectedVariant.id;
            link.href = href;
        });

        addToCartButtons.forEach(function (btn) {
            btn.dataset.selectedVariant = selectedVariant ? selectedVariant.id : '';
        });
    }

    if (qtyInput) qtyInput.setAttribute('max', currentMaxStock());
    if (decreaseButton && qtyInput) decreaseButton.addEventListener('click', function () { qtyInput.value = clampQuantity() - 1; updateSelectionSummary(); });
    if (increaseButton && qtyInput) increaseButton.addEventListener('click', function () { qtyInput.value = clampQuantity() + 1; updateSelectionSummary(); });
    if (qtyInput) { qtyInput.addEventListener('input', updateSelectionSummary); qtyInput.addEventListener('change', updateSelectionSummary); }

    document.querySelectorAll('[data-variant-option]').forEach(function (option) {
        option.addEventListener('click', function () {
            if (option.disabled) return;

            document.querySelectorAll('[data-variant-option]').forEach(function (item) {
                item.classList.remove('border-teal', 'text-teal-dark', 'bg-teal-light/45');
                item.classList.add('border-gray-border', 'text-navy/55');
            });
            option.classList.remove('border-gray-border', 'text-navy/55');
            option.classList.add('border-teal', 'text-teal-dark', 'bg-teal-light/45');

            selectedVariant = {
                id: option.dataset.variantId,
                label: option.dataset.variantLabel,
                price: parseFloat(option.dataset.variantPrice),
                stock: parseInt(option.dataset.variantStock, 10),
            };

            if (qtyInput) qtyInput.value = 1;
            if (qtyInput) qtyInput.setAttribute('max', currentMaxStock());
            updateSelectionSummary();
        });
    });

    const productMotionReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const productRevealTargets = document.querySelectorAll('.product-reveal, .product-stagger');

    if (productMotionReduced || !('IntersectionObserver' in window)) {
        productRevealTargets.forEach(item => item.classList.add('is-visible'));
    } else {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -20px 0px' });
        productRevealTargets.forEach(item => observer.observe(item));
    }

    const mainProductImage = document.getElementById('mainProductImage');
    const galleryThumbs = document.querySelectorAll('[data-gallery-thumb]');
    galleryThumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            if (mainProductImage) mainProductImage.src = thumb.dataset.galleryThumb;
            galleryThumbs.forEach(function (item) { item.classList.remove('border-teal', 'ring-1', 'ring-teal/20'); item.classList.add('border-gray-border'); });
            thumb.classList.remove('border-gray-border');
            thumb.classList.add('border-teal', 'ring-1', 'ring-teal/20');
        });
    });

    const toast = document.getElementById('productToast');
    const toastMessage = toast ? toast.querySelector('[data-toast-message]') : null;
    let toastTimer = null;
    function showToast(message) {
        if (!toast) return;
        if (toastMessage) toastMessage.textContent = message;
        toast.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toast.classList.remove('is-visible'); }, 1800);
    }

    // Refresh the cart-count badge in the navbar, if the navbar partial
    // exposes one via [data-cart-count].
    function refreshCartCountBadge(count) {
        document.querySelectorAll('[data-cart-count]').forEach(function (badge) {
            badge.textContent = count;
        });
    }

    async function postAddToCart(payload, triggerButtons) {
        triggerButtons.forEach(function (btn) { btn.disabled = true; });

        try {
            const response = await fetch(CART_ADD_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error('Add to cart failed: ' + response.status);
            }

            const data = await response.json();

            if (data && typeof data.cart_count !== 'undefined') {
                refreshCartCountBadge(data.cart_count);
            }

            showToast('Added ' + payload.qty + ' item(s) to cart');
        } catch (error) {
            console.error(error);
            showToast('Could not add to cart. Please try again.');
        } finally {
            triggerButtons.forEach(function (btn) { btn.disabled = false; });
        }
    }

    // Main product "Add to Cart" buttons (desktop + mobile).
    addToCartButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const qty = clampQuantity();

            const payload = {
                product_id: productBase.id,
                variant_id: selectedVariant ? selectedVariant.id : null,
                variant_label: selectedVariant ? selectedVariant.label : 'Standard',
                name: productBase.name,
                image: productBase.image,
                price: selectedVariant ? selectedVariant.price : productBase.price,
                original_price: productBase.originalPrice,
                stock: selectedVariant ? selectedVariant.stock : productBase.stock,
                qty: qty,
                shop_id: productBase.shopId,
                shop_name: productBase.shopName,
                shop_response_rate: productBase.shopResponseRate,
                shop_preferred: productBase.shopPreferred,
            };

            postAddToCart(payload, Array.from(addToCartButtons));
        });
    });

    // Mini "Add to Cart" buttons on the "You May Also Like" cards.
    document.querySelectorAll('[data-add-related-to-cart]').forEach(function (button) {
        button.addEventListener('click', function () {
            const originalPrice = button.dataset.relatedOriginalPrice;
            const stock = button.dataset.relatedStock;

            const payload = {
                product_id: button.dataset.relatedId,
                variant_id: null,
                variant_label: 'Standard',
                name: button.dataset.relatedName,
                image: button.dataset.relatedImage,
                price: parseFloat(button.dataset.relatedPrice),
                original_price: originalPrice ? parseFloat(originalPrice) : null,
                stock: stock ? parseInt(stock, 10) : null,
                qty: 1,
            };

            postAddToCart(payload, [button]);
        });
    });

    let wished = false;
    const wishlistButtons = document.querySelectorAll('[data-wishlist-toggle]');
    wishlistButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            wished = !wished;
            wishlistButtons.forEach(btn => btn.classList.toggle('is-wished', wished));
            showToast(wished ? 'Added to wishlist' : 'Removed from wishlist');
        });
    });

    document.querySelectorAll('[data-share-product]').forEach(function (button) {
        button.addEventListener('click', async function () {
            try {
                if (navigator.share) { await navigator.share({ title: document.title, url: window.location.href }); }
                else if (navigator.clipboard) { await navigator.clipboard.writeText(window.location.href); showToast('Product link copied'); }
            } catch (error) {}
        });
    });

    const reviewFilterButtons = document.querySelectorAll('[data-review-filter]');
    const reviewItems = document.querySelectorAll('.review-item');
    const reviewVisibleCount = document.getElementById('reviewVisibleCount');
    const reviewEmptyState = document.getElementById('reviewEmptyState');
    const clearReviewFilter = document.getElementById('clearReviewFilter');

    function applyReviewFilter(filter) {
        let visibleCount = 0;
        reviewItems.forEach(function (item) {
            const rating = item.dataset.reviewRating;
            const shouldShow = filter === 'all' || filter === rating;
            item.classList.toggle('hidden', !shouldShow);
            if (shouldShow) visibleCount++;
        });
        reviewFilterButtons.forEach(function (button) {
            const active = button.dataset.reviewFilter === filter;
            button.classList.toggle('border-teal', active);
            button.classList.toggle('text-teal-dark', active);
            button.classList.toggle('bg-teal-light/40', active);
            button.classList.toggle('border-gray-border', !active);
            button.classList.toggle('text-navy/55', !active);
            button.classList.toggle('bg-white', !active);
        });
        if (reviewVisibleCount) reviewVisibleCount.textContent = visibleCount;
        if (reviewEmptyState) reviewEmptyState.classList.toggle('hidden', visibleCount !== 0);
        if (clearReviewFilter) clearReviewFilter.classList.toggle('hidden', filter === 'all');
    }

    reviewFilterButtons.forEach(function (button) {
        button.addEventListener('click', function () { applyReviewFilter(button.dataset.reviewFilter); });
    });
    if (clearReviewFilter) clearReviewFilter.addEventListener('click', function () { applyReviewFilter('all'); });

    updateSelectionSummary();
    applyReviewFilter('all');
});
</script>
@endpush