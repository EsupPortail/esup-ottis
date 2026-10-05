/**
 * @module lib/browser
 * Browser utilities and error handling
 */

/**
 * Display an error message to the console
 * @param {string} message - The error message to display
 * @param {string} [context=null] - Optional context for logging
 */
export function showError(message, context = null) {
  console.error(context ? `[${context}] ${message}` : message);
}

/**
 * Detect the browser type
 * @returns {string} The browser name
 */
export function whichBrowser() {
  const ua = navigator.userAgent;
  const browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
  for (let i = 0; i < browsers.length; i++) {
    if (ua.indexOf(browsers[i]) > 0) return browsers[i];
  }
  return 'Unknown';
}

/**
 * Escape HTML special characters to prevent XSS
 * @param {string} text - The text to escape
 * @returns {string} The escaped text
 */
export function escapeHtml(text) {
  if (typeof text !== 'string') return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
