@extends('admin.layout')
@section('title', 'Inspect Product Compliance')
@section('content')
{{-- Persisted detail separates review history from compatibility approval and inventory availability. --}}
<div class="compliance-workspace space-y-4" data-console-detail-source>
    <a data-console-detail-dismiss href="{{ route('admin.product-compliance.index', ['filter' => $product->compliance_status]) }}">Back to Product Compliance</a>
    <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
    <p>{{ $product->seller?->business_name ?? 'Seller unavailable' }} · Product #{{ $product->id }}</p>
    @if(session('status'))<p class="pc-panel" role="status">{{ session('status') }}</p>@endif
    @if($errors->any())<div class="pc-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <div class="pc-grid">
        <section class="pc-panel"><h2>Product details</h2><p>Compliance: {{ ucwords(str_replace('_', ' ', $product->compliance_status)) }}</p><p>Inventory: {{ $product->status }} · {{ $product->stock }} in stock</p><p>Category: {{ $product->category }}</p><p>SKU: {{ $product->sku ?: 'Not supplied' }}</p><p>Price: ₱{{ number_format((float)$product->price, 2) }}</p><p>{{ $product->description ?: 'No description supplied.' }}</p>
            @foreach($product->variants as $variant)<p>Variant: {{ $variant->name }} · {{ $variant->stock }} in stock · {{ $variant->status }}</p>@endforeach
        </section>
        <section class="pc-panel"><h2>Review history</h2><p>Submitted: {{ $product->submitted_at?->format('M j, Y g:i A') ?? 'Not recorded' }}</p><p>Reviewed: {{ $product->reviewed_at?->format('M j, Y g:i A') ?? 'No human review recorded' }}</p><p>Reviewer user ID: {{ $product->reviewed_by ?? 'None' }}</p>
            @if($product->rejection_reason)<p>Reason: {{ $reasons[$product->rejection_reason] ?? $product->rejection_reason }}</p><p>{{ $product->rejection_notes }}</p>@endif
        </section>
    </div>
    {{-- Escaped storage URLs and text avoid the reference modal's HTML interpolation of Product data. --}}
    @if($product->is_video_first && $product->video_url)
        <section class="pc-panel"><h2>Featured video — first uploaded / selected</h2>
            <video controls muted playsinline preload="metadata" style="max-width:480px;width:100%;border-radius:12px" src="{{ $product->video_url }}"></video>
        </section>
    @endif
    <section class="pc-panel"><h2>Product images</h2><div class="pc-images">@forelse($product->images as $image)<img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $product->name }}">@empty @if($product->image)<img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">@else<p>No images supplied.</p>@endif @endforelse</div></section>
    @if($product->video_url && ! $product->is_video_first)
        <section class="pc-panel"><h2>Product video</h2><video controls playsinline preload="metadata" style="max-width:480px;width:100%;border-radius:12px" src="{{ $product->video_url }}"></video></section>
    @endif
    {{-- Explicit native confirmation sections remain keyboard usable and never promise archived merchandise is live. --}}
    <div class="pc-grid">
        <details class="pc-panel"><summary>Approve Product</summary><p>Confirm approval for Buyer visibility when active and stocked. This clears rejection details.</p><form method="POST" action="{{ route('admin.compliance.approve', $product) }}">@csrf @method('PATCH')<button class="sh-btn" type="submit">Confirm approval</button></form></details>
        <details class="pc-panel" @if($errors->any()) open @endif><summary>Reject Product</summary><p>Rejecting hides this Product from Buyer discovery and prevents new purchases. The Seller may correct and resubmit it.</p><form method="POST" action="{{ route('admin.compliance.reject', $product) }}">@csrf @method('PATCH')<div class="pc-controls"><label>Rejection reason<select name="rejection_reason" required><option value="">Choose a reason</option>@foreach($reasons as $value => $label)<option value="{{ $value }}" @selected(old('rejection_reason') === $value)>{{ $label }}</option>@endforeach</select></label><label>Notes (optional)<textarea name="rejection_notes" rows="3" maxlength="2000">{{ old('rejection_notes') }}</textarea></label></div><button class="sh-btn-danger" type="submit">Confirm rejection</button></form></details>
    </div>
</div>
<dialog id="complianceDetailDecision" class="m-auto w-[min(94vw,420px)] rounded-2xl border border-slate-200 bg-white p-0 font-[Poppins] text-navy shadow-2xl backdrop:bg-navy/60" aria-labelledby="complianceDetailDecisionTitle">
    <div class="p-5"><p class="text-[10px] font-bold uppercase tracking-widest text-teal-dark">Confirm review decision</p><h2 id="complianceDetailDecisionTitle" class="mt-1 text-lg font-bold"></h2><p id="complianceDetailDecisionMessage" class="mt-2 text-xs leading-relaxed text-slate-600"></p><div class="mt-5 flex justify-end gap-2"><button type="button" data-decision-cancel class="sh-btn-soft">Cancel</button><button type="button" data-decision-confirm class="sh-btn">Confirm</button></div></div>
</dialog>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('complianceDetailDecision');
    document.querySelectorAll('.compliance-workspace .pc-grid form').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.decisionConfirmed === '1' || !dialog?.showModal) return;
            event.preventDefault();
            if (!form.reportValidity()) return;
            const rejecting = form.action.includes('/reject');
            dialog.querySelector('#complianceDetailDecisionTitle').textContent = rejecting ? 'Reject this product?' : 'Approve this product?';
            dialog.querySelector('#complianceDetailDecisionMessage').textContent = rejecting
                ? 'This product will be hidden from new purchases. The seller can correct and resubmit it.'
                : 'This product can appear to buyers when it is active and stocked.';
            const confirm = dialog.querySelector('[data-decision-confirm]');
            const cancel = dialog.querySelector('[data-decision-cancel]');
            const submit = () => { form.dataset.decisionConfirmed = '1'; dialog.close(); form.requestSubmit(); };
            confirm.addEventListener('click', submit, { once: true });
            cancel.addEventListener('click', () => dialog.close(), { once: true });
            dialog.addEventListener('close', () => confirm.removeEventListener('click', submit), { once: true });
            dialog.showModal();
        });
    });
});
</script>
@endsection
