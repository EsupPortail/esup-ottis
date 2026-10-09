/**
 * @module lib/auditor/post
 * Auditor post-processing functions and DOM event handlers
 */
import { makeDraggable } from '../utils/draggable.js';
import { safeSetTimeout } from '../utils/cleanup.js';
import { startAuditorPolling } from './main.js';
import { readText, readTextSubTranscript } from './network.js';
import { readTextQCommon } from '../chat/network.js';
import { defaultMessageHandler } from '../chat/handlers.js';
import { setLanguageSelected, setFirstClick, setFirstTime, setDiscoursRate } from '../state/shared.js';
import { setLanguageOutput } from '../language/management.js';
import { setBuiltinVoice } from '../state/auditor.js';
import { rebuildOutputForLanguage } from './translation.js';
import { updateSpeechVersion } from './seg.js';
import { retranslateChatStream } from '../chat/sse.js';
import { _ } from '../dom/selector.js';

// ============================================================================
// Fonction locale readTextQ pour auditor (remplace l'appel window.readTextQ)
// Chat desactive en attente du passage au mode SSE :
// conservee et exportee pour la reactivation ulterieure.
export function readTextQ() {
  const classroomidEl = _('classroomid');
  if (classroomidEl && classroomidEl.value) {
    readTextQCommon(classroomidEl.value, defaultMessageHandler);
  }
}

// ============================================================================
// Utility Functions - Title positioning
// ============================================================================

/**
 * Fix title element positions
 */
export function fixTitle() {
  let e = document.getElementById('titre');
  if (e && e.style.height) {
    const height = parseInt(e.style.height.replace('px', ''), 10) || 0;
    e.style.top = `${innerHeight - height - 100 - 10}px`;
  }
  e = document.getElementById('VOBanner');
  if (e && e.style.height) {
    const height = parseInt(e.style.height.replace('px', ''), 10) || 0;
    e.style.top = `${innerHeight - height - 100 - 10}px`;
  }
}

/**
 * Limit title element positions to window bounds
 */
export function MinTitlePos() {
  let e = document.getElementById('titre');
  if (e) {
    const postop = parseInt(e.style.top.replace('px', ''), 10) || 0;
    if (postop > innerHeight - 50) {
      e.style.top = `${innerHeight - 50}px`;
    }
  }
  e = document.getElementById('VOBanner');
  if (e) {
    const postop = parseInt(e.style.top.replace('px', ''), 10) || 0;
    if (postop > innerHeight - 50) {
      e.style.top = `${innerHeight - 50}px`;
    }
  }
}

// ============================================================================
// UI Toggle Functions
// ============================================================================

/**
 * Toggle floating title visibility
 */
export function toggleFloatingTitle() {
  const titre = document.getElementById('titre');
  const button = document.getElementById('toggle-floating-title');
  if (titre) {
    if (titre.style.display === 'none') {
      titre.style.display = 'block';
      if (button) button.textContent = 'Hide Floating Title';
    } else {
      titre.style.display = 'none';
      if (button) button.textContent = 'Show Floating Title';
    }
  }
}

/**
 * Open language selection dialog
 */
export function openLanguageDialog() {
  const dialogChoose = document.getElementById('dialog-choose');
  if (dialogChoose) dialogChoose.showModal();
}

// Expose for backward compatibility
if (typeof window !== 'undefined') {
  window.openLanguageDialog = openLanguageDialog;
}

/**
 * Refresh speech rate from slider
 */
export function refreshSpeed() {
  const sliderSpeed = document.getElementById('slider_speed');
  if (sliderSpeed) {
    setDiscoursRate((parseInt(sliderSpeed.value, 10) || 100) / 100);
  }
}

// ============================================================================
// Dialog-choose : application de la langue choisie
// ============================================================================

/**
 * @summary Apply the language chosen in dialog-choose and close the dialog
 * @param {HTMLDialogElement} dialogChoose - Le pop-up de choix de langue
 * @param {HTMLSelectElement} dialogChooseSelect - Le select #VoiceOutputchoose
 */
function applyChosenLanguage(dialogChoose, dialogChooseSelect) {
  setFirstTime(false);
  setFirstClick(false);
  const voiceOutputSelect = document.getElementById('VoiceOutput');
  if (voiceOutputSelect) voiceOutputSelect.value = dialogChooseSelect.value;
  if (voiceOutputSelect && voiceOutputSelect.value) {
    setLanguageOutput(voiceOutputSelect.value);
    // Also update the store for consistency with translation logic
    setBuiltinVoice({ lang: voiceOutputSelect.value });
    // RGAA 8.4 : langue du contenu affiche (sous-titres et discours traduit)
    const fixedTitle = document.getElementById('fixed-title');
    if (fixedTitle) fixedTitle.setAttribute('lang', voiceOutputSelect.value);
    const output = document.getElementById('output');
    if (output) output.setAttribute('lang', voiceOutputSelect.value);
  }
  updateSpeechVersion();
  rebuildOutputForLanguage();
  // Chat desactive en attente du passage au mode SSE :
  // pas de rechargement de la file de messages au changement de langue.

  const synthCheck = document.getElementById('synthesison');
  if (synthCheck) synthCheck.checked = false;
  setFirstClick(true);
  dialogChoose.close();
  setLanguageSelected(true);

  // Load all historical content from Verylast files
  if (typeof readTextSubTranscript === 'function') {
    readTextSubTranscript();
  }

  waitForReadTextAndStart();
}

// ============================================================================
// Initialization
// ============================================================================

/**
 * Initialize auditor post features
 */
export function initAuditorPost() {
  // Set beforeunload handler
  window.addEventListener('beforeunload', (event) => {
    const evt = event;
    evt.preventDefault();
    evt.returnValue = 'Quit';
  });

  // Initialize on DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePost);
  } else {
    initializePost();
  }
}

function initializePost() {
  fixTitle();

  // Make elements draggable
  const titre = document.getElementById('titre');
  const voBanner = document.getElementById('VOBanner');
  if (titre) {
    makeDraggable(titre);
    // Hide floating title by default
    titre.style.display = 'none';
  }
  if (voBanner) {
    makeDraggable(voBanner);
  }

  // Initialize slider
  const sliderSpeed = document.getElementById('slider_speed');
  if (sliderSpeed) {
    sliderSpeed.value = 100;
    sliderSpeed.addEventListener('input', refreshSpeed);
    sliderSpeed.addEventListener('change', refreshSpeed);
  }
  refreshSpeed();

  // Dialog-choose : validation par le bouton "Valider" (soumission du formulaire)
  const dialogChoose = document.getElementById('dialog-choose');
  const dialogChooseSelect = document.getElementById('VoiceOutputchoose');
  const dialogChooseForm = dialogChoose?.querySelector('form');
  if (dialogChoose && dialogChooseSelect && dialogChooseForm) {
    dialogChooseForm.addEventListener('submit', (e) => {
      e.preventDefault();
      applyChosenLanguage(dialogChoose, dialogChooseSelect);
    });
  }

  // Dialog-mail cancel button
  const dialogMail = document.getElementById('dialog-mail');
  const cancelBtn = dialogMail?.querySelector('button[type="reset"]');
  if (cancelBtn) {
    cancelBtn.addEventListener('click', (e) => {
      e.preventDefault();
      dialogMail.close();
    });
  }

  // VoiceOutput change handler - retraduire quand l'utilisateur change la langue cible
  const voiceOutputSelect = document.getElementById('VoiceOutput');
  if (voiceOutputSelect) {
    voiceOutputSelect.addEventListener('change', () => {
      setLanguageOutput(voiceOutputSelect.value);
      setBuiltinVoice({ lang: voiceOutputSelect.value });
      // RGAA 8.4 : langue du contenu affiche (sous-titres et discours traduit)
      const fixedTitle = document.getElementById('fixed-title');
      if (fixedTitle) fixedTitle.setAttribute('lang', voiceOutputSelect.value);
      const output = document.getElementById('output');
      if (output) output.setAttribute('lang', voiceOutputSelect.value);
      rebuildOutputForLanguage();
      // Retraduire tous les messages du tchat dans la nouvelle langue
      retranslateChatStream();
    });
  }
}

function waitForReadTextAndStart() {
  if (typeof readText === 'function' && typeof startAuditorPolling === 'function') {
    startAuditorPolling();
  } else {
    safeSetTimeout(
      waitForReadTextAndStart,
      100
    );
  }
}

// ============================================================================
// Legacy functions (kept for backward compatibility)
// ============================================================================

export function retourOK(e) {
  let res = '';
  Object.entries(e).forEach(([p, v]) => {
    res += `${p}=${v}\n`;
  });
  return res;
}

export function retourErreur() {}

// ============================================================================
// Auto-initialize
// ============================================================================
initAuditorPost();
