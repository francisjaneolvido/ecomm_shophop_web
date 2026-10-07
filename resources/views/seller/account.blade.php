{{-- Account data comes from the session-owned registration profile; demo mutation handlers are deliberately absent. --}}
@extends('seller.partials.layout')

@section('title', 'Account Management')

@section('content')
<div id="sellerAccount" class="space-y-5 max-w-5xl">
    {{-- Keep the existing Seller shell and palette while limiting the page to supported account outcomes. --}}
    <header>
        <h1 class="text-xl sm:text-2xl font-bold text-navy">Account Management</h1>
        <p class="text-sm text-navy/60 mt-1">Update your core profile and view your registered business details.</p>
    </header>

    {{-- Feedback reflects the server result and retains invalid input without claiming a failed write succeeded. --}}
    @if (session('status'))
        <p role="status" class="rounded-xl border border-teal/30 bg-teal-light p-4 text-sm text-navy">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-semibold">Profile changes were not saved. Correct the fields below.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- The native form remains usable without JavaScript and submits only fields supported by the current schema. --}}
    <section class="rounded-xl border border-gray-border bg-white p-4 sm:p-6" aria-labelledby="profileHeading">
        <h2 id="profileHeading" class="font-semibold text-navy">Personal Information</h2>
        <p class="mt-1 text-xs text-navy/60">Email: <span class="break-all">{{ auth()->user()->email }}</span>. Email changes require a separate verification flow and are unavailable here.</p>
        <form method="POST" action="{{ route('seller.account.update') }}" class="mt-5 space-y-4">
            @csrf
            @method('PATCH')
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach (['first_name' => 'First Name', 'last_name' => 'Last Name', 'middle_initial' => 'Middle Initial'] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="block text-xs font-medium text-navy mb-1">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $seller->$field) }}"
                            maxlength="{{ $field === 'middle_initial' ? 2 : 100 }}" @required($field !== 'middle_initial')
                            aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" @if ($errors->has($field)) aria-describedby="{{ $field }}Error" @endif
                            class="w-full rounded-lg border border-gray-border bg-white px-3 py-2.5 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40">
                        @error($field)<p id="{{ $field }}Error" class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div>
                    <label for="sex" class="block text-xs font-medium text-navy mb-1">Sex</label>
                    <select id="sex" name="sex" required aria-invalid="{{ $errors->has('sex') ? 'true' : 'false' }}" @error('sex') aria-describedby="sexError" @enderror
                        class="w-full rounded-lg border border-gray-border bg-white px-3 py-2.5 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40">
                        @foreach ($sexOptions as $option)
                            <option value="{{ $option }}" @selected(old('sex', $seller->sex) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('sex')<p id="sexError" class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="contact_no" class="block text-xs font-medium text-navy mb-1">Contact Number</label>
                    <input id="contact_no" name="contact_no" type="tel" inputmode="numeric" pattern="09[0-9]{9}" maxlength="11" required
                        value="{{ old('contact_no', $seller->contact_no) }}" aria-invalid="{{ $errors->has('contact_no') ? 'true' : 'false' }}" @error('contact_no') aria-describedby="contactError" @enderror
                        class="w-full rounded-lg border border-gray-border bg-white px-3 py-2.5 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40">
                    <p class="mt-1 text-xs text-navy/60">11 digits starting with 09.</p>
                    @error('contact_no')<p id="contactError" class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="birthday" class="block text-xs font-medium text-navy mb-1">Birthday</label>
                    <input id="birthday" name="birthday" type="date" required max="{{ now()->subDay()->format('Y-m-d') }}"
                        value="{{ old('birthday', $seller->birthday?->format('Y-m-d')) }}" aria-invalid="{{ $errors->has('birthday') ? 'true' : 'false' }}" @error('birthday') aria-describedby="birthdayError" @enderror
                        class="w-full rounded-lg border border-gray-border bg-white px-3 py-2.5 text-sm text-navy focus:outline-none focus:ring-2 focus:ring-teal/40">
                    @error('birthday')<p id="birthdayError" class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>
            <button type="submit" class="rounded-lg bg-teal-dark px-4 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-teal focus:ring-offset-2">Save Profile</button>
        </form>
    </section>

    {{-- Registered business/address and account approval are read-only; core edits cannot rewrite reviewed facts or expose private documents. --}}
    <section class="rounded-xl border border-gray-border bg-white p-4 sm:p-6" aria-labelledby="businessHeading">
        <h2 id="businessHeading" class="font-semibold text-navy">Registered Business</h2>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
            <div><dt class="text-xs text-navy/60">Business Name</dt><dd class="mt-1 text-navy break-words">{{ $seller->business_name }}</dd></div>
            <div><dt class="text-xs text-navy/60">Business Category</dt><dd class="mt-1 text-navy">{{ $seller->business_category }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs text-navy/60">Registered Address</dt><dd class="mt-1 text-navy break-words">{{ $seller->street_address }}, {{ $seller->barangay_name }}, {{ $seller->municipality_name }}, {{ $seller->province_name }}</dd></div>
            <div><dt class="text-xs text-navy/60">Account Status</dt><dd class="mt-1 text-navy">{{ ucfirst(auth()->user()->status) }}</dd></div>
        </dl>
        <p class="mt-4 text-xs text-navy/60">Business, address, and registration document changes are unavailable here.</p>
    </section>

    {{-- Unsupported controls have no pretend save/upload/deactivation outcome and cannot mutate client-only account state. --}}
    <section class="rounded-xl border border-gray-border bg-white p-4 sm:p-6" aria-labelledby="unavailableHeading">
        <h2 id="unavailableHeading" class="font-semibold text-navy">Unavailable Account Features</h2>
        <p class="mt-2 text-sm text-navy/60">Profile photo and shop logo uploads, password changes, notification preferences, and self-deactivation are unavailable. No changes to these features can be saved from this page.</p>
    </section>
</div>
@endsection
