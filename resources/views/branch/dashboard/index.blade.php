@extends('branch.layout.app')

@section('title', 'Dashboard — Branch Console')
@section('heading', 'Dashboard')

@section('content')
@php
    $stats = array_merge([
        'pickups' => 0, 'to_receive' => 0, 'at_branch' => 0, 'to_assign' => 0,
        'out_for_delivery' => 0, 'delivered_today' => 0, 'failed' => 0, 'cod_with_riders' => 0,
    ], $stats ?? []);
    $awaiting = collect($awaiting ?? []);
    $toAssign = collect($toAssign ?? []);
    $riders   = collect($riders ?? []);
    $alerts   = collect($alerts ?? []);

    $has  = fn (string $name) => \Illuminate\Support\Facades\Route::has($name);
    $link = fn (string $name) => $has($name) ? route($name) : null;

    $cards = [
        ['Pickup requests', $stats['pickups'], 'Naghihintay ng pickup rider'],
        ['Awaiting receipt', $stats['to_receive'], 'Papunta sa branch, i-scan pagdating'],
        ['At branch', $stats['at_branch'], 'Naka-receive na, naka-sort'],
        ['To assign', $stats['to_assign'], 'Wala pang delivery rider'],
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
        @if ($link('branch.parcels.index'))
            <div class="flex flex-wrap gap-3">
                <a href="{{ $link('branch.parcels.index') }}" class="sh-btn">Assign riders</a>
            </div>
        @endif
    </header>

    @if (session('status'))
        <div role="status" class="sh-alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>
    @endif

    {{-- Stat cards --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
        {{-- Awaiting receipt --}}
        <section class="sh-surface sh-card-pad xl:col-span-2">
            <div class="sh-toolbar">
                <div>
                    <h2 class="text-base font-bold text-navy">Awaiting receipt</h2>
                    <p class="mt-1 text-xs text-navy/45">Parcels na dala ng pickup rider o ng transfer. I-scan o i-type ang tracking code, tapos i-receive.</p>
                </div>
            </div>

            {{-- Scan box: gumagana sa USB scanner (nagta-type + Enter) --}}
            @if ($has('branch.receive'))
                <form method="POST" action="{{ route('branch.receive') }}" class="mt-4 flex flex-wrap items-center gap-2">
                    @csrf
                    <input name="tracking_code" required autofocus autocomplete="off"
                           placeholder="Scan tracking code (SHP-YYYYMMDD-000000)" class="sh-input min-w-0 flex-1">
                    <button class="sh-btn">Receive</button>
                </form>
            @endif

            <div class="mt-4 grid gap-3">
                @forelse ($awaiting as $item)
                    @php $i = (object) $item; @endphp
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-border bg-white px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-navy">{{ $i->tracking }}</p>
                            <p class="mt-0.5 text-xs text-navy/50">{{ $i->label }} · {{ $i->from }} · {{ $i->when }}</p>
                        </div>
                        @if ($has('branch.receive'))
                            <form method="POST" action="{{ route('branch.receive') }}">
                                @csrf
                                <input type="hidden" name="tracking_code" value="{{ $i->tracking }}">
                                <button class="sh-btn-soft"
                                        onclick="return confirm('Na-check mo ba ang physical na parcel at tugma ang tracking code?')">Receive</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="sh-empty">
                        <p class="text-sm font-semibold text-navy">Walang hinihintay na parcel</p>
                        <p class="mt-1 text-xs text-navy/45">Lalabas dito kapag na-pickup na ng rider o may transfer na papunta.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- COD with riders --}}
        <section class="sh-surface sh-card-pad">
            <h2 class="text-base font-bold text-navy">COD with riders</h2>
            <p class="mt-1 text-xs text-navy/45">Nakolekta pero hindi pa nai-remit ng riders.</p>
            <p class="mt-5 text-3xl font-bold text-navy">₱{{ number_format($stats['cod_with_riders'], 2) }}</p>
            @if ($link('branch.cod'))
                <a href="{{ $link('branch.cod') }}" class="sh-btn-soft mt-5 inline-flex">Open remittance</a>
            @endif
        </section>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        {{-- To assign --}}
        <section class="sh-surface sh-card-pad">
            <div class="sh-toolbar">
                <div>
                    <h2 class="text-base font-bold text-navy">Ready to assign</h2>
                    <p class="mt-1 text-xs text-navy/45">Nasa branch na, wala pang delivery rider.</p>
                </div>
                @if ($link('branch.parcels.index'))
                    <a href="{{ $link('branch.parcels.index') }}" class="sh-btn-ghost">Open parcels</a>
                @endif
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
                @if ($link('branch.riders'))
                    <a href="{{ $link('branch.riders') }}" class="sh-btn-ghost">View all</a>
                @endif
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