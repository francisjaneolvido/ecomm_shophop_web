@extends('admin.layout')
@section('title', 'Product Compliance')
@include('admin.product-compliance.styles')
@section('content')
{{-- Queue counts describe compliance states; archive and stock remain separate visibility gates. --}}
<div class="compliance-workspace">
    <h1>Product Compliance</h1>
    <p class="pc-muted">Review merchandise before Buyer visibility. Approval also requires an active Product with stock.</p>
    <nav class="pc-controls" aria-label="Compliance status">
        @foreach ($counts as $state => $count)
            <a href="{{ route('admin.product-compliance.index', ['filter' => $state, 'sort' => $sort, 'search' => $search]) }}" @if($filter === $state) aria-current="page" @endif>{{ ucwords(str_replace('_', ' ', $state)) }} ({{ $count }})</a>
        @endforeach
    </nav>
    {{-- Native GET controls preserve supported query choices across pagination without JavaScript. --}}
    <form class="pc-panel pc-controls" method="GET" action="{{ route('admin.product-compliance.index') }}">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <label>Product, SKU, or Seller<input type="search" name="search" value="{{ $search }}" maxlength="255"></label>
        <label>Sort<select name="sort">
            @foreach (['newest' => 'Newest submission', 'oldest' => 'Oldest submission', 'az' => 'Name A–Z', 'za' => 'Name Z–A'] as $value => $label)
                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
            @endforeach
        </select></label>
        <button class="pc-button" type="submit">Apply</button>
    </form>
    {{-- Escaped feedback makes server validation visible even when client constraints are bypassed. --}}
    @if(session('status'))<p role="status" class="pc-panel">{{ session('status') }}</p>@endif
    @if($errors->any())<div role="alert" class="pc-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <section class="pc-panel pc-list" aria-label="Filtered Product queue">
        @forelse($products as $product)
            <article class="pc-row"><div><h2>{{ $product->name }}</h2><p>{{ $product->seller?->business_name ?? 'Seller unavailable' }} · {{ $product->sku ?: 'No SKU' }}</p><p class="pc-muted">{{ $product->status }} · {{ $product->stock }} in stock · Submitted {{ $product->submitted_at?->format('M j, Y g:i A') ?? 'Not recorded (existing catalogue)' }}</p></div><a href="{{ route('admin.product-compliance.show', $product) }}">Inspect Product #{{ $product->id }}</a></article>
        @empty
            <h2>No matching Products</h2><p class="pc-muted">There are no Products in this status matching the current search.</p>
        @endforelse
    </section>
    {{ $products->links() }}
</div>
@endsection
