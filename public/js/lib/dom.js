/**
 * @module lib/dom
 * DOM utility functions - Re-exports from lib/dom/ submodules for backward compatibility
 */

export { _, get, query, queryAll, attr, val } from './dom/selector.js';
export { hide, show } from './dom/visibility.js';
export { ChangeColor, ChangeOpacity, css } from './dom/styles.js';
export { resizeWindow, position, getPosition } from './dom/layout.js';
export { on, off, ready, trigger } from './dom/events.js';
