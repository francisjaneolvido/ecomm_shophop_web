@extends('logistics.layouts')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    | Defaults muna para hindi mag-error habang hindi pa fully connected
    | sa database ang Logistics dashboard.
    */

    $stats = array_merge([
        'total_riders' => 0,
        'pending_riders' => 0,
        'active_deliveries' => 0,
        'completed_today' => 0,
        'failed_deliveries' => 0,
    ], $stats ?? []);

    $authUser = auth()->user();

    $logisticsName = $logisticsName
        ?? $authUser?->name
        ?? 'Logistics Partner';

    $recentRiderApplications = collect($recentRiderApplications ?? [
        [
            'name' => 'Juan Dela Cruz',
            'initials' => 'JD',
            'vehicle' => 'Motorcycle',
            'applied' => '8 minutes ago',
            'applied_at' => now()->subMinutes(8)->toIso8601String(),
        ],
        [
            'name' => 'Pedro Santos',
            'initials' => 'PS',
            'vehicle' => 'Motorcycle',
            'applied' => '25 minutes ago',
            'applied_at' => now()->subMinutes(25)->toIso8601String(),
        ],
        [
            'name' => 'Mark Reyes',
            'initials' => 'MR',
            'vehicle' => 'Van',
            'applied' => '1 hour ago',
            'applied_at' => now()->subHour()->toIso8601String(),
        ],
    ]);

    $recentDeliveries = collect($recentDeliveries ?? [
        [
            'reference' => 'SHP-250829-001',
            'customer' => 'Maria Santos',
            'rider' => 'Juan Dela Cruz',
            'status' => 'In Transit',
        ],
        [
            'reference' => 'SHP-250829-002',
            'customer' => 'Carlo Reyes',
            'rider' => 'Pedro Santos',
            'status' => 'Assigned',
        ],
        [
            'reference' => 'SHP-250829-003',
            'customer' => 'Angela Cruz',
            'rider' => 'Mark Reyes',
            'status' => 'Delivered',
        ],
    ]);

    $needsAttention = $stats['pending_riders'] + $stats['failed_deliveries'];

    $hour = now()->hour;

    $greeting = $hour < 12
        ? 'Good morning'
        : ($hour < 18 ? 'Good afternoon' : 'Good evening');

    // Delivery status mix, sourced from the same $recentDeliveries used below.
    $deliveryMix = [
        'in_transit' => $recentDeliveries->where('status', 'In Transit')->count(),
        'assigned' => $recentDeliveries->where('status', 'Assigned')->count(),
        'delivered' => $recentDeliveries->where('status', 'Delivered')->count(),
        'failed' => $recentDeliveries->where('status', 'Failed')->count(),
    ];

    $deliveryMixMax = max(1, ...array_values($deliveryMix));

    $avatarPalette = [
        'bg-teal/15 text-teal-dark',
        'bg-sky/15 text-sky',
        'bg-amber-50 text-amber-700',
        'bg-navy/10 text-navy',
    ];

    // NEW: optional endpoint for background stat refresh. Only used if the
    // route actually exists, so this is safe to ship even without a backend
    // endpoint wired up yet.
    $dashboardRefreshUrl = \Illuminate\Support\Facades\Route::has('logistics.dashboard.refresh')
        ? route('logistics.dashboard.refresh')
        : null;
@endphp


<style>
    #logisticsDashboard {
        --dash-gap: 1rem;
        --dash-section-gap: 1.25rem;
        --dash-card-pad: 1rem;
        --dash-row-pad: .8rem;
    }

    #logisticsDashboard[data-dashboard-density="compact"] {
        --dash-gap: .75rem;
        --dash-section-gap: 1rem;
        --dash-card-pad: .8rem;
        --dash-row-pad: .65rem;
    }

    #logisticsDashboard .dash-gap {
        gap: var(--dash-gap);
    }

    #logisticsDashboard .dash-section {
        margin-bottom: var(--dash-section-gap);
    }

    #logisticsDashboard .dash-card-pad {
        padding: var(--dash-card-pad);
    }

    #logisticsDashboard .dash-row {
        padding-top: var(--dash-row-pad);
        padding-bottom: var(--dash-row-pad);
    }

    #logisticsDashboard[data-dashboard-density="compact"] .density-hide-compact {
        display: none;
    }

    #logisticsDashboard.dashboard-focus-mode [data-low-priority="true"] {
        display: none !important;
    }

    #logisticsDashboard [data-dashboard-widget][hidden] {
        display: none !important;
    }

    #logisticsDashboard .dashboard-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(15, 44, 63, .18) transparent;
    }

    #logisticsDashboard .dashboard-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    #logisticsDashboard .dashboard-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(15, 44, 63, .16);
        border-radius: 999px;
    }

    #logisticsDashboardCustomizePanel[hidden] {
        display: none !important;
    }

    /* NEW: sortable table headers — cursor + tiny arrow via ::after, no layout shift */
    #logisticsDashboard th[data-sort-key] {
        cursor: pointer;
        user-select: none;
    }

    #logisticsDashboard th[data-sort-key]:hover {
        color: rgba(15, 44, 63, .65);
    }

    #logisticsDashboard th[data-sort-key][aria-sort="ascending"]::after {
        content: " \25B2";
        font-size: 8px;
    }

    #logisticsDashboard th[data-sort-key][aria-sort="descending"]::after {
        content: " \25BC";
        font-size: 8px;
    }

    /* NEW: copyable reference cell affordance */
    #logisticsDashboard [data-copy-reference] {
        cursor: pointer;
    }

    #logisticsDashboard [data-copy-reference]:hover {
        text-decoration: underline;
        text-decoration-style: dotted;
        text-underline-offset: 3px;
    }

    /* NEW: transient "navigating" state so double clicks on links don't fire twice */
    #logisticsDashboard a.is-navigating {
        opacity: .6;
        pointer-events: none;
    }

    /* NEW: lightweight toast used for copy / refresh feedback, injected at runtime */
    .dash-toast {
        position: fixed;
        top: 1.25rem;
        right: 1.25rem;
        z-index: 80;
        background: #0f2c3f;
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        padding: .55rem .9rem;
        border-radius: .5rem;
        box-shadow: 0 8px 24px rgba(15, 44, 63, .25);
        opacity: 0;
        transform: translateY(-6px);
        transition: opacity .18s ease, transform .18s ease;
        pointer-events: none;
    }

    .dash-toast.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>


<div
    id="logisticsDashboard"
    data-dashboard-density="comfortable"
    data-refresh-url="{{ $dashboardRefreshUrl }}"
    class="relative"
>

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <header class="dash-section">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

            <div class="min-w-0">

                <div class="flex items-center gap-2 mb-1.5">
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inline-flex w-full h-full rounded-full bg-teal opacity-30 animate-ping"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-teal"></span>
                    </span>

                    <p class="text-[10px] sm:text-xs font-semibold tracking-[0.16em] uppercase text-teal-dark">
                        Logistics Overview
                    </p>
                </div>


                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">

                    <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                        {{ $greeting }}, {{ $logisticsName }}
                    </h1>

                    @if ($needsAttention === 0)
                        <span
                            class="inline-flex items-center gap-1
                                   px-2 py-1 rounded-full
                                   bg-teal/10 text-teal-dark
                                   text-[9px] font-semibold"
                        >
                            <x-lucide-circle-check class="w-3 h-3" />
                            All clear
                        </span>
                    @endif

                </div>


                <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                    Manage your riders and monitor ShopHop deliveries from one workspace.
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                {{-- NEW: last-updated indicator, refreshed client-side --}}
                <div
                    data-dashboard-last-updated
                    data-updated-at="{{ now()->toIso8601String() }}"
                    class="hidden md:inline-flex items-center gap-2
                           h-9 px-3 rounded-lg
                           bg-white border border-gray-border
                           text-[11px] text-navy/45"
                >
                    <x-lucide-calendar-days class="w-3.5 h-3.5 text-teal-dark" />
                    {{ now()->format('M d, Y') }}
                </div>


                <button
                    type="button"
                    data-dashboard-customize-open
                    aria-haspopup="dialog"
                    aria-expanded="false"
                    aria-controls="logisticsDashboardCustomizePanel"
                    class="inline-flex items-center justify-center gap-1.5
                           h-9 px-3.5 rounded-lg
                           border border-gray-border
                           bg-white
                           text-xs font-semibold text-navy
                           hover:border-teal/40 hover:text-teal-dark
                           hover:shadow-sm
                           transition-all"
                >
                    <x-lucide-sliders-horizontal class="w-3.5 h-3.5" />
                    Customize
                </button>


                <a
                    href="{{ route('logistics.deliveries.board') }}"
                    class="inline-flex items-center justify-center gap-1.5
                           h-9 px-3.5 rounded-lg
                           bg-navy hover:bg-navy/90
                           text-xs font-semibold text-white
                           transition-colors"
                >
                    <x-lucide-package class="w-3.5 h-3.5" />
                    Deliveries
                </a>

            </div>

        </div>

    </header>


    {{-- =========================================================
        KPI CARDS
    ========================================================= --}}
    <section
        data-dashboard-widget="stats"
        class="dash-section"
    >
        <div class="grid grid-cols-2 xl:grid-cols-4 dash-gap">

            {{-- Total Riders --}}
            <a
                href="{{ route('logistics.riders.index') }}"
                class="group relative overflow-hidden
                       bg-white border border-gray-border
                       rounded-xl dash-card-pad
                       hover:border-teal/35
                       hover:shadow-lg hover:shadow-teal/5
                       hover:-translate-y-0.5
                       transition-all duration-200"
            >
                <span
                    class="absolute inset-x-4 top-0 h-0.5 rounded-full
                           bg-teal
                           scale-x-0 group-hover:scale-x-100
                           transition-transform origin-left"
                ></span>

                <div class="flex items-start justify-between gap-3">

                    <div
                        class="w-9 h-9 rounded-lg
                               bg-teal/10 text-teal-dark
                               flex items-center justify-center
                               group-hover:bg-teal group-hover:text-white
                               transition-colors"
                    >
                        <x-lucide-bike class="w-4 h-4" />
                    </div>


                    <span
                        class="text-[9px] font-semibold
                               text-navy/40 bg-gray-bg
                               px-2 py-1 rounded-full"
                    >
                        Fleet
                    </span>

                </div>


                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums" data-stat="total_riders">
                        {{ number_format($stats['total_riders']) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Total Riders
                    </p>

                </div>


                <div
                    class="density-hide-compact
                           flex items-center mt-2
                           text-[9px] sm:text-[10px] text-navy/35"
                >
                    <span>Approved riders</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-teal-dark
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>


            {{-- Rider Applications --}}
            <a
                href="{{ route('logistics.riders.index') }}"
                class="group relative overflow-hidden
                       bg-white border border-gray-border
                       rounded-xl dash-card-pad
                       hover:border-amber-200
                       hover:shadow-lg hover:shadow-amber-500/5
                       hover:-translate-y-0.5
                       transition-all duration-200"
            >
                <span
                    class="absolute inset-x-4 top-0 h-0.5 rounded-full
                           bg-amber-400
                           scale-x-0 group-hover:scale-x-100
                           transition-transform origin-left"
                ></span>

                <div class="flex items-start justify-between gap-3">

                    <div
                        class="w-9 h-9 rounded-lg
                               bg-amber-50 text-amber-700
                               flex items-center justify-center
                               group-hover:bg-amber-500 group-hover:text-white
                               transition-colors"
                    >
                        <x-lucide-user-round-plus class="w-4 h-4" />
                    </div>


                    @if ($stats['pending_riders'] > 0)
                        <span
                            class="inline-flex items-center gap-1
                                   text-[9px] font-semibold
                                   text-amber-700 bg-amber-50
                                   px-2 py-1 rounded-full"
                        >
                            <span class="w-1 h-1 rounded-full bg-amber-500"></span>
                            Review
                        </span>
                    @endif

                </div>


                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums" data-stat="pending_riders">
                        {{ number_format($stats['pending_riders']) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Rider Applications
                    </p>

                </div>


                <div
                    class="density-hide-compact
                           flex items-center mt-2
                           text-[9px] sm:text-[10px] text-navy/35"
                >
                    <span>Waiting for review</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-amber-700
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>


            {{-- Active Deliveries --}}
            <a
                href="{{ route('logistics.deliveries.board') }}"
                class="group relative overflow-hidden
                       bg-white border border-gray-border
                       rounded-xl dash-card-pad
                       hover:border-sky/35
                       hover:shadow-lg hover:shadow-sky/5
                       hover:-translate-y-0.5
                       transition-all duration-200"
            >
                <span
                    class="absolute inset-x-4 top-0 h-0.5 rounded-full
                           bg-sky
                           scale-x-0 group-hover:scale-x-100
                           transition-transform origin-left"
                ></span>

                <div class="flex items-start justify-between gap-3">

                    <div
                        class="w-9 h-9 rounded-lg
                               bg-sky/10 text-sky
                               flex items-center justify-center
                               group-hover:bg-sky group-hover:text-white
                               transition-colors"
                    >
                        <x-lucide-truck class="w-4 h-4" />
                    </div>


                    <span
                        class="text-[9px] font-semibold
                               text-navy/40 bg-gray-bg
                               px-2 py-1 rounded-full"
                    >
                        Live
                    </span>

                </div>


                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums" data-stat="active_deliveries">
                        {{ number_format($stats['active_deliveries']) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Active Deliveries
                    </p>

                </div>


                <div
                    class="density-hide-compact
                           flex items-center mt-2
                           text-[9px] sm:text-[10px] text-navy/35"
                >
                    <span>Currently in progress</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-sky
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>


            {{-- Completed Today --}}
            <a
                href="{{ route('logistics.reports.index') }}"
                class="group relative overflow-hidden
                       bg-white border border-gray-border
                       rounded-xl dash-card-pad
                       hover:border-teal/35
                       hover:shadow-lg hover:shadow-teal/5
                       hover:-translate-y-0.5
                       transition-all duration-200"
            >
                <span
                    class="absolute inset-x-4 top-0 h-0.5 rounded-full
                           bg-teal
                           scale-x-0 group-hover:scale-x-100
                           transition-transform origin-left"
                ></span>

                <div class="flex items-start justify-between gap-3">

                    <div
                        class="w-9 h-9 rounded-lg
                               bg-teal-light text-teal-dark
                               flex items-center justify-center
                               group-hover:bg-teal group-hover:text-white
                               transition-colors"
                    >
                        <x-lucide-circle-check-big class="w-4 h-4" />
                    </div>


                    <span
                        class="text-[9px] font-semibold
                               text-navy/40 bg-gray-bg
                               px-2 py-1 rounded-full"
                    >
                        Today
                    </span>

                </div>


                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums" data-stat="completed_today">
                        {{ number_format($stats['completed_today']) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Completed Today
                    </p>

                </div>


                <div
                    class="density-hide-compact
                           flex items-center mt-2
                           text-[9px] sm:text-[10px] text-navy/35"
                >
                    <span>Successful deliveries</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-teal-dark
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>

        </div>
    </section>


    {{-- =========================================================
        NEEDS ATTENTION
    ========================================================= --}}
    <section
        data-dashboard-widget="attention"
        class="dash-section
               relative overflow-hidden
               bg-gradient-to-br from-navy to-navy/90
               rounded-xl px-4 sm:px-5 py-4"
    >
        <div class="pointer-events-none absolute -top-16 -right-16 w-44 h-44 rounded-full bg-teal/10"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 w-44 h-44 rounded-full bg-white/[0.025]"></div>


        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div class="flex items-start gap-3">

                <div
                    class="w-9 h-9 rounded-lg
                           bg-white/10 text-teal
                           flex items-center justify-center
                           shrink-0"
                >
                    <x-lucide-bell-ring class="w-4 h-4" />
                </div>


                <div>

                    <div class="flex flex-wrap items-center gap-2">

                        <h2 class="text-sm sm:text-base font-bold text-white">
                            Needs Attention
                        </h2>


                        @if ($needsAttention > 0)
                            <span
                                data-stat="needs_attention_badge"
                                class="inline-flex items-center justify-center
                                       min-w-5 h-5 px-1.5
                                       rounded-full bg-teal
                                       text-white text-[9px] font-bold"
                            >
                                {{ $needsAttention }}
                            </span>
                        @else
                            <span
                                data-stat="needs_attention_badge"
                                class="inline-flex items-center gap-1
                                       text-[9px] font-semibold text-teal
                                       bg-white/10
                                       px-2 py-0.5 rounded-full"
                            >
                                <x-lucide-circle-check class="w-3 h-3" />
                                All clear
                            </span>
                        @endif

                    </div>


                    <p class="text-[10px] sm:text-xs text-white/45 mt-1 max-w-md">
                        Review riders and deliveries that may need a decision or follow-up.
                    </p>

                </div>

            </div>


            <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">

                <a
                    href="{{ route('logistics.riders.index') }}"
                    class="group flex items-center gap-2
                           sm:min-w-40 px-3 py-2.5
                           rounded-lg
                           bg-white/10 hover:bg-white/15
                           border border-white/10
                           transition-colors"
                >
                    <div
                        class="w-7 h-7 rounded-lg
                               bg-teal/15 text-teal
                               flex items-center justify-center"
                    >
                        <x-lucide-user-check class="w-3.5 h-3.5" />
                    </div>

                    <div class="min-w-0">

                        <p class="text-[9px] text-white/40">
                            Rider applications
                        </p>

                        <p class="text-xs font-semibold text-white truncate" data-stat="pending_riders_label">
                            {{ $stats['pending_riders'] }} pending
                        </p>

                    </div>

                    <x-lucide-chevron-right
                        class="hidden sm:block
                               w-3.5 h-3.5 ml-auto
                               text-white/25
                               group-hover:text-teal
                               group-hover:translate-x-0.5
                               transition"
                    />
                </a>


                <a
                    href="{{ route('logistics.deliveries.board') }}"
                    class="group flex items-center gap-2
                           sm:min-w-40 px-3 py-2.5
                           rounded-lg
                           bg-white/10 hover:bg-white/15
                           border border-white/10
                           transition-colors"
                >
                    <div
                        class="w-7 h-7 rounded-lg
                               bg-red-400/10 text-red-300
                               flex items-center justify-center"
                    >
                        <x-lucide-triangle-alert class="w-3.5 h-3.5" />
                    </div>

                    <div class="min-w-0">

                        <p class="text-[9px] text-white/40">
                            Failed deliveries
                        </p>

                        <p class="text-xs font-semibold text-white truncate" data-stat="failed_deliveries_label">
                            {{ $stats['failed_deliveries'] }} open
                        </p>

                    </div>

                    <x-lucide-chevron-right
                        class="hidden sm:block
                               w-3.5 h-3.5 ml-auto
                               text-white/25
                               group-hover:text-red-300
                               group-hover:translate-x-0.5
                               transition"
                    />
                </a>

            </div>

        </div>
    </section>


    {{-- =========================================================
        MAIN WORKSPACE GRID
    ========================================================= --}}
    <div
        class="grid grid-cols-1
               xl:grid-cols-[minmax(0,1.72fr)_minmax(290px,0.8fr)]
               dash-gap"
    >

        {{-- =====================================================
            RECENT DELIVERIES
        ===================================================== --}}
        <section
            id="dashboardRecentDeliveries"
            data-dashboard-widget="deliveries"
            class="bg-white border border-gray-border rounded-xl overflow-hidden"
        >

            <div
                class="flex items-center justify-between gap-4
                       px-4 sm:px-5 py-3.5
                       border-b border-gray-border"
            >

                <div class="min-w-0">

                    <div class="flex items-center gap-2">

                        <h2 class="text-sm font-bold text-navy">
                            Recent Deliveries
                        </h2>

                        @if ($recentDeliveries->count() > 0)
                            <span
                                class="inline-flex items-center justify-center
                                       min-w-5 h-5 px-1.5
                                       rounded-full bg-gray-bg
                                       text-[9px] font-bold text-navy/45"
                            >
                                {{ $recentDeliveries->count() }}
                            </span>
                        @endif

                    </div>

                    <p class="text-[10px] sm:text-xs text-navy/40 mt-0.5">
                        Latest delivery activity handled by your riders.
                    </p>

                </div>


                <a
                    href="{{ route('logistics.deliveries.board') }}"
                    class="inline-flex items-center gap-1
                           text-[10px] sm:text-xs
                           font-semibold text-teal-dark
                           hover:text-navy
                           transition-colors shrink-0"
                >
                    View all
                    <x-lucide-arrow-right class="w-3 h-3" />
                </a>

            </div>


            <div class="overflow-x-auto">

                {{-- NEW: data-sort-key headers enable client-side sorting --}}
                <table class="w-full text-sm" data-deliveries-table>

                    <thead>

                        <tr class="bg-gray-bg border-b border-gray-border">

                            <th
                                data-sort-key="reference"
                                data-sort-type="text"
                                aria-sort="none"
                                class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wide text-navy/40"
                            >
                                Reference
                            </th>

                            <th
                                data-sort-key="customer"
                                data-sort-type="text"
                                aria-sort="none"
                                class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wide text-navy/40"
                            >
                                Customer
                            </th>

                            <th class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wide text-navy/40 density-hide-compact">
                                Rider
                            </th>

                            <th
                                data-sort-key="status"
                                data-sort-type="text"
                                aria-sort="none"
                                class="text-left px-5 py-3 text-[10px] font-semibold uppercase tracking-wide text-navy/40"
                            >
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-border">

                        @forelse ($recentDeliveries as $delivery)

                            @php
                                $deliveryStatus = match ($delivery['status']) {
                                    'Delivered' => 'bg-teal-light text-teal-dark',
                                    'In Transit' => 'bg-sky/10 text-sky',
                                    'Assigned' => 'bg-amber-50 text-amber-700',
                                    'Failed' => 'bg-red-50 text-red-600',
                                    default => 'bg-gray-bg text-navy/50',
                                };
                            @endphp


                            <tr class="dash-row hover:bg-gray-bg/60 transition">

                                <td
                                    class="px-5 py-4 text-xs font-semibold text-navy"
                                    data-sort-value="{{ $delivery['reference'] }}"
                                    data-copy-reference="{{ $delivery['reference'] }}"
                                    role="button"
                                    tabindex="0"
                                    title="Click to copy reference"
                                >
                                    {{ $delivery['reference'] }}
                                </td>

                                <td
                                    class="px-5 py-4 text-xs text-navy/60"
                                    data-sort-value="{{ $delivery['customer'] }}"
                                >
                                    {{ $delivery['customer'] }}
                                </td>

                                <td class="px-5 py-4 text-xs text-navy/60 density-hide-compact">
                                    {{ $delivery['rider'] }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                    data-sort-value="{{ $delivery['status'] }}"
                                >
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[9px] font-semibold {{ $deliveryStatus }}">
                                        {{ $delivery['status'] }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">

                                    <div class="w-11 h-11 mx-auto rounded-xl bg-teal/10 text-teal-dark flex items-center justify-center">
                                        <x-lucide-package class="w-4 h-4" />
                                    </div>

                                    <p class="text-xs font-semibold text-navy/60 mt-3">
                                        No deliveries yet
                                    </p>

                                    <p class="text-[10px] text-navy/35 mt-1 max-w-[230px] mx-auto">
                                        New pickups assigned to your riders will appear here.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>


        {{-- =====================================================
            RIGHT SIDEBAR
        ===================================================== --}}
        <div class="space-y-4">

            {{-- Delivery Mix Snapshot --}}
            <section
                data-dashboard-widget="snapshot"
                data-low-priority="true"
                class="bg-white border border-gray-border rounded-xl dash-card-pad"
            >

                <div class="flex items-start justify-between gap-3 mb-4">

                    <div>

                        <h2 class="text-sm font-bold text-navy">
                            Delivery Mix
                        </h2>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Status of the latest deliveries.
                        </p>

                    </div>


                    <div
                        class="w-8 h-8 rounded-lg
                               bg-teal/10 text-teal-dark
                               flex items-center justify-center
                               shrink-0"
                    >
                        <x-lucide-chart-no-axes-column-increasing class="w-4 h-4" />
                    </div>

                </div>


                <div class="space-y-3.5">

                    @foreach ([
                        [
                            'label' => 'In Transit',
                            'count' => $deliveryMix['in_transit'],
                            'icon' => 'truck',
                            'classes' => 'bg-sky/10 text-sky',
                            'bar' => 'bg-sky',
                        ],
                        [
                            'label' => 'Assigned',
                            'count' => $deliveryMix['assigned'],
                            'icon' => 'user-round-plus',
                            'classes' => 'bg-amber-50 text-amber-700',
                            'bar' => 'bg-amber-500',
                        ],
                        [
                            'label' => 'Delivered',
                            'count' => $deliveryMix['delivered'],
                            'icon' => 'circle-check-big',
                            'classes' => 'bg-teal/10 text-teal-dark',
                            'bar' => 'bg-teal',
                        ],
                        [
                            'label' => 'Failed',
                            'count' => $deliveryMix['failed'],
                            'icon' => 'triangle-alert',
                            'classes' => 'bg-red-50 text-red-600',
                            'bar' => 'bg-red-400',
                        ],
                    ] as $item)

                        <div>

                            <div class="flex items-center gap-3 mb-1.5">

                                <div
                                    class="w-7 h-7 rounded-lg
                                           {{ $item['classes'] }}
                                           flex items-center justify-center
                                           shrink-0"
                                >
                                    <x-dynamic-component
                                        :component="'lucide-' . $item['icon']"
                                        class="w-3.5 h-3.5"
                                    />
                                </div>

                                <span class="text-xs text-navy/60 flex-1">
                                    {{ $item['label'] }}
                                </span>

                                <span class="text-xs font-bold text-navy tabular-nums">
                                    {{ $item['count'] }}
                                </span>

                            </div>


                            <div class="h-1.5 rounded-full bg-gray-bg overflow-hidden ml-10">

                                <div
                                    class="h-full rounded-full {{ $item['bar'] }}
                                           transition-all duration-500"
                                    style="width:
                                        {{ $item['count'] > 0
                                            ? max(6, round(($item['count'] / $deliveryMixMax) * 100))
                                            : 0 }}%"
                                ></div>

                            </div>

                        </div>

                    @endforeach

                </div>


                <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-gray-border">

                    <div class="rounded-lg bg-gray-bg px-2.5 py-2.5">
                        <p class="text-[8px] text-navy/40 truncate">
                            Riders
                        </p>
                        <p class="text-sm font-bold text-navy mt-0.5 tabular-nums">
                            {{ $stats['total_riders'] }}
                        </p>
                    </div>

                    <div class="rounded-lg bg-gray-bg px-2.5 py-2.5">
                        <p class="text-[8px] text-navy/40 truncate">
                            Active
                        </p>
                        <p class="text-sm font-bold text-navy mt-0.5 tabular-nums">
                            {{ $stats['active_deliveries'] }}
                        </p>
                    </div>

                    <div class="rounded-lg bg-gray-bg px-2.5 py-2.5">
                        <p class="text-[8px] text-navy/40 truncate">
                            Today
                        </p>
                        <p class="text-sm font-bold text-navy mt-0.5 tabular-nums">
                            {{ $stats['completed_today'] }}
                        </p>
                    </div>

                </div>

            </section>


            {{-- Quick Actions --}}
            <section
                data-dashboard-widget="quick-actions"
                data-low-priority="true"
                class="bg-white border border-gray-border rounded-xl dash-card-pad"
            >

                <div class="flex items-center justify-between gap-3 mb-3">

                    <div>

                        <h2 class="text-sm font-bold text-navy">
                            Quick Actions
                        </h2>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Common logistics management tasks.
                        </p>

                    </div>

                    <x-lucide-zap class="w-4 h-4 text-teal-dark" />

                </div>


                <div class="space-y-1">

                    @foreach ([
                        [
                            'route' => 'logistics.riders.index',
                            'label' => 'Manage Riders',
                            'icon' => 'users',
                            'classes' => 'bg-teal/10 text-teal-dark',
                        ],
                        [
                            'route' => 'logistics.deliveries.board',
                            'label' => 'Delivery Board',
                            'icon' => 'package-check',
                            'classes' => 'bg-sky/10 text-sky',
                        ],
                        [
                            'route' => 'logistics.reports.index',
                            'label' => 'View Reports',
                            'icon' => 'chart-no-axes-combined',
                            'classes' => 'bg-amber-50 text-amber-700',
                        ],
                    ] as $action)

                        <a
                            href="{{ route($action['route']) }}"
                            class="group flex items-center gap-3
                                   px-2.5 py-2.5 rounded-lg
                                   hover:bg-gray-bg
                                   transition-colors"
                        >
                            <div
                                class="w-7 h-7 rounded-lg
                                       {{ $action['classes'] }}
                                       flex items-center justify-center
                                       shrink-0
                                       group-hover:scale-105
                                       transition-transform"
                            >
                                <x-dynamic-component
                                    :component="'lucide-' . $action['icon']"
                                    class="w-3.5 h-3.5"
                                />
                            </div>

                            <span class="text-xs font-semibold text-navy flex-1">
                                {{ $action['label'] }}
                            </span>

                            <x-lucide-chevron-right
                                class="w-3.5 h-3.5
                                       text-navy/25
                                       group-hover:text-teal-dark
                                       group-hover:translate-x-0.5
                                       transition-transform"
                            />
                        </a>

                    @endforeach

                </div>

            </section>

        </div>

    </div>


    {{-- =========================================================
        RIDER APPLICATIONS
    ========================================================= --}}
    <section
        id="dashboardRiderApplications"
        data-dashboard-widget="rider-applications"
        data-low-priority="true"
        class="mt-4 sm:mt-5
               bg-white border border-gray-border rounded-xl overflow-hidden"
    >

        <div
            class="flex items-center justify-between gap-4
                   px-4 sm:px-5 py-3.5
                   border-b border-gray-border"
        >

            <div class="min-w-0">

                <div class="flex items-center gap-2">

                    <h2 class="text-sm font-bold text-navy">
                        Rider Applications
                    </h2>

                    @if ($recentRiderApplications->count() > 0)
                        <span
                            class="inline-flex items-center justify-center
                                   min-w-5 h-5 px-1.5
                                   rounded-full bg-gray-bg
                                   text-[9px] font-bold text-navy/45"
                        >
                            {{ $recentRiderApplications->count() }}
                        </span>
                    @endif

                </div>

                <p class="text-[10px] sm:text-xs text-navy/40 mt-0.5">
                    Riders requesting to join your logistics team.
                </p>

            </div>


            <a
                href="{{ route('logistics.riders.index') }}"
                class="inline-flex items-center gap-1
                       text-[10px] sm:text-xs
                       font-semibold text-teal-dark
                       hover:text-navy
                       transition-colors shrink-0"
            >
                View all
                <x-lucide-arrow-right class="w-3 h-3" />
            </a>

        </div>


        <div class="divide-y divide-gray-border">

            @forelse ($recentRiderApplications as $index => $application)

                @php
                    $avatarClasses = $avatarPalette[$index % count($avatarPalette)];
                @endphp

                <div
                    class="dash-row
                           flex items-center justify-between gap-4
                           px-4 sm:px-5
                           hover:bg-gray-bg/60
                           transition-colors"
                >

                    <div class="flex items-center gap-3 min-w-0">

                        <div
                            class="w-9 h-9 rounded-full
                                   {{ $avatarClasses }}
                                   flex items-center justify-center
                                   text-[11px] font-bold uppercase shrink-0"
                        >
                            {{ $application['initials'] }}
                        </div>


                        <div class="min-w-0">

                            <p class="text-xs sm:text-sm font-semibold text-navy truncate">
                                {{ $application['name'] }}
                            </p>

                            {{-- NEW: "applied" text wrapped in a span with a raw timestamp
                                 so JS can keep it accurate without a page reload --}}
                            <p class="text-[9px] sm:text-[10px] text-navy/40 mt-0.5">
                                {{ $application['vehicle'] }} ·
                                <span
                                    @if (!empty($application['applied_at']))
                                        data-applied-at="{{ $application['applied_at'] }}"
                                    @endif
                                >{{ $application['applied'] }}</span>
                            </p>

                        </div>

                    </div>


                    <span
                        class="inline-flex items-center gap-1.5
                               px-2.5 py-1 rounded-full
                               bg-amber-50 text-amber-700
                               text-[9px] font-semibold
                               shrink-0"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Pending
                    </span>

                </div>

            @empty

                <div class="px-5 py-10 text-center">

                    <div class="w-11 h-11 mx-auto rounded-xl bg-teal/10 text-teal-dark flex items-center justify-center">
                        <x-lucide-user-check class="w-4 h-4" />
                    </div>

                    <p class="text-xs font-semibold text-navy/60 mt-3">
                        No pending applications
                    </p>

                </div>

            @endforelse

        </div>

    </section>


    {{-- =========================================================
        BOTTOM SHORTCUTS
    ========================================================= --}}
    <section
        data-dashboard-widget="shortcuts"
        data-low-priority="true"
        class="mt-4 sm:mt-5"
    >

        <div class="grid sm:grid-cols-3 dash-gap">

            @foreach ([
                [
                    'route' => 'logistics.riders.index',
                    'label' => 'Riders',
                    'desc' => 'Review applications and manage your fleet',
                    'icon' => 'bike',
                ],
                [
                    'route' => 'logistics.deliveries.board',
                    'label' => 'Delivery Board',
                    'desc' => 'Assign and monitor deliveries',
                    'icon' => 'package-check',
                ],
                [
                    'route' => 'logistics.reports.index',
                    'label' => 'Reports',
                    'desc' => 'Review logistics performance',
                    'icon' => 'chart-no-axes-combined',
                ],
            ] as $shortcut)

                <a
                    href="{{ route($shortcut['route']) }}"
                    class="group flex items-center gap-3
                           bg-white border border-gray-border
                           rounded-xl px-3.5 py-3
                           hover:border-teal/30 hover:shadow-sm
                           transition-all"
                >
                    <div
                        class="w-8 h-8 rounded-lg
                               bg-gray-bg text-teal-dark
                               flex items-center justify-center
                               shrink-0
                               group-hover:bg-teal/10
                               transition-colors"
                    >
                        <x-dynamic-component
                            :component="'lucide-' . $shortcut['icon']"
                            class="w-4 h-4"
                        />
                    </div>


                    <div class="min-w-0">

                        <p class="text-xs font-semibold text-navy">
                            {{ $shortcut['label'] }}
                        </p>

                        <p class="text-[9px] text-navy/35 mt-0.5 truncate">
                            {{ $shortcut['desc'] }}
                        </p>

                    </div>


                    <x-lucide-arrow-up-right
                        class="w-3.5 h-3.5
                               text-navy/20 ml-auto
                               group-hover:text-teal-dark
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </a>

            @endforeach

        </div>

    </section>


    {{-- =========================================================
        CUSTOMIZE DASHBOARD PANEL
    ========================================================= --}}
    <div
        id="logisticsDashboardCustomizePanel"
        hidden
        class="fixed inset-0 z-[70]"
        aria-hidden="true"
    >

        {{-- Backdrop --}}
        <button
            type="button"
            data-dashboard-customize-close
            class="absolute inset-0 w-full h-full bg-navy/45 backdrop-blur-[1px]"
            aria-label="Close customization panel"
        ></button>


        {{-- Drawer --}}
        <aside
            class="absolute inset-y-0 right-0
                   w-full max-w-sm
                   bg-white
                   border-l border-gray-border
                   shadow-2xl
                   flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-labelledby="logisticsDashboardCustomizeTitle"
        >

            {{-- Header --}}
            <div
                class="flex items-center justify-between gap-4
                       px-5 py-4
                       border-b border-gray-border"
            >

                <div>

                    <p class="text-[9px] font-semibold tracking-[0.15em] uppercase text-teal-dark">
                        Dashboard Preferences
                    </p>

                    <h2
                        id="logisticsDashboardCustomizeTitle"
                        class="text-base font-bold text-navy mt-0.5"
                    >
                        Customize your workspace
                    </h2>

                </div>


                <button
                    type="button"
                    data-dashboard-customize-close
                    class="w-8 h-8 rounded-lg
                           border border-gray-border
                           text-navy/45
                           flex items-center justify-center
                           hover:text-navy hover:bg-gray-bg
                           transition"
                    aria-label="Close"
                >
                    <x-lucide-x class="w-4 h-4" />
                </button>

            </div>


            <div class="dashboard-scrollbar flex-1 overflow-y-auto px-5 py-5 space-y-6">

                {{-- Density --}}
                <section>

                    <div class="mb-3">

                        <h3 class="text-xs font-bold text-navy">
                            Dashboard Density
                        </h3>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Choose how much information fits on screen.
                        </p>

                    </div>


                    <div class="grid grid-cols-2 gap-2">

                        <label class="cursor-pointer">

                            <input
                                type="radio"
                                name="dashboard_density"
                                value="comfortable"
                                data-dashboard-density-option
                                class="peer sr-only"
                                checked
                            >

                            <span
                                class="flex flex-col gap-2
                                       border border-gray-border
                                       rounded-xl p-3
                                       peer-checked:border-teal
                                       peer-checked:bg-teal/5
                                       transition"
                            >
                                <span class="flex items-center gap-2">

                                    <span
                                        class="w-7 h-7 rounded-lg
                                               bg-gray-bg text-navy/55
                                               flex items-center justify-center"
                                    >
                                        <x-lucide-panel-top class="w-3.5 h-3.5" />
                                    </span>

                                    <span class="text-xs font-semibold text-navy">
                                        Comfortable
                                    </span>

                                </span>

                                <span class="text-[9px] text-navy/40">
                                    More breathing room and context.
                                </span>
                            </span>
                        </label>


                        <label class="cursor-pointer">

                            <input
                                type="radio"
                                name="dashboard_density"
                                value="compact"
                                data-dashboard-density-option
                                class="peer sr-only"
                            >

                            <span
                                class="flex flex-col gap-2
                                       border border-gray-border
                                       rounded-xl p-3
                                       peer-checked:border-teal
                                       peer-checked:bg-teal/5
                                       transition"
                            >
                                <span class="flex items-center gap-2">

                                    <span
                                        class="w-7 h-7 rounded-lg
                                               bg-gray-bg text-navy/55
                                               flex items-center justify-center"
                                    >
                                        <x-lucide-rows-3 class="w-3.5 h-3.5" />
                                    </span>

                                    <span class="text-xs font-semibold text-navy">
                                        Compact
                                    </span>

                                </span>

                                <span class="text-[9px] text-navy/40">
                                    Tighter layout for faster scanning.
                                </span>
                            </span>
                        </label>

                    </div>

                </section>


                {{-- Focus Mode --}}
                <section class="pt-5 border-t border-gray-border">

                    <label class="flex items-start justify-between gap-4 cursor-pointer">

                        <span>

                            <span class="flex items-center gap-2 text-xs font-bold text-navy">
                                <x-lucide-scan-eye class="w-3.5 h-3.5 text-teal-dark" />
                                Focus Mode
                            </span>

                            <span class="block text-[10px] text-navy/40 mt-1 max-w-[245px]">
                                Temporarily hide secondary widgets and focus on priority work.
                            </span>

                        </span>


                        <span class="relative inline-flex items-center shrink-0 mt-0.5">

                            <input
                                type="checkbox"
                                data-dashboard-focus-toggle
                                class="peer sr-only"
                            >

                            <span
                                class="w-10 h-5.5 rounded-full
                                       bg-gray-border
                                       peer-checked:bg-teal
                                       transition-colors"
                            ></span>

                            <span
                                class="absolute left-1 top-1
                                       w-3.5 h-3.5 rounded-full
                                       bg-white shadow-sm
                                       peer-checked:translate-x-[18px]
                                       transition-transform"
                            ></span>

                        </span>

                    </label>

                </section>


                {{-- Widget Visibility --}}
                <section class="pt-5 border-t border-gray-border">

                    <div class="mb-3">

                        <h3 class="text-xs font-bold text-navy">
                            Visible Widgets
                        </h3>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Choose which dashboard sections you want to see.
                        </p>

                    </div>


                    <div class="space-y-1">

                        @foreach ([
                            ['key' => 'stats', 'label' => 'Overview Cards', 'icon' => 'layout-dashboard'],
                            ['key' => 'attention', 'label' => 'Needs Attention', 'icon' => 'bell-ring'],
                            ['key' => 'deliveries', 'label' => 'Recent Deliveries', 'icon' => 'package-check'],
                            ['key' => 'snapshot', 'label' => 'Delivery Mix', 'icon' => 'chart-no-axes-column-increasing'],
                            ['key' => 'quick-actions', 'label' => 'Quick Actions', 'icon' => 'zap'],
                            ['key' => 'rider-applications', 'label' => 'Rider Applications', 'icon' => 'user-round-check'],
                            ['key' => 'shortcuts', 'label' => 'Bottom Shortcuts', 'icon' => 'panels-top-left'],
                        ] as $widget)

                            <label
                                class="flex items-center gap-3
                                       px-2.5 py-2.5 rounded-lg
                                       hover:bg-gray-bg
                                       cursor-pointer transition"
                            >

                                <div
                                    class="w-7 h-7 rounded-lg
                                           bg-gray-bg text-teal-dark
                                           flex items-center justify-center"
                                >
                                    <x-dynamic-component
                                        :component="'lucide-' . $widget['icon']"
                                        class="w-3.5 h-3.5"
                                    />
                                </div>


                                <span class="text-xs font-semibold text-navy flex-1">
                                    {{ $widget['label'] }}
                                </span>


                                <span class="relative inline-flex items-center">

                                    <input
                                        type="checkbox"
                                        data-dashboard-widget-toggle="{{ $widget['key'] }}"
                                        class="peer sr-only"
                                        checked
                                    >

                                    <span
                                        class="w-9 h-5 rounded-full
                                               bg-gray-border
                                               peer-checked:bg-teal
                                               transition-colors"
                                    ></span>

                                    <span
                                        class="absolute left-1 top-1
                                               w-3 h-3 rounded-full
                                               bg-white shadow-sm
                                               peer-checked:translate-x-4
                                               transition-transform"
                                    ></span>

                                </span>

                            </label>

                        @endforeach

                    </div>

                </section>

            </div>


            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-border bg-gray-bg/50">

                <button
                    type="button"
                    data-dashboard-reset
                    class="w-full
                           inline-flex items-center justify-center gap-2
                           h-9 rounded-lg
                           border border-gray-border
                           bg-white
                           text-xs font-semibold text-navy
                           hover:border-teal/30
                           hover:text-teal-dark
                           transition"
                >
                    <x-lucide-rotate-ccw class="w-3.5 h-3.5" />
                    Reset dashboard layout
                </button>


                <p class="text-center text-[9px] text-navy/30 mt-2">
                    Preferences are saved only in this browser.
                </p>

            </div>

        </aside>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const dashboard = document.getElementById('logisticsDashboard');
    const panel = document.getElementById('logisticsDashboardCustomizePanel');

    if (!dashboard || !panel) {
        return;
    }


    const storageKey = 'shophop_logistics_dashboard_preferences_v1';

    const defaults = {
        density: 'comfortable',
        focusMode: false,
        widgets: {
            stats: true,
            attention: true,
            deliveries: true,
            snapshot: true,
            'quick-actions': true,
            'rider-applications': true,
            shortcuts: true,
        },
    };


    // NEW: detect whether localStorage actually works (private/incognito
    // mode, disabled storage, or quota-full browsers can throw here). If it
    // doesn't, we fall back to an in-memory store so the panel still works
    // for the current session instead of breaking outright.
    let storageAvailable = true;
    let memoryStore = null;

    (function checkStorage() {
        try {
            const testKey = '__shophop_storage_test__';
            window.localStorage.setItem(testKey, '1');
            window.localStorage.removeItem(testKey);
        } catch (error) {
            storageAvailable = false;
        }
    })();


    function cloneDefaults() {
        return JSON.parse(JSON.stringify(defaults));
    }


    function loadPreferences() {

        try {

            const raw = storageAvailable
                ? window.localStorage.getItem(storageKey)
                : memoryStore;

            const saved = raw ? JSON.parse(raw) : null;

            if (!saved || typeof saved !== 'object') {
                return cloneDefaults();
            }

            return {
                density:
                    saved.density === 'compact'
                        ? 'compact'
                        : 'comfortable',

                focusMode:
                    saved.focusMode === true,

                widgets: {
                    ...defaults.widgets,
                    ...(saved.widgets || {}),
                },
            };

        } catch (error) {

            return cloneDefaults();

        }

    }


    let preferences = loadPreferences();


    function savePreferences() {

        const serialized = JSON.stringify(preferences);

        try {

            if (storageAvailable) {
                window.localStorage.setItem(storageKey, serialized);
            } else {
                memoryStore = serialized;
            }

        } catch (error) {
            // Quota exceeded or storage blocked mid-session — keep working
            // in memory for the rest of this page view instead of erroring.
            storageAvailable = false;
            memoryStore = serialized;
        }

    }


    function applyPreferences() {

        dashboard.setAttribute(
            'data-dashboard-density',
            preferences.density
        );

        dashboard.classList.toggle(
            'dashboard-focus-mode',
            preferences.focusMode
        );


        document
            .querySelectorAll('[data-dashboard-widget]')
            .forEach(function (widget) {

                const key =
                    widget.getAttribute('data-dashboard-widget');

                widget.hidden =
                    preferences.widgets[key] === false;

            });


        document
            .querySelectorAll('[data-dashboard-density-option]')
            .forEach(function (radio) {

                radio.checked =
                    radio.value === preferences.density;

            });


        const focusToggle =
            document.querySelector(
                '[data-dashboard-focus-toggle]'
            );

        if (focusToggle) {
            focusToggle.checked =
                preferences.focusMode;
        }


        document
            .querySelectorAll('[data-dashboard-widget-toggle]')
            .forEach(function (toggle) {

                const key =
                    toggle.getAttribute(
                        'data-dashboard-widget-toggle'
                    );

                toggle.checked =
                    preferences.widgets[key] !== false;

            });

    }


    // ---------------------------------------------------------------
    // NEW: cross-tab sync — if the user tweaks preferences in another
    // open tab, this tab picks up the change without a manual refresh.
    // ---------------------------------------------------------------
    window.addEventListener('storage', function (event) {

        if (event.key !== storageKey) {
            return;
        }

        preferences = loadPreferences();
        applyPreferences();

    });


    // ---------------------------------------------------------------
    // NEW: lightweight toast for copy / refresh feedback
    // ---------------------------------------------------------------
    let toastEl = null;
    let toastTimer = null;

    function showToast(message) {

        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.className = 'dash-toast';
            toastEl.setAttribute('role', 'status');
            toastEl.setAttribute('aria-live', 'polite');
            document.body.appendChild(toastEl);
        }

        toastEl.textContent = message;

        window.requestAnimationFrame(function () {
            toastEl.classList.add('is-visible');
        });

        window.clearTimeout(toastTimer);

        toastTimer = window.setTimeout(function () {
            toastEl.classList.remove('is-visible');
        }, 1800);

    }


    // ---------------------------------------------------------------
    // NEW: click-to-copy delivery reference numbers
    // ---------------------------------------------------------------
    function copyText(text) {

        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }

        // Fallback for older browsers / non-HTTPS contexts.
        return new Promise(function (resolve, reject) {

            try {

                const helper = document.createElement('textarea');
                helper.value = text;
                helper.style.position = 'fixed';
                helper.style.opacity = '0';
                document.body.appendChild(helper);
                helper.focus();
                helper.select();
                document.execCommand('copy');
                document.body.removeChild(helper);
                resolve();

            } catch (error) {
                reject(error);
            }

        });

    }

    document
        .querySelectorAll('[data-copy-reference]')
        .forEach(function (cell) {

            function handleCopy() {

                const value = cell.getAttribute('data-copy-reference');

                copyText(value)
                    .then(function () {
                        showToast('Copied "' + value + '"');
                    })
                    .catch(function () {
                        showToast('Could not copy reference');
                    });

            }

            cell.addEventListener('click', handleCopy);

            cell.addEventListener('keydown', function (event) {

                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    handleCopy();
                }

            });

        });


    // ---------------------------------------------------------------
    // NEW: client-side sortable "Recent Deliveries" table
    // ---------------------------------------------------------------
    const deliveriesTable = document.querySelector('[data-deliveries-table]');

    if (deliveriesTable) {

        const tbody = deliveriesTable.querySelector('tbody');
        const sortableHeaders = deliveriesTable.querySelectorAll('[data-sort-key]');

        sortableHeaders.forEach(function (header) {

            header.addEventListener('click', function () {

                const type = header.getAttribute('data-sort-type') || 'text';
                const currentDirection = header.getAttribute('aria-sort');
                const nextDirection = currentDirection === 'ascending' ? 'descending' : 'ascending';

                // Reset every header's indicator, then mark the active one.
                sortableHeaders.forEach(function (other) {
                    other.setAttribute('aria-sort', 'none');
                });
                header.setAttribute('aria-sort', nextDirection);

                const columnIndex = Array.prototype.indexOf.call(
                    header.parentElement.children,
                    header
                );

                const rows = Array.prototype.slice.call(
                    tbody.querySelectorAll('tr')
                ).filter(function (row) {
                    // Skip the empty-state row (it has a colspan cell).
                    return row.children.length > 1;
                });

                rows.sort(function (a, b) {

                    const cellA = a.children[columnIndex];
                    const cellB = b.children[columnIndex];

                    const rawA = cellA ? (cellA.getAttribute('data-sort-value') || cellA.textContent.trim()) : '';
                    const rawB = cellB ? (cellB.getAttribute('data-sort-value') || cellB.textContent.trim()) : '';

                    let comparison;

                    if (type === 'number') {
                        comparison = parseFloat(rawA) - parseFloat(rawB);
                    } else {
                        comparison = rawA.localeCompare(rawB, undefined, { sensitivity: 'base' });
                    }

                    return nextDirection === 'ascending' ? comparison : -comparison;

                });

                rows.forEach(function (row) {
                    tbody.appendChild(row);
                });

            });

        });

    }


    // ---------------------------------------------------------------
    // NEW: keep "applied X minutes ago" labels accurate without reload
    // ---------------------------------------------------------------
    function timeAgo(isoString) {

        const then = new Date(isoString).getTime();

        if (isNaN(then)) {
            return null;
        }

        const seconds = Math.round((Date.now() - then) / 1000);

        if (seconds < 60) {
            return 'Just now';
        }

        const minutes = Math.round(seconds / 60);

        if (minutes < 60) {
            return minutes + (minutes === 1 ? ' minute ago' : ' minutes ago');
        }

        const hours = Math.round(minutes / 60);

        if (hours < 24) {
            return hours + (hours === 1 ? ' hour ago' : ' hours ago');
        }

        const days = Math.round(hours / 24);

        return days + (days === 1 ? ' day ago' : ' days ago');

    }

    function refreshRelativeTimes() {

        document
            .querySelectorAll('[data-applied-at]')
            .forEach(function (node) {

                const label = timeAgo(node.getAttribute('data-applied-at'));

                if (label) {
                    node.textContent = label;
                }

            });

    }

    refreshRelativeTimes();
    window.setInterval(refreshRelativeTimes, 60000);


    // ---------------------------------------------------------------
    // NEW: optional background stat refresh — only runs if a real
    // endpoint was resolved server-side via data-refresh-url.
    // ---------------------------------------------------------------
    const refreshUrl = dashboard.getAttribute('data-refresh-url');

    function updateLastUpdatedLabel() {

        const badge = document.querySelector('[data-dashboard-last-updated]');

        if (badge) {
            badge.setAttribute('data-updated-at', new Date().toISOString());
        }

    }

    function applyStatUpdates(payload) {

        if (!payload || typeof payload !== 'object') {
            return;
        }

        Object.keys(payload).forEach(function (key) {

            const target = document.querySelector('[data-stat="' + key + '"]');

            if (target) {
                target.textContent = payload[key];
            }

        });

        updateLastUpdatedLabel();

    }

    function pollDashboardStats() {

        if (!refreshUrl || document.hidden) {
            return;
        }

        fetch(refreshUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (response) {
                return response.ok ? response.json() : null;
            })
            .then(applyStatUpdates)
            .catch(function () {
                // Silently skip — the dashboard still works with the
                // server-rendered values if the background refresh fails.
            });

    }

    if (refreshUrl) {
        window.setInterval(pollDashboardStats, 60000);
    }


    // ---------------------------------------------------------------
    // NEW: brief "navigating" state on outbound dashboard links so a
    // slow connection doesn't invite a double click / double navigation
    // ---------------------------------------------------------------
    dashboard
        .querySelectorAll('a[href]:not([href="#"])')
        .forEach(function (link) {

            link.addEventListener('click', function () {

                if (link.target === '_blank') {
                    return;
                }

                link.classList.add('is-navigating');
                link.setAttribute('aria-busy', 'true');

            });

        });




    // ---------------------------------------------------------------
    // Customize panel: open/close + focus trap (NEW) and focus restore
    // ---------------------------------------------------------------
    let lastFocusedElement = null;

    function getFocusableElements() {
        return Array.prototype.slice.call(
            panel.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            )
        ).filter(function (el) {
            return !el.hasAttribute('disabled') && el.offsetParent !== null;
        });
    }

    function trapFocus(event) {

        if (event.key !== 'Tab') {
            return;
        }

        const focusable = getFocusableElements();

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }

    }

    function setCustomizeButtonState(isOpen) {

        document
            .querySelectorAll('[data-dashboard-customize-open]')
            .forEach(function (button) {
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

    }

    function openCustomizePanel(triggerEl) {

        lastFocusedElement = triggerEl || document.activeElement;

        panel.hidden = false;

        panel.setAttribute(
            'aria-hidden',
            'false'
        );

        setCustomizeButtonState(true);

        document.body.style.overflow = 'hidden';

        panel.addEventListener('keydown', trapFocus);

        window.setTimeout(function () {

            const closeButton =
                panel.querySelector(
                    '[data-dashboard-customize-close]'
                );

            closeButton?.focus();

        }, 0);

    }


    function closeCustomizePanel() {

        panel.hidden = true;

        panel.setAttribute(
            'aria-hidden',
            'true'
        );

        setCustomizeButtonState(false);

        document.body.style.overflow = '';

        panel.removeEventListener('keydown', trapFocus);

        // Return focus to whatever opened the panel, so keyboard users
        // don't lose their place on the page.
        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
        }

    }


    document
        .querySelectorAll('[data-dashboard-customize-open]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {
                    openCustomizePanel(button);
                }
            );

        });


    panel
        .querySelectorAll('[data-dashboard-customize-close]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                closeCustomizePanel
            );

        });


    document
        .querySelectorAll('[data-dashboard-density-option]')
        .forEach(function (radio) {

            radio.addEventListener('change', function () {

                if (!radio.checked) {
                    return;
                }

                preferences.density =
                    radio.value === 'compact'
                        ? 'compact'
                        : 'comfortable';

                savePreferences();
                applyPreferences();

            });

        });


    const focusToggle =
        document.querySelector(
            '[data-dashboard-focus-toggle]'
        );

    if (focusToggle) {

        focusToggle.addEventListener('change', function () {

            preferences.focusMode =
                focusToggle.checked;

            savePreferences();
            applyPreferences();

        });

    }


    document
        .querySelectorAll('[data-dashboard-widget-toggle]')
        .forEach(function (toggle) {

            toggle.addEventListener('change', function () {

                const key =
                    toggle.getAttribute(
                        'data-dashboard-widget-toggle'
                    );

                preferences.widgets[key] =
                    toggle.checked;

                savePreferences();
                applyPreferences();

            });

        });


    const resetButton =
        document.querySelector('[data-dashboard-reset]');

    if (resetButton) {

        resetButton.addEventListener('click', function () {

            preferences = cloneDefaults();

            savePreferences();
            applyPreferences();
            showToast('Dashboard layout reset');

        });

    }


    document.addEventListener('keydown', function (event) {

        if (
            event.key === 'Escape' &&
            panel.hidden === false
        ) {
            closeCustomizePanel();
        }

    });


    applyPreferences();

});
</script>

@endsection