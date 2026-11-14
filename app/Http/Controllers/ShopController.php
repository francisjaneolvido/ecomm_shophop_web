<?php

namespace App\Http\Controllers;

use App\Models\Buyer\Favorite\Favorite;
use App\Models\Buyer\Review\Review;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function show(Request $request, Seller $seller)
    {
        abort_unless($seller->user()->where('account_type', 'seller')->where('status', 'approved')->exists(), 404);

        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['newest', 'price_low', 'price_high'])],
        ]);
        $catalog = Product::query()->publiclyDiscoverable()->where('seller_id', $seller->user_id);
        $categories = (clone $catalog)->distinct()->orderBy('category')->pluck('category');
        $category = $data['category'] ?? null;
        if ($category && ! $categories->contains($category)) abort(404);
        if ($category) $catalog->where('category', $category);
        $sort = $data['sort'] ?? 'newest';
        if ($sort === 'price_low') $catalog->orderBy('price')->orderBy('id');
        elseif ($sort === 'price_high') $catalog->orderByDesc('price')->orderByDesc('id');
        else $catalog->latest('created_at')->orderByDesc('id');
        $products = $catalog->with('images')->withSum('orderItems as completed_units_sold', 'quantity')
            ->paginate(20)->withQueryString();

        // A current listing supplies the shop backdrop; the uploaded logo remains its own image.
        $coverProduct = $seller->products()->publiclyDiscoverable()
            ->whereHas('images')->with('images')->latest('created_at')->first()
            ?? $seller->products()->publiclyDiscoverable()->whereNotNull('image')->latest('created_at')->first();
        $coverPath = $coverProduct?->image;
        $coverUrl = $coverPath ? asset('storage/'.ltrim($coverPath, '/')) : $seller->logo_url;

        $productCount = $seller->products()->publiclyDiscoverable()->count();
        $rating = round((float) Review::query()->whereHas('product', fn ($q) =>
            $q->where('seller_id', $seller->user_id))->avg('rating'), 1);
        $ratingCount = Review::query()->whereHas('product', fn ($q) =>
            $q->where('seller_id', $seller->user_id))->count();
        $vouchers = Voucher::query()->where('seller_id', $seller->user_id)->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->orderBy('ends_at')->limit(4)->get();
        $isShopBuyer = auth()->check() && auth()->user()->account_type === 'buyer';
        $favoriteProductIds = $isShopBuyer && auth()->user()->buyer?->id && Schema::hasTable('favorites')
            ? Favorite::where('buyer_id', auth()->user()->buyer->id)->pluck('product_id')->map(fn ($id) => (int) $id)->all()
            : [];

        return view('shop.show', compact('seller', 'products', 'categories', 'category', 'sort',
            'productCount', 'rating', 'ratingCount', 'vouchers', 'isShopBuyer', 'favoriteProductIds', 'coverUrl'));
    }
}
