import { _ } from '../dom/selector.js';
import { ChangeColor } from '../dom/styles.js';
/**
 * Main initialization functions for auditor
 */
import { safeSetInterval, safeSetTimeout, registerRequest } from '../utils/cleanup.js';
import { isLanguageSelected, setGotolive } from '../state/shared.js';
import { readText } from './network.js';
import { startChatPolling } from '../chat/main.js';
import { setupChatInput } from '../chat/input.js';
import { CompleteSpeech, Parle } from './speech.js';
import { setLanguageInput } from '../language/management.js';
import { setBuiltinLanguage } from '../state/auditor.js';

/**
 * @summary A function that is launched when the HTML page is loaded
 * Note: Centralized polling for auditor-specific functions (readText, readImage, readTextSubTranscript)
 */
// Global flag to track if polling has started
let auditorPollingStarted = false;

// Function to start polling - should be called after language is selected
function startAuditorPolling() {
  // Check if we can start (language selected for Auditor) - direct check
  const hasDialogChoose = !!document.getElementById('dialog-choose');

  if (hasDialogChoose && !isLanguageSelected()) {
    return;
  }

  if (auditorPollingStarted) return;
  auditorPollingStarted = true;

  // Wait for required functions to be available
  function checkAndStart() {
    if (typeof readText === 'function' /* && typeof readImage === 'function' */) {
      // Start centralized polling for auditor (2000ms interval to prevent 429 errors)
      // This handles readText (main queue) /* , readImage (image queue) DISABLED */
      safeSetInterval(() => {
        if (typeof _ !== 'undefined' && _('classroomid') && _('classroomid').value !== '') {
          readText();
          // readImage(); // DISABLED - Obsolete image sharing
        }
      }, 2000);

      // startChatPolling handles chat queue polling (readTextQ)
      if (typeof startChatPolling === 'function') {
        startChatPolling();
      }
    } else {
      // Functions not yet available, try again in 100ms
      safeSetTimeout(checkAndStart, 100);
    }
  }

  checkAndStart();
}

function Init() {
  // Initialize default language to French ('fr')
  if (typeof setLanguageInput !== 'undefined') {
    const defaultLang = 'fr'; // Default to French
    setLanguageInput(defaultLang);
    // Also update the store for consistency with translation logic
    if (typeof setBuiltinLanguage !== 'undefined') {
      setBuiltinLanguage(defaultLang);
    }
  }

  // Polling starts after language selection via auditor-post.js dialog handler

  // Initialize voices when available - will call LoadVoices which calls InitVoices
  // InitVoices will open the language dialog

  // Setup chat input Enter key handler
  setupChatInput();

  /*
    setTimeout(function() {
        let wooclapEl = _('wooclap');
        if (wooclapEl) {
            wooclapEl.src = 'https://app.wooclap.com/';
        }
    }, 1000);
    */
  const colorTitreEl = _('ColorTitre');
  ChangeColor('titre', colorTitreEl ? colorTitreEl.value : '0,0,255|0,0,255');
  ChangeColor('fixed-title', colorTitreEl ? colorTitreEl.value : '0,0,255|0,0,255');

  // Keyboard navigation
  document.addEventListener('keyup', function (event) {
    if (['AuditorName', 'dialog_MailTo', 'dialog_MailFrom', 'dialog_MailSubject', 'question'].indexOf(document.activeElement.id) < 0) {
      // Tab navigation removed - all sections are now displayed sequentially
      if (event.code === 'KeyL') { // go to live
        setGotolive(true);
        CompleteSpeech();
      }
      if (event.code === 'KeyF') { // go fullscreen
        GoFS();
      }
      if (event.code === 'KeyR') { // repeat
        Parle(_('titre').innerHTML);
      }
      if (event.code === 'ArrowUp') { // Move the subtitles UP
        _('titre').style.top = `${_('titre').style.top.replace('px', '') * 1 - 5}px`;
      }
      if (event.code === 'ArrowDown') { // Move the subtitles Down
        _('titre').style.top = `${_('titre').style.top.replace('px', '') * 1 + 5}px`;
      }
    }
  });
}

/**
 * @summary Switch the fullscreen mode on and off
 */
function GoFS() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
}

export { Init, startAuditorPolling, GoFS };
