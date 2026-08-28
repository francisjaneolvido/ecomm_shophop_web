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


    @media (prefers-reduced-motion: reduce) {

        .category-slide {
            animation: none;
        }

        .category-slide:first-child {
            opacity: 1;
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

});
</script>

@endpush