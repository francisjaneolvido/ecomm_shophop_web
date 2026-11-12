{{-- Single light appearance; retire saved choices from the former theme switcher. --}}
<meta name="color-scheme" content="light">
<meta name="theme-color" content="#FBF6EE">
<script>
(function () {
    const root = document.documentElement;
    root.style.colorScheme = 'light';
    root.setAttribute('data-theme', 'light');
    root.removeAttribute('data-theme-switching');
    root.classList.remove('dark', 'oled');
    try { localStorage.removeItem('shophop-theme'); } catch (_) {}
})();
</script>
