/* Native dialog handling scoped to Admin operational tables. Preserve server-side validation. */
(function () {
  'use strict';
  document.addEventListener('click', function (event) {
    var opener = event.target.closest('[data-admin-dialog-open]');
    if (opener) {
      var dialog = document.getElementById(opener.getAttribute('data-admin-dialog-open'));
      if (dialog && typeof dialog.showModal === 'function') {
        event.preventDefault();
        if (!dialog.open) dialog.showModal();
      }
      return;
    }
    var closer = event.target.closest('[data-admin-dialog-close]');
    if (closer) {
      var modal = closer.closest('dialog');
      if (modal && modal.open) { event.preventDefault(); modal.close(); }
    }
  });
  document.addEventListener('click', function (event) {
    if (event.target.matches('dialog.admin-dialog') && event.target.open) {
      var rect = event.target.getBoundingClientRect();
      if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) {
        event.target.close();
      }
    }
  });
})();
