@extends('seller.partials.layout')

@section('title', 'Order Details')

@section('content')
{{-- This page displays only the Order selected through the authenticated Seller's sellers.id scope. --}}
<section class="mx-auto max-w-4xl space-y-5">
    <a href="{{ route('seller.orders.notifications') }}" class="text-sm font-semibold text-teal-dark hover:underline">Back to Orders</a>
    <div class="rounded-xl border border-gray-border bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-navy">Order SHP-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</h1>
                <p class="mt-1 text-sm text-navy/55">Placed {{ $order->created_at->format('M j, Y g:i A') }}</p>
            </div>
            <span class="rounded-full bg-teal/10 px-3 py-1 text-sm font-semibold text-teal-dark">{{ $order->statusLabel() }}</span>
        </div>

        @if (session('status'))
            <p role="status" class="mt-5 rounded-lg bg-teal/10 p-3 text-sm text-teal-dark">{{ session('status') }}</p>
        @endif
        @if ($errors->has('status'))
            <p role="alert" class="mt-5 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first('status') }}</p>
        @endif

        {{-- Only persisted Buyer and delivery snapshot fields needed to prepare this shop's parcel are shown. --}}
        <dl class="mt-6 grid gap-4 border-t border-gray-border pt-5 text-sm sm:grid-cols-2">
            <div><dt class="text-navy/50">Buyer</dt><dd class="font-semibold text-navy">{{ trim(($order->buyer?->first_name ?? '') . ' ' . ($order->buyer?->last_name ?? '')) ?: 'Buyer' }}</dd></div>
            <div><dt class="text-navy/50">Recipient</dt><dd class="font-semibold text-navy">{{ $order->delivery_name ?: 'Unavailable' }}</dd></div>
            <div><dt class="text-navy/50">Delivery address</dt><dd class="font-semibold text-navy">{{ $order->delivery_address ?: 'Unavailable' }}</dd></div>
            <div><dt class="text-navy/50">Shipping</dt><dd class="font-semibold text-navy">{{ ucfirst($order->shipping_method ?? 'Unavailable') }}</dd></div>
            {{-- COD is due at delivery; this Order has no persisted paid or verified payment flag. --}}
            <div><dt class="text-navy/50">Payment</dt><dd class="font-semibold text-navy">{{ $order->payment_method === 'cod' ? 'Cash on Delivery; payment due on delivery' : 'Payment verification unavailable' }}</dd></div>
            @if ($order->note)
                <div><dt class="text-navy/50">Buyer note</dt><dd class="font-semibold text-navy">{{ $order->note }}</dd></div>
            @endif
        </dl>

        {{-- OrderItem.price is the recorded purchase price; current catalogue prices cannot rewrite this Order. --}}
        <h2 class="mt-7 text-base font-bold text-navy">Purchased Items</h2>
        <ul class="mt-3 divide-y divide-gray-border border-y border-gray-border">
            @foreach ($order->items as $item)
                <li class="flex flex-wrap justify-between gap-2 py-3 text-sm text-navy/70">
                    <span>{{ $item->product?->name ?? 'Product unavailable' }} · {{ $item->variantLabel() }} · Qty {{ $item->quantity }}</span>
                    <span>₱{{ number_format((float) $item->price, 2) }} each</span>
                </li>
            @endforeach
        </ul>
        <dl class="mt-4 space-y-2 text-sm text-navy/65">
            <div class="flex justify-between"><dt>Shipping fee</dt><dd>₱{{ number_format((float) $order->shipping_fee, 2) }}</dd></div>
            <div class="flex justify-between"><dt>COD fee</dt><dd>₱{{ number_format((float) $order->cod_fee, 2) }}</dd></div>
            <div class="flex justify-between"><dt>Voucher discount</dt><dd>₱{{ number_format((float) $order->voucher_discount, 2) }}</dd></div>
            <div class="flex justify-between border-t border-gray-border pt-3 font-bold text-navy"><dt>Recorded Order total</dt><dd>₱{{ number_format((float) $order->total_amount, 2) }}</dd></div>
        </dl>

        {{-- Only COD Orders can use the two Seller-owned transitions; forms use server status and CSRF protection. --}}
        <div class="mt-7 border-t border-gray-border pt-5">
            @if ($order->payment_method === 'cod' && $order->status === \App\Models\Buyer\Order\Order::STATUS_TO_SHIP)
                <form method="POST" action="{{ route('seller.orders.start-preparation', $order) }}">
                    @csrf
                    @method('PATCH')
                    <button class="rounded-lg bg-teal px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-dark">Start Preparation</button>
                </form>
            @elseif ($order->payment_method === 'cod' && $order->status === \App\Models\Buyer\Order\Order::STATUS_PREPARING)
                <form method="POST" action="{{ route('seller.orders.mark-ready', $order) }}">
                    @csrf
                    @method('PATCH')
                    <button class="rounded-lg bg-teal px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-dark">Mark Ready for Pickup</button>
                </form>
            @elseif ($order->status === \App\Models\Buyer\Order\Order::STATUS_READY_FOR_PICKUP)
                <p class="text-sm text-navy/65">Preparation complete. Courier assignment and pickup are pending Logistics.</p>
            @else
                <p class="text-sm text-navy/65">No Seller fulfillment action is available for this payment and Order state.</p>
            @endif
        </div>
    </div>
</section>
@endsection
