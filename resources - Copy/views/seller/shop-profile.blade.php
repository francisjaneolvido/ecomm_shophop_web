@extends('seller.partials.layout')
@section('title', 'Shop Profile')
@push('styles')<link rel="stylesheet" href="{{ asset('css/shophop-seller-workspace.css') }}?v=1">@endpush
@section('content')
<div id="sellerShopProfile" class="ss-workspace">
    <header class="ss-page-head"><div><p class="ss-eyebrow">SELLER / SHOP PROFILE</p><h1>Shop Profile</h1><p>Registered shop details, status, and shortcuts to supported settings.</p></div><a class="ss-btn ss-btn-primary" href="{{ route('seller.account') }}">Edit Contact Profile</a></header>
    <section class="ss-panel">
        <div class="ss-panel-heading"><div><h2>{{ $seller->business_name ?: 'Registered Seller' }}</h2><p>ShopHop Seller account · {{ $seller->business_category ?? 'Category not set' }}</p></div>
            <span class="ss-pill {{ auth()->user()->status === 'approved' ? 'ss-pill-success':'ss-pill-neutral' }}">{{ ucfirst(auth()->user()->status ?? 'pending') }}</span></div>
        <div class="ss-stats" style="padding:16px">
            <div class="ss-stat"><span>Listed products</span><strong>{{ number_format($summary['products']) }}</strong><small>All listings</small></div>
            <div class="ss-stat"><span>Active listings</span><strong>{{ number_format($summary['active']) }}</strong><small>Available in your catalog</small></div>
            <div class="ss-stat"><span>For compliance review</span><strong>{{ number_format($summary['review']) }}</strong><small>Awaiting Admin review</small></div>
            <a href="{{ route('seller.promotions') }}" class="ss-stat ss-stat-link"><span>Vouchers & Promotions</span><strong>Manage</strong><small>See codes and discounts →</small></a>
        </div>
    </section>
    <section class="ss-detail-grid">
        <div class="ss-info"><h3>Registered business</h3><dl>
            <dt>Business name</dt><dd>{{ $seller->business_name ?: 'Not provided' }}</dd>
            <dt>Category</dt><dd>{{ $seller->business_category ?: 'Not provided' }}</dd>
            <dt>Approval status</dt><dd>{{ ucfirst(auth()->user()->status ?? 'pending') }}</dd>
            <dt>ShopHop email</dt><dd>{{ auth()->user()->email }}</dd>
        </dl></div>
        <div class="ss-info"><h3>Registered pickup/business address</h3><dl>
            <dt>Street</dt><dd>{{ $seller->street_address ?: 'Not provided' }}</dd>
            <dt>Barangay</dt><dd>{{ $seller->barangay_name ?: 'Not provided' }}</dd>
            <dt>Municipality</dt><dd>{{ $seller->municipality_name ?: 'Not provided' }}</dd>
            <dt>Province / Region</dt><dd>{{ collect([$seller->province_name, $seller->region_name])->filter()->implode(' / ') ?: 'Not provided' }}</dd>
        </dl></div>
        <div class="ss-info"><h3>Shop contact</h3><dl>
            <dt>Registered owner</dt><dd>{{ trim($seller->first_name.' '.$seller->last_name) }}</dd>
            <dt>Phone</dt><dd>{{ $seller->contact_no ?: 'Not provided' }}</dd>
            <dt>Login email</dt><dd>{{ auth()->user()->email }}</dd>
        </dl><a href="{{ route('seller.account') }}" class="ss-link" style="font-size:11px;display:inline-block;margin-top:15px">Update supported profile fields →</a></div>
        <div class="ss-info"><h3>Available management tools</h3><a class="ss-link-row" href="{{ route('seller.inventory') }}"><div><strong>Product Inventory</strong><small>Products, pricing and stock</small></div><span>→</span></a><a class="ss-link-row" href="{{ route('seller.promotions') }}"><div><strong>Vouchers & Promotions</strong><small>Visible codes and discounts</small></div><span>→</span></a><a class="ss-link-row" href="{{ route('seller.orders.notifications') }}"><div><strong>Orders</strong><small>Prepare and track shipments</small></div><span>→</span></a></div>
    </section>
    <div class="ss-message ss-message-info"><strong>For account security:</strong> Registered business name, category, address, and verification documents cannot be self-edited on this page. Changes to reviewed business information require an Admin-supported approval process. No unpublished shop logo or storefront customization settings are implied.</div>
</div>
@endsection
