/**
 * Chat send helpers
 * Module feuille : envoi de questions dans la file de la salle.
 * Ne pas importer main.js ou handlers.js ici (cycle main<->handlers,
 * historiquement contourne par un import dynamique dans handlers).
 */

import { _ } from '../dom/selector.js';
import { saveTextQCommon } from './network.js';
import { getBuiltinVoice, getBuiltinLanguage } from '../state/auditor.js';

/**
 * @summary Save a new question to the classroom queue with auto-detection of sender
 *
 * @param {string} ch - Text of the question
 * @returns {void}
 */
export function saveTextQ(ch) {
  // Determine sender identity based on the authenticated user when available.
  let senderName = 'Intervenant';
  let senderUid = 'intervenant';
  let roomId = '';
  let language = '';

  const auditorNameEl = _('AuditorName');
  const classroomIdEl = _('classroomid');
  const currentUserNameEl = document.getElementById('CurrentUserDisplayName');
  const currentUser = globalThis.OTTIS_USER || {};

  if (currentUser.fullName) {
    senderName = currentUser.fullName;
  } else if (currentUserNameEl && currentUserNameEl.value) {
    senderName = currentUserNameEl.value;
  } else if (auditorNameEl && auditorNameEl.value) {
    senderName = auditorNameEl.value;
  }

  if (currentUser.uid) {
    senderUid = currentUser.uid;
  } else if (currentUserNameEl && currentUserNameEl.value) {
    senderUid = currentUserNameEl.value;
  } else if (auditorNameEl && auditorNameEl.value) {
    senderUid = auditorNameEl.value;
  }

  if (classroomIdEl && classroomIdEl.value) {
    roomId = classroomIdEl.value;
  }

  const voice = getBuiltinVoice();
  const lang = getBuiltinLanguage();
  if (voice && voice.lang) {
    language = voice.lang;
  } else if (lang) {
    language = lang;
  }

  const senderRole = currentUser.isIntervenant ? 'intervenant' : 'auditor';
  saveTextQCommon(ch, `${senderUid}:${senderName}`, roomId, language, senderRole);
}
