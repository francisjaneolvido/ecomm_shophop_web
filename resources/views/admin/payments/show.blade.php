@extends('admin.layout')
@section('title', 'Payment Review')
@include('payments.partials.styles')
@section('content')
{{-- Review separates evidence, proof decisions and terminal closure while preserving all server authority. --}}
{{-- Admin and Buyer use the same persisted status vocabulary; no provider state is inferred. --}}
@php
    $stateTitle = match ($payment->status) {
        'awaiting_proof' => 'Awaiting Payment Proof', 'pending_review' => 'Pending Payment Verification',
        'rejected' => 'Payment Rejected / Resubmission Required', 'verified' => 'Verified by ShopHop Admin',
        'cancelled' => 'Cancelled', 'expired' => 'Expired', default => 'Payment status unavailable',
    };
    $stateTone = in_array($payment->status, ['awaiting_proof', 'rejected'], true) ? 'warning'
        : (in_array($payment->status, ['pending_review', 'verified'], true) ? 'brand' : 'neutral');
@endphp
<div class="payment-ui pay-container">
    <nav aria-label="Payment navigation"><a class="pay-back" href="{{ route('admin.payments.index') }}"><x-lucide-arrow-left aria-hidden="true" />Pending Payments</a></nav>
    <header class="pay-header"><div><h1>Payment #{{ $payment->id }}</h1><p>Review the evidence against the full checkout group.</p></div><span class="pay-status" data-tone="{{ $stateTone }}">{{ $stateTitle }}</span></header>
    @include('payments.partials.alerts')
    <div class="pay-grid"><div class="pay-stack">
        {{-- Submitted identity and private receipt remain separate from amount and decisions. --}}
        <section class="pay-panel pay-pad" aria-labelledby="evidence-heading">
            <div class="pay-section-head"><span class="pay-icon"><x-lucide-file-image aria-hidden="true" /></span><h2 id="evidence-heading">Buyer & payment evidence</h2></div>
            <dl class="pay-facts"><div><dt>Buyer</dt><dd>{{ trim(($payment->buyer?->first_name ?? '').' '.($payment->buyer?->last_name ?? '')) ?: 'Buyer unavailable' }}</dd></div><div><dt>Method</dt><dd>Online Payment · manual proof review</dd></div><div><dt>Submitted reference</dt><dd>{{ $payment->reference ?: 'Not submitted' }}</dd></div><div><dt>Submitted</dt><dd>{{ $payment->submitted_at?->format('M j, Y g:i A T') ?? 'Not submitted' }}</dd></div></dl>
            @if ($payment->receipt_path)<div class="pay-actions"><a class="pay-button" href="{{ route('payments.receipt', $payment) }}">View private receipt<x-lucide-arrow-up-right aria-hidden="true" /></a></div><p class="pay-note">Open the receipt and compare it with the submitted reference and expected group amount.</p>@else<p class="pay-note">No receipt has been submitted.</p>@endif
        </section>
        {{-- Only retained current review metadata is available; resubmission clears earlier decisions, so do not fabricate history. --}}
        <section class="pay-panel pay-pad" aria-labelledby="review-state-heading">
            <div class="pay-section-head"><span class="pay-icon"><x-lucide-history aria-hidden="true" /></span><h2 id="review-state-heading">Current review record</h2></div><p><strong>{{ $stateTitle }}</strong></p>
            @if ($payment->reviewed_at)<dl class="pay-facts pay-divider"><div><dt>Recorded decision</dt><dd>{{ ucfirst($payment->decision ?? 'Unavailable') }}</dd></div><div><dt>Reviewed by</dt><dd>{{ $payment->reviewer?->email ?? 'Admin' }}</dd></div><div><dt>Reviewed</dt><dd>{{ $payment->reviewed_at->format('M j, Y g:i A T') }}</dd></div></dl>@else<p class="pay-note">No Admin proof decision is recorded for the current submission.</p>@endif
            @if ($payment->status === 'rejected' && $payment->rejection_reason)<div class="pay-reason"><strong>Rejection reason</strong><p>{{ $payment->rejection_reason }}</p></div>@endif
            @if ($payment->expires_at && in_array($payment->status, ['awaiting_proof', 'rejected'], true))<div class="pay-deadline"><x-lucide-clock aria-hidden="true" /><p>Buyer proof deadline: <strong>{{ $payment->expires_at->format('M j, Y g:i A T') }}</strong></p></div>@endif
            @if ($payment->status === 'pending_review')<p class="pay-note">Expiry is paused while this submission awaits Admin review.</p>@endif
            @if ($payment->closed_at)<div class="pay-reason"><strong>Closed {{ $payment->closed_at->format('M j, Y g:i A T') }}</strong><p>{{ $payment->closure_reason }}</p></div>@endif
        </section>
        @if ($payment->status === 'pending_review')
            {{-- Verify releases fulfillment; Reject keeps this payment open for correction. Server locks still decide stale actions. --}}
            <section class="pay-panel pay-pad" aria-labelledby="decision-heading">
                <div class="pay-section-head"><span class="pay-icon"><x-lucide-shield-check aria-hidden="true" /></span><h2 id="decision-heading">Review decision</h2></div>
                <h3>Verify payment proof</h3><p class="pay-muted pay-small">Verification releases the linked Seller Orders for fulfillment. Confirm the receipt, reference and full group amount before proceeding.</p>
                <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="pay-actions">@csrf<button type="submit" class="pay-button pay-button--primary"><x-lucide-check aria-hidden="true" />Verify by ShopHop Admin</button></form>
                <div class="pay-divider"><h3>Reject proof for correction</h3><p class="pay-muted pay-small" style="margin-bottom: 1rem">The payment stays alive. The Buyer receives a fresh proof window to correct evidence on this same payment. Orders and inventory are not cancelled or restored.</p>
                    <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">@csrf<div class="pay-field"><label for="rejection-reason">Rejection reason</label><textarea id="rejection-reason" name="reason" required maxlength="1000" aria-describedby="rejection-help" @error('reason') aria-invalid="true" @enderror>{{ old('reason') }}</textarea><p id="rejection-help" class="pay-help">Tell the Buyer exactly what needs correcting.</p></div><div class="pay-actions"><button type="submit" class="pay-button">Reject Proof</button></div></form>
                </div>
            </section>
        @endif
    </div>
    {{-- The expected amount uses persisted payment membership even in terminal states. --}}
    <aside class="pay-stack pay-summary" aria-label="Payment group summary"><section class="pay-panel pay-pad"><h2>Expected group amount</h2><div class="pay-amount">₱{{ number_format((float) $payment->expectedAmount(), 2) }}</div><p class="pay-muted pay-small">Compare proof with the total of all linked Seller Orders.</p></section>@include('payments.partials.orders')</aside>
    </div>
    @if (in_array($payment->status, ['awaiting_proof', 'pending_review', 'rejected'], true))@include('payments.partials.cancel', ['admin' => true])@endif
</div>
@endsection
