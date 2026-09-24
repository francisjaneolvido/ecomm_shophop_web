{{-- A Seller card uses this Order's recorded items and total; a checkout group never merges shop portions. --}}
<article class="rounded-xl border border-gray-border bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('seller.orders.show', $order) }}" class="text-base font-bold text-teal-dark hover:underline">
                Order SHP-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}
            </a>
            <p class="mt-1 text-xs text-navy/55">Placed {{ $order->created_at->format('M j, Y g:i A') }}</p>
        </div>
        <span class="rounded-full bg-teal/10 px-3 py-1 text-xs font-semibold text-teal-dark">{{ $order->statusLabel() }}</span>
    </div>

    {{-- Buyer identity and delivery facts come from the Seller-owned Order only. --}}
    <p class="mt-4 text-sm text-navy/70">Buyer: {{ trim(($order->buyer?->first_name ?? '') . ' ' . ($order->buyer?->last_name ?? '')) ?: 'Buyer' }}</p>
    <ul class="mt-3 space-y-2 border-t border-gray-border pt-3">
        @foreach ($order->items as $item)
            <li class="flex flex-wrap justify-between gap-2 text-sm text-navy/70">
                <span>{{ $item->product?->name ?? 'Product unavailable' }} · {{ $item->variantLabel() }} · Qty {{ $item->quantity }}</span>
                <span>₱{{ number_format((float) $item->price, 2) }} each</span>
            </li>
        @endforeach
    </ul>
    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-border pt-3 text-sm">
        <span class="text-navy/60">{{ ucfirst($order->shipping_method ?? 'Shipping unavailable') }} · {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Payment verification unavailable' }}</span>
        <strong class="text-navy">Order total ₱{{ number_format((float) $order->total_amount, 2) }}</strong>
    </div>
    <a href="{{ route('seller.orders.show', $order) }}" class="mt-4 inline-block text-sm font-semibold text-teal-dark hover:underline">View Order details</a>
</article>
