@extends('logistics.layouts.console')

@section('title', 'COD Settlements — ShopHop Logistics')

@section('content')
@php
    $awaitingRemittance = $deliveries->filter(fn ($delivery) => $delivery->codSettlement && $delivery->codSettlement->status === \App\Models\Logistics\CodSettlement::COLLECTED)->count();
    $awaitingReceipt = $deliveries->filter(fn ($delivery) => $delivery->codSettlement && $delivery->codSettlement->status === \App\Models\Logistics\CodSettlement::REMITTED)->count();
    $reconciled = $deliveries->filter(fn ($delivery) => $delivery->codSettlement && $delivery->codSettlement->status === \App\Models\Logistics\CodSettlement::RECONCILED)->count();
@endphp
<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">Finance · Cash custody</p>
            <h1 class="title">COD Settlements</h1>
            <p class="description">Track remittance and confirm receipt of COD collections turned over by riders. Seller payouts remain separate from this logistics cash-custody workflow.</p>
        </div>
    </header>

    @if (session('status')) <div role="status" class="sh-alert-success">{{ session('status') }}</div> @endif
    @if ($errors->any()) <div role="alert" class="sh-alert-error">{{ $errors->first() }}</div> @endif

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        <div class="sh-stat"><p class="sh-stat-label">Awaiting rider remittance</p><p class="sh-stat-value">{{ $awaitingRemittance }}</p><p class="sh-stat-note">Cash still with rider</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Awaiting receipt confirmation</p><p class="sh-stat-value">{{ $awaitingReceipt }}</p><p class="sh-stat-note">Ready for logistics acknowledgement</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Reconciled</p><p class="sh-stat-value">{{ $reconciled }}</p><p class="sh-stat-note">Received and confirmed</p></div>
    </div>

    <div class="sh-stack">
        @forelse ($deliveries as $delivery)
            <article class="sh-surface sh-card-pad">
                <div class="sh-toolbar items-start">
                    <div>
                        <h2 class="text-base font-bold text-navy">Order #{{ $delivery->order_id }}</h2>
                        <p class="mt-1 text-sm text-navy/60">{{ $delivery->order?->seller?->business_name ?? 'Seller unavailable' }}</p>
                        <p class="mt-1 text-sm text-navy/50">Assigned rider: {{ $delivery->rider?->name ?? 'Unavailable' }}</p>
                    </div>
                    @if ($cash = $delivery->codSettlement)
                        @php
                            $badge = match($cash->status) {
                                \App\Models\Logistics\CodSettlement::COLLECTED => 'sh-badge-warning',
                                \App\Models\Logistics\CodSettlement::REMITTED => 'sh-badge-info',
                                default => 'sh-badge-success',
                            };
                        @endphp
                        <span class="sh-badge {{ $badge }}">{{ ucfirst(strtolower($cash->status)) }}</span>
                    @else
                        <span class="sh-badge sh-badge-neutral">No settlement record</span>
                    @endif
                </div>

                @if ($cash)
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <div class="sh-surface-soft p-4"><p class="sh-stat-label">Expected</p><p class="mt-2 text-lg font-bold text-navy">₱{{ number_format((float) $cash->expected_amount, 2) }}</p></div>
                        <div class="sh-surface-soft p-4"><p class="sh-stat-label">Collected</p><p class="mt-2 text-lg font-bold text-navy">₱{{ number_format((float) $cash->collected_amount, 2) }}</p></div>
                        <div class="sh-surface-soft p-4"><p class="sh-stat-label">Recorded by</p><p class="mt-2 text-sm font-semibold text-navy">{{ $cash->collector?->name ?? 'Rider' }}</p><p class="mt-1 text-xs text-navy/45">{{ $cash->collected_at?->format('M j, Y g:i A') }}</p></div>
                    </div>

                    @if ($cash->status === \App\Models\Logistics\CodSettlement::COLLECTED)
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Awaiting rider remittance. Cash remains with the rider until physically turned over.</div>
                    @elseif ($cash->status === \App\Models\Logistics\CodSettlement::REMITTED)
                        <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                            Awaiting receipt confirmation · remitted by {{ $cash->remitter?->name ?? 'Rider' }} at {{ $cash->remitted_at?->format('M j, Y g:i A') }}
                        </div>
                        <form class="mt-4" method="POST" action="{{ route('logistics.settlements.reconcile', $delivery) }}" data-confirm-title="Confirm receipt of COD cash?" data-confirm-message="This will mark the remitted amount as officially received by your logistics team." data-confirm-button="Confirm receipt">
                            @csrf
                            <button class="sh-btn">Confirm receipt of ₱{{ number_format((float) $cash->collected_amount, 2) }}</button>
                        </form>
                    @else
                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">Logistics receipt reconciled · ₱{{ number_format((float) $cash->received_amount, 2) }} confirmed at {{ $cash->reconciled_at?->format('M j, Y g:i A') }}</div>
                    @endif
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-gray-border bg-gray-bg/45 px-4 py-4 text-sm text-navy/50">Settlement not recorded for this legacy delivery.</div>
                @endif
            </article>
        @empty
            <div class="sh-empty">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-light text-teal-dark"><x-lucide-wallet class="h-5 w-5" /></div>
                <p class="mt-3 text-sm font-semibold text-navy">No delivered COD records yet</p>
                <p class="mt-1 text-xs text-navy/45">COD settlement entries will appear here once partner deliveries complete and cash records exist.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
