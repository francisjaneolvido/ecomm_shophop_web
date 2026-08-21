@extends('admin.layout')

@section('title', 'Seller Compliance')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Seller Compliance</h1>
        <p class="text-sm text-slate-500 mt-1">I-verify ang mga seller documents at product compliance.</p>
    </div>

    {{-- ============ STAT SUMMARY ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">312</p>
                    <p class="text-xs text-slate-500">Verified Sellers</p>
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
                    <p class="text-xl font-bold text-navy">19</p>
                    <p class="text-xs text-slate-500">Pending Review</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">4</p>
                    <p class="text-xs text-slate-500">Flagged / Non-compliant</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ============ FILTER TABS ============ --}}
    <div class="flex items-center gap-2 mb-5">
        <button class="px-4 py-2 rounded-xl text-sm font-semibold bg-navy text-white">
            Pending Review <span class="ml-1 opacity-70">(19)</span>
        </button>
        <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">
            Verified
        </button>
        <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">
            Flagged
        </button>
    </div>

    {{-- ============ COMPLIANCE LIST ============ --}}
    {{-- Palitan mo na lang ito ng @foreach loop galing sa $sellers mo sa controller --}}
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">Sellers Awaiting Review</h2>
            <span class="text-xs text-slate-400">19 total</span>
        </div>

        <div class="divide-y divide-slate-100">

            {{-- ITEM 1 --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-sky/15 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-sky">TH</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">TechHub PH</p>
                        <p class="text-xs text-slate-500">DTI Permit, BIR Form 2303 submitted</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[11px] font-semibold text-yellow-700 bg-yellow/20 px-2 py-0.5 rounded-full">Documents under review</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View Documents
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Verify
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                        Flag
                    </button>
                </div>
            </div>

            {{-- ITEM 2 --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-mint-dark">AN</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">Aling Nena's Store</p>
                        <p class="text-xs text-slate-500">Barangay Business Permit submitted</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[11px] font-semibold text-yellow-700 bg-yellow/20 px-2 py-0.5 rounded-full">Documents under review</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View Documents
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Verify
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                        Flag
                    </button>
                </div>
            </div>

            {{-- ITEM 3 (flagged example) --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-coral">JR</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">Jomar's Repair Shop</p>
                        <p class="text-xs text-slate-500">Missing DTI/BIR documents</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[11px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">Incomplete submission</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View Documents
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Verify
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                        Flag
                    </button>
                </div>
            </div>

        </div>

        {{-- ============ PAGINATION (static) ============ --}}
        <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100">
            <p class="text-xs text-slate-400">Showing 1–3 of 19</p>
            <div class="flex items-center gap-1">
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400">‹</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold bg-navy text-white">1</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50">2</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50">›</button>
            </div>
        </div>

    </div>

@endsection