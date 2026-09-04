@extends('seller.partials.layout')

@section('title', 'Confirm Delivery')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    | Mirrors the full status list from the ERP flow so the seller can
    | track a parcel end-to-end after handover to the rider.
    */
    $orders = collect($orders ?? [
        [
            'id' => 'ORD-10218',
            'buyer' => 'Ronald Cabrera',
            'status' => 'OUT_FOR_DELIVERY',
            'updated_at' => 'Today, 1:40 PM',
        ],
        [
            'id' => 'ORD-10210',
            'buyer' => 'Trisha Ang',
            'status' => 'DELIVERED',
            'updated_at' => 'Yesterday, 5:12 PM',
        ],
        [
            'id' => 'ORD-10204',
            'buyer' => 'Miguel Ortiz',
            'status' => 'COMPLETED',
            'updated_at' => '2 days ago',
        ],
        [
            'id' => 'ORD-10198',
            'buyer' => 'Anna Reyes',
            'status' => 'DELIVERY_FAILED',
            'updated_at' => '3 days ago',
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
    ];

    $statusClasses = [
        'PICKED_UP' => 'bg-navy/10 text-navy',
        'AT_SORTING_CENTER' => 'bg-navy/10 text-navy',
        'SORTED' => 'bg-navy/10 text-navy',
        'ASSIGNED_TO_RIDER' => 'bg-sky/10 text-sky',
        'OUT_FOR_DELIVERY' => 'bg-sky/10 text-sky',
        'DELIVERED' => 'bg-teal/10 text-teal-dark',
        'COMPLETED' => 'bg-teal/10 text-teal-dark',
        'DELIVERY_FAILED' => 'bg-red-50 text-red-600',
        'RETURNED' => 'bg-red-50 text-red-600',
    ];
@endphp


<style>
    #sellerConfirmDelivery .dash-gap { gap: 1rem; }
    #sellerConfirmDelivery .dash-section { margin-bottom: 1.25rem; }
</style>


<div id="sellerConfirmDelivery">

    <header class="dash-section flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                Confirm Delivery
            </h1>
            <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                Track parcels after handover. You'll be notified automatically once the buyer confirms receipt.
            </p>
        </div>
    </header>


    @if ($orders->isEmpty())

        <div class="bg-white border border-gray-border rounded-xl py-14 text-center">
            <div class="w-10 h-10 mx-auto rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                <x-lucide-package-check class="w-4.5 h-4.5" />
            </div>
            <p class="text-xs font-semibold text-navy/50 mt-3">No deliveries to track</p>
            <p class="text-[10px] text-navy/35 mt-1">Parcels handed over to riders will show up here.</p>
        </div>

    @else

        <div class="space-y-2.5">
            @foreach ($orders as $order)
                <div class="bg-white border border-gray-border rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-xs font-bold text-navy">{{ $order['id'] }}</p>

                            @if ($order['status'] === 'COMPLETED')
                                <span class="inline-flex items-center gap-1 text-[9px] font-semibold px-2 py-0.5 rounded-full bg-teal/10 text-teal-dark">
                                    <x-lucide-circle-check class="w-3 h-3" />
                                    Buyer confirmed
                                </span>
                            @endif
                        </div>
                        <p class="text-[10px] text-navy/45 mt-1">
                            {{ $order['buyer'] }} · updated {{ $order['updated_at'] }}
                        </p>
                    </div>

                    <span class="text-[9px] font-semibold px-2.5 py-1 rounded-full {{ $statusClasses[$order['status']] ?? 'bg-navy/10 text-navy/50' }}">
                        {{ $statusFlow[$order['status']] ?? str_replace('_', ' ', $order['status']) }}
                    </span>

                </div>
            @endforeach
        </div>

    @endif

</div>

@endsection