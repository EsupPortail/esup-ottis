/**
 * Network Utilities
 * ES Module for network-related helper functions
 */

/**
 * Get CSRF token from DOM
 * @returns {string} - CSRF token value
 */
function getCsrfToken() {
    // Try meta tag first (Symfony standard)
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.content;
    
    // Fallback to input field (legacy)
    const input = document.querySelector('input[name="csrf_token"]');
    return input ? input.value : '';
}

/**
 * Add CSRF token to data object or URL
 * @param {Object|string|FormData} data - Data object, URL string, or FormData
 * @returns {Object|string|FormData} - Data with CSRF token or URL with token
 */
export function addCsrfToken(data) {
    const token = getCsrfToken();
    if (!token) return data;

    if (data instanceof FormData) {
        data.append('csrf_token', token);
        return data;
    }

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
