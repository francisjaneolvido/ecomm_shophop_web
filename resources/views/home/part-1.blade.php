{{-- HERO & CATEGORY SHORTCUTS: visual hierarchy inspired by marketplaces, with original ShopHop design. --}}
@php
    $heroSlides = collect(config('shophop_marketplace.banners', []))->map(function ($slide) {
        $slide['link'] = match ($slide['target']) {
            'categories' => route('buyer.category.index'),
            'deals' => route('home').'#deals',
            default => route('home').'#trending',
        };
        return $slide;
    });

@endphp

<section class="sh-market-top" aria-label="ShopHop marketplace promotions">
    <div class="sh-market-width">
        <div class="sh-banner-layout">
            <div class="sh-banner-primary" data-shop-hero aria-label="Featured marketplace collections">
                @foreach ($heroSlides as $slide)
                    <div class="sh-hero-slide" data-shop-slide @if (!$loop->first) hidden @endif>
                        <img class="sh-slide-photo" src="{{ asset($slide['image']) }}" alt="" @if (!$loop->first) loading="lazy" @endif>
                        <div class="sh-slide-shade"></div>
                        <div class="sh-slide-text">
                            <span class="sh-banner-eyebrow">{{ $slide['eyebrow'] }}</span>
                            <h1>{{ $slide['heading'] }}</h1>
                            <p>{{ $slide['description'] }}</p>
                            <a class="sh-banner-cta" href="{{ $slide['link'] }}">{{ $slide['link_label'] }} <x-lucide-arrow-right aria-hidden="true" /></a>
                        </div>
                    </div>
                @endforeach

                <div class="sh-slide-controls">
                    <button type="button" aria-label="Previous featured banner" data-shop-hero-prev><x-lucide-chevron-left aria-hidden="true" /></button>
                    <div class="sh-slide-dots" role="group" aria-label="Choose promotional banner">
                        @foreach ($heroSlides as $slide)
                            <button type="button" data-shop-hero-dot="{{ $loop->index }}" aria-label="Show banner {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                        @endforeach
                    </div>
                    <button type="button" aria-label="Next featured banner" data-shop-hero-next><x-lucide-chevron-right aria-hidden="true" /></button>
                </div>
            </div>

            <div class="sh-banner-side">
                <a href="{{ route('home') }}#deals" class="sh-side-promo sh-side-promo-first">
                    <img src="{{ asset('images/category_icons_bg/electronics_gadgets/cpu.jpg') }}" alt="" loading="lazy">
                    <span class="sh-side-shade"></span>
                    <span class="sh-side-content">
                        <span class="sh-side-kicker">SHOPHOP DEALS</span>
                        <strong>Discover seller discounts</strong>
                        <span>See offers <x-lucide-arrow-right aria-hidden="true" /></span>
                    </span>
                </a>
                <a href="{{ route('home') }}#new-arrivals" class="sh-side-promo sh-side-promo-second">
                    <img src="{{ asset('images/category_icons_bg/furniture_office/tablelamps.jpg') }}" alt="" loading="lazy">
                    <span class="sh-side-shade"></span>
                    <span class="sh-side-content">
                        <span class="sh-side-kicker">TOP PRODUCTS</span>
                        <strong>Explore shoppers' picks</strong>
                        <span>View products <x-lucide-arrow-right aria-hidden="true" /></span>
                    </span>
                </a>
            </div>
        </div>

        {{-- 14 master categories; exactly seven visible at desktop width. --}}
        <section id="categories" class="sh-category-section" aria-labelledby="sh-categories-title">
            <div class="sh-category-header">
                <div>
                    <h2 id="sh-categories-title">Shop by Category</h2>
                    <p>Explore all {{ $landingCategories->count() }} ShopHop departments.</p>
                </div>
                <div class="sh-category-controls">
                    <span data-shop-category-position aria-live="polite">1–{{ min(7, $landingCategories->count()) }} of {{ $landingCategories->count() }}</span>
                    <button type="button" data-shop-category-prev aria-label="Previous categories" disabled><x-lucide-chevron-left aria-hidden="true" /></button>
                    <button type="button" data-shop-category-next aria-label="Next categories"><x-lucide-chevron-right aria-hidden="true" /></button>
                </div>
            </div>

            <div class="sh-categories-track" data-shop-categories aria-label="ShopHop categories">
                @foreach ($landingCategories as $category)
                    @php
                        $folder = (string) ($category['folder'] ?? '');
                        $coverImage = null;
                        if ($folder !== '' && preg_match('/^[a-z0-9_\-]+$/i', $folder)) {
                            $folderPath = public_path('images/category_icons_bg/' . $folder);
                            if (is_dir($folderPath)) {
                                $files = glob($folderPath . '/*.{jpg,jpeg,png,webp,avif,gif}', GLOB_BRACE);
                                if (! empty($files)) {
                                    $coverImage = asset('images/category_icons_bg/' . $folder . '/' . basename($files[0]));
                                }
                            }
                        }
                    @endphp
                    <a href="{{ route('buyer.category.show', ['category' => $category['slug']]) }}" class="sh-category-tile" data-shop-category>
                        <span class="sh-category-art">
                            @if ($coverImage)
                                <img src="{{ $coverImage }}" alt="" loading="lazy">
                            @else
                                <x-dynamic-component :component="'lucide-' . ($category['icon'] ?? 'shopping-bag')" aria-hidden="true" />
                            @endif
                        </span>
                        <span class="sh-category-name">{{ $category['name'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</section>
