<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rider task — ShopHop</title>@vite(['resources/css/app.css'])</head>
<body class="min-h-screen bg-gray-50 p-6 text-navy"><main class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow">
    <a class="text-teal underline" href="{{ route('rider.deliveries.index') }}">Rider assignments</a>
    <div class="mt-4 flex flex-wrap items-start justify-between gap-3"><div><h1 class="text-2xl font-bold">{{ $delivery->tracking_code ?? 'Order #'.$delivery->order_id }}</h1><p>Order #{{ $delivery->order_id }} · {{ $delivery->order?->seller?->business_name ?? 'Seller' }}</p></div><span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold">{{ $delivery->statusLabel() }}</span></div>

    @if (session('status')) <p role="status" class="mt-4 rounded bg-teal-50 p-3 text-teal-800">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p role="alert" class="mt-4 rounded bg-red-50 p-3 text-red-700">{{ $errors->first() }}</p> @endif

    <dl class="mt-5 space-y-2 text-sm">
        <div><dt class="font-semibold">Recipient</dt><dd>{{ $delivery->order?->delivery_name ?: 'Unavailable' }}</dd></div>
        <div><dt class="font-semibold">Delivery address</dt><dd>{{ $delivery->order?->delivery_address ?: 'Unavailable' }}</dd></div>
        <div><dt class="font-semibold">Contact</dt><dd>{{ $delivery->order?->delivery_phone ?: 'Unavailable' }}</dd></div>
        <div><dt class="font-semibold">Destination area</dt><dd>{{ $delivery->destinationArea?->area_name ?? 'Not sorted yet' }}</dd></div>
    </dl>

    @if ($delivery->order?->payment_method === 'cod')
        <div class="mt-4 rounded-xl bg-gray-50 p-4"><p class="font-semibold">COD amount: ₱{{ number_format((float) $delivery->order->total_amount, 2) }}</p><p class="text-sm">Settlement: {{ $delivery->codSettlement ? ucfirst($delivery->codSettlement->status) : 'Not recorded' }}</p></div>
    @elseif ($delivery->order?->isPaymentEligible())
        <div class="mt-4 rounded-xl bg-gray-50 p-4"><p class="font-semibold">Payment verified by ShopHop Admin</p><p class="text-sm">Amount to collect: ₱0.00</p></div>
    @endif

    @php $currentRiderId = auth('rider')->id(); @endphp

    @if ($delivery->rider_id === $currentRiderId && $delivery->status === \App\Models\Logistics\Delivery::PICKUP_ASSIGNED)
        <form method="POST" action="{{ route('rider.deliveries.accept-pickup', $delivery) }}" class="mt-5">@csrf<button class="rounded bg-navy px-5 py-2 text-white">Accept Pickup Assignment</button></form>
    @elseif ($delivery->rider_id === $currentRiderId && $delivery->status === \App\Models\Logistics\Delivery::PICKUP_ACCEPTED)
        <p class="mt-5 text-sm text-navy/60">Go to the Seller, collect the parcel, then confirm pickup.</p>
        <form method="POST" action="{{ route('rider.deliveries.pickup', $delivery) }}" class="mt-3">@csrf<button class="rounded bg-navy px-5 py-2 text-white">Confirm Parcel Pickup</button></form>
    @elseif ($delivery->pickup_rider_id === $currentRiderId && $delivery->status === \App\Models\Logistics\Delivery::PICKED_UP)
        <p class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">Bring the parcel to the sorting center. Logistics must scan <strong>{{ $delivery->tracking_code }}</strong> to receive it.</p>
    @elseif ($delivery->rider_id === $currentRiderId && $delivery->delivery_rider_id === $currentRiderId && $delivery->status === \App\Models\Logistics\Delivery::DELIVERY_ASSIGNED)
        <p class="mt-5 text-sm text-navy/60">Pick up the sorted parcel from the sorting center before dispatch.</p>
        <form method="POST" action="{{ route('rider.deliveries.out-for-delivery', $delivery) }}" class="mt-3">@csrf<button class="rounded bg-navy px-5 py-2 text-white">Mark Out for Delivery</button></form>
    @elseif ($delivery->rider_id === $currentRiderId && $delivery->delivery_rider_id === $currentRiderId && $delivery->status === \App\Models\Logistics\Delivery::OUT_FOR_DELIVERY)
        @if ($delivery->order?->isPaymentEligible())
            <form method="POST" enctype="multipart/form-data" action="{{ route('rider.deliveries.complete', $delivery) }}" class="mt-5 space-y-3 rounded-xl border p-4">@csrf
                <p class="font-semibold">Successful delivery</p>
                <label for="proof" class="block text-sm">Delivery photo</label><input id="proof" name="proof" type="file" accept="image/jpeg,image/png,image/webp" required>
                @if ($delivery->order->payment_method === 'cod')<label class="flex items-center gap-2 text-sm"><input name="cash_collected" type="checkbox" value="1" required> I collected ₱{{ number_format((float) $delivery->order->total_amount, 2) }} COD cash.</label>@endif
                <button class="rounded bg-navy px-5 py-2 text-white">Mark Delivered</button>
            </form>
        @endif
        <form method="POST" action="{{ route('rider.deliveries.fail', $delivery) }}" class="mt-4 space-y-3 rounded-xl border border-red-200 p-4">@csrf
            <p class="font-semibold text-red-700">Delivery failed</p>
            <label for="failure-reason" class="block text-sm">Reason</label>
            <select id="failure-reason" name="failure_reason" required class="w-full rounded border px-3 py-2 text-sm">
                <option value="">Choose reason</option><option>Customer unavailable</option><option>Incorrect address</option><option>Customer refused</option><option>Unable to contact customer</option><option>Vehicle issue</option><option>Weather / access issue</option><option>Other</option>
            </select>
            <button class="rounded border border-red-500 px-5 py-2 text-red-700">Record Failed Attempt</button>
        </form>
    @elseif ($delivery->status === \App\Models\Logistics\Delivery::DELIVERED)
        <p class="mt-5 rounded-xl bg-teal-50 p-4 text-sm text-teal-800">Delivered successfully. Waiting for Buyer confirmation before the Order becomes completed.</p>
        @if ($delivery->proof_path)<a class="mt-3 inline-block text-teal underline" href="{{ route('delivery.proof', $delivery) }}">View delivery proof</a>@endif
    @elseif ($delivery->status === \App\Models\Logistics\Delivery::DELIVERY_FAILED)
        <p class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">Failure recorded: {{ $delivery->failure_reason }}. Logistics will reschedule or return the parcel.</p>
    @endif
</main></body></html>
