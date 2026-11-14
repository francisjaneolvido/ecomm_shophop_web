@extends('seller.partials.layout')
@section('title', 'Shop Profile')
@push('styles')<link rel="stylesheet" href="{{ asset('css/shophop-seller-workspace.css') }}?v=20261112-navy-pink-2">@endpush
@section('content')
<div id="sellerShopProfile" class="ss-workspace">
    <header class="ss-page-head"><div><p class="ss-eyebrow">SELLER / SHOP PROFILE</p><h1>Shop Profile</h1><p>Registered shop details, your buyer-facing logo, and store shortcuts.</p></div><div class="flex flex-wrap gap-2"><a class="ss-btn" href="{{ route('shop.show', $seller) }}">View Public Shop</a><a class="ss-btn ss-btn-primary" href="{{ route('seller.account') }}">Edit Contact Profile</a></div></header>
    @if(session('status'))<div role="status" class="ss-message ss-message-info mb-3">{{ session('status') }}</div>@endif
    @if($errors->has('logo'))<div role="alert" class="ss-message mb-3" style="color:#b91c1c;background:#fef2f2">{{ $errors->first('logo') }}</div>@endif
    <section class="ss-panel mb-4 p-4 sm:p-5" aria-labelledby="shopLogoHeading">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div id="shopLogoPreview" class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-teal-light text-xl font-bold text-teal-dark">
                @if($seller->logo_url)<img src="{{ $seller->logo_url }}" alt="Current {{ $seller->business_name }} logo" class="h-full w-full object-cover">@else{{ strtoupper(mb_substr($seller->business_name ?: 'S', 0, 1)) }}@endif
            </div>
            <div class="min-w-0 flex-1"><h2 id="shopLogoHeading" class="text-sm font-bold text-navy">Shop photo or logo</h2><p class="mt-1 text-xs text-navy/60">Shown on your public shop page and beside your products. JPG, PNG, or WebP, up to 2 MB.</p>
                <form method="POST" action="{{ route('seller.shop-profile.logo') }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-2">
                    @csrf @method('PATCH')
                    <input type="file" name="logo" id="shopLogoInput" accept="image/jpeg,image/png,image/webp" class="max-w-full text-xs" aria-label="Choose a shop logo">
                    <button type="submit" class="ss-btn ss-btn-primary">Save logo</button>
                    @if($seller->logo_url)<button type="submit" name="remove_logo" value="1" formnovalidate data-remove-shop-logo class="ss-btn">Remove logo</button>@endif
                </form>
            </div>
        </div>
    </section>
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
    <div class="ss-message ss-message-info"><strong>For account security:</strong> Registered business name, category, address, and verification documents cannot be self-edited on this page. Changes to reviewed business information require an Admin-supported approval process.</div>
</div>
<dialog id="removeShopLogoDialog" class="m-auto w-[min(92vw,390px)] rounded-2xl border border-slate-200 bg-white p-0 font-[Poppins] text-navy shadow-2xl backdrop:bg-navy/60" aria-labelledby="removeShopLogoTitle"><div class="p-5"><p class="text-[10px] font-bold uppercase tracking-widest text-teal-dark">Shop profile</p><h2 id="removeShopLogoTitle" class="mt-1 text-base font-bold">Remove your shop logo?</h2><p class="mt-2 text-xs text-navy/60">Your public shop will show its default initial until you upload another photo.</p><div class="mt-5 flex justify-end gap-2"><button type="button" data-logo-cancel class="ss-btn">Keep logo</button><button type="button" data-logo-confirm class="ss-btn ss-btn-primary">Remove logo</button></div></div></dialog>
@endsection
@push('scripts')<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('shopLogoInput');
    const preview = document.getElementById('shopLogoPreview');
    let previewUrl;
    input?.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        const file = input.files?.[0];
        if (!file?.type.startsWith('image/')) return;
        previewUrl = URL.createObjectURL(file);
        preview.replaceChildren(Object.assign(document.createElement('img'), {src: previewUrl, alt: 'New shop logo preview', className: 'h-full w-full object-cover'}));
    });
    const remove = document.querySelector('[data-remove-shop-logo]');
    const dialog = document.getElementById('removeShopLogoDialog');
    remove?.addEventListener('click', event => {
        if (!dialog?.showModal) return;
        event.preventDefault();
        dialog.showModal();
    });
    dialog?.querySelector('[data-logo-cancel]')?.addEventListener('click', () => dialog.close());
    dialog?.querySelector('[data-logo-confirm]')?.addEventListener('click', () => {
        input.value = '';
        dialog.close();
        remove.form.requestSubmit(remove);
    });
});
</script>@endpush
