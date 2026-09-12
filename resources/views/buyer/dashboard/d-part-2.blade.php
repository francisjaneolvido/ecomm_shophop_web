{{-- Path: resources/views/buyer/dashboard/d-part-2.blade.php --}}

@php
    /*
    |--------------------------------------------------------------------------
    | CATEGORY + PRODUCT FALLBACKS
    |--------------------------------------------------------------------------
    */

    $categoryConfig = collect(config('shophop_categories', []))->keyBy('name');
    $categorySlideshows = $categoryConfig->pluck('folder', 'name')->all();

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

    if (empty($vouchers)) {
        $vouchers = [
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

    $fallbackDeals = [
        [
            'id' => 9201,
            'preview' => true,
            'name' => 'Smart CCTV Camera with Night Vision',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cctv'],
            'price' => 649,
            'original_price' => 1199,
            'rating' => 4.8,
            'reviews' => 238,
            'sold' => 1800,
        ],
        [
            'id' => 9202,
            'preview' => true,
            'name' => 'Performance Desktop CPU',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cpu'],
            'price' => 2399,
            'original_price' => 3299,
            'rating' => 4.7,
            'reviews' => 124,
            'sold' => 720,
        ],
        [
            'id' => 9203,
            'preview' => true,
            'name' => 'Family Snack Bundle',
            'category' => 'Food and Gourmet',
            'image' => $sampleImages['chips'],
            'price' => 159,
            'original_price' => 249,
            'rating' => 4.9,
            'reviews' => 508,
            'sold' => 4100,
        ],
        [
            'id' => 9204,
            'preview' => true,
            'name' => 'Modern Table Lamp',
            'category' => 'Furniture and Office Equipment',
            'image' => $sampleImages['lamp'],
            'price' => 369,
            'original_price' => 599,
            'rating' => 4.8,
            'reviews' => 187,
            'sold' => 980,
        ],
        [
            'id' => 9205,
            'preview' => true,
            'name' => 'Dual Camera Home Security Pack',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cctv'],
            'price' => 1299,
            'original_price' => 1899,
            'rating' => 4.9,
            'reviews' => 286,
            'sold' => 1100,
        ],
        [
            'id' => 9206,
            'preview' => true,
            'name' => 'Office PC Starter Tower',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cpu'],
            'price' => 2599,
            'original_price' => 3499,
            'rating' => 4.8,
            'reviews' => 103,
            'sold' => 590,
        ],
    ];

    $fallbackNew = [
        [
            'id' => 9301,
            'preview' => true,
            'name' => 'Mini Smart Security Camera',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cctv'],
            'price' => 799,
            'original_price' => null,
            'rating' => 4.8,
            'reviews' => 43,
            'sold' => 160,
        ],
        [
            'id' => 9302,
            'preview' => true,
            'name' => 'Compact PC Tower',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cpu'],
            'price' => 2799,
            'original_price' => null,
            'rating' => 4.7,
            'reviews' => 31,
            'sold' => 95,
        ],
        [
            'id' => 9303,
            'preview' => true,
            'name' => 'New Flavor Snack Pack',
            'category' => 'Food and Gourmet',
            'image' => $sampleImages['chips'],
            'price' => 199,
            'original_price' => null,
            'rating' => 4.9,
            'reviews' => 72,
            'sold' => 230,
        ],
        [
            'id' => 9304,
            'preview' => true,
            'name' => 'Ambient Table Lamp',
            'category' => 'Furniture and Office Equipment',
            'image' => $sampleImages['lamp'],
            'price' => 429,
            'original_price' => null,
            'rating' => 4.8,
            'reviews' => 39,
            'sold' => 120,
        ],
        [
            'id' => 9305,
            'preview' => true,
            'name' => 'Wireless Indoor CCTV',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cctv'],
            'price' => 899,
            'original_price' => null,
            'rating' => 4.9,
            'reviews' => 58,
            'sold' => 190,
        ],
        [
            'id' => 9306,
            'preview' => true,
            'name' => 'Everyday Workstation CPU',
            'category' => 'Electronics and Gadgets',
            'image' => $sampleImages['cpu'],
            'price' => 2999,
            'original_price' => null,
            'rating' => 4.8,
            'reviews' => 47,
            'sold' => 130,
        ],
    ];

    if (empty($dealProducts)) {
        $dealProducts = $fallbackDeals;
    }

    if (empty($newArrivals)) {
        $newArrivals = $fallbackNew;
    }

    $moreToLove = collect($dealProducts)
        ->concat($newArrivals)
        ->unique('id')
        ->take(8)
        ->values()
        ->all();
@endphp


{{-- =========================================================
    SHOP BY CATEGORY
========================================================= --}}
<section id="categories" class="scroll-mt-20 bg-white py-5 sm:py-6 border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">
                    EXPLORE
                </p>

                <p role="heading" aria-level="2" class="mt-0.5 text-[16px] sm:text-[18px] font-bold text-navy">
                    Shop by Category
                </p>

                <p class="mt-0.5 text-[10px] sm:text-[11px] text-navy/40">
                    Browse departments with real category imagery.
                </p>
            </div>

            <span class="hidden sm:inline-flex text-[9.5px] text-navy/30">
                Swipe or use arrows
            </span>
        </div>


        <div class="relative buyer-reveal" data-category-slider>

            <button
                type="button"
                data-category-prev
                aria-label="Previous categories"
                class="hidden absolute left-0 top-1/2 -translate-x-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white border border-gray-border shadow-md items-center justify-center text-navy hover:text-teal-dark hover:border-teal/30 transition"
            >
                <x-lucide-chevron-left class="w-3.5 h-3.5" />
            </button>


            <div
                data-category-track
                class="grid grid-flow-col gap-2.5 overflow-x-auto scroll-smooth snap-x snap-mandatory [&::-webkit-scrollbar]:hidden
                       auto-cols-[calc((100%-0.75rem)/2)]
                       sm:auto-cols-[calc((100%-1.5rem)/3)]
                       md:auto-cols-[calc((100%-3rem)/5)]
                       lg:auto-cols-[calc((100%-3.75rem)/6)]
                       xl:auto-cols-[calc((100%-5.25rem)/8)]"
            >
                @forelse ($categories as $category)

                    @php
                        $folder = $categorySlideshows[$category['name']] ?? null;
                        $slideImages = [];

                        if ($folder) {
                            $folderPath = public_path('images/category_icons_bg/' . $folder);

                            if (is_dir($folderPath)) {
                                $allFiles = glob(
                                    $folderPath . '/*.{jpg,jpeg,png,webp,avif,gif}',
                                    GLOB_BRACE
                                );

                                $slideImages = array_map(
                                    'basename',
                                    array_slice($allFiles, 0, 6)
                                );
                            }
                        }

                        $imageCount = count($slideImages);
                        $slideInterval = $imageCount > 0 ? 12 / $imageCount : 0;
                    @endphp


                    <a
                        href="{{ route('buyer.category.show', $categoryConfig[$category['name']]['slug'] ?? \Illuminate\Support\Str::slug($category['name'])) }}"
                        class="group relative min-h-30 sm:min-h-32 overflow-hidden rounded-2xl border border-gray-border bg-gray-bg p-3 text-center flex flex-col items-center justify-end snap-start hover:border-teal/35 hover:-translate-y-0.5 hover:shadow-md transition-all"
                    >
                        @if ($imageCount > 0)
                            <div class="kb-stack z-0">
                                @foreach ($slideImages as $i => $file)
                                    <img
                                        src="{{ asset('images/category_icons_bg/' . $folder . '/' . $file) }}"
                                        alt=""
                                        style="animation-delay: {{ $i * $slideInterval }}s, {{ $i * $slideInterval }}s; animation-duration: 12s, {{ 12 * $imageCount }}s;"
                                    >
                                @endforeach
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-white via-white/78 to-white/20 group-hover:via-white/68 transition"></div>

                        <div class="relative z-10 flex w-full flex-col items-center">
                            <div class="w-9 h-9 rounded-xl bg-white shadow-sm text-teal-dark flex items-center justify-center group-hover:bg-teal group-hover:text-white transition">
                                <x-dynamic-component
                                    :component="'lucide-' . $category['icon']"
                                    class="w-3.5 h-3.5"
                                />
                            </div>

                            <p class="mt-2 text-[9.5px] sm:text-[10.5px] font-semibold text-navy leading-snug line-clamp-2">
                                {{ $category['name'] }}
                            </p>
                        </div>
                    </a>

                @empty
                    <div class="col-span-full py-8 text-center text-sm text-navy/40">
                        No categories available yet.
                    </div>
                @endforelse
            </div>


            <button
                type="button"
                data-category-next
                aria-label="Next categories"
                class="absolute right-0 top-1/2 translate-x-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white border border-gray-border shadow-md flex items-center justify-center text-navy hover:text-teal-dark hover:border-teal/30 transition"
            >
                <x-lucide-chevron-right class="w-3.5 h-3.5" />
            </button>
        </div>
    </div>
</section>


{{-- =========================================================
    VOUCHER CENTER
========================================================= --}}
<section id="vouchers" class="scroll-mt-20 bg-gray-bg py-5 sm:py-6 border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#E9F8F4] via-white to-[#EEF8FC] border border-teal/10 p-4 sm:p-5 buyer-reveal">

            <div class="pointer-events-none absolute -right-20 -top-24 w-56 h-56 rounded-full bg-teal/10 blur-2xl"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center gap-4">

                <div class="lg:w-56 shrink-0">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal text-white">
                        <x-lucide-ticket-percent class="w-4 h-4" />
                    </div>

                    <p class="mt-3 text-[9px] font-bold uppercase tracking-[0.12em] text-teal-dark">
                        SHOPHOP VOUCHERS
                    </p>

                    <p class="mt-0.5 text-[17px] font-bold text-navy">
                        Save before checkout
                    </p>

                    <p class="mt-1 text-[9.5px] leading-relaxed text-navy/45">
                        Claim shipping and discount vouchers before placing your next order.
                    </p>
                </div>


                <div class="grid sm:grid-cols-3 gap-2.5 flex-1">
                    @foreach ($vouchers as $voucher)
                        <div class="buyer-voucher-card relative overflow-hidden rounded-xl border border-teal/15 bg-white p-3 shadow-sm">

                            <div class="flex items-start gap-2.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-light text-teal-dark">
                                    <x-dynamic-component
                                        :component="'lucide-' . $voucher['icon']"
                                        class="w-3.5 h-3.5"
                                    />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-[10.5px] sm:text-[11.5px] font-bold text-navy">
                                        {{ $voucher['title'] }}
                                    </p>

                                    <p class="mt-0.5 text-[8.5px] text-navy/40 line-clamp-2">
                                        {{ $voucher['description'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-2 border-t border-dashed border-gray-border pt-2.5">
                                <span class="rounded-md bg-teal-light px-2 py-1 font-mono text-[8.5px] font-bold text-teal-dark">
                                    {{ $voucher['code'] }}
                                </span>

                                <button
                                    type="button"
                                    class="text-[8.5px] font-bold text-teal-dark hover:text-navy transition"
                                >
                                    Claim
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>


{{-- =========================================================
    DEALS FOR YOU
========================================================= --}}
<section id="deals" class="scroll-mt-20 bg-white py-5 sm:py-6 border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.12em] text-coral">
                    SAVE MORE
                </p>

                <p role="heading" aria-level="2" class="mt-0.5 text-[16px] sm:text-[18px] font-bold text-navy">
                    Deals For You
                </p>

                <p class="mt-0.5 text-[10px] sm:text-[11px] text-navy/40">
                    Limited-time offers selected for ShopHop buyers.
                </p>
            </div>

            <div class="hidden sm:flex gap-1.5">
                <button
                    type="button"
                    data-product-shelf-prev
                    aria-label="Previous deals"
                    class="w-8 h-8 rounded-full border border-gray-border bg-white flex items-center justify-center text-navy/50 hover:text-teal-dark hover:border-teal/30 transition"
                >
                    <x-lucide-chevron-left class="w-3.5 h-3.5" />
                </button>

                <button
                    type="button"
                    data-product-shelf-next
                    aria-label="Next deals"
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
                @foreach ($dealProducts as $product)
                    @php
                        $discount = ! empty($product['original_price']) && $product['original_price'] > 0
                            ? max(1, (int) round((1 - ($product['price'] / $product['original_price'])) * 100))
                            : 15;
                    @endphp

                    <article class="group snap-start overflow-hidden rounded-xl border border-gray-border bg-white hover:border-teal/35 hover:shadow-md transition-all">

                        <div class="relative aspect-[4/3] overflow-hidden bg-gray-bg">
                            <a href="{{ $productHref($product) }}" class="block w-full h-full">
                                <img
                                    src="{{ $productImageUrl($product['image']) }}"
                                    alt="{{ $product['name'] }}"
                                    class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500"
                                >
                            </a>

                            <span class="absolute left-2 top-2 rounded-md bg-coral px-2 py-1 text-[8px] font-bold text-white">
                                -{{ $discount }}%
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
    NEW ARRIVALS
========================================================= --}}
<section id="new-arrivals" class="scroll-mt-20 bg-gray-bg py-5 sm:py-6 border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">
                    JUST IN
                </p>

                <p role="heading" aria-level="2" class="mt-0.5 text-[16px] sm:text-[18px] font-bold text-navy">
                    New Arrivals
                </p>

                <p class="mt-0.5 text-[10px] sm:text-[11px] text-navy/40">
                    Fresh products recently added to ShopHop.
                </p>
            </div>

            <div class="hidden sm:flex gap-1.5">
                <button
                    type="button"
                    data-product-shelf-prev
                    aria-label="Previous new arrivals"
                    class="w-8 h-8 rounded-full border border-gray-border bg-white flex items-center justify-center text-navy/50 hover:text-teal-dark hover:border-teal/30 transition"
                >
                    <x-lucide-chevron-left class="w-3.5 h-3.5" />
                </button>

                <button
                    type="button"
                    data-product-shelf-next
                    aria-label="Next new arrivals"
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
                @foreach ($newArrivals as $product)
                    <article class="group snap-start overflow-hidden rounded-xl border border-gray-border bg-white hover:border-teal/35 hover:shadow-md transition-all">

                        <div class="relative aspect-[4/3] overflow-hidden bg-gray-bg">
                            <a href="{{ $productHref($product) }}" class="block w-full h-full">
                                <img
                                    src="{{ $productImageUrl($product['image']) }}"
                                    alt="{{ $product['name'] }}"
                                    class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500"
                                >
                            </a>

                            <span class="absolute left-2 top-2 rounded-md bg-teal px-2 py-1 text-[8px] font-bold text-white">
                                NEW
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
                                <span class="text-[12px] sm:text-[13px] font-bold text-navy">
                                    ₱{{ number_format($product['price']) }}
                                </span>

                                <button
                                    type="button"
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-teal text-white hover:bg-teal-dark transition"
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


{{-- =========================================================
    MORE TO LOVE
========================================================= --}}
<section class="bg-white py-5 sm:py-6 border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-3">
            <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">
                DISCOVER MORE
            </p>

            <p role="heading" aria-level="2" class="mt-0.5 text-[16px] sm:text-[18px] font-bold text-navy">
                More to Love
            </p>

            <p class="mt-0.5 text-[10px] sm:text-[11px] text-navy/40">
                A fuller marketplace grid while your real catalog grows.
            </p>
        </div>


        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3 buyer-stagger">
            @foreach ($moreToLove as $product)
                <article class="group overflow-hidden rounded-xl border border-gray-border bg-white hover:border-teal/35 hover:-translate-y-0.5 hover:shadow-md transition-all">

                    <div class="relative aspect-[4/3] overflow-hidden bg-gray-bg">
                        <a href="{{ $productHref($product) }}" class="block w-full h-full">
                            <img
                                src="{{ $productImageUrl($product['image']) }}"
                                alt="{{ $product['name'] }}"
                                class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500"
                            >
                        </a>

                        <button
                            type="button"
                            class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full border border-black/5 bg-white/95 text-navy/55 shadow-sm hover:text-teal-dark transition"
                        >
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <div class="p-2.5">
                        <p class="text-[8.5px] text-navy/35 truncate">
                            {{ $product['category'] }}
                        </p>

                        <a
                            href="{{ $productHref($product) }}"
                            class="mt-0.5 block text-[10px] sm:text-[11px] font-semibold text-navy line-clamp-2 leading-snug min-h-7 hover:text-teal-dark transition"
                        >
                            {{ $product['name'] }}
                        </a>

                        <div class="mt-2 flex items-end justify-between gap-2">
                            <div>
                                <p class="text-[12px] sm:text-[13px] font-bold text-teal-dark">
                                    ₱{{ number_format($product['price']) }}
                                </p>

                                <p class="mt-0.5 text-[8px] text-navy/30">
                                    {{ number_format($product['sold'] ?? 0) }} sold
                                </p>
                            </div>

                            <button
                                type="button"
                                class="inline-flex h-7 items-center gap-1 rounded-lg bg-teal-light px-2 text-[8.5px] font-semibold text-teal-dark hover:bg-teal hover:text-white transition"
                            >
                                <x-lucide-shopping-cart class="w-3 h-3" />
                                Add
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>


{{-- =========================================================
    TRUST + SHORTCUT CTA
========================================================= --}}
<section class="bg-gray-bg py-5 sm:py-6">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid sm:grid-cols-3 gap-2.5 mb-3 buyer-stagger">

            <div class="rounded-2xl border border-gray-border bg-white p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-light text-teal-dark">
                        <x-lucide-shield-check class="w-4 h-4" />
                    </div>

                    <div>
                        <p class="text-[10.5px] font-bold text-navy">Buyer Protection</p>
                        <p class="mt-1 text-[8.5px] leading-relaxed text-navy/40">
                            Shop with clearer order tracking and account protection.
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-border bg-white p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-light text-teal-dark">
                        <x-lucide-package-check class="w-4 h-4" />
                    </div>

                    <div>
                        <p class="text-[10.5px] font-bold text-navy">Easy Order Tracking</p>
                        <p class="mt-1 text-[8.5px] leading-relaxed text-navy/40">
                            Follow your purchase from seller confirmation to delivery.
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-border bg-white p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-light text-teal-dark">
                        <x-lucide-badge-percent class="w-4 h-4" />
                    </div>

                    <div>
                        <p class="text-[10.5px] font-bold text-navy">Daily Savings</p>
                        <p class="mt-1 text-[8.5px] leading-relaxed text-navy/40">
                            Vouchers, markdowns, and seller deals in one marketplace.
                        </p>
                    </div>
                </div>
            </div>
        </div>


        <div class="relative overflow-hidden rounded-2xl bg-navy p-5 sm:p-6 buyer-reveal">

            <img
                src="{{ asset($sampleImages['lamp']) }}"
                alt=""
                class="absolute inset-y-0 right-0 hidden sm:block w-[42%] h-full object-cover opacity-25"
            >

            <div class="absolute inset-0 bg-gradient-to-r from-navy via-navy/95 to-navy/55"></div>

            <div class="relative max-w-xl">
                <p class="text-[9px] font-bold uppercase tracking-[0.13em] text-teal">
                    YOUR SHOPHOP
                </p>

                <p class="mt-1 text-[20px] sm:text-[24px] font-bold leading-tight text-white">
                    Everything you need, one hop away.
                </p>

                <p class="mt-2 text-[9.5px] sm:text-[10.5px] leading-relaxed text-white/55">
                    Manage orders, revisit favorites, claim vouchers, and keep discovering products from one dashboard.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a
                        href="#my-orders"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-teal px-3 py-2 text-[9.5px] font-semibold text-white hover:bg-teal-dark transition"
                    >
                        <x-lucide-package class="w-3.5 h-3.5" />
                        My Orders
                    </a>

                    <a
                        href="{{ Route::has('buyer.profile') ? route('buyer.profile') : '#' }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-[9.5px] font-semibold text-white/80 hover:bg-white/15 hover:text-white transition"
                    >
                        <x-lucide-user-cog class="w-3.5 h-3.5" />
                        Manage Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
