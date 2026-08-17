/**
 * DOM Utilities - Native JavaScript replacements for jQuery core functionality
 * Provides cross-browser DOM manipulation helpers
 */

// =============================================
// Selectors
// =============================================

/**
 * Shortcut for document.getElementById()
 * @param {string} id - The element ID
 * @returns {HTMLElement|null} The DOM element or null
 */
function get(id) {
    return document.getElementById(id);
}

/**
 * Shortcut for document.querySelector()
 * @param {string} selector - CSS selector
 * @returns {HTMLElement|null} The first matching element
 */
function query(selector) {
    return document.querySelector(selector);
}

/**
 * Shortcut for document.querySelectorAll()
 * @param {string} selector - CSS selector
 * @returns {NodeList} List of matching elements
 */
function queryAll(selector) {
    return document.querySelectorAll(selector);
}

// =============================================
// Events
// =============================================

/**
 * Add an event listener
 * @param {HTMLElement|string} element - Element or ID
 * @param {string} event - Event name
 * @param {Function} fn - Callback function
 * @param {Object} [options] - Options (e.g., { capture: true, once: true })
 */
function on(element, event, fn, options) {
    const el = typeof element === 'string' ? get(element) : element;
    if (el) {
        el.addEventListener(event, fn, options);
    }
}

/**
 * Remove an event listener
 * @param {HTMLElement|string} element - Element or ID
 * @param {string} event - Event name
 * @param {Function} fn - Callback function
 */
function off(element, event, fn) {
    const el = typeof element === 'string' ? get(element) : element;
    if (el) {
        el.removeEventListener(event, fn);
    }
}

// =============================================
// DOM Ready
// =============================================

/**
 * Execute a function when the DOM is loaded
 * @param {Function} fn - Function to execute
 */
function ready(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        setTimeout(fn, 0);
    }
}

// =============================================
// Styles
// =============================================

/**
 * Set CSS styles on an element
 * @param {HTMLElement|string} element - Element or ID
 * @param {Object} styles - Object with CSS properties (e.g., { top: '10px', left: '20px' })
 */
function css(element, styles) {
    const el = typeof element === 'string' ? get(element) : element;
    if (el && el.style) {
        for (const [property, value] of Object.entries(styles)) {
            el.style[property] = value;
        }
    }
}

// =============================================
// Attributes
// =============================================

/**
 * Get or set an attribute
 * @param {HTMLElement|string} element - Element or ID
 * @param {string} name - Attribute name
 * @param {string} [value] - Value to set (optional)
 * @returns {string|null} The attribute value if value is not provided
 */
function attr(element, name, value) {
    const el = typeof element === 'string' ? get(element) : element;
    if (!el) return null;

    if (value !== undefined) {
        el.setAttribute(name, value);
        return value;
    }
    return el.getAttribute(name);
}

// =============================================
// Value
// =============================================

/**
 * Get or set the value of an input/textarea
 * @param {HTMLElement|string} element - Element or ID
 * @param {string} [value] - Value to set (optional)
 * @returns {string} The element value if value is not provided
 */
function val(element, value) {
    const el = typeof element === 'string' ? get(element) : element;
    if (!el) return null;

    if (value !== undefined) {
        el.value = value;
        return value;
    }
    return el.value;
}

// =============================================
// Position
// =============================================

/**
 * Get the position of an element (replaces jQuery .position() method)
 * @param {HTMLElement|string} element - Element or ID
 * @returns {Object} Position { top: number, left: number }
 */
function position(element) {
    const el = typeof element === 'string' ? get(element) : element;
    if (!el) return { top: 0, left: 0 };

    const rect = el.getBoundingClientRect();
    return {
        top: rect.top + window.scrollY,
        left: rect.left + window.scrollX
    };
}

// =============================================
// Module export (for use with import/export)
// =============================================

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        get, query, queryAll, on, off, ready, css, attr, val, position
    };
}
