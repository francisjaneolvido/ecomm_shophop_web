@extends('seller.partials.layout')

@section('title', 'Manage Inventory')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FRONTEND-ONLY DEMO DATA
    |--------------------------------------------------------------------------
    | Temporary hardcoded inventory data. No Product/Voucher model or
    | InventoryController query is required for this Blade to render.
    | Replace this block with controller-provided data when the backend is ready.
    */

    $defaultLowStockThreshold = 10;

    $categories = collect([
        'Electronics',
        'Home & Living',
        'Fashion',
        'Beauty & Personal Care',
        'Food & Beverages',
    ]);

    $products = collect([
        (object) [
            'id' => 1,
            'name' => 'Wireless Bluetooth Mouse',
            'sku' => 'MOUSE-BT-001',
            'category' => 'Electronics',
            'price' => 799.00,
            'discount' => 10,
            'stock' => 42,
            'low_stock_threshold' => 10,
            'has_variants' => false,
            'description' => 'Compact wireless mouse with silent clicks and adjustable DPI.',
            'image' => null,
            'status' => 'active',
            'created_at' => now()->subHours(3),
            'images' => collect([]),
            'variants' => collect([]),
        ],
        (object) [
            'id' => 2,
            'name' => 'Minimalist Canvas Tote Bag',
            'sku' => 'TOTE-CNV-002',
            'category' => 'Fashion',
            'price' => 449.00,
            'discount' => 0,
            'stock' => 7,
            'low_stock_threshold' => 10,
            'has_variants' => false,
            'description' => 'Everyday canvas tote with reinforced handles and inner pocket.',
            'image' => null,
            'status' => 'active',
            'created_at' => now()->subDay(),
            'images' => collect([]),
            'variants' => collect([]),
        ],
        (object) [
            'id' => 3,
            'name' => 'Insulated Travel Tumbler',
            'sku' => 'TUMBLER-003',
            'category' => 'Home & Living',
            'price' => 599.00,
            'discount' => 15,
            'stock' => 0,
            'low_stock_threshold' => 8,
            'has_variants' => false,
            'description' => 'Double-wall insulated tumbler for hot and cold drinks.',
            'image' => null,
            'status' => 'active',
            'created_at' => now()->subDays(2),
            'images' => collect([]),
            'variants' => collect([]),
        ],
        (object) [
            'id' => 4,
            'name' => 'Classic Oversized Shirt',
            'sku' => 'SHIRT-OVR-004',
            'category' => 'Fashion',
            'price' => 699.00,
            'discount' => 5,
            'stock' => 31,
            'low_stock_threshold' => 10,
            'has_variants' => true,
            'description' => 'Relaxed oversized shirt available in multiple sizes.',
            'image' => null,
            'status' => 'active',
            'created_at' => now()->subDays(4),
            'images' => collect([]),
            'variants' => collect([
                (object) ['id' => 41, 'name' => 'Black / Small', 'sku' => 'SHIRT-BLK-S', 'price' => 699.00, 'stock' => 8, 'status' => 'active'],
                (object) ['id' => 42, 'name' => 'Black / Medium', 'sku' => 'SHIRT-BLK-M', 'price' => 699.00, 'stock' => 13, 'status' => 'active'],
                (object) ['id' => 43, 'name' => 'Black / Large', 'sku' => 'SHIRT-BLK-L', 'price' => 699.00, 'stock' => 10, 'status' => 'active'],
            ]),
        ],
        (object) [
            'id' => 5,
            'name' => 'Daily Glow Face Serum',
            'sku' => 'SERUM-005',
            'category' => 'Beauty & Personal Care',
            'price' => 549.00,
            'discount' => 0,
            'stock' => 18,
            'low_stock_threshold' => 5,
            'has_variants' => false,
            'description' => 'Lightweight daily facial serum for a fresh, hydrated look.',
            'image' => null,
            'status' => 'active',
            'created_at' => now()->subDays(6),
            'images' => collect([]),
            'variants' => collect([]),
        ],
        (object) [
            'id' => 6,
            'name' => 'Portable Mini Fan',
            'sku' => 'FAN-MINI-006',
            'category' => 'Electronics',
            'price' => 399.00,
            'discount' => 0,
            'stock' => 14,
            'low_stock_threshold' => 5,
            'has_variants' => false,
            'description' => 'Archived sample product for the inventory demo.',
            'image' => null,
            'status' => 'archived',
            'created_at' => now()->subDays(12),
            'images' => collect([]),
            'variants' => collect([]),
        ],
    ]);

    $vouchers = collect([
        (object) [
            'id' => 1,
            'name' => 'Welcome Discount',
            'code' => 'WELCOME10',
            'type' => 'percent',
            'value' => 10,
            'min_order_amount' => 500,
            'usage_limit' => 100,
            'used_count' => 28,
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addDays(30),
            'status' => 'active',
            'products' => collect([]),
        ],
        (object) [
            'id' => 2,
            'name' => 'Fashion Payday Sale',
            'code' => 'PAYDAY150',
            'type' => 'fixed',
            'value' => 150,
            'min_order_amount' => 1200,
            'usage_limit' => 50,
            'used_count' => 11,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(5),
            'status' => 'active',
            'products' => $products->whereIn('id', [2, 4])->values(),
        ],
        (object) [
            'id' => 3,
            'name' => 'Old Store Promo',
            'code' => 'SAVE50',
            'type' => 'fixed',
            'value' => 50,
            'min_order_amount' => 300,
            'usage_limit' => null,
            'used_count' => 64,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
            'status' => 'inactive',
            'products' => collect([]),
        ],
    ]);

    $activeProducts = $products->where('status', 'active');
    $archivedProducts = $products->where('status', 'archived');

    $activeCount = $activeProducts->count();

    $lowStockCount = $activeProducts
        ->filter(fn ($product) =>
            (int) $product->stock > 0 &&
            (int) $product->stock <=
                (int) ($product->low_stock_threshold ?? $defaultLowStockThreshold)
        )
        ->count();

    $outOfStockCount = $activeProducts
        ->filter(fn ($product) => (int) $product->stock === 0)
        ->count();

    $archivedCount = $archivedProducts->count();
    $totalUnits = $activeProducts->sum(fn ($product) => (int) $product->stock);

    $stockMeta = function ($product) use ($defaultLowStockThreshold) {
        if ($product->status === 'archived') {
            return [
                'label' => 'Archived',
                'class' => 'bg-navy/10 text-navy/55',
                'state' => 'archived',
            ];
        }

        $stock = (int) $product->stock;
        $threshold =
            (int) ($product->low_stock_threshold ?? $defaultLowStockThreshold);

        if ($stock === 0) {
            return [
                'label' => 'Out of Stock',
                'class' => 'bg-red-50 text-red-600',
                'state' => 'out',
            ];
        }

        if ($stock <= $threshold) {
            return [
                'label' => 'Low Stock',
                'class' => 'bg-yellow/20 text-amber-700',
                'state' => 'low',
            ];
        }

        return [
            'label' => 'In Stock',
            'class' => 'bg-teal/10 text-teal-dark',
            'state' => 'in',
        ];
    };

    $sellingPrice = function ($product) {
        $price = (float) $product->price;
        $discount = (int) $product->discount;

        return $discount > 0
            ? round($price - ($price * ($discount / 100)), 2)
            : $price;
    };
@endphp


<style>
    #sellerInventory .inventory-card {
        transition:
            transform .16s ease,
            box-shadow .16s ease,
            border-color .16s ease;
    }

    #sellerInventory .inventory-card:hover {
        transform: translateY(-1px);
    }

    #productModal[hidden],
    #archiveModal[hidden],
    #voucherModal[hidden] {
        display: none !important;
    }

    .inventory-tab-active {
        background: #0F2C3F;
        color: white;
        border-color: #0F2C3F;
    }

    .inventory-section-tab-active {
        color: #0F2C3F;
        border-color: #2ECFA6;
        background: #E9F8F4;
    }

    .inventory-row[hidden] {
        display: none !important;
    }
</style>


@if (session('status'))
    <div class="mb-4 rounded-xl border border-teal/25 bg-teal-light px-4 py-3 flex items-start gap-3">
        <x-lucide-circle-check class="w-4 h-4 text-teal-dark mt-0.5 shrink-0" />

        <p class="text-xs font-medium text-teal-dark">
            {{ session('status') }}
        </p>
    </div>
@endif


@if (session('error'))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 flex items-start gap-3">
        <x-lucide-circle-alert class="w-4 h-4 text-red-500 mt-0.5 shrink-0" />

        <p class="text-xs font-medium text-red-600">
            {{ session('error') }}
        </p>
    </div>
@endif


@if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
        <div class="flex items-start gap-3">
            <x-lucide-circle-alert class="w-4 h-4 text-red-500 mt-0.5 shrink-0" />

            <div>
                <p class="text-xs font-semibold text-red-600">
                    Please review the following:
                </p>

                <ul class="mt-1 space-y-0.5 text-xs text-red-500">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif


<div id="sellerInventory" class="space-y-5">

    {{-- =========================================================
        HEADER
    ========================================================= --}}
    <section>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

            <div class="min-w-0">

                <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                    Manage Inventory
                </h1>

                <p class="text-xs text-navy/45 mt-1 max-w-2xl">
                    Manage products, variants, images, pricing, stock, and seller vouchers.
                </p>

                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-yellow/20 px-2.5 py-1 text-xs font-bold text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    FRONTEND DEMO · HARDCODED DATA
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    data-open-voucher-modal
                    class="inline-flex items-center justify-center gap-2 h-9 px-3.5 rounded-lg border border-gray-border bg-white text-xs font-semibold text-navy/60 hover:border-teal/30 hover:text-teal-dark transition"
                >
                    <x-lucide-ticket-percent class="w-4 h-4" />
                    Create Voucher
                </button>

                <button
                    type="button"
                    data-open-product-modal
                    class="inline-flex items-center justify-center gap-2 h-9 px-3.5 rounded-lg bg-navy hover:bg-navy-light text-xs font-semibold text-white transition"
                >
                    <x-lucide-plus class="w-4 h-4" />
                    Add Product
                </button>
            </div>

        </div>
    </section>


    {{-- =========================================================
        PRODUCTS / VOUCHERS
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl p-2 overflow-x-auto">
        <div class="flex items-center gap-1 min-w-max">

            <button
                type="button"
                data-section-tab="products"
                class="inventory-section-tab inventory-section-tab-active h-9 px-3.5 rounded-lg border border-transparent text-xs font-bold transition"
            >
                Products
                <span class="ml-1 opacity-50">{{ $products->count() }}</span>
            </button>

            <button
                type="button"
                data-section-tab="vouchers"
                class="inventory-section-tab h-9 px-3.5 rounded-lg border border-transparent text-xs font-bold text-navy/45 hover:bg-gray-bg transition"
            >
                Vouchers
                <span class="ml-1 opacity-50">{{ $vouchers->count() }}</span>
            </button>

        </div>
    </section>


    {{-- =========================================================
        PRODUCTS SECTION
    ========================================================= --}}
    <div id="inventoryProductsSection" class="space-y-5">

        {{-- Summary cards --}}
        <section class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">

            <button
                type="button"
                data-stat-filter="active"
                class="inventory-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-teal/25"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="w-9 h-9 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center">
                        <x-lucide-package-check class="w-4 h-4" />
                    </div>

                    <span class="text-[10px] font-semibold text-navy/30">ACTIVE</span>
                </div>

                <p class="mt-4 text-2xl font-bold text-navy">
                    {{ number_format($activeCount) }}
                </p>

                <p class="text-xs text-navy/45">
                    Active Products
                </p>
            </button>


            <button
                type="button"
                data-stat-filter="low"
                class="inventory-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-yellow/30"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="w-9 h-9 rounded-lg bg-yellow/20 text-amber-700 flex items-center justify-center">
                        <x-lucide-triangle-alert class="w-4 h-4" />
                    </div>

                    <span class="text-[10px] font-semibold text-amber-700/60">ALERT</span>
                </div>

                <p class="mt-4 text-2xl font-bold text-amber-700">
                    {{ number_format($lowStockCount) }}
                </p>

                <p class="text-xs text-navy/45">
                    Low Stock
                </p>
            </button>


            <button
                type="button"
                data-stat-filter="out"
                class="inventory-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-red-200"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="w-9 h-9 rounded-lg bg-red-50 text-red-500 flex items-center justify-center">
                        <x-lucide-package-x class="w-4 h-4" />
                    </div>

                    <span class="text-[10px] font-semibold text-red-400">URGENT</span>
                </div>

                <p class="mt-4 text-2xl font-bold text-red-500">
                    {{ number_format($outOfStockCount) }}
                </p>

                <p class="text-xs text-navy/45">
                    Out of Stock
                </p>
            </button>


            <button
                type="button"
                data-stat-filter="archived"
                class="inventory-card text-left bg-white border border-gray-border rounded-xl p-4 hover:shadow-soft hover:border-navy/15"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="w-9 h-9 rounded-lg bg-navy/10 text-navy/55 flex items-center justify-center">
                        <x-lucide-archive class="w-4 h-4" />
                    </div>

                    <span class="text-[10px] font-semibold text-navy/30">HIDDEN</span>
                </div>

                <p class="mt-4 text-2xl font-bold text-navy">
                    {{ number_format($archivedCount) }}
                </p>

                <p class="text-xs text-navy/45">
                    Archived
                </p>
            </button>

        </section>


        {{-- Status tabs --}}
        <section class="bg-white border border-gray-border rounded-xl p-2 overflow-x-auto">
            <div class="flex items-center gap-1 min-w-max">

                @foreach ([
                    ['key' => 'all', 'label' => 'All', 'count' => $products->count()],
                    ['key' => 'active', 'label' => 'Active', 'count' => $activeCount],
                    ['key' => 'low', 'label' => 'Low Stock', 'count' => $lowStockCount],
                    ['key' => 'out', 'label' => 'Out of Stock', 'count' => $outOfStockCount],
                    ['key' => 'archived', 'label' => 'Archived', 'count' => $archivedCount],
                ] as $tab)
                    <button
                        type="button"
                        data-inventory-tab="{{ $tab['key'] }}"
                        class="inventory-tab h-9 px-3 rounded-lg border border-transparent text-xs font-semibold transition
                            {{ $tab['key'] === 'all'
                                ? 'inventory-tab-active'
                                : 'text-navy/50 hover:bg-gray-bg'
                            }}"
                    >
                        {{ $tab['label'] }}
                        <span class="ml-1 opacity-60">
                            {{ $tab['count'] }}
                        </span>
                    </button>
                @endforeach

            </div>
        </section>


        {{-- Filters --}}
        <section class="bg-white border border-gray-border rounded-xl p-3">

            <div class="flex flex-col xl:flex-row xl:items-center gap-2">

                <div class="relative flex-1 min-w-0">
                    <x-lucide-search class="w-3.5 h-3.5 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2" />

                    <input
                        type="text"
                        id="inventorySearch"
                        placeholder="Search product name or SKU..."
                        class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/30 focus:outline-none focus:border-teal/50"
                    >
                </div>


                <select
                    id="inventoryCategoryFilter"
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs text-navy bg-white focus:outline-none focus:border-teal/50"
                >
                    <option value="">All Categories</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>


                <select
                    id="inventoryStockFilter"
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs text-navy bg-white focus:outline-none focus:border-teal/50"
                >
                    <option value="">All Stock Levels</option>
                    <option value="in">In Stock</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>


                <select
                    id="inventorySort"
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs text-navy bg-white focus:outline-none focus:border-teal/50"
                >
                    <option value="newest">Newest</option>
                    <option value="name-asc">Name A-Z</option>
                    <option value="name-desc">Name Z-A</option>
                    <option value="price-asc">Price Low-High</option>
                    <option value="price-desc">Price High-Low</option>
                    <option value="stock-asc">Stock Low-High</option>
                    <option value="stock-desc">Stock High-Low</option>
                </select>


                <button
                    type="button"
                    id="inventoryClearFilters"
                    class="h-9 px-3 rounded-lg border border-gray-border text-xs font-semibold text-navy/45 hover:bg-gray-bg hover:text-navy transition"
                >
                    Clear
                </button>

            </div>


            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">

                <p class="text-xs text-navy/35">
                    Showing
                    <strong id="inventoryVisibleCount" class="text-navy/60">
                        {{ $products->count() }}
                    </strong>
                    of
                    <strong class="text-navy/60">
                        {{ $products->count() }}
                    </strong>
                    products
                </p>

                <p class="text-xs text-navy/30">
                    {{ number_format($totalUnits) }} total active units in stock
                </p>

            </div>

        </section>


        {{-- Products table --}}
        <section class="bg-white border border-gray-border rounded-xl overflow-hidden">

            @if ($products->isEmpty())

                <div class="py-16 px-5 text-center">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                        <x-lucide-package-plus class="w-5 h-5" />
                    </div>

                    <p class="mt-3 text-xs font-semibold text-navy/55">
                        No products yet
                    </p>

                    <p class="mt-1 text-xs text-navy/35">
                        Add your first product to start selling on ShopHop.
                    </p>

                    <button
                        type="button"
                        data-open-product-modal
                        class="mt-4 inline-flex items-center gap-2 h-9 px-3.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy-light transition"
                    >
                        <x-lucide-plus class="w-3.5 h-3.5" />
                        Add Your First Product
                    </button>
                </div>

            @else

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px]" id="inventoryTable">

                        <thead>
                            <tr class="border-b border-gray-border bg-gray-bg/40">
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Product</th>
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">SKU</th>
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Category</th>
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Price</th>
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Stock</th>
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Status</th>
                                <th class="px-4 py-3 text-right text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Actions</th>
                            </tr>
                        </thead>


                        <tbody id="inventoryTableBody" class="divide-y divide-gray-border">

                            @foreach ($products as $product)

                                @php
                                    $meta = $stockMeta($product);
                                    $finalPrice = $sellingPrice($product);

                                    $primaryImage =
                                        $product->images->firstWhere('is_primary', true)
                                        ?? $product->images->first();

                                    $imageUrl = $primaryImage
                                        ? asset('storage/' . $primaryImage->image_path)
                                        : ($product->image
                                            ? asset('storage/' . $product->image)
                                            : null);

                                    $productJson = [
                                        'id' => $product->id,
                                        'name' => $product->name,
                                        'sku' => $product->sku,
                                        'category' => $product->category,
                                        'price' => $product->price,
                                        'discount' => $product->discount,
                                        'stock' => $product->stock,
                                        'low_stock_threshold' =>
                                            $product->low_stock_threshold
                                                ?? $defaultLowStockThreshold,
                                        'has_variants' => (bool) $product->has_variants,
                                        'description' => $product->description,
                                        'legacy_image_url' => $product->image
                                            ? asset('storage/' . $product->image)
                                            : null,
                                        'images' => $product->images
                                            ->map(fn ($image) => [
                                                'id' => $image->id,
                                                'url' => asset('storage/' . $image->image_path),
                                                'is_primary' => (bool) $image->is_primary,
                                            ])
                                            ->values(),
                                        'variants' => $product->variants
                                            ->map(fn ($variant) => [
                                                'id' => $variant->id,
                                                'name' => $variant->name,
                                                'sku' => $variant->sku,
                                                'price' => $variant->price,
                                                'stock' => $variant->stock,
                                                'status' => $variant->status,
                                            ])
                                            ->values(),
                                    ];
                                @endphp


                                <tr
                                    class="inventory-row hover:bg-gray-bg/50 transition"
                                    data-row
                                    data-name="{{ strtolower($product->name . ' ' . ($product->sku ?? '')) }}"
                                    data-name-original="{{ $product->name }}"
                                    data-category="{{ $product->category }}"
                                    data-stock="{{ (int) $product->stock }}"
                                    data-stock-state="{{ $meta['state'] }}"
                                    data-status="{{ $product->status }}"
                                    data-price="{{ (float) $product->price }}"
                                    data-created="{{ optional($product->created_at)->timestamp ?? 0 }}"
                                >

                                    {{-- Product --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">

                                            <div class="w-11 h-11 rounded-xl bg-gray-bg border border-gray-border flex items-center justify-center shrink-0 overflow-hidden">
                                                @if ($imageUrl)
                                                    <img
                                                        src="{{ $imageUrl }}"
                                                        alt="{{ $product->name }}"
                                                        class="w-full h-full object-cover"
                                                    >
                                                @else
                                                    <x-lucide-image class="w-4 h-4 text-navy/20" />
                                                @endif
                                            </div>


                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-navy truncate max-w-[240px]">
                                                    {{ $product->name }}
                                                </p>


                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">

                                                    @if ($product->discount > 0)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-teal/10 text-teal-dark text-[10px] font-bold">
                                                            {{ $product->discount }}% OFF
                                                        </span>
                                                    @endif


                                                    @if ($product->has_variants)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-sky/10 text-sky text-[10px] font-bold">
                                                            {{ $product->variants->count() }} VARIANTS
                                                        </span>
                                                    @endif


                                                    @if ($product->status === 'archived')
                                                        <span class="text-[10px] text-navy/30">
                                                            Hidden from buyers
                                                        </span>
                                                    @elseif ((int) $product->stock === 0)
                                                        <span class="text-[10px] text-red-500">
                                                            Restock required
                                                        </span>
                                                    @elseif ((int) $product->stock <= (int) ($product->low_stock_threshold ?? $defaultLowStockThreshold))
                                                        <span class="text-[10px] text-amber-700">
                                                            Only {{ (int) $product->stock }} left
                                                        </span>
                                                    @endif

                                                </div>
                                            </div>

                                        </div>
                                    </td>


                                    {{-- SKU --}}
                                    <td class="px-4 py-3">
                                        <span class="text-xs font-medium text-navy/45">
                                            {{ $product->sku ?: '—' }}
                                        </span>
                                    </td>


                                    {{-- Category --}}
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 rounded-lg bg-gray-bg text-xs font-medium text-navy/55">
                                            {{ $product->category }}
                                        </span>
                                    </td>


                                    {{-- Price --}}
                                    <td class="px-4 py-3">

                                        @if ($product->discount > 0)
                                            <p class="text-xs font-bold text-teal-dark tabular-nums">
                                                ₱{{ number_format($finalPrice, 2) }}
                                            </p>

                                            <p class="mt-0.5 text-[10px] text-navy/30 line-through tabular-nums">
                                                ₱{{ number_format((float) $product->price, 2) }}
                                            </p>
                                        @else
                                            <p class="text-xs font-semibold text-navy tabular-nums">
                                                ₱{{ number_format((float) $product->price, 2) }}
                                            </p>
                                        @endif

                                    </td>


                                    {{-- Stock --}}
                                    <td class="px-4 py-3">

                                        @if ($product->status === 'active' && ! $product->has_variants)

                                            <form
                                                method="POST"
                                                action="#"
                                                data-demo-form
                                                data-demo-action="stock"
                                                class="quick-stock-form inline-flex items-center gap-1"
                                            >
                                                @csrf

                                                <button
                                                    type="button"
                                                    data-stock-minus
                                                    class="w-7 h-7 rounded-lg border border-gray-border text-navy/40 hover:bg-gray-bg hover:text-navy flex items-center justify-center transition"
                                                >
                                                    <x-lucide-minus class="w-3 h-3" />
                                                </button>

                                                <input
                                                    type="number"
                                                    min="0"
                                                    name="stock"
                                                    value="{{ (int) $product->stock }}"
                                                    data-stock-input
                                                    class="w-14 h-7 rounded-lg border border-gray-border text-center text-xs font-semibold text-navy focus:outline-none focus:border-teal/50"
                                                >

                                                <button
                                                    type="button"
                                                    data-stock-plus
                                                    class="w-7 h-7 rounded-lg border border-gray-border text-navy/40 hover:bg-gray-bg hover:text-navy flex items-center justify-center transition"
                                                >
                                                    <x-lucide-plus class="w-3 h-3" />
                                                </button>

                                                <button
                                                    type="submit"
                                                    data-stock-save
                                                    hidden
                                                    class="h-7 px-2 rounded-lg bg-teal/10 text-teal-dark text-[10px] font-bold hover:bg-teal/15 transition"
                                                >
                                                    Save
                                                </button>
                                            </form>

                                        @elseif ($product->has_variants)

                                            <div>
                                                <p class="text-xs font-semibold text-navy">
                                                    {{ number_format((int) $product->stock) }}
                                                </p>

                                                <p class="text-[10px] text-sky mt-0.5">
                                                    Variant total
                                                </p>
                                            </div>

                                        @else

                                            <span class="text-xs text-navy/35">
                                                {{ number_format((int) $product->stock) }}
                                            </span>

                                        @endif
                                    </td>


                                    {{-- Status --}}
                                    <td class="px-4 py-3">
                                        <span class="inline-flex text-[10px] font-bold px-2 py-1 rounded-full {{ $meta['class'] }}">
                                            {{ $meta['label'] }}
                                        </span>
                                    </td>


                                    {{-- Actions --}}
                                    <td class="px-4 py-3">

                                        <div class="flex items-center justify-end gap-1.5">

                                            <button
                                                type="button"
                                                data-open-product-modal
                                                data-product="{{ e(json_encode($productJson)) }}"
                                                class="h-8 px-2.5 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:text-teal-dark hover:border-teal/30 hover:bg-teal-light transition"
                                            >
                                                Edit
                                            </button>


                                            <button
                                                type="button"
                                                data-open-archive-modal
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                data-is-archived="{{ $product->status === 'archived' ? '1' : '0' }}"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/35 hover:text-coral hover:bg-coral/10 transition"
                                                title="{{ $product->status === 'archived' ? 'Restore product' : 'Archive product' }}"
                                            >
                                                @if ($product->status === 'archived')
                                                    <x-lucide-rotate-ccw class="w-3.5 h-3.5" />
                                                @else
                                                    <x-lucide-archive class="w-3.5 h-3.5" />
                                                @endif
                                            </button>

                                        </div>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>
                </div>


                <div
                    id="inventoryEmptyFilter"
                    hidden
                    class="py-14 px-5 text-center"
                >
                    <div class="w-11 h-11 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                        <x-lucide-search-x class="w-4 h-4" />
                    </div>

                    <p class="mt-3 text-xs font-semibold text-navy/45">
                        No products match your filters.
                    </p>

                    <button
                        type="button"
                        id="inventoryEmptyClear"
                        class="mt-3 text-xs font-bold text-teal-dark hover:text-teal transition"
                    >
                        Clear filters
                    </button>
                </div>

            @endif

        </section>

    </div>


    {{-- =========================================================
        VOUCHERS SECTION
    ========================================================= --}}
    <div id="inventoryVouchersSection" hidden class="space-y-5">

        <section class="bg-white border border-gray-border rounded-xl overflow-hidden">

            <div class="px-5 py-4 border-b border-gray-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                <div>
                    <h2 class="text-base font-bold text-navy">
                        Seller Vouchers
                    </h2>

                    <p class="text-xs text-navy/40 mt-0.5">
                        Create store-wide or product-specific discounts.
                    </p>
                </div>


                <button
                    type="button"
                    data-open-voucher-modal
                    class="inline-flex items-center justify-center gap-2 h-9 px-3.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy-light transition"
                >
                    <x-lucide-plus class="w-3.5 h-3.5" />
                    Create Voucher
                </button>

            </div>


            @if ($vouchers->isEmpty())

                <div class="py-16 px-5 text-center">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-gray-bg text-navy/25 flex items-center justify-center">
                        <x-lucide-ticket-percent class="w-5 h-5" />
                    </div>

                    <p class="mt-3 text-xs font-semibold text-navy/55">
                        No vouchers yet
                    </p>

                    <p class="mt-1 text-xs text-navy/35">
                        Create a voucher to offer discounts to buyers.
                    </p>
                </div>

            @else

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px]">

                        <thead>
                            <tr class="border-b border-gray-border bg-gray-bg/40">
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Voucher</th>
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Discount</th>
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Products</th>
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Usage</th>
                                <th class="px-4 py-3 text-left text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Status</th>
                                <th class="px-4 py-3 text-right text-[10px] uppercase tracking-[0.12em] font-bold text-navy/30">Actions</th>
                            </tr>
                        </thead>


                        <tbody class="divide-y divide-gray-border">

                            @foreach ($vouchers as $voucher)

                                @php
                                    $voucherJson = [
                                        'id' => $voucher->id,
                                        'name' => $voucher->name,
                                        'code' => $voucher->code,
                                        'type' => $voucher->type,
                                        'value' => $voucher->value,
                                        'min_order_amount' => $voucher->min_order_amount,
                                        'usage_limit' => $voucher->usage_limit,
                                        'starts_at' => optional($voucher->starts_at)->format('Y-m-d\TH:i'),
                                        'ends_at' => optional($voucher->ends_at)->format('Y-m-d\TH:i'),
                                        'product_ids' => $voucher->products->pluck('id')->values(),
                                    ];
                                @endphp


                                <tr class="hover:bg-gray-bg/50 transition">

                                    <td class="px-4 py-3">
                                        <p class="text-xs font-semibold text-navy">
                                            {{ $voucher->name }}
                                        </p>

                                        <p class="text-[10px] font-bold text-teal-dark mt-0.5">
                                            {{ $voucher->code }}
                                        </p>
                                    </td>


                                    <td class="px-4 py-3">
                                        <p class="text-xs font-semibold text-navy">
                                            @if ($voucher->type === 'percent')
                                                {{ number_format((float) $voucher->value, 0) }}%
                                            @else
                                                ₱{{ number_format((float) $voucher->value, 2) }}
                                            @endif
                                        </p>

                                        <p class="text-[10px] text-navy/30 mt-0.5">
                                            Min. ₱{{ number_format((float) $voucher->min_order_amount, 2) }}
                                        </p>
                                    </td>


                                    <td class="px-4 py-3">
                                        <span class="text-xs text-navy/45">
                                            {{ $voucher->products->isEmpty()
                                                ? 'Store-wide'
                                                : $voucher->products->count() . ' selected product(s)'
                                            }}
                                        </span>
                                    </td>


                                    <td class="px-4 py-3">
                                        <span class="text-xs text-navy/45">
                                            {{ number_format((int) $voucher->used_count) }}
                                            /
                                            {{ $voucher->usage_limit !== null
                                                ? number_format((int) $voucher->usage_limit)
                                                : '∞'
                                            }}
                                        </span>
                                    </td>


                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold
                                                {{ $voucher->status === 'active'
                                                    ? 'bg-teal/10 text-teal-dark'
                                                    : 'bg-navy/10 text-navy/45'
                                                }}"
                                        >
                                            {{ ucfirst($voucher->status) }}
                                        </span>
                                    </td>


                                    <td class="px-4 py-3">

                                        <div class="flex items-center justify-end gap-1.5">

                                            <button
                                                type="button"
                                                data-open-voucher-modal
                                                data-voucher="{{ e(json_encode($voucherJson)) }}"
                                                class="h-8 px-2.5 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:text-teal-dark hover:border-teal/30 transition"
                                            >
                                                Edit
                                            </button>


                                            <form
                                                method="POST"
                                                action="#"
                                                data-demo-form
                                                data-demo-action="voucher-toggle"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="h-8 px-2.5 rounded-lg border border-gray-border text-xs font-semibold text-navy/45 hover:bg-gray-bg transition"
                                                >
                                                    {{ $voucher->status === 'active' ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>


                                            <form
                                                method="POST"
                                                action="#"
                                                data-demo-form
                                                data-demo-action="voucher-delete"
                                                
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/30 hover:bg-red-50 hover:text-red-500 transition"
                                                    title="Delete voucher"
                                                >
                                                    <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>
                    </table>
                </div>

            @endif

        </section>

    </div>

</div>


{{-- =========================================================
    PRODUCT MODAL
========================================================= --}}
<div
    id="productModal"
    hidden
    class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-5"
    aria-hidden="true"
>

    <button
        type="button"
        id="productModalBackdrop"
        class="absolute inset-0 bg-navy/50 backdrop-blur-[2px]"
        aria-label="Close product form"
    ></button>


    <div
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[94vh] overflow-hidden flex flex-col"
        role="dialog"
        aria-modal="true"
        aria-labelledby="productModalTitle"
    >

        <div class="flex items-start justify-between gap-3 px-5 sm:px-6 py-4 border-b border-gray-border shrink-0">

            <div>
                <p id="productModalTitle" class="text-base font-bold text-navy">
                    Add Product
                </p>

                <p id="productModalSubtitle" class="text-xs text-navy/35 mt-0.5">
                    Create a complete product listing.
                </p>
            </div>


            <button
                type="button"
                data-close-product-modal
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/35 hover:bg-gray-bg hover:text-navy transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <form
            id="productForm"
            method="POST"
            action="#"
            data-demo-form
            data-demo-action="product"
            enctype="multipart/form-data"
            class="flex-1 overflow-y-auto content-scrollbar"
        >
            @csrf

            <input type="hidden" name="product_id" id="productId">
            <input type="hidden" name="primary_image_id" id="primaryImageId">

            <div id="removeImageInputs"></div>


            <div class="px-5 sm:px-6 py-5 space-y-6">

                {{-- Basic --}}
                <section>

                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-teal/10 text-teal-dark flex items-center justify-center">
                            <x-lucide-package class="w-3.5 h-3.5" />
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-navy">
                                Basic Information
                            </h3>

                            <p class="text-[10px] text-navy/35">
                                Main product details and seller SKU.
                            </p>
                        </div>
                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-navy/60">
                                Product Name <span class="text-coral">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                id="productName"
                                required
                                maxlength="255"
                                placeholder="e.g. Wireless Bluetooth Mouse"
                                class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/25 focus:outline-none focus:border-teal/50"
                            >
                        </div>


                        <div>
                            <label class="text-xs font-semibold text-navy/60">
                                Seller SKU
                            </label>

                            <input
                                type="text"
                                name="sku"
                                id="productSku"
                                maxlength="100"
                                placeholder="e.g. MOUSE-BT-001"
                                class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/25 focus:outline-none focus:border-teal/50"
                            >

                            <p class="mt-1 text-[10px] text-navy/30">
                                Optional. Must be unique in your store.
                            </p>
                        </div>


                        <div>
                            <label class="text-xs font-semibold text-navy/60">
                                Category <span class="text-coral">*</span>
                            </label>

                            <select
                                name="category"
                                id="productCategory"
                                required
                                class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs text-navy bg-white focus:outline-none focus:border-teal/50"
                            >
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}">
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-navy/60">
                                Description
                            </label>

                            <textarea
                                name="description"
                                id="productDescription"
                                rows="4"
                                placeholder="Describe features, materials, usage, specifications, and important buyer information..."
                                class="w-full mt-1 px-3 py-2.5 rounded-lg border border-gray-border text-xs text-navy placeholder:text-navy/25 focus:outline-none focus:border-teal/50 resize-y"
                            ></textarea>
                        </div>

                    </div>

                </section>


                {{-- Images --}}
                <section>

                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-sky/10 text-sky flex items-center justify-center">
                            <x-lucide-images class="w-3.5 h-3.5" />
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-navy">
                                Product Images
                            </h3>

                            <p class="text-[10px] text-navy/35">
                                Up to 8 gallery images. Choose a primary image.
                            </p>
                        </div>
                    </div>


                    <div
                        id="existingImageGallery"
                        class="grid grid-cols-3 sm:grid-cols-5 gap-2 mb-3"
                    ></div>


                    <label
                        class="block rounded-xl border border-dashed border-gray-border bg-gray-bg/50 p-4 text-center cursor-pointer hover:border-teal/30 hover:bg-teal-light/40 transition"
                    >
                        <x-lucide-image-plus class="w-5 h-5 mx-auto text-teal-dark" />

                        <p class="mt-2 text-xs font-semibold text-navy/55">
                            Add product images
                        </p>

                        <p class="mt-1 text-[10px] text-navy/30">
                            JPG, PNG, WebP · max 5MB each
                        </p>

                        <input
                            type="file"
                            name="images[]"
                            id="productImages"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            class="hidden"
                        >
                    </label>


                    <div
                        id="newImagePreview"
                        class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-3"
                    ></div>

                </section>


                {{-- Pricing --}}
                <section>

                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-yellow/20 text-amber-700 flex items-center justify-center">
                            <x-lucide-tags class="w-3.5 h-3.5" />
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-navy">
                                Pricing
                            </h3>

                            <p class="text-[10px] text-navy/35">
                                Base price and product-wide discount.
                            </p>
                        </div>
                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                        <div>
                            <label class="text-xs font-semibold text-navy/60">
                                Regular Price <span class="text-coral">*</span>
                            </label>

                            <div class="relative mt-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-navy/35">
                                    ₱
                                </span>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="price"
                                    id="productPrice"
                                    required
                                    class="w-full h-10 pl-7 pr-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                                >
                            </div>
                        </div>


                        <div>
                            <label class="text-xs font-semibold text-navy/60">
                                Discount
                            </label>

                            <div class="relative mt-1">
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    name="discount"
                                    id="productDiscount"
                                    value="0"
                                    class="w-full h-10 px-3 pr-8 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                                >

                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-navy/35">
                                    %
                                </span>
                            </div>
                        </div>


                        <div>
                            <label class="text-xs font-semibold text-navy/60">
                                Selling Price
                            </label>

                            <div class="mt-1 h-10 px-3 rounded-lg bg-teal/10 border border-teal/10 flex items-center">
                                <span id="productSellingPrice" class="text-xs font-bold text-teal-dark">
                                    ₱0.00
                                </span>
                            </div>
                        </div>

                    </div>

                </section>


                {{-- Inventory --}}
                <section>

                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-coral/10 text-coral flex items-center justify-center">
                            <x-lucide-boxes class="w-3.5 h-3.5" />
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-navy">
                                Inventory
                            </h3>

                            <p class="text-[10px] text-navy/35">
                                Stock and product-specific low-stock warning.
                            </p>
                        </div>
                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                        <div>
                            <label class="text-xs font-semibold text-navy/60">
                                Stock Quantity
                            </label>

                            <input
                                type="number"
                                min="0"
                                name="stock"
                                id="productStock"
                                value="0"
                                class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                            >

                            <p class="mt-1 text-[10px] text-navy/30">
                                Disabled when variants are enabled.
                            </p>
                        </div>


                        <div>
                            <label class="text-xs font-semibold text-navy/60">
                                Low Stock Alert
                            </label>

                            <input
                                type="number"
                                min="0"
                                name="low_stock_threshold"
                                id="productLowStockThreshold"
                                value="{{ $defaultLowStockThreshold }}"
                                class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs text-navy focus:outline-none focus:border-teal/50"
                            >

                            <p class="mt-1 text-[10px] text-navy/30">
                                Low-stock badge appears at or below this quantity.
                            </p>
                        </div>

                    </div>

                </section>


                {{-- Variants --}}
                <section class="rounded-xl border border-gray-border overflow-hidden">

                    <div class="p-4 bg-gray-bg/50 flex items-center justify-between gap-3">

                        <div>
                            <h3 class="text-sm font-bold text-navy">
                                Product Variants
                            </h3>

                            <p class="text-[10px] text-navy/35 mt-0.5">
                                Example: Black / Large, Red / Small, 128GB.
                            </p>
                        </div>


                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="has_variants" value="0">

                            <input
                                type="checkbox"
                                name="has_variants"
                                id="productHasVariants"
                                value="1"
                                class="w-4 h-4 accent-teal"
                            >

                            <span class="text-xs font-semibold text-navy/55">
                                Has variants
                            </span>
                        </label>

                    </div>


                    <div id="variantSection" hidden class="p-4">

                        <div class="flex items-center justify-between gap-3 mb-3">

                            <p class="text-xs text-navy/40">
                                Parent stock is automatically calculated from active variants.
                            </p>

                            <button
                                type="button"
                                id="addVariantButton"
                                class="h-8 px-3 rounded-lg bg-teal/10 text-teal-dark text-xs font-bold hover:bg-teal/15 transition"
                            >
                                + Add Variant
                            </button>

                        </div>


                        <div id="variantRows" class="space-y-2"></div>


                        <div id="variantEmpty" class="py-7 text-center rounded-xl bg-gray-bg/60">
                            <p class="text-xs text-navy/35">
                                Add at least one variant.
                            </p>
                        </div>

                    </div>

                </section>

            </div>


            <div class="sticky bottom-0 bg-white border-t border-gray-border px-5 sm:px-6 py-4 flex items-center justify-end gap-2">

                <button
                    type="button"
                    data-close-product-modal
                    class="h-9 px-3.5 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="h-9 px-4 rounded-lg bg-navy hover:bg-navy-light text-xs font-semibold text-white transition inline-flex items-center gap-2"
                >
                    <x-lucide-save class="w-3.5 h-3.5" />
                    <span id="productSubmitText">Add Product</span>
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
    ARCHIVE MODAL
========================================================= --}}
<div
    id="archiveModal"
    hidden
    class="fixed inset-0 z-[110] flex items-center justify-center p-4"
    aria-hidden="true"
>

    <button
        type="button"
        id="archiveModalBackdrop"
        class="absolute inset-0 bg-navy/50 backdrop-blur-[2px]"
        aria-label="Close confirmation"
    ></button>


    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-5">

        <div
            id="archiveModalIcon"
            class="w-10 h-10 rounded-xl bg-coral/10 text-coral flex items-center justify-center"
        >
            <x-lucide-archive class="w-4 h-4" />
        </div>


        <p class="text-base font-bold text-navy mt-3" id="archiveModalHeading">
            Archive this product?
        </p>


        <p class="text-xs text-navy/45 mt-1 leading-relaxed" id="archiveModalSubtext">
            Archived products won't be visible to buyers.
        </p>


        <div class="flex items-center justify-end gap-2 mt-5">

            <button
                type="button"
                data-close-archive-modal
                class="h-9 px-3.5 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
            >
                Cancel
            </button>


            <form id="archiveForm" method="POST" action="#" data-demo-form data-demo-action="archive">
                @csrf

                <button
                    type="submit"
                    id="archiveSubmitBtn"
                    class="h-9 px-3.5 rounded-lg bg-coral hover:bg-coral/90 text-xs font-semibold text-white transition"
                >
                    Archive
                </button>
            </form>

        </div>

    </div>

</div>


{{-- =========================================================
    VOUCHER MODAL
========================================================= --}}
<div
    id="voucherModal"
    hidden
    class="fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-5"
    aria-hidden="true"
>

    <button
        type="button"
        id="voucherModalBackdrop"
        class="absolute inset-0 bg-navy/50 backdrop-blur-[2px]"
        aria-label="Close voucher form"
    ></button>


    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-hidden flex flex-col">

        <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-gray-border">

            <div>
                <p id="voucherModalTitle" class="text-base font-bold text-navy">
                    Create Voucher
                </p>

                <p class="text-xs text-navy/35 mt-0.5">
                    Create a store-wide or product-specific discount.
                </p>
            </div>


            <button
                type="button"
                data-close-voucher-modal
                class="w-8 h-8 rounded-lg flex items-center justify-center text-navy/35 hover:bg-gray-bg transition"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>

        </div>


        <form
            method="POST"
            action="#"
            data-demo-form
            data-demo-action="voucher"
            id="voucherForm"
            class="flex-1 overflow-y-auto content-scrollbar"
        >
            @csrf

            <input type="hidden" name="voucher_id" id="voucherId">


            <div class="px-5 py-5 space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    <div>
                        <label class="text-xs font-semibold text-navy/60">
                            Voucher Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="voucherName"
                            required
                            placeholder="e.g. Weekend Sale"
                            class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                        >
                    </div>


                    <div>
                        <label class="text-xs font-semibold text-navy/60">
                            Voucher Code
                        </label>

                        <input
                            type="text"
                            name="code"
                            id="voucherCode"
                            required
                            placeholder="e.g. WEEKEND10"
                            class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs uppercase focus:outline-none focus:border-teal/50"
                        >
                    </div>


                    <div>
                        <label class="text-xs font-semibold text-navy/60">
                            Discount Type
                        </label>

                        <select
                            name="type"
                            id="voucherType"
                            required
                            class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs bg-white focus:outline-none focus:border-teal/50"
                        >
                            <option value="percent">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₱)</option>
                        </select>
                    </div>


                    <div>
                        <label class="text-xs font-semibold text-navy/60">
                            Discount Value
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            name="value"
                            id="voucherValue"
                            required
                            class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                        >
                    </div>


                    <div>
                        <label class="text-xs font-semibold text-navy/60">
                            Minimum Order
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="min_order_amount"
                            id="voucherMinOrder"
                            value="0"
                            class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                        >
                    </div>


                    <div>
                        <label class="text-xs font-semibold text-navy/60">
                            Usage Limit
                        </label>

                        <input
                            type="number"
                            min="1"
                            name="usage_limit"
                            id="voucherUsageLimit"
                            placeholder="Blank = unlimited"
                            class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                        >
                    </div>


                    <div>
                        <label class="text-xs font-semibold text-navy/60">
                            Starts At
                        </label>

                        <input
                            type="datetime-local"
                            name="starts_at"
                            id="voucherStartsAt"
                            class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                        >
                    </div>


                    <div>
                        <label class="text-xs font-semibold text-navy/60">
                            Ends At
                        </label>

                        <input
                            type="datetime-local"
                            name="ends_at"
                            id="voucherEndsAt"
                            class="w-full h-10 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                        >
                    </div>

                </div>


                <div class="rounded-xl border border-gray-border overflow-hidden">

                    <div class="px-4 py-3 bg-gray-bg/60">
                        <p class="text-xs font-semibold text-navy">
                            Applicable Products
                        </p>

                        <p class="text-[10px] text-navy/35 mt-0.5">
                            Leave all unchecked to make the voucher store-wide.
                        </p>
                    </div>


                    <div class="max-h-52 overflow-y-auto p-3 space-y-1 content-scrollbar">

                        @foreach ($products->where('status', 'active') as $product)

                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-bg cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="product_ids[]"
                                    value="{{ $product->id }}"
                                    data-voucher-product
                                    class="w-4 h-4 accent-teal"
                                >

                                <span class="text-xs font-medium text-navy/60">
                                    {{ $product->name }}
                                </span>
                            </label>

                        @endforeach

                    </div>

                </div>

            </div>


            <div class="sticky bottom-0 bg-white border-t border-gray-border px-5 py-4 flex justify-end gap-2">

                <button
                    type="button"
                    data-close-voucher-modal
                    class="h-9 px-3.5 rounded-lg border border-gray-border text-xs font-semibold text-navy/55 hover:bg-gray-bg transition"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="h-9 px-4 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy-light transition"
                >
                    Save Voucher
                </button>

            </div>

        </form>

    </div>

</div>


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const currencyFormatter = new Intl.NumberFormat(
        'en-PH',
        {
            style: 'currency',
            currency: 'PHP'
        }
    );

    function parseJson(raw) {
        if (!raw) return null;

        try {
            return JSON.parse(raw);
        } catch (error) {
            console.error('Invalid JSON payload', error);
            return null;
        }
    }


    /* =========================================================
       PRODUCTS / VOUCHERS SECTION SWITCH
    ========================================================= */
    const sectionTabs =
        Array.from(
            document.querySelectorAll('[data-section-tab]')
        );

    const productsSection =
        document.getElementById('inventoryProductsSection');

    const vouchersSection =
        document.getElementById('inventoryVouchersSection');


    function setSection(section) {
        const productsActive =
            section !== 'vouchers';

        productsSection.hidden =
            !productsActive;

        vouchersSection.hidden =
            productsActive;

        sectionTabs.forEach(function (button) {
            const active =
                button.dataset.sectionTab ===
                (productsActive ? 'products' : 'vouchers');

            button.classList.toggle(
                'inventory-section-tab-active',
                active
            );

            button.classList.toggle(
                'text-navy/45',
                !active
            );
        });
    }


    sectionTabs.forEach(function (button) {
        button.addEventListener('click', function () {
            setSection(button.dataset.sectionTab);
        });
    });


    /* =========================================================
       PRODUCT MODAL
    ========================================================= */
    const productModal =
        document.getElementById('productModal');

    const productForm =
        document.getElementById('productForm');

    const productModalTitle =
        document.getElementById('productModalTitle');

    const productModalSubtitle =
        document.getElementById('productModalSubtitle');

    const productSubmitText =
        document.getElementById('productSubmitText');

    const productId =
        document.getElementById('productId');

    const productName =
        document.getElementById('productName');

    const productSku =
        document.getElementById('productSku');

    const productCategory =
        document.getElementById('productCategory');

    const productPrice =
        document.getElementById('productPrice');

    const productDiscount =
        document.getElementById('productDiscount');

    const productStock =
        document.getElementById('productStock');

    const productLowStockThreshold =
        document.getElementById('productLowStockThreshold');

    const productDescription =
        document.getElementById('productDescription');

    const productHasVariants =
        document.getElementById('productHasVariants');

    const productSellingPrice =
        document.getElementById('productSellingPrice');

    const productImages =
        document.getElementById('productImages');

    const existingImageGallery =
        document.getElementById('existingImageGallery');

    const newImagePreview =
        document.getElementById('newImagePreview');

    const removeImageInputs =
        document.getElementById('removeImageInputs');

    const primaryImageId =
        document.getElementById('primaryImageId');

    const variantSection =
        document.getElementById('variantSection');

    const variantRows =
        document.getElementById('variantRows');

    const variantEmpty =
        document.getElementById('variantEmpty');

    const addVariantButton =
        document.getElementById('addVariantButton');

    let variantIndex = 0;
    let removedImageIds = new Set();


    function updateSellingPrice() {
        const price =
            Math.max(
                0,
                parseFloat(productPrice?.value || '0') || 0
            );

        const discount =
            Math.min(
                100,
                Math.max(
                    0,
                    parseFloat(productDiscount?.value || '0') || 0
                )
            );

        productSellingPrice.textContent =
            currencyFormatter.format(
                price - (price * (discount / 100))
            );
    }


    function renderRemoveImageInputs() {
        removeImageInputs.innerHTML = '';

        removedImageIds.forEach(function (id) {
            const input =
                document.createElement('input');

            input.type = 'hidden';
            input.name = 'remove_image_ids[]';
            input.value = id;

            removeImageInputs.appendChild(input);
        });
    }


    function renderExistingImages(images) {
        existingImageGallery.innerHTML = '';
        removedImageIds = new Set();
        primaryImageId.value = '';

        renderRemoveImageInputs();

        (images || []).forEach(function (image) {
            const card =
                document.createElement('div');

            card.className =
                'relative rounded-xl overflow-hidden border border-gray-border bg-gray-bg aspect-square';

            card.dataset.imageId =
                image.id;

            card.innerHTML = `
                <img src="${image.url}" alt="" class="w-full h-full object-cover">

                <div class="absolute inset-x-1 bottom-1 flex gap-1">
                    <button
                        type="button"
                        data-make-primary="${image.id}"
                        class="flex-1 h-6 rounded-md bg-white/90 text-[9px] font-bold ${
                            image.is_primary
                                ? 'text-teal-dark'
                                : 'text-navy/45'
                        }"
                    >
                        ${image.is_primary ? 'Primary' : 'Set Primary'}
                    </button>

                    <button
                        type="button"
                        data-remove-image="${image.id}"
                        class="w-6 h-6 rounded-md bg-white/90 text-red-500 text-xs font-bold"
                    >
                        ×
                    </button>
                </div>
            `;

            existingImageGallery.appendChild(card);

            if (image.is_primary) {
                primaryImageId.value =
                    image.id;
            }
        });
    }


    existingImageGallery?.addEventListener(
        'click',
        function (event) {
            const primaryButton =
                event.target.closest('[data-make-primary]');

            const removeButton =
                event.target.closest('[data-remove-image]');


            if (primaryButton) {
                primaryImageId.value =
                    primaryButton.dataset.makePrimary;

                existingImageGallery
                    .querySelectorAll('[data-make-primary]')
                    .forEach(function (button) {
                        button.textContent =
                            'Set Primary';

                        button.classList.remove(
                            'text-teal-dark'
                        );

                        button.classList.add(
                            'text-navy/45'
                        );
                    });

                primaryButton.textContent =
                    'Primary';

                primaryButton.classList.remove(
                    'text-navy/45'
                );

                primaryButton.classList.add(
                    'text-teal-dark'
                );
            }


            if (removeButton) {
                const id =
                    Number(removeButton.dataset.removeImage);

                removedImageIds.add(id);

                renderRemoveImageInputs();

                removeButton
                    .closest('[data-image-id]')
                    ?.classList.add(
                        'opacity-30',
                        'grayscale'
                    );

                if (
                    String(primaryImageId.value) ===
                    String(id)
                ) {
                    primaryImageId.value = '';
                }
            }
        }
    );


    productImages?.addEventListener(
        'change',
        function () {
            newImagePreview.innerHTML = '';

            Array.from(productImages.files || [])
                .slice(0, 8)
                .forEach(function (file) {
                    const reader =
                        new FileReader();

                    reader.onload =
                        function (event) {
                            const card =
                                document.createElement('div');

                            card.className =
                                'rounded-xl overflow-hidden border border-gray-border bg-gray-bg aspect-square';

                            card.innerHTML =
                                `<img src="${event.target.result}" alt="" class="w-full h-full object-cover">`;

                            newImagePreview.appendChild(card);
                        };

                    reader.readAsDataURL(file);
                });
        }
    );


    function refreshVariantEmpty() {
        variantEmpty.hidden =
            variantRows.querySelectorAll('[data-variant-row]').length > 0;
    }


    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }


    function addVariantRow(variant = null) {
        const index =
            variantIndex++;

        const row =
            document.createElement('div');

        row.dataset.variantRow = '1';

        row.className =
            'rounded-xl border border-gray-border p-3 bg-white';

        row.innerHTML = `
            <input
                type="hidden"
                name="variants[${index}][id]"
                value="${escapeHtml(variant?.id ?? '')}"
            >

            <div class="grid grid-cols-1 sm:grid-cols-[1.4fr_1fr_1fr_.7fr_auto] gap-2 items-end">

                <div>
                    <label class="text-[10px] font-semibold text-navy/45">
                        Variant Name
                    </label>

                    <input
                        type="text"
                        name="variants[${index}][name]"
                        value="${escapeHtml(variant?.name ?? '')}"
                        placeholder="Black / Large"
                        required
                        class="w-full h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                    >
                </div>

                <div>
                    <label class="text-[10px] font-semibold text-navy/45">
                        SKU
                    </label>

                    <input
                        type="text"
                        name="variants[${index}][sku]"
                        value="${escapeHtml(variant?.sku ?? '')}"
                        placeholder="Optional"
                        class="w-full h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                    >
                </div>

                <div>
                    <label class="text-[10px] font-semibold text-navy/45">
                        Price Override
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="variants[${index}][price]"
                        value="${escapeHtml(variant?.price ?? '')}"
                        placeholder="Base price"
                        class="w-full h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                    >
                </div>

                <div>
                    <label class="text-[10px] font-semibold text-navy/45">
                        Stock
                    </label>

                    <input
                        type="number"
                        min="0"
                        name="variants[${index}][stock]"
                        value="${escapeHtml(variant?.stock ?? 0)}"
                        required
                        class="w-full h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs focus:outline-none focus:border-teal/50"
                    >
                </div>

                <div class="flex items-center gap-1">

                    <select
                        name="variants[${index}][status]"
                        class="h-9 px-2 rounded-lg border border-gray-border text-[10px] bg-white"
                    >
                        <option value="active" ${variant?.status !== 'archived' ? 'selected' : ''}>
                            Active
                        </option>

                        <option value="archived" ${variant?.status === 'archived' ? 'selected' : ''}>
                            Archived
                        </option>
                    </select>

                    <button
                        type="button"
                        data-remove-variant
                        class="w-9 h-9 rounded-lg border border-gray-border text-red-400 hover:bg-red-50 flex items-center justify-center"
                    >
                        ×
                    </button>

                </div>

            </div>
        `;

        variantRows.appendChild(row);

        refreshVariantEmpty();
    }


    variantRows?.addEventListener(
        'click',
        function (event) {
            const button =
                event.target.closest('[data-remove-variant]');

            if (!button) return;

            button
                .closest('[data-variant-row]')
                ?.remove();

            refreshVariantEmpty();
        }
    );


    addVariantButton?.addEventListener(
        'click',
        function () {
            addVariantRow();
        }
    );


    function toggleVariants() {
        const enabled =
            productHasVariants.checked;

        variantSection.hidden =
            !enabled;

        productStock.disabled =
            enabled;

        productStock.classList.toggle(
            'bg-gray-bg',
            enabled
        );

        if (
            enabled &&
            variantRows.querySelectorAll('[data-variant-row]').length === 0
        ) {
            addVariantRow();
        }
    }


    productHasVariants?.addEventListener(
        'change',
        toggleVariants
    );


    function resetProductForm() {
        productForm.reset();

        productId.value = '';
        productSku.value = '';
        productDiscount.value = 0;
        productStock.value = 0;
        productLowStockThreshold.value =
            {{ $defaultLowStockThreshold }};

        productHasVariants.checked =
            false;

        productStock.disabled =
            false;

        variantSection.hidden =
            true;

        primaryImageId.value =
            '';

        existingImageGallery.innerHTML =
            '';

        newImagePreview.innerHTML =
            '';

        removeImageInputs.innerHTML =
            '';

        variantRows.innerHTML =
            '';

        variantIndex =
            0;

        removedImageIds =
            new Set();

        refreshVariantEmpty();
        updateSellingPrice();
    }


    function openProductModal(product = null) {
        resetProductForm();

        if (product) {
            productModalTitle.textContent =
                'Edit Product';

            productModalSubtitle.textContent =
                'Update product information, images, pricing, variants, and inventory.';

            productSubmitText.textContent =
                'Save Changes';

            productId.value =
                product.id ?? '';

            productName.value =
                product.name ?? '';

            productSku.value =
                product.sku ?? '';

            productCategory.value =
                product.category ?? '';

            productPrice.value =
                product.price ?? '';

            productDiscount.value =
                product.discount ?? 0;

            productStock.value =
                product.stock ?? 0;

            productLowStockThreshold.value =
                product.low_stock_threshold ??
                {{ $defaultLowStockThreshold }};

            productDescription.value =
                product.description ?? '';

            productHasVariants.checked =
                Boolean(product.has_variants);

            renderExistingImages(
                product.images || []
            );

            if (
                (!product.images || product.images.length === 0) &&
                product.legacy_image_url
            ) {
                const legacy =
                    document.createElement('div');

                legacy.className =
                    'rounded-xl overflow-hidden border border-gray-border bg-gray-bg aspect-square';

                legacy.innerHTML =
                    `<img src="${product.legacy_image_url}" alt="" class="w-full h-full object-cover">`;

                existingImageGallery.appendChild(legacy);
            }

            (product.variants || []).forEach(
                function (variant) {
                    addVariantRow(variant);
                }
            );

            toggleVariants();
        } else {
            productModalTitle.textContent =
                'Add Product';

            productModalSubtitle.textContent =
                'Create a complete product listing.';

            productSubmitText.textContent =
                'Add Product';
        }

        updateSellingPrice();

        productModal.hidden =
            false;

        document.body.style.overflow =
            'hidden';

        setTimeout(
            function () {
                productName?.focus();
            },
            50
        );
    }


    function closeProductModal() {
        productModal.hidden =
            true;

        document.body.style.overflow =
            '';
    }


    document
        .querySelectorAll('[data-open-product-modal]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    openProductModal(
                        parseJson(button.dataset.product)
                    );
                }
            );
        });


    document
        .querySelectorAll('[data-close-product-modal]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                closeProductModal
            );
        });


    document
        .getElementById('productModalBackdrop')
        ?.addEventListener(
            'click',
            closeProductModal
        );


    productPrice?.addEventListener(
        'input',
        updateSellingPrice
    );

    productDiscount?.addEventListener(
        'input',
        updateSellingPrice
    );


    /* =========================================================
       ARCHIVE MODAL
    ========================================================= */
    const archiveModal =
        document.getElementById('archiveModal');

    const archiveForm =
        document.getElementById('archiveForm');

    const archiveModalHeading =
        document.getElementById('archiveModalHeading');

    const archiveModalSubtext =
        document.getElementById('archiveModalSubtext');

    const archiveSubmitBtn =
        document.getElementById('archiveSubmitBtn');

    const archiveModalIcon =
        document.getElementById('archiveModalIcon');

    const archiveUrlTemplate = "#";


    function openArchiveModal(
        id,
        name,
        isArchived
    ) {
        archiveForm.action =
            archiveUrlTemplate.replace(
                '__ID__',
                id
            );

        if (isArchived) {
            archiveModalHeading.textContent =
                'Restore ' + name + '?';

            archiveModalSubtext.textContent =
                'This product will become visible to buyers again.';

            archiveSubmitBtn.textContent =
                'Restore';

            archiveSubmitBtn.classList.remove(
                'bg-coral',
                'hover:bg-coral/90'
            );

            archiveSubmitBtn.classList.add(
                'bg-navy',
                'hover:bg-navy-light'
            );

            archiveModalIcon.classList.remove(
                'bg-coral/10',
                'text-coral'
            );

            archiveModalIcon.classList.add(
                'bg-teal/10',
                'text-teal-dark'
            );
        } else {
            archiveModalHeading.textContent =
                'Archive ' + name + '?';

            archiveModalSubtext.textContent =
                "Archived products won't be visible to buyers. You can restore them from the Archived tab.";

            archiveSubmitBtn.textContent =
                'Archive';

            archiveSubmitBtn.classList.remove(
                'bg-navy',
                'hover:bg-navy-light'
            );

            archiveSubmitBtn.classList.add(
                'bg-coral',
                'hover:bg-coral/90'
            );

            archiveModalIcon.classList.remove(
                'bg-teal/10',
                'text-teal-dark'
            );

            archiveModalIcon.classList.add(
                'bg-coral/10',
                'text-coral'
            );
        }

        archiveModal.hidden =
            false;

        document.body.style.overflow =
            'hidden';
    }


    function closeArchiveModal() {
        archiveModal.hidden =
            true;

        document.body.style.overflow =
            '';
    }


    document
        .querySelectorAll('[data-open-archive-modal]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    openArchiveModal(
                        button.dataset.productId,
                        button.dataset.productName || 'this product',
                        button.dataset.isArchived === '1'
                    );
                }
            );
        });


    document
        .querySelectorAll('[data-close-archive-modal]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                closeArchiveModal
            );
        });


    document
        .getElementById('archiveModalBackdrop')
        ?.addEventListener(
            'click',
            closeArchiveModal
        );


    /* =========================================================
       QUICK STOCK
    ========================================================= */
    document
        .querySelectorAll('.quick-stock-form')
        .forEach(function (form) {
            const input =
                form.querySelector('[data-stock-input]');

            const minus =
                form.querySelector('[data-stock-minus]');

            const plus =
                form.querySelector('[data-stock-plus]');

            const save =
                form.querySelector('[data-stock-save]');

            const original =
                parseInt(input.value || '0', 10) || 0;


            function refresh() {
                const current =
                    Math.max(
                        0,
                        parseInt(input.value || '0', 10) || 0
                    );

                input.value =
                    current;

                save.hidden =
                    current === original;
            }


            minus?.addEventListener(
                'click',
                function () {
                    input.value =
                        Math.max(
                            0,
                            (parseInt(input.value || '0', 10) || 0) - 1
                        );

                    refresh();
                }
            );


            plus?.addEventListener(
                'click',
                function () {
                    input.value =
                        (parseInt(input.value || '0', 10) || 0) + 1;

                    refresh();
                }
            );


            input?.addEventListener(
                'input',
                refresh
            );
        });


    /* =========================================================
       PRODUCT FILTERS
    ========================================================= */
    const searchInput =
        document.getElementById('inventorySearch');

    const categoryFilter =
        document.getElementById('inventoryCategoryFilter');

    const stockFilter =
        document.getElementById('inventoryStockFilter');

    const sortSelect =
        document.getElementById('inventorySort');

    const clearButton =
        document.getElementById('inventoryClearFilters');

    const emptyClearButton =
        document.getElementById('inventoryEmptyClear');

    const emptyMessage =
        document.getElementById('inventoryEmptyFilter');

    const visibleCount =
        document.getElementById('inventoryVisibleCount');

    const tableBody =
        document.getElementById('inventoryTableBody');

    const tabs =
        Array.from(
            document.querySelectorAll('[data-inventory-tab]')
        );

    const statButtons =
        Array.from(
            document.querySelectorAll('[data-stat-filter]')
        );

    let activeTab =
        'all';


    function getRows() {
        return Array.from(
            document.querySelectorAll(
                '#inventoryTable [data-row]'
            )
        );
    }


    function setInventoryTab(tab) {
        activeTab =
            tab || 'all';

        tabs.forEach(function (button) {
            const active =
                button.dataset.inventoryTab === activeTab;

            button.classList.toggle(
                'inventory-tab-active',
                active
            );

            button.classList.toggle(
                'text-navy/50',
                !active
            );
        });

        applyView();
    }


    function matchesTab(row) {
        if (activeTab === 'all') {
            return true;
        }

        if (activeTab === 'active') {
            return row.dataset.status === 'active';
        }

        if (activeTab === 'archived') {
            return row.dataset.status === 'archived';
        }

        if (activeTab === 'low') {
            return (
                row.dataset.status === 'active' &&
                row.dataset.stockState === 'low'
            );
        }

        if (activeTab === 'out') {
            return (
                row.dataset.status === 'active' &&
                row.dataset.stockState === 'out'
            );
        }

        return true;
    }


    function compareRows(a, b, sort) {
        const nameA =
            a.dataset.nameOriginal || '';

        const nameB =
            b.dataset.nameOriginal || '';

        const priceA =
            parseFloat(a.dataset.price || '0') || 0;

        const priceB =
            parseFloat(b.dataset.price || '0') || 0;

        const stockA =
            parseInt(a.dataset.stock || '0', 10) || 0;

        const stockB =
            parseInt(b.dataset.stock || '0', 10) || 0;

        const createdA =
            parseInt(a.dataset.created || '0', 10) || 0;

        const createdB =
            parseInt(b.dataset.created || '0', 10) || 0;


        switch (sort) {
            case 'name-asc':
                return nameA.localeCompare(nameB);

            case 'name-desc':
                return nameB.localeCompare(nameA);

            case 'price-asc':
                return priceA - priceB;

            case 'price-desc':
                return priceB - priceA;

            case 'stock-asc':
                return stockA - stockB;

            case 'stock-desc':
                return stockB - stockA;

            default:
                return createdB - createdA;
        }
    }


    function applyView() {
        const query =
            (searchInput?.value || '')
                .trim()
                .toLowerCase();

        const category =
            categoryFilter?.value || '';

        const stockState =
            stockFilter?.value || '';

        const sort =
            sortSelect?.value || 'newest';

        const rows =
            getRows();

        let count = 0;


        rows.forEach(function (row) {
            const matchesSearch =
                query === '' ||
                (row.dataset.name || '').includes(query);

            const matchesCategory =
                category === '' ||
                row.dataset.category === category;

            const matchesStock =
                stockState === '' ||
                (
                    row.dataset.status === 'active' &&
                    row.dataset.stockState === stockState
                );

            const visible =
                matchesSearch &&
                matchesCategory &&
                matchesStock &&
                matchesTab(row);

            row.hidden =
                !visible;

            if (visible) {
                count++;
            }
        });


        rows
            .sort(function (a, b) {
                return compareRows(
                    a,
                    b,
                    sort
                );
            })
            .forEach(function (row) {
                tableBody?.appendChild(row);
            });


        if (visibleCount) {
            visibleCount.textContent =
                count;
        }

        if (emptyMessage) {
            emptyMessage.hidden =
                count > 0;
        }
    }


    function clearFilters() {
        if (searchInput) {
            searchInput.value = '';
        }

        if (categoryFilter) {
            categoryFilter.value = '';
        }

        if (stockFilter) {
            stockFilter.value = '';
        }

        if (sortSelect) {
            sortSelect.value = 'newest';
        }

        setInventoryTab('all');
    }


    tabs.forEach(function (button) {
        button.addEventListener(
            'click',
            function () {
                setInventoryTab(
                    button.dataset.inventoryTab
                );
            }
        );
    });


    statButtons.forEach(function (button) {
        button.addEventListener(
            'click',
            function () {
                setInventoryTab(
                    button.dataset.statFilter
                );
            }
        );
    });


    searchInput?.addEventListener(
        'input',
        applyView
    );

    categoryFilter?.addEventListener(
        'change',
        applyView
    );

    stockFilter?.addEventListener(
        'change',
        applyView
    );

    sortSelect?.addEventListener(
        'change',
        applyView
    );

    clearButton?.addEventListener(
        'click',
        clearFilters
    );

    emptyClearButton?.addEventListener(
        'click',
        clearFilters
    );


    /* =========================================================
       VOUCHER MODAL
    ========================================================= */
    const voucherModal =
        document.getElementById('voucherModal');

    const voucherForm =
        document.getElementById('voucherForm');

    const voucherModalTitle =
        document.getElementById('voucherModalTitle');

    const voucherId =
        document.getElementById('voucherId');

    const voucherName =
        document.getElementById('voucherName');

    const voucherCode =
        document.getElementById('voucherCode');

    const voucherType =
        document.getElementById('voucherType');

    const voucherValue =
        document.getElementById('voucherValue');

    const voucherMinOrder =
        document.getElementById('voucherMinOrder');

    const voucherUsageLimit =
        document.getElementById('voucherUsageLimit');

    const voucherStartsAt =
        document.getElementById('voucherStartsAt');

    const voucherEndsAt =
        document.getElementById('voucherEndsAt');

    const voucherProductInputs =
        Array.from(
            document.querySelectorAll('[data-voucher-product]')
        );


    function openVoucherModal(voucher = null) {
        voucherForm.reset();

        voucherId.value = '';

        voucherProductInputs.forEach(function (input) {
            input.checked = false;
        });


        if (voucher) {
            voucherModalTitle.textContent =
                'Edit Voucher';

            voucherId.value =
                voucher.id ?? '';

            voucherName.value =
                voucher.name ?? '';

            voucherCode.value =
                voucher.code ?? '';

            voucherType.value =
                voucher.type ?? 'percent';

            voucherValue.value =
                voucher.value ?? '';

            voucherMinOrder.value =
                voucher.min_order_amount ?? 0;

            voucherUsageLimit.value =
                voucher.usage_limit ?? '';

            voucherStartsAt.value =
                voucher.starts_at ?? '';

            voucherEndsAt.value =
                voucher.ends_at ?? '';

            const selected =
                new Set(
                    (voucher.product_ids || [])
                        .map(Number)
                );

            voucherProductInputs.forEach(function (input) {
                input.checked =
                    selected.has(
                        Number(input.value)
                    );
            });
        } else {
            voucherModalTitle.textContent =
                'Create Voucher';

            voucherType.value =
                'percent';

            voucherMinOrder.value =
                0;
        }


        voucherModal.hidden =
            false;

        document.body.style.overflow =
            'hidden';
    }


    function closeVoucherModal() {
        voucherModal.hidden =
            true;

        document.body.style.overflow =
            '';
    }


    document
        .querySelectorAll('[data-open-voucher-modal]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    openVoucherModal(
                        parseJson(button.dataset.voucher)
                    );
                }
            );
        });


    document
        .querySelectorAll('[data-close-voucher-modal]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                closeVoucherModal
            );
        });


    document
        .getElementById('voucherModalBackdrop')
        ?.addEventListener(
            'click',
            closeVoucherModal
        );


    /* =========================================================
       FRONTEND-ONLY DEMO SUBMITS
    ========================================================= */
    document
        .querySelectorAll('[data-demo-form]')
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const action = form.dataset.demoAction || 'action';

                if (action === 'stock') {
                    const save = form.querySelector('[data-stock-save]');
                    if (save) save.hidden = true;
                }

                if (action === 'product') {
                    closeProductModal();
                }

                if (action === 'voucher') {
                    closeVoucherModal();
                }

                if (action === 'archive') {
                    closeArchiveModal();
                }

                window.alert(
                    'Demo only muna — frontend is hardcoded. The backend team can connect this action later.'
                );
            });
        });


    /* =========================================================
       ESCAPE
    ========================================================= */
    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (!productModal.hidden) {
                closeProductModal();
                return;
            }

            if (!voucherModal.hidden) {
                closeVoucherModal();
                return;
            }

            if (!archiveModal.hidden) {
                closeArchiveModal();
            }
        }
    );


    updateSellingPrice();
    applyView();
    refreshVariantEmpty();

});
</script>

@endpush

@endsection
