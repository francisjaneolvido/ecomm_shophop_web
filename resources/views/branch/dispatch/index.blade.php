@extends('branch.layout.app')

@section('title', 'Dispatch')

@section('content')
@php
    $pickups    = collect($pickups ?? []);
    $deliveries = collect($deliveries ?? []);
    $riders     = collect($riders ?? []);
@endphp

<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">{{ $center->name ?? 'Your branch' }}</p>
            <h1 class="title">Dispatch</h1>
            <p class="description">Mag-assign ng rider para sa pickup sa seller at para sa final delivery sa buyer. Auto-select ang rider na tugma sa barangay.</p>
        </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-3">
        <div class="sh-stat">
            <p class="sh-stat-label">Pickup requests</p>
            <p class="sh-stat-value">{{ number_format($pickups->count()) }}</p>
            <p class="sh-stat-note">Naghihintay ng pickup rider</p>
        </div>
        <div class="sh-stat">
            <p class="sh-stat-label">Ready for delivery</p>
            <p class="sh-stat-value">{{ number_format($deliveries->count()) }}</p>
            <p class="sh-stat-note">Wala pang delivery rider</p>
        </div>
        <div class="sh-stat">
            <p class="sh-stat-label">Active riders</p>
            <p class="sh-stat-value">{{ number_format($riders->count()) }}</p>
            <p class="sh-stat-note">Sa branch na ito</p>
        </div>
    </section>

    {{-- 1. PICKUP REQUESTS --}}
    <section id="pickups" class="sh-surface sh-card-pad">
        <h2 class="text-base font-bold text-navy">Pickup requests</h2>
        <p class="mt-1 text-xs text-navy/45">Rider na kukuha ng parcel sa seller papunta sa branch mo.</p>

        <div class="mt-4 grid gap-3">
            @forelse ($pickups as $p)
                <div class="rounded-2xl border border-gray-border bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-navy">{{ $p['seller'] }}</p>
                            <p class="mt-0.5 text-xs text-navy/50">
                                {{ $p['tracking'] ?? 'Walang tracking code' }}
                                @if ($p['requested']) &middot; {{ $p['requested'] }} @endif
                            </p>
                            <p class="mt-1 text-xs text-navy/60">{{ $p['address'] ?: '-' }}</p>
                            @unless ($p['ready'])
                                <span class="sh-badge sh-badge-warning mt-2">Hindi pa ready for pickup ang order</span>
                            @endunless
                        </div>

                        <form method="POST" action="{{ route('branch.dispatch.pickups.assign', $p['id']) }}"
                              class="flex flex-wrap items-end gap-2"
                              data-confirm-title="Assign pickup rider?"
                              data-confirm-message="Itatalaga ang rider na ito para kunin ang parcel sa seller."
                              data-confirm-button="Assign">
                            @csrf
                            <label class="sh-field">
                                <span class="sh-label">Rider</span>
                                <select name="rider_id" required class="sh-select" @disabled($riders->isEmpty() || ! $p['ready'])>
                                    <option value="">Pumili ng rider</option>
                                    @foreach ($riders as $r)
                                        <option value="{{ $r['id'] }}" @selected($p['suggested'] === $r['id'])>
                                            {{ $r['name'] }}{{ $p['suggested'] === $r['id'] ? ' (tugma sa barangay)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <button class="sh-btn" @disabled($riders->isEmpty() || ! $p['ready'])>Assign pickup</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="sh-empty">
                    <p class="text-sm font-semibold text-navy">Walang pickup request</p>
                    <p class="mt-1 text-xs text-navy/45">Lalabas dito kapag may seller na nag-request ng pickup sa branch mo.</p>
                </div>
            @endforelse
        </div>

        @if ($riders->isEmpty())
            <p class="mt-3 text-xs text-navy/50">Walang active rider sa branch na ito, kaya hindi pa makakapag-assign.</p>
        @endif
    </section>

    {{-- 2. READY FOR DELIVERY --}}
    <section id="deliveries" class="sh-surface sh-card-pad">
        <h2 class="text-base font-bold text-navy">Ready for delivery</h2>
        <p class="mt-1 text-xs text-navy/45">Naka-sort na sa branch mo. Rider lang na naka-assign sa delivery area ng parcel ang lalabas sa dropdown.</p>

        <div class="mt-4 grid gap-3">
            @forelse ($deliveries as $d)
                <div class="rounded-2xl border border-gray-border bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-navy">{{ $d['tracking'] }}</p>
                            <p class="mt-0.5 text-xs text-navy/50">
                                {{ $d['receiver'] ?? '-' }}
                                @if ($d['barangay']) &middot; {{ $d['barangay'] }} @endif
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @if ($d['cod'])
                                    <span class="sh-badge sh-badge-neutral">COD &#8369;{{ number_format($d['cod'], 2) }}</span>
                                @else
                                    <span class="sh-badge sh-badge-success">Prepaid</span>
                                @endif
                                @if ($d['failed'])
                                    <span class="sh-badge sh-badge-warning">Failed attempt ({{ $d['attempts'] }})</span>
                                @endif
                            </div>
                            @if ($d['failed'] && $d['reason'])
                                <p class="mt-2 max-w-md text-xs text-navy/50">{{ $d['reason'] }}</p>
                            @endif
                        </div>

                        @if (count($d['riders']))
                            <form method="POST" action="{{ route('branch.dispatch.deliveries.assign', $d['id']) }}"
                                  class="flex flex-wrap items-end gap-2"
                                  data-confirm-title="Assign delivery rider?"
                                  data-confirm-message="Itatalaga ang rider na ito para ihatid ang parcel sa buyer."
                                  data-confirm-button="Assign">
                                @csrf
                                <label class="sh-field">
                                    <span class="sh-label">Rider</span>
                                    <select name="rider_id" required class="sh-select">
                                        <option value="">Pumili ng rider</option>
                                        @foreach ($d['riders'] as $r)
                                            <option value="{{ $r['id'] }}" @selected($d['suggested'] === $r['id'])>
                                                {{ $r['name'] }}{{ $d['suggested'] === $r['id'] ? ' (tugma sa barangay)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <button class="sh-btn">{{ $d['failed'] ? 'Retry delivery' : 'Assign delivery' }}</button>
                            </form>
                        @else
                            <p class="max-w-xs text-xs text-navy/50">Walang active rider na naka-assign sa delivery area ng parcel na ito.</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="sh-empty">
                    <p class="text-sm font-semibold text-navy">Walang parcel na ready i-assign</p>
                    <p class="mt-1 text-xs text-navy/45">Lalabas dito ang mga parcel na na-receive at na-sort na sa branch mo.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection