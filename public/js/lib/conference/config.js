/**
 * @module lib/conference/config
 * Conference configuration and global state variables
 * This is the main configuration file that combines state variables and functions
 */

// Re-export state variables and functions from config-functions
// (which was originally conference-def.js and part of common-func.js)
// Import state variables and functions from state.js
// These are the single source of truth for application state
import {
  AllOutputVoices,
  TLangInput,
  builtin_voice,
  builtin_language,
  subtitle_language,
  inputnumberQ,
  setInputnumberQ,
  setBuiltinVoice,
  setBuiltinLanguage,
} from './state.js';

// Import functions from other modules that are used here
import { readTextQ } from './notebook.js';

export * from './config-functions.js';

// Also export from lib/colors to ensure ColorsForCR is available
export { ColorsForCR } from '../colors.js';

// Re-export state variables for other modules that might import from config.js
export {
  AllOutputVoices,
  TLangInput,
  builtin_voice,
  builtin_language,
  subtitle_language,
  inputnumberQ,
  setInputnumberQ,
  setBuiltinVoice,
  setBuiltinLanguage,
} from './state.js';

// URL parameters - these were in the original conference-def.js
// Parse URL parameters
export let zoomid = '';
export let uncloudlink = '';
export let uncloudhtmllink = '';
export let targettab = 0;

// Additional state variables from conference-def.js
export let inSlideTab = false;
export const speech_language = 'same';

// Speech recognition variables
export let builtin_recognition;
export let builtin_recognitionon = false;
export let builtin_externaltranscritionon = false;
export let builtin_microsofttranscritionon = false;

/**
 * Set the builtin_recognitionon flag
 * @param {boolean} value - The new value
 */
export function setBuiltinRecognitionon(value) {
  builtin_recognitionon = value;
  if (typeof window !== 'undefined') {
    window.builtin_recognitionon = value;
  }
}

/**
 * Set the builtin_externaltranscritionon flag
 * @param {boolean} value - The new value
 */
export function setBuiltinExternalTranscriptionon(value) {
  builtin_externaltranscritionon = value;
  if (typeof window !== 'undefined') {
    window.builtin_externaltranscritionon = value;
  }
}

/**
 * Set the builtin_microsofttranscritionon flag
 * @param {boolean} value - The new value
 */
export function setBuiltinMicrosoftTranscriptionon(value) {
  builtin_microsofttranscritionon = value;
  if (typeof window !== 'undefined') {
    window.builtin_microsofttranscritionon = value;
  }
}

// Initialize speech recognition
if (typeof window !== 'undefined') {
  // Fake SpeechRecognition for browsers that don't support it
  function fakeSpeechRecognition() {
    this.lang = '';
    this.onresult = function (e) {};
    this.onend = function (e) {};
    this.start = function () {};
    this.stop = function () {};
    this.abort = function () {};
    this.continuous = true;
    this.interimResults = true;
    this.enableAutomaticPunctuation = true;
    this.maxAlternatives = 1;
  }

  const SpeechRecognitionAPI = window.SpeechRecognition || window.webkitSpeechRecognition || fakeSpeechRecognition;
  builtin_recognition = new SpeechRecognitionAPI();

  if (builtin_recognition) {
    builtin_recognition.continuous = true;
    builtin_recognition.interimResults = true;
    builtin_recognition.enableAutomaticPunctuation = true;
    builtin_recognition.maxAlternatives = 1;
  }

  // Expose on window for backward compatibility
  window.builtin_recognition = builtin_recognition;
  window.builtin_recognitionon = builtin_recognitionon;
  window.builtin_externaltranscritionon = builtin_externaltranscritionon;
  window.builtin_microsofttranscritionon = builtin_microsofttranscritionon;
}

// Initialize from URL if available
if (typeof window !== 'undefined') {
  const base = new URL(window.location.toLocaleString()).searchParams;
  if (base.has('ZOOMID')) zoomid = base.get('ZOOMID');
  if (base.has('link')) uncloudlink = base.get('link');
  if (base.has('htmllink')) uncloudhtmllink = base.get('htmllink');
  if (base.has('tab')) targettab = base.get('tab');
  if (base.has('tab')) inSlideTab = true;
}

// ScreenCR is defined globally by common-func.js, use window.ScreenCR
// Note: This module is loaded after common-func.js in auditor.php, so window.ScreenCR should be available

// ============================================
// Additional state variables that were in common-func.js
// ============================================

// Initialize inputnumberQ from window if available (for backward compatibility)
if (typeof window !== 'undefined') {
  setInputnumberQ(window.inputnumberQ || 0);
}

// ============================================
// Language functions (migrated from common-func.js)
// These were not in the original config.js but need to be here
// ============================================

// We need to access TLangInput and AllOutputVoices from config-functions
// and builtin_voice, builtin_language from there too

/**
 * Set the input language
 * @param {number} i - Language index
 */
export function setLanguageInput(i) {
  // These variables are exported from config-functions.js
  if (typeof TLangInput !== 'undefined' && TLangInput[i]) {
    setBuiltinLanguage(TLangInput[i]);
  }
}

/**
 * Set the output language
 * @param {number} i - Language index
 */
export function setLanguageOutput(i) {
  if (typeof AllOutputVoices !== 'undefined' && AllOutputVoices[i]) {
    setBuiltinVoice(AllOutputVoices[i]);
  }
  const newLanguage = (builtin_voice && builtin_voice.lang) ? builtin_voice.lang : '';
  setBuiltinLanguage(newLanguage);
}

/**
 * Set the output language and retranslate the chat
 * @param {number} i - Language index
 */
export function setLanguageOutputAndRetranslate(i) {
  setLanguageOutput(i);
  // Reload and retranslate all messages with the new language
  recharger();

  // Expose on window for backward compatibility
  if (typeof window !== 'undefined') {
    window.setLanguageOutputAndRetranslate = setLanguageOutputAndRetranslate;
  }
}


