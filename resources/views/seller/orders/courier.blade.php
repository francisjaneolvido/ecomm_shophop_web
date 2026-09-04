@extends('seller.partials.layout')

@section('title', 'Hand Over to Courier')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    */
    $orders = collect($orders ?? [
        [
            'id' => 'ORD-10225',
            'buyer' => 'Ella Marasigan',
            'status' => 'READY_FOR_PICKUP',
            'rider' => null,
            'items_count' => 2,
        ],
        [
            'id' => 'ORD-10220',
            'buyer' => 'Ronald Cabrera',
            'status' => 'READY_FOR_PICKUP',
            'rider' => 'Rider 04 — Paolo M.',
            'items_count' => 1,
        ],
    ]);
@endphp


<style>
    #sellerCourier .dash-gap { gap: 1rem; }
    #sellerCourier .dash-section { margin-bottom: 1.25rem; }
</style>


<div id="sellerCourier">

    <header class="dash-section flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                Hand Over to Courier
            </h1>
            <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                Orders packed and waiting for rider pickup. Confirm once the rider collects the parcel.
            </p>
        </div>
    </header>


    @if ($orders->isEmpty())

        <div class="bg-white border border-gray-border rounded-xl py-14 text-center">
            <div class="w-10 h-10 mx-auto rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                <x-lucide-truck class="w-4.5 h-4.5" />
            </div>
            <p class="text-xs font-semibold text-navy/50 mt-3">No orders waiting for pickup</p>
            <p class="text-[10px] text-navy/35 mt-1">Orders marked ready for pickup will appear here.</p>
        </div>

    @else

        <div class="bg-white border border-gray-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-border">
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3">Order</th>
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3">Buyer</th>
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3">Items</th>
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3">Rider</th>
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="border-b border-gray-border last:border-0">
                                <td class="px-4 py-3 text-[11px] font-semibold text-navy">{{ $order['id'] }}</td>
                                <td class="px-4 py-3 text-[11px] text-navy/70">{{ $order['buyer'] }}</td>
                                <td class="px-4 py-3 text-[11px] text-navy/60">{{ $order['items_count'] }} item{{ $order['items_count'] === 1 ? '' : 's' }}</td>
                                <td class="px-4 py-3">
                                    @if ($order['rider'])
                                        <span class="inline-flex items-center gap-1.5 text-[10px] text-navy/60">
                                            <x-lucide-bike class="w-3.5 h-3.5 text-sky" />
                                            {{ $order['rider'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-[10px] text-navy/35">
                                            <x-lucide-loader class="w-3.5 h-3.5" />
                                            Waiting for assignment
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('seller.orders.courier') }}" class="inline-flex">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                        <input type="hidden" name="action" value="confirm_pickup">
                                        <button
                                            type="submit"
                                            class="h-8 px-3.5 rounded-lg bg-navy hover:bg-navy/90 text-[11px] font-semibold text-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                            {{ $order['rider'] ? '' : 'disabled' }}
                                        >
                                            Confirm Rider Pickup
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif

</div>

@endsection