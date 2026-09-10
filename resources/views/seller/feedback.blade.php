@extends('seller.partials.layout')

@section('title', 'Customer Feedback')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    | This page is intentionally hardcoded for UI/UX development.
    |
    | Your backend teammate can later replace this collection with controller
    | data and connect product/order relationships, review media, seller replies,
    | moderation/reporting, pagination, and notification states.
    */

    $reviews = collect($reviews ?? [
        [
            'id' => 1,
            'order_id' => 'ORD-10210',
            'buyer' => 'Trisha Ang',
            'buyer_initials' => 'TA',
            'product' => 'Handwoven Rattan Basket',
            'variant' => 'Natural / Medium',
            'sku' => 'RATTAN-NAT-M',
            'product_image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=260&q=80',
            'rating' => 5,
            'comment' => 'Ang ganda ng gawa, mabilis din yung delivery. Sulit!',
            'date' => '2 days ago',
            'created_at_label' => 'Sep 6, 2026 · 7:42 PM',
            'verified_purchase' => true,
            'helpful_count' => 14,
            'reply' => null,
            'reply_date' => null,
            'media' => [
                'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=600&q=80',
            ],
        ],

        [
            'id' => 2,
            'order_id' => 'ORD-10204',
            'buyer' => 'Miguel Ortiz',
            'buyer_initials' => 'MO',
            'product' => 'Barako Coffee Beans 250g',
            'variant' => 'Dark Roast',
            'sku' => 'BARAKO-250-DR',
            'product_image' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=260&q=80',
            'rating' => 4,
            'comment' => 'Masarap yung kape, medyo natagalan lang ng konti sa packaging.',
            'date' => '5 days ago',
            'created_at_label' => 'Sep 3, 2026 · 9:18 AM',
            'verified_purchase' => true,
            'helpful_count' => 7,
            'reply' => 'Salamat po sa order! Pinapaganda pa namin yung packaging namin, thank you sa patience!',
            'reply_date' => 'Sep 3, 2026 · 10:05 AM',
            'media' => [],
        ],

        [
            'id' => 3,
            'order_id' => 'ORD-10198',
            'buyer' => 'Anna Reyes',
            'buyer_initials' => 'AR',
            'product' => 'Capiz Shell Wall Lamp',
            'variant' => 'Warm White / Medium',
            'sku' => 'CSL-WW-M',
            'product_image' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=260&q=80',
            'rating' => 3,
            'comment' => 'Maganda naman pero medyo may gasgas nang dumating.',
            'date' => '1 week ago',
            'created_at_label' => 'Sep 1, 2026 · 4:35 PM',
            'verified_purchase' => true,
            'helpful_count' => 3,
            'reply' => null,
            'reply_date' => null,
            'media' => [
                'https://images.unsplash.com/photo-1519710887729-000cb8e0a1dc?auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80',
            ],
        ],

        [
            'id' => 4,
            'order_id' => 'ORD-10191',
            'buyer' => 'Jessa Lim',
            'buyer_initials' => 'JL',
            'product' => 'Daily Glow Face Serum',
            'variant' => '30ml',
            'sku' => 'SERUM-30ML',
            'product_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=260&q=80',
            'rating' => 2,
            'comment' => 'Okay sana yung product pero tumagas nang kaunti sa parcel.',
            'date' => '9 days ago',
            'created_at_label' => 'Aug 30, 2026 · 6:22 PM',
            'verified_purchase' => true,
            'helpful_count' => 9,
            'reply' => 'Sorry po about the packaging issue. Thank you for letting us know — we are improving the sealing and packing process.',
            'reply_date' => 'Aug 30, 2026 · 7:05 PM',
            'media' => [],
        ],

        [
            'id' => 5,
            'order_id' => 'ORD-10182',
            'buyer' => 'Paolo Mendoza',
            'buyer_initials' => 'PM',
            'product' => 'Minimalist Canvas Tote Bag',
            'variant' => 'Natural',
            'sku' => 'TOTE-CNV-NAT',
            'product_image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=260&q=80',
            'rating' => 5,
            'comment' => 'Simple pero maganda. Matibay yung tahi at sakto yung size.',
            'date' => '2 weeks ago',
            'created_at_label' => 'Aug 24, 2026 · 2:14 PM',
            'verified_purchase' => true,
            'helpful_count' => 18,
            'reply' => 'Thank you so much po! Happy kami na nagustuhan ninyo yung tote bag.',
            'reply_date' => 'Aug 24, 2026 · 3:01 PM',
            'media' => [],
        ],
    ]);

    $averageRating = $reviews->isEmpty()
        ? 0
        : round($reviews->avg('rating'), 1);

    $ratingCounts = collect(range(5, 1))
        ->mapWithKeys(
            fn ($star) => [
                $star => $reviews->where('rating', $star)->count()
            ]
        );

    $totalReviews = $reviews->count();

    $unrepliedCount = $reviews
        ->filter(fn ($review) => empty($review['reply']))
        ->count();

    $attentionCount = $reviews
        ->filter(fn ($review) => (int) $review['rating'] <= 3)
        ->count();

    $fiveStarCount = $reviews
        ->where('rating', 5)
        ->count();
@endphp


<style>
    #sellerFeedback .feedback-card {
        transition:
            transform .16s ease,
            box-shadow .16s ease,
            border-color .16s ease;
    }

    #sellerFeedback .feedback-card:hover {
        transform: translateY(-1px);
    }

    #sellerFeedback [hidden],
    #reviewDetailsModal[hidden],
    #replyModal[hidden],
    #reviewMediaModal[hidden] {
        display: none !important;
    }

    .feedback-tab-active {
        background: #0F2C3F;
        color: #ffffff;
        border-color: #0F2C3F;
    }

    .review-media-thumb {
        transition:
            transform .16s ease,
            opacity .16s ease;
    }

    .review-media-thumb:hover {
        transform: scale(1.02);
    }
</style>


<div id="sellerFeedback" class="space-y-5">

    {{-- =========================================================
        PAGE HEADER
    ========================================================= --}}
    <section>
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">

            <div class="min-w-0">


                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    Customer Feedback
                </h1>


                <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-3xl">
                    Monitor product ratings, review buyer comments and photos,
                    and respond professionally to customer feedback.
                </p>


                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-yellow/20 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    FRONTEND DEMO · HARDCODED DATA
                </div>

            </div>

        </div>
    </section>


    {{-- =========================================================
        SUMMARY CARDS
    ========================================================= --}}
    <section class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">

        <button
            type="button"
            data-summary-filter="all"
            class="feedback-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-yellow/30"
        >
            <div class="flex items-start justify-between gap-3">

                <div class="w-10 h-10 rounded-lg bg-yellow/20 text-amber-700 flex items-center justify-center">
                    <x-lucide-star class="w-5 h-5 fill-yellow" />
                </div>

                <span class="text-[10px] font-bold text-amber-700/60">
                    AVERAGE
                </span>

            </div>

            <p class="mt-4 text-2xl font-bold text-navy">
                {{ number_format($averageRating, 1) }}
            </p>

            <p class="text-xs text-navy/45">
                Average store rating
            </p>
        </button>


        <button
            type="button"
            data-summary-filter="unreplied"
            class="feedback-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-sky/25"
        >
            <div class="flex items-start justify-between gap-3">

                <div class="w-10 h-10 rounded-lg bg-sky/10 text-sky flex items-center justify-center">
                    <x-lucide-message-square-reply class="w-5 h-5" />
                </div>

                <span class="text-[10px] font-bold text-sky/60">
                    TO REPLY
                </span>

            </div>

            <p class="mt-4 text-2xl font-bold text-navy">
                {{ $unrepliedCount }}
            </p>

            <p class="text-xs text-navy/45">
                Reviews awaiting reply
            </p>
        </button>


        <button
            type="button"
            data-summary-filter="attention"
            class="feedback-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-red-200"
        >
            <div class="flex items-start justify-between gap-3">

                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center">
                    <x-lucide-triangle-alert class="w-5 h-5" />
                </div>

                <span class="text-[10px] font-bold text-red-400">
                    ATTENTION
                </span>

            </div>

            <p class="mt-4 text-2xl font-bold text-red-500">
                {{ $attentionCount }}
            </p>

            <p class="text-xs text-navy/45">
                Ratings of 3 stars or below
            </p>
        </button>


        <button
            type="button"
            data-summary-filter="5"
            class="feedback-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-teal/25"
        >
            <div class="flex items-start justify-between gap-3">

                <div class="w-10 h-10 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center">
                    <x-lucide-sparkles class="w-5 h-5" />
                </div>

                <span class="text-[10px] font-bold text-teal-dark/60">
                    EXCELLENT
                </span>

            </div>

            <p class="mt-4 text-2xl font-bold text-navy">
                {{ $fiveStarCount }}
            </p>

            <p class="text-xs text-navy/45">
                Five-star reviews
            </p>
        </button>

    </section>


    {{-- =========================================================
        RATING SUMMARY
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl p-4 sm:p-5">

        <div class="grid grid-cols-1 md:grid-cols-[220px_1fr] gap-5 md:gap-7 items-center">

            {{-- Average --}}
            <div class="text-center md:text-left md:pr-7 md:border-r md:border-gray-border">

                <p class="text-[10px] uppercase tracking-[0.15em] font-bold text-navy/35">
                    Overall Rating
                </p>


                <div class="mt-2 flex items-end justify-center md:justify-start gap-2">

                    <p class="text-4xl font-bold text-navy">
                        {{ number_format($averageRating, 1) }}
                    </p>

                    <p class="text-xs text-navy/35 mb-1">
                        / 5
                    </p>

                </div>


                <div class="flex items-center justify-center md:justify-start gap-0.5 mt-2">

                    @for ($i = 1; $i <= 5; $i++)

                        <x-lucide-star
                            class="w-4 h-4
                                {{ $i <= round($averageRating)
                                    ? 'text-yellow fill-yellow'
                                    : 'text-gray-border'
                                }}"
                        />

                    @endfor

                </div>


                <p class="text-[11px] text-navy/40 mt-2">
                    Based on {{ $totalReviews }}
                    review{{ $totalReviews === 1 ? '' : 's' }}
                </p>

            </div>


            {{-- Distribution --}}
            <div class="space-y-2">

                @foreach ($ratingCounts as $star => $count)

                    @php
                        $pct = $totalReviews > 0
                            ? round(($count / $totalReviews) * 100)
                            : 0;
                    @endphp


                    <button
                        type="button"
                        data-rating-filter="{{ $star }}"
                        class="w-full group"
                    >

                        <div class="flex items-center gap-3">

                            <span class="text-[11px] font-semibold text-navy/55 w-12 shrink-0 text-left">
                                {{ $star }} star
                            </span>


                            <div class="h-2 flex-1 rounded-full bg-gray-bg overflow-hidden">

                                <div
                                    class="h-full rounded-full bg-yellow transition-all"
                                    style="width: {{ $pct }}%"
                                ></div>

                            </div>


                            <span class="text-[11px] text-navy/40 w-8 text-right shrink-0">
                                {{ $count }}
                            </span>


                            <span class="hidden sm:inline text-[10px] text-navy/25 w-10 text-right shrink-0">
                                {{ $pct }}%
                            </span>

                        </div>

                    </button>

                @endforeach

            </div>

        </div>

    </section>


    {{-- =========================================================
        FILTERS
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl p-3">

        <div class="flex flex-col xl:flex-row xl:items-center gap-3">

            <div class="flex items-center gap-1 overflow-x-auto">

                @foreach ([
                    ['key' => 'all', 'label' => 'All Reviews', 'count' => $totalReviews],
                    ['key' => 'unreplied', 'label' => 'Unreplied', 'count' => $unrepliedCount],
                    ['key' => 'replied', 'label' => 'Replied', 'count' => $totalReviews - $unrepliedCount],
                    ['key' => 'attention', 'label' => 'Needs Attention', 'count' => $attentionCount],
                ] as $tab)

                    <button
                        type="button"
                        data-feedback-tab="{{ $tab['key'] }}"
                        class="feedback-tab h-9 px-3.5 rounded-lg border border-transparent text-xs font-semibold whitespace-nowrap transition
                            {{ $tab['key'] === 'all'
                                ? 'feedback-tab-active'
                                : 'text-navy/50 hover:bg-gray-bg'
                            }}"
                    >
                        {{ $tab['label'] }}

                        <span class="ml-1 opacity-60">
                            {{ $tab['count'] }}
                        </span>
                    </button>

                @endforeach

            </div>


            <div class="flex flex-col sm:flex-row sm:items-center gap-2 flex-1 xl:justify-end">

                <div class="relative flex-1 xl:max-w-sm">

                    <x-lucide-search class="w-4 h-4 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2" />

                    <input
                        type="text"
                        id="feedbackSearch"
                        placeholder="Search buyer, product, order or SKU..."
                        class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/30 focus:outline-none focus:border-teal/50"
                    >

                </div>


                <select
                    id="feedbackRatingFilter"
                    class="h-9 px-3 rounded-lg border border-gray-border bg-white text-xs text-navy focus:outline-none focus:border-teal/50"
                >
                    <option value="">All Ratings</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>


                <select
                    id="feedbackSort"
                    class="h-9 px-3 rounded-lg border border-gray-border bg-white text-xs text-navy focus:outline-none focus:border-teal/50"
                >
                    <option value="newest">Newest First</option>
                    <option value="rating-high">Highest Rating</option>
                    <option value="rating-low">Lowest Rating</option>
                    <option value="helpful">Most Helpful</option>
                </select>

            </div>

        </div>


        <div class="mt-3 flex items-center justify-between gap-3">

            <p class="text-[10px] text-navy/35">

                Showing

                <strong id="feedbackVisibleCount" class="text-navy/60">
                    {{ $totalReviews }}
                </strong>

                of {{ $totalReviews }} reviews

            </p>


            <button
                type="button"
                id="feedbackClearFilters"
                class="text-[11px] font-semibold text-navy/45 hover:text-teal-dark transition"
            >
                Clear filters
            </button>

        </div>

    </section>


    {{-- =========================================================
        REVIEWS LIST
    ========================================================= --}}
    @if ($reviews->isEmpty())

        <section class="bg-white border border-gray-border rounded-xl py-14 text-center">

            <div class="w-12 h-12 mx-auto rounded-xl bg-teal-light text-teal-dark flex items-center justify-center">
                <x-lucide-message-square class="w-5 h-5" />
            </div>

            <p class="text-sm font-semibold text-navy/55 mt-3">
                No reviews yet
            </p>

            <p class="text-xs text-navy/35 mt-1">
                Buyer feedback will appear here after completed orders.
            </p>

        </section>

    @else

        <section id="feedbackCards" class="space-y-4">

            @foreach ($reviews as $review)

                @php
                    $isLowRating = (int) $review['rating'] <= 3;
                    $hasReply = !empty($review['reply']);

                    $searchText = strtolower(
                        $review['buyer'] . ' ' .
                        $review['product'] . ' ' .
                        $review['order_id'] . ' ' .
                        ($review['variant'] ?? '') . ' ' .
                        ($review['sku'] ?? '')
                    );

                    $reviewJson = json_encode(
                        $review,
                        JSON_HEX_APOS | JSON_HEX_QUOT
                    );
                @endphp


                <article
                    class="feedback-card bg-white border rounded-xl overflow-hidden
                        {{ $isLowRating
                            ? 'border-red-200'
                            : 'border-gray-border'
                        }}"
                    data-review-card
                    data-review-id="{{ $review['id'] }}"
                    data-rating="{{ $review['rating'] }}"
                    data-replied="{{ $hasReply ? '1' : '0' }}"
                    data-helpful="{{ $review['helpful_count'] }}"
                    data-search="{{ $searchText }}"
                    data-review='{{ $reviewJson }}'
                >

                    {{-- Header --}}
                    <div class="px-4 sm:px-5 py-4 border-b border-gray-border">

                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

                            <div class="flex items-start gap-3 min-w-0">

                                {{-- Buyer avatar --}}
                                <div class="w-10 h-10 rounded-full bg-navy text-white flex items-center justify-center text-[11px] font-bold shrink-0">
                                    {{ $review['buyer_initials'] }}
                                </div>


                                <div class="min-w-0">

                                    <div class="flex flex-wrap items-center gap-2">

                                        <p class="text-sm font-bold text-navy">
                                            {{ $review['buyer'] }}
                                        </p>


                                        @if ($review['verified_purchase'])

                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-teal/10 text-[10px] font-bold text-teal-dark">
                                                <x-lucide-badge-check class="w-3 h-3" />
                                                Verified Purchase
                                            </span>

                                        @endif


                                        @if ($isLowRating)

                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-50 text-[10px] font-bold text-red-500">
                                                <x-lucide-triangle-alert class="w-3 h-3" />
                                                Needs Attention
                                            </span>

                                        @endif

                                    </div>


                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-1">

                                        <div class="flex items-center gap-0.5">

                                            @for ($i = 1; $i <= 5; $i++)

                                                <x-lucide-star
                                                    class="w-4 h-4
                                                        {{ $i <= $review['rating']
                                                            ? 'text-yellow fill-yellow'
                                                            : 'text-gray-border'
                                                        }}"
                                                />

                                            @endfor

                                        </div>


                                        <span class="text-[11px] font-semibold text-navy/55">
                                            {{ $review['rating'] }}.0
                                        </span>


                                        <span class="text-navy/20">
                                            ·
                                        </span>


                                        <span class="text-[10px] text-navy/35">
                                            {{ $review['date'] }}
                                        </span>

                                    </div>

                                </div>

                            </div>


                            <div class="sm:text-right">

                                <p class="text-[10px] text-navy/35">
                                    Order
                                </p>

                                <p class="text-[11px] font-semibold text-navy mt-0.5">
                                    {{ $review['order_id'] }}
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- Product --}}
                    <div class="px-4 sm:px-5 py-4 bg-gray-bg/30 border-b border-gray-border">

                        <div class="flex items-center gap-3">

                            <div class="w-14 h-14 rounded-xl bg-white border border-gray-border overflow-hidden shrink-0">

                                @if (!empty($review['product_image']))

                                    <img
                                        src="{{ $review['product_image'] }}"
                                        alt="{{ $review['product'] }}"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    >

                                @else

                                    <div class="w-full h-full flex items-center justify-center">
                                        <x-lucide-package class="w-5 h-5 text-navy/25" />
                                    </div>

                                @endif

                            </div>


                            <div class="min-w-0 flex-1">

                                <p class="text-xs font-semibold text-navy">
                                    {{ $review['product'] }}
                                </p>

                                <p class="text-[11px] text-navy/45 mt-0.5">
                                    {{ $review['variant'] ?? 'Default variant' }}
                                </p>

                                <p class="text-[10px] text-navy/30 mt-0.5">
                                    SKU: {{ $review['sku'] ?? '—' }}
                                </p>

                            </div>


                            <button
                                type="button"
                                data-open-review-details
                                class="hidden sm:inline-flex items-center gap-1 text-[11px] font-bold text-teal-dark hover:text-teal transition"
                            >
                                View details
                                <x-lucide-chevron-right class="w-3.5 h-3.5" />
                            </button>

                        </div>

                    </div>


                    {{-- Review --}}
                    <div class="px-4 sm:px-5 py-4">

                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-navy/35">
                            Customer Review
                        </p>


                        <p class="text-xs sm:text-[13px] text-navy/70 mt-2 leading-relaxed">
                            “{{ $review['comment'] }}”
                        </p>


                        {{-- Buyer media --}}
                        @if (!empty($review['media']))

                            <div class="mt-3">

                                <div class="flex items-center gap-1.5 mb-2">

                                    <x-lucide-image class="w-3.5 h-3.5 text-navy/35" />

                                    <p class="text-[10px] font-semibold text-navy/40">
                                        Buyer photos
                                    </p>

                                </div>


                                <div class="flex flex-wrap gap-2">

                                    @foreach ($review['media'] as $mediaIndex => $media)

                                        <button
                                            type="button"
                                            data-open-review-media
                                            data-media-index="{{ $mediaIndex }}"
                                            class="review-media-thumb w-20 h-20 rounded-xl overflow-hidden border border-gray-border bg-gray-bg"
                                        >

                                            <img
                                                src="{{ $media }}"
                                                alt="Buyer review photo"
                                                class="w-full h-full object-cover"
                                                loading="lazy"
                                            >

                                        </button>

                                    @endforeach

                                </div>

                            </div>

                        @endif


                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">

                            <span class="inline-flex items-center gap-1 text-[10px] text-navy/35">
                                <x-lucide-thumbs-up class="w-3.5 h-3.5" />
                                {{ $review['helpful_count'] }}
                                found this helpful
                            </span>


                            <span class="inline-flex items-center gap-1 text-[10px] text-navy/30">
                                <x-lucide-clock-3 class="w-3.5 h-3.5" />
                                {{ $review['created_at_label'] }}
                            </span>

                        </div>

                    </div>


                    {{-- Seller reply --}}
                    <div class="px-4 sm:px-5 pb-4">

                        @if ($hasReply)

                            <div
                                data-reply-display
                                class="rounded-xl border border-teal/20 bg-teal/5 p-3"
                            >

                                <div class="flex items-start gap-3">

                                    <div class="w-8 h-8 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
                                        <x-lucide-store class="w-4 h-4" />
                                    </div>


                                    <div class="min-w-0 flex-1">

                                        <div class="flex flex-wrap items-center justify-between gap-2">

                                            <div>

                                                <p class="text-[10px] font-bold uppercase tracking-wide text-teal-dark">
                                                    Your Reply
                                                </p>

                                                <p
                                                    data-reply-date
                                                    class="text-[9px] text-navy/30 mt-0.5"
                                                >
                                                    {{ $review['reply_date'] }}
                                                </p>

                                            </div>


                                            <button
                                                type="button"
                                                data-reply-review
                                                data-reply-mode="edit"
                                                class="text-[10px] font-bold text-navy/40 hover:text-teal-dark transition"
                                            >
                                                Edit reply
                                            </button>

                                        </div>


                                        <p
                                            data-reply-text
                                            class="text-xs text-navy/65 mt-2 leading-relaxed"
                                        >
                                            {{ $review['reply'] }}
                                        </p>

                                    </div>

                                </div>

                            </div>


                        @else

                            <div
                                data-no-reply
                                class="rounded-xl border border-dashed border-gray-border bg-gray-bg/30 p-3"
                            >

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                                    <div>

                                        <p class="text-xs font-semibold text-navy">
                                            No seller reply yet
                                        </p>

                                        <p class="text-[10px] text-navy/40 mt-0.5">
                                            A helpful response can show buyers that you value their feedback.
                                        </p>

                                    </div>


                                    <button
                                        type="button"
                                        data-reply-review
                                        data-reply-mode="new"
                                        class="inline-flex items-center justify-center gap-1.5 h-9 px-3.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition shrink-0"
                                    >
                                        <x-lucide-message-square-reply class="w-4 h-4" />
                                        Reply
                                    </button>

                                </div>

                            </div>

                        @endif

                    </div>


                    {{-- Footer actions --}}
                    <div class="px-4 sm:px-5 py-3 border-t border-gray-border bg-gray-bg/25">

                        <div class="flex flex-wrap items-center justify-between gap-2">

                            <button
                                type="button"
                                data-open-review-details
                                class="inline-flex sm:hidden items-center gap-1.5 text-[11px] font-bold text-teal-dark hover:text-teal transition"
                            >
                                View details
                                <x-lucide-chevron-right class="w-3.5 h-3.5" />
                            </button>


                            <div class="ml-auto flex items-center gap-2">

                                <span
                                    data-reply-status
                                    class="inline-flex items-center gap-1 text-[10px] font-semibold
                                        {{ $hasReply
                                            ? 'text-teal-dark'
                                            : 'text-navy/35'
                                        }}"
                                >
                                    @if ($hasReply)

                                        <x-lucide-circle-check class="w-3.5 h-3.5" />
                                        Replied

                                    @else

                                        <x-lucide-clock-3 class="w-3.5 h-3.5" />
                                        Awaiting reply

                                    @endif
                                </span>

                            </div>

                        </div>

                    </div>

                </article>

            @endforeach

        </section>


        {{-- No filtered results --}}
        <section
            id="feedbackNoResults"
            hidden
            class="bg-white border border-gray-border rounded-xl py-12 text-center"
        >

            <div class="w-11 h-11 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                <x-lucide-search-x class="w-5 h-5" />
            </div>

            <p class="text-sm font-semibold text-navy/55 mt-3">
                No matching reviews
            </p>

            <p class="text-xs text-navy/35 mt-1">
                Try another buyer, product, rating, or reply filter.
            </p>

        </section>

    @endif

</div>


{{-- =========================================================
    REVIEW DETAILS MODAL
========================================================= --}}
<div id="reviewDetailsModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div data-close-review-details class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-xl max-h-[90vh] overflow-y-auto content-scrollbar">

        <div class="sticky top-0 z-10 bg-white flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p class="text-base font-bold text-navy">
                    Review Details
                </p>

                <p id="detailsReviewOrder" class="text-[11px] text-navy/40 mt-0.5"></p>

            </div>


            <button
                type="button"
                data-close-review-details
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <div class="px-5 py-5 space-y-5">

            {{-- Buyer --}}
            <div class="flex items-start gap-3">

                <div
                    id="detailsBuyerAvatar"
                    class="w-11 h-11 rounded-full bg-navy text-white flex items-center justify-center text-xs font-bold shrink-0"
                ></div>


                <div class="min-w-0 flex-1">

                    <div class="flex flex-wrap items-center gap-2">

                        <p id="detailsBuyer" class="text-sm font-bold text-navy"></p>

                        <span
                            id="detailsVerified"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-teal/10 text-[10px] font-bold text-teal-dark"
                        >
                            <x-lucide-badge-check class="w-3 h-3" />
                            Verified Purchase
                        </span>

                    </div>


                    <div id="detailsStars" class="flex items-center gap-0.5 mt-1.5"></div>

                    <p id="detailsReviewDate" class="text-[10px] text-navy/35 mt-1"></p>

                </div>

            </div>


            {{-- Product --}}
            <div class="rounded-xl border border-gray-border p-3">

                <div class="flex items-center gap-3">

                    <div class="w-14 h-14 rounded-xl border border-gray-border bg-gray-bg overflow-hidden shrink-0">

                        <img
                            id="detailsProductImage"
                            src=""
                            alt=""
                            class="w-full h-full object-cover"
                        >

                    </div>


                    <div>

                        <p id="detailsProduct" class="text-xs font-semibold text-navy"></p>

                        <p id="detailsVariant" class="text-[11px] text-navy/45 mt-0.5"></p>

                        <p id="detailsSku" class="text-[10px] text-navy/30 mt-0.5"></p>

                    </div>

                </div>

            </div>


            {{-- Comment --}}
            <div>

                <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                    Customer Comment
                </p>

                <div class="mt-2 rounded-xl bg-gray-bg p-3">

                    <p id="detailsComment" class="text-xs text-navy/70 leading-relaxed"></p>

                </div>

            </div>


            {{-- Media --}}
            <div id="detailsMediaWrap">

                <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                    Buyer Photos
                </p>

                <div
                    id="detailsMedia"
                    class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2"
                ></div>

            </div>


            {{-- Seller reply --}}
            <div id="detailsReplyWrap">

                <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                    Seller Reply
                </p>

                <div class="mt-2 rounded-xl border border-teal/20 bg-teal/5 p-3">

                    <p id="detailsReply" class="text-xs text-navy/65 leading-relaxed"></p>

                    <p id="detailsReplyDate" class="text-[9px] text-navy/30 mt-2"></p>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    REPLY MODAL
========================================================= --}}
<div id="replyModal" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div data-close-reply class="absolute inset-0 bg-navy/45"></div>


    <div class="relative bg-white rounded-2xl shadow-panel w-full max-w-lg">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">

            <div>

                <p id="replyModalTitle" class="text-base font-bold text-navy">
                    Reply to Review
                </p>

                <p id="replyReviewMeta" class="text-[11px] text-navy/40 mt-0.5"></p>

            </div>


            <button
                type="button"
                data-close-reply
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <div class="p-5">

            <div class="rounded-xl bg-gray-bg p-3">

                <div class="flex items-center gap-1.5">

                    <div id="replyStars" class="flex items-center gap-0.5"></div>

                    <span id="replyRatingLabel" class="text-[11px] font-semibold text-navy/50"></span>

                </div>


                <p id="replyCustomerComment" class="text-xs text-navy/65 mt-2 leading-relaxed"></p>

            </div>


            <div class="mt-4">

                <div class="flex items-center justify-between gap-3">

                    <label
                        for="sellerReplyInput"
                        class="text-[10px] font-bold uppercase tracking-wide text-navy/35"
                    >
                        Your Response
                    </label>

                    <span
                        id="replyCharacterCount"
                        class="text-[10px] text-navy/30"
                    >
                        0 / 500
                    </span>

                </div>


                <textarea
                    id="sellerReplyInput"
                    rows="5"
                    maxlength="500"
                    placeholder="Thank the buyer, acknowledge their feedback, and keep your response helpful and professional..."
                    class="mt-2 w-full rounded-xl border border-gray-border px-3 py-2.5 text-xs text-navy placeholder:text-navy/25 resize-none focus:outline-none focus:border-teal/50"
                ></textarea>


                <p class="text-[10px] text-navy/35 mt-1.5">
                    Your reply will be visible to buyers viewing this review.
                </p>

            </div>


            {{-- Quick reply helpers --}}
            <div class="mt-4">

                <p class="text-[10px] font-bold uppercase tracking-wide text-navy/35">
                    Quick Starters
                </p>


                <div class="mt-2 flex flex-wrap gap-2">

                    <button
                        type="button"
                        data-reply-template="Thank you so much for your feedback! We’re happy that you enjoyed your order."
                        class="px-2.5 py-1.5 rounded-lg border border-gray-border text-[10px] font-semibold text-navy/50 hover:bg-gray-bg transition"
                    >
                        Thank buyer
                    </button>


                    <button
                        type="button"
                        data-reply-template="Thank you for letting us know. We appreciate the feedback and we’ll use it to improve our product and service."
                        class="px-2.5 py-1.5 rounded-lg border border-gray-border text-[10px] font-semibold text-navy/50 hover:bg-gray-bg transition"
                    >
                        Acknowledge feedback
                    </button>


                    <button
                        type="button"
                        data-reply-template="We’re sorry about your experience. Thank you for bringing this to our attention — we’re reviewing what happened so we can improve."
                        class="px-2.5 py-1.5 rounded-lg border border-gray-border text-[10px] font-semibold text-navy/50 hover:bg-gray-bg transition"
                    >
                        Apologize
                    </button>

                </div>

            </div>


            <div class="mt-5 flex items-center justify-end gap-2">

                <button
                    type="button"
                    data-close-reply
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    id="saveReplyButton"
                    disabled
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy/90 transition disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Post Reply
                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    REVIEW MEDIA MODAL
========================================================= --}}
<div id="reviewMediaModal" hidden class="fixed inset-0 z-[55] flex items-center justify-center p-4">

    <div data-close-review-media class="absolute inset-0 bg-navy/60"></div>


    <div class="relative w-full max-w-3xl">

        <div class="flex justify-end mb-2">

            <button
                type="button"
                data-close-review-media
                class="w-9 h-9 rounded-full bg-white text-navy flex items-center justify-center shadow-panel hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <div class="rounded-2xl overflow-hidden bg-white shadow-panel">

            <img
                id="reviewMediaImage"
                src=""
                alt="Buyer review photo"
                class="w-full max-h-[78vh] object-contain bg-navy/5"
            >

        </div>

    </div>

</div>


{{-- =========================================================
    DEMO TOAST
========================================================= --}}
<div
    id="feedbackToast"
    hidden
    class="fixed right-4 bottom-4 z-[60] max-w-sm rounded-xl border border-teal/25 bg-white shadow-panel px-4 py-3"
>

    <div class="flex items-start gap-3">

        <div class="w-8 h-8 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
            <x-lucide-circle-check class="w-4 h-4" />
        </div>


        <div>

            <p class="text-xs font-bold text-navy">
                Feedback updated
            </p>

            <p id="feedbackToastMessage" class="text-[11px] text-navy/45 mt-0.5"></p>

        </div>

    </div>

</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const cards = Array.from(
        document.querySelectorAll('[data-review-card]')
    );

    const tabs = Array.from(
        document.querySelectorAll('[data-feedback-tab]')
    );

    const searchInput =
        document.getElementById('feedbackSearch');

    const ratingFilter =
        document.getElementById('feedbackRatingFilter');

    const sortSelect =
        document.getElementById('feedbackSort');

    const cardsWrap =
        document.getElementById('feedbackCards');

    const visibleCount =
        document.getElementById('feedbackVisibleCount');

    const noResults =
        document.getElementById('feedbackNoResults');


    const detailsModal =
        document.getElementById('reviewDetailsModal');

    const replyModal =
        document.getElementById('replyModal');

    const mediaModal =
        document.getElementById('reviewMediaModal');


    const replyInput =
        document.getElementById('sellerReplyInput');

    const saveReplyButton =
        document.getElementById('saveReplyButton');

    const characterCount =
        document.getElementById('replyCharacterCount');


    const toast =
        document.getElementById('feedbackToast');

    const toastMessage =
        document.getElementById('feedbackToastMessage');


    let activeFilter = 'all';
    let activeCard = null;
    let activeReview = null;
    let toastTimer = null;


    function parseReview(card) {

        try {

            return JSON.parse(
                card.getAttribute('data-review') || '{}'
            );

        } catch (error) {

            return {};
        }
    }


    function setBodyLock(locked) {

        document.body.style.overflow =
            locked ? 'hidden' : '';
    }


    function showToast(message) {

        if (!toast) {
            return;
        }


        toastMessage.textContent =
            message;


        toast.hidden =
            false;


        if (toastTimer) {
            clearTimeout(toastTimer);
        }


        toastTimer =
            setTimeout(function () {

                toast.hidden = true;

            }, 3200);
    }


    function starHtml(rating) {

        let html = '';


        for (let i = 1; i <= 5; i++) {

            const active =
                i <= Number(rating || 0);


            html += `
                <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="${active ? 'currentColor' : 'none'}"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="${active ? 'text-yellow' : 'text-gray-border'}"
                >
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            `;
        }


        return html;
    }


    /* ---------------------------------------------------------
       FILTERING + SORTING
    --------------------------------------------------------- */
    function matchesMainFilter(card) {

        const rating =
            Number(card.dataset.rating || 0);

        const replied =
            card.dataset.replied === '1';


        if (activeFilter === 'all') {
            return true;
        }


        if (activeFilter === 'unreplied') {
            return !replied;
        }


        if (activeFilter === 'replied') {
            return replied;
        }


        if (activeFilter === 'attention') {
            return rating <= 3;
        }


        if (/^[1-5]$/.test(activeFilter)) {
            return rating === Number(activeFilter);
        }


        return true;
    }


    function applyFilters() {

        const query =
            (searchInput?.value || '')
                .trim()
                .toLowerCase();


        const selectedRating =
            ratingFilter?.value || '';


        let shown = 0;


        cards.forEach(function (card) {

            const rating =
                String(card.dataset.rating || '');


            const matchesFilter =
                matchesMainFilter(card);


            const matchesRating =
                !selectedRating ||
                rating === selectedRating;


            const matchesSearch =
                !query ||
                (card.dataset.search || '')
                    .includes(query);


            const show =
                matchesFilter &&
                matchesRating &&
                matchesSearch;


            card.hidden =
                !show;


            if (show) {
                shown++;
            }
        });


        if (visibleCount) {
            visibleCount.textContent = shown;
        }


        if (noResults) {
            noResults.hidden = shown !== 0;
        }


        tabs.forEach(function (tab) {

            const active =
                tab.dataset.feedbackTab === activeFilter;


            tab.classList.toggle(
                'feedback-tab-active',
                active
            );


            tab.classList.toggle(
                'text-navy/50',
                !active
            );


            tab.classList.toggle(
                'hover:bg-gray-bg',
                !active
            );
        });
    }


    function applySort() {

        if (!cardsWrap) {
            return;
        }


        const mode =
            sortSelect?.value || 'newest';


        const sorted =
            [...cards].sort(function (a, b) {

                const aRating =
                    Number(a.dataset.rating || 0);

                const bRating =
                    Number(b.dataset.rating || 0);


                const aHelpful =
                    Number(a.dataset.helpful || 0);

                const bHelpful =
                    Number(b.dataset.helpful || 0);


                if (mode === 'rating-high') {
                    return bRating - aRating;
                }


                if (mode === 'rating-low') {
                    return aRating - bRating;
                }


                if (mode === 'helpful') {
                    return bHelpful - aHelpful;
                }


                /*
                 * Demo collection is already newest-first.
                 * Preserve original DOM order for "newest".
                 */
                return cards.indexOf(a) - cards.indexOf(b);
            });


        sorted.forEach(function (card) {
            cardsWrap.appendChild(card);
        });
    }


    tabs.forEach(function (tab) {

        tab.addEventListener('click', function () {

            activeFilter =
                tab.dataset.feedbackTab || 'all';


            applyFilters();
        });
    });


    document
        .querySelectorAll('[data-summary-filter]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                activeFilter =
                    button.dataset.summaryFilter || 'all';


                applyFilters();
            });
        });


    document
        .querySelectorAll('[data-rating-filter]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const star =
                    button.dataset.ratingFilter || '';


                activeFilter =
                    star;


                if (ratingFilter) {
                    ratingFilter.value = star;
                }


                applyFilters();
            });
        });


    searchInput?.addEventListener(
        'input',
        applyFilters
    );


    ratingFilter?.addEventListener(
        'change',
        applyFilters
    );


    sortSelect?.addEventListener(
        'change',
        function () {

            applySort();
            applyFilters();
        }
    );


    document
        .getElementById('feedbackClearFilters')
        ?.addEventListener('click', function () {

            activeFilter = 'all';


            if (searchInput) {
                searchInput.value = '';
            }


            if (ratingFilter) {
                ratingFilter.value = '';
            }


            if (sortSelect) {
                sortSelect.value = 'newest';
            }


            applySort();
            applyFilters();
        });


    /* ---------------------------------------------------------
       MEDIA VIEWER
    --------------------------------------------------------- */
    function openMedia(url) {

        if (!url) {
            return;
        }


        document
            .getElementById('reviewMediaImage')
            .src =
                url;


        mediaModal.hidden =
            false;


        setBodyLock(true);
    }


    document
        .querySelectorAll('[data-open-review-media]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const card =
                    button.closest('[data-review-card]');


                const review =
                    parseReview(card);


                const index =
                    Number(button.dataset.mediaIndex || 0);


                openMedia(
                    review.media?.[index] || ''
                );
            });
        });


    document
        .querySelectorAll('[data-close-review-media]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                mediaModal.hidden = true;


                setBodyLock(
                    !detailsModal.hidden ||
                    !replyModal.hidden
                );
            });
        });


    /* ---------------------------------------------------------
       REVIEW DETAILS
    --------------------------------------------------------- */
    function openDetails(card) {

        const review =
            parseReview(card);


        document
            .getElementById('detailsReviewOrder')
            .textContent =
                review.order_id +
                ' · Review #' +
                review.id;


        document
            .getElementById('detailsBuyerAvatar')
            .textContent =
                review.buyer_initials || '';


        document
            .getElementById('detailsBuyer')
            .textContent =
                review.buyer || '';


        document
            .getElementById('detailsVerified')
            .hidden =
                !review.verified_purchase;


        document
            .getElementById('detailsStars')
            .innerHTML =
                starHtml(review.rating);


        document
            .getElementById('detailsReviewDate')
            .textContent =
                review.created_at_label || '';


        document
            .getElementById('detailsProduct')
            .textContent =
                review.product || '';


        document
            .getElementById('detailsVariant')
            .textContent =
                review.variant || 'Default variant';


        document
            .getElementById('detailsSku')
            .textContent =
                'SKU: ' +
                (review.sku || '—');


        const productImage =
            document.getElementById('detailsProductImage');


        if (review.product_image) {

            productImage.src =
                review.product_image;

            productImage.parentElement.hidden =
                false;

        } else {

            productImage.removeAttribute('src');

            productImage.parentElement.hidden =
                true;
        }


        document
            .getElementById('detailsComment')
            .textContent =
                '“' +
                (review.comment || '') +
                '”';


        const mediaWrap =
            document.getElementById('detailsMediaWrap');


        const mediaGrid =
            document.getElementById('detailsMedia');


        mediaGrid.innerHTML = '';


        if (review.media?.length) {

            mediaWrap.hidden = false;


            review.media.forEach(function (url) {

                const button =
                    document.createElement('button');


                button.type =
                    'button';


                button.className =
                    'rounded-xl overflow-hidden border border-gray-border h-28 bg-gray-bg';


                button.innerHTML =
                    `<img src="${url}" alt="Buyer review photo" class="w-full h-full object-cover">`;


                button.addEventListener(
                    'click',
                    function () {

                        openMedia(url);
                    }
                );


                mediaGrid.appendChild(button);
            });

        } else {

            mediaWrap.hidden = true;
        }


        const replyWrap =
            document.getElementById('detailsReplyWrap');


        if (review.reply) {

            replyWrap.hidden =
                false;


            document
                .getElementById('detailsReply')
                .textContent =
                    review.reply;


            document
                .getElementById('detailsReplyDate')
                .textContent =
                    review.reply_date || '';

        } else {

            replyWrap.hidden =
                true;
        }


        detailsModal.hidden =
            false;


        setBodyLock(true);
    }


    document
        .querySelectorAll('[data-open-review-details]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                openDetails(
                    button.closest('[data-review-card]')
                );
            });
        });


    document
        .querySelectorAll('[data-close-review-details]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                detailsModal.hidden = true;
                setBodyLock(false);
            });
        });


    /* ---------------------------------------------------------
       REPLY
    --------------------------------------------------------- */
    function refreshReplyButton() {

        const length =
            (replyInput?.value || '')
                .trim()
                .length;


        if (characterCount) {

            characterCount.textContent =
                (replyInput?.value.length || 0) +
                ' / 500';
        }


        if (saveReplyButton) {

            saveReplyButton.disabled =
                length === 0;
        }
    }


    function openReply(card, mode) {

        activeCard =
            card;


        activeReview =
            parseReview(card);


        document
            .getElementById('replyModalTitle')
            .textContent =
                mode === 'edit'
                    ? 'Edit Your Reply'
                    : 'Reply to Review';


        document
            .getElementById('replyReviewMeta')
            .textContent =
                activeReview.buyer +
                ' · ' +
                activeReview.product;


        document
            .getElementById('replyStars')
            .innerHTML =
                starHtml(activeReview.rating);


        document
            .getElementById('replyRatingLabel')
            .textContent =
                activeReview.rating +
                '.0';


        document
            .getElementById('replyCustomerComment')
            .textContent =
                '“' +
                (activeReview.comment || '') +
                '”';


        replyInput.value =
            mode === 'edit'
                ? (activeReview.reply || '')
                : '';


        saveReplyButton.textContent =
            mode === 'edit'
                ? 'Save Changes'
                : 'Post Reply';


        refreshReplyButton();


        replyModal.hidden =
            false;


        setBodyLock(true);


        setTimeout(function () {
            replyInput?.focus();
        }, 50);
    }


    document
        .querySelectorAll('[data-reply-review]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const card =
                    button.closest('[data-review-card]');


                openReply(
                    card,
                    button.dataset.replyMode || 'new'
                );
            });
        });


    replyInput?.addEventListener(
        'input',
        refreshReplyButton
    );


    document
        .querySelectorAll('[data-reply-template]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const template =
                    button.dataset.replyTemplate || '';


                if (!replyInput) {
                    return;
                }


                replyInput.value =
                    template;


                refreshReplyButton();


                replyInput.focus();
            });
        });


    document
        .querySelectorAll('[data-close-reply]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                replyModal.hidden =
                    true;


                setBodyLock(false);
            });
        });


    saveReplyButton?.addEventListener(
        'click',
        function () {

            if (!activeCard || !activeReview) {
                return;
            }


            const reply =
                (replyInput?.value || '')
                    .trim();


            if (!reply) {
                return;
            }


            const wasReplied =
                activeCard.dataset.replied === '1';


            activeReview.reply =
                reply;


            activeReview.reply_date =
                'Just now';


            activeCard.dataset.replied =
                '1';


            activeCard.setAttribute(
                'data-review',
                JSON.stringify(activeReview)
            );


            let replyDisplay =
                activeCard.querySelector('[data-reply-display]');


            const noReply =
                activeCard.querySelector('[data-no-reply]');


            if (!replyDisplay) {

                replyDisplay =
                    document.createElement('div');


                replyDisplay.setAttribute(
                    'data-reply-display',
                    ''
                );


                replyDisplay.className =
                    'rounded-xl border border-teal/20 bg-teal/5 p-3';


                replyDisplay.innerHTML = `
                    <div class="flex items-start gap-3">

                        <div class="w-8 h-8 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center shrink-0">
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M3 9l9-7 9 7"></path>
                                <path d="M5 10v10h14V10"></path>
                                <path d="M9 20v-6h6v6"></path>
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center justify-between gap-2">

                                <div>

                                    <p class="text-[10px] font-bold uppercase tracking-wide text-teal-dark">
                                        Your Reply
                                    </p>

                                    <p data-reply-date class="text-[9px] text-navy/30 mt-0.5">
                                        Just now
                                    </p>

                                </div>

                                <button
                                    type="button"
                                    data-reply-review
                                    data-reply-mode="edit"
                                    class="text-[10px] font-bold text-navy/40 hover:text-teal-dark transition"
                                >
                                    Edit reply
                                </button>

                            </div>

                            <p data-reply-text class="text-xs text-navy/65 mt-2 leading-relaxed"></p>

                        </div>

                    </div>
                `;


                const wrapper =
                    noReply?.parentElement;


                if (wrapper && noReply) {

                    wrapper.replaceChild(
                        replyDisplay,
                        noReply
                    );
                }


                const editButton =
                    replyDisplay.querySelector('[data-reply-review]');


                editButton?.addEventListener(
                    'click',
                    function () {

                        openReply(
                            activeCard,
                            'edit'
                        );
                    }
                );
            }


            const replyText =
                replyDisplay?.querySelector('[data-reply-text]');


            const replyDate =
                replyDisplay?.querySelector('[data-reply-date]');


            if (replyText) {
                replyText.textContent = reply;
            }


            if (replyDate) {
                replyDate.textContent = 'Just now';
            }


            const status =
                activeCard.querySelector('[data-reply-status]');


            if (status) {

                status.className =
                    'inline-flex items-center gap-1 text-[10px] font-semibold text-teal-dark';


                status.innerHTML = `
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <path d="m9 11 3 3L22 4"></path>
                    </svg>
                    Replied
                `;
            }


            replyModal.hidden =
                true;


            setBodyLock(false);


            showToast(
                wasReplied
                    ? 'Your reply was updated.'
                    : 'Your reply was posted.'
            );


            applyFilters();
        }
    );


    /* ---------------------------------------------------------
       ESCAPE
    --------------------------------------------------------- */
    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key !== 'Escape') {
                return;
            }


            if (!mediaModal.hidden) {

                mediaModal.hidden =
                    true;


                setBodyLock(
                    !detailsModal.hidden ||
                    !replyModal.hidden
                );


                return;
            }


            if (!replyModal.hidden) {

                replyModal.hidden =
                    true;


                setBodyLock(false);
            }


            if (!detailsModal.hidden) {

                detailsModal.hidden =
                    true;


                setBodyLock(false);
            }
        }
    );


    applySort();
    applyFilters();
});
</script>
@endpush

@endsection
