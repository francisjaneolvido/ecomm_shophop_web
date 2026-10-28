@extends('logistics.layouts.console')
@section('title', 'Logistics Earnings')
@section('content')
<div class="space-y-5">
  <header class="sh-page-header"><div><p class="eyebrow">Finance · Earnings & distribution</p><h1 class="title">Logistics Earnings & Distribution</h1><p class="description">ShopHop settles the gross shipping entitlement to the Logistics company. Rider fees, Branch/Hub allocations and Linehaul Driver earnings are tracked separately from Seller merchandise money.</p></div><a class="sh-btn-soft" href="{{ route('logistics.settlements.index') }}">COD settlements →</a></header>
  @if(session('status'))<div class="p-3 border rounded">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="p-3 text-red-600">{{ $errors->first() }}</div>@endif

  <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 sh-ui-money-grid">
    <div class="sh-surface sh-card-pad"><div class="text-xs opacity-60">ShopHop → Logistics pending</div><strong>₱{{ number_format((float)$pending,2) }}</strong></div>
    <div class="sh-surface sh-card-pad"><div class="text-xs opacity-60">Logistics available</div><strong>₱{{ number_format((float)$available,2) }}</strong></div>
    <div class="sh-surface sh-card-pad"><div class="text-xs opacity-60">Logistics paid</div><strong>₱{{ number_format((float)$paid,2) }}</strong></div>
    <div class="sh-surface sh-card-pad"><div class="text-xs opacity-60">Linehaul pending</div><strong>₱{{ number_format((float)$linehaulPending,2) }}</strong></div>
    <div class="sh-surface sh-card-pad"><div class="text-xs opacity-60">Linehaul available</div><strong>₱{{ number_format((float)$linehaulAvailable,2) }}</strong></div>
    <div class="sh-surface sh-card-pad"><div class="text-xs opacity-60">Linehaul paid</div><strong>₱{{ number_format((float)$linehaulPaid,2) }}</strong></div>
  </div>

  <section class="sh-surface sh-card-pad">
    <h2 class="font-semibold mb-2">Internal shipping allocation summary</h2>
    <p class="text-xs mb-3">These are accounting allocations from settled/eligible order shipping snapshots. Branch/Hub amounts are reporting ledgers, not withdrawable wallets.</p>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm sh-ui-allocation">
      <div>Gross shipping<br><strong>₱{{ number_format($allocationTotals['gross_shipping'],2) }}</strong></div>
      <div>Pickup Riders<br><strong>₱{{ number_format($allocationTotals['pickup_riders'],2) }}</strong></div>
      <div>Delivery Riders<br><strong>₱{{ number_format($allocationTotals['delivery_riders'],2) }}</strong></div>
      <div>Branches<br><strong>₱{{ number_format($allocationTotals['branches'],2) }}</strong></div>
      <div>Province Hubs<br><strong>₱{{ number_format($allocationTotals['hubs'],2) }}</strong></div>
      <div>Linehaul pool<br><strong>₱{{ number_format($allocationTotals['linehaul_pool'],2) }}</strong></div>
      <div>Logistics margin<br><strong>₱{{ number_format($allocationTotals['margin'],2) }}</strong></div>
    </div>
  </section>

  <section class="sh-surface sh-card-pad"><h2 class="font-semibold mb-3">Admin-to-Logistics settlements</h2><div class="overflow-x-auto"><table class="sh-table"><thead><tr><th>Order</th><th>Gross Shipping</th><th>Status</th><th>Reference</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>#{{ $entry->order_id }}</td><td>₱{{ $entry->amount }}</td><td>{{ $entry->status }}</td><td>{{ $entry->payment_reference ?? $entry->distribution_reference ?? '—' }}</td></tr>@empty<tr><td colspan="4"><div class="py-3"><strong>No ShopHop settlement entries yet.</strong><div class="mt-1 text-xs text-slate-500">A COD record can already be reconciled/received while the order settlement is still unprepared. ShopHop Admin must Prepare, then Distribute the settlement before it appears as Pending/Available here.</div></div></td></tr>@endforelse</tbody></table></div>{{ $entries->links() }}</section>

  <section class="sh-surface sh-card-pad">
    <h2 class="font-semibold mb-2">Branch / Province Hub revenue ledger</h2>
    <p class="text-xs mb-3">Operational attribution only. It shows which center handled the parcel and its configured allocation.</p>
    <div class="overflow-x-auto"><table class="sh-table"><thead><tr><th>Center</th><th>Type</th><th>Handled Orders</th><th>Scans</th><th>Allocated Revenue</th></tr></thead><tbody>
    @forelse($branchActivity as $branch)
    <tr><td>{{ $branch['name'] }} ({{ $branch['code'] }})</td><td>{{ str_replace('_',' ', $branch['type'] ?? 'station') }}</td><td>{{ $branch['handled_orders'] }}</td><td>{{ $branch['scans'] }}</td><td>₱{{ number_format($branch['allocated_revenue'],2) }}</td></tr>
    @empty<tr><td colspan="5">No centers configured yet.</td></tr>@endforelse
    </tbody></table></div>
  </section>

  <section class="sh-surface sh-card-pad"><h2 class="font-semibold mb-3">Pickup / Delivery Rider payouts</h2><p class="text-xs mb-3">Rider earning is separate from COD cash. COD must be remitted in full; Rider fees are paid from Logistics revenue.</p><div class="overflow-x-auto"><table class="sh-table"><thead><tr><th>Order</th><th>Rider</th><th>Fee</th><th>Status</th><th>Payout action</th></tr></thead><tbody>@forelse($riderEntries as $entry)<tr><td>#{{ $entry->order_id }}</td><td>{{ str_replace('_',' ', $entry->kind) }} #{{ $entry->payee_id }}</td><td>₱{{ $entry->amount }}</td><td>{{ $entry->status }}</td><td>@if($entry->status === 'available')<form method="POST" action="{{ route('logistics.earnings.rider-paid',$entry->id) }}" class="sh-ui-inline-form" data-confirm-title="Record rider payout?" data-confirm-message="Only record payment after the rider fee has actually been transferred. Check the payout reference." data-confirm-subject="Order #{{ $entry->order_id }} · ₱{{ number_format((float)$entry->amount,2) }}" data-confirm-button="Record as paid">@csrf<input required maxlength="120" name="reference" placeholder="Payout reference" class="sh-input"><button class="sh-btn">Record paid</button></form>@else {{ $entry->payment_reference }} @endif</td></tr>@empty<tr><td colspan="5">No Rider earnings prepared</td></tr>@endforelse</tbody></table></div>{{ $riderEntries->links() }}</section>

  <section class="sh-surface sh-card-pad"><h2 class="font-semibold mb-3">Linehaul Driver trip earnings</h2><p class="text-xs mb-3">Created only after the destination Branch/Hub has accepted the Manifest and the trip is completed. Driver earning is per trip, not per parcel.</p><div class="overflow-x-auto"><table class="sh-table"><thead><tr><th>Trip</th><th>Driver</th><th>Route</th><th>Fee</th><th>Status</th><th>Payout action</th></tr></thead><tbody>@forelse($linehaulEntries as $entry)<tr><td>{{ $entry->trip?->code ?? '#'.$entry->linehaul_trip_id }}</td><td>{{ $entry->driver?->name ?? 'Driver #'.$entry->driver_id }}</td><td>{{ $entry->trip?->fromCenter?->name ?? '—' }} → {{ $entry->trip?->toCenter?->name ?? '—' }}</td><td>₱{{ number_format((float)$entry->amount,2) }}</td><td>{{ $entry->status }}</td><td>@if($entry->status === 'available')<form method="POST" action="{{ route('logistics.earnings.linehaul-paid',$entry->id) }}" class="sh-ui-inline-form" data-confirm-title="Record linehaul payout?" data-confirm-message="Confirm the driver payout has been made before recording it as paid." data-confirm-subject="{{ $entry->trip?->code ?? 'Trip #'.$entry->linehaul_trip_id }} · ₱{{ number_format((float)$entry->amount,2) }}" data-confirm-button="Record as paid">@csrf<input required maxlength="120" name="reference" placeholder="Payout reference" class="sh-input"><button class="sh-btn">Record paid</button></form>@else {{ $entry->payment_reference }} @endif</td></tr>@empty<tr><td colspan="6">No completed Linehaul Driver earnings yet.</td></tr>@endforelse</tbody></table></div>{{ $linehaulEntries->links() }}</section>
</div>
@endsection
