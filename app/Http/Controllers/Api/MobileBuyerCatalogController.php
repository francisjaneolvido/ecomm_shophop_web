<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer\Cart\CartItem;
use App\Models\Buyer\Favorite\Favorite;
use App\Models\Buyer\Review\Review;
use App\Models\Seller;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MobileBuyerCatalogController extends Controller
{
    private array $likedProductIds = [];

    public function shop(Request $request, Seller $seller): JsonResponse
    {
        $this->approvedBuyer($request);
        abort_unless($seller->user()->where('account_type', 'seller')->where('status', 'approved')->exists(), 404);

        $catalog = Product::query()->publiclyDiscoverable()->where('seller_id', $seller->user_id);
        $categories = (clone $catalog)->distinct()->orderBy('category')->pluck('category');
        $category = trim((string) $request->query('category', ''));
        if ($category !== '') {
            abort_unless($categories->contains($category), 404);
            $catalog->where('category', $category);
        }
        $page = $catalog->with(['seller', 'images'])->latest('created_at')->paginate(24);
        $coverProduct = $seller->products()->publiclyDiscoverable()->whereHas('images')
            ->with('images')->latest('created_at')->first()
            ?? $seller->products()->publiclyDiscoverable()->whereNotNull('image')->latest('created_at')->first();
        $coverPath = $coverProduct?->image;
        $ratings = Review::query()->whereHas('product', fn ($q) => $q->where('seller_id', $seller->user_id));

        return response()->json(['success' => true, 'data' => [
            'id' => $seller->id,
            'user_id' => $seller->user_id,
            'business_name' => $seller->business_name,
            'business_category' => $seller->business_category,
            'municipality' => $seller->municipality_name,
            'province' => $seller->province_name,
            'joined' => $seller->created_at?->format('M Y'),
            'logo_url' => $seller->logo_url,
            'cover_url' => $coverPath ? asset('storage/'.ltrim($coverPath, '/')) : $seller->logo_url,
            'products_count' => $seller->products()->publiclyDiscoverable()->count(),
            'rating' => round((float) (clone $ratings)->avg('rating'), 1),
            'reviews_count' => (clone $ratings)->count(),
            'categories' => $categories,
            'products' => $page->getCollection()->map(fn (Product $product) => $this->productCard($product))->values(),
            'pagination' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
                'has_more' => $page->hasMorePages()],
        ]]);
    }

    /**
     * Buyer Home data used by the Flutter app.
     *
     * Important: this endpoint intentionally returns only real database/config
     * data. It does not return the Blade dashboard's sample/fallback products.
     */
    public function home(Request $request): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);

        $marketplace = app(\App\Services\MarketplaceCatalogService::class);
        $sections = $marketplace->sections();
        $cards = fn ($products) => $products->map(fn (Product $product) => $this->productCard($product))->values();
        $data = [
            'categories' => $this->categoryPayload(),
            'banners' => $marketplace->banners(),
            'top_products' => $cards($sections['top']),
            'discover_products' => $cards($sections['discover']),
            'has_completed_sales' => $sections['has_completed_sales'],
            'deal_products' => $cards($sections['deals']),
            // Retain the previous keys for installed mobile versions.
            'trending_products' => $cards($sections['top']),
            'recommended_products' => $cards($sections['discover']),
            'new_arrivals' => $cards($sections['discover']->take(6)),
            'cart_count' => Schema::hasTable('cart_items')
                ? CartItem::where('buyer_id', $buyer->id)->count() : 0,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Buyer home loaded.',
            'data' => $data,
        ]);
    }

    /**
     * Master category tree from config/shophop_categories.php.
     * This keeps Flutter synchronized with the web category source of truth.
     */
    public function categories(Request $request): JsonResponse
    {
        $this->approvedBuyer($request);

        return response()->json([
            'success' => true,
            'message' => 'Categories loaded.',
            'data' => $this->categoryPayload(),
        ]);
    }

    /**
     * Search/browse the public buyer catalogue.
     *
     * Supported query params:
     * q, category (slug or exact name), sort, page, per_page
     */
    public function products(Request $request): JsonResponse
    {
        $this->approvedBuyer($request);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:160'],
            'subcategory' => ['nullable', 'string', 'max:160'],
            'collection' => ['nullable', Rule::in(['deals', 'top', 'discover'])],
            'sort' => [
                'nullable',
                Rule::in(['popular', 'latest', 'top_sales', 'price_low', 'price_high']),
            ],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);

        if (! Schema::hasTable((new Product())->getTable())) {
            return response()->json([
                'success' => true,
                'message' => 'Products loaded.',
                'data' => [
                    'items' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => (int) ($validated['per_page'] ?? 20),
                        'total' => 0,
                        'has_more' => false,
                    ],
                ],
            ]);
        }

        $query = $this->catalogQuery();

        if ($search = trim((string) ($validated['q'] ?? ''))) {
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('subcategory', 'like', '%' . $search . '%');
            });
        }

        if ($categoryInput = trim((string) ($validated['category'] ?? ''))) {
            $category = $this->resolveCategory($categoryInput);

            if (! $category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown ShopHop category.',
                    'errors' => [
                        'category' => ['The selected category does not exist.'],
                    ],
                ], 422);
            }

            $query->where('category', $category['name']);
        }

        if ($subcategory = trim((string) ($validated['subcategory'] ?? ''))) {
            $category = $this->resolveCategory((string) ($validated['category'] ?? ''));
            abort_unless($category && in_array($subcategory, $category['subcategories'] ?? [], true), 422, 'Unknown subcategory.');
            $query->where('subcategory', $subcategory);
        }
        if (($validated['collection'] ?? '') === 'deals') {
            $query->withActiveDiscount();
        }
        $sort = $validated['sort'] ?? 'popular';

        match ($sort) {
            'latest' => $query->latest(),
            'top_sales' => $this->supportsSoldCount()
                ? $query->orderByDesc('completed_units_sold')->latest('products.created_at')->orderByDesc('products.id')
                : $query->latest(),
            'price_low' => $query->orderByRaw('ROUND(price * (1 - CASE WHEN deal_ends_at IS NULL OR deal_ends_at > ? THEN COALESCE(discount, 0) ELSE 0 END / 100.0), 2) ASC', [now()])->latest('products.created_at'),
            'price_high' => $query->orderByRaw('ROUND(price * (1 - CASE WHEN deal_ends_at IS NULL OR deal_ends_at > ? THEN COALESCE(discount, 0) ELSE 0 END / 100.0), 2) DESC', [now()])->latest('products.created_at'),
            default => $this->supportsSoldCount()
                ? $query->orderByDesc('completed_units_sold')->latest('products.created_at')->orderByDesc('products.id')
                : $query->latest(),
        };

        $perPage = (int) ($validated['per_page'] ?? 20);
        $paginated = $query->paginate($perPage)->withQueryString();
        $shopMatches = empty($validated['q']) ? collect() : Seller::query()
            ->whereRaw('LOWER(business_name) LIKE ?', ['%'.mb_strtolower(trim($validated['q'])).'%'])
            ->whereHas('user', fn ($builder) => $builder->where('account_type', 'seller')->where('status', 'approved'))
            ->whereHas('products', fn ($builder) => $builder->publiclyDiscoverable())
            ->withCount(['products as available_products_count' => fn ($builder) => $builder->publiclyDiscoverable()])
            ->orderBy('business_name')->limit(6)->get()->map(fn (Seller $shop) => [
                'id' => $shop->id,
                'business_name' => $shop->business_name,
                'business_category' => $shop->business_category,
                'logo_url' => $shop->logo_url,
                'products_count' => $shop->available_products_count,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Products loaded.',
            'data' => [
                'shops' => $shopMatches,
                'items' => $paginated->getCollection()
                    ->map(fn (Product $product) => $this->productCard($product))
                    ->values(),
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'has_more' => $paginated->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Full product details for Flutter's product page.
     */
    public function show(Request $request, int $product): JsonResponse
    {
        $this->approvedBuyer($request);

        if (! Schema::hasTable((new Product())->getTable())) {
            abort(404);
        }

        $item = $this->catalogQuery()
            ->with(['seller', 'images', 'variants.image', 'vouchers'])
            ->findOrFail($product);

        $galleryPaths = $item->images
            ->sortByDesc('is_primary')
            ->pluck('image_path')
            ->filter()
            ->values();

        if ($galleryPaths->isEmpty() && $item->image) {
            $galleryPaths = collect([$item->image]);
        }

        $gallery = $galleryPaths
            ->map(fn ($path) => $this->productImageUrl($path))
            ->values();

        if ($gallery->isEmpty()) {
            $gallery = collect([$this->productImageUrl(null)]);
        }

        $variants = $item->variants
            ->map(function ($variant) use ($item) {
                $attributes = $variant->attributes ?? [];
                $label = ! empty($attributes)
                    ? $variant->displayLabel()
                    : $variant->name;

                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'label' => $label,
                    'attributes' => $attributes,
                    'image' => $variant->image && (int) $variant->image->product_id === (int) $item->id
                        ? $this->productImageUrl($variant->image->image_path) : null,
                    'price' => $variant->price === null ? null : (float) $variant->price,
                    'stock' => (int) $variant->stock,
                    'status' => $variant->status,
                    'in_stock' => $variant->status === 'active' && (int) $variant->stock > 0 && $variant->price !== null,
                ];
            })
            ->values();

        $shopVouchers = $item->vouchers
            ->where('status', 'active')
            ->map(function ($voucher) {
                $label = $voucher->type === 'percent'
                    ? $voucher->value . '% off'
                    : '₱' . number_format($voucher->value) . ' off';

                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'label' => $label,
                    'min_order_amount' => (float) $voucher->min_order_amount,
                ];
            })
            ->values();

        $reviews = collect();
        $ratingBreakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        if (Schema::hasTable('reviews')) {
            $reviewRows = $item->reviews()
                ->with('buyer')
                ->latest()
                ->take(20)
                ->get();

            $reviews = $reviewRows->map(function ($review) {
                $buyerName = $review->buyer?->first_name
                    ? $review->buyer->first_name . ' ' . mb_substr($review->buyer->last_name ?? '', 0, 1) . '.'
                    : 'ShopHop Buyer';

                return [
                    'id' => $review->id,
                    'buyer_name' => $buyerName,
                    'rating' => (int) $review->rating,
                    'variant' => $review->variant_label ?? '—',
                    'date' => optional($review->created_at)->toDateString(),
                    'comment' => $review->comment,
                    'seller_reply' => $review->seller_reply,
                    'media' => $review->publicMedia(),
                    'image' => $review->image_path
                        ? $this->productImageUrl($review->image_path)
                        : null,
                    'helpful' => (int) $review->helpful_count,
                ];
            })->values();

            for ($stars = 5; $stars >= 1; $stars--) {
                $ratingBreakdown[$stars] = $item->reviews()
                    ->where('rating', $stars)
                    ->count();
            }
        }

        $seller = $item->seller;
        $sellerProductsCount = $seller
            ? Product::query()
                ->publiclyDiscoverable()
                ->where('seller_id', $seller->user_id)
                ->count()
            : 0;

        $related = $this->catalogQuery()
            ->where('category', $item->category)
            ->whereKeyNot($item->id)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Product $productModel) => $this->productCard($productModel))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Product loaded.',
            'data' => [
                ...$this->productCard($item),
                'description' => $item->description,
                'gallery' => $gallery,
                'video_url' => $item->video_url,
                'media_first' => $item->is_video_first ? 'video' : 'image',
                'media' => $item->ordered_media,
                'has_variants' => (bool) $item->has_variants,
                'variants' => $variants,
                'vouchers' => $shopVouchers,
                'reviews_data' => $reviews,
                'rating_breakdown' => $ratingBreakdown,
                'seller' => [
                    'id' => $seller?->id,
                    'user_id' => $seller?->user_id,
                    'business_name' => $seller?->business_name ?? 'ShopHop Seller',
                    'logo_url' => $seller?->logo_url,
                    'municipality' => $seller?->municipality_name,
                    'joined' => $seller?->created_at?->diffForHumans(),
                    'products_count' => $sellerProductsCount,
                ],
                'related_products' => $related,
            ],
        ]);
    }

    /**
     * Same catalogue authority used by the web Product model.
     */
    private function catalogQuery(): Builder
    {
        return app(\App\Services\MarketplaceCatalogService::class)->query();
    }

    private function productCard(Product $product): array
    {
        $price = (float) $product->price;
        $discount = (int) ($product->discount ?? 0);
        $finalPrice = $discount > 0
            ? round($price - ($price * $discount / 100), 2)
            : $price;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'subcategory' => $product->subcategory,
            'price' => $finalPrice,
            'original_price' => $discount > 0 ? $price : null,
            'discount_percent' => $discount,
            'stock' => (int) $product->stock,
            'image' => $this->productImageUrl($product->image),
            'rating' => isset($product->avg_rating)
                ? round((float) $product->avg_rating, 1)
                : 0,
            'reviews' => isset($product->reviews_count)
                ? (int) $product->reviews_count
                : 0,
            'sold' => isset($product->completed_units_sold)
                ? (int) $product->completed_units_sold
                : 0,
            'has_variants' => (bool) $product->has_variants,
            'is_liked' => isset($this->likedProductIds[(int) $product->id]),
            'seller' => [
                'id' => $product->seller?->id,
                'business_name' => $product->seller?->business_name ?? 'ShopHop Seller',
                'logo_url' => $product->seller?->logo_url,
                'municipality' => $product->seller?->municipality_name,
            ],
        ];
    }

    private function categoryPayload(): array
    {
        $counts = Schema::hasTable('products')
            ? Product::query()->publiclyDiscoverable()->selectRaw('category, COUNT(*) AS available_count')->groupBy('category')->pluck('available_count', 'category')
            : collect();
        return collect(config('shophop_categories', []))
            ->map(function (array $category) use ($counts) {
                $folder = $category['folder'] ?? null;
                $images = [];

                if ($folder) {
                    $directory = public_path('images/category_icons_bg/' . $folder);

                    if (is_dir($directory)) {
                        $files = glob($directory . '/*.{jpg,jpeg,png,webp,avif,gif}', GLOB_BRACE) ?: [];

                        $images = collect(array_slice($files, 0, 6))
                            ->map(fn ($file) => asset(
                                'images/category_icons_bg/' . $folder . '/' . basename($file)
                            ))
                            ->values()
                            ->all();
                    }
                }

                return [
                    'name' => $category['name'],
                    'product_count' => (int) ($counts[$category['name']] ?? 0),
                    'slug' => $category['slug'],
                    'folder' => $folder,
                    'icon' => $category['icon'] ?? null,
                    'subcategories' => array_values($category['subcategories'] ?? []),
                    'images' => $images,
                    'cover_image' => $images[0] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    private function resolveCategory(string $value): ?array
    {
        $needle = Str::lower(trim($value));

        return collect(config('shophop_categories', []))
            ->first(function (array $category) use ($needle) {
                return Str::lower($category['slug']) === $needle
                    || Str::lower($category['name']) === $needle;
            });
    }

    private function productImageUrl(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return asset('images/placeholder-product.jpg');
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['storage/', 'images/'])) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private function supportsSoldCount(): bool
    {
        return Schema::hasTable('order_items') && Schema::hasTable('orders');
    }

    private function approvedBuyer(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 401, 'Unauthenticated.');
        abort_unless($user->account_type === 'buyer', 403, 'Buyer account required.');
        abort_unless($user->email_verified_at, 403, 'Email verification required.');
        abort_unless($user->status === 'approved', 403, 'Approved buyer account required.');
        abort_unless($user->buyer, 403, 'Buyer profile not found.');

        if (Schema::hasTable('favorites')) {
            $this->likedProductIds = Favorite::query()
                ->where('buyer_id', $user->buyer->id)
                ->pluck('product_id')
                ->mapWithKeys(fn ($id) => [(int) $id => true])
                ->all();
        } else {
            $this->likedProductIds = [];
        }

        return $user->buyer;
    }
}
