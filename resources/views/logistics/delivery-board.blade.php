@extends('logistics.layouts')

@section('title', 'Deliveries — ShopHop Logistics')

@section('content')
{{-- Every card comes from a Seller Order or partner-owned Delivery; forms replace preview drag and fake waybills. --}}
<div class="mb-6">
    <h1 class="text-navy text-2xl sm:text-3xl font-bold">Deliveries</h1>
    {{-- The board reflects Rider events and private proof; live GPS remains outside this workflow. --}}
    <p class="text-navy/55 text-sm mt-1">Persisted Rider milestones and delivery proof. Live GPS is unavailable.</p>
</div>

@if (session('status')) <p role="status" class="mb-4 rounded-xl bg-teal-light p-3 text-teal-dark">{{ session('status') }}</p> @endif
@if ($errors->any()) <p role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-red-700">{{ $errors->first() }}</p> @endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <section class="rounded-2xl bg-gray-bg p-4">
        <h2 class="text-navy font-bold mb-3">Ready for pickup <span class="text-navy/45">({{ $ready->count() }})</span></h2>
        <div class="space-y-3">
            @forelse ($ready as $order)
                <article class="rounded-xl border border-gray-border bg-white p-4">
                    <p class="text-navy font-semibold">Order #{{ $order->id }} · {{ $order->seller?->business_name ?? 'Seller unavailable' }}</p>
                    <p class="text-xs text-navy/60 mt-1">{{ $order->delivery_name ?? 'Buyer unavailable' }} · {{ $order->delivery_address ?? 'Destination unavailable' }}</p>
                    <p class="text-xs text-navy/60 mt-1">{{ $order->items->sum('quantity') }} items · {{ $order->shipping_method }} · {{ strtoupper($order->payment_method) }} · ₱{{ number_format($order->total_amount, 2) }}</p>
                    {{-- Package contents and recipient contact come from the recorded Order, not fixture waybills. --}}
                    <p class="text-xs text-navy/60 mt-1">Contact: {{ $order->delivery_phone ?? 'Unavailable' }}</p>
                    <ul class="mt-2 text-xs text-navy/60 space-y-1">
                        @foreach ($order->items as $item) <li>{{ $item->quantity }} × {{ $item->product?->name ?? 'Product unavailable' }}</li> @endforeach
                    </ul>
                    @if ($riders->isNotEmpty())
                        <form method="POST" action="{{ route('logistics.deliveries.assign', $order) }}" class="mt-3 flex flex-wrap gap-2 items-center">
                            @csrf
                            <label for="rider-{{ $order->id }}" class="sr-only">Rider for Order #{{ $order->id }}</label>
                            <select id="rider-{{ $order->id }}" name="rider_id" required class="rounded-full border border-gray-border px-3 py-2 text-sm">
                                <option value="">Choose Rider</option>
                                @foreach ($riders as $rider) <option value="{{ $rider->id }}">{{ $rider->name }} · {{ $rider->vehicle_type }}</option> @endforeach
                            </select>
                            <button class="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-teal">Assign Rider</button>
                        </form>
                    @else
                        <p class="mt-3 text-sm text-amber-700">Add an active Rider before assignment.</p>
                    @endif
                </article>
            @empty
                <p class="text-sm text-navy/50">No eligible ready Orders in your registered coverage.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl bg-gray-bg p-4">
        <h2 class="text-navy font-bold mb-3">Your deliveries <span class="text-navy/45">({{ $deliveries->count() }})</span></h2>
        <div class="space-y-3">
            @forelse ($deliveries as $delivery)
                <article class="rounded-xl border border-gray-border bg-white p-4">
                    <p class="text-navy font-semibold">Order #{{ $delivery->order_id }} · {{ $delivery->order?->seller?->business_name ?? 'Seller unavailable' }}</p>
                    <p class="text-xs text-navy/60 mt-1">{{ $delivery->order?->delivery_address ?? 'Destination unavailable' }}</p>
                    <p class="text-xs text-navy/60 mt-1">Rider: {{ $delivery->rider?->name ?? 'Unavailable' }} · {{ str_replace('_', ' ', ucfirst($delivery->status)) }}</p>
                    {{-- Assigned records retain the same recorded package and COD facts through completion. --}}
                    <p class="text-xs text-navy/60 mt-1">{{ $delivery->order?->delivery_name ?? 'Recipient unavailable' }} · {{ $delivery->order?->delivery_phone ?? 'Contact unavailable' }} · {{ strtoupper($delivery->order?->payment_method ?? '') }} · ₱{{ number_format($delivery->order?->total_amount ?? 0, 2) }}</p>
                    <ul class="mt-2 text-xs text-navy/60 space-y-1">
                        @foreach ($delivery->order?->items ?? [] as $item) <li>{{ $item->quantity }} × {{ $item->product?->name ?? 'Product unavailable' }}</li> @endforeach
                    </ul>
                    {{-- These event times are the persisted milestone history; no GPS coordinates are implied. --}}
                    <ol class="mt-2 text-xs text-navy/50 space-y-1">
                        <li>Assigned {{ $delivery->assigned_at?->format('M j, Y g:i A') }}</li>
                        @if ($delivery->picked_up_at) <li>Picked up {{ $delivery->picked_up_at->format('M j, Y g:i A') }}</li> @endif
                        @if ($delivery->in_transit_at) <li>In transit {{ $delivery->in_transit_at->format('M j, Y g:i A') }}</li> @endif
                        @if ($delivery->delivered_at) <li>Delivered {{ $delivery->delivered_at->format('M j, Y g:i A') }}</li> @endif
                    </ol>
                    {{-- The operator observes Rider-owned events and proof without a competing mutation path. --}}
                    @if ($delivery->pickup_rider_id) <p class="text-xs text-navy/60">Pickup by {{ $delivery->pickupRider?->name }}</p> @endif
                    @if ($delivery->delivered_rider_id) <p class="text-xs text-navy/60">Completed by {{ $delivery->deliveredRider?->name }}</p> @endif
                    @if ($delivery->proof_path) <a class="text-xs text-teal underline" href="{{ route('delivery.proof', $delivery) }}">View delivery proof</a> @endif
                </article>
            @empty
                <p class="text-sm text-navy/50">No assignments yet.</p>
            @endforelse
        </div>
    </section>
</div>
{{-- The removed fixture modal and drag script had no server transition; the forms above own every persisted move. --}}
@endsection
