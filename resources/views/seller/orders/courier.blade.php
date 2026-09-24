@extends('seller.partials.layout')

@section('title', 'Ready for Pickup')

@section('content')
{{-- Seller readiness is persisted, but Rider assignment, pickup, waybills, and tracking await Logistics Operations. --}}
<section class="mx-auto max-w-5xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-navy">Ready for Pickup</h1>
        <p class="mt-1 text-sm text-navy/55">These Orders are ready. Courier assignment and pickup are pending Logistics.</p>
    </div>

    @forelse ($orders as $order)
        @include('seller.orders.partials.card', ['order' => $order])
    @empty
        <div class="rounded-xl border border-gray-border bg-white p-8 text-center text-sm text-navy/60">
            No orders are ready for pickup yet.
        </div>
    @endforelse
</section>
@endsection
