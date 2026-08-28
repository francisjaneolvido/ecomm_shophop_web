{{-- Path: resources/views/partials/loading-screen.blade.php --}}

{{--
    ========================================
    SHOPHOP LOADING SCREEN
    ========================================
    Include once, site-wide, right after the opening <body> tag in
    resources/views/layouts/app.blade.php:

        @include('partials.loading-screen')

    - Hides itself on the window `load` event, with a small minimum
      visible time so it never just flashes on fast connections.
    - Respects prefers-reduced-motion (mascot fades instead of hopping).
    - Exposes window.ShopHopLoader.show() / .hide() for manual control,
      e.g. on Livewire/Inertia route transitions.

    Asset: expects public/images/shophop-hop.png (the mascot artwork,
    transparent background). Swap the asset() path below if your build
    keeps images somewhere else.
========================================= --}}

<noscript>
    <style>#shophop-loader { display: none !important; }</style>
</noscript>

<div
    id="shophop-loader"
    role="status"
    aria-live="polite"
    aria-label="Loading ShopHop"
>

    {{-- ambient glow field --}}
    <div class="sh-loader__field" aria-hidden="true"></div>

    {{-- hopping mascot + ground track --}}
    <div class="sh-loader__stage">

        <div class="sh-loader__track" aria-hidden="true"></div>
        <div class="sh-loader__shadow" aria-hidden="true"></div>
        <div class="sh-loader__impact" aria-hidden="true"></div>

        <img
            src="{{ asset('images/logo.png') }}"
            alt=""
            aria-hidden="true"
            class="sh-loader__mascot"
        >

    </div>

    {{-- wordmark --}}
    <div class="sh-loader__word" aria-hidden="true">
        <span class="sh-loader__word-part sh-loader__word-part--shop">
            <span style="--i:0">S</span><span style="--i:1">h</span><span style="--i:2">o</span><span style="--i:3">p</span>
        </span><span class="sh-loader__word-part sh-loader__word-part--hop">
            <span style="--i:4">H</span><span style="--i:5">o</span><span style="--i:6">p</span>
        </span>
    </div>

    <p class="sh-loader__tagline" aria-hidden="true">Hop In. Shop More.</p>

    {{-- progress rail + status line --}}
    <div class="sh-loader__bar" aria-hidden="true"><span></span></div>
    <p class="sh-loader__status" aria-hidden="true"></p>

</div>


<style>

    #shophop-loader {
        --sh-navy: #0B1B33;
        --sh-navy-light: #16294B;
        --sh-navy-deep: #071322;
        --sh-teal: #12938D;
        --sh-teal-bright: #2FD9C8;
        --sh-teal-dark: #0A5F5A;
        --sh-mist: #C9DCE6;

        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: radial-gradient(ellipse 120% 70% at 50% 28%, var(--sh-navy-light), var(--sh-navy) 55%, var(--sh-navy-deep) 100%);
        transition: opacity .5s ease, visibility .5s ease;
    }

    #shophop-loader.sh-is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    /* ambient glow blobs */
    .sh-loader__field::before,
    .sh-loader__field::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        filter: blur(60px);
        opacity: .35;
        animation: sh-drift 6s ease-in-out infinite;
    }

    .sh-loader__field::before {
        width: 360px;
        height: 360px;
        top: -120px;
        left: -80px;
        background: var(--sh-teal-dark);
    }

    .sh-loader__field::after {
        width: 300px;
        height: 300px;
        bottom: -140px;
        right: -60px;
        background: var(--sh-teal);
        animation-delay: -3s;
    }

    @keyframes sh-drift {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50%      { transform: translate(20px, -16px) scale(1.08); }
    }

    /* ============ stage ============ */

    .sh-loader__stage {
        --amp-top: -50px;
        --amp-mid: -40px;
        --amp-bottom: 50px;
        --amp-bottom-sq: 54px;

        position: relative;
        width: 300px;
        height: 190px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }

    .sh-loader__track {
        position: absolute;
        bottom: 40px;
        left: 8%;
        right: 8%;
        height: 3px;
        border-radius: 999px;
        background-image: repeating-linear-gradient(
            90deg,
            var(--sh-teal) 0 14px,
            transparent 14px 26px
        );
        background-size: 40px 3px;
        opacity: .55;
        animation: sh-track-scroll .55s linear infinite;
    }

    @keyframes sh-track-scroll {
        to { background-position-x: -40px; }
    }

    .sh-loader__shadow {
        position: absolute;
        bottom: 40px;
        width: 92px;
        height: 16px;
        border-radius: 50%;
        background: var(--sh-teal-dark);
        animation: sh-shadow 1.2s cubic-bezier(.42, 0, .58, 1) infinite;
    }

    @keyframes sh-shadow {
        0%   { filter: blur(4px);   opacity: .4;  transform: scale(.5, .5); }
        45%  { filter: blur(1.5px); opacity: .85; transform: scale(1, 1);   }
        55%  { filter: blur(1.5px); opacity: .85; transform: scale(1, 1);   }
        100% { filter: blur(4px);   opacity: .4;  transform: scale(.5, .5); }
    }

    .sh-loader__impact {
        position: absolute;
        bottom: 46px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid var(--sh-teal-bright);
        opacity: 0;
        animation: sh-impact 1.2s cubic-bezier(.42, 0, .58, 1) infinite;
    }

    @keyframes sh-impact {
        0%, 40% { opacity: 0;   transform: scale(.4);  }
        47%     { opacity: .5;  transform: scale(.75); }
        55%     { opacity: 0;   transform: scale(1.5); }
        100%    { opacity: 0;   transform: scale(1.5); }
    }

    .sh-loader__mascot {
        position: absolute;
        bottom: 50px;
        width: 128px;
        height: auto;
        transform-origin: 50% 85%;
        filter: drop-shadow(0 6px 16px rgba(47, 217, 200, .35));
        animation: sh-hop 1.2s cubic-bezier(.42, 0, .58, 1) infinite;
    }

    @keyframes sh-hop {
        0%   { transform: translateY(var(--amp-top))      rotate(-5deg); }
        15%  { transform: translateY(var(--amp-mid))      rotate(-3deg); }
        45%  { transform: translateY(var(--amp-bottom))   rotate(2deg);  }
        50%  { transform: translateY(var(--amp-bottom-sq)) rotate(3deg) scaleY(.94); }
        55%  { transform: translateY(var(--amp-bottom))   rotate(2deg) scaleY(.97);  }
        85%  { transform: translateY(var(--amp-mid))      rotate(-3deg); }
        95%  { transform: translateY(var(--amp-top))      rotate(-5deg); }
        100% { transform: translateY(var(--amp-top))      rotate(-5deg); }
    }

    /* ============ wordmark ============ */

    .sh-loader__word {
        display: flex;
        margin-top: 6px;
        font-family: 'Poppins', sans-serif;
        font-weight: 800;
        font-size: 2.25rem;
        letter-spacing: -.02em;
    }

    .sh-loader__word-part {
        display: flex;
    }

    .sh-loader__word-part--shop { color: #FFFFFF; }
    .sh-loader__word-part--hop  { color: var(--sh-teal-bright); }

    .sh-loader__word span {
        opacity: 0;
        transform: translateY(14px);
        animation: sh-letter-in .55s cubic-bezier(.16, 1, .3, 1) forwards;
        animation-delay: calc(.25s + var(--i) * .045s);
    }

    @keyframes sh-letter-in {
        to { opacity: 1; transform: translateY(0); }
    }

    .sh-loader__tagline {
        margin-top: .5rem;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: .7rem;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--sh-mist);
        opacity: 0;
        animation: sh-fade-in .6s ease forwards;
        animation-delay: 1s;
    }

    /* ============ progress + status ============ */

    .sh-loader__bar {
        width: 170px;
        height: 3px;
        margin-top: 2rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        overflow: hidden;
        opacity: 0;
        animation: sh-fade-in .6s ease forwards;
        animation-delay: 1.15s;
    }

    .sh-loader__bar span {
        display: block;
        height: 100%;
        width: 40%;
        border-radius: 999px;
        background: linear-gradient(90deg, transparent, var(--sh-teal-bright), var(--sh-teal), transparent);
        animation: sh-bar-sweep 1.3s ease-in-out infinite;
    }

    @keyframes sh-bar-sweep {
        0%   { transform: translateX(-120%); }
        100% { transform: translateX(320%);  }
    }

    .sh-loader__status {
        margin-top: .85rem;
        font-family: 'Poppins', sans-serif;
        font-size: .75rem;
        color: rgba(255, 255, 255, .55);
        min-height: 1em;
        opacity: 0;
        transition: opacity .2s ease;
        animation: sh-fade-in .6s ease forwards;
        animation-delay: 1.3s;
    }

    @keyframes sh-fade-in {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0);   }
    }

    /* ============ small screens ============ */

    @media (max-width: 420px) {
        .sh-loader__stage {
            --amp-top: -38px;
            --amp-mid: -30px;
            --amp-bottom: 38px;
            --amp-bottom-sq: 41px;
            width: 240px;
            height: 160px;
        }

        .sh-loader__mascot { width: 100px; bottom: 40px; }
        .sh-loader__shadow,
        .sh-loader__impact { bottom: 32px; }
        .sh-loader__track  { bottom: 32px; }
        .sh-loader__word   { font-size: 1.75rem; }
    }

    /* ============ reduced motion ============ */

    @media (prefers-reduced-motion: reduce) {
        .sh-loader__mascot {
            animation: sh-pulse 1.6s ease-in-out infinite;
        }

        .sh-loader__shadow,
        .sh-loader__impact,
        .sh-loader__track,
        .sh-loader__field::before,
        .sh-loader__field::after {
            animation: none;
        }

        .sh-loader__shadow { opacity: .6; }

        @keyframes sh-pulse {
            0%, 100% { opacity: .75; transform: translateY(0); }
            50%      { opacity: 1;   transform: translateY(-6px); }
        }
    }

</style>


<script>
    (function () {
        var loader = document.getElementById('shophop-loader');
        if (!loader) return;

        var statusEl = loader.querySelector('.sh-loader__status');
        var lines = [
            'Naghahanda ng magagandang deals\u2026',
            'Sinasagot ang mga hop request\u2026',
            'Halos hop na tayo\u2026'
        ];
        var lineIndex = 0;
        if (statusEl) statusEl.textContent = lines[0];

        var statusTimer = setInterval(function () {
            if (!statusEl) return;
            lineIndex = (lineIndex + 1) % lines.length;
            statusEl.style.opacity = 0;
            setTimeout(function () {
                statusEl.textContent = lines[lineIndex];
                statusEl.style.opacity = 1;
            }, 200);
        }, 2200);

        var MIN_VISIBLE_MS = 900;
        var shownAt = Date.now();
        var isHidden = false;

        function hideNow() {
            if (isHidden) return;
            isHidden = true;
            clearInterval(statusTimer);
            loader.classList.add('sh-is-hidden');
            loader.setAttribute('aria-hidden', 'true');
            setTimeout(function () {
                loader.style.display = 'none';
            }, 550);
            document.dispatchEvent(new CustomEvent('shophop:loaded'));
        }

        function hide() {
            var elapsed = Date.now() - shownAt;
            setTimeout(hideNow, Math.max(0, MIN_VISIBLE_MS - elapsed));
        }

        function show() {
            isHidden = false;
            shownAt = Date.now();
            loader.style.display = 'flex';
            loader.classList.remove('sh-is-hidden');
            loader.removeAttribute('aria-hidden');
        }

        if (document.readyState === 'complete') {
            hide();
        } else {
            window.addEventListener('load', hide);
        }

        window.ShopHopLoader = { show: show, hide: hide };
    })();
</script>
