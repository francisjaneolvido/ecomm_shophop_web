(() => {
    const money = new Intl.NumberFormat('en-PH', {style: 'currency', currency: 'PHP'});
    document.querySelectorAll('[data-search-suggestions]').forEach(form => {
        const input = form.querySelector('input[type="search"]');
        if (!input) return;
        const panel = document.createElement('div');
        panel.className = 'sh-suggestions'; panel.hidden = true;
        const list = document.createElement('div');
        list.id = input.id + '-suggestions'; list.setAttribute('role', 'listbox'); list.setAttribute('aria-label', 'Search suggestions');
        panel.append(list); form.append(panel);
        input.setAttribute('aria-controls', list.id);
        let timer, controller, version = 0, options = [], active = -1;
        const safeUrl = value => {
            try { const url = new URL(value, location.href); return url.origin === location.origin && ['http:', 'https:'].includes(url.protocol) ? url.href : null; }
            catch (_) { return null; }
        };
        function close(cancel = true) {
            panel.hidden = true; input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); active = -1;
            if (cancel) { version++; clearTimeout(timer); controller?.abort(); }
        }
        function choose(index) {
            active = index;
            options.forEach((option, i) => option.setAttribute('aria-selected', String(i === active)));
            if (active >= 0) { input.setAttribute('aria-activedescendant', options[active].id); options[active].scrollIntoView({block: 'nearest'}); }
            else input.removeAttribute('aria-activedescendant');
        }
        function option(parent, item, product = false) {
            const href = safeUrl(item.url); if (!href) return;
            const link = document.createElement('a'); link.href = href; link.className = 'sh-suggestions-option'; link.tabIndex = -1;
            link.id = list.id + '-' + options.length; link.setAttribute('role', 'option'); link.setAttribute('aria-selected', 'false');
            if (product && item.image) {
                const src = safeUrl(item.image);
                if (src) { const img = document.createElement('img'); img.src = src; img.alt = ''; img.loading = 'lazy'; link.append(img); }
            }
            const copy = document.createElement('span'); copy.className = 'sh-suggestions-copy';
            const name = document.createElement('span'); name.className = 'sh-suggestions-name'; name.textContent = item.name; copy.append(name);
            if (product) { const meta = document.createElement('span'); meta.className = 'sh-suggestions-meta'; meta.textContent = item.category ?? ''; copy.append(meta); }
            link.append(copy);
            if (product && Number.isFinite(Number(item.price))) { const price = document.createElement('span'); price.className = 'sh-suggestions-price'; price.textContent = money.format(Number(item.price)); link.append(price); }
            else { const arrow = document.createElement('span'); arrow.className = 'sh-suggestions-arrow'; arrow.textContent = '\u2197'; link.append(arrow); }
            const index = options.length; link.addEventListener('pointermove', () => choose(index)); options.push(link); parent.append(link);
            return link;
        }
        function group(title, items, product = false) {
            if (!items.length) return;
            const section = document.createElement('div'); section.setAttribute('role', 'group'); section.setAttribute('aria-label', title);
            const label = document.createElement('p'); label.className = 'sh-suggestions-group-label'; label.setAttribute('role', 'presentation'); label.textContent = title; section.append(label);
            items.forEach(item => option(section, item, product)); list.append(section);
        }
        async function load() {
            const query = input.value.trim();
            const requestId = ++version;
            controller?.abort(); controller = new AbortController();
            if (query.length === 1) { close(false); return; }
            try {
                const url = new URL(form.dataset.suggestionsUrl, location.href); url.searchParams.set('q', query);
                const response = await fetch(url, {headers: {Accept: 'application/json'}, cache: 'no-store', signal: controller.signal});
                if (!response.ok) { if (requestId === version) close(false); return; }
                const data = await response.json();
                if (requestId !== version || document.activeElement !== input || input.value.trim() !== query) return;
                list.replaceChildren(); options = []; active = -1;
                const categories = Array.isArray(data.categories) ? data.categories : [];
                const products = Array.isArray(data.products) ? data.products : [];
                group(query ? 'Matching categories' : 'Explore categories', categories);
                group(query ? 'Matching products' : 'Recently added products', products, true);
                if (!options.length) { const note = document.createElement('p'); note.className = 'sh-suggestions-note'; note.textContent = 'No matching suggestions. Try another term or view all search results.'; list.append(note); }
                const allUrl = new URL(query ? form.action : form.dataset.categoriesUrl, location.href);
                if (query) allUrl.searchParams.set('search', query);
                const footer = option(list, {name: query ? `View all results for "${query}"` : 'Browse all categories', url: allUrl.href});
                footer?.classList.add('sh-suggestions-footer');
                panel.hidden = false; input.setAttribute('aria-expanded', 'true');
            } catch (error) { if (requestId === version) close(false); }
        }
        input.addEventListener('focus', load);
        input.addEventListener('input', () => { version++; controller?.abort(); clearTimeout(timer); close(false); timer = setTimeout(load, 250); });
        input.addEventListener('keydown', event => {
            if (event.isComposing) return;
            if (event.key === 'Escape') { close(); return; }
            if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && !panel.hidden && options.length) {
                event.preventDefault(); choose(active < 0 ? (event.key === 'ArrowDown' ? 0 : options.length - 1) : (active + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length);
            } else if (event.key === 'Enter' && !panel.hidden && active >= 0) { event.preventDefault(); location.assign(options[active].href); }
        });
        form.addEventListener('submit', () => close());
        form.addEventListener('focusout', event => { if (!form.contains(event.relatedTarget)) close(); });
        document.addEventListener('pointerdown', event => { if (!form.contains(event.target)) close(); });
    });
})();
