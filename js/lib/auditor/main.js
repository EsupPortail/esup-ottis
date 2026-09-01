/**
 * Main initialization functions for auditor
 */

/**
 * @summary A function that is launched when the HTML page is loaded
 * Note: Centralized polling for auditor-specific functions (readText, readImage, readTextSubTranscript)
 */
// Global flag to track if polling has started
let auditorPollingStarted = false;

// Expose to window for access from auditor-post.js
if (typeof window !== 'undefined') {
  window.startAuditorPolling = startAuditorPolling;
}

// Function to start polling - should be called after language is selected
function startAuditorPolling() {
  if (auditorPollingStarted) return;
  auditorPollingStarted = true;

  // Wait for required functions to be available (they are attached by conference-legacy.js)
  function checkAndStart() {
    if (typeof window.readText === 'function' && typeof window.readImage === 'function') {
      // Start centralized polling for auditor (2000ms interval to prevent 429 errors)
      // This handles readText (main queue), readImage (image queue)
      setInterval(() => {
        if (typeof _ !== 'undefined' && _('classroomid') && _('classroomid').value !== '') {
          window.readText();
          window.readImage();
        }
      }, 2000);

      // startChatPolling is attached to window by conference-legacy.js
      // It handles chat queue polling (readTextQ)
      if (typeof window.startChatPolling === 'function') {
        window.startChatPolling();
      }

      // LaunchRemote handles image queue polling
      if (typeof window.LaunchRemote === 'function') {
        window.LaunchRemote();
      }
    } else {
      // Functions not yet available, try again in 100ms
      setTimeout(checkAndStart, 100);
    }
  }

  checkAndStart();
}

function Init() {
  // Initialize default language
  if (typeof setLanguageInput !== 'undefined') {
    setLanguageInput(0);
  }

  // Start polling immediately (will use builtin_language for display if no voice selected)
  // Language dialog will be opened by InitVoices
  if (typeof window.startAuditorPolling === 'function') {
    setTimeout(window.startAuditorPolling, 100);
  }

  // Initialize voices when available - will call LoadVoices which calls InitVoices
  // InitVoices will open the language dialog

  // Wooclap disabled - tabs commented out
  /*
    _('question').addEventListener('keyup', function (event) {
      if (event.code === 'Enter') {
          saveTextQ(_('question').value);
        event.preventDefault();
        event.stopPropagation();
      }
    },false);
    */

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
  document.onkeyup = function (event) {
    if (['AuditorName', 'dialog_MailTo', 'dialog_MailFrom', 'dialog_MailSubject', 'question'].indexOf(document.activeElement.id) < 0) {
      if (event.code === 'SemiColon' || event.code === 'KeyM') { // Show/Hide the menu
      }
      // Tab navigation removed - all sections are now displayed sequentially
      if (event.code === 'KeyL') { // go to live
        gotolive = true;
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
  };
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
