@extends('admin.layout')
@section('title', 'Payment Review')
@section('content')
{{-- Admin's existing shell keeps payment review separate from account and Product moderation. --}}
<main class="mx-auto max-w-4xl rounded-2xl bg-white p-6 shadow">
    <a class="text-teal underline" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
    <h1 class="mt-4 text-2xl font-bold">Pending Payment Verification</h1>
    {{-- This list contains Buyer-submitted manual proofs awaiting a ShopHop Admin decision. --}}
    <ul class="mt-5 divide-y">
        @forelse ($payments as $payment)
            <li class="flex flex-wrap justify-between gap-3 py-3"><span>{{ $payment->buyer?->first_name }} {{ $payment->buyer?->last_name }} · {{ $payment->reference }} · ₱{{ number_format((float) $payment->expectedAmount(), 2) }}</span><a class="text-teal underline" href="{{ route('admin.payments.show', $payment) }}">Review</a></li>
        @empty
            <li class="py-3">No pending payments.</li>
        @endforelse
    </ul>
</main>
@endsection
