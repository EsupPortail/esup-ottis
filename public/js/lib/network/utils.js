/**
 * Network Utilities
 * ES Module for network-related helper functions
 */

/**
 * Get CSRF token from meta tag
 * @returns {string} - CSRF token value
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

/**
 * Add CSRF token to data object or URL
 * @param {Object|string} data - Data object or URL string
 * @returns {Object|string} - Data with CSRF token or URL with token
 */
export function addCsrfToken(data) {
    const token = getCsrfToken();
    if (!token) return data;

    if (typeof data === 'string') {
        // If it's a URL, append as query parameter
        const separator = data.includes('?') ? '&' : '?';
        return data + separator + 'csrf_token=' + encodeURIComponent(token);
    }

    if (typeof data === 'object' && data !== null) {
        // If it's an object, add CSRF token property
        return { ...data, csrf_token: token };
    }

    return data;
}

/**
 * Get CSRF header name
 * @returns {string}
 */
export function getCsrfHeaderName() {
    return 'X-CSRF-Token';
}

/**
 * Get CSRF token for headers
 * @returns {string}
 */
export function getCsrfHeaderValue() {
    return getCsrfToken();
}
