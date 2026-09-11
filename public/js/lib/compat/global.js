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

// ScreenCR - Global array to store chat messages (from common-func.js)
const ScreenCR = [];

// ============================================================================
// Global State Variables from auditor-def.js
// These are needed by legacy code for speech processing and must be available on window
// ============================================================================
const ToSay = [];
const ToTranslate = [];
const ToTranslate2 = [];
const AllPhrases = [];
let currentdiscours = -1;
let gotolive = false;
let completespeechactive = false;
let currentorder_readtext = 0;
let maxorder_readtext = -1;
let inputnumber = -1;
let builtin_discoursrate = 1;

// ============================================================================
// Simple hide function
// ============================================================================
function hide(e) {
  const el = typeof e === 'string' ? _(e) : e;
  if (el) el.style.display = 'none';
}

// ============================================================================
// Simple show function
// ============================================================================
function show(e) {
  const el = typeof e === 'string' ? _(e) : e;
  if (el) el.style.display = 'block';
}

// ============================================================================
// Function to resize window elements
// ============================================================================
function resizeWindow(wname) {
  const wc = _(wname);
  if (!wc) return;
  wc.style.width = `${window.innerWidth - 20}px`;
  wc.style.height = `${window.innerHeight - 48}px`;
  wc.style.top = '48px';
  wc.style.left = '0px';
}

// ============================================================================
// Function to change the color of a HTML div
// ============================================================================
function ChangeColor(boxname, color) {
  const [bgcolor, bordercolor, textcolor] = color.split('|');
  const e = _(boxname);
  if (!e) return;
  const opacityEl = _('selectopacity');
  const opacity = opacityEl ? opacityEl.value : '0.5';
  if (textcolor) e.style.color = `rgb(${textcolor})`;
  else e.style.color = 'rgb(255,255,255)';
  e.style.backgroundColor = `rgba(${bgcolor},${opacity})`;
  e.style.boxShadow = `2px 2px 5px rgb(${bgcolor})`;
  e.style.textShadow = `2px 0 0 rgb(${bordercolor}), -2px 0 0 rgb(${bordercolor}), 0 2px 0 rgb(${bordercolor}), 0 -2px 0 rgb(${bordercolor}), 1px 1px rgb(${bordercolor}), -1px -1px 0 rgb(${bordercolor}), 1px -1px 0 rgb(${bordercolor}), -1px 1px 0 rgb(${bordercolor})`;
}

// ============================================================================
// Function to change the opacity of a HTML div
// ============================================================================
function ChangeOpacity(boxname, opacity) {
  const e = _(boxname);
  if (!e) return;
  const color = e.style.backgroundColor;
  if (!color || color === '') return;
  const [red, green, blue] = color.substring(color.indexOf('(') + 1, color.lastIndexOf(')')).split(/,\s*/);
  const bgcolor = [red, green, blue];
  e.style.backgroundColor = `rgba(${bgcolor},${opacity})`;
}

// ============================================================================
// Function to clear the classroom chat
// ============================================================================
function ClearCR() {
  const classroomEl = _('classroom');
  if (classroomEl) classroomEl.innerHTML = '';
  ScreenCR.length = 0;
}

// ============================================================================
// Function to reload the classroom queue
// ============================================================================
function recharger() {
  ClearCR();
  if (typeof setInputnumberQ !== 'undefined') {
    setInputnumberQ(0);
  } else if (typeof window !== 'undefined') {
    window.inputnumberQ = 0;
  }
  if (typeof readTextQ !== 'undefined') {
    const roomId = _('classroomid')?.value;
    if (roomId) readTextQ(roomId);
  }
}

// ============================================================================
// Function to refresh the classroom display
// ============================================================================
function RefreshCR() {
  let html = '<ul role="list" aria-live="polite" style="list-style: none; padding: 0; margin: 0;">';
  for (let i = 0; i < ScreenCR.length; i++) {
    if (ScreenCR[i] !== undefined) {
      html += typeof window.DOMPurify !== 'undefined' ? window.DOMPurify.sanitize(ScreenCR[i]) : ScreenCR[i];
    }
  }
  html += '</ul>';
  const classroomEl = _('classroom');
  if (classroomEl) {
    classroomEl.innerHTML = typeof window.DOMPurify !== 'undefined' ? window.DOMPurify.sanitize(html) : html;
  }
}

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
  if (typeof TLangInput !== 'undefined' && TLangInput && TLangInput[i]) {
    window.builtin_language = TLangInput[i];
  } else {
    window.builtin_language = '';
  }
}

// ============================================================================
// Set language output function
// ============================================================================
function setLanguageOutput(i) {
  if (typeof AllOutputVoices !== 'undefined' && AllOutputVoices && AllOutputVoices[i]) {
    window.builtin_voice = AllOutputVoices[i];
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
  
  // Download utilities
  { DownloadSpeechLesson },
  
  // Chat modules
  chatMain,
  chatAlert,
  chatDisplay,
  chatHandlers,
  chatNetwork,
  
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
  window.ClearCR = ClearCR;
  window.recharger = recharger;
  window.RefreshCR = RefreshCR;
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