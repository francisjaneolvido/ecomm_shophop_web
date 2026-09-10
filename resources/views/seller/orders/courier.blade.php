@extends('seller.partials.layout')

@section('title', 'Hand Over to Courier')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    | This page is intentionally hardcoded for UI/UX development.
    | Your backend teammate can later replace this collection with controller
    | data and connect pickup requests, rider assignment, parcel verification,
    | status updates, and tracking.
    */

    $orders = collect($orders ?? [
        [
            'id' => 'ORD-10225',
            'buyer' => 'Ella Marasigan',
            'status' => 'READY_FOR_PICKUP',
            'pickup_status' => 'WAITING_ASSIGNMENT',
            'ready_at' => 'Today, 11:20 AM',
            'pickup_window' => '1:00 PM – 3:00 PM',
            'delivery_area' => 'Calamba, Laguna',
            'address' => 'Blk 7 Lot 14, Brgy. Real, Calamba, Laguna',
            'items_count' => 2,
            'parcel_type' => 'Box',
            'weight' => '1.8 kg',
            'dimensions' => '32 × 24 × 20 cm',
            'waybill_no' => 'WB-10225-0908',
            'handover_code' => '4821',
            'rider' => null,
            'items' => [
                [
                    'name' => 'Capiz Shell Wall Lamp',
                    'variant' => 'Warm White / Medium',
                    'sku' => 'CSL-WW-M',
                    'qty' => 1,
                    'image' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=240&q=80',
                ],
                [
                    'name' => 'Handwoven Rattan Basket',
                    'variant' => 'Natural / Medium',
                    'sku' => 'RATTAN-NAT-M',
                    'qty' => 1,
                    'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=240&q=80',
                ],
            ],
        ],
        [
            'id' => 'ORD-10220',
            'buyer' => 'Ronald Cabrera',
            'status' => 'READY_FOR_PICKUP',
            'pickup_status' => 'RIDER_ASSIGNED',
            'ready_at' => 'Today, 10:05 AM',
            'pickup_window' => '12:30 PM – 1:30 PM',
            'delivery_area' => 'Los Baños, Laguna',
            'address' => 'Brgy. Batong Malake, Los Baños, Laguna',
            'items_count' => 1,
            'parcel_type' => 'Pouch',
            'weight' => '0.6 kg',
            'dimensions' => '24 × 18 × 8 cm',
            'waybill_no' => 'WB-10220-0908',
            'handover_code' => '7364',
            'rider' => [
                'id' => 'RDR-004',
                'name' => 'Paolo Mendoza',
                'display' => 'Rider 04 — Paolo M.',
                'phone' => '09•• ••• •184',
                'vehicle' => 'Motorcycle',
                'plate' => 'NCR 4821',
                'eta' => '12 mins',
            ],
            'items' => [
                [
                    'name' => 'Barako Coffee Beans 250g',
                    'variant' => 'Dark Roast',
                    'sku' => 'BARAKO-250-DR',
                    'qty' => 2,
                    'image' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=240&q=80',
                ],
            ],
        ],
        [
            'id' => 'ORD-10218',
            'buyer' => 'Mika Reyes',
            'status' => 'READY_FOR_PICKUP',
            'pickup_status' => 'RIDER_ARRIVED',
            'ready_at' => 'Today, 9:42 AM',
            'pickup_window' => '11:30 AM – 12:30 PM',
            'delivery_area' => 'Santa Cruz, Laguna',
            'address' => 'Brgy. Poblacion II, Santa Cruz, Laguna',
            'items_count' => 2,
            'parcel_type' => 'Box',
            'weight' => '1.2 kg',
            'dimensions' => '28 × 22 × 16 cm',
            'waybill_no' => 'WB-10218-0908',
            'handover_code' => '1957',
            'rider' => [
                'id' => 'RDR-011',
                'name' => 'Miguel Santos',
                'display' => 'Rider 11 — Miguel S.',
                'phone' => '09•• ••• •629',
                'vehicle' => 'Motorcycle',
                'plate' => 'DAB 2190',
                'eta' => 'Arrived',
            ],
            'items' => [
                [
                    'name' => 'Daily Glow Face Serum',
                    'variant' => '30ml',
                    'sku' => 'SERUM-30ML',
                    'qty' => 1,
                    'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=240&q=80',
                ],
                [
                    'name' => 'Minimalist Canvas Tote Bag',
                    'variant' => 'Natural',
                    'sku' => 'TOTE-CNV-NAT',
                    'qty' => 1,
                    'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=240&q=80',
                ],
            ],
        ],
        [
            'id' => 'ORD-10212',
            'buyer' => 'Jessa Lim',
            'status' => 'PICKED_UP',
            'pickup_status' => 'PICKED_UP',
            'ready_at' => 'Yesterday, 3:15 PM',
            'pickup_window' => 'Yesterday, 4:00 PM',
            'picked_up_at' => 'Yesterday, 4:18 PM',
            'delivery_area' => 'Pagsanjan, Laguna',
            'address' => 'Brgy. Sampaloc, Pagsanjan, Laguna',
            'items_count' => 1,
            'parcel_type' => 'Box',
            'weight' => '0.9 kg',
            'dimensions' => '25 × 18 × 14 cm',
            'waybill_no' => 'WB-10212-0907',
            'handover_code' => '6208',
            'rider' => [
                'id' => 'RDR-007',
                'name' => 'Carlo Dizon',
                'display' => 'Rider 07 — Carlo D.',
                'phone' => '09•• ••• •320',
                'vehicle' => 'Motorcycle',
                'plate' => 'NBU 5310',
                'eta' => null,
            ],
            'items' => [
                [
                    'name' => 'Insulated Travel Tumbler',
                    'variant' => 'Black / 500ml',
                    'sku' => 'TUMBLER-BLK-500',
                    'qty' => 1,
                    'image' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=240&q=80',
                ],
            ],
        ],
    ]);

    $pickupMeta = [
        'WAITING_ASSIGNMENT' => [
            'label' => 'Waiting for Rider',
            'class' => 'bg-navy/10 text-navy/55',
            'icon' => 'loader-circle',
        ],
        'RIDER_ASSIGNED' => [
            'label' => 'Rider Assigned',
            'class' => 'bg-sky/10 text-sky',
            'icon' => 'bike',
        ],
        'RIDER_ARRIVED' => [
            'label' => 'Rider Arrived',
            'class' => 'bg-yellow/20 text-amber-700',
            'icon' => 'map-pin-check',
        ],
        'PICKED_UP' => [
            'label' => 'Picked Up',
            'class' => 'bg-teal/10 text-teal-dark',
            'icon' => 'package-check',
        ],
    ];

    $waitingCount = $orders->where('pickup_status', 'WAITING_ASSIGNMENT')->count();
    $assignedCount = $orders->whereIn('pickup_status', ['RIDER_ASSIGNED', 'RIDER_ARRIVED'])->count();
    $arrivedCount = $orders->where('pickup_status', 'RIDER_ARRIVED')->count();
    $pickedUpCount = $orders->where('pickup_status', 'PICKED_UP')->count();
@endphp


<style>
    #sellerCourier .courier-card {
        transition:
            transform .16s ease,
            box-shadow .16s ease,
            border-color .16s ease;
    }

    #sellerCourier .courier-card:hover {
        transform: translateY(-1px);
    }

    #sellerCourier [hidden],
    #courierOrderModal[hidden],
    #handoverModal[hidden],
    #pickupRequestModal[hidden] {
        display: none !important;
    }

    .courier-tab-active {
        background: #0F2C3F;
        color: #ffffff;
        border-color: #0F2C3F;
    }

    .handover-check {
        accent-color: #2ECFA6;
    }
</style>


<div id="sellerCourier" class="space-y-5">

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <section>
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
            <div class="min-w-0">

                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    Hand Over to Courier
                </h1>

                <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                    Monitor rider assignment, verify the assigned courier, hand over the correct parcel,
                    then confirm pickup once the rider has collected it.
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
    <section class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">

        <button
            type="button"
            data-summary-filter="waiting_assignment"
            class="courier-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-navy/15"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-navy/10 text-navy/55 flex items-center justify-center">
                    <x-lucide-loader-circle class="w-5 h-5" />
                </div>
                <span class="text-[10px] font-bold text-navy/35">WAITING</span>
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">{{ $waitingCount }}</p>
            <p class="text-xs text-navy/45">Awaiting rider assignment</p>
        </button>


        <button
            type="button"
            data-summary-filter="assigned"
            class="courier-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-sky/25"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-sky/10 text-sky flex items-center justify-center">
                    <x-lucide-bike class="w-5 h-5" />
                </div>
                <span class="text-[10px] font-bold text-sky/60">ASSIGNED</span>
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">{{ $assignedCount }}</p>
            <p class="text-xs text-navy/45">Riders assigned</p>
        </button>


        <button
            type="button"
            data-summary-filter="rider_arrived"
            class="courier-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-yellow/30"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow/20 text-amber-700 flex items-center justify-center">
                    <x-lucide-map-pin-check class="w-5 h-5" />
                </div>
                <span class="text-[10px] font-bold text-amber-700/65">ACTION</span>
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">{{ $arrivedCount }}</p>
            <p class="text-xs text-navy/45">Rider arrived</p>
        </button>


        <button
            type="button"
            data-summary-filter="picked_up"
            class="courier-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-teal/25"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center">
                    <x-lucide-package-check class="w-5 h-5" />
                </div>
                <span class="text-[10px] font-bold text-teal-dark/60">DONE</span>
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">{{ $pickedUpCount }}</p>
            <p class="text-xs text-navy/45">Recently picked up</p>
        </button>

    </section>


    {{-- =========================================================
        FILTERS
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl p-3">
        <div class="flex flex-col xl:flex-row xl:items-center gap-3">

            <div class="flex items-center gap-1 overflow-x-auto">
                @foreach ([
                    ['key' => 'all', 'label' => 'All', 'count' => $orders->count()],
                    ['key' => 'waiting_assignment', 'label' => 'Waiting', 'count' => $waitingCount],
                    ['key' => 'assigned', 'label' => 'Assigned', 'count' => $assignedCount],
                    ['key' => 'rider_arrived', 'label' => 'Rider Arrived', 'count' => $arrivedCount],
                    ['key' => 'picked_up', 'label' => 'Picked Up', 'count' => $pickedUpCount],
                ] as $tab)
                    <button
                        type="button"
                        data-courier-tab="{{ $tab['key'] }}"
                        class="courier-tab h-9 px-3.5 rounded-lg border border-transparent text-xs font-semibold whitespace-nowrap transition
                            {{ $tab['key'] === 'all' ? 'courier-tab-active' : 'text-navy/50 hover:bg-gray-bg' }}"
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
                    id="courierSearch"
                    placeholder="Search order, buyer, rider or waybill..."
                    class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/30 focus:outline-none focus:border-teal/50"
                >
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between gap-3">
            <p class="text-[10px] text-navy/35">
                Showing
                <strong id="courierVisibleCount" class="text-navy/60">
                    {{ $orders->count() }}
                </strong>
                of {{ $orders->count() }} orders
            </p>

            <button
                type="button"
                id="courierClearFilters"
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
                <x-lucide-truck class="w-5 h-5" />
            </div>

            <p class="text-sm font-semibold text-navy/55 mt-3">
                No orders waiting for pickup
            </p>

            <p class="text-xs text-navy/35 mt-1">
                Orders marked ready for pickup will appear here.
            </p>
        </section>

    @else

        <section id="courierCards" class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            @foreach ($orders as $order)
                @php
                    $meta = $pickupMeta[$order['pickup_status']]
                        ?? [
                            'label' => str_replace('_', ' ', $order['pickup_status']),
                            'class' => 'bg-navy/10 text-navy/50',
                            'icon' => 'circle',
                        ];

                    $riderName = $order['rider']['name'] ?? '';
                    $riderDisplay = $order['rider']['display'] ?? '';

                    $searchText = strtolower(
                        $order['id'] . ' ' .
                        $order['buyer'] . ' ' .
                        $order['waybill_no'] . ' ' .
                        $riderName . ' ' .
                        $riderDisplay . ' ' .
                        $order['delivery_area']
                    );

                    $orderJson = json_encode($order, JSON_HEX_APOS | JSON_HEX_QUOT);
                @endphp

                <article
                    class="courier-card bg-white border border-gray-border rounded-xl overflow-hidden"
                    data-courier-card
                    data-pickup-status="{{ strtolower($order['pickup_status']) }}"
                    data-search="{{ $searchText }}"
                    data-order='{{ $orderJson }}'
                >
                    {{-- Header --}}
                    <div class="px-4 sm:px-5 py-4 border-b border-gray-border">
                        <div class="flex flex-wrap items-start justify-between gap-3">

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-bold text-navy">
                                        {{ $order['id'] }}
                                    </p>

                                    <span
                                        data-pickup-badge
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $meta['class'] }}"
                                    >
                                        <x-dynamic-component
                                            :component="'lucide-' . $meta['icon']"
                                            class="w-3.5 h-3.5"
                                        />
                                        <span data-pickup-label>{{ $meta['label'] }}</span>
                                    </span>
                                </div>

                                <p class="text-xs text-navy/50 mt-1">
                                    {{ $order['buyer'] }}
                                    <span class="text-navy/25">·</span>
                                    {{ $order['delivery_area'] }}
                                </p>

                                <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-navy/40">
                                    <span class="inline-flex items-center gap-1">
                                        <x-lucide-clock-3 class="w-3.5 h-3.5" />
                                        Ready {{ $order['ready_at'] }}
                                    </span>

                                    <span class="inline-flex items-center gap-1">
                                        <x-lucide-calendar-clock class="w-3.5 h-3.5" />
                                        {{ $order['pickup_window'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <p class="text-xs font-bold text-navy">
                                    {{ $order['items_count'] }}
                                    item{{ $order['items_count'] === 1 ? '' : 's' }}
                                </p>

                                <p class="text-[10px] text-navy/35 mt-0.5">
                                    {{ $order['parcel_type'] }} · {{ $order['weight'] }}
                                </p>
                            </div>
                        </div>
                    </div>


                    {{-- Parcel summary --}}
                    <div class="px-4 sm:px-5 py-4 border-b border-gray-border">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div>
                                <p class="text-xs font-semibold text-navy">
                                    Parcel Ready for Handover
                                </p>
                                <p class="text-[10px] text-navy/40 mt-0.5">
                                    Verify the waybill and parcel before releasing it.
                                </p>
                            </div>

                            <x-lucide-package-check class="w-5 h-5 text-teal-dark shrink-0" />
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="rounded-xl bg-gray-bg p-3">
                                <p class="text-[10px] text-navy/35">Waybill</p>
                                <p class="text-[11px] font-semibold text-navy mt-1 break-all">
                                    {{ $order['waybill_no'] }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-gray-bg p-3">
                                <p class="text-[10px] text-navy/35">Weight</p>
                                <p class="text-[11px] font-semibold text-navy mt-1">
                                    {{ $order['weight'] }}
                                </p>
                            </div>

                            <div class="col-span-2 sm:col-span-1 rounded-xl bg-gray-bg p-3">
                                <p class="text-[10px] text-navy/35">Dimensions</p>
                                <p class="text-[11px] font-semibold text-navy mt-1">
                                    {{ $order['dimensions'] }}
                                </p>
                            </div>
                        </div>
                    </div>


                    {{-- Rider assignment --}}
                    <div class="px-4 sm:px-5 py-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-navy/35">
                                Rider Assignment
                            </p>

                            @if ($order['rider'] && $order['pickup_status'] !== 'PICKED_UP')
                                <span class="text-[10px] font-semibold {{ $order['pickup_status'] === 'RIDER_ARRIVED' ? 'text-amber-700' : 'text-sky' }}">
                                    {{ $order['rider']['eta'] }}
                                </span>
                            @endif
                        </div>

                        @if ($order['rider'])
                            <div class="rounded-xl border border-gray-border p-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-sky/10 text-sky flex items-center justify-center shrink-0">
                                        <x-lucide-bike class="w-5 h-5" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <p class="text-xs font-bold text-navy">
                                                    {{ $order['rider']['name'] }}
                                                </p>

                                                <p class="text-[10px] text-navy/40 mt-0.5">
                                                    {{ $order['rider']['id'] }}
                                                </p>
                                            </div>

                                            @if ($order['pickup_status'] === 'RIDER_ARRIVED')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-yellow/20 px-2 py-1 text-[10px] font-bold text-amber-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    AT SELLER
                                                </span>
                                            @elseif ($order['pickup_status'] === 'PICKED_UP')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-teal/10 px-2 py-1 text-[10px] font-bold text-teal-dark">
                                                    <x-lucide-circle-check class="w-3 h-3" />
                                                    COLLECTED
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-navy/45">
                                            <span class="inline-flex items-center gap-1">
                                                <x-lucide-phone class="w-3 h-3" />
                                                {{ $order['rider']['phone'] }}
                                            </span>

                                            <span class="inline-flex items-center gap-1">
                                                <x-lucide-bike class="w-3 h-3" />
                                                {{ $order['rider']['vehicle'] }}
                                            </span>

                                            <span class="inline-flex items-center gap-1">
                                                <x-lucide-badge class="w-3 h-3" />
                                                {{ $order['rider']['plate'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @else
                            <div class="rounded-xl border border-dashed border-gray-border bg-gray-bg/40 p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-navy/10 text-navy/45 flex items-center justify-center shrink-0">
                                        <x-lucide-loader-circle class="w-4 h-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold text-navy">
                                            Waiting for rider assignment
                                        </p>

                                        <p class="text-[10px] text-navy/40 mt-1 leading-relaxed">
                                            The parcel is ready. Once logistics assigns a rider,
                                            the rider details will appear here.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>


                    {{-- Picked up summary --}}
                    @if ($order['pickup_status'] === 'PICKED_UP')
                        <div class="mx-4 sm:mx-5 mb-4 rounded-xl border border-teal/25 bg-teal-light p-3">
                            <div class="flex items-start gap-2.5">
                                <x-lucide-circle-check class="w-4 h-4 text-teal-dark mt-0.5 shrink-0" />

                                <div>
                                    <p class="text-xs font-semibold text-teal-dark">
                                        Parcel successfully handed over
                                    </p>

                                    <p class="text-[10px] text-navy/45 mt-0.5">
                                        Picked up {{ $order['picked_up_at'] ?? '' }} by {{ $order['rider']['name'] ?? 'assigned rider' }}.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif


                    {{-- Actions --}}
                    <div class="px-4 sm:px-5 py-4 border-t border-gray-border bg-gray-bg/25">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    data-view-courier-order
                                    class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-border bg-white text-xs font-semibold text-navy/60 hover:bg-gray-bg hover:text-navy transition"
                                >
                                    <x-lucide-eye class="w-4 h-4" />
                                    View Parcel
                                </button>

                                @if (!$order['rider'])
                                    <button
                                        type="button"
                                        data-request-pickup
                                        class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-teal/25 bg-teal/5 text-xs font-semibold text-teal-dark hover:bg-teal/10 transition"
                                    >
                                        <x-lucide-send class="w-4 h-4" />
                                        Pickup Request
                                    </button>
                                @endif
                            </div>


                            @if ($order['pickup_status'] === 'PICKED_UP')
                                <button
                                    type="button"
                                    disabled
                                    class="h-9 px-4 rounded-lg bg-teal/10 text-xs font-semibold text-teal-dark cursor-default"
                                >
                                    Picked Up
                                </button>

                            @elseif ($order['pickup_status'] === 'RIDER_ARRIVED')
                                <button
                                    type="button"
                                    data-confirm-handover
                                    class="h-9 px-4 rounded-lg bg-navy hover:bg-navy/90 text-xs font-semibold text-white transition"
                                >
                                    Verify & Confirm Pickup
                                </button>

                            @elseif ($order['rider'])
                                <button
                                    type="button"
                                    disabled
                                    class="h-9 px-4 rounded-lg bg-navy text-xs font-semibold text-white opacity-40 cursor-not-allowed"
                                >
                                    Waiting for Rider
                                </button>

                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="h-9 px-4 rounded-lg bg-navy text-xs font-semibold text-white opacity-40 cursor-not-allowed"
                                >
                                    Awaiting Assignment
                                </button>
                            @endif

                        </div>

                        @if ($order['pickup_status'] === 'RIDER_ARRIVED')
                            <p class="mt-2 text-[10px] text-amber-700 sm:text-right">
                                Rider is at the seller location. Verify identity and parcel before handover.
                            </p>
                        @elseif ($order['pickup_status'] === 'RIDER_ASSIGNED')
                            <p class="mt-2 text-[10px] text-navy/35 sm:text-right">
                                Pickup confirmation will become available once the rider arrives.
                            </p>
                        @elseif ($order['pickup_status'] === 'WAITING_ASSIGNMENT')
                            <p class="mt-2 text-[10px] text-navy/35 sm:text-right">
                                Waiting for logistics to assign a rider.
                            </p>
                        @endif
                    </div>

                </article>
            @endforeach

        </section>


        {{-- No results --}}
        <section
            id="courierNoResults"
            hidden
            class="bg-white border border-gray-border rounded-xl py-12 text-center"
        >
            <div class="w-11 h-11 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                <x-lucide-search-x class="w-5 h-5" />
            </div>

            <p class="text-sm font-semibold text-navy/55 mt-3">
                No matching pickup orders
            </p>

            <p class="text-xs text-navy/35 mt-1">
                Try another order, rider, or status filter.
            </p>
        </section>

    @endif

</div>


{{-- =========================================================
    PARCEL / ORDER DETAILS MODAL
========================================================= --}}
<div id="courierOrderModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div data-close-courier-order class="absolute inset-0 bg-navy/45"></div>

    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-xl max-h-[90vh] overflow-y-auto content-scrollbar">
        <div class="sticky top-0 z-10 bg-white flex items-center justify-between px-5 py-4 border-b border-gray-border">
            <div>
                <p class="text-base font-bold text-navy">
                    Parcel Details
                </p>

                <p id="parcelOrderId" class="text-[11px] text-navy/40 mt-0.5"></p>
            </div>

            <button
                type="button"
                data-close-courier-order
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>


        <div class="px-5 py-5 space-y-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                        Buyer
                    </p>

                    <p id="parcelBuyer" class="text-xs font-semibold text-navy mt-1"></p>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                        Delivery Area
                    </p>

                    <p id="parcelArea" class="text-xs text-navy/70 mt-1"></p>
                </div>
            </div>


            <div>
                <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                    Delivery Address
                </p>

                <p id="parcelAddress" class="text-xs text-navy/70 mt-1 leading-relaxed"></p>
            </div>


            <div>
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                        Parcel Items
                    </p>

                    <span id="parcelItemCount" class="text-[10px] text-navy/30"></span>
                </div>

                <div id="parcelItems" class="mt-2 space-y-2"></div>
            </div>


            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-xl bg-gray-bg p-3">
                    <p class="text-[10px] text-navy/35">Type</p>
                    <p id="parcelType" class="text-xs font-semibold text-navy mt-1"></p>
                </div>

                <div class="rounded-xl bg-gray-bg p-3">
                    <p class="text-[10px] text-navy/35">Weight</p>
                    <p id="parcelWeight" class="text-xs font-semibold text-navy mt-1"></p>
                </div>

                <div class="col-span-2 sm:col-span-2 rounded-xl bg-gray-bg p-3">
                    <p class="text-[10px] text-navy/35">Dimensions</p>
                    <p id="parcelDimensions" class="text-xs font-semibold text-navy mt-1"></p>
                </div>
            </div>


            <div class="rounded-xl border border-gray-border p-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
                        <x-lucide-barcode class="w-5 h-5" />
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] text-navy/35">
                            Waybill Number
                        </p>

                        <p id="parcelWaybill" class="text-sm font-bold text-navy mt-0.5 break-all"></p>
                    </div>
                </div>
            </div>


            <div id="parcelRiderWrap">
                <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                    Assigned Rider
                </p>

                <div class="mt-2 rounded-xl border border-gray-border p-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-sky/10 text-sky flex items-center justify-center shrink-0">
                            <x-lucide-bike class="w-5 h-5" />
                        </div>

                        <div>
                            <p id="parcelRiderName" class="text-xs font-bold text-navy"></p>
                            <p id="parcelRiderDetails" class="text-[10px] text-navy/40 mt-1"></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


{{-- =========================================================
    PICKUP REQUEST MODAL
========================================================= --}}
<div id="pickupRequestModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div data-close-pickup-request class="absolute inset-0 bg-navy/45"></div>

    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-md">
        <div class="p-5">
            <div class="w-11 h-11 rounded-xl bg-teal/10 text-teal-dark flex items-center justify-center">
                <x-lucide-send class="w-5 h-5" />
            </div>

            <h2 class="text-base font-bold text-navy mt-4">
                Pickup Request
            </h2>

            <p class="text-xs text-navy/50 mt-2 leading-relaxed">
                This frontend demo represents the pickup request that will later be sent
                to your logistics or sorting center for rider assignment.
            </p>


            <div class="mt-4 rounded-xl bg-gray-bg p-3">
                <p class="text-[10px] text-navy/35">Order</p>
                <p id="pickupRequestOrderId" class="text-sm font-bold text-navy mt-0.5"></p>

                <div class="mt-3 pt-3 border-t border-gray-border">
                    <p class="text-[10px] text-navy/35">Preferred Pickup Window</p>

                    <select
                        id="pickupWindowSelect"
                        class="mt-1.5 w-full h-9 px-3 rounded-lg border border-gray-border bg-white text-xs text-navy focus:outline-none focus:border-teal/50"
                    >
                        <option>As soon as possible</option>
                        <option>Within 1 hour</option>
                        <option>1:00 PM – 3:00 PM</option>
                        <option>3:00 PM – 5:00 PM</option>
                    </select>
                </div>
            </div>


            <div class="mt-5 flex items-center justify-end gap-2">
                <button
                    type="button"
                    data-close-pickup-request
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    id="confirmPickupRequestButton"
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                >
                    Send Request
                </button>
            </div>
        </div>
    </div>
</div>


{{-- =========================================================
    HANDOVER VERIFICATION MODAL
========================================================= --}}
<div id="handoverModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div data-close-handover class="absolute inset-0 bg-navy/45"></div>

    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-lg max-h-[90vh] overflow-y-auto content-scrollbar">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">
            <div>
                <p class="text-base font-bold text-navy">
                    Verify Courier Handover
                </p>

                <p id="handoverOrderId" class="text-[11px] text-navy/40 mt-0.5"></p>
            </div>

            <button
                type="button"
                data-close-handover
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>


        <div class="px-5 py-5">

            <div class="rounded-xl border border-sky/20 bg-sky/5 p-3">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-full bg-sky/10 text-sky flex items-center justify-center shrink-0">
                        <x-lucide-bike class="w-4 h-4" />
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-sky">
                            Assigned Rider
                        </p>

                        <p id="handoverRiderName" class="text-sm font-bold text-navy mt-0.5"></p>
                        <p id="handoverRiderInfo" class="text-[10px] text-navy/45 mt-1"></p>
                    </div>
                </div>
            </div>


            <div class="mt-5">
                <p class="text-xs font-bold text-navy">
                    Handover Checklist
                </p>

                <p class="text-[10px] text-navy/40 mt-0.5">
                    Complete these checks before releasing the parcel.
                </p>


                <div class="mt-3 space-y-2.5">
                    <label class="flex items-start gap-3 rounded-xl border border-gray-border p-3 cursor-pointer hover:border-teal/30 transition">
                        <input
                            type="checkbox"
                            data-handover-check
                            class="handover-check w-4 h-4 mt-0.5 rounded border-gray-border focus:ring-teal/40"
                        >

                        <div>
                            <p class="text-xs font-semibold text-navy">
                                Rider identity verified
                            </p>

                            <p class="text-[10px] text-navy/40 mt-0.5">
                                Rider name, ID, vehicle, and plate match the assignment.
                            </p>
                        </div>
                    </label>


                    <label class="flex items-start gap-3 rounded-xl border border-gray-border p-3 cursor-pointer hover:border-teal/30 transition">
                        <input
                            type="checkbox"
                            data-handover-check
                            class="handover-check w-4 h-4 mt-0.5 rounded border-gray-border focus:ring-teal/40"
                        >

                        <div>
                            <p class="text-xs font-semibold text-navy">
                                Waybill and parcel verified
                            </p>

                            <p class="text-[10px] text-navy/40 mt-0.5">
                                The parcel waybill matches the order being released.
                            </p>
                        </div>
                    </label>


                    <label class="flex items-start gap-3 rounded-xl border border-gray-border p-3 cursor-pointer hover:border-teal/30 transition">
                        <input
                            type="checkbox"
                            data-handover-check
                            class="handover-check w-4 h-4 mt-0.5 rounded border-gray-border focus:ring-teal/40"
                        >

                        <div>
                            <p class="text-xs font-semibold text-navy">
                                Parcel physically handed to rider
                            </p>

                            <p class="text-[10px] text-navy/40 mt-0.5">
                                Confirm only after the assigned rider has the parcel.
                            </p>
                        </div>
                    </label>
                </div>
            </div>


            <div class="mt-5 rounded-xl bg-gray-bg p-3">
                <label for="handoverCodeInput" class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                    Handover Verification Code
                </label>

                <p class="text-[10px] text-navy/40 mt-1">
                    Demo safety check before pickup confirmation.
                </p>

                <input
                    type="text"
                    id="handoverCodeInput"
                    inputmode="numeric"
                    maxlength="4"
                    placeholder="Enter 4-digit code"
                    class="mt-2 w-full h-10 px-3 rounded-lg border border-gray-border bg-white text-sm font-semibold tracking-[0.25em] text-navy placeholder:tracking-normal placeholder:font-normal placeholder:text-navy/25 focus:outline-none focus:border-teal/50"
                >

                <p
                    id="handoverCodeError"
                    hidden
                    class="mt-1.5 text-[10px] font-semibold text-red-500"
                >
                    The verification code does not match.
                </p>
            </div>


            <div class="mt-5 flex items-center justify-end gap-2">
                <button
                    type="button"
                    data-close-handover
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    id="confirmHandoverButton"
                    disabled
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Confirm Rider Pickup
                </button>
            </div>

        </div>
    </div>
</div>


{{-- =========================================================
    DEMO TOAST
========================================================= --}}
<div
    id="courierToast"
    hidden
    class="fixed right-4 bottom-4 z-[60] max-w-sm rounded-xl border border-teal/25 bg-white shadow-panel px-4 py-3"
>
    <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
            <x-lucide-circle-check class="w-4 h-4" />
        </div>

        <div>
            <p class="text-xs font-bold text-navy">
                Courier handover updated
            </p>

            <p id="courierToastMessage" class="text-[11px] text-navy/45 mt-0.5"></p>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const cards = Array.from(document.querySelectorAll('[data-courier-card]'));
    const searchInput = document.getElementById('courierSearch');
    const tabs = Array.from(document.querySelectorAll('[data-courier-tab]'));
    const visibleCount = document.getElementById('courierVisibleCount');
    const noResults = document.getElementById('courierNoResults');

    const orderModal = document.getElementById('courierOrderModal');
    const pickupRequestModal = document.getElementById('pickupRequestModal');
    const handoverModal = document.getElementById('handoverModal');

    const toast = document.getElementById('courierToast');
    const toastMessage = document.getElementById('courierToastMessage');

    const handoverChecks = Array.from(document.querySelectorAll('[data-handover-check]'));
    const handoverCodeInput = document.getElementById('handoverCodeInput');
    const handoverCodeError = document.getElementById('handoverCodeError');
    const confirmHandoverButton = document.getElementById('confirmHandoverButton');

    let activeFilter = 'all';
    let activeCard = null;
    let activeOrder = null;
    let toastTimer = null;


    function parseCardOrder(card) {
        try {
            return JSON.parse(card.getAttribute('data-order') || '{}');
        } catch (error) {
            return {};
        }
    }


    function setBodyLock(locked) {
        document.body.style.overflow = locked ? 'hidden' : '';
    }


    function showToast(message) {
        if (!toast) return;

        toastMessage.textContent = message;
        toast.hidden = false;

        if (toastTimer) clearTimeout(toastTimer);

        toastTimer = setTimeout(function () {
            toast.hidden = true;
        }, 3200);
    }


    /* ---------------------------------------------------------
       FILTERS
    --------------------------------------------------------- */
    function matchesFilter(card, filter) {
        const status = card.dataset.pickupStatus || '';

        if (filter === 'all') return true;
        if (filter === 'waiting_assignment') return status === 'waiting_assignment';
        if (filter === 'assigned') return status === 'rider_assigned' || status === 'rider_arrived';
        if (filter === 'rider_arrived') return status === 'rider_arrived';
        if (filter === 'picked_up') return status === 'picked_up';

        return true;
    }


    function applyFilters() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let shown = 0;

        cards.forEach(function (card) {
            const statusMatch = matchesFilter(card, activeFilter);
            const searchMatch =
                !query ||
                (card.dataset.search || '').includes(query);

            const show = statusMatch && searchMatch;
            card.hidden = !show;

            if (show) shown++;
        });

        if (visibleCount) visibleCount.textContent = shown;
        if (noResults) noResults.hidden = shown !== 0;

        tabs.forEach(function (tab) {
            const active = tab.dataset.courierTab === activeFilter;

            tab.classList.toggle('courier-tab-active', active);
            tab.classList.toggle('text-navy/50', !active);
            tab.classList.toggle('hover:bg-gray-bg', !active);
        });
    }


    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activeFilter = tab.dataset.courierTab || 'all';
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


    document.getElementById('courierClearFilters')?.addEventListener('click', function () {
        activeFilter = 'all';

        if (searchInput) {
            searchInput.value = '';
        }

        applyFilters();
    });


    /* ---------------------------------------------------------
       PARCEL DETAILS
    --------------------------------------------------------- */
    function openParcelDetails(card) {
        const order = parseCardOrder(card);

        document.getElementById('parcelOrderId').textContent =
            order.id + ' · ' + String(order.pickup_status || '').replaceAll('_', ' ');

        document.getElementById('parcelBuyer').textContent = order.buyer || '';
        document.getElementById('parcelArea').textContent = order.delivery_area || '';
        document.getElementById('parcelAddress').textContent = order.address || '';
        document.getElementById('parcelType').textContent = order.parcel_type || '—';
        document.getElementById('parcelWeight').textContent = order.weight || '—';
        document.getElementById('parcelDimensions').textContent = order.dimensions || '—';
        document.getElementById('parcelWaybill').textContent = order.waybill_no || '—';

        const totalQty = (order.items || []).reduce(
            (sum, item) => sum + Number(item.qty || 0),
            0
        );

        document.getElementById('parcelItemCount').textContent =
            totalQty + ' item' + (totalQty === 1 ? '' : 's');

        const itemsWrap = document.getElementById('parcelItems');
        itemsWrap.innerHTML = '';

        (order.items || []).forEach(function (item) {
            const row = document.createElement('div');

            row.className =
                'flex items-center gap-3 rounded-xl border border-gray-border p-3';

            row.innerHTML = `
                <div class="w-12 h-12 rounded-lg bg-gray-bg border border-gray-border overflow-hidden shrink-0">
                    ${
                        item.image
                            ? `<img src="${item.image}" alt="" class="w-full h-full object-cover">`
                            : `<div class="w-full h-full flex items-center justify-center text-[10px] text-navy/30">No image</div>`
                    }
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-navy">
                        ${item.name || ''}
                    </p>

                    <p class="text-[10px] text-navy/40 mt-0.5">
                        ${item.variant || 'Default variant'}
                    </p>

                    <p class="text-[10px] text-navy/30 mt-0.5">
                        SKU: ${item.sku || '—'}
                    </p>
                </div>

                <div class="text-right shrink-0">
                    <p class="text-xs font-bold text-navy">
                        ×${item.qty || 0}
                    </p>
                </div>
            `;

            itemsWrap.appendChild(row);
        });


        const riderWrap = document.getElementById('parcelRiderWrap');

        if (order.rider) {
            riderWrap.hidden = false;

            document.getElementById('parcelRiderName').textContent =
                order.rider.name || '';

            document.getElementById('parcelRiderDetails').textContent =
                [
                    order.rider.id,
                    order.rider.vehicle,
                    order.rider.plate,
                    order.rider.phone
                ].filter(Boolean).join(' · ');
        } else {
            riderWrap.hidden = true;
        }


        orderModal.hidden = false;
        setBodyLock(true);
    }


    document.querySelectorAll('[data-view-courier-order]').forEach(function (button) {
        button.addEventListener('click', function () {
            openParcelDetails(
                button.closest('[data-courier-card]')
            );
        });
    });


    document.querySelectorAll('[data-close-courier-order]').forEach(function (button) {
        button.addEventListener('click', function () {
            orderModal.hidden = true;
            setBodyLock(false);
        });
    });


    /* ---------------------------------------------------------
       PICKUP REQUEST DEMO
    --------------------------------------------------------- */
    document.querySelectorAll('[data-request-pickup]').forEach(function (button) {
        button.addEventListener('click', function () {
            activeCard = button.closest('[data-courier-card]');
            activeOrder = parseCardOrder(activeCard);

            document.getElementById('pickupRequestOrderId').textContent =
                activeOrder.id || '';

            pickupRequestModal.hidden = false;
            setBodyLock(true);
        });
    });


    document.querySelectorAll('[data-close-pickup-request]').forEach(function (button) {
        button.addEventListener('click', function () {
            pickupRequestModal.hidden = true;
            setBodyLock(false);
        });
    });


    document.getElementById('confirmPickupRequestButton')?.addEventListener('click', function () {
        if (!activeOrder) return;

        const preferredWindow =
            document.getElementById('pickupWindowSelect')?.value || 'selected window';

        pickupRequestModal.hidden = true;
        setBodyLock(false);

        showToast(
            'Pickup request for ' +
            activeOrder.id +
            ' sent for ' +
            preferredWindow +
            '.'
        );
    });


    /* ---------------------------------------------------------
       HANDOVER VERIFICATION
    --------------------------------------------------------- */
    function refreshHandoverButton() {
        const allChecked =
            handoverChecks.length > 0 &&
            handoverChecks.every(function (checkbox) {
                return checkbox.checked;
            });

        confirmHandoverButton.disabled = !allChecked;
    }


    handoverChecks.forEach(function (checkbox) {
        checkbox.addEventListener('change', refreshHandoverButton);
    });


    handoverCodeInput?.addEventListener('input', function () {
        handoverCodeError.hidden = true;

        handoverCodeInput.value =
            handoverCodeInput.value
                .replace(/\D/g, '')
                .slice(0, 4);
    });


    document.querySelectorAll('[data-confirm-handover]').forEach(function (button) {
        button.addEventListener('click', function () {
            activeCard = button.closest('[data-courier-card]');
            activeOrder = parseCardOrder(activeCard);

            document.getElementById('handoverOrderId').textContent =
                activeOrder.id || '';

            document.getElementById('handoverRiderName').textContent =
                activeOrder.rider?.name || '';

            document.getElementById('handoverRiderInfo').textContent =
                [
                    activeOrder.rider?.id,
                    activeOrder.rider?.vehicle,
                    activeOrder.rider?.plate,
                    activeOrder.rider?.phone
                ].filter(Boolean).join(' · ');

            handoverChecks.forEach(function (checkbox) {
                checkbox.checked = false;
            });

            if (handoverCodeInput) {
                handoverCodeInput.value = '';
            }

            handoverCodeError.hidden = true;

            refreshHandoverButton();

            handoverModal.hidden = false;
            setBodyLock(true);
        });
    });


    document.querySelectorAll('[data-close-handover]').forEach(function (button) {
        button.addEventListener('click', function () {
            handoverModal.hidden = true;
            setBodyLock(false);
        });
    });


    confirmHandoverButton?.addEventListener('click', function () {
        if (!activeCard || !activeOrder) return;

        const enteredCode =
            (handoverCodeInput?.value || '').trim();

        const expectedCode =
            String(activeOrder.handover_code || '');

        if (enteredCode !== expectedCode) {
            handoverCodeError.hidden = false;
            handoverCodeInput?.focus();
            return;
        }


        activeCard.dataset.pickupStatus = 'picked_up';

        activeOrder.pickup_status = 'PICKED_UP';
        activeOrder.status = 'PICKED_UP';
        activeOrder.picked_up_at = 'Just now';

        activeCard.setAttribute(
            'data-order',
            JSON.stringify(activeOrder)
        );


        const badge = activeCard.querySelector('[data-pickup-badge]');
        const label = activeCard.querySelector('[data-pickup-label]');

        if (badge) {
            badge.className =
                'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal/10 text-teal-dark';
        }

        if (label) {
            label.textContent = 'Picked Up';
        }


        const actionButton =
            activeCard.querySelector('[data-confirm-handover]');

        if (actionButton) {
            actionButton.disabled = true;
            actionButton.textContent = 'Picked Up';
            actionButton.className =
                'h-9 px-4 rounded-lg bg-teal/10 text-xs font-semibold text-teal-dark cursor-default';
        }


        handoverModal.hidden = true;
        setBodyLock(false);

        showToast(
            activeOrder.id +
            ' was handed over to ' +
            (activeOrder.rider?.name || 'the assigned rider') +
            '. Status is now PICKED_UP.'
        );

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

        if (!pickupRequestModal.hidden) {
            pickupRequestModal.hidden = true;
            setBodyLock(false);
        }

        if (!handoverModal.hidden) {
            handoverModal.hidden = true;
            setBodyLock(false);
        }
    });


    applyFilters();
});
</script>
@endpush

@endsection
