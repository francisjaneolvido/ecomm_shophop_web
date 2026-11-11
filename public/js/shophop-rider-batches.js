(() => {
    const routes = document.getElementById('rider-batch-routes');
    if (!routes) return;
    const status = document.querySelector('[data-batch-sync-status]');
    let busy = false;
    let stopped = false;
    async function refresh() {
        if (busy || stopped || document.hidden || document.querySelector('#branchConfirmModal:not([hidden])')) return;
        if (routes.contains(document.activeElement)) return;
        busy = true;
        const abort = new AbortController();
        const timeout = setTimeout(() => abort.abort(), 12000);
        try {
            const response = await fetch(routes.dataset.liveUrl, {
                headers: {Accept: 'application/json'}, signal: abort.signal, cache: 'no-store'
            });
            if ([401, 403, 419].includes(response.status)) {
                stopped = true;
                throw new Error('Session expired. Sign in again to refresh routes.');
            }
            if (!response.ok) throw new Error('Routes could not refresh. Refresh the page to check the latest assignments.');
            const payload = await response.json();
            if (!payload.success || typeof payload.data?.html !== 'string') throw new Error('Unexpected route response. Refresh this page to retry.');
            const open = [...routes.querySelectorAll('article[data-batch-id]')].filter(card => card.querySelector('details[open]')).map(card => card.dataset.batchId);
            // This HTML comes exclusively from our authenticated, escaped Blade view.
            routes.innerHTML = payload.data.html;
            for (const card of routes.querySelectorAll('article[data-batch-id]')) {
                if (open.includes(card.dataset.batchId)) card.querySelector('details')?.setAttribute('open', '');
            }
            for (const total of document.querySelectorAll('[data-batch-total]')) {
                total.textContent = String(payload.data.totals?.[total.dataset.batchTotal] ?? 0);
            }
            if (status) status.textContent = 'Routes updated at ' + new Date().toLocaleTimeString() + '.';
        } catch (error) {
            if (status) status.textContent = error.name === 'AbortError' ? 'Route refresh timed out. Retrying shortly.' : error.message;
        } finally {
            clearTimeout(timeout);
            busy = false;
        }
    }
    let timer = setInterval(refresh, 15000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) return;
        stopped = false;
        timer = setInterval(refresh, 15000);
        refresh();
    });
    window.addEventListener('pagehide', () => {stopped = true;clearInterval(timer);}, {once: true});
})();
