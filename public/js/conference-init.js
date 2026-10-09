/** @module conference-init */
import { loadVoicesWhenAvailable, applyConfig, initConferenceEvents, Init } from './lib/conference/index.js';
import { startChatStream } from './lib/chat/sse.js';
import { defaultMessageHandler } from './lib/chat/handlers.js';
import { safeSetInterval, safeSetTimeout } from './lib/utils/cleanup.js';
import { addCsrfToken } from './lib/network/utils.js';
import { getBasePath } from './lib/state/shared.js';

// Initialiser après chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
  // Maintenir la session PHP active pendant toute la conference (toutes les 10 minutes)
  safeSetInterval(() => {
    fetch('/keepalive', { method: 'GET', credentials: 'same-origin' }).catch(() => {});
  }, 600000);

  // Initialiser les event listeners
  initConferenceEvents();

  // Initialiser les voix et autres configurations
  if (typeof loadVoicesWhenAvailable === 'function') {
    safeSetTimeout(loadVoicesWhenAvailable, 100);
  }

  // Initialiser la configuration (inclut le gestionnaire Escape)
  if (typeof Init === 'function') {
    Init();
  }

  // Appliquer la configuration
  if (typeof applyConfig === 'function') {
    safeSetTimeout(applyConfig, 200);
  }

  // Démarrer le flux SSE du tchat (remplace le polling, désactivé)
  const classroomIdEl = document.getElementById('classroomid');
  if (classroomIdEl && classroomIdEl.value) {
    startChatStream(classroomIdEl.value, defaultMessageHandler);
  }

  // Signaler la présence de la page conference : heartbeat périodique
  // (RoomPresence côté serveur) + beacon de fermeture immédiat.
  if (classroomIdEl && classroomIdEl.value) {
    const roomId = classroomIdEl.value;
    const buildPresenceData = () => {
      const f = new FormData();
      f.append('ROOMID', roomId);
      f.append('room_token', window.ROOM_TOKEN);
      addCsrfToken(f);
      return f;
    };
    const sendHeartbeat = () => {
      fetch(getBasePath() + '/conference/heartbeat', {
        method: 'POST',
        body: buildPresenceData(),
        credentials: 'same-origin'
      }).catch(() => {});
    };
    sendHeartbeat();
    safeSetInterval(sendHeartbeat, 20000);
    window.addEventListener('pagehide', () => {
      if (navigator.sendBeacon) {
        navigator.sendBeacon(getBasePath() + '/conference/close', buildPresenceData());
      }
    });
  }
});
