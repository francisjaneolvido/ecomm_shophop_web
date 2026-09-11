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


            {{-- SEARCH — icon-only submit button instead of a text button --}}
            <div class="hidden md:flex flex-1 max-w-80 ml-auto">

                <form
                    action="#"
                    method="GET"
                    class="flex items-center w-full h-9 bg-gray-bg rounded-full pl-1 pr-1"
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
                        aria-label="Search"
                        class="inline-flex items-center justify-center
                               w-7 h-7 shrink-0
                               bg-teal hover:bg-teal-dark
                               text-white rounded-full transition"
                    >
                        <x-lucide-search class="w-3.5 h-3.5" />
                    </button>

                </form>

            </div>


            {{-- ACTIONS --}}
            <div class="ml-auto md:ml-0 flex items-center gap-1.5 shrink-0">

                {{-- WISHLIST — prompts login (data-login-required, same
                     hook used on the landing page's product cards) --}}
                <a
                    href="#"
                    data-login-required
                    title="Wishlist"
                    aria-label="Wishlist"
                    class="hidden sm:flex relative w-8 h-8 items-center justify-center
                           rounded-full text-navy
                           hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    <x-lucide-heart class="w-4.5 h-4.5" />
                </a>


                {{-- CART — prompts login --}}
                <a
                    href="#"
                    data-login-required
                    title="Shopping Cart"
                    aria-label="Shopping Cart"
                    class="relative w-8 h-8 flex items-center justify-center
                           rounded-full text-navy
                           hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    <x-lucide-shopping-cart class="w-4.5 h-4.5" />
                </a>


                {{-- ACCOUNT — quick sign-in for returning visitors --}}
                <a
                    href="{{ route('login') }}"
                    title="Sign In"
                    aria-label="Sign In"
                    class="hidden sm:flex w-8 h-8 items-center justify-center
                           rounded-full text-navy
                           hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    <x-lucide-user class="w-4.5 h-4.5" />
                </a>


                {{-- DIVIDER --}}
                <div class="hidden xl:block h-5 w-px bg-gray-border mx-1"></div>


                {{-- SIGN UP — this is the account.register route, so the
                     label now matches what it actually does (the icon
                     above already covers returning-user sign-in). --}}
                <a
                    href="{{ route('register') }}"
                    class="hidden xl:inline-flex items-center justify-center
                           bg-teal hover:bg-teal-dark
                           text-white text-[11px] font-semibold
                           px-3.5 py-2 rounded-full
                           transition whitespace-nowrap"
                >
                    Sign Up
                </a>


                {{-- MOBILE MENU --}}
                <button
                    type="button"
                    data-mobile-menu-toggle
                    aria-label="Open navigation menu"
                    aria-expanded="false"
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
                class="flex items-center w-full h-9 bg-gray-bg rounded-full pl-1 pr-1"
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
                    aria-label="Search"
                    class="inline-flex items-center justify-center
                           w-7 h-7 shrink-0
                           bg-teal hover:bg-teal-dark
                           text-white rounded-full transition"
                >
                    <x-lucide-search class="w-3.5 h-3.5" />
                </button>

            </form>

        </div>


        {{-- MOBILE NAVIGATION --}}
        <div data-mobile-menu-panel class="hidden xl:hidden pb-4 border-t border-gray-border pt-3">
            <nav class="flex flex-col gap-1 text-[13px] text-navy">
                <a href="{{ route('home') }}" class="px-3 py-2.5 rounded-lg bg-teal/15 text-teal-dark font-medium">Home</a>
                <a href="{{ route('home') }}#categories" class="px-3 py-2.5 rounded-lg hover:bg-gray-bg">Categories</a>
                <a href="{{ route('home') }}#deals" class="px-3 py-2.5 rounded-lg hover:bg-gray-bg">Deals</a>
                <a href="{{ route('home') }}#new-arrivals" class="px-3 py-2.5 rounded-lg hover:bg-gray-bg">New Arrivals</a>

                <div class="my-2 border-t border-gray-border"></div>

                <a href="#" data-login-required class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg hover:bg-gray-bg">
                    <x-lucide-heart class="w-4 h-4" />
                    Wishlist
                </a>

                <a href="#" data-login-required class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg hover:bg-gray-bg">
                    <x-lucide-shopping-cart class="w-4 h-4" />
                    Shopping Cart
                </a>

                <div class="my-2 border-t border-gray-border"></div>

                <a href="{{ route('login') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg border border-gray-border text-navy font-semibold justify-center">
                    <x-lucide-log-in class="w-4 h-4" />
                    Sign In
                </a>

                <a href="{{ route('register') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-teal hover:bg-teal-dark text-white font-semibold justify-center transition">
                    <x-lucide-user-plus class="w-4 h-4" />
                    Sign Up
                </a>
            </nav>
        </div>

    </div>

</header>

{{-- =============================================================
    NAVBAR SCRIPTS
============================================================= --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const mobileToggle = document.querySelector('[data-mobile-menu-toggle]');
    const mobilePanel = document.querySelector('[data-mobile-menu-panel]');

    if (mobileToggle && mobilePanel) {
        mobileToggle.addEventListener('click', function () {
            const isHidden = mobilePanel.classList.contains('hidden');
            mobilePanel.classList.toggle('hidden', !isHidden);
            mobileToggle.setAttribute('aria-expanded', String(isHidden));
        });
    }

});
</script>
@endpush