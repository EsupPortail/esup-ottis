/**
 * @module lib/compat/global
 * Global compatibility module - Replaces common-func.js and conference-legacy.js
 * 
 * This module imports all modern ES modules and exposes their functions on the window object
 * for backward compatibility with legacy code that expects global functions.
 * 
 * Load this file as a module: <script type="module" src="/js/lib/compat/global.js"></script>
 * It should be loaded before any legacy scripts that depend on global functions.
 */

// ============================================================================
// DOM Utilities
// ============================================================================
import { _, query, queryAll } from '../conference/dom-helpers.js';
import { get, css, attr, val, position, on, off, ready } from '../utils/dom.js';
import { hide, show } from '../dom/visibility.js';
import { ChangeColor, ChangeOpacity } from '../dom/styles.js';
import { resizeWindow } from '../dom/layout.js';

// ============================================================================
// Browser & Error Utilities
// ============================================================================
import { showError, whichBrowser, escapeHtml } from '../browser.js';

// ============================================================================
// Color Management
// ============================================================================
import { ColorsForCR, rgb, getUserColor } from '../colors.js';

// ============================================================================
// Cleanup & Timers
// ============================================================================
import { 
  registerTimer, 
  registerCleanup, 
  registerRequest,
  clearAllTimers, 
  abortAllRequests, 
  executeCleanupCallbacks,
  cleanupAll,
  safeSetTimeout,
  safeSetInterval
} from '../utils/cleanup.js';

// ============================================================================
// Network Utilities
// ============================================================================
import { 
  addCsrfToken,
  handleXhrError,
  createXhrRequest,
  createFetchRequest,
  AbortTimeout,
  fetchWithErrorHandling
} from '../xhr.js';

// ============================================================================
// Download Utilities
// ============================================================================
import { DownloadSpeechLesson } from '../download.js';

// ============================================================================
// Chat Module
// ============================================================================
import * as chatMain from '../chat/main.js';
import * as chatAlert from '../chat/alert.js';
import * as chatDisplay from '../chat/display.js';
import * as chatHandlers from '../chat/handlers.js';
import * as chatNetwork from '../chat/network.js';
import { ScreenCR, ToSay, ToTranslate, currentdiscours, gotolive } from '../chat/state.js';
import { ClearCR, RefreshCR, recharger } from '../chat/utils.js';

// ============================================================================
// Conference Modules
// ============================================================================
import * as conference from '../conference/index.js';
import * as state from '../conference/state.js';
import * as domHelpers from '../conference/dom-helpers.js';
import * as network from '../conference/network.js';
import * as compat from '../conference/compat.js';
import * as utils from '../conference/utils.js';
import * as events from '../conference/events.js';
import * as config from '../conference/config.js';
import * as speech from '../conference/speech.js';
import * as translation from '../conference/translation.js';
import * as notebook from '../conference/notebook.js';
import * as voices from '../conference/voices.js';
import * as pdfViewer from '../conference/pdf-viewer.js';
import * as navigation from '../conference/navigation.js';

// ============================================================================
// Lib Module
// ============================================================================
import * as lib from '../index.js';

// ============================================================================
// Auditor Modules (needed for CompleteSpeech, Parle, etc.)
// ============================================================================
import { CompleteSpeech, Parle, ParleThen } from '../auditor/speech.js';

// ============================================================================
// Global State Variables
// ============================================================================

// ============================================================================
// Global State Variables from auditor-def.js
// These are needed by legacy code for speech processing and must be available on window
// ============================================================================
const ToTranslate2 = [];
const AllPhrases = [];
let completespeechactive = false;
let currentorder_readtext = 0;
let maxorder_readtext = -1;
let inputnumber = -1;
let builtin_discoursrate = 1;

// ============================================================================

// ============================================================================
// Function to compute the number of questions in the classroom queue
// ============================================================================
function TailleCR() {
  const classroomEl = _('classroom');
  if (!classroomEl) return 0;
  return classroomEl.innerHTML.split('<br/>').length;
}

// ============================================================================
// Set language input function
// ============================================================================
function setLanguageInput(i) {
  if (typeof state.TLangInput !== 'undefined' && state.TLangInput && state.TLangInput[i]) {
    window.builtin_language = state.TLangInput[i];
  } else {
    window.builtin_language = '';
  }
}

// ============================================================================
// Set language output function
// ============================================================================
function setLanguageOutput(i) {
  if (typeof state.AllOutputVoices !== 'undefined' && state.AllOutputVoices && state.AllOutputVoices[i]) {
    window.builtin_voice = state.AllOutputVoices[i];
    window.builtin_language = (window.builtin_voice && window.builtin_voice.lang) ? window.builtin_voice.lang : '';
  } else {
    window.builtin_voice = null;
    window.builtin_language = '';
  }
}

// ============================================================================
// Set the output language and retranslate the chat
// ============================================================================
function setLanguageOutputAndRetranslate(i) {
  setLanguageOutput(i);
  if (typeof recharger === 'function') {
    recharger();
  }
}

// ============================================================================
// Array of all modules to expose on window
// ============================================================================
const modulesToExpose = [
  // DOM utilities
  { _: _, get, query, queryAll, on, off, ready, css, attr, val, position, hide, show },
  
  // Browser utilities
  { showError, whichBrowser, escapeHtml },
  
  // Color utilities
  { ColorsForCR, rgb, getUserColor },
  
  // Cleanup utilities
  { 
    registerTimer, 
    registerCleanup, 
    registerRequest,
    clearAllTimers, 
    abortAllRequests, 
    executeCleanupCallbacks,
    cleanupAll,
    safeSetTimeout,
    safeSetInterval
  },
  
  // Network utilities
  { 
    addCsrfToken,
    handleXhrError,
    createXhrRequest,
    createFetchRequest,
    AbortTimeout,
    fetchWithErrorHandling
  },
  
  // DOM utilities from lib/dom/
  { hide, show, resizeWindow, ChangeColor, ChangeOpacity },
  
  // Download utilities
  { DownloadSpeechLesson },
  
  // Chat modules
  chatMain,
  chatAlert,
  chatDisplay,
  chatHandlers,
  chatNetwork,
  // Chat utilities from lib/chat/
  { ScreenCR, ToSay, ToTranslate, currentdiscours, gotolive },
  { ClearCR, RefreshCR, recharger },
  
  // Conference modules
  conference,
  state,
  domHelpers,
  network,
  compat,
  utils,
  events,
  config,
  speech,
  translation,
  notebook,
  voices,
  pdfViewer,
  navigation,
  
  // Lib module
  lib
];

// ============================================================================
// Expose all functions on window for backward compatibility
// ============================================================================
if (typeof window !== 'undefined') {
  // First, expose all modules
  modulesToExpose.forEach((module) => {
    Object.entries(module).forEach(([key, value]) => {
      // Do not overwrite existing window properties
      if (!(key in window)) {
        window[key] = value;
      }
    });
  });
  
  // Expose global functions and variables
  window.ScreenCR = ScreenCR;
  window.ColorsForCR = ColorsForCR;
  window.rgb = rgb;
  window.escapeHtml = escapeHtml;
  window.getUserColor = getUserColor;
  window.hide = hide;
  window.show = show;
  window.resizeWindow = resizeWindow;
  window.ChangeColor = ChangeColor;
  window.ChangeOpacity = ChangeOpacity;
  window.TailleCR = TailleCR;
  window.setLanguageInput = setLanguageInput;
  window.setLanguageOutput = setLanguageOutput;
  window.setLanguageOutputAndRetranslate = setLanguageOutputAndRetranslate;
  
  // Expose Micro function from conference module
  if (typeof conference.Micro === 'function') {
    window.Micro = conference.Micro;
  }
  
  // Expose CompleteSpeech and Parle functions from auditor module
  if (typeof CompleteSpeech === 'function') {
    window.CompleteSpeech = CompleteSpeech;
  }
  
  if (typeof Parle === 'function') {
    window.Parle = Parle;
  }
  
  if (typeof ParleThen === 'function') {
    window.ParleThen = ParleThen;
  }
  
  // Global state variables from auditor-def.js
  window.ToSay = ToSay;
  window.ToTranslate = ToTranslate;
  window.ToTranslate2 = ToTranslate2;
  window.AllPhrases = AllPhrases;
  window.currentdiscours = currentdiscours;
  window.gotolive = gotolive;
  window.completespeechactive = completespeechactive;
  window.currentorder_readtext = currentorder_readtext;
  window.maxorder_readtext = maxorder_readtext;
  window.inputnumber = inputnumber;
  window.builtin_discoursrate = builtin_discoursrate;
  
  window.addCsrfToken = addCsrfToken;
  window.createXhrRequest = createXhrRequest;
  window.createFetchRequest = createFetchRequest;
  window.AbortTimeout = AbortTimeout;
  window.handleXhrError = handleXhrError;
  window.registerTimer = registerTimer;
  window.registerCleanup = registerCleanup;
  window.registerRequest = registerRequest;
  window.clearAllTimers = clearAllTimers;
  window.abortAllRequests = abortAllRequests;
  window.executeCleanupCallbacks = executeCleanupCallbacks;
  window.cleanupAll = cleanupAll;
  window.safeSetTimeout = safeSetTimeout;
  window.safeSetInterval = safeSetInterval;
  window.DownloadSpeechLesson = DownloadSpeechLesson;
  window.showError = showError;
  window.whichBrowser = whichBrowser;
  window._ = _;
  
  // Initialize cleanup on page unload
  window.addEventListener('beforeunload', cleanupAll);

}

// ============================================================================
// Call initialization functions after DOM is loaded
// ============================================================================
document.addEventListener('DOMContentLoaded', () => {
  // Initialize event listeners
  // NOTE: initConferenceEvents est appelée dans conference-init.js pour éviter les doublons
  // if (typeof events.initConferenceEvents === 'function') {
  //   events.initConferenceEvents();
  // }

  if (typeof Init === 'function') {
    Init();
  }
  
  // Start chat polling for conference
  if (typeof startChatPolling === 'function') {
    startChatPolling();
  }
  
  // Attach sendclick handler to chat elements for conference and auditor pages
  if (typeof sendclick === 'function') {
    const questionEl = document.getElementById('question');
    const sendBtn = document.getElementById('questionsend');

    if (questionEl) {
      questionEl.addEventListener('keyup', (event) => {
        if (event && (event.key === 'Enter' || event.keyCode === 13)) {
          sendclick();
        }
      });
    }

    if (sendBtn) {
      sendBtn.addEventListener('click', sendclick);
    }
  }
  
  // Attach language change handlers for auditor page
  if (typeof setLanguageInput === 'function') {
    const classroomLangEl = document.getElementById('classroomlanguage');
    if (classroomLangEl) {
      classroomLangEl.addEventListener('change', () => {
        setLanguageInput(classroomLangEl.value);
      });
    }
  }
  
  if (typeof setLanguageOutputAndRetranslate === 'function') {
    const voiceOutputEl = document.getElementById('VoiceOutput');
    if (voiceOutputEl) {
      voiceOutputEl.addEventListener('change', () => {
        setLanguageOutputAndRetranslate(voiceOutputEl.value);
      });
    }
  }
  
  if (typeof InitVoices === 'function') {
    InitVoices();
  }
  
  if (typeof applyConfig === 'function') {
    setTimeout(applyConfig, 100);
  }
});