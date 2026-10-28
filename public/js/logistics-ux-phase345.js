/* ShopHop Logistics 3–5: progressive enhancement only. No API calls or backend changes. */
(() => {
  'use strict';
  function init() {
    const collections = document.querySelectorAll('[data-ui-collection][id]');
    collections.forEach((container) => {
      const id = container.id;
      const search = document.querySelector(`[data-ui-list-search="${id}"]`);
      const status = document.querySelector(`[data-ui-status-select="${id}"]`);
      const count = document.querySelector(`[data-ui-count="${id}"]`);
      if (!search && !status) return;
      const records = Array.from(container.querySelectorAll('[data-ui-record]'));
      // Filtering is limited to already-rendered records; it does not modify server queries.
      const empty = document.createElement('div');
      empty.className = 'sh-ui-no-results';
      empty.hidden = true;
      empty.setAttribute('data-ui-empty', '');
      empty.setAttribute('role', 'status');
      empty.textContent = 'No matching records on this page. Clear the search or status filter.';
      container.appendChild(empty);
      function update() {
        const q = (search?.value || '').trim().toLocaleLowerCase();
        const selected = status?.value || 'all';
        let visible = 0;
        records.forEach((item) => {
          const matchesText = !q || item.textContent.toLocaleLowerCase().includes(q);
          const matchesStatus = selected === 'all' || (item.dataset.uiStatus || '').toLocaleLowerCase() === selected;
          const show = matchesText && matchesStatus;
          item.hidden = !show;
          if (show) visible++;
        });
        empty.hidden = visible !== 0 || records.length === 0;
        if (count) count.textContent = `${visible} of ${records.length} shown`;
      }
      search?.addEventListener('input', update);
      status?.addEventListener('change', update);
      update();
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once:true});
  else init();
})();
