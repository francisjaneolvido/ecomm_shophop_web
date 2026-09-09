{{-- resources/views/logistics/deliveries/board.blade.php --}}

@extends('logistics.layouts')

@section('title', 'Deliveries — ShopHop Logistics')

@section('content')

{{--
    ============================================================
    BACKEND NOTES
    ============================================================
    $columns shape (unchanged, all new keys optional):

        [
            'new' => [
                'label' => 'New',
                'items' => [
                    [
                        'id'       => 'WB-00123',
                        'seller'   => 'Aling Nena Store',
                        'meta'     => '2 items · 3.2kg',
                        'status'   => 'Pending',
                        'rider'    => 'Mark D.',
                        'priority' => 'high',
                        'time'     => '10:42 AM',
                        'address'  => 'Brgy. Bayan, Imus, Cavite',
                        'cod'      => 850.00,
                        'notes'    => 'Fragile — glassware',
                    ],
                ],
            ],
        ]

    $riders — needed for the Assign Rider modal. Hardcoded below via
    $riders ?? [...] fallback so this still renders even before the
    controller passes real data.

    'zone' and 'active_deliveries' are used client-side to suggest the
    best-fit rider for a given waybill (matched against the card's
    'address', with 'meta' parsed for weight to flag heavy packages
    that should prefer a Van). Once ready, pass from controller:

        $riders = Rider::where('status', 'active')->get()->map(fn ($r) => [
            'id'                => $r->id,
            'name'              => $r->name,
            'vehicle_type'      => $r->vehicle_type,
            'zone'              => $r->current_zone,          // e.g. "Imus, Cavite"
            'active_deliveries' => $r->deliveries()->active()->count(),
        ]);

        [
            ['id' => 1, 'name' => 'Mark D.',  'vehicle_type' => 'Motorcycle', 'zone' => 'Imus, Cavite',       'active_deliveries' => 2],
            ['id' => 2, 'name' => 'Liza R.',  'vehicle_type' => 'Motorcycle', 'zone' => 'Bacoor, Cavite',     'active_deliveries' => 1],
            ['id' => 3, 'name' => 'Jomar T.', 'vehicle_type' => 'Van',        'zone' => 'Dasmariñas, Cavite', 'active_deliveries' => 0],
        ]

    Endpoints still needed once backend/DB is ready:
      1. Drag-and-drop column move -> POST /logistics/deliveries/{id}/status
      2. Assign rider submit       -> POST /logistics/deliveries/{id}/assign-rider
         body: { rider_id, notes }
         (see submitAssignRider() below — currently just fakes success,
         updates the card in place, and closes the modal.)
    ============================================================ --}}

@php
    $riders = $riders ?? [
        ['id' => 1, 'name' => 'Mark D.', 'vehicle_type' => 'Motorcycle', 'zone' => 'Imus, Cavite', 'active_deliveries' => 2],
        ['id' => 2, 'name' => 'Liza R.', 'vehicle_type' => 'Motorcycle', 'zone' => 'Bacoor, Cavite', 'active_deliveries' => 1],
        ['id' => 3, 'name' => 'Jomar T.', 'vehicle_type' => 'Van', 'zone' => 'Dasmariñas, Cavite', 'active_deliveries' => 0],
        ['id' => 4, 'name' => 'Bea S.', 'vehicle_type' => 'Motorcycle', 'zone' => 'Imus, Cavite', 'active_deliveries' => 3],
    ];
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-navy text-2xl sm:text-3xl font-bold">Deliveries</h1>
        <p class="text-navy/55 text-sm mt-1">Every pickup request moving through your fleet, live.</p>
    </div>

    <div class="relative w-full sm:w-auto">
        <x-lucide-search class="w-4 h-4 text-navy/35 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input type="text" data-board-search placeholder="Search waybill or seller"
               class="pl-9 pr-4 py-2.5 text-sm border border-gray-border rounded-full w-full sm:w-56 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal">
    </div>
</div>

<div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1" data-board-filters>
    <button type="button" data-filter="all" class="filter-chip shrink-0 text-xs font-semibold px-3.5 py-1.5 rounded-full bg-navy text-white transition">
        All
    </button>
    <button type="button" data-filter="priority" class="filter-chip shrink-0 text-xs font-semibold px-3.5 py-1.5 rounded-full bg-white border border-gray-border text-navy/60 hover:border-navy/30 transition">
        Priority only
    </button>
    <button type="button" data-filter="unassigned" class="filter-chip shrink-0 text-xs font-semibold px-3.5 py-1.5 rounded-full bg-white border border-gray-border text-navy/60 hover:border-navy/30 transition">
        No rider yet
    </button>
    <button type="button" data-filter="failed" class="filter-chip shrink-0 text-xs font-semibold px-3.5 py-1.5 rounded-full bg-white border border-gray-border text-navy/60 hover:border-navy/30 transition">
        Failed
    </button>

    <div class="flex-1"></div>
    <span class="hidden sm:inline text-[11px] text-navy/35 shrink-0" data-last-updated></span>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" data-board>
    @foreach ($columns as $key => $column)
        <div class="bg-gray-bg rounded-2xl p-4" data-column="{{ $key }}">
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-xs font-bold text-navy">{{ $column['label'] }}</p>
                <span class="text-[11px] font-semibold bg-white text-navy/50 rounded-full px-2 py-0.5" data-column-count>
                    {{ count($column['items']) }}
                </span>
            </div>

            <div class="space-y-2.5 min-h-16" data-column-list data-dropzone="{{ $key }}">
                @forelse ($column['items'] as $item)
                    <div class="bg-white border border-gray-border rounded-xl p-3.5 cursor-pointer hover:border-teal/40 transition"
                         data-board-card
                         draggable="true"
                         data-id="{{ $item['id'] }}"
                         data-search-text="{{ strtolower($item['id'] . ' ' . $item['seller']) }}"
                         data-priority="{{ $item['priority'] ?? 'normal' }}"
                         data-status="{{ $item['status'] ?? 'Unassigned' }}"
                         data-rider="{{ $item['rider'] ?? '' }}"
                         data-seller="{{ $item['seller'] }}"
                         data-meta="{{ $item['meta'] }}"
                         data-address="{{ $item['address'] ?? '' }}"
                         data-cod="{{ $item['cod'] ?? '' }}"
                         data-notes="{{ $item['notes'] ?? '' }}"
                         data-time="{{ $item['time'] ?? '' }}">

                        <div class="flex items-start justify-between gap-2 mb-1">
                            <button type="button"
                                    class="text-[11px] font-semibold text-navy/40 hover:text-teal-dark transition flex items-center gap-1"
                                    data-copy-waybill="{{ $item['id'] }}" title="Copy waybill no.">
                                #{{ $item['id'] }}
                            </button>

                            @if (!empty($item['priority']) && $item['priority'] === 'high')
                                <span class="text-[10px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded-full flex items-center gap-1 shrink-0">
                                    <x-lucide-flame class="w-2.5 h-2.5" />
                                    Priority
                                </span>
                            @endif
                        </div>

                        <p class="text-sm font-semibold text-navy mb-2">{{ $item['seller'] }}</p>

                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-xs text-navy/50">{{ $item['meta'] }}</span>

                            @if (isset($item['status']))
                                <span class="text-[10px] font-semibold px-2 py-1 rounded-full whitespace-nowrap
                                    {{ str_contains($item['status'], 'Failed')
                                        ? 'bg-red-50 text-red-600'
                                        : (str_contains($item['status'], 'Delivered')
                                            ? 'bg-teal-light text-teal-dark'
                                            : 'bg-amber-50 text-amber-700') }}"
                                      data-status-badge>
                                    {{ $item['status'] }}
                                </span>
                            @else
                                <span class="text-[10px] font-semibold px-2 py-1 rounded-full bg-teal-light text-teal-dark whitespace-nowrap" data-status-badge>
                                    Unassigned
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-border/70">
                            <span class="flex items-center gap-1.5 text-[11px] text-navy/60" data-rider-display>
                                @if (!empty($item['rider']))
                                    <span class="w-5 h-5 rounded-full bg-navy/10 text-navy text-[9px] font-bold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($item['rider'], 0, 1)) }}
                                    </span>
                                    {{ $item['rider'] }}
                                @else
                                    <span class="text-navy/30">No rider yet</span>
                                @endif
                            </span>

                            @if (!empty($item['time']))
                                <span class="text-[10px] text-navy/35">{{ $item['time'] }}</span>
                            @endif
                        </div>

                        @if ($key === 'new')
                            <button type="button" data-assign-rider="{{ $item['id'] }}"
                                    class="w-full mt-3 text-xs font-semibold bg-navy hover:bg-teal text-white py-2 rounded-full transition">
                                Assign rider
                            </button>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-navy/35 text-center py-6" data-column-empty>Nothing here.</p>
                @endforelse

                <p class="hidden text-xs text-navy/35 text-center py-6" data-no-results>
                    No matches in this column.
                </p>
            </div>
        </div>
    @endforeach
</div>

{{-- ============================================================
    DETAIL MODAL — same visual language as register.blade.php
    (rounded-2xl white card, x-lucide icons, navy/teal buttons)
============================================================ --}}
<div class="hidden fixed inset-0 z-50 items-center justify-center p-4" data-modal-backdrop="detail">
    <div class="absolute inset-0 bg-navy/40" data-modal-close="detail"></div>

    <div class="relative bg-white rounded-2xl w-full max-w-md p-6 shadow-xl">
        <button type="button" data-modal-close="detail" class="absolute top-4 right-4 text-navy/40 hover:text-navy transition">
            <x-lucide-x class="w-5 h-5" />
        </button>

        <p class="text-[11px] font-semibold text-navy/40 mb-1" data-modal-id>—</p>
        <h3 class="text-navy font-bold text-lg mb-4" data-modal-seller>—</h3>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-navy/55">Status</span><span class="font-semibold text-navy" data-modal-status>—</span></div>
            <div class="flex justify-between"><span class="text-navy/55">Package</span><span class="font-semibold text-navy" data-modal-meta>—</span></div>
            <div class="flex justify-between"><span class="text-navy/55">Rider</span><span class="font-semibold text-navy" data-modal-rider>—</span></div>
            <div class="flex justify-between"><span class="text-navy/55">COD amount</span><span class="font-semibold text-navy" data-modal-cod>—</span></div>
            <div class="pt-2 border-t border-gray-border">
                <span class="text-navy/55 block mb-1">Delivery address</span>
                <span class="font-semibold text-navy block" data-modal-address>—</span>
            </div>
            <div>
                <span class="text-navy/55 block mb-1">Notes</span>
                <span class="text-navy/70 block" data-modal-notes>—</span>
            </div>
        </div>

        <div class="flex gap-2 mt-6">
            <button type="button" data-modal-copy class="flex-1 border-2 border-navy text-navy hover:bg-navy hover:text-white text-xs font-semibold py-2.5 rounded-full transition">
                Copy waybill no.
            </button>
            <button type="button" data-modal-close="detail" class="flex-1 bg-teal hover:bg-teal-dark text-white text-xs font-semibold py-2.5 rounded-full transition">
                Close
            </button>
        </div>
    </div>
</div>

{{-- ============================================================
    ASSIGN RIDER MODAL — mirrors register.blade.php's field style:
    same label classes, same input classes, same error-text pattern
    (data-client-error), same button pair at the bottom.

    Adds:
      - "Delivering to" context box (address + package) so the picker
        isn't blind to what's being moved.
      - Rider list (instead of a plain <select>) showing each rider's
        vehicle, zone, and current load, sorted with the best-fit
        rider first. A rider is flagged:
          "Nearby"    — zone matches the delivery's city/province
          "Good for heavy load" — Van rider when package looks heavy
        The top-ranked rider (when it actually scores above the
        others) gets a "Suggested" badge. This is a client-side
        heuristic only — final call is always the admin's.
============================================================ --}}
<div class="hidden fixed inset-0 z-50 items-center justify-center p-4" data-modal-backdrop="assign">
    <div class="absolute inset-0 bg-navy/40" data-modal-close="assign"></div>

    <div class="relative bg-white rounded-2xl w-full max-w-md p-6 shadow-xl">
        <button type="button" data-modal-close="assign" class="absolute top-4 right-4 text-navy/40 hover:text-navy transition">
            <x-lucide-x class="w-5 h-5" />
        </button>

        <div class="flex items-center gap-2 mb-1">
            <div class="w-9 h-9 rounded-xl bg-teal-light flex items-center justify-center text-teal-dark shrink-0">
                <x-lucide-user-plus class="w-4 h-4" />
            </div>
            <div>
                <p class="text-navy font-bold text-sm">Assign a rider</p>
                <p class="text-[11px] text-navy/45">Waybill <span class="font-semibold" data-assign-waybill>—</span></p>
            </div>
        </div>

        <div class="mt-4 bg-gray-bg rounded-xl px-3.5 py-3 flex items-start gap-2.5">
            <x-lucide-map-pin class="w-4 h-4 text-navy/40 shrink-0 mt-0.5" />
            <div class="text-xs min-w-0">
                <p class="text-navy font-semibold" data-assign-address>—</p>
                <p class="text-navy/45 mt-0.5" data-assign-meta>—</p>
            </div>
        </div>

        <div class="mt-4 space-y-4">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-navy">
                        Rider <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[10px] text-navy/40">Best fit shown first</span>
                </div>

                <div class="space-y-2 max-h-56 overflow-y-auto pr-0.5" data-assign-rider-list>
                    {{-- rider option cards are rendered here on open, see renderRiderList() --}}
                </div>

                <p class="hidden text-xs text-red-500 mt-1" data-client-error="assign_rider">Please select a rider.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-navy mb-1.5">Note for the rider (optional)</label>
                <textarea data-assign-notes rows="3" placeholder="e.g. Call the seller before pickup"
                          class="w-full border border-gray-border rounded-xl px-4 py-3 text-sm text-navy placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal resize-none"></textarea>
            </div>

            <p class="hidden text-xs text-red-500 font-medium flex items-center gap-1.5" data-assign-error>
                <x-lucide-alert-triangle class="w-3.5 h-3.5" />
                Something went wrong assigning the rider. Please try again.
            </p>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="button" data-modal-close="assign"
                    class="flex-1 border-2 border-navy text-navy hover:bg-navy hover:text-white text-xs font-semibold py-2.5 rounded-full transition">
                Cancel
            </button>
            <button type="button" data-assign-submit
                    class="flex-1 bg-teal hover:bg-teal-dark text-white text-xs font-semibold py-2.5 rounded-full transition flex items-center justify-center gap-2">
                <span data-assign-submit-label>Confirm assignment</span>
            </button>
        </div>
    </div>
</div>

<div class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-navy text-white text-xs font-semibold px-4 py-2.5 rounded-full shadow-lg" data-toast></div>

<script>
    const RIDERS = @json($riders);

    document.addEventListener('DOMContentLoaded', function () {
        const board = document.querySelector('[data-board]');
        if (!board) return;

        // ---- Toast helper ----
        const toastEl = document.querySelector('[data-toast]');
        let toastTimeout = null;
        function showToast(message) {
            if (!toastEl) return;
            toastEl.textContent = message;
            toastEl.classList.remove('hidden');
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(function () { toastEl.classList.add('hidden'); }, 1800);
        }

        // ---- Last updated stamp ----
        const lastUpdatedEl = document.querySelector('[data-last-updated]');
        function stampLastUpdated() {
            if (!lastUpdatedEl) return;
            const now = new Date();
            lastUpdatedEl.textContent = 'Updated ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        stampLastUpdated();

        // ---- Search + filter chips ----
        const searchInput = document.querySelector('[data-board-search]');
        let activeFilter = 'all';

        function cardMatchesFilter(card) {
            switch (activeFilter) {
                case 'priority': return card.dataset.priority === 'high';
                case 'unassigned': return !card.dataset.rider;
                case 'failed': return card.dataset.status.includes('Failed');
                default: return true;
            }
        }

        function applyFilters() {
            const q = searchInput ? searchInput.value.trim().toLowerCase() : '';

            board.querySelectorAll('[data-column]').forEach(function (column) {
                const cards = column.querySelectorAll('[data-board-card]');
                let visibleCount = 0;

                cards.forEach(function (card) {
                    const visible = (!q || card.dataset.searchText.includes(q)) && cardMatchesFilter(card);
                    card.classList.toggle('hidden', !visible);
                    if (visible) visibleCount++;
                });

                const emptyEl = column.querySelector('[data-column-empty]');
                const noResultsEl = column.querySelector('[data-no-results]');
                const hasAnyCards = cards.length > 0;

                if (emptyEl) emptyEl.classList.toggle('hidden', hasAnyCards);
                if (noResultsEl) noResultsEl.classList.toggle('hidden', !(hasAnyCards && visibleCount === 0));

                const countEl = column.querySelector('[data-column-count]');
                if (countEl) countEl.textContent = (q || activeFilter !== 'all') ? visibleCount : cards.length;
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { searchInput.value = ''; applyFilters(); searchInput.blur(); }
            });
        }

        document.querySelectorAll('[data-filter]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                activeFilter = chip.dataset.filter;
                document.querySelectorAll('.filter-chip').forEach(function (c) {
                    c.classList.remove('bg-navy', 'text-white');
                    c.classList.add('bg-white', 'border', 'border-gray-border', 'text-navy/60');
                });
                chip.classList.remove('bg-white', 'border', 'border-gray-border', 'text-navy/60');
                chip.classList.add('bg-navy', 'text-white');
                applyFilters();
            });
        });

        // ---- Copy waybill ----
        function copyText(value) {
            if (!navigator.clipboard || !value) return;
            navigator.clipboard.writeText(value).then(function () {
                showToast('Copied ' + value);
            }).catch(function () {});
        }

        // ---- Detail modal ----
        function openDetailModal(card) {
            document.querySelector('[data-modal-id]').textContent = '#' + card.dataset.id;
            document.querySelector('[data-modal-seller]').textContent = card.dataset.seller;
            document.querySelector('[data-modal-status]').textContent = card.dataset.status || '—';
            document.querySelector('[data-modal-meta]').textContent = card.dataset.meta || '—';
            document.querySelector('[data-modal-rider]').textContent = card.dataset.rider || 'No rider yet';
            document.querySelector('[data-modal-address]').textContent = card.dataset.address || '—';
            document.querySelector('[data-modal-notes]').textContent = card.dataset.notes || 'No notes.';

            const cod = card.dataset.cod;
            document.querySelector('[data-modal-cod]').textContent = cod ? ('₱' + Number(cod).toLocaleString('en-PH', { minimumFractionDigits: 2 })) : '—';

            const modalCopyBtn = document.querySelector('[data-modal-copy]');
            if (modalCopyBtn) modalCopyBtn.dataset.value = card.dataset.id;

            openModal('detail');
        }

        // ---- Generic modal open/close (works for both modals) ----
        function openModal(name) {
            const backdrop = document.querySelector('[data-modal-backdrop="' + name + '"]');
            if (!backdrop) return;
            backdrop.classList.remove('hidden');
            backdrop.classList.add('flex');
        }
        function closeModal(name) {
            const backdrop = document.querySelector('[data-modal-backdrop="' + name + '"]');
            if (!backdrop) return;
            backdrop.classList.add('hidden');
            backdrop.classList.remove('flex');
        }

        document.querySelectorAll('[data-modal-close]').forEach(function (el) {
            el.addEventListener('click', function () { closeModal(el.dataset.modalClose); });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            closeModal('detail');
            closeModal('assign');
        });

        const modalCopyBtn = document.querySelector('[data-modal-copy]');
        if (modalCopyBtn) {
            modalCopyBtn.addEventListener('click', function () { copyText(modalCopyBtn.dataset.value || ''); });
        }

        // ---- Assign Rider modal ----
        const assignWaybillEl = document.querySelector('[data-assign-waybill]');
        const assignAddressEl = document.querySelector('[data-assign-address]');
        const assignMetaEl = document.querySelector('[data-assign-meta]');
        const assignRiderList = document.querySelector('[data-assign-rider-list]');
        const assignNotes = document.querySelector('[data-assign-notes]');
        const assignErrorField = document.querySelector('[data-client-error="assign_rider"]');
        const assignErrorGeneral = document.querySelector('[data-assign-error]');
        const assignSubmitBtn = document.querySelector('[data-assign-submit]');
        const assignSubmitLabel = document.querySelector('[data-assign-submit-label]');
        let assignTargetCard = null;

        // City/province the package is headed to, e.g. "Brgy. Bayan, Imus, Cavite" -> "Imus, Cavite"
        function extractCityProvince(address) {
            if (!address) return '';
            const parts = address.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
            if (parts.length >= 2) return parts.slice(-2).join(', ');
            return parts[0] || '';
        }

        // Pull a weight figure out of the package meta string, e.g. "2 items · 3.2kg" -> 3.2
        function extractWeightKg(meta) {
            if (!meta) return null;
            const match = meta.match(/([\d.]+)\s*kg/i);
            return match ? parseFloat(match[1]) : null;
        }

        function zoneMatches(riderZone, cityProvince) {
            if (!riderZone || !cityProvince) return false;
            const a = riderZone.toLowerCase();
            const b = cityProvince.toLowerCase();
            return a === b || a.includes(b) || b.includes(a);
        }

        function rankRiders(cityProvince, weightKg) {
            const heavyPackage = weightKg !== null && weightKg >= 5;

            const ranked = RIDERS.map(function (rider) {
                const nearby = zoneMatches(rider.zone || '', cityProvince);
                const goodForLoad = heavyPackage && (rider.vehicle_type || '').toLowerCase() === 'van';
                const load = rider.active_deliveries || 0;

                let score = 0;
                if (nearby) score += 2;
                if (goodForLoad) score += 2;
                score -= load * 0.1;

                return Object.assign({}, rider, { nearby: nearby, goodForLoad: goodForLoad, load: load, score: score });
            });

            ranked.sort(function (a, b) {
                if (b.score !== a.score) return b.score - a.score;
                if (a.load !== b.load) return a.load - b.load;
                return a.name.localeCompare(b.name);
            });

            // Only call out a "Suggested" pick if it actually stands out from the rest.
            if (ranked.length && ranked[0].score > 0 && (ranked.length === 1 || ranked[0].score > ranked[1].score)) {
                ranked[0].suggested = true;
            }

            return ranked;
        }

        function riderInitial(name) {
            return (name || '?').charAt(0).toUpperCase();
        }

        function renderRiderList(rankedRiders, selectedId) {
            if (!assignRiderList) return;

            assignRiderList.innerHTML = rankedRiders.map(function (rider) {
                const checked = String(rider.id) === String(selectedId) ? 'checked' : '';
                const selectedClasses = checked ? 'border-teal bg-teal-light/40' : 'border-gray-border';

                const chips = [];
                if (rider.suggested) {
                    chips.push(
                        '<span class="text-[10px] font-semibold text-teal-dark bg-teal-light px-1.5 py-0.5 rounded-full inline-flex items-center gap-1">' +
                            '<svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.287 1.288L3 12l5.8 1.9a2 2 0 0 1 1.288 1.287L12 21l1.9-5.8a2 2 0 0 1 1.287-1.288L21 12l-5.8-1.9a2 2 0 0 1-1.288-1.287Z"/></svg>' +
                            'Suggested' +
                        '</span>'
                    );
                }
                if (rider.nearby) {
                    chips.push('<span class="text-[10px] font-semibold text-navy/60 bg-white border border-gray-border px-1.5 py-0.5 rounded-full">Nearby</span>');
                }
                if (rider.goodForLoad) {
                    chips.push('<span class="text-[10px] font-semibold text-navy/60 bg-white border border-gray-border px-1.5 py-0.5 rounded-full">Fits heavy load</span>');
                }

                return (
                    '<label data-rider-option data-rider-id="' + rider.id + '" ' +
                    'class="flex items-start gap-3 border rounded-xl px-3 py-2.5 cursor-pointer transition hover:border-teal/40 ' + selectedClasses + '">' +
                        '<input type="radio" name="assign_rider" value="' + rider.id + '" ' + checked + ' ' +
                            'data-rider-name="' + rider.name + '" data-assign-rider-radio class="sr-only">' +
                        '<span class="w-8 h-8 rounded-full bg-navy/10 text-navy text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">' +
                            riderInitial(rider.name) +
                        '</span>' +
                        '<span class="flex-1 min-w-0">' +
                            '<span class="flex items-center gap-1.5 flex-wrap">' +
                                '<span class="text-sm font-semibold text-navy">' + rider.name + '</span>' +
                                chips.join('') +
                            '</span>' +
                            '<span class="flex items-center gap-1.5 mt-1 text-[11px] text-navy/45">' +
                                '<span>' + (rider.vehicle_type || 'Rider') + '</span>' +
                                '<span>·</span>' +
                                '<span class="truncate">' + (rider.zone || 'Zone unknown') + '</span>' +
                                '<span class="ml-auto shrink-0">' + rider.load + ' active</span>' +
                            '</span>' +
                        '</span>' +
                    '</label>'
                );
            }).join('');
        }

        if (assignRiderList) {
            assignRiderList.addEventListener('change', function (e) {
                if (!e.target.matches('[data-assign-rider-radio]')) return;

                assignRiderList.querySelectorAll('[data-rider-option]').forEach(function (label) {
                    const isSelected = label.querySelector('[data-assign-rider-radio]').checked;
                    label.classList.toggle('border-teal', isSelected);
                    label.classList.toggle('bg-teal-light/40', isSelected);
                    label.classList.toggle('border-gray-border', !isSelected);
                });

                if (assignErrorField) assignErrorField.classList.add('hidden');
            });
        }

        function openAssignModal(card) {
            assignTargetCard = card;
            if (assignWaybillEl) assignWaybillEl.textContent = '#' + card.dataset.id;
            if (assignAddressEl) assignAddressEl.textContent = card.dataset.address || 'No address on file';
            if (assignMetaEl) assignMetaEl.textContent = card.dataset.meta || '—';
            if (assignNotes) assignNotes.value = '';
            if (assignErrorField) assignErrorField.classList.add('hidden');
            if (assignErrorGeneral) assignErrorGeneral.classList.add('hidden');

            const cityProvince = extractCityProvince(card.dataset.address || '');
            const weightKg = extractWeightKg(card.dataset.meta || '');
            renderRiderList(rankRiders(cityProvince, weightKg), null);

            openModal('assign');
        }

        if (assignSubmitBtn) {
            assignSubmitBtn.addEventListener('click', function () {
                const checkedRadio = assignRiderList ? assignRiderList.querySelector('[data-assign-rider-radio]:checked') : null;

                if (!checkedRadio) {
                    if (assignErrorField) assignErrorField.classList.remove('hidden');
                    return;
                }
                if (assignErrorField) assignErrorField.classList.add('hidden');
                if (assignErrorGeneral) assignErrorGeneral.classList.add('hidden');

                submitAssignRider(checkedRadio.value, checkedRadio.dataset.riderName);
            });
        }

        function submitAssignRider(riderId, riderName) {
            if (!assignTargetCard) return;

            const notes = assignNotes ? assignNotes.value.trim() : '';

            assignSubmitBtn.disabled = true;
            if (assignSubmitLabel) assignSubmitLabel.textContent = 'Assigning…';

            // TODO: replace this fake delay + always-succeed block with a real call:
            //
            // fetch('/logistics/deliveries/' + assignTargetCard.dataset.id + '/assign-rider', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            //     },
            //     body: JSON.stringify({ rider_id: riderId, notes: notes }),
            // })
            // .then(res => { if (!res.ok) throw new Error('Assign failed'); return res.json(); })
            // .then(data => { applyAssignmentToCard(assignTargetCard, data.rider_name || riderName); closeModal('assign'); showToast('Rider assigned'); })
            // .catch(() => { assignErrorGeneral.classList.remove('hidden'); })
            // .finally(() => { assignSubmitBtn.disabled = false; assignSubmitLabel.textContent = 'Confirm assignment'; });

            setTimeout(function () {
                applyAssignmentToCard(assignTargetCard, riderName);
                assignSubmitBtn.disabled = false;
                if (assignSubmitLabel) assignSubmitLabel.textContent = 'Confirm assignment';
                closeModal('assign');
                showToast('Rider assigned — ' + riderName);
            }, 500);
        }

        function applyAssignmentToCard(card, riderName) {
            card.dataset.rider = riderName;

            const riderDisplay = card.querySelector('[data-rider-display]');
            if (riderDisplay) {
                riderDisplay.innerHTML =
                    '<span class="w-5 h-5 rounded-full bg-navy/10 text-navy text-[9px] font-bold flex items-center justify-center shrink-0">' +
                        riderName.charAt(0).toUpperCase() +
                    '</span>' + riderName;
            }

            applyFilters();
        }

        // ---- Card + button clicks (delegated) ----
        board.addEventListener('click', function (e) {
            const copyBtn = e.target.closest('[data-copy-waybill]');
            if (copyBtn) { e.stopPropagation(); copyText(copyBtn.dataset.copyWaybill); return; }

            const assignBtn = e.target.closest('[data-assign-rider]');
            if (assignBtn) {
                e.stopPropagation();
                const card = assignBtn.closest('[data-board-card]');
                if (card) openAssignModal(card);
                return;
            }

            const card = e.target.closest('[data-board-card]');
            if (card) openDetailModal(card);
        });

        // ---- Drag-and-drop between columns ----
        let draggedCard = null;

        board.addEventListener('dragstart', function (e) {
            const card = e.target.closest('[data-board-card]');
            if (!card) return;
            draggedCard = card;
            e.dataTransfer.effectAllowed = 'move';
            setTimeout(function () { card.classList.add('opacity-40'); }, 0);
        });

        board.addEventListener('dragend', function (e) {
            const card = e.target.closest('[data-board-card]');
            if (card) card.classList.remove('opacity-40');
            draggedCard = null;
        });

        board.querySelectorAll('[data-dropzone]').forEach(function (zone) {
            zone.addEventListener('dragover', function (e) {
                e.preventDefault();
                zone.classList.add('ring-2', 'ring-teal/40', 'rounded-xl');
            });
            zone.addEventListener('dragleave', function () {
                zone.classList.remove('ring-2', 'ring-teal/40', 'rounded-xl');
            });
            zone.addEventListener('drop', function (e) {
                e.preventDefault();
                zone.classList.remove('ring-2', 'ring-teal/40', 'rounded-xl');
                if (!draggedCard) return;

                const fromColumnKey = draggedCard.closest('[data-column]').dataset.column;
                const toColumnKey = zone.dataset.dropzone;

                const emptyEl = zone.querySelector('[data-column-empty]');
                if (emptyEl) emptyEl.classList.add('hidden');

                zone.appendChild(draggedCard);
                updateColumnCounts();

                if (fromColumnKey !== toColumnKey) {
                    handleCardMoved(draggedCard.dataset.id, fromColumnKey, toColumnKey);
                }
            });
        });

        function updateColumnCounts() {
            board.querySelectorAll('[data-column]').forEach(function (column) {
                const cards = column.querySelectorAll('[data-board-card]');
                const countEl = column.querySelector('[data-column-count]');
                if (countEl) countEl.textContent = cards.length;

                const emptyEl = column.querySelector('[data-column-empty]');
                if (emptyEl) emptyEl.classList.toggle('hidden', cards.length > 0);
            });
        }

        function handleCardMoved(waybillId, fromKey, toKey) {
            // TODO: persist via fetch('/logistics/deliveries/' + waybillId + '/status', ...)
            showToast('Moved #' + waybillId + ' → ' + toKey + ' (not saved yet — backend pending)');
        }

        applyFilters();
    });
</script>

@endsection