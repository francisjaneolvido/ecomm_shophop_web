<footer class="lp-footer">
    <div class="lp-wrap lp-footer-grid">
        <div class="lp-footer-brand">
            <a href="{{ route('logistics.home') }}" class="lp-brand" aria-label="ShopHop Logistics home">
                <img src="{{ asset('images/logo-bunny.png') }}" alt="ShopHop logo" width="38" height="38">
                <span>Shop<span class="sh-brand-hop">Hop</span><small>LOGISTICS</small></span>
            </a>
            <p>One connected platform for partner onboarding, parcel handling and delivery coordination.</p>
        </div>
        <nav aria-label="Explore Logistics">
            <strong>Explore</strong>
            <a href="{{ route('logistics.home') }}#benefits">Why ShopHop?</a>
            <a href="{{ route('logistics.home') }}#process">Partner process</a>
            <a href="{{ route('logistics.home') }}#rider-guide">Rider guide</a>
        </nav>
        <nav aria-label="Partner Links">
            <strong>Partners</strong>
            <a href="{{ route('logistics.register') }}">Apply as partner</a>
            <a href="{{ route('logistics.login') }}">Partner login</a>
            <a href="{{ route('logistics.terms') }}">Partner terms</a>
        </nav>
        <nav aria-label="ShopHop Links">
            <strong>Shop<span class="sh-brand-hop">Hop</span></strong>
            <a href="{{ route('home') }}">Marketplace ↗</a>
            <a href="{{ route('logistics.home') }}#faq">Frequently asked questions</a>
        </nav>
    </div>
    <div class="lp-wrap lp-footer-bottom">
        <span>© {{ date('Y') }} ShopHop Logistics · Part of ShopHop</span>
        <span>Built for clearer operations, from pickup to delivery.</span>
    </div>
</footer>
