/**
 * conference/pdf-fullscreen/core.js
 * Module core pour la gestion du plein écran PDF
 */

import {
  canvas, fullscreenScale, initialClientHeight, initialClientWidth,
  isCalculatingFullscreenScale, originalPdfControlStyle, originalPdfScale,
  originalTextBandDisplay, originalTextBandStyle, originalTextBandVisibility,
  originalTextBandParent, originalTextBandNextSibling, originalPdfControlParent,
  originalPdfControlNextSibling, originalPdfViewerStyle, originalCanvasDisplay,
  originalCanvasWidth, originalCanvasHeight, pageNum, pageRendering, pdfDoc, scale,
  setPageNum, setScale, setPdfDoc, setPageRendering, setFullscreenScale,
  setIsCalculatingFullscreenScale, setOriginalCanvasStyle, setOriginalCanvasDisplay,
  setOriginalCanvasWidth, setOriginalCanvasHeight, setOriginalPdfControlStyle,
  setOriginalTextBandDisplay, setOriginalTextBandStyle, setOriginalTextBandVisibility,
  setOriginalTextBandParent, setOriginalTextBandNextSibling, setOriginalPdfControlParent,
  setOriginalPdfControlNextSibling, setOriginalPdfViewerStyle, setOriginalPdfScale,
} from '../state.js';

import { _ } from '../dom-helpers.js';
import { renderPage } from '../pdf-viewer.js';

// Alias pour compatibilité avec l'ancien code
const get = _;

// =============================================================================
// Logger pour le débogage du plein écran PDF
// =============================================================================

const LOG_LEVEL = {
  DEBUG: 0,
  INFO: 1,
  WARN: 2,
  ERROR: 3,
};

let currentLogLevel = LOG_LEVEL.DEBUG;

export function setPdfFullscreenLogLevel(level) {
  currentLogLevel = level;
}

export function logPdfDebug(message, data = {}) {
  if (currentLogLevel <= LOG_LEVEL.DEBUG) {
    console.log(`[PDF-FS DEBUG] ${message}`, data);
  }
}

export function logPdfInfo(message, data = {}) {
  if (currentLogLevel <= LOG_LEVEL.INFO) {
    console.info(`[PDF-FS INFO] ${message}`, data);
  }
}

export function logPdfWarn(message, data = {}) {
  if (currentLogLevel <= LOG_LEVEL.WARN) {
    console.warn(`[PDF-FS WARN] ${message}`, data);
  }
}

export function logPdfError(message, data = {}) {
  if (currentLogLevel <= LOG_LEVEL.ERROR) {
    console.error(`[PDF-FS ERROR] ${message}`, data);
  }
}

// =============================================================================
// Fonctions de gestion du plein écran
// =============================================================================

export function exitFullscreenAsync() {
  return new Promise((resolve) => {
    const isFullscreen = document.fullscreenElement
                           || document.webkitFullscreenElement
                           || document.mozFullScreenElement
                           || document.msFullscreenElement;

    if (!isFullscreen) {
      resolve();
      return;
    }

    const onFullscreenChange = () => {
      const stillFullscreen = document.fullscreenElement
                                   || document.webkitFullscreenElement
                                   || document.mozFullScreenElement
                                   || document.msFullscreenElement;

      if (!stillFullscreen) {
        document.removeEventListener('fullscreenchange', onFullscreenChange);
        document.removeEventListener('webkitfullscreenchange', onFullscreenChange);
        document.removeEventListener('mozfullscreenchange', onFullscreenChange);
        document.removeEventListener('MSFullscreenChange', onFullscreenChange);
        resolve();
      }
    };

    document.addEventListener('fullscreenchange', onFullscreenChange);
    document.addEventListener('webkitfullscreenchange', onFullscreenChange);
    document.addEventListener('mozfullscreenchange', onFullscreenChange);
    document.addEventListener('MSFullscreenChange', onFullscreenChange);

    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    }
  });
}

export function GoFS() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    document.exitFullscreen();
    _('bar').style.transform = 'scaleY(1)';
  }
}

export function saveCurrentCanvasStyles() {
  const canvasEl = _('pdf-canvas');
  const textBandEl = _('textBand');
  const pdfControlEl = _('pdf-control');
  const pdfViewerEl = _('pdf-viewer');

  if (pdfViewerEl) {
    setOriginalPdfViewerStyle(pdfViewerEl.style.cssText);
  } else {
    logPdfWarn('PDF Viewer introuvable');
  }

  if (canvasEl) {
    setOriginalCanvasDisplay(canvasEl.style.display);
    setOriginalCanvasWidth(canvasEl.width);
    setOriginalCanvasHeight(canvasEl.height);
  } else {
    logPdfWarn('Canvas pdf-canvas introuvable');
  }

  if (textBandEl) {
    setOriginalTextBandParent(textBandEl.parentNode);
    setOriginalTextBandNextSibling(textBandEl.nextSibling);
    setOriginalTextBandDisplay(textBandEl.style.display);
    setOriginalTextBandVisibility(textBandEl.style.visibility);
    setOriginalTextBandStyle(textBandEl.style.cssText);
  } else {
    logPdfWarn('TextBand introuvable');
  }

  if (pdfControlEl) {
    setOriginalPdfControlParent(pdfControlEl.parentNode);
    setOriginalPdfControlNextSibling(pdfControlEl.nextSibling);

    const controlButtons = pdfControlEl.querySelectorAll('button, input, span');
    const savedButtonStyles = [];
    controlButtons.forEach((el) => {
      savedButtonStyles.push({
        tagName: el.tagName,
        background: el.style.background,
        color: el.style.color,
        border: el.style.border,
        display: el.style.display,
        visibility: el.style.visibility,
      });
    });

    setOriginalPdfControlStyle({
      cssText: pdfControlEl.style.cssText,
      buttonStyles: savedButtonStyles,
    });
  } else {
    logPdfWarn('PDF Control introuvable');
  }
}

export function applyFullscreenCanvasStyles() {
  const canvasEl = _('pdf-canvas');
  const textBandEl = _('textBand');
  const pdfControlEl = _('pdf-control');
  const pdfViewerEl = _('pdf-viewer');

  if (pdfViewerEl) {
    pdfViewerEl.style.position = 'fixed';
    pdfViewerEl.style.top = '0';
    pdfViewerEl.style.left = '0';
    pdfViewerEl.style.width = '100vw';
    pdfViewerEl.style.height = 'calc(100vh - 171px)';
    pdfViewerEl.style.zIndex = '9999';
    pdfViewerEl.style.margin = '0';
    pdfViewerEl.style.padding = '0';
    pdfViewerEl.style.overflow = 'hidden';
    pdfViewerEl.style.boxSizing = 'border-box';
  } else {
    logPdfError('pdf-viewer introuvable, application des styles annulée');
    return;
  }

  if (textBandEl) {
    textBandEl.style.position = 'fixed';
    textBandEl.style.bottom = '0';
    textBandEl.style.left = '0';
    textBandEl.style.right = '0';
    textBandEl.style.height = '100px';
    textBandEl.style.zIndex = '10000';
  }

  if (pdfControlEl) {
    pdfControlEl.style.position = 'fixed';
    pdfControlEl.style.bottom = '100px';
    pdfControlEl.style.left = '0';
    pdfControlEl.style.right = '0';
    pdfControlEl.style.height = '70px';
    pdfControlEl.style.zIndex = '10000';
  }

  if (canvasEl) {
    canvasEl.style.display = 'block';
    canvasEl.style.margin = '0';
    canvasEl.style.padding = '0';
    canvasEl.style.boxSizing = 'border-box';
    canvasEl.style.maxWidth = '100%';
  } else {
    logPdfWarn('Canvas introuvable');
  }

  if (pdfControlEl) {
    pdfControlEl.style.background = 'rgba(0, 0, 0, 0.8)';
    pdfControlEl.style.display = 'block';
    pdfControlEl.style.boxSizing = 'border-box';

    const controlButtons = pdfControlEl.querySelectorAll('button, input, span');
    controlButtons.forEach((el) => {
      if (el.tagName === 'BUTTON') {
        el.style.background = 'rgba(255, 255, 255, 0.2)';
        el.style.color = 'white';
        el.style.border = '1px solid rgba(255, 255, 255, 0.4)';
        el.style.margin = '0 5px';
        el.style.padding = '8px 12px';
      } else if (el.tagName === 'SPAN') {
        el.style.color = 'white';
        el.style.background = 'transparent';
        el.style.margin = '0 5px';
      } else if (el.tagName === 'INPUT') {
        el.style.background = 'rgba(0, 0, 0, 0.5)';
        el.style.color = 'white';
        el.style.border = '1px solid rgba(255, 255, 255, 0.4)';
        el.style.margin = '0 5px';
      }
    });
  } else {
    logPdfWarn('PDF Control non trouvé');
  }
}

export function restoreCanvasStyles(renderPageCalled = false) {
  const canvasEl = _('pdf-canvas');
  const textBandEl = _('textBand');
  const pdfControlEl = _('pdf-control');
  const pdfViewerEl = _('pdf-viewer');

  if (pdfViewerEl) {
    if (originalPdfViewerStyle && originalPdfViewerStyle.trim()) {
      pdfViewerEl.style.cssText = originalPdfViewerStyle;
    }
    pdfViewerEl.style.position = '';
    pdfViewerEl.style.top = '';
    pdfViewerEl.style.left = '';
    pdfViewerEl.style.width = '';
    pdfViewerEl.style.height = '';
    pdfViewerEl.style.zIndex = '';
    pdfViewerEl.style.margin = '';
    pdfViewerEl.style.padding = '';
    pdfViewerEl.style.overflow = '';
    pdfViewerEl.style.boxSizing = '';
  } else {
    logPdfError('PDF Viewer introuvable pour restauration');
  }

  if (textBandEl) {
    if (originalTextBandParent && textBandEl.parentNode !== originalTextBandParent) {
      if (originalTextBandNextSibling) {
        originalTextBandParent.insertBefore(textBandEl, originalTextBandNextSibling);
      } else {
        originalTextBandParent.appendChild(textBandEl);
      }
    }

    if (originalTextBandStyle && originalTextBandStyle.trim()) {
      textBandEl.style.cssText = originalTextBandStyle;
    }
    if (originalTextBandDisplay) {
      textBandEl.style.display = originalTextBandDisplay;
    }
    if (originalTextBandVisibility) {
      textBandEl.style.visibility = originalTextBandVisibility;
    }
    textBandEl.style.position = '';
    textBandEl.style.bottom = '';
    textBandEl.style.left = '';
    textBandEl.style.right = '';
    textBandEl.style.height = '';
  } else {
    logPdfError('TextBand introuvable pour restauration');
  }

  if (pdfControlEl && originalPdfControlStyle) {
    if (originalPdfControlParent && pdfControlEl.parentNode !== originalPdfControlParent) {
      if (originalPdfControlNextSibling) {
        originalPdfControlParent.insertBefore(pdfControlEl, originalPdfControlNextSibling);
      } else {
        originalPdfControlParent.appendChild(pdfControlEl);
      }
    }

    if (originalPdfControlStyle.cssText && originalPdfControlStyle.cssText.trim()) {
      pdfControlEl.style.cssText = originalPdfControlStyle.cssText;
    }
    pdfControlEl.style.position = '';
    pdfControlEl.style.bottom = '';
    pdfControlEl.style.left = '';
    pdfControlEl.style.right = '';
    pdfControlEl.style.height = '';

    if (originalPdfControlStyle.buttonStyles && originalPdfControlStyle.buttonStyles.length > 0) {
      const controlButtons = pdfControlEl.querySelectorAll('button, input, span');
      controlButtons.forEach((el, index) => {
        if (index < originalPdfControlStyle.buttonStyles.length) {
          const orig = originalPdfControlStyle.buttonStyles[index];
          if (orig.tagName === el.tagName) {
            if (orig.background !== undefined) el.style.background = orig.background;
            if (orig.color !== undefined) el.style.color = orig.color;
            if (orig.border !== undefined) el.style.border = orig.border;
            if (orig.display !== undefined) el.style.display = orig.display;
            if (orig.visibility !== undefined) el.style.visibility = orig.visibility;
            el.style.margin = '';
            el.style.padding = '';
          }
        }
      });
    }
  } else {
    logPdfError('PDF Control ou style original introuvable pour restauration');
  }

  if (canvasEl) {
    if (originalCanvasDisplay) {
      canvasEl.style.display = originalCanvasDisplay;
    } else {
      canvasEl.style.display = '';
    }
    canvasEl.style.margin = '';
    canvasEl.style.padding = '';
    canvasEl.style.boxSizing = '';
    canvasEl.style.width = '';
    canvasEl.style.height = '';
    canvasEl.style.maxWidth = 'none';
  } else {
    logPdfError('Canvas introuvable pour restauration');
  }
}

export function enterPdfFullscreen(element) {
  logPdfInfo('enterPdfFullscreen: Entrée en mode plein écran');
  setOriginalPdfScale(scale);
  saveCurrentCanvasStyles();

  const fullscreenBtn = _('pdf-fullscreen-btn');
  if (fullscreenBtn) {
    fullscreenBtn.textContent = 'Exit Fullscreen';
    fullscreenBtn.title = 'Exit fullscreen mode';
  }

  const requestPromise = new Promise((resolve) => {
    const onFullscreen = () => {
      document.removeEventListener('fullscreenchange', onFullscreen);
      document.removeEventListener('webkitfullscreenchange', onFullscreen);
      document.removeEventListener('mozfullscreenchange', onFullscreen);
      document.removeEventListener('MSFullscreenChange', onFullscreen);
      resolve();
    };

    document.addEventListener('fullscreenchange', onFullscreen);
    document.addEventListener('webkitfullscreenchange', onFullscreen);
    document.addEventListener('mozfullscreenchange', onFullscreen);
    document.addEventListener('MSFullscreenChange', onFullscreen);

    if (element.requestFullscreen) {
      element.requestFullscreen();
    } else if (element.webkitRequestFullscreen) {
      element.webkitRequestFullscreen();
    } else if (element.mozRequestFullScreen) {
      element.mozRequestFullScreen();
    } else if (element.msRequestFullscreen) {
      element.msRequestFullscreen();
    }

    setTimeout(onFullscreen, 500);
  });

  requestPromise.then(() => {
    logPdfInfo('Plein écran activé avec succès');
    applyFullscreenCanvasStyles();
    calculateFullscreenScale();
  }).catch((error) => {
    logPdfError('Erreur lors de l\'activation du plein écran', { error });
  });
}

export function exitPdfFullscreen() {
  logPdfInfo('exitPdfFullscreen: Sortie du mode plein écran');

  const fullscreenBtn = _('pdf-fullscreen-btn');
  if (fullscreenBtn) {
    fullscreenBtn.textContent = 'Fullscreen';
    fullscreenBtn.title = 'Toggle fullscreen mode';
  }

  exitFullscreenAsync().then(() => {
    restoreCanvasStyles(false);

    if (pdfDoc && initialClientWidth > 0) {
      pdfDoc.getPage(1).then((page) => {
        const viewport = page.getViewport({ scale: 1.0 });
        const requiredScale = initialClientWidth / viewport.width;
        setScale(requiredScale);

        if (pageNum) {
          renderPage(pageNum);
        }
      }).catch((error) => {
        logPdfError('Erreur lors du calcul de l échelle', { error });
        if (pageNum) {
          renderPage(pageNum);
        }
      });
    } else if (pageNum) {
      renderPage(pageNum);
    }
  }).catch((error) => {
    logPdfError('Erreur lors de la sortie du plein écran', { error });
  });
}

export function calculateFullscreenScale() {
  const pdfCanvas = _('pdf-canvas');
  if (!pdfCanvas || !pdfDoc) {
    logPdfWarn('calculateFullscreenScale: Canvas ou PDF doc introuvable');
    return;
  }

  if (isCalculatingFullscreenScale) {
    return;
  }
  setIsCalculatingFullscreenScale(true);

  const reservedHeight = 170;
  const availableHeight = window.innerHeight - reservedHeight;
  const availableWidth = window.innerWidth;

  pdfDoc.getPage(1).then((page) => {
    const originalViewport = page.getViewport({ scale: 1.0 });
    const heightScale = availableHeight / originalViewport.height;
    const widthScale = availableWidth / originalViewport.width;
    const calculatedScale = Math.min(heightScale, widthScale) * 0.95;
    setFullscreenScale(calculatedScale);
    setScale(calculatedScale);

    if (pageNum && !pageRendering) {
      renderPage(pageNum);
    }
    setIsCalculatingFullscreenScale(false);
  }).catch((error) => {
    logPdfError('Erreur lors du calcul de l\'échelle plein écran', { error });
    setIsCalculatingFullscreenScale(false);
  });
}

export function handleFullscreenResize() {
  const isFullscreen = document.fullscreenElement
                        || document.webkitFullscreenElement
                        || document.mozFullScreenElement
                        || document.msFullscreenElement;

  if (isFullscreen) {
    setTimeout(calculateFullscreenScale, 100);
  }
}
