/**
 * Chat display functions
 * Dependencies: conference/dom-helpers.js (for _)
 */
import { _ } from '../conference/dom-helpers.js';

// Use global ScreenCR from window (defined in common-func.js)
// This ensures compatibility with existing code
let ScreenCR = (typeof window !== 'undefined' && window.ScreenCR) ? window.ScreenCR : [];

// Export for use by other modules
export { ScreenCR };

/**
 * @summary Clear the classroom chat
 *
 * @returns {void}
 */
export function ClearCR() {
  const classroomEl = _('classroom');
  if (classroomEl) classroomEl.innerHTML = '<ul role="list" aria-live="polite" class="chat-message-list"></ul>';

  ScreenCR = [];
}

/**
 * @summary Refresh the classroom chat display
 *
 * @returns {void}
 */
export function RefreshCR() {
  let html = '<ul role="list" aria-live="polite" class="chat-message-list">';
  for (let i = 0; i < ScreenCR.length; i++) {
    if (ScreenCR[i] !== undefined) {
      html += window.DOMPurify.sanitize(ScreenCR[i]);
    }
  }
  html += '</ul>';

  const classroomEl = _('classroom');
  if (classroomEl) classroomEl.innerHTML = window.DOMPurify.sanitize(html);
}

/**
 * @summary Write a message to the classroom chat
 *
 * @param {string} who - Sender name
 * @param {string} ch - Message text
 * @param {number} order - Message order
 * @returns {void}
 */
export function EcrireCR(who, ch, order) {
  // Use the centralized version from common-func.js if available
  if (typeof window !== 'undefined' && window.ColorsForCR && typeof window.getUserColor === 'function') {
    if (!window.ColorsForCR[who]) {
      const colors = window.getUserColor(who);
      const bg = colors[0];
      const fg = colors[1];
      window.ColorsForCR[who] = [bg, fg, 20];
    }

    if (typeof ScreenCR === 'undefined') {
      ScreenCR = [];
    }

    const rgbFunc = typeof window.rgb === 'function' ? window.rgb : function (T) {
      return `rgb(${T[0]},${T[1]},${T[2]})`;
    };
    const escapeHtmlFunc = typeof window.escapeHtml === 'function' ? window.escapeHtml : function (text) {
      if (typeof text !== 'string') return '';
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    };

    // Determine CSS class based on sender
    let messageClass = 'chat-message';
    let styleAttr = '';

    if (who === '----') {
      messageClass += ' chat-message--system';
    } else if (who === 'Intervenant') {
      messageClass += ' chat-message--intervenant';
    } else {
      messageClass += ' chat-message--user';
      styleAttr = ` style="background-color: ${rgbFunc(window.ColorsForCR[who][0])
      }; color: ${rgbFunc(window.ColorsForCR[who][1])
      }; margin-left: 20px;"`;
    }

    ScreenCR[order] = `<li role="listitem" class="${messageClass}"${styleAttr}>${
      escapeHtmlFunc(`${who} : ${ch}`)}</li>`;
    RefreshCR();
  }
}
