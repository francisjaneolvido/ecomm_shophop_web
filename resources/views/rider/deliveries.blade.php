<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rider assignments — ShopHop</title>@vite(['resources/css/app.css'])</head>
<body class="min-h-screen bg-gray-50 p-6 text-navy"><main class="mx-auto max-w-3xl">
    <header class="mb-8 flex items-center justify-between gap-4"><div><h1 class="text-2xl font-bold">Rider assignments</h1><p class="text-sm text-navy/55">Pickup and delivery tasks from your Logistics partner.</p></div>
        <div><a class="mr-4 underline" href="{{ route('rider.settlements.index') }}">COD settlements</a><form class="inline" method="POST" action="{{ route('rider.logout') }}">@csrf<button class="underline">Sign out</button></form></div></header>
    @forelse ($deliveries as $delivery)
        <article class="mb-4 rounded-2xl bg-white p-5 shadow">
            <div class="flex items-start justify-between gap-3"><div><h2 class="font-semibold">{{ $delivery->tracking_code ?? 'Order #'.$delivery->order_id }}</h2>
                <p class="text-sm">Order #{{ $delivery->order_id }} · {{ $delivery->order?->seller?->business_name ?? 'Seller' }}</p></div>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold">{{ $delivery->statusLabel() }}</span></div>
            @if ($delivery->destinationArea)<p class="mt-2 text-sm text-navy/60">Area: {{ $delivery->destinationArea->area_name }}</p>@endif
            <a class="mt-3 inline-block text-teal underline" href="{{ route('rider.deliveries.show', $delivery) }}">View task</a>
        </article>
    @empty <p>No assigned pickup or delivery tasks.</p> @endforelse
</main></body></html>
