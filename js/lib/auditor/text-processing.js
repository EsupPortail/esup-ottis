/**
 * Text processing utilities for auditor
 */

/**
 * @summary Decode HTML entities in a string
 * @param {string} text - Text containing HTML entities
 * @returns {string} Decoded text
 */
function decodeHtmlEntities(text) {
    let textarea = document.createElement("textarea");
    textarea.innerHTML = text;
    return textarea.value;
}

/**
 * @summary Count the number of lines in an element
 * @param {string} elm - Element ID
 * @returns {number} Number of lines
 */
function countLines(elm) {
    let el = _(elm);
    let divHeight = el.offsetHeight;
    let lineHeight = parseInt(el.style.lineHeight);
    let lines = divHeight / lineHeight;
    return lines;
}

/**
 * @summary Auto-size text to fit within max lines
 * @param {string} elm - Element ID (default: "titre")
 * @param {number} max_lines - Maximum number of lines (default: 3)
 */
function auto_size_text(elm = "titre", max_lines = 3) {

    let el = _(elm);
    let lines = countLines(elm);
    let size = parseInt(el.style.fontSize.replace("px", ""));
    if (lines > max_lines) {
        size--;
        el.style.fontSize = size + "px";
        el.style.lineHeight = size + "px";
        auto_size_text(elm);
    }
}
