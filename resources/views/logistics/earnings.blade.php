@extends('logistics.layouts.console')
@section('title', 'Logistics & Rider Earnings')
@section('content')
<div class="space-y-5"><div><h1 class="title">Logistics & Rider Earnings</h1><p class="description">Delivery fee revenue belongs to Logistics. Rider fees come from it; they are not deducted from the Seller's merchandise payment.</p></div>
@if(session('status'))<div class="p-3 border rounded">{{ session('status') }}</div>@endif
@if($errors->any())<div class="p-3 text-red-600">{{ $errors->first() }}</div>@endif
<div class="grid grid-cols-2 gap-3"><div class="sh-surface sh-card-pad">Logistics pending: ₱{{ number_format((float)$pending,2) }}</div><div class="sh-surface sh-card-pad">Logistics recorded paid: ₱{{ number_format((float)$paid,2) }}</div></div>
<section class="sh-surface sh-card-pad"><h2 class="font-semibold mb-3">Admin-to-Logistics manual settlements</h2><div class="overflow-x-auto"><table class="sh-table"><thead><tr><th>Order</th><th>Amount</th><th>Status</th><th>Reference</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>#{{ $entry->order_id }}</td><td>₱{{ $entry->amount }}</td><td>{{ $entry->status }}</td><td>{{ $entry->payment_reference ?? '—' }}</td></tr>@empty<tr><td colspan="4">No entries yet</td></tr>@endforelse</tbody></table></div>{{ $entries->links() }}</section>
<section class="sh-surface sh-card-pad">
  <h2 class="font-semibold mb-2">Branch activity & shipping attribution</h2>
  <p class="text-xs mb-3">Reporting only: full shipping settlement still belongs to the Logistics company. These figures attribute eligible delivery activity to the destination branch; they do not create branch-level payouts.</p>
  <div class="overflow-x-auto"><table class="sh-table"><thead><tr><th>Branch</th><th>Completed Deliveries</th><th>Inbound Scans</th><th>Shipping Attributed</th></tr></thead><tbody>
  @forelse($branchActivity as $branch)
  <tr><td>{{ $branch['name'] }} ({{ $branch['code'] }})</td><td>{{ $branch['completed_orders'] }}</td><td>{{ $branch['scans'] }}</td><td>₱{{ number_format($branch['shipping_attributed'],2) }}</td></tr>
  @empty<tr><td colspan="4">No branches configured yet.</td></tr>@endforelse
  </tbody></table></div>
</section>
<section class="sh-surface sh-card-pad"><h2 class="font-semibold mb-3">Rider payout records</h2><p class="text-xs mb-3">Mark a Rider paid only after Logistics has received its own fee from Admin and confirmed a separate manual Rider payout.</p><div class="overflow-x-auto"><table class="sh-table"><thead><tr><th>Order</th><th>Rider</th><th>Fee</th><th>Status</th><th>Payout action</th></tr></thead><tbody>@forelse($riderEntries as $entry)<tr><td>#{{ $entry->order_id }}</td><td>{{ str_replace('_',' ', $entry->kind) }} #{{ $entry->payee_id }}</td><td>₱{{ $entry->amount }}</td><td>{{ $entry->status }}</td><td>@if($entry->status === 'pending')<form method="POST" action="{{ route('logistics.earnings.rider-paid',$entry->id) }}">@csrf<input required maxlength="120" name="reference" placeholder="Payout reference" class="sh-input"><button class="sh-btn">Record paid</button></form>@else {{ $entry->payment_reference }} @endif</td></tr>@empty<tr><td colspan="5">No Rider earnings prepared</td></tr>@endforelse</tbody></table></div>{{ $riderEntries->links() }}</section>
</div>
@endsection
