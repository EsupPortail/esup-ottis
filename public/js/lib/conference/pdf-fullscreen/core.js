/**
 * conference/pdf-fullscreen/core.js
 * Module core pour la gestion du plein écran PDF
 */

import {
  getIsCalculatingFullscreenScale,
  getOriginalPdfControlStyle,
  getOriginalTextBandDisplay,
  getOriginalTextBandStyle,
  getOriginalTextBandVisibility,
  getOriginalTextBandParent,
  getOriginalTextBandNextSibling,
  getOriginalPdfControlParent,
  getOriginalPdfControlNextSibling,
  getOriginalPdfViewerStyle,
  getOriginalCanvasDisplay,
  getOriginalLiveStyle,
  getOriginalLiveDisplay,
  getOriginalLiveVisibility,
  getOriginalLiveParent,
  getOriginalLiveNextSibling,
  getPageNum,
  getPageRendering,
  getPdfDoc,
  getScale,
  setScale,
  setFullscreenScale,
  setIsCalculatingFullscreenScale,
  setOriginalCanvasDisplay,
  setOriginalCanvasWidth,
  setOriginalCanvasHeight,
  setOriginalPdfControlStyle,
  setOriginalTextBandDisplay,
  setOriginalTextBandStyle,
  setOriginalTextBandVisibility,
  setOriginalTextBandParent,
  setOriginalTextBandNextSibling,
  setOriginalPdfControlParent,
  setOriginalPdfControlNextSibling,
  setOriginalPdfViewerStyle,
  setOriginalPdfScale,
  setOriginalLiveStyle,
  setOriginalLiveDisplay,
  setOriginalLiveVisibility,
  setOriginalLiveParent,
  setOriginalLiveNextSibling,
} from '../state.js';

import { _ } from '../../dom/selector.js';
import { toggleTextBandDisplay } from '../ui.js';
import { safeSetTimeout } from '../../utils/cleanup.js';
import { t } from '../../i18n.js';

// Rendu de page injecte par pdf-viewer (inversion de dependance) : core ne doit
// pas importer pdf-viewer (cycle core<->pdf-viewer). Null tant que non enregistre.
let renderCurrentPage = null;

/**
 * Enregistre la fonction de rendu fournie par pdf-viewer.
 * @param {Function} renderFn - Fonction de rendu (renderPage)
 */
export function setPdfRenderer(renderFn) {
  renderCurrentPage = renderFn;
}

// Ajustement d'échelle embarqué injecté par pdf-viewer (même principe que
// setPdfRenderer : éviter le cycle d'imports core <-> pdf-viewer).
let embeddedScaleCalculator = null;

/**
 * Enregistre la fonction d'ajustement d'échelle embarquée.
 * @param {Function} scaleFn - Fonction d'ajustement (calculateEmbeddedScale)
 */
export function setEmbeddedScaleCalculator(scaleFn) {
  embeddedScaleCalculator = scaleFn;
}

/**
 * Met à jour le libellé du bouton plein écran sans détruire son
 * <span class="translate"> : le bouton continue de suivre les
 * retraductions i18n et la liste d'éléments sauvegardée pour la
 * restauration des styles (saveCurrentCanvasStyles) reste synchronisée.
 * @param {string} key - Clé i18n du libellé
 */
export function setFullscreenButtonLabel(key) {
  const fullscreenBtn = _('pdf-fullscreen-btn');
  if (!fullscreenBtn) return;
  const labelSpan = fullscreenBtn.querySelector('span');
  if (labelSpan) {
    labelSpan.textContent = t(key);
  } else {
    fullscreenBtn.textContent = t(key);
  }
  fullscreenBtn.title = t(key);
}

// Indique que le plein ecran PDF est actif (styles appliques, non encore restaures)
let pdfFullscreenActive = false;

// =============================================================================
// Fonctions de gestion du plein écran
// =============================================================================

export function exitFullscreenAsync() {
  return new Promise((resolve) => {
    const isFullscreen =
      document.fullscreenElement ||
      document.webkitFullscreenElement ||
      document.mozFullScreenElement ||
      document.msFullscreenElement;

    if (!isFullscreen) {
      resolve();
      return;
    }

    const onFullscreenChange = () => {
      const stillFullscreen =
        document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement;

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
  }

  if (canvasEl) {
    setOriginalCanvasDisplay(canvasEl.style.display);
    setOriginalCanvasWidth(canvasEl.width);
    setOriginalCanvasHeight(canvasEl.height);
  }

  if (textBandEl) {
    setOriginalTextBandParent(textBandEl.parentNode);
    setOriginalTextBandNextSibling(textBandEl.nextSibling);
    setOriginalTextBandDisplay(textBandEl.style.display);
    setOriginalTextBandVisibility(textBandEl.style.visibility);
    setOriginalTextBandStyle(textBandEl.style.cssText);
  }

  // Gestion de live (ottis-live-area) - remplace textBand
  const liveEl = _('live');
  if (liveEl) {
    setOriginalLiveParent(liveEl.parentNode);
    setOriginalLiveNextSibling(liveEl.nextSibling);
    setOriginalLiveDisplay(liveEl.style.display);
    setOriginalLiveVisibility(liveEl.style.visibility);
    setOriginalLiveStyle(liveEl.style.cssText);
  }

  if (pdfControlEl) {
    setOriginalPdfControlParent(pdfControlEl.parentNode);
    setOriginalPdfControlNextSibling(pdfControlEl.nextSibling);

    setOriginalPdfControlStyle({
      cssText: pdfControlEl.style.cssText,
    });
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

  // Gestion de live (ottis-live-area) - remplace textBand
  const liveEl = _('live');
  if (liveEl) {
    // En plein ecran, seuls l'element plein ecran et ses descendants sont rendus :
    // rattacher le bloc au pdf-viewer (restoreCanvasStyles le remettra a sa place)
    if (pdfViewerEl && liveEl.parentNode !== pdfViewerEl) {
      pdfViewerEl.appendChild(liveEl);
    }
    liveEl.style.position = 'fixed';
    liveEl.style.bottom = '0';
    liveEl.style.left = '0';
    liveEl.style.right = '0';
    liveEl.style.zIndex = '10000';
    liveEl.style.height = 'auto';
  }

  if (pdfControlEl) {
    pdfControlEl.style.position = 'fixed';
    pdfControlEl.style.bottom = '110px';
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
  }
}

export function restoreCanvasStyles() {
  const canvasEl = _('pdf-canvas');
  const textBandEl = _('textBand');
  const pdfControlEl = _('pdf-control');
  const pdfViewerEl = _('pdf-viewer');

  const viewerStyle = getOriginalPdfViewerStyle();
  const textBandParent = getOriginalTextBandParent();
  const textBandNextSibling = getOriginalTextBandNextSibling();
  const textBandStyle = getOriginalTextBandStyle();
  const textBandDisplay = getOriginalTextBandDisplay();
  const textBandVisibility = getOriginalTextBandVisibility();
  const liveParent = getOriginalLiveParent();
  const liveNextSibling = getOriginalLiveNextSibling();
  const liveStyle = getOriginalLiveStyle();
  const liveDisplay = getOriginalLiveDisplay();
  const liveVisibility = getOriginalLiveVisibility();
  const controlStyle = getOriginalPdfControlStyle();
  const controlParent = getOriginalPdfControlParent();
  const controlNextSibling = getOriginalPdfControlNextSibling();
  const canvasDisplay = getOriginalCanvasDisplay();

  if (pdfViewerEl) {
    if (viewerStyle && viewerStyle.trim()) {
      pdfViewerEl.style.cssText = viewerStyle;
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
  }

  if (textBandEl) {
    if (textBandParent && textBandEl.parentNode !== textBandParent) {
      if (textBandNextSibling) {
        textBandParent.insertBefore(textBandEl, textBandNextSibling);
      } else {
        textBandParent.appendChild(textBandEl);
      }
    }

    if (textBandStyle && textBandStyle.trim()) {
      textBandEl.style.cssText = textBandStyle;
    }
    if (textBandDisplay) {
      textBandEl.style.display = textBandDisplay;
    }
    if (textBandVisibility) {
      textBandEl.style.visibility = textBandVisibility;
    }
    textBandEl.style.position = '';
    textBandEl.style.bottom = '';
    textBandEl.style.left = '';
    textBandEl.style.right = '';
    textBandEl.style.height = '';
  }

  // Restaurer live (ottis-live-area)
  const liveEl = _('live');
  if (liveEl) {
    if (liveParent && liveEl.parentNode !== liveParent) {
      if (liveNextSibling) {
        liveParent.insertBefore(liveEl, liveNextSibling);
      } else {
        liveParent.appendChild(liveEl);
      }
    }

    if (liveStyle && liveStyle.trim()) {
      liveEl.style.cssText = liveStyle;
    }
    if (liveDisplay) {
      liveEl.style.display = liveDisplay;
    }
    if (liveVisibility) {
      liveEl.style.visibility = liveVisibility;
    }
    liveEl.style.position = '';
    liveEl.style.bottom = '';
    liveEl.style.left = '';
    liveEl.style.right = '';
    liveEl.style.height = '';
  }

  if (pdfControlEl && controlStyle) {
    if (controlParent && pdfControlEl.parentNode !== controlParent) {
      if (controlNextSibling) {
        controlParent.insertBefore(pdfControlEl, controlNextSibling);
      } else {
        controlParent.appendChild(pdfControlEl);
      }
    }

    if (controlStyle.cssText && controlStyle.cssText.trim()) {
      pdfControlEl.style.cssText = controlStyle.cssText;
    }
    pdfControlEl.style.position = '';
    pdfControlEl.style.bottom = '';
    pdfControlEl.style.left = '';
    pdfControlEl.style.right = '';
    pdfControlEl.style.height = '';

    // Restauration insensible à l'ordre/nombre d'éléments : on vide les
    // styles inline posés par applyFullscreenCanvasStyles, la CSS reprend
    // la main (les valeurs d'origine étaient vides par construction).
    const controlButtons = pdfControlEl.querySelectorAll('button, input, span');
    controlButtons.forEach((el) => {
      el.style.background = '';
      el.style.color = '';
      el.style.border = '';
      el.style.margin = '';
      el.style.padding = '';
    });
  }

  if (canvasEl) {
    if (canvasDisplay) {
      canvasEl.style.display = canvasDisplay;
    } else {
      canvasEl.style.display = '';
    }
    canvasEl.style.margin = '';
    canvasEl.style.padding = '';
    canvasEl.style.boxSizing = '';
    canvasEl.style.width = '';
    canvasEl.style.height = '';
    canvasEl.style.maxWidth = '';
  }
}

export function enterPdfFullscreen(element) {
  setOriginalPdfScale(getScale());
  saveCurrentCanvasStyles();

  setFullscreenButtonLabel('conference.outil.exitFullscreen');

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

    safeSetTimeout(onFullscreen, 500);
  });

  requestPromise
    .then(() => {
      applyFullscreenCanvasStyles();
      calculateFullscreenScale();

      // Appliquer l'affichage selon la case (le plein écran impose le bloc fixe)
      toggleTextBandDisplay(true);
      pdfFullscreenActive = true;
    })
    .catch(() => {});
}

export function exitPdfFullscreen() {
  pdfFullscreenActive = false;

  setFullscreenButtonLabel('conference.outil.fullscreen');

  exitFullscreenAsync()
    .then(() => {
      restoreCanvasStyles(false);

      // Restaurer l'affichage de textBand/partialtitre selon useTextBand
      safeSetTimeout(() => {
        toggleTextBandDisplay();
      }, 50);

      // Réajuster l'échelle embarquée via la fonction injectée par pdf-viewer
      // (remplace l'ancien recalcul basé sur la largeur seule)
      safeSetTimeout(() => {
        if (embeddedScaleCalculator) {
          embeddedScaleCalculator();
        }
      }, 60);
    })
    .catch(() => {});
}

/**
 * Rattrape une sortie de plein ecran faite sans le bouton (touche Echap) :
 * restaure les styles et reapplique l'affichage selon la case.
 */
export function ensureFullscreenExited() {
  if (pdfFullscreenActive && !document.fullscreenElement) {
    exitPdfFullscreen();
  }
}

export function calculateFullscreenScale() {
  const pdfCanvas = _('pdf-canvas');
  if (!pdfCanvas || !getPdfDoc()) {
    return;
  }

  if (getIsCalculatingFullscreenScale()) {
    return;
  }
  setIsCalculatingFullscreenScale(true);

  const reservedHeight = 180;
  const availableHeight = window.innerHeight - reservedHeight;
  const availableWidth = window.innerWidth;

  getPdfDoc()
    .getPage(1)
    .then((page) => {
      const originalViewport = page.getViewport({ scale: 1.0 });
      const heightScale = availableHeight / originalViewport.height;
      const widthScale = availableWidth / originalViewport.width;
      const calculatedScale = Math.min(heightScale, widthScale) * 0.95;
      setFullscreenScale(calculatedScale);
      setScale(calculatedScale);

      const num = getPageNum();
      if (num && !getPageRendering()) {
        if (renderCurrentPage) renderCurrentPage(num);
      }
      setIsCalculatingFullscreenScale(false);
    })
    .catch(() => {
      setIsCalculatingFullscreenScale(false);
    });
}

export function handleFullscreenResize() {
  const isFullscreen =
    document.fullscreenElement ||
    document.webkitFullscreenElement ||
    document.mozFullScreenElement ||
    document.msFullscreenElement;

  if (isFullscreen) {
    safeSetTimeout(calculateFullscreenScale, 100);
  }
}
