{{-- Path: resources/views/buyer/dashboard/d-part-1.blade.php --}}

{{-- =========================================================
    WELCOME / BUYER OVERVIEW
========================================================= --}}
<section class="relative overflow-hidden bg-gray-bg border-b border-gray-border/70">

    {{-- Subtle decorations --}}
    <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-teal/8"></div>
    <div class="pointer-events-none absolute -bottom-28 -left-20 w-64 h-64 rounded-full bg-teal/5"></div>

    <div class="relative max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-7 sm:py-8 lg:py-9">

        <div class="grid lg:grid-cols-[minmax(0,1fr)_auto] gap-6 lg:gap-8 lg:items-center">

            {{-- WELCOME --}}
            <div class="min-w-0">

                <div class="inline-flex items-center gap-2 bg-white border border-gray-border px-3 py-1.5 rounded-full shadow-sm mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal"></span>
                    <span class="text-[11px] sm:text-xs font-semibold text-teal-dark">
                        Welcome back
                    </span>
                </div>

                <h1 class="text-navy text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight tracking-tight">
                    Hello,
                    <span class="text-teal">{{ $buyerName }}!</span>
                    <span class="inline-block text-[0.9em]"></span>
                </h1>

                <p class="text-xs sm:text-sm text-navy/55 mt-2 max-w-xl leading-relaxed">
                    Track your orders, continue shopping, and quickly jump back into your ShopHop activity.
                </p>

                <div class="flex flex-wrap gap-2.5 mt-4">
                    <a
                        href="#my-orders"
                        class="inline-flex items-center justify-center gap-2
                               bg-teal hover:bg-teal-dark
                               text-white text-xs sm:text-sm font-semibold
                               px-4 py-2.5 rounded-xl
                               shadow-sm hover:shadow-md
                               transition-all"
                    >
                        <x-lucide-package class="w-4 h-4" />
                        View Orders
                    </a>

                    <a
                        href="#recommended"
                        class="inline-flex items-center justify-center gap-2
                               bg-white border border-gray-border
                               text-navy hover:border-navy/30 hover:bg-navy/5
                               text-xs sm:text-sm font-semibold
                               px-4 py-2.5 rounded-xl
                               transition-all"
                    >
                        Continue Shopping
                        <x-lucide-arrow-right class="w-3.5 h-3.5" />
                    </a>
                </div>

            </div>

            {{-- QUICK ACCOUNT SUMMARY --}}
            <div class="grid grid-cols-3 gap-2 sm:gap-2.5 w-full lg:w-auto">

                <a
                    href="#"
                    class="group min-w-0 lg:w-32
                           bg-white border border-gray-border
                           rounded-xl px-3 py-3
                           hover:border-teal/35 hover:shadow-md hover:-translate-y-px
                           transition-all"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0 group-hover:bg-teal group-hover:text-white transition">
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-base font-bold leading-none text-navy">2</p>
                            <p class="text-[10px] sm:text-[11px] text-navy/45 mt-1 truncate">Wishlist</p>
                        </div>
                    </div>
                </a>

                <a
                    href="#"
                    class="group min-w-0 lg:w-32
                           bg-white border border-gray-border
                           rounded-xl px-3 py-3
                           hover:border-teal/35 hover:shadow-md hover:-translate-y-px
                           transition-all"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0 group-hover:bg-teal group-hover:text-white transition">
                            <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-base font-bold leading-none text-navy">3</p>
                            <p class="text-[10px] sm:text-[11px] text-navy/45 mt-1 truncate">Cart</p>
                        </div>
                    </div>
                </a>

                <a
                    href="#vouchers"
                    class="group min-w-0 lg:w-32
                           bg-white border border-gray-border
                           rounded-xl px-3 py-3
                           hover:border-teal/35 hover:shadow-md hover:-translate-y-px
                           transition-all"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0 group-hover:bg-teal group-hover:text-white transition">
                            <x-lucide-ticket class="w-3.5 h-3.5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-base font-bold leading-none text-navy">{{ count($vouchers) }}</p>
                            <p class="text-[10px] sm:text-[11px] text-navy/45 mt-1 truncate">Vouchers</p>
                        </div>
                    </div>
                </a>

            </div>

        </div>

    </div>

</section>


{{-- =========================================================
    MY PURCHASES
========================================================= --}}
<section id="my-orders" class="py-8 sm:py-9 bg-white">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        {{-- HEADER --}}
        <div class="flex items-end justify-between gap-4 mb-4 sm:mb-5">
            <div>
                <p class="text-[10px] sm:text-[11px] font-bold tracking-[0.14em] text-teal-dark mb-1">
                    MY SHOPPING
                </p>
                <h2 class="text-lg sm:text-xl font-bold text-navy tracking-tight">
                    My Purchases
                </h2>
                <p class="text-xs sm:text-sm text-navy/45 mt-1">
                    Check your order status at a glance.
                </p>
            </div>

            <a
                href="#"
                class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-teal-dark hover:text-navy transition"
            >
                View all orders
                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>
        </div>

        {{-- STATUS CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">

            @foreach ($orderSummary as $status)
                <a
                    href="#"
                    class="group bg-white border border-gray-border rounded-xl
                           px-3.5 py-3.5 sm:px-4
                           hover:border-teal/35 hover:shadow-md hover:-translate-y-px
                           transition-all duration-200"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-teal-light text-teal-dark flex items-center justify-center shrink-0 group-hover:bg-teal group-hover:text-white transition">
                            <x-dynamic-component
                                :component="'lucide-' . $status['icon']"
                                class="w-4 h-4"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs sm:text-sm font-semibold text-navy truncate">
                                    {{ $status['label'] }}
                                </p>

                                @if ($status['count'] > 0)
                                    <span class="min-w-5 h-5 px-1.5 rounded-full bg-teal text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                                        {{ $status['count'] }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-[10px] sm:text-[11px] text-navy/40 mt-0.5">
                                {{ $status['count'] }} {{ $status['count'] === 1 ? 'order' : 'orders' }}
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach

        </div>

    </div>

</section>


{{-- =========================================================
    CURRENT DELIVERY / TRACKING
========================================================= --}}
<section class="pb-9 sm:pb-10 bg-white">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="relative overflow-hidden rounded-2xl bg-navy text-white px-4 sm:px-6 lg:px-7 py-5 sm:py-6">

            <div class="pointer-events-none absolute -right-16 -top-20 w-48 h-48 rounded-full bg-teal/10"></div>

            <div class="relative">

                {{-- ORDER SUMMARY --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 md:gap-6">

                    <div class="flex items-center gap-3.5 min-w-0">
                        <img
                            src="{{ asset($activeOrder['image']) }}"
                            alt="{{ $activeOrder['product_name'] }}"
                            class="w-14 h-14 sm:w-16 sm:h-16 object-cover rounded-xl border border-white/10 shrink-0"
                        >

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 bg-teal/15 border border-teal/20 rounded-full text-[9px] sm:text-[10px] font-semibold text-teal">
                                    <x-lucide-truck class="w-3 h-3" />
                                    {{ $activeOrder['status'] }}
                                </span>

                                <span class="text-[9px] sm:text-[10px] text-white/35">
                                    {{ $activeOrder['order_number'] }}
                                </span>
                            </div>

                            <h3 class="font-semibold text-sm sm:text-base mt-1.5 truncate">
                                {{ $activeOrder['product_name'] }}
                            </h3>

                            <p class="text-[10px] sm:text-xs text-white/45 mt-0.5 truncate">
                                {{ $activeOrder['variant'] }} · Qty {{ $activeOrder['quantity'] }} · ₱{{ number_format($activeOrder['price']) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-end md:items-center justify-between md:justify-end gap-4 shrink-0">
                        <div class="md:text-right">
                            <p class="text-[9px] uppercase tracking-[0.12em] text-white/35">
                                Estimated Delivery
                            </p>
                            <p class="text-xs sm:text-sm font-semibold text-teal mt-1">
                                {{ $activeOrder['estimated_delivery'] }}
                            </p>
                        </div>

                        <a
                            href="#"
                            class="inline-flex items-center justify-center gap-1.5 h-9 px-3.5 rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 text-[11px] font-semibold text-white transition"
                        >
                            Track
                            <x-lucide-arrow-right class="w-3.5 h-3.5" />
                        </a>
                    </div>

                </div>

                {{-- TRACKING --}}
                <div class="mt-5 pt-4 border-t border-white/10">
                    <div class="grid grid-cols-5">

                        @foreach ($activeOrder['steps'] as $step)
                            <div class="relative text-center">

                                @if (! $loop->last)
                                    <div
                                        class="absolute left-1/2 top-3.5 w-full h-px {{ $step['done'] ? 'bg-teal' : 'bg-white/15' }}"
                                    ></div>
                                @endif

                                <div
                                    class="relative z-10 mx-auto w-7 h-7 rounded-full flex items-center justify-center border
                                           {{ $step['done']
                                               ? 'bg-teal border-teal text-white'
                                               : 'bg-navy border-white/20 text-white/35' }}"
                                >
                                    <x-dynamic-component
                                        :component="'lucide-' . $step['icon']"
                                        class="w-3 h-3"
                                    />
                                </div>

                                <p
                                    class="mt-1.5 text-[7px] min-[420px]:text-[8px] sm:text-[9px] font-medium leading-tight
                                           {{ $step['done'] ? 'text-white/90' : 'text-white/30' }}"
                                >
                                    {{ $step['label'] }}
                                </p>

                            </div>
                        @endforeach

                    </div>
                </div>

            </div>

        </div>

    </div>

</section>


{{-- =========================================================
    RECENTLY VIEWED
========================================================= --}}
<section class="py-9 sm:py-10 bg-gray-bg border-y border-gray-border/60">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-4 mb-4 sm:mb-5">
            <div>
                <p class="text-[10px] sm:text-[11px] font-bold tracking-[0.14em] text-teal-dark mb-1">
                    PICK UP WHERE YOU LEFT OFF
                </p>
                <h2 class="text-lg sm:text-xl font-bold text-navy tracking-tight">
                    Recently Viewed
                </h2>
                <p class="text-xs sm:text-sm text-navy/45 mt-1">
                    Products you've checked recently.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">

            @foreach ($recentlyViewed as $product)
                <article class="group bg-white rounded-xl overflow-hidden border border-gray-border hover:border-teal/30 hover:shadow-lg hover:-translate-y-px transition-all">

                    <div class="relative aspect-[4/3] bg-white overflow-hidden">
                        <img
                            src="{{ str_starts_with($product['image'], 'http')
                                ? $product['image']
                                : asset($product['image']) }}"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300"
                        >

                        @if ($product['original_price'])
                            <span class="absolute top-2 left-2 bg-teal text-white text-[9px] font-bold px-2 py-1 rounded-md shadow-sm">
                                SALE
                            </span>
                        @endif

                        <button
                            type="button"
                            title="Add to wishlist"
                            class="absolute top-2 right-2 w-7 h-7 rounded-lg bg-white/95 shadow-sm border border-black/5 flex items-center justify-center text-navy hover:text-teal-dark hover:scale-105 transition"
                        >
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <div class="p-3 sm:p-3.5">
                        <p class="text-[9px] sm:text-[10px] text-navy/40 truncate">
                            {{ $product['category'] }}
                        </p>

                        <h3 class="text-xs sm:text-sm font-semibold text-navy mt-0.5 truncate" title="{{ $product['name'] }}">
                            {{ $product['name'] }}
                        </h3>

                        <div class="flex items-center gap-1.5 mt-1.5 min-w-0">
                            <span class="text-amber-400 text-[8px] sm:text-[9px] tracking-tight shrink-0">★★★★★</span>
                            <span class="text-[9px] text-navy/35 truncate">
                                {{ $product['rating'] }} ({{ $product['reviews'] }})
                            </span>
                        </div>

                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 mt-2">
                            <span class="text-sm font-bold text-navy">
                                ₱{{ number_format($product['price']) }}
                            </span>

                            @if ($product['original_price'])
                                <span class="text-[10px] text-navy/30 line-through">
                                    ₱{{ number_format($product['original_price']) }}
                                </span>
                            @endif
                        </div>

                        <button
                            type="button"
                            class="w-full mt-2.5 flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-[10px] sm:text-[11px] font-semibold py-2 rounded-lg transition"
                        >
                            <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                            Add to Cart
                        </button>
                    </div>

                </article>
            @endforeach

        </div>

    </div>

</section>


{{-- =========================================================
    RECOMMENDED FOR YOU
========================================================= --}}
<section id="recommended" class="py-9 sm:py-10 bg-white">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-4 mb-4 sm:mb-5">
            <div>
                <p class="text-[10px] sm:text-[11px] font-bold tracking-[0.14em] text-teal-dark mb-1">
                    JUST FOR YOU
                </p>
                <h2 class="text-lg sm:text-xl font-bold text-navy tracking-tight">
                    Recommended For You
                </h2>
                <p class="text-xs sm:text-sm text-navy/45 mt-1">
                    Suggestions based on your shopping activity.
                </p>
            </div>

            <a
                href="#"
                class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-teal-dark hover:text-navy transition"
            >
                See more
                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">

            @foreach ($recommendedProducts as $product)
                <article class="group bg-white rounded-xl overflow-hidden border border-gray-border hover:border-teal/30 hover:shadow-lg hover:-translate-y-px transition-all">

                    <div class="relative aspect-[4/3] bg-gray-bg overflow-hidden">
                        <img
                            src="{{ str_starts_with($product['image'], 'http')
                                ? $product['image']
                                : asset($product['image']) }}"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300"
                        >

                        @if ($product['original_price'])
                            <span class="absolute top-2 left-2 bg-teal text-white text-[9px] font-bold px-2 py-1 rounded-md shadow-sm">
                                FOR YOU
                            </span>
                        @endif

                        <button
                            type="button"
                            title="Add to wishlist"
                            class="absolute top-2 right-2 w-7 h-7 rounded-lg bg-white/95 shadow-sm border border-black/5 flex items-center justify-center text-navy hover:text-teal-dark hover:scale-105 transition"
                        >
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <div class="p-3 sm:p-3.5">
                        <p class="text-[9px] sm:text-[10px] text-navy/40 truncate">
                            {{ $product['category'] }}
                        </p>

                        <h3 class="text-xs sm:text-sm font-semibold text-navy mt-0.5 truncate" title="{{ $product['name'] }}">
                            {{ $product['name'] }}
                        </h3>

                        <div class="flex items-center gap-1.5 mt-1.5 min-w-0">
                            <span class="text-amber-400 text-[8px] sm:text-[9px] tracking-tight shrink-0">★★★★★</span>
                            <span class="text-[9px] text-navy/35 truncate">
                                {{ $product['rating'] }} ({{ $product['reviews'] }})
                            </span>
                        </div>

                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 mt-2">
                            <span class="text-sm font-bold text-navy">
                                ₱{{ number_format($product['price']) }}
                            </span>

                            @if ($product['original_price'])
                                <span class="text-[10px] text-navy/30 line-through">
                                    ₱{{ number_format($product['original_price']) }}
                                </span>
                            @endif
                        </div>

                        <button
                            type="button"
                            class="w-full mt-2.5 flex items-center justify-center gap-1.5 bg-teal hover:bg-teal-dark text-white text-[10px] sm:text-[11px] font-semibold py-2 rounded-lg transition"
                        >
                            <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                            Add to Cart
                        </button>
                    </div>

                </article>
            @endforeach

        </div>

    </div>

</section>
