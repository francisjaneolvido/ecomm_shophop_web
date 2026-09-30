@extends('admin.layout')
@section('title', 'Payment Review')
@include('payments.partials.styles')
@section('content')
{{-- Queue uses only the real pending collection and controller ordering inside the unchanged Admin shell. --}}
<div class="payment-ui pay-container">
    <nav aria-label="Payment navigation"><a class="pay-back" href="{{ route('admin.dashboard') }}"><x-lucide-arrow-left aria-hidden="true" />Admin Dashboard</a></nav>
    <header class="pay-header"><div><h1>Pending Payment Verification</h1><p>Review Buyer-submitted proof before releasing Seller fulfillment.</p></div></header>
    <section class="pay-panel" aria-label="Pending payment review queue">
        @if ($payments->isNotEmpty())
            {{-- Responsive labels preserve identity, reference, amount and time without inventing queue metrics. --}}
            <table class="pay-queue" role="table"><thead><tr><th scope="col">Buyer</th><th scope="col">Payment / reference</th><th scope="col">Expected amount</th><th scope="col">Submitted</th><th scope="col"><span class="sr-only">Action</span></th></tr></thead><tbody>
            @foreach ($payments as $payment)
                <tr><td data-label="Buyer"><strong>{{ trim(($payment->buyer?->first_name ?? '').' '.($payment->buyer?->last_name ?? '')) ?: 'Buyer unavailable' }}</strong></td><td data-label="Payment / reference"><strong>Payment #{{ $payment->id }}</strong><div class="pay-muted pay-small">{{ $payment->reference ?: 'Reference unavailable' }}</div></td><td data-label="Expected amount" class="pay-money">₱{{ number_format((float) $payment->expectedAmount(), 2) }}</td><td data-label="Submitted"><div class="pay-small">{{ $payment->submitted_at?->format('M j, Y g:i A T') ?? 'Time unavailable' }}</div><div class="pay-muted pay-small">Pending review</div></td><td><a class="pay-button" aria-label="Review payment #{{ $payment->id }}" href="{{ route('admin.payments.show', $payment) }}">Review<x-lucide-arrow-right aria-hidden="true" /></a></td></tr>
            @endforeach
            </tbody></table>
        @else
            {{-- Empty means no pending proof, never that all payments are settled. --}}
            <div class="pay-empty"><span class="pay-icon"><x-lucide-clipboard-check aria-hidden="true" /></span><h2>No pending payments</h2><p>There are no submitted proofs awaiting review. New Buyer submissions will appear here when they are ready for your decision.</p></div>
        @endif
    </section>
</div>
@endsection
