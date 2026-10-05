/**
 * conference/state/pdf.js
 * Module pour gérer l'état du PDF viewer et plein écran
 */

// =============================================================================
// État du viewer PDF
// =============================================================================

/** @type {Object|null} */
export let pdfDoc = null;

/** @type {number} */
export let pageNum = 1;

/** @type {boolean} */
export let pageRendering = false;

/** @type {number|null} */
export let pageNumPending = null;

/** @type {number} */
export let scale = 1.5;

/** @type {HTMLCanvasElement|null} */
export let canvas = null;

/** @type {CanvasRenderingContext2D|null} */
export let ctx = null;

/** @type {Object|null} */
export let currentRenderTask = null;

/** @type {number} */
export let originalPdfScale = 1.5;

/** @type {number} */
export let fullscreenScale = 1.5;

/** @type {boolean} */
export let isCalculatingFullscreenScale = false;

/** @type {boolean} */
export let hasSavedInitialDimensions = false;

// =============================================================================
// Styles originaux (pour le mode plein écran)
// =============================================================================

/** @type {string} */
export let originalCanvasStyle = '';

/** @type {string} */
export let originalCanvasDisplay = '';

/** @type {number} */
export let originalCanvasWidth = 0;

/** @type {number} */
export let originalCanvasHeight = 0;

/** @type {number} */
export let initialClientWidth = 0;

/** @type {number} */
export let initialClientHeight = 0;

/** @type {string} */
export let originalTextBandStyle = '';

/** @type {Object} */
export let originalPdfControlStyle = { cssText: '', buttonStyles: [] };



/** @type {string} */
export let originalTextBandDisplay = '';

/** @type {string} */
export let originalTextBandVisibility = '';

// =============================================================================
// Positions DOM originales (pour le mode plein écran)
// =============================================================================

/** @type {Element|null} */
export let originalTextBandParent = null;

/** @type {Element|null} */
export let originalTextBandNextSibling = null;

/** @type {Element|null} */
export let originalPdfControlParent = null;

/** @type {Element|null} */
export let originalPdfControlNextSibling = null;

/** @type {string} */
export let originalPdfViewerStyle = '';

// =============================================================================
// PDF Viewer Setters
// =============================================================================

/**
 * Set the pdfDoc value
 * @param {Object|null} value - The PDF document object
 */
export function setPdfDoc(value) {
  pdfDoc = value;
}

/**
 * Set the pageNum value
 * @param {number} value - The page number
 */
export function setPageNum(value) {
  pageNum = value;
}

/**
 * Set the pageRendering value
 * @param {boolean} value - The rendering status
 */
export function setPageRendering(value) {
  pageRendering = value;
}

/**
 * Set the pageNumPending value
 * @param {number|null} value - The pending page number
 */
export function setPageNumPending(value) {
  pageNumPending = value;
}

/**
 * Set the scale value
 * @param {number} value - The scale factor
 */
export function setScale(value) {
  scale = value;
}

/**
 * Set the canvas value
 * @param {HTMLCanvasElement|null} value - The canvas element
 */
export function setCanvas(value) {
  canvas = value;
}

/**
 * Set the ctx value
 * @param {CanvasRenderingContext2D|null} value - The canvas context
 */
export function setCtx(value) {
  ctx = value;
}

/**
 * Set the currentRenderTask value
 * @param {Object|null} value - The current render task
 */
export function setCurrentRenderTask(value) {
  currentRenderTask = value;
}

// =============================================================================
// PDF Fullscreen Setters
// =============================================================================

/**
 * Set the fullscreenScale value
 * @param {number} value - The scale factor
 */
export function setFullscreenScale(value) {
  fullscreenScale = value;
}

/**
 * Set the isCalculatingFullscreenScale value
 * @param {boolean} value - The calculation status
 */
export function setIsCalculatingFullscreenScale(value) {
  isCalculatingFullscreenScale = value;
}

/**
 * Set the hasSavedInitialDimensions value
 * @param {boolean} value - Whether initial dimensions have been saved
 */
export function setHasSavedInitialDimensions(value) {
  hasSavedInitialDimensions = value;
}

/**
 * Set the initialClientWidth value
 * @param {number} value - The initial client width
 */
export function setInitialClientWidth(value) {
  initialClientWidth = value;
}

/**
 * Set the initialClientHeight value
 * @param {number} value - The initial client height
 */
export function setInitialClientHeight(value) {
  initialClientHeight = value;
}

/**
 * Set the originalPdfScale value
 * @param {number} value - The original scale factor
 */
export function setOriginalPdfScale(value) {
  originalPdfScale = value;
}

/**
 * Set the originalCanvasStyle value
 * @param {string} value - The original canvas style
 */
export function setOriginalCanvasStyle(value) {
  originalCanvasStyle = value;
}

/**
 * Set the originalCanvasDisplay value
 * @param {string} value - The original canvas display value
 */
export function setOriginalCanvasDisplay(value) {
  originalCanvasDisplay = value;
}

/**
 * Set the originalCanvasWidth value
 * @param {number} value - The original canvas width
 */
export function setOriginalCanvasWidth(value) {
  originalCanvasWidth = value;
}

/**
 * Set the originalCanvasHeight value
 * @param {number} value - The original canvas height
 */
export function setOriginalCanvasHeight(value) {
  originalCanvasHeight = value;
}

/**
 * Set the originalTextBandStyle value
 * @param {string} value - The original text band style
 */
export function setOriginalTextBandStyle(value) {
  originalTextBandStyle = value;
}

/**
 * Set the originalPdfControlStyle value
 * @param {Object} value - The original PDF control style
 */
export function setOriginalPdfControlStyle(value) {
  originalPdfControlStyle = value;
}

/**
 * Set the originalTextBandDisplay value
 * @param {string} value - The original text band display value
 */
export function setOriginalTextBandDisplay(value) {
  originalTextBandDisplay = value;
}

/**
 * Set the originalTextBandVisibility value
 * @param {string} value - The original text band visibility value
 */
export function setOriginalTextBandVisibility(value) {
  originalTextBandVisibility = value;
}

// =============================================================================
// PDF Fullscreen DOM Position Setters
// =============================================================================

/**
 * Set the originalTextBandParent value
 * @param {Element|null} value - The original text band parent element
 */
export function setOriginalTextBandParent(value) {
  originalTextBandParent = value;
}

/**
 * Set the originalTextBandNextSibling value
 * @param {Element|null} value - The original text band next sibling element
 */
export function setOriginalTextBandNextSibling(value) {
  originalTextBandNextSibling = value;
}

/**
 * Set the originalPdfControlParent value
 * @param {Element|null} value - The original PDF control parent element
 */
export function setOriginalPdfControlParent(value) {
  originalPdfControlParent = value;
}

/**
 * Set the originalPdfControlNextSibling value
 * @param {Element|null} value - The original PDF control next sibling element
 */
export function setOriginalPdfControlNextSibling(value) {
  originalPdfControlNextSibling = value;
}

/**
 * Set the originalPdfViewerStyle value
 * @param {string} value - The original PDF viewer style
 */
export function setOriginalPdfViewerStyle(value) {
  originalPdfViewerStyle = value;
}
