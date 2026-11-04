@extends('admin.layout')
@section('title', 'ShopHop Finances')
@section('content')
@php
  // Reusable class strings (para consistent ang itsura ng lahat ng form)
  $card   = 'rounded-lg border border-slate-200 bg-white shadow-sm';
  $input  = 'mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30';
  $label  = 'block text-xs font-medium text-slate-600';
  $btn    = 'inline-flex items-center justify-center rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500/40';
  $btnSm  = 'inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40';
  $btnOut = 'inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-500/30';
  $th     = 'px-4 py-2.5 text-xs font-semibold text-slate-500';
  $td     = 'px-4 py-3 align-top';
  $badge  = fn($s) => match(strtolower((string)$s)) {
      'pending'   => 'bg-amber-50 text-amber-700 ring-amber-200',
      'available' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
      'paid'      => 'bg-sky-50 text-sky-700 ring-sky-200',
      default     => 'bg-slate-100 text-slate-600 ring-slate-200',
  };
@endphp

<div class="mx-auto max-w-7xl space-y-5">

  {{-- ============ PAGE HEADER ============ --}}
  <header class="flex flex-wrap items-end justify-between gap-4">
    <div class="max-w-2xl">
      <h1 class="text-2xl font-bold text-navy">ShopHop finances</h1>
      <p class="mt-1 text-sm text-slate-500">
        10% commission is computed on merchandise after the Seller voucher. Platform discounts are funded by ShopHop.
        A recorded payout is <strong class="font-semibold text-slate-700">not</strong> an automatic money transfer.
      </p>
    </div>
    <nav class="flex flex-wrap gap-2 text-xs font-semibold" aria-label="Jump to section">
      <a href="#distribution-queue" class="{{ $btnOut }}">Distribution</a>
      <a href="#available-payouts" class="{{ $btnOut }}">Payouts</a>
      <a href="#settings" class="{{ $btnOut }}">Settings</a>
      <a href="#vouchers" class="{{ $btnOut }}">Vouchers</a>
      <a href="#history" class="{{ $btnOut }}">History</a>
    </nav>
  </header>

  {{-- ============ ALERTS ============ --}}
  @if(session('status'))
    <div role="status" class="flex items-start gap-2 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800">
      <span aria-hidden="true">✓</span><span>{{ session('status') }}</span>
    </div>
  @endif
  @if($errors->any())
    <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      <p class="font-semibold">May kailangang ayusin:</p>
      <ul class="mt-1 list-inside list-disc space-y-0.5">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- ============ SUMMARY ============ --}}
  <section aria-label="Settlement summary" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="{{ $card }} border-l-4 border-l-amber-400 p-4">
      <p class="text-xs text-slate-500">Pending distribution</p>
      <p class="mt-1 text-xl font-bold text-slate-800">₱{{ number_format($pendingDistributionTotal,2) }}</p>
    </div>
    <div class="{{ $card }} border-l-4 border-l-emerald-500 p-4">
      <p class="text-xs text-slate-500">Available for payout</p>
      <p class="mt-1 text-xl font-bold text-slate-800">₱{{ number_format($availablePayoutTotal,2) }}</p>
    </div>
    <div class="{{ $card }} border-l-4 border-l-sky-500 p-4">
      <p class="text-xs text-slate-500">Paid out</p>
      <p class="mt-1 text-xl font-bold text-slate-800">₱{{ number_format($paidPayoutTotal,2) }}</p>
    </div>
    <div class="{{ $card }} border-l-4 border-l-teal-600 p-4">
      <p class="text-xs text-slate-500">Commission (completed orders)</p>
      <p class="mt-1 text-xl font-bold text-slate-800">₱{{ number_format($commissionTotal,2) }}</p>
      <p class="mt-0.5 text-[11px] text-slate-400">Not a cash balance</p>
    </div>
  </section>

  {{-- ============ QUICK LINKS ============ --}}
  <section class="grid gap-3 md:grid-cols-2">
    <a href="{{ route('admin.finance.cod.index') }}" class="{{ $card }} group block p-4 transition hover:border-emerald-300">
      <p class="text-sm font-semibold text-emerald-700 group-hover:underline">COD cash remittance verification</p>
      <p class="mt-1 text-xs text-slate-500">Verify actual Logistics-to-ShopHop cash receipt before releasing Seller or Logistics payouts.</p>
    </a>
    <a href="{{ route('admin.returns.index') }}" class="group block rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm transition hover:border-amber-300">
      <p class="text-sm font-semibold text-amber-900 group-hover:underline">Review return-to-Seller and manual refunds</p>
      <p class="mt-1 text-xs text-amber-800">No refund is automatic. The Seller must confirm physical receipt first.</p>
    </a>
  </section>

  {{-- ============ 1. PREPARE SETTLEMENT ============ --}}
  <section class="{{ $card }} p-5">
    <h2 class="text-base font-bold text-slate-800">Completed orders awaiting settlement preparation</h2>
    <p class="mt-1 max-w-3xl text-xs leading-relaxed text-slate-500">
      For COD, the full collected amount must already be received and verified by ShopHop Admin. The parcel must be delivered with no settlement hold.
      Historical completed orders without a finance snapshot are rebuilt from their frozen Order/OrderItem checkout amounts; unsafe mismatches are blocked for manual review.
    </p>
    <div class="mt-4 flex flex-wrap gap-2">
      @forelse($eligibleOrders as $o)
        <form method="POST" action="{{ route('admin.finance.prepare', $o->id) }}">@csrf
          <button class="{{ $btnOut }}">
            Prepare #{{ $o->id }}
            @unless($o->financial)<span class="ml-1.5 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500">historical</span>@endunless
          </button>
        </form>
      @empty
        <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-500">No new eligible orders.</p>
      @endforelse
    </div>
  </section>

  {{-- ============ 2. DISTRIBUTION QUEUE ============ --}}
  <section id="distribution-queue" class="{{ $card }} scroll-mt-6 p-5">
    <h2 class="text-base font-bold text-slate-800">Settlement distribution queue</h2>
    <p class="mt-1 max-w-3xl text-xs leading-relaxed text-slate-500">
      Prepared means computed but still held by ShopHop. Distributing moves beneficiary ledgers from Pending to Available.
      It does not mean an external bank or e-wallet transfer happened.
    </p>

    <div class="admin-table-scroll mt-4 rounded-lg border border-slate-200">
      <table class="min-w-[760px]">
        <thead><tr><th>Order</th><th class="text-right">Seller</th><th class="text-right">Logistics</th><th class="text-right">Rider allocations</th><th>Platform net</th><th class="text-right">Action</th></tr></thead>
        <tbody>
          @forelse($distributionOrders as $o)
            @php
              $sellerEntry = $o->settlements->firstWhere('kind', 'seller');
              $logisticsEntry = $o->settlements->firstWhere('kind', 'logistics');
              $riderTotal = (float) $o->settlements->whereIn('kind', ['pickup_rider', 'delivery_rider'])->sum('amount');
            @endphp
            <tr>
              <td><strong>Order #{{ $o->id }}</strong><div class="mt-1"><span class="admin-status admin-status-ok">Prepared</span></div></td>
              <td class="text-right tabular-nums">₱{{ number_format((float) ($sellerEntry?->amount ?? 0), 2) }}</td>
              <td class="text-right tabular-nums">₱{{ number_format((float) ($logisticsEntry?->amount ?? 0), 2) }}</td>
              <td class="text-right tabular-nums">₱{{ number_format($riderTotal, 2) }}</td>
              <td class="tabular-nums">{{ $o->financial ? '₱'.number_format((float) $o->financial->platform_net, 2) : '—' }}</td>
              <td>
                <form method="POST" action="{{ route('admin.finance.distribute', $o->id) }}" class="admin-row-actions" onsubmit="return confirm('Distribute this prepared settlement? This updates ledgers but does not move external money.')">
                  @csrf<button class="admin-btn admin-btn-teal">Distribute</button>
                </form>
              </td>
            </tr>
          @empty<tr><td colspan="6" class="admin-empty">No prepared orders are waiting for distribution.</td></tr>@endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- ============ 3. AVAILABLE PAYOUTS ============ --}}
  <section id="available-payouts" class="{{ $card }} scroll-mt-6 p-5">
    <h2 class="text-base font-bold text-slate-800">Available Seller and Logistics payouts</h2>
    <p class="mt-1 text-xs text-slate-500">Available means Admin already distributed the ledger amount. Mark as paid only after the actual or manual payout is verified.</p>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
      <table class="w-full min-w-[720px] text-left text-sm">
        <thead class="bg-slate-50"><tr>
          <th class="{{ $th }}">Order</th><th class="{{ $th }}">Payee</th><th class="{{ $th }} text-right">Amount</th><th class="{{ $th }}">Distributed</th><th class="{{ $th }}">Record payout</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($availablePayouts as $entry)
            <tr class="hover:bg-slate-50/60">
              <td class="{{ $td }} font-medium">#{{ $entry->order_id }}</td>
              <td class="{{ $td }}">{{ ucfirst($entry->kind) }} #{{ $entry->payee_id }}</td>
              <td class="{{ $td }} text-right font-semibold tabular-nums">₱{{ number_format((float)$entry->amount,2) }}</td>
              <td class="{{ $td }} text-xs text-slate-600">
                {{ $entry->distributed_at?->format('M j, g:i A') ?? '—' }}
                <div class="text-[11px] text-slate-400">{{ $entry->distribution_reference }}</div>
              </td>
              <td class="{{ $td }}">
                <button type="button" class="admin-btn admin-btn-teal" data-admin-dialog-open="payout-{{ $entry->id }}">Record payout</button>
                <dialog id="payout-{{ $entry->id }}" class="admin-dialog" aria-labelledby="payout-title-{{ $entry->id }}">
                  <form method="POST" action="{{ route('admin.finance.paid', $entry->id) }}" class="admin-dialog-frame" onsubmit="return confirm('Confirm only after verifying the actual payout. Mark paid?')">@csrf
                    <div class="admin-dialog-header"><div><h3 id="payout-title-{{ $entry->id }}" class="font-bold">Record payout · Order #{{ $entry->order_id }}</h3><p class="mt-1 text-xs text-slate-500">{{ ucfirst($entry->kind) }} #{{ $entry->payee_id }} · ₱{{ number_format((float)$entry->amount,2) }}</p></div><button type="button" class="admin-btn" data-admin-dialog-close aria-label="Close">✕</button></div>
                    <div class="admin-dialog-body"><label class="block"><span class="admin-label">Verified payout reference *</span><input name="reference" required maxlength="120" class="admin-input" placeholder="Bank or manual payout reference"></label><p class="mt-3 text-xs text-amber-700">This records a verified payment; it does not send money automatically.</p></div>
                    <div class="admin-dialog-footer"><button type="button" class="admin-btn" data-admin-dialog-close>Cancel</button><button type="submit" class="admin-btn admin-btn-teal">Mark paid</button></div>
                  </form>
                </dialog>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">No distributed payouts awaiting payment.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- ============ 4. MARKETPLACE RATES ============ --}}
  <div id="settings" class="scroll-mt-5 space-y-4">

    {{-- RATES --}}
    <section class="{{ $card }} p-5">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="max-w-3xl">
          <h2 class="text-base font-bold text-slate-800">Marketplace shipping and payout rates</h2>
          <p class="mt-1 text-xs leading-relaxed text-slate-500">
            Gross shipping = base + route distance band + linehaul component + excess weight. Express adds a percentage surcharge. ShopHop commission is locked at 10%.
          </p>
        </div>
        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">ShopHop 10%</span>
      </div>

      <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-md border border-teal-100 bg-teal-50/60 px-3 py-2.5">
        <p class="text-xs leading-relaxed text-teal-900"><strong>Live settings:</strong> Changes affect future checkout quotes only. Previously saved orders keep their original amounts.</p>
        <span class="text-xs font-semibold text-teal-800">Commission fixed at 10%</span>
      </div>

      <form id="shMarketplaceRateForm" method="POST" action="{{ route('admin.finance.rates') }}" class="mt-4 space-y-5">@csrf

        <fieldset>
          <legend class="text-sm font-semibold text-slate-700">Customer shipping</legend>
          <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="{{ $label }}">Commission %
              <input name="commission_percent" value="10" readonly class="{{ $input }} bg-slate-50 text-slate-500">
            </label>
            <label class="{{ $label }}">Base shipping
              <input name="base_shipping_fee" data-saved-rate="{{ $rates->base_shipping_fee }}" type="number" step="0.01" min="0" value="{{ old('base_shipping_fee',$rates->base_shipping_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Local distance band
              <input name="local_distance_fee" data-saved-rate="{{ $rates->local_distance_fee }}" type="number" step="0.01" min="0" value="{{ old('local_distance_fee',$rates->local_distance_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Same-province distance band
              <input name="same_province_distance_fee" data-saved-rate="{{ $rates->same_province_distance_fee }}" type="number" step="0.01" min="0" value="{{ old('same_province_distance_fee',$rates->same_province_distance_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Inter-province distance band
              <input name="interprovince_distance_fee" data-saved-rate="{{ $rates->interprovince_distance_fee }}" type="number" step="0.01" min="0" value="{{ old('interprovince_distance_fee',$rates->interprovince_distance_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Same-province linehaul
              <input name="same_province_linehaul_fee" data-saved-rate="{{ $rates->same_province_linehaul_fee }}" type="number" step="0.01" min="0" value="{{ old('same_province_linehaul_fee',$rates->same_province_linehaul_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Inter-province linehaul
              <input name="interprovince_linehaul_fee" data-saved-rate="{{ $rates->interprovince_linehaul_fee }}" type="number" step="0.01" min="0" value="{{ old('interprovince_linehaul_fee',$rates->interprovince_linehaul_fee) }}" class="{{ $input }}">
            </label>
          </div>
        </fieldset>

        <fieldset class="border-t border-slate-100 pt-5">
          <legend class="text-sm font-semibold text-slate-700">Weight and express</legend>
          <div class="mt-3 grid gap-4 sm:grid-cols-3">
            <label class="{{ $label }}">Included weight (kg)
              <input name="included_weight_kg" data-saved-rate="{{ $rates->included_weight_kg }}" type="number" step="0.001" min="0.05" value="{{ old('included_weight_kg',$rates->included_weight_kg) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Extra weight fee per kg
              <input name="extra_weight_fee_per_kg" data-saved-rate="{{ $rates->extra_weight_fee_per_kg }}" type="number" step="0.01" min="0" value="{{ old('extra_weight_fee_per_kg',$rates->extra_weight_fee_per_kg) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Express surcharge %
              <input name="express_surcharge_percent" data-saved-rate="{{ $rates->express_surcharge_percent }}" type="number" step="0.01" min="0" value="{{ old('express_surcharge_percent',$rates->express_surcharge_percent) }}" class="{{ $input }}">
            </label>
          </div>
        </fieldset>

        <fieldset class="border-t border-slate-100 pt-5">
          <legend class="text-sm font-semibold text-slate-700">Logistics allocations</legend>
          <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <label class="{{ $label }}">Pickup rider earning
              <input name="pickup_rider_fee" data-saved-rate="{{ $rates->pickup_rider_fee }}" type="number" step="0.01" min="0" value="{{ old('pickup_rider_fee',$rates->pickup_rider_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Delivery rider earning
              <input name="delivery_rider_fee" data-saved-rate="{{ $rates->delivery_rider_fee }}" type="number" step="0.01" min="0" value="{{ old('delivery_rider_fee',$rates->delivery_rider_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Province hub per parcel
              <input name="province_hub_fee" data-saved-rate="{{ $rates->province_hub_fee }}" type="number" step="0.01" min="0" value="{{ old('province_hub_fee',$rates->province_hub_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Origin branch allocation
              <input name="origin_branch_fee" data-saved-rate="{{ $rates->origin_branch_fee }}" type="number" step="0.01" min="0" value="{{ old('origin_branch_fee',$rates->origin_branch_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Destination branch allocation
              <input name="destination_branch_fee" data-saved-rate="{{ $rates->destination_branch_fee }}" type="number" step="0.01" min="0" value="{{ old('destination_branch_fee',$rates->destination_branch_fee) }}" class="{{ $input }}">
            </label>
          </div>
        </fieldset>

        <fieldset class="border-t border-slate-100 pt-5">
          <legend class="text-sm font-semibold text-slate-700">Linehaul driver fees (per trip)</legend>
          <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <label class="{{ $label }}">Branch ↔ Hub
              <input name="linehaul_branch_hub_driver_fee" data-saved-rate="{{ $rates->linehaul_branch_hub_driver_fee }}" type="number" step="0.01" min="0" value="{{ old('linehaul_branch_hub_driver_fee',$rates->linehaul_branch_hub_driver_fee) }}" class="{{ $input }}">
            </label>
            <label class="{{ $label }}">Hub ↔ Hub
              <input name="linehaul_hub_hub_driver_fee" data-saved-rate="{{ $rates->linehaul_hub_hub_driver_fee }}" type="number" step="0.01" min="0" value="{{ old('linehaul_hub_hub_driver_fee',$rates->linehaul_hub_hub_driver_fee) }}" class="{{ $input }}">
            </label>
          </div>
        </fieldset>

        <div class="sh-rate-actions sticky bottom-0 z-10 -mx-5 -mb-5 mt-2 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-white/95 px-5 py-3 backdrop-blur-sm">
          <p class="text-xs text-slate-500">Reset discards unsaved edits; Save validates and updates the database.</p>
          <div class="flex flex-wrap items-center gap-2">
            <button type="reset" class="{{ $btnOut }}" id="shResetRates">Reset changes</button>
            <button type="submit" class="{{ $btn }}" id="shSaveRates">Save marketplace rates</button>
          </div>
        </div>
      </form>
    </section>
  </div>

  {{-- ============ 5. VOUCHERS ============ --}}
  <section id="vouchers" class="{{ $card }} scroll-mt-6 p-5">
    <h2 class="text-base font-bold text-slate-800">ShopHop-funded vouchers</h2>
    <p class="mt-1 text-xs text-slate-500">Platform merchandise and free-shipping vouchers are separate from Seller vouchers.</p>

    <form method="POST" action="{{ route('admin.finance.vouchers.create') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">@csrf
      <label class="{{ $label }}">Code
        <input name="code" placeholder="e.g. SHOPHOP50" required maxlength="40" class="{{ $input }} uppercase">
      </label>
      <label class="{{ $label }} lg:col-span-1">Voucher name
        <input name="name" placeholder="e.g. Payday sale" required maxlength="160" class="{{ $input }}">
      </label>
      <label class="{{ $label }}">Applies to
        <select name="kind" class="{{ $input }}">
          <option value="merchandise">Platform merchandise</option>
          <option value="shipping">Free shipping</option>
        </select>
      </label>
      <label class="{{ $label }}">Discount type
        <select name="type" class="{{ $input }}">
          <option value="fixed">Fixed peso</option>
          <option value="percent">Percentage</option>
        </select>
      </label>
      <label class="{{ $label }}">Value
        <input name="value" type="number" min="0.01" step="0.01" required class="{{ $input }}">
      </label>
      <label class="{{ $label }}">Minimum merchandise spend
        <input name="min_spend" type="number" min="0" step="0.01" value="0" required class="{{ $input }}">
      </label>
      <label class="{{ $label }}">Usage limit <span class="text-slate-400">(optional)</span>
        <input name="usage_limit" type="number" min="1" class="{{ $input }}">
      </label>
      <label class="{{ $label }}">Expires <span class="text-slate-400">(optional)</span>
        <input name="ends_at" type="datetime-local" class="{{ $input }}">
      </label>
      <div class="sm:col-span-2 lg:col-span-4"><button class="{{ $btn }}">Create platform voucher</button></div>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
      <table class="w-full min-w-[560px] text-left text-sm">
        <thead class="bg-slate-50"><tr>
          <th class="{{ $th }}">Code</th><th class="{{ $th }}">Type</th><th class="{{ $th }}">Value</th><th class="{{ $th }}">Used</th><th class="{{ $th }}">Status</th><th class="{{ $th }}"><span class="sr-only">Action</span></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($vouchers as $v)
            <tr class="hover:bg-slate-50/60">
              <td class="{{ $td }} font-mono text-xs font-semibold">{{ $v->code }}</td>
              <td class="{{ $td }} capitalize">{{ $v->kind }}</td>
              <td class="{{ $td }} tabular-nums">{{ $v->type === 'fixed' ? '₱' : '' }}{{ $v->value }}{{ $v->type === 'percent' ? '%' : '' }}</td>
              <td class="{{ $td }} tabular-nums">{{ $v->used_count }} / {{ $v->usage_limit ?? '∞' }}</td>
              <td class="{{ $td }}">
                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $v->active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-slate-200' }}">
                  {{ $v->active ? 'Active' : 'Disabled' }}
                </span>
              </td>
              <td class="{{ $td }} text-right">
                <form method="POST" action="{{ route('admin.finance.vouchers.toggle', $v) }}">@csrf
                  <button class="{{ $btnOut }}">{{ $v->active ? 'Disable' : 'Enable' }}</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">No platform vouchers yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- ============ 6. HISTORY ============ --}}
  <div id="history" class="scroll-mt-5 space-y-4">

    {{-- Snapshots --}}
    <section class="{{ $card }} p-5">
      <h2 class="text-base font-bold text-slate-800">Order financial snapshots</h2>
      <p class="mt-1 text-xs text-slate-500">Snapshot amounts are fixed at checkout.</p>

      <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full min-w-[1100px] text-left text-xs">
          <thead class="bg-slate-50"><tr>
            <th class="{{ $th }}">Order</th>
            <th class="{{ $th }} text-right">Merchandise</th>
            <th class="{{ $th }} text-right">Seller voucher</th>
            <th class="{{ $th }} text-right">Seller shipping disc.</th>
            <th class="{{ $th }} text-right">ShopHop discounts</th>
            <th class="{{ $th }} text-right">Gross shipping</th>
            <th class="{{ $th }}">Route / weight</th>
            <th class="{{ $th }} text-right">Commission</th>
            <th class="{{ $th }} text-right">Seller net</th>
            <th class="{{ $th }} text-right">ShopHop net</th>
            <th class="{{ $th }}">Settlement hold</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($finances as $f)
              <tr class="hover:bg-slate-50/60">
                <td class="{{ $td }} font-medium">#{{ $f->order_id }}</td>
                <td class="{{ $td }} text-right tabular-nums">₱{{ $f->merchandise }}</td>
                <td class="{{ $td }} text-right tabular-nums">₱{{ $f->seller_discount }}</td>
                <td class="{{ $td }} text-right tabular-nums">₱{{ number_format((float)$f->seller_shipping_discount,2) }}</td>
                <td class="{{ $td }} text-right tabular-nums">₱{{ number_format((float)$f->platform_discount + (float)$f->shipping_discount,2) }}</td>
                <td class="{{ $td }} text-right tabular-nums">₱{{ $f->logistics_earnings }}</td>
                <td class="{{ $td }}">{{ $f->shipping_route_band ?? '—' }}<div class="text-[11px] text-slate-400">{{ number_format((float)$f->shipping_weight_kg,3) }} kg</div></td>
                <td class="{{ $td }} text-right tabular-nums">₱{{ $f->commission }}</td>
                <td class="{{ $td }} text-right font-semibold tabular-nums">₱{{ $f->seller_earnings }}</td>
                <td class="{{ $td }} text-right font-semibold tabular-nums">₱{{ $f->platform_net }}</td>
                <td class="{{ $td }}">
                  <form method="POST" action="{{ route('admin.finance.hold', $f->order_id) }}" class="flex items-center gap-1.5">@csrf
                    <input type="hidden" name="hold" value="{{ $f->settlement_hold ? 0 : 1 }}">
                    <input name="reason" placeholder="Reason" aria-label="Hold reason" maxlength="500" class="w-28 rounded-lg border border-slate-300 px-2 py-1 text-xs focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30">
                    <button class="{{ $f->settlement_hold ? 'rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 hover:bg-amber-200' : $btnOut }}">
                      {{ $f->settlement_hold ? 'Release hold' : 'Hold' }}
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="11" class="px-4 py-8 text-center text-sm text-slate-500">No financial snapshots yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-4">{{ $finances->links() }}</div>
    </section>

    {{-- Settlement history --}}
    <section class="{{ $card }} p-5">
      <h2 class="text-base font-bold text-slate-800">Settlement payout history</h2>
      <p class="mt-1 text-xs text-slate-500">
        <span class="font-semibold text-amber-700">Pending</span> = prepared, not distributed ·
        <span class="font-semibold text-emerald-700">Available</span> = distributed and credited ·
        <span class="font-semibold text-sky-700">Paid</span> = actual or manual payout recorded.
      </p>

      <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full min-w-[640px] text-left text-sm">
          <thead class="bg-slate-50"><tr>
            <th class="{{ $th }}">Order</th><th class="{{ $th }}">Payee</th><th class="{{ $th }} text-right">Amount</th><th class="{{ $th }}">Status</th><th class="{{ $th }}">Reference</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($settlements as $entry)
              @if(in_array($entry->kind, ['seller','logistics']))
                <tr class="hover:bg-slate-50/60">
                  <td class="{{ $td }} font-medium">#{{ $entry->order_id }}</td>
                  <td class="{{ $td }}">{{ ucfirst($entry->kind) }} #{{ $entry->payee_id }}</td>
                  <td class="{{ $td }} text-right font-semibold tabular-nums">₱{{ number_format((float)$entry->amount,2) }}</td>
                  <td class="{{ $td }}"><span class="rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $badge($entry->status) }}">{{ ucfirst($entry->status) }}</span></td>
                  <td class="{{ $td }} text-xs text-slate-600">{{ $entry->payment_reference ?? $entry->distribution_reference ?? '—' }}</td>
                </tr>
              @endif
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-4">{{ $settlements->links() }}</div>
    </section>
  </div>

</div>

@push('scripts')
<script>
// Reset to the last persisted rate values, not to potentially invalid old() input.
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('shMarketplaceRateForm');
    if (!form) return;
    form.addEventListener('reset', (event) => {
        event.preventDefault();
        form.querySelectorAll('[data-saved-rate]').forEach((field) => {
            field.value = field.dataset.savedRate ?? '';
        });
    });
});
</script>
@endpush

@endsection