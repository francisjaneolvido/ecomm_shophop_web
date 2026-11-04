@extends('seller.partials.layout')
@section('title', 'Seller Earnings')
@section('content')
<section id="sellerEarnings" class="mx-auto max-w-7xl space-y-4">
    <header class="sh-page-header flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-teal-dark">Seller finances</p>
            <h1 class="mt-1 text-2xl font-bold text-navy">My Earnings</h1>
            <p class="mt-1 max-w-3xl text-sm leading-relaxed text-navy/60">Track distribution and payout records. A payout is marked Paid only after the transfer is recorded; available earnings are not automatically transferred.</p>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="Earnings overview">
        <div class="sh-finance-summary"><p>Pending distribution</p><strong class="tabular-nums">₱{{ number_format((float)$pending, 2) }}</strong><span>Not yet available for payout</span></div>
        <div class="sh-finance-summary"><p>Available for payout</p><strong class="tabular-nums">₱{{ number_format((float)$available, 2) }}</strong><span>Distributed, awaiting payment record</span></div>
        <div class="sh-finance-summary"><p>Recorded as paid</p><strong class="tabular-nums">₱{{ number_format((float)$paid, 2) }}</strong><span>Manual or verified transfer records</span></div>
    </div>

    <section class="sh-management-panel" aria-labelledby="sellerEarningsHistoryTitle">
        <div class="sh-management-panel-head">
            <div><h2 id="sellerEarningsHistoryTitle">Earnings history</h2><p>All recorded Seller allocations from ShopHop settlements.</p></div>
        </div>
        <div class="sh-management-table-scroll" tabindex="0" role="region" aria-label="Seller earnings history table">
            <table class="w-full min-w-[650px]">
                <thead><tr><th scope="col">Order</th><th scope="col" class="text-right">Amount</th><th scope="col">Payout status</th><th scope="col">Distribution / payout reference</th></tr></thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td class="font-semibold text-navy">#{{ $entry->order_id }}</td>
                            <td class="text-right font-semibold tabular-nums text-navy">₱{{ number_format((float)$entry->amount, 2) }}</td>
                            <td><span @class(['sh-management-status','sh-management-status-ok' => $entry->status === 'paid','sh-management-status-info' => $entry->status === 'available','sh-management-status-warn' => $entry->status === 'pending'])>{{ ucfirst($entry->status) }}</span></td>
                            <td class="break-all text-navy/65">{{ $entry->payment_reference ?? $entry->distribution_reference ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-navy/55">No Seller earnings have been prepared yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="sh-management-panel-foot">{{ $entries->links() }}</div>
    </section>
    <p class="text-xs leading-relaxed text-navy/60">ShopHop commission is 10% of merchandise after Seller-funded merchandise discounts, excluding shipping. Seller-funded shipping vouchers reduce Seller payout; ShopHop-funded vouchers do not.</p>
</section>
@endsection
