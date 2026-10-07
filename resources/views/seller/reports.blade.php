@extends('seller.partials.layout')

@section('title', 'Reports')

@section('content')

@php
    $totalSales = (float) ($totalSales ?? 0);
    $previousSales = (float) ($previousSales ?? 0);
    $totalOrders = (int) ($totalOrders ?? 0);
    $previousOrders = (int) ($previousOrders ?? 0);
    $completedOrders = (int) ($completedOrders ?? 0);
    $cancelledOrders = (int) ($cancelledOrders ?? 0);
    $failedDeliveryReports = (int) ($failedDeliveryReports ?? 0);
    $commissionRate = (float) ($commissionRate ?? 10);
    $commissionAmount = $totalSales * ($commissionRate / 100);
    $netEarnings = $totalSales - $commissionAmount;
    $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
    $previousAvgOrderValue = $previousOrders > 0 ? $previousSales / $previousOrders : 0;
    $salesGrowth = $previousSales > 0 ? (($totalSales - $previousSales) / $previousSales) * 100 : ($totalSales > 0 ? 100 : 0);
    $orderGrowth = $previousOrders > 0 ? (($totalOrders - $previousOrders) / $previousOrders) * 100 : ($totalOrders > 0 ? 100 : 0);
    $aovGrowth = $previousAvgOrderValue > 0 ? (($avgOrderValue - $previousAvgOrderValue) / $previousAvgOrderValue) * 100 : ($avgOrderValue > 0 ? 100 : 0);
    $completionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;
    $dailySales = collect($dailySales ?? []);
    $topProducts = collect($topProducts ?? []);
    $categorySales = collect($categorySales ?? []);
    $orderStatusBreakdown = collect($orderStatusBreakdown ?? []);
    $paymentBreakdown = collect($paymentBreakdown ?? []);
    $recentTransactions = collect($recentTransactions ?? []);
    $dailySalesMax = max(1, ...($dailySales->pluck('amount')->all() ?: [1]));
    $categoryMax = max(1, ...($categorySales->pluck('revenue')->all() ?: [1]));

    $formatGrowth = function ($value) {
        $value = round((float) $value, 1);
        return [
            'value' => $value,
            'label' => ($value > 0 ? '+' : '') . number_format($value, 1) . '%',
            'class' => $value >= 0 ? 'text-teal-dark bg-teal/10' : 'text-red-500 bg-red-50',
            'icon' => $value >= 0 ? 'trending-up' : 'trending-down',
        ];
    };

    $salesGrowthMeta = $formatGrowth($salesGrowth);
    $orderGrowthMeta = $formatGrowth($orderGrowth);
    $aovGrowthMeta = $formatGrowth($aovGrowth);
@endphp


<style>
    #sellerReports .report-card {
        transition:
            transform .16s ease,
            box-shadow .16s ease,
            border-color .16s ease;
    }

    #sellerReports .report-card:hover {
        transform: translateY(-1px);
    }

    #sellerReports [hidden],
    #reportExportModal[hidden] {
        display: none !important;
    }

    .report-preset-active {
        background: #0F2C3F;
        color: #ffffff;
        border-color: #0F2C3F;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #printableSellerReport,
        #printableSellerReport * {
            visibility: visible !important;
        }

        #printableSellerReport {
            position: absolute;
            inset: 0;
            width: 100%;
            padding: 20px;
            background: white;
        }

        .report-no-print {
            display: none !important;
        }
    }

    /* Click-to-copy affordance for order numbers */
    #sellerReports [data-copy-order-id] {
        cursor: pointer;
    }

    #sellerReports [data-copy-order-id]:hover {
        text-decoration: underline;
        text-decoration-style: dotted;
        text-underline-offset: 3px;
    }
</style>


<div id="sellerReports" class="space-y-5">

    {{-- =========================================================
        HEADER
    ========================================================= --}}
    <section class="report-no-print">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">

            <div class="min-w-0">

                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    Reports
                </h1>


                <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-3xl">
                    Review sales, platform commission, net earnings, order performance,
                    and your best-performing products for any selected period.
                </p>



            </div>


            <div class="flex flex-wrap items-center gap-2">

                <a
                    href="{{ route('seller.reports.export.csv', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                    class="inline-flex items-center justify-center gap-2 h-9 px-3.5 rounded-lg border border-gray-border bg-white text-xs font-semibold text-navy hover:border-teal/40 hover:text-teal-dark transition"
                >
                    <x-lucide-download class="w-4 h-4" />
                    Export CSV
                </a>


                <button
                    type="button"
                    id="printReportButton"
                    class="inline-flex items-center justify-center gap-2 h-9 px-3.5 rounded-lg bg-navy text-xs font-semibold text-white hover:bg-navy/90 transition"
                >
                    <x-lucide-printer class="w-4 h-4" />
                    Print
                </button>

            </div>

        </div>
    </section>


    {{-- =========================================================
        DATE RANGE
    ========================================================= --}}
    <section class="report-no-print bg-white border border-gray-border rounded-xl p-3">

        <div class="flex flex-col xl:flex-row xl:items-end gap-3">

            <div class="flex flex-wrap items-center gap-1">

                @foreach ([
                    ['key' => '7d', 'label' => '7 Days'],
                    ['key' => '30d', 'label' => '30 Days'],
                    ['key' => 'month', 'label' => 'This Month'],
                    ['key' => 'custom', 'label' => 'Custom'],
                ] as $preset)

                    <button
                        type="button"
                        data-report-preset="{{ $preset['key'] }}"
                        class="report-preset h-9 px-3 rounded-lg border border-transparent text-xs font-semibold transition
                            {{ $preset['key'] === '7d'
                                ? 'report-preset-active'
                                : 'text-navy/50 hover:bg-gray-bg'
                            }}"
                    >
                        {{ $preset['label'] }}
                    </button>

                @endforeach

            </div>


            <form
                method="GET"
                action="{{ route('seller.reports') }}"
                class="flex flex-col sm:flex-row sm:items-end gap-2 flex-1 xl:justify-end"
            >

                <div>

                    <label
                        for="reportDateFrom"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        From
                    </label>

                    <input
                        id="reportDateFrom"
                        type="date"
                        name="date_from"
                        value="{{ $dateFrom }}"
                        class="block h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs text-navy bg-white focus:outline-none focus:border-teal/50"
                    >

                </div>


                <div>

                    <label
                        for="reportDateTo"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        To
                    </label>

                    <input
                        id="reportDateTo"
                        type="date"
                        name="date_to"
                        value="{{ $dateTo }}"
                        class="block h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs text-navy bg-white focus:outline-none focus:border-teal/50"
                    >

                </div>


                <button
                    type="submit"
                    class="h-9 px-4 rounded-lg bg-navy hover:bg-navy/90 text-xs font-semibold text-white transition"
                >
                    Apply Range
                </button>

            </form>

        </div>


        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">

            <p class="text-[10px] text-navy/35">
                Viewing:
                <strong class="text-navy/60">{{ $periodLabel }}</strong>
            </p>


            <p class="text-[10px] text-navy/30">
                {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
                –
                {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
            </p>

        </div>

    </section>


    {{-- =========================================================
        PRINTABLE REPORT
    ========================================================= --}}
    <div id="printableSellerReport" class="space-y-5">

        {{-- Print title --}}
        <section class="hidden print:block">

            <div class="border-b border-gray-border pb-4">

                <p class="text-xl font-bold text-navy">
                    ShopHop Seller Performance Report
                </p>

                <p class="text-xs text-navy/50 mt-1">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
                    –
                    {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                </p>

            </div>

        </section>


        {{-- =========================================================
            KPI CARDS
        ========================================================= --}}
        <section class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">

            {{-- Sales --}}
            <div class="report-card bg-white border border-gray-border rounded-xl p-4">

                <div class="flex items-start justify-between gap-3">

                    <div class="w-10 h-10 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center">
                        <x-lucide-wallet class="w-5 h-5" />
                    </div>


                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold {{ $salesGrowthMeta['class'] }}">

                        <x-dynamic-component
                            :component="'lucide-' . $salesGrowthMeta['icon']"
                            class="w-3 h-3"
                        />

                        {{ $salesGrowthMeta['label'] }}

                    </span>

                </div>


                <p class="mt-4 text-xl sm:text-2xl font-bold text-navy tabular-nums">
                    ₱{{ number_format($totalSales, 2) }}
                </p>


                <p class="text-xs text-navy/45">
                    Gross Sales
                </p>


                <p class="mt-1.5 text-[10px] text-navy/30">
                    vs ₱{{ number_format($previousSales, 2) }} {{ strtolower($previousPeriodLabel) }}
                </p>

            </div>


            {{-- Orders --}}
            <div class="report-card bg-white border border-gray-border rounded-xl p-4">

                <div class="flex items-start justify-between gap-3">

                    <div class="w-10 h-10 rounded-lg bg-sky/10 text-sky flex items-center justify-center">
                        <x-lucide-shopping-bag class="w-5 h-5" />
                    </div>


                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold {{ $orderGrowthMeta['class'] }}">

                        <x-dynamic-component
                            :component="'lucide-' . $orderGrowthMeta['icon']"
                            class="w-3 h-3"
                        />

                        {{ $orderGrowthMeta['label'] }}

                    </span>

                </div>


                <p class="mt-4 text-xl sm:text-2xl font-bold text-navy">
                    {{ number_format($totalOrders) }}
                </p>


                <p class="text-xs text-navy/45">
                    Total Orders
                </p>


                <p class="mt-1.5 text-[10px] text-navy/30">
                    {{ number_format($completionRate, 1) }}% completion rate
                </p>

            </div>


            {{-- AOV --}}
            <div class="report-card bg-white border border-gray-border rounded-xl p-4">

                <div class="flex items-start justify-between gap-3">

                    <div class="w-10 h-10 rounded-lg bg-yellow/20 text-amber-700 flex items-center justify-center">
                        <x-lucide-receipt-text class="w-5 h-5" />
                    </div>


                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold {{ $aovGrowthMeta['class'] }}">

                        <x-dynamic-component
                            :component="'lucide-' . $aovGrowthMeta['icon']"
                            class="w-3 h-3"
                        />

                        {{ $aovGrowthMeta['label'] }}

                    </span>

                </div>


                <p class="mt-4 text-xl sm:text-2xl font-bold text-navy tabular-nums">
                    ₱{{ number_format($avgOrderValue, 2) }}
                </p>


                <p class="text-xs text-navy/45">
                    Avg. Order Value
                </p>


                <p class="mt-1.5 text-[10px] text-navy/30">
                    Average gross value per order
                </p>

            </div>


            {{-- Net earnings --}}
            <div class="report-card bg-white border border-gray-border rounded-xl p-4">

                <div class="flex items-start justify-between gap-3">

                    <div class="w-10 h-10 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                        <x-lucide-badge-dollar-sign class="w-5 h-5" />
                    </div>


                    <span class="px-2 py-1 rounded-full bg-navy/10 text-[10px] font-bold text-navy/55">
                        NET
                    </span>

                </div>


                <p class="mt-4 text-xl sm:text-2xl font-bold text-teal-dark tabular-nums">
                    ₱{{ number_format($netEarnings, 2) }}
                </p>


                <p class="text-xs text-navy/45">
                    Net Earnings
                </p>


                <p class="mt-1.5 text-[10px] text-navy/30">
                    After {{ number_format($commissionRate, 0) }}% platform commission
                </p>

            </div>

        </section>


        {{-- =========================================================
            EARNINGS BREAKDOWN + ORDER HEALTH
        ========================================================= --}}
        <section class="grid grid-cols-1 xl:grid-cols-[1fr_.8fr] gap-5">

            {{-- Earnings --}}
            <div class="bg-white border border-gray-border rounded-xl p-4 sm:p-5">

                <div class="flex items-start justify-between gap-3">

                    <div>

                        <h2 class="text-base font-bold text-navy">
                            Earnings Breakdown
                        </h2>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Gross sales minus platform commission.
                        </p>

                    </div>


                    <x-lucide-calculator class="w-5 h-5 text-teal-dark" />

                </div>


                <div class="mt-5 space-y-3">

                    <div class="flex items-center justify-between gap-3">

                        <div>

                            <p class="text-xs font-semibold text-navy">
                                Gross Sales
                            </p>

                            <p class="text-[10px] text-navy/35 mt-0.5">
                                Total sales before platform deductions
                            </p>

                        </div>


                        <p class="text-sm font-bold text-navy tabular-nums">
                            ₱{{ number_format($totalSales, 2) }}
                        </p>

                    </div>


                    <div class="flex items-center justify-between gap-3">

                        <div>

                            <p class="text-xs font-semibold text-navy">
                                Platform Commission
                            </p>

                            <p class="text-[10px] text-navy/35 mt-0.5">
                                {{ number_format($commissionRate, 0) }}% of gross sales
                            </p>

                        </div>


                        <p class="text-sm font-bold text-coral tabular-nums">
                            −₱{{ number_format($commissionAmount, 2) }}
                        </p>

                    </div>


                    <div class="pt-3 border-t border-gray-border flex items-center justify-between gap-3">

                        <div>

                            <p class="text-sm font-bold text-navy">
                                Estimated Net Earnings
                            </p>

                            <p class="text-[10px] text-navy/35 mt-0.5">
                                Amount remaining after commission
                            </p>

                        </div>


                        <p class="text-lg font-bold text-teal-dark tabular-nums">
                            ₱{{ number_format($netEarnings, 2) }}
                        </p>

                    </div>

                </div>


                <div class="mt-5 h-3 rounded-full overflow-hidden bg-gray-bg flex">

                    <div
                        class="h-full bg-teal"
                        style="width: {{ 100 - $commissionRate }}%"
                        title="Seller net earnings"
                    ></div>

                    <div
                        class="h-full bg-coral"
                        style="width: {{ $commissionRate }}%"
                        title="Platform commission"
                    ></div>

                </div>


                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-navy/40">

                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-teal"></span>
                        Seller: {{ number_format(100 - $commissionRate, 0) }}%
                    </span>

                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-coral"></span>
                        Commission: {{ number_format($commissionRate, 0) }}%
                    </span>

                </div>

            </div>


            {{-- Order Health --}}
            <div class="bg-white border border-gray-border rounded-xl p-4 sm:p-5">

                <div>

                    <h2 class="text-base font-bold text-navy">
                        Order Health
                    </h2>

                    <p class="text-[10px] text-navy/40 mt-0.5">
                        Completion, cancellation, and return outcomes.
                    </p>

                </div>


                <div class="mt-5 space-y-4">

                    @foreach ($orderStatusBreakdown as $status)

                        @php
                            $pct = $totalOrders > 0
                                ? round(($status['count'] / $totalOrders) * 100, 1)
                                : 0;
                        @endphp


                        <div>

                            <div class="flex items-center justify-between gap-3">

                                <div class="flex items-center gap-2">

                                    <span class="w-2.5 h-2.5 rounded-full {{ $status['class'] }}"></span>

                                    <span class="text-xs font-semibold text-navy/65">
                                        {{ $status['status'] }}
                                    </span>

                                </div>


                                <div class="text-right">

                                    <span class="text-xs font-bold text-navy">
                                        {{ $status['count'] }}
                                    </span>

                                    <span class="text-[10px] text-navy/30 ml-1">
                                        {{ number_format($pct, 1) }}%
                                    </span>

                                </div>

                            </div>


                            <div class="mt-1.5 h-1.5 rounded-full bg-gray-bg overflow-hidden">

                                <div
                                    class="h-full rounded-full {{ $status['class'] }}"
                                    style="width: {{ $pct }}%"
                                ></div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        </section>


        {{-- =========================================================
            SALES TREND + TOP PRODUCTS
        ========================================================= --}}
        <section class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,.85fr)] gap-5">

            {{-- Sales trend --}}
            <div class="bg-white border border-gray-border rounded-xl p-4 sm:p-5">

                <div class="flex flex-wrap items-start justify-between gap-3 mb-5">

                    <div>

                        <h2 class="text-base font-bold text-navy">
                            Sales Trend
                        </h2>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Daily gross revenue and order volume.
                        </p>

                    </div>


                    <span class="text-[10px] font-semibold text-navy/40 bg-gray-bg px-2.5 py-1 rounded-full">
                        ₱ PHP
                    </span>

                </div>


                <div class="relative">

                    <div class="absolute inset-x-0 top-0 bottom-7 flex flex-col justify-between pointer-events-none">

                        @foreach ([100, 75, 50, 25, 0] as $line)

                            <div class="border-t border-dashed border-gray-border/70"></div>

                        @endforeach

                    </div>


                    <div class="relative flex items-end justify-between gap-2 sm:gap-3 h-56">

                        @foreach ($dailySales as $day)

                            @php
                                $barPct = max(
                                    5,
                                    round(($day['amount'] / $dailySalesMax) * 100)
                                );
                            @endphp


                            <div class="flex-1 flex flex-col items-center justify-end h-full min-w-0 group">

                                <div class="w-full flex-1 flex items-end relative">

                                    <div
                                        class="w-full max-w-14 mx-auto rounded-t-lg bg-teal/15 hover:bg-teal/25 transition relative"
                                        style="height: {{ $barPct }}%"
                                    >

                                        <div class="absolute inset-x-0 top-0 h-1.5 rounded-t-lg bg-teal"></div>


                                        <div class="absolute -top-16 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 pointer-events-none transition z-10">

                                            <div class="rounded-lg bg-navy text-white px-2.5 py-2 shadow-soft whitespace-nowrap">

                                                <p class="text-[10px] font-bold">
                                                    ₱{{ number_format($day['amount']) }}
                                                </p>

                                                <p class="text-[9px] text-white/60 mt-0.5">
                                                    {{ $day['orders'] }} orders
                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <div class="pt-2 text-center">

                                    <p class="text-[11px] font-semibold text-navy/55">
                                        {{ $day['label'] }}
                                    </p>

                                    <p class="hidden sm:block text-[9px] text-navy/30 mt-0.5">
                                        {{ $day['date'] }}
                                    </p>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>


                <div class="mt-4 pt-4 border-t border-gray-border grid grid-cols-2 sm:grid-cols-3 gap-3">

                    <div>

                        <p class="text-[10px] text-navy/35">
                            Best Day
                        </p>

                        @php
                            $bestDay = $dailySales->sortByDesc('amount')->first();
                        @endphp

                        <p class="text-xs font-bold text-navy mt-1">
                            {{ $bestDay['label'] ?? '—' }}
                            ·
                            ₱{{ number_format($bestDay['amount'] ?? 0) }}
                        </p>

                    </div>


                    <div>

                        <p class="text-[10px] text-navy/35">
                            Daily Average
                        </p>

                        <p class="text-xs font-bold text-navy mt-1">
                            ₱{{ number_format($dailySales->avg('amount') ?? 0) }}
                        </p>

                    </div>


                    <div class="col-span-2 sm:col-span-1">

                        <p class="text-[10px] text-navy/35">
                            Avg. Orders / Day
                        </p>

                        <p class="text-xs font-bold text-navy mt-1">
                            {{ number_format($dailySales->avg('orders') ?? 0, 1) }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Top Products --}}
            <div class="bg-white border border-gray-border rounded-xl p-4 sm:p-5">

                <div class="flex items-start justify-between gap-3">

                    <div>

                        <h2 class="text-base font-bold text-navy">
                            Top Products
                        </h2>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Ranked by gross revenue.
                        </p>

                    </div>


                    <x-lucide-trophy class="w-5 h-5 text-amber-700" />

                </div>


                <div class="mt-4 divide-y divide-gray-border">

                    @foreach ($topProducts as $index => $product)

                        <div class="py-3 first:pt-0 last:pb-0">

                            <div class="flex items-center gap-3">

                                <div class="w-7 h-7 rounded-lg bg-gray-bg text-[10px] font-bold text-navy/45 flex items-center justify-center shrink-0">
                                    {{ $index + 1 }}
                                </div>


                                <div class="w-11 h-11 rounded-lg bg-gray-bg border border-gray-border overflow-hidden shrink-0">

                                    @if (!empty($product['image']))

                                        <img
                                            src="{{ $product['image'] }}"
                                            alt="{{ $product['name'] }}"
                                            class="w-full h-full object-cover"
                                            loading="lazy"
                                        >

                                    @else

                                        <div class="w-full h-full flex items-center justify-center">
                                            <x-lucide-package class="w-4 h-4 text-navy/25" />
                                        </div>

                                    @endif

                                </div>


                                <div class="min-w-0 flex-1">

                                    <p class="text-xs font-semibold text-navy truncate">
                                        {{ $product['name'] }}
                                    </p>

                                    <p class="text-[10px] text-navy/35 mt-0.5 truncate">
                                        {{ $product['variant'] }}
                                        ·
                                        {{ $product['sold'] }} sold
                                    </p>

                                </div>


                                <div class="text-right shrink-0">

                                    <p class="text-xs font-bold text-navy tabular-nums">
                                        ₱{{ number_format($product['revenue']) }}
                                    </p>

                                    <p class="text-[9px] text-navy/30 mt-0.5">
                                        {{ number_format($product['share'], 1) }}%
                                    </p>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        </section>


        {{-- =========================================================
            CATEGORY + PAYMENT
        ========================================================= --}}
        <section class="grid grid-cols-1 xl:grid-cols-2 gap-5">

            {{-- Category Sales --}}
            <div class="bg-white border border-gray-border rounded-xl p-4 sm:p-5">

                <div>

                    <h2 class="text-base font-bold text-navy">
                        Sales by Category
                    </h2>

                    <p class="text-[10px] text-navy/40 mt-0.5">
                        Revenue contribution by product category.
                    </p>

                </div>


                <div class="mt-5 space-y-4">

                    @foreach ($categorySales as $category)

                        @php
                            $width = round(
                                ($category['revenue'] / $categoryMax) * 100
                            );
                        @endphp


                        <div>

                            <div class="flex items-center justify-between gap-3">

                                <div>

                                    <p class="text-xs font-semibold text-navy">
                                        {{ $category['category'] }}
                                    </p>

                                    <p class="text-[10px] text-navy/35 mt-0.5">
                                        {{ $category['orders'] }} orders
                                    </p>

                                </div>


                                <p class="text-xs font-bold text-navy tabular-nums">
                                    ₱{{ number_format($category['revenue']) }}
                                </p>

                            </div>


                            <div class="mt-2 h-2 rounded-full bg-gray-bg overflow-hidden">

                                <div
                                    class="h-full rounded-full bg-teal"
                                    style="width: {{ $width }}%"
                                ></div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>


            {{-- Payment methods --}}
            <div class="bg-white border border-gray-border rounded-xl p-4 sm:p-5">

                <div>

                    <h2 class="text-base font-bold text-navy">
                        Payment Methods
                    </h2>

                    <p class="text-[10px] text-navy/40 mt-0.5">
                        Order and sales mix by payment method.
                    </p>

                </div>


                <div class="mt-5 space-y-3">

                    @foreach ($paymentBreakdown as $payment)

                        @php
                            $paymentPct = $totalSales > 0
                                ? ($payment['amount'] / $totalSales) * 100
                                : 0;
                        @endphp


                        <div class="rounded-xl border border-gray-border p-3">

                            <div class="flex items-center justify-between gap-3">

                                <div>

                                    <p class="text-xs font-semibold text-navy">
                                        {{ $payment['method'] }}
                                    </p>

                                    <p class="text-[10px] text-navy/35 mt-0.5">
                                        {{ $payment['orders'] }} orders
                                    </p>

                                </div>


                                <div class="text-right">

                                    <p class="text-xs font-bold text-navy">
                                        ₱{{ number_format($payment['amount']) }}
                                    </p>

                                    <p class="text-[9px] text-navy/30 mt-0.5">
                                        {{ number_format($paymentPct, 1) }}%
                                    </p>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        </section>


        {{-- =========================================================
            RECENT TRANSACTIONS
        ========================================================= --}}
        <section class="bg-white border border-gray-border rounded-xl overflow-hidden">

            <div class="px-4 sm:px-5 py-4 border-b border-gray-border flex flex-wrap items-center justify-between gap-3">

                <div>

                    <h2 class="text-base font-bold text-navy">
                        Recent Report Transactions
                    </h2>

                    <p class="text-[10px] text-navy/40 mt-0.5">
                        Sample order-level sales and commission breakdown.
                    </p>

                </div>


                <span class="text-[10px] font-semibold text-navy/35">
                    Latest {{ $recentTransactions->count() }} rows
                </span>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full min-w-[900px]">

                    <thead>

                        <tr class="border-b border-gray-border bg-gray-bg/40">

                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.1em] text-navy/30">
                                Order
                            </th>

                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.1em] text-navy/30">
                                Buyer
                            </th>

                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-[0.1em] text-navy/30">
                                Items
                            </th>

                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.1em] text-navy/30">
                                Gross
                            </th>

                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.1em] text-navy/30">
                                Commission
                            </th>

                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.1em] text-navy/30">
                                Net
                            </th>

                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.1em] text-navy/30">
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-border">

                        @foreach ($recentTransactions as $transaction)

                            <tr class="hover:bg-gray-bg/40 transition">

                                <td class="px-4 py-3">

                                    <p
                                        class="text-xs font-semibold text-navy"
                                        data-copy-order-id
                                        data-order-id="{{ $transaction['order_id'] }}"
                                    >
                                        {{ $transaction['order_id'] }}
                                    </p>

                                    <p class="text-[9px] text-navy/30 mt-0.5">
                                        {{ $transaction['date'] }}
                                    </p>

                                </td>


                                <td class="px-4 py-3 text-xs text-navy/60">
                                    {{ $transaction['buyer'] }}
                                </td>


                                <td class="px-4 py-3 text-center text-xs text-navy/55">
                                    {{ $transaction['items'] }}
                                </td>


                                <td class="px-4 py-3 text-right text-xs font-semibold text-navy tabular-nums">
                                    ₱{{ number_format($transaction['gross'], 2) }}
                                </td>


                                <td class="px-4 py-3 text-right text-xs font-semibold text-coral tabular-nums">
                                    −₱{{ number_format($transaction['commission'], 2) }}
                                </td>


                                <td class="px-4 py-3 text-right text-xs font-bold text-teal-dark tabular-nums">
                                    ₱{{ number_format($transaction['net'], 2) }}
                                </td>


                                <td class="px-4 py-3 text-right">

                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold
                                            {{ $transaction['status'] === 'Completed'
                                                ? 'bg-teal/10 text-teal-dark'
                                                : 'bg-red-50 text-red-500'
                                            }}"
                                    >
                                        {{ $transaction['status'] }}
                                    </span>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </section>


        {{-- =========================================================
            REPORT FOOTNOTE
        ========================================================= --}}


    </div>

</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const presets =
        Array.from(
            document.querySelectorAll('[data-report-preset]')
        );

    const fromInput =
        document.getElementById('reportDateFrom');

    const toInput =
        document.getElementById('reportDateTo');

    function formatDateInput(date) {

        const year =
            date.getFullYear();

        const month =
            String(date.getMonth() + 1).padStart(2, '0');

        const day =
            String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }


    function setPresetActive(key) {

        presets.forEach(function (button) {

            const active =
                button.dataset.reportPreset === key;


            button.classList.toggle(
                'report-preset-active',
                active
            );


            button.classList.toggle(
                'text-navy/50',
                !active
            );


            button.classList.toggle(
                'hover:bg-gray-bg',
                !active
            );
        });
    }


    function applyPreset(key) {

        const today =
            new Date();


        let from =
            new Date(today);


        let to =
            new Date(today);


        if (key === '7d') {

            from.setDate(
                today.getDate() - 6
            );

        } else if (key === '30d') {

            from.setDate(
                today.getDate() - 29
            );

        } else if (key === 'month') {

            from =
                new Date(
                    today.getFullYear(),
                    today.getMonth(),
                    1
                );

        } else if (key === 'custom') {

            setPresetActive('custom');

            fromInput?.focus();

            return;
        }


        if (fromInput) {
            fromInput.value =
                formatDateInput(from);
        }


        if (toInput) {
            toInput.value =
                formatDateInput(to);
        }


        setPresetActive(key);
    }


    presets.forEach(function (button) {

        button.addEventListener('click', function () {

            applyPreset(
                button.dataset.reportPreset || 'custom'
            );
        });
    });


    fromInput?.addEventListener('change', function () {

        setPresetActive('custom');
    });


    toInput?.addEventListener('change', function () {

        setPresetActive('custom');
    });


    /* ---------------------------------------------------------
       PRINT
    --------------------------------------------------------- */
    function printReport() {

        window.print();
    }


    document
        .getElementById('printReportButton')
        ?.addEventListener(
            'click',
            printReport
        );


    /* ---------------------------------------------------------
       COPY ORDER ID (silent, no toast/feedback)
    --------------------------------------------------------- */
    document.addEventListener('click', function (event) {
        const target = event.target.closest('[data-copy-order-id]');
        if (!target) return;

        const orderId = target.dataset.orderId || target.textContent.trim();
        if (!orderId) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(orderId).catch(function () {});
        } else {
            const temp = document.createElement('textarea');
            temp.value = orderId;
            temp.style.position = 'fixed';
            temp.style.opacity = '0';
            document.body.appendChild(temp);
            temp.select();
            try { document.execCommand('copy'); } catch (error) {}
            document.body.removeChild(temp);
        }
    });


    /* ---------------------------------------------------------
       ESCAPE
    --------------------------------------------------------- */
    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                !exportModal.hidden
            ) {

                exportModal.hidden =
                    true;

                document.body.style.overflow =
                    '';
            }
        }
    );
});
</script>
@endpush

@endsection