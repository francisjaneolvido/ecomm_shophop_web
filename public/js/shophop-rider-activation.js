(() => {
    'use strict';
    document.querySelectorAll('[data-rider-activation]').forEach((form) => {
        const search = form.querySelector('[data-coverage-search]');
        const options = Array.from(form.querySelectorAll('[data-coverage-option]'));
        const button = form.querySelector('[data-activate-button]');
        const status = form.querySelector('[data-coverage-status]');
        function update() {
            const selected = options.map((option) => option.querySelector('input[name="coverage[]"]')).filter((input) => input.checked);
            const full = selected.some((input) => input.dataset.capacityBlocked === '1');
            const blocked = form.dataset.serverBlocked === '1';
            button.disabled = blocked || selected.length === 0 || full;
            status.textContent = blocked ? 'Resolve the activation requirements shown above first.'
                : full ? 'A selected barangay is full. Choose available coverage or ask Main Hub for an individual capacity override.'
                : selected.length === 0 ? 'Choose at least one official barangay.'
                : selected.length + ' official barangay(s) selected. Confirm your authorization below to activate.';
        }
        form.addEventListener('change', update);
        search.addEventListener('input', () => {
            const term = search.value.trim().toLocaleLowerCase();
            options.forEach((option) => { option.hidden = !option.dataset.label.includes(term); });
        });
        update();
    });
})();
