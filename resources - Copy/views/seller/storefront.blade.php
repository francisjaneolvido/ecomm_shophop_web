@extends('seller.partials.layout')

@section('title', 'Storefront availability')

@section('content')
{{-- Keep the existing destination usable without inventing management from Seller registration or Product data. --}}
<section id="sellerStorefront" aria-labelledby="storefrontTitle" class="mx-auto max-w-5xl space-y-5">
    <div>
        <h1 id="storefrontTitle" class="text-2xl font-bold text-navy">Storefront availability</h1>
        <p class="mt-1 text-sm text-navy/55">Storefront features on ShopHop.</p>
    </div>

    {{-- Informational copy replaces the missing view; unsupported management has no action controls or shop metrics. --}}
    <div class="rounded-xl border border-gray-border bg-white p-5 sm:p-6">
        <span class="inline-flex rounded-full bg-teal/10 px-3 py-1 text-xs font-semibold text-teal-dark">Unavailable</span>
        <h2 class="mt-4 text-lg font-semibold text-navy">Storefront management is not available yet.</h2>
        <p class="mt-2 text-sm text-navy/60">Shop customization and publishing are unavailable.</p>
        <p class="mt-2 text-sm text-navy/60">This page does not provide a live shop preview.</p>

        {{-- Inventory and Account remain their own supported workflows, not a second Storefront system. --}}
        <p class="mt-4 text-sm text-navy/60">Manage products in Inventory and your core profile in Account.</p>
    </div>
</section>
@endsection
