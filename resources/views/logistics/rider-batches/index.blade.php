@extends('logistics.layouts.console')
@section('title','Rider Route Monitoring')
@section('content')
<div class="ops-module space-y-5">
    <header class="ops-hero"><div><span class="eyebrow">Company monitoring</span><h1>Rider route monitoring</h1><p class="description">Routes and parcel outcomes grouped by Branch. Daily planning and dispatch are managed by the assigned Branch.</p></div></header>
    <form method="GET" class="ops-panel p-4 flex flex-wrap items-end gap-3">
        <label class="sh-field"><span class="sh-label">Branch</span><select name="branch" class="sh-input"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch')==$branch->id)>{{ $branch->name }}</option>@endforeach</select></label>
        <label class="sh-field"><span class="sh-label">Status</span><select name="status" class="sh-input"><option value="">All statuses</option>@foreach(['queued','in_progress','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select></label>
        <button class="sh-btn-primary">Apply filters</button>
    </form>
    <div class="grid gap-3 md:grid-cols-4">@foreach(['queued','in_progress','completed','cancelled'] as $status)<div class="sh-stat"><p class="sh-stat-label">{{ ucwords(str_replace('_',' ',$status)) }}</p><p class="sh-stat-value" data-batch-total="{{ $status }}">{{ $totals->where('status',$status)->sum('total') }}</p></div>@endforeach</div>
    <p data-batch-sync-status role="status" class="text-xs text-slate-500">Updates every 15 seconds while this page is visible.</p>
    <div id="rider-batch-routes" class="space-y-3" data-live-url="{{ request()->fullUrl() }}">
        @include('logistics.partials.rider-batch-routes',['canManage'=>false])
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/shophop-rider-batches.js') }}?v=20261112-navy-pink-2" defer></script>
@endpush
