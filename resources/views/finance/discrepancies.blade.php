@extends($actor->account_type === 'logistics' ? 'logistics.layouts.console' : ($actor->account_type === 'branch' ? 'branch.layout.app' : 'admin.layout'))
@section('title', 'COD discrepancy resolution')
@section('content')
<div class="space-y-4 sh-finance-ops">
<header class="sh-page-header"><div><p class="eyebrow">Finance · Cash exceptions</p><h1 class="title">COD Discrepancy Review</h1><p class="description">Review counted cash and evidence. Open cases block further remittance and settlement.</p></div>
<a class="sh-btn-soft" href="{{ route($actor->account_type === 'admin' ? 'hybrid.index' : 'logistics.hybrid') }}">Cash handovers</a></header>
@if(session('status'))<p role="status" class="sh-alert-success">{{ session('status') }}</p>@endif
@if($errors->any())<div role="alert" class="sh-alert-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<section class="ops-panel">
<nav class="sh-console-tabs" aria-label="Case status">
@foreach(['active'=>'Active cases','open'=>'Investigation','pending_approval'=>'Awaiting Admin','approved'=>'Approved return','resolved'=>'Resolved','all'=>'All history'] as $value=>$label)
<a href="{{ route($routePrefix.'.index', ['status'=>$value]) }}" @if($filter===$value) aria-current="page" @endif>{{ $label }}</a>
@endforeach
</nav>
<div class="ops-header"><div><h2>Cash discrepancy cases</h2><p class="ops-panel-note">{{ $cases->total() }} matching case(s). Select a case to review evidence and permitted actions.</p></div></div>
<div class="ops-table-scroll"><table class="ops-table"><thead><tr><th scope="col">Case / Order</th><th scope="col">Receiving facility</th><th scope="col" class="ops-money">Expected COD</th><th scope="col" class="ops-money">Net verified</th><th scope="col" class="ops-money">Difference</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>
@forelse($cases as $case)
@php $caseDifference = \App\Services\FinanceMoney::minor($case->received_amount) - \App\Services\FinanceMoney::minor($case->expected_amount); @endphp
<tr><td><strong>{{ $case->reference }}</strong><div class="ops-row-sub">Order #{{ $case->order_id }}</div></td>
<td>{{ $case->facility_name ?: 'Company review · no facility recorded' }}</td>
<td class="ops-money">₱{{ number_format((float)$case->expected_amount,2) }}</td><td class="ops-money">₱{{ number_format((float)($case->received_amount ?? 0),2) }}</td>
<td class="ops-money">₱{{ number_format($caseDifference/100,2) }}</td><td><span class="sh-badge {{ $case->status==='resolved' ? 'sh-badge-success' : 'sh-badge-warning' }}">{{ ucwords(str_replace('_',' ',$case->status)) }}</span></td>
<td class="ops-row-actions"><a class="ops-action" data-console-details="#case-{{ $case->id }}" href="#case-{{ $case->id }}">Review case</a></td></tr>
@empty<tr><td colspan="7" class="ops-empty">No cases match this status.</td></tr>@endforelse
</tbody></table></div><div class="ops-footer"><span>{{ $cases->total() }} case(s)</span>{{ $cases->links() }}</div>
</section>
@forelse($cases as $case)
@php
    $difference = App\Services\FinanceMoney::minor($case->received_amount) - App\Services\FinanceMoney::minor($case->expected_amount);
    $collectionMatches = App\Services\FinanceMoney::minor($case->collected_amount) === App\Services\FinanceMoney::minor($case->expected_amount);
@endphp
<details class="ops-panel sh-case-record" id="case-{{ $case->id }}" data-console-inline-detail data-console-detail-title="COD case {{ $case->reference }}" @if((int)old('ui_case_id') === (int)$case->id) data-console-auto-open @endif @if((int)old('ui_case_id') === (int)$case->id) open @endif>
<summary class="ops-header"><span><strong>{{ $case->reference }}</strong> &middot; Order #{{ $case->order_id }}</span><span class="sh-badge {{ $case->status === 'resolved' ? 'sh-badge-success' : 'sh-badge-warning' }}">{{ ucwords(str_replace('_',' ',$case->status)) }}</span></summary>
<div class="p-4 space-y-3">
<p><strong>Case:</strong> {{ $case->reference }}<br><strong>Receiving facility:</strong> {{ $case->facility_name ?: 'Not recorded; company review required' }}</p>
<dl class="sh-detail-grid">
<div><dt>Expected full COD</dt><dd>PHP {{ $case->expected_amount }}</dd></div>
<div><dt>Buyer cash recorded</dt><dd>PHP {{ $case->collected_amount }}</dd></div>
<div><dt>Current net verified cash</dt><dd>PHP {{ $case->received_amount ?? '0.00' }}</dd></div>
<div><dt>Current difference</dt><dd>PHP {{ App\Services\FinanceMoney::decimal($difference) }}</dd></div>
<div><dt>Original case difference</dt><dd>PHP {{ App\Services\FinanceMoney::decimal($case->opening_difference_minor) }}</dd></div>
<div><dt>Opened</dt><dd>{{ $case->opened_at }}</dd></div>
</dl>
@if($case->status === 'resolved')
<p class="sh-notice">Review completed {{ $case->resolved_at }}. Original receipts and differences remain in the history. ShopHop receipt and settlement still have their own approvals.</p>
@else
@if(!$collectionMatches)<p class="sh-notice sh-notice-warning">The Buyer collection differs from the expected COD. Investigate the original collection; personal earnings cannot cover or waive this difference.</p>
@elseif($difference < 0)<p class="sh-notice sh-notice-warning">Shortage: PHP {{ App\Services\FinanceMoney::decimal(-$difference) }} remains owed. Submit and verify supplemental cash through COD handover before proposing closure.</p>
@elseif($difference > 0)<p class="sh-notice sh-notice-warning">Excess cash must be investigated. Logistics may propose returning exactly PHP {{ App\Services\FinanceMoney::decimal($difference) }} to the verified source. Approval alone does not record a cash return.</p>
@else<p class="sh-notice sh-notice-warning">Cash is balanced. Logistics may propose closure with the supplemental receipt evidence; Admin must review it.</p>@endif
<details class="sh-record-details"><summary>Add investigation evidence</summary>
<form class="sh-case-form" method="POST" action="{{ route($routePrefix.'.note', $case->id) }}">@csrf<input type="hidden" name="ui_case_id" value="{{ $case->id }}">
<label>Investigation notes<textarea name="reason" required minlength="10" maxlength="2000"></textarea></label>
<label>Evidence / receipt reference<input name="evidence_reference" required minlength="4" maxlength="160"></label>
<button class="sh-btn" type="submit">Add evidence</button>
</form></details>
@if($actor->account_type === 'logistics' && $case->status === 'open' && $collectionMatches && $difference >= 0)
<details class="sh-record-details"><summary>Propose {{ $difference > 0 ? 'excess return' : 'case closure' }}</summary>
<form class="sh-case-form" method="POST" action="{{ route($routePrefix.'.propose', $case->id) }}">@csrf<input type="hidden" name="ui_case_id" value="{{ $case->id }}">
<input type="hidden" name="kind" value="{{ $difference > 0 ? 'overage_return' : 'balance_confirmed' }}">
<label>Reason and findings<textarea name="reason" required minlength="10" maxlength="2000"></textarea></label>
<label>Evidence reference<input name="evidence_reference" required minlength="4" maxlength="160"></label>
@if($difference > 0)<label>Verified return recipient<input name="recipient" required minlength="4" maxlength="160"></label>@endif
<button class="sh-btn" type="submit">Submit for Admin approval</button>
</form></details>
@endif
@endif
@foreach($proposals->get($case->id, collect()) as $proposal)
<details @if(in_array($proposal->status, ['pending', 'approved'], true)) open @endif>
<summary>Proposal #{{ $proposal->id }} - {{ str_replace('_', ' ', $proposal->kind) }} - {{ $proposal->status }}</summary>
<p>Amount: PHP {{ App\Services\FinanceMoney::decimal($proposal->amount_minor) }}<br>Proposed by User #{{ $proposal->proposed_by_user_id }} on {{ $proposal->created_at }}</p>
<p>{{ $proposal->reason }}<br>Evidence: {{ $proposal->evidence_reference }}</p>
@if($proposal->recipient)<p>Verified return recipient: {{ $proposal->recipient }}</p>@endif
@if($proposal->reviewed_at)<p>Reviewed by User #{{ $proposal->reviewed_by_user_id }} on {{ $proposal->reviewed_at }}<br>{{ $proposal->review_notes }}</p>@endif
@if($actor->account_type === 'admin' && $proposal->status === 'pending')
<form class="sh-case-form" method="POST" action="{{ route($routePrefix.'.review', [$case->id, $proposal->id]) }}">@csrf<input type="hidden" name="ui_case_id" value="{{ $case->id }}">
<label>Review findings<textarea name="notes" required minlength="10" maxlength="2000"></textarea></label>
<button class="sh-btn" name="decision" value="approve" type="submit">Approve resolution</button>
<button class="sh-btn-danger" name="decision" value="reject" type="submit">Reject proposal</button>
</form>
@endif
@if($actor->account_type === 'logistics' && $proposal->status === 'approved')
<p class="sh-notice sh-notice-warning">Admin approved the return. Record it only after the recipient has actually received the excess cash.</p>
<form class="sh-case-form" method="POST" action="{{ route($routePrefix.'.return', [$case->id, $proposal->id]) }}">@csrf<input type="hidden" name="ui_case_id" value="{{ $case->id }}">
<label>Actual amount returned (PHP)<input name="returned_amount" type="number" min="0.01" step="0.01" value="{{ App\Services\FinanceMoney::decimal($proposal->amount_minor) }}" required></label>
<label>Cash return transaction reference<input name="reference" required minlength="4" maxlength="120"></label>
<label>Recipient acknowledgment reference<input name="recipient_acknowledgment" required minlength="4" maxlength="160"></label>
<label><input name="cash_returned" value="1" type="checkbox" required> I confirmed the actual cash return to the approved recipient.</label>
<button class="sh-btn" type="submit">Record actual return</button>
</form>
@endif
@if($adjustment = $adjustments->get($proposal->id))
<p class="sh-notice">Recorded cash return: PHP {{ App\Services\FinanceMoney::decimal(-$adjustment->amount_minor) }}<br>Reference: {{ $adjustment->reference }}<br>Recipient acknowledgment: {{ $adjustment->recipient_acknowledgment }}<br>User #{{ $adjustment->recorded_by_user_id }} on {{ $adjustment->recorded_at }}</p>
@endif
</details>
@endforeach
<details class="sh-record-details"><summary>Audit and evidence history</summary>
@forelse($events->get($case->id, collect()) as $event)
<p><strong>{{ str_replace('_', ' ', $event->action) }}</strong> - User #{{ $event->actor_id }} - {{ $event->created_at }}</p>
<pre>{{ json_encode(json_decode($event->details), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
@empty<p>This historical case was indexed during the database update. Original cash receipts remain authoritative.</p>@endforelse
</details>
</div></details>
@empty @endforelse
</div>
@endsection
