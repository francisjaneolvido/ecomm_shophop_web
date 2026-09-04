@extends('seller.partials.layout')

@section('title', 'Manage Inventory')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | BACKEND-SAFE VIEW DEFAULTS
    |--------------------------------------------------------------------------
    | Same convention as seller.dashboard — controller can pass real
    | data later without breaking this view.
    */
    $products = collect($products ?? [
        [
            'id' => 1,
            'name' => 'Handwoven Rattan Basket',
            'category' => 'Home & Living',
            'price' => 450,
            'discount' => 10,
            'stock' => 32,
            'sold' => 58,
            'status' => 'active',
            'image' => null,
        ],
        [
            'id' => 2,
            'name' => 'Barako Coffee Beans 250g',
            'category' => 'Food & Beverage',
            'price' => 220,
            'discount' => 0,
            'stock' => 6,
            'sold' => 140,
            'status' => 'active',
            'image' => null,
        ],
        [
            'id' => 3,
            'name' => 'Capiz Shell Wall Lamp',
            'category' => 'Home & Living',
            'price' => 890,
            'discount' => 15,
            'stock' => 0,
            'sold' => 12,
            'status' => 'active',
            'image' => null,
        ],
        [
            'id' => 4,
            'name' => 'Handmade Soap Bar Set',
            'category' => 'Beauty & Wellness',
            'price' => 175,
            'discount' => 0,
            'stock' => 84,
            'sold' => 96,
            'status' => 'archived',
            'image' => null,
        ],
    ]);

    $categories = collect($categories ?? [
        'Home & Living',
        'Food & Beverage',
        'Beauty & Wellness',
        'Fashion & Apparel',
    ]);

    $lowStockThreshold = $lowStockThreshold ?? 10;

    $totalProducts = $products->where('status', 'active')->count();
    $lowStockCount = $products->where('status', 'active')
        ->filter(fn ($p) => ($p['stock'] ?? 0) > 0 && ($p['stock'] ?? 0) <= $lowStockThreshold)
        ->count();
    $outOfStockCount = $products->where('status', 'active')
        ->filter(fn ($p) => ($p['stock'] ?? 0) === 0)
        ->count();
    $totalValue = $products->where('status', 'active')
        ->sum(fn ($p) => ($p['price'] ?? 0) * ($p['stock'] ?? 0));

    $stockBadge = function ($product) use ($lowStockThreshold) {
        $stock = $product['stock'] ?? 0;

        if ($stock === 0) {
            return ['label' => 'Out of Stock', 'classes' => 'bg-red-50 text-red-600'];
        }

        if ($stock <= $lowStockThreshold) {
            return ['label' => 'Low Stock', 'classes' => 'bg-coral/10 text-coral'];
        }

        return ['label' => 'In Stock', 'classes' => 'bg-teal/10 text-teal-dark'];
    };
@endphp


<style>
    #sellerInventory .dash-gap {
        gap: 1rem;
    }

    #sellerInventory .dash-section {
        margin-bottom: 1.25rem;
    }

    #productModal[hidden],
    #archiveModal[hidden] {
        display: none !important;
    }
</style>


<div id="sellerInventory">

    {{-- =========================================================
        HEADER
    ========================================================= --}}
    <header class="dash-section flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
                Manage Inventory
            </h1>
            <p class="text-xs sm:text-sm text-navy/45 mt-1 max-w-2xl">
                Add products, update pricing, and keep your stock levels accurate.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                data-open-product-modal
                class="inline-flex items-center justify-center gap-1.5
                       h-9 px-3.5 rounded-lg
                       bg-navy hover:bg-navy/90
                       text-xs font-semibold text-white
                       transition-colors"
            >
                <x-lucide-plus class="w-3.5 h-3.5" />
                Add Product
            </button>
        </div>

    </header>


    {{-- =========================================================
        STATS
    ========================================================= --}}
    <section class="dash-section grid grid-cols-2 xl:grid-cols-4 dash-gap">

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">
                Active Products
            </p>
            <p class="text-lg font-bold text-navy mt-1">
                {{ $totalProducts }}
            </p>
        </div>

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">
                Low Stock
            </p>
            <p class="text-lg font-bold text-coral mt-1">
                {{ $lowStockCount }}
            </p>
        </div>

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">
                Out of Stock
            </p>
            <p class="text-lg font-bold text-red-500 mt-1">
                {{ $outOfStockCount }}
            </p>
        </div>

        <div class="bg-white border border-gray-border rounded-xl p-4">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-navy/35">
                Inventory Value
            </p>
            <p class="text-lg font-bold text-navy mt-1">
                ₱{{ number_format($totalValue) }}
            </p>
        </div>

    </section>


    {{-- =========================================================
        FILTERS
    ========================================================= --}}
    <section class="dash-section bg-white border border-gray-border rounded-xl p-3 flex flex-col sm:flex-row sm:items-center gap-2">

        <div class="relative flex-1">
            <x-lucide-search class="w-3.5 h-3.5 text-navy/30 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
                type="text"
                id="inventorySearch"
                placeholder="Search products..."
                class="w-full h-9 pl-9 pr-3 rounded-lg border border-gray-border
                       text-xs text-navy placeholder:text-navy/30
                       focus:outline-none focus:border-teal/50"
            >
        </div>

        <select
            id="inventoryCategoryFilter"
            class="h-9 px-3 rounded-lg border border-gray-border
                   text-xs text-navy bg-white
                   focus:outline-none focus:border-teal/50"
        >
            <option value="">All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
            @endforeach
        </select>

        <select
            id="inventoryStatusFilter"
            class="h-9 px-3 rounded-lg border border-gray-border
                   text-xs text-navy bg-white
                   focus:outline-none focus:border-teal/50"
        >
            <option value="">All Stock Status</option>
            <option value="in">In Stock</option>
            <option value="low">Low Stock</option>
            <option value="out">Out of Stock</option>
        </select>

    </section>


    {{-- =========================================================
        PRODUCTS TABLE
    ========================================================= --}}
    <section class="bg-white border border-gray-border rounded-xl overflow-hidden">

        @if ($products->isEmpty())

            <div class="py-14 text-center">
                <div
                    class="w-10 h-10 mx-auto
                           rounded-lg bg-gray-bg text-navy/30
                           flex items-center justify-center"
                >
                    <x-lucide-package class="w-4.5 h-4.5" />
                </div>
                <p class="text-xs font-semibold text-navy/50 mt-3">
                    No products yet
                </p>
                <p class="text-[10px] text-navy/35 mt-1">
                    Add your first product to start selling.
                </p>
            </div>

        @else

            <div class="overflow-x-auto">
                <table class="w-full text-left" id="inventoryTable">
                    <thead>
                        <tr class="border-b border-gray-border">
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3">Product</th>
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3">Category</th>
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3">Price</th>
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3">Stock</th>
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3">Status</th>
                            <th class="text-[9px] font-semibold uppercase tracking-wide text-navy/35 px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php $badge = $stockBadge($product); @endphp

                            <tr
                                class="border-b border-gray-border last:border-0 hover:bg-gray-bg/50 transition-colors"
                                data-row
                                data-name="{{ strtolower($product['name'] ?? '') }}"
                                data-category="{{ $product['category'] ?? '' }}"
                                data-stock-state="{{ ($product['stock'] ?? 0) === 0 ? 'out' : (($product['stock'] ?? 0) <= $lowStockThreshold ? 'low' : 'in') }}"
                                data-status="{{ $product['status'] ?? 'active' }}"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gray-bg flex items-center justify-center shrink-0">
                                            <x-lucide-image class="w-3.5 h-3.5 text-navy/25" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-semibold text-navy truncate">
                                                {{ $product['name'] ?? 'Product' }}
                                            </p>
                                            @if (($product['discount'] ?? 0) > 0)
                                                <p class="text-[9px] text-teal-dark mt-0.5">
                                                    {{ $product['discount'] }}% off
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-[10px] text-navy/60">
                                        {{ $product['category'] ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-[11px] font-semibold text-navy tabular-nums">
                                        ₱{{ number_format($product['price'] ?? 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-[11px] text-navy/70 tabular-nums">
                                        {{ $product['stock'] ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if (($product['status'] ?? 'active') === 'archived')
                                        <span class="inline-flex text-[9px] font-semibold px-2 py-1 rounded-full bg-navy/10 text-navy/50">
                                            Archived
                                        </span>
                                    @else
                                        <span class="inline-flex text-[9px] font-semibold px-2 py-1 rounded-full {{ $badge['classes'] }}">
                                            {{ $badge['label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            type="button"
                                            data-open-product-modal
                                            data-product='{{ json_encode($product) }}'
                                            class="w-7 h-7 rounded-lg flex items-center justify-center
                                                   text-navy/40 hover:text-teal-dark hover:bg-teal-light
                                                   transition-colors"
                                            title="Edit product"
                                        >
                                            <x-lucide-pencil class="w-3.5 h-3.5" />
                                        </button>

                                        <button
                                            type="button"
                                            data-open-archive-modal
                                            data-product-name="{{ $product['name'] ?? 'this product' }}"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center
                                                   text-navy/40 hover:text-coral hover:bg-coral/10
                                                   transition-colors"
                                            title="{{ ($product['status'] ?? 'active') === 'archived' ? 'Restore product' : 'Archive product' }}"
                                        >
                                            @if (($product['status'] ?? 'active') === 'archived')
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

            <p
                id="inventoryEmptyFilter"
                hidden
                class="py-10 text-center text-[10px] text-navy/35"
            >
                No products match your filters.
            </p>

        @endif

    </section>

</div>


{{-- =========================================================
    ADD / EDIT PRODUCT MODAL
========================================================= --}}
<div
    id="productModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div id="productModalBackdrop" class="absolute inset-0 bg-navy/40"></div>

    <div class="relative bg-white rounded-xl shadow-panel w-full max-w-md max-h-[90vh] overflow-y-auto content-scrollbar">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-border">
            <p id="productModalTitle" class="text-sm font-bold text-navy">
                Add Product
            </p>
            <button
                type="button"
                data-close-product-modal
                class="w-7 h-7 rounded-lg flex items-center justify-center text-navy/40 hover:bg-gray-bg transition-colors"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <form id="productForm" class="px-5 py-4 space-y-3" method="POST" action="{{ route('seller.inventory') }}">
            @csrf

            <input type="hidden" name="product_id" id="productId">

            <div>
                <label class="text-[10px] font-semibold text-navy/60">Product Name</label>
                <input
                    type="text" name="name" id="productName" required
                    class="w-full h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs
                           focus:outline-none focus:border-teal/50"
                >
            </div>

            <div>
                <label class="text-[10px] font-semibold text-navy/60">Category</label>
                <select
                    name="category" id="productCategory" required
                    class="w-full h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs bg-white
                           focus:outline-none focus:border-teal/50"
                >
                    @foreach ($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] font-semibold text-navy/60">Price (₱)</label>
                    <input
                        type="number" step="0.01" min="0" name="price" id="productPrice" required
                        class="w-full h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs
                               focus:outline-none focus:border-teal/50"
                    >
                </div>
                <div>
                    <label class="text-[10px] font-semibold text-navy/60">Discount (%)</label>
                    <input
                        type="number" step="1" min="0" max="100" name="discount" id="productDiscount"
                        class="w-full h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs
                               focus:outline-none focus:border-teal/50"
                    >
                </div>
            </div>

            <div>
                <label class="text-[10px] font-semibold text-navy/60">Stock Quantity</label>
                <input
                    type="number" min="0" name="stock" id="productStock" required
                    class="w-full h-9 mt-1 px-3 rounded-lg border border-gray-border text-xs
                           focus:outline-none focus:border-teal/50"
                >
            </div>

            <div>
                <label class="text-[10px] font-semibold text-navy/60">Description</label>
                <textarea
                    name="description" id="productDescription" rows="3"
                    class="w-full mt-1 px-3 py-2 rounded-lg border border-gray-border text-xs
                           focus:outline-none focus:border-teal/50"
                ></textarea>
            </div>

            <div>
                <label class="text-[10px] font-semibold text-navy/60">Product Image</label>
                <input
                    type="file" name="image" accept="image/*"
                    class="w-full mt-1 text-[10px] text-navy/50
                           file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                           file:text-[10px] file:font-semibold
                           file:bg-teal-light file:text-teal-dark"
                >
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button
                    type="button"
                    data-close-product-modal
                    class="h-9 px-3.5 rounded-lg border border-gray-border text-xs font-semibold text-navy/60 hover:bg-gray-bg transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="h-9 px-3.5 rounded-lg bg-navy hover:bg-navy/90 text-xs font-semibold text-white transition-colors"
                >
                    Save Product
                </button>
            </div>

        </form>

    </div>
</div>


{{-- =========================================================
    ARCHIVE CONFIRM MODAL
========================================================= --}}
<div
    id="archiveModal"
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div id="archiveModalBackdrop" class="absolute inset-0 bg-navy/40"></div>

    <div class="relative bg-white rounded-xl shadow-panel w-full max-w-sm p-5">

        <div class="w-9 h-9 rounded-lg bg-coral/10 text-coral flex items-center justify-center">
            <x-lucide-archive class="w-4 h-4" />
        </div>

        <p class="text-sm font-bold text-navy mt-3">
            Archive <span id="archiveProductName">this product</span>?
        </p>
        <p class="text-[10px] text-navy/45 mt-1">
            Archived products won't be visible to buyers. You can restore them anytime from this page.
        </p>

        <div class="flex items-center justify-end gap-2 mt-4">
            <button
                type="button"
                data-close-archive-modal
                class="h-9 px-3.5 rounded-lg border border-gray-border text-xs font-semibold text-navy/60 hover:bg-gray-bg transition-colors"
            >
                Cancel
            </button>
            <form id="archiveForm" method="POST" action="{{ route('seller.inventory') }}">
                @csrf
                <button
                    type="submit"
                    class="h-9 px-3.5 rounded-lg bg-coral hover:bg-coral/90 text-xs font-semibold text-white transition-colors"
                >
                    Archive
                </button>
            </form>
        </div>

    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       PRODUCT MODAL
    ===================================================== */
    const productModal = document.getElementById('productModal');
    const productForm = document.getElementById('productForm');
    const productModalTitle = document.getElementById('productModalTitle');

    function openProductModal(product) {
        productForm.reset();
        document.getElementById('productId').value = '';

        if (product) {
            productModalTitle.textContent = 'Edit Product';
            document.getElementById('productId').value = product.id ?? '';
            document.getElementById('productName').value = product.name ?? '';
            document.getElementById('productCategory').value = product.category ?? '';
            document.getElementById('productPrice').value = product.price ?? '';
            document.getElementById('productDiscount').value = product.discount ?? 0;
            document.getElementById('productStock').value = product.stock ?? '';
            document.getElementById('productDescription').value = product.description ?? '';
        } else {
            productModalTitle.textContent = 'Add Product';
        }

        productModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeProductModal() {
        productModal.hidden = true;
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-open-product-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            const raw = button.getAttribute('data-product');
            openProductModal(raw ? JSON.parse(raw) : null);
        });
    });

    document.querySelectorAll('[data-close-product-modal]').forEach(function (button) {
        button.addEventListener('click', closeProductModal);
    });

    document.getElementById('productModalBackdrop')?.addEventListener('click', closeProductModal);


    /* =====================================================
       ARCHIVE MODAL
    ===================================================== */
    const archiveModal = document.getElementById('archiveModal');
    const archiveProductName = document.getElementById('archiveProductName');

    function openArchiveModal(name) {
        archiveProductName.textContent = name || 'this product';
        archiveModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeArchiveModal() {
        archiveModal.hidden = true;
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-open-archive-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            openArchiveModal(button.getAttribute('data-product-name'));
        });
    });

    document.querySelectorAll('[data-close-archive-modal]').forEach(function (button) {
        button.addEventListener('click', closeArchiveModal);
    });

    document.getElementById('archiveModalBackdrop')?.addEventListener('click', closeArchiveModal);


    /* =====================================================
       ESCAPE KEY
    ===================================================== */
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        if (!productModal.hidden) closeProductModal();
        if (!archiveModal.hidden) closeArchiveModal();
    });


    /* =====================================================
       SEARCH + FILTERS
    ===================================================== */
    const searchInput = document.getElementById('inventorySearch');
    const categoryFilter = document.getElementById('inventoryCategoryFilter');
    const statusFilter = document.getElementById('inventoryStatusFilter');
    const rows = Array.from(document.querySelectorAll('#inventoryTable [data-row]'));
    const emptyFilterMessage = document.getElementById('inventoryEmptyFilter');

    function applyFilters() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        const category = categoryFilter?.value || '';
        const stockState = statusFilter?.value || '';

        let visibleCount = 0;

        rows.forEach(function (row) {
            const matchesQuery = query === '' || row.dataset.name.includes(query);
            const matchesCategory = category === '' || row.dataset.category === category;
            const matchesStock = stockState === '' || row.dataset.stockState === stockState;

            const visible = matchesQuery && matchesCategory && matchesStock;
            row.hidden = !visible;

            if (visible) visibleCount++;
        });

        if (emptyFilterMessage) {
            emptyFilterMessage.hidden = visibleCount > 0;
        }
    }

    searchInput?.addEventListener('input', applyFilters);
    categoryFilter?.addEventListener('change', applyFilters);
    statusFilter?.addEventListener('change', applyFilters);

});
</script>
@endpush

@endsection