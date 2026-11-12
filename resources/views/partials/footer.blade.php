{{-- Unified, compact ShopHop shopper footer used by guest and Buyer pages. --}}
@php
    $footerBuyer = auth()->user()?->account_type === 'buyer';
    $footerHome = $footerBuyer && \Illuminate\Support\Facades\Route::has('buyer.dashboard')
        ? route('buyer.dashboard') : route('home');
@endphp
<footer class="sh-shop-footer" role="contentinfo">
    <div class="sh-shop-shell">
        <div class="sh-shop-footer-main">
            <div class="sh-shop-footer-brand">
                <a href="{{ $footerHome }}" class="sh-shop-footer-brand-link">
                    <img src="{{ asset('images/logo-bunny.png') }}" width="34" height="34" alt="ShopHop logo">
                    <span><strong>Shop<span class="sh-brand-hop">Hop</span></strong><small>HOP IN. SHOP MORE.</small></span>
                </a>
                <p>Find everyday essentials, trending picks, and useful deals from shops in one marketplace.</p>
                <span class="sh-shop-footer-tagline">Shop more, hop less.</span>
            </div>

            <nav class="sh-shop-footer-links" aria-label="Explore ShopHop">
                <h2>Explore</h2>
                <a href="{{ route('buyer.category.index') }}">Categories</a>
                <a href="{{ $footerHome }}#deals">Flash Deals</a>
                <a href="{{ $footerHome }}#new-arrivals">Top Products</a>
                <a href="{{ $footerHome }}#trending">Daily Discover</a>
            </nav>

            <nav class="sh-shop-footer-links" aria-label="Shopping account">
                <h2>My ShopHop</h2>
                @if ($footerBuyer)
                    <a href="{{ route('buyer.orders') }}">My Orders</a>
                    <a href="{{ route('buyer.likes') }}">My Likes</a>
                    <a href="{{ route('buyer.cart') }}">My Cart</a>
                    <a href="{{ route('buyer.profile') }}">Account Settings</a>
                @else
                    <a href="{{ route('login') }}">Sign In</a>
                    <a href="{{ route('register') }}" data-open-account-type-modal>Create Account</a>
                    <a href="{{ route('search.index') }}">Search Products</a>
                @endif
            </nav>

            <nav class="sh-shop-footer-links" aria-label="ShopHop partnerships">
                <h2>Get Started</h2>
                <a href="{{ route('register') }}" data-open-account-type-modal>Start Selling</a>
                <a href="{{ route('logistics.home') }}">Logistics Partners</a>
                <a href="{{ $footerHome }}">Marketplace Home</a>
                <a href="{{ $footerBuyer ? route('buyer.help') : route('help.index') }}">Help Center</a>
                <p>Shopping assistance is available through your account when supported.</p>
            </nav>
        </div>
        <div class="sh-shop-footer-bottom">
            <span>&copy; {{ date('Y') }} ShopHop. All rights reserved.</span>
            <span><strong>Shop<span class="sh-brand-hop">Hop</span></strong> &nbsp;·&nbsp; Your marketplace, everyday finds</span>
        </div>
    </div>
</footer>
