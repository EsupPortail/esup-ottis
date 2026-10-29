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

// Frequence des traductions intermediaires (sous-titres partiels) :
// 1 appel de traduction tous les N partiels ; 0 = aucune traduction
// intermediaire (le texte final reste traduit). Configuree par le serveur
// via window.OTTIS_CONFIG (CONFERENCE_TRANSLATION_PARTIAL_FREQ dans .env, defaut 5).
const liveTranslationFreqConfig =
  (typeof window !== 'undefined' &&
    window.OTTIS_CONFIG &&
    window.OTTIS_CONFIG.conference_translation_partial_freq) ||
  '';

/** @type {number} */
export let LiveTranslationFreq = 5;
if (String(liveTranslationFreqConfig).toLowerCase() === 'none') {
  LiveTranslationFreq = 0;
} else {
  const parsedFreq = parseInt(liveTranslationFreqConfig, 10);
  if (Number.isFinite(parsedFreq)) {
    LiveTranslationFreq = parsedFreq <= 0 ? 1 : parsedFreq;
  }
}

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
