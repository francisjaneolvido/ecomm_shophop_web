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

                    <a
                        href="#"
                        class="group relative overflow-hidden
                               min-h-46 sm:min-h-48
                               rounded-2xl bg-gray-bg
                               px-4 py-5 text-center
                               flex flex-col items-center justify-center
                               border border-transparent
                               hover:border-teal/30
                               hover:bg-teal-light
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
                            <!-- <x-dynamic-component
                                :component="'lucide-' . $category['icon']"
                                class="w-7 h-7" -->
                            <!-- /> -->
                             <x-lucide-tag />
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

