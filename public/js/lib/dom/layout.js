/**
 * DOM Layout Utilities
 * ES Module for window and element layout operations
 */

import { _ } from './selector.js';

/**
 * Resize a window element to fit the viewport
 * @param {string} wname - Window element ID
 */
export function resizeWindow(wname) {
    const wc = _(wname);
    if (!wc) return;
    wc.style.width = `${window.innerWidth - 20}px`;
    wc.style.height = `${window.innerHeight - 48}px`;
    wc.style.top = '48px';
    wc.style.left = '0px';
}

/**
 * Position element absolutely
 * @param {HTMLElement|string} element - Element or element ID
 * @param {number} x - X position
 * @param {number} y - Y position
 */
export function position(element, x, y) {
    const el = typeof element === 'string' ? _(element) : element;
    if (el) {
        el.style.position = 'absolute';
        el.style.left = x + 'px';
        el.style.top = y + 'px';
    }
}

/**
 * Get element position (compatible with jQuery .position() method)
 * Returns position relative to document (includes scroll offset)
 * @param {HTMLElement|string} element - Element or element ID
 * @returns {Object} - Object with top and left properties (includes scroll offset)
 */
export function getPosition(element) {
    const el = typeof element === 'string' ? _(element) : element;
    if (el) {
        const rect = el.getBoundingClientRect();
        return {
            top: rect.top + window.scrollY,
            left: rect.left + window.scrollX,
        };
    }
    return { top: 0, left: 0 };
}
