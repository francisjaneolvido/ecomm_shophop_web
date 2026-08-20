{{-- Path in your project: resources/views/partials/navbar.blade.php --}}
<header class="bg-white border-b border-gray-border sticky top-0 z-50">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="h-14 flex items-center gap-6">

            {{-- LOGO --}}
            <a
                href="{{ route('home') }}"
                class="flex items-center gap-2 shrink-0"
            >
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="ShopHop"
                    class="w-8 h-8 object-contain"
                >

                <div class="leading-none">
                    <span class="block font-bold text-[14px] text-navy">
                        ShopHop
                    </span>

                    <span class="hidden sm:block text-[6px] tracking-wide text-teal-dark mt-1">
                        HOP IN. SHOP MORE.
                    </span>
                </div>
            </a>


            {{-- DESKTOP NAVIGATION --}}
            {{-- FIX: added ml-2 so it doesn't sit flush against the logo
                 even when the outer gap gets squeezed at smaller xl widths --}}
            <nav class="hidden xl:flex items-center gap-1 text-[12px] text-navy shrink-0 ml-2">

                <a
                    href="{{ route('home') }}"
                    class="px-3.5 py-2 rounded-lg bg-teal/15 text-teal-dark font-medium"
                >
                    Home
                </a>

                <a
                    href="{{ route('home') }}#categories"
                    class="px-3.5 py-2 rounded-lg hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    Categories
                </a>

                <a
                    href="{{ route('home') }}#deals"
                    class="px-3.5 py-2 rounded-lg hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    Deals
                </a>

                <a
                    href="{{ route('home') }}#new-arrivals"
                    class="px-3.5 py-2 rounded-lg hover:bg-gray-bg hover:text-teal-dark transition whitespace-nowrap"
                >
                    New Arrivals
                </a>

            </nav>


            {{-- SEARCH --}}
            <div class="hidden md:flex flex-1 max-w-80 ml-auto">

                <form
                    action="#"
                    method="GET"
                    class="flex items-center w-full h-9 bg-gray-bg rounded-full px-1"
                >

                    <div class="flex items-center gap-2 px-3 flex-1 min-w-0">

                        <x-lucide-search
                            class="w-4 h-4 text-navy/35 shrink-0"
                        />

                        <input
                            type="text"
                            name="search"
                            placeholder="Search products"
                            class="bg-transparent border-0 outline-none focus:ring-0
                                   w-full min-w-0 p-0
                                   text-[12px] text-navy
                                   placeholder:text-navy/35"
                        >

                    </div>

                    <button
                        type="submit"
                        class="hidden lg:inline-flex items-center justify-center
                               h-7 bg-teal hover:bg-teal-dark
                               text-white text-[11px] font-semibold
                               px-4 rounded-full transition"
                    >
                        Search
                    </button>

                </form>

            </div>


            {{-- ACTIONS --}}
            <div class="ml-auto md:ml-0 flex items-center gap-1.5 shrink-0">

                {{-- WISHLIST --}}
                <a
                    href="#"
                    title="Wishlist"
                    class="hidden sm:flex relative w-8 h-8 items-center justify-center
                           rounded-full text-navy
                           hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    <x-lucide-heart class="w-4.5 h-4.5" />

                    <span
                        class="absolute -top-0.5 -right-0.5
                               min-w-3.5 h-3.5 px-1
                               flex items-center justify-center
                               rounded-full bg-teal
                               text-white text-[7px] font-bold"
                    >
                        2
                    </span>
                </a>


                {{-- CART --}}
                <a
                    href="#"
                    title="Shopping Cart"
                    class="relative w-8 h-8 flex items-center justify-center
                           rounded-full text-navy
                           hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    <x-lucide-shopping-cart class="w-4.5 h-4.5" />

                    <span
                        class="absolute -top-0.5 -right-0.5
                               min-w-3.5 h-3.5 px-1
                               flex items-center justify-center
                               rounded-full bg-teal
                               text-white text-[7px] font-bold"
                    >
                        3
                    </span>
                </a>


                {{-- ACCOUNT --}}
<a
    href="{{ route('login') }}"
    title="Account"
    class="hidden sm:flex w-8 h-8 items-center justify-center
           rounded-full text-navy
           hover:bg-gray-bg hover:text-teal-dark transition"
>
    <x-lucide-user class="w-4.5 h-4.5" />
</a>


{{-- DIVIDER --}}
<div class="hidden xl:block h-5 w-px bg-gray-border mx-1"></div>


{{-- LOG IN --}}
<a
    href="{{ route('login') }}"
    class="hidden xl:block text-[11px] font-medium text-navy
           hover:text-teal-dark transition whitespace-nowrap"
>
    Log In
</a>


{{-- CREATE ACCOUNT --}}
<a
    href="{{ route('register') }}"
    class="hidden xl:inline-flex items-center justify-center
           bg-teal hover:bg-teal-dark
           text-white text-[11px] font-semibold
           px-3.5 py-2 rounded-full
           transition whitespace-nowrap"
>
    Create Account
</a>


                {{-- MOBILE MENU --}}
                <button
                    type="button"
                    aria-label="Open navigation menu"
                    class="xl:hidden w-8 h-8 flex items-center justify-center
                           rounded-full text-navy
                           hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    <x-lucide-menu class="w-4.5 h-4.5" />
                </button>

            </div>

        </div>


        {{-- MOBILE SEARCH --}}
        <div class="md:hidden pb-3">

            <form
                action="#"
                method="GET"
                class="flex items-center w-full h-9 bg-gray-bg rounded-full px-1"
            >

                <div class="flex items-center gap-2 px-3 flex-1 min-w-0">

                    <x-lucide-search
                        class="w-4 h-4 text-navy/35 shrink-0"
                    />

                    <input
                        type="text"
                        name="search"
                        placeholder="Search products"
                        class="bg-transparent border-0 outline-none focus:ring-0
                               w-full min-w-0 p-0
                               text-[12px] text-navy
                               placeholder:text-navy/35"
                    >

                </div>

                <button
                    type="submit"
                    class="h-7 bg-teal hover:bg-teal-dark
                           text-white text-[11px] font-semibold
                           px-4 rounded-full transition"
                >
                    Search
                </button>

            </form>

        </div>

    </div>

</header>