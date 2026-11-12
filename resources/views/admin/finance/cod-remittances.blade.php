@extends('admin.layout')
@section('title', 'COD Cash Remittances')
@section('content')
<div class="space-y-4">
    <header class="sh-page-header">
        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-wider text-teal-dark">Admin / Finance</p>
            <h1 class="mt-1 font-bold text-navy">COD Cash Remittances</h1>
            <p class="mt-1 max-w-3xl text-xs leading-5 text-slate-500">Review actual COD custody before settlement. Receiving money is a separate verification from a Logistics remittance entry.</p>
        </div>
        <a href="{{ route('admin.commission') }}" class="admin-btn">Back to Finance</a>
    </header>
    @if(session('status'))<div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-800">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-800">{{ $errors->first() }}</div>@endif

    <a class="admin-btn" href="{{ route('hybrid.discrepancies.index') }}">Review COD discrepancies</a>
    <section class="admin-panel">
        <nav class="sh-console-tabs" aria-label="COD receipt status">
            @foreach(['all' => 'All records', 'awaiting' => 'To verify', 'with_logistics' => 'With logistics', 'received' => 'Received'] as $value => $label)
                <a href="{{ route('admin.finance.cod.index', ['status' => $value, 'q' => $search]) }}" @if($status === $value) aria-current="page" @endif>{{ $label }} <span class="sh-tab-count">{{ $counts[$value] }}</span></a>
            @endforeach
        </nav>
        <form class="ops-filters" method="GET" action="{{ route('admin.finance.cod.index') }}" role="search">
            <input type="hidden" name="status" value="{{ $status }}">
            <label class="sh-field"><span>Order, logistics company or reference</span><input type="search" class="sh-input" name="q" value="{{ $search }}" maxlength="120" placeholder="Search remittances"></label>
            <button type="submit" class="sh-btn">Filter</button><a href="{{ route('admin.finance.cod.index', ['status' => $status]) }}" class="sh-btn-soft">Clear</a>
        </form>

        <div class="admin-panel-header">
            <div><h2 class="font-bold text-sm text-navy">Remittance directory</h2><p class="mt-1 text-xs text-slate-500">Verified receipts and pending Logistics remittances</p></div>
            <span class="text-xs text-slate-500">{{ $settlements->firstItem() ?? 0 }}–{{ $settlements->lastItem() ?? 0 }} of {{ $settlements->total() }}</span>
        </div>
        <div class="admin-table-scroll">
            <table class="min-w-[920px]">
                <thead><tr><th>Order / Logistics</th><th class="sh-money">Amount due</th><th>Remittance reference</th><th>Cash status</th><th class="sh-money">Admin received</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($settlements as $cash)
                    @php
                        $orderSettlements = $cash->order?->settlements ?? collect();
                        $hasPrepared = $orderSettlements->isNotEmpty();
                        $hasPending = $orderSettlements->where('status', 'pending')->isNotEmpty();
                        $hasAvailable = $orderSettlements->where('status', 'available')->isNotEmpty();
                        $allPaid = $hasPrepared && $orderSettlements->every(fn ($entry) => $entry->status === 'paid');
                    @endphp
                    <tr>
                        <td><strong class="text-navy">Order #{{ $cash->order_id }}</strong><div class="mt-1 text-[11px] text-slate-500">{{ $cash->partner?->company_name ?? 'Logistics partner' }}</div></td>
                        <td class="sh-money font-semibold tabular-nums">₱{{ number_format((float) $cash->expected_amount, 2) }}</td>
                        <td class="break-all text-xs text-slate-600">{{ $cash->admin_remittance_reference ?: '—' }}</td>
                        <td>
                            @if($cash->admin_received_at)<span class="admin-status admin-status-ok">Received</span>
                            @elseif($cash->admin_remitted_at)<span class="admin-status admin-status-info">Awaiting verification</span>
                            @else<span class="admin-status admin-status-warn">With Logistics</span>@endif
                        </td>
                        <td class="sh-money tabular-nums">
                            @if($cash->admin_received_at)
                                <strong>₱{{ number_format((float) $cash->admin_received_amount, 2) }}</strong>
                                <div class="mt-1 text-[11px] text-slate-500">{{ $cash->admin_received_at->format('M j, Y g:i A') }}</div>
                            @else<span class="text-slate-400">Not confirmed</span>@endif
                        </td>
                        <td>
                            <div class="admin-row-actions">
                            @if($reviewCase = $openCases->get($cash->id))
                                <a data-console-details data-console-detail-target="case-{{ $reviewCase->id }}" href="{{ route('hybrid.discrepancies.index', ['case' => $reviewCase->id, 'status' => 'all']) }}#case-{{ $reviewCase->id }}" class="admin-btn">Resolve COD case first</a>
                            @elseif($cash->admin_received_at)
                                @if(!$hasPrepared)
                                <form method="POST" action="{{ route('admin.finance.prepare', $cash->order_id) }}" onsubmit="return confirm('Prepare the settlement after checking the confirmed COD amount?')">@csrf<button type="submit" class="admin-btn admin-btn-primary">Prepare settlement</button></form>
                                @elseif($hasPending)
                                <a href="{{ route('admin.commission') }}#distribution-queue" class="admin-btn">Review distribution</a>
                                @elseif($hasAvailable)
                                <a href="{{ route('admin.commission') }}#available-payouts" class="admin-btn">Available payouts</a>
                                @elseif($allPaid)
                                <span class="admin-status admin-status-ok">Fully paid</span>
                                @endif
                            @elseif($cash->admin_remitted_at)
                                <button type="button" class="admin-btn admin-btn-teal" data-admin-dialog-open="cod-confirm-{{ $cash->id }}">Confirm cash received</button>
                            @else
                                <span class="text-xs text-slate-500">Await Logistics</span>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="admin-empty">No reconciled COD records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-border px-4 py-3">{{ $settlements->links() }}</div>
    </section>

    {{-- Only an explicit user action opens a sensitive cash-confirmation form. --}}
    @foreach($settlements as $cash)
        @if(!$cash->admin_received_at && $cash->admin_remitted_at && !$openCases->has($cash->id))
        <dialog class="admin-dialog" id="cod-confirm-{{ $cash->id }}" aria-labelledby="cod-confirm-title-{{ $cash->id }}">
            <form method="POST" action="{{ route('admin.finance.cod.confirm', $cash) }}" class="admin-dialog-frame" onsubmit="return confirm('Confirm only after verifying the actual cash amount and reference. Continue?')">
                @csrf
                <div class="admin-dialog-header"><div><h2 id="cod-confirm-title-{{ $cash->id }}" class="font-bold text-base">Confirm COD receipt · Order #{{ $cash->order_id }}</h2><p class="mt-1 text-xs text-slate-500">Logistics reference: {{ $cash->admin_remittance_reference ?: 'Not supplied' }}</p></div><button type="button" data-admin-dialog-close class="admin-btn" aria-label="Close">✕</button></div>
                <div class="admin-dialog-body space-y-3">
                    <div class="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600">Expected amount due: <strong class="text-navy">₱{{ number_format((float)$cash->expected_amount, 2) }}</strong></div>
                    <label class="block"><span class="admin-label">Reference verified <span class="text-red-600">*</span></span><input name="reference" required maxlength="120" autocomplete="off" class="admin-input" placeholder="Enter verified remittance reference"></label>
                    <label class="block"><span class="admin-label">Amount actually received (PHP) <span class="text-red-600">*</span></span><input name="received_amount" type="number" step="0.01" min="0" required class="admin-input" placeholder="0.00"></label>
                    <p class="text-xs text-amber-700">This records an Admin-confirmed receipt. It does not initiate an electronic transfer.</p>
                </div>
                <div class="admin-dialog-footer"><button type="button" class="admin-btn" data-admin-dialog-close>Cancel</button><button type="submit" class="admin-btn admin-btn-teal">Confirm received</button></div>
            </form>
        </dialog>
        @endif
    @endforeach
</div>
@endsection
