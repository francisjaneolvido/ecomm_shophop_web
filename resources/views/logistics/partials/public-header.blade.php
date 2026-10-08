<header class="lp-header">
    <div class="lp-wrap lp-header-inner">
        <a class="lp-brand" href="{{ route('logistics.home') }}" aria-label="ShopHop Logistics home">
            <img src="{{ asset('images/logo.png') }}" alt="ShopHop logo" width="36" height="36">
            <span>ShopHop<small>LOGISTICS</small></span>
        </a>
        <nav class="lp-header-links" aria-label="Logistics site navigation">
            <a class="lp-optional" href="{{ route('logistics.home') }}#benefits">Why partner?</a>
            <a class="lp-optional" href="{{ route('logistics.home') }}#process">How it works</a>
            <a class="lp-marketplace" href="{{ route('home') }}" title="Visit ShopHop marketplace">ShopHop Store ↗</a>
            <a href="{{ route('logistics.login') }}">Log In</a>
            <a class="lp-button lp-button-primary lp-button-pill" href="{{ route('logistics.register') }}">Apply Now</a>
        </nav>
    </div>
</header>
