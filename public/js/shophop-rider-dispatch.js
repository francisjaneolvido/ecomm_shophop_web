(() => {
    const form = document.getElementById('dispatchAssignForm');
    if (!form) return;
    const dialog = document.getElementById('dispatchAssignModal');
    const select = document.getElementById('dispatchRiderSelect');
    const feedback = document.getElementById('dispatchFeedback');
    const submit = document.getElementById('dispatchSubmit');
    const capacityNotice = document.getElementById('dispatchCapacityNotice');
    const capacityDialog = document.getElementById('dispatchCapacityModal');
    const capacityForm = document.getElementById('dispatchCapacityForm');
    const capacityRider = document.getElementById('dispatchCapacityRider');
    const capacityFeedback = document.getElementById('dispatchCapacityFeedback');
    const scheduleLater = document.getElementById('dispatchScheduleLater');
    const riders = new Map(JSON.parse(document.getElementById('dispatchRiderData').textContent).map(r => [Number(r.id), r]));
    let selected = [];
    let kind = 'pickup';
    let saving = false;
    let savingCapacity = false;

    document.querySelectorAll('[data-dialog-close]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
    const parcelFor = row => JSON.parse(row.dataset.parcel);
    function riderState() {
        const rider = riders.get(Number(select.value));
        capacityNotice.hidden = !rider || rider.capacity_configured;
        submit.disabled = saving || !rider;
    }
    function scheduleState() {
        const later = scheduleLater.checked;
        document.getElementById('dispatchScheduleFields').hidden = !later;
        form.elements.scheduled_start.required = later;
        form.elements.scheduled_end.required = later;
        submit.textContent = later ? 'Schedule assignment' : 'Assign and dispatch';
    }
    select.addEventListener('change', riderState);
    scheduleLater.addEventListener('change', scheduleState);

    function measurement(label, key, parcel, step) {
        const field = document.createElement('label'); field.className = 'sh-field';
        const title = document.createElement('span'); title.className = 'sh-label'; title.textContent = label;
        const input = document.createElement('input'); input.className = 'sh-input'; input.type = 'number';
        input.required = true; input.step = step; input.min = step; input.max = '999999';
        input.dataset.measurement = key; input.dataset.order = String(parcel.order_id);
        input.setAttribute('aria-label', label + ' for ' + parcel.tracking);
        if (key === 'weight_kg' && Number(parcel.weight_kg) > 0) { input.value = parcel.weight_kg; input.min = parcel.weight_kg; }
        field.append(title, input); return field;
    }
    function openAssignment(rows, type) {
        selected = rows.map(parcelFor); kind = type; feedback.textContent = '';
        form.reset(); scheduleState();
        document.getElementById('dispatchAssignTitle').textContent = type === 'pickup' ? 'Assign pickup rider' : 'Assign delivery rider';
        document.getElementById('dispatchSelectionLabel').textContent = selected.length === 1 ? selected[0].tracking : selected.length + ' selected parcels';
        const common = selected[0].riders.filter(rider => selected.every(parcel => parcel.riders.some(r => Number(r.id) === Number(rider.id))));
        select.replaceChildren(new Option('Choose rider', ''));
        common.forEach(rider => select.add(new Option(rider.name, String(rider.id))));
        if (common.length === 1) select.value = String(common[0].id);
        if (!common.length) feedback.textContent = 'No single available rider covers every selected parcel. Choose parcels in the same rider coverage.';
        const measurements = document.getElementById('dispatchParcelMeasurements'); measurements.replaceChildren();
        selected.forEach(parcel => {
            const group = document.createElement('div'); group.className = 'rounded-lg border border-gray-border p-3 space-y-2';
            const title = document.createElement('p'); title.className = 'text-sm font-semibold'; title.textContent = parcel.tracking;
            const fields = document.createElement('div'); fields.className = 'grid gap-3 sm:grid-cols-2';
            fields.append(measurement('Weight (kg)', 'weight_kg', parcel, '0.001'), measurement('Volume (m3)', 'volume_m3', parcel, '0.000001'));
            const help = document.createElement('p'); help.className = 'text-xs text-slate-500'; help.textContent = 'Use measured values. Volume = length x width x height in metres.';
            group.append(title, fields, help); measurements.append(group);
        });
        riderState(); dialog.showModal();
    }
    document.querySelectorAll('[data-dispatch-row]').forEach(row => {
        row.querySelector('[data-dispatch-open]').addEventListener('click', () => openAssignment([row], row.dataset.kind));
        row.querySelector('[data-select-parcel]').addEventListener('change', () => {
            const count = document.querySelectorAll('[data-dispatch-row][data-kind="' + row.dataset.kind + '"] [data-select-parcel]:checked').length;
            const button = document.querySelector('[data-assign-selected="' + row.dataset.kind + '"]');
            button.disabled = !count; button.textContent = 'Assign selected (' + count + ')';
        });
    });
    document.querySelectorAll('[data-assign-selected]').forEach(button => button.addEventListener('click', () => {
        const rows = [...document.querySelectorAll('[data-dispatch-row][data-kind="' + button.dataset.assignSelected + '"]')].filter(row => row.querySelector('[data-select-parcel]').checked);
        if (rows.length) openAssignment(rows, button.dataset.assignSelected);
    }));

    function fillCapacity() {
        const rider = riders.get(Number(capacityRider.value));
        for (const name of ['max_weight_kg','max_volume_m3','max_stops','max_duration_minutes','max_parcels']) capacityForm.elements[name].value = rider?.limits?.[name] ?? '';
    }
    capacityRider.addEventListener('change', fillCapacity);
    function openCapacity(riderId = '') {
        capacityFeedback.textContent = ''; capacityRider.value = String(riderId); fillCapacity(); capacityDialog.showModal();
    }
    document.querySelectorAll('[data-capacity-open]').forEach(button => button.addEventListener('click', () => openCapacity()));
    document.getElementById('dispatchSetupCapacity').addEventListener('click', () => openCapacity(select.value));

    async function post(url, body, token) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 30000);
        try {
            const response = await fetch(url, {method: 'POST', credentials: 'same-origin', signal: controller.signal,
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token}, body: JSON.stringify(body)});
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                if ([401,403,419].includes(response.status)) throw new Error('Your session or access has changed. Sign in again and retry.');
                if (response.status >= 500) throw new Error('Assignment is temporarily unavailable. Refresh the page to check the result before retrying.');
                throw new Error(Object.values(payload.errors ?? {}).flat().join(' ') || payload.message || 'Unable to save. Please review the details.');
            }
            if (payload.success !== true) throw new Error('Unexpected response. Refresh this page to check the result.');
            return payload;
        } finally { clearTimeout(timeout); }
    }
    capacityForm.addEventListener('submit', async event => {
        event.preventDefault(); if (savingCapacity || !capacityForm.reportValidity()) return;
        savingCapacity = true; const button = capacityForm.querySelector('button[type=submit]'); button.disabled = true;
        capacityFeedback.textContent = 'Saving vehicle limits...';
        try {
            const limits = {};
            for (const name of ['max_weight_kg','max_volume_m3','max_stops','max_duration_minutes','max_parcels']) limits[name] = capacityForm.elements[name].value === '' ? null : Number(capacityForm.elements[name].value);
            const id = Number(capacityRider.value);
            await post(capacityForm.dataset.url.replace('RIDER_ID', String(id)), limits, capacityForm.elements._token.value);
            const rider = riders.get(id); rider.limits = limits; rider.capacity_configured = true;
            capacityDialog.close(); riderState();
        } catch (error) { capacityFeedback.textContent = error.name === 'AbortError' ? 'The request timed out. Check the saved settings before retrying.' : error.message; }
        finally { savingCapacity = false; button.disabled = false; }
    });
    form.addEventListener('submit', async event => {
        event.preventDefault(); if (saving || !form.reportValidity()) return;
        const rider = riders.get(Number(select.value)); if (!rider) { riderState(); return; }
        saving = true; riderState(); feedback.textContent = 'Checking coverage and vehicle limits...';
        try {
            const parcels = selected.map(parcel => {
                const inputs = [...form.querySelectorAll('[data-order="' + parcel.order_id + '"]')];
                return {order_id: Number(parcel.order_id), ...Object.fromEntries(inputs.map(input => [input.dataset.measurement, Number(input.value)]))};
            });
            const data = {type: kind, rider_id: Number(select.value), dispatch_now: !scheduleLater.checked,
                estimated_minutes: Number(form.elements.estimated_minutes.value), parcels};
            if (scheduleLater.checked) {
                data.scheduled_start = new Date(form.elements.scheduled_start.value).toISOString();
                data.scheduled_end = new Date(form.elements.scheduled_end.value).toISOString();
            }
            const payload = await post(form.action, data, form.elements._token.value);
            feedback.textContent = payload.message; window.location.reload();
        } catch (error) { feedback.textContent = error.name === 'AbortError' ? 'The request timed out. Refresh to check whether the parcels were assigned before retrying.' : (error instanceof RangeError ? 'Enter a valid route start and end time.' : error.message); }
        finally { saving = false; riderState(); }
    });
})();
