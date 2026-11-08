(() => {
  'use strict';
  const modal = document.getElementById('ssVoucherModal');
  if (!modal) return;
  const cfg = window.ShopHopPromotionsConfig || {};
  const $ = id => document.getElementById(id);
  const form = $('ssVoucherForm');
  const results = $('ssProductResults');
  const selectedPanel = $('ssSelectedProducts');
  const search = $('ssProductSearch');
  const selected = new Map();
  let lastFocus = null;
  let controller = null;
  let debounce = null;

  function drawSelected() {
    selectedPanel.replaceChildren();
    $('ssSelectedCount').textContent = selected.size + ' selected';
    for (const [id, name] of selected) {
      const chip = document.createElement('span'); chip.className = 'ss-product-chip';
      const text = document.createElement('span'); text.textContent = name;
      const button = document.createElement('button'); button.type = 'button'; button.textContent = '×';
      button.setAttribute('aria-label', 'Remove ' + name);
      button.addEventListener('click', () => {selected.delete(id); drawSelected(); loadResults();});
      const hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = 'product_ids[]'; hidden.value = id;
      chip.append(text, button, hidden); selectedPanel.append(chip);
    }
  }
  function open(button) {
    lastFocus = document.activeElement;
    form.reset(); selected.clear();
    const data = button?.dataset?.voucher ? JSON.parse(button.dataset.voucher) : null;
    $('ssVoucherTitle').textContent = data ? 'Edit Voucher' : 'Create Voucher';
    const fields = {ssVoucherId:'id',ssVoucherName:'name',ssVoucherCode:'code',ssVoucherKind:'kind',ssVoucherType:'type',ssVoucherValue:'value',ssVoucherMin:'min_order_amount',ssVoucherUsage:'usage_limit',ssVoucherCap:'max_discount',ssVoucherBuyerLimit:'per_buyer_limit',ssVoucherStart:'starts_at',ssVoucherEnd:'ends_at'};
    if (data) {
      for (const [element, field] of Object.entries(fields)) $(element).value = data[field] ?? '';
      (data.products || []).forEach(p => selected.set(String(p.id), p.name));
    } else if (cfg.hasErrors && !button) {
      (cfg.oldProductIds || []).forEach(id => selected.set(String(id), 'Product #' + id));
    } else {
      // Create must never inherit an old edit ID after a validation error.
      $('ssVoucherId').value = '';
    }
    search.value = ''; drawSelected();
    modal.hidden = false; modal.setAttribute('aria-hidden','false'); document.body.classList.add('ss-modal-open');
    $('ssVoucherName').focus(); loadResults();
  }
  function close() {
    modal.hidden = true; modal.setAttribute('aria-hidden','true'); document.body.classList.remove('ss-modal-open');
    if (controller) controller.abort(); if (lastFocus?.focus) lastFocus.focus();
  }
  async function loadResults() {
    if (controller) controller.abort(); controller = new AbortController();
    results.textContent = 'Loading matching products...';
    try {
      const url = new URL(cfg.searchUrl, window.location.href); url.searchParams.set('q', search.value.trim());
      const response = await fetch(url.toString(), {headers:{Accept:'application/json'},credentials:'same-origin',signal:controller.signal});
      if (!response.ok) throw new Error('Product lookup unavailable');
      const payload = await response.json();
      results.replaceChildren();
      if (!(payload.products || []).length) {results.textContent = 'No active products matched.';return;}
      (payload.products || []).forEach(p => {
        const row = document.createElement('button'); row.type = 'button'; row.className = 'ss-product-result';
        const id = String(p.id); row.textContent = (selected.has(id) ? '✓ ' : '+ ') + p.name + (p.sku ? ' · ' + p.sku : '');
        row.addEventListener('click', () => {selected.has(id) ? selected.delete(id) : selected.set(id, p.name); drawSelected(); loadResults();});
        results.appendChild(row);
      });
    } catch(e) {if (e.name !== 'AbortError') results.textContent = 'Could not load products. Please retry.';}
  }
  document.querySelectorAll('[data-voucher-open]').forEach(b => b.addEventListener('click', () => open(b)));
  document.querySelectorAll('[data-voucher-close]').forEach(b => b.addEventListener('click', close));
  document.addEventListener('keydown', event => {if (!modal.hidden && event.key === 'Escape') close();});
  search.addEventListener('input', () => {clearTimeout(debounce);debounce = setTimeout(loadResults,240);});
  document.querySelectorAll('[data-copy-code]').forEach(b => b.addEventListener('click', async () => {
    try {await navigator.clipboard.writeText(b.dataset.copyCode);b.title='Copied';} catch (_) {b.title='Copy unavailable';}
  }));
  form.addEventListener('submit', event => {
    const value = Number($('ssVoucherValue').value);
    if ($('ssVoucherType').value === 'percent' && value > 100) {
      event.preventDefault(); $('ssVoucherValue').setCustomValidity('Percent must not exceed 100.'); $('ssVoucherValue').reportValidity();
    }
  });
  $('ssVoucherValue').addEventListener('input', () => $('ssVoucherValue').setCustomValidity(''));
  if (cfg.hasErrors) open(null);
})();
