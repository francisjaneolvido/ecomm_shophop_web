<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Assigned deliveries — ShopHop</title>@vite(['resources/css/app.css'])</head>
<body class="min-h-screen bg-gray-50 p-6 text-navy"><main class="mx-auto max-w-3xl">
    <header class="mb-8 flex items-center justify-between"><h1 class="text-2xl font-bold">Assigned deliveries</h1>
        <form method="POST" action="{{ route('rider.logout') }}">@csrf<button class="underline">Sign out</button></form></header>
    {{-- Only server-scoped assignments are rendered; destination details are limited to each assigned job. --}}
    @forelse ($deliveries as $delivery)
        <article class="mb-4 rounded-2xl bg-white p-5 shadow">
            <h2 class="font-semibold">Order #{{ $delivery->order_id }} · {{ $delivery->order?->seller?->business_name ?? 'Seller' }}</h2>
            <p class="text-sm">Delivery: {{ str_replace('_', ' ', ucfirst($delivery->status)) }} · Order: {{ $delivery->order?->statusLabel() }}</p>
            <a class="mt-3 inline-block text-teal underline" href="{{ route('rider.deliveries.show', $delivery) }}">View delivery</a>
        </article>
    @empty <p>No assigned deliveries.</p> @endforelse
</main></body></html>
