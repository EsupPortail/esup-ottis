/**
 * DOM Selector Utilities
 * ES Module for DOM element selection
 */

/**
 * Get element by ID
 * @param {string} id - Element ID
 * @returns {HTMLElement|null}
 */
export function get(id) {
    return document.getElementById(id);
}

/**
 * Query selector
 * @param {string} selector - CSS selector
 * @returns {HTMLElement|null}
 */
export function query(selector) {
    return document.querySelector(selector);
}

/**
 * Query all elements
 * @param {string} selector - CSS selector
 * @returns {NodeList}
 */
export function queryAll(selector) {
    return document.querySelectorAll(selector);
}

/**
 * Shortcut for getElementById
 * @param {string} id - Element ID
 * @returns {HTMLElement|null}
 */
export function _(id) {
    return get(id);
}

/**
 * Get or set an attribute
 * @param {HTMLElement|string} element - Element or ID
 * @param {string} name - Attribute name
 * @param {string} [value] - Value to set (optional)
 * @returns {string|null} The attribute value if value is not provided
 */
export function attr(element, name, value) {
    const el = typeof element === 'string' ? get(element) : element;
    if (!el) return null;

    if (value !== undefined) {
        el.setAttribute(name, value);
        return value;
    }
    return el.getAttribute(name);
}

/**
 * Get or set the value of an input/textarea
 * @param {HTMLElement|string} element - Element or ID
 * @param {string} [value] - Value to set (optional)
 * @returns {string} The element value if value is not provided
 */
export function val(element, value) {
    const el = typeof element === 'string' ? get(element) : element;
    if (!el) return null;

    if (value !== undefined) {
        el.value = value;
        return value;
    }
    return el.value;
}
