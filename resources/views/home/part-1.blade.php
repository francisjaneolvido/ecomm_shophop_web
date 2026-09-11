{{-- =========================================================
    HERO SECTION — MAJOR REDESIGN
========================================================= --}}
<section class="relative overflow-hidden bg-[linear-gradient(135deg,#F7FAFC_0%,#F3F8F7_45%,#EEF8F5_100%)]" data-parallax-container>

    {{-- Ambient background --}}
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-28 -right-20 w-80 h-80 sm:w-md sm:h-112 rounded-full bg-teal/10 blur-3xl" data-parallax="18"></div>
        <div class="absolute -bottom-36 -left-28 w-80 h-80 sm:w-120 sm:h-120 rounded-full bg-navy/5 blur-3xl" data-parallax="-14"></div>

        <div class="absolute inset-0 opacity-[0.28]"
             style="background-image:
                    linear-gradient(rgba(15,27,61,.035) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(15,27,61,.035) 1px, transparent 1px);
                    background-size: 36px 36px;">
        </div>
    </div>

    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">

        <div class="grid lg:grid-cols-[0.92fr_1.08fr] gap-7 lg:gap-10 xl:gap-12 items-center">

            {{-- =================================================
                LEFT CONTENT
            ================================================== --}}
            <div class="max-w-xl">

                {{-- Original ShopHop badge --}}
                <div
                    style="--stagger-index: 0;"
                    class="stagger-item
                           inline-flex items-center gap-2
                           bg-teal-light text-teal-dark
                           px-3 py-1.5
                           rounded-full
                           text-[10.5px] sm:text-xs font-medium
                           mb-4
                           shadow-sm shadow-teal/5"
                >
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-teal opacity-30 hero-ping"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-teal"></span>
                    </span>

                    New arrivals every day
                </div>


                {{-- Keep official ShopHop tagline --}}
                <p
                    role="heading"
                    aria-level="1"
                    style="--stagger-index: 1;"
                    class="stagger-item
                           text-navy mb-3 sm:mb-4
                           text-[2rem] leading-[1.08]
                           sm:text-[2.4rem]
                           lg:text-[2.65rem]
                           xl:text-[2.9rem]
                           font-bold tracking-[-0.035em]">

                    Everything You Love,

                    <span class="block text-teal">
                        Just a Hop Away.
                    </span>

                </p>


                {{-- Original supporting copy --}}
                <p
                    style="--stagger-index: 2;"
                    class="stagger-item
                           text-navy/60
                           text-[12px] sm:text-sm lg:text-[15px]
                           leading-relaxed
                           max-w-lg
                           mb-5 sm:mb-6"
                >
                    Discover everyday essentials, trending finds,
                    and products you'll love — all in one place.
                </p>


                {{-- CTA Buttons --}}
                <div style="--stagger-index: 3;" class="stagger-item flex flex-col sm:flex-row gap-2.5 sm:gap-3">

                    <a
                        href="#trending"
                        class="group magnetic ripple-surface inline-flex items-center justify-center gap-2
                               bg-teal hover:bg-teal-dark
                               text-white
                               text-xs sm:text-[13px] font-semibold
                               px-5 sm:px-6
                               py-2.5 sm:py-3
                               rounded-xl
                               transition-colors duration-300
                               hover:shadow-xl
                               shadow-lg shadow-teal/20"
                    >
                        Shop Now

                        <x-lucide-arrow-right
                            class="w-4 h-4
                                   transition-transform duration-300
                                   group-hover:translate-x-1"
                        />
                    </a>


                    {{-- Refined secondary CTA --}}
                    <a
                        href="#categories"
                        class="group magnetic ripple-surface inline-flex items-center justify-center gap-2.5
                               bg-white/80 hover:bg-white
                               border border-navy/12 hover:border-teal/35
                               text-navy hover:text-teal-dark
                               text-xs sm:text-[13px] font-semibold
                               px-4.5 sm:px-5
                               py-2.5 sm:py-3
                               rounded-xl
                               shadow-sm shadow-navy/5
                               hover:shadow-lg hover:shadow-navy/8
                               transition-colors duration-300"
                    >
                        <span
                            class="w-6 h-6 rounded-lg
                                   bg-teal-light
                                   flex items-center justify-center
                                   text-teal-dark
                                   group-hover:bg-teal
                                   group-hover:text-white
                                   group-hover:rotate-6
                                   transition-all duration-300"
                        >
                            <x-lucide-grid-2x2 class="w-3 h-3" />
                        </span>

                        Explore Categories
                    </a>

                </div>


                {{-- Original stats, visually refined only --}}
                <div
                    style="--stagger-index: 4;"
                    class="stagger-item
                           grid grid-cols-3
                           gap-3 sm:gap-5
                           mt-7 sm:mt-8
                           pt-4 sm:pt-5
                           border-t border-navy/10
                           max-w-xl"
                >

                    <div>
                        <div class="text-lg sm:text-xl font-bold text-navy tabular-nums">
                            <span data-count-to="50" data-count-suffix="K+">0K+</span>
                        </div>

                        <div class="text-[9.5px] sm:text-[10.5px] text-navy/50 mt-1">
                            Products
                        </div>
                    </div>


                    <div class="border-l border-navy/10 pl-3 sm:pl-5">
                        <div class="text-lg sm:text-xl font-bold text-navy tabular-nums">
                            <span data-count-to="2" data-count-suffix="M+">0M+</span>
                        </div>

                        <div class="text-[9.5px] sm:text-[10.5px] text-navy/50 mt-1">
                            Happy Shoppers
                        </div>
                    </div>


                    <div class="border-l border-navy/10 pl-3 sm:pl-5">
                        <div class="flex items-center gap-1.5">
                            <span class="text-lg sm:text-xl font-bold text-navy tabular-nums" data-count-to="4.9" data-count-decimals="1">
                                0.0
                            </span>
                            <x-lucide-star class="w-3.5 h-3.5 text-amber-400 fill-current" />
                        </div>

                        <div class="text-[9.5px] sm:text-[10.5px] text-navy/50 mt-1">
                            App Rating
                        </div>
                    </div>

                </div>

            </div>


            {{-- =================================================
                RIGHT PRODUCT SHOWCASE
            ================================================== --}}
            <div class="relative min-h-82.5 sm:min-h-102.5 lg:min-h-112.5 xlxl:min-h-122.5ero-enter hero-enter-delay">

                {{-- Main showcase glow --}}
                <div class="absolute left-1/2 top-1/2
                            -translate-x-1/2 -translate-y-1/2
                            w-[74%] h-[74%]
                            rounded-full
                            bg-teal/10 blur-3xl">
                </div>

                {{-- Back panel --}}
                <div class="absolute left-1/2 top-1/2
                            -translate-x-1/2 -translate-y-1/2
                            w-[82%] h-[78%]
                            rounded-4xl sm:rounded-[2.5rem]
                            bg-white/50 backdrop-blur-xl
                            border border-white
                            shadow-[0_30px_80px_rgba(15,27,61,0.10)]
                            -rotate-3">
                </div>

                {{-- Main product card --}}
                <div class="absolute z-20
                            left-1/2 top-1/2
                            -translate-x-1/2 -translate-y-1/2
                            w-[66%] max-w-90
                            aspect-[0.92]
                            rounded-[1.75rem] sm:rounded-[2.25rem]
                            bg-white
                            border border-white
                            overflow-hidden
                            shadow-[0_28px_70px_rgba(15,27,61,0.18)]
                            hero-main-product">

                    <img
                        src="{{ asset('images/hero/sneaker.jpg') }}"
                        alt="Featured ShopHop sneaker"
                        class="w-full h-full object-cover scale-[1.03]
                               transition-transform duration-700
                               hover:scale-[1.08]"
                    >

                    <div class="absolute inset-x-0 bottom-0
                                bg-linear-to-t from-navy/75 via-navy/25 to-transparent
                                p-4 sm:p-5 pt-16">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <p class="text-white/60 text-[10px] sm:text-xs uppercase tracking-wider">
                                    Featured Pick
                                </p>
                                <p class="text-white text-sm sm:text-base font-semibold mt-1">
                                    Everyday Sneakers
                                </p>
                            </div>

                            <div class="shrink-0
                                        bg-white text-navy
                                        text-xs sm:text-sm font-bold
                                        px-3 py-2
                                        rounded-full shadow-lg">
                                ₱1,499
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Earbuds floating card --}}
                <div class="absolute z-30
                            left-0 sm:left-3 lg:left-0
                            top-5 sm:top-10 lg:top-12
                            w-28 sm:w-34 lg:w-38
                            rounded-2xl sm:rounded-3xl
                            bg-white/95 backdrop-blur
                            border border-white
                            shadow-[0_18px_50px_rgba(15,27,61,0.14)]
                            overflow-hidden
                            hero-float-soft">

                    <div class="relative">
                        <img
                            src="{{ asset('images/hero/earbuds.jpg') }}"
                            alt="Wireless earbuds"
                            class="w-full h-20 sm:h-24 lg:h-26 object-cover"
                        >

                        <span class="absolute top-2 left-2
                                     bg-navy/85 backdrop-blur
                                     text-white
                                     text-[8px] sm:text-[9px]
                                     font-semibold
                                     px-2 py-1 rounded-full">
                            HOT
                        </span>
                    </div>

                    <div class="px-2.5 py-2.5">
                        <p class="text-[10px] sm:text-xs font-semibold text-navy truncate">
                            Earbuds Pro
                        </p>
                        <div class="flex items-center justify-between mt-1.5">
                            <span class="text-xs sm:text-sm font-bold text-teal-dark">₱1,299</span>
                            <div class="flex items-center gap-1 text-[9px] text-navy/45">
                                <x-lucide-star class="w-3 h-3 text-amber-400 fill-current" />
                                4.8
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Watch floating card --}}
                <div class="absolute z-30
                            right-0 sm:right-3 lg:right-0
                            bottom-4 sm:bottom-7 lg:bottom-10
                            w-28 sm:w-34 lg:w-38
                            rounded-2xl sm:rounded-3xl
                            bg-white/95 backdrop-blur
                            border border-white
                            shadow-[0_18px_50px_rgba(15,27,61,0.14)]
                            overflow-hidden
                            hero-float-soft hero-float-delay">

                    <img
                        src="{{ asset('images/hero/watch.jpg') }}"
                        alt="Fitness watch"
                        class="w-full h-20 sm:h-24 lg:h-26 object-cover"
                    >

                    <div class="px-2.5 py-2.5">
                        <p class="text-[10px] sm:text-xs font-semibold text-navy truncate">
                            Fitness Watch
                        </p>
                        <div class="flex items-center justify-between mt-1.5">
                            <span class="text-xs sm:text-sm font-bold text-teal-dark">₱1,799</span>
                            <span class="text-[8px] sm:text-[9px] text-navy/40">New</span>
                        </div>
                    </div>
                </div>


                {{-- Discount badge --}}
                <div class="absolute z-40
                            right-[3%] sm:right-[8%] lg:right-[5%]
                            top-[8%] sm:top-[11%]
                            w-14 h-14 sm:w-16 sm:h-16
                            rounded-full
                            bg-navy text-white
                            flex flex-col items-center justify-center
                            shadow-xl shadow-navy/20
                            hero-badge-pop">

                    <span class="text-[7px] sm:text-[8px] font-semibold tracking-wider">UP TO</span>
                    <span class="text-sm sm:text-base leading-none font-black text-teal mt-0.5">50%</span>
                    <span class="text-[7px] sm:text-[8px] font-semibold tracking-wider mt-0.5">OFF</span>
                </div>


                {{-- Small trust chip --}}
                <div class="absolute z-40
                            left-[4%] sm:left-[10%] lg:left-[6%]
                            bottom-[5%] sm:bottom-[8%]
                            inline-flex items-center gap-2
                            bg-white/95 backdrop-blur
                            px-3 py-2
                            rounded-xl
                            shadow-lg shadow-navy/10
                            border border-white
                            hero-chip-enter">

                    <span class="w-7 h-7 rounded-lg bg-teal-light
                                 flex items-center justify-center">
                        <x-lucide-shield-check class="w-3.5 h-3.5 text-teal-dark" />
                    </span>

                    <div>
                        <p class="text-[9px] sm:text-[10px] text-navy/40 leading-none">Shop with</p>
                        <p class="text-[10px] sm:text-xs font-semibold text-navy mt-1">Confidence</p>
                    </div>
                </div>

            </div>

        </div>

    </div>
</section>


{{-- =========================================================
    HERO ANIMATIONS
========================================================= --}}
<style>
    @keyframes heroEnter {
        from {
            opacity: 0;
            transform: translateY(22px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes heroFloatSoft {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-8px) rotate(0.7deg);
        }
    }

    @keyframes heroMainFloat {
        0%, 100% {
            margin-top: 0;
        }
        50% {
            margin-top: -8px;
        }
    }

    @keyframes heroBadgePop {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.045);
        }
    }

    @keyframes heroPingSoft {
        75%, 100% {
            transform: scale(2.15);
            opacity: 0;
        }
    }

    .hero-enter {
        opacity: 0;
        animation: heroEnter .75s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .hero-enter-delay {
        animation-delay: .12s;
    }

    .hero-main-product {
        animation: heroMainFloat 5.6s ease-in-out infinite;
    }

    .hero-float-soft {
        animation: heroFloatSoft 5s ease-in-out infinite;
    }

    .hero-float-delay {
        animation-delay: 1.1s;
    }

    .hero-badge-pop {
        animation: heroBadgePop 3.4s ease-in-out infinite;
    }

    .hero-chip-enter {
        opacity: 0;
        animation: heroEnter .7s .38s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .hero-ping {
        animation: heroPingSoft 2s cubic-bezier(0, 0, .2, 1) infinite;
    }

    @media (prefers-reduced-motion: reduce) {
        .hero-enter,
        .hero-main-product,
        .hero-float-soft,
        .hero-badge-pop,
        .hero-chip-enter,
        .hero-ping {
            animation: none !important;
            opacity: 1 !important;
        }
    }
</style>


{{-- =========================================================
    CATEGORIES
========================================================= --}}
@php
    /*
     * Fixed landing-page category list.
     * Guaranteed 14 cards while keeping the existing carousel/slideshow behavior.
     */
    $landingCategories = [
        ['name' => 'Pet Supplies', 'icon' => 'paw-print'],
        ['name' => 'Electronics and Gadgets', 'icon' => 'smartphone'],
        ['name' => 'Women\'s Apparel', 'icon' => 'shirt'],
        ['name' => 'Men\'s Apparel', 'icon' => 'shirt'],
        ['name' => 'Kids and Baby', 'icon' => 'baby'],
        ['name' => 'Home and Garden', 'icon' => 'house'],
        ['name' => 'Sports and Outdoors', 'icon' => 'dumbbell'],
        ['name' => 'Health and Beauty', 'icon' => 'sparkles'],
        ['name' => 'Books and Media', 'icon' => 'book-open'],
        ['name' => 'Food and Gourmet', 'icon' => 'utensils'],
        ['name' => 'Automotive & Motorcycle', 'icon' => 'car'],
        ['name' => 'Furniture and Office Equipment', 'icon' => 'armchair'],
        ['name' => 'Jewelry and Watches', 'icon' => 'gem'],
        ['name' => 'Office and School Supplies', 'icon' => 'notebook-pen'],
    ];

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

<section id="categories" class="py-8 sm:py-10 lg:py-12 bg-white">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-4 sm:mb-5">
            <p class="text-teal-dark text-[10px] sm:text-[11px] font-semibold mb-1.5 tracking-[0.12em]">
                EXPLORE
            </p>

            <p role="heading" aria-level="2" class="text-[20px] sm:text-[22px] lg:text-[24px] leading-tight font-bold text-navy">
                Shop by Category
            </p>

            <p class="text-[11px] sm:text-xs text-navy/50 mt-1.5">
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
                       z-20 w-8 h-8 sm:w-9 sm:h-9
                       rounded-full bg-white border border-gray-border
                       shadow-lg items-center justify-center
                       text-navy hover:text-teal-dark hover:scale-110
                       transition-all duration-200"
            >
                <x-lucide-chevron-left class="w-4 h-4" />
            </button>

            <div
                data-category-track
                class="grid grid-flow-col gap-3
                       overflow-x-auto scroll-smooth
                       snap-x snap-mandatory
                       scrollbar-none
                       [&::-webkit-scrollbar]:hidden
                       auto-cols-[calc((100%-1rem)/2)]
                       sm:auto-cols-[calc((100%-2rem)/3)]
                       md:auto-cols-[calc((100%-3rem)/4)]
                       lg:auto-cols-[calc((100%-6rem)/7)]"
            >
                @foreach ($landingCategories as $category)
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
                        style="--stagger-index: {{ $loop->index % 7 }};"
                        class="group tilt-card stagger-item relative overflow-hidden
                               min-h-38 sm:min-h-40
                               rounded-xl bg-gray-bg
                               px-3 py-4 text-center
                               flex flex-col items-center justify-center
                               border border-transparent
                               hover:border-teal/30
                               hover:bg-teal-light
                               hover:shadow-lg
                               snap-start"
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
                                   w-12 h-12 rounded-xl bg-white/90
                                   flex items-center justify-center
                                   text-teal-dark shadow-sm
                                   group-hover:bg-teal
                                   group-hover:text-white
                                   group-hover:scale-105
                                   transition-all duration-300"
                        >
                            <x-dynamic-component
                                :component="'lucide-' . $category['icon']"
                                class="w-5 h-5"
                            />
                             
                        </div>

                        <span
                            class="relative z-10 block mt-3
                                   text-[10.5px] sm:text-[11.5px]
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
                       z-20 w-8 h-8 sm:w-9 sm:h-9
                       rounded-full bg-white border border-gray-border
                       shadow-lg flex items-center justify-center
                       text-navy hover:text-teal-dark hover:scale-110
                       transition-all duration-200"
            >
                <x-lucide-chevron-right class="w-4 h-4" />
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