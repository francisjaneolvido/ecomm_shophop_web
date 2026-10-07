@extends('logistics.layouts')

@section('title', 'Riders — ShopHop Logistics')

@section('content')
<div class="mb-6">
    <h1 class="text-navy text-2xl sm:text-3xl font-bold">Riders</h1>
    <p class="text-navy/55 text-sm mt-1">Review Rider applications, assign each Rider to a Sorting Center, and set the branch-owned delivery area.</p>
</div>
@if (session('status')) <p role="status" class="mb-4 rounded-xl bg-teal-light p-3 text-teal-dark">{{ session('status') }}</p> @endif
@if ($errors->any()) <p role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-red-700">{{ $errors->first() }}</p> @endif

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @foreach ([
        'Pending review' => $riders->where('status', 'pending')->whereNotNull('email_verified_at')->count(),
        'Awaiting email' => $riders->where('status', 'pending')->whereNull('email_verified_at')->count(),
        'Active' => $riders->where('status', 'active')->count(),
        'Suspended / Rejected' => $riders->whereIn('status', ['suspended', 'rejected'])->count(),
    ] as $label => $count)
        <div class="rounded-2xl border border-gray-border bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-navy/45">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold text-navy">{{ $count }}</p>
        </div>
    @endforeach
</div>

<details class="rounded-2xl bg-gray-bg mb-6">
    <summary class="cursor-pointer px-4 py-3 font-semibold text-navy">Add Rider manually</summary>
    <form method="POST" action="{{ route('logistics.riders.store') }}" class="p-4 pt-1 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 items-end">
        @csrf
        <div><label for="rider-name" class="block text-xs font-semibold text-navy mb-1">Rider name</label><input id="rider-name" name="name" required maxlength="150" value="{{ old('name') }}" class="w-full rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
        <div><label for="rider-vehicle" class="block text-xs font-semibold text-navy mb-1">Vehicle type</label><input id="rider-vehicle" name="vehicle_type" required maxlength="80" value="{{ old('vehicle_type') }}" class="w-full rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
        <div><label for="rider-center" class="block text-xs font-semibold text-navy mb-1">Sorting Center</label><select id="rider-center" name="sorting_center_id" class="w-full rounded-xl border border-gray-border px-3 py-2 text-sm"><option value="">Main / default center</option>@foreach ($sortingCenters->where('status', 'active') as $center)<option value="{{ $center->id }}" @selected(old('sorting_center_id') == $center->id)>{{ $center->name }}</option>@endforeach</select></div>
        <div><label for="rider-area" class="block text-xs font-semibold text-navy mb-1">Delivery area</label><select id="rider-area" name="coverage_area_id" class="w-full rounded-xl border border-gray-border px-3 py-2 text-sm"><option value="">Pickup only / unassigned</option>@foreach ($coverageAreas as $area)<option value="{{ $area->id }}" @selected(old('coverage_area_id') == $area->id)>{{ $area->sortingCenter?->name ? $area->sortingCenter->name.' · ' : '' }}{{ $area->area_name }}{{ $area->cities ? ' · '.$area->cities : '' }}</option>@endforeach</select></div>
        <div><label for="rider-email" class="block text-xs font-semibold text-navy mb-1">Rider email</label><input id="rider-email" name="email" type="email" required value="{{ old('email') }}" class="w-full rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
        <div><label for="rider-password" class="block text-xs font-semibold text-navy mb-1">Temporary password</label><input id="rider-password" name="password" type="password" minlength="12" required class="w-full rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
        <div><label for="rider-password-confirmation" class="block text-xs font-semibold text-navy mb-1">Confirm password</label><input id="rider-password-confirmation" name="password_confirmation" type="password" minlength="12" required class="w-full rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
        <button class="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-teal">Add Rider</button>
    </form>
</details>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @forelse ($riders as $rider)
        @php
            $statusClasses = match ($rider->status) {
                'active' => 'bg-emerald-50 text-emerald-700',
                'pending' => 'bg-amber-50 text-amber-700',
                'rejected' => 'bg-red-50 text-red-700',
                'suspended' => 'bg-slate-100 text-slate-700',
                default => 'bg-gray-100 text-gray-700',
            };
        @endphp
        <article class="rounded-2xl border border-gray-border bg-white p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-navy">{{ $rider->name }}</h2>
                    <p class="text-sm text-navy/60">{{ $rider->vehicle_type }}{{ $rider->plate_number ? ' · '.$rider->plate_number : '' }}</p>
                    <p class="text-sm text-navy/60">{{ $rider->email ?: 'Credentials not provisioned' }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ ucfirst($rider->status) }}</span>
            </div>

            @if ($rider->applied_at)
                <div class="mt-4 rounded-xl bg-gray-bg p-3 text-xs text-navy/65 space-y-1">
                    <p><strong>Application:</strong> {{ $rider->email_verified_at ? 'Email verified' : 'Waiting for email verification' }} · {{ $rider->applied_at->diffForHumans() }}</p>
                    @if ($rider->contact_no)<p><strong>Contact:</strong> {{ $rider->contact_no }}</p>@endif
                    @if ($rider->applicationAddress())<p><strong>Address:</strong> {{ $rider->applicationAddress() }}</p>@endif
                    @if ($rider->birthday)<p><strong>Birthday:</strong> {{ $rider->birthday->format('M d, Y') }} · {{ $rider->sex }}</p>@endif
                    <div class="pt-2 flex flex-wrap gap-2">
                        @if ($rider->or_cr_path)
                            <a target="_blank" rel="noopener" href="{{ route('logistics.riders.documents.show', [$rider, 'or_cr']) }}" class="rounded-full border border-navy/20 bg-white px-3 py-1.5 font-semibold text-navy hover:bg-teal-light">View OR/CR</a>
                        @endif
                        @if ($rider->id_or_license_path)
                            <a target="_blank" rel="noopener" href="{{ route('logistics.riders.documents.show', [$rider, 'id_or_license']) }}" class="rounded-full border border-navy/20 bg-white px-3 py-1.5 font-semibold text-navy hover:bg-teal-light">View ID / License</a>
                        @endif
                    </div>
                </div>
            @endif

            @if ($rider->status === 'pending')
                @if ($rider->email_verified_at)
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <form method="POST" action="{{ route('logistics.riders.approve', $rider) }}">
                            @csrf
                            <button class="w-full rounded-full bg-teal px-4 py-2 text-xs font-semibold text-white hover:bg-teal-dark">Approve Rider</button>
                        </form>
                        <form method="POST" action="{{ route('logistics.riders.reject', $rider) }}" class="grid gap-2">
                            @csrf
                            <textarea name="rejection_reason" required maxlength="1000" rows="2" placeholder="Reason for rejection" class="w-full rounded-xl border border-gray-border px-3 py-2 text-xs"></textarea>
                            <button class="rounded-full border border-red-300 px-4 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Reject Rider</button>
                        </form>
                    </div>
                @else
                    <p class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-800">Approval is locked until the Rider verifies the registered email.</p>
                @endif
            @endif

            @if ($rider->status === 'rejected' && $rider->rejection_reason)
                <p class="mt-4 rounded-xl bg-red-50 p-3 text-xs text-red-700"><strong>Rejection reason:</strong> {{ $rider->rejection_reason }}</p>
            @endif

            @if (in_array($rider->status, ['active', 'suspended'], true))
                <div class="mt-4 grid gap-1 text-sm text-navy/60">
                    <p>Sorting Center: <strong>{{ $rider->sortingCenter?->name ?? 'Unassigned' }}</strong></p>
                    <p>Delivery area: <strong>{{ $rider->coverageArea?->area_name ?? 'Unassigned' }}</strong></p>
                </div>

                <form method="POST" action="{{ route('logistics.riders.area', $rider) }}" class="mt-3 grid gap-2 sm:grid-cols-[1fr_1.4fr_auto] items-center">@csrf
                    <select name="sorting_center_id" class="min-w-0 rounded-xl border border-gray-border px-3 py-2 text-sm">
                        <option value="">Main / default center</option>
                        @foreach ($sortingCenters->where('status', 'active') as $center)
                            <option value="{{ $center->id }}" @selected($rider->sorting_center_id === $center->id)>{{ $center->name }}</option>
                        @endforeach
                    </select>
                    <select name="coverage_area_id" class="min-w-0 rounded-xl border border-gray-border px-3 py-2 text-sm">
                        <option value="">Pickup only / unassigned</option>
                        @foreach ($coverageAreas as $area)
                            <option value="{{ $area->id }}" @selected($rider->coverage_area_id === $area->id)>{{ $area->sortingCenter?->name ? $area->sortingCenter->name.' · ' : '' }}{{ $area->area_name }}{{ $area->cities ? ' · '.$area->cities : '' }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-full border border-navy px-3 py-2 text-xs font-semibold">Update assignment</button>
                </form>

                @if (! $rider->email)
                    <form method="POST" action="{{ route('logistics.riders.provision', $rider) }}" class="mt-3 grid gap-2">@csrf
                        <input name="email" type="email" required aria-label="Rider email" placeholder="Email" class="rounded-xl border border-gray-border p-2">
                        <input name="password" type="password" minlength="12" required aria-label="Rider password" placeholder="Password" class="rounded-xl border border-gray-border p-2">
                        <input name="password_confirmation" type="password" minlength="12" required aria-label="Confirm Rider password" placeholder="Confirm password" class="rounded-xl border border-gray-border p-2">
                        <button class="rounded-full border border-navy p-2 text-xs font-semibold">Provision credentials</button>
                    </form>
                @endif

                <form method="POST" action="{{ $rider->status === 'active' ? route('logistics.riders.suspend', $rider) : route('logistics.riders.activate', $rider) }}" class="mt-3">@csrf
                    <button class="rounded-full border border-navy px-4 py-2 text-xs font-semibold text-navy hover:bg-teal-light">{{ $rider->status === 'active' ? 'Suspend' : 'Activate' }}</button>
                </form>
            @endif
        </article>
    @empty
        <p class="text-sm text-navy/50">No Riders yet. Mobile Rider applications will appear here after registration.</p>
    @endforelse
</div>
@endsection
