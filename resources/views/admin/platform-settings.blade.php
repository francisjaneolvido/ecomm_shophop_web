@extends('admin.layout')

@section('title', 'Platform Settings')

@section('content')
{{-- No configuration persistence contract exists, so editable controls cannot imply platform changes. --}}
<section id="adminPlatformSettings" aria-labelledby="platformSettingsTitle" class="space-y-6">
    <div>
        <h1 id="platformSettingsTitle" class="text-2xl font-bold text-navy">Platform Settings</h1>
        <p class="text-sm text-slate-500 mt-1">Platform configuration availability on ShopHop.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
        <span class="inline-flex rounded-full bg-mint/15 px-3 py-1 text-xs font-semibold text-mint-dark">Unavailable</span>
        <h2 class="text-lg font-semibold text-navy mt-4">Platform configuration changes are unavailable here.</h2>
        <p class="text-sm text-slate-500 mt-2">No live platform configuration is displayed or saved on this page.</p>
        <ul class="list-disc pl-5 mt-4 space-y-2 text-sm text-slate-500">
            <li>Platform name, description and support contact changes are unavailable.</li>
            <li>Maintenance mode cannot be changed here.</li>
            <li>Commission rates and payout schedules cannot be configured here.</li>
            <li>Platform notification preferences and security configuration are unavailable.</li>
        </ul>
    </div>
</section>
@endsection
