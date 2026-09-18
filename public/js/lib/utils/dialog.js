/**
 * @module lib/utils/dialog
 * Dialog Utilities - Native <dialog> element replacements for jQuery UI .dialog() functionality
 * Provides modal dialog management without jQuery UI dependency
 */

import { get } from '../dom/selector.js';

/**
 * Open a dialog by ID
 * @param {string} id - The dialog element ID
 */
export function openDialog(id) {
  const dialog = get(id);
  if (dialog && dialog.tagName === 'DIALOG') {
    dialog.showModal();
  }
}

/**
 * Close a dialog by ID
 * @param {string} id - The dialog element ID
 */
export function closeDialog(id) {
  const dialog = get(id);
  if (dialog && dialog.tagName === 'DIALOG') {
    dialog.close();
  }
}

/**
 * Toggle a dialog by ID
 * @param {string} id - The dialog element ID
 */
export function toggleDialog(id) {
  const dialog = get(id);
  if (dialog && dialog.tagName === 'DIALOG') {
    if (dialog.open) {
      dialog.close();
    } else {
      dialog.showModal();
    }
  }
}

/**
 * Create a dialog from a div element (converts existing divs to dialogs)
 * @param {string} id - The div element ID to convert
 * @param {Object} [options] - Dialog options
 * @param {string} [options.title] - Dialog title
 * @param {boolean} [options.modal=true] - Whether the dialog is modal
 * @param {Object[]} [options.buttons] - Array of buttons: { text: string, click: Function, class: string }
 * @param {Function} [options.close] - Callback when dialog closes
 * @returns {HTMLDialogElement} The created dialog element
 */
export function createDialog(id, options = {}) {
  const div = get(id);
  if (!div || div.tagName === 'DIALOG') {
    return div;
  }

  // Create dialog element
  const dialog = document.createElement('dialog');

  // Copy attributes from div
  if (div.id) {
    dialog.id = div.id;
  }
  if (div.className) {
    dialog.className = div.className;
  }

  // Create content container
  const content = document.createElement('div');
  content.className = 'dialog-content';

  // Move all children from div to content
  while (div.firstChild) {
    content.appendChild(div.firstChild);
  }

  // Add title if provided
  if (options.title) {
    const title = document.createElement('h3');
    title.className = 'dialog-title';
    title.textContent = options.title;
    dialog.appendChild(title);
  }

  dialog.appendChild(content);

  // Add buttons if provided
  if (options.buttons && options.buttons.length > 0) {
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'dialog-buttons';

    options.buttons.forEach((btn) => {
      const button = document.createElement('button');
      button.textContent = btn.text || 'OK';
      button.className = btn.class || 'dialog-button';

      if (btn.click) {
        button.addEventListener('click', () => {
          btn.click.call(dialog);
          if (btn.close !== false) {
            closeDialog(id);
          }
        });
      } else {
        button.addEventListener('click', () => closeDialog(id));
      }

      buttonContainer.appendChild(button);
    });

    dialog.appendChild(buttonContainer);
  }

  // Handle close callback
  if (options.close) {
    dialog.addEventListener('close', () => options.close.call(dialog));
  }

  // Replace div with dialog
  if (div.parentNode) {
    div.parentNode.replaceChild(dialog, div);
  }

  return dialog;
}

/**
 * Convert all existing div dialogs to native dialog elements
 * Looks for divs that are used as dialogs and converts them
 */
export function upgradeLegacyDialogs() {
  // Find legacy div elements that look like dialogs (previously created with jQuery .dialog())
  const dialogDivs = document.querySelectorAll('div[id*="dialog-"], div.dialog');

  dialogDivs.forEach((div) => {
    // Skip if already a dialog
    if (div.tagName === 'DIALOG') return;

    // Check if this div is referenced in any script as a dialog
    // For now, we'll just convert common dialog IDs
    const dialogIds = [
      'dialog-invite', 'dialog-mail', 'dialog-message', 'dialog-warning',
      'dialog-choose', 'dialog-download', 'dialog-licence', 'dialog-delay',
      'dialog-ratio', 'dialog-all-lang', 'dialog-keywordParameter',
      'mouse-position', 'dialog-message',
    ];

    if (dialogIds.some((id) => div.id === id)) {
      createDialog(div.id, { modal: true });
    }
  });
}

// Auto-upgrade legacy dialogs when DOM is ready
if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', upgradeLegacyDialogs);
  } else {
    upgradeLegacyDialogs();
  }
}
