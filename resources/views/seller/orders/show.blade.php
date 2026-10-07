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

        {{-- Logistics owns these persisted milestones after Seller readiness; the Seller has no transition control. --}}
        @if ($order->delivery)
        <p class="mt-3 text-sm text-navy/65">Logistics: {{ $order->delivery->statusLabel() }} · Rider: {{ $order->delivery->rider?->name ?? 'Unavailable' }}</p>
        {{-- Seller reads the completed Rider event and private proof for its own Order only. --}}
        @if ($order->delivery->picked_up_at) <p class="text-sm text-navy/65">Picked up {{ $order->delivery->picked_up_at->format('M j, Y g:i A') }}</p> @endif
        @if ($order->delivery->delivered_at) <p class="text-sm text-navy/65">Delivered {{ $order->delivery->delivered_at->format('M j, Y g:i A') }} by {{ $order->delivery->deliveredRider?->name ?? 'Rider' }}</p> @endif
        @if ($order->delivery->proof_path) <a class="text-sm text-teal underline" href="{{ route('delivery.proof', $order->delivery) }}">View delivery proof</a> @endif
        @endif

        @if ($order->tracking_code)
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-teal/20 bg-teal/5 p-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-navy/40">Shipment tracking code</p>
                    <p class="mt-1 font-mono text-lg font-bold tracking-wider text-navy">{{ $order->tracking_code }}</p>
                    @if ($order->pickupRequest)
                        <p class="mt-1 text-xs text-navy/55">Pickup requested from {{ $order->pickupRequest->partner?->company_name ?? 'Logistics' }}@if($order->pickupRequest->originSortingCenter) · Origin: {{ $order->pickupRequest->originSortingCenter->name }}@endif</p>
                    @endif
                </div>
                <a href="{{ route('seller.orders.shipping-label', $order) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-teal/30 bg-white px-4 py-2 text-sm font-semibold text-teal-dark hover:bg-teal-light">
                    <x-lucide-printer class="h-4 w-4" /> Print Shipping Label
                </a>
            </div>
        @endif

        @php($parcelTimeline = $order->trackingTimeline())
        @if ($parcelTimeline)
            <div class="mt-4 rounded-xl border border-gray-border bg-gray-bg/40 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-navy">Parcel Journey</p>
                        <p class="mt-0.5 text-xs text-navy/50">Real scans, branch transfers, and delivery reports for this order.</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1 text-[10px] font-semibold text-navy/45">{{ count($parcelTimeline) }} events</span>
                </div>

                <ol class="mt-4 space-y-3">
                    @foreach ($parcelTimeline as $event)
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full
                                {{ ($event['type'] ?? '') === 'failed' ? 'bg-red-400' : (($event['type'] ?? '') === 'success' ? 'bg-teal' : (($event['type'] ?? '') === 'transfer' ? 'bg-amber-400' : 'bg-navy/25')) }}"></span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs font-semibold text-navy">{{ $event['label'] }}</p>
                                    <span class="text-[10px] text-navy/35">{{ $event['time']->format('M j, Y g:i A') }}</span>
                                </div>
                                <p class="mt-0.5 text-xs leading-relaxed text-navy/55">{{ $event['note'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

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
            {{-- Seller sees recorded COD custody only; Logistics receipt does not mean Seller payout. --}}
            <div><dt class="text-navy/50">Payment</dt><dd class="font-semibold text-navy">
                @if ($order->payment_method === 'online' && $order->isPaymentEligible())
                    Online Payment · Verified by ShopHop Admin
                @elseif ($order->payment_method !== 'cod')
                    {{ $order->statusLabel() }}
                @elseif ($cash = $order->codSettlement)
                    COD collected · ₱{{ number_format((float) $cash->collected_amount, 2) }} at {{ $cash->collected_at->format('M j, Y g:i A') }}
                    @if ($cash->status === \App\Models\Logistics\CodSettlement::RECONCILED) · Logistics receipt confirmed @endif
                @elseif ($order->status === \App\Models\Buyer\Order\Order::STATUS_COMPLETED)
                    Cash on Delivery · collection not recorded
                @else
                    Cash on Delivery · payment due on delivery
                @endif
            </dd></div>
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

        {{-- Seller fulfillment follows the ERP sequence: Placed → Confirmed → Preparing → Ready for Pickup. --}}
        <div class="mt-7 border-t border-gray-border pt-5">
            @if ($order->isPaymentEligible() && $order->status === \App\Models\Buyer\Order\Order::STATUS_TO_SHIP)
                <div class="rounded-xl border border-teal/20 bg-teal/5 p-4">
                    <p class="text-sm font-semibold text-navy">Review stock and order details before accepting.</p>
                    <p class="mt-1 text-xs text-navy/55">Accepting records the ERP <strong>CONFIRMED</strong> stage. Preparation starts separately.</p>
                    <form class="mt-3" method="POST" action="{{ route('seller.orders.accept', $order) }}">
                        @csrf
                        @method('PATCH')
                        <button class="rounded-lg bg-teal px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-dark">Accept / Confirm Order</button>
                    </form>
                </div>
            @elseif ($order->isPaymentEligible() && $order->status === \App\Models\Buyer\Order\Order::STATUS_CONFIRMED)
                <div class="flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('seller.orders.start-preparation', $order) }}">
                        @csrf
                        @method('PATCH')
                        <button class="rounded-lg bg-teal px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-dark">Start Preparation</button>
                    </form>
                    <a target="_blank" href="{{ route('seller.orders.shipping-label', $order) }}" class="rounded-lg border border-gray-border bg-white px-5 py-2.5 text-sm font-semibold text-navy hover:border-teal/30 hover:text-teal-dark">Preview Shipping Label</a>
                </div>
            @elseif ($order->isPaymentEligible() && $order->status === \App\Models\Buyer\Order\Order::STATUS_PREPARING)
                <div class="rounded-xl border border-gray-border bg-gray-bg/50 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-navy">Packed and labeled?</p>
                            <p class="mt-1 text-xs text-navy/55">Select the Logistics / Sorting Center company that will collect this parcel. Only providers covering the Buyer destination are shown.</p>
                        </div>
                        <a target="_blank" href="{{ route('seller.orders.shipping-label', $order) }}" class="text-xs font-semibold text-teal-dark hover:underline">Print label</a>
                    </div>
                    @if ($errors->has('logistics_partner_id'))
                        <p class="mt-3 rounded-lg bg-red-50 p-2 text-xs text-red-700">{{ $errors->first('logistics_partner_id') }}</p>
                    @endif
                    <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" method="POST" action="{{ route('seller.orders.mark-ready', $order) }}">
                        @csrf
                        @method('PATCH')
                        <label class="flex-1 text-xs font-semibold text-navy">
                            Logistics / Sorting Center
                            <select name="logistics_partner_id" required class="mt-2 w-full rounded-lg border border-gray-border bg-white px-3 py-2.5 text-sm font-medium text-navy focus:border-teal focus:outline-none">
                                <option value="">Choose provider</option>
                                @foreach ($logisticsPartners as $partner)
                                    <option value="{{ $partner->id }}" @selected((string) old('logistics_partner_id') === (string) $partner->id)>{{ $partner->company_name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button @disabled($logisticsPartners->isEmpty()) class="rounded-lg bg-teal px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-dark disabled:cursor-not-allowed disabled:opacity-40">Mark Ready & Request Pickup</button>
                    </form>
                    @if ($logisticsPartners->isEmpty())
                        <p class="mt-3 text-xs text-amber-700">No approved Logistics partner currently covers this delivery destination. Keep the order in Preparing until coverage is available.</p>
                    @endif
                </div>
            @elseif ($order->status === \App\Models\Buyer\Order\Order::STATUS_READY_FOR_PICKUP)
                <p class="text-sm text-navy/65">Pickup request sent. Keep the parcel sealed and labeled while waiting for Logistics to assign a Rider.</p>
            @else
                <p class="text-sm text-navy/65">No Seller fulfillment action is available for this payment and Order state.</p>
            @endif
        </div>

    </div>
</section>
@endsection
