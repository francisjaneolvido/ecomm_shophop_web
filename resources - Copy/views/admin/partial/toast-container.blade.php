{{-- =========================================================
    TOAST NOTIFICATION CONTAINER
    Isang beses lang ito nire-render sa app.blade.php layout.
    Function na `showToast(message, type, duration)` ang gamit
    sa kahit anong page — same pattern gaya ng ibang modals
    (naka-attach sa window para accessible kahit saan).
========================================================= --}}

<ul id="shophop-toast-container" class="toast-container"></ul>

<style>
    .toast-container {
        position: fixed;
        top: 1.25rem;
        right: 1.25rem;
        z-index: 9999;

        --content-color: #0f172a;
        --background-color: #f8fafc;
        --font-size-content: 0.8125rem;
        --icon-size: 1.1em;

        display: flex;
        flex-direction: column-reverse;
        gap: 0.6rem;
        max-width: min(360px, calc(100vw - 2.5rem));
        list-style: none;
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        pointer-events: none;
    }

    .toast-item {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        overflow: hidden;
        padding: 0.75rem 0.875rem;
        border-radius: 0.875rem;
        background-color: var(--background-color);
        color: var(--content-color);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
        border: 1px solid rgba(15, 23, 42, 0.06);
        pointer-events: auto;

        opacity: 0;
        transform: translateX(16px) scale(0.98);
        transition: opacity 200ms ease, transform 200ms ease;
    }

    .toast-item.toast-show {
        opacity: 1;
        transform: translateX(0) scale(1);
    }

    .toast-item.toast-hide {
        opacity: 0;
        transform: translateX(16px) scale(0.98);
    }

    .toast-content {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        min-width: 0;
    }

    .toast-icon {
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }

    .toast-icon svg {
        width: var(--icon-size);
        height: var(--icon-size);
        color: var(--content-color);
    }

    .toast-text {
        font-size: var(--font-size-content);
        font-weight: 500;
        line-height: 1.4;
        user-select: none;
    }

    .toast-close {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background-color 150ms ease;
    }

    .toast-close svg {
        width: 0.85em;
        height: 0.85em;
        color: var(--content-color);
        opacity: 0.55;
    }

    .toast-close:hover {
        background-color: rgba(15, 23, 42, 0.06);
    }

    .toast-close:hover svg {
        opacity: 1;
    }

    .toast-progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 2px;
        width: 100%;
        background: var(--content-color);
        opacity: 0.25;
        transform: translateX(-100%);
        animation: shophop-toast-progress linear forwards;
    }

    @keyframes shophop-toast-progress {
        from { transform: translateX(-100%); }
        to   { transform: translateX(0); }
    }

    /* Types — tugma sa role/status badge colors ng ShopHop */

    .toast-success {
        --content-color: #059669;
        background-color: #ecfdf5;
        border-color: rgba(5, 150, 105, 0.15);
    }

    .toast-error {
        --content-color: #e11d48;
        background-color: #fef2f4;
        border-color: rgba(225, 29, 72, 0.15);
    }

    .toast-warning {
        --content-color: #b45309;
        background-color: #fffbeb;
        border-color: rgba(180, 83, 9, 0.15);
    }

    .toast-info {
        --content-color: #0369a1;
        background-color: #f0f9ff;
        border-color: rgba(3, 105, 161, 0.15);
    }
</style>

<script>
    (function () {
        const ICONS = {
            success: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            error: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
            warning: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
            info: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
            close: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
        };

        function showToast(message, type = 'success', duration = 5000) {
            const container = document.getElementById('shophop-toast-container');
            if (!container) return;

            const item = document.createElement('li');
            item.className = `toast-item toast-${type}`;
            item.innerHTML = `
                <div class="toast-content">
                    <span class="toast-icon">${ICONS[type] || ICONS.info}</span>
                    <span class="toast-text">${message}</span>
                </div>
                <span class="toast-close">${ICONS.close}</span>
                ${duration > 0 ? `<div class="toast-progress-bar" style="animation-duration:${duration}ms;"></div>` : ''}
            `;

            container.appendChild(item);
            requestAnimationFrame(() => item.classList.add('toast-show'));

            let timer = null;

            const removeToast = () => {
                clearTimeout(timer);
                item.classList.remove('toast-show');
                item.classList.add('toast-hide');
                setTimeout(() => item.remove(), 200);
            };

            item.querySelector('.toast-close').addEventListener('click', removeToast);

            if (duration > 0) {
                timer = setTimeout(removeToast, duration);
                item.addEventListener('mouseenter', () => clearTimeout(timer));
                item.addEventListener('mouseleave', () => {
                    timer = setTimeout(removeToast, 1500);
                });
            }
        }

        window.showToast = showToast;
    })();
</script>