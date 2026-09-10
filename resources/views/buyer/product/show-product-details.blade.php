{{-- Path: resources/views/buyer/product/show-product-details.blade.php --}}

@extends('layouts.app')

{{-- TEMPORARY SHOPEE-INSPIRED HARDCODED PREVIEW DATA --}}
@php
    $product = [
        'id' => 1,
        'name' => 'Wireless Earbuds Pro with ENC Noise Reduction & Charging Case',
        'category' => 'Electronics and Gadgets',
        'image' => 'images/hero/earbuds.jpg',
        'price' => 1299,
        'original_price' => 1699,
        'discount' => 24,
        'rating' => 4.9,
        'reviews' => 328,
        'sold' => 1200,
        'stock' => 42,
        'in_stock' => true,
        'seller_name' => 'ShopHop Tech Store',
        'seller_rating' => 4.8,
        'seller_products' => 126,
        'seller_response' => '96%',
        'seller_joined' => '2 years ago',
        'description' => "Experience clear, immersive audio for music, calls, and everyday listening.\n\n• ENC noise reduction for clearer calls\n• Bluetooth 5.3 stable connection\n• Touch controls\n• Compact charging case\n• Up to 24 hours total battery life",
        'updated_at' => now()->timestamp,
    ];

    $gallery = [
        'images/hero/earbuds.jpg',
        'images/hero/earbuds.jpg',
        'images/hero/earbuds.jpg',
        'images/hero/earbuds.jpg',
        'images/hero/earbuds.jpg',
    ];

    $variantGroups = [
        [
            'name' => 'Color',
            'options' => ['Black', 'White', 'Mint Green'],
        ],
        [
            'name' => 'Bundle',
            'options' => ['Earbuds Only', 'With Silicone Case', 'With Charger'],
        ],
    ];

    $reviews = collect([
        [
            'name' => 'M***a',
            'rating' => 5,
            'variant' => 'Black, Earbuds Only',
            'date' => '2026-09-02',
            'comment' => 'Ang ganda ng sound quality for the price. Malinaw din calls at mabilis mag-connect. Sulit!',
            'image' => 'images/hero/earbuds.jpg',
            'helpful' => 24,
        ],
        [
            'name' => 'J***n',
            'rating' => 5,
            'variant' => 'White, With Silicone Case',
            'date' => '2026-08-28',
            'comment' => 'Maayos packaging at mabilis dumating. Comfortable isuot kahit matagal.',
            'image' => 'images/hero/earbuds.jpg',
            'helpful' => 11,
        ],
        [
            'name' => 'A***e',
            'rating' => 4,
            'variant' => 'Mint Green, Earbuds Only',
            'date' => '2026-08-21',
            'comment' => 'Good battery life and okay ang bass. Nice din yung color in person.',
            'image' => null,
            'helpful' => 7,
        ],
    ]);

    $ratingBreakdown = [
        5 => 280,
        4 => 32,
        3 => 10,
        2 => 4,
        1 => 2,
    ];

    $relatedProducts = collect([
        ['id'=>2,'name'=>'Wireless Earbuds Lite','category'=>'Electronics and Gadgets','image'=>'images/hero/earbuds.jpg','price'=>899,'original_price'=>1099,'rating'=>4.8,'sold'=>850,'updated_at'=>now()->timestamp],
        ['id'=>3,'name'=>'Everyday Bluetooth Buds','category'=>'Electronics and Gadgets','image'=>'images/hero/earbuds.jpg','price'=>749,'original_price'=>null,'rating'=>4.7,'sold'=>540,'updated_at'=>now()->timestamp],
        ['id'=>4,'name'=>'AirBeat Pro Earbuds','category'=>'Electronics and Gadgets','image'=>'images/hero/earbuds.jpg','price'=>1499,'original_price'=>1899,'rating'=>4.9,'sold'=>980,'updated_at'=>now()->timestamp],
        ['id'=>5,'name'=>'Mini Wireless Earphones','category'=>'Electronics and Gadgets','image'=>'images/hero/earbuds.jpg','price'=>699,'original_price'=>null,'rating'=>4.6,'sold'=>420,'updated_at'=>now()->timestamp],
        ['id'=>6,'name'=>'SoundHop TWS Earbuds','category'=>'Electronics and Gadgets','image'=>'images/hero/earbuds.jpg','price'=>1199,'original_price'=>1399,'rating'=>4.8,'sold'=>760,'updated_at'=>now()->timestamp],
        ['id'=>7,'name'=>'Pocket ANC Earbuds','category'=>'Electronics and Gadgets','image'=>'images/hero/earbuds.jpg','price'=>1599,'original_price'=>1999,'rating'=>4.9,'sold'=>610,'updated_at'=>now()->timestamp],
    ]);
@endphp

@section('title', $product['name'] . ' - ShopHop')

@section('hideChrome', true)


@section('content')




@php
    /**
     * Converts product image values into browser-ready URLs.
     * Supports:
     * - products/file.png
     * - storage/products/file.png
     * - /storage/products/file.png
     * - images/products/file.jpg
     * - full http/https URLs
     */
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
@endphp


{{-- =========================================================
    BUYER NAVBAR
========================================================= --}}
@include('buyer.partials.navbar-buyer')


{{-- =========================================================
    BREADCRUMB
========================================================= --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-1.5 text-xs text-navy/45 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('buyer.dashboard') }}" class="hover:text-teal-dark transition">Home</a>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0" />
            <span class="text-navy/40">{{ $product['category'] }}</span>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0" />
            <span class="text-navy font-medium truncate max-w-50 sm:max-w-none">{{ $product['name'] }}</span>
        </nav>
    </div>
</div>


{{-- =========================================================
    PRODUCT DETAIL — MARKETPLACE / SHOPEE-INSPIRED STRUCTURE
========================================================= --}}
<section class="bg-gray-bg/70 py-5 sm:py-7">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white border border-gray-border rounded-2xl sm:rounded-3xl overflow-hidden shadow-sm product-reveal">
            <div class="grid lg:grid-cols-[0.92fr_1.08fr]">

                {{-- GALLERY --}}
                <div class="p-4 sm:p-5 lg:p-6 border-b lg:border-b-0 lg:border-r border-gray-border">
                    <div class="relative aspect-square rounded-2xl overflow-hidden bg-gray-bg">
                        <img id="mainProductImage"
                             src="{{ asset($gallery[0]) }}"
                             alt="{{ $product['name'] }}"
                             class="w-full h-full object-contain bg-white p-5 sm:p-7 transition duration-300">
                        <span class="absolute top-3 left-3 bg-teal text-white text-[10px] font-bold px-2.5 py-1.5 rounded-full">
                            -{{ $product['discount'] }}%
                        </span>
                        <button type="button"
                                class="absolute top-3 right-3 w-10 h-10 rounded-full bg-white border border-gray-border shadow-sm flex items-center justify-center text-navy/55 hover:text-teal-dark transition">
                            <x-lucide-heart class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="grid grid-cols-5 gap-2 mt-3">
                        @foreach ($gallery as $index => $image)
                            <button type="button"
                                    data-gallery-thumb="{{ asset($image) }}"
                                    class="gallery-thumb aspect-square rounded-xl overflow-hidden border-2 {{ $index === 0 ? 'border-teal' : 'border-transparent hover:border-teal/40' }} bg-gray-bg transition">
                                <img src="{{ asset($image) }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between mt-4 text-xs">
                        <div class="flex items-center gap-2 text-navy/55">
                            <span>Share:</span>
                            <button class="w-8 h-8 rounded-full bg-gray-bg hover:bg-teal-light hover:text-teal-dark flex items-center justify-center transition">
                                <x-lucide-share-2 class="w-3.5 h-3.5" />
                            </button>
                        </div>
                        <button class="flex items-center gap-1.5 text-navy/55 hover:text-teal-dark transition">
                            <x-lucide-heart class="w-4 h-4" />
                            2.1k
                        </button>
                    </div>
                </div>

                {{-- PRODUCT INFO --}}
                <div class="p-5 sm:p-6 lg:p-7">
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="bg-teal text-white text-[10px] font-bold px-2 py-1 rounded-md">Mall Pick</span>
                        <span class="bg-teal-light text-teal-dark text-[10px] font-semibold px-2 py-1 rounded-md">{{ $product['category'] }}</span>
                    </div>

                    <h1 class="text-lg sm:text-xl lg:text-2xl font-semibold text-navy leading-snug">
                        {{ $product['name'] }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-4 pb-4 border-b border-gray-border">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs sm:text-sm font-semibold text-teal-dark underline underline-offset-4">{{ $product['rating'] }}</span>
                            <div class="flex">
                                @for ($i = 0; $i < 5; $i++)
                                    <x-lucide-star class="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                                @endfor
                            </div>
                        </div>
                        <span class="w-px h-5 bg-gray-border"></span>
                        <button class="text-xs sm:text-sm text-navy/65 hover:text-teal-dark">
                            <span class="font-semibold text-navy">{{ number_format($product['reviews']) }}</span> Ratings
                        </button>
                        <span class="w-px h-5 bg-gray-border"></span>
                        <span class="text-xs sm:text-sm text-navy/65">
                            <span class="font-semibold text-navy">{{ number_format($product['sold']) }}</span> Sold
                        </span>
                    </div>

                    <div class="mt-4 bg-[#EAF9F5] rounded-2xl px-4 sm:px-5 py-4">
                        <div class="flex flex-wrap items-end gap-3">
                            <span class="text-2xl sm:text-3xl font-bold text-teal-dark">₱{{ number_format($product['price']) }}</span>
                            <span class="text-sm text-navy/35 line-through pb-1">₱{{ number_format($product['original_price']) }}</span>
                            <span class="text-[10px] font-bold bg-teal text-white px-2 py-1 rounded-md mb-1">{{ $product['discount'] }}% OFF</span>
                        </div>
                        <p class="text-[11px] text-navy/45 mt-1">Limited-time ShopHop deal</p>
                    </div>

                    <div class="mt-5 space-y-5">
                        <div class="grid sm:grid-cols-[110px_1fr] gap-2 sm:gap-4">
                            <span class="text-xs text-navy/45 pt-1">Shipping</span>
                            <div class="flex gap-2 text-xs text-navy">
                                <x-lucide-truck class="w-4 h-4 text-teal-dark shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-medium">Free shipping eligible</p>
                                    <p class="text-navy/45 mt-0.5">Delivery in 2–5 days</p>
                                </div>
                            </div>
                        </div>

                        @foreach ($variantGroups as $groupIndex => $group)
                        <div class="grid sm:grid-cols-[110px_1fr] gap-2 sm:gap-4">
                            <span class="text-xs text-navy/45 pt-2">{{ $group['name'] }}</span>
                            <div class="flex flex-wrap gap-2" data-variant-group="{{ $groupIndex }}">
                                @foreach ($group['options'] as $optionIndex => $option)
                                    <button type="button"
                                            data-variant-option
                                            class="variant-option min-w-24 px-3.5 py-2.5 rounded-xl border text-xs font-medium transition
                                                   {{ $optionIndex === 0 ? 'border-teal text-teal-dark bg-teal-light/50' : 'border-gray-border text-navy/65 hover:border-teal/50 hover:text-teal-dark' }}">
                                        {{ $option }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div class="grid sm:grid-cols-[110px_1fr] gap-2 sm:gap-4 items-center">
                            <span class="text-xs text-navy/45">Quantity</span>
                            <div class="flex items-center gap-3">
                                <div class="flex border border-gray-border rounded-xl overflow-hidden">
                                    <button type="button" data-qty-decrease class="w-10 h-10 flex items-center justify-center hover:bg-teal-light transition">
                                        <x-lucide-minus class="w-3.5 h-3.5" />
                                    </button>
                                    <input type="number" data-qty-input value="1" min="1" max="{{ $product['stock'] }}"
                                           class="w-12 h-10 text-center border-x border-gray-border text-sm font-semibold focus:outline-none">
                                    <button type="button" data-qty-increase class="w-10 h-10 flex items-center justify-center hover:bg-teal-light transition">
                                        <x-lucide-plus class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <span class="text-xs text-navy/40">{{ $product['stock'] }} pieces available</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3 mt-6">
                        <button class="h-12 rounded-xl border border-teal bg-teal-light/60 text-teal-dark font-semibold text-sm flex items-center justify-center gap-2 hover:bg-teal-light transition">
                            <x-lucide-shopping-cart class="w-4 h-4" />
                            Add to Cart
                        </button>
                        <button class="h-12 rounded-xl bg-teal hover:bg-teal-dark text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm transition">
                            Buy Now
                            <x-lucide-arrow-right class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-2 mt-5 pt-4 border-t border-gray-border">
                        <div class="flex items-center gap-2 text-[10px] sm:text-[11px] text-navy/55">
                            <x-lucide-shield-check class="w-4 h-4 text-teal-dark shrink-0" />
                            Buyer Protection
                        </div>
                        <div class="flex items-center gap-2 text-[10px] sm:text-[11px] text-navy/55">
                            <x-lucide-rotate-ccw class="w-4 h-4 text-teal-dark shrink-0" />
                            Easy Returns
                        </div>
                        <div class="flex items-center gap-2 text-[10px] sm:text-[11px] text-navy/55">
                            <x-lucide-badge-check class="w-4 h-4 text-teal-dark shrink-0" />
                            Authenticity
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SELLER CARD --}}
        <section class="bg-white border border-gray-border rounded-2xl sm:rounded-3xl mt-4 p-5 sm:p-6 product-reveal">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-teal-light text-teal-dark flex items-center justify-center shrink-0">
                        <x-lucide-store class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-semibold text-navy">{{ $product['seller_name'] }}</h2>
                            <span class="text-[9px] font-bold bg-teal text-white px-2 py-1 rounded-full">Preferred</span>
                        </div>
                        <p class="text-xs text-navy/40 mt-1">Active recently</p>
                        <div class="flex gap-2 mt-3">
                            <button class="px-3 py-2 rounded-lg bg-teal-light text-teal-dark text-xs font-semibold">Chat Now</button>
                            <button class="px-3 py-2 rounded-lg border border-gray-border text-navy text-xs font-semibold hover:border-teal/40">View Shop</button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-8 gap-y-3 text-xs">
                    <div><span class="text-navy/40">Ratings</span><p class="font-semibold text-teal-dark mt-0.5">{{ $product['seller_rating'] }}</p></div>
                    <div><span class="text-navy/40">Products</span><p class="font-semibold text-teal-dark mt-0.5">{{ $product['seller_products'] }}</p></div>
                    <div><span class="text-navy/40">Response</span><p class="font-semibold text-teal-dark mt-0.5">{{ $product['seller_response'] }}</p></div>
                    <div><span class="text-navy/40">Joined</span><p class="font-semibold text-navy mt-0.5">{{ $product['seller_joined'] }}</p></div>
                </div>
            </div>
        </section>

        {{-- DETAILS --}}
        <section class="bg-white border border-gray-border rounded-2xl sm:rounded-3xl mt-4 p-5 sm:p-7 product-reveal">
            <h2 class="text-base sm:text-lg font-bold text-navy">Product Specifications</h2>
            <div class="grid sm:grid-cols-2 gap-x-10 gap-y-3 mt-5 text-sm">
                <div class="grid grid-cols-[120px_1fr] gap-3"><span class="text-navy/40">Category</span><span class="text-teal-dark">{{ $product['category'] }}</span></div>
                <div class="grid grid-cols-[120px_1fr] gap-3"><span class="text-navy/40">Stock</span><span class="text-navy">{{ $product['stock'] }} units</span></div>
                <div class="grid grid-cols-[120px_1fr] gap-3"><span class="text-navy/40">Condition</span><span class="text-navy">New</span></div>
                <div class="grid grid-cols-[120px_1fr] gap-3"><span class="text-navy/40">Ships From</span><span class="text-navy">Metro Manila</span></div>
            </div>

            <div class="mt-8">
                <h2 class="text-base sm:text-lg font-bold text-navy">Product Description</h2>
                <p class="text-xs sm:text-sm text-navy/60 leading-7 whitespace-pre-line mt-4">{{ $product['description'] }}</p>
            </div>
        </section>

        {{-- RATINGS & REVIEWS --}}
        <section class="bg-white border border-gray-border rounded-2xl sm:rounded-3xl mt-4 p-5 sm:p-7 product-reveal" id="ratings">
            <h2 class="text-base sm:text-lg font-bold text-navy">Product Ratings</h2>

            <div class="mt-5 rounded-2xl bg-[#EAF9F5] border border-teal/10 p-5">
                <div class="grid lg:grid-cols-[180px_1fr] gap-6 items-center">
                    <div class="text-center lg:text-left">
                        <div><span class="text-4xl font-bold text-teal-dark">{{ $product['rating'] }}</span><span class="text-lg text-teal-dark"> out of 5</span></div>
                        <div class="flex justify-center lg:justify-start mt-2">
                            @for ($i=0; $i<5; $i++)
                                <x-lucide-star class="w-4 h-4 fill-amber-400 text-amber-400" />
                            @endfor
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-review-filter="all" class="review-filter px-3.5 py-2 rounded-lg border border-teal bg-white text-teal-dark text-[11px] sm:text-xs font-semibold">All ({{ $product['reviews'] }})</button>
                        @foreach ($ratingBreakdown as $stars => $count)
                            <button type="button" data-review-filter="{{ $stars }}" class="review-filter px-3.5 py-2 rounded-lg border border-gray-border bg-white text-navy/65 text-[11px] sm:text-xs hover:border-teal/40 hover:text-teal-dark transition">{{ $stars }} Star ({{ $count }})</button>
                        @endforeach
                        <button type="button" data-review-filter="media" class="review-filter px-3.5 py-2 rounded-lg border border-gray-border bg-white text-navy/65 text-[11px] sm:text-xs hover:border-teal/40 hover:text-teal-dark transition">With Media (96)</button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mt-4">
                <p class="text-[11px] sm:text-xs text-navy/40">
                    Showing <span id="reviewVisibleCount" class="font-semibold text-navy">{{ $reviews->count() }}</span> preview reviews
                </p>
                <button type="button" id="clearReviewFilter" class="hidden text-[11px] sm:text-xs font-semibold text-teal-dark hover:text-navy transition">
                    Clear filter
                </button>
            </div>

            <div class="divide-y divide-gray-border mt-2">
                @foreach ($reviews as $review)
                    <article class="review-item py-5" data-review-rating="{{ $review['rating'] }}" data-review-media="{{ $review['image'] ? '1' : '0' }}">
                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-bg flex items-center justify-center text-xs font-bold text-navy/50 shrink-0">
                                {{ mb_substr($review['name'], 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-navy">{{ $review['name'] }}</p>
                                <div class="flex mt-1">
                                    @for ($i=1; $i<=5; $i++)
                                        <x-lucide-star class="w-3.5 h-3.5 {{ $i <= $review['rating'] ? 'fill-amber-400 text-amber-400' : 'text-gray-border' }}" />
                                    @endfor
                                </div>
                                <p class="text-[11px] text-navy/35 mt-1">{{ $review['date'] }} · Variation: {{ $review['variant'] }}</p>
                                <p class="text-xs sm:text-sm text-navy/65 leading-6 mt-3">{{ $review['comment'] }}</p>

                                @if ($review['image'])
                                    <button class="mt-3 w-20 h-20 rounded-xl overflow-hidden border border-gray-border">
                                        <img src="{{ asset($review['image']) }}" alt="Review photo" class="w-full h-full object-cover">
                                    </button>
                                @endif

                                <div class="flex items-center gap-4 mt-4 text-xs text-navy/40">
                                    <button class="flex items-center gap-1.5 hover:text-teal-dark transition">
                                        <x-lucide-thumbs-up class="w-3.5 h-3.5" />
                                        Helpful ({{ $review['helpful'] }})
                                    </button>
                                    <button class="hover:text-teal-dark transition">Report</button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex justify-center mt-2">
                <button class="px-5 py-2.5 rounded-xl border border-gray-border text-xs sm:text-sm font-semibold text-navy hover:border-teal/40 hover:text-teal-dark transition">
                    View All Reviews
                </button>
            </div>
        </section>

        {{-- RECOMMENDATIONS --}}
        <section class="mt-8 mb-4 product-reveal">
            <div class="flex items-end justify-between mb-4">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.14em] font-bold text-teal-dark">Recommended for you</p>
                    <h2 class="text-lg sm:text-xl font-bold text-navy mt-1">You May Also Like</h2>
                </div>
                <button class="hidden sm:flex items-center gap-1 text-xs font-semibold text-teal-dark">See More <x-lucide-chevron-right class="w-3.5 h-3.5"/></button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 product-stagger">
                @foreach ($relatedProducts->take(5) as $related)
                    <article class="group bg-white border border-gray-border rounded-2xl overflow-hidden hover:border-teal/30 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                        <a href="#" class="block aspect-square bg-gray-bg overflow-hidden">
                            <img src="{{ asset($related['image']) }}" alt="{{ $related['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </a>
                        <div class="p-3.5">
                            <h3 class="text-xs sm:text-xs sm:text-sm font-medium text-navy leading-5 line-clamp-2 min-h-10">{{ $related['name'] }}</h3>
                            <div class="flex items-end gap-2 mt-2">
                                <span class="text-sm sm:text-base font-bold text-teal-dark">₱{{ number_format($related['price']) }}</span>
                                @if ($related['original_price'])
                                    <span class="text-[10px] text-navy/30 line-through">₱{{ number_format($related['original_price']) }}</span>
                                @endif
                            </div>
                            <div class="flex justify-between items-center mt-2 text-[10px] text-navy/40">
                                <span class="flex items-center gap-1"><x-lucide-star class="w-3 h-3 fill-amber-400 text-amber-400"/>{{ $related['rating'] }}</span>
                                <span>{{ $related['sold'] }} sold</span>
                            </div>
                            <button class="w-full mt-3 py-2.5 rounded-xl bg-teal hover:bg-teal-dark text-white text-[11px] font-semibold transition">Add to Cart</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</section>

{{-- PAGE MOTION --}}
<style>
    .product-reveal {
        opacity: 0;
        transform: translateY(16px);
        transition:
            opacity .65s ease var(--product-delay, 0ms),
            transform .65s cubic-bezier(.22, 1, .36, 1) var(--product-delay, 0ms);
    }

    .product-reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .product-stagger > * {
        opacity: 0;
        transform: translateY(14px);
        transition:
            opacity .55s ease,
            transform .55s cubic-bezier(.22, 1, .36, 1);
    }

    .product-stagger.is-visible > * {
        opacity: 1;
        transform: translateY(0);
    }

    .product-stagger.is-visible > *:nth-child(2) { transition-delay: 45ms; }
    .product-stagger.is-visible > *:nth-child(3) { transition-delay: 90ms; }
    .product-stagger.is-visible > *:nth-child(4) { transition-delay: 135ms; }
    .product-stagger.is-visible > *:nth-child(5) { transition-delay: 180ms; }

    @media (prefers-reduced-motion: reduce) {
        .product-reveal,
        .product-stagger > * {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }
    }
</style>


{{-- =========================================================
    FOOTER
========================================================= --}}
@include('partials.footer')

@endsection


{{-- =========================================================
    PAGE-SPECIFIC SCRIPTS
========================================================= --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const decreaseButton = document.querySelector('[data-qty-decrease]');
    const increaseButton = document.querySelector('[data-qty-increase]');
    const qtyInput = document.querySelector('[data-qty-input]');

    if (decreaseButton && increaseButton && qtyInput) {
        const max = parseInt(qtyInput.max, 10) || 1;

        decreaseButton.addEventListener('click', function () {
            const current = parseInt(qtyInput.value, 10) || 1;
            qtyInput.value = Math.max(1, current - 1);
        });

        increaseButton.addEventListener('click', function () {
            const current = parseInt(qtyInput.value, 10) || 1;
            qtyInput.value = Math.min(max, current + 1);
        });
    }


    const productMotionReduced =
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const productRevealTargets =
        document.querySelectorAll('.product-reveal, .product-stagger');

    if (productMotionReduced || !('IntersectionObserver' in window)) {
        productRevealTargets.forEach(function (item) {
            item.classList.add('is-visible');
        });
    } else {
        const productRevealObserver = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        productRevealObserver.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.12,
                rootMargin: '0px 0px -24px 0px'
            }
        );

        productRevealTargets.forEach(function (item) {
            productRevealObserver.observe(item);
        });
    }

    // Product gallery
    const mainProductImage = document.getElementById('mainProductImage');
    const galleryThumbs = document.querySelectorAll('[data-gallery-thumb]');
    galleryThumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            if (mainProductImage) {
                mainProductImage.src = thumb.dataset.galleryThumb;
            }
            galleryThumbs.forEach(t => {
                t.classList.remove('border-teal');
                t.classList.add('border-transparent');
            });
            thumb.classList.remove('border-transparent');
            thumb.classList.add('border-teal');
        });
    });

    // Variant selection
    document.querySelectorAll('[data-variant-group]').forEach(function (group) {
        group.querySelectorAll('[data-variant-option]').forEach(function (option) {
            option.addEventListener('click', function () {
                group.querySelectorAll('[data-variant-option]').forEach(function (item) {
                    item.classList.remove('border-teal', 'text-teal-dark', 'bg-teal-light/50');
                    item.classList.add('border-gray-border', 'text-navy/65');
                });

                option.classList.remove('border-gray-border', 'text-navy/65');
                option.classList.add('border-teal', 'text-teal-dark', 'bg-teal-light/50');
            });
        });
    });

    // Review filtering
    const reviewFilterButtons = document.querySelectorAll('[data-review-filter]');
    const reviewItems = document.querySelectorAll('.review-item');
    const reviewVisibleCount = document.getElementById('reviewVisibleCount');
    const reviewEmptyState = document.getElementById('reviewEmptyState');
    const clearReviewFilter = document.getElementById('clearReviewFilter');

    function applyReviewFilter(filter) {
        let visibleCount = 0;

        reviewItems.forEach(function (item) {
            const rating = item.dataset.reviewRating;
            const hasMedia = item.dataset.reviewMedia === '1';

            const shouldShow =
                filter === 'all' ||
                filter === rating ||
                (filter === 'media' && hasMedia);

            item.classList.toggle('hidden', !shouldShow);

            if (shouldShow) {
                visibleCount++;
            }
        });

        reviewFilterButtons.forEach(function (button) {
            const active = button.dataset.reviewFilter === filter;

            button.classList.toggle('border-teal', active);
            button.classList.toggle('text-teal-dark', active);
            button.classList.toggle('bg-teal-light/40', active);

            button.classList.toggle('border-gray-border', !active);
            button.classList.toggle('text-navy/65', !active);
            button.classList.toggle('bg-white', !active);
        });

        if (reviewVisibleCount) {
            reviewVisibleCount.textContent = visibleCount;
        }

        if (reviewEmptyState) {
            reviewEmptyState.classList.toggle('hidden', visibleCount !== 0);
        }

        if (clearReviewFilter) {
            clearReviewFilter.classList.toggle('hidden', filter === 'all');
        }
    }

    reviewFilterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            applyReviewFilter(button.dataset.reviewFilter);
        });
    });

    if (clearReviewFilter) {
        clearReviewFilter.addEventListener('click', function () {
            applyReviewFilter('all');
        });
    }

    applyReviewFilter('all');

});
</script>
@endpush