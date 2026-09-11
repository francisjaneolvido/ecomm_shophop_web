{{-- Path: resources/views/buyer/dashboard/d-part-2.blade.php --}}

@php
    // Folder + slug lookup now lives in one place: config/shophop_categories.php
    // (also used by the category browse page), so this stays in sync automatically.
    $categoryConfig = collect(config('shophop_categories', []))->keyBy('name');
    $categorySlideshows = $categoryConfig->pluck('folder', 'name')->all();
@endphp


{{-- =========================================================
    SHOP BY CATEGORY
========================================================= --}}
<section id="categories" class="scroll-mt-20 py-6 sm:py-7 bg-gray-bg">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">
            <div>
                <p class="text-[9px] sm:text-[10px] font-semibold text-teal-dark tracking-[0.12em] uppercase">
                    Explore
                </p>

                <p role="heading" aria-level="2" class="text-[16px] sm:text-[18px] font-bold text-navy mt-0.5 leading-tight">
                    Shop by Category
                </p>

                <p class="text-[10px] sm:text-[11px] text-navy/45 mt-0.5">
                    Find what you need, faster.
                </p>
            </div>

            <span class="hidden sm:inline-flex text-[11px] text-navy/35">
                Swipe or use arrows
            </span>
        </div>


        <div class="relative buyer-reveal" data-category-slider>

            <button
                type="button"
                data-category-prev
                aria-label="Previous categories"
                class="hidden absolute left-0 top-1/2
                       -translate-x-1/2 -translate-y-1/2 z-20
                       w-8 h-8 rounded-full bg-white
                       border border-gray-border shadow-md
                       items-center justify-center text-navy
                       hover:text-teal-dark hover:border-teal/30
                       transition"
            >
                <x-lucide-chevron-left class="w-3.5 h-3.5" />
            </button>


            <div
                data-category-track
                class="grid grid-flow-col gap-2.5
                       overflow-x-auto scroll-smooth
                       snap-x snap-mandatory
                       [&::-webkit-scrollbar]:hidden
                       auto-cols-[calc((100%-0.75rem)/2)]
                       sm:auto-cols-[calc((100%-1.5rem)/3)]
                       md:auto-cols-[calc((100%-3rem)/5)]
                       lg:auto-cols-[calc((100%-3.75rem)/6)]
                       xl:auto-cols-[calc((100%-5.25rem)/8)]"
            >
                @forelse ($categories as $category)

                    @php
                        $folder = $categorySlideshows[$category['name']] ?? null;
                        $slideImages = [];

                        if ($folder) {
                            $folderPath = public_path('images/category_icons_bg/' . $folder);

                            if (is_dir($folderPath)) {
                                $allFiles = glob(
                                    $folderPath . '/*.{jpg,jpeg,png,webp,avif,gif}',
                                    GLOB_BRACE
                                );

                                $slideImages = array_map(
                                    'basename',
                                    array_slice($allFiles, 0, 6)
                                );
                            }
                        }

                        $imageCount = count($slideImages);
                        $slideInterval = $imageCount > 0 ? 12 / $imageCount : 0;
                    @endphp


                    <a
                        href="{{ route('buyer.category.show', $categoryConfig[$category['name']]['slug'] ?? \Illuminate\Support\Str::slug($category['name'])) }}"
                        class="group relative overflow-hidden
                               min-h-28 sm:min-h-30
                               rounded-xl bg-white
                               border border-gray-border
                               px-2.5 py-3 text-center
                               flex flex-col items-center justify-center
                               hover:border-teal/35
                               hover:-translate-y-0.5
                               hover:shadow-md
                               snap-start
                               transition-all duration-200"
                    >
                        @if ($imageCount > 0)
                            <div class="kb-stack z-0">
                                @foreach ($slideImages as $i => $file)
                                    <img
                                        src="{{ asset('images/category_icons_bg/' . $folder . '/' . $file) }}"
                                        alt=""
                                        style="animation-delay: {{ $i * $slideInterval }}s, {{ $i * $slideInterval }}s; animation-duration: 12s, {{ 12 * $imageCount }}s;"
                                    >
                                @endforeach

                                <div
                                    class="absolute inset-0 bg-white/80
                                           group-hover:bg-white/70 transition"
                                ></div>
                            </div>
                        @endif


                        <div
                            class="relative z-10
                                   w-9 h-9 rounded-lg
                                   bg-white/95 shadow-sm
                                   text-teal-dark
                                   flex items-center justify-center
                                   group-hover:bg-teal
                                   group-hover:text-white
                                   transition"
                        >
                            <x-dynamic-component
                                :component="'lucide-' . $category['icon']"
                                class="w-3.5 h-3.5"
                            />
                        </div>

                        <p
                            class="relative z-10 mt-1.5
                                   text-[10px] sm:text-[11px]
                                   font-semibold text-navy
                                   leading-snug line-clamp-2"
                        >
                            {{ $category['name'] }}
                        </p>
                    </a>

                @empty
                    <div class="col-span-full py-8 text-center text-sm text-navy/40">
                        No categories available yet.
                    </div>
                @endforelse
            </div>


            <button
                type="button"
                data-category-next
                aria-label="Next categories"
                class="absolute right-0 top-1/2
                       translate-x-1/2 -translate-y-1/2 z-20
                       w-8 h-8 rounded-full bg-white
                       border border-gray-border shadow-md
                       flex items-center justify-center text-navy
                       hover:text-teal-dark hover:border-teal/30
                       transition"
            >
                <x-lucide-chevron-right class="w-3.5 h-3.5" />
            </button>

        </div>
    </div>
</section>


{{-- =========================================================
    VOUCHERS
========================================================= --}}
<section id="vouchers" class="scroll-mt-20 py-6 sm:py-7 bg-white">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative overflow-hidden buyer-reveal
                   bg-[#EAF9F5]
                   border border-teal/10
                   rounded-2xl
                   px-4 sm:px-5 lg:px-6
                   py-4 sm:py-5"
        >
            <div class="pointer-events-none absolute -right-16 -bottom-20 w-48 h-48 bg-teal/10 rounded-full"></div>

            <div class="relative">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">

                    <div class="flex items-center gap-2.5 min-w-0">
                        <div
                            class="w-8 h-8 rounded-lg
                                   bg-teal text-white
                                   flex items-center justify-center
                                   shrink-0"
                        >
                            <x-lucide-ticket class="w-3.5 h-3.5" />
                        </div>

                        <div class="min-w-0">
                            <p class="text-[10px] sm:text-xs font-semibold text-teal-dark tracking-[0.12em] uppercase">
                                Savings waiting for you
                            </p>

                            <p role="heading" aria-level="2" class="text-[15px] sm:text-[17px] font-bold text-navy mt-0.5 leading-tight">
                                {{ count($vouchers) }} vouchers available
                            </p>
                        </div>
                    </div>


                    <a
                        href="{{ Route::has('buyer.profile') ? route('buyer.profile') : '#' }}"
                        class="inline-flex items-center justify-center gap-1.5
                               bg-teal hover:bg-teal-dark
                               text-white text-[10.5px] sm:text-[11px] font-semibold
                               px-3.5 py-2 rounded-lg
                               transition shrink-0"
                    >
                        View Vouchers
                        <x-lucide-arrow-right class="w-3.5 h-3.5" />
                    </a>
                </div>


                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    @forelse ($vouchers as $voucher)
                        <div
                            class="bg-white/90 border border-white
                                   rounded-lg p-2.5
                                   hover:bg-white hover:shadow-sm
                                   transition"
                        >
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-7 h-7 rounded-md
                                           bg-teal-light text-teal-dark
                                           flex items-center justify-center
                                           shrink-0"
                                >
                                    <x-dynamic-component
                                        :component="'lucide-' . $voucher['icon']"
                                        class="w-3.5 h-3.5"
                                    />
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                        <p class="text-[10.5px] sm:text-[11.5px] font-bold text-navy">
                                            {{ $voucher['title'] }}
                                        </p>

                                        <span class="text-[9px] font-mono font-semibold text-teal-dark bg-teal-light px-2 py-0.5 rounded-md">
                                            {{ $voucher['code'] }}
                                        </span>
                                    </div>

                                    <p class="text-[9px] sm:text-[10px] text-navy/45 mt-0.5 truncate">
                                        {{ $voucher['description'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="sm:col-span-2 lg:col-span-3 text-center py-5 text-xs text-navy/40">
                            No vouchers available right now.
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</section>


{{-- =========================================================
    DEALS FOR YOU
========================================================= --}}
<section id="deals" class="scroll-mt-20 py-6 sm:py-7 bg-gray-bg">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">
            <div>
                <p class="text-[9px] sm:text-[10px] font-semibold text-teal-dark tracking-[0.12em] uppercase">
                    Save More
                </p>

                <p role="heading" aria-level="2" class="text-[16px] sm:text-[18px] font-bold text-navy mt-0.5 leading-tight">
                    Deals For You
                </p>

                <p class="text-[10px] sm:text-[11px] text-navy/45 mt-0.5">
                    Limited-time offers selected for ShopHop buyers.
                </p>
            </div>

            <a
                href="#"
                class="hidden sm:inline-flex items-center gap-1
                       text-xs font-semibold text-teal-dark
                       hover:text-navy transition"
            >
                See all
                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>
        </div>


        <div
            class="grid grid-cols-2 md:grid-cols-3
                   lg:grid-cols-4 xl:grid-cols-5
                   gap-2.5 sm:gap-3 buyer-stagger"
        >
            @forelse ($dealProducts as $product)

                <article
                    class="group bg-white
                           rounded-xl overflow-hidden
                           border border-gray-border
                           hover:border-teal/35
                           hover:shadow-md hover:-translate-y-0.5
                           transition-all duration-300"
                >
                    <div class="relative aspect-4/3 bg-white overflow-hidden">
                        <a
                            href="{{ route('buyer.product.show', $product['id']) }}"
                            class="block w-full h-full"
                        >
                            <img
                                src="{{ str_starts_with($product['image'], 'http')
                                    ? $product['image']
                                    : asset($product['image']) }}"
                                alt="{{ $product['name'] }}"
                                class="w-full h-full object-cover
                                       group-hover:scale-[1.03]
                                       transition-transform duration-300"
                            >
                        </a>

                        <span
                            class="absolute top-2 left-2
                                   bg-navy text-white
                                   text-[9px] font-bold
                                   px-2 py-1 rounded-md"
                        >
                            DEAL
                        </span>

                        <button
                            type="button"
                            title="Add to wishlist"
                            class="absolute top-2 right-2
                                   w-7 h-7 rounded-lg
                                   bg-white/95 shadow-sm
                                   text-navy
                                   flex items-center justify-center
                                   hover:text-teal-dark
                                   transition"
                        >
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </button>
                    </div>


                    <div class="p-2.5">
                        <p class="text-[9px] sm:text-[10px] text-navy/40 truncate">
                            {{ $product['category'] }}
                        </p>

                        <p role="heading" aria-level="3"
                            class="text-[10.5px] sm:text-[11.5px] font-semibold
                                   text-navy mt-0.5 truncate"
                            title="{{ $product['name'] }}"
                        >
                            <a
                                href="{{ route('buyer.product.show', $product['id']) }}"
                                class="hover:text-teal-dark transition"
                            >
                                {{ $product['name'] }}
                            </a>
                        </p>

                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="text-amber-400 text-[9px] tracking-tight">
                                ★★★★★
                            </span>

                            <span class="text-[9px] text-navy/35">
                                {{ $product['rating'] }} ({{ $product['reviews'] }})
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span class="text-[11px] sm:text-xs font-bold text-teal-dark">
                                ₱{{ number_format($product['price']) }}
                            </span>

                            @if (! empty($product['original_price']))
                                <span class="text-[9px] sm:text-[10px] text-navy/30 line-through">
                                    ₱{{ number_format($product['original_price']) }}
                                </span>
                            @endif
                        </div>

                        <button
                            type="button"
                            class="w-full mt-2.5
                                   bg-teal hover:bg-teal-dark
                                   text-white
                                   text-[9.5px] sm:text-[10.5px] font-semibold
                                   py-2 rounded-lg
                                   flex items-center justify-center gap-1.5
                                   transition"
                        >
                            <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                            Add to Cart
                        </button>
                    </div>
                </article>

            @empty
                <div class="col-span-full py-8 text-center text-sm text-navy/40">
                    No deals available right now.
                </div>
            @endforelse
        </div>
    </div>
</section>


{{-- =========================================================
    NEW ARRIVALS
========================================================= --}}
<section id="new-arrivals" class="scroll-mt-20 py-6 sm:py-7 bg-white">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between gap-3 mb-3">
            <div>
                <p class="text-[9px] sm:text-[10px] font-semibold text-teal-dark tracking-[0.12em] uppercase">
                    Just In
                </p>

                <p role="heading" aria-level="2" class="text-[16px] sm:text-[18px] font-bold text-navy mt-0.5 leading-tight">
                    New Arrivals
                </p>

                <p class="text-[10px] sm:text-[11px] text-navy/45 mt-0.5">
                    Fresh products recently added to ShopHop.
                </p>
            </div>

            <a
                href="#"
                class="hidden sm:inline-flex items-center gap-1
                       text-xs font-semibold text-teal-dark
                       hover:text-navy transition"
            >
                See all
                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>
        </div>


        <div
            class="grid grid-cols-2 md:grid-cols-3
                   lg:grid-cols-4 xl:grid-cols-5
                   gap-2.5 sm:gap-3 buyer-stagger"
        >
            @forelse ($newArrivals as $product)

                <article
                    class="group bg-white
                           rounded-xl overflow-hidden
                           border border-gray-border
                           hover:border-teal/35
                           hover:shadow-md hover:-translate-y-0.5
                           transition-all duration-300"
                >
                    <div class="relative aspect-4/3 bg-gray-bg overflow-hidden">
                        <a
                            href="{{ route('buyer.product.show', $product['id']) }}"
                            class="block w-full h-full"
                        >
                            <img
                                src="{{ str_starts_with($product['image'], 'http')
                                    ? $product['image']
                                    : asset($product['image']) }}"
                                alt="{{ $product['name'] }}"
                                class="w-full h-full object-cover
                                       group-hover:scale-[1.03]
                                       transition-transform duration-300"
                            >
                        </a>

                        <span
                            class="absolute top-2 left-2
                                   bg-teal text-white
                                   text-[9px] font-bold
                                   px-2 py-1 rounded-md"
                        >
                            NEW
                        </span>

                        <button
                            type="button"
                            title="Add to wishlist"
                            class="absolute top-2 right-2
                                   w-7 h-7 rounded-lg
                                   bg-white/95 shadow-sm
                                   text-navy
                                   flex items-center justify-center
                                   hover:text-teal-dark
                                   transition"
                        >
                            <x-lucide-heart class="w-3.5 h-3.5" />
                        </button>
                    </div>


                    <div class="p-2.5">
                        <p class="text-[9px] sm:text-[10px] text-navy/40 truncate">
                            {{ $product['category'] }}
                        </p>

                        <p role="heading" aria-level="3"
                            class="text-[10.5px] sm:text-[11.5px] font-semibold
                                   text-navy mt-0.5 truncate"
                            title="{{ $product['name'] }}"
                        >
                            <a
                                href="{{ route('buyer.product.show', $product['id']) }}"
                                class="hover:text-teal-dark transition"
                            >
                                {{ $product['name'] }}
                            </a>
                        </p>

                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="text-amber-400 text-[9px] tracking-tight">
                                ★★★★★
                            </span>

                            <span class="text-[9px] text-navy/35">
                                {{ $product['rating'] }} ({{ $product['reviews'] }})
                            </span>
                        </div>

                        <p class="text-[11px] sm:text-xs font-bold text-navy mt-1.5">
                            ₱{{ number_format($product['price']) }}
                        </p>

                        <button
                            type="button"
                            class="w-full mt-2.5
                                   bg-teal hover:bg-teal-dark
                                   text-white
                                   text-[9.5px] sm:text-[10.5px] font-semibold
                                   py-2 rounded-lg
                                   flex items-center justify-center gap-1.5
                                   transition"
                        >
                            <x-lucide-shopping-cart class="w-3.5 h-3.5" />
                            Add to Cart
                        </button>
                    </div>
                </article>

            @empty
                <div class="col-span-full py-8 text-center text-sm text-navy/40">
                    No new arrivals yet.
                </div>
            @endforelse
        </div>
    </div>
</section>


{{-- =========================================================
    BUYER SHORTCUT CTA — SHOPHOP LIGHT
========================================================= --}}
<section class="pb-6 sm:pb-7 bg-white">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative overflow-hidden buyer-reveal
                   rounded-2xl
                   bg-[#EAF9F5]
                   border border-teal/10
                   px-4 sm:px-5 lg:px-6
                   py-4 sm:py-5
                   shadow-lg shadow-navy/5"
        >
            <div class="pointer-events-none absolute -right-20 -top-24 w-56 h-56 bg-teal/10 rounded-full blur-sm"></div>
            <div class="pointer-events-none absolute -left-16 -bottom-20 w-48 h-48 bg-white/70 rounded-full"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                <div>
                    <p class="text-[9px] sm:text-[10px] font-semibold text-teal-dark tracking-[0.12em] uppercase">
                        Your ShopHop
                    </p>

                    <p role="heading" aria-level="2" class="text-[16px] sm:text-[18px] font-bold text-navy mt-0.5 leading-tight">
                        Everything you need, one hop away.
                    </p>

                    <p class="text-[10px] sm:text-[11px] text-navy/50 mt-1 max-w-xl">
                        Manage orders, favorites, vouchers, and your account from one place.
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 shrink-0">

                    <a href="#my-orders"
                       class="group flex flex-col items-center justify-center min-w-20
                              bg-white hover:bg-white
                              border border-white
                              rounded-xl px-3 py-2.5
                              text-navy shadow-sm
                              hover:-translate-y-0.5 hover:shadow-md
                              transition-all duration-300">
                        <x-lucide-package class="w-3.5 h-3.5 text-teal-dark" />
                        <span class="text-[9px] sm:text-[10px] font-semibold mt-1.5">Orders</span>
                    </a>

                    <a href="#"
                       class="group flex flex-col items-center justify-center min-w-20
                              bg-white hover:bg-white
                              border border-white
                              rounded-xl px-3 py-2.5
                              text-navy shadow-sm
                              hover:-translate-y-0.5 hover:shadow-md
                              transition-all duration-300">
                        <x-lucide-heart class="w-3.5 h-3.5 text-teal-dark" />
                        <span class="text-[9px] sm:text-[10px] font-semibold mt-1.5">Wishlist</span>
                    </a>

                    <a href="#vouchers"
                       class="group flex flex-col items-center justify-center min-w-20
                              bg-white hover:bg-white
                              border border-white
                              rounded-xl px-3 py-2.5
                              text-navy shadow-sm
                              hover:-translate-y-0.5 hover:shadow-md
                              transition-all duration-300">
                        <x-lucide-ticket class="w-3.5 h-3.5 text-teal-dark" />
                        <span class="text-[9px] sm:text-[10px] font-semibold mt-1.5">Vouchers</span>
                    </a>

                    <a href="{{ Route::has('buyer.profile') ? route('buyer.profile') : '#' }}"
                       class="group flex flex-col items-center justify-center min-w-20
                              bg-white hover:bg-white
                              border border-white
                              rounded-xl px-3 py-2.5
                              text-navy shadow-sm
                              hover:-translate-y-0.5 hover:shadow-md
                              transition-all duration-300">
                        <x-lucide-settings class="w-3.5 h-3.5 text-teal-dark" />
                        <span class="text-[9px] sm:text-[10px] font-semibold mt-1.5">Account</span>
                    </a>

                </div>
            </div>
        </div>

    </div>
</section>