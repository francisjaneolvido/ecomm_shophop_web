@extends('admin.layout')
@section('title', 'Payment Review')
@section('content')
{{-- Admin reviews Buyer proof and all linked Seller Orders within the existing protected shell. --}}
<main class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow">
    <a class="text-teal underline" href="{{ route('admin.payments.index') }}">Pending Payments</a>
    <h1 class="mt-4 text-2xl font-bold">Payment #{{ $payment->id }}</h1>
    {{-- Review amount and ownership come from the payment's persisted Buyer and Seller Orders. --}}
    <dl class="mt-4 space-y-2"><div><dt class="font-semibold">Buyer</dt><dd>{{ $payment->buyer?->first_name }} {{ $payment->buyer?->last_name }}</dd></div>
        <div><dt class="font-semibold">Method</dt><dd>Online Payment · manual proof review</dd></div>
        <div><dt class="font-semibold">Status</dt><dd>{{ str_replace('_', ' ', ucfirst($payment->status)) }}</dd></div>
        <div><dt class="font-semibold">Reference</dt><dd>{{ $payment->reference ?: 'Not submitted' }}</dd></div>
        <div><dt class="font-semibold">Submitted</dt><dd>{{ $payment->submitted_at?->format('M j, Y g:i A') ?? 'Not submitted' }}</dd></div>
        <div><dt class="font-semibold">Expected group amount</dt><dd>₱{{ number_format((float) $payment->expectedAmount(), 2) }}</dd></div></dl>
    <h2 class="mt-5 font-bold">Seller Orders</h2>
    <ul class="mt-2 divide-y border-y">@foreach ($payment->orders as $order)
        <li class="flex justify-between py-2"><span>#{{ $order->id }} · {{ $order->seller?->business_name ?? 'Shop' }} · {{ $order->status }}</span><span>₱{{ number_format((float) $order->total_amount, 2) }}</span></li>
    @endforeach</ul>
    @if ($payment->receipt_path)<a class="mt-4 inline-block text-teal underline" href="{{ route('payments.receipt', $payment) }}">View private receipt</a>@endif
    @if ($payment->reviewed_at)<p class="mt-3">Decision: {{ $payment->decision }} by {{ $payment->reviewer?->email ?? 'Admin' }} at {{ $payment->reviewed_at->format('M j, Y g:i A') }}</p>@endif
    @if ($payment->status === 'rejected' && $payment->rejection_reason)<p class="mt-2">Reason: {{ $payment->rejection_reason }}</p>@endif
    @if (session('status'))<p role="status" class="mt-3 text-teal">{{ session('status') }}</p>@endif
    @if ($errors->any())<p role="alert" class="mt-3 text-red-700">{{ $errors->first() }}</p>@endif
    @if ($payment->status === 'pending_review')
        {{-- Server-side locks and group checks still decide whether a stale page may verify or reject. --}}
        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="mt-6">@csrf<button class="rounded bg-teal px-5 py-2 text-white">Verify by ShopHop Admin</button></form>
        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="mt-4 space-y-2">@csrf
            <label class="block">Rejection reason<textarea name="reason" required maxlength="1000" class="mt-1 block w-full rounded border p-2"></textarea></label>
            <button class="rounded bg-red-700 px-5 py-2 text-white">Reject proof</button>
        </form>
    @endif
</main>
@endsection
