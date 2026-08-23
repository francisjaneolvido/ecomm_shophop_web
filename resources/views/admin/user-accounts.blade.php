@extends('admin.layout')

@section('title', 'User Accounts')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">User Accounts</h1>
        <p class="text-sm text-slate-500 mt-1">Buyer & Seller Account Management.</p>
    </div>

    {{-- ============ SEARCH + FILTER TABS ============ --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <button class="px-4 py-2 rounded-xl text-sm font-semibold bg-navy text-white">
                All <span class="ml-1 opacity-70">(8,542)</span>
            </button>
            <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">
                Buyers
            </button>
            <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">
                Sellers
            </button>
            <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">
                Suspended
            </button>
        </div>

        <div class="relative w-full sm:w-64">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" placeholder="Search by name or email..."
                class="w-full pl-9 pr-4 py-2 text-sm rounded-xl bg-white border border-slate-200 focus:border-mint focus:outline-none focus:ring-2 focus:ring-mint/20 transition">
        </div>
    </div>

    {{-- ============ USER ACCOUNTS TABLE ============ --}}
    {{-- Palitan mo na lang ito ng @foreach loop galing sa $users mo sa controller --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">User</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Role</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Joined</th>
                        <th class="text-right font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">

                    {{-- ROW 1 --}}
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-mint/15 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-mint-dark">MR</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-navy truncate">Maria Reyes</p>
                                    <p class="text-xs text-slate-500 truncate">maria.reyes@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-semibold text-sky bg-sky/10 px-2.5 py-1 rounded-full">Buyer</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-mint-dark">
                                <span class="w-1.5 h-1.5 rounded-full bg-mint-dark"></span> Active
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">Jan 14, 2026</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                                    View
                                </button>
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                                    Suspend
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- ROW 2 --}}
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-coral/15 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-coral">TH</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-navy truncate">TechHub PH</p>
                                    <p class="text-xs text-slate-500 truncate">techhubph@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-semibold text-coral bg-coral/10 px-2.5 py-1 rounded-full">Seller</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-mint-dark">
                                <span class="w-1.5 h-1.5 rounded-full bg-mint-dark"></span> Active
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">Nov 2, 2025</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                                    View
                                </button>
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                                    Suspend
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- ROW 3 (suspended example) --}}
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-slate-500">JD</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-navy truncate">Jonas Dela Cruz</p>
                                    <p class="text-xs text-slate-500 truncate">jonas.dc@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-semibold text-sky bg-sky/10 px-2.5 py-1 rounded-full">Buyer</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-coral">
                                <span class="w-1.5 h-1.5 rounded-full bg-coral"></span> Suspended
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">Aug 20, 2025</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                                    View
                                </button>
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                                    Reactivate
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        {{-- ============ PAGINATION (static) ============ --}}
        <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100">
            <p class="text-xs text-slate-400">Showing 1–3 of 8,542</p>
            <div class="flex items-center gap-1">
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400">‹</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold bg-navy text-white">1</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50">2</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50">3</button>
                <button class="w-8 h-8 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50">›</button>
            </div>
        </div>

    </div>

@endsection