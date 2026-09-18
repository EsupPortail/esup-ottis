/**
 * auditor-init.js
 * Initialisation spécifique pour auditor
 * Module ES pur - zéro dépendance à window
 */

// Import des fonctions voix
import { Parle, ParleThen, CompleteSpeech } from './lib/auditor/speech.js';
import { Init } from './lib/auditor/main.js';

// Import des fonctions réseau
import { readText, readTextSub, readTextSubTranscript /* readImage DISABLED */ } from './lib/auditor/network.js';

// Import des fonctions de traduction
import { rebuildOutputForLanguage } from './lib/auditor/translation.js';

// Import des fonctions utilitaires
import { GoFS } from './lib/auditor/main.js';
import { initChatAlert } from './lib/chat/alert.js';
import { loadVoicesWhenAvailable } from './lib/conference/voices.js';
import { setLanguageOutput } from './lib/language/management.js';
import { safeSetTimeout } from './lib/utils/cleanup.js';
import { EnvoiMail } from './lib/auditor/mail.js';
import { DownloadSpeechLesson } from './lib/download.js';

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

  // Setup all event listeners
  setupEventListeners();

  // Load voices when available
  if (typeof loadVoicesWhenAvailable === 'function') {
    safeSetTimeout(loadVoicesWhenAvailable, 100);
  }
}

/**
 * Setup all event listeners for the auditor interface
 * Replaces all inline onclick handlers
 */
function setupEventListeners() {
  // Repeat button - plays the title text
  const repeatBtn = document.getElementById('repeat');
  if (repeatBtn) {
    repeatBtn.addEventListener('click', () => {
      const titreEl = document.getElementById('titre');
      if (titreEl && typeof Parle === 'function') {
        Parle(titreEl.innerHTML);
      }
    });
  }

  // Fullscreen button
  const fsBtn = document.getElementById('FS');
  if (fsBtn) {
    fsBtn.addEventListener('click', () => {
      GoFS();
    });
  }

  // Synthesis checkbox - hides UI arrows and animations
  const synthCheck = document.getElementById('synthesison');
  if (synthCheck) {
    synthCheck.addEventListener('change', () => {
      const arrowLanguage = document.getElementById('arrowlanguage');
      const arrowSynthesis = document.getElementById('arrowsynthesis');
      const liLanguage = document.getElementById('lilanguage');
      const liSynthese = document.getElementById('lisynthese');

      if (arrowLanguage) arrowLanguage.style.visibility = 'hidden';
      if (arrowSynthesis) arrowSynthesis.style.visibility = 'hidden';
      if (liLanguage) liLanguage.style.animation = 'none';
      if (liSynthese) liSynthese.style.animation = 'none';
    });
  }

  // Download notebook button
  const downloadNotebookBtn = document.getElementById('download-notebook-btn');
  if (downloadNotebookBtn) {
    downloadNotebookBtn.addEventListener('click', () => {
      const classroomidEl = document.getElementById('classroomid');
      const notebookEl = document.getElementById('notebook');
      if (classroomidEl && notebookEl && typeof DownloadSpeechLesson === 'function') {
        DownloadSpeechLesson(
          `lesson_${classroomidEl.value}.txt`,
          notebookEl.innerHTML
        );
      }
    });
  }

  // Download output button
  const downloadOutputBtn = document.getElementById('download-output-btn');
  if (downloadOutputBtn) {
    downloadOutputBtn.addEventListener('click', () => {
      const classroomidEl = document.getElementById('classroomid');
      const outputEl = document.getElementById('output');
      if (classroomidEl && outputEl && typeof DownloadSpeechLesson === 'function') {
        DownloadSpeechLesson(
          `translated_lesson_${classroomidEl.value}.txt`,
          outputEl.innerHTML
        );
      }
    });
  }

  // Send mail button (in dialog)
  const sendMailBtn = document.getElementById('send-mail-btn');
  if (sendMailBtn) {
    sendMailBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (typeof EnvoiMail === 'function') {
        EnvoiMail();
      }
    });
  }

  // Cancel mail button (in dialog)
  const cancelMailBtn = document.getElementById('cancel-mail-btn');
  if (cancelMailBtn) {
    cancelMailBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const dialogMail = document.getElementById('dialog-mail');
      if (dialogMail) {
        dialogMail.close();
      }
    });
  }
}
