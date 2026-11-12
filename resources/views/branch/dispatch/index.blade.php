@extends('branch.layout.app')
@section('title','Rider Dispatch')
@section('content')
<style>#dispatchScheduleFields[hidden],#dispatchCapacityNotice[hidden]{display:none!important}#dispatchAssignModal,#dispatchCapacityModal{width:min(680px,calc(100vw - 24px));padding:0;overflow-y:auto}#dispatchAssignModal .ops-dialog-foot,#dispatchCapacityModal .ops-dialog-foot{display:flex;justify-content:flex-end;gap:12px;padding:16px 18px;border-top:1px solid rgba(16,25,60,0.150)}</style>
<div class="space-y-5">
    <header class="sh-page-header">
        <div><p class="eyebrow">{{ $center->name }}</p><h1 class="title">Rider Dispatch</h1><p class="description">Choose parcels, select an eligible rider, and assign the trip from this page.</p></div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="sh-btn-soft" data-capacity-open @disabled(! $dispatchEnabled)>Rider vehicle settings (optional)</button>
            @if($routePrefix === 'branch.dispatch')<a href="{{ route('branch.scanner') }}" class="sh-btn-soft">Open QR Scanner</a>@endif
        </div>
    </header>
    @unless($dispatchEnabled)<div class="sh-alert-error" role="alert">Rider assignment is temporarily unavailable. Please contact your logistics administrator.</div>@endunless
    <section class="grid grid-cols-2 gap-3 xl:grid-cols-3">
        @foreach([['Pending pickup',$pickups->total(),'Ready to assign'],['Ready for delivery',$deliveries->total(),'At this branch'],['Active riders',$riderDetails->count(),'Assigned to this branch']] as [$label,$count,$note])
            <div class="sh-stat"><p class="sh-stat-label">{{ $label }}</p><p class="sh-stat-value">{{ number_format($count) }}</p><p class="sh-stat-note">{{ $note }}</p></div>
        @endforeach
    </section>
    <div class="ops-panel" data-ops-tabs>
        <nav class="ops-tabs" role="tablist" aria-label="Dispatch queues">
            <button class="ops-tab" type="button" role="tab" aria-selected="{{ request('type') === 'delivery' ? 'false' : 'true' }}" data-ops-tab="pickups">Seller Pickups ({{ $pickups->total() }})</button>
            <button class="ops-tab" type="button" role="tab" aria-selected="{{ request('type') === 'delivery' ? 'true' : 'false' }}" data-ops-tab="deliveries">Buyer Deliveries ({{ $deliveries->total() }})</button>
        </nav>
        @foreach(['pickup' => $pickups, 'delivery' => $deliveries] as $kind => $queue)
        <section id="{{ $kind === 'pickup' ? 'pickups' : 'deliveries' }}" data-ops-panel="{{ $kind === 'pickup' ? 'pickups' : 'deliveries' }}" role="tabpanel" @if((request('type') === 'delivery') !== ($kind === 'delivery')) hidden @endif>
            <div class="ops-panel-head">
                <div><h2>{{ $kind === 'pickup' ? 'Pickup requests' : 'Ready for last-mile delivery' }}</h2><p class="ops-panel-note">Assign one parcel or select several for the same rider.</p></div>
                <div class="flex flex-wrap gap-2"><input class="sh-input" data-ops-search="{{ $kind }}Rows" type="search" placeholder="Search this page" aria-label="Search {{ $kind }} parcels" style="max-width:220px"><button type="button" class="sh-btn-soft" data-assign-selected="{{ $kind }}" disabled>Assign selected (0)</button></div>
            </div>
            <div class="ops-table-scroll"><table id="{{ $kind }}Rows" class="ops-table"><thead><tr><th>Select</th><th>Parcel</th><th>{{ $kind === 'pickup' ? 'Pickup location' : 'Delivery location' }}</th><th>Status</th><th>Eligible riders</th><th>Action</th></tr></thead><tbody>
            @forelse($queue as $parcel)
                <tr data-ops-row data-dispatch-row data-kind="{{ $kind }}" data-parcel="{{ json_encode($parcel) }}">
                    <td><input type="checkbox" data-select-parcel aria-label="Select {{ $parcel['tracking'] }}" @disabled(! $dispatchEnabled || ! $parcel['ready'] || empty($parcel['riders']))></td>
                    <td><strong>{{ $parcel['name'] }}</strong><div class="ops-row-sub">{{ $parcel['tracking'] ?? 'Tracking pending' }}</div></td>
                    <td>{{ $parcel['address'] ?: 'Address unavailable' }}</td><td><span class="sh-badge {{ $parcel['ready'] ? 'sh-badge-success' : 'sh-badge-warning' }}">{{ $parcel['ready'] ? 'Ready' : 'Not ready' }}</span><div class="ops-row-sub">{{ $parcel['note'] }}</div></td>
                    <td>{{ count($parcel['riders']) }} matched</td>
                    <td class="ops-row-actions"><button type="button" class="ops-action" data-dispatch-open @disabled(! $dispatchEnabled || ! $parcel['ready'] || empty($parcel['riders']))>Assign rider</button></td>
                </tr>
            @empty<tr><td class="ops-empty" colspan="6">No {{ $kind }} parcels currently waiting.</td></tr>@endforelse
                <tr data-ops-search-empty hidden><td colspan="6" class="ops-empty">No matching parcels on this page.</td></tr>
            </tbody></table></div>
            <div class="ops-count">{{ $queue->firstItem() ?? 0 }}&ndash;{{ $queue->lastItem() ?? 0 }} of {{ $queue->total() }} parcels</div>
            @if($queue->hasPages())<div class="border-t p-4">{{ $queue->appends(['stage' => $kind === 'pickup' ? 'pickups' : 'deliveries'])->links() }}</div>@endif
        </section>
        @endforeach
    </div>
    <section class="space-y-3" aria-labelledby="rider-routes-title">
        <div class="flex flex-wrap justify-between gap-2"><div><h2 id="rider-routes-title" class="font-semibold text-navy">Rider routes</h2><p class="text-xs text-slate-500">Manage active assignments, scheduled trips, and recent routes here.</p></div><p class="text-xs text-slate-500" data-batch-sync-status role="status" aria-live="polite">Routes refresh automatically.</p></div>
        <div id="rider-batch-routes" class="space-y-3" data-live-url="{{ request()->fullUrl() }}">@include('branch.dispatch.routes')</div>
    </section>
</div>
<dialog id="dispatchAssignModal" class="ops-dialog" aria-labelledby="dispatchAssignTitle">
    <form id="dispatchAssignForm" action="{{ route($routePrefix.'.assign') }}" method="POST">
        @csrf
        <div class="ops-dialog-head"><div><h2 class="ops-dialog-title" id="dispatchAssignTitle">Assign rider</h2><p class="ops-panel-note" id="dispatchSelectionLabel"></p></div><button type="button" class="ops-action" data-dialog-close aria-label="Close assignment">&times;</button></div>
        <div class="ops-dialog-body space-y-4">
            <label class="sh-field"><span class="sh-label">Eligible rider</span><select class="sh-select" name="rider_id" id="dispatchRiderSelect" required></select></label>
            <div id="dispatchCapacityNotice" class="ops-inline-alert" hidden>Vehicle limits are optional. You can assign now without setting them. <button type="button" class="font-semibold underline" id="dispatchSetupCapacity">Set vehicle capacity</button></div>
            <div id="dispatchParcelMeasurements" class="space-y-3"></div>
            <label class="sh-field"><span class="sh-label">Estimated trip time (minutes)</span><input class="sh-input" name="estimated_minutes" type="number" min="1" max="1440" step="1" value="30" required><span class="text-xs text-slate-500">Include the return to this branch for a pickup trip.</span></label>
            <details><summary class="cursor-pointer text-sm font-medium text-teal-dark">Schedule for later (optional)</summary><div class="space-y-3 mt-3"><label class="flex items-center gap-2 text-sm"><input type="checkbox" id="dispatchScheduleLater"> Schedule this trip instead of dispatching now</label><div id="dispatchScheduleFields" class="grid gap-3 sm:grid-cols-2" hidden><label class="sh-field"><span class="sh-label">Route start (your local time)</span><input class="sh-input" name="scheduled_start" type="datetime-local"></label><label class="sh-field"><span class="sh-label">Route end (your local time)</span><input class="sh-input" name="scheduled_end" type="datetime-local"></label></div></div></details>
            <p class="text-xs text-slate-500">The rider must scan each parcel QR to confirm physical pickup or handoff.</p>
            <p id="dispatchFeedback" class="text-sm text-red-600" role="status" aria-live="polite"></p>
        </div>
        <div class="ops-dialog-foot"><button type="button" class="sh-btn-ghost" data-dialog-close>Cancel</button><button type="submit" class="sh-btn" id="dispatchSubmit">Assign and dispatch</button></div>
    </form>
</dialog>
<dialog id="dispatchCapacityModal" class="ops-dialog" aria-labelledby="dispatchCapacityTitle">
    <form id="dispatchCapacityForm" data-url="{{ route($routePrefix.'.capacity', ['rider' => 'RIDER_ID']) }}" method="POST">
        @csrf
        <div class="ops-dialog-head"><div><h2 class="ops-dialog-title" id="dispatchCapacityTitle">Rider vehicle settings</h2><p class="ops-panel-note">Optional. Save limits only if you want capacity checks for this vehicle.</p></div><button type="button" class="ops-action" data-dialog-close aria-label="Close vehicle settings">&times;</button></div>
        <div class="ops-dialog-body space-y-4"><label class="sh-field"><span class="sh-label">Rider</span><select id="dispatchCapacityRider" class="sh-select" required><option value="">Choose rider</option>@foreach($riderDetails as $rider)<option value="{{ $rider['id'] }}">{{ $rider['name'] }}</option>@endforeach</select></label>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach(['max_weight_kg' => 'Max weight (kg)', 'max_volume_m3' => 'Max volume (m3)', 'max_stops' => 'Max stops', 'max_duration_minutes' => 'Max trip duration (minutes)', 'max_parcels' => 'Max parcels (optional)'] as $key => $label)
                <label class="sh-field"><span class="sh-label">{{ $label }}</span><input class="sh-input" name="{{ $key }}" type="number" min="{{ $key === 'max_volume_m3' ? '0.000001' : ($key === 'max_weight_kg' ? '0.001' : '1') }}" step="{{ in_array($key, ['max_weight_kg','max_volume_m3']) ? 'any' : '1' }}" @required($key !== 'max_parcels')></label>
                @endforeach
            </div><p id="dispatchCapacityFeedback" class="text-sm text-red-600" role="status" aria-live="polite"></p>
        </div>
        <div class="ops-dialog-foot"><button type="button" class="sh-btn-ghost" data-dialog-close>Cancel</button><button type="submit" class="sh-btn">Save vehicle capacity</button></div>
    </form>
</dialog>
<script type="application/json" id="dispatchRiderData">{!! json_encode($riderDetails->values(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
@push('scripts')
<script src="{{ asset('js/shophop-rider-dispatch.js') }}?v=20261112-navy-pink-2" defer></script>
<script src="{{ asset('js/shophop-rider-batches.js') }}?v=20261112-navy-pink-2" defer></script>
@endpush
