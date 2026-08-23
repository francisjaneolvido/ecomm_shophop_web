{{-- resources/views/logistics/partials/sidebar.blade.php --}}

{{-- Mobile dim overlay, lalabas lang pag bukas yung sidebar sa mobile --}}
<div data-sidebar-overlay class="fixed inset-0 bg-navy/50 z-40 hidden"></div>

<aside data-sidebar
       class="fixed inset-y-0 left-0 z-50 w-64 bg-navy text-white flex flex-col">

    {{-- Wordmark --}}
    <div class="h-16 sm:h-18 flex items-center justify-between px-5 border-b border-white/10 shrink-0">
        <a href="{{ route('logistics.dashboard') }}" class="flex items-baseline gap-2 min-w-0">
            <span class="text-lg font-extrabold tracking-tight text-white shrink-0">
                Shop<span class="text-teal">Hop</span>
            </span>
            <span class="text-xs sm:text-sm font-bold text-teal uppercase tracking-wide truncate">
                Logistics
            </span>
        </a>
        <button type="button" data-sidebar-close aria-label="Close menu"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition shrink-0">
            <x-lucide-x class="w-4 h-4" />
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-1 text-sm font-semibold">
        <a href="{{ route('logistics.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('logistics.dashboard') ? 'bg-teal text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
            <x-lucide-layout-dashboard class="w-4.5 h-4.5 shrink-0" />
            Dashboard
        </a>

        <a href="{{ route('logistics.riders.index') }}"
           class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('logistics.riders.*') ? 'bg-teal text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
            <span class="flex items-center gap-3">
                <x-lucide-bike class="w-4.5 h-4.5 shrink-0" />
                Riders
            </span>
            @isset($pendingApplications)
                @if (count($pendingApplications))
                    <span class="text-[10px] font-bold bg-red-500 text-white px-1.5 py-0.5 rounded-full leading-none">
                        {{ count($pendingApplications) }}
                    </span>
                @endif
            @endisset
        </a>

        <a href="{{ route('logistics.deliveries.board') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('logistics.deliveries.*') ? 'bg-teal text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
            <x-lucide-truck class="w-4.5 h-4.5 shrink-0" />
            Deliveries
        </a>

        <a href="{{ route('logistics.reports.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('logistics.reports.*') ? 'bg-teal text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
            <x-lucide-bar-chart-3 class="w-4.5 h-4.5 shrink-0" />
            Reports
        </a>

        <a href="#"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition text-white/60 hover:text-white hover:bg-white/10">
            <x-lucide-message-circle class="w-4.5 h-4.5 shrink-0" />
            Messages
        </a>

        <div class="pt-4 mt-4 border-t border-white/10">
            <a href="{{ url('/') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-white/45 hover:text-white hover:bg-white/10 transition text-xs font-semibold">
                <x-lucide-arrow-left class="w-4 h-4 shrink-0" />
                Back to ShopHop
            </a>
        </div>
    </nav>

    {{-- Partner profile chip --}}
    <div class="px-4 py-4 border-t border-white/10 shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-teal text-white flex items-center justify-center text-xs font-bold shrink-0">
                J&amp;T
            </div>
            <div class="leading-tight min-w-0">
                <p class="text-xs font-semibold text-white truncate">J&amp;T Express</p>
                <p class="text-[11px] text-white/45 truncate">Cavite Hub</p>
            </div>
        </div>
    </div>
</aside>