@php
    $cashView = request('cash_view', $batches->total() ? 'branch' : 'company');
    $cashView = in_array($cashView, ['branch', 'company'], true) ? $cashView : 'branch';
    $money = fn ($minor) => number_format($minor / 100, 2);
    $date = fn ($value) => $value ? Illuminate\Support\Carbon::parse($value)->timezone('Asia/Manila')->format('M j, Y · g:i A').' PHT' : 'Not yet recorded';
@endphp
@push('styles')
<link rel="stylesheet" href="{{ asset('css/shophop-branch-cod.css') }}?v=20261112">
@endpush
<div class="branch-cod">
    <header class="bc-header">
        <div><p class="bc-eyebrow">BRANCH FINANCE</p><h1>COD Verification</h1><p>Review rider cash handovers and confirm the amount your branch received.</p></div>
        <a class="bc-link" href="{{ route('branch.dashboard') }}">← Branch dashboard</a>
    </header>

    <div class="bc-flow" aria-label="COD cash handover flow">
        <span><b>1</b> Rider hands over cash</span><i aria-hidden="true">→</i>
        <span><b>2</b> Branch counts &amp; verifies</span><i aria-hidden="true">→</i>
        <span><b>3</b> Logistics handles remittance</span>
    </div>
    @if(session('status'))<div role="status" class="bc-notice bc-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="bc-notice bc-warning">{{ $errors->first() }}</div>@endif

    <nav class="bc-tabs" aria-label="Who verifies the cash">
        <a href="{{ request()->fullUrlWithQuery(['cash_view' => 'branch', 'page' => null, 'handovers_page' => null]) }}" @if($cashView === 'branch') aria-current="page" @endif>Branch verification <span>{{ $batches->total() }}</span></a>
        <a href="{{ request()->fullUrlWithQuery(['cash_view' => 'company', 'page' => null, 'handovers_page' => null]) }}" @if($cashView === 'company') aria-current="page" @endif>Company review <span>{{ $directHandovers->total() }}</span></a>
    </nav>

    @if($cashView === 'branch')
        <div class="bc-section-title"><h2>Cash handovers to your branch</h2><p>Open a handover, count the cash, then confirm the actual amount received.</p></div>
        @forelse($batches as $batch)
            <article class="bc-card">
                <div class="bc-card-top">
                    <div><span class="bc-badge {{ $batch->status === 'submitted' ? 'bc-pending' : ($batch->status === 'discrepancy' ? 'bc-pending' : 'bc-done') }}">{{ match($batch->status) { 'submitted' => 'Needs verification', 'discrepancy' => 'Amount needs review', default => 'Receipt verified' } }}</span><h3>{{ $riderNames->get($batch->rider_id, 'Rider #'.$batch->rider_id) }}</h3><p class="bc-muted">{{ $date($batch->created_at) }}</p></div>
                    <div class="bc-amount"><span>Cash handed over</span><strong>₱{{ $money($batch->submitted_minor) }}</strong></div>
                </div>
                <dl class="bc-meta"><div><dt>Receiving branch</dt><dd>{{ $centerNames->get($batch->sorting_center_id, 'Facility #'.$batch->sorting_center_id) }}</dd></div><div><dt>Receipt reference</dt><dd>{{ $batch->receipt_reference }}</dd></div></dl>
                @if(!$batch->verified_at)
                    <details class="bc-details" @if($errors->any()) open @endif><summary>Review &amp; verify cash <span aria-hidden="true">＋</span></summary>
                        <p class="bc-muted">Confirm only after the cash is physically with you. Enter the amount you counted for each collection.</p>
                        <form method="POST" action="{{ route('logistics.hybrid.verify', $batch->id) }}" class="bc-verify">@csrf
                            @foreach($items->get($batch->id, collect()) as $i => $item)
                                <div class="bc-cash-row">
                                    <div><strong>Collection #{{ $item->cod_settlement_id }}</strong><p class="bc-muted">Rider handed over ₱{{ $money($item->submitted_minor) }}</p></div>
                                    <input type="hidden" name="items[{{ $i }}][collection_id]" value="{{ $item->cod_settlement_id }}">
                                    <label>Actual cash received (₱)<input name="items[{{ $i }}][amount]" type="number" min="0" max="999999999" step="0.01" inputmode="decimal" required placeholder="0.00"></label>
                                </div>
                            @endforeach
                            <button class="bc-primary" type="submit">Confirm counted cash receipt</button>
                        </form>
                    </details>
                @else
                    <div class="bc-receipt"><span>Verified received <strong>₱{{ $money($batch->verified_minor) }}</strong></span><span>Verified by {{ $receiverNames->get($batch->verified_by_user_id) ?: 'User #'.$batch->verified_by_user_id }}</span></div>
                    @if($batch->status === 'discrepancy')<p class="bc-notice bc-warning">The counted amount differs from the expected cash. This handover remains under review.</p>@endif
                    <details class="bc-details"><summary>Receipt details <span aria-hidden="true">＋</span></summary><p class="bc-muted">Verified {{ $date($batch->verified_at) }} · Reference {{ $batch->reference }}</p><p>Expected ₱{{ $money($batch->expected_minor) }} · Difference ₱{{ $money($batch->difference_minor) }}</p></details>
                @endif
            </article>
        @empty
            <div class="bc-empty"><div aria-hidden="true">✓</div><h3>No cash handovers to verify</h3><p>When a rider selects your branch and submits a cash handover, it appears here.</p>@if($directHandovers->total())<a class="bc-link" href="{{ request()->fullUrlWithQuery(['cash_view' => 'company']) }}">View handovers for company review →</a>@endif</div>
        @endforelse
        <div class="bc-pagination">{{ $batches->appends(['cash_view' => 'branch'])->links() }}</div>
    @else
        <div class="bc-section-title"><h2>For logistics company review</h2><p>These handovers have no receiving branch recorded. Your logistics company verifies their receipt.</p></div>
        @forelse($directHandovers as $cash)
            <article class="bc-card">
                <div class="bc-card-top">
                    <div><span class="bc-badge {{ $cash->reconciled_at ? 'bc-done' : 'bc-pending' }}">{{ $cash->reconciled_at ? 'Receipt verified' : 'Awaiting company verification' }}</span><h3>Order #{{ $cash->order_id }}</h3><p class="bc-muted">{{ $cash->delivery?->tracking_code ?: 'Tracking unavailable' }}</p></div>
                    <div class="bc-amount"><span>Cash handed over</span><strong>₱{{ number_format((float)$cash->collected_amount, 2) }}</strong></div>
                </div>
                <dl class="bc-meta"><div><dt>From rider</dt><dd>{{ $cash->remitter?->name ?: 'Rider unavailable' }}</dd></div><div><dt>Company responsible</dt><dd>{{ $cash->partner?->company_name ?: 'Logistics company' }}</dd></div><div><dt>Submitted</dt><dd>{{ $date($cash->remitted_at) }}</dd></div><div><dt>Receiving branch</dt><dd>Not specified</dd></div></dl>
                @if($cash->reconciled_at)
                    <div class="bc-receipt"><span>Verified received <strong>₱{{ number_format((float)$cash->received_amount, 2) }}</strong></span><span>Verified by {{ $cash->reconciler?->display_name ?: 'Logistics user #'.$cash->reconciled_by_user_id }}</span></div>
                @else
                    <div class="bc-next"><strong>Next step · Logistics company</strong><p>Open COD Settlements → Order #{{ $cash->order_id }} → Manage to verify receipt.</p><span>No action needed from the rider. Do not submit this handover again.</span></div>
                @endif
            </article>
        @empty
            <div class="bc-empty"><div aria-hidden="true">✓</div><h3>No handovers for company review</h3><p>Cash handovers assigned to your branch appear under Branch verification.</p></div>
        @endforelse
        <div class="bc-pagination">{{ $directHandovers->appends(['cash_view' => 'company'])->links() }}</div>
    @endif
</div>
