{{-- Path: resources/views/buyer/orders/index.blade.php --}}

@extends('layouts.app')

@php
    /*
    |--------------------------------------------------------------------------
    | TEMPORARY ORDER PREVIEW DATA
    |--------------------------------------------------------------------------
    | Replace this collection with real Order models later.
    |
    | Important:
    | - No refund flow is included.
    | - Delivered orders use "Report Issue" only.
    | - Courier/rider/tracking/proof-of-delivery values below are demo data.
    | - proof_photo should later come from the courier/rider delivery upload.
    */

    $orders = collect([

        /*
        |--------------------------------------------------------------------------
        | TO RECEIVE — WITH ACTIVE COURIER / RIDER TRACKING
        |--------------------------------------------------------------------------
        */
        [
            'id' => 'SHP-2026-00125',
            'status' => 'to-receive',
            'status_label' => 'To Receive',
            'status_note' => 'Your parcel is out for delivery and should arrive today.',
            'shop' => 'ShopHop Tech Store',
            'shop_slug' => 'shophop-tech-store',
            'shop_preferred' => true,

            'placed_at' => 'Sep 8, 2026 · 9:32 AM',
            'payment' => 'Cash on Delivery',
            'shipping' => 'Standard Delivery',

            'tracking_no' => 'SPXPH026492018',
            'courier' => 'SPX Express',
            'estimated_delivery' => 'Sep 11 · Today',

            'rider' => [
                'name' => 'Carlo M.',
                'phone' => '09•• ••• ••82',
                'vehicle' => 'Motorcycle',
                'plate' => 'NCR ••••',
                'status' => 'Out for delivery',
            ],

            'total' => 1299,
            'shipping_fee' => 58,
            'voucher_discount' => 100,

            'items' => [
                [
                    'id' => 1,
                    'name' => 'Wireless Earbuds Pro with ENC Noise Reduction & Charging Case',
                    'image' => 'images/hero/earbuds.jpg',
                    'variant' => 'Black · Earbuds Only',
                    'price' => 1299,
                    'qty' => 1,
                ],
            ],

            'progress' => [
                ['label' => 'Order Placed', 'done' => true],
                ['label' => 'Confirmed', 'done' => true],
                ['label' => 'Shipped', 'done' => true],
                ['label' => 'In Transit', 'done' => true],
                ['label' => 'Delivered', 'done' => false],
            ],

            'tracking_events' => [
                [
                    'type' => 'current',
                    'title' => 'Out for delivery',
                    'description' => 'Your parcel has left the local delivery hub and is on the way to your address.',
                    'location' => 'Calamba Delivery Hub, Laguna',
                    'date' => 'Sep 11, 2026',
                    'time' => '10:18 AM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Arrived at local delivery hub',
                    'description' => 'Parcel received by the hub that serves your delivery area.',
                    'location' => 'Calamba Delivery Hub, Laguna',
                    'date' => 'Sep 11, 2026',
                    'time' => '6:42 AM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Departed sorting facility',
                    'description' => 'Parcel departed the sorting facility for the destination hub.',
                    'location' => 'Cabuyao Sorting Center, Laguna',
                    'date' => 'Sep 10, 2026',
                    'time' => '11:34 PM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Parcel processed at sorting facility',
                    'description' => 'Shipment was scanned and sorted for the next delivery stage.',
                    'location' => 'Cabuyao Sorting Center, Laguna',
                    'date' => 'Sep 10, 2026',
                    'time' => '5:16 PM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Parcel picked up from seller',
                    'description' => 'Courier successfully collected the parcel from the seller.',
                    'location' => 'Quezon City, Metro Manila',
                    'date' => 'Sep 9, 2026',
                    'time' => '2:21 PM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Seller prepared your order',
                    'description' => 'The parcel was packed and handed over for courier pickup.',
                    'location' => 'ShopHop Tech Store',
                    'date' => 'Sep 9, 2026',
                    'time' => '10:02 AM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Order confirmed',
                    'description' => 'Your order was confirmed and sent to the seller for preparation.',
                    'location' => 'ShopHop',
                    'date' => 'Sep 8, 2026',
                    'time' => '9:33 AM',
                ],
            ],

            'delivery_proof' => null,
            'can_report' => false,
        ],


        /*
        |--------------------------------------------------------------------------
        | TO SHIP
        |--------------------------------------------------------------------------
        */
        [
            'id' => 'SHP-2026-00118',
            'status' => 'to-ship',
            'status_label' => 'To Ship',
            'status_note' => 'The seller is preparing your order for courier pickup.',
            'shop' => 'ShopHop Tech Store',
            'shop_slug' => 'shophop-tech-store',
            'shop_preferred' => true,

            'placed_at' => 'Sep 10, 2026 · 2:15 PM',
            'payment' => 'GCash · Verified',
            'shipping' => 'Express Delivery',

            'tracking_no' => null,
            'courier' => 'SPX Express',
            'estimated_delivery' => 'Sep 12 – 13',

            'rider' => null,

            'total' => 2499,
            'shipping_fee' => 128,
            'voucher_discount' => 200,

            'items' => [
                [
                    'id' => 2,
                    'name' => 'ShopHop Fitness Watch, Heart Rate & Sleep Tracking',
                    'image' => 'images/hero/watch.jpg',
                    'variant' => 'Midnight Black · 44mm',
                    'price' => 2499,
                    'qty' => 1,
                ],
            ],

            'progress' => [
                ['label' => 'Order Placed', 'done' => true],
                ['label' => 'Payment Verified', 'done' => true],
                ['label' => 'Preparing', 'done' => true],
                ['label' => 'Shipped', 'done' => false],
                ['label' => 'Delivered', 'done' => false],
            ],

            'tracking_events' => [
                [
                    'type' => 'current',
                    'title' => 'Seller is preparing your parcel',
                    'description' => 'Your payment has been verified. The seller is packing your order for pickup.',
                    'location' => 'ShopHop Tech Store',
                    'date' => 'Sep 11, 2026',
                    'time' => '8:14 AM',
                ],
                [
                    'type' => 'done',
                    'title' => 'GCash payment verified',
                    'description' => 'Your submitted payment reference and proof were approved.',
                    'location' => 'ShopHop Payment Review',
                    'date' => 'Sep 10, 2026',
                    'time' => '3:02 PM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Order placed',
                    'description' => 'ShopHop received your order.',
                    'location' => 'ShopHop',
                    'date' => 'Sep 10, 2026',
                    'time' => '2:15 PM',
                ],
            ],

            'delivery_proof' => null,
            'can_report' => false,
        ],


        /*
        |--------------------------------------------------------------------------
        | DELIVERED — WITH RIDER + PROOF OF DELIVERY
        |--------------------------------------------------------------------------
        */
        [
            'id' => 'SHP-2026-00097',
            'status' => 'completed',
            'status_label' => 'Delivered',
            'status_note' => 'Delivered successfully on Sep 7, 2026. Report an issue if something is wrong with this order.',
            'shop' => 'StepUp Footwear PH',
            'shop_slug' => 'stepup-footwear-ph',
            'shop_preferred' => false,

            'placed_at' => 'Sep 3, 2026 · 11:08 AM',
            'payment' => 'Cash on Delivery',
            'shipping' => 'Standard Delivery',

            'tracking_no' => 'SPXPH025991421',
            'courier' => 'SPX Express',
            'estimated_delivery' => 'Delivered Sep 7',

            'rider' => [
                'name' => 'Miguel R.',
                'phone' => '09•• ••• ••41',
                'vehicle' => 'Motorcycle',
                'plate' => 'CAL ••••',
                'status' => 'Delivery completed',
            ],

            'total' => 1899,
            'shipping_fee' => 65,
            'voucher_discount' => 50,

            'items' => [
                [
                    'id' => 3,
                    'name' => 'Everyday Running Sneakers, Lightweight & Breathable',
                    'image' => 'images/hero/sneaker.jpg',
                    'variant' => 'White · Size 9',
                    'price' => 1899,
                    'qty' => 1,
                ],
            ],

            'progress' => [
                ['label' => 'Order Placed', 'done' => true],
                ['label' => 'Confirmed', 'done' => true],
                ['label' => 'Shipped', 'done' => true],
                ['label' => 'Out for Delivery', 'done' => true],
                ['label' => 'Delivered', 'done' => true],
            ],

            'tracking_events' => [
                [
                    'type' => 'delivered',
                    'title' => 'Parcel delivered',
                    'description' => 'Delivery completed successfully. Proof of delivery was uploaded by the rider.',
                    'location' => 'Brgy. San Isidro, Santa Cruz, Laguna',
                    'date' => 'Sep 7, 2026',
                    'time' => '4:37 PM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Out for delivery',
                    'description' => 'The rider left the local delivery hub with your parcel.',
                    'location' => 'Santa Cruz Delivery Hub, Laguna',
                    'date' => 'Sep 7, 2026',
                    'time' => '10:12 AM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Arrived at local delivery hub',
                    'description' => 'Parcel reached the delivery hub nearest your address.',
                    'location' => 'Santa Cruz Delivery Hub, Laguna',
                    'date' => 'Sep 7, 2026',
                    'time' => '6:05 AM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Departed sorting center',
                    'description' => 'Parcel departed the main sorting facility.',
                    'location' => 'Cabuyao Sorting Center, Laguna',
                    'date' => 'Sep 6, 2026',
                    'time' => '10:28 PM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Parcel picked up',
                    'description' => 'Courier collected the parcel from the seller.',
                    'location' => 'Pasay City, Metro Manila',
                    'date' => 'Sep 4, 2026',
                    'time' => '1:46 PM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Order confirmed',
                    'description' => 'The seller accepted your order.',
                    'location' => 'ShopHop',
                    'date' => 'Sep 3, 2026',
                    'time' => '11:10 AM',
                ],
            ],

            'delivery_proof' => [
                /*
                 * TEMP DEMO PHOTO:
                 * replace with something like:
                 * storage/order-delivery-proofs/SHP-2026-00097.jpg
                 * once the rider/courier uploads real proof.
                 */
                'photo' => 'images/hero/sneaker.jpg',
                'delivered_at' => 'Sep 7, 2026 · 4:37 PM',
                'received_by' => 'Buyer / Household Member',
                'delivery_note' => 'Parcel handed over at the delivery address.',
                'uploaded_by' => 'Miguel R. · SPX Express',
            ],

            'can_report' => true,
        ],


        /*
        |--------------------------------------------------------------------------
        | TO PAY
        |--------------------------------------------------------------------------
        */
        [
            'id' => 'SHP-2026-00088',
            'status' => 'to-pay',
            'status_label' => 'To Pay',
            'status_note' => 'Your GCash payment is waiting for verification.',
            'shop' => 'HomeTech Essentials',
            'shop_slug' => 'hometech-essentials',
            'shop_preferred' => false,

            'placed_at' => 'Sep 11, 2026 · 8:45 AM',
            'payment' => 'GCash · Pending Verification',
            'shipping' => 'Standard Delivery',

            'tracking_no' => null,
            'courier' => null,
            'estimated_delivery' => 'After payment verification',

            'rider' => null,

            'total' => 999,
            'shipping_fee' => 58,
            'voucher_discount' => 0,

            'items' => [
                [
                    'id' => 4,
                    'name' => 'Compact TWS Earbuds',
                    'image' => 'images/hero/earbuds.jpg',
                    'variant' => 'White',
                    'price' => 999,
                    'qty' => 1,
                ],
            ],

            'progress' => [
                ['label' => 'Order Placed', 'done' => true],
                ['label' => 'Payment', 'done' => false],
                ['label' => 'Preparing', 'done' => false],
                ['label' => 'Shipped', 'done' => false],
                ['label' => 'Delivered', 'done' => false],
            ],

            'tracking_events' => [
                [
                    'type' => 'current',
                    'title' => 'Payment verification pending',
                    'description' => 'ShopHop is checking the submitted GCash reference and proof of payment.',
                    'location' => 'ShopHop Payment Review',
                    'date' => 'Sep 11, 2026',
                    'time' => '8:48 AM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Order placed',
                    'description' => 'Your order was created successfully.',
                    'location' => 'ShopHop',
                    'date' => 'Sep 11, 2026',
                    'time' => '8:45 AM',
                ],
            ],

            'delivery_proof' => null,
            'can_report' => false,
        ],


        /*
        |--------------------------------------------------------------------------
        | CANCELLED
        |--------------------------------------------------------------------------
        */
        [
            'id' => 'SHP-2026-00072',
            'status' => 'cancelled',
            'status_label' => 'Cancelled',
            'status_note' => 'This order was cancelled before courier pickup.',
            'shop' => 'Gadget Lane PH',
            'shop_slug' => 'gadget-lane-ph',
            'shop_preferred' => false,

            'placed_at' => 'Aug 29, 2026 · 4:20 PM',
            'payment' => 'Cash on Delivery',
            'shipping' => 'Standard Delivery',

            'tracking_no' => null,
            'courier' => null,
            'estimated_delivery' => 'Cancelled',

            'rider' => null,

            'total' => 749,
            'shipping_fee' => 0,
            'voucher_discount' => 0,

            'items' => [
                [
                    'id' => 5,
                    'name' => 'Everyday Bluetooth Buds',
                    'image' => 'images/hero/earbuds.jpg',
                    'variant' => 'Mint Green',
                    'price' => 749,
                    'qty' => 1,
                ],
            ],

            'progress' => [
                ['label' => 'Order Placed', 'done' => true],
                ['label' => 'Cancelled', 'done' => true],
            ],

            'tracking_events' => [
                [
                    'type' => 'cancelled',
                    'title' => 'Order cancelled',
                    'description' => 'The order was cancelled before shipment.',
                    'location' => 'ShopHop',
                    'date' => 'Aug 29, 2026',
                    'time' => '5:02 PM',
                ],
                [
                    'type' => 'done',
                    'title' => 'Order placed',
                    'description' => 'Your order was created.',
                    'location' => 'ShopHop',
                    'date' => 'Aug 29, 2026',
                    'time' => '4:20 PM',
                ],
            ],

            'delivery_proof' => null,
            'can_report' => false,
        ],
    ]);


    $orderCounts = [
        'all' => $orders->count(),
        'to-pay' => $orders->where('status', 'to-pay')->count(),
        'to-ship' => $orders->where('status', 'to-ship')->count(),
        'to-receive' => $orders->where('status', 'to-receive')->count(),
        'completed' => $orders->where('status', 'completed')->count(),
        'cancelled' => $orders->where('status', 'cancelled')->count(),
        'reported' => 0,
    ];
@endphp


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
<section class="bg-gray-bg/75 min-h-[72vh] py-5 sm:py-6">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

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

                    Track payment, packing, courier movement, delivery, and proof of delivery.

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
                    ['key' => 'reported', 'label' => 'Reports'],
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
        <div class="flex items-start gap-2 bg-[#EAF9F5] border border-teal/10 rounded-xl px-3 py-2.5 mb-3">

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
                    data-order-status="{{ $order['status'] }}"
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
                                        src="{{ asset($item['image']) }}"
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

                                            ₱{{ number_format($item['price']) }}

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
                                    ? 'bg-[#EAF9F5]'
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
                    <div class="px-4 py-3.5 border-t border-gray-border/70">

                        <div class="flex items-center justify-between gap-3 mb-3">

                            <div>

                                <p class="text-[11.5px] font-bold text-navy">

                                    Order Progress

                                </p>


                                <p class="text-[10px] text-navy/40 mt-0.5">

                                    @if ($order['tracking_no'])

                                        {{ $order['courier'] }} · {{ $order['tracking_no'] }}

                                    @else

                                        Tracking number will appear after courier pickup.

                                    @endif

                                </p>

                            </div>


                            <span class="text-[10px] font-semibold text-teal-dark shrink-0">

                                {{ $order['estimated_delivery'] }}

                            </span>

                        </div>


                        <div
                            class="grid"
                            style="grid-template-columns: repeat({{ count($order['progress']) }}, minmax(0, 1fr));"
                        >

                            @foreach ($order['progress'] as $step)

                                <div class="relative text-center">

                                    @if (! $loop->last)

                                        <div
                                            class="absolute left-1/2 top-3 w-full h-0.5
                                                   {{ $step['done']
                                                        ? 'bg-teal'
                                                        : 'bg-gray-border' }}"
                                        ></div>

                                    @endif


                                    <span
                                        class="relative z-10 mx-auto w-6 h-6 rounded-full
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

                            {{-- Tracking history --}}
                            <div class="p-4 lg:border-r border-gray-border/80">

                                <div class="flex items-center justify-between gap-3 mb-4">

                                    <div>

                                        <p class="text-[13px] font-bold text-navy">

                                            Detailed Tracking

                                        </p>


                                        <p class="text-[10px] text-navy/45 mt-1">

                                            Latest courier updates for this order.

                                        </p>

                                    </div>


                                    @if ($order['tracking_no'])

                                        <button
                                            type="button"
                                            class="copy-tracking-btn h-7 px-2.5 rounded-lg
                                                   border border-gray-border bg-white
                                                   text-[8.5px] font-semibold text-navy/45
                                                   hover:border-teal/40 hover:text-teal-dark transition"
                                            data-copy-tracking="{{ $order['tracking_no'] }}"
                                        >

                                            Copy Tracking No.

                                        </button>

                                    @endif

                                </div>


                                <div class="relative">

                                    @foreach ($order['tracking_events'] as $event)

                                        <div class="relative flex gap-3 pb-5 last:pb-0">

                                            @if (! $loop->last)

                                                <div class="absolute left-2.75 top-6 bottom-0 w-px bg-gray-border"></div>

                                            @endif


                                            <span
                                                class="relative z-10 w-6 h-6 rounded-full border shrink-0
                                                       flex items-center justify-center
                                                       {{ $event['type'] === 'current'
                                                            ? 'bg-teal border-teal text-white ring-4 ring-teal/10'
                                                            : ($event['type'] === 'delivered'
                                                                ? 'bg-teal border-teal text-white'
                                                                : ($event['type'] === 'cancelled'
                                                                    ? 'bg-red-50 border-red-200 text-red-500'
                                                                    : 'bg-white border-gray-border text-teal-dark')) }}"
                                            >

                                                @if ($event['type'] === 'current')

                                                    <x-lucide-truck class="w-2.5 h-2.5" />

                                                @elseif ($event['type'] === 'delivered')

                                                    <x-lucide-check class="w-2.5 h-2.5" />

                                                @elseif ($event['type'] === 'cancelled')

                                                    <x-lucide-x class="w-2.5 h-2.5" />

                                                @else

                                                    <x-lucide-package class="w-2.5 h-2.5" />

                                                @endif

                                            </span>


                                            <div class="min-w-0 flex-1">

                                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-1.5">

                                                    <div>

                                                        <p
                                                            class="text-[11.5px] font-semibold
                                                                   {{ in_array($event['type'], ['current', 'delivered'])
                                                                        ? 'text-teal-dark'
                                                                        : ($event['type'] === 'cancelled'
                                                                            ? 'text-red-500'
                                                                            : 'text-navy') }}"
                                                        >

                                                            {{ $event['title'] }}

                                                        </p>


                                                        <p class="text-[10.5px] leading-relaxed text-navy/50 mt-1">

                                                            {{ $event['description'] }}

                                                        </p>


                                                        <div class="flex items-center gap-1 mt-1.5 text-[9.5px] text-navy/40">

                                                            <x-lucide-map-pin class="w-2.5 h-2.5" />

                                                            {{ $event['location'] }}

                                                        </div>

                                                    </div>


                                                    <div class="sm:text-right shrink-0">

                                                        <p class="text-[10px] font-medium text-navy/50">

                                                            {{ $event['date'] }}

                                                        </p>


                                                        <p class="text-[9px] text-navy/40 mt-0.5">

                                                            {{ $event['time'] }}

                                                        </p>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>


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

                                                    {{ $order['courier'] ?? 'Not assigned yet' }}

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


                                {{-- Rider --}}
                                @if ($order['rider'])

                                    <div class="mt-4 pt-4 border-t border-gray-border">

                                        <div class="flex items-center justify-between gap-2">

                                            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-teal-dark">

                                                Delivery Rider

                                            </p>


                                            <span class="text-[8.5px] font-semibold text-teal-dark bg-teal-light px-1.5 py-0.5 rounded">

                                                {{ $order['rider']['status'] }}

                                            </span>

                                        </div>


                                        <div class="mt-3 flex items-start gap-3">

                                            <span class="w-10 h-10 rounded-full bg-navy text-white flex items-center justify-center shrink-0">

                                                <x-lucide-user class="w-4 h-4" />

                                            </span>


                                            <div class="min-w-0 flex-1">

                                                <p class="text-[12px] font-bold text-navy">

                                                    {{ $order['rider']['name'] }}

                                                </p>


                                                <p class="text-[9.5px] text-navy/45 mt-0.5">

                                                    {{ $order['rider']['vehicle'] }}
                                                    · {{ $order['rider']['plate'] }}

                                                </p>


                                                <div class="flex items-center gap-1 mt-1.5 text-[9.5px] text-navy/50">

                                                    <x-lucide-phone class="w-2.5 h-2.5" />

                                                    {{ $order['rider']['phone'] }}

                                                </div>

                                            </div>

                                        </div>


                                        @if ($order['status'] === 'to-receive')

                                            <button
                                                type="button"
                                                class="w-full h-8 mt-3 rounded-lg border border-teal
                                                       text-[8.5px] font-semibold text-teal-dark
                                                       hover:bg-teal-light transition"
                                            >

                                                Contact Rider

                                            </button>

                                        @endif

                                    </div>

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
                                            data-proof-image="{{ asset($order['delivery_proof']['photo']) }}"
                                            data-proof-order="{{ $order['id'] }}"
                                        >

                                            <img
                                                src="{{ asset($order['delivery_proof']['photo']) }}"
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

                                                <span class="text-[9px] text-navy/40">
                                                    Received by
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

                                            Demo image only. Replace this with the rider-uploaded delivery photo from storage later.

                                        </p>

                                    </div>

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
                                        ₱{{ number_format($order['total']) }}
                                    </p>

                                </div>

                            </div>


                            <div class="flex flex-wrap items-center gap-2 lg:justify-end">

                                {{-- Tracking toggle --}}
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
                                        View Tracking
                                    </span>

                                </button>


                                @if ($order['status'] === 'to-pay')

                                    <button
                                        type="button"
                                        class="h-8 px-3 rounded-lg bg-teal hover:bg-teal-dark
                                               text-white text-[9.5px] font-semibold transition"
                                    >
                                        Complete Payment
                                    </button>

                                @endif


                                @if (in_array($order['status'], ['to-ship', 'to-receive']))

                                    <button
                                        type="button"
                                        class="h-8 px-3 rounded-lg border border-teal
                                               text-teal-dark hover:bg-teal-light
                                               text-[9.5px] font-semibold transition"
                                    >
                                        Contact Seller
                                    </button>

                                @endif


                                @if ($order['status'] === 'completed')

                                    <button
                                        type="button"
                                        class="h-8 px-3 rounded-lg border border-gray-border
                                               text-navy/60 hover:border-teal/40 hover:text-teal-dark
                                               text-[9.5px] font-semibold transition"
                                    >
                                        Buy Again
                                    </button>

                                @endif


                                @if ($order['can_report'])

                                    <button
                                        type="button"
                                        class="report-order-btn h-8 px-3 rounded-lg border border-red-200
                                               bg-red-50/70 text-red-500 hover:bg-red-50
                                               text-[9.5px] font-semibold transition
                                               inline-flex items-center gap-1.5"
                                        data-order-id="{{ $order['id'] }}"
                                        data-order-shop="{{ $order['shop'] }}"
                                    >

                                        <x-lucide-flag class="w-3 h-3" />

                                        Report Issue

                                    </button>

                                @endif


                                <button
                                    type="button"
                                    class="h-8 px-3 rounded-lg border border-gray-border
                                           text-navy/50 hover:text-teal-dark hover:border-teal/40
                                           text-[9.5px] font-semibold transition"
                                >
                                    Order Details
                                </button>

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


{{-- =========================================================
    REPORT ISSUE MODAL
    REPORT ONLY — NO REFUND
========================================================= --}}
<div
    id="reportOrderModal"
    class="hidden fixed inset-0 z-100 items-center justify-center p-4"
    aria-hidden="true"
>

    <div
        data-report-backdrop
        class="absolute inset-0 bg-navy/45 backdrop-blur-[2px]"
    ></div>


    <div
        class="relative w-full max-w-130 max-h-[90vh] overflow-y-auto
               bg-white rounded-2xl border border-gray-border shadow-2xl"
    >

        {{-- Modal header --}}
        <div class="flex items-start justify-between gap-3 px-4 py-3 border-b border-gray-border">

            <div class="flex items-center gap-2">

                <span class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center">

                    <x-lucide-flag class="w-4 h-4" />

                </span>


                <div>

                    <p class="text-[12px] font-bold text-navy">

                        Report an Order Issue

                    </p>


                    <p class="text-[9px] text-navy/40 mt-0.5">

                        Order
                        <span
                            id="reportOrderNumber"
                            class="font-semibold text-navy/60"
                        ></span>

                    </p>

                </div>

            </div>


            <button
                type="button"
                data-close-report
                class="w-7 h-7 rounded-lg flex items-center justify-center
                       text-navy/35 hover:text-navy hover:bg-gray-bg transition"
                aria-label="Close report modal"
            >

                <x-lucide-x class="w-3.5 h-3.5" />

            </button>

        </div>


        <form
            id="reportOrderForm"
            class="p-4"
        >

            <input
                type="hidden"
                id="reportOrderId"
                name="order_id"
            >


            {{-- Order context --}}
            <div class="grid grid-cols-2 gap-2 mb-3">

                <div class="rounded-xl bg-gray-bg px-3 py-2.5">

                    <p class="text-[8px] text-navy/30">
                        Courier
                    </p>

                    <p
                        id="reportCourier"
                        class="text-[9px] font-semibold text-navy mt-0.5"
                    >
                        —
                    </p>

                </div>


                <div class="rounded-xl bg-gray-bg px-3 py-2.5">

                    <p class="text-[8px] text-navy/30">
                        Tracking Number
                    </p>

                    <p
                        id="reportTracking"
                        class="text-[9px] font-semibold text-navy mt-0.5 break-all"
                    >
                        —
                    </p>

                </div>

            </div>


            <div class="rounded-xl bg-[#EAF9F5] border border-teal/10 p-3 mb-4">

                <div class="flex gap-2">

                    <x-lucide-info class="w-3.5 h-3.5 text-teal-dark shrink-0 mt-0.5" />


                    <p class="text-[9px] leading-relaxed text-navy/50">

                        This report is sent to ShopHop support for review.
                        <span class="font-semibold text-navy">
                            It does not request a refund.
                        </span>

                    </p>

                </div>

            </div>


            <div>

                <label
                    for="reportReason"
                    class="text-[10px] font-semibold text-navy/60 block mb-1.5"
                >
                    What is the problem?
                    <span class="text-red-500">*</span>
                </label>


                <select
                    id="reportReason"
                    name="reason"
                    required
                    class="w-full h-10 rounded-xl border border-gray-border bg-white
                           px-3 text-[10.5px] text-navy outline-none
                           focus:border-teal focus:ring-1 focus:ring-teal/10"
                >

                    <option value="">
                        Select a problem
                    </option>

                    <option value="damaged">
                        Item arrived damaged
                    </option>

                    <option value="wrong-item">
                        Wrong item received
                    </option>

                    <option value="missing-item">
                        Missing item or parts
                    </option>

                    <option value="defective">
                        Item is defective / not working
                    </option>

                    <option value="delivery-status">
                        Marked delivered but not received
                    </option>

                    <option value="proof-concern">
                        Delivery photo / proof concern
                    </option>

                    <option value="rider-concern">
                        Courier / rider concern
                    </option>

                    <option value="seller-concern">
                        Seller / parcel concern
                    </option>

                    <option value="other">
                        Other concern
                    </option>

                </select>

            </div>


            <div class="mt-3">

                <label
                    for="reportDescription"
                    class="text-[10px] font-semibold text-navy/60 block mb-1.5"
                >
                    Tell us what happened
                    <span class="text-red-500">*</span>
                </label>


                <textarea
                    id="reportDescription"
                    name="description"
                    rows="4"
                    maxlength="500"
                    required
                    placeholder="Describe the issue clearly so ShopHop support can review it."
                    class="w-full rounded-xl border border-gray-border
                           px-3 py-2.5 text-[10.5px] leading-relaxed text-navy
                           placeholder:text-navy/30 outline-none resize-none
                           focus:border-teal focus:ring-1 focus:ring-teal/10"
                ></textarea>


                <div class="flex items-center justify-between mt-1">

                    <span class="text-[8.5px] text-navy/35">

                        Do not share passwords or payment PINs.

                    </span>


                    <span
                        id="reportCharacterCount"
                        class="text-[8.5px] text-navy/35"
                    >
                        0 / 500
                    </span>

                </div>

            </div>


            <div class="mt-3">

                <label class="text-[10px] font-semibold text-navy/60 block mb-1.5">

                    Evidence

                    <span class="font-normal text-navy/30">
                        (optional)
                    </span>

                </label>


                <label
                    for="reportEvidence"
                    class="flex items-center gap-2.5 min-h-11 rounded-xl
                           border border-dashed border-gray-border
                           px-3 py-2 cursor-pointer
                           hover:border-teal hover:bg-teal-light/20 transition"
                >

                    <span class="w-7 h-7 rounded-lg bg-gray-bg text-navy/40 flex items-center justify-center shrink-0">

                        <x-lucide-upload class="w-3.5 h-3.5" />

                    </span>


                    <div class="min-w-0">

                        <p
                            id="reportEvidenceLabel"
                            class="text-[10px] font-medium text-navy/55 truncate"
                        >

                            Add photo or screenshot

                        </p>


                        <p class="text-[8.5px] text-navy/35 mt-0.5">

                            JPG, PNG, WEBP · up to 5 MB

                        </p>

                    </div>

                </label>


                <input
                    id="reportEvidence"
                    type="file"
                    name="evidence"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                >

            </div>


            <label class="flex items-start gap-2 mt-4 cursor-pointer">

                <input
                    id="reportConfirm"
                    type="checkbox"
                    required
                    class="w-3.5 h-3.5 mt-0.5 accent-teal shrink-0"
                >


                <span class="text-[9.5px] leading-relaxed text-navy/50">

                    I confirm that the information in this report is accurate to the best of my knowledge.

                </span>

            </label>


            <div class="flex items-center justify-end gap-2 mt-5 pt-4 border-t border-gray-border">

                <button
                    type="button"
                    data-close-report
                    class="h-9 px-3.5 rounded-xl border border-gray-border
                           text-[10px] font-semibold text-navy/55
                           hover:border-teal/40 hover:text-teal-dark transition"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="h-9 px-4 rounded-xl bg-teal hover:bg-teal-dark
                           text-white text-[10px] font-semibold
                           inline-flex items-center gap-1.5 transition"
                >

                    <x-lucide-send class="w-3 h-3" />

                    Submit Report

                </button>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
    PROOF OF DELIVERY LIGHTBOX
========================================================= --}}
<div
    id="proofPhotoModal"
    class="hidden fixed inset-0 z-105 items-center justify-center p-4"
    aria-hidden="true"
>

    <div
        data-proof-backdrop
        class="absolute inset-0 bg-navy/80 backdrop-blur-sm"
    ></div>


    <div class="relative w-full max-w-3xl">

        <button
            type="button"
            data-close-proof
            class="absolute -top-10 right-0 w-8 h-8 rounded-full
                   bg-white/10 text-white hover:bg-white/20
                   flex items-center justify-center transition"
            aria-label="Close proof photo"
        >

            <x-lucide-x class="w-4 h-4" />

        </button>


        <div class="overflow-hidden rounded-2xl bg-white shadow-2xl">

            <img
                id="proofPhotoImage"
                src=""
                alt="Proof of delivery"
                class="w-full max-h-[72vh] object-contain bg-black"
            >


            <div class="px-4 py-3">

                <p class="text-[11px] font-bold text-navy">

                    Proof of Delivery

                </p>


                <p class="text-[9px] text-navy/40 mt-0.5">

                    Rider-uploaded delivery evidence for order
                    <span
                        id="proofPhotoOrder"
                        class="font-semibold text-navy/60"
                    ></span>.

                </p>

            </div>

        </div>

    </div>

</div>


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
            'cancelled',
            'reported'
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
                                otherLabel.textContent =
                                    'View Tracking';
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

                    label.textContent =
                        willOpen
                            ? 'Hide Tracking'
                            : 'View Tracking';

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

                    showToast(
                        trackingNumber
                    );
                }
            });
        });


    /*
    |--------------------------------------------------------------------------
    | PROOF OF DELIVERY PHOTO MODAL
    |--------------------------------------------------------------------------
    */

    const proofModal =
        document.getElementById('proofPhotoModal');

    const proofImage =
        document.getElementById('proofPhotoImage');

    const proofOrder =
        document.getElementById('proofPhotoOrder');


    function openProofModal(imageUrl, orderId) {

        proofImage.src =
            imageUrl;


        proofOrder.textContent =
            orderId;


        proofModal.classList.remove(
            'hidden'
        );


        proofModal.classList.add(
            'flex'
        );


        proofModal.setAttribute(
            'aria-hidden',
            'false'
        );


        document.body.classList.add(
            'overflow-hidden'
        );
    }


    function closeProofModal() {

        proofModal.classList.add(
            'hidden'
        );


        proofModal.classList.remove(
            'flex'
        );


        proofModal.setAttribute(
            'aria-hidden',
            'true'
        );


        document.body.classList.remove(
            'overflow-hidden'
        );


        proofImage.src = '';
    }


    document.querySelectorAll('.proof-photo-btn')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                openProofModal(
                    button.dataset.proofImage,
                    button.dataset.proofOrder
                );
            });
        });


    document.querySelectorAll('[data-close-proof]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                closeProofModal
            );
        });


    document.querySelector('[data-proof-backdrop]')
        ?.addEventListener(
            'click',
            closeProofModal
        );


    /*
    |--------------------------------------------------------------------------
    | REPORT MODAL
    |--------------------------------------------------------------------------
    */

    const reportModal =
        document.getElementById('reportOrderModal');

    const reportForm =
        document.getElementById('reportOrderForm');

    const reportOrderId =
        document.getElementById('reportOrderId');

    const reportOrderNumber =
        document.getElementById('reportOrderNumber');

    const reportCourier =
        document.getElementById('reportCourier');

    const reportTracking =
        document.getElementById('reportTracking');

    const reportReason =
        document.getElementById('reportReason');

    const reportDescription =
        document.getElementById('reportDescription');

    const reportCharacterCount =
        document.getElementById('reportCharacterCount');

    const reportEvidence =
        document.getElementById('reportEvidence');

    const reportEvidenceLabel =
        document.getElementById('reportEvidenceLabel');


    function openReportModal(card) {

        const orderId =
            card.dataset.orderId;


        reportForm.reset();


        reportOrderId.value =
            orderId;


        reportOrderNumber.textContent =
            orderId;


        reportCourier.textContent =
            card.dataset.courier || 'Not available';


        reportTracking.textContent =
            card.dataset.trackingNumber || 'Not available';


        reportEvidenceLabel.textContent =
            'Add photo or screenshot';


        reportCharacterCount.textContent =
            '0 / 500';


        reportModal.classList.remove(
            'hidden'
        );


        reportModal.classList.add(
            'flex'
        );


        reportModal.setAttribute(
            'aria-hidden',
            'false'
        );


        document.body.classList.add(
            'overflow-hidden'
        );


        setTimeout(function () {
            reportReason.focus();
        }, 60);
    }


    function closeReportModal() {

        reportModal.classList.add(
            'hidden'
        );


        reportModal.classList.remove(
            'flex'
        );


        reportModal.setAttribute(
            'aria-hidden',
            'true'
        );


        document.body.classList.remove(
            'overflow-hidden'
        );
    }


    document.querySelectorAll('.report-order-btn')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const card =
                    button.closest('.order-card');


                if (card) {
                    openReportModal(card);
                }
            });
        });


    document.querySelectorAll('[data-close-report]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                closeReportModal
            );
        });


    document.querySelector('[data-report-backdrop]')
        ?.addEventListener(
            'click',
            closeReportModal
        );


    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key !== 'Escape') {
                return;
            }


            if (
                reportModal &&
                !reportModal.classList.contains('hidden')
            ) {
                closeReportModal();
            }


            if (
                proofModal &&
                !proofModal.classList.contains('hidden')
            ) {
                closeProofModal();
            }
        }
    );


    reportDescription?.addEventListener(
        'input',
        function () {

            reportCharacterCount.textContent =
                reportDescription.value.length +
                ' / 500';

        }
    );


    reportEvidence?.addEventListener(
        'change',
        function () {

            const file =
                reportEvidence.files?.[0];


            reportEvidenceLabel.textContent =
                file
                    ? file.name
                    : 'Add photo or screenshot';

        }
    );


    reportForm?.addEventListener(
        'submit',
        function (event) {

            event.preventDefault();


            const orderId =
                reportOrderId.value;


            const card =
                document.querySelector(
                    '.order-card[data-order-id="' +
                    orderId +
                    '"]'
                );


            if (!card) {

                closeReportModal();

                return;
            }


            /*
             * FRONTEND PREVIEW ONLY
             * ---------------------------------------------
             * Replace this block later with:
             *
             * POST /buyer/orders/{order}/report
             *
             * and save to an order_reports table.
             */

            card.dataset.orderStatus =
                'reported';


            const statusBadge =
                card.querySelector(
                    '.order-status-badge'
                );


            const statusLabel =
                card.querySelector(
                    '[data-order-status-label]'
                );


            if (statusBadge) {

                statusBadge.className =
                    'order-status-badge inline-flex items-center gap-1 px-2 py-1 rounded-full text-[9px] font-bold bg-amber-50 text-amber-600';

            }


            if (statusLabel) {

                statusLabel.textContent =
                    'Report Submitted';

            }


            const statusNote =
                card.querySelector(
                    '[data-order-status-note]'
                );


            if (statusNote) {

                statusNote.textContent =
                    'Your report was submitted to ShopHop support and is waiting for review.';

            }


            const reportButton =
                card.querySelector(
                    '.report-order-btn'
                );


            if (reportButton) {

                reportButton.disabled = true;


                reportButton.innerHTML =
                    '<span class="inline-flex items-center gap-1.5">' +
                    '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>' +
                    'Report Submitted' +
                    '</span>';


                reportButton.className =
                    'report-order-btn h-8 px-3 rounded-lg border border-amber-200 bg-amber-50 text-amber-600 text-[9.5px] font-semibold cursor-default';

            }


            updateTabCounts();


            closeReportModal();


            const reportTab =
                document.querySelector(
                    '[data-order-tab="reported"]'
                );


            reportTab?.click();


            showToast(
                'Report submitted. ShopHop support will review your concern.'
            );
        }
    );


    updateTabCounts();
    filterOrders();

});
</script>
@endpush
