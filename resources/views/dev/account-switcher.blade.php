@if (app()->environment('local') && (request()->is('__dev/preview/*') || request()->path() === '/'))
    @php
        $isPreview = request()->is('__dev/preview/*');
        $previewRole = $isPreview ? request()->segment(3) : null;
        $previewRoles = [
            'buyer' => ['label' => 'Buyer', 'route' => 'dev.preview.buyer.messages'],
            'seller' => ['label' => 'Seller', 'route' => 'dev.preview.seller.dashboard'],
            'admin' => ['label' => 'Admin', 'route' => 'dev.preview.admin.chat'],
            'logistics' => ['label' => 'Logistics', 'route' => 'dev.preview.logistics.dashboard'],
        ];
    @endphp

    {{-- LOCAL DEV MODES:
         - TEST bypasses auth for frontend role previews.
         - DEMO uses the normal login/application flow.
         - APP_ENV=local only; remove this block with local preview routes. --}}
    <aside class="dev-account-switcher" aria-label="Local development modes">
        <div class="dev-account-switcher__label">LOCAL DEVELOPMENT MODE</div>
        <button type="button" class="dev-account-switcher__toggle" data-dev-mode-toggle aria-pressed="false">TEST MODE</button>
        <div class="dev-account-switcher__hint" data-dev-mode-hint>Switch to DEMO MODE</div>
        @if ($isPreview)
            <div class="dev-account-switcher__context" data-dev-role-context>TEST MODE · {{ strtoupper($previewRole) }}</div>
            <div class="dev-account-switcher__roles" data-dev-role-switcher aria-label="TEST MODE role switcher">
            @foreach ($previewRoles as $role => $preview)
                <a
                    href="{{ route($preview['route']) }}"
                    @class(['is-active' => $previewRole === $role])
                    @if ($previewRole === $role) aria-current="page" @endif
                >
                    {{ $preview['label'] }}
                </a>
            @endforeach
            </div>
        @endif
    </aside>

    <style>
        .dev-account-switcher {
            position: fixed;
            left: .75rem;
            bottom: .75rem;
            z-index: 120;
            max-width: calc(100vw - 1.5rem);
            padding: .45rem;
            border: 1px solid var(--sh-border);
            border-radius: .75rem;
            background: color-mix(in srgb, var(--sh-surface) 94%, transparent);
            box-shadow: 0 10px 30px var(--sh-shadow);
            color: var(--sh-text);
            backdrop-filter: blur(12px);
        }

        .dev-account-switcher__label {
            display: block;
            padding: .15rem .35rem .4rem;
            color: var(--sh-muted);
            font-size: .5625rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-decoration: none;
        }

        .dev-account-switcher__roles {
            display: flex;
            gap: .25rem;
            overflow-x: auto;
        }

        .dev-account-switcher__context {
            padding: 0 .35rem .35rem;
            color: var(--sh-brand-strong);
            font-size: .5rem;
            font-weight: 800;
            letter-spacing: .06em;
        }

        .dev-account-switcher__toggle { width: 100%; min-height: 2rem; padding: 0 .7rem; border: 1px solid var(--sh-brand); border-radius: .5rem; background: var(--sh-brand-soft); color: var(--sh-brand-strong); font-size: .625rem; font-weight: 800; cursor: pointer; }
        .dev-account-switcher__hint { padding: .15rem .35rem .35rem; color: var(--sh-muted); font-size: .5625rem; font-weight: 800; letter-spacing: .06em; }

        .dev-account-switcher__roles a {
            display: inline-flex;
            min-height: 2rem;
            align-items: center;
            padding: 0 .55rem;
            border-radius: .5rem;
            color: var(--sh-muted);
            font-size: .625rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        .dev-account-switcher__roles a:hover,
        .dev-account-switcher__roles a.is-active {
            background: var(--sh-brand-soft);
            color: var(--sh-brand-strong);
        }
    </style>

    <script>
        // Refactor: one local mode control owns persistence, navigation, and role-switch visibility.
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.querySelector('[data-dev-mode-toggle]');
            if (!toggle) return;
            const key = 'shophop-dev-mode';
            const isPreview = @json($isPreview);
            const roleSwitcher = document.querySelector('[data-dev-role-switcher]');
            const roleContext = document.querySelector('[data-dev-role-context]');
            const hint = document.querySelector('[data-dev-mode-hint]');
            const savedMode = window.localStorage.getItem(key);
            let mode = savedMode === 'test' || savedMode === 'demo'
                ? savedMode
                : (isPreview ? 'test' : 'demo');

            // DEMO never leaves a local preview surface usable after refresh or a pasted URL.
            if (mode === 'demo' && isPreview) {
                window.location.replace(@json(route('login')));
                return;
            }

            const sync = function () {
                const isTest = mode === 'test';
                toggle.textContent = isTest ? 'TEST MODE' : 'DEMO MODE';
                toggle.setAttribute('aria-label', isTest ? 'Switch to DEMO MODE' : 'Switch to TEST MODE');
                toggle.setAttribute('aria-pressed', String(isTest));
                if (hint) hint.textContent = isTest ? 'Switch to DEMO MODE' : 'Switch to TEST MODE';
                if (roleSwitcher) roleSwitcher.hidden = !isTest;
                if (roleContext) roleContext.hidden = !isTest;
            };

            window.localStorage.setItem(key, mode);
            sync();

            if (isPreview) {
                // TEST routing: exact normal-route to preview-route pairs keep role navigation local.
                // Longest matches win so parameterized product/order URLs use their list preview.
                const previewPaths = {
                    buyer: {
                        // Buyer dashboard requires missing product-schema data, so Messages is the
                        // first renderable preview surface until a real frontend fixture exists.
                        '/': @json(route('dev.preview.buyer.messages')),
                        '/buyer/dashboard': @json(route('dev.preview.buyer.messages')),
                        '/buyer/categories': @json(route('dev.preview.buyer.categories')),
                        '/buyer/category': @json(route('dev.preview.buyer.categories')),
                        '/buyer/product-preview': @json(route('dev.preview.buyer.products')),
                        '/buyer/product': @json(route('dev.preview.buyer.products')),
                        '/buyer/cart/checkout': @json(route('dev.preview.buyer.checkout')),
                        '/buyer/cart': @json(route('dev.preview.buyer.cart')),
                        '/buyer/orders': @json(route('dev.preview.buyer.orders')),
                        '/buyer/messages': @json(route('dev.preview.buyer.messages')),
                        '/buyer/profile': @json(route('dev.preview.buyer.profile')),
                        '/buyer/store': @json(route('dev.preview.buyer.storefront')),
                    },
                    seller: {
                        '/': @json(route('dev.preview.seller.dashboard')),
                        '/seller/dashboard': @json(route('dev.preview.seller.dashboard')),
                        '/seller/inventory': @json(route('dev.preview.seller.inventory')),
                        '/seller/orders': @json(route('dev.preview.seller.orders')),
                        '/seller/feedback': @json(route('dev.preview.seller.feedback')),
                        '/seller/reports': @json(route('dev.preview.seller.reports')),
                        '/seller/chat': @json(route('dev.preview.seller.chat')),
                        '/seller/account': @json(route('dev.preview.seller.account')),
                        '/seller/storefront': @json(route('dev.preview.seller.storefront')),
                    },
                    admin: {
                        '/': @json(route('dev.preview.admin.dashboard')),
                        '/admin/dashboard': @json(route('dev.preview.admin.dashboard')),
                        '/admin/registration': @json(route('dev.preview.admin.registrations')),
                        '/admin/users': @json(route('dev.preview.admin.users')),
                        '/admin/seller-compliance': @json(route('dev.preview.admin.compliance')),
                        '/admin/complaints-disputes': @json(route('dev.preview.admin.disputes')),
                        '/admin/commission': @json(route('dev.preview.admin.commission')),
                        '/admin/reports': @json(route('dev.preview.admin.reports')),
                        '/admin/chat': @json(route('dev.preview.admin.chat')),
                        '/admin/settings': @json(route('dev.preview.admin.settings')),
                        '/admin/accounts': @json(route('dev.preview.admin.accounts')),
                    },
                    logistics: {
                        '/': @json(route('dev.preview.logistics.dashboard')),
                        '/logistics-partner/dashboard': @json(route('dev.preview.logistics.dashboard')),
                        '/logistics-partner/riders': @json(route('dev.preview.logistics.riders')),
                        '/logistics-partner/deliveries': @json(route('dev.preview.logistics.deliveries')),
                        '/logistics-partner/reports': @json(route('dev.preview.logistics.reports')),
                    },
                };
                const rolePaths = previewPaths[@json($previewRole)] || {};
                document.querySelectorAll('a[href]').forEach(function (link) {
                    const url = new URL(link.href, window.location.href);
                    const match = Object.keys(rolePaths).sort(function (left, right) {
                        return right.length - left.length;
                    }).find(function (path) {
                        return url.pathname === path || (path !== '/' && url.pathname.indexOf(path + '/') === 0);
                    });
                    if (!match) return;
                    link.href = rolePaths[match] + url.search + url.hash;
                });
            }

            toggle.addEventListener('click', function () {
                mode = mode === 'test' ? 'demo' : 'test';
                window.localStorage.setItem(key, mode);
                window.location.href = mode === 'test'
                    ? (isPreview ? window.location.href : @json(route('dev.preview.buyer.messages')))
                    : @json(route('login'));
            });
        });
    </script>
@endif
