(() => {
    const config = document.querySelector('[data-catalog-revision-url]');
    if (!config) return;
    let revision = config.dataset.catalogRevision, busy = false, editing = false, submitting = false;
    // Preserve unsent search/form changes and pending cart submissions.
    document.addEventListener('input', event => {
        if (event.target.matches('input,textarea,select')) editing = true;
    });
    document.addEventListener('submit', () => { submitting = true; });
    document.addEventListener('click', event => {
        if (event.target.closest('[data-add-to-cart],#addToCartBtn,#buyNowBtn')) {
            submitting = true;
            setTimeout(() => { submitting = false; }, 5000);
        }
    });
    async function check() {
        if (busy || document.hidden || editing || submitting) return;
        busy = true;
        try {
            const response = await fetch(config.dataset.catalogRevisionUrl, {cache: 'no-store', headers: {Accept: 'application/json'}});
            if (!response.ok) return;
            const data = await response.json();
            if (typeof data.revision !== 'string') return;
            if (revision && revision !== data.revision && !editing && !submitting) {
                location.reload();
                return;
            }
            revision = data.revision;
        } catch (_) {} finally { busy = false; }
    }
    setInterval(check, 15000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) check(); });
    window.addEventListener('focus', check);
    check();
})();
