/** @module conference/network */
import { safeSetTimeout } from '../utils/cleanup.js';
import { addCsrfToken } from '../network/utils.js';

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

/**
 * Create a fetch request with centralized error handling and progress support
 * Falls back to XMLHttpRequest when upload progress tracking is required
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {string} url - The URL to request
 * @param {Object} [options={}] - Options object
 * @param {Function} [options.onSuccess] - Success callback
 * @param {Function} [options.onError] - Error callback
 * @param {FormData|Object|null} [options.data] - Request data
 * @param {number} [options.timeout=30000] - Request timeout in ms
 * @param {string} [options.contentType] - Content-Type header
 * @param {string} [options.context] - Context for error identification
 * @param {Function} [options.onProgress] - Download progress callback
 * @param {Function} [options.onUploadProgress] - Upload progress callback (triggers XHR fallback)
 * @returns {Object} Object with send() and abort() methods
 */
export function createFetchRequest(method, url, options = {}) {
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

  if (onUploadProgress) {
    console.warn('createFetchRequest: Upload progress tracking requires XMLHttpRequest fallback');
    const xhr = createXhrRequest(method, url, options);
    return {
      send: (body = data) => {
        xhr.send(body);
        return Promise.resolve(xhr);
      },
      abort: () => xhr.abort(),
      xhr: xhr
    };
  }

  const controller = new AbortController();
  const signal = controller.signal;
  let timeoutId = null;

  const fetchOptions = {
    method,
    signal,
    headers: {}
  };

  if (data) {
    if (data instanceof FormData) {
      fetchOptions.body = data;
    } else if (typeof data === 'object') {
      fetchOptions.body = new URLSearchParams(data).toString();
      fetchOptions.headers['Content-Type'] = contentType;
    } else {
      fetchOptions.body = data;
      fetchOptions.headers['Content-Type'] = contentType;
    }
  }

  const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
  if (csrfToken && method !== 'GET') {
    if (fetchOptions.body instanceof FormData) {
      fetchOptions.body.append('csrf_token', csrfToken);
    } else if (fetchOptions.body) {
      fetchOptions.body += `&csrf_token=${encodeURIComponent(csrfToken)}`;
    } else {
      const formData = new FormData();
      formData.append('csrf_token', csrfToken);
      fetchOptions.body = formData;
    }
  }

  timeoutId = safeSetTimeout(() => {
    controller.abort();
    if (onError) onError({ status: 0, statusText: 'Timeout', responseText: '' }, context);
  }, timeout);

  const createResponseObject = (response, responseText) => ({
    status: response.status,
    statusText: response.statusText,
    responseText,
    getResponseHeader: (header) => response.headers.get(header)
  });

  const request = {
    send: async (body = data) => {
      if (timeoutId) clearTimeout(timeoutId);
      timeoutId = safeSetTimeout(() => {
        controller.abort();
        if (onError) onError({ status: 0, statusText: 'Timeout', responseText: '' }, context);
      }, timeout);

      if (body !== data) {
        if (body instanceof FormData) {
          fetchOptions.body = body;
          if (csrfToken) fetchOptions.body.append('csrf_token', csrfToken);
        } else if (typeof body === 'object') {
          fetchOptions.body = new URLSearchParams(body).toString();
          fetchOptions.headers['Content-Type'] = contentType;
        } else {
          fetchOptions.body = body;
        }
      }

      try {
        const response = await fetch(url, fetchOptions);

        if (!response.ok) {
          const errorText = await response.text().catch(() => '');
          const errorResponse = createResponseObject(response, errorText);
          if (onError) onError(errorResponse, context);
          return errorResponse;
        }

        if (onProgress && response.body) {
          const reader = response.body.getReader();
          const contentLength = response.headers.get('content-length');
          const total = contentLength ? parseInt(contentLength) : 0;
          let loaded = 0;
          const chunks = [];

          while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            chunks.push(value);
            loaded += value.length;
            if (onProgress) onProgress({ loaded, total, lengthComputable: total > 0 });
          }

          const responseText = new TextDecoder().decode(new Uint8Array(chunks.flat()));
          const successResponse = createResponseObject(response, responseText);
          if (onSuccess) onSuccess(successResponse);
          return successResponse;
        }

        const responseText = await response.text();
        const successResponse = createResponseObject(response, responseText);
        if (onSuccess) onSuccess(successResponse);
        return successResponse;

      } catch (error) {
        clearTimeout(timeoutId);
        if (error.name === 'AbortError') {
          if (onError) onError({ status: 0, statusText: 'Request aborted', responseText: '' }, context);
        } else if (onError) {
          onError({ status: 0, statusText: error.message || 'Network error', responseText: '' }, context);
        }
        return { status: 0, statusText: error.message || 'Network error', responseText: '' };
      }
    },
    abort: () => {
      clearTimeout(timeoutId);
      controller.abort();
    }
  };

  return request;
}

// Re-export addCsrfToken from network/utils.js for backward compatibility
export { addCsrfToken } from '../network/utils.js';
