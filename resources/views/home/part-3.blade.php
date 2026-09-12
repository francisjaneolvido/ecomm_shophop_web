{{-- =========================================================
    SHOPHOP LANDING — PART 3
    New arrivals + marketplace identity + final CTA + interactions
========================================================= --}}

@php
    $landingEditorial = [
        [
            'image' => 'images/category_icons_bg/furniture_office/tablelamps.jpg',
            'label' => 'HOME & OFFICE',
            'title' => 'Give your everyday spaces a small refresh.',
        ],
        [
            'image' => 'images/category_icons_bg/electronics_gadgets/cctv.jpg',
            'label' => 'SMART LIVING',
            'title' => 'Simple tech for a more connected home.',
        ],
        [
            'image' => 'images/category_icons_bg/food_gourmet/chips.jpg',
            'label' => 'FOOD & GOURMET',
            'title' => 'Stock the snack drawer with something fun.',
        ],
    ];
@endphp


{{-- =========================================================
    NEW ARRIVALS — PUBLIC EDITORIAL
========================================================= --}}
<section id="new-arrivals" class="scroll-mt-20 bg-gray-bg py-7 sm:py-8 lg:py-9 border-b border-gray-border/70">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-4 reveal-up">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">
                    JUST IN
                </p>

                <h2 class="mt-0.5 text-[19px] sm:text-[21px] lg:text-[23px] font-bold text-navy">
                    Fresh Finds, Different Corners
                </h2>

                <p class="mt-1 text-[10px] sm:text-[11px] text-navy/45">
                    The landing page stays broad; your buyer dashboard becomes personal after sign-in.
                </p>
            </div>

            <a
                href="#trending"
                class="hidden sm:inline-flex items-center gap-1.5 text-[10.5px] font-semibold text-teal-dark hover:text-navy transition"
            >
                Browse products
                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>
        </div>


        <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-3">

            {{-- FEATURE IMAGE --}}
            <a
                href="#categories"
                class="group relative min-h-[330px] sm:min-h-[390px] overflow-hidden rounded-2xl border border-gray-border bg-white reveal-up"
            >
                <img
                    src="{{ asset($landingEditorial[0]['image']) }}"
                    alt="{{ $landingEditorial[0]['title'] }}"
                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-700"
                >

                <div class="absolute inset-0 bg-gradient-to-r from-navy/92 via-navy/52 to-navy/5"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-navy/35 via-transparent to-transparent"></div>

                <div class="relative z-10 h-full min-h-[330px] sm:min-h-[390px] p-5 sm:p-7 flex flex-col justify-end max-w-[72%]">

                    <span class="inline-flex w-fit rounded-md bg-teal px-2 py-1 text-[8px] font-bold tracking-[0.1em] text-white">
                        {{ $landingEditorial[0]['label'] }}
                    </span>

                    <p class="mt-3 text-[23px] sm:text-[29px] lg:text-[32px] font-extrabold leading-[1.05] tracking-[-0.03em] text-white">
                        {{ $landingEditorial[0]['title'] }}
                    </p>

                    <p class="mt-2 text-[9.5px] sm:text-[10.5px] leading-relaxed text-white/55">
                        ShopHop is built to help visitors discover across the marketplace before narrowing down what they want.
                    </p>

                    <span class="mt-4 inline-flex items-center gap-1.5 text-[10px] font-semibold text-teal">
                        Explore categories
                        <x-lucide-arrow-right class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" />
                    </span>
                </div>
            </a>


            {{-- TWO STACKED EDITORIAL CARDS --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-3">

                @foreach (array_slice($landingEditorial, 1) as $index => $card)
                    <a
                        href="{{ $index === 0 ? '#trending' : '#categories' }}"
                        style="--reveal-delay: {{ ($index + 1) * 70 }}ms;"
                        class="group relative min-h-[190px] overflow-hidden rounded-2xl border border-gray-border bg-white reveal-up"
                    >
                        <img
                            src="{{ asset($card['image']) }}"
                            alt="{{ $card['title'] }}"
                            class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.05] transition-transform duration-700"
                        >

                        <div class="absolute inset-0 bg-gradient-to-t from-navy/90 via-navy/20 to-transparent"></div>

                        <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
                            <span class="text-[8px] font-bold tracking-[0.12em] text-teal">
                                {{ $card['label'] }}
                            </span>

                            <p class="mt-1.5 max-w-sm text-[15px] sm:text-[17px] font-bold leading-tight text-white">
                                {{ $card['title'] }}
                            </p>
                        </div>
                    </a>
                @endforeach

            </div>
        </div>
    </div>
</section>


{{-- =========================================================
    LANDING-ONLY DISTINCTION: WHAT HAPPENS AFTER SIGN-IN
========================================================= --}}
<section class="bg-white py-7 sm:py-8 lg:py-9 border-b border-gray-border/70">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid lg:grid-cols-[.82fr_1.18fr] gap-4 items-center">

            <div class="reveal-up">
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.12em] text-teal-dark">
                    ONE MARKETPLACE, DIFFERENT EXPERIENCES
                </p>

                <h2 class="mt-1 text-[22px] sm:text-[26px] lg:text-[29px] font-extrabold leading-[1.08] tracking-[-0.025em] text-navy">
                    Browse publicly.
                    <span class="block text-teal">
                        Shop personally.
                    </span>
                </h2>

                <p class="mt-3 max-w-md text-[10px] sm:text-[11px] leading-relaxed text-navy/45">
                    The landing page introduces ShopHop and helps anyone explore.
                    After a buyer signs in, the experience shifts to orders, cart,
                    wishlist, vouchers, recently viewed items, and personalized recommendations.
                </p>

                <a
                    href="#trending"
                    data-login-required
                    class="group ripple-surface mt-5 inline-flex items-center gap-2 rounded-xl bg-teal px-4 py-2.5 text-[10px] sm:text-[11px] font-bold text-white hover:bg-teal-dark transition"
                >
                    Sign In to Shop
                    <x-lucide-log-in class="w-3.5 h-3.5" />
                </a>
            </div>


            <div class="grid sm:grid-cols-3 gap-2.5 buyer-stagger">

                <div class="rounded-2xl border border-gray-border bg-gray-bg/70 p-4 sm:p-5">
                    <span class="flex w-10 h-10 items-center justify-center rounded-xl bg-white text-teal-dark shadow-sm">
                        <x-lucide-compass class="w-4.5 h-4.5" />
                    </span>

                    <p class="mt-3 text-[11px] sm:text-[12px] font-bold text-navy">
                        Landing
                    </p>

                    <p class="mt-1 text-[8.5px] sm:text-[9.5px] leading-relaxed text-navy/40">
                        Brand, categories, deals, trending products, and discovery.
                    </p>
                </div>


                <div class="rounded-2xl border border-teal/20 bg-teal-light/45 p-4 sm:p-5">
                    <span class="flex w-10 h-10 items-center justify-center rounded-xl bg-white text-teal-dark shadow-sm">
                        <x-lucide-shopping-bag class="w-4.5 h-4.5" />
                    </span>

                    <p class="mt-3 text-[11px] sm:text-[12px] font-bold text-navy">
                        Buyer
                    </p>

                    <p class="mt-1 text-[8.5px] sm:text-[9.5px] leading-relaxed text-navy/40">
                        Personal orders, cart, wishlist, vouchers, history, and recommendations.
                    </p>
                </div>


                <div class="rounded-2xl border border-gray-border bg-gray-bg/70 p-4 sm:p-5">
                    <span class="flex w-10 h-10 items-center justify-center rounded-xl bg-white text-teal-dark shadow-sm">
                        <x-lucide-store class="w-4.5 h-4.5" />
                    </span>

                    <p class="mt-3 text-[11px] sm:text-[12px] font-bold text-navy">
                        Seller
                    </p>

                    <p class="mt-1 text-[8.5px] sm:text-[9.5px] leading-relaxed text-navy/40">
                        Store management, inventory, orders, reports, and storefront tools.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>


{{-- =========================================================
    FINAL CTA
========================================================= --}}
<section class="bg-gray-bg py-7 sm:py-8 lg:py-9">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative overflow-hidden rounded-3xl bg-navy p-6 sm:p-8 lg:p-10 reveal-up"
            data-parallax-container
        >
            <img
                src="{{ asset('images/category_icons_bg/electronics_gadgets/cctv.jpg') }}"
                alt=""
                class="absolute inset-y-0 right-0 hidden md:block w-[42%] h-full object-cover opacity-25"
            >

            <div class="absolute inset-0 bg-gradient-to-r from-navy via-navy/95 to-navy/55"></div>

            <div class="absolute -left-20 -top-20 w-52 h-52 rounded-full bg-teal/15 blur-2xl" data-parallax="12"></div>
            <div class="absolute right-[26%] -bottom-24 w-56 h-56 rounded-full bg-sky/10 blur-2xl" data-parallax="-10"></div>


            <div class="relative max-w-xl">

                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[8.5px] font-bold tracking-[0.12em] text-teal">
                    <x-lucide-shopping-bag class="w-3 h-3" />
                    SHOPHOP
                </span>

                <h2 class="mt-4 text-[24px] sm:text-[29px] lg:text-[32px] font-extrabold leading-[1.06] tracking-[-0.03em] text-white">
                    Ready to find your next favorite?
                </h2>

                <p class="mt-2.5 max-w-lg text-[10px] sm:text-[11px] leading-relaxed text-white/55">
                    Explore the marketplace now, then sign in when you are ready to save products, add to cart, and manage purchases.
                </p>

                <div class="mt-5 flex flex-wrap gap-2.5">

                    <a
                        href="#trending"
                        data-login-required
                        class="group ripple-surface inline-flex items-center gap-2 rounded-xl bg-teal px-4 py-2.5 text-[10px] sm:text-[11px] font-bold text-white hover:bg-teal-dark transition"
                    >
                        Sign In & Start Shopping
                        <x-lucide-arrow-right class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" />
                    </a>

                    <a
                        href="#categories"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-[10px] sm:text-[11px] font-semibold text-white/80 hover:bg-white/15 hover:text-white transition"
                    >
                        Browse First
                    </a>

                </div>
            </div>
        </div>
    </div>
</section>


{{-- =========================================================
    LANDING PAGE STYLES
========================================================= --}}
@push('styles')

<style>
    /*
    |--------------------------------------------------------------------------
    | STAGGER / REVEAL
    |--------------------------------------------------------------------------
    */

    .reveal-up,
    .stagger-item {
        opacity: 0;
        transform: translateY(16px);
        transition:
            opacity .6s ease var(--reveal-delay, calc(var(--stagger-index, 0) * 55ms)),
            transform .6s cubic-bezier(.22, 1, .36, 1) var(--reveal-delay, calc(var(--stagger-index, 0) * 55ms));
    }

    .reveal-up.is-visible,
    .stagger-item.is-visible {
        opacity: 1;
        transform: translateY(0);
    }


    /*
    |--------------------------------------------------------------------------
    | HERO MOTION
    |--------------------------------------------------------------------------
    */

    @keyframes landingPing {
        75%, 100% {
            transform: scale(2.1);
            opacity: 0;
        }
    }

    @keyframes landingFloatA {
        0%, 100% { transform: translateY(0) rotate(-1deg); }
        50% { transform: translateY(-7px) rotate(.5deg); }
    }

    @keyframes landingFloatB {
        0%, 100% { transform: translateY(0) rotate(1deg); }
        50% { transform: translateY(7px) rotate(-.5deg); }
    }

    @keyframes landingFloatC {
        0%, 100% { transform: translateY(0) rotate(.5deg); }
        50% { transform: translateY(-5px) rotate(-1deg); }
    }

    @keyframes landingFloatD {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    @keyframes landingChip {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    .landing-ping {
        animation: landingPing 2s cubic-bezier(0, 0, .2, 1) infinite;
    }

    .landing-float-a {
        animation: landingFloatA 6s ease-in-out infinite;
    }

    .landing-float-b {
        animation: landingFloatB 5.2s ease-in-out infinite;
    }

    .landing-float-c {
        animation: landingFloatC 5.8s ease-in-out infinite .6s;
    }

    .landing-float-d {
        animation: landingFloatD 6.4s ease-in-out infinite .9s;
    }

    .landing-chip {
        animation: landingChip 4.5s ease-in-out infinite;
    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY PHOTO SLIDESHOW
    |--------------------------------------------------------------------------
    */

    @keyframes landingCategorySlide {
        0% {
            opacity: 0;
            transform: scale(1.035);
        }

        7% {
            opacity: 1;
            transform: scale(1);
        }

        24% {
            opacity: 1;
            transform: scale(1);
        }

        31%,
        100% {
            opacity: 0;
            transform: scale(1.02);
        }
    }

    .landing-category-slide {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        animation-name: landingCategorySlide;
        animation-timing-function: ease-in-out;
        animation-iteration-count: infinite;
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCT SHELF
    |--------------------------------------------------------------------------
    */

    .landing-product-shelf {
        grid-auto-columns: minmax(160px, 44%);
        scrollbar-width: none;
    }

    .landing-product-shelf::-webkit-scrollbar {
        display: none;
    }

    @media (min-width: 640px) {
        .landing-product-shelf {
            grid-auto-columns: minmax(178px, 30%);
        }
    }

    @media (min-width: 768px) {
        .landing-product-shelf {
            grid-auto-columns: minmax(185px, 23%);
        }
    }

    @media (min-width: 1024px) {
        .landing-product-shelf {
            grid-auto-columns: minmax(190px, 19%);
        }
    }

    @media (min-width: 1280px) {
        .landing-product-shelf {
            grid-auto-columns: minmax(190px, 16%);
        }
    }

    .landing-quick-view {
        opacity: 0;
        transform: translateY(8px);
        transition:
            opacity .2s ease,
            transform .2s ease;
    }

    article:hover .landing-quick-view {
        opacity: 1;
        transform: translateY(0);
    }


    /*
    |--------------------------------------------------------------------------
    | WISHLIST FEEDBACK
    |--------------------------------------------------------------------------
    */

    @keyframes landingHeartPop {
        0% { transform: scale(1); }
        45% { transform: scale(1.25); }
        100% { transform: scale(1); }
    }

    .heart-pop {
        animation: landingHeartPop .28s ease;
    }


    /*
    |--------------------------------------------------------------------------
    | RIPPLE
    |--------------------------------------------------------------------------
    */

    .ripple-surface {
        position: relative;
        overflow: hidden;
    }

    .ripple {
        position: absolute;
        border-radius: 999px;
        background: rgba(255, 255, 255, .28);
        transform: scale(0);
        animation: landingRipple .5s ease-out;
        pointer-events: none;
    }

    @keyframes landingRipple {
        to {
            transform: scale(2.4);
            opacity: 0;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REDUCED MOTION
    |--------------------------------------------------------------------------
    */

    @media (prefers-reduced-motion: reduce) {
        .reveal-up,
        .stagger-item {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }

        .landing-ping,
        .landing-float-a,
        .landing-float-b,
        .landing-float-c,
        .landing-float-d,
        .landing-chip,
        .landing-category-slide,
        .heart-pop,
        .ripple {
            animation: none !important;
        }

        .landing-category-slide:first-child {
            opacity: 1;
        }
    }
</style>

@endpush


{{-- =========================================================
    LANDING PAGE SCRIPTS
========================================================= --}}
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const reducedMotion =
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;


    /*
    |--------------------------------------------------------------------------
    | SCROLL REVEAL
    |--------------------------------------------------------------------------
    */

    const revealItems =
        document.querySelectorAll(
            '.reveal-up, .stagger-item'
        );

    if (reducedMotion) {
        revealItems.forEach(function (item) {
            item.classList.add('is-visible');
        });
    } else if ('IntersectionObserver' in window) {

        const revealObserver =
            new IntersectionObserver(
                function (entries) {

                    entries.forEach(
                        function (entry) {

                            if (!entry.isIntersecting) {
                                return;
                            }

                            entry.target.classList.add(
                                'is-visible'
                            );

                            revealObserver.unobserve(
                                entry.target
                            );
                        }
                    );
                },
                {
                    threshold: 0.08,
                    rootMargin: '0px 0px -18px 0px'
                }
            );

        revealItems.forEach(function (item) {
            revealObserver.observe(item);
        });

    } else {
        revealItems.forEach(function (item) {
            item.classList.add('is-visible');
        });
    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY SLIDER
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-landing-category-slider]')
        .forEach(function (slider) {

            const track =
                slider.querySelector(
                    '[data-landing-category-track]'
                );

            const previousButton =
                slider.querySelector(
                    '[data-landing-category-prev]'
                );

            const nextButton =
                slider.querySelector(
                    '[data-landing-category-next]'
                );

            if (!track || !previousButton || !nextButton) {
                return;
            }

            function updateButtons() {
                const maxScroll =
                    track.scrollWidth -
                    track.clientWidth;

                const atStart =
                    track.scrollLeft <= 5;

                const atEnd =
                    track.scrollLeft >= maxScroll - 5;

                previousButton.classList.toggle(
                    'hidden',
                    atStart
                );

                previousButton.classList.toggle(
                    'flex',
                    !atStart
                );

                const hideNext =
                    maxScroll <= 5 || atEnd;

                nextButton.classList.toggle(
                    'hidden',
                    hideNext
                );

                nextButton.classList.toggle(
                    'flex',
                    !hideNext
                );
            }

            function scrollAmount() {
                return track.clientWidth * 0.92;
            }

            previousButton.addEventListener(
                'click',
                function () {
                    track.scrollBy({
                        left: -scrollAmount(),
                        behavior:
                            reducedMotion
                                ? 'auto'
                                : 'smooth'
                    });
                }
            );

            nextButton.addEventListener(
                'click',
                function () {
                    track.scrollBy({
                        left: scrollAmount(),
                        behavior:
                            reducedMotion
                                ? 'auto'
                                : 'smooth'
                    });
                }
            );

            track.addEventListener(
                'scroll',
                updateButtons,
                { passive: true }
            );

            window.addEventListener(
                'resize',
                updateButtons
            );

            updateButtons();
        });


    /*
    |--------------------------------------------------------------------------
    | TRENDING PRODUCT SHELF
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-landing-product-shelf]')
        .forEach(function (shelf) {

            const section =
                shelf.closest('section');

            const track =
                shelf.querySelector(
                    '[data-landing-product-track]'
                );

            const previousButton =
                section?.querySelector(
                    '[data-landing-shelf-prev]'
                );

            const nextButton =
                section?.querySelector(
                    '[data-landing-shelf-next]'
                );

            if (!track) {
                return;
            }

            function amount() {
                return Math.max(
                    220,
                    track.clientWidth * 0.8
                );
            }

            previousButton?.addEventListener(
                'click',
                function () {
                    track.scrollBy({
                        left: -amount(),
                        behavior:
                            reducedMotion
                                ? 'auto'
                                : 'smooth'
                    });
                }
            );

            nextButton?.addEventListener(
                'click',
                function () {
                    track.scrollBy({
                        left: amount(),
                        behavior:
                            reducedMotion
                                ? 'auto'
                                : 'smooth'
                    });
                }
            );
        });


    /*
    |--------------------------------------------------------------------------
    | DAILY DEAL COUNTDOWN
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-countdown]')
        .forEach(function (element) {

            function updateCountdown() {
                const now =
                    new Date();

                const end =
                    new Date(now);

                end.setHours(
                    24,
                    0,
                    0,
                    0
                );

                const difference =
                    Math.max(
                        0,
                        end - now
                    );

                const hours =
                    String(
                        Math.floor(
                            difference / 3.6e6
                        )
                    ).padStart(2, '0');

                const minutes =
                    String(
                        Math.floor(
                            (difference % 3.6e6) / 6e4
                        )
                    ).padStart(2, '0');

                const seconds =
                    String(
                        Math.floor(
                            (difference % 6e4) / 1000
                        )
                    ).padStart(2, '0');

                element.textContent =
                    hours +
                    ':' +
                    minutes +
                    ':' +
                    seconds;
            }

            updateCountdown();

            window.setInterval(
                updateCountdown,
                1000
            );
        });


    /*
    |--------------------------------------------------------------------------
    | WISHLIST HEART FEEDBACK
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.wishlist-btn')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    const wished =
                        button.getAttribute(
                            'data-wished'
                        ) === 'true';

                    button.setAttribute(
                        'data-wished',
                        String(!wished)
                    );

                    if (reducedMotion) {
                        return;
                    }

                    button.classList.remove(
                        'heart-pop'
                    );

                    void button.offsetWidth;

                    button.classList.add(
                        'heart-pop'
                    );
                }
            );
        });


    /*
    |--------------------------------------------------------------------------
    | RIPPLE
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.ripple-surface')
        .forEach(function (surface) {

            surface.addEventListener(
                'click',
                function (event) {

                    if (reducedMotion) {
                        return;
                    }

                    const rectangle =
                        surface.getBoundingClientRect();

                    const size =
                        Math.max(
                            rectangle.width,
                            rectangle.height
                        );

                    const ripple =
                        document.createElement(
                            'span'
                        );

                    ripple.className =
                        'ripple';

                    ripple.style.width =
                        size + 'px';

                    ripple.style.height =
                        size + 'px';

                    ripple.style.left =
                        (
                            event.clientX -
                            rectangle.left -
                            size / 2
                        ) + 'px';

                    ripple.style.top =
                        (
                            event.clientY -
                            rectangle.top -
                            size / 2
                        ) + 'px';

                    surface.appendChild(
                        ripple
                    );

                    ripple.addEventListener(
                        'animationend',
                        function () {
                            ripple.remove();
                        }
                    );
                }
            );
        });


    /*
    |--------------------------------------------------------------------------
    | LIGHT PARALLAX
    |--------------------------------------------------------------------------
    */

    if (!reducedMotion) {

        document
            .querySelectorAll('[data-parallax-container]')
            .forEach(function (container) {

                const layers =
                    container.querySelectorAll(
                        '[data-parallax]'
                    );

                if (!layers.length) {
                    return;
                }

                container.addEventListener(
                    'mousemove',
                    function (event) {

                        const rectangle =
                            container.getBoundingClientRect();

                        const positionX =
                            (
                                event.clientX -
                                rectangle.left
                            ) /
                            rectangle.width -
                            0.5;

                        const positionY =
                            (
                                event.clientY -
                                rectangle.top
                            ) /
                            rectangle.height -
                            0.5;

                        layers.forEach(
                            function (layer) {

                                const speed =
                                    Number(
                                        layer.dataset.parallax
                                    ) || 10;

                                layer.style.transform =
                                    'translate(' +
                                    (positionX * speed) +
                                    'px, ' +
                                    (positionY * speed) +
                                    'px)';
                            }
                        );
                    }
                );

                container.addEventListener(
                    'mouseleave',
                    function () {

                        layers.forEach(
                            function (layer) {
                                layer.style.transform = '';
                            }
                        );
                    }
                );
            });
    }
});
</script>

@endpush
