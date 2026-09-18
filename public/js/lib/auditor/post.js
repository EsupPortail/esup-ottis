/**
 * @module lib/auditor/post
 * Auditor post-processing functions and DOM event handlers
 */
import { makeDraggable } from '../utils/draggable.js';
import { safeSetTimeout } from '../utils/cleanup.js';
import { startAuditorPolling } from './main.js';
import { readText, readTextSubTranscript } from './network.js';
import { readTextQCommon } from '../chat/network.js';
import { EcrireCR, ClearCR } from '../chat/display.js';
import { defaultMessageHandler } from '../chat/handlers.js';
import { setLanguageSelected, setFirstClick, setFirstTime, setDiscoursRate, setInputNumberQ, getInputNumberQ } from '../state/shared.js';
import { setLanguageOutput } from '../language/management.js';
import { setBuiltinVoice, setBuiltinLanguage, getBuiltinVoice } from '../state/auditor.js';
import { rebuildOutputForLanguage } from './translation.js';
import { _ } from '../dom/selector.js';

// ============================================================================
// Fonction locale readTextQ pour auditor (remplace l'appel window.readTextQ)
// ============================================================================
function readTextQ() {
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
    const height = parseInt(e.style.height.replace('px', '')) || 0;
    e.style.top = `${innerHeight - height - 100 - 10}px`;
  }
  e = document.getElementById('VOBanner');
  if (e && e.style.height) {
    const height = parseInt(e.style.height.replace('px', '')) || 0;
    e.style.top = `${innerHeight - height - 100 - 10}px`;
  }
}

/**
 * Limit title element positions to window bounds
 */
export function MinTitlePos() {
  let e = document.getElementById('titre');
  if (e) {
    let postop = parseInt(e.style.top.replace('px', '')) || 0;
    if (postop > innerHeight - 50) {
      e.style.top = `${innerHeight - 50}px`;
    }
  }
  e = document.getElementById('VOBanner');
  if (e) {
    let postop = parseInt(e.style.top.replace('px', '')) || 0;
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
    setDiscoursRate((parseInt(sliderSpeed.value) || 100) / 100);
  }
}

// ============================================================================
// Initialization
// ============================================================================

/**
 * Initialize auditor post features
 */
export function initAuditorPost() {
  // Set beforeunload handler
  onbeforeunload = function(event) {
    event.returnValue = 'Quit';
  };

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

  // Dialog-choose form handler
  const dialogChoose = document.getElementById('dialog-choose');
  const dialogChooseForm = dialogChoose?.querySelector('form');
  if (dialogChooseForm) {
    dialogChooseForm.addEventListener('submit', (e) => {
      e.preventDefault();
      setFirstTime(false);
      setFirstClick(false);
      document.getElementById('VoiceOutput').value = document.getElementById('VoiceOutputchoose').value;
      // Set language output and rebuild for auditor
      const voiceOutputSelect = document.getElementById('VoiceOutput');
      setLanguageOutput(voiceOutputSelect.value);
      // Also update the store for consistency with translation logic
      setBuiltinVoice({ lang: voiceOutputSelect.value });
      rebuildOutputForLanguage();
      // Clear and re-fetch all messages to translate them with the new language
      safeSetTimeout(() => {
        setInputNumberQ(0);
        ClearCR();
        readTextQ();
      }, 100);

      document.getElementById('synthesison').checked = false;
      document.getElementById('arrowlanguage').style.visibility = 'hidden';
      document.getElementById('arrowlanguage').style.display = 'none';
      document.getElementById('lilanguage').style.animation = 'none';
      setFirstClick(true);
      dialogChoose.close();
      setLanguageSelected(true);
      
      // Load all historical content from Verylast files
      if (typeof readTextSubTranscript === 'function') {
        readTextSubTranscript();
      }
      
      waitForReadTextAndStart();
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

  // Language change handler
  const classroomLanguage = document.getElementById('classroomlanguage');
  if (classroomLanguage) {
    classroomLanguage.addEventListener('change', () => {
      safeSetTimeout(() => {
        // Clear chat to allow re-translation with new language
        ClearCR();
        readTextQ();
      }, 500);
    });
  }

  // VoiceOutput change handler - retraduire quand l'utilisateur change la langue cible
  const voiceOutputSelect = document.getElementById('VoiceOutput');
  if (voiceOutputSelect) {
    voiceOutputSelect.addEventListener('change', () => {
      setLanguageOutput(voiceOutputSelect.value);
      setBuiltinVoice({ lang: voiceOutputSelect.value });
      rebuildOutputForLanguage();
      // Clear and re-fetch all messages to translate them with the new language
      safeSetTimeout(() => {
        setInputNumberQ(0);
        ClearCR();
        readTextQ();
      }, 100);
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
  for (const p in e) res += `${p}=${e[p]}\n`;
  return res;
}

export function retourErreur() {}

// ============================================================================
// Auto-initialize
// ============================================================================
initAuditorPost();
