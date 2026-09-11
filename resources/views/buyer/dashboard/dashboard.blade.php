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

    /* Category slideshow now runs on the shared .kb-stack /
       .kb-photo "moving photos" system in app.css, and the
       buyer-reveal / buyer-stagger / buyer-ping motion utilities
       also now live in app.css so every buyer page (dashboard,
       category browse, etc.) can reuse them. Nothing page-specific
       left to scope here. */

    .buyer-card {
        will-change: transform;
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