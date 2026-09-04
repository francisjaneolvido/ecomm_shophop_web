@extends('seller.partials.layout')

@section('title', 'Seller Dashboard')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    | Same convention as admin.dashboard — controller can pass real
    | data later without breaking this view.
    */
    $newOrders = $newOrders ?? 0;
    $ordersToPrepare = $ordersToPrepare ?? 0;
    $lowStockCount = $lowStockCount ?? 0;
    $revenueThisMonth = $revenueThisMonth ?? 0;
    $revenueChangePct = $revenueChangePct ?? 0;

    $recentOrders = collect($recentOrders ?? []);
    $lowStockProducts = collect($lowStockProducts ?? []);
    $weeklySales = collect($weeklySales ?? []);
    $topProducts = collect($topProducts ?? []);

    $authSeller = auth()->user();

    $sellerFirstName = $sellerFirstName
        ?? $authSeller?->name
        ?? ($authSeller?->email ? explode('@', $authSeller->email)[0] : null)
        ?? 'Seller';

    $needsAttention = $newOrders + $lowStockCount;

    $hour = now()->hour;

    $greeting = $hour < 12
        ? 'Good morning'
        : ($hour < 18 ? 'Good afternoon' : 'Good evening');

    /*
    |--------------------------------------------------------------------------
    | ORDER STATUS BREAKDOWN
    |--------------------------------------------------------------------------
    | Mirrors the ERP status list (PLACED → COMPLETED) collapsed into
    | the stages a seller actually acts on.
    */
    $statusConfig = [
        'to_confirm' => [
            'label' => 'To Confirm',
            'classes' => 'bg-coral/10 text-coral',
            'bar' => 'bg-coral',
        ],
        'preparing' => [
            'label' => 'Preparing',
            'classes' => 'bg-yellow/15 text-amber-700',
            'bar' => 'bg-yellow',
        ],
        'ready_for_pickup' => [
            'label' => 'Ready for Pickup',
            'classes' => 'bg-sky/10 text-sky',
            'bar' => 'bg-sky',
        ],
        'in_transit' => [
            'label' => 'In Transit',
            'classes' => 'bg-navy/10 text-navy',
            'bar' => 'bg-navy',
        ],
        'completed' => [
            'label' => 'Completed',
            'classes' => 'bg-teal/10 text-teal-dark',
            'bar' => 'bg-teal',
        ],
    ];

    $orderStatusMix = $orderStatusMix ?? [
        'to_confirm' => 0,
        'preparing' => 0,
        'ready_for_pickup' => 0,
        'in_transit' => 0,
        'completed' => 0,
    ];

    $orderStatusTotal = max(1, array_sum($orderStatusMix));

    $orderRowStatusClasses = [
        'PLACED' => 'bg-coral/10 text-coral border-coral/20',
        'CONFIRMED' => 'bg-sky/10 text-sky border-sky/20',
        'PREPARING' => 'bg-yellow/15 text-amber-700 border-yellow/30',
        'READY_FOR_PICKUP' => 'bg-sky/10 text-sky border-sky/20',
        'PICKED_UP' => 'bg-navy/10 text-navy border-navy/15',
        'OUT_FOR_DELIVERY' => 'bg-navy/10 text-navy border-navy/15',
        'DELIVERED' => 'bg-teal/10 text-teal-dark border-teal/20',
        'COMPLETED' => 'bg-teal/10 text-teal-dark border-teal/20',
        'DELIVERY_FAILED' => 'bg-red-50 text-red-600 border-red-200',
        'RETURNED' => 'bg-red-50 text-red-600 border-red-200',
    ];

    $weeklySalesMax = max(1, ...($weeklySales->pluck('amount')->all() ?: [1]));
@endphp


<style>
    #sellerDashboard .dash-gap {
        gap: 1rem;
    }

    #sellerDashboard .dash-section {
        margin-bottom: 1.25rem;
    }
</style>


<div id="sellerDashboard">

    {{-- =========================================================
        HEADER
    ========================================================= --}}
    <header class="dash-section flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

        <div class="min-w-0">

            <div class="flex items-center gap-2 mb-2">

                <span class="relative flex w-2 h-2">
                    <span class="animate-ping absolute inline-flex w-full h-full rounded-full bg-teal opacity-60"></span>
                    <span class="relative inline-flex w-2 h-2 rounded-full bg-teal"></span>
                </span>

                <p class="text-[10px] sm:text-xs font-semibold tracking-[0.16em] uppercase text-teal-dark">
                    ShopHop Seller
                </p>
            </div>


            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">

                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    {{ $greeting }}, {{ $sellerFirstName }}
                </h1>

                @if ($needsAttention === 0)
                    <span
                        class="inline-flex items-center gap-1
                               px-2 py-1 rounded-full
                               bg-teal/10 text-teal-dark
                               text-[9px] font-semibold"
                    >
                        <x-lucide-circle-check class="w-3 h-3" />
                        All caught up
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1
                               px-2 py-1 rounded-full
                               bg-coral/10 text-coral
                               text-[9px] font-semibold"
                    >
                        <x-lucide-circle-alert class="w-3 h-3" />
                        {{ $needsAttention }} need{{ $needsAttention === 1 ? 's' : '' }} attention
                    </span>
                @endif

            </div>


            <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                Track your orders, stock levels, and store performance from one workspace.
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


            <a
                href="{{ route('seller.reports') }}"
                class="inline-flex items-center justify-center gap-1.5
                       h-9 px-3.5 rounded-lg
                       border border-gray-border
                       bg-white
                       text-xs font-semibold text-navy
                       hover:border-teal/40 hover:text-teal-dark
                       hover:shadow-sm
                       transition-all"
            >
                <x-lucide-chart-no-axes-combined class="w-3.5 h-3.5" />
                Reports
            </a>


            <a
                href="{{ route('seller.inventory') }}"
                class="inline-flex items-center justify-center gap-1.5
                       h-9 px-3.5 rounded-lg
                       bg-navy hover:bg-navy/90
                       text-xs font-semibold text-white
                       transition-colors"
            >
                <x-lucide-plus class="w-3.5 h-3.5" />
                Add Product
            </a>

        </div>

    </header>


    {{-- =========================================================
        KPI CARDS
    ========================================================= --}}
    <section class="dash-section">
        <div class="grid grid-cols-2 xl:grid-cols-4 dash-gap">

            {{-- New Orders --}}
            <a
                href="{{ route('seller.orders.notifications') }}"
                class="group relative overflow-hidden
                       bg-white border border-gray-border
                       rounded-xl p-4
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
                        <x-lucide-bell-ring class="w-4 h-4" />
                    </div>

                    @if ($newOrders > 0)
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
                        {{ number_format($newOrders) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        New Orders
                    </p>

                </div>

                <div class="flex items-center mt-2 text-[9px] sm:text-[10px] text-navy/35">
                    <span>Awaiting confirmation</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-teal-dark
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>


            {{-- To Prepare --}}
            <a
                href="{{ route('seller.orders.prepare') }}"
                class="group relative overflow-hidden
                       bg-white border border-gray-border
                       rounded-xl p-4
                       hover:border-coral/35
                       hover:shadow-lg hover:shadow-coral/5
                       hover:-translate-y-0.5
                       transition-all duration-200"
            >
                <span
                    class="absolute inset-x-4 top-0 h-0.5 rounded-full
                           bg-coral
                           scale-x-0 group-hover:scale-x-100
                           transition-transform origin-left"
                ></span>

                <div class="flex items-start justify-between gap-3">

                    <div
                        class="w-9 h-9 rounded-lg
                               bg-coral/10 text-coral
                               flex items-center justify-center
                               group-hover:bg-coral group-hover:text-white
                               transition-colors"
                    >
                        <x-lucide-box class="w-4 h-4" />
                    </div>

                    <span
                        class="text-[9px] font-semibold
                               text-navy/40 bg-gray-bg
                               px-2 py-1 rounded-full"
                    >
                        Packing
                    </span>

                </div>

                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums">
                        {{ number_format($ordersToPrepare) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Orders to Prepare
                    </p>

                </div>

                <div class="flex items-center mt-2 text-[9px] sm:text-[10px] text-navy/35">
                    <span>Pack &amp; print waybill</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-coral
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>


            {{-- Low Stock --}}
            <a
                href="{{ route('seller.inventory') }}"
                class="group relative overflow-hidden
                       bg-white border border-gray-border
                       rounded-xl p-4
                       hover:border-yellow/50
                       hover:shadow-lg hover:shadow-yellow/10
                       hover:-translate-y-0.5
                       transition-all duration-200"
            >
                <span
                    class="absolute inset-x-4 top-0 h-0.5 rounded-full
                           bg-yellow
                           scale-x-0 group-hover:scale-x-100
                           transition-transform origin-left"
                ></span>

                <div class="flex items-start justify-between gap-3">

                    <div
                        class="w-9 h-9 rounded-lg
                               bg-yellow/15 text-amber-700
                               flex items-center justify-center
                               group-hover:bg-yellow group-hover:text-white
                               transition-colors"
                    >
                        <x-lucide-triangle-alert class="w-4 h-4" />
                    </div>

                    @if ($lowStockCount > 0)
                        <span
                            class="inline-flex items-center gap-1
                                   text-[9px] font-semibold
                                   text-amber-700 bg-yellow/15
                                   px-2 py-1 rounded-full"
                        >
                            <span class="w-1 h-1 rounded-full bg-yellow"></span>
                            Restock
                        </span>
                    @endif

                </div>

                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums">
                        {{ number_format($lowStockCount) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Low Stock Items
                    </p>

                </div>

                <div class="flex items-center mt-2 text-[9px] sm:text-[10px] text-navy/35">
                    <span>Below reorder point</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-amber-700
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>


            {{-- Revenue This Month --}}
            <a
                href="{{ route('seller.reports') }}"
                class="group relative overflow-hidden
                       bg-white border border-gray-border
                       rounded-xl p-4
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
                        <x-lucide-wallet class="w-4 h-4" />
                    </div>

                    @if ($revenueChangePct != 0)
                        <span
                            class="inline-flex items-center gap-1
                                   text-[9px] font-semibold
                                   px-2 py-1 rounded-full
                                   {{ $revenueChangePct > 0 ? 'text-teal-dark bg-teal/10' : 'text-red-600 bg-red-50' }}"
                        >
                            <x-dynamic-component
                                :component="$revenueChangePct > 0 ? 'lucide-trending-up' : 'lucide-trending-down'"
                                class="w-3 h-3"
                            />
                            {{ abs($revenueChangePct) }}%
                        </span>
                    @endif

                </div>

                <div class="mt-3">

                    <p class="text-xl sm:text-2xl font-bold text-navy tabular-nums">
                        ₱{{ number_format($revenueThisMonth, 2) }}
                    </p>

                    <p class="text-[10px] sm:text-xs font-medium text-navy/55 mt-0.5">
                        Revenue This Month
                    </p>

                </div>

                <div class="flex items-center mt-2 text-[9px] sm:text-[10px] text-navy/35">
                    <span>vs. last month</span>

                    <x-lucide-arrow-up-right
                        class="w-3 h-3 ml-auto
                               group-hover:text-sky
                               group-hover:translate-x-0.5
                               group-hover:-translate-y-0.5
                               transition-transform"
                    />
                </div>
            </a>

        </div>
    </section>


    {{-- =========================================================
        MAIN GRID — Recent Orders + Side Panels
    ========================================================= --}}
    <section
        class="dash-section
               grid grid-cols-1
               xl:grid-cols-[minmax(0,1.72fr)_minmax(290px,0.8fr)]
               dash-gap"
    >

        {{-- ============================
            RECENT ORDERS
        ============================ --}}
        <div class="bg-white border border-gray-border rounded-xl overflow-hidden">

            <div class="flex items-center justify-between px-4 py-3.5 border-b border-gray-border">

                <div>
                    <p class="text-sm font-bold text-navy">
                        Recent Orders
                    </p>
                    <p class="text-[9px] text-navy/35 mt-0.5">
                        Latest activity across your store
                    </p>
                </div>

                <a
                    href="{{ route('seller.orders.notifications') }}"
                    class="inline-flex items-center gap-1
                           text-[10px] font-semibold text-teal-dark
                           hover:text-teal-dark/70
                           transition"
                >
                    View all
                    <x-lucide-arrow-right class="w-3 h-3" />
                </a>

            </div>


            @if ($recentOrders->isEmpty())

                <div class="px-4 py-14 text-center">

                    <div
                        class="w-11 h-11 mx-auto
                               rounded-xl bg-gray-bg text-navy/30
                               flex items-center justify-center"
                    >
                        <x-lucide-package-search class="w-5 h-5" />
                    </div>

                    <p class="text-xs font-semibold text-navy/60 mt-3">
                        No orders yet
                    </p>

                    <p class="text-[10px] text-navy/35 mt-1">
                        New orders from buyers will show up here.
                    </p>

                </div>

            @else

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-border">
                                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-navy/35">Order</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-navy/35">Buyer</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-navy/35">Items</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-navy/35">Total</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-navy/35">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr class="border-b border-gray-border last:border-0 hover:bg-gray-bg/60 transition-colors">

                                    <td class="px-4 py-3">
                                        <p class="text-[11px] font-semibold text-navy">
                                            #{{ $order['id'] ?? '—' }}
                                        </p>
                                        <p class="text-[9px] text-navy/35 mt-0.5">
                                            {{ $order['placed_at'] ?? '' }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-3 text-[11px] text-navy/70">
                                        {{ $order['buyer_name'] ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-[11px] text-navy/70">
                                        {{ $order['item_count'] ?? 0 }} item{{ ($order['item_count'] ?? 0) === 1 ? '' : 's' }}
                                    </td>

                                    <td class="px-4 py-3 text-[11px] font-semibold text-navy tabular-nums">
                                        ₱{{ number_format($order['total'] ?? 0, 2) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @php
                                            $status = $order['status'] ?? 'PLACED';
                                            $statusClass = $orderRowStatusClasses[$status] ?? 'bg-gray-bg text-navy/50 border-gray-border';
                                        @endphp

                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full
                                                   border text-[9px] font-semibold
                                                   {{ $statusClass }}"
                                        >
                                            {{ ucwords(strtolower(str_replace('_', ' ', $status))) }}
                                        </span>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif

        </div>


        {{-- ============================
            SIDE PANELS
        ============================ --}}
        <div class="flex flex-col dash-gap">

            {{-- Order Status Breakdown --}}
            <div class="bg-white border border-gray-border rounded-xl p-4">

                <p class="text-sm font-bold text-navy">
                    Order Status
                </p>
                <p class="text-[9px] text-navy/35 mt-0.5 mb-4">
                    Where your active orders stand
                </p>

                <div class="space-y-3">
                    @foreach ($statusConfig as $key => $config)
                        @php
                            $count = $orderStatusMix[$key] ?? 0;
                            $pct = round(($count / $orderStatusTotal) * 100);
                        @endphp

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-medium text-navy/60">
                                    {{ $config['label'] }}
                                </span>
                                <span class="text-[10px] font-semibold text-navy tabular-nums">
                                    {{ $count }}
                                </span>
                            </div>

                            <div class="h-1.5 rounded-full bg-gray-bg overflow-hidden">
                                <div
                                    class="h-full rounded-full {{ $config['bar'] }}"
                                    style="width: {{ $pct }}%"
                                ></div>
                            </div>
                        </div>

                    @endforeach
                </div>

            </div>


            {{-- Low Stock Alerts --}}
            <div class="bg-white border border-gray-border rounded-xl p-4 flex-1">

                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm font-bold text-navy">
                            Low Stock Alerts
                        </p>
                        <p class="text-[9px] text-navy/35 mt-0.5">
                            Restock before you run out
                        </p>
                    </div>

                    <a
                        href="{{ route('seller.inventory') }}"
                        class="text-[10px] font-semibold text-teal-dark hover:text-teal-dark/70 transition"
                    >
                        Manage
                    </a>
                </div>

                @if ($lowStockProducts->isEmpty())

                    <div class="py-6 text-center">
                        <div
                            class="w-9 h-9 mx-auto
                                   rounded-lg bg-teal-light text-teal-dark
                                   flex items-center justify-center"
                        >
                            <x-lucide-circle-check class="w-4 h-4" />
                        </div>
                        <p class="text-[10px] font-semibold text-navy/50 mt-2">
                            Stock levels look healthy
                        </p>
                    </div>

                @else

                    <div class="space-y-2">
                        @foreach ($lowStockProducts as $product)
                            <div class="flex items-center gap-2.5 py-1.5">

                                <div
                                    class="w-8 h-8 rounded-lg
                                           bg-yellow/15 text-amber-700
                                           flex items-center justify-center shrink-0"
                                >
                                    <x-lucide-package class="w-3.5 h-3.5" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-semibold text-navy truncate">
                                        {{ $product['name'] ?? 'Product' }}
                                    </p>
                                    <p class="text-[9px] text-navy/35 mt-0.5">
                                        {{ $product['stock'] ?? 0 }} left
                                    </p>
                                </div>

                                <span class="text-[9px] font-semibold text-coral bg-coral/10 px-2 py-1 rounded-full shrink-0">
                                    Low
                                </span>

                            </div>
                        @endforeach
                    </div>

                @endif

            </div>

        </div>

    </section>


    {{-- =========================================================
        SALES OVERVIEW + TOP PRODUCTS
    ========================================================= --}}
    <section class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.72fr)_minmax(290px,0.8fr)] dash-gap">

        {{-- Weekly Sales --}}
        <div class="bg-white border border-gray-border rounded-xl p-4">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-bold text-navy">
                        Sales This Week
                    </p>
                    <p class="text-[9px] text-navy/35 mt-0.5">
                        Daily revenue, last 7 days
                    </p>
                </div>

                <span class="text-[9px] font-semibold text-navy/40 bg-gray-bg px-2 py-1 rounded-full">
                    ₱ PHP
                </span>
            </div>

            @if ($weeklySales->isEmpty())

                <div class="py-10 text-center">
                    <p class="text-[10px] text-navy/35">
                        No sales recorded yet this week.
                    </p>
                </div>

            @else

                <div class="flex items-end justify-between gap-2 h-40">
                    @foreach ($weeklySales as $day)
                        @php
                            $barPct = max(4, round((($day['amount'] ?? 0) / $weeklySalesMax) * 100));
                        @endphp

                        <div class="flex-1 flex flex-col items-center justify-end h-full gap-2">

                            <div class="w-full flex-1 flex items-end">
                                <div
                                    class="w-full rounded-t-md bg-teal/15 hover:bg-teal/25 transition-colors relative group"
                                    style="height: {{ $barPct }}%"
                                >
                                    <span
                                        class="absolute -top-6 left-1/2 -translate-x-1/2
                                               text-[9px] font-semibold text-navy
                                               opacity-0 group-hover:opacity-100 transition
                                               whitespace-nowrap"
                                    >
                                        ₱{{ number_format($day['amount'] ?? 0) }}
                                    </span>
                                    <div class="w-full h-1 rounded-t-md bg-teal"></div>
                                </div>
                            </div>

                            <span class="text-[9px] font-medium text-navy/40">
                                {{ $day['label'] ?? '' }}
                            </span>

                        </div>
                    @endforeach
                </div>

            @endif

        </div>


        {{-- Top Products --}}
        <div class="bg-white border border-gray-border rounded-xl p-4">

            <p class="text-sm font-bold text-navy">
                Top Products
            </p>
            <p class="text-[9px] text-navy/35 mt-0.5 mb-3">
                Best sellers this month
            </p>

            @if ($topProducts->isEmpty())

                <div class="py-8 text-center">
                    <p class="text-[10px] text-navy/35">
                        No product sales yet.
                    </p>
                </div>

            @else

                <div class="space-y-3">
                    @foreach ($topProducts as $index => $product)
                        <div class="flex items-center gap-3">

                            <span class="text-[10px] font-bold text-navy/25 w-4 shrink-0">
                                {{ $index + 1 }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-semibold text-navy truncate">
                                    {{ $product['name'] ?? 'Product' }}
                                </p>
                                <p class="text-[9px] text-navy/35 mt-0.5">
                                    {{ $product['sold'] ?? 0 }} sold
                                </p>
                            </div>

                            <span class="text-[10px] font-semibold text-navy tabular-nums shrink-0">
                                ₱{{ number_format($product['revenue'] ?? 0) }}
                            </span>

                        </div>
                    @endforeach
                </div>

            @endif

        </div>

    </section>

</div>

@endsection