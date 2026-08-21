{{-- Path: resources/views/partials/footer.blade.php --}}

<footer class="bg-[#F0EFED] text-navy">

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


                {{-- SOCIAL MEDIA --}}
                <div class="flex items-center gap-3 mt-5">

                    {{-- FACEBOOK --}}
                    <a
                        href="#"
                        title="Facebook"
                        aria-label="Facebook"
                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white text-navy/60 hover:bg-teal hover:text-white transition-all duration-200"
                    >
                        <x-lucide-facebook class="w-4 h-4" />
                    </a>


                    {{-- INSTAGRAM --}}
                    <a
                        href="#"
                        title="Instagram"
                        aria-label="Instagram"
                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white text-navy/60 hover:bg-teal hover:text-white transition-all duration-200"
                    >
                        <x-lucide-instagram class="w-4 h-4" />
                    </a>


                    {{-- TWITTER / X --}}
                    <a
                        href="#"
                        title="Twitter"
                        aria-label="Twitter"
                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white text-navy/60 hover:bg-teal hover:text-white transition-all duration-200"
                    >
                        <x-lucide-twitter class="w-4 h-4" />
                    </a>

                </div>

            </div>


            {{-- =================================================
                SHOP
            ================================================== --}}
            <div>

                <h4 class="text-navy font-semibold text-sm mb-4">
                    Shop
                </h4>

                <div class="space-y-3">

                    <a
                        href="#"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        All Products
                    </a>

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
                </h4>

                <div class="space-y-3">

                    <a
                        href="#"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        Customer Support
                    </a>

                    <a
                        href="#"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        Shipping & Delivery
                    </a>

                    <a
                        href="#"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        Returns & Refunds
                    </a>

                    <a
                        href="#"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        FAQs
                    </a>

                </div>

            </div>


            {{-- =================================================
                COMPANY
            ================================================== --}}
            <div>

                <h4 class="text-navy font-semibold text-sm mb-4">
                    Company
                </h4>

                <div class="space-y-3">

                    <a
                        href="#"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        About ShopHop
                    </a>

                    <a
                        href="#"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        Contact Us
                    </a>

                    <a
                        href="#"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        Terms & Conditions
                    </a>

                    <a
                        href="#"
                        class="block text-sm text-navy/60 hover:text-teal-dark transition-colors duration-200"
                    >
                        Privacy Policy
                    </a>

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