{{-- Path: resources/views/buyer/cart/cart.blade.php --}}
{{--
    REFERENCE / STARTER VERSION.
    Wala pang uploaded cart.blade.php kaya ito ay bagong file — i-merge
    mo na lang ang mga parts na kailangan (navbar include, footer,
    styling) sa existing cart page mo kung meron na.

    KEY BEHAVIORS:
    1. May checkbox bawat item + "Select All" per shop.
    2. Kung galing sa "Buy Now" (may ?buy_now={id} sa URL), yun lang
       ang naka-check by default — hindi yung buong cart.
    3. "Proceed to Checkout" button ay disabled kapag walang naka-check,
       at kapag pinindot, ipinapasa ang mga selected item IDs bilang
       query array papunta sa /buyer/cart/checkout
       (hal. /buyer/cart/checkout?items[]=1&items[]=3).
    4. May "Delete Selected" bulk action (gumagana kasabay ng Select
       All / per-shop select all) + per-item delete (trash icon).
       Sa frontend pa lang ito ngayon — dagdagan ng AJAX call papunta
       sa backend delete route (hal. DELETE /buyer/cart/{id}) kapag
       na-wire na sa totoong cart data.
--}}

@extends('layouts.app')

{{-- TEMPORARY PREVIEW DATA — palitan ng galing session/DB cart --}}
@php
    $buyNowProductId = request()->query('buy_now');
    $buyNowQty = (int) request()->query('qty', 1);

    $cartGroups = collect([
        [
            'shop' => ['id' => 1, 'name' => 'ShopHop Tech Store', 'response_rate' => '96%'],
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

    // Kung galing Buy Now, i-apply yung qty sa item na yun para tama
    // agad kapag pumasok sa checkout.
    if ($buyNowProductId) {
        $cartGroups = $cartGroups->map(function ($group) use ($buyNowProductId, $buyNowQty) {
            $group['items'] = collect($group['items'])->map(function ($item) use ($buyNowProductId, $buyNowQty) {
                if ((string) $item['id'] === (string) $buyNowProductId) {
                    $item['qty'] = max(1, $buyNowQty);
                }
                return $item;
            })->all();
            return $group;
        });
    }
@endphp

@section('title', 'My Cart - ShopHop')

@section('hideChrome', true)

@section('content')

@include('buyer.partials.navbar-buyer')

<div class="bg-white border-b border-gray-border/70">
    <div class="max-w-260 mx-auto px-4 sm:px-6 lg:px-8 py-2.5">
        <nav class="flex items-center gap-1.5 text-xs text-navy/45">
            <a href="{{ route('buyer.dashboard') }}" class="hover:text-teal-dark transition">Home</a>
            <x-lucide-chevron-right class="w-3 h-3 shrink-0" />
            <span class="text-navy font-medium">Cart</span>
        </nav>
    </div>
</div>

<section class="bg-gray-bg/70 py-4 sm:py-6">
    <div class="max-w-260 mx-auto px-4 sm:px-6 lg:px-8">

        <h1 class="text-navy text-lg sm:text-xl font-bold mb-3 sm:mb-4">My Cart</h1>

        <div class="grid lg:grid-cols-[1fr_300px] gap-3 sm:gap-4 items-start">

            {{-- LEFT: CART ITEMS --}}
            <div class="space-y-3 min-w-0" id="cartGroupsWrapper">

                {{-- TOOLBAR: Select All + Bulk Delete --}}
                <div class="bg-white border border-gray-border rounded-xl px-3.5 py-2.5 flex items-center justify-between gap-3">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" id="selectAllGlobal" class="w-4 h-4 accent-teal">
                        <span class="text-[13px] font-semibold text-navy">Select All</span>
                    </label>

                    <button type="button" id="deleteSelectedBtn" disabled
                            class="flex items-center gap-1.5 text-xs font-semibold text-navy/30 cursor-not-allowed transition
                                   enabled:text-red-500 enabled:hover:text-red-600 enabled:cursor-pointer">
                        <x-lucide-trash-2 class="w-3.5 h-3.5" />
                        <span id="deleteSelectedLabel">Delete</span>
                    </button>
                </div>

                {{-- EMPTY STATE (hidden unless everything gets deleted) --}}
                <div id="emptyCartState" class="hidden bg-white border border-gray-border rounded-xl py-12 px-4 flex flex-col items-center text-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gray-bg flex items-center justify-center">
                        <x-lucide-shopping-cart class="w-5 h-5 text-navy/30" />
                    </div>
                    <p class="text-sm font-medium text-navy">Your cart is empty</p>
                    <p class="text-xs text-navy/45 -mt-1.5">Items you remove or check out will show up gone from here.</p>
                    <a href="{{ route('buyer.dashboard') }}"
                       class="mt-1 inline-flex h-9 items-center px-4 rounded-lg bg-teal hover:bg-teal-dark text-white text-xs font-semibold transition">
                        Continue Shopping
                    </a>
                </div>

                @foreach ($cartGroups as $groupIndex => $group)
                    <div class="cart-shop-group bg-white border border-gray-border rounded-xl overflow-hidden" data-shop="{{ $groupIndex }}">

                        <div class="flex items-center gap-2.5 px-3.5 sm:px-4 py-2.5 border-b border-gray-border">
                            <input type="checkbox" class="shop-select-all w-4 h-4 accent-teal" data-shop="{{ $groupIndex }}">
                            <x-lucide-store class="w-3.5 h-3.5 text-navy/55" />
                            <span class="font-semibold text-navy text-[13px]">{{ $group['shop']['name'] }}</span>
                        </div>

                        <div class="divide-y divide-gray-border">
                            @foreach ($group['items'] as $item)
                                @php
                                    $isPreselected = $buyNowProductId
                                        ? ((string) $item['id'] === (string) $buyNowProductId)
                                        : true; // walang buy_now param = normal cart view, default check all
                                @endphp
                                <div class="cart-item flex items-start gap-2.5 sm:gap-3 px-3.5 sm:px-4 py-3"
                                     data-item-id="{{ $item['id'] }}"
                                     data-shop="{{ $groupIndex }}">

                                    <input type="checkbox"
                                           class="item-checkbox w-4 h-4 accent-teal mt-1 shrink-0"
                                           data-item-id="{{ $item['id'] }}"
                                           data-shop="{{ $groupIndex }}"
                                           {{ $isPreselected ? 'checked' : '' }}>

                                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden bg-gray-bg shrink-0">
                                        <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] sm:text-sm font-medium text-navy line-clamp-2">{{ $item['name'] }}</p>
                                        <p class="text-[11px] text-navy/45 mt-0.5">{{ $item['variant'] }}</p>
                                        <p class="text-sm font-semibold text-teal-dark mt-1.5">₱{{ number_format($item['price']) }}</p>
                                    </div>

                                    <div class="flex flex-col items-end gap-2 shrink-0">
                                        <button type="button" class="item-delete-btn text-navy/35 hover:text-red-500 transition p-0.5"
                                                data-item-id="{{ $item['id'] }}" aria-label="Remove item">
                                            <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                        </button>

                                        <div class="flex items-center border border-gray-border rounded-lg overflow-hidden">
                                            <button type="button" data-qty-decrease class="w-7 h-7 flex items-center justify-center hover:bg-teal-light transition">
                                                <x-lucide-minus class="w-3 h-3" />
                                            </button>
                                            <input type="number" data-qty-input value="{{ $item['qty'] }}" min="1" max="{{ $item['stock'] }}"
                                                   class="w-9 h-7 text-center border-x border-gray-border text-xs font-semibold focus:outline-none">
                                            <button type="button" data-qty-increase class="w-7 h-7 flex items-center justify-center hover:bg-teal-light transition">
                                                <x-lucide-plus class="w-3 h-3" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>

            {{-- RIGHT: SUMMARY / PROCEED --}}
            <div class="bg-white border border-gray-border rounded-xl p-4 sm:sticky sm:top-4">
                <div class="flex items-center justify-between text-[13px] mb-3">
                    <span class="text-navy/55">Selected items</span>
                    <span id="selectedCount" class="font-semibold text-navy">0</span>
                </div>
                <div class="flex items-center justify-between text-[13px] mb-4 pb-4 border-b border-gray-border">
                    <span class="text-navy/55">Subtotal</span>
                    <span id="selectedSubtotal" class="font-bold text-teal-dark text-base">₱0</span>
                </div>

                <button type="button" id="proceedToCheckout" disabled
                        class="w-full h-11 rounded-xl bg-teal hover:bg-teal-dark disabled:bg-gray-border disabled:cursor-not-allowed text-white font-semibold text-sm transition">
                    Proceed to Checkout
                </button>
            </div>

        </div>
    </div>
</section>

@include('partials.footer')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const cartGroupsWrapper = document.getElementById('cartGroupsWrapper');
    const selectAllGlobal = document.getElementById('selectAllGlobal');
    const selectedCountEl = document.getElementById('selectedCount');
    const selectedSubtotalEl = document.getElementById('selectedSubtotal');
    const proceedBtn = document.getElementById('proceedToCheckout');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const deleteSelectedLabel = document.getElementById('deleteSelectedLabel');
    const emptyCartState = document.getElementById('emptyCartState');

    // Item price lookup, keyed by item id, read from each row's data.
    function getItemPrice(row) {
        const priceText = row.querySelector('.text-teal-dark')?.textContent || '';
        return parseFloat(priceText.replace(/[^\d.]/g, '')) || 0;
    }

    function currentItemCheckboxes() {
        return document.querySelectorAll('.item-checkbox');
    }

    function currentShopSelectAlls() {
        return document.querySelectorAll('.shop-select-all');
    }

    function bindItemCheckbox(cb) {
        cb.addEventListener('change', recalc);
    }

    function bindShopSelectAll(shopAll) {
        shopAll.addEventListener('change', function () {
            const shopIndex = shopAll.dataset.shop;
            document.querySelectorAll('.item-checkbox[data-shop="' + shopIndex + '"]')
                .forEach(cb => cb.checked = shopAll.checked);
            recalc();
        });
    }

    function bindQtyControls(row) {
        const decreaseButton = row.querySelector('[data-qty-decrease]');
        const increaseButton = row.querySelector('[data-qty-increase]');
        const qtyInput = row.querySelector('[data-qty-input]');
        if (!decreaseButton || !increaseButton || !qtyInput) return;

        const max = parseInt(qtyInput.max, 10) || 1;

        decreaseButton.addEventListener('click', function () {
            qtyInput.value = Math.max(1, (parseInt(qtyInput.value, 10) || 1) - 1);
            recalc();
        });
        increaseButton.addEventListener('click', function () {
            qtyInput.value = Math.min(max, (parseInt(qtyInput.value, 10) || 1) + 1);
            recalc();
        });
        qtyInput.addEventListener('change', recalc);
    }

    function bindItemDelete(btn) {
        btn.addEventListener('click', function () {
            const itemId = btn.dataset.itemId;
            if (!confirm('Remove this item from your cart?')) return;
            removeItem(itemId);
        });
    }

    // Removes an item row (+ its qty-stepper row) from the DOM.
    // TODO: fire the real delete request here once wired to backend,
    // e.g. fetch(`/buyer/cart/${itemId}`, { method: 'DELETE', ... })
    function removeItem(itemId) {
        const row = document.querySelector('.cart-item[data-item-id="' + itemId + '"]');
        if (!row) return;

        const group = row.closest('.cart-shop-group');
        row.remove();

        // If the shop group has no items left, remove the whole group.
        if (group && group.querySelectorAll('.cart-item').length === 0) {
            group.remove();
        }

        recalc();
    }

    function deleteSelected() {
        const selectedIds = Array.from(currentItemCheckboxes())
            .filter(cb => cb.checked)
            .map(cb => cb.dataset.itemId);

        if (selectedIds.length === 0) return;

        const label = selectedIds.length === 1 ? 'this item' : `these ${selectedIds.length} items`;
        if (!confirm(`Remove ${label} from your cart?`)) return;

        selectedIds.forEach(removeItem);
    }

    function recalc() {
        const itemCheckboxes = currentItemCheckboxes();
        const shopSelectAlls = currentShopSelectAlls();

        let count = 0;
        let subtotal = 0;

        itemCheckboxes.forEach(function (cb) {
            if (cb.checked) {
                count++;
                const row = cb.closest('.cart-item');
                const qty = parseInt(row?.querySelector('[data-qty-input]')?.value, 10) || 1;
                subtotal += getItemPrice(row) * qty;
            }
        });

        selectedCountEl.textContent = count;
        selectedSubtotalEl.textContent = '₱' + subtotal.toLocaleString('en-PH');
        proceedBtn.disabled = count === 0;

        deleteSelectedBtn.disabled = count === 0;
        deleteSelectedLabel.textContent = count > 0 ? `Delete (${count})` : 'Delete';

        // Sync "select all" checkboxes based on current state
        shopSelectAlls.forEach(function (shopAll) {
            const shopIndex = shopAll.dataset.shop;
            const shopItems = document.querySelectorAll('.item-checkbox[data-shop="' + shopIndex + '"]');
            shopAll.checked = shopItems.length > 0 && Array.from(shopItems).every(cb => cb.checked);
        });

        selectAllGlobal.checked = itemCheckboxes.length > 0 && Array.from(itemCheckboxes).every(cb => cb.checked);

        // Toggle empty state
        const hasGroups = cartGroupsWrapper.querySelectorAll('.cart-shop-group').length > 0;
        emptyCartState.classList.toggle('hidden', hasGroups);
    }

    currentItemCheckboxes().forEach(bindItemCheckbox);
    currentShopSelectAlls().forEach(bindShopSelectAll);
    document.querySelectorAll('.cart-item').forEach(bindQtyControls);
    document.querySelectorAll('.item-delete-btn').forEach(bindItemDelete);

    selectAllGlobal.addEventListener('change', function () {
        currentItemCheckboxes().forEach(cb => cb.checked = selectAllGlobal.checked);
        recalc();
    });

    deleteSelectedBtn.addEventListener('click', deleteSelected);

    proceedBtn.addEventListener('click', function () {
        const selectedIds = Array.from(currentItemCheckboxes())
            .filter(cb => cb.checked)
            .map(cb => cb.dataset.itemId);

        if (selectedIds.length === 0) return;

        const params = selectedIds.map(id => 'items[]=' + encodeURIComponent(id)).join('&');
        window.location.href = '{{ url('/buyer/cart/checkout') }}?' + params;
    });

    recalc();

});
</script>
@endpush