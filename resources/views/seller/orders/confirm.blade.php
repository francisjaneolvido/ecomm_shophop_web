@extends('seller.partials.layout')

@section('title', 'Confirm Delivery')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    | This page is intentionally hardcoded for UI/UX development.
    |
    | IMPORTANT:
    | The seller does NOT manually mark an order DELIVERED or COMPLETED here.
    | Those states should eventually come from courier/logistics updates and
    | buyer receipt confirmation.
    |
    | Your backend teammate can later replace this collection with controller
    | data and connect shipment events, proof of delivery, failed delivery
    | reasons, return handling, notifications, and buyer confirmation.
    */

    $orders = collect($orders ?? [
        [
            'id' => 'ORD-10218',
            'buyer' => 'Ronald Cabrera',
            'status' => 'OUT_FOR_DELIVERY',
            'updated_at' => 'Today, 1:40 PM',
            'ordered_at' => 'Sep 8, 2026 · 8:24 AM',
            'delivery_area' => 'Calamba, Laguna',
            'address' => 'Purok 3, Brgy. Halang, Calamba, Laguna',
            'waybill_no' => 'WB-10218-0908',
            'parcel_type' => 'Box',
            'weight' => '1.2 kg',
            'items_count' => 2,
            'buyer_confirmed_at' => null,
            'delivered_at' => null,
            'failure_reason' => null,
            'return_status' => null,
            'proof' => null,
            'rider' => [
                'id' => 'RDR-011',
                'name' => 'Miguel Santos',
                'phone' => '09•• ••• •629',
                'vehicle' => 'Motorcycle',
                'plate' => 'DAB 2190',
            ],
            'sorting_center' => [
                'name' => 'Laguna Sorting Hub',
                'location' => 'Calamba, Laguna',
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
            'events' => [
                ['status' => 'PICKED_UP', 'label' => 'Picked Up', 'time' => 'Today, 10:18 AM', 'detail' => 'Parcel collected from seller by Rider 11.'],
                ['status' => 'AT_SORTING_CENTER', 'label' => 'At Sorting Center', 'time' => 'Today, 10:52 AM', 'detail' => 'Parcel arrived at Laguna Sorting Hub.'],
                ['status' => 'SORTED', 'label' => 'Sorted', 'time' => 'Today, 11:24 AM', 'detail' => 'Parcel sorted for Calamba delivery area.'],
                ['status' => 'ASSIGNED_TO_RIDER', 'label' => 'Assigned to Rider', 'time' => 'Today, 12:15 PM', 'detail' => 'Assigned to Miguel Santos for final delivery.'],
                ['status' => 'OUT_FOR_DELIVERY', 'label' => 'Out for Delivery', 'time' => 'Today, 1:40 PM', 'detail' => 'Rider is currently delivering the parcel.'],
            ],
        ],

        [
            'id' => 'ORD-10210',
            'buyer' => 'Trisha Ang',
            'status' => 'DELIVERED',
            'updated_at' => 'Yesterday, 5:12 PM',
            'ordered_at' => 'Sep 7, 2026 · 9:05 AM',
            'delivery_area' => 'Los Baños, Laguna',
            'address' => 'Brgy. Batong Malake, Los Baños, Laguna',
            'waybill_no' => 'WB-10210-0907',
            'parcel_type' => 'Pouch',
            'weight' => '0.7 kg',
            'items_count' => 1,
            'buyer_confirmed_at' => null,
            'delivered_at' => 'Yesterday, 5:12 PM',
            'failure_reason' => null,
            'return_status' => null,
            'proof' => [
                'type' => 'Photo Proof',
                'recipient' => 'Trisha Ang',
                'note' => 'Received by customer at delivery address.',
                'image' => 'https://images.unsplash.com/photo-1616401784845-180882ba9ba8?auto=format&fit=crop&w=700&q=80',
            ],
            'rider' => [
                'id' => 'RDR-006',
                'name' => 'Jomar Cruz',
                'phone' => '09•• ••• •488',
                'vehicle' => 'Motorcycle',
                'plate' => 'NCT 7132',
            ],
            'sorting_center' => [
                'name' => 'Laguna Sorting Hub',
                'location' => 'Calamba, Laguna',
            ],
            'items' => [
                [
                    'name' => 'Barako Coffee Beans 250g',
                    'variant' => 'Medium Roast',
                    'sku' => 'BARAKO-250-MR',
                    'qty' => 2,
                    'image' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=240&q=80',
                ],
            ],
            'events' => [
                ['status' => 'PICKED_UP', 'label' => 'Picked Up', 'time' => 'Yesterday, 10:02 AM', 'detail' => 'Parcel collected from seller.'],
                ['status' => 'AT_SORTING_CENTER', 'label' => 'At Sorting Center', 'time' => 'Yesterday, 11:04 AM', 'detail' => 'Parcel arrived at Laguna Sorting Hub.'],
                ['status' => 'SORTED', 'label' => 'Sorted', 'time' => 'Yesterday, 12:18 PM', 'detail' => 'Parcel sorted for Los Baños delivery area.'],
                ['status' => 'ASSIGNED_TO_RIDER', 'label' => 'Assigned to Rider', 'time' => 'Yesterday, 1:06 PM', 'detail' => 'Assigned to Jomar Cruz.'],
                ['status' => 'OUT_FOR_DELIVERY', 'label' => 'Out for Delivery', 'time' => 'Yesterday, 2:30 PM', 'detail' => 'Parcel left sorting center for delivery.'],
                ['status' => 'DELIVERED', 'label' => 'Delivered', 'time' => 'Yesterday, 5:12 PM', 'detail' => 'Courier marked parcel successfully delivered.'],
            ],
        ],

        [
            'id' => 'ORD-10204',
            'buyer' => 'Miguel Ortiz',
            'status' => 'COMPLETED',
            'updated_at' => '2 days ago · 6:03 PM',
            'ordered_at' => 'Sep 5, 2026 · 2:40 PM',
            'delivery_area' => 'Santa Cruz, Laguna',
            'address' => 'Brgy. Poblacion II, Santa Cruz, Laguna',
            'waybill_no' => 'WB-10204-0905',
            'parcel_type' => 'Box',
            'weight' => '0.9 kg',
            'items_count' => 1,
            'buyer_confirmed_at' => 'Sep 6, 2026 · 6:03 PM',
            'delivered_at' => 'Sep 6, 2026 · 5:36 PM',
            'failure_reason' => null,
            'return_status' => null,
            'proof' => [
                'type' => 'Photo Proof',
                'recipient' => 'Miguel Ortiz',
                'note' => 'Parcel received personally by buyer.',
                'image' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=700&q=80',
            ],
            'rider' => [
                'id' => 'RDR-003',
                'name' => 'Leo Martinez',
                'phone' => '09•• ••• •145',
                'vehicle' => 'Motorcycle',
                'plate' => 'NDA 2051',
            ],
            'sorting_center' => [
                'name' => 'Laguna Sorting Hub',
                'location' => 'Calamba, Laguna',
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
            'events' => [
                ['status' => 'PICKED_UP', 'label' => 'Picked Up', 'time' => 'Sep 6, 9:10 AM', 'detail' => 'Parcel collected from seller.'],
                ['status' => 'AT_SORTING_CENTER', 'label' => 'At Sorting Center', 'time' => 'Sep 6, 10:20 AM', 'detail' => 'Parcel received by sorting center.'],
                ['status' => 'SORTED', 'label' => 'Sorted', 'time' => 'Sep 6, 11:12 AM', 'detail' => 'Parcel sorted for Santa Cruz area.'],
                ['status' => 'ASSIGNED_TO_RIDER', 'label' => 'Assigned to Rider', 'time' => 'Sep 6, 1:00 PM', 'detail' => 'Assigned to Leo Martinez.'],
                ['status' => 'OUT_FOR_DELIVERY', 'label' => 'Out for Delivery', 'time' => 'Sep 6, 2:15 PM', 'detail' => 'Rider started final delivery.'],
                ['status' => 'DELIVERED', 'label' => 'Delivered', 'time' => 'Sep 6, 5:36 PM', 'detail' => 'Parcel successfully delivered.'],
                ['status' => 'COMPLETED', 'label' => 'Completed', 'time' => 'Sep 6, 6:03 PM', 'detail' => 'Buyer confirmed receipt.'],
            ],
        ],

        [
            'id' => 'ORD-10198',
            'buyer' => 'Anna Reyes',
            'status' => 'DELIVERY_FAILED',
            'updated_at' => '3 days ago · 4:45 PM',
            'ordered_at' => 'Sep 4, 2026 · 11:20 AM',
            'delivery_area' => 'Pagsanjan, Laguna',
            'address' => 'Brgy. Sampaloc, Pagsanjan, Laguna',
            'waybill_no' => 'WB-10198-0904',
            'parcel_type' => 'Box',
            'weight' => '1.4 kg',
            'items_count' => 2,
            'buyer_confirmed_at' => null,
            'delivered_at' => null,
            'failure_reason' => 'Customer unavailable at the delivery address after courier contact attempt.',
            'return_status' => 'Reschedule requested by logistics',
            'proof' => null,
            'rider' => [
                'id' => 'RDR-009',
                'name' => 'Andre Bautista',
                'phone' => '09•• ••• •902',
                'vehicle' => 'Motorcycle',
                'plate' => 'NEF 8204',
            ],
            'sorting_center' => [
                'name' => 'Laguna Sorting Hub',
                'location' => 'Calamba, Laguna',
            ],
            'items' => [
                [
                    'name' => 'Handwoven Rattan Basket',
                    'variant' => 'Natural / Medium',
                    'sku' => 'RATTAN-NAT-M',
                    'qty' => 1,
                    'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=240&q=80',
                ],
                [
                    'name' => 'Portable Mini Fan',
                    'variant' => 'White',
                    'sku' => 'FAN-MINI-WHT',
                    'qty' => 1,
                    'image' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=240&q=80',
                ],
            ],
            'events' => [
                ['status' => 'PICKED_UP', 'label' => 'Picked Up', 'time' => 'Sep 5, 9:02 AM', 'detail' => 'Parcel collected from seller.'],
                ['status' => 'AT_SORTING_CENTER', 'label' => 'At Sorting Center', 'time' => 'Sep 5, 10:18 AM', 'detail' => 'Parcel received by Laguna Sorting Hub.'],
                ['status' => 'SORTED', 'label' => 'Sorted', 'time' => 'Sep 5, 11:05 AM', 'detail' => 'Parcel sorted for Pagsanjan area.'],
                ['status' => 'ASSIGNED_TO_RIDER', 'label' => 'Assigned to Rider', 'time' => 'Sep 5, 1:22 PM', 'detail' => 'Assigned to Andre Bautista.'],
                ['status' => 'OUT_FOR_DELIVERY', 'label' => 'Out for Delivery', 'time' => 'Sep 5, 2:10 PM', 'detail' => 'Rider started delivery attempt.'],
                ['status' => 'DELIVERY_FAILED', 'label' => 'Delivery Failed', 'time' => 'Sep 5, 4:45 PM', 'detail' => 'Customer unavailable at delivery address.'],
            ],
        ],

        [
            'id' => 'ORD-10191',
            'buyer' => 'Jessa Lim',
            'status' => 'RETURNED',
            'updated_at' => '5 days ago · 2:16 PM',
            'ordered_at' => 'Sep 1, 2026 · 3:15 PM',
            'delivery_area' => 'San Pablo, Laguna',
            'address' => 'Brgy. San Roque, San Pablo, Laguna',
            'waybill_no' => 'WB-10191-0901',
            'parcel_type' => 'Pouch',
            'weight' => '0.5 kg',
            'items_count' => 1,
            'buyer_confirmed_at' => null,
            'delivered_at' => null,
            'failure_reason' => 'Multiple unsuccessful delivery attempts.',
            'return_status' => 'Returned to seller',
            'proof' => null,
            'rider' => [
                'id' => 'RDR-014',
                'name' => 'Renz Mercado',
                'phone' => '09•• ••• •512',
                'vehicle' => 'Motorcycle',
                'plate' => 'NFR 1128',
            ],
            'sorting_center' => [
                'name' => 'Laguna Sorting Hub',
                'location' => 'Calamba, Laguna',
            ],
            'items' => [
                [
                    'name' => 'Classic Oversized Shirt',
                    'variant' => 'Black / Medium',
                    'sku' => 'SHIRT-BLK-M',
                    'qty' => 1,
                    'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=240&q=80',
                ],
            ],
            'events' => [
                ['status' => 'PICKED_UP', 'label' => 'Picked Up', 'time' => 'Sep 2, 10:12 AM', 'detail' => 'Parcel collected from seller.'],
                ['status' => 'AT_SORTING_CENTER', 'label' => 'At Sorting Center', 'time' => 'Sep 2, 11:33 AM', 'detail' => 'Parcel received at sorting center.'],
                ['status' => 'SORTED', 'label' => 'Sorted', 'time' => 'Sep 2, 1:10 PM', 'detail' => 'Parcel sorted for San Pablo area.'],
                ['status' => 'ASSIGNED_TO_RIDER', 'label' => 'Assigned to Rider', 'time' => 'Sep 2, 2:08 PM', 'detail' => 'Assigned to Renz Mercado.'],
                ['status' => 'OUT_FOR_DELIVERY', 'label' => 'Out for Delivery', 'time' => 'Sep 2, 3:30 PM', 'detail' => 'First delivery attempt started.'],
                ['status' => 'DELIVERY_FAILED', 'label' => 'Delivery Failed', 'time' => 'Sep 3, 5:02 PM', 'detail' => 'Multiple unsuccessful delivery attempts.'],
                ['status' => 'RETURNED', 'label' => 'Returned', 'time' => 'Sep 4, 2:16 PM', 'detail' => 'Parcel returned to seller.'],
            ],
        ],
    ]);

    $statusFlow = [
        'PICKED_UP' => 'Picked Up',
        'AT_SORTING_CENTER' => 'At Sorting Center',
        'SORTED' => 'Sorted',
        'ASSIGNED_TO_RIDER' => 'Assigned to Rider',
        'OUT_FOR_DELIVERY' => 'Out for Delivery',
        'DELIVERED' => 'Delivered',
        'COMPLETED' => 'Completed',
        'DELIVERY_FAILED' => 'Delivery Failed',
        'RETURNED' => 'Returned',
    ];

    $statusMeta = [
        'PICKED_UP' => [
            'class' => 'bg-navy/10 text-navy',
            'icon' => 'package-check',
        ],
        'AT_SORTING_CENTER' => [
            'class' => 'bg-navy/10 text-navy',
            'icon' => 'warehouse',
        ],
        'SORTED' => [
            'class' => 'bg-navy/10 text-navy',
            'icon' => 'boxes',
        ],
        'ASSIGNED_TO_RIDER' => [
            'class' => 'bg-sky/10 text-sky',
            'icon' => 'bike',
        ],
        'OUT_FOR_DELIVERY' => [
            'class' => 'bg-sky/10 text-sky',
            'icon' => 'truck',
        ],
        'DELIVERED' => [
            'class' => 'bg-teal/10 text-teal-dark',
            'icon' => 'package-check',
        ],
        'COMPLETED' => [
            'class' => 'bg-teal/10 text-teal-dark',
            'icon' => 'circle-check',
        ],
        'DELIVERY_FAILED' => [
            'class' => 'bg-red-50 text-red-600',
            'icon' => 'triangle-alert',
        ],
        'RETURNED' => [
            'class' => 'bg-red-50 text-red-600',
            'icon' => 'rotate-ccw',
        ],
    ];

    $inTransitStatuses = [
        'PICKED_UP',
        'AT_SORTING_CENTER',
        'SORTED',
        'ASSIGNED_TO_RIDER',
        'OUT_FOR_DELIVERY',
    ];

    $inTransitCount = $orders->whereIn('status', $inTransitStatuses)->count();
    $deliveredCount = $orders->where('status', 'DELIVERED')->count();
    $completedCount = $orders->where('status', 'COMPLETED')->count();
    $attentionCount = $orders->whereIn('status', ['DELIVERY_FAILED', 'RETURNED'])->count();
@endphp


<style>
    #sellerConfirmDelivery .delivery-card {
        transition:
            transform .16s ease,
            box-shadow .16s ease,
            border-color .16s ease;
    }

    #sellerConfirmDelivery .delivery-card:hover {
        transform: translateY(-1px);
    }

    #sellerConfirmDelivery [hidden],
    #deliveryDetailsModal[hidden],
    #deliveryIssueModal[hidden],
    #proofModal[hidden] {
        display: none !important;
    }

    .delivery-tab-active {
        background: #0F2C3F;
        color: #ffffff;
        border-color: #0F2C3F;
    }

    .timeline-line::before {
        content: "";
        position: absolute;
        left: 15px;
        top: 32px;
        bottom: -14px;
        width: 1px;
        background: #DFE5E8;
    }

    .timeline-line:last-child::before {
        display: none;
    }

    /* Click-to-copy affordance for order numbers */
    #sellerConfirmDelivery [data-copy-order-id] {
        cursor: pointer;
    }

    #sellerConfirmDelivery [data-copy-order-id]:hover {
        text-decoration: underline;
        text-decoration-style: dotted;
        text-underline-offset: 3px;
    }
</style>


<div id="sellerConfirmDelivery" class="space-y-5">

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <section>
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
            <div class="min-w-0">

                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    Confirm Delivery
                </h1>

                <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-3xl">
                    Track parcels after courier handover, review delivery events and proof,
                    and monitor buyer receipt confirmation. Delivery and completion statuses
                    should eventually update automatically from logistics and buyer actions.
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
            data-summary-filter="in_transit"
            class="delivery-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-sky/25"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-sky/10 text-sky flex items-center justify-center">
                    <x-lucide-truck class="w-5 h-5" />
                </div>

                <span class="text-[10px] font-bold text-sky/60">IN TRANSIT</span>
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">
                {{ $inTransitCount }}
            </p>

            <p class="text-xs text-navy/45">
                Parcels still moving
            </p>
        </button>


        <button
            type="button"
            data-summary-filter="delivered"
            class="delivery-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-teal/25"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center">
                    <x-lucide-package-check class="w-5 h-5" />
                </div>

                <span class="text-[10px] font-bold text-teal-dark/60">
                    WAITING BUYER
                </span>
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">
                {{ $deliveredCount }}
            </p>

            <p class="text-xs text-navy/45">
                Delivered, awaiting confirmation
            </p>
        </button>


        <button
            type="button"
            data-summary-filter="completed"
            class="delivery-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-teal/25"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                    <x-lucide-circle-check class="w-5 h-5" />
                </div>

                <span class="text-[10px] font-bold text-teal-dark/60">
                    COMPLETE
                </span>
            </div>

            <p class="mt-4 text-2xl font-bold text-navy">
                {{ $completedCount }}
            </p>

            <p class="text-xs text-navy/45">
                Buyer confirmed receipt
            </p>
        </button>


        <button
            type="button"
            data-summary-filter="attention"
            class="delivery-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-red-200"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center">
                    <x-lucide-triangle-alert class="w-5 h-5" />
                </div>

                <span class="text-[10px] font-bold text-red-400">
                    ATTENTION
                </span>
            </div>

            <p class="mt-4 text-2xl font-bold text-red-500">
                {{ $attentionCount }}
            </p>

            <p class="text-xs text-navy/45">
                Failed or returned
            </p>
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
                    ['key' => 'in_transit', 'label' => 'In Transit', 'count' => $inTransitCount],
                    ['key' => 'delivered', 'label' => 'Delivered', 'count' => $deliveredCount],
                    ['key' => 'completed', 'label' => 'Completed', 'count' => $completedCount],
                    ['key' => 'attention', 'label' => 'Needs Attention', 'count' => $attentionCount],
                ] as $tab)

                    <button
                        type="button"
                        data-delivery-tab="{{ $tab['key'] }}"
                        class="delivery-tab h-9 px-3.5 rounded-lg border border-transparent text-xs font-semibold whitespace-nowrap transition
                            {{ $tab['key'] === 'all'
                                ? 'delivery-tab-active'
                                : 'text-navy/50 hover:bg-gray-bg'
                            }}"
                    >
                        {{ $tab['label'] }}

                        <span class="ml-1 opacity-60">
                            {{ $tab['count'] }}
                        </span>
                    </button>

                @endforeach

            </div>


            <div class="relative flex-1 min-w-0 xl:ml-auto xl:max-w-md">
                <x-lucide-search class="w-4 h-4 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2" />

                <input
                    type="text"
                    id="deliverySearch"
                    placeholder="Search order, buyer, rider or waybill..."
                    class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/30 focus:outline-none focus:border-teal/50"
                >
            </div>

        </div>


        <div class="mt-3 flex items-center justify-between gap-3">

            <p class="text-[10px] text-navy/35">
                Showing

                <strong id="deliveryVisibleCount" class="text-navy/60">
                    {{ $orders->count() }}
                </strong>

                of {{ $orders->count() }} deliveries
            </p>


            <button
                type="button"
                id="deliveryClearFilters"
                class="text-[11px] font-semibold text-navy/45 hover:text-teal-dark transition"
            >
                Clear filters
            </button>

        </div>
    </section>


    {{-- =========================================================
        DELIVERY CARDS
    ========================================================= --}}
    @if ($orders->isEmpty())

        <section class="bg-white border border-gray-border rounded-xl py-14 text-center">

            <div class="w-12 h-12 mx-auto rounded-xl bg-teal-light text-teal-dark flex items-center justify-center">
                <x-lucide-package-check class="w-5 h-5" />
            </div>

            <p class="text-sm font-semibold text-navy/55 mt-3">
                No deliveries to track
            </p>

            <p class="text-xs text-navy/35 mt-1">
                Parcels handed over to riders will appear here.
            </p>

        </section>

    @else

        <section id="deliveryCards" class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            @foreach ($orders as $order)

                @php
                    $meta = $statusMeta[$order['status']]
                        ?? [
                            'class' => 'bg-navy/10 text-navy/50',
                            'icon' => 'circle',
                        ];

                    $statusLabel =
                        $statusFlow[$order['status']]
                        ?? str_replace('_', ' ', $order['status']);

                    $searchText = strtolower(
                        $order['id'] . ' ' .
                        $order['buyer'] . ' ' .
                        $order['waybill_no'] . ' ' .
                        ($order['rider']['name'] ?? '') . ' ' .
                        $order['delivery_area']
                    );

                    $orderJson = json_encode(
                        $order,
                        JSON_HEX_APOS | JSON_HEX_QUOT
                    );

                    $isAttention = in_array(
                        $order['status'],
                        ['DELIVERY_FAILED', 'RETURNED']
                    );
                @endphp


                <article
                    class="delivery-card bg-white border rounded-xl overflow-hidden
                        {{ $isAttention ? 'border-red-200' : 'border-gray-border' }}"
                    data-delivery-card
                    data-status="{{ strtolower($order['status']) }}"
                    data-search="{{ $searchText }}"
                    data-order='{{ $orderJson }}'
                >

                    {{-- Header --}}
                    <div class="px-4 sm:px-5 py-4 border-b border-gray-border">

                        <div class="flex flex-wrap items-start justify-between gap-3">

                            <div class="min-w-0">

                                <div class="flex flex-wrap items-center gap-2">

                                    <p
                                        class="text-sm font-bold text-navy"
                                        data-copy-order-id
                                        data-order-id="{{ $order['id'] }}"
                                    >
                                        {{ $order['id'] }}
                                    </p>

                                    <span
                                        class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full {{ $meta['class'] }}"
                                    >
                                        <x-dynamic-component
                                            :component="'lucide-' . $meta['icon']"
                                            class="w-3.5 h-3.5"
                                        />

                                        {{ $statusLabel }}
                                    </span>


                                    @if ($order['status'] === 'COMPLETED')

                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full bg-teal-light text-teal-dark">
                                            <x-lucide-circle-check class="w-3 h-3" />
                                            Buyer Confirmed
                                        </span>

                                    @endif

                                </div>


                                <p class="text-xs text-navy/50 mt-1">
                                    {{ $order['buyer'] }}

                                    <span class="text-navy/25">·</span>

                                    {{ $order['delivery_area'] }}
                                </p>


                                <p class="text-[10px] text-navy/35 mt-1">
                                    Updated {{ $order['updated_at'] }}
                                </p>

                            </div>


                            <div class="text-right shrink-0">

                                <p class="text-xs font-bold text-navy">
                                    {{ $order['items_count'] }}
                                    item{{ $order['items_count'] === 1 ? '' : 's' }}
                                </p>

                                <p class="text-[10px] text-navy/35 mt-0.5">
                                    {{ $order['parcel_type'] }}
                                    ·
                                    {{ $order['weight'] }}
                                </p>

                            </div>

                        </div>
                    </div>


                    {{-- Current status block --}}
                    <div class="px-4 sm:px-5 py-4">

                        @if ($order['status'] === 'OUT_FOR_DELIVERY')

                            <div class="rounded-xl border border-sky/20 bg-sky/5 p-3">

                                <div class="flex items-start gap-3">

                                    <div class="w-9 h-9 rounded-lg bg-sky/10 text-sky flex items-center justify-center shrink-0">
                                        <x-lucide-truck class="w-4 h-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="text-xs font-semibold text-navy">
                                            Parcel is out for delivery
                                        </p>

                                        <p class="text-[10px] text-navy/45 mt-1 leading-relaxed">
                                            {{ $order['rider']['name'] ?? 'Assigned rider' }}
                                            is delivering this parcel to the buyer.
                                        </p>

                                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-navy/40">

                                            <span class="inline-flex items-center gap-1">
                                                <x-lucide-bike class="w-3 h-3" />
                                                {{ $order['rider']['name'] ?? '—' }}
                                            </span>

                                            <span class="inline-flex items-center gap-1">
                                                <x-lucide-badge class="w-3 h-3" />
                                                {{ $order['rider']['plate'] ?? '—' }}
                                            </span>

                                        </div>

                                    </div>
                                </div>

                            </div>


                        @elseif ($order['status'] === 'DELIVERED')

                            <div class="rounded-xl border border-teal/25 bg-teal/5 p-3">

                                <div class="flex items-start gap-3">

                                    <div class="w-9 h-9 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
                                        <x-lucide-package-check class="w-4 h-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="text-xs font-semibold text-navy">
                                            Courier marked this order delivered
                                        </p>

                                        <p class="text-[10px] text-navy/45 mt-1 leading-relaxed">
                                            Delivered {{ $order['delivered_at'] }}.
                                            Waiting for the buyer to confirm receipt before the transaction becomes completed.
                                        </p>

                                        @if ($order['proof'])

                                            <button
                                                type="button"
                                                data-view-proof
                                                class="mt-2 text-[11px] font-bold text-teal-dark hover:text-teal transition"
                                            >
                                                View delivery proof →
                                            </button>

                                        @endif

                                    </div>
                                </div>

                            </div>


                        @elseif ($order['status'] === 'COMPLETED')

                            <div class="rounded-xl border border-teal/25 bg-teal-light p-3">

                                <div class="flex items-start gap-3">

                                    <div class="w-9 h-9 rounded-lg bg-white text-teal-dark flex items-center justify-center shrink-0">
                                        <x-lucide-circle-check class="w-4 h-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="text-xs font-semibold text-teal-dark">
                                            Transaction completed
                                        </p>

                                        <p class="text-[10px] text-navy/45 mt-1 leading-relaxed">
                                            Buyer confirmed receipt
                                            {{ $order['buyer_confirmed_at'] }}.
                                        </p>

                                        @if ($order['proof'])

                                            <button
                                                type="button"
                                                data-view-proof
                                                class="mt-2 text-[11px] font-bold text-teal-dark hover:text-teal transition"
                                            >
                                                View delivery proof →
                                            </button>

                                        @endif

                                    </div>
                                </div>

                            </div>


                        @elseif ($order['status'] === 'DELIVERY_FAILED')

                            <div class="rounded-xl border border-red-200 bg-red-50 p-3">

                                <div class="flex items-start gap-3">

                                    <div class="w-9 h-9 rounded-lg bg-white text-red-500 flex items-center justify-center shrink-0">
                                        <x-lucide-triangle-alert class="w-4 h-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="text-xs font-semibold text-red-600">
                                            Delivery attempt failed
                                        </p>

                                        <p class="text-[10px] text-navy/55 mt-1 leading-relaxed">
                                            {{ $order['failure_reason'] }}
                                        </p>

                                        @if ($order['return_status'])

                                            <p class="mt-2 text-[10px] font-semibold text-red-600">
                                                Next step: {{ $order['return_status'] }}
                                            </p>

                                        @endif

                                    </div>
                                </div>

                            </div>


                        @elseif ($order['status'] === 'RETURNED')

                            <div class="rounded-xl border border-red-200 bg-red-50 p-3">

                                <div class="flex items-start gap-3">

                                    <div class="w-9 h-9 rounded-lg bg-white text-red-500 flex items-center justify-center shrink-0">
                                        <x-lucide-rotate-ccw class="w-4 h-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="text-xs font-semibold text-red-600">
                                            Parcel returned to seller
                                        </p>

                                        <p class="text-[10px] text-navy/55 mt-1 leading-relaxed">
                                            {{ $order['failure_reason'] }}
                                        </p>

                                        <p class="mt-2 text-[10px] font-semibold text-red-600">
                                            {{ $order['return_status'] }}
                                        </p>

                                    </div>
                                </div>

                            </div>


                        @else

                            <div class="rounded-xl border border-gray-border bg-gray-bg/40 p-3">

                                <div class="flex items-start gap-3">

                                    <div class="w-9 h-9 rounded-lg bg-navy/10 text-navy/55 flex items-center justify-center shrink-0">
                                        <x-dynamic-component
                                            :component="'lucide-' . $meta['icon']"
                                            class="w-4 h-4"
                                        />
                                    </div>

                                    <div>

                                        <p class="text-xs font-semibold text-navy">
                                            {{ $statusLabel }}
                                        </p>

                                        <p class="text-[10px] text-navy/40 mt-1">
                                            Latest shipment update from logistics.
                                        </p>

                                    </div>
                                </div>

                            </div>

                        @endif

                    </div>


                    {{-- Shipment summary --}}
                    <div class="px-4 sm:px-5 pb-4">

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">

                            <div class="rounded-xl bg-gray-bg p-3">

                                <p class="text-[10px] text-navy/35">
                                    Waybill
                                </p>

                                <p class="text-[11px] font-semibold text-navy mt-1 break-all">
                                    {{ $order['waybill_no'] }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-gray-bg p-3">

                                <p class="text-[10px] text-navy/35">
                                    Rider
                                </p>

                                <p class="text-[11px] font-semibold text-navy mt-1">
                                    {{ $order['rider']['name'] ?? 'Not assigned' }}
                                </p>

                            </div>


                            <div class="col-span-2 sm:col-span-1 rounded-xl bg-gray-bg p-3">

                                <p class="text-[10px] text-navy/35">
                                    Sorting Hub
                                </p>

                                <p class="text-[11px] font-semibold text-navy mt-1">
                                    {{ $order['sorting_center']['name'] ?? '—' }}
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="px-4 sm:px-5 py-4 border-t border-gray-border bg-gray-bg/25">

                        <div class="flex flex-wrap items-center justify-between gap-2">

                            <button
                                type="button"
                                data-view-delivery-details
                                class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-border bg-white text-xs font-semibold text-navy/60 hover:bg-gray-bg hover:text-navy transition"
                            >
                                <x-lucide-eye class="w-4 h-4" />
                                Track Details
                            </button>


                            @if (in_array($order['status'], ['DELIVERY_FAILED', 'RETURNED']))

                                <button
                                    type="button"
                                    data-view-delivery-issue
                                    class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg border border-red-200 bg-red-50 text-xs font-semibold text-red-600 hover:bg-red-100 transition"
                                >
                                    <x-lucide-triangle-alert class="w-4 h-4" />
                                    Review Issue
                                </button>


                            @elseif ($order['status'] === 'DELIVERED')

                                <div class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-teal/10 text-xs font-semibold text-teal-dark">
                                    <x-lucide-clock-3 class="w-4 h-4" />
                                    Waiting for Buyer
                                </div>


                            @elseif ($order['status'] === 'COMPLETED')

                                <div class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-teal-light text-xs font-semibold text-teal-dark">
                                    <x-lucide-circle-check class="w-4 h-4" />
                                    Completed
                                </div>


                            @else

                                <div class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-sky/10 text-xs font-semibold text-sky">
                                    <x-lucide-truck class="w-4 h-4" />
                                    Tracking
                                </div>

                            @endif

                        </div>

                    </div>

                </article>

            @endforeach

        </section>


        {{-- No filtered results --}}
        <section
            id="deliveryNoResults"
            hidden
            class="bg-white border border-gray-border rounded-xl py-12 text-center"
        >

            <div class="w-11 h-11 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                <x-lucide-search-x class="w-5 h-5" />
            </div>

            <p class="text-sm font-semibold text-navy/55 mt-3">
                No matching deliveries
            </p>

            <p class="text-xs text-navy/35 mt-1">
                Try another order, buyer, rider, or status filter.
            </p>

        </section>

    @endif

</div>


{{-- =========================================================
    DELIVERY DETAILS / TIMELINE MODAL
========================================================= --}}
<div id="deliveryDetailsModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div data-close-delivery-details class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-2xl max-h-[90vh] overflow-y-auto content-scrollbar">

        <div class="sticky top-0 z-10 bg-white flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Delivery Tracking
                </p>

                <p id="detailsOrderId" class="text-[11px] text-navy/40 mt-0.5" data-copy-order-id></p>

            </div>


            <button
                type="button"
                data-close-delivery-details
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <div class="px-5 py-5 space-y-5">

            {{-- Buyer + shipment info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>

                    <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                        Buyer
                    </p>

                    <p id="detailsBuyer" class="text-xs font-semibold text-navy mt-1"></p>

                </div>


                <div>

                    <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                        Waybill
                    </p>

                    <p id="detailsWaybill" class="text-xs font-semibold text-navy mt-1"></p>

                </div>

            </div>


            <div>

                <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                    Delivery Address
                </p>

                <p id="detailsAddress" class="text-xs text-navy/70 mt-1 leading-relaxed"></p>

            </div>


            {{-- Parcel items --}}
            <div>

                <div class="flex items-center justify-between gap-3">

                    <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                        Parcel Items
                    </p>

                    <span id="detailsItemCount" class="text-[10px] text-navy/30"></span>

                </div>


                <div id="detailsItems" class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2"></div>

            </div>


            {{-- Rider + hub --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <div class="rounded-xl border border-gray-border p-3">

                    <div class="flex items-start gap-3">

                        <div class="w-9 h-9 rounded-full bg-sky/10 text-sky flex items-center justify-center shrink-0">
                            <x-lucide-bike class="w-4 h-4" />
                        </div>

                        <div>

                            <p class="text-[10px] text-navy/35">
                                Delivery Rider
                            </p>

                            <p id="detailsRiderName" class="text-xs font-bold text-navy mt-0.5"></p>

                            <p id="detailsRiderInfo" class="text-[10px] text-navy/40 mt-1"></p>

                        </div>

                    </div>

                </div>


                <div class="rounded-xl border border-gray-border p-3">

                    <div class="flex items-start gap-3">

                        <div class="w-9 h-9 rounded-lg bg-navy/10 text-navy/55 flex items-center justify-center shrink-0">
                            <x-lucide-warehouse class="w-4 h-4" />
                        </div>

                        <div>

                            <p class="text-[10px] text-navy/35">
                                Sorting Center
                            </p>

                            <p id="detailsHubName" class="text-xs font-bold text-navy mt-0.5"></p>

                            <p id="detailsHubLocation" class="text-[10px] text-navy/40 mt-1"></p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Timeline --}}
            <div>

                <div class="flex items-center justify-between gap-3">

                    <div>

                        <p class="text-sm font-bold text-navy">
                            Shipment Timeline
                        </p>

                        <p class="text-[10px] text-navy/40 mt-0.5">
                            Latest events from seller handover through final delivery.
                        </p>

                    </div>

                    <x-lucide-route class="w-5 h-5 text-teal-dark" />

                </div>


                <div id="detailsTimeline" class="mt-4 space-y-0"></div>

            </div>


            {{-- Delivered / buyer confirmation --}}
            <div id="detailsDeliveredWrap" hidden class="rounded-xl border border-teal/25 bg-teal/5 p-3">

                <div class="flex items-start gap-3">

                    <x-lucide-package-check class="w-4 h-4 text-teal-dark mt-0.5 shrink-0" />

                    <div>

                        <p class="text-xs font-semibold text-navy">
                            Delivery information
                        </p>

                        <p id="detailsDeliveredText" class="text-[10px] text-navy/50 mt-1 leading-relaxed"></p>

                        <button
                            type="button"
                            id="detailsProofButton"
                            hidden
                            class="mt-2 text-[11px] font-bold text-teal-dark hover:text-teal transition"
                        >
                            View proof of delivery →
                        </button>

                    </div>

                </div>

            </div>


            {{-- Issue --}}
            <div id="detailsIssueWrap" hidden class="rounded-xl border border-red-200 bg-red-50 p-3">

                <div class="flex items-start gap-3">

                    <x-lucide-triangle-alert class="w-4 h-4 text-red-500 mt-0.5 shrink-0" />

                    <div>

                        <p class="text-xs font-semibold text-red-600">
                            Delivery issue
                        </p>

                        <p id="detailsIssueReason" class="text-[10px] text-navy/55 mt-1 leading-relaxed"></p>

                        <p id="detailsIssueNext" class="text-[10px] font-semibold text-red-600 mt-2"></p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    DELIVERY ISSUE MODAL
========================================================= --}}
<div id="deliveryIssueModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div data-close-delivery-issue class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-lg">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Delivery Issue
                </p>

                <p id="issueOrderId" class="text-[11px] text-navy/40 mt-0.5"></p>

            </div>


            <button
                type="button"
                data-close-delivery-issue
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <div class="p-5">

            <div class="rounded-xl border border-red-200 bg-red-50 p-3">

                <div class="flex items-start gap-3">

                    <div class="w-9 h-9 rounded-lg bg-white text-red-500 flex items-center justify-center shrink-0">
                        <x-lucide-triangle-alert class="w-4 h-4" />
                    </div>

                    <div>

                        <p class="text-[10px] font-bold uppercase tracking-wide text-red-600">
                            Reported Reason
                        </p>

                        <p id="issueReason" class="text-xs text-navy/65 mt-1 leading-relaxed"></p>

                    </div>

                </div>

            </div>


            <div class="mt-4 rounded-xl bg-gray-bg p-3">

                <p class="text-[10px] text-navy/35">
                    Logistics Status / Next Step
                </p>

                <p id="issueNextStep" class="text-xs font-semibold text-navy mt-1"></p>

            </div>


            <div class="mt-4 rounded-xl border border-gray-border p-3">

                <p class="text-xs font-semibold text-navy">
                    Seller guidance
                </p>

                <p class="text-[10px] text-navy/45 mt-1 leading-relaxed">
                    This page should remain informational for the seller.
                    The backend can later add dispute handling, buyer messaging,
                    reschedule updates, or returned-parcel receiving actions where required.
                </p>

            </div>


            <div class="mt-5 flex justify-end">

                <button
                    type="button"
                    data-close-delivery-issue
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition"
                >
                    Close
                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    PROOF OF DELIVERY MODAL
========================================================= --}}
<div id="proofModal" hidden class="fixed inset-0 z-[55] flex items-center justify-center p-4">

    <div data-close-proof class="absolute inset-0 bg-navy/50"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-lg max-h-[90vh] overflow-y-auto content-scrollbar">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Proof of Delivery
                </p>

                <p id="proofOrderId" class="text-[11px] text-navy/40 mt-0.5"></p>

            </div>


            <button
                type="button"
                data-close-proof
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <div class="p-5">

            <div id="proofImageWrap" class="rounded-xl overflow-hidden bg-gray-bg border border-gray-border">

                <img
                    id="proofImage"
                    src=""
                    alt="Delivery proof"
                    class="w-full max-h-72 object-cover"
                >

            </div>


            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">

                <div class="rounded-xl bg-gray-bg p-3">

                    <p class="text-[10px] text-navy/35">
                        Proof Type
                    </p>

                    <p id="proofType" class="text-xs font-semibold text-navy mt-1"></p>

                </div>


                <div class="rounded-xl bg-gray-bg p-3">

                    <p class="text-[10px] text-navy/35">
                        Recipient
                    </p>

                    <p id="proofRecipient" class="text-xs font-semibold text-navy mt-1"></p>

                </div>

            </div>


            <div class="mt-3 rounded-xl border border-gray-border p-3">

                <p class="text-[10px] text-navy/35">
                    Courier Note
                </p>

                <p id="proofNote" class="text-xs text-navy/65 mt-1 leading-relaxed"></p>

            </div>

        </div>

    </div>

</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const cards = Array.from(
        document.querySelectorAll('[data-delivery-card]')
    );

    const searchInput =
        document.getElementById('deliverySearch');

    const tabs = Array.from(
        document.querySelectorAll('[data-delivery-tab]')
    );

    const visibleCount =
        document.getElementById('deliveryVisibleCount');

    const noResults =
        document.getElementById('deliveryNoResults');


    const detailsModal =
        document.getElementById('deliveryDetailsModal');

    const issueModal =
        document.getElementById('deliveryIssueModal');

    const proofModal =
        document.getElementById('proofModal');


    let activeFilter = 'all';
    let activeOrder = null;


    const inTransitStatuses = [
        'picked_up',
        'at_sorting_center',
        'sorted',
        'assigned_to_rider',
        'out_for_delivery'
    ];


    function parseCardOrder(card) {
        try {
            return JSON.parse(
                card.getAttribute('data-order') || '{}'
            );
        } catch (error) {
            return {};
        }
    }


    function setBodyLock(locked) {
        document.body.style.overflow =
            locked ? 'hidden' : '';
    }


    function matchesFilter(card, filter) {
        const status =
            card.dataset.status || '';

        if (filter === 'all') {
            return true;
        }

        if (filter === 'in_transit') {
            return inTransitStatuses.includes(status);
        }

        if (filter === 'delivered') {
            return status === 'delivered';
        }

        if (filter === 'completed') {
            return status === 'completed';
        }

        if (filter === 'attention') {
            return (
                status === 'delivery_failed' ||
                status === 'returned'
            );
        }

        return true;
    }


    function applyFilters() {
        const query =
            (searchInput?.value || '')
                .trim()
                .toLowerCase();

        let shown = 0;


        cards.forEach(function (card) {

            const statusMatch =
                matchesFilter(card, activeFilter);

            const searchMatch =
                !query ||
                (card.dataset.search || '')
                    .includes(query);

            const show =
                statusMatch && searchMatch;

            card.hidden = !show;

            if (show) {
                shown++;
            }
        });


        if (visibleCount) {
            visibleCount.textContent = shown;
        }


        if (noResults) {
            noResults.hidden = shown !== 0;
        }


        tabs.forEach(function (tab) {

            const active =
                tab.dataset.deliveryTab === activeFilter;

            tab.classList.toggle(
                'delivery-tab-active',
                active
            );

            tab.classList.toggle(
                'text-navy/50',
                !active
            );

            tab.classList.toggle(
                'hover:bg-gray-bg',
                !active
            );
        });
    }


    tabs.forEach(function (tab) {

        tab.addEventListener('click', function () {

            activeFilter =
                tab.dataset.deliveryTab || 'all';

            applyFilters();
        });
    });


    document
        .querySelectorAll('[data-summary-filter]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                activeFilter =
                    button.dataset.summaryFilter || 'all';

                applyFilters();
            });
        });


    searchInput?.addEventListener(
        'input',
        applyFilters
    );


    document
        .getElementById('deliveryClearFilters')
        ?.addEventListener('click', function () {

            activeFilter = 'all';

            if (searchInput) {
                searchInput.value = '';
            }

            applyFilters();
        });


    /* ---------------------------------------------------------
       PROOF OF DELIVERY
    --------------------------------------------------------- */
    function openProof(order) {

        if (!order?.proof) {
            return;
        }


        document
            .getElementById('proofOrderId')
            .textContent =
                order.id || '';


        document
            .getElementById('proofType')
            .textContent =
                order.proof.type || '—';


        document
            .getElementById('proofRecipient')
            .textContent =
                order.proof.recipient || '—';


        document
            .getElementById('proofNote')
            .textContent =
                order.proof.note || '—';


        const image =
            document.getElementById('proofImage');

        const imageWrap =
            document.getElementById('proofImageWrap');


        if (order.proof.image) {

            image.src =
                order.proof.image;

            imageWrap.hidden = false;

        } else {

            image.removeAttribute('src');
            imageWrap.hidden = true;
        }


        proofModal.hidden = false;
        setBodyLock(true);
    }


    document
        .querySelectorAll('[data-view-proof]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const card =
                    button.closest('[data-delivery-card]');

                const order =
                    parseCardOrder(card);

                openProof(order);
            });
        });


    document
        .querySelectorAll('[data-close-proof]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                proofModal.hidden = true;

                /*
                 * Keep body locked if the tracking details modal
                 * is still open behind the proof modal.
                 */
                setBodyLock(
                    !detailsModal.hidden
                );
            });
        });


    /* ---------------------------------------------------------
       DETAILS / TRACKING TIMELINE
    --------------------------------------------------------- */
    function eventIcon(status) {

        const icons = {
            PICKED_UP: 'package',
            AT_SORTING_CENTER: 'warehouse',
            SORTED: 'boxes',
            ASSIGNED_TO_RIDER: 'bike',
            OUT_FOR_DELIVERY: 'truck',
            DELIVERED: 'package-check',
            COMPLETED: 'circle-check',
            DELIVERY_FAILED: 'triangle-alert',
            RETURNED: 'rotate-ccw'
        };

        return icons[status] || 'circle';
    }


    function eventTone(status) {

        if (
            status === 'DELIVERY_FAILED' ||
            status === 'RETURNED'
        ) {
            return 'bg-red-50 text-red-500';
        }


        if (
            status === 'DELIVERED' ||
            status === 'COMPLETED'
        ) {
            return 'bg-teal/10 text-teal-dark';
        }


        if (
            status === 'ASSIGNED_TO_RIDER' ||
            status === 'OUT_FOR_DELIVERY'
        ) {
            return 'bg-sky/10 text-sky';
        }


        return 'bg-navy/10 text-navy/55';
    }


    function iconSvg(name) {

        const common =
            'width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';


        const paths = {
            package:
                '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/>',

            warehouse:
                '<path d="M3 21V8l9-5 9 5v13"/><path d="M13 13h4v8"/><path d="M5 13h4v8"/><path d="M3 21h18"/>',

            boxes:
                '<path d="M2.97 12.92 11 17.56l8.03-4.64"/><path d="M11 22V17.56"/><path d="m7 15.25-4.03-2.33V8.28L7 5.95l4.03 2.33v4.64Z"/><path d="m17 15.25-4.03-2.33V8.28L17 5.95l4.03 2.33v4.64Z"/><path d="m11 8.28-4.03-2.33V1.31L11-1.02"/>',

            bike:
                '<circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h3"/><path d="m5.5 17.5 3-6.5"/>',

            truck:
                '<path d="M10 17h4V5H2v12h3"/><path d="M14 9h4l4 4v4h-3"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="16.5" cy="17.5" r="2.5"/>',

            'package-check':
                '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/><path d="m16 19 2 2 4-4"/>',

            'circle-check':
                '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',

            'triangle-alert':
                '<path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',

            'rotate-ccw':
                '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>',

            circle:
                '<circle cx="12" cy="12" r="10"/>'
        };


        return (
            '<svg ' +
            common +
            '>' +
            (paths[name] || paths.circle) +
            '</svg>'
        );
    }


    function openDetails(card) {

        activeOrder =
            parseCardOrder(card);


        document
            .getElementById('detailsOrderId')
            .textContent =
                activeOrder.id +
                ' · ' +
                String(activeOrder.status || '')
                    .replaceAll('_', ' ');

        document
            .getElementById('detailsOrderId')
            .dataset.orderId =
                activeOrder.id || '';


        document
            .getElementById('detailsBuyer')
            .textContent =
                activeOrder.buyer || '';


        document
            .getElementById('detailsWaybill')
            .textContent =
                activeOrder.waybill_no || '—';


        document
            .getElementById('detailsAddress')
            .textContent =
                activeOrder.address || '';


        const totalQty =
            (activeOrder.items || [])
                .reduce(
                    (sum, item) =>
                        sum + Number(item.qty || 0),
                    0
                );


        document
            .getElementById('detailsItemCount')
            .textContent =
                totalQty +
                ' item' +
                (totalQty === 1 ? '' : 's');


        const itemsWrap =
            document.getElementById('detailsItems');

        itemsWrap.innerHTML = '';


        (activeOrder.items || [])
            .forEach(function (item) {

                const row =
                    document.createElement('div');


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

                    <p class="text-xs font-bold text-navy shrink-0">
                        ×${item.qty || 0}
                    </p>
                `;


                itemsWrap.appendChild(row);
            });


        document
            .getElementById('detailsRiderName')
            .textContent =
                activeOrder.rider?.name || 'Not assigned';


        document
            .getElementById('detailsRiderInfo')
            .textContent =
                [
                    activeOrder.rider?.id,
                    activeOrder.rider?.vehicle,
                    activeOrder.rider?.plate,
                    activeOrder.rider?.phone
                ]
                .filter(Boolean)
                .join(' · ');


        document
            .getElementById('detailsHubName')
            .textContent =
                activeOrder.sorting_center?.name || '—';


        document
            .getElementById('detailsHubLocation')
            .textContent =
                activeOrder.sorting_center?.location || '—';


        const timeline =
            document.getElementById('detailsTimeline');

        timeline.innerHTML = '';


        (activeOrder.events || [])
            .forEach(function (event, index) {

                const item =
                    document.createElement('div');


                item.className =
                    'timeline-line relative flex gap-3 pb-5 last:pb-0';


                const tone =
                    eventTone(event.status);

                const icon =
                    eventIcon(event.status);


                item.innerHTML = `
                    <div class="relative z-10 w-8 h-8 rounded-full ${tone} flex items-center justify-center shrink-0">
                        ${iconSvg(icon)}
                    </div>

                    <div class="min-w-0 pt-0.5">

                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">

                            <p class="text-xs font-semibold text-navy">
                                ${event.label || ''}
                            </p>

                            <span class="text-[10px] text-navy/30">
                                ${event.time || ''}
                            </span>

                        </div>

                        <p class="text-[10px] text-navy/45 mt-1 leading-relaxed">
                            ${event.detail || ''}
                        </p>

                    </div>
                `;


                timeline.appendChild(item);
            });


        const deliveredWrap =
            document.getElementById('detailsDeliveredWrap');

        const deliveredText =
            document.getElementById('detailsDeliveredText');

        const proofButton =
            document.getElementById('detailsProofButton');


        if (
            activeOrder.status === 'DELIVERED' ||
            activeOrder.status === 'COMPLETED'
        ) {

            deliveredWrap.hidden = false;


            if (activeOrder.status === 'COMPLETED') {

                deliveredText.textContent =
                    'Courier delivered the parcel ' +
                    (activeOrder.delivered_at || '') +
                    '. Buyer confirmed receipt ' +
                    (activeOrder.buyer_confirmed_at || '') +
                    '.';

            } else {

                deliveredText.textContent =
                    'Courier delivered the parcel ' +
                    (activeOrder.delivered_at || '') +
                    '. Waiting for buyer receipt confirmation.';
            }


            proofButton.hidden =
                !activeOrder.proof;

        } else {

            deliveredWrap.hidden = true;
            proofButton.hidden = true;
        }


        const issueWrap =
            document.getElementById('detailsIssueWrap');


        if (
            activeOrder.status === 'DELIVERY_FAILED' ||
            activeOrder.status === 'RETURNED'
        ) {

            issueWrap.hidden = false;


            document
                .getElementById('detailsIssueReason')
                .textContent =
                    activeOrder.failure_reason || '';


            document
                .getElementById('detailsIssueNext')
                .textContent =
                    activeOrder.return_status
                        ? 'Next step: ' +
                          activeOrder.return_status
                        : '';

        } else {

            issueWrap.hidden = true;
        }


        detailsModal.hidden = false;
        setBodyLock(true);
    }


    document
        .querySelectorAll('[data-view-delivery-details]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                openDetails(
                    button.closest('[data-delivery-card]')
                );
            });
        });


    document
        .querySelectorAll('[data-close-delivery-details]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                detailsModal.hidden = true;
                setBodyLock(false);
            });
        });


    document
        .getElementById('detailsProofButton')
        ?.addEventListener('click', function () {

            if (activeOrder?.proof) {
                openProof(activeOrder);
            }
        });


    /* ---------------------------------------------------------
       DELIVERY ISSUE
    --------------------------------------------------------- */
    function openIssue(card) {

        const order =
            parseCardOrder(card);


        document
            .getElementById('issueOrderId')
            .textContent =
                order.id +
                ' · ' +
                String(order.status || '')
                    .replaceAll('_', ' ');


        document
            .getElementById('issueReason')
            .textContent =
                order.failure_reason ||
                'No reason provided.';


        document
            .getElementById('issueNextStep')
            .textContent =
                order.return_status ||
                'Waiting for logistics update.';


        issueModal.hidden = false;
        setBodyLock(true);
    }


    document
        .querySelectorAll('[data-view-delivery-issue]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                openIssue(
                    button.closest('[data-delivery-card]')
                );
            });
        });


    document
        .querySelectorAll('[data-close-delivery-issue]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                issueModal.hidden = true;
                setBodyLock(false);
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
       ESCAPE KEY
    --------------------------------------------------------- */
    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') {
            return;
        }


        if (!proofModal.hidden) {

            proofModal.hidden = true;

            setBodyLock(
                !detailsModal.hidden
            );

            return;
        }


        if (!detailsModal.hidden) {

            detailsModal.hidden = true;
            setBodyLock(false);
        }


        if (!issueModal.hidden) {

            issueModal.hidden = true;
            setBodyLock(false);
        }
    });


    applyFilters();
});
</script>
@endpush

@endsection