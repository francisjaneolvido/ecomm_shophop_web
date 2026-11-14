{{-- Path: resources/views/buyer/orders/index.blade.php --}}

@extends('layouts.app')

@section('title', 'My Orders - ShopHop')
@section('hideChrome', true)


@section('content')

@include('buyer.partials.navbar-buyer')


{{-- =========================================================
    BREADCRUMB
========================================================= --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-2">

        <nav class="flex items-center gap-1.5 text-[10px] sm:text-[10.5px] text-navy/45">

            <a
                href="{{ route('buyer.dashboard') }}"
                class="hover:text-teal-dark transition"
            >
                Home
            </a>

            <x-lucide-chevron-right class="w-3 h-3 text-navy/25" />

            <span class="text-navy font-medium">
                My Orders
            </span>

        </nav>

    </div>
</div>


{{-- =========================================================
    MY ORDERS
========================================================= --}}
<section class="shb-orders-page bg-gray-bg/75 min-h-[72vh] py-5 sm:py-6">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('status')) <p role="status" class="mb-3 rounded-xl bg-teal-light px-4 py-3 text-[11px] font-semibold text-teal-dark">{{ session('status') }}</p> @endif
        @if ($errors->any()) <p role="alert" class="mb-3 rounded-xl bg-red-50 px-4 py-3 text-[11px] font-semibold text-red-700">{{ $errors->first() }}</p> @endif

        {{-- Page header --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3 mb-4">

            <div>

                <div class="inline-flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-widest text-teal-dark mb-1.5">

                    <x-lucide-package class="w-3 h-3" />

                    Purchases

                </div>


                <p
                    role="heading"
                    aria-level="1"
                    class="text-[24px] sm:text-[28px] font-bold leading-none tracking-tight text-navy"
                >
                    My Orders
                </p>


                <p class="text-[11.5px] sm:text-[12px] text-navy/50 mt-1.5">

                    {{-- Order cards show persisted purchases; courier and report records are unavailable. --}}
                    Review your persisted purchases and their current order status.

                </p>

            </div>


            <div class="w-full lg:w-[320px]">

                <label
                    for="orderSearch"
                    class="sr-only"
                >
                    Search orders
                </label>


                <div class="relative">

                    <x-lucide-search
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-navy/30"
                    />


                    <input
                        id="orderSearch"
                        type="search"
                        placeholder="Search order, shop, or product..."
                        class="w-full h-9 rounded-xl bg-white border border-gray-border
                               pl-8.5 pr-3 text-[10.5px] text-navy placeholder:text-navy/30
                               outline-none focus:border-teal focus:ring-1 focus:ring-teal/10 transition"
                    >

                </div>

            </div>

        </div>


        {{-- Tabs --}}
        <div class="bg-white border border-gray-border rounded-2xl shadow-sm overflow-hidden mb-3">

            <div class="grid grid-flow-col auto-cols-max sm:auto-cols-fr overflow-x-auto [&::-webkit-scrollbar]:hidden">

                @foreach ([
                    ['key' => 'all', 'label' => 'All'],
                    ['key' => 'to-pay', 'label' => 'To Pay'],
                    ['key' => 'to-ship', 'label' => 'To Ship'],
                    ['key' => 'to-receive', 'label' => 'To Receive'],
                    ['key' => 'completed', 'label' => 'Completed'],
                    ['key' => 'cancelled', 'label' => 'Cancelled'],
                    {{-- Reports have no persisted endpoint, so this status tab is intentionally absent. --}}
                ] as $index => $tab)

                    <button
                        type="button"
                        class="order-tab relative min-w-23 sm:min-w-0 px-3 py-3
                               text-[10.5px] font-semibold transition
                               {{ $index === 0
                                    ? 'text-teal-dark'
                                    : 'text-navy/45 hover:text-teal-dark' }}"
                        data-order-tab="{{ $tab['key'] }}"
                    >

                        <span class="inline-flex items-center gap-1.5">

                            {{ $tab['label'] }}


                            <span
                                class="order-tab-count min-w-4 h-4 px-1 rounded-full
                                       {{ $index === 0
                                            ? 'bg-teal-light text-teal-dark'
                                            : 'bg-gray-bg text-navy/40' }}
                                       text-[8px] font-bold inline-flex items-center justify-center"
                                data-count-for="{{ $tab['key'] }}"
                            >
                                {{ $orderCounts[$tab['key']] }}
                            </span>

                        </span>


                        <span
                            class="order-tab-line absolute left-3 right-3 bottom-0 h-0.5 rounded-full bg-teal
                                   {{ $index === 0 ? '' : 'hidden' }}"
                        ></span>

                    </button>

                @endforeach

            </div>

        </div>


        {{-- Report explanation --}}
        <div class="flex items-start gap-2 bg-[#FCECEF] border border-teal/10 rounded-xl px-3 py-2.5 mb-3">

            <x-lucide-info class="w-3.5 h-3.5 text-teal-dark shrink-0 mt-0.5" />

            <p class="text-[9.5px] sm:text-[10px] leading-relaxed text-navy/55">

                Delivered order has a problem?
                Use <span class="font-semibold text-teal-dark">Report Issue</span>.
                No refund workflow is included in this version.

            </p>

        </div>


        {{-- =====================================================
            ORDER CARDS
        ====================================================== --}}
        <div id="ordersList" class="space-y-3">

            @foreach ($orders as $order)

                <article
                    class="order-card bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm"
                    {{-- Payment review stays in To Pay; later Seller preparation stays in To Ship. --}}
                    data-order-status="{{ $order['status_group'] }}"
                    data-order-original-status="{{ $order['status'] }}"
                    data-order-search="{{ strtolower(
                        $order['id'] . ' ' .
                        $order['shop'] . ' ' .
                        ($order['tracking_no'] ?? '') . ' ' .
                        ($order['courier'] ?? '') . ' ' .
                        collect($order['items'])->pluck('name')->implode(' ')
                    ) }}"
                    data-order-id="{{ $order['id'] }}"
                    data-tracking-number="{{ $order['tracking_no'] ?? '' }}"
                    data-courier="{{ $order['courier'] ?? '' }}"
                    data-rider="{{ $order['rider']['name'] ?? '' }}"
                >

                    {{-- =================================================
                        CARD HEADER
                    ================================================== --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-4 py-3 border-b border-gray-border/80">

                        <div class="flex items-center gap-2 min-w-0">

                            <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0">

                                <x-lucide-store class="w-3.5 h-3.5" />

                            </span>


                            <div class="min-w-0">

                                <div class="flex items-center gap-1.5 min-w-0">

                                    <p class="text-[11.5px] sm:text-[12px] font-bold text-navy truncate">

                                        {{ $order['shop'] }}

                                    </p>


                                    @if ($order['shop_preferred'])

                                        <span class="hidden sm:inline-flex text-[7.5px] font-bold bg-teal text-white px-1.5 py-0.5 rounded-full">

                                            Preferred

                                        </span>

                                    @endif

                                </div>


                                <p class="text-[10px] text-navy/40 mt-0.5">

                                    Order {{ $order['id'] }}

                                </p>

                            </div>

                        </div>


                        <div class="flex items-center gap-2 sm:justify-end">

                            <span
                                class="order-status-badge inline-flex items-center gap-1 px-2 py-1 rounded-full
                                       text-[9px] font-bold
                                       {{ $order['status'] === 'completed'
                                            ? 'bg-teal-light text-teal-dark'
                                            : ($order['status'] === 'cancelled'
                                                ? 'bg-red-50 text-red-500'
                                                : 'bg-gray-bg text-navy/55') }}"
                            >

                                @if ($order['status'] === 'to-receive')

                                    <x-lucide-truck class="w-2.5 h-2.5" />

                                @elseif ($order['status'] === 'to-ship')

                                    <x-lucide-package class="w-2.5 h-2.5" />

                                @elseif ($order['status'] === 'to-pay')

                                    <x-lucide-wallet class="w-2.5 h-2.5" />

                                @elseif ($order['status'] === 'completed')

                                    <x-lucide-check class="w-2.5 h-2.5" />

                                @else

                                    <x-lucide-x class="w-2.5 h-2.5" />

                                @endif


                                <span data-order-status-label>
                                    {{ $order['status_label'] }}
                                </span>

                            </span>


                            <a
                                href="{{ Route::has('buyer.store.show')
                                    ? route('buyer.store.show', $order['shop_slug'])
                                    : '#' }}"
                                class="text-[9.5px] font-semibold text-teal-dark hover:text-navy transition"
                            >
                                View Shop
                            </a>

                        </div>

                    </div>


                    {{-- =================================================
                        ITEMS
                    ================================================== --}}
                    <div class="divide-y divide-gray-border/70">

                        @foreach ($order['items'] as $item)

                            <div class="flex gap-3 px-4 py-3.5">

                                <a
                                    href="#"
                                    class="w-16 h-16 sm:w-17.5 sm:h-17.5 rounded-xl overflow-hidden
                                           bg-gray-bg border border-gray-border/80 shrink-0"
                                >

                                    <img
                                        src="{{ $item['image'] }}"
                                        alt="{{ $item['name'] }}"
                                        class="w-full h-full object-cover"
                                    >

                                </a>


                                <div class="min-w-0 flex-1">

                                    <p class="text-[11.5px] sm:text-[12.5px] leading-[1.45] font-semibold text-navy line-clamp-2">

                                        {{ $item['name'] }}

                                    </p>


                                    <p class="text-[9.5px] text-navy/40 mt-1">

                                        {{ $item['variant'] }}

                                    </p>


                                    <div class="flex flex-wrap items-center justify-between gap-2 mt-2">

                                        <span class="text-[13px] font-bold text-teal-dark">

                                            {{-- Preserve the OrderItem purchase price, including cents. --}}
                                            ₱{{ number_format($item['price'], 2) }}

                                        </span>


                                        <span class="text-[9.5px] text-navy/35">

                                            Qty {{ $item['qty'] }}

                                        </span>

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>


                    {{-- =================================================
                        STATUS NOTE
                    ================================================== --}}
                    <div
                        class="order-status-note flex items-start gap-2 px-4 py-3
                               {{ $order['status'] === 'completed'
                                    ? 'bg-[#FCECEF]'
                                    : 'bg-gray-bg/55' }}
                               border-t border-gray-border/70"
                    >

                        <x-lucide-info class="w-3.5 h-3.5 text-teal-dark shrink-0 mt-0.5" />


                        <p
                            class="text-[10.5px] sm:text-[11px] leading-relaxed text-navy/55"
                            data-order-status-note
                        >

                            {{ $order['status_note'] }}

                        </p>

                    </div>


                    {{-- =================================================
                        COMPACT PROGRESS
                    ================================================== --}}
                    <div class="px-4 py-3.5 border-t border-gray-border/70 overflow-x-auto">

                        <div class="flex items-center justify-between gap-3 mb-3">

                            <div>

                                <p class="text-[11.5px] font-bold text-navy">

                                    Order Progress

                                </p>


                                <p class="text-[10px] text-navy/40 mt-0.5">

                                    @if ($order['tracking_no'])

                                        {{ $order['courier'] }} · {{ $order['tracking_no'] }}

                                    @else

                                        Courier tracking is unavailable for this order.

                                    @endif

                                </p>

                            </div>


                            <span class="text-[10px] font-semibold text-teal-dark shrink-0">

                                {{ $order['estimated_delivery'] }}

                            </span>

                        </div>


                        <div
                            class="grid min-w-[390px]"
                            style="grid-template-columns: repeat({{ count($order['progress']) }}, minmax(0, 1fr));"
                        >

                            @foreach ($order['progress'] as $step)

                                <div class="relative text-center" @if ($step['active']) aria-current="step" @endif>

                                    @if (! $loop->last)

                                        <div
                                            class="absolute left-1/2 top-3 w-full h-0.5
                                                   {{ $step['done'] && ! $step['active']
                                                        ? 'bg-teal'
                                                        : 'bg-gray-border' }}"
                                        ></div>

                                    @endif


                                    <span
                                        class="{{ $step['active'] ? 'ring-4 ring-teal/20' : '' }} relative z-10 mx-auto w-6 h-6 rounded-full
                                               flex items-center justify-center border
                                               {{ $step['done']
                                                    ? 'bg-teal border-teal text-white'
                                                    : 'bg-white border-gray-border text-navy/25' }}"
                                    >

                                        @if (str_contains(strtolower($step['label']), 'deliver'))

                                            <x-lucide-package class="w-2.5 h-2.5" />

                                        @elseif (str_contains(strtolower($step['label']), 'ship') || str_contains(strtolower($step['label']), 'transit'))

                                            <x-lucide-truck class="w-2.5 h-2.5" />

                                        @elseif (str_contains(strtolower($step['label']), 'pay'))

                                            <x-lucide-wallet class="w-2.5 h-2.5" />

                                        @elseif (str_contains(strtolower($step['label']), 'cancel'))

                                            <x-lucide-x class="w-2.5 h-2.5" />

                                        @else

                                            <x-lucide-check class="w-2.5 h-2.5" />

                                        @endif

                                    </span>


                                    <p
                                        class="mt-1.5 text-[9px] min-[430px]:text-[9.5px] sm:text-[10px]
                                               font-medium leading-tight
                                               {{ $step['done']
                                                    ? 'text-navy/65'
                                                    : 'text-navy/25' }}"
                                    >

                                        {{ $step['label'] }}

                                    </p>

                                </div>

                            @endforeach

                        </div>

                    </div>


                    {{-- =================================================
                        DETAILED TRACKING PANEL
                    ================================================== --}}
                    <div
                        id="tracking-{{ $loop->index }}"
                        class="tracking-panel hidden border-t border-gray-border/80 bg-gray-bg/35"
                    >

                        <div class="grid lg:grid-cols-[minmax(0,1fr)_310px] gap-0">

                            @include('buyer.orders.partials.parcel-journey', ['order' => $order])

                            {{-- Courier / rider / proof --}}
                            <aside class="p-4 bg-white/75">

                                {{-- Courier --}}
                                <div>

                                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-teal-dark">

                                        Delivery Information

                                    </p>


                                    <div class="mt-3 space-y-2.5">

                                        <div class="flex items-start gap-2.5">

                                            <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0">

                                                <x-lucide-truck class="w-3.5 h-3.5" />

                                            </span>


                                            <div>

                                                <p class="text-[9px] text-navy/40">
                                                    Courier
                                                </p>

                                                <p class="text-[11px] font-semibold text-navy mt-0.5">

                                            {{-- A shipping method does not imply a persisted courier assignment. --}}
                                            {{ $order['courier'] ?? 'Courier data unavailable' }}

                                                </p>

                                            </div>

                                        </div>


                                        @if ($order['tracking_no'])

                                            <div class="flex items-start gap-2.5">

                                                <span class="w-7 h-7 rounded-lg bg-gray-bg text-navy/45 flex items-center justify-center shrink-0">

                                                    <x-lucide-package class="w-3.5 h-3.5" />

                                                </span>


                                                <div class="min-w-0">

                                                    <p class="text-[9px] text-navy/40">
                                                        Tracking Number
                                                    </p>

                                                    <p class="text-[10.5px] font-semibold text-navy mt-0.5 break-all">

                                                        {{ $order['tracking_no'] }}

                                                    </p>

                                                </div>

                                            </div>

                                        @endif

                                    </div>

                                </div>


                                <div class="mt-4 pt-4 border-t border-gray-border text-xs text-navy/70">
                                    <p class="font-semibold text-navy">{{ $order['delivery_status_label'] }}</p>
                                    @if ($order['delivered_at'])
                                        <p class="mt-1">Delivered: {{ $order['delivered_at'] }}</p>
                                    @endif
                                </div>
                                {{-- Only the final delivery handler is labelled Delivery Rider. --}}
                                @if ($order['rider'])
                                    <div class="mt-4 pt-4 border-t border-gray-border">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-teal-dark">Delivery Rider</p>
                                        <div class="mt-3 flex items-start gap-3">
                                            <span class="w-10 h-10 rounded-full bg-navy text-white flex items-center justify-center shrink-0">
                                                <x-lucide-user class="w-4 h-4" />
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-bold text-navy">{{ $order['rider']['name'] }}</p>
                                                <p class="text-xs text-navy/60 mt-1">{{ $order['rider']['vehicle'] }}</p>
                                                <p class="text-xs text-teal-dark mt-1">{{ $order['rider']['status'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <p class="mt-4 text-xs text-navy/60">No final delivery Rider recorded for the current stage.</p>
                                @endif

                                {{-- Proof of delivery --}}
                                @if ($order['delivery_proof'])

                                    <div class="mt-4 pt-4 border-t border-gray-border">

                                        <div class="flex items-center gap-1.5">

                                            <x-lucide-camera class="w-3.5 h-3.5 text-teal-dark" />


                                            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-teal-dark">

                                                Proof of Delivery

                                            </p>

                                        </div>


                                        <button
                                            type="button"
                                            class="proof-photo-btn group relative block w-full aspect-4/3
                                                   mt-3 rounded-xl overflow-hidden
                                                   bg-gray-bg border border-gray-border"
                                            data-proof-image="{{ $order['delivery_proof']['photo'] }}"
                                            data-proof-order="{{ $order['id'] }}"
                                        >

                                            <img
                                                src="{{ $order['delivery_proof']['photo'] }}"
                                                alt="Proof of delivery for {{ $order['id'] }}"
                                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                                            >


                                            <span
                                                class="absolute inset-0 bg-navy/0 group-hover:bg-navy/20
                                                       transition flex items-center justify-center"
                                            >

                                                <span
                                                    class="opacity-0 group-hover:opacity-100
                                                           w-8 h-8 rounded-full bg-white/95
                                                           text-teal-dark shadow
                                                           flex items-center justify-center transition"
                                                >

                                                    <x-lucide-search class="w-3.5 h-3.5" />

                                                </span>

                                            </span>

                                        </button>


                                        <div class="mt-2.5 space-y-1.5">

                                            <div class="flex items-start justify-between gap-2">

                                                <span class="text-[9px] text-navy/40">
                                                    Delivered
                                                </span>

                                                <span class="text-[9.5px] font-medium text-navy/65 text-right">
                                                    {{ $order['delivery_proof']['delivered_at'] }}
                                                </span>

                                            </div>


                                            <div class="flex items-start justify-between gap-2">

                                                {{-- A Rider photo does not establish recipient acknowledgement. --}}
                                                <span class="text-[9px] text-navy/40">
                                                    Recipient acknowledgement
                                                </span>

                                                <span class="text-[9.5px] font-medium text-navy/65 text-right">
                                                    {{ $order['delivery_proof']['received_by'] }}
                                                </span>

                                            </div>


                                            <div class="flex items-start justify-between gap-2">

                                                <span class="text-[9px] text-navy/40">
                                                    Photo by
                                                </span>

                                                <span class="text-[9.5px] font-medium text-navy/65 text-right">
                                                    {{ $order['delivery_proof']['uploaded_by'] }}
                                                </span>

                                            </div>

                                        </div>


                                        <p class="text-[9px] leading-relaxed text-navy/45 mt-2">

                                            {{ $order['delivery_proof']['delivery_note'] }}

                                        </p>


                                        <p class="text-[8.5px] leading-relaxed text-amber-600 bg-amber-50 rounded-lg px-2 py-1.5 mt-2">

                                            {{-- Private proof is a persisted Rider photo; no recipient acknowledgement is claimed. --}}
                                            Rider-submitted delivery photo.

                                        </p>

                                    </div>

                                @endif

                                @if ($order['can_confirm_receipt'])
                                    <form method="POST" action="{{ route('buyer.orders.confirm-receipt', $order['raw_id']) }}" class="mt-4">
                                        @csrf
                                        <button class="w-full h-9 rounded-xl bg-teal text-white text-[10px] font-bold hover:bg-teal-dark transition">
                                            Confirm Order Received
                                        </button>
                                        <p class="mt-1.5 text-[8.5px] leading-relaxed text-navy/45">Confirm only after you have received and checked the parcel.</p>
                                    </form>
                                @endif

                            </aside>

                        </div>

                    </div>


                    {{-- =================================================
                        ORDER FOOTER
                    ================================================== --}}
                    <div class="px-4 py-3.5 border-t border-gray-border/80">

                        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3">

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-5 gap-y-2">

                                <div>

                                    <p class="text-[9.5px] text-navy/40">
                                        Placed
                                    </p>

                                    <p class="text-[10.5px] font-medium text-navy/65 mt-0.5">
                                        {{ $order['placed_at'] }}
                                    </p>

                                </div>


                                <div>

                                    <p class="text-[9.5px] text-navy/40">
                                        Payment
                                    </p>

                                    <p class="text-[10.5px] font-medium text-navy/65 mt-0.5">
                                        {{ $order['payment'] }}
                                    </p>
                                    {{-- A Buyer can return to the same private group payment without placing Commerce again. --}}
                                    @if ($order['payment_url'])
                                        <a class="text-[10.5px] font-semibold text-teal-dark underline" href="{{ $order['payment_url'] }}">View payment</a>
                                    @endif

                                </div>


                                <div>

                                    <p class="text-[9.5px] text-navy/40">
                                        Shipping
                                    </p>

                                    <p class="text-[10.5px] font-medium text-navy/65 mt-0.5">
                                        {{ $order['shipping'] }}
                                    </p>

                                </div>


                                <div>

                                    <p class="text-[9.5px] text-navy/40">
                                        Order Total
                                    </p>

                                    <p class="text-[12.5px] font-bold text-teal-dark mt-0.5">
                                        {{-- Display the persisted order total without rounding away cents. --}}
                                        ₱{{ number_format($order['total'], 2) }}
                                    </p>

                                </div>

                            </div>


                            <div class="flex flex-wrap items-center gap-2 lg:justify-end">

                                    {{-- This panel shows the recorded order event; courier tracking is unavailable. --}}
                                <button
                                    type="button"
                                    class="tracking-toggle-btn h-8 px-3 rounded-lg
                                           bg-teal-light text-teal-dark
                                           hover:bg-teal hover:text-white
                                           text-[9.5px] font-semibold transition
                                           inline-flex items-center gap-1.5"
                                    data-tracking-target="tracking-{{ $loop->index }}"
                                    aria-expanded="false"
                                >

                                    <x-lucide-map-pin class="w-3 h-3" />

                                    <span data-tracking-label>
                                            Order Details
                                    </span>

                                </button>


                                {{-- Payment, contact, repeat-purchase, and report actions have no route or persistence contract. --}}

                            </div>

                        </div>

                    </div>

                </article>

            @endforeach

        </div>


        {{-- No results --}}
        <div
            id="ordersEmptyState"
            class="hidden bg-white border border-gray-border rounded-2xl py-14 px-4 text-center shadow-sm"
        >

            <div class="w-11 h-11 rounded-xl bg-teal-light text-teal-dark flex items-center justify-center mx-auto">

                <x-lucide-package class="w-5 h-5" />

            </div>


            <p class="text-[12px] font-bold text-navy mt-3">

                No orders found

            </p>


            <p class="text-[9.5px] text-navy/40 mt-1">

                Try another status or search term.

            </p>

        </div>

    </div>

</section>


{{-- Issue reports and delivery proof have no persisted backend contract, so their modals are unavailable. --}}

{{-- Toast --}}
<div
    id="ordersToast"
    class="fixed left-1/2 bottom-5 z-110
           -translate-x-1/2 translate-y-6 opacity-0 pointer-events-none
           bg-navy text-white text-[10px] font-medium
           px-3.5 py-2.5 rounded-xl shadow-xl transition-all duration-300"
></div>


@include('partials.footer')

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const tabs =
        document.querySelectorAll('[data-order-tab]');

    const orderCards =
        Array.from(document.querySelectorAll('.order-card'));

    const orderSearch =
        document.getElementById('orderSearch');

    const emptyState =
        document.getElementById('ordersEmptyState');

    const ordersToast =
        document.getElementById('ordersToast');


    let activeTab = 'all';


    /*
    |--------------------------------------------------------------------------
    | TOAST
    |--------------------------------------------------------------------------
    */

    function showToast(message) {

        if (!ordersToast) {
            return;
        }


        ordersToast.textContent =
            message;


        ordersToast.classList.remove(
            'translate-y-6',
            'opacity-0'
        );


        ordersToast.classList.add(
            'translate-y-0',
            'opacity-100'
        );


        clearTimeout(
            showToast.timer
        );


        showToast.timer =
            setTimeout(function () {

                ordersToast.classList.remove(
                    'translate-y-0',
                    'opacity-100'
                );


                ordersToast.classList.add(
                    'translate-y-6',
                    'opacity-0'
                );

            }, 2400);
    }


    /*
    |--------------------------------------------------------------------------
    | TABS / SEARCH
    |--------------------------------------------------------------------------
    */

    function updateTabCounts() {

        const statuses = [
            'all',
            'to-pay',
            'to-ship',
            'to-receive',
            'completed',
            // Only persisted order statuses participate in filtering; report simulation was removed.
            'cancelled'
        ];


        statuses.forEach(function (status) {

            const count =
                status === 'all'
                    ? orderCards.length
                    : orderCards.filter(function (card) {
                        return card.dataset.orderStatus === status;
                    }).length;


            const el =
                document.querySelector(
                    '[data-count-for="' + status + '"]'
                );


            if (el) {
                el.textContent = count;
            }
        });
    }


    function filterOrders() {

        const query =
            (orderSearch?.value || '')
                .trim()
                .toLowerCase();


        let visibleCount = 0;


        orderCards.forEach(function (card) {

            const matchesStatus =
                activeTab === 'all' ||
                card.dataset.orderStatus === activeTab;


            const matchesSearch =
                !query ||
                (card.dataset.orderSearch || '')
                    .includes(query);


            const visible =
                matchesStatus &&
                matchesSearch;


            card.classList.toggle(
                'hidden',
                !visible
            );


            if (visible) {
                visibleCount++;
            }
        });


        emptyState?.classList.toggle(
            'hidden',
            visibleCount !== 0
        );
    }


    tabs.forEach(function (tab) {

        tab.addEventListener('click', function () {

            activeTab =
                tab.dataset.orderTab;


            tabs.forEach(function (item) {

                item.classList.remove(
                    'text-teal-dark'
                );


                item.classList.add(
                    'text-navy/45'
                );


                item.querySelector('.order-tab-line')
                    ?.classList.add('hidden');


                const count =
                    item.querySelector('.order-tab-count');


                count?.classList.remove(
                    'bg-teal-light',
                    'text-teal-dark'
                );


                count?.classList.add(
                    'bg-gray-bg',
                    'text-navy/40'
                );
            });


            tab.classList.remove(
                'text-navy/45'
            );


            tab.classList.add(
                'text-teal-dark'
            );


            tab.querySelector('.order-tab-line')
                ?.classList.remove('hidden');


            const activeCount =
                tab.querySelector('.order-tab-count');


            activeCount?.classList.remove(
                'bg-gray-bg',
                'text-navy/40'
            );


            activeCount?.classList.add(
                'bg-teal-light',
                'text-teal-dark'
            );


            filterOrders();
        });
    });


    orderSearch?.addEventListener(
        'input',
        filterOrders
    );


    /*
    |--------------------------------------------------------------------------
    | DETAILED TRACKING TOGGLE
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.tracking-toggle-btn')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const targetId =
                    button.dataset.trackingTarget;


                const panel =
                    document.getElementById(targetId);


                if (!panel) {
                    return;
                }


                const willOpen =
                    panel.classList.contains('hidden');


                /*
                 * Keep only one tracking panel open at a time.
                 */
                document.querySelectorAll('.tracking-panel')
                    .forEach(function (otherPanel) {

                        if (otherPanel !== panel) {
                            otherPanel.classList.add('hidden');
                        }
                    });


                document.querySelectorAll('.tracking-toggle-btn')
                    .forEach(function (otherButton) {

                        if (otherButton !== button) {

                            otherButton.setAttribute(
                                'aria-expanded',
                                'false'
                            );


                            const otherLabel =
                                otherButton.querySelector(
                                    '[data-tracking-label]'
                                );


                            if (otherLabel) {
                                // Closing another order panel restores its truthful details label.
                                otherLabel.textContent =
                                    'Order Details';
                            }
                        }
                    });


                panel.classList.toggle(
                    'hidden',
                    !willOpen
                );


                button.setAttribute(
                    'aria-expanded',
                    String(willOpen)
                );


                const label =
                    button.querySelector(
                        '[data-tracking-label]'
                    );


                if (label) {

                    // The panel is order history until real courier events exist.
                    label.textContent =
                        willOpen
                            ? 'Hide Details'
                            : 'Order Details';

                }


                if (willOpen) {

                    setTimeout(function () {

                        panel.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });

                    }, 40);
                }
            });
        });


    /*
    |--------------------------------------------------------------------------
    | COPY TRACKING NUMBER
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.copy-tracking-btn')
        .forEach(function (button) {

            button.addEventListener('click', async function () {

                const trackingNumber =
                    button.dataset.copyTracking || '';


                if (!trackingNumber) {
                    return;
                }


                try {

                    await navigator.clipboard.writeText(
                        trackingNumber
                    );


                    showToast(
                        'Tracking number copied.'
                    );

                } catch (error) {

                    const input = document.createElement('textarea');
                    input.value = trackingNumber;
                    input.setAttribute('readonly', '');
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();
                    let copied = false;
                    try { copied = document.execCommand('copy'); } finally { input.remove(); button.focus(); }
                    showToast(copied ? 'Tracking number copied.' : 'Copy unavailable. Tracking number: ' + trackingNumber);
                }
            });
        });


    // No proof or report modal handlers exist until those records and actions are persisted.

    updateTabCounts();
    filterOrders();

});
</script>
@endpush
