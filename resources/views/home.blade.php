{{-- resources/views/home.blade.php --}}

@extends('layouts.app')

@section('title', 'ShopHop — Hop In. Shop More.')

@section('content')


{{-- =========================================================
    HERO SECTION
========================================================= --}}
<section class="relative overflow-hidden bg-gray-bg">

    {{-- Decorative background circles --}}
    <div class="absolute -top-28 -right-28 w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-teal/10"></div>
    <div class="absolute -bottom-36 -left-36 w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-teal/5"></div>


    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-24">

        <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">


            {{-- =================================================
                LEFT CONTENT
            ================================================== --}}
            <div class="max-w-xl">


                {{-- Small badge --}}
                <div
                    class="inline-flex items-center gap-2
                           bg-teal-light text-teal-dark
                           px-3.5 sm:px-4 py-2
                           rounded-full
                           text-xs sm:text-sm font-medium
                           mb-5 sm:mb-6"
                >
                    <span class="w-2 h-2 rounded-full bg-teal"></span>

                    New arrivals every day
                </div>


                {{-- Heading --}}
                <h1 class="text-navy mb-5 sm:mb-6">

                    Everything You Love,

                    <span class="block text-teal">
                        Just a Hop Away.
                    </span>

                </h1>


                {{-- Description --}}
                <p
                    class="text-navy/65
                           text-sm sm:text-base lg:text-lg
                           leading-relaxed
                           max-w-lg
                           mb-7 sm:mb-8"
                >
                    Discover everyday essentials, trending finds,
                    and products you'll love — all in one place.
                </p>


                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">

                    <a
                        href="#trending"
                        class="inline-flex items-center justify-center gap-2
                               bg-teal hover:bg-teal-dark
                               text-white
                               text-sm font-semibold
                               px-6 sm:px-7
                               py-3 sm:py-3.5
                               rounded-full
                               transition-all duration-300
                               hover:-translate-y-0.5
                               shadow-lg shadow-teal/20"
                    >

                        Shop Now

                        <x-lucide-arrow-right class="w-4 h-4" />

                    </a>


                    <a
                        href="#categories"
                        class="inline-flex items-center justify-center
                               border-2 border-navy
                               text-navy
                               hover:bg-navy hover:text-white
                               text-sm font-semibold
                               px-6 sm:px-7 py-3
                               rounded-full
                               transition-all duration-300"
                    >
                        Explore Categories
                    </a>

                </div>


                {{-- =================================================
                    STATS
                ================================================== --}}
                <div
                    class="grid grid-cols-3
                           gap-3 sm:gap-6
                           mt-10 sm:mt-12
                           pt-7 sm:pt-8
                           border-t border-navy/10"
                >

                    <div>

                        <div class="text-xl sm:text-2xl font-bold text-navy">
                            50K+
                        </div>

                        <div class="text-[10px] sm:text-xs text-navy/55 mt-1">
                            Products
                        </div>

                    </div>


                    <div class="border-l border-navy/10 pl-3 sm:pl-6">

                        <div class="text-xl sm:text-2xl font-bold text-navy">
                            2M+
                        </div>

                        <div class="text-[10px] sm:text-xs text-navy/55 mt-1">
                            Happy Shoppers
                        </div>

                    </div>


                    <div class="border-l border-navy/10 pl-3 sm:pl-6">

                        <div class="text-xl sm:text-2xl font-bold text-navy">
                            4.9
                        </div>

                        <div class="text-[10px] sm:text-xs text-navy/55 mt-1">
                            App Rating
                        </div>

                    </div>

                </div>

            </div>



            {{-- =================================================
                RIGHT HERO PRODUCTS
            ================================================== --}}
            <div class="relative min-h-90 sm:min-h-107.5 lg:min-h-120">


                {{-- Main product --}}
                <div
                    class="absolute z-20
                           left-1/2 top-1/2
                           -translate-x-1/2 -translate-y-1/2
                           w-52 h-52
                           min-[400px]:w-60 min-[400px]:h-60
                           sm:w-72 sm:h-72
                           lg:w-80 lg:h-80
                           rounded-3xl
                           bg-white
                           shadow-2xl
                           border border-white
                           overflow-hidden
                           hero-main-product"
                >

                    <img
                        src="{{ asset('images/hero/sneaker.jpg') }}"
                        alt="Featured ShopHop product"
                        class="w-full h-full object-cover"
                    >

                </div>


                {{-- Earbuds card --}}
                <div
                    class="absolute z-30
                           left-0 top-3
                           sm:left-8 sm:top-5
                           lg:left-4
                           w-28 sm:w-36
                           bg-white
                           rounded-2xl
                           overflow-hidden
                           shadow-xl
                           border border-white
                           hero-floating-card"
                >

                    <img
                        src="{{ asset('images/hero/earbuds.jpg') }}"
                        alt="Earbuds"
                        class="w-full h-20 sm:h-28 object-cover"
                    >

                    <div class="px-2.5 sm:px-3 py-2">

                        <p class="text-[10px] sm:text-[11px] font-semibold text-navy truncate">
                            Earbuds Pro
                        </p>

                        <p class="text-[11px] sm:text-xs font-bold text-teal-dark">
                            ₱1,299
                        </p>

                    </div>

                </div>


                {{-- Watch card --}}
                <div
                    class="absolute z-30
                           right-0 bottom-5
                           sm:right-8 sm:bottom-8
                           lg:right-0
                           w-28 sm:w-36
                           bg-white
                           rounded-2xl
                           overflow-hidden
                           shadow-xl
                           border border-white
                           hero-floating-card
                           hero-delay"
                >

                    <img
                        src="{{ asset('images/hero/watch.jpg') }}"
                        alt="Fitness Watch"
                        class="w-full h-20 sm:h-28 object-cover"
                    >

                    <div class="px-2.5 sm:px-3 py-2">

                        <p class="text-[10px] sm:text-[11px] font-semibold text-navy truncate">
                            Fitness Watch
                        </p>

                        <p class="text-[11px] sm:text-xs font-bold text-teal-dark">
                            ₱1,799
                        </p>

                    </div>

                </div>


                {{-- Discount badge --}}
                <div
                    class="absolute z-40
                           right-0 top-5
                           sm:right-5
                           lg:right-8
                           w-14 h-14
                           sm:w-16 sm:h-16
                           rounded-full
                           bg-navy text-white
                           flex flex-col
                           items-center justify-center
                           shadow-lg
                           animate-pulse"
                >

                    <span class="text-[8px] sm:text-[9px] font-bold">
                        UP TO
                    </span>

                    <span class="text-xs sm:text-sm font-extrabold text-teal">
                        50%
                    </span>

                    <span class="text-[7px] sm:text-[8px] font-bold">
                        OFF
                    </span>

                </div>


                {{-- Rating --}}
                <div
                    class="absolute z-40
                           left-1 bottom-2
                           sm:left-10 sm:bottom-4
                           lg:left-0
                           bg-white
                           px-3 sm:px-4
                           py-2 sm:py-2.5
                           rounded-xl
                           shadow-lg
                           flex items-center gap-2"
                >

                    <div class="flex text-amber-400 text-xs sm:text-sm">
                        ★★★★★
                    </div>

                    <span class="text-[11px] sm:text-xs font-semibold text-navy">
                        4.9
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>



{{-- =========================================================
    CATEGORIES
========================================================= --}}
@php
    $categorySlideshows = [
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
@endphp

<section id="categories" class="py-12 sm:py-16 lg:py-20 bg-white">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-6 sm:mb-8">
            <p class="text-teal-dark text-xs sm:text-sm font-semibold mb-2 tracking-wide">
                EXPLORE
            </p>

            <h2 class="text-navy">
                Shop by Category
            </h2>

            <p class="text-sm sm:text-base text-navy/55 mt-2">
                Find what you need, faster.
            </p>
        </div>

        <div class="relative" data-category-slider>

            <button
                type="button"
                data-category-prev
                aria-label="Previous categories"
                class="hidden absolute left-0 top-1/2
                       -translate-x-1/2 -translate-y-1/2
                       z-20 w-10 h-10 sm:w-11 sm:h-11
                       rounded-full bg-white border border-gray-border
                       shadow-lg items-center justify-center
                       text-navy hover:text-teal-dark hover:scale-110
                       transition-all duration-200"
            >
                <x-lucide-chevron-left class="w-5 h-5" />
            </button>

            <div
                data-category-track
                class="grid grid-flow-col gap-4
                       overflow-x-auto scroll-smooth
                       snap-x snap-mandatory
                       scrollbar-none
                       [&::-webkit-scrollbar]:hidden
                       auto-cols-[calc((100%-1rem)/2)]
                       sm:auto-cols-[calc((100%-2rem)/3)]
                       md:auto-cols-[calc((100%-3rem)/4)]
                       lg:auto-cols-[calc((100%-6rem)/7)]"
            >
                @foreach ($categories as $category)
                    @php
                        $folder = $categorySlideshows[$category['name']] ?? null;
                        $slideImages = [];

                        if ($folder) {
                            $folderPath = public_path('images/category_icons_bg/' . $folder);

                            if (is_dir($folderPath)) {
                                $allFiles = glob($folderPath . '/*.{jpg,jpeg,png,webp,avif,gif}', GLOB_BRACE);
                                $slideImages = array_map('basename', array_slice($allFiles, 0, 6));
                            }
                        }

                        // Even spacing sa loob ng 12s animation cycle base sa bilang ng images
                        $imageCount = count($slideImages);
                        $slideInterval = $imageCount > 0 ? 12 / $imageCount : 0;
                    @endphp

                    
                        href="#"
                        class="group relative overflow-hidden
                               min-h-46 sm:min-h-48
                               rounded-2xl bg-gray-bg
                               px-4 py-5 text-center
                               flex flex-col items-center justify-center
                               border border-transparent
                               hover:border-teal/30
                               hover:-translate-y-1
                               hover:shadow-lg
                               snap-start
                               transition-all duration-300"
                    >
                        @if ($imageCount > 0)
                            <div class="absolute inset-0 z-0">
                                @foreach ($slideImages as $i => $file)
                                    <img
                                        src="{{ asset('images/category_icons_bg/' . $folder . '/' . $file) }}"
                                        alt=""
                                        class="category-slide"
                                        style="animation-delay: {{ $i * $slideInterval }}s;"
                                        loading="lazy"
                                    >
                                @endforeach

                                <div class="absolute inset-0 bg-white/75 group-hover:bg-white/60 transition-colors duration-300"></div>
                            </div>
                        @else
                            <div class="absolute inset-0 z-0 bg-gray-bg"></div>
                        @endif

                        <div
                            class="relative z-10
                                   w-16 h-16 rounded-2xl bg-white/90
                                   flex items-center justify-center
                                   text-teal-dark shadow-sm
                                   group-hover:bg-teal
                                   group-hover:text-white
                                   group-hover:scale-105
                                   transition-all duration-300"
                        >
                            <x-dynamic-component
                                :component="'lucide-' . $category['icon']"
                                class="w-7 h-7"
                            />
                        </div>

                        <span
                            class="relative z-10 block mt-4
                                   text-xs sm:text-sm
                                   font-semibold text-navy
                                   leading-snug"
                        >
                            {{ $category['name'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <button
                type="button"
                data-category-next
                aria-label="Next categories"
                class="absolute right-0 top-1/2
                       translate-x-1/2 -translate-y-1/2
                       z-20 w-10 h-10 sm:w-11 sm:h-11
                       rounded-full bg-white border border-gray-border
                       shadow-lg flex items-center justify-center
                       text-navy hover:text-teal-dark hover:scale-110
                       transition-all duration-200"
            >
                <x-lucide-chevron-right class="w-5 h-5" />
            </button>

        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-category-slider]').forEach(function (slider) {
            const track = slider.querySelector('[data-category-track]');
            const prevButton = slider.querySelector('[data-category-prev]');
            const nextButton = slider.querySelector('[data-category-next]');

            if (!track || !prevButton || !nextButton) {
                return;
            }

            function updateButtons() {
                const maxScrollLeft = track.scrollWidth - track.clientWidth;
                const atStart = track.scrollLeft <= 5;
                const atEnd = track.scrollLeft >= maxScrollLeft - 5;

                prevButton.classList.toggle('hidden', atStart);
                prevButton.classList.toggle('flex', !atStart);

                const hideNext = atEnd || maxScrollLeft <= 5;
                nextButton.classList.toggle('hidden', hideNext);
                nextButton.classList.toggle('flex', !hideNext);
            }

            function scrollAmount() {
                return track.clientWidth * 0.95;
            }

            nextButton.addEventListener('click', function () {
                track.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
            });

            prevButton.addEventListener('click', function () {
                track.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
            });

            track.addEventListener('scroll', updateButtons, { passive: true });
            window.addEventListener('resize', updateButtons);

            updateButtons();
        });
    });
</script>


{{-- =========================================================
    DEALS / PROMOTION
========================================================= --}}
<section
    id="deals"
    class="py-12 sm:py-16 lg:py-20 bg-gray-bg"
>
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative overflow-hidden
                   rounded-2xl sm:rounded-3xl
                   bg-navy
                   px-6 sm:px-8 lg:px-14
                   py-9 sm:py-11 lg:py-14
                   shadow-xl shadow-navy/10"
        >

            {{-- Decorative glow --}}
            <div
                class="pointer-events-none absolute
                       -right-24 -top-24
                       w-64 h-64
                       rounded-full
                       bg-teal/10
                       blur-sm"
            ></div>

            <div
                class="pointer-events-none absolute
                       right-24 -bottom-28
                       w-52 h-52
                       rounded-full
                       bg-teal/5"
            ></div>

            {{-- Small accent --}}
            <div
                class="pointer-events-none absolute
                       left-[45%] top-8
                       w-24 h-24
                       rounded-full
                       bg-white/5
                       blur-2xl"
            ></div>


            <div
                class="relative
                       grid lg:grid-cols-[1.2fr_0.8fr]
                       gap-10 lg:gap-14
                       items-center"
            >

                {{-- =================================================
                    LEFT CONTENT
                ================================================== --}}
                <div class="max-w-xl">

                    <span
                        class="inline-flex items-center gap-2
                               bg-teal/15
                               border border-teal/20
                               text-teal
                               px-3.5 sm:px-4
                               py-2
                               rounded-full
                               text-[11px] sm:text-xs
                               font-semibold
                               tracking-wide
                               mb-5"
                    >
                        <x-lucide-badge-percent class="w-4 h-4" />

                        SHOPHOP DEALS
                    </span>


                    <h2
                        class="text-white
                               text-3xl sm:text-4xl lg:text-[42px]
                               font-bold
                               leading-tight"
                    >
                        Great Finds.
                        <span class="block text-teal">
                            Better Prices.
                        </span>
                    </h2>


                    <p
                        class="text-sm sm:text-base
                               text-white/65
                               mt-5
                               max-w-lg
                               leading-relaxed"
                    >
                        Discover limited-time offers, discounted favorites,
                        and great-value finds across ShopHop.
                    </p>


                    {{-- Deal highlights --}}
                    <div
                        class="flex flex-wrap
                               gap-x-5 gap-y-3
                               mt-6"
                    >

                        <div class="flex items-center gap-2 text-white/75 text-xs sm:text-sm">

                            <span
                                class="w-5 h-5
                                       rounded-full
                                       bg-teal/15
                                       flex items-center justify-center"
                            >
                                <x-lucide-check class="w-3 h-3 text-teal" />
                            </span>

                            Limited-time deals

                        </div>


                        <div class="flex items-center gap-2 text-white/75 text-xs sm:text-sm">

                            <span
                                class="w-5 h-5
                                       rounded-full
                                       bg-teal/15
                                       flex items-center justify-center"
                            >
                                <x-lucide-check class="w-3 h-3 text-teal" />
                            </span>

                            Selected discounts

                        </div>

                    </div>


                    <a
                        href="#trending"
                        class="inline-flex
                               items-center justify-center gap-2
                               mt-7 sm:mt-8
                               bg-teal
                               hover:bg-teal-dark
                               text-white
                               text-sm font-semibold
                               px-6
                               py-3.5
                               rounded-full
                               shadow-lg shadow-teal/20
                               hover:-translate-y-0.5
                               transition-all duration-300"
                    >
                        Explore Deals

                        <x-lucide-arrow-right class="w-4 h-4" />
                    </a>

                </div>



                {{-- =================================================
                    RIGHT BENEFIT CARDS
                ================================================== --}}
                <div
                    class="grid grid-cols-2
                           gap-3 sm:gap-4
                           w-full
                           max-w-md
                           lg:max-w-none
                           lg:ml-auto"
                >

                    {{-- Discount --}}
                    <div
                        class="group
                               bg-white/10
                               hover:bg-white/15
                               backdrop-blur
                               rounded-2xl
                               p-4 sm:p-5
                               border border-white/10
                               transition-all duration-300"
                    >

                        <div
                            class="w-10 h-10
                                   rounded-xl
                                   bg-teal/15
                                   flex items-center justify-center
                                   mb-4"
                        >
                            <x-lucide-tag class="w-5 h-5 text-teal" />
                        </div>

                        <p class="text-white text-xl sm:text-2xl font-bold">
                            Up to 50%
                        </p>

                        <p class="text-white/50 text-[10px] sm:text-xs mt-1.5 leading-relaxed">
                            Off selected items
                        </p>

                    </div>


                    {{-- Delivery --}}
                    <div
                        class="group
                               bg-white/10
                               hover:bg-white/15
                               backdrop-blur
                               rounded-2xl
                               p-4 sm:p-5
                               border border-white/10
                               transition-all duration-300"
                    >

                        <div
                            class="w-10 h-10
                                   rounded-xl
                                   bg-teal/15
                                   flex items-center justify-center
                                   mb-4"
                        >
                            <x-lucide-truck class="w-5 h-5 text-teal" />
                        </div>

                        <p class="text-white text-xl sm:text-2xl font-bold">
                            Fast
                        </p>

                        <p class="text-white/50 text-[10px] sm:text-xs mt-1.5 leading-relaxed">
                            Delivery options
                        </p>

                    </div>


                    {{-- Secure --}}
                    <div
                        class="group
                               bg-white/10
                               hover:bg-white/15
                               backdrop-blur
                               rounded-2xl
                               p-4 sm:p-5
                               border border-white/10
                               transition-all duration-300"
                    >

                        <div
                            class="w-10 h-10
                                   rounded-xl
                                   bg-teal/15
                                   flex items-center justify-center
                                   mb-4"
                        >
                            <x-lucide-shield-check class="w-5 h-5 text-teal" />
                        </div>

                        <p class="text-white text-xl sm:text-2xl font-bold">
                            Secure
                        </p>

                        <p class="text-white/50 text-[10px] sm:text-xs mt-1.5 leading-relaxed">
                            Safe shopping experience
                        </p>

                    </div>


                    {{-- Support --}}
                    <div
                        class="group
                               bg-white/10
                               hover:bg-white/15
                               backdrop-blur
                               rounded-2xl
                               p-4 sm:p-5
                               border border-white/10
                               transition-all duration-300"
                    >

                        <div
                            class="w-10 h-10
                                   rounded-xl
                                   bg-teal/15
                                   flex items-center justify-center
                                   mb-4"
                        >
                            <x-lucide-headphones class="w-5 h-5 text-teal" />
                        </div>

                        <p class="text-white text-xl sm:text-2xl font-bold">
                            24/7
                        </p>

                        <p class="text-white/50 text-[10px] sm:text-xs mt-1.5 leading-relaxed">
                            Customer support
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>



{{-- =========================================================
    TRENDING PRODUCTS
========================================================= --}}
<section
    id="trending"
    class="py-12 sm:py-16 lg:py-20 bg-white"
>

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">


        {{-- Section header --}}
        <div
            class="flex flex-col
                   md:flex-row
                   md:items-end
                   md:justify-between
                   gap-4
                   mb-8 sm:mb-10"
        >

            <div>

                <p
                    class="text-teal-dark
                           text-xs sm:text-sm
                           font-semibold
                           mb-2
                           tracking-wide"
                >
                    POPULAR PICKS
                </p>


                <h2 class="text-navy">
                    Trending Now
                </h2>


                <p class="text-sm sm:text-base text-navy/55 mt-2">
                    Products shoppers are loving right now.
                </p>

            </div>


            <a
                href="#"
                class="inline-flex items-center gap-2
                       text-sm font-semibold
                       text-teal-dark
                       hover:text-navy
                       transition"
            >

                View all products

                <x-lucide-arrow-right class="w-4 h-4" />

            </a>

        </div>



        {{-- Product Grid --}}
        <div
            class="grid
                   grid-cols-1
                   min-[420px]:grid-cols-2
                   md:grid-cols-3
                   lg:grid-cols-4
                   gap-4 sm:gap-5"
        >

            @foreach ($trendingProducts as $product)

                <article
                    class="group
                           bg-white
                           rounded-2xl
                           overflow-hidden
                           border border-gray-border
                           hover:border-teal/30
                           hover:shadow-xl
                           transition-all duration-300"
                >


                    {{-- Product Image --}}
                    <div class="relative aspect-square bg-gray-bg overflow-hidden">

                        <img
                            src="{{ str_starts_with($product['image'], 'http')
                                ? $product['image']
                                : asset('images/' . $product['image']) }}"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full
                                   object-cover
                                   group-hover:scale-105
                                   transition-transform duration-500"
                        >


                        {{-- Sale --}}
                        @if ($product['original_price'])

                            <span
                                class="absolute
                                       top-3 left-3
                                       bg-teal
                                       text-white
                                       text-[10px] font-bold
                                       px-3 py-1.5
                                       rounded-full"
                            >
                                SALE
                            </span>

                        @endif


                        {{-- Wishlist --}}
                        <button
                            type="button"
                            class="absolute
                                   top-3 right-3
                                   w-9 h-9
                                   rounded-full
                                   bg-white/95
                                   backdrop-blur
                                   flex items-center justify-center
                                   text-navy
                                   hover:text-teal-dark
                                   hover:scale-105
                                   transition"
                            title="Add to wishlist"
                            aria-label="Add {{ $product['name'] }} to wishlist"
                        >

                            <x-lucide-heart class="w-4 h-4" />

                        </button>

                    </div>



                    {{-- Product Info --}}
                    <div class="p-4 sm:p-5">


                        <span class="text-[11px] sm:text-xs text-navy/45">
                            {{ $product['category'] }}
                        </span>


                        <h3
                            class="text-base sm:text-lg
                                   text-navy
                                   mt-1 mb-2
                                   line-clamp-1"
                        >
                            {{ $product['name'] }}
                        </h3>


                        {{-- Rating --}}
                        <div class="flex flex-wrap items-center gap-2 mb-3">

                            <div class="flex text-amber-400 text-[10px] sm:text-xs">
                                ★★★★★
                            </div>

                            <span class="text-[10px] sm:text-xs text-navy/45">
                                {{ $product['rating'] }}
                                ({{ $product['reviews'] }})
                            </span>

                        </div>



                        {{-- Price --}}
                        <div class="flex flex-wrap items-center gap-2 mb-4">

                            <span class="font-bold text-base sm:text-lg text-navy">
                                ₱{{ number_format($product['price']) }}
                            </span>


                            @if ($product['original_price'])

                                <span
                                    class="line-through
                                           text-[11px] sm:text-xs
                                           text-navy/35"
                                >
                                    ₱{{ number_format($product['original_price']) }}
                                </span>

                            @endif

                        </div>



                        {{-- Add to cart --}}
                        <button
                            type="button"
                            class="w-full
                                   flex items-center justify-center gap-2
                                   bg-teal
                                   hover:bg-teal-dark
                                   text-white
                                   text-xs sm:text-sm
                                   font-semibold
                                   py-2.5 sm:py-3
                                   rounded-xl
                                   transition"
                        >

                            <x-lucide-shopping-cart class="w-4 h-4" />

                            Add to Cart

                        </button>

                    </div>

                </article>

            @endforeach

        </div>

    </div>

</section>



{{-- =========================================================
    NEW ARRIVALS
========================================================= --}}
<section
    id="new-arrivals"
    class="py-12 sm:py-16 lg:py-20 bg-gray-bg"
>

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">


        {{-- Header --}}
        <div class="text-center max-w-xl mx-auto mb-8 sm:mb-10">

            <p
                class="text-teal-dark
                       text-xs sm:text-sm
                       font-semibold
                       mb-2
                       tracking-wide"
            >
                JUST IN
            </p>


            <h2 class="text-navy">
                New Arrivals
            </h2>


            <p class="text-sm sm:text-base text-navy/55 mt-2">
                Fresh products and exciting finds added to ShopHop.
            </p>

        </div>



        {{-- Main card --}}
        <div
            class="bg-white
                   rounded-2xl sm:rounded-3xl
                   border border-gray-border
                   p-5 sm:p-8 lg:p-12"
        >

            <div class="grid lg:grid-cols-2 gap-8 sm:gap-10 items-center">


                {{-- Content --}}
                <div>

                    <span
                        class="inline-block
                               bg-teal-light
                               text-teal-dark
                               text-xs font-semibold
                               px-3 py-1.5
                               rounded-full
                               mb-4 sm:mb-5"
                    >
                        NEW THIS WEEK
                    </span>


                    <h2 class="text-navy">

                        Find something

                        <span class="text-teal">
                            you'll love.
                        </span>

                    </h2>


                    <p
                        class="text-sm sm:text-base
                               text-navy/55
                               mt-4
                               leading-relaxed
                               max-w-lg"
                    >
                        From everyday essentials to the latest trends,
                        there's always something new waiting for you.
                    </p>


                    <a
                        href="#trending"
                        class="inline-flex items-center gap-2
                               mt-5 sm:mt-6
                               text-sm font-semibold
                               text-teal-dark
                               hover:text-navy
                               transition"
                    >

                        Discover new arrivals

                        <x-lucide-arrow-right class="w-4 h-4" />

                    </a>

                </div>



                {{-- Images --}}
                <div class="grid grid-cols-2 gap-3 sm:gap-4">


                    <div class="aspect-square rounded-2xl overflow-hidden bg-gray-bg">

                        <img
                            src="{{ asset('images/hero/earbuds.jpg') }}"
                            alt="New earbuds"
                            class="w-full h-full
                                   object-cover
                                   hover:scale-105
                                   transition-transform duration-500"
                        >

                    </div>


                    <div
                        class="aspect-square
                               rounded-2xl
                               overflow-hidden
                               bg-gray-bg
                               mt-5 sm:mt-8"
                    >

                        <img
                            src="{{ asset('images/hero/watch.jpg') }}"
                            alt="New fitness watch"
                            class="w-full h-full
                                   object-cover
                                   hover:scale-105
                                   transition-transform duration-500"
                        >

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



{{-- =========================================================
    FINAL CTA
========================================================= --}}
<section class="py-12 sm:py-16 lg:py-20 bg-white">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative
                   overflow-hidden
                   rounded-2xl sm:rounded-3xl
                   bg-[#E9F8F4]
                   px-5 sm:px-8 lg:px-16
                   py-10 sm:py-12 lg:py-14
                   text-center"
        >


            {{-- Decorations --}}
            <div
                class="absolute
                       -left-20 -top-20
                       w-40 sm:w-48
                       h-40 sm:h-48
                       rounded-full
                       bg-teal/10"
            ></div>

            <div
                class="absolute
                       -right-20 -bottom-20
                       w-40 sm:w-48
                       h-40 sm:h-48
                       rounded-full
                       bg-teal/10"
            ></div>



            <div class="relative max-w-2xl mx-auto">

                <p
                    class="text-teal-dark
                           text-xs sm:text-sm
                           font-semibold
                           mb-3
                           tracking-wide"
                >
                    SHOPHOP
                </p>


                <h2 class="text-navy">
                    Ready to Hop In?
                </h2>


                <p class="text-sm sm:text-base text-navy/55 mt-3 mb-6 sm:mb-7">
                    Discover your next favorite product today.
                </p>


                <a
                    href="#trending"
                    class="inline-flex
                           items-center justify-center gap-2
                           bg-teal
                           hover:bg-teal-dark
                           text-white
                           text-sm font-semibold
                           px-6 sm:px-7
                           py-3 sm:py-3.5
                           rounded-full
                           transition-all duration-300
                           hover:-translate-y-0.5
                           shadow-lg shadow-teal/20"
                >

                    Start Shopping

                    <x-lucide-arrow-right class="w-4 h-4" />

                </a>

            </div>

        </div>

    </div>

</section>


@endsection



{{-- =========================================================
    HERO / CATEGORY ANIMATIONS
========================================================= --}}
@push('styles')
<style>
    @keyframes shopHopFloat {
        0%, 100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    @keyframes categorySlideshow {
        0% {
            opacity: 0;
            transform: scale(1.03);
        }

        4% {
            opacity: 1;
            transform: scale(1);`
        }

        21% {
            opacity: 1;
            transform: scale(1);
        }

        25% {
            opacity: 0;
            transform: scale(1.02);
        }

        100% {
            opacity: 0;
        }
    }

    .category-slide {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        animation: categorySlideshow 12s ease-in-out infinite;
    }

    .hero-main-product {
        animation: shopHopFloat 5s ease-in-out infinite;
    }

    .hero-floating-card {
        animation: shopHopFloat 4s ease-in-out infinite;
    }

    .hero-delay {
        animation-delay: 1.5s;
    }

    @media (prefers-reduced-motion: reduce) {
        .hero-main-product,
        .hero-floating-card,
        .category-slide {
            animation: none;
        }

        .category-slide:first-child {
            opacity: 1;
        }
    }
</style>
@endpush