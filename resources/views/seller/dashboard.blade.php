@extends('seller.partials.layout')

@section('title', 'Seller Dashboard')

@section('content')

    @php

        /*
|--------------------------------------------------------------------------
| SAFE DEFAULT DATA
|--------------------------------------------------------------------------
*/

        $newOrders = $newOrders ?? 0;
        $ordersToPrepare = $ordersToPrepare ?? 0;
        $lowStockCount = $lowStockCount ?? 0;

        $revenueToday = $revenueToday ?? 0;
        $revenueMonth = $revenueMonth ?? 0;

        $revenueChangePct = $revenueChangePct ?? 0;

        $recentOrders = collect($recentOrders ?? []);
        $lowStockProducts = collect($lowStockProducts ?? []);
        $topProducts = collect($topProducts ?? []);

        $weeklySales = collect($weeklySales ?? []);

        $orderPipeline = $orderPipeline ?? [
            'placed' => 0,
            'confirmed' => 0,
            'preparing' => 0,
            'ready' => 0,
            'picked_up' => 0,
            'delivery' => 0,
            'completed' => 0,
        ];

        $authSeller = auth()->user();

        $sellerName =
            $authSeller?->name ?? ($authSeller?->email ? ucfirst(explode('@', $authSeller->email)[0]) : 'Seller');

        $hour = now()->hour;

        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        $attentionCount = $newOrders + $lowStockCount;

        /*
|--------------------------------------------------------------------------
| PIPELINE STYLE
|--------------------------------------------------------------------------
*/

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
                'label' => 'Out Delivery',
                'icon' => 'map-pin',
                'color' => 'bg-sky/10 text-sky',
                'bar' => 'bg-sky',
            ],

            'completed' => [
                'label' => 'Completed',
                'icon' => 'circle-check',
                'color' => 'bg-teal-light text-teal-dark',
                'bar' => 'bg-teal',
            ],
        ];

    @endphp



    <div id="sellerDashboard">


        {{-- =========================================================
HEADER
========================================================= --}}

        <section class="mb-5">

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">


                <div>


                    <div class="flex items-center gap-2 mb-2">

                        <span class="w-2 h-2 rounded-full bg-teal animate-pulse"></span>

                        <p class="
text-[10px]
uppercase
tracking-[0.18em]
font-bold
text-teal-dark">

                            ShopHop Seller

                        </p>

                    </div>



                    <div class="flex flex-wrap items-center gap-3">


                        <h1 class="
text-2xl
font-bold
text-navy">

                            {{ $greeting }}, {{ $sellerName }}

                        </h1>



                        @if ($attentionCount > 0)
                            <span
                                class="
inline-flex
items-center
gap-1
px-2.5
py-1
rounded-full
bg-coral/10
text-coral
text-[10px]
font-semibold">

                                <x-lucide-circle-alert class="w-3 h-3" />

                                {{ $attentionCount }}
                                Need Attention

                            </span>
                        @else
                            <span
                                class="
inline-flex
items-center
gap-1
px-2.5
py-1
rounded-full
bg-teal/10
text-teal-dark
text-[10px]
font-semibold">

                                <x-lucide-circle-check class="w-3 h-3" />

                                All caught up

                            </span>
                        @endif


                    </div>



                    <p class="
mt-2
text-xs
text-navy/40">

                        Manage orders, inventory, sales, and customer activity.

                    </p>


                </div>




                <div class="flex gap-2">


                    <a href="{{ route('seller.reports') }}"
                        class="
h-9
px-4
rounded-lg
bg-white
border
border-gray-border
text-xs
font-semibold
text-navy
flex
items-center
gap-2
hover:border-teal/40
transition">


                        <x-lucide-chart-column class="w-4 h-4" />

                        Reports

                    </a>



                    <a href="{{ route('seller.inventory') }}"
                        class="
h-9
px-4
rounded-lg
bg-navy
text-white
text-xs
font-semibold
flex
items-center
gap-2
hover:bg-navy-light
transition">


                        <x-lucide-plus class="w-4 h-4" />

                        Add Product

                    </a>


                </div>


            </div>

        </section>





        {{-- =========================================================
KPI CARDS
========================================================= --}}


        <section class="mb-5">


            <div class="
grid
grid-cols-2
xl:grid-cols-4
gap-4">



                {{-- NEW ORDERS --}}

                <div class="
bg-white
border
border-gray-border
rounded-xl
p-4
hover:shadow-soft
transition">


                    <div class="
flex
justify-between
items-start">


                        <div class="
w-10
h-10
rounded-lg
bg-teal/10
text-teal-dark
flex
items-center
justify-center">


                            <x-lucide-bell-ring class="w-5 h-5" />


                        </div>



                        <span class="
text-[9px]
font-semibold
bg-teal/10
text-teal-dark
px-2
py-1
rounded-full">

                            NEW

                        </span>



                    </div>



                    <p class="
mt-4
text-2xl
font-bold
text-navy">

                        {{ number_format($newOrders) }}

                    </p>


                    <p class="
text-xs
text-navy/50">

                        New Orders

                    </p>



                </div>





                {{-- PREPARE --}}

                <div class="
bg-white
border
border-gray-border
rounded-xl
p-4
hover:shadow-soft
transition">


                    <div class="
w-10
h-10
rounded-lg
bg-coral/10
text-coral
flex
items-center
justify-center">


                        <x-lucide-box class="w-5 h-5" />

                    </div>



                    <p class="
mt-4
text-2xl
font-bold
text-navy">

                        {{ number_format($ordersToPrepare) }}

                    </p>


                    <p class="
text-xs
text-navy/50">

                        Orders To Prepare

                    </p>


                </div>





                {{-- STOCK --}}


                <div class="
bg-white
border
border-gray-border
rounded-xl
p-4
hover:shadow-soft
transition">


                    <div class="
w-10
h-10
rounded-lg
bg-yellow/20
text-amber-700
flex
items-center
justify-center">


                        <x-lucide-triangle-alert class="w-5 h-5" />

                    </div>



                    <p class="
mt-4
text-2xl
font-bold
text-navy">

                        {{ number_format($lowStockCount) }}

                    </p>


                    <p class="
text-xs
text-navy/50">

                        Low Stock

                    </p>


                </div>






                {{-- REVENUE --}}


                <div class="
bg-white
border
border-gray-border
rounded-xl
p-4
hover:shadow-soft
transition">


                    <div class="
w-10
h-10
rounded-lg
bg-sky/10
text-sky
flex
items-center
justify-center">


                        <x-lucide-wallet class="w-5 h-5" />

                    </div>



                    <p class="
mt-4
text-xl
font-bold
text-navy">

                        ₱{{ number_format($revenueMonth, 2) }}

                    </p>


                    <p class="
text-xs
text-navy/50">

                        Monthly Revenue

                    </p>


                </div>




            </div>


        </section>






        {{-- =========================================================
ACTION CENTER
========================================================= --}}


        <section class="mb-5">


            <div class="
bg-white
border
border-gray-border
rounded-xl
p-5">


                <div class="flex items-center justify-between mb-4">


                    <div>

                        <h2 class="
text-sm
font-bold
text-navy">

                            Action Center

                        </h2>


                        <p class="
text-[10px]
text-navy/40">

                            Tasks that need your attention

                        </p>


                    </div>



                    <x-lucide-zap class="
w-5
h-5
text-teal-dark" />


                </div>



                <div class="
grid
grid-cols-2
lg:grid-cols-4
gap-3">


                    <div class="
rounded-xl
bg-teal/10
p-3">


                        <p class="
text-xl
font-bold
text-teal-dark">

                            {{ $newOrders }}

                        </p>


                        <p class="
text-[10px]
text-navy/50">

                            Confirm Orders

                        </p>


                    </div>



                    <div class="
rounded-xl
bg-coral/10
p-3">


                        <p class="
text-xl
font-bold
text-coral">

                            {{ $ordersToPrepare }}

                        </p>


                        <p class="
text-[10px]
text-navy/50">

                            Pack Items

                        </p>


                    </div>




                    <div class="
rounded-xl
bg-yellow/20
p-3">


                        <p class="
text-xl
font-bold
text-amber-700">

                            {{ $lowStockCount }}

                        </p>


                        <p class="
text-[10px]
text-navy/50">

                            Restock

                        </p>


                    </div>




                    <div class="
rounded-xl
bg-sky/10
p-3">


                        <p class="
text-xl
font-bold
text-sky">

                            0

                        </p>


                        <p class="
text-[10px]
text-navy/50">

                            Unread Messages

                        </p>


                    </div>


                </div>


            </div>


        </section>
        {{-- =========================================================
ORDER PIPELINE
========================================================= --}}

        <section class="mb-5">


            <div class="
bg-white
border
border-gray-border
rounded-xl
p-5">


                <div class="
flex
items-center
justify-between
mb-5">


                    <div>

                        <h2 class="
text-sm
font-bold
text-navy">

                            Order Pipeline

                        </h2>


                        <p class="
text-[10px]
text-navy/40">

                            Track order movement from placement to completion

                        </p>

                    </div>


                    <div class="
text-[10px]
font-semibold
text-teal-dark
bg-teal/10
px-3
py-1.5
rounded-full">


                        Live Status


                    </div>


                </div>





                <div class="
grid
grid-cols-2
md:grid-cols-4
xl:grid-cols-7
gap-3">


                    @foreach ($pipelineConfig as $key => $stage)
                        <div class="
group
rounded-xl
border
border-gray-border
p-3
hover:shadow-soft
transition">


                            <div class="
flex
items-center
justify-between">


                                <div class="
w-8
h-8
rounded-lg
flex
items-center
justify-center
{{ $stage['color'] }}">


                                    <x-dynamic-component :component="'lucide-' . $stage['icon']" class="w-4 h-4" />


                                </div>



                                <span class="
text-lg
font-bold
text-navy">


                                    {{ $orderPipeline[$key] ?? 0 }}


                                </span>


                            </div>



                            <p class="
mt-3
text-[10px]
font-semibold
text-navy/60">


                                {{ $stage['label'] }}


                            </p>



                            <div class="
mt-2
h-1.5
rounded-full
bg-gray-bg
overflow-hidden">


                                <div class="
h-full
rounded-full
{{ $stage['bar'] }}"
                                    style="
width:
{{ min(100, ($orderPipeline[$key] ?? 0) * 10) }}%
">

                                </div>


                            </div>


                        </div>
                    @endforeach


                </div>



            </div>


        </section>







        {{-- =========================================================
MAIN CONTENT GRID
========================================================= --}}


        <section class="
grid
grid-cols-1
xl:grid-cols-[1.7fr_.8fr]
gap-5
mb-5">





            {{-- =====================================================
RECENT ORDERS
===================================================== --}}



            <div class="
bg-white
border
border-gray-border
rounded-xl
overflow-hidden">



                <div class="
px-5
py-4
border-b
border-gray-border
flex
items-center
justify-between">


                    <div>


                        <h2 class="
text-sm
font-bold
text-navy">

                            Recent Orders

                        </h2>


                        <p class="
text-[10px]
text-navy/40">

                            Latest buyer activity

                        </p>


                    </div>



                    <a href="{{ route('seller.orders.notifications') }}"
                        class="
text-[10px]
font-semibold
text-teal-dark
hover:text-teal
">


                        View All →

                    </a>


                </div>





                @if ($recentOrders->isEmpty())


                    <div class="
py-16
text-center">


                        <div class="
w-12
h-12
mx-auto
rounded-xl
bg-gray-bg
flex
items-center
justify-center
text-navy/30">


                            <x-lucide-package-search class="w-5 h-5" />


                        </div>


                        <p class="
mt-3
text-xs
font-semibold
text-navy/50">

                            No orders yet

                        </p>


                    </div>
                @else
                    <div class="overflow-x-auto">


                        <table class="w-full">


                            <thead>


                                <tr class="
border-b
border-gray-border">


                                    <th class="
px-5
py-3
text-left
text-[9px]
uppercase
text-navy/30">


                                        Order


                                    </th>


                                    <th class="
px-5
py-3
text-left
text-[9px]
uppercase
text-navy/30">


                                        Buyer


                                    </th>


                                    <th class="
px-5
py-3
text-left
text-[9px]
uppercase
text-navy/30">


                                        Amount


                                    </th>


                                    <th class="
px-5
py-3
text-left
text-[9px]
uppercase
text-navy/30">


                                        Status


                                    </th>


                                </tr>


                            </thead>




                            <tbody>


                                @foreach ($recentOrders as $order)
                                    <tr class="
border-b
border-gray-border
hover:bg-gray-bg/60
transition">



                                        <td class="
px-5
py-3">


                                            <p class="
text-xs
font-semibold
text-navy">


                                                #{{ $order['id'] ?? '----' }}


                                            </p>


                                            <p class="
text-[9px]
text-navy/35">


                                                {{ $order['placed_at'] ?? '' }}


                                            </p>


                                        </td>




                                        <td class="
px-5
py-3
text-xs
text-navy/70">


                                            {{ $order['buyer_name'] ?? 'Buyer' }}


                                        </td>




                                        <td class="
px-5
py-3
text-xs
font-semibold
text-navy">


                                            ₱{{ number_format($order['total'] ?? 0, 2) }}


                                        </td>




                                        <td class="px-5 py-3">


                                            @php

                                                $status = $order['status'] ?? 'PLACED';

                                            @endphp



                                            <span
                                                class="
px-2
py-1
rounded-full
text-[9px]
font-semibold
bg-teal/10
text-teal-dark">


                                                {{ str_replace('_', ' ', ucwords(strtolower($status))) }}


                                            </span>


                                        </td>


                                    </tr>
                                @endforeach



                            </tbody>


                        </table>


                    </div>



                @endif



            </div>








            {{-- =====================================================
RIGHT SIDE
===================================================== --}}



            <div class="
space-y-5">





                {{-- INVENTORY HEALTH --}}


                <div class="
bg-white
border
border-gray-border
rounded-xl
p-5">


                    <div class="
flex
items-center
justify-between">


                        <div>


                            <h2 class="
text-sm
font-bold
text-navy">

                                Inventory Health

                            </h2>


                            <p class="
text-[10px]
text-navy/40">

                                Current stock condition

                            </p>


                        </div>


                        <x-lucide-box class="
w-5
h-5
text-teal-dark" />


                    </div>





                    <div class="
mt-5
flex
items-center
gap-4">


                        <div
                            class="
relative
w-20
h-20
rounded-full
border-8
border-teal/20
flex
items-center
justify-center">


                            <span class="
text-lg
font-bold
text-navy">

                                82%

                            </span>


                        </div>



                        <div class="space-y-2">


                            <div>

                                <p class="
text-[10px]
text-navy/40">

                                    Healthy Products

                                </p>

                                <p class="
text-sm
font-bold
text-navy">

                                    {{ max(0, 150 - $lowStockCount) }}

                                </p>

                            </div>



                            <div>

                                <p class="
text-[10px]
text-navy/40">

                                    Need Restock

                                </p>

                                <p class="
text-sm
font-bold
text-coral">

                                    {{ $lowStockCount }}

                                </p>

                            </div>


                        </div>



                    </div>


                </div>






                {{-- COURIER TRACKER --}}


                <div class="
bg-white
border
border-gray-border
rounded-xl
p-5">


                    <div class="
flex
items-center
gap-2
mb-4">


                        <x-lucide-truck class="
w-5
h-5
text-sky" />


                        <h2 class="
text-sm
font-bold
text-navy">

                            Courier Pickup

                        </h2>


                    </div>



                    <div class="
space-y-3">


                        <div class="
flex
justify-between
items-center
p-3
rounded-lg
bg-sky/10">


                            <span class="
text-[10px]
text-navy/60">

                                Ready Pickup

                            </span>


                            <strong class="
text-sm
text-sky">

                                {{ $orderPipeline['ready'] ?? 0 }}

                            </strong>


                        </div>





                        <div class="
flex
justify-between
items-center
p-3
rounded-lg
bg-navy/10">


                            <span class="
text-[10px]
text-navy/60">

                                Picked Up

                            </span>


                            <strong class="
text-sm
text-navy">

                                {{ $orderPipeline['picked_up'] ?? 0 }}

                            </strong>


                        </div>



                        <div class="
flex
justify-between
items-center
p-3
rounded-lg
bg-teal/10">


                            <span class="
text-[10px]
text-navy/60">

                                Completed

                            </span>


                            <strong class="
text-sm
text-teal-dark">

                                {{ $orderPipeline['completed'] ?? 0 }}

                            </strong>


                        </div>


                    </div>


                </div>





            </div>


        </section>

        {{-- =========================================================
SALES ANALYTICS + PERFORMANCE
========================================================= --}}

        <section class="
grid
grid-cols-1
xl:grid-cols-[1.7fr_.8fr]
gap-5
mb-5">





            {{-- =====================================================
SALES OVERVIEW
===================================================== --}}


            <div class="
bg-white
border
border-gray-border
rounded-xl
p-5">


                <div class="
flex
items-center
justify-between
mb-5">


                    <div>


                        <h2 class="
text-sm
font-bold
text-navy">

                            Sales Overview

                        </h2>


                        <p class="
text-[10px]
text-navy/40">

                            Revenue performance this week

                        </p>


                    </div>



                    <div class="
px-3
py-1.5
rounded-lg
bg-gray-bg
text-[10px]
font-semibold
text-navy/50">


                        PHP ₱


                    </div>


                </div>





                <div class="
grid
grid-cols-3
gap-3
mb-6">



                    <div class="
rounded-xl
bg-gray-bg
p-3">


                        <p class="
text-[10px]
text-navy/40">

                            Today

                        </p>


                        <p class="
mt-1
text-lg
font-bold
text-navy">

                            ₱{{ number_format($revenueToday, 2) }}

                        </p>


                    </div>




                    <div class="
rounded-xl
bg-teal/10
p-3">


                        <p class="
text-[10px]
text-navy/40">

                            This Month

                        </p>


                        <p class="
mt-1
text-lg
font-bold
text-teal-dark">

                            ₱{{ number_format($revenueMonth, 2) }}

                        </p>


                    </div>




                    <div class="
rounded-xl
bg-sky/10
p-3">


                        <p class="
text-[10px]
text-navy/40">

                            Growth

                        </p>


                        <p class="
mt-1
text-lg
font-bold
text-sky">


                            {{ $revenueChangePct }}%


                        </p>


                    </div>



                </div>






                {{-- SIMPLE SALES GRAPH --}}


                @if ($weeklySales->isEmpty())


                    <div class="
h-40
flex
items-center
justify-center
text-xs
text-navy/30">

                        No sales data yet

                    </div>
                @else
                    <div class="
h-40
flex
items-end
gap-3">


                        @php

                            $maxSale = max(1, $weeklySales->max('amount'));

                        @endphp



                        @foreach ($weeklySales as $sale)
                            <div class="
flex-1
flex
flex-col
items-center
gap-2
h-full
justify-end
group">


                                <div class="
relative
w-full
rounded-t-lg
bg-teal/20
hover:bg-teal/40
transition"
                                    style="
height:
{{ max(8, (($sale['amount'] ?? 0) / $maxSale) * 100) }}%
">


                                    <div class="
absolute
bottom-0
left-0
right-0
h-1
bg-teal
rounded-t-lg">

                                    </div>


                                </div>


                                <span class="
text-[9px]
text-navy/40">


                                    {{ $sale['label'] ?? '' }}


                                </span>


                            </div>
                        @endforeach



                    </div>


                @endif



            </div>







            {{-- =====================================================
TOP PRODUCTS
===================================================== --}}



            <div class="
bg-white
border
border-gray-border
rounded-xl
p-5">


                <div class="
flex
items-center
justify-between
mb-4">


                    <div>

                        <h2 class="
text-sm
font-bold
text-navy">

                            Top Products

                        </h2>


                        <p class="
text-[10px]
text-navy/40">

                            Best performing items

                        </p>

                    </div>



                    <x-lucide-star class="
w-5
h-5
text-yellow" />


                </div>





                @if ($topProducts->isEmpty())


                    <div class="
py-10
text-center
text-xs
text-navy/30">

                        No product data

                    </div>
                @else
                    <div class="
space-y-4">


                        @foreach ($topProducts as $index => $product)
                            <div class="
flex
items-center
gap-3">


                                <div
                                    class="
w-7
h-7
rounded-lg
bg-navy/10
flex
items-center
justify-center
text-xs
font-bold
text-navy">


                                    {{ $index + 1 }}


                                </div>




                                <div class="
flex-1
min-w-0">


                                    <p class="
text-xs
font-semibold
text-navy
truncate">


                                        {{ $product['name'] ?? 'Product' }}


                                    </p>


                                    <p class="
text-[10px]
text-navy/40">


                                        {{ $product['sold'] ?? 0 }} sold


                                    </p>


                                </div>




                                <p class="
text-xs
font-bold
text-teal-dark">


                                    ₱{{ number_format($product['revenue'] ?? 0) }}


                                </p>


                            </div>
                        @endforeach


                    </div>



                @endif


            </div>




        </section>








        {{-- =========================================================
STORE HEALTH + CUSTOMER AREA
========================================================= --}}


        <section class="
grid
grid-cols-1
md:grid-cols-3
gap-5
mb-5">





            {{-- STORE PERFORMANCE --}}


            <div class="
bg-white
border
border-gray-border
rounded-xl
p-5">


                <div class="
flex
items-center
gap-2
mb-4">


                    <x-lucide-chart-no-axes-combined class="
w-5
h-5
text-teal-dark" />


                    <h2 class="
text-sm
font-bold
text-navy">

                        Store Performance

                    </h2>


                </div>




                <div class="
space-y-4">



                    <div class="
flex
justify-between">


                        <span class="
text-xs
text-navy/50">

                            Rating

                        </span>


                        <strong class="
text-sm
text-navy">

                            4.9 ⭐

                        </strong>


                    </div>



                    <div class="
h-1.5
bg-gray-bg
rounded-full">


                        <div class="
h-full
bg-teal
rounded-full" style="width:98%">
                        </div>


                    </div>






                    <div class="
flex
justify-between">


                        <span class="
text-xs
text-navy/50">

                            Response Rate

                        </span>


                        <strong class="
text-sm
text-navy">

                            98%

                        </strong>


                    </div>


                    <div class="
h-1.5
bg-gray-bg
rounded-full">


                        <div class="
h-full
bg-sky
rounded-full" style="width:98%">
                        </div>


                    </div>





                    <div class="
flex
justify-between">


                        <span class="
text-xs
text-navy/50">

                            Ship On Time

                        </span>


                        <strong class="
text-sm
text-navy">

                            96%

                        </strong>


                    </div>


                    <div class="
h-1.5
bg-gray-bg
rounded-full">


                        <div class="
h-full
bg-yellow
rounded-full" style="width:96%">
                        </div>


                    </div>



                </div>


            </div>









            {{-- CUSTOMER FEEDBACK --}}


            <div class="
bg-white
border
border-gray-border
rounded-xl
p-5">


                <div class="
flex
items-center
gap-2
mb-4">


                    <x-lucide-message-circle class="
w-5
h-5
text-sky" />


                    <h2 class="
text-sm
font-bold
text-navy">

                        Customer Feedback

                    </h2>


                </div>





                <div class="
text-center
mb-5">


                    <p class="
text-3xl
font-bold
text-navy">

                        4.9

                    </p>


                    <div class="
text-yellow
text-sm">

                        ★★★★★

                    </div>


                    <p class="
text-[10px]
text-navy/40">

                        Average Seller Rating

                    </p>


                </div>





                <div class="
space-y-3">


                    <div class="
bg-gray-bg
rounded-xl
p-3">


                        <p class="
text-xs
text-navy/70">

                            "Fast delivery and good packaging"

                        </p>


                        <p class="
mt-1
text-[9px]
text-navy/35">

                            Buyer feedback

                        </p>


                    </div>




                    <div class="
bg-gray-bg
rounded-xl
p-3">


                        <p class="
text-xs
text-navy/70">

                            "Product quality is excellent"

                        </p>


                        <p class="
mt-1
text-[9px]
text-navy/35">

                            Buyer feedback

                        </p>


                    </div>


                </div>



            </div>








            {{-- CHAT PREVIEW --}}


            <div class="
bg-white
border
border-gray-border
rounded-xl
p-5">


                <div class="
flex
items-center
justify-between
mb-4">


                    <div class="
flex
items-center
gap-2">


                        <x-lucide-messages-square class="
w-5
h-5
text-teal-dark" />


                        <h2 class="
text-sm
font-bold
text-navy">

                            Messages

                        </h2>


                    </div>



                    <span class="
text-[9px]
bg-coral/10
text-coral
px-2
py-1
rounded-full
font-semibold">


                        3 unread


                    </span>


                </div>





                <div class="
space-y-3">


                    <div class="
flex
gap-3">


                        <div class="
w-8
h-8
rounded-full
bg-teal/10
flex
items-center
justify-center">


                            <x-lucide-user class="
w-4
h-4
text-teal-dark" />


                        </div>


                        <div>


                            <p class="
text-xs
font-semibold
text-navy">

                                Juan

                            </p>


                            <p class="
text-[10px]
text-navy/40">

                                Available po ba?

                            </p>


                        </div>


                    </div>





                    <div class="
flex
gap-3">


                        <div class="
w-8
h-8
rounded-full
bg-sky/10
flex
items-center
justify-center">


                            <x-lucide-user class="
w-4
h-4
text-sky" />


                        </div>


                        <div>


                            <p class="
text-xs
font-semibold
text-navy">

                                Maria

                            </p>


                            <p class="
text-[10px]
text-navy/40">

                                When will this ship?

                            </p>


                        </div>


                    </div>



                </div>




                <a href="{{ route('seller.chat') }}"
                    class="
mt-5
block
text-center
text-xs
font-semibold
text-teal-dark">


                    Open Messages →

                </a>



            </div>




        </section>

        {{-- =========================================================
QUICK ACTION FOOTER PANEL
========================================================= --}}

        <section class="mb-5">


            <div class="
bg-navy
rounded-xl
p-5
text-white
relative
overflow-hidden">


                {{-- decorative --}}

                <div class="
absolute
-right-20
-top-20
w-60
h-60
rounded-full
bg-teal/10
blur-3xl">
                </div>




                <div class="
relative
flex
flex-col
lg:flex-row
lg:items-center
lg:justify-between
gap-5">



                    <div>


                        <p class="
text-[10px]
uppercase
tracking-[0.2em]
text-white/40
font-semibold">

                            Quick Actions

                        </p>


                        <h2 class="
mt-1
text-xl
font-bold">

                            Manage your store faster

                        </h2>


                        <p class="
mt-2
text-xs
text-white/50">

                            Access your most used seller tools.

                        </p>


                    </div>





                    <div class="
grid
grid-cols-2
sm:grid-cols-4
gap-3">



                        <a href="{{ route('seller.inventory') }}"
                            class="
px-4
py-3
rounded-xl
bg-white/10
hover:bg-white/20
transition
text-xs
font-semibold
flex
items-center
gap-2">


                            <x-lucide-package class="w-4 h-4 text-teal" />

                            Inventory


                        </a>




                        <a href="{{ route('seller.orders.notifications') }}"
                            class="
px-4
py-3
rounded-xl
bg-white/10
hover:bg-white/20
transition
text-xs
font-semibold
flex
items-center
gap-2">


                            <x-lucide-shopping-bag class="w-4 h-4 text-sky" />

                            Orders


                        </a>





                        <a href="{{ route('seller.reports') }}"
                            class="
px-4
py-3
rounded-xl
bg-white/10
hover:bg-white/20
transition
text-xs
font-semibold
flex
items-center
gap-2">


                            <x-lucide-file-chart-column class="w-4 h-4 text-yellow" />

                            Reports


                        </a>





                        <a href="{{ route('seller.chat') }}"
                            class="
px-4
py-3
rounded-xl
bg-white/10
hover:bg-white/20
transition
text-xs
font-semibold
flex
items-center
gap-2">


                            <x-lucide-messages-square class="w-4 h-4 text-coral" />

                            Chat


                        </a>



                    </div>


                </div>


            </div>


        </section>






        {{-- =========================================================
RESPONSIVE DASHBOARD POLISH
========================================================= --}}


        <style>
            #sellerDashboard {


                animation:
                    sellerFade .35s ease;


            }



            @keyframes sellerFade {


                from {

                    opacity: 0;
                    transform:
                        translateY(8px);

                }


                to {

                    opacity: 1;
                    transform:
                        translateY(0);

                }


            }




            #sellerDashboard .hover-card {


                transition:
                    transform .2s ease,
                    box-shadow .2s ease;


            }



            @media(max-width:768px) {


                #sellerDashboard h1 {


                    font-size:
                        1.25rem;


                }



                #sellerDashboard table {


                    min-width:
                        650px;


                }


            }
        </style>






        {{-- =========================================================
DASHBOARD LIVE INTERACTION SCRIPT
========================================================= --}}


        <script>
            document.addEventListener(
                'DOMContentLoaded',
                function() {



                    /*
                    |--------------------------------------------------------------------------
                    | AUTO UPDATE CLOCK
                    |--------------------------------------------------------------------------
                    */


                    const dateTargets =
                        document.querySelectorAll(
                            '[data-live-time]'
                        );



                    function updateTime() {


                        dateTargets.forEach(
                            item => {


                                item.innerText =
                                    new Date()
                                    .toLocaleTimeString();


                            });


                    }



                    setInterval(
                        updateTime,
                        1000
                    );






                    /*
                    |--------------------------------------------------------------------------
                    | CARD CLICK FEEDBACK
                    |--------------------------------------------------------------------------
                    */


                    document
                        .querySelectorAll(
                            '#sellerDashboard a'
                        )
                        .forEach(
                            link => {


                                link.addEventListener(
                                    'mouseenter',
                                    () => {


                                        link.classList.add(
                                            'scale-[1.01]'
                                        );


                                    });


                                link.addEventListener(
                                    'mouseleave',
                                    () => {


                                        link.classList.remove(
                                            'scale-[1.01]'
                                        );


                                    });


                            });


                });
        </script>




    @endsection