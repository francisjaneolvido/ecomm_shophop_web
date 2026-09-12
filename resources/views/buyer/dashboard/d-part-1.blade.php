{{-- Path: resources/views/buyer/dashboard/d-part-1.blade.php --}}

@php
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD HELPERS + TEMP FALLBACK DATA
    |--------------------------------------------------------------------------
    | Real controller/database data still wins.
    | These preview records only appear when a real section is empty.
    */

    $sampleImages = [
        'cctv' => 'images/category_icons_bg/electronics_gadgets/cctv.jpg',
        'cpu' => 'images/category_icons_bg/electronics_gadgets/cpu.jpg',
        'chips' => 'images/category_icons_bg/food_gourmet/chips.jpg',
        'lamp' => 'images/category_icons_bg/furniture_office/tablelamps.jpg',
    ];

    $productImageUrl = function ($path) use ($sampleImages) {
        $path = trim((string) $path);

        if ($path === '') {
            return asset($sampleImages['cctv']);
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    };

    $productHref = function ($product) {
        return ($product['preview'] ?? false)
            ? '#'
            : route('buyer.product.show', $product['id']);
    };

    $previewProducts = [
        [
            'id' => 9001,
            'preview' => true,
            'name' => 'Smart Wi-Fi CCTV Camera',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cctv'],
            'price' => 699,
            'original_price' => 1099,
            'rating' => 4.8,
            'reviews' => 184,
            'sold' => 1200,
        ],
        [
            'id' => 9002,
            'preview' => true,
            'name' => 'Desktop CPU Home Office Set',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cpu'],
            'price' => 2499,
            'original_price' => 3199,
            'rating' => 4.7,
            'reviews' => 92,
            'sold' => 540,
        ],
        [
            'id' => 9003,
            'preview' => true,
            'name' => 'Crunchy Snack Variety Pack',
            'category' => 'Food and Gourmet',
            'image' => $sampleImages['chips'],
            'price' => 179,
            'original_price' => 249,
            'rating' => 4.9,
            'reviews' => 327,
            'sold' => 2100,
        ],
        [
            'id' => 9004,
            'preview' => true,
            'name' => 'Minimal Desk & Bedside Lamp',
            'category' => 'Furniture and Office Equipment',
            'image' => $sampleImages['lamp'],
            'price' => 399,
            'original_price' => 599,
            'rating' => 4.8,
            'reviews' => 145,
            'sold' => 760,
        ],
        [
            'id' => 9005,
            'preview' => true,
            'name' => 'Indoor Security Camera Bundle',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cctv'],
            'price' => 999,
            'original_price' => 1499,
            'rating' => 4.9,
            'reviews' => 211,
            'sold' => 930,
        ],
        [
            'id' => 9006,
            'preview' => true,
            'name' => 'Compact PC Tower Essentials',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cpu'],
            'price' => 2799,
            'original_price' => 3599,
            'rating' => 4.8,
            'reviews' => 116,
            'sold' => 610,
        ],
    ];

    if (empty($trendingProducts)) {
        $trendingProducts = array_slice($previewProducts, 0, 4);
    }

    if (empty($recentlyViewed)) {
        $recentlyViewed = array_slice($previewProducts, 0, 5);
    }

    if (empty($recommendedProducts)) {
        $recommendedProducts = [
            $previewProducts[1],
            $previewProducts[0],
            $previewProducts[3],
            $previewProducts[2],
            $previewProducts[5],
        ];
    }

    $heroPromos = [
        [
            'image' => $sampleImages['cctv'],
            'eyebrow' => 'SMART HOME PICKS',
            'title' => 'Upgrade your space with smarter security.',
            'description' => 'Discover cameras, sensors, and electronics for a safer connected home.',
            'cta' => 'Shop Electronics',
            'href' => '#categories',
        ],
        [
            'image' => $sampleImages['cpu'],
            'eyebrow' => 'TECH WEEK',
            'title' => 'Build the setup you have been planning.',
            'description' => 'PC essentials and gadgets with everyday ShopHop deals.',
            'cta' => 'Explore Tech',
            'href' => '#deals',
        ],
        [
            'image' => $sampleImages['chips'],
            'eyebrow' => 'SNACK DROP',
            'title' => 'Your next snack haul starts here.',
            'description' => 'Stock up on crowd favorites and quick treats for the whole crew.',
            'cta' => 'Browse Food',
            'href' => '#categories',
        ],
        [
            'image' => $sampleImages['lamp'],
            'eyebrow' => 'HOME REFRESH',
            'title' => 'Small upgrades. Big room energy.',
            'description' => 'Find stylish home and office pieces that make every corner feel better.',
            'cta' => 'Shop Home',
            'href' => '#new-arrivals',
        ],
    ];

    $marketShortcuts = [
        ['label' => 'Flash Deals', 'caption' => 'Limited-time offers', 'icon' => 'zap', 'href' => '#flash-sale'],
        ['label' => 'Free Shipping', 'caption' => 'Selected shops', 'icon' => 'truck', 'href' => '#recommended'],
        ['label' => 'Vouchers', 'caption' => 'Claim savings', 'icon' => 'ticket', 'href' => '#vouchers'],
        ['label' => 'Top Picks', 'caption' => 'Popular today', 'icon' => 'star', 'href' => '#recommended'],
        ['label' => 'New Arrivals', 'caption' => 'Fresh products', 'icon' => 'sparkles', 'href' => '#new-arrivals'],
        ['label' => 'Categories', 'caption' => 'Browse everything', 'icon' => 'layout-grid', 'href' => '#categories'],
    ];

    $activeOrderImage = $activeOrder['image'] ?? $sampleImages['cctv'];

    if (
        ! str_starts_with((string) $activeOrderImage, 'http://') &&
        ! str_starts_with((string) $activeOrderImage, 'https://') &&
        ! is_file(public_path(ltrim((string) $activeOrderImage, '/')))
    ) {
        $activeOrderImage = $sampleImages['cctv'];
    }
@endphp


{{-- =========================================================
    SERVICE STRIP
========================================================= --}}
<section class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-5 sm:gap-8 overflow-x-auto py-2.5 [&::-webkit-scrollbar]:hidden">
            <span class="shrink-0 inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-medium text-navy/50">
                <x-lucide-shield-check class="w-3.5 h-3.5 text-teal-dark" />
                Buyer Protection
            </span>

            <span class="shrink-0 inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-medium text-navy/50">
                <x-lucide-truck class="w-3.5 h-3.5 text-teal-dark" />
                Free Shipping Deals
            </span>

            <span class="shrink-0 inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-medium text-navy/50">
                <x-lucide-ticket class="w-3.5 h-3.5 text-teal-dark" />
                Daily Vouchers
            </span>

            <span class="shrink-0 inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-medium text-navy/50">
                <x-lucide-badge-percent class="w-3.5 h-3.5 text-teal-dark" />
                New Deals Every Day
            </span>
        </div>
    </div>
</section>


{{-- =========================================================
    MARKETPLACE HERO
========================================================= --}}
<section class="bg-gray-bg border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">

        {{-- Welcome + account stats --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 buyer-reveal">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.13em] text-teal-dark">
                    Your ShopHop
                </p>

                <h1 class="mt-0.5 text-[21px] sm:text-[25px] font-bold tracking-tight text-navy">
                    Hi, {{ $buyerName }} <span aria-hidden="true">👋</span>
                </h1>

                <p class="mt-0.5 text-[10px] sm:text-[11px] text-navy/45">
                    Deals, orders, and new finds — all in one place.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-2 w-full sm:w-auto">
                <a
                    href="#recommended"
                    class="min-w-0 sm:w-28 rounded-xl border border-gray-border bg-white px-3 py-2.5 hover:border-teal/30 hover:shadow-sm transition"
                >
                    <div class="flex items-center gap-2">
                        <x-lucide-heart class="w-3.5 h-3.5 text-teal-dark shrink-0" />
                        <div class="min-w-0">
                            <p class="text-sm font-bold leading-none text-navy">2</p>
                            <p class="text-[8px] text-navy/35 mt-1 truncate">Wishlist</p>
                        </div>
                    </div>
                </a>

                <a
                    href="{{ Route::has('buyer.cart') ? route('buyer.cart') : '#' }}"
                    class="min-w-0 sm:w-28 rounded-xl border border-gray-border bg-white px-3 py-2.5 hover:border-teal/30 hover:shadow-sm transition"
                >
                    <div class="flex items-center gap-2">
                        <x-lucide-shopping-cart class="w-3.5 h-3.5 text-teal-dark shrink-0" />
                        <div class="min-w-0">
                            <p class="text-sm font-bold leading-none text-navy">3</p>
                            <p class="text-[8px] text-navy/35 mt-1 truncate">Cart</p>
                        </div>
                    </div>
                </a>

                <a
                    href="#vouchers"
                    class="min-w-0 sm:w-28 rounded-xl border border-gray-border bg-white px-3 py-2.5 hover:border-teal/30 hover:shadow-sm transition"
                >
                    <div class="flex items-center gap-2">
                        <x-lucide-ticket class="w-3.5 h-3.5 text-teal-dark shrink-0" />
                        <div class="min-w-0">
                            <p class="text-sm font-bold leading-none text-navy">{{ count($vouchers) }}</p>
                            <p class="text-[8px] text-navy/35 mt-1 truncate">Vouchers</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>


        {{-- Large banner + two side promos --}}
        <div class="grid lg:grid-cols-[1.65fr_.72fr] gap-3">

            <div
                class="relative min-h-67.5 sm:min-h-80 overflow-hidden rounded-2xl bg-navy shadow-sm buyer-reveal"
                data-buyer-promo-slider
            >
                @foreach ($heroPromos as $index => $promo)
                    <article
                        data-buyer-promo-slide
                        class="buyer-promo-slide absolute inset-0 {{ $index === 0 ? 'is-active' : '' }}"
                        aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                    >
                        <img
                            src="{{ asset($promo['image']) }}"
                            alt=""
                            class="absolute inset-0 h-full w-full object-cover"
                        >

                        <div class="absolute inset-0 bg-linear-to-r from-navy/95 via-navy/72 to-navy/10"></div>
                        <div class="absolute inset-0 bg-linear-to-t from-navy/40 via-transparent to-transparent"></div>

                        <div class="relative z-10 flex h-full max-w-[68%] flex-col justify-center p-5 sm:p-7 lg:p-8">
                            <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-2.5 py-1 text-[8px] sm:text-[9px] font-bold tracking-[0.14em] text-teal backdrop-blur">
                                <x-lucide-sparkles class="w-3 h-3" />
                                {{ $promo['eyebrow'] }}
                            </span>

                            <p class="mt-3 text-[24px] sm:text-[34px] lg:text-[38px] font-extrabold leading-[1.02] tracking-[-0.035em] text-white">
                                {{ $promo['title'] }}
                            </p>

                            <p class="mt-3 max-w-md text-[9.5px] sm:text-[11px] leading-relaxed text-white/65">
                                {{ $promo['description'] }}
                            </p>

                            <div class="mt-5">
                                <a
                                    href="{{ $promo['href'] }}"
                                    class="group inline-flex items-center gap-2 rounded-xl bg-teal px-4 py-2.5 text-[10px] sm:text-[11px] font-bold text-white shadow-lg shadow-black/10 hover:bg-teal-dark transition"
                                >
                                    {{ $promo['cta'] }}
                                    <x-lucide-arrow-right class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" />
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach

                <div class="absolute z-20 bottom-4 left-5 sm:left-7 flex items-center gap-1.5">
                    @foreach ($heroPromos as $index => $promo)
                        <button
                            type="button"
                            data-buyer-promo-dot="{{ $index }}"
                            class="buyer-promo-dot h-1.5 rounded-full {{ $index === 0 ? 'is-active' : '' }}"
                            aria-label="Show promotional banner {{ $index + 1 }}"
                        ></button>
                    @endforeach
                </div>

                <div class="absolute z-20 bottom-3.5 right-4 flex gap-1.5">
                    <button
                        type="button"
                        data-buyer-promo-prev
                        aria-label="Previous promotion"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-white/15 bg-navy/35 text-white backdrop-blur hover:bg-white hover:text-navy transition"
                    >
                        <x-lucide-chevron-left class="w-3.5 h-3.5" />
                    </button>

                    <button
                        type="button"
                        data-buyer-promo-next
                        aria-label="Next promotion"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-white/15 bg-navy/35 text-white backdrop-blur hover:bg-white hover:text-navy transition"
                    >
                        <x-lucide-chevron-right class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>


            <div class="grid grid-cols-2 lg:grid-cols-1 gap-3">

                <a
                    href="#deals"
                    class="group relative min-h-37.5 lg:min-h-0 overflow-hidden rounded-2xl bg-white border border-gray-border shadow-sm buyer-reveal"
                    style="--buyer-delay: 70ms;"
                >
                    <img
                        src="{{ asset($sampleImages['chips']) }}"
                        alt="Snack deals"
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    >

                    <div class="absolute inset-0 bg-linear-to-trom-navy/90 via-navy/20 to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 p-4">
                        <span class="rounded-md bg-coral px-2 py-1 text-[8px] font-bold text-white">
                            SNACK DEAL
                        </span>

                        <p class="mt-2 text-[14px] sm:text-[16px] font-bold leading-tight text-white">
                            Snack favorites under ₱199
                        </p>

                        <p class="mt-1 text-[9px] text-white/60">
                            Grab-and-go treats for your next haul.
                        </p>
                    </div>
                </a>


                <a
                    href="#new-arrivals"
                    class="group relative min-h-37.5 lg:min-h-0 overflow-hidden rounded-2xl bg-white border border-gray-border shadow-sm buyer-reveal"
                    style="--buyer-delay: 120ms;"
                >
                    <img
                        src="{{ asset($sampleImages['lamp']) }}"
                        alt="Home and office deals"
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    >

                    <div class="absolute inset-0 bg-linear-to-t from-navy/90 via-navy/20 to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 p-4">
                        <span class="rounded-md bg-teal px-2 py-1 text-[8px] font-bold text-white">
                            HOME FIND
                        </span>

                        <p class="mt-2 text-[14px] sm:text-[16px] font-bold leading-tight text-white">
                            Fresh pieces for work & home
                        </p>

                        <p class="mt-1 text-[9px] text-white/60">
                            Small upgrades starting from ₱299.
                        </p>
                    </div>
                </a>
            </div>
        </div>


        {{-- Marketplace shortcuts --}}
        <div class="mt-3 grid grid-cols-3 md:grid-cols-6 overflow-hidden rounded-2xl border border-gray-border bg-white buyer-stagger">
            @foreach ($marketShortcuts as $shortcut)
                <a
                    href="{{ $shortcut['href'] }}"
                    class="group flex min-w-0 flex-col items-center justify-center px-2 py-3.5 text-center border-r border-b border-gray-border/70 last:border-r-0 hover:bg-teal-light/30 transition"
                >
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-light text-teal-dark group-hover:bg-teal group-hover:text-white transition">
                        <x-dynamic-component :component="'lucide-' . $shortcut['icon']" class="w-4 h-4" />
                    </span>

                    <span class="mt-2 text-[9.5px] sm:text-[10.5px] font-semibold text-navy truncate max-w-full">
                        {{ $shortcut['label'] }}
                    </span>

                    <span class="hidden sm:block mt-0.5 text-[8px] text-navy/35 truncate max-w-full">
                        {{ $shortcut['caption'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>


{{-- =========================================================
    FLASH SALE / TRENDING
========================================================= --}}
<section id="flash-sale" class="scroll-mt-20 bg-white py-5 sm:py-6 border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3 buyer-reveal">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-bold tracking-[0.12em] text-coral">
                        <x-lucide-zap class="w-3.5 h-3.5 fill-current" />
                        FLASH SALE
                    </span>

                    <span
                        data-shop-countdown
                        data-countdown-seconds="7919"
                        class="rounded-md bg-navy px-2 py-1 font-mono text-[8px] font-bold text-white"
                    >
                        02 : 11 : 59
                    </span>
                </div>

                <p class="mt-1 text-[16px] sm:text-[18px] font-bold text-navy">
                    Trending deals worth checking
                </p>

                <p class="mt-0.5 text-[10px] sm:text-[11px] text-navy/40">
                    Real products appear here once your seller inventory has active items.
                </p>
            </div>

            <a
                href="#deals"
                class="hidden sm:inline-flex items-center gap-1.5 text-[10.5px] font-semibold text-teal-dark hover:text-navy transition"
            >
                See all deals
                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>
        </div>


        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 buyer-stagger">
            @foreach (array_slice($trendingProducts, 0, 4) as $index => $product)
                @php
                    $discount = ! empty($product['original_price']) && $product['original_price'] > 0
                        ? max(1, (int) round((1 - ($product['price'] / $product['original_price'])) * 100))
                        : [18, 26, 32, 21][$index] ?? 20;

                    $claimed = [82, 61, 94, 73][$index] ?? 70;
                @endphp

                <article class="group overflow-hidden rounded-2xl border border-gray-border bg-white hover:border-teal/35 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">

                    <div class="relative aspect-4/3 overflow-hidden bg-gray-bg">
                        <a href="{{ $productHref($product) }}" class="block w-full h-full">
                            <img
                                src="{{ $productImageUrl($product['image']) }}"
                                alt="{{ $product['name'] }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            >
                        </a>

                        <span class="absolute left-2 top-2 rounded-md bg-coral px-2 py-1 text-[8px] font-bold text-white shadow-sm">
                            -{{ $discount }}%
                        </span>

                        <button
                            type="button"
                            class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full border border-black/5 bg-white/95 text-navy/55 shadow-sm hover:text-teal-dark transition"
                            aria-label="Add {{ $product['name'] }} to wishlist"
                        >
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <div class="p-3">
                        <p class="truncate text-[9px] text-navy/35">
                            {{ $product['category'] }}
                        </p>

                        <a
                            href="{{ $productHref($product) }}"
                            class="mt-0.5 block truncate text-[10.5px] sm:text-[11.5px] font-semibold text-navy hover:text-teal-dark transition"
                        >
                            {{ $product['name'] }}
                        </a>

                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <span class="text-[13px] sm:text-[14px] font-bold text-teal-dark">
                                ₱{{ number_format($product['price']) }}
                            </span>

                            @if (! empty($product['original_price']))
                                <span class="text-[8.5px] sm:text-[9px] text-navy/30 line-through">
                                    ₱{{ number_format($product['original_price']) }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-2.5">
                            <div class="h-1.5 overflow-hidden rounded-full bg-coral/10">
                                <div class="h-full rounded-full bg-coral" style="width: {{ $claimed }}%;"></div>
                            </div>

                            <p class="mt-1 text-[8px] font-medium text-coral">
                                {{ $claimed }}% claimed
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>


{{-- =========================================================
    MY PURCHASES
========================================================= --}}
<section id="my-orders" class="scroll-mt-20 bg-gray-bg py-5 sm:py-6 border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3 buyer-reveal">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold tracking-[0.12em] text-teal-dark">
                    MY SHOPPING
                </p>

                <p role="heading" aria-level="2" class="mt-0.5 text-[17px] sm:text-[19px] font-bold text-navy">
                    My Purchases
                </p>

                <p class="mt-0.5 text-[10px] sm:text-[11px] text-navy/45">
                    Check your order status at a glance.
                </p>
            </div>

            <a
                href="{{ Route::has('buyer.orders') ? route('buyer.orders') : '#' }}"
                class="hidden sm:inline-flex items-center gap-1.5 text-[10.5px] font-semibold text-teal-dark hover:text-navy transition"
            >
                View all orders
                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>
        </div>


        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 buyer-stagger">
            @foreach ($orderSummary as $status)
                <a
                    href="{{ Route::has('buyer.orders') ? route('buyer.orders') : '#' }}"
                    class="group rounded-2xl border border-gray-border bg-white px-3 py-3 hover:border-teal/35 hover:shadow-sm hover:-translate-y-0.5 transition-all"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-teal-light text-teal-dark flex items-center justify-center shrink-0 group-hover:bg-teal group-hover:text-white transition">
                            <x-dynamic-component
                                :component="'lucide-' . $status['icon']"
                                class="w-4 h-4"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[10.5px] sm:text-[11.5px] font-semibold text-navy truncate">
                                    {{ $status['label'] }}
                                </p>

                                @if ($status['count'] > 0)
                                    <span class="min-w-5 h-5 px-1 rounded-full bg-teal text-white text-[8px] font-bold flex items-center justify-center">
                                        {{ $status['count'] }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-[8.5px] sm:text-[9px] text-navy/35 mt-0.5">
                                {{ $status['count'] }} {{ $status['count'] === 1 ? 'order' : 'orders' }}
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>


        {{-- Active order --}}
        <div class="mt-3 overflow-hidden rounded-2xl border border-gray-border bg-white shadow-sm buyer-reveal">
            <div class="grid lg:grid-cols-[1fr_auto] gap-4 p-4 sm:p-5">

                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-gray-bg border border-gray-border shrink-0">
                        <img
                            src="{{ $productImageUrl($activeOrderImage) }}"
                            alt="{{ $activeOrder['product_name'] }}"
                            class="w-full h-full object-cover"
                        >
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-teal-light text-[8.5px] sm:text-[9px] font-semibold text-teal-dark">
                                <x-lucide-truck class="w-3 h-3" />
                                {{ $activeOrder['status'] }}
                            </span>

                            <span class="text-[8.5px] sm:text-[9px] text-navy/35">
                                {{ $activeOrder['order_number'] }}
                            </span>
                        </div>

                        <p class="mt-1.5 truncate text-[12px] sm:text-[13px] font-semibold text-navy">
                            {{ $activeOrder['product_name'] }}
                        </p>

                        <p class="mt-0.5 text-[9px] sm:text-[10px] text-navy/45 truncate">
                            {{ $activeOrder['variant'] }}
                            · Qty {{ $activeOrder['quantity'] }}
                            · ₱{{ number_format($activeOrder['price']) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between lg:justify-end gap-3">
                    <div class="lg:text-right">
                        <p class="text-[8px] uppercase tracking-[0.12em] text-navy/35">
                            Estimated Delivery
                        </p>

                        <p class="mt-1 text-[10.5px] sm:text-[11.5px] font-semibold text-teal-dark">
                            {{ $activeOrder['estimated_delivery'] }}
                        </p>
                    </div>

                    <a
                        href="{{ Route::has('buyer.orders') ? route('buyer.orders') : '#' }}"
                        class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg bg-teal px-3 text-[9.5px] font-semibold text-white hover:bg-teal-dark transition"
                    >
                        Track
                        <x-lucide-arrow-right class="w-3.5 h-3.5" />
                    </a>
                </div>
            </div>

            <div class="border-t border-gray-border/80 px-4 sm:px-5 py-3">
                <div class="grid grid-cols-5">
                    @foreach ($activeOrder['steps'] as $step)
                        <div class="relative text-center">

                            @if (! $loop->last)
                                <div
                                    class="absolute left-1/2 top-3.5 w-full h-0.5 {{ $step['done'] ? 'bg-teal' : 'bg-gray-border' }}"
                                ></div>
                            @endif

                            <div
                                class="relative z-10 mx-auto w-7 h-7 rounded-full flex items-center justify-center border
                                       {{ $step['done']
                                            ? 'bg-teal border-teal text-white'
                                            : 'bg-white border-gray-border text-navy/25' }}"
                            >
                                <x-dynamic-component
                                    :component="'lucide-' . $step['icon']"
                                    class="w-3 h-3"
                                />
                            </div>

                            <p
                                class="mt-1.5 text-[7px] min-[420px]:text-[8px] sm:text-[9px] font-medium leading-tight
                                       {{ $step['done'] ? 'text-navy/70' : 'text-navy/30' }}"
                            >
                                {{ $step['label'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>


{{-- =========================================================
    RECENTLY VIEWED
========================================================= --}}
<section class="bg-white py-5 sm:py-6 border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold tracking-[0.12em] text-teal-dark">
                    PICK UP WHERE YOU LEFT OFF
                </p>

                <p role="heading" aria-level="2" class="mt-0.5 text-[16px] sm:text-[18px] font-bold text-navy">
                    Recently Viewed
                </p>

                <p class="mt-0.5 text-[10px] sm:text-[11px] text-navy/40">
                    Continue browsing products you checked recently.
                </p>
            </div>

            <div class="hidden sm:flex gap-1.5">
                <button
                    type="button"
                    data-product-shelf-prev
                    aria-label="Previous recently viewed products"
                    class="w-8 h-8 rounded-full border border-gray-border bg-white flex items-center justify-center text-navy/50 hover:text-teal-dark hover:border-teal/30 transition"
                >
                    <x-lucide-chevron-left class="w-3.5 h-3.5" />
                </button>

                <button
                    type="button"
                    data-product-shelf-next
                    aria-label="Next recently viewed products"
                    class="w-8 h-8 rounded-full border border-gray-border bg-white flex items-center justify-center text-navy/50 hover:text-teal-dark hover:border-teal/30 transition"
                >
                    <x-lucide-chevron-right class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>


        <div data-product-shelf class="relative">
            <div
                data-product-shelf-track
                class="buyer-product-shelf grid grid-flow-col gap-2.5 sm:gap-3 overflow-x-auto scroll-smooth snap-x snap-mandatory [&::-webkit-scrollbar]:hidden"
            >
                @foreach ($recentlyViewed as $product)
                    <article class="group buyer-card snap-start overflow-hidden rounded-xl border border-gray-border bg-white hover:border-teal/35 hover:shadow-md transition-all">

                        <div class="relative aspect-4/3 overflow-hidden bg-gray-bg">
                            <a href="{{ $productHref($product) }}" class="block w-full h-full">
                                <img
                                    src="{{ $productImageUrl($product['image']) }}"
                                    alt="{{ $product['name'] }}"
                                    class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500"
                                >
                            </a>

                            @if (! empty($product['original_price']))
                                <span class="absolute left-2 top-2 rounded-md bg-teal px-2 py-1 text-[8px] font-bold text-white">
                                    SALE
                                </span>
                            @endif

                            <button
                                type="button"
                                class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full border border-black/5 bg-white/95 text-navy/55 shadow-sm hover:text-teal-dark transition"
                            >
                                <x-lucide-heart class="w-3.5 h-3.5" />
                            </button>
                        </div>

                        <div class="p-2.5">
                            <p class="text-[8.5px] sm:text-[9px] text-navy/35 truncate">
                                {{ $product['category'] }}
                            </p>

                            <a
                                href="{{ $productHref($product) }}"
                                class="mt-0.5 block text-[10px] sm:text-[11px] font-semibold text-navy line-clamp-2 leading-snug min-h-7 hover:text-teal-dark transition"
                            >
                                {{ $product['name'] }}
                            </a>

                            <div class="mt-1.5 flex items-center gap-1 text-[8px] sm:text-[8.5px] text-navy/35">
                                <span class="text-amber-400">★</span>
                                <span>{{ $product['rating'] }}</span>
                                <span>·</span>
                                <span>{{ number_format($product['sold'] ?? 0) }} sold</span>
                            </div>

                            <div class="mt-2 flex items-baseline gap-1.5">
                                <span class="text-[12px] sm:text-[13px] font-bold text-teal-dark">
                                    ₱{{ number_format($product['price']) }}
                                </span>

                                @if (! empty($product['original_price']))
                                    <span class="text-[8px] text-navy/25 line-through">
                                        ₱{{ number_format($product['original_price']) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>


{{-- =========================================================
    RECOMMENDED FOR YOU
========================================================= --}}
<section id="recommended" class="scroll-mt-20 bg-gray-bg py-5 sm:py-6 border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold tracking-[0.12em] text-teal-dark">
                    JUST FOR YOU
                </p>

                <p role="heading" aria-level="2" class="mt-0.5 text-[16px] sm:text-[18px] font-bold text-navy">
                    Recommended For You
                </p>

                <p class="mt-0.5 text-[10px] sm:text-[11px] text-navy/40">
                    Suggestions based on your shopping activity.
                </p>
            </div>

            <div class="hidden sm:flex gap-1.5">
                <button
                    type="button"
                    data-product-shelf-prev
                    aria-label="Previous recommended products"
                    class="w-8 h-8 rounded-full border border-gray-border bg-white flex items-center justify-center text-navy/50 hover:text-teal-dark hover:border-teal/30 transition"
                >
                    <x-lucide-chevron-left class="w-3.5 h-3.5" />
                </button>

                <button
                    type="button"
                    data-product-shelf-next
                    aria-label="Next recommended products"
                    class="w-8 h-8 rounded-full border border-gray-border bg-white flex items-center justify-center text-navy/50 hover:text-teal-dark hover:border-teal/30 transition"
                >
                    <x-lucide-chevron-right class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>


        <div data-product-shelf>
            <div
                data-product-shelf-track
                class="buyer-product-shelf grid grid-flow-col gap-2.5 sm:gap-3 overflow-x-auto scroll-smooth snap-x snap-mandatory [&::-webkit-scrollbar]:hidden"
            >
                @foreach ($recommendedProducts as $product)
                    <article class="group buyer-card snap-start overflow-hidden rounded-xl border border-gray-border bg-white hover:border-teal/35 hover:shadow-md transition-all">

                        <div class="relative aspect-4/3 overflow-hidden bg-gray-bg">
                            <a href="{{ $productHref($product) }}" class="block w-full h-full">
                                <img
                                    src="{{ $productImageUrl($product['image']) }}"
                                    alt="{{ $product['name'] }}"
                                    class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500"
                                >
                            </a>

                            <span class="absolute left-2 top-2 rounded-md bg-navy px-2 py-1 text-[8px] font-bold text-white">
                                FOR YOU
                            </span>

                            <button
                                type="button"
                                class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full border border-black/5 bg-white/95 text-navy/55 shadow-sm hover:text-teal-dark transition"
                            >
                                <x-lucide-heart class="w-3.5 h-3.5" />
                            </button>
                        </div>

                        <div class="p-2.5">
                            <p class="text-[8.5px] sm:text-[9px] text-navy/35 truncate">
                                {{ $product['category'] }}
                            </p>

                            <a
                                href="{{ $productHref($product) }}"
                                class="mt-0.5 block text-[10px] sm:text-[11px] font-semibold text-navy line-clamp-2 leading-snug min-h-7 hover:text-teal-dark transition"
                            >
                                {{ $product['name'] }}
                            </a>

                            <div class="mt-1.5 flex items-center gap-1 text-[8px] sm:text-[8.5px] text-navy/35">
                                <span class="text-amber-400">★</span>
                                <span>{{ $product['rating'] }}</span>
                                <span>·</span>
                                <span>{{ number_format($product['sold'] ?? 0) }} sold</span>
                            </div>

                            <div class="mt-2 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="text-[12px] sm:text-[13px] font-bold text-teal-dark">
                                        ₱{{ number_format($product['price']) }}
                                    </span>

                                    @if (! empty($product['original_price']))
                                        <span class="ml-1 text-[8px] text-navy/25 line-through">
                                            ₱{{ number_format($product['original_price']) }}
                                        </span>
                                    @endif
                                </div>

                                <button
                                    type="button"
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-teal text-white hover:bg-teal-dark transition"
                                    aria-label="Add {{ $product['name'] }} to cart"
                                >
                                    <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
