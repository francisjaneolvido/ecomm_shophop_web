{{-- The toggle is markup-only; shared theme behavior lives in theme-head so repeated controls cannot drift. --}}
<button
    type="button"
    class="theme-toggle"
    data-theme-toggle
    data-theme-current="light"
    aria-label="Light theme. Switch to dark theme"
    title="Theme: Light (switch to dark)"
>
    <svg class="theme-toggle__icon theme-toggle__icon--light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"></path>
    </svg>
    <svg class="theme-toggle__icon theme-toggle__icon--dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"></path>
    </svg>
    <svg class="theme-toggle__icon theme-toggle__icon--oled" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <circle cx="12" cy="12" r="9"></circle>
        <circle cx="12" cy="12" r="4" fill="var(--sh-brand)"></circle>
    </svg>
    <span class="hidden sm:inline" data-theme-label>Light</span>
</button>
