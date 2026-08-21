@extends('admin.layout')

@section('title', 'Complaints & Disputes')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Complaints &amp; Disputes</h1>
        <p class="text-sm text-slate-500 mt-1">Suriin at lutasin ang mga isyu sa pagitan ng buyer at seller.</p>
    </div>

    {{-- ============ STAT SUMMARY ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">3</p>
                    <p class="text-xs text-slate-500">Open / Urgent</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-yellow/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">7</p>
                    <p class="text-xs text-slate-500">In Mediation</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">142</p>
                    <p class="text-xs text-slate-500">Resolved This Month</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ============ FILTER TABS ============ --}}
    <div class="flex items-center gap-2 mb-5">
        <button class="px-4 py-2 rounded-xl text-sm font-semibold bg-navy text-white">
            Open <span class="ml-1 opacity-70">(3)</span>
        </button>
        <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">
            In Mediation
        </button>
        <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">
            Resolved
        </button>
    </div>

    {{-- ============ DISPUTES LIST ============ --}}
    {{-- Palitan mo na lang ito ng @foreach loop galing sa $disputes mo sa controller --}}
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">Open Complaints</h2>
            <span class="text-xs text-slate-400">3 total</span>
        </div>

        <div class="divide-y divide-slate-100">

            {{-- ITEM 1 --}}
            <div class="flex items-start justify-between gap-4 px-5 py-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-navy">Order #10432</p>
                            <span class="text-[11px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">High priority</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Buyer: Maria Reyes vs Seller: TechHub PH</p>
                        <p class="text-xs text-slate-400 mt-1">"Item received ay iba sa na-order — mali ang model." Filed 32 minutes ago.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View Details
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Mediate
                    </button>
                </div>
            </div>

            {{-- ITEM 2 --}}
            <div class="flex items-start justify-between gap-4 px-5 py-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-yellow/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-navy">Order #10398</p>
                            <span class="text-[11px] font-semibold text-yellow-700 bg-yellow/20 px-2 py-0.5 rounded-full">Medium priority</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Buyer: Jonas Dela Cruz vs Seller: Aling Nena's Store</p>
                        <p class="text-xs text-slate-400 mt-1">Late delivery, hindi na-refund agad. Filed 2 hours ago.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View Details
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Mediate
                    </button>
                </div>
            </div>

            {{-- ITEM 3 --}}
            <div class="flex items-start justify-between gap-4 px-5 py-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-navy">Order #10375</p>
                            <span class="text-[11px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">High priority</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Buyer: Carla Mendoza vs Seller: TechHub PH</p>
                        <p class="text-xs text-slate-400 mt-1">Payment nakuha pero walang natanggap na item. Filed 5 hours ago.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View Details
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Mediate
                    </button>
                </div>
            </div>

        </div>

        {{-- ============ PAGINATION (static) ============ --}}
        <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100">
            <p class="text-xs text-slate-400">Showing 1–3 of 3</p>
            <div class="flex items-center gap-1">
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400" disabled>‹</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold bg-navy text-white">1</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400" disabled>›</button>
            </div>
        </div>

    </div>

@endsection