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
    <p class="text-xs text-slate-500 my-2">An Order must also have a verified online payment or COD actually received and verified by ShopHop Admin, a delivered parcel, and no settlement hold. Non-eligible attempts show a validation error.</p>
    <div class="flex flex-wrap gap-2">@forelse($eligibleOrders as $o)
      <form method="POST" action="{{ route('admin.finance.prepare', $o->id) }}">@csrf<button class="border rounded px-3 py-2 text-xs">Prepare #{{ $o->id }}</button></form>
      @empty <span class="text-xs text-slate-500">No new eligible candidates</span> @endforelse</div>
  </section>
  <section class="p-5 bg-white rounded-xl border"><h2 class="font-bold">Order financial snapshots</h2>
    <p class="text-xs mb-2">Snapshot amounts are fixed at checkout. Commission from completed orders: ₱{{ number_format($commissionTotal,2) }} (not a cash balance).</p>
    <div class="overflow-x-auto"><table class="text-xs w-full text-left"><thead><tr><th>Order</th><th>Merchandise</th><th>Seller discount</th><th>ShopHop discounts</th><th>Commission</th><th>Seller net</th><th>Logistics</th><th>ShopHop net</th><th>Hold</th></tr></thead><tbody>
    @foreach($finances as $f)<tr class="border-t"><td>#{{ $f->order_id }}</td><td>₱{{ $f->merchandise }}</td><td>₱{{ $f->seller_discount }}</td><td>₱{{ number_format((float)$f->platform_discount + (float)$f->shipping_discount,2) }}</td><td>₱{{ $f->commission }}</td><td>₱{{ $f->seller_earnings }}</td><td>₱{{ $f->logistics_earnings }}</td><td>₱{{ $f->platform_net }}</td><td>
       <form method="POST" action="{{ route('admin.finance.hold', $f->order_id) }}">@csrf<input type="hidden" name="hold" value="{{ $f->settlement_hold ? 0 : 1 }}"><input name="reason" placeholder="reason" maxlength="500" class="border px-1 w-28"><button class="underline">{{ $f->settlement_hold ? 'Release hold' : 'Hold' }}</button></form></td></tr>@endforeach
    </tbody></table></div>{{ $finances->links() }}
  </section>
  <section class="p-5 bg-white rounded-xl border"><h2 class="font-bold mb-2">Manual Seller / Logistics payouts</h2>
    <p class="text-xs text-slate-500 mb-3">Pending means payable, not paid. Record a reference ONLY after a manual demo transfer is confirmed.</p>
    <div class="overflow-x-auto"><table class="text-xs w-full text-left"><thead><tr><th>Order</th><th>Payee</th><th>Amount</th><th>Status</th><th>Reference / Record</th></tr></thead><tbody>
     @foreach($settlements as $entry)@if(in_array($entry->kind, ['seller','logistics']))<tr class="border-t"><td>#{{ $entry->order_id }}</td><td>{{ ucfirst($entry->kind) }} #{{ $entry->payee_id }}</td><td>₱{{ $entry->amount }}</td><td>{{ $entry->status }}</td><td>@if($entry->status === 'pending')<form method="POST" action="{{ route('admin.finance.paid', $entry->id) }}" class="flex gap-1">@csrf<input name="reference" required maxlength="120" placeholder="Demo payout reference" class="border rounded px-2"><button class="underline">Mark paid</button></form>@else {{ $entry->payment_reference }} @endif</td></tr>@endif @endforeach
    </tbody></table></div>{{ $settlements->links() }}
  </section>
</div>
@endsection
