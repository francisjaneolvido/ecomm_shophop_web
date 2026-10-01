@extends('admin.layout')

@section('title', 'Commission')

@section('content')
    {{-- An informational surface replaces fixture ledgers and financial controls until a real accounting contract exists. --}}
    <section id="adminCommission" aria-labelledby="commissionTitle" class="space-y-6">
        <div>
            <h1 id="commissionTitle" class="text-2xl font-bold text-navy">Commission</h1>
            <p class="text-sm text-slate-500 mt-1">Commission availability on ShopHop.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <span class="inline-flex rounded-full bg-mint/15 px-3 py-1 text-xs font-semibold text-mint-dark">Unavailable</span>
            <h2 class="text-lg font-semibold text-navy mt-4">Commission accounting is not yet available.</h2>
            <p class="text-sm text-slate-500 mt-2">This page does not represent live commission, payout or accounting records.</p>

            {{-- Removing export and paid/resolved affordances prevents unsupported actions from implying financial completion. --}}
            <ul class="list-disc pl-5 mt-4 space-y-2 text-sm text-slate-500">
                <li>Commission rates, totals and transaction records are unavailable.</li>
                <li>Marking commissions as paid or resolved is unavailable.</li>
                <li>Seller payout recording is unavailable.</li>
                <li>PDF and report export are unavailable.</li>
            </ul>
        </div>

        {{-- Cash custody and proof review cannot establish Commission accounting or external provider settlement. --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <h2 class="text-base font-semibold text-navy">Separate payment workflows</h2>
            <p class="text-sm text-slate-500 mt-2">COD collection, remittance and reconciliation track cash handling. They do not record seller payouts or commission earnings.</p>
            <p class="text-sm text-slate-500 mt-2">Manual cashless payment review checks buyer-submitted payment evidence. It does not confirm bank or wallet settlement or calculate commission.</p>
        </div>
    </section>
@endsection
