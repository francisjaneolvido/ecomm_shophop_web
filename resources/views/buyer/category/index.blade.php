@extends('layouts.app')
@section('title', 'All Categories - ShopHop')
@section('hideChrome', true)
@push('styles')
<link rel="stylesheet" href="{{ asset('css/shophop-buyer-discovery.css') }}?v=20261112-navy-pink-2">
@endpush
@section('content')
@include('buyer.partials.navbar-buyer')
<section class="shb-categories-page discovery-page">
    <div class="sh-shop-shell">
        <header class="discovery-header">
            <div>
                <p class="discovery-eyebrow">Find your next everyday essential</p>
                <h1 class="discovery-title">Browse Categories</h1>
                <p class="discovery-description">Explore {{ count($categoryTree) }} departments. Choose a category or go straight to a subcategory.</p>
            </div>
            <a class="discovery-secondary" href="{{ route('search.index') }}">Search all products <x-lucide-arrow-right /></a>
        </header>
        <div class="discovery-toolbar">
            <label class="discovery-filter">
                <x-lucide-search />
                <span class="sr-only">Find a category or subcategory</span>
                <input type="search" data-category-filter placeholder="Find a category or subcategory" maxlength="100" autocomplete="off">
                <button type="button" data-category-clear aria-label="Clear category search" hidden><x-lucide-x /></button>
            </label>
            <p data-category-count role="status">{{ count($categoryTree) }} categories</p>
        </div>
        <div class="discovery-grid" data-category-grid>
            @foreach($categoryTree as $category)
                @php
                    $categoryUrl = route('buyer.category.show', $category['slug']);
                    $subcategories = $category['subcategories'] ?? [];
                    $available = (int) ($categoryCounts[$category['name']] ?? 0);
                @endphp
                <article class="discovery-card" data-category-card data-category-name="{{ mb_strtolower($category['name'].' '.implode(' ', $subcategories)) }}">
                    <a class="discovery-card-heading" href="{{ $categoryUrl }}">
                        <span class="discovery-category-icon"><x-dynamic-component :component="'lucide-'.($category['icon'] ?? 'package')" /></span>
                        <div><h2>{{ $category['name'] }}</h2><p>{{ $available }} available {{ \Illuminate\Support\Str::plural('product', $available) }}</p></div>
                        <x-lucide-chevron-right class="discovery-card-arrow" />
                    </a>
                    <ul class="discovery-subcategories">
                        @foreach(array_slice($subcategories, 0, 4) as $subcategory)
                            <li><a href="{{ route('buyer.category.show', ['category' => $category['slug'], 'sub' => \Illuminate\Support\Str::slug($subcategory)]) }}" title="{{ $subcategory }}">{{ $subcategory }}<x-lucide-arrow-up-right /></a></li>
                        @endforeach
                    </ul>
                    <a class="discovery-card-footer" href="{{ $categoryUrl }}">View all {{ count($subcategories) }} subcategories <x-lucide-arrow-right /></a>
                </article>
            @endforeach
        </div>
        <div class="discovery-empty" data-category-empty hidden><x-lucide-search-x /><h2>No matching categories</h2><p>Try another category or subcategory name.</p><button type="button" data-category-reset>Show all categories</button></div>
    </div>
</section>
@include('partials.footer')
@endsection
@push('scripts')
<script src="{{ asset('js/shophop-buyer-discovery.js') }}?v=20261112-navy-pink-2" defer></script>
@endpush
