@extends('branch.layout.app')

@section('title', 'Outbound Linehaul')

@section('content')
@php
    $toSort = collect($toSort ?? []);
    $ready = collect($ready ?? []);
    $manifests = collect($manifests ?? []);
    $drivers = collect($drivers ?? []);
    $vehicles = collect($vehicles ?? []);
@endphp

<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">{{ $center->name ?? 'Your branch' }}</p>
            <h1 class="title">Outbound Linehaul</h1>
            <p class="description">
                Receive and sort parcels, build a manifest, confirm the physical batch count, then assign a registered truck/van and Linehaul Driver.
                @if($hub) Next hub: <span class="font-semibold text-navy">{{ $hub->name }}</span>. @endif
            </p>
        </div>
    </header>

    @unless($hub)
        <div class="sh-alert-error">Walang active Province Hub na naka-link sa branch na ito.</div>
    @endunless

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="sh-stat"><p class="sh-stat-label">To sort</p><p class="sh-stat-value">{{ number_format($stats['to_sort']) }}</p><p class="sh-stat-note">Received at branch</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Ready for manifest</p><p class="sh-stat-value">{{ number_format($stats['ready']) }}</p><p class="sh-stat-note">Not reserved yet</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Loading / Ready</p><p class="sh-stat-value">{{ number_format($stats['prepared']) }}</p><p class="sh-stat-note">Waiting for Linehaul</p></div>
        <div class="sh-stat"><p class="sh-stat-label">In transit</p><p class="sh-stat-value">{{ number_format($stats['in_transit']) }}</p><p class="sh-stat-note">Driver/truck departed</p></div>
    </section>

    <section class="sh-surface sh-card-pad">
        <p class="sh-kicker">Step 1</p>
        <h2 class="sh-title-sm">Sort received parcels</h2>
        <p class="mt-1 text-xs text-navy/50">Local parcels stay here for Delivery Rider. Parcels for another station are routed to the required Province Hub.</p>
        @if($toSort->isEmpty())
            <div class="sh-empty mt-4"><p class="text-sm font-semibold text-navy">Nothing to sort</p></div>
        @else
        <form method="POST" action="{{ route('branch.outbound.sort') }}" data-bulk class="mt-4">@csrf
            <div class="sh-surface-soft overflow-x-auto"><table class="w-full min-w-[42rem] text-left text-sm">
                <thead><tr class="border-b border-gray-border text-xs text-navy/50"><th class="w-10 px-4 py-3"><input type="checkbox" data-check-all></th><th class="px-4 py-3">Tracking</th><th class="px-4 py-3">Receiver</th><th class="px-4 py-3">Destination</th></tr></thead>
                <tbody>
                @foreach($toSort as $p)
                    <tr class="border-b border-gray-border/60">
                        <td class="px-4 py-3"><input type="checkbox" name="parcel_ids[]" value="{{ $p['id'] }}" data-row @checked($p['sortable']) @disabled(!$p['sortable'])></td>
                        <td class="px-4 py-3 font-semibold">{{ $p['tracking'] }}</td>
                        <td class="px-4 py-3">{{ $p['receiver'] ?? '—' }}<div class="text-xs text-navy/45">{{ $p['barangay'] }}</div></td>
                        <td class="px-4 py-3">@if(!$p['sortable'])<span class="sh-badge sh-badge-danger">No coverage</span>@elseif($p['local'])<span class="sh-badge sh-badge-success">Local delivery</span>@else<span class="sh-badge sh-badge-info">{{ $p['destination'] }}</span>@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <div class="mt-3 flex items-center justify-between gap-3"><span class="text-xs text-navy/50"><span data-count>0</span> selected</span><button class="sh-btn" data-submit>Sort selected</button></div>
        </form>
        @endif
    </section>

    <section class="sh-surface sh-card-pad">
        <p class="sh-kicker">Step 2</p>
        <h2 class="sh-title-sm">Create manifest</h2>
        <p class="mt-1 text-xs text-navy/50">One manifest = one origin + one next hub + many parcels. Transport is assigned only after loading is verified.</p>
        @if($ready->isEmpty())
            <div class="sh-empty mt-4"><p class="text-sm font-semibold text-navy">No parcels ready for a new manifest</p></div>
        @else
        <form method="POST" action="{{ route('branch.outbound.store') }}" data-bulk class="mt-4">@csrf
            <div class="sh-surface-soft overflow-x-auto"><table class="w-full min-w-[48rem] text-left text-sm">
                <thead><tr class="border-b border-gray-border text-xs text-navy/50"><th class="w-10 px-4 py-3"><input type="checkbox" data-check-all></th><th class="px-4 py-3">Tracking</th><th class="px-4 py-3">Receiver</th><th class="px-4 py-3">Final branch</th><th class="px-4 py-3">COD</th></tr></thead>
                <tbody>
                @foreach($ready as $p)
                    <tr class="border-b border-gray-border/60">
                        <td class="px-4 py-3"><input type="checkbox" name="parcel_ids[]" value="{{ $p['id'] }}" data-row checked></td>
                        <td class="px-4 py-3 font-semibold">{{ $p['tracking'] }}</td>
                        <td class="px-4 py-3">{{ $p['receiver'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $p['final'] }}</td>
                        <td class="px-4 py-3">{{ $p['cod'] ? '₱'.number_format($p['cod'],2) : 'Paid' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <div class="mt-4 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end">
                <label class="sh-field"><span class="sh-label">Manifest note</span><input name="note" class="sh-input" maxlength="255" placeholder="Optional batch note"></label>
                <button class="sh-btn" data-submit>Create manifest (<span data-count>0</span>)</button>
            </div>
        </form>
        @endif
    </section>

    <section class="space-y-4">
        <div class="sh-surface sh-card-pad">
            <p class="sh-kicker">Step 3</p>
            <h2 class="sh-title-sm">Load parcels & assign Linehaul</h2>
            <p class="mt-1 text-xs text-navy/50">Branch staff scans every parcel while it is physically loaded. The Driver scans only the Manifest QR when taking custody of the complete load.</p>
        </div>

        @forelse($manifests as $m)
            <article class="sh-surface sh-card-pad space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold text-navy">{{ $m['code'] }}</h3>
                            <span class="sh-badge {{ $m['status']==='received' ? 'sh-badge-success' : ($m['status']==='dispatched' ? 'sh-badge-info' : ($m['status']==='ready' ? 'sh-badge-success' : 'sh-badge-warning')) }}">{{ ucfirst(str_replace('_',' ',$m['status'])) }}</span>
                        </div>
                        <p class="mt-1 text-xs text-navy/55">{{ $m['count'] }} parcel(s) → {{ $m['to'] }} · Loaded {{ $m['loaded'] }}/{{ $m['count'] }} · Received {{ $m['received'] }}/{{ $m['count'] }}</p>
                        @if($m['trip_code'])<p class="mt-1 text-xs text-navy/55">Trip {{ $m['trip_code'] }} · {{ $m['vehicle'] }} {{ $m['plate'] ? '('.$m['plate'].')' : '' }} · Driver {{ $m['driver'] }} · {{ ucfirst(str_replace('_',' ',$m['trip_status'])) }}</p>@endif
                    </div>
                    <a href="{{ route('branch.outbound.print', $m['id']) }}" target="_blank" class="sh-btn-ghost">Print Manifest QR</a>
                </div>

                @if(in_array($m['status'], ['prepared','loading']))
                    <form method="POST" action="{{ route('branch.outbound.load', $m['id']) }}" class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end">@csrf
                        <label class="sh-field">
                            <span class="sh-label">Physical parcel count loaded</span>
                            <input type="number" name="physical_count" class="sh-input" min="1" max="5000" value="{{ $m['count'] }}" required>
                        </label>
                        <button class="sh-btn">Confirm complete batch loaded</button>
                    </form>
                    <p class="text-xs text-navy/45">Expected: {{ $m['count'] }} parcels. If the physical count does not match, do not confirm the batch; use Logistics mobile → Outbound Manifest → Exception / reconciliation mode for individual Parcel QR checks.</p>
                @endif

                @if($m['status']==='ready' && !$m['trip_code'])
                    @if($drivers->isEmpty() || $vehicles->isEmpty())
                        <div class="sh-alert-error">Register an active Linehaul Driver and an available vehicle for {{ $hub?->name ?? 'this Province Hub' }} in Logistics → Fleet & Linehaul first.</div>
                    @else
                    <form method="POST" action="{{ route('branch.outbound.assign-linehaul', $m['id']) }}" class="grid gap-3 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">@csrf
                        <label class="sh-field"><span class="sh-label">Vehicle</span><select name="vehicle_id" class="sh-select" required><option value="">Select vehicle</option>@foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->code }} · {{ $v->plate_number }} · {{ ucwords(str_replace('_',' ',$v->vehicle_type)) }}@if($v->is_primary) · PRIMARY @endif</option>@endforeach</select></label>
                        <label class="sh-field"><span class="sh-label">Linehaul Driver</span><select name="driver_id" class="sh-select" required><option value="">Select driver</option>@foreach($drivers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></label>
                        <label class="sh-field"><span class="sh-label">Trip note</span><input name="trip_note" class="sh-input" maxlength="255"></label>
                        <button class="sh-btn">Assign Linehaul</button>
                    </form>
                    @endif
                @endif

                @if(in_array($m['status'], ['prepared','loading','ready']) && !$m['trip_code'])
                    <form method="POST" action="{{ route('branch.outbound.cancel', $m['id']) }}">@csrf<button class="sh-btn-danger" onclick="return confirm('Cancel this manifest?')">Cancel manifest</button></form>
                @endif

                @if($m['trip_code'] && $m['trip_status']==='scheduled')
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-navy">Waiting for the assigned Driver to scan Manifest QR <strong>{{ $m['code'] }}</strong> and tap <strong>Accept Load & Depart</strong>.</div>
                @elseif($m['trip_status']==='in_transit')
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-navy">Truck is in transit to {{ $m['to'] }}.</div>
                @elseif($m['trip_status']==='arrived')
                    <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-navy">Destination confirmed the Manifest QR arrival. Receiving is now controlled by the destination Branch/Hub.</div>
                @endif
            </article>
        @empty
            <div class="sh-surface sh-card-pad sh-empty"><p class="text-sm font-semibold text-navy">No manifests yet</p></div>
        @endforelse
    </section>
</div>
@endsection

@push('scripts')
<script>
(function(){
 document.querySelectorAll('[data-bulk]').forEach(function(form){
   const boxes=[...form.querySelectorAll('[data-row]')].filter(b=>!b.disabled), all=form.querySelector('[data-check-all]');
   const counts=[...form.querySelectorAll('[data-count]')], submit=form.querySelector('[data-submit]');
   function sync(){const n=boxes.filter(b=>b.checked).length; counts.forEach(c=>c.textContent=n); if(submit)submit.disabled=n===0; if(all){all.checked=boxes.length>0&&n===boxes.length;all.indeterminate=n>0&&n<boxes.length;}}
   boxes.forEach(b=>b.addEventListener('change',sync)); if(all)all.addEventListener('change',()=>{boxes.forEach(b=>b.checked=all.checked);sync();}); sync();
 });
})();
</script>
@endpush
