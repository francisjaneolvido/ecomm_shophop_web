{{-- =========================================================
    NEW ARRIVALS — PREMIUM EDITORIAL SECTION
========================================================= --}}
<section
    id="new-arrivals"
    class="relative overflow-hidden py-12 sm:py-16 lg:py-18 bg-gray-bg"
>

    {{-- Soft background accents --}}
    <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-teal/7 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-28 -left-28 w-72 h-72 rounded-full bg-navy/4 blur-3xl"></div>

    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-7 sm:mb-9 reveal-up">

            <div class="max-w-xl">

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


            <a
                href="#trending"
                class="group inline-flex items-center gap-2
                       text-sm font-semibold
                       text-teal-dark
                       hover:text-navy
                       transition-colors duration-300"
            >
                View latest products

                <x-lucide-arrow-right
                    class="w-4 h-4
                           transition-transform duration-300
                           group-hover:translate-x-1"
                />
            </a>

        </div>



        {{-- Feature Card --}}
        <div
            class="relative overflow-hidden
                   bg-white
                   rounded-3xl
                   border border-gray-border
                   shadow-sm shadow-navy/5
                   reveal-up"
            style="--reveal-delay: 80ms;"
        >

            <div class="grid lg:grid-cols-[0.88fr_1.12fr] min-h-[430px] lg:min-h-[500px]">


                {{-- =================================================
                    LEFT CONTENT
                ================================================== --}}
                <div
                    class="relative z-10
                           flex flex-col justify-center
                           px-6 sm:px-8 lg:px-10 xl:px-12
                           py-9 sm:py-11 lg:py-12"
                >

                    <span
                        class="inline-flex items-center gap-2
                               w-fit
                               bg-teal-light
                               text-teal-dark
                               text-[10px] sm:text-xs font-semibold
                               px-3.5 py-2
                               rounded-full
                               mb-5"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-teal"></span>
                        NEW THIS WEEK
                    </span>


                    <h2 class="text-navy max-w-md">
                        Find something
                        <span class="block text-teal">
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


                    {{-- Mini benefits --}}
                    <div class="flex flex-wrap gap-x-5 gap-y-3 mt-6">

                        <div class="flex items-center gap-2 text-xs sm:text-sm text-navy/55">
                            <span class="w-7 h-7 rounded-full bg-teal-light flex items-center justify-center">
                                <x-lucide-sparkles class="w-3.5 h-3.5 text-teal-dark" />
                            </span>
                            Fresh picks
                        </div>

                        <div class="flex items-center gap-2 text-xs sm:text-sm text-navy/55">
                            <span class="w-7 h-7 rounded-full bg-teal-light flex items-center justify-center">
                                <x-lucide-package-check class="w-3.5 h-3.5 text-teal-dark" />
                            </span>
                            Just added
                        </div>

                    </div>


                    <a
                        href="#trending"
                        class="group inline-flex items-center justify-center gap-2
                               w-fit
                               mt-7
                               bg-navy hover:bg-[#172750]
                               text-white
                               text-sm font-semibold
                               px-5 sm:px-6
                               py-3 sm:py-3.5
                               rounded-full
                               shadow-lg shadow-navy/10
                               hover:-translate-y-0.5
                               transition-all duration-300"
                    >
                        Discover new arrivals

                        <x-lucide-arrow-right
                            class="w-4 h-4
                                   transition-transform duration-300
                                   group-hover:translate-x-1"
                        />
                    </a>

                </div>



                {{-- =================================================
                    RIGHT PRODUCT COLLAGE
                ================================================== --}}
                <div class="relative min-h-[360px] sm:min-h-[420px] lg:min-h-full bg-[#EEF8F5] overflow-hidden">

                    {{-- Decorative shapes --}}
                    <div class="absolute -top-16 -right-16 w-52 h-52 rounded-full bg-teal/10 blur-2xl"></div>
                    <div class="absolute -bottom-20 -left-12 w-44 h-44 rounded-full bg-white/70 blur-xl"></div>


                    {{-- Main earbuds image --}}
                    <div
                        class="absolute
                               left-[7%] top-[9%]
                               w-[58%]
                               aspect-[1/1.05]
                               rounded-[1.6rem] sm:rounded-[2rem]
                               overflow-hidden
                               bg-white
                               border border-white
                               shadow-[0_22px_60px_rgba(15,27,61,0.12)]
                               new-arrival-float"
                    >
                        <img
                            src="{{ asset('images/hero/earbuds.jpg') }}"
                            alt="New earbuds"
                            class="w-full h-full
                                   object-cover
                                   transition-transform duration-700
                                   hover:scale-105"
                        >
                    </div>


                    {{-- Watch image --}}
                    <div
                        class="absolute
                               right-[7%] bottom-[8%]
                               w-[47%]
                               aspect-square
                               rounded-[1.4rem] sm:rounded-[1.8rem]
                               overflow-hidden
                               bg-white
                               border border-white
                               shadow-[0_20px_50px_rgba(15,27,61,0.12)]
                               new-arrival-float new-arrival-delay"
                    >
                        <img
                            src="{{ asset('images/hero/watch.jpg') }}"
                            alt="New fitness watch"
                            class="w-full h-full
                                   object-cover
                                   transition-transform duration-700
                                   hover:scale-105"
                        >
                    </div>


                    {{-- Floating label --}}
                    <div
                        class="absolute
                               z-20
                               right-[5%] top-[10%]
                               bg-white/95 backdrop-blur
                               rounded-2xl
                               px-3.5 py-3
                               shadow-lg shadow-navy/10
                               border border-white
                               new-arrival-chip"
                    >
                        <div class="flex items-center gap-2.5">

                            <span
                                class="w-9 h-9
                                       rounded-xl
                                       bg-teal-light
                                       flex items-center justify-center"
                            >
                                <x-lucide-trending-up class="w-4 h-4 text-teal-dark" />
                            </span>

                            <div>
                                <p class="text-[9px] sm:text-[10px] text-navy/40">
                                    Fresh drop
                                </p>

                                <p class="text-[10px] sm:text-xs font-semibold text-navy mt-0.5">
                                    This Week
                                </p>
                            </div>

                        </div>
                    </div>


                    {{-- Small badge --}}
                    <div
                        class="absolute
                               z-20
                               left-[5%] bottom-[7%]
                               inline-flex items-center gap-1.5
                               bg-navy
                               text-white
                               text-[9px] sm:text-[10px]
                               font-semibold
                               px-3 py-2
                               rounded-full
                               shadow-lg shadow-navy/15"
                    >
                        <x-lucide-zap class="w-3 h-3 text-teal" />
                        TRENDING NOW
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



{{-- =========================================================
    FINAL CTA — LIGHT / HIGH-CONTRAST
========================================================= --}}
<section class="py-12 sm:py-16 lg:py-18 bg-white">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative
                   overflow-hidden
                   rounded-3xl
                   bg-[#EAF9F5]
                   border border-teal/10
                   px-5 sm:px-8 lg:px-14
                   py-10 sm:py-12 lg:py-14
                   text-center
                   shadow-lg shadow-navy/5
                   reveal-up"
        >

            {{-- Decorations --}}
            <div
                class="pointer-events-none
                       absolute -left-20 -top-20
                       w-48 h-48
                       rounded-full
                       bg-teal/12
                       blur-sm"
            ></div>

            <div
                class="pointer-events-none
                       absolute -right-20 -bottom-20
                       w-48 h-48
                       rounded-full
                       bg-navy/5"
            ></div>

            <div
                class="pointer-events-none
                       absolute left-1/2 top-1/2
                       -translate-x-1/2 -translate-y-1/2
                       w-80 h-32
                       rounded-full
                       bg-white/50
                       blur-3xl"
            ></div>


            <div class="relative max-w-2xl mx-auto">

                <div
                    class="inline-flex items-center gap-2
                           bg-white/80
                           border border-teal/15
                           text-teal-dark
                           text-[10px] sm:text-xs
                           font-semibold
                           tracking-wide
                           px-3.5 py-2
                           rounded-full
                           mb-4
                           shadow-sm"
                >
                    <x-lucide-shopping-bag class="w-3.5 h-3.5" />
                    SHOPHOP
                </div>


                <h2 class="text-navy">
                    Ready to Hop In?
                </h2>


                <p
                    class="text-sm sm:text-base
                           text-navy/55
                           mt-3
                           mb-6 sm:mb-7"
                >
                    Discover your next favorite product today.
                </p>


                <a
                    href="#trending"
                    data-login-required
                    class="group inline-flex
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
                           hover:shadow-xl
                           shadow-lg shadow-teal/20"
                >
                    Start Shopping

                    <x-lucide-arrow-right
                        class="w-4 h-4
                               transition-transform duration-300
                               group-hover:translate-x-1"
                    />
                </a>

            </div>

        </div>

    </div>

</section>


{{-- =========================================================
    LANDING PAGE ANIMATIONS
    Hero animation styles now live with the redesigned hero.
    Keep category slideshow + section animations here.
========================================================= --}}
@push('styles')
<style>

    /* =================================
       CATEGORY BACKGROUND SLIDESHOW
    ================================= */

    @keyframes categorySlideshow {
        0% {
            opacity: 0;
            transform: scale(1.035);
        }

        4% {
            opacity: 1;
            transform: scale(1);
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


    /* =================================
       NEW ARRIVALS FLOATING VISUALS
    ================================= */

    @keyframes newArrivalFloat {
        0%, 100% {
            transform: translateY(0) rotate(0);
        }

        50% {
            transform: translateY(-8px) rotate(.5deg);
        }
    }

    @keyframes newArrivalChip {
        0%, 100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }

    .new-arrival-float {
        animation: newArrivalFloat 5.5s ease-in-out infinite;
    }

    .new-arrival-delay {
        animation-delay: 1.2s;
    }

    .new-arrival-chip {
        animation: newArrivalChip 4.4s ease-in-out infinite;
    }


    /* =================================
       SCROLL REVEAL
    ================================= */

    .reveal-up {
        opacity: 0;
        transform: translateY(18px);
        transition:
            opacity .65s ease var(--reveal-delay, 0ms),
            transform .65s cubic-bezier(.22, 1, .36, 1) var(--reveal-delay, 0ms);
    }

    .reveal-up.is-visible {
        opacity: 1;
        transform: translateY(0);
    }


    /* =================================
       REDUCED MOTION
    ================================= */

    @media (prefers-reduced-motion: reduce) {

        .category-slide,
        .new-arrival-float,
        .new-arrival-chip {
            animation: none !important;
        }

        .category-slide:first-child {
            opacity: 1;
        }

        .reveal-up {
            opacity: 1;
            transform: none;
            transition: none;
        }
    }

</style>
@endpush



@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const revealItems = document.querySelectorAll('.reveal-up');

        if (!revealItems.length) {
            return;
        }

        if (
            window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            revealItems.forEach(function (item) {
                item.classList.add('is-visible');
            });

            return;
        }

        const observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.12
            }
        );

        revealItems.forEach(function (item) {
            observer.observe(item);
        });

    });
</script>
@endpush
