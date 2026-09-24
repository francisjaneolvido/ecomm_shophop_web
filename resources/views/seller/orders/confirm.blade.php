@extends('seller.partials.layout')

@section('title', 'Delivery Confirmation')

@section('content')
{{-- Delivery confirmation and proof require Logistics or Buyer evidence; Sellers have no transition here. --}}
<section class="mx-auto max-w-3xl rounded-xl border border-gray-border bg-white p-8">
    <h1 class="text-2xl font-bold text-navy">Delivery Confirmation</h1>
    <p class="mt-3 text-sm text-navy/65">Delivery confirmation is unavailable until Logistics records pickup and delivery. Sellers cannot mark an Order delivered or upload proof here.</p>
    <a href="{{ route('seller.orders.courier') }}" class="mt-5 inline-block text-sm font-semibold text-teal-dark hover:underline">View orders ready for pickup</a>
</section>
@endsection
