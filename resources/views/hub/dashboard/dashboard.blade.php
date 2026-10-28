@extends('hub.layouts.console')
@section('title', 'Dashboard — Province Hub')
@section('content')
<div class="sh-ui-workspace space-y-5">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">Province Hub · {{ $center->province }}</p>
            <h1 class="title">{{ $center->name }} Dashboard</h1>
            <p class="description">Live hub operations, incoming and outgoing parcels, and station workload. All figures are provided by your operations database.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="sh-btn" href="{{ route('logistics.hub.parcels') }}">Open parcel operations →</a>
            <a class="sh-btn-soft" href="{{ route('logistics.hub.station-hubs') }}">Station hubs</a>
        </div>
    </header>

    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Current province hub metrics">
        @foreach ($stats as [$label, $value, $icon, $color])
            <article class="sh-stat sh-ui-kpi">
                <p class="sh-stat-label">{{ $label }}</p>
                <div class="flex items-end justify-between gap-2"><p class="sh-stat-value {{ $color }}">{{ number_format((int)$value) }}</p>@svg($icon, 'h-5 w-5 text-navy/25')</div>
            </article>
        @endforeach
    </section>

    <nav class="sh-ui-jump" aria-label="Province Hub shortcuts">
        <a href="{{ route('logistics.hub.parcels') }}">Process inbound & sorting</a>
        <a href="{{ route('logistics.hub.station-hubs') }}">Manage {{ $stationCount }} station hubs</a>
        <a href="#station-load">View station workload ↓</a>
    </nav>

    <div class="grid gap-4 xl:grid-cols-[1.2fr_1fr]">
        <section id="station-load" class="sh-surface overflow-hidden scroll-mt-6">
            <div class="sh-ui-section-heading"><div><p class="sh-kicker">Network overview</p><h2 class="sh-title-sm">Station workload</h2><p class="mt-1 text-xs text-navy/55">Bar length compares each station's active parcels with the busiest station; it is not physical storage capacity.</p></div><a href="{{ route('logistics.hub.station-hubs') }}" class="sh-btn-soft">Manage</a></div>
            <div class="divide-y divide-gray-border">
                @forelse($stations as $station)
                    <div class="sh-ui-station-row">
                        <div class="sh-toolbar"><div class="min-w-0"><strong class="block truncate text-sm text-navy">{{ $station['name'] }}</strong><span class="text-xs text-navy/50">{{ $station['city'] }} · {{ $station['riders'] }} active riders</span></div><span class="sh-badge {{ $station['status'] === 'active' ? 'sh-badge-success' : 'sh-badge-neutral' }}">{{ $station['active'] }} active parcels</span></div>
                        <div class="sh-ui-progress" role="meter" aria-label="Relative parcel load for {{ $station['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $station['percent'] }}"><span style="width: {{ max(0,min(100,(int)$station['percent'])) }}%"></span></div>
                    </div>
                @empty
                    <div class="sh-empty m-4"><strong>No station hubs assigned yet</strong><p class="mt-1 text-xs text-navy/55">Add a station hub to start monitoring its parcel workload.</p></div>
                @endforelse
            </div>
        </section>
        <section class="sh-surface overflow-hidden">
            <div class="sh-ui-section-heading"><div><p class="sh-kicker">Operations feed</p><h2 class="sh-title-sm">Recent scan & transfer activity</h2></div></div>
            <ul class="divide-y divide-gray-border">
                @forelse($activity as $event)
                    <li class="sh-ui-activity-row"><div class="min-w-0"><p class="text-sm font-semibold text-navy">{{ $event['title'] }}</p><p class="mt-0.5 text-xs text-navy/55">{{ $event['sub'] }}</p></div><time class="sh-ui-timestamp" datetime="{{ $event['at']?->toIso8601String() }}">{{ $event['at']?->diffForHumans() }}</time></li>
                @empty
                    <li class="sh-empty m-4"><strong>No scan or transfer activity yet</strong><p class="mt-1 text-xs text-navy/55">The timeline updates as staff scan and hand off parcels.</p></li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection
