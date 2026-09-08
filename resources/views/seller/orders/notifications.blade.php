@extends('seller.partials.layout')

@section('title', 'Order Notifications')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    | This page is intentionally hardcoded so the seller-side UI can be built
    | and reviewed while the backend/order workflow is still in development.
    |
    | Replace this collection with controller-provided orders later.
    */

    $orders = collect([
        [
            'id' => 'ORD-10231',
            'buyer' => 'Maricel Santos',
            'placed_at' => '10 mins ago',
            'placed_minutes' => 10,
            'notification_state' => 'unread',
            'status' => 'PLACED',
            'address' => 'Blk 4 Lot 12, Sampaguita St., Brgy. San Isidro, Calamba, Laguna',
            'delivery_area' => 'Calamba, Laguna',
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'Pending',
            'voucher_code' => 'WELCOME100',
            'voucher_discount' => 100,
            'shipping_fee' => 80,
            'note' => 'Please pack securely, pasalubong po ito.',
            'items' => [
                [
                    'name' => 'Handwoven Rattan Basket',
                    'sku' => 'RATTAN-001',
                    'variant' => 'Natural / Medium',
                    'qty' => 2,
                    'price' => 450,
                    'stock' => 8,
                ],
                [
                    'name' => 'Handmade Soap Bar Set',
                    'sku' => 'SOAP-SET-004',
                    'variant' => null,
                    'qty' => 1,
                    'price' => 175,
                    'stock' => 15,
                ],
            ],
        ],
        [
            'id' => 'ORD-10230',
            'buyer' => 'Jonas Villareal',
            'placed_at' => '42 mins ago',
            'placed_minutes' => 42,
            'notification_state' => 'unread',
            'status' => 'PLACED',
            'address' => 'Purok 3, Brgy. Halang, Calamba, Laguna',
            'delivery_area' => 'Calamba, Laguna',
            'payment_method' => 'GCash',
            'payment_status' => 'Paid',
            'voucher_code' => null,
            'voucher_discount' => 0,
            'shipping_fee' => 65,
            'note' => null,
            'items' => [
                [
                    'name' => 'Barako Coffee Beans 250g',
                    'sku' => 'COFFEE-250-011',
                    'variant' => 'Medium Roast',
                    'qty' => 3,
                    'price' => 220,
                    'stock' => 12,
                ],
            ],
        ],
        [
            'id' => 'ORD-10229',
            'buyer' => 'Alyssa Mendoza',
            'placed_at' => '1 hr ago',
            'placed_minutes' => 60,
            'notification_state' => 'read',
            'status' => 'PLACED',
            'address' => 'Brgy. Real, Santa Rosa City, Laguna',
            'delivery_area' => 'Santa Rosa, Laguna',
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'Pending',
            'voucher_code' => 'PAYDAY150',
            'voucher_discount' => 150,
            'shipping_fee' => 95,
            'note' => 'Please message me before dispatching the order.',
            'items' => [
                [
                    'name' => 'Classic Oversized Shirt',
                    'sku' => 'SHIRT-OVR-004',
                    'variant' => 'Black / Medium',
                    'qty' => 2,
                    'price' => 699,
                    'stock' => 1,
                ],
                [
                    'name' => 'Minimalist Canvas Tote Bag',
                    'sku' => 'TOTE-CNV-002',
                    'variant' => null,
                    'qty' => 1,
                    'price' => 449,
                    'stock' => 7,
                ],
            ],
        ],
        [
            'id' => 'ORD-10228',
            'buyer' => 'Carlo Reyes',
            'placed_at' => '2 hrs ago',
            'placed_minutes' => 120,
            'notification_state' => 'actioned',
            'status' => 'CONFIRMED',
            'address' => 'Brgy. Bucal, Calamba, Laguna',
            'delivery_area' => 'Calamba, Laguna',
            'payment_method' => 'GCash',
            'payment_status' => 'Paid',
            'voucher_code' => null,
            'voucher_discount' => 0,
            'shipping_fee' => 70,
            'note' => null,
            'items' => [
                [
                    'name' => 'Insulated Travel Tumbler',
                    'sku' => 'TUMBLER-003',
                    'variant' => 'Cream / 500ml',
                    'qty' => 1,
                    'price' => 599,
                    'stock' => 6,
                ],
            ],
        ],
    ]);

    $orderSubtotal = function (array $order) {
        return collect($order['items'] ?? [])
            ->sum(fn ($item) => ((int) ($item['qty'] ?? 0)) * ((float) ($item['price'] ?? 0)));
    };

    $orderTotal = function (array $order) use ($orderSubtotal) {
        return max(
            0,
            $orderSubtotal($order)
            - (float) ($order['voucher_discount'] ?? 0)
            + (float) ($order['shipping_fee'] ?? 0)
        );
    };

    $hasStockIssue = function (array $order) {
        return collect($order['items'] ?? [])->contains(function ($item) {
            return (int) ($item['stock'] ?? 0) < (int) ($item['qty'] ?? 0);
        });
    };

    $newOrders = $orders->where('status', 'PLACED')->values();
    $reviewedOrders = $orders->where('status', '!=', 'PLACED')->values();

    $totalNew = $newOrders->count();
    $totalUnread = $newOrders->where('notification_state', 'unread')->count();
@endphp

<style>
    #sellerOrderNotifications .notification-card {
        transition:
            transform .16s ease,
            box-shadow .16s ease,
            border-color .16s ease,
            opacity .16s ease;
    }

    #sellerOrderNotifications .notification-card:hover {
        transform: translateY(-1px);
    }

    #sellerOrderNotifications .order-tab-active {
        background: #0F2C3F;
        color: #ffffff;
        border-color: #0F2C3F;
    }

    #sellerOrderNotifications [hidden],
    #orderDetailsModal[hidden],
    #declineOrderModal[hidden],
    #demoToast[hidden] {
        display: none !important;
    }
</style>

<div id="sellerOrderNotifications" class="space-y-5">

    {{-- =========================================================
        HEADER
    ========================================================= --}}
    <section>
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-coral"></span>
                    <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-coral">
                        Order Management
                    </p>
                </div>

                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    Order Notifications
                </h1>

                <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                    Review incoming orders, verify stock availability, then accept them into preparation.
                </p>

                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-yellow/20 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    FRONTEND DEMO · HARDCODED DATA
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-bg text-navy/55 text-[11px] font-semibold">
                    <x-lucide-mail class="w-3.5 h-3.5" />
                    {{ $totalUnread }} unread
                </span>

                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-coral/10 text-coral text-[11px] font-semibold">
                    <x-lucide-bell-ring class="w-3.5 h-3.5" />
                    {{ $totalNew }} new order{{ $totalNew === 1 ? '' : 's' }}
                </span>
            </div>
        </div>
    </section>


    {{-- =========================================================
        TABS + FILTERS
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl p-3">
        <div class="flex flex-col xl:flex-row xl:items-center gap-3">
            <div class="flex items-center gap-1 p-1 rounded-lg bg-gray-bg w-fit overflow-x-auto">
                <button
                    type="button"
                    data-order-tab="new"
                    class="order-tab order-tab-active h-9 px-3.5 rounded-lg border border-transparent text-xs font-semibold transition whitespace-nowrap"
                >
                    New Orders
                    <span class="ml-1 opacity-60" data-new-count>{{ $totalNew }}</span>
                </button>

                <button
                    type="button"
                    data-order-tab="reviewed"
                    class="order-tab h-9 px-3.5 rounded-lg border border-transparent text-xs font-semibold text-navy/50 hover:bg-white transition whitespace-nowrap"
                >
                    Recently Reviewed
                    <span class="ml-1 opacity-60" data-reviewed-count>{{ $reviewedOrders->count() }}</span>
                </button>
            </div>

            <div class="flex-1"></div>

            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <div class="relative min-w-0 sm:min-w-[260px]">
                    <x-lucide-search class="w-4 h-4 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        id="orderSearch"
                        placeholder="Search order, buyer, or item..."
                        class="w-full h-10 pl-9 pr-3 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/30 focus:outline-none focus:border-teal/50"
                    >
                </div>

                <select
                    id="orderSort"
                    class="h-10 px-3 rounded-lg border border-gray-border text-xs text-navy bg-white focus:outline-none focus:border-teal/50"
                >
                    <option value="newest">Newest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="total-desc">Highest total</option>
                    <option value="total-asc">Lowest total</option>
                </select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-[10px] text-navy/35">
            <p>
                Showing <strong id="visibleOrderCount" class="text-navy/60">{{ $totalNew }}</strong> order(s)
            </p>
            <p>Open an order to review full delivery, payment, and stock details.</p>
        </div>
    </section>


    {{-- =========================================================
        ORDER CARDS
    ========================================================= --}}
    <section>
        <div id="ordersList" class="space-y-3">
            @foreach ($orders as $order)
                @php
                    $subtotal = $orderSubtotal($order);
                    $total = $orderTotal($order);
                    $stockIssue = $hasStockIssue($order);
                    $group = $order['status'] === 'PLACED' ? 'new' : 'reviewed';
                    $searchText = strtolower(collect([
                        $order['id'],
                        $order['buyer'],
                        $order['delivery_area'],
                        collect($order['items'])->pluck('name')->implode(' '),
                    ])->filter()->implode(' '));
                @endphp

                <article
                    class="notification-card bg-white border border-gray-border rounded-xl overflow-hidden hover:shadow-soft {{ $order['notification_state'] === 'unread' && $group === 'new' ? 'border-l-4 border-l-coral' : '' }}"
                    data-order-card
                    data-order-id="{{ $order['id'] }}"
                    data-group="{{ $group }}"
                    data-search="{{ $searchText }}"
                    data-minutes="{{ $order['placed_minutes'] }}"
                    data-total="{{ $total }}"
                    {{ $group !== 'new' ? 'hidden' : '' }}
                >
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($order['notification_state'] === 'unread' && $group === 'new')
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-coral uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-coral"></span>
                                            New
                                        </span>
                                    @endif

                                    <p class="text-sm font-bold text-navy">
                                        {{ $order['id'] }}
                                    </p>

                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full {{ $group === 'new' ? 'bg-coral/10 text-coral' : 'bg-teal/10 text-teal-dark' }}">
                                        {{ $order['status'] }}
                                    </span>

                                    @if ($stockIssue && $group === 'new')
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-full bg-red-50 text-red-600">
                                            <x-lucide-triangle-alert class="w-3 h-3" />
                                            Stock Issue
                                        </span>
                                    @elseif ($group === 'new')
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-full bg-teal/10 text-teal-dark">
                                            <x-lucide-circle-check class="w-3 h-3" />
                                            Stock Ready
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-navy/50 mt-1.5">
                                    <span class="font-semibold text-navy/70">{{ $order['buyer'] }}</span>
                                    <span class="mx-1">·</span>
                                    placed {{ $order['placed_at'] }}
                                </p>
                            </div>

                            <div class="md:text-right shrink-0">
                                <p class="text-lg sm:text-xl font-bold text-navy tabular-nums">
                                    ₱{{ number_format($total, 2) }}
                                </p>
                                <p class="text-[10px] text-navy/35 mt-0.5">
                                    {{ collect($order['items'])->sum('qty') }} item(s)
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-border space-y-2.5">
                            @foreach ($order['items'] as $item)
                                @php $itemHasIssue = (int) $item['stock'] < (int) $item['qty']; @endphp
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 text-xs">
                                    <div class="min-w-0">
                                        <p class="text-navy/75 font-medium">
                                            {{ $item['qty'] }}× {{ $item['name'] }}
                                        </p>

                                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px] text-navy/35">
                                            <span>{{ $item['sku'] }}</span>
                                            @if ($item['variant'])
                                                <span>•</span>
                                                <span>{{ $item['variant'] }}</span>
                                            @endif

                                            <span>•</span>

                                            @if ($itemHasIssue)
                                                <span class="font-semibold text-red-600">
                                                    Requested {{ $item['qty'] }} · Only {{ $item['stock'] }} available
                                                </span>
                                            @else
                                                <span class="font-semibold text-teal-dark">
                                                    {{ $item['stock'] }} available
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <span class="text-navy/55 font-semibold tabular-nums shrink-0">
                                        ₱{{ number_format($item['qty'] * $item['price'], 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-gray-bg text-[10px] font-semibold text-navy/55">
                                <x-lucide-map-pin class="w-3.5 h-3.5" />
                                {{ $order['delivery_area'] }}
                            </span>

                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-gray-bg text-[10px] font-semibold text-navy/55">
                                <x-lucide-wallet-cards class="w-3.5 h-3.5" />
                                {{ $order['payment_method'] }}
                            </span>

                            @if ($order['note'])
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-yellow/20 text-[10px] font-semibold text-amber-700">
                                    <x-lucide-message-square-text class="w-3.5 h-3.5" />
                                    Buyer left a note
                                </span>
                            @endif

                            @if ($order['voucher_code'])
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-teal/10 text-[10px] font-semibold text-teal-dark">
                                    <x-lucide-ticket-percent class="w-3.5 h-3.5" />
                                    {{ $order['voucher_code'] }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <button
                                type="button"
                                data-open-order-details
                                data-order-id="{{ $order['id'] }}"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-dark hover:text-teal transition"
                            >
                                View full details
                                <x-lucide-arrow-right class="w-3.5 h-3.5" />
                            </button>

                            @if ($group === 'new')
                                <div class="flex items-center gap-2 sm:justify-end">
                                    <button
                                        type="button"
                                        data-open-decline-modal
                                        data-order-id="{{ $order['id'] }}"
                                        class="h-9 px-3.5 rounded-lg border border-gray-border text-xs font-semibold text-navy/60 hover:bg-gray-bg transition-colors"
                                    >
                                        Decline
                                    </button>

                                    <button
                                        type="button"
                                        data-accept-order
                                        data-order-id="{{ $order['id'] }}"
                                        data-has-stock-issue="{{ $stockIssue ? '1' : '0' }}"
                                        {{ $stockIssue ? 'disabled' : '' }}
                                        title="{{ $stockIssue ? 'Resolve the stock issue before accepting this order.' : 'Accept this order and move it to preparation.' }}"
                                        class="h-9 px-4 rounded-lg bg-navy hover:bg-navy/90 text-xs font-semibold text-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        Accept &amp; Prepare
                                    </button>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-dark">
                                    <x-lucide-circle-check class="w-4 h-4" />
                                    Ready for preparation
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div id="noOrdersMatch" hidden class="bg-white border border-gray-border rounded-xl py-14 px-5 text-center">
            <div class="w-11 h-11 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                <x-lucide-package-search class="w-5 h-5" />
            </div>
            <p class="mt-3 text-sm font-semibold text-navy/55">No matching orders</p>
            <p class="mt-1 text-xs text-navy/35">Try another search or switch tabs.</p>
        </div>
    </section>

</div>


{{-- =========================================================
    ORDER DETAILS MODAL
========================================================= --}}
<div id="orderDetailsModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div data-close-order-details class="absolute inset-0 bg-navy/45"></div>

    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-xl max-h-[92vh] overflow-y-auto content-scrollbar">
        <div class="sticky top-0 z-10 bg-white flex items-start justify-between gap-4 px-5 sm:px-6 py-4 border-b border-gray-border">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-base font-bold text-navy">
                        Order <span id="detailsOrderId"></span>
                    </h2>
                    <span id="detailsStatus" class="px-2 py-1 rounded-full bg-coral/10 text-coral text-[10px] font-bold"></span>
                </div>
                <p id="detailsPlacedAt" class="text-[10px] text-navy/35 mt-1"></p>
            </div>

            <button
                type="button"
                data-close-order-details
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition-colors shrink-0"
                aria-label="Close order details"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="px-5 sm:px-6 py-5 space-y-5">

            <section class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-xl bg-gray-bg p-3.5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-navy/35">Customer</p>
                    <p id="detailsBuyer" class="text-sm font-semibold text-navy mt-1"></p>
                </div>

                <div class="rounded-xl bg-gray-bg p-3.5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-navy/35">Payment</p>
                    <p id="detailsPayment" class="text-sm font-semibold text-navy mt-1"></p>
                    <p id="detailsPaymentStatus" class="text-[10px] text-navy/40 mt-0.5"></p>
                </div>
            </section>

            <section>
                <div class="flex items-center gap-2 mb-2">
                    <x-lucide-map-pin class="w-4 h-4 text-teal-dark" />
                    <h3 class="text-sm font-bold text-navy">Delivery Address</h3>
                </div>
                <div class="rounded-xl border border-gray-border p-3.5">
                    <p id="detailsAddress" class="text-xs leading-relaxed text-navy/70"></p>
                </div>
            </section>

            <section>
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div class="flex items-center gap-2">
                        <x-lucide-package-open class="w-4 h-4 text-teal-dark" />
                        <h3 class="text-sm font-bold text-navy">Items &amp; Stock</h3>
                    </div>
                    <span id="detailsStockSummary" class="text-[10px] font-bold"></span>
                </div>

                <div id="detailsItems" class="rounded-xl border border-gray-border divide-y divide-gray-border"></div>
            </section>

            <section class="rounded-xl bg-gray-bg p-4">
                <h3 class="text-sm font-bold text-navy mb-3">Order Summary</h3>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between gap-3 text-navy/55">
                        <span>Subtotal</span>
                        <span id="detailsSubtotal" class="tabular-nums"></span>
                    </div>

                    <div id="detailsVoucherRow" class="flex items-center justify-between gap-3 text-teal-dark">
                        <span id="detailsVoucherLabel">Voucher</span>
                        <span id="detailsVoucherDiscount" class="tabular-nums"></span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-navy/55">
                        <span>Delivery Fee</span>
                        <span id="detailsShipping" class="tabular-nums"></span>
                    </div>

                    <div class="pt-3 mt-3 border-t border-gray-border flex items-center justify-between gap-3">
                        <span class="text-sm font-bold text-navy">Total</span>
                        <span id="detailsTotal" class="text-lg font-bold text-navy tabular-nums"></span>
                    </div>
                </div>
            </section>

            <section id="detailsNoteWrap">
                <div class="flex items-center gap-2 mb-2">
                    <x-lucide-message-square-text class="w-4 h-4 text-amber-700" />
                    <h3 class="text-sm font-bold text-navy">Buyer Note</h3>
                </div>
                <div class="rounded-xl bg-yellow/20 border border-yellow/30 p-3.5">
                    <p id="detailsNote" class="text-xs leading-relaxed text-navy/70"></p>
                </div>
            </section>
        </div>

        <div id="detailsActionBar" class="sticky bottom-0 bg-white px-5 sm:px-6 py-4 border-t border-gray-border flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
            <button
                type="button"
                id="detailsDeclineButton"
                class="h-10 px-4 rounded-lg border border-gray-border text-xs font-semibold text-navy/60 hover:bg-gray-bg transition-colors"
            >
                Decline Order
            </button>

            <button
                type="button"
                id="detailsAcceptButton"
                class="h-10 px-4 rounded-lg bg-navy hover:bg-navy/90 text-xs font-semibold text-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
            >
                Accept &amp; Prepare
            </button>
        </div>
    </div>
</div>


{{-- =========================================================
    DECLINE ORDER MODAL
========================================================= --}}
<div id="declineOrderModal" hidden class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div data-close-decline-modal class="absolute inset-0 bg-navy/45"></div>

    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-md">
        <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-gray-border">
            <div>
                <h2 class="text-base font-bold text-navy">Decline Order</h2>
                <p class="text-xs text-navy/40 mt-1">
                    Order <span id="declineOrderId" class="font-semibold text-navy/65"></span>
                </p>
            </div>

            <button
                type="button"
                data-close-decline-modal
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
                aria-label="Close decline modal"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="px-5 py-5">
            <p class="text-xs font-semibold text-navy mb-3">Why are you declining this order?</p>

            <div class="space-y-2" id="declineReasons">
                @foreach ([
                    'Item unavailable',
                    'Insufficient stock',
                    'Product issue',
                    'Unable to fulfill order',
                    'Other',
                ] as $reason)
                    <label class="flex items-center gap-3 rounded-xl border border-gray-border p-3 cursor-pointer hover:bg-gray-bg/60 transition">
                        <input type="radio" name="decline_reason" value="{{ $reason }}" class="accent-navy">
                        <span class="text-xs text-navy/70">{{ $reason }}</span>
                    </label>
                @endforeach
            </div>

            <div id="otherReasonWrap" hidden class="mt-3">
                <label for="otherDeclineReason" class="block text-[10px] font-bold uppercase tracking-wide text-navy/35 mb-1.5">
                    Other reason
                </label>
                <textarea
                    id="otherDeclineReason"
                    rows="3"
                    placeholder="Enter reason..."
                    class="w-full rounded-xl border border-gray-border px-3 py-2.5 text-xs text-navy placeholder:text-navy/30 focus:outline-none focus:border-teal/50 resize-none"
                ></textarea>
            </div>

            <p id="declineError" hidden class="text-[10px] text-red-600 font-semibold mt-3">
                Please choose a reason before declining the order.
            </p>
        </div>

        <div class="px-5 py-4 border-t border-gray-border flex items-center justify-end gap-2">
            <button
                type="button"
                data-close-decline-modal
                class="h-10 px-4 rounded-lg border border-gray-border text-xs font-semibold text-navy/60 hover:bg-gray-bg transition"
            >
                Cancel
            </button>

            <button
                type="button"
                id="confirmDeclineButton"
                class="h-10 px-4 rounded-lg bg-red-600 hover:bg-red-700 text-xs font-semibold text-white transition"
            >
                Decline Order
            </button>
        </div>
    </div>
</div>


{{-- DEMO TOAST --}}
<div id="demoToast" hidden class="fixed right-4 bottom-4 z-[80] w-[min(360px,calc(100vw-2rem))] rounded-xl bg-navy text-white shadow-panel px-4 py-3">
    <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
            <x-lucide-circle-check class="w-4 h-4" />
        </div>
        <div class="min-w-0">
            <p id="demoToastTitle" class="text-xs font-bold">Demo action completed</p>
            <p id="demoToastMessage" class="text-[10px] text-white/65 mt-0.5"></p>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const orders = @json($orders->values());

    const state = {
        activeTab: 'new',
        currentDetailsId: null,
        currentDeclineId: null,
    };

    const ordersList = document.getElementById('ordersList');
    const orderSearch = document.getElementById('orderSearch');
    const orderSort = document.getElementById('orderSort');
    const visibleOrderCount = document.getElementById('visibleOrderCount');
    const noOrdersMatch = document.getElementById('noOrdersMatch');
    const orderDetailsModal = document.getElementById('orderDetailsModal');
    const declineOrderModal = document.getElementById('declineOrderModal');

    function peso(value) {
        return `₱${Number(value || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    function getOrder(id) {
        return orders.find(order => order.id === id) ?? null;
    }

    function subtotal(order) {
        return (order.items ?? []).reduce((sum, item) => {
            return sum + (Number(item.qty) * Number(item.price));
        }, 0);
    }

    function total(order) {
        return Math.max(
            0,
            subtotal(order)
            - Number(order.voucher_discount || 0)
            + Number(order.shipping_fee || 0)
        );
    }

    function hasStockIssue(order) {
        return (order.items ?? []).some(item => Number(item.stock) < Number(item.qty));
    }

    function showToast(title, message) {
        const toast = document.getElementById('demoToast');
        document.getElementById('demoToastTitle').textContent = title;
        document.getElementById('demoToastMessage').textContent = message;

        toast.hidden = false;
        window.clearTimeout(showToast.timer);
        showToast.timer = window.setTimeout(() => {
            toast.hidden = true;
        }, 3200);
    }

    function updateTabCounts() {
        const newCount = orders.filter(order => order.status === 'PLACED').length;
        const reviewedCount = orders.filter(order => order.status !== 'PLACED').length;

        document.querySelector('[data-new-count]').textContent = newCount;
        document.querySelector('[data-reviewed-count]').textContent = reviewedCount;
    }

    function refreshCards() {
        const query = (orderSearch.value || '').trim().toLowerCase();
        const cards = Array.from(document.querySelectorAll('[data-order-card]'));
        const sortValue = orderSort.value;

        cards.forEach(card => {
            const order = getOrder(card.dataset.orderId);
            if (!order) return;

            card.dataset.group = order.status === 'PLACED' ? 'new' : 'reviewed';
            card.dataset.total = total(order);

            const matchesTab = card.dataset.group === state.activeTab;
            const matchesSearch = !query || card.dataset.search.includes(query);

            card.hidden = !(matchesTab && matchesSearch);
        });

        const sortedVisible = cards
            .filter(card => !card.hidden)
            .sort((a, b) => {
                if (sortValue === 'oldest') {
                    return Number(b.dataset.minutes) - Number(a.dataset.minutes);
                }

                if (sortValue === 'total-desc') {
                    return Number(b.dataset.total) - Number(a.dataset.total);
                }

                if (sortValue === 'total-asc') {
                    return Number(a.dataset.total) - Number(b.dataset.total);
                }

                return Number(a.dataset.minutes) - Number(b.dataset.minutes);
            });

        sortedVisible.forEach(card => ordersList.appendChild(card));

        visibleOrderCount.textContent = sortedVisible.length;
        noOrdersMatch.hidden = sortedVisible.length !== 0;
    }

    function setActiveTab(tab) {
        state.activeTab = tab;

        document.querySelectorAll('[data-order-tab]').forEach(button => {
            const active = button.dataset.orderTab === tab;
            button.classList.toggle('order-tab-active', active);
            button.classList.toggle('text-navy/50', !active);
            button.classList.toggle('hover:bg-white', !active);
        });

        refreshCards();
    }

    function markOrderReviewed(id, status) {
        const order = getOrder(id);
        const card = document.querySelector(`[data-order-card][data-order-id="${CSS.escape(id)}"]`);

        if (!order || !card) return;

        order.status = status;
        order.notification_state = 'actioned';
        card.dataset.group = 'reviewed';

        const statusPills = card.querySelectorAll('span');
        statusPills.forEach(span => {
            if (span.textContent.trim() === 'PLACED') {
                span.textContent = status;
                span.className = 'text-[10px] font-bold px-2 py-1 rounded-full bg-teal/10 text-teal-dark';
            }
        });

        const actionWrap = card.querySelector('[data-accept-order]')?.parentElement;
        if (actionWrap) {
            const reviewed = document.createElement('span');
            reviewed.className = 'inline-flex items-center gap-1.5 text-xs font-semibold text-teal-dark';
            reviewed.textContent = status === 'CONFIRMED'
                ? 'Accepted · Ready for preparation'
                : 'Order declined';
            actionWrap.replaceWith(reviewed);
        }

        updateTabCounts();
        refreshCards();
    }

    function openDetails(id) {
        const order = getOrder(id);
        if (!order) return;

        state.currentDetailsId = id;

        document.getElementById('detailsOrderId').textContent = order.id;
        document.getElementById('detailsStatus').textContent = order.status;
        document.getElementById('detailsPlacedAt').textContent = `Placed ${order.placed_at}`;
        document.getElementById('detailsBuyer').textContent = order.buyer;
        document.getElementById('detailsPayment').textContent = order.payment_method;
        document.getElementById('detailsPaymentStatus').textContent = `Payment status: ${order.payment_status}`;
        document.getElementById('detailsAddress').textContent = order.address;

        const itemsWrap = document.getElementById('detailsItems');
        itemsWrap.innerHTML = '';

        (order.items ?? []).forEach(item => {
            const issue = Number(item.stock) < Number(item.qty);
            const row = document.createElement('div');
            row.className = 'p-3.5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2';

            const left = document.createElement('div');
            left.className = 'min-w-0';

            const name = document.createElement('p');
            name.className = 'text-xs font-semibold text-navy';
            name.textContent = `${item.qty}× ${item.name}`;

            const meta = document.createElement('p');
            meta.className = 'text-[10px] text-navy/35 mt-0.5';
            meta.textContent = [item.sku, item.variant].filter(Boolean).join(' · ');

            const stock = document.createElement('p');
            stock.className = issue
                ? 'text-[10px] font-semibold text-red-600 mt-1'
                : 'text-[10px] font-semibold text-teal-dark mt-1';
            stock.textContent = issue
                ? `Requested ${item.qty} · Only ${item.stock} available`
                : `${item.stock} available · Stock sufficient`;

            left.append(name, meta, stock);

            const price = document.createElement('p');
            price.className = 'text-xs font-semibold text-navy/60 tabular-nums shrink-0';
            price.textContent = peso(Number(item.qty) * Number(item.price));

            row.append(left, price);
            itemsWrap.appendChild(row);
        });

        const stockIssue = hasStockIssue(order);
        const stockSummary = document.getElementById('detailsStockSummary');
        stockSummary.textContent = stockIssue ? 'Stock issue detected' : 'All items available';
        stockSummary.className = stockIssue
            ? 'text-[10px] font-bold text-red-600'
            : 'text-[10px] font-bold text-teal-dark';

        document.getElementById('detailsSubtotal').textContent = peso(subtotal(order));
        document.getElementById('detailsShipping').textContent = peso(order.shipping_fee);
        document.getElementById('detailsTotal').textContent = peso(total(order));

        const voucherRow = document.getElementById('detailsVoucherRow');
        if (Number(order.voucher_discount || 0) > 0) {
            voucherRow.hidden = false;
            document.getElementById('detailsVoucherLabel').textContent = order.voucher_code
                ? `Voucher (${order.voucher_code})`
                : 'Voucher';
            document.getElementById('detailsVoucherDiscount').textContent = `-${peso(order.voucher_discount)}`;
        } else {
            voucherRow.hidden = true;
        }

        const noteWrap = document.getElementById('detailsNoteWrap');
        if (order.note) {
            noteWrap.hidden = false;
            document.getElementById('detailsNote').textContent = order.note;
        } else {
            noteWrap.hidden = true;
        }

        const actionBar = document.getElementById('detailsActionBar');
        const acceptButton = document.getElementById('detailsAcceptButton');
        const declineButton = document.getElementById('detailsDeclineButton');

        if (order.status === 'PLACED') {
            actionBar.hidden = false;
            acceptButton.disabled = stockIssue;
            acceptButton.title = stockIssue
                ? 'Resolve the stock issue before accepting this order.'
                : 'Accept this order and move it to preparation.';
            declineButton.hidden = false;
        } else {
            actionBar.hidden = true;
        }

        orderDetailsModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeDetails() {
        orderDetailsModal.hidden = true;
        state.currentDetailsId = null;
        document.body.style.overflow = declineOrderModal.hidden ? '' : 'hidden';
    }

    function openDecline(id) {
        state.currentDeclineId = id;
        document.getElementById('declineOrderId').textContent = id;
        document.getElementById('declineError').hidden = true;
        document.getElementById('otherReasonWrap').hidden = true;
        document.getElementById('otherDeclineReason').value = '';

        document.querySelectorAll('input[name="decline_reason"]').forEach(input => {
            input.checked = false;
        });

        declineOrderModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeDecline() {
        declineOrderModal.hidden = true;
        state.currentDeclineId = null;
        document.body.style.overflow = orderDetailsModal.hidden ? '' : 'hidden';
    }

    function acceptOrder(id) {
        const order = getOrder(id);
        if (!order) return;

        if (hasStockIssue(order)) {
            showToast('Cannot accept order', 'One or more items do not have enough stock.');
            return;
        }

        markOrderReviewed(id, 'CONFIRMED');
        closeDetails();
        showToast('Order accepted', `${id} moved to the preparation stage (frontend demo only).`);
    }

    document.querySelectorAll('[data-order-tab]').forEach(button => {
        button.addEventListener('click', () => setActiveTab(button.dataset.orderTab));
    });

    orderSearch.addEventListener('input', refreshCards);
    orderSort.addEventListener('change', refreshCards);

    document.querySelectorAll('[data-open-order-details]').forEach(button => {
        button.addEventListener('click', () => openDetails(button.dataset.orderId));
    });

    document.querySelectorAll('[data-accept-order]').forEach(button => {
        button.addEventListener('click', () => acceptOrder(button.dataset.orderId));
    });

    document.querySelectorAll('[data-open-decline-modal]').forEach(button => {
        button.addEventListener('click', () => openDecline(button.dataset.orderId));
    });

    document.querySelectorAll('[data-close-order-details]').forEach(button => {
        button.addEventListener('click', closeDetails);
    });

    document.querySelectorAll('[data-close-decline-modal]').forEach(button => {
        button.addEventListener('click', closeDecline);
    });

    document.getElementById('detailsAcceptButton').addEventListener('click', function () {
        if (state.currentDetailsId) acceptOrder(state.currentDetailsId);
    });

    document.getElementById('detailsDeclineButton').addEventListener('click', function () {
        if (state.currentDetailsId) openDecline(state.currentDetailsId);
    });

    document.querySelectorAll('input[name="decline_reason"]').forEach(input => {
        input.addEventListener('change', function () {
            document.getElementById('otherReasonWrap').hidden = input.value !== 'Other';
            document.getElementById('declineError').hidden = true;
        });
    });

    document.getElementById('confirmDeclineButton').addEventListener('click', function () {
        const selected = document.querySelector('input[name="decline_reason"]:checked');
        const error = document.getElementById('declineError');

        if (!selected) {
            error.hidden = false;
            return;
        }

        let reason = selected.value;
        if (reason === 'Other') {
            reason = document.getElementById('otherDeclineReason').value.trim();
            if (!reason) {
                error.textContent = 'Please enter the reason for declining this order.';
                error.hidden = false;
                return;
            }
        }

        const id = state.currentDeclineId;
        if (!id) return;

        markOrderReviewed(id, 'DECLINED');
        closeDecline();
        closeDetails();
        showToast('Order declined', `${id} declined: ${reason} (frontend demo only).`);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        if (!declineOrderModal.hidden) {
            closeDecline();
            return;
        }

        if (!orderDetailsModal.hidden) {
            closeDetails();
        }
    });

    refreshCards();
});
</script>
@endpush

@endsection
