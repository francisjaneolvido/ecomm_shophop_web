<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function index(): View
    {
        $products = Product::query()
            ->where('seller_id', Auth::id())
            ->latest()
            ->get();

        return view('seller.inventory', [
            'products' => $products,
            'categories' => $this->categories,
            'lowStockThreshold' => 10,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $product = null;

        if (! empty($validated['product_id'])) {
            $product = Product::where('seller_id', Auth::id())
                ->findOrFail($validated['product_id']);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');

            if ($product && $product->image) {
                Storage::disk('public')->delete($product->image);
            }
        }

        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['seller_id'] = Auth::id();

        unset($validated['product_id']);

        if ($product) {
            $product->update($validated);
            $message = 'Product updated successfully.';
        } else {
            $validated['status'] = 'active';
            Product::create($validated);
            $message = 'Product added successfully.';
        }

        return redirect()
            ->route('seller.inventory')
            ->with('status', $message);
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
}