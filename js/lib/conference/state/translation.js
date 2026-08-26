/**
 * conference/state/translation.js
 * Module pour gérer l'état de traduction et du discours
 */

// =============================================================================
// État de la traduction étudiante
// =============================================================================

/** @type {string} */
export let lastPartialTranslation = '';

/** @type {Array} */
export let StudentToTranslate2 = [];

// =============================================================================
// État du discours étudiant
// =============================================================================

/** @type {number} */
export let Studentcurrentdiscours = 0;

/** @type {Object} */
export let StudentToSay = {};

/** @type {boolean} */
export let Studentgotolive = false;

/** @type {number} */
export let currentdiscours = 0;

// =============================================================================
// Langue des sous-titres
// =============================================================================

/** @type {string} */
export let subtitle_language = '';

// =============================================================================
// Voix disponibles
// =============================================================================

/** @type {Array} */
export let AllOutputVoices = [];

/** @type {Array} */
export let TLangInput = [];

// =============================================================================
// Variables globales pour la voix et la langue
// =============================================================================

/** @type {Object|null} */
export let builtin_voice = null;

/** @type {string} */
export let builtin_language = 'en-US'; // Anglais (États-Unis) par défaut

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
export let LiveTranslationFreq = 5;

/** @type {boolean} */
export let Studentcompletespeechactive = false;

// =============================================================================
// Setter functions for translation state
// =============================================================================

/**
 * Set the builtin voice
 * @param {Object} voice - The voice object
 */
export function setBuiltinVoice(voice) {
    builtin_voice = voice;
    if (typeof window !== 'undefined') {
        window.builtin_voice = voice;
    }
}

/**
 * Set the builtin language
 * @param {string} lang - The language code
 */
export function setBuiltinLanguage(lang) {
    builtin_language = lang;
    if (typeof window !== 'undefined') {
        window.builtin_language = lang;
    }
}

/**
 * Set the subtitle language
 * @param {string} lang - The language code
 */
export function setSubtitleLanguage(lang) {
    subtitle_language = lang;
    if (typeof window !== 'undefined') {
        window.subtitle_language = lang;
    }
}

/**
 * Set the last partial translation
 * @param {string} value - The translation text
 */
export function setLastPartialTranslation(value) {
    lastPartialTranslation = value;
    if (typeof window !== 'undefined') {
        window.lastPartialTranslation = value;
    }
}

/**
 * Set the last sentence translated
 * @param {string} value - The translated text
 */
export function setLastSentenceTranslated(value) {
    lastSentenceTranslated = value;
    if (typeof window !== 'undefined') {
        window.lastSentenceTranslated = value;
    }
}

/**
 * Set the LiveTranslationNum value
 * @param {number} value - The new value
 */
export function setLiveTranslationNum(value) {
    LiveTranslationNum = value;
    if (typeof window !== 'undefined') {
        window.LiveTranslationNum = value;
    }
}

/**
 * Set the previousimage value
 * @param {number} value - The previous image number
 */
export function setPreviousimage(value) {
    previousimage = value;
    if (typeof window !== 'undefined') {
        window.previousimage = value;
    }
}

/**
 * Set the currentscroll value
 * @param {number} value - The scroll position
 */
export function setCurrentscroll(value) {
    currentscroll = value;
    if (typeof window !== 'undefined') {
        window.currentscroll = value;
    }
}
