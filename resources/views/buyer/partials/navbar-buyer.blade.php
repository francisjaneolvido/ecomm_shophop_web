{{-- Path: resources/views/buyer/partials/navbar-buyer.blade.php --}}

@php
    $buyer = auth()->user();

    $buyerName = $buyer?->first_name ?? 'Buyer';
    $buyerInitial = strtoupper(substr($buyerName, 0, 1));

    /*
    |--------------------------------------------------------------------------
    | TEMPORARY NOTIFICATIONS
    |--------------------------------------------------------------------------
    | Later pwede itong manggaling sa database.
    */

    $notifications = [
        [
            'title' => 'Order shipped',
            'message' => 'Your Wireless Earbuds Pro is on the way.',
            'time' => '5 min ago',
            'icon' => 'truck',
            'unread' => true,
        ],
        [
            'title' => 'New voucher available',
            'message' => 'You received a ₱100 ShopHop voucher.',
            'time' => '1 hour ago',
            'icon' => 'ticket',
            'unread' => true,
        ],
        [
            'title' => 'Price dropped',
            'message' => 'An item from your wishlist is now cheaper.',
            'time' => '3 hours ago',
            'icon' => 'badge-percent',
            'unread' => true,
        ],
    ];

    $notificationCount = collect($notifications)
        ->where('unread', true)
        ->count();
@endphp


<header class="bg-white border-b border-gray-border sticky top-0 z-50">

    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">

        <div class="h-14 flex items-center gap-6">


            {{-- =========================================================
                LOGO
            ========================================================= --}}
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


            {{-- =========================================================
                DESKTOP NAVIGATION
            ========================================================= --}}
            <nav
                class="hidden xl:flex items-center gap-1
                       text-[12px] text-navy
                       shrink-0 ml-2"
            >

                <a
                    href="{{ route('buyer.dashboard') }}"
                    class="px-3.5 py-2
                           rounded-lg
                           bg-teal/15
                           text-teal-dark
                           font-medium"
                >
                    Home
                </a>


                <a
                    href="{{ route('buyer.dashboard') }}#categories"
                    class="px-3.5 py-2
                           rounded-lg
                           hover:bg-gray-bg
                           hover:text-teal-dark
                           transition"
                >
                    Categories
                </a>


                <a
                    href="{{ route('buyer.dashboard') }}#deals"
                    class="px-3.5 py-2
                           rounded-lg
                           hover:bg-gray-bg
                           hover:text-teal-dark
                           transition"
                >
                    Deals
                </a>


                <a
                    href="{{ route('buyer.dashboard') }}#new-arrivals"
                    class="px-3.5 py-2
                           rounded-lg
                           hover:bg-gray-bg
                           hover:text-teal-dark
                           transition
                           whitespace-nowrap"
                >
                    New Arrivals
                </a>

            </nav>


            {{-- =========================================================
                SEARCH
            ========================================================= --}}
            <div class="hidden md:flex flex-1 max-w-80 ml-auto">

                <form
                    action="#"
                    method="GET"
                    class="flex items-center
                           w-full h-9
                           bg-gray-bg
                           rounded-full
                           px-1"
                >

                    <div class="flex items-center gap-2 px-3 flex-1 min-w-0">

                        <x-lucide-search
                            class="w-4 h-4 text-navy/35 shrink-0"
                        />

                        <input
                            type="text"
                            name="search"
                            placeholder="Search products"
                            class="bg-transparent
                                   border-0 outline-none
                                   focus:ring-0
                                   w-full min-w-0 p-0
                                   text-[12px] text-navy
                                   placeholder:text-navy/35"
                        >

                    </div>


                    <button
                        type="submit"
                        class="hidden lg:inline-flex
                               items-center justify-center
                               h-7
                               bg-teal
                               hover:bg-teal-dark
                               text-white
                               text-[11px]
                               font-semibold
                               px-4
                               rounded-full
                               transition"
                    >
                        Search
                    </button>

                </form>

            </div>


            {{-- =========================================================
                ACTION ICONS
            ========================================================= --}}
            <div class="ml-auto md:ml-0 flex items-center gap-1.5 shrink-0">


                {{-- =====================================================
                    WISHLIST
                ====================================================== --}}
                <a
                    href="#"
                    title="Wishlist"
                    aria-label="Wishlist"
                    class="hidden sm:flex
                           relative
                           w-8 h-8
                           items-center justify-center
                           rounded-full
                           text-navy
                           hover:bg-gray-bg
                           hover:text-teal-dark
                           transition"
                >

                    <x-lucide-heart class="w-4.5 h-4.5" />

                    {{-- TEMP count --}}
                    <span
                        class="absolute
                               -top-0.5 -right-0.5
                               min-w-3.5 h-3.5 px-1
                               flex items-center justify-center
                               rounded-full
                               bg-teal
                               text-white
                               text-[7px]
                               font-bold"
                    >
                        2
                    </span>

                </a>


                {{-- =====================================================
                    CART
                ====================================================== --}}
                <a
                    href="#"
                    title="Shopping Cart"
                    aria-label="Shopping Cart"
                    class="relative
                           w-8 h-8
                           flex items-center justify-center
                           rounded-full
                           text-navy
                           hover:bg-gray-bg
                           hover:text-teal-dark
                           transition"
                >

                    <x-lucide-shopping-cart class="w-4.5 h-4.5" />

                    {{-- TEMP count --}}
                    <span
                        class="absolute
                               -top-0.5 -right-0.5
                               min-w-3.5 h-3.5 px-1
                               flex items-center justify-center
                               rounded-full
                               bg-teal
                               text-white
                               text-[7px]
                               font-bold"
                    >
                        3
                    </span>

                </a>


                {{-- =====================================================
                    NOTIFICATIONS
                ====================================================== --}}
                <div
                    class="relative"
                    data-hover-menu
                    data-notification-menu
                >

                    <button
                        type="button"
                        data-hover-menu-toggle
                        aria-haspopup="true"
                        aria-expanded="false"
                        title="Notifications"
                        aria-label="Notifications"
                        class="relative
                               w-8 h-8
                               flex items-center justify-center
                               rounded-full
                               text-navy
                               hover:bg-gray-bg
                               hover:text-teal-dark
                               transition"
                    >

                        <x-lucide-bell class="w-4.5 h-4.5" />


                        @if ($notificationCount > 0)

                            <span
                                data-notification-badge
                                class="absolute
                                       -top-0.5 -right-0.5
                                       min-w-3.5 h-3.5 px-1
                                       flex items-center justify-center
                                       rounded-full
                                       bg-teal
                                       text-white
                                       text-[7px]
                                       font-bold"
                            >
                                {{ $notificationCount }}
                            </span>

                        @endif

                    </button>


                    {{-- NOTIFICATION DROPDOWN WRAPPER --}}
                    <div
                        data-hover-menu-panel
                        class="hidden
                               absolute
                               right-0
                               top-full
                               pt-2
                               z-60
                               w-80
                               max-w-[calc(100vw-2rem)]"
                    >

                        <div
                            class="bg-white
                                   border border-gray-border
                                   rounded-2xl
                                   shadow-xl shadow-navy/10
                                   overflow-hidden"
                        >

                            {{-- Header --}}
                            <div
                                class="flex items-center justify-between
                                       px-4 py-3
                                       border-b border-gray-border"
                            >

                                <div>

                                    <p class="text-[13px] font-bold text-navy">
                                        Notifications
                                    </p>

                                    <p
                                        data-notification-summary
                                        class="text-[10px] text-navy/45 mt-0.5"
                                    >
                                        {{ $notificationCount }} unread
                                    </p>

                                </div>


                                <button
                                    type="button"
                                    data-mark-all-read
                                    class="text-[10px]
                                           font-semibold
                                           text-teal-dark
                                           hover:text-teal
                                           transition"
                                >
                                    Mark all as read
                                </button>

                            </div>


                            {{-- Notification List --}}
                            <div class="max-h-80 overflow-y-auto">

                                @forelse ($notifications as $index => $notification)

                                    <button
                                        type="button"
                                        data-notification-item
                                        class="w-full text-left
                                               flex items-start gap-3
                                               px-4 py-3.5
                                               border-b border-gray-border
                                               last:border-b-0
                                               {{ $notification['unread'] ? 'bg-teal-light/25' : 'bg-white' }}
                                               hover:bg-gray-bg
                                               transition"
                                    >

                                        {{-- Icon --}}
                                        <div
                                            class="w-9 h-9
                                                   rounded-xl
                                                   bg-teal-light
                                                   text-teal-dark
                                                   flex items-center justify-center
                                                   shrink-0"
                                        >

                                            @if ($notification['icon'] === 'truck')

                                                <x-lucide-truck class="w-4 h-4" />

                                            @elseif ($notification['icon'] === 'ticket')

                                                <x-lucide-ticket class="w-4 h-4" />

                                            @else

                                                <x-lucide-badge-percent class="w-4 h-4" />

                                            @endif

                                        </div>


                                        {{-- Text --}}
                                        <div class="flex-1 min-w-0">

                                            <div class="flex items-start gap-2">

                                                <p class="text-[11px] font-semibold text-navy flex-1">
                                                    {{ $notification['title'] }}
                                                </p>


                                                @if ($notification['unread'])

                                                    <span
                                                        data-unread-dot
                                                        class="w-1.5 h-1.5
                                                               mt-1
                                                               rounded-full
                                                               bg-teal
                                                               shrink-0"
                                                    ></span>

                                                @endif

                                            </div>


                                            <p class="text-[10px] text-navy/55 leading-relaxed mt-0.5">
                                                {{ $notification['message'] }}
                                            </p>


                                            <p class="text-[9px] text-navy/35 mt-1">
                                                {{ $notification['time'] }}
                                            </p>

                                        </div>

                                    </button>

                                @empty

                                    <div class="px-4 py-8 text-center">

                                        <x-lucide-bell-off
                                            class="w-7 h-7 text-navy/25 mx-auto mb-2"
                                        />

                                        <p class="text-[11px] text-navy/45">
                                            No notifications yet.
                                        </p>

                                    </div>

                                @endforelse

                            </div>


                            {{-- Footer --}}
                            <a
                                href="#"
                                class="flex items-center justify-center
                                       gap-1.5
                                       px-4 py-3
                                       border-t border-gray-border
                                       text-[11px]
                                       font-semibold
                                       text-teal-dark
                                       hover:bg-gray-bg
                                       transition"
                            >
                                View all notifications

                                <x-lucide-chevron-right class="w-3.5 h-3.5" />

                            </a>

                        </div>

                    </div>

                </div>


                {{-- DIVIDER --}}
                <div class="hidden xl:block h-5 w-px bg-gray-border mx-1"></div>


                {{-- =====================================================
                    ACCOUNT DROPDOWN
                ====================================================== --}}
                <div
                    class="relative"
                    data-hover-menu
                    data-account-menu
                >

                    <button
                        type="button"
                        data-hover-menu-toggle
                        aria-haspopup="true"
                        aria-expanded="false"
                        title="Account"
                        class="flex items-center gap-1.5
                               h-8
                               pl-1 pr-2
                               sm:pr-2.5
                               rounded-full
                               text-navy
                               hover:bg-gray-bg
                               hover:text-teal-dark
                               transition"
                    >

                        {{-- Avatar / Initial --}}
                        <span
                            class="w-6.5 h-6.5
                                   rounded-full
                                   bg-teal/15
                                   text-teal-dark
                                   flex items-center justify-center
                                   text-[11px]
                                   font-bold
                                   shrink-0"
                        >
                            {{ $buyerInitial }}
                        </span>


                        {{-- Name --}}
                        <span
                            class="hidden xl:block
                                   text-[11px]
                                   font-medium
                                   whitespace-nowrap"
                        >
                            {{ $buyerName }}
                        </span>


                        <x-lucide-chevron-down
                            class="hidden xl:block
                                   w-3.5 h-3.5
                                   transition-transform"
                        />

                    </button>


                    {{-- ACCOUNT DROPDOWN WRAPPER --}}
                    <div
                        data-hover-menu-panel
                        class="hidden
                               absolute
                               right-0
                               top-full
                               pt-2
                               z-60
                               w-48"
                    >

                        <div
                            class="rounded-2xl
                                   bg-white
                                   border border-gray-border
                                   shadow-xl shadow-navy/10
                                   py-2"
                        >


                            {{-- ACCOUNT MANAGEMENT --}}
                            <a
                                href="{{ Route::has('buyer.profile') ? route('buyer.profile') : '#' }}"
                                class="flex items-center gap-2.5
                                       px-4 py-2.5
                                       text-[12px] text-navy
                                       hover:bg-gray-bg
                                       hover:text-teal-dark
                                       transition"
                            >

                                <x-lucide-user class="w-4 h-4" />

                                <span>
                                    Account Management
                                </span>

                            </a>


                            {{-- MY ORDERS --}}
                            <a
                                href="#"
                                class="flex items-center gap-2.5
                                       px-4 py-2.5
                                       text-[12px] text-navy
                                       hover:bg-gray-bg
                                       hover:text-teal-dark
                                       transition"
                            >

                                <x-lucide-package class="w-4 h-4" />

                                My Orders

                            </a>


                            {{-- MESSAGES --}}
                            <a
                                href="#"
                                class="flex items-center gap-2.5
                                       px-4 py-2.5
                                       text-[12px] text-navy
                                       hover:bg-gray-bg
                                       hover:text-teal-dark
                                       transition"
                            >

                                <x-lucide-message-circle class="w-4 h-4" />

                                Messages

                            </a>


                            <div class="my-1.5 border-t border-gray-border"></div>


                            {{-- LOGOUT --}}
                            <a
                                href="{{ Route::has('logout') ? route('logout') : '#' }}"
                                class="flex items-center gap-2.5
                                       px-4 py-2.5
                                       text-[12px]
                                       text-red-600
                                       hover:bg-red-50
                                       transition"
                            >

                                <x-lucide-log-out class="w-4 h-4" />

                                Logout

                            </a>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                    MOBILE MENU BUTTON
                ====================================================== --}}
                <button
                    type="button"
                    data-mobile-menu-toggle
                    aria-label="Open navigation menu"
                    aria-expanded="false"
                    class="xl:hidden
                           w-8 h-8
                           flex items-center justify-center
                           rounded-full
                           text-navy
                           hover:bg-gray-bg
                           hover:text-teal-dark
                           transition"
                >

                    <x-lucide-menu class="w-4.5 h-4.5" />

                </button>


            </div>

        </div>


        {{-- =========================================================
            MOBILE SEARCH
        ========================================================= --}}
        <div class="md:hidden pb-3">

            <form
                action="#"
                method="GET"
                class="flex items-center
                       w-full h-9
                       bg-gray-bg
                       rounded-full
                       px-1"
            >

                <div class="flex items-center gap-2 px-3 flex-1 min-w-0">

                    <x-lucide-search
                        class="w-4 h-4 text-navy/35 shrink-0"
                    />

                    <input
                        type="text"
                        name="search"
                        placeholder="Search products"
                        class="bg-transparent
                               border-0 outline-none
                               focus:ring-0
                               w-full min-w-0 p-0
                               text-[12px] text-navy
                               placeholder:text-navy/35"
                    >

                </div>


                <button
                    type="submit"
                    class="h-7
                           bg-teal
                           hover:bg-teal-dark
                           text-white
                           text-[11px]
                           font-semibold
                           px-4
                           rounded-full
                           transition"
                >
                    Search
                </button>

            </form>

        </div>


        {{-- =========================================================
            MOBILE NAVIGATION
        ========================================================= --}}
        <div
            data-mobile-menu-panel
            class="hidden
                   xl:hidden
                   pb-4
                   border-t border-gray-border
                   pt-3"
        >

            <nav class="flex flex-col gap-1 text-[13px] text-navy">


                <a
                    href="{{ route('buyer.dashboard') }}"
                    class="px-3 py-2.5
                           rounded-lg
                           bg-teal/15
                           text-teal-dark
                           font-medium"
                >
                    Home
                </a>


                <a
                    href="{{ route('buyer.dashboard') }}#categories"
                    class="px-3 py-2.5 rounded-lg hover:bg-gray-bg"
                >
                    Categories
                </a>


                <a
                    href="{{ route('buyer.dashboard') }}#deals"
                    class="px-3 py-2.5 rounded-lg hover:bg-gray-bg"
                >
                    Deals
                </a>


                <a
                    href="{{ route('buyer.dashboard') }}#new-arrivals"
                    class="px-3 py-2.5 rounded-lg hover:bg-gray-bg"
                >
                    New Arrivals
                </a>


                <div class="my-2 border-t border-gray-border"></div>


                <a
                    href="#"
                    class="flex items-center gap-2.5
                           px-3 py-2.5
                           rounded-lg
                           hover:bg-gray-bg"
                >

                    <x-lucide-heart class="w-4 h-4" />

                    Wishlist

                </a>


                <a
                    href="#"
                    class="flex items-center gap-2.5
                           px-3 py-2.5
                           rounded-lg
                           hover:bg-gray-bg"
                >

                    <x-lucide-shopping-cart class="w-4 h-4" />

                    Shopping Cart

                </a>


                <a
                    href="#"
                    class="flex items-center gap-2.5
                           px-3 py-2.5
                           rounded-lg
                           hover:bg-gray-bg"
                >

                    <x-lucide-bell class="w-4 h-4" />

                    Notifications

                    @if ($notificationCount > 0)

                        <span
                            class="ml-auto
                                   min-w-5 h-5
                                   px-1.5
                                   rounded-full
                                   bg-teal
                                   text-white
                                   text-[9px]
                                   font-bold
                                   flex items-center justify-center"
                        >
                            {{ $notificationCount }}
                        </span>

                    @endif

                </a>


                <a
                    href="#"
                    class="flex items-center gap-2.5
                           px-3 py-2.5
                           rounded-lg
                           hover:bg-gray-bg"
                >

                    <x-lucide-package class="w-4 h-4" />

                    My Orders

                </a>


                <a
                    href="#"
                    class="flex items-center gap-2.5
                           px-3 py-2.5
                           rounded-lg
                           hover:bg-gray-bg"
                >

                    <x-lucide-message-circle class="w-4 h-4" />

                    Messages

                </a>


                <a
                    href="{{ Route::has('buyer.profile') ? route('buyer.profile') : '#' }}"
                    class="flex items-center gap-2.5
                           px-3 py-2.5
                           rounded-lg
                           hover:bg-gray-bg"
                >

                    <x-lucide-user class="w-4 h-4" />

                    Account Management

                </a>


                <a
                    href="{{ Route::has('logout') ? route('logout') : '#' }}"
                    class="flex items-center gap-2.5
                           px-3 py-2.5
                           rounded-lg
                           text-red-600
                           hover:bg-red-50"
                >

                    <x-lucide-log-out class="w-4 h-4" />

                    Logout

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

    /*
    |--------------------------------------------------------------------------
    | HOVER + CLICK DROPDOWNS
    |--------------------------------------------------------------------------
    |
    | Desktop:
    | Hovering automatically opens the dropdown.
    |
    | Mobile/touch:
    | Clicking still opens/closes the dropdown.
    |
    */

    const hoverMenus = document.querySelectorAll('[data-hover-menu]');

    hoverMenus.forEach(function (menu) {

        const toggle = menu.querySelector('[data-hover-menu-toggle]');
        const panel = menu.querySelector('[data-hover-menu-panel]');

        if (!toggle || !panel) {
            return;
        }


        let closeTimer = null;


        function openMenu() {

            if (closeTimer) {
                clearTimeout(closeTimer);
            }

            /*
             * Close other dropdowns first.
             */
            hoverMenus.forEach(function (otherMenu) {

                if (otherMenu === menu) {
                    return;
                }

                const otherToggle = otherMenu.querySelector(
                    '[data-hover-menu-toggle]'
                );

                const otherPanel = otherMenu.querySelector(
                    '[data-hover-menu-panel]'
                );

                if (otherPanel) {
                    otherPanel.classList.add('hidden');
                }

                if (otherToggle) {
                    otherToggle.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }

            });


            panel.classList.remove('hidden');

            toggle.setAttribute(
                'aria-expanded',
                'true'
            );

        }


        function closeMenu() {

            panel.classList.add('hidden');

            toggle.setAttribute(
                'aria-expanded',
                'false'
            );

        }


        function closeMenuDelayed() {

            closeTimer = setTimeout(function () {
                closeMenu();
            }, 160);

        }


        /*
         * Desktop hover
         */
        menu.addEventListener('mouseenter', function () {
            openMenu();
        });


        menu.addEventListener('mouseleave', function () {
            closeMenuDelayed();
        });


        /*
         * Keyboard accessibility
         */
        menu.addEventListener('focusin', function () {
            openMenu();
        });


        menu.addEventListener('focusout', function (event) {

            if (!menu.contains(event.relatedTarget)) {
                closeMenuDelayed();
            }

        });


        /*
         * Click support for touch screens.
         */
        toggle.addEventListener('click', function (event) {

            event.stopPropagation();

            const isOpen =
                !panel.classList.contains('hidden');

            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }

        });


        /*
         * Don't close when clicking inside panel.
         */
        panel.addEventListener('click', function (event) {
            event.stopPropagation();
        });


        /*
         * Escape key closes.
         */
        document.addEventListener('keydown', function (event) {

            if (event.key === 'Escape') {
                closeMenu();
            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | CLICK OUTSIDE
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function () {

        hoverMenus.forEach(function (menu) {

            const toggle = menu.querySelector(
                '[data-hover-menu-toggle]'
            );

            const panel = menu.querySelector(
                '[data-hover-menu-panel]'
            );

            if (panel) {
                panel.classList.add('hidden');
            }

            if (toggle) {

                toggle.setAttribute(
                    'aria-expanded',
                    'false'
                );

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | MARK ALL NOTIFICATIONS AS READ
    |--------------------------------------------------------------------------
    */

    const markAllReadButton =
        document.querySelector('[data-mark-all-read]');

    const notificationBadge =
        document.querySelector('[data-notification-badge]');

    const notificationSummary =
        document.querySelector('[data-notification-summary]');


    if (markAllReadButton) {

        markAllReadButton.addEventListener('click', function () {

            document
                .querySelectorAll('[data-notification-item]')
                .forEach(function (item) {

                    item.classList.remove(
                        'bg-teal-light/25'
                    );

                    item.classList.add(
                        'bg-white'
                    );

                });


            document
                .querySelectorAll('[data-unread-dot]')
                .forEach(function (dot) {
                    dot.remove();
                });


            if (notificationBadge) {
                notificationBadge.remove();
            }


            if (notificationSummary) {
                notificationSummary.textContent =
                    'You’re all caught up';
            }


            markAllReadButton.textContent = 'All read';

            markAllReadButton.disabled = true;

            markAllReadButton.classList.add(
                'opacity-50',
                'cursor-default'
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | MOBILE NAVIGATION
    |--------------------------------------------------------------------------
    */

    const mobileToggle =
        document.querySelector(
            '[data-mobile-menu-toggle]'
        );

    const mobilePanel =
        document.querySelector(
            '[data-mobile-menu-panel]'
        );


    if (mobileToggle && mobilePanel) {

        mobileToggle.addEventListener('click', function () {

            const isHidden =
                mobilePanel.classList.contains('hidden');


            mobilePanel.classList.toggle(
                'hidden',
                !isHidden
            );


            mobileToggle.setAttribute(
                'aria-expanded',
                String(isHidden)
            );

        });

    }

});
</script>

@endpush