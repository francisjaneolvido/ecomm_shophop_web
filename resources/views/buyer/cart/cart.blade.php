{{-- Path: resources/views/buyer/cart/cart.blade.php --}}

@extends('layouts.app')

@php
    // Data comes straight from CartController@index:
    //   $cartGroups   -> session cart grouped by shop (line_key based)
    //   $vouchers     -> static voucher list
    //   $cartItemCount
    //   $buyNowLineKey -> line_key of the item that came from "Buy Now", if any
@endphp

@section('title', 'My Cart - ShopHop')
@section('hideChrome', true)

@section('content')

@include('buyer.partials.navbar-buyer')


{{-- =========================================================
    BREADCRUMB
========================================================= --}}
<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <nav class="flex items-center gap-1.5 text-[10px] sm:text-[10.5px] text-navy/45">
            <a href="{{ route('buyer.dashboard') }}" class="hover:text-teal-dark transition">Home</a>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0 text-navy/25" />
            <span class="text-navy font-medium">Cart</span>
        </nav>
    </div>
</div>


{{-- =========================================================
    CART
========================================================= --}}
<section class="bg-gray-bg/75 min-h-[70vh] py-5 sm:py-6">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page heading --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
            <div>
                <div class="inline-flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-widest text-teal-dark mb-1.5">
                    <x-lucide-shopping-bag class="w-3 h-3" />
                    My Shopping
                </div>

                <p role="heading" aria-level="1"
                   class="text-[24px] sm:text-[28px] font-bold leading-none tracking-tight text-navy">
                    My Cart
                </p>

                <p class="text-[10.5px] sm:text-[11px] text-navy/45 mt-1.5">
                    Review your items, apply a voucher, and checkout when you're ready.
                </p>
            </div>

            <div class="text-[10px] text-navy/40">
                <span id="cartItemCountTop" class="font-semibold text-navy">{{ $cartItemCount }}</span>
                {{ \Illuminate\Support\Str::plural('item', $cartItemCount) }} in cart
            </div>
        </div>


        <div class="grid lg:grid-cols-[minmax(0,1fr)_320px] gap-4 lg:gap-5 items-start">

            {{-- =================================================
                LEFT — CART ITEMS
            ================================================== --}}
            <div class="space-y-3 min-w-0" id="cartGroupsWrapper">

                {{-- Selection toolbar --}}
                <div class="bg-white border border-gray-border rounded-xl px-3.5 sm:px-4 py-2.5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="selectAllGlobal"
                                   class="w-3.5 h-3.5 rounded accent-teal">
                            <span class="text-[11px] font-semibold text-navy">Select all items</span>
                        </label>

                        <button type="button" id="deleteSelectedBtn" disabled
                                class="inline-flex items-center gap-1.5 text-[10.5px] font-semibold
                                       text-navy/25 cursor-not-allowed transition
                                       enabled:text-red-500 enabled:hover:text-red-600 enabled:cursor-pointer">
                            <x-lucide-trash-2 class="w-3.5 h-3.5" />
                            <span id="deleteSelectedLabel">Delete</span>
                        </button>
                    </div>
                </div>


                {{-- Empty cart --}}
                <div id="emptyCartState"
                     class="{{ count($cartGroups) === 0 ? 'flex' : 'hidden' }} bg-white border border-gray-border rounded-2xl py-12 px-4 flex-col items-center text-center shadow-sm">

                    <div class="w-12 h-12 rounded-xl bg-teal-light text-teal-dark flex items-center justify-center mb-3">
                        <x-lucide-shopping-cart class="w-5 h-5" />
                    </div>

                    <p class="text-[13px] font-bold text-navy">Your cart is empty</p>

                    <p class="text-[10.5px] text-navy/45 mt-1 max-w-xs">
                        Add something you love and it will appear here.
                    </p>

                    <a href="{{ route('buyer.dashboard') }}"
                       class="mt-4 inline-flex items-center gap-1.5 h-9 px-4 rounded-lg
                              bg-teal hover:bg-teal-dark text-white text-[10.5px] font-semibold transition">
                        Continue Shopping
                        <x-lucide-arrow-right class="w-3.5 h-3.5" />
                    </a>
                </div>


                @foreach ($cartGroups as $groupIndex => $group)
                    <section class="cart-shop-group bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm"
                             data-shop="{{ $groupIndex }}">

                        {{-- Shop header --}}
                        <div class="flex items-center justify-between gap-3 px-3.5 sm:px-4 py-2.5 border-b border-gray-border/80">
                            <div class="flex items-center gap-2 min-w-0">
                                <input type="checkbox"
                                       class="shop-select-all w-3.5 h-3.5 rounded accent-teal shrink-0"
                                       data-shop="{{ $groupIndex }}">

                                <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0">
                                    <x-lucide-store class="w-3.5 h-3.5" />
                                </span>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <p class="text-[11px] sm:text-[11.5px] font-bold text-navy truncate">
                                            {{ $group['shop']['name'] }}
                                        </p>

                                        @if (! empty($group['shop']['preferred']))
                                            <span class="hidden sm:inline-flex text-[8px] font-bold px-1.5 py-0.5 rounded-full bg-teal text-white">
                                                Preferred
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-[9px] text-navy/35 mt-0.5">
                                        {{ $group['shop']['response_rate'] }} response rate
                                    </p>
                                </div>
                            </div>

                            <a href="#"
                               class="text-[9.5px] font-semibold text-teal-dark hover:text-navy transition shrink-0">
                                View shop
                            </a>
                        </div>


                        <div class="divide-y divide-gray-border/80">

                            @foreach ($group['items'] as $item)
                                @php
                                    $isPreselected = $buyNowLineKey
                                        ? ((string) $item['line_key'] === (string) $buyNowLineKey)
                                        : true;

                                    $discountPercent = $item['original_price']
                                        ? max(0, (int) round((1 - ($item['price'] / $item['original_price'])) * 100))
                                        : 0;
                                @endphp

                                <article class="cart-item px-3.5 sm:px-4 py-3.5"
                                         data-line-key="{{ $item['line_key'] }}"
                                         data-shop="{{ $groupIndex }}"
                                         data-price="{{ $item['price'] }}"
                                         data-original-price="{{ $item['original_price'] ?? $item['price'] }}">

                                    <div class="grid grid-cols-[auto_64px_minmax(0,1fr)] sm:grid-cols-[auto_72px_minmax(0,1fr)_auto] gap-2.5 sm:gap-3 items-start">

                                        <input type="checkbox"
                                               class="item-checkbox w-3.5 h-3.5 rounded accent-teal mt-1 shrink-0"
                                               data-line-key="{{ $item['line_key'] }}"
                                               data-shop="{{ $groupIndex }}"
                                               {{ $isPreselected ? 'checked' : '' }}>

                                        <a href="#"
                                           class="w-16 h-16 sm:w-18 sm:h-18 rounded-xl overflow-hidden
                                                  bg-gray-bg border border-gray-border/80 shrink-0">
                                            <img src="{{ asset($item['image']) }}"
                                                 alt="{{ $item['name'] }}"
                                                 class="w-full h-full object-cover">
                                        </a>

                                        <div class="min-w-0">
                                            <a href="#"
                                               class="block text-[11px] sm:text-[12px] leading-[1.4] font-semibold text-navy
                                                      hover:text-teal-dark transition line-clamp-2">
                                                {{ $item['name'] }}
                                            </a>

                                            <p class="text-[9.5px] text-navy/40 mt-1">
                                                {{ $item['variant'] }}
                                            </p>

                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-2">
                                                <span class="text-[12px] sm:text-[13px] font-bold text-teal-dark">
                                                    ₱{{ number_format($item['price']) }}
                                                </span>

                                                @if ($item['original_price'])
                                                    <span class="text-[9px] text-navy/30 line-through">
                                                        ₱{{ number_format($item['original_price']) }}
                                                    </span>

                                                    <span class="text-[8px] font-bold text-teal-dark bg-teal-light px-1.5 py-0.5 rounded">
                                                        -{{ $discountPercent }}%
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="sm:hidden mt-2.5 flex items-center justify-between gap-3">
                                                <span class="text-[9px] text-navy/35">
                                                    {{ $item['stock'] }} available
                                                </span>

                                                <div class="flex items-center border border-gray-border rounded-lg overflow-hidden bg-white">
                                                    <button type="button" data-qty-decrease
                                                            class="w-7 h-7 flex items-center justify-center hover:bg-teal-light transition">
                                                        <x-lucide-minus class="w-3 h-3" />
                                                    </button>

                                                    <input type="number" data-qty-input
                                                           value="{{ $item['qty'] }}"
                                                           min="1"
                                                           max="{{ $item['stock'] }}"
                                                           class="w-9 h-7 text-center border-x border-gray-border
                                                                  text-[10px] font-semibold focus:outline-none">

                                                    <button type="button" data-qty-increase
                                                            class="w-7 h-7 flex items-center justify-center hover:bg-teal-light transition">
                                                        <x-lucide-plus class="w-3 h-3" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>


                                        {{-- Desktop qty / remove --}}
                                        <div class="hidden sm:flex flex-col items-end gap-2.5 shrink-0">

                                            <button type="button"
                                                    class="item-delete-btn w-7 h-7 rounded-lg
                                                           text-navy/30 hover:text-red-500 hover:bg-red-50
                                                           flex items-center justify-center transition"
                                                    data-line-key="{{ $item['line_key'] }}"
                                                    aria-label="Remove {{ $item['name'] }}">
                                                <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                            </button>

                                            <div class="flex items-center border border-gray-border rounded-lg overflow-hidden bg-white">
                                                <button type="button" data-qty-decrease
                                                        class="w-7 h-7 flex items-center justify-center hover:bg-teal-light transition">
                                                    <x-lucide-minus class="w-3 h-3" />
                                                </button>

                                                <input type="number" data-qty-input
                                                       value="{{ $item['qty'] }}"
                                                       min="1"
                                                       max="{{ $item['stock'] }}"
                                                       class="w-9 h-7 text-center border-x border-gray-border
                                                              text-[10px] font-semibold focus:outline-none">

                                                <button type="button" data-qty-increase
                                                        class="w-7 h-7 flex items-center justify-center hover:bg-teal-light transition">
                                                    <x-lucide-plus class="w-3 h-3" />
                                                </button>
                                            </div>

                                            <span class="text-[8.5px] text-navy/30">
                                                {{ $item['stock'] }} available
                                            </span>
                                        </div>

                                    </div>

                                    {{-- Mobile remove --}}
                                    <div class="sm:hidden flex justify-end mt-2">
                                        <button type="button"
                                                class="item-delete-btn inline-flex items-center gap-1 text-[9px] text-navy/35
                                                       hover:text-red-500 transition"
                                                data-line-key="{{ $item['line_key'] }}">
                                            <x-lucide-trash-2 class="w-3 h-3" />
                                            Remove
                                        </button>
                                    </div>

                                </article>
                            @endforeach

                        </div>

                    </section>
                @endforeach
            </div>


            {{-- =================================================
                RIGHT — VOUCHER + ORDER SUMMARY
                (voucher list stays hardcoded, comes from
                CartController@vouchers())
            ================================================== --}}
            <aside class="space-y-3 lg:sticky lg:top-4">

                {{-- Vouchers --}}
                <section class="bg-white border border-gray-border rounded-2xl overflow-hidden shadow-sm">

                    <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-gray-border/80">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                                <x-lucide-ticket-percent class="w-3.5 h-3.5" />
                            </span>

                            <div>
                                <p class="text-[11px] font-bold text-navy">ShopHop Voucher</p>
                                <p class="text-[8.5px] text-navy/35 mt-0.5">Use one voucher per checkout</p>
                            </div>
                        </div>

                        <span id="voucherStatus"
                              class="hidden text-[8px] font-bold bg-teal-light text-teal-dark px-1.5 py-1 rounded-md">
                            Applied
                        </span>
                    </div>

                    <div class="p-3 space-y-2" id="voucherList">
                        @foreach ($vouchers as $voucher)
                            <div class="voucher-card border border-gray-border rounded-xl p-2.5 transition"
                                 data-voucher-code="{{ $voucher['code'] }}"
                                 data-voucher-title="{{ $voucher['title'] }}"
                                 data-voucher-type="{{ $voucher['type'] }}"
                                 data-voucher-value="{{ $voucher['value'] }}"
                                 data-voucher-min="{{ $voucher['min_spend'] }}"
                                 data-voucher-max="{{ $voucher['max_discount'] ?? '' }}">

                                <div class="flex gap-2.5">
                                    <div class="w-9 h-9 rounded-lg bg-[#EAF9F5] text-teal-dark flex items-center justify-center shrink-0">
                                        <x-lucide-ticket class="w-4 h-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="text-[10.5px] font-bold text-navy">{{ $voucher['title'] }}</p>
                                                <p class="text-[8.5px] font-mono font-semibold text-teal-dark mt-0.5">
                                                    {{ $voucher['code'] }}
                                                </p>
                                            </div>

                                            <button type="button"
                                                    class="voucher-apply-btn shrink-0 h-7 px-2.5 rounded-lg
                                                           bg-teal-light text-teal-dark hover:bg-teal hover:text-white
                                                           text-[9px] font-bold transition"
                                                    data-voucher-code="{{ $voucher['code'] }}">
                                                Apply
                                            </button>
                                        </div>

                                        <p class="text-[8.5px] leading-relaxed text-navy/40 mt-1.5">
                                            {{ $voucher['description'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="removeVoucherBtn"
                            class="hidden w-full border-t border-gray-border/80 py-2.5
                                   text-[9px] font-semibold text-navy/40 hover:text-red-500 transition">
                        Remove applied voucher
                    </button>

                </section>


                {{-- Summary --}}
                <section class="bg-white border border-gray-border rounded-2xl p-4 shadow-sm">

                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-7 h-7 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                            <x-lucide-receipt-text class="w-3.5 h-3.5" />
                        </span>

                        <div>
                            <p class="text-[11px] font-bold text-navy">Order Summary</p>
                            <p class="text-[8.5px] text-navy/35 mt-0.5">Selected items only</p>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-[10.5px]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-navy/50">Selected items</span>
                            <span id="selectedCount" class="font-semibold text-navy">0</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-navy/50">Merchandise subtotal</span>
                            <span id="selectedSubtotal" class="font-semibold text-navy">₱0</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-navy/50">Product savings</span>
                            <span id="productSavings" class="font-semibold text-teal-dark">-₱0</span>
                        </div>

                        <div id="voucherDiscountRow"
                             class="hidden items-center justify-between gap-3">
                            <span class="text-navy/50">
                                Voucher
                                <span id="appliedVoucherCode" class="text-[8px] font-mono text-teal-dark"></span>
                            </span>

                            <span id="voucherDiscount" class="font-semibold text-teal-dark">-₱0</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-border mt-4 pt-4">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <p class="text-[9px] text-navy/40">Total</p>
                                <p class="text-[8px] text-navy/30 mt-0.5">Shipping calculated at checkout</p>
                            </div>

                            <span id="selectedTotal"
                                  class="text-[19px] leading-none font-bold text-teal-dark">
                                ₱0
                            </span>
                        </div>
                    </div>

                    <button type="button" id="proceedToCheckout" disabled
                            class="w-full h-10 mt-4 rounded-xl
                                   bg-teal hover:bg-teal-dark
                                   disabled:bg-gray-border disabled:text-white/80 disabled:cursor-not-allowed
                                   text-white text-[11px] font-semibold
                                   flex items-center justify-center gap-1.5
                                   shadow-sm hover:shadow-md transition">
                        Proceed to Checkout
                        <x-lucide-arrow-right class="w-3.5 h-3.5" />
                    </button>

                    <div class="flex items-center justify-center gap-1.5 mt-3 text-[8.5px] text-navy/35">
                        <x-lucide-shield-check class="w-3 h-3 text-teal-dark" />
                        Secure checkout · Buyer protection
                    </div>

                </section>

            </aside>
        </div>


        {{-- Mobile sticky summary --}}
        <div id="mobileCheckoutBar"
             class="lg:hidden sticky bottom-2 z-30 mt-4 bg-white/95 backdrop-blur
                    border border-gray-border rounded-2xl shadow-lg shadow-navy/10
                    px-3 py-2.5">

            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[8.5px] text-navy/40">
                        <span id="mobileSelectedCount">0</span> selected
                    </p>

                    <p id="mobileSelectedTotal"
                       class="text-[15px] font-bold text-teal-dark leading-tight mt-0.5">
                        ₱0
                    </p>
                </div>

                <button type="button" id="mobileProceedBtn" disabled
                        class="h-9 px-4 rounded-xl
                               bg-teal hover:bg-teal-dark
                               disabled:bg-gray-border disabled:cursor-not-allowed
                               text-white text-[10.5px] font-semibold transition">
                    Checkout
                </button>
            </div>
        </div>

    </div>
</section>


{{-- Toast --}}
<div id="cartToast"
     class="fixed left-1/2 bottom-5 z-50 -translate-x-1/2 translate-y-6 opacity-0 pointer-events-none
            bg-navy text-white text-[10px] font-medium
            px-3.5 py-2.5 rounded-xl shadow-xl
            transition-all duration-300">
</div>


@include('partials.footer')

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const cartGroupsWrapper = document.getElementById('cartGroupsWrapper');
    const selectAllGlobal = document.getElementById('selectAllGlobal');

    const selectedCountEl = document.getElementById('selectedCount');
    const selectedSubtotalEl = document.getElementById('selectedSubtotal');
    const selectedTotalEl = document.getElementById('selectedTotal');
    const productSavingsEl = document.getElementById('productSavings');

    const proceedBtn = document.getElementById('proceedToCheckout');
    const mobileProceedBtn = document.getElementById('mobileProceedBtn');
    const mobileSelectedCount = document.getElementById('mobileSelectedCount');
    const mobileSelectedTotal = document.getElementById('mobileSelectedTotal');

    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const deleteSelectedLabel = document.getElementById('deleteSelectedLabel');
    const emptyCartState = document.getElementById('emptyCartState');
    const cartItemCountTop = document.getElementById('cartItemCountTop');

    const voucherStatus = document.getElementById('voucherStatus');
    const voucherDiscountRow = document.getElementById('voucherDiscountRow');
    const voucherDiscountEl = document.getElementById('voucherDiscount');
    const appliedVoucherCode = document.getElementById('appliedVoucherCode');
    const removeVoucherBtn = document.getElementById('removeVoucherBtn');

    const cartToast = document.getElementById('cartToast');

    // NOTE: assumes <meta name="csrf-token" content="{{ csrf_token() }}">
    // is present in the shared layout head.
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const CART_UPDATE_URL_TEMPLATE = @json(route('buyer.cart.update', ['lineKey' => '__LINE_KEY__']));
    const CART_REMOVE_URL_TEMPLATE = @json(route('buyer.cart.remove', ['lineKey' => '__LINE_KEY__']));
    const CART_REMOVE_MANY_URL = @json(route('buyer.cart.removeMany'));

    let activeVoucher = null;
    let currentSubtotal = 0;
    let currentVoucherDiscount = 0;


    function money(value) {
        return '₱' + Math.max(0, Math.round(value)).toLocaleString('en-PH');
    }


    function showToast(message) {
        if (!cartToast) return;

        cartToast.textContent = message;
        cartToast.classList.remove('translate-y-6', 'opacity-0');
        cartToast.classList.add('translate-y-0', 'opacity-100');

        clearTimeout(showToast.timer);

        showToast.timer = setTimeout(function () {
            cartToast.classList.remove('translate-y-0', 'opacity-100');
            cartToast.classList.add('translate-y-6', 'opacity-0');
        }, 2200);
    }


    async function apiRequest(url, options = {}) {
        try {
            const response = await fetch(url, {
                method: options.method || 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: options.body ? JSON.stringify(options.body) : undefined,
            });

            if (!response.ok) {
                throw new Error('Request failed: ' + response.status);
            }

            return await response.json();
        } catch (error) {
            console.error(error);
            showToast('Something went wrong. Please try again.');
            return null;
        }
    }


    function currentItemCheckboxes() {
        return document.querySelectorAll('.item-checkbox');
    }


    function currentShopSelectAlls() {
        return document.querySelectorAll('.shop-select-all');
    }


    function getRowQty(row) {
        const input = row?.querySelector('[data-qty-input]');
        return Math.max(1, parseInt(input?.value, 10) || 1);
    }


    function setRowQty(row, nextValue) {
        const inputs = row.querySelectorAll('[data-qty-input]');

        inputs.forEach(function (input) {
            const max = parseInt(input.max, 10) || 1;
            input.value = Math.max(1, Math.min(max, nextValue));
        });
    }


    function calculateVoucherDiscount(subtotal) {
        if (!activeVoucher || subtotal <= 0) return 0;

        const minimum = Number(activeVoucher.min || 0);

        if (subtotal < minimum) {
            return 0;
        }

        if (activeVoucher.type === 'percent') {
            const raw = subtotal * (Number(activeVoucher.value || 0) / 100);
            const maxDiscount = Number(activeVoucher.max || 0);

            return maxDiscount > 0
                ? Math.min(raw, maxDiscount)
                : raw;
        }

        return Number(activeVoucher.value || 0);
    }


    function syncVoucherUI() {
        document.querySelectorAll('.voucher-card').forEach(function (card) {
            const selected = activeVoucher && card.dataset.voucherCode === activeVoucher.code;

            card.classList.toggle('border-teal', !!selected);
            card.classList.toggle('bg-teal-light/20', !!selected);
            card.classList.toggle('border-gray-border', !selected);

            const button = card.querySelector('.voucher-apply-btn');

            if (button) {
                button.textContent = selected ? 'Applied' : 'Apply';

                button.classList.toggle('bg-teal', !!selected);
                button.classList.toggle('text-white', !!selected);
                button.classList.toggle('bg-teal-light', !selected);
                button.classList.toggle('text-teal-dark', !selected);
            }
        });

        const hasActiveVoucher = !!activeVoucher;

        voucherStatus?.classList.toggle('hidden', !hasActiveVoucher);
        removeVoucherBtn?.classList.toggle('hidden', !hasActiveVoucher);

        voucherDiscountRow?.classList.toggle('hidden', currentVoucherDiscount <= 0);
        voucherDiscountRow?.classList.toggle('flex', currentVoucherDiscount > 0);

        if (appliedVoucherCode) {
            appliedVoucherCode.textContent = hasActiveVoucher
                ? '(' + activeVoucher.code + ')'
                : '';
        }

        if (voucherDiscountEl) {
            voucherDiscountEl.textContent = '-' + money(currentVoucherDiscount);
        }
    }


    function bindVoucherButtons() {
        document.querySelectorAll('.voucher-apply-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const card = button.closest('.voucher-card');
                if (!card) return;

                const minimum = Number(card.dataset.voucherMin || 0);

                if (currentSubtotal < minimum) {
                    showToast('Spend ' + money(minimum) + ' to use ' + card.dataset.voucherCode + '.');
                    return;
                }

                activeVoucher = {
                    code: card.dataset.voucherCode,
                    title: card.dataset.voucherTitle,
                    type: card.dataset.voucherType,
                    value: Number(card.dataset.voucherValue || 0),
                    min: minimum,
                    max: Number(card.dataset.voucherMax || 0),
                };

                recalc();
                showToast(activeVoucher.code + ' applied.');
            });
        });

        removeVoucherBtn?.addEventListener('click', function () {
            activeVoucher = null;
            recalc();
            showToast('Voucher removed.');
        });
    }


    function bindItemCheckbox(cb) {
        cb.addEventListener('change', recalc);
    }


    function bindShopSelectAll(shopAll) {
        shopAll.addEventListener('change', function () {
            const shopIndex = shopAll.dataset.shop;

            document
                .querySelectorAll('.item-checkbox[data-shop="' + shopIndex + '"]')
                .forEach(function (cb) {
                    cb.checked = shopAll.checked;
                });

            recalc();
        });
    }


    function bindQtyControls(row) {
        const decreaseButtons = row.querySelectorAll('[data-qty-decrease]');
        const increaseButtons = row.querySelectorAll('[data-qty-increase]');
        const qtyInputs = row.querySelectorAll('[data-qty-input]');
        const lineKey = row.dataset.lineKey;

        function persistQty() {
            const qty = getRowQty(row);
            const url = CART_UPDATE_URL_TEMPLATE.replace('__LINE_KEY__', encodeURIComponent(lineKey));

            apiRequest(url, {
                method: 'PATCH',
                body: { qty: qty },
            });

            recalc();
        }

        decreaseButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setRowQty(row, getRowQty(row) - 1);
                persistQty();
            });
        });

        increaseButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setRowQty(row, getRowQty(row) + 1);
                persistQty();
            });
        });

        qtyInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                setRowQty(row, parseInt(input.value, 10) || 1);
                persistQty();
            });
        });
    }


    function bindItemDelete(btn) {
        btn.addEventListener('click', function () {
            const lineKey = btn.dataset.lineKey;

            if (!confirm('Remove this item from your cart?')) return;

            removeItem(lineKey);
            showToast('Item removed from cart.');
        });
    }


    function removeItem(lineKey) {
        const row = document.querySelector('.cart-item[data-line-key="' + lineKey + '"]');
        const url = CART_REMOVE_URL_TEMPLATE.replace('__LINE_KEY__', encodeURIComponent(lineKey));

        apiRequest(url, {
            method: 'DELETE',
        });

        if (!row) return;

        const group = row.closest('.cart-shop-group');

        row.remove();

        if (group && group.querySelectorAll('.cart-item').length === 0) {
            group.remove();
        }

        recalc();
    }


    function deleteSelected() {
        const selectedKeys = Array.from(currentItemCheckboxes())
            .filter(function (cb) { return cb.checked; })
            .map(function (cb) { return cb.dataset.lineKey; });

        if (selectedKeys.length === 0) return;

        const label = selectedKeys.length === 1
            ? 'this item'
            : 'these ' + selectedKeys.length + ' items';

        if (!confirm('Remove ' + label + ' from your cart?')) return;

        apiRequest(CART_REMOVE_MANY_URL, {
            method: 'POST',
            body: { keys: selectedKeys },
        });

        selectedKeys.forEach(function (lineKey) {
            const row = document.querySelector('.cart-item[data-line-key="' + lineKey + '"]');
            if (!row) return;

            const group = row.closest('.cart-shop-group');
            row.remove();

            if (group && group.querySelectorAll('.cart-item').length === 0) {
                group.remove();
            }
        });

        recalc();
        showToast('Selected items removed.');
    }


    function recalc() {
        const itemCheckboxes = currentItemCheckboxes();
        const shopSelectAlls = currentShopSelectAlls();

        let selectedCount = 0;
        let subtotal = 0;
        let savings = 0;

        itemCheckboxes.forEach(function (cb) {
            if (!cb.checked) return;

            selectedCount++;

            const row = cb.closest('.cart-item');
            const qty = getRowQty(row);
            const price = Number(row?.dataset.price || 0);
            const originalPrice = Number(row?.dataset.originalPrice || price);

            subtotal += price * qty;
            savings += Math.max(0, originalPrice - price) * qty;
        });

        currentSubtotal = subtotal;
        currentVoucherDiscount = calculateVoucherDiscount(subtotal);

        // If subtotal drops below minimum, keep voucher selected but discount becomes 0.
        const total = Math.max(0, subtotal - currentVoucherDiscount);

        selectedCountEl.textContent = selectedCount;
        selectedSubtotalEl.textContent = money(subtotal);
        selectedTotalEl.textContent = money(total);
        productSavingsEl.textContent = '-' + money(savings);

        mobileSelectedCount.textContent = selectedCount;
        mobileSelectedTotal.textContent = money(total);

        proceedBtn.disabled = selectedCount === 0;
        mobileProceedBtn.disabled = selectedCount === 0;

        deleteSelectedBtn.disabled = selectedCount === 0;
        deleteSelectedLabel.textContent = selectedCount > 0
            ? 'Delete (' + selectedCount + ')'
            : 'Delete';

        shopSelectAlls.forEach(function (shopAll) {
            const shopIndex = shopAll.dataset.shop;

            const shopItems = document.querySelectorAll(
                '.item-checkbox[data-shop="' + shopIndex + '"]'
            );

            shopAll.checked =
                shopItems.length > 0 &&
                Array.from(shopItems).every(function (cb) { return cb.checked; });
        });

        selectAllGlobal.checked =
            itemCheckboxes.length > 0 &&
            Array.from(itemCheckboxes).every(function (cb) { return cb.checked; });

        const currentItemCount = document.querySelectorAll('.cart-item').length;

        if (cartItemCountTop) {
            cartItemCountTop.textContent = currentItemCount;
        }

        const hasGroups =
            cartGroupsWrapper.querySelectorAll('.cart-shop-group').length > 0;

        emptyCartState.classList.toggle('hidden', hasGroups);

        syncVoucherUI();
    }


    function proceedToCheckout() {
        const selected = Array.from(currentItemCheckboxes())
            .filter(function (cb) { return cb.checked; });

        if (selected.length === 0) return;

        const params = new URLSearchParams();

        selected.forEach(function (cb) {
            const row = cb.closest('.cart-item');
            const lineKey = cb.dataset.lineKey;

            params.append('items[]', lineKey);
            params.append('qty[' + lineKey + ']', getRowQty(row));
        });

        if (activeVoucher && currentVoucherDiscount > 0) {
            params.set('voucher', activeVoucher.code);
        }

        window.location.href =
            '{{ url('/buyer/cart/checkout') }}?' + params.toString();
    }


    currentItemCheckboxes().forEach(bindItemCheckbox);
    currentShopSelectAlls().forEach(bindShopSelectAll);

    document.querySelectorAll('.cart-item').forEach(function (row) {
        bindQtyControls(row);
    });

    document.querySelectorAll('.item-delete-btn').forEach(bindItemDelete);

    selectAllGlobal.addEventListener('change', function () {
        currentItemCheckboxes().forEach(function (cb) {
            cb.checked = selectAllGlobal.checked;
        });

        recalc();
    });

    deleteSelectedBtn.addEventListener('click', deleteSelected);
    proceedBtn.addEventListener('click', proceedToCheckout);
    mobileProceedBtn.addEventListener('click', proceedToCheckout);

    bindVoucherButtons();
    recalc();

});
</script>
@endpush