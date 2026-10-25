@extends('admin.layout')
@section('title', 'ShopHop Finances')
@section('content')
<div class="mb-4 rounded-xl border border-slate-200 bg-white p-4"><a class="text-sm font-semibold text-emerald-700 underline" href="{{ route('admin.finance.cod.index') }}">COD cash remittance verification →</a><p class="mt-1 text-xs text-slate-500">Verify actual Logistics-to-ShopHop cash receipt before releasing Seller/Logistics payouts.</p></div>
<div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4"><a href="{{route('admin.returns.index')}}" class="text-sm font-bold text-amber-900 underline">Review Return-to-Seller & Manual Refunds →</a><p class="text-xs text-amber-800 mt-1">No refund is automatic; Seller must confirm physical receipt first.</p></div>
<div class="space-y-6">
  <div><h1 class="text-2xl font-bold text-navy">ShopHop Finances (Demo)</h1>
    <p class="text-sm text-slate-500">10% of merchandise after Seller voucher; platform discounts are funded by ShopHop. A recorded payout is NOT an automatic money transfer.</p></div>
  @if(session('status')) <div class="p-3 rounded bg-teal-50 text-teal-800">{{ session('status') }}</div> @endif
  @if($errors->any()) <div class="p-3 rounded bg-red-50 text-red-800"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <section class="p-5 bg-white rounded-xl border"><h2 class="font-bold mb-3">ShopHop demo payment QR (Admin managed)</h2>
    <form method="POST" action="{{ route('admin.finance.qr') }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-2">@csrf
      <label>Displayed recipient name<input required name="recipient_name" maxlength="160" value="{{ old('recipient_name', $setting?->recipient_name ?? 'ShopHop Demo') }}" class="w-full border p-2 rounded"></label>
      <label>Demo account / reference label<input name="recipient_identifier" maxlength="100" value="{{ old('recipient_identifier', $setting?->recipient_identifier) }}" class="w-full border p-2 rounded"></label>
      <label>QR image (PNG/JPEG/WebP, 2 MB)<input type="file" accept="image/png,image/jpeg,image/webp" name="qr_image" class="w-full border p-2 rounded"></label>
      <label>Instructions<textarea name="instructions" rows="2" maxlength="1000" class="w-full border p-2 rounded">{{ old('instructions', $setting?->instructions ?? 'DEMO ONLY: No actual electronic payment will occur.') }}</textarea></label>
      <div><button class="bg-teal-600 text-white px-4 py-2 rounded">Save demo QR</button></div>
    </form>
    @if($setting?->qrUrl()) <img src="{{ $setting->qrUrl() }}" alt="Admin uploaded demo payment QR" class="w-40 h-40 object-contain mt-3 border rounded"> @endif
    <p class="text-xs text-amber-700 mt-2">Do not put a real wallet QR in a public school demo unless you intentionally intend to receive actual transfers.</p>
  </section>
  <section class="p-5 bg-white rounded-xl border">
    <div class="flex items-start justify-between gap-3"><div><h2 class="font-bold">Marketplace shipping & payout rates</h2><p class="text-xs text-slate-500 mt-1">Gross shipping = base + route distance band + linehaul component + excess weight. Express adds a percentage surcharge. ShopHop commission is locked at 10%.</p></div><span class="text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 px-3 py-1">ShopHop 10%</span></div>
    <form method="POST" action="{{ route('admin.finance.rates') }}" class="mt-4 grid gap-3 md:grid-cols-3 xl:grid-cols-4">@csrf
      <label class="text-xs">Commission %<input name="commission_percent" value="10" readonly class="mt-1 w-full border p-2 rounded bg-slate-50"></label>
      <label class="text-xs">Base shipping<input name="base_shipping_fee" type="number" step="0.01" min="0" value="{{ old('base_shipping_fee',$rates->base_shipping_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Local distance band<input name="local_distance_fee" type="number" step="0.01" min="0" value="{{ old('local_distance_fee',$rates->local_distance_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Same-province distance band<input name="same_province_distance_fee" type="number" step="0.01" min="0" value="{{ old('same_province_distance_fee',$rates->same_province_distance_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Inter-province distance band<input name="interprovince_distance_fee" type="number" step="0.01" min="0" value="{{ old('interprovince_distance_fee',$rates->interprovince_distance_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Same-province linehaul<input name="same_province_linehaul_fee" type="number" step="0.01" min="0" value="{{ old('same_province_linehaul_fee',$rates->same_province_linehaul_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Inter-province linehaul<input name="interprovince_linehaul_fee" type="number" step="0.01" min="0" value="{{ old('interprovince_linehaul_fee',$rates->interprovince_linehaul_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Included weight (kg)<input name="included_weight_kg" type="number" step="0.001" min="0.05" value="{{ old('included_weight_kg',$rates->included_weight_kg) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Extra weight / kg<input name="extra_weight_fee_per_kg" type="number" step="0.01" min="0" value="{{ old('extra_weight_fee_per_kg',$rates->extra_weight_fee_per_kg) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Express surcharge %<input name="express_surcharge_percent" type="number" step="0.01" min="0" value="{{ old('express_surcharge_percent',$rates->express_surcharge_percent) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Pickup Rider earning<input name="pickup_rider_fee" type="number" step="0.01" min="0" value="{{ old('pickup_rider_fee',$rates->pickup_rider_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Delivery Rider earning<input name="delivery_rider_fee" type="number" step="0.01" min="0" value="{{ old('delivery_rider_fee',$rates->delivery_rider_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Origin Branch allocation<input name="origin_branch_fee" type="number" step="0.01" min="0" value="{{ old('origin_branch_fee',$rates->origin_branch_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Destination Branch allocation<input name="destination_branch_fee" type="number" step="0.01" min="0" value="{{ old('destination_branch_fee',$rates->destination_branch_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Province Hub / parcel<input name="province_hub_fee" type="number" step="0.01" min="0" value="{{ old('province_hub_fee',$rates->province_hub_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Driver: Branch ↔ Hub / trip<input name="linehaul_branch_hub_driver_fee" type="number" step="0.01" min="0" value="{{ old('linehaul_branch_hub_driver_fee',$rates->linehaul_branch_hub_driver_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <label class="text-xs">Driver: Hub ↔ Hub / trip<input name="linehaul_hub_hub_driver_fee" type="number" step="0.01" min="0" value="{{ old('linehaul_hub_hub_driver_fee',$rates->linehaul_hub_hub_driver_fee) }}" class="mt-1 w-full border p-2 rounded"></label>
      <div class="md:col-span-3 xl:col-span-4"><button class="bg-teal-600 text-white px-4 py-2 rounded">Save rates</button></div>
    </form>
  </section>
  <section class="p-5 bg-white rounded-xl border"><h2 class="font-bold mb-3">ShopHop-funded vouchers</h2>
    <p class="text-xs mb-3">Platform merchandise vouchers and free-shipping vouchers are separate from Seller vouchers.</p>
    <form method="POST" action="{{ route('admin.finance.vouchers.create') }}" class="grid grid-cols-2 md:grid-cols-4 gap-2">@csrf
      <input name="code" placeholder="CODE" required maxlength="40" class="border p-2 rounded">
      <input name="name" placeholder="Voucher name" required maxlength="160" class="border p-2 rounded">
      <select name="kind" class="border p-2 rounded"><option value="merchandise">Platform merchandise</option><option value="shipping">Free shipping</option></select>
      <select name="type" class="border p-2 rounded"><option value="fixed">Fixed peso</option><option value="percent">Percentage</option></select>
      <label>Value<input name="value" type="number" min="0.01" step="0.01" required class="w-full border p-2 rounded"></label>
      <label>Minimum merchandise spend<input name="min_spend" type="number" min="0" step="0.01" value="0" required class="w-full border p-2 rounded"></label>
      <label>Usage limit (optional)<input name="usage_limit" type="number" min="1" class="w-full border p-2 rounded"></label>
      <label>Expires (optional)<input name="ends_at" type="datetime-local" class="w-full border p-2 rounded"></label>
      <button class="bg-teal-600 text-white px-4 py-2 rounded">Create platform voucher</button>
    </form>
    <div class="overflow-x-auto mt-3"><table class="w-full text-xs text-left"><thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Used</th><th>Active</th><th></th></tr></thead><tbody>
      @foreach($vouchers as $v)<tr class="border-t"><td>{{ $v->code }}</td><td>{{ $v->kind }}</td><td>{{ $v->type === 'fixed' ? '₱' : '' }}{{ $v->value }}{{ $v->type === 'percent' ? '%' : '' }}</td><td>{{ $v->used_count }} / {{ $v->usage_limit ?? '∞' }}</td><td>{{ $v->active ? 'Yes' : 'No' }}</td><td><form method="POST" action="{{ route('admin.finance.vouchers.toggle', $v) }}">@csrf<button class="underline">{{ $v->active ? 'Disable' : 'Enable' }}</button></form></td></tr>@endforeach
    </tbody></table></div>
  </section>
  <section class="p-5 bg-white rounded-xl border"><h2 class="font-bold">Completed Orders awaiting settlement preparation</h2>
    <p class="text-xs text-slate-500 my-2">For COD, the full collected amount must already be received and verified by ShopHop Admin; the parcel must be delivered and have no settlement hold. Historical completed orders without a finance snapshot can be reconstructed from their frozen Order/OrderItem checkout amounts; unsafe mismatches are blocked for manual review.</p>
    <div class="flex flex-wrap gap-2">@forelse($eligibleOrders as $o)
      <form method="POST" action="{{ route('admin.finance.prepare', $o->id) }}">@csrf<button class="border rounded px-3 py-2 text-xs">Prepare #{{ $o->id }}{{ $o->financial ? '' : ' · historical' }}</button></form>
      @empty <span class="text-xs text-slate-500">No new eligible candidates</span> @endforelse</div>
  </section>
  <section id="distribution-queue" class="p-5 bg-white rounded-xl border">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div><h2 class="font-bold">Settlement distribution queue</h2><p class="mt-1 text-xs text-slate-500">Prepared = computed but still held by ShopHop. Distribute moves beneficiary ledgers from Pending to Available; it does not claim an external bank/e-wallet transfer occurred.</p></div>
      <div class="grid grid-cols-3 gap-2 text-center text-xs">
        <div class="rounded-xl bg-amber-50 px-3 py-2"><div class="text-slate-500">Pending</div><strong>₱{{ number_format($pendingDistributionTotal,2) }}</strong></div>
        <div class="rounded-xl bg-emerald-50 px-3 py-2"><div class="text-slate-500">Available</div><strong>₱{{ number_format($availablePayoutTotal,2) }}</strong></div>
        <div class="rounded-xl bg-slate-50 px-3 py-2"><div class="text-slate-500">Paid</div><strong>₱{{ number_format($paidPayoutTotal,2) }}</strong></div>
      </div>
    </div>
    <div class="mt-4 space-y-3">
      @forelse($distributionOrders as $o)
        @php
          $sellerEntry = $o->settlements->firstWhere('kind','seller');
          $logisticsEntry = $o->settlements->firstWhere('kind','logistics');
          $riderTotal = (float)$o->settlements->whereIn('kind',['pickup_rider','delivery_rider'])->sum('amount');
          $financial = $o->financial;
        @endphp
        <div class="rounded-2xl border border-slate-200 p-4">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <div class="font-semibold text-navy">Order #{{ $o->id }} · READY FOR DISTRIBUTION</div>
              <div class="mt-1 text-xs text-slate-500">Seller ₱{{ number_format((float)($sellerEntry?->amount ?? 0),2) }} · Logistics ₱{{ number_format((float)($logisticsEntry?->amount ?? 0),2) }} · Rider sub-allocations ₱{{ number_format($riderTotal,2) }}</div>
              @if($financial)<div class="mt-1 text-xs text-slate-500">ShopHop commission ₱{{ number_format((float)$financial->commission,2) }} · ShopHop net after platform promos ₱{{ number_format((float)$financial->platform_net,2) }}</div>@endif
            </div>
            <form method="POST" action="{{ route('admin.finance.distribute',$o->id) }}" onsubmit="return confirm('Distribute this prepared settlement to beneficiary ledgers? This changes Pending to Available but does not move external money.')">@csrf
              <button class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white">Distribute settlement</button>
            </form>
          </div>
        </div>
      @empty
        <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No prepared orders are waiting for distribution.</div>
      @endforelse
    </div>
  </section>
  <section id="available-payouts" class="p-5 bg-white rounded-xl border">
    <h2 class="font-bold">Available Seller / Logistics payouts</h2>
    <p class="mt-1 text-xs text-slate-500">Available means Admin already distributed the ledger amount. Record Paid only after the actual/manual payout is verified.</p>
    <div class="mt-3 overflow-x-auto"><table class="w-full text-left text-xs"><thead><tr><th>Order</th><th>Payee</th><th>Amount</th><th>Distributed</th><th>Actual payout</th></tr></thead><tbody>
      @forelse($availablePayouts as $entry)<tr class="border-t"><td>#{{ $entry->order_id }}</td><td>{{ ucfirst($entry->kind) }} #{{ $entry->payee_id }}</td><td>₱{{ number_format((float)$entry->amount,2) }}</td><td>{{ $entry->distributed_at?->format('M j, g:i A') ?? '—' }}<br><span class="text-[10px] text-slate-400">{{ $entry->distribution_reference }}</span></td><td><form method="POST" action="{{ route('admin.finance.paid',$entry->id) }}" class="flex gap-1">@csrf<input name="reference" required maxlength="120" placeholder="Payout reference" class="border rounded px-2 py-1"><button class="underline font-semibold">Mark paid</button></form></td></tr>
      @empty<tr><td colspan="5" class="py-4 text-slate-500">No distributed payouts awaiting payment.</td></tr>@endforelse
    </tbody></table></div>
  </section>
  <section class="p-5 bg-white rounded-xl border"><h2 class="font-bold">Order financial snapshots</h2>
    <p class="text-xs mb-2">Snapshot amounts are fixed at checkout. Commission from completed orders: ₱{{ number_format($commissionTotal,2) }} (not a cash balance).</p>
    <div class="overflow-x-auto"><table class="text-xs w-full text-left"><thead><tr><th>Order</th><th>Merchandise</th><th>Seller merch.</th><th>Seller shipping</th><th>ShopHop discounts</th><th>Gross shipping</th><th>Route / weight</th><th>Commission</th><th>Seller net</th><th>ShopHop net</th><th>Hold</th></tr></thead><tbody>
    @foreach($finances as $f)<tr class="border-t"><td>#{{ $f->order_id }}</td><td>₱{{ $f->merchandise }}</td><td>₱{{ $f->seller_discount }}</td><td>₱{{ number_format((float)$f->seller_shipping_discount,2) }}</td><td>₱{{ number_format((float)$f->platform_discount + (float)$f->shipping_discount,2) }}</td><td>₱{{ $f->logistics_earnings }}</td><td>{{ $f->shipping_route_band ?? '—' }} · {{ number_format((float)$f->shipping_weight_kg,3) }}kg</td><td>₱{{ $f->commission }}</td><td>₱{{ $f->seller_earnings }}</td><td>₱{{ $f->platform_net }}</td><td>
       <form method="POST" action="{{ route('admin.finance.hold', $f->order_id) }}">@csrf<input type="hidden" name="hold" value="{{ $f->settlement_hold ? 0 : 1 }}"><input name="reason" placeholder="reason" maxlength="500" class="border px-1 w-28"><button class="underline">{{ $f->settlement_hold ? 'Release hold' : 'Hold' }}</button></form></td></tr>@endforeach
    </tbody></table></div>{{ $finances->links() }}
  </section>
  <section class="p-5 bg-white rounded-xl border"><h2 class="font-bold mb-2">Settlement payout history</h2>
    <p class="text-xs text-slate-500 mb-3">Pending = prepared but not distributed. Available = distributed/credited. Paid = actual/manual payout recorded.</p>
    <div class="overflow-x-auto"><table class="text-xs w-full text-left"><thead><tr><th>Order</th><th>Payee</th><th>Amount</th><th>Status</th><th>Distribution / payout reference</th></tr></thead><tbody>
     @foreach($settlements as $entry)@if(in_array($entry->kind, ['seller','logistics']))<tr class="border-t"><td>#{{ $entry->order_id }}</td><td>{{ ucfirst($entry->kind) }} #{{ $entry->payee_id }}</td><td>₱{{ number_format((float)$entry->amount,2) }}</td><td class="font-semibold">{{ strtoupper($entry->status) }}</td><td>{{ $entry->payment_reference ?? $entry->distribution_reference ?? '—' }}</td></tr>@endif @endforeach
    </tbody></table></div>{{ $settlements->links() }}
  </section>
</div>
@endsection
