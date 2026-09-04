@extends('seller.partials.layout')

@section('title', 'Reports')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    */
    $dateFrom = $dateFrom ?? now()->subDays(6)->format('Y-m-d');
    $dateTo = $dateTo ?? now()->format('Y-m-d');

    $totalSales = $totalSales ?? 48250;
    $totalOrders = $totalOrders ?? 96;
    $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
    $commissionRate = $commissionRate ?? 10;
    $commissionAmount = $totalSales * ($commissionRate / 100);
    $netEarnings = $totalSales - $commissionAmount;

    $dailySales = collect($dailySales ?? [
        ['label' => 'Mon', 'amount' => 5200],
        ['label' => 'Tue', 'amount' => 6800],
        ['label' => 'Wed', 'amount' => 4100],
        ['label' => 'Thu', 'amount' => 7300],
        ['label' => 'Fri', 'amount' => 8950],
        ['label' => 'Sat', 'amount' => 9600],
        ['label' => 'Sun', 'amount' => 6300],
    ]);

    $dailySalesMax = max(1, ...($dailySales->pluck('amount')->all() ?: [1]));

    $topProducts = collect($topProducts ?? [
        ['name' => 'Barako Coffee Beans 250g', 'sold' => 58, 'revenue' => 12760],
        ['name' => 'Handwoven Rattan Basket', 'sold' => 34, 'revenue' => 15300],
        ['name' => 'Handmade Soap Bar Set', 'sold' => 41, 'revenue' => 7175],
    ]);
@endphp


<style>
    #sellerReports .dash-gap { gap: 1rem; }
    #sellerReports .dash-section { margin-bottom: 1.25rem; }
</style>


<div id="sellerReports">

    <header class="dash-section flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                Reports
            </h1>
            <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                Track your sales, commission, and performance over any date range.
            </p>
        </div>

        <button
            type="button"
            onclick="window.print()"
            class="inline-flex items-center justify-center gap-1.5
                   h-9 px-3.5 rounded-lg
                   border border-gray-border bg-white
                   text-xs font-semibold text-navy
                   hover:border-teal/40 hover:text-teal-dark
                   transition-all"
        >
            <x-lucide-download class="w-3.5 h-3.5" />
            Export Report
        </button>
    </header>


    {{-- =========================================================
        DATE RANGE
    ========================================================= --}}
    <form method="GET" action="{{ route('seller.reports') }}" class="dash-section bg-white border border-gray-border rounded-xl p-3 flex flex-wrap items-end gap-3">

        <div>
            <label class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">From</label>
            <input
                type="date" name="date_from" value="{{ $dateFrom }}"
                class="block h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs
                       focus:outline-none focus:border-teal/50"
            >
        </div>

        <div>
            <label class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">To</label>
            <input
                type="date" name="date_to" value="{{ $dateTo }}"
                class="block h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs
                       focus:outline-none focus:border-teal/50"
            >
        </div>

        <button
            type="submit"
            class="h-9 px-3.5 rounded-lg bg-navy hover:bg-navy/90 text-xs font-semibold text-white transition-colors"
        >
            Apply
        </button>
    </form>


    {{-- =========================================================
        SUMMARY CARDS
    ========================================================= --}}
    <section class="dash-section grid grid-cols-2 xl:grid-cols-4 dash-gap">

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">Total Sales</p>
            <p class="text-lg font-bold text-navy mt-1">₱{{ number_format($totalSales) }}</p>
        </div>

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">Orders</p>
            <p class="text-lg font-bold text-navy mt-1">{{ $totalOrders }}</p>
        </div>

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">Avg. Order Value</p>
            <p class="text-lg font-bold text-navy mt-1">₱{{ number_format($avgOrderValue) }}</p>
        </div>

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">Net Earnings</p>
            <p class="text-lg font-bold text-teal-dark mt-1">₱{{ number_format($netEarnings) }}</p>
            <p class="text-[9px] text-navy/35 mt-0.5">after {{ $commissionRate }}% commission</p>
        </div>

    </section>


    {{-- =========================================================
        SALES CHART + TOP PRODUCTS
    ========================================================= --}}
    <section class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.72fr)_minmax(290px,0.8fr)] dash-gap">

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-bold text-navy">Sales Trend</p>
                    <p class="text-[9px] text-navy/35 mt-0.5">Revenue for selected date range</p>
                </div>
                <span class="text-[9px] font-semibold text-navy/40 bg-gray-bg px-2 py-1 rounded-full">₱ PHP</span>
            </div>

            <div class="flex items-end justify-between gap-2 h-40">
                @foreach ($dailySales as $day)
                    @php $barPct = max(4, round(($day['amount'] / $dailySalesMax) * 100)); @endphp
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-2">
                        <div class="w-full flex-1 flex items-end">
                            <div class="w-full rounded-t-md bg-teal/15 hover:bg-teal/25 transition-colors relative group" style="height: {{ $barPct }}%">
                                <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[9px] font-semibold text-navy opacity-0 group-hover:opacity-100 transition whitespace-nowrap">
                                    ₱{{ number_format($day['amount']) }}
                                </span>
                                <div class="w-full h-1 rounded-t-md bg-teal"></div>
                            </div>
                        </div>
                        <span class="text-[9px] font-medium text-navy/40">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <p class="text-sm font-bold text-navy">Top Products</p>
            <p class="text-[9px] text-navy/35 mt-0.5 mb-3">By revenue, selected range</p>

            <div class="space-y-3">
                @foreach ($topProducts as $index => $product)
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-bold text-navy/25 w-4 shrink-0">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-semibold text-navy truncate">{{ $product['name'] }}</p>
                            <p class="text-[9px] text-navy/35 mt-0.5">{{ $product['sold'] }} sold</p>
                        </div>
                        <span class="text-[10px] font-semibold text-navy tabular-nums shrink-0">₱{{ number_format($product['revenue']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

    </section>

</div>

@endsection