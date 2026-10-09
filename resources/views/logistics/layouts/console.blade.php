<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    {{-- Apply the persisted theme before the logistics shell paints. --}}
    @include('partials.theme-head')

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Dashboard') - ShopHop Logistics
    </title>


    {{-- =========================================================
        TAILWIND
    ========================================================= --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0F1B3D',
                        'navy-light': '#1C3150',
                        'navy-soft': '#244B61',

                        teal: '#21C3A6',
                        'teal-dark': '#18A98E',
                        'teal-light': '#D4F5EE',

                        mint: '#21C3A6',
                        'mint-dark': '#18A98E',

                        sky: '#4AA8E0',
                        yellow: '#F7C948',
                        coral: '#FF7A59',

                        'gray-bg': '#F3F5F7',
                        'gray-border': '#E2E6EA',
                    },

                    fontFamily: {
                        sans: [
                            'Poppins',
                            'ui-sans-serif',
                            'system-ui',
                            'sans-serif'
                        ],
                    },

                    boxShadow: {
                        soft: '0 10px 30px rgba(15, 44, 63, 0.08)',
                        panel: '0 18px 50px rgba(15, 44, 63, 0.12)',
                    },
                }
            }
        }
    </script>


    {{-- =========================================================
        FONT
    ========================================================= --}}
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    {{-- =========================================================
        LOGISTICS STYLES
    ========================================================= --}}
    <style>

        :root {
            color-scheme: light;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
        }

        .logistics-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .16) transparent;
        }

        .logistics-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .logistics-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .16);
            border-radius: 999px;
        }

        .content-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(15, 44, 63, .15) transparent;
        }

        .content-scrollbar::-webkit-scrollbar {
            width: 7px;
        }

        .content-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(15, 44, 63, .14);
            border-radius: 999px;
        }

        #logisticsMobileOverlay[hidden] {
            display: none !important;
        }

        /* NEW: command palette visibility hooks, same pattern used on the
           admin layout (#adminSearchPalette). */
        #logisticsSearchPalette[hidden] {
            display: none !important;
        }

        /* =====================================================
           DESKTOP COLLAPSE (>=1024px)
        ===================================================== */
        #logisticsSidebar[data-collapsed="true"] {
            width: 5rem;
        }

        #logisticsSidebar .sidebar-collapsed-only {
            display: none;
        }

        #logisticsSidebar[data-collapsed="true"] .sidebar-expanded-only {
            display: none;
        }

        #logisticsSidebar[data-collapsed="true"] .sidebar-collapsed-only {
            display: flex;
        }

        #logisticsSidebar[data-collapsed="true"] .sidebar-nav-link {
            justify-content: center;
            padding-left: .75rem;
            padding-right: .75rem;
        }

        #logisticsSidebar[data-collapsed="true"] .sidebar-brand-wrap {
            justify-content: center;
            padding-left: .75rem;
            padding-right: .75rem;
        }

        #logisticsSidebar[data-collapsed="true"] .sidebar-tooltip {
            display: block;
        }

        .sidebar-tooltip {
            display: none;
        }

        /* =====================================================
           MOBILE SLIDE-IN (<1024px)
        ===================================================== */
        @media (max-width: 1023px) {

            #logisticsSidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;

                width: 17rem !important;

                z-index: 60;

                transform: translateX(-100%);

                transition: transform .22s ease;
            }

            #logisticsShell[data-mobile-open="true"]
            #logisticsSidebar {
                transform: translateX(0);
            }

            #logisticsSidebar[data-collapsed="true"] .sidebar-expanded-only {
                display: initial;
            }

            #logisticsSidebar[data-collapsed="true"] .sidebar-nav-link {
                justify-content: flex-start;
                padding-left: .75rem;
                padding-right: .75rem;
            }

            #logisticsSidebar[data-collapsed="true"] .sidebar-brand-wrap {
                justify-content: flex-start;
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }

            #logisticsSidebar .sidebar-tooltip {
                display: none !important;
            }

            .sidebar-collapsed-only {
                display: none !important;
            }
        }

    </style>

    <style>
        /* ShopHop Admin-aligned details, focus states, and form hierarchy. */
        .logistics-profile-menu > summary::-webkit-details-marker { display: none; }
        .logistics-profile-menu > summary::marker { content: ''; }
        .logistics-console :is(button, a, input, select, textarea, summary):focus-visible {
            outline: 2px solid #18A98E;
            outline-offset: 3px;
        }
        .logistics-console main :is(input:not([type="checkbox"]):not([type="radio"]), select, textarea) {
            max-width: 100%;
            min-height: 2.5rem;
            background-color: #fff;
        }
        .logistics-console main :is(input, select, textarea):focus {
            border-color: #18A98E;
            box-shadow: 0 0 0 3px rgba(33, 195, 166, .12);
            outline: none;
        }
        .logistics-console main section[id], .logistics-console main article[id] { scroll-margin-top: 1rem; }
        .logistics-console .logistics-panel { border: 1px solid #E2E6EA; border-radius: 1rem; background: #fff; }
        @media (prefers-reduced-motion: reduce) {
            .logistics-console * { scroll-behavior: auto !important; transition-duration: 0.01ms !important; }
        }
    </style>


    <style>
        /* v5 polish system: reusable buttons, cards, forms, tables, and confirmation modal */
        .sh-page-header { display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:1rem; }
        .sh-page-header .eyebrow { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.16em; color:#18A98E; }
        .sh-page-header .title { margin-top:.25rem; font-size:1.75rem; line-height:1.15; font-weight:700; color:#0F1B3D; letter-spacing:-.02em; }
        .sh-page-header .description { margin-top:.4rem; max-width:42rem; font-size:.875rem; line-height:1.7; color:rgba(15,27,61,.58); }
        .sh-surface { border:1px solid #E2E6EA; border-radius:1.5rem; background:#fff; box-shadow:0 10px 30px rgba(15,44,63,.06); }
        .sh-surface-soft { border:1px solid #E2E6EA; border-radius:1.25rem; background:#F8FAFB; }
        .sh-card-pad { padding:1.125rem; }
        @media (min-width: 640px){ .sh-card-pad { padding:1.375rem; } }
        .sh-stat { border:1px solid #E2E6EA; border-radius:1.25rem; background:#fff; padding:1rem; box-shadow:0 8px 20px rgba(15,44,63,.05); }
        .sh-stat-label { font-size:.68rem; text-transform:uppercase; letter-spacing:.12em; font-weight:700; color:rgba(15,27,61,.42); }
        .sh-stat-value { margin-top:.35rem; font-size:1.7rem; font-weight:700; line-height:1; color:#0F1B3D; }
        .sh-stat-note { margin-top:.35rem; font-size:.72rem; color:rgba(15,27,61,.5); }
        .sh-kicker { font-size:.7rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:#18A98E; }
        .sh-title-sm { font-size:.95rem; font-weight:700; color:#0F1B3D; }
        .sh-text-muted { color:rgba(15,27,61,.55); }
        .sh-divider { border-color:#E2E6EA; }
        .sh-btn, .sh-btn-secondary, .sh-btn-ghost, .sh-btn-danger, .sh-btn-soft {
            min-height:2.6rem; display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
            border-radius:999px; padding:.68rem 1rem; font-size:.78rem; font-weight:700; transition:all .18s ease;
            border:1px solid transparent; text-decoration:none;
        }
        .sh-btn { background:#0F1B3D; color:#fff; box-shadow:0 10px 25px rgba(15,27,61,.12); }
        .sh-btn:hover { background:#18A98E; transform:translateY(-1px); }
        .sh-btn-secondary { background:#D4F5EE; color:#11806B; }
        .sh-btn-secondary:hover { background:#c4efe6; transform:translateY(-1px); }
        .sh-btn-ghost { background:#fff; color:#0F1B3D; border-color:#DCE2E7; }
        .sh-btn-ghost:hover { border-color:rgba(24,169,142,.35); color:#11806B; background:#F7FBFA; }
        .sh-btn-soft { background:#F3F5F7; color:#0F1B3D; border-color:#E2E6EA; }
        .sh-btn-soft:hover { background:#EAF8F4; color:#11806B; }
        .sh-btn-danger { background:#fff; color:#C24141; border-color:#F3CACA; }
        .sh-btn-danger:hover { background:#FEF2F2; }
        .sh-input, .sh-select, .sh-textarea {
            width:100%; border-radius:1rem; border:1px solid #D8DEE4; background:#fff; color:#0F1B3D;
            padding:.7rem .9rem; font-size:.9rem; line-height:1.4; box-shadow: inset 0 1px 2px rgba(15,27,61,.02);
        }
        .sh-input::placeholder, .sh-textarea::placeholder { color:rgba(15,27,61,.33); }
        .sh-select, .sh-input, .sh-textarea { transition:border-color .16s ease, box-shadow .16s ease, background-color .16s ease; }
        .sh-field { display:grid; gap:.42rem; }
        .sh-label { font-size:.73rem; font-weight:700; color:#0F1B3D; }
        .sh-help { font-size:.7rem; color:rgba(15,27,61,.45); }
        .sh-badge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.38rem .72rem; font-size:.66rem; font-weight:700; }
        .sh-badge-info { background:#EDF7FF; color:#2664A9; }
        .sh-badge-success { background:#E9FBF6; color:#11806B; }
        .sh-badge-warning { background:#FFF6DD; color:#A46700; }
        .sh-badge-danger { background:#FEF2F2; color:#B42318; }
        .sh-badge-neutral { background:#F2F4F7; color:#475467; }
        .sh-stack { display:grid; gap:1rem; }
        .sh-toolbar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem; }
        .sh-grid-2 { display:grid; gap:1rem; grid-template-columns:repeat(1,minmax(0,1fr)); }
        @media (min-width: 1024px){ .sh-grid-2 { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        .sh-empty { border:1px dashed #D8DEE4; border-radius:1.25rem; background:#fff; padding:2rem 1.25rem; text-align:center; }
        .sh-table-wrap { overflow-x:auto; }
        .sh-table { width:100%; min-width:720px; border-collapse:separate; border-spacing:0; }
        .sh-table thead th { background:#F7F9FA; padding:.9rem 1rem; font-size:.67rem; font-weight:700; color:rgba(15,27,61,.45); text-transform:uppercase; letter-spacing:.12em; text-align:left; border-bottom:1px solid #E2E6EA; }
        .sh-table tbody td { padding:1rem; font-size:.82rem; color:#0F1B3D; border-bottom:1px solid rgba(226,230,234,.8); vertical-align:top; }
        .sh-table tbody tr:hover td { background:#FBFCFD; }
        .sh-alert-success, .sh-alert-error { border-radius:1rem; padding:.95rem 1rem; font-size:.82rem; border:1px solid transparent; }
        .sh-alert-success { background:#EAFBF5; color:#0F7B61; border-color:#B9EEDB; }
        .sh-alert-error { background:#FEF2F2; color:#B42318; border-color:#F6C4C4; }
        .sh-summary { list-style:none; cursor:pointer; }
        .sh-summary::-webkit-details-marker { display:none; }
        #logisticsConfirmModal[hidden] { display:none !important; }
        .sh-modal-backdrop { position:fixed; inset:0; background:rgba(15,27,61,.55); backdrop-filter: blur(3px); }
        .sh-modal-card { position:relative; width:min(100%, 29rem); border-radius:1.5rem; background:#fff; border:1px solid #E2E6EA; box-shadow:0 30px 80px rgba(15,27,61,.24); overflow:hidden; }
        .sh-modal-head { padding:1.1rem 1.25rem; border-bottom:1px solid #E2E6EA; }
        .sh-modal-body { padding:1rem 1.25rem; }
        .sh-modal-actions { padding:1rem 1.25rem 1.25rem; display:flex; justify-content:flex-end; gap:.65rem; }
    </style>

    @stack('styles')

</head>


@php

    /*
    |--------------------------------------------------------------------------
    | LOGISTICS DISPLAY DATA
    |--------------------------------------------------------------------------
    | Guest-safe muna habang development/testing.
    */

    $logisticsUser = auth()->user();

    $logisticsName =
        $logisticsName
        ?? $logisticsUser?->name
        ?? 'QuickHop Logistics';

    $logisticsEmail =
        $logisticsUser?->email
        ?? 'logistics@shophop.com';


    $logisticsInitials =
        collect(
            preg_split(
                '/[\s._-]+/',
                trim($logisticsName)
            )
        )
        ->filter()
        ->map(
            fn ($part) =>
                mb_strtoupper(
                    mb_substr($part, 0, 1)
                )
        )
        ->take(2)
        ->implode('');


    if ($logisticsInitials === '') {
        $logisticsInitials = 'LG';
    }


    /*
    |--------------------------------------------------------------------------
    | SIDEBAR NAVIGATION
    |--------------------------------------------------------------------------
    */

    $logisticsNavigation = [

        [
            'label' => 'Overview',

            'items' => [

                [
                    'label' => 'Dashboard',
                    'route' => 'logistics.dashboard',
                    'pattern' => 'logistics.dashboard',
                    'icon' => 'layout-dashboard',
                ],

            ],
        ],


        [
            'label' => 'Branches & Fleet',

            'items' => [

                [
                    'label' => 'Sorting Centers',
                    'route' => 'logistics.sorting-centers.index',
                    'pattern' => 'logistics.sorting-centers.*',
                    'icon' => 'warehouse',
                ],

                [
                    'label' => 'Riders',
                    'route' => 'logistics.riders.index',
                    'pattern' => 'logistics.riders.*',
                    'icon' => 'bike',
                    'badge' => isset($pendingApplications) ? count($pendingApplications) : 0,
                ],

            ],
        ],


        [
            'label' => 'Operations',

            'items' => [

                [
                    'label' => 'Pickup & Delivery Board',
                    'route' => 'logistics.deliveries.board',
                    'pattern' => 'logistics.deliveries.*',
                    'icon' => 'package-check',
                ],

                // COD custody is a distinct persisted operation beside delivery fulfillment.
                [
                    'label' => 'COD Settlements',
                    'route' => 'logistics.settlements.index',
                    'pattern' => 'logistics.settlements.*',
                    'icon' => 'wallet',
                ],

            ],
        ],


        [
            'label' => 'Analytics',

            'items' => [

                [
                    'label' => 'Delivery Reports',
                    'route' => 'logistics.reports.index',
                    'pattern' => 'logistics.reports.*',
                    'icon' => 'chart-no-axes-combined',
                ],

            ],
        ],

    ];

@endphp


<body class="logistics-console bg-gray-bg text-navy antialiased">

{{-- Local previews need an obvious TEST/DEMO control without exposing it in production. --}}
@includeWhen(app()->environment('local'), 'dev.account-switcher')

<div
    id="logisticsShell"
    data-mobile-open="false"
    class="flex h-screen overflow-hidden"
>

    {{-- =========================================================
        MOBILE OVERLAY
    ========================================================= --}}
    <button
        id="logisticsMobileOverlay"
        type="button"
        hidden
        class="fixed inset-0 z-50
               bg-navy/45
               backdrop-blur-[1px]
               lg:hidden"
        aria-label="Close navigation"
    ></button>


    {{-- =========================================================
        SIDEBAR
    ========================================================= --}}
    <aside
        id="logisticsSidebar"
        data-collapsed="false"
        class="relative
               w-64
               bg-navy
               text-white
               flex flex-col
               shrink-0
               border-r border-white/5
               transition-[width] duration-200"
    >

        {{-- Background decor --}}
        <div
            class="pointer-events-none
                   absolute
                   -top-20 -left-20
                   w-48 h-48
                   rounded-full
                   bg-teal/10
                   blur-2xl"
        ></div>


        {{-- =====================================================
            BRAND
        ===================================================== --}}
        <div
            class="sidebar-brand-wrap
                   relative
                   h-[68px]
                   flex items-center gap-2
                   px-5
                   border-b border-white/10
                   shrink-0"
        >

            
                <a href="{{ route('logistics.dashboard') }}"
                class="flex items-center gap-3 min-w-0"
            >

                {{-- Official Logo --}}
                <div
                    class="w-9 h-9
                           flex items-center justify-center
                           shrink-0"
                >
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="ShopHop Logo"
                        class="w-9 h-9 object-contain"
                    >
                </div>


                <div class="sidebar-expanded-only min-w-0">

                    <p
                        class="text-[15px]
                               font-bold
                               leading-tight
                               tracking-tight
                               text-white"
                    >
                        Shop<span class="text-teal">Hop</span>
                    </p>

                    <p
                        class="text-[8px]
                               uppercase
                               tracking-[0.18em]
                               text-white/35
                               font-semibold
                               mt-0.5"
                    >
                        Logistics Portal
                    </p>

                </div>

            </a>


            {{-- Desktop collapse toggle --}}
            <button
                id="logisticsDesktopCollapse"
                type="button"
                class="sidebar-expanded-only
                       hidden lg:flex
                       ml-auto
                       w-7 h-7 rounded-lg
                       items-center justify-center
                       text-white/40
                       hover:text-white hover:bg-white/10
                       transition"
                aria-label="Collapse sidebar"
            >
                <x-lucide-panel-left-close class="w-3.5 h-3.5" />
            </button>


            {{-- Mobile close --}}
            <button
                id="logisticsSidebarClose"
                type="button"
                class="lg:hidden
                       ml-auto
                       w-8 h-8
                       flex items-center justify-center
                       rounded-lg
                       text-white/60
                       hover:text-white hover:bg-white/10
                       transition shrink-0"
                aria-label="Close menu"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        {{-- Desktop expand control shown only while collapsed --}}
        <button
            id="logisticsDesktopExpand"
            type="button"
            class="sidebar-collapsed-only
                   absolute top-[22px] -right-3 z-30
                   w-6 h-6 rounded-full
                   bg-white text-navy
                   border border-gray-border
                   shadow-md
                   items-center justify-center
                   hover:text-teal-dark hover:border-teal/30
                   transition"
            aria-label="Expand sidebar"
        >
            <x-lucide-chevron-right class="w-3.5 h-3.5" />
        </button>


        {{-- =====================================================
            LOGISTICS COMPANY
        ===================================================== --}}
        <div class="sidebar-expanded-only px-3 pt-4">

            <div
                class="rounded-xl
                       bg-white/[0.05]
                       border border-white/[0.06]
                       p-3"
            >

                <div class="flex items-center gap-3">

                    <div
                        class="w-9 h-9
                               rounded-xl
                               bg-teal/15
                               text-teal
                               flex items-center justify-center
                               shrink-0"
                    >
                        <x-lucide-building-2 class="w-4 h-4" />
                    </div>


                    <div class="min-w-0">

                        <p
                            class="text-[10px]
                                   font-semibold
                                   text-white
                                   truncate"
                        >
                            {{ $logisticsName }}
                        </p>

                        <div
                            class="flex items-center gap-1.5
                                   mt-1"
                        >

                            <span
                                class="w-1.5 h-1.5
                                       rounded-full
                                       bg-teal"
                            ></span>

                            <p
                                class="text-[8px]
                                       text-white/40"
                            >
                                Approved Partner
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
            NAVIGATION
        ===================================================== --}}
        <nav
            class="logistics-scrollbar
                   relative
                   flex-1
                   overflow-y-auto
                   px-3 py-4"
            aria-label="Logistics navigation"
        >

            @foreach ($logisticsNavigation as $section)

                <div class="{{ ! $loop->first ? 'mt-5' : '' }}">

                    <p
                        class="sidebar-expanded-only
                               px-3 mb-1.5
                               text-[8px]
                               uppercase
                               tracking-[0.16em]
                               font-bold
                               text-white/25"
                    >
                        {{ $section['label'] }}
                    </p>


                    <div class="space-y-1">

                        @foreach ($section['items'] as $item)

                            @php

                                $active =
                                    request()
                                    ->routeIs(
                                        $item['pattern']
                                    );

                            @endphp


                            <div class="relative group">

                                
                                    <a href="{{ route($item['route']) }}"
                                    @if($active) aria-current="page" @endif
                                    class="sidebar-nav-link
                                           relative
                                           flex items-center gap-3
                                           px-3 py-2.5
                                           rounded-xl
                                           text-[12px]
                                           transition-all duration-150

                                           {{
                                                $active
                                                    ? 'bg-white/10 text-white font-semibold'
                                                    : 'text-white/55 hover:text-white hover:bg-white/[0.06]'
                                           }}"
                                >

                                    @if ($active)

                                        <span
                                            class="absolute
                                                   left-0 top-1/2
                                                   -translate-y-1/2
                                                   w-0.5 h-5
                                                   rounded-r-full
                                                   bg-teal"
                                        ></span>

                                    @endif


                                    <span
                                        class="w-8 h-8
                                               rounded-lg
                                               flex items-center justify-center
                                               shrink-0

                                               {{
                                                    $active
                                                        ? 'bg-teal/15 text-teal'
                                                        : 'text-white/45 group-hover:bg-white/[0.06] group-hover:text-teal'
                                               }}"
                                    >

                                        <x-dynamic-component
                                            :component="'lucide-' . $item['icon']"
                                            class="w-4 h-4"
                                        />

                                    </span>


                                    <span class="sidebar-expanded-only flex-1 min-w-0 truncate">
                                        {{ $item['label'] }}
                                    </span>


                                    @if (!empty($item['badge']))
                                        <span class="sidebar-expanded-only text-[10px] font-bold bg-red-500 text-white px-1.5 py-0.5 rounded-full leading-none shrink-0">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif

                                </a>


                                {{-- Collapsed tooltip --}}
                                <div
                                    class="sidebar-tooltip
                                           pointer-events-none
                                           absolute left-[calc(100%+10px)] top-1/2 -translate-y-1/2
                                           z-[80]
                                           px-2.5 py-1.5
                                           rounded-lg
                                           bg-navy-light text-white
                                           border border-white/10
                                           shadow-lg
                                           text-[10px] font-medium
                                           whitespace-nowrap
                                           opacity-0 translate-x-1
                                           group-hover:opacity-100 group-hover:translate-x-0
                                           transition"
                                >
                                    {{ $item['label'] }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            @endforeach

        </nav>


        {{-- =====================================================
            SIDEBAR BOTTOM
        ===================================================== --}}
        <div
            class="relative
                   p-3
                   border-t border-white/10
                   shrink-0"
        >

            <div
                class="flex items-center gap-3
                       px-2 py-2 mb-1"
            >

                <div
                    class="w-8 h-8
                           rounded-full
                           bg-gradient-to-br
                           from-teal to-sky
                           text-navy
                           flex items-center justify-center
                           text-[10px]
                           font-bold
                           shrink-0"
                >
                    {{ $logisticsInitials }}
                </div>


                <div class="sidebar-expanded-only min-w-0 flex-1">

                    <p
                        class="text-[10px]
                               font-semibold
                               text-white
                               truncate"
                    >
                        {{ $logisticsName }}
                    </p>

                    <p
                        class="text-[8px]
                               text-white/30
                               truncate"
                    >
                        Logistics Partner
                    </p>

                </div>

            </div>


            <form
                action="{{ route('logistics.logout') }}"
                method="POST"
            >
                @csrf

                <button
                    type="submit"
                    class="sidebar-nav-link
                           w-full
                           group
                           flex items-center gap-3
                           px-3 py-2.5
                           rounded-xl
                           text-[12px]
                           font-medium
                           text-white/45
                           hover:bg-red-400/10
                           hover:text-red-300
                           transition"
                >

                    <span
                        class="w-8 h-8
                               rounded-lg
                               flex items-center justify-center
                               shrink-0"
                    >
                        <x-lucide-log-out class="w-4 h-4" />
                    </span>

                    <span class="sidebar-expanded-only">
                        Log Out
                    </span>

                </button>

            </form>

        </div>

    </aside>


    {{-- =========================================================
        MAIN COLUMN
    ========================================================= --}}
    <div
        class="flex-1 min-w-0
               flex flex-col
               overflow-hidden"
    >

        {{-- =====================================================
            TOPBAR
        ===================================================== --}}
        <header
            class="h-[68px]
                   bg-white/95
                   backdrop-blur
                   border-b border-gray-border
                   flex items-center
                   px-3 sm:px-4 lg:px-5
                   shrink-0
                   z-30"
        >

            {{-- Mobile menu --}}
            <button
                id="logisticsMobileToggle"
                type="button"
                class="lg:hidden
                       w-9 h-9
                       rounded-lg
                       border border-gray-border
                       text-navy/55
                       flex items-center justify-center
                       hover:bg-gray-bg
                       transition
                       shrink-0"
            >
                <x-lucide-menu class="w-4 h-4" />
            </button>


            {{-- NEW: Search / command trigger — same place and style as
                 the admin layout's topbar search bar. --}}
            <button
                id="logisticsSearchTrigger"
                type="button"
                class="group
                       ml-2 lg:ml-0
                       w-full max-w-md
                       flex items-center gap-2.5
                       h-9 px-3
                       rounded-lg
                       bg-gray-bg
                       border border-transparent
                       text-left
                       hover:bg-white hover:border-gray-border
                       transition"
                aria-label="Search logistics tools"
            >
                <x-lucide-search class="w-3.5 h-3.5 text-navy/35 shrink-0" />

                <span class="text-[11px] text-navy/35 truncate flex-1">
                    Search logistics tools...
                </span>

                <kbd
                    class="hidden sm:inline-flex items-center
                           px-1.5 py-0.5 rounded-md
                           bg-white border border-gray-border
                           text-[8px] font-semibold text-navy/30"
                >
                    Ctrl K
                </kbd>
            </button>


            {{-- Page title — kept, but tucked in after the search bar so
                 both fit comfortably on wider screens. --}}



            <div
                class="ml-auto
                       flex items-center
                       gap-2"
            >

                @include('partials.theme-toggle')

                {{-- Website --}}
                
                    <a href="{{ route('home') }}"
                    target="_blank"
                    class="hidden sm:inline-flex
                           items-center gap-1.5
                           h-9 px-3
                           rounded-lg
                           text-[10px]
                           font-semibold
                           text-navy/45
                           hover:text-teal-dark
                           hover:bg-gray-bg
                           transition"
                >
                    <x-lucide-external-link class="w-3.5 h-3.5" />

                    View ShopHop
                </a>


                <div
                    class="hidden sm:block
                           w-px h-6
                           bg-gray-border"
                ></div>


                {{-- Dedicated, keyboard-accessible partner menu (no JavaScript required). --}}
                <details class="logistics-profile-menu relative">
                    <summary class="flex h-10 cursor-pointer list-none items-center gap-2 rounded-xl px-1.5 transition hover:bg-gray-bg focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal" aria-label="Partner account menu">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-teal to-sky text-[10px] font-bold text-navy">{{ $logisticsInitials }}</span>
                        <span class="hidden min-w-0 max-w-36 text-left md:block">
                            <span class="block truncate text-[10px] font-semibold text-navy">{{ $logisticsName }}</span>
                            <span class="block truncate text-[8px] text-navy/40">{{ $logisticsEmail }}</span>
                        </span>
                        <x-lucide-chevron-down class="hidden h-3.5 w-3.5 text-navy/35 md:block" />
                    </summary>
                    <div class="absolute right-0 top-[calc(100%+10px)] z-[85] w-56 rounded-2xl border border-gray-border bg-white p-2 shadow-panel">
                        <div class="border-b border-gray-border px-3 py-2">
                            <p class="truncate text-[11px] font-bold text-navy">{{ $logisticsName }}</p>
                            <p class="truncate text-[10px] text-navy/45">Logistics partner</p>
                        </div>
                        <a href="{{ route('logistics.dashboard') }}" class="mt-1 flex items-center gap-2 rounded-lg px-3 py-2 text-[11px] text-navy/70 hover:bg-gray-bg"><x-lucide-layout-dashboard class="h-4 w-4" /> Dashboard</a>
                        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 rounded-lg px-3 py-2 text-[11px] text-navy/70 hover:bg-gray-bg"><x-lucide-external-link class="h-4 w-4" /> Main ShopHop</a>
                        <form method="POST" action="{{ route('logistics.logout') }}" class="mt-1 border-t border-gray-border pt-1">@csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-[11px] font-semibold text-rose-600 hover:bg-rose-50"><x-lucide-log-out class="h-4 w-4" /> Sign out</button>
                        </form>
                    </div>
                </details>
            </div>

        </header>


        {{-- =====================================================
            SESSION ALERTS
        ===================================================== --}}
        @if (session('success'))

            <div
                class="px-3 sm:px-4 lg:px-5
                       pt-3 shrink-0"
            >

                <div
                    class="max-w-[1600px] mx-auto
                           flex items-center gap-3
                           rounded-xl
                           bg-teal-light
                           border border-teal/20
                           px-4 py-3"
                >

                    <x-lucide-circle-check
                        class="w-4 h-4
                               text-teal-dark
                               shrink-0"
                    />

                    <p
                        class="text-xs
                               font-medium
                               text-teal-dark"
                    >
                        {{ session('success') }}
                    </p>

                </div>

            </div>

        @endif


        {{-- =====================================================
            CONTENT
        ===================================================== --}}
        <main
            class="content-scrollbar
                   flex-1
                   overflow-y-auto
                   px-3 py-4
                   sm:px-4 sm:py-5
                   lg:px-5 lg:py-5"
        >

            <div class="max-w-[1600px] mx-auto">

                @yield('content')

            </div>

        </main>

    </div>

</div>


{{-- =========================================================
    NEW: COMMAND / SEARCH PALETTE
    Same structure as the admin layout's #adminSearchPalette, built
    from $logisticsNavigation so it always matches the sidebar.
========================================================= --}}
<div
    id="logisticsSearchPalette"
    hidden
    class="fixed inset-0 z-[90]
           flex items-start justify-center
           px-3 pt-[10vh] sm:pt-[14vh]"
    aria-hidden="true"
>

    <button
        id="logisticsSearchBackdrop"
        type="button"
        class="absolute inset-0
               bg-navy/50 backdrop-blur-[2px]"
        aria-label="Close search"
    ></button>


    <div
        class="relative
               w-full max-w-xl
               bg-white
               border border-gray-border
               rounded-2xl shadow-2xl
               overflow-hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="logisticsSearchTitle"
    >

        <div class="flex items-center gap-3 px-4 border-b border-gray-border">

            <x-lucide-search class="w-4 h-4 text-teal-dark shrink-0" />

            <input
                id="logisticsCommandInput"
                type="text"
                autocomplete="off"
                placeholder="Find a logistics page or tool..."
                class="w-full h-12
                       bg-transparent
                       text-xs sm:text-sm text-navy
                       placeholder:text-navy/30
                       focus:outline-none"
            >

            <kbd
                class="px-1.5 py-0.5 rounded-md
                       bg-gray-bg border border-gray-border
                       text-[8px] font-semibold text-navy/30"
            >
                ESC
            </kbd>

        </div>


        <div
            id="logisticsCommandResults"
            class="max-h-[360px] overflow-y-auto p-2"
        >

            <p
                id="logisticsSearchTitle"
                class="px-2 py-2
                       text-[8px] font-bold uppercase tracking-[0.14em]
                       text-navy/30"
            >
                Logistics Navigation
            </p>


            @foreach ($logisticsNavigation as $section)
                @foreach ($section['items'] as $item)

                    <a
                        href="{{ route($item['route']) }}"
                        data-logistics-command-item
                        data-search-text="{{ strtolower($item['label'] . ' ' . $section['label']) }}"
                        class="group
                               flex items-center gap-3
                               px-2.5 py-2.5 rounded-xl
                               hover:bg-gray-bg
                               transition"
                    >
                        <div
                            class="w-8 h-8 rounded-lg
                                   bg-gray-bg text-teal-dark
                                   flex items-center justify-center
                                   shrink-0"
                        >
                            <x-dynamic-component
                                :component="'lucide-' . $item['icon']"
                                class="w-4 h-4"
                            />
                        </div>

                        <div class="min-w-0 flex-1">

                            <p class="text-[11px] font-semibold text-navy">
                                {{ $item['label'] }}
                            </p>

                            <p class="text-[8px] text-navy/35 mt-0.5">
                                {{ $section['label'] }}
                            </p>

                        </div>


                        @if (!empty($item['badge']))
                            <span class="text-[9px] font-bold bg-red-500 text-white px-1.5 py-0.5 rounded-full leading-none shrink-0">
                                {{ $item['badge'] }}
                            </span>
                        @endif


                        <x-lucide-corner-down-left
                            class="w-3.5 h-3.5 text-navy/20
                                   group-hover:text-teal-dark"
                        />
                    </a>

                @endforeach
            @endforeach


            <div
                id="logisticsCommandEmpty"
                hidden
                class="px-4 py-10 text-center"
            >
                <div
                    class="w-10 h-10 mx-auto
                           rounded-xl bg-gray-bg text-navy/25
                           flex items-center justify-center"
                >
                    <x-lucide-search-x class="w-4 h-4" />
                </div>

                <p class="text-[11px] font-semibold text-navy/50 mt-3">
                    No logistics tool found
                </p>

                <p class="text-[9px] text-navy/30 mt-1">
                    Try another keyword.
                </p>
            </div>

        </div>

    </div>
</div>



<div id="logisticsConfirmModal" hidden class="fixed inset-0 z-[95] flex items-center justify-center px-4">
    <button type="button" class="sh-modal-backdrop" data-confirm-close aria-label="Close confirmation dialog"></button>
    <div class="sh-modal-card" role="dialog" aria-modal="true" aria-labelledby="logisticsConfirmTitle" aria-describedby="logisticsConfirmBody">
        <div class="sh-modal-head flex items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                <x-lucide-shield-alert class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <p id="logisticsConfirmTitle" class="text-base font-bold text-navy">Confirm action</p>
                <p id="logisticsConfirmBody" class="mt-1 text-sm leading-relaxed text-navy/55">Please confirm to continue.</p>
            </div>
        </div>
        <div class="sh-modal-actions">
            <button type="button" class="sh-btn-ghost" data-confirm-close>Cancel</button>
            <button type="button" class="sh-btn" id="logisticsConfirmAccept">Continue</button>
        </div>
    </div>
</div>


{{-- =========================================================
    SIDEBAR JS (mobile toggle + desktop collapse)
========================================================= --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const shell =
        document.getElementById('logisticsShell');

    const sidebar =
        document.getElementById('logisticsSidebar');

    const toggle =
        document.getElementById('logisticsMobileToggle');

    const overlay =
        document.getElementById('logisticsMobileOverlay');

    const sidebarClose =
        document.getElementById('logisticsSidebarClose');


    /* =====================================================
       MOBILE OPEN / CLOSE
    ===================================================== */
    function openSidebar() {

        shell?.setAttribute(
            'data-mobile-open',
            'true'
        );

        if (overlay) {
            overlay.hidden = false;
        }

        document.body.style.overflow = 'hidden';

    }


    function closeSidebar() {

        shell?.setAttribute(
            'data-mobile-open',
            'false'
        );

        if (overlay) {
            overlay.hidden = true;
        }

        document.body.style.overflow = '';

    }


    toggle?.addEventListener(
        'click',
        openSidebar
    );


    overlay?.addEventListener(
        'click',
        closeSidebar
    );


    sidebarClose?.addEventListener(
        'click',
        closeSidebar
    );


    document
        .querySelectorAll('#logisticsSidebar a, #logisticsSidebar button[type="submit"]')
        .forEach(function (link) {

            link.addEventListener(
                'click',
                function () {

                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }

                }
            );

        });


    /* =====================================================
       DESKTOP COLLAPSE / EXPAND
    ===================================================== */
    const collapseStorageKey = 'shophop_logistics_sidebar_collapsed';

    const collapseBtn =
        document.getElementById('logisticsDesktopCollapse');

    const expandBtn =
        document.getElementById('logisticsDesktopExpand');


    function setCollapsed(collapsed) {

        const apply =
            window.innerWidth >= 1024 && collapsed;

        sidebar?.setAttribute(
            'data-collapsed',
            apply ? 'true' : 'false'
        );

        localStorage.setItem(
            collapseStorageKey,
            apply ? '1' : '0'
        );

    }


    setCollapsed(
        localStorage.getItem(collapseStorageKey) === '1'
    );


    collapseBtn?.addEventListener(
        'click',
        function () {
            setCollapsed(true);
        }
    );


    expandBtn?.addEventListener(
        'click',
        function (event) {

            event.preventDefault();

            setCollapsed(false);

        }
    );


    window.addEventListener(
        'resize',
        function () {

            if (window.innerWidth >= 1024) {

                closeSidebar();

                setCollapsed(
                    localStorage.getItem(collapseStorageKey) === '1'
                );

            } else {

                setCollapsed(false);

            }

        }
    );


    /* =====================================================
       NEW: COMMAND PALETTE (mirrors the admin layout's version)
    ===================================================== */
    const searchTrigger =
        document.getElementById('logisticsSearchTrigger');

    const searchPalette =
        document.getElementById('logisticsSearchPalette');

    const searchBackdrop =
        document.getElementById('logisticsSearchBackdrop');

    const commandInput =
        document.getElementById('logisticsCommandInput');

    const commandItems =
        Array.from(
            document.querySelectorAll(
                '[data-logistics-command-item]'
            )
        );

    const commandEmpty =
        document.getElementById('logisticsCommandEmpty');


    function filterCommands() {

        const query =
            (commandInput?.value || '')
                .trim()
                .toLowerCase();

        let visibleCount = 0;


        commandItems.forEach(function (item) {

            const searchText =
                item.getAttribute('data-search-text') || '';

            const visible =
                query === '' ||
                searchText.includes(query);

            item.hidden = !visible;

            if (visible) {
                visibleCount++;
            }

        });


        if (commandEmpty) {
            commandEmpty.hidden =
                visibleCount > 0;
        }

    }


    function openCommandPalette() {

        if (!searchPalette) {
            return;
        }

        searchPalette.hidden = false;

        searchPalette.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.style.overflow = 'hidden';

        window.setTimeout(function () {

            commandInput?.focus();
            commandInput?.select();

        }, 0);

    }


    function closeCommandPalette() {

        if (!searchPalette) {
            return;
        }

        searchPalette.hidden = true;

        searchPalette.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.style.overflow = '';

        if (commandInput) {
            commandInput.value = '';
        }

        filterCommands();

    }


    searchTrigger?.addEventListener(
        'click',
        openCommandPalette
    );


    searchBackdrop?.addEventListener(
        'click',
        closeCommandPalette
    );


    commandInput?.addEventListener(
        'input',
        filterCommands
    );


    commandInput?.addEventListener(
        'keydown',
        function (event) {

            if (event.key !== 'Enter') {
                return;
            }

            const firstVisible =
                commandItems.find(
                    item => !item.hidden
                );

            if (firstVisible) {
                window.location.href =
                    firstVisible.href;
            }

        }
    );


    /* =====================================================
       GLOBAL KEYBOARD SHORTCUTS
    ===================================================== */
    document.addEventListener('keydown', function (event) {

        const isCommandShortcut =
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 'k';


        if (isCommandShortcut) {

            event.preventDefault();
            openCommandPalette();

            return;
        }


        if (event.key === 'Escape') {

            if (
                searchPalette &&
                !searchPalette.hidden
            ) {
                closeCommandPalette();
                return;
            }

            if (
                shell?.getAttribute(
                    'data-mobile-open'
                ) === 'true'
            ) {
                closeSidebar();
            }

        }

    });


    filterCommands();


    /* =====================================================
       GLOBAL CONFIRMATION MODAL
    ===================================================== */
    const confirmModal = document.getElementById('logisticsConfirmModal');
    const confirmTitle = document.getElementById('logisticsConfirmTitle');
    const confirmBody = document.getElementById('logisticsConfirmBody');
    const confirmAccept = document.getElementById('logisticsConfirmAccept');
    let confirmHandler = null;
    let previousConfirmFocus = null;

    function openConfirmModal(options) {
        if (!confirmModal) return;
        confirmTitle.textContent = options.title || 'Confirm action';
        confirmBody.textContent = options.message || 'Please confirm to continue.';
        confirmAccept.textContent = options.confirmLabel || 'Continue';
        confirmAccept.className = (options.danger ? 'sh-btn-danger' : 'sh-btn') + ' px-5';
        confirmHandler = options.onConfirm || null;
        previousConfirmFocus = document.activeElement;
        confirmModal.hidden = false;
        document.body.style.overflow = 'hidden';
        confirmModal.querySelector('[data-confirm-close]:not(.sh-modal-backdrop)')?.focus();
    }

    function closeConfirmModal() {
        if (!confirmModal) return;
        confirmModal.hidden = true;
        if (searchPalette && !searchPalette.hidden) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        confirmHandler = null;
        previousConfirmFocus?.focus?.();
        previousConfirmFocus = null;
    }

    document.querySelectorAll('[data-confirm-close]').forEach(function(btn){
        btn.addEventListener('click', closeConfirmModal);
    });

    confirmAccept?.addEventListener('click', function(){
        if (typeof confirmHandler === 'function') {
            const run = confirmHandler;
            closeConfirmModal();
            run();
        }
    });

    document.addEventListener('submit', function(event){
        const form = event.target.closest('form[data-confirm-title]');
        if (!form || form.dataset.confirmed === '1') {
            if (form) form.dataset.confirmed = '0';
            return;
        }
        event.preventDefault();
        openConfirmModal({
            title: form.dataset.confirmTitle,
            message: form.dataset.confirmMessage || 'Please confirm to continue.',
            confirmLabel: form.dataset.confirmButton || 'Continue',
            danger: form.dataset.confirmDanger === '1',
            onConfirm: function(){
                form.dataset.confirmed = '1';
                form.requestSubmit();
            }
        });
    }, true);

    function syncCoverageRemoveButtons() {
        document.querySelectorAll('[data-coverage-list]').forEach(function(list){
            const buttons = list.querySelectorAll('[data-remove-coverage]');
            const soleRow = list.querySelectorAll('[data-coverage-row]').length <= 1;
            buttons.forEach(function(button){
                button.disabled = soleRow;
                button.title = soleRow ? 'Keep at least one coverage area' : 'Remove this coverage area';
                button.classList.toggle('opacity-40', soleRow);
                button.classList.toggle('cursor-not-allowed', soleRow);
            });
        });
    }

    document.addEventListener('click', function(event){
        const add = event.target.closest('[data-add-coverage]');
        if (add) {
            // The page's own builder handles adding and renumbering rows.
            window.requestAnimationFrame(syncCoverageRemoveButtons);
            return;
        }
        const removeCoverage = event.target.closest('[data-remove-coverage]');
        if (!removeCoverage || removeCoverage.disabled) return;
        event.preventDefault();
        const row = removeCoverage.closest('[data-coverage-row]');
        const list = removeCoverage.closest('[data-coverage-list]');
        if (!row || !list || list.querySelectorAll('[data-coverage-row]').length <= 1) return;
        openConfirmModal({
            title: 'Remove coverage area?',
            message: 'This will remove the area from the form. Changes are not saved until you submit the branch form.',
            confirmLabel: 'Remove area',
            danger: true,
            onConfirm: function(){
                row.remove();
                list.querySelectorAll('[data-coverage-row]').forEach(function(row, index){
                    row.querySelectorAll('[name]').forEach(function(field){
                        field.name = field.name.replace(/coverage\[\d+\]/, 'coverage[' + index + ']');
                    });
                });
                syncCoverageRemoveButtons();
            }
        });
    });

    document.addEventListener('keydown', function(event){
        if (!confirmModal || confirmModal.hidden) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeConfirmModal();
        } else if (event.key === 'Tab') {
            const focusables = [...confirmModal.querySelectorAll('.sh-modal-card button:not([disabled])')]
                .filter(el => el.offsetParent !== null);
            if (!focusables.length) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault(); last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first.focus();
            }
        }
    }, true);
    syncCoverageRemoveButtons();

});

</script>


@stack('scripts')

</body>

</html>
