<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Buyer\Favorite\Favorite;
use App\Models\Buyer\Order\Order;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $buyer = auth()->user()?->buyer;
        $buyerName = $buyer?->first_name ?? 'Buyer';

        $categories = $this->getCategories();
        $trendingProducts = $this->getTrendingProducts();
        $vouchers = $this->getVouchers();
        $orderSummary = $this->getOrderSummary($buyer?->id);
        $activeOrder = $this->getActiveOrder($buyer?->id);
        $recentlyViewed = []; // No product-view persistence exists yet; do not invent history.
        $recommendedProducts = $this->getRecommendedProducts();
        $dealProducts = $this->getDealProducts();
        $newArrivals = $this->getNewArrivals();
        $cartItemCount = Schema::hasTable('cart_items') && $buyer
            ? CartItem::where('buyer_id', $buyer->id)->count() : 0;
        $favoriteCount = Schema::hasTable('favorites') && $buyer
            ? Favorite::where('buyer_id', $buyer->id)->count() : 0;

        return view('buyer.dashboard.dashboard', compact(
            'buyerName', 'categories', 'trendingProducts', 'vouchers', 'orderSummary',
            'activeOrder', 'recentlyViewed', 'recommendedProducts', 'dealProducts',
            'newArrivals', 'cartItemCount', 'favoriteCount'
        ));
    }

    private function activeProductsQuery()
    {
        return Product::query()->publiclyDiscoverable()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withSum('orderItems as sold_count', 'quantity');
    }

    private function productsTableIsAvailable(): bool
    {
        return Schema::hasTable((new Product())->getTable());
    }

    private function formatProduct(Product $product): array
    {
        $price = (float) $product->price;
        $discount = (int) ($product->discount ?? 0);
        $finalPrice = $discount > 0 ? round($price - ($price * $discount / 100), 2) : $price;
        $imagePath = $product->image ? 'storage/'.ltrim($product->image, '/') : 'images/placeholder-product.jpg';

        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'price' => $finalPrice,
            'original_price' => $discount > 0 ? $price : null,
            'rating' => $product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : null,
            'reviews' => (int) ($product->reviews_count ?? 0),
            'sold' => (int) ($product->sold_count ?? 0),
            'image' => asset($imagePath),
            'stock' => (int) $product->stock,
            'shop_id' => $product->seller_id,
        ];
    }

    private function getCategories(): array
    {
        return collect(config('shophop_categories', []))->map(fn ($category) => [
            'name' => $category['name'],
            'icon' => $category['icon'],
        ])->values()->all();
    }

    private function getTrendingProducts(): array
    {
        if (! $this->productsTableIsAvailable()) return [];
        return $this->activeProductsQuery()->orderByDesc('sold_count')->latest('products.created_at')->take(4)->get()
            ->map(fn ($p) => $this->formatProduct($p))->all();
    }

    private function getRecommendedProducts(): array
    {
        if (! $this->productsTableIsAvailable()) return [];
        // Until preference/history scoring exists, show real discoverable products instead of fake recommendations.
        return $this->activeProductsQuery()->latest('products.created_at')->take(5)->get()
            ->map(fn ($p) => $this->formatProduct($p))->all();
    }

    private function getDealProducts(): array
    {
        if (! $this->productsTableIsAvailable()) return [];
        return $this->activeProductsQuery()->where('discount', '>', 0)->latest('products.created_at')->take(6)->get()
            ->map(fn ($p) => $this->formatProduct($p))->all();
    }

    private function getNewArrivals(): array
    {
        if (! $this->productsTableIsAvailable()) return [];
        return $this->activeProductsQuery()->latest('products.created_at')->take(6)->get()
            ->map(fn ($p) => $this->formatProduct($p))->all();
    }

    private function getVouchers(): array
    {
        if (! Schema::hasTable('vouchers')) return [];

        return Voucher::query()->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->latest()->limit(4)->get()->map(function (Voucher $voucher) {
                $value = (float) $voucher->value;
                $benefit = $voucher->type === 'percent' ? rtrim(rtrim(number_format($value, 2), '0'), '.').'%' : '₱'.number_format($value, 0);
                return [
                    'title' => $voucher->name ?: $benefit.' Off',
                    'code' => $voucher->code,
                    'description' => $benefit.' off'.((float) $voucher->min_order_amount > 0 ? ' · Min. ₱'.number_format((float) $voucher->min_order_amount, 0) : ''),
                    'icon' => 'ticket',
                ];
            })->all();
    }

    private function getOrderSummary(?int $buyerId): array
    {
        $counts = ['to-pay' => 0, 'to-ship' => 0, 'to-receive' => 0, 'completed' => 0];
        if ($buyerId && Schema::hasTable('orders')) {
            Order::with('manualCashlessPayment')->where('buyer_id', $buyerId)->get()->each(function (Order $order) use (&$counts) {
                $group = $order->buyerStatusGroup();
                if (array_key_exists($group, $counts)) $counts[$group]++;
            });
        }
        return [
            ['label' => 'To Pay', 'count' => $counts['to-pay'], 'icon' => 'wallet'],
            ['label' => 'To Ship', 'count' => $counts['to-ship'], 'icon' => 'package'],
            ['label' => 'To Receive', 'count' => $counts['to-receive'], 'icon' => 'truck'],
            ['label' => 'Completed', 'count' => $counts['completed'], 'icon' => 'circle-check'],
        ];
    }

    private function getActiveOrder(?int $buyerId): ?array
    {
        if (! $buyerId || ! Schema::hasTable('orders')) return null;

        $order = Order::with([
            'manualCashlessPayment', 'items.product', 'items.variant',
            'pickupRequest.partner', 'pickupRequest.originSortingCenter',
            'delivery.rider', 'delivery.pickupRider', 'delivery.deliveryRider',
            'delivery.originSortingCenter', 'delivery.currentSortingCenter', 'delivery.destinationSortingCenter',
            'delivery.transfers.fromCenter', 'delivery.transfers.toCenter', 'delivery.failureReports.rider',
        ])
            ->where('buyer_id', $buyerId)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->latest()->first();
        if (! $order) return null;

        $item = $order->items->first();
        $image = $item?->product?->image ? 'storage/'.ltrim($item->product->image, '/') : 'images/placeholder-product.jpg';
        $done = fn (array $states) => in_array($order->status, $states, true);

        return [
            'order_number' => '#SHP-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'product_name' => $item?->product?->name ?? ($order->items->count().' item order'),
            'variant' => $item?->variantLabel() ?? 'Order items',
            'quantity' => (int) ($item?->quantity ?? $order->items->sum('quantity')),
            'price' => (float) ($item?->price ?? $order->total_amount),
            'status' => $order->statusLabel(),
            'image' => $image,
            'latest_update' => $order->statusNote(),
            'timeline' => array_slice($order->trackingTimeline(), -5),
            'steps' => [
                ['label' => 'Placed', 'icon' => 'shopping-bag', 'done' => true],
                ['label' => 'Confirmed', 'icon' => 'circle-check', 'done' => $done([Order::STATUS_CONFIRMED, Order::STATUS_PREPARING, Order::STATUS_READY_FOR_PICKUP, Order::STATUS_TO_RECEIVE])],
                ['label' => 'Preparing', 'icon' => 'package', 'done' => $done([Order::STATUS_PREPARING, Order::STATUS_READY_FOR_PICKUP, Order::STATUS_TO_RECEIVE])],
                ['label' => 'In Transit', 'icon' => 'truck', 'done' => $order->status === Order::STATUS_TO_RECEIVE],
                ['label' => 'Delivered', 'icon' => 'house', 'done' => $order->delivery?->status === \App\Models\Logistics\Delivery::DELIVERED],
            ],
        ];
    }
}
