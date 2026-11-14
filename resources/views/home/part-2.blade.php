{{-- Real ShopHop marketplace catalogue sections. No fake timers, counts, reviews, or promotions. --}}
@php
    $landingProductImageUrl = function ($path) {
        $path = trim((string) $path);
        if ($path === '') {
            return asset('images/placeholder-product.jpg');
        }
        if (str_starts_with($path, 'https://') || str_starts_with($path, 'http://')) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'images/')) {
            return asset($path);
        }
        return asset('storage/' . $path);
    };
@endphp

<section id="deals" class="sh-market-section sh-flash-section" aria-labelledby="sh-flash-title">
    <div class="sh-market-width">
        <div class="sh-section-heading">
            <div>
                <h2 id="sh-flash-title"><x-lucide-zap aria-hidden="true" /> Flash Deals</h2>
                <p>Products with discounts currently set by ShopHop sellers.</p>
            </div>
        </div>
        @if ($flashDealProducts->isNotEmpty())
            <div class="sh-products-grid sh-flash-grid">
                @foreach ($flashDealProducts as $product)
                    @include('home.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div class="sh-products-empty">
                <x-lucide-tag aria-hidden="true" />
                <div><strong>No seller discounts listed yet</strong><p>Check Daily Discover for approved, in-stock products while sellers update their offers.</p></div>
                <a href="#trending">Daily Discover <x-lucide-arrow-right aria-hidden="true" /></a>
            </div>
        @endif
    </div>
</section>

<section id="new-arrivals" class="sh-market-section sh-top-section" aria-labelledby="sh-top-title">
    <div class="sh-market-width">
        <div class="sh-section-heading">
            <div>
                <h2 id="sh-top-title"><x-lucide-trending-up aria-hidden="true" /> Top Products</h2>
                <p>{{ $hasCompletedSales ? 'Ranked by units in completed ShopHop orders.' : 'Recently listed products — sales rankings will appear after completed orders.' }}</p>
            </div>
            <a href="#trending" class="sh-section-more">More Products <x-lucide-chevron-right aria-hidden="true" /></a>
        </div>
        @if ($topProducts->isNotEmpty())
            <div class="sh-products-grid">
                @foreach ($topProducts as $product)
                    @include('home.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div class="sh-products-empty">
                <x-lucide-package aria-hidden="true" />
                <div><strong>No products to rank yet</strong><p>Approved, in-stock products will appear here automatically.</p></div>
            </div>
        @endif
    </div>
</section>

<section id="trending" class="sh-market-section sh-discover-section" aria-labelledby="sh-discover-title">
    <div class="sh-market-width">
        <div class="sh-section-heading sh-discover-heading">
            <div>
                <h2 id="sh-discover-title"><x-lucide-sparkles aria-hidden="true" /> Daily Discover</h2>
                <p>Explore the newest approved, available products across ShopHop.</p>
            </div>
        </div>
        @if ($discoverProducts->isNotEmpty())
            <div class="sh-products-grid sh-discover-grid">
                @foreach ($discoverProducts as $product)
                    @include('home.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div class="sh-products-empty">
                <x-lucide-package-search aria-hidden="true" />
                <div><strong>No products available yet</strong><p>Once a seller has an approved, active, in-stock product, it will show here automatically.</p></div>
            </div>
        @endif
    </div>
</section>
