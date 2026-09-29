@extends('layouts.app')
@section('title', 'Online Payment — ShopHop')
@section('hideChrome', true)
@section('content')
{{-- Buyer payment proof uses the existing marketplace theme and authenticated Buyer navigation. --}}
@include('buyer.partials.navbar-buyer')
<main class="min-h-screen bg-gray-50 p-6 text-navy"><div class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow">
    <a class="text-teal underline" href="{{ route('buyer.orders') }}">My Orders</a>
    <h1 class="mt-4 text-2xl font-bold">Online Payment #{{ $payment->id }}</h1>
    {{-- A payment covers the Buyer-owned checkout group; the amount is summed from persisted Seller Orders. --}}
    <p class="mt-2">Expected amount: <strong>₱{{ number_format((float) $payment->expectedAmount(), 2) }}</strong></p>
    <ul class="mt-3 divide-y border-y">
        @foreach ($payment->orders as $order)
            {{-- Each Seller Order shows its persisted terminal state after group closure. --}}
            <li class="flex justify-between py-2"><span>Order #{{ $order->id }} · {{ $order->seller?->business_name ?? 'Shop' }} · {{ $order->statusLabel() }}</span><span>₱{{ number_format((float) $order->total_amount, 2) }}</span></li>
        @endforeach
    </ul>
    {{-- ShopHop Admin reviews submitted evidence; no bank, wallet, or provider confirmation is claimed. --}}
    <p class="mt-4 font-semibold">{{ match ($payment->status) {
        'awaiting_proof' => 'Awaiting Payment Proof',
        'pending_review' => 'Pending Payment Verification',
        'rejected' => 'Payment Rejected / Resubmission Required',
        'verified' => 'Verified by ShopHop Admin',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
        default => 'Payment status unavailable',
    } }}</p>
    @if ($payment->status === 'rejected' && $payment->rejection_reason)
        <p class="mt-2 text-red-700">Reason: {{ $payment->rejection_reason }}</p>
    @endif
    {{-- The persisted deadline is paused during review and removed after a terminal decision. --}}
    @if ($payment->expires_at && in_array($payment->status, ['awaiting_proof', 'rejected'], true))
        <p class="mt-2 text-sm">Submit proof by {{ $payment->expires_at->format('M j, Y g:i A') }}.</p>
    @endif
    @if (in_array($payment->status, ['cancelled', 'expired'], true))
        <p class="mt-2 text-sm">All Seller Orders in this checkout group are closed.</p>
    @endif
    @if (session('status')) <p role="status" class="mt-3 text-teal">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p role="alert" class="mt-3 text-red-700">{{ $errors->first() }}</p> @endif
    @if ($payment->receipt_path)
        <a class="mt-3 inline-block text-teal underline" href="{{ route('payments.receipt', $payment) }}">View submitted receipt</a>
    @endif
    @if (in_array($payment->status, ['awaiting_proof', 'rejected'], true))
        {{-- Payment occurs externally; this form records evidence for ShopHop Admin review. --}}
        <p class="mt-5 text-sm text-navy/70">Complete your payment using the agreed external payment method, then submit the transaction reference and receipt below for ShopHop Admin review.</p>
        {{-- Resubmission changes only the reference and private receipt on this same payment. --}}
        <form method="POST" enctype="multipart/form-data" action="{{ route('buyer.payments.submit', $payment) }}" class="mt-6 space-y-4">@csrf
            <label class="block">Payment reference<input name="reference" value="{{ old('reference', $payment->reference) }}" maxlength="120" required class="mt-1 block w-full rounded border p-2"></label>
            <label class="block">Receipt image (JPEG, PNG, or WebP; up to 5 MiB)<input name="receipt" type="file" accept="image/jpeg,image/png,image/webp" required class="mt-1 block w-full"></label>
            <button class="rounded bg-teal px-5 py-2 text-white">Submit for Admin review</button>
        </form>
    @endif
    @if (in_array($payment->status, ['awaiting_proof', 'pending_review', 'rejected'], true))
        {{-- One confirmation closes every Seller Order in the checkout group and cannot be repeated. --}}
        <form method="POST" action="{{ route('buyer.payments.cancel', $payment) }}" class="mt-6" onsubmit="return confirm('Cancel all Seller Orders in this checkout group? This cannot be undone.');">@csrf
            <p class="mb-2 text-sm">Cancel Order closes all Seller Orders in this checkout group.</p>
            <button class="rounded border border-red-700 px-5 py-2 text-red-700">Cancel Order</button>
        </form>
    @endif
</div></main>
@endsection
