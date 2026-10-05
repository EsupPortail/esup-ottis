import { canvas, ctx, currentRenderTask, hasSavedInitialDimensions, initialClientHeight, initialClientWidth, pageNum, pageNumPending, pageRendering, pdfDoc, scale, setCanvas, setCtx, setCurrentRenderTask, setHasSavedInitialDimensions, setInitialClientHeight, setInitialClientWidth, setPageNum, setPageNumPending, setPageRendering, setPdfDoc, setScale } from './state/pdf.js';
import { previousimage, setPreviousimage } from './state/translation.js';

import { b64toBlob } from './utils.js';
import { enterPdfFullscreen, exitPdfFullscreen } from './pdf-fullscreen/core.js';

import { _ } from '../dom/selector.js';
import { addCsrfToken, createFetchRequest } from './network.js';
import { safeSetTimeout } from '../utils/cleanup.js';

import { getRoomToken } from './compat.js';
import { EcrireNBonly, EcrireNBonlyVO } from './notebook.js';

// Alias pour compatibilité avec l'ancien code
const get = _;

/** @module conference/pdf-viewer */

export function progressHandler(event) {
  const percent = (event.loaded / event.total) * 100;
  _('progressBar').value = Math.round(percent);
  //  _("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}

export function ShowImage(n) {
  if (n != previousimage) {
    EcrireNBonly(`---------- Slide ${n} ----------`);
    EcrireNBonlyVO(`---------- Slide ${n} ----------`);
    setPreviousimage(n);
    // console.log('IMG OBJ = '+_("img_"+n));
    // console.log('IMG URL = "'+_("img_"+n).src+'"');
    if (_(`img_${n}`) && _(`img_${n}`).src.length > 200) {
      _('img_slides').src = _(`img_${n}`).src;
      resizeSlides();

      // Diffusion Image
      const f = new FormData();
      f.append('random', Math.floor(Math.random() * 10000));
      const imageurl = _(`img_${n}`).src;
      /*
            let nimages=0;
            let maxcar=10000;
            for (let i=0; i*maxcar<imageurl.length;i++) {
              f.append('image'+i,imageurl.substr(i*maxcar,maxcar));
              nimages++;
            }
            f.append('nbimages',nimages);
            */
      const block = imageurl.split(';');
      const contentType = block[0].split(':')[1];
      const realData = block[1].split(',')[1];
      const blob = b64toBlob(realData, contentType);
      f.append('image', blob);
      f.append('ROOMID', _('classroomid').value);
      f.append('room_token', getRoomToken());
      addCsrfToken(f);

      const request = createFetchRequest('POST', '/image/save', {
        data: f,
        timeout: 10000,
        context: 'ShowImage',
      });
      request.send();
      //	request.onload = function(event) {};
    }
  }
}

export function SlideNotesUncloud(uncloudlink) {
  _('spinnertxt').innerHTML = 'Your presentation is not ready yet....<br/>Still working on it...';
  const scriptfile = '/extract/pptx-images';
  const formdata = new FormData();
  formdata.append('ACCESS', _('classroomid').value);
  formdata.append('uncloudlink', uncloudlink);
  addCsrfToken(formdata);

  const request = createFetchRequest('POST', scriptfile, {
    data: formdata,
    timeout: 30000,
    context: 'SlideNotesUNCloud',
    onUploadProgress: progressHandler,
    onSuccess: completeHandler,
    onError(response, context) {
      if (response.status === 0 && response.statusText === 'Timeout') {
        _('spinnertxt').innerHTML = 'Your PPTX presentation is too long to be imported directly.<br/>Please convert it first with the OMIST Toolbox and use the generated html file instead.';
        alert('Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.');
      }
    },
  });
  request.send();
}

export function SlideNotesUncloudHtml(uncloudhtmllink) {
  _('spinnertxt').innerHTML = 'Your presentation is not ready yet....<br/>Still working on it...';

  const scriptfile = '/extract/slides-notes-apphtml';
  const formdata = new FormData();
  formdata.append('ACCESS', _('classroomid').value);
  formdata.append('uncloudlink', uncloudhtmllink);
  addCsrfToken(formdata);

  const request = createFetchRequest('POST', scriptfile, {
    data: formdata,
    timeout: 30000,
    context: 'SlideNotesUncloudHtml',
    onUploadProgress: progressHandler,
    onSuccess: completeHandler,
    onError(response, context) {
      if (response.status === 0 && response.statusText === 'Timeout') {
        _('spinnertxt').innerHTML = 'No presentation yet...<br/>Please go to the complete lesson tab to import it.';
        alert('Your presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.');
      }
    },
  });
  request.send();
}

export function configurePdfJs() {
  if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    // Add CMap support for proper character encoding in PDFs
    // This resolves "TT: undefined function" warnings
    pdfjsLib.GlobalWorkerOptions.cMapUrl = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/cmaps/';
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

export function renderPage(num) {
  // Don't start new render if one is already in progress
  if (pageRendering) {
    setPageNumPending(num);
    // Return a resolved promise for chaining
    return Promise.resolve();
  }

  setPageRendering(true);

  // Cancel any previous render task to avoid canvas conflicts
  if (currentRenderTask) {
    currentRenderTask.cancel();
    setCurrentRenderTask(null);
  }

  // Return the promise chain
  return pdfDoc.getPage(num).then((page) => {
    const isFullscreen = document.fullscreenElement
                            || document.webkitFullscreenElement
                            || document.mozFullScreenElement
                            || document.msFullscreenElement;

    let viewport;
    if (isFullscreen) {
      // In fullscreen mode, use the pdf-viewer dimensions to determine scale
      // The pdf-viewer should have the correct dimensions from applyFullscreenCanvasStyles()
      const pdfViewerEl = _('pdf-viewer');
      if (pdfViewerEl) {
        // Get the available dimensions from pdf-viewer
        const availableWidth = pdfViewerEl.clientWidth;
        const availableHeight = pdfViewerEl.clientHeight;

        // Get page original dimensions
        const originalViewport = page.getViewport({ scale: 1.0 });
        const widthScale = availableWidth / originalViewport.width;
        const heightScale = availableHeight / originalViewport.height;
        // Use the scale that fits the entire page without margins (we already reserved space in pdf-viewer)
        const fsScale = Math.min(widthScale, heightScale);

        viewport = page.getViewport({ scale: fsScale });

        // Also set canvas CSS size to match the rendered content for proper display
        if (canvas) {
          canvas.style.width = `${viewport.width}px`;
          canvas.style.height = `${viewport.height}px`;
        }
      } else {
        // Fallback to normal scale
        viewport = page.getViewport({ scale });
      }
    } else {
      viewport = page.getViewport({ scale });
    }

    if (canvas) {
      canvas.height = viewport.height;
      canvas.width = viewport.width;
    }

    const renderContext = {
      canvasContext: ctx,
      viewport,
    };

    const renderTask = page.render(renderContext);

    // Store the current render task so it can be cancelled
    setCurrentRenderTask(renderTask);

    // Update navigation UI
    updatePdfNavigation();

    // Return the render task promise for chaining
    return renderTask.promise.then(() => {
      setCurrentRenderTask(null);
      setPageRendering(false);
      if (pageNumPending !== null) {
        renderPage(pageNumPending);
        setPageNumPending(null);
      }
    }).catch((error) => {
      // Handle render errors (e.g., cancelled)
      if (error.name !== 'RenderingCancelledException') {
        console.error('Render error:', error);
      }
      setCurrentRenderTask(null);
      setPageRendering(false);
      if (pageNumPending !== null) {
        renderPage(pageNumPending);
        setPageNumPending(null);
      }
      // Re-throw to allow error handling
      throw error;
    });
  });
}

export function updatePdfNavigation() {
  if (!pdfDoc) return;

  get('pdf-page-indicator').textContent = `Page ${pageNum} of ${pdfDoc.numPages}`;
  get('pdf-page-input').value = pageNum;
  get('pdf-prev').disabled = pageNum <= 1;
  get('pdf-next').disabled = pageNum >= pdfDoc.numPages;
}

export function prevPage() {
  if (pageNum <= 1) return;
  setPageNum(pageNum - 1);
  queueRenderPage(pageNum);
}

export function nextPage() {
  if (pageNum >= pdfDoc.numPages) return;
  setPageNum(pageNum + 1);
  queueRenderPage(pageNum);
}

export function goToPage(num) {
  const pageInput = get('pdf-page-input');
  num = parseInt(num);
  if (num >= 1 && num <= pdfDoc.numPages) {
    setPageNum(num);
    queueRenderPage(pageNum);
  } else {
    pageInput.value = pageNum;
  }
}

export function queueRenderPage(num) {
  if (pageRendering) {
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
    const canvas = get('pdf-canvas');
    if (canvas) canvas.style.display = 'block';
    if (textViewer) textViewer.style.display = 'none';
    if (pdfControl) pdfControl.style.display = 'block';
    // Masquer textfixe quand une presentation est chargée
    const textfixe = get('textfixe');
    if (textfixe) textfixe.style.display = 'none';

    // Passer l'URL directement à PDF.js (plus simple et évite les problèmes de Blob)
    pdfjsLib.getDocument(url).promise
      .then((pdf) => {
        setPdfDoc(pdf);
        setPageNum(1);

        // Rendre la première page et sauvegarder les dimensions initiales
        return renderPage(1).then(() => {
          // Sauvegarder l'échelle et les dimensions NATIVES (celles du chargement initial)
          if (!hasSavedInitialDimensions) {
            const canvasEl = get('pdf-canvas');
            if (canvasEl) {
              // Sauvegarder la taille RENDUE (clientWidth/clientHeight), pas les attributs
              setInitialClientWidth(canvasEl.clientWidth);
              setInitialClientHeight(canvasEl.clientHeight);
              setHasSavedInitialDimensions(true);
            }
          }
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
  const canvas = get('pdf-canvas');
  if (canvas) canvas.style.display = 'none';
  if (pdfControl) pdfControl.style.display = 'none';
  // Masquer textfixe quand une presentation (texte) est chargée
  const textfixe = get('textfixe');
  if (textfixe) textfixe.style.display = 'none';
}

export function togglePdfFullscreen() {
  const pdfViewer = get('pdf-viewer');
  const fullscreenBtn = get('pdf-fullscreen-btn');

  if (!pdfViewer || !fullscreenBtn) {
    return;
  }

  // Check if already in fullscreen
  const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement
                        || document.mozFullScreenElement || document.msFullscreenElement;

  if (isFullscreen) {
    // Exit fullscreen
    exitPdfFullscreen();
  } else {
    // Enter fullscreen
    enterPdfFullscreen(pdfViewer);
  }
}
