/* Shared console interactions: native dialogs, accessible tabs, row menus and local search. */
(() => {
  'use strict';
  const root = document.querySelector('.sh-management-ui') || document.querySelector('.sh-ops');
  if (!root || root.dataset.opsReady) return;
  root.dataset.opsReady = '1';
  const all = (selector, base = root) => Array.from(base.querySelectorAll(selector));
  let sequence = 0;
  all('[data-ops-tabs]').forEach(wrapper => {
    const tabs = all('[data-ops-tab]', wrapper);
    const panels = tabs.map(tab => {
      const name = tab.dataset.opsTab;
      return document.getElementById(tab.getAttribute('aria-controls')) ||
        all('[data-ops-panel]', wrapper).find(panel => panel.dataset.opsPanel === name);
    });
    if (!tabs.length || panels.some(panel => !panel)) return;
    const tablist = wrapper.matches('[role=tablist]') ? wrapper : wrapper.querySelector('[role=tablist]');
    const param = wrapper.dataset.opsTabParam || 'stage';
    const storageKey = 'shophop:ops-tab:' + location.pathname + ':' + tabs.map(tab => tab.dataset.opsTab).join(',');
    tabs.forEach((tab, index) => {
      const panel = panels[index];
      tab.id ||= 'console-tab-' + (++sequence);
      panel.id ||= 'console-panel-' + sequence;
      tab.setAttribute('role', 'tab');
      tab.setAttribute('aria-controls', panel.id);
      panel.setAttribute('role', 'tabpanel');
      panel.setAttribute('aria-labelledby', tab.id);
    });
    function activate(name, writeUrl = false, moveFocus = false) {
      const index = tabs.findIndex(tab => tab.dataset.opsTab === name);
      if (index < 0) return;
      tabs.forEach((tab, i) => {
        const selected = i === index;
        tab.setAttribute('aria-selected', String(selected));
        tab.tabIndex = selected ? 0 : -1;
        panels[i].hidden = !selected;
      });
      if (moveFocus) tabs[index].focus();
      try { sessionStorage.setItem(storageKey, name); } catch (_) {}
      if (writeUrl) {
        const url = new URL(location.href);
        url.searchParams.set(param, name);
        if (url.searchParams.has('type')) url.searchParams.delete('type');
        if (url.hash && (tabs.some(tab => url.hash === '#' + tab.dataset.opsTab) || panels.some(panel => panel.contains(document.getElementById(url.hash.slice(1)))))) url.hash = '';
        history.replaceState(null, '', url);
      }
    }
    function fromLocation() {
      const url = new URL(location.href);
      let stored = '';
      try { stored = sessionStorage.getItem(storageKey) || ''; } catch (_) {}
      const hashTarget = document.getElementById(url.hash.slice(1));
      const hashPanel = hashTarget ? panels.findIndex(panel => panel === hashTarget || panel.contains(hashTarget)) : -1;
      const candidates = [hashPanel >= 0 ? tabs[hashPanel].dataset.opsTab : '', url.searchParams.get(param), url.hash.slice(1),
        url.searchParams.get('type') === 'delivery' ? 'deliveries' : '', stored,
        tabs.find(tab => tab.getAttribute('aria-selected') === 'true')?.dataset.opsTab, tabs[0].dataset.opsTab];
      activate(candidates.find(name => tabs.some(tab => tab.dataset.opsTab === name)));
    }
    tabs.forEach(tab => tab.addEventListener('click', () => activate(tab.dataset.opsTab, true)));
    (tablist || wrapper).addEventListener('keydown', event => {
      const current = tabs.indexOf(event.target);
      if (current < 0 || !['ArrowLeft','ArrowRight','Home','End'].includes(event.key)) return;
      event.preventDefault();
      const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1
        : (current + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
      activate(tabs[next].dataset.opsTab, true, true);
    });
    window.addEventListener('popstate', fromLocation);
    window.addEventListener('hashchange', fromLocation);
    fromLocation();
  });
  const openDialog = dialog => { if (dialog && !dialog.open) dialog.showModal(); };
  root.addEventListener('click', event => {
    const close = event.target.closest('[data-ops-close]');
    if (close) { close.closest('dialog')?.close(); return; }
    const trigger = event.target.closest('[data-ops-open]');
    if (trigger) {
      all('.ops-actions[open]').forEach(menu => menu.open = false);
      openDialog(document.getElementById(trigger.dataset.opsOpen));
    }
    const button = event.target.closest('[data-ops-template]');
    if (!button) return;
    const template = document.getElementById(button.dataset.opsTemplate);
    const dialog = document.getElementById('opsRecordDialog');
    const target = dialog?.querySelector('[data-ops-record-content]');
    if (!template || !target) return;
    dialog.setAttribute('aria-label', button.dataset.opsTitle || 'Record details');
    target.replaceChildren(template.content.cloneNode(true));
    const title = dialog.querySelector('#opsRecordTitle');
    const hint = dialog.querySelector('#opsRecordHint');
    if (title) title.textContent = button.dataset.opsTitle || 'Record details';
    if (hint) hint.textContent = button.dataset.opsHint || '';
    openDialog(dialog);
  });
  all('dialog.ops-dialog,dialog.ops-modal,dialog[data-ops-modal]').forEach(dialog => {
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
  });
  const menus = () => all('.ops-actions[open],.ops-table details[open]').filter(details => details.querySelector(':scope > .ops-actions-menu'));
  root.addEventListener('toggle', event => {
    const details = event.target;
    if (!(details instanceof HTMLDetailsElement) || !details.open) return;
    const menu = details.querySelector(':scope > .ops-actions-menu');
    const summary = details.querySelector(':scope > summary');
    if (!menu || !summary) return;
    menus().forEach(other => { if (other !== details) other.open = false; });
    const rect = summary.getBoundingClientRect();
    const width = Math.min(224, innerWidth - 24);
    menu.style.position = 'fixed';
    menu.style.width = width + 'px';
    menu.style.right = 'auto';
    menu.style.left = Math.max(12, Math.min(innerWidth - width - 12, rect.right - width)) + 'px';
    const height = menu.getBoundingClientRect().height;
    menu.style.top = Math.max(12, Math.min(innerHeight - height - 12, rect.bottom + 5)) + 'px';
  }, true);
  document.addEventListener('click', event => menus().forEach(details => {
    if (!details.contains(event.target) || event.target.closest('.ops-actions-menu a')) details.open = false;
  }));
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    menus().forEach(details => { details.open = false; details.querySelector('summary')?.focus(); });
  });
  const closeMenus = () => menus().forEach(details => details.open = false);
  function labelScrollableTables() {
    all('.ops-table-scroll,.ops-table-wrap').forEach(container => {
      if (container.scrollWidth > container.clientWidth) {
        container.tabIndex = 0;
        if (!container.hasAttribute('aria-label')) container.setAttribute('aria-label', 'Data table. Scroll horizontally for more columns.');
      } else container.removeAttribute('tabindex');
    });
  }
  labelScrollableTables();
  window.addEventListener('resize', labelScrollableTables);

  window.addEventListener('resize', closeMenus);
  window.addEventListener('scroll', event => {
    if (event.target instanceof Element && event.target.closest('.ops-actions-menu')) return;
    closeMenus();
  }, true);
  // Deep links open a handover dialog or an expandable case without extra navigation.
  function openHashRecord() {
    let id; try { id = decodeURIComponent(location.hash.slice(1)); } catch (_) { return; }
    if (!id) return;
    const target = document.getElementById(id);
    if (!target) return;
    if (target.matches('[data-ops-template]')) target.click();
    else if (target instanceof HTMLDetailsElement) target.open = true;
  }
  window.addEventListener('hashchange', openHashRecord);
  openHashRecord();
  root.querySelector('[data-reopen-record]')?.click();
  const activeLink = root.querySelector(':is(.sh-logistics-sidebar,.sh-console-sidebar) nav [aria-current=page]');
  if (activeLink) activeLink.scrollIntoView({block:'nearest', inline:'nearest'});
  all('[data-personnel-form]').forEach(form => {
    const type = form.elements.namedItem('personnel_type');
    const people = form.elements.namedItem('personnel_id');
    const update = () => {
      let selectedAllowed = false;
      Array.from(people.options).forEach(option => {
        const allowed = !option.dataset.personnelType || option.dataset.personnelType === type.value;
        option.hidden = !allowed;
        option.disabled = !allowed;
        if (option.selected && allowed) selectedAllowed = true;
      });
      if (!selectedAllowed) people.value = '';
    };
    type.addEventListener('change', update);
    update();
  });
  all('[data-ops-search]').forEach(input => {
    const target = document.getElementById(input.dataset.opsSearch);
    if (!target) return;
    input.addEventListener('input', () => {
      const term = input.value.toLocaleLowerCase().trim();
      let visible = 0;
      all('[data-ops-row]', target).forEach(row => {
        row.hidden = !row.textContent.toLocaleLowerCase().includes(term);
        if (!row.hidden) visible++;
      });
      const empty = target.querySelector('[data-ops-search-empty]');
      if (empty) empty.hidden = visible !== 0;
    });
  });
})();
