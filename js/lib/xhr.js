/**
 * @module lib/xhr
 * XHR and fetch utilities with centralized error handling
 */
import { showError } from './browser.js';

/**
 * Add CSRF token to FormData object
 * @param {FormData} formData - The FormData object to add CSRF token to
 * @returns {FormData} The FormData object with CSRF token added
 */
export function addCsrfToken(formData) {
  const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
  if (csrfToken) {
    formData.append('csrf_token', csrfToken);
  }
  return formData;
}

/**
 * Handle XMLHttpRequest errors centrally
 * @param {XMLHttpRequest} xhr - The XMLHttpRequest object
 * @param {string} [context='unknown'] - Context for error identification
 * @returns {boolean} True if error was handled
 */
export function handleXhrError(xhr, context = 'unknown') {
  if (xhr.status === 0) {
    showError('Network error: Unable to connect to server', context);
  } else if (xhr.status >= 400 && xhr.status < 600) {
    showError(`HTTP error ${xhr.status}: ${xhr.statusText}`, context);
  } else {
    showError('An unexpected error occurred', context);
  }
  return true;
}

/**
 * Create an XMLHttpRequest with centralized error handling
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {string} url - The URL to request
 * @param {Object} [options={}] - Options object
 * @param {Function} [options.onSuccess] - Success callback
 * @param {Function} [options.onError] - Error callback (defaults to handleXhrError)
 * @param {FormData|Object|null} [options.data] - Request data
 * @param {number} [options.timeout=30000] - Request timeout in ms
 * @param {string} [options.contentType] - Content-Type header
 * @param {string} [options.context] - Context for error identification
 * @param {Function} [options.onProgress] - Progress callback
 * @param {Function} [options.onUploadProgress] - Upload progress callback
 * @returns {XMLHttpRequest} The configured XMLHttpRequest object
 */
export function createXhrRequest(method, url, options = {}) {
  const xhr = new XMLHttpRequest();
  const {
    onSuccess = null,
    onError = handleXhrError,
    data = null,
    timeout = 30000,
    contentType = 'application/x-www-form-urlencoded',
    context = url,
    onProgress = null,
    onUploadProgress = null,
  } = options;

  xhr.open(method, url, true);
  xhr.timeout = timeout;

  if (method === 'POST' && data instanceof FormData) {
    // FormData sets its own Content-Type with boundary
  } else if (contentType) {
    xhr.setRequestHeader('Content-Type', contentType);
  }

  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status >= 200 && xhr.status < 300) {
        if (onSuccess) onSuccess(xhr);
      } else if (onError) onError(xhr, context);
    }
  };

  xhr.ontimeout = function () {
    showError('Request timed out', context);
  };

  xhr.onerror = function () {
    showError('Network error', context);
  };

  // Add progress event listeners if provided
  if (onProgress && xhr.addEventListener) {
    xhr.addEventListener('progress', onProgress, false);
  }
  if (onUploadProgress && xhr.upload && xhr.upload.addEventListener) {
    xhr.upload.addEventListener('progress', onUploadProgress, false);
  }

  return xhr;
}

/**
 * Abort a timeout for XHR requests
 * @param {XMLHttpRequest} r - The XHR request
 * @param {string} s - Context string
 */
export function AbortTimeout(r, s) {
  r.timeout = 10000;
  r.ontimeout = (e) => {
    r.abort();
  };
}

/**
 * Simple fetch wrapper with centralized error handling
 * @param {string} url - The URL to request
 * @param {Object} [options={}] - Fetch options
 * @returns {Promise<Response>} The fetch response
 */
export async function fetchWithErrorHandling(url, options = {}) {
  try {
    const response = await fetch(url, options);

    if (!response.ok) {
      const errorText = await response.text().catch(() => '');
      showError(`HTTP ${response.status}: ${errorText || response.statusText}`, url);
      throw new Error(`HTTP ${response.status}`);
    }

    return response;
  } catch (error) {
    if (error.name !== 'AbortError') {
      showError(error.message, url);
    }
    throw error;
  }
}
