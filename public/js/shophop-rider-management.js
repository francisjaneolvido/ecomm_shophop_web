(() => {
    'use strict';
    const root = document.querySelector('[data-rider-management]');
    if (!root) return;
    const status = root.querySelector('[data-management-sync]');
    let busy = false, stopped = false, pageHidden = false;
    async function refresh() {
        if (busy || stopped || pageHidden || document.hidden || document.activeElement?.matches('input,select,textarea,button')) return;
        busy = true;
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 20000);
        try {
            const response = await fetch(window.location.href, {
                credentials: 'same-origin', signal: controller.signal, headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
            });
            if (response.status === 401 || response.status === 403) {
                status.textContent = 'Your session or permissions changed. Reload to sign in again.';
                stopped = true;
                clearInterval(timer);
                return;
            }
            if (!response.ok) throw new Error('Refresh unavailable');
            const payload = await response.json();
            if (!payload.success || typeof payload.data?.rows !== 'string') throw new Error('Invalid refresh');
            root.querySelector('[data-management-rows]').innerHTML = payload.data.rows;
            root.querySelector('[data-management-pagination]').innerHTML = payload.data.pagination;
            root.querySelectorAll('[data-management-branch-count]').forEach((cell) => {
                cell.textContent = String(payload.data.active_by_branch?.[cell.dataset.managementBranchCount] ?? 0);
            });
            const total = root.querySelector('[data-management-total]');
            if (total) total.textContent = String(payload.data.total);
            status.textContent = 'Directory updated just now. Open forms keep your entries.';
        } catch (_) {
            status.textContent = 'Live refresh paused by a connection issue. The current directory is retained; retrying automatically.';
        } finally {
            clearTimeout(timeout);
            busy = false;
        }
    }
    let timer = setInterval(refresh, 15000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    window.addEventListener('pagehide', () => { pageHidden = true; clearInterval(timer); });
    window.addEventListener('pageshow', (event) => {
        if (event.persisted && !stopped) { pageHidden = false; timer = setInterval(refresh, 15000); refresh(); }
    });
})();
