{{-- Payment-only styles reuse Checkout proportions and semantic tokens without changing either role shell. --}}
@push('styles')
<style>
    /* Darken semantic teal text enough for normal-size status labels on the existing mint surface. */
    .payment-ui { --pay-accent: #077260; --pay-danger: #b42336; --pay-warning: #895400; color: var(--sh-text); font-size: .875rem; line-height: 1.65; }
    html:not([data-theme="light"]) .payment-ui { --pay-accent: #62e0c4; --pay-danger: #ff9fac; --pay-warning: #f4cd80; }
    .payment-ui *, .payment-ui *::before, .payment-ui *::after { box-sizing: border-box; }
    .payment-ui svg { width: 1.15rem; height: 1.15rem; flex: none; }
    .payment-ui h1 { font-size: clamp(1.5rem, 3vw, 1.875rem); line-height: 1.25; letter-spacing: -.025em; font-weight: 700; }
    .payment-ui h2 { font-size: 1rem; line-height: 1.4; letter-spacing: -.01em; font-weight: 700; }
    .payment-ui h3 { font-size: .875rem; line-height: 1.5; font-weight: 600; }
    .payment-ui p { margin: 0; }
    /* Author button display must not override the no-script fallback's hidden safe-action control. */
    .payment-ui [hidden] { display: none !important; }
    .payment-ui a { text-underline-offset: .25em; }
    .payment-ui :where(a, button, input, textarea, summary):focus-visible { outline: 3px solid var(--pay-accent); outline-offset: 3px; }
    .payment-ui ::selection { background: var(--sh-brand-soft); color: var(--sh-text); }
    .pay-page { background: var(--sh-surface-soft); min-height: 75vh; padding: 1.75rem 1rem 3rem; }
    .pay-container { max-width: 1160px; margin: auto; }
    .pay-back { display: inline-flex; gap: .5rem; align-items: center; min-height: 44px; color: var(--sh-muted); font-weight: 500; text-decoration: none; margin-bottom: 1rem; }
    .pay-back:hover { color: var(--pay-accent); }
    .pay-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
    .pay-header p { margin-top: .5rem; color: var(--sh-muted); max-width: 65ch; }
    .pay-grid { display: grid; grid-template-columns: minmax(0, 1fr) 350px; gap: 1.5rem; align-items: start; }
    .pay-stack { display: grid; gap: 1.25rem; min-width: 0; }
    .pay-panel { min-width: 0; border: 1px solid var(--sh-border); border-radius: 16px; background: var(--sh-surface); overflow: hidden; }
    .pay-pad { padding: 1.5rem; }
    .pay-section-head { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.25rem; }
    .pay-icon { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 10px; background: var(--sh-brand-soft); color: var(--pay-accent); flex: none; }
    .pay-muted { color: var(--sh-muted); }
    .pay-small { font-size: .8125rem; }
    .pay-status { display: inline-flex; align-items: center; gap: .45rem; border-radius: 8px; padding: .4rem .65rem; font-size: .75rem; font-weight: 600; background: var(--sh-brand-soft); color: var(--pay-accent); }
    .pay-status[data-tone="warning"] { background: color-mix(in srgb, var(--pay-warning) 10%, var(--sh-surface)); color: var(--pay-warning); }
    .pay-status[data-tone="neutral"] { background: var(--sh-surface-raised); color: var(--sh-muted); }
    .pay-state { padding-bottom: 1.25rem; border-bottom: 1px solid var(--sh-border); margin-bottom: 1.25rem; }
    .pay-state h2 { margin: .875rem 0 .5rem; font-size: 1.125rem; }
    .pay-deadline { display: flex; gap: .625rem; align-items: flex-start; margin-top: 1rem; padding: .875rem 1rem; background: var(--sh-surface-raised); border-radius: 10px; }
    .pay-deadline svg { margin-top: .2rem; color: var(--pay-warning); }
    .pay-amount { font-size: clamp(1.8rem, 4vw, 2.25rem); font-weight: 700; letter-spacing: -.03em; line-height: 1.3; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; margin: .4rem 0 .5rem; }
    .pay-orders { list-style: none; padding: 0; margin: 0; }
    .pay-order { display: flex; justify-content: space-between; gap: 1rem; padding: 1rem 0; border-top: 1px solid var(--sh-border); align-items: start; }
    .pay-order > div { min-width: 0; }
    /* Keep money readable when the Seller name consumes the remaining row width. */
    .pay-order > .pay-money { flex-shrink: 0; }
    .pay-order strong, .pay-wrap { overflow-wrap: anywhere; }
    .pay-order strong { display: block; font-weight: 600; }
    .pay-order span { display: block; font-size: .75rem; color: var(--sh-muted); margin-top: .25rem; }
    .pay-money { font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .pay-fields { display: grid; gap: 1.125rem; }
    .pay-field label { display: block; font-weight: 600; margin-bottom: .375rem; }
    .pay-field input, .pay-field textarea { display: block; width: 100%; min-width: 0; border: 1px solid var(--sh-border); border-radius: 10px; background: var(--sh-input); color: var(--sh-text); padding: .75rem; font: inherit; caret-color: var(--pay-accent); }
    .pay-field textarea { min-height: 110px; resize: vertical; }
    .pay-field input[type="file"] { font-size: .8125rem; overflow: hidden; }
    .pay-field input::file-selector-button { border: 0; border-radius: 6px; background: var(--sh-surface-raised); color: var(--sh-text); padding: .5rem .75rem; margin-right: .75rem; font: inherit; cursor: pointer; }
    .pay-field input[aria-invalid="true"], .pay-field textarea[aria-invalid="true"] { border-color: var(--pay-danger); }
    .pay-help { margin-top: .4rem !important; font-size: .75rem; color: var(--sh-muted); }
    .pay-error { color: var(--pay-danger); font-size: .8125rem; margin-top: .375rem !important; overflow-wrap: anywhere; }
    .pay-button { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 44px; padding: .65rem 1.125rem; border: 1px solid var(--sh-border); border-radius: 10px; background: var(--sh-surface); color: var(--sh-text); font: inherit; font-weight: 600; cursor: pointer; text-decoration: none; }
    .pay-button:hover { background: var(--sh-surface-raised); }
    .pay-button--primary { background: var(--sh-brand); border-color: var(--sh-brand); color: #0f1b3d; }
    .pay-button--primary:hover { background: var(--sh-brand-strong); border-color: var(--sh-brand-strong); }
    .pay-button--danger { color: var(--pay-danger); border-color: var(--pay-danger); }
    .pay-button--danger:hover { background: color-mix(in srgb, var(--pay-danger) 10%, var(--sh-surface)); }
    .pay-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; margin-top: 1.25rem; }
    .pay-note { margin-top: 1rem !important; font-size: .8125rem; color: var(--sh-muted); }
    .pay-notice { border-radius: 10px; padding: 1rem; background: var(--sh-brand-soft); color: var(--pay-accent); margin-bottom: 1.25rem; overflow-wrap: anywhere; }
    .pay-notice--error { background: color-mix(in srgb, var(--pay-danger) 8%, var(--sh-surface)); color: var(--pay-danger); }
    .pay-notice ul { padding-left: 1.25rem; margin: .5rem 0 0; }
    .pay-reason { padding: 1rem; border-radius: 10px; background: var(--sh-surface-raised); margin-top: 1rem; overflow-wrap: anywhere; white-space: pre-line; }
    .pay-facts { margin: 0; display: grid; gap: 1rem; }
    .pay-facts dt { color: var(--sh-muted); font-size: .75rem; }
    .pay-facts dd { margin: .25rem 0 0; font-weight: 500; overflow-wrap: anywhere; }
    .pay-divider { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--sh-border); }
    /* Closure stays outside proof review; native modal enhancement retains an inline no-script confirmation. */
    .pay-danger-area { border-top: 1px solid var(--sh-border); padding-top: 1.5rem; margin-top: 1.75rem; }
    .pay-danger-area h2 { color: var(--pay-danger); margin-bottom: .5rem; }
    .pay-danger-area > p { max-width: 65ch; }
    .pay-danger-area details { margin-top: 1rem; }
    .pay-danger-area summary { list-style: none; width: fit-content; }
    .pay-danger-area summary::-webkit-details-marker { display: none; }
    .pay-confirm { padding: 1.5rem; }
    .pay-confirm h2 { margin: .75rem 0; }
    .pay-confirm ul { padding-left: 1.25rem; margin: 1rem 0; }
    .pay-confirm li + li { margin-top: .5rem; }
    .pay-dialog { margin: auto; width: min(560px, calc(100% - 2rem)); max-height: calc(100dvh - 2rem); padding: 0; border: 1px solid var(--sh-border); border-radius: 16px; background: var(--sh-surface); color: var(--sh-text); overflow-y: auto; box-shadow: 0 24px 80px var(--sh-shadow); }
    .pay-dialog::backdrop { background: var(--sh-overlay); }
    /* Queue rows retain backend ordering; phone labels replace horizontal scrolling. */
    .pay-queue { width: 100%; border-collapse: collapse; text-align: left; }
    .pay-queue th { background: var(--sh-surface-soft); color: var(--sh-muted); font-size: .75rem; font-weight: 500; }
    .pay-queue th, .pay-queue td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--sh-border); overflow-wrap: anywhere; }
    .pay-queue td { vertical-align: middle; }
    .pay-queue td:first-child, .pay-queue td:nth-child(2) { width: 26%; }
    .pay-queue tr:last-child td { border-bottom: 0; }
    .pay-queue tbody tr:hover { background: var(--sh-surface-soft); }
    .pay-empty { padding: 3.5rem 1.5rem; text-align: center; }
    .pay-empty .pay-icon { margin: auto auto 1rem; width: 48px; height: 48px; }
    .pay-empty p { max-width: 48ch; margin: .75rem auto 0; color: var(--sh-muted); }
    /* On phones/tablets, amount precedes the task and the long Seller list follows it. */
    @media (max-width: 960px) {
        .pay-grid { grid-template-columns: minmax(0, 1fr); }
        .pay-summary { display: contents; }
        .pay-summary > .pay-panel:first-child { grid-row: 1; }
        .pay-grid > .pay-stack:not(.pay-summary) { grid-row: 2; }
        .pay-summary > .pay-panel:last-child { grid-row: 3; }
    }
    @media (max-width: 640px) {
        .pay-page { padding: 1rem 1rem 2rem; }
        .pay-pad, .pay-confirm { padding: 1.125rem; }
        .pay-header { flex-direction: column; gap: .75rem; }
        .pay-actions .pay-button { width: 100%; }
        .pay-order { flex-wrap: wrap; gap: .5rem; }
        .pay-queue thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }
        .pay-queue, .pay-queue tbody, .pay-queue tr, .pay-queue td { display: block; width: 100% !important; }
        .pay-queue tr { padding: 1rem; border-bottom: 1px solid var(--sh-border); }
        .pay-queue td { padding: .4rem 0; border: 0; }
        .pay-queue td::before { content: attr(data-label); display: block; font-size: .75rem; color: var(--sh-muted); margin-bottom: .2rem; }
    }
    @media (prefers-reduced-motion: reduce) { .payment-ui *, .payment-ui *::before, .payment-ui *::after { animation: none !important; transition: none !important; scroll-behavior: auto !important; } }
</style>
@endpush
