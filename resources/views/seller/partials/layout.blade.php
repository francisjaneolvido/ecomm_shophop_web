<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Seller') - ShopHop Seller</title>

    {{-- =========================================================
        TAILWIND
        Same config as admin.layout — keep both in sync if the
        palette ever changes.
    ========================================================= --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        /*
                        |--------------------------------------------------------------------------
                        | SHOPHOP CORE PALETTE
                        |--------------------------------------------------------------------------
                        | Shared with admin.layout so buyer/seller/admin surfaces
                        | all read as one product.
                        */
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
                        sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },

                    boxShadow: {
                        'soft': '0 10px 30px rgba(15, 44, 63, 0.08)',
                        'panel': '0 18px 50px rgba(15, 44, 63, 0.12)',
                    },
                }
            }
        }
    </script>


    {{-- =========================================================
        FONT
    ========================================================= --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    {{-- =========================================================
        GLOBAL SELLER STYLES
        Mirrors admin.layout's structural styles 1:1 so the two
        shells behave identically (collapse, mobile drawer, etc.)
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

        [data-cloak] {
            display: none !important;
        }

        .admin-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .16) transparent;
        }

        .admin-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .admin-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .16);
            border-radius: 999px;
        }

        .content-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(15, 44, 63, .16) transparent;
        }

        .content-scrollbar::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        .content-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(15, 44, 63, .14);
            border-radius: 999px;
        }

        #sellerShell[data-sidebar-collapsed="true"] #sellerSidebar {
            width: 5rem;
        }

        .sidebar-collapsed-only {
            display: none;
        }

        #sellerShell[data-sidebar-collapsed="true"] .sidebar-expanded-only {
            display: none;
        }

        #sellerShell[data-sidebar-collapsed="true"] .sidebar-collapsed-only {
            display: flex;
        }

        #sellerShell[data-sidebar-collapsed="true"] .sidebar-nav-link {
            justify-content: center;
            padding-left: .75rem;
            padding-right: .75rem;
        }

        #sellerShell[data-sidebar-collapsed="true"] .sidebar-section-label {
            display: none;
        }

        #sellerShell[data-sidebar-collapsed="true"] .sidebar-logo-wrap {
            justify-content: center;
            padding-left: .75rem;
            padding-right: .75rem;
        }

        #sellerShell[data-sidebar-collapsed="true"] .sidebar-tooltip {
            display: block;
        }

        .sidebar-tooltip {
            display: none;
        }

        #sellerMobileOverlay[hidden],
        #sellerSearchPalette[hidden],
        #sellerNotificationMenu[hidden],
        #sellerProfileMenu[hidden] {
            display: none !important;
        }

        @media (max-width: 1023px) {
            #sellerSidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: 60;
                transform: translateX(-100%);
                width: 17rem !important;
                transition: transform .22s ease;
            }

            #sellerShell[data-mobile-sidebar-open="true"] #sellerSidebar {
                transform: translateX(0);
            }

            #sellerShell[data-sidebar-collapsed="true"] #sellerSidebar {
                width: 17rem !important;
            }

            #sellerShell[data-sidebar-collapsed="true"] .sidebar-expanded-only,
            #sellerShell[data-sidebar-collapsed="true"] .sidebar-section-label {
                display: initial;
            }

            #sellerShell[data-sidebar-collapsed="true"] .sidebar-nav-link {
                justify-content: flex-start;
                padding-left: .75rem;
                padding-right: .75rem;
            }

            #sellerShell[data-sidebar-collapsed="true"] .sidebar-logo-wrap {
                justify-content: flex-start;
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }

            #sellerShell[data-sidebar-collapsed="true"] .sidebar-tooltip {
                display: none;
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
    | SELLER DISPLAY DATA
    |--------------------------------------------------------------------------
    | Guest-safe defaults so the shell renders even before auth/seller
    | profile wiring lands.
    */
    $authSeller = auth()->user();

    $sellerEmail = $authSeller?->email ?? 'seller@shophop.com';

    $sellerDisplayName = $sellerName
        ?? $authSeller?->name
        ?? ($authSeller?->email ? ucfirst(explode('@', $authSeller->email)[0]) : null)
        ?? 'ShopHop Seller';

    $sellerBusinessName = $sellerBusinessName
        ?? $authSeller?->business_name
        ?? 'My Store';

    $sellerInitials = collect(preg_split('/[\s._-]+/', trim($sellerDisplayName)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');

    if ($sellerInitials === '') {
        $sellerInitials = 'SL';
    }

    $sellerNotificationCount = $sellerNotificationCount ?? 0;

    /*
    |--------------------------------------------------------------------------
    | SIDEBAR NAVIGATION
    |--------------------------------------------------------------------------
    | Centralized here so the UI is easier to customize later —
    | same convention as admin.layout.
    */
    $sellerNavigation = [
        [
            'label' => 'Overview',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'seller.dashboard',
                    'pattern' => 'seller.dashboard',
                    'icon' => 'layout-dashboard',
                ],
            ],
        ],

        [
            'label' => 'Catalog',
            'items' => [
                [
                    'label' => 'Manage Inventory',
                    'route' => 'seller.inventory',
                    'pattern' => 'seller.inventory*',
                    'icon' => 'package',
                ],
            ],
        ],

        [
            'label' => 'Orders',
            'items' => [
                [
                    'label' => 'Order Notifications',
                    'route' => 'seller.orders.notifications',
                    'pattern' => 'seller.orders.notifications*',
                    'icon' => 'bell-ring',
                ],
                [
                    'label' => 'Prepare Orders',
                    'route' => 'seller.orders.prepare',
                    'pattern' => 'seller.orders.prepare*',
                    'icon' => 'box',
                ],
                [
                    'label' => 'Courier Hand-Over',
                    'route' => 'seller.orders.courier',
                    'pattern' => 'seller.orders.courier*',
                    'icon' => 'truck',
                ],
                [
                    'label' => 'Delivery Confirmations',
                    'route' => 'seller.orders.confirm',
                    'pattern' => 'seller.orders.confirm*',
                    'icon' => 'package-check',
                ],
                [
                    'label' => 'Customer Feedback',
                    'route' => 'seller.feedback',
                    'pattern' => 'seller.feedback*',
                    'icon' => 'star',
                ],
            ],
        ],

        [
            'label' => 'Insights',
            'items' => [
                [
                    'label' => 'Generate Report',
                    'route' => 'seller.reports',
                    'pattern' => 'seller.reports*',
                    'icon' => 'file-chart-column-increasing',
                ],
            ],
        ],

        [
            'label' => 'System',
            'items' => [
                [
                    'label' => 'Chat / Messaging',
                    'route' => 'seller.chat',
                    'pattern' => 'seller.chat*',
                    'icon' => 'messages-square',
                ],
                [
                    'label' => 'Account Management',
                    'route' => 'seller.account',
                    'pattern' => 'seller.account*',
                    'icon' => 'user-cog',
                ],
            ],
        ],
    ];
@endphp


<body class="bg-gray-bg text-navy antialiased">

<div
    id="sellerShell"
    data-sidebar-collapsed="false"
    data-mobile-sidebar-open="false"
    class="flex h-screen overflow-hidden"
>

    {{-- =========================================================
        MOBILE OVERLAY
    ========================================================= --}}
    <button
        id="sellerMobileOverlay"
        type="button"
        hidden
        class="fixed inset-0 z-50 bg-navy/45 backdrop-blur-[1px] lg:hidden"
        aria-label="Close navigation"
    ></button>


    {{-- =========================================================
        SIDEBAR
    ========================================================= --}}
    <aside
        id="sellerSidebar"
        class="relative
               w-64
               bg-navy text-white
               flex flex-col shrink-0
               border-r border-white/5
               transition-[width,transform] duration-200"
    >

        {{-- Background decor --}}
        <div class="pointer-events-none absolute -top-20 -left-20 w-48 h-48 rounded-full bg-teal/10 blur-2xl"></div>


        {{-- Logo --}}
        <div
            class="sidebar-logo-wrap relative
                   h-[68px]
                   flex items-center gap-3
                   px-5
                   border-b border-white/10
                   shrink-0"
        >

            <a
                id="sellerBrandLink"
                href="{{ route('seller.dashboard') }}"
                class="flex items-center gap-3 min-w-0"
                aria-label="ShopHop Seller Dashboard"
                title="Go to Seller Dashboard"
            >
                <div class="w-9 h-9 flex items-center justify-center shrink-0">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="ShopHop Logo"
                        class="w-9 h-9 object-contain"
                    >
                </div>


                <div class="sidebar-expanded-only min-w-0">

                    <p class="text-[15px] font-bold leading-tight tracking-tight text-white">
                        Shop<span class="text-teal">Hop</span>
                    </p>

                    <p class="text-[8px] uppercase tracking-[0.18em] text-white/35 font-semibold mt-0.5">
                        Seller Console
                    </p>

                </div>
            </a>


            <button
                id="desktopSidebarToggle"
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
                title="Collapse sidebar"
            >
                <x-lucide-panel-left-close class="w-3.5 h-3.5" />
            </button>

        </div>


        {{-- Desktop expand control shown only while collapsed --}}
        <button
            id="desktopSidebarEdgeExpand"
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
            title="Expand sidebar"
        >
            <x-lucide-chevron-right class="w-3.5 h-3.5" />
        </button>


        {{-- Navigation --}}
        <nav
            class="admin-scrollbar
                   relative flex-1
                   overflow-y-auto
                   px-3 py-4"
            aria-label="Seller navigation"
        >

            @foreach ($sellerNavigation as $section)

                <div class="{{ ! $loop->first ? 'mt-5' : '' }}">

                    <p
                        class="sidebar-section-label
                               px-3 mb-1.5
                               text-[8px] uppercase tracking-[0.16em]
                               font-bold text-white/25"
                    >
                        {{ $section['label'] }}
                    </p>


                    <div class="space-y-1">

                        @foreach ($section['items'] as $item)

                            @php
                                $isActive = request()->routeIs($item['pattern']);
                            @endphp


                            <div class="relative group">

                                <a
                                    href="{{ route($item['route']) }}"
                                    class="sidebar-nav-link
                                           relative
                                           flex items-center gap-3
                                           px-3 py-2.5 rounded-xl
                                           text-[12px]
                                           transition-all duration-150

                                           {{
                                               $isActive
                                                   ? 'bg-white/10 text-white font-semibold shadow-sm'
                                                   : 'text-white/55 hover:text-white hover:bg-white/[0.06] font-medium'
                                           }}"
                                >

                                    @if ($isActive)
                                        <span
                                            class="absolute left-0 top-1/2 -translate-y-1/2
                                                   w-0.5 h-5 rounded-r-full
                                                   bg-teal"
                                        ></span>
                                    @endif


                                    <span
                                        class="relative
                                               w-8 h-8 rounded-lg
                                               flex items-center justify-center
                                               shrink-0

                                               {{
                                                   $isActive
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


                                    @if ($isActive)
                                        <span
                                            class="sidebar-expanded-only
                                                   w-1.5 h-1.5 rounded-full bg-teal
                                                   shrink-0"
                                        ></span>
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


        {{-- Sidebar bottom --}}
        <div class="relative p-3 border-t border-white/10 shrink-0">

            {{-- Seller mini profile --}}
            <div
                class="sidebar-expanded-only
                       flex items-center gap-3
                       px-2 py-2 mb-1"
            >
                <div
                    class="w-8 h-8 rounded-full
                           bg-gradient-to-br from-teal to-sky
                           text-navy
                           flex items-center justify-center
                           text-[10px] font-bold
                           shrink-0"
                >
                    {{ $sellerInitials }}
                </div>


                <div class="min-w-0 flex-1">

                    <p class="text-[11px] font-semibold text-white truncate">
                        {{ $sellerBusinessName }}
                    </p>

                    <p class="text-[8px] text-white/30 truncate mt-0.5">
                        Seller Account
                    </p>

                </div>
            </div>


            <a
                href="{{ route('seller.logout') }}"
                class="sidebar-nav-link
                       group
                       flex items-center gap-3
                       px-3 py-2.5 rounded-xl
                       text-[12px] font-medium
                       text-white/45
                       hover:bg-red-400/10 hover:text-red-300
                       transition"
            >
                <span
                    class="w-8 h-8 rounded-lg
                           flex items-center justify-center
                           group-hover:bg-red-400/10
                           transition"
                >
                    <x-lucide-log-out class="w-4 h-4" />
                </span>

                <span class="sidebar-expanded-only">
                    Log Out
                </span>
            </a>

        </div>

    </aside>


    {{-- =========================================================
        MAIN COLUMN
    ========================================================= --}}
    <div class="flex-1 min-w-0 flex flex-col overflow-hidden">

        {{-- =====================================================
            TOPBAR
        ===================================================== --}}
        <header
            class="h-[68px]
                   bg-white/95 backdrop-blur
                   border-b border-gray-border
                   flex items-center
                   px-3 sm:px-4 lg:px-5
                   shrink-0 z-30"
        >

            {{-- Mobile menu --}}
            <button
                id="mobileSidebarToggle"
                type="button"
                class="lg:hidden
                       w-9 h-9 rounded-lg
                       border border-gray-border
                       text-navy/55
                       flex items-center justify-center
                       hover:bg-gray-bg hover:text-navy
                       transition shrink-0"
                aria-label="Open navigation"
            >
                <x-lucide-menu class="w-4 h-4" />
            </button>


            {{-- Search / command --}}
            <button
                id="sellerSearchTrigger"
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
                aria-label="Search seller tools"
            >
                <x-lucide-search class="w-3.5 h-3.5 text-navy/35 shrink-0" />

                <span class="text-[11px] text-navy/35 truncate flex-1">
                    Search seller tools...
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


            <div class="ml-auto flex items-center gap-1.5 sm:gap-2">

                {{-- Visit storefront --}}
                <a
                    href="{{ route('seller.storefront') }}"
                    target="_blank"
                    class="hidden md:inline-flex
                           items-center gap-1.5
                           h-9 px-3 rounded-lg
                           text-[10px] font-semibold text-navy/45
                           hover:text-teal-dark hover:bg-gray-bg
                           transition"
                >
                    <x-lucide-external-link class="w-3.5 h-3.5" />
                    View Storefront
                </a>


                {{-- Notifications --}}
                <div class="relative">

                    <button
                        id="sellerNotificationToggle"
                        type="button"
                        class="relative
                               w-9 h-9 rounded-lg
                               text-navy/50
                               flex items-center justify-center
                               hover:bg-gray-bg hover:text-navy
                               transition"
                        aria-label="Notifications"
                        aria-expanded="false"
                    >
                        <x-lucide-bell class="w-4 h-4" />

                        @if ($sellerNotificationCount > 0)
                            <span
                                class="absolute top-1.5 right-1.5
                                       min-w-2 h-2 rounded-full
                                       bg-coral ring-2 ring-white"
                            ></span>
                        @endif
                    </button>


                    <div
                        id="sellerNotificationMenu"
                        hidden
                        class="absolute right-0 top-[calc(100%+10px)]
                               w-[310px] max-w-[calc(100vw-24px)]
                               bg-white
                               border border-gray-border
                               rounded-xl shadow-panel
                               overflow-hidden z-[80]"
                    >
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-border">

                            <div>
                                <p class="text-xs font-bold text-navy">
                                    Notifications
                                </p>

                                <p class="text-[9px] text-navy/35 mt-0.5">
                                    New orders and store updates
                                </p>
                            </div>

                            @if ($sellerNotificationCount > 0)
                                <span
                                    class="px-2 py-1 rounded-full
                                           bg-teal/10 text-teal-dark
                                           text-[9px] font-bold"
                                >
                                    {{ $sellerNotificationCount }} new
                                </span>
                            @endif

                        </div>


                        <div class="px-4 py-8 text-center">

                            <div
                                class="w-10 h-10 mx-auto
                                       rounded-xl bg-gray-bg text-navy/30
                                       flex items-center justify-center"
                            >
                                <x-lucide-bell-ring class="w-4 h-4" />
                            </div>

                            <p class="text-[11px] font-semibold text-navy/60 mt-3">
                                No notification feed yet
                            </p>

                            <p class="text-[9px] text-navy/35 mt-1">
                                New order and stock alerts will appear here.
                            </p>

                        </div>
                    </div>

                </div>


                <div class="hidden sm:block w-px h-6 bg-gray-border mx-0.5"></div>


                {{-- Profile --}}
                <div class="relative">

                    <button
                        id="sellerProfileToggle"
                        type="button"
                        class="group
                               flex items-center gap-2
                               h-10 pl-1 pr-1 sm:pr-2
                               rounded-xl
                               hover:bg-gray-bg
                               transition"
                        aria-label="Seller account menu"
                        aria-expanded="false"
                    >
                        <div
                            class="w-8 h-8 rounded-full
                                   bg-gradient-to-br from-teal to-sky
                                   text-navy
                                   flex items-center justify-center
                                   text-[10px] font-bold
                                   shrink-0"
                        >
                            {{ $sellerInitials }}
                        </div>


                        <div class="hidden xl:block text-left min-w-0 max-w-32">

                            <p class="text-[10px] font-semibold text-navy truncate">
                                {{ $sellerBusinessName }}
                            </p>

                            <p class="text-[8px] text-navy/35 truncate mt-0.5">
                                Seller
                            </p>

                        </div>


                        <x-lucide-chevron-down
                            class="hidden xl:block w-3.5 h-3.5 text-navy/25
                                   group-hover:text-navy/50"
                        />
                    </button>


                    <div
                        id="sellerProfileMenu"
                        hidden
                        class="absolute right-0 top-[calc(100%+10px)]
                               w-56
                               bg-white
                               border border-gray-border
                               rounded-xl shadow-panel
                               overflow-hidden z-[80]"
                    >

                        <div class="px-4 py-3 border-b border-gray-border">

                            <p class="text-xs font-semibold text-navy truncate">
                                {{ $sellerDisplayName }}
                            </p>

                            <p class="text-[9px] text-navy/35 truncate mt-0.5">
                                {{ $sellerEmail }}
                            </p>

                        </div>


                        <div class="p-1.5">

                            <a
                                href="{{ route('seller.account') }}"
                                class="flex items-center gap-2.5
                                       px-2.5 py-2 rounded-lg
                                       text-[10px] font-medium text-navy/60
                                       hover:bg-gray-bg hover:text-navy
                                       transition"
                            >
                                <x-lucide-user-cog class="w-3.5 h-3.5 text-teal-dark" />
                                Account Management
                            </a>


                            <a
                                href="{{ route('seller.reports') }}"
                                class="flex items-center gap-2.5
                                       px-2.5 py-2 rounded-lg
                                       text-[10px] font-medium text-navy/60
                                       hover:bg-gray-bg hover:text-navy
                                       transition"
                            >
                                <x-lucide-file-chart-column-increasing class="w-3.5 h-3.5 text-teal-dark" />
                                Generate Report
                            </a>

                        </div>


                        <div class="p-1.5 border-t border-gray-border">

                            <a
                                href="{{ route('seller.logout') }}"
                                class="flex items-center gap-2.5
                                       px-2.5 py-2 rounded-lg
                                       text-[10px] font-medium text-red-500
                                       hover:bg-red-50
                                       transition"
                            >
                                <x-lucide-log-out class="w-3.5 h-3.5" />
                                Log Out
                            </a>

                        </div>
                    </div>

                </div>

            </div>
        </header>


        {{-- =====================================================
            SESSION FEEDBACK
        ===================================================== --}}
        @if (session('success') || session('error') || $errors->any())
            <div class="px-3 sm:px-4 lg:px-5 pt-3 shrink-0">

                @if (session('success'))
                    <div
                        data-admin-alert
                        class="flex items-start gap-3
                               max-w-7xl mx-auto
                               bg-teal-light
                               border border-teal/20
                               rounded-xl px-3.5 py-3"
                    >
                        <x-lucide-circle-check class="w-4 h-4 text-teal-dark mt-0.5 shrink-0" />

                        <p class="text-[10px] sm:text-xs font-medium text-teal-dark flex-1">
                            {{ session('success') }}
                        </p>

                        <button
                            type="button"
                            data-admin-alert-close
                            class="text-teal-dark/50 hover:text-teal-dark"
                            aria-label="Dismiss"
                        >
                            <x-lucide-x class="w-3.5 h-3.5" />
                        </button>
                    </div>
                @endif


                @if (session('error'))
                    <div
                        data-admin-alert
                        class="flex items-start gap-3
                               max-w-7xl mx-auto
                               bg-red-50
                               border border-red-200
                               rounded-xl px-3.5 py-3"
                    >
                        <x-lucide-circle-alert class="w-4 h-4 text-red-500 mt-0.5 shrink-0" />

                        <p class="text-[10px] sm:text-xs font-medium text-red-600 flex-1">
                            {{ session('error') }}
                        </p>

                        <button
                            type="button"
                            data-admin-alert-close
                            class="text-red-400 hover:text-red-600"
                            aria-label="Dismiss"
                        >
                            <x-lucide-x class="w-3.5 h-3.5" />
                        </button>
                    </div>
                @endif


                @if ($errors->any())
                    <div
                        data-admin-alert
                        class="flex items-start gap-3
                               max-w-7xl mx-auto
                               bg-red-50
                               border border-red-200
                               rounded-xl px-3.5 py-3"
                    >
                        <x-lucide-triangle-alert class="w-4 h-4 text-red-500 mt-0.5 shrink-0" />

                        <div class="flex-1">

                            <p class="text-[10px] sm:text-xs font-semibold text-red-600">
                                Please review the following:
                            </p>

                            <ul class="mt-1 text-[9px] sm:text-[10px] text-red-500 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>

                        </div>


                        <button
                            type="button"
                            data-admin-alert-close
                            class="text-red-400 hover:text-red-600"
                            aria-label="Dismiss"
                        >
                            <x-lucide-x class="w-3.5 h-3.5" />
                        </button>
                    </div>
                @endif

            </div>
        @endif


        {{-- =====================================================
            PAGE CONTENT
        ===================================================== --}}
        <main
            class="content-scrollbar
                   flex-1 overflow-y-auto
                   px-3 py-4
                   sm:px-4 sm:py-5
                   lg:px-5 lg:py-5"
        >
            <div class="max-w-[1600px] mx-auto">
                @yield('content')
            </div>
        </main>

    </div>


    {{-- =========================================================
        COMMAND / SEARCH PALETTE
    ========================================================= --}}
    <div
        id="sellerSearchPalette"
        hidden
        class="fixed inset-0 z-[90]
               flex items-start justify-center
               px-3 pt-[10vh] sm:pt-[14vh]"
        aria-hidden="true"
    >

        <button
            id="sellerSearchBackdrop"
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
            aria-labelledby="sellerSearchTitle"
        >

            <div class="flex items-center gap-3 px-4 border-b border-gray-border">

                <x-lucide-search class="w-4 h-4 text-teal-dark shrink-0" />

                <input
                    id="sellerCommandInput"
                    type="text"
                    autocomplete="off"
                    placeholder="Search inventory, orders, reports..."
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
                id="sellerCommandResults"
                class="max-h-[360px] overflow-y-auto p-2"
            >

                <p
                    id="sellerSearchTitle"
                    class="px-2 py-2
                           text-[8px] font-bold uppercase tracking-[0.14em]
                           text-navy/30"
                >
                    Seller Navigation
                </p>


                @foreach ($sellerNavigation as $section)
                    @foreach ($section['items'] as $item)

                        <a
                            href="{{ route($item['route']) }}"
                            data-admin-command-item
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


                            <x-lucide-corner-down-left
                                class="w-3.5 h-3.5 text-navy/20
                                       group-hover:text-teal-dark"
                            />
                        </a>

                    @endforeach
                @endforeach


                <div
                    id="sellerCommandEmpty"
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
                        No seller tool found
                    </p>

                    <p class="text-[9px] text-navy/30 mt-1">
                        Try another keyword.
                    </p>
                </div>

            </div>

        </div>
    </div>

</div>


{{-- =========================================================
    SELLER SHELL JAVASCRIPT
    Same behavior contract as admin.layout's script, IDs
    renamed to the seller* namespace.
========================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const shell = document.getElementById('sellerShell');

    if (!shell) {
        return;
    }


    /* =====================================================
       SIDEBAR PREFERENCES
    ===================================================== */
    const sidebarStorageKey = 'shophop_seller_sidebar_collapsed';

    const desktopSidebarToggle =
        document.getElementById('desktopSidebarToggle');

    const desktopSidebarEdgeExpand =
        document.getElementById('desktopSidebarEdgeExpand');

    const sellerBrandLink =
        document.getElementById('sellerBrandLink');

    const mobileSidebarToggle =
        document.getElementById('mobileSidebarToggle');

    const mobileOverlay =
        document.getElementById('sellerMobileOverlay');


    function setSidebarCollapsed(collapsed) {

        const shouldCollapse =
            window.innerWidth >= 1024 && collapsed;

        shell.setAttribute(
            'data-sidebar-collapsed',
            shouldCollapse ? 'true' : 'false'
        );

        localStorage.setItem(
            sidebarStorageKey,
            shouldCollapse ? '1' : '0'
        );

        if (sellerBrandLink) {

            sellerBrandLink.setAttribute(
                'title',
                shouldCollapse
                    ? 'Expand sidebar'
                    : 'Go to Seller Dashboard'
            );

            sellerBrandLink.setAttribute(
                'aria-label',
                shouldCollapse
                    ? 'Expand ShopHop Seller sidebar'
                    : 'ShopHop Seller Dashboard'
            );
        }

    }


    function openMobileSidebar() {

        shell.setAttribute(
            'data-mobile-sidebar-open',
            'true'
        );

        if (mobileOverlay) {
            mobileOverlay.hidden = false;
        }

        document.body.style.overflow = 'hidden';

    }


    function closeMobileSidebar() {

        shell.setAttribute(
            'data-mobile-sidebar-open',
            'false'
        );

        if (mobileOverlay) {
            mobileOverlay.hidden = true;
        }

        document.body.style.overflow = '';

    }


    const initialCollapsed =
        localStorage.getItem(sidebarStorageKey) === '1';

    setSidebarCollapsed(initialCollapsed);


    desktopSidebarToggle?.addEventListener(
        'click',
        function () {
            setSidebarCollapsed(true);
        }
    );


    desktopSidebarEdgeExpand?.addEventListener(
        'click',
        function (event) {

            event.preventDefault();
            event.stopPropagation();

            setSidebarCollapsed(false);

        }
    );


    sellerBrandLink?.addEventListener(
        'click',
        function (event) {

            const isDesktop =
                window.innerWidth >= 1024;

            const isCollapsed =
                shell.getAttribute(
                    'data-sidebar-collapsed'
                ) === 'true';

            if (isDesktop && isCollapsed) {

                event.preventDefault();

                setSidebarCollapsed(false);

            }

        }
    );


    mobileSidebarToggle?.addEventListener(
        'click',
        openMobileSidebar
    );


    mobileOverlay?.addEventListener(
        'click',
        closeMobileSidebar
    );


    /* =====================================================
       NOTIFICATION / PROFILE MENUS
    ===================================================== */
    const notificationToggle =
        document.getElementById('sellerNotificationToggle');

    const notificationMenu =
        document.getElementById('sellerNotificationMenu');

    const profileToggle =
        document.getElementById('sellerProfileToggle');

    const profileMenu =
        document.getElementById('sellerProfileMenu');


    function closeNotificationMenu() {

        if (!notificationMenu || notificationMenu.hidden) {
            return;
        }

        notificationMenu.hidden = true;

        notificationToggle?.setAttribute('aria-expanded', 'false');

    }


    function closeProfileMenu() {

        if (!profileMenu || profileMenu.hidden) {
            return;
        }

        profileMenu.hidden = true;

        profileToggle?.setAttribute('aria-expanded', 'false');

    }


    notificationToggle?.addEventListener(
        'click',
        function (event) {

            event.stopPropagation();

            closeProfileMenu();

            notificationMenu.hidden =
                !notificationMenu.hidden;

            notificationToggle.setAttribute(
                'aria-expanded',
                notificationMenu.hidden ? 'false' : 'true'
            );

        }
    );


    profileToggle?.addEventListener(
        'click',
        function (event) {

            event.stopPropagation();

            closeNotificationMenu();

            profileMenu.hidden =
                !profileMenu.hidden;

            profileToggle.setAttribute(
                'aria-expanded',
                profileMenu.hidden ? 'false' : 'true'
            );

        }
    );


    document.addEventListener('click', function (event) {

        if (
            notificationMenu &&
            !notificationMenu.hidden &&
            !notificationMenu.contains(event.target) &&
            !notificationToggle?.contains(event.target)
        ) {
            closeNotificationMenu();
        }

        if (
            profileMenu &&
            !profileMenu.hidden &&
            !profileMenu.contains(event.target) &&
            !profileToggle?.contains(event.target)
        ) {
            closeProfileMenu();
        }

    });


    /* =====================================================
       COMMAND PALETTE
    ===================================================== */
    const searchTrigger =
        document.getElementById('sellerSearchTrigger');

    const searchPalette =
        document.getElementById('sellerSearchPalette');

    const searchBackdrop =
        document.getElementById('sellerSearchBackdrop');

    const commandInput =
        document.getElementById('sellerCommandInput');

    const commandItems =
        Array.from(
            document.querySelectorAll(
                '[data-admin-command-item]'
            )
        );

    const commandEmpty =
        document.getElementById('sellerCommandEmpty');


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

        closeNotificationMenu();
        closeProfileMenu();

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

            closeNotificationMenu();
            closeProfileMenu();

            if (
                shell.getAttribute(
                    'data-mobile-sidebar-open'
                ) === 'true'
            ) {
                closeMobileSidebar();
            }

        }

    });


    /* =====================================================
       DISMISSIBLE ALERTS
    ===================================================== */
    document
        .querySelectorAll('[data-admin-alert-close]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    button
                        .closest('[data-admin-alert]')
                        ?.remove();

                }
            );

        });


    /* =====================================================
       RESIZE CLEANUP
    ===================================================== */
    window.addEventListener('resize', function () {

        if (window.innerWidth >= 1024) {

            closeMobileSidebar();

            const savedCollapsed =
                localStorage.getItem(
                    sidebarStorageKey
                ) === '1';

            setSidebarCollapsed(savedCollapsed);

        } else {

            shell.setAttribute(
                'data-sidebar-collapsed',
                'false'
            );

        }

    });


    filterCommands();

});
</script>

@stack('scripts')

</body>
</html>