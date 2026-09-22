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

export function parseChatIdentity(senderValue) {
  const rawValue = String(senderValue ?? '').trim();
  if (!rawValue) {
    return { uid: '', displayName: '' };
  }

  const separatorIndex = rawValue.indexOf(':');
  if (separatorIndex > 0) {
    const uid = rawValue.slice(0, separatorIndex).trim();
    const displayName = rawValue.slice(separatorIndex + 1).trim();
    return { uid, displayName: displayName || uid };
  }

  return { uid: rawValue, displayName: rawValue };
}

export function isIntervenantSender(who, isIntervenant = false) {
  if (Boolean(isIntervenant)) {
    return true;
  }

  return false;
}

/**
 * @summary Write a message to the classroom chat
 *
 * @param {string} who - Sender name
 * @param {string} ch - Message text
 * @param {number} order - Message order
 * @returns {void}
 */
export function EcrireCR(who, ch, order, isIntervenant = false) {
  // Prevent overwriting existing messages (already translated)
  if (ScreenCR[order] !== undefined) {
    return;
  }

  const senderIdentity = parseChatIdentity(who);
  const displayWho = senderIdentity.displayName || senderIdentity.uid || who || '';
  const isIntervenantMessage = Boolean(isIntervenant) || isIntervenantSender(who, isIntervenant);

  // Use the centralized version from colors.js
  if (!ColorsForCR[displayWho]) {
    const colors = getUserColor(displayWho);
    const bg = colors[0];
    const fg = colors[1];
    ColorsForCR[displayWho] = [bg, fg, 20];
  }

  const rgbFunc = rgb;
  const escapeHtmlFunc = escapeHtml;

  // Determine CSS class based on sender
  let messageClass = 'chat-message';
  let styleAttr = '';

  if (who === '----') {
    messageClass += ' chat-message--system';;
  } else if (isIntervenantMessage) {
    messageClass += ' chat-message--intervenant';
  } else {
    messageClass += ' chat-message--user';
    styleAttr = ` style="background-color: ${rgbFunc(ColorsForCR[displayWho][0])}; color: ${rgbFunc(ColorsForCR[displayWho][1])}; margin-left: 20px;"`;
  }

  ScreenCR[order] = `<li role="listitem" class="${messageClass}"${styleAttr}>${
    escapeHtmlFunc(`${displayWho} : ${ch}`)}</li>`;
  RefreshCR();
}
