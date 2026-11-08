@extends('seller.partials.layout')

@section('title', 'Customer Feedback')

@section('content')
{{-- Persisted Product Reviews do not establish Seller feedback reads, reply persistence or store-rating semantics. --}}
<section id="sellerFeedback" aria-labelledby="feedbackTitle" class="mx-auto max-w-5xl space-y-5">
    <div>
        <h1 id="feedbackTitle" class="text-2xl font-bold text-navy">Customer Feedback</h1>
        <p class="mt-1 text-sm text-navy/55">Feedback availability on ShopHop.</p>
    </div>

    {{-- Remove fabricated reviews and posted/updated reply outcomes rather than simulate unsupported account state. --}}
    <div class="rounded-xl border border-gray-border bg-white p-5 sm:p-6">
        <span class="inline-flex rounded-full bg-teal/10 px-3 py-1 text-xs font-semibold text-teal-dark">Unavailable</span>
        <h2 class="mt-4 text-lg font-semibold text-navy">Seller feedback viewing is unavailable.</h2>
        <p class="mt-2 text-sm text-navy/60">No live reviews, ratings or feedback history are displayed.</p>
        <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-navy/60">
            <li>This page does not load customer reviews for your products.</li>
            <li>Seller replies cannot be posted, edited or stored here.</li>
            <li>Review moderation and helpful-vote actions are unavailable.</li>
            <li>No Seller or store rating is calculated here.</li>
        </ul>
    </div>
</section>
@endsection
