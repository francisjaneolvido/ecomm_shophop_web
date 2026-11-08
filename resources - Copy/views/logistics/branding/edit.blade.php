@extends('logistics.layouts.console')

@section('title', 'Partner Profile & Logo')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
    <div>
        <p class="text-[10px] font-bold uppercase tracking-widest text-teal-dark">LOGISTICS / PUBLIC IDENTITY</p>
        <h1 class="mt-1 text-xl font-extrabold tracking-tight text-navy sm:text-2xl">Partner Profile &amp; Logo</h1>
        <p class="mt-1 text-xs text-navy/55">This is the company card riders see when choosing where to apply in ShopHop Mobile. Only approved partners are listed.</p>
    </div>

    @if(session('status'))
        <div role="status" class="rounded-md border border-teal/25 bg-teal-light p-3 text-xs font-semibold text-teal-dark">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div role="alert" class="rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700">
            <p class="font-bold">Please check the form:</p>
            <ul class="mt-1 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.3fr)_minmax(260px,.7fr)]">
        <form action="{{ route('logistics.partner-profile.update') }}" method="POST" enctype="multipart/form-data" class="rounded-md border border-gray-border bg-white p-4 shadow-sm sm:p-5">
            @csrf
            <div class="mb-5 flex items-center gap-3">
                @if($partner->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($partner->logo_path) }}" alt="Current company logo" class="h-16 w-16 rounded-md border border-gray-border bg-white object-contain p-1">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-md bg-teal-light text-xl font-extrabold text-teal-dark">{{ strtoupper(mb_substr($partner->company_name, 0, 1)) }}</div>
                @endif
                <div><h2 class="text-sm font-bold text-navy">{{ $partner->company_name }}</h2><p class="mt-1 text-[11px] text-navy/50">Public company identity for Rider applications</p></div>
            </div>
            <label for="partnerLogo" class="block text-xs font-semibold text-navy">Company logo <span class="font-normal text-navy/45">(optional)</span></label>
            <input id="partnerLogo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full rounded-md border border-gray-border p-2.5 text-xs file:mr-3 file:rounded file:border-0 file:bg-teal-light file:px-3 file:py-2 file:font-semibold file:text-teal-dark">
            <p class="mt-1 text-[10px] text-navy/50">PNG, JPG, or WebP. Maximum 2 MB and 2400 × 2400 px. Square, transparent logos work best.</p>

            @if($partner->logo_path)
            <label class="mt-3 flex items-center gap-2 text-xs text-navy/65"><input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-border text-teal focus:ring-teal"> Remove current logo (if no replacement is selected)</label>
            @endif

            <label for="partnerDescription" class="mt-5 block text-xs font-semibold text-navy">About your logistics company</label>
            <textarea id="partnerDescription" name="public_description" rows="3" maxlength="280" class="mt-2 block w-full rounded-md border border-gray-border p-3 text-xs text-navy focus:border-teal focus:ring-teal" placeholder="Describe your delivery network and services in a few words.">{{ old('public_description', $partner->public_description) }}</textarea>
            <p class="mt-1 text-[10px] text-navy/50">Keep it brief, clear, and accurate (up to 280 characters). Publicly visible to riders.</p>

            <fieldset class="mt-5 rounded-md border border-gray-border bg-gray-bg/50 p-3">
                <legend class="px-1 text-xs font-bold text-navy">Rider applications</legend>
                <label class="flex items-start gap-2 text-xs text-navy">
                    <input type="hidden" name="accepting_riders" value="0">
                    <input type="checkbox" name="accepting_riders" value="1" @checked((bool) old('accepting_riders', $partner->accepting_riders)) class="mt-0.5 rounded border-gray-border text-teal focus:ring-teal">
                    <span><strong class="block">Accepting new Rider applications</strong><span class="mt-1 block text-[11px] leading-relaxed text-navy/50">Turn off to hide your company from new mobile Rider applications. Existing applications are not affected.</span></span>
                </label>
            </fieldset>

            <div class="mt-5 flex justify-end"><button type="submit" class="rounded-md bg-teal px-5 py-2.5 text-xs font-bold text-white transition hover:bg-teal-dark">Save Public Profile</button></div>
        </form>

        <aside class="rounded-md border border-gray-border bg-white p-4 shadow-sm sm:p-5">
            <p class="text-[10px] font-bold uppercase tracking-widest text-teal-dark">MOBILE PREVIEW</p>
            <h2 class="mt-1 text-sm font-bold text-navy">Your Rider selection card</h2>
            <p class="mt-1 text-[11px] leading-relaxed text-navy/50">This is a preview of the partner identity shown in the mobile list.</p>
            <div class="mt-5 rounded-md border border-teal/35 bg-teal-light/30 p-3">
                <div class="flex gap-3">
                    @if($partner->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($partner->logo_path) }}" alt="{{ $partner->company_name }} logo" class="h-14 w-14 rounded-md border border-gray-border bg-white object-contain p-1">
                    @else
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-md bg-teal-light text-xl font-extrabold text-teal-dark">{{ strtoupper(mb_substr($partner->company_name, 0, 1)) }}</div>
                    @endif
                    <div class="min-w-0"><p class="text-xs font-bold text-navy">{{ $partner->company_name }}</p><p class="mt-1 text-[11px] text-navy/60">{{ $partner->municipality }}, {{ $partner->province }}</p></div>
                </div>
                <p class="mt-3 text-[11px] leading-relaxed text-navy/65">{{ $partner->public_description ?: 'Your company description will appear here.' }}</p>
                <span class="mt-3 inline-flex rounded px-2 py-1 text-[10px] font-bold {{ $partner->accepting_riders ? 'bg-teal-light text-teal-dark' : 'bg-gray-100 text-navy/50' }}">{{ $partner->accepting_riders ? 'Accepting Riders' : 'Applications Closed' }}</span>
            </div>
            <p class="mt-4 text-[11px] leading-relaxed text-navy/50">Changes appear in the Rider registration list after the app refreshes its approved partners.</p>
        </aside>
    </div>
</div>
@endsection
