/**
 * @module lib/auditor/post
 * Auditor post-processing functions and DOM event handlers
 */

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
    e.style.top = `${window.innerHeight - height - 100 - 10}px`;
  }
  e = document.getElementById('VOBanner');
  if (e && e.style.height) {
    const height = parseInt(e.style.height.replace('px', '')) || 0;
    e.style.top = `${window.innerHeight - height - 100 - 10}px`;
  }
}

/**
 * Limit title element positions to window bounds
 */
export function MinTitlePos() {
  let e = document.getElementById('titre');
  if (e) {
    let postop = parseInt(e.style.top.replace('px', '')) || 0;
    if (postop > window.innerHeight - 50) {
      e.style.top = `${window.innerHeight - 50}px`;
    }
  }
  e = document.getElementById('VOBanner');
  if (e) {
    let postop = parseInt(e.style.top.replace('px', '')) || 0;
    if (postop > window.innerHeight - 50) {
      e.style.top = `${window.innerHeight - 50}px`;
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

// Expose on window for backward compatibility
if (typeof window !== 'undefined') {
  window.openLanguageDialog = openLanguageDialog;
}

/**
 * Refresh speech rate from slider
 */
export function refreshSpeed() {
  const sliderSpeed = document.getElementById('slider_speed');
  if (sliderSpeed) {
    window.builtin_discoursrate = (parseInt(sliderSpeed.value) || 100) / 100;
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
  window.onbeforeunload = function(event) {
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
    if (typeof window.makeDraggable === 'function') {
      window.makeDraggable(titre);
    }
    // Hide floating title by default
    titre.style.display = 'none';
  }
  if (voBanner && typeof window.makeDraggable === 'function') {
    window.makeDraggable(voBanner);
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
      window.firsttime = false;
      window.firstclick = false;
      document.getElementById('VoiceOutput').value = document.getElementById('VoiceOutputchoose').value;
      if (typeof window.setLanguageOutputAndRetranslate === 'function') {
        window.setLanguageOutputAndRetranslate(document.getElementById('VoiceOutput').value);
      }
      document.getElementById('synthesison').checked = false;
      document.getElementById('arrowlanguage').style.visibility = 'hidden';
      document.getElementById('arrowlanguage').style.display = 'none';
      document.getElementById('lilanguage').style.animation = 'none';
      window.firstclick = true;
      dialogChoose.close();
      window.languageSelected = true;
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
      (typeof window.safeSetTimeout === 'function' ? window.safeSetTimeout : setTimeout)(() => {
        if (typeof window.readTextQ !== 'undefined') window.readTextQ();
      }, 500);
    });
  }
}

function waitForReadTextAndStart() {
  if (typeof window.readText === 'function' && typeof window.startAuditorPolling === 'function') {
    window.startAuditorPolling();
  } else {
    (typeof window.safeSetTimeout === 'function' ? window.safeSetTimeout : setTimeout)(
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
