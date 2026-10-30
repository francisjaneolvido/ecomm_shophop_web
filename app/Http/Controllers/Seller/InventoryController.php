<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Support\SellerProductCategories;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductImage;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    protected int $defaultLowStockThreshold = 10;

    public function index(): View
    {
        $products = Product::query()
            ->where('seller_id', Auth::id())
            ->with(['images', 'variants'])
            ->latest()
            ->get();

        $vouchers = Voucher::query()
            ->where('seller_id', Auth::id())
            ->with('products')
            ->latest()
            ->get();

        $allowed = SellerProductCategories::forBusiness(Auth::user()?->seller?->business_category);
        $filterCategories = $products->pluck('subcategory')->filter()->unique()->sort()->values()->all();

        return view('seller.inventory', [
            'products' => $products,
            'vouchers' => $vouchers,
            'categories' => $allowed,
            'filterCategories' => $filterCategories,
            'registeredCategory' => Auth::user()?->seller?->business_category,
            'defaultLowStockThreshold' => $this->defaultLowStockThreshold,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = null;

        if ($request->filled('product_id')) {
            $product = Product::where('seller_id', Auth::id())
                ->findOrFail($request->input('product_id'));
        }

        $allowed = SellerProductCategories::forBusiness(Auth::user()?->seller?->business_category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                $product
                    ? 'unique:products,sku,' . $product->id
                    : 'unique:products,sku',
            ],
            'subcategory' => ['required', 'string', 'max:255', Rule::in($allowed)],
            'price' => ['required', 'numeric', 'min:0'],
            'weight_kg' => ['required', 'numeric', 'min:0.05', 'max:500'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'has_variants' => ['nullable', 'boolean'],

            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
            'primary_image_id' => ['nullable', 'integer'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4', 'max:30720'],
            'remove_video' => ['nullable', 'boolean'],
            'media_first' => ['nullable', Rule::in(['image', 'video'])],
            'media_sequence' => ['nullable', 'json', 'max:3000'],

            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.weight_kg' => ['nullable', 'numeric', 'min:0.05', 'max:500'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.status' => ['nullable', 'string', 'in:active,archived'],
        ]);

        if ($allowed === []) {
            return back()->withErrors(['subcategory' => 'Your registered business category is not supported by the ShopHop category tree. Contact Admin before adding products.'])->withInput();
        }

        $hasVariants = $request->boolean('has_variants');
        $variantsInput = collect($request->input('variants', []));

        $stock = $hasVariants
            ? $variantsInput
                ->filter(fn ($variant) => ($variant['status'] ?? 'active') === 'active')
                ->sum(fn ($variant) => (int) ($variant['stock'] ?? 0))
            : (int) ($validated['stock'] ?? 0);

        $payload = [
            // Full content submissions invalidate old review identity and rejection history, including approved edits.
            'compliance_status' => 'pending_review',
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'rejection_reason' => null,
            'rejection_notes' => null,
            'seller_id' => Auth::id(),
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'category' => Auth::user()->seller->business_category,
            'subcategory' => $validated['subcategory'],
            'price' => $validated['price'],
            'weight_kg' => $validated['weight_kg'],
            'discount' => $validated['discount'] ?? 0,
            'stock' => $stock,
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? null,
            'description' => $validated['description'] ?? null,
            'has_variants' => $hasVariants,
        ];

        DB::transaction(function () use (
            &$product,
            $payload,
            $request,
            $variantsInput,
            $hasVariants
        ) {
            if ($product) {
                $product->update($payload);
            } else {
                $payload['status'] = 'active';
                $product = Product::create($payload);
            }

            $newImageIds = $this->syncImages($product, $request);
            $this->syncVideo($product, $request);
            $sequence = $request->input('media_sequence');
            if (! $sequence) {
                $sequence = json_encode($request->input('media_first', 'image') === 'video'
                    ? ['video'] : []);
            }
            $product->saveMediaSequence($sequence, $newImageIds);
            $this->syncVariants($product, $hasVariants ? $variantsInput : collect());
        });

        return redirect()
            ->route('seller.inventory')
            // Persistence succeeds before review; neither creation nor full edit promises Buyer visibility.
            ->with('status', $product->wasRecentlyCreated
                ? 'Product added. Awaiting compliance review before Buyer visibility.'
                : 'Product updated and resubmitted for compliance review.');
    }

    /**
     * Handles the quick +/- stock editor on the inventory table row.
     * Only allowed for simple products (no variants) — variant stock
     * is derived automatically from the variant rows instead.
     */
    public function updateStock(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->seller_id !== Auth::id(), 403);

        if ($product->has_variants) {
            return redirect()
                ->route('seller.inventory')
                ->with('error', 'Stock for this product is managed through its variants.');
        }

        $validated = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product->update([
            'stock' => $validated['stock'],
        ]);

        return redirect()
            ->route('seller.inventory')
            ->with('status', 'Stock updated.');
    }

    protected function syncImages(Product $product, Request $request): array
    {
        $createdIds = [];
        $removeIds = collect($request->input('remove_image_ids', []))->map(fn ($id) => (int) $id);

        if ($removeIds->isNotEmpty()) {
            $product->images()
                ->whereIn('id', $removeIds)
                ->get()
                ->each(function (ProductImage $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                });
        }

        if ($request->hasFile('images')) {
            $existingCount = $product->images()->count();

            foreach ($request->file('images') as $file) {
                if ($existingCount >= 8) {
                    break;
                }

                $path = $file->store('products', 'public');

                $created = $product->images()->create([
                    'image_path' => $path,
                    'is_primary' => false,
                ]);
                $createdIds[] = $created->id;

                $existingCount++;
            }
        }

        $primaryId = $request->input('primary_image_id');

        $product->images()->update(['is_primary' => false]);

        if ($primaryId) {
            $product->images()->where('id', $primaryId)->update(['is_primary' => true]);
        } elseif ($first = $product->images()->first()) {
            $first->update(['is_primary' => true]);
        }

        return $createdIds;
    }

    /** Replace a single optional product video. A media change triggers the
     * same compliance resubmission as a full product edit.
     */
    protected function syncVideo(Product $product, Request $request): void
    {
        $oldPath = $product->getRawOriginal('video_path');
        if ($request->hasFile('video')) {
            $newPath = $request->file('video')->store('product-videos', 'public');
            $product->forceFill(['video_path' => $newPath])->save();
            if ($oldPath) Storage::disk('public')->delete($oldPath);
        } elseif ($request->boolean('remove_video') && $oldPath) {
            $product->forceFill(['video_path' => null])->save();
            Storage::disk('public')->delete($oldPath);
        }
    }

    protected function syncVariants(Product $product, $variantsInput): void
    {
        $submittedIds = $variantsInput
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        $product->variants()
            ->whereNotIn('id', $submittedIds->isEmpty() ? [0] : $submittedIds)
            ->delete();

        foreach ($variantsInput as $variant) {
            $data = [
                'name' => $variant['name'],
                'sku' => $variant['sku'] ?? null,
                'price' => $variant['price'] ?? null,
                'weight_kg' => $variant['weight_kg'] ?? null,
                'stock' => (int) ($variant['stock'] ?? 0),
                'status' => $variant['status'] ?? 'active',
            ];

            if (! empty($variant['id'])) {
                $product->variants()
                    ->where('id', $variant['id'])
                    ->update($data);
            } else {
                $product->variants()->create($data);
            }
        }
    }

    public function archive(Product $product): RedirectResponse
    {
        abort_if($product->seller_id !== Auth::id(), 403);

        $product->update([
            'status' => $product->status === 'archived' ? 'active' : 'archived',
        ]);

        $message = $product->status === 'archived'
            ? 'Product archived.'
            : 'Product restored.';

        return redirect()
            ->route('seller.inventory')
            ->with('status', $message);
    }

    public function storeVoucher(Request $request): RedirectResponse
    {
        $voucher = null;

        if ($request->filled('voucher_id')) {
            $voucher = Voucher::where('seller_id', Auth::id())
                ->findOrFail($request->input('voucher_id'));
        }

        // A foreign Product id is an authorization failure, even if other fields are invalid.
        $submittedProductIds = $request->input('product_ids', []);
        if (is_array($submittedProductIds)) {
            abort_if(
                Product::query()
                    ->whereIn('id', array_filter($submittedProductIds, static fn ($id) => is_int($id) || is_string($id)))
                    ->where('seller_id', '!=', Auth::id())
                    ->exists(),
                403
            );
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                $voucher
                    ? 'unique:vouchers,code,' . $voucher->id
                    : 'unique:vouchers,code',
            ],
            'kind' => ['required', 'in:merchandise,shipping'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'product_ids' => ['nullable', 'array'],
            // The checkbox list is seller scoped, but submitted IDs need the same guard.
            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')
                    ->where('seller_id', Auth::id())
                    ->where('status', 'active'),
            ],
        ]);

        $payload = [
            'seller_id' => Auth::id(),
            'name' => $validated['name'],
            'code' => Str::upper($validated['code']),
            'kind' => $validated['kind'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'min_order_amount' => $validated['min_order_amount'] ?? 0,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ];

        if ($voucher) {
            $voucher->update($payload);
        } else {
            $payload['status'] = 'active';
            $payload['used_count'] = 0;
            $voucher = Voucher::create($payload);
        }

        $voucher->products()->sync($validated['product_ids'] ?? []);

        return redirect()
            ->route('seller.inventory')
            ->with('status', $voucher->wasRecentlyCreated
                ? 'Voucher created successfully.'
                : 'Voucher updated successfully.');
    }

    public function toggleVoucher(Voucher $voucher): RedirectResponse
    {
        abort_if($voucher->seller_id !== Auth::id(), 403);

        $voucher->update([
            'status' => $voucher->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()
            ->route('seller.inventory')
            ->with('status', 'Voucher ' . ($voucher->status === 'active' ? 'enabled.' : 'disabled.'));
    }

    public function destroyVoucher(Voucher $voucher): RedirectResponse
    {
        abort_if($voucher->seller_id !== Auth::id(), 403);

        $voucher->delete();

        return redirect()
            ->route('seller.inventory')
            ->with('status', 'Voucher deleted.');
    }
}
