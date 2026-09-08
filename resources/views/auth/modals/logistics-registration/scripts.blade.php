@push('scripts')
<script>
    (function () {

        const modal = document.getElementById('logistics-registration-modal');

        if (!modal) {
            return;
        }

        const dialog = document.getElementById('logistics-registration-dialog');
        const filePreviewModal = document.getElementById('logistics-file-preview-modal');
        const filePreviewFullImage = document.getElementById('logistics-file-preview-full-image');
        const filePreviewPdfFrame = document.getElementById('logistics-file-preview-pdf-frame');
        const filePreviewName = document.getElementById('logistics-file-preview-name');

        let lastFocusedElement = null;
        let addressLoaded = false;
        let closeTimeoutId = null;
        const filePreviewUrls = {};
        let activeFilePreviewKey = null;


        /*
        |--------------------------------------------------------------------------
        | MODAL OPEN / CLOSE
        |--------------------------------------------------------------------------
        */

        function openLogisticsRegistrationModal() {

            lastFocusedElement = document.activeElement;

            if (closeTimeoutId) {
                window.clearTimeout(closeTimeoutId);
                closeTimeoutId = null;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {

                    modal.classList.remove('opacity-0');
                    modal.classList.add('opacity-100');

                    if (dialog) {
                        dialog.classList.remove('opacity-0', 'scale-95', 'translate-y-3');
                        dialog.classList.add('opacity-100', 'scale-100', 'translate-y-0');
                    }

                });
            });

            if (!addressLoaded) {
                addressLoaded = true;
                initRegions();
            }

            window.setTimeout(function () {
                const firstField = document.getElementById('logistics_agreement_rep_name');
                if (firstField) firstField.focus();
            }, 50);

        }

        function closeLogisticsRegistrationModal() {

            closeLogisticsFilePreview();

            modal.classList.add('opacity-0');
            modal.classList.remove('opacity-100');

            if (dialog) {
                dialog.classList.add('opacity-0', 'scale-95', 'translate-y-3');
                dialog.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            }

            document.body.classList.remove('overflow-hidden');

            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }

            closeTimeoutId = window.setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                closeTimeoutId = null;
            }, 300);

        }

        // Opened when account-type-modal (or anything else) dispatches this event
        // with { type: 'logistics' }. This is the piece that was missing before —
        // the event was already being fired, nothing was listening for it.
        document.addEventListener('shophop:open-registration-modal', function (event) {
            if (event.detail && event.detail.type === 'logistics') {
                openLogisticsRegistrationModal();
            }
        });

        document.addEventListener('click', function (event) {

            const filePreviewCloseTrigger = event.target.closest('[data-logistics-file-preview-close]');

            if (filePreviewCloseTrigger && filePreviewModal && filePreviewModal.contains(filePreviewCloseTrigger)) {
                event.preventDefault();
                closeLogisticsFilePreview();
                return;
            }

            const backTrigger = event.target.closest('[data-logistics-registration-modal-back]');

            if (backTrigger && modal.contains(backTrigger)) {
                event.preventDefault();
                closeLogisticsRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-account-type-modal'));
                return;
            }

            const signInTrigger = event.target.closest('[data-logistics-registration-modal-signin]');

            if (signInTrigger && modal.contains(signInTrigger)) {
                event.preventDefault();
                closeLogisticsRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-login-modal'));
                return;
            }

            const closeTrigger = event.target.closest('[data-logistics-registration-modal-close]');

            if (closeTrigger && modal.contains(closeTrigger)) {
                event.preventDefault();
                closeLogisticsRegistrationModal();
            }

        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;

            if (filePreviewModal && filePreviewModal.getAttribute('aria-hidden') === 'false') {
                closeLogisticsFilePreview();
                return;
            }

            if (modal.getAttribute('aria-hidden') === 'false') {
                closeLogisticsRegistrationModal();
            }
        });


        /*
        |--------------------------------------------------------------------------
        | PSGC (Philippine Standard Geographic Code) API
        | Same approach as the old full-page logistics wizard: regions are
        | hardcoded (stable, official codes), provinces/cities/barangays are
        | fetched live from https://psgc.gitlab.io/api.
        |--------------------------------------------------------------------------
        */

        const PSGC_BASE = 'https://psgc.gitlab.io/api';

        const PH_REGIONS = [
            { code: '010000000', name: 'Region I (Ilocos Region)' },
            { code: '020000000', name: 'Region II (Cagayan Valley)' },
            { code: '030000000', name: 'Region III (Central Luzon)' },
            { code: '040000000', name: 'Region IV-A (CALABARZON)' },
            { code: '170000000', name: 'MIMAROPA Region' },
            { code: '050000000', name: 'Region V (Bicol Region)' },
            { code: '060000000', name: 'Region VI (Western Visayas)' },
            { code: '070000000', name: 'Region VII (Central Visayas)' },
            { code: '080000000', name: 'Region VIII (Eastern Visayas)' },
            { code: '090000000', name: 'Region IX (Zamboanga Peninsula)' },
            { code: '100000000', name: 'Region X (Northern Mindanao)' },
            { code: '110000000', name: 'Region XI (Davao Region)' },
            { code: '120000000', name: 'Region XII (SOCCSKSARGEN)' },
            { code: '130000000', name: 'National Capital Region (NCR)' },
            { code: '140000000', name: 'Cordillera Administrative Region (CAR)' },
            { code: '150000000', name: 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)' },
            { code: '160000000', name: 'Region XIII (Caraga)' },
        ];

        const psgcCache = {
            allProvinces: null,
            citiesByProvince: {},
            allCitiesMunicipalities: null,
            barangaysByCity: {},
        };

        async function psgcGet(path) {
            const res = await fetch(PSGC_BASE + path);
            if (!res.ok) throw new Error('PSGC request failed: ' + path);
            const json = await res.json();
            return Array.isArray(json) ? json : (json.data || []);
        }

        function sortByName(list) {
            return [...list].sort(function (a, b) { return a.name.localeCompare(b.name, 'en'); });
        }

        function loadRegions() {
            return Promise.resolve(sortByName(PH_REGIONS));
        }

        async function loadAllProvinces() {
            if (psgcCache.allProvinces) return psgcCache.allProvinces;
            psgcCache.allProvinces = sortByName(await psgcGet('/provinces/'));
            return psgcCache.allProvinces;
        }

        async function loadProvinces(regionCode) {
            const all = await loadAllProvinces();
            return all.filter(function (p) { return p.regionCode === regionCode; });
        }

        async function loadCitiesByProvince(provinceCode) {
            if (psgcCache.citiesByProvince[provinceCode]) return psgcCache.citiesByProvince[provinceCode];
            const cities = sortByName(await psgcGet('/provinces/' + encodeURIComponent(provinceCode) + '/cities-municipalities/'));
            psgcCache.citiesByProvince[provinceCode] = cities;
            return cities;
        }

        async function loadCitiesByRegion(regionCode) {
            if (!psgcCache.allCitiesMunicipalities) {
                psgcCache.allCitiesMunicipalities = await psgcGet('/cities-municipalities/');
            }
            const cities = psgcCache.allCitiesMunicipalities.filter(function (c) {
                const cRegion = c.regionCode || c.region_code;
                const cProvince = c.provinceCode || c.province_code;
                return cRegion === regionCode && !cProvince;
            });
            return sortByName(cities);
        }

        async function loadBarangays(cityCode) {
            if (psgcCache.barangaysByCity[cityCode]) return psgcCache.barangaysByCity[cityCode];
            const brgys = sortByName(await psgcGet('/cities-municipalities/' + encodeURIComponent(cityCode) + '/barangays/'));
            psgcCache.barangaysByCity[cityCode] = brgys;
            return brgys;
        }

        function fillOptionSelect(selectEl, items, placeholder) {
            selectEl.innerHTML = '';
            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = placeholder;
            selectEl.appendChild(ph);
            items.forEach(function (item) {
                const opt = document.createElement('option');
                opt.value = item.name;
                opt.dataset.code = item.code;
                opt.textContent = item.name;
                selectEl.appendChild(opt);
            });
        }

        const regionSelect = document.getElementById('logistics_region');
        const provinceSelect = document.getElementById('logistics_province');
        const municipalitySelect = document.getElementById('logistics_municipality');
        const barangaySelect = document.getElementById('logistics_barangay');
        const addressStatusEl = document.getElementById('logistics-address-status');

        const addressState = { regionCode: '', regionName: '', provinceCode: '', provinceName: '', isNcrLike: false };

        function setAddressStatus(message, isError) {
            if (!addressStatusEl) return;
            if (!message) {
                addressStatusEl.classList.add('hidden');
                return;
            }
            addressStatusEl.textContent = message;
            addressStatusEl.classList.remove('hidden');
            addressStatusEl.classList.toggle('text-red-500', !!isError);
            addressStatusEl.classList.toggle('text-teal-dark', !isError);
        }

        async function initRegions() {
            if (!regionSelect) return;
            try {
                const regions = await loadRegions();
                fillOptionSelect(regionSelect, regions, 'Select region');
            } catch (e) {
                regionSelect.innerHTML = '<option value="">Couldn\'t load regions — check your connection</option>';
            }
        }

        if (regionSelect) {
            regionSelect.addEventListener('change', async function () {
                const opt = regionSelect.selectedOptions[0];
                addressState.regionCode = (opt && opt.dataset.code) || '';
                addressState.regionName = regionSelect.value;

                provinceSelect.innerHTML = '<option value="">Loading provinces…</option>';
                provinceSelect.disabled = true;
                municipalitySelect.innerHTML = '<option value="">Select province first</option>';
                municipalitySelect.disabled = true;
                barangaySelect.innerHTML = '<option value="">Select city/municipality first</option>';
                barangaySelect.disabled = true;

                if (!addressState.regionCode) return;

                try {
                    setAddressStatus('Loading provinces…');
                    const provinces = await loadProvinces(addressState.regionCode);

                    if (provinces.length === 0) {
                        addressState.isNcrLike = true;
                        addressState.provinceCode = '';
                        addressState.provinceName = addressState.regionName;
                        provinceSelect.innerHTML = '<option value="' + addressState.regionName + '">' + addressState.regionName + ' (no provinces)</option>';
                        provinceSelect.value = addressState.regionName;
                        provinceSelect.disabled = true;

                        municipalitySelect.innerHTML = '<option value="">Loading cities/municipalities…</option>';
                        const cities = await loadCitiesByRegion(addressState.regionCode);
                        fillOptionSelect(municipalitySelect, cities, 'Select city/municipality');
                        municipalitySelect.disabled = false;
                    } else {
                        addressState.isNcrLike = false;
                        fillOptionSelect(provinceSelect, provinces, 'Select province');
                        provinceSelect.disabled = false;
                    }
                    setAddressStatus('');
                } catch (e) {
                    provinceSelect.innerHTML = '<option value="">Couldn\'t load provinces</option>';
                    setAddressStatus('Unable to load address dropdowns. Please check your connection.', true);
                }
            });
        }

        if (provinceSelect) {
            provinceSelect.addEventListener('change', async function () {
                const opt = provinceSelect.selectedOptions[0];
                addressState.provinceCode = (opt && opt.dataset.code) || '';
                addressState.provinceName = provinceSelect.value;

                municipalitySelect.innerHTML = '<option value="">Loading cities/municipalities…</option>';
                municipalitySelect.disabled = true;
                barangaySelect.innerHTML = '<option value="">Select city/municipality first</option>';
                barangaySelect.disabled = true;

                if (!addressState.provinceCode) return;
                try {
                    const cities = await loadCitiesByProvince(addressState.provinceCode);
                    fillOptionSelect(municipalitySelect, cities, 'Select city/municipality');
                    municipalitySelect.disabled = false;
                } catch (e) {
                    municipalitySelect.innerHTML = '<option value="">Couldn\'t load cities</option>';
                }
            });
        }

        if (municipalitySelect) {
            municipalitySelect.addEventListener('change', async function () {
                const opt = municipalitySelect.selectedOptions[0];
                const cityCode = (opt && opt.dataset.code) || '';

                barangaySelect.innerHTML = '<option value="">Loading barangays…</option>';
                barangaySelect.disabled = true;

                if (!cityCode) return;
                try {
                    const brgys = await loadBarangays(cityCode);
                    fillOptionSelect(barangaySelect, brgys, 'Select barangay');
                    barangaySelect.disabled = false;
                } catch (e) {
                    barangaySelect.innerHTML = '<option value="">Couldn\'t load barangays</option>';
                }
            });
        }


        /*
        |--------------------------------------------------------------------------
        | COVERAGE AREAS BUILDER (Step 6)
        |--------------------------------------------------------------------------
        */

        const coverageListEl = document.getElementById('logistics-coverage-list');
        const coverageEmptyEl = document.getElementById('logistics-coverage-empty');
        const coverageAddSelect = document.getElementById('logistics-coverage-add-select');
        const coverageAddBtn = document.getElementById('logistics-coverage-add-btn');
        const coverageErrorEl = document.getElementById('logistics-coverage-error');
        const coverageSuggestNote = document.getElementById('logistics-coverage-suggestion-note');
        const coverageRegionNameEl = document.getElementById('logistics-coverage-region-name');

        const coverageChips = new Map();
        let coverageAutoSuggested = false;
        let coverageOptionsLoaded = false;

        async function initCoverageAddOptions() {
            if (!coverageAddSelect) return;
            try {
                const regions = await loadRegions();
                const provinces = await loadAllProvinces();
                const options = provinces.map(function (p) {
                    return { value: p.name, code: p.code, type: 'province', label: p.name };
                });

                const noProvinceRegion = regions.find(function (r) { return /national capital region|\bncr\b/i.test(r.name); });
                if (noProvinceRegion) {
                    options.push({ value: noProvinceRegion.name, code: noProvinceRegion.code, type: 'region', label: noProvinceRegion.name + ' (no provinces)' });
                }
                options.sort(function (a, b) { return a.label.localeCompare(b.label, 'en'); });

                coverageAddSelect.innerHTML = '<option value="">+ Choose a province to add…</option>';
                options.forEach(function (o) {
                    const opt = document.createElement('option');
                    opt.value = o.value;
                    opt.dataset.code = o.code;
                    opt.dataset.type = o.type;
                    opt.textContent = o.label;
                    coverageAddSelect.appendChild(opt);
                });
            } catch (e) {
                coverageAddSelect.innerHTML = '<option value="">Couldn\'t load provinces</option>';
            }
        }

        async function loadCoverageCities(key) {
            const chip = coverageChips.get(key);
            if (!chip) return;
            try {
                const cities = chip.type === 'region' ? await loadCitiesByRegion(chip.code) : await loadCitiesByProvince(chip.code);
                cities.forEach(function (c) { chip.cities.set(c.code, { name: c.name, checked: false }); });
                chip.citiesLoaded = true;
            } catch (e) {
                chip.citiesLoaded = 'error';
            }
            renderCoverageList();
        }

        function addCoverageChip(name, code, type) {
            const key = type + ':' + (code || name);
            if (coverageChips.has(key)) return;
            coverageChips.set(key, { key: key, name: name, code: code, type: type, cities: new Map(), citiesLoaded: false, selectAll: false });
            renderCoverageList();
            loadCoverageCities(key);
        }

        function removeCoverageChip(key) {
            coverageChips.delete(key);
            renderCoverageList();
        }

        function renderCoverageList() {
            if (!coverageListEl) return;
            coverageListEl.querySelectorAll('[data-coverage-chip]').forEach(function (el) { el.remove(); });
            if (coverageEmptyEl) coverageEmptyEl.classList.toggle('hidden', coverageChips.size > 0);

            coverageChips.forEach(function (chip) {
                const wrap = document.createElement('div');
                wrap.className = 'border border-gray-border/70 rounded-xl p-4 bg-white hover:border-teal/30 transition-colors';
                wrap.dataset.coverageChip = chip.key;

                let citiesHtml;
                if (!chip.citiesLoaded) {
                    citiesHtml = '<p class="text-[11px] text-navy/40 col-span-full">Loading cities/municipalities…</p>';
                } else if (chip.citiesLoaded === 'error') {
                    citiesHtml = '<p class="text-[11px] text-red-500 col-span-full">Couldn\'t load cities for this province.</p>';
                } else {
                    citiesHtml = [...chip.cities.entries()].map(function ([code, c]) {
                        return '<label class="flex items-center gap-1.5 text-[11px] text-navy/70 py-1.5 px-1 -mx-1 rounded-lg hover:bg-gray-bg/60 active:bg-gray-bg transition cursor-pointer break-words">' +
                            '<input type="checkbox" data-city-code="' + code + '" data-chip-key="' + chip.key + '" class="w-4 h-4 shrink-0 accent-teal" ' + (c.checked ? 'checked' : '') + '>' +
                            '<span>' + c.name + '</span></label>';
                    }).join('');
                }

                wrap.innerHTML =
                    '<div class="flex items-start justify-between gap-2 mb-2">' +
                        '<span class="inline-flex items-center gap-1.5 text-sm font-bold text-navy break-words">' +
                            '<span class="w-1.5 h-1.5 rounded-full bg-teal shrink-0"></span>' + chip.name +
                        '</span>' +
                        '<button type="button" data-coverage-remove="' + chip.key + '" class="shrink-0 text-navy/40 hover:text-red-500 text-xs font-semibold px-2 py-1 -m-1 rounded-lg hover:bg-red-50 active:bg-red-50 transition">Remove</button>' +
                    '</div>' +
                    '<div class="flex items-center justify-between gap-2 flex-wrap mb-1">' +
                        '<span class="text-[11px] text-navy/50">Cities / municipalities covered</span>' +
                        '<label class="flex items-center gap-1.5 text-[11px] font-semibold text-teal-dark cursor-pointer shrink-0 py-1 px-1.5 -mx-1.5 rounded-lg hover:bg-teal-light/50 active:bg-teal-light/50 transition">' +
                            '<input type="checkbox" data-select-all-key="' + chip.key + '" class="w-4 h-4 accent-teal" ' + (chip.selectAll ? 'checked' : '') + '>' +
                            'Select all' +
                        '</label>' +
                    '</div>' +
                    '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-2 gap-y-0.5 max-h-44 overflow-y-auto pr-1">' + citiesHtml + '</div>';

                coverageListEl.appendChild(wrap);
            });
        }

        if (coverageListEl) {
            coverageListEl.addEventListener('click', function (e) {
                const removeBtn = e.target.closest('[data-coverage-remove]');
                if (removeBtn) removeCoverageChip(removeBtn.dataset.coverageRemove);
            });

            coverageListEl.addEventListener('change', function (e) {
                const cityCb = e.target.closest('[data-city-code]');
                if (cityCb) {
                    const chip = coverageChips.get(cityCb.dataset.chipKey);
                    const city = chip && chip.cities.get(cityCb.dataset.cityCode);
                    if (city) city.checked = cityCb.checked;
                    if (chip) chip.selectAll = [...chip.cities.values()].every(function (c) { return c.checked; });
                    renderCoverageList();
                    return;
                }
                const selectAllCb = e.target.closest('[data-select-all-key]');
                if (selectAllCb) {
                    const chip = coverageChips.get(selectAllCb.dataset.selectAllKey);
                    if (chip) {
                        chip.selectAll = selectAllCb.checked;
                        chip.cities.forEach(function (c) { c.checked = selectAllCb.checked; });
                    }
                    renderCoverageList();
                }
            });
        }

        if (coverageAddBtn) {
            coverageAddBtn.addEventListener('click', function () {
                const opt = coverageAddSelect.selectedOptions[0];
                if (!opt || !opt.value) return;
                addCoverageChip(opt.value, opt.dataset.code, opt.dataset.type);
                coverageAddSelect.value = '';
            });
        }

        function autoSuggestCoverage() {
            if (coverageAutoSuggested || !addressState.regionName) return;
            coverageAutoSuggested = true;
            if (coverageSuggestNote) coverageSuggestNote.classList.remove('hidden');
            if (coverageRegionNameEl) coverageRegionNameEl.textContent = addressState.regionName;

            if (addressState.isNcrLike) {
                addCoverageChip(addressState.regionName, addressState.regionCode, 'region');
            } else if (addressState.provinceName) {
                addCoverageChip(addressState.provinceName, addressState.provinceCode, 'province');
            }
        }

        function serializeCoverage() {
            modal.querySelectorAll('[data-coverage-hidden]').forEach(function (el) { el.remove(); });
            const form = document.getElementById('logistics-register-form');
            coverageChips.forEach(function (chip) {
                const provinceInput = document.createElement('input');
                provinceInput.type = 'hidden';
                provinceInput.name = 'coverage_areas[]';
                provinceInput.value = chip.name;
                provinceInput.dataset.coverageHidden = '1';
                form.appendChild(provinceInput);

                const checkedCities = [...chip.cities.values()].filter(function (c) { return c.checked; }).map(function (c) { return c.name; });
                const citiesValue = (chip.citiesLoaded === true && chip.selectAll) ? 'ALL' : checkedCities.join('|');

                const cityInput = document.createElement('input');
                cityInput.type = 'hidden';
                cityInput.name = 'coverage_cities[' + chip.name + ']';
                cityInput.value = citiesValue;
                cityInput.dataset.coverageHidden = '1';
                form.appendChild(cityInput);
            });
        }


        /*
        |--------------------------------------------------------------------------
        | ID AUTO-FILL — TEMPLATE / STUB, same graceful-fallback pattern as
        | before. Wire this up to a real ID-recognition service later.
        |--------------------------------------------------------------------------
        */

        const repIdFileInput = document.getElementById('logistics_rep_valid_id');
        const repIdFileNameEl = document.getElementById('logistics-rep-valid-id-name');
        const idDetectStatus = document.getElementById('logistics-id-detect-status');

        async function detectRepresentativeId(file) {
            if (!idDetectStatus) return;
            idDetectStatus.textContent = 'Reading your ID…';

            try {
                const formData = new FormData();
                formData.append('id_file', file);
                const csrfInput = document.querySelector('#logistics-register-form input[name="_token"]');

                // TODO: point this at the real detection endpoint once it exists.
                const res = await fetch('/logistics/detect-id', {
                    method: 'POST',
                    body: formData,
                    headers: csrfInput ? { 'X-CSRF-TOKEN': csrfInput.value } : {},
                });
                if (!res.ok) throw new Error('Detection endpoint not available yet');
                const data = await res.json();

                if (data.first_name) document.getElementById('logistics_rep_first_name').value = data.first_name;
                if (data.last_name) document.getElementById('logistics_rep_last_name').value = data.last_name;
                if (data.id_number) document.getElementById('logistics_rep_id_number').value = data.id_number;

                idDetectStatus.textContent = 'Details auto-filled from your ID — please double-check them below.';
            } catch (e) {
                // Expected for now since the backend endpoint isn't built yet.
                idDetectStatus.textContent = 'Upload received. (Auto-fill from ID scanning is still in progress — please fill in the fields below manually for now.)';
            }
        }

        if (repIdFileInput) {
            repIdFileInput.addEventListener('change', function () {
                if (this.files.length > 0 && repIdFileNameEl) {
                    repIdFileNameEl.textContent = this.files[0].name;
                    repIdFileNameEl.classList.remove('hidden');
                }
                if (this.files.length) detectRepresentativeId(this.files[0]);
            });
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY EMAIL / OTP / RESEND — UX-only for now, same as before.
        |--------------------------------------------------------------------------
        */

        const verifyEmailDisplay = document.getElementById('logistics-verify-email-display');
        const otpEmailDisplay = document.getElementById('logistics-otp-email-display');
        const otpInputs = Array.from(document.querySelectorAll('#logistics-otp-boxes [data-otp-digit]'));
        const otpHidden = document.getElementById('logistics-otp-hidden');
        const otpErrorEl = document.getElementById('logistics-otp-error');
        const resendBtn = document.getElementById('logistics-resend-code');
        const resendLabel = document.getElementById('logistics-resend-label');
        const resendTimerEl = document.getElementById('logistics-resend-timer');

        function currentAccountEmail() {
            const emailField = document.getElementById('logistics_email');
            return emailField ? emailField.value.trim() : '';
        }

        // TODO: point this at the real send-verification-code endpoint once it
        // exists. For now it just updates the UI and starts the resend countdown.
        function sendVerificationCode() {
            const email = currentAccountEmail() || 'your email';
            if (verifyEmailDisplay) verifyEmailDisplay.textContent = email;
            if (otpEmailDisplay) otpEmailDisplay.textContent = email;
            startResendCountdown();
        }

        let resendInterval = null;

        function startResendCountdown() {
            if (!resendBtn || !resendLabel || !resendTimerEl) return;

            let secondsLeft = 30;

            resendBtn.disabled = true;
            resendBtn.classList.add('text-navy/30');
            resendBtn.classList.remove('text-teal-dark', 'hover:text-navy', 'cursor-pointer');
            resendLabel.textContent = 'Resend in';
            resendTimerEl.classList.remove('hidden');

            if (resendInterval) clearInterval(resendInterval);

            function render() {
                const mins = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
                const secs = String(secondsLeft % 60).padStart(2, '0');
                resendTimerEl.textContent = mins + ':' + secs;
            }

            render();

            resendInterval = setInterval(function () {
                secondsLeft--;
                if (secondsLeft <= 0) {
                    clearInterval(resendInterval);
                    resendBtn.disabled = false;
                    resendLabel.textContent = 'Resend code';
                    resendTimerEl.classList.add('hidden');
                    resendBtn.classList.remove('text-navy/30');
                    resendBtn.classList.add('text-teal-dark', 'hover:text-navy', 'cursor-pointer');
                    return;
                }
                render();
            }, 1000);
        }

        if (resendBtn) {
            resendBtn.addEventListener('click', function () {
                if (resendBtn.disabled) return;
                // TODO: real resend-code request goes here.
                otpInputs.forEach(function (input) { input.value = ''; input.classList.remove('otp-filled'); });
                if (otpInputs[0]) otpInputs[0].focus();
                startResendCountdown();
            });
        }

        otpInputs.forEach(function (input, index) {

            input.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 1);
                this.classList.toggle('otp-filled', this.value.length > 0);
                if (this.value && index < otpInputs.length - 1) otpInputs[index + 1].focus();
                if (otpErrorEl) otpErrorEl.classList.add('hidden');
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && index > 0) otpInputs[index - 1].focus();
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, otpInputs.length);
                pasted.split('').forEach(function (digit, i) { if (otpInputs[i]) { otpInputs[i].value = digit; otpInputs[i].classList.add('otp-filled'); } });
                const nextEmpty = otpInputs.findIndex(function (box) { return !box.value; });
                (otpInputs[nextEmpty] || otpInputs[otpInputs.length - 1]).focus();
            });

        });

        function otpValue() {
            return otpInputs.map(function (input) { return input.value; }).join('');
        }

        function otpComplete() {
            return otpValue().length === otpInputs.length;
        }

        function serializeOtp() {
            if (otpHidden) otpHidden.value = otpValue();
        }


        /*
        |--------------------------------------------------------------------------
        | PASSWORD — requirements checklist, strength meter, show/hide
        |--------------------------------------------------------------------------
        */

        const passwordUppercaseRegex = /[A-Z]/;
        const passwordLowercaseRegex = /[a-z]/;
        const passwordNumberRegex = /[0-9]/;

        function isValidPassword(v) {
            return v.length >= 8 && passwordUppercaseRegex.test(v) && passwordLowercaseRegex.test(v) && passwordNumberRegex.test(v);
        }

        function updatePasswordRequirements(value) {
            const checks = {
                length: value.length >= 8,
                uppercase: passwordUppercaseRegex.test(value),
                lowercase: passwordLowercaseRegex.test(value),
                number: passwordNumberRegex.test(value),
            };
            Object.keys(checks).forEach(function (key) {
                const item = document.querySelector('#logistics-password-requirements [data-req="' + key + '"]');
                if (!item) return;
                const dot = item.querySelector('.req-dot');
                const check = item.querySelector('.req-check');
                const satisfied = checks[key];
                item.classList.toggle('text-teal-dark', satisfied);
                item.classList.toggle('text-navy/40', !satisfied);
                dot.classList.toggle('bg-teal', satisfied);
                dot.classList.toggle('border-teal', satisfied);
                dot.classList.toggle('border-gray-border', !satisfied);
                check.classList.toggle('hidden', !satisfied);
            });

            const strengthFill = document.getElementById('logistics-password-strength-fill');
            const strengthLabel = document.getElementById('logistics-password-strength-label');
            if (strengthFill && strengthLabel) {
                const passedCount = Object.values(checks).filter(Boolean).length + (value.length >= 12 ? 1 : 0);
                const tiers = [
                    { min: 0, width: '0%', color: 'bg-gray-border', label: '' },
                    { min: 1, width: '25%', color: 'bg-red-400', label: 'Weak' },
                    { min: 2, width: '50%', color: 'bg-amber-400', label: 'Fair' },
                    { min: 3, width: '75%', color: 'bg-teal/70', label: 'Good' },
                    { min: 4, width: '100%', color: 'bg-teal', label: 'Strong' },
                ];
                const tier = value.length === 0 ? tiers[0] : [...tiers].reverse().find(function (t) { return passedCount >= t.min; });
                strengthFill.style.width = tier.width;
                strengthFill.className = 'h-full rounded-full transition-all duration-300 ' + tier.color;
                strengthLabel.textContent = tier.label;
            }
        }

        const passwordInput = document.getElementById('logistics_password');
        const passwordError = document.getElementById('logistics_password_error');
        const passwordConfirmInput = document.getElementById('logistics_password_confirmation');
        const passwordConfirmError = document.getElementById('logistics_password_confirmation_error');

        function passwordStepValid() {
            if (!passwordInput || !passwordConfirmInput) return true;
            const passOk = isValidPassword(passwordInput.value);
            const matchOk = passwordConfirmInput.value.length > 0 && passwordConfirmInput.value === passwordInput.value;
            passwordError.textContent = passOk ? '' : 'Password needs 8+ characters, an uppercase letter, a lowercase letter, and a number.';
            passwordError.classList.toggle('hidden', passOk);
            passwordConfirmError.textContent = matchOk ? '' : 'Passwords do not match.';
            passwordConfirmError.classList.toggle('hidden', matchOk);
            return passOk && matchOk;
        }

        if (passwordInput) {
            passwordInput.addEventListener('input', function () {
                updatePasswordRequirements(passwordInput.value);
                if (passwordConfirmInput.value) passwordStepValid();
            });
        }
        if (passwordConfirmInput) {
            passwordConfirmInput.addEventListener('input', passwordStepValid);
        }

        function setupPasswordToggle(inputEl, buttonEl) {
            if (!inputEl || !buttonEl) return;
            const showIcon = buttonEl.querySelector('.password-icon-show');
            const hideIcon = buttonEl.querySelector('.password-icon-hide');
            buttonEl.addEventListener('click', function () {
                const isHidden = inputEl.type === 'password';
                inputEl.type = isHidden ? 'text' : 'password';
                showIcon.style.display = isHidden ? 'none' : '';
                hideIcon.style.display = isHidden ? '' : 'none';
                buttonEl.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                buttonEl.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                inputEl.focus();
                const caret = inputEl.value.length;
                inputEl.setSelectionRange(caret, caret);
            });
        }

        setupPasswordToggle(passwordInput, document.getElementById('logistics_toggle_password'));
        setupPasswordToggle(passwordConfirmInput, document.getElementById('logistics_toggle_password_confirmation'));


        /*
        |--------------------------------------------------------------------------
        | FIELD VALIDATORS
        |--------------------------------------------------------------------------
        */

        function isValidName(v) { return /^[^0-9]+$/.test(v.trim()); }
        function isValidBusinessName(v) { return /[A-Za-zÀ-ÖØ-öø-ÿÑñ]/.test(v); }
        function isValidPhone(v) { return /^[0-9+\-]+$/.test(v.trim()); }
        function isValidIdNumber(v) { return /^[A-Za-z0-9\-]+$/.test(v.trim()); }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        function showFieldError(input, errorEl, message) {
            if (!errorEl) return;
            if (message) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
                if (input) {
                    input.classList.add('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                    input.classList.remove('border-gray-border/70', 'focus:border-teal', 'focus:ring-teal/10');
                }
            } else {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
                if (input) {
                    input.classList.remove('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                    input.classList.add('border-gray-border/70', 'focus:border-teal', 'focus:ring-teal/10');
                }
            }
        }

        function bindFieldValidator(id, testFn, message) {
            const input = document.getElementById(id);
            const errorEl = document.getElementById(id + '_error');
            if (!input || !errorEl) return;
            const check = function () {
                const val = input.value.trim();
                const bad = val.length > 0 && !testFn(val);
                showFieldError(input, errorEl, bad ? message : '');
            };
            input.addEventListener('input', check);
            input.addEventListener('blur', check);
        }

        bindFieldValidator('logistics_agreement_rep_name', isValidName, 'Please remove any numbers from the name.');
        bindFieldValidator('logistics_company_name', isValidBusinessName, 'Business name needs at least one letter.');
        bindFieldValidator('logistics_rep_last_name', isValidName, 'Please remove any numbers from the name.');
        bindFieldValidator('logistics_rep_first_name', isValidName, 'Please remove any numbers from the name.');
        bindFieldValidator('logistics_contact_no', isValidPhone, 'Numbers only — only + and - are allowed as symbols.');
        bindFieldValidator('logistics_rep_id_number', isValidIdNumber, 'ID number can only contain letters, numbers, and hyphens.');
        bindFieldValidator('logistics_email', function (v) { return emailRegex.test(v); }, 'Please enter a valid email address.');


        /*
        |--------------------------------------------------------------------------
        | TERMS GATE (Step 1)
        |--------------------------------------------------------------------------
        */

        const termsScroll = document.getElementById('logistics-terms-scroll');
        const termsScrollWrap = document.getElementById('logistics-terms-scroll-wrap');
        const termsHint = document.getElementById('logistics-terms-scroll-hint');
        const termsCheckbox = document.getElementById('logistics_terms_agree');
        const step1ErrorEl = document.getElementById('logistics-step1-error');
        const signatureInput = document.getElementById('logistics_agreement_signature');
        const signatureNameEl = document.getElementById('logistics-agreement-signature-name');

        if (termsScroll && termsCheckbox) {
            const unlockCheckbox = function () {
                termsCheckbox.disabled = false;
                if (termsHint) termsHint.classList.add('hidden');
                if (termsScrollWrap) termsScrollWrap.classList.add('terms-at-bottom');
            };
            termsScroll.addEventListener('scroll', function () {
                const reachedBottom = termsScroll.scrollTop + termsScroll.clientHeight >= termsScroll.scrollHeight - 24;
                if (reachedBottom) {
                    unlockCheckbox();
                } else if (termsScrollWrap) {
                    termsScrollWrap.classList.remove('terms-at-bottom');
                }
            });
        }

        if (signatureInput) {
            signatureInput.addEventListener('change', function () {
                if (this.files.length && signatureNameEl) signatureNameEl.textContent = this.files[0].name;
            });
        }

        function termsAccepted() {
            const repNameInput = document.getElementById('logistics_agreement_rep_name');
            const repName = repNameInput.value.trim();
            const date = document.getElementById('logistics_agreement_date').value.trim();
            const hasSignatureFile = signatureInput && signatureInput.files && signatureInput.files.length > 0;
            return !!(termsCheckbox && termsCheckbox.checked && repName && date && hasSignatureFile && isValidName(repName));
        }


        /*
        |--------------------------------------------------------------------------
        | STEP-BY-STEP FLOW (7 steps)
        |--------------------------------------------------------------------------
        */

        function getPanel(step) {
            return modal.querySelector('[data-step-panel="' + step + '"]');
        }

        function updateProgressBar(activeStep) {

            modal.querySelectorAll('[data-step-circle]').forEach(function (circle) {

                const s = parseInt(circle.dataset.stepCircle, 10);
                const numberEl = circle.querySelector('.step-number');
                const checkEl = circle.querySelector('.step-check');

                circle.classList.remove(
                    'bg-teal', 'border-teal', 'text-white',
                    'bg-white', 'border-gray-border', 'text-navy/30',
                    'step-circle-active'
                );

                if (s < activeStep) {
                    circle.classList.add('bg-teal', 'border-teal', 'text-white');
                    numberEl.classList.add('hidden');
                    checkEl.classList.remove('hidden');
                } else if (s === activeStep) {
                    circle.classList.add('bg-teal', 'border-teal', 'text-white', 'step-circle-active');
                    numberEl.classList.remove('hidden');
                    checkEl.classList.add('hidden');
                } else {
                    circle.classList.add('bg-white', 'border-gray-border', 'text-navy/30');
                    numberEl.classList.remove('hidden');
                    checkEl.classList.add('hidden');
                }

            });

            modal.querySelectorAll('[data-step-label]').forEach(function (label) {
                const s = parseInt(label.dataset.stepLabel, 10);
                label.classList.toggle('text-navy', s <= activeStep);
                label.classList.toggle('font-semibold', s <= activeStep);
                label.classList.toggle('text-navy/30', s > activeStep);
                label.classList.toggle('font-medium', s > activeStep);
            });

            modal.querySelectorAll('[data-step-line]').forEach(function (line) {
                const s = parseInt(line.dataset.stepLine, 10);
                line.classList.toggle('bg-teal', s < activeStep);
                line.classList.toggle('bg-gray-border', s >= activeStep);
            });

        }

        function populateReview() {
            modal.querySelectorAll('[data-review]').forEach(function (el) {
                const input = document.getElementById('logistics_' + el.dataset.review) || document.querySelector('#logistics-register-form [name="' + el.dataset.review + '"]');
                if (input && input.value) el.textContent = input.value;
            });

            const sigReview = document.getElementById('logistics-review-signature-file');
            if (sigReview) {
                sigReview.textContent = (signatureInput && signatureInput.files && signatureInput.files.length)
                    ? signatureInput.files[0].name
                    : '—';
            }

            const addressReview = document.getElementById('logistics-review-address');
            if (addressReview) {
                const parts = [
                    document.getElementById('logistics_unit_no') && document.getElementById('logistics_unit_no').value,
                    document.getElementById('logistics_street_no') && document.getElementById('logistics_street_no').value,
                    barangaySelect && barangaySelect.value,
                    municipalitySelect && municipalitySelect.value,
                    provinceSelect && provinceSelect.value,
                    regionSelect && regionSelect.value,
                ].filter(Boolean);
                addressReview.textContent = parts.length ? parts.join(', ') : '—';
            }

            const coverageReview = document.getElementById('logistics-review-coverage');
            if (coverageReview) {
                const names = [...coverageChips.values()].map(function (c) { return c.name; });
                coverageReview.textContent = names.length ? names.join(', ') : '—';
            }
        }

        function goToStep(step) {
            modal.querySelectorAll('[data-step-panel]').forEach(function (panel) {
                const s = parseInt(panel.dataset.stepPanel, 10);
                panel.classList.toggle('hidden', s !== step);
            });

            updateProgressBar(step);

            if (step === 7) populateReview();

            if (dialog) {
                dialog.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function validateStep1() {
            if (!termsAccepted()) {
                if (step1ErrorEl) step1ErrorEl.classList.remove('hidden');
                termsScroll.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return false;
            }
            if (step1ErrorEl) step1ErrorEl.classList.add('hidden');
            return true;
        }

        function step2Valid() {
            const required = [
                'logistics_company_name', 'logistics_business_registration_no', 'logistics_line_of_business',
                'logistics_rep_last_name', 'logistics_rep_first_name', 'logistics_rep_sex', 'logistics_rep_birthday',
                'logistics_email', 'logistics_contact_no', 'logistics_rep_id_number',
                'logistics_region', 'logistics_province', 'logistics_municipality', 'logistics_barangay',
                'logistics_street_no', 'logistics_unit_no',
            ];
            for (const id of required) {
                const el = document.getElementById(id);
                if (!el || !el.value || !el.value.trim()) return false;
            }
            if (!repIdFileInput || !repIdFileInput.files || repIdFileInput.files.length === 0) return false;

            return isValidName(document.getElementById('logistics_rep_last_name').value)
                && isValidName(document.getElementById('logistics_rep_first_name').value)
                && isValidBusinessName(document.getElementById('logistics_company_name').value)
                && isValidPhone(document.getElementById('logistics_contact_no').value)
                && isValidIdNumber(document.getElementById('logistics_rep_id_number').value)
                && emailRegex.test(document.getElementById('logistics_email').value.trim());
        }

        function validateStep2() {
            const step2ErrorEl = document.getElementById('logistics-step2-error');
            const ok = step2Valid();
            if (!ok) {
                if (step2ErrorEl) step2ErrorEl.classList.remove('hidden');
                getPanel(2).scrollIntoView({ behavior: 'smooth', block: 'start' });
                return false;
            }
            if (step2ErrorEl) step2ErrorEl.classList.add('hidden');
            return true;
        }

        function validateStep4() {
            if (!otpComplete()) {
                if (otpErrorEl) otpErrorEl.classList.remove('hidden');
                return false;
            }
            if (otpErrorEl) otpErrorEl.classList.add('hidden');
            return true;
        }

        function validateStep6() {
            const permitInput = document.getElementById('logistics_business_permit');
            const hasPermit = permitInput && permitInput.files && permitInput.files.length > 0;
            const hasCoverage = coverageChips.size > 0;
            if (!hasCoverage || !hasPermit) {
                if (coverageErrorEl && !hasCoverage) coverageErrorEl.classList.remove('hidden');
                return false;
            }
            if (coverageErrorEl) coverageErrorEl.classList.add('hidden');
            return true;
        }

        function validateStep7() {
            const certify = document.getElementById('logistics_certify');
            const certifyError = document.getElementById('logistics_certify_error');
            if (!certify.checked) {
                showFieldError(null, certifyError, 'Please certify that your information is accurate.');
                return false;
            }
            showFieldError(null, certifyError, '');
            return true;
        }

        document.getElementById('logistics-step1-next').addEventListener('click', function () {
            if (validateStep1()) goToStep(2);
        });

        document.getElementById('logistics-step2-back').addEventListener('click', function () { goToStep(1); });
        document.getElementById('logistics-step2-next').addEventListener('click', function () {
            if (validateStep2()) {
                sendVerificationCode();
                goToStep(3);
            }
        });

        document.getElementById('logistics-step3-back').addEventListener('click', function () { goToStep(2); });
        document.getElementById('logistics-step3-next').addEventListener('click', function () {
            goToStep(4);
            if (otpInputs[0]) otpInputs[0].focus();
        });

        document.getElementById('logistics-step4-back').addEventListener('click', function () { goToStep(3); });
        document.getElementById('logistics-step4-next').addEventListener('click', function () {
            if (validateStep4()) {
                serializeOtp();
                goToStep(5);
            }
        });

        document.getElementById('logistics-step5-back').addEventListener('click', function () { goToStep(4); });
        document.getElementById('logistics-step5-next').addEventListener('click', function () {
            if (passwordStepValid()) {
                if (!coverageOptionsLoaded) {
                    coverageOptionsLoaded = true;
                    initCoverageAddOptions();
                }
                autoSuggestCoverage();
                goToStep(6);
            }
        });

        document.getElementById('logistics-step6-back').addEventListener('click', function () { goToStep(5); });
        document.getElementById('logistics-step6-next').addEventListener('click', function () {
            if (validateStep6()) {
                serializeCoverage();
                goToStep(7);
            }
        });

        document.getElementById('logistics-step7-back').addEventListener('click', function () { goToStep(6); });


        /*
        |--------------------------------------------------------------------------
        | FILE PICKERS — generic label-swap for business permit / accreditation
        |--------------------------------------------------------------------------
        */

        function setupSimpleFilePicker(inputId, nameElId) {
            const input = document.getElementById(inputId);
            const nameEl = document.getElementById(nameElId);
            if (!input || !nameEl) return;
            input.addEventListener('change', function () {
                if (this.files.length) {
                    nameEl.textContent = this.files[0].name;
                    nameEl.classList.remove('hidden');
                } else {
                    nameEl.textContent = '';
                    nameEl.classList.add('hidden');
                }
            });
        }

        setupSimpleFilePicker('logistics_business_permit', 'logistics-business-permit-name');
        setupSimpleFilePicker('logistics_accreditation_docs', 'logistics-accreditation-docs-name');

        /*
        |--------------------------------------------------------------------------
        | LIVE IMAGE / PDF PREVIEW FOR UPLOADED FILES
        |--------------------------------------------------------------------------
        */

        function closeLogisticsFilePreview() {
            if (!filePreviewModal) return;

            filePreviewModal.classList.add('hidden');
            filePreviewModal.classList.remove('flex');
            filePreviewModal.setAttribute('aria-hidden', 'true');

            if (filePreviewFullImage) {
                filePreviewFullImage.classList.add('hidden');
                filePreviewFullImage.removeAttribute('src');
            }

            if (filePreviewPdfFrame) {
                filePreviewPdfFrame.classList.add('hidden');
                filePreviewPdfFrame.removeAttribute('src');
            }

            activeFilePreviewKey = null;

            if (modal.getAttribute('aria-hidden') === 'false') {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        }

        function openLogisticsFilePreview(key, fallbackName) {
            if (!filePreviewModal || !filePreviewUrls[key]) return;

            const entry = filePreviewUrls[key];
            activeFilePreviewKey = key;

            if (filePreviewName) {
                filePreviewName.textContent = entry.name || fallbackName || 'Uploaded file';
            }

            if (entry.type === 'image' && filePreviewFullImage) {
                filePreviewFullImage.src = entry.url;
                filePreviewFullImage.classList.remove('hidden');

                if (filePreviewPdfFrame) {
                    filePreviewPdfFrame.classList.add('hidden');
                    filePreviewPdfFrame.removeAttribute('src');
                }
            }

            if (entry.type === 'pdf' && filePreviewPdfFrame) {
                filePreviewPdfFrame.src = entry.url;
                filePreviewPdfFrame.classList.remove('hidden');

                if (filePreviewFullImage) {
                    filePreviewFullImage.classList.add('hidden');
                    filePreviewFullImage.removeAttribute('src');
                }
            }

            filePreviewModal.classList.remove('hidden');
            filePreviewModal.classList.add('flex');
            filePreviewModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        }

        function setupLogisticsFilePreview(config) {
            const input = document.getElementById(config.inputId);
            const nameEl = document.getElementById(config.nameId);
            const card = document.getElementById(config.cardId);
            const image = document.getElementById(config.imageId);
            const pdf = document.getElementById(config.pdfId);
            const status = document.getElementById(config.statusId);
            const viewButton = document.getElementById(config.viewId);
            const changeButton = document.getElementById(config.changeId);

            if (!input || !card) return;

            function clearInlinePreview() {
                if (filePreviewUrls[config.key]) {
                    URL.revokeObjectURL(filePreviewUrls[config.key].url);
                    delete filePreviewUrls[config.key];
                }

                if (activeFilePreviewKey === config.key) closeLogisticsFilePreview();

                card.classList.add('hidden');

                if (image) {
                    image.classList.add('hidden');
                    image.removeAttribute('src');
                }

                if (pdf) {
                    pdf.classList.add('hidden');
                    pdf.classList.remove('flex');
                }

                if (status) status.textContent = 'Uploaded file';
            }

            function renderInlinePreview(file) {
                clearInlinePreview();

                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                const isImage = file.type.startsWith('image/');

                if (!isPdf && !isImage) return;

                const objectUrl = URL.createObjectURL(file);
                filePreviewUrls[config.key] = {
                    url: objectUrl,
                    type: isPdf ? 'pdf' : 'image',
                    name: file.name,
                };

                card.classList.remove('hidden');
                if (status) status.textContent = file.name;

                if (isImage && image) {
                    image.src = objectUrl;
                    image.classList.remove('hidden');

                    if (pdf) {
                        pdf.classList.add('hidden');
                        pdf.classList.remove('flex');
                    }
                }

                if (isPdf && pdf) {
                    pdf.classList.remove('hidden');
                    pdf.classList.add('flex');

                    if (image) {
                        image.classList.add('hidden');
                        image.removeAttribute('src');
                    }
                }
            }

            input.addEventListener('change', function () {
                if (!this.files || !this.files.length) {
                    clearInlinePreview();
                    return;
                }

                // Existing field validation still owns what file types/sizes are valid.
                // This listener only previews a file that remains selected afterward.
                window.setTimeout(function () {
                    if (!input.files || !input.files.length) {
                        clearInlinePreview();
                        return;
                    }
                    renderInlinePreview(input.files[0]);
                }, 0);
            });

            if (viewButton) {
                viewButton.addEventListener('click', function () {
                    openLogisticsFilePreview(config.key, nameEl ? nameEl.textContent : 'Uploaded file');
                });
            }

            if (changeButton) {
                changeButton.addEventListener('click', function () {
                    input.click();
                });
            }
        }

        setupLogisticsFilePreview({
            key: 'agreement-signature',
            inputId: 'logistics_agreement_signature',
            nameId: 'logistics-agreement-signature-name',
            cardId: 'logistics-agreement-signature-preview-card',
            imageId: 'logistics-agreement-signature-preview-image',
            pdfId: 'logistics-agreement-signature-preview-pdf',
            statusId: 'logistics-agreement-signature-preview-status',
            viewId: 'logistics-agreement-signature-preview-view',
            changeId: 'logistics-agreement-signature-preview-change',
        });

        setupLogisticsFilePreview({
            key: 'rep-valid-id',
            inputId: 'logistics_rep_valid_id',
            nameId: 'logistics-rep-valid-id-name',
            cardId: 'logistics-rep-valid-id-preview-card',
            imageId: 'logistics-rep-valid-id-preview-image',
            pdfId: 'logistics-rep-valid-id-preview-pdf',
            statusId: 'logistics-rep-valid-id-preview-status',
            viewId: 'logistics-rep-valid-id-preview-view',
            changeId: 'logistics-rep-valid-id-preview-change',
        });

        setupLogisticsFilePreview({
            key: 'business-permit',
            inputId: 'logistics_business_permit',
            nameId: 'logistics-business-permit-name',
            cardId: 'logistics-business-permit-preview-card',
            imageId: 'logistics-business-permit-preview-image',
            pdfId: 'logistics-business-permit-preview-pdf',
            statusId: 'logistics-business-permit-preview-status',
            viewId: 'logistics-business-permit-preview-view',
            changeId: 'logistics-business-permit-preview-change',
        });

        setupLogisticsFilePreview({
            key: 'accreditation-docs',
            inputId: 'logistics_accreditation_docs',
            nameId: 'logistics-accreditation-docs-name',
            cardId: 'logistics-accreditation-docs-preview-card',
            imageId: 'logistics-accreditation-docs-preview-image',
            pdfId: 'logistics-accreditation-docs-preview-pdf',
            statusId: 'logistics-accreditation-docs-preview-status',
            viewId: 'logistics-accreditation-docs-preview-view',
            changeId: 'logistics-accreditation-docs-preview-change',
        });



        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT GUARD — safety net in case a step is bypassed
        |--------------------------------------------------------------------------
        */

        const registerForm = document.getElementById('logistics-register-form');

        registerForm.addEventListener('submit', function (e) {

            if (!termsAccepted()) {
                e.preventDefault();
                goToStep(1);
                if (step1ErrorEl) step1ErrorEl.classList.remove('hidden');
                return;
            }

            if (!step2Valid()) {
                e.preventDefault();
                goToStep(2);
                const step2ErrorEl = document.getElementById('logistics-step2-error');
                if (step2ErrorEl) step2ErrorEl.classList.remove('hidden');
                return;
            }

            if (!otpComplete()) {
                e.preventDefault();
                goToStep(4);
                if (otpErrorEl) otpErrorEl.classList.remove('hidden');
                return;
            }

            if (!passwordStepValid()) {
                e.preventDefault();
                goToStep(5);
                return;
            }

            if (!validateStep6()) {
                e.preventDefault();
                goToStep(6);
                return;
            }

            if (!validateStep7()) {
                e.preventDefault();
                goToStep(7);
                return;
            }

            serializeOtp();
            serializeCoverage();
        });


        // Initial visual state — safe even while the modal is still hidden.
        updateProgressBar(1);

    })();
</script>
@endpush