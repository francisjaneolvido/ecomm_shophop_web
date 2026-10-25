@extends('seller.partials.layout')
@section('title', 'Seller Earnings')
@section('content')
<div class="space-y-4"><h1 class="text-2xl font-bold">Seller Earnings</h1>
<p class="text-sm">ShopHop takes 10% of merchandise after Seller-funded merchandise discounts; shipping is excluded from commission. Seller-funded shipping vouchers are deducted from the Seller payout, while ShopHop-funded vouchers do not reduce Seller earnings. Records are released only after the order is eligible for settlement.</p>
<div class="grid grid-cols-1 gap-3 md:grid-cols-3"><div class="p-5 border rounded">Pending distribution: <strong>₱{{ number_format((float)$pending,2) }}</strong></div><div class="p-5 border rounded border-emerald-200 bg-emerald-50">Available for payout: <strong>₱{{ number_format((float)$available,2) }}</strong></div><div class="p-5 border rounded">Recorded as paid: <strong>₱{{ number_format((float)$paid,2) }}</strong></div></div>
<table class="w-full text-sm"><thead><tr><th>Order</th><th>Amount</th><th>Status</th><th>Distribution / payout reference</th></tr></thead><tbody>@forelse($entries as $entry)
<tr class="border-t"><td>#{{ $entry->order_id }}</td><td>₱{{ $entry->amount }}</td><td>{{ $entry->status }}</td><td>{{ $entry->payment_reference ?? $entry->distribution_reference ?? '—' }}</td></tr>
@empty<tr><td colspan="4" class="p-5">No Seller earnings prepared yet.</td></tr>@endforelse</tbody></table>{{ $entries->links() }}
</div>
@endsection
