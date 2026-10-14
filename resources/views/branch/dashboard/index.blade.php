@extends('branch.layout.app')

@section('title', 'Dashboard — Branch Console')
@section('heading', 'Dashboard')

@section('content')
@php
    // Data contract mula sa controller. May safe defaults para hindi mag-error kung kulang.
    $stats = array_merge([
        'incoming' => 0, 'at_branch' => 0, 'to_assign' => 0, 'out_for_delivery' => 0,
        'delivered_today' => 0, 'failed' => 0, 'cod_with_riders' => 0,
    ], $stats ?? []);
    $incoming = collect($incoming ?? []);
    $toAssign = collect($toAssign ?? []);
    $riders = collect($riders ?? []);
    $alerts = collect($alerts ?? []);

    $link = fn (string $name, $param = null) => \Illuminate\Support\Facades\Route::has($name)
        ? ($param ? route($name, $param) : route($name))
        : '#';

    $cards = [
        ['Incoming', $stats['incoming'], 'Batches papunta sa branch'],
        ['At branch', $stats['at_branch'], 'Naka-receive na, naka-sort'],
        ['To assign', $stats['to_assign'], 'Wala pang rider'],
        ['Out for delivery', $stats['out_for_delivery'], 'Hawak ng riders'],
        ['Delivered today', $stats['delivered_today'], 'Tapos na at may proof'],
        ['Failed / return', $stats['failed'], 'Kailangan ng aksyon'],
    ];
@endphp

<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">{{ $center->name ?? 'Your branch' }}</p>
            <h1 class="title">Branch Dashboard</h1>
            <p class="description">I-receive ang dumarating na parcels, i-assign sa riders, at subaybayan ang deliveries ng branch mo.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ $link('branch.dispatch') }}" class="sh-btn">Assign riders</a>
        </div>
    </header>

    {{-- Stat cards --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($cards as [$label, $value, $note])
            <div class="sh-stat">
                <p class="sh-stat-label">{{ $label }}</p>
                <p class="sh-stat-value">{{ number_format($value) }}</p>
                <p class="sh-stat-note">{{ $note }}</p>
            </div>
        @endforeach
    </section>

    {{-- Alerts --}}
    @if ($alerts->isNotEmpty())
        <section class="grid gap-3">
            @foreach ($alerts as $alert)
                @php $level = $alert['level'] ?? 'info'; @endphp
                <div class="flex items-start gap-3 rounded-2xl border border-gray-border bg-white px-4 py-3">
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $level === 'danger' ? 'bg-coral' : ($level === 'warning' ? 'bg-yellow' : 'bg-teal') }}"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-navy">{{ $alert['title'] ?? '' }}</p>
                        @if (! empty($alert['detail']))
                            <p class="mt-0.5 text-xs text-navy/50">{{ $alert['detail'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    <div class="grid gap-5 xl:grid-cols-3">
        {{-- Incoming transfers --}}
        <section class="sh-surface sh-card-pad xl:col-span-2">
            <div class="sh-toolbar">
                <div>
                    <h2 class="text-base font-bold text-navy">Incoming transfers</h2>
                    <p class="mt-1 text-xs text-navy/45">Bilangin ang parcels pagdating, at ilagay ang bilang bago i-receive.</p>
                </div>
                <a href="{{ $link('branch.transfers') }}" class="sh-btn-ghost">View all</a>
            </div>

            <div class="mt-4 grid gap-3">
                @forelse ($incoming as $batch)
                    @php $b = (object) $batch; @endphp
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-border bg-white px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-navy">{{ $b->code ?? '—' }}</p>
                            <p class="mt-0.5 text-xs text-navy/50">
                                {{ number_format($b->parcels ?? 0) }} parcels · galing {{ $b->from ?? 'Main hub' }} · {{ $b->sent_at ?? '' }}
                            </p>
                        </div>
                        <form method="POST" action="{{ $link('branch.transfers.receive', $b->id ?? null) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="number" name="received_count" min="0" max="{{ $b->parcels ?? 0 }}" required placeholder="Counted" class="sh-input w-28">
                            <button class="sh-btn">Receive</button>
                        </form>
                    </div>
                @empty
                    <div class="sh-empty">
                        <p class="text-sm font-semibold text-navy">Walang dumarating na transfer</p>
                        <p class="mt-1 text-xs text-navy/45">Kapag may ipinadala ang main hub, lalabas dito.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- COD with riders --}}
        <section class="sh-surface sh-card-pad">
            <h2 class="text-base font-bold text-navy">COD with riders</h2>
            <p class="mt-1 text-xs text-navy/45">Nakolekta pero hindi pa nai-remit sa branch.</p>
            <p class="mt-5 text-3xl font-bold text-navy">₱{{ number_format($stats['cod_with_riders'], 2) }}</p>
            <a href="{{ $link('branch.cod') }}" class="sh-btn-soft mt-5 inline-flex">Open remittance</a>
        </section>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        {{-- To assign --}}
        <section class="sh-surface sh-card-pad">
            <div class="sh-toolbar">
                <div>
                    <h2 class="text-base font-bold text-navy">Ready to assign</h2>
                    <p class="mt-1 text-xs text-navy/45">Nasa branch na, wala pang rider.</p>
                </div>
                <a href="{{ $link('branch.dispatch') }}" class="sh-btn-ghost">Dispatch</a>
            </div>

            <div class="mt-4 divide-y divide-gray-border">
                @forelse ($toAssign->take(6) as $parcel)
                    @php $p = (object) $parcel; @endphp
                    <div class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-navy">{{ $p->tracking ?? '—' }}</p>
                            <p class="truncate text-xs text-navy/50">{{ $p->receiver ?? '' }}{{ ! empty($p->barangay) ? ' · '.$p->barangay : '' }}</p>
                        </div>
                        @if (! empty($p->cod))
                            <span class="sh-badge sh-badge-neutral shrink-0">COD ₱{{ number_format($p->cod, 2) }}</span>
                        @endif
                    </div>
                @empty
                    <div class="sh-empty">
                        <p class="text-xs text-navy/45">Wala nang naghihintay na i-assign.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Riders --}}
        <section class="sh-surface sh-card-pad">
            <div class="sh-toolbar">
                <div>
                    <h2 class="text-base font-bold text-navy">Riders</h2>
                    <p class="mt-1 text-xs text-navy/45">Sino ang available at gaano karami ang hawak.</p>
                </div>
                <a href="{{ $link('branch.riders') }}" class="sh-btn-ghost">View all</a>
            </div>

            <div class="mt-4 divide-y divide-gray-border">
                @forelse ($riders->take(6) as $rider)
                    @php $r = (object) $rider; @endphp
                    <div class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-navy">{{ $r->name ?? '—' }}</p>
                            <p class="text-xs text-navy/50">{{ $r->active ?? 0 }} hawak · {{ $r->delivered ?? 0 }} natapos ngayon</p>
                        </div>
                        <span class="sh-badge {{ ($r->status ?? '') === 'available' ? 'sh-badge-success' : 'sh-badge-neutral' }}">{{ ucfirst($r->status ?? 'offline') }}</span>
                    </div>
                @empty
                    <div class="sh-empty">
                        <p class="text-xs text-navy/45">Wala pang rider na naka-assign sa branch mo.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection