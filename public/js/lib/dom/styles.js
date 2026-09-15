/**
 * DOM Style Utilities
 * ES Module for manipulating element styles
 */

import { _ } from './selector.js';

/**
 * Change the color of a HTML div
 * @param {string} boxname - The div identifier
 * @param {string} color - Three colors separated by | (bgcolor|bordercolor|textcolor)
 */
export function ChangeColor(boxname, color) {
    const [bgcolor, bordercolor, textcolor] = color.split('|');
    const e = _(boxname);
    if (!e) return;
    const opacityEl = _('selectopacity');
    const opacity = opacityEl ? opacityEl.value : '0.5';
    if (textcolor) e.style.color = `rgb(${textcolor})`;
    else e.style.color = 'rgb(255,255,255)';
    e.style.backgroundColor = `rgba(${bgcolor},${opacity})`;
    e.style.boxShadow = `2px 2px 5px rgb(${bgcolor})`;
    e.style.textShadow = `2px 0 0 rgb(${bordercolor}), -2px 0 0 rgb(${bordercolor}), 0 2px 0 rgb(${bordercolor}), 0 -2px 0 rgb(${bordercolor}), 1px 1px rgb(${bordercolor}), -1px -1px 0 rgb(${bordercolor}), 1px -1px 0 rgb(${bordercolor}), -1px 1px 0 rgb(${bordercolor})`;
}

/**
 * Change the opacity of a HTML div
 * @param {string} boxname - The div identifier
 * @param {string} opacity - A [0,1] real number
 */
export function ChangeOpacity(boxname, opacity) {
    const e = _(boxname);
    if (!e) return;
    const color = e.style.backgroundColor;
    if (!color || color === '') return;
    const [red, green, blue] = color.substring(color.indexOf('(') + 1, color.lastIndexOf(')')).split(/,\s*/);
    const bgcolor = [red, green, blue];
    e.style.backgroundColor = `rgba(${bgcolor},${opacity})`;
}

/**
 * Set multiple CSS properties
 * @param {HTMLElement|string} element - Element or element ID
 * @param {Object} styles - Object with CSS property-value pairs
 */
export function css(element, styles) {
    const el = typeof element === 'string' ? _(element) : element;
    if (el) {
        Object.assign(el.style, styles);
    }
}
