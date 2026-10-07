@extends('layouts.app')

{{-- Preserve the existing Buyer navigation while keeping the Store destination explicitly informational. --}}
@section('title', 'Store availability - ShopHop')
@section('hideChrome', true)

@section('content')
@include('buyer.partials.navbar-buyer')

{{-- No public Seller lookup exists; fixture identity, metrics and local Follow/Claim success cannot stand in for one. --}}
<section id="buyerStore" aria-labelledby="buyerStoreTitle" class="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-10">
    <h1 id="buyerStoreTitle" class="text-2xl font-bold text-navy">Store availability</h1>
    <div class="mt-5 rounded-xl border border-gray-border bg-white p-5 sm:p-6">
        <span class="inline-flex rounded-full bg-teal/10 px-3 py-1 text-xs font-semibold text-teal-dark">Unavailable</span>
        <h2 class="mt-4 text-lg font-semibold text-navy">Individual Store pages are not available yet.</h2>
        <p class="mt-2 text-sm text-navy/60">This address does not open a live shop.</p>
        <p class="mt-2 text-sm text-navy/60">Store browsing and shop interactions are unavailable.</p>

        {{-- Marketplace Search is a separate public workflow, not a replacement Seller catalogue or dead Product link. --}}
        <a href="{{ route('search.index') }}" class="mt-5 inline-flex rounded-lg bg-teal px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal">
            Search ShopHop products
        </a>
    </div>
</section>
@endsection
