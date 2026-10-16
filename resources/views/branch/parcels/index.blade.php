{{-- resources/views/branch/parcels/index.blade.php --}}
@extends('branch.layout.app')

@section('title', 'Branch Parcels')

@section('content')
@php
    // HARDCODED muna. Palitan ng $parcels galing controller kapag may data na.
    $center = $center ?? (object) ['name' => 'Calamba Branch'];

    $allParcels = collect([
        ['tracking' => 'SH-20261011-0001', 'order' => 'ORD-10231', 'receiver' => 'Maria Santos',    'barangay' => 'Brgy. Real, Calamba',          'cod' => 1250.00, 'status' => 'at_branch',        'rider' => null,            'updated' => 'Oct 11, 9:14 AM'],
        ['tracking' => 'SH-20261011-0002', 'order' => 'ORD-10232', 'receiver' => 'Juan dela Cruz', 'barangay' => 'Brgy. Parian, Calamba',        'cod' => 0,       'status' => 'at_branch',        'rider' => null,            'updated' => 'Oct 11, 9:14 AM'],
        ['tracking' => 'SH-20261011-0003', 'order' => 'ORD-10228', 'receiver' => 'Ana Reyes',      'barangay' => 'Brgy. Halang, Calamba',        'cod' => 899.00,  'status' => 'out_for_delivery', 'rider' => 'Rico Mendoza',  'updated' => 'Oct 11, 10:02 AM'],
        ['tracking' => 'SH-20261011-0004', 'order' => 'ORD-10229', 'receiver' => 'Pedro Garcia',   'barangay' => 'Brgy. Canlubang, Calamba',     'cod' => 0,       'status' => 'out_for_delivery', 'rider' => 'Rico Mendoza',  'updated' => 'Oct 11, 10:02 AM'],
        ['tracking' => 'SH-20261010-0017', 'order' => 'ORD-10201', 'receiver' => 'Liza Fernandez', 'barangay' => 'Brgy. Mayapa, Calamba',        'cod' => 450.00,  'status' => 'delivered',        'rider' => 'Jomar Villanueva', 'updated' => 'Oct 10, 4:35 PM'],
        ['tracking' => 'SH-20261010-0018', 'order' => 'ORD-10203', 'receiver' => 'Carlo Navarro',  'barangay' => 'Brgy. Bucal, Calamba',         'cod' => 0,       'status' => 'delivered',        'rider' => 'Jomar Villanueva', 'updated' => 'Oct 10, 5:10 PM'],
        ['tracking' => 'SH-20261010-0021', 'order' => 'ORD-10207', 'receiver' => 'Grace Ramos',    'barangay' => 'Brgy. Pansol, Calamba',        'cod' => 2100.00, 'status' => 'failed',           'rider' => 'Rico Mendoza',  'updated' => 'Oct 10, 3:20 PM', 'note' => 'Walang tao sa address, 1st attempt.'],
        ['tracking' => 'SH-20261009-0009', 'order' => 'ORD-10180', 'receiver' => 'Mark Aquino',    'barangay' => 'Brgy. Turbina, Calamba',       'cod' => 780.00,  'status' => 'returning',        'rider' => null,            'updated' => 'Oct 9, 2:45 PM',  'note' => 'Tinanggihan ng buyer.'],
    ]);

    $activeStatus = request('status', 'all');
    $search = trim((string) request('q', ''));

    $parcels = $allParcels
        ->when($activeStatus !== 'all', fn ($c) => $c->where('status', $activeStatus))
        ->when($search !== '', fn ($c) => $c->filter(fn ($p) =>
            str_contains(strtolower($p['tracking'].' '.$p['order'].' '.$p['receiver']), strtolower($search))))
        ->values();

    $counts = [
        'at_branch'        => $allParcels->where('status', 'at_branch')->count(),
        'out_for_delivery' => $allParcels->where('status', 'out_for_delivery')->count(),
        'failed'           => $allParcels->where('status', 'failed')->count(),
    ];

    $tabs = [
        'all'              => 'All',
        'at_branch'        => 'Nasa branch',
        'out_for_delivery' => 'Out for delivery',
        'delivered'        => 'Delivered',
        'failed'           => 'Failed attempt',
        'returning'        => 'Returning',
    ];

    $statusBadge = [
        'at_branch'        => ['sh-badge-neutral', 'Nasa branch'],
        'out_for_delivery' => ['sh-badge-info', 'Out for delivery'],
        'delivered'        => ['sh-badge-success', 'Delivered'],
        'failed'           => ['sh-badge-warning', 'Failed attempt'],
        'returning'        => ['sh-badge-warning', 'Returning'],
    ];

    $riders = ['Rico Mendoza', 'Jomar Villanueva', 'Ernie Castillo'];
@endphp

<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">{{ $center->name }}</p>
            <h1 class="title">Branch Parcels</h1>
            <p class="description">Lahat ng parcels na hawak ng branch mo. Mag-assign ng rider sa mga nasa branch at bantayan ang mga failed attempt.</p>
        </div>
    </header>

    {{-- Stats --}}
    <section class="grid gap-4 sm:grid-cols-3">
        <div class="sh-stat">
            <p class="sh-stat-label">Nasa branch</p>
            <p class="sh-stat-value">{{ number_format($counts['at_branch']) }}</p>
            <p class="sh-stat-note">Hindi pa na-assign sa rider</p>
        </div>
        <div class="sh-stat">
            <p class="sh-stat-label">Out for delivery</p>
            <p class="sh-stat-value">{{ number_format($counts['out_for_delivery']) }}</p>
            <p class="sh-stat-note">Hawak ng riders ngayon</p>
        </div>
        <div class="sh-stat">
            <p class="sh-stat-label">Failed attempt</p>
            <p class="sh-stat-value">{{ number_format($counts['failed']) }}</p>
            <p class="sh-stat-note">Kailangan ng bagong attempt</p>
        </div>
    </section>

    {{-- Filter tabs --}}
    <nav class="flex flex-wrap gap-2" aria-label="Parcel status filter">
        @foreach ($tabs as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => null]) }}"
               class="{{ $activeStatus === $key ? 'sh-btn' : 'sh-btn-ghost' }}"
               @if ($activeStatus === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    {{-- Search --}}
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="status" value="{{ $activeStatus }}">
        <label class="sh-field min-w-[16rem] flex-1">
            <span class="sh-label">Search</span>
            <input type="search" name="q" value="{{ $search }}" placeholder="Tracking no., order no., o pangalan ng receiver" class="sh-input">
        </label>
        <button class="sh-btn">Search</button>
        @if ($search !== '')
            <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" class="sh-btn-ghost">Clear</a>
        @endif
    </form>

    {{-- Bulk assign --}}
    <form method="POST" action="#" id="assign-form" class="sh-surface sh-card-pad" hidden>
        @csrf
        <div class="sh-toolbar">
            <p class="text-sm font-semibold text-navy"><span data-selected>0</span> parcel(s) napili</p>
            <div class="flex flex-wrap items-end gap-3">
                <label class="sh-field">
                    <span class="sh-label">Rider</span>
                    <select name="rider" required class="sh-input">
                        <option value="">Pumili ng rider</option>
                        @foreach ($riders as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="sh-btn">Assign rider</button>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="sh-surface overflow-x-auto">
        <table class="w-full min-w-[56rem] text-left text-sm">
            <thead>
                <tr class="border-b border-gray-border text-xs text-navy/50">
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" data-check-all aria-label="Select all assignable parcels" class="h-4 w-4 rounded border-gray-border text-teal focus:ring-teal">
                    </th>
                    <th class="px-4 py-3">Tracking</th>
                    <th class="px-4 py-3">Receiver</th>
                    <th class="px-4 py-3">COD</th>
                    <th class="px-4 py-3">Rider</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($parcels as $p)
                    @php
                        $p = (object) $p;
                        [$badgeClass, $badgeLabel] = $statusBadge[$p->status];
                    @endphp
                    <tr class="border-b border-gray-border/60 align-top last:border-0">
                        <td class="px-4 py-3">
                            @if ($p->status === 'at_branch' || $p->status === 'failed')
                                <input type="checkbox" name="parcel_ids[]" value="{{ $p->tracking }}" form="assign-form" data-parcel
                                       aria-label="Select {{ $p->tracking }}"
                                       class="h-4 w-4 rounded border-gray-border text-teal focus:ring-teal">
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-navy">{{ $p->tracking }}</p>
                            <p class="text-xs text-navy/50">{{ $p->order }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-navy">{{ $p->receiver }}</p>
                            <p class="text-xs text-navy/50">{{ $p->barangay }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if ($p->cod > 0)
                                <span class="sh-badge sh-badge-neutral">₱{{ number_format($p->cod, 2) }}</span>
                            @else
                                <span class="text-xs text-navy/40">Prepaid</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-navy/70">{{ $p->rider ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="sh-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            @if (! empty($p->note))
                                <p class="mt-1 max-w-[14rem] text-xs text-navy/50">{{ $p->note }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-navy/50">{{ $p->updated }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10">
                            <div class="sh-empty">
                                <p class="text-sm font-semibold text-navy">Walang parcel na tumugma</p>
                                <p class="mt-1 text-xs text-navy/45">Subukan ang ibang status o search.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('assign-form');
    const boxes = Array.from(document.querySelectorAll('[data-parcel]'));
    const checkAll = document.querySelector('[data-check-all]');
    const selected = form.querySelector('[data-selected]');

    function sync() {
        const n = boxes.filter(function (b) { return b.checked; }).length;
        selected.textContent = n;
        form.hidden = n === 0;
        if (checkAll) {
            checkAll.checked = boxes.length > 0 && n === boxes.length;
            checkAll.indeterminate = n > 0 && n < boxes.length;
        }
    }

    boxes.forEach(function (b) { b.addEventListener('change', sync); });
    checkAll && checkAll.addEventListener('change', function () {
        boxes.forEach(function (b) { b.checked = checkAll.checked; });
        sync();
    });

    sync();
})();
</script>
@endpush