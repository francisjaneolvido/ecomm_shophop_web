{{-- resources/views/logistics/dashboard.blade.php --}}

@extends('logistics.layouts.logistics')

@section('title', 'Dashboard — ShopHop Logistics')

@section('content')

{{--
    ============================================================
    BACKEND NOTES for Yesel (things the controller needs to pass)
    ============================================================
    1. $calendarMonth   -> a Carbon instance for the month being viewed.
                           Read from request('month', now()->format('Y-m')).
                           Example:
                           $calendarMonth = \Carbon\Carbon::createFromFormat(
                               'Y-m', request('month', now()->format('Y-m'))
                           )->startOfMonth();
    2. $calendarDeliveries -> collection/array keyed by 'Y-m-d' => delivery count
                           for that day, used to draw the dots on the calendar.
                           Example: ['2026-08-03' => 12, '2026-08-04' => 4, ...]
    3. $riders (top riders) — unchanged.
    4. All existing variables ($stats, $weeklyDeliveries, $topRiders,
       $pendingApplications) stay exactly the same.
    ============================================================
--}}

@php
    $calendarMonth = $calendarMonth ?? \Carbon\Carbon::now()->startOfMonth();
    $calendarDeliveries = collect($calendarDeliveries ?? []);
    $startOffset = $calendarMonth->copy()->startOfMonth()->dayOfWeek; // 0=Sun
    $daysInMonth = $calendarMonth->daysInMonth;
    $today = \Carbon\Carbon::today();
@endphp

{{-- =========================================================
    PAGE HEADER
========================================================= --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-navy text-2xl sm:text-3xl font-bold">Good morning, J&amp;T Express</h1>
        <p class="text-navy/55 text-sm mt-1">Here's what's happening with your fleet today.</p>
    </div>

    <div class="flex items-center gap-2.5 shrink-0">
        <button type="button" id="customizeDashboardBtn"
                class="inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-bg text-navy border border-gray-border text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300">
            <x-lucide-sliders-horizontal class="w-4 h-4" />
            Customize
        </button>

        @if (count($pendingApplications))
            <a href="{{ route('logistics.riders.index') }}"
               class="inline-flex items-center justify-center gap-2 bg-teal hover:bg-teal-dark text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-teal/20">
                Review {{ count($pendingApplications) }} rider application{{ count($pendingApplications) === 1 ? '' : 's' }}
                <x-lucide-arrow-right class="w-4 h-4" />
            </a>
        @endif
    </div>
</div>

{{-- =========================================================
    STAT CARDS (widget: stats — visibility only, always full width)
========================================================= --}}
<div data-widget="stats" data-fixed="1" class="dash-widget grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ($stats as $stat)
        <div class="bg-white border border-gray-border rounded-2xl p-5">
            <p class="text-xs font-semibold text-navy/50 mb-2">{{ $stat['label'] }}</p>
            <p class="text-2xl font-bold text-navy">{{ $stat['value'] }}</p>
            <p class="text-[11px] font-semibold mt-2 {{ $stat['tone'] === 'up' ? 'text-teal-dark' : 'text-amber-600' }}">
                {{ $stat['trend'] }}
            </p>
        </div>
    @endforeach
</div>

{{-- =========================================================
    RESIZABLE / TOGGLEABLE WIDGET GRID
    3-column base grid — widgets set their own col-span via
    data-size (sm = 1 col, lg = 2 cols, full = 3 cols) and their
    order via the "order" CSS property, both applied by JS from
    the saved layout in localStorage. Resizing is now done via
    the corner handle on each widget (drag or click/tap).
========================================================= --}}
<div id="dashboardGrid" class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Deliveries chart --}}
    <div data-widget="chart" data-size="lg" class="dash-widget relative bg-white border border-gray-border rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <p class="text-sm font-bold text-navy">Deliveries — last 7 days</p>
            <a href="{{ route('logistics.reports.index') }}" class="text-xs font-semibold text-teal-dark hover:text-navy transition">View report</a>
        </div>

        @php $max = collect($weeklyDeliveries)->max('value') ?: 1; @endphp
        <div class="flex items-end gap-2.5 h-28">
            @foreach ($weeklyDeliveries as $day)
                <div class="flex-1 bg-gradient-to-t from-teal to-teal-light rounded-t-md" style="height: {{ $day['value'] / $max * 100 }}%"></div>
            @endforeach
        </div>
        <div class="flex gap-2.5 mt-2">
            @foreach ($weeklyDeliveries as $day)
                <span class="flex-1 text-center text-[10px] font-semibold text-navy/40">{{ $day['label'] }}</span>
            @endforeach
        </div>

        <div class="resize-handle" data-resize-handle data-key="chart" title="Drag or click to resize">
            <x-lucide-maximize-2 class="w-3.5 h-3.5 icon-grow" />
            <x-lucide-minimize-2 class="w-3.5 h-3.5 icon-shrink" />
        </div>
    </div>

    {{-- Top riders --}}
    <div data-widget="topRiders" data-size="sm" class="dash-widget relative bg-white border border-gray-border rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-bold text-navy">Top riders today</p>
            <a href="{{ route('logistics.riders.index') }}" class="text-xs font-semibold text-teal-dark hover:text-navy transition">See all</a>
        </div>
        <div class="divide-y divide-gray-border">
            @foreach ($topRiders as $rider)
                <div class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                    <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                        {{ collect(explode(' ', $rider['name']))->map(fn ($p) => $p[0])->implode('') }}
                    </div>
                    <p class="flex-1 text-sm font-semibold text-navy">{{ $rider['name'] }}</p>
                    <p class="text-xs text-navy/50">{{ $rider['deliveries'] }} deliveries</p>
                </div>
            @endforeach
        </div>

        <div class="resize-handle" data-resize-handle data-key="topRiders" title="Drag or click to resize">
            <x-lucide-maximize-2 class="w-3.5 h-3.5 icon-grow" />
            <x-lucide-minimize-2 class="w-3.5 h-3.5 icon-shrink" />
        </div>
    </div>

    {{-- Calendar (NEW) --}}
    <div data-widget="calendar" data-size="sm" class="dash-widget relative bg-white border border-gray-border rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-bold text-navy">{{ $calendarMonth->format('F Y') }}</p>
            <div class="flex items-center gap-1">
                <a href="{{ route('logistics.dashboard', ['month' => $calendarMonth->copy()->subMonth()->format('Y-m')]) }}"
                   class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-bg text-navy/50 hover:text-navy transition">
                    <x-lucide-chevron-left class="w-3.5 h-3.5" />
                </a>
                <a href="{{ route('logistics.dashboard') }}"
                   class="text-[10px] font-semibold text-teal-dark hover:text-navy px-1.5 transition">Today</a>
                <a href="{{ route('logistics.dashboard', ['month' => $calendarMonth->copy()->addMonth()->format('Y-m')]) }}"
                   class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-bg text-navy/50 hover:text-navy transition">
                    <x-lucide-chevron-right class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-y-1.5 text-center">
            @foreach (['S','M','T','W','T','F','S'] as $d)
                <span class="text-[10px] font-semibold text-navy/35">{{ $d }}</span>
            @endforeach

            @for ($i = 0; $i < $startOffset; $i++)
                <span></span>
            @endfor

            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = $calendarMonth->copy()->day($day);
                    $isToday = $date->isSameDay($today);
                    $count = $calendarDeliveries->get($date->format('Y-m-d'), 0);
                @endphp
                <div class="relative flex flex-col items-center justify-center h-7">
                    <span class="text-[11px] font-semibold w-6 h-6 flex items-center justify-center rounded-full
                        {{ $isToday ? 'bg-navy text-white' : 'text-navy/70' }}">
                        {{ $day }}
                    </span>
                    @if ($count > 0)
                        <span class="absolute -bottom-0.5 w-1 h-1 rounded-full {{ $isToday ? 'bg-white' : 'bg-teal' }}"></span>
                    @endif
                </div>
            @endfor
        </div>

        <div class="resize-handle" data-resize-handle data-key="calendar" title="Drag or click to resize">
            <x-lucide-maximize-2 class="w-3.5 h-3.5 icon-grow" />
            <x-lucide-minimize-2 class="w-3.5 h-3.5 icon-shrink" />
        </div>
    </div>

    {{-- Pending rider applications --}}
    <div data-widget="pendingApps" data-size="lg" class="dash-widget relative bg-white border border-gray-border rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-border">
            <p class="text-sm font-bold text-navy">Pending rider applications</p>
            <a href="{{ route('logistics.riders.index') }}" class="text-xs font-semibold text-teal-dark hover:text-navy transition">View all</a>
        </div>

        @forelse ($pendingApplications as $app)
            <div class="flex flex-wrap items-center gap-3 sm:gap-4 px-6 py-4 {{ !$loop->last ? 'border-b border-gray-border' : '' }}">
                <p class="flex-1 min-w-32 text-sm font-semibold text-navy">{{ $app['name'] }}</p>
                <p class="flex-1 min-w-32 text-xs text-navy/50">{{ $app['vehicle'] }}</p>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $app['complete'] ? 'bg-teal-light text-teal-dark' : 'bg-red-50 text-red-600' }}">
                    {{ $app['complete'] ? 'Docs complete' : 'Docs incomplete' }}
                </span>
                <a href="{{ route('logistics.riders.index') }}" class="text-xs font-semibold text-navy hover:text-teal-dark transition">Review</a>
            </div>
        @empty
            <p class="px-6 py-10 text-center text-navy/40 text-sm">No pending applications right now.</p>
        @endforelse

        <div class="resize-handle" data-resize-handle data-key="pendingApps" title="Drag or click to resize">
            <x-lucide-maximize-2 class="w-3.5 h-3.5 icon-grow" />
            <x-lucide-minimize-2 class="w-3.5 h-3.5 icon-shrink" />
        </div>
    </div>

</div>

{{-- =========================================================
    CUSTOMIZE PANEL (slide-over)
========================================================= --}}
<div id="customizeOverlay" class="fixed inset-0 bg-navy/30 backdrop-blur-[2px] z-40 hidden opacity-0 transition-opacity duration-300"></div>

<div id="customizePanel"
     class="fixed top-0 right-0 h-full w-full sm:w-96 bg-white z-50 shadow-2xl translate-x-full transition-transform duration-300 flex flex-col">
    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-border">
        <p class="text-base font-bold text-navy">Customize dashboard</p>
        <button type="button" id="closeCustomizeBtn" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-bg text-navy/50 hover:text-navy transition">
            <x-lucide-x class="w-4 h-4" />
        </button>
    </div>

    <div class="px-6 pt-4 pb-1">
        <p class="text-xs text-navy/45 leading-relaxed">
            Show/hide and reorder widgets below. To resize a widget, drag or tap the
            <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-gray-bg text-navy/60 align-middle mx-0.5">
                <x-lucide-maximize-2 class="w-2.5 h-2.5" />
            </span>
            handle on its bottom-right corner.
        </p>
    </div>

    <div id="widgetList" class="flex-1 overflow-y-auto px-6 py-5 space-y-3"></div>

    <div class="px-6 py-5 border-t border-gray-border flex gap-2.5">
        <button type="button" id="resetLayoutBtn"
                class="flex-1 text-sm font-semibold text-navy/60 hover:text-navy bg-gray-bg hover:bg-gray-border px-4 py-2.5 rounded-full transition">
            Reset to default
        </button>
        <button type="button" id="saveLayoutBtn"
                class="flex-1 text-sm font-semibold text-white bg-teal hover:bg-teal-dark px-4 py-2.5 rounded-full transition">
            Save changes
        </button>
    </div>
</div>

<style>
    .dash-widget { transition: opacity .2s ease, box-shadow .2s ease, outline-color .2s ease; }
    .dash-widget[data-hidden="1"] { display: none !important; }

    .widget-row { display: flex; align-items: center; gap: .625rem; padding: .75rem; border: 1px solid #E7E9EE; border-radius: 1rem; background: #fff; }

    .toggle-switch { width: 34px; height: 19px; border-radius: 9999px; background: #E7E9EE; position: relative; cursor: pointer; transition: background .2s ease; flex-shrink: 0; }
    .toggle-switch[data-on="1"] { background: #14B8A6; }
    .toggle-switch span { position: absolute; top: 2px; left: 2px; width: 15px; height: 15px; border-radius: 9999px; background: #fff; transition: transform .2s ease; }
    .toggle-switch[data-on="1"] span { transform: translateX(15px); }

    /* --- Corner resize handle --- */
    .resize-handle {
        display: none;
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        background: #0F172A;
        color: #fff;
        align-items: center;
        justify-content: center;
        cursor: ew-resize;
        user-select: none;
        touch-action: none;
        z-index: 5;
        box-shadow: 0 2px 6px rgba(15,23,42,.25);
        transition: transform .12s ease, background .15s ease;
    }
    .resize-handle:hover { background: #14B8A6; }
    .resize-handle:active { transform: scale(0.92); }
    .resize-handle .icon-grow,
    .resize-handle .icon-shrink { position: absolute; }
    .resize-handle .icon-shrink { display: none; }

    /* when widget is at max size (full), show shrink icon instead of grow icon */
    .dash-widget[data-size="full"] .resize-handle .icon-grow { display: none; }
    .dash-widget[data-size="full"] .resize-handle .icon-shrink { display: block; }

    #dashboardGrid.customizing .dash-widget:not([data-fixed]) {
        outline: 2px dashed #CBD5E1;
        outline-offset: 3px;
        border-radius: 1rem;
    }
    #dashboardGrid.customizing .dash-widget:not([data-fixed]) .resize-handle { display: flex; }
    .dash-widget.is-resizing { box-shadow: 0 0 0 2px #14B8A6; }
</style>

<script>
(function () {
    const STORAGE_KEY = 'shophop_logistics_dashboard_layout';

    const WIDGET_LABELS = {
        stats: 'Stat cards',
        chart: 'Deliveries chart',
        topRiders: 'Top riders',
        calendar: 'Calendar',
        pendingApps: 'Pending applications',
    };

    const SIZE_SPAN = { sm: 'lg:col-span-1', lg: 'lg:col-span-2', full: 'lg:col-span-3' };
    const SIZE_ORDER = ['sm', 'lg', 'full'];

    function defaultLayout() {
        const widgets = document.querySelectorAll('.dash-widget');
        const layout = {};
        widgets.forEach((el, i) => {
            layout[el.dataset.widget] = {
                visible: true,
                size: el.dataset.fixed ? 'full' : (el.dataset.size || 'sm'),
                order: i,
            };
        });
        return layout;
    }

    function loadLayout() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return defaultLayout();
            const saved = JSON.parse(raw);
            return { ...defaultLayout(), ...saved };
        } catch (e) {
            return defaultLayout();
        }
    }

    function saveLayout(layout) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(layout));
    }

    function applyLayout(layout) {
        document.querySelectorAll('.dash-widget').forEach((el) => {
            const key = el.dataset.widget;
            const conf = layout[key];
            if (!conf) return;

            el.dataset.hidden = conf.visible ? '0' : '1';
            el.style.order = conf.order ?? 0;

            if (!el.dataset.fixed) {
                el.dataset.size = conf.size;
                Object.values(SIZE_SPAN).forEach((c) => el.classList.remove(c));
                el.classList.add(SIZE_SPAN[conf.size] || SIZE_SPAN.sm);
            }
        });
    }

    function renderWidgetList(layout) {
        const list = document.getElementById('widgetList');
        list.innerHTML = '';

        const ordered = Object.entries(layout).sort((a, b) => (a[1].order ?? 0) - (b[1].order ?? 0));

        ordered.forEach(([key, conf]) => {
            const row = document.createElement('div');
            row.className = 'widget-row';
            row.innerHTML = `
                <div class="flex flex-col gap-0.5">
                    <button type="button" data-move="up" data-key="${key}" class="text-navy/30 hover:text-navy leading-none text-[10px]">▲</button>
                    <button type="button" data-move="down" data-key="${key}" class="text-navy/30 hover:text-navy leading-none text-[10px]">▼</button>
                </div>
                <p class="flex-1 text-sm font-semibold text-navy">${WIDGET_LABELS[key] || key}</p>
                <div class="toggle-switch" data-on="${conf.visible ? '1' : '0'}" data-toggle="${key}"><span></span></div>
            `;
            list.appendChild(row);
        });

        list.querySelectorAll('[data-toggle]').forEach((el) => {
            el.addEventListener('click', () => {
                const key = el.dataset.toggle;
                currentLayout[key].visible = !currentLayout[key].visible;
                el.dataset.on = currentLayout[key].visible ? '1' : '0';
            });
        });

        list.querySelectorAll('[data-move]').forEach((el) => {
            el.addEventListener('click', () => {
                const key = el.dataset.key;
                const dir = el.dataset.move === 'up' ? -1 : 1;
                const entries = Object.entries(currentLayout).sort((a, b) => (a[1].order ?? 0) - (b[1].order ?? 0));
                const pos = entries.findIndex(([k]) => k === key);
                const swapWith = pos + dir;
                if (swapWith < 0 || swapWith >= entries.length) return;
                [entries[pos][1].order, entries[swapWith][1].order] = [entries[swapWith][1].order, entries[pos][1].order];
                renderWidgetList(currentLayout);
            });
        });
    }

    let currentLayout = loadLayout();
    applyLayout(currentLayout);

    const overlay = document.getElementById('customizeOverlay');
    const panel = document.getElementById('customizePanel');
    const grid = document.getElementById('dashboardGrid');

    function openPanel() {
        currentLayout = loadLayout();
        renderWidgetList(currentLayout);
        grid.classList.add('customizing');
        overlay.classList.remove('hidden');
        requestAnimationFrame(() => { overlay.classList.remove('opacity-0'); panel.classList.remove('translate-x-full'); });
    }

    function closePanel() {
        grid.classList.remove('customizing');
        overlay.classList.add('opacity-0');
        panel.classList.add('translate-x-full');
        setTimeout(() => overlay.classList.add('hidden'), 300);
    }

    document.getElementById('customizeDashboardBtn').addEventListener('click', openPanel);
    document.getElementById('closeCustomizeBtn').addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);

    document.getElementById('saveLayoutBtn').addEventListener('click', () => {
        saveLayout(currentLayout);
        applyLayout(currentLayout);
        closePanel();
    });

    document.getElementById('resetLayoutBtn').addEventListener('click', () => {
        localStorage.removeItem(STORAGE_KEY);
        currentLayout = defaultLayout();
        applyLayout(currentLayout);
        renderWidgetList(currentLayout);
    });

    // ---------------------------------------------------------
    // Corner resize handles — click to cycle, drag to snap size
    // ---------------------------------------------------------
    const DRAG_THRESHOLD = 50; // px of horizontal movement needed to bump a size step

    document.querySelectorAll('[data-resize-handle]').forEach((handle) => {
        const key = handle.dataset.key;
        const widgetEl = handle.closest('.dash-widget');

        let startX = 0;
        let dragged = false;
        let stepApplied = false;

        function setSize(newSize) {
            if (!SIZE_ORDER.includes(newSize)) return;
            currentLayout[key].size = newSize;
            widgetEl.dataset.size = newSize;
            Object.values(SIZE_SPAN).forEach((c) => widgetEl.classList.remove(c));
            widgetEl.classList.add(SIZE_SPAN[newSize]);
        }

        function cycleSize(dir) {
            const idx = SIZE_ORDER.indexOf(currentLayout[key].size);
            const nextIdx = Math.min(SIZE_ORDER.length - 1, Math.max(0, idx + dir));
            setSize(SIZE_ORDER[nextIdx]);
        }

        function onPointerDown(e) {
            startX = e.clientX;
            dragged = false;
            stepApplied = false;
            widgetEl.classList.add('is-resizing');
            document.addEventListener('pointermove', onPointerMove);
            document.addEventListener('pointerup', onPointerUp);
            e.preventDefault();
        }

        function onPointerMove(e) {
            const delta = e.clientX - startX;
            if (Math.abs(delta) > 8) dragged = true;

            if (!stepApplied && delta > DRAG_THRESHOLD) {
                cycleSize(1);
                stepApplied = true;
            } else if (!stepApplied && delta < -DRAG_THRESHOLD) {
                cycleSize(-1);
                stepApplied = true;
            }
        }

        function onPointerUp() {
            widgetEl.classList.remove('is-resizing');
            document.removeEventListener('pointermove', onPointerMove);
            document.removeEventListener('pointerup', onPointerUp);

            // plain click (no real drag) -> cycle forward, wrapping back to sm after full
            if (!dragged) {
                const idx = SIZE_ORDER.indexOf(currentLayout[key].size);
                const nextIdx = (idx + 1) % SIZE_ORDER.length;
                setSize(SIZE_ORDER[nextIdx]);
            }
        }

        handle.addEventListener('pointerdown', onPointerDown);
    });
})();
</script>

@endsection