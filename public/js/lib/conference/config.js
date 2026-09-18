/**
 * @module lib/conference/config
 * Conference configuration and global state variables
 * This is the main configuration file that combines state variables and functions
 */

// Import state variables and functions from NEW centralized store
import {
  getAllOutputVoices as AllOutputVoices,
  getTLangInput as TLangInput
} from '../state/shared.js';

import {
  getSubtitleLanguage as subtitle_language,
  getInputNumberQ as inputnumberQ,
  setInputNumberQ as setInputnumberQ
} from '../state/conference.js';

import {
  getBuiltinVoice as builtin_voice,
  getBuiltinLanguage as builtin_language,
  setBuiltinVoice,
  setBuiltinLanguage
} from '../state/auditor.js';

import { recharger } from '../chat/utils.js';

// Note: config-functions.js is no longer re-exported here to break circular dependency
// Removed unused import: readTextQ (was imported but never used)
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
};

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
}

/**
 * Set the builtin_externaltranscritionon flag
 * @param {boolean} value - The new value
 */
export function setBuiltinExternalTranscriptionon(value) {
  builtin_externaltranscritionon = value;
}

/**
 * Set the builtin_microsofttranscritionon flag
 * @param {boolean} value - The new value
 */
export function setBuiltinMicrosoftTranscriptionon(value) {
  builtin_microsofttranscritionon = value;
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
}

// ScreenCR is defined globally by common-func.js, use window.ScreenCR
// URL parameters are now parsed in config-functions.js to break circular dependency
// Note: This module is loaded after common-func.js in auditor.php, so window.ScreenCR should be available

// ============================================
// Additional state variables that were in common-func.js
// ============================================

// Initialize inputnumberQ from store if available
// (backward compatibility handled by store initialization from window)

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
  console.log('[CONFIG] setLanguageInput appelé avec i:', i);
  // These variables are now getters from the centralized store
  const tLangInput = TLangInput();
  if (typeof tLangInput !== 'undefined' && tLangInput[i]) {
    setBuiltinLanguage(tLangInput[i]);
  }
}

/**
 * Set the output language
 * @param {number} i - Language index
 */
export function setLanguageOutput(i) {
  const allOutputVoices = AllOutputVoices();
  if (typeof allOutputVoices !== 'undefined' && allOutputVoices[i]) {
    setBuiltinVoice(allOutputVoices[i]);
  }
  // Note: We no longer update builtin_language here to avoid overwriting the input language
  // builtin_language should only be set via setLanguageInput()
}

/**
 * Set the output language and retranslate the chat
 * @param {number} i - Language index
 */
export function setLanguageOutputAndRetranslate(i) {
  setLanguageOutput(i);
  // Reload and retranslate all messages with the new language
  recharger();
}


