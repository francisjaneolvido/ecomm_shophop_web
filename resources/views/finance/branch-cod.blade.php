@php
    $cashView = request('cash_view', $batches->total() ? 'branch' : 'company');
    $cashView = in_array($cashView, ['branch','company'], true) ? $cashView : 'branch';
    $money = fn ($minor) => number_format($minor / 100, 2);
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->timezone('Asia/Manila')->format('M j, Y · g:i A').' PHT' : 'Not recorded';
@endphp
<div class="space-y-4 sh-finance-ops">
    <header class="sh-page-header"><div><p class="eyebrow">Station Branch · Finance</p><h1 class="title">COD Verification</h1><p class="description">Review rider handovers and confirm the cash physically received at your Branch.</p></div>
        <a class="sh-btn-soft" href="{{ route('logistics.hybrid.discrepancies.index') }}">Discrepancy review</a></header>
    @if(session('status'))<div role="status" class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <p class="sh-notice">Rider hands over the full COD → Branch counts and verifies → Logistics remits to ShopHop. Rider earnings are paid separately.</p>
    <section class="ops-panel">
        <nav class="sh-console-tabs" aria-label="Cash receipt responsibility">
            <a href="{{ request()->fullUrlWithQuery(['cash_view'=>'branch','page'=>null,'handovers_page'=>null]) }}" @if($cashView==='branch') aria-current="page" @endif>Branch verification <span class="sh-tab-count">{{ $batches->total() }}</span></a>
            <a href="{{ request()->fullUrlWithQuery(['cash_view'=>'company','page'=>null,'handovers_page'=>null]) }}" @if($cashView==='company') aria-current="page" @endif>Company review <span class="sh-tab-count">{{ $directHandovers->total() }}</span></a>
        </nav>
        <div class="ops-header"><div><h2>{{ $cashView==='branch' ? 'Cash handovers to your Branch' : 'For Logistics company review' }}</h2><p class="ops-panel-note">{{ $cashView==='branch' ? 'Open a handover and enter the actual amount counted for each collection.' : 'No receiving Branch was recorded for these earlier handovers. The Logistics company verifies them.' }}</p></div></div>
        <div class="ops-table-scroll"><table class="ops-table">
        @if($cashView==='branch')
            <thead><tr><th scope="col">Rider / Reference</th><th scope="col" class="ops-money">Expected</th><th scope="col" class="ops-money">Submitted</th><th scope="col" class="ops-money">Verified</th><th scope="col" class="ops-money">Difference</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>
            @forelse($batches as $batch)<tr>
                <td><strong>{{ $riderNames->get($batch->rider_id, 'Rider #'.$batch->rider_id) }}</strong><div class="ops-row-sub">{{ $batch->reference }}</div><div class="ops-row-sub">{{ $date($batch->created_at) }}</div></td>
                <td class="ops-money">₱{{ $money($batch->expected_minor) }}</td><td class="ops-money">₱{{ $money($batch->submitted_minor) }}</td>
                <td class="ops-money">{{ $batch->verified_minor === null ? 'Pending' : '₱'.$money($batch->verified_minor) }}</td>
                <td class="ops-money">{{ $batch->difference_minor === null ? '—' : '₱'.$money($batch->difference_minor) }}</td>
                <td><span class="sh-badge {{ $batch->status === 'discrepancy' ? 'sh-badge-warning' : ($batch->verified_at ? 'sh-badge-success' : 'sh-badge-info') }}">{{ $batch->status === 'discrepancy' ? 'Under review' : ($batch->verified_at ? 'Receipt verified' : 'Needs verification') }}</span></td>
                <td class="ops-row-actions"><button class="ops-action" type="button" id="handover-{{ $batch->id }}" data-ops-template="branch-cash-{{ $batch->id }}" data-ops-title="Handover · {{ $batch->reference }}" @if((string)old('ui_record') === 'handover-'.$batch->id) data-reopen-record @endif>{{ $batch->verified_at ? 'View receipt' : 'Verify cash' }}</button></td>
            </tr>@empty<tr><td colspan="7" class="ops-empty"><strong>No cash handovers to verify.</strong><div class="ops-row-sub">Handovers addressed to your Branch will appear here.</div></td></tr>@endforelse
            </tbody>
        @else
            <thead><tr><th scope="col">Order / Tracking</th><th scope="col">Rider</th><th scope="col" class="ops-money">Collected</th><th scope="col" class="ops-money">Verified received</th><th scope="col">Next actor</th></tr></thead><tbody>
            @forelse($directHandovers as $cash)<tr><td><strong>Order #{{ $cash->order_id }}</strong><div class="ops-row-sub">{{ $cash->delivery?->tracking_code ?: 'Tracking unavailable' }}</div></td><td>{{ $cash->remitter?->name ?: 'Rider unavailable' }}</td><td class="ops-money">₱{{ number_format((float)$cash->collected_amount,2) }}</td><td class="ops-money">{{ $cash->received_amount === null ? 'Pending' : '₱'.number_format((float)$cash->received_amount,2) }}</td><td><span class="sh-badge {{ $cash->reconciled_at ? 'sh-badge-success' : 'sh-badge-info' }}">{{ $cash->reconciled_at ? 'Receipt verified' : 'Logistics company' }}</span></td></tr>
            @empty<tr><td colspan="5" class="ops-empty">No handovers for company review.</td></tr>@endforelse
            </tbody>
        @endif
        </table></div>
        <div class="ops-footer"><span>{{ $cashView==='branch' ? $batches->total() : $directHandovers->total() }} record(s)</span>{{ $cashView==='branch' ? $batches->appends(['cash_view'=>'branch'])->links() : $directHandovers->appends(['cash_view'=>'company'])->links() }}</div>
    </section>
    @foreach($batches as $batch)
    <template id="branch-cash-{{ $batch->id }}">
        <dl class="sh-detail-grid">
            <div><dt>Rider</dt><dd>{{ $riderNames->get($batch->rider_id, 'Rider #'.$batch->rider_id) }}</dd></div>
            <div><dt>Receiving Branch</dt><dd>{{ $centerNames->get($batch->sorting_center_id, 'Facility #'.$batch->sorting_center_id) }}</dd></div>
            <div><dt>Receipt evidence</dt><dd>{{ $batch->receipt_reference }}</dd></div>
            <div><dt>Submitted</dt><dd>{{ $date($batch->created_at) }}</dd></div>
            <div><dt>Verified by</dt><dd>{{ $receiverNames->get($batch->verified_by_user_id) ?: 'Awaiting verification' }}</dd></div>
            <div><dt>Verified at</dt><dd>{{ $date($batch->verified_at) }}</dd></div>
        </dl>
        @if(!$batch->verified_at)
            <p class="sh-notice">Count the cash before confirming. Enter the actual amount received for each collection.</p>
            <form method="POST" action="{{ route('logistics.hybrid.verify',$batch->id) }}" class="space-y-3">@csrf
                <input type="hidden" name="ui_record" value="handover-{{ $batch->id }}">
                @foreach($items->get($batch->id, collect()) as $i=>$item)
                    <input type="hidden" name="items[{{ $i }}][collection_id]" value="{{ $item->cod_settlement_id }}">
                    <label class="sh-field"><span class="sh-label">Collection #{{ $item->cod_settlement_id }} · Submitted ₱{{ $money($item->submitted_minor) }}</span><input class="sh-input" name="items[{{ $i }}][amount]" type="number" min="0" max="999999999" step="0.01" inputmode="decimal" required placeholder="Actual cash received" value="{{ (string)old('ui_record') === 'handover-'.$batch->id ? old('items.'.$i.'.amount') : '' }}"></label>
                @endforeach
                <button class="sh-btn" type="submit">Confirm counted cash receipt</button>
            </form>
        @else
            <p class="sh-notice">Verified received ₱{{ $money($batch->verified_minor) }} · Difference ₱{{ $money($batch->difference_minor) }}</p>
            @if($batch->status === 'discrepancy')<p class="sh-notice sh-notice-warning">The counted amount needs review. Add evidence through Discrepancy Review.</p><a class="sh-btn-soft" href="{{ route('logistics.hybrid.discrepancies.index') }}">Review discrepancy</a>@endif
        @endif
    </template>
    @endforeach
    <dialog class="ops-dialog" id="opsRecordDialog" aria-labelledby="opsRecordTitle">
        <div class="ops-dialog-head"><h2 class="ops-dialog-title" id="opsRecordTitle">Cash handover</h2><button type="button" class="ops-action" data-ops-close aria-label="Close handover">&times;</button></div>
        <div class="ops-dialog-body space-y-4" data-ops-record-content></div><div class="ops-dialog-foot"><button type="button" class="sh-btn-soft" data-ops-close>Close</button></div>
    </dialog>
</div>
