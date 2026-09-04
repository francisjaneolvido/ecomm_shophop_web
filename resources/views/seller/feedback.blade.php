@extends('seller.partials.layout')

@section('title', 'Customer Feedback')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    */
    $reviews = collect($reviews ?? [
        [
            'id' => 1,
            'buyer' => 'Trisha Ang',
            'product' => 'Handwoven Rattan Basket',
            'rating' => 5,
            'comment' => 'Ang ganda ng gawa, mabilis din yung delivery. Sulit!',
            'date' => '2 days ago',
            'reply' => null,
        ],
        [
            'id' => 2,
            'buyer' => 'Miguel Ortiz',
            'product' => 'Barako Coffee Beans 250g',
            'rating' => 4,
            'comment' => 'Masarap yung kape, medyo natagalan lang ng konti sa packaging.',
            'date' => '5 days ago',
            'reply' => 'Salamat po sa order! Pinapaganda pa namin yung packaging namin, thank you sa patience!',
        ],
        [
            'id' => 3,
            'buyer' => 'Anna Reyes',
            'product' => 'Capiz Shell Wall Lamp',
            'rating' => 3,
            'comment' => 'Maganda naman pero medyo may gasgas nang dumating.',
            'date' => '1 week ago',
            'reply' => null,
        ],
    ]);

    $averageRating = $reviews->isEmpty() ? 0 : round($reviews->avg('rating'), 1);
    $ratingCounts = collect(range(5, 1))->mapWithKeys(fn ($star) => [$star => $reviews->where('rating', $star)->count()]);
    $totalReviews = $reviews->count();
@endphp


<style>
    #sellerFeedback .dash-gap { gap: 1rem; }
    #sellerFeedback .dash-section { margin-bottom: 1.25rem; }
</style>


<div id="sellerFeedback">

    <header class="dash-section">
        <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
            Customer Feedback
        </h1>
        <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
            See what buyers are saying and reply directly to their reviews.
        </p>
    </header>


    {{-- =========================================================
        RATING SUMMARY
    ========================================================= --}}
    <section class="dash-section bg-white border border-gray-border rounded-xl p-4 grid grid-cols-1 sm:grid-cols-[auto_1fr] dash-gap items-center">

        <div class="text-center sm:pr-6 sm:border-r sm:border-gray-border">
            <p class="text-3xl font-bold text-navy">{{ $averageRating }}</p>
            <div class="flex items-center justify-center gap-0.5 mt-1">
                @for ($i = 1; $i <= 5; $i++)
                    <x-lucide-star class="w-3.5 h-3.5 {{ $i <= round($averageRating) ? 'text-yellow fill-yellow' : 'text-gray-border' }}" />
                @endfor
            </div>
            <p class="text-[9px] text-navy/35 mt-1">{{ $totalReviews }} review{{ $totalReviews === 1 ? '' : 's' }}</p>
        </div>

        <div class="space-y-1.5">
            @foreach ($ratingCounts as $star => $count)
                @php $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0; @endphp
                <div class="flex items-center gap-2">
                    <span class="text-[9px] text-navy/40 w-8 shrink-0">{{ $star }} star</span>
                    <div class="h-1.5 flex-1 rounded-full bg-gray-bg overflow-hidden">
                        <div class="h-full rounded-full bg-yellow" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-[9px] text-navy/35 w-6 text-right shrink-0">{{ $count }}</span>
                </div>
            @endforeach
        </div>

    </section>


    {{-- =========================================================
        REVIEWS LIST
    ========================================================= --}}
    @if ($reviews->isEmpty())

        <div class="bg-white border border-gray-border rounded-xl py-14 text-center">
            <div class="w-10 h-10 mx-auto rounded-lg bg-teal-light text-teal-dark flex items-center justify-center">
                <x-lucide-message-square class="w-4.5 h-4.5" />
            </div>
            <p class="text-xs font-semibold text-navy/50 mt-3">No reviews yet</p>
            <p class="text-[10px] text-navy/35 mt-1">Buyer feedback will appear here after their orders are completed.</p>
        </div>

    @else

        <div class="space-y-3">
            @foreach ($reviews as $review)
                <div class="bg-white border border-gray-border rounded-xl p-4">

                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-navy">{{ $review['buyer'] }}</p>
                            <p class="text-[10px] text-navy/40 mt-0.5">{{ $review['product'] }} · {{ $review['date'] }}</p>
                        </div>
                        <div class="flex items-center gap-0.5">
                            @for ($i = 1; $i <= 5; $i++)
                                <x-lucide-star class="w-3.5 h-3.5 {{ $i <= $review['rating'] ? 'text-yellow fill-yellow' : 'text-gray-border' }}" />
                            @endfor
                        </div>
                    </div>

                    <p class="text-[11px] text-navy/70 mt-2.5">
                        {{ $review['comment'] }}
                    </p>

                    @if ($review['reply'])
                        <div class="mt-3 ml-3 pl-3 border-l-2 border-teal/30">
                            <p class="text-[9px] font-semibold text-teal-dark">Your reply</p>
                            <p class="text-[11px] text-navy/60 mt-0.5">{{ $review['reply'] }}</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('seller.feedback') }}" class="mt-3 flex items-start gap-2">
                            @csrf
                            <input type="hidden" name="review_id" value="{{ $review['id'] }}">
                            <input
                                type="text"
                                name="reply"
                                placeholder="Write a reply..."
                                required
                                class="flex-1 h-8 px-3 rounded-lg border border-gray-border text-[11px]
                                       focus:outline-none focus:border-teal/50"
                            >
                            <button
                                type="submit"
                                class="h-8 px-3 rounded-lg bg-navy hover:bg-navy/90 text-[11px] font-semibold text-white transition-colors"
                            >
                                Reply
                            </button>
                        </form>
                    @endif

                </div>
            @endforeach
        </div>

    @endif

</div>

@endsection