<style>
    .pc-review-dialog::backdrop,.pc-decision-dialog::backdrop{background:rgba(16,25,60,.62);backdrop-filter:blur(3px)}
    .pc-review-dialog,.pc-decision-dialog{font-family:Poppins,sans-serif;color:#10193c}
    .pc-review-dialog[open],.pc-decision-dialog[open]{display:flex;flex-direction:column}
</style>

@foreach($products as $product)
    <dialog id="review-product-{{ $product->id }}" class="pc-review-dialog m-auto w-[min(94vw,850px)] max-h-[88dvh] overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl" aria-labelledby="review-title-{{ $product->id }}">
        <header class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
            <div class="min-w-0"><p class="text-[10px] font-bold uppercase tracking-widest text-teal-dark">Product compliance review</p><h2 id="review-title-{{ $product->id }}" class="mt-1 truncate text-lg font-bold">{{ $product->name }}</h2><p class="text-xs text-slate-500">{{ $product->seller?->business_name ?? 'Seller unavailable' }} · {{ $product->sku ?: 'No SKU' }}</p></div>
            <button type="button" data-review-close class="sh-btn-soft shrink-0" aria-label="Close review">Close</button>
        </header>
        <div class="min-h-0 overflow-y-auto px-4 py-4 sm:px-5">
            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                <section aria-label="Product media" class="min-w-0 rounded-xl bg-slate-50 p-3">
                    @if($product->images->isNotEmpty())
                        <img src="{{ asset('storage/'.$product->images->first()->image_path) }}" alt="{{ $product->name }}" class="aspect-square max-h-64 w-full rounded-lg bg-white object-contain">
                        @if($product->images->count() > 1)
                            <div class="mt-2 flex gap-2 overflow-x-auto pb-1">
                                @foreach($product->images as $image)
                                    <button type="button" data-review-thumbnail="{{ asset('storage/'.$image->image_path) }}" class="shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white" aria-label="Preview product image {{ $loop->iteration }}"><img src="{{ asset('storage/'.$image->image_path) }}" alt="" loading="lazy" class="h-12 w-12 object-cover"></button>
                                @endforeach
                            </div>
                        @endif
                    @elseif($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="aspect-square max-h-64 w-full rounded-lg bg-white object-contain">
                    @else
                        <div class="flex aspect-square max-h-64 items-center justify-center rounded-lg bg-white text-xs text-slate-400">No product photo</div>
                    @endif
                    @if($product->video_url)
                        <video controls playsinline preload="metadata" src="{{ $product->video_url }}" class="mt-3 aspect-video w-full rounded-lg bg-navy"></video>
                    @endif
                </section>
                <div class="space-y-3 text-xs">
                    <section class="rounded-xl border border-slate-200 p-3"><h3 class="mb-2 text-xs font-bold">Listing details</h3>
                        <dl class="grid grid-cols-[105px_1fr] gap-x-3 gap-y-2"><dt class="text-slate-500">Status</dt><dd>{{ ucwords(str_replace('_', ' ', $product->compliance_status)) }}</dd><dt class="text-slate-500">Category</dt><dd>{{ $product->category }}{{ $product->subcategory ? ' / '.$product->subcategory : '' }}</dd><dt class="text-slate-500">Seller type</dt><dd>{{ $product->seller?->business_category ?? 'Unavailable' }}</dd><dt class="text-slate-500">Price</dt><dd>₱{{ number_format((float)$product->price, 2) }}</dd><dt class="text-slate-500">Stock</dt><dd>{{ number_format((int)$product->stock) }} units</dd><dt class="text-slate-500">Submitted</dt><dd>{{ $product->submitted_at?->format('M j, Y g:i A') ?? 'Not recorded' }}</dd></dl>
                    </section>
                    <section class="rounded-xl border border-slate-200 p-3"><h3 class="mb-1 text-xs font-bold">Description</h3><p class="max-h-32 overflow-y-auto whitespace-pre-line leading-relaxed text-slate-600">{{ $product->description ?: 'No description supplied.' }}</p></section>
                    @if($product->has_variants && $product->variants->isNotEmpty())
                        <section class="rounded-xl border border-slate-200 p-3"><h3 class="mb-2 text-xs font-bold">Variations</h3><ul class="max-h-32 space-y-1 overflow-y-auto">@foreach($product->variants as $variant)<li class="flex justify-between gap-2"><span>{{ $variant->displayLabel() }}</span><span class="shrink-0 text-slate-500">{{ $variant->stock }} · {{ $variant->status }}</span></li>@endforeach</ul></section>
                    @endif
                    @if($product->rejection_reason)
                        <section class="rounded-xl border border-red-200 bg-red-50 p-3"><h3 class="font-bold text-red-700">Previous rejection</h3><p>{{ \App\Http\Controllers\Admin\ProductComplianceController::REJECTION_REASONS[$product->rejection_reason] ?? $product->rejection_reason }}</p><p class="mt-1 text-slate-600">{{ $product->rejection_notes }}</p></section>
                    @endif
                </div>
            </div>
        </div>
        <footer class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-white px-4 py-3 sm:px-5">
            <a class="sh-btn-soft" href="{{ route('admin.compliance.show', $product) }}">Full details</a>
            <div class="flex gap-2">
                @if($product->compliance_status !== 'rejected')<button type="button" class="sh-btn-danger" data-decision-kind="reject" data-decision-url="{{ route('admin.compliance.reject', $product) }}" data-decision-name="{{ $product->name }}" data-decision-id="{{ $product->id }}">Reject</button>@endif
                @if($product->compliance_status !== 'approved')<button type="button" class="sh-btn" data-decision-kind="approve" data-decision-url="{{ route('admin.compliance.approve', $product) }}" data-decision-name="{{ $product->name }}" data-decision-id="{{ $product->id }}">Approve</button>@endif
            </div>
        </footer>
    </dialog>
@endforeach

<dialog id="productDecisionDialog" class="pc-decision-dialog m-auto w-[min(94vw,420px)] rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl" aria-labelledby="productDecisionTitle">
    <form id="productDecisionForm" method="POST" class="p-5">
        @csrf @method('PATCH')
        <input type="hidden" name="review_product_id" id="productDecisionId">
        <p class="text-[10px] font-bold uppercase tracking-widest text-teal-dark">Confirm decision</p>
        <h2 id="productDecisionTitle" class="mt-1 text-lg font-bold"></h2>
        <p id="productDecisionMessage" class="mt-2 text-xs leading-relaxed text-slate-600"></p>
        <div id="productRejectFields" class="mt-4 space-y-3" hidden>
            <label class="block text-xs font-semibold">Reason <select id="productRejectReason" name="rejection_reason" disabled class="sh-select mt-1 w-full"><option value="">Choose a reason</option>@foreach(\App\Http\Controllers\Admin\ProductComplianceController::REJECTION_REASONS as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
            <label class="block text-xs font-semibold">Notes for the seller <textarea id="productRejectNotes" name="rejection_notes" rows="3" maxlength="2000" disabled class="sh-input mt-1 w-full" placeholder="What should the seller correct?"></textarea></label>
        </div>
        <div class="mt-5 flex justify-end gap-2"><button type="button" data-decision-cancel class="sh-btn-soft">Cancel</button><button id="productDecisionSubmit" type="submit" class="sh-btn">Confirm</button></div>
    </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const decision = document.getElementById('productDecisionDialog');
    const form = document.getElementById('productDecisionForm');
    const reason = document.getElementById('productRejectReason');
    const notes = document.getElementById('productRejectNotes');
    document.querySelectorAll('[data-review-open]').forEach(button => button.addEventListener('click', () => {
        document.getElementById(button.dataset.reviewOpen)?.showModal();
    }));
    document.querySelectorAll('[data-review-close]').forEach(button => button.addEventListener('click', () => button.closest('dialog')?.close()));
    document.querySelectorAll('[data-review-thumbnail]').forEach(button => button.addEventListener('click', () => {
        button.closest('section')?.querySelector('img.aspect-square')?.setAttribute('src', button.dataset.reviewThumbnail);
    }));
    document.querySelectorAll('[data-decision-kind]').forEach(button => button.addEventListener('click', () => {
        const rejecting = button.dataset.decisionKind === 'reject';
        form.action = button.dataset.decisionUrl;
        document.getElementById('productDecisionId').value = button.dataset.decisionId;
        document.getElementById('productDecisionTitle').textContent = rejecting ? 'Reject this product?' : 'Approve this product?';
        document.getElementById('productDecisionMessage').textContent = rejecting
            ? `“${button.dataset.decisionName}” will be hidden from new purchases. The seller can correct and resubmit it.`
            : `“${button.dataset.decisionName}” can appear to buyers when it is active and stocked.`;
        document.getElementById('productRejectFields').hidden = !rejecting;
        reason.disabled = notes.disabled = !rejecting;
        reason.required = rejecting;
        reason.value = '';
        notes.value = '';
        document.getElementById('productDecisionSubmit').textContent = rejecting ? 'Confirm rejection' : 'Confirm approval';
        decision.showModal();
        if (rejecting) reason.focus();
    }));
    document.querySelector('[data-decision-cancel]')?.addEventListener('click', () => decision.close());
    form.addEventListener('submit', () => { document.getElementById('productDecisionSubmit').disabled = true; });
});
</script>
