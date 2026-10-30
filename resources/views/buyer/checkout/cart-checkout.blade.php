{{-- Path: resources/views/buyer/checkout/cart-checkout.blade.php --}}

@extends('layouts.app')

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

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 text-[11px] rounded-xl px-4 py-3 mb-4">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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


        {{-- No file upload anymore (online payment proof removed), so no multipart encoding needed. --}}
        <form
            id="checkoutForm"
            action="{{ route('buyer.checkout.place') }}"
            method="POST"
            class="grid lg:grid-cols-[minmax(0,1fr)_340px] gap-4 lg:gap-5 items-start"
        >
            @csrf

            <input type="hidden" name="voucher_code" id="voucherCodeHidden" value="{{ $initialVoucherCode }}">
            {{-- COD is the only payment method. The server must still enforce this. --}}
            <input type="hidden" name="payment_method" value="cod">
            {{-- Seller codes, Platform product codes, and Platform shipping codes are independent. --}}


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

                        <a
                            href="{{ route('buyer.profile') }}#address"
                            class="h-7 px-2.5 rounded-lg bg-gray-bg hover:bg-teal-light
                                   text-[9px] font-semibold text-navy/55 hover:text-teal-dark transition
                                   flex items-center"
                        >
                            Change
                        </a>
                    </div>


                    <div class="px-4 py-3.5">

                        <div class="flex items-start gap-3">

                            <span class="w-8 h-8 rounded-full bg-[#EAF9F5] text-teal-dark flex items-center justify-center shrink-0">
                                <x-lucide-house class="w-3.5 h-3.5" />
                            </span>

                            <div class="min-w-0 flex-1">

                                @if (empty($address['line']))
                                    <p class="text-[10.5px] text-red-500">
                                        No delivery address on file. Please
                                        <a href="{{ route('buyer.profile') }}#address" class="underline font-semibold">add one</a>
                                        before placing an order.
                                    </p>
                                @else
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
                                    </div>

                                    <p class="text-[10px] sm:text-[10.5px] text-navy/55 mt-1.5 leading-relaxed">
                                        {{ $address['line'] }}, {{ $address['city'] }}
                                    </p>
                                @endif

                            </div>
                        </div>
                    </div>
                </section>


                {{-- =================================================
                    ORDER ITEMS — GROUPED BY SHOP
                ================================================== --}}
                @foreach ($cartGroups as $groupIndex => $group)
                    @php
                        $standardFee = (float) $group['shipping_fee_standard'];
                        $shopId = $group['shop']['id'];
                        $shippingQuote = $group['shipping_quote'] ?? [];

                        $shopVouchers = $availableVouchers
                            ->filter(fn ($voucher) => (int) $voucher['seller_id'] === (int) $shopId)
                            ->values();
                    @endphp

                    <section
                        class="checkout-shop-group bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm"
                        data-standard-fee="{{ $standardFee }}"
                        data-shop-index="{{ $groupIndex }}"
                        data-shop-id="{{ $shopId }}"
                        data-base-fee="{{ $shippingQuote['base_fee'] ?? 0 }}"
                        data-distance-fee="{{ $shippingQuote['distance_fee'] ?? 0 }}"
                        data-linehaul-fee="{{ $shippingQuote['linehaul_fee'] ?? 0 }}"
                        data-included-weight="{{ $shippingQuote['included_weight_kg'] ?? 1 }}"
                        data-extra-weight-fee="{{ $shippingQuote['extra_weight_fee_per_kg'] ?? 0 }}"
                    >

                        {{-- Shop header --}}
                        <div class="flex items-center justify-between gap-3 px-4 py-2.5 border-b border-gray-border/80">

                            <div class="flex items-center gap-2 min-w-0">

                                <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0">
                                    <x-lucide-store class="w-3.5 h-3.5" />
                                </span>

                                <div class="min-w-0">
                                    <p class="text-[10.5px] sm:text-[11px] font-bold text-navy truncate">
                                        {{ $group['shop']['name'] }}
                                    </p>
                                </div>
                            </div>

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
                                    data-product-id="{{ $item['product_id'] }}"
                                    data-weight-kg="{{ $item['weight_kg'] ?? 0.5 }}"
                                >

                                    <div class="flex gap-3">

                                        <a href="{{ route('buyer.product.show', $item['product_id']) }}"
                                           class="w-16 h-16 sm:w-18 sm:h-18 rounded-xl overflow-hidden
                                                  bg-gray-bg border border-gray-border/80 shrink-0">
                                            <img
                                                src="{{ $item['image'] }}"
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


                                            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2 mt-2.5">

                                                <div class="flex flex-wrap items-center gap-1.5">

                                                    <span class="text-[12px] font-bold text-teal-dark">
                                                        {{-- Canonical discounted prices retain cents through display and placement. --}}
                                                        ₱{{ number_format($item['price'], 2) }}
                                                    </span>

                                                    @if ($item['original_price'])
                                                        <span class="text-[8.5px] text-navy/30 line-through">
                                                            ₱{{ number_format($item['original_price'], 2) }}
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

                                                    <div class="flex items-center border border-gray-border rounded-lg overflow-hidden bg-white">

                                                        <button
                                                            type="button"
                                                            data-qty-decrease
                                                            aria-label="Decrease quantity"
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
                                                            aria-label="Quantity"
                                                            class="w-9 h-7 text-center text-[10px] font-semibold
                                                                   border-x border-gray-border focus:outline-none"
                                                        >

                                                        <button
                                                            type="button"
                                                            data-qty-increase
                                                            aria-label="Increase quantity"
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

                            <div class="grid sm:grid-cols-[105px_1fr] gap-1.5 sm:gap-2 sm:items-center">

                                <label for="note-{{ $shopId }}" class="text-[9.5px] font-medium text-navy/45">
                                    Message
                                </label>

                                <input
                                    type="text"
                                    id="note-{{ $shopId }}"
                                    name="groups[{{ $shopId }}][note]"
                                    placeholder="Optional note for this seller"
                                    class="w-full h-9 text-[10px] text-navy rounded-lg border border-gray-border
                                           px-3 focus:outline-none focus:border-teal focus:ring-1 focus:ring-teal/10"
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
                                            name="shipping_method[{{ $shopId }}]"
                                            value="standard"
                                            data-fee="{{ $standardFee }}"
                                            {{ ($group['selected_shipping_method'] ?? 'standard') === 'standard' ? 'checked' : '' }}
                                            class="mt-0.5 accent-teal-dark w-3.5 h-3.5"
                                        >

                                        <div>
                                            <p class="text-[10px] font-semibold text-navy">Standard</p>
                                            {{-- Fixed shipping charge has no carrier delivery-time estimate. --}}
                                            <p class="text-[8.5px] text-navy/40 mt-0.5">Delivery time unavailable</p>
                                        </div>
                                    </div>

                                    <span class="text-[10px] font-bold text-navy shrink-0" data-shipping-price="standard">
                                        ₱{{ number_format($standardFee, 2) }}
                                    </span>
                                </label>


                            </div>
                            <div class="mt-2 rounded-lg bg-gray-bg/60 px-3 py-2 text-[8.5px] text-navy/45">
                                <span class="font-semibold text-navy/60">{{ $shippingQuote['route_label'] ?? 'Route-based shipping' }}</span>
                                · <span data-shipping-weight>{{ number_format((float)($shippingQuote['weight_kg'] ?? 0), 3) }} kg</span>
                                · Base ₱{{ number_format((float)($shippingQuote['base_fee'] ?? 0), 2) }}
                                + route ₱{{ number_format((float)($shippingQuote['distance_fee'] ?? 0), 2) }}
                                @if((float)($shippingQuote['linehaul_fee'] ?? 0) > 0) + linehaul ₱{{ number_format((float)$shippingQuote['linehaul_fee'], 2) }} @endif
                                <span data-weight-fee-note>@if((float)($shippingQuote['weight_fee'] ?? 0) > 0) + weight ₱{{ number_format((float)$shippingQuote['weight_fee'], 2) }} @endif</span>
                            </div>


                            {{-- Courier chosen by the Buyer (only couriers covering this address and seller) --}}
                            <div class="mt-3 pt-3 border-t border-gray-border/70">
                                <p class="text-[10px] font-bold text-navy">Courier</p>
                                <p class="text-[8.5px] text-navy/35 mt-0.5 mb-2">Choose who delivers your parcel</p>

                                @if (count($group['logistics_options']) > 0)
                                    <div class="grid sm:grid-cols-2 gap-2">
                                        @foreach ($group['logistics_options'] as $option)
                                            <label class="flex items-center gap-2 border border-gray-border rounded-xl px-3 py-2.5 cursor-pointer hover:border-teal/40 transition">
                                                <input type="radio" name="logistics_partner[{{ $shopId }}]" value="{{ $option['id'] }}"
                                                       class="accent-teal-dark w-3.5 h-3.5"
                                                       {{ (int) $group['selected_logistics_partner_id'] === (int) $option['id'] ? 'checked' : '' }}>
                                                <span class="text-[10px] font-semibold text-navy">{{ $option['name'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-[9px] text-navy/40">
                                        No courier currently covers both this Seller and your delivery address. Checkout is unavailable for this shop.
                                    </p>
                                @endif
                            </div>
                        </div>


                        {{-- Shop voucher (only vouchers issued by this seller) --}}
                        @if ($shopVouchers->isNotEmpty())
                            <div class="px-4 py-3 border-t border-gray-border/80">
                                <p class="text-[9px] font-semibold text-navy/45 mb-2">
                                    Vouchers from this seller
                                </p>

                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($shopVouchers as $voucher)
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
                        @endif


                        {{-- Shop subtotal --}}
                        <div class="flex items-center justify-between gap-3 px-4 py-2.5
                                    bg-gray-bg/55 border-t border-gray-border/80">

                            <span class="text-[9px] text-navy/45">
                                Shop subtotal · {{ count($group['items']) }}
                                {{ \Illuminate\Support\Str::plural('item', count($group['items'])) }}
                            </span>

                            <span class="shop-subtotal text-[11px] font-bold text-navy"
                                  data-shop-subtotal="{{ $groupIndex }}">
                                ₱0.00
                            </span>
                        </div>

                    </section>
                @endforeach


                {{-- PAYMENT METHOD — Cash on Delivery only --}}
                <section class="bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm">

                    <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-border/80">

                        <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                            <x-lucide-wallet class="w-3.5 h-3.5" />
                        </span>

                        <div>
                            <p class="text-[11px] font-bold text-navy">Payment Method</p>
                            <p class="text-[8.5px] text-navy/35 mt-0.5">Pay in cash when your order arrives</p>
                        </div>
                    </div>


                    <div class="p-3">

                        <div class="flex items-center justify-between gap-3
                                    border border-teal bg-teal-light/35 rounded-xl px-3 py-2.5">

                            <div class="flex items-center gap-2.5 min-w-0">

                                <span class="w-8 h-8 rounded-lg bg-white border border-gray-border
                                             flex items-center justify-center shrink-0">
                                    <x-lucide-banknote class="w-3.5 h-3.5 text-teal-dark" />
                                </span>

                                <div class="min-w-0">
                                    <p class="text-[10.5px] font-semibold text-navy">Cash on Delivery</p>
                                    <p class="text-[8.5px] text-navy/40 mt-0.5">
                                        Pay the rider when your order arrives
                                    </p>
                                </div>
                            </div>

                            <span class="w-5 h-5 rounded-full bg-teal text-white flex items-center justify-center shrink-0">
                                <x-lucide-check class="w-3 h-3" />
                            </span>
                        </div>


                        <ul class="mt-3 space-y-1.5 px-1 text-[9px] text-navy/45 leading-relaxed">
                            <li class="flex items-start gap-1.5">
                                <x-lucide-circle-check class="w-3 h-3 mt-0.5 shrink-0 text-teal-dark" />
                                Please prepare the exact amount shown in Total Payment.
                            </li>
                            <li class="flex items-start gap-1.5">
                                <x-lucide-circle-check class="w-3 h-3 mt-0.5 shrink-0 text-teal-dark" />
                                Make sure someone can receive the parcel at your delivery address.
                            </li>
                        </ul>

                    </div>

                </section>

            </div>


            {{-- =================================================
                RIGHT — VOUCHER + ORDER SUMMARY
            ================================================== --}}
            <aside class="space-y-3 lg:sticky lg:top-4">

                {{-- Seller voucher --}}
                <section class="bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm">

                    <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-gray-border/80">

                        <div class="flex items-center gap-2">

                            <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                                <x-lucide-ticket-percent class="w-3.5 h-3.5" />
                            </span>

                            <div>
                                <p class="text-[11px] font-bold text-navy">Voucher</p>
                                <p class="text-[8.5px] text-navy/35 mt-0.5">Applies only to the matching seller</p>
                            </div>

                        </div>

                        <span id="voucherAppliedBadge"
                              class="{{ $initialVoucherCode ? '' : 'hidden' }} text-[7.5px] font-bold
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
                                aria-label="Seller voucher code"
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

                        @if ($availableVouchers->isEmpty())
                            <p class="text-[9px] text-navy/35 mt-3">
                                No vouchers available for the items in this order.
                            </p>
                        @endif

                    </div>

                </section>


                {{-- Platform-funded promotions --}}
                <section class="bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm">

                    <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-border/80">

                        <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                            <x-lucide-badge-percent class="w-3.5 h-3.5" />
                        </span>

                        <div>
                            <p class="text-[11px] font-bold text-navy">ShopHop Vouchers</p>
                            <p class="text-[8.5px] text-navy/35 mt-0.5">Funded by ShopHop Admin</p>
                        </div>
                    </div>


                    <div class="p-3 space-y-3">

                        <div>
                            <label for="platformVoucherInput" class="block text-[9px] font-semibold text-navy/45 mb-1.5">
                                Merchandise voucher
                            </label>

                            <input
                                id="platformVoucherInput"
                                name="merchandise_voucher_code"
                                maxlength="40"
                                value="{{ $platformVoucherCode }}"
                                placeholder="Enter code"
                                class="w-full h-9 text-[9.5px] rounded-lg border border-gray-border
                                       px-3 uppercase focus:outline-none focus:border-teal focus:ring-1 focus:ring-teal/10"
                            >
                        </div>

                        <div>
                            <label for="shippingVoucherInput" class="block text-[9px] font-semibold text-navy/45 mb-1.5">
                                Free shipping voucher
                            </label>

                            <input
                                id="shippingVoucherInput"
                                name="shipping_voucher_code"
                                maxlength="40"
                                value="{{ $shippingVoucherCode }}"
                                placeholder="Enter code"
                                class="w-full h-9 text-[9.5px] rounded-lg border border-gray-border
                                       px-3 uppercase focus:outline-none focus:border-teal focus:ring-1 focus:ring-teal/10"
                            >
                        </div>

                        <div>
                            <p class="text-[9px] font-semibold text-navy/45 mb-1.5">Available codes</p>

                            @forelse ($platformVouchers as $promo)
                                <button
                                    type="button"
                                    class="platform-suggestion inline-flex items-center gap-1 mr-1 mb-1
                                           px-2 py-1 rounded-md bg-gray-bg hover:bg-teal-light
                                           text-[8px] font-semibold text-navy/45 hover:text-teal-dark transition"
                                    data-code="{{ $promo['code'] }}"
                                    data-kind="{{ $promo['kind'] }}"
                                >
                                    <x-lucide-ticket class="w-2.5 h-2.5" />
                                    {{ $promo['code'] }}
                                    <span class="text-navy/30">({{ $promo['kind'] }})</span>
                                </button>
                            @empty
                                <p class="text-[9px] text-navy/35">No ShopHop vouchers currently available.</p>
                            @endforelse
                        </div>

                        <p id="platformVoucherFeedback" class="text-[8.5px] leading-relaxed text-navy/40">
                            ShopHop funds these discounts; Seller earnings remain unchanged.
                        </p>

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
                                {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}
                                · {{ $shopCount }} {{ \Illuminate\Support\Str::plural('shop', $shopCount) }}
                            </p>
                        </div>
                    </div>


                    <div class="space-y-2.5 text-[10px]">

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-navy/50">Merchandise</span>
                            <span id="summarySubtotal" class="font-semibold text-navy">₱0.00</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-navy/50">Shipping</span>
                            <span id="summaryShipping" class="font-semibold text-navy">₱0.00</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-navy/50">Product savings</span>
                            <span id="summaryProductSavings" class="font-semibold text-teal-dark">-₱0.00</span>
                        </div>

                        <div id="summaryVoucherRow"
                             class="hidden items-center justify-between gap-3">
                            <span class="text-navy/50">
                                Voucher
                                <span id="summaryVoucherCode" class="text-[7.5px] font-mono text-teal-dark"></span>
                            </span>

                            <span id="summaryVoucherDiscount" class="font-semibold text-teal-dark">-₱0.00</span>
                        </div>

                        <div id="summaryPlatformRow" class="hidden items-center justify-between gap-3">
                            <span class="text-navy/50">ShopHop discount</span>
                            <span id="summaryPlatformDiscount" class="font-semibold text-teal-dark"></span>
                        </div>

                        <div id="summaryShippingPromoRow" class="hidden items-center justify-between gap-3">
                            <span class="text-navy/50">Free shipping</span>
                            <span id="summaryShippingPromoDiscount" class="font-semibold text-teal-dark"></span>
                        </div>

                    </div>


                    <div class="border-t border-gray-border mt-4 pt-4">

                        <div class="flex items-end justify-between gap-3">

                            <div>
                                <p class="text-[9px] text-navy/40">Total Payment</p>
                                <p class="text-[8px] text-navy/30 mt-0.5">Pay in cash on delivery</p>

                                <p id="summarySavedText"
                                   class="text-[8px] text-teal-dark mt-0.5">
                                    You save ₱0.00
                                </p>
                            </div>

                            <span id="summaryTotal"
                                  class="text-[20px] leading-none font-bold text-teal-dark">
                                ₱0.00
                            </span>
                        </div>
                    </div>


                    <button
                        type="submit"
                        id="placeOrderBtn"
                        {{ empty($address['line']) || $itemCount === 0 ? 'disabled' : '' }}
                        class="w-full h-10 mt-4 rounded-xl bg-teal hover:bg-teal-dark
                               disabled:bg-gray-border disabled:cursor-not-allowed
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
                        <x-lucide-banknote class="w-3.5 h-3.5 text-teal-dark mx-auto" />
                        <p class="text-[7.5px] text-navy/45 mt-1">Pay on Delivery</p>
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
                    <p class="text-[8px] text-navy/40">Total Payment · COD</p>

                    <p id="mobileSummaryTotal"
                       class="text-[15px] font-bold text-teal-dark leading-tight mt-0.5">
                        ₱0.00
                    </p>
                </div>

                <button
                    type="button"
                    id="mobilePlaceOrderBtn"
                    {{ empty($address['line']) || $itemCount === 0 ? 'disabled' : '' }}
                    class="h-9 px-4 rounded-xl bg-teal hover:bg-teal-dark
                           disabled:bg-gray-border disabled:cursor-not-allowed
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

    const voucherCatalog = @json(
        $availableVouchers->mapWithKeys(fn ($voucher) => [$voucher['code'] => $voucher])
    );

    const initialVoucherCode = @json($initialVoucherCode);
    const platformCatalog = @json(collect($platformVouchers)->keyBy('code'));
    const platformInput = document.getElementById('platformVoucherInput');
    const shippingInput = document.getElementById('shippingVoucherInput');
    const platformFeedback = document.getElementById('platformVoucherFeedback');


    const shopGroups = document.querySelectorAll('.checkout-shop-group');

    const summarySubtotal = document.getElementById('summarySubtotal');
    const summaryShipping = document.getElementById('summaryShipping');
    const summaryProductSavings = document.getElementById('summaryProductSavings');

    const summaryVoucherRow = document.getElementById('summaryVoucherRow');
    const summaryVoucherCode = document.getElementById('summaryVoucherCode');
    const summaryVoucherDiscount = document.getElementById('summaryVoucherDiscount');

    const summaryTotal = document.getElementById('summaryTotal');
    const summarySavedText = document.getElementById('summarySavedText');

    const mobileSummaryTotal = document.getElementById('mobileSummaryTotal');

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

    let currentVoucherDiscount = 0;


    function formatPeso(amount) {
        // Summary previews retain cents so the server's persisted decimal total is not visually rounded.
        return '₱' + Math.max(0, Number(amount) || 0)
            .toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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


    function voucherDiscountForShop(shopSubtotal, shippingFee, sellerGroupSubtotal = shopSubtotal) {
        const voucher = activeVoucherCode ? voucherCatalog[activeVoucherCode] : null;

        if (!voucher) return 0;

        const minimum = Number(voucher.min_spend || 0);

        if (shopSubtotal < minimum) {
            return 0;
        }

        const base = voucher.kind === 'shipping' ? shippingFee : shopSubtotal;
        const raw = voucher.type === 'percent'
            ? Math.min(base, base * (Number(voucher.value || 0) / 100))
            : Math.min(Number(voucher.value || 0), base);

        // Seller-funded shipping cannot exceed the Seller's available merchandise net
        // after ShopHop's locked 10% commission. This prevents an accidental platform subsidy.
        if (voucher.kind === 'shipping') {
            return Math.min(raw, Math.max(0, sellerGroupSubtotal * 0.90));
        }
        return raw;
    }


    function syncVoucherUI(voucherEligibleShopSubtotal, discount) {
        const voucher = activeVoucherCode ? voucherCatalog[activeVoucherCode] : null;

        currentVoucherDiscount = discount;

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
            const show = voucher && currentVoucherDiscount > 0;

            summaryVoucherRow.classList.toggle('hidden', !show);
            summaryVoucherRow.classList.toggle('flex', !!show);
        }

        if (voucher) {
            const minimum = Number(voucher.min_spend || 0);

            if (voucherEligibleShopSubtotal >= minimum) {
                voucherMessage.textContent =
                    activeVoucherCode + ' applied — you save ' +
                    formatPeso(currentVoucherDiscount) + '.';

                voucherMessage.classList.remove('hidden', 'text-red-500');
                voucherMessage.classList.add('text-teal-dark');
            } else {
                voucherMessage.textContent =
                    'Spend ' + formatPeso(minimum) +
                    ' on this seller\'s items to use ' + activeVoucherCode + '.';

                voucherMessage.classList.remove('hidden', 'text-teal-dark');
                voucherMessage.classList.add('text-red-500');
            }
        }
    }


    function promoDiscount(input, kind, eligible, merchandiseBase) {
        const code = (input?.value || '').trim().toUpperCase();
        if (!code) return 0;

        const promo = platformCatalog[code];

        if (!promo || promo.kind !== kind || merchandiseBase < Number(promo.min_spend || 0) || eligible <= 0) {
            return 0;
        }

        return Math.round(Math.min(eligible, promo.type === 'percent'
            ? eligible * Number(promo.value) / 100
            : Number(promo.value)) * 100) / 100;
    }


    function recalculate() {
        let merchandiseSubtotal = 0;
        let shippingTotal = 0;
        let productSavings = 0;
        let voucherEligibleShopSubtotal = 0;
        let voucherDiscount = 0;

        const voucher = activeVoucherCode ? voucherCatalog[activeVoucherCode] : null;

        shopGroups.forEach(function (group, index) {
            let groupSubtotal = 0;
            let eligibleSubtotal = 0;
            let groupWeight = 0;

            group.querySelectorAll('.checkout-item').forEach(function (row) {
                const price = Number(row.dataset.price || 0);
                const originalPrice = Number(row.dataset.originalPrice || price);

                const qtyInput = row.querySelector('[data-qty-input]');
                const qty = Math.max(1, parseInt(qtyInput?.value, 10) || 1);

                groupSubtotal += price * qty;
                groupWeight += Number(row.dataset.weightKg || 0.5) * qty;

                // Match the server voucher rule by counting only assigned Product rows in this seller group.
                if (voucher && String(group.dataset.shopId) === String(voucher.seller_id)
                    && voucher.product_ids.includes(Number(row.dataset.productId))) {
                    eligibleSubtotal += price * qty;
                }

                productSavings += Math.max(0, originalPrice - price) * qty;
            });

            merchandiseSubtotal += groupSubtotal;

            const baseFee = Number(group.dataset.baseFee || 0);
            const distanceFee = Number(group.dataset.distanceFee || 0);
            const linehaulFee = Number(group.dataset.linehaulFee || 0);
            const includedWeight = Number(group.dataset.includedWeight || 1);
            const extraWeightFee = Number(group.dataset.extraWeightFee || 0);
            const extraKg = Math.max(0, groupWeight - includedWeight);
            const weightFee = Math.ceil(Math.max(0, extraKg - 0.000001)) * extraWeightFee;
            const standardDynamic = Math.round((baseFee + distanceFee + linehaulFee + weightFee) * 100) / 100;
            const standardRadio = group.querySelector('input[value="standard"][name^="shipping_method"]');
            if (standardRadio) standardRadio.dataset.fee = standardDynamic;
            const standardPrice = group.querySelector('[data-shipping-price="standard"]');
            if (standardPrice) standardPrice.textContent = formatPeso(standardDynamic);
            const weightLabel = group.querySelector('[data-shipping-weight]');
            if (weightLabel) weightLabel.textContent = groupWeight.toFixed(3) + ' kg';
            const weightFeeNote = group.querySelector('[data-weight-fee-note]');
            if (weightFeeNote) weightFeeNote.textContent = weightFee > 0 ? ' + weight ' + formatPeso(weightFee) : '';

            const selectedShipping = group.querySelector(
                'input[type="radio"][name^="shipping_method"]:checked'
            );
            const selectedShippingFee = Number(selectedShipping?.dataset.fee || 0);
            shippingTotal += selectedShippingFee;

            const groupSubtotalEl = group.querySelector(
                '[data-shop-subtotal="' + index + '"]'
            );

            if (groupSubtotalEl) {
                groupSubtotalEl.textContent = formatPeso(groupSubtotal);
            }

            if (voucher && String(group.dataset.shopId) === String(voucher.seller_id)) {
                voucherEligibleShopSubtotal = eligibleSubtotal;
                voucherDiscount = voucherDiscountForShop(eligibleSubtotal, selectedShippingFee, groupSubtotal);
            }
        });

        syncVoucherUI(voucherEligibleShopSubtotal, voucherDiscount);

        const sellerMerchandiseSaving = (voucher?.kind === 'shipping') ? 0 : currentVoucherDiscount;
        const sellerShippingSaving = (voucher?.kind === 'shipping') ? currentVoucherDiscount : 0;
        const netMerchandise = Math.max(0, merchandiseSubtotal - sellerMerchandiseSaving);
        const netShipping = Math.max(0, shippingTotal - sellerShippingSaving);
        const platformSaving = promoDiscount(platformInput, 'merchandise', netMerchandise, netMerchandise);
        const shippingSaving = promoDiscount(shippingInput, 'shipping', netShipping, netMerchandise);

        const platformRow = document.getElementById('summaryPlatformRow');
        const shippingRow = document.getElementById('summaryShippingPromoRow');

        platformRow.classList.toggle('hidden', platformSaving <= 0);
        platformRow.classList.toggle('flex', platformSaving > 0);
        shippingRow.classList.toggle('hidden', shippingSaving <= 0);
        shippingRow.classList.toggle('flex', shippingSaving > 0);

        document.getElementById('summaryPlatformDiscount').textContent = '-' + formatPeso(platformSaving);
        document.getElementById('summaryShippingPromoDiscount').textContent = '-' + formatPeso(shippingSaving);

        if (platformFeedback) {
            platformFeedback.textContent = 'ShopHop-funded savings: ' + formatPeso(platformSaving + shippingSaving) +
                '. Final eligibility and amount are validated by the server.';
        }

        const total = Math.max(0, merchandiseSubtotal + shippingTotal
            - sellerMerchandiseSaving - sellerShippingSaving - platformSaving - shippingSaving);

        summarySubtotal.textContent = formatPeso(merchandiseSubtotal);
        summaryShipping.textContent = formatPeso(shippingTotal);
        summaryProductSavings.textContent = '-' + formatPeso(productSavings);

        summaryTotal.textContent = formatPeso(total);
        mobileSummaryTotal.textContent = formatPeso(total);

        summarySavedText.textContent =
            'You save ' + formatPeso(productSavings + currentVoucherDiscount + platformSaving + shippingSaving);
    }


    [platformInput, shippingInput].forEach(function (input) {
        input?.addEventListener('input', recalculate);
        input?.addEventListener('change', recalculate);
    });


    // Tap a ShopHop code chip to fill the matching input.
    document.querySelectorAll('.platform-suggestion').forEach(function (button) {
        button.addEventListener('click', function () {
            const target = button.dataset.kind === 'shipping' ? shippingInput : platformInput;

            if (!target) return;

            target.value = button.dataset.code;
            recalculate();
            showToast(button.dataset.code + ' added.');
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Quantity
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.checkout-item').forEach(function (row) {

        const decreaseButton = row.querySelector('[data-qty-decrease]');
        const increaseButton = row.querySelector('[data-qty-increase]');
        const qtyInput = row.querySelector('[data-qty-input]');

        if (!decreaseButton || !increaseButton || !qtyInput) {
            return;
        }

        const max = parseInt(qtyInput.max, 10) || 1;


        function normalizeQty(value) {
            return Math.max(1, Math.min(max, parseInt(value, 10) || 1));
        }


        decreaseButton.addEventListener('click', function () {
            qtyInput.value = normalizeQty((parseInt(qtyInput.value, 10) || 1) - 1);
            recalculate();
        });


        increaseButton.addEventListener('click', function () {
            qtyInput.value = normalizeQty((parseInt(qtyInput.value, 10) || 1) + 1);
            recalculate();
        });


        qtyInput.addEventListener('change', function () {
            qtyInput.value = normalizeQty(qtyInput.value);
            recalculate();
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Shipping option
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('[data-shipping-option]').forEach(function (option) {

        const radio = option.querySelector('input[type="radio"]');

        option.addEventListener('click', function () {
            radio.checked = true;

            const group = option.closest('[data-shipping-group]');

            group.querySelectorAll('[data-shipping-option]').forEach(function (item) {
                item.classList.remove('border-teal', 'bg-teal-light/35');
                item.classList.add('border-gray-border');
            });

            option.classList.remove('border-gray-border');
            option.classList.add('border-teal', 'bg-teal-light/35');

            recalculate();
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Voucher
    |--------------------------------------------------------------------------
    */
    function applyVoucher(code, showFeedback = true) {
        const normalized = String(code || '').trim().toUpperCase();

        voucherInput.value = normalized;

        if (!normalized || !voucherCatalog[normalized]) {
            activeVoucherCode = '';

            voucherMessage.textContent =
                normalized ? 'Invalid or expired voucher code.' : 'Enter a voucher code first.';

            voucherMessage.classList.remove('hidden', 'text-teal-dark');
            voucherMessage.classList.add('text-red-500');

            recalculate();

            if (showFeedback && normalized) {
                showToast('Voucher not available.');
            }

            return;
        }

        // A voucher code is eligible only when an assigned Product is selected from its seller.
        const belongsToCart = Array.from(shopGroups).some((group) =>
            String(group.dataset.shopId) === String(voucherCatalog[normalized].seller_id)
            && Array.from(group.querySelectorAll('.checkout-item')).some((row) =>
                voucherCatalog[normalized].product_ids.includes(Number(row.dataset.productId))
            )
        );

        if (!belongsToCart) {
            activeVoucherCode = '';

            voucherMessage.textContent = 'This voucher does not apply to any seller in your order.';
            voucherMessage.classList.remove('hidden', 'text-teal-dark');
            voucherMessage.classList.add('text-red-500');

            recalculate();

            if (showFeedback) {
                showToast('Voucher not applicable.');
            }

            return;
        }

        activeVoucherCode = normalized;

        recalculate();

        if (showFeedback) {
            // The server applies the code only after final eligibility and price validation.
            showToast(currentVoucherDiscount > 0
                ? normalized + ' selected for Checkout.'
                : 'This voucher does not meet the eligible spend minimum.');
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
    let isSubmitting = false;
    checkoutForm.addEventListener('submit', function (event) {
        // Repeated clicks on the same loaded form must not dispatch a second placement request.
        if (isSubmitting) {
            event.preventDefault();
            return;
        }
        isSubmitting = true;
        document.getElementById('placeOrderBtn').disabled = true;
        mobilePlaceOrderBtn.disabled = true;
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