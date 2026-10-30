/* ShopHop Web QR camera helper. Shared by Branch and Province Hub pages.
 * Opens the camera without depending on BarcodeDetector, and uses jsQR fallback
 * for QR decoding. Scanning only fills a form; never performs a server action.
 */
(() => {
  'use strict';
  const fallbackUrl = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js';
  let fallbackPromise = null;
  function loadQrFallback() {
    if (typeof window.jsQR === 'function') return Promise.resolve();
    if (fallbackPromise) return fallbackPromise;
    fallbackPromise = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = fallbackUrl;
      script.async = true;
      script.onload = () => typeof window.jsQR === 'function' ? resolve() : reject(new Error('QR decoder unavailable.'));
      script.onerror = () => reject(new Error('Could not load QR decoder. Check your internet connection.'));
      document.head.appendChild(script);
    }).catch(error => { fallbackPromise = null; throw error; });
    return fallbackPromise;
  }
  function friendlyCameraError(error) {
    const name = error?.name || '';
    if (name === 'NotAllowedError' || name === 'PermissionDeniedError' || name === 'SecurityError')
      return 'Camera access denied. Click the camera/lock icon beside the browser URL, allow Camera, and reload.';
    if (name === 'NotFoundError' || name === 'DevicesNotFoundError')
      return 'No usable webcam detected. Connect a webcam or use manual tracking entry.';
    if (name === 'NotReadableError' || name === 'TrackStartError')
      return 'Camera is being used by another application. Close Zoom/Teams/other camera apps and retry.';
    if (name === 'OverconstrainedError')
      return 'Camera does not support the requested settings. Please retry or use manual entry.';
    return `Unable to start camera: ${error?.message || 'Unknown browser error'}. Check permissions.`;
  }
  window.ShopHopQrCamera = function createQrCamera(options) {
    const video = options.video;
    const onResult = options.onResult || (() => {});
    const onStatus = options.onStatus || (() => {});
    let stream = null, active = false, pending = false, detector = null;
    let frameRequest = null, lastFrameAt = 0, decoding = false, session = 0;
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', {willReadFrequently: true});
    async function scanFrame(timestamp) {
      if (!active) return;
      frameRequest = requestAnimationFrame(scanFrame);
      if (decoding || timestamp - lastFrameAt < 180 || video.readyState < 2) return;
      lastFrameAt = timestamp;
      decoding = true;
      try {
        let text = '';
        if (detector) {
          const found = await detector.detect(video);
          text = String(found?.[0]?.rawValue || '').trim();
        } else if (typeof window.jsQR === 'function' && context && video.videoWidth && video.videoHeight) {
          // Bound the decode size to reduce CPU load on laptop/mobile browsers.
          const width = Math.min(video.videoWidth, 640);
          const height = Math.max(1, Math.round(video.videoHeight * width / video.videoWidth));
          if (canvas.width !== width || canvas.height !== height) {canvas.width = width; canvas.height = height;}
          context.drawImage(video, 0, 0, width, height);
          const pixels = context.getImageData(0, 0, width, height);
          const result = window.jsQR(pixels.data, width, height, {inversionAttempts: 'dontInvert'});
          text = String(result?.data || '').trim();
        }
        if (text && active) {close(); onResult(text);}
      } catch (error) {
        // Some frames can fail while focus/exposure changes; continue scanning.
      } finally {decoding = false;}
    }
    async function open() {
      if (active || pending) return;
      if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia)
        throw new Error('Camera needs a secure browser page: use HTTPS or localhost, then retry.');
      pending = true;
      const requestedSession = ++session;
      try {
        // IMPORTANT: request camera BEFORE testing QR-decoding support.
        stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: {ideal: 'environment'}}, audio: false});
        if (requestedSession !== session) {stream.getTracks().forEach(track => track.stop()); return;}
        video.srcObject = stream;
        video.muted = true;
        video.setAttribute('playsinline', '');
        await video.play();
        active = true;
        try {
          if (window.BarcodeDetector) detector = new window.BarcodeDetector({formats: ['qr_code']});
        } catch (_) {detector = null;}
        if (detector) {
          onStatus('Camera ready. Point it at a parcel QR code. Review before confirming.');
        } else {
          onStatus('Camera ready. Loading compatible QR decoder...');
          loadQrFallback().then(() => {
            if (active && requestedSession === session) onStatus('Camera ready (compatible QR reader). Point it at the QR code.');
          }).catch(error => {
            if (active && requestedSession === session) onStatus(`${error.message} Camera preview works; type the tracking code manually if needed.`);
          });
        }
        frameRequest = requestAnimationFrame(scanFrame);
      } catch (error) {
        close();
        throw new Error(friendlyCameraError(error));
      } finally {pending = false;}
    }
    function close() {
      session++;
      active = false;
      if (frameRequest !== null) cancelAnimationFrame(frameRequest);
      frameRequest = null;
      if (stream) stream.getTracks().forEach(track => track.stop());
      stream = null;
      if (video.srcObject) video.srcObject = null;
      try {video.pause();} catch (_) {}
    }
    return {open, close, isActive: () => active};
  };
})();
