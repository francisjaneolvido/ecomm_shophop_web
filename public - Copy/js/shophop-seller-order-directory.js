/* ShopHop Seller Order Management: one native accessible modal and restrained row menus. */
(() => {
  'use strict';
  const root = document.getElementById('sellerOrderDirectory');
  if (!root) return;
  const dialog = document.getElementById('sellerOrderDialog');
  const body = document.getElementById('sellerOrderDialogBody');
  if (!dialog || !body) return;

  root.addEventListener('click', (event) => {
    const open = event.target.closest('[data-so-preview]');
    if (open) {
      const template = document.getElementById(open.getAttribute('data-so-preview'));
      if (!template || template.tagName !== 'TEMPLATE') return;
      body.replaceChildren(template.content.cloneNode(true));
      root.querySelectorAll('.so-actions-menu[open]').forEach((menu) => menu.removeAttribute('open'));
      if (typeof dialog.showModal === 'function') dialog.showModal();
      else window.location.assign(open.closest('tr').querySelector('.so-order-id').href);
      return;
    }
    if (event.target.closest('[data-so-close]')) dialog.close();
    if (!event.target.closest('.so-actions-menu')) {
      root.querySelectorAll('.so-actions-menu[open]').forEach((menu) => menu.removeAttribute('open'));
    }
  });

  const placeMenu = (menu) => {
    const popover = menu.querySelector('.so-actions-popover');
    const trigger = menu.querySelector('summary');
    if (!popover || !trigger) return;
    const rect = trigger.getBoundingClientRect();
    const width = Math.max(popover.offsetWidth, 168);
    const height = popover.offsetHeight;
    const left = Math.max(8, Math.min(window.innerWidth - width - 8, rect.right - width));
    const top = rect.bottom + height + 8 <= window.innerHeight ? rect.bottom + 5 : Math.max(8, rect.top - height - 5);
    popover.style.left = `${left}px`;
    popover.style.top = `${top}px`;
  };

  root.addEventListener('toggle', (event) => {
    const menu = event.target;
    if (!menu.classList?.contains('so-actions-menu') || !menu.open) return;
    root.querySelectorAll('.so-actions-menu[open]').forEach((other) => {
      if (other !== menu) other.removeAttribute('open');
    });
    placeMenu(menu);
  }, true);

  // Don't leave a floating menu in the wrong position while scrolling.
  document.addEventListener('scroll', () => {
    root.querySelectorAll('.so-actions-menu[open]').forEach((menu) => menu.removeAttribute('open'));
  }, true);
  window.addEventListener('resize', () => {
    root.querySelectorAll('.so-actions-menu[open]').forEach((menu) => menu.removeAttribute('open'));
  });

  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') root.querySelectorAll('.so-actions-menu[open]').forEach((menu) => menu.removeAttribute('open'));
  });
})();
