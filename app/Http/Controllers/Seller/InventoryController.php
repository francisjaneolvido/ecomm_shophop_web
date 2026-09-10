<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller\Manage_inventory\Product;
use App\Models\Seller\Manage_inventory\ProductImage;
use App\Models\Seller\Manage_inventory\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Categories are hardcoded for now since there's no categories
     * table yet — same placeholder list used in the seller
     * registration modal. Swap this for a real query once a
     * categories table exists.
     */
    protected array $categories = [
        'Fashion & Apparel',
        'Electronics & Gadgets',
        'Health & Beauty',
        'Home & Living',
        'Groceries & Food',
        'Toys, Kids & Baby',
        'Sports & Outdoors',
        'Automotive',
        'Books, Hobbies & Stationery',
        'Pet Supplies',
        'Other',
    ];

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

        return view('seller.inventory', [
            'products' => $products,
            'vouchers' => $vouchers,
            'categories' => $this->categories,
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
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
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

            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.status' => ['nullable', 'string', 'in:active,archived'],
        ]);

        $hasVariants = $request->boolean('has_variants');
        $variantsInput = collect($request->input('variants', []));

        $stock = $hasVariants
            ? $variantsInput
                ->filter(fn ($variant) => ($variant['status'] ?? 'active') === 'active')
                ->sum(fn ($variant) => (int) ($variant['stock'] ?? 0))
            : (int) ($validated['stock'] ?? 0);

        $payload = [
            'seller_id' => Auth::id(),
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'category' => $validated['category'],
            'price' => $validated['price'],
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

            $this->syncImages($product, $request);
            $this->syncVariants($product, $hasVariants ? $variantsInput : collect());
        });

        return redirect()
            ->route('seller.inventory')
            ->with('status', $product->wasRecentlyCreated
                ? 'Product added successfully.'
                : 'Product updated successfully.');
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

    protected function syncImages(Product $product, Request $request): void
    {
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

                $product->images()->create([
                    'image_path' => $path,
                    'is_primary' => false,
                ]);

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
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $payload = [
            'seller_id' => Auth::id(),
            'name' => $validated['name'],
            'code' => Str::upper($validated['code']),
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