<?php

namespace App\Services;

use App\Models\Buyer\Order\Order;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

/** The web Home and mobile Home share eligibility, ordering and section limits. */
class MarketplaceCatalogService
{
    public function query(): Builder
    {
        $query = Product::query()->publiclyDiscoverable()->with(['images', 'seller']);
        if (Schema::hasTable('reviews')) {
            $query->withAvg('reviews as avg_rating', 'rating')->withCount('reviews');
        }
        if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
            $query->withSum(['orderItems as completed_units_sold' => function ($items) {
                $items->whereHas('order', fn ($order) => $order->where('status', Order::STATUS_COMPLETED));
            }], 'quantity');
        }
        return $query;
    }

    public function sections(): array
    {
        if (! Schema::hasTable('products')) {
            return ['deals' => collect(), 'top' => collect(), 'discover' => collect(), 'has_completed_sales' => false];
        }
        $top = $this->query();
        if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
            $top->orderByDesc('completed_units_sold');
        }
        $top = $top->latest('products.created_at')->orderByDesc('products.id')->take(12)->get();
        return [
            'deals' => $this->query()->withActiveDiscount()->orderByDesc('discount')
                ->latest('products.created_at')->orderByDesc('products.id')->take(12)->get(),
            'top' => $top,
            'discover' => $this->query()->latest('products.created_at')->orderByDesc('products.id')->take(24)->get(),
            'has_completed_sales' => $top->contains(fn ($product) => (int) $product->completed_units_sold > 0),
        ];
    }

    public function banners(): array
    {
        return collect(config('shophop_marketplace.banners', []))->map(fn ($banner) => [
            ...$banner, 'image' => asset($banner['image']),
        ])->all();
    }
}
