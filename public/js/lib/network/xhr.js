/**
 * XHR Utilities
 * ES Module for XMLHttpRequest operations
 */

/**
 * Create an XMLHttpRequest with default error handling
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {string} url - Request URL
 * @param {Function} callback - Success callback
 * @param {Object} [options] - Request options
 * @param {Object} [options.data] - Data to send
 * @param {boolean} [options.async=true] - Async flag
 * @param {Object} [options.headers] - Request headers
 * @returns {XMLHttpRequest}
 */
export function createXhrRequest(method, url, callback, options = {}) {
    const { data = null, async = true, headers = {} } = options;
    
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, async);
    
    // Set headers
    for (const [key, value] of Object.entries(headers)) {
        xhr.setRequestHeader(key, value);
    }
    
    // Set CSRF header if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        xhr.setRequestHeader('X-CSRF-Token', csrfToken);
    }
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = xhr.responseText ? JSON.parse(xhr.responseText) : null;
                callback(null, response, xhr);
            } catch (e) {
                callback(new Error('Failed to parse JSON response'), null, xhr);
            }
        } else {
            handleXhrError(xhr, callback);
        }
    };
    
    xhr.onerror = function() {
        callback(new Error('Network error'), null, xhr);
    };
    
    xhr.send(data ? JSON.stringify(data) : null);
    return xhr;
}

/**
 * Handle XHR error
 * @param {XMLHttpRequest} xhr - The XMLHttpRequest object
 * @param {Function} callback - Error callback
 */
export function handleXhrError(xhr, callback) {
    let error;
    try {
        const response = xhr.responseText ? JSON.parse(xhr.responseText) : null;
        error = new Error(response?.message || response?.error || `HTTP ${xhr.status}: ${xhr.statusText}`);
        error.status = xhr.status;
        error.response = response;
    } catch (e) {
        error = new Error(`HTTP ${xhr.status}: ${xhr.statusText}`);
        error.status = xhr.status;
    }
    callback(error, null, xhr);
}

/**
 * Simple GET request
 * @param {string} url - Request URL
 * @param {Function} callback - Callback(error, response)
 * @returns {XMLHttpRequest}
 */
export function get(url, callback) {
    return createXhrRequest('GET', url, callback);
}

/**
 * Simple POST request
 * @param {string} url - Request URL
 * @param {Object} data - Data to send
 * @param {Function} callback - Callback(error, response)
 * @returns {XMLHttpRequest}
 */
export function post(url, data, callback) {
    return createXhrRequest('POST', url, callback, { 
        data,
        headers: { 'Content-Type': 'application/json' }
    });
}
