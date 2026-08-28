/** @module conference/network */

/**
 * Add CSRF token to FormData object
 * @param {FormData} formData - The FormData object to add token to
 * @returns {FormData} The FormData object with CSRF token added
 */
export function addCsrfToken(formData) {
  const csrfInput = document.querySelector('input[name="csrf_token"]');
  const csrfToken = csrfInput ? csrfInput.value : null;
  if (csrfToken) {
    formData.append('csrf_token', csrfToken);
  }
  return formData;
}

/**
 * Create and configure an XMLHttpRequest
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {string} url - The URL to request
 * @param {Object} options - Configuration options
 * @param {Function} options.onSuccess - Callback for successful response
 * @param {Function} options.onError - Callback for error response
 * @param {FormData|Object|null} options.data - Data to send
 * @param {number} options.timeout - Request timeout in milliseconds
 * @param {string} options.contentType - Content-Type header
 * @param {string} options.context - Context identifier for error messages
 * @param {Function} options.onProgress - Progress callback for download
 * @param {Function} options.onUploadProgress - Progress callback for upload
 * @returns {XMLHttpRequest} The configured XHR object
 */
export function createXhrRequest(method, url, options = {}) {
  const xhr = new XMLHttpRequest();
  const {
    onSuccess = null,
    onError = null,
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
    if (typeof showError !== 'undefined') {
      showError('Request timed out', context);
    }
  };

  xhr.onerror = function () {
    if (typeof showError !== 'undefined') {
      showError('Network error', context);
    }
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
 * Simple fetch wrapper with centralized error handling
 * @param {string} url - The URL to request
 * @param {Object} options - Fetch options
 * @returns {Promise<Response>} The fetch response
 */
export async function fetchWithErrorHandling(url, options = {}) {
  try {
    const response = await fetch(url, options);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response;
  } catch (error) {
    console.error('Fetch error:', error);
    throw error;
  }
}
