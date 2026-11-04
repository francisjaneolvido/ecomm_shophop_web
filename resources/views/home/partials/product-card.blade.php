{{-- One truthful product card shared by Flash Deals, Top Products, and Daily Discover. --}}
@php
    $basePrice = (float) $product->price;
    $discount = max(0, min(100, (int) ($product->discount ?? 0)));
    // Align the shown sale price with the existing public Buyer product page.
    $salePrice = $discount > 0 ? round($basePrice - ($basePrice * $discount / 100)) : $basePrice;
    $stockCount = (int) ($product->stock ?? 0);
    $soldCount = (int) ($product->completed_units_sold ?? 0);
    $productUrl = route('buyer.product.show', ['product' => $product->id]);
    $imageUrl = $landingProductImageUrl($product->image);
    $videoFirst = $product->is_video_first && $product->video_url;
    $isBuyer = $isShopBuyer ?? (auth()->check() && auth()->user()->account_type === 'buyer');
    $isLiked = $isBuyer && in_array((int) $product->id, $favoriteProductIds ?? [], true);
@endphp

<article class="sh-product-card">
    <a href="{{ $productUrl }}" class="sh-product-primary" aria-label="View {{ $product->name }}">
        <div class="sh-product-media">
            @if ($videoFirst)
                <video muted playsinline preload="metadata" poster="{{ $imageUrl }}" aria-label="Video preview for {{ $product->name }}">
                    <source src="{{ $product->video_url }}" type="video/mp4">
                </video>
                <span class="sh-video-label">VIDEO</span>
            @else
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.jpg') }}';">
            @endif

            @if ($discount > 0)
                <span class="sh-discount-badge">-{{ $discount }}%</span>
            @elseif ($stockCount <= 5)
                <span class="sh-low-stock-badge">LOW STOCK</span>
            @endif
        </div>

        <div class="sh-product-info">
            <p class="sh-product-category">{{ $product->category }}</p>
            <h3 class="sh-product-name">{{ $product->name }}</h3>

            <div class="sh-product-prices">
                <strong class="sh-product-price">₱{{ number_format($salePrice, 2) }}</strong>
                @if ($discount > 0)
                    <del class="sh-product-original">₱{{ number_format($basePrice, 2) }}</del>
                @endif
            </div>

            <div class="sh-product-footnote">
                @if ($soldCount > 0)
                    <span>{{ number_format($soldCount) }} completed sales</span>
                @else
                    <span>{{ $stockCount }} in stock</span>
                @endif
            </div>
        </div>
    </a>
    <button type="button" class="sh-card-heart" aria-label="{{ $isLiked ? 'Remove' : 'Save' }} {{ $product->name }} {{ $isBuyer ? 'from wishlist' : '(sign in required)' }}"
                @if ($isBuyer)
                    data-shop-favorite
                    data-favorite-product="{{ (int) $product->id }}" data-favorite-name="{{ $product->name }}"
                    data-favorite-url="{{ route('buyer.likes.store', ['product' => $product->id]) }}"
                    aria-pressed="{{ $isLiked ? 'true' : 'false' }}"
                @else
                    data-login-required
                @endif>
                <x-lucide-heart aria-hidden="true" />
    </button>

    @if ($isBuyer && ! $product->has_variants)
        <button type="button" class="sh-product-action" data-shop-add-cart
            data-product-id="{{ (int) $product->id }}" data-cart-url="{{ route('buyer.cart.add') }}"
            aria-label="Add {{ $product->name }} to cart">
            <x-lucide-shopping-cart aria-hidden="true" /><span>Add to Cart</span>
        </button>
    @elseif ($isBuyer)
        <a class="sh-product-action" href="{{ $productUrl }}" aria-label="Choose options for {{ $product->name }}">
            <x-lucide-list-filter aria-hidden="true" /><span>Select Options</span>
        </a>
    @else
        <button type="button" class="sh-product-action" data-login-required aria-label="Sign in to add {{ $product->name }} to cart">
            <x-lucide-shopping-cart aria-hidden="true" /><span>Sign in to Buy</span>
        </button>
    @endif
</article>
