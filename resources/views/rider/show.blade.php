<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Delivery #{{ $delivery->id }} — ShopHop</title>@vite(['resources/css/app.css'])</head>
<body class="min-h-screen bg-gray-50 p-6 text-navy"><main class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow">
    <a class="text-teal underline" href="{{ route('rider.deliveries.index') }}">Assigned deliveries</a>
    <h1 class="mt-4 text-2xl font-bold">Order #{{ $delivery->order_id }}</h1>
    <p>{{ $delivery->order?->seller?->business_name ?? 'Seller' }} · {{ str_replace('_', ' ', ucfirst($delivery->status)) }}</p>

    {{-- COD due is the persisted Seller Order total; cash status comes only from its settlement record. --}}
    @if ($delivery->order?->payment_method === 'cod')
        <p class="mt-3 font-semibold">COD amount due: ₱{{ number_format((float) $delivery->order->total_amount, 2) }}</p>
        <p>Settlement: {{ $delivery->codSettlement ? ucfirst($delivery->codSettlement->status) : 'Not recorded' }}</p>
        @if ($delivery->codSettlement)
            <p>Collected: ₱{{ number_format((float) $delivery->codSettlement->collected_amount, 2) }} at {{ $delivery->codSettlement->collected_at->format('M j, Y g:i A') }}</p>
        @elseif ($delivery->status === 'delivered')
            <p>Legacy delivery: cash collection was not recorded.</p>
        @endif
        <a class="text-teal underline" href="{{ route('rider.settlements.index') }}">COD settlements</a>
    @endif
    {{-- Recipient contact comes from the assigned Order snapshot and is needed for this delivery only. --}}
    <dl class="mt-5 space-y-2"><div><dt class="font-semibold">Recipient</dt><dd>{{ $delivery->order?->delivery_name ?: 'Unavailable' }}</dd></div>
        <div><dt class="font-semibold">Address</dt><dd>{{ $delivery->order?->delivery_address ?: 'Unavailable' }}</dd></div>
        <div><dt class="font-semibold">Contact</dt><dd>{{ $delivery->order?->delivery_phone ?: 'Unavailable' }}</dd></div>
        <div><dt class="font-semibold">Pickup</dt><dd>{{ $delivery->picked_up_at?->format('M j, Y g:i A') ?? 'Pending' }}</dd></div>
        <div><dt class="font-semibold">Transit</dt><dd>{{ $delivery->in_transit_at?->format('M j, Y g:i A') ?? 'Pending' }}</dd></div>
        <div><dt class="font-semibold">Completion</dt><dd>{{ $delivery->delivered_at?->format('M j, Y g:i A') ?? 'Pending' }}</dd></div></dl>
    @if (session('status')) <p role="status" class="mt-4 text-teal">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p role="alert" class="mt-4 text-red-700">{{ $errors->first() }}</p> @endif
    {{-- Each action is available at one persisted state; server transitions recheck ownership and current state. --}}
    @if ($delivery->status === 'assigned')
        <form method="POST" action="{{ route('rider.deliveries.pickup', $delivery) }}" class="mt-5">@csrf<button class="rounded bg-navy px-5 py-2 text-white">Confirm pickup</button></form>
    @elseif ($delivery->status === 'picked_up')
        <form method="POST" action="{{ route('rider.deliveries.transit', $delivery) }}" class="mt-5">@csrf<button class="rounded bg-navy px-5 py-2 text-white">Mark in transit</button></form>
    @elseif ($delivery->status === 'in_transit')

        @if ($delivery->order?->payment_method === 'cod')
        {{-- Delivery completion requires one private photo tied to the authenticated Rider event. --}}
        <form method="POST" enctype="multipart/form-data" action="{{ route('rider.deliveries.complete', $delivery) }}" class="mt-5 space-y-3">@csrf
            <label for="proof" class="block">Delivery photo</label><input id="proof" name="proof" type="file" accept="image/jpeg,image/png,image/webp" required>
            {{-- The explicit Rider declaration is required; delivery photo alone never records payment. --}}
            <label class="flex items-center gap-2"><input name="cash_collected" type="checkbox" value="1" required> I collected ₱{{ number_format((float) $delivery->order->total_amount, 2) }} in COD cash.</label>
            <button class="block rounded bg-navy px-5 py-2 text-white">Complete delivery and record COD collection</button>
        </form>
        @else
            {{-- A forged historical non-COD Delivery cannot enter the COD completion form. --}}
            <p class="mt-5">This payment method cannot be completed through COD delivery.</p>
        @endif
    @elseif ($delivery->status === 'delivered' && $delivery->proof_path)
        <a class="mt-5 inline-block text-teal underline" href="{{ route('delivery.proof', $delivery) }}">View delivery proof</a>
    @endif
</main></body></html>
