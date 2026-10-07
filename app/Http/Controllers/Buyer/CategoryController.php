<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * "All Categories" landing page — simple index of every main category.
     * Used by the breadcrumb's "All Categories" link.
     */
    public function index()
    {
        return view('buyer.category.index', [
            'categoryTree' => config('shophop_categories', []),
        ]);
    }

    /**
     * Category browse page — sidebar (categories + filters) + product grid.
     * Route: GET /buyer/category/{category}
     */
    public function show(Request $request, string $category)
    {
        $categoryTree = config('shophop_categories', []);

        $activeCategoryData = collect($categoryTree)
            ->first(fn ($cat) => $cat['slug'] === $category);

        abort_if(! $activeCategoryData, 404);

        $activeCategory = [
            'name' => $activeCategoryData['name'],
            'slug' => $activeCategoryData['slug'],
            'icon' => $activeCategoryData['icon'],
        ];

        // Sidebar subcategory list — highlight the one selected via ?sub=
        $activeSub = $request->query('sub');

        $subcategories = collect($activeCategoryData['subcategories'])
            ->map(fn ($subName) => [
                'name' => $subName,
                'slug' => Str::slug($subName),
                'active' => $activeSub
                    ? Str::slug($subName) === $activeSub
                    : false,
            ])
            ->values()
            ->all();

        $breadcrumbs = [
            ['label' => 'All Categories', 'url' => Route::has('buyer.category.index') ? route('buyer.category.index') : '#'],
            ['label' => $activeCategory['name'], 'url' => null],
        ];

        // ---------------------------------------------------------------
        // Filters (all optional query params)
        // ---------------------------------------------------------------
        // Only expose filters backed by Product data. Hidden/fake controls are worse UX than an honest smaller filter set.
        $filters = [
            'shipped_from' => [],
            'brands' => [],
            'ratings' => [],
            'shipping_options' => [],
        ];

        $activeFilters = array_filter([
            'price_min' => $request->query('price_min'),
            'price_max' => $request->query('price_max'),
        ]);

        // ---------------------------------------------------------------
        // Product query
        // ---------------------------------------------------------------
        $query = Product::query()
            ->publiclyDiscoverable()
            ->where('category', $activeCategory['name'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withSum('orderItems as sold_count', 'quantity');

        if ($activeSub) {
            // Only applies if you also store a subcategory column on products.
            // Safe to leave — it's a no-op if the column doesn't exist... actually
            // remove this block if you don't have a `subcategory` column yet.
            // $query->where('subcategory', $subName-you-resolved-from-slug);
        }

        if ($min = $request->query('price_min')) {
            $query->where('price', '>=', $min);
        }

        if ($max = $request->query('price_max')) {
            $query->where('price', '<=', $max);
        }

        $currentSort = $request->query('sort', 'popular');

        match ($currentSort) {
            'latest' => $query->latest('products.created_at'),
            'top_sales' => $query->orderByDesc('sold_count')->latest('products.created_at'),
            'price_asc' => $query->orderBy('price')->latest('products.created_at'),
            'price_desc' => $query->orderByDesc('price')->latest('products.created_at'),
            default => $query->orderByDesc('sold_count')->orderByDesc('reviews_avg_rating')->latest('products.created_at'),
        };

        $paginated = $query->paginate(20)->withQueryString();

        $products = $paginated->getCollection()->map(function ($product) {
            $price = (float) $product->price;
            $discount = (int) ($product->discount ?? 0);

            $finalPrice = $discount > 0
                ? round($price - ($price * $discount / 100))
                : $price;

            $imagePath = $product->image
                ? 'storage/' . ltrim($product->image, '/')
                : 'images/placeholder-product.jpg';

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $finalPrice,
                'original_price' => $discount > 0 ? $price : null,
                'discount_percent' => $discount > 0 ? $discount : null,
                'badge' => null,
                'rating' => $product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : null,
                'reviews_count' => (int) ($product->reviews_count ?? 0),
                'sold_count' => (int) ($product->sold_count ?? 0),
                'image' => asset($imagePath),
            ];
        })->all();

        return view('buyer.category.show', [
            'activeCategory' => $activeCategory,
            'breadcrumbs' => $breadcrumbs,
            'subcategories' => $subcategories,
            'subcategoriesMoreCount' => 0,
            'filters' => $filters,
            'activeFilters' => $activeFilters,
            'sortOptions' => [
                'popular' => 'Popular',
                'latest' => 'Latest',
                'top_sales' => 'Top Sales',
                'price_asc' => 'Price: Low to High',
                'price_desc' => 'Price: High to Low',
            ],
            'currentSort' => $currentSort,
            'products' => $products,
            'currentPage' => $paginated->currentPage(),
            'lastPage' => $paginated->lastPage(),
            'previousPageUrl' => $paginated->previousPageUrl(),
            'nextPageUrl' => $paginated->nextPageUrl(),
        ]);
    }
}
