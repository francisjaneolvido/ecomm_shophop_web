@once
<script>
window.ShopHopFavoriteIds = @json(array_values($likedProductIds ?? []));
window.ShopHopFavoriteBaseUrl = @json(url('/buyer/likes'));

(function () {
    function bootFavorites() {
        const likedIds = new Set((window.ShopHopFavoriteIds || []).map(Number));
        const baseUrl = window.ShopHopFavoriteBaseUrl || '/buyer/likes';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let toastTimer = null;

        function productIdFromButton(button) {
            const direct = Number(button.dataset.productId || 0);
            if (direct > 0) return direct;

            const scope = button.closest('article') || button.closest('[data-product-card]');
            const link = scope?.querySelector('a[href*="/buyer/product/"]');
            if (!link) return 0;

            const match = link.href.match(/\/buyer\/product\/(\d+)/);
            return match ? Number(match[1]) : 0;
        }

        function paint(button, liked) {
            const svg = button.querySelector('svg');
            button.dataset.liked = liked ? '1' : '0';
            button.setAttribute('aria-pressed', liked ? 'true' : 'false');
            button.setAttribute('aria-label', liked ? 'Remove from My Likes' : 'Add to My Likes');
            button.title = liked ? 'Remove from My Likes' : 'Add to My Likes';
            button.classList.toggle('text-rose-500', liked);
            button.classList.toggle('text-navy/55', !liked);
            button.classList.remove('cursor-not-allowed', 'opacity-50', 'opacity-55', 'text-navy/45');
            svg?.classList.toggle('fill-current', liked);

            const label = button.querySelector('[data-favorite-label]');
            if (label) label.textContent = liked ? 'Liked' : 'Add to My Likes';
        }

        function paintAll(productId, liked) {
            document.querySelectorAll('[data-favorite-toggle]').forEach(function (button) {
                if (Number(button.dataset.productId || 0) === productId) {
                    paint(button, liked);
                }
            });
        }

        function updateCount(count) {
            document.querySelectorAll('[data-favorite-count]').forEach(function (node) {
                node.textContent = String(count);
            });
        }

        function showToast(message) {
            let toast = document.getElementById('shopHopFavoriteToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'shopHopFavoriteToast';
                toast.className = 'fixed left-1/2 bottom-6 z-[80] -translate-x-1/2 translate-y-3 opacity-0 pointer-events-none bg-navy text-white text-[10px] font-semibold px-4 py-2.5 rounded-xl shadow-xl transition-all duration-200';
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            toast.classList.remove('translate-y-3', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(function () {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-3', 'opacity-0');
            }, 1800);
        }

        const candidates = document.querySelectorAll('button[data-wishlist-unavailable], button[data-favorite-toggle]');
        candidates.forEach(function (button) {
            const productId = productIdFromButton(button);
            if (!productId) return;

            button.disabled = false;
            button.removeAttribute('data-wishlist-unavailable');
            button.dataset.favoriteToggle = '';
            button.dataset.productId = String(productId);
            paint(button, likedIds.has(productId));
        });

        document.addEventListener('click', async function (event) {
            const button = event.target.closest('[data-favorite-toggle]');
            if (!button || button.dataset.busy === '1') return;

            event.preventDefault();
            event.stopPropagation();

            const productId = Number(button.dataset.productId || 0);
            if (!productId) return;

            const currentlyLiked = likedIds.has(productId);
            button.dataset.busy = '1';
            button.disabled = true;

            try {
                const response = await fetch(`${baseUrl}/${productId}`, {
                    method: currentlyLiked ? 'DELETE' : 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.ok === false) {
                    throw new Error(payload.message || 'Unable to update My Likes.');
                }

                const liked = payload.liked === true;
                if (liked) likedIds.add(productId);
                else likedIds.delete(productId);

                paintAll(productId, liked);
                updateCount(Number(payload.favorites_count ?? likedIds.size));
                showToast(liked ? 'Added to My Likes' : 'Removed from My Likes');

                if (!liked) {
                    document.querySelectorAll(`[data-favorite-card="${productId}"]`).forEach(function (card) {
                        card.remove();
                    });
                    const grid = document.querySelector('[data-favorites-grid]');
                    const empty = document.querySelector('[data-favorites-empty]');
                    if (grid && empty && grid.children.length === 0) {
                        empty.classList.remove('hidden');
                    }
                }
            } catch (error) {
                showToast(error?.message || 'Unable to update My Likes.');
            } finally {
                button.dataset.busy = '0';
                button.disabled = false;
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootFavorites, { once: true });
    } else {
        bootFavorites();
    }
})();
</script>
@endonce
