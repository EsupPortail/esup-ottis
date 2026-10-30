import { setPreviousimage } from './state/translation.js';

import { formateresponse } from './utils.js';
import { ShowImage, nextPage, prevPage } from './pdf-viewer.js';
import { addToNB } from './notebook.js';

import { _ } from '../dom/selector.js';
import { safeSetTimeout } from '../utils/cleanup.js';
import {
  getCurrentPresentation,
  getCurrentDiscours,
  setCurrentDiscours,
} from '../state/conference.js';
import { getPdfDoc } from './state/pdf.js';

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
    if (getPdfDoc()) {
      nextPage();
    }
    return;
  }
  const currentIndex = getCurrentDiscours();
  const slide = presentation[currentIndex];
  if (slide && slide.Images && slide.Images.length > 1) {
    const currentSlide = slide.Slide;
    let newIndex = currentIndex;
    while (newIndex < presentation.length - 1 && presentation[newIndex].Slide === currentSlide) {
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
    if (getPdfDoc()) {
      prevPage();
    }
    return;
  }
  const currentIndex = getCurrentDiscours();
  const slide = presentation[currentIndex];
  if (slide && slide.Images && slide.Images.length > 1) {
    const currentSlide = slide.Slide;
    let newIndex = currentIndex;
    while (newIndex > 0 && presentation[newIndex].Slide === currentSlide) {
      newIndex--;
    }
    setCurrentDiscours(newIndex);
    ShowImage(slide.Slide);
  }
}

export function completeHandler(event, editable = false) {
  _('notebookcompleteformatted').innerHTML = window.DOMPurify.sanitize(
    formateresponse(event.target.responseText, editable)
  );
  _('progressBar').value = 0;

  // Re-initialize the presentation
  setPreviousimage(-1);
  setCurrentDiscours(0);
  const presentation = getCurrentPresentation();
  if (presentation.length > 0) {
    safeSetTimeout(() => {
      _('stillwaiting').style.display = 'none';
      ShowImage(presentation[0].Slide);
    }, 1000);
  }
}

export function reduceToolbar() {
  if (document.fullscreenElement) {
    // _('bar').style.transform = "scaleY(0.1)";
  } else {
    // _('bar').style.transform = 'scaleY(1)';
  }
}
