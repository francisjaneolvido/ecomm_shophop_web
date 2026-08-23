{{-- =========================================================
    DEALS / PROMOTION
========================================================= --}}
<section
    id="deals"
    class="py-12 sm:py-16 lg:py-20 bg-gray-bg"
>
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="relative overflow-hidden
                   rounded-2xl sm:rounded-3xl
                   bg-navy
                   px-6 sm:px-8 lg:px-14
                   py-9 sm:py-11 lg:py-14
                   shadow-xl shadow-navy/10"
        >

            {{-- Decorative glow --}}
            <div
                class="pointer-events-none absolute
                       -right-24 -top-24
                       w-64 h-64
                       rounded-full
                       bg-teal/10
                       blur-sm"
            ></div>

            <div
                class="pointer-events-none absolute
                       right-24 -bottom-28
                       w-52 h-52
                       rounded-full
                       bg-teal/5"
            ></div>

            {{-- Small accent --}}
            <div
                class="pointer-events-none absolute
                       left-[45%] top-8
                       w-24 h-24
                       rounded-full
                       bg-white/5
                       blur-2xl"
            ></div>


            <div
                class="relative
                       grid lg:grid-cols-[1.2fr_0.8fr]
                       gap-10 lg:gap-14
                       items-center"
            >

                {{-- =================================================
                    LEFT CONTENT
                ================================================== --}}
                <div class="max-w-xl">

                    <span
                        class="inline-flex items-center gap-2
                               bg-teal/15
                               border border-teal/20
                               text-teal
                               px-3.5 sm:px-4
                               py-2
                               rounded-full
                               text-[11px] sm:text-xs
                               font-semibold
                               tracking-wide
                               mb-5"
                    >
                        <x-lucide-badge-percent class="w-4 h-4" />

                        SHOPHOP DEALS
                    </span>


                    <h2
                        class="text-white
                               text-3xl sm:text-4xl lg:text-[42px]
                               font-bold
                               leading-tight"
                    >
                        Great Finds.
                        <span class="block text-teal">
                            Better Prices.
                        </span>
                    </h2>


                    <p
                        class="text-sm sm:text-base
                               text-white/65
                               mt-5
                               max-w-lg
                               leading-relaxed"
                    >
                        Discover limited-time offers, discounted favorites,
                        and great-value finds across ShopHop.
                    </p>


                    {{-- Deal highlights --}}
                    <div
                        class="flex flex-wrap
                               gap-x-5 gap-y-3
                               mt-6"
                    >

                        <div class="flex items-center gap-2 text-white/75 text-xs sm:text-sm">

                            <span
                                class="w-5 h-5
                                       rounded-full
                                       bg-teal/15
                                       flex items-center justify-center"
                            >
                                <x-lucide-check class="w-3 h-3 text-teal" />
                            </span>

                            Limited-time deals

                        </div>


                        <div class="flex items-center gap-2 text-white/75 text-xs sm:text-sm">

                            <span
                                class="w-5 h-5
                                       rounded-full
                                       bg-teal/15
                                       flex items-center justify-center"
                            >
                                <x-lucide-check class="w-3 h-3 text-teal" />
                            </span>

                            Selected discounts

                        </div>

                    </div>


                    <a
                        href="#trending"
                        class="inline-flex
                               items-center justify-center gap-2
                               mt-7 sm:mt-8
                               bg-teal
                               hover:bg-teal-dark
                               text-white
                               text-sm font-semibold
                               px-6
                               py-3.5
                               rounded-full
                               shadow-lg shadow-teal/20
                               hover:-translate-y-0.5
                               transition-all duration-300"
                    >
                        Explore Deals

                        <x-lucide-arrow-right class="w-4 h-4" />
                    </a>

                </div>



                {{-- =================================================
                    RIGHT BENEFIT CARDS
                ================================================== --}}
                <div
                    class="grid grid-cols-2
                           gap-3 sm:gap-4
                           w-full
                           max-w-md
                           lg:max-w-none
                           lg:ml-auto"
                >

                    {{-- Discount --}}
                    <div
                        class="group
                               bg-white/10
                               hover:bg-white/15
                               backdrop-blur
                               rounded-2xl
                               p-4 sm:p-5
                               border border-white/10
                               transition-all duration-300"
                    >

                        <div
                            class="w-10 h-10
                                   rounded-xl
                                   bg-teal/15
                                   flex items-center justify-center
                                   mb-4"
                        >
                            <x-lucide-tag class="w-5 h-5 text-teal" />
                        </div>

                        <p class="text-white text-xl sm:text-2xl font-bold">
                            Up to 50%
                        </p>

                        <p class="text-white/50 text-[10px] sm:text-xs mt-1.5 leading-relaxed">
                            Off selected items
                        </p>

                    </div>


                    {{-- Delivery --}}
                    <div
                        class="group
                               bg-white/10
                               hover:bg-white/15
                               backdrop-blur
                               rounded-2xl
                               p-4 sm:p-5
                               border border-white/10
                               transition-all duration-300"
                    >

                        <div
                            class="w-10 h-10
                                   rounded-xl
                                   bg-teal/15
                                   flex items-center justify-center
                                   mb-4"
                        >
                            <x-lucide-truck class="w-5 h-5 text-teal" />
                        </div>

                        <p class="text-white text-xl sm:text-2xl font-bold">
                            Fast
                        </p>

                        <p class="text-white/50 text-[10px] sm:text-xs mt-1.5 leading-relaxed">
                            Delivery options
                        </p>

                    </div>


                    {{-- Secure --}}
                    <div
                        class="group
                               bg-white/10
                               hover:bg-white/15
                               backdrop-blur
                               rounded-2xl
                               p-4 sm:p-5
                               border border-white/10
                               transition-all duration-300"
                    >

                        <div
                            class="w-10 h-10
                                   rounded-xl
                                   bg-teal/15
                                   flex items-center justify-center
                                   mb-4"
                        >
                            <x-lucide-shield-check class="w-5 h-5 text-teal" />
                        </div>

                        <p class="text-white text-xl sm:text-2xl font-bold">
                            Secure
                        </p>

                        <p class="text-white/50 text-[10px] sm:text-xs mt-1.5 leading-relaxed">
                            Safe shopping experience
                        </p>

                    </div>


                    {{-- Support --}}
                    <div
                        class="group
                               bg-white/10
                               hover:bg-white/15
                               backdrop-blur
                               rounded-2xl
                               p-4 sm:p-5
                               border border-white/10
                               transition-all duration-300"
                    >

                        <div
                            class="w-10 h-10
                                   rounded-xl
                                   bg-teal/15
                                   flex items-center justify-center
                                   mb-4"
                        >
                            <x-lucide-headphones class="w-5 h-5 text-teal" />
                        </div>

                        <p class="text-white text-xl sm:text-2xl font-bold">
                            24/7
                        </p>

                        <p class="text-white/50 text-[10px] sm:text-xs mt-1.5 leading-relaxed">
                            Customer support
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>



{{-- =========================================================
    TRENDING PRODUCTS
========================================================= --}}
<section
    id="trending"
    class="py-12 sm:py-16 lg:py-20 bg-white"
>

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">


        {{-- Section header --}}
        <div
            class="flex flex-col
                   md:flex-row
                   md:items-end
                   md:justify-between
                   gap-4
                   mb-8 sm:mb-10"
        >

            <div>

                <p
                    class="text-teal-dark
                           text-xs sm:text-sm
                           font-semibold
                           mb-2
                           tracking-wide"
                >
                    POPULAR PICKS
                </p>


                <h2 class="text-navy">
                    Trending Now
                </h2>


                <p class="text-sm sm:text-base text-navy/55 mt-2">
                    Products shoppers are loving right now.
                </p>

            </div>


            <a
                href="#"
                class="inline-flex items-center gap-2
                       text-sm font-semibold
                       text-teal-dark
                       hover:text-navy
                       transition"
            >

                View all products

                <x-lucide-arrow-right class="w-4 h-4" />

            </a>

        </div>



        {{-- Product Grid --}}
        <div
            class="grid
                   grid-cols-1
                   min-[420px]:grid-cols-2
                   md:grid-cols-3
                   lg:grid-cols-4
                   gap-4 sm:gap-5"
        >

            @foreach ($trendingProducts as $product)

                <article
                    class="group
                           bg-white
                           rounded-2xl
                           overflow-hidden
                           border border-gray-border
                           hover:border-teal/30
                           hover:shadow-xl
                           transition-all duration-300"
                >


                    {{-- Product Image --}}
                    <div class="relative aspect-square bg-gray-bg overflow-hidden">

                        <img
                            src="{{ str_starts_with($product['image'], 'http')
                                ? $product['image']
                                : asset('images/' . $product['image']) }}"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full
                                   object-cover
                                   group-hover:scale-105
                                   transition-transform duration-500"
                        >


                        {{-- Sale --}}
                        @if ($product['original_price'])

                            <span
                                class="absolute
                                       top-3 left-3
                                       bg-teal
                                       text-white
                                       text-[10px] font-bold
                                       px-3 py-1.5
                                       rounded-full"
                            >
                                SALE
                            </span>

                        @endif


                        {{-- Wishlist --}}
                        <button
                            type="button"
                            data-login-required
                            class="absolute
                                   top-3 right-3
                                   w-9 h-9
                                   rounded-full
                                   bg-white/95
                                   backdrop-blur
                                   flex items-center justify-center
                                   text-navy
                                   hover:text-teal-dark
                                   hover:scale-105
                                   transition"
                            title="Add to wishlist"
                            aria-label="Add {{ $product['name'] }} to wishlist"
                        >

                            <x-lucide-heart class="w-4 h-4" />

                        </button>

                    </div>



                    {{-- Product Info --}}
                    <div class="p-4 sm:p-5">


                        <span class="text-[11px] sm:text-xs text-navy/45">
                            {{ $product['category'] }}
                        </span>


                        <h3
                            class="text-base sm:text-lg
                                   text-navy
                                   mt-1 mb-2
                                   line-clamp-1"
                        >
                            {{ $product['name'] }}
                        </h3>


                        {{-- Rating --}}
                        <div class="flex flex-wrap items-center gap-2 mb-3">

                            <div class="flex text-amber-400 text-[10px] sm:text-xs">
                                ★★★★★
                            </div>

                            <span class="text-[10px] sm:text-xs text-navy/45">
                                {{ $product['rating'] }}
                                ({{ $product['reviews'] }})
                            </span>

                        </div>



                        {{-- Price --}}
                        <div class="flex flex-wrap items-center gap-2 mb-4">

                            <span class="font-bold text-base sm:text-lg text-navy">
                                ₱{{ number_format($product['price']) }}
                            </span>


                            @if ($product['original_price'])

                                <span
                                    class="line-through
                                           text-[11px] sm:text-xs
                                           text-navy/35"
                                >
                                    ₱{{ number_format($product['original_price']) }}
                                </span>

                            @endif

                        </div>



                        {{-- Add to cart --}}
                        <button
                            type="button"
                            data-login-required
                            class="w-full
                                   flex items-center justify-center gap-2
                                   bg-teal
                                   hover:bg-teal-dark
                                   text-white
                                   text-xs sm:text-sm
                                   font-semibold
                                   py-2.5 sm:py-3
                                   rounded-xl
                                   transition"
                        >

                            <x-lucide-shopping-cart class="w-4 h-4" />

                            Add to Cart

                        </button>

                    </div>

                </article>

            @endforeach

        </div>

    </div>

</section>

