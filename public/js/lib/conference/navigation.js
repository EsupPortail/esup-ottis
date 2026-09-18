import { currentscroll, previousimage } from './state/translation.js';
import { scale } from './state/pdf.js';

import { formateresponse } from './utils.js';
import { ShowImage, nextPage, prevPage } from './pdf-viewer.js';

import { _ } from '../dom/selector.js';
import { addCsrfToken, createFetchRequest } from './network.js';
import { safeSetTimeout } from '../utils/cleanup.js';
import { getCurrentPresentation, getCurrentDiscours, setCurrentDiscours } from '../state/conference.js';
import { pdfDoc } from './state/pdf.js';

/** @module conference/navigation */

export function SkipForward() {
  const presentation = getCurrentPresentation();
  if (presentation.length > 0) {
    const newIndex = (getCurrentDiscours() + 1) % presentation.length;
    setCurrentDiscours(newIndex);
    ShowImage(presentation[newIndex].Slide);
  }
}

export function SkipBackward() {
  const presentation = getCurrentPresentation();
  if (presentation.length > 0) {
    const newIndex = (getCurrentDiscours() - 1 + presentation.length) % presentation.length;
    setCurrentDiscours(newIndex);
    ShowImage(presentation[newIndex].Slide);
  }
}

export function Forward() {
  const presentation = getCurrentPresentation();
  if (presentation.length > 0) {
    const newIndex = (getCurrentDiscours() + 1) % presentation.length;
    setCurrentDiscours(newIndex);
    addToNB(presentation[newIndex].Discours, 0);
    ShowImage(presentation[newIndex].Slide);
  }
}

export function Backward() {
  const presentation = getCurrentPresentation();
  if (presentation.length > 0) {
    const newIndex = (getCurrentDiscours() - 1 + presentation.length) % presentation.length;
    setCurrentDiscours(newIndex);
    addToNB(presentation[newIndex].Discours, 0);
    ShowImage(presentation[newIndex].Slide);
  }
}

export function SlideForward() {
  const presentation = getCurrentPresentation();
  if (!presentation || presentation.length === 0) {
    // Fallback to PDF navigation if a PDF is loaded
    if (pdfDoc) {
      nextPage();
    }
    return;
  }
  const currentIndex = getCurrentDiscours();
  const slide = presentation[currentIndex];
  if (slide && slide.Images && slide.Images.length > 1) {
    const currentSlide = slide.Slide;
    let newIndex = currentIndex;
    while (newIndex < presentation.length - 1 && presentation[newIndex].Slide == currentSlide) {
      newIndex++;
    }
    setCurrentDiscours(newIndex);
    ShowImage(slide.Slide);
  }
}

export function SlideBackward() {
  const presentation = getCurrentPresentation();
  if (!presentation || presentation.length === 0) {
    // Fallback to PDF navigation if a PDF is loaded
    if (pdfDoc) {
      prevPage();
    }
    return;
  }
  const currentIndex = getCurrentDiscours();
  const slide = presentation[currentIndex];
  if (slide && slide.Images && slide.Images.length > 1) {
    const currentSlide = slide.Slide;
    let newIndex = currentIndex;
    while (newIndex > 0 && presentation[newIndex].Slide == currentSlide) {
      newIndex--;
    }
    setCurrentDiscours(newIndex);
    ShowImage(slide.Slide);
  }
}

export function completeHandler(event, editable = false) {
  _('notebookcompleteformatted').innerHTML = window.DOMPurify.sanitize(formateresponse(event.target.responseText, editable));
  _('progressBar').value = 0;

  // Re-initialize the presentation
  previousimage = -1;
  setCurrentDiscours(0);
  const presentation = getCurrentPresentation();
  if (presentation.length > 0) {
    safeSetTimeout(() => {
      _('stillwaiting').style.display = 'none';
      ShowImage(presentation[0].Slide);
    }, 1000);
  }
}

export function SlideNotes() {
  const fileInput = _('ppt_presentation');
  if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
    alert('Please select a file first!');
    return;
  }
  const file = fileInput.files[0];
  let scriptfile = '';
  let time = 30000;

  // Determine the script based on file extension
  if (file.name.endsWith('pptx')) {
    scriptfile = typeof LoadPPTX !== 'undefined' && LoadPPTX
      ? '/extract/pptx-images'
      : '/extract/slides-notes';
  } else if (file.name.endsWith('odp')) {
    scriptfile = '/extract/slides-notes';
  } else if (file.name.endsWith('txt')) {
    scriptfile = '/extract/slides-notes-txt';
  } else if (file.name.endsWith('html')) {
    scriptfile = '/extract/slides-notes-apphtml';
    time = 300000;
  }

  if (scriptfile === '') {
    alert('Only pptx, odp, html, and txt files are allowed!');
    return;
  }

  _('stillwaiting').style.display = 'block';
  _('spinnertxt').innerHTML = 'Your presentation is not ready yet....<br/>Still working on it...';

  const formdata = new FormData();
  formdata.append('ppt_presentation', file);
  formdata.append('ACCESS', _('classroomid').value);
  addCsrfToken(formdata);

  const request = createFetchRequest('POST', scriptfile, {
    data: formdata,
    timeout: time,
    context: 'SlideNotes',
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

export function reduceToolbar() {
  if (document.fullscreenElement) {
    // _('bar').style.transform = "scaleY(0.1)";
  } else {
    _('bar').style.transform = 'scaleY(1)';
  }
}

export function resizeSlides() {
  const slide = _('img_slides');
  if (!slide || !slide.width || !slide.height) {
    return;
  }
  const ratio = slide.width / slide.height;
  const aggrandissmentX = (window.innerWidth - 30) / slide.width;
  const aggrandissmentY = (window.innerHeight - 80) / slide.height;
  if (aggrandissmentX > aggrandissmentY) {
    /*
        slide.width=aggrandissmentY*slide.width;
        slide.style.width=aggrandissmentY*slide.width+"px";
        slide.height=aggrandissmentY*slide.height;
        slide.style.height=aggrandissmentY*slide.height+"px";
        */
    slide.style.transform = `scaleX(${aggrandissmentY}) scaleY(${aggrandissmentY})`;
    //    slide.style.transform='scaleX('+aggrandissmentX+') scaleY('+aggrandissmentY+')';
  } else {
    /*    slide.width=aggrandissmentX*slide.width;
            slide.style.width=aggrandissmentX*slide.width+"px";
            slide.height=aggrandissmentX*slide.height;
            slide.style.height=aggrandissmentX*slide.height+"px";
        */
    slide.style.transform = `scaleX(${aggrandissmentX}) scaleY(${aggrandissmentX})`;

    // slide.style.transform='scale('+aggrandissmentX+')';
  }

  // For wooclap and others
  // ["wooclap", "extratab1", "extratab2", "extratab3", "extratab4", "visio"].map(resizeWindow)
}

export function setScrollvalues(l) {
  currentscroll = 1;
  const txtlength = Math.max(20, l);
  // txtlength=Math.min(200,txtlength);
  const duration = 55 * txtlength; // estimated duration in ms
  currentindent = scrollinterval / duration;
}
