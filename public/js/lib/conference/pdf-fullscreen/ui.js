/**
 * conference/pdf-fullscreen/ui.js
 * Module UI pour la gestion du plein écran PDF
 */

import { _ } from '../../dom/selector.js';
import { addCsrfToken } from '../network.js';
import { calculateFullscreenScale } from './core.js';
import { displayPDF } from '../pdf-viewer.js';
import { safeSetInterval, safeSetTimeout } from '../../utils/cleanup.js';

// Alias pour compatibilité avec l'ancien code
const get = _;

// =============================================================================
// Gestion des événements et upload
// =============================================================================

/**
 * Affiche une erreur de présentation
 * @param {string} message - Le message d'erreur
 */
export function showPresentationError(message) {
  const statusEl = _('progress-status');
  if (statusEl) {
    if (typeof DOMPurify !== 'undefined') {
      statusEl.innerHTML = DOMPurify.sanitize(`<span style="color: red; font-weight: bold;">ERREUR: ${message}</span>`);
    } else {
      statusEl.innerHTML = `<span style="color: red; font-weight: bold;">ERREUR: ${message}</span>`;
    }
  }
  if (typeof window !== 'undefined' && window.console && window.console.error) {
    console.error('Présentation error', { message });
  }
}

/**
 * Gère la sélection de fichier PDF
 * @param {Event} event - L'événement de sélection de fichier
 */
export function handleFileSelect(event) {
  const fileInput = event.target;
  const file = fileInput.files[0];

  if (typeof window !== 'undefined' && window.console && window.console.info) {
    console.info('handleFileSelect: Fichier sélectionné', { fileName: file?.name, fileSize: file?.size });
  }

  if (!file) {
    if (typeof window !== 'undefined' && window.console && window.console.warn) {
      console.warn('Aucun fichier sélectionné');
    }
    return;
  }

  const progressBar = _('presentation-progress');
  const progressStatus = _('progress-status');

  if (progressBar) {
    progressBar.value = 0;
  }
  if (progressStatus) {
    progressStatus.textContent = 'Preparing upload...';
  }

  const pdfViewer = _('pdf-viewer');
  const textViewer = _('text-content-viewer');
  const pdfControl = _('pdf-control');
  const canvas = _('pdf-canvas');
  if (pdfViewer) pdfViewer.style.visibility = 'hidden';
  if (canvas) canvas.style.display = 'none';
  if (textViewer) textViewer.style.display = 'none';
  if (pdfControl) pdfControl.style.display = 'none';

  const formData = new FormData();
  formData.append('presentation_file', file);

  const uploadProgress = safeSetInterval(() => {
    if (progressBar && progressBar.value < 50) {
      progressBar.value = Math.min(progressBar.value + 10, 50);
      if (progressStatus) {
        progressStatus.textContent = `Uploading... ${progressBar.value}%`;
      }
    }
  }, 300);

  fetch('/upload/handle', {
    method: 'POST',
    body: addCsrfToken(formData),
  })
    .then((response) => {
      clearInterval(uploadProgress);

      if (progressBar) {
        progressBar.value = 75;
      }
      if (progressStatus) {
        progressStatus.textContent = 'Converting...';
      }

      return response.json().catch((err) => {
        throw new Error(`Invalid JSON response: ${err.message}`);
      });
    })
    .then((data) => {
      if (progressBar) {
        progressBar.value = 100;
      }
      if (progressStatus) {
        progressStatus.textContent = 'Processing...';
      }

      if (data.status === 'error') {
        throw new Error(data.message || 'Server error');
      }

      if (data.display_method === 'pdf') {
        if (progressStatus) {
          progressStatus.textContent = 'Displaying...';
        }
        displayPDF(data.url);
      } else {
        showPresentationError(`Unexpected file type: ${data.file_type}`);
      }
    })
    .catch((error) => {
      clearInterval(uploadProgress);
      if (progressBar) {
        progressBar.value = 0;
      }
      if (progressStatus) {
        progressStatus.textContent = `Error: ${error.message}`;
      }
      showPresentationError(`Upload failed: ${error.message}`);
    });
}

/**
 * Gère le changement d'état du plein écran
 */
export function handleFullscreenStateChange() {
  const isFullscreen = document.fullscreenElement
                        || document.webkitFullscreenElement
                        || document.mozFullScreenElement
                        || document.msFullscreenElement;

  const fullscreenBtn = _('pdf-fullscreen-btn');
  const pdfViewer = _('pdf-viewer');

  const isPdfFullscreen = pdfViewer && (
    pdfViewer === document.fullscreenElement
        || pdfViewer === document.webkitFullscreenElement
        || pdfViewer === document.mozFullScreenElement
        || pdfViewer === document.msFullscreenElement
  );

  if (isFullscreen && isPdfFullscreen) {
    if (fullscreenBtn) {
      fullscreenBtn.textContent = 'Exit Fullscreen';
      fullscreenBtn.title = 'Exit fullscreen mode';
    }
  } else if (!isFullscreen) {
    if (typeof window !== 'undefined' && window.console && window.console.info) {
      console.info('Sortie du plein écran (via state change)');
    }
    if (fullscreenBtn) {
      fullscreenBtn.textContent = 'Fullscreen';
      fullscreenBtn.title = 'Toggle fullscreen mode';
    }
  }
}

/**
 * Configure les écouteurs d'événements pour le plein écran PDF
 */
export function setupPdfFullscreenListeners() {
  document.addEventListener('fullscreenchange', handleFullscreenStateChange);
  document.addEventListener('webkitfullscreenchange', handleFullscreenStateChange);
  document.addEventListener('mozfullscreenchange', handleFullscreenStateChange);
  document.addEventListener('MSFullscreenChange', handleFullscreenStateChange);

  window.addEventListener('resize', () => {
    const isFullscreen = document.fullscreenElement
                            || document.webkitFullscreenElement
                            || document.mozFullScreenElement
                            || document.msFullscreenElement;

    if (isFullscreen) {
      safeSetTimeout(() => {
        calculateFullscreenScale();
      }, 100);
    }
  });
}
