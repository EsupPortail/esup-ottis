/**
 * DOM Event Utilities
 * ES Module for event handling
 */

import { safeSetTimeout } from '../utils/cleanup.js';

/**
 * Add event listener
 * @param {HTMLElement|string} element - Element or element ID
 * @param {string} event - Event name (can be space-separated for multiple events)
 * @param {Function} handler - Event handler function
 * @param {boolean|Object} [options] - Event listener options
 */
export function on(element, event, handler, options) {
    const el = typeof element === 'string' ? document.getElementById(element) : element;
    if (el) {
        // Handle multiple events separated by spaces
        const events = event.split(/\s+/);
        events.forEach((evt) => {
            el.addEventListener(evt, handler, options);
        });
    }
}

/**
 * Remove event listener
 * @param {HTMLElement|string} element - Element or element ID
 * @param {string} event - Event name (can be space-separated for multiple events)
 * @param {Function} handler - Event handler function
 */
export function off(element, event, handler) {
    const el = typeof element === 'string' ? document.getElementById(element) : element;
    if (el) {
        // Handle multiple events separated by spaces
        const events = event.split(/\s+/);
        events.forEach((evt) => {
            el.removeEventListener(evt, handler);
        });
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
        safeSetTimeout(callback, 0);
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
