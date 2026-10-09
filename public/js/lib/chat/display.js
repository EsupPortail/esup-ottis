/**
 * Chat display functions
 * Dependencies: chat/state.js, chat/utils.js, ../dom/selector.js and ../dom/dialog.js
 */
import { ScreenCR } from './state.js';
import { ClearCR, RefreshCR } from './utils.js';
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
  const currentUser = globalThis.OMIST_USER || {};
  const isMe = Boolean(displayWho)
    && (displayWho === currentUser.fullName || senderIdentity.uid === currentUser.uid);

  const d = new Date();
  const time = `${d.getHours()}:${String(d.getMinutes()).padStart(2, '0')}`;

  const msgClasses = ['msg', isMe ? 'me' : 'o'];
  // Nom de l'intervenant mis en evidence dans tous les tchats
  if (isIntervenant) {
    msgClasses.push('msg-intervenant');
  }

  // Rendu maquette : bulle + meta (nom, heure) ; moi a droite, les autres a gauche
  ScreenCR[order] = `<li class="${msgClasses.join(' ')}">`
    + `<p class="meta"><strong>${escapeHtml(displayWho)}</strong> <time>${time}</time></p>`
    + `<p class="bub">${escapeHtml(ch)}</p></li>`;
  RefreshCR();
}
