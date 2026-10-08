{{-- Path: resources/views/partials/footer.blade.php --}}

<footer class="bg-[#ebeef0] text-navy">

    {{-- =====================================================
        MAIN FOOTER
    ====================================================== --}}
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 pt-12 sm:pt-14 pb-8 sm:pb-10">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_1fr] gap-8 lg:gap-10">

            {{-- =================================================
                BRAND
            ================================================== --}}
            <div class="sm:col-span-2 lg:col-span-1">

                {{-- LOGO --}}
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center gap-2 mb-4"
                >

                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="ShopHop"
                        class="w-10 h-10 object-contain"
                    >

                    <div class="leading-none">

                        <span class="block font-bold text-[17px] text-navy">
                            ShopHop
                        </span>

                        <span class="block text-[7px] tracking-wide text-teal-dark mt-1">
                            HOP IN. SHOP MORE.
                        </span>

                    </div>

                </a>


                {{-- DESCRIPTION --}}
                <p class="text-sm leading-6 max-w-sm text-navy/60">
                    Your everyday marketplace for everything you love.
                    Discover great finds, everyday essentials, and more —
                    all just a hop away.
                </p>


                {{-- No social destinations are configured, so icons cannot imply links to marketplace accounts. --}}
                <p class="text-xs text-navy/60 mt-5">Social links unavailable.</p>

            </div>


            {{-- =================================================
                SHOP
            ================================================== --}}
            <div>

                <h4 class="text-navy font-semibold text-sm mb-4">
                    Shop
                </h4>

                {{-- Unsupported destinations stay non-interactive rather than linking to dead placeholders. --}}
                <div class="space-y-3">

                    <span data-footer-unavailable class="block text-sm text-navy/60">All Products (unavailable)</span>

                    <a
                        href="{{ route('home') }}#categories"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        Categories
                    </a>

                    <a
                        href="{{ route('home') }}#deals"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        Deals
                    </a>

                    <a
                        href="{{ route('home') }}#new-arrivals"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        New Arrivals
                    </a>

                </div>

            </div>


            {{-- =================================================
                HELP
            ================================================== --}}
            <div>

                <h4 class="text-navy font-semibold text-sm mb-4">
                    Help
                    <span class="text-xs font-normal text-navy/60">(unavailable)</span>
                </h4>

                {{-- Unsupported destinations stay non-interactive rather than linking to dead placeholders. --}}
                <div class="space-y-3">

                    <span data-footer-unavailable class="block text-sm text-navy/60">Customer Support</span>

                    <span data-footer-unavailable class="block text-sm text-navy/60">Shipping & Delivery</span>

                    <span data-footer-unavailable class="block text-sm text-navy/60">Returns & Refunds</span>

                    <span data-footer-unavailable class="block text-sm text-navy/60">FAQs</span>

                </div>

            </div>


            {{-- =================================================
                COMPANY
            ================================================== --}}
            <div>

                <h4 class="text-navy font-semibold text-sm mb-4">
                    Company
                    <span class="text-xs font-normal text-navy/60">(unavailable)</span>
                </h4>

                {{-- Unsupported destinations stay non-interactive rather than linking to dead placeholders. --}}
                <div class="space-y-3">

                    <span data-footer-unavailable class="block text-sm text-navy/60">About ShopHop</span>

                    <span data-footer-unavailable class="block text-sm text-navy/60">Contact Us</span>

                    {{-- Logistics has its own site and registration, separate from Buyer/Seller. --}}
                    <a href="{{ route('logistics.home') }}"
                       class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200">
                        Become a Logistics Partner ↗
                    </a>

                    <span data-footer-unavailable class="block text-sm text-navy/60">Terms & Conditions</span>

                    <span data-footer-unavailable class="block text-sm text-navy/60">Privacy Policy</span>

                </div>

            </div>

        </div>


        {{-- =====================================================
            FOOTER DIVIDER
        ====================================================== --}}
        <div class="border-t border-navy/10 mt-10 sm:mt-12 pt-6">

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 text-center sm:text-left">

                {{-- COPYRIGHT --}}
                <p class="text-xs text-navy/50">
                    &copy; {{ date('Y') }} ShopHop. All rights reserved.
                </p>


                {{-- SMALL BRAND MESSAGE --}}
                <div class="flex items-center justify-center gap-2 text-xs text-navy/50">

                    <span>
                        Hop in.
                    </span>

                    <span class="text-teal font-semibold">
                        Shop more.
                    </span>

                </div>

            </div>

        </div>

    </div>

</footer>