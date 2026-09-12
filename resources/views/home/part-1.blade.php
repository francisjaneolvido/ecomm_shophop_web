{{-- =========================================================
    SHOPHOP LANDING — PART 1
    Public discovery experience:
    brand hero + marketplace mosaic + categories
========================================================= --}}

@php
    /*
    |--------------------------------------------------------------------------
    | LANDING PAGE CATEGORY SOURCE
    |--------------------------------------------------------------------------
    | Prefer the shared master config so the public landing page stays aligned
    | with buyer/seller category choices. A fallback list is kept for safety.
    */

    $landingIconMap = [
        'Pet Supplies' => 'paw-print',
        'Electronics and Gadgets' => 'smartphone',
        'Women\'s Apparel' => 'shirt',
        'Men\'s Apparel' => 'shirt',
        'Kids and Baby' => 'baby',
        'Home and Garden' => 'house',
        'Sports and Outdoors' => 'dumbbell',
        'Health and Beauty' => 'sparkles',
        'Books and Media' => 'book-open',
        'Food and Gourmet' => 'utensils',
        'Automotive & Motorcycle' => 'car',
        'Furniture and Office Equipment' => 'armchair',
        'Jewelry and Watches' => 'gem',
        'Office and School Supplies' => 'notebook-pen',
    ];

    $landingFallbackFolders = [
        'Pet Supplies' => 'pet_supplies',
        'Electronics and Gadgets' => 'electronics_gadgets',
        'Women\'s Apparel' => 'women_apparel',
        'Men\'s Apparel' => 'men_apparel',
        'Kids and Baby' => 'kids_baby',
        'Home and Garden' => 'home_garden',
        'Sports and Outdoors' => 'sports_outdoors',
        'Health and Beauty' => 'health_beauty',
        'Books and Media' => 'books_media',
        'Food and Gourmet' => 'food_gourmet',
        'Automotive & Motorcycle' => 'automotive_motorcycle',
        'Furniture and Office Equipment' => 'furniture_office',
        'Jewelry and Watches' => 'jewelry_watches',
        'Office and School Supplies' => 'office_schoolsupplies',
    ];

    $landingCategories = collect(config('shophop_categories', []))
        ->map(function ($category) use ($landingIconMap, $landingFallbackFolders) {
            $name = $category['name'] ?? 'ShopHop Category';

            return [
                'name' => $name,
                'slug' => $category['slug'] ?? \Illuminate\Support\Str::slug($name),
                'folder' => $category['folder'] ?? ($landingFallbackFolders[$name] ?? null),
                'icon' => $category['icon'] ?? ($landingIconMap[$name] ?? 'shopping-bag'),
            ];
        });

    if ($landingCategories->isEmpty()) {
        $landingCategories = collect($landingIconMap)
            ->map(function ($icon, $name) use ($landingFallbackFolders) {
                return [
                    'name' => $name,
                    'slug' => \Illuminate\Support\Str::slug($name),
                    'folder' => $landingFallbackFolders[$name] ?? null,
                    'icon' => $icon,
                ];
            })
            ->values();
    }

    $landingHeroTiles = [
        [
            'image' => 'images/category_icons_bg/electronics_gadgets/cctv.jpg',
            'label' => 'Smart Home',
            'caption' => 'Everyday tech finds',
            'accent' => 'bg-teal',
        ],
        [
            'image' => 'images/category_icons_bg/electronics_gadgets/cpu.jpg',
            'label' => 'Tech & Work',
            'caption' => 'Build your setup',
            'accent' => 'bg-sky',
        ],
        [
            'image' => 'images/category_icons_bg/food_gourmet/chips.jpg',
            'label' => 'Food & Snacks',
            'caption' => 'Easy everyday treats',
            'accent' => 'bg-coral',
        ],
        [
            'image' => 'images/category_icons_bg/furniture_office/tablelamps.jpg',
            'label' => 'Home Finds',
            'caption' => 'Refresh your space',
            'accent' => 'bg-yellow',
        ],
    ];
@endphp


{{-- =========================================================
    PUBLIC SERVICE STRIP
========================================================= --}}
<section class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-5 sm:gap-8 overflow-x-auto py-2.5 [&::-webkit-scrollbar]:hidden">

            <span class="shrink-0 inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-medium text-navy/50">
                <x-lucide-shield-check class="w-3.5 h-3.5 text-teal-dark" />
                Buyer-friendly shopping
            </span>

            <span class="shrink-0 inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-medium text-navy/50">
                <x-lucide-layout-grid class="w-3.5 h-3.5 text-teal-dark" />
                Multi-category marketplace
            </span>

            <span class="shrink-0 inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-medium text-navy/50">
                <x-lucide-badge-percent class="w-3.5 h-3.5 text-teal-dark" />
                Deals and vouchers
            </span>

            <span class="shrink-0 inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-medium text-navy/50">
                <x-lucide-package-check class="w-3.5 h-3.5 text-teal-dark" />
                Order tracking after checkout
            </span>

        </div>
    </div>
</section>


{{-- =========================================================
    HERO — BRAND / PUBLIC DISCOVERY
========================================================= --}}
<section class="relative overflow-hidden bg-gray-bg border-b border-gray-border/70" data-parallax-container>

    <div class="pointer-events-none absolute -top-28 -right-20 w-80 h-80 rounded-full bg-teal/10 blur-3xl" data-parallax="12"></div>
    <div class="pointer-events-none absolute -bottom-36 -left-28 w-96 h-96 rounded-full bg-navy/5 blur-3xl" data-parallax="-10"></div>

    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 lg:py-10">

        <div class="grid lg:grid-cols-[0.86fr_1.14fr] gap-6 lg:gap-8 items-center">

            {{-- LEFT: PUBLIC BRAND MESSAGE --}}
            <div class="max-w-xl">

                <div
                    style="--stagger-index: 0;"
                    class="stagger-item inline-flex items-center gap-2 rounded-full bg-white border border-teal/15 px-3 py-1.5 shadow-sm"
                >
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-teal opacity-35 landing-ping"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-teal"></span>
                    </span>

                    <span class="text-[9.5px] sm:text-[10.5px] font-semibold text-teal-dark">
                        Welcome to ShopHop
                    </span>
                </div>


                <h1
                    style="--stagger-index: 1;"
                    class="stagger-item mt-4 text-[32px] sm:text-[39px] lg:text-[46px] xl:text-[50px] font-extrabold leading-[1.02] tracking-[-0.04em] text-navy"
                >
                    Everything you love,
                    <span class="block text-teal">
                        just a hop away.
                    </span>
                </h1>


                <p
                    style="--stagger-index: 2;"
                    class="stagger-item mt-4 max-w-lg text-[11.5px] sm:text-[13px] lg:text-sm leading-relaxed text-navy/55"
                >
                    Explore everyday essentials, trending finds, food, tech, fashion,
                    home picks, and more — all inside one growing marketplace.
                </p>


                <div
                    style="--stagger-index: 3;"
                    class="stagger-item mt-5 sm:mt-6 flex flex-col sm:flex-row gap-2.5"
                >
                    <a
                        href="#trending"
                        class="group ripple-surface inline-flex items-center justify-center gap-2 rounded-xl bg-teal px-5 py-2.5 sm:py-3 text-[11px] sm:text-xs font-bold text-white shadow-lg shadow-teal/15 hover:bg-teal-dark hover:-translate-y-0.5 transition-all"
                    >
                        Start Exploring

                        <x-lucide-arrow-right class="w-4 h-4 transition-transform group-hover:translate-x-1" />
                    </a>

                    <a
                        href="#categories"
                        class="group inline-flex items-center justify-center gap-2 rounded-xl border border-gray-border bg-white px-5 py-2.5 sm:py-3 text-[11px] sm:text-xs font-semibold text-navy hover:text-teal-dark hover:border-teal/30 hover:-translate-y-0.5 transition-all"
                    >
                        <x-lucide-layout-grid class="w-4 h-4" />
                        Browse Categories
                    </a>
                </div>


                {{-- Distinct from buyer dashboard: public marketplace promises, not account stats --}}
                <div
                    style="--stagger-index: 4;"
                    class="stagger-item mt-6 sm:mt-7 grid grid-cols-3 gap-2.5 max-w-xl"
                >
                    <div class="rounded-xl border border-gray-border bg-white/80 px-3 py-3 backdrop-blur">
                        <x-lucide-store class="w-4 h-4 text-teal-dark" />
                        <p class="mt-2 text-[9.5px] sm:text-[10.5px] font-semibold text-navy">
                            Marketplace
                        </p>
                        <p class="mt-0.5 text-[8px] sm:text-[9px] text-navy/35">
                            Different shops, one place
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-border bg-white/80 px-3 py-3 backdrop-blur">
                        <x-lucide-tags class="w-4 h-4 text-teal-dark" />
                        <p class="mt-2 text-[9.5px] sm:text-[10.5px] font-semibold text-navy">
                            More choices
                        </p>
                        <p class="mt-0.5 text-[8px] sm:text-[9px] text-navy/35">
                            Browse across categories
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-border bg-white/80 px-3 py-3 backdrop-blur">
                        <x-lucide-shopping-bag class="w-4 h-4 text-teal-dark" />
                        <p class="mt-2 text-[9.5px] sm:text-[10.5px] font-semibold text-navy">
                            Easy discovery
                        </p>
                        <p class="mt-0.5 text-[8px] sm:text-[9px] text-navy/35">
                            Find your next favorite
                        </p>
                    </div>
                </div>
            </div>


            {{-- RIGHT: MARKETPLACE PHOTO MOSAIC --}}
            <div
                style="--stagger-index: 2;"
                class="stagger-item relative min-h-[390px] sm:min-h-[450px] lg:min-h-[490px]"
            >
                <div class="absolute inset-[8%] rounded-[2.4rem] bg-teal/10 blur-3xl"></div>

                <div class="absolute left-[5%] top-[5%] w-[55%] h-[61%] overflow-hidden rounded-[1.8rem] border border-white bg-white shadow-[0_24px_60px_rgba(15,44,63,0.14)] landing-float-a">
                    <img
                        src="{{ asset($landingHeroTiles[0]['image']) }}"
                        alt="{{ $landingHeroTiles[0]['label'] }}"
                        class="w-full h-full object-cover"
                    >

                    <div class="absolute inset-0 bg-gradient-to-t from-navy/80 via-transparent to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
                        <span class="inline-flex rounded-md {{ $landingHeroTiles[0]['accent'] }} px-2 py-1 text-[8px] font-bold text-white">
                            TRENDING
                        </span>

                        <p class="mt-2 text-[14px] sm:text-[16px] font-bold text-white">
                            {{ $landingHeroTiles[0]['label'] }}
                        </p>

                        <p class="mt-0.5 text-[9px] text-white/55">
                            {{ $landingHeroTiles[0]['caption'] }}
                        </p>
                    </div>
                </div>


                <div class="absolute right-[3%] top-[2%] w-[34%] h-[40%] overflow-hidden rounded-[1.5rem] border border-white bg-white shadow-[0_18px_46px_rgba(15,44,63,0.12)] landing-float-b">
                    <img
                        src="{{ asset($landingHeroTiles[1]['image']) }}"
                        alt="{{ $landingHeroTiles[1]['label'] }}"
                        class="w-full h-full object-cover"
                    >

                    <div class="absolute inset-0 bg-gradient-to-t from-navy/75 via-transparent to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 p-3">
                        <p class="text-[11px] sm:text-[12px] font-bold text-white">
                            {{ $landingHeroTiles[1]['label'] }}
                        </p>
                    </div>
                </div>


                <div class="absolute left-[17%] bottom-[3%] w-[34%] h-[31%] overflow-hidden rounded-[1.5rem] border border-white bg-white shadow-[0_18px_46px_rgba(15,44,63,0.12)] landing-float-c">
                    <img
                        src="{{ asset($landingHeroTiles[2]['image']) }}"
                        alt="{{ $landingHeroTiles[2]['label'] }}"
                        class="w-full h-full object-cover"
                    >

                    <div class="absolute inset-0 bg-gradient-to-t from-navy/75 via-transparent to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 p-3">
                        <p class="text-[11px] sm:text-[12px] font-bold text-white">
                            {{ $landingHeroTiles[2]['label'] }}
                        </p>
                    </div>
                </div>


                <div class="absolute right-[3%] bottom-[5%] w-[41%] h-[46%] overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-[0_20px_52px_rgba(15,44,63,0.13)] landing-float-d">
                    <img
                        src="{{ asset($landingHeroTiles[3]['image']) }}"
                        alt="{{ $landingHeroTiles[3]['label'] }}"
                        class="w-full h-full object-cover"
                    >

                    <div class="absolute inset-0 bg-gradient-to-t from-navy/80 via-transparent to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 p-4">
                        <span class="inline-flex rounded-md bg-teal px-2 py-1 text-[8px] font-bold text-white">
                            FRESH FIND
                        </span>

                        <p class="mt-2 text-[12px] sm:text-[14px] font-bold text-white">
                            {{ $landingHeroTiles[3]['label'] }}
                        </p>
                    </div>
                </div>


                <div class="absolute z-20 left-[45%] top-[43%] rounded-xl border border-white bg-white/95 px-3 py-2.5 shadow-xl backdrop-blur landing-chip">
                    <div class="flex items-center gap-2">
                        <span class="flex w-7 h-7 items-center justify-center rounded-lg bg-teal-light text-teal-dark">
                            <x-lucide-sparkles class="w-3.5 h-3.5" />
                        </span>

                        <div>
                            <p class="text-[8px] text-navy/35">
                                Discover
                            </p>
                            <p class="text-[9.5px] font-bold text-navy">
                                Something new
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- =========================================================
    CATEGORY DISCOVERY
========================================================= --}}
<section id="categories" class="scroll-mt-20 bg-white py-7 sm:py-8 lg:py-9 border-b border-gray-border/70">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-4 reveal-up">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">
                    EXPLORE SHOPHOP
                </p>

                <h2 class="mt-0.5 text-[19px] sm:text-[21px] lg:text-[23px] font-bold text-navy">
                    Shop by Category
                </h2>

                <p class="mt-1 text-[10px] sm:text-[11px] text-navy/45">
                    Start broad, then find the products that fit your day.
                </p>
            </div>

            <span class="hidden sm:inline-flex text-[9.5px] text-navy/30">
                Swipe or use arrows
            </span>
        </div>


        <div class="relative" data-landing-category-slider>

            <button
                type="button"
                data-landing-category-prev
                aria-label="Previous categories"
                class="hidden absolute left-0 top-1/2 -translate-x-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white border border-gray-border shadow-lg items-center justify-center text-navy hover:text-teal-dark hover:border-teal/30 transition"
            >
                <x-lucide-chevron-left class="w-4 h-4" />
            </button>


            <div
                data-landing-category-track
                class="grid grid-flow-col gap-2.5 overflow-x-auto scroll-smooth snap-x snap-mandatory [&::-webkit-scrollbar]:hidden
                       auto-cols-[calc((100%-0.75rem)/2)]
                       sm:auto-cols-[calc((100%-1.5rem)/3)]
                       md:auto-cols-[calc((100%-3rem)/5)]
                       lg:auto-cols-[calc((100%-3.75rem)/6)]
                       xl:auto-cols-[calc((100%-5.25rem)/8)]"
            >
                @foreach ($landingCategories as $category)

                    @php
                        $slideImages = [];

                        if (! empty($category['folder'])) {
                            $folderPath = public_path('images/category_icons_bg/' . $category['folder']);

                            if (is_dir($folderPath)) {
                                $allFiles = glob(
                                    $folderPath . '/*.{jpg,jpeg,png,webp,avif,gif}',
                                    GLOB_BRACE
                                );

                                $slideImages = array_map(
                                    'basename',
                                    array_slice($allFiles, 0, 5)
                                );
                            }
                        }

                        $imageCount = count($slideImages);
                        $slideInterval = $imageCount > 0 ? 12 / $imageCount : 0;
                    @endphp


                    <a
                        href="#trending"
                        style="--stagger-index: {{ $loop->index % 8 }};"
                        class="group stagger-item relative min-h-31 sm:min-h-33 overflow-hidden rounded-2xl border border-gray-border bg-gray-bg snap-start hover:border-teal/35 hover:-translate-y-0.5 hover:shadow-lg transition-all"
                    >

                        @if ($imageCount > 0)
                            <div class="absolute inset-0">
                                @foreach ($slideImages as $i => $file)
                                    <img
                                        src="{{ asset('images/category_icons_bg/' . $category['folder'] . '/' . $file) }}"
                                        alt=""
                                        class="landing-category-slide"
                                        style="
                                            animation-delay: {{ $i * $slideInterval }}s;
                                            animation-duration: {{ 12 * max(1, $imageCount) }}s;
                                        "
                                    >
                                @endforeach
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-white via-white/82 to-white/30 group-hover:via-white/72 transition"></div>

                        <div class="relative z-10 h-full min-h-31 sm:min-h-33 p-3 flex flex-col items-center justify-end text-center">
                            <span class="flex w-9 h-9 items-center justify-center rounded-xl bg-white text-teal-dark shadow-sm group-hover:bg-teal group-hover:text-white transition">
                                <x-dynamic-component
                                    :component="'lucide-' . $category['icon']"
                                    class="w-3.5 h-3.5"
                                />
                            </span>

                            <p class="mt-2 text-[9.5px] sm:text-[10.5px] font-semibold text-navy leading-snug line-clamp-2">
                                {{ $category['name'] }}
                            </p>
                        </div>
                    </a>

                @endforeach
            </div>


            <button
                type="button"
                data-landing-category-next
                aria-label="Next categories"
                class="absolute right-0 top-1/2 translate-x-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white border border-gray-border shadow-lg flex items-center justify-center text-navy hover:text-teal-dark hover:border-teal/30 transition"
            >
                <x-lucide-chevron-right class="w-4 h-4" />
            </button>

        </div>
    </div>
</section>
