<header class="lp-header">
    <div class="lp-header-utility" role="note">
        <div class="lp-wrap lp-header-utility-inner">
            <span>SHOPHOP LOGISTICS NETWORK <span aria-hidden="true">·</span> Supporting parcels, hubs and delivery riders</span>
            <span class="lp-utility-links"><a href="{{ route('logistics.home') }}#rider-guide">Rider Guide</a><a href="{{ route('logistics.login') }}">Partner Sign In</a></span>
        </div>
    </div>
    <div class="lp-wrap lp-header-inner">
        <a class="lp-brand" href="{{ route('logistics.home') }}" aria-label="ShopHop Logistics home">
            <img src="{{ asset('images/logo-bunny.png') }}" alt="ShopHop logo" width="40" height="40">
            <span>Shop<span class="sh-brand-hop">Hop</span><small>LOGISTICS</small></span>
        </a>
        <nav class="lp-header-links" aria-label="Logistics site navigation">
            <a class="lp-optional" href="{{ route('logistics.home') }}#benefits">Why ShopHop?</a>
            <a class="lp-optional" href="{{ route('logistics.home') }}#process">How it works</a>
            <a class="lp-rider-link" href="{{ route('logistics.home') }}#rider-guide">Rider Guide</a>
            <a class="lp-marketplace" href="{{ route('home') }}">Marketplace <span aria-hidden="true">↗</span></a>
            <a class="lp-signin" href="{{ route('logistics.login') }}">Log In</a>
            <a class="lp-button lp-button-primary lp-header-apply" href="{{ route('logistics.register') }}">Apply as Partner</a>
        </nav>
    </div>
</header>
