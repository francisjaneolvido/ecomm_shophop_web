<header class="bg-white border-b border-gray-border sticky top-0 z-50">
    <div class="max-w-310 mx-auto px-6 py-3 flex items-center gap-6">

        {{-- LOGO --}}
        <a
            href="{{ route('home') }}"
            class="flex items-center gap-2 shrink-0"
        >
            <img
                src="{{ asset('images/logo.png') }}"
                alt="ShopHop"
                class="w-9 h-9 object-contain"
            >

            <div class="leading-none">
                <span class="block font-bold text-[15px] text-navy">
                    ShopHop
                </span>

                <span class="block text-[7px] tracking-wide text-teal-dark mt-1">
                    HOP IN. SHOP MORE.
                </span>
            </div>
        </a>


        {{-- NAVIGATION --}}
        <nav class="hidden lg:flex items-center gap-1 text-[13px] text-navy">

            {{-- HOME --}}
            <a
                href="{{ route('home') }}"
                class="px-4 py-2 rounded-lg bg-teal/15 text-teal-dark font-medium"
            >
                Home
            </a>

            {{-- CATEGORIES --}}
            <a
                href="{{ route('home') }}#categories"
                class="px-4 py-2 rounded-lg hover:bg-gray-bg hover:text-teal-dark transition"
            >
                Categories
            </a>

            {{-- DEALS --}}
            <a
                href="{{ route('home') }}#deals"
                class="px-4 py-2 rounded-lg hover:bg-gray-bg hover:text-teal-dark transition"
            >
                Deals
            </a>

            {{-- NEW ARRIVALS --}}
            <a
                href="{{ route('home') }}#new-arrivals"
                class="px-4 py-2 rounded-lg hover:bg-gray-bg hover:text-teal-dark transition"
            >
                New Arrivals
            </a>

        </nav>


        {{-- SEARCH --}}
        <div class="hidden md:flex flex-1 max-w-75 ml-auto">

            <form
                action="#"
                method="GET"
                class="flex items-center w-full bg-gray-bg rounded-full p-1.5"
            >

                <div class="flex items-center gap-2 px-3 flex-1">

                    <x-lucide-search
                        class="w-4 h-4 text-navy/40 shrink-0"
                    />

                    <input
                        type="text"
                        name="search"
                        placeholder="Search products"
                        class="bg-transparent outline-none border-0 w-full text-xs text-navy placeholder:text-navy/40"
                    >

                </div>

                <button
                    type="submit"
                    class="bg-teal hover:bg-teal-dark text-white text-xs font-semibold px-4 py-2 rounded-full transition"
                >
                    Search
                </button>

            </form>

        </div>


        {{-- ACTIONS --}}
        <div class="flex items-center gap-4 shrink-0">

            {{-- WISHLIST --}}
            <a
                href="#"
                title="Wishlist"
                class="relative text-navy hover:text-teal-dark transition"
            >

                <x-lucide-heart class="w-[19px] h-[19px]" />

                <span
                    class="absolute -top-2 -right-2 min-w-[15px] h-[15px] px-1 flex items-center justify-center rounded-full bg-teal text-white text-[8px] font-bold"
                >
                    2
                </span>

            </a>


            {{-- CART --}}
            <a
                href="#"
                title="Shopping Cart"
                class="relative text-navy hover:text-teal-dark transition"
            >

                <x-lucide-shopping-cart class="w-[19px] h-[19px]" />

                <span
                    class="absolute -top-2 -right-2 min-w-[15px] h-[15px] px-1 flex items-center justify-center rounded-full bg-teal text-white text-[8px] font-bold"
                >
                    3
                </span>

            </a>


            {{-- ACCOUNT --}}
            <a
                href="#"
                title="Account"
                class="text-navy hover:text-teal-dark transition"
            >
                <x-lucide-user class="w-[19px] h-[19px]" />
            </a>


            {{-- DIVIDER --}}
            <div class="hidden lg:block h-6 w-px bg-gray-border"></div>


            {{-- SIGN IN --}}
            <a
                href="#"
                class="hidden lg:block text-xs font-medium text-navy hover:text-teal-dark transition"
            >
                Sign In
            </a>


            {{-- CREATE ACCOUNT --}}
            <a
                href="#"
                class="bg-teal hover:bg-teal-dark text-white text-xs font-semibold px-4 py-2.5 rounded-full transition whitespace-nowrap"
            >
                Create Account
            </a>

        </div>

    </div>
</header>