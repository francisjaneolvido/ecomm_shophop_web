@extends('logistics.layouts.public')

@section('title', 'ShopHop Logistics | Partner with us · Rider Registration Guide')

@php
    // Only a verified public HTTPS link can be presented as a direct app download.
    $candidate = trim((string) config('shophop_rider.app_url', ''));
    $scheme = strtolower((string) parse_url($candidate, PHP_URL_SCHEME));
    $appHost = strtolower((string) parse_url($candidate, PHP_URL_HOST));
    $appUrl = $scheme === 'https' && filter_var($candidate, FILTER_VALIDATE_URL)
        && $appHost !== '' && ! in_array($appHost, ['localhost', '127.0.0.1', '0.0.0.0'], true)
        && ! str_ends_with($appHost, '.localhost') ? $candidate : null;
    $guideUrl = route('logistics.home') . '#rider-guide';
    $qrUrl = $appUrl ?: $guideUrl;
    $hostName = strtolower((string) request()->getHost());
    $isLocalHost = $hostName === 'localhost' || $hostName === '127.0.0.1' || $hostName === '0.0.0.0' || str_ends_with($hostName, '.localhost');
@endphp

@push('styles')
<style>
    .lp-home {--ink:#10193C;--teal:#EDA3AE;--dark-teal:#10193C;--mint:#FCECEF;--line:#e0e8eb;--light:#FBF6EE;color:var(--ink)}
    .lp-home .lp-wrap {width:min(calc(100% - 48px),1320px)}
    .lp-home h1,.lp-home h2,.lp-home h3,.lp-home p {margin-top:0}
    .lp-home a {text-decoration:none}
    .lp-home section[id] {scroll-margin-top:108px}
    .lp-eyebrow {display:inline-flex;align-items:center;gap:10px;color:#10193C;font-size:10px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}
    .lp-eyebrow::before {content:"";width:20px;height:2px;background:#EDA3AE}
    .lp-h1 {font-size:clamp(34px,4vw,57px);line-height:1.09;letter-spacing:-.049em;font-weight:800;margin:16px 0;color:#fff}
    .lp-h1 span {color:#10193C}
    .lp-home h2 {font-size:clamp(24px,2.5vw,33px);line-height:1.22;letter-spacing:-.035em;font-weight:800}
    .lp-home h3 {font-size:15px;font-weight:700;line-height:1.4}
    .lp-copy {font-size:12.5px;line-height:1.8;color:#526879}
    .lp-section {padding:64px 0}
    .lp-section-alt {background:#FBF6EE}
    .lp-lead {max-width:660px;margin:13px 0 0}
    .lp-hero {background:linear-gradient(115deg,#10193C 0%,#10193C 57%,#26305A 100%);color:#fff;overflow:hidden}
    .lp-hero-grid {display:grid;grid-template-columns:1.04fr .96fr;gap:42px;align-items:center;min-height:452px;padding:50px 0}
    .lp-hero .lp-eyebrow {color:#10193C}
    .lp-hero-copy {font-size:13px;line-height:1.8;color:#FCECEF;max-width:620px;margin:0 0 24px}
    .lp-actions {display:flex;flex-wrap:wrap;gap:10px;align-items:center}
    .lp-hero .lp-button {border-radius:7px;min-height:44px}
    .lp-hero .lp-button-strong {background:#10193C;color:#fff;font-size:12px;padding:12px 22px;border:1px solid transparent}
    .lp-hero .lp-button-outline {border-color:rgba(255,255,255,.37)}
    .lp-hero .lp-note {font-size:10.5px;color:#10193C;margin:19px 0 0}
    .lp-hero-board {background:#ffffff10;border:1px solid #ffffff30;border-radius:11px;padding:24px;box-shadow:0 26px 50px #06162f30;backdrop-filter:blur(12px)}
    .lp-hero-board-header {display:flex;align-items:center;justify-content:space-between;gap:12px;padding-bottom:18px;border-bottom:1px solid #ffffff24}
    .lp-hero-board-header strong {font-size:16px;color:white}
    .lp-hero-board-header small {display:block;margin-top:5px;font-size:11px;color:#bbd5de}
    .lp-hero-chip {font-size:9px;color:#10193C;border:1px solid #EDA3AE44;padding:6px 9px;border-radius:4px;font-weight:800;letter-spacing:.1em}
    .lp-hero-path {display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:7px;margin-top:23px}
    .lp-hero-node {background:#ffffff11;border:1px solid #ffffff1c;border-radius:6px;padding:12px 9px;min-height:85px}
    .lp-hero-node b {display:block;color:#10193C;font-size:15px;margin-bottom:9px}
    .lp-hero-node span {font-size:10.5px;line-height:1.5;color:white;font-weight:600}
    .lp-hero-board-footer {display:flex;justify-content:space-between;align-items:center;padding-top:19px;margin-top:17px;border-top:1px solid #ffffff24;gap:12px;color:#10193C;font-size:11px}
    .lp-hero-board-footer b {color:#10193C;font-size:11px}
    .lp-role-grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:28px}
    .lp-role-card {border:1px solid var(--line);padding:26px;border-radius:8px;background:#fff;box-shadow:0 8px 24px #10193C08;display:flex;align-items:flex-start;gap:19px;min-height:190px}
    .lp-role-icon {width:45px;height:45px;flex:0 0 45px;background:var(--mint);display:grid;place-items:center;border-radius:8px;font-size:23px;color:var(--dark-teal)}
    .lp-role-card h3 {font-size:17px;margin-bottom:7px}
    .lp-role-card p {margin:0 0 12px;font-size:11.5px;line-height:1.7;color:#597080}
    .lp-link {display:inline-flex;align-items:center;gap:6px;color:#10193C;font-size:11px;font-weight:800}
    .lp-benefits {display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:28px}
    .lp-benefit {border:1px solid var(--line);border-radius:8px;padding:22px;background:white}
    .lp-benefit-icon {width:40px;height:40px;background:var(--mint);color:var(--dark-teal);font-size:21px;display:grid;place-items:center;border-radius:7px;margin-bottom:15px}
    .lp-benefit h3 {margin-bottom:7px}
    .lp-benefit p {color:#637688;line-height:1.65;font-size:11.5px;margin:0}
    .lp-process {display:grid;grid-template-columns:repeat(4,1fr);gap:13px;margin-top:26px}
    .lp-process > div {background:#fff;border:1px solid var(--line);border-radius:7px;padding:22px}
    .lp-process b {font-size:23px;color:#10193C;font-weight:800}
    .lp-process h3 {margin:12px 0 8px}
    .lp-process p {font-size:11.5px;line-height:1.75;color:#627487;margin:0}
    .lp-guide-layout {display:grid;grid-template-columns:minmax(0,1fr) minmax(340px,.92fr);gap:42px;align-items:center;margin-top:29px}
    .lp-guide-intro {max-width:650px}
    .lp-guide-intro > p {margin-top:12px}
    .lp-step-list {display:grid;gap:8px;margin-top:23px}
    .lp-step-pick {width:100%;border:1px solid var(--line);background:white;display:flex;gap:14px;align-items:center;text-align:left;padding:14px 15px;border-radius:7px;font:inherit;cursor:pointer;transition:border-color .16s,background .16s}
    .lp-step-pick:hover {border-color:#EDA3AE}
    .lp-step-pick[aria-selected="true"] {background:#FCECEF;border-color:#EDA3AE;box-shadow:0 0 0 1px #EDA3AE10}
    .lp-step-no {height:31px;width:31px;flex:none;display:grid;place-items:center;background:#FCECEF;color:#10193C;border-radius:5px;font-size:12px;font-weight:800}
    .lp-step-pick[aria-selected="true"] .lp-step-no {background:#EDA3AE;color:#10193C}
    .lp-step-pick b {font-size:12px;color:#11243c;display:block;margin-bottom:3px}
    .lp-step-pick small {font-size:10.5px;color:#607486;line-height:1.5}
    .lp-guide-next {display:flex;align-items:center;gap:9px;margin-top:15px;flex-wrap:wrap}
    .lp-guide-next button {border:1px solid #FCECEF;background:white;color:#10193C;border-radius:6px;padding:9px 13px;font-family:inherit;font-size:11px;font-weight:700;cursor:pointer}
    .lp-guide-next button:hover {border-color:#EDA3AE;color:#10193C}
    .lp-phone-stage {display:flex;justify-content:center;align-items:center;padding:26px;background:linear-gradient(125deg,#FCECEF,#FCECEF);border:1px solid #FCECEF;border-radius:10px;min-height:490px}
    .lp-phone {width:264px;min-height:470px;background:#FBF6EE;border:7px solid #162c3c;border-radius:35px;box-shadow:0 24px 48px #132f3d27;position:relative;overflow:hidden;color:#102441}
    .lp-phone::before {content:"";position:absolute;top:7px;left:50%;transform:translateX(-50%);z-index:4;width:72px;height:14px;border-radius:99px;background:#162c3c}
    .lp-phone-screen {padding:32px 13px 15px;height:456px;overflow:hidden;display:flex;flex-direction:column}
    .lp-phone-nav {display:flex;align-items:center;justify-content:space-between;font-size:12px;font-weight:800;margin-bottom:16px;gap:8px}
    .lp-phone-nav img {height:22px;width:22px;object-fit:contain}
    .lp-phone-small {font-weight:800;color:#10193C;font-size:8px;letter-spacing:.12em}
    .lp-phone-title {font-size:18px;letter-spacing:-.035em;font-weight:800;margin:5px 0 6px;line-height:1.15}
    .lp-phone-desc {font-size:8.5px;color:#637486;line-height:1.5;margin:0}
    .lp-phone-progress {border:1px solid #FCECEF;background:white;border-radius:12px;padding:11px 9px;margin:11px 0 13px}
    .lp-phone-progress-head {display:flex;align-items:center;justify-content:space-between;font-size:8px;font-weight:800;color:#72839a;margin-bottom:10px}
    .lp-phone-progress-head span:last-child {color:#10193C}
    .lp-phone-progress-dots {display:grid;grid-template-columns:repeat(4,1fr);gap:4px;text-align:center}
    .lp-phone-progress-dots span {font-size:7px;color:#637486}
    .lp-phone-progress-dots span b {margin:0 auto 4px;background:white;border:1px solid #FCECEF;display:grid;place-items:center;width:23px;height:23px;border-radius:50%;font-size:8px}
    .lp-phone-progress-dots span.is-on b {background:#26305A;color:#fff;border-color:#EDA3AE}
    .lp-phone-panel {background:white;border:1px solid rgba(16,25,60,0.150);border-radius:14px;padding:14px 12px;flex:1;overflow:hidden;display:flex;flex-direction:column;gap:8px}
    .lp-phone-panel h4 {font-size:12px;font-weight:800;margin:0 0 1px;color:#13243e}
    .lp-phone-panel > p {font-size:8.5px;color:#627487;line-height:1.5;margin:0 0 2px}
    .lp-preview-field {background:#FFFAF6;border:1px solid #e0e9ed;border-radius:7px;padding:7px 9px;min-height:33px}
    .lp-preview-field b {font-size:8px;color:#536877;display:block;margin-bottom:3px}
    .lp-preview-field span {font-size:8.5px;color:#92a0ab}
    .lp-preview-upload {border:1px dashed #EDA3AE;border-radius:6px;background:#FCECEF;padding:9px;text-align:center;font-size:8px;color:#10193C;font-weight:700}
    .lp-phone-foot {margin-top:auto;display:flex;gap:6px;padding-top:10px}
    .lp-phone-foot span {font-size:9px;border:1px solid #dee7e9;border-radius:7px;padding:10px;text-align:center;flex:1;font-weight:800}
    .lp-phone-foot span:last-child {background:#EDA3AE;color:#10193C;border-color:#EDA3AE;flex:2}
    [data-rider-panel] {display:none}
    [data-rider-panel].is-active {display:flex}
    .lp-preview-note {text-align:center;font-size:10px;color:#718293;margin-top:10px}

    .lp-video-stage { border:1px solid #FCECEF;background:linear-gradient(130deg,#FCECEF,#FBF6EE);padding:23px;border-radius:12px;display:flex;justify-content:center;align-items:center;min-width:0; }
    .lp-video-frame { position:relative;width:min(100%,285px);border-radius:33px;border:7px solid #152b3c;box-shadow:0 20px 45px #10193C2A;overflow:hidden;background:#0a2938; }
    .lp-video-frame video { display:block;width:100%;height:auto;aspect-ratio:540/960;object-fit:contain;background:#0a2938; }
    .lp-video-list {list-style:none;margin:18px 0 0;padding:0;display:grid;gap:10px}
    .lp-video-list li {border:1px solid #FCECEF;background:white;border-radius:7px;padding:13px 15px;display:flex;align-items:center;gap:13px;font-size:12px;color:#243b50;line-height:1.6}
    .lp-video-list b {height:27px;width:27px;flex:none;border-radius:7px;display:grid;place-items:center;background:#FCECEF;color:#10193C}
    .lp-video-desc {max-width:660px;font-size:13px;color:#617387;line-height:1.75;margin:12px 0 0}
    .lp-qr-wrap {display:grid;grid-template-columns:130px minmax(0,1fr);align-items:center;gap:22px;padding:19px;margin-top:26px;border:1px solid #FCECEF;border-radius:8px;background:white;max-width:655px}
    .lp-qr-code {height:130px;width:130px;border:1px solid #FCECEF;border-radius:5px;background:#fff;display:grid;place-items:center;padding:9px}
    .lp-qr-code svg {width:100%;height:100%;display:block}
    .lp-qr-caption h3 {font-size:13px;margin:0 0 7px}
    .lp-qr-caption p {font-size:10.5px;line-height:1.65;color:#637486;margin:0 0 7px}
    .lp-qr-caption a {font-size:11px;font-weight:800;color:#10193C;overflow-wrap:anywhere}
    .lp-qr-warning {display:block;margin-top:7px;color:#9b6704;font-size:10px;line-height:1.5}
    .lp-info-list {margin:18px 0 0;padding-left:19px;color:#54697a;font-size:11.5px;line-height:1.8}
    .lp-faq {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:24px}
    .lp-faq details {background:white;border:1px solid var(--line);border-radius:6px;padding:17px}
    .lp-faq summary {font-size:12px;font-weight:700;cursor:pointer}
    .lp-faq details p {margin:11px 0 0;color:#6B6B7B;line-height:1.7;font-size:11.5px}
    .lp-cta {background:#0f2a45;color:#fff;padding:37px;border-radius:9px;display:flex;align-items:center;justify-content:space-between;gap:25px}
    .lp-cta h2 {color:#fff;margin:0 0 9px;font-size:clamp(21px,2.4vw,30px)}
    .lp-cta p {color:#FCECEF;font-size:12px;line-height:1.7;margin:0}
    .lp-cta .lp-button {border-radius:6px;white-space:nowrap}
    .lp-cta-section {padding:15px 0 65px}
    @media(max-width:1080px){.lp-guide-layout {grid-template-columns:1fr .9fr;gap:23px}.lp-hero-grid{gap:25px}}
    @media(max-width:820px){.lp-hero-grid,.lp-guide-layout{grid-template-columns:1fr}.lp-hero-grid{min-height:auto;padding:49px 0}.lp-hero-board{max-width:600px}.lp-guide-layout{gap:27px}.lp-role-grid,.lp-benefits,.lp-faq{grid-template-columns:repeat(2,minmax(0,1fr))}.lp-process{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:560px){.lp-home .lp-wrap{width:min(calc(100% - 30px),1320px)}.lp-hero-grid{padding:40px 0}.lp-h1{font-size:36px}.lp-section{padding:43px 0}.lp-role-grid,.lp-benefits,.lp-faq,.lp-process{grid-template-columns:1fr}.lp-role-card{padding:20px;min-height:auto}.lp-hero-path{grid-template-columns:repeat(2,1fr)}.lp-phone-stage{padding:16px;min-height:0}.lp-qr-wrap{grid-template-columns:100px 1fr;padding:12px;gap:11px}.lp-qr-code{width:100px;height:100px}.lp-cta{padding:23px;align-items:flex-start;flex-direction:column}}
    @media(prefers-reduced-motion:reduce){.lp-home *{transition:none!important}}
</style>
@endpush

@section('content')
<div class="lp-home">
    @if (session('status'))
        <div class="lp-wrap" role="status" style="padding:14px 0;color:#10193C;font-weight:600">{{ session('status') }}</div>
    @endif

    <section class="lp-hero" id="top">
        <div class="lp-wrap lp-hero-grid">
            <div>
                <div class="lp-eyebrow">SHOPHOP LOGISTICS PARTNERS</div>
                <h1 class="lp-h1">Deliver smarter. <span>Grow together.</span></h1>
                <p class="lp-hero-copy">Manage seller pickups, sorting centers, rider assignments, and parcel delivery through the ShopHop logistics network. Two ways to get started—one platform connecting every handoff.</p>
                <div class="lp-actions">
                    <a class="lp-button lp-button-strong" href="{{ route('logistics.register') }}">Become a Logistics Partner →</a>
                    <a class="lp-button lp-button-outline" href="#rider-guide">Become a Rider · View Guide</a>
                </div>
                <p class="lp-note">Partner applications require review and approval. Individual riders register in ShopHop Mobile.</p>
            </div>
            <div class="lp-hero-board" aria-label="ShopHop parcel flow overview">
                <div class="lp-hero-board-header">
                    <div><strong>One connected delivery network</strong><small>From seller pickup to final delivery</small></div>
                    <span class="lp-hero-chip">SHOPHOP</span>
                </div>
                <div class="lp-hero-path">
                    <div class="lp-hero-node"><b>01</b><span>Seller pickup</span></div>
                    <div class="lp-hero-node"><b>02</b><span>Branch receipt</span></div>
                    <div class="lp-hero-node"><b>03</b><span>Hub / transport</span></div>
                    <div class="lp-hero-node"><b>04</b><span>Last-mile delivery</span></div>
                </div>
                <div class="lp-hero-board-footer"><span>Verified handoffs · Manifest and QR workflows</span><b>Powered by ShopHop</b></div>
            </div>
        </div>
    </section>

    <section class="lp-section" id="join">
        <div class="lp-wrap">
            <div class="lp-eyebrow">CHOOSE HOW TO JOIN</div>
            <h2 style="margin:14px 0 0">Business partner or individual rider?</h2>
            <p class="lp-copy lp-lead">Different registration paths, explained clearly. Choose the one that matches your role.</p>
            <div class="lp-role-grid">
                <article class="lp-role-card">
                    <div class="lp-role-icon" aria-hidden="true">▣</div>
                    <div>
                        <h3>Logistics Partner / Sorting Center</h3>
                        <p>For registered logistics businesses. Complete web application, verification, requirements, and ShopHop admin review.</p>
                        <a class="lp-link" href="{{ route('logistics.register') }}">Apply online →</a>
                    </div>
                </article>
                <article class="lp-role-card">
                    <div class="lp-role-icon" aria-hidden="true">♧</div>
                    <div>
                        <h3>Individual Rider</h3>
                        <p>Register in the ShopHop mobile app, select an approved Logistics / Sorting Center, upload rider documents, and verify your email.</p>
                        <a class="lp-link" href="#rider-guide">Follow the rider tutorial →</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="lp-section lp-section-alt" id="benefits">
        <div class="lp-wrap">
            <div class="lp-eyebrow">BUILT FOR THE LAST MILE AND BEYOND</div>
            <h2 style="margin:14px 0 0">A clearer way to manage logistics.</h2>
            <p class="lp-copy lp-lead">The ShopHop partner portal supports real operational handoffs—not just a map of possible routes.</p>
            <div class="lp-benefits">
                <article class="lp-benefit"><div class="lp-benefit-icon">↗</div><h3>Pickup coordination</h3><p>Receive seller pickup requests and assign riders with eligible service-area coverage.</p></article>
                <article class="lp-benefit"><div class="lp-benefit-icon">▤</div><h3>Branch &amp; hub operations</h3><p>Scan and receive parcels, sort outbound manifests, and verify custody transfers.</p></article>
                <article class="lp-benefit"><div class="lp-benefit-icon">⌖</div><h3>Area-based riders</h3><p>Review rider applications and manage barangay-level rider assignment and availability.</p></article>
                <article class="lp-benefit"><div class="lp-benefit-icon">⇄</div><h3>Inter-island transfers</h3><p>Coordinate configured Road/Sea/Air legs with manifest status and receiving checks.</p></article>
                <article class="lp-benefit"><div class="lp-benefit-icon">✓</div><h3>Parcel tracking</h3><p>Follow pickup, sorting, in-transit, and last-mile delivery activity.</p></article>
                <article class="lp-benefit"><div class="lp-benefit-icon">▥</div><h3>COD &amp; reports</h3><p>Monitor settlement records and reporting in your authorized logistics dashboard.</p></article>
            </div>
        </div>
    </section>

    <section class="lp-section" id="process">
        <div class="lp-wrap">
            <div class="lp-eyebrow">FOR LOGISTICS BUSINESSES</div>
            <h2 style="margin:14px 0 0">Four steps to partner onboarding.</h2>
            <p class="lp-copy lp-lead">This is the web application flow for logistics companies—not individual riders.</p>
            <div class="lp-process">
                <div><b>01</b><h3>Apply online</h3><p>Enter your business and authorized representative information in the partner form.</p></div>
                <div><b>02</b><h3>Verify &amp; submit</h3><p>Complete email verification and upload required business and identification documents.</p></div>
                <div><b>03</b><h3>Application review</h3><p>ShopHop administrators review the request and update the application status.</p></div>
                <div><b>04</b><h3>Manage your network</h3><p>Once approved, log in to configure your branches, riders, and parcel operations.</p></div>
            </div>
            <div class="lp-actions" style="margin-top:24px">
                <a class="lp-button lp-button-primary" style="border-radius:6px" href="{{ route('logistics.register') }}">Start Partner Registration →</a>
                <a class="lp-link" href="{{ route('logistics.login') }}">Already approved? Sign in →</a>
            </div>
        </div>
    </section>

    <section class="lp-section lp-section-alt" id="rider-guide">
        <div class="lp-wrap">
            <div class="lp-eyebrow">SHOPHOP MOBILE · RIDER REGISTRATION</div>
            <h2 style="margin:14px 0 0">Watch the Rider registration tutorial.</h2>
            <p class="lp-video-desc">The tutorial plays automatically (muted), follows the current four-step Flutter Rider registration flow, and includes the new Logistics Partner card selection. Use the built-in video controls to pause, replay, or seek.</p>
            <div class="lp-guide-layout">
                <div class="lp-guide-intro">
                    <div class="lp-eyebrow" style="color:#10193C;font-size:10px">WHAT THE VIDEO COVERS</div>
                    <ol class="lp-video-list">
                        <li><b>01</b><span><strong>Personal details</strong><br>Name, birthday, email, and contact number</span></li>
                        <li><b>02</b><span><strong>Your address</strong><br>Province, city/municipality, barangay and street</span></li>
                        <li><b>03</b><span><strong>Choose a Logistics Partner</strong><br>Search partner cards with company logos, location and services; upload vehicle documents</span></li>
                        <li><b>04</b><span><strong>Account security</strong><br>Password, terms, submit, then verify your email</span></li>
                    </ol>
                    <ul class="lp-info-list">
                        <li>Your application is sent to the approved partner you select in the app.</li>
                        <li>Logistics companies manage public logos and Rider availability in their Web dashboard.</li>
                        <li>After email verification, partner approval is still required.</li>
                    </ul>
                </div>
                <div>
                    <div class="lp-video-stage">
                        <div class="lp-video-frame">
                            <video autoplay muted loop playsinline controls preload="metadata" poster="{{ asset('images/rider-registration-video-poster-navy-pink.png') }}" aria-label="ShopHop Rider registration video tutorial">
                                <source src="{{ asset('videos/rider-registration-tutorial-navy-pink.mp4') }}" type="video/mp4">
                                Your browser does not support the tutorial video.
                            </video>
                        </div>
                    </div>
                    <p class="lp-preview-note">This is a video walkthrough recreated from the current Flutter form structure, not a screen recording or live account form. It contains no real applicant data.</p>
                </div>
            </div>
            <div class="lp-qr-wrap">
                <div class="lp-qr-code" data-rider-qr data-qr-url="{{ $qrUrl }}" aria-label="QR code for {{ $appUrl ? 'ShopHop Rider app link' : 'ShopHop Rider registration guide' }}"><span style="font-size:10px;color:#617487;text-align:center">QR loading...</span></div>
                <div class="lp-qr-caption">
                    <h3>{{ $appUrl ? 'Scan to access the ShopHop Rider app' : 'Scan to open this Rider Guide' }}</h3>
                    <p>{{ $appUrl ? 'Your configured official app distribution page opens when you scan this code.' : 'No verified public APK / Play Store link is configured yet. This QR leads to the Rider Guide—not an app download.' }}</p>
                    <a href="{{ $qrUrl }}" @if ($appUrl) target="_blank" rel="noopener noreferrer" @endif>{{ $appUrl ? 'Open app link →' : 'Open Rider Guide →' }}</a>
                    @if ($isLocalHost && ! $appUrl)
                        <span class="lp-qr-warning">You are running on a local hostname. A separate phone cannot open this QR until the website is served at a reachable LAN or public HTTPS address.</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="lp-section" id="faq">
        <div class="lp-wrap">
            <div class="lp-eyebrow">FREQUENTLY ASKED QUESTIONS</div>
            <h2 style="margin:14px 0 0">Before you apply.</h2>
            <div class="lp-faq">
                <details><summary>Can a rider register on the logistics website?</summary><p>Riders register through ShopHop Mobile. The web application is for logistics businesses and sorting-center operators.</p></details>
                <details><summary>Who approves a rider application?</summary><p>The Logistics / Sorting Center selected during registration reviews the rider's application, after required email verification.</p></details>
                <details><summary>What files do riders upload?</summary><p>The Flutter registration form requires vehicle OR/CR and a valid ID or driver's license, plus vehicle and plate details.</p></details>
                <details><summary>Why does my QR open a website, not an APK?</summary><p>No verified public app download destination has been configured. When an official HTTPS app link is available, the site can show a direct installation QR.</p></details>
            </div>
        </div>
    </section>

    <section class="lp-cta-section">
        <div class="lp-wrap lp-cta">
            <div><h2>Ready to join ShopHop Logistics?</h2><p>Businesses apply online. Individual riders can follow the mobile registration guide above.</p></div>
            <div class="lp-actions"><a class="lp-button lp-button-light" href="{{ route('logistics.register') }}">Apply as Partner →</a><a class="lp-button lp-button-outline" href="#rider-guide">Rider Guide</a></div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/shophop-rider-qr.js') }}?v=20261112-navy-pink-2"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qrTarget = document.querySelector('[data-rider-qr]');
    if (qrTarget && window.ShopHopRiderQR) {
        try {window.ShopHopRiderQR.render(qrTarget, qrTarget.dataset.qrUrl);}
        catch (error) {qrTarget.textContent = 'Unable to generate QR. Use the link beside it.';}
    }
});
</script>
@endpush
