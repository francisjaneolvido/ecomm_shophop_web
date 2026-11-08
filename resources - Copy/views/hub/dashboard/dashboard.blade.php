@extends('hub.layouts.console')
@section('title','Province Hub Dashboard')
@section('content')
<div class="space-y-5">
  <header class="sh-page-header"><div><p class="eyebrow">Province Hub · {{ $center->province }}</p><h1 class="title">{{ $center->name }} Dashboard</h1><p class="description">Live hub receiving, sorting, transport and branch workload. Manage city/municipal Branches under this province.</p></div>
    <div class="flex flex-wrap gap-2"><a class="sh-btn-soft" href="{{ route('logistics.hub.station-hubs') }}">Branches ({{ $stationCount }})</a><a class="sh-btn" href="{{ route('logistics.hub.parcels') }}">Open Operations</a></div></header>
  <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">@foreach($stats as [$label,$value,$icon,$color])
    <div class="sh-stat"><p class="sh-stat-label">{{ $label }}</p><div class="flex justify-between items-end"><p class="sh-stat-value {{ $color }}">{{ number_format((int)$value) }}</p>@svg($icon,'h-5 w-5 text-navy/25')</div></div>
  @endforeach</section>
  <nav class="ops-panel p-3 flex flex-wrap gap-2" aria-label="Hub workflow shortcuts">
    <a class="sh-btn-soft" href="{{ route('logistics.hub.parcels',['stage'=>'receive']) }}">1. Inbound</a>
    <a class="sh-btn-soft" href="{{ route('logistics.hub.parcels',['stage'=>'sort']) }}">2. Sorting</a>
    <a class="sh-btn-soft" href="{{ route('logistics.hub.parcels',['stage'=>'manifest']) }}">3. Manifests</a>
    <a class="sh-btn-soft" href="{{ route('logistics.hub.parcels',['stage'=>'transport']) }}">4. Transport</a>
  </nav>
  <div class="grid gap-4 xl:grid-cols-[1.2fr_1fr]">
    <section class="ops-panel"><div class="ops-panel-head"><div><h2>Branch workload</h2><p class="ops-panel-note">Active parcels at destination; not a physical warehouse-capacity measurement.</p></div><a class="sh-btn-ghost" href="{{ route('logistics.hub.station-hubs') }}">Manage branches</a></div>
      <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Branch</th><th>Active Parcels</th><th>Riders</th><th>Status</th></tr></thead><tbody>
        @forelse($stations->take(12) as $station)<tr><td><strong>{{ $station['name'] }}</strong><div class="ops-row-sub">{{ $station['city'] }}</div></td><td>{{ number_format($station['active']) }}</td><td>{{ number_format($station['riders']) }}</td><td><span class="sh-badge {{ $station['status']==='active'?'sh-badge-success':'sh-badge-neutral' }}">{{ ucfirst($station['status']) }}</span></td></tr>
        @empty<tr><td colspan="4" class="ops-empty">No Branch records yet.</td></tr>@endforelse
      </tbody></table></div></section>
    <section class="ops-panel"><div class="ops-panel-head"><div><h2>Recent handoffs & scans</h2><p class="ops-panel-note">Latest activity across this Hub and its Branches.</p></div></div>
      <div class="divide-y divide-gray-border">@forelse($activity as $event)
        <div class="px-4 py-3 flex justify-between gap-4"><div class="min-w-0"><div class="text-xs font-semibold text-navy">{{ $event['title'] }}</div><div class="ops-row-sub">{{ $event['sub'] }}</div></div><time class="text-xs text-navy/50 shrink-0" datetime="{{ $event['at']?->toIso8601String() }}">{{ $event['at']?->diffForHumans() }}</time></div>
        @empty<div class="ops-empty">No recent activity.</div>@endforelse</div></section>
  </div>
</div>
@endsection
