@extends('logistics.layouts.public')

@section('title', 'Become a ShopHop Logistics Partner | ShopHop Logistics')

@push('styles')
<style>
    .lp-hero {position:relative;isolation:isolate;overflow:hidden;background:linear-gradient(115deg,#0F1B3D 0%,#12334a 63%,#146c64 115%);color:#fff;}
    .lp-hero::before {content:"";position:absolute;inset:-10% -20% auto auto;width:850px;height:750px;background:radial-gradient(ellipse,#18d0a82d,transparent 65%);z-index:-1;pointer-events:none;}
    .lp-hero-grid {min-height:460px;display:grid;grid-template-columns:1.15fr .85fr;align-items:center;gap:clamp(30px,5vw,100px);padding-block:51px;}
    .lp-eyebrow {display:inline-flex;align-items:center;gap:9px;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#98f1dd;}
    .lp-eyebrow::before {content:"";display:inline-block;width:22px;height:2px;background:#40dec0;border-radius:9px;}
    .lp-hero h1 {font-size:clamp(33px,3.65vw,50px);font-weight:800;letter-spacing:-.052em;line-height:1.13;color:#fff;margin:19px 0;max-width:600px;}
    .lp-hero h1 span {color:#5ee1c2;}
    .lp-hero p {font-size:clamp(12px,1.08vw,14px);max-width:650px;color:#c9dce5;line-height:1.75;margin:0 0 24px;}
    .lp-hero-actions {display:flex;gap:12px;flex-wrap:wrap;}
    .lp-hero-note {font-size:12px!important;color:#aacbd0!important;margin:24px 0 0!important;}
    .lp-board {background:linear-gradient(145deg,#ffffff1c,#ffffff08);border:1px solid #ffffff33;box-shadow:0 30px 70px #04182233;border-radius:20px;padding:clamp(18px,2.1vw,26px);backdrop-filter:blur(8px);}
    .lp-board-top {display:flex;align-items:center;justify-content:space-between;gap:12px;padding-bottom:20px;border-bottom:1px solid #ffffff25;}
    .lp-board-title {font-weight:700;font-size:14px;}
    .lp-board-kicker {display:block;font-size:12px;font-weight:400;color:#b4d3d9;margin-top:5px;}
    .lp-board-badge {border:1px solid #8bf8d355;background:#6ae2c11f;padding:7px 11px;color:#8ef6d3;border-radius:30px;font-size:11px;font-weight:700;white-space:nowrap;}
    .lp-board-timeline {display:grid;gap:15px;padding:20px 0;}
    .lp-board-row {display:grid;grid-template-columns:30px 1fr;gap:12px;align-items:center;color:#e7f5f8;font-size:13px;}
    .lp-board-number {height:30px;width:30px;border-radius:50%;display:grid;place-items:center;background:#ffffff22;color:#b2fae8;font-size:12px;font-weight:800;}
    .lp-board-foot {border-top:1px solid #ffffff25;padding-top:15px;display:flex;gap:12px;align-items:center;justify-content:space-between;font-size:12px;color:#b8d6dd;}
    .lp-board-foot strong {color:#94fae2;font-size:13px;text-align:right;}
    .lp-section {padding:53px 0;}
    .lp-section-alt {background:#f5f8f9;}
    .lp-section-kicker {font-size:12px;letter-spacing:.13em;text-transform:uppercase;color:var(--lp-forest);font-weight:800;margin:0 0 17px;}
    .lp-section h2 {font-size:clamp(25px,2.5vw,36px);letter-spacing:-.04em;line-height:1.23;font-weight:800;margin:0;max-width:820px;}
    .lp-section-intro {color:#526779;line-height:1.75;font-size:13px;max-width:680px;margin:18px 0 0;}
    .lp-benefits {display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px;margin-top:30px;}
    .lp-benefit {background:#fff;border:1px solid #e2ebec;border-radius:16px;padding:22px;min-width:0;box-shadow:0 8px 22px #0f244105;}
    .lp-benefit-icon {width:39px;height:39px;border-radius:13px;background:#e6f8f4;color:#08796e;display:grid;place-items:center;font-size:18px;margin-bottom:15px;}
    .lp-benefit h3 {font-size:14px;font-weight:700;margin:0 0 9px;line-height:1.4;}
    .lp-benefit p {font-size:13px;line-height:1.75;color:#627487;margin:0;}
    .lp-steps {display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px;margin-top:34px;}
    .lp-step-number {font-size:26px;color:#18A98E;font-weight:800;letter-spacing:-.06em;margin-bottom:15px;}
    .lp-step h3 {font-size:14px;font-weight:700;margin-bottom:10px;}
    .lp-step p {font-size:13px;line-height:1.8;color:#617387;}
    .lp-requirements {display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:clamp(36px,6vw,110px);}
    .lp-requirements-list {display:grid;gap:13px;}
    .lp-requirement {display:flex;align-items:flex-start;gap:14px;padding:19px 20px;border:1px solid #e2eaef;border-radius:15px;box-shadow:0 5px 16px #0f244106;font-size:13px;line-height:1.65;color:#3d5367;}
    .lp-check {flex:none;display:grid;place-items:center;width:25px;height:25px;border-radius:50%;color:white;background:#0b9584;font-weight:700;font-size:14px;}
    .lp-rider {background:#edf9f7;}
    .lp-rider-inner {display:flex;align-items:center;justify-content:space-between;gap:36px;}
    .lp-rider h2 {max-width:660px;}
    .lp-rider p:last-child {line-height:1.85;color:#506878;max-width:720px;margin-top:20px;}
    .lp-cta-section {padding:0 0 65px;background:#fff;}
    .lp-cta {text-align:center;background:linear-gradient(110deg,#102846,#0d5559);border-radius:28px;padding:52px 25px;color:#fff;}
    .lp-cta h2 {font-size:clamp(25px,2.3vw,34px);font-weight:800;color:#fff;margin:0 0 15px;line-height:1.3;}
    .lp-cta p {margin:0 auto 27px;max-width:610px;color:#d4e7e9;font-size:14px;line-height:1.8;}
    .lp-cta-actions {display:flex;justify-content:center;gap:12px;flex-wrap:wrap;}
    @media(max-width:980px){.lp-hero-grid {grid-template-columns:1fr;min-height:unset;}.lp-board {max-width:570px;}.lp-benefits {grid-template-columns:repeat(2,minmax(0,1fr));}.lp-steps {grid-template-columns:repeat(2,minmax(0,1fr));}.lp-requirements {grid-template-columns:1fr;}}
    @media(max-width:640px){.lp-hero-grid {padding-block:45px;}.lp-hero h1 {font-size:34px;}.lp-section {padding:53px 0;}.lp-benefits,.lp-steps {grid-template-columns:1fr;gap:16px;}.lp-step-number {margin-bottom:6px;}.lp-cta-section {padding-bottom:65px;}.lp-cta {padding:46px 21px;border-radius:20px;}.lp-rider-inner {align-items:flex-start;flex-direction:column;}.lp-button {width:auto;}}
</style>
@endpush

@section('content')
@if (session('status'))
    <div class="lp-wrap" role="status" style="margin-block:16px;padding:14px 18px;border:1px solid #9bdfd3;border-radius:14px;background:#eafbf6;color:#0b665d;font-size:13px;font-weight:600;line-height:1.65;">
        {{ session('status') }}
    </div>
@endif
<section class="lp-hero">
    <div class="lp-wrap lp-hero-grid">
        <div>
            <div class="lp-eyebrow">SHOPHOP LOGISTICS PARTNERS</div>
            <h1>Deliver more, <span>grow together.</span></h1>
            <p>Become a ShopHop Logistics &amp; Sorting Center partner. Manage parcel pickup, sorting, rider assignment, and delivery monitoring through your dedicated partner portal.</p>
            <div class="lp-hero-actions">
                <a class="lp-button lp-button-light" href="{{ route('logistics.register') }}">Apply as Logistics Partner &rarr;</a>
                <a class="lp-button lp-button-outline" href="{{ route('logistics.login') }}">Partner Log In</a>
            </div>
            <p class="lp-hero-note">For registered logistics businesses and sorting centers · Subject to admin approval</p>
        </div>
        <div class="lp-board" aria-label="Illustrative ShopHop logistics workflow">
            <div class="lp-board-top"><div><span class="lp-board-title">Your deliveries, connected</span><span class="lp-board-kicker">From seller pickup to buyer delivery</span></div><span class="lp-board-badge">SHOPHOP</span></div>
            <div class="lp-board-timeline">
                <div class="lp-board-row"><span class="lp-board-number">01</span><span>Seller prepares the parcel</span></div>
                <div class="lp-board-row"><span class="lp-board-number">02</span><span>Rider picks up the order</span></div>
                <div class="lp-board-row"><span class="lp-board-number">03</span><span>Sorting center receives and sorts</span></div>
                <div class="lp-board-row"><span class="lp-board-number">04</span><span>Assigned rider delivers to the buyer</span></div>
            </div>
            <div class="lp-board-foot"><span>Delivery coordination</span><strong>Powered by ShopHop</strong></div>
        </div>
    </div>
</section>

<section id="benefits" class="lp-section">
    <div class="lp-wrap">
        <p class="lp-section-kicker">Why partner with ShopHop?</p>
        <h2>Built for logistics businesses and sorting center operators.</h2>
        <p class="lp-section-intro">Manage your fleet and day-to-day delivery operations alongside ShopHop sellers, buyers, and riders.</p>
        <div class="lp-benefits">
            <article class="lp-benefit"><div class="lp-benefit-icon">↗</div><h3>Connected seller pickups</h3><p>Review pickup requests associated with seller orders and coordinate collection through your rider network.</p></article>
            <article class="lp-benefit"><div class="lp-benefit-icon">▤</div><h3>Sorting center operations</h3><p>Receive parcels, organize them by destination area, and monitor sorting center activity.</p></article>
            <article class="lp-benefit"><div class="lp-benefit-icon">⌖</div><h3>Area-based assignments</h3><p>Assign delivery riders according to their registered coverage areas and delivery destinations.</p></article>
            <article class="lp-benefit"><div class="lp-benefit-icon">✓</div><h3>Rider verification</h3><p>Review rider applications and manage eligible members of your fleet from the portal.</p></article>
            <article class="lp-benefit"><div class="lp-benefit-icon">◷</div><h3>Delivery monitoring</h3><p>Keep track of deliveries as parcels move between pickup, sorting, assignment, and completion.</p></article>
            <article class="lp-benefit"><div class="lp-benefit-icon">▥</div><h3>Reports and settlements</h3><p>View logistics reports and manage COD settlement records through your dashboard.</p></article>
        </div>
    </div>
</section>

<section id="process" class="lp-section lp-section-alt">
    <div class="lp-wrap">
        <p class="lp-section-kicker">How to become a partner</p>
        <h2>From application to your first delivery assignment.</h2>
        <div class="lp-steps">
            <div class="lp-step"><div class="lp-step-number">01</div><h3>Apply online</h3><p>Provide your business and authorized representative's information through our secure application form.</p></div>
            <div class="lp-step"><div class="lp-step-number">02</div><h3>Submit documents</h3><p>Complete email verification and upload the required identification and business permits.</p></div>
            <div class="lp-step"><div class="lp-step-number">03</div><h3>Wait for approval</h3><p>ShopHop administrators review your application and notify you through your registered email.</p></div>
            <div class="lp-step"><div class="lp-step-number">04</div><h3>Manage operations</h3><p>Once approved, sign in to manage your sorting centers, riders, parcels, and assignments.</p></div>
        </div>
    </div>
</section>

<section id="requirements" class="lp-section">
    <div class="lp-wrap lp-requirements">
        <div>
            <p class="lp-section-kicker">Before you apply</p>
            <h2>Prepare your business requirements.</h2>
            <p class="lp-section-intro">Having your documents ready makes registration faster. The application form accepts supported image or PDF files and includes email verification.</p>
            <div style="margin-top:24px"><a class="lp-button lp-button-primary" href="{{ route('logistics.register') }}">Start your application &rarr;</a></div>
        </div>
        <div class="lp-requirements-list">
            <div class="lp-requirement"><span class="lp-check">✓</span><span>Authorized representative's valid government ID</span></div>
            <div class="lp-requirement"><span class="lp-check">✓</span><span>Business permit and business registration information</span></div>
            <div class="lp-requirement"><span class="lp-check">✓</span><span>Business contact and complete address details</span></div>
            <div class="lp-requirement"><span class="lp-check">✓</span><span>Service coverage areas and representative's verified email</span></div>
        </div>
    </div>
</section>

<section class="lp-section lp-rider">
    <div class="lp-wrap lp-rider-inner">
        <div>
            <p class="lp-section-kicker">Applying as an individual rider?</p>
            <h2>Rider registration is through the ShopHop mobile app.</h2>
            <p>Our website is for logistics businesses and sorting centers. Individual riders apply in the ShopHop mobile app, select a logistics center, and await that center's approval.</p>
        </div>
        <div><a class="lp-button lp-button-ink" href="{{ route('home') }}">Visit ShopHop &rarr;</a></div>
    </div>
</section>

<section class="lp-cta-section">
    <div class="lp-wrap lp-cta">
        <h2>Ready to become a ShopHop Logistics Partner?</h2>
        <p>Apply for review by the ShopHop team. Already registered and approved? Sign in to your logistics dashboard.</p>
        <div class="lp-cta-actions"><a class="lp-button lp-button-light" href="{{ route('logistics.register') }}">Apply Now</a><a class="lp-button lp-button-outline" href="{{ route('logistics.login') }}">Already a partner? Log In</a></div>
    </div>
</section>
@endsection
