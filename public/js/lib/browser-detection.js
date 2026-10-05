/**
 * @module lib/browser-detection
 * Browser detection utilities for OMIST
 * Detects non-Chrome/Edge browsers and shows warning banner
 */

/**
 * Detect if the browser is NOT Chrome or Edge
 * @returns {boolean} True if browser is Firefox, Safari, or other non-Chromium browsers
 */
export function isNotChromeOrEdge() {
  const userAgent = navigator.userAgent.toLowerCase();
  const isChrome = /chrome|crios/.test(userAgent) && !/edge|edg|opr/.test(userAgent);
  const isEdge = /edge|edg/.test(userAgent);
  return !(isChrome || isEdge);
}

/**
 * Initialize browser detection and show warning banner if needed
 * Must be called after DOM is loaded
 */
export function initBrowserDetection() {
  if (isNotChromeOrEdge()) {
    const warningElement = document.getElementById('firefox-warning');
    if (warningElement) {
      warningElement.style.display = 'block';
    }
    document.body.classList.add('firefox-warning-active');
  }
}

/**
 * Auto-initialize on DOM ready for backward compatibility
 * This ensures the warning shows even if the module is loaded without explicit initialization
 */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initBrowserDetection);
} else {
  initBrowserDetection();
}
