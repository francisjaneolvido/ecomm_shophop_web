{{-- resources/views/logistics/reports.blade.php --}}

@extends('layouts.logistics')

@section('title', 'Reports — ShopHop Logistics')

@section('content')

{{--
    ============================================================
    BACKEND NOTES for Yesel (things the controller/routes need)
    ============================================================
    1. Each entry in $riders should now also include:
       - 'id'                => the rider's ID (used for the per-rider PDF export link)
       - 'recent_deliveries'  => array of the rider's recent orders, e.g.:
            [
                ['order_id' => 'SH-10231', 'date' => '2026-08-20', 'status' => 'delivered', 'time' => '38 min', 'fee' => 85],
                ...
            ]
         If this key is missing the row still works — it just shows
         "No detailed delivery data available yet" when expanded.

    2. Routes needed:
       - route('logistics.reports.export.pdf', request()->query())
            -> replaces the old CSV export, streams/downloads a PDF
               (e.g. using barryvdh/laravel-dompdf) of the whole report.
       - route('logistics.reports.riders.export.pdf', $rider['id'])
            -> streams/downloads a PDF for a single rider covering
               the same date range as the current filter.

    3. Everything else ($from, $to, $summary, $riders) stays the same.
    ============================================================
--}}

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-navy text-2xl sm:text-3xl font-bold">Delivery &amp; earnings report</h1>
        <p class="text-navy/55 text-sm mt-1">Filter by date range, then export for your records.</p>
    </div>
</div>

<form method="GET" action="{{ route('logistics.reports.index') }}" class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 mb-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-navy/55">
        <span>From</span>
        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
               class="border border-gray-border rounded-lg px-3 py-2 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal w-full sm:w-auto">
        <span>To</span>
        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
               class="border border-gray-border rounded-lg px-3 py-2 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal w-full sm:w-auto">
    </div>
    <div class="flex flex-wrap items-center gap-3 sm:contents">
        <button type="submit" class="text-xs font-semibold bg-gray-bg hover:bg-navy hover:text-white text-navy px-4 py-2.5 rounded-full transition">
            Apply
        </button>
        <a href="{{ route('logistics.reports.export.pdf', request()->query()) }}"
           class="sm:ml-auto inline-flex items-center justify-center gap-2 text-xs font-semibold bg-navy hover:bg-teal text-white px-4 py-2.5 rounded-full transition">
            <x-lucide-file-text class="w-3.5 h-3.5" />
            Export PDF
        </a>
    </div>
</form>

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
        <x-lucide-search class="w-4 h-4 text-navy/35 absolute left-3.5 top-1/2 -translate-y-1/2" />
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
        <table class="w-full text-sm">
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
                    <th class="px-6 py-3 text-right">Export</th>
                </tr>
            </thead>
            <tbody id="reportsTableBody" class="divide-y divide-gray-border">
                @forelse ($riders as $rider)
                    <tr class="rider-row cursor-pointer hover:bg-gray-bg/60 transition"
                        data-rider-name="{{ Str::lower($rider['name']) }}"
                        data-rider-status="{{ $rider['status'] }}"
                        data-deliveries="{{ $rider['deliveries'] }}"
                        data-on-time="{{ $rider['on_time'] }}"
                        data-earnings="{{ $rider['earnings'] }}">
                        <td class="px-4 py-4 text-navy/30">
                            <x-lucide-chevron-right class="w-4 h-4 expand-chevron transition-transform duration-200" />
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
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('logistics.reports.riders.export.pdf', $rider['id'] ?? $loop->index) }}"
                               onclick="event.stopPropagation()"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-navy hover:text-teal-dark transition">
                                <x-lucide-download class="w-3.5 h-3.5" />
                                PDF
                            </a>
                        </td>
                    </tr>
                    <tr class="rider-detail-row hidden" data-detail-for="{{ $rider['id'] ?? $loop->index }}">
                        <td colspan="7" class="px-6 py-5 bg-gray-bg/40">
                            @if (!empty($rider['recent_deliveries']))
                                <p class="text-xs font-bold text-navy/60 uppercase tracking-wide mb-3">Recent deliveries — {{ $rider['name'] }}</p>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs">
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
                                            @foreach ($rider['recent_deliveries'] as $delivery)
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

<style>
    .expand-chevron.rotated { transform: rotate(90deg); }
    th[data-sort] .sort-arrow::after { content: ''; }
    th[data-sort].sorted-asc .sort-arrow::after { content: '▲'; font-size: 8px; margin-left: 4px; }
    th[data-sort].sorted-desc .sort-arrow::after { content: '▼'; font-size: 8px; margin-left: 4px; }
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
        const row = e.target.closest('.rider-row');
        if (!row) return;
        const key = row.querySelector('[data-detail-for]');
        const detail = tbody.querySelector(`.rider-detail-row[data-detail-for="${row.nextElementSibling?.dataset.detailFor}"]`) || row.nextElementSibling;
        if (!detail || !detail.classList.contains('rider-detail-row')) return;

        detail.classList.toggle('hidden');
        row.querySelector('.expand-chevron').classList.toggle('rotated');
    });

    // ---- search + status filter ----
    function applyFilters() {
        const q = searchInput.value.trim().toLowerCase();
        const status = statusFilter.value;
        let visible = 0;
        let total = 0;

        rows().forEach((row) => {
            total++;
            const matchesName = row.dataset.riderName.includes(q);
            const matchesStatus = !status || row.dataset.riderStatus === status;
            const show = matchesName && matchesStatus;

            row.classList.toggle('hidden', !show);
            const detail = row.nextElementSibling;
            if (detail && detail.classList.contains('rider-detail-row')) {
                detail.classList.add('hidden');
                if (!show) detail.classList.add('hidden');
            }
            if (show) visible++;
        });

        countLabel.textContent = `Showing ${visible} of ${total} riders`;
    }

    searchInput.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    // ---- column sorting ----
    let sortState = { key: null, dir: 1 };

    document.querySelectorAll('th[data-sort]').forEach((th) => {
        th.addEventListener('click', () => {
            const key = th.dataset.sort;
            sortState.dir = (sortState.key === key) ? sortState.dir * -1 : 1;
            sortState.key = key;

            document.querySelectorAll('th[data-sort]').forEach((h) => h.classList.remove('sorted-asc', 'sorted-desc'));
            th.classList.add(sortState.dir === 1 ? 'sorted-asc' : 'sorted-desc');

            const attr = { deliveries: 'deliveries', on_time: 'onTime', earnings: 'earnings' }[key];
            const dataAttr = { deliveries: 'data-deliveries', on_time: 'data-on-time', earnings: 'data-earnings' }[key];

            const sorted = rows().sort((a, b) => {
                const av = parseFloat(a.getAttribute(dataAttr));
                const bv = parseFloat(b.getAttribute(dataAttr));
                return (av - bv) * sortState.dir;
            });

            sorted.forEach((row) => {
                const detail = row.nextElementSibling;
                tbody.appendChild(row);
                if (detail && detail.classList.contains('rider-detail-row')) tbody.appendChild(detail);
            });
        });
    });
})();
</script>

@endsection