@extends('logistics.layouts.public')

@section('title', 'Sign In | ShopHop Logistics')

@push('styles')
<style>
    .sh-auth-shell { min-height:calc(100vh - 133px); padding:clamp(36px,5vw,70px) 20px; display:flex; justify-content:center; align-items:center; background:radial-gradient(ellipse at 12% 8%,#def9f3 0%,transparent 44%),linear-gradient(135deg,#F9FCFD,#F1F6F8); }
    .sh-auth-grid { display:grid; grid-template-columns:minmax(0,1fr); gap:24px; width:min(100%,980px); align-items:stretch; }
    .sh-auth-card,.sh-auth-intro { border:1px solid #E1E9ED; border-radius:24px; background:#fff; box-shadow:0 18px 55px rgba(15,27,61,.08); }
    .sh-auth-card { padding:clamp(24px,4vw,42px); }
    .sh-auth-intro { padding:clamp(25px,4vw,42px); overflow:hidden; background:linear-gradient(140deg,#102747 4%,#0B625E 95%); color:#fff; display:none; }
    .sh-auth-brand { display:inline-flex; align-items:center; gap:12px; color:#0F1B3D; text-decoration:none; }
    .sh-auth-brand img { width:42px; height:42px; object-fit:contain; }
    .sh-auth-brand strong { display:block; font-size:15px; font-weight:800; line-height:1.2; }
    .sh-auth-brand small { display:block; margin-top:4px; font-size:9px; font-weight:700; letter-spacing:.17em; color:#118F80; }
    .sh-auth-head { margin-top:24px; }
    .sh-auth-head h1 { margin:0; color:#0F1B3D; font-size:clamp(23px,3.3vw,29px); line-height:1.2; letter-spacing:-.03em; font-weight:800; }
    .sh-auth-head p { margin:10px 0 0; max-width:31rem; font-size:12px; line-height:1.75; color:#64758B; }
    .sh-auth-field { display:block; margin-top:18px; }
    .sh-auth-label { display:block; margin-bottom:8px; font-size:12px; font-weight:700; color:#152748; }
    .sh-auth-input { width:100%; min-height:46px; padding:11px 14px; border:1px solid #DCE5EB; border-radius:12px; background:#fff; color:#152748; font:inherit; font-size:13px; outline:none; transition:border-color .2s,box-shadow .2s; }
    .sh-auth-input:focus { border-color:#21C3A6; box-shadow:0 0 0 4px rgba(33,195,166,.13); }
    .sh-auth-password-wrap { position:relative; }
    .sh-auth-password-wrap .sh-auth-input { padding-right:80px; }
    .sh-auth-show-password { position:absolute; right:8px; top:50%; transform:translateY(-50%); border:0; border-radius:8px; padding:7px 9px; color:#486176; background:transparent; font:inherit; font-size:11px; font-weight:700; cursor:pointer; }
    .sh-auth-show-password:hover { background:#F3F8F7; color:#128F7C; }
    .sh-auth-remember { display:flex; align-items:center; gap:8px; margin:16px 0 20px; color:#526982; font-size:11px; cursor:pointer; }
    .sh-auth-remember input { width:15px; height:15px; accent-color:#18A98E; }
    .sh-auth-primary { display:flex; align-items:center; justify-content:center; gap:10px; min-height:46px; width:100%; border:0; border-radius:12px; background:#18B99C; color:#fff; font:inherit; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 9px 18px rgba(24,185,156,.17); transition:background .2s,transform .2s; }
    .sh-auth-primary:hover { background:#119C86; transform:translateY(-1px); }
    .sh-auth-primary:disabled { opacity:.7; cursor:wait; }
    .sh-auth-separator { display:flex; align-items:center; gap:13px; margin:21px 0; color:#8B9AAA; font-size:11px; }
    .sh-auth-separator:before,.sh-auth-separator:after { content:""; flex:1; height:1px; background:#E4E9ED; }
    .sh-auth-google { display:flex; align-items:center; justify-content:center; gap:11px; width:100%; min-height:46px; padding:12px 14px; border:1px solid #DCE5EB; border-radius:12px; background:#fff; text-decoration:none; color:#1B304B; font:inherit; font-size:12px; font-weight:700; transition:background .2s,border-color .2s; }
    .sh-auth-google:hover { border-color:#91DACB; background:#F5FCFA; }
    .sh-auth-google svg { width:18px; height:18px; flex:none; }
    .sh-auth-notice { margin-top:18px; border:1px solid; border-radius:12px; padding:12px 14px; font-size:12px; line-height:1.7; }
    .sh-auth-notice.warning { background:#FFF9EA; border-color:#F3DDB3; color:#795B26; }
    .sh-auth-notice.error { background:#FFF5F5; border-color:#F5CECE; color:#953B3B; }
    .sh-auth-bottom { margin:22px 0 0; text-align:center; color:#607188; font-size:11px; line-height:1.9; }
    .sh-auth-bottom a { color:#128F7C; font-weight:700; text-decoration:none; }
    .sh-auth-bottom a:hover { text-decoration:underline; }
    .sh-auth-small-note { margin:13px 0 0; text-align:center; color:#7D8EA0; font-size:10px; line-height:1.7; }
    .sh-auth-intro h2 { margin:25px 0 0; font-size:clamp(23px,2.4vw,32px); line-height:1.3; font-weight:800; letter-spacing:-.035em; }
    .sh-auth-intro .eyebrow { display:inline-flex; border:1px solid rgba(167,248,231,.25); background:rgba(167,248,231,.09); color:#AEFFE9; padding:8px 12px; font-size:10px; font-weight:700; letter-spacing:.1em; border-radius:999px; }
    .sh-auth-intro p { margin:14px 0 0; color:#CFE7EC; font-size:12px; line-height:1.9; }
    .sh-auth-intro ul { padding:0; margin:30px 0 0; list-style:none; display:grid; gap:14px; }
    .sh-auth-intro li { display:flex; align-items:flex-start; gap:11px; color:#E9F6F7; font-size:12px; line-height:1.6; }
    .sh-auth-intro .step { display:flex; align-items:center; justify-content:center; width:29px; height:29px; flex:none; border-radius:9px; background:rgba(116,235,207,.17); color:#A7FFE7; font-size:11px; font-weight:700; }
    @media (min-width:840px) { .sh-auth-grid { grid-template-columns:minmax(0,1.07fr) minmax(0,.93fr); } .sh-auth-intro { display:block; } }
    @media (prefers-reduced-motion:reduce) { .sh-auth-primary, .sh-auth-google { transition:none; } }
</style>
@endpush

@section('content')
<section class="sh-auth-shell">
    <div class="sh-auth-grid">
        <div class="sh-auth-card">
            <a class="sh-auth-brand" href="{{ route('logistics.home') }}" aria-label="ShopHop Logistics homepage">
                <img src="{{ asset('images/logo.png') }}" alt="ShopHop logo">
                <span><strong>ShopHop</strong><small>LOGISTICS PARTNERS</small></span>
            </a>
            <div class="sh-auth-head">
                <h1>Welcome back</h1>
                <p>Sign in to your approved Logistics account to manage deliveries, sorting centers, and your rider fleet.</p>
            </div>

            @if (session('login_notice'))
                @php($notice = session('login_notice'))
                <div class="sh-auth-notice {{ ($notice['type'] ?? '') === 'error' ? 'error' : 'warning' }}" role="alert">
                    <strong>{{ $notice['title'] ?? 'Notice' }}</strong><br>{{ $notice['message'] ?? '' }}
                </div>
            @endif
            @if (request()->query('portal') === '1')
                <div class="sh-auth-notice warning" role="status">This portal is available to approved Logistics Partners only.</div>
            @endif
            @if ($errors->any())
                <div class="sh-auth-notice error" role="alert">{{ $errors->first() }}</div>
            @endif

            {{-- Existing password login action, field names, CSRF and approval checks remain unchanged. --}}
            <form method="POST" action="{{ route('logistics.login.submit') }}" id="logisticsSignInForm">
                @csrf
                <label class="sh-auth-field" for="logistics-email">
                    <span class="sh-auth-label">Email address</span>
                    <input class="sh-auth-input" id="logistics-email" name="email" type="email" autocomplete="username" placeholder="Enter your registered email" value="{{ old('email') }}" required autofocus>
                </label>
                <label class="sh-auth-field" for="logistics-password">
                    <span class="sh-auth-label">Password</span>
                </label>
                <div class="sh-auth-password-wrap">
                    <input class="sh-auth-input" id="logistics-password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required>
                    <button type="button" id="logisticsPasswordToggle" class="sh-auth-show-password" aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                <label class="sh-auth-remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <button class="sh-auth-primary" type="submit" id="logisticsSignInSubmit">Sign in <span aria-hidden="true">→</span></button>
            </form>

            <div class="sh-auth-separator">or</div>
            <a class="sh-auth-google" href="{{ route('logistics.google.redirect') }}">
                <svg viewBox="0 0 48 48" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.25 5.48-4.76 7.18l7.73 6C44.41 38.03 46.98 31.88 46.98 24.55z"/><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.28-3.13.77-4.59l-7.98-6.19A23.91 23.91 0 0 0 0 24c0 3.87.92 7.52 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.92-2.13 15.89-5.8l-7.73-6c-2.14 1.44-4.89 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                Continue with Google
            </a>
            <p class="sh-auth-small-note">Google sign-in requires a configured Google OAuth client and an approved partner account.</p>
            <p class="sh-auth-bottom">New Logistics business? <a href="{{ route('logistics.register') }}">Apply as a Partner</a><br>
                <a href="{{ route('logistics.home') }}">← Back to Logistics homepage</a>
            </p>
        </div>
        <aside class="sh-auth-intro" aria-label="Logistics portal overview">
            <span class="eyebrow">ShopHop partner console</span>
            <h2>One place to keep your deliveries moving.</h2>
            <p>Manage the essential handoffs in your delivery network with a dedicated portal for logistics businesses and sorting centers.</p>
            <ul>
                <li><span class="step">01</span><span>Receive seller pickup requests and assign riders.</span></li>
                <li><span class="step">02</span><span>Scan incoming parcels and route them through sorting centers.</span></li>
                <li><span class="step">03</span><span>Assign final-mile riders and monitor delivery statuses.</span></li>
            </ul>
        </aside>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pass = document.getElementById('logistics-password');
    const toggle = document.getElementById('logisticsPasswordToggle');
    const form = document.getElementById('logisticsSignInForm');
    const submit = document.getElementById('logisticsSignInSubmit');
    toggle?.addEventListener('click', function () {
        if (!pass) return;
        const show = pass.type === 'password';
        pass.type = show ? 'text' : 'password';
        toggle.textContent = show ? 'Hide' : 'Show';
        toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        toggle.setAttribute('aria-pressed', String(show));
    });
    form?.addEventListener('submit', function () {
        if (!submit) return;
        submit.disabled = true;
        submit.textContent = 'Signing in…';
    });
});
</script>
@endpush
