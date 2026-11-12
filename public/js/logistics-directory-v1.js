/* Directory form population. Shared console controls own tabs, dialogs and menus. */
(() => {
  'use strict';
  document.addEventListener('click', event => {
    const button = event.target.closest('[data-ops-record-edit]');
    if (!button) return;
    const dialog = document.getElementById(button.dataset.opsRecordEdit);
    const form = dialog?.querySelector('form');
    if (!form) return;
    form.reset();
    form.action = button.dataset.url;
    let data;
    try { data = JSON.parse(button.dataset.fields || '{}'); } catch (_) { return; }
    for (const [name, value] of Object.entries(data)) {
      const control = form.elements.namedItem(name);
      if (!control) continue;
      if (control.type === 'checkbox') control.checked = !!value;
      else control.value = value === null ? '' : String(value);
    }
    const subject = dialog.querySelector('[data-ops-subject]');
    if (subject) subject.textContent = button.dataset.subject || '';
    const title = dialog.querySelector('[data-ops-title]');
    if (title && button.dataset.title) title.textContent = button.dataset.title;
    form.querySelectorAll('input[type=password]').forEach(input => input.value = '');
  });
})();
