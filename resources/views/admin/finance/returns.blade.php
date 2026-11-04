@extends('admin.layout')
@section('title', 'Returns & Manual Refunds')
@section('content')
<div class="space-y-4">
    <header class="flex flex-wrap justify-between items-start gap-3">
        <div><p class="text-[10px] uppercase tracking-wider font-bold text-teal-dark">Admin / Finance</p><h1 class="mt-1 font-bold">Returns & Manual Refunds</h1><p class="mt-1 max-w-3xl text-xs leading-5 text-slate-500">Refunds are recorded only after Seller confirms return receipt. No automatic refund transfer occurs.</p></div>
        <a href="{{route('admin.commission')}}" class="admin-btn">Back to Finance</a>
    </header>
    @if(session('status'))<div role="status" class="rounded-lg border border-teal-200 bg-teal-50 p-3 text-xs text-teal-800">{{session('status')}}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-800">{{$errors->first()}}</div>@endif
    <section class="admin-panel">
        <div class="admin-panel-header"><div><h2 class="text-sm font-bold">Return case directory</h2><p class="mt-1 text-xs text-slate-500">Payment/refund status and authorized manual actions</p></div><span class="text-xs text-slate-500">{{ $cases->firstItem() ?? 0 }}–{{ $cases->lastItem() ?? 0 }} of {{ $cases->total() }}</span></div>
        <div class="admin-table-scroll"><table class="min-w-[850px]">
            <thead><tr><th>Order / Seller</th><th>Return reason</th><th>Payment</th><th>Refund required</th><th>Return / Refund status</th><th class="text-right">Action</th></tr></thead>
            <tbody>
            @forelse($cases as $case)
            <tr>
                <td><strong>Order #{{$case->order_id}}</strong><div class="mt-1 text-[11px] text-slate-500">{{$case->order?->seller?->business_name ?? 'Seller not available'}}</div></td>
                <td class="text-xs text-slate-600">{{$case->reason ?: '—'}}</td>
                <td>{{ strtoupper($case->order?->payment_method ?? '—') }}</td>
                <td class="font-semibold tabular-nums">₱{{number_format((float)$case->refund_amount,2)}}</td>
                <td><span class="admin-status {{ $case->refund_status === 'paid' ? 'admin-status-ok' : 'admin-status-warn' }}">{{ ucfirst(str_replace('_', ' ', $case->refund_status)) }}</span><div class="mt-1 text-[11px] text-slate-500">{{ ucwords(str_replace('_', ' ', $case->status)) }}</div></td>
                <td><div class="admin-row-actions">
                @if($case->status === \App\Models\Logistics\ReturnCase::SELLER_RECEIVED && $case->refund_status === 'pending')
                    <button type="button" class="admin-btn admin-btn-teal" data-admin-dialog-open="refund-record-{{$case->id}}">Record refund</button>
                @elseif($case->refund_status === 'paid')
                    <span class="text-xs text-teal-dark">{{ $case->refund_reference ?: 'Paid' }} · {{ $case->refunded_at?->format('M j, Y') }}</span>
                @else
                    <span class="text-xs text-slate-500">Await seller receipt</span>
                @endif
                </div></td>
            </tr>
            @empty<tr><td colspan="6" class="admin-empty">No return cases have been created.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="border-t border-gray-border px-4 py-3">{{$cases->links()}}</div>
    </section>
    @foreach($cases as $case)
        @if($case->status === \App\Models\Logistics\ReturnCase::SELLER_RECEIVED && $case->refund_status === 'pending')
        <dialog class="admin-dialog" id="refund-record-{{$case->id}}" aria-labelledby="refund-title-{{$case->id}}">
            <form method="POST" action="{{route('admin.returns.refund-paid',$case)}}" class="admin-dialog-frame" onsubmit="return confirm('Record this manual refund as paid? This action does not transfer money.')">
                @csrf
                <div class="admin-dialog-header"><div><h2 id="refund-title-{{$case->id}}" class="text-base font-bold">Record Manual Refund</h2><p class="mt-1 text-xs text-slate-500">Order #{{$case->order_id}}</p></div><button type="button" class="admin-btn" data-admin-dialog-close aria-label="Close">✕</button></div>
                <div class="admin-dialog-body space-y-3"><p class="rounded-md bg-slate-50 p-3 text-xs">Required refund: <strong>₱{{number_format((float)$case->refund_amount,2)}}</strong></p><label class="block"><span class="admin-label">Manual refund transaction reference <span class="text-red-600">*</span></span><input name="reference" required minlength="4" maxlength="120" class="admin-input" placeholder="DEMO-REF-0001"></label><p class="text-xs text-amber-700">Verify the refund payment externally before recording it.</p></div>
                <div class="admin-dialog-footer"><button type="button" class="admin-btn" data-admin-dialog-close>Cancel</button><button type="submit" class="admin-btn admin-btn-teal">Record refund paid</button></div>
            </form>
        </dialog>
        @endif
    @endforeach
</div>
@endsection
