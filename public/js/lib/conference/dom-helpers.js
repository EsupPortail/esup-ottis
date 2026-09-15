/** @module conference/dom-helpers */
// This module re-exports DOM helpers from canonical lib/dom/ modules
// for backward compatibility during migration.

export { _, get, query, queryAll, attr, val } from '../dom/selector.js';
export { openDialog, closeDialog } from '../dom/dialog.js';

