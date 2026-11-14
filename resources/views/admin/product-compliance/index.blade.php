@extends('admin.layout')
@section('title', 'Product Compliance')
@section('content')
<div class="space-y-4">
    <header class="sh-page-header"><div><p class="sh-kicker">Accounts & Catalog</p><h1 class="font-bold mt-1">Product Compliance</h1><p class="description">Review product information and media before approving buyer visibility.</p></div></header>
    @if(session('status'))<p role="status" class="sh-notice">{{ session('status') }}</p>@endif
    @if($errors->any())<div role="alert" class="pc-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <section class="ops-panel" aria-label="Product review queue">
        <nav class="sh-console-tabs" aria-label="Compliance status">
            @foreach($counts as $state => $count)
                <a href="{{ route('admin.product-compliance.index', ['filter' => $state, 'sort' => $sort, 'search' => $search]) }}" @if($filter === $state) aria-current="page" @endif>{{ ucwords(str_replace('_', ' ', $state)) }} <span class="sh-tab-count">{{ $count }}</span></a>
            @endforeach
        </nav>
        <form class="ops-filters" method="GET" action="{{ route('admin.product-compliance.index') }}" role="search">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <label class="sh-field"><span>Product, SKU or seller</span><input class="sh-input" type="search" name="search" value="{{ $search }}" maxlength="255" placeholder="Search products"></label>
            <label class="sh-field"><span>Sort by</span><select class="sh-select" name="sort">@foreach(['newest' => 'Newest submission', 'oldest' => 'Oldest submission', 'az' => 'Name A–Z', 'za' => 'Name Z–A'] as $value => $label)<option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>@endforeach</select></label>
            <button class="sh-btn" type="submit">Filter</button><a class="sh-btn-soft" href="{{ route('admin.product-compliance.index', ['filter' => $filter]) }}">Clear</a>
        </form>
        <div class="ops-panel-head"><h2 class="text-sm font-semibold">Product review queue</h2><span class="text-xs ops-muted">{{ number_format($products->total()) }} matching</span></div>
        <div class="admin-table-scroll"><table class="ops-table w-full text-left">
            <thead><tr><th scope="col">Product / SKU</th><th scope="col">Seller</th><th scope="col">Inventory</th><th scope="col">Submitted</th><th scope="col">Review status</th><th scope="col">Action</th></tr></thead>
            <tbody>@forelse($products as $product)
                <tr><td><strong>{{ $product->name }}</strong><p class="text-xs ops-muted">{{ $product->sku ?: 'No SKU' }}</p></td><td>{{ $product->seller?->business_name ?? 'Seller unavailable' }}</td><td>{{ number_format($product->stock) }} in stock<p class="text-xs ops-muted">{{ ucfirst($product->status) }}</p></td><td>{{ $product->submitted_at?->format('M j, Y') ?? 'Not recorded' }}</td><td><span class="sh-badge {{ $product->compliance_status === 'approved' ? 'sh-badge-success' : ($product->compliance_status === 'rejected' ? 'sh-badge-danger' : 'sh-badge-warning') }}">{{ ucwords(str_replace('_', ' ', $product->compliance_status)) }}</span></td><td><div class="flex items-center gap-2"><button type="button" class="sh-btn-soft" data-review-open="review-product-{{ $product->id }}">Quick review</button><a data-console-details class="sh-btn-soft" href="{{ route('admin.compliance.show', $product) }}">Full details</a></div></td></tr>
            @empty<tr><td colspan="6" class="ops-empty text-center py-10">No products match these filters.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="ops-footer">{{ $products->withQueryString()->links() }}</div>
    </section>
    @include('admin.product-compliance.review-modals')
</div>
@endsection
