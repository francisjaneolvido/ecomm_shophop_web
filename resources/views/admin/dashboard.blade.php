@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    | No new controller variables are required for this dashboard version.
    */
    $pendingRegistrations = $pendingRegistrations ?? 0;
    $activeUserAccounts = $activeUserAccounts ?? 0;
    $openDisputes = $openDisputes ?? 0;
    $commissionThisMonth = $commissionThisMonth ?? 0;
    $recentRegistrations = collect($recentRegistrations ?? []);

    $authUser = auth()->user();

    $adminName = $adminName
        ?? $authUser?->name
        ?? ($authUser?->email ? explode('@', $authUser->email)[0] : null)
        ?? 'Admin';

    $needsAttention = $pendingRegistrations + $openDisputes;

    $registrationMix = [
        'buyer' => $recentRegistrations->where('account_type', 'buyer')->count(),
        'seller' => $recentRegistrations->where('account_type', 'seller')->count(),
        'logistics' => $recentRegistrations->where('account_type', 'logistics')->count(),
    ];

    $registrationMixTotal = array_sum($registrationMix);
    $registrationMixMax = max(1, ...array_values($registrationMix));

    $todayRegistrations = $recentRegistrations
        ->filter(fn ($user) => $user->created_at?->isToday())
        ->count();

    $pendingRecent = $recentRegistrations
        ->where('status', 'pending')
        ->count();

    $approvedRecent = $recentRegistrations
        ->where('status', 'approved')
        ->count();

    $recentApprovalRate = $recentRegistrations->count() > 0
        ? round(($approvedRecent / $recentRegistrations->count()) * 100)
        : 0;

    $typeConfig = [
        'buyer' => [
            'label' => 'Buyer',
            'icon' => 'user',
            'classes' => 'bg-sky/10 text-sky',
            'bar' => 'bg-sky',
        ],
        'seller' => [
            'label' => 'Seller',
            'icon' => 'store',
            'classes' => 'bg-teal/10 text-teal-dark',
            'bar' => 'bg-teal',
        ],
        'logistics' => [
            'label' => 'Logistics',
            'icon' => 'truck',
            'classes' => 'bg-amber-50 text-amber-700',
            'bar' => 'bg-amber-500',
        ],
    ];

    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'approved' => 'bg-teal/10 text-teal-dark border-teal/20',
        'rejected' => 'bg-red-50 text-red-600 border-red-200',
        'suspended' => 'bg-red-50 text-red-600 border-red-200',
    ];

    $hour = now()->hour;

    $greeting = $hour < 12
        ? 'Good morning'
        : ($hour < 18 ? 'Good afternoon' : 'Good evening');

    $avatarPalette = [
        'bg-teal/15 text-teal-dark',
        'bg-sky/15 text-sky',
        'bg-amber-50 text-amber-700',
        'bg-navy/10 text-navy',
    ];
@endphp


<style>
    #adminDashboard {
        --dash-gap: 1rem;
        --dash-section-gap: 1.25rem;
        --dash-card-pad: 1rem;
        --dash-row-pad: .8rem;
    }

    #adminDashboard[data-dashboard-density="compact"] {
        --dash-gap: .75rem;
        --dash-section-gap: 1rem;
        --dash-card-pad: .8rem;
        --dash-row-pad: .65rem;
    }

    #adminDashboard .dash-gap {
        gap: var(--dash-gap);
    }

    #adminDashboard .dash-section {
        margin-bottom: var(--dash-section-gap);
    }

    #adminDashboard .dash-card-pad {
        padding: var(--dash-card-pad);
    }

    #adminDashboard .dash-row {
        padding-top: var(--dash-row-pad);
        padding-bottom: var(--dash-row-pad);
    }

    #adminDashboard[data-dashboard-density="compact"] .density-hide-compact {
        display: none;
    }

    #adminDashboard.dashboard-focus-mode [data-low-priority="true"] {
        display: none !important;
    }

    #adminDashboard [data-dashboard-widget][hidden] {
        display: none !important;
    }

    #adminDashboard .dashboard-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(15, 27, 61, .18) transparent;
    }

    #adminDashboard .dashboard-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    #adminDashboard .dashboard-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(15, 27, 61, .16);
        border-radius: 999px;
    }

    #dashboardCustomizePanel[hidden] {
        display: none !important;
    }
</style>


<div
    id="adminDashboard"
    data-dashboard-density="comfortable"
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
                        ShopHop Admin
                    </p>
                </div>


                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">

                    <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                        {{ $greeting }}, {{ $adminName }}
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
                    Monitor registrations, accounts, disputes, and platform activity from one workspace.
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <div
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
                    href="{{ route('admin.reports') }}"
                    class="inline-flex items-center justify-center gap-1.5
                           h-9 px-3.5 rounded-lg
                           bg-navy hover:bg-navy/90
                           text-xs font-semibold text-white
                           transition-colors"
                >
                    <x-lucide-chart-no-axes-combined class="w-3.5 h-3.5" />
                    Reports
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

            {{-- Pending Registrations --}}
            <a
                href="{{ route('admin.registrations') }}"
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
                        <x-lucide-user-round-check class="w-4 h-4" />
                    </div>


                    @if ($pendingRegistrations > 0)
                        <span
                            class="inline-flex items-center gap-1
                                   text-[9px] font-semibold
                                   text-teal-dark bg-teal/10
                                   px-2 py-1 rounded-full"
                        >
                            <span class="w-1 h-1 rounded-full bg-teal"></span>
                            Review
                        </span>
                    @endif

                </div>


                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums">
                        {{ number_format($pendingRegistrations) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Pending Registrations
                    </p>

                </div>


                <div
                    class="density-hide-compact
                           flex items-center mt-2
                           text-[9px] sm:text-[10px] text-navy/35"
                >
                    <span>{{ $todayRegistrations }} recent today</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-teal-dark
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>


            {{-- Active Accounts --}}
            <a
                href="{{ route('admin.users') }}"
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
                        <x-lucide-users class="w-4 h-4" />
                    </div>


                    <span
                        class="text-[9px] font-semibold
                               text-navy/40 bg-gray-bg
                               px-2 py-1 rounded-full"
                    >
                        Accounts
                    </span>

                </div>


                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums">
                        {{ number_format($activeUserAccounts) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Active User Accounts
                    </p>

                </div>


                <div
                    class="density-hide-compact
                           flex items-center mt-2
                           text-[9px] sm:text-[10px] text-navy/35"
                >
                    <span>Platform users</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-sky
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>


            {{-- Open Disputes --}}
            <a
                href="{{ route('admin.disputes') }}"
                class="group relative overflow-hidden
                       bg-white border border-gray-border
                       rounded-xl dash-card-pad
                       hover:border-red-200
                       hover:shadow-lg hover:shadow-red-500/5
                       hover:-translate-y-0.5
                       transition-all duration-200"
            >
                <span
                    class="absolute inset-x-4 top-0 h-0.5 rounded-full
                           bg-red-400
                           scale-x-0 group-hover:scale-x-100
                           transition-transform origin-left"
                ></span>

                <div class="flex items-start justify-between gap-3">

                    <div
                        class="w-9 h-9 rounded-lg
                               bg-red-50 text-red-500
                               flex items-center justify-center
                               group-hover:bg-red-500 group-hover:text-white
                               transition-colors"
                    >
                        <x-lucide-message-square-warning class="w-4 h-4" />
                    </div>


                    @if ($openDisputes > 0)
                        <span
                            class="inline-flex items-center gap-1
                                   text-[9px] font-semibold
                                   text-red-600 bg-red-50
                                   px-2 py-1 rounded-full"
                        >
                            <span class="w-1 h-1 rounded-full bg-red-500"></span>
                            Attention
                        </span>
                    @endif

                </div>


                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums">
                        {{ number_format($openDisputes) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Open Complaints & Disputes
                    </p>

                </div>


                <div
                    class="density-hide-compact
                           flex items-center mt-2
                           text-[9px] sm:text-[10px] text-navy/35"
                >
                    <span>
                        {{ $openDisputes > 0 ? 'Needs resolution' : 'No urgent cases' }}
                    </span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-red-500
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>


            {{-- Commission --}}
            <a
                href="{{ route('admin.commission') }}"
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
                        <x-lucide-wallet-cards class="w-4 h-4" />
                    </div>


                    <span
                        class="text-[9px] font-semibold
                               text-amber-700 bg-amber-50
                               px-2 py-1 rounded-full"
                    >
                        10%
                    </span>

                </div>


                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums">
                        ₱{{ number_format($commissionThisMonth) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Commission This Month
                    </p>

                </div>


                <div
                    class="density-hide-compact
                           flex items-center mt-2
                           text-[9px] sm:text-[10px] text-navy/35"
                >
                    <span>Platform earnings</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-amber-700
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
                                class="inline-flex items-center justify-center
                                       min-w-5 h-5 px-1.5
                                       rounded-full bg-teal
                                       text-white text-[9px] font-bold"
                            >
                                {{ $needsAttention }}
                            </span>
                        @else
                            <span
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
                        Review items that may require an approval, decision, or follow-up.
                    </p>

                </div>

            </div>


            <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">

                <a
                    href="{{ route('admin.registrations') }}"
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
                            Registrations
                        </p>

                        <p class="text-xs font-semibold text-white truncate">
                            {{ $pendingRegistrations }} pending
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
                    href="{{ route('admin.disputes') }}"
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
                            Disputes
                        </p>

                        <p class="text-xs font-semibold text-white truncate">
                            {{ $openDisputes }} open
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
            RECENT REGISTRATIONS
        ===================================================== --}}
        <section
            data-dashboard-widget="registrations"
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
                            Recent Registrations
                        </h2>

                        @if ($recentRegistrations->count() > 0)
                            <span
                                class="inline-flex items-center justify-center
                                       min-w-5 h-5 px-1.5
                                       rounded-full bg-gray-bg
                                       text-[9px] font-bold text-navy/45"
                            >
                                {{ $recentRegistrations->count() }}
                            </span>
                        @endif

                    </div>

                    <p class="text-[10px] sm:text-xs text-navy/40 mt-0.5">
                        Latest buyer, seller, and logistics applications.
                    </p>

                </div>


                <a
                    href="{{ route('admin.registrations') }}"
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

                @forelse ($recentRegistrations as $index => $user)

                    @php
                        $type = strtolower($user->account_type ?? '');

                        $config = $typeConfig[$type] ?? [
                            'label' => ucfirst($type ?: 'User'),
                            'icon' => 'user',
                            'classes' => 'bg-gray-bg text-navy/50',
                            'bar' => 'bg-navy/30',
                        ];

                        $status = strtolower($user->status ?? 'pending');

                        $statusClass = $statusClasses[$status]
                            ?? 'bg-gray-bg text-navy/50 border-gray-border';

                        $displayName = trim($user->display_name ?? 'User');

                        $initials = collect(explode(' ', $displayName))
                            ->filter()
                            ->map(fn ($part) => mb_substr($part, 0, 1))
                            ->take(2)
                            ->implode('');

                        $avatarClasses = $avatarPalette[$index % count($avatarPalette)];
                    @endphp


                    <div
                        class="dash-row group
                               flex items-center gap-3
                               px-4 sm:px-5
                               hover:bg-gray-bg/60
                               transition-colors"
                    >

                        {{-- Avatar --}}
                        <div class="relative shrink-0">

                            <div
                                class="w-9 h-9 rounded-full
                                       {{ $avatarClasses }}
                                       flex items-center justify-center
                                       text-[11px] font-bold uppercase"
                            >
                                {{ $initials ?: 'U' }}
                            </div>


                            <div
                                class="absolute -bottom-1 -right-1
                                       w-4 h-4 rounded-md
                                       {{ $config['classes'] }}
                                       flex items-center justify-center
                                       ring-2 ring-white"
                            >
                                <x-dynamic-component
                                    :component="'lucide-' . $config['icon']"
                                    class="w-2.5 h-2.5"
                                />
                            </div>

                        </div>


                        {{-- User details --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">

                                <p class="text-xs sm:text-sm font-semibold text-navy truncate">
                                    {{ $displayName }}
                                </p>


                                <span
                                    class="inline-flex items-center
                                           text-[8px] sm:text-[9px]
                                           font-semibold
                                           px-1.5 py-0.5 rounded-md
                                           {{ $config['classes'] }}"
                                >
                                    {{ $config['label'] }}
                                </span>

                            </div>


                            <p class="text-[9px] sm:text-[10px] text-navy/40 mt-0.5">
                                Submitted {{ $user->created_at?->diffForHumans() ?? 'recently' }}
                            </p>

                        </div>


                        {{-- Status --}}
                        <span
                            class="hidden sm:inline-flex items-center
                                   border px-2 py-1 rounded-full
                                   text-[9px] font-semibold
                                   {{ $statusClass }}"
                        >
                            {{ ucfirst($status) }}
                        </span>


                        {{-- Review --}}
                        <a
                            href="{{ route('admin.registrations') }}"
                            class="inline-flex items-center justify-center
                                   w-8 h-8 rounded-lg
                                   border border-gray-border
                                   text-navy/45
                                   hover:border-teal/30
                                   hover:bg-teal/5
                                   hover:text-teal-dark
                                   transition-colors shrink-0"
                            aria-label="Review registration"
                            title="Review registration"
                        >
                            <x-lucide-chevron-right class="w-3.5 h-3.5" />
                        </a>

                    </div>

                @empty

                    <div class="px-5 py-12 text-center">

                        <div
                            class="w-11 h-11 mx-auto
                                   rounded-xl bg-teal/10 text-teal-dark
                                   flex items-center justify-center"
                        >
                            <x-lucide-users class="w-4 h-4" />
                        </div>

                        <p class="text-xs font-semibold text-navy/60 mt-3">
                            No registrations yet
                        </p>

                        <p class="text-[10px] text-navy/35 mt-1 max-w-[230px] mx-auto">
                            New buyer, seller, and logistics applications will appear here.
                        </p>

                    </div>

                @endforelse

            </div>

        </section>


        {{-- =====================================================
            RIGHT SIDEBAR
        ===================================================== --}}
        <div class="space-y-4">

            {{-- Registration Snapshot --}}
            <section
                data-dashboard-widget="snapshot"
                data-low-priority="true"
                class="bg-white border border-gray-border rounded-xl dash-card-pad"
            >

                <div class="flex items-start justify-between gap-3 mb-4">

                    <div>

                        <h2 class="text-sm font-bold text-navy">
                            Registration Snapshot
                        </h2>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Mix of the latest applications.
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
                            'label' => 'Buyers',
                            'count' => $registrationMix['buyer'],
                            'icon' => 'user',
                            'classes' => 'bg-sky/10 text-sky',
                            'bar' => 'bg-sky',
                        ],
                        [
                            'label' => 'Sellers',
                            'count' => $registrationMix['seller'],
                            'icon' => 'store',
                            'classes' => 'bg-teal/10 text-teal-dark',
                            'bar' => 'bg-teal',
                        ],
                        [
                            'label' => 'Logistics',
                            'count' => $registrationMix['logistics'],
                            'icon' => 'truck',
                            'classes' => 'bg-amber-50 text-amber-700',
                            'bar' => 'bg-amber-500',
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
                                            ? max(6, round(($item['count'] / $registrationMixMax) * 100))
                                            : 0 }}%"
                                ></div>

                            </div>

                        </div>

                    @endforeach

                </div>


                <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-gray-border">

                    <div class="rounded-lg bg-gray-bg px-2.5 py-2.5">
                        <p class="text-[8px] text-navy/40 truncate">
                            Pending
                        </p>
                        <p class="text-sm font-bold text-navy mt-0.5 tabular-nums">
                            {{ $pendingRecent }}
                        </p>
                    </div>

                    <div class="rounded-lg bg-gray-bg px-2.5 py-2.5">
                        <p class="text-[8px] text-navy/40 truncate">
                            Today
                        </p>
                        <p class="text-sm font-bold text-navy mt-0.5 tabular-nums">
                            {{ $todayRegistrations }}
                        </p>
                    </div>

                    <div class="rounded-lg bg-gray-bg px-2.5 py-2.5">
                        <p class="text-[8px] text-navy/40 truncate">
                            Approved
                        </p>
                        <p class="text-sm font-bold text-navy mt-0.5 tabular-nums">
                            {{ $recentApprovalRate }}%
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
                            Common admin tasks.
                        </p>

                    </div>

                    <x-lucide-zap class="w-4 h-4 text-teal-dark" />

                </div>


                <div class="space-y-1">

                    @foreach ([
                        [
                            'route' => 'admin.registrations',
                            'label' => 'Review Registrations',
                            'icon' => 'user-round-check',
                            'classes' => 'bg-teal/10 text-teal-dark',
                        ],
                        [
                            'route' => 'admin.disputes',
                            'label' => 'Resolve Disputes',
                            'icon' => 'message-square-warning',
                            'classes' => 'bg-red-50 text-red-500',
                        ],
                        [
                            'route' => 'admin.compliance',
                            'label' => 'Seller Compliance',
                            'icon' => 'badge-check',
                            'classes' => 'bg-sky/10 text-sky',
                        ],
                        [
                            'route' => 'admin.reports',
                            'label' => 'Generate Report',
                            'icon' => 'file-chart-column-increasing',
                            'classes' => 'bg-amber-50 text-amber-700',
                        ],
                        [
                            'route' => 'admin.settings',
                            'label' => 'Platform Settings',
                            'icon' => 'settings',
                            'classes' => 'bg-gray-bg text-navy/55',
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
        BOTTOM SHORTCUTS
    ========================================================= --}}
    <section
        data-dashboard-widget="shortcuts"
        data-low-priority="true"
        class="mt-4 sm:mt-5"
    >

        <div class="grid sm:grid-cols-2 xl:grid-cols-4 dash-gap">

            @foreach ([
                [
                    'route' => 'admin.users',
                    'label' => 'User Accounts',
                    'desc' => 'Manage platform users',
                    'icon' => 'users',
                ],
                [
                    'route' => 'admin.chat',
                    'label' => 'Admin Chat',
                    'desc' => 'Support conversations',
                    'icon' => 'messages-square',
                ],
                [
                    'route' => 'admin.commission',
                    'label' => 'Commission',
                    'desc' => 'Review platform earnings',
                    'icon' => 'landmark',
                ],
                [
                    'route' => 'admin.settings',
                    'label' => 'Platform Settings',
                    'desc' => 'Configure ShopHop',
                    'icon' => 'sliders-horizontal',
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
        id="dashboardCustomizePanel"
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
            aria-labelledby="dashboardCustomizeTitle"
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
                        id="dashboardCustomizeTitle"
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
                            ['key' => 'registrations', 'label' => 'Recent Registrations', 'icon' => 'user-round-check'],
                            ['key' => 'snapshot', 'label' => 'Registration Snapshot', 'icon' => 'chart-no-axes-column-increasing'],
                            ['key' => 'quick-actions', 'label' => 'Quick Actions', 'icon' => 'zap'],
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

    const dashboard = document.getElementById('adminDashboard');
    const panel = document.getElementById('dashboardCustomizePanel');

    if (!dashboard || !panel) {
        return;
    }


    const storageKey = 'shophop_admin_dashboard_preferences_v2';

    const defaults = {
        density: 'comfortable',
        focusMode: false,
        widgets: {
            stats: true,
            attention: true,
            registrations: true,
            snapshot: true,
            'quick-actions': true,
            shortcuts: true,
        },
    };


    function cloneDefaults() {
        return JSON.parse(JSON.stringify(defaults));
    }


    function loadPreferences() {

        try {

            const saved = JSON.parse(localStorage.getItem(storageKey));

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
        localStorage.setItem(
            storageKey,
            JSON.stringify(preferences)
        );
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


    function openCustomizePanel() {

        panel.hidden = false;

        panel.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.style.overflow = 'hidden';

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

        document.body.style.overflow = '';

    }


    document
        .querySelectorAll('[data-dashboard-customize-open]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                openCustomizePanel
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