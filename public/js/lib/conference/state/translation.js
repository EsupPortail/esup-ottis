/**
 * conference/state/translation.js
 * Module pour gérer l'état de traduction et du discours
 */

// =============================================================================
// État de la traduction étudiante
// =============================================================================

/** @type {string} */
// =============================================================================
// Langue des sous-titres (désormais gérée par le store)
// =============================================================================

// =============================================================================
// Voix disponibles
// =============================================================================

import { AllOutputVoices, TLangInput } from '../../language/state.js';

export let lastPartialTranslation = '';

/** @type {Array} */
export const StudentToTranslate2 = [];

// =============================================================================
// État du discours étudiant
// =============================================================================

/** @type {number} */
export let Studentcurrentdiscours = 0;

/** @type {Object} */
export const StudentToSay = {};

/** @type {boolean} */
export let Studentgotolive = false;
export { AllOutputVoices, TLangInput };

// =============================================================================
// État du discours et de la parole
// =============================================================================

/** @type {boolean} */
export let readytotalk = false;

/** @type {number} */
export let previousimage = 0;

/** @type {number} */
export let currentscroll = 1;

/** @type {string} */
export let lastSentenceTranslated = '';

/** @type {number} */
export let LiveTranslationNum = 0;

/** @type {number} */
export const LiveTranslationFreq = 5;

/** @type {boolean} */
export let Studentcompletespeechactive = false;

// =============================================================================
// Setter functions for translation state (désormais gérée par le store)
// =============================================================================

/**
 * Set the last partial translation
 * @param {string} value - The translation text
 */
export function setLastPartialTranslation(value) {
  lastPartialTranslation = value;
}

/**
 * Set the last sentence translated
 * @param {string} value - The translated text
 */
export function setLastSentenceTranslated(value) {
  lastSentenceTranslated = value;
}

/**
 * Set the LiveTranslationNum value
 * @param {number} value - The new value
 */
export function setLiveTranslationNum(value) {
  LiveTranslationNum = value;
}

/**
 * Set the readytotalk flag
 * @param {boolean} value - Whether the speaker is ready
 */
export function setReadytotalk(value) {
  readytotalk = value;
}

/**
 * Set the Studentgotolive flag
 * @param {boolean} value - Whether the student went live
 */
export function setStudentgotolive(value) {
  Studentgotolive = value;
}

/**
 * Set the Studentcurrentdiscours index
 * @param {number} value - The current discours index
 */
export function setStudentcurrentdiscours(value) {
  Studentcurrentdiscours = value;
}

/**
 * Set the Studentcompletespeechactive flag
 * @param {boolean} value - Whether a complete speech is active
 */
export function setStudentcompletespeechactive(value) {
  Studentcompletespeechactive = value;
}

/**
 * Set the previousimage value
 * @param {number} value - The previous image number
 */
export function setPreviousimage(value) {
  previousimage = value;
}

/**
 * Set the currentscroll value
 * @param {number} value - The scroll position
 */
export function setCurrentscroll(value) {
  currentscroll = value;
}
