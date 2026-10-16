@extends('seller.partials.layout')
@section('title', 'Seller Earnings')
@section('content')
<div class="space-y-4"><h1 class="text-2xl font-bold">Seller Earnings</h1>
<p class="text-sm">10% commission applies to merchandise after Seller discount, excluding shipping. These records are generated after Admin confirms completed, eligible orders. Not all completed Orders are released yet.</p>
<div class="grid grid-cols-2 gap-3"><div class="p-5 border rounded">Pending manual payout: <strong>₱{{ number_format((float)$pending,2) }}</strong></div><div class="p-5 border rounded">Recorded as paid: <strong>₱{{ number_format((float)$paid,2) }}</strong></div></div>
<table class="w-full text-sm"><thead><tr><th>Order</th><th>Amount</th><th>Status</th><th>Manual reference</th></tr></thead><tbody>@forelse($entries as $entry)
<tr class="border-t"><td>#{{ $entry->order_id }}</td><td>₱{{ $entry->amount }}</td><td>{{ $entry->status }}</td><td>{{ $entry->payment_reference ?? '—' }}</td></tr>
@empty<tr><td colspan="4" class="p-5">No eligible earnings released by Admin yet.</td></tr>@endforelse</tbody></table>{{ $entries->links() }}
</div>
@endsection
