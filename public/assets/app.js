(() => {
  'use strict';

  const timeZone = 'Asia/Makassar';
  const byId = (id) => document.getElementById(id);
  const interfaceLanguage = document.documentElement.lang === 'en' ? 'en-US' : 'id-ID';
  let cameraStream = null;
  let cameraTimer = null;

  function formatDate(options) {
    return new Intl.DateTimeFormat(interfaceLanguage, { ...options, timeZone }).format(new Date());
  }

  function updateClock() {
    const clock = byId('live-clock');

    if (clock) {
      clock.textContent = formatDate({
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hourCycle: 'h23',
      });
    }

    const date = byId('today-label');
    if (date) {
      date.textContent = formatDate({ weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
    }
  }

  function closeCamera() {
    if (cameraTimer) {
      clearTimeout(cameraTimer);
    }
    cameraTimer = null;

    if (cameraStream) {
      cameraStream.getTracks().forEach((track) => track.stop());
    }
    cameraStream = null;

    const video = byId('camera-video');
    if (video) {
      video.srcObject = null;
    }

    byId('camera-placeholder')?.classList.remove('hidden');
  }

  function closeModals() {
    document.querySelectorAll('.modal-backdrop').forEach((modal) => modal.classList.add('hidden'));
    closeCamera();
  }

  async function openCamera() {
    const modal = byId('camera-modal');
    const message = byId('camera-message');
    const video = byId('camera-video');
    const form = byId('scan-form');

    if (!modal || !message || !video || !form) {
      return;
    }

    modal.classList.remove('hidden');
    message.textContent = document.body.dataset.cameraPreparing;

    if (!('BarcodeDetector' in window) || !navigator.mediaDevices?.getUserMedia) {
      message.textContent = document.body.dataset.cameraUnsupported;
      return;
    }

    try {
      if (typeof window.BarcodeDetector.getSupportedFormats !== 'function') {
        message.textContent = document.body.dataset.cameraUnavailable;
        return;
      }

      const supportedFormats = await window.BarcodeDetector.getSupportedFormats();
      if (!supportedFormats.includes('qr_code')) {
        message.textContent = document.body.dataset.qrUnsupported;
        return;
      }

      cameraStream = await navigator.mediaDevices.getUserMedia({
        audio: false,
        video: { facingMode: { ideal: 'environment' } },
      });
      video.srcObject = cameraStream;
      await video.play();
      byId('camera-placeholder')?.classList.add('hidden');
      const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
      message.textContent = document.body.dataset.aimQr;
      scanCameraFrame(detector, video, form);
    } catch {
      message.textContent = document.body.dataset.cameraOpenFailed;
      closeCamera();
    }
  }

  async function scanCameraFrame(detector, video, form) {
    if (!cameraStream || !video.srcObject) {
      return;
    }

    try {
      const results = await detector.detect(video);
      if (results.length > 0) {
        const input = byId('scan-input');
        const code = String(results[0].rawValue ?? '').trim().toUpperCase();

        if (code && input) {
          input.value = code;
          byId('scan-source').value = 'Kamera';
          closeCamera();
          form.requestSubmit();
          return;
        }
      }
    } catch {
      // Frames that are blurred or not ready yet can be scanned on the next pass.
    }

    cameraTimer = window.setTimeout(() => scanCameraFrame(detector, video, form), 280);
  }

  function showLegacyImport() {
    const notice = byId('legacy-import-notice');
    const button = byId('legacy-import-button');

    if (!notice || !button) {
      return;
    }

    let users;
    let logs;

    try {
      users = JSON.parse(window.localStorage.getItem('kantin-attendance-users-v1') || '[]');
      logs = JSON.parse(window.localStorage.getItem('kantin-attendance-logs-v1') || '[]');
    } catch {
      return;
    }

    if ((!Array.isArray(users) || users.length === 0) && (!Array.isArray(logs) || logs.length === 0)) {
      return;
    }

    notice.classList.remove('hidden');
    button.addEventListener('click', async () => {
      button.disabled = true;
      button.textContent = document.body.dataset.importing;
      const token = document.querySelector('meta[name="csrf-token"]')?.content;

      try {
        const response = await fetch(notice.dataset.importUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token || '',
          },
          body: JSON.stringify({ users, logs }),
        });
        const result = await response.json();

        if (!response.ok) {
          const validationMessage = Object.values(result.errors || {}).flat()[0];
          throw new Error(validationMessage || result.message || document.body.dataset.legacyImportFailed);
        }

        window.localStorage.removeItem('kantin-attendance-users-v1');
        window.localStorage.removeItem('kantin-attendance-logs-v1');
        window.location.reload();
      } catch (error) {
        button.disabled = false;
        button.textContent = document.body.dataset.tryAgain;
        const message = document.createElement('span');
        message.className = 'field-error';
        message.textContent = error instanceof Error ? error.message : document.body.dataset.legacyImportFailed;
        notice.append(message);
      }
    }, { once: true });
  }

  document.querySelectorAll('[data-close-modal]').forEach((button) => {
    button.addEventListener('click', closeModals);
  });
  document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
    backdrop.addEventListener('click', (event) => {
      if (event.target === backdrop) {
        closeModals();
      }
    });
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeModals();
    }
  });
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm || document.body.dataset.confirmAction)) {
        event.preventDefault();
      }
    });
  });

  byId('open-camera')?.addEventListener('click', openCamera);
  updateClock();
  window.setInterval(updateClock, 1000);

  if (document.body.dataset.page === 'admin') {
    showLegacyImport();
  }

  if (document.body.dataset.autoPrint === 'true') {
    document.querySelector('[data-print-now]')?.addEventListener('click', () => window.print());

    const qrImage = document.querySelector('.barcode-print-card img');
    const printWhenReady = () => window.setTimeout(() => window.print(), 250);

    if (qrImage?.decode) {
      qrImage.decode().then(printWhenReady).catch(printWhenReady);
    } else {
      printWhenReady();
    }
  }
})();
