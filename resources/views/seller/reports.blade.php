@extends('seller.partials.layout')

@section('title', 'Reports')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    |
    | Your backend teammate can later replace these values with controller data
    | and connect real date filtering, order/product aggregation, commissions,
    | CSV/PDF export, pagination, and historical comparisons.
    */

    $dateFrom = $dateFrom ?? now()->subDays(6)->format('Y-m-d');
    $dateTo = $dateTo ?? now()->format('Y-m-d');

    $periodLabel = $periodLabel ?? 'Last 7 Days';
    $previousPeriodLabel = $previousPeriodLabel ?? 'Previous 7 Days';

    $totalSales = (float) ($totalSales ?? 48250);
    $previousSales = (float) ($previousSales ?? 43100);

    $totalOrders = (int) ($totalOrders ?? 96);
    $previousOrders = (int) ($previousOrders ?? 84);

    $completedOrders = (int) ($completedOrders ?? 89);
    $cancelledOrders = (int) ($cancelledOrders ?? 4);
    $returnedOrders = (int) ($returnedOrders ?? 3);

    $commissionRate = (float) ($commissionRate ?? 10);
    $commissionAmount = $totalSales * ($commissionRate / 100);
    $netEarnings = $totalSales - $commissionAmount;

    $avgOrderValue = $totalOrders > 0
        ? $totalSales / $totalOrders
        : 0;

    $previousAvgOrderValue = $previousOrders > 0
        ? $previousSales / $previousOrders
        : 0;

    $salesGrowth = $previousSales > 0
        ? (($totalSales - $previousSales) / $previousSales) * 100
        : 0;

    $orderGrowth = $previousOrders > 0
        ? (($totalOrders - $previousOrders) / $previousOrders) * 100
        : 0;

    $aovGrowth = $previousAvgOrderValue > 0
        ? (($avgOrderValue - $previousAvgOrderValue) / $previousAvgOrderValue) * 100
        : 0;

    $completionRate = $totalOrders > 0
        ? ($completedOrders / $totalOrders) * 100
        : 0;

    $dailySales = collect($dailySales ?? [
        ['label' => 'Mon', 'date' => 'Sep 2', 'amount' => 5200, 'orders' => 11],
        ['label' => 'Tue', 'date' => 'Sep 3', 'amount' => 6800, 'orders' => 14],
        ['label' => 'Wed', 'date' => 'Sep 4', 'amount' => 4100, 'orders' => 9],
        ['label' => 'Thu', 'date' => 'Sep 5', 'amount' => 7300, 'orders' => 15],
        ['label' => 'Fri', 'date' => 'Sep 6', 'amount' => 8950, 'orders' => 17],
        ['label' => 'Sat', 'date' => 'Sep 7', 'amount' => 9600, 'orders' => 18],
        ['label' => 'Sun', 'date' => 'Sep 8', 'amount' => 6300, 'orders' => 12],
    ]);

    $dailySalesMax = max(
        1,
        ...($dailySales->pluck('amount')->all() ?: [1])
    );

    $topProducts = collect($topProducts ?? [
        [
            'name' => 'Handwoven Rattan Basket',
            'variant' => 'Natural / Medium',
            'sku' => 'RATTAN-NAT-M',
            'sold' => 34,
            'orders' => 29,
            'revenue' => 15300,
            'share' => 31.7,
            'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=220&q=80',
        ],
        [
            'name' => 'Barako Coffee Beans 250g',
            'variant' => 'Dark Roast',
            'sku' => 'BARAKO-250-DR',
            'sold' => 58,
            'orders' => 35,
            'revenue' => 12760,
            'share' => 26.4,
            'image' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=220&q=80',
        ],
        [
            'name' => 'Handmade Soap Bar Set',
            'variant' => 'Assorted / 4 pcs',
            'sku' => 'SOAP-SET-4',
            'sold' => 41,
            'orders' => 25,
            'revenue' => 7175,
            'share' => 14.9,
            'image' => 'https://images.unsplash.com/photo-1600857544200-b2f666a9a2ec?auto=format&fit=crop&w=220&q=80',
        ],
        [
            'name' => 'Daily Glow Face Serum',
            'variant' => '30ml',
            'sku' => 'SERUM-30ML',
            'sold' => 20,
            'orders' => 18,
            'revenue' => 6590,
            'share' => 13.7,
            'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=220&q=80',
        ],
    ]);

    $categorySales = collect($categorySales ?? [
        ['category' => 'Home & Living', 'revenue' => 17780, 'orders' => 31],
        ['category' => 'Food & Beverages', 'revenue' => 12760, 'orders' => 35],
        ['category' => 'Beauty & Personal Care', 'revenue' => 8090, 'orders' => 16],
        ['category' => 'Fashion', 'revenue' => 6120, 'orders' => 14],
        ['category' => 'Electronics', 'revenue' => 3500, 'orders' => 8],
    ]);

    $categoryMax = max(
        1,
        ...($categorySales->pluck('revenue')->all() ?: [1])
    );

    $orderStatusBreakdown = collect($orderStatusBreakdown ?? [
        ['status' => 'Completed', 'count' => $completedOrders, 'class' => 'bg-teal', 'text' => 'text-teal-dark'],
        ['status' => 'Cancelled', 'count' => $cancelledOrders, 'class' => 'bg-coral', 'text' => 'text-coral'],
        ['status' => 'Returned', 'count' => $returnedOrders, 'class' => 'bg-red-400', 'text' => 'text-red-500'],
    ]);

    $paymentBreakdown = collect($paymentBreakdown ?? [
        ['method' => 'Cash on Delivery', 'orders' => 54, 'amount' => 27180],
        ['method' => 'GCash', 'orders' => 28, 'amount' => 14120],
        ['method' => 'Card / Online', 'orders' => 14, 'amount' => 6950],
    ]);

    $recentTransactions = collect($recentTransactions ?? [
        [
            'order_id' => 'ORD-10231',
            'date' => 'Sep 8, 2026 · 12:44 PM',
            'buyer' => 'Maricel Santos',
            'items' => 3,
            'gross' => 1780,
            'commission' => 178,
            'net' => 1602,
            'status' => 'Completed',
        ],
        [
            'order_id' => 'ORD-10228',
            'date' => 'Sep 8, 2026 · 11:08 AM',
            'buyer' => 'Jonas Villareal',
            'items' => 2,
            'gross' => 1320,
            'commission' => 132,
            'net' => 1188,
            'status' => 'Completed',
        ],
        [
            'order_id' => 'ORD-10224',
            'date' => 'Sep 8, 2026 · 9:36 AM',
            'buyer' => 'Ella Marasigan',
            'items' => 1,
            'gross' => 899,
            'commission' => 89.90,
            'net' => 809.10,
            'status' => 'Completed',
        ],
        [
            'order_id' => 'ORD-10216',
            'date' => 'Sep 7, 2026 · 5:20 PM',
            'buyer' => 'Kim Delos Reyes',
            'items' => 2,
            'gross' => 1560,
            'commission' => 156,
            'net' => 1404,
            'status' => 'Completed',
        ],
        [
            'order_id' => 'ORD-10211',
            'date' => 'Sep 7, 2026 · 2:18 PM',
            'buyer' => 'Trisha Ang',
            'items' => 1,
            'gross' => 720,
            'commission' => 72,
            'net' => 648,
            'status' => 'Returned',
        ],
    ]);

    $formatGrowth = function ($value) {
        $value = round((float) $value, 1);

        return [
            'value' => $value,
            'label' => ($value > 0 ? '+' : '') . number_format($value, 1) . '%',
            'class' => $value >= 0
                ? 'text-teal-dark bg-teal/10'
                : 'text-red-500 bg-red-50',
            'icon' => $value >= 0
                ? 'trending-up'
                : 'trending-down',
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


                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-yellow/20 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    FRONTEND DEMO · HARDCODED DATA
                </div>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <button
                    type="button"
                    id="openExportModal"
                    class="inline-flex items-center justify-center gap-2 h-9 px-3.5 rounded-lg border border-gray-border bg-white text-xs font-semibold text-navy hover:border-teal/40 hover:text-teal-dark transition"
                >
                    <x-lucide-download class="w-4 h-4" />
                    Export Report
                </button>


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
        <section class="rounded-xl border border-gray-border bg-gray-bg/30 p-4">

            <div class="flex items-start gap-3">

                <x-lucide-info class="w-4 h-4 text-navy/35 mt-0.5 shrink-0" />

                <div>

                    <p class="text-xs font-semibold text-navy/60">
                        Demo report note
                    </p>

                    <p class="text-[10px] text-navy/40 mt-1 leading-relaxed">
                        All values on this page are hardcoded sample data.
                        Your backend can later calculate these figures from completed orders,
                        refunds/returns, vouchers, payment records, and platform commission entries.
                    </p>

                </div>

            </div>

        </section>

    </div>

</div>


{{-- =========================================================
    EXPORT MODAL
========================================================= --}}
<div
    id="reportExportModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>

    <div
        data-close-export
        class="absolute inset-0 bg-navy/45"
    ></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-md">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Export Report
                </p>

                <p class="text-[11px] text-navy/40 mt-0.5">
                    Frontend demo export options
                </p>

            </div>


            <button
                type="button"
                data-close-export
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <div class="p-5">

            <p class="text-xs text-navy/50 leading-relaxed">
                Choose an export format. For now these buttons demonstrate the UI;
                your backend teammate can later generate actual PDF or CSV files.
            </p>


            <div class="mt-4 space-y-2">

                <button
                    type="button"
                    data-export-type="pdf"
                    class="w-full flex items-center gap-3 rounded-xl border border-gray-border p-3 text-left hover:border-teal/30 hover:bg-teal/5 transition"
                >

                    <div class="w-9 h-9 rounded-lg bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                        <x-lucide-file-text class="w-4 h-4" />
                    </div>


                    <div class="min-w-0 flex-1">

                        <p class="text-xs font-semibold text-navy">
                            Export as PDF
                        </p>

                        <p class="text-[10px] text-navy/35 mt-0.5">
                            Best for printing and formal submission
                        </p>

                    </div>


                    <x-lucide-chevron-right class="w-4 h-4 text-navy/25" />

                </button>


                <button
                    type="button"
                    data-export-type="csv"
                    class="w-full flex items-center gap-3 rounded-xl border border-gray-border p-3 text-left hover:border-teal/30 hover:bg-teal/5 transition"
                >

                    <div class="w-9 h-9 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
                        <x-lucide-sheet class="w-4 h-4" />
                    </div>


                    <div class="min-w-0 flex-1">

                        <p class="text-xs font-semibold text-navy">
                            Export as CSV
                        </p>

                        <p class="text-[10px] text-navy/35 mt-0.5">
                            Best for spreadsheet analysis
                        </p>

                    </div>


                    <x-lucide-chevron-right class="w-4 h-4 text-navy/25" />

                </button>


                <button
                    type="button"
                    data-export-type="print"
                    class="w-full flex items-center gap-3 rounded-xl border border-gray-border p-3 text-left hover:border-teal/30 hover:bg-teal/5 transition"
                >

                    <div class="w-9 h-9 rounded-lg bg-sky/10 text-sky flex items-center justify-center shrink-0">
                        <x-lucide-printer class="w-4 h-4" />
                    </div>


                    <div class="min-w-0 flex-1">

                        <p class="text-xs font-semibold text-navy">
                            Print Report
                        </p>

                        <p class="text-[10px] text-navy/35 mt-0.5">
                            Open your browser print dialog
                        </p>

                    </div>


                    <x-lucide-chevron-right class="w-4 h-4 text-navy/25" />

                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    DEMO TOAST
========================================================= --}}
<div
    id="reportToast"
    hidden
    class="fixed right-4 bottom-4 z-[60] max-w-sm rounded-xl border border-teal/25 bg-white shadow-panel px-4 py-3"
>

    <div class="flex items-start gap-3">

        <div class="w-8 h-8 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
            <x-lucide-circle-check class="w-4 h-4" />
        </div>


        <div>

            <p class="text-xs font-bold text-navy">
                Report action
            </p>

            <p
                id="reportToastMessage"
                class="text-[11px] text-navy/45 mt-0.5"
            ></p>

        </div>

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

    const exportModal =
        document.getElementById('reportExportModal');

    const toast =
        document.getElementById('reportToast');

    const toastMessage =
        document.getElementById('reportToastMessage');

    let toastTimer = null;


    function formatDateInput(date) {

        const year =
            date.getFullYear();

        const month =
            String(date.getMonth() + 1).padStart(2, '0');

        const day =
            String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }


    function showToast(message) {

        if (!toast) {
            return;
        }


        toastMessage.textContent =
            message;


        toast.hidden =
            false;


        if (toastTimer) {
            clearTimeout(toastTimer);
        }


        toastTimer =
            setTimeout(function () {

                toast.hidden =
                    true;

            }, 3200);
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
       EXPORT MODAL
    --------------------------------------------------------- */
    document
        .getElementById('openExportModal')
        ?.addEventListener('click', function () {

            exportModal.hidden =
                false;

            document.body.style.overflow =
                'hidden';
        });


    document
        .querySelectorAll('[data-close-export]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                exportModal.hidden =
                    true;

                document.body.style.overflow =
                    '';
            });
        });


    document
        .querySelectorAll('[data-export-type]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const type =
                    button.dataset.exportType;


                if (type === 'print') {

                    exportModal.hidden =
                        true;

                    document.body.style.overflow =
                        '';

                    printReport();

                    return;
                }


                exportModal.hidden =
                    true;

                document.body.style.overflow =
                    '';


                if (type === 'pdf') {

                    showToast(
                        'PDF export is ready for backend integration. Use Print for the frontend demo.'
                    );

                } else if (type === 'csv') {

                    showToast(
                        'CSV export is ready for backend integration.'
                    );
                }
            });
        });


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