@extends('admin.layout')

@section('title', 'Reports')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Reports</h1>
        <p class="text-sm text-slate-500 mt-1">Bumuo at tingnan ang mga platform reports.</p>
    </div>

    {{-- ============ GENERATE REPORT PANEL ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
        <h2 class="font-semibold text-navy text-sm mb-4">Generate New Report</h2>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">

            <div>
                <label class="text-xs font-medium text-slate-500 mb-1 block">Report Type</label>
                <select class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
                    <option>Sales Summary</option>
                    <option>Commission Breakdown</option>
                    <option>Seller Performance</option>
                    <option>User Growth</option>
                    <option>Disputes Log</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-slate-500 mb-1 block">Date From</label>
                <input type="date" class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
            </div>

            <div>
                <label class="text-xs font-medium text-slate-500 mb-1 block">Date To</label>
                <input type="date" class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20">
            </div>

            <div class="flex items-end">
                <button class="w-full px-4 py-2 rounded-xl text-sm font-semibold text-white bg-mint-dark hover:opacity-90 transition">
                    Generate Report
                </button>
            </div>

        </div>
    </div>

    {{-- ============ GENERATED REPORTS LIST ============ --}}
    {{-- Palitan mo na lang ito ng @foreach loop galing sa $reports mo sa controller --}}
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-navy text-sm">Recent Reports</h2>
            <span class="text-xs text-slate-400">6 total</span>
        </div>

        <div class="divide-y divide-slate-100">

            {{-- ITEM 1 --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-yellow/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h10l4 4v8a2 2 0 01-2 2h-2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v4h4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">Monthly Sales Summary — August 2026</p>
                        <p class="text-xs text-slate-400 mt-0.5">Generated 3 hours ago · PDF</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Download
                    </button>
                </div>
            </div>

            {{-- ITEM 2 --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h10l4 4v8a2 2 0 01-2 2h-2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v4h4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">Commission Breakdown — July 2026</p>
                        <p class="text-xs text-slate-400 mt-0.5">Generated 2 days ago · CSV</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Download
                    </button>
                </div>
            </div>

            {{-- ITEM 3 --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-sky/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-sky" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h10l4 4v8a2 2 0 01-2 2h-2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v4h4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">Seller Performance — Q2 2026</p>
                        <p class="text-xs text-slate-400 mt-0.5">Generated 1 week ago · PDF</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Download
                    </button>
                </div>
            </div>

            {{-- ITEM 4 --}}
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h10l4 4v8a2 2 0 01-2 2h-2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v4h4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-navy truncate">Disputes Log — July 2026</p>
                        <p class="text-xs text-slate-400 mt-0.5">Generated 2 weeks ago · CSV</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                        View
                    </button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                        Download
                    </button>
                </div>
            </div>

        </div>

        {{-- ============ PAGINATION (static) ============ --}}
        <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100">
            <p class="text-xs text-slate-400">Showing 1–4 of 6</p>
            <div class="flex items-center gap-1">
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400" disabled>‹</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold bg-navy text-white">1</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50">2</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50">›</button>
            </div>
        </div>

    </div>

@endsection