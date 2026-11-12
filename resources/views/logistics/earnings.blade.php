@extends('logistics.layouts.console')
@section('title','Logistics Earnings & Payouts')
@section('content')
<a class="inline-block rounded border p-3 text-teal-700" href="{{ route('logistics.hybrid') }}">COD batches and partner compensation</a>

<div class="sh-ops sh-finance-ops space-y-5">
    <header class="sh-page-header"><div><p class="eyebrow">Finance · Revenue & distribution</p><h1 class="title">Logistics Earnings</h1><p class="description">Separate ShopHop settlement income from internal rider fees, linehaul driver payouts, and non-withdrawable Branch/Hub allocation records.</p></div><a class="sh-btn-soft" href="{{ route('logistics.settlements.index') }}">View COD Cash Custody</a></header>
    @if(session('status'))<div role="status" class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="sh-alert-error">{{ $errors->first() }}</div>@endif
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach([['ShopHop pending',$pending,'Awaiting available allocation'],['ShopHop available',$available,'Ready for logistics payout'],['ShopHop paid',$paid,'Recorded as paid'],['Linehaul available',$linehaulAvailable,'Eligible completed trips']] as [$label,$amount,$note])
            <div class="sh-stat"><p class="sh-stat-label">{{ $label }}</p><p class="sh-stat-value ops-money-value">₱{{ number_format((float)$amount,2) }}</p><p class="sh-stat-note">{{ $note }}</p></div>
        @endforeach
    </div>
    <p class="ops-inline-alert"><strong>COD cash ≠ Rider fees or Logistics earnings.</strong> Payout actions below only record verified external payments and require references; they do not transfer funds. Branch/Hub ledgers are attribution, not withdrawable wallets.</p>
    <section class="ops-panel" data-ops-tabs>
        <nav role="tablist" class="ops-tabs" aria-label="Logistics finance ledgers">
            <button class="ops-tab" type="button" role="tab" data-ops-tab="overview" aria-selected="true">Allocation overview</button>
            <button class="ops-tab" type="button" role="tab" data-ops-tab="settlements" aria-selected="false">ShopHop settlements ({{ $entries->total() }})</button>
            <button class="ops-tab" type="button" role="tab" data-ops-tab="riders" aria-selected="false">Rider payouts ({{ $riderEntries->total() }})</button>
            <button class="ops-tab" type="button" role="tab" data-ops-tab="linehaul" aria-selected="false">Linehaul payouts ({{ $linehaulEntries->total() }})</button>
            <button class="ops-tab" type="button" role="tab" data-ops-tab="centers" aria-selected="false">Center ledger ({{ $branchActivity->total() }})</button>
            <button class="ops-tab" type="button" role="tab" data-ops-tab="pickup" aria-selected="false">Pickup accruals</button>
        </nav>
        <section role="tabpanel" data-ops-panel="overview" id="overview" class="p-4 sm:p-5">
            <h2 class="text-sm font-bold text-navy">Internal shipping allocation snapshot</h2><p class="ops-panel-note">Accounting allocations from eligible order financial snapshots; not additional charges collected from Buyers.</p>
            <div class="grid grid-cols-2 gap-3 mt-4 md:grid-cols-4">
                @foreach(['gross_shipping'=>'Gross shipping','pickup_riders'=>'Pickup rider fees','delivery_riders'=>'Delivery rider fees','branches'=>'Branch allocation','hubs'=>'Province Hub allocation','linehaul_pool'=>'Linehaul pool','margin'=>'Logistics margin'] as $key=>$label)
                    <div class="ops-allocation-tile"><p>{{ $label }}</p><strong>₱{{ number_format((float)($allocationTotals[$key] ?? 0),2) }}</strong></div>
                @endforeach
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <div class="ops-modal-section"><h3>1. Verified custody</h3><p class="text-xs">Branch/Hub QR scans confirm the parcel's physical handoff. Scanning alone is not money transfer.</p></div>
                <div class="ops-modal-section"><h3>2. COD reconciliation</h3><p class="text-xs">Rider collection and Logistics remittance are tracked independently from payout accounting.</p></div>
                <div class="ops-modal-section"><h3>3. Actual payout</h3><p class="text-xs">Pending → Available → Paid. Recording Paid requires a real external payment reference.</p></div>
            </div>
        </section>
        <section role="tabpanel" data-ops-panel="settlements" id="settlements" hidden>
            <div class="ops-panel-head"><div><h2>ShopHop-to-Logistics settlements</h2><p class="ops-panel-note">Gross shipping entitlement, prepared and distributed by ShopHop Admin.</p></div><span class="sh-badge sh-badge-info">{{ $entries->total() }} records</span></div>
            <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Order</th><th>Amount</th><th>Status</th><th>Payment / Distribution reference</th></tr></thead><tbody>
                @forelse($entries as $entry)<tr><td><strong>#{{ $entry->order_id }}</strong></td><td class="ops-money">₱{{ number_format((float)$entry->amount,2) }}</td><td><span class="sh-badge {{ $entry->status === 'paid' ? 'sh-badge-success' : ($entry->status === 'available' ? 'sh-badge-info' : 'sh-badge-warning') }}">{{ ucfirst($entry->status) }}</span></td><td class="ops-wrap">{{ $entry->payment_reference ?? $entry->distribution_reference ?? '—' }}</td></tr>
                @empty<tr><td class="ops-empty" colspan="4">No ShopHop logistics settlement entries yet. COD receipt may already be reconciled, but Admin must still prepare and distribute the settlement.</td></tr>@endforelse
            </tbody></table></div><div class="ops-count">{{ $entries->firstItem() ?? 0 }}–{{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }}</div>@if($entries->hasPages())<div class="p-4 border-t">{{ $entries->links() }}</div>@endif
        </section>
        <section role="tabpanel" data-ops-panel="riders" id="riders" hidden>
            <div class="ops-panel-head"><div><h2>Pickup and delivery rider payouts</h2><p class="ops-panel-note">Fees are separate from COD cash. Only Available fees may be recorded as paid.</p></div></div>
            <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Order</th><th>Rider / Role</th><th>Fee</th><th>Status</th><th>Reference</th><th>Action</th></tr></thead><tbody>
                @forelse($riderEntries as $entry)<tr><td><strong>#{{ $entry->order_id }}</strong></td><td>{{ ucwords(str_replace('_',' ',$entry->kind)) }}<div class="ops-row-sub">Rider #{{ $entry->payee_id }}</div></td><td class="ops-money">₱{{ number_format((float)$entry->amount,2) }}</td><td><span class="sh-badge {{ $entry->status === 'paid' ? 'sh-badge-success' : 'sh-badge-info' }}">{{ ucfirst($entry->status) }}</span></td><td class="ops-wrap">{{ $entry->payment_reference ?? '—' }}</td><td class="ops-row-actions">@if($entry->status === 'pending' && in_array($entry->kind, ['pickup_rider', 'delivery_rider']))<form method="POST" action="{{ route('logistics.hybrid.legacy.approve', $entry->id) }}">@csrf<button class="border p-2">Approve historical Rider earning</button></form>@endif
@if($entry->status === 'available')<button class="ops-action" type="button" data-ops-template="rider-payout-{{ $entry->id }}" data-ops-title="Rider payout · Order #{{ $entry->order_id }}" data-ops-hint="Record actual transfer reference">Record paid</button>@else <span class="text-xs text-navy/50">—</span> @endif</td></tr>
                @empty<tr><td class="ops-empty" colspan="6">No rider earnings are prepared.</td></tr>@endforelse
            </tbody></table></div><div class="ops-count">{{ $riderEntries->firstItem() ?? 0 }}–{{ $riderEntries->lastItem() ?? 0 }} of {{ $riderEntries->total() }}</div>@if($riderEntries->hasPages())<div class="p-4 border-t">{{ $riderEntries->links() }}</div>@endif
        </section>
        <section role="tabpanel" data-ops-panel="linehaul" id="linehaul" hidden>
            <div class="ops-panel-head"><div><h2>Linehaul driver trip earnings</h2><p class="ops-panel-note">Fees are earned per completed manifest trip, not per parcel.</p></div><div class="flex gap-2 flex-wrap"><span class="sh-badge sh-badge-warning">Pending ₱{{ number_format((float)$linehaulPending,2) }}</span><span class="sh-badge sh-badge-info">Available ₱{{ number_format((float)$linehaulAvailable,2) }}</span><span class="sh-badge sh-badge-success">Paid ₱{{ number_format((float)$linehaulPaid,2) }}</span></div></div>
            <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Trip</th><th>Driver</th><th>Route</th><th>Fee</th><th>Status</th><th>Action</th></tr></thead><tbody>
                @forelse($linehaulEntries as $entry)<tr><td><strong>{{ $entry->trip?->code ?? '#'.$entry->linehaul_trip_id }}</strong></td><td>{{ $entry->driver?->name ?? 'Driver #'.$entry->driver_id }}</td><td>{{ $entry->trip?->fromCenter?->name ?? '—' }} → {{ $entry->trip?->toCenter?->name ?? '—' }}</td><td class="ops-money">₱{{ number_format((float)$entry->amount,2) }}</td><td><span class="sh-badge {{ $entry->status === 'paid' ? 'sh-badge-success' : 'sh-badge-info' }}">{{ ucfirst($entry->status) }}</span></td><td class="ops-row-actions">@if($entry->status === 'pending' && in_array($entry->kind, ['pickup_rider', 'delivery_rider']))<form method="POST" action="{{ route('logistics.hybrid.legacy.approve', $entry->id) }}">@csrf<button class="border p-2">Approve historical Rider earning</button></form>@endif
@if($entry->status === 'available')<button type="button" class="ops-action" data-ops-template="linehaul-payout-{{ $entry->id }}" data-ops-title="Linehaul payout · {{ $entry->trip?->code ?? '#'.$entry->linehaul_trip_id }}" data-ops-hint="Record actual payment reference">Record paid</button>@else<span class="ops-row-sub">{{ $entry->payment_reference ?: '—' }}</span>@endif</td></tr>
                @empty<tr><td class="ops-empty" colspan="6">No completed Linehaul Driver earnings yet.</td></tr>@endforelse
            </tbody></table></div><div class="ops-count">{{ $linehaulEntries->firstItem() ?? 0 }}–{{ $linehaulEntries->lastItem() ?? 0 }} of {{ $linehaulEntries->total() }}</div>@if($linehaulEntries->hasPages())<div class="p-4 border-t">{{ $linehaulEntries->links() }}</div>@endif
        </section>
        <section role="tabpanel" data-ops-panel="centers" id="centers" hidden>
            <div class="ops-panel-head"><div><h2>Branch / Province Hub attribution ledger</h2><p class="ops-panel-note">Scans and allocated revenue by center. This is an accounting/reporting view, not cash held by the center.</p></div></div>
            <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Center / Code</th><th>Type</th><th>Orders handled</th><th>Scans</th><th>Allocated revenue</th></tr></thead><tbody>
                @forelse($branchActivity as $branch)<tr><td><strong>{{ $branch['name'] }}</strong><div class="ops-row-sub">{{ $branch['code'] }}</div></td><td>{{ ucwords(str_replace('_',' ',$branch['type'] ?? 'station')) }}</td><td>{{ number_format($branch['handled_orders']) }}</td><td>{{ number_format($branch['scans']) }}</td><td class="ops-money">₱{{ number_format((float)$branch['allocated_revenue'],2) }}</td></tr>
                @empty<tr><td class="ops-empty" colspan="5">No registered centers or allocations.</td></tr>@endforelse
            </tbody></table></div><div class="ops-count">{{ $branchActivity->firstItem() ?? 0 }}–{{ $branchActivity->lastItem() ?? 0 }} of {{ $branchActivity->total() }} centers</div>@if($branchActivity->hasPages())<div class="p-4 border-t">{{ $branchActivity->links() }}</div>@endif
        </section>
        <section role="tabpanel" data-ops-panel="pickup" id="pickup" hidden>
            <div class="ops-panel-head"><div><h2>Provisional pickup rider accruals</h2><p class="ops-panel-note">Preview of up to 100 recent qualifying handoffs not yet converted to formal settlement entries. Totals are limited to this preview.</p></div><div class="flex gap-2 flex-wrap"><span class="sh-badge sh-badge-info">Pending ₱{{ number_format((float)($pickupProvisional['total_pending_release'] ?? 0),2) }}</span><span class="sh-badge sh-badge-warning">On hold ₱{{ number_format((float)($pickupProvisional['total_on_hold'] ?? 0),2) }}</span></div></div>
            <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Order</th><th>Rider</th><th>Accrual fee</th><th>Status</th></tr></thead><tbody>
                @forelse(($pickupProvisional['entries'] ?? []) as $row)<tr><td><strong>#{{ $row['order_id'] }}</strong></td><td>Rider #{{ $row['rider_id'] }}</td><td class="ops-money">₱{{ number_format((float)$row['amount'],2) }}</td><td><span class="sh-badge {{ $row['status'] === 'on_hold' ? 'sh-badge-warning' : 'sh-badge-info' }}">{{ ucwords(str_replace('_',' ',$row['status'])) }}</span></td></tr>
                @empty<tr><td class="ops-empty" colspan="4">No provisional pickup accrual entries.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </section>
    @foreach($riderEntries as $entry)
        @if($entry->status === 'pending' && in_array($entry->kind, ['pickup_rider', 'delivery_rider']))<form method="POST" action="{{ route('logistics.hybrid.legacy.approve', $entry->id) }}">@csrf<button class="border p-2">Approve historical Rider earning</button></form>@endif
@if($entry->status === 'available')<template id="rider-payout-{{ $entry->id }}"><div class="ops-modal-meta"><p><strong>Order:</strong> #{{ $entry->order_id }}</p><p><strong>Rider:</strong> #{{ $entry->payee_id }}</p><p><strong>Role:</strong> {{ ucwords(str_replace('_',' ',$entry->kind)) }}</p><p><strong>Amount:</strong> ₱{{ number_format((float)$entry->amount,2) }}</p></div><form method="POST" action="{{ route('logistics.earnings.rider-paid',$entry->id) }}" class="space-y-3" data-confirm-title="Record rider payout?" data-confirm-message="Confirm the actual external payment before marking this fee paid. This action does NOT transfer funds." data-confirm-button="Record paid">@csrf<label class="sh-field"><span class="sh-label">Actual payout reference *</span><input class="sh-input" required maxlength="120" name="reference" placeholder="Transaction / receipt reference"></label><div class="ops-dialog-actions"><button class="sh-btn">Record rider payout</button></div></form></template>@endif
    @endforeach
    @foreach($linehaulEntries as $entry)
        @if($entry->status === 'pending' && in_array($entry->kind, ['pickup_rider', 'delivery_rider']))<form method="POST" action="{{ route('logistics.hybrid.legacy.approve', $entry->id) }}">@csrf<button class="border p-2">Approve historical Rider earning</button></form>@endif
@if($entry->status === 'available')<template id="linehaul-payout-{{ $entry->id }}"><div class="ops-modal-meta"><p><strong>Trip:</strong> {{ $entry->trip?->code ?? '#'.$entry->linehaul_trip_id }}</p><p><strong>Driver:</strong> {{ $entry->driver?->name ?? 'Driver #'.$entry->driver_id }}</p><p><strong>Amount:</strong> ₱{{ number_format((float)$entry->amount,2) }}</p></div><form method="POST" action="{{ route('logistics.earnings.linehaul-paid',$entry->id) }}" class="space-y-3" data-confirm-title="Record linehaul driver payout?" data-confirm-message="Only completed trips and verified external payments may be marked as paid. No funds are transferred here." data-confirm-button="Record paid">@csrf<label class="sh-field"><span class="sh-label">Actual payout reference *</span><input class="sh-input" required maxlength="120" name="reference" placeholder="Transaction / receipt reference"></label><div class="ops-dialog-actions"><button class="sh-btn">Record driver payout</button></div></form></template>@endif
    @endforeach
    <dialog class="ops-dialog" id="opsRecordDialog" aria-labelledby="opsRecordTitle"><div class="ops-dialog-head"><div><h2 class="ops-dialog-title" id="opsRecordTitle">Payout</h2><p class="ops-panel-note" id="opsRecordHint"></p></div><button type="button" class="ops-action" data-ops-close aria-label="Close">✕</button></div><div class="ops-dialog-body space-y-4" data-ops-record-content></div><div class="ops-dialog-foot"><button class="sh-btn-ghost" type="button" data-ops-close>Close</button></div></dialog>
</div>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/shophop-operations-table.css') }}?v=20261104-bh"><link rel="stylesheet" href="{{ asset('css/shophop-logistics-workspace.css') }}?v=20261104-finance">@endpush
@push('scripts')@endpush
