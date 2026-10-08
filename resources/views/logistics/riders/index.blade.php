@extends('logistics.layouts.console')

@section('title', 'Riders — ShopHop Logistics')

@section('content')
@php
    $pendingReview = $riders->where('status', 'pending')->whereNotNull('email_verified_at')->count();
    $awaitingEmail = $riders->where('status', 'pending')->whereNull('email_verified_at')->count();
    $activeCount = $riders->where('status', 'active')->count();
    $inactiveCount = $riders->whereIn('status', ['suspended', 'rejected'])->count();
@endphp

<div class="space-y-6">
    <header class="sh-page-header">
        <div>
            <p class="eyebrow">Network · Fleet management</p>
            <h1 class="title">Rider Management</h1>
            <p class="description">Review verified applications, approve or reject riders with confirmation modals, assign sorting centers and coverage areas, and manage active rider access in a cleaner workflow.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="#rider-directory" class="sh-btn-ghost">View riders <x-lucide-arrow-down class="h-4 w-4" /></a>
            <a href="{{ route('logistics.sorting-centers.index') }}" class="sh-btn-soft">Manage branches <x-lucide-arrow-up-right class="h-4 w-4" /></a>
        </div>
    </header>

    @if (session('status')) <div role="status" class="sh-alert-success">{{ session('status') }}</div> @endif
    @if ($errors->any()) <div role="alert" class="sh-alert-error">{{ $errors->first() }}</div> @endif

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="sh-stat"><p class="sh-stat-label">Pending review</p><p class="sh-stat-value">{{ $pendingReview }}</p><p class="sh-stat-note">Verified and ready for decision</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Awaiting email</p><p class="sh-stat-value">{{ $awaitingEmail }}</p><p class="sh-stat-note">Cannot be approved yet</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Active riders</p><p class="sh-stat-value">{{ $activeCount }}</p><p class="sh-stat-note">Operational accounts</p></div>
        <div class="sh-stat"><p class="sh-stat-label">Suspended / Rejected</p><p class="sh-stat-value">{{ $inactiveCount }}</p><p class="sh-stat-note">Inactive or denied</p></div>
    </div>

    <section class="sh-surface sh-card-pad">
        <details>
            <summary class="sh-summary sh-toolbar">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-light text-teal-dark"><x-lucide-user-plus class="h-5 w-5" /></div>
                    <div>
                        <h2 class="text-base font-bold text-navy">Add Rider manually</h2>
                        <p class="mt-1 text-xs text-navy/50">Create a rider account directly under this partner for immediate setup.</p>
                    </div>
                </div>
                <span class="sh-btn-soft">Open rider form</span>
            </summary>

            <form method="POST" action="{{ route('logistics.riders.store') }}" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <label class="sh-field"><span class="sh-label">Rider name</span><input id="rider-name" name="name" required maxlength="150" value="{{ old('name') }}" class="sh-input"></label>
                <label class="sh-field"><span class="sh-label">Vehicle type</span><input id="rider-vehicle" name="vehicle_type" required maxlength="80" value="{{ old('vehicle_type') }}" class="sh-input"></label>
                <label class="sh-field"><span class="sh-label">Sorting Center</span><select id="rider-center" name="sorting_center_id" class="sh-select"><option value="">Main / default center</option>@foreach ($sortingCenters->where('status', 'active') as $center)<option value="{{ $center->id }}" @selected(old('sorting_center_id') == $center->id)>{{ $center->name }}</option>@endforeach</select></label>
                <label class="sh-field"><span class="sh-label">Delivery area</span><select id="rider-area" name="coverage_area_id" class="sh-select"><option value="">Pickup only / unassigned</option>@foreach ($coverageAreas as $area)<option value="{{ $area->id }}" @selected(old('coverage_area_id') == $area->id)>{{ $area->sortingCenter?->name ? $area->sortingCenter->name.' · ' : '' }}{{ $area->area_name }}{{ $area->cities ? ' · '.$area->cities : '' }}</option>@endforeach</select></label>
                <label class="sh-field"><span class="sh-label">Rider email</span><input id="rider-email" name="email" type="email" required value="{{ old('email') }}" class="sh-input"></label>
                <label class="sh-field"><span class="sh-label">Temporary password</span><input id="rider-password" name="password" type="password" minlength="12" required class="sh-input"></label>
                <label class="sh-field"><span class="sh-label">Confirm password</span><input id="rider-password-confirmation" name="password_confirmation" type="password" minlength="12" required class="sh-input"></label>
                <div class="md:col-span-2 xl:col-span-3 flex justify-end"><button class="sh-btn">Add rider</button></div>
            </form>
        </details>
    </section>

    <div id="rider-directory" class="grid grid-cols-1 gap-5 2xl:grid-cols-2">
        @forelse ($riders as $rider)
            @php
                $statusClasses = match ($rider->status) {
                    'active' => 'sh-badge-success',
                    'pending' => 'sh-badge-warning',
                    'rejected' => 'sh-badge-danger',
                    'suspended' => 'sh-badge-neutral',
                    default => 'sh-badge-neutral',
                };
            @endphp

            <article class="sh-surface sh-card-pad">
                <div class="sh-toolbar items-start">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-base font-bold text-navy">{{ $rider->name }}</h2>
                            <span class="sh-badge {{ $statusClasses }}">{{ ucfirst($rider->status) }}</span>
                            @if ($rider->email_verified_at)
                                <span class="sh-badge sh-badge-info">Email verified</span>
                            @elseif ($rider->status === 'pending')
                                <span class="sh-badge sh-badge-warning">Waiting for email verification</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-navy/60">{{ $rider->vehicle_type }}{{ $rider->plate_number ? ' · '.$rider->plate_number : '' }}</p>
                        <p class="mt-1 text-sm text-navy/50">{{ $rider->email ?: 'Credentials not provisioned yet' }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-light text-teal-dark">
                        <x-lucide-bike class="h-5 w-5" />
                    </div>
                </div>

                @if ($rider->applied_at)
                    <div class="mt-4 sh-surface-soft p-4 text-xs text-navy/65">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <p><strong>Application:</strong> {{ $rider->applied_at->diffForHumans() }}</p>
                            <p><strong>Status gate:</strong> {{ $rider->email_verified_at ? 'Ready for review' : 'Email verification required' }}</p>
                            @if ($rider->contact_no)<p><strong>Contact:</strong> {{ $rider->contact_no }}</p>@endif
                            @if ($rider->birthday)<p><strong>Birthday:</strong> {{ $rider->birthday->format('M d, Y') }} · {{ $rider->sex }}</p>@endif
                            @if ($rider->applicationAddress())<p class="sm:col-span-2"><strong>Address:</strong> {{ $rider->applicationAddress() }}</p>@endif
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($rider->or_cr_path)
                                <a target="_blank" rel="noopener" href="{{ route('logistics.riders.documents.show', [$rider, 'or_cr']) }}" class="sh-btn-ghost">View OR/CR</a>
                            @endif
                            @if ($rider->id_or_license_path)
                                <a target="_blank" rel="noopener" href="{{ route('logistics.riders.documents.show', [$rider, 'id_or_license']) }}" class="sh-btn-ghost">View ID / License</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($rider->status === 'pending')
                    @if ($rider->email_verified_at)
                        <div class="mt-4 grid gap-3 lg:grid-cols-2">
                            <form method="POST" action="{{ route('logistics.riders.approve', $rider) }}" data-confirm-title="Approve this rider?" data-confirm-message="The rider will become active and can be assigned to deliveries." data-confirm-button="Approve rider">
                                @csrf
                                <button class="sh-btn w-full">Approve rider</button>
                            </form>
                            <form method="POST" action="{{ route('logistics.riders.reject', $rider) }}" class="sh-surface-soft p-4" data-confirm-title="Reject this rider application?" data-confirm-message="This decision marks the application as rejected. Make sure the rejection reason is complete." data-confirm-button="Reject rider" data-confirm-danger="1">
                                @csrf
                                <label class="sh-field"><span class="sh-label">Reason for rejection</span><textarea name="rejection_reason" required maxlength="1000" rows="3" placeholder="Explain why the rider application is being rejected." class="sh-textarea"></textarea></label>
                                <div class="mt-3 flex justify-end"><button class="sh-btn-danger">Reject rider</button></div>
                            </form>
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">Approval is locked until the rider verifies the registered email address.</div>
                    @endif
                @endif

                @if ($rider->status === 'rejected' && $rider->rejection_reason)
                    <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700"><strong>Rejection reason:</strong> {{ $rider->rejection_reason }}</div>
                @endif

                @if (in_array($rider->status, ['active', 'suspended'], true))
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="sh-surface-soft p-4">
                            <p class="sh-stat-label">Sorting Center</p>
                            <p class="mt-2 text-sm font-semibold text-navy">{{ $rider->sortingCenter?->name ?? 'Unassigned' }}</p>
                        </div>
                        <div class="sh-surface-soft p-4">
                            <p class="sh-stat-label">Delivery area</p>
                            <p class="mt-2 text-sm font-semibold text-navy">{{ $rider->coverageArea?->area_name ?? 'Unassigned' }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logistics.riders.area', $rider) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_1.4fr_auto] sm:items-end">
                        @csrf
                        <label class="sh-field"><span class="sh-label">Sorting center</span><select name="sorting_center_id" class="sh-select"><option value="">Main / default center</option>@foreach ($sortingCenters->where('status', 'active') as $center)<option value="{{ $center->id }}" @selected($rider->sorting_center_id === $center->id)>{{ $center->name }}</option>@endforeach</select></label>
                        <label class="sh-field"><span class="sh-label">Coverage area</span><select name="coverage_area_id" class="sh-select"><option value="">Pickup only / unassigned</option>@foreach ($coverageAreas as $area)<option value="{{ $area->id }}" @selected($rider->coverage_area_id === $area->id)>{{ $area->sortingCenter?->name ? $area->sortingCenter->name.' · ' : '' }}{{ $area->area_name }}{{ $area->cities ? ' · '.$area->cities : '' }}</option>@endforeach</select></label>
                        <button class="sh-btn-ghost">Update assignment</button>
                    </form>

                    @if (! $rider->email)
                        <form method="POST" action="{{ route('logistics.riders.provision', $rider) }}" class="mt-4 sh-surface-soft p-4" data-confirm-title="Provision rider credentials?" data-confirm-message="Login credentials will be created for this rider using the email and password below." data-confirm-button="Provision credentials">
                            @csrf
                            <p class="text-sm font-bold text-navy">Provision rider credentials</p>
                            <p class="mt-1 text-xs text-navy/45">Use a secure temporary password and share it safely with the rider.</p>
                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                <label class="sh-field"><span class="sh-label">Email</span><input name="email" type="email" required aria-label="Rider email" placeholder="Email" class="sh-input"></label>
                                <label class="sh-field"><span class="sh-label">Password</span><input name="password" type="password" minlength="12" required aria-label="Rider password" placeholder="Password" class="sh-input"></label>
                                <label class="sh-field"><span class="sh-label">Confirm password</span><input name="password_confirmation" type="password" minlength="12" required aria-label="Confirm Rider password" placeholder="Confirm password" class="sh-input"></label>
                            </div>
                            <div class="mt-4 flex justify-end"><button class="sh-btn">Provision credentials</button></div>
                        </form>
                    @endif

                    <form method="POST" action="{{ $rider->status === 'active' ? route('logistics.riders.suspend', $rider) : route('logistics.riders.activate', $rider) }}" class="mt-4" data-confirm-title="{{ $rider->status === 'active' ? 'Suspend this rider?' : 'Reactivate this rider?' }}" data-confirm-message="{{ $rider->status === 'active' ? 'The rider will no longer be assignable until reactivated.' : 'The rider will become available for assignment again.' }}" data-confirm-button="{{ $rider->status === 'active' ? 'Suspend rider' : 'Activate rider' }}" data-confirm-danger="{{ $rider->status === 'active' ? '1' : '0' }}">
                        @csrf
                        <button class="{{ $rider->status === 'active' ? 'sh-btn-danger' : 'sh-btn-ghost' }}">{{ $rider->status === 'active' ? 'Suspend rider' : 'Activate rider' }}</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="sh-empty 2xl:col-span-2">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-light text-teal-dark"><x-lucide-bike class="h-5 w-5" /></div>
                <p class="mt-3 text-sm font-semibold text-navy">No riders yet</p>
                <p class="mt-1 text-xs text-navy/45">Mobile rider applications will appear here after registration, or you may add one manually above.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
