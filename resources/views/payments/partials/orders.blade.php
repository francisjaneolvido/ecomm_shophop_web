{{-- Membership and money remain controller-filtered persisted Seller Orders; long names wrap. --}}
<section class="pay-panel pay-pad" aria-labelledby="seller-orders-heading">
    <div class="pay-section-head"><span class="pay-icon"><x-lucide-store aria-hidden="true" /></span><h2 id="seller-orders-heading">Seller Orders</h2></div>
    <p class="pay-muted pay-small" style="margin-bottom: 1rem">These Orders belong to this checkout payment.</p>
    <ul class="pay-orders">
        @forelse ($payment->orders as $order)
            <li class="pay-order"><div><strong>{{ $order->seller?->business_name ?? 'Shop' }}</strong><span>Order #{{ $order->id }} · {{ $order->statusLabel() }}</span></div><div class="pay-money">₱{{ number_format((float) $order->total_amount, 2) }}</div></li>
        @empty
            <li class="pay-muted">No linked Seller Orders are available.</li>
        @endforelse
    </ul>
</section>
