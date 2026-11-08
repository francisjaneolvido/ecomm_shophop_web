@extends('logistics.layouts.console')
@section('title', 'Center Details — ShopHop Logistics')
@section('content')
<div class="ops-page space-y-4">
 <header class="flex flex-wrap justify-between items-start gap-3"><div><p class="eyebrow">NETWORK DIRECTORY · {{ $center->type === 'province_hub' ? 'PROVINCE HUB' : 'BRANCH' }}</p><h1 class="ops-title">{{ $center->name }}</h1><p class="ops-subtitle">{{ $center->code }} · {{ $center->addressLabel() }}</p></div><div class="flex gap-2 flex-wrap"><a class="ops-button" href="{{ $center->type === 'province_hub' ? route('logistics.sorting-centers.index') : route('logistics.branches.index') }}">← Back to directory</a>@if($center->type === 'province_hub')<button type="button" class="ops-button ops-button-primary" data-ops-open="showHubEdit">Edit hub</button>@endif</div></header>
 @if(session('status'))<div class="sh-alert-success">{{ session('status') }}</div>@endif
 @if($errors->any())<div class="sh-alert-error">{{ $errors->first() }}</div>@endif
 <div class="grid gap-3 sm:grid-cols-3"><div class="sh-stat"><p class="sh-stat-label">Type</p><p class="text-lg font-bold text-navy mt-2">{{ $center->type === 'province_hub' ? 'Province Hub' : 'Branch' }}</p></div><div class="sh-stat"><p class="sh-stat-label">Unfinished parcels</p><p class="sh-stat-value">{{ number_format($openParcels) }}</p></div><div class="sh-stat"><p class="sh-stat-label">In-transit transfers</p><p class="sh-stat-value">{{ number_format($inTransit) }}</p></div></div>
 <section class="ops-panel"><div class="ops-header"><div><h2 class="font-bold">Center information</h2><p class="ops-subtitle">Status and profile details</p></div><span class="ops-badge" data-status="{{ $center->status }}">{{ ucfirst($center->status) }}</span></div>
  <div class="grid sm:grid-cols-3 gap-4 p-5 text-sm"><div><p class="ops-muted text-xs">Region / Province</p><strong>{{ $center->region }} / {{ $center->province ?: 'NCR' }}</strong></div><div><p class="ops-muted text-xs">Municipality / Barangay</p><strong>{{ $center->municipality }} / {{ $center->barangay }}</strong></div><div><p class="ops-muted text-xs">Contact</p><strong>{{ $center->contact_no ?: 'Not provided' }}</strong></div></div>
 </section>
 @if($center->type === 'province_hub')
 <section class="ops-panel"><div class="ops-header"><div><h2 class="font-bold">Child Branches</h2><p class="ops-subtitle">Local station hubs registered under this province hub</p></div><a href="{{ route('logistics.branches.index', ['province'=>$center->province]) }}" class="ops-button">All branches</a></div>
  <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Branch</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody>
   @forelse($center->stationHubs()->orderBy('municipality')->get() as $branch)<tr><td><div class="ops-primary">{{ $branch->name }}</div><div class="ops-secondary">{{ $branch->code }}</div></td><td>{{ $branch->municipality }}</td><td><span class="ops-badge" data-status="{{ $branch->status }}">{{ ucfirst($branch->status) }}</span></td><td><a class="ops-button" href="{{ route('logistics.sorting-centers.show', $branch) }}">View</a></td></tr>@empty<tr><td colspan="4" class="ops-empty">No branches assigned yet. Province Hub Managers create branches from their portal.</td></tr>@endforelse
  </tbody></table></div></section>
 @else
 <section class="ops-panel"><div class="ops-header"><div><h2 class="font-bold">Assigned Riders</h2><p class="ops-subtitle">Branch-level rider operations and coverage management</p></div><a href="{{ route('logistics.riders.index', ['center'=>$center->id]) }}" class="ops-button">Manage riders</a></div><div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Rider</th><th>Email</th><th>Status</th></tr></thead><tbody>@forelse($center->riders as $rider)<tr><td class="ops-primary">{{ $rider->name }}</td><td>{{ $rider->email }}</td><td><span class="ops-badge" data-status="{{ $rider->status }}">{{ ucfirst($rider->status) }}</span></td></tr>@empty<tr><td colspan="3" class="ops-empty">No riders assigned to this branch.</td></tr>@endforelse</tbody></table></div></section>
 @endif
</div>
@if($center->type === 'province_hub')
<dialog data-ops-modal id="showHubEdit" class="ops-modal" aria-labelledby="showHubTitle"><div class="ops-modal-head"><div><h2 id="showHubTitle">Edit province hub</h2><p>Contact/address only; routing and assigned stations remain unchanged</p></div><button type="button" class="ops-button" data-ops-close>✕</button></div>
<form method="POST" action="{{ route('logistics.sorting-centers.profile',$center) }}" data-confirm-title="Save hub changes?" data-confirm-message="This updates the hub profile only." data-confirm-button="Save changes">@csrf
<div class="ops-modal-body"><div class="ops-fields">@foreach([['name','Hub name'],['contact_no','Contact number'],['region','Region'],['province','Province'],['municipality','City / Municipality'],['barangay','Barangay'],['street_no','Street address'],['unit_no','Unit / Landmark']] as [$field,$label])<label class="sh-field"><span class="sh-label">{{ $label }}</span><input class="sh-input" name="{{ $field }}" value="{{ old($field, $center->$field) }}" maxlength="180" @if(!in_array($field,['contact_no','unit_no'])) required @endif @if(in_array($field,['province','region'])) readonly @endif></label>@endforeach</div></div><div class="ops-modal-footer"><button type="button" class="ops-button" data-ops-close>Cancel</button><button class="ops-button ops-button-primary">Save changes</button></div></form></dialog>
@endif
@endsection
@push('scripts')
@if(request('edit') && $center->type === 'province_hub')
<script>document.getElementById('showHubEdit')?.showModal();</script>
@endif
@endpush
