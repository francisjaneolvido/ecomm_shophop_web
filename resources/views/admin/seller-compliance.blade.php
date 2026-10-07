@extends('admin.layout')

@section('title', 'Seller Compliance')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Seller Compliance</h1>
        <p class="text-sm text-slate-500 mt-1">Review and approve products before they go live.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 flex items-start gap-3 bg-teal-light border border-teal/20 rounded-xl px-4 py-3">
            <x-lucide-circle-check class="w-4 h-4 text-teal-dark mt-0.5 shrink-0" />
            <p class="text-xs font-medium text-teal-dark">{{ session('status') }}</p>
        </div>
    @endif

    {{-- ============ STAT SUMMARY ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-yellow/20 flex items-center justify-center shrink-0">
                    <x-lucide-clock class="w-5 h-5 text-yellow-600" />
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['pending_review'] }}</p>
                    <p class="text-xs text-slate-500">Pending Review</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                    <x-lucide-badge-check class="w-5 h-5 text-mint-dark" />
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['approved'] }}</p>
                    <p class="text-xs text-slate-500">Approved</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                    <x-lucide-x-circle class="w-5 h-5 text-coral" />
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ $counts['rejected'] }}</p>
                    <p class="text-xs text-slate-500">Rejected</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ============ FILTER TABS + SORT + SEARCH ============ --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'pending_review', 'sort' => $sort, 'page' => null]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'pending_review' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Pending Review <span class="ml-1 opacity-70">({{ $counts['pending_review'] }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'approved', 'sort' => $sort, 'page' => null]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'approved' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Approved <span class="ml-1 opacity-70">({{ $counts['approved'] }})</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'rejected', 'sort' => $sort, 'page' => null]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'rejected' ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Rejected <span class="ml-1 opacity-70">({{ $counts['rejected'] }})</span>
            </a>
        </div>

        <div class="flex items-center gap-2 w-full xl:w-auto">

            <form method="GET" action="{{ route('admin.compliance') }}" class="shrink-0">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <select name="sort" onchange="this.form.submit()"
                    class="text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white text-slate-600 font-medium focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>Product name (A–Z)</option>
                    <option value="za" {{ $sort === 'za' ? 'selected' : '' }}>Product name (Z–A)</option>
                </select>
            </form>

            <form method="GET" action="{{ route('admin.compliance') }}" class="relative w-full xl:w-72">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />

                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Search by product, SKU, or seller..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl bg-white border border-slate-200 text-navy placeholder:text-slate-400 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
            </form>

        </div>

    </div>

    {{-- ============ PRODUCT LIST ============ --}}
    @php $modalData = []; @endphp

    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">
                {{ $filter === 'approved' ? 'Approved Products' : ($filter === 'rejected' ? 'Rejected Products' : 'Products Awaiting Review') }}
            </h2>
            <span class="text-xs text-slate-400">{{ $products->total() }} total</span>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($products as $product)

                @php
                    $statusStyles = [
                        'pending_review' => ['badge' => 'text-yellow-700 bg-yellow/20', 'label' => 'Pending Review'],
                        'approved'       => ['badge' => 'text-mint-dark bg-mint/15', 'label' => 'Approved'],
                        'rejected'       => ['badge' => 'text-coral bg-coral/10', 'label' => 'Rejected'],
                    ];
                    $style = $statusStyles[$product->compliance_status] ?? $statusStyles['pending_review'];

                    $thumbnail = $product->images->first();
                    $categoryMismatch = $product->seller
                        && $product->seller->category
                        && strcasecmp($product->seller->category, $product->category) !== 0;

                    // Data para sa modal (hindi na kailangan ng fetch/JSON endpoint)
                    $modalData[$product->id] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'seller' => [
                            'business_name' => $product->seller->business_name ?? null,
                            'category' => $product->seller->category ?? null,
                        ],
                        'category' => $product->category,
                        'price' => $product->price,
                        'stock' => $product->stock,
                        'sku' => $product->sku,
                        'description' => $product->description,
                        'compliance_status' => $product->compliance_status,
                        'submitted_at' => $product->submitted_at ? \Illuminate\Support\Carbon::parse($product->submitted_at)->toIso8601String() : null,
                        'reviewed_at' => $product->reviewed_at ? \Illuminate\Support\Carbon::parse($product->reviewed_at)->toIso8601String() : null,
                        'rejection_reason' => $product->rejection_reason,
                        'rejection_notes' => $product->rejection_notes,
                        'images' => $product->images->map(fn ($img) => ['image_path' => $img->image_path])->values(),
                    ];
                @endphp

                <div class="flex items-center justify-between gap-4 px-5 py-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-14 h-14 rounded-xl bg-gray-bg overflow-hidden shrink-0 border border-slate-200">
                            <img
                                src="{{ $thumbnail ? asset('storage/' . $thumbnail->image_path) : asset('images/products/placeholder.png') }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-navy truncate">{{ $product->name }}</p>
                            <p class="text-xs text-slate-500 truncate">
                                {{ $product->seller->business_name ?? 'Unknown Seller' }} · {{ $product->category }}
                            </p>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $style['badge'] }}">
                                    {{ $style['label'] }}
                                </span>
                                @if ($categoryMismatch)
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full text-coral bg-coral/10">
                                        Category Mismatch
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" class="product-view-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50" data-product-id="{{ $product->id }}">
                            View Details
                        </button>
                        @if ($product->compliance_status !== 'approved')
                            <button type="button" class="product-approve-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                                Approve
                            </button>
                        @endif
                        @if ($product->compliance_status !== 'rejected')
                            <button type="button" class="product-reject-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                                Reject
                            </button>
                        @endif
                    </div>
                </div>

            @empty

                <div class="px-5 py-14 text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                        <x-lucide-package-search class="w-5 h-5" />
                    </div>
                    <p class="text-sm font-semibold text-navy mt-3">No products found</p>
                    <p class="text-xs text-slate-400 mt-1">Try a different filter tab.</p>
                </div>

            @endforelse

        </div>

        @if ($products->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $products->withQueryString()->links() }}
            </div>
        @endif

    </div>


    {{-- =========================================================
        PRODUCT DETAILS MODAL
    ========================================================= --}}
    <div id="productModalOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <div id="productModalPanel" class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150">

            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            <button type="button" id="productModalClose" aria-label="Close"
                class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-slate-100 text-navy/45 flex items-center justify-center hover:bg-mint/10 hover:text-mint-dark focus:outline-none focus:ring-4 focus:ring-mint/15 transition">
                <x-lucide-x class="w-4 h-4" />
            </button>

            <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">
                <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">SELLER COMPLIANCE</p>
                <h2 id="modalProductName" class="text-xl sm:text-2xl font-bold text-navy truncate"></h2>
                <p id="modalProductSeller" class="text-xs text-slate-500 mt-1"></p>

                <div class="flex items-center gap-2 flex-wrap mt-3">
                    <span id="modalStatusBadge" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full"></span>
                    <span id="modalMismatchBadge" class="hidden inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full text-coral bg-coral/10">
                        Category Mismatch
                    </span>
                </div>
            </div>

            <div class="px-6 py-5 grid grid-cols-1 lg:grid-cols-2 gap-4">

                <div class="space-y-4">

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Product Details</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Category (Product)</dt>
                                <dd id="modalProductCategory" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Category (Seller registered)</dt>
                                <dd id="modalSellerCategory" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Price</dt>
                                <dd id="modalPrice" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Stock</dt>
                                <dd id="modalStock" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">SKU</dt>
                                <dd id="modalSku" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Timeline</p>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Submitted</dt>
                                <dd id="modalSubmittedAt" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-xs text-slate-400 shrink-0">Last Reviewed</dt>
                                <dd id="modalReviewedAt" class="text-xs text-slate-700 text-right"></dd>
                            </div>
                        </dl>
                    </div>

                    <div id="modalRejectionWrap" class="hidden bg-coral/5 border border-coral/15 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-coral mb-2">Rejection Reason</p>
                        <p id="modalRejectionReason" class="text-xs text-slate-700 font-semibold"></p>
                        <p id="modalRejectionNotes" class="text-xs text-slate-600 leading-relaxed mt-1.5"></p>
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Description</p>
                        <p id="modalDescription" class="text-xs text-slate-600 leading-relaxed"></p>
                    </div>

                </div>

                <div class="space-y-4">
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Product Images</p>
                        <div id="modalImages" class="grid grid-cols-3 gap-3"></div>
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" id="modalRejectBtn" class="h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 transition-all duration-300">
                    Reject
                </button>
                <button type="button" id="modalApproveBtn" class="h-9 inline-flex items-center gap-1.5 px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300">
                    Approve
                </button>
            </div>

        </div>

    </div>


    {{-- =========================================================
        APPROVE / REJECT — CONFIRMATION MODAL
    ========================================================= --}}
    <div id="confirmModalOverlay" class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <div id="confirmModalPanel" class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150 p-6">

            <div id="confirmIconWrap" class="w-11 h-11 rounded-xl flex items-center justify-center mb-4"></div>

            <h3 id="confirmTitle" class="text-base font-bold text-navy mb-1.5"></h3>
            <p id="confirmMessage" class="text-sm text-slate-500 leading-relaxed mb-4"></p>

            <form id="confirmRejectForm" method="POST" action="">
                @csrf
                @method('PATCH')

                <div id="confirmRejectFields" class="hidden mb-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Reason for rejection</label>
                        <select name="rejection_reason" id="confirmRejectReason" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-coral/40">
                            <option value="wrong_category">Wrong Category</option>
                            <option value="prohibited_product">Prohibited Product</option>
                            <option value="inappropriate_content">Inappropriate Content</option>
                            <option value="misleading_info">Misleading Product Information</option>
                            <option value="poor_image_quality">Poor Image Quality</option>
                            <option value="incomplete_details">Incomplete Details</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Notes</label>
                        <textarea name="rejection_notes" id="confirmRejectNotes" rows="3" maxlength="2000" placeholder="Enter details for the seller..."
                            class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-coral/40"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" id="confirmCancelBtn" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit" id="confirmProceedBtn" class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white transition-all duration-300"></button>
                </div>
            </form>

        </div>

    </div>


    {{-- =========================================================
        MODAL DATA + BEHAVIOR
    ========================================================= --}}
    <script>
        const PRODUCTS = @json($modalData);
        const APPROVE_URL = @json(route('admin.compliance.approve', ['product' => '__ID__']));
        const REJECT_URL  = @json(route('admin.compliance.reject',  ['product' => '__ID__']));

        const statusBadge = {
            pending_review: { text: 'text-yellow-700', bg: 'bg-yellow/20', label: 'Pending Review' },
            approved:       { text: 'text-mint-dark',  bg: 'bg-mint/15',   label: 'Approved' },
            rejected:       { text: 'text-coral',      bg: 'bg-coral/10',  label: 'Rejected' },
        };

        const rejectionReasonLabels = {
            wrong_category: 'Wrong Category',
            prohibited_product: 'Prohibited Product',
            inappropriate_content: 'Inappropriate Content',
            misleading_info: 'Misleading Product Information',
            poor_image_quality: 'Poor Image Quality',
            incomplete_details: 'Incomplete Details',
            other: 'Other',
        };

        const overlay  = document.getElementById('productModalOverlay');
        const panel    = document.getElementById('productModalPanel');
        const closeBtn = document.getElementById('productModalClose');

        let currentProduct = null;

        function openProductModal(id) {
            const product = PRODUCTS[id];
            if (!product) return;

            currentProduct = product;
            populateProductModal(product);

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            requestAnimationFrame(() => panel.classList.remove('translate-y-2', 'opacity-0'));
        }

        function populateProductModal(product) {
            document.getElementById('modalProductName').textContent = product.name;
            document.getElementById('modalProductSeller').textContent = product.seller?.business_name ?? 'Unknown Seller';

            const sBadge = statusBadge[product.compliance_status] || statusBadge.pending_review;
            const badgeEl = document.getElementById('modalStatusBadge');
            badgeEl.className = 'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ' + sBadge.bg + ' ' + sBadge.text;
            badgeEl.textContent = sBadge.label;

            const mismatch = product.seller?.category
                && product.seller.category.toLowerCase() !== (product.category || '').toLowerCase();
            document.getElementById('modalMismatchBadge').classList.toggle('hidden', !mismatch);

            document.getElementById('modalProductCategory').textContent = product.category ?? '—';
            document.getElementById('modalSellerCategory').textContent = product.seller?.category ?? '—';
            document.getElementById('modalPrice').textContent = '₱' + Number(product.price).toLocaleString();
            document.getElementById('modalStock').textContent = product.stock + ' units';
            document.getElementById('modalSku').textContent = product.sku ?? '—';
            document.getElementById('modalDescription').textContent = product.description ?? 'No description provided.';

            document.getElementById('modalSubmittedAt').textContent = product.submitted_at
                ? new Date(product.submitted_at).toLocaleString() : '—';
            document.getElementById('modalReviewedAt').textContent = product.reviewed_at
                ? new Date(product.reviewed_at).toLocaleString() : 'Not yet reviewed';

            const rejectionWrap = document.getElementById('modalRejectionWrap');
            if (product.compliance_status === 'rejected') {
                rejectionWrap.classList.remove('hidden');
                document.getElementById('modalRejectionReason').textContent =
                    rejectionReasonLabels[product.rejection_reason] || product.rejection_reason || '—';
                document.getElementById('modalRejectionNotes').textContent = product.rejection_notes ?? '';
            } else {
                rejectionWrap.classList.add('hidden');
            }

            const imagesGrid = document.getElementById('modalImages');
            imagesGrid.innerHTML = '';
            (product.images || []).forEach(img => {
                const div = document.createElement('div');
                div.className = 'aspect-square rounded-lg overflow-hidden border border-slate-200 bg-slate-100';
                div.innerHTML = `<img src="/storage/${img.image_path}" class="w-full h-full object-cover">`;
                imagesGrid.appendChild(div);
            });
            if ((product.images || []).length === 0) {
                imagesGrid.innerHTML = '<p class="text-xs text-slate-400 col-span-3">No images uploaded.</p>';
            }

            document.getElementById('modalApproveBtn').classList.toggle('hidden', product.compliance_status === 'approved');
            document.getElementById('modalRejectBtn').classList.toggle('hidden', product.compliance_status === 'rejected');
        }

        function closeProductModal() {
            panel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.product-view-btn').forEach(btn => {
            btn.addEventListener('click', () => openProductModal(btn.dataset.productId));
        });

        closeBtn.addEventListener('click', closeProductModal);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) closeProductModal(); });

        document.getElementById('modalApproveBtn').addEventListener('click', () => {
            if (currentProduct) openConfirmModal('approve', currentProduct.id, currentProduct.name);
        });
        document.getElementById('modalRejectBtn').addEventListener('click', () => {
            if (currentProduct) openConfirmModal('reject', currentProduct.id, currentProduct.name);
        });

        /* -----------------------------------------------------------
           APPROVE / REJECT — CONFIRMATION MODAL
        ----------------------------------------------------------- */
        const confirmOverlay      = document.getElementById('confirmModalOverlay');
        const confirmPanel        = document.getElementById('confirmModalPanel');
        const confirmIconWrap     = document.getElementById('confirmIconWrap');
        const confirmTitle        = document.getElementById('confirmTitle');
        const confirmMessage      = document.getElementById('confirmMessage');
        const confirmProceedBtn   = document.getElementById('confirmProceedBtn');
        const confirmCancelBtn    = document.getElementById('confirmCancelBtn');
        const confirmRejectFields = document.getElementById('confirmRejectFields');
        const confirmRejectForm   = document.getElementById('confirmRejectForm');

        const checkIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        const xIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M6 18L18 6M6 6l12 12"></path></svg>';

        function openConfirmModal(action, id, name) {
            if (action === 'approve') {
                confirmRejectFields.classList.add('hidden');
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/15 text-mint-dark';
                confirmIconWrap.innerHTML = checkIconSvg;
                confirmTitle.textContent = 'Approve this product?';
                confirmMessage.textContent = `Approve "${name}"? Buyer visibility also requires active status and available stock.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Confirm Approve';
                confirmRejectForm.action = APPROVE_URL.replace('__ID__', id);
            } else {
                confirmRejectFields.classList.remove('hidden');
                confirmIconWrap.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-coral/15 text-coral';
                confirmIconWrap.innerHTML = xIconSvg;
                confirmTitle.textContent = 'Reject this product?';
                confirmMessage.textContent = `Reject "${name}"? The seller can correct and resubmit it for review.`;
                confirmProceedBtn.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-coral hover:opacity-90 transition-all duration-300';
                confirmProceedBtn.textContent = 'Confirm Reject';
                confirmRejectForm.action = REJECT_URL.replace('__ID__', id);
                document.getElementById('confirmRejectReason').value = 'wrong_category';
                document.getElementById('confirmRejectNotes').value = '';
            }

            confirmOverlay.classList.remove('hidden');
            confirmOverlay.classList.add('flex');
            requestAnimationFrame(() => confirmPanel.classList.remove('translate-y-2', 'opacity-0'));
        }

        function closeConfirmModal() {
            confirmPanel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                confirmOverlay.classList.add('hidden');
                confirmOverlay.classList.remove('flex');
            }, 150);
        }

        document.querySelectorAll('.product-approve-btn').forEach(btn => {
            btn.addEventListener('click', () => openConfirmModal('approve', btn.dataset.productId, btn.dataset.productName));
        });
        document.querySelectorAll('.product-reject-btn').forEach(btn => {
            btn.addEventListener('click', () => openConfirmModal('reject', btn.dataset.productId, btn.dataset.productName));
        });

        confirmCancelBtn.addEventListener('click', closeConfirmModal);
        confirmOverlay.addEventListener('click', (e) => { if (e.target === confirmOverlay) closeConfirmModal(); });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!confirmOverlay.classList.contains('hidden')) closeConfirmModal();
            else if (!overlay.classList.contains('hidden')) closeProductModal();
        });
    </script>

@endsection