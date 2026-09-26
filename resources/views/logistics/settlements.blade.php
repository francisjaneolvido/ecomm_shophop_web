@extends('logistics.layouts')

@section('title', 'COD Settlements — ShopHop Logistics')

@section('content')
<section class="space-y-4">
    <h1 class="text-2xl font-bold text-navy">COD settlements</h1>
    <p class="text-sm text-navy/60">Receipt confirmation records cash received from a Rider. Seller payout is separate.</p>
    @if (session('status')) <p role="status" class="rounded bg-teal/10 p-3">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p role="alert" class="rounded bg-red-50 p-3 text-red-700">{{ $errors->first() }}</p> @endif
    {{-- Only this partner's delivered records appear; missing legacy cash facts stay explicitly unrecorded. --}}
    @forelse ($deliveries as $delivery)
        <article class="rounded-xl border border-gray-border bg-white p-5">
            <h2 class="font-semibold">Order #{{ $delivery->order_id }} · {{ $delivery->order?->seller?->business_name ?? 'Seller' }}</h2>
            <p>Assigned Rider: {{ $delivery->rider?->name ?? 'Unavailable' }}</p>
            @if ($cash = $delivery->codSettlement)
                <p>Expected / collected: ₱{{ number_format((float) $cash->expected_amount, 2) }} / ₱{{ number_format((float) $cash->collected_amount, 2) }}</p>
                <p>Collected by {{ $cash->collector?->name ?? 'Rider' }} at {{ $cash->collected_at->format('M j, Y g:i A') }}</p>
                @if ($cash->status === \App\Models\Logistics\CodSettlement::COLLECTED)
                    <p>Awaiting Rider remittance · cash remains with Rider</p>
                @elseif ($cash->status === \App\Models\Logistics\CodSettlement::REMITTED)
                    <p>Awaiting receipt confirmation · remitted by {{ $cash->remitter?->name ?? 'Rider' }} at {{ $cash->remitted_at?->format('M j, Y g:i A') }}</p>
                    <form class="mt-3" method="POST" action="{{ route('logistics.settlements.reconcile', $delivery) }}">@csrf
                        <button class="rounded bg-navy px-4 py-2 text-white">Confirm receipt of ₱{{ number_format((float) $cash->collected_amount, 2) }}</button>
                    </form>
                @else
                    <p>Logistics receipt reconciled · ₱{{ number_format((float) $cash->received_amount, 2) }} confirmed at {{ $cash->reconciled_at?->format('M j, Y g:i A') }}</p>
                @endif
            @else
                <p>Settlement not recorded for this legacy delivery.</p>
            @endif
        </article>
    @empty
        <p>No delivered COD records for this Logistics partner.</p>
    @endforelse
</section>
@endsection
