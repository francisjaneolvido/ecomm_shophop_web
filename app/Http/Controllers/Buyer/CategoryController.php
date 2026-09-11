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
        $filters = [
            'shipped_from' => ['Domestic', 'Overseas', 'Metro Manila', 'North Luzon'],
            'brands' => [], // fill in once you track brand per product
            'ratings' => [4, 3, 2, 1],
            'shipping_options' => ['Free Shipping', 'Cash on Delivery'],
        ];

        $activeFilters = array_filter([
            'shipped_from' => $request->query('shipped_from'),
            'brand' => $request->query('brand'),
            'price_min' => $request->query('price_min'),
            'price_max' => $request->query('price_max'),
            'rating' => $request->query('rating'),
            'shipping_option' => $request->query('shipping_option'),
        ]);

        // ---------------------------------------------------------------
        // Product query
        // ---------------------------------------------------------------
        $query = Product::query()
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->where('category', $activeCategory['name']);

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
            'latest' => $query->latest(),
            'top_sales' => $query->orderByDesc('sold_count'), // adjust column name if different
            default => $query->latest(), // "popular" — swap for a real popularity metric later
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
                'badge' => null, // e.g. 'BUY 1 TAKE 1' — set this from a real column when you have one
                'rating' => 4.5, // placeholder until reviews exist
                'sold_count' => null, // set from a real column when you track it
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
            ],
            'currentSort' => $currentSort,
            'products' => $products,
            'currentPage' => $paginated->currentPage(),
            'lastPage' => $paginated->lastPage(),
        ]);
    }
}