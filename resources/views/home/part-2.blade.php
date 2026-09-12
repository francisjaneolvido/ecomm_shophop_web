{{-- =========================================================
    SHOPHOP LANDING — PART 2
    Public marketplace value + trending product discovery
========================================================= --}}

@php
    /*
    |--------------------------------------------------------------------------
    | LANDING PRODUCT FALLBACKS
    |--------------------------------------------------------------------------
    | Existing controller products are preserved. Demo records are only
    | appended if the public landing needs more visual content.
    */

    $landingProductImageUrl = function ($path) {
        $path = trim((string) $path);

        if ($path === '') {
            return asset('images/category_icons_bg/electronics_gadgets/cctv.jpg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'images/') || str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('images/' . $path);
    };

    $landingFallbackProducts = collect([
        [
            'name' => 'Smart Wi-Fi CCTV Camera',
            'category' => 'Electronics and Gadgets',
            'price' => 699,
            'original_price' => 1099,
            'rating' => '4.8',
            'reviews' => 184,
            'sold' => 1200,
            'image' => 'images/category_icons_bg/electronics_gadgets/cctv.jpg',
        ],
        [
            'name' => 'Desktop CPU Home Office Set',
            'category' => 'Electronics and Gadgets',
            'price' => 2499,
            'original_price' => 3199,
            'rating' => '4.7',
            'reviews' => 92,
            'sold' => 540,
            'image' => 'images/category_icons_bg/electronics_gadgets/cpu.jpg',
        ],
        [
            'name' => 'Crunchy Snack Variety Pack',
            'category' => 'Food and Gourmet',
            'price' => 179,
            'original_price' => 249,
            'rating' => '4.9',
            'reviews' => 327,
            'sold' => 2100,
            'image' => 'images/category_icons_bg/food_gourmet/chips.jpg',
        ],
        [
            'name' => 'Minimal Desk & Bedside Lamp',
            'category' => 'Furniture and Office Equipment',
            'price' => 399,
            'original_price' => 599,
            'rating' => '4.8',
            'reviews' => 145,
            'sold' => 760,
            'image' => 'images/category_icons_bg/furniture_office/tablelamps.jpg',
        ],
        [
            'name' => 'Indoor Security Camera Bundle',
            'category' => 'Electronics and Gadgets',
            'price' => 999,
            'original_price' => 1499,
            'rating' => '4.9',
            'reviews' => 211,
            'sold' => 930,
            'image' => 'images/category_icons_bg/electronics_gadgets/cctv.jpg',
        ],
        [
            'name' => 'Compact PC Tower Essentials',
            'category' => 'Electronics and Gadgets',
            'price' => 2799,
            'original_price' => 3599,
            'rating' => '4.8',
            'reviews' => 116,
            'sold' => 610,
            'image' => 'images/category_icons_bg/electronics_gadgets/cpu.jpg',
        ],
    ]);

    $landingTrendingProducts = collect($trendingProducts ?? []);

    if ($landingTrendingProducts->count() < 6) {
        $landingTrendingProducts = $landingTrendingProducts
            ->concat(
                $landingFallbackProducts->take(
                    6 - $landingTrendingProducts->count()
                )
            );
    }

    $landingTrendingProducts = $landingTrendingProducts
        ->take(6)
        ->values();
@endphp


{{-- =========================================================
    MARKETPLACE MIX — IMAGE-LED PROMO SECTION
========================================================= --}}
<section id="deals" class="scroll-mt-20 bg-gray-bg py-7 sm:py-8 lg:py-9 border-b border-gray-border/70">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-4 reveal-up">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">
                    TODAY ON SHOPHOP
                </p>

                <h2 class="mt-0.5 text-[19px] sm:text-[21px] lg:text-[23px] font-bold text-navy">
                    Explore more than one aisle
                </h2>

                <p class="mt-1 text-[10px] sm:text-[11px] text-navy/45">
                    A quick mix of tech, food, home, and everyday finds from around the marketplace.
                </p>
            </div>

            <a
                href="#trending"
                class="hidden sm:inline-flex items-center gap-1.5 text-[10.5px] font-semibold text-teal-dark hover:text-navy transition"
            >
                See trending products
                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>
        </div>


        <div class="grid lg:grid-cols-[1.2fr_.8fr] gap-3">

            {{-- MAIN FEATURE --}}
            <a
                href="#trending"
                class="group relative min-h-82.5 sm:min-h-97.5 overflow-hidden rounded-2xl border border-gray-border bg-white reveal-up"
            >
                <img
                    src="{{ asset('images/category_icons_bg/electronics_gadgets/cpu.jpg') }}"
                    alt="Electronics and computer deals"
                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.04]"
                >

                <div class="absolute inset-0 bg-linear-to-r from-navy/95 via-navy/68 to-navy/10"></div>
                <div class="absolute inset-0 bg-linear-to-t from-navy/35 via-transparent to-transparent"></div>

                <div class="relative z-10 h-full min-h-82.5 sm:min-h-97.5 p-5 sm:p-7 flex flex-col justify-center max-w-[72%]">

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 border border-white/15 px-2.5 py-1 text-[8.5px] font-bold tracking-[0.12em] text-teal backdrop-blur">
                            SHOPHOP DEALS
                        </span>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-coral px-2.5 py-1 text-[8.5px] font-bold text-white">
                            <x-lucide-clock class="w-3 h-3" />
                            <span data-countdown>00:00:00</span>
                        </span>
                    </div>

                    <h3 class="mt-4 text-[25px] sm:text-[31px] lg:text-[35px] font-extrabold leading-[1.04] tracking-[-0.03em] text-white">
                        Browse more.
                        <span class="block text-teal">
                            Spend smarter.
                        </span>
                    </h3>

                    <p class="mt-3 max-w-md text-[10px] sm:text-[11px] leading-relaxed text-white/65">
                        Compare marketplace finds, sale prices, and promos before deciding what belongs in your cart.
                    </p>

                    <span class="mt-5 inline-flex w-fit items-center gap-2 rounded-xl bg-teal px-4 py-2.5 text-[10px] sm:text-[11px] font-bold text-white group-hover:bg-teal-dark transition">
                        Explore Products
                        <x-lucide-arrow-right class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" />
                    </span>
                </div>
            </a>


            {{-- IMAGE-LED QUICK PICKS --}}
            <div class="grid grid-cols-2 lg:grid-cols-1 gap-3">

                <a
                    href="#trending"
                    class="group relative min-h-38.75 lg:min-h-0 overflow-hidden rounded-2xl border border-gray-border bg-white reveal-up"
                    style="--reveal-delay: 60ms;"
                >
                    <img
                        src="{{ asset('images/category_icons_bg/electronics_gadgets/cctv.jpg') }}"
                        alt="Smart home products"
                        class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.05]"
                    >

                    <div class="absolute inset-0 bg-linear-to-t from-navy/90 via-navy/25 to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 p-4">
                        <span class="inline-flex rounded-md bg-teal px-2 py-1 text-[8px] font-bold text-white">
                            SMART LIVING
                        </span>

                        <p class="mt-2 text-[13px] sm:text-[15px] font-bold text-white">
                            Security & smart-home finds
                        </p>

                        <p class="mt-1 text-[8.5px] text-white/55">
                            Cameras, gadgets, and connected essentials.
                        </p>
                    </div>
                </a>


                <div class="grid grid-cols-2 gap-3 lg:grid-cols-2">

                    <a
                        href="#trending"
                        class="group relative min-h-38.75 overflow-hidden rounded-2xl border border-gray-border bg-white reveal-up"
                        style="--reveal-delay: 100ms;"
                    >
                        <img
                            src="{{ asset('images/category_icons_bg/food_gourmet/chips.jpg') }}"
                            alt="Food and gourmet products"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.06]"
                        >

                        <div class="absolute inset-0 bg-linear-to-t from-navy/90 via-navy/20 to-transparent"></div>

                        <div class="absolute inset-x-0 bottom-0 p-3.5">
                            <span class="text-[8px] font-bold tracking-widest text-teal">
                                FOOD & GOURMET
                            </span>

                            <p class="mt-1 text-[11px] sm:text-[12px] font-bold leading-tight text-white">
                                Snack favorites
                            </p>
                        </div>
                    </a>


                    <a
                        href="#new-arrivals"
                        class="group relative min-h-38.75 overflow-hidden rounded-2xl border border-gray-border bg-white reveal-up"
                        style="--reveal-delay: 140ms;"
                    >
                        <img
                            src="{{ asset('images/category_icons_bg/furniture_office/tablelamps.jpg') }}"
                            alt="Home and office products"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.06]"
                        >

                        <div class="absolute inset-0 bg-linear-to-t from-navy/90 via-navy/20 to-transparent"></div>

                        <div class="absolute inset-x-0 bottom-0 p-3.5">
                            <span class="text-[8px] font-bold tracking-widest text-teal">
                                HOME & OFFICE
                            </span>

                            <p class="mt-1 text-[11px] sm:text-[12px] font-bold leading-tight text-white">
                                Easy room refresh
                            </p>
                        </div>
                    </a>

                </div>
            </div>
        </div>


        {{-- BOTTOM MARKETPLACE NOTE --}}
        <div class="mt-3 grid sm:grid-cols-3 overflow-hidden rounded-2xl border border-gray-border bg-white reveal-up">

            <div class="px-4 py-3.5 border-b sm:border-b-0 sm:border-r border-gray-border/70">
                <p class="text-[9px] font-bold uppercase tracking-widest text-teal-dark">
                    More categories
                </p>
                <p class="mt-1 text-[9px] sm:text-[10px] text-navy/45">
                    Explore different departments without leaving ShopHop.
                </p>
            </div>

            <div class="px-4 py-3.5 border-b sm:border-b-0 sm:border-r border-gray-border/70">
                <p class="text-[9px] font-bold uppercase tracking-widesttext-teal-dark">
                    Deals at a glance
                </p>
                <p class="mt-1 text-[9px] sm:text-[10px] text-navy/45">
                    Spot markdowns, promos, and popular marketplace picks faster.
                </p>
            </div>

            <div class="px-4 py-3.5">
                <p class="text-[9px] font-bold uppercase tracking-widest text-teal-dark">
                    Shop when ready
                </p>
                <p class="mt-1 text-[9px] sm:text-[10px] text-navy/45">
                    Browse first, then sign in when you want to save or purchase.
                </p>
            </div>

        </div>
    </div>
</section>
{{-- =========================================================
    TRENDING PRODUCTS — PUBLIC DISCOVERY
========================================================= --}}
<section id="trending" class="scroll-mt-20 bg-white py-7 sm:py-8 lg:py-9 border-b border-gray-border/70">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-4 reveal-up">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">
                    POPULAR PICKS
                </p>

                <h2 class="mt-0.5 text-[19px] sm:text-[21px] lg:text-[23px] font-bold text-navy">
                    Trending Around ShopHop
                </h2>

                <p class="mt-1 text-[10px] sm:text-[11px] text-navy/45">
                    A public preview of products and categories shoppers can discover.
                </p>
            </div>

            <div class="hidden sm:flex gap-1.5">
                <button
                    type="button"
                    data-landing-shelf-prev
                    aria-label="Previous trending products"
                    class="w-9 h-9 rounded-full border border-gray-border bg-white flex items-center justify-center text-navy/45 hover:text-teal-dark hover:border-teal/30 transition"
                >
                    <x-lucide-chevron-left class="w-4 h-4" />
                </button>

                <button
                    type="button"
                    data-landing-shelf-next
                    aria-label="Next trending products"
                    class="w-9 h-9 rounded-full border border-gray-border bg-white flex items-center justify-center text-navy/45 hover:text-teal-dark hover:border-teal/30 transition"
                >
                    <x-lucide-chevron-right class="w-4 h-4" />
                </button>
            </div>
        </div>


        <div data-landing-product-shelf>
            <div
                data-landing-product-track
                class="landing-product-shelf grid grid-flow-col gap-2.5 sm:gap-3 overflow-x-auto scroll-smooth snap-x snap-mandatory [&::-webkit-scrollbar]:hidden"
            >
                @foreach ($landingTrendingProducts as $product)

                    @php
                        $hasDiscount = ! empty($product['original_price']);

                        $discount = $hasDiscount && (float) $product['original_price'] > 0
                            ? max(
                                1,
                                (int) round(
                                    (1 - ((float) $product['price'] / (float) $product['original_price'])) * 100
                                )
                            )
                            : null;
                    @endphp

                    <article
                        style="--stagger-index: {{ $loop->index % 6 }};"
                        class="group stagger-item snap-start overflow-hidden rounded-xl border border-gray-border bg-white hover:border-teal/35 hover:-translate-y-0.5 hover:shadow-lg transition-all"
                    >

                        <div class="relative aspect-4/3 overflow-hidden bg-gray-bg">

                            <img
                                src="{{ $landingProductImageUrl($product['image']) }}"
                                alt="{{ $product['name'] }}"
                                class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500"
                            >

                            @if ($discount)
                                <span class="absolute left-2 top-2 rounded-md bg-coral px-2 py-1 text-[8px] font-bold text-white shadow-sm">
                                    -{{ $discount }}%
                                </span>
                            @else
                                <span class="absolute left-2 top-2 rounded-md bg-navy px-2 py-1 text-[8px] font-bold text-white shadow-sm">
                                    POPULAR
                                </span>
                            @endif

                            <button
                                type="button"
                                data-login-required
                                data-wished="false"
                                class="wishlist-btn absolute right-2 top-2 flex w-7 h-7 items-center justify-center rounded-full bg-white/95 border border-black/5 text-navy/55 shadow-sm hover:text-teal-dark hover:scale-105 transition"
                                aria-label="Add {{ $product['name'] }} to wishlist"
                            >
                                <x-lucide-heart class="w-3.5 h-3.5" />
                            </button>

                        </div>


                        <div class="p-2.5 sm:p-3">

                            <p class="text-[8.5px] sm:text-[9px] uppercase tracking-wide text-navy/35 truncate">
                                {{ $product['category'] }}
                            </p>

                            <h3 class="mt-0.5 text-[10.5px] sm:text-[11.5px] font-semibold text-navy leading-snug line-clamp-2 min-h-7">
                                {{ $product['name'] }}
                            </h3>

                            <div class="mt-1.5 flex items-center gap-1 text-[8px] sm:text-[8.5px] text-navy/35">
                                <span class="text-amber-400">★</span>
                                <span>{{ $product['rating'] }}</span>
                                <span>·</span>
                                <span>{{ number_format($product['reviews'] ?? 0) }} reviews</span>
                            </div>

                            <div class="mt-2 flex flex-wrap items-baseline gap-1.5">
                                <span class="text-[12px] sm:text-[13px] font-bold text-teal-dark">
                                    ₱{{ number_format($product['price']) }}
                                </span>

                                @if ($hasDiscount)
                                    <span class="text-[8px] sm:text-[9px] text-navy/25 line-through">
                                        ₱{{ number_format($product['original_price']) }}
                                    </span>
                                @endif
                            </div>

                            <button
                                type="button"
                                data-login-required
                                class="ripple-surface mt-2.5 w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal py-2 text-[9.5px] sm:text-[10.5px] font-semibold text-white hover:bg-teal-dark transition"
                            >
                                <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                                Add to Cart
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
