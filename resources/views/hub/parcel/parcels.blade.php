@extends('hub.layouts.console')
@section('title', 'Parcels — Province Hub')
@section('content')
@php
    $badge = [
        'Arrived'    => 'bg-teal/15 text-teal',
        'In transit' => 'bg-sky/15 text-sky',
        'Received'   => 'bg-teal/15 text-teal',
        'Sorted'     => 'bg-teal/15 text-teal-dark',
        'Delayed'    => 'bg-yellow/20 text-navy',
        'Dispatched' => 'bg-sky/15 text-sky',
    ];

    // Auto-routing (UI demo): destination city -> station hub na sumasakop dito
    $stationFor = [
        'Los Baños' => 'Los Baños Station',
        'Calamba' => 'Calamba Station',
        'Santa Rosa' => 'Santa Rosa Station',
        'San Pablo City' => 'San Pablo Station',
    ];

    // [tracking, from, destination city, status, truck]
    $inbound = [
        ['SH-100301', 'Main Hub (Manila)', 'Los Baños', 'Arrived', 'Truck LGN-204'],
        ['SH-100302', 'Main Hub (Manila)', 'Calamba', 'Arrived', 'Truck LGN-204'],
        ['SH-100303', 'Main Hub (Manila)', 'Santa Rosa', 'In transit', 'Truck LGN-207'],
        ['SH-100245', 'Main Hub (Manila)', 'San Pablo City', 'Delayed', 'Truck LGN-198'],
    ];

    // [tracking, station, destination city, status, vehicle]
    $outbound = [
        ['SH-100290', 'Los Baños Station', 'Los Baños', 'Sorted', '—'],
        ['SH-100291', 'Calamba Station', 'Calamba', 'Dispatched', 'Van CLB-12'],
        ['SH-100292', 'Santa Rosa Station', 'Santa Rosa', 'Sorted', '—'],
    ];
@endphp
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <p class="eyebrow">LAGUNA PROVINCE HUB</p>
        <h1 class="title">Parcels</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/60">Pindutin ang <strong>Confirm received</strong> kapag dumating na ang parcel sa hub. Awtomatikong mapupunta ito sa station hub na sumasakop sa address ng buyer.</p>
    </div>

    <div class="sh-surface flex flex-wrap items-center justify-between gap-3 p-4 sm:p-5">
        <div class="flex min-w-[220px] flex-1 items-center gap-3">
            <x-lucide-search class="h-5 w-5 text-teal" />
            <input id="parcelFilter" class="sh-input flex-1" placeholder="Hanapin ang tracking number (hal. SH-100301)" aria-label="Search tracking number">
        </div>
        <button type="button" id="confirmAll" class="sh-btn">Confirm all arrived</button>
    </div>

    <div class="flex gap-2" role="tablist">
        <button type="button" role="tab" data-tab="inbound" class="sh-btn text-xs">Inbound (<span id="countInbound">{{ count($inbound) }}</span>)</button>
        <button type="button" role="tab" data-tab="outbound" class="sh-btn-soft text-xs">Outbound (<span id="countOutbound">{{ count($outbound) }}</span>)</button>
    </div>

    {{-- Inbound --}}
    <section id="tab-inbound" class="sh-surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead class="border-b border-gray-border text-xs text-navy/50">
                    <tr>
                        <th class="p-4 font-semibold">Tracking no.</th>
                        <th class="p-4 font-semibold">From</th>
                        <th class="p-4 font-semibold">Destination</th>
                        <th class="p-4 font-semibold">Auto-route to</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Truck</th>
                        <th class="p-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody id="inbound-body" class="divide-y divide-gray-border">
                    @foreach($inbound as [$track, $from, $city, $status, $truck])
                        <tr class="hover:bg-gray-bg/40" data-track="{{ $track }}" data-city="{{ $city }}" data-station="{{ $stationFor[$city] ?? 'Needs review' }}" data-truck="{{ $truck }}">
                            <td class="p-4 font-bold">{{ $track }}</td>
                            <td class="p-4 text-navy/70">{{ $from }}</td>
                            <td class="p-4 text-navy/70">{{ $city }}</td>
                            <td class="p-4 text-navy/70">{{ $stationFor[$city] ?? 'Needs review' }}</td>
                            <td class="p-4"><span data-status class="sh-badge {{ $badge[$status] ?? '' }}">{{ $status }}</span></td>
                            <td class="p-4 text-navy/70">{{ $truck }}</td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($status === 'In transit')
                                        <button type="button" class="sh-btn-soft text-xs opacity-50" disabled title="Hindi pa dumarating">Not yet arrived</button>
                                    @else
                                        <button type="button" class="sh-btn-soft text-xs" data-confirm>Confirm received</button>
                                        <button type="button" class="sh-btn-ghost text-xs" data-issue>Report issue</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Outbound --}}
    <section id="tab-outbound" class="sh-surface hidden overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="border-b border-gray-border text-xs text-navy/50">
                    <tr>
                        <th class="p-4 font-semibold">Tracking no.</th>
                        <th class="p-4 font-semibold">Station hub</th>
                        <th class="p-4 font-semibold">Destination</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Vehicle</th>
                        <th class="p-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody id="outbound-body" class="divide-y divide-gray-border">
                    @foreach($outbound as [$track, $station, $city, $status, $vehicle])
                        <tr class="hover:bg-gray-bg/40" data-track="{{ $track }}">
                            <td class="p-4 font-bold">{{ $track }}</td>
                            <td class="p-4 text-navy/70">{{ $station }}</td>
                            <td class="p-4 text-navy/70">{{ $city }}</td>
                            <td class="p-4"><span data-status class="sh-badge {{ $badge[$status] ?? '' }}">{{ $status }}</span></td>
                            <td class="p-4 text-navy/70">{{ $vehicle }}</td>
                            <td class="p-4 text-right">
                                @if($status === 'Dispatched')
                                    <button type="button" class="sh-btn-soft text-xs opacity-50" disabled>Dispatched</button>
                                @else
                                    <button type="button" class="sh-btn-soft text-xs" data-dispatch>Dispatch</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <p id="toast" class="sh-alert-success hidden" role="status"></p>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const BADGE = @json($badge);
    const inBody = document.getElementById('inbound-body');
    const outBody = document.getElementById('outbound-body');
    const cIn = document.getElementById('countInbound');
    const cOut = document.getElementById('countOutbound');
    const toast = document.getElementById('toast');

    function say(msg) {
        toast.textContent = msg;
        toast.classList.remove('hidden');
        clearTimeout(say.t);
        say.t = setTimeout(() => toast.classList.add('hidden'), 3500);
    }
    function setBadge(el, status) {
        el.className = 'sh-badge ' + (BADGE[status] || '');
        el.textContent = status;
    }
    function counts() {
        cIn.textContent = inBody.querySelectorAll('tr').length;
        cOut.textContent = outBody.querySelectorAll('tr').length;
    }

    // Tabs
    document.querySelectorAll('[data-tab]').forEach(btn => btn.addEventListener('click', () => {
        document.querySelectorAll('[data-tab]').forEach(b => b.className = (b === btn ? 'sh-btn' : 'sh-btn-soft') + ' text-xs');
        ['inbound', 'outbound'].forEach(k => document.getElementById('tab-' + k).classList.toggle('hidden', k !== btn.dataset.tab));
    }));

    // Confirm received: parcel moves to outbound, already routed to its station hub
    function confirmRow(tr) {
        const track = tr.dataset.track, station = tr.dataset.station, city = tr.dataset.city;
        if (station === 'Needs review') {
            setBadge(tr.querySelector('[data-status]'), 'Delayed');
            say(track + ': walang station hub na sumasakop sa ' + city + '. Ilagay sa Exceptions.');
            return false;
        }
        tr.remove();
        const out = document.createElement('tr');
        out.className = 'hover:bg-gray-bg/40';
        out.dataset.track = track;
        out.innerHTML =
            '<td class="p-4 font-bold">' + track + '</td>' +
            '<td class="p-4 text-navy/70">' + station + '</td>' +
            '<td class="p-4 text-navy/70">' + city + '</td>' +
            '<td class="p-4"><span data-status class="sh-badge ' + BADGE['Sorted'] + '">Sorted</span></td>' +
            '<td class="p-4 text-navy/70">—</td>' +
            '<td class="p-4 text-right"><button type="button" class="sh-btn-soft text-xs" data-dispatch>Dispatch</button></td>';
        outBody.prepend(out);
        counts();
        return true;
    }

    inBody.addEventListener('click', e => {
        const tr = e.target.closest('tr');
        if (!tr) return;
        if (e.target.closest('[data-confirm]')) {
            if (confirmRow(tr)) say(tr.dataset.track + ' received. Naka-route sa ' + tr.dataset.station + '.');
        }
        if (e.target.closest('[data-issue]')) {
            const reason = prompt('Anong problema sa ' + tr.dataset.track + '? (damaged, kulang, maling ruta)');
            if (reason) { setBadge(tr.querySelector('[data-status]'), 'Delayed'); say(tr.dataset.track + ' inilagay sa Exceptions: ' + reason); }
        }
    });

    outBody.addEventListener('click', e => {
        const btn = e.target.closest('[data-dispatch]');
        if (!btn) return;
        const tr = btn.closest('tr');
        setBadge(tr.querySelector('[data-status]'), 'Dispatched');
        tr.children[4].textContent = 'Van (assigned)';
        btn.outerHTML = '<button type="button" class="sh-btn-soft text-xs opacity-50" disabled>Dispatched</button>';
        say(tr.dataset.track + ' dispatched papunta sa station hub.');
    });

    // Confirm all arrived
    document.getElementById('confirmAll').addEventListener('click', () => {
        const rows = [...inBody.querySelectorAll('tr')].filter(tr => tr.querySelector('[data-confirm]'));
        if (!rows.length) return say('Walang parcel na kailangang i-confirm.');
        const ok = rows.filter(confirmRow).length;
        say(ok + ' parcel(s) na-confirm at naka-route sa kani-kanilang station hub.');
    });

    // Filter by tracking number
    document.getElementById('parcelFilter').addEventListener('input', e => {
        const q = e.target.value.trim().toLowerCase();
        document.querySelectorAll('#inbound-body tr, #outbound-body tr').forEach(tr => {
            tr.classList.toggle('hidden', q && !tr.dataset.track.toLowerCase().includes(q));
        });
    });
})();
</script>
@endpush