@php
    $financeSection = request('section') === 'compensation' ? 'compensation' : 'handovers';
    $riderDirectory = $personnel->keyBy('id');
    $driverDirectory = $drivers->keyBy('id');
    $personName = fn ($type, $id) => ($type === 'rider' ? $riderDirectory : $driverDirectory)->get($id)?->name ?? ucfirst(str_replace('_',' ',$type)).' #'.$id;
    $cashMoney = fn ($minor) => number_format($minor / 100, 2);
@endphp
<div class="space-y-4 sh-finance-ops">
    <header class="sh-page-header">
        <div><p class="eyebrow">Finance · {{ $financeSection === 'handovers' ? 'Cash custody' : 'Personnel & operating costs' }}</p>
            <h1 class="title">{{ $financeSection === 'handovers' ? 'Cash Handovers' : 'Compensation & Costs' }}</h1>
            <p class="description">{{ $financeSection === 'handovers' ? 'Review actual rider handovers and verify the cash received at the receiving facility.' : 'Manage compensation policies, payroll, recorded earnings and operating expenses.' }}</p></div>
        <div class="flex flex-wrap gap-2"><a class="sh-btn-soft" href="{{ route('logistics.settlements.index') }}">COD Settlements</a><a class="sh-btn-soft" href="{{ route('logistics.earnings') }}">Earnings & Payouts</a></div>
    </header>
    @if(session('status'))<div class="sh-alert-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="sh-alert-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($financeSection === 'handovers')
        <p class="sh-notice">Count the full COD before confirming receipt. Any shortage or excess stays under review; personnel earnings are paid separately.</p>
        <section class="ops-panel">
            <div class="ops-header"><div><h2>Branch cash handovers</h2><p class="ops-panel-note">Review the receiving Branch, evidence reference, and actual counted amount.</p></div><a class="sh-btn-soft" href="{{ route('logistics.hybrid.discrepancies.index') }}">Discrepancy review</a></div>
            <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th scope="col">Reference / Rider</th><th scope="col">Receiving branch</th><th scope="col" class="ops-money">Expected</th><th scope="col" class="ops-money">Submitted</th><th scope="col" class="ops-money">Verified</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>
                @forelse($batches as $batch)<tr>
                    <td><strong>{{ $batch->reference }}</strong><div class="ops-row-sub">{{ $riderNames->get($batch->rider_id, 'Rider #'.$batch->rider_id) }}</div></td>
                    <td>{{ $centerNames->get($batch->sorting_center_id, 'Facility #'.$batch->sorting_center_id) }}</td>
                    <td class="ops-money">₱{{ $cashMoney($batch->expected_minor) }}</td><td class="ops-money">₱{{ $cashMoney($batch->submitted_minor) }}</td>
                    <td class="ops-money">{{ $batch->verified_minor === null ? 'Pending' : '₱'.$cashMoney($batch->verified_minor) }}</td>
                    <td><span class="sh-badge {{ $batch->status === 'discrepancy' ? 'sh-badge-warning' : ($batch->verified_at ? 'sh-badge-success' : 'sh-badge-info') }}">{{ $batch->status === 'discrepancy' ? 'Under review' : ($batch->verified_at ? 'Receipt verified' : 'Awaiting verification') }}</span></td>
                    <td class="ops-row-actions"><button class="ops-action" type="button" id="handover-{{ $batch->id }}" data-ops-template="handover-detail-{{ $batch->id }}" data-ops-title="Handover · {{ $batch->reference }}" @if((string)old('ui_record') === 'handover-'.$batch->id) data-reopen-record @endif>{{ $batch->verified_at ? 'View receipt' : 'Verify receipt' }}</button></td>
                </tr>@empty<tr><td colspan="7" class="ops-empty">No branch cash handovers recorded.</td></tr>@endforelse
            </tbody></table></div>
            <div class="ops-footer"><span>{{ $batches->total() }} handover(s)</span>{{ $batches->appends(['section'=>'handovers'])->links() }}</div>
        </section>
        @foreach($batches as $batch)
        <template id="handover-detail-{{ $batch->id }}">
            <dl class="sh-detail-grid">
                <div><dt>Rider</dt><dd>{{ $riderNames->get($batch->rider_id, 'Rider #'.$batch->rider_id) }}</dd></div>
                <div><dt>Receiving branch</dt><dd>{{ $centerNames->get($batch->sorting_center_id) }}</dd></div>
                <div><dt>Receipt evidence</dt><dd>{{ $batch->receipt_reference }}</dd></div>
                <div><dt>Submitted</dt><dd>{{ \Illuminate\Support\Carbon::parse($batch->created_at)->timezone('Asia/Manila')->format('M j, Y · g:i A') }} PHT</dd></div>
                <div><dt>Verified by</dt><dd>{{ $receiverNames->get($batch->verified_by_user_id) ?: 'Awaiting verification' }}</dd></div>
                <div><dt>Difference</dt><dd>{{ $batch->difference_minor === null ? 'Pending verification' : '₱'.$cashMoney($batch->difference_minor) }}</dd></div>
            </dl>
            @if(!$batch->verified_at)
            <p class="sh-notice">Next action: count each collection and enter the cash physically received.</p>
            <form method="POST" action="{{ route('logistics.hybrid.verify', $batch->id) }}" class="space-y-3">@csrf
                <input type="hidden" name="ui_record" value="handover-{{ $batch->id }}">
                @foreach($items->get($batch->id, collect()) as $i=>$item)
                <input type="hidden" name="items[{{ $i }}][collection_id]" value="{{ $item->cod_settlement_id }}">
                <label class="sh-field"><span class="sh-label">Collection #{{ $item->cod_settlement_id }} · Submitted ₱{{ $cashMoney($item->submitted_minor) }}</span>
                    <input class="sh-input" name="items[{{ $i }}][amount]" type="number" min="0" step="0.01" inputmode="decimal" required placeholder="Actual counted cash" value="{{ (string)old('ui_record') === 'handover-'.$batch->id ? old('items.'.$i.'.amount') : '' }}"></label>
                @endforeach
                <button class="sh-btn" type="submit">Confirm counted cash receipt</button>
            </form>
            @else
                <p class="sh-notice">Verified receipt: ₱{{ $cashMoney($batch->verified_minor) }} · {{ \Illuminate\Support\Carbon::parse($batch->verified_at)->timezone('Asia/Manila')->format('M j, Y · g:i A') }} PHT</p>
                @if($batch->status === 'discrepancy')<p class="sh-notice sh-notice-warning">This handover requires discrepancy review before remittance can continue.</p><a class="sh-btn-soft" href="{{ route('logistics.hybrid.discrepancies.index') }}">Open discrepancy review</a>@endif
            @endif
        </template>
        @endforeach
        <section class="ops-panel">
            <div class="ops-header"><div><h2>Company receipt review</h2><p class="ops-panel-note">Earlier single-order handovers without a recorded receiving Branch. The company verifies these receipts.</p></div></div>
            <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th scope="col">Order / Tracking</th><th scope="col">Rider</th><th scope="col" class="ops-money">Collected</th><th scope="col" class="ops-money">Verified received</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>
                @forelse($directHandovers as $cash)<tr>
                    <td><strong>Order #{{ $cash->order_id }}</strong><div class="ops-row-sub">{{ $cash->delivery?->tracking_code ?: 'Tracking unavailable' }}</div></td>
                    <td>{{ $cash->remitter?->name ?: 'Rider unavailable' }}</td><td class="ops-money">₱{{ number_format((float)$cash->collected_amount,2) }}</td>
                    <td class="ops-money">{{ $cash->received_amount === null ? 'Pending' : '₱'.number_format((float)$cash->received_amount,2) }}</td>
                    <td><span class="sh-badge {{ $cash->reconciled_at ? 'sh-badge-success' : 'sh-badge-info' }}">{{ $cash->reconciled_at ? 'Receipt verified' : 'Awaiting company' }}</span></td>
                    <td class="ops-row-actions"><a class="ops-action" href="{{ route('logistics.settlements.index', ['q'=>(string)$cash->order_id]) }}">{{ $cash->reconciled_at ? 'View receipt' : 'Review receipt' }}</a></td>
                </tr>@empty<tr><td colspan="6" class="ops-empty">No single-order handovers for company review.</td></tr>@endforelse
            </tbody></table></div>
            <div class="ops-footer"><span>{{ $directHandovers->total() }} record(s)</span>{{ $directHandovers->appends(['section'=>'handovers'])->links() }}</div>
        </section>
    @else
        <p class="sh-notice">Rider and driver compensation is paid by Logistics. Recording a cost or earning does not confirm an external payment.</p>
        <section class="ops-panel" data-ops-tabs data-ops-tab-param="tab">
            <nav class="ops-tabs" role="tablist" aria-label="Compensation and costs">
                @foreach(['policies'=>'Policies','payroll'=>'Payroll','earnings'=>'Personnel earnings','expenses'=>'Operating expenses'] as $key=>$label)<button class="ops-tab" type="button" role="tab" data-ops-tab="{{ $key }}">{{ $label }}</button>@endforeach
            </nav>
            <section data-ops-panel="policies">
                <div class="ops-header"><div><h2>Compensation policies</h2><p class="ops-panel-note">Prospective rates for verified work. Fixed and base rates are monthly.</p></div></div>
                <details class="p-4 sh-record-details" @if(old('ui_form') === 'policy') open @endif><summary>Create compensation policy</summary>
                    <form method="POST" action="{{ route('logistics.hybrid.policy') }}" class="grid gap-3 mt-3 md:grid-cols-3" data-personnel-form>@csrf<input type="hidden" name="ui_form" value="policy">
                        <label class="sh-field"><span class="sh-label">Personnel type</span><select class="sh-select" name="personnel_type"><option value="rider" @selected(old('personnel_type', 'rider') === 'rider')>Rider</option><option value="linehaul_driver" @selected(old('personnel_type') === 'linehaul_driver')>Linehaul driver</option></select></label>
                        <label class="sh-field"><span class="sh-label">Personnel</span><select class="sh-select" name="personnel_id" required><option value="">Select personnel</option>
                            @foreach(['rider'=>$personnel, 'linehaul_driver'=>$drivers] as $type=>$people)@foreach($people as $person)<option value="{{ $person->id }}" data-personnel-type="{{ $type }}" @selected(old('personnel_type', 'rider') === $type && (int)old('personnel_id') === (int)$person->id)>{{ $person->name }}</option>@endforeach @endforeach
                        </select></label>
                        <label class="sh-field"><span class="sh-label">Arrangement</span><select class="sh-select" name="type">@foreach(\App\Services\PartnerCompensationService::TYPES as $type)<option value="{{ $type }}" @selected(old('type') === $type)>{{ ucwords(str_replace('_',' ',$type)) }}</option>@endforeach</select></label>
                        <label class="sh-field"><span class="sh-label">Rate (₱)</span><input class="sh-input" name="rate" type="number" min="0" step="0.01" required value="{{ old('rate') }}"></label>
                        <label class="sh-field"><span class="sh-label">Pay schedule</span><input class="sh-input" name="schedule" required maxlength="160" value="{{ old('schedule') }}"></label>
                        <label class="sh-field"><span class="sh-label">Effective from</span><input class="sh-input" name="effective_from" type="date" required value="{{ old('effective_from') }}"></label>
                        <label class="sh-field"><span class="sh-label">Effective through (optional)</span><input class="sh-input" name="effective_until" type="date" value="{{ old('effective_until') }}"></label>
                        <div class="flex items-end"><button class="sh-btn" type="submit">Create policy</button></div>
                    </form>
                </details>
                <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Personnel</th><th>Arrangement</th><th class="ops-money">Rate</th><th>Effective dates</th><th>Pay schedule</th><th>Action</th></tr></thead><tbody>
                    @forelse($policies as $policy)<tr><td><strong>{{ $personName($policy->personnel_type,$policy->personnel_id) }}</strong><div class="ops-row-sub">{{ ucwords(str_replace('_',' ',$policy->personnel_type)) }} · Policy #{{ $policy->id }}</div></td>
                        <td>{{ ucwords(str_replace('_',' ',$policy->type)) }}</td><td class="ops-money">₱{{ $cashMoney($policy->rate_minor) }}</td><td>{{ $policy->effective_from }}<div class="ops-row-sub">Through {{ $policy->effective_until ?: 'open ended' }}</div></td><td>{{ $policy->schedule }}</td>
                        <td>@if(!$policy->effective_until)<form method="POST" action="{{ route('logistics.hybrid.policy.end',$policy->id) }}" class="flex flex-wrap gap-2">@csrf<label class="sh-field"><span class="sh-label">Prospective end date</span><input class="sh-input" type="date" name="effective_until" required aria-label="End date for policy {{ $policy->id }}" value="{{ old('effective_until') }}"></label><button class="sh-btn-soft">Set end date</button></form>@else<span class="ops-muted">End date recorded</span>@endif</td>
                    </tr>@empty<tr><td colspan="6" class="ops-empty">No compensation policies recorded.</td></tr>@endforelse
                </tbody></table></div>
            </section>
            <section data-ops-panel="payroll" hidden>
                <div class="ops-header"><div><h2>Record monthly payroll</h2><p class="ops-panel-note">Select a policy and a completed month. Hourly arrangements require approved minutes.</p></div></div>
                <form method="POST" action="{{ route('logistics.hybrid.payroll') }}" class="grid gap-3 p-4 md:grid-cols-3">@csrf
                    <label class="sh-field"><span class="sh-label">Compensation policy</span><select class="sh-select" name="policy_id" required><option value="">Select policy</option>@foreach($policies as $policy)<option value="{{ $policy->id }}" @selected((int)old('policy_id') === (int)$policy->id)>#{{ $policy->id }} · {{ $personName($policy->personnel_type,$policy->personnel_id) }} · {{ ucwords(str_replace('_',' ',$policy->type)) }}</option>@endforeach</select></label>
                    <label class="sh-field"><span class="sh-label">Completed month</span><input class="sh-input" name="period" type="month" required value="{{ old('period') }}"></label>
                    <label class="sh-field"><span class="sh-label">Approved minutes (hourly)</span><input class="sh-input" name="minutes" type="number" min="0" required value="{{ old('minutes', '0') }}"></label>
                    <label class="sh-field"><span class="sh-label">Incentive (₱)</span><input class="sh-input" name="incentive" type="number" step="0.01" min="0" required value="{{ old('incentive', '0') }}"></label>
                    <label class="sh-field"><span class="sh-label">Approved work / payroll evidence</span><input class="sh-input" name="evidence" required minlength="4" maxlength="1000" value="{{ old('evidence') }}"></label>
                    <div class="flex items-end"><button class="sh-btn">Record payroll</button></div>
                </form>
            </section>
            <section data-ops-panel="earnings" hidden>
                <div class="ops-header"><div><h2>Personnel earnings</h2><p class="ops-panel-note">Approve verified obligations, then record payment after the actual payout.</p></div></div>
                <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Personnel</th><th>Work reference</th><th class="ops-money">Amount</th><th>Status</th><th>Payment reference</th><th>Action</th></tr></thead><tbody>
                    @forelse($earnings as $entry)<tr><td>{{ $personName($entry->personnel_type,$entry->personnel_id) }}<div class="ops-row-sub">{{ ucwords(str_replace('_',' ',$entry->personnel_type)) }}</div></td><td>{{ $entry->event_key }}</td><td class="ops-money">₱{{ $cashMoney($entry->amount_minor) }}</td><td><span class="sh-badge {{ $entry->status === 'paid' ? 'sh-badge-success' : 'sh-badge-neutral' }}">{{ ucfirst($entry->status) }}</span></td><td>{{ $entry->payment_reference ?: 'Not recorded' }}</td>
                        <td class="ops-row-actions">@if($entry->status === 'pending')<form method="POST" action="{{ route('logistics.hybrid.approve',$entry->id) }}">@csrf<button class="ops-action">Approve earning</button></form>
                        @elseif($entry->status === 'approved')<button class="ops-action" type="button" data-ops-template="earning-payment-{{ $entry->id }}" data-ops-title="Record personnel payout" @if(old('ui_record') === 'earning-'.$entry->id) data-reopen-record @endif>Record payout</button>@else<span class="ops-muted">{{ $entry->paid_at ?: 'No action' }}</span>@endif</td>
                    </tr>@empty<tr><td colspan="6" class="ops-empty">No personnel earnings recorded.</td></tr>@endforelse
                </tbody></table></div>
                <div class="ops-footer"><span>{{ $earnings->total() }} earning(s)</span>{{ $earnings->appends(['section'=>'compensation','tab'=>'earnings'])->links() }}</div>
            </section>
            <section data-ops-panel="expenses" hidden>
                <div class="ops-header"><div><h2>Operating expenses</h2><p class="ops-panel-note">Record incurred costs separately from payments. Personnel payroll belongs in compensation.</p></div></div>
                <details class="p-4 sh-record-details" @if(old('ui_form') === 'expense') open @endif><summary>Record operating expense</summary>
                    <form method="POST" action="{{ route('logistics.hybrid.expense') }}" class="grid gap-3 mt-3 md:grid-cols-3">@csrf<input type="hidden" name="ui_form" value="expense">
                        <label class="sh-field"><span class="sh-label">Category</span><select class="sh-select" name="category">@foreach(['hub_operations','branch_operations','fuel','transport','maintenance','sorting','other'] as $category)<option value="{{ $category }}" @selected(old('category') === $category)>{{ ucwords(str_replace('_',' ',$category)) }}</option>@endforeach</select></label>
                        <label class="sh-field"><span class="sh-label">Amount (₱)</span><input class="sh-input" name="amount" type="number" min="0.01" step="0.01" required value="{{ old('amount') }}"></label>
                        <label class="sh-field"><span class="sh-label">Incurred on</span><input class="sh-input" name="incurred_on" type="date" required value="{{ old('incurred_on') }}"></label>
                        <label class="sh-field"><span class="sh-label">Expense evidence</span><input class="sh-input" name="evidence_reference" required minlength="4" maxlength="160" value="{{ old('evidence_reference') }}"></label>
                        <label class="sh-field"><span class="sh-label">Payment reference (if paid)</span><input class="sh-input" name="payment_reference" minlength="4" maxlength="120" value="{{ old('payment_reference') }}"></label>
                        <div class="flex items-end"><button class="sh-btn">Record expense</button></div>
                    </form>
                </details>
                <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Category</th><th class="ops-money">Amount</th><th>Incurred on</th><th>Evidence reference</th><th>Payment</th></tr></thead><tbody>
                    @forelse($expenses as $expense)<tr><td>{{ ucwords(str_replace('_',' ',$expense->category)) }}</td><td class="ops-money">₱{{ $cashMoney($expense->amount_minor) }}</td><td>{{ $expense->incurred_on }}</td><td>{{ $expense->evidence_reference }}</td><td>{{ $expense->payment_reference ?: 'Payment not recorded' }}</td></tr>@empty<tr><td colspan="5" class="ops-empty">No operating expenses recorded.</td></tr>@endforelse
                </tbody></table></div>
                <div class="ops-footer"><span>{{ $expenses->total() }} expense(s)</span>{{ $expenses->appends(['section'=>'compensation','tab'=>'expenses'])->links() }}</div>
            </section>
        </section>
        @foreach($earnings as $entry)@if($entry->status === 'approved')
        <template id="earning-payment-{{ $entry->id }}">
            <p class="sh-notice">{{ $personName($entry->personnel_type,$entry->personnel_id) }} · ₱{{ $cashMoney($entry->amount_minor) }}</p>
            <form method="POST" action="{{ route('logistics.hybrid.paid',$entry->id) }}" class="space-y-3">@csrf<input type="hidden" name="ui_record" value="earning-{{ $entry->id }}">
                <label class="sh-field"><span class="sh-label">Actual external payment reference</span><input class="sh-input" name="reference" required minlength="4" maxlength="120"></label>
                <label class="flex gap-2 items-start"><input type="checkbox" name="payment_confirmed" value="1" required> Actual external payment confirmed</label>
                <button class="sh-btn">Record confirmed payment</button>
            </form>
        </template>
        @endif @endforeach
    @endif
    <dialog class="ops-dialog" id="opsRecordDialog" aria-labelledby="opsRecordTitle">
        <div class="ops-dialog-head"><h2 class="ops-dialog-title" id="opsRecordTitle">Cash receipt details</h2><button class="ops-action" type="button" data-ops-close aria-label="Close details">&times;</button></div>
        <div class="ops-dialog-body space-y-4" data-ops-record-content></div><div class="ops-dialog-foot"><button class="sh-btn-soft" type="button" data-ops-close>Close</button></div>
    </dialog>
</div>
