@extends('seller.partials.layout')

@section('title', 'Order Notifications')

@section('content')
{{-- Persisted Seller Orders replace the preview notifications and local-only accept/decline controls. --}}
<section class="mx-auto max-w-5xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-navy">Order Notifications</h1>
        <p class="mt-1 text-sm text-navy/55">New purchases and current orders for your store.</p>
    </div>

    @forelse ($orders ?? [] as $order)
        @include('seller.orders.partials.card', ['order' => $order])
    @empty
        <div class="rounded-xl border border-gray-border bg-white p-8 text-center text-sm text-navy/60">
            No orders for your store yet.
        </div>
    @endforelse
</section>
@endsection
