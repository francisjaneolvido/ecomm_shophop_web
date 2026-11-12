@extends('hub.layouts.console')

@section('title', 'Parcel Operations')

@section('content')
@php
    $awaiting = collect($awaiting ?? []);
    $notDeparted = collect($notDeparted ?? []);
    $toSort = collect($toSort ?? []);
    $ready = collect($ready ?? []);
    $manifests = collect($manifests ?? []);
    $drivers = collect($drivers ?? []);
    $vehicles = collect($vehicles ?? []);
@endphp

<div class="space-y-6">
    <header>
        <p class="sh-kicker">{{ $center->name }}</p>
        <h1 class="sh-title">Province Hub Parcel Operations</h1>
        <p class="mt-2 max-w-4xl text-sm text-navy/55">Receive arriving parcels, validate the next hierarchy hop, consolidate them into manifests, confirm the physical batch count, then choose a verified Road driver or Sea/Air cargo leg.</p>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="sh-stat"><p class="sh-stat-label">Awaiting receive</p><p class="sh-stat-value">{{ $stats['awaiting'] }}</p><p class="sh-stat-note">Truck arrived / legacy transfer ready</p></div>
        <div class="sh-stat"><p class="sh-stat-label">In transit / not arrived</p><p class="sh-stat-value">{{ $stats['not_departed'] }}</p><p class="sh-stat-note">Cannot receive yet</p></div>
        <div class="sh-stat"><p class="sh-stat-label">To sort</p><p class="sh-stat-value">{{ $stats['to_sort'] }}</p><p class="sh-stat-note">Received at hub</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Ready for manifest</p><p class="sh-stat-value">{{ $stats['ready'] }}</p><p class="sh-stat-note">Sorted by next hop</p></div>
    </section>

    <div class="ops-panel" data-ops-tabs>
    <nav class="ops-tabs" role="tablist" aria-label="Parcel workflow stages"><button type="button" class="ops-tab" role="tab" aria-selected="true" data-ops-tab="receive">1. Inbound receiving</button><button type="button" class="ops-tab" role="tab" aria-selected="false" data-ops-tab="sort">2. Sort parcels</button><button type="button" class="ops-tab" role="tab" aria-selected="false" data-ops-tab="manifest">3. Create manifest</button><button type="button" class="ops-tab" role="tab" aria-selected="false" data-ops-tab="transport">4. Transport & manifests</button></nav>
    <section data-ops-panel="receive" role="tabpanel" id="hub-receive" class="p-4 space-y-4 scroll-mt-6">
        <div><p class="sh-kicker">Step 1</p><h2 class="sh-title-sm">Receive inbound Road / Sea / Air</h2><p class="mt-1 text-xs text-navy/50">Normal intact batch: use Logistics mobile → Receive inbound manifest, scan the Manifest QR, acknowledge arrival, then confirm the physical count. The parcel field below is only for individual exception/reconciliation.</p></div>
        <form method="POST" action="{{ route('logistics.hub.parcels.receive') }}" data-confirm-title="Receive exception parcel?" data-confirm-message="Verify the physical parcel and its tracking code before recording hub receipt." data-confirm-button="Confirm receipt" class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end">@csrf
            <label class="sh-field"><span class="sh-label">Exception Parcel QR / tracking</span><input class="sh-input" id="hub-exception-code" name="tracking_code" placeholder="SHP-YYYYMMDD-000001" required></label>
            <button class="sh-btn">Receive exception parcel</button>
        </form>
        @include('partials.logistics-qr-scanner', ['inputId' => 'hub-exception-code'])

        @if($awaiting->isNotEmpty())
        <div class="sh-surface-soft overflow-x-auto"><table class="ops-table w-full min-w-[46rem] text-left text-sm">
            <thead><tr class="border-b border-gray-border text-xs text-navy/50"><th class="px-4 py-3">Tracking</th><th class="px-4 py-3">From</th><th class="px-4 py-3">Manifest</th><th class="px-4 py-3">Status</th></tr></thead>
            <tbody>@foreach($awaiting as $t)<tr class="border-b border-gray-border/60"><td class="px-4 py-3 font-semibold">{{ $t->delivery?->tracking_code }}</td><td class="px-4 py-3">{{ $t->fromCenter?->name }}</td><td class="px-4 py-3">{{ $t->manifest?->code ?? 'Direct transfer' }}</td><td class="px-4 py-3"><span class="sh-badge sh-badge-success">Ready to unload</span></td></tr>@endforeach</tbody>
        </table></div>
        @endif

        @if($notDeparted->isNotEmpty())
            <div class="ops-panel mt-3"><div class="ops-panel-head"><div><h2>Still in transit</h2><p class="ops-panel-note">Cannot receive until the destination acknowledges physical arrival.</p></div></div>
                <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Tracking</th><th>From Center</th><th>Destination</th><th>Manifest</th><th>Status</th></tr></thead><tbody>
                    @foreach($notDeparted as $t)<tr><td><strong>{{ $t->delivery?->tracking_code }}</strong></td><td>{{ $t->fromCenter?->name }}</td><td>{{ $t->toCenter?->name }}</td><td>{{ $t->manifest?->code ?? 'Individual' }}</td><td><span class="sh-badge sh-badge-warning">In transit</span></td></tr>@endforeach
                </tbody></table></div>
            </div>
        @endif
    </section>

    <section data-ops-panel="sort" role="tabpanel" hidden id="hub-sort" class="p-4 scroll-mt-6">
        <p class="sh-kicker">Step 2</p><h2 class="sh-title-sm">Sort & validate next hop</h2>
        @if($toSort->isEmpty())
            <div class="sh-empty mt-4"><p class="text-sm font-semibold text-navy">Nothing to sort</p></div>
        @else
        <form method="POST" action="{{ route('logistics.hub.parcels.sort') }}" data-bulk class="mt-4">@csrf
            <div class="sh-surface-soft overflow-x-auto"><table class="ops-table w-full min-w-[52rem] text-left text-sm">
                <thead><tr class="border-b border-gray-border text-xs text-navy/50"><th class="w-10 px-4 py-3"><input type="checkbox" data-check-all></th><th class="px-4 py-3">Tracking</th><th class="px-4 py-3">Final destination</th><th class="px-4 py-3">Required next hop</th></tr></thead>
                <tbody>@foreach($toSort as $p)<tr class="border-b border-gray-border/60"><td class="px-4 py-3"><input type="checkbox" data-row name="parcel_ids[]" value="{{ $p['id'] }}" @disabled(!$p['sortable'])></td><td class="px-4 py-3 font-semibold">{{ $p['tracking'] }}</td><td class="px-4 py-3">{{ $p['destination'] ?? '—' }}</td><td class="px-4 py-3">@if($p['sortable'])<span class="sh-badge sh-badge-info">{{ $p['next_hop'] }}</span>@else<span class="sh-badge sh-badge-danger">{{ $p['issue'] ?? 'Routing issue' }}</span>@endif</td></tr>@endforeach</tbody>
            </table></div>
            <div class="mt-3 flex justify-between"><span class="text-xs text-navy/50"><span data-count>0</span> selected</span><button class="sh-btn" data-submit>Sort selected</button></div>
        </form>
        @endif
    </section>

    <section data-ops-panel="manifest" role="tabpanel" hidden id="hub-manifest" class="space-y-4 scroll-mt-6">
        <div class="sh-surface sh-card-pad"><p class="sh-kicker">Step 3</p><h2 class="sh-title-sm">Create outbound manifest by next hop</h2><p class="mt-1 text-xs text-navy/50">Each group below has one enforced next hub/station.</p></div>
        @forelse($ready as $group)
        <form method="POST" action="{{ route('logistics.hub.parcels.manifests.store') }}" data-bulk class="sh-surface sh-card-pad space-y-4">@csrf
            <input type="hidden" name="next_sorting_center_id" value="{{ $group['next_id'] }}">
            <div><p class="text-xs font-bold uppercase tracking-[.12em] text-teal">Required next hop</p><h3 class="mt-1 font-bold text-navy">{{ $group['next_name'] }}</h3><p class="text-xs text-navy/45">{{ $group['next_type']==='province_hub' ? 'Province Hub' : 'Station Hub' }} · {{ count($group['parcels']) }} parcel(s)</p></div>
            <div class="sh-surface-soft overflow-x-auto"><table class="ops-table w-full min-w-[52rem] text-left text-sm"><thead><tr class="border-b border-gray-border text-xs text-navy/50"><th class="w-10 px-4 py-3"><input type="checkbox" data-check-all></th><th class="px-4 py-3">Tracking</th><th class="px-4 py-3">Receiver</th><th class="px-4 py-3">Final station</th><th class="px-4 py-3">COD</th></tr></thead><tbody>
                @foreach($group['parcels'] as $parcel)<tr class="border-b border-gray-border/60"><td class="px-4 py-3"><input type="checkbox" data-row name="parcel_ids[]" value="{{ $parcel['id'] }}"></td><td class="px-4 py-3 font-semibold">{{ $parcel['tracking'] }}</td><td class="px-4 py-3">{{ $parcel['receiver'] ?? '—' }}</td><td class="px-4 py-3">{{ $parcel['final_destination'] ?? '—' }}</td><td class="px-4 py-3">{{ $parcel['cod']!==null ? '₱'.number_format($parcel['cod'],2) : 'Paid' }}</td></tr>@endforeach
            </tbody></table></div>
            <div class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end"><label class="sh-field"><span class="sh-label">Manifest note</span><input class="sh-input" name="note" maxlength="255"></label><button class="sh-btn" data-submit>Create manifest (<span data-count>0</span>)</button></div>
        </form>
        @empty
            <div class="sh-surface sh-card-pad sh-empty"><p class="text-sm font-semibold text-navy">No parcels ready for manifest</p></div>
        @endforelse
    </section>

    <section data-ops-panel="transport" role="tabpanel" hidden id="hub-linehaul" class="space-y-4 scroll-mt-6">
        <div class="sh-surface sh-card-pad"><p class="sh-kicker">Step 4</p><h2 class="sh-title-sm">Load & assign transport</h2><p class="mt-1 text-xs text-navy/50">Staff confirms the physical batch count once. Only verified transport is allowed. Road uses a driver and vehicle; Sea/Air uses a carrier booking and hub handoff.</p></div>
        <div class="ops-panel mt-4"><div class="ops-panel-head"><div><h2>Outbound manifest register</h2><p class="ops-panel-note">Select Manage to load, assign transport, print or reconcile a manifest.</p></div><input class="sh-input" style="max-width:240px" type="search" data-ops-search="hubManifestRows" placeholder="Search this page" aria-label="Search manifests"></div>
<div class="ops-table-scroll"><table class="ops-table" id="hubManifestRows"><thead><tr><th>Manifest</th><th>Next center</th><th>Counts</th><th>Status</th><th>Transport</th><th>Action</th></tr></thead><tbody>
@forelse($manifests as $m)
        <tr data-ops-row><td><strong>{{ $m['code'] }}</strong><div class="ops-row-sub">{{ $m['created'] ?? '' }}</div></td>
 <td>{{ $m['to'] ?? '—' }}</td>
 <td>{{ $m['count'] }} parcels<div class="ops-row-sub">Loaded {{ $m['loaded'] }}/{{ $m['count'] }} · Received {{ $m['received'] }}/{{ $m['count'] }}</div></td>
 <td><span class="sh-badge {{ in_array($m['status'],['ready','received'],true) ? 'sh-badge-success' : ($m['status']==='dispatched'?'sh-badge-info':'sh-badge-warning') }}">{{ ucfirst(str_replace('_',' ',$m['status'])) }}</span></td>
 <td>{{ $m['cargo_mode'] ?? $m['trip_code'] ?? 'Unassigned' }}</td>
 <td class="ops-row-actions"><button type="button" class="ops-action" data-ops-template="opsManifest{{ $m['id'] }}">Manage</button></td></tr>
 <template id="opsManifest{{ $m['id'] }}">
 <div class="ops-dialog-head"><div><h2 class="ops-dialog-title">Manifest {{ $m['code'] }}</h2><p class="ops-panel-note">{{ $m['to'] }} · {{ $m['count'] }} parcels</p></div><button type="button" class="ops-action" data-ops-close>✕</button></div>
 <div class="ops-dialog-body"><div class="space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-navy">{{ $m['code'] }}</h3><span class="sh-badge {{ $m['status']==='received' ? 'sh-badge-success' : ($m['status']==='dispatched' ? 'sh-badge-info' : ($m['status']==='ready' ? 'sh-badge-success' : 'sh-badge-warning')) }}">{{ ucfirst(str_replace('_',' ',$m['status'])) }}</span></div>
                <p class="mt-1 text-xs text-navy/55">{{ $m['count'] }} → {{ $m['to'] }} · Loaded {{ $m['loaded'] }}/{{ $m['count'] }} · Received {{ $m['received'] }}/{{ $m['count'] }}</p>
                @if($m['cargo_mode'])
                <p class="mt-1 text-xs font-semibold text-teal">{{ strtoupper($m['cargo_mode']) }} CARGO · {{ $m['carrier_name'] }} · Ref {{ $m['booking_reference'] }} · {{ ucfirst(str_replace('_',' ', $m['cargo_status'])) }}</p>
                @elseif($m['trip_code'])
                <p class="mt-1 text-xs text-navy/55">ROAD · {{ $m['trip_code'] }} · {{ $m['vehicle'] }} · {{ $m['driver'] }} · {{ ucfirst(str_replace('_',' ',$m['trip_status'])) }}</p>
                @endif
                </div>
                <a class="sh-btn-ghost" target="_blank" href="{{ route('logistics.hub.parcels.manifests.print',$m['id']) }}">Print Manifest QR</a>
            </div>

            @if(in_array($m['status'],['prepared','loading']))
            <form method="POST" action="{{ route('logistics.hub.parcels.manifests.load',$m['id']) }}" data-confirm-title="Confirm manifest loaded?" data-confirm-message="Verify the physical batch count before confirming loaded parcels." data-confirm-subject="{{ $m['code'] }}" data-confirm-button="Confirm loaded" class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end">@csrf
                <label class="sh-field"><span class="sh-label">Physical parcel count loaded</span><input type="number" class="sh-input" name="physical_count" min="1" max="5000" value="{{ $m['count'] }}" required></label><button class="sh-btn">Confirm complete batch loaded</button>
            </form>
            <p class="text-xs text-navy/45">Expected {{ $m['count'] }} parcel(s). If the count differs, use Logistics mobile → Outbound Manifest → Exception / reconciliation mode for individual Parcel QR checks.</p>
            @endif

            @if($m['status']==='ready' && !$m['trip_code'])
                @if(!$m['region_known'])<div class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">Unknown Province Hub region. Correct PSGC region metadata before assigning road or cargo.</div>@endif
                @if($m['cross_group'] || !empty($m['cargo_links']))
                    @if(empty($m['cargo_links']))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-navy">Inter-island shipment: no verified Sea/Air connection for this exact destination. Add one in Logistics → Transport Routes; ROAD assignment is blocked.</div>
                    @else
                    <form method="POST" action="{{ route('logistics.hub.parcels.manifests.cargo-book',$m['id']) }}" class="rounded-xl border border-teal/25 bg-teal/5 p-3 space-y-3">@csrf
                        <div class="flex flex-wrap items-center gap-2"><span class="sh-badge sh-badge-info">SEA / AIR CARGO</span><p class="text-xs text-navy/60">Requires verified connection and physical carrier handoff.</p></div>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 xl:items-end">
                            <label class="sh-field"><span class="sh-label">Verified connection *</span><select class="sh-select" name="route_link_id" required><option value="">Choose transport</option>@foreach($m['cargo_links'] as $link)<option value="{{ $link['id'] }}">{{ strtoupper($link['mode']) }} · {{ $link['notes'] }}</option>@endforeach</select></label>
                            <label class="sh-field"><span class="sh-label">Carrier / operator *</span><input class="sh-input" name="carrier_name" placeholder="Actual shipping / airline carrier" maxlength="150" required></label>
                            <label class="sh-field"><span class="sh-label">Booking reference *</span><input class="sh-input" name="booking_reference" placeholder="Carrier waybill / booking" maxlength="120" required></label>
                            <button class="sh-btn">Book carrier</button>
                        </div>
                    </form>
                    @endif
                @endif
                @if(!$m['cross_group'] && $m['region_known'])
                    @if($drivers->isEmpty() || $vehicles->isEmpty())
                        <div class="sh-alert-error">No available Road Linehaul Driver/vehicle. Register them under Fleet & Linehaul.</div>
                    @else
                        <form method="POST" action="{{ route('logistics.hub.parcels.manifests.assign-linehaul',$m['id']) }}" class="grid gap-3 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">@csrf
                            <label class="sh-field"><span class="sh-label">Road vehicle</span><select class="sh-select" name="vehicle_id" required><option value="">Select vehicle</option>@foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->code }} · {{ $v->plate_number }}</option>@endforeach</select></label>
                            <label class="sh-field"><span class="sh-label">Road driver</span><select class="sh-select" name="driver_id" required><option value="">Select driver</option>@foreach($drivers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></label>
                            <label class="sh-field"><span class="sh-label">Trip note</span><input class="sh-input" name="trip_note" maxlength="255"></label>
                            <button class="sh-btn">Assign Road Linehaul</button>
                        </form>
                    @endif
                @endif
            @endif
            @if($m['cargo_status']==='booked')
            <form method="POST" action="{{ route('logistics.hub.parcels.manifests.cargo-depart',$m['id']) }}" data-confirm-title="Carrier physically accepted cargo?" data-confirm-message="This starts the Sea/Air transport leg and records parcel transfers. Verify real carrier custody first." data-confirm-button="Confirm departure" class="rounded-xl border border-teal/25 p-3 flex flex-wrap items-center justify-between gap-3">@csrf
                <div><p class="text-sm font-bold text-navy">Carrier booking ready for handoff</p><p class="text-xs text-navy/55">Confirm only after verifying manifest load and real carrier custody.</p></div>
                <input type="hidden" name="custody_confirmation" value="CONFIRM_CARRIER_HANDOFF"><button class="sh-btn">Confirm carrier handoff</button>
            </form>
            @endif
            @if(in_array($m['status'],['prepared','loading','ready']) && !$m['trip_code'])
            <form method="POST" action="{{ route('logistics.hub.parcels.manifests.cancel',$m['id']) }}" data-confirm-title="Cancel this manifest?" data-confirm-message="This removes the manifest from active outbound processing. Verify its status before continuing." data-confirm-button="Cancel manifest" data-confirm-danger="1">@csrf<button class="sh-btn-danger">Cancel manifest</button></form>
            @endif

            @if($m['cross_group'] && $m['trip_code'] && !$m['cargo_mode'])<div class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-700">LEGACY INVALID ROAD TRIP — do not confirm Davao receipt. Reconcile this active trip manually.</div>@endif
            @if($m['cargo_mode'])<div class="text-xs text-navy/55">Sea/Air cargo status: {{ str_replace('_',' ',$m['cargo_status']) }}. Destination scans the same Manifest QR to acknowledge arrival and receive parcels.</div>
            @elseif($m['trip_status']==='scheduled')<div class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-navy">Waiting for Driver to scan Manifest QR and Accept Load & Depart.</div>
            @elseif($m['trip_status']==='in_transit')<div class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-navy">Truck is in transit to {{ $m['to'] }}.</div>
            @elseif($m['trip_status']==='arrived')<div class="rounded-xl border border-yellow-200 bg-yellow-50 p-3 text-sm text-navy">Destination acknowledged the Manifest QR. Receiving is controlled by the destination center.</div>
            @endif
        </div></div><div class="ops-dialog-foot"><button type="button" class="sh-btn-ghost" data-ops-close>Close</button></div></template>
        @empty
            <tr><td colspan="6" class="ops-empty">No outbound manifests yet.</td></tr>
        @endforelse
<tr data-ops-search-empty hidden><td colspan="6" class="ops-empty">No matching manifests on this page.</td></tr></tbody></table></div></div><dialog class="ops-dialog" id="opsRecordDialog"><div data-ops-record-content></div></dialog>
    </section>
</div>
</div>
@endsection

@push('scripts')
<script>
(function(){
 document.querySelectorAll('[data-bulk]').forEach(function(form){
  const boxes=[...form.querySelectorAll('[data-row]')].filter(b=>!b.disabled), all=form.querySelector('[data-check-all]'), counts=[...form.querySelectorAll('[data-count]')], submit=form.querySelector('[data-submit]');
  function sync(){const n=boxes.filter(b=>b.checked).length;counts.forEach(c=>c.textContent=n);if(submit)submit.disabled=n===0;if(all){all.checked=boxes.length>0&&n===boxes.length;all.indeterminate=n>0&&n<boxes.length;}}
  boxes.forEach(b=>b.addEventListener('change',sync));if(all)all.addEventListener('change',()=>{boxes.forEach(b=>b.checked=all.checked);sync();});sync();
 });
})();
</script>
@endpush
