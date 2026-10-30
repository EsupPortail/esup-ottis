/**
 * conference/state/pdf.js
 * Module pour gérer l'état du PDF viewer et plein écran
 */

// =============================================================================
// État du viewer PDF
// =============================================================================

/** @type {Object|null} */
let pdfDoc = null;

/** @type {number} */
let pageNum = 1;

/** @type {boolean} */
let pageRendering = false;

/** @type {number|null} */
let pageNumPending = null;

/** @type {number} */
let scale = 1.5;

/** @type {HTMLCanvasElement|null} */
let canvas = null;

/** @type {CanvasRenderingContext2D|null} */
let ctx = null;

/** @type {Object|null} */
let currentRenderTask = null;

/** @type {number} */
let originalPdfScale = 1.5;

/** @type {number} */
let fullscreenScale = 1.5;

/** @type {boolean} */
let isCalculatingFullscreenScale = false;

/** @type {boolean} */
let hasSavedInitialDimensions = false;

// =============================================================================
// Styles originaux (pour le mode plein écran)
// =============================================================================

/** @type {string} */
let originalCanvasStyle = '';

/** @type {string} */
let originalCanvasDisplay = '';

/** @type {number} */
let originalCanvasWidth = 0;

/** @type {number} */
let originalCanvasHeight = 0;

/** @type {number} */
let initialClientWidth = 0;

/** @type {number} */
let initialClientHeight = 0;

/** @type {string} */
let originalTextBandStyle = '';

/** @type {Object} */
let originalPdfControlStyle = { cssText: '', buttonStyles: [] };

/** @type {string} */
let originalTextBandDisplay = '';

/** @type {string} */
let originalTextBandVisibility = '';

// =============================================================================
// Variables pour live (ottis-live-area) - Remplace textBand
// =============================================================================

/** @type {string} */
let originalLiveStyle = '';

/** @type {string} */
let originalLiveDisplay = '';

/** @type {string} */
let originalLiveVisibility = '';

// =============================================================================
// Positions DOM originales (pour le mode plein écran)
// =============================================================================

/** @type {Element|null} */
let originalTextBandParent = null;

/** @type {Element|null} */
let originalTextBandNextSibling = null;

/** @type {Element|null} */
let originalLiveParent = null;

/** @type {Element|null} */
let originalLiveNextSibling = null;

/** @type {Element|null} */
let originalPdfControlParent = null;

/** @type {Element|null} */
let originalPdfControlNextSibling = null;

/** @type {string} */
let originalPdfViewerStyle = '';

// =============================================================================
// Getters (variables privées, lecture externe via fonctions)
// =============================================================================

/**
 * Get the pdfDoc value
 * @returns {Object|null} The PDF document object
 */
export function getPdfDoc() {
  return pdfDoc;
}

/**
 * Get the pageNum value
 * @returns {number} The page number
 */
export function getPageNum() {
  return pageNum;
}

/**
 * Get the pageRendering value
 * @returns {boolean} The rendering status
 */
export function getPageRendering() {
  return pageRendering;
}

/**
 * Get the pageNumPending value
 * @returns {number|null} The pending page number
 */
export function getPageNumPending() {
  return pageNumPending;
}

/**
 * Get the scale value
 * @returns {number} The scale factor
 */
export function getScale() {
  return scale;
}

/**
 * Get the canvas value
 * @returns {HTMLCanvasElement|null} The canvas element
 */
export function getCanvas() {
  return canvas;
}

/**
 * Get the ctx value
 * @returns {CanvasRenderingContext2D|null} The canvas context
 */
export function getCtx() {
  return ctx;
}

/**
 * Get the currentRenderTask value
 * @returns {Object|null} The current render task
 */
export function getCurrentRenderTask() {
  return currentRenderTask;
}

/**
 * Get the originalPdfScale value
 * @returns {number} The original scale factor
 */
export function getOriginalPdfScale() {
  return originalPdfScale;
}

/**
 * Get the fullscreenScale value
 * @returns {number} The scale factor
 */
export function getFullscreenScale() {
  return fullscreenScale;
}

/**
 * Get the isCalculatingFullscreenScale value
 * @returns {boolean} The calculation status
 */
export function getIsCalculatingFullscreenScale() {
  return isCalculatingFullscreenScale;
}

/**
 * Get the hasSavedInitialDimensions value
 * @returns {boolean} Whether initial dimensions have been saved
 */
export function getHasSavedInitialDimensions() {
  return hasSavedInitialDimensions;
}

/**
 * Get the originalCanvasStyle value
 * @returns {string} The original canvas style
 */
export function getOriginalCanvasStyle() {
  return originalCanvasStyle;
}

/**
 * Get the originalCanvasDisplay value
 * @returns {string} The original canvas display value
 */
export function getOriginalCanvasDisplay() {
  return originalCanvasDisplay;
}

/**
 * Get the originalCanvasWidth value
 * @returns {number} The original canvas width
 */
export function getOriginalCanvasWidth() {
  return originalCanvasWidth;
}

/**
 * Get the originalCanvasHeight value
 * @returns {number} The original canvas height
 */
export function getOriginalCanvasHeight() {
  return originalCanvasHeight;
}

/**
 * Get the initialClientWidth value
 * @returns {number} The initial client width
 */
export function getInitialClientWidth() {
  return initialClientWidth;
}

/**
 * Get the initialClientHeight value
 * @returns {number} The initial client height
 */
export function getInitialClientHeight() {
  return initialClientHeight;
}

/**
 * Get the originalTextBandStyle value
 * @returns {string} The original text band style
 */
export function getOriginalTextBandStyle() {
  return originalTextBandStyle;
}

/**
 * Get the originalPdfControlStyle value
 * @returns {Object} The original PDF control style
 */
export function getOriginalPdfControlStyle() {
  return originalPdfControlStyle;
}

/**
 * Get the originalTextBandDisplay value
 * @returns {string} The original text band display value
 */
export function getOriginalTextBandDisplay() {
  return originalTextBandDisplay;
}

/**
 * Get the originalTextBandVisibility value
 * @returns {string} The original text band visibility value
 */
export function getOriginalTextBandVisibility() {
  return originalTextBandVisibility;
}

/**
 * Get the originalLiveStyle value
 * @returns {string} The original live style
 */
export function getOriginalLiveStyle() {
  return originalLiveStyle;
}

/**
 * Get the originalLiveDisplay value
 * @returns {string} The original live display value
 */
export function getOriginalLiveDisplay() {
  return originalLiveDisplay;
}

/**
 * Get the originalLiveVisibility value
 * @returns {string} The original live visibility value
 */
export function getOriginalLiveVisibility() {
  return originalLiveVisibility;
}

/**
 * Get the originalTextBandParent value
 * @returns {Element|null} The original text band parent element
 */
export function getOriginalTextBandParent() {
  return originalTextBandParent;
}

/**
 * Get the originalTextBandNextSibling value
 * @returns {Element|null} The original text band next sibling element
 */
export function getOriginalTextBandNextSibling() {
  return originalTextBandNextSibling;
}

/**
 * Get the originalLiveParent value
 * @returns {Element|null} The original live parent element
 */
export function getOriginalLiveParent() {
  return originalLiveParent;
}

/**
 * Get the originalLiveNextSibling value
 * @returns {Element|null} The original live next sibling element
 */
export function getOriginalLiveNextSibling() {
  return originalLiveNextSibling;
}

/**
 * Get the originalPdfControlParent value
 * @returns {Element|null} The original PDF control parent element
 */
export function getOriginalPdfControlParent() {
  return originalPdfControlParent;
}

/**
 * Get the originalPdfControlNextSibling value
 * @returns {Element|null} The original PDF control next sibling element
 */
export function getOriginalPdfControlNextSibling() {
  return originalPdfControlNextSibling;
}

/**
 * Get the originalPdfViewerStyle value
 * @returns {string} The original PDF viewer style
 */
export function getOriginalPdfViewerStyle() {
  return originalPdfViewerStyle;
}

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
// Live Setters (ottis-live-area)
// =============================================================================

/**
 * Set the originalLiveStyle value
 * @param {string} value - The original live style
 */
export function setOriginalLiveStyle(value) {
  originalLiveStyle = value;
}

/**
 * Set the originalLiveDisplay value
 * @param {string} value - The original live display value
 */
export function setOriginalLiveDisplay(value) {
  originalLiveDisplay = value;
}

/**
 * Set the originalLiveVisibility value
 * @param {string} value - The original live visibility value
 */
export function setOriginalLiveVisibility(value) {
  originalLiveVisibility = value;
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
 * Set the originalLiveParent value
 * @param {Element|null} value - The original live parent element
 */
export function setOriginalLiveParent(value) {
  originalLiveParent = value;
}

/**
 * Set the originalLiveNextSibling value
 * @param {Element|null} value - The original live next sibling element
 */
export function setOriginalLiveNextSibling(value) {
  originalLiveNextSibling = value;
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
