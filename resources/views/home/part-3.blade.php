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
                    data-login-required
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


{{-- =========================================================
    HERO / CATEGORY ANIMATIONS
    (Login modal has moved to resources/views/auth/modals/login-modal.blade.php
    — include it once in your layout instead of here.)
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