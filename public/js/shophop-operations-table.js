/* Shared accessibility-conscious operations controls; no logistics state mutation here. */
(() => {
  const root = document.querySelector('.sh-ops');
  if (!root) return;
  const $ = (selector, base = root) => base.querySelector(selector);
  const $$ = (selector, base = root) => Array.from(base.querySelectorAll(selector));
  const openDialog = dialog => {
    if (dialog && !dialog.open) dialog.showModal();
  };
  $$('[data-ops-tabs]').forEach(wrapper => {
    const tabs = $$('[data-ops-tab]', wrapper);
    const panels = $$('[data-ops-panel]', wrapper);
    const storageKey = 'shophop:ops-tab:' + location.pathname;
    let stored = '';
    try { stored = sessionStorage.getItem(storageKey) || ''; } catch (_) {}
    const initial = new URL(location.href).searchParams.get('stage') || location.hash.substring(1) || stored;
    const activate = name => {
      if (!tabs.some(t => t.dataset.opsTab === name)) return;
      tabs.forEach(t => { const selected = t.dataset.opsTab === name; t.setAttribute('aria-selected', String(selected)); t.tabIndex = selected ? 0 : -1; });
      panels.forEach(p => { p.hidden = p.dataset.opsPanel !== name; });
      try { sessionStorage.setItem(storageKey, name); } catch (_) {}
    };
    tabs.forEach(tab => tab.addEventListener('click', () => activate(tab.dataset.opsTab)));
    activate(tabs.some(t => t.dataset.opsTab === initial) ? initial : tabs[0]?.dataset.opsTab);
  });
  root.addEventListener('click', event => {
    const close = event.target.closest('[data-ops-close]');
    if (close) { close.closest('dialog')?.close(); return; }
    const trigger = event.target.closest('[data-ops-open]');
    if (trigger) {
      const dialog = document.getElementById(trigger.dataset.opsOpen);
      if (dialog) openDialog(dialog);
    }
    const templateButton = event.target.closest('[data-ops-template]');
    if (templateButton) {
      const template = document.getElementById(templateButton.dataset.opsTemplate);
      const dialog = document.getElementById('opsRecordDialog');
      const target = dialog && $('[data-ops-record-content]', dialog);
      if (!template || !target) return;
      target.replaceChildren(template.content.cloneNode(true));
      const title = $('#opsRecordTitle', dialog);
      const hint = $('#opsRecordHint', dialog);
      if (title) title.textContent = templateButton.dataset.opsTitle || 'Record details';
      if (hint) hint.textContent = templateButton.dataset.opsHint || '';
      openDialog(dialog);
    }
  });
  $$('dialog.ops-dialog').forEach(d => d.addEventListener('click', event => { if (event.target === d) d.close(); }));
  // Position row action menus above scrollable tables instead of clipping at table edges.
  $$('.ops-table details').forEach(details => {
    const menu = $('div', details);
    const summary = $('summary', details);
    if (!menu || !summary) return;
    details.addEventListener('toggle', () => {
      if (!details.open) return;
      const rect = summary.getBoundingClientRect();
      const width = Math.max(160, menu.offsetWidth);
      const height = menu.offsetHeight;
      menu.style.position = 'fixed';
      menu.style.right = 'auto';
      menu.style.left = Math.max(8, Math.min(innerWidth - width - 8, rect.right - width)) + 'px';
      menu.style.top = Math.max(8, Math.min(innerHeight - height - 8, rect.bottom + 5)) + 'px';
    });
  });
  document.addEventListener('click', event => {
    $$('.ops-table details[open]').forEach(details => {
      if (!details.contains(event.target)) details.open = false;
    });
  });

  $$('[data-ops-search]').forEach(input => {
    const target = document.getElementById(input.dataset.opsSearch);
    if (!target) return;
    const rows = $$('[data-ops-row]', target);
    const empty = $('[data-ops-search-empty]', target);
    const update = () => {
      const term = input.value.toLocaleLowerCase().trim();
      let visible = 0;
      rows.forEach(row => {
        const show = row.textContent.toLocaleLowerCase().includes(term);
        row.hidden = !show;
        if (show) visible++;
      });
      if (empty) empty.hidden = visible !== 0;
    };
    input.addEventListener('input', update);
  });
})();
