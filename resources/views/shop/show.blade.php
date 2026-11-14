@extends('layouts.app')
@section('title', ($seller->business_name ?: 'Shop').' — ShopHop')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/shophop-marketplace-landing.css') }}?v=20261114-wide-2">
<link rel="stylesheet" href="{{ asset('css/shophop-shop-page.css') }}?v=20261114-3">
@endpush
@section('content')
@php
    $landingProductImageUrl = fn ($path) => $path ? asset('storage/'.ltrim($path, '/')) : asset('images/placeholder-product.jpg');
@endphp
<main class="sh-market sh-shop-page">
    <div class="sh-market-width">
        <nav class="sh-shop-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>›</span><span>{{ $seller->business_name }}</span></nav>
        <header class="sh-shop-hero">
            <div class="sh-shop-cover-card">
                @if($coverUrl)<img class="sh-shop-cover-photo" src="{{ $coverUrl }}" alt="" aria-hidden="true">@endif
                <div class="sh-shop-cover-shade"></div>
                <div class="sh-shop-cover-content">
                    <div class="sh-shop-identity">
                        <div class="sh-shop-logo">
                            @if($seller->logo_url)<img src="{{ $seller->logo_url }}" alt="{{ $seller->business_name }} logo">@else<span aria-hidden="true">{{ strtoupper(mb_substr($seller->business_name ?: 'S', 0, 1)) }}</span>@endif
                        </div>
                        <div class="sh-shop-title"><p class="sh-shop-kicker">SHOPHOP STORE</p><h1>{{ $seller->business_name ?: 'ShopHop Seller' }}</h1><p>{{ $seller->business_category }} @if($seller->municipality_name) · {{ $seller->municipality_name }}, {{ $seller->province_name }} @endif</p></div>
                    </div>
                    @if(!auth()->check() || auth()->user()->account_type === 'buyer')<button type="button" data-open-shop-chat class="sh-shop-chat-button">Chat with shop</button>@endif
                </div>
            </div>
            <dl class="sh-shop-stats">
                <div><dt>Products</dt><dd>{{ number_format($productCount) }}</dd></div>
                <div><dt>Rating</dt><dd>{{ $ratingCount ? number_format($rating, 1).' / 5' : 'New shop' }}</dd></div>
                <div><dt>Reviews</dt><dd>{{ number_format($ratingCount) }}</dd></div>
                <div><dt>Joined</dt><dd>{{ $seller->created_at?->format('M Y') ?? '—' }}</dd></div>
            </dl>
        </header>
        <nav class="sh-shop-tabs" aria-label="Shop sections">
            <a href="{{ route('shop.show', $seller) }}" @if(!$category) aria-current="page" @endif>Home</a>
            <a href="#shopProductsTitle">All Products</a>
            @foreach($categories->take(5) as $shopCategory)
                <a href="{{ route('shop.show', ['seller' => $seller, 'category' => $shopCategory, 'sort' => $sort]) }}" @if($category === $shopCategory) aria-current="page" @endif>{{ $shopCategory }}</a>
            @endforeach
        </nav>

        @if($vouchers->isNotEmpty())
            <section class="sh-shop-vouchers" aria-labelledby="shopVouchersTitle"><div class="sh-shop-section-head"><h2 id="shopVouchersTitle">Shop vouchers</h2><p>Use a code at checkout when eligible.</p></div><div class="sh-shop-voucher-grid">@foreach($vouchers as $voucher)<article class="sh-shop-voucher"><strong>{{ $voucher->type === 'percent' ? rtrim(rtrim(number_format((float)$voucher->value, 2), '0'), '.').'%' : '₱'.number_format((float)$voucher->value, 0) }} off</strong><span>{{ $voucher->min_order_amount ? 'Min. spend ₱'.number_format((float)$voucher->min_order_amount, 0) : 'No minimum spend' }}</span><code>{{ $voucher->code }}</code>@if($voucher->ends_at)<small>Until {{ $voucher->ends_at->format('M j') }}</small>@endif</article>@endforeach</div></section>
        @endif

        <section class="sh-shop-products" aria-labelledby="shopProductsTitle">
            <div class="sh-shop-section-head"><div><p class="sh-shop-kicker">EXPLORE THE SHOP</p><h2 id="shopProductsTitle">All Products</h2></div><form method="GET" action="{{ route('shop.show', $seller) }}"><input type="hidden" name="category" value="{{ $category }}"><label>Sort <select name="sort" onchange="this.form.submit()"><option value="newest" @selected($sort === 'newest')>Newest</option><option value="price_low" @selected($sort === 'price_low')>Price: low to high</option><option value="price_high" @selected($sort === 'price_high')>Price: high to low</option></select></label></form></div>
            <nav class="sh-shop-categories" aria-label="Shop categories"><a href="{{ route('shop.show', ['seller' => $seller, 'sort' => $sort]) }}" @if(!$category) aria-current="page" @endif>All</a>@foreach($categories as $shopCategory)<a href="{{ route('shop.show', ['seller' => $seller, 'category' => $shopCategory, 'sort' => $sort]) }}" @if($category === $shopCategory) aria-current="page" @endif>{{ $shopCategory }}</a>@endforeach</nav>
            @if($products->isEmpty())<div class="sh-shop-empty"><h3>No available products in this category</h3><p>Check back soon or explore the rest of ShopHop.</p><a href="{{ route('home') }}">Browse marketplace</a></div>
            @else<div class="sh-shop-product-grid">@foreach($products as $product)@include('home.partials.product-card', ['product' => $product])@endforeach</div><div class="sh-shop-pagination">{{ $products->links() }}</div>@endif
        </section>
    </div>
</main>
@if(!auth()->check() || auth()->user()->account_type === 'buyer')
    @include('partials.shop-chat-widget', ['sellerId' => $seller->user_id, 'sellerName' => $seller->business_name])
@endif
@endsection
@push('scripts')<script defer src="{{ asset('js/shophop-marketplace-landing.js') }}?v=20261114-deals-video-1"></script>@endpush
