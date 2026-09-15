/**
 * Chat display functions
 * Dependencies: chat/state.js, chat/utils.js, conference/dom-helpers.js
 */
import { _ } from '../conference/dom-helpers.js';
import { ScreenCR } from './state.js';
import { ClearCR, RefreshCR } from './utils.js';

// Re-export for backward compatibility
export { ScreenCR, ClearCR, RefreshCR };

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
