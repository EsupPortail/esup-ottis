import {
  getCanvas,
  getCtx,
  getCurrentRenderTask,
  getHasSavedInitialDimensions,
  getPageNum,
  getPageNumPending,
  getPageRendering,
  getPdfDoc,
  getScale,
  setCanvas,
  setCtx,
  setCurrentRenderTask,
  setHasSavedInitialDimensions,
  setInitialClientHeight,
  setInitialClientWidth,
  setPageNum,
  setPageNumPending,
  setPageRendering,
  setPdfDoc,
  setScale,
} from './state/pdf.js';
import { getPreviousimage, setPreviousimage } from './state/translation.js';

import { resizeSlides } from './utils.js';
import {
  enterPdfFullscreen,
  exitPdfFullscreen,
  setEmbeddedScaleCalculator,
  setPdfRenderer,
} from './pdf-fullscreen/core.js';
import { showPresentationError } from './errors.js';
import { t } from '../i18n.js';

import { _ } from '../dom/selector.js';
import { safeSetTimeout } from '../utils/cleanup.js';

import { EcrireNBonly, EcrireNBonlyVO } from './notebook.js';
import * as logger from '../utils/logger.js';

// Alias pour compatibilité avec l'ancien code
const get = _;

/** @module conference/pdf-viewer */

// Inverser la dependance : core n'importe plus pdf-viewer, c'est ici qu'on
// lui fournit la fonction de rendu (cycle core<->pdf-viewer).
setPdfRenderer(renderPage);
// Même principe pour l'ajustement d'échelle embarqué (sortie de plein écran).
setEmbeddedScaleCalculator(calculateEmbeddedScale);

export function progressHandler(event) {
  const percent = (event.loaded / event.total) * 100;
  _('progressBar').value = Math.round(percent);
  //  _("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}

export function ShowImage(n) {
  if (String(n) !== String(getPreviousimage())) {
    EcrireNBonly(`---------- Slide ${n} ----------`);
    EcrireNBonlyVO(`---------- Slide ${n} ----------`);
    setPreviousimage(n);
    // logger.debug('IMG OBJ = '+_("img_"+n));
    // logger.debug('IMG URL = "'+_("img_"+n).src+'"');
    if (_(`img_${n}`) && _(`img_${n}`).src.length > 200) {
      _('img_slides').src = _(`img_${n}`).src;
      resizeSlides();
    }
  }
}

export function configurePdfJs() {
  if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc =
      'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    // Add CMap support for proper character encoding in PDFs
    // This resolves "TT: undefined function" warnings
    pdfjsLib.GlobalWorkerOptions.cMapUrl =
      'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/cmaps/';
    pdfjsLib.GlobalWorkerOptions.cMapPacked = true;
  } else {
    safeSetTimeout(configurePdfJs, 200);
  }
}

export function initPdfViewer() {
  const canvasEl = get('pdf-canvas');
  if (canvasEl) {
    setCanvas(canvasEl);
    setCtx(canvasEl.getContext('2d'));
  }
}

// Debounce du réajustement embarqué (listener resize)
let embeddedScaleTimer = null;

/**
 * Calcule l'échelle embarquée pour que la page PDF tienne dans la zone
 * disponible : présentation et bulle visibles sans scroll, ratio conservé.
 * Équivalent embarqué de calculateFullscreenScale (pdf-fullscreen/core.js).
 * @returns {Promise} Se résout après application de l'échelle
 */
export function calculateEmbeddedScale() {
  const pdfViewerEl = _('pdf-viewer');
  const containerEl = _('ottis-import');
  const controlEl = _('pdf-control');
  const liveEl = _('live');
  if (!pdfViewerEl || !containerEl || !getPdfDoc()) {
    return Promise.resolve();
  }

  return getPdfDoc()
    .getPage(1)
    .then((page) => {
      const originalViewport = page.getViewport({ scale: 1.0 });
      const viewerStyle = window.getComputedStyle(pdfViewerEl);

      // Largeur : zone du viewer moins la bande du bouton de fermeture
      const availableWidth = pdfViewerEl.clientWidth - (parseFloat(viewerStyle.paddingRight) || 0);

      // Hauteur : budget du conteneur (max-height CSS) moins la barre de
      // navigation et la bulle de sous-titrage
      const containerStyle = window.getComputedStyle(containerEl);
      const containerMaxHeight = parseFloat(containerStyle.maxHeight);
      const budgetHeight = Number.isFinite(containerMaxHeight)
        ? containerMaxHeight
        : window.innerHeight - 170;
      const controlHeight = controlEl
        ? controlEl.offsetHeight + (parseFloat(window.getComputedStyle(controlEl).marginTop) || 0)
        : 0;
      const liveHeight = liveEl
        ? liveEl.offsetHeight + (parseFloat(window.getComputedStyle(liveEl).marginTop) || 0)
        : 0;
      const gap = parseFloat(viewerStyle.columnGap || viewerStyle.rowGap) || 0;
      const heightForCanvas = budgetHeight - controlHeight - liveHeight - gap - 4;

      const widthScale = availableWidth / originalViewport.width;
      const heightScale = heightForCanvas / originalViewport.height;
      // Marge de sécurité, comme le plein écran
      const embeddedScale = Math.max(0.1, Math.min(widthScale, heightScale) * 0.95);

      setScale(embeddedScale);
      if (getPageNum() && !getPageRendering()) {
        renderPage(getPageNum());
      }
    })
    .catch(() => {});
}

// Changement d'écran (resize) : réajuster la présentation hors plein écran.
// En plein écran, c'est handleFullscreenResize qui recalcule.
window.addEventListener('resize', () => {
  const isFullscreen =
    document.fullscreenElement ||
    document.webkitFullscreenElement ||
    document.mozFullScreenElement ||
    document.msFullScreenElement;
  const canvasEl = _('pdf-canvas');
  if (isFullscreen || !getPdfDoc() || !canvasEl || canvasEl.style.display === 'none') {
    return;
  }
  if (embeddedScaleTimer) {
    clearTimeout(embeddedScaleTimer);
  }
  embeddedScaleTimer = safeSetTimeout(() => {
    embeddedScaleTimer = null;
    calculateEmbeddedScale();
  }, 150);
});

export function renderPage(num) {
  // Don't start new render if one is already in progress
  if (getPageRendering()) {
    setPageNumPending(num);
    // Return a resolved promise for chaining
    return Promise.resolve();
  }

  setPageRendering(true);

  // Cancel any previous render task to avoid canvas conflicts
  const previousRenderTask = getCurrentRenderTask();
  if (previousRenderTask) {
    previousRenderTask.cancel();
    setCurrentRenderTask(null);
  }

  // Return the promise chain
  return getPdfDoc()
    .getPage(num)
    .then((page) => {
      const isFullscreen =
        document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement;

      let viewport;
      if (isFullscreen) {
        // In fullscreen mode, use the pdf-viewer dimensions to determine scale
        // The pdf-viewer should have the correct dimensions from applyFullscreenCanvasStyles()
        const pdfViewerEl = _('pdf-viewer');
        if (pdfViewerEl) {
          // Get the available dimensions from pdf-viewer
          const availableWidth = pdfViewerEl.clientWidth;
          // La feuille UA du navigateur force l'élément plein écran à
          // 100vw x 100vh (!important) : réserver la bande basse
          // (bulle live ~105px + contrôle 70px + écarts) ici, pas sur le viewer
          const availableHeight = pdfViewerEl.clientHeight - 180;

          // Get page original dimensions
          const originalViewport = page.getViewport({ scale: 1.0 });
          const widthScale = availableWidth / originalViewport.width;
          const heightScale = availableHeight / originalViewport.height;
          // Use the scale that fits the entire page without margins (we already reserved space in pdf-viewer)
          const fsScale = Math.min(widthScale, heightScale);

          viewport = page.getViewport({ scale: fsScale });

          // Also set canvas CSS size to match the rendered content for proper display
          const canvasEl = getCanvas();
          if (canvasEl) {
            canvasEl.style.width = `${viewport.width}px`;
            canvasEl.style.height = `${viewport.height}px`;
          }
        } else {
          // Fallback to normal scale
          viewport = page.getViewport({ scale: getScale() });
        }
      } else {
        viewport = page.getViewport({ scale: getScale() });
      }

      const canvasEl = getCanvas();
      if (canvasEl) {
        canvasEl.height = viewport.height;
        canvasEl.width = viewport.width;
      }

      const renderContext = {
        canvasContext: getCtx(),
        viewport,
      };

      const renderTask = page.render(renderContext);

      // Store the current render task so it can be cancelled
      setCurrentRenderTask(renderTask);

      // Update navigation UI
      updatePdfNavigation();

      // Return the render task promise for chaining
      return renderTask.promise
        .then(() => {
          setCurrentRenderTask(null);
          setPageRendering(false);
          const pending = getPageNumPending();
          if (pending !== null) {
            renderPage(pending);
            setPageNumPending(null);
          }
        })
        .catch((error) => {
          // Handle render errors (e.g., cancelled)
          if (error.name !== 'RenderingCancelledException') {
            logger.error('Render error:', error);
          }
          setCurrentRenderTask(null);
          setPageRendering(false);
          const pending = getPageNumPending();
          if (pending !== null) {
            renderPage(pending);
            setPageNumPending(null);
          }
          // Re-throw to allow error handling
          throw error;
        });
    });
}

export function updatePdfNavigation() {
  const doc = getPdfDoc();
  const current = getPageNum();
  if (!doc) return;

  get('pdf-page-indicator').textContent = t('conference.pdf.pageIndicator', {
    n: current,
    m: doc.numPages,
  });
  get('pdf-page-input').value = current;
  get('pdf-prev').disabled = current <= 1;
  get('pdf-next').disabled = current >= doc.numPages;
}

export function prevPage() {
  const current = getPageNum();
  if (current <= 1) return;
  setPageNum(current - 1);
  queueRenderPage(current - 1);
}

export function nextPage() {
  const doc = getPdfDoc();
  const current = getPageNum();
  if (current >= doc.numPages) return;
  setPageNum(current + 1);
  queueRenderPage(current + 1);
}

export function goToPage(num) {
  const pageInput = get('pdf-page-input');
  num = parseInt(num, 10);
  const doc = getPdfDoc();
  if (num >= 1 && num <= doc.numPages) {
    setPageNum(num);
    queueRenderPage(num);
  } else if (pageInput) {
    pageInput.value = getPageNum();
  }
}

export function queueRenderPage(num) {
  if (getPageRendering()) {
    setPageNumPending(num);
  } else {
    renderPage(num);
  }
}

export function displayPDF(url) {
  // Initialize PDF viewer canvas and context
  initPdfViewer();

  // Attendre que PDF.js soit chargé
  function waitForPdfJs() {
    if (typeof pdfjsLib !== 'undefined' && pdfjsLib.getDocument) {
      loadPDF();
    } else {
      safeSetTimeout(waitForPdfJs, 200);
    }
  }

  function loadPDF() {
    const pdfViewer = get('pdf-viewer');
    const textViewer = get('text-content-viewer');
    const pdfControl = get('pdf-control');

    if (pdfViewer) pdfViewer.style.visibility = 'visible';
    const pdfCanvas = get('pdf-canvas');
    if (pdfCanvas) pdfCanvas.style.display = 'block';
    if (textViewer) textViewer.style.display = 'none';
    if (pdfControl) pdfControl.style.display = 'block';

    // Passer l'URL directement à PDF.js (plus simple et évite les problèmes de Blob)
    pdfjsLib
      .getDocument(url)
      .promise.then((pdf) => {
        setPdfDoc(pdf);
        setPageNum(1);

        // Rendre la première page et sauvegarder les dimensions initiales
        return renderPage(1).then(() => {
          // Sauvegarder l'échelle et les dimensions NATIVES (celles du chargement initial)
          if (!getHasSavedInitialDimensions()) {
            const canvasEl = get('pdf-canvas');
            if (canvasEl) {
              // Sauvegarder la taille RENDUE (clientWidth/clientHeight), pas les attributs
              setInitialClientWidth(canvasEl.clientWidth);
              setInitialClientHeight(canvasEl.clientHeight);
              setHasSavedInitialDimensions(true);
            }
          }

          // Ajuster la présentation à la place disponible
          // (présentation + bulle visibles sans scroll)
          return calculateEmbeddedScale().then(() => {
            // Masquer la barre de progression une fois le PDF chargé
            const dProg = get('d-prog');
            if (dProg) {
              dProg.hidden = true;
            }
          });
        });
      })
      .catch((error) => {
        showPresentationError(`Erreur chargement PDF: ${error.message}`);
      });
  }

  // Démarrer l'attente
  waitForPdfJs();
}

export function displayTextContent(content) {
  const viewer = get('text-content-viewer');
  const pdfViewer = get('pdf-viewer');
  const pdfControl = get('pdf-control');

  if (viewer) {
    viewer.style.display = 'block';
    viewer.style.whiteSpace = 'pre-wrap';
    viewer.style.wordWrap = 'break-word';
    viewer.textContent = content;
  }
  // Masquer pdf-viewer mais garder textBand visible (visibility: visible)
  if (pdfViewer) pdfViewer.style.visibility = 'hidden';
  const pdfCanvas = get('pdf-canvas');
  if (pdfCanvas) pdfCanvas.style.display = 'none';
  if (pdfControl) pdfControl.style.display = 'none';
}

export function togglePdfFullscreen() {
  const pdfViewer = get('pdf-viewer');
  const fullscreenBtn = get('pdf-fullscreen-btn');

  if (!pdfViewer || !fullscreenBtn) {
    return;
  }

  // Check if already in fullscreen
  const isFullscreen =
    document.fullscreenElement ||
    document.webkitFullscreenElement ||
    document.mozFullScreenElement ||
    document.msFullscreenElement;

  if (isFullscreen) {
    // Exit fullscreen
    exitPdfFullscreen();
  } else {
    // Enter fullscreen
    enterPdfFullscreen(pdfViewer);
  }
}
