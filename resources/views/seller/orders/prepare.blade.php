@extends('seller.partials.layout')

@section('title', 'Prepare Orders')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    | This page is intentionally hardcoded for UI/UX development.
    | Your backend teammate can later replace this collection with controller
    | data and connect status changes, packed states, waybills, and pickup flow.
    */

    $orders = collect($orders ?? [
        [
            'id' => 'ORD-10229',
            'buyer' => 'Ella Marasigan',
            'placed_at' => 'Today, 10:42 AM',
            'status' => 'CONFIRMED',
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'Pending',
            'address' => 'Blk 7 Lot 14, Brgy. Real, Calamba, Laguna',
            'delivery_area' => 'Calamba, Laguna',
            'note' => 'Please wrap the lamp securely. It is a gift.',
            'subtotal' => 1650,
            'shipping_fee' => 85,
            'discount' => 100,
            'total' => 1635,
            'parcel_type' => 'Box',
            'estimated_weight' => '1.8 kg',
            'dimensions' => '32 × 24 × 20 cm',
            'label_attached' => false,
            'items' => [
                [
                    'name' => 'Capiz Shell Wall Lamp',
                    'variant' => 'Warm White / Medium',
                    'sku' => 'CSL-WW-M',
                    'qty' => 1,
                    'price' => 1200,
                    'packed' => false,
                    'image' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=240&q=80',
                ],
                [
                    'name' => 'Handwoven Rattan Basket',
                    'variant' => 'Natural / Medium',
                    'sku' => 'RATTAN-NAT-M',
                    'qty' => 1,
                    'price' => 450,
                    'packed' => false,
                    'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=240&q=80',
                ],
            ],
        ],
        [
            'id' => 'ORD-10227',
            'buyer' => 'Kim Delos Reyes',
            'placed_at' => 'Today, 9:18 AM',
            'status' => 'PREPARING',
            'payment_method' => 'GCash',
            'payment_status' => 'Paid',
            'address' => 'Purok 3, Brgy. Halang, Calamba, Laguna',
            'delivery_area' => 'Calamba, Laguna',
            'note' => null,
            'subtotal' => 440,
            'shipping_fee' => 65,
            'discount' => 0,
            'total' => 505,
            'parcel_type' => 'Pouch',
            'estimated_weight' => '0.6 kg',
            'dimensions' => '24 × 18 × 8 cm',
            'label_attached' => true,
            'items' => [
                [
                    'name' => 'Barako Coffee Beans 250g',
                    'variant' => 'Dark Roast',
                    'sku' => 'BARAKO-250-DR',
                    'qty' => 2,
                    'price' => 220,
                    'packed' => true,
                    'image' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=240&q=80',
                ],
            ],
        ],
        [
            'id' => 'ORD-10225',
            'buyer' => 'Paolo Mendoza',
            'placed_at' => 'Yesterday, 4:35 PM',
            'status' => 'PREPARING',
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'Pending',
            'address' => 'Brgy. Bucal, Calamba, Laguna',
            'delivery_area' => 'Calamba, Laguna',
            'note' => 'Please do not include a printed receipt inside the parcel.',
            'subtotal' => 1098,
            'shipping_fee' => 75,
            'discount' => 50,
            'total' => 1123,
            'parcel_type' => 'Box',
            'estimated_weight' => '1.1 kg',
            'dimensions' => '28 × 20 × 16 cm',
            'label_attached' => false,
            'items' => [
                [
                    'name' => 'Daily Glow Face Serum',
                    'variant' => '30ml',
                    'sku' => 'SERUM-30ML',
                    'qty' => 2,
                    'price' => 549,
                    'packed' => true,
                    'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=240&q=80',
                ],
            ],
        ],
    ]);

    $statusClasses = [
        'CONFIRMED' => 'bg-sky/10 text-sky',
        'PREPARING' => 'bg-yellow/20 text-amber-700',
        'READY_FOR_PICKUP' => 'bg-teal/10 text-teal-dark',
    ];

    $confirmedCount = $orders->where('status', 'CONFIRMED')->count();
    $preparingCount = $orders->where('status', 'PREPARING')->count();
    $totalItemLines = $orders->sum(fn ($order) => count($order['items'] ?? []));
@endphp


<style>
    #sellerPrepareOrders .prepare-card {
        transition:
            transform .16s ease,
            box-shadow .16s ease,
            border-color .16s ease;
    }

    #sellerPrepareOrders .prepare-card:hover {
        transform: translateY(-1px);
    }

    #sellerPrepareOrders [hidden],
    #orderDetailsModal[hidden],
    #waybillModal[hidden],
    #readyModal[hidden] {
        display: none !important;
    }

    .prepare-tab-active {
        background: #0F2C3F;
        color: #ffffff;
        border-color: #0F2C3F;
    }

    .pack-checkbox,
    .label-checkbox {
        accent-color: #2ECFA6;
    }
</style>


<div id="sellerPrepareOrders" class="space-y-5">

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <section>
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
            <div class="min-w-0">

                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    Prepare Orders
                </h1>

                <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                    Verify each item, pack the parcel, print and attach the shipping label,
                    then mark the order ready for rider pickup.
                </p>

                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-yellow/20 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    FRONTEND DEMO · HARDCODED DATA
                </div>
            </div>
        </div>
    </section>


    {{-- =========================================================
        SUMMARY CARDS
    ========================================================= --}}
    <section class="grid grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4">
        <button
            type="button"
            data-summary-filter="confirmed"
            class="prepare-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-sky/25"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-sky/10 text-sky flex items-center justify-center">
                    <x-lucide-circle-check-big class="w-5 h-5" />
                </div>
                <span class="text-[10px] font-bold text-sky/60">CONFIRMED</span>
            </div>
            <p class="mt-4 text-2xl font-bold text-navy">{{ $confirmedCount }}</p>
            <p class="text-xs text-navy/45">Waiting to be packed</p>
        </button>

        <button
            type="button"
            data-summary-filter="preparing"
            class="prepare-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-yellow/30"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow/20 text-amber-700 flex items-center justify-center">
                    <x-lucide-package-open class="w-5 h-5" />
                </div>
                <span class="text-[10px] font-bold text-amber-700/65">IN PROGRESS</span>
            </div>
            <p class="mt-4 text-2xl font-bold text-navy">{{ $preparingCount }}</p>
            <p class="text-xs text-navy/45">Currently preparing</p>
        </button>

        <div class="prepare-card col-span-2 xl:col-span-1 bg-white border border-gray-border rounded-xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center">
                    <x-lucide-list-checks class="w-5 h-5" />
                </div>
                <span class="text-[10px] font-bold text-navy/35">ITEM LINES</span>
            </div>
            <p class="mt-4 text-2xl font-bold text-navy">{{ $totalItemLines }}</p>
            <p class="text-xs text-navy/45">Product lines to verify</p>
        </div>
    </section>


    {{-- =========================================================
        FILTERS
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl p-3">
        <div class="flex flex-col xl:flex-row xl:items-center gap-3">

            <div class="flex items-center gap-1 overflow-x-auto">
                @foreach ([
                    ['key' => 'all', 'label' => 'All', 'count' => $orders->count()],
                    ['key' => 'confirmed', 'label' => 'Confirmed', 'count' => $confirmedCount],
                    ['key' => 'preparing', 'label' => 'Preparing', 'count' => $preparingCount],
                ] as $tab)
                    <button
                        type="button"
                        data-prepare-tab="{{ $tab['key'] }}"
                        class="prepare-tab h-9 px-3.5 rounded-lg border border-transparent text-xs font-semibold whitespace-nowrap transition
                            {{ $tab['key'] === 'all' ? 'prepare-tab-active' : 'text-navy/50 hover:bg-gray-bg' }}"
                    >
                        {{ $tab['label'] }}
                        <span class="ml-1 opacity-60">{{ $tab['count'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="relative flex-1 min-w-0 xl:ml-auto xl:max-w-md">
                <x-lucide-search class="w-4 h-4 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    id="prepareSearch"
                    placeholder="Search order, buyer, product or SKU..."
                    class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/30 focus:outline-none focus:border-teal/50"
                >
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between gap-3">
            <p class="text-[10px] text-navy/35">
                Showing <strong id="prepareVisibleCount" class="text-navy/60">{{ $orders->count() }}</strong>
                of {{ $orders->count() }} orders
            </p>

            <button
                type="button"
                id="prepareClearFilters"
                class="text-[11px] font-semibold text-navy/45 hover:text-teal-dark transition"
            >
                Clear filters
            </button>
        </div>
    </section>


    {{-- =========================================================
        ORDER CARDS
    ========================================================= --}}
    @if ($orders->isEmpty())

        <section class="bg-white border border-gray-border rounded-xl py-14 text-center">
            <div class="w-12 h-12 mx-auto rounded-xl bg-teal-light text-teal-dark flex items-center justify-center">
                <x-lucide-box class="w-5 h-5" />
            </div>

            <p class="text-sm font-semibold text-navy/55 mt-3">Nothing to prepare</p>
            <p class="text-xs text-navy/35 mt-1">Accepted orders will appear here for packing.</p>
        </section>

    @else

        <section id="prepareCards" class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            @foreach ($orders as $order)
                @php
                    $packedCount = collect($order['items'])->where('packed', true)->count();
                    $itemCount = count($order['items']);
                    $progress = $itemCount > 0 ? round(($packedCount / $itemCount) * 100) : 0;

                    $orderSearchText = strtolower(
                        $order['id'] . ' ' .
                        $order['buyer'] . ' ' .
                        collect($order['items'])->map(fn ($item) =>
                            $item['name'] . ' ' . ($item['variant'] ?? '') . ' ' . ($item['sku'] ?? '')
                        )->implode(' ')
                    );

                    $orderJson = json_encode($order, JSON_HEX_APOS | JSON_HEX_QUOT);
                @endphp

                <article
                    class="prepare-card bg-white border border-gray-border rounded-xl overflow-hidden"
                    data-prepare-card
                    data-status="{{ strtolower($order['status']) }}"
                    data-search="{{ $orderSearchText }}"
                    data-order='{{ $orderJson }}'
                >
                    {{-- Order header --}}
                    <div class="px-4 sm:px-5 py-4 border-b border-gray-border">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-bold text-navy">
                                        {{ $order['id'] }}
                                    </p>

                                    <span
                                        data-status-badge
                                        class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $statusClasses[$order['status']] ?? 'bg-navy/10 text-navy/50' }}"
                                    >
                                        {{ str_replace('_', ' ', $order['status']) }}
                                    </span>
                                </div>

                                <p class="text-xs text-navy/50 mt-1">
                                    {{ $order['buyer'] }}
                                    <span class="text-navy/25">·</span>
                                    {{ $order['placed_at'] }}
                                </p>

                                <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-navy/40">
                                    <span class="inline-flex items-center gap-1">
                                        <x-lucide-map-pin class="w-3.5 h-3.5" />
                                        {{ $order['delivery_area'] }}
                                    </span>

                                    <span class="inline-flex items-center gap-1">
                                        <x-lucide-credit-card class="w-3.5 h-3.5" />
                                        {{ $order['payment_method'] }}
                                    </span>

                                    @if ($order['note'])
                                        <span class="inline-flex items-center gap-1 text-amber-700">
                                            <x-lucide-message-square-text class="w-3.5 h-3.5" />
                                            Buyer note
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-bold text-navy tabular-nums">
                                    ₱{{ number_format($order['total'], 2) }}
                                </p>
                                <p class="text-[10px] text-navy/35 mt-0.5">
                                    {{ $itemCount }} item line{{ $itemCount === 1 ? '' : 's' }}
                                </p>
                            </div>
                        </div>
                    </div>


                    {{-- Packing progress --}}
                    <div class="px-4 sm:px-5 py-4 bg-gray-bg/35 border-b border-gray-border">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold text-navy">
                                    Packing Progress
                                </p>
                                <p class="text-[10px] text-navy/40 mt-0.5">
                                    Check each item after it is verified and packed.
                                </p>
                            </div>

                            <p class="text-xs font-bold text-navy tabular-nums">
                                <span data-packed-count>{{ $packedCount }}</span>
                                /
                                <span data-item-count>{{ $itemCount }}</span>
                            </p>
                        </div>

                        <div class="mt-3 h-2 rounded-full bg-white border border-gray-border overflow-hidden">
                            <div
                                data-progress-bar
                                class="h-full rounded-full bg-teal transition-all duration-200"
                                style="width: {{ $progress }}%"
                            ></div>
                        </div>

                        <p class="mt-1.5 text-[10px] font-semibold text-teal-dark">
                            <span data-progress-percent>{{ $progress }}</span>% packed
                        </p>
                    </div>


                    {{-- Item checklist --}}
                    <div class="px-4 sm:px-5 py-4 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-navy/35">
                                Packing Checklist
                            </p>

                            <span class="text-[10px] text-navy/30">
                                Verify photo, variant & SKU
                            </span>
                        </div>

                        @foreach ($order['items'] as $index => $item)
                            <label
                                class="block rounded-xl border border-gray-border p-3 cursor-pointer hover:border-teal/30 hover:bg-teal/5 transition"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="w-16 h-16 rounded-xl bg-gray-bg border border-gray-border overflow-hidden shrink-0 flex items-center justify-center">
                                        @if (!empty($item['image']))
                                            <img
                                                src="{{ $item['image'] }}"
                                                alt="{{ $item['name'] }}"
                                                class="w-full h-full object-cover"
                                                loading="lazy"
                                            >
                                        @else
                                            <x-lucide-package class="w-5 h-5 text-navy/25" />
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-navy leading-snug">
                                                    {{ $item['name'] }}
                                                </p>

                                                <p class="text-[11px] text-navy/45 mt-1">
                                                    {{ $item['variant'] ?? 'Default variant' }}
                                                </p>

                                                <p class="text-[10px] text-navy/35 mt-0.5">
                                                    SKU: {{ $item['sku'] ?? '—' }}
                                                </p>
                                            </div>

                                            <div class="text-right shrink-0">
                                                <p class="text-xs font-bold text-navy">
                                                    ×{{ $item['qty'] }}
                                                </p>
                                                <p class="text-[10px] text-navy/35 mt-0.5">
                                                    ₱{{ number_format($item['price'], 2) }} each
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                class="pack-checkbox w-4 h-4 rounded border-gray-border focus:ring-teal/40"
                                                data-pack-checkbox
                                                {{ $item['packed'] ? 'checked' : '' }}
                                            >
                                            <span class="text-[11px] font-semibold text-navy/60">
                                                Item verified and packed
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>


                    {{-- Buyer note --}}
                    @if ($order['note'])
                        <div class="mx-4 sm:mx-5 mb-4 rounded-xl bg-yellow/15 border border-yellow/30 p-3">
                            <div class="flex items-start gap-2.5">
                                <x-lucide-message-square-text class="w-4 h-4 text-amber-700 mt-0.5 shrink-0" />
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-amber-700">
                                        Buyer Note
                                    </p>
                                    <p class="text-xs text-navy/65 mt-1 leading-relaxed">
                                        “{{ $order['note'] }}”
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif


                    {{-- Shipping label requirement --}}
                    <div class="mx-4 sm:mx-5 mb-4 rounded-xl border border-gray-border p-3 bg-gray-bg/35">
                        <div class="flex items-start gap-3">
                            <input
                                type="checkbox"
                                class="label-checkbox w-4 h-4 mt-0.5 rounded border-gray-border focus:ring-teal/40"
                                data-label-checkbox
                                {{ $order['label_attached'] ? 'checked' : '' }}
                            >

                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-navy">
                                    Shipping label printed and attached
                                </p>
                                <p class="text-[10px] text-navy/40 mt-0.5">
                                    Required before this order can be marked ready for pickup.
                                </p>
                            </div>

                            <x-lucide-sticker class="w-4 h-4 text-navy/30 shrink-0" />
                        </div>
                    </div>


                    {{-- Actions --}}
                    <div class="px-4 sm:px-5 py-4 border-t border-gray-border bg-gray-bg/25">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    data-view-order
                                    class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-border bg-white text-xs font-semibold text-navy/60 hover:bg-gray-bg hover:text-navy transition"
                                >
                                    <x-lucide-eye class="w-4 h-4" />
                                    View Order
                                </button>

                                <button
                                    type="button"
                                    data-open-waybill
                                    class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-border bg-white text-xs font-semibold text-navy/60 hover:bg-gray-bg hover:text-navy transition"
                                >
                                    <x-lucide-printer class="w-4 h-4" />
                                    Print Waybill
                                </button>
                            </div>

                            <button
                                type="button"
                                data-mark-ready
                                class="h-9 px-4 rounded-lg bg-navy hover:bg-navy/90 text-xs font-semibold text-white transition disabled:opacity-40 disabled:cursor-not-allowed"
                                {{ ($packedCount < $itemCount || !$order['label_attached']) ? 'disabled' : '' }}
                            >
                                Mark Ready for Pickup
                            </button>
                        </div>

                        <p
                            data-ready-helper
                            class="mt-2 text-[10px] text-navy/35 sm:text-right"
                        >
                            Complete all packing checks and attach the label first.
                        </p>
                    </div>

                </article>
            @endforeach

        </section>


        {{-- No filtered results --}}
        <section id="prepareNoResults" hidden class="bg-white border border-gray-border rounded-xl py-12 text-center">
            <div class="w-11 h-11 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                <x-lucide-search-x class="w-5 h-5" />
            </div>
            <p class="text-sm font-semibold text-navy/55 mt-3">No matching orders</p>
            <p class="text-xs text-navy/35 mt-1">Try another search or status filter.</p>
        </section>

    @endif

</div>


{{-- =========================================================
    ORDER DETAILS MODAL
========================================================= --}}
<div id="orderDetailsModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div data-close-order-modal class="absolute inset-0 bg-navy/45"></div>

    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-xl max-h-[90vh] overflow-y-auto content-scrollbar">
        <div class="sticky top-0 z-10 bg-white flex items-center justify-between px-5 py-4 border-b border-gray-border">
            <div>
                <p class="text-base font-bold text-navy">
                    Order Review
                </p>
                <p class="text-[11px] text-navy/40 mt-0.5" id="detailsOrderId"></p>
            </div>

            <button
                type="button"
                data-close-order-modal
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="px-5 py-5 space-y-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">Buyer</p>
                    <p class="text-xs font-semibold text-navy mt-1" id="detailsBuyer"></p>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">Payment</p>
                    <p class="text-xs text-navy/70 mt-1" id="detailsPayment"></p>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">Delivery Address</p>
                <p class="text-xs text-navy/70 mt-1 leading-relaxed" id="detailsAddress"></p>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">Items</p>
                    <span class="text-[10px] text-navy/30">Packing reference</span>
                </div>

                <div id="detailsItems" class="mt-2 space-y-2"></div>
            </div>

            <div id="detailsNoteWrap">
                <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">Buyer Note</p>
                <div class="mt-1 rounded-xl bg-yellow/15 border border-yellow/30 p-3">
                    <p class="text-xs text-navy/70 leading-relaxed" id="detailsNote"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-xl bg-gray-bg p-3">
                    <p class="text-[10px] text-navy/35">Parcel Type</p>
                    <p class="text-xs font-semibold text-navy mt-1" id="detailsParcelType"></p>
                </div>

                <div class="rounded-xl bg-gray-bg p-3">
                    <p class="text-[10px] text-navy/35">Est. Weight</p>
                    <p class="text-xs font-semibold text-navy mt-1" id="detailsWeight"></p>
                </div>

                <div class="rounded-xl bg-gray-bg p-3">
                    <p class="text-[10px] text-navy/35">Dimensions</p>
                    <p class="text-xs font-semibold text-navy mt-1" id="detailsDimensions"></p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-border overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-border bg-gray-bg/40">
                    <p class="text-xs font-bold text-navy">Order Summary</p>
                </div>

                <div class="px-4 py-3 space-y-2 text-xs">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-navy/45">Subtotal</span>
                        <span class="font-semibold text-navy" id="detailsSubtotal"></span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-navy/45">Discount</span>
                        <span class="font-semibold text-teal-dark" id="detailsDiscount"></span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-navy/45">Shipping Fee</span>
                        <span class="font-semibold text-navy" id="detailsShipping"></span>
                    </div>

                    <div class="pt-2 border-t border-gray-border flex items-center justify-between gap-3">
                        <span class="font-bold text-navy">Total</span>
                        <span class="text-sm font-bold text-navy" id="detailsTotal"></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


{{-- =========================================================
    WAYBILL MODAL
========================================================= --}}
<div id="waybillModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div data-close-waybill class="absolute inset-0 bg-navy/45"></div>

    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-lg max-h-[90vh] overflow-y-auto content-scrollbar">
        <div class="sticky top-0 z-10 bg-white flex items-center justify-between px-5 py-4 border-b border-gray-border">
            <div>
                <p class="text-base font-bold text-navy">Shipping Waybill</p>
                <p class="text-[11px] text-navy/40 mt-0.5">
                    Preview before printing
                </p>
            </div>

            <button
                type="button"
                data-close-waybill
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="p-5">
            <div id="waybillPreview" class="border-2 border-dashed border-navy/20 rounded-xl p-4 bg-white">
                <div class="flex items-start justify-between gap-3 pb-3 border-b border-gray-border">
                    <div>
                        <p class="text-lg font-black text-navy tracking-tight">ShopHop</p>
                        <p class="text-[10px] uppercase tracking-[0.15em] text-navy/35">Shipping Label</p>
                    </div>

                    <div class="text-right">
                        <p class="text-[10px] text-navy/35">ORDER</p>
                        <p class="text-sm font-black text-navy" id="waybillOrderId"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 py-4 border-b border-gray-border">
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wide text-navy/35">From</p>
                        <p class="text-xs font-bold text-navy mt-1">My Store</p>
                        <p class="text-[10px] text-navy/50 mt-1">Seller Fulfillment</p>
                    </div>

                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wide text-navy/35">Deliver To</p>
                        <p class="text-xs font-bold text-navy mt-1" id="waybillBuyer"></p>
                        <p class="text-[10px] text-navy/55 mt-1 leading-relaxed" id="waybillAddress"></p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 py-4 border-b border-gray-border">
                    <div>
                        <p class="text-[9px] text-navy/35">Payment</p>
                        <p class="text-[10px] font-bold text-navy mt-1" id="waybillPayment"></p>
                    </div>

                    <div>
                        <p class="text-[9px] text-navy/35">Weight</p>
                        <p class="text-[10px] font-bold text-navy mt-1" id="waybillWeight"></p>
                    </div>

                    <div>
                        <p class="text-[9px] text-navy/35">Parcel</p>
                        <p class="text-[10px] font-bold text-navy mt-1" id="waybillParcel"></p>
                    </div>
                </div>

                <div class="py-4">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[9px] text-navy/35">Items</p>
                            <p class="text-xs font-bold text-navy mt-1" id="waybillItemCount"></p>
                        </div>

                        <div class="text-right">
                            <p class="text-[9px] text-navy/35">Order Total</p>
                            <p class="text-base font-black text-navy mt-1" id="waybillTotal"></p>
                        </div>
                    </div>

                    <div class="mt-4 h-14 rounded-lg bg-gray-bg flex items-center justify-center border border-gray-border">
                        <div class="text-center">
                            <x-lucide-barcode class="w-24 h-7 mx-auto text-navy" />
                            <p class="text-[8px] tracking-[0.18em] text-navy/45 mt-1" id="waybillBarcodeText"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2">
                <button
                    type="button"
                    data-close-waybill
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Close
                </button>

                <button
                    type="button"
                    id="printWaybillButton"
                    class="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                >
                    <x-lucide-printer class="w-4 h-4" />
                    Print Waybill
                </button>
            </div>
        </div>
    </div>
</div>


{{-- =========================================================
    READY FOR PICKUP CONFIRMATION
========================================================= --}}
<div id="readyModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div data-close-ready class="absolute inset-0 bg-navy/45"></div>

    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-md">
        <div class="p-5">
            <div class="w-11 h-11 rounded-xl bg-teal/10 text-teal-dark flex items-center justify-center">
                <x-lucide-package-check class="w-5 h-5" />
            </div>

            <h2 class="text-base font-bold text-navy mt-4">
                Mark ready for pickup?
            </h2>

            <p class="text-xs text-navy/50 mt-2 leading-relaxed">
                Confirm that every product is packed and the shipping label is securely attached.
                The order will move to <strong class="text-navy">READY FOR PICKUP</strong>.
            </p>

            <div class="mt-4 rounded-xl bg-gray-bg px-3 py-2.5">
                <p class="text-[10px] text-navy/35">Order</p>
                <p class="text-sm font-bold text-navy mt-0.5" id="readyOrderId"></p>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
                <button
                    type="button"
                    data-close-ready
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    id="confirmReadyButton"
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                >
                    Confirm Ready
                </button>
            </div>
        </div>
    </div>
</div>


{{-- =========================================================
    DEMO TOAST
========================================================= --}}
<div
    id="prepareToast"
    hidden
    class="fixed right-4 bottom-4 z-[60] max-w-sm rounded-xl border border-teal/25 bg-white shadow-panel px-4 py-3"
>
    <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
            <x-lucide-circle-check class="w-4 h-4" />
        </div>

        <div>
            <p class="text-xs font-bold text-navy">Order updated</p>
            <p id="prepareToastMessage" class="text-[11px] text-navy/45 mt-0.5"></p>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cards = Array.from(document.querySelectorAll('[data-prepare-card]'));
    const searchInput = document.getElementById('prepareSearch');
    const tabs = Array.from(document.querySelectorAll('[data-prepare-tab]'));
    const visibleCount = document.getElementById('prepareVisibleCount');
    const noResults = document.getElementById('prepareNoResults');

    const orderModal = document.getElementById('orderDetailsModal');
    const waybillModal = document.getElementById('waybillModal');
    const readyModal = document.getElementById('readyModal');

    const toast = document.getElementById('prepareToast');
    const toastMessage = document.getElementById('prepareToastMessage');

    let activeFilter = 'all';
    let activeCard = null;
    let activeOrder = null;
    let toastTimer = null;

    function money(value) {
        return '₱' + Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function showToast(message) {
        if (!toast) return;

        toastMessage.textContent = message;
        toast.hidden = false;

        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.hidden = true;
        }, 3200);
    }

    function setBodyLock(locked) {
        document.body.style.overflow = locked ? 'hidden' : '';
    }

    function parseCardOrder(card) {
        try {
            return JSON.parse(card.getAttribute('data-order') || '{}');
        } catch (e) {
            return {};
        }
    }

    function refreshCard(card) {
        const checkboxes = Array.from(card.querySelectorAll('[data-pack-checkbox]'));
        const labelCheckbox = card.querySelector('[data-label-checkbox]');
        const readyButton = card.querySelector('[data-mark-ready]');
        const helper = card.querySelector('[data-ready-helper]');
        const packedCountEl = card.querySelector('[data-packed-count]');
        const itemCountEl = card.querySelector('[data-item-count]');
        const progressBar = card.querySelector('[data-progress-bar]');
        const progressPercentEl = card.querySelector('[data-progress-percent]');
        const statusBadge = card.querySelector('[data-status-badge]');

        const packedCount = checkboxes.filter(cb => cb.checked).length;
        const total = checkboxes.length;
        const allPacked = total > 0 && packedCount === total;
        const labelAttached = !!labelCheckbox?.checked;
        const canReady = allPacked && labelAttached;
        const progress = total > 0 ? Math.round((packedCount / total) * 100) : 0;

        if (packedCountEl) packedCountEl.textContent = packedCount;
        if (itemCountEl) itemCountEl.textContent = total;
        if (progressBar) progressBar.style.width = progress + '%';
        if (progressPercentEl) progressPercentEl.textContent = progress;
        if (readyButton) readyButton.disabled = !canReady;

        if (helper) {
            if (!allPacked && !labelAttached) {
                helper.textContent = 'Pack all items and attach the shipping label first.';
            } else if (!allPacked) {
                helper.textContent = 'Finish packing all items before marking this order ready.';
            } else if (!labelAttached) {
                helper.textContent = 'Attach the printed shipping label before marking this order ready.';
            } else {
                helper.textContent = 'Everything is complete. This order can be marked ready for pickup.';
                helper.classList.add('text-teal-dark');
            }
        }

        /*
         * Frontend demo behavior:
         * Once the seller starts packing a CONFIRMED order,
         * visually move it into PREPARING.
         */
        if (packedCount > 0 && card.dataset.status === 'confirmed') {
            card.dataset.status = 'preparing';

            if (statusBadge) {
                statusBadge.textContent = 'PREPARING';
                statusBadge.className = 'text-[10px] font-bold px-2.5 py-1 rounded-full bg-yellow/20 text-amber-700';
            }

            const order = parseCardOrder(card);
            order.status = 'PREPARING';
            card.setAttribute('data-order', JSON.stringify(order));
        }
    }

    cards.forEach(function (card) {
        const checkboxes = Array.from(card.querySelectorAll('[data-pack-checkbox]'));
        const labelCheckbox = card.querySelector('[data-label-checkbox]');

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                refreshCard(card);
                applyFilters();
            });
        });

        labelCheckbox?.addEventListener('change', () => refreshCard(card));

        refreshCard(card);
    });


    /* ---------------------------------------------------------
       FILTERING
    --------------------------------------------------------- */
    function applyFilters() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let shown = 0;

        cards.forEach(function (card) {
            const matchesStatus =
                activeFilter === 'all' ||
                card.dataset.status === activeFilter;

            const matchesSearch =
                !query ||
                (card.dataset.search || '').includes(query);

            const show = matchesStatus && matchesSearch;
            card.hidden = !show;

            if (show) shown++;
        });

        if (visibleCount) visibleCount.textContent = shown;
        if (noResults) noResults.hidden = shown !== 0;

        tabs.forEach(function (tab) {
            const active = tab.dataset.prepareTab === activeFilter;

            tab.classList.toggle('prepare-tab-active', active);
            tab.classList.toggle('text-navy/50', !active);
            tab.classList.toggle('hover:bg-gray-bg', !active);
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activeFilter = tab.dataset.prepareTab || 'all';
            applyFilters();
        });
    });

    document.querySelectorAll('[data-summary-filter]').forEach(function (button) {
        button.addEventListener('click', function () {
            activeFilter = button.dataset.summaryFilter || 'all';
            applyFilters();
        });
    });

    searchInput?.addEventListener('input', applyFilters);

    document.getElementById('prepareClearFilters')?.addEventListener('click', function () {
        activeFilter = 'all';
        if (searchInput) searchInput.value = '';
        applyFilters();
    });


    /* ---------------------------------------------------------
       ORDER DETAILS
    --------------------------------------------------------- */
    function openOrderDetails(card) {
        const order = parseCardOrder(card);

        document.getElementById('detailsOrderId').textContent =
            order.id + ' · ' + String(order.status || '').replaceAll('_', ' ');

        document.getElementById('detailsBuyer').textContent = order.buyer || '';
        document.getElementById('detailsPayment').textContent =
            (order.payment_method || '') + ' · ' + (order.payment_status || '');

        document.getElementById('detailsAddress').textContent = order.address || '';

        document.getElementById('detailsParcelType').textContent = order.parcel_type || '—';
        document.getElementById('detailsWeight').textContent = order.estimated_weight || '—';
        document.getElementById('detailsDimensions').textContent = order.dimensions || '—';

        document.getElementById('detailsSubtotal').textContent = money(order.subtotal);
        document.getElementById('detailsDiscount').textContent =
            Number(order.discount || 0) > 0 ? '-' + money(order.discount) : money(0);

        document.getElementById('detailsShipping').textContent = money(order.shipping_fee);
        document.getElementById('detailsTotal').textContent = money(order.total);

        const noteWrap = document.getElementById('detailsNoteWrap');
        if (order.note) {
            noteWrap.hidden = false;
            document.getElementById('detailsNote').textContent = order.note;
        } else {
            noteWrap.hidden = true;
        }

        const itemsWrap = document.getElementById('detailsItems');
        itemsWrap.innerHTML = '';

        (order.items || []).forEach(function (item) {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 rounded-xl border border-gray-border p-3';

            row.innerHTML = `
                <div class="w-12 h-12 rounded-lg bg-gray-bg border border-gray-border overflow-hidden shrink-0">
                    ${item.image
                        ? `<img src="${item.image}" alt="" class="w-full h-full object-cover">`
                        : `<div class="w-full h-full flex items-center justify-center text-[10px] text-navy/30">No image</div>`
                    }
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-navy">${item.name || ''}</p>
                    <p class="text-[10px] text-navy/40 mt-0.5">${item.variant || 'Default variant'}</p>
                    <p class="text-[10px] text-navy/30 mt-0.5">SKU: ${item.sku || '—'}</p>
                </div>

                <div class="text-right shrink-0">
                    <p class="text-xs font-bold text-navy">×${item.qty || 0}</p>
                    <p class="text-[10px] text-navy/40 mt-0.5">${money(item.price)}</p>
                </div>
            `;

            itemsWrap.appendChild(row);
        });

        orderModal.hidden = false;
        setBodyLock(true);
    }

    document.querySelectorAll('[data-view-order]').forEach(function (button) {
        button.addEventListener('click', function () {
            openOrderDetails(button.closest('[data-prepare-card]'));
        });
    });

    document.querySelectorAll('[data-close-order-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            orderModal.hidden = true;
            setBodyLock(false);
        });
    });


    /* ---------------------------------------------------------
       WAYBILL
    --------------------------------------------------------- */
    function openWaybill(card) {
        activeCard = card;
        activeOrder = parseCardOrder(card);

        const itemQty = (activeOrder.items || [])
            .reduce((sum, item) => sum + Number(item.qty || 0), 0);

        document.getElementById('waybillOrderId').textContent = activeOrder.id || '';
        document.getElementById('waybillBuyer').textContent = activeOrder.buyer || '';
        document.getElementById('waybillAddress').textContent = activeOrder.address || '';
        document.getElementById('waybillPayment').textContent = activeOrder.payment_method || '';
        document.getElementById('waybillWeight').textContent = activeOrder.estimated_weight || '—';
        document.getElementById('waybillParcel').textContent = activeOrder.parcel_type || '—';
        document.getElementById('waybillItemCount').textContent =
            itemQty + ' item' + (itemQty === 1 ? '' : 's');

        document.getElementById('waybillTotal').textContent = money(activeOrder.total);
        document.getElementById('waybillBarcodeText').textContent =
            String(activeOrder.id || '').replaceAll('-', '');

        waybillModal.hidden = false;
        setBodyLock(true);
    }

    document.querySelectorAll('[data-open-waybill]').forEach(function (button) {
        button.addEventListener('click', function () {
            openWaybill(button.closest('[data-prepare-card]'));
        });
    });

    document.querySelectorAll('[data-close-waybill]').forEach(function (button) {
        button.addEventListener('click', function () {
            waybillModal.hidden = true;
            setBodyLock(false);
        });
    });

    document.getElementById('printWaybillButton')?.addEventListener('click', function () {
        const preview = document.getElementById('waybillPreview');
        if (!preview) return;

        const printWindow = window.open('', '_blank', 'width=760,height=900');

        if (!printWindow) {
            alert('Please allow pop-ups to print the waybill.');
            return;
        }

        printWindow.document.write(`
            <!doctype html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Waybill ${activeOrder?.id || ''}</title>
                <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        margin: 24px;
                        color: #0F2C3F;
                    }
                    * { box-sizing: border-box; }
                    img { max-width: 100%; }
                    button { display: none !important; }
                    .rounded-xl, .rounded-lg { border-radius: 8px; }
                    .border, .border-2 { border: 1px solid #dfe5e8; }
                    .border-dashed { border-style: dashed; }
                    .border-b { border-bottom: 1px solid #dfe5e8; }
                    .p-4 { padding: 16px; }
                    .py-4 { padding-top: 16px; padding-bottom: 16px; }
                    .pb-3 { padding-bottom: 12px; }
                    .mt-1 { margin-top: 4px; }
                    .mt-4 { margin-top: 16px; }
                    .grid { display: grid; }
                    .grid-cols-2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
                    .grid-cols-3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
                    .gap-3 { gap: 12px; }
                    .gap-4 { gap: 16px; }
                    .flex { display: flex; }
                    .items-start { align-items: flex-start; }
                    .items-end { align-items: flex-end; }
                    .justify-between { justify-content: space-between; }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .font-bold, .font-black { font-weight: 700; }
                    .text-lg { font-size: 18px; }
                    .text-base { font-size: 16px; }
                    .text-sm { font-size: 14px; }
                    .text-xs { font-size: 12px; }
                    .text-\\[10px\\] { font-size: 10px; }
                    .text-\\[9px\\] { font-size: 9px; }
                    .text-\\[8px\\] { font-size: 8px; }
                    .uppercase { text-transform: uppercase; }
                    .tracking-tight { letter-spacing: -0.02em; }
                    .tracking-wide { letter-spacing: .05em; }
                    .h-14 { height: 56px; }
                    @page { margin: 12mm; }
                </style>
            </head>
            <body>
                ${preview.outerHTML}
                <script>
                    window.onload = function () {
                        window.print();
                        window.onafterprint = function () { window.close(); };
                    };
                <\/script>
            </body>
            </html>
        `);

        printWindow.document.close();

        showToast('Waybill opened for printing. Attach it to the parcel after printing.');
    });


    /* ---------------------------------------------------------
       READY FOR PICKUP
    --------------------------------------------------------- */
    document.querySelectorAll('[data-mark-ready]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.disabled) return;

            activeCard = button.closest('[data-prepare-card]');
            activeOrder = parseCardOrder(activeCard);

            document.getElementById('readyOrderId').textContent = activeOrder.id || '';

            readyModal.hidden = false;
            setBodyLock(true);
        });
    });

    document.querySelectorAll('[data-close-ready]').forEach(function (button) {
        button.addEventListener('click', function () {
            readyModal.hidden = true;
            setBodyLock(false);
        });
    });

    document.getElementById('confirmReadyButton')?.addEventListener('click', function () {
        if (!activeCard) return;

        activeCard.dataset.status = 'ready_for_pickup';

        const badge = activeCard.querySelector('[data-status-badge]');
        if (badge) {
            badge.textContent = 'READY FOR PICKUP';
            badge.className = 'text-[10px] font-bold px-2.5 py-1 rounded-full bg-teal/10 text-teal-dark';
        }

        const order = parseCardOrder(activeCard);
        order.status = 'READY_FOR_PICKUP';
        activeCard.setAttribute('data-order', JSON.stringify(order));

        const readyButton = activeCard.querySelector('[data-mark-ready]');
        if (readyButton) {
            readyButton.disabled = true;
            readyButton.textContent = 'Ready for Pickup';
        }

        const helper = activeCard.querySelector('[data-ready-helper]');
        if (helper) {
            helper.textContent = 'Ready for rider pickup. Backend can move this order to the courier handover module.';
            helper.classList.add('text-teal-dark');
        }

        readyModal.hidden = true;
        setBodyLock(false);

        showToast((order.id || 'Order') + ' is now ready for pickup.');
        applyFilters();
    });


    /* ---------------------------------------------------------
       ESCAPE KEY
    --------------------------------------------------------- */
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        if (!orderModal.hidden) {
            orderModal.hidden = true;
            setBodyLock(false);
        }

        if (!waybillModal.hidden) {
            waybillModal.hidden = true;
            setBodyLock(false);
        }

        if (!readyModal.hidden) {
            readyModal.hidden = true;
            setBodyLock(false);
        }
    });

    applyFilters();
});
</script>
@endpush

@endsection
