document.addEventListener('DOMContentLoaded', function () {
    // Public navigation remains ordinary links without JavaScript; these hooks add in-page state only when available.
    const sectionIds = ['home', 'categories', 'deals', 'new-arrivals'];
    const validSectionIds = new Set(sectionIds.concat('trending'));
    const navLinks = Array.from(document.querySelectorAll('[data-section-nav]'));
    const sectionTargets = {
        home: document.documentElement,
        categories: document.getElementById('categories'),
        deals: document.getElementById('deals'),
        'new-arrivals': document.getElementById('new-arrivals'),
    };

    const setActiveSection = function (sectionId) {
        const activeId = sectionIds.includes(sectionId) ? sectionId : 'home';
        navLinks.forEach(function (link) {
            const active = link.dataset.sectionNav === activeId;
            link.classList.toggle('bg-teal/15', active);
            link.classList.toggle('text-teal-dark', active);
            link.classList.toggle('font-medium', active);
            link.setAttribute('aria-current', active ? 'location' : 'false');
        });
    };

    const scrollToSection = function (sectionId, behavior) {
        const target = sectionTargets[sectionId] || document.getElementById(sectionId);

        if (!target) {
            return false;
        }

        if (sectionId === 'home') {
            window.scrollTo({ top: 0, behavior });
        } else {
            target.scrollIntoView({ behavior, block: 'start' });
        }

        return true;
    };

    const currentSectionId = function () {
        const hashSectionId = window.location.hash.slice(1);

        return validSectionIds.has(hashSectionId) ? hashSectionId : 'home';
    };

    if (navLinks.length) {
        const sections = sectionIds.slice(1)
            .map(function (id) { return document.getElementById(id); })
            .filter(Boolean);
        // Observe section visibility instead of calculating positions in every scroll event.
        const observer = new IntersectionObserver(function (entries) {
            const visible = entries
                .filter(function (entry) { return entry.isIntersecting; })
                .sort(function (a, b) { return b.intersectionRatio - a.intersectionRatio; });
            if (visible[0]) setActiveSection(visible[0].target.id);
        }, { rootMargin: '-20% 0px -55% 0px', threshold: [0, .25, .5] });
        sections.forEach(observer.observe.bind(observer));

        window.addEventListener('scroll', function () {
            if (window.scrollY < 120) setActiveSection('home');
        }, { passive: true });
        window.addEventListener('popstate', function () {
            const sectionId = currentSectionId();
            setActiveSection(sectionId);
            scrollToSection(sectionId, 'auto');
        });
        setActiveSection(currentSectionId());
    }

    // Intercept only same-document section links so normal routes and the no-JavaScript navigation path stay intact.
    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');

        if (!link) {
            return;
        }

        const href = link.getAttribute('href');

        if (href === '#') {
            event.preventDefault();
            return;
        }

        let url;

        try {
            url = new URL(link.href, window.location.href);
        } catch (error) {
            return;
        }

        const targetId = url.hash.slice(1) || link.dataset.sectionNav;
        const isCurrentDocument = url.origin === window.location.origin
            && url.pathname === window.location.pathname
            && url.search === window.location.search;

        if (!isCurrentDocument || !validSectionIds.has(targetId)) {
            return;
        }

        const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';

        if (!scrollToSection(targetId, behavior)) {
            return;
        }

        event.preventDefault();
        setActiveSection(targetId);

        const destination = targetId === 'home'
            ? window.location.pathname + window.location.search
            : '#' + targetId;

        if (destination !== window.location.pathname + window.location.search + window.location.hash) {
            window.history.pushState(null, '', destination);
        }
    });

    // Placeholder forms have no server endpoint yet; prevent a misleading jump to the top of the page.
    document.querySelectorAll('form[action="#"]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
        });
    });
});
