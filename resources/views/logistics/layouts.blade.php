<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

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
                        navy: '#0F2C3F',
                        'navy-light': '#173B52',
                        'navy-soft': '#244B61',

                        teal: '#2ECFA6',
                        'teal-dark': '#22B593',
                        'teal-light': '#E9F8F4',

                        mint: '#2ECFA6',
                        'mint-dark': '#22B593',

                        sky: '#4AA8E0',
                        yellow: '#F7C948',
                        coral: '#FF7A59',

                        'gray-bg': '#F4F7F8',
                        'gray-border': '#E4EAEE',
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
            'label' => 'Rider Management',

            'items' => [

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
            'label' => 'Deliveries',

            'items' => [

                [
                    'label' => 'Delivery Board',
                    'route' => 'logistics.deliveries.board',
                    'pattern' => 'logistics.deliveries.*',
                    'icon' => 'package-check',
                ],

            ],
        ],


        [
            'label' => 'Insights',

            'items' => [

                [
                    'label' => 'Reports',
                    'route' => 'logistics.reports.index',
                    'pattern' => 'logistics.reports.*',
                    'icon' => 'chart-no-axes-combined',
                ],

            ],
        ],

    ];

@endphp


<body class="bg-gray-bg text-navy antialiased">

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
                action="{{ route('logout') }}"
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
                    Search admin tools...
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


                {{-- Profile --}}
                <div
                    class="flex items-center
                           gap-2
                           h-10
                           pl-1 pr-2"
                >

                    <div
                        class="w-8 h-8
                               rounded-full
                               bg-gradient-to-br
                               from-teal to-sky
                               text-navy
                               flex items-center justify-center
                               text-[10px]
                               font-bold"
                    >
                        {{ $logisticsInitials }}
                    </div>


                    <div
                        class="hidden md:block
                               max-w-36"
                    >

                        <p
                            class="text-[10px]
                                   font-semibold
                                   text-navy
                                   truncate"
                        >
                            {{ $logisticsName }}
                        </p>

                        <p
                            class="text-[8px]
                                   text-navy/35
                                   truncate"
                        >
                            {{ $logisticsEmail }}
                        </p>

                    </div>

                </div>

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
                placeholder="Search riders, deliveries, reports..."
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

});

</script>


@stack('scripts')

</body>

</html>