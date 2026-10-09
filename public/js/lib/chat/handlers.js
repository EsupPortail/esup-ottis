/**
 * Chat message handlers
 * Dependencies: colors.js (for ColorsForCR), ../dom/selector.js (for _), display.js (for EcrireCR)
 */
import { _ } from '../dom/selector.js';
import { EcrireCR, isIntervenantSender } from './display.js';
import { ColorsForCR } from '../colors.js';
import { safeSetTimeout } from '../utils/cleanup.js';
import { isSendInProgress, setSendInProgress } from '../state/shared.js';
import { getBuiltinLanguage, getBuiltinVoice } from '../state/auditor.js';
import { nbnewQ, setNbnewQ, NBconnectedusers, Tconnectedusers, setNBconnectedusers, setTconnectedusers } from '../conference/state/core.js';
import { updateUsers } from '../conference/config-functions.js';
import { TranslateQ } from './network.js';
import { triggerNewMessageAlert } from './alert.js';
import { ScreenCR } from './state.js';

// Initialize default colors if not already set
if (!ColorsForCR['----']) {
  ColorsForCR['----'] = [[255, 0, 0], [255, 255, 255], 5];
}
if (!ColorsForCR.Intervenant) {
  ColorsForCR.Intervenant = [[0, 0, 255], [255, 255, 255], 0];
}

/**
 * @summary Default message handler for chat messages
 * Translates if needed, then displays using EcrireCR
 * Handles conference-specific features: EventNewQuestion, user connection tracking
 *
 * @param {string} who - Sender name
 * @param {string} text - Message text
 * @param {string} lang - Message language
 * @param {number} order - Message order
 * @param {boolean} isSpecial - Is this a special message
 * @param {string} specialType - Type of special message
 */
export function defaultMessageHandler(who, text, lang, order, isSpecial, specialType, senderRole = '') {
  // Skip if message already displayed to prevent re-translation
  if (ScreenCR[order] !== undefined) {
    return;
  }

  // Handle special messages for conference
  if (isSpecial) {
    // Handle new student arrived
    if (specialType === 'newstudentarrived') {
      setNBconnectedusers(NBconnectedusers + 1);
      Tconnectedusers.push(who);
      updateUsers();
    }
    // Handle new student lived (disconnected)
    else if (specialType === 'newstudentlived') {
      setNBconnectedusers(NBconnectedusers - 1);
      setTconnectedusers(Tconnectedusers.filter((student) => student !== who));
      updateUsers();
    }
    // For other special messages, skip normal processing
    return;
  }

  // Trigger alert for new messages from other users
  triggerNewMessageAlert(who);

  // Track new questions for conference
  setNbnewQ(nbnewQ + 1);

  // Get current language
  // Conference : le tchat suit la langue du conferencier (select "Langue du conferencier")
  // Auditeur : langue choisie au pop-up (builtin_voice), sinon builtin_language
  let currentLang = '';
  const currentUser = globalThis.OMIST_USER || {};
  const classroomLanguageSelect = document.getElementById('classroomlanguage');
  if (currentUser.isIntervenant && classroomLanguageSelect && classroomLanguageSelect.value) {
    currentLang = classroomLanguageSelect.value;
  } else {
    const bv = getBuiltinVoice();
    const bl = getBuiltinLanguage();
    if (typeof bv !== 'undefined' && bv && bv.lang) {
      currentLang = bv.lang;
    } else if (typeof bl !== 'undefined' && bl) {
      currentLang = bl;
    }
  }

  // Only translate if we have a language selected and it's different from the message language
  if (currentLang && lang && typeof currentLang === 'string' && typeof lang === 'string'
        && currentLang.substr(0, 2) !== lang.substr(0, 2)) {

    if (typeof TranslateQ === 'function') {
      TranslateQ(text, currentLang.substr(0, 2), lang.substr(0, 2), who, order, senderRole);
    }
  } else {
    const hasIntervenantIdentity = senderRole === 'intervenant' || (!senderRole && isIntervenantSender(who));
    EcrireCR(who, text, order, hasIntervenantIdentity);
  }
}

/**
 * @summary Send click handler - called when user clicks send button
 *
 * @returns {void}
 */
export function sendclick() {
  // Prevent multiple rapid clicks
  if (isSendInProgress()) {
    return;
  }

  let questionElement = _('question');

  if (questionElement) {
    const tosend = questionElement.value.replaceAll('\n', '').trim();
    if (tosend !== '') {
      // Set flag to prevent multiple submissions
      setSendInProgress(true);

      // Import dynamique : main.js importe deja handlers.js (evite le cycle)
      import('./main.js').then(({ saveTextQ }) => {
        if (typeof saveTextQ === 'function') {
          saveTextQ(tosend);
        }
      });

      // Disable send button if it exists
      const sendButton = document.getElementById('questionsend');
      if (sendButton) {
        sendButton.disabled = true;
      }

      questionElement.value = '';

      // Re-enable after a delay (in case of error, timeout will reset it)
      safeSetTimeout(() => {
        setSendInProgress(false);
        if (sendButton) {
          sendButton.disabled = false;
        }
      }, 2000); // 2 second cooldown
    }
  }
}

// Expose for inline event handlers - removed, use ES module imports instead
