{{-- =========================================================
    DEALS / PROMOTION — LIGHT PREMIUM REDESIGN
========================================================= --}}
<section
    id="deals"
    class="relative overflow-hidden py-12 sm:py-16 lg:py-18 bg-gray-bg"
>

    <div class="pointer-events-none absolute -top-24 -right-20 w-72 h-72 rounded-full bg-teal/8 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-28 -left-20 w-72 h-72 rounded-full bg-navy/4 blur-3xl"></div>

    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative overflow-hidden reveal-up
                   rounded-3xl
                   bg-white
                   border border-gray-border
                   shadow-lg shadow-navy/5"
        >

            <div class="grid lg:grid-cols-[0.95fr_1.05fr] items-stretch">

                {{-- LEFT CONTENT --}}
                <div class="relative z-10 px-6 sm:px-8 lg:px-10 xl:px-12 py-9 sm:py-11 lg:py-12">

                    <span
                        class="inline-flex items-center gap-2
                               bg-teal-light
                               text-teal-dark
                               border border-teal/10
                               px-3.5 py-2
                               rounded-full
                               text-[10px] sm:text-xs
                               font-semibold tracking-wide
                               mb-5"
                    >
                        <x-lucide-badge-percent class="w-3.5 h-3.5" />
                        SHOPHOP DEALS
                    </span>


                    <h2
                        class="text-navy
                               text-3xl sm:text-4xl lg:text-[42px]
                               font-bold leading-tight"
                    >
                        Great Finds.
                        <span class="block text-teal">
                            Better Prices.
                        </span>
                    </h2>


                    <p
                        class="text-sm sm:text-base
                               text-navy/55
                               mt-4 sm:mt-5
                               max-w-lg
                               leading-relaxed"
                    >
                        Discover limited-time offers, discounted favorites,
                        and great-value finds across ShopHop.
                    </p>


                    <div class="flex flex-wrap gap-x-5 gap-y-3 mt-6">

                        <div class="flex items-center gap-2 text-xs sm:text-sm text-navy/55">
                            <span class="w-7 h-7 rounded-full bg-teal-light flex items-center justify-center">
                                <x-lucide-check class="w-3.5 h-3.5 text-teal-dark" />
                            </span>
                            Limited-time deals
                        </div>

                        <div class="flex items-center gap-2 text-xs sm:text-sm text-navy/55">
                            <span class="w-7 h-7 rounded-full bg-teal-light flex items-center justify-center">
                                <x-lucide-tag class="w-3.5 h-3.5 text-teal-dark" />
                            </span>
                            Selected discounts
                        </div>

                    </div>


                    <a
                        href="#trending"
                        class="group inline-flex
                               items-center justify-center gap-2
                               mt-7 sm:mt-8
                               bg-teal hover:bg-teal-dark
                               text-white
                               text-sm font-semibold
                               px-6 py-3.5
                               rounded-full
                               shadow-lg shadow-teal/20
                               hover:-translate-y-0.5
                               hover:shadow-xl
                               transition-all duration-300"
                    >
                        Explore Deals

                        <x-lucide-arrow-right
                            class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"
                        />
                    </a>

                </div>


                {{-- RIGHT BENEFIT GRID --}}
                <div
                    class="relative overflow-hidden
                           bg-[#EAF9F5]
                           px-5 sm:px-7 lg:px-8
                           py-6 sm:py-8 lg:py-10
                           flex items-center"
                >

                    <div class="pointer-events-none absolute -right-16 -top-16 w-48 h-48 rounded-full bg-teal/10"></div>
                    <div class="pointer-events-none absolute -left-16 -bottom-16 w-48 h-48 rounded-full bg-white/60"></div>

                    <div class="relative grid grid-cols-2 gap-3 sm:gap-4 w-full">

                        <div
                            class="group bg-white/90
                                   rounded-2xl
                                   p-4 sm:p-5
                                   border border-white
                                   shadow-sm shadow-navy/5
                                   hover:-translate-y-1
                                   hover:shadow-lg
                                   transition-all duration-300"
                        >
                            <div class="w-10 h-10 rounded-xl bg-teal-light flex items-center justify-center mb-4">
                                <x-lucide-tag class="w-5 h-5 text-teal-dark" />
                            </div>

                            <p class="text-navy text-lg sm:text-xl font-bold">
                                Up to 50%
                            </p>

                            <p class="text-navy/45 text-[10px] sm:text-xs mt-1.5">
                                Off selected items
                            </p>
                        </div>


                        <div
                            class="group bg-white/90
                                   rounded-2xl
                                   p-4 sm:p-5
                                   border border-white
                                   shadow-sm shadow-navy/5
                                   hover:-translate-y-1
                                   hover:shadow-lg
                                   transition-all duration-300"
                        >
                            <div class="w-10 h-10 rounded-xl bg-teal-light flex items-center justify-center mb-4">
                                <x-lucide-truck class="w-5 h-5 text-teal-dark" />
                            </div>

                            <p class="text-navy text-lg sm:text-xl font-bold">
                                Fast
                            </p>

                            <p class="text-navy/45 text-[10px] sm:text-xs mt-1.5">
                                Delivery options
                            </p>
                        </div>


                        <div
                            class="group bg-white/90
                                   rounded-2xl
                                   p-4 sm:p-5
                                   border border-white
                                   shadow-sm shadow-navy/5
                                   hover:-translate-y-1
                                   hover:shadow-lg
                                   transition-all duration-300"
                        >
                            <div class="w-10 h-10 rounded-xl bg-teal-light flex items-center justify-center mb-4">
                                <x-lucide-shield-check class="w-5 h-5 text-teal-dark" />
                            </div>

                            <p class="text-navy text-lg sm:text-xl font-bold">
                                Secure
                            </p>

                            <p class="text-navy/45 text-[10px] sm:text-xs mt-1.5">
                                Safe shopping experience
                            </p>
                        </div>


                        <div
                            class="group bg-white/90
                                   rounded-2xl
                                   p-4 sm:p-5
                                   border border-white
                                   shadow-sm shadow-navy/5
                                   hover:-translate-y-1
                                   hover:shadow-lg
                                   transition-all duration-300"
                        >
                            <div class="w-10 h-10 rounded-xl bg-teal-light flex items-center justify-center mb-4">
                                <x-lucide-headphones class="w-5 h-5 text-teal-dark" />
                            </div>

                            <p class="text-navy text-lg sm:text-xl font-bold">
                                24/7
                            </p>

                            <p class="text-navy/45 text-[10px] sm:text-xs mt-1.5">
                                Customer support
                            </p>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>


{{-- =========================================================
    TRENDING PRODUCTS — COMPACT CARDS
========================================================= --}}
<section id="trending" class="py-12 sm:py-16 lg:py-18 bg-white">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6 sm:mb-8 reveal-up">
            <div>
                <p class="text-teal-dark text-xs sm:text-sm font-semibold mb-2 tracking-wide">POPULAR PICKS</p>
                <h2 class="text-navy">Trending Now</h2>
                <p class="text-sm sm:text-base text-navy/50 mt-2">Products shoppers are loving right now.</p>
            </div>

            <a href="#" class="group inline-flex items-center gap-2 text-sm font-semibold text-teal-dark hover:text-navy transition-colors duration-300">
                View all products
                <x-lucide-arrow-right class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4 reveal-up" style="--reveal-delay: 80ms;">
            @php
                /*
                 * Landing-page fallback:
                 * Make sure the Trending section always has at least 5 cards.
                 * Existing products are kept; hardcoded demo products are appended only when needed.
                 */
                $trendingDisplayProducts = collect($trendingProducts ?? []);

                $trendingFallbackProducts = collect([
                    [
                        'name' => 'Wireless Headphones',
                        'category' => 'Electronics and Gadgets',
                        'price' => 1299,
                        'original_price' => 1599,
                        'rating' => '4.8',
                        'reviews' => 186,
                        'image' => 'hero/earbuds.jpg',
                    ],
                    [
                        'name' => 'Fitness Smart Watch',
                        'category' => 'Electronics and Gadgets',
                        'price' => 1799,
                        'original_price' => 2199,
                        'rating' => '4.9',
                        'reviews' => 241,
                        'image' => 'hero/watch.jpg',
                    ],
                    [
                        'name' => 'Everyday Sneakers',
                        'category' => 'Men\'s Apparel',
                        'price' => 1499,
                        'original_price' => 1899,
                        'rating' => '4.7',
                        'reviews' => 154,
                        'image' => 'hero/sneaker.jpg',
                    ],
                    [
                        'name' => 'Premium Earbuds',
                        'category' => 'Electronics and Gadgets',
                        'price' => 999,
                        'original_price' => 1299,
                        'rating' => '4.8',
                        'reviews' => 203,
                        'image' => 'hero/earbuds.jpg',
                    ],
                    [
                        'name' => 'Active Fitness Watch',
                        'category' => 'Sports and Outdoors',
                        'price' => 1599,
                        'original_price' => 1999,
                        'rating' => '4.9',
                        'reviews' => 178,
                        'image' => 'hero/watch.jpg',
                    ],
                ]);

                if ($trendingDisplayProducts->count() < 5) {
                    $needed = 5 - $trendingDisplayProducts->count();
                    $trendingDisplayProducts = $trendingDisplayProducts
                        ->concat($trendingFallbackProducts->take($needed));
                }

                // Landing page shows exactly five cards in this section.
                $trendingDisplayProducts = $trendingDisplayProducts->take(5);
            @endphp

            @foreach ($trendingDisplayProducts as $product)
                <article class="group bg-white rounded-2xl overflow-hidden border border-gray-border hover:border-teal/25 hover:-translate-y-1 hover:shadow-xl hover:shadow-navy/8 transition-all duration-300">
                    <div class="relative aspect-[1/0.82] bg-gray-bg overflow-hidden">
                        <img
                            src="{{ str_starts_with($product['image'], 'http') ? $product['image'] : asset('images/' . $product['image']) }}"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        >

                        @if ($product['original_price'])
                            <span class="absolute top-2 left-2 bg-teal text-white text-[9px] font-bold px-2.5 py-1 rounded-full shadow-sm">SALE</span>
                        @endif

                        <button type="button" data-login-required class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/95 backdrop-blur flex items-center justify-center text-navy hover:text-teal-dark hover:scale-110 shadow-sm transition-all duration-200" title="Add to wishlist" aria-label="Add {{ $product['name'] }} to wishlist">
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <div class="p-3 sm:p-3.5 lg:p-3.5">
                        <span class="block text-[9px] sm:text-[10px] uppercase tracking-wide font-medium text-navy/40 truncate">{{ $product['category'] }}</span>

                        <h3 class="text-[13px] sm:text-sm font-semibold text-navy mt-1 mb-1.5 line-clamp-1">{{ $product['name'] }}</h3>

                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="text-amber-400 text-[9px]">★★★★★</span>
                            <span class="text-[9px] sm:text-[10px] text-navy/40">{{ $product['rating'] }} ({{ $product['reviews'] }})</span>
                        </div>

                        <div class="flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5 mb-3">
                            <span class="font-bold text-sm sm:text-base text-navy">₱{{ number_format($product['price']) }}</span>
                            @if ($product['original_price'])
                                <span class="line-through text-[9px] sm:text-[10px] text-navy/30">₱{{ number_format($product['original_price']) }}</span>
                            @endif
                        </div>

                        <button type="button" data-login-required class="w-full flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-[10px] sm:text-xs font-semibold py-2 sm:py-2.5 rounded-xl transition-all duration-300 hover:shadow-md active:scale-[0.98]">
                            <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                            Add to Cart
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

