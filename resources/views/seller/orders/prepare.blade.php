@extends('seller.partials.layout')

@section('title', 'Prepare Orders')

@section('content')
{{-- Preparation actions live on persisted Order details; packed-item checkboxes and waybills had no backend contract. --}}
<section class="mx-auto max-w-5xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-navy">Prepare Orders</h1>
        <p class="mt-1 text-sm text-navy/55">Open an Order to start preparation or mark it ready for pickup.</p>
    </div>

    @forelse ($orders as $order)
        @include('seller.orders.partials.card', ['order' => $order])
    @empty
        <div class="rounded-xl border border-gray-border bg-white p-8 text-center text-sm text-navy/60">
            No orders need preparation.
        </div>
    @endforelse
</section>
@endsection
