/**
 * Chat network functions
 * Dependencies: common-func.js (for createXhrRequest, addCsrfToken)
 */

// Ensure common-func.js is loaded first
if (typeof createXhrRequest === 'undefined') {
  console.error('chat/network.js requires common-func.js to be loaded first');
}

/**
 * @summary Send a new question to the classroom queue
 *
 * @param {string} ch - Text of the question
 * @param {string} senderName - Name of the sender (e.g., "Intervenant", "User Red Alpaca")
 * @param {string} roomId - The ROOMID
 * @param {string} language - The language code
 * @returns {void}
 */
export function saveTextQCommon(ch, senderName, roomId, language) {
  // Use window.increasependingrequests if available
  if (typeof window.increasependingrequests === 'function') {
    window.increasependingrequests();
  }

  const f = new FormData();
  f.append('lineid', senderName);
  f.append('text', ch);
  f.append('inputlanguage', language || '');
  f.append('ROOMID', roomId || '');

  // Ajouter le token pour l'auditeur (multi-onglets)
  if (typeof window.ROOM_TOKEN !== 'undefined') {
    f.append('room_token', window.ROOM_TOKEN);
  }

  // Ajouter le token CSRF pour la protection
  addCsrfToken(f);

  const request = createXhrRequest('POST', 'savetextQ.php', {
    onSuccess(xhr) {
      // Use window.decreasependingrequests if available
      if (typeof window.decreasependingrequests === 'function') {
        window.decreasependingrequests();
      }
    },
    data: f,
    timeout: 10000,
    context: 'saveTextQ',
  });
  request.send(f);
}

/**
 * @summary Read messages from the classroom queue
 *
 * @param {string} roomId - The ROOMID
 * @param {function} messageHandler - Function to handle each message: messageHandler(who, text, lang, order, isSpecial)
 * @returns {void}
 */
export function readTextQCommon(roomId, messageHandler) {
  if (!roomId || roomId === '') return;

  // Use window.increasependingrequests if available
  if (typeof window.increasependingrequests === 'function') {
    window.increasependingrequests();
  }

  const f = new FormData();
  f.append('lineid', 0);
  f.append('ROOMID', roomId);

  // Ajouter le token pour l'auditeur (multi-onglets)
  if (typeof window.ROOM_TOKEN !== 'undefined') {
    f.append('room_token', window.ROOM_TOKEN);
  }

  // Ajouter le token CSRF pour la protection
  addCsrfToken(f);

  const request = createXhrRequest('POST', 'readtextQ.php', {
    onSuccess(xhr) {
      // Use window.decreasependingrequests if available
      if (typeof window.decreasependingrequests === 'function') {
        window.decreasependingrequests();
      }

      // Safety checks for xhr and responseText
      if (!xhr || typeof xhr.responseText !== 'string') {
        console.error('readTextQ: Invalid xhr or responseText');
        return;
      }

      const Told = xhr.responseText.split('\n');
      const k = 0;
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
      for (let i = window.inputnumberQ; i < T.length; i++) {
        const L = T[i].split(';');
        if (L.length >= 3) {
          const who = L[0] || '';
          const lang = L[1] || '';
          const text = L[2] || '';
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

          // Process messages through handler
          // Skip ROBOT messages, but pass everything else including special messages
          if (who !== 'ROBOT') {
            if (messageHandler) {
              messageHandler(who, text, lang, i, isSpecial, specialType);
            }
          }
        }
      }

      // Trigger EventNewQuestion for conference if new messages were processed
      if (typeof nbnewQ !== 'undefined' && nbnewQ > 0 && typeof EventNewQuestion === 'function') {
        EventNewQuestion();
      }

      // Reset nbnewQ for next polling cycle
      if (typeof nbnewQ !== 'undefined') {
        nbnewQ = 0;
      }

      // Update the counter
      window.inputnumberQ = T.length;
    },
    data: f,
    timeout: 10000,
    context: 'readTextQ',
  });
  request.send(f);
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
export function TranslateQ(ch, targetLang, sourceLang, name, order) {
  const roomid = (typeof _ !== 'undefined' && window._('classroomid') ? window._('classroomid').value : (document.getElementById ? window._('classroomid').value : ''));

  const f = new FormData();
  f.append('texte', ch);
  f.append('lang', targetLang);
  f.append('langinput', sourceLang);
  f.append('ROOMID', roomid);
  f.append('room_token', window.ROOM_TOKEN);

  // Use window.increasependingrequests if available
  if (typeof window.increasependingrequests === 'function') {
    window.increasependingrequests();
  }

  addCsrfToken(f);

  const xhr = createXhrRequest('POST', 'Translate.php', {
    onSuccess(xhr) {
      if (typeof window.decreasependingrequests === 'function') {
        window.decreasependingrequests();
      }

      try {
        const response = JSON.parse(xhr.responseText);
        if (!response.error && response.data && response.data.translations && response.data.translations[0]) {
          const translation = response.data.translations[0].text;
          if (translation) {
            if (typeof EcrireCR === 'function') {
              EcrireCR(name, translation, order);
            }
          } else {
            // Fallback to original text
            if (typeof EcrireCR === 'function') {
              EcrireCR(name, '---- Translation ERROR ---- (see console)', order);
            }
            console.error('Translation ERROR - No translation text found');
          }
        } else {
          if (typeof EcrireCR === 'function') {
            EcrireCR(name, '---- Translation ERROR ---- (see console)', order);
          }
          console.error('Translation ERROR - Response:', response, 'Raw:', xhr.responseText);
        }
      } catch (e) {
        if (typeof EcrireCR === 'function') {
          EcrireCR(name, '---- Translation ERROR ---- (see console)', order);
        }
        console.error('Translation ERROR - Exception:', e, 'Response:', xhr.responseText);
      }
    },
    data: f,
    timeout: 10000,
    context: 'TranslateQ',
  });
  xhr.send(f);
}
