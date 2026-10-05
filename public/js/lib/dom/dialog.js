/**
 * DOM Dialog Utilities
 * ES Module for dialog element management
 */

import { _ } from './selector.js';

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
