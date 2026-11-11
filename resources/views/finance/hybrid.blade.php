<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>ShopHop financial workspace</title>@vite(['resources/css/app.css'])</head>
<body class="bg-slate-50 text-slate-900"><main class="mx-auto max-w-6xl space-y-6 p-6">
<h1 class="text-2xl font-bold">Financial workspace</h1>
<a class="inline-block rounded border p-2 text-teal-700" href="{{ $isRider ? route('rider.settlements.index') : route(match($actor->account_type) { 'admin' => 'admin.commission', 'logistics' => 'logistics.earnings', default => 'branch.dashboard' }) }}">Back to financial overview</a>
<p>Full COD cash handover and partner personnel compensation are separate records. Amounts shown here are records of obligations and receipts. External payments require confirmation.</p>
@if(session('status'))<p role="status" class="rounded bg-teal-50 p-3">{{ session('status') }}</p>@endif
@if($errors->any())<div role="alert" class="rounded bg-red-50 p-3">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@if(!$isRider && $actor->account_type === 'admin')
<section class="rounded bg-white p-4"><h2 class="text-xl font-semibold">Prospective platform financial rules</h2><p>Buyer confirmation releases Seller eligibility. An optional hold period permits eligibility after the configured delivered days even without confirmation, subject to cash reconciliation, returns and disputes. Zero retains Buyer confirmation. Rider compensation follows partner policy.</p>
<form method="POST" action="{{ route('hybrid.platform-rule') }}" class="flex flex-wrap gap-3">@csrf
<label>Product commission % <input name="product_percent" type="number" min="0" max="100" step="0.01" required class="border p-2"></label>
<label>Shipping commission % <input name="shipping_percent" type="number" min="0" max="100" step="0.01" required class="border p-2"></label>
<label>Automatic Seller eligibility after days <input name="seller_hold_days" type="number" min="0" max="365" value="0" required class="border p-2"></label>
<label>Effective at ({{ config('app.timezone') }}) <input name="effective_at" type="datetime-local" required class="border p-2"></label><button class="border p-2">Schedule rule</button></form>
@foreach($rules as $rule)<p>#{{ $rule->id }} ? Product {{ $rule->product_percent }}% ? Shipping {{ $rule->shipping_percent }}% ? Seller period {{ $rule->seller_hold_days }} days ? Effective {{ $rule->effective_at }}</p>@endforeach
</section>
@endif
@if($isRider)
<section class="rounded bg-white p-4"><h2 class="text-xl font-semibold">Submit a cash handover</h2>
<p>Select collections by entering their IDs from the list below. The full cash obligation remains outstanding until independently received. Record shortages using the actual cash handed over.</p>
@foreach($collections as $cash)<p>Collection {{ $cash->id }} · Order {{ $cash->order_id }} · Collected {{ $cash->collected_amount }} · Received {{ $cash->received_amount ?? '0.00' }}</p>@endforeach
<form method="POST" action="{{ route('hybrid.submit') }}" class="space-y-3">@csrf
<input type="hidden" name="idempotency_key" value="{{ (string) Illuminate\Support\Str::uuid() }}">
<label>Receiving facility <select name="sorting_center_id" required>@foreach($centers as $center)<option value="{{ $center->id }}">{{ $center->name }}</option>@endforeach</select></label>
<label>Receipt/evidence reference <input name="reference" required minlength="4" maxlength="120" class="border p-2"></label>
<div id="cash-items"><div><label>Collection ID <input name="items[0][collection_id]" type="number" min="1" required class="border p-2"></label><label>Cash handed over <input name="items[0][amount]" type="number" min="0.01" step="0.01" required class="border p-2"></label></div></div>
<button type="button" id="add-cash-item" class="border p-2">Add another order</button>
<button class="rounded bg-teal-700 p-2 text-white">Submit physical handover</button>
</form>
<script>document.getElementById('add-cash-item').addEventListener('click', function () { const root = document.getElementById('cash-items'); const i = root.children.length; if(i >= 100) return; const row = root.firstElementChild.cloneNode(true); row.querySelectorAll('input').forEach(input => { input.name = input.name.replace('[0]', '[' + i + ']'); input.value = ''; }); root.appendChild(row); });</script>
</section>
@endif
<section><h2 class="text-xl font-semibold">COD remittance history and verification</h2>
@forelse($batches as $batch)
<article class="my-3 space-y-2 rounded border bg-white p-4"><h3 class="font-semibold">{{ $batch->reference }}</h3>
<p>Rider {{ $batch->rider_id }} · Facility {{ $batch->sorting_center_id }} · {{ $batch->created_at }} · {{ $batch->status }}</p>
<p>Expected ₱{{ App\Services\FinanceMoney::decimal($batch->expected_minor) }} · Submitted ₱{{ App\Services\FinanceMoney::decimal($batch->submitted_minor) }} · Verified {{ $batch->verified_minor === null ? 'Pending' : '₱'.App\Services\FinanceMoney::decimal($batch->verified_minor) }} · Difference {{ $batch->difference_minor === null ? 'Pending' : '₱'.App\Services\FinanceMoney::decimal($batch->difference_minor) }}</p>
<p>Evidence: {{ $batch->receipt_reference }} · Receiver {{ $batch->verified_by_user_id ?? 'Pending' }} · {{ $batch->verified_at }}</p>
@if(!$isRider && $actor->account_type !== 'admin' && !$batch->verified_at)
<form method="POST" action="{{ route($isRider || $actor->account_type === 'admin' ? 'hybrid.verify' : 'logistics.hybrid.verify', $batch->id) }}">@csrf
@foreach($items->get($batch->id, collect()) as $i => $item)
<input type="hidden" name="items[{{ $i }}][collection_id]" value="{{ $item->cod_settlement_id }}">
<label class="block">Collection {{ $item->cod_settlement_id }} — actual counted cash <input name="items[{{ $i }}][amount]" type="number" min="0" step="0.01" required class="border p-2"></label>
@endforeach
<button class="mt-2 rounded bg-teal-700 p-2 text-white">Confirm counted cash receipt</button></form>
@else
@foreach($items->get($batch->id, collect()) as $item)<p>Collection {{ $item->cod_settlement_id }} · Submitted ₱{{ App\Services\FinanceMoney::decimal($item->submitted_minor) }} · Verified {{ $item->verified_minor === null ? 'Pending' : App\Services\FinanceMoney::decimal($item->verified_minor) }}</p>@endforeach
@endif
@if($batch->status === 'discrepancy')<p class="text-red-700">Under review. A shortage remains owed; overages are held for investigation and are not revenue. Original receipt records are retained.</p>@endif
</article>
@empty<p>No remittance batches.</p>@endforelse
{{ $batches->links() }}</section>
@if($isPartner)
<section class="rounded bg-white p-4"><h2 class="text-xl font-semibold">Partner operating expenses</h2><p>Costs incurred are separate from actual payments. Personnel earnings are recorded in the compensation section; do not enter the same payroll again here.</p>
<form method="POST" action="{{ route('logistics.hybrid.expense') }}" class="flex flex-wrap gap-3">@csrf
<label>Category <select name="category">@foreach(['hub_operations','branch_operations','fuel','transport','maintenance','sorting','other'] as $category)<option>{{ $category }}</option>@endforeach</select></label>
<label>Amount ? <input name="amount" type="number" min="0.01" step="0.01" required class="border p-2"></label>
<label>Incurred on <input name="incurred_on" type="date" required class="border p-2"></label>
<label>Expense evidence <input name="evidence_reference" required minlength="4" maxlength="160" class="border p-2"></label>
<label>Confirmed payment reference (if paid) <input name="payment_reference" minlength="4" maxlength="120" class="border p-2"></label>
<button class="border p-2">Record expense</button></form>
@foreach($expenses as $expense)<p>{{ $expense->category }} ? ?{{ App\Services\FinanceMoney::decimal($expense->amount_minor) }} ? {{ $expense->incurred_on }} ? Evidence {{ $expense->evidence_reference }} ? {{ $expense->payment_reference ? 'Paid: '.$expense->payment_reference : 'Incurred; payment not recorded' }}</p>@endforeach{{ $expenses->links() }}</section>
<section class="space-y-3 rounded bg-white p-4"><h2 class="text-xl font-semibold">Partner compensation policies</h2><p>Policies apply prospectively to verified work, independently of seller settlement. Fixed/base rates are monthly; hourly rates use approved minutes. Keep effective periods non-overlapping.</p>
<p>Riders: @foreach($personnel as $person){{ $person->id }}: {{ $person->name }}; @endforeach</p><p>Linehaul drivers: @foreach($drivers as $person){{ $person->id }}: {{ $person->name }}; @endforeach</p>
<form method="POST" action="{{ route('logistics.hybrid.policy') }}" class="flex flex-wrap gap-3">@csrf
<label>Personnel type <select name="personnel_type"><option value="rider">Rider</option><option value="linehaul_driver">Linehaul driver</option></select></label>
<label>Personnel ID <input name="personnel_id" type="number" min="1" required class="border p-2"></label>
<label>Arrangement <select name="type">@foreach(App\Services\PartnerCompensationService::TYPES as $type)<option>{{ $type }}</option>@endforeach</select></label>
<label>Rate ₱ <input name="rate" type="number" min="0" step="0.01" required class="border p-2"></label>
<label>Pay schedule <input name="schedule" required maxlength="160" class="border p-2"></label>
<label>Effective from <input name="effective_from" type="date" required class="border p-2"></label>
<label>Through <input name="effective_until" type="date" class="border p-2"></label>
<button class="rounded bg-teal-700 p-2 text-white">Create policy</button></form>
@foreach($policies as $policy)<p>#{{ $policy->id }} · {{ $policy->personnel_type }} {{ $policy->personnel_id }} · {{ $policy->type }} · ₱{{ App\Services\FinanceMoney::decimal($policy->rate_minor) }} · {{ $policy->effective_from }} through {{ $policy->effective_until ?? 'open ended' }} · {{ $policy->schedule }}</p>
@if(!$policy->effective_until)<form method="POST" action="{{ route('logistics.hybrid.policy.end', $policy->id) }}">@csrf<label>Prospective final day <input name="effective_until" type="date" required class="border p-2"></label><button class="border p-2">Set policy end</button></form>@endif
@endforeach
</section>
<section class="rounded bg-white p-4"><h2 class="text-xl font-semibold">Monthly payroll records</h2><form method="POST" action="{{ route('logistics.hybrid.payroll') }}" class="flex flex-wrap gap-3">@csrf
<label>Policy ID <input name="policy_id" type="number" required class="border p-2"></label><label>Completed month <input name="period" type="month" required class="border p-2"></label><label>Approved minutes (hourly) <input name="minutes" type="number" value="0" min="0" required class="border p-2"></label><label>Incentive ₱ <input name="incentive" type="number" step="0.01" min="0" value="0" required class="border p-2"></label><label>Approved work/payroll evidence <input name="evidence" required minlength="4" maxlength="1000" class="border p-2"></label><button class="rounded bg-teal-700 p-2 text-white">Record payroll</button>
</form></section>
<section><h2 class="text-xl font-semibold">Personnel earnings and confirmed payouts</h2>
@foreach($earnings as $entry)<article class="my-3 rounded border bg-white p-4"><p>#{{ $entry->id }} · {{ $entry->personnel_type }} {{ $entry->personnel_id }} · {{ $entry->event_key }} · ₱{{ App\Services\FinanceMoney::decimal($entry->amount_minor) }} · {{ $entry->status }}</p>
@if($entry->status === 'pending')<form method="POST" action="{{ route('logistics.hybrid.approve', $entry->id) }}">@csrf<button class="border p-2">Approve earning</button></form>@endif
@if($entry->status === 'approved')<form method="POST" action="{{ route('logistics.hybrid.paid', $entry->id) }}">@csrf<label>External payment reference <input name="reference" required minlength="4" maxlength="120" class="border p-2"></label><label><input type="checkbox" name="payment_confirmed" value="1" required> Actual external payment confirmed</label><button class="border p-2">Record confirmed payment</button></form>@endif
@if($entry->paid_at)<p>Paid {{ $entry->paid_at }} · {{ $entry->payment_reference }}</p>@endif
</article>@endforeach{{ $earnings->links() }}</section>
@endif
@if($audit)
<section><h2 class="text-xl font-semibold">Financial audit history</h2>
@foreach($audit as $event)<article class="my-2 rounded border bg-white p-3"><p>{{ $event->created_at }} ? {{ $event->subject_type }} #{{ $event->subject_id }} ? {{ $event->action }} ? {{ $event->actor_type }} #{{ $event->actor_id }}</p><details><summary>Recorded evidence and changes</summary><pre class="overflow-x-auto whitespace-pre-wrap text-xs">{{ json_encode(json_decode($event->details), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details></article>@endforeach
{{ $audit->links() }}</section>
@endif
</main></body></html>