/* ShopHop shared required-field label styling.
 * No input names, values, validation rules, form submissions, or backend fields are changed.
 */
(function () {
  'use strict';
  function decorateLabel(label) {
    if (!label || label.classList.contains('sr-only') || label.querySelector('.sh-required-star')) return;
    let textTarget = label.querySelector('.sh-label') || label.querySelector('.form-label') || label;
    // Keep required asterisk with the actual field title, not at the end of a label's input wrapper.
    if (textTarget === label) {
      const candidate = Array.from(label.children).find(child => ['SPAN','STRONG','B'].includes(child.tagName) && child.textContent.trim());
      if (candidate) textTarget = candidate;
    }
    const walker = document.createTreeWalker(textTarget, NodeFilter.SHOW_TEXT);
    let textNode;
    while ((textNode = walker.nextNode())) {
      if (!/\*\s*$/.test(textNode.textContent)) continue;
      const matched = textNode.textContent.match(/^(.*?)(\s*)\*(\s*)$/s);
      if (!matched) continue;
      textNode.textContent = matched[1] + matched[2];
      const mark = document.createElement('span');
      mark.className = 'sh-required-star';
      mark.setAttribute('aria-hidden', 'true');
      mark.textContent = '*';
      textNode.after(mark);
      if (matched[3]) mark.after(document.createTextNode(matched[3]));
      return;
    }
    // If it already has a styled or nested literal star, do not add another.
    if (textTarget.textContent.includes('*')) return;
    const mark = document.createElement('span');
    mark.className = 'sh-required-star';
    mark.setAttribute('aria-hidden', 'true');
    mark.textContent = '*';
    textTarget.append(document.createTextNode(' '), mark);
  }
  function applyRequiredLabels(root) {
    root.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
      if (field.type === 'hidden' || field.type === 'checkbox' || field.type === 'radio') return;
      let label = field.closest('label');
      if (!label && field.id) {
        try { label = root.querySelector(`label[for="${CSS.escape(field.id)}"]`) || document.querySelector(`label[for="${CSS.escape(field.id)}"]`); }
        catch (_) { label = [...document.querySelectorAll('label[for]')].find(candidate => candidate.htmlFor === field.id); }
      }
      if (label) decorateLabel(label);
      field.setAttribute('aria-required', 'true');
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => applyRequiredLabels(document));
  else applyRequiredLabels(document);
})();
