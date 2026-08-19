{{-- resources/views/home.blade.php --}}

@extends('layouts.app')

@section('title', 'ShopHop — Hop In. Shop More.')

@section('content')

{{-- =========================================================
    HERO SECTION
========================================================= --}}
<section class="relative overflow-hidden bg-gray-bg">

    {{-- Decorative background circles --}}
    <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-teal/10"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-teal/5"></div>

    <div class="relative max-w-310 mx-auto px-6 py-20 lg:py-24">

        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- LEFT CONTENT --}}
            <div class="max-w-xl">

                {{-- Small badge --}}
                <div class="inline-flex items-center gap-2 bg-teal-light text-teal-dark px-4 py-2 rounded-full text-sm font-medium mb-6">
                    <span class="w-2 h-2 rounded-full bg-teal"></span>
                    New arrivals every day
                </div>

                <h1 class="text-navy mb-6">
                    Everything You Love,
                    <span class="block text-teal">
                        Just a Hop Away.
                    </span>
                </h1>

                <p class="text-navy/65 text-base lg:text-lg leading-relaxed max-w-lg mb-8">
                    Discover everyday essentials, trending finds, and products
                    you'll love — all in one place.
                </p>

                <div class="flex flex-wrap gap-4">

                    <a
                        href="#trending"
                        class="inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white font-semibold px-7 py-3.5 rounded-full transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-teal/20"
                    >
                        Shop Now
                        <x-lucide-arrow-right class="w-4 h-4" />
                    </a>

                    <a
                        href="#categories"
                        class="inline-flex items-center justify-center border-2 border-navy text-navy hover:bg-navy hover:text-white font-semibold px-7 py-3 rounded-full transition-all duration-300"
                    >
                        Explore Categories
                    </a>

                </div>

                {{-- STATS --}}
                <div class="flex flex-wrap gap-8 mt-12 pt-8 border-t border-navy/10">

                    <div>
                        <div class="text-2xl font-bold text-navy">
                            50K+
                        </div>
                        <div class="text-xs text-navy/55">
                            Products
                        </div>
                    </div>

                    <div class="w-px bg-navy/10"></div>

                    <div>
                        <div class="text-2xl font-bold text-navy">
                            2M+
                        </div>
                        <div class="text-xs text-navy/55">
                            Happy Shoppers
                        </div>
                    </div>

                    <div class="w-px bg-navy/10"></div>

                    <div>
                        <div class="text-2xl font-bold text-navy">
                            4.9
                        </div>
                        <div class="text-xs text-navy/55">
                            App Rating
                        </div>
                    </div>

                </div>

            </div>


            {{-- RIGHT HERO PRODUCTS --}}
            <div class="relative min-h-105 lg:min-h-120">

                {{-- Main product --}}
                <div
                    class="absolute z-20 left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2
                           w-64 h-64 sm:w-72 sm:h-72 lg:w-80 lg:h-80
                           rounded-3xl bg-white shadow-2xl
                           border border-white overflow-hidden
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
                    class="absolute z-30 left-2 top-5 sm:left-8 lg:left-4
                           w-32 sm:w-36
                           bg-white rounded-2xl overflow-hidden
                           shadow-xl border border-white
                           hero-floating-card"
                >
                    <img
                        src="{{ asset('images/hero/earbuds.jpg') }}"
                        alt="Earbuds"
                        class="w-full h-28 object-cover"
                    >

                    <div class="px-3 py-2">
                        <p class="text-[11px] font-semibold text-navy">
                            Earbuds Pro
                        </p>
                        <p class="text-xs font-bold text-teal-dark">
                            ₱1,299
                        </p>
                    </div>
                </div>


                {{-- Watch card --}}
                <div
                    class="absolute z-30 right-2 bottom-8 sm:right-8 lg:right-0
                           w-32 sm:w-36
                           bg-white rounded-2xl overflow-hidden
                           shadow-xl border border-white
                           hero-floating-card hero-delay"
                >
                    <img
                        src="{{ asset('images/hero/watch.jpg') }}"
                        alt="Fitness Watch"
                        class="w-full h-28 object-cover"
                    >

                    <div class="px-3 py-2">
                        <p class="text-[11px] font-semibold text-navy">
                            Fitness Watch
                        </p>
                        <p class="text-xs font-bold text-teal-dark">
                            ₱1,799
                        </p>
                    </div>
                </div>


                {{-- Discount badge --}}
                <div
                    class="absolute z-40 right-0 top-5 sm:right-5 lg:right-8
                           w-16 h-16 rounded-full
                           bg-navy text-white
                           flex flex-col items-center justify-center
                           shadow-lg
                           animate-pulse"
                >
                    <span class="text-[9px] font-bold">
                        UP TO
                    </span>

                    <span class="text-sm font-extrabold text-teal">
                        50%
                    </span>

                    <span class="text-[8px] font-bold">
                        OFF
                    </span>
                </div>


                {{-- Rating --}}
                <div
                    class="absolute z-40 left-4 bottom-4 sm:left-10 lg:left-0
                           bg-white px-4 py-2.5 rounded-xl
                           shadow-lg flex items-center gap-2"
                >
                    <div class="flex text-amber-400 text-sm">
                        ★★★★★
                    </div>

                    <span class="text-xs font-semibold text-navy">
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
        'Pet Supplies' => ['folder' => 'pet_supplies', 'files' => ['bed.webp','cage.avif','dog_food.jpg','grooming.avif','toys.jpg']],
        'Electronics and Gadgets' => ['folder' => 'electronics_gadgets', 'files' => ['cctv.jpg','consoles.webp','cpu.jpg','gadgets.webp','laptop.avif']],
        'Women\'s Apparel' => ['folder' => 'women_apparel', 'files' => ['activewear.png','jacket.png','outfit.jpg','shoes.png','skirt.png','sleepwear.png','tops.png']],
        'Men\'s Apparel' => ['folder' => 'men_apparel', 'files' => ['active.jpg','casual.jpg','outfit.jpg','shoes.jpg','sleepwear.png','suit.jpg']],
        'Kids and Baby' => ['folder' => 'kids_baby', 'files' => ['educational.jpg','safety.jpg','sleepwear.jpg','stroller.jpg','toys.png']],
        'Home and Garden' => ['folder' => 'home_garden', 'files' => ['baskets.jpg','clocks.png','couches.png','fakegrass.jpg','gardentools.jpg','lamps.png','pots.jpg','stones.jpg','tools.jpg','wall.jpg']],
        'Sports and Outdoors' => ['folder' => 'sports_outdoors', 'files' => ['bikes.jpg','dumbell.jpg','elliptical.jpg','goggles.jpg','pickle.jpg','tabletennis.jpg']],
        'Health and Beauty' => ['folder' => 'health_beauty', 'files' => ['mengrooming.jpg','selfcare.png','supplement.jpg','tools.jpg']],
        'Makeup & Cosmetics' => ['folder' => 'health_beauty', 'files' => ['makeup.jpg','brush.jpg','cuticle.jpg']],
        'Books and Media' => ['folder' => 'books_media', 'files' => ['books.jpg','cds.jpg','dvd.jpg','magazine.jpg','ps5.jpg','ps5cds.jpg']],
        'Food and Gourmet' => ['folder' => 'food_gourmet', 'files' => ['chips.jpg','chocolate.jpg','heinz.jpg','noodleds.jpg','sbs.jpg','spices.jpg']],
        'Automotive & Motorcycle' => ['folder' => 'automotive_motorcycle', 'files' => ['cleaner.jpg','gloves.jpg','helmet.jpg','parts.jpg','tape.jpg','tool.jpg','tools.jpg','wheels.jpg']],
        'Furniture and Office Equipment' => ['folder' => 'furniture_office', 'files' => ['cabinet.jpg','desk.jpg','officechair.jpg','organizer.jpg','tablelamps.jpg']],
        'Jewelry and Watches' => ['folder' => 'jewelry_watches', 'files' => ['bracelet.jpg','hats.jpg','necklace.jpg','rings.jpg','watches.jpg']],
        'Office and School Supplies' => ['folder' => 'office_schoolsupplies', 'files' => ['bondpapers.jpg','ink.jpg','notebooks.jpg','pens.jpg','printer.jpg','setsupplies.jpg']],
    ];
@endphp

<section
    id="categories"
    class="py-20 bg-white"
>
    <div class="max-w-310 mx-auto px-6">

        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-10">

            <div>
                <p class="text-teal-dark text-sm font-semibold mb-2">
                    EXPLORE
                </p>

                <h2 class="text-navy">
                    Shop by Category
                </h2>

                <p class="text-navy/55 mt-2">
                    Find what you need, faster.
                </p>
            </div>

            <a
                href="#"
                class="mt-4 md:mt-0 inline-flex items-center gap-2 text-sm font-semibold text-teal-dark hover:text-navy transition"
            >
                View all
                <x-lucide-arrow-right class="w-4 h-4" />
            </a>

        </div>


        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">

            @foreach ($categories as $category)

                @php
                    $slideshow = $categorySlideshows[$category['name']] ?? null;
                    $slideImages = [];

                    if ($slideshow) {
                        foreach (array_slice($slideshow['files'], 0, 4) as $file) {
                            $path = public_path('images/category_icons_bg/' . $slideshow['folder'] . '/' . $file);
                            if (file_exists($path)) {
                                $slideImages[] = $file;
                            }
                        }
                    }
                @endphp

                <a href="#"
                    class="group relative overflow-hidden aspect-square flex items-center justify-center bg-gray-bg rounded-2xl p-6 text-center border border-transparent hover:border-teal/30 hover:bg-teal-light transition-all duration-300 hover:-translate-y-1">

                    @if (count($slideImages) > 0)

                        <div class="absolute inset-0 z-0">

                            @foreach ($slideImages as $i => $file)
                                <img
                                    src="{{ asset('images/category_icons_bg/' . $slideshow['folder'] . '/' . $file) }}"
                                    class="category-slide"
                                    style="animation-delay: {{ $i * 3 }}s;"
                                    loading="eager"
                                    onload="this.classList.add('is-ready')"
                                >
                            @endforeach

                            <div class="absolute inset-0 bg-white/75 group-hover:bg-white/55 transition-colors duration-300"></div>

                        </div>

                    @else

                        {{-- Fallback kung walang existing images --}}
                        <div class="absolute inset-0 z-0 bg-gray-bg"></div>

                    @endif

                    <span class="relative z-10 text-sm font-semibold text-navy">
                        {{ $category['name'] }}
                    </span>

                </a>

            @endforeach

        </div>

    </div>
</section>


{{-- =========================================================
    DEALS / PROMOTION
========================================================= --}}
<section
    id="deals"
    class="py-20 bg-gray-bg"
>
    <div class="max-w-310 mx-auto px-6">

        <div class="relative overflow-hidden rounded-3xl bg-navy px-8 py-12 lg:px-14">

            {{-- Decorative circles --}}
            <div class="absolute -right-20 -top-20 w-64 h-64 rounded-full bg-teal/10"></div>
            <div class="absolute right-32 -bottom-24 w-48 h-48 rounded-full bg-teal/5"></div>

            <div class="relative grid lg:grid-cols-2 gap-10 items-center">

                <div>

                    <span class="inline-block bg-teal/15 text-teal px-4 py-2 rounded-full text-xs font-semibold mb-5">
                        SHOPHOP DEALS
                    </span>

                    <h2 class="text-white">
                        Great Finds.
                        <span class="text-teal">
                            Better Prices.
                        </span>
                    </h2>

                    <p class="text-white/65 mt-4 max-w-lg">
                        Save more on selected products and discover limited-time
                        deals made just for you.
                    </p>

                    <a
                        href="#trending"
                        class="inline-flex items-center gap-2 mt-7 bg-teal hover:bg-teal-dark text-white font-semibold px-6 py-3 rounded-full transition"
                    >
                        Explore Deals
                        <x-lucide-arrow-right class="w-4 h-4" />
                    </a>

                </div>


                <div class="grid grid-cols-2 gap-4 max-w-sm lg:ml-auto">

                    <div class="bg-white/10 backdrop-blur rounded-2xl p-5 border border-white/10">
                        <x-lucide-tag class="w-6 h-6 text-teal mb-4" />
                        <p class="text-white text-2xl font-bold">
                            50%
                        </p>
                        <p class="text-white/55 text-xs mt-1">
                            Selected items
                        </p>
                    </div>

                    <div class="bg-white/10 backdrop-blur rounded-2xl p-5 border border-white/10">
                        <x-lucide-truck class="w-6 h-6 text-teal mb-4" />
                        <p class="text-white text-2xl font-bold">
                            Fast
                        </p>
                        <p class="text-white/55 text-xs mt-1">
                            Delivery options
                        </p>
                    </div>

                    <div class="bg-white/10 backdrop-blur rounded-2xl p-5 border border-white/10">
                        <x-lucide-shield-check class="w-6 h-6 text-teal mb-4" />
                        <p class="text-white text-2xl font-bold">
                            Secure
                        </p>
                        <p class="text-white/55 text-xs mt-1">
                            Shopping experience
                        </p>
                    </div>

                    <div class="bg-white/10 backdrop-blur rounded-2xl p-5 border border-white/10">
                        <x-lucide-headphones class="w-6 h-6 text-teal mb-4" />
                        <p class="text-white text-2xl font-bold">
                            24/7
                        </p>
                        <p class="text-white/55 text-xs mt-1">
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
    class="py-20 bg-white"
>

    <div class="max-w-310 mx-auto px-6">

        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-10">

            <div>

                <p class="text-teal-dark text-sm font-semibold mb-2">
                    POPULAR PICKS
                </p>

                <h2 class="text-navy">
                    Trending Now
                </h2>

                <p class="text-navy/55 mt-2">
                    Products shoppers are loving right now.
                </p>

            </div>

            <a
                href="#"
                class="mt-4 md:mt-0 inline-flex items-center gap-2 text-sm font-semibold text-teal-dark hover:text-navy transition"
            >
                View all products
                <x-lucide-arrow-right class="w-4 h-4" />
            </a>

        </div>


        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">

            @foreach ($trendingProducts as $product)

                <article
                    class="group bg-white rounded-2xl overflow-hidden border border-gray-border hover:border-teal/30 hover:shadow-xl transition-all duration-300"
                >

                    {{-- PRODUCT IMAGE --}}
                    <div class="relative aspect-square bg-gray-bg overflow-hidden">

                        <img
                            src="{{ str_starts_with($product['image'], 'http')
                                ? $product['image']
                                : asset('images/' . $product['image']) }}"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        >


                        {{-- SALE --}}
                        @if ($product['original_price'])

                            <span class="absolute top-3 left-3 bg-teal text-white text-[10px] font-bold px-3 py-1.5 rounded-full">
                                SALE
                            </span>

                        @endif


                        {{-- WISHLIST --}}
                        <button
                            type="button"
                            class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/95 backdrop-blur flex items-center justify-center text-navy hover:text-teal-dark hover:scale-105 transition"
                            title="Add to wishlist"
                        >
                            <x-lucide-heart class="w-4 h-4" />
                        </button>

                    </div>


                    {{-- PRODUCT INFORMATION --}}
                    <div class="p-5">

                        <span class="text-xs text-navy/45">
                            {{ $product['category'] }}
                        </span>

                        <h3 class="text-navy mt-1 mb-2 line-clamp-1">
                            {{ $product['name'] }}
                        </h3>


                        {{-- RATING --}}
                        <div class="flex items-center gap-2 mb-3">

                            <div class="flex text-amber-400 text-xs">
                                ★★★★★
                            </div>

                            <span class="text-xs text-navy/45">
                                {{ $product['rating'] }}
                                ({{ $product['reviews'] }})
                            </span>

                        </div>


                        {{-- PRICE --}}
                        <div class="flex items-center gap-2 mb-4">

                            <span class="font-bold text-lg text-navy">
                                ₱{{ number_format($product['price']) }}
                            </span>

                            @if ($product['original_price'])

                                <span class="line-through text-xs text-navy/35">
                                    ₱{{ number_format($product['original_price']) }}
                                </span>

                            @endif

                        </div>


                        {{-- ADD TO CART --}}
                        <button
                            type="button"
                            class="w-full flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold py-3 rounded-xl transition"
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
    class="py-20 bg-gray-bg"
>

    <div class="max-w-310 mx-auto px-6">

        <div class="text-center max-w-xl mx-auto mb-10">

            <p class="text-teal-dark text-sm font-semibold mb-2">
                JUST IN
            </p>

            <h2 class="text-navy">
                New Arrivals
            </h2>

            <p class="text-navy/55 mt-2">
                Fresh products and exciting finds added to ShopHop.
            </p>

        </div>


        <div class="bg-white rounded-3xl border border-gray-border p-8 lg:p-12">

            <div class="grid lg:grid-cols-2 gap-10 items-center">

                <div>

                    <span class="inline-block bg-teal-light text-teal-dark text-xs font-semibold px-3 py-1.5 rounded-full mb-5">
                        NEW THIS WEEK
                    </span>

                    <h2 class="text-navy">
                        Find something
                        <span class="text-teal">
                            you'll love.
                        </span>
                    </h2>

                    <p class="text-navy/55 mt-4 leading-relaxed">
                        From everyday essentials to the latest trends,
                        there's always something new waiting for you.
                    </p>

                    <a
                        href="#"
                        class="inline-flex items-center gap-2 mt-6 text-sm font-semibold text-teal-dark hover:text-navy transition"
                    >
                        Discover new arrivals
                        <x-lucide-arrow-right class="w-4 h-4" />
                    </a>

                </div>


                <div class="grid grid-cols-2 gap-4">

                    <div class="aspect-square rounded-2xl overflow-hidden bg-gray-bg">
                        <img
                            src="{{ asset('images/hero/earbuds.jpg') }}"
                            alt="New earbuds"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                        >
                    </div>

                    <div class="aspect-square rounded-2xl overflow-hidden bg-gray-bg mt-8">
                        <img
                            src="{{ asset('images/hero/watch.jpg') }}"
                            alt="New fitness watch"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
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
<section class="py-20 bg-white">

    <div class="max-w-310 mx-auto px-6">

        <div class="relative overflow-hidden rounded-3xl bg-[#E9F8F4] px-8 py-14 lg:px-16 text-center">

            <div class="absolute -left-20 -top-20 w-48 h-48 rounded-full bg-teal/10"></div>
            <div class="absolute -right-20 -bottom-20 w-48 h-48 rounded-full bg-teal/10"></div>

            <div class="relative max-w-2xl mx-auto">

                <p class="text-teal-dark text-sm font-semibold mb-3">
                    SHOPHOP
                </p>

                <h2 class="text-navy">
                    Ready to Hop In?
                </h2>

                <p class="text-navy/55 mt-3 mb-7">
                    Discover your next favorite product today.
                </p>

                <a
                    href="#trending"
                    class="inline-flex items-center gap-2 bg-teal hover:bg-teal-dark text-white font-semibold px-7 py-3.5 rounded-full transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-teal/20"
                >
                    Start Shopping
                    <x-lucide-arrow-right class="w-4 h-4" />
                </a>

            </div>

        </div>

    </div>

</section>


{{-- =========================================================
    HERO ANIMATION
========================================================= --}}
@push('styles')

<style>

    @keyframes shopHopFloat {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }

    }

@keyframes categorySlideshow {
    0%   { opacity: 0; transform: translateY(20px); }
    4%   { opacity: 1; transform: translateY(0); }
    21%  { opacity: 1; transform: translateY(0); }
    25%  { opacity: 0; transform: translateY(-20px); }
    100% { opacity: 0; }
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



</style>

@endpush

@endsection