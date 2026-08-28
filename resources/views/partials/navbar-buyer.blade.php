{{-- Path in your project: resources/views/partials/navbar-buyer.blade.php --}}
{{-- Shown instead of partials.navbar once a buyer is logged in. --}}
<header class="bg-white border-b border-gray-border sticky top-0 z-50">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="h-14 flex items-center gap-6">

            {{-- LOGO --}}
            <a
                href="{{ route('buyer.dashboard') }}"
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
                    href="{{ route('buyer.dashboard') }}"
                    class="px-3.5 py-2 rounded-lg bg-teal/15 text-teal-dark font-medium"
                >
                    Home
                </a>

                <a
                    href="{{ route('buyer.dashboard') }}#categories"
                    class="px-3.5 py-2 rounded-lg hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    Categories
                </a>

                <a
                    href="{{ route('buyer.dashboard') }}#deals"
                    class="px-3.5 py-2 rounded-lg hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    Deals
                </a>

                <a
                    href="{{ route('buyer.dashboard') }}#new-arrivals"
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


                {{-- ORDERS --}}
                <a
                    href="#"
                    title="My Orders"
                    class="hidden sm:flex w-8 h-8 items-center justify-center
                           rounded-full text-navy
                           hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    <x-lucide-package class="w-4.5 h-4.5" />
                </a>


                {{-- CHAT / MESSAGES --}}
                <a
                    href="#"
                    title="Messages"
                    class="hidden sm:flex relative w-8 h-8 items-center justify-center
                           rounded-full text-navy
                           hover:bg-gray-bg hover:text-teal-dark transition"
                >
                    <x-lucide-message-circle class="w-4.5 h-4.5" />

                    <span
                        class="absolute -top-0.5 -right-0.5
                               min-w-3.5 h-3.5 px-1
                               flex items-center justify-center
                               rounded-full bg-teal
                               text-white text-[7px] font-bold"
                    >
                        1
                    </span>
                </a>


                {{-- DIVIDER --}}
                <div class="hidden xl:block h-5 w-px bg-gray-border mx-1"></div>


                {{-- ACCOUNT DROPDOWN --}}
                <div class="relative" data-account-menu>

                    <button
                        type="button"
                        data-account-menu-toggle
                        aria-haspopup="true"
                        aria-expanded="false"
                        title="Account"
                        class="flex items-center gap-1.5
                               h-8 pl-1 pr-2 sm:pr-2.5
                               rounded-full
                               text-navy
                               hover:bg-gray-bg hover:text-teal-dark
                               transition"
                    >
                        <span
                            class="w-6.5 h-6.5 rounded-full
                                   bg-teal/15 text-teal-dark
                                   flex items-center justify-center
                                   text-[11px] font-bold shrink-0"
                        >
                            {{ Str::of(Auth::user()?->first_name ?? 'B')->substr(0, 1)->upper() }}
                        </span>

                        <span class="hidden xl:block text-[11px] font-medium whitespace-nowrap">
                            {{ Auth::user()?->first_name ?? 'Buyer' }}
                        </span>

                        <x-lucide-chevron-down class="hidden xl:block w-3.5 h-3.5" />
                    </button>

                    <div
                        data-account-menu-panel
                        class="hidden absolute right-0 top-11
                               w-48
                               rounded-2xl
                               bg-white
                               border border-gray-border
                               shadow-xl shadow-navy/10
                               py-2
                               z-50"
                    >
                        <a
                            href="#"
                            class="flex items-center gap-2.5
                                   px-4 py-2.5
                                   text-[12px] text-navy
                                   hover:bg-gray-bg hover:text-teal-dark
                                   transition"
                        >
                            <x-lucide-user class="w-4 h-4" />
                            Account Management
                        </a>

                        <a
                            href="#"
                            class="flex items-center gap-2.5
                                   px-4 py-2.5
                                   text-[12px] text-navy
                                   hover:bg-gray-bg hover:text-teal-dark
                                   transition"
                        >
                            <x-lucide-package class="w-4 h-4" />
                            My Orders
                        </a>

                        <a
                            href="#"
                            class="flex items-center gap-2.5
                                   px-4 py-2.5
                                   text-[12px] text-navy
                                   hover:bg-gray-bg hover:text-teal-dark
                                   transition"
                        >
                            <x-lucide-message-circle class="w-4 h-4" />
                            Messages
                        </a>

                        <div class="my-1.5 border-t border-gray-border"></div>

                        <a
                            href="#"
                            class="flex items-center gap-2.5
                                   px-4 py-2.5
                                   text-[12px] text-red-600
                                   hover:bg-red-50
                                   transition"
                        >
                            <x-lucide-log-out class="w-4 h-4" />
                            Logout
                        </a>
                    </div>

                </div>


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


        {{-- MOBILE NAV / MENU PANEL --}}
        <div
            data-mobile-menu-panel
            class="hidden xl:hidden pb-4 border-t border-gray-border pt-3"
        >
            <nav class="flex flex-col gap-1 text-[13px] text-navy">

                <a href="{{ route('buyer.dashboard') }}" class="px-3 py-2.5 rounded-lg bg-teal/15 text-teal-dark font-medium">Home</a>
                <a href="{{ route('buyer.dashboard') }}#categories" class="px-3 py-2.5 rounded-lg hover:bg-gray-bg">Categories</a>
                <a href="{{ route('buyer.dashboard') }}#deals" class="px-3 py-2.5 rounded-lg hover:bg-gray-bg">Deals</a>
                <a href="{{ route('buyer.dashboard') }}#new-arrivals" class="px-3 py-2.5 rounded-lg hover:bg-gray-bg">New Arrivals</a>

                <div class="my-2 border-t border-gray-border"></div>

                <a href="#" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg hover:bg-gray-bg">
                    <x-lucide-heart class="w-4 h-4" /> Wishlist
                </a>
                <a href="#" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg hover:bg-gray-bg">
                    <x-lucide-package class="w-4 h-4" /> My Orders
                </a>
                <a href="#" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg hover:bg-gray-bg">
                    <x-lucide-message-circle class="w-4 h-4" /> Messages
                </a>
                <a href="#" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg hover:bg-gray-bg">
                    <x-lucide-user class="w-4 h-4" /> Account Management
                </a>
                <a href="#" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50">
                    <x-lucide-log-out class="w-4 h-4" /> Logout
                </a>

            </nav>
        </div>

    </div>

</header>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ----- Account dropdown -----
        document.querySelectorAll('[data-account-menu]').forEach(function (menu) {
            const toggle = menu.querySelector('[data-account-menu-toggle]');
            const panel = menu.querySelector('[data-account-menu-panel]');

            if (!toggle || !panel) return;

            function closeMenu() {
                panel.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
            }

            function openMenu() {
                panel.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            }

            toggle.addEventListener('click', function (event) {
                event.stopPropagation();
                const isOpen = !panel.classList.contains('hidden');
                isOpen ? closeMenu() : openMenu();
            });

            document.addEventListener('click', function (event) {
                if (!menu.contains(event.target)) closeMenu();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeMenu();
            });
        });

        // ----- Mobile nav panel -----
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