{{-- =========================================================
    NEW ARRIVALS — PREMIUM EDITORIAL SECTION
========================================================= --}}
<section
    id="new-arrivals"
    class="relative overflow-hidden py-8 sm:py-10 lg:py-12 bg-gray-bg"
>

    {{-- Soft background accents --}}
    <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-teal/7 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-28 -left-28 w-72 h-72 rounded-full bg-navy/4 blur-3xl"></div>

    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-4 sm:mb-5 reveal-up">

            <div class="max-w-xl">

                <p
                    class="text-teal-dark
                           text-[10px] sm:text-[11px]
                           font-semibold
                           mb-1.5
                           tracking-[0.12em]"
                >
                    JUST IN
                </p>

                <p role="heading" aria-level="2" class="text-[20px] sm:text-[22px] lg:text-[24px] leading-tight font-bold text-navy">
                    New Arrivals
                </p>

                <p class="text-[11px] sm:text-xs text-navy/50 mt-1.5">
                    Fresh products and exciting finds added to ShopHop.
                </p>

            </div>


            <a
                href="#trending"
                class="group inline-flex items-center gap-2
                       text-[11px] sm:text-xs font-semibold
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
                   rounded-2xl
                   border border-gray-border
                   shadow-sm shadow-navy/5
                   reveal-up"
            style="--reveal-delay: 80ms;"
        >

            <div class="grid lg:grid-cols-[0.88fr_1.12fr] min-h-90 lg:min-h-105">


                {{-- =================================================
                    LEFT CONTENT
                ================================================== --}}
                <div
                    class="relative z-10
                           flex flex-col justify-center
                           px-5 sm:px-6 lg:px-8 xl:px-9
                           py-6 sm:py-7 lg:py-8"
                >

                    <span
                        class="inline-flex items-center gap-2
                               w-fit
                               bg-teal-light
                               text-teal-dark
                               text-[9.5px] sm:text-[10.5px] font-semibold
                               px-3 py-1.5
                               rounded-full
                               mb-4"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-teal"></span>
                        NEW THIS WEEK
                    </span>


                    <p role="heading" aria-level="2" class="text-[24px] sm:text-[28px] lg:text-[32px] leading-[1.12] tracking-[-0.02em] font-bold text-navy max-w-md">
                        Find something
                        <span class="block text-teal">
                            you'll love.
                        </span>
                    </p>


                    <p
                        class="text-[12px] sm:text-[13px] lg:text-sm
                               text-navy/55
                               mt-3
                               leading-relaxed
                               max-w-lg"
                    >
                        From everyday essentials to the latest trends,
                        there's always something new waiting for you.
                    </p>


                    {{-- Mini benefits --}}
                    <div class="flex flex-wrap gap-x-4 gap-y-2.5 mt-4 sm:mt-5">

                        <div class="flex items-center gap-1.5 text-[10.5px] sm:text-xs text-navy/55">
                            <span class="w-6 h-6 rounded-lg bg-teal-light flex items-center justify-center">
                                <x-lucide-sparkles class="w-3 h-3 text-teal-dark" />
                            </span>
                            Fresh picks
                        </div>

                        <div class="flex items-center gap-1.5 text-[10.5px] sm:text-xs text-navy/55">
                            <span class="w-6 h-6 rounded-lg bg-teal-light flex items-center justify-center">
                                <x-lucide-package-check class="w-3 h-3 text-teal-dark" />
                            </span>
                            Just added
                        </div>

                    </div>


                    <a
                        href="#trending"
                        class="group magnetic ripple-surface inline-flex items-center justify-center gap-2
                               w-fit
                               mt-5 sm:mt-6
                               bg-teal hover:bg-teal-dark
                               text-white
                               text-xs sm:text-[13px] font-semibold
                               px-5 sm:px-6
                               py-2.5 sm:py-3
                               rounded-xl
                               shadow-md shadow-teal/15
                               transition-colors duration-300"
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
                <div class="relative min-h-75 sm:min-h-87.5 lg:min-h-full bg-[#EEF8F5] overflow-hidden">

                    {{-- Decorative shapes --}}
                    <div class="absolute -top-16 -right-16 w-52 h-52 rounded-full bg-teal/10 blur-2xl"></div>
                    <div class="absolute -bottom-20 -left-12 w-44 h-44 rounded-full bg-white/70 blur-xl"></div>


                    {{-- Main earbuds image --}}
                    <div
                        class="absolute
                               left-[7%] top-[9%]
                               w-[58%]
                               aspect-[1/1.05]
                               rounded-[1.6rem] sm:rounded-4xl
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
                               rounded-xl
                               px-3 py-2.5
                               shadow-lg shadow-navy/10
                               border border-white
                               new-arrival-chip"
                    >
                        <div class="flex items-center gap-2.5">

                            <span
                                class="w-8 h-8
                                       rounded-lg
                                       bg-teal-light
                                       flex items-center justify-center"
                            >
                                <x-lucide-trending-up class="w-3.5 h-3.5 text-teal-dark" />
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
                               px-2.5 py-1.5
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
<section class="py-8 sm:py-10 lg:py-12 bg-white">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            data-parallax-container
            class="relative
                   overflow-hidden
                   rounded-2xl
                   bg-[#EAF9F5]
                   border border-teal/10
                   px-5 sm:px-7 lg:px-10
                   py-7 sm:py-8 lg:py-9
                   text-center
                   shadow-lg shadow-navy/5
                   reveal-up"
        >

            {{-- Decorations --}}
            <div
                data-parallax="16"
                class="pointer-events-none
                       absolute -left-20 -top-20
                       w-48 h-48
                       rounded-full
                       bg-teal/12
                       blur-sm"
            ></div>

            <div
                data-parallax="-12"
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
                           text-[9.5px] sm:text-[10.5px]
                           font-semibold
                           tracking-[0.12em]
                           px-3 py-1.5
                           rounded-full
                           mb-3
                           shadow-sm"
                >
                    <x-lucide-shopping-bag class="w-3.5 h-3.5" />
                    SHOPHOP
                </div>


                <p role="heading" aria-level="2" class="text-[22px] sm:text-[24px] lg:text-[26px] leading-tight font-bold text-navy">
                    Ready to Hop In?
                </p>


                <p
                    class="text-[12px] sm:text-[13px]
                           text-navy/55
                           mt-2
                           mb-5 sm:mb-6"
                >
                    Discover your next favorite product today.
                </p>


                <a
                    href="#trending"
                    data-login-required
                    class="group magnetic ripple-surface inline-flex
                           items-center justify-center gap-2
                           bg-teal
                           hover:bg-teal-dark
                           text-white
                           text-xs sm:text-[13px] font-semibold
                           px-5 sm:px-6
                           py-2.5 sm:py-3
                           rounded-xl
                           transition-colors duration-300
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

        const prefersReducedMotion = window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /* =================================
           SCROLL REVEAL (single fades + staggered groups)
        ================================= */
        const revealItems = document.querySelectorAll('.reveal-up, .stagger-item');

        if (revealItems.length) {
            if (prefersReducedMotion) {
                revealItems.forEach(function (item) {
                    item.classList.add('is-visible');
                });
            } else {
                const revealObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            revealObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });

                revealItems.forEach(function (item) {
                    revealObserver.observe(item);
                });
            }
        }

        /* =================================
           ANIMATED STAT COUNTERS
        ================================= */
        const countTargets = document.querySelectorAll('[data-count-to]');

        if (countTargets.length) {
            countTargets.forEach(function (el) {
                const target = parseFloat(el.dataset.countTo);
                const suffix = el.dataset.countSuffix || '';
                const decimals = el.dataset.countDecimals ? parseInt(el.dataset.countDecimals, 10) : 0;
                const duration = 1400;

                if (prefersReducedMotion) {
                    el.textContent = (decimals ? target.toFixed(decimals) : Math.round(target)) + suffix;
                    return;
                }

                const countObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;
                        countObserver.unobserve(entry.target);

                        const start = performance.now();

                        function tick(now) {
                            const progress = Math.min((now - start) / duration, 1);
                            const eased = 1 - Math.pow(1 - progress, 3);
                            const current = target * eased;

                            el.textContent = (decimals ? current.toFixed(decimals) : Math.round(current)) + suffix;

                            if (progress < 1) {
                                requestAnimationFrame(tick);
                            }
                        }

                        requestAnimationFrame(tick);
                    });
                }, { threshold: 0.4 });

                countObserver.observe(el);
            });
        }

        /* =================================
           DEAL COUNTDOWN
        ================================= */
        document.querySelectorAll('[data-countdown]').forEach(function (el) {
            function update() {
                const now = new Date();
                const end = new Date(now);
                end.setHours(24, 0, 0, 0);

                const diff = Math.max(0, end - now);
                const h = String(Math.floor(diff / 3.6e6)).padStart(2, '0');
                const m = String(Math.floor((diff % 3.6e6) / 6e4)).padStart(2, '0');
                const s = String(Math.floor((diff % 6e4) / 1000)).padStart(2, '0');

                el.textContent = h + ':' + m + ':' + s;
            }

            update();
            setInterval(update, 1000);
        });

        /* =================================
           WISHLIST HEART POP
        ================================= */
        document.querySelectorAll('.wishlist-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const wished = btn.getAttribute('data-wished') === 'true';
                btn.setAttribute('data-wished', String(!wished));

                if (prefersReducedMotion) return;

                btn.classList.remove('heart-pop');
                void btn.offsetWidth; // restart the animation
                btn.classList.add('heart-pop');
            });
        });

        /* =================================
           RIPPLE PRESS FEEDBACK
        ================================= */
        document.querySelectorAll('.ripple-surface').forEach(function (surface) {
            surface.addEventListener('click', function (e) {
                if (prefersReducedMotion) return;

                const rect = surface.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const ripple = document.createElement('span');

                ripple.className = 'ripple';
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
                ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';

                surface.appendChild(ripple);
                ripple.addEventListener('animationend', function () {
                    ripple.remove();
                });
            });
        });

        if (prefersReducedMotion) {
            return;
        }

        /* =================================
           MAGNETIC BUTTONS
        ================================= */
        document.querySelectorAll('.magnetic').forEach(function (btn) {
            btn.addEventListener('mousemove', function (e) {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;

                btn.style.transform = 'translate(' + (x * 0.18) + 'px, ' + (y * 0.35) + 'px)';
            });

            btn.addEventListener('mouseleave', function () {
                btn.style.transform = '';
            });
        });

        /* =================================
           TILT CARDS (category tiles)
        ================================= */
        document.querySelectorAll('.tilt-card').forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                const rect = card.getBoundingClientRect();
                const px = (e.clientX - rect.left) / rect.width;
                const py = (e.clientY - rect.top) / rect.height;
                const rotateX = (0.5 - py) * 10;
                const rotateY = (px - 0.5) * 12;

                card.style.transform = 'perspective(700px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateY(-4px)';
            });

            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
            });
        });

        /* =================================
           BACKGROUND PARALLAX LAYERS
           (safe on elements with no other
           transform-based positioning)
        ================================= */
        document.querySelectorAll('[data-parallax-container]').forEach(function (container) {
            const layers = container.querySelectorAll('[data-parallax]');

            if (!layers.length) return;

            container.addEventListener('mousemove', function (e) {
                const rect = container.getBoundingClientRect();
                const px = (e.clientX - rect.left) / rect.width - 0.5;
                const py = (e.clientY - rect.top) / rect.height - 0.5;

                layers.forEach(function (layer) {
                    const speed = parseFloat(layer.dataset.parallax) || 10;
                    layer.style.transform = 'translate(' + (px * speed) + 'px, ' + (py * speed) + 'px)';
                });
            });

            container.addEventListener('mouseleave', function () {
                layers.forEach(function (layer) {
                    layer.style.transform = '';
                });
            });
        });

    });
</script>
@endpush