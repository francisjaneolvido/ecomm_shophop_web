{{-- Path in your project: resources/views/partials/footer.blade.php --}}

<footer class="bg-[#f3f0f0] text-navy">

    <div class="max-w-310 mx-auto px-6 pt-14 grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr] gap-10 pb-10">

        {{-- BRAND --}}
        <div>
            <div class="font-serif text-2xl text-navy mb-3">
                ShopHop
            </div>

            <p class="text-sm max-w-xs text-navy/65">
                Your everyday marketplace for everything you love.
            </p>

            {{-- SOCIAL MEDIA --}}
            <div class="flex gap-4 mt-5">

                <a
                    href="#"
                    title="Facebook"
                    class="text-navy/60 hover:text-teal transition-colors"
                >
                    <x-lucide-facebook class="w-5 h-5" />
                </a>

                <a
                    href="#"
                    title="Instagram"
                    class="text-navy/60 hover:text-teal transition-colors"
                >
                    <x-lucide-instagram class="w-5 h-5" />
                </a>

                <a
                    href="#"
                    title="Twitter"
                    class="text-navy/60 hover:text-teal transition-colors"
                >
                    <x-lucide-twitter class="w-5 h-5" />
                </a>

            </div>
        </div>


        {{-- SHOP --}}
        <div>
            <h4 class="text-navy font-semibold mb-3">
                Shop
            </h4>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                All Products
            </a>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                Categories
            </a>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                Deals
            </a>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                New Arrivals
            </a>
        </div>


        {{-- HELP --}}
        <div>
            <h4 class="text-navy font-semibold mb-3">
                Help
            </h4>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                Customer Support
            </a>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                Shipping & Delivery
            </a>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                Returns & Refunds
            </a>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                FAQs
            </a>
        </div>


        {{-- COMPANY --}}
        <div>
            <h4 class="text-navy font-semibold mb-3">
                Company
            </h4>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                About ShopHop
            </a>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                Contact Us
            </a>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                Terms & Conditions
            </a>

            <a
                href="#"
                class="block text-sm mb-2 text-navy/65 hover:text-teal-dark transition-colors"
            >
                Privacy Policy
            </a>
        </div>

    </div>


    {{-- COPYRIGHT --}}
    <div class="border-t border-navy/10 text-center text-xs py-5 text-navy/55">
        &copy; {{ date('Y') }} ShopHop. All rights reserved.
    </div>

</footer>