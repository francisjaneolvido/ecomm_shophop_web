<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ShopHop Logistics — Partner Console')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    {{-- Adjust to match how the main app loads its build assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    {{-- Sidebar collapse/hide behaviour. Plain CSS (not JIT-generated Tailwind classes)
         so the show/hide toggle works reliably regardless of what gets purged at build time. --}}
    <style>
        [data-sidebar] {
            transform: translateX(-100%);
            transition: transform .2s ease-in-out;
        }
        [data-sidebar].is-open { transform: translateX(0); }

        @media (min-width: 768px) {
            [data-sidebar] { transform: translateX(0); }
            [data-sidebar].is-closed { transform: translateX(-100%); }
        }

        [data-sidebar-wrapper] { transition: padding-left .2s ease-in-out; }

        @media (min-width: 768px) {
            [data-sidebar-wrapper] { padding-left: 16rem; }
            [data-sidebar-wrapper].is-collapsed { padding-left: 0; }
        }
    </style>
</head>
<body class="bg-gray-bg text-navy antialiased">

{{-- =========================================================
    SIDEBAR
========================================================= --}}
@include('logistics.partials.sidebar')

<div data-sidebar-wrapper class="flex flex-col min-h-screen">

    {{-- =========================================================
        SLIM TOP BAR — sidebar toggle + notifications
    ========================================================= --}}
    <header class="sticky top-0 z-30 bg-white border-b border-gray-border">
        <div class="flex items-center justify-between h-16 sm:h-18 px-4 sm:px-6 lg:px-8">
            <button type="button" data-sidebar-toggle aria-label="Toggle menu"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-navy hover:bg-gray-bg transition">
                <x-lucide-menu class="w-5 h-5" />
            </button>

            <div class="flex-1"></div>

            <div class="flex items-center gap-3 sm:gap-4">
                <button type="button" aria-label="Notifications"
                        class="relative w-9 h-9 rounded-full flex items-center justify-center text-navy/60 hover:text-navy hover:bg-gray-bg transition">
                    <x-lucide-bell class="w-5 h-5" />
                    @isset($pendingApplications)
                        @if (count($pendingApplications))
                            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500"></span>
                        @endif
                    @endisset
                </button>
            </div>
        </div>
    </header>

    {{-- =========================================================
        FLASH MESSAGE
    ========================================================= --}}
    @if (session('status'))
        <div class="px-4 sm:px-6 lg:px-8 pt-6">
            <div class="flex items-center gap-3 bg-teal-light text-teal-dark text-sm font-medium px-4 py-3 rounded-xl">
                <x-lucide-check-circle-2 class="w-4 h-4 shrink-0" />
                {{ session('status') }}
            </div>
        </div>
    @endif

    {{-- =========================================================
        PAGE CONTENT
    ========================================================= --}}
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 sm:py-10 max-w-310 w-full mx-auto">
        @yield('content')
    </main>

    <footer class="border-t border-gray-border py-6">
        <div class="px-4 sm:px-6 lg:px-8 text-xs text-navy/50">
            &copy; {{ date('Y') }} ShopHop Logistics · Partner Console
        </div>
    </footer>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.querySelector('[data-sidebar]');
        const wrapper = document.querySelector('[data-sidebar-wrapper]');
        const overlay = document.querySelector('[data-sidebar-overlay]');
        const toggleBtn = document.querySelector('[data-sidebar-toggle]');
        const closeBtn = document.querySelector('[data-sidebar-close]');

        // Start "open" on desktop widths, "closed" on mobile — matches the CSS defaults.
        let isOpen = window.innerWidth >= 768;

        function render() {
            sidebar.classList.toggle('is-open', isOpen);
            sidebar.classList.toggle('is-closed', !isOpen);
            wrapper.classList.toggle('is-collapsed', !isOpen);
            const showOverlay = isOpen && window.innerWidth < 768;
            overlay.classList.toggle('hidden', !showOverlay);
        }

        function toggleSidebar() {
            isOpen = !isOpen;
            render();
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        closeBtn?.addEventListener('click', function () { isOpen = false; render(); });
        overlay?.addEventListener('click', function () { isOpen = false; render(); });

        render();
    });
</script>

@stack('scripts')
</body>
</html>