/**
 * Fetch Utilities
 * ES Module for Fetch API operations
 */

import { addCsrfToken } from './utils.js';

/**
 * Create a fetch request with default error handling
 * @param {string} url - Request URL
 * @param {Object} [options] - Fetch options
 * @param {string} [options.method='GET'] - HTTP method
 * @param {Object} [options.headers] - Request headers
 * @param {Object|string} [options.body] - Request body
 * @returns {Promise<Response>}
 */
export async function createFetchRequest(url, options = {}) {
    const { method = 'GET', headers = {}, body } = options;
    
    const fetchOptions = {
        method,
        headers: new Headers(headers),
        credentials: 'same-origin'
    };
    
    // Add CSRF token to headers
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        fetchOptions.headers.set('X-CSRF-Token', csrfToken);
    }
    
    // Handle body
    if (body) {
        if (body instanceof FormData) {
            fetchOptions.body = body;
        } else if (typeof body === 'object') {
            fetchOptions.headers.set('Content-Type', 'application/json');
            fetchOptions.body = JSON.stringify(body);
        } else {
            fetchOptions.body = body;
        }
    }
    
    try {
        const response = await fetch(url, fetchOptions);
        
        if (!response.ok) {
            const error = new Error(`HTTP ${response.status}: ${response.statusText}`);
            error.status = response.status;
            try {
                error.response = await response.json();
            } catch (e) {
                error.response = await response.text();
            }
            throw error;
        }
        
        return response;
    } catch (error) {
        if (error instanceof TypeError) {
            error.message = 'Network error: ' + error.message;
        }
        throw error;
    }
}

/**
 * Simple GET request using fetch
 * @param {string} url - Request URL
 * @returns {Promise<any>}
 */
export async function fetchGet(url) {
    const response = await createFetchRequest(url);
    try {
        return await response.json();
    } catch (e) {
        return await response.text();
    }
}

/**
 * Simple POST request using fetch
 * @param {string} url - Request URL
 * @param {Object} data - Data to send
 * @returns {Promise<any>}
 */
export async function fetchPost(url, data) {
    const response = await createFetchRequest(url, {
        method: 'POST',
        body: data
    });
    try {
        return await response.json();
    } catch (e) {
        return await response.text();
    }
}

/**
 * Simple PUT request using fetch
 * @param {string} url - Request URL
 * @param {Object} data - Data to send
 * @returns {Promise<any>}
 */
export async function fetchPut(url, data) {
    const response = await createFetchRequest(url, {
        method: 'PUT',
        body: data
    });
    try {
        return await response.json();
    } catch (e) {
        return await response.text();
    }
}

/**
 * Simple DELETE request using fetch
 * @param {string} url - Request URL
 * @returns {Promise<any>}
 */
export async function fetchDelete(url) {
    const response = await createFetchRequest(url, { method: 'DELETE' });
    try {
        return await response.json();
    } catch (e) {
        return await response.text();
    }
}
