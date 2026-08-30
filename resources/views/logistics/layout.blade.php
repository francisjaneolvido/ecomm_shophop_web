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

        @media (max-width: 1023px) {

            #logisticsSidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;

                width: 17rem;

                z-index: 60;

                transform: translateX(-100%);

                transition: transform .22s ease;
            }

            #logisticsShell[data-mobile-open="true"]
            #logisticsSidebar {
                transform: translateX(0);
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
        class="relative
               w-64
               bg-navy
               text-white
               flex flex-col
               shrink-0
               border-r border-white/5"
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
            class="relative
                   h-[68px]
                   flex items-center
                   px-5
                   border-b border-white/10
                   shrink-0"
        >

            <a
                href="{{ route('logistics.dashboard') }}"
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


                <div class="min-w-0">

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

        </div>


        {{-- =====================================================
            LOGISTICS COMPANY
        ===================================================== --}}
        <div class="px-3 pt-4">

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
                        class="px-3 mb-1.5
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


                            <a
                                href="{{ route($item['route']) }}"
                                class="relative
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
                                                    : 'text-white/45'
                                           }}"
                                >

                                    <x-dynamic-component
                                        :component="'lucide-' . $item['icon']"
                                        class="w-4 h-4"
                                    />

                                </span>


                                <span class="flex-1 truncate">
                                    {{ $item['label'] }}
                                </span>

                            </a>

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


                <div class="min-w-0 flex-1">

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
                    class="w-full
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
                               flex items-center justify-center"
                    >
                        <x-lucide-log-out class="w-4 h-4" />
                    </span>

                    Log Out

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
                       transition"
            >
                <x-lucide-menu class="w-4 h-4" />
            </button>


            <div class="ml-3 lg:ml-0">

                <p
                    class="text-xs
                           font-semibold
                           text-navy"
                >
                    @yield('page-title', 'Dashboard')
                </p>

                <p
                    class="hidden sm:block
                           text-[9px]
                           text-navy/35
                           mt-0.5"
                >
                    ShopHop Logistics Partner Console
                </p>

            </div>


            <div
                class="ml-auto
                       flex items-center
                       gap-2"
            >

                {{-- Website --}}
                <a
                    href="{{ route('home') }}"
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
    MOBILE SIDEBAR JS
========================================================= --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const shell =
        document.getElementById('logisticsShell');

    const toggle =
        document.getElementById('logisticsMobileToggle');

    const overlay =
        document.getElementById('logisticsMobileOverlay');


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


    document
        .querySelectorAll('#logisticsSidebar a')
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


    window.addEventListener(
        'resize',
        function () {

            if (window.innerWidth >= 1024) {
                closeSidebar();
            }

        }
    );

});

</script>


@stack('scripts')

</body>

</html>