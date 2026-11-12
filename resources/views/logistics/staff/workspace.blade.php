@extends($staff->role === 'manager' && $staff->sortingCenter?->type === 'province_hub' && !$staff->sortingCenter?->is_main ? 'hub.layouts.console' : 'branch.layout.app')
@section('title', 'Account & Workspace')
@section('content')
<div class="space-y-4">
    <header class="sh-page-header">
        <div><p class="eyebrow">{{ $staff->sortingCenter->is_main ? 'Main Hub' : ($staff->sortingCenter->type === 'province_hub' ? 'Province Hub' : 'Station Branch') }} · Account</p>
            <h1 class="title">Account & Workspace</h1><p class="description">{{ $staff->sortingCenter->name }} · {{ $staff->partner->company_name }}</p></div>
        <span class="sh-badge sh-badge-info">{{ ucfirst($staff->role) }}</span>
    </header>
    @if(session('status'))<div class="sh-alert-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="sh-alert-error" role="alert">{{ $errors->first() }}</div>@endif
    <section class="ops-panel">
        <div class="ops-panel-head"><h2>Your assigned facility</h2><span class="sh-badge sh-badge-success">Active account</span></div>
        <dl class="sh-detail-grid p-4">
            <div><dt>Facility</dt><dd>{{ $staff->sortingCenter->name }}</dd></div>
            <div><dt>Facility code</dt><dd>{{ $staff->sortingCenter->code }}</dd></div>
            <div><dt>Signed in as</dt><dd>{{ $staff->name }}</dd></div>
            <div><dt>Login email</dt><dd>{{ $staff->email }}</dd></div>
            <div><dt>Address</dt><dd>{{ $staff->sortingCenter->addressLabel() }}</dd></div>
        </dl>
        <div class="flex flex-wrap gap-2 p-4 border-t border-gray-border">
            @if($staff->role==='manager' && ($staff->sortingCenter->is_main || $staff->sortingCenter->type==='station_hub') && app(\App\Services\RiderManagementService::class)->available())
                <a class="sh-btn" href="{{ route('logistics.staff.rider-management.index') }}">Open Rider Management</a>
            @endif
            @if($staff->role==='manager' && $staff->sortingCenter->type==='station_hub' && !$staff->sortingCenter->is_main && \Illuminate\Support\Facades\Schema::hasTable('rider_batches'))
                <a class="sh-btn-soft" href="{{ route('logistics.staff.dispatch.index') }}">Open Rider Dispatch</a>
            @endif
            @if($staff->role==='manager' && $staff->sortingCenter->type==='province_hub' && !$staff->sortingCenter->is_main)
                <a class="sh-btn" href="{{ route('logistics.hub.parcels') }}">Open Parcel Operations</a>
            @endif
        </div>
    </section>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>Recent facility scans</h2><p class="ops-panel-note">Latest recorded scans at your assigned facility.</p></div></div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th scope="col">Tracking code</th><th scope="col">Event</th><th scope="col">Recorded at</th></tr></thead><tbody>
            @forelse($recentScans as $scan)<tr><td><strong>{{ $scan->tracking_code }}</strong></td><td>{{ ucwords(str_replace('_',' ',$scan->event_type)) }}</td><td>{{ \Illuminate\Support\Carbon::parse($scan->scanned_at)->timezone('Asia/Manila')->format('M j, Y · g:i A') }} PHT</td></tr>
            @empty<tr><td colspan="3" class="ops-empty">No scans recorded at this facility yet.</td></tr>@endforelse
        </tbody></table></div>
    </section>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>Change password</h2><p class="ops-panel-note">Use at least 8 characters with mixed case, a number and a symbol.</p></div></div>
        <form method="POST" action="{{ route('logistics.staff.password') }}" class="p-4 space-y-4">@csrf
            <div class="grid gap-3 md:grid-cols-3">
                <label class="sh-field"><span class="sh-label">Current password</span><input class="sh-input" type="password" name="current_password" required autocomplete="current-password"></label>
                <label class="sh-field"><span class="sh-label">New password</span><input class="sh-input" type="password" name="password" required autocomplete="new-password" minlength="8"></label>
                <label class="sh-field"><span class="sh-label">Confirm new password</span><input class="sh-input" type="password" name="password_confirmation" required autocomplete="new-password" minlength="8"></label>
            </div>
            <button class="sh-btn" type="submit">Update password</button>
        </form>
    </section>
</div>
@endsection
