<?php

namespace App\Services;

use App\Models\Buyer\Review\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class SellerFeedbackService
{
    public function listing(Request $request): array
    {
        $filters = $request->validate([
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        if (! Schema::hasTable('reviews')) {
            return ['reviews' => new LengthAwarePaginator([], 0, 20, 1), 'total' => 0, 'average' => 0];
        }
        $base = Review::query()->whereHas('product', fn ($q) => $q->where('seller_id', $request->user()->id));
        $total = (clone $base)->count();
        $average = round((float) (clone $base)->avg('rating'), 1);
        $reviews = $base->with(['product:id,name,seller_id', 'buyer:id,first_name,last_name'])
            ->when($filters['rating'] ?? null, fn ($q, $rating) => $q->where('rating', $rating))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('comment', 'like', '%'.$search.'%')
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', '%'.$search.'%'));
            }))
            ->latest()->orderByDesc('id')->paginate(20)->withQueryString();
        $reviews->through(fn ($review) => [
            'id' => $review->id, 'product_name' => $review->product->name,
            'buyer_name' => trim(($review->buyer?->first_name ?? 'Buyer').' '.mb_substr($review->buyer?->last_name ?? '', 0, 1)),
            'rating' => $review->rating, 'comment' => $review->comment,
            'date' => $review->created_at?->toDateString(),
            'seller_reply' => $review->seller_reply,
        ]);
        return compact('reviews', 'total', 'average');
    }

    public function reply(Request $request, int $review): void
    {
        $owned = Review::query()->whereHas('product', fn ($q) => $q->where('seller_id', $request->user()->id))->findOrFail($review);
        $data = $request->validate(['reply' => ['required', 'string', 'max:2000', 'not_regex:/^\s*$/u']]);
        $owned->forceFill(['seller_reply' => trim($data['reply']), 'seller_replied_at' => now()])->save();
    }
}
