{{-- Path: resources/views/buyer/dashboard/dashboard.blade.php --}}

@extends('layouts.app')

@section('title', 'My ShopHop')

@section('hideChrome', true)


@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BUYER DISPLAY NAME
    |--------------------------------------------------------------------------
    | Prefer the dedicated buyer profile record when available.
    */

    $authBuyerUser = auth()->user();

    $buyerName = $authBuyerUser?->buyer?->first_name
        ?? $authBuyerUser?->first_name
        ?? ($buyerName ?? null)
        ?? $authBuyerUser?->name
        ?? 'Buyer';
@endphp


@include('buyer.partials.navbar-buyer')

@include('buyer.dashboard.d-part-1')

@include('buyer.dashboard.d-part-2')

<div id="dashboardCartToast" role="status" aria-live="polite"
    class="fixed left-1/2 bottom-5 z-60 -translate-x-1/2 translate-y-6 opacity-0 pointer-events-none bg-navy text-white text-[9.5px] font-medium px-3 py-2 rounded-lg shadow-lg transition-all duration-200">
    <span data-dashboard-cart-status></span>
</div>

@include('partials.footer')

@endsection


@push('styles')

<style>
    /*
    |--------------------------------------------------------------------------
    | BUYER DASHBOARD MOTION / SHARED VISUALS
    |--------------------------------------------------------------------------
    */

    .buyer-card {
        will-change: transform;
    }

    .buyer-product-shelf {
        grid-auto-columns: minmax(160px, 44%);
        scrollbar-width: none;
    }

    @media (min-width: 640px) {
        .buyer-product-shelf {
            grid-auto-columns: minmax(175px, 30%);
        }
    }

    @media (min-width: 768px) {
        .buyer-product-shelf {
            grid-auto-columns: minmax(185px, 23%);
        }
    }

    @media (min-width: 1024px) {
        .buyer-product-shelf {
            grid-auto-columns: minmax(190px, 19%);
        }
    }

    @media (min-width: 1280px) {
        .buyer-product-shelf {
            grid-auto-columns: minmax(195px, 16.4%);
        }
    }

    .buyer-product-shelf::-webkit-scrollbar {
        display: none;
    }


    /*
    |--------------------------------------------------------------------------
    | PROMO SLIDER
    |--------------------------------------------------------------------------
    */

    .buyer-promo-slide {
        opacity: 0;
        visibility: hidden;
        transform: scale(1.012);
        transition:
            opacity .55s ease,
            visibility .55s ease,
            transform 5.5s ease;
    }

    .buyer-promo-slide.is-active {
        opacity: 1;
        visibility: visible;
        transform: scale(1);
        z-index: 1;
    }

    .buyer-promo-slide img {
        transform: scale(1.04);
        transition: transform 5.5s ease;
    }

    .buyer-promo-slide.is-active img {
        transform: scale(1);
    }

    .buyer-promo-dot {
        width: .4rem;
        background: rgba(255, 255, 255, .45);
        transition:
            width .25s ease,
            background-color .25s ease;
    }

    .buyer-promo-dot.is-active {
        width: 1.35rem;
        background: #2ECFA6;
    }


    /*
    |--------------------------------------------------------------------------
    | VOUCHER CARD DETAILS
    |--------------------------------------------------------------------------
    */

    .buyer-voucher-card::before,
    .buyer-voucher-card::after {
        content: '';
        position: absolute;
        top: 62%;
        width: 12px;
        height: 12px;
        border-radius: 999px;
        background: #F4F7F8;
        border: 1px solid rgba(46, 207, 166, .12);
        transform: translateY(-50%);
    }

    .buyer-voucher-card::before {
        left: -7px;
    }

    .buyer-voucher-card::after {
        right: -7px;
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSIBILITY / REDUCED MOTION
    |--------------------------------------------------------------------------
    */

    @media (prefers-reduced-motion: reduce) {
        .buyer-promo-slide,
        .buyer-promo-slide img,
        .buyer-promo-dot {
            transition: none !important;
        }
    }
</style>

@endpush


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const motionReduced =
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const dashboardCartToast = document.getElementById('dashboardCartToast');
    const dashboardCartStatus = document.querySelector('[data-dashboard-cart-status]');
    // Card data is only a session-cart hint; checkout must re-read price, stock, and ownership from persisted Product.
    const dashboardCartAddUrl = @json(route('buyer.cart.add'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let dashboardCartToastTimer = null;

    function updateCartCountBadges(count) {
        document.querySelectorAll('[data-cart-count]').forEach(function (badge) {
            badge.textContent = count;
        });
    }

    function showDashboardCartStatus(message) {
        if (!dashboardCartToast || !dashboardCartStatus) return;

        dashboardCartStatus.textContent = message;
        dashboardCartToast.classList.remove('translate-y-6', 'opacity-0');
        dashboardCartToast.classList.add('translate-y-0', 'opacity-100');
        clearTimeout(dashboardCartToastTimer);
        dashboardCartToastTimer = setTimeout(function () {
            dashboardCartToast.classList.remove('translate-y-0', 'opacity-100');
            dashboardCartToast.classList.add('translate-y-6', 'opacity-0');
        }, 2200);
    }

    document.querySelectorAll('[data-dashboard-add-to-cart]').forEach(function (button) {
        button.addEventListener('click', async function () {
            if (button.dataset.cartAdding === 'true') return;

            const price = Number(button.dataset.cartPrice);
            if (!button.dataset.cartProductId || !button.dataset.cartName || !Number.isFinite(price)) {
                console.error('Dashboard cart payload is incomplete.');
                showDashboardCartStatus('Could not add this item to cart.');
                return;
            }

            button.dataset.cartAdding = 'true';
            button.disabled = true;

            try {
                const response = await fetch(dashboardCartAddUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        product_id: button.dataset.cartProductId,
                        variant_id: null,
                        variant_label: 'Standard',
                        name: button.dataset.cartName,
                        image: button.dataset.cartImage,
                        price: price,
                        original_price: button.dataset.cartOriginalPrice
                            ? Number(button.dataset.cartOriginalPrice)
                            : null,
                        stock: button.dataset.cartStock
                            ? Number(button.dataset.cartStock)
                            : null,
                        qty: 1,
                        shop_id: button.dataset.cartShopId || null,
                        shop_name: button.dataset.cartShopName || null,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Dashboard add to cart failed: ' + response.status);
                }

                const data = await response.json();
                if (typeof data?.cart_count !== 'number') {
                    throw new Error('Dashboard add to cart returned no cart count.');
                }

                updateCartCountBadges(data.cart_count);
                showDashboardCartStatus('Added to cart. Cart has ' + data.cart_count + ' item(s).');
            } catch (error) {
                console.error(error);
                showDashboardCartStatus('Could not add this item to cart.');
            } finally {
                delete button.dataset.cartAdding;
                button.disabled = false;
            }
        });
    });


    /*
    |--------------------------------------------------------------------------
    | CATEGORY SLIDER
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-category-slider]')
        .forEach(function (slider) {

            const track =
                slider.querySelector('[data-category-track]');

            const previousButton =
                slider.querySelector('[data-category-prev]');

            const nextButton =
                slider.querySelector('[data-category-next]');

            if (!track || !previousButton || !nextButton) {
                return;
            }

            function updateButtons() {
                const maxScrollLeft =
                    track.scrollWidth - track.clientWidth;

                const atStart =
                    track.scrollLeft <= 5;

                const atEnd =
                    track.scrollLeft >= maxScrollLeft - 5;

                previousButton.classList.toggle(
                    'hidden',
                    atStart
                );

                previousButton.classList.toggle(
                    'flex',
                    !atStart
                );

                const hideNext =
                    atEnd ||
                    maxScrollLeft <= 5;

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

            nextButton.addEventListener('click', function () {
                track.scrollBy({
                    left: scrollAmount(),
                    behavior: motionReduced ? 'auto' : 'smooth'
                });
            });

            previousButton.addEventListener('click', function () {
                track.scrollBy({
                    left: -scrollAmount(),
                    behavior: motionReduced ? 'auto' : 'smooth'
                });
            });

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
    | MAIN PROMO SLIDER
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-buyer-promo-slider]')
        .forEach(function (slider) {

            const slides = Array.from(
                slider.querySelectorAll(
                    '[data-buyer-promo-slide]'
                )
            );

            const dots = Array.from(
                slider.querySelectorAll(
                    '[data-buyer-promo-dot]'
                )
            );

            const previousButton =
                slider.querySelector(
                    '[data-buyer-promo-prev]'
                );

            const nextButton =
                slider.querySelector(
                    '[data-buyer-promo-next]'
                );

            if (slides.length <= 1) {
                return;
            }

            let currentIndex = 0;
            let autoTimer = null;

            function showSlide(index) {
                currentIndex =
                    (index + slides.length) %
                    slides.length;

                slides.forEach(
                    function (slide, slideIndex) {

                        const active =
                            slideIndex === currentIndex;

                        slide.classList.toggle(
                            'is-active',
                            active
                        );

                        slide.setAttribute(
                            'aria-hidden',
                            active ? 'false' : 'true'
                        );
                    }
                );

                dots.forEach(
                    function (dot, dotIndex) {

                        dot.classList.toggle(
                            'is-active',
                            dotIndex === currentIndex
                        );
                    }
                );
            }

            function stopAutoPlay() {
                if (autoTimer) {
                    window.clearInterval(autoTimer);
                    autoTimer = null;
                }
            }

            function startAutoPlay() {
                stopAutoPlay();

                if (motionReduced) {
                    return;
                }

                autoTimer =
                    window.setInterval(
                        function () {
                            showSlide(currentIndex + 1);
                        },
                        4800
                    );
            }

            previousButton?.addEventListener(
                'click',
                function () {
                    showSlide(currentIndex - 1);
                    startAutoPlay();
                }
            );

            nextButton?.addEventListener(
                'click',
                function () {
                    showSlide(currentIndex + 1);
                    startAutoPlay();
                }
            );

            dots.forEach(
                function (dot, dotIndex) {

                    dot.addEventListener(
                        'click',
                        function () {
                            showSlide(dotIndex);
                            startAutoPlay();
                        }
                    );
                }
            );

            slider.addEventListener(
                'mouseenter',
                stopAutoPlay
            );

            slider.addEventListener(
                'mouseleave',
                startAutoPlay
            );

            slider.addEventListener(
                'focusin',
                stopAutoPlay
            );

            slider.addEventListener(
                'focusout',
                startAutoPlay
            );

            showSlide(0);
            startAutoPlay();
        });


    /*
    |--------------------------------------------------------------------------
    | PRODUCT SHELVES
    |--------------------------------------------------------------------------
    |
    | Buttons are located in the section header.
    | We bind each button pair to the nearest shelf in its section.
    |
    */

    document
        .querySelectorAll('[data-product-shelf]')
        .forEach(function (shelf) {

            const section =
                shelf.closest('section');

            const track =
                shelf.querySelector(
                    '[data-product-shelf-track]'
                );

            const previousButton =
                section?.querySelector(
                    '[data-product-shelf-prev]'
                );

            const nextButton =
                section?.querySelector(
                    '[data-product-shelf-next]'
                );

            if (!track) {
                return;
            }

            function amount() {
                return Math.max(
                    220,
                    track.clientWidth * 0.78
                );
            }

            previousButton?.addEventListener(
                'click',
                function () {
                    track.scrollBy({
                        left: -amount(),
                        behavior:
                            motionReduced
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
                            motionReduced
                                ? 'auto'
                                : 'smooth'
                    });
                }
            );
        });


    /*
    |--------------------------------------------------------------------------
    | FLASH SALE COUNTDOWN
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-shop-countdown]')
        .forEach(function (element) {

            let remaining =
                Number(
                    element.getAttribute(
                        'data-countdown-seconds'
                    ) || 0
                );

            function renderCountdown() {
                const safeRemaining =
                    Math.max(0, remaining);

                const hours =
                    Math.floor(
                        safeRemaining / 3600
                    );

                const minutes =
                    Math.floor(
                        (safeRemaining % 3600) / 60
                    );

                const seconds =
                    safeRemaining % 60;

                element.textContent =
                    String(hours).padStart(2, '0') +
                    ' : ' +
                    String(minutes).padStart(2, '0') +
                    ' : ' +
                    String(seconds).padStart(2, '0');

                if (remaining > 0) {
                    remaining--;
                }
            }

            renderCountdown();

            window.setInterval(
                renderCountdown,
                1000
            );
        });


    /*
    |--------------------------------------------------------------------------
    | REVEAL MOTION
    |--------------------------------------------------------------------------
    */

    const revealTargets =
        document.querySelectorAll(
            '.buyer-reveal, .buyer-stagger'
        );

    if (motionReduced) {
        revealTargets.forEach(
            function (item) {
                item.classList.add(
                    'is-visible'
                );
            }
        );

        return;
    }

    if ('IntersectionObserver' in window) {
        const revealObserver =
            new IntersectionObserver(
                function (entries) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {
                                entry.target.classList.add(
                                    'is-visible'
                                );

                                revealObserver.unobserve(
                                    entry.target
                                );
                            }
                        }
                    );
                },
                {
                    threshold: 0.08,
                    rootMargin:
                        '0px 0px -18px 0px'
                }
            );

        revealTargets.forEach(
            function (item) {
                revealObserver.observe(item);
            }
        );
    } else {
        revealTargets.forEach(
            function (item) {
                item.classList.add(
                    'is-visible'
                );
            }
        );
    }
});
</script>

@endpush
