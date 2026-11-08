@extends('layouts.app')
@section('title', 'Search ShopHop')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/shophop-buyer-discovery.css') }}?v=20261108">
@endpush
@section('content')
<section class="discovery-page sh-search-page"><div class="sh-shop-shell">
    <header class="discovery-header"><div><p class="discovery-eyebrow">Available on ShopHop</p><h1 class="discovery-title">@if($search === '') Search ShopHop @else Search results for <span>{{ $search }}</span>@endif</h1><p class="discovery-description">@if($products){{ $products->total() }} matching {{ \Illuminate\Support\Str::plural('product', $products->total()) }}@else Find products and categories using the search bar above.@endif</p></div><a class="discovery-secondary" href="{{ route('buyer.category.index') }}">Browse categories <x-lucide-arrow-right /></a></header>
    @if($search === '')
        <div class="discovery-empty"><x-lucide-search /><h2>What are you looking for?</h2><p>Enter a product name, category, or description to search the available ShopHop catalogue.</p></div>
    @elseif($products->isEmpty())
        <div class="discovery-empty"><x-lucide-search-x /><h2>No available products matched</h2><p>Try a different product name, category, or description.</p><a class="discovery-primary" href="{{ route('buyer.category.index') }}">Explore categories</a></div>
    @else
        <div class="search-results-grid">
            @foreach($products as $product)
                <article data-search-result class="search-result-card">
                    <a href="{{ route('buyer.product.show', $product) }}" class="search-result-image"><img src="{{ $product->image ? asset('storage/'.ltrim($product->image, '/')) : asset('images/placeholder-product.jpg') }}" alt="{{ $product->name }}" loading="lazy"></a>
                    <div class="search-result-body"><p>{{ $product->category }}</p><h2><a href="{{ route('buyer.product.show', $product) }}">{{ $product->name }}</a></h2><strong>&#8369;{{ number_format((float) $product->price * (1 - ((int) $product->discount / 100)), 2) }}</strong><span>{{ (int) $product->stock }} in stock</span></div>
                </article>
            @endforeach
        </div>
        <div class="search-pagination">{{ $products->links() }}</div>
    @endif
</div></section>
@endsection
