@extends('seller.partials.layout')
@section('title', 'Vouchers & Promotions')
@push('styles')<link rel="stylesheet" href="{{ asset('css/shophop-seller-workspace.css') }}?v=20261112-navy-pink-2">@endpush
@section('content')
<div class="ss-workspace" id="sellerPromotions">
    <header class="ss-page-head">
        <div><p class="ss-eyebrow">CATALOG / PROMOTIONS</p><h1>Vouchers & Promotions</h1><p>Manage your voucher codes and review product discounts in one place.</p></div>
        <button type="button" class="ss-btn ss-btn-primary" data-voucher-open>+ Create voucher</button>
    </header>

    @if(session('status'))<div class="ss-message ss-message-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="ss-message ss-message-error" role="alert"><strong>Voucher was not saved.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <section class="ss-stats ss-stats-five" aria-label="Promotion summary">
        <div class="ss-stat"><span>Total vouchers</span><strong>{{ number_format($counts['total']) }}</strong><small>All your voucher records</small></div>
        <div class="ss-stat"><span>Currently active</span><strong>{{ number_format($counts['active']) }}</strong><small>Within date and usage limits</small></div>
        <div class="ss-stat"><span>Scheduled</span><strong>{{ number_format($counts['scheduled']) }}</strong><small>Upcoming start dates</small></div>
        <div class="ss-stat"><span>Expired</span><strong>{{ number_format($counts['expired']) }}</strong><small>Past end date</small></div>
        <a class="ss-stat ss-stat-link" href="{{ route('seller.inventory', ['sort'=>'newest']) }}"><span>Discounted products</span><strong>{{ number_format($discountedProducts) }}</strong><small>Manage product discounts →</small></a>
    </section>

    <div class="ss-message ss-message-info"><strong>Important:</strong> Voucher codes currently appear on selected product pages. <strong>Voucher-code redemption at checkout is not yet available.</strong> Product price discounts are managed in Inventory and follow the existing compliance review process.</div>

    <section class="ss-panel">
        <div class="ss-panel-heading"><div><h2>Voucher directory</h2><p>Search, filter, schedule, enable or pause your codes.</p></div><span class="ss-count">{{ number_format($vouchers->total()) }} result(s)</span></div>
        <form method="GET" action="{{ route('seller.promotions') }}" class="ss-filter-bar">
            <label class="ss-field ss-field-search"><span>Voucher name / code</span><input class="ss-input" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Search vouchers..."></label>
            <label class="ss-field"><span>Status</span><select class="ss-input" name="status">@foreach(['all'=>'All statuses','active'=>'Active now','scheduled'=>'Scheduled','expired'=>'Expired','paused'=>'Paused','exhausted'=>'Usage exhausted'] as $val=>$label)<option value="{{ $val }}" @selected($status===$val)>{{ $label }}</option>@endforeach</select></label>
            <label class="ss-field"><span>Sort by</span><select class="ss-input" name="sort">@foreach(['newest'=>'Newest first','oldest'=>'Oldest first','code'=>'Code A–Z','ending'=>'Ending soon'] as $val=>$label)<option value="{{ $val }}" @selected($sort===$val)>{{ $label }}</option>@endforeach</select></label>
            <label class="ss-field ss-field-small"><span>Per page</span><select class="ss-input" name="per_page"><option value="25" @selected($perPage===25)>25</option><option value="50" @selected($perPage===50)>50</option></select></label>
            <button class="ss-btn ss-btn-primary" type="submit">Filter</button><a class="ss-btn ss-btn-light" href="{{ route('seller.promotions') }}">Clear</a>
        </form>
        <div class="ss-table-scroll"><table class="ss-table"><thead><tr><th>Voucher / Code</th><th>Discount</th><th>Products</th><th>Uses</th><th>Valid dates</th><th>Status</th><th class="ss-align-right">Actions</th></tr></thead><tbody>
            @forelse($vouchers as $voucher)
                @php
                    $state = $voucher->status !== 'active' ? 'Paused' : (($voucher->ends_at && $voucher->ends_at->isPast()) ? 'Expired' : (($voucher->starts_at && $voucher->starts_at->isFuture()) ? 'Scheduled' : (($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) ? 'Exhausted' : 'Active')));
                    $editData = [
                        'id'=>$voucher->id,'name'=>$voucher->name,'code'=>$voucher->code,
                        'kind'=>$voucher->kind ?? 'merchandise','type'=>$voucher->type,'value'=>(float)$voucher->value,
                        'min_order_amount'=>(float)$voucher->min_order_amount,'usage_limit'=>$voucher->usage_limit,
                        'max_discount'=>$voucher->max_discount,'per_buyer_limit'=>$voucher->per_buyer_limit,
                        'starts_at'=>$voucher->starts_at?->format('Y-m-d\TH:i'),
                        'ends_at'=>$voucher->ends_at?->format('Y-m-d\TH:i'),
                        'products'=>$voucher->products->map(fn($p)=>['id'=>$p->id,'name'=>$p->name])->values(),
                    ];
                @endphp
                <tr>
                    <td><strong>{{ $voucher->name }}</strong><div class="ss-secondary"><button type="button" class="ss-copy" data-copy-code="{{ $voucher->code }}" title="Copy code">{{ $voucher->code }} <span aria-hidden="true">⧉</span></button> · {{ ($voucher->kind ?? 'merchandise')==='shipping' ? 'Shipping' : 'Merchandise' }}</div></td>
                    <td><strong>{{ $voucher->type==='percent' ? number_format((float)$voucher->value,0).'%' : '₱'.number_format((float)$voucher->value,2) }}</strong><div class="ss-secondary">Min. ₱{{ number_format((float)$voucher->min_order_amount,2) }}</div></td>
                    <td>{{ number_format($voucher->products_count) }} product(s)</td>
                    <td>{{ number_format((int)$voucher->used_count) }} / {{ $voucher->usage_limit === null ? 'Unlimited' : number_format($voucher->usage_limit) }}</td>
                    <td><span class="ss-secondary">{{ $voucher->starts_at?->format('M j, Y') ?? 'No start' }} – {{ $voucher->ends_at?->format('M j, Y') ?? 'No end' }}</span></td>
                    <td><span class="ss-pill {{ $state==='Active' ? 'ss-pill-success' : ($state==='Expired'||$state==='Exhausted' ? 'ss-pill-warning' : 'ss-pill-neutral') }}">{{ $state }}</span></td>
                    <td><div class="ss-row-actions"><button type="button" class="ss-btn ss-btn-light ss-btn-sm" data-voucher-open data-voucher='@json($editData)'>Edit</button>
                        <form method="POST" action="{{ route('seller.inventory.vouchers.toggle',$voucher->id) }}">@csrf @method('PATCH')<input type="hidden" name="return_to" value="promotions"><button class="ss-btn ss-btn-light ss-btn-sm" type="submit">{{ $voucher->status==='active' ? 'Pause' : 'Enable' }}</button></form>
                        <form method="POST" action="{{ route('seller.inventory.vouchers.destroy',$voucher->id) }}" onsubmit="return confirm('Delete this voucher permanently?')">@csrf @method('DELETE')<input type="hidden" name="return_to" value="promotions"><button class="ss-btn ss-btn-danger ss-btn-sm" type="submit">Delete</button></form>
                    </div></td>
                </tr>
            @empty<tr><td colspan="7" class="ss-empty">No matching vouchers. Try clearing your filters or create your first voucher.</td></tr>@endforelse
        </tbody></table></div>
        <footer class="ss-table-footer"><span>Showing {{ $vouchers->firstItem() ?? 0 }}–{{ $vouchers->lastItem() ?? 0 }} of {{ number_format($vouchers->total()) }}</span><div>{{ $vouchers->onEachSide(1)->links() }}</div></footer>
    </section>
</div>

<div id="ssVoucherModal" class="ss-modal" hidden aria-hidden="true">
    <div class="ss-modal-backdrop" data-voucher-close></div>
    <section class="ss-dialog" role="dialog" aria-modal="true" aria-labelledby="ssVoucherTitle">
        <header class="ss-dialog-header"><div><p class="ss-eyebrow">SELLER VOUCHER</p><h2 id="ssVoucherTitle">Create Voucher</h2><p>Eligible Seller vouchers can be redeemed at Web and Mobile checkout, one per shop.</p></div><button type="button" class="ss-icon-close" data-voucher-close aria-label="Close voucher form">×</button></header>
        <form method="POST" action="{{ route('seller.inventory.vouchers.store') }}" id="ssVoucherForm" class="ss-dialog-form">
            @csrf <input type="hidden" name="return_to" value="promotions"><input type="hidden" name="voucher_id" id="ssVoucherId" value="{{ old('voucher_id') }}">
            <div class="ss-dialog-body">
                <div class="ss-form-grid">
                    <label class="ss-field"><span>Voucher name *</span><input class="ss-input" name="name" id="ssVoucherName" maxlength="255" value="{{ old('name') }}" required></label>
                    <label class="ss-field"><span>Code *</span><input class="ss-input" name="code" id="ssVoucherCode" maxlength="50" value="{{ old('code') }}" required placeholder="SAVE10"></label>
                    <label class="ss-field"><span>Applies to *</span><select class="ss-input" name="kind" id="ssVoucherKind" required><option value="merchandise">Merchandise</option><option value="shipping">Seller-funded shipping</option></select></label>
                    <label class="ss-field"><span>Discount type *</span><select class="ss-input" name="type" id="ssVoucherType" required><option value="percent">Percent</option><option value="fixed">Fixed amount (₱)</option></select></label>
                    <label class="ss-field"><span>Discount value *</span><input class="ss-input" type="number" id="ssVoucherValue" name="value" step="0.01" min="0.01" value="{{ old('value') }}" required></label>
                    <label class="ss-field"><span>Minimum order amount</span><input class="ss-input" type="number" id="ssVoucherMin" name="min_order_amount" step="0.01" min="0" value="{{ old('min_order_amount',0) }}"></label>
                    <label class="ss-field"><span>Usage limit</span><input class="ss-input" type="number" id="ssVoucherUsage" name="usage_limit" min="1" value="{{ old('usage_limit') }}" placeholder="Unlimited"></label>
                    <label class="ss-field"><span>Maximum discount (₱)</span><input class="ss-input" type="number" step="0.01" min="0.01" id="ssVoucherCap" name="max_discount" value="{{ old('max_discount') }}" placeholder="No extra cap"></label>
                    <label class="ss-field"><span>Uses per Buyer</span><input class="ss-input" type="number" min="1" id="ssVoucherBuyerLimit" name="per_buyer_limit" value="{{ old('per_buyer_limit') }}" placeholder="Unlimited"></label>
                    <div></div>
                    <label class="ss-field"><span>Starts at (optional)</span><input class="ss-input" type="datetime-local" id="ssVoucherStart" name="starts_at" value="{{ old('starts_at') }}"></label>
                    <label class="ss-field"><span>Ends at (optional)</span><input class="ss-input" type="datetime-local" id="ssVoucherEnd" name="ends_at" value="{{ old('ends_at') }}"></label>
                </div>
                <div class="ss-selected-head"><div><h3>Eligible product pages</h3><p>Select the products eligible for this voucher at checkout. Without eligible products, the voucher cannot be redeemed.</p></div><span id="ssSelectedCount" class="ss-pill ss-pill-neutral">0 selected</span></div>
                <input type="search" class="ss-input" id="ssProductSearch" placeholder="Search your active products by name or SKU..." autocomplete="off">
                <div id="ssProductResults" class="ss-product-results" aria-live="polite">Type to search or view the first 25 products.</div>
                <div id="ssSelectedProducts" class="ss-selected-products" aria-live="polite"></div>
            </div>
            <footer class="ss-dialog-footer"><button class="ss-btn ss-btn-light" type="button" data-voucher-close>Cancel</button><button class="ss-btn ss-btn-primary" type="submit">Save voucher</button></footer>
        </form>
    </section>
</div>
@endsection
@push('scripts')
<script>window.ShopHopPromotionsConfig = { searchUrl: @json(route('seller.promotions.products')), hasErrors: @json($errors->any()), oldProductIds: @json(old('product_ids', [])) };</script>
<script src="{{ asset('js/shophop-seller-promotions.js') }}?v=20261112-navy-pink-2" defer></script>
@endpush
