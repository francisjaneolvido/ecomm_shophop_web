{{-- resources/views/logistics/reports.blade.php --}}

@extends('layouts.logistics')

@section('title', 'Reports — ShopHop Logistics')

@section('content')

{{--
    ============================================================
    BACKEND NOTES for Yesel (things the controller/routes need)
    ============================================================
    1. $from / $to — Carbon instances for the current filter range,
       read from request('from') / request('to') same as before.

    2. Each entry in $riders should include:
       - 'id'                 => rider ID (per-rider PDF export + modal)
       - 'phone'               => (optional) shown in the "View full" modal
       - 'vehicle'             => (optional) shown in the "View full" modal
       - 'joined'              => (optional) e.g. "Jan 2025", shown in the modal
       - 'recent_deliveries'   => array of the rider's orders **already
                                   filtered server-side to the $from/$to
                                   range** — the view no longer does any
                                   client-side date filtering, so make sure
                                   the controller scopes this query by date.
            [
                ['order_id' => 'SH-10231', 'date' => '2026-08-20', 'status' => 'delivered', 'time' => '38 min', 'fee' => 85],
                ...
            ]
         Missing keys degrade gracefully (row still renders).

    3. Routes needed:
       - route('logistics.reports.index')                    -> GET, accepts ?from=&to= (already existed)
       - route('logistics.reports.export.pdf', [...])         -> streams/downloads a PDF of the whole report
                                                                   (e.g. via barryvdh/laravel-dompdf)
       - route('logistics.reports.riders.export.pdf', $id)    -> PDF for a single rider, same date range

    4. The date-range calendar picker below is just a nicer front-end
       for the same GET ?from=&to= params — picking a range and hitting
       "Apply" in the popover submits the existing filter <form>, so no
       new backend behavior is required for it.
    ============================================================
--}}

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-navy text-xl sm:text-2xl lg:text-3xl font-bold">Delivery &amp; earnings report</h1>
        <p class="text-navy/55 text-sm mt-1">Filter by date range, then export for your records.</p>
    </div>
</div>

<form method="GET" action="{{ route('logistics.reports.index') }}" id="reportFilterForm"
      class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 mb-2">

    <button type="button" id="dateRangeTriggerBtn"
            class="inline-flex items-center gap-2 border border-gray-border rounded-full pl-4 pr-3 py-2.5 text-sm font-semibold text-navy hover:border-teal transition w-full sm:w-auto">
        <svg class="icon w-4 h-4 text-navy/40" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span id="dateRangeLabel">{{ $from->format('M j') }} – {{ $to->format('M j, Y') }}</span>
        <svg class="icon w-3.5 h-3.5 text-navy/30" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <input type="hidden" name="from" id="reportFromDate" value="{{ $from->format('Y-m-d') }}">
    <input type="hidden" name="to" id="reportToDate" value="{{ $to->format('Y-m-d') }}">

    <a href="{{ route('logistics.reports.export.pdf', request()->query()) }}"
       class="sm:ml-auto inline-flex items-center justify-center gap-2 text-xs font-semibold bg-navy hover:bg-teal text-white px-4 py-2.5 rounded-full transition">
        <svg class="icon w-3.5 h-3.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Export PDF
    </a>
</form>
<p class="text-[11px] font-semibold text-navy/35 mb-6">Picking a range reloads the report scoped to those dates.</p>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-gray-border rounded-2xl p-5">
        <p class="text-xs font-semibold text-navy/50 mb-2">Total deliveries</p>
        <p class="text-2xl font-bold text-navy">{{ number_format($summary['total_deliveries']) }}</p>
    </div>
    <div class="bg-white border border-gray-border rounded-2xl p-5">
        <p class="text-xs font-semibold text-navy/50 mb-2">On-time rate</p>
        <p class="text-2xl font-bold text-navy">{{ $summary['on_time_rate'] }}%</p>
    </div>
    <div class="bg-white border border-gray-border rounded-2xl p-5">
        <p class="text-xs font-semibold text-navy/50 mb-2">Gross delivery fees</p>
        <p class="text-2xl font-bold text-navy">₱{{ number_format($summary['gross_fees']) }}</p>
    </div>
    <div class="bg-white border border-gray-border rounded-2xl p-5">
        <p class="text-xs font-semibold text-navy/50 mb-2">Platform commission</p>
        <p class="text-2xl font-bold text-navy">₱{{ number_format($summary['commission']) }}</p>
    </div>
</div>

{{-- =========================================================
    SEARCH + STATUS FILTER (client-side)
========================================================= --}}
<div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
    <div class="relative flex-1 max-w-xs">
        <svg class="icon w-4 h-4 text-navy/35 absolute left-3.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="riderSearchInput" placeholder="Search rider name…"
               class="w-full border border-gray-border rounded-full pl-9 pr-4 py-2.5 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
    </div>

    <select id="riderStatusFilter" class="border border-gray-border rounded-full px-4 py-2.5 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal w-full sm:w-auto">
        <option value="">All statuses</option>
        <option value="paid">Paid</option>
        <option value="pending">Pending</option>
    </select>

    <p id="riderCountLabel" class="sm:ml-auto text-xs font-semibold text-navy/45">
        Showing {{ count($riders) }} of {{ count($riders) }} riders
    </p>
</div>

<div class="bg-white border border-gray-border rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[680px]">
            <thead>
                <tr class="text-left text-[11px] font-semibold text-navy/45 uppercase tracking-wide bg-gray-bg sticky top-0 z-10">
                    <th class="px-4 py-3 w-8"></th>
                    <th class="px-6 py-3">Rider</th>
                    <th class="px-6 py-3 cursor-pointer select-none hover:text-navy" data-sort="deliveries">
                        Deliveries <span class="sort-arrow"></span>
                    </th>
                    <th class="px-6 py-3 cursor-pointer select-none hover:text-navy" data-sort="on_time">
                        On-time % <span class="sort-arrow"></span>
                    </th>
                    <th class="px-6 py-3 cursor-pointer select-none hover:text-navy" data-sort="earnings">
                        Earnings <span class="sort-arrow"></span>
                    </th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="reportsTableBody" class="divide-y divide-gray-border">
                @forelse ($riders as $rider)
                    @php $riderId = $rider['id'] ?? $loop->index; @endphp
                    <tr class="rider-row cursor-pointer hover:bg-gray-bg/60 transition"
                        data-rider-name="{{ Str::lower($rider['name']) }}"
                        data-rider-status="{{ $rider['status'] }}"
                        data-deliveries="{{ $rider['deliveries'] }}"
                        data-on-time="{{ $rider['on_time'] }}"
                        data-earnings="{{ $rider['earnings'] }}"
                        data-rider="{{ json_encode($rider) }}">
                        <td class="px-4 py-4 text-navy/30">
                            <svg class="icon w-4 h-4 expand-chevron transition-transform duration-200" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                        </td>
                        <td class="px-6 py-4 font-semibold text-navy whitespace-nowrap">{{ $rider['name'] }}</td>
                        <td class="px-6 py-4 text-navy/60">{{ $rider['deliveries'] }}</td>
                        <td class="px-6 py-4 text-navy/60">{{ $rider['on_time'] }}%</td>
                        <td class="px-6 py-4 text-navy/60">₱{{ number_format($rider['earnings']) }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap {{ $rider['status'] === 'paid' ? 'bg-teal-light text-teal-dark' : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($rider['status']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-3">
                                <button type="button" class="view-full-btn inline-flex items-center gap-1.5 text-xs font-semibold text-navy hover:text-teal-dark transition" data-id="{{ $riderId }}">
                                    <svg class="icon w-3.5 h-3.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span class="hidden sm:inline">View full</span>
                                </button>
                                <a href="{{ route('logistics.reports.riders.export.pdf', $riderId) }}"
                                   onclick="event.stopPropagation()"
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-navy hover:text-teal-dark transition">
                                    <svg class="icon w-3.5 h-3.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    <span class="hidden sm:inline">PDF</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr class="rider-detail-row hidden" data-detail-for="{{ $riderId }}">
                        <td colspan="7" class="px-6 py-5 bg-gray-bg/40">
                            @if (!empty($rider['recent_deliveries']))
                                <p class="text-xs font-bold text-navy/60 uppercase tracking-wide mb-3">Recent deliveries — {{ $rider['name'] }}</p>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs min-w-[420px]">
                                        <thead>
                                            <tr class="text-left text-navy/40 font-semibold">
                                                <th class="py-1.5 pr-4">Order ID</th>
                                                <th class="py-1.5 pr-4">Date</th>
                                                <th class="py-1.5 pr-4">Delivery time</th>
                                                <th class="py-1.5 pr-4">Fee</th>
                                                <th class="py-1.5 pr-4">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-border/70">
                                            @foreach (array_slice($rider['recent_deliveries'], 0, 3) as $delivery)
                                                <tr>
                                                    <td class="py-2 pr-4 font-semibold text-navy">{{ $delivery['order_id'] }}</td>
                                                    <td class="py-2 pr-4 text-navy/60">{{ $delivery['date'] }}</td>
                                                    <td class="py-2 pr-4 text-navy/60">{{ $delivery['time'] }}</td>
                                                    <td class="py-2 pr-4 text-navy/60">₱{{ number_format($delivery['fee']) }}</td>
                                                    <td class="py-2 pr-4">
                                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $delivery['status'] === 'delivered' ? 'bg-teal-light text-teal-dark' : 'bg-red-50 text-red-600' }}">
                                                            {{ ucfirst($delivery['status']) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="view-full-btn mt-3 text-xs font-semibold text-teal-dark hover:text-navy transition" data-id="{{ $riderId }}">View full history →</button>
                            @else
                                <p class="text-xs text-navy/40 text-center py-2">No detailed delivery data available yet.</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-14 text-center text-navy/40 text-sm">
                            No riders match this date range.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- =========================================================
    DATE RANGE CALENDAR PICKER MODAL
========================================================= --}}
<div id="rangeModalOverlay" class="modal-overlay hidden items-center justify-center p-0 sm:p-6">
    <div class="modal-panel w-full sm:rounded-2xl sm:max-w-sm" id="rangeModalPanel"></div>
</div>

{{-- =========================================================
    RIDER "VIEW FULL" MODAL
========================================================= --}}
<div id="riderModalOverlay" class="modal-overlay hidden items-center justify-center p-0 sm:p-6">
    <div class="modal-panel w-full sm:rounded-2xl" id="riderModalPanel"></div>
</div>

<style>
    .icon { width: 1em; height: 1em; display: inline-block; vertical-align: -0.125em; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .expand-chevron.rotated { transform: rotate(90deg); }
    th[data-sort] .sort-arrow::after { content: ''; }
    th[data-sort].sorted-asc .sort-arrow::after { content: '▲'; font-size: 8px; margin-left: 4px; }
    th[data-sort].sorted-desc .sort-arrow::after { content: '▼'; font-size: 8px; margin-left: 4px; }

    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.4); backdrop-filter: blur(2px); z-index: 60; }
    .modal-panel { background: #fff; width: 100%; max-height: 92vh; overflow-y: auto; }
    @media (min-width: 640px) { .modal-panel { max-width: 32rem; border-radius: 1.25rem; } }
</style>

<script>
(function () {
    const searchInput = document.getElementById('riderSearchInput');
    const statusFilter = document.getElementById('riderStatusFilter');
    const countLabel = document.getElementById('riderCountLabel');
    const tbody = document.getElementById('reportsTableBody');
    const rows = () => Array.from(tbody.querySelectorAll('.rider-row'));

    // ---- expand / collapse rider detail ----
    tbody.addEventListener('click', (e) => {
        if (e.target.closest('.view-full-btn') || e.target.closest('a')) return;
        const row = e.target.closest('.rider-row');
        if (!row) return;
        const detail = row.nextElementSibling;
        if (!detail || !detail.classList.contains('rider-detail-row')) return;
        detail.classList.toggle('hidden');
        row.querySelector('.expand-chevron').classList.toggle('rotated');
    });

    // ---- search + status filter ----
    function applyFilters() {
        const q = searchInput.value.trim().toLowerCase();
        const status = statusFilter.value;
        let visible = 0, total = 0;

        rows().forEach((row) => {
            total++;
            const matchesName = row.dataset.riderName.includes(q);
            const matchesStatus = !status || row.dataset.riderStatus === status;
            const show = matchesName && matchesStatus;

            row.classList.toggle('hidden', !show);
            const detail = row.nextElementSibling;
            if (detail && detail.classList.contains('rider-detail-row')) detail.classList.add('hidden');
            if (show) visible++;
        });

        countLabel.textContent = `Showing ${visible} of ${total} riders`;
    }
    searchInput.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    applyFilters();

    // ---- column sorting ----
    let sortState = { key: null, dir: 1 };
    document.querySelectorAll('th[data-sort]').forEach((th) => {
        th.addEventListener('click', () => {
            const key = th.dataset.sort;
            sortState.dir = (sortState.key === key) ? sortState.dir * -1 : 1;
            sortState.key = key;

            document.querySelectorAll('th[data-sort]').forEach((h) => h.classList.remove('sorted-asc', 'sorted-desc'));
            th.classList.add(sortState.dir === 1 ? 'sorted-asc' : 'sorted-desc');

            const dataAttr = { deliveries: 'data-deliveries', on_time: 'data-on-time', earnings: 'data-earnings' }[key];
            const sorted = rows().sort((a, b) => (parseFloat(a.getAttribute(dataAttr)) - parseFloat(b.getAttribute(dataAttr))) * sortState.dir);

            sorted.forEach((row) => {
                const detail = row.nextElementSibling;
                tbody.appendChild(row);
                if (detail && detail.classList.contains('rider-detail-row')) tbody.appendChild(detail);
            });
        });
    });

    // ---- "View full" rider modal ----
    const riderModalOverlay = document.getElementById('riderModalOverlay');
    const riderModalPanel = document.getElementById('riderModalPanel');
    const historyRangeLabel = "{{ $from->format('M j') }} – {{ $to->format('M j, Y') }}";

    function closeRiderModal() { riderModalOverlay.classList.add('hidden'); riderModalOverlay.classList.remove('flex'); }
    riderModalOverlay.addEventListener('click', (e) => { if (e.target === riderModalOverlay) closeRiderModal(); });

    function openRiderModal(id) {
        const targetRow = Array.from(tbody.querySelectorAll('.rider-row')).find((r) => {
            const data = JSON.parse(r.dataset.rider);
            return String(data.id ?? '') === String(id);
        });
        if (!targetRow) return;
        const r = JSON.parse(targetRow.dataset.rider);
        const initials = r.name.split(' ').map(p => p[0]).join('');
        const recent = r.recent_deliveries || [];

        riderModalPanel.innerHTML = `
            <div class="flex items-start justify-between px-6 py-5 border-b border-gray-border">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-full bg-navy text-white flex items-center justify-center text-sm font-bold shrink-0">${initials}</div>
                    <div class="min-w-0">
                        <p class="text-base font-bold text-navy truncate">${r.name}</p>
                        <p class="text-xs text-navy/45">${r.vehicle || 'Vehicle not on file'}${r.joined ? ' · Since ' + r.joined : ''}</p>
                    </div>
                </div>
                <button type="button" id="closeRiderModalBtn" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-bg text-navy/50 hover:text-navy transition shrink-0">
                    <svg class="icon w-4 h-4" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5">
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-gray-bg rounded-xl p-3 text-center">
                        <p class="text-[10px] font-semibold text-navy/45">Deliveries</p>
                        <p class="text-lg font-bold text-navy mt-1">${r.deliveries}</p>
                    </div>
                    <div class="bg-gray-bg rounded-xl p-3 text-center">
                        <p class="text-[10px] font-semibold text-navy/45">On-time</p>
                        <p class="text-lg font-bold text-navy mt-1">${r.on_time}%</p>
                    </div>
                    <div class="bg-gray-bg rounded-xl p-3 text-center">
                        <p class="text-[10px] font-semibold text-navy/45">Earnings</p>
                        <p class="text-lg font-bold text-navy mt-1">₱${Number(r.earnings).toLocaleString()}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full ${r.status === 'paid' ? 'bg-teal-light text-teal-dark' : 'bg-amber-50 text-amber-700'}">
                        ${r.status === 'paid' ? 'Paid' : 'Pending payout'}
                    </span>
                    ${r.phone ? `<p class="text-xs text-navy/40">${r.phone}</p>` : ''}
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold text-navy/60 uppercase tracking-wide">Delivery history</p>
                        <span class="text-[10px] font-semibold text-navy/40">${historyRangeLabel}</span>
                    </div>
                    <div class="overflow-x-auto -mx-1">
                        ${recent.length ? `
                            <table class="w-full text-xs min-w-[420px]">
                                <thead><tr class="text-left text-navy/40 font-semibold">
                                    <th class="py-1.5 pr-4">Order ID</th><th class="py-1.5 pr-4">Date</th>
                                    <th class="py-1.5 pr-4">Time</th><th class="py-1.5 pr-4">Fee</th><th class="py-1.5 pr-4">Status</th>
                                </tr></thead>
                                <tbody class="divide-y divide-gray-border">
                                    ${recent.map(d => `
                                        <tr>
                                            <td class="py-2 pr-4 font-semibold text-navy">${d.order_id}</td>
                                            <td class="py-2 pr-4 text-navy/60">${d.date}</td>
                                            <td class="py-2 pr-4 text-navy/60">${d.time}</td>
                                            <td class="py-2 pr-4 text-navy/60">₱${d.fee}</td>
                                            <td class="py-2 pr-4"><span class="text-[10px] font-semibold px-2 py-0.5 rounded-full ${d.status === 'delivered' ? 'bg-teal-light text-teal-dark' : 'bg-red-50 text-red-600'}">${d.status === 'delivered' ? 'Delivered' : 'Delayed'}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        ` : `<p class="text-xs text-navy/40 text-center py-4">No deliveries in this date range.</p>`}
                    </div>
                </div>
            </div>

            <div class="px-6 pb-6">
                <a href="/logistics/reports/riders/${r.id}/export.pdf${window.location.search}"
                   class="w-full inline-flex items-center justify-center gap-2 text-sm font-semibold text-white bg-navy hover:bg-teal px-4 py-2.5 rounded-full transition">
                    <svg class="icon w-3.5 h-3.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Export ${r.name.split(' ')[0]}'s report (PDF)
                </a>
            </div>
        `;
        document.getElementById('closeRiderModalBtn').addEventListener('click', closeRiderModal);
        riderModalOverlay.classList.remove('hidden');
        riderModalOverlay.classList.add('flex');
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.view-full-btn');
        if (!btn) return;
        e.stopPropagation();
        openRiderModal(btn.dataset.id);
    });

    // ---- date range calendar picker ----
    const rangeOverlay = document.getElementById('rangeModalOverlay');
    const rangePanel = document.getElementById('rangeModalPanel');
    const triggerBtn = document.getElementById('dateRangeTriggerBtn');
    const fromInput = document.getElementById('reportFromDate');
    const toInput = document.getElementById('reportToDate');
    const filterForm = document.getElementById('reportFilterForm');

    let pendingFrom = fromInput.value ? new Date(fromInput.value + 'T00:00:00') : null;
    let pendingTo = toInput.value ? new Date(toInput.value + 'T00:00:00') : null;
    let pickerMonth = pendingFrom ? new Date(pendingFrom.getFullYear(), pendingFrom.getMonth(), 1) : new Date();

    function toInputVal(d) { return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`; }
    function sameDay(a, b) { return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate(); }

    function renderPicker() {
        const year = pickerMonth.getFullYear(), month = pickerMonth.getMonth();
        const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        let daysHtml = ['S','M','T','W','T','F','S'].map(d => `<span class="text-[10px] font-semibold text-navy/35">${d}</span>`).join('');
        for (let i = 0; i < firstDay; i++) daysHtml += '<span></span>';

        for (let day = 1; day <= daysInMonth; day++) {
            const d = new Date(year, month, day);
            const isFrom = sameDay(d, pendingFrom);
            const isTo = sameDay(d, pendingTo);
            const inRange = pendingFrom && pendingTo && d > pendingFrom && d < pendingTo;
            let cls = 'text-navy/70 hover:bg-gray-bg';
            if (isFrom || isTo) cls = 'bg-navy text-white font-bold';
            else if (inRange) cls = 'bg-teal-light text-teal-dark';

            daysHtml += `<button type="button" data-day="${day}" class="range-day w-7 h-7 rounded-full text-[11px] font-semibold flex items-center justify-center mx-auto transition ${cls}">${day}</button>`;
        }

        rangePanel.innerHTML = `
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-border">
                <p class="text-base font-bold text-navy">Filter by date</p>
                <button type="button" id="closeRangeModalBtn" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-bg text-navy/50 hover:text-navy transition">
                    <svg class="icon w-4 h-4" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="px-6 pt-4">
                <div class="flex flex-wrap gap-2 mb-4">
                    <button type="button" data-preset="week" class="text-[11px] font-semibold px-3 py-1.5 rounded-full border border-gray-border text-navy/60 hover:border-teal hover:text-teal-dark transition">Last 7 days</button>
                    <button type="button" data-preset="month" class="text-[11px] font-semibold px-3 py-1.5 rounded-full border border-gray-border text-navy/60 hover:border-teal hover:text-teal-dark transition">This month</button>
                    <button type="button" data-preset="all" class="text-[11px] font-semibold px-3 py-1.5 rounded-full border border-gray-border text-navy/60 hover:border-teal hover:text-teal-dark transition">All dates</button>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-bold text-navy">${monthNames[month]} ${year}</p>
                    <div class="flex items-center gap-1">
                        <button type="button" id="rangePrev" class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-bg text-navy/50 hover:text-navy transition">
                            <svg class="icon w-3.5 h-3.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <button type="button" id="rangeNext" class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-bg text-navy/50 hover:text-navy transition">
                            <svg class="icon w-3.5 h-3.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-y-1.5 text-center">${daysHtml}</div>
                <p class="text-[11px] font-semibold text-navy/40 mt-3">${pendingFrom && !pendingTo ? 'Pick an end date…' : 'Click a start date, then an end date.'}</p>
            </div>
            <div class="px-6 py-5 mt-2 flex gap-2.5">
                <button type="button" id="clearRangeBtn" class="flex-1 text-sm font-semibold text-navy/60 hover:text-navy bg-gray-bg hover:bg-gray-border px-4 py-2.5 rounded-full transition">Clear</button>
                <button type="button" id="applyRangeBtn" class="flex-1 text-sm font-semibold text-white bg-teal hover:bg-teal-dark px-4 py-2.5 rounded-full transition">Apply</button>
            </div>
        `;

        document.getElementById('closeRangeModalBtn').addEventListener('click', closePicker);
        document.getElementById('rangePrev').addEventListener('click', () => { pickerMonth.setMonth(pickerMonth.getMonth() - 1); renderPicker(); });
        document.getElementById('rangeNext').addEventListener('click', () => { pickerMonth.setMonth(pickerMonth.getMonth() + 1); renderPicker(); });

        rangePanel.querySelectorAll('.range-day').forEach((btn) => {
            btn.addEventListener('click', () => {
                const d = new Date(year, month, parseInt(btn.dataset.day, 10));
                if (!pendingFrom || (pendingFrom && pendingTo)) {
                    pendingFrom = d; pendingTo = null;
                } else if (d < pendingFrom) {
                    pendingTo = pendingFrom; pendingFrom = d;
                } else {
                    pendingTo = d;
                }
                renderPicker();
            });
        });

        rangePanel.querySelectorAll('[data-preset]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const preset = btn.dataset.preset;
                const anchor = new Date();
                if (preset === 'week') {
                    pendingTo = new Date(anchor); pendingFrom = new Date(anchor); pendingFrom.setDate(pendingFrom.getDate() - 6);
                } else if (preset === 'month') {
                    pendingFrom = new Date(anchor.getFullYear(), anchor.getMonth(), 1);
                    pendingTo = new Date(anchor.getFullYear(), anchor.getMonth() + 1, 0);
                } else {
                    pendingFrom = null; pendingTo = null;
                }
                pickerMonth = new Date(anchor.getFullYear(), anchor.getMonth(), 1);
                renderPicker();
            });
        });

        document.getElementById('clearRangeBtn').addEventListener('click', () => {
            fromInput.value = ''; toInput.value = '';
            filterForm.submit();
        });
        document.getElementById('applyRangeBtn').addEventListener('click', () => {
            if (pendingFrom && !pendingTo) pendingTo = pendingFrom;
            fromInput.value = pendingFrom ? toInputVal(pendingFrom) : '';
            toInput.value = pendingTo ? toInputVal(pendingTo) : '';
            filterForm.submit(); // full reload — controller re-scopes recent_deliveries server-side
        });
    }

    function openPicker() { renderPicker(); rangeOverlay.classList.remove('hidden'); rangeOverlay.classList.add('flex'); }
    function closePicker() { rangeOverlay.classList.add('hidden'); rangeOverlay.classList.remove('flex'); }

    triggerBtn.addEventListener('click', openPicker);
    rangeOverlay.addEventListener('click', (e) => { if (e.target === rangeOverlay) closePicker(); });
})();
</script>

@endsection