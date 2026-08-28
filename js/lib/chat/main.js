/**
 * Chat main functions
 * Dependencies: all other chat modules
 */
import { readTextQCommon, saveTextQCommon, TranslateQ } from './network.js';
import { defaultMessageHandler, sendclick } from './handlers.js';

/**
 * @summary Read and display messages from the classroom queue
 *
 * @param {string} roomId - The ROOMID
 * @returns {void}
 */
export function readTextQ(roomId) {
  readTextQCommon(roomId, defaultMessageHandler);
}

/**
 * @summary Save a new question to the classroom queue with auto-detection of sender
 *
 * @param {string} ch - Text of the question
 * @returns {void}
 */
export function saveTextQ(ch) {
  // Determine sender name based on context
  let senderName = 'User Unknown';
  let roomId = '';
  let language = '';

  // Try to get from different possible contexts
  if (typeof _ !== 'undefined') {
    // auditeur.php, conference.php context
    if (window._('AuditorName') && window._('AuditorName').value) {
      senderName = window._('AuditorName').value;
    }
    if (window._('classroomid') && window._('classroomid').value) {
      roomId = window._('classroomid').value;
    }
    if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
      language = builtin_voice.lang;
    } else if (typeof builtin_language !== 'undefined' && builtin_language) {
      language = builtin_language;
    }
  } else if (document.getElementById) {
    // chat.php, Student.php context
    if (window._('AuditorName') && window._('AuditorName').value) {
      senderName = window._('AuditorName').value;
    } else if (window._('AuditorName')) {
      senderName = window._('AuditorName').value;
    }
    if (window._('classroomid') && window._('classroomid').value) {
      roomId = window._('classroomid').value;
    }
    if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
      language = builtin_voice.lang;
    }
  }

  // For conference (teacher), use Intervenant
  if (roomId && (senderName === 'User Unknown' || senderName === 'Student ')) {
    // Check if this is a conference/teacher context
    if (typeof _ !== 'undefined' && window._('classroomid')) {
      // This might be conference - use Intervenant
      senderName = 'Intervenant';
    }
  }

  saveTextQCommon(ch, senderName, roomId, language);
}

/**
 * @summary Start the chat polling
 *
 * @returns {void}
 */
export function startChatPolling() {
  // Start polling every 2000ms (reduced from 500ms to prevent 429 errors)
  setInterval(() => {
    let roomId = '';
    if (typeof _ !== 'undefined' && window._('classroomid')) {
      roomId = window._('classroomid').value;
    } else if (document.getElementById && window._('classroomid')) {
      roomId = window._('classroomid').value;
    }

    if (roomId) {
      readTextQ(roomId);
    }
  }, 2000);
}
