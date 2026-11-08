<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>COD settlements — ShopHop Rider</title>@vite(['resources/css/app.css'])</head>
<body class="min-h-screen bg-gray-50 p-6 text-navy"><main class="mx-auto max-w-3xl space-y-4">
    <header class="flex items-center justify-between"><h1 class="text-2xl font-bold">COD settlements</h1>
        <form method="POST" action="{{ route('rider.logout') }}">@csrf<button class="text-teal underline">Sign out</button></form></header>
    @if (auth('rider')->user()?->status === 'active') <a class="text-teal underline" href="{{ route('rider.deliveries.index') }}">Assigned deliveries</a> @endif
    @if (session('status')) <p role="status" class="rounded bg-teal/10 p-3">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p role="alert" class="rounded bg-red-50 p-3 text-red-700">{{ $errors->first() }}</p> @endif
    {{-- Cash custody is shown only for records attributed to this Rider; legacy delivery does not imply collection. --}}
    @forelse ($deliveries as $delivery)
        <article class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Order #{{ $delivery->order_id }} · {{ $delivery->order?->seller?->business_name ?? 'Seller' }}</h2>
            @if ($cash = $delivery->codSettlement)
                <p>Expected and collected: ₱{{ number_format((float) $cash->collected_amount, 2) }}</p>
                <p>Collected {{ $cash->collected_at->format('M j, Y g:i A') }} by {{ $cash->collector?->name ?? 'Rider' }}</p>
                @if ($cash->status === \App\Models\Logistics\CodSettlement::COLLECTED)
                    <p>Awaiting remittance · cash remains with Rider</p>
                    <form class="mt-3" method="POST" action="{{ route('rider.settlements.remit', $delivery) }}">@csrf
                        <button class="rounded bg-navy px-4 py-2 text-white">Submit exact cash remittance</button>
                    </form>
                @elseif ($cash->status === \App\Models\Logistics\CodSettlement::REMITTED)
                    <p>Remittance submitted {{ $cash->remitted_at?->format('M j, Y g:i A') }} · awaiting Logistics receipt</p>
                @else
                    <p>Logistics receipt confirmed {{ $cash->reconciled_at?->format('M j, Y g:i A') }}</p>
                @endif
            @elseif ($delivery->status === 'delivered')
                <p>Settlement not recorded for this legacy delivery.</p>
            @else
                <p>COD collection pending delivery.</p>
            @endif
        </article>
    @empty
        <p>No COD deliveries belong to this Rider.</p>
    @endforelse
</main></body></html>
