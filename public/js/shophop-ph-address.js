/* ShopHop Philippines-wide cascading location selector.
 * Uses the same 9-digit PSGC identifiers already stored by ShopHop.  NCR
 * (which has no provinces) is represented in the legacy province snapshot
 * as region code 130000000, name 'National Capital Region (NCR)'.
 */
(function (global) {
    'use strict';
    const BASE = 'https://psgc.gitlab.io/api';
    const NCR = '130000000';
    const NIR = '180000000';
    const NIR_PROVINCES = ['Negros Occidental', 'Negros Oriental', 'Siquijor'];
    const cache = new Map();
    const sort = rows => [...rows].sort((a, b) => a.name.localeCompare(b.name, 'en'));
    const normalName = text => String(text || '').trim().toLowerCase().replace(/\s+/g, ' ');
    async function get(path) {
        if (!cache.has(path)) {
            const result = fetch(BASE + path, { headers: { Accept: 'application/json' } })
                .then(r => { if (!r.ok) throw new Error('Location service unavailable'); return r.json(); })
                .then(json => {
                    const items = Array.isArray(json) ? json : (json.data || []);
                    if (!Array.isArray(items)) throw new Error('Unexpected location response');
                    return sort(items.filter(x => x && x.code && x.name));
                })
                .catch(error => { cache.delete(path); throw error; });
            cache.set(path, result);
        }
        return cache.get(path);
    }
    async function regions() {
        const list = await get('/regions/');
        // PSGC GitLab has the legacy 17 regions. NIR was restored in 2024;
        // the old province data still contains its three provinces.
        return sort([...list.filter(x => x.code !== NIR),
            { code: NIR, name: 'Negros Island Region (NIR)', regionName: 'Negros Island Region' }]);
    }
    function regionLabel(region) {
        if (!region) return '';
        if (region.code === NCR) return 'National Capital Region (NCR)';
        const prefix = region.regionName || '';
        return prefix && prefix !== region.name && !region.name.includes(prefix)
            ? prefix + ' (' + region.name + ')' : region.name;
    }
    async function provinces(regionCode) {
        const all = await get('/provinces/');
        if (regionCode === NIR) return all.filter(p => NIR_PROVINCES.some(n => normalName(n) === normalName(p.name)));
        if (regionCode === NCR) return [];
        return all.filter(p => p.regionCode === regionCode || p.region_code === regionCode);
    }
    async function cities(regionCode, provinceCode) {
        if (regionCode === NIR && (!provinceCode || provinceCode === NIR)) {
            const parts = await provinces(NIR);
            const batches = await Promise.all(parts.map(p =>
                get('/provinces/' + encodeURIComponent(p.code) + '/cities-municipalities/')));
            return sort(batches.flat());
        }
        if (provinceCode && provinceCode !== NCR && provinceCode !== NIR)
            return get('/provinces/' + encodeURIComponent(provinceCode) + '/cities-municipalities/');
        return get('/regions/' + encodeURIComponent(regionCode) + '/cities-municipalities/');
    }
    const barangays = code => get('/cities-municipalities/' + encodeURIComponent(code) + '/barangays/');
    function fill(select, rows, placeholder, oldCode = '', oldName = '') {
        select.replaceChildren(new Option(placeholder, ''));
        rows.forEach(row => {
            const opt = new Option(row.name, row.code);
            if (row.code === oldCode || (!oldCode && normalName(row.name) === normalName(oldName))) opt.selected = true;
            select.add(opt);
        });
        select.disabled = rows.length === 0;
        return select.value;
    }
    function placeholder(select, text) { select.replaceChildren(new Option(text, '')); select.disabled = true; }
    async function bind({ region, province, city, barangay, provinceName, cityName, barangayName,
        regionName, status, oldRegion, oldProvince, oldProvinceName, oldCity, oldCityName, oldBarangay, oldBarangayName }) {
        if (!region || !province || !city || !barangay) return;
        let serial = 0;
        const provinceWrapper = province.closest?.('[data-ph-province-wrapper]');
        const noProvinceNote = region.parentElement?.querySelector('[data-ph-no-province-note]');
        const showProvince = (isApplicable) => {
            if (provinceWrapper) provinceWrapper.classList.toggle('hidden', !isApplicable);
            if (noProvinceNote) noProvinceNote.classList.toggle('hidden', isApplicable);
        };
        const report = (message, error = false) => {
            if (typeof status === 'function') status(message, error);
        };
        const resetDown = () => {
            placeholder(city, 'Select city / municipality'); placeholder(barangay, 'Select barangay');
            if (cityName) cityName.value = '';
            if (barangayName) barangayName.value = '';
        };
        const chooseName = select => select.value ? select.selectedOptions[0]?.textContent || '' : '';
        const loadBarangays = async (preselect = '') => {
            const token = ++serial;
            if (barangayName) barangayName.value = '';
            placeholder(barangay, city.value ? 'Loading barangays...' : 'Select city first');
            if (!city.value) return;
            try {
                const list = await barangays(city.value);
                if (serial !== token) return;
                fill(barangay, list, 'Select barangay', preselect, oldBarangayName);
                if (barangayName) barangayName.value = chooseName(barangay);
                report('');
            } catch (_) { if (serial === token) report('Unable to load barangays. Please try again.', true); }
        };
        const loadCities = async (preselect = '') => {
            const token = ++serial;
            placeholder(city, 'Loading cities / municipalities...');
            placeholder(barangay, 'Select city first');
            if (cityName) cityName.value = '';
            if (barangayName) barangayName.value = '';
            if (!province.value || !region.value) return;
            try {
                const rows = await cities(region.value, province.value);
                if (serial !== token) return;
                fill(city, rows, 'Select city / municipality', preselect, oldCityName);
                if (cityName) cityName.value = chooseName(city);
                if (city.value) await loadBarangays(oldBarangay);
                else report('');
            } catch (_) { if (serial === token) report('Unable to load cities. Please try again.', true); }
        };
        const loadProvinces = async (preselect = '') => {
            const token = ++serial;
            placeholder(province, 'Loading provinces...'); resetDown();
            showProvince(true);
            if (provinceName) provinceName.value = '';
            if (!region.value) return;
            const selectedRegion = region.selectedOptions[0]?.textContent || '';
            if (regionName) regionName.value = selectedRegion;
            try {
                const list = await provinces(region.value);
                if (serial !== token) return;
                if (region.value === NCR) {
                    // Existing database validators require province fields. Store NCR
                    // as the compatibility value, but do not show a fake province.
                    fill(province, [{ code: NCR, name: selectedRegion }], 'Province not applicable');
                    province.value = NCR;
                    province.disabled = false; // required select must submit its value
                    showProvince(false);
                } else if (list.length === 0) {
                    report('No provinces are available for this region. Please retry.', true);
                    return;
                } else {
                    fill(province, list, 'Select province', preselect, oldProvinceName);
                }
                if (provinceName) provinceName.value = chooseName(province);
                if (province.value) await loadCities(oldCity);
                else report('');
            } catch (_) { if (serial === token) report('Unable to load provinces. Please retry.', true); }
        };
        region.addEventListener('change', () => loadProvinces());
        province.addEventListener('change', () => {
            if (provinceName) provinceName.value = chooseName(province);
            loadCities();
        });
        city.addEventListener('change', () => {
            if (cityName) cityName.value = chooseName(city);
            loadBarangays();
        });
        barangay.addEventListener('change', () => { if (barangayName) barangayName.value = chooseName(barangay); });
        try {
            report('Loading Philippine regions...');
            const list = await regions();
            fill(region, list.map(x => ({ code: x.code, name: regionLabel(x) })), 'Select region', oldRegion, oldRegion);
            // Restore previous postback selections from the saved province code or name.
            if (!region.value && (oldProvince || oldProvinceName)) {
                const provinceList = await get('/provinces/');
                const match = provinceList.find(p => p.code === oldProvince || normalName(p.name) === normalName(oldProvinceName));
                if (match) region.value = NIR_PROVINCES.some(x => normalName(x) === normalName(match.name)) ? NIR : match.regionCode;
                else if (oldProvince === NCR || /\bncr\b|national capital region/i.test(oldProvinceName || '')) region.value = NCR;
            }
            if (region.value) await loadProvinces(oldProvince);
            else report('');
        } catch (_) { report('Could not load Philippine locations. Check your connection and reload.', true); }
    }
    global.ShopHopPHAddress = { regions, provinces, cities, barangays, regionLabel, fill, bind, NCR, NIR };
})(window);
