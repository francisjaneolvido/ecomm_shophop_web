@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-navy">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-1">Platform overview at notifications.</p>
    </div>

    {{-- ============ STAT CARDS ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                @if ($pendingRegistrations > 0)
                    <span class="text-[11px] font-semibold text-mint-dark bg-mint/10 px-2 py-0.5 rounded-full">Needs review</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-navy">{{ number_format($pendingRegistrations) }}</p>
            <p class="text-xs text-slate-500 mt-1">Pending Registrations</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-sky/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-sky" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-navy">{{ number_format($activeUserAccounts) }}</p>
            <p class="text-xs text-slate-500 mt-1">Active User Accounts</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-coral/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                @if ($openDisputes > 0)
                    <span class="text-[11px] font-semibold text-coral bg-coral/10 px-2 py-0.5 rounded-full">Urgent</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-navy">{{ number_format($openDisputes) }}</p>
            <p class="text-xs text-slate-500 mt-1">Open Complaints/Disputes</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-yellow/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 2v8m0 0v2m0-2c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-navy">₱{{ number_format($commissionThisMonth) }}</p>
            <p class="text-xs text-slate-500 mt-1">Commission This Month (10%)</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ============ NOTIFICATIONS / RECENT ACTIVITY ============ --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-navy text-sm">Recent Registrations</h2>
                <a href="{{ route('admin.users') }}" class="text-xs font-medium text-mint-dark hover:underline">View all</a>
            </div>

            <div class="divide-y divide-slate-100">

                @forelse ($recentRegistrations as $user)
                    @php
                        $iconColors = [
                            'buyer' => ['bg-sky/15', 'text-sky'],
                            'seller' => ['bg-mint/15', 'text-mint-dark'],
                            'logistics' => ['bg-yellow/20', 'text-yellow-600'],
                        ];
                        [$iconBg, $iconColor] = $iconColors[$user->account_type] ?? ['bg-slate-100', 'text-slate-500'];
                    @endphp
                    <div class="flex items-start gap-3 px-5 py-4">
                        <div class="w-9 h-9 shrink-0 rounded-full {{ $iconBg }} flex items-center justify-center">
                            <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-navy">
                                <span class="font-semibold">New {{ $user->account_type }} registration</span>
                                — "{{ $user->display_name }}" submitted an application ({{ ucfirst($user->status) }})
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-400">
                        No registrations yet.
                    </div>
                @endforelse

            </div>
        </div>

        {{-- ============ QUICK ACTIONS ============ --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-navy text-sm mb-4">Quick Actions</h2>

            <div class="space-y-2">
                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-slate-100 hover:border-mint hover:bg-mint/5 transition group">
                    <div class="w-8 h-8 rounded-lg bg-mint/15 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-mint-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-navy">Review Registrations</span>
                </a>

                <a href="{{ route('admin.disputes') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-slate-100 hover:border-coral hover:bg-coral/5 transition group">
                    <div class="w-8 h-8 rounded-lg bg-coral/15 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-navy">Resolve Disputes</span>
                </a>

                <a href="{{ route('admin.reports') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-slate-100 hover:border-yellow hover:bg-yellow/5 transition group">
                    <div class="w-8 h-8 rounded-lg bg-yellow/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0H5a2 2 0 01-2-2V5a2 2 0 012-2h10l4 4v8a2 2 0 01-2 2h-2" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-navy">Generate Report</span>
                </a>

                <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-slate-100 hover:border-sky hover:bg-sky/5 transition group">
                    <div class="w-8 h-8 rounded-lg bg-sky/15 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-sky" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-navy">Platform Settings</span>
                </a>
            </div>
        </div>

    </div>

@endsection