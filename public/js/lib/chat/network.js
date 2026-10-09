/**
 * Chat network functions
 * Dependencies: conference/network.js (for createFetchRequest), network/utils.js (for addCsrfToken)
 */

import { addCsrfToken } from '../network/utils.js';
import { createFetchRequest } from '../conference/network.js';
import { increasependingrequests, decreasependingrequests } from '../utils/request-counter.js';
import { getRoomToken, getBasePath, getInputNumberQ, setInputNumberQ } from '../state/shared.js';
import { EcrireCR } from './display.js';
import { ScreenCR } from './state.js';
import { nbnewQ, setNbnewQ } from '../conference/state/core.js';
import { EventNewQuestion } from '../conference/utils.js';
import { _ } from '../dom/selector.js';

/**
 * @summary Send a new question to the classroom queue
 *
 * @param {string} ch - Text of the question
 * @param {string} senderName - Name of the sender (e.g., "Intervenant", "User Red Alpaca")
 * @param {string} roomId - The ROOMID
 * @param {string} language - The language code
 * @returns {void}
 */
export function saveTextQCommon(ch, senderName, roomId, language, senderRole = 'auditor') {
  increasependingrequests();

  const f = new FormData();
  f.append('lineid', senderName);
  f.append('text', ch);
  f.append('inputlanguage', language || '');
  f.append('ROOMID', roomId || '');
  f.append('sender_role', senderRole || 'auditor');

  // Ajouter le token pour l'auditeur (multi-onglets)
  const roomToken = getRoomToken();
  if (typeof roomToken !== 'undefined') {
    f.append('room_token', roomToken);
  }

  // Ajouter le token CSRF pour la protection
  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/text/save-q', {
    onSuccess() {
      decreasependingrequests();
    },
    onError(response, context) {
      decreasependingrequests();
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('saveTextQ request failed. Context:', context);
      }
    },
    data: f,
    timeout: 10000,
    context: 'saveTextQ',
  });
  request.send();
}

/**
 * @summary Parse a raw chat queue line (who;lang;text[;role])
 * @param {string} line - Raw line from the LinkQuestions file / SSE event
 * @param {number} order - Message order (line index)
 * @returns {Object|null} Parsed message or null
 */
export function parseChatLine(line, order) {
  if (!line || typeof line.split !== 'function') return null;
  const L = line.split(';');
  if (L.length < 3) return null;
  const who = L[0] || '';
  const lang = L[1] || '';
  const roleToken = (L[L.length - 1] || '').trim().toLowerCase();
  const hasExplicitRole = ['intervenant', 'auditor'].includes(roleToken);
  const senderRole = hasExplicitRole ? roleToken : '';
  const textParts = hasExplicitRole ? L.slice(2, -1) : L.slice(2);
  const text = textParts.join(';');
  let isSpecial = false;
  let specialType = '';

  // Check for special messages
  if (text.indexOf('[newstudentarrived]') >= 0) {
    specialType = 'newstudentarrived';
    isSpecial = true;
  } else if (text.indexOf('[newstudentlived]') >= 0) {
    specialType = 'newstudentlived';
    isSpecial = true;
  } else if (text.indexOf('[extratab') >= 0) {
    specialType = 'extratab';
    isSpecial = true;
  }

  return { who, text, lang, order, isSpecial, specialType, senderRole };
}

/**
 * @summary Read messages from the classroom queue
 *
 * @param {string} roomId - The ROOMID
 * @param {function} messageHandler - Function to handle each message: messageHandler(who, text, lang, order, isSpecial)
 * @param {function} onRateLimit - Callback for rate limit errors (429)
 * @param {function} onSuccess - Callback for successful requests
 * @returns {void}
 */
export function readTextQCommon(roomId, messageHandler, onRateLimit, onSuccess) {
  if (!roomId || roomId === '') return;

  increasependingrequests();

  const f = new FormData();
  f.append('lineid', 0);
  f.append('ROOMID', roomId);

  // Ajouter le token pour l'auditeur (multi-onglets)
  const roomToken = getRoomToken();
  if (typeof roomToken !== 'undefined') {
    f.append('room_token', roomToken);
  }

  // Ajouter le token CSRF pour la protection
  addCsrfToken(f);

  // Callbacks pour le polling adaptatif
  const handleRateLimit = () => { if (typeof onRateLimit === 'function') onRateLimit(); };
  const handleSuccess = () => { if (typeof onSuccess === 'function') onSuccess(); };

  const request = createFetchRequest('POST', getBasePath() + '/text/read-q', {
    onSuccess(response) {
      decreasependingrequests();

      // Safety checks for response and responseText
      if (!response || typeof response.responseText !== 'string') {
        console.error('readTextQ: Invalid response or responseText');
        return;
      }

      const Told = response.responseText.split('\n');
      const T = [];

      // Filter out empty lines
      for (let i = 0; i < Told.length; i++) {
        if (Told[i] && Told[i].split) {
          const parts = Told[i].split(';');
          if (parts.length > 2) {
            T.push(Told[i]);
          }
        }
      }

      // Process new messages
      const startIndex = getInputNumberQ();
      
      // Update inputnumberQ immediately to prevent overlapping requests from reprocessing messages
      if (T.length > startIndex) {
        setInputNumberQ(T.length);
      }
      
      for (let i = startIndex; i < T.length; i++) {
        const parsed = parseChatLine(T[i], i);
        // Skip ROBOT messages, but pass everything else including special messages
        if (parsed && parsed.who !== 'ROBOT' && messageHandler) {
          messageHandler(
            parsed.who, parsed.text, parsed.lang, i,
            parsed.isSpecial, parsed.specialType, parsed.senderRole
          );
        }
      }

      // Trigger EventNewQuestion for conference if new messages were processed
      if (nbnewQ > 0 && typeof EventNewQuestion === 'function') {
        EventNewQuestion();
      }

      // Reset nbnewQ for next polling cycle
      setNbnewQ(0);

      // Appeler le callback de succes pour le polling adaptatif
      handleSuccess();
    },
    onError(response, context) {
      decreasependingrequests();
      
      // Appeler le callback de rate limit pour le polling adaptatif
      if (response.status === 429) {
        handleRateLimit();
      }
      
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('readTextQ request failed. Context:', context);
      }
    },
    data: f,
    timeout: 10000,
    context: 'readTextQ',
  });
  request.send();
}

/**
 * @summary Translate a text and display it using EcrireCR
 *
 * @param {string} ch - Text to translate
 * @param {string} targetLang - Target language
 * @param {string} sourceLang - Source language
 * @param {string} name - Sender name
 * @param {number} order - Message order
 * @returns {void}
 */
export function TranslateQ(ch, targetLang, sourceLang, name, order, senderRole = '') {
  const classroomIdEl = _('classroomid');
  const roomid = classroomIdEl ? classroomIdEl.value : '';

  const f = new FormData();
  f.append('texte', ch);
  f.append('lang', targetLang);
  f.append('langinput', sourceLang);
  f.append('ROOMID', roomid);
  f.append('room_token', getRoomToken());
  if (senderRole) {
    f.append('sender_role', senderRole);
  }

  increasependingrequests();

  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/text/translate', {
    onSuccess(response) {
      decreasependingrequests();

      try {
        const parsedResponse = JSON.parse(response.responseText);
        if (!parsedResponse.error && parsedResponse.data && parsedResponse.data.translations && parsedResponse.data.translations[0]) {
          const translation = parsedResponse.data.translations[0].text;
          if (translation) {
            EcrireCR(name, translation, order, senderRole === 'intervenant');
          } else {
            // Fallback to original text
            if (ScreenCR[order] === undefined) {
              EcrireCR(name, ch, order, senderRole === 'intervenant');
            }
            console.error('Translation ERROR - No translation text found, not displaying original to avoid overwrite:', ch);
          }
        } else {
          if (ScreenCR[order] === undefined) {
            EcrireCR(name, ch, order, senderRole === 'intervenant');
          }
          console.error('Translation ERROR - Response:', parsedResponse, 'Raw:', response.responseText);
        }
      } catch (e) {
        if (ScreenCR[order] === undefined) {
          EcrireCR(name, ch, order, senderRole === 'intervenant');
        }
        console.error('Translation ERROR - Exception:', e, 'Response:', response.responseText);
      }
    },
    onError(response, context) {
      decreasependingrequests();
      // Ne pas afficher le texte original si le message existe déjà
      // (le message a peut-être déjà été affiché traduit par une autre requête)
      if (ScreenCR[order] === undefined) {
        EcrireCR(name, ch, order, senderRole === 'intervenant');
      }
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('[TRANSLATE-Q] Request failed (not displaying original text to avoid overwrite). Context:', context);
      }
    },
    data: f,
    timeout: 10000,
    context: 'TranslateQ',
  });
  request.send();
}
