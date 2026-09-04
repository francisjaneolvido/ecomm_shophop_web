@extends('seller.partials.layout')

@section('title', 'Prepare Orders')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    */
    $orders = collect($orders ?? [
        [
            'id' => 'ORD-10229',
            'buyer' => 'Ella Marasigan',
            'status' => 'CONFIRMED',
            'items' => [
                ['name' => 'Capiz Shell Wall Lamp', 'qty' => 1, 'packed' => false],
                ['name' => 'Handwoven Rattan Basket', 'qty' => 1, 'packed' => false],
            ],
        ],
        [
            'id' => 'ORD-10227',
            'buyer' => 'Kim Delos Reyes',
            'status' => 'PREPARING',
            'items' => [
                ['name' => 'Barako Coffee Beans 250g', 'qty' => 2, 'packed' => true],
            ],
        ],
    ]);

    $statusClasses = [
        'CONFIRMED' => 'bg-sky/10 text-sky',
        'PREPARING' => 'bg-yellow/15 text-amber-700',
    ];
@endphp


<style>
    #sellerPrepareOrders .dash-gap { gap: 1rem; }
    #sellerPrepareOrders .dash-section { margin-bottom: 1.25rem; }
</style>


<div id="sellerPrepareOrders">

    <header class="dash-section flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                Prepare Orders
            </h1>
            <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                Pack items, attach the shipping label, then mark each order ready for pickup.
            </p>
        </div>
    </header>


    @if ($orders->isEmpty())

        <div class="bg-white border border-gray-border rounded-xl py-14 text-center">
            <div class="w-10 h-10 mx-auto rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                <x-lucide-box class="w-4.5 h-4.5" />
            </div>
            <p class="text-xs font-semibold text-navy/50 mt-3">Nothing to prepare</p>
            <p class="text-[10px] text-navy/35 mt-1">Accepted orders will appear here for packing.</p>
        </div>

    @else

        <div class="grid grid-cols-1 lg:grid-cols-2 dash-gap">
            @foreach ($orders as $order)
                <div class="bg-white border border-gray-border rounded-xl p-4" data-prepare-card>

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-navy">{{ $order['id'] }}</p>
                            <p class="text-[10px] text-navy/45 mt-0.5">{{ $order['buyer'] }}</p>
                        </div>
                        <span class="text-[9px] font-semibold px-2 py-0.5 rounded-full {{ $statusClasses[$order['status']] ?? 'bg-navy/10 text-navy/50' }}">
                            {{ str_replace('_', ' ', $order['status']) }}
                        </span>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-border space-y-2">
                        <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">Packing Checklist</p>

                        @foreach ($order['items'] as $index => $item)
                            <label class="flex items-center gap-2.5 text-[11px] text-navy/70 cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="w-3.5 h-3.5 rounded border-gray-border text-teal focus:ring-teal/40"
                                    data-pack-checkbox
                                    {{ $item['packed'] ? 'checked' : '' }}
                                >
                                <span>{{ $item['qty'] }}× {{ $item['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-gray-border text-[11px] font-semibold text-navy/60 hover:bg-gray-bg transition-colors"
                            onclick="window.print()"
                        >
                            <x-lucide-printer class="w-3.5 h-3.5" />
                            Print Waybill
                        </button>

                        <form method="POST" action="{{ route('seller.orders.prepare') }}">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                            <input type="hidden" name="action" value="mark_ready">
                            <button
                                type="submit"
                                data-mark-ready
                                class="h-8 px-3.5 rounded-lg bg-navy hover:bg-navy/90 text-[11px] font-semibold text-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                {{ collect($order['items'])->contains('packed', false) ? 'disabled' : '' }}
                            >
                                Mark Ready for Pickup
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>

    @endif

</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-prepare-card]').forEach(function (card) {
        const checkboxes = Array.from(card.querySelectorAll('[data-pack-checkbox]'));
        const readyButton = card.querySelector('[data-mark-ready]');

        function refresh() {
            const allChecked = checkboxes.every(cb => cb.checked);
            if (readyButton) readyButton.disabled = !allChecked;
        }

        checkboxes.forEach(cb => cb.addEventListener('change', refresh));
    });
});
</script>
@endpush

@endsection