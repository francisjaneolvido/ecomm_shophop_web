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
</head>
<body class="bg-gray-bg text-navy antialiased">

{{-- =========================================================
    PORTAL HEADER
========================================================= --}}
<header class="sticky top-0 z-40 bg-white border-b border-gray-border">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-18">

            {{-- Wordmark --}}
            <a href="{{ route('logistics.dashboard') }}" class="flex items-baseline gap-2 shrink-0">
                <span class="text-lg sm:text-xl font-extrabold tracking-tight text-navy">
                    Shop<span class="text-teal">Hop</span>
                </span>
                <span class="text-[11px] sm:text-xs font-semibold text-navy/45 uppercase tracking-wide">
                    Logistics
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-7 text-sm font-semibold">
                <a href="{{ route('logistics.dashboard') }}"
                   class="{{ request()->routeIs('logistics.dashboard') ? 'text-teal-dark' : 'text-navy/60 hover:text-navy transition' }}">
                    Dashboard
                </a>
                <a href="{{ route('logistics.riders.index') }}"
                   class="{{ request()->routeIs('logistics.riders.*') ? 'text-teal-dark' : 'text-navy/60 hover:text-navy transition' }}">
                    Riders
                </a>
                <a href="{{ route('logistics.deliveries.board') }}"
                   class="{{ request()->routeIs('logistics.deliveries.*') ? 'text-teal-dark' : 'text-navy/60 hover:text-navy transition' }}">
                    Deliveries
                </a>
                <a href="{{ route('logistics.reports.index') }}"
                   class="{{ request()->routeIs('logistics.reports.*') ? 'text-teal-dark' : 'text-navy/60 hover:text-navy transition' }}">
                    Reports
                </a>
                <a href="#" class="text-navy/60 hover:text-navy transition">Messages</a>
            </nav>

            {{-- Right cluster --}}
            <div class="flex items-center gap-3 sm:gap-4">
                <button type="button" aria-label="Notifications"
                        class="relative w-9 h-9 rounded-full flex items-center justify-center text-navy/60 hover:text-navy hover:bg-gray-bg transition">
                    <x-lucide-bell class="w-5 h-5" />
                    {{-- TODO: move this into a view composer shared by every logistics.* view
                         instead of relying on $pendingApplications being passed per-page. --}}
                    @isset($pendingApplications)
                        @if (count($pendingApplications))
                            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500"></span>
                        @endif
                    @endisset
                </button>

                <div class="hidden sm:flex items-center gap-2 pl-3 sm:pl-4 border-l border-gray-border">
                    <div class="w-9 h-9 rounded-full bg-navy text-white flex items-center justify-center text-xs font-bold shrink-0">
                        J&amp;T
                    </div>
                    <div class="leading-tight">
                        <p class="text-xs font-semibold text-navy">J&amp;T Express</p>
                        <p class="text-[11px] text-navy/50">Cavite Hub</p>
                    </div>
                </div>

                <button type="button" data-mobile-menu-toggle aria-label="Open menu"
                        class="md:hidden w-9 h-9 rounded-full flex items-center justify-center text-navy hover:bg-gray-bg transition">
                    <x-lucide-menu class="w-5 h-5" />
                </button>
            </div>
        </div>

        {{-- Mobile nav --}}
        <nav data-mobile-menu class="hidden flex-col gap-1 pb-4 text-sm font-semibold md:hidden">
            <a href="{{ route('logistics.dashboard') }}" class="px-2 py-2 rounded-lg {{ request()->routeIs('logistics.dashboard') ? 'bg-teal-light text-teal-dark' : 'text-navy/70' }}">Dashboard</a>
            <a href="{{ route('logistics.riders.index') }}" class="px-2 py-2 rounded-lg {{ request()->routeIs('logistics.riders.*') ? 'bg-teal-light text-teal-dark' : 'text-navy/70' }}">Riders</a>
            <a href="{{ route('logistics.deliveries.board') }}" class="px-2 py-2 rounded-lg {{ request()->routeIs('logistics.deliveries.*') ? 'bg-teal-light text-teal-dark' : 'text-navy/70' }}">Deliveries</a>
            <a href="{{ route('logistics.reports.index') }}" class="px-2 py-2 rounded-lg {{ request()->routeIs('logistics.reports.*') ? 'bg-teal-light text-teal-dark' : 'text-navy/70' }}">Reports</a>
            <a href="#" class="px-2 py-2 rounded-lg text-navy/70">Messages</a>
        </nav>
    </div>
</header>

{{-- =========================================================
    FLASH MESSAGE
========================================================= --}}
@if (session('status'))
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="flex items-center gap-3 bg-teal-light text-teal-dark text-sm font-medium px-4 py-3 rounded-xl">
            <x-lucide-check-circle-2 class="w-4 h-4 shrink-0" />
            {{ session('status') }}
        </div>
    </div>
@endif

{{-- =========================================================
    PAGE CONTENT
========================================================= --}}
<main class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    @yield('content')
</main>

<footer class="border-t border-gray-border py-8">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-navy/50">
        <p>&copy; {{ date('Y') }} ShopHop Logistics · Partner Console</p>
        <a href="{{ url('/') }}" class="font-semibold text-navy/60 hover:text-navy transition">Back to ShopHop</a>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('[data-mobile-menu-toggle]');
        const menu = document.querySelector('[data-mobile-menu]');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', function () {
            menu.classList.toggle('hidden');
            menu.classList.toggle('flex');
        });
    });
</script>

@stack('scripts')
</body>
</html>
