@extends('seller.partials.layout')
@section('title', 'Customer Feedback')
@section('content')
<section id="sellerFeedback" class="mx-auto max-w-5xl space-y-5">
    <h1 class="text-2xl font-bold text-navy">Customer Feedback</h1>
    <p class="text-sm text-navy/60">Reviews for your products. New feedback refreshes automatically every 15 seconds while you are not editing a reply.</p>
    @if(session('status'))<p role="status" class="rounded-xl bg-teal/10 p-4">{{ session('status') }}</p>@endif
    @if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-red-700">{{ $errors->first() }}</div>@endif
    <form method="GET" class="flex flex-wrap gap-3">
        <input name="search" value="{{ request('search') }}" maxlength="100" placeholder="Search product or comment" aria-label="Search feedback" class="rounded-lg border p-2">
        <select name="rating" aria-label="Rating" class="rounded-lg border p-2">
            <option value="">All ratings</option>
            @foreach(range(5,1) as $rating)<option value="{{ $rating }}" @selected((string) request('rating') === (string) $rating)>{{ $rating }} stars</option>@endforeach
        </select>
        <button class="sh-btn rounded-lg bg-teal-dark px-4 py-2 text-white">Filter</button>
    </form>
    <div id="feedbackResults" class="space-y-4">
        <p class="text-sm text-navy">{{ $total ?? 0 }} product reviews | Average {{ $average ?? 0 }}/5</p>
        @forelse(($reviews ?? collect()) as $review)
            <article class="rounded-xl border border-gray-border bg-white p-5">
                <h2 class="font-semibold text-navy">{{ $review['product_name'] }}</h2>
                <p class="mt-1 text-xs text-navy/60">{{ $review['buyer_name'] }} | {{ $review['rating'] }}/5 | {{ $review['date'] }}</p>
                <p class="mt-3 whitespace-pre-wrap text-sm">{{ $review['comment'] }}</p>
                @if($review['seller_reply'])<p class="mt-3 rounded-lg bg-teal/10 p-3 text-sm whitespace-pre-wrap">Seller reply: {{ $review['seller_reply'] }}</p>@endif
                <form method="POST" action="{{ route('seller.feedback.reply', $review['id']) }}" class="mt-4 space-y-2">
                    @csrf @method('PATCH')
                    <label for="reply{{ $review['id'] }}" class="block text-xs">{{ $review['seller_reply'] ? 'Edit reply' : 'Reply to customer' }}</label>
                    <textarea id="reply{{ $review['id'] }}" name="reply" required maxlength="2000" rows="3" class="w-full rounded-lg border p-2">{{ old('reply', $review['seller_reply']) }}</textarea>
                    <button class="sh-btn rounded-lg bg-teal-dark px-4 py-2 text-sm text-white">Save reply</button>
                </form>
            </article>
        @empty
            <p class="rounded-xl border bg-white p-6 text-sm text-navy/60">No customer reviews found.</p>
        @endforelse
        @if(isset($reviews) && $reviews instanceof \Illuminate\Contracts\Pagination\Paginator){{ $reviews->links() }}@endif
    </div>
</section>
@endsection
@push('scripts')
<script>
(() => {
    let busy = false, dirty = false;
    const root = document.getElementById('sellerFeedback');
    root.addEventListener('input', () => { dirty = true; });
    async function refresh() {
        if (busy || dirty || document.hidden || root.contains(document.activeElement) && document.activeElement.matches('textarea,input,select')) return;
        busy = true;
        try {
            const response = await fetch(location.href, {cache: 'no-store', headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok || response.redirected) return;
            const updated = new DOMParser().parseFromString(await response.text(), 'text/html').getElementById('feedbackResults');
            if (updated && !dirty) document.getElementById('feedbackResults').replaceWith(updated);
        } catch (_) {} finally { busy = false; }
    }
    setInterval(refresh, 15000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
})();
</script>
@endpush
