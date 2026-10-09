@extends('layouts.app')

@section('title', 'My Likes — ShopHop')
@section('hideChrome', true)

@section('content')
@include('buyer.partials.navbar-buyer')

<section class="min-h-[70vh] bg-gray-bg/80 py-5 sm:py-7">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
            <div>
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.13em] text-teal-dark">Saved by you</p>
                <h1 class="mt-0.5 text-[22px] sm:text-[28px] font-bold tracking-tight text-navy">My Likes</h1>
                <p class="mt-1 text-[10.5px] sm:text-[11.5px] text-navy/45">
                    Your liked products are shared across ShopHop web and mobile.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 rounded-xl border border-gray-border bg-white px-3 py-2 shadow-sm">
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center">
                    <x-lucide-heart class="w-4 h-4 fill-current" />
                </span>
                <div>
                    <p data-favorite-count class="text-sm font-bold leading-none text-navy">{{ $favoriteCount }}</p>
                    <p class="text-[8.5px] text-navy/40 mt-1">saved item{{ $favoriteCount === 1 ? '' : 's' }}</p>
                </div>
            </div>
        </div>

        <div class="mb-4 flex flex-col sm:flex-row gap-2">
            <label class="relative flex-1 max-w-xl">
                <x-lucide-search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/35" />
                <input
                    type="search"
                    data-likes-search
                    placeholder="Search your liked products"
                    class="w-full h-10 rounded-xl border border-gray-border bg-white pl-10 pr-3 text-[11px] text-navy outline-none focus:border-teal/60 focus:ring-2 focus:ring-teal/10"
                >
            </label>
            <select data-likes-filter class="h-10 rounded-xl border border-gray-border bg-white px-3 text-[11px] text-navy outline-none focus:border-teal/60">
                <option value="all">All liked products</option>
                <option value="available">In stock</option>
                <option value="discount">On sale</option>
            </select>
        </div>

        <div data-favorites-grid class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2.5 sm:gap-3">
            @foreach ($favorites as $product)
                <article
                    data-favorite-card="{{ $product['id'] }}"
                    data-like-search="{{ strtolower($product['name'] . ' ' . $product['category'] . ' ' . $product['seller_name']) }}"
                    data-like-available="{{ $product['is_available'] ? '1' : '0' }}"
                    data-like-discount="{{ $product['discount_percent'] > 0 ? '1' : '0' }}"
                    class="group overflow-hidden rounded-2xl border border-gray-border bg-white hover:border-teal/35 hover:shadow-md transition-all"
                >
                    <div class="relative aspect-square overflow-hidden bg-gray-bg">
                        @if ($product['is_available'])
                            <a href="{{ route('buyer.product.show', $product['id']) }}" class="block w-full h-full">
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300">
                            </a>
                        @else
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover grayscale-[35%] opacity-80">
                        @endif

                        @if ($product['discount_percent'] > 0)
                            <span class="absolute left-2 top-2 rounded-md bg-rose-500 px-2 py-1 text-[8px] font-bold text-white">
                                -{{ $product['discount_percent'] }}%
                            </span>
                        @endif

                        <button
                            type="button"
                            data-favorite-toggle
                            data-product-id="{{ $product['id'] }}"
                            data-liked="1"
                            aria-pressed="true"
                            aria-label="Remove from My Likes"
                            title="Remove from My Likes"
                            class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full border border-black/5 bg-white/95 text-rose-500 shadow-sm hover:scale-105 transition"
                        >
                            <x-lucide-heart class="w-4 h-4 fill-current" />
                        </button>

                        @unless ($product['is_available'])
                            <span class="absolute inset-x-2 bottom-2 rounded-lg bg-navy/85 px-2 py-1.5 text-center text-[8px] font-semibold text-white">Currently unavailable</span>
                        @endunless
                    </div>

                    <div class="p-3">
                        <p class="text-[8.5px] text-navy/35 truncate">{{ $product['category'] }}</p>
                        @if ($product['is_available'])
                            <a href="{{ route('buyer.product.show', $product['id']) }}" class="mt-0.5 block min-h-8 text-[10.5px] sm:text-[11px] font-semibold leading-snug text-navy line-clamp-2 hover:text-teal-dark transition">
                                {{ $product['name'] }}
                            </a>
                        @else
                            <p class="mt-0.5 min-h-8 text-[10.5px] sm:text-[11px] font-semibold leading-snug text-navy/65 line-clamp-2">{{ $product['name'] }}</p>
                        @endif

                        <div class="mt-2 flex items-end justify-between gap-2">
                            <div class="min-w-0">
                                @if ($product['original_price'])
                                    <p class="text-[8px] text-navy/30 line-through">₱{{ number_format($product['original_price'], 2) }}</p>
                                @endif
                                <p class="text-[13px] font-bold text-teal-dark">₱{{ number_format($product['price'], 2) }}</p>
                            </div>
                            @if ($product['rating'] > 0)
                                <span class="shrink-0 text-[8.5px] text-navy/40 flex items-center gap-0.5">
                                    <x-lucide-star class="w-2.5 h-2.5 fill-amber-400 text-amber-400" />
                                    {{ number_format($product['rating'], 1) }}
                                </span>
                            @endif
                        </div>

                        <p class="mt-1.5 text-[8.5px] text-navy/35 truncate">
                            {{ $product['seller_location'] ?: $product['seller_name'] }}
                            @if ($product['sold'] > 0) · {{ $product['sold'] }} sold @endif
                        </p>
                    </div>
                </article>
            @endforeach
        </div>

        <div data-favorites-empty class="{{ $favoriteCount > 0 ? 'hidden' : '' }} rounded-2xl border border-dashed border-gray-border bg-white py-14 px-5 text-center">
            <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-50 text-rose-400 flex items-center justify-center">
                <x-lucide-heart class="w-7 h-7" />
            </div>
            <h2 class="mt-4 text-base font-bold text-navy">No liked products yet</h2>
            <p class="mt-1 text-[10.5px] text-navy/45">Tap a heart on a product and it will appear here on both web and mobile.</p>
            <a href="{{ route('buyer.dashboard') }}" class="mt-4 inline-flex h-9 items-center justify-center rounded-lg bg-teal px-4 text-[10px] font-semibold text-white hover:bg-teal-dark transition">
                Browse products
            </a>
        </div>

        <div data-likes-no-results class="hidden rounded-2xl border border-dashed border-gray-border bg-white py-10 px-5 text-center text-[11px] text-navy/45">
            No liked products match this filter.
        </div>
    </div>
</section>

@include('partials.footer')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.querySelector('[data-likes-search]');
    const filter = document.querySelector('[data-likes-filter]');
    const noResults = document.querySelector('[data-likes-no-results]');

    function applyLikesFilter() {
        const q = (search?.value || '').trim().toLowerCase();
        const mode = filter?.value || 'all';
        let visible = 0;

        document.querySelectorAll('[data-favorite-card]').forEach(function (card) {
            const matchesSearch = !q || (card.dataset.likeSearch || '').includes(q);
            const matchesMode = mode === 'all'
                || (mode === 'available' && card.dataset.likeAvailable === '1')
                || (mode === 'discount' && card.dataset.likeDiscount === '1');
            const show = matchesSearch && matchesMode;
            card.classList.toggle('hidden', !show);
            if (show) visible += 1;
        });

        if (noResults) {
            const hasAnyCards = document.querySelectorAll('[data-favorite-card]').length > 0;
            noResults.classList.toggle('hidden', !hasAnyCards || visible > 0);
        }
    }

    search?.addEventListener('input', applyLikesFilter);
    filter?.addEventListener('change', applyLikesFilter);
});
</script>
@endpush
