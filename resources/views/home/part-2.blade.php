{{-- =========================================================
    DEALS / PROMOTION — LIGHT PREMIUM REDESIGN
========================================================= --}}
<section
    id="deals"
    class="relative overflow-hidden py-8 sm:py-10 lg:py-12 bg-gray-bg"
>

    <div class="pointer-events-none absolute -top-24 -right-20 w-72 h-72 rounded-full bg-teal/8 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-28 -left-20 w-72 h-72 rounded-full bg-navy/4 blur-3xl"></div>

    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative overflow-hidden reveal-up
                   rounded-2xl
                   bg-white
                   border border-gray-border
                   shadow-sm shadow-navy/5"
        >

            <div class="grid lg:grid-cols-[0.95fr_1.05fr] items-stretch">

                {{-- LEFT CONTENT --}}
                <div class="relative z-10 px-5 sm:px-6 lg:px-8 xl:px-9 py-6 sm:py-7 lg:py-8">

                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span
                            class="inline-flex items-center gap-2
                                   bg-teal-light
                                   text-teal-dark
                                   border border-teal/10
                                   px-3 py-1.5
                                   rounded-full
                                   text-[9.5px] sm:text-[10.5px]
                                   font-semibold tracking-wide"
                        >
                            <x-lucide-badge-percent class="w-3.5 h-3.5" />
                            SHOPHOP DEALS
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5
                                   bg-navy text-white
                                   px-3 py-1.5
                                   rounded-full
                                   text-[9.5px] sm:text-[10.5px]
                                   font-semibold tracking-wide"
                        >
                            <x-lucide-clock class="w-3.5 h-3.5 text-teal" />
                            Refreshes in
                            <span data-countdown class="tabular-nums text-teal">00:00:00</span>
                        </span>
                    </div>


                    <p
                        role="heading"
                        aria-level="2"
                        class="text-navy
                               text-[24px] sm:text-[28px] lg:text-[32px]
                               font-bold leading-[1.12] tracking-[-0.02em]"
                    >
                        Great Finds.
                        <span class="block text-teal">
                            Better Prices.
                        </span>
                    </p>


                    <p
                        class="text-[12px] sm:text-[13px] lg:text-sm
                               text-navy/55
                               mt-3 sm:mt-4
                               max-w-lg
                               leading-relaxed"
                    >
                        Discover limited-time offers, discounted favorites,
                        and great-value finds across ShopHop.
                    </p>


                    <div class="flex flex-wrap gap-x-4 gap-y-2.5 mt-4 sm:mt-5">

                        <div class="flex items-center gap-1.5 text-[10.5px] sm:text-xs text-navy/55">
                            <span class="w-6 h-6 rounded-lg bg-teal-light flex items-center justify-center">
                                <x-lucide-check class="w-3 h-3 text-teal-dark" />
                            </span>
                            Limited-time deals
                        </div>

                        <div class="flex items-center gap-1.5 text-[10.5px] sm:text-xs text-navy/55">
                            <span class="w-6 h-6 rounded-lg bg-teal-light flex items-center justify-center">
                                <x-lucide-tag class="w-3 h-3 text-teal-dark" />
                            </span>
                            Selected discounts
                        </div>

                    </div>


                    <a
                        href="#trending"
                        class="group magnetic ripple-surface inline-flex
                               items-center justify-center gap-2
                               mt-5 sm:mt-6
                               bg-teal hover:bg-teal-dark
                               text-white
                               text-xs sm:text-[13px] font-semibold
                               px-5 py-2.5 sm:py-3
                               rounded-xl
                               shadow-lg shadow-teal/20
                               hover:shadow-xl
                               transition-colors duration-300"
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
                           px-4 sm:px-5 lg:px-6
                           py-5 sm:py-6 lg:py-7
                           flex items-center"
                >

                    <div class="pointer-events-none absolute -right-16 -top-16 w-48 h-48 rounded-full bg-teal/10"></div>
                    <div class="pointer-events-none absolute -left-16 -bottom-16 w-48 h-48 rounded-full bg-white/60"></div>

                    <div class="relative grid grid-cols-2 gap-2.5 sm:gap-3 w-full">

                        <div
                            style="--stagger-index: 0;"
                            class="group stagger-item bg-white/90
                                   rounded-xl
                                   p-3 sm:p-4
                                   border border-white
                                   shadow-sm shadow-navy/5
                                   hover:-translate-y-1
                                   hover:shadow-lg
                                   transition-[transform,box-shadow] duration-300"
                        >
                            <div class="w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center mb-3 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                                <x-lucide-tag class="w-4 h-4 text-teal-dark" />
                            </div>

                            <p class="text-navy text-[15px] sm:text-[17px] font-bold">
                                Up to 50%
                            </p>

                            <p class="text-navy/45 text-[9.5px] sm:text-[10.5px] mt-1">
                                Off selected items
                            </p>
                        </div>


                        <div
                            style="--stagger-index: 1;"
                            class="group stagger-item bg-white/90
                                   rounded-xl
                                   p-3 sm:p-4
                                   border border-white
                                   shadow-sm shadow-navy/5
                                   hover:-translate-y-1
                                   hover:shadow-lg
                                   transition-[transform,box-shadow] duration-300"
                        >
                            <div class="w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center mb-3 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                                <x-lucide-truck class="w-4 h-4 text-teal-dark" />
                            </div>

                            <p class="text-navy text-[15px] sm:text-[17px] font-bold">
                                Fast
                            </p>

                            <p class="text-navy/45 text-[9.5px] sm:text-[10.5px] mt-1">
                                Delivery options
                            </p>
                        </div>


                        <div
                            style="--stagger-index: 2;"
                            class="group stagger-item bg-white/90
                                   rounded-xl
                                   p-3 sm:p-4
                                   border border-white
                                   shadow-sm shadow-navy/5
                                   hover:-translate-y-1
                                   hover:shadow-lg
                                   transition-[transform,box-shadow] duration-300"
                        >
                            <div class="w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center mb-3 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                                <x-lucide-shield-check class="w-4 h-4 text-teal-dark" />
                            </div>

                            <p class="text-navy text-[15px] sm:text-[17px] font-bold">
                                Secure
                            </p>

                            <p class="text-navy/45 text-[9.5px] sm:text-[10.5px] mt-1">
                                Safe shopping experience
                            </p>
                        </div>


                        <div
                            style="--stagger-index: 3;"
                            class="group stagger-item bg-white/90
                                   rounded-xl
                                   p-3 sm:p-4
                                   border border-white
                                   shadow-sm shadow-navy/5
                                   hover:-translate-y-1
                                   hover:shadow-lg
                                   transition-[transform,box-shadow] duration-300"
                        >
                            <div class="w-8 h-8 rounded-lg bg-teal-light flex items-center justify-center mb-3 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                                <x-lucide-headphones class="w-4 h-4 text-teal-dark" />
                            </div>

                            <p class="text-navy text-[15px] sm:text-[17px] font-bold">
                                24/7
                            </p>

                            <p class="text-navy/45 text-[9.5px] sm:text-[10.5px] mt-1">
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
<section id="trending" class="py-8 sm:py-10 lg:py-12 bg-white">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-4 sm:mb-5 reveal-up">
            <div>
                <p class="text-teal-dark text-[10px] sm:text-[11px] font-semibold mb-1.5 tracking-[0.12em]">POPULAR PICKS</p>
                <p role="heading" aria-level="2" class="text-[20px] sm:text-[22px] lg:text-[24px] leading-tight font-bold text-navy">Trending Now</p>
                <p class="text-[11px] sm:text-xs text-navy/50 mt-1.5">Products shoppers are loving right now.</p>
            </div>

            <a href="#" class="group inline-flex items-center gap-1.5 text-[11px] sm:text-xs font-semibold text-teal-dark hover:text-navy transition-colors duration-300">
                View all products
                <x-lucide-arrow-right class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
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
                <article
                    style="--stagger-index: {{ $loop->index % 5 }};"
                    class="group stagger-item bg-white rounded-xl overflow-hidden border border-gray-border hover:border-teal/25 hover:-translate-y-1 hover:shadow-xl hover:shadow-navy/8 transition-[transform,box-shadow,border-color] duration-300"
                >
                    <div class="relative aspect-[1/0.82] bg-gray-bg overflow-hidden">
                        <img
                            src="{{ str_starts_with($product['image'], 'http') ? $product['image'] : asset('images/' . $product['image']) }}"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        >

                        @if ($product['original_price'])
                            <span class="absolute top-2 left-2 bg-teal text-white text-[9px] font-bold px-2.5 py-1 rounded-full shadow-sm">SALE</span>
                        @endif

                        <button type="button" data-login-required data-wished="false" class="wishlist-btn absolute top-2 right-2 w-7 h-7 rounded-lg bg-white/95 backdrop-blur flex items-center justify-center text-navy hover:text-teal-dark hover:scale-110 shadow-sm transition-all duration-200" title="Add to wishlist" aria-label="Add {{ $product['name'] }} to wishlist">
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </button>

                        {{-- Quick view — slides up on hover --}}
                        <a
                            href="#"
                            class="quick-view-bar absolute inset-x-0 bottom-0 flex items-center justify-center gap-1.5 bg-navy/90 backdrop-blur text-white text-[10px] sm:text-[11px] font-semibold py-2"
                        >
                            <x-lucide-eye class="w-3.5 h-3.5" />
                            Quick View
                        </a>
                    </div>

                    <div class="p-2.5 sm:p-3">
                        <span class="block text-[9px] sm:text-[10px] uppercase tracking-wide font-medium text-navy/40 truncate">{{ $product['category'] }}</span>

                        <p role="heading" aria-level="3" class="text-[11.5px] sm:text-[12.5px] font-semibold text-navy mt-1 mb-1.5 line-clamp-1">{{ $product['name'] }}</p>

                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="text-amber-400 text-[9px]">★★★★★</span>
                            <span class="text-[9px] sm:text-[10px] text-navy/40">{{ $product['rating'] }} ({{ $product['reviews'] }})</span>
                        </div>

                        <div class="flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5 mb-3">
                            <span class="font-bold text-xs sm:text-sm text-navy">₱{{ number_format($product['price']) }}</span>
                            @if ($product['original_price'])
                                <span class="line-through text-[9px] sm:text-[10px] text-navy/30">₱{{ number_format($product['original_price']) }}</span>
                            @endif
                        </div>

                        <button type="button" data-login-required class="ripple-surface w-full flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-[10px] sm:text-[11px] font-semibold py-2 rounded-lg transition-all duration-300 hover:shadow-md active:scale-[0.98]">
                            <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                            Add to Cart
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>