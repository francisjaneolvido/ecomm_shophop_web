@extends('logistics.layouts.console')
@section('title', 'Branches — ShopHop Logistics')
@section('content')
<div class="ops-page space-y-4">
 @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
 @if($errors->any())<div class="sh-alert-error">{{ $errors->first() }}</div>@endif
 <header class="flex flex-wrap justify-between items-end gap-3">
  <div><p class="eyebrow">NETWORK & PEOPLE</p><h1 class="ops-title">City / Municipal Branches</h1>
   <p class="ops-subtitle">Branches are local stations. Province Hubs manage their child branches; barangays are service coverage, not physical hubs.</p></div>
  <a href="{{ route('logistics.sorting-centers.index') }}" class="ops-button">← Province Hubs</a>
 </header>
 <div class="ops-note">Manage branch creation and credentials through the owning Province Hub's authorized manager portal. This directory shows all current branches under your logistics partner, not just demo entries.</div>
 <section class="ops-panel">
  <form class="ops-filters" method="GET" action="{{ route('logistics.branches.index') }}">
   <label class="sh-field"><span class="sh-label">Branch / city / code</span><input class="sh-input" name="q" value="{{ request('q') }}" placeholder="Search branches"></label>
   <label class="sh-field"><span class="sh-label">Province</span><select name="province" class="sh-select"><option value="">All provinces</option>@foreach($provinces as $province)<option value="{{ $province }}" @selected(request('province')===$province)>{{ $province }}</option>@endforeach</select></label>
   <label class="sh-field"><span class="sh-label">Status</span><select name="status" class="sh-select"><option value="">All statuses</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Inactive</option></select></label>
   <button class="ops-button ops-button-primary">Filter</button><a href="{{ route('logistics.branches.index') }}" class="ops-button">Clear</a>
  </form>
  <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Branch</th><th>Location</th><th>Parent Province Hub</th><th>Branch Login</th><th>Riders</th><th>Status</th><th>Actions</th></tr></thead><tbody>
   @forelse($branches as $branch)
    <tr><td><div class="ops-primary">{{ $branch->name }}</div><div class="ops-secondary">{{ $branch->code }}</div></td>
    <td><div>{{ $branch->municipality ?: '—' }}</div><div class="ops-secondary">{{ $branch->province ?: 'NCR' }} · {{ $branch->region }}</div></td>
    <td>{{ $branch->parent?->name ?: 'Independent / unlinked' }}</td>
    <td><div class="text-xs break-all">{{ $branch->user?->email ?: 'Managed in branch portal' }}</div></td>
    <td class="font-bold">{{ $branch->riders_count }}</td>
    <td><span class="ops-badge" data-status="{{ $branch->status }}">{{ ucfirst($branch->status) }}</span></td>
    <td><details class="ops-actions"><summary class="ops-button">Actions ▾</summary><div class="ops-actions-menu"><a data-console-details href="{{ route('logistics.sorting-centers.show', $branch) }}">View branch details</a><button type="button" data-ops-open="branchProfileModal" data-ops-record-edit="branchProfileModal" data-subject="{{ $branch->name }}" data-url="{{ route('logistics.branches.profile',$branch) }}" data-fields="{{ json_encode(['name'=>$branch->name,'contact_no'=>$branch->contact_no,'street_no'=>$branch->street_no,'unit_no'=>$branch->unit_no],JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_HEX_TAG) }}">Edit branch details</button>@if($branch->parent_id)<a data-console-details href="{{ route('logistics.sorting-centers.show', $branch->parent_id) }}">View parent hub</a>@endif <a href="{{ route('logistics.riders.index', ['center'=>$branch->id]) }}">View branch riders</a></div></details></td></tr>
   @empty<tr><td colspan="7" class="ops-empty">No branches match your search.</td></tr>@endforelse
  </tbody></table></div>
  <footer class="ops-footer"><span>Showing {{ $branches->firstItem() ?? 0 }}–{{ $branches->lastItem() ?? 0 }} of {{ number_format($branches->total()) }} branches</span><div>{{ $branches->links() }}</div></footer>
 </section>
</div>
<dialog data-ops-modal class="ops-modal" id="branchProfileModal"><div class="ops-modal-head"><div><h2>Edit branch details</h2><p data-ops-subject></p></div><button type="button" class="ops-button" data-ops-close>✕</button></div>
<form method="POST" action="" data-confirm-title="Save branch details?" data-confirm-message="This changes only the branch name and contact/street description, not its parent hub or service coverage." data-confirm-button="Save branch">@csrf
<div class="ops-modal-body"><div class="ops-note mb-3">Changing the parent Province Hub, municipality or active service areas requires authorized operational review.</div><div class="ops-fields">
<label class="sh-field"><span class="sh-label">Branch name *</span><input name="name" class="sh-input" required maxlength="180"></label>
<label class="sh-field"><span class="sh-label">Contact number</span><input name="contact_no" class="sh-input" maxlength="30"></label>
<label class="sh-field"><span class="sh-label">Street address *</span><input name="street_no" class="sh-input" required maxlength="180"></label>
<label class="sh-field"><span class="sh-label">Unit / Landmark</span><input name="unit_no" class="sh-input" maxlength="180"></label>
</div></div><div class="ops-modal-footer"><button class="ops-button" type="button" data-ops-close>Cancel</button><button class="ops-button ops-button-primary">Save changes</button></div></form></dialog>
@endsection
