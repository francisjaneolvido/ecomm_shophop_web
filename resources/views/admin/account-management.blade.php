@extends('admin.layout')

@section('title', 'Account Management')

@section('content')

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-navy">Account Management</h1>
            <p class="text-sm text-slate-500 mt-1">Pamahalaan ang mga admin at staff accounts ng ShopHop.</p>
        </div>
        <button class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-mint-dark hover:opacity-90 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Admin Account
        </button>
    </div>

    {{-- ============ STAT SUMMARY ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">6</p>
                    <p class="text-xs text-slate-500">Total Admin Accounts</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-sky" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-navy">5</p>
                    <p class="text-xs text-slate-500">Active</p>
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
                    <p class="text-xl font-bold text-navy">1</p>
                    <p class="text-xs text-slate-500">Disabled</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ============ ADMIN ACCOUNTS TABLE ============ --}}
    {{-- Palitan mo na lang ito ng @foreach loop galing sa $admins mo sa controller --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Admin</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Role</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-left font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Last Active</th>
                        <th class="text-right font-semibold text-slate-500 px-5 py-3 text-xs uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">

                    {{-- ROW 1 (you) --}}
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-mint to-sky flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-navy">CJ</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-navy truncate">Carl Jasper <span class="text-[10px] font-semibold text-mint-dark bg-mint/10 px-1.5 py-0.5 rounded-full ml-1">You</span></p>
                                    <p class="text-xs text-slate-500 truncate">admin@shophop.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-semibold text-navy bg-navy/10 px-2.5 py-1 rounded-full">Super Admin</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-mint-dark">
                                <span class="w-1.5 h-1.5 rounded-full bg-mint-dark"></span> Active
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">Just now</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- ROW 2 --}}
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-sky/15 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-sky">LP</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-navy truncate">Liza Padilla</p>
                                    <p class="text-xs text-slate-500 truncate">liza.padilla@shophop.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-semibold text-sky bg-sky/10 px-2.5 py-1 rounded-full">Compliance Officer</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-mint-dark">
                                <span class="w-1.5 h-1.5 rounded-full bg-mint-dark"></span> Active
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">1 hour ago</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                                    Edit
                                </button>
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                                    Disable
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- ROW 3 --}}
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-yellow/20 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-yellow-700">RM</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-navy truncate">Ramon Mercado</p>
                                    <p class="text-xs text-slate-500 truncate">ramon.mercado@shophop.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-semibold text-coral bg-coral/10 px-2.5 py-1 rounded-full">Support Staff</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-mint-dark">
                                <span class="w-1.5 h-1.5 rounded-full bg-mint-dark"></span> Active
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">4 hours ago</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                                    Edit
                                </button>
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-coral border border-coral/30 hover:bg-coral/5">
                                    Disable
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- ROW 4 (disabled example) --}}
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-slate-500">EF</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-navy truncate">Edwin Flores</p>
                                    <p class="text-xs text-slate-500 truncate">edwin.flores@shophop.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-semibold text-coral bg-coral/10 px-2.5 py-1 rounded-full">Support Staff</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Disabled
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">2 weeks ago</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200 hover:bg-slate-50">
                                    Edit
                                </button>
                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-mint-dark hover:opacity-90">
                                    Enable
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

    </div>

@endsection