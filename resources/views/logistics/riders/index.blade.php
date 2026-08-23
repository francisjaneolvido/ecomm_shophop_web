{{-- resources/views/logistics/riders/index.blade.php --}}

@extends('layouts.logistics')

@section('title', 'Riders — ShopHop Logistics')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-navy text-2xl sm:text-3xl font-bold">Riders</h1>
        <p class="text-navy/55 text-sm mt-1">Review applications and manage your active fleet.</p>
    </div>
</div>

{{-- =========================================================
    TABS
========================================================= --}}
<div class="flex gap-5 mb-6 border-b border-gray-border overflow-x-auto whitespace-nowrap">
    <button type="button" data-tab-btn="applications" class="shrink-0 px-1 pb-3 -mb-px text-sm font-semibold border-b-2 border-teal text-teal-dark">
        Pending applications
        <span class="ml-1 text-[11px] font-bold bg-red-50 text-red-600 px-2 py-0.5 rounded-full">{{ count($applications) }}</span>
    </button>
    <button type="button" data-tab-btn="active" class="shrink-0 px-1 pb-3 -mb-px text-sm font-semibold border-b-2 border-transparent text-navy/50 hover:text-navy transition">
        Active riders
        <span class="ml-1 text-[11px] font-bold bg-gray-bg text-navy/50 px-2 py-0.5 rounded-full">{{ count($activeRiders) }}</span>
    </button>
</div>

{{-- =========================================================
    APPLICATIONS TAB
========================================================= --}}
<div data-tab-panel="applications" class="bg-white border border-gray-border rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] font-semibold text-navy/45 uppercase tracking-wide bg-gray-bg">
                    <th class="px-6 py-3">Applicant</th>
                    <th class="px-6 py-3">Vehicle</th>
                    <th class="px-6 py-3">Documents</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-border">
                @forelse ($applications as $rider)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-navy whitespace-nowrap">{{ $rider['name'] }}</td>
                        <td class="px-6 py-4 text-navy/60 whitespace-nowrap">{{ $rider['vehicle'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex gap-1.5 flex-wrap">
                                @foreach ($rider['docs'] as $doc => $ok)
                                    <span class="text-[10px] font-semibold px-2 py-1 rounded-md whitespace-nowrap {{ $ok ? 'bg-gray-bg text-navy/70' : 'border border-red-200 text-red-600' }}">
                                        {{ $doc }} {{ $ok ? '✓' : 'missing' }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                @if (collect($rider['docs'])->every(fn ($ok) => $ok))
                                    <form method="POST" action="{{ route('logistics.riders.approve', $rider['id']) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold bg-teal hover:bg-teal-dark text-white px-3.5 py-1.5 rounded-full transition whitespace-nowrap">
                                            Approve
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('logistics.riders.disapprove', $rider['id']) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:bg-red-50 border border-red-200 px-3.5 py-1.5 rounded-full transition whitespace-nowrap">
                                            Disapprove
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="text-xs font-semibold text-navy border border-navy/20 hover:bg-gray-bg px-3.5 py-1.5 rounded-full transition whitespace-nowrap">
                                    Review
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-10 text-center text-navy/40 text-sm">No pending applications right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- =========================================================
    ACTIVE RIDERS TAB
========================================================= --}}
<div data-tab-panel="active" class="hidden bg-white border border-gray-border rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] font-semibold text-navy/45 uppercase tracking-wide bg-gray-bg">
                    <th class="px-6 py-3">Rider</th>
                    <th class="px-6 py-3">Zone</th>
                    <th class="px-6 py-3">Completion</th>
                    <th class="px-6 py-3">Rating</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-border">
                @forelse ($activeRiders as $rider)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-semibold text-navy">{{ $rider['name'] }}</p>
                            <p class="text-xs text-navy/45">{{ $rider['vehicle'] }}</p>
                        </td>
                        <td class="px-6 py-4 text-navy/60 whitespace-nowrap">{{ $rider['zone'] }}</td>
                        <td class="px-6 py-4 text-navy/60">{{ $rider['completion'] }}%</td>
                        <td class="px-6 py-4 text-navy/60">★ {{ number_format($rider['rating'], 1) }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap {{ $rider['status'] === 'active' ? 'bg-teal-light text-teal-dark' : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($rider['status']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form method="POST" action="{{ route($rider['status'] === 'active' ? 'logistics.riders.suspend' : 'logistics.riders.activate', $rider['id']) }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold border border-navy/20 hover:bg-gray-bg text-navy px-3.5 py-1.5 rounded-full transition whitespace-nowrap">
                                    {{ $rider['status'] === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-navy/40 text-sm">No active riders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('[data-tab-btn]');
        const panels = document.querySelectorAll('[data-tab-panel]');

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(function (b) {
                    b.classList.remove('border-teal', 'text-teal-dark');
                    b.classList.add('border-transparent', 'text-navy/50');
                });
                btn.classList.add('border-teal', 'text-teal-dark');
                btn.classList.remove('border-transparent', 'text-navy/50');

                panels.forEach(function (panel) {
                    panel.classList.toggle('hidden', panel.dataset.tabPanel !== btn.dataset.tabBtn);
                });
            });
        });
    });
</script>

@endsection
