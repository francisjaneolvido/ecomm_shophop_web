{{-- Path in your project: resources/views/partials/footer.blade.php --}}
<footer class="bg-navy text-gray-300">
    <div class="max-w-310 mx-auto px-6 pt-14 grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr] gap-10 pb-10">

        <div>
            <div class="font-serif text-2xl text-white mb-3">ShopHop</div>
            <p class="text-sm max-w-xs">Your everyday marketplace for everything you love.</p>
            <div class="flex gap-4 mt-5">
                <a href="#" class="text-gray-300 hover:text-teal transition-colors"><x-lucide-facebook class="w-5 h-5" /></a>
                <a href="#" class="text-gray-300 hover:text-teal transition-colors"><x-lucide-instagram class="w-5 h-5" /></a>
                <a href="#" class="text-gray-300 hover:text-teal transition-colors"><x-lucide-twitter class="w-5 h-5" /></a>
            </div>
        </div>

        <div>
            <h4 class="text-white font-semibold mb-3">Shop</h4>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">All Products</a>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">Categories</a>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">Deals</a>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">New Arrivals</a>
        </div>

        <div>
            <h4 class="text-white font-semibold mb-3">Help</h4>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">Customer Support</a>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">Shipping & Delivery</a>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">Returns & Refunds</a>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">FAQs</a>
        </div>

        <div>
            <h4 class="text-white font-semibold mb-3">Company</h4>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">About ShopHop</a>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">Contact Us</a>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">Terms & Conditions</a>
            <a href="#" class="block text-sm mb-2 hover:text-teal transition-colors">Privacy Policy</a>
        </div>

    </div>

    <div class="border-t border-white/10 text-center text-xs py-5">
        &copy; {{ date('Y') }} ShopHop. All rights reserved.
    </div>
</footer>