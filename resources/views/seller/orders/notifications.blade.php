@extends('seller.partials.layout')

@section('title', 'Order Notifications')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    */
    $orders = collect($orders ?? [
        [
            'id' => 'ORD-10231',
            'buyer' => 'Maricel Santos',
            'placed_at' => '10 mins ago',
            'items' => [
                ['name' => 'Handwoven Rattan Basket', 'qty' => 2, 'price' => 450],
                ['name' => 'Handmade Soap Bar Set', 'qty' => 1, 'price' => 175],
            ],
            'note' => 'Please pack securely, pasalubong po ito.',
            'address' => 'Blk 4 Lot 12, Sampaguita St., Brgy. San Isidro, Calamba, Laguna',
            'status' => 'PLACED',
        ],
        [
            'id' => 'ORD-10230',
            'buyer' => 'Jonas Villareal',
            'placed_at' => '42 mins ago',
            'items' => [
                ['name' => 'Barako Coffee Beans 250g', 'qty' => 3, 'price' => 220],
            ],
            'note' => null,
            'address' => 'Purok 3, Brgy. Halang, Calamba, Laguna',
            'status' => 'PLACED',
        ],
    ]);

    $totalNew = $orders->where('status', 'PLACED')->count();
@endphp


<style>
    #sellerOrderNotifications .dash-gap { gap: 1rem; }
    #sellerOrderNotifications .dash-section { margin-bottom: 1.25rem; }
    #orderDetailsModal[hidden] { display: none !important; }
</style>


<div id="sellerOrderNotifications">

    <header class="dash-section flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                Order Notifications
            </h1>
            <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                New orders waiting for your review. Accept to move them into preparation.
            </p>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-coral/10 text-coral text-[11px] font-semibold">
            <x-lucide-bell-ring class="w-3.5 h-3.5" />
            {{ $totalNew }} new order{{ $totalNew === 1 ? '' : 's' }}
        </span>
    </header>


    @if ($orders->isEmpty())

        <div class="bg-white border border-gray-border rounded-xl py-14 text-center">
            <div class="w-10 h-10 mx-auto rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                <x-lucide-circle-check class="w-4.5 h-4.5" />
            </div>
            <p class="text-xs font-semibold text-navy/50 mt-3">No new orders right now</p>
            <p class="text-[10px] text-navy/35 mt-1">New orders will show up here as buyers check out.</p>
        </div>

    @else

        <div class="space-y-3">
            @foreach ($orders as $order)
                @php $itemsTotal = collect($order['items'])->sum(fn ($i) => $i['qty'] * $i['price']); @endphp

                <div class="bg-white border border-gray-border rounded-xl p-4" data-order-card>

                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-xs font-bold text-navy">{{ $order['id'] }}</p>
                                <span class="text-[9px] font-semibold px-2 py-0.5 rounded-full bg-coral/10 text-coral">
                                    {{ $order['status'] }}
                                </span>
                            </div>
                            <p class="text-[10px] text-navy/45 mt-1">
                                {{ $order['buyer'] }} · placed {{ $order['placed_at'] }}
                            </p>
                        </div>

                        <p class="text-sm font-bold text-navy tabular-nums">
                            ₱{{ number_format($itemsTotal) }}
                        </p>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-border space-y-1.5">
                        @foreach ($order['items'] as $item)
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-navy/70">{{ $item['qty'] }}× {{ $item['name'] }}</span>
                                <span class="text-navy/50 tabular-nums">₱{{ number_format($item['qty'] * $item['price']) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <button
                            type="button"
                            data-open-order-details
                            data-order='{{ json_encode($order) }}'
                            class="text-[10px] font-semibold text-teal-dark hover:text-teal-dark/70 transition"
                        >
                            View full details
                        </button>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('seller.orders.notifications') }}">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                <input type="hidden" name="action" value="decline">
                                <button
                                    type="submit"
                                    class="h-8 px-3 rounded-lg border border-gray-border text-[11px] font-semibold text-navy/60 hover:bg-gray-bg transition-colors"
                                >
                                    Decline
                                </button>
                            </form>

                            <form method="POST" action="{{ route('seller.orders.notifications') }}">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                <input type="hidden" name="action" value="accept">
                                <button
                                    type="submit"
                                    class="h-8 px-3.5 rounded-lg bg-navy hover:bg-navy/90 text-[11px] font-semibold text-white transition-colors"
                                >
                                    Accept Order
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

    @endif

</div>


{{-- ORDER DETAILS MODAL --}}
<div id="orderDetailsModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="orderDetailsBackdrop" class="absolute inset-0 bg-navy/40"></div>

    <div class="relative bg-white rounded-xl shadow-panel w-full max-w-md max-h-[90vh] overflow-y-auto content-scrollbar">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">
            <p class="text-sm font-bold text-navy">Order <span id="detailsOrderId"></span></p>
            <button type="button" data-close-order-details class="w-7 h-7 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition-colors">
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="px-5 py-4 space-y-4">
            <div>
                <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 mb-1">Buyer</p>
                <p class="text-xs text-navy" id="detailsBuyer"></p>
            </div>

            <div>
                <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 mb-1">Delivery Address</p>
                <p class="text-xs text-navy/70" id="detailsAddress"></p>
            </div>

            <div>
                <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 mb-1">Items</p>
                <div id="detailsItems" class="space-y-1.5"></div>
            </div>

            <div id="detailsNoteWrap">
                <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 mb-1">Buyer Note</p>
                <p class="text-xs text-navy/70" id="detailsNote"></p>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('orderDetailsModal');

    function open(order) {
        document.getElementById('detailsOrderId').textContent = order.id ?? '';
        document.getElementById('detailsBuyer').textContent = order.buyer ?? '';
        document.getElementById('detailsAddress').textContent = order.address ?? '';

        const itemsWrap = document.getElementById('detailsItems');
        itemsWrap.innerHTML = '';
        (order.items ?? []).forEach(function (item) {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between text-[11px]';
            row.innerHTML = `<span class="text-navy/70">${item.qty}× ${item.name}</span><span class="text-navy/50">₱${(item.qty * item.price).toLocaleString()}</span>`;
            itemsWrap.appendChild(row);
        });

        const noteWrap = document.getElementById('detailsNoteWrap');
        if (order.note) {
            noteWrap.hidden = false;
            document.getElementById('detailsNote').textContent = order.note;
        } else {
            noteWrap.hidden = true;
        }

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function close() {
        modal.hidden = true;
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-open-order-details]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            open(JSON.parse(btn.getAttribute('data-order')));
        });
    });

    document.querySelectorAll('[data-close-order-details]').forEach(b => b.addEventListener('click', close));
    document.getElementById('orderDetailsBackdrop')?.addEventListener('click', close);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) close();
    });
});
</script>
@endpush

@endsection