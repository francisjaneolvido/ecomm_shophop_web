{{-- Resolve the saved theme before the first paint so public and role layouts never flash light. --}}
<meta name="theme-color" content="#ffffff" data-theme-color>

<script>
    (function () {
        const themes = ['light', 'dark', 'oled'];
        const colors = { light: '#ffffff', dark: '#0b1220', oled: '#000000' };
        let transitionReleaseFrame = null;

        function savedTheme() {
            try {
                const theme = localStorage.getItem('shophop-theme');
                return themes.includes(theme) ? theme : 'light';
            } catch (error) {
                // Privacy modes can deny storage; light remains the safe deterministic fallback.
                return 'light';
            }
        }

        function updateControls(theme) {
            const nextTheme = themes[(themes.indexOf(theme) + 1) % themes.length];
            const names = { light: 'Light', dark: 'Dark', oled: 'OLED' };

            document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
                button.dataset.themeCurrent = theme;
                button.setAttribute('aria-label', names[theme] + ' theme. Switch to ' + names[nextTheme] + ' theme');
                button.setAttribute('title', 'Theme: ' + names[theme] + ' (switch to ' + names[nextTheme] + ')');

                const label = button.querySelector('[data-theme-label]');
                if (label) {
                    label.textContent = names[theme];
                }
            });
        }

        function applyTheme(theme, persist) {
            const selected = themes.includes(theme) ? theme : 'light';
            const root = document.documentElement;

            if (persist) {
                // Switch semantic tokens atomically instead of animating every affected utility.
                if (transitionReleaseFrame) {
                    window.cancelAnimationFrame(transitionReleaseFrame);
                }

                root.dataset.themeSwitching = 'true';
            }

            root.dataset.theme = selected;
            root.style.colorScheme = selected === 'light' ? 'light' : 'dark';

            const themeColor = document.querySelector('[data-theme-color]');
            if (themeColor) {
                themeColor.setAttribute('content', colors[selected]);
            }

            if (persist) {
                try {
                    localStorage.setItem('shophop-theme', selected);
                } catch (error) {
                    // The active theme still works when storage is unavailable.
                }
            }

            updateControls(selected);
            document.dispatchEvent(new CustomEvent('shophop:theme-change', { detail: { theme: selected } }));

            if (persist) {
                transitionReleaseFrame = window.requestAnimationFrame(function () {
                    root.removeAttribute('data-theme-switching');
                    transitionReleaseFrame = null;
                });
            }
        }

        // Layouts share this small API so every repeated toggle applies identical state and accessibility copy.
        window.ShopHopTheme = {
            apply: applyTheme,
            current: function () {
                return document.documentElement.dataset.theme || 'light';
            },
        };

        applyTheme(savedTheme(), false);

        document.addEventListener('DOMContentLoaded', function () {
            updateControls(window.ShopHopTheme.current());
        });

        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-theme-toggle]');
            if (!button) {
                return;
            }

            const current = window.ShopHopTheme.current();
            applyTheme(themes[(themes.indexOf(current) + 1) % themes.length], true);
        });

        // Keep a theme choice consistent when a shopper has multiple ShopHop tabs open.
        window.addEventListener('storage', function (event) {
            if (event.key === 'shophop-theme') {
                applyTheme(event.newValue, false);
            }
        });
    })();
</script>

<style>
    :root {
        --sh-page: #ffffff;
        --sh-surface: #ffffff;
        --sh-surface-raised: #f3f5f7;
        --sh-surface-soft: #f8fafb;
        --sh-text: #0f1b3d;
        --sh-muted: #59667a;
        --sh-faint: #748095;
        --sh-border: #e2e6ea;
        --sh-input: #f3f5f7;
        --sh-overlay: rgba(15, 27, 61, .5);
        --sh-shadow: rgba(15, 27, 61, .12);
        --sh-brand: #21c3a6;
        --sh-brand-strong: #18a98e;
        --sh-brand-soft: #d4f5ee;
    }

    html[data-theme="dark"] {
        --sh-page: #0b1220;
        --sh-surface: #111c2d;
        --sh-surface-raised: #172438;
        --sh-surface-soft: #0f1928;
        --sh-text: #eef4f7;
        --sh-muted: #adbac8;
        --sh-faint: #8998aa;
        --sh-border: #2a394d;
        --sh-input: #101a2a;
        --sh-overlay: rgba(0, 0, 0, .72);
        --sh-shadow: rgba(0, 0, 0, .32);
        --sh-brand-soft: #123a37;
    }

    html[data-theme="oled"] {
        --sh-page: #000000;
        --sh-surface: #080b0f;
        --sh-surface-raised: #11161c;
        --sh-surface-soft: #050709;
        --sh-text: #f4f8fa;
        --sh-muted: #b5c0ca;
        --sh-faint: #8e9aa6;
        --sh-border: #252c33;
        --sh-input: #090d12;
        --sh-overlay: rgba(0, 0, 0, .84);
        --sh-shadow: rgba(0, 0, 0, .6);
        --sh-brand-soft: #0d302d;
    }

    body {
        background: var(--sh-page) !important;
        color: var(--sh-text);
        transition: background-color .18s ease, color .18s ease;
    }

    /* Theme changes should not create a page-wide paint queue from utility transitions. */
    html[data-theme-switching],
    html[data-theme-switching] *,
    html[data-theme-switching] *::before,
    html[data-theme-switching] *::after {
        transition: none !important;
    }

    .theme-toggle {
        display: inline-flex;
        min-width: 2.75rem;
        height: 2.75rem;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        padding: 0 .65rem;
        border: 1px solid var(--sh-border);
        border-radius: 999px;
        background: var(--sh-surface-raised);
        color: var(--sh-text);
        font-size: .6875rem;
        font-weight: 700;
        line-height: 1;
        transition: border-color .18s ease, background-color .18s ease, color .18s ease, transform .18s ease;
    }

    .theme-toggle:hover {
        border-color: var(--sh-brand);
        color: var(--sh-brand-strong);
    }

    .theme-toggle:active {
        transform: translateY(1px);
    }

    .theme-toggle:focus-visible,
    :where(a, button, input, select, textarea):focus-visible {
        outline: 3px solid color-mix(in srgb, var(--sh-brand) 45%, transparent);
        outline-offset: 2px;
    }

    .theme-toggle__icon {
        display: none;
        width: 1rem;
        height: 1rem;
    }

    .theme-toggle[data-theme-current="light"] .theme-toggle__icon--light,
    .theme-toggle[data-theme-current="dark"] .theme-toggle__icon--dark,
    .theme-toggle[data-theme-current="oled"] .theme-toggle__icon--oled {
        display: block;
    }

    /* Map legacy light utilities to semantic Dark/OLED surfaces without rewriting every route. */
    html:not([data-theme="light"]) .bg-white,
    html:not([data-theme="light"]) .bg-white\/95,
    html:not([data-theme="light"]) .bg-white\/90,
    html:not([data-theme="light"]) .bg-white\/80 {
        background-color: var(--sh-surface) !important;
    }

    html:not([data-theme="light"]) .bg-gray-bg,
    html:not([data-theme="light"]) .bg-gray-50,
    html:not([data-theme="light"]) .bg-gray-bg\/75,
    html:not([data-theme="light"]) .bg-gray-bg\/40,
    html:not([data-theme="light"]) .bg-gray-bg\/80,
    html:not([data-theme="light"]) .bg-gray-bg\/65,
    html:not([data-theme="light"]) .bg-gray-bg\/55,
    html:not([data-theme="light"]) .bg-slate-50,
    html:not([data-theme="light"]) .bg-\[\#FBFCFD\] {
        background-color: var(--sh-page) !important;
    }

    html:not([data-theme="light"]) .bg-slate-50\/50,
    html:not([data-theme="light"]) .bg-slate-50\/60,
    html:not([data-theme="light"]) .bg-slate-100 {
        background-color: var(--sh-surface-raised) !important;
    }

    html:not([data-theme="light"]) .bg-\[\#EAF9F5\],
    html:not([data-theme="light"]) .bg-\[\#FFF8F7\],
    html:not([data-theme="light"]) .bg-\[\#ebeef0\] {
        background-color: var(--sh-surface-raised) !important;
    }

    html:not([data-theme="light"]) .bg-teal-light,
    html:not([data-theme="light"]) .bg-teal-light\/60,
    html:not([data-theme="light"]) .bg-teal-light\/25,
    html:not([data-theme="light"]) .bg-teal\/10,
    html:not([data-theme="light"]) .bg-teal\/15 {
        background-color: var(--sh-brand-soft) !important;
    }

    html:not([data-theme="light"]) .text-navy {
        color: var(--sh-text) !important;
    }

    html:not([data-theme="light"]) .text-navy\/80,
    html:not([data-theme="light"]) .text-navy\/75,
    html:not([data-theme="light"]) .text-navy\/65,
    html:not([data-theme="light"]) .text-navy\/70,
    html:not([data-theme="light"]) .text-navy\/60,
    html:not([data-theme="light"]) .text-navy\/55,
    html:not([data-theme="light"]) .text-navy\/50,
    html:not([data-theme="light"]) .text-navy\/45 {
        color: var(--sh-muted) !important;
    }

    html:not([data-theme="light"]) .text-navy\/40,
    html:not([data-theme="light"]) .text-navy\/35,
    html:not([data-theme="light"]) .text-navy\/30,
    html:not([data-theme="light"]) .text-navy\/25,
    html:not([data-theme="light"]) .text-navy\/20 {
        color: var(--sh-faint) !important;
    }

    html:not([data-theme="light"]) .text-slate-700,
    html:not([data-theme="light"]) .text-slate-600 {
        color: var(--sh-muted) !important;
    }

    html:not([data-theme="light"]) .text-slate-500,
    html:not([data-theme="light"]) .text-slate-400,
    html:not([data-theme="light"]) .text-slate-300 {
        color: var(--sh-faint) !important;
    }

    html:not([data-theme="light"]) .border-gray-border,
    html:not([data-theme="light"]) .border-gray-border\/70,
    html:not([data-theme="light"]) .divide-gray-border > :not(:last-child) {
        border-color: var(--sh-border) !important;
    }

    html:not([data-theme="light"]) .border-slate-100,
    html:not([data-theme="light"]) .border-slate-200 {
        border-color: var(--sh-border) !important;
    }

    html:not([data-theme="light"]) :where(input, select, textarea) {
        background-color: var(--sh-input) !important;
        border-color: var(--sh-border) !important;
        color: var(--sh-text) !important;
    }

    html:not([data-theme="light"]) :where(input, textarea)::placeholder {
        color: var(--sh-faint) !important;
        opacity: 1;
    }

    html:not([data-theme="light"]) .shadow-soft,
    html:not([data-theme="light"]) .shadow-panel,
    html:not([data-theme="light"]) .shadow-lg,
    html:not([data-theme="light"]) .shadow-xl {
        --tw-shadow-color: var(--sh-shadow) !important;
    }

    html:not([data-theme="light"]) .hover\:bg-white:hover,
    html:not([data-theme="light"]) .hover\:bg-gray-bg:hover,
    html:not([data-theme="light"]) .hover\:bg-slate-50:hover,
    html:not([data-theme="light"]) .hover\:bg-slate-100:hover {
        background-color: var(--sh-surface-raised) !important;
    }

    html:not([data-theme="light"]) .hover\:text-navy:hover {
        color: var(--sh-text) !important;
    }

    html:not([data-theme="light"]) .ring-white {
        --tw-ring-color: var(--sh-surface) !important;
    }

    /* Image-card captions intentionally retain the light overlay for legibility over photography. */
    html:not([data-theme="light"]) .theme-image-card .text-navy {
        color: #0f1b3d !important;
    }

    html:not([data-theme="light"]) .theme-image-card .bg-white {
        background-color: rgba(255, 255, 255, .94) !important;
    }

    /* Theme fix:
       - Registration panels use gradient utility color stops, not a flat background.
       - One shared hook keeps all three role forms on semantic surfaces in Dark/OLED. */
    html:not([data-theme="light"]) .shophop-registration-form-panel {
        background: linear-gradient(to bottom, var(--sh-surface), var(--sh-surface), var(--sh-page)) !important;
    }

    html:not([data-theme="light"]) .shophop-registration-dialog {
        background-color: var(--sh-surface) !important;
        color: var(--sh-text) !important;
    }

    /* Lift legacy microcopy to a readable floor while preserving its existing hierarchy. */
    [class~="text-[7px]"],
    [class~="text-[8px]"],
    [class~="text-[8.5px]"],
    [class~="text-[9px]"] {
        font-size: .625rem !important;
    }

    @media (prefers-reduced-motion: reduce) {
        html {
            scroll-behavior: auto !important;
        }

        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }
</style>
