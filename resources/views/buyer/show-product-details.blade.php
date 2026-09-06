{{-- Path: resources/views/buyer/product/show-product-details.blade.php --}}

@extends('layouts.app')

@section('title', $product['name'] . ' - ShopHop')

@section('hideChrome', true)


@section('content')

{{-- =========================================================
    BUYER NAVBAR
========================================================= --}}
@include('buyer.partials.navbar-buyer')


{{-- =========================================================
    BREADCRUMB
========================================================= --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-1.5 text-xs text-navy/45 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('buyer.dashboard') }}" class="hover:text-teal-dark transition">Home</a>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0" />
            <span class="text-navy/40">{{ $product['category'] }}</span>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0" />
            <span class="text-navy font-medium truncate max-w-50 sm:max-w-none">{{ $product['name'] }}</span>
        </nav>
    </div>
</div>


{{-- =========================================================
    PRODUCT DETAIL
========================================================= --}}
<section class="py-8 sm:py-10 bg-white">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">

            {{-- IMAGE --}}
            <div>
                <div class="relative aspect-square rounded-2xl overflow-hidden bg-gray-bg border border-gray-border">
                    <img
                        src="{{ $product['image'] }}"
                        alt="{{ $product['name'] }}"
                        class="w-full h-full object-cover"
                    >

                    @if ($product['original_price'])
                        <span class="absolute top-3 left-3 bg-teal text-white text-[11px] font-bold px-2.5 py-1 rounded-md shadow-sm">
                            {{ $product['discount'] }}% OFF
                        </span>
                    @endif

                    <button
                        type="button"
                        title="Add to wishlist"
                        class="absolute top-3 right-3 w-9 h-9 rounded-lg bg-white/95 shadow-sm border border-black/5 flex items-center justify-center text-navy hover:text-teal-dark hover:scale-105 transition"
                    >
                        <x-lucide-heart class="w-4 h-4" />
                    </button>
                </div>
            </div>

            {{-- INFO --}}
            <div class="min-w-0">

                <p class="text-[11px] sm:text-xs font-semibold text-teal-dark tracking-[0.12em] uppercase">
                    {{ $product['category'] }}
                </p>

                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-navy mt-2 leading-snug">
                    {{ $product['name'] }}
                </h1>

                <div class="flex items-center gap-2 mt-3">
                    <span class="text-amber-400 text-sm tracking-tight">★★★★★</span>
                    <span class="text-xs text-navy/45">
                        {{ $product['rating'] }} · {{ $product['reviews'] }} {{ $product['reviews'] === 1 ? 'review' : 'reviews' }}
                    </span>
                </div>

                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 mt-5">
                    <span class="text-2xl sm:text-3xl font-bold text-navy">
                        ₱{{ number_format($product['price']) }}
                    </span>

                    @if ($product['original_price'])
                        <span class="text-sm sm:text-base text-navy/30 line-through">
                            ₱{{ number_format($product['original_price']) }}
                        </span>
                    @endif
                </div>

                <div class="mt-3">
                    @if ($product['in_stock'])
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-dark">
                            <x-lucide-circle-check class="w-3.5 h-3.5" />
                            In Stock ({{ $product['stock'] }} available)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500">
                            <x-lucide-circle-x class="w-3.5 h-3.5" />
                            Out of Stock
                        </span>
                    @endif
                </div>

                {{-- QUANTITY + ACTIONS --}}
                <div class="flex flex-wrap items-center gap-3 mt-6">

                    <div class="flex items-center border border-gray-border rounded-xl overflow-hidden">
                        <button
                            type="button"
                            data-qty-decrease
                            class="w-9 h-9 flex items-center justify-center text-navy hover:bg-gray-bg transition"
                        >
                            <x-lucide-minus class="w-3.5 h-3.5" />
                        </button>

                        <input
                            type="number"
                            data-qty-input
                            value="1"
                            min="1"
                            max="{{ $product['stock'] }}"
                            class="w-12 h-9 text-center text-sm font-semibold text-navy border-x border-gray-border focus:outline-none"
                        >

                        <button
                            type="button"
                            data-qty-increase
                            class="w-9 h-9 flex items-center justify-center text-navy hover:bg-gray-bg transition"
                        >
                            <x-lucide-plus class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <button
                        type="button"
                        {{ ! $product['in_stock'] ? 'disabled' : '' }}
                        class="flex-1 min-w-40 inline-flex items-center justify-center gap-2
                               bg-teal hover:bg-teal-dark disabled:bg-gray-border disabled:cursor-not-allowed
                               text-white text-sm font-semibold
                               px-5 py-2.5 rounded-xl
                               shadow-sm hover:shadow-md
                               transition-all"
                    >
                        <x-lucide-shopping-cart class="w-4 h-4" />
                        Add to Cart
                    </button>

                    <button
                        type="button"
                        {{ ! $product['in_stock'] ? 'disabled' : '' }}
                        class="flex-1 min-w-40 inline-flex items-center justify-center gap-2
                               bg-navy hover:bg-navy/90 disabled:bg-gray-border disabled:cursor-not-allowed
                               text-white text-sm font-semibold
                               px-5 py-2.5 rounded-xl
                               transition-all"
                    >
                        Buy Now
                    </button>

                </div>

                {{-- SELLER --}}
                <div class="flex items-center justify-between gap-3 mt-6 p-4 bg-gray-bg rounded-xl border border-gray-border">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0">
                            <x-lucide-store class="w-4 h-4" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-navy/40">Sold by</p>
                            <p class="text-sm font-semibold text-navy truncate">{{ $product['seller_name'] }}</p>
                        </div>
                    </div>

                    <a
                        href="#"
                        class="text-xs font-semibold text-teal-dark hover:text-navy transition shrink-0"
                    >
                        Visit Store
                    </a>
                </div>

                {{-- DESCRIPTION --}}
                <div class="mt-6 pt-6 border-t border-gray-border">
                    <h2 class="text-sm font-bold text-navy mb-2">Description</h2>
                    <p class="text-sm text-navy/60 leading-relaxed whitespace-pre-line">
                        {{ $product['description'] ?: 'No description provided for this product.' }}
                    </p>
                </div>

            </div>

        </div>

    </div>
</section>


{{-- =========================================================
    RELATED PRODUCTS
========================================================= --}}
@if (count($relatedProducts))
<section class="py-9 sm:py-10 bg-gray-bg border-t border-gray-border/60">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <h2 class="text-lg sm:text-xl font-bold text-navy tracking-tight mb-5">
            You May Also Like
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">

            @foreach ($relatedProducts as $related)
                <article class="group bg-white rounded-xl overflow-hidden border border-gray-border hover:border-teal/30 hover:shadow-lg hover:-translate-y-px transition-all">

                    <a href="{{ route('buyer.product.show', $related['id']) }}" class="block relative aspect-4/3 bg-gray-bg overflow-hidden">
                        <img
                            src="{{ $related['image'] }}"
                            alt="{{ $related['name'] }}"
                            class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300"
                        >

                        @if ($related['original_price'])
                            <span class="absolute top-2 left-2 bg-teal text-white text-[9px] font-bold px-2 py-1 rounded-md shadow-sm">
                                SALE
                            </span>
                        @endif
                    </a>

                    <div class="p-3 sm:p-3.5">
                        <p class="text-[9px] sm:text-[10px] text-navy/40 truncate">
                            {{ $related['category'] }}
                        </p>

                        <h3 class="text-xs sm:text-sm font-semibold text-navy mt-0.5 truncate" title="{{ $related['name'] }}">
                            <a href="{{ route('buyer.product.show', $related['id']) }}" class="hover:text-teal-dark transition">
                                {{ $related['name'] }}
                            </a>
                        </h3>

                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 mt-2">
                            <span class="text-sm font-bold text-navy">
                                ₱{{ number_format($related['price']) }}
                            </span>

                            @if ($related['original_price'])
                                <span class="text-[10px] text-navy/30 line-through">
                                    ₱{{ number_format($related['original_price']) }}
                                </span>
                            @endif
                        </div>

                        <button
                            type="button"
                            class="w-full mt-2.5 flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-[10px] sm:text-[11px] font-semibold py-2 rounded-lg transition"
                        >
                            <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                            Add to Cart
                        </button>
                    </div>

                </article>
            @endforeach

        </div>

    </div>
</section>
@endif


{{-- =========================================================
    FOOTER
========================================================= --}}
@include('partials.footer')

@endsection


{{-- =========================================================
    PAGE-SPECIFIC SCRIPTS
========================================================= --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const decreaseButton = document.querySelector('[data-qty-decrease]');
    const increaseButton = document.querySelector('[data-qty-increase]');
    const qtyInput = document.querySelector('[data-qty-input]');

    if (!decreaseButton || !increaseButton || !qtyInput) {
        return;
    }

    const max = parseInt(qtyInput.max, 10) || 1;

    decreaseButton.addEventListener('click', function () {
        const current = parseInt(qtyInput.value, 10) || 1;
        qtyInput.value = Math.max(1, current - 1);
    });

    increaseButton.addEventListener('click', function () {
        const current = parseInt(qtyInput.value, 10) || 1;
        qtyInput.value = Math.min(max, current + 1);
    });

});
</script>
@endpush