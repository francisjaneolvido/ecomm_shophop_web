/* Branch setup wizard. No database changes until the final Save action. */
(function () {
  'use strict';
  const form = document.querySelector('[data-branch-wizard]');
  if (!form) return;
  const steps = [...form.querySelectorAll('[data-wizard-step]')];
  const links = [...form.querySelectorAll('[data-step-link]')];
  const back = form.querySelector('[data-wizard-back]');
  const next = form.querySelector('[data-wizard-next]');
  const save = form.querySelector('[data-wizard-submit]');
  const review = form.querySelector('[data-branch-review]');
  const list = form.querySelector('[data-coverage-list]');
  const template = document.getElementById('coverage-row-template');
  let current = 0;
  function field(name) { return form.querySelector('[name="' + name + '"]')?.value?.trim() || ''; }
  function updateReview() {
    if (!review) return;
    const areas = [...list.querySelectorAll('[name$="[area_name]"]')].map(el => el.value).filter(Boolean);
    const items = [
      ['Facility', field('name') || 'Not provided'],
      ['Warehouse address', [field('barangay'), field('municipality'), field('province'), field('region')].filter(Boolean).join(', ') || 'Not provided'],
      ['Service coverage', areas.length ? areas.join('; ') : 'Not configured'],
    ];
    if (form.querySelector('[name="manager_name"]')) items.push(['New representative', field('manager_name') || 'Add after saving']);
    review.replaceChildren(...items.map(([name, value]) => {
      const line = document.createElement('p');
      const heading = document.createElement('strong'); heading.textContent = name + ': ';
      line.append(heading, document.createTextNode(value));
      return line;
    }));
  }
  function show(step) {
    current = Math.max(0, Math.min(step, steps.length - 1));
    steps.forEach((el, i) => { el.hidden = i !== current; });
    links.forEach((el, i) => {
      el.classList.toggle('border-teal', i === current);
      el.classList.toggle('bg-teal-light', i === current);
      el.setAttribute('aria-current', i === current ? 'step' : 'false');
    });
    back.hidden = current === 0;
    next.hidden = current === steps.length - 1;
    save.hidden = current !== steps.length - 1;
    if (current === steps.length - 1) updateReview();
  }
  function firstInvalid(index) {
    const control = steps[index].querySelector(':invalid');
    if (!control) return false;
    if (control.reportValidity) control.reportValidity();
    control.focus();
    return true;
  }
  next.addEventListener('click', () => { if (!firstInvalid(current)) show(current + 1); });
  back.addEventListener('click', () => show(current - 1));
  links.forEach((link, step) => link.addEventListener('click', () => {
    if (step <= current) { show(step); return; }
    for (let i = 0; i < step; i++) { if (firstInvalid(i)) { show(i); return; } }
    show(step);
  }));
  function renumber() {
    list.querySelectorAll('[data-coverage-row]').forEach((row, i) => {
      row.querySelectorAll('[data-field], [name^="coverage["]').forEach(el => {
        const name = el.dataset.field || el.name?.match(/\]\[([^\]]+)\]$/)?.[1];
        if (name) el.name = 'coverage[' + i + '][' + name + ']';
      });
    });
  }
  form.querySelector('[data-add-coverage]').addEventListener('click', () => {
    if (list.querySelectorAll('[data-coverage-row]').length >= 20) return;
    list.append(template.content.cloneNode(true));
    renumber();
  });
  form.addEventListener('submit', event => {
    for (let i = 0; i < steps.length; i++) {
      if (firstInvalid(i)) { event.preventDefault(); show(i); return; }
    }
    if (save.disabled) { event.preventDefault(); return; }
    save.disabled = true; save.textContent = 'Saving branch…';
  });
  show(0);
  if (form.querySelector('.sh-alert-error')) show(0);
})();
