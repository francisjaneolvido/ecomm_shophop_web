/* ShopHop Logistics Directory interaction helpers — no AJAX and no API changes */
(() => {
  const dialogs = () => document.querySelectorAll('dialog[data-ops-modal]');
  document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-ops-open]');
    if (opener) {
      const dialog = document.getElementById(opener.getAttribute('data-ops-open'));
      if (dialog && typeof dialog.showModal === 'function') {
        document.querySelectorAll('.ops-actions[open]').forEach(d => d.open = false);
        dialog.showModal();
      }
      return;
    }
    const closer = event.target.closest('[data-ops-close]');
    if (closer) { const dialog = closer.closest('dialog'); if(dialog)dialog.close(); return; }
    document.querySelectorAll('.ops-actions[open]').forEach(d => {
      if (!d.contains(event.target))d.open = false;
    });
  });
  document.querySelectorAll('.ops-actions').forEach(dropdown => {
    dropdown.addEventListener('toggle', () => {
      if (!dropdown.open) return;
      document.querySelectorAll('.ops-actions[open]').forEach(other => { if (other !== dropdown) other.open = false; });
      const menu = dropdown.querySelector('.ops-actions-menu');
      const toggle = dropdown.querySelector('summary');
      if (!menu || !toggle) return;
      const rect = toggle.getBoundingClientRect();
      const width = Math.min(225, window.innerWidth - 20);
      menu.style.width = width + 'px';
      menu.style.left = Math.max(10, Math.min(rect.right - width, window.innerWidth - width - 10)) + 'px';
      menu.style.top = Math.min(rect.bottom + 5, window.innerHeight - Math.min(170,menu.offsetHeight) - 10) + 'px';
    });
  });
  window.addEventListener('scroll', () => document.querySelectorAll('.ops-actions[open]').forEach(d => d.open = false), true);
  dialogs().forEach(dialog => dialog.addEventListener('click', event => {
    if (event.target === dialog) dialog.close();
  }));
  document.querySelectorAll('[data-ops-tabs]').forEach(group => {
    const tabs = [...group.querySelectorAll('[data-ops-tab]')];
    const activate = name => {
      tabs.forEach(tab => {
        const active = tab.dataset.opsTab === name;
        tab.setAttribute('aria-selected', String(active));
        const panel = document.getElementById(tab.getAttribute('aria-controls'));
        if(panel) panel.hidden = !active;
      });
    };
    tabs.forEach(tab => tab.addEventListener('click', () => activate(tab.dataset.opsTab)));
    const requested = new URLSearchParams(location.search).get('tab');
    activate(tabs.some(t=>t.dataset.opsTab===requested) ? requested : (tabs[0]?.dataset.opsTab));
  });
  document.querySelectorAll('[data-ops-record-edit]').forEach(button => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.opsRecordEdit);
      if (!dialog) return;
      const form = dialog.querySelector('form');
      if (!form) return;
      form.action = button.dataset.url;
      const data = JSON.parse(button.dataset.fields || '{}');
      for (const [name,value] of Object.entries(data)) {
        const control = form.elements.namedItem(name);
        if (!control) continue;
        if (control.type === 'checkbox') control.checked = !!value;
        else control.value = value === null ? '' : String(value);
      }
      const subject = dialog.querySelector('[data-ops-subject]');
      if (subject) subject.textContent = button.dataset.subject || '';
      const title = dialog.querySelector('[data-ops-title]');
      if(title && button.dataset.title) title.textContent = button.dataset.title;
      const passwords = form.querySelectorAll('input[type=password]');
      passwords.forEach(el => el.value = '');
    });
  });
})();
