<dialog id="consoleDetailsDialog" class="sh-details-dialog" aria-labelledby="consoleDetailsTitle">
    <header class="sh-details-header">
        <div><p class="sh-kicker">Record details</p><h2 id="consoleDetailsTitle">View details</h2></div>
        <button type="button" class="sh-details-close" data-console-details-close aria-label="Close details">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </header>
    <div class="sh-details-status" role="status" aria-live="polite" hidden>Loading details…</div>
    <div class="sh-details-error" role="alert" hidden><p>Unable to load these details. Please try again.</p><button type="button" class="sh-btn-soft" data-console-details-retry>Retry</button></div>
    <div class="sh-details-content"></div>
    <footer class="sh-details-footer"><button type="button" class="sh-btn-soft" data-console-details-close>Close</button></footer>
</dialog>
<script defer src="{{ asset('js/shophop-console-details.js') }}?v=20261112-navy-pink-2"></script>
