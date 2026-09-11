{{-- Path: resources/views/buyer/category/index.blade.php --}}

@extends('layouts.app')

@section('title', 'All Categories — ShopHop')

{{-- Match the buyer category page: use the buyer navbar instead of guest chrome. --}}
@section('hideChrome', true)

@section('content')

@php
    $categoryTree ??= config('shophop_categories', []);

    /*
    |--------------------------------------------------------------------------
    | Optional visual accents
    |--------------------------------------------------------------------------
    | These are only presentation helpers. Your actual category data still
    | comes from config('shophop_categories').
    */
    $categoryAccents = [
        'mens-apparel'      => ['from-sky-50',     'to-cyan-50',    'text-sky-700',     'bg-sky-100'],
        'womens-apparel'    => ['from-rose-50',    'to-pink-50',    'text-rose-700',    'bg-rose-100'],
        'mobile-gadgets'    => ['from-indigo-50',  'to-violet-50',  'text-indigo-700',  'bg-indigo-100'],
        'home-living'       => ['from-amber-50',   'to-orange-50',  'text-amber-700',   'bg-amber-100'],
        'health-beauty'     => ['from-fuchsia-50', 'to-pink-50',    'text-fuchsia-700', 'bg-fuchsia-100'],
        'sports-outdoors'   => ['from-emerald-50', 'to-green-50',   'text-emerald-700', 'bg-emerald-100'],
        'pet-supplies'      => ['from-teal-50',    'to-cyan-50',    'text-teal-700',    'bg-teal-100'],
        'automotive'        => ['from-slate-50',   'to-gray-100',   'text-slate-700',   'bg-slate-100'],
        'baby-kids'         => ['from-yellow-50',  'to-amber-50',   'text-amber-700',   'bg-amber-100'],
        'groceries'         => ['from-lime-50',    'to-green-50',   'text-lime-700',    'bg-lime-100'],
        'computers'         => ['from-blue-50',    'to-indigo-50',  'text-blue-700',    'bg-blue-100'],
        'gaming'            => ['from-violet-50',  'to-purple-50',  'text-violet-700',  'bg-violet-100'],
    ];

    $defaultAccent = ['from-teal-50', 'to-cyan-50', 'text-teal-dark', 'bg-teal-light'];

    $totalSubcategories = collect($categoryTree)
        ->sum(fn ($category) => count($category['subcategories'] ?? []));

    $breadcrumbs = [
        ['label' => 'All Categories', 'url' => null],
    ];
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
                    <a href="{{ $crumb['url'] }}" class="hover:text-teal-dark transition">
                        {{ $crumb['label'] }}
                    </a>
                    <x-lucide-chevron-right class="w-3 h-3 text-navy/25" />
                @else
                    <span class="text-navy font-medium">{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </nav>
    </div>
</div>


{{-- =========================================================
    ALL CATEGORIES
========================================================= --}}
<section class="bg-gray-bg min-h-[70vh]">
    <div class="max-w-310 mx-auto px-3 sm:px-4 lg:px-5 py-4 sm:py-5">

        {{-- Page header --}}
        <div class="buyer-reveal relative overflow-hidden bg-white border border-gray-border rounded-2xl shadow-sm mb-4 sm:mb-5">
            <div class="absolute inset-0 bg-linear-to-br from-teal-light/70 via-white to-cyan-50/70 pointer-events-none"></div>
            <div class="absolute -right-12 -top-16 w-44 h-44 rounded-full bg-teal/5 pointer-events-none"></div>
            <div class="absolute right-12 -bottom-20 w-36 h-36 rounded-full bg-teal/5 pointer-events-none"></div>

            <div class="relative px-4 sm:px-5 lg:px-6 py-5 sm:py-6">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-teal-light text-teal-dark text-[9px] sm:text-[9.5px] font-bold uppercase tracking-[0.06em] mb-2">
                            <x-lucide-layout-grid class="w-3 h-3" />
                            Shop by category
                        </div>

                        <h1 class="text-xl sm:text-2xl lg:text-[28px] leading-tight font-extrabold text-navy tracking-[-0.02em]">
                            Find what you need faster
                        </h1>

                        <p class="mt-1.5 text-[10.5px] sm:text-[11.5px] leading-relaxed text-navy/55 max-w-xl">
                            Browse every ShopHop department, then jump straight into the products and subcategories you want.
                        </p>

                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/80 border border-gray-border text-[9.5px] text-navy/55">
                                <x-lucide-grid-2x2 class="w-3 h-3 text-teal-dark" />
                                <strong class="text-navy">{{ count($categoryTree) }}</strong>
                                categories
                            </span>

                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/80 border border-gray-border text-[9.5px] text-navy/55">
                                <x-lucide-list-tree class="w-3 h-3 text-teal-dark" />
                                <strong class="text-navy">{{ $totalSubcategories }}</strong>
                                subcategories
                            </span>
                        </div>
                    </div>

                    {{-- Local category search --}}
                    <div class="w-full lg:w-77.5">
                        <label for="category-search" class="sr-only">Search categories</label>
                        <div class="relative">
                            <x-lucide-search class="w-3.5 h-3.5 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input
                                id="category-search"
                                type="search"
                                placeholder="Search categories..."
                                autocomplete="off"
                                class="w-full bg-white border border-gray-border text-[10.5px] text-navy placeholder:text-navy/30 rounded-xl pl-8.5 pr-9 py-2.5 outline-none focus:border-teal focus:ring-2 focus:ring-teal/10 transition"
                            >
                            <button
                                id="category-search-clear"
                                type="button"
                                aria-label="Clear category search"
                                class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 rounded-md items-center justify-center text-navy/35 hover:text-teal-dark hover:bg-teal-light transition"
                            >
                                <x-lucide-x class="w-3 h-3" />
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        {{-- Empty config state --}}
        @if (empty($categoryTree))
            <div class="buyer-reveal bg-white border border-gray-border rounded-2xl py-14 px-4 text-center shadow-sm">
                <div class="w-11 h-11 mx-auto rounded-xl bg-teal-light flex items-center justify-center mb-3">
                    <x-lucide-layout-grid class="w-5 h-5 text-teal-dark" />
                </div>
                <p class="text-[12px] font-bold text-navy">No categories configured yet.</p>
                <p class="text-[10px] text-navy/45 mt-1">
                    Add category entries to <code class="text-teal-dark">config/shophop_categories.php</code>.
                </p>
            </div>
        @else

            {{-- Category grid --}}
            <div
                id="category-grid"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 buyer-stagger"
            >
                @foreach ($categoryTree as $category)
                    @php
                        $slug = $category['slug'] ?? \Illuminate\Support\Str::slug($category['name'] ?? 'category');
                        $name = $category['name'] ?? 'Category';
                        $icon = $category['icon'] ?? 'package';
                        $subcategories = $category['subcategories'] ?? [];
                        $accent = $categoryAccents[$slug] ?? $defaultAccent;

                        $categoryUrl = \Illuminate\Support\Facades\Route::has('buyer.category.show')
                            ? route('buyer.category.show', $slug)
                            : url('/buyer/category/' . $slug);
                    @endphp

                    <article
                        class="category-card group buyer-card bg-white rounded-xl border border-gray-border hover:border-teal/30 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 overflow-hidden"
                        data-category-name="{{ \Illuminate\Support\Str::lower($name . ' ' . implode(' ', $subcategories)) }}"
                    >
                        {{-- Visual header --}}
                        <a href="{{ $categoryUrl }}" class="block">
                            <div class="relative h-26 sm:h-28 overflow-hidden bg-linear-to-br {{ $accent[0] }} {{ $accent[1] }}">
                                <div class="absolute -right-5 -top-7 w-24 h-24 rounded-full bg-white/35"></div>
                                <div class="absolute right-8 -bottom-10 w-20 h-20 rounded-full bg-white/25"></div>

                                <div class="relative h-full px-3.5 py-3 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $accent[3] }} mb-2 shadow-sm">
                                            <x-dynamic-component
                                                :component="'lucide-' . $icon"
                                                class="w-4 h-4 {{ $accent[2] }}"
                                            />
                                        </span>

                                        <h2 class="text-[13px] sm:text-[13.5px] leading-tight font-extrabold text-navy truncate">
                                            {{ $name }}
                                        </h2>

                                        <p class="text-[9.5px] text-navy/45 mt-0.5">
                                            {{ count($subcategories) }} {{ \Illuminate\Support\Str::plural('subcategory', count($subcategories)) }}
                                        </p>
                                    </div>

                                    <span class="w-7 h-7 rounded-full bg-white/75 border border-white flex items-center justify-center text-navy/40 group-hover:text-teal-dark group-hover:translate-x-0.5 transition">
                                        <x-lucide-arrow-right class="w-3.5 h-3.5" />
                                    </span>
                                </div>
                            </div>
                        </a>

                        {{-- Subcategories --}}
                        <div class="px-3.5 py-3">
                            @if (! empty($subcategories))
                                <div class="flex flex-wrap gap-1.5 min-h-13.5 content-start">
                                    @foreach (array_slice($subcategories, 0, 5) as $subName)
                                        <a
                                            href="{{ $categoryUrl }}?sub={{ \Illuminate\Support\Str::slug($subName) }}"
                                            class="inline-flex max-w-full items-center px-2 py-1 rounded-md bg-gray-bg text-[9px] leading-none text-navy/55 hover:bg-teal-light hover:text-teal-dark transition"
                                            title="{{ $subName }}"
                                        >
                                            <span class="truncate">{{ $subName }}</span>
                                        </a>
                                    @endforeach

                                    @if (count($subcategories) > 5)
                                        <a
                                            href="{{ $categoryUrl }}"
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-teal-light text-[9px] leading-none font-semibold text-teal-dark hover:bg-teal/10 transition"
                                        >
                                            +{{ count($subcategories) - 5 }} more
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="min-h-13.5 flex items-center">
                                    <p class="text-[9.5px] text-navy/35">Browse products in this category.</p>
                                </div>
                            @endif

                            <a
                                href="{{ $categoryUrl }}"
                                class="mt-3 flex items-center justify-between gap-2 pt-2.5 border-t border-gray-border/80 text-[9.5px] font-semibold text-navy/55 hover:text-teal-dark transition"
                            >
                                <span>Explore {{ $name }}</span>
                                <x-lucide-chevron-right class="w-3 h-3 shrink-0" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>


            {{-- Search no-results state --}}
            <div
                id="category-no-results"
                class="hidden bg-white border border-gray-border rounded-2xl py-12 px-4 text-center shadow-sm"
            >
                <x-lucide-search-x class="w-7 h-7 text-navy/20 mx-auto mb-2" />
                <p class="text-[11px] font-semibold text-navy/60">No matching categories found.</p>
                <p class="text-[9.5px] text-navy/35 mt-1">Try a different category or subcategory name.</p>
            </div>

        @endif

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
        revealTargets.forEach(function (item) {
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
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -24px 0px'
        });

        revealTargets.forEach(function (item) {
            revealObserver.observe(item);
        });
    }


    // ---------------------------------------------------------------
    // Client-side category search
    // ---------------------------------------------------------------
    const searchInput = document.getElementById('category-search');
    const clearButton = document.getElementById('category-search-clear');
    const categoryGrid = document.getElementById('category-grid');
    const noResults = document.getElementById('category-no-results');
    const cards = Array.from(document.querySelectorAll('.category-card'));

    if (!searchInput || cards.length === 0) {
        return;
    }

    function filterCategories() {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        cards.forEach(function (card) {
            const haystack = (card.dataset.categoryName || '').toLowerCase();
            const matches = !query || haystack.includes(query);

            card.classList.toggle('hidden', !matches);

            if (matches) {
                visibleCount++;
            }
        });

        if (clearButton) {
            clearButton.classList.toggle('hidden', !query);
            clearButton.classList.toggle('flex', !!query);
        }

        if (categoryGrid) {
            categoryGrid.classList.toggle('hidden', visibleCount === 0);
        }

        if (noResults) {
            noResults.classList.toggle('hidden', visibleCount !== 0);
        }
    }

    searchInput.addEventListener('input', filterCategories);

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            searchInput.value = '';
            filterCategories();
            searchInput.focus();
        });
    }

});
</script>
@endpush
