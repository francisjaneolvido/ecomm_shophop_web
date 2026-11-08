{{-- Single ShopHop shopper header for guest homepage and Buyer web pages. --}}
@php
    $shopperUser = auth()->user();
    $shopperIsBuyer = $shopperUser && $shopperUser->account_type === 'buyer';
    $shopperBuyer = $shopperIsBuyer ? $shopperUser->buyer : null;
    $shopperName = $shopperIsBuyer ? ($shopperUser->first_name ?: 'Buyer') : 'Guest';
    $shopperInitial = strtoupper(mb_substr($shopperName, 0, 1));
    $shopperHome = $shopperIsBuyer && \Illuminate\Support\Facades\Route::has('buyer.dashboard')
        ? route('buyer.dashboard') : route('home');

    $shopperLikedIds = $likedProductIds ?? null;
    if ($shopperIsBuyer && $shopperLikedIds === null) {
        $shopperLikedIds = ($shopperBuyer?->id && \Illuminate\Support\Facades\Schema::hasTable('favorites'))
            ? \App\Models\Buyer\Favorite\Favorite::query()->where('buyer_id', $shopperBuyer->id)->pluck('product_id')->map(fn ($id) => (int) $id)->all()
            : [];
    }
    $shopperFavoriteCount = $shopperIsBuyer ? ($favoriteCount ?? count($shopperLikedIds ?? [])) : 0;
    $shopperCartCount = $shopperIsBuyer
        ? ($cartItemCount ?? ($shopperBuyer?->id && \Illuminate\Support\Facades\Schema::hasTable('cart_items')
            ? \App\Models\Buyer\Cart\CartItem::query()->where('buyer_id', $shopperBuyer->id)->count() : 0))
        : 0;
@endphp

<header id="shopper-header" class="sh-shop-header sticky top-0 z-50" role="banner">
    <div class="sh-shop-utility">
        <div class="sh-shop-shell sh-shop-utility-inner">
            <div class="sh-shop-utility-left">
                <a href="{{ route('register') }}" data-open-account-type-modal>Start Selling</a>
                <span class="sh-shop-separator" aria-hidden="true">|</span>
                <span class="sh-shop-announcement">Discover marketplace finds from ShopHop sellers</span>
            </div>
            <nav class="sh-shop-utility-right" aria-label="Quick account links">
                <a href="{{ $shopperHome }}#deals">Flash Deals</a>
                @if ($shopperIsBuyer)
                    <a href="{{ route('buyer.orders') }}">My Orders</a>
                    <a href="{{ route('buyer.profile') }}">My Account</a>
                <a href="{{ route('buyer.help') }}">Help Center</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form>
                @else
                    <a href="{{ route('register') }}" data-open-account-type-modal>Sign Up</a>
                    <a href="{{ route('login') }}">Login</a>
                @endif
            </nav>
        </div>
    </div>

    <div class="sh-shop-shell sh-shop-main-shell">
        <div class="sh-shop-main-row">
            <a class="sh-shop-brand" href="{{ $shopperHome }}" aria-label="ShopHop Home">
                <img src="{{ asset('images/logo.png') }}" alt="ShopHop logo" width="39" height="39">
                <span class="sh-shop-brand-type"><strong>ShopHop</strong><small>HOP IN. SHOP MORE.</small></span>
            </a>

            <nav class="sh-shop-nav" aria-label="Main marketplace navigation">
                <a class="{{ request()->routeIs('home', 'buyer.dashboard') ? 'is-active' : '' }}" href="{{ $shopperHome }}">Home</a>
                <a class="{{ request()->routeIs('buyer.category.*') ? 'is-active' : '' }}" href="{{ route('buyer.category.index') }}">Categories</a>
                <a href="{{ $shopperHome }}#deals">Flash Deals</a>
                <a href="{{ $shopperHome }}#new-arrivals">Top Products</a>
            </nav>

            <form class="sh-shop-search" action="{{ route('search.index') }}" method="GET" role="search" data-search-suggestions data-suggestions-url="{{ route('search.suggestions') }}" data-categories-url="{{ route('buyer.category.index') }}">
                <x-lucide-search class="sh-shop-search-icon" />
                <label class="sr-only" for="sh-shop-search-desktop">Search products</label>
                <input id="sh-shop-search-desktop" name="search" type="search" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="sh-shop-search-desktop-suggestions" maxlength="100" value="{{ request('search') }}" placeholder="Search products" autocomplete="off">
                <button type="submit" aria-label="Search"><x-lucide-search /></button>
            </form>

            <div class="sh-shop-actions">
                <a class="sh-shop-action {{ request()->routeIs('buyer.likes') ? 'is-current' : '' }}"
                    href="{{ $shopperIsBuyer ? route('buyer.likes') : route('login') }}"
                    @unless ($shopperIsBuyer) data-login-required @endunless
                    title="My Likes" aria-label="My Likes">
                    <x-lucide-heart />
                    @if ($shopperIsBuyer)
                        <span class="sh-shop-counter sh-shop-counter-heart" data-favorite-count @if (!$shopperFavoriteCount) hidden @endif>{{ $shopperFavoriteCount }}</span>
                    @endif
                </a>
                <a class="sh-shop-action {{ request()->routeIs('buyer.cart') ? 'is-current' : '' }}"
                    data-buyer-cart-link href="{{ $shopperIsBuyer ? route('buyer.cart') : route('login') }}"
                    @unless ($shopperIsBuyer) data-login-required @endunless
                    title="Shopping Cart" aria-label="Shopping Cart">
                    <x-lucide-shopping-cart />
                    @if ($shopperIsBuyer)
                        <span class="sh-shop-counter sh-shop-counter-cart" data-shop-cart-count data-cart-count @if (!$shopperCartCount) hidden @endif>{{ $shopperCartCount }}</span>
                    @endif
                </a>

                @if ($shopperIsBuyer)
                    <div class="sh-shop-account" data-shop-account>
                        <button type="button" class="sh-shop-account-trigger" data-shop-account-toggle aria-haspopup="true" aria-expanded="false" aria-controls="sh-shop-account-panel">
                            <span class="sh-shop-avatar">{{ $shopperInitial }}</span>
                            <span class="sh-shop-account-name">{{ $shopperName }}</span>
                            <x-lucide-chevron-down class="sh-shop-chevron" />
                        </button>
                        <div id="sh-shop-account-panel" class="sh-shop-account-panel" data-shop-account-panel hidden>
                            <div class="sh-shop-account-intro"><strong>{{ $shopperName }}</strong><span>Buyer account</span></div>
                            <a href="{{ route('buyer.profile') }}"><x-lucide-user-round /> Account Settings</a>
                            <a href="{{ route('buyer.orders') }}"><x-lucide-package /> My Orders</a>
                            <a href="{{ route('buyer.likes') }}"><x-lucide-heart /> My Likes</a>
                            <a href="{{ route('buyer.cart') }}"><x-lucide-shopping-cart /> My Cart</a>
                            <a href="{{ route('buyer.messages') }}"><x-lucide-message-circle /> Messages</a>
                            <a href="{{ route('buyer.help') }}"><x-lucide-circle-help /> Help Center</a>
                            <div class="sh-shop-account-appearance">@include('partials.theme-toggle')</div>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-lucide-log-out /> Logout</button></form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="sh-shop-action sh-shop-guest-account" title="Sign In" aria-label="Sign In"><x-lucide-user-round /></a>
                @endif

                <button type="button" class="sh-shop-menu-toggle" data-shop-menu-toggle aria-label="Open navigation" aria-expanded="false" aria-controls="sh-shop-mobile-nav"><x-lucide-menu /></button>
            </div>
        </div>

        <div class="sh-shop-mobile-search">
            <form action="{{ route('search.index') }}" method="GET" role="search" data-search-suggestions data-suggestions-url="{{ route('search.suggestions') }}" data-categories-url="{{ route('buyer.category.index') }}">
                <x-lucide-search class="sh-shop-search-icon" />
                <label class="sr-only" for="sh-shop-search-mobile">Search products</label>
                <input id="sh-shop-search-mobile" name="search" type="search" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="sh-shop-search-mobile-suggestions" autocomplete="off" maxlength="100" value="{{ request('search') }}" placeholder="Search products">
                <button type="submit" aria-label="Search"><x-lucide-search /></button>
            </form>
        </div>

        <nav id="sh-shop-mobile-nav" class="sh-shop-mobile-nav" aria-label="Mobile marketplace navigation" hidden>
            <a href="{{ $shopperHome }}">Home</a>
            <a href="{{ route('buyer.category.index') }}">Categories</a>
            <a href="{{ $shopperHome }}#deals">Flash Deals</a>
            <a href="{{ $shopperHome }}#new-arrivals">Top Products</a>
            <a href="{{ $shopperIsBuyer ? route('buyer.likes') : route('login') }}" @unless ($shopperIsBuyer) data-login-required @endunless>My Likes</a>
            <a data-buyer-cart-link href="{{ $shopperIsBuyer ? route('buyer.cart') : route('login') }}" @unless ($shopperIsBuyer) data-login-required @endunless>My Cart</a>
            @if ($shopperIsBuyer)
                <a href="{{ route('buyer.orders') }}">My Orders</a>
                <a href="{{ route('buyer.messages') }}">Messages</a>
                <a href="{{ route('buyer.profile') }}">My Account</a>
                <a href="{{ route('buyer.help') }}">Help Center</a>
            @else
                <a href="{{ route('help.index') }}">Help Center</a>
                <a href="{{ route('login') }}">Sign In</a>
                <a href="{{ route('register') }}" data-open-account-type-modal>Create Account</a>
            @endif
        </nav>
    </div>
</header>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('shopper-header');
    if (!header) return;

    const mobileToggle = header.querySelector('[data-shop-menu-toggle]');
    const mobileMenu = header.querySelector('#sh-shop-mobile-nav');
    const accountToggle = header.querySelector('[data-shop-account-toggle]');
    const accountPanel = header.querySelector('[data-shop-account-panel]');
    const closePanels = () => {
        if (mobileMenu) mobileMenu.hidden = true;
        if (mobileToggle) mobileToggle.setAttribute('aria-expanded', 'false');
        if (accountPanel) accountPanel.hidden = true;
        if (accountToggle) accountToggle.setAttribute('aria-expanded', 'false');
    };

    mobileToggle?.addEventListener('click', event => {
        event.stopPropagation();
        const open = !!mobileMenu.hidden;
        closePanels();
        mobileMenu.hidden = !open;
        mobileToggle.setAttribute('aria-expanded', String(open));
    });
    accountToggle?.addEventListener('click', event => {
        event.stopPropagation();
        const open = !!accountPanel.hidden;
        closePanels();
        accountPanel.hidden = !open;
        accountToggle.setAttribute('aria-expanded', String(open));
    });
    accountPanel?.addEventListener('click', event => event.stopPropagation());
    mobileMenu?.addEventListener('click', event => event.stopPropagation());
    document.addEventListener('click', event => {
        if (!header.contains(event.target)) closePanels();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closePanels();
    });

    @unless ($shopperIsBuyer)
    // Public shopping actions are gated by the existing Laravel login modal.
    document.addEventListener('click', event => {
        if (!event.target.closest('[data-login-required]')) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        document.dispatchEvent(new CustomEvent('shophop:open-login-modal'));
    }, true);
    document.addEventListener('submit', event => {
        if (!event.target.matches('[data-buy-now-form]')) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        document.dispatchEvent(new CustomEvent('shophop:open-login-modal'));
    }, true);
    @endunless
});
</script>
@endpush
@endonce
