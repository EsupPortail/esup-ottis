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
