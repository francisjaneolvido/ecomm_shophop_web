@extends('logistics.layouts.console')
@php
    $editing = (bool) $center;
    $heading = $editing ? 'Edit branch' : 'Set up a new branch';
    $coverage = old('coverage', $editing ? $center->coverageAreas->map(fn ($area) => ['area_type'=>$area->area_type, 'area_name'=>$area->area_name, 'cities'=>$area->cities])->toArray() : [['area_type'=>'province','area_name'=>'','cities'=>'ALL']]);
    if (!$coverage) $coverage = [['area_type'=>'province','area_name'=>'','cities'=>'ALL']];
@endphp
@section('title', $heading.' — ShopHop Logistics')
@section('content')
<div class="mx-auto max-w-5xl space-y-5">
    <a href="{{ $editing ? route('logistics.sorting-centers.show', $center) : route('logistics.sorting-centers.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-teal-dark hover:underline"><x-lucide-arrow-left class="h-4 w-4"/> {{ $editing ? 'Back to branch' : 'Back to branch directory' }}</a>
    <div><p class="eyebrow">{{ $partner->company_name }} · BRANCH MANAGEMENT</p><h1 class="title">{{ $heading }}</h1><p class="mt-2 max-w-3xl text-sm leading-6 text-navy/60">The warehouse address is where parcels arrive. Delivery coverage defines where this branch can serve customers. These are separate settings.</p></div>
    @if($errors->any())<div role="alert" class="sh-alert-error"><strong>Review the highlighted information before saving.</strong><ul class="mt-2 list-disc space-y-1 pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form id="branchSetupForm" method="POST" action="{{ $editing ? route('logistics.sorting-centers.update', $center) : route('logistics.sorting-centers.store') }}" data-ph-branch data-branch-wizard>
        @csrf
        <div class="sh-surface overflow-hidden">
            <div class="grid grid-cols-2 gap-2 border-b border-gray-border bg-gray-bg/40 p-4 md:grid-cols-4" aria-label="Branch form steps">
                @foreach(['Branch details','Warehouse address','Delivery coverage','Review'.($editing ? '' : ' & manager')] as $step => $label)
                    <button type="button" data-step-link="{{ $step }}" aria-current="{{ $step === 0 ? 'step' : 'false' }}" class="rounded-xl border border-gray-border bg-white p-3 text-left text-xs font-semibold text-navy/65"><span class="mr-1 text-teal-dark">{{ $step + 1 }}.</span> {{ $label }}</button>
                @endforeach
            </div>
            <div class="p-5 sm:p-7">
                <section data-wizard-step="0" class="space-y-5">
                    <div><h2 class="text-lg font-bold text-navy">Branch details</h2><p class="mt-1 text-sm text-navy/55">Use a recognizable facility name. The branch code is generated automatically.</p></div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="sh-field"><span class="sh-label">Branch / sorting hub name *</span><input class="sh-input" name="name" value="{{ old('name', $center?->name) }}" required maxlength="180" placeholder="Example: Santa Cruz Sorting Hub"></label>
                        <label class="sh-field"><span class="sh-label">Contact number</span><input class="sh-input" name="contact_no" value="{{ old('contact_no', $center?->contact_no) }}" maxlength="30" placeholder="09XX XXX XXXX"></label>
                    </div>
                    <div class="rounded-xl border border-teal/20 bg-teal-light/40 p-4 text-xs leading-6 text-navy/70"><strong>Who owns this branch?</strong> {{ $partner->company_name }}. It does not have a separate company registration or wallet. You will assign people who operate the facility.</div>
                </section>
                <section data-wizard-step="1" hidden class="space-y-5">
                    <div><h2 class="text-lg font-bold text-navy">Physical warehouse address</h2><p class="mt-1 text-sm text-navy/55">Where the scanner operator will physically receive parcels. Select from the Philippine address list, including NCR.</p></div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach([['region','Region','Region'], ['province','Province / NCR','Province (not needed for NCR)'], ['municipality','City / municipality','City or municipality'], ['barangay','Barangay','Barangay'], ['street_no','Street / house number','Street, building, house no.'], ['unit_no','Unit / warehouse details (optional)','Floor, suite or warehouse bay']] as [$field,$label,$placeholder])
                            <label class="sh-field"><span class="sh-label">{{ $label }}{{ $field === 'unit_no' ? '' : ' *' }}</span><input class="sh-input" name="{{ $field }}" value="{{ old($field, $center?->{$field}) }}" {{ $field === 'unit_no' ? '' : 'required' }} maxlength="180" placeholder="{{ $placeholder }}"></label>
                        @endforeach
                    </div>
                </section>
                <section data-wizard-step="2" hidden class="space-y-5">
                    <div><h2 class="text-lg font-bold text-navy">Delivery service coverage</h2><p class="mt-1 text-sm text-navy/55">Set the city/municipality areas this branch can deliver to. <strong>Coverage is not the warehouse address.</strong> You can serve multiple cities from one branch.</p></div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-5 text-amber-900">If a coverage area is different from your warehouse province or region, double-check that this is intentional. For existing branches, changing an area already tied to parcels is protected by the backend.</div>
                    <div class="space-y-3" data-coverage-list="branch-form">
                        @foreach($coverage as $i => $area)
                            <div data-coverage-row class="rounded-2xl border border-gray-border bg-gray-bg/30 p-4">
                                <div class="grid gap-3 sm:grid-cols-[150px_minmax(0,1fr)]">
                                    <label class="sh-field"><span class="sh-label">Area level *</span><select class="sh-select" name="coverage[{{ $i }}][area_type]" required><option value="province" @selected(($area['area_type']??'province')==='province')>Province</option><option value="region" @selected(($area['area_type']??'')==='region')>Region (e.g. NCR)</option></select></label>
                                    <label class="sh-field"><span class="sh-label">Province / Region *</span><input class="sh-input" name="coverage[{{ $i }}][area_name]" value="{{ $area['area_name'] ?? '' }}" required maxlength="150" placeholder="Select province or region"></label>
                                </div>
                                <label class="sh-field mt-3"><span class="sh-label">Cities / municipalities served</span><input class="sh-input" name="coverage[{{ $i }}][cities]" value="{{ $area['cities'] ?? 'ALL' }}" maxlength="5000" placeholder="ALL or city names separated by |"></label>
                                <div class="mt-3 flex justify-end"><button type="button" data-remove-coverage class="sh-btn-ghost text-xs">Remove this area</button></div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" data-add-coverage="branch-form" class="sh-btn-soft">+ Add another service area</button>
                    <p class="text-xs text-navy/55">For several cities, tap individual city checkboxes. You do not need to hold Ctrl/Cmd.</p>
                </section>
                <section data-wizard-step="3" hidden class="space-y-5">
                    <div><h2 class="text-lg font-bold text-navy">Review &amp; {{ $editing ? 'save changes' : 'branch representative' }}</h2><p class="mt-1 text-sm text-navy/55">Confirm your settings before saving. Creating a new branch always starts as a draft/inactive facility.</p></div>
                    @unless($editing)
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="sh-field"><span class="sh-label">Initial branch manager (optional)</span><input class="sh-input" name="manager_name" value="{{ old('manager_name') }}" maxlength="150" placeholder="Manager full name"></label>
                            <label class="sh-field"><span class="sh-label">Manager email (optional)</span><input class="sh-input" type="email" name="manager_email" value="{{ old('manager_email') }}" maxlength="190" placeholder="manager@example.com"></label>
                        </div>
                        <p class="text-xs text-navy/55">Leave both blank to add the manager after saving. If provided, a temporary login password is shown once on the next page.</p>
                    @else
                        <p class="rounded-xl bg-gray-bg p-4 text-xs text-navy/65">Manager accounts are managed on the branch details page, not in this editor. Changing the address does not create a new branch or alter existing order address snapshots.</p>
                    @endunless
                    <div class="rounded-2xl border border-teal/25 bg-teal-light/30 p-4 text-sm leading-6 text-navy/75">
                        <p class="font-bold text-navy">Before you continue</p>
                        <div class="mt-3 grid gap-2" data-branch-review></div>
                        <p class="mt-3 text-xs">{{ $editing ? 'Existing branch status stays unchanged.' : 'New branch: Draft → assign manager → verify coverage → Activate → assign riders.' }}</p>
                    </div>
                </section>
                <div class="mt-7 flex flex-wrap items-center justify-between gap-3 border-t border-gray-border pt-5">
                    <button type="button" data-wizard-back class="sh-btn-ghost" hidden>← Previous</button>
                    <span class="flex-1"></span>
                    <button type="button" data-wizard-next class="sh-btn-soft">Continue →</button>
                    <button type="submit" data-wizard-submit class="sh-btn" hidden>{{ $editing ? 'Save branch changes' : 'Create branch draft' }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
<template id="coverage-row-template">
    <div data-coverage-row class="rounded-2xl border border-gray-border bg-gray-bg/30 p-4">
        <div class="grid gap-3 sm:grid-cols-[150px_minmax(0,1fr)]">
            <label class="sh-field"><span class="sh-label">Area level *</span><select class="sh-select" data-field="area_type" required><option value="province">Province</option><option value="region">Region (e.g. NCR)</option></select></label>
            <label class="sh-field"><span class="sh-label">Province / Region *</span><input class="sh-input" data-field="area_name" required maxlength="150" placeholder="Select province or region"></label>
        </div>
        <label class="sh-field mt-3"><span class="sh-label">Cities / municipalities served</span><input class="sh-input" data-field="cities" maxlength="5000" value="ALL"></label>
        <div class="mt-3 flex justify-end"><button type="button" data-remove-coverage class="sh-btn-ghost text-xs">Remove this area</button></div>
    </div>
</template>
@push('styles')
<style>#branchSetupForm [hidden] { display:none !important; }</style>
@endpush
@push('scripts')
<script src="{{ asset('js/shophop-ph-branches.js') }}"></script>
<script src="{{ asset('js/shophop-branch-wizard.js') }}"></script>
@endpush
@endsection
