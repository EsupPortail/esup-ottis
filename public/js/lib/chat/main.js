/**
 * Chat main functions
 * Dependencies: all other chat modules
 */
import { _ } from '../dom/selector.js';
import { readTextQCommon, saveTextQCommon, TranslateQ } from './network.js';
import { defaultMessageHandler, sendclick } from './handlers.js';
import { safeSetInterval } from '../utils/cleanup.js';
import { isLanguageSelected, setLanguageSelected, isChatPollingStarted, setChatPollingStarted, getChatPollingIntervalId, setChatPollingIntervalId } from '../state/shared.js';
import { getBuiltinVoice, getBuiltinLanguage } from '../state/auditor.js';

/**
 * @summary Read and display messages from the classroom queue
 *
 * @param {string} roomId - The ROOMID
 * @param {function} onRateLimit - Callback for rate limit errors
 * @param {function} onSuccess - Callback for successful requests
 * @returns {void}
 */
export function readTextQ(roomId, onRateLimit, onSuccess) {
  readTextQCommon(roomId, defaultMessageHandler, onRateLimit, onSuccess);
}

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
  const currentUser = globalThis.OMIST_USER || {};

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

/**
 * @summary Start the chat polling with adaptive interval
 *
 * @returns {void}
 */
export function startChatPolling() {
  // Polling DISABLED - Waiting for SSE migration to avoid PHP-FPM saturation
  console.log("Chat polling DISABLED - Waiting for SSE migration");
  return;

  const hasDialogChoose = !!document.getElementById('dialog-choose');

  // Check if we can start (language selected for Auditor) - direct check
  if (hasDialogChoose && !isLanguageSelected()) {
    return;
  }

  setChatPollingStarted(true);

  // Adaptive polling configuration
  let currentInterval = 500;
  const MIN_INTERVAL = 500;
  const MAX_INTERVAL = 5000;
  const INCREMENT = 500;
  const SUCCESS_THRESHOLD = 5;
  
  let consecutiveErrors = 0;
  let consecutiveSuccesses = 0;

  const restartPolling = () => {
    const intervalId = getChatPollingIntervalId();
    if (intervalId) {
      clearInterval(intervalId);
    }
    setChatPollingIntervalId(safeSetInterval(pollChat, currentInterval));
  };

  const handleRateLimit = () => {
    consecutiveErrors++;
    consecutiveSuccesses = 0;
    currentInterval = Math.min(currentInterval + INCREMENT, MAX_INTERVAL);
    restartPolling();
  };

  const handleSuccess = () => {
    consecutiveSuccesses++;
    consecutiveErrors = 0;
    if (consecutiveSuccesses >= SUCCESS_THRESHOLD && currentInterval > MIN_INTERVAL) {
      currentInterval = Math.max(currentInterval - INCREMENT, MIN_INTERVAL);
      consecutiveSuccesses = 0;
      restartPolling();
    }
  };

  const pollChat = () => {
    const classroomIdEl = _('classroomid');
    let roomId = '';
    
    if (classroomIdEl) {
      roomId = classroomIdEl.value;
    }

    if (roomId) {
      readTextQ(roomId, handleRateLimit, handleSuccess);
    }
  };

  setChatPollingIntervalId(safeSetInterval(pollChat, currentInterval));
}

// Expose globally for inline event handlers - removed, use ES module imports instead
