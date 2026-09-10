{{-- Path: resources/views/buyer/checkout/cart-checkout.blade.php --}}

@extends('layouts.app')

{{--
    TEMPORARY PREVIEW DATA
    In production, $cartGroups comes from the buyer's SELECTED
    (checked) cart items, grouped by shop — not hardcoded.

    This is now the ONLY checkout page. Both "Buy Now" (single item,
    pre-selected in the cart) and the normal "Add to Cart" flow end
    up here. place-order.blade.php (the old single-item direct
    checkout page) is no longer routed to — kept only as reference.

    Expected input once wired up: an array of selected cart item IDs
    from the cart page, e.g. ?items[]=1&items[]=3, used here to
    filter which cart rows to group and display.
--}}
@php
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
    ];

    $cartGroups = collect([
        [
            'shop' => ['id' => 1, 'name' => 'ShopHop Tech Store', 'response_rate' => '96%'],
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
            'shop' => ['id' => 2, 'name' => 'StepUp Footwear PH', 'response_rate' => '89%'],
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

    $itemCount = $cartGroups->sum(fn ($group) => count($group['items']));
    $codFee = 20;
@endphp

@section('title', 'Checkout - ShopHop')

@section('hideChrome', true)


@section('content')


{{-- =========================================================
    BUYER NAVBAR
========================================================= --}}
@include('buyer.partials.navbar-buyer')


{{-- =========================================================
    BREADCRUMB
========================================================= --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-260 mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-1.5 text-xs text-navy/45 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('buyer.dashboard') }}" class="hover:text-teal-dark transition">Home</a>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0" />
            <a href="#" class="hover:text-teal-dark transition">Cart</a>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0" />
            <span class="text-navy font-medium">Checkout</span>
        </nav>
    </div>
</div>


{{-- =========================================================
    CHECKOUT
========================================================= --}}
<section class="bg-gray-bg/70 py-5 sm:py-7">
    <div class="max-w-260 mx-auto px-4 sm:px-6 lg:px-8">

        <h1 class="text-navy text-xl sm:text-2xl font-bold mb-4 sm:mb-5">
            Checkout
            <span class="text-navy/40 font-medium text-base">({{ $itemCount }} {{ Str::plural('item', $itemCount) }})</span>
        </h1>

        <form
            id="checkoutForm"
            action="{{ url('/buyer/checkout/place-order') }}"
            method="POST"
            enctype="multipart/form-data"
            class="grid lg:grid-cols-[1fr_360px] gap-4 sm:gap-5 items-start"
        >
            @csrf

            {{-- =================================================
                LEFT COLUMN
            ================================================== --}}
            <div class="space-y-4 sm:space-y-5 min-w-0">

                {{-- DELIVERY ADDRESS --}}
                <div class="bg-white border border-gray-border rounded-2xl p-4 sm:p-5">

                    <div class="flex items-center gap-2 text-teal-dark mb-3">
                        <x-lucide-map-pin class="w-4 h-4" />
                        <span class="text-xs sm:text-sm font-semibold uppercase tracking-wide">
                            Delivery Address
                        </span>
                    </div>

                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-navy text-sm">{{ $address['name'] }}</span>
                                <span class="text-navy/40 text-sm">{{ $address['phone'] }}</span>
                                @if ($address['is_default'])
                                    <span class="text-[10px] font-bold border border-teal text-teal-dark px-2 py-0.5 rounded-md">
                                        Default
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs sm:text-sm text-navy/60 mt-1.5 leading-relaxed">
                                {{ $address['line'] }}, {{ $address['city'] }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="shrink-0 text-xs sm:text-sm font-semibold text-teal-dark hover:text-navy transition"
                        >
                            Change
                        </button>
                    </div>

                </div>


                {{-- =================================================
                    CART ITEMS — GROUPED BY SHOP
                ================================================== --}}
                @foreach ($cartGroups as $groupIndex => $group)
                    <div
                        class="checkout-shop-group bg-white border border-gray-border rounded-2xl overflow-hidden"
                        data-shipping-fee="{{ $group['shipping_fee'] }}"
                    >

                        <div class="flex items-center gap-2 px-4 sm:px-5 py-3.5 border-b border-gray-border">
                            <x-lucide-store class="w-4 h-4 text-navy/55" />
                            <span class="font-semibold text-navy text-sm">{{ $group['shop']['name'] }}</span>
                            <span class="text-[10px] text-navy/40">· {{ $group['shop']['response_rate'] }} response rate</span>
                            <button type="button" class="ml-auto text-xs font-semibold text-teal-dark hover:text-navy transition">
                                Chat
                            </button>
                        </div>

                        <div class="divide-y divide-gray-border">
                            @foreach ($group['items'] as $item)
                                <div
                                    class="checkout-item flex gap-3 sm:gap-4 px-4 sm:px-5 py-4"
                                    data-price="{{ $item['price'] }}"
                                >
                                    <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-gray-bg shrink-0">
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['name'] }}"
                                            class="w-full h-full object-cover"
                                        >
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-navy font-medium leading-snug line-clamp-2">
                                            {{ $item['name'] }}
                                        </p>
                                        <p class="text-xs text-navy/40 mt-1">
                                            Variation: {{ $item['variant'] }}
                                        </p>

                                        <div class="flex flex-wrap items-end justify-between gap-3 mt-3">
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-teal-dark font-bold text-sm sm:text-base">
                                                    ₱{{ number_format($item['price']) }}
                                                </span>
                                                @if ($item['original_price'])
                                                    <span class="text-navy/35 text-xs line-through">
                                                        ₱{{ number_format($item['original_price']) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center border border-gray-border rounded-lg overflow-hidden">
                                                <button
                                                    type="button"
                                                    data-qty-decrease
                                                    class="w-8 h-8 flex items-center justify-center hover:bg-teal-light transition"
                                                >
                                                    <x-lucide-minus class="w-3.5 h-3.5" />
                                                </button>
                                                <input
                                                    type="number"
                                                    name="items[{{ $item['id'] }}][quantity]"
                                                    data-qty-input
                                                    value="{{ $item['qty'] }}"
                                                    min="1"
                                                    max="{{ $item['stock'] }}"
                                                    class="w-11 h-8 text-center text-sm font-semibold border-x border-gray-border focus:outline-none"
                                                >
                                                <button
                                                    type="button"
                                                    data-qty-increase
                                                    class="w-8 h-8 flex items-center justify-center hover:bg-teal-light transition"
                                                >
                                                    <x-lucide-plus class="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="px-4 sm:px-5 py-3.5 border-t border-gray-border">
                            <label class="text-xs text-navy/45 block mb-1.5">Message to seller (optional)</label>
                            <input
                                type="text"
                                name="groups[{{ $group['shop']['id'] }}][note]"
                                placeholder="e.g. Please pack securely"
                                class="w-full text-sm rounded-lg border border-gray-border px-3 py-2.5 focus:outline-none focus:border-teal"
                            >
                        </div>

                        <label class="flex items-center justify-between gap-3 mx-4 sm:mx-5 mb-4 sm:mb-5 border border-teal bg-teal-light/40 rounded-xl px-4 py-3 cursor-pointer">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="shipping_method[{{ $group['shop']['id'] }}]" value="standard" checked class="accent-teal-dark w-4 h-4">
                                <div>
                                    <p class="text-sm font-medium text-navy">Standard Delivery</p>
                                    <p class="text-xs text-navy/45">Estimated arrival in 2–5 days</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-navy">₱{{ number_format($group['shipping_fee']) }}</span>
                        </label>

                        <div class="flex items-center justify-between px-4 sm:px-5 py-3 bg-gray-bg/60 border-t border-gray-border">
                            <span class="text-xs text-navy/50">
                                Shop Subtotal ({{ count($group['items']) }} {{ Str::plural('item', count($group['items'])) }})
                            </span>
                            <span class="shop-subtotal text-sm font-semibold text-navy" data-shop-subtotal="{{ $groupIndex }}">
                                ₱0
                            </span>
                        </div>

                    </div>
                @endforeach


                {{-- PAYMENT METHOD --}}
                <div class="bg-white border border-gray-border rounded-2xl p-4 sm:p-5">

                    <div class="flex items-center gap-2 text-navy mb-3">
                        <x-lucide-wallet class="w-4 h-4 text-teal-dark" />
                        <span class="text-sm font-semibold">Payment Method</span>
                    </div>

                    <div class="space-y-3" data-payment-group>

                        {{-- COD --}}
                        <label
                            data-payment-option="cod"
                            class="payment-option flex items-center justify-between gap-3 border-2 border-teal bg-teal-light/40 rounded-xl px-4 py-3.5 cursor-pointer transition"
                        >
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="cod" checked class="accent-teal-dark w-4 h-4">
                                <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center border border-gray-border shrink-0">
                                    <x-lucide-banknote class="w-4 h-4 text-teal-dark" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-navy">Cash on Delivery (COD)</p>
                                    <p class="text-xs text-navy/45">Pay in cash when your order arrives</p>
                                </div>
                            </div>
                        </label>

                        {{-- GCASH MANUAL --}}
                        <label
                            data-payment-option="gcash"
                            class="payment-option flex items-center justify-between gap-3 border-2 border-gray-border rounded-xl px-4 py-3.5 cursor-pointer transition"
                        >
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="gcash" class="accent-teal-dark w-4 h-4">
                                <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center border border-gray-border shrink-0">
                                    <x-lucide-smartphone class="w-4 h-4 text-teal-dark" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-navy">GCash <span class="text-navy/40 font-normal">(Manual)</span></p>
                                    <p class="text-xs text-navy/45">Send payment, then upload proof for verification</p>
                                </div>
                            </div>
                        </label>

                    </div>


                    {{-- GCASH DETAILS PANEL --}}
                    <div id="gcashPanel" class="hidden mt-4 pt-4 border-t border-gray-border">

                        <div class="grid sm:grid-cols-[140px_1fr] gap-4 items-start">

                            {{-- QR placeholder — swap for a real QR image asset --}}
                            <div class="w-full sm:w-35 aspect-square rounded-xl border-2 border-dashed border-gray-border flex flex-col items-center justify-center gap-1.5 text-navy/35 mx-auto sm:mx-0">
                                <x-lucide-qr-code class="w-8 h-8" />
                                <span class="text-[10px] font-medium">Scan to Pay</span>
                            </div>

                            <div>
                                <p class="text-sm text-navy">
                                    Send exactly
                                    <span class="font-bold text-teal-dark" id="gcashAmountText">₱0</span>
                                    to:
                                </p>
                                <p class="text-sm font-semibold text-navy mt-1">GCash — 0917 123 4567 (ShopHop Store)</p>

                                <div class="mt-4">
                                    <label class="text-xs text-navy/55 block mb-1.5">
                                        GCash Reference Number <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="gcash_reference"
                                        id="gcashReference"
                                        placeholder="e.g. 0123456789012"
                                        class="w-full text-sm rounded-lg border border-gray-border px-3 py-2.5 focus:outline-none focus:border-teal"
                                    >
                                </div>

                                <div class="mt-3">
                                    <label class="text-xs text-navy/55 block mb-1.5">
                                        Proof of Payment (screenshot) <span class="text-red-500">*</span>
                                    </label>

                                    <label
                                        for="gcashProof"
                                        class="flex items-center gap-3 border border-dashed border-gray-border rounded-lg px-3 py-2.5 cursor-pointer hover:border-teal transition"
                                    >
                                        <x-lucide-upload class="w-4 h-4 text-navy/45 shrink-0" />
                                        <span id="gcashProofLabel" class="text-xs sm:text-sm text-navy/45 truncate">
                                            Upload screenshot of your GCash payment
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

                                <p class="text-[11px] text-navy/40 mt-3 leading-relaxed">
                                    Your order will be marked <span class="font-medium text-navy/60">Pending Payment</span>
                                    until our team manually verifies your reference number and screenshot.
                                    This usually takes a few hours.
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>



            {{-- =================================================
                RIGHT COLUMN — ORDER SUMMARY
            ================================================== --}}
            <div class="lg:sticky lg:top-4">

                <div class="bg-white border border-gray-border rounded-2xl p-4 sm:p-5">

                    <h2 class="text-sm font-semibold text-navy mb-4">
                        Order Summary
                        <span class="text-navy/40 font-normal">· {{ $cartGroups->count() }} {{ Str::plural('shop', $cartGroups->count()) }}</span>
                    </h2>

                    <div class="space-y-2.5 text-sm">
                        <div class="flex items-center justify-between text-navy/60">
                            <span>Merchandise Subtotal</span>
                            <span id="summarySubtotal" class="text-navy font-medium">₱0</span>
                        </div>
                        <div class="flex items-center justify-between text-navy/60">
                            <span>Total Shipping Fee</span>
                            <span id="summaryShipping" class="text-navy font-medium">₱0</span>
                        </div>
                        <div id="summaryCodFeeRow" class="flex items-center justify-between text-navy/60">
                            <span>COD Handling Fee</span>
                            <span id="summaryCodFee" class="text-navy font-medium">₱{{ number_format($codFee) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <input
                            type="text"
                            name="voucher_code"
                            id="voucherInput"
                            placeholder="Enter voucher code"
                            class="flex-1 min-w-0 text-xs sm:text-sm rounded-lg border border-gray-border px-3 py-2.5 focus:outline-none focus:border-teal"
                        >
                        <button
                            type="button"
                            id="applyVoucherBtn"
                            class="shrink-0 px-3.5 rounded-lg border border-teal text-teal-dark text-xs sm:text-sm font-semibold hover:bg-teal-light transition"
                        >
                            Apply
                        </button>
                    </div>
                    <p id="voucherMessage" class="text-[11px] text-navy/40 mt-1.5 hidden"></p>

                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-border">
                        <span class="text-sm font-semibold text-navy">Total Payment</span>
                        <span id="summaryTotal" class="text-lg sm:text-xl font-bold text-teal-dark">₱0</span>
                    </div>

                    <button
                        type="submit"
                        id="placeOrderBtn"
                        class="w-full h-12 mt-5 rounded-xl bg-teal hover:bg-teal-dark text-white font-semibold text-sm transition"
                    >
                        Place Order
                    </button>

                    <p class="text-[11px] text-navy/40 mt-3 text-center leading-relaxed">
                        By placing your order, you agree to ShopHop's
                        <a href="#" class="text-teal-dark hover:underline">Terms of Service</a>.
                    </p>

                </div>

                <div class="grid grid-cols-3 gap-2 mt-4">
                    <div class="bg-white border border-gray-border rounded-xl px-2 py-3 flex flex-col items-center gap-1.5 text-center">
                        <x-lucide-shield-check class="w-4 h-4 text-teal-dark" />
                        <span class="text-[10px] text-navy/55 leading-tight">Buyer Protection</span>
                    </div>
                    <div class="bg-white border border-gray-border rounded-xl px-2 py-3 flex flex-col items-center gap-1.5 text-center">
                        <x-lucide-rotate-ccw class="w-4 h-4 text-teal-dark" />
                        <span class="text-[10px] text-navy/55 leading-tight">Easy Returns</span>
                    </div>
                    <div class="bg-white border border-gray-border rounded-xl px-2 py-3 flex flex-col items-center gap-1.5 text-center">
                        <x-lucide-lock class="w-4 h-4 text-teal-dark" />
                        <span class="text-[10px] text-navy/55 leading-tight">Secure Payment</span>
                    </div>
                </div>

            </div>

        </form>

    </div>
</section>


{{-- =========================================================
    FOOTER
========================================================= --}}
@include('partials.footer')

@endsection


{{-- =========================================================
    PAGE-SPECIFIC SCRIPTS
========================================================= --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const codFee = {{ (float) $codFee }};

    const shopGroups = document.querySelectorAll('.checkout-shop-group');

    const summarySubtotal = document.getElementById('summarySubtotal');
    const summaryShipping = document.getElementById('summaryShipping');
    const summaryCodFeeRow = document.getElementById('summaryCodFeeRow');
    const summaryCodFee = document.getElementById('summaryCodFee');
    const summaryTotal = document.getElementById('summaryTotal');
    const gcashAmountText = document.getElementById('gcashAmountText');

    const paymentOptions = document.querySelectorAll('[data-payment-option]');
    const gcashPanel = document.getElementById('gcashPanel');
    const gcashReference = document.getElementById('gcashReference');
    const gcashProof = document.getElementById('gcashProof');
    const gcashProofLabel = document.getElementById('gcashProofLabel');

    const voucherInput = document.getElementById('voucherInput');
    const applyVoucherBtn = document.getElementById('applyVoucherBtn');
    const voucherMessage = document.getElementById('voucherMessage');

    const checkoutForm = document.getElementById('checkoutForm');

    let voucherDiscount = 0;

    function formatPeso(amount) {
        return '₱' + amount.toLocaleString('en-PH', { maximumFractionDigits: 0 });
    }

    function currentPaymentMethod() {
        const checked = document.querySelector('input[name="payment_method"]:checked');
        return checked ? checked.value : 'cod';
    }

    function recalculate() {
        let merchandiseSubtotal = 0;
        let shippingTotal = 0;

        shopGroups.forEach(function (group, index) {
            let groupSubtotal = 0;

            group.querySelectorAll('.checkout-item').forEach(function (row) {
                const price = parseFloat(row.dataset.price || 0);
                const qty = parseInt(row.querySelector('[data-qty-input]')?.value, 10) || 1;
                groupSubtotal += price * qty;
            });

            const groupSubtotalEl = group.querySelector('[data-shop-subtotal="' + index + '"]');
            if (groupSubtotalEl) {
                groupSubtotalEl.textContent = formatPeso(groupSubtotal);
            }

            merchandiseSubtotal += groupSubtotal;
            shippingTotal += parseFloat(group.dataset.shippingFee || 0);
        });

        const isCod = currentPaymentMethod() === 'cod';
        summaryCodFeeRow.classList.toggle('hidden', !isCod);

        const total = merchandiseSubtotal + shippingTotal + (isCod ? codFee : 0) - voucherDiscount;

        summarySubtotal.textContent = formatPeso(merchandiseSubtotal);
        summaryShipping.textContent = formatPeso(shippingTotal);
        summaryCodFee.textContent = formatPeso(codFee);
        summaryTotal.textContent = formatPeso(Math.max(total, 0));

        if (gcashAmountText) {
            gcashAmountText.textContent = formatPeso(merchandiseSubtotal + shippingTotal - voucherDiscount);
        }
    }

    document.querySelectorAll('.checkout-item').forEach(function (row) {
        const decreaseButton = row.querySelector('[data-qty-decrease]');
        const increaseButton = row.querySelector('[data-qty-increase]');
        const qtyInput = row.querySelector('[data-qty-input]');

        if (!decreaseButton || !increaseButton || !qtyInput) {
            return;
        }

        const max = parseInt(qtyInput.max, 10) || 1;

        decreaseButton.addEventListener('click', function () {
            qtyInput.value = Math.max(1, (parseInt(qtyInput.value, 10) || 1) - 1);
            recalculate();
        });

        increaseButton.addEventListener('click', function () {
            qtyInput.value = Math.min(max, (parseInt(qtyInput.value, 10) || 1) + 1);
            recalculate();
        });

        qtyInput.addEventListener('change', recalculate);
    });

    paymentOptions.forEach(function (option) {
        const radio = option.querySelector('input[type="radio"]');

        option.addEventListener('click', function () {
            radio.checked = true;

            paymentOptions.forEach(function (opt) {
                opt.classList.remove('border-teal', 'bg-teal-light/40');
                opt.classList.add('border-gray-border');
            });

            option.classList.remove('border-gray-border');
            option.classList.add('border-teal', 'bg-teal-light/40');

            const isGcash = option.dataset.paymentOption === 'gcash';
            gcashPanel.classList.toggle('hidden', !isGcash);

            gcashReference.required = isGcash;
            gcashProof.required = isGcash;

            recalculate();
        });
    });

    if (gcashProof && gcashProofLabel) {
        gcashProof.addEventListener('change', function () {
            gcashProofLabel.textContent = gcashProof.files?.[0]?.name || 'Upload screenshot of your GCash payment';
            gcashProofLabel.classList.toggle('text-navy', !!gcashProof.files?.[0]);
        });
    }

    if (applyVoucherBtn && voucherInput) {
        applyVoucherBtn.addEventListener('click', function () {
            const code = voucherInput.value.trim().toUpperCase();

            voucherMessage.classList.remove('hidden');

            if (code === 'SHOPHOP50') {
                voucherDiscount = 50;
                voucherMessage.textContent = '₱50 voucher applied.';
                voucherMessage.classList.add('text-teal-dark');
                voucherMessage.classList.remove('text-red-500');
            } else {
                voucherDiscount = 0;
                voucherMessage.textContent = 'Invalid or expired voucher code.';
                voucherMessage.classList.add('text-red-500');
                voucherMessage.classList.remove('text-teal-dark');
            }

            recalculate();
        });
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (event) {
            if (currentPaymentMethod() === 'gcash') {
                if (!gcashReference.value.trim() || !gcashProof.files?.length) {
                    event.preventDefault();
                    alert('Please provide your GCash reference number and upload proof of payment before placing your order.');
                    return;
                }
            }

            // TEMPORARY: no backend route wired yet — remove this block
            // once /buyer/checkout/place-order has a real controller.
            event.preventDefault();
            alert('Order placed! (demo — connect this form to your checkout controller)');
        });
    }

    recalculate();

});
</script>
@endpush