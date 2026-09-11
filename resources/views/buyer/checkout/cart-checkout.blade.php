{{-- Path: resources/views/buyer/checkout/cart-checkout.blade.php --}}

@extends('layouts.app')

@php
    /*
    |--------------------------------------------------------------------------
    | TEMPORARY CHECKOUT PREVIEW DATA
    |--------------------------------------------------------------------------
    | Once the checkout backend is connected:
    | - replace $address with the buyer's saved/default address
    | - replace $allCartGroups with selected cart rows from session / DB
    | - validate voucher/payment server-side
    | - remove the demo submit preventDefault() in the script
    */

    $buyer = auth()->user();

    $buyerName = $buyer?->first_name
        ?? $buyer?->name
        ?? 'Buyer';

    $address = [
        'name' => $buyerName,
        'phone' => '+63 917 123 4567',
        'line' => 'Blk 4 Lot 12, Purok 3, Brgy. San Isidro',
        'city' => 'Santa Cruz, Laguna, 4009',
        'is_default' => true,
        'verified' => true,
    ];

    $allCartGroups = collect([
        [
            'shop' => [
                'id' => 1,
                'name' => 'ShopHop Tech Store',
                'response_rate' => '96%',
                'preferred' => true,
            ],
            'shipping_fee' => 58,
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Wireless Earbuds Pro with ENC Noise Reduction & Charging Case',
                    'image' => 'images/hero/earbuds.jpg',
                    'variant' => 'Black, Earbuds Only',
                    'price' => 1299,
                    'original_price' => 1699,
                    'qty' => 1,
                    'stock' => 42,
                ],
                [
                    'id' => 2,
                    'name' => 'ShopHop Fitness Watch, Heart Rate & Sleep Tracking',
                    'image' => 'images/hero/watch.jpg',
                    'variant' => 'Midnight Black, 44mm',
                    'price' => 2499,
                    'original_price' => null,
                    'qty' => 1,
                    'stock' => 15,
                ],
            ],
        ],
        [
            'shop' => [
                'id' => 2,
                'name' => 'StepUp Footwear PH',
                'response_rate' => '89%',
                'preferred' => false,
            ],
            'shipping_fee' => 65,
            'items' => [
                [
                    'id' => 3,
                    'name' => 'Everyday Running Sneakers, Lightweight & Breathable',
                    'image' => 'images/hero/sneaker.jpg',
                    'variant' => 'White, Size 9',
                    'price' => 1899,
                    'original_price' => 2199,
                    'qty' => 1,
                    'stock' => 8,
                ],
            ],
        ],
    ]);

    $availableVouchers = collect([
        [
            'code' => 'SHOPHOP100',
            'title' => '₱100 Off',
            'description' => '₱100 off on ₱1,000 minimum spend',
            'type' => 'fixed',
            'value' => 100,
            'min_spend' => 1000,
            'max_discount' => null,
        ],
        [
            'code' => 'WELCOME10',
            'title' => '10% Off',
            'description' => '10% off on ₱500+, capped at ₱200',
            'type' => 'percent',
            'value' => 10,
            'min_spend' => 500,
            'max_discount' => 200,
        ],
        [
            'code' => 'SAVE50',
            'title' => '₱50 Off',
            'description' => '₱50 off on ₱699 minimum spend',
            'type' => 'fixed',
            'value' => 50,
            'min_spend' => 699,
            'max_discount' => null,
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | Read selection / qty from cart page
    |--------------------------------------------------------------------------
    | cart.blade.php sends:
    | ?items[]=1&items[]=3&qty[1]=2&qty[3]=1&voucher=SHOPHOP100
    */

    $selectedItemIds = collect(request()->query('items', []))
        ->map(fn ($id) => (string) $id)
        ->filter()
        ->values();

    $queryQty = collect(request()->query('qty', []));
    $queryVoucher = strtoupper(trim((string) request()->query('voucher', '')));

    $cartGroups = $allCartGroups
        ->map(function ($group) use ($selectedItemIds, $queryQty) {
            $items = collect($group['items']);

            if ($selectedItemIds->isNotEmpty()) {
                $items = $items->filter(
                    fn ($item) => $selectedItemIds->contains((string) $item['id'])
                );
            }

            $group['items'] = $items
                ->map(function ($item) use ($queryQty) {
                    $requestedQty = (int) $queryQty->get((string) $item['id'], $item['qty']);

                    $item['qty'] = max(
                        1,
                        min((int) $item['stock'], $requestedQty ?: 1)
                    );

                    return $item;
                })
                ->values()
                ->all();

            return $group;
        })
        ->filter(fn ($group) => count($group['items']) > 0)
        ->values();

    // If there are no valid selected IDs, show preview cart items.
    if ($cartGroups->isEmpty()) {
        $cartGroups = $allCartGroups;
    }

    $itemCount = $cartGroups->sum(fn ($group) => count($group['items']));
    $shopCount = $cartGroups->count();

    $codFee = 20;

    $initialVoucher = $availableVouchers
        ->first(fn ($voucher) => $voucher['code'] === $queryVoucher);

    $initialVoucherCode = $initialVoucher['code'] ?? '';
@endphp


@section('title', 'Checkout - ShopHop')
@section('hideChrome', true)


@section('content')

@include('buyer.partials.navbar-buyer')


{{-- =========================================================
    BREADCRUMB
========================================================= --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <nav class="flex items-center gap-1.5 text-[10px] sm:text-[10.5px] text-navy/45 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('buyer.dashboard') }}" class="hover:text-teal-dark transition">Home</a>

            <x-lucide-chevron-right class="w-3 h-3 shrink-0 text-navy/25" />

            <a href="{{ url('/buyer/cart') }}" class="hover:text-teal-dark transition">Cart</a>

            <x-lucide-chevron-right class="w-3 h-3 shrink-0 text-navy/25" />

            <span class="text-navy font-medium">Checkout</span>
        </nav>
    </div>
</div>


{{-- =========================================================
    CHECKOUT
========================================================= --}}
<section class="bg-gray-bg/75 min-h-[72vh] py-5 sm:py-6">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3 mb-4 sm:mb-5">

            <div>
                <div class="inline-flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-widest text-teal-dark mb-1.5">
                    <x-lucide-shield-check class="w-3 h-3" />
                    Secure Checkout
                </div>

                <p role="heading" aria-level="1"
                   class="text-[24px] sm:text-[28px] font-bold leading-none tracking-tight text-navy">
                    Checkout
                </p>

                <p class="text-[10.5px] sm:text-[11px] text-navy/45 mt-1.5">
                    {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}
                    from {{ $shopCount }} {{ \Illuminate\Support\Str::plural('shop', $shopCount) }}.
                    Review everything before placing your order.
                </p>
            </div>


            {{-- Progress --}}
            <div class="hidden sm:flex items-center gap-1.5 bg-white border border-gray-border rounded-xl px-3 py-2 shadow-sm">

                <div class="flex items-center gap-1.5 text-[9px] font-semibold text-navy/45">
                    <span class="w-5 h-5 rounded-full bg-teal text-white flex items-center justify-center">
                        <x-lucide-check class="w-3 h-3" />
                    </span>
                    Cart
                </div>

                <div class="w-7 h-px bg-teal/40"></div>

                <div class="flex items-center gap-1.5 text-[9px] font-semibold text-teal-dark">
                    <span class="w-5 h-5 rounded-full bg-teal-light text-teal-dark flex items-center justify-center">
                        2
                    </span>
                    Checkout
                </div>

                <div class="w-7 h-px bg-gray-border"></div>

                <div class="flex items-center gap-1.5 text-[9px] font-semibold text-navy/30">
                    <span class="w-5 h-5 rounded-full bg-gray-bg text-navy/35 flex items-center justify-center">
                        3
                    </span>
                    Complete
                </div>

            </div>
        </div>


        <form
            id="checkoutForm"
            action="{{ url('/buyer/checkout/place-order') }}"
            method="POST"
            enctype="multipart/form-data"
            class="grid lg:grid-cols-[minmax(0,1fr)_340px] gap-4 lg:gap-5 items-start"
        >
            @csrf

            <input type="hidden" name="voucher_code" id="voucherCodeHidden" value="{{ $initialVoucherCode }}">


            {{-- =================================================
                LEFT COLUMN
            ================================================== --}}
            <div class="space-y-3.5 min-w-0">

                {{-- DELIVERY ADDRESS --}}
                <section class="bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm">

                    <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-border/80">

                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                                <x-lucide-map-pin class="w-3.5 h-3.5" />
                            </span>

                            <div>
                                <p class="text-[11px] font-bold text-navy">Delivery Address</p>
                                <p class="text-[8.5px] text-navy/35 mt-0.5">Where should we send your order?</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="h-7 px-2.5 rounded-lg bg-gray-bg hover:bg-teal-light
                                   text-[9px] font-semibold text-navy/55 hover:text-teal-dark transition"
                        >
                            Change
                        </button>
                    </div>


                    <div class="px-4 py-3.5">

                        <div class="flex items-start gap-3">

                            <span class="w-8 h-8 rounded-full bg-[#EAF9F5] text-teal-dark flex items-center justify-center shrink-0">
                                <x-lucide-house class="w-3.5 h-3.5" />
                            </span>

                            <div class="min-w-0 flex-1">

                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <p class="text-[11px] font-bold text-navy">
                                        {{ $address['name'] }}
                                    </p>

                                    <span class="text-[9.5px] text-navy/45">
                                        {{ $address['phone'] }}
                                    </span>

                                    @if ($address['is_default'])
                                        <span class="inline-flex text-[7.5px] font-bold px-1.5 py-0.5 rounded
                                                     bg-teal-light text-teal-dark">
                                            DEFAULT
                                        </span>
                                    @endif

                                    @if (! empty($address['verified']))
                                        <span class="inline-flex items-center gap-1 text-[7.5px] font-semibold text-teal-dark">
                                            <x-lucide-badge-check class="w-2.5 h-2.5" />
                                            Verified
                                        </span>
                                    @endif
                                </div>

                                <p class="text-[10px] sm:text-[10.5px] text-navy/55 mt-1.5 leading-relaxed">
                                    {{ $address['line'] }}, {{ $address['city'] }}
                                </p>

                            </div>
                        </div>
                    </div>
                </section>


                {{-- =================================================
                    ORDER ITEMS — GROUPED BY SHOP
                ================================================== --}}
                @foreach ($cartGroups as $groupIndex => $group)
                    @php
                        $standardFee = (float) $group['shipping_fee'];
                        $expressFee = $standardFee + 70;
                    @endphp

                    <section
                        class="checkout-shop-group bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm"
                        data-standard-fee="{{ $standardFee }}"
                        data-express-fee="{{ $expressFee }}"
                        data-shop-index="{{ $groupIndex }}"
                    >

                        {{-- Shop header --}}
                        <div class="flex items-center justify-between gap-3 px-4 py-2.5 border-b border-gray-border/80">

                            <div class="flex items-center gap-2 min-w-0">

                                <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0">
                                    <x-lucide-store class="w-3.5 h-3.5" />
                                </span>

                                <div class="min-w-0">

                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <p class="text-[10.5px] sm:text-[11px] font-bold text-navy truncate">
                                            {{ $group['shop']['name'] }}
                                        </p>

                                        @if (! empty($group['shop']['preferred']))
                                            <span class="hidden sm:inline-flex text-[7.5px] font-bold px-1.5 py-0.5 rounded-full bg-teal text-white">
                                                Preferred
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-[8.5px] text-navy/35 mt-0.5">
                                        {{ $group['shop']['response_rate'] }} response rate
                                    </p>

                                </div>
                            </div>

                            <button type="button"
                                    class="inline-flex items-center gap-1 text-[9px] font-semibold text-teal-dark hover:text-navy transition shrink-0">
                                <x-lucide-message-circle class="w-3 h-3" />
                                Chat
                            </button>

                        </div>


                        {{-- Items --}}
                        <div class="divide-y divide-gray-border/80">

                            @foreach ($group['items'] as $item)
                                @php
                                    $discountPercent = $item['original_price']
                                        ? max(0, (int) round((1 - ($item['price'] / $item['original_price'])) * 100))
                                        : 0;
                                @endphp

                                <article
                                    class="checkout-item px-4 py-3"
                                    data-price="{{ $item['price'] }}"
                                    data-original-price="{{ $item['original_price'] ?? $item['price'] }}"
                                >

                                    <div class="flex gap-3">

                                        <a href="#"
                                           class="w-16 h-16 sm:w-17 sm:h-17 rounded-xl overflow-hidden
                                                  bg-gray-bg border border-gray-border/80 shrink-0">
                                            <img
                                                src="{{ asset($item['image']) }}"
                                                alt="{{ $item['name'] }}"
                                                class="w-full h-full object-cover"
                                            >
                                        </a>


                                        <div class="min-w-0 flex-1">

                                            <p class="text-[10.5px] sm:text-[11.5px] font-semibold text-navy leading-[1.4] line-clamp-2">
                                                {{ $item['name'] }}
                                            </p>

                                            <p class="text-[9px] text-navy/40 mt-1">
                                                {{ $item['variant'] }}
                                            </p>


                                            <div class="flex flex-wrap items-center justify-between gap-2 mt-2.5">

                                                <div class="flex flex-wrap items-center gap-1.5">

                                                    <span class="text-[12px] font-bold text-teal-dark">
                                                        ₱{{ number_format($item['price']) }}
                                                    </span>

                                                    @if ($item['original_price'])
                                                        <span class="text-[8.5px] text-navy/30 line-through">
                                                            ₱{{ number_format($item['original_price']) }}
                                                        </span>

                                                        <span class="text-[7.5px] font-bold text-teal-dark bg-teal-light px-1.5 py-0.5 rounded">
                                                            -{{ $discountPercent }}%
                                                        </span>
                                                    @endif

                                                </div>


                                                <div class="flex items-center gap-2">

                                                    <span class="hidden sm:inline text-[8.5px] text-navy/30">
                                                        {{ $item['stock'] }} available
                                                    </span>

                                                    <div class="flex items-center border border-gray-border rounded-lg overflow-hidden">

                                                        <button
                                                            type="button"
                                                            data-qty-decrease
                                                            class="w-7 h-7 flex items-center justify-center hover:bg-teal-light transition"
                                                        >
                                                            <x-lucide-minus class="w-3 h-3" />
                                                        </button>

                                                        <input
                                                            type="number"
                                                            name="items[{{ $item['id'] }}][quantity]"
                                                            data-qty-input
                                                            value="{{ $item['qty'] }}"
                                                            min="1"
                                                            max="{{ $item['stock'] }}"
                                                            class="w-9 h-7 text-center text-[10px] font-semibold
                                                                   border-x border-gray-border focus:outline-none"
                                                        >

                                                        <button
                                                            type="button"
                                                            data-qty-increase
                                                            class="w-7 h-7 flex items-center justify-center hover:bg-teal-light transition"
                                                        >
                                                            <x-lucide-plus class="w-3 h-3" />
                                                        </button>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>


                        {{-- Message to seller --}}
                        <div class="px-4 py-3 border-t border-gray-border/80">

                            <div class="grid sm:grid-cols-[105px_1fr] gap-2 sm:items-center">

                                <label class="text-[9.5px] font-medium text-navy/45">
                                    Message
                                </label>

                                <input
                                    type="text"
                                    name="groups[{{ $group['shop']['id'] }}][note]"
                                    placeholder="Optional note for this seller"
                                    class="w-full text-[10px] text-navy rounded-lg border border-gray-border
                                           px-3 py-2 focus:outline-none focus:border-teal focus:ring-1 focus:ring-teal/10"
                                >

                            </div>
                        </div>


                        {{-- Shipping --}}
                        <div class="px-4 py-3 border-t border-gray-border/80">

                            <div class="flex items-center justify-between gap-3 mb-2.5">
                                <div>
                                    <p class="text-[10px] font-bold text-navy">Shipping Option</p>
                                    <p class="text-[8.5px] text-navy/35 mt-0.5">Choose how fast you want it delivered</p>
                                </div>

                                <x-lucide-truck class="w-4 h-4 text-teal-dark" />
                            </div>


                            <div class="grid sm:grid-cols-2 gap-2" data-shipping-group>

                                <label class="shipping-option flex items-center justify-between gap-2
                                              border border-teal bg-teal-light/35 rounded-xl px-3 py-2.5
                                              cursor-pointer transition"
                                       data-shipping-option>

                                    <div class="flex items-start gap-2">

                                        <input
                                            type="radio"
                                            name="shipping_method[{{ $group['shop']['id'] }}]"
                                            value="standard"
                                            data-fee="{{ $standardFee }}"
                                            checked
                                            class="mt-0.5 accent-teal-dark w-3.5 h-3.5"
                                        >

                                        <div>
                                            <p class="text-[10px] font-semibold text-navy">Standard</p>
                                            <p class="text-[8.5px] text-navy/40 mt-0.5">2–5 days</p>
                                        </div>
                                    </div>

                                    <span class="text-[10px] font-bold text-navy">
                                        ₱{{ number_format($standardFee) }}
                                    </span>
                                </label>


                                <label class="shipping-option flex items-center justify-between gap-2
                                              border border-gray-border rounded-xl px-3 py-2.5
                                              cursor-pointer hover:border-teal/40 transition"
                                       data-shipping-option>

                                    <div class="flex items-start gap-2">

                                        <input
                                            type="radio"
                                            name="shipping_method[{{ $group['shop']['id'] }}]"
                                            value="express"
                                            data-fee="{{ $expressFee }}"
                                            class="mt-0.5 accent-teal-dark w-3.5 h-3.5"
                                        >

                                        <div>
                                            <p class="text-[10px] font-semibold text-navy">Express</p>
                                            <p class="text-[8.5px] text-navy/40 mt-0.5">1–2 days</p>
                                        </div>
                                    </div>

                                    <span class="text-[10px] font-bold text-navy">
                                        ₱{{ number_format($expressFee) }}
                                    </span>
                                </label>

                            </div>
                        </div>


                        {{-- Shop subtotal --}}
                        <div class="flex items-center justify-between gap-3 px-4 py-2.5
                                    bg-gray-bg/55 border-t border-gray-border/80">

                            <span class="text-[9px] text-navy/45">
                                Shop subtotal · {{ count($group['items']) }}
                                {{ \Illuminate\Support\Str::plural('item', count($group['items'])) }}
                            </span>

                            <span class="shop-subtotal text-[11px] font-bold text-navy"
                                  data-shop-subtotal="{{ $groupIndex }}">
                                ₱0
                            </span>
                        </div>

                    </section>
                @endforeach


                {{-- PAYMENT METHOD --}}
                <section class="bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm">

                    <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-border/80">

                        <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                            <x-lucide-wallet class="w-3.5 h-3.5" />
                        </span>

                        <div>
                            <p class="text-[11px] font-bold text-navy">Payment Method</p>
                            <p class="text-[8.5px] text-navy/35 mt-0.5">Choose how you want to pay</p>
                        </div>
                    </div>


                    <div class="p-3 space-y-2" data-payment-group>

                        {{-- COD --}}
                        <label
                            data-payment-option="cod"
                            class="payment-option flex items-center justify-between gap-3
                                   border border-teal bg-teal-light/35 rounded-xl px-3 py-2.5
                                   cursor-pointer transition"
                        >
                            <div class="flex items-center gap-2.5">

                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    checked
                                    class="accent-teal-dark w-3.5 h-3.5"
                                >

                                <span class="w-8 h-8 rounded-lg bg-white border border-gray-border
                                             flex items-center justify-center shrink-0">
                                    <x-lucide-banknote class="w-3.5 h-3.5 text-teal-dark" />
                                </span>

                                <div>
                                    <p class="text-[10.5px] font-semibold text-navy">Cash on Delivery</p>
                                    <p class="text-[8.5px] text-navy/40 mt-0.5">
                                        Pay when your order arrives
                                    </p>
                                </div>
                            </div>

                            <span class="text-[8px] font-semibold text-navy/35">
                                +₱{{ number_format($codFee) }}
                            </span>
                        </label>


                        {{-- GCASH --}}
                        <label
                            data-payment-option="gcash"
                            class="payment-option flex items-center justify-between gap-3
                                   border border-gray-border rounded-xl px-3 py-2.5
                                   cursor-pointer hover:border-teal/40 transition"
                        >
                            <div class="flex items-center gap-2.5">

                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="gcash"
                                    class="accent-teal-dark w-3.5 h-3.5"
                                >

                                <span class="w-8 h-8 rounded-lg bg-white border border-gray-border
                                             flex items-center justify-center shrink-0">
                                    <x-lucide-smartphone class="w-3.5 h-3.5 text-teal-dark" />
                                </span>

                                <div>
                                    <p class="text-[10.5px] font-semibold text-navy">
                                        GCash
                                        <span class="text-[8px] font-normal text-navy/35">(Manual verification)</span>
                                    </p>

                                    <p class="text-[8.5px] text-navy/40 mt-0.5">
                                        Upload proof after payment
                                    </p>
                                </div>
                            </div>

                            <span class="text-[8px] font-semibold text-teal-dark">
                                No fee
                            </span>
                        </label>

                    </div>


                    {{-- GCASH PANEL --}}
                    <div id="gcashPanel" class="hidden px-4 pb-4">

                        <div class="rounded-xl bg-[#EAF9F5] border border-teal/10 p-3">

                            <div class="grid sm:grid-cols-[105px_1fr] gap-3 items-start">

                                <div class="aspect-square rounded-xl bg-white border border-dashed border-teal/30
                                            flex flex-col items-center justify-center text-teal-dark">
                                    <x-lucide-qr-code class="w-7 h-7" />
                                    <span class="text-[8px] font-semibold mt-1">Scan to Pay</span>
                                </div>


                                <div>

                                    <p class="text-[9px] text-navy/45">Send exactly</p>

                                    <p class="text-[18px] leading-none font-bold text-teal-dark mt-1"
                                       id="gcashAmountText">
                                        ₱0
                                    </p>

                                    <p class="text-[9.5px] font-semibold text-navy mt-2">
                                        0917 123 4567
                                    </p>

                                    <p class="text-[8.5px] text-navy/40">
                                        ShopHop Store
                                    </p>


                                    <div class="grid sm:grid-cols-2 gap-2 mt-3">

                                        <div>
                                            <label class="text-[8.5px] font-medium text-navy/50 block mb-1">
                                                Reference Number
                                            </label>

                                            <input
                                                type="text"
                                                name="gcash_reference"
                                                id="gcashReference"
                                                placeholder="e.g. 0123456789012"
                                                class="w-full text-[9.5px] rounded-lg border border-gray-border
                                                       px-2.5 py-2 focus:outline-none focus:border-teal"
                                            >
                                        </div>


                                        <div>
                                            <label class="text-[8.5px] font-medium text-navy/50 block mb-1">
                                                Proof of Payment
                                            </label>

                                            <label
                                                for="gcashProof"
                                                class="h-8.5 flex items-center gap-1.5 border border-dashed border-gray-border
                                                       bg-white rounded-lg px-2.5 cursor-pointer hover:border-teal transition"
                                            >
                                                <x-lucide-upload class="w-3 h-3 text-navy/35 shrink-0" />

                                                <span id="gcashProofLabel"
                                                      class="text-[8.5px] text-navy/40 truncate">
                                                    Upload image
                                                </span>
                                            </label>

                                            <input
                                                type="file"
                                                name="gcash_proof"
                                                id="gcashProof"
                                                accept="image/*"
                                                class="hidden"
                                            >
                                        </div>
                                    </div>


                                    <p class="text-[8px] leading-relaxed text-navy/35 mt-2.5">
                                        GCash orders stay pending until the reference number and screenshot are verified.
                                    </p>

                                </div>
                            </div>
                        </div>
                    </div>

                </section>

            </div>


            {{-- =================================================
                RIGHT — VOUCHER + ORDER SUMMARY
            ================================================== --}}
            <aside class="space-y-3 lg:sticky lg:top-4">

                {{-- Voucher --}}
                <section class="bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm">

                    <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-gray-border/80">

                        <div class="flex items-center gap-2">

                            <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                                <x-lucide-ticket-percent class="w-3.5 h-3.5" />
                            </span>

                            <div>
                                <p class="text-[11px] font-bold text-navy">Voucher</p>
                                <p class="text-[8.5px] text-navy/35 mt-0.5">Apply one ShopHop voucher</p>
                            </div>

                        </div>

                        <span id="voucherAppliedBadge"
                              class="{{ $initialVoucher ? '' : 'hidden' }} text-[7.5px] font-bold
                                     bg-teal-light text-teal-dark px-1.5 py-1 rounded">
                            Applied
                        </span>
                    </div>


                    <div class="p-3">

                        <div class="flex gap-2">

                            <input
                                type="text"
                                id="voucherInput"
                                value="{{ $initialVoucherCode }}"
                                placeholder="Enter voucher code"
                                class="flex-1 min-w-0 h-9 text-[9.5px] rounded-lg border border-gray-border
                                       px-3 uppercase focus:outline-none focus:border-teal focus:ring-1 focus:ring-teal/10"
                            >

                            <button
                                type="button"
                                id="applyVoucherBtn"
                                class="h-9 px-3 rounded-lg bg-teal-light text-teal-dark
                                       hover:bg-teal hover:text-white text-[9px] font-bold transition"
                            >
                                Apply
                            </button>
                        </div>


                        <p id="voucherMessage"
                           class="hidden text-[8.5px] mt-1.5">
                        </p>


                        <div class="flex flex-wrap gap-1.5 mt-3">
                            @foreach ($availableVouchers as $voucher)
                                <button
                                    type="button"
                                    class="voucher-suggestion inline-flex items-center gap-1
                                           px-2 py-1 rounded-md bg-gray-bg hover:bg-teal-light
                                           text-[8px] font-semibold text-navy/45 hover:text-teal-dark transition"
                                    data-code="{{ $voucher['code'] }}"
                                >
                                    <x-lucide-ticket class="w-2.5 h-2.5" />
                                    {{ $voucher['code'] }}
                                </button>
                            @endforeach
                        </div>

                    </div>

                </section>


                {{-- Summary --}}
                <section class="bg-white border border-gray-border rounded-2xl p-4 shadow-sm">

                    <div class="flex items-center gap-2 mb-4">

                        <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                            <x-lucide-receipt-text class="w-3.5 h-3.5" />
                        </span>

                        <div>
                            <p class="text-[11px] font-bold text-navy">Order Summary</p>
                            <p class="text-[8.5px] text-navy/35 mt-0.5">
                                {{ $itemCount }} items · {{ $shopCount }} shops
                            </p>
                        </div>
                    </div>


                    <div class="space-y-2.5 text-[10px]">

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-navy/50">Merchandise</span>
                            <span id="summarySubtotal" class="font-semibold text-navy">₱0</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-navy/50">Shipping</span>
                            <span id="summaryShipping" class="font-semibold text-navy">₱0</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-navy/50">Product savings</span>
                            <span id="summaryProductSavings" class="font-semibold text-teal-dark">-₱0</span>
                        </div>

                        <div id="summaryVoucherRow"
                             class="hidden items-center justify-between gap-3">
                            <span class="text-navy/50">
                                Voucher
                                <span id="summaryVoucherCode" class="text-[7.5px] font-mono text-teal-dark"></span>
                            </span>

                            <span id="summaryVoucherDiscount" class="font-semibold text-teal-dark">-₱0</span>
                        </div>

                        <div id="summaryCodFeeRow"
                             class="flex items-center justify-between gap-3">

                            <span class="text-navy/50">COD handling fee</span>

                            <span id="summaryCodFee" class="font-semibold text-navy">
                                ₱{{ number_format($codFee) }}
                            </span>
                        </div>

                    </div>


                    <div class="border-t border-gray-border mt-4 pt-4">

                        <div class="flex items-end justify-between gap-3">

                            <div>
                                <p class="text-[9px] text-navy/40">Total Payment</p>

                                <p id="summarySavedText"
                                   class="text-[8px] text-teal-dark mt-0.5">
                                    You save ₱0
                                </p>
                            </div>

                            <span id="summaryTotal"
                                  class="text-[20px] leading-none font-bold text-teal-dark">
                                ₱0
                            </span>
                        </div>
                    </div>


                    <button
                        type="submit"
                        id="placeOrderBtn"
                        class="w-full h-10 mt-4 rounded-xl bg-teal hover:bg-teal-dark
                               text-white text-[11px] font-semibold
                               flex items-center justify-center gap-1.5
                               shadow-sm hover:shadow-md active:scale-[0.99]
                               transition-all"
                    >
                        Place Order
                        <x-lucide-arrow-right class="w-3.5 h-3.5" />
                    </button>


                    <p class="text-[8px] text-center text-navy/35 mt-2.5 leading-relaxed">
                        By placing this order, you agree to ShopHop's
                        <a href="#" class="text-teal-dark hover:underline">Terms of Service</a>.
                    </p>

                </section>


                {{-- Trust --}}
                <section class="grid grid-cols-3 gap-2">

                    <div class="bg-white border border-gray-border rounded-xl px-2 py-2.5 text-center">
                        <x-lucide-shield-check class="w-3.5 h-3.5 text-teal-dark mx-auto" />
                        <p class="text-[7.5px] text-navy/45 mt-1">Buyer Protection</p>
                    </div>

                    <div class="bg-white border border-gray-border rounded-xl px-2 py-2.5 text-center">
                        <x-lucide-rotate-ccw class="w-3.5 h-3.5 text-teal-dark mx-auto" />
                        <p class="text-[7.5px] text-navy/45 mt-1">Easy Returns</p>
                    </div>

                    <div class="bg-white border border-gray-border rounded-xl px-2 py-2.5 text-center">
                        <x-lucide-lock class="w-3.5 h-3.5 text-teal-dark mx-auto" />
                        <p class="text-[7.5px] text-navy/45 mt-1">Secure Payment</p>
                    </div>

                </section>

            </aside>
        </form>


        {{-- Mobile sticky place order --}}
        <div id="mobileCheckoutBar"
             class="lg:hidden sticky bottom-2 z-30 mt-4
                    bg-white/95 backdrop-blur border border-gray-border
                    rounded-2xl shadow-lg shadow-navy/10 px-3 py-2.5">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-[8px] text-navy/40">Total Payment</p>

                    <p id="mobileSummaryTotal"
                       class="text-[15px] font-bold text-teal-dark leading-tight mt-0.5">
                        ₱0
                    </p>
                </div>

                <button
                    type="button"
                    id="mobilePlaceOrderBtn"
                    class="h-9 px-4 rounded-xl bg-teal hover:bg-teal-dark
                           text-white text-[10.5px] font-semibold transition"
                >
                    Place Order
                </button>

            </div>
        </div>

    </div>
</section>


{{-- Toast --}}
<div id="checkoutToast"
     class="fixed left-1/2 bottom-5 z-50 -translate-x-1/2 translate-y-6 opacity-0 pointer-events-none
            bg-navy text-white text-[10px] font-medium px-3.5 py-2.5 rounded-xl
            shadow-xl transition-all duration-300">
</div>


@include('partials.footer')

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const codFee = {{ (float) $codFee }};

    const voucherCatalog = @json(
        $availableVouchers->mapWithKeys(fn ($voucher) => [$voucher['code'] => $voucher])
    );

    const initialVoucherCode = @json($initialVoucherCode);


    const shopGroups = document.querySelectorAll('.checkout-shop-group');

    const summarySubtotal = document.getElementById('summarySubtotal');
    const summaryShipping = document.getElementById('summaryShipping');
    const summaryProductSavings = document.getElementById('summaryProductSavings');

    const summaryVoucherRow = document.getElementById('summaryVoucherRow');
    const summaryVoucherCode = document.getElementById('summaryVoucherCode');
    const summaryVoucherDiscount = document.getElementById('summaryVoucherDiscount');

    const summaryCodFeeRow = document.getElementById('summaryCodFeeRow');
    const summaryCodFee = document.getElementById('summaryCodFee');
    const summaryTotal = document.getElementById('summaryTotal');
    const summarySavedText = document.getElementById('summarySavedText');

    const mobileSummaryTotal = document.getElementById('mobileSummaryTotal');

    const gcashAmountText = document.getElementById('gcashAmountText');

    const paymentOptions = document.querySelectorAll('[data-payment-option]');
    const gcashPanel = document.getElementById('gcashPanel');
    const gcashReference = document.getElementById('gcashReference');
    const gcashProof = document.getElementById('gcashProof');
    const gcashProofLabel = document.getElementById('gcashProofLabel');

    const voucherInput = document.getElementById('voucherInput');
    const applyVoucherBtn = document.getElementById('applyVoucherBtn');
    const voucherMessage = document.getElementById('voucherMessage');
    const voucherAppliedBadge = document.getElementById('voucherAppliedBadge');
    const voucherCodeHidden = document.getElementById('voucherCodeHidden');

    const checkoutForm = document.getElementById('checkoutForm');
    const mobilePlaceOrderBtn = document.getElementById('mobilePlaceOrderBtn');

    const checkoutToast = document.getElementById('checkoutToast');

    let activeVoucherCode =
        initialVoucherCode && voucherCatalog[initialVoucherCode]
            ? initialVoucherCode
            : '';

    let currentMerchandiseSubtotal = 0;
    let currentVoucherDiscount = 0;


    function formatPeso(amount) {
        return '₱' + Math.max(0, Math.round(Number(amount) || 0))
            .toLocaleString('en-PH');
    }


    function showToast(message) {
        if (!checkoutToast) return;

        checkoutToast.textContent = message;

        checkoutToast.classList.remove('translate-y-6', 'opacity-0');
        checkoutToast.classList.add('translate-y-0', 'opacity-100');

        clearTimeout(showToast.timer);

        showToast.timer = setTimeout(function () {
            checkoutToast.classList.remove('translate-y-0', 'opacity-100');
            checkoutToast.classList.add('translate-y-6', 'opacity-0');
        }, 2200);
    }


    function currentPaymentMethod() {
        const checked = document.querySelector(
            'input[name="payment_method"]:checked'
        );

        return checked ? checked.value : 'cod';
    }


    function voucherDiscountFor(subtotal) {
        if (!activeVoucherCode || !voucherCatalog[activeVoucherCode]) {
            return 0;
        }

        const voucher = voucherCatalog[activeVoucherCode];
        const minimum = Number(voucher.min_spend || 0);

        if (subtotal < minimum) {
            return 0;
        }

        if (voucher.type === 'percent') {
            const raw =
                subtotal * (Number(voucher.value || 0) / 100);

            const maximum =
                Number(voucher.max_discount || 0);

            return maximum > 0
                ? Math.min(raw, maximum)
                : raw;
        }

        return Number(voucher.value || 0);
    }


    function syncVoucherUI() {
        const voucher = activeVoucherCode
            ? voucherCatalog[activeVoucherCode]
            : null;

        currentVoucherDiscount =
            voucherDiscountFor(currentMerchandiseSubtotal);

        if (voucherCodeHidden) {
            voucherCodeHidden.value = voucher ? activeVoucherCode : '';
        }

        if (voucherAppliedBadge) {
            voucherAppliedBadge.classList.toggle('hidden', !voucher);
        }

        if (summaryVoucherCode) {
            summaryVoucherCode.textContent =
                voucher ? '(' + activeVoucherCode + ')' : '';
        }

        if (summaryVoucherDiscount) {
            summaryVoucherDiscount.textContent =
                '-' + formatPeso(currentVoucherDiscount);
        }

        if (summaryVoucherRow) {
            const show =
                voucher && currentVoucherDiscount > 0;

            summaryVoucherRow.classList.toggle('hidden', !show);
            summaryVoucherRow.classList.toggle('flex', !!show);
        }

        if (voucher) {
            const minimum = Number(voucher.min_spend || 0);

            if (currentMerchandiseSubtotal >= minimum) {
                voucherMessage.textContent =
                    activeVoucherCode + ' applied — you save ' +
                    formatPeso(currentVoucherDiscount) + '.';

                voucherMessage.classList.remove('hidden', 'text-red-500');
                voucherMessage.classList.add('text-teal-dark');
            } else {
                voucherMessage.textContent =
                    'Spend ' + formatPeso(minimum) +
                    ' to use ' + activeVoucherCode + '.';

                voucherMessage.classList.remove('hidden', 'text-teal-dark');
                voucherMessage.classList.add('text-red-500');
            }
        }
    }


    function recalculate() {
        let merchandiseSubtotal = 0;
        let shippingTotal = 0;
        let productSavings = 0;

        shopGroups.forEach(function (group, index) {
            let groupSubtotal = 0;

            group.querySelectorAll('.checkout-item').forEach(function (row) {
                const price =
                    Number(row.dataset.price || 0);

                const originalPrice =
                    Number(row.dataset.originalPrice || price);

                const qtyInput =
                    row.querySelector('[data-qty-input]');

                const qty =
                    Math.max(1, parseInt(qtyInput?.value, 10) || 1);

                groupSubtotal += price * qty;

                productSavings +=
                    Math.max(0, originalPrice - price) * qty;
            });

            const selectedShipping =
                group.querySelector(
                    'input[type="radio"][name^="shipping_method"]:checked'
                );

            shippingTotal +=
                Number(selectedShipping?.dataset.fee || 0);

            const groupSubtotalEl =
                group.querySelector(
                    '[data-shop-subtotal="' + index + '"]'
                );

            if (groupSubtotalEl) {
                groupSubtotalEl.textContent =
                    formatPeso(groupSubtotal);
            }
        });


        currentMerchandiseSubtotal =
            merchandiseSubtotal;

        syncVoucherUI();

        const isCod =
            currentPaymentMethod() === 'cod';

        const paymentFee =
            isCod ? codFee : 0;

        const total =
            merchandiseSubtotal +
            shippingTotal +
            paymentFee -
            currentVoucherDiscount;


        summarySubtotal.textContent =
            formatPeso(merchandiseSubtotal);

        summaryShipping.textContent =
            formatPeso(shippingTotal);

        summaryProductSavings.textContent =
            '-' + formatPeso(productSavings);

        summaryCodFee.textContent =
            formatPeso(codFee);

        summaryCodFeeRow.classList.toggle(
            'hidden',
            !isCod
        );

        summaryTotal.textContent =
            formatPeso(total);

        mobileSummaryTotal.textContent =
            formatPeso(total);

        summarySavedText.textContent =
            'You save ' +
            formatPeso(productSavings + currentVoucherDiscount);


        if (gcashAmountText) {
            gcashAmountText.textContent =
                formatPeso(
                    merchandiseSubtotal +
                    shippingTotal -
                    currentVoucherDiscount
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Quantity
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.checkout-item').forEach(function (row) {

        const decreaseButton =
            row.querySelector('[data-qty-decrease]');

        const increaseButton =
            row.querySelector('[data-qty-increase]');

        const qtyInput =
            row.querySelector('[data-qty-input]');

        if (!decreaseButton || !increaseButton || !qtyInput) {
            return;
        }

        const max =
            parseInt(qtyInput.max, 10) || 1;


        function normalizeQty(value) {
            return Math.max(
                1,
                Math.min(
                    max,
                    parseInt(value, 10) || 1
                )
            );
        }


        decreaseButton.addEventListener('click', function () {
            qtyInput.value =
                normalizeQty(
                    (parseInt(qtyInput.value, 10) || 1) - 1
                );

            recalculate();
        });


        increaseButton.addEventListener('click', function () {
            qtyInput.value =
                normalizeQty(
                    (parseInt(qtyInput.value, 10) || 1) + 1
                );

            recalculate();
        });


        qtyInput.addEventListener('change', function () {
            qtyInput.value =
                normalizeQty(qtyInput.value);

            recalculate();
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Shipping option
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('[data-shipping-option]').forEach(function (option) {

        const radio =
            option.querySelector('input[type="radio"]');

        option.addEventListener('click', function () {
            radio.checked = true;

            const group =
                option.closest('[data-shipping-group]');

            group
                .querySelectorAll('[data-shipping-option]')
                .forEach(function (item) {
                    item.classList.remove(
                        'border-teal',
                        'bg-teal-light/35'
                    );

                    item.classList.add(
                        'border-gray-border'
                    );
                });

            option.classList.remove(
                'border-gray-border'
            );

            option.classList.add(
                'border-teal',
                'bg-teal-light/35'
            );

            recalculate();
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    */
    paymentOptions.forEach(function (option) {

        const radio =
            option.querySelector('input[type="radio"]');

        option.addEventListener('click', function () {

            radio.checked = true;

            paymentOptions.forEach(function (item) {
                item.classList.remove(
                    'border-teal',
                    'bg-teal-light/35'
                );

                item.classList.add(
                    'border-gray-border'
                );
            });

            option.classList.remove(
                'border-gray-border'
            );

            option.classList.add(
                'border-teal',
                'bg-teal-light/35'
            );


            const isGcash =
                option.dataset.paymentOption === 'gcash';

            gcashPanel.classList.toggle(
                'hidden',
                !isGcash
            );

            gcashReference.required =
                isGcash;

            gcashProof.required =
                isGcash;

            recalculate();
        });

    });


    if (gcashProof && gcashProofLabel) {
        gcashProof.addEventListener('change', function () {

            const file =
                gcashProof.files?.[0];

            gcashProofLabel.textContent =
                file
                    ? file.name
                    : 'Upload image';

            gcashProofLabel.classList.toggle(
                'text-navy',
                !!file
            );

        });
    }


    /*
    |--------------------------------------------------------------------------
    | Voucher
    |--------------------------------------------------------------------------
    */
    function applyVoucher(code, showFeedback = true) {
        const normalized =
            String(code || '').trim().toUpperCase();

        voucherInput.value =
            normalized;

        if (!normalized || !voucherCatalog[normalized]) {
            activeVoucherCode = '';

            voucherMessage.textContent =
                normalized
                    ? 'Invalid or expired voucher code.'
                    : 'Enter a voucher code first.';

            voucherMessage.classList.remove(
                'hidden',
                'text-teal-dark'
            );

            voucherMessage.classList.add(
                'text-red-500'
            );

            recalculate();

            if (showFeedback && normalized) {
                showToast('Voucher not available.');
            }

            return;
        }

        activeVoucherCode =
            normalized;

        recalculate();

        if (showFeedback) {
            showToast(normalized + ' applied.');
        }
    }


    applyVoucherBtn.addEventListener('click', function () {
        applyVoucher(voucherInput.value);
    });


    voucherInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyVoucher(voucherInput.value);
        }
    });


    document.querySelectorAll('.voucher-suggestion').forEach(function (button) {
        button.addEventListener('click', function () {
            applyVoucher(button.dataset.code);
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */
    function tryPlaceOrder() {
        if (currentPaymentMethod() === 'gcash') {

            if (
                !gcashReference.value.trim() ||
                !gcashProof.files?.length
            ) {
                showToast(
                    'Add your GCash reference number and proof of payment.'
                );

                gcashPanel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                return false;
            }
        }

        return true;
    }


    checkoutForm.addEventListener('submit', function (event) {

        if (!tryPlaceOrder()) {
            event.preventDefault();
            return;
        }

        /*
         * TEMPORARY PREVIEW:
         * remove this preventDefault block once your real
         * /buyer/checkout/place-order controller is ready.
         */
        event.preventDefault();

        showToast(
            'Order ready to submit — connect the checkout controller next.'
        );
    });


    mobilePlaceOrderBtn.addEventListener('click', function () {
        checkoutForm.requestSubmit();
    });


    if (initialVoucherCode) {
        applyVoucher(initialVoucherCode, false);
    } else {
        recalculate();
    }

});
</script>
@endpush
