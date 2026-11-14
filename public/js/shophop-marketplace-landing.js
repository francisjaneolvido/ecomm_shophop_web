/* A deal timer always uses its real end timestamp, even after a tab was asleep. */
(function () {
    'use strict';
    const clocks = [...document.querySelectorAll('[data-deal-countdown]')];
    if (!clocks.length) return;
    function update() {
        let expired = false;
        const now = Date.now();
        clocks.forEach((clock) => {
            const end = Date.parse(clock.dataset.dealEnd || '');
            if (!Number.isFinite(end)) return;
            const remaining = Math.max(0, Math.ceil((end - now) / 1000));
            const hours = Math.floor(remaining / 3600);
            const minutes = Math.floor((remaining % 3600) / 60);
            const seconds = remaining % 60;
            clock.textContent = [hours, minutes, seconds].map((part) => String(part).padStart(2, '0')).join(':');
            if (remaining === 0) expired = true;
        });
        if (expired) window.location.reload();
    }
    update();
    window.setInterval(update, 1000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) update(); });
})();

/* Play only the visible seller video; the first frame remains a lightweight poster. */
(function () {
    'use strict';
    const videos = [...document.querySelectorAll('[data-shop-video]')];
    if (!videos.length) return;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const visibility = new Map();
    let current = null;
    function activate(video) {
        if (current && current !== video) current.pause();
        current = video;
        if (!video) return;
        if (!video.src) video.src = video.dataset.videoSrc;
        video.play().catch(() => {});
    }
    function choose() {
        if (document.hidden || reduced) { activate(null); return; }
        const visible = videos.filter((video) => (visibility.get(video) || 0) >= .6);
        visible.sort((a, b) => (visibility.get(b) || 0) - (visibility.get(a) || 0));
        activate(visible[0] || null);
    }
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => visibility.set(entry.target, entry.intersectionRatio));
            choose();
        }, { threshold: [0, .25, .6, .85, 1] });
        videos.forEach((video) => observer.observe(video));
    }
    document.addEventListener('visibilitychange', choose);
    videos.forEach((video) => video.addEventListener('click', (event) => {
        if (video.closest('a')) return;
        event.preventDefault();
        if (current === video && !video.paused) video.pause();
        else activate(video);
    }));
    document.querySelectorAll('[data-video-sound]').forEach((button) => button.addEventListener('click', () => {
        const video = button.closest('.sh-watch-card')?.querySelector('video');
        if (!video) return;
        activate(video);
        video.muted = !video.muted;
        button.textContent = video.muted ? 'Sound off' : 'Sound on';
        button.setAttribute('aria-pressed', String(!video.muted));
    }));
})();

/* ShopHop landing hero controls. Keeps the first slide visible without JS. */
(function () {
    'use strict';
    const carousel = document.querySelector('[data-shop-hero]');
    if (!carousel) return;

    const slides = Array.from(carousel.querySelectorAll('[data-shop-slide]'));
    const dots = Array.from(carousel.querySelectorAll('[data-shop-hero-dot]'));
    const prev = carousel.querySelector('[data-shop-hero-prev]');
    const next = carousel.querySelector('[data-shop-hero-next]');
    const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!slides.length) return;

    let index = 0;
    let interval = null;
    let paused = false;

    function show(nextIndex) {
        index = (nextIndex + slides.length) % slides.length;
        slides.forEach(function (slide, i) { slide.hidden = i !== index; });
        dots.forEach(function (dot, i) { dot.setAttribute('aria-current', i === index ? 'true' : 'false'); });
    }

    function stop() {
        if (interval !== null) {
            window.clearInterval(interval);
            interval = null;
        }
    }

    function start() {
        stop();
        if (!reduced && !paused && !document.hidden && slides.length > 1) {
            interval = window.setInterval(function () { show(index + 1); }, 6500);
        }
    }

    prev?.addEventListener('click', function () { show(index - 1); start(); });
    next?.addEventListener('click', function () { show(index + 1); start(); });
    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () { show(i); start(); });
    });

    carousel.addEventListener('mouseenter', function () { paused = true; stop(); });
    carousel.addEventListener('mouseleave', function () { paused = false; start(); });
    carousel.addEventListener('focusin', function () { paused = true; stop(); });
    carousel.addEventListener('focusout', function (e) {
        if (!carousel.contains(e.relatedTarget)) { paused = false; start(); }
    });
    document.addEventListener('visibilitychange', start);

    show(0);
    start();
})();

/* ShopHop 14-category slider: seven at once on desktop, fewer on narrow screens. */
(function () {
    'use strict';
    const track = document.querySelector('[data-shop-categories]');
    if (!track) return;

    const items = Array.from(track.querySelectorAll('[data-shop-category]'));
    const prev = document.querySelector('[data-shop-category-prev]');
    const next = document.querySelector('[data-shop-category-next]');
    const position = document.querySelector('[data-shop-category-position]');
    if (!items.length) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function metrics() {
        const itemWidth = items[0].getBoundingClientRect().width;
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
        const step = itemWidth + gap;
        const visible = Math.max(1, Math.min(items.length, Math.round((track.clientWidth + gap) / step)));
        const maxLeft = Math.max(0, track.scrollWidth - track.clientWidth);
        return { step, visible, maxLeft };
    }

    function update() {
        const m = metrics();
        const first = m.step ? Math.max(0, Math.min(items.length - m.visible, Math.round(track.scrollLeft / m.step))) : 0;
        if (prev) prev.disabled = track.scrollLeft <= 2;
        if (next) next.disabled = track.scrollLeft >= m.maxLeft - 2;
        if (position) position.textContent = `${first + 1}–${Math.min(items.length, first + m.visible)} of ${items.length}`;
    }

    function move(direction) {
        const m = metrics();
        if (m.step <= 0) return;
        const current = Math.round(track.scrollLeft / m.step);
        const targetFirst = Math.min(items.length - m.visible, Math.max(0, current + direction * m.visible));
        track.scrollTo({ left: Math.max(0, targetFirst * m.step), behavior: reducedMotion ? 'auto' : 'smooth' });
    }

    prev?.addEventListener('click', () => move(-1));
    next?.addEventListener('click', () => move(1));
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
})();

/* Private Buyer actions on a public marketplace view: role-authorized Laravel routes only. */
(function () {
    'use strict';
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const toast = document.createElement('div');
    toast.className = 'sh-market-toast';
    toast.hidden = true;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.appendChild(toast);
    let toastTimer;

    function notify(message) {
        toast.textContent = message;
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.hidden = true; }, 3000);
    }

    async function send(url, method, body) {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token || '',
                ...(body ? { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' } : {}),
            },
            body: body ? new URLSearchParams(body).toString() : undefined,
        });
        if (!response.ok) {
            const error = await response.json().catch(() => null);
            const message = error?.message || Object.values(error?.errors || {})[0]?.[0] || 'Action unavailable. Try again.';
            throw new Error(message);
        }
        return response.json();
    }

    document.addEventListener('click', async (event) => {
        const heart = event.target.closest('[data-shop-favorite]');
        if (heart) {
            event.preventDefault();
            if (heart.disabled) return;
            const liked = heart.getAttribute('aria-pressed') === 'true';
            heart.disabled = true;
            try {
                const result = await send(heart.dataset.favoriteUrl, liked ? 'DELETE' : 'POST');
                heart.setAttribute('aria-pressed', result.liked ? 'true' : 'false');
                const id = heart.dataset.favoriteProduct;
                document.querySelectorAll('[data-shop-favorite]').forEach((button) => {
                    if (button.dataset.favoriteProduct === id) {
                        button.setAttribute('aria-pressed', result.liked ? 'true' : 'false');
                        button.setAttribute('aria-label', (result.liked ? 'Remove ' : 'Save ') + (button.dataset.favoriteName || 'product') + (result.liked ? ' from wishlist' : ' to wishlist'));
                    }
                });
                if (Number.isFinite(Number(result.favorites_count))) {
                    document.querySelectorAll('[data-favorite-count]').forEach((badge) => {
                        badge.textContent = String(result.favorites_count);
                        badge.hidden = Number(result.favorites_count) <= 0;
                    });
                }
                notify(result.liked ? 'Saved to My Likes.' : 'Removed from My Likes.');
            } catch (error) {
                notify(error.message);
            } finally { heart.disabled = false; }
            return;
        }

        const add = event.target.closest('[data-shop-add-cart]');
        if (add) {
            event.preventDefault();
            if (add.disabled) return;
            add.disabled = true;
            try {
                const result = await send(add.dataset.cartUrl, 'POST', { product_id: add.dataset.productId, qty: 1 });
                const count = document.querySelector('[data-shop-cart-count]');
                if (count && Number.isFinite(Number(result.cart_count))) {
                    count.textContent = result.cart_count;
                    count.hidden = Number(result.cart_count) <= 0;
                }
                notify('Added to your cart.');
            } catch (error) {
                notify(error.message);
            } finally { add.disabled = false; }
        }
    });
})();
