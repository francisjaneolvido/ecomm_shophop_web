@extends('admin.layout')
@section('title', 'COD Cash Remittances')
@section('content')
<div class="space-y-6">
  <div><a href="{{ route('admin.commission') }}" class="text-sm text-teal-700">← Finance dashboard</a><h1 class="mt-2 text-2xl font-bold text-navy">COD Cash Remittances</h1><p class="mt-2 max-w-3xl text-sm text-slate-500">Rider → Logistics confirmation → Logistics transfers to ShopHop → Admin verifies actual receipt. Confirm only after checking your demo/manual receipt ledger; this action does not move real money.</p></div>
  @if(session('status'))<div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
  <div class="space-y-4">@forelse($settlements as $cash)
    <section class="rounded-2xl border border-slate-200 bg-white p-5">
      <div class="flex flex-wrap justify-between gap-3"><div><h2 class="font-semibold text-navy">Order #{{ $cash->order_id }} · {{ $cash->partner?->company_name ?? 'Logistics' }}</h2><p class="mt-1 text-xs text-slate-500">Amount due ₱{{ number_format((float)$cash->expected_amount,2) }} · Logistics reconciled {{ $cash->reconciled_at?->format('M j, Y g:i A') }}</p></div>
        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $cash->admin_received_at ? 'bg-emerald-50 text-emerald-700' : ($cash->admin_remitted_at ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">{{ $cash->admin_received_at ? 'ShopHop received' : ($cash->admin_remitted_at ? 'Awaiting Admin confirmation' : 'Awaiting Logistics remittance') }}</span>
      </div>
      @if($cash->admin_received_at)
        <p class="mt-4 text-sm text-emerald-800">Received ₱{{ number_format((float)$cash->admin_received_amount,2) }} on {{ $cash->admin_received_at->format('M j, Y g:i A') }}.</p>
        @php
          $orderSettlements = $cash->order?->settlements ?? collect();
          $hasPrepared = $orderSettlements->isNotEmpty();
          $hasPending = $orderSettlements->where('status','pending')->isNotEmpty();
          $hasAvailable = $orderSettlements->where('status','available')->isNotEmpty();
          $allPaid = $hasPrepared && $orderSettlements->every(fn($entry) => $entry->status === 'paid');
        @endphp
        <div class="mt-3 flex flex-wrap items-center gap-2">
          @if(!$hasPrepared)
            <form method="POST" action="{{ route('admin.finance.prepare', $cash->order_id) }}">@csrf<button class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold">Prepare settlement</button></form>
            @if(!$cash->order?->financial)
              <span class="text-xs text-slate-500">Historical order · frozen checkout amounts will be safely reconstructed first.</span>
            @endif
          @elseif($hasPending)
            <a href="{{ route('admin.commission') }}#distribution-queue" class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Review & distribute</a>
            <span class="text-xs text-amber-700">Prepared · pending distribution</span>
          @elseif($hasAvailable)
            <a href="{{ route('admin.commission') }}#available-payouts" class="rounded-xl border border-emerald-300 px-3 py-2 text-xs font-semibold text-emerald-700">View available payouts</a>
            <span class="text-xs text-emerald-700">Distributed · awaiting payout record</span>
          @elseif($allPaid)
            <span class="text-xs font-semibold text-emerald-700">Settlement fully paid ✓</span>
          @endif
        </div>
      @elseif($cash->admin_remitted_at)
        <p class="mt-4 text-sm">Logistics reference: <strong>{{ $cash->admin_remittance_reference }}</strong></p>
        <form method="POST" action="{{ route('admin.finance.cod.confirm', $cash) }}" class="mt-3 grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">@csrf
          <label class="text-xs font-semibold text-slate-600">Reference verified<input name="reference" required maxlength="120" autocomplete="off" placeholder="Type reference after verification" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm" /></label>
          <label class="text-xs font-semibold text-slate-600">Amount actually received (PHP)<input name="received_amount" type="number" step="0.01" min="0" required class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm" /></label>
          <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white" onclick="return confirm('Only confirm after verifying the actual receipt amount. Proceed?')">Confirm received</button>
        </form>
      @else<p class="mt-4 text-sm text-amber-700">Waiting for Logistics to record remittance and reference.</p>@endif
    </section>
  @empty<div class="rounded-xl border bg-white p-7 text-sm text-slate-500">No reconciled COD records yet.</div>@endforelse</div>
  {{ $settlements->links() }}
</div>
@endsection
