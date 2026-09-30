@extends('admin.layout')
@section('title', 'Inspect Product Compliance')
@include('admin.product-compliance.styles')
@section('content')
{{-- Persisted detail separates review history from compatibility approval and inventory availability. --}}
<div class="compliance-workspace">
    <a href="{{ route('admin.product-compliance.index', ['filter' => $product->compliance_status]) }}">Back to Product Compliance</a>
    <h1>{{ $product->name }}</h1>
    <p>{{ $product->seller?->business_name ?? 'Seller unavailable' }} · Product #{{ $product->id }}</p>
    @if(session('status'))<p class="pc-panel" role="status">{{ session('status') }}</p>@endif
    @if($errors->any())<div class="pc-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <div class="pc-grid">
        <section class="pc-panel"><h2>Product details</h2><p>Compliance: {{ ucwords(str_replace('_', ' ', $product->compliance_status)) }}</p><p>Inventory: {{ $product->status }} · {{ $product->stock }} in stock</p><p>Category: {{ $product->category }}</p><p>SKU: {{ $product->sku ?: 'Not supplied' }}</p><p>Price: ₱{{ number_format((float)$product->price, 2) }}</p><p>{{ $product->description ?: 'No description supplied.' }}</p>
            @foreach($product->variants as $variant)<p>Variant: {{ $variant->name }} · {{ $variant->stock }} in stock · {{ $variant->status }}</p>@endforeach
        </section>
        <section class="pc-panel"><h2>Review history</h2><p>Submitted: {{ $product->submitted_at?->format('M j, Y g:i A') ?? 'Not recorded' }}</p><p>Reviewed: {{ $product->reviewed_at?->format('M j, Y g:i A') ?? 'No human review recorded' }}</p><p>Reviewer user ID: {{ $product->reviewed_by ?? 'None' }}</p>
            @if($product->rejection_reason)<p>Reason: {{ $reasons[$product->rejection_reason] ?? $product->rejection_reason }}</p><p>{{ $product->rejection_notes }}</p>@endif
        </section>
    </div>
    {{-- Escaped storage URLs and text avoid the reference modal's HTML interpolation of Product data. --}}
    <section class="pc-panel"><h2>Product images</h2><div class="pc-images">@forelse($product->images as $image)<img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $product->name }}">@empty @if($product->image)<img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">@else<p>No images supplied.</p>@endif @endforelse</div></section>
    {{-- Explicit native confirmation sections remain keyboard usable and never promise archived merchandise is live. --}}
    <div class="pc-grid">
        <details class="pc-panel"><summary>Approve Product</summary><p>Confirm approval for Buyer visibility when active and stocked. This clears rejection details.</p><form method="POST" action="{{ route('admin.product-compliance.approve', $product) }}">@csrf @method('PATCH')<button class="pc-button" type="submit">Confirm approval</button></form></details>
        <details class="pc-panel" @if($errors->any()) open @endif><summary>Reject Product</summary><p>Rejecting hides this Product from Buyer discovery and prevents new purchases. The Seller may correct and resubmit it.</p><form method="POST" action="{{ route('admin.product-compliance.reject', $product) }}">@csrf @method('PATCH')<div class="pc-controls"><label>Rejection reason<select name="rejection_reason" required><option value="">Choose a reason</option>@foreach($reasons as $value => $label)<option value="{{ $value }}" @selected(old('rejection_reason') === $value)>{{ $label }}</option>@endforeach</select></label><label>Notes (optional)<textarea name="rejection_notes" rows="3" maxlength="2000">{{ old('rejection_notes') }}</textarea></label></div><button class="pc-button" type="submit">Confirm rejection</button></form></details>
    </div>
</div>
@endsection
