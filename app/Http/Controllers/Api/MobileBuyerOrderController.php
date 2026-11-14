<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Buyer\Order\Order;
use App\Models\Buyer\Review\Review;
use App\Models\Logistics\Delivery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** Read the same Order records used by the web Buyer Orders page. */
class MobileBuyerOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);
        $request->validate(['status_group' => ['nullable', 'in:to-ship,to-receive,to-rate,completed,cancelled']]);
        $reviewsAvailable = Schema::hasTable('reviews');
        $groups = [
            'to-ship' => [Order::STATUS_TO_SHIP, Order::STATUS_CONFIRMED, Order::STATUS_PREPARING, Order::STATUS_READY_FOR_PICKUP],
            'to-receive' => [Order::STATUS_TO_RECEIVE],
            'completed' => [Order::STATUS_COMPLETED],
            'cancelled' => [Order::STATUS_CANCELLED],
        ];
        $statusCounts = Order::where('buyer_id', $buyer->id)->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');
        $counts = ['all' => (int) $statusCounts->sum()];
        foreach ($groups as $key => $statuses) {
            $counts[$key] = (int) $statusCounts->only($statuses)->sum();
        }
        $pending = Order::where('buyer_id', $buyer->id);
        $this->pendingRatings($pending, (int) $buyer->id, $reviewsAvailable);
        $counts['to-rate'] = $pending->count();
        $orders = Order::query()
            ->where('buyer_id', $buyer->id)
            ->when($request->input('status_group') === 'to-rate', fn ($query) => $this->pendingRatings($query, (int) $buyer->id, $reviewsAvailable))
            ->when(isset($groups[$request->input('status_group')]), fn ($query) => $query->whereIn('status', $groups[$request->input('status_group')]))
            ->with(['seller', 'items.product', 'items.variant', 'delivery', 'returnCase'])
            ->latest('id')
            ->paginate(20);
        $reviews = $reviewsAvailable ? Review::where('buyer_id', $buyer->id)
            ->whereIn('product_id', $orders->getCollection()->flatMap(fn ($order) => $order->items->pluck('product_id'))->unique())
            ->orderBy('id')->get()->keyBy('product_id') : collect();

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders->getCollection()->map(fn (Order $order) => $this->resource($order, $reviews, $reviewsAvailable))->values(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'has_more' => $orders->hasMorePages(),
                'status_counts' => $counts,
            ],
        ]);
    }

    private function pendingRatings(Builder $query, int $buyerId, bool $available): Builder
    {
        if (! $available) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('status', Order::STATUS_COMPLETED)->whereHas('items', fn ($items) => $items->whereHas('product')->whereNotExists(fn ($reviews) => $reviews->selectRaw('1')->from('reviews')
            ->whereColumn('reviews.product_id', 'order_items.product_id')->where('reviews.buyer_id', $buyerId)));
    }

    public function rateItem(Request $request, int $order, int $item): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);
        abort_unless(Schema::hasTable('reviews'), 503, 'Product ratings are currently unavailable.');
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
            'videos' => ['nullable', 'array', 'max:2'],
            'videos.*' => ['required', 'file', 'mimetypes:video/mp4', 'extensions:mp4', 'max:30720'],
        ], [
            'images.max' => 'You can attach up to 6 photos.',
            'videos.max' => 'You can attach up to 2 MP4 videos.',
            'images.*.uploaded' => 'A photo could not reach the server. Try again, or remove it and submit without attachments.',
            'videos.*.uploaded' => 'A video could not reach the server. Try again, or remove it and submit without attachments.',
            'images.*.max' => 'Each photo must be 5 MB or smaller.',
            'videos.*.max' => 'Each MP4 video must be 30 MB or smaller.',
            'images.*.image' => 'Choose a valid JPG, PNG, or WebP photo.',
            'images.*.mimes' => 'Choose a JPG, PNG, or WebP photo.',
            'images.*.extensions' => 'Photo filenames must end in JPG, JPEG, PNG, or WebP.',
            'videos.*.mimetypes' => 'Choose a valid MP4 video.',
            'videos.*.extensions' => 'Video filenames must end in MP4.',
            'images.*.required' => 'Remove the unreadable photo and select it again.',
            'videos.*.required' => 'Remove the unreadable video and select it again.',
        ]);
        $attachments = array_merge($request->file('images', []), $request->file('videos', []));
        abort_if($attachments && ! Schema::hasColumn('reviews', 'media'), 503, 'Review media update is not installed yet.');
        $stored = [];
        try {
            $review = DB::transaction(function () use ($buyer, $order, $item, $data, $request, &$stored) {
                // Serializes this Buyer's submissions across repeat purchases and retries without a schema change.
                Buyer::whereKey($buyer->id)->lockForUpdate()->firstOrFail();
                $owned = Order::where('buyer_id', $buyer->id)->whereKey($order)->lockForUpdate()->firstOrFail();
                if ($owned->status !== Order::STATUS_COMPLETED) {
                    throw ValidationException::withMessages(['order' => 'Confirm Order Received before rating your items.']);
                }
                $purchased = $owned->items()->with(['product', 'variant'])->whereKey($item)->firstOrFail();
                abort_unless($purchased->product, 404, 'This product is no longer available for rating.');
                $existing = Review::where('buyer_id', $buyer->id)->where('product_id', $purchased->product_id)->first();
                if ($existing) {
                    return $existing; // Idempotent retry; never silently replace a submitted review.
                }
                $media = [];
                foreach (['images' => 'image', 'videos' => 'video'] as $field => $type) {
                    foreach ($request->file($field, []) as $file) {
                        $path = $file->store('reviews', 'public');
                        if (! $path) {
                            throw new \RuntimeException('Review media could not be stored.');
                        }
                        $stored[] = $path;
                        $media[] = ['type' => $type, 'path' => $path];
                    }
                }

                return Review::create([
                    'buyer_id' => $buyer->id, 'product_id' => $purchased->product_id,
                    'variant_label' => $purchased->variantLabel(), 'rating' => $data['rating'],
                    'comment' => trim($data['comment'] ?? '') ?: null,
                    'image_path' => collect($media)->firstWhere('type', 'image')['path'] ?? null,
                    ...($media ? ['media' => $media] : []),
                ]);
            });
        } catch (\Throwable $error) {
            // Filesystem writes are not transactional; remove only this failed submission's uploads.
            if ($stored) {
                Storage::disk('public')->delete($stored);
            }
            throw $error;
        }

        return response()->json(['success' => true, 'message' => 'Your product rating is saved.',
            'data' => ['review_id' => $review->id, 'rating' => $review->rating]]);
    }

    public function confirmReceipt(Request $request, int $order): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);
        DB::transaction(function () use ($buyer, $order) {
            $owned = Order::with('delivery')
                ->where('buyer_id', $buyer->id)
                ->whereKey($order)
                ->lockForUpdate()
                ->firstOrFail();

            if ($owned->status !== Order::STATUS_TO_RECEIVE
                || $owned->delivery?->status !== Delivery::DELIVERED
                || ! $owned->isPaymentEligible()) {
                throw ValidationException::withMessages([
                    'order' => 'This order is not yet eligible for receipt confirmation.',
                ]);
            }

            $updated = Order::whereKey($owned->id)->where('buyer_id', $buyer->id)
                ->where('status', Order::STATUS_TO_RECEIVE)
                ->update(['status' => Order::STATUS_COMPLETED]);

            if ($updated !== 1) {
                throw ValidationException::withMessages(['order' => 'Order changed. Refresh and review it.']);
            }
        });

        return response()->json(['success' => true, 'message' => 'Order received and completed.']);
    }

    private function resource(Order $order, $reviews, bool $reviewsAvailable): array
    {
        $delivery = $order->delivery;
        $timeline = [
            ['label' => 'Order placed', 'at' => $order->created_at?->toIso8601String()],
        ];
        if ($delivery) {
            foreach ([
                'assigned_at' => 'Pickup rider assigned',
                'picked_up_at' => 'Picked up from seller',
                'at_sorting_center_at' => 'At sorting center',
                'sorted_at' => 'Sorted',
                'delivery_assigned_at' => 'Delivery rider assigned',
                'out_for_delivery_at' => 'Out for delivery',
                'delivered_at' => 'Delivered',
                'delivery_failed_at' => 'Delivery attempt failed',
                'returned_at' => 'Returned',
            ] as $field => $label) {
                if ($delivery->{$field}) {
                    $timeline[] = ['label' => $label, 'at' => $delivery->{$field}->toIso8601String()];
                }
            }
        }

        return [
            'id' => (int) $order->id,
            'display_id' => 'SHP-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'seller_name' => $order->seller?->business_name ?? 'ShopHop Seller',
            'status' => $order->status,
            'status_label' => $order->statusLabel(),
            'status_note' => $order->statusNote(),
            'delivery_status' => $delivery?->status,
            'tracking_code' => $delivery?->tracking_code,
            'return_status' => $order->returnCase?->status,
            'refund_status' => $order->returnCase?->refund_status,
            'refund_amount' => (float) ($order->returnCase?->refund_amount ?? 0),
            'failure_reason' => $delivery?->status === Delivery::DELIVERY_FAILED ? $delivery->failure_reason : null,
            'total_amount' => (float) $order->total_amount,
            'shipping_fee' => (float) $order->shipping_fee,
            'voucher_discount' => (float) $order->voucher_discount,
            'payment_method' => $order->payment_method,
            'delivery_address' => $order->delivery_address,
            'placed_at' => $order->created_at?->toIso8601String(),
            'can_confirm_receipt' => $order->status === Order::STATUS_TO_RECEIVE
                && $delivery?->status === Delivery::DELIVERED
                && $order->isPaymentEligible(),
            'items' => $order->items->map(fn ($item) => [
                'id' => (int) $item->id,
                'can_rate' => $reviewsAvailable && $order->status === Order::STATUS_COMPLETED && $item->product !== null && ! $reviews->has($item->product_id),
                'rating' => $reviews->get($item->product_id)?->rating,
                'product_id' => (int) $item->product_id,
                'name' => $item->product?->name ?? 'Product',
                'variant' => $item->variantLabel(),
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
                'image' => ($item->variant_image_path ?? $item->product?->image)
                    ? asset('storage/'.ltrim($item->variant_image_path ?? $item->product?->image, '/')) : null,
            ])->values(),
            'timeline' => $timeline,
        ];
    }

    private function approvedBuyer(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401, 'Unauthenticated.');
        abort_unless($user->account_type === 'buyer', 403, 'Buyer account required.');
        abort_unless($user->email_verified_at && $user->status === 'approved' && $user->buyer,
            403, 'Approved verified Buyer account required.');

        return $user->buyer;
    }
}
