@extends('layouts.app')
@section('title', 'Help Center - ShopHop')
@section('hideChrome', true)
@push('styles')
<link rel="stylesheet" href="{{ asset('css/shophop-buyer-discovery.css') }}?v=20261108">
@endpush
@section('content')
@include('buyer.partials.navbar-buyer')
<section class="discovery-page help-page"><div class="sh-shop-shell">
    <header class="discovery-header"><div><p class="discovery-eyebrow">Shopping support</p><h1 class="discovery-title">Help Center</h1><p class="discovery-description">Quick answers and a place to follow up on your concerns.</p></div><a class="discovery-secondary" href="{{ route('buyer.category.index') }}">Continue shopping <x-lucide-arrow-right /></a></header>
    @if(session('help_status'))<p class="help-success" role="status">{{ session('help_status') }}</p>@endif
    <div class="help-layout">
        <div class="help-surface">@include('buyer.help.content')</div>
        <aside class="help-support-column">
            <section class="help-surface" id="support-request">
                <h2>Contact support</h2><p class="help-muted">Tell us what happened so the ShopHop team can review your concern.</p>
                @if($supportEmail)<a class="help-contact-link" href="mailto:{{ $supportEmail }}"><x-lucide-mail />{{ $supportEmail }}</a>@endif
                @if($supportPhone)<p class="help-contact-link"><x-lucide-phone />{{ $supportPhone }}</p>@endif
                @if($canReport)
                    @if($errors->any())<div role="alert" class="help-error">{{ $errors->first() }}</div>@endif
                    <form method="POST" action="{{ route('buyer.help.requests') }}" class="help-request-form">
                        @csrf
                        <label for="help-type">What do you need help with?</label><select id="help-type" name="type" required>
                            @foreach(['other'=>'Technical problem or other concern','delivery'=>'Delivery problem','product'=>'Product issue','account'=>'Account concern'] as $value=>$label)<option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>@endforeach
                        </select>
                        <label for="help-subject">Subject</label><input id="help-subject" name="subject" value="{{ old('subject') }}" required minlength="3" maxlength="160" placeholder="A short summary of the issue">
                        <label for="help-description">What happened?</label><textarea id="help-description" name="description" required minlength="10" maxlength="4000" rows="5" placeholder="Describe the problem and include an order reference if relevant.">{{ old('description') }}</textarea>
                        <button type="submit" class="discovery-primary">Submit support request <x-lucide-send /></button>
                    </form>
                @else
                    <a class="discovery-primary help-signin" href="{{ route('login') }}">Sign in as a buyer to submit a request</a>
                @endif
            </section>
            @if($canReport)
                <section class="help-surface" id="support-requests"><h2>Your Support Requests</h2><p class="help-muted">Saved reports and their latest review status.</p>
                    @forelse($helpRequests as $report)
                        <article class="help-request"><div><span>{{ $report->case_no }}</span><span class="help-status">{{ $report->status_label }}</span></div><h3>{{ $report->subject }}</h3><time datetime="{{ $report->created_at->toDateString() }}">{{ $report->created_at->format('M j, Y') }}</time></article>
                    @empty<p class="help-muted">No support requests yet.</p>@endforelse
                    {{ $helpRequests->links() }}
                </section>
            @endif
        </aside>
    </div>
</div></section>
@include('partials.footer')
@endsection
@push('scripts')
<script src="{{ asset('js/shophop-buyer-discovery.js') }}?v=20261108" defer></script>
@endpush
