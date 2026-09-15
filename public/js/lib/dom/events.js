/**
 * DOM Event Utilities
 * ES Module for event handling
 */

/**
 * Add event listener
 * @param {HTMLElement|string} element - Element or element ID
 * @param {string} event - Event name
 * @param {Function} handler - Event handler function
 * @param {boolean|Object} [options] - Event listener options
 */
export function on(element, event, handler, options) {
    const el = typeof element === 'string' ? document.getElementById(element) : element;
    if (el) {
        el.addEventListener(event, handler, options);
    }
}

/**
 * Remove event listener
 * @param {HTMLElement|string} element - Element or element ID
 * @param {string} event - Event name
 * @param {Function} handler - Event handler function
 */
export function off(element, event, handler) {
    const el = typeof element === 'string' ? document.getElementById(element) : element;
    if (el) {
        el.removeEventListener(event, handler);
    }
}

/**
 * Execute callback when DOM is ready
 * @param {Function} callback - Function to execute
 */
export function ready(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
}

/**
 * Trigger custom event
 * @param {HTMLElement|string} element - Element or element ID
 * @param {string} eventName - Custom event name
 * @param {Object} [detail] - Event detail data
 */
export function trigger(element, eventName, detail) {
    const el = typeof element === 'string' ? document.getElementById(element) : element;
    if (el) {
        const event = new CustomEvent(eventName, { detail });
        el.dispatchEvent(event);
    }
}
