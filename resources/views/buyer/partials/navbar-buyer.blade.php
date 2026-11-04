{{-- ShopHop Buyer navigation delegates visual structure and interaction to the shared shopper header. --}}
@php
    $shHeaderBuyer = auth()->user();
    $shHeaderIsBuyer = $shHeaderBuyer && $shHeaderBuyer->account_type === 'buyer';
    $likedProductIds = $shHeaderIsBuyer && $shHeaderBuyer->buyer?->id && \Illuminate\Support\Facades\Schema::hasTable('favorites')
        ? \App\Models\Buyer\Favorite\Favorite::query()
            ->where('buyer_id', $shHeaderBuyer->buyer->id)
            ->pluck('product_id')->map(fn ($id) => (int) $id)->all()
        : [];
@endphp
@include('partials.shopper-header', ['likedProductIds' => $likedProductIds])
@if ($shHeaderIsBuyer)
    @include('buyer.partials.favorite-scripts')
@endif
