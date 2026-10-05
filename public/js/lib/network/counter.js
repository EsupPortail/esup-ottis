/**
 * Request Counter Utilities
 * ES Module for tracking pending requests
 */

let pendingRequests = 0;

/**
 * Increase pending requests counter and show loading indicator
 */
export function increasependingrequests() {
    pendingRequests++;
    showLoading();
}

/**
 * Decrease pending requests counter and hide loading indicator if no more requests
 */
export function decreasependingrequests() {
    pendingRequests = Math.max(0, pendingRequests - 1);
    if (pendingRequests === 0) {
        hideLoading();
    }
}

/**
 * Get current pending requests count
 * @returns {number}
 */
export function getPendingRequests() {
    return pendingRequests;
}

/**
 * Show loading indicator
 */
function showLoading() {
    const loadingEl = document.getElementById('loading');
    if (loadingEl) {
        loadingEl.style.display = 'block';
    }
}

/**
 * Hide loading indicator
 */
function hideLoading() {
    const loadingEl = document.getElementById('loading');
    if (loadingEl) {
        loadingEl.style.display = 'none';
    }
}

/**
 * Reset pending requests counter
 */
export function resetpendingrequests() {
    pendingRequests = 0;
    hideLoading();
}
