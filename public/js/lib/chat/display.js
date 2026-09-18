/**
 * Chat display functions
 * Dependencies: chat/state.js, chat/utils.js, ../dom/selector.js and ../dom/dialog.js
 */
import { _ } from '../dom/selector.js';
import { ScreenCR } from './state.js';
import { ClearCR, RefreshCR } from './utils.js';
import { ColorsForCR, rgb, getUserColor } from '../colors.js';
import { escapeHtml } from '../browser.js';

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
  // Prevent overwriting existing messages (already translated)
  if (ScreenCR[order] !== undefined) {
    return;
  }
  
  // Use the centralized version from colors.js
  if (!ColorsForCR[who]) {
    const colors = getUserColor(who);
    const bg = colors[0];
    const fg = colors[1];
    ColorsForCR[who] = [bg, fg, 20];
  }

  const rgbFunc = rgb;
  const escapeHtmlFunc = escapeHtml;

    // Determine CSS class based on sender
    let messageClass = 'chat-message';
    let styleAttr = '';

    if (who === '----') {
      messageClass += ' chat-message--system';
    } else if (who === 'Intervenant') {
      messageClass += ' chat-message--intervenant';
    } else {
      messageClass += ' chat-message--user';
      styleAttr = ` style="background-color: ${rgbFunc(ColorsForCR[who][0])
      }; color: ${rgbFunc(ColorsForCR[who][1])
      }; margin-left: 20px;"`;
    }

    ScreenCR[order] = `<li role="listitem" class="${messageClass}"${styleAttr}>${
      escapeHtmlFunc(`${who} : ${ch}`)}</li>`;
    RefreshCR();
}
