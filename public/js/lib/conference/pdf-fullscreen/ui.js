/**
 * conference/pdf-fullscreen/ui.js
 * Module UI pour la gestion du plein écran PDF
 */

import { _ } from '../../dom/selector.js';
import { addCsrfToken } from '../network.js';
import { calculateFullscreenScale, ensureFullscreenExited } from './core.js';
import { displayPDF } from '../pdf-viewer.js';
import { safeSetInterval, safeSetTimeout } from '../../utils/cleanup.js';
import { t } from '../../i18n.js';

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

  // Masquer la section d'import une fois le fichier sélectionné
  const importSection = _('import-section');
  if (importSection) {
    importSection.hidden = true;
  }

  const progressBar = _('presentation-progress');
  const progressStatus = _('progress-status');
  const dProg = _('d-prog');

  if (progressBar) {
    progressBar.value = 0;
  }
  if (progressStatus) {
    progressStatus.textContent = t('conference.progress.preparing');
  }
  if (dProg) {
    dProg.hidden = false;
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
        progressStatus.textContent = t('conference.progress.uploading', { n: progressBar.value });
      }
    }
  }, 300);

  fetch('/upload', {
    method: 'POST',
    body: addCsrfToken(formData),
  })
    .then((response) => {
      clearInterval(uploadProgress);

      if (progressBar) {
        progressBar.value = 75;
      }
      if (progressStatus) {
        progressStatus.textContent = t('conference.progress.converting');
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
        progressStatus.textContent = t('conference.progress.processing');
      }

      if (data.status === 'error') {
        throw new Error(data.message || 'Server error');
      }

      if (data.display_method === 'pdf') {
        if (progressStatus) {
          progressStatus.textContent = t('conference.progress.displaying');
        }
        // Afficher le canvas et les contrôles PDF (ottis-live-area reste visible)
        if (canvas) {
          canvas.hidden = false;
          canvas.style.display = 'block';
        }
        if (pdfControl) {
          pdfControl.hidden = false;
          pdfControl.style.display = 'block';
        }
        // Afficher le bouton de fermeture
        const closeBtn = _('pdf-close-btn');
        if (closeBtn) {
          closeBtn.hidden = false;
        }
        // pdf-viewer est déjà visible, ottis-live-area reste visible
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
        progressStatus.textContent = `${t('conference.progress.uploadFailed')}${error.message}`;
      }
      showPresentationError(`Upload failed: ${error.message}`);
    });
}

/**
 * Ferme la présentation et retourne à l'état initial
 */
export function closePresentation() {
  const importSection = _('import-section');
  const pdfViewer = _('pdf-viewer');
  const pdfControl = _('pdf-control');
  const canvas = _('pdf-canvas');
  const textViewer = _('text-content-viewer');
  const textfixe = _('textfixe');
  const closeBtn = _('pdf-close-btn');

  // Réafficher la section d'import
  if (importSection) {
    importSection.hidden = false;
  }

  // Masquer les éléments PDF
  if (pdfViewer) pdfViewer.style.visibility = 'hidden';
  if (canvas) {
    canvas.style.display = 'none';
    canvas.hidden = true;
  }
  if (pdfControl) {
    pdfControl.style.display = 'none';
    pdfControl.hidden = true;
  }
  if (textViewer) textViewer.style.display = 'none';

  // Réafficher textfixe
  if (textfixe) textfixe.style.display = 'block';

  // Masquer le bouton de fermeture
  if (closeBtn) {
    closeBtn.hidden = true;
  }

  // ottis-live-area reste visible (déplacé en dehors de pdf-viewer)

  // Réinitialiser le state PDF
  if (typeof setPdfDoc !== 'undefined') setPdfDoc(null);
  if (typeof setPageNum !== 'undefined') setPageNum(1);
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
      fullscreenBtn.textContent = t('conference.outil.exitFullscreen');
      fullscreenBtn.title = t('conference.outil.exitFullscreen');
    }
  } else if (!isFullscreen) {
    if (typeof window !== 'undefined' && window.console && window.console.info) {
      console.info('Sortie du plein écran (via state change)');
    }
    // Sortie par Echap : restaurer les styles si exitPdfFullscreen n'a pas encore agi
    ensureFullscreenExited();
    if (fullscreenBtn) {
      fullscreenBtn.textContent = t('conference.outil.fullscreen');
      fullscreenBtn.title = t('conference.outil.fullscreen');
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

/**
 * Configure l'écouteur pour le bouton de fermeture de la présentation
 */
export function setupPdfCloseListener() {
  const closeBtn = _('pdf-close-btn');
  if (closeBtn) {
    closeBtn.addEventListener('click', closePresentation);
  }
}
