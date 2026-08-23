@extends('admin.layout')

@section('title', 'Commission')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Commission (10%)</h1>
        <p class="text-sm text-slate-500 mt-1">Subaybayan ang kita ng platform mula sa mga completed na transactions.</p>
    </div>

    {{-- ============ STAT SUMMARY ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-yellow/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 2v8m0 0v2m0-2c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <span class="text-[11px] font-semibold text-mint-dark bg-mint/10 px-2 py-0.5 rounded-full">+12% vs last month</span>
            </div>
            <p class="text-2xl font-bold text-navy">₱84,210</p>
            <p class="text-xs text-slate-500 mt-1">Commission This Month</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5-1h5v5M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-navy">₱842,100</p>
            <p class="text-xs text-slate-500 mt-1">Gross Sales This Month</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-sky/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-sky" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m0 0v4m0-4h-4M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-navy">1,204</p>
            <p class="text-xs text-slate-500 mt-1">Transactions This Month</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-[11px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">Unpaid</span>
            </div>
            <p class="text-2xl font-bold text-navy">₱6,340</p>
            <p class="text-xs text-slate-500 mt-1">Pending Payout Deductions</p>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ============ RECENT TRANSACTIONS TABLE ============ --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-navy text-sm">Recent Commission Entries</h2>
                <a href="#" class="text-xs font-medium text-mint-dark hover:underline">View all</a>
            </div>

            {{-- Palitan mo na lang ito ng @foreach loop galing sa $transactions mo sa controller --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Order</th>
                            <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Seller</th>
                            <th class="text-right font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Sale Amount</th>
                            <th class="text-right font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4 font-medium text-navy">#10432</td>
                            <td class="px-5 py-4 text-slate-500">TechHub PH</td>
                            <td class="px-5 py-4 text-right text-navy">₱2,499.00</td>
                            <td class="px-5 py-4 text-right font-semibold text-mint-dark">₱249.90</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4 font-medium text-navy">#10431</td>
                            <td class="px-5 py-4 text-slate-500">Aling Nena's Store</td>
                            <td class="px-5 py-4 text-right text-navy">₱780.00</td>
                            <td class="px-5 py-4 text-right font-semibold text-mint-dark">₱78.00</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4 font-medium text-navy">#10430</td>
                            <td class="px-5 py-4 text-slate-500">Jomar's Repair Shop</td>
                            <td class="px-5 py-4 text-right text-navy">₱1,150.00</td>
                            <td class="px-5 py-4 text-right font-semibold text-mint-dark">₱115.00</td>
                        </tr>
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4 font-medium text-navy">#10429</td>
                            <td class="px-5 py-4 text-slate-500">TechHub PH</td>
                            <td class="px-5 py-4 text-right text-navy">₱3,200.00</td>
                            <td class="px-5 py-4 text-right font-semibold text-mint-dark">₱320.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============ TOP EARNING SELLERS ============ --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-navy text-sm mb-4">Top Commission Contributors</h2>

            <div class="space-y-4">

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-sky/15 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-sky">TH</span>
                        </div>
                        <p class="text-sm font-medium text-navy truncate">TechHub PH</p>
                    </div>
                    <p class="text-sm font-semibold text-navy shrink-0">₱18,420</p>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-mint/15 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-mint-dark">AN</span>
                        </div>
                        <p class="text-sm font-medium text-navy truncate">Aling Nena's Store</p>
                    </div>
                    <p class="text-sm font-semibold text-navy shrink-0">₱9,760</p>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-coral/15 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-coral">JR</span>
                        </div>
                        <p class="text-sm font-medium text-navy truncate">Jomar's Repair Shop</p>
                    </div>
                    <p class="text-sm font-semibold text-navy shrink-0">₱6,150</p>
                </div>

            </div>
        </div>

    </div>

@endsection