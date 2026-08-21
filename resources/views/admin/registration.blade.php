@extends('admin.layout')

@section('title', 'Account Registrations')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Account Registrations</h1>
        <p class="text-sm text-slate-500 mt-1">Review at aprubahan ang mga bagong seller applications.</p>
    </div>

    {{-- ============ FILTER TABS ============ --}}
    <div class="flex items-center gap-2 mb-5">
        <button class="px-4 py-2 rounded-xl text-sm font-semibold bg-navy text-white">
            Pending <span class="ml-1 opacity-70">(14)</span>
        </button>
        <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">
            Approved
        </button>
        <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">
            Rejected
        </button>
    </div>

    {{-- ============ REGISTRATIONS LIST ============ --}}
    {{-- Palitan mo na lang ito ng @foreach loop galing sa $registrations mo sa controller --}}
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">Pending Applications</h2>
            <span class="text-xs text-slate-400">14 total</span>
        </div>

        <div class="divide-y divide-slate-100">

            {{-- ITEM 1 --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-mint-dark">AN</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">Aling Nena's Store</p>
                        <p class="text-xs text-slate-500">Seller · nena.store@email.com</p>
                        <p class="text-xs text-slate-400 mt-0.5">Applied 5 minutes ago</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Approve
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                        Reject
                    </button>
                </div>
            </div>

            {{-- ITEM 2 --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-sky/15 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-sky">TH</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">TechHub PH</p>
                        <p class="text-xs text-slate-500">Seller · techhubph@email.com</p>
                        <p class="text-xs text-slate-400 mt-0.5">Applied 40 minutes ago</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Approve
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                        Reject
                    </button>
                </div>
            </div>

            {{-- ITEM 3 --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-coral">JR</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">Jomar's Repair Shop</p>
                        <p class="text-xs text-slate-500">Seller · jomarrepair@email.com</p>
                        <p class="text-xs text-slate-400 mt-0.5">Applied 2 hours ago</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Approve
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                        Reject
                    </button>
                </div>
            </div>

        </div>

        {{-- ============ PAGINATION (static) ============ --}}
        <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100">
            <p class="text-xs text-slate-400">Showing 1–3 of 14</p>
            <div class="flex items-center gap-1">
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400">‹</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold bg-navy text-white">1</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50">2</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50">›</button>
            </div>
        </div>

    </div>

@endsection