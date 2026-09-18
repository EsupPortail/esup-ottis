/**
 * @module utils/cleanup
 * Centralized cleanup utilities for preventing memory leaks
 * Manages timers, requests, and cleanup callbacks to prevent memory leaks
 * from setTimeout, setInterval, and XMLHttpRequest/fetch operations.
 */

// Store all active timer IDs
const activeTimers = new Set();

// Store all active request objects (with abort capability)
const activeRequests = new Set();

// Store cleanup callbacks to execute on page unload
const cleanupCallbacks = [];

/**
 * Register a timer ID for automatic cleanup
 * @param {number} timerId - The timer ID from setTimeout or setInterval
 * @returns {number} The timer ID
 */
export function registerTimer(timerId) {
  activeTimers.add(timerId);
  return timerId;
}

/**
 * Register a cleanup callback to execute on page unload
 * @param {Function} callback - Function to call during cleanup
 * @returns {Function} The callback function
 */
export function registerCleanup(callback) {
  cleanupCallbacks.push(callback);
  return callback;
}

/**
 * Register a request for automatic cleanup
 * @param {Object} request - The request object with abort method
 * @returns {Object} The request object
 */
export function registerRequest(request) {
  if (request && typeof request.abort === 'function') {
    activeRequests.add(request);
  }
  return request;
}

/**
 * Clear all registered timers
 */
export function clearAllTimers() {
  activeTimers.forEach(timerId => {
    try {
      clearTimeout(timerId);
      clearInterval(timerId);
    } catch (error) {
      console.warn('Error clearing timer:', error);
    }
  });
  activeTimers.clear();
}

/**
 * Abort all registered requests
 */
export function abortAllRequests() {
  activeRequests.forEach(request => {
    try {
      request.abort();
    } catch (error) {
      console.warn('Error aborting request:', error);
    }
  });
  activeRequests.clear();
}

/**
 * Execute all registered cleanup callbacks
 */
export function executeCleanupCallbacks() {
  cleanupCallbacks.forEach(callback => {
    try {
      callback();
    } catch (error) {
      console.warn('Error in cleanup callback:', error);
    }
  });
  cleanupCallbacks.length = 0;
}

/**
 * Cleanup everything - call this on page unload
 */
export function cleanupAll() {
  clearAllTimers();
  abortAllRequests();
  executeCleanupCallbacks();
}

/**
 * Safe setTimeout that automatically registers for cleanup
 * @param {Function} fn - Callback function
 * @param {number} delay - Delay in milliseconds
 * @param {...any} args - Arguments to pass to the callback
 * @returns {number} Timer ID
 */
export function safeSetTimeout(fn, delay, ...args) {
  const timerId = setTimeout(() => {
    activeTimers.delete(timerId);
    fn(...args);
  }, delay);
  return registerTimer(timerId);
}

/**
 * Safe setInterval that automatically registers for cleanup
 * @param {Function} fn - Callback function
 * @param {number} interval - Interval in milliseconds
 * @param {...any} args - Arguments to pass to the callback
 * @returns {number} Timer ID
 */
export function safeSetInterval(fn, interval, ...args) {
  const timerId = setInterval(fn, interval, ...args);
  return registerTimer(timerId);
}

// Install automatic cleanup on beforeunload
if (typeof window !== 'undefined') {
  window.addEventListener('beforeunload', cleanupAll);
}
