@extends('seller.partials.layout')

@section('title', 'Seller Dashboard')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | SELLER PROFILE / DISPLAY DATA
    |--------------------------------------------------------------------------
    | Prefer controller-provided $sellerProfile.
    | Fallback to the sellers table so business_name and seller name are
    | pulled from the correct model instead of assuming they live on users.
    */
    $authUser = auth()->user();

    $sellerProfile = $sellerProfile
        ?? ($authUser ? \App\Models\Seller::where('user_id', $authUser->id)->first() : null);

    $sellerName = $sellerName
        ?? trim(collect([
            $sellerProfile?->first_name,
            $sellerProfile?->middle_initial,
            $sellerProfile?->last_name,
        ])->filter()->implode(' '))
        ?: ($authUser?->email ? ucfirst(explode('@', $authUser->email)[0]) : 'Seller');

    $sellerBusinessName = $sellerBusinessName
        ?? $sellerProfile?->business_name
        ?? 'My Store';

    $sellerCategory = $sellerCategory
        ?? $sellerProfile?->business_category
        ?? 'Seller';

    $sellerStatus = $sellerStatus
        ?? $authUser?->status
        ?? 'pending';

    $statusLabel = match ($sellerStatus) {
        'approved' => 'Approved Seller',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
        default => 'Pending Approval',
    };

    $statusClass = match ($sellerStatus) {
        'approved' => 'bg-teal/10 text-teal-dark',
        'rejected' => 'bg-red-50 text-red-600',
        'suspended' => 'bg-coral/10 text-coral',
        default => 'bg-yellow/20 text-amber-700',
    };

    /*
    |--------------------------------------------------------------------------
    | SAFE DASHBOARD DEFAULTS
    |--------------------------------------------------------------------------
    | These values can be replaced by controller data as each module lands.
    */
    $newOrders = (int) ($newOrders ?? 0);
    $ordersToPrepare = (int) ($ordersToPrepare ?? 0);
    $readyForPickup = (int) ($readyForPickup ?? 0);
    $pickedUpOrders = (int) ($pickedUpOrders ?? 0);
    $inTransitOrders = (int) ($inTransitOrders ?? 0);
    $deliveredOrders = (int) ($deliveredOrders ?? 0);

    $totalProducts = (int) ($totalProducts ?? 0);
    $activeProducts = (int) ($activeProducts ?? 0);
    $lowStockCount = (int) ($lowStockCount ?? 0);
    $outOfStockCount = (int) ($outOfStockCount ?? 0);

    $salesToday = (float) ($salesToday ?? $revenueToday ?? 0);
    $salesMonth = (float) ($salesMonth ?? $revenueMonth ?? 0);
    $salesGrowthPct = (float) ($salesGrowthPct ?? $revenueChangePct ?? 0);
    $monthlyOrderCount = (int) ($monthlyOrderCount ?? 0);

    $unreadMessages = (int) ($unreadMessages ?? 0);
    $averageRating = $averageRating ?? null;
    $reviewCount = (int) ($reviewCount ?? 0);

    $recentOrders = collect($recentOrders ?? []);
    $lowStockProducts = collect($lowStockProducts ?? []);
    $topProducts = collect($topProducts ?? []);
    $weeklySales = collect($weeklySales ?? []);
    $recentFeedback = collect($recentFeedback ?? []);
    $recentMessages = collect($recentMessages ?? []);

    $orderPipeline = array_merge([
        'placed' => $newOrders,
        'confirmed' => 0,
        'preparing' => $ordersToPrepare,
        'ready' => $readyForPickup,
        'picked_up' => $pickedUpOrders,
        'delivery' => $inTransitOrders,
        'completed' => $deliveredOrders,
    ], $orderPipeline ?? []);

    $attentionCount =
        $newOrders +
        $ordersToPrepare +
        $readyForPickup +
        $lowStockCount +
        $outOfStockCount;

    $hour = now()->hour;
    $greeting = $hour < 12
        ? 'Good morning'
        : ($hour < 18 ? 'Good afternoon' : 'Good evening');

    $pipelineConfig = [
        'placed' => [
            'label' => 'New Orders',
            'icon' => 'bell-ring',
            'color' => 'bg-teal/10 text-teal-dark',
            'bar' => 'bg-teal',
        ],
        'confirmed' => [
            'label' => 'Confirmed',
            'icon' => 'badge-check',
            'color' => 'bg-sky/10 text-sky',
            'bar' => 'bg-sky',
        ],
        'preparing' => [
            'label' => 'Preparing',
            'icon' => 'box',
            'color' => 'bg-yellow/20 text-amber-700',
            'bar' => 'bg-yellow',
        ],
        'ready' => [
            'label' => 'Ready Pickup',
            'icon' => 'package-check',
            'color' => 'bg-coral/10 text-coral',
            'bar' => 'bg-coral',
        ],
        'picked_up' => [
            'label' => 'Picked Up',
            'icon' => 'truck',
            'color' => 'bg-navy/10 text-navy',
            'bar' => 'bg-navy',
        ],
        'delivery' => [
            'label' => 'In Transit',
            'icon' => 'map-pin',
            'color' => 'bg-sky/10 text-sky',
            'bar' => 'bg-sky',
        ],
        'completed' => [
            'label' => 'Delivered',
            'icon' => 'circle-check',
            'color' => 'bg-teal-light text-teal-dark',
            'bar' => 'bg-teal',
        ],
    ];

    $taskItems = collect([
        [
            'count' => $newOrders,
            'label' => 'orders waiting for confirmation',
            'action' => 'Review Orders',
            'route' => 'seller.orders.notifications',
            'icon' => 'bell-ring',
            'tone' => 'teal',
        ],
        [
            'count' => $ordersToPrepare,
            'label' => 'orders that need packing',
            'action' => 'Prepare Orders',
            'route' => 'seller.orders.prepare',
            'icon' => 'box',
            'tone' => 'coral',
        ],
        [
            'count' => $readyForPickup,
            'label' => 'parcels ready for courier pickup',
            'action' => 'Hand Over',
            'route' => 'seller.orders.courier',
            'icon' => 'truck',
            'tone' => 'sky',
        ],
        [
            'count' => $lowStockCount + $outOfStockCount,
            'label' => 'products that need stock attention',
            'action' => 'Manage Stock',
            'route' => 'seller.inventory',
            'icon' => 'triangle-alert',
            'tone' => 'yellow',
        ],
    ])->filter(fn ($item) => $item['count'] > 0)->values();

    $toneClasses = [
        'teal' => [
            'wrap' => 'bg-teal/10',
            'icon' => 'text-teal-dark',
            'button' => 'text-teal-dark hover:text-teal',
        ],
        'coral' => [
            'wrap' => 'bg-coral/10',
            'icon' => 'text-coral',
            'button' => 'text-coral hover:text-red-500',
        ],
        'sky' => [
            'wrap' => 'bg-sky/10',
            'icon' => 'text-sky',
            'button' => 'text-sky hover:text-navy',
        ],
        'yellow' => [
            'wrap' => 'bg-yellow/20',
            'icon' => 'text-amber-700',
            'button' => 'text-amber-700 hover:text-amber-800',
        ],
    ];
@endphp

<div id="sellerDashboard" class="space-y-5">

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <section>
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">

            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-teal animate-pulse"></span>

                    <p class="text-[12px] uppercase tracking-[0.18em] font-bold text-teal-dark">
                        ShopHop Seller
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-bold text-navy">
                        {{ $greeting }}, {{ $sellerName }}
                    </h1>

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusClass }}">
                        @if ($sellerStatus === 'approved')
                            <x-lucide-badge-check class="w-3 h-3" />
                        @elseif ($sellerStatus === 'suspended')
                            <x-lucide-ban class="w-3 h-3" />
                        @elseif ($sellerStatus === 'rejected')
                            <x-lucide-circle-x class="w-3 h-3" />
                        @else
                            <x-lucide-clock-3 class="w-3 h-3" />
                        @endif

                        {{ $statusLabel }}
                    </span>
                </div>

                <p class="mt-2 text-sm text-navy/45">
                    Here’s what needs your attention today.
                </p>

                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[12px] text-navy/35">
                    <span class="font-semibold text-navy/60">{{ $sellerBusinessName }}</span>
                    <span>•</span>
                    <span>{{ $sellerCategory }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('seller.reports') }}"
                    class="h-9 px-3.5 rounded-lg bg-white border border-gray-border text-xs sm:text-sm font-semibold text-navy flex items-center gap-2 hover:border-teal/40 hover:text-teal-dark transition"
                >
                    <x-lucide-chart-column class="w-4 h-4" />
                    View Reports
                </a>

                <a
                    href="{{ route('seller.inventory') }}"
                    class="h-9 px-3.5 rounded-lg bg-navy text-white text-xs sm:text-sm font-semibold flex items-center gap-2 hover:bg-navy-light transition"
                >
                    <x-lucide-plus class="w-4 h-4" />
                    Add Product
                </a>
            </div>

        </div>
    </section>


    {{-- =========================================================
        KPI CARDS
    ========================================================= --}}
    <section class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">

        <a
            href="{{ route('seller.orders.notifications') }}"
            class="group bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-teal/25 transition"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center">
                    <x-lucide-bell-ring class="w-5 h-5" />
                </div>

                @if ($newOrders > 0)
                    <span class="px-2 py-1 rounded-full bg-teal/10 text-teal-dark text-[10px] font-bold">
                        NEW
                    </span>
                @endif
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">
                {{ number_format($newOrders) }}
            </p>

            <p class="text-xs sm:text-sm text-navy/50">
                New Orders
            </p>

            <p class="mt-2 text-[11px] text-teal-dark font-semibold opacity-0 group-hover:opacity-100 transition">
                Review orders →
            </p>
        </a>


        <a
            href="{{ route('seller.orders.prepare') }}"
            class="group bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-coral/25 transition"
        >
            <div class="w-10 h-10 rounded-lg bg-coral/10 text-coral flex items-center justify-center">
                <x-lucide-box class="w-5 h-5" />
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">
                {{ number_format($ordersToPrepare) }}
            </p>

            <p class="text-xs sm:text-sm text-navy/50">
                To Prepare
            </p>

            <p class="mt-2 text-[11px] text-coral font-semibold opacity-0 group-hover:opacity-100 transition">
                Pack orders →
            </p>
        </a>


        <a
            href="{{ route('seller.inventory') }}"
            class="group bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-yellow/30 transition"
        >
            <div class="w-10 h-10 rounded-lg bg-yellow/20 text-amber-700 flex items-center justify-center">
                <x-lucide-triangle-alert class="w-5 h-5" />
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">
                {{ number_format($lowStockCount + $outOfStockCount) }}
            </p>

            <p class="text-xs sm:text-sm text-navy/50">
                Stock Attention
            </p>

            <p class="mt-2 text-[11px] text-amber-700 font-semibold opacity-0 group-hover:opacity-100 transition">
                Check inventory →
            </p>
        </a>


        <a
            href="{{ route('seller.reports') }}"
            class="group bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-sky/25 transition"
        >
            <div class="w-10 h-10 rounded-lg bg-sky/10 text-sky flex items-center justify-center">
                <x-lucide-wallet class="w-5 h-5" />
            </div>

            <p class="mt-4 text-lg sm:text-xl font-bold text-navy">
                ₱{{ number_format($salesMonth, 2) }}
            </p>

            <p class="text-xs sm:text-sm text-navy/50">
                Sales This Month
            </p>

            <p class="mt-2 text-[11px] text-sky font-semibold opacity-0 group-hover:opacity-100 transition">
                View report →
            </p>
        </a>

    </section>


    {{-- =========================================================
        TODAY'S TASKS
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl overflow-hidden">

        <div class="px-4 sm:px-5 py-4 border-b border-gray-border flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-navy">
                    Today’s Tasks
                </h2>

                <p class="text-[12px] text-navy/40 mt-0.5">
                    Prioritized seller actions that need attention.
                </p>
            </div>

            @if ($attentionCount > 0)
                <span class="px-2.5 py-1 rounded-full bg-coral/10 text-coral text-[11px] font-bold">
                    {{ $attentionCount }} pending
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal/10 text-teal-dark text-[11px] font-bold">
                    <x-lucide-circle-check class="w-3 h-3" />
                    All caught up
                </span>
            @endif
        </div>

        @if ($taskItems->isEmpty())
            <div class="px-5 py-10 text-center">
                <div class="w-11 h-11 mx-auto rounded-xl bg-teal/10 text-teal-dark flex items-center justify-center">
                    <x-lucide-circle-check-big class="w-5 h-5" />
                </div>

                <p class="mt-3 text-xs font-semibold text-navy/60">
                    No urgent seller tasks right now.
                </p>

                <p class="mt-1 text-[12px] text-navy/35">
                    New orders, courier actions, and stock alerts will appear here.
                </p>
            </div>
        @else
            <div class="divide-y divide-gray-border">
                @foreach ($taskItems as $task)
                    @php
                        $tone = $toneClasses[$task['tone']] ?? $toneClasses['teal'];
                    @endphp

                    <div class="px-4 sm:px-5 py-3.5 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg {{ $tone['wrap'] }} {{ $tone['icon'] }} flex items-center justify-center shrink-0">
                            <x-dynamic-component :component="'lucide-' . $task['icon']" class="w-4 h-4" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs sm:text-sm font-semibold text-navy">
                                {{ $task['count'] }} {{ $task['label'] }}
                            </p>

                            <p class="text-[11px] text-navy/35 mt-0.5">
                                Take action to keep your store operations moving.
                            </p>
                        </div>

                        <a
                            href="{{ route($task['route']) }}"
                            class="hidden sm:inline-flex items-center gap-1 text-[11px] font-bold {{ $tone['button'] }} transition shrink-0"
                        >
                            {{ $task['action'] }}
                            <x-lucide-arrow-right class="w-3 h-3" />
                        </a>

                        <a
                            href="{{ route($task['route']) }}"
                            class="sm:hidden w-8 h-8 rounded-lg bg-gray-bg text-navy/40 flex items-center justify-center shrink-0"
                            aria-label="{{ $task['action'] }}"
                        >
                            <x-lucide-chevron-right class="w-3.5 h-3.5" />
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

    </section>


    {{-- =========================================================
        ORDER PIPELINE
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl p-4 sm:p-5">

        <div class="flex items-start sm:items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="text-base font-bold text-navy">
                    Order Pipeline
                </h2>

                <p class="text-[12px] text-navy/40 mt-0.5">
                    From new order to successful delivery.
                </p>
            </div>

            <a
                href="{{ route('seller.orders.notifications') }}"
                class="text-[11px] font-bold text-teal-dark hover:text-teal transition"
            >
                View Orders →
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3">

            @foreach ($pipelineConfig as $key => $stage)
                @php
                    $count = (int) ($orderPipeline[$key] ?? 0);
                    $maxPipeline = max(1, collect($orderPipeline)->max());
                    $barWidth = min(100, ($count / $maxPipeline) * 100);
                @endphp

                <div class="rounded-xl border border-gray-border p-3 hover:shadow-soft transition">
                    <div class="flex items-center justify-between gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $stage['color'] }}">
                            <x-dynamic-component :component="'lucide-' . $stage['icon']" class="w-4 h-4" />
                        </div>

                        <span class="text-lg font-bold text-navy">
                            {{ number_format($count) }}
                        </span>
                    </div>

                    <p class="mt-3 text-xs sm:text-sm font-semibold text-navy/60">
                        {{ $stage['label'] }}
                    </p>

                    <div class="mt-2 h-1.5 rounded-full bg-gray-bg overflow-hidden">
                        <div
                            class="h-full rounded-full {{ $stage['bar'] }}"
                            style="width: {{ $barWidth }}%"
                        ></div>
                    </div>
                </div>
            @endforeach

        </div>

    </section>


    {{-- =========================================================
        RECENT ORDERS + INVENTORY SUMMARY
    ========================================================= --}}
    <section class="grid grid-cols-1 xl:grid-cols-[1.7fr_.8fr] gap-5">

        {{-- Recent Orders --}}
        <div class="bg-white border border-gray-border rounded-xl overflow-hidden">

            <div class="px-4 sm:px-5 py-4 border-b border-gray-border flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-navy">
                        Recent Orders
                    </h2>

                    <p class="text-[12px] text-navy/40 mt-0.5">
                        Latest buyer activity for your store.
                    </p>
                </div>

                <a
                    href="{{ route('seller.orders.notifications') }}"
                    class="text-[11px] font-bold text-teal-dark hover:text-teal transition"
                >
                    View All →
                </a>
            </div>

            @if ($recentOrders->isEmpty())
                <div class="py-14 text-center">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                        <x-lucide-package-search class="w-5 h-5" />
                    </div>

                    <p class="mt-3 text-xs font-semibold text-navy/50">
                        No orders yet
                    </p>

                    <p class="mt-1 text-[12px] text-navy/30">
                        New customer orders will appear here.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-180">

                        <thead>
                            <tr class="border-b border-gray-border bg-gray-bg/40">
                                <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-navy/30">
                                    Order
                                </th>

                                <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-navy/30">
                                    Buyer
                                </th>

                                <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-navy/30">
                                    Items
                                </th>

                                <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-navy/30">
                                    Total
                                </th>

                                <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-navy/30">
                                    Status
                                </th>

                                <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-navy/30">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-border">
                            @foreach ($recentOrders->take(6) as $order)
                                @php
                                    $status = strtolower((string) ($order['status'] ?? 'placed'));

                                    $statusMeta = match ($status) {
                                        'confirmed' => [
                                            'label' => 'Confirmed',
                                            'class' => 'bg-sky/10 text-sky',
                                            'action' => 'Prepare',
                                            'route' => 'seller.orders.prepare',
                                        ],
                                        'preparing' => [
                                            'label' => 'Preparing',
                                            'class' => 'bg-yellow/20 text-amber-700',
                                            'action' => 'Continue',
                                            'route' => 'seller.orders.prepare',
                                        ],
                                        'ready', 'ready_for_pickup' => [
                                            'label' => 'Ready Pickup',
                                            'class' => 'bg-coral/10 text-coral',
                                            'action' => 'Handover',
                                            'route' => 'seller.orders.courier',
                                        ],
                                        'picked_up', 'in_transit', 'delivery', 'out_for_delivery' => [
                                            'label' => 'In Transit',
                                            'class' => 'bg-navy/10 text-navy',
                                            'action' => 'Track',
                                            'route' => 'seller.orders.courier',
                                        ],
                                        'completed', 'delivered' => [
                                            'label' => 'Delivered',
                                            'class' => 'bg-teal/10 text-teal-dark',
                                            'action' => 'View',
                                            'route' => 'seller.orders.confirm',
                                        ],
                                        'cancelled' => [
                                            'label' => 'Cancelled',
                                            'class' => 'bg-red-50 text-red-600',
                                            'action' => 'View',
                                            'route' => 'seller.orders.notifications',
                                        ],
                                        default => [
                                            'label' => 'New',
                                            'class' => 'bg-teal/10 text-teal-dark',
                                            'action' => 'Review',
                                            'route' => 'seller.orders.notifications',
                                        ],
                                    };
                                @endphp

                                <tr class="hover:bg-gray-bg/50 transition">
                                    <td class="px-5 py-3.5">
                                        <p class="text-[12px] font-semibold text-navy">
                                            #{{ $order['id'] ?? '----' }}
                                        </p>

                                        <p class="text-[10px] text-navy/30 mt-0.5">
                                            {{ $order['placed_at'] ?? $order['created_at'] ?? '' }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-3.5 text-[12px] text-navy/65">
                                        {{ $order['buyer_name'] ?? 'Buyer' }}
                                    </td>

                                    <td class="px-5 py-3.5 text-[12px] text-navy/55">
                                        {{ (int) ($order['items_count'] ?? $order['quantity'] ?? 0) }} item(s)
                                    </td>

                                    <td class="px-5 py-3.5 text-[12px] font-semibold text-navy">
                                        ₱{{ number_format((float) ($order['total'] ?? 0), 2) }}
                                    </td>

                                    <td class="px-5 py-3.5">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $statusMeta['class'] }}">
                                            {{ $statusMeta['label'] }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-3.5 text-right">
                                        <a
                                            href="{{ route($statusMeta['route']) }}"
                                            class="text-[11px] font-bold text-teal-dark hover:text-teal transition"
                                        >
                                            {{ $statusMeta['action'] }} →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            @endif

        </div>


        {{-- Inventory Summary --}}
        <div class="space-y-5">

            <div class="bg-white border border-gray-border rounded-xl p-5">

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-navy">
                            Inventory Summary
                        </h2>

                        <p class="text-[12px] text-navy/40 mt-0.5">
                            Current stock health.
                        </p>
                    </div>

                    <x-lucide-boxes class="w-5 h-5 text-teal-dark" />
                </div>

                <div class="grid grid-cols-2 gap-3 mt-5">

                    <div class="rounded-xl bg-gray-bg p-3">
                        <p class="text-xl font-bold text-navy">
                            {{ number_format($totalProducts) }}
                        </p>

                        <p class="text-[11px] text-navy/40 mt-1">
                            Total Products
                        </p>
                    </div>

                    <div class="rounded-xl bg-teal/10 p-3">
                        <p class="text-xl font-bold text-teal-dark">
                            {{ number_format($activeProducts) }}
                        </p>

                        <p class="text-[11px] text-navy/40 mt-1">
                            Active
                        </p>
                    </div>

                    <div class="rounded-xl bg-yellow/20 p-3">
                        <p class="text-xl font-bold text-amber-700">
                            {{ number_format($lowStockCount) }}
                        </p>

                        <p class="text-[11px] text-navy/40 mt-1">
                            Low Stock
                        </p>
                    </div>

                    <div class="rounded-xl bg-coral/10 p-3">
                        <p class="text-xl font-bold text-coral">
                            {{ number_format($outOfStockCount) }}
                        </p>

                        <p class="text-[11px] text-navy/40 mt-1">
                            Out of Stock
                        </p>
                    </div>

                </div>

                <a
                    href="{{ route('seller.inventory') }}"
                    class="mt-4 inline-flex items-center gap-1 text-[11px] font-bold text-teal-dark hover:text-teal transition"
                >
                    Manage Inventory
                    <x-lucide-arrow-right class="w-3 h-3" />
                </a>

            </div>


            <div class="bg-white border border-gray-border rounded-xl overflow-hidden">

                <div class="px-5 py-4 border-b border-gray-border flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-bold text-navy">
                            Needs Restocking
                        </h2>

                        <p class="text-[12px] text-navy/40 mt-0.5">
                            Products with low or zero stock.
                        </p>
                    </div>

                    <x-lucide-triangle-alert class="w-4 h-4 text-amber-700" />
                </div>

                @if ($lowStockProducts->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-[12px] font-semibold text-navy/45">
                            No low-stock products right now.
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-gray-border">
                        @foreach ($lowStockProducts->take(4) as $product)
                            <div class="px-5 py-3 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-yellow/20 text-amber-700 flex items-center justify-center shrink-0">
                                    <x-lucide-package class="w-3.5 h-3.5" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-[12px] font-semibold text-navy truncate">
                                        {{ $product['name'] ?? data_get($product, 'name', 'Product') }}
                                    </p>

                                    <p class="text-[10px] text-navy/35 mt-0.5">
                                        {{ (int) ($product['stock'] ?? data_get($product, 'stock', 0)) }} left
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

        </div>

    </section>


    {{-- =========================================================
        SALES + FULFILLMENT
    ========================================================= --}}
    <section class="grid grid-cols-1 xl:grid-cols-[1.7fr_.8fr] gap-5">

        {{-- Sales Overview --}}
        <div class="bg-white border border-gray-border rounded-xl p-4 sm:p-5">

            <div class="flex items-start sm:items-center justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-base font-bold text-navy">
                        Sales Overview
                    </h2>

                    <p class="text-[12px] text-navy/40 mt-0.5">
                        Revenue performance from completed orders.
                    </p>
                </div>

                <span class="px-2.5 py-1.5 rounded-lg bg-gray-bg text-[11px] font-semibold text-navy/45">
                    PHP ₱
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">

                <div class="rounded-xl bg-gray-bg p-3">
                    <p class="text-[11px] text-navy/40">
                        Today
                    </p>

                    <p class="mt-1 text-base sm:text-lg font-bold text-navy">
                        ₱{{ number_format($salesToday, 2) }}
                    </p>
                </div>

                <div class="rounded-xl bg-teal/10 p-3">
                    <p class="text-[11px] text-navy/40">
                        This Month
                    </p>

                    <p class="mt-1 text-base sm:text-lg font-bold text-teal-dark">
                        ₱{{ number_format($salesMonth, 2) }}
                    </p>
                </div>

                <div class="rounded-xl bg-sky/10 p-3">
                    <p class="text-[11px] text-navy/40">
                        Orders
                    </p>

                    <p class="mt-1 text-base sm:text-lg font-bold text-sky">
                        {{ number_format($monthlyOrderCount) }}
                    </p>
                </div>

                <div class="rounded-xl bg-yellow/20 p-3">
                    <p class="text-[11px] text-navy/40">
                        Growth
                    </p>

                    <p class="mt-1 text-base sm:text-lg font-bold {{ $salesGrowthPct >= 0 ? 'text-teal-dark' : 'text-coral' }}">
                        {{ $salesGrowthPct > 0 ? '+' : '' }}{{ number_format($salesGrowthPct, 1) }}%
                    </p>
                </div>

            </div>

            @if ($weeklySales->isEmpty())
                <div class="h-44 rounded-xl bg-gray-bg/60 flex items-center justify-center">
                    <div class="text-center">
                        <x-lucide-chart-no-axes-column class="w-6 h-6 mx-auto text-navy/20" />

                        <p class="mt-2 text-[12px] text-navy/30">
                            No sales data yet
                        </p>
                    </div>
                </div>
            @else
                @php
                    $maxSale = max(1, (float) $weeklySales->max('amount'));
                @endphp

                <div class="h-44 flex items-end gap-2 sm:gap-3">
                    @foreach ($weeklySales as $sale)
                        @php
                            $amount = (float) ($sale['amount'] ?? 0);
                            $height = max(6, ($amount / $maxSale) * 100);
                        @endphp

                        <div class="flex-1 h-full flex flex-col justify-end items-center gap-2 group">
                            <div class="relative w-full h-32.5 flex items-end">
                                <div
                                    class="w-full rounded-t-lg bg-teal/20 group-hover:bg-teal/35 transition relative"
                                    style="height: {{ $height }}%"
                                    title="₱{{ number_format($amount, 2) }}"
                                >
                                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-teal rounded-t-lg"></div>
                                </div>
                            </div>

                            <span class="text-xs text-navy/35">
                                {{ $sale['label'] ?? '' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>


        {{-- Fulfillment --}}
        <div class="bg-white border border-gray-border rounded-xl p-5">

            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-navy">
                        Fulfillment
                    </h2>

                    <p class="text-[12px] text-navy/40 mt-0.5">
                        Courier and delivery progress.
                    </p>
                </div>

                <x-lucide-truck class="w-5 h-5 text-sky" />
            </div>

            <div class="mt-5 space-y-3">

                <div class="flex items-center justify-between p-3 rounded-xl bg-coral/10">
                    <div class="flex items-center gap-2">
                        <x-lucide-package-check class="w-4 h-4 text-coral" />

                        <span class="text-[12px] text-navy/60">
                            Ready for Pickup
                        </span>
                    </div>

                    <strong class="text-sm text-coral">
                        {{ number_format($readyForPickup) }}
                    </strong>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-navy/10">
                    <div class="flex items-center gap-2">
                        <x-lucide-truck class="w-4 h-4 text-navy" />

                        <span class="text-[12px] text-navy/60">
                            Picked Up
                        </span>
                    </div>

                    <strong class="text-sm text-navy">
                        {{ number_format($pickedUpOrders) }}
                    </strong>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-sky/10">
                    <div class="flex items-center gap-2">
                        <x-lucide-map-pin class="w-4 h-4 text-sky" />

                        <span class="text-[12px] text-navy/60">
                            In Transit
                        </span>
                    </div>

                    <strong class="text-sm text-sky">
                        {{ number_format($inTransitOrders) }}
                    </strong>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-teal/10">
                    <div class="flex items-center gap-2">
                        <x-lucide-circle-check class="w-4 h-4 text-teal-dark" />

                        <span class="text-[12px] text-navy/60">
                            Delivered
                        </span>
                    </div>

                    <strong class="text-sm text-teal-dark">
                        {{ number_format($deliveredOrders) }}
                    </strong>
                </div>

            </div>

            <a
                href="{{ route('seller.orders.courier') }}"
                class="mt-4 inline-flex items-center gap-1 text-[11px] font-bold text-teal-dark hover:text-teal transition"
            >
                View Shipments
                <x-lucide-arrow-right class="w-3 h-3" />
            </a>

        </div>

    </section>


    {{-- =========================================================
        TOP PRODUCTS + FEEDBACK + MESSAGES
    ========================================================= --}}
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Top Products --}}
        <div class="bg-white border border-gray-border rounded-xl overflow-hidden">

            <div class="px-5 py-4 border-b border-gray-border flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-navy">
                        Top Products
                    </h2>

                    <p class="text-[12px] text-navy/40 mt-0.5">
                        Best-performing items.
                    </p>
                </div>

                <x-lucide-star class="w-4 h-4 text-yellow" />
            </div>

            @if ($topProducts->isEmpty())
                <div class="px-5 py-10 text-center">
                    <p class="text-[12px] text-navy/30">
                        No product performance data yet.
                    </p>
                </div>
            @else
                <div class="divide-y divide-gray-border">
                    @foreach ($topProducts->take(5) as $index => $product)
                        <div class="px-5 py-3 flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-navy/10 text-navy flex items-center justify-center text-[12px] font-bold shrink-0">
                                {{ $index + 1 }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-[12px] font-semibold text-navy truncate">
                                    {{ $product['name'] ?? 'Product' }}
                                </p>

                                <p class="text-[10px] text-navy/35 mt-0.5">
                                    {{ number_format((int) ($product['sold'] ?? 0)) }} sold
                                </p>
                            </div>

                            <p class="text-[12px] font-bold text-teal-dark shrink-0">
                                ₱{{ number_format((float) ($product['revenue'] ?? 0), 2) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="px-5 py-3 border-t border-gray-border">
                <a
                    href="{{ route('seller.inventory') }}"
                    class="text-[11px] font-bold text-teal-dark hover:text-teal transition"
                >
                    View Products →
                </a>
            </div>

        </div>


        {{-- Customer Feedback --}}
        <div class="bg-white border border-gray-border rounded-xl overflow-hidden">

            <div class="px-5 py-4 border-b border-gray-border flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-navy">
                        Customer Feedback
                    </h2>

                    <p class="text-[12px] text-navy/40 mt-0.5">
                        Reviews from delivered orders.
                    </p>
                </div>

                <x-lucide-message-circle class="w-4 h-4 text-sky" />
            </div>

            @if ($reviewCount <= 0 || $averageRating === null)
                <div class="px-5 py-10 text-center">
                    <div class="w-10 h-10 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                        <x-lucide-star class="w-4 h-4" />
                    </div>

                    <p class="mt-3 text-[12px] font-semibold text-navy/45">
                        No customer reviews yet.
                    </p>

                    <p class="mt-1 text-[11px] text-navy/30">
                        Reviews from delivered orders will appear here.
                    </p>
                </div>
            @else
                <div class="px-5 py-4 border-b border-gray-border text-center">
                    <p class="text-3xl font-bold text-navy">
                        {{ number_format((float) $averageRating, 1) }}
                    </p>

                    <div class="mt-1 text-yellow text-xs tracking-wider">
                        ★★★★★
                    </div>

                    <p class="mt-1 text-[11px] text-navy/35">
                        Based on {{ number_format($reviewCount) }} review(s)
                    </p>
                </div>

                @if ($recentFeedback->isNotEmpty())
                    <div class="divide-y divide-gray-border">
                        @foreach ($recentFeedback->take(2) as $feedback)
                            <div class="px-5 py-3">
                                <p class="text-[12px] text-navy/65 leading-relaxed">
                                    “{{ $feedback['comment'] ?? '' }}”
                                </p>

                                <p class="mt-1 text-[10px] text-navy/30">
                                    {{ $feedback['buyer_name'] ?? 'Buyer' }}
                                    @if (! empty($feedback['order_id']))
                                        · Order #{{ $feedback['order_id'] }}
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            <div class="px-5 py-3 border-t border-gray-border">
                <a
                    href="{{ route('seller.feedback') }}"
                    class="text-[11px] font-bold text-teal-dark hover:text-teal transition"
                >
                    View Feedback →
                </a>
            </div>

        </div>


        {{-- Messages --}}
        <div class="bg-white border border-gray-border rounded-xl overflow-hidden">

            <div class="px-5 py-4 border-b border-gray-border flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-navy">
                        Messages
                    </h2>

                    <p class="text-[12px] text-navy/40 mt-0.5">
                        Buyer inquiries and order chats.
                    </p>
                </div>

                @if ($unreadMessages > 0)
                    <span class="px-2 py-1 rounded-full bg-coral/10 text-coral text-[10px] font-bold">
                        {{ $unreadMessages }} unread
                    </span>
                @else
                    <x-lucide-messages-square class="w-4 h-4 text-teal-dark" />
                @endif
            </div>

            @if ($recentMessages->isEmpty())
                <div class="px-5 py-10 text-center">
                    <div class="w-10 h-10 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                        <x-lucide-message-square class="w-4 h-4" />
                    </div>

                    <p class="mt-3 text-[12px] font-semibold text-navy/45">
                        No unread messages.
                    </p>

                    <p class="mt-1 text-[11px] text-navy/30">
                        Customer messages will appear here.
                    </p>
                </div>
            @else
                <div class="divide-y divide-gray-border">
                    @foreach ($recentMessages->take(4) as $message)
                        <div class="px-5 py-3 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
                                <x-lucide-user class="w-3.5 h-3.5" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[12px] font-semibold text-navy truncate">
                                        {{ $message['sender_name'] ?? 'Buyer' }}
                                    </p>

                                    <span class="text-[10px] text-navy/25 shrink-0">
                                        {{ $message['time'] ?? '' }}
                                    </span>
                                </div>

                                <p class="text-[11px] text-navy/40 mt-0.5 truncate">
                                    {{ $message['message'] ?? '' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="px-5 py-3 border-t border-gray-border">
                <a
                    href="{{ route('seller.chat') }}"
                    class="text-[11px] font-bold text-teal-dark hover:text-teal transition"
                >
                    Open Messages →
                </a>
            </div>

        </div>

    </section>


    {{-- =========================================================
        QUICK ACTIONS
    ========================================================= --}}
    <section class="relative overflow-hidden bg-navy rounded-xl p-5 text-white">

        <div class="pointer-events-none absolute -right-16 -top-20 w-56 h-56 rounded-full bg-teal/10 blur-3xl"></div>

        <div class="relative flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">

            <div>
                <p class="text-[11px] uppercase tracking-[0.18em] font-semibold text-white/35">
                    Quick Actions
                </p>

                <h2 class="mt-1 text-lg font-bold">
                    Manage your store faster
                </h2>

                <p class="mt-1 text-[12px] text-white/45">
                    Jump directly to your most-used seller tools.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">

                <a
                    href="{{ route('seller.inventory') }}"
                    class="px-3.5 py-3 rounded-xl bg-white/10 hover:bg-white/15 transition text-[12px] font-semibold flex items-center gap-2"
                >
                    <x-lucide-package class="w-4 h-4 text-teal" />
                    Inventory
                </a>

                <a
                    href="{{ route('seller.orders.notifications') }}"
                    class="px-3.5 py-3 rounded-xl bg-white/10 hover:bg-white/15 transition text-[12px] font-semibold flex items-center gap-2"
                >
                    <x-lucide-shopping-bag class="w-4 h-4 text-sky" />
                    Orders
                </a>

                <a
                    href="{{ route('seller.reports') }}"
                    class="px-3.5 py-3 rounded-xl bg-white/10 hover:bg-white/15 transition text-[12px] font-semibold flex items-center gap-2"
                >
                    <x-lucide-file-chart-column class="w-4 h-4 text-yellow" />
                    Reports
                </a>

                <a
                    href="{{ route('seller.chat') }}"
                    class="px-3.5 py-3 rounded-xl bg-white/10 hover:bg-white/15 transition text-[12px] font-semibold flex items-center gap-2"
                >
                    <x-lucide-messages-square class="w-4 h-4 text-coral" />
                    Chat
                </a>

            </div>

        </div>

    </section>

</div>

@endsection
