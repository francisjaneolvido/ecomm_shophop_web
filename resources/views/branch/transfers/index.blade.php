@extends('branch.layout.app')

@section('title', 'Incoming Transfers')

@section('content')
@php
    $counts = array_merge([
        'awaiting' => 0,
        'preparing' => 0,
        'received_today' => 0,
        'all' => 0,
    ], $counts ?? []);

    $activeStatus = $status ?? request('status', 'awaiting');

    $tabs = [
        'awaiting' => 'Awaiting receipt',
        'preparing' => 'In linehaul',
        'received' => 'Received',
        'all' => 'All',
    ];
@endphp

<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">{{ $center->name ?? 'Your branch' }}</p>
            <h1 class="title">Incoming Transfers</h1>
            <p class="description">Actual parcels na ipinadala papunta sa branch/hub na ito. I-scan ang QR/tracking code ng physical parcel bago ito i-receive.</p>
        </div>
    </header>

    @if (session('status'))
        <div role="status" class="sh-alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="sh-stat">
            <p class="sh-stat-label">Awaiting receipt</p>
            <p class="sh-stat-value">{{ number_format($counts['awaiting']) }}</p>
            <p class="sh-stat-note">Pwede nang i-scan pagdating</p>
        </div>

        <div class="sh-stat">
            <p class="sh-stat-label">In linehaul</p>
            <p class="sh-stat-value">{{ number_format($counts['preparing']) }}</p>
            <p class="sh-stat-note">Scheduled o bumibiyahe pa ang truck/van</p>
        </div>

        <div class="sh-stat">
            <p class="sh-stat-label">Received today</p>
            <p class="sh-stat-value">{{ number_format($counts['received_today']) }}</p>
            <p class="sh-stat-note">Na-scan at na-confirm ngayong araw</p>
        </div>

        <div class="sh-stat">
            <p class="sh-stat-label">All incoming records</p>
            <p class="sh-stat-value">{{ number_format($counts['all']) }}</p>
            <p class="sh-stat-note">Lahat ng ParcelTransfer papunta rito</p>
        </div>
    </section>

    <section class="sh-surface sh-card-pad">
        <div class="sh-toolbar">
            <div>
                <h2 class="text-base font-bold text-navy">Receive by QR / tracking code</h2>
                <p class="mt-1 text-xs text-navy/45">USB scanner: i-focus ang field at i-scan ang parcel label. Pareho itong validation rules ng mobile Logistics scanner.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('branch.receive') }}" class="mt-4 flex flex-wrap items-center gap-2">
            @csrf
            <input
                name="tracking_code"
                value="{{ old('tracking_code') }}"
                required
                autofocus
                autocomplete="off"
                placeholder="Scan tracking code (SHP-YYYYMMDD-000000)"
                class="sh-input min-w-0 flex-1"
            >
            <button class="sh-btn">Receive parcel</button>
        </form>
    </section>

    <nav class="flex flex-wrap gap-2" aria-label="Incoming transfer status filter">
        @foreach ($tabs as $key => $label)
            <a
                href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => null]) }}"
                class="{{ $activeStatus === $key ? 'sh-btn' : 'sh-btn-ghost' }}"
                @if ($activeStatus === $key) aria-current="page" @endif
            >{{ $label }}</a>
        @endforeach
    </nav>

    <div class="grid gap-4">
        @forelse ($transfers as $transfer)
            @php
                $delivery = $transfer->delivery;
                $order = $delivery?->order;

                $linehaulStatus = $transfer->linehaulTrip?->status;
                $canReceive = $transfer->status === \App\Models\Logistics\ParcelTransfer::IN_TRANSIT
                    && ($transfer->linehaul_trip_id
                        ? in_array($linehaulStatus, ['arrived', 'completed'], true)
                        : (empty($transfer->transit_rider_id) || ! empty($transfer->departed_at)));

                $isPreparing = $transfer->status === \App\Models\Logistics\ParcelTransfer::IN_TRANSIT
                    && ($transfer->linehaul_trip_id
                        ? in_array($linehaulStatus, ['scheduled', 'in_transit'], true)
                        : (! empty($transfer->transit_rider_id) && empty($transfer->departed_at)));

                if ($transfer->status === \App\Models\Logistics\ParcelTransfer::RECEIVED) {
                    $badgeClass = 'sh-badge-success';
                    $badgeLabel = 'Received';
                } elseif ($transfer->status === \App\Models\Logistics\ParcelTransfer::CANCELLED) {
                    $badgeClass = 'sh-badge-neutral';
                    $badgeLabel = 'Cancelled';
                } elseif ($isPreparing) {
                    $badgeClass = 'sh-badge-warning';
                    $badgeLabel = $transfer->linehaul_trip_id ? 'Linehaul '.ucwords(str_replace('_', ' ', (string) $linehaulStatus)) : 'Not departed';
                } else {
                    $badgeClass = 'sh-badge-info';
                    $badgeLabel = 'Awaiting receipt';
                }

                $tracking = $delivery?->tracking_code ?? '—';
                $manifestCode = $transfer->manifest?->code;
                $from = $transfer->fromCenter?->name ?? 'Sending hub';
                $to = $transfer->toCenter?->name ?? ($center->name ?? 'This branch');
                $recipient = $order?->delivery_name;
                $barangay = $order?->delivery_barangay_name ?: $order?->delivery_address;
                $isCod = $order?->payment_method === 'cod';
            @endphp

            <article class="sh-surface sh-card-pad">
                <div class="sh-toolbar">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-bold text-navy">{{ $tracking }}</h2>
                            <span class="sh-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            @if ($manifestCode)
                                <span class="sh-badge sh-badge-neutral">{{ $manifestCode }}</span>
                            @endif
                        </div>

                        <p class="mt-1 text-xs text-navy/50">
                            {{ $from }} → {{ $to }}
                            @if ($transfer->dispatched_at)
                                · Dispatched {{ $transfer->dispatched_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>

                    <div class="text-right text-xs text-navy/50">
                        <p class="font-semibold text-navy">Transfer #{{ $transfer->id }}</p>
                        @if ($transfer->received_at)
                            <p class="mt-0.5">Received {{ $transfer->received_at->format('M d, Y h:i A') }}</p>
                        @elseif ($transfer->departed_at)
                            <p class="mt-0.5">Departed {{ $transfer->departed_at->format('M d, Y h:i A') }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid gap-3 rounded-2xl border border-gray-border bg-gray-bg/45 px-4 py-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="sh-label">Recipient</p>
                        <p class="mt-1 text-sm font-semibold text-navy">{{ $recipient ?: '—' }}</p>
                        <p class="mt-0.5 text-xs text-navy/50">{{ $barangay ?: 'No destination detail' }}</p>
                    </div>

                    <div>
                        <p class="sh-label">Linehaul transport</p>
                        @if ($transfer->linehaulTrip)
                            <p class="mt-1 text-sm font-semibold text-navy">{{ $transfer->linehaulTrip->vehicle?->code ?? 'Vehicle' }} · {{ $transfer->linehaulTrip->vehicle?->plate_number ?? '—' }}</p>
                            <p class="mt-0.5 text-xs text-navy/50">{{ $transfer->linehaulTrip->driver?->name ?? 'Driver' }} · {{ strtoupper((string) $transfer->linehaulTrip->status) }}</p>
                        @else
                            <p class="mt-1 text-sm font-semibold text-navy">Legacy transfer</p>
                            <p class="mt-0.5 text-xs text-navy/50">{{ $transfer->transitRider?->name ?? 'Company transport' }}</p>
                        @endif
                    </div>

                    <div>
                        <p class="sh-label">Manifest</p>
                        <p class="mt-1 text-sm font-semibold text-navy">{{ $manifestCode ?: 'Individual transfer' }}</p>
                        <p class="mt-0.5 text-xs text-navy/50">{{ $transfer->manifest?->parcel_count ? number_format($transfer->manifest->parcel_count).' parcel(s) in batch' : 'No manifest batch linked' }}</p>
                    </div>

                    <div>
                        <p class="sh-label">Payment</p>
                        @if ($isCod)
                            <p class="mt-1 text-sm font-semibold text-navy">COD ₱{{ number_format((float) $order->total_amount, 2) }}</p>
                        @else
                            <p class="mt-1 text-sm font-semibold text-navy">{{ strtoupper((string) ($order?->payment_method ?: 'Non-COD')) }}</p>
                        @endif
                    </div>
                </div>

                @if ($canReceive)
                    <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl border border-gray-border bg-white px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-navy">Physical receipt required</p>
                            <p class="mt-0.5 text-xs text-navy/45">I-scan ang QR ng parcel sa receive box sa taas. Hindi sapat na nasa listahan lang ito.</p>
                        </div>
                        <span class="sh-badge sh-badge-info shrink-0">Ready to scan</span>
                    </div>
                @elseif ($isPreparing)
                    <div class="mt-4 rounded-2xl border border-gray-border bg-white px-4 py-3">
                        <p class="text-sm font-semibold text-navy">Hindi pa puwedeng i-receive</p>
                        <p class="mt-0.5 text-xs text-navy/45">Hindi pa puwedeng i-receive habang scheduled/in transit ang Linehaul Trip. Pag nag-scan ang Driver ng arrival sa destination, lilipat ito sa Awaiting receipt.</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="sh-empty">
                <p class="text-sm font-semibold text-navy">Walang transfer sa filter na ito</p>
                <p class="mt-1 text-xs text-navy/45">Kapag may real ParcelTransfer na papunta sa branch/hub na ito, lalabas dito.</p>
            </div>
        @endforelse
    </div>

    @if ($transfers->hasPages())
        <div>{{ $transfers->links() }}</div>
    @endif
</div>
@endsection
