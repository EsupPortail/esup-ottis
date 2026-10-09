/**
 * sse.js
 * Flux Server-Sent Events du tchat : remplace le polling /text/read-q.
 * Une connexion EventSource persistante par client ; le serveur ferme le
 * flux apres 60 s et le navigateur reconnecte automatiquement avec le
 * header Last-Event-ID (aucun message perdu).
 */
import { getRoomToken, getBasePath, getInputNumberQ, setInputNumberQ } from '../state/shared.js';
import { parseChatLine } from './network.js';
import { ClearCR } from './utils.js';
import { nbnewQ, setNbnewQ } from '../conference/state/core.js';
import { EventNewQuestion } from '../conference/utils.js';

let chatSource = null;
let currentRoomId = '';
let currentHandler = null;

/**
 * @summary Start the chat SSE stream
 * @param {string} roomId - The ROOMID
 * @param {function} messageHandler - Handler for each message
 * @returns {void}
 */
export function startChatStream(roomId, messageHandler) {
  if (!roomId || roomId === '') return;
  if (chatSource) return; // une seule connexion

  currentRoomId = roomId;
  currentHandler = messageHandler;

  const params = new URLSearchParams({
    ROOMID: roomId,
    room_token: getRoomToken() || '',
    last_id: String(getInputNumberQ()),
  });

  chatSource = new EventSource(getBasePath() + '/text/chat-stream?' + params.toString());

  chatSource.onmessage = (ev) => {
    const index = Number.parseInt(ev.lastEventId, 10);
    if (Number.isNaN(index)) return;
    setInputNumberQ(index + 1);

    const parsed = parseChatLine(ev.data, index);
    if (parsed && parsed.who !== 'ROBOT' && typeof messageHandler === 'function') {
      messageHandler(
        parsed.who, parsed.text, parsed.lang, parsed.order,
        parsed.isSpecial, parsed.specialType, parsed.senderRole
      );
      // Alertes conference (badge nouveau message)
      if (nbnewQ > 0 && typeof EventNewQuestion === 'function') {
        EventNewQuestion();
      }
      setNbnewQ(0);
    }
  };

  // Le serveur ferme le flux periodiquement (cycle 60 s) :
  // reconnexion automatique par le navigateur (retry: 3000 cote serveur).
  chatSource.addEventListener('bye', () => {
    // rien a faire : reconnexion native du navigateur
  });
  chatSource.onerror = () => {
    // reconnexion automatique par le navigateur
  };
}

/**
 * @summary Stop the chat SSE stream
 * @returns {void}
 */
export function stopChatStream() {
  if (chatSource) {
    chatSource.close();
    chatSource = null;
  }
}

/**
 * @summary Relancer le flux depuis le debut (last_id=0), apres un
 * changement de langue : le serveur renvoie tout l'historique.
 * @returns {void}
 */
export function restartChatStream() {
  if (!chatSource || !currentRoomId) return;
  const handler = currentHandler;
  stopChatStream();
  setInputNumberQ(0);
  startChatStream(currentRoomId, handler);
}

/**
 * @summary Retraduire tout le tchat apres un changement de langue :
 * purger l'affichage (ScreenCR + DOM) puis relancer le flux depuis le debut.
 * @returns {void}
 */
export function retranslateChatStream() {
  if (!chatSource) return;
  ClearCR();
  restartChatStream();
}
