/**
 * @module lib/dom
 * DOM utility functions
 */

/**
 * Shortcut for document.getElementById
 * @param {string} el - Element ID
 * @returns {HTMLElement|null} The DOM element or null
 */
export function _(el) {
    return document.getElementById(el);
}

/**
 * Hide an HTML element
 * @param {string|HTMLElement} e - Element ID or element
 */
export function hide(e) {
    const el = typeof e === 'string' ? _(e) : e;
    if (el) el.style.display = 'none';
}

/**
 * Show an HTML element
 * @param {string|HTMLElement} e - Element ID or element
 */
export function show(e) {
    const el = typeof e === 'string' ? _(e) : e;
    if (el) el.style.display = 'block';
}

/**
 * Resize a window element to fit the viewport
 * @param {string} wname - Window element ID
 */
export function resizeWindow(wname) {
    const wc = _(wname);
    if (!wc) return;
    wc.style.width = (window.innerWidth - 20) + "px";
    wc.style.height = (window.innerHeight - 48) + "px";
    wc.style.top = "48px";
    wc.style.left = "0px";
}

/**
 * Change the color of a HTML div
 * @param {string} boxname - The div identifier
 * @param {string} color - Three colors separated by |
 */
export function ChangeColor(boxname, color) {
    const [bgcolor, bordercolor, textcolor] = color.split('|');
    const e = _(boxname);
    if (!e) return;
    const opacityEl = _("selectopacity");
    const opacity = opacityEl ? opacityEl.value : '0.5';
    if (textcolor) e.style.color = 'rgb(' + textcolor + ')';
    else e.style.color = 'rgb(255,255,255)';
    e.style.backgroundColor = 'rgba(' + bgcolor + ',' + opacity + ')';
    e.style.boxShadow = '2px 2px 5px rgb(' + bgcolor + ')';
    e.style.textShadow = '2px 0 0 rgb(' + bordercolor + '), -2px 0 0 rgb(' + bordercolor + '), 0 2px 0 rgb(' + bordercolor + '), 0 -2px 0 rgb(' + bordercolor + '), 1px 1px rgb(' + bordercolor + '), -1px -1px 0 rgb(' + bordercolor + '), 1px -1px 0 rgb(' + bordercolor + '), -1px 1px 0 rgb(' + bordercolor + ')';
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
    e.style.backgroundColor = 'rgba(' + bgcolor + ',' + opacity + ')';
}
