<?php

namespace App\Http\Controllers;

use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicSearchController extends Controller
{
    private function matching(Builder $query, string $search): Builder
    {
        $needle = '%'.mb_strtolower($search).'%';
        return $query->where(function (Builder $q) use ($needle) {
            $q->whereRaw('LOWER(name) LIKE ?', [$needle])
                ->orWhereRaw('LOWER(category) LIKE ?', [$needle])
                ->orWhereRaw('LOWER(description) LIKE ?', [$needle]);
            if (Schema::hasColumn('products', 'subcategory')) {
                $q->orWhereRaw('LOWER(subcategory) LIKE ?', [$needle]);
            }
        });
    }

    public function index(Request $request)
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = trim($data['search'] ?? '');
        $products = null;
        if ($search !== '') {
            $products = Schema::hasTable('products')
                ? $this->matching(Product::query()->publiclyDiscoverable()->with('images'), $search)
                    ->orderBy('name')->orderBy('id')->paginate(24)->withQueryString()
                : new LengthAwarePaginator([], 0, 24);
        }
        return view('search.index', compact('search', 'products'));
    }

    public function suggestions(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim($data['q'] ?? '');
        $needle = mb_strtolower($search);
        $categories = collect(config('shophop_categories', []))
            ->filter(fn ($category) => $search === '' || Str::contains(
                mb_strtolower($category['name'].' '.implode(' ', $category['subcategories'] ?? [])), $needle))
            ->take($search === '' ? 4 : 3)->map(fn ($category) => [
                'name' => $category['name'],
                'url' => route('buyer.category.show', $category['slug']),
            ])->values();
        $products = collect();
        if (Schema::hasTable('products') && ($search === '' || mb_strlen($search) >= 2)) {
            $query = Product::query()->publiclyDiscoverable()->with('images');
            if ($search !== '') $this->matching($query, $search);
            $products = $query->latest('products.created_at')->orderByDesc('products.id')->limit(6)->get()
                ->map(fn ($product) => [
                    'id' => $product->id, 'name' => $product->name, 'category' => $product->category,
                    'price' => round((float) $product->price * (1 - ((int) $product->discount / 100)), 2),
                    'image' => $product->image ? asset('storage/'.ltrim($product->image, '/')) : asset('images/placeholder-product.jpg'),
                    'url' => route('buyer.product.show', $product->id),
                ]);
        }
        return response()->json(['query' => $search, 'products' => $products, 'categories' => $categories])
            ->header('Cache-Control', 'no-store, private');
    }
}
