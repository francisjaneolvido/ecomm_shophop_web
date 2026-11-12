<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Apply the shared light appearance before the shell paints. --}}
    @include('partials.theme-head')

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - ShopHop Branch</title>

    {{-- Tailwind + theme tokens (kapareho ng logistics console) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#10193C',
                        'navy-light': '#26305A',
                        'navy-soft': '#26305A',
                        teal: '#EDA3AE',
                        'teal-dark': '#E1909E',
                        'teal-light': '#FCECEF',
                        mint: '#EDA3AE',
                        'mint-dark': '#E1909E',
                        sky: '#4AA8E0',
                        yellow: '#F7C948',
                        coral: '#FF7A59',
                        'gray-bg': '#FBF6EE',
                        'gray-border': '#E2E6EA',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        soft: '0 10px 30px rgba(16,25,60, 0.08)',
                        panel: '0 18px 50px rgba(16,25,60, 0.12)',
                    },
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { color-scheme: light; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; }

        .logistics-scrollbar { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.16) transparent; }
        .logistics-scrollbar::-webkit-scrollbar { width: 6px; }
        .logistics-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.16); border-radius: 999px; }
        .content-scrollbar { scrollbar-width: thin; scrollbar-color: rgba(16,25,60,.15) transparent; }
        .content-scrollbar::-webkit-scrollbar { width: 7px; }
        .content-scrollbar::-webkit-scrollbar-thumb { background: rgba(16,25,60,.14); border-radius: 999px; }

        #branchMobileOverlay[hidden], #branchSearchPalette[hidden], #branchConfirmModal[hidden] { display: none !important; }

        /* Desktop collapse */
        #branchSidebar[data-collapsed="true"] { width: 5rem; }
        #branchSidebar .sidebar-collapsed-only { display: none; }
        #branchSidebar[data-collapsed="true"] .sidebar-expanded-only { display: none; }
        #branchSidebar[data-collapsed="true"] .sidebar-collapsed-only { display: flex; }
        #branchSidebar[data-collapsed="true"] .sidebar-nav-link { justify-content: center; padding-left: .75rem; padding-right: .75rem; }
        #branchSidebar[data-collapsed="true"] .sidebar-brand-wrap { justify-content: center; padding-left: .75rem; padding-right: .75rem; }
        #branchSidebar[data-collapsed="true"] .sidebar-tooltip { display: block; }
        .sidebar-tooltip { display: none; }

        /* Mobile slide-in */
        @media (max-width: 1023px) {
            #branchSidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 17rem !important; z-index: 60; transform: translateX(-100%); transition: transform .22s ease; }
            #branchShell[data-mobile-open="true"] #branchSidebar { transform: translateX(0); }
            #branchSidebar[data-collapsed="true"] .sidebar-expanded-only { display: initial; }
            #branchSidebar[data-collapsed="true"] .sidebar-nav-link { justify-content: flex-start; padding-left: .75rem; padding-right: .75rem; }
            #branchSidebar[data-collapsed="true"] .sidebar-brand-wrap { justify-content: flex-start; padding-left: 1.25rem; padding-right: 1.25rem; }
            #branchSidebar .sidebar-tooltip { display: none !important; }
            .sidebar-collapsed-only { display: none !important; }
        }

        .branch-profile-menu > summary::-webkit-details-marker { display: none; }
        .branch-profile-menu > summary::marker { content: ''; }
        .branch-console :is(button, a, input, select, textarea, summary):focus-visible { outline: 2px solid #10193C; outline-offset: 3px; }
        .branch-console main :is(input:not([type="checkbox"]):not([type="radio"]), select, textarea) { max-width: 100%; min-height: 2.5rem; background-color: #fff; }
        .branch-console main :is(input, select, textarea):focus { border-color: #EDA3AE; box-shadow: 0 0 0 3px rgba(237,163,174,.12); outline: none; }
        @media (prefers-reduced-motion: reduce) {
            .branch-console * { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>

    <style>
        /* ShopHop shared component system (kapareho ng logistics console) */
        .sh-page-header { display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:1rem; }
        .sh-page-header .eyebrow { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.16em; color:#10193C; }
        .sh-page-header .title { margin-top:.25rem; font-size:1.75rem; line-height:1.15; font-weight:700; color:#10193C; letter-spacing:-.02em; }
        .sh-page-header .description { margin-top:.4rem; max-width:42rem; font-size:.875rem; line-height:1.7; color:rgba(16,25,60,.58); }
        .sh-surface { border:1px solid rgba(16,25,60,0.150); border-radius:1.5rem; background:#fff; box-shadow:0 10px 30px rgba(16,25,60,.06); }
        .sh-surface-soft { border:1px solid rgba(16,25,60,0.150); border-radius:1.25rem; background:#FFFAF6; }
        .sh-card-pad { padding:1.125rem; }
        @media (min-width: 640px){ .sh-card-pad { padding:1.375rem; } }
        .sh-stat { border:1px solid rgba(16,25,60,0.150); border-radius:1.25rem; background:#fff; padding:1rem; box-shadow:0 8px 20px rgba(16,25,60,.05); }
        .sh-stat-label { font-size:.68rem; text-transform:uppercase; letter-spacing:.12em; font-weight:700; color:rgba(16,25,60,.42); }
        .sh-stat-value { margin-top:.35rem; font-size:1.7rem; font-weight:700; line-height:1; color:#10193C; }
        .sh-stat-note { margin-top:.35rem; font-size:.72rem; color:rgba(16,25,60,.5); }
        .sh-btn, .sh-btn-secondary, .sh-btn-ghost, .sh-btn-danger, .sh-btn-soft {
            min-height:2.6rem; display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
            border-radius:999px; padding:.68rem 1rem; font-size:.78rem; font-weight:700; transition:all .18s ease;
            border:1px solid transparent; text-decoration:none;
        }
        .sh-btn { background:#10193C; color:#fff; box-shadow:0 10px 25px rgba(16,25,60,.12); }
        .sh-btn:hover { background:#10193C; transform:translateY(-1px); }
        .sh-btn-secondary { background:#FCECEF; color:#10193C; }
        .sh-btn-secondary:hover { background:#FCECEF; transform:translateY(-1px); }
        .sh-btn-ghost { background:#fff; color:#10193C; border-color:#DCE2E7; }
        .sh-btn-ghost:hover { border-color:rgba(237,163,174,.35); color:#10193C; background:#FCECEF; }
        .sh-btn-soft { background:#FBF6EE; color:#10193C; border-color:rgba(16,25,60,0.150); }
        .sh-btn-soft:hover { background:#FCECEF; color:#10193C; }
        .sh-btn-danger { background:#fff; color:#C24141; border-color:#F3CACA; }
        .sh-btn-danger:hover { background:#FEF2F2; }
        .sh-input, .sh-select, .sh-textarea {
            width:100%; border-radius:1rem; border:1px solid rgba(16,25,60,0.150); background:#fff; color:#10193C;
            padding:.7rem .9rem; font-size:.9rem; line-height:1.4; box-shadow: inset 0 1px 2px rgba(16,25,60,.02);
        }
        .sh-input::placeholder, .sh-textarea::placeholder { color:rgba(16,25,60,.33); }
        .sh-select, .sh-input, .sh-textarea { transition:border-color .16s ease, box-shadow .16s ease, background-color .16s ease; }
        .sh-field { display:grid; gap:.42rem; }
        .sh-label { font-size:.73rem; font-weight:700; color:#10193C; }
        .sh-badge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.38rem .72rem; font-size:.66rem; font-weight:700; }
        .sh-badge-info { background:#EDF7FF; color:#2664A9; }
        .sh-badge-success { background:#EAF7EE; color:#166534; }
        .sh-badge-warning { background:#FFF6DD; color:#A46700; }
        .sh-badge-danger { background:#FEF2F2; color:#B42318; }
        .sh-badge-neutral { background:#F2F4F7; color:#475467; }
        .sh-stack { display:grid; gap:1rem; }
        .sh-toolbar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem; }
        .sh-empty { border:1px dashed rgba(16,25,60,0.150); border-radius:1.25rem; background:#fff; padding:2rem 1.25rem; text-align:center; }
        .sh-table-wrap { overflow-x:auto; }
        .sh-table { width:100%; min-width:720px; border-collapse:separate; border-spacing:0; }
        .sh-table thead th { background:#F7F9FA; padding:.9rem 1rem; font-size:.67rem; font-weight:700; color:rgba(16,25,60,.45); text-transform:uppercase; letter-spacing:.12em; text-align:left; border-bottom:1px solid rgba(16,25,60,0.150); }
        .sh-table tbody td { padding:1rem; font-size:.82rem; color:#10193C; border-bottom:1px solid rgba(226,230,234,.8); vertical-align:top; }
        .sh-alert-success, .sh-alert-error { border-radius:1rem; padding:.95rem 1rem; font-size:.82rem; border:1px solid transparent; }
        .sh-alert-success { background:#EAF7EE; color:#166534; border-color:#EAF7EE; }
        .sh-alert-error { background:#FEF2F2; color:#B42318; border-color:#F6C4C4; }
        .sh-summary { list-style:none; cursor:pointer; }
        .sh-summary::-webkit-details-marker { display:none; }
        .sh-modal-backdrop { position:fixed; inset:0; background:rgba(16,25,60,.55); backdrop-filter: blur(3px); }
        .sh-modal-card { position:relative; width:min(100%, 29rem); border-radius:1.5rem; background:#fff; border:1px solid rgba(16,25,60,0.150); box-shadow:0 30px 80px rgba(16,25,60,.24); overflow:hidden; }
        .sh-modal-head { padding:1.1rem 1.25rem; border-bottom:1px solid rgba(16,25,60,0.150); }
        .sh-modal-actions { padding:1rem 1.25rem 1.25rem; display:flex; justify-content:flex-end; gap:.65rem; }
    </style>

    {{-- Phase 3–5: shared compact logistics workspaces (Partner / Hub / Branch). --}}
    <link rel="stylesheet" href="{{ asset('css/logistics-ux-phase345.css') }}?v=20261112-navy-pink-2">
    @stack('styles')
    {{-- Shared required-field styling; no field schema changes. --}}
    <link rel="stylesheet" href="{{ asset('css/shophop-required-hubs.css') }}?v=20261112-navy-pink-2">
    {{-- ShopHop UI Option A: compact rectangular components across all portals. --}}
    <link rel="stylesheet" href="{{ asset('css/shophop-compact-ui.css') }}?v=20261112-navy-pink-2">
    <link rel="stylesheet" href="{{ asset('css/shophop-operations-table.css') }}?v=20261112-navy-pink-2">
    {{-- Unified management design system: loaded last to prevent styles silently disappearing. --}}
    <link rel="stylesheet" href="{{ asset('css/shophop-management-unified.css') }}?v=20261112-navy-pink-2">
    @include('partials.console-assets')
@include('partials.brand-assets')
</head>

@php
    $branchStaff = request()->routeIs('logistics.staff.*') ? auth('logistics_staff')->user() : null;
    $branchUser = $branchStaff ?? auth()->user();

    // Kung walang ipinasa ang controller na $center, hanapin ang branch ng naka-login.
    $branchCenter = $center
        ?? $branchStaff?->sortingCenter
        ?? ($branchUser
            ? \App\Models\Logistics\SortingCenter::where('user_id', $branchUser->id)
                ->where('status', 'active')->whereNull('archived_at')->first()
            : null);

    $branchName  = $branchCenter->name ?? 'Branch';
    $branchCode  = $branchCenter->code ?? 'Sorting center';
    $branchEmail = $branchUser?->email ?? 'branch@shophop.com';
    $userName    = $branchUser?->name ?? $branchName;

    $branchInitials = collect(preg_split('/[\s._-]+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('') ?: 'BR';

    $routeUrl = fn (string $name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';
    $logoutUrl = $branchStaff ? route('logistics.logout') : (\Illuminate\Support\Facades\Route::has('branch.logout')
        ? route('branch.logout')
        : (\Illuminate\Support\Facades\Route::has('logistics.logout') ? route('logistics.logout') : '#'));

    /*
     * Station Hub (Branch) modules, ayon sa spec.
     *  - 'route'   : pangalan ng route. Kapag may ganitong route na, kusang nagiging live ang item.
     *  - 'pattern' : kung kailan naka-highlight (null = hindi kailanman).
     *  - 'hash'    : anchor sa loob ng page (opsyonal).
     *  - 'hold'    : true = patayin muna kahit may route (sira o sample pa ang page).
     */
    $isMainHub = (bool) $branchCenter?->is_main;
    $portalLabel = $isMainHub ? 'Main Hub' : ($branchCenter?->type === 'province_hub' ? 'Province Hub' : 'Station Branch');
    $branchNavigation = [
        ['label'=>'Overview','items'=>[
            ['label'=>'Dashboard','route'=>'branch.dashboard','pattern'=>'branch.dashboard','icon'=>'layout-dashboard'],
        ]],
        ['label'=>'Operations','items'=>[
            ['label'=>'Parcel Tracking','route'=>'branch.parcels.index','pattern'=>'branch.parcels*','icon'=>'package-search'],
            ['label'=>'Rider Dispatch','route'=>'branch.dispatch.index','pattern'=>'branch.dispatch*','icon'=>'bike'],
            ['label'=>'Incoming Transfers','route'=>'branch.transfers.index','pattern'=>'branch.transfers*','icon'=>'package-check'],
            ['label'=>'Outbound Manifests','route'=>'branch.outbound.index','pattern'=>'branch.outbound*','icon'=>'truck'],
            ['label'=>'QR Scanner','route'=>'branch.scanner','pattern'=>'branch.scanner*','icon'=>'scan-barcode'],
        ]],
        ['label'=>'People','items'=>[]],
        ['label'=>'Finance','items'=>[
            ['label'=>'COD Verification','route'=>'logistics.hybrid','pattern'=>'logistics.hybrid','icon'=>'receipt'],
            ['label'=>'Discrepancy Review','route'=>'logistics.hybrid.discrepancies.index','pattern'=>'logistics.hybrid.discrepancies.*','icon'=>'circle-alert'],
        ]],
    ];
    if ($branchStaff) {
        $branchNavigation = [
            ['label'=>'Overview','items'=>[
                ['label'=>'Workspace','route'=>'logistics.staff.workspace','pattern'=>'logistics.staff.workspace','icon'=>'layout-dashboard'],
            ]],
            ['label'=>'Operations','items'=> $branchStaff->role === 'manager' && !$isMainHub && $branchCenter?->type === 'station_hub' ? [
                ['label'=>'Rider Dispatch','route'=>'logistics.staff.dispatch.index','pattern'=>'logistics.staff.dispatch*','icon'=>'bike'],
            ] : []],
            ['label'=>'People','items'=>[]],
        ];
    }
    $canManageRiders = $branchCenter && app(\App\Services\RiderManagementService::class)->available()
        && ($isMainHub || $branchCenter->type === 'station_hub')
        && (!$branchStaff || $branchStaff->role === 'manager');
    if ($canManageRiders) {
        $pendingRiderActivations = $pendingRiderActivations ?? \App\Models\Logistics\Rider::where('logistics_partner_id', $branchCenter->logistics_partner_id)
            ->where('sorting_center_id', $branchCenter->id)
            ->whereHas('lifecycleProfile', fn ($profile) => $profile->where('application_status', 'approved')->where('operational_status', 'pending_activation'))->count();
        $branchNavigation[2]['items'][] = [
            'label'=>$isMainHub ? 'Rider Applications' : 'Riders & Activation',
            'route'=>$branchStaff ? 'logistics.staff.rider-management.index' : 'logistics.rider-management.index',
            'pattern'=>$branchStaff ? 'logistics.staff.rider-management.*' : 'logistics.rider-management.*',
            'icon'=>'users', 'badge'=>$isMainHub ? 0 : $pendingRiderActivations,
        ];
    }
    $branchNavigation = array_values(array_filter($branchNavigation, fn ($section) => count($section['items'])));
    // Alamin kung live ang bawat item (may route at hindi naka-hold) at buuin ang URL.
    $branchNavigation = collect($branchNavigation)->map(function ($section) {
        $section['items'] = collect($section['items'])->map(function ($item) {
            $item['pattern'] = $item['pattern'] ?? null;
            $item['live'] = empty($item['hold']) && \Illuminate\Support\Facades\Route::has($item['route']);
            $item['url'] = $item['live']
                ? route($item['route']).(empty($item['hash']) ? '' : '#'.$item['hash'])
                : '#';

            return $item;
        })->all();

        return $section;
    })->all();
@endphp

<body class="{{ (request()->boolean('console_modal') || request()->header('Sec-Fetch-Dest') === 'iframe') ? 'sh-console-embedded' : '' }} sh-management-ui branch-console bg-gray-bg text-navy antialiased sh-ops">

<div id="branchShell" data-mobile-open="false" class="flex h-screen overflow-hidden">

    <button id="branchMobileOverlay" type="button" hidden
            class="fixed inset-0 z-50 bg-navy/45 backdrop-blur-[1px] lg:hidden"
            aria-label="Close navigation"></button>

    {{-- SIDEBAR --}}
    <aside id="branchSidebar" data-collapsed="false"
           class="sh-logistics-sidebar relative w-64 bg-navy text-white flex flex-col shrink-0 border-r border-white/5 transition-[width] duration-200">

        <div class="pointer-events-none absolute -top-20 -left-20 w-48 h-48 rounded-full bg-teal/10 blur-2xl"></div>

        <div class="sidebar-brand-wrap relative h-[68px] flex items-center gap-2 px-5 border-b border-white/10 shrink-0">
            <a href="{{ $routeUrl($branchStaff ? 'logistics.staff.workspace' : 'branch.dashboard') }}" class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logo-bunny.png') }}" alt="ShopHop Logo" class="w-9 h-9 object-contain">
                </div>
                <div class="sidebar-expanded-only min-w-0">
                    <p class="text-[15px] font-bold leading-tight tracking-tight text-white">Shop<span class="sh-brand-hop text-teal">Hop</span></p>
                    <p class="text-[8px] uppercase tracking-[0.18em] text-white/35 font-semibold mt-0.5">{{ $portalLabel }} Portal</p>
                </div>
            </a>

            <button id="branchDesktopCollapse" type="button"
                    class="sidebar-expanded-only hidden lg:flex ml-auto w-7 h-7 rounded-lg items-center justify-center text-white/40 hover:text-white hover:bg-white/10 transition"
                    aria-label="Collapse sidebar">
                <x-lucide-panel-left-close class="w-3.5 h-3.5" />
            </button>

            <button id="branchSidebarClose" type="button"
                    class="lg:hidden ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition shrink-0"
                    aria-label="Close menu">
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <button id="branchDesktopExpand" type="button"
                class="sidebar-collapsed-only absolute top-[22px] -right-3 z-30 w-6 h-6 rounded-full bg-white text-navy border border-gray-border shadow-md items-center justify-center hover:text-teal-dark hover:border-teal/30 transition"
                aria-label="Expand sidebar">
            <x-lucide-chevron-right class="w-3.5 h-3.5" />
        </button>

        {{-- Branch card --}}
        <div class="sidebar-expanded-only px-3 pt-4">
            <div class="rounded-xl bg-white/[0.05] border border-white/[0.06] p-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-teal/15 text-teal flex items-center justify-center shrink-0">
                        <x-lucide-warehouse class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-white truncate">{{ $branchName }}</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal"></span>
                            <p class="text-[8px] text-white/40 truncate">{{ $branchCode }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <nav class="logistics-scrollbar relative flex-1 overflow-y-auto px-3 py-4" aria-label="Branch navigation">
            @foreach ($branchNavigation as $section)
                <div class="{{ ! $loop->first ? 'mt-5' : '' }}">
                    <p class="sidebar-expanded-only px-3 mb-1.5 text-[8px] uppercase tracking-[0.16em] font-bold text-white/25">{{ $section['label'] }}</p>

                    <div class="space-y-1">
                        @foreach ($section['items'] as $item)
                            @php $active = $item['live'] && ! empty($item['pattern']) && request()->routeIs($item['pattern']); @endphp

                            <div class="relative group">
                                @if ($item['live'])
                                    <a href="{{ $item['url'] }}"
                                       @if($active) aria-current="page" @endif
                                       class="sidebar-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[12px] transition-all duration-150 {{ $active ? 'bg-white/10 text-white font-semibold' : 'text-white/55 hover:text-white hover:bg-white/[0.06]' }}">

                                        @if ($active)
                                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 rounded-r-full bg-teal"></span>
                                        @endif

                                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'bg-teal/15 text-teal' : 'text-white/45 group-hover:bg-white/[0.06] group-hover:text-teal' }}">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']" class="w-4 h-4" />
                                        </span>

                                        <span class="sidebar-expanded-only flex-1 min-w-0 truncate">{{ $item['label'] }}</span>

                                        @if (!empty($item['badge']))
                                            <span class="sh-nav-badge sidebar-expanded-only text-[10px] font-bold bg-coral text-white px-1.5 py-0.5 rounded-full leading-none shrink-0">{{ $item['badge'] }}</span>
                                        @endif
                                    </a>
                                @else
                                    {{-- Wala pang page: hindi clickable, may "Soon" label --}}
                                    <div class="sidebar-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[12px] text-white/30 cursor-not-allowed select-none"
                                         aria-disabled="true">
                                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-white/25">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']" class="w-4 h-4" />
                                        </span>
                                        <span class="sidebar-expanded-only flex-1 min-w-0 truncate">{{ $item['label'] }}</span>
                                        <span class="sidebar-expanded-only text-[8px] font-bold uppercase tracking-wider bg-white/10 text-white/45 px-1.5 py-0.5 rounded-full leading-none shrink-0">Soon</span>
                                    </div>
                                @endif

                                <div class="sidebar-tooltip pointer-events-none absolute left-[calc(100%+10px)] top-1/2 -translate-y-1/2 z-[80] px-2.5 py-1.5 rounded-lg bg-navy-light text-white border border-white/10 shadow-lg text-[10px] font-medium whitespace-nowrap opacity-0 translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 transition">
                                    {{ $item['label'] }}{{ $item['live'] ? '' : ' (Soon)' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="relative p-3 border-t border-white/10 shrink-0">
            <div class="flex items-center gap-3 px-2 py-2 mb-1">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal to-sky text-navy flex items-center justify-center text-[10px] font-bold shrink-0">{{ $branchInitials }}</div>
                <div class="sidebar-expanded-only min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-white truncate">{{ $userName }}</p>
                    <p class="text-[8px] text-white/30 truncate">{{ $portalLabel }} account</p>
                </div>
            </div>

            <form action="{{ $logoutUrl }}" method="POST">
                @csrf
                <button type="submit"
                        class="sidebar-nav-link w-full group flex items-center gap-3 px-3 py-2.5 rounded-xl text-[12px] font-medium text-white/45 hover:bg-red-400/10 hover:text-red-300 transition">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"><x-lucide-log-out class="w-4 h-4" /></span>
                    <span class="sidebar-expanded-only">Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN COLUMN --}}
    <div class="flex-1 min-w-0 flex flex-col overflow-hidden">

        <header class="h-[68px] bg-white/95 backdrop-blur border-b border-gray-border flex items-center px-3 sm:px-4 lg:px-5 shrink-0 z-30">

            <button id="branchMobileToggle" type="button"
                    class="lg:hidden w-9 h-9 rounded-lg border border-gray-border text-navy/55 flex items-center justify-center hover:bg-gray-bg transition shrink-0"
                    aria-label="Open menu">
                <x-lucide-menu class="w-4 h-4" />
            </button>

            <button id="branchSearchTrigger" type="button"
                    class="group ml-2 lg:ml-0 w-full max-w-md flex items-center gap-2.5 h-9 px-3 rounded-lg bg-gray-bg border border-transparent text-left hover:bg-white hover:border-gray-border transition"
                    aria-label="Search branch tools">
                <x-lucide-search class="w-3.5 h-3.5 text-navy/35 shrink-0" />
                <span class="text-[11px] text-navy/35 truncate flex-1">Search branch tools...</span>
                <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 rounded-md bg-white border border-gray-border text-[8px] font-semibold text-navy/30">Ctrl K</kbd>
            </button>

            <div class="ml-auto flex items-center gap-2">


                <a href="{{ route('home') }}" target="_blank"
                   class="hidden sm:inline-flex items-center gap-1.5 h-9 px-3 rounded-lg text-[10px] font-semibold text-navy/45 hover:text-teal-dark hover:bg-gray-bg transition">
                    <x-lucide-external-link class="w-3.5 h-3.5" />
                    View ShopHop
                </a>

                <div class="hidden sm:block w-px h-6 bg-gray-border"></div>

                <details class="branch-profile-menu relative">
                    <summary class="flex h-10 cursor-pointer list-none items-center gap-2 rounded-xl px-1.5 transition hover:bg-gray-bg focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal" aria-label="{{ $portalLabel }} account menu">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-teal to-sky text-[10px] font-bold text-navy">{{ $branchInitials }}</span>
                        <span class="hidden min-w-0 max-w-36 text-left md:block">
                            <span class="block truncate text-[10px] font-semibold text-navy">{{ $userName }}</span>
                            <span class="block truncate text-[8px] text-navy/40">{{ $branchEmail }}</span>
                        </span>
                        <x-lucide-chevron-down class="hidden h-3.5 w-3.5 text-navy/35 md:block" />
                    </summary>
                    <div class="absolute right-0 top-[calc(100%+10px)] z-[85] w-56 rounded-2xl border border-gray-border bg-white p-2 shadow-panel">
                        <div class="border-b border-gray-border px-3 py-2">
                            <p class="truncate text-[11px] font-bold text-navy">{{ $branchName }}</p>
                            <p class="truncate text-[10px] text-navy/45">{{ $portalLabel }} account</p>
                        </div>
                        <a href="{{ $routeUrl($branchStaff ? 'logistics.staff.workspace' : 'branch.dashboard') }}" class="mt-1 flex items-center gap-2 rounded-lg px-3 py-2 text-[11px] text-navy/70 hover:bg-gray-bg"><x-lucide-layout-dashboard class="h-4 w-4" /> Dashboard</a>
                        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 rounded-lg px-3 py-2 text-[11px] text-navy/70 hover:bg-gray-bg"><x-lucide-external-link class="h-4 w-4" /> Main ShopHop</a>
                        <form method="POST" action="{{ $logoutUrl }}" class="mt-1 border-t border-gray-border pt-1">@csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-[11px] font-semibold text-rose-600 hover:bg-rose-50"><x-lucide-log-out class="h-4 w-4" /> Sign out</button>
                        </form>
                    </div>
                </details>
            </div>
        </header>

        <main class="sh-management-main content-scrollbar flex-1 overflow-y-auto px-3 py-4 sm:px-4 sm:py-5 lg:px-5 lg:py-5">
            <div class="max-w-[1600px] mx-auto">

                @if (session('success') || session('status'))
                    <div class="mb-4 flex items-center gap-3 rounded-xl bg-teal-light border border-teal/20 px-4 py-3" role="status">
                        <x-lucide-circle-check class="w-4 h-4 text-teal-dark shrink-0" />
                        <p class="text-xs font-medium text-teal-dark">{{ session('success') ?? session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="sh-alert-error mb-4">
                        <p class="font-semibold">{{ $errors->first() }}</p>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

{{-- COMMAND / SEARCH PALETTE (live pages lang) --}}
<div id="branchSearchPalette" hidden class="fixed inset-0 z-[90] flex items-start justify-center px-3 pt-[10vh] sm:pt-[14vh]" aria-hidden="true">
    <button id="branchSearchBackdrop" type="button" class="absolute inset-0 bg-navy/50 backdrop-blur-[2px]" aria-label="Close search"></button>

    <div class="relative w-full max-w-xl bg-white border border-gray-border rounded-2xl shadow-2xl overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="branchSearchTitle">
        <div class="flex items-center gap-3 px-4 border-b border-gray-border">
            <x-lucide-search class="w-4 h-4 text-teal-dark shrink-0" />
            <input id="branchCommandInput" type="text" autocomplete="off" placeholder="Find a branch page or tool..."
                   class="w-full h-12 bg-transparent text-xs sm:text-sm text-navy placeholder:text-navy/30 focus:outline-none">
            <kbd class="px-1.5 py-0.5 rounded-md bg-gray-bg border border-gray-border text-[8px] font-semibold text-navy/30">ESC</kbd>
        </div>

        <div class="max-h-[360px] overflow-y-auto p-2">
            <p id="branchSearchTitle" class="px-2 py-2 text-[8px] font-bold uppercase tracking-[0.14em] text-navy/30">Branch Navigation</p>

            @foreach ($branchNavigation as $section)
                @foreach ($section['items'] as $item)
                    @continue(! $item['live'])
                    <a href="{{ $item['url'] }}" data-branch-command-item
                       data-search-text="{{ strtolower($item['label'] . ' ' . $section['label']) }}"
                       class="group flex items-center gap-3 px-2.5 py-2.5 rounded-xl hover:bg-gray-bg transition">
                        <div class="w-8 h-8 rounded-lg bg-gray-bg text-teal-dark flex items-center justify-center shrink-0">
                            <x-dynamic-component :component="'lucide-' . $item['icon']" class="w-4 h-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] font-semibold text-navy">{{ $item['label'] }}</p>
                            <p class="text-[8px] text-navy/35 mt-0.5">{{ $section['label'] }}</p>
                        </div>
                        <x-lucide-corner-down-left class="w-3.5 h-3.5 text-navy/20 group-hover:text-teal-dark" />
                    </a>
                @endforeach
            @endforeach

            <div id="branchCommandEmpty" hidden class="px-4 py-10 text-center">
                <div class="w-10 h-10 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center"><x-lucide-search-x class="w-4 h-4" /></div>
                <p class="text-[11px] font-semibold text-navy/50 mt-3">No branch tool found</p>
                <p class="text-[9px] text-navy/30 mt-1">Try another keyword.</p>
            </div>
        </div>
    </div>
</div>

{{-- CONFIRM MODAL (gumagana sa mga form na may data-confirm-title) --}}
<div id="branchConfirmModal" hidden class="fixed inset-0 z-[95] flex items-center justify-center px-4">
    <button type="button" class="sh-modal-backdrop" data-confirm-close aria-label="Close confirmation dialog"></button>
    <div class="sh-modal-card" role="dialog" aria-modal="true" aria-labelledby="branchConfirmTitle" aria-describedby="branchConfirmBody">
        <div class="sh-modal-head flex items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-700"><x-lucide-shield-alert class="h-5 w-5" /></div>
            <div class="min-w-0">
                <p id="branchConfirmTitle" class="text-base font-bold text-navy">Confirm action</p>
                <p id="branchConfirmBody" class="mt-1 text-sm leading-relaxed text-navy/55">Please confirm to continue.</p>
            </div>
        </div>
        <div class="sh-modal-actions">
            <button type="button" class="sh-btn-ghost" data-confirm-close>Cancel</button>
            <button type="button" class="sh-btn" id="branchConfirmAccept">Continue</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const shell = document.getElementById('branchShell');
    const sidebar = document.getElementById('branchSidebar');
    const toggle = document.getElementById('branchMobileToggle');
    const overlay = document.getElementById('branchMobileOverlay');
    const sidebarClose = document.getElementById('branchSidebarClose');

    function openSidebar() {
        shell?.setAttribute('data-mobile-open', 'true');
        if (overlay) overlay.hidden = false;
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        shell?.setAttribute('data-mobile-open', 'false');
        if (overlay) overlay.hidden = true;
        document.body.style.overflow = '';
    }
    toggle?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    document.querySelectorAll('#branchSidebar a, #branchSidebar button[type="submit"]').forEach(function (el) {
        el.addEventListener('click', function () { if (window.innerWidth < 1024) closeSidebar(); });
    });

    /* Desktop collapse */
    const key = 'shophop_branch_sidebar_collapsed';
    function setCollapsed(collapsed) {
        const apply = window.innerWidth >= 1024 && collapsed;
        sidebar?.setAttribute('data-collapsed', apply ? 'true' : 'false');
        localStorage.setItem(key, apply ? '1' : '0');
    }
    setCollapsed(localStorage.getItem(key) === '1');
    document.getElementById('branchDesktopCollapse')?.addEventListener('click', function () { setCollapsed(true); });
    document.getElementById('branchDesktopExpand')?.addEventListener('click', function (e) { e.preventDefault(); setCollapsed(false); });
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) { closeSidebar(); setCollapsed(localStorage.getItem(key) === '1'); }
        else { setCollapsed(false); }
    });

    /* Command palette */
    const searchTrigger = document.getElementById('branchSearchTrigger');
    const searchPalette = document.getElementById('branchSearchPalette');
    const searchBackdrop = document.getElementById('branchSearchBackdrop');
    const commandInput = document.getElementById('branchCommandInput');
    const commandItems = Array.from(document.querySelectorAll('[data-branch-command-item]'));
    const commandEmpty = document.getElementById('branchCommandEmpty');

    function filterCommands() {
        const q = (commandInput?.value || '').trim().toLowerCase();
        let visible = 0;
        commandItems.forEach(function (item) {
            const show = q === '' || (item.getAttribute('data-search-text') || '').includes(q);
            item.hidden = !show;
            if (show) visible++;
        });
        if (commandEmpty) commandEmpty.hidden = visible > 0;
    }
    function openPalette() {
        if (!searchPalette) return;
        searchPalette.hidden = false;
        searchPalette.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { commandInput?.focus(); commandInput?.select(); }, 0);
    }
    function closePalette() {
        if (!searchPalette) return;
        searchPalette.hidden = true;
        searchPalette.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (commandInput) commandInput.value = '';
        filterCommands();
    }
    searchTrigger?.addEventListener('click', openPalette);
    searchBackdrop?.addEventListener('click', closePalette);
    commandInput?.addEventListener('input', filterCommands);
    commandInput?.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        const first = commandItems.find(function (i) { return !i.hidden; });
        if (first) window.location.href = first.href;
    });
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openPalette(); return; }
        if (e.key === 'Escape') {
            if (searchPalette && !searchPalette.hidden) { closePalette(); return; }
            if (shell?.getAttribute('data-mobile-open') === 'true') closeSidebar();
        }
    });
    filterCommands();

    /* Confirm modal */
    const confirmModal = document.getElementById('branchConfirmModal');
    const confirmTitle = document.getElementById('branchConfirmTitle');
    const confirmBody = document.getElementById('branchConfirmBody');
    const confirmAccept = document.getElementById('branchConfirmAccept');
    let confirmHandler = null;
    let previousFocus = null;

    function openConfirm(o) {
        if (!confirmModal) return;
        confirmTitle.textContent = o.title || 'Confirm action';
        confirmBody.textContent = o.message || 'Please confirm to continue.';
        confirmAccept.textContent = o.confirmLabel || 'Continue';
        confirmAccept.className = (o.danger ? 'sh-btn-danger' : 'sh-btn') + ' px-5';
        confirmHandler = o.onConfirm || null;
        previousFocus = document.activeElement;
        confirmModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }
    function closeConfirm() {
        if (!confirmModal) return;
        confirmModal.hidden = true;
        document.body.style.overflow = (searchPalette && !searchPalette.hidden) ? 'hidden' : '';
        confirmHandler = null;
        previousFocus?.focus?.();
        previousFocus = null;
    }
    document.querySelectorAll('[data-confirm-close]').forEach(function (b) { b.addEventListener('click', closeConfirm); });
    confirmAccept?.addEventListener('click', function () {
        if (typeof confirmHandler === 'function') { const run = confirmHandler; closeConfirm(); run(); }
    });
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('form[data-confirm-title]');
        if (!form || form.dataset.confirmed === '1') { if (form) form.dataset.confirmed = '0'; return; }
        event.preventDefault();
        // Native detail modals sit in the top layer; close them before showing the global confirmation dialog.
        form.closest('dialog')?.close();
        openConfirm({
            title: form.dataset.confirmTitle,
            message: form.dataset.confirmMessage || 'Please confirm to continue.',
            confirmLabel: form.dataset.confirmButton || 'Continue',
            danger: form.dataset.confirmDanger === '1',
            onConfirm: function () { form.dataset.confirmed = '1'; form.requestSubmit(); }
        });
    }, true);
    document.addEventListener('keydown', function (e) {
        if (!confirmModal || confirmModal.hidden) return;
        if (e.key === 'Escape') { e.preventDefault(); closeConfirm(); }
    }, true);
});
</script>

<script defer src="{{ asset('js/logistics-ux-phase345.js') }}?v=20261112-navy-pink-2"></script>
@stack('scripts')

    <script src="{{ asset('js/shophop-required-fields.js') }}?v=20261112-navy-pink-2"></script>

<script defer src="{{ asset('js/shophop-operations-table.js') }}?v=20261112-navy-pink-2"></script>
@include('partials.console-details')
</body>
</html>
