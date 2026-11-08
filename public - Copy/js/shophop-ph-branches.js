/* Philippine location fields for logistics branches. Enhances inputs only when API is reachable.
   Preserves legacy saved location names and allows text fallback on API failure. */
(function () {
  'use strict';
  const api = window.ShopHopPHAddress;
  if (!api) return;
  const norm = value => String(value || '').trim().toLowerCase();
  function selectify(input, label) {
    const select = document.createElement('select');
    select.className = input.className.replace('sh-input', 'sh-select');
    select.name = input.name; select.required = input.required;
    select.setAttribute('aria-label', label);
    select.append(new Option('Select ' + label, ''));
    input.replaceWith(select);
    return select;
  }
  function fill(select, rows, label, previous = '') {
    select.replaceChildren(new Option('Select ' + label, ''));
    rows.forEach(row => {
      const option = new Option(row.name, row.name);
      option.dataset.code = row.code;
      if (norm(previous) === norm(row.name)) option.selected = true;
      select.append(option);
    });
    if (previous && !select.value) select.add(new Option(previous + ' (saved)', previous, true, true));
    select.disabled = false;
  }
  const code = el => el?.selectedOptions[0]?.dataset.code || '';
  function helper(after, text) {
    const note = document.createElement('p');
    note.className = 'mt-2 text-xs text-navy/60'; note.textContent = text;
    after.after(note); return note;
  }
  async function setupAddress(form, regions) {
    const inputs = {};
    for (const name of ['region', 'province', 'municipality', 'barangay']) {
      inputs[name] = form.querySelector('input[name="' + name + '"]');
      if (!inputs[name]) return;
    }
    const original = Object.fromEntries(Object.entries(inputs).map(([key, el]) => [key, el.value]));
    const region = selectify(inputs.region, 'region');
    const province = selectify(inputs.province, 'province');
    const city = selectify(inputs.municipality, 'city or municipality');
    const barangay = selectify(inputs.barangay, 'barangay');
    const provinceField = province.closest('label');
    const note = helper(barangay.closest('label'), 'Region → Province (when applicable) → City / Municipality → Barangay');
    fill(region, regions.map(r => ({ code: r.code, name: api.regionLabel(r) })), 'region', original.region);
    // Preserve older stored labels when the region name is abbreviated.
    if (!region.value && original.region) {
      const found = regions.find(r => norm(original.region).includes(norm(r.name)) || norm(original.region).includes(norm(r.regionName || '##')));
      if (found) region.value = api.regionLabel(found);
    }
    let ticket = 0;
    async function onCity(previous = '') {
      const id = ++ticket;
      fill(barangay, [], 'barangay');
      if (!code(city)) return;
      note.textContent = 'Loading barangays…';
      try {
        const result = await api.barangays(code(city));
        if (id !== ticket) return;
        fill(barangay, result, 'barangay', previous);
        note.textContent = 'Select the exact warehouse barangay.';
      } catch (_) { note.textContent = 'Barangays unavailable. Retry selection when connected.'; }
    }
    async function onProvince(previousCity = '', previousBarangay = '') {
      const id = ++ticket;
      fill(city, [], 'city or municipality'); fill(barangay, [], 'barangay');
      if (!code(region) || !code(province)) return;
      note.textContent = 'Loading cities…';
      try {
        const result = await api.cities(code(region), code(province));
        if (id !== ticket) return;
        fill(city, result, 'city or municipality', previousCity);
        note.textContent = 'Select the exact warehouse city and barangay.';
        if (code(city)) await onCity(previousBarangay);
      } catch (_) { note.textContent = 'Cities unavailable. Retry when connected.'; }
    }
    async function onRegion(previousProvince = '', previousCity = '', previousBarangay = '') {
      const id = ++ticket;
      fill(province, [], 'province'); fill(city, [], 'city or municipality'); fill(barangay, [], 'barangay');
      provinceField.style.display = '';
      if (!code(region)) return;
      note.textContent = 'Loading provinces…';
      try {
        const rows = await api.provinces(code(region));
        if (id !== ticket) return;
        if (!rows.length) {
          fill(province, [{ code: code(region), name: region.value }], 'province', region.value);
          provinceField.style.display = 'none';
          note.textContent = 'NCR has no province — proceed to city or municipality.';
        } else fill(province, rows, 'province', previousProvince);
        if (code(province)) await onProvince(previousCity, previousBarangay);
      } catch (_) { note.textContent = 'Province list unavailable. Retry when connected.'; }
    }
    region.addEventListener('change', () => onRegion());
    province.addEventListener('change', () => onProvince());
    city.addEventListener('change', () => onCity());
    if (code(region)) await onRegion(original.province, original.municipality, original.barangay);
  }
  async function setupCoverage(row, regions, provinces) {
    if (row.dataset.coverageReady) return;
    const type = row.querySelector('[name$="[area_type]"]');
    const area = row.querySelector('input[name$="[area_name]"]');
    const cities = row.querySelector('input[name$="[cities]"]');
    if (!type || !area || !cities) return;
    row.dataset.coverageReady = '1';
    const previousArea = area.value;
    const previousCities = cities.value || 'ALL';
    const areaSelect = selectify(area, 'service area');
    cities.type = 'hidden';
    const box = document.createElement('div'); box.className = 'mt-2 rounded-xl border border-gray-border bg-white p-3';
    const allLabel = document.createElement('label'); allLabel.className = 'flex cursor-pointer items-center gap-2 text-xs font-semibold text-navy';
    const all = document.createElement('input'); all.type = 'checkbox'; all.checked = norm(previousCities) === 'all';
    allLabel.append(all, document.createTextNode('Serve ALL cities / municipalities in this area'));
    const search = document.createElement('input'); search.type = 'search'; search.className = 'sh-input mt-3'; search.placeholder = 'Filter cities by name…'; search.setAttribute('aria-label', 'Filter cities');
    const options = document.createElement('div'); options.className = 'mt-3 grid max-h-48 gap-1.5 overflow-y-auto sm:grid-cols-2';
    options.setAttribute('role', 'group'); options.setAttribute('aria-label', 'Select cities served');
    const hint = document.createElement('p'); hint.className = 'mt-2 text-xs text-navy/55';
    box.append(allLabel, search, options, hint); cities.after(box);
    function sync() {
      options.hidden = all.checked; search.hidden = all.checked;
      if (all.checked) cities.value = 'ALL';
      else cities.value = [...options.querySelectorAll('input[type="checkbox"]:checked')].map(input => input.value).join('|');
      if (!all.checked && options.children.length && !cities.value) hint.textContent = 'Select at least one city or choose ALL cities.';
    }
    all.addEventListener('change', sync);
    search.addEventListener('input', () => {
      const term = norm(search.value);
      options.querySelectorAll('label').forEach(el => { el.hidden = !norm(el.textContent).includes(term); });
    });
    let ticket = 0;
    async function updateCities(preset = 'ALL') {
      const id = ++ticket;
      options.replaceChildren();
      if (!code(areaSelect)) { hint.textContent = 'Choose a province or region first.'; return; }
      hint.textContent = 'Loading cities…';
      try {
        const list = type.value === 'region' ? await api.cities(code(areaSelect), '') : await api.cities('', code(areaSelect));
        if (id !== ticket) return;
        const selected = preset.split(/[|,]+/).map(norm);
        const fragment = document.createDocumentFragment();
        list.forEach(item => {
          const label = document.createElement('label'); label.className = 'flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1 text-xs text-navy hover:bg-gray-bg';
          const check = document.createElement('input'); check.type = 'checkbox'; check.value = item.name; check.checked = selected.includes(norm(item.name));
          check.addEventListener('change', sync);
          label.append(check, document.createTextNode(item.name)); fragment.append(label);
        });
        if (!list.length) {
          cities.type = 'text'; cities.readOnly = false; box.hidden = true; cities.value = preset; return;
        }
        options.append(fragment); all.checked = norm(preset) === 'all' || !preset;
        hint.textContent = list.length ? 'Tap cities to select several. No Ctrl/Cmd needed.' : 'No cities returned; select ALL or enter manually.';
        sync();
      } catch (_) {
        cities.type = 'text'; cities.readOnly = false; cities.classList.remove('hidden');
        box.hidden = true; cities.value = preset;
      }
    }
    async function updateAreas(saved = '') {
      ++ticket;
      const data = type.value === 'region' ? regions.map(r => ({ code: r.code, name: api.regionLabel(r) })) : provinces;
      fill(areaSelect, data, 'service area', saved);
      if (code(areaSelect)) await updateCities(saved ? previousCities : 'ALL');
      else { options.replaceChildren(); cities.value = 'ALL'; hint.textContent = 'Choose a coverage area.'; }
    }
    type.addEventListener('change', () => updateAreas());
    areaSelect.addEventListener('change', () => updateCities('ALL'));
    await updateAreas(previousArea);
  }
  async function init() {
    const forms = [...document.querySelectorAll('form[data-ph-branch]')];
    if (!forms.length) return;
    try {
      const regions = await api.regions();
      // A single unavailable province endpoint must not break the entire form.
      const results = await Promise.allSettled(regions.map(region => api.provinces(region.code)));
      const provinces = [...new Map(results.filter(r => r.status === 'fulfilled').flatMap(r => r.value).map(p => [p.code, p])).values()];
      forms.forEach(form => setupAddress(form, regions));
      const initializeCoverage = () => document.querySelectorAll('[data-coverage-row]').forEach(row => setupCoverage(row, regions, provinces));
      initializeCoverage();
      for (const list of document.querySelectorAll('[data-coverage-list]')) {
        new MutationObserver(initializeCoverage).observe(list, { childList: true });
      }
    } catch (error) { console.warn('Location service unavailable — editable text inputs retained.', error); }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
