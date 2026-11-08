{{-- Camera-assisted entry for authenticated Branch/Hub exception parcels.
     This component NEVER posts or changes custody by scanning alone. --}}
@php($scannerId = 'shopscan-'.str_replace(['_', ' '], '-', $inputId))
<div id="{{ $scannerId }}" class="mt-3 rounded-xl border border-gray-200 bg-slate-50 p-3" data-shopscan-target="{{ $inputId }}">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <p class="text-sm font-semibold text-slate-800">Scan parcel QR using webcam</p>
            <p class="mt-0.5 text-xs text-slate-500">Scan to fill the tracking field. Review the parcel and use the existing confirmation to receive.</p>
        </div>
        <button type="button" class="shopscan-toggle inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-600">
            <span aria-hidden="true">▣</span><span class="shopscan-label">Open camera</span>
        </button>
    </div>
    <div class="shopscan-camera mt-3 hidden overflow-hidden rounded-lg bg-slate-900" style="max-width:420px">
        <video muted playsinline aria-label="Live camera scanning preview" class="w-full" style="max-height:260px;object-fit:cover"></video>
    </div>
    <p class="shopscan-message mt-2 text-xs text-slate-500" role="status" aria-live="polite">Manual tracking entry is always available.</p>
</div>
@once
<script src="/js/shophop-web-qr.js"></script>
<script>
(() => {
  function initializeScanner(root) {
    if (root.dataset.shopscanReady === '1') return;
    root.dataset.shopscanReady = '1';
    const target = document.getElementById(root.dataset.shopscanTarget);
    const toggle = root.querySelector('.shopscan-toggle');
    const label = root.querySelector('.shopscan-label');
    const camera = root.querySelector('.shopscan-camera');
    const video = camera.querySelector('video');
    const message = root.querySelector('.shopscan-message');
    const reader = window.ShopHopQrCamera({
      video,
      onResult: raw => {
        const code = String(raw).trim().toUpperCase();
        camera.classList.add('hidden');
        label.textContent = 'Open camera';
        if (/^SHP-[0-9]{8}-[0-9]{6,}$/.test(code)) {
          target.value = code;
          target.dispatchEvent(new Event('input', {bubbles: true}));
          target.dispatchEvent(new Event('change', {bubbles: true}));
          target.focus();
          notice('Scanned ' + code + '. Review parcel details before confirming receipt.');
        } else {
          notice('Scanned QR is not a ShopHop parcel tracking number. Retry or type the tracking code.');
        }
      },
      onStatus: notice,
    });
    function notice(text) { message.textContent = text; }
    function stop() {
      reader.close();
      camera.classList.add('hidden');
      label.textContent = 'Open camera';
    }
    toggle.addEventListener('click', async () => {
      if (reader.isActive()) {stop(); notice('Camera stopped.'); return;}
      if (!target) {notice('Tracking field missing.'); return;}
      toggle.disabled = true;
      try {
        // Show preview before the browser starts playback.
        camera.classList.remove('hidden');
        await reader.open();
        label.textContent = 'Stop camera';
      } catch (error) {
        stop(); notice(error.message);
      } finally {toggle.disabled = false;}
    });
    document.addEventListener('visibilitychange', () => { if (document.hidden) stop(); });
    window.addEventListener('pagehide', stop);
  }
  function boot() { document.querySelectorAll('[data-shopscan-target]').forEach(initializeScanner); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
</script>
@endonce
