@extends('admin.layout')

@section('title', 'Seller Compliance')

@section('content')

    {{-- =========================================================
        Product review queue.
        Seller documents are verified on the Account Registrations page;
        this page is where admins approve or reject the products a
        verified seller submits before they appear on the buyer side.
        Data comes from Admin\ProductComplianceController@index.
    ========================================================= --}}
    @php
        $statusStyles = [
            'pending_review' => ['badge' => 'text-yellow-700 bg-yellow/20', 'label' => 'Pending review'],
            'approved'       => ['badge' => 'text-mint-dark bg-mint/15', 'label' => 'Approved'],
            'rejected'       => ['badge' => 'text-coral bg-coral/10', 'label' => 'Rejected'],
        ];

        $tabs = [
            'pending'  => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];

        $listTitle = [
            'pending'  => 'Products Awaiting Review',
            'approved' => 'Approved Products',
            'rejected' => 'Rejected Products',
        ][$filter];

        $approveUrl = route('admin.compliance.products.approve', ['product' => '__ID__']);
        $rejectUrl  = route('admin.compliance.products.reject', ['product' => '__ID__']);
    @endphp

    {{-- ============ PAGE HEADER ============ --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Seller Compliance</h1>
        <p class="text-sm text-slate-500 mt-1">Review products from verified sellers before they go live to buyers.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-mint/30 bg-mint/10 px-4 py-3 text-sm font-medium text-mint-dark">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3">
            <p class="text-sm font-semibold text-coral">Please review the following:</p>
            <ul class="mt-1 space-y-0.5 text-xs text-coral">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
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
                    <p class="text-xl font-bold text-navy">{{ number_format($counts['pending']) }}</p>
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
                    <p class="text-xl font-bold text-navy">{{ number_format($counts['approved']) }}</p>
                    <p class="text-xs text-slate-500">Approved Products</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                    <x-lucide-alert-triangle class="w-5 h-5 text-coral" />
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">{{ number_format($counts['rejected']) }}</p>
                    <p class="text-xs text-slate-500">Rejected Products</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ============ FILTER TABS + SORT + SEARCH ============ --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">

        <div class="flex items-center gap-2 flex-wrap">
            @foreach ($tabs as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['filter' => $key, 'page' => null]) }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === $key ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                    {{ $label }} <span class="ml-1 opacity-70">({{ number_format($counts[$key]) }})</span>
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-2 w-full xl:w-auto">

            {{-- SORT --}}
            <form method="GET" class="shrink-0">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="sort" onchange="this.form.submit()"
                    class="text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white text-slate-600 font-medium focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>Product name (A–Z)</option>
                    <option value="za" {{ $sort === 'za' ? 'selected' : '' }}>Product name (Z–A)</option>
                    <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>Price (low–high)</option>
                    <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>Price (high–low)</option>
                </select>
            </form>

            {{-- SEARCH --}}
            <form method="GET" class="relative w-full xl:w-72">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by product, SKU, or seller..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl bg-white border border-slate-200 text-navy placeholder:text-slate-400 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
            </form>

        </div>

    </div>

    {{-- ============ PRODUCT LIST ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">{{ $listTitle }}</h2>
            <span class="text-xs text-slate-400">{{ number_format($products->total()) }} total</span>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($products as $product)

                @php
                    $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                    $thumb = $primary
                        ? asset('storage/' . ltrim($primary->image_path, '/'))
                        : ($product->image ? asset('storage/' . ltrim($product->image, '/')) : null);

                    $style = $statusStyles[$product->compliance_status] ?? $statusStyles['pending_review'];
                    $price = (float) $product->price;
                    $final = (int) $product->discount > 0
                        ? $price - ($price * ((int) $product->discount) / 100)
                        : $price;
                    $submitted = $product->submitted_at ?? $product->created_at;
                    $reasonLabel = $product->rejection_reason
                        ? ($rejectionReasons[$product->rejection_reason] ?? \Illuminate\Support\Str::headline($product->rejection_reason))
                        : null;
                @endphp

                <div class="flex items-center justify-between gap-4 px-5 py-4">

                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                            @if ($thumb)
                                <img src="{{ $thumb }}" alt="" class="w-full h-full object-cover">
                            @else
                                <x-lucide-image class="w-5 h-5 text-slate-300" />
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-navy truncate">{{ $product->name }}</p>
                            <p class="text-xs text-slate-500 truncate">
                                {{ $product->seller?->business_name ?? 'Unknown seller' }}
                                · {{ $product->category }}
                                · ₱{{ number_format($final, 2) }}
                            </p>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $style['badge'] }}">
                                    {{ $style['label'] }}
                                </span>

                                @if ($reasonLabel)
                                    <span class="text-[11px] text-coral">{{ $reasonLabel }}</span>
                                @endif

                                @if ($submitted)
                                    <span class="text-[11px] text-slate-400">Submitted {{ $submitted->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button"
                            class="js-review px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50"
                            data-product-id="{{ $product->id }}">
                            Review
                        </button>

                        @if ($product->compliance_status === 'pending_review')
                            <button type="button"
                                class="js-approve px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90"
                                data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                                Approve
                            </button>
                            <button type="button"
                                class="js-reject px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5"
                                data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                                Reject
                            </button>
                        @elseif ($product->compliance_status === 'approved')
                            <button type="button"
                                class="js-reject px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5"
                                data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-takedown="1">
                                Take down
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
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $filter === 'pending' ? 'Nothing is waiting for review right now.' : 'Try a different tab or search.' }}
                    </p>
                </div>

            @endforelse

        </div>

        @if ($products->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif

    </div>


    {{-- =========================================================
        PRODUCT DETAILS MODAL
    ========================================================= --}}
    <div id="detailOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <div id="detailPanel" class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-xl translate-y-2 opacity-0 transition duration-150">

            <div class="h-1.5 bg-mint-dark rounded-t-2xl"></div>

            <button type="button" id="detailClose" aria-label="Close"
                class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-slate-100 text-navy/45 flex items-center justify-center hover:bg-mint/10 hover:text-mint-dark focus:outline-none focus:ring-4 focus:ring-mint/15 transition">
                <x-lucide-x class="w-4 h-4" />
            </button>

            <div class="px-6 pt-9 pb-5 border-b border-slate-100 pr-16">
                <p class="text-[11px] font-bold tracking-[0.12em] text-mint-dark mb-1">PRODUCT REVIEW</p>
                <h2 id="detailName" class="text-xl sm:text-2xl font-bold text-navy"></h2>
                <p id="detailSeller" class="text-xs text-slate-500 mt-0.5"></p>
                <span id="detailBadge" class="inline-flex mt-3 text-xs font-semibold px-2.5 py-1 rounded-full"></span>
            </div>

            <div id="detailBody" class="px-6 py-5 grid grid-cols-1 lg:grid-cols-2 gap-5"></div>

            <div id="detailFooter" class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2"></div>

        </div>

    </div>


    {{-- =========================================================
        APPROVE / REJECT CONFIRMATION MODAL
        One modal, one form: the action URL and fields are set in
        openConfirm() depending on approve / reject / take down.
    ========================================================= --}}
    <div id="confirmOverlay" class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy/40 backdrop-blur-[2px] px-4">

        <form id="confirmForm" method="POST" action="#"
            class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl p-6">
            @csrf

            <div id="confirmIcon" class="w-11 h-11 rounded-xl flex items-center justify-center mb-4"></div>

            <h3 id="confirmTitle" class="text-base font-bold text-navy mb-1.5"></h3>
            <p id="confirmMessage" class="text-sm text-slate-500 leading-relaxed mb-4"></p>

            <div id="confirmRejectFields" class="hidden mb-4 space-y-3">
                <div>
                    <label for="confirmReason" class="block text-xs font-semibold text-slate-500 mb-1.5">Reason</label>
                    <select id="confirmReason" name="reason"
                        class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-coral/40">
                        @foreach ($rejectionReasons as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="confirmNotes" class="block text-xs font-semibold text-slate-500 mb-1.5">
                        Notes for the seller <span id="confirmNotesHint" class="font-normal text-slate-400">(optional)</span>
                    </label>
                    <textarea id="confirmNotes" name="notes" rows="3" maxlength="1000"
                        placeholder="Tell the seller what to fix before resubmitting..."
                        class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-coral/40"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" id="confirmCancel"
                    class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" id="confirmSubmit"
                    class="h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white transition-all duration-300"></button>
            </div>

        </form>

    </div>


    <script>
        const productsData = @json($productsForJs);
        const approveUrlTemplate = @json($approveUrl);
        const rejectUrlTemplate = @json($rejectUrl);

        const badgeStyles = {
            pending_review: { cls: 'text-yellow-700 bg-yellow/20', label: 'Pending review' },
            approved:       { cls: 'text-mint-dark bg-mint/15',    label: 'Approved' },
            rejected:       { cls: 'text-coral bg-coral/10',       label: 'Rejected' },
        };

        // Seller-controlled text is rendered in the admin console, so every dynamic
        // value that goes into innerHTML must be escaped.
        function esc(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function infoRow(label, value) {
            return `
                <div class="flex justify-between gap-3">
                    <dt class="text-xs text-slate-400 shrink-0">${esc(label)}</dt>
                    <dd class="text-xs text-slate-700 text-right">${value === null || value === '' || value === undefined ? '—' : esc(value)}</dd>
                </div>`;
        }

        /* -----------------------------------------------------------
           DETAILS MODAL
        ----------------------------------------------------------- */
        const detailOverlay = document.getElementById('detailOverlay');
        const detailPanel   = document.getElementById('detailPanel');
        const detailBody    = document.getElementById('detailBody');
        const detailFooter  = document.getElementById('detailFooter');

        function openDetails(id) {
            const p = productsData.find(item => item.id === id);
            if (!p) return;

            document.getElementById('detailName').textContent = p.name;
            document.getElementById('detailSeller').textContent =
                'Sold by ' + p.seller_name + (p.seller_email ? ' · ' + p.seller_email : '');

            const badge = badgeStyles[p.status] || badgeStyles.pending_review;
            const badgeEl = document.getElementById('detailBadge');
            badgeEl.className = 'inline-flex mt-3 text-xs font-semibold px-2.5 py-1 rounded-full ' + badge.cls;
            badgeEl.textContent = badge.label;

            const gallery = p.images.length
                ? `
                    <div>
                        <div class="aspect-square rounded-xl border border-slate-200 bg-slate-50 overflow-hidden">
                            <img id="detailMainImage" src="${esc(p.images[0])}" alt="" class="w-full h-full object-cover">
                        </div>
                        ${p.images.length > 1 ? `
                            <div class="grid grid-cols-5 gap-2 mt-2">
                                ${p.images.map(url => `
                                    <button type="button" class="js-thumb aspect-square rounded-lg border border-slate-200 overflow-hidden" data-src="${esc(url)}">
                                        <img src="${esc(url)}" alt="" class="w-full h-full object-cover">
                                    </button>`).join('')}
                            </div>` : ''}
                    </div>`
                : `
                    <div class="aspect-square rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-xs text-slate-400">
                        No images uploaded
                    </div>`;

            const variants = p.has_variants && p.variants.length
                ? `
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Variants</p>
                        <ul class="space-y-2">
                            ${p.variants.map(v => `
                                <li class="flex justify-between gap-3 text-xs text-slate-700">
                                    <span class="truncate">${esc(v.name)}${v.sku ? ' · ' + esc(v.sku) : ''}${v.status === 'archived' ? ' (archived)' : ''}</span>
                                    <span class="shrink-0">${v.price ? '₱' + esc(v.price) + ' · ' : ''}${esc(v.stock)} in stock</span>
                                </li>`).join('')}
                        </ul>
                    </div>`
                : '';

            const rejection = p.status === 'rejected'
                ? `
                    <div class="bg-coral/5 border border-coral/15 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-coral mb-1.5">Rejection</p>
                        <p class="text-xs text-slate-700 font-medium">${esc(p.rejection_reason)}</p>
                        ${p.rejection_notes ? `<p class="text-xs text-slate-600 mt-1.5 leading-relaxed">${esc(p.rejection_notes)}</p>` : ''}
                    </div>`
                : '';

            detailBody.innerHTML = `
                <div class="space-y-4">
                    ${gallery}
                </div>

                <div class="space-y-4">
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Product Information</p>
                        <dl class="space-y-2.5">
                            ${infoRow('Category', p.category)}
                            ${infoRow('SKU', p.sku)}
                            ${infoRow('Regular price', '₱' + p.price)}
                            ${infoRow('Discount', p.discount > 0 ? p.discount + '%' : 'None')}
                            ${infoRow('Selling price', '₱' + p.final_price)}
                            ${infoRow('Stock', p.stock)}
                        </dl>
                    </div>

                    ${variants}

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Description</p>
                        <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">${p.description ? esc(p.description) : 'No description provided.'}</p>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-3">Review History</p>
                        <dl class="space-y-2.5">
                            ${infoRow('Submitted', p.submitted_at)}
                            ${infoRow('Last reviewed', p.reviewed_at)}
                            ${infoRow('Reviewed by', p.reviewed_by)}
                        </dl>
                    </div>

                    ${rejection}
                </div>`;

            detailFooter.innerHTML = '';

            if (p.status === 'pending_review') {
                detailFooter.appendChild(makeButton('Reject', 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 transition',
                    () => openConfirm('reject', p.id, p.name, false)));
                detailFooter.appendChild(makeButton('Approve', 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition',
                    () => openConfirm('approve', p.id, p.name, false)));
            } else if (p.status === 'approved') {
                detailFooter.appendChild(makeButton('Take down', 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5 transition',
                    () => openConfirm('reject', p.id, p.name, true)));
            }

            detailOverlay.classList.remove('hidden');
            detailOverlay.classList.add('flex');
            requestAnimationFrame(() => detailPanel.classList.remove('translate-y-2', 'opacity-0'));
        }

        function makeButton(label, className, onClick) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = className;
            button.textContent = label;
            button.addEventListener('click', onClick);
            return button;
        }

        function closeDetails() {
            detailPanel.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                detailOverlay.classList.add('hidden');
                detailOverlay.classList.remove('flex');
            }, 150);
        }

        detailBody.addEventListener('click', (event) => {
            const thumb = event.target.closest('.js-thumb');
            if (!thumb) return;
            document.getElementById('detailMainImage').src = thumb.dataset.src;
        });

        document.getElementById('detailClose').addEventListener('click', closeDetails);
        detailOverlay.addEventListener('click', (event) => { if (event.target === detailOverlay) closeDetails(); });

        document.querySelectorAll('.js-review').forEach(button => {
            button.addEventListener('click', () => openDetails(parseInt(button.dataset.productId, 10)));
        });

        /* -----------------------------------------------------------
           APPROVE / REJECT / TAKE DOWN CONFIRMATION
        ----------------------------------------------------------- */
        const confirmOverlay = document.getElementById('confirmOverlay');
        const confirmForm    = document.getElementById('confirmForm');
        const confirmIcon    = document.getElementById('confirmIcon');
        const confirmTitle   = document.getElementById('confirmTitle');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmSubmit  = document.getElementById('confirmSubmit');
        const confirmFields  = document.getElementById('confirmRejectFields');
        const confirmReason  = document.getElementById('confirmReason');
        const confirmNotes   = document.getElementById('confirmNotes');
        const confirmHint    = document.getElementById('confirmNotesHint');

        const checkIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        const flagIcon  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>';

        function syncNotesRequirement() {
            const required = confirmReason.value === 'other';
            confirmNotes.required = required;
            confirmHint.textContent = required ? '(required for "Other")' : '(optional)';
        }

        function openConfirm(action, id, name, takedown) {
            const isApprove = action === 'approve';

            confirmForm.action = (isApprove ? approveUrlTemplate : rejectUrlTemplate).replace('__ID__', id);
            confirmFields.classList.toggle('hidden', isApprove);

            // Only send reason/notes when rejecting.
            confirmReason.disabled = isApprove;
            confirmNotes.disabled = isApprove;

            if (isApprove) {
                confirmIcon.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-mint/15 text-mint-dark';
                confirmIcon.innerHTML = checkIcon;
                confirmTitle.textContent = 'Approve this product?';
                confirmMessage.textContent = name + ' will become visible to buyers once it is active and in stock.';
                confirmSubmit.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-mint-dark hover:opacity-90 transition-all duration-300';
                confirmSubmit.textContent = 'Confirm Approve';
            } else {
                confirmIcon.className = 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-coral/15 text-coral';
                confirmIcon.innerHTML = flagIcon;
                confirmTitle.textContent = takedown ? 'Take down this product?' : 'Reject this product?';
                confirmMessage.textContent = takedown
                    ? name + ' will be hidden from buyers right away. The seller must fix it and resubmit for review.'
                    : name + ' will stay hidden from buyers. The seller will see your reason and can resubmit after editing.';
                confirmSubmit.className = 'h-9 inline-flex items-center px-4 rounded-full text-xs font-semibold text-white bg-coral hover:opacity-90 transition-all duration-300';
                confirmSubmit.textContent = takedown ? 'Confirm Take Down' : 'Confirm Reject';
                confirmReason.selectedIndex = 0;
                confirmNotes.value = '';
                syncNotesRequirement();
            }

            confirmOverlay.classList.remove('hidden');
            confirmOverlay.classList.add('flex');
        }

        function closeConfirm() {
            confirmOverlay.classList.add('hidden');
            confirmOverlay.classList.remove('flex');
        }

        confirmReason.addEventListener('change', syncNotesRequirement);
        document.getElementById('confirmCancel').addEventListener('click', closeConfirm);
        confirmOverlay.addEventListener('click', (event) => { if (event.target === confirmOverlay) closeConfirm(); });

        document.querySelectorAll('.js-approve').forEach(button => {
            button.addEventListener('click', () => openConfirm('approve', parseInt(button.dataset.productId, 10), button.dataset.productName, false));
        });

        document.querySelectorAll('.js-reject').forEach(button => {
            button.addEventListener('click', () => openConfirm('reject', parseInt(button.dataset.productId, 10), button.dataset.productName, button.dataset.takedown === '1'));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            if (!confirmOverlay.classList.contains('hidden')) closeConfirm();
            else if (!detailOverlay.classList.contains('hidden')) closeDetails();
        });
    </script>

@endsection