{{-- Path: resources/views/buyer/dashboard.blade.php --}}

@extends('layouts.app')

@section('title', 'My ShopHop')

{{--
    We hide the default guest navbar/footer from layouts.app
    because this page uses the Buyer-specific navbar.
--}}
@section('hideChrome', true)


@section('content')

@php
    $buyer = auth()->user();

    $buyerName = $buyer?->first_name
        ?? $buyer?->name
        ?? 'Buyer';
@endphp


{{-- =========================================================
    BUYER NAVBAR
========================================================= --}}
@include('buyer.partials.navbar-buyer')


{{-- Main dashboard sections --}}
@include('buyer.dashboard.d-part-1')
@include('buyer.dashboard.d-part-2')



{{-- =========================================================
    FOOTER

    layouts.app is hiding the normal footer because this page
    sets hideChrome=true, so we manually reuse the same footer.
========================================================= --}}
@include('partials.footer')


@endsection


{{-- =========================================================
    PAGE-SPECIFIC STYLES
========================================================= --}}
@push('styles')

<style>

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

        animation:
            categorySlideshow
            12s
            ease-in-out
            infinite;
    }




    /* =========================================================
       BUYER DASHBOARD MOTION / MICRO-INTERACTIONS
    ========================================================= */

    @keyframes buyerPingSoft {
        75%, 100% {
            transform: scale(2.1);
            opacity: 0;
        }
    }

    .buyer-ping {
        animation: buyerPingSoft 2s cubic-bezier(0, 0, .2, 1) infinite;
    }

    .buyer-reveal {
        opacity: 0;
        transform: translateY(18px);
        transition:
            opacity .65s ease var(--buyer-delay, 0ms),
            transform .65s cubic-bezier(.22, 1, .36, 1) var(--buyer-delay, 0ms);
    }

    .buyer-reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .buyer-stagger > * {
        opacity: 0;
        transform: translateY(14px);
        transition:
            opacity .55s ease,
            transform .55s cubic-bezier(.22, 1, .36, 1);
    }

    .buyer-stagger.is-visible > * {
        opacity: 1;
        transform: translateY(0);
    }

    .buyer-stagger.is-visible > *:nth-child(2) { transition-delay: 45ms; }
    .buyer-stagger.is-visible > *:nth-child(3) { transition-delay: 90ms; }
    .buyer-stagger.is-visible > *:nth-child(4) { transition-delay: 135ms; }
    .buyer-stagger.is-visible > *:nth-child(5) { transition-delay: 180ms; }
    .buyer-stagger.is-visible > *:nth-child(6) { transition-delay: 225ms; }
    .buyer-stagger.is-visible > *:nth-child(7) { transition-delay: 270ms; }
    .buyer-stagger.is-visible > *:nth-child(8) { transition-delay: 315ms; }

    .buyer-card {
        will-change: transform;
    }


    @media (prefers-reduced-motion: reduce) {

        .category-slide {
            animation: none;
        }

        .category-slide:first-child {
            opacity: 1;
        }

        .buyer-reveal,
        .buyer-stagger > * {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }

        .buyer-ping {
            animation: none !important;
        }

    }

</style>

@endpush


{{-- =========================================================
    PAGE-SPECIFIC SCRIPTS
========================================================= --}}
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | CATEGORY SLIDER
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-category-slider]')
        .forEach(function (slider) {

            const track =
                slider.querySelector(
                    '[data-category-track]'
                );

            const previousButton =
                slider.querySelector(
                    '[data-category-prev]'
                );

            const nextButton =
                slider.querySelector(
                    '[data-category-next]'
                );


            if (
                !track ||
                !previousButton ||
                !nextButton
            ) {
                return;
            }


            function updateButtons() {

                const maxScrollLeft =
                    track.scrollWidth -
                    track.clientWidth;

                const atStart =
                    track.scrollLeft <= 5;

                const atEnd =
                    track.scrollLeft >=
                    maxScrollLeft - 5;


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
                return track.clientWidth * 0.95;
            }


            nextButton.addEventListener(
                'click',
                function () {

                    track.scrollBy({
                        left: scrollAmount(),
                        behavior: 'smooth'
                    });

                }
            );


            previousButton.addEventListener(
                'click',
                function () {

                    track.scrollBy({
                        left: -scrollAmount(),
                        behavior: 'smooth'
                    });

                }
            );


            track.addEventListener(
                'scroll',
                updateButtons,
                {
                    passive: true
                }
            );


            window.addEventListener(
                'resize',
                updateButtons
            );


            updateButtons();

        });


    /*
    |--------------------------------------------------------------------------
    | BUYER DASHBOARD REVEALS
    |--------------------------------------------------------------------------
    */

    const motionReduced =
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const revealTargets = document.querySelectorAll('.buyer-reveal, .buyer-stagger');

    if (motionReduced) {
        revealTargets.forEach(function (item) {
            item.classList.add('is-visible');
        });
    } else if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.12,
                rootMargin: '0px 0px -24px 0px'
            }
        );

        revealTargets.forEach(function (item) {
            revealObserver.observe(item);
        });
    } else {
        revealTargets.forEach(function (item) {
            item.classList.add('is-visible');
        });
    }

});
</script>

@endpush