@extends('logistics.layouts')

@section('title', 'Riders — ShopHop Logistics')

@section('content')
{{-- Operator-managed Rider identity and availability are the only supported management contract. --}}
<div class="mb-6">
    <h1 class="text-navy text-2xl sm:text-3xl font-bold">Riders</h1>
    <p class="text-navy/55 text-sm mt-1">Your persisted Riders available for delivery assignment.</p>
</div>
@if (session('status')) <p role="status" class="mb-4 rounded-xl bg-teal-light p-3 text-teal-dark">{{ session('status') }}</p> @endif
@if ($errors->any()) <p role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-red-700">{{ $errors->first() }}</p> @endif

<form method="POST" action="{{ route('logistics.riders.store') }}" class="rounded-2xl bg-gray-bg p-4 mb-6 flex flex-wrap gap-3 items-end">
    @csrf
    <div><label for="rider-name" class="block text-xs font-semibold text-navy mb-1">Rider name</label><input id="rider-name" name="name" required maxlength="150" value="{{ old('name') }}" class="rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
    <div><label for="rider-vehicle" class="block text-xs font-semibold text-navy mb-1">Vehicle type</label><input id="rider-vehicle" name="vehicle_type" required maxlength="80" value="{{ old('vehicle_type') }}" class="rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
    {{-- Credentials are shown only at entry and stored as a hash; operators must pass them to the Rider securely. --}}
    <div><label for="rider-email" class="block text-xs font-semibold text-navy mb-1">Rider email</label><input id="rider-email" name="email" type="email" required value="{{ old('email') }}" class="rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
    <div><label for="rider-password" class="block text-xs font-semibold text-navy mb-1">Temporary password</label><input id="rider-password" name="password" type="password" minlength="12" required class="rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
    <div><label for="rider-password-confirmation" class="block text-xs font-semibold text-navy mb-1">Confirm password</label><input id="rider-password-confirmation" name="password_confirmation" type="password" minlength="12" required class="rounded-xl border border-gray-border px-3 py-2 text-sm"></div>
    <button class="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-teal">Add Rider</button>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @forelse ($riders as $rider)
        <article class="rounded-2xl border border-gray-border bg-white p-4">
            <h2 class="font-semibold text-navy">{{ $rider->name }}</h2>
        <p class="text-sm text-navy/60">{{ $rider->vehicle_type }} · {{ ucfirst($rider->status) }}</p>
        <p class="text-sm text-navy/60">{{ $rider->email ?: 'Credentials not provisioned' }}</p>
        @if (! $rider->email)
            {{-- Existing Rider rows stay usable as records until this explicit one-time credential step. --}}
            <form method="POST" action="{{ route('logistics.riders.provision', $rider) }}" class="mt-3 flex flex-wrap gap-2">@csrf
                <input name="email" type="email" required aria-label="Rider email" placeholder="Email" class="rounded border p-2">
                <input name="password" type="password" minlength="12" required aria-label="Rider password" placeholder="Password" class="rounded border p-2">
                <input name="password_confirmation" type="password" minlength="12" required aria-label="Confirm Rider password" placeholder="Confirm password" class="rounded border p-2">
                <button class="rounded border p-2">Provision credentials</button>
            </form>
        @endif
            {{-- Availability changes are scoped server-side and do not simulate approvals or warnings. --}}
            <form method="POST" action="{{ $rider->status === 'active' ? route('logistics.riders.suspend', $rider) : route('logistics.riders.activate', $rider) }}" class="mt-3">
                @csrf
                <button class="rounded-full border border-navy px-4 py-2 text-xs font-semibold text-navy hover:bg-teal-light">{{ $rider->status === 'active' ? 'Suspend' : 'Activate' }}</button>
            </form>
        </article>
    @empty
        <p class="text-sm text-navy/50">No Riders yet. Add one to begin assignment.</p>
    @endforelse
</div>
<p class="mt-6 text-xs text-navy/50">Applications, interviews, approvals, and warnings require a separate verified Rider onboarding contract.</p>
@endsection
