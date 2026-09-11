{{-- Path: resources/views/buyer/category/show.blade.php --}}

@extends('layouts.app')

@section('title', ($activeCategory['name'] ?? 'Category') . ' — ShopHop')

{{--
    Same pattern as the buyer dashboard: this page uses the
    Buyer-specific navbar instead of the guest chrome.
--}}
@section('hideChrome', true)


@section('content')

@php
    // ---------------------------------------------------------------
    // Fallback sample data so this view also renders in isolation.
    // In production these all come from the CategoryController, which
    // should read from config('shophop_categories') — see
    // config/shophop_categories.php for the full category tree.
    // ---------------------------------------------------------------
    $categoryTree ??= config('shophop_categories', []);

    $activeSlug ??= request()->route('category') ?? request()->route('slug');

    $activeCategoryData ??= collect($categoryTree)
        ->first(fn ($cat) => $cat['slug'] === $activeSlug) ?? ($categoryTree[0] ?? null);

    $activeCategory ??= $activeCategoryData ? [
        'name' => $activeCategoryData['name'],
        'slug' => $activeCategoryData['slug'],
        'icon' => $activeCategoryData['icon'],
    ] : [
        'name' => 'Pet Supplies',
        'slug' => 'pet-supplies',
        'icon' => 'paw-print',
    ];

    $breadcrumbs ??= [
        ['label' => 'All Categories', 'url' => \Illuminate\Support\Facades\Route::has('buyer.category.index') ? route('buyer.category.index') : '#'],
        ['label' => $activeCategory['name'], 'url' => null],
    ];

    $subcategories ??= $activeCategoryData ? collect($activeCategoryData['subcategories'])
        ->map(fn ($subName, $i) => [
            'name' => $subName,
            'slug' => $activeCategoryData['slug'] . '/' . \Illuminate\Support\Str::slug($subName),
            'active' => $i === 0,
        ])->values()->all() : [];

    $subcategoriesMoreCount ??= 0;

    $filters ??= [
        'shipped_from' => ['Domestic', 'Overseas', 'Metro Manila', 'North Luzon'],
        'brands' => ['ShopHop Picks', 'Ohana', 'Deoplus', 'Veze'],
        'ratings' => [4, 3, 2, 1],
        'shipping_options' => ['Free Shipping', 'Cash on Delivery'],
    ];

    $activeFilters ??= [];

    $sortOptions ??= [
        'popular' => 'Popular',
        'latest' => 'Latest',
        'top_sales' => 'Top Sales',
    ];

    $currentSort ??= 'popular';

    $currentPage ??= 1;
    $lastPage ??= 1;

    // ---------------------------------------------------------------
    // TEMPORARY PREVIEW PRODUCTS
    // Remove this block once CategoryController is already returning
    // real products. It only fills the grid when $products is missing
    // or empty, so you can immediately preview the category layout.
    // ---------------------------------------------------------------
    if (! isset($products) || (is_countable($products) && count($products) === 0)) {
        $products = [
            [
                'id' => 1,
                'name' => 'Premium Grain-Free Dog Food',
                'price' => 649,
                'image' => 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => 12,
                'badge' => 'Best Seller',
                'rating' => 4.9,
                'sold_count' => 328,
            ],
            [
                'id' => 2,
                'name' => 'Soft Adjustable Pet Harness',
                'price' => 299,
                'image' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => 8,
                'badge' => 'Popular',
                'rating' => 4.8,
                'sold_count' => 215,
            ],
            [
                'id' => 3,
                'name' => 'Cat Scratching Post with Toy',
                'price' => 459,
                'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => null,
                'badge' => 'New',
                'rating' => 4.7,
                'sold_count' => 96,
            ],
            [
                'id' => 4,
                'name' => 'Stainless Pet Feeding Bowl',
                'price' => 189,
                'image' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => 5,
                'badge' => null,
                'rating' => 4.6,
                'sold_count' => 174,
            ],
            [
                'id' => 5,
                'name' => 'Natural Dog Treats Chicken Bites',
                'price' => 229,
                'image' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => 15,
                'badge' => 'Sale',
                'rating' => 4.9,
                'sold_count' => 441,
            ],
            [
                'id' => 6,
                'name' => 'Pet Grooming Brush',
                'price' => 149,
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => null,
                'badge' => null,
                'rating' => 4.5,
                'sold_count' => 88,
            ],
            [
                'id' => 7,
                'name' => 'Comfortable Washable Pet Bed',
                'price' => 799,
                'image' => 'https://images.unsplash.com/photo-1560807707-8cc77767d783?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => 10,
                'badge' => 'Top Rated',
                'rating' => 4.8,
                'sold_count' => 154,
            ],
            [
                'id' => 8,
                'name' => 'Interactive Cat Ball Toy',
                'price' => 119,
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => null,
                'badge' => null,
                'rating' => 4.6,
                'sold_count' => 267,
            ],
            [
                'id' => 9,
                'name' => 'Portable Pet Water Bottle',
                'price' => 259,
                'image' => 'https://images.unsplash.com/photo-1537151608828-ea2b11777ee8?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => 7,
                'badge' => null,
                'rating' => 4.7,
                'sold_count' => 132,
            ],
            [
                'id' => 10,
                'name' => 'Durable Rope Chew Toy Set',
                'price' => 199,
                'image' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=700&q=80',
                'discount_percent' => 9,
                'badge' => 'Value Pick',
                'rating' => 4.8,
                'sold_count' => 303,
            ],
        ];
    }
@endphp


{{-- =========================================================
    BUYER NAVBAR
========================================================= --}}
@include('buyer.partials.navbar-buyer')


{{-- =========================================================
    BREADCRUMB
========================================================= --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-3 sm:px-4 lg:px-5 py-2">
        <nav class="flex items-center flex-wrap gap-x-1.5 gap-y-1 text-[10px] sm:text-[10.5px] text-navy/45">
            @foreach ($breadcrumbs as $crumb)
                @if (! $loop->last && $crumb['url'])
                    <a href="{{ $crumb['url'] }}" class="hover:text-teal-dark transition">{{ $crumb['label'] }}</a>
                    <x-lucide-chevron-right class="w-3 h-3 text-navy/25" />
                @else
                    <span class="text-navy font-medium">{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </nav>
    </div>
</div>


{{-- =========================================================
    CATEGORY BROWSE — SIDEBAR + FILTERS + PRODUCT GRID
========================================================= --}}
<section class="bg-gray-bg">
    <div class="max-w-310 mx-auto px-3 sm:px-4 lg:px-5 py-3.5 sm:py-4">

        <div class="grid lg:grid-cols-[205px_minmax(0,1fr)] gap-4 items-start">

            {{-- =================================================
                LEFT: CATEGORY TREE + SEARCH FILTER
            ================================================== --}}
            <aside class="buyer-reveal lg:sticky lg:top-3 flex flex-col gap-3">

                {{-- Category tree --}}
                <div class="bg-white rounded-xl border border-gray-border/90 shadow-sm overflow-hidden">

                    {{-- IMPORTANT: use <p>, not <h2>, so the global h2 desktop rule does not enlarge this sidebar title. --}}
                    <div class="flex items-center gap-2 px-3 py-2.5 border-b border-gray-border/80 bg-white">
                        <span class="w-6 h-6 rounded-md bg-teal-light flex items-center justify-center shrink-0">
                            <x-lucide-list class="w-3.5 h-3.5 text-teal-dark" />
                        </span>
                        <p class="text-[12px] leading-none font-bold text-navy whitespace-nowrap">All Categories</p>
                    </div>

                    <nav class="py-1.5">
                        @foreach ($subcategories as $sub)
                            <a
                                href="{{ route('buyer.category.show', $activeCategory['slug']) }}?sub={{ \Illuminate\Support\Str::slug($sub['name']) }}"
                                class="group flex items-center gap-1.5 px-3 py-1.75 border-l-2 text-[10.5px] leading-[1.3] transition-colors
                                       {{ $sub['active']
                                           ? 'border-teal bg-teal-light/55 text-teal-dark font-semibold'
                                           : 'border-transparent text-navy/60 hover:border-teal/40 hover:bg-gray-bg/80 hover:text-teal-dark' }}"
                            >
                                <x-lucide-chevron-right
                                    class="w-2.5 h-2.5 shrink-0 transition-transform
                                           {{ $sub['active'] ? 'text-teal-dark' : 'text-navy/25 group-hover:translate-x-0.5' }}"
                                />
                                <span class="min-w-0">{{ $sub['name'] }}</span>
                            </a>
                        @endforeach

                        @if ($subcategoriesMoreCount > 0)
                            <button
                                type="button"
                                data-subcategory-toggle
                                class="w-full flex items-center gap-1.5 px-3 py-1.75 border-l-2 border-transparent text-[10.5px] font-medium text-navy/45 hover:text-teal-dark hover:bg-gray-bg/80 transition-colors"
                            >
                                <x-lucide-chevron-down class="w-2.5 h-2.5" />
                                More
                            </button>
                        @endif
                    </nav>

                </div>

                {{-- Search filter --}}
                <div class="bg-white rounded-xl border border-gray-border/90 shadow-sm overflow-hidden">

                    <div class="flex items-center justify-between gap-2 px-3 py-2.5 border-b border-gray-border/80 bg-white">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-md bg-teal-light flex items-center justify-center shrink-0">
                                <x-lucide-sliders-horizontal class="w-3.5 h-3.5 text-teal-dark" />
                            </span>
                            <p class="text-[12px] leading-none font-bold text-navy whitespace-nowrap">Search Filter</p>
                        </div>

                        @if (count($activeFilters))
                            <span class="text-[9px] leading-none font-semibold text-teal-dark bg-teal-light px-1.5 py-1 rounded-full shrink-0">
                                {{ count($activeFilters) }}
                            </span>
                        @endif
                    </div>

                    <div class="divide-y divide-gray-border">

                        {{-- Shipped from --}}
                        <div class="px-3 py-2.5">
                            <p class="text-[9.5px] uppercase tracking-[0.04em] font-bold text-navy/75 mb-2">Shipped From</p>
                            <div class="flex flex-col gap-1.5">
                                @foreach ($filters['shipped_from'] as $option)
                                    <label class="flex items-center gap-2 text-[10px] leading-none text-navy/60 cursor-pointer hover:text-navy transition">
                                        <input type="checkbox" name="shipped_from[]" value="{{ $option }}"
                                               class="w-3.5 h-3.5 rounded border-gray-border text-teal accent-teal focus:ring-teal/30 focus:ring-offset-0">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Brand --}}
                        <div class="px-3 py-2.5">
                            <p class="text-[9.5px] uppercase tracking-[0.04em] font-bold text-navy/75 mb-2">Brand</p>
                            <div class="flex flex-col gap-1.5">
                                @foreach ($filters['brands'] as $brand)
                                    <label class="flex items-center gap-2 text-[10px] leading-none text-navy/60 cursor-pointer hover:text-navy transition">
                                        <input type="checkbox" name="brand[]" value="{{ $brand }}"
                                               class="w-3.5 h-3.5 rounded border-gray-border text-teal accent-teal focus:ring-teal/30 focus:ring-offset-0">
                                        {{ $brand }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Price range --}}
                        <div class="px-3 py-2.5">
                            <p class="text-[9.5px] uppercase tracking-[0.04em] font-bold text-navy/75 mb-2">Price Range</p>
                            <div class="flex items-center gap-2">
                                <input
                                    type="number" min="0" name="price_min" placeholder="₱ Min"
                                    class="w-full min-w-0 text-[10px] text-navy px-2 py-1.5 rounded-md border border-gray-border focus:border-teal focus:ring-1 focus:ring-teal/30 outline-none"
                                >
                                <span class="text-navy/30 text-[10px] shrink-0">—</span>
                                <input
                                    type="number" min="0" name="price_max" placeholder="₱ Max"
                                    class="w-full min-w-0 text-[10px] text-navy px-2 py-1.5 rounded-md border border-gray-border focus:border-teal focus:ring-1 focus:ring-teal/30 outline-none"
                                >
                            </div>
                            <button
                                type="button"
                                class="w-full mt-2 bg-teal hover:bg-teal-dark text-white text-[10.5px] font-semibold py-1.5 rounded-md transition-colors"
                            >
                                Apply
                            </button>
                        </div>

                        {{-- Rating --}}
                        <div class="px-3 py-2.5">
                            <p class="text-[9.5px] uppercase tracking-[0.04em] font-bold text-navy/75 mb-2">Rating</p>
                            <div class="flex flex-col gap-1">
                                @foreach ($filters['ratings'] as $rating)
                                    <button
                                        type="button"
                                        class="flex items-center gap-1 text-navy/65 hover:text-teal-dark transition w-fit"
                                    >
                                        @for ($i = 0; $i < 5; $i++)
                                            <x-lucide-star
                                                class="w-3 h-3 {{ $i < $rating ? 'fill-amber-400 text-amber-400' : 'text-gray-border' }}"
                                            />
                                        @endfor
                                        <span class="text-[9.5px] ml-0.5">& Up</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Shipping option --}}
                        <div class="px-3 py-2.5">
                            <p class="text-[9.5px] uppercase tracking-[0.04em] font-bold text-navy/75 mb-2">Shipping Option</p>
                            <div class="flex flex-col gap-1">
                                @foreach ($filters['shipping_options'] as $option)
                                    <label class="flex items-center gap-2 text-[10px] leading-none text-navy/60 cursor-pointer hover:text-navy transition">
                                        <input type="checkbox" name="shipping_option[]" value="{{ $option }}"
                                               class="w-3.5 h-3.5 rounded border-gray-border text-teal accent-teal focus:ring-teal/30 focus:ring-offset-0">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    <div class="px-3 py-2.5">
                        <a href="{{ route('buyer.category.show', $activeCategory['slug']) }}"
                           class="block text-center text-[10px] font-semibold text-navy/45 hover:text-teal-dark transition">
                            Clear All
                        </a>
                    </div>

                </div>

            </aside>


            {{-- =================================================
                RIGHT: SORT BAR + PRODUCT GRID
            ================================================== --}}
            <div class="min-w-0">

                {{-- Sort bar --}}
                <div class="buyer-reveal flex flex-wrap items-center justify-between gap-2 bg-white border border-gray-border rounded-xl px-2.5 sm:px-3 py-2 mb-3">

                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[10px] text-navy/50 font-medium pr-1 hidden sm:inline">Sort by</span>

                        @foreach ($sortOptions as $key => $label)
                            <a
                                href="?sort={{ $key }}"
                                class="text-[10px] font-semibold px-2.5 py-1 rounded-md transition-colors
                                       {{ $currentSort === $key
                                           ? 'bg-teal text-white'
                                           : 'bg-gray-bg text-navy/60 hover:text-navy' }}"
                            >
                                {{ $label }}
                            </a>
                        @endforeach

                        <div class="relative">
                            <select
                                class="appearance-none text-[10px] font-semibold text-navy/60 bg-gray-bg pl-2.5 pr-7 py-1 rounded-md outline-none cursor-pointer hover:text-navy transition-colors"
                            >
                                <option>Price</option>
                                <option value="price_asc">Lowest to Highest</option>
                                <option value="price_desc">Highest to Lowest</option>
                            </select>
                            <x-lucide-chevron-down class="w-3 h-3 text-navy/40 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" />
                        </div>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        <span class="text-[10px] text-navy/50">
                            <span class="text-navy font-semibold">{{ $currentPage }}</span>/{{ $lastPage }}
                        </span>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                {{ $currentPage <= 1 ? 'disabled' : '' }}
                                class="w-6 h-6 rounded-md border border-gray-border flex items-center justify-center text-navy/40 hover:text-teal-dark hover:border-teal/30 disabled:opacity-40 disabled:pointer-events-none transition"
                            >
                                <x-lucide-chevron-left class="w-3 h-3" />
                            </button>
                            <button
                                type="button"
                                {{ $currentPage >= $lastPage ? 'disabled' : '' }}
                                class="w-6 h-6 rounded-md border border-gray-border flex items-center justify-center text-navy/40 hover:text-teal-dark hover:border-teal/30 disabled:opacity-40 disabled:pointer-events-none transition"
                            >
                                <x-lucide-chevron-right class="w-3 h-3" />
                            </button>
                        </div>
                    </div>

                </div>

                {{-- Product grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2 buyer-stagger">

                    @forelse ($products as $product)
                        <article class="group buyer-card bg-white rounded-lg overflow-hidden border border-gray-border hover:border-teal/30 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">

                            <div class="relative aspect-square bg-gray-bg overflow-hidden">
                                <a href="{{ route('buyer.product.show', $product['id']) }}" class="block w-full h-full">
                                    <img
                                        src="{{ str_starts_with($product['image'], 'http') ? $product['image'] : asset($product['image']) }}"
                                        alt="{{ $product['name'] }}"
                                        class="kb-photo{{ $loop->index % 2 ? ' kb-photo-alt' : '' }}"
                                    >
                                </a>

                                @if (! empty($product['discount_percent']))
                                    <span class="absolute top-1.5 right-1.5 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">
                                        -{{ $product['discount_percent'] }}%
                                    </span>
                                @endif

                                @if (! empty($product['badge']))
                                    <span class="absolute bottom-1.5 left-1.5 bg-navy/85 text-white text-[8.5px] font-semibold px-1.5 py-0.5 rounded">
                                        {{ $product['badge'] }}
                                    </span>
                                @endif

                                <button
                                    type="button"
                                    title="Add to wishlist"
                                    class="wishlist-btn absolute top-1.5 left-1.5 w-6 h-6 rounded-md bg-white/95 shadow-sm border border-black/5 flex items-center justify-center text-navy hover:text-teal-dark transition opacity-0 group-hover:opacity-100"
                                    data-wished="false"
                                >
                                    <x-lucide-heart class="w-3 h-3" />
                                </button>
                            </div>

                            <div class="p-2">
                                <h3 class="text-[10.5px] leading-snug text-navy/85 line-clamp-2 min-h-[2.4em]" title="{{ $product['name'] }}">
                                    <a href="{{ route('buyer.product.show', $product['id']) }}" class="hover:text-teal-dark transition">
                                        {{ $product['name'] }}
                                    </a>
                                </h3>

                                <p class="text-[12.5px] font-bold text-teal-dark mt-0.5">
                                    ₱{{ number_format($product['price']) }}
                                </p>

                                <div class="flex items-center justify-between mt-0.5">
                                    @if (! empty($product['rating']))
                                        <span class="flex items-center gap-0.5 text-[9px] text-navy/40">
                                            <x-lucide-star class="w-2.5 h-2.5 fill-amber-400 text-amber-400" />
                                            {{ $product['rating'] }}
                                        </span>
                                    @else
                                        <span></span>
                                    @endif

                                    @if (! empty($product['sold_count']))
                                        <span class="text-[9px] text-navy/35">
                                            {{ $product['sold_count'] }} sold
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </article>
                    @empty
                        <div class="col-span-full py-12 text-center">
                            <x-lucide-package-search class="w-7 h-7 text-navy/20 mx-auto mb-1.5" />
                            <p class="text-[10.5px] text-navy/40">No products found in this category yet.</p>
                        </div>
                    @endforelse

                </div>

                {{-- Bottom pager (mirrors the top counter) --}}
                @if ($lastPage > 1)
                    <div class="flex items-center justify-center gap-1 mt-4">
                        @for ($p = 1; $p <= $lastPage; $p++)
                            <a
                                href="?page={{ $p }}"
                                class="w-7 h-7 rounded-md flex items-center justify-center text-[10px] font-semibold transition
                                       {{ $p === $currentPage
                                           ? 'bg-teal text-white'
                                           : 'bg-white border border-gray-border text-navy/50 hover:text-teal-dark hover:border-teal/30' }}"
                            >
                                {{ $p }}
                            </a>
                        @endfor
                    </div>
                @endif

            </div>

        </div>

    </div>
</section>


{{-- =========================================================
    FOOTER
========================================================= --}}
@include('partials.footer')

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const motionReduced = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const revealTargets = document.querySelectorAll('.buyer-reveal, .buyer-stagger');

    if (motionReduced || !('IntersectionObserver' in window)) {
        revealTargets.forEach(function (item) { item.classList.add('is-visible'); });
    } else {
        const revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -24px 0px' });

        revealTargets.forEach(function (item) { revealObserver.observe(item); });
    }

    // "More" toggle for the sidebar subcategory list
    document.querySelectorAll('[data-subcategory-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const nav = btn.closest('nav');
            nav.querySelectorAll('a').forEach(function (link) {
                link.classList.remove('hidden');
            });
            btn.remove();
        });
    });

    document.querySelectorAll('.wishlist-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const wished = btn.getAttribute('data-wished') === 'true';
            btn.setAttribute('data-wished', String(!wished));
            if (motionReduced) return;
            btn.classList.remove('heart-pop');
            void btn.offsetWidth;
            btn.classList.add('heart-pop');
        });
    });

});
</script>
@endpush