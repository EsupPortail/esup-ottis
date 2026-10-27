/**
 * Chat main functions
 * Dependencies: all other chat modules
 */
import { readTextQCommon } from './network.js';
import { defaultMessageHandler } from './handlers.js';

// Compat : saveTextQ vit desormais dans send.js (module feuille, evite le
// cycle main<->handlers qui etait contourne par un import dynamique)
export { saveTextQ } from './send.js';

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
 * @summary Start the chat polling with adaptive interval
 *
 * @returns {void}
 */
export function startChatPolling() {
  // Polling DISABLED - Waiting for SSE migration to avoid PHP-FPM saturation
  console.log('Chat polling DISABLED - Waiting for SSE migration');
  /* Polling desactive en attendant la migration SSE (code conserve pour reactivation)
     Reactiver aussi : import { _ } de ../dom/selector.js,
     import { safeSetInterval } de ../utils/cleanup.js et
     { isLanguageSelected, setChatPollingStarted, getChatPollingIntervalId,
     setChatPollingIntervalId } de ../state/shared.js

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
  */
}

// Expose globally for inline event handlers - removed, use ES module imports instead
