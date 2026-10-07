@extends('layouts.app')
@section('title', 'Online Payment — ShopHop')
@section('hideChrome', true)
@include('payments.partials.styles')
@section('content')
{{-- Keep Buyer marketplace navigation intact; the shared state describes persisted review truth. --}}
@include('buyer.partials.navbar-buyer')
{{-- Presentation labels describe persisted ShopHop review states, never provider confirmation. --}}
@php
    [$stateTitle, $stateHeading, $stateCopy, $stateTone] = match ($payment->status) {
        'awaiting_proof' => ['Awaiting Payment Proof', 'Submit your payment proof', 'Your Seller Orders are waiting for payment verification. Add your transaction reference and receipt for ShopHop Admin review.', 'warning'],
        'pending_review' => ['Pending Payment Verification', 'Your proof is with ShopHop Admin', 'You do not need to submit again. The proof deadline is paused while Admin reviews your evidence. Seller fulfillment can begin after verification.', 'brand'],
        'rejected' => ['Payment Rejected / Resubmission Required', 'Update your proof and submit again', 'Check the rejection reason below, correct your reference or receipt, and resubmit on this same payment. Your Seller Orders remain linked.', 'warning'],
        'verified' => ['Verified by ShopHop Admin', 'Payment proof verified', 'ShopHop Admin has verified your submitted evidence. Your Seller Orders are eligible for fulfillment. Check My Orders for their current progress.', 'brand'],
        'cancelled' => ['Cancelled', 'This checkout payment is cancelled', 'All Seller Orders in this checkout group are closed. This payment cannot be reopened or submitted again.', 'neutral'],
        'expired' => ['Expired', 'The proof deadline has passed', 'All Seller Orders in this checkout group are closed. This payment cannot be reopened or submitted again.', 'neutral'],
        default => ['Payment status unavailable', 'Check your Orders', 'The current payment state is unavailable. Return to My Orders for the recorded Order details.', 'neutral'],
    };
@endphp
<div class="payment-ui pay-page"><div class="pay-container">
    <nav aria-label="Payment navigation"><a class="pay-back" href="{{ route('buyer.orders') }}"><x-lucide-arrow-left aria-hidden="true" />Back to My Orders</a></nav>
    {{-- The next action remains above the amount and Orders at narrow widths. --}}
    <header class="pay-header"><div><h1>Online Payment #{{ $payment->id }}</h1><p>{{ $stateHeading }} · One payment for your linked Seller Orders.</p></div><span class="pay-status" data-tone="{{ $stateTone }}">{{ $stateTitle }}</span></header>
    @include('payments.partials.alerts')
    <div class="pay-grid">
        <div class="pay-stack">
            {{-- Exact persisted deadlines include timezone; pending and terminal states never invent a countdown. --}}
            <section class="pay-panel pay-pad" aria-labelledby="payment-next-heading">
                <div class="pay-state">
                    <span class="pay-icon"><x-lucide-clipboard-check aria-hidden="true" /></span><h2 id="payment-next-heading">{{ $stateHeading }}</h2><p class="pay-muted">{{ $stateCopy }}</p>
                    @if ($payment->status === 'rejected' && $payment->rejection_reason)<div class="pay-reason"><strong>Admin rejection reason</strong><p>{{ $payment->rejection_reason }}</p></div>@endif
                    @if ($payment->closed_at)
                        <p class="pay-note">Closed {{ $payment->closed_at->format('M j, Y g:i A T') }}</p>
                        @if ($payment->closure_reason)<div class="pay-reason"><strong>Closure reason</strong><p>{{ $payment->closure_reason }}</p></div>@endif
                    @endif
                </div>
                @if (in_array($payment->status, ['awaiting_proof', 'rejected'], true))
                    {{-- Preserve the multipart field contract and resubmit on this same payment. --}}
                    <h2>{{ $payment->status === 'rejected' ? 'Correct your payment proof' : 'Payment proof' }}</h2>
                    <p class="pay-note" style="margin-bottom: 1.25rem">Complete your payment using the agreed external payment method, then submit the transaction reference and receipt below for ShopHop Admin review.</p>
                    <form method="POST" enctype="multipart/form-data" action="{{ route('buyer.payments.submit', $payment) }}" class="pay-fields">@csrf
                        <div class="pay-field"><label for="payment-reference">Payment reference</label><input id="payment-reference" name="reference" value="{{ old('reference', $payment->reference) }}" maxlength="120" required aria-describedby="reference-help @error('reference') reference-error @enderror" @error('reference') aria-invalid="true" @enderror><p id="reference-help" class="pay-help">Use the transaction reference from your payment receipt.</p>@error('reference')<p id="reference-error" class="pay-error">{{ $message }}</p>@enderror</div>
                        <div class="pay-field"><label for="payment-receipt">Receipt image</label><input id="payment-receipt" name="receipt" type="file" accept="image/jpeg,image/png,image/webp" required aria-describedby="receipt-help @error('receipt') receipt-error @enderror" @error('receipt') aria-invalid="true" @enderror><p id="receipt-help" class="pay-help">JPEG, PNG, or WebP · up to 5 MiB. Upload a clear image of the receipt. @if ($payment->receipt_path)Choose a new file to replace the previous receipt.@endif</p>@error('receipt')<p id="receipt-error" class="pay-error">{{ $message }}</p>@enderror</div>
                        <div><button type="submit" class="pay-button pay-button--primary"><x-lucide-upload aria-hidden="true" />Submit for Admin review</button></div>
                    </form>
                    <p class="pay-note">Submission sends evidence to ShopHop Admin. Seller fulfillment stays on hold until Admin verifies it.</p>
                @else
                    {{-- Pending and terminal states expose no proof mutation controls. --}}
                    <a class="pay-button" href="{{ route('buyer.orders') }}">View My Orders<x-lucide-arrow-right aria-hidden="true" /></a>
                @endif
            </section>
            {{-- Receipt access continues through the private authenticated endpoint. --}}
            <section class="pay-panel pay-pad" aria-labelledby="receipt-heading">
                <div class="pay-section-head"><span class="pay-icon"><x-lucide-file-image aria-hidden="true" /></span><h2 id="receipt-heading">{{ $payment->receipt_path ? 'Submitted evidence' : 'Your receipt' }}</h2></div>
                @if ($payment->receipt_path)
                    <dl class="pay-facts"><div><dt>Payment reference</dt><dd>{{ $payment->reference ?: 'Not submitted' }}</dd></div><div><dt>Submitted</dt><dd>{{ $payment->submitted_at?->format('M j, Y g:i A T') ?? 'Submission time unavailable' }}</dd></div></dl>
                    <div class="pay-actions"><a class="pay-button" href="{{ route('payments.receipt', $payment) }}">View submitted receipt<x-lucide-arrow-up-right aria-hidden="true" /></a></div><p class="pay-note">Receipt access is private to the owning Buyer and ShopHop Admin.</p>
                @else
                    <p class="pay-muted">No receipt has been submitted for this payment.</p>
                @endif
            </section>
        </div>
        {{-- Amount language stays accurate after closure; money still comes from persisted group totals. --}}
        <aside class="pay-stack pay-summary" aria-label="Payment summary">
            <section class="pay-panel pay-pad">
                <h2>{{ in_array($payment->status, ['awaiting_proof', 'rejected'], true) ? 'Amount due' : 'Checkout payment amount' }}</h2><div class="pay-amount">₱{{ number_format((float) $payment->expectedAmount(), 2) }}</div><p class="pay-muted pay-small">Total of the Seller Orders linked to this payment.</p>
                {{-- Keep the persisted exact deadline next to the amount, before long reasons or Seller names on mobile. --}}
                @if ($payment->expires_at && in_array($payment->status, ['awaiting_proof', 'rejected'], true))
                    <div class="pay-deadline"><x-lucide-clock aria-hidden="true" /><div><strong>Submit proof by {{ $payment->expires_at->format('M j, Y g:i A T') }}</strong><p class="pay-small pay-muted">Without proof, this payment and its linked Orders expire.</p></div></div>
                @endif
                <div class="pay-divider pay-small"><strong>Online Payment</strong><p class="pay-muted">Manual proof review by ShopHop Admin.</p></div>
            </section>
            @include('payments.partials.orders')
        </aside>
    </div>
    @if (in_array($payment->status, ['awaiting_proof', 'pending_review', 'rejected'], true))@include('payments.partials.cancel', ['admin' => false])@endif
</div></div>
@endsection
