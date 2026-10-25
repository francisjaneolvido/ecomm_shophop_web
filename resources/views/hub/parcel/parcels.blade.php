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
        <p class="mt-2 max-w-4xl text-sm text-navy/55">Receive arriving parcels, validate the next hierarchy hop, consolidate them into manifests, scan every parcel while loading, then assign a Linehaul vehicle and driver.</p>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="sh-stat"><p class="sh-stat-label">Awaiting receive</p><p class="sh-stat-value">{{ $stats['awaiting'] }}</p><p class="sh-stat-note">Truck arrived / legacy transfer ready</p></div>
        <div class="sh-stat"><p class="sh-stat-label">In transit / not arrived</p><p class="sh-stat-value">{{ $stats['not_departed'] }}</p><p class="sh-stat-note">Cannot receive yet</p></div>
        <div class="sh-stat"><p class="sh-stat-label">To sort</p><p class="sh-stat-value">{{ $stats['to_sort'] }}</p><p class="sh-stat-note">Received at hub</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Ready for manifest</p><p class="sh-stat-value">{{ $stats['ready'] }}</p><p class="sh-stat-note">Sorted by next hop</p></div>
    </section>

    <section class="sh-surface sh-card-pad space-y-4">
        <div><p class="sh-kicker">Step 1</p><h2 class="sh-title-sm">Receive inbound parcels</h2><p class="mt-1 text-xs text-navy/50">The Linehaul Driver first marks the Manifest as arrived. Hub staff then scans each physical parcel during unloading.</p></div>
        <form method="POST" action="{{ route('logistics.hub.parcels.receive') }}" class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end">@csrf
            <label class="sh-field"><span class="sh-label">Parcel QR / tracking</span><input class="sh-input" name="tracking_code" placeholder="SHP-YYYYMMDD-000001" required></label>
            <button class="sh-btn">Receive parcel</button>
        </form>

        @if($awaiting->isNotEmpty())
        <div class="sh-surface-soft overflow-x-auto"><table class="w-full min-w-[46rem] text-left text-sm">
            <thead><tr class="border-b border-gray-border text-xs text-navy/50"><th class="px-4 py-3">Tracking</th><th class="px-4 py-3">From</th><th class="px-4 py-3">Manifest</th><th class="px-4 py-3">Status</th></tr></thead>
            <tbody>@foreach($awaiting as $t)<tr class="border-b border-gray-border/60"><td class="px-4 py-3 font-semibold">{{ $t->delivery?->tracking_code }}</td><td class="px-4 py-3">{{ $t->fromCenter?->name }}</td><td class="px-4 py-3">{{ $t->manifest?->code ?? 'Direct transfer' }}</td><td class="px-4 py-3"><span class="sh-badge sh-badge-success">Ready to unload</span></td></tr>@endforeach</tbody>
        </table></div>
        @endif

        @if($notDeparted->isNotEmpty())
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                <p class="font-semibold text-navy">Still in transit / not marked arrived</p>
                <div class="mt-2 space-y-1 text-xs text-navy/60">@foreach($notDeparted as $t)<div>{{ $t->delivery?->tracking_code }} · {{ $t->fromCenter?->name }} → {{ $t->toCenter?->name }} · {{ $t->manifest?->code }}</div>@endforeach</div>
            </div>
        @endif
    </section>

    <section class="sh-surface sh-card-pad">
        <p class="sh-kicker">Step 2</p><h2 class="sh-title-sm">Sort & validate next hop</h2>
        @if($toSort->isEmpty())
            <div class="sh-empty mt-4"><p class="text-sm font-semibold text-navy">Nothing to sort</p></div>
        @else
        <form method="POST" action="{{ route('logistics.hub.parcels.sort') }}" data-bulk class="mt-4">@csrf
            <div class="sh-surface-soft overflow-x-auto"><table class="w-full min-w-[52rem] text-left text-sm">
                <thead><tr class="border-b border-gray-border text-xs text-navy/50"><th class="w-10 px-4 py-3"><input type="checkbox" data-check-all></th><th class="px-4 py-3">Tracking</th><th class="px-4 py-3">Final destination</th><th class="px-4 py-3">Required next hop</th></tr></thead>
                <tbody>@foreach($toSort as $p)<tr class="border-b border-gray-border/60"><td class="px-4 py-3"><input type="checkbox" data-row name="parcel_ids[]" value="{{ $p['id'] }}" @disabled(!$p['sortable'])></td><td class="px-4 py-3 font-semibold">{{ $p['tracking'] }}</td><td class="px-4 py-3">{{ $p['destination'] ?? '—' }}</td><td class="px-4 py-3">@if($p['sortable'])<span class="sh-badge sh-badge-info">{{ $p['next_hop'] }}</span>@else<span class="sh-badge sh-badge-danger">{{ $p['issue'] ?? 'Routing issue' }}</span>@endif</td></tr>@endforeach</tbody>
            </table></div>
            <div class="mt-3 flex justify-between"><span class="text-xs text-navy/50"><span data-count>0</span> selected</span><button class="sh-btn" data-submit>Sort selected</button></div>
        </form>
        @endif
    </section>

    <section class="space-y-4">
        <div class="sh-surface sh-card-pad"><p class="sh-kicker">Step 3</p><h2 class="sh-title-sm">Create outbound manifest by next hop</h2><p class="mt-1 text-xs text-navy/50">Each group below has one enforced next hub/station.</p></div>
        @forelse($ready as $group)
        <form method="POST" action="{{ route('logistics.hub.parcels.manifests.store') }}" data-bulk class="sh-surface sh-card-pad space-y-4">@csrf
            <input type="hidden" name="next_sorting_center_id" value="{{ $group['next_id'] }}">
            <div><p class="text-xs font-bold uppercase tracking-[.12em] text-teal">Required next hop</p><h3 class="mt-1 font-bold text-navy">{{ $group['next_name'] }}</h3><p class="text-xs text-navy/45">{{ $group['next_type']==='province_hub' ? 'Province Hub' : 'Station Hub' }} · {{ count($group['parcels']) }} parcel(s)</p></div>
            <div class="sh-surface-soft overflow-x-auto"><table class="w-full min-w-[52rem] text-left text-sm"><thead><tr class="border-b border-gray-border text-xs text-navy/50"><th class="w-10 px-4 py-3"><input type="checkbox" data-check-all></th><th class="px-4 py-3">Tracking</th><th class="px-4 py-3">Receiver</th><th class="px-4 py-3">Final station</th><th class="px-4 py-3">COD</th></tr></thead><tbody>
                @foreach($group['parcels'] as $parcel)<tr class="border-b border-gray-border/60"><td class="px-4 py-3"><input type="checkbox" data-row name="parcel_ids[]" value="{{ $parcel['id'] }}"></td><td class="px-4 py-3 font-semibold">{{ $parcel['tracking'] }}</td><td class="px-4 py-3">{{ $parcel['receiver'] ?? '—' }}</td><td class="px-4 py-3">{{ $parcel['final_destination'] ?? '—' }}</td><td class="px-4 py-3">{{ $parcel['cod']!==null ? '₱'.number_format($parcel['cod'],2) : 'Paid' }}</td></tr>@endforeach
            </tbody></table></div>
            <div class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end"><label class="sh-field"><span class="sh-label">Manifest note</span><input class="sh-input" name="note" maxlength="255"></label><button class="sh-btn" data-submit>Create manifest (<span data-count>0</span>)</button></div>
        </form>
        @empty
            <div class="sh-surface sh-card-pad sh-empty"><p class="text-sm font-semibold text-navy">No parcels ready for manifest</p></div>
        @endforelse
    </section>

    <section class="space-y-4">
        <div class="sh-surface sh-card-pad"><p class="sh-kicker">Step 4</p><h2 class="sh-title-sm">Load & assign Linehaul</h2><p class="mt-1 text-xs text-navy/50">Staff scans every parcel during loading. Only a complete manifest can be assigned to a vehicle and driver.</p></div>
        @forelse($manifests as $m)
        <article class="sh-surface sh-card-pad space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-navy">{{ $m['code'] }}</h3><span class="sh-badge {{ $m['status']==='received' ? 'sh-badge-success' : ($m['status']==='dispatched' ? 'sh-badge-info' : ($m['status']==='ready' ? 'sh-badge-success' : 'sh-badge-warning')) }}">{{ ucfirst(str_replace('_',' ',$m['status'])) }}</span></div>
                <p class="mt-1 text-xs text-navy/55">{{ $m['count'] }} → {{ $m['to'] }} · Loaded {{ $m['loaded'] }}/{{ $m['count'] }} · Received {{ $m['received'] }}/{{ $m['count'] }}</p>
                @if($m['trip_code'])<p class="mt-1 text-xs text-navy/55">Trip {{ $m['trip_code'] }} · {{ $m['vehicle'] }} {{ $m['plate'] ? '('.$m['plate'].')' : '' }} · {{ $m['driver'] }} · {{ ucfirst(str_replace('_',' ',$m['trip_status'])) }}</p>@endif
                </div>
                <a class="sh-btn-ghost" target="_blank" href="{{ route('logistics.hub.parcels.manifests.print',$m['id']) }}">Print Manifest QR</a>
            </div>

            @if(in_array($m['status'],['prepared','loading']))
            <form method="POST" action="{{ route('logistics.hub.parcels.manifests.load',$m['id']) }}" class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end">@csrf
                <label class="sh-field"><span class="sh-label">Scan parcel QR while loading</span><input class="sh-input" name="tracking_code" placeholder="SHP-YYYYMMDD-000001" required></label><button class="sh-btn">Mark loaded</button>
            </form>
            <p class="text-xs text-navy/45">Missing {{ max(0,$m['count']-$m['loaded']) }} parcel(s).</p>
            @endif

            @if($m['status']==='ready' && !$m['trip_code'])
                @if($drivers->isEmpty() || $vehicles->isEmpty())
                    <div class="sh-alert-error">No available Linehaul Driver/vehicle. Register them in Logistics → Fleet & Linehaul.</div>
                @else
                <form method="POST" action="{{ route('logistics.hub.parcels.manifests.assign-linehaul',$m['id']) }}" class="grid gap-3 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">@csrf
                    <label class="sh-field"><span class="sh-label">Vehicle</span><select class="sh-select" name="vehicle_id" required><option value="">Select</option>@foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->code }} · {{ $v->plate_number }}@if($v->is_primary) · PRIMARY @endif</option>@endforeach</select></label>
                    <label class="sh-field"><span class="sh-label">Driver</span><select class="sh-select" name="driver_id" required><option value="">Select</option>@foreach($drivers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></label>
                    <label class="sh-field"><span class="sh-label">Trip note</span><input class="sh-input" name="trip_note" maxlength="255"></label>
                    <button class="sh-btn">Assign Linehaul</button>
                </form>
                @endif
            @endif

            @if(in_array($m['status'],['prepared','loading','ready']) && !$m['trip_code'])
            <form method="POST" action="{{ route('logistics.hub.parcels.manifests.cancel',$m['id']) }}">@csrf<button class="sh-btn-danger" onclick="return confirm('Cancel manifest?')">Cancel manifest</button></form>
            @endif

            @if($m['trip_status']==='scheduled')<div class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-navy">Waiting for Driver to scan Manifest QR and Accept Load & Depart.</div>
            @elseif($m['trip_status']==='in_transit')<div class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-navy">Truck is in transit to {{ $m['to'] }}.</div>
            @elseif($m['trip_status']==='arrived')<div class="rounded-xl border border-yellow-200 bg-yellow-50 p-3 text-sm text-navy">Truck arrived. Destination staff must unload and scan each parcel.</div>
            @endif
        </article>
        @empty
            <div class="sh-surface sh-card-pad sh-empty"><p class="text-sm font-semibold text-navy">No outbound manifests yet</p></div>
        @endforelse
    </section>
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
