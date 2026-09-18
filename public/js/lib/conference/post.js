/**
 * @module lib/conference/post
 * Conference post-load initialization - converted from conference-post.js
 * Handles QR code generation, drag-and-drop, dialogs, and mail sending
 */

import { addCsrfToken } from '../network/utils.js';
import { createFetchRequest } from '../xhr.js';
import { makeDraggable } from '../utils/draggable.js';
import { upgradeLegacyDialogs, closeDialog } from '../utils/dialog.js';

/**
 * Initialize QR codes for shared and remote links
 */
export function initQRCode() {
  // Need to use window.QRCode as it's loaded from CDN
  if (typeof window.QRCode !== 'undefined') {
    const sharedqr = document.getElementById('sharedqr');
    const remotelink = document.getElementById('remotelink');
    const remoteqr = document.getElementById('remoteqr');
    const sharedlink = document.getElementById('sharedlink');

    if (sharedqr && sharedlink) {
      new window.QRCode(sharedqr, {
        text: sharedlink.href,
        width: 256,
        height: 256,
        colorDark: '#0000ff',
        colorLight: '#ffffff',
        correctLevel: window.QRCode.CorrectLevel.L,
      });

      sharedqr.title = '';
      sharedqr.onclick = function () {
        if (this.firstChild) {
          this.firstChild.toBlob((blob) => {
            const url = URL.createObjectURL(blob);
            window.open(url);
          });
        }
      };
    }

    if (remoteqr && remotelink) {
      new window.QRCode(remoteqr, {
        text: remotelink.href,
        width: 128,
        height: 128,
        colorDark: '#0000ff',
        colorLight: '#ffffff',
        correctLevel: window.QRCode.CorrectLevel.L,
      });

      remoteqr.title = '';
      remoteqr.onclick = function () {
        if (this.firstChild) {
          this.firstChild.toBlob((blob) => {
            const url = URL.createObjectURL(blob);
            window.open(url);
          });
        }
      };
    }
  }
}

/**
 * Initialize event listeners
 */
export function initEventListeners() {
  const bar = document.getElementById('bar');
  if (bar) {
    bar.addEventListener('keydown', (e) => {
      e.preventDefault();
    });
    bar.addEventListener('keyup', (e) => {
      e.preventDefault();
    });
    bar.addEventListener('keypress', (e) => {
      e.preventDefault();
    });
  }
}

/**
 * Initialize drag-and-drop for partialtitre
 */
export function initDragDrop() {
  const partialTitreEl = document.getElementById('partialtitre');
  if (partialTitreEl) {
    makeDraggable(partialTitreEl);
  }
}

/**
 * Initialize dialog functionality
 */
export function initDialogs() {
  // Dialog #dialog-invite will be auto-upgraded by upgradeLegacyDialogs()
  // or can be manually initialized with createDialog()
  upgradeLegacyDialogs();
}

/**
 * Set up beforeunload handler
 */
export function initBeforeUnload() {
  window.onbeforeunload = function (event) {
    event.returnValue = 'Quit';
  };
}

/**
 * Initialize form submission handling
 */
export function initFormHandling() {
  document.addEventListener('submit', (event) => {
    event.preventDefault();
  });
}

/**
 * Fix tab order issue for tabbed elements
 */
export function FixTabOrderIssue() {
  const l = document.getElementsByClassName('tabbed');
  for (let i = 0; i < l.length; i++) {
    if (l[i]) l[i].tabIndex = 0;
  }
}

/**
 * Send mail function
 */
export function EnvoiMail() {
  const FD = new FormData();

  const dialog_MailFrom = document.getElementById('dialog_MailFrom');
  const dialog_MailTo = document.getElementById('dialog_MailTo');
  const dialog_MailSubject = document.getElementById('dialog_MailSubject');
  const dialog_MailBody = document.getElementById('dialog_MailBody');

  if (dialog_MailFrom) FD.append('from', dialog_MailFrom.value);
  if (dialog_MailTo) FD.append('to', dialog_MailTo.value.replaceAll(' - ', ','));
  if (dialog_MailSubject) FD.append('subject', dialog_MailSubject.value);
  if (dialog_MailBody) FD.append('body', dialog_MailBody.innerHTML);

  // Add CSRF token
  addCsrfToken(FD);

  const request = createFetchRequest('POST', '/email/send', {
    onSuccess(response) {
      alert('Mail sent.');
    },
    onError(response, context) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        alert('Error, the mail cannot be sent.');
      }
    },
    data: FD,
    timeout: 10000,
    context: 'EnvoiMail',
  });

  request.send();

  closeDialog('dialog-mail');
}

/**
 * Initialize all post-load functionality
 * This is the main entry point that should be called after DOM is loaded
 */
export function initConferencePost() {
  // Initialize QR codes
  initQRCode();

  // Initialize event listeners
  initEventListeners();

  // Initialize drag-and-drop
  initDragDrop();

  // Initialize dialogs
  initDialogs();

  // Set up beforeunload handler
  initBeforeUnload();

  // Initialize form handling
  initFormHandling();

  // Fix tab order
  FixTabOrderIssue();
}

// Auto-initialize when DOM is loaded
if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConferencePost);
  } else {
    // DOM already loaded
    initConferencePost();
  }
}
