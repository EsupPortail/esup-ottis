/** @module conference/dom-helpers */

/**
 * Shortcut for document.getElementById()
 * @param {string} id - The element ID
 * @returns {HTMLElement|null} The DOM element or null
 */
export function _(id) {
    return document.getElementById(id);
}

/**
 * Shortcut for document.querySelector()
 * @param {string} selector - CSS selector
 * @returns {HTMLElement|null} The first matching element
 */
export function query(selector) {
    return document.querySelector(selector);
}

/**
 * Shortcut for document.querySelectorAll()
 * @param {string} selector - CSS selector
 * @returns {NodeList} All matching elements
 */
export function queryAll(selector) {
    return document.querySelectorAll(selector);
}

/**
 * Open a dialog by ID
 * @param {string} id - The dialog element ID
 */
export function openDialog(id) {
    const dialog = _(id);
    if (dialog && dialog.tagName === 'DIALOG') {
        dialog.showModal();
    }
}

/**
 * Close a dialog by ID
 * @param {string} id - The dialog element ID
 */
export function closeDialog(id) {
    const dialog = _(id);
    if (dialog && dialog.tagName === 'DIALOG') {
        dialog.close();
    }
}
