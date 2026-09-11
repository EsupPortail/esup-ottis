/** @module conference-init */
import { loadVoicesWhenAvailable, applyConfig, initConferenceEvents, Init } from './lib/conference/index.js';
import { startChatPolling } from './lib/chat/main.js';
import { safeSetTimeout } from './lib/utils/cleanup.js';

// Initialiser après chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
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

  // Démarrer le polling du chat
  if (typeof startChatPolling === 'function') {
    startChatPolling();
  }
});
