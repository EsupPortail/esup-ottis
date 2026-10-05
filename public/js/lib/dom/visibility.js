/**
 * DOM Visibility Utilities
 * ES Module for showing/hiding elements
 */

/** Import du sélecteur depuis selector.js */
import { _ } from './selector.js';

/**
 * Hide element by setting display to none
 * @param {HTMLElement|string} element - Element or element ID
 */
export function hide(element) {
    const el = typeof element === 'string' ? _(element) : element;
    if (el) el.style.display = 'none';
}

/**
 * Show element by setting display to block
 * @param {HTMLElement|string} element - Element or element ID
 * @param {string} [display='block'] - Display value
 */
export function show(element, display = 'block') {
    const el = typeof element === 'string' ? _(element) : element;
    if (el) el.style.display = display;
}

/**
 * Toggle element visibility
 * @param {HTMLElement|string} element - Element or element ID
 */
export function toggle(element) {
    const el = typeof element === 'string' ? _(element) : element;
    if (el) {
        el.style.display = el.style.display === 'none' ? '' : 'none';
    }
}
