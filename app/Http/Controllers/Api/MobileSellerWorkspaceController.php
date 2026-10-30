<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Order\Order;
use App\Models\Seller;
use App\Models\Logistics\PickupRequest;
use App\Models\Seller\Manage_inventory\Product;
use App\Support\SellerProductCategories;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Services\LogisticsRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sanctum-only, owner-scoped Seller workspace over the existing Web tables.
 * No migrations. Never exposes arbitrary seller IDs or order writes.
 */
class MobileSellerWorkspaceController extends Controller
{
    private function seller(Request $request): Seller
    {
        $user = $request->user();
        abort_unless(
            $user instanceof User && $user->account_type === 'seller'
            && $user->status === 'approved' && $user->email_verified_at,
            403, 'Approved Seller login required.'
        );
        return $user->seller ?? abort(403, 'Seller profile unavailable.');
    }

    private function ok(array $data, string $message = 'Loaded.'): JsonResponse
    {
        return response()->json(['success' => true, 'status' => 'ok', 'message' => $message, 'data' => $data]);
    }

    private function orderRow(Order $o): array
    {
        return [
            'id' => $o->id,
            'display_id' => 'SHP-'.str_pad((string) $o->id, 6, '0', STR_PAD_LEFT),
            'status' => $o->status,
            'status_label' => $o->statusLabel(),
            'status_note' => $o->statusNote(),
            'buyer' => trim(($o->buyer?->first_name ?? '').' '.($o->buyer?->last_name ?? '')) ?: 'Buyer',
            'total_amount' => (float) $o->total_amount,
            'payment_method' => $o->payment_method,
            'tracking_code' => $o->tracking_code,
            'delivery_address' => $o->delivery_address,
            'placed_at' => $o->created_at?->toIso8601String(),
            'items_count' => $o->items_count ?? $o->items?->count() ?? 0,
            'items' => $o->relationLoaded('items') ? $o->items->map(fn ($i) => [
                'name' => $i->product?->name ?? 'Product',
                'quantity' => (int) $i->quantity,
                'price' => (float) $i->price,
            ])->values()->all() : [],
            'can_accept' => $o->isPaymentEligible() && $o->status === Order::STATUS_TO_SHIP,
            'can_prepare' => $o->isPaymentEligible() && $o->status === Order::STATUS_CONFIRMED,
            'needs_pickup_booking' => $o->isPaymentEligible() && $o->status === Order::STATUS_PREPARING,
        ];
    }

    public function dashboard(Request $request): JsonResponse
    {
        $seller = $this->seller($request);
        $orders = Order::where('seller_id', $seller->id)->paymentEligible();
        $counts = (clone $orders)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $products = Product::where('seller_id', $seller->user_id);
        $recent = (clone $orders)->with('buyer')->withCount('items')->latest('id')->limit(8)->get();
        return $this->ok([
            'business_name' => $seller->business_name ?: 'Your store',
            'new_orders' => (int) ($counts[Order::STATUS_TO_SHIP] ?? 0),
            'preparing' => (int) ($counts[Order::STATUS_CONFIRMED] ?? 0) + (int) ($counts[Order::STATUS_PREPARING] ?? 0),
            'ready' => (int) ($counts[Order::STATUS_READY_FOR_PICKUP] ?? 0),
            'completed' => (int) ($counts[Order::STATUS_COMPLETED] ?? 0),
            'total_products' => (clone $products)->count(),
            'low_stock' => (clone $products)->where('status', 'active')->where('stock', '>', 0)->whereColumn('stock', '<=', 'low_stock_threshold')->count(),
            'out_of_stock' => (clone $products)->where('status', 'active')->where('stock', '<=', 0)->count(),
            'recent_orders' => $recent->map(fn (Order $o) => $this->orderRow($o))->values()->all(),
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $seller = $this->seller($request);
        $orders = Order::where('seller_id', $seller->id)
            ->with(['buyer', 'items.product', 'delivery'])
            ->latest('id')->limit(100)->get();
        return $this->ok(['orders' => $orders->map(fn (Order $o) => $this->orderRow($o))->values()->all()]);
    }

    public function inventory(Request $request): JsonResponse
    {
        $seller = $this->seller($request);
        $products = Product::where('seller_id', $seller->user_id)
            ->with(['images'])->withCount('variants')->latest('id')->limit(150)->get();
        return $this->ok(['products' => $products->map(fn (Product $p) => [
            'id' => $p->id, 'name' => $p->name, 'sku' => $p->sku,
            'category' => $p->category, 'subcategory' => $p->subcategory, 'price' => (float) $p->price,
            'stock' => (int) $p->stock, 'low_stock_threshold' => (int) ($p->low_stock_threshold ?? 10),
            'status' => $p->status, 'compliance_status' => $p->compliance_status,
            'has_variants' => (bool) $p->has_variants,
            'variants_count' => (int) $p->variants_count,
            'image_url' => $p->image ? asset('storage/' . ltrim($p->image, '/')) : null,
            'video_url' => $p->video_url,
            'media_first' => $p->is_video_first ? 'video' : 'image',
            'media' => $p->ordered_media,
        ])->values()->all()]);
    }

    public function profile(Request $request): JsonResponse
    {
        $seller = $this->seller($request);
        return $this->ok(['business_name' => $seller->business_name,
            'name' => trim($seller->first_name.' '.$seller->last_name),
            'email' => $request->user()->email,
            'contact_no' => $seller->contact_no,
            'business_category' => $seller->business_category,
            'allowed_subcategories' => SellerProductCategories::forBusiness($seller->business_category),
            'address' => implode(', ', array_filter([
                $seller->street_address, $seller->barangay_name,
                $seller->municipality_name, $seller->province_name,
            ])),
        ]);
    }

    public function reports(Request $request): JsonResponse
    {
        $seller = $this->seller($request);
        $orders = Order::where('seller_id', $seller->id)->paymentEligible();
        $completed = (clone $orders)->where('status', Order::STATUS_COMPLETED);
        return $this->ok([
            'total_orders' => (clone $orders)->count(),
            'completed_orders' => (clone $completed)->count(),
            'cancelled_orders' => (clone $orders)->where('status', Order::STATUS_CANCELLED)->count(),
            'completed_sales' => (float) (clone $completed)->sum('total_amount'),
            'this_month_sales' => (float) (clone $completed)->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount'),
            'note' => 'Order totals are gross; consult Web Reports for commission/net financial detail.',
        ]);
    }

    /** Resolve partner coverage before allowing a Seller to request pickup. */
    public function couriers(Request $request, int $order, LogisticsRoutingService $routing): JsonResponse
    {
        $seller = $this->seller($request);
        $owned = Order::where('seller_id', $seller->id)->with(['seller', 'buyer'])->findOrFail($order);
        abort_unless($owned->isPaymentEligible() && $owned->status === Order::STATUS_PREPARING, 422);
        return $this->ok([
            'selected_id' => $owned->logistics_partner_id,
            'partners' => $routing->eligiblePartners($owned)->map(fn ($partner) => [
                'id' => $partner->id,
                'name' => $partner->company_name,
            ])->values()->all(),
        ]);
    }

    /** Mirrors the Web Seller ready-for-pickup state and origin sorting-center checks. */
    public function ready(Request $request, int $order, LogisticsRoutingService $routing): JsonResponse
    {
        $seller = $this->seller($request);
        $input = $request->validate(['logistics_partner_id' => ['nullable', 'integer']]);
        return DB::transaction(function () use ($seller, $order, $routing, $input) {
            $owned = Order::where('seller_id', $seller->id)->whereKey($order)
                ->lockForUpdate()->firstOrFail();
            if (! $owned->isPaymentEligible() || $owned->status !== Order::STATUS_PREPARING) {
                throw ValidationException::withMessages(['order' => 'Order status changed. Refresh and review.']);
            }
            $owned->loadMissing(['seller', 'buyer']);
            $partnerId = (int) ($owned->logistics_partner_id ?: ($input['logistics_partner_id'] ?? 0));
            $partner = $partnerId > 0
                ? $routing->eligiblePartners($owned)->firstWhere('id', $partnerId)
                : null;
            if (! $partner) {
                throw ValidationException::withMessages(['logistics_partner_id' =>
                    'Choose an approved courier that covers pickup and delivery.']);
            }
            $originCenter = $routing->originCenter($partner, $owned->seller);
            if (! $originCenter) {
                throw ValidationException::withMessages(['logistics_partner_id' => 'Courier origin center is inactive.']);
            }
            $owned->forceFill([
                'tracking_code' => $owned->tracking_code ?: $routing->trackingCode($owned),
                'status' => Order::STATUS_READY_FOR_PICKUP,
            ])->save();
            PickupRequest::updateOrCreate(['order_id' => $owned->id], [
                'logistics_partner_id' => $partner->id,
                'origin_sorting_center_id' => $originCenter->id,
                'status' => PickupRequest::REQUESTED,
                'origin_province_code' => $owned->seller?->province_code,
                'origin_province_name' => $owned->seller?->province_name,
                'origin_municipality_code' => $owned->seller?->municipality_code,
                'origin_municipality_name' => $owned->seller?->municipality_name,
                'origin_barangay_code' => $owned->seller?->barangay_code,
                'origin_barangay_name' => $owned->seller?->barangay_name,
                'origin_street' => $owned->seller?->street_address,
                'requested_at' => now(),
                'assigned_at' => null,
                'picked_up_at' => null,
                'received_at' => null,
            ]);
            return $this->ok(['order' => $this->orderRow($owned)],
                'Pickup requested. Print and attach the shipping label from Seller Web.');
        });
    }

    /**
     * Simple product create/edit uses the same category, compliance, and inventory
     * contract as Seller Web. Variants and bulk image editing remain on Web.
     */
    public function saveProduct(Request $request, ?int $product = null): JsonResponse
    {
        $seller = $this->seller($request);
        $item = $product ? Product::where('seller_id', $seller->user_id)->findOrFail($product) : null;
        if ($item && $item->has_variants) {
            throw ValidationException::withMessages(['product' => 'Variant products must be edited in Seller Web.']);
        }
        $allowed = SellerProductCategories::forBusiness($seller->business_category);
        if ($allowed === []) {
            throw ValidationException::withMessages(['subcategory' => 'Registered shop category has no approved subcategories. Contact Admin.']);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($item?->id)],
            'subcategory' => ['required', 'string', Rule::in($allowed)],
            'price' => ['required', 'numeric', 'min:0'],
            'weight_kg' => ['required', 'numeric', 'min:0.05', 'max:500'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:10000'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4', 'max:30720'],
            'remove_video' => ['nullable', 'boolean'],
            'media_first' => ['nullable', Rule::in(['image', 'video'])],
            'media_sequence' => ['nullable', 'json', 'max:3000'],
        ]);
        if ($item && $request->hasFile('images') && $item->images()->count() + count($request->file('images')) > 8) {
            throw ValidationException::withMessages(['images' => 'A product supports up to 8 images total.']);
        }
        $payload = [
            'seller_id' => $seller->user_id,
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'category' => $seller->business_category,
            'subcategory' => $validated['subcategory'],
            'price' => $validated['price'],
            'weight_kg' => $validated['weight_kg'],
            'discount' => $validated['discount'] ?? 0,
            'stock' => $validated['stock'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 10,
            'description' => $validated['description'] ?? null,
            'has_variants' => false,
            'compliance_status' => 'pending_review',
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'rejection_reason' => null,
            'rejection_notes' => null,
        ];
        $wasNew = $item === null;
        $saved = DB::transaction(function () use ($item, $payload) {
            if ($item) {
                $item->update($payload);
                return $item;
            }
            return Product::create($payload + ['status' => 'active']);
        });
        $images = $request->file('images', []);
        $newImageIds = [];
        foreach ($images as $image) {
            $path = $image->store('products', 'public');
            $created = $saved->images()->create([
                'image_path' => $path,
                'is_primary' => ! $saved->images()->where('is_primary', true)->exists(),
            ]);
            $newImageIds[] = $created->id;
        }
        // Seller Media is owner-scoped, and every edit already returns the
        // listing to Admin compliance review before public visibility.
        $oldVideoPath = $saved->getRawOriginal('video_path');
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('product-videos', 'public');
            $saved->forceFill(['video_path' => $videoPath])->save();
            if ($oldVideoPath) Storage::disk('public')->delete($oldVideoPath);
        } elseif ($request->boolean('remove_video') && $oldVideoPath) {
            $saved->forceFill(['video_path' => null])->save();
            Storage::disk('public')->delete($oldVideoPath);
        }
        $sequence = $request->input('media_sequence');
        if (! $sequence) $sequence = json_encode(
            $request->input('media_first', 'image') === 'video' ? ['video'] : []);
        $saved->saveMediaSequence($sequence, $newImageIds);
        return $this->ok(['product_id' => $saved->id], $wasNew
            ? 'Product created and submitted for Admin review.'
            : 'Product updated and resubmitted for Admin review.');
    }

    public function toggleProduct(Request $request, int $product): JsonResponse
    {
        $seller = $this->seller($request);
        $item = Product::where('seller_id', $seller->user_id)->findOrFail($product);
        $item->update(['status' => $item->status === 'archived' ? 'active' : 'archived']);
        return $this->ok(['product_id' => $item->id, 'status' => $item->status],
            $item->status === 'archived' ? 'Product archived.' : 'Product restored.');
    }

    public function updateStock(Request $request, int $product): JsonResponse
    {
        $seller = $this->seller($request);
        $validated = $request->validate(['stock' => ['required', 'integer', 'min:0']]);
        $p = Product::where('seller_id', $seller->user_id)->findOrFail($product);
        if ($p->has_variants) {
            throw ValidationException::withMessages(['stock' => 'Variant stock is managed on Seller Web.']);
        }
        $p->update(['stock' => (int) $validated['stock']]);
        return $this->ok(['product_id' => $p->id, 'stock' => (int) $p->stock], 'Stock updated.');
    }

    public function accept(Request $request, int $order, LogisticsRoutingService $routing): JsonResponse
    {
        $seller = $this->seller($request);
        return DB::transaction(function () use ($seller, $order, $routing) {
            $owned = Order::where('seller_id', $seller->id)->whereKey($order)->lockForUpdate()->firstOrFail();
            if (! $owned->isPaymentEligible() || $owned->status !== Order::STATUS_TO_SHIP) {
                throw ValidationException::withMessages(['order' => 'Order status changed. Refresh and review.']);
            }
            $owned->forceFill([
                'status' => Order::STATUS_CONFIRMED,
                'tracking_code' => $owned->tracking_code ?: $routing->trackingCode($owned),
            ])->save();
            return $this->ok(['order' => $this->orderRow($owned)], 'Order accepted.');
        });
    }

    public function prepare(Request $request, int $order): JsonResponse
    {
        $seller = $this->seller($request);
        return DB::transaction(function () use ($seller, $order) {
            $owned = Order::where('seller_id', $seller->id)->whereKey($order)->lockForUpdate()->firstOrFail();
            if (! $owned->isPaymentEligible() || $owned->status !== Order::STATUS_CONFIRMED) {
                throw ValidationException::withMessages(['order' => 'Order status changed. Refresh and review.']);
            }
            $owned->update(['status' => Order::STATUS_PREPARING]);
            return $this->ok(['order' => $this->orderRow($owned)], 'Preparation started.');
        });
    }
}
