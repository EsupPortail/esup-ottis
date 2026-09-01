/**
 * auditor-init.js
 * Initialisation spécifique pour auditor.php
 * Doit être chargé comme module ES après conference-legacy.js
 */

// Import des fonctions voix (nécessaires pour les appels onclick dans le HTML)
import { Parle, ParleThen, CompleteSpeech } from './lib/auditor/speech.js';

// Import des fonctions réseau (nécessaires pour le polling et conference-legacy.js)
import { readText, readTextSub, readTextSubTranscript, readImage } from './lib/auditor/network.js';

import { initChatAlert } from './lib/chat/alert.js';
import { loadVoicesWhenAvailable, setLanguageOutput } from './lib/conference/index.js';
import { rebuildOutputForLanguage } from './lib/auditor/translation.js';

// Get user name from DOM
const userName = document.getElementById('AuditorName') ? document.getElementById('AuditorName').value : window.randomname;

// Initialize chat alert
if (typeof initChatAlert === 'function') {
  initChatAlert(userName);
}

// Wait for DOM to be fully loaded
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    initializeAuditor();
  });
} else {
  initializeAuditor();
}

function initializeAuditor() {
  // Initialize the application
  if (typeof Init === 'function') {
    Init();
  }

  // ===== FIX: Attacher les fonctions voix et réseau à window =====
  // Cela permet aux appels onclick="Parle(...)" et onclick="CompleteSpeech()"
  // dans auditor.php et views/auditor/index.php de fonctionner
  window.Parle = Parle;
  window.ParleThen = ParleThen;
  window.CompleteSpeech = CompleteSpeech;

  // Attacher les fonctions réseau pour le polling (utilisées par main.js)
  window.readText = readText;
  window.readTextSub = readTextSub;
  window.readTextSubTranscript = readTextSubTranscript;
  window.readImage = readImage;

  // ===== NOUVEAU: Surcharger setLanguageOutputAndRetranslate pour l'auditor =====
  // Utiliser rebuildOutputForLanguage au lieu de recharger() pour économiser les ressources
  if (typeof rebuildOutputForLanguage === 'function') {
    window.setLanguageOutputAndRetranslate = function(i) {
      setLanguageOutput(i);
      rebuildOutputForLanguage();
    };
  }

  // Load voices when available
  if (typeof loadVoicesWhenAvailable === 'function') {
    setTimeout(loadVoicesWhenAvailable, 100);
  }
}
