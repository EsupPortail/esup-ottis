/**
 * Language State Module
 * ES Module for managing language state across the application
 */

/**
 * Available output voices
 * @type {Array}
 */
export let AllOutputVoices = [
    { id: 'fr-FR', name: 'Français' },
    { id: 'en-US', name: 'English' },
    { id: 'es-ES', name: 'Español' },
    { id: 'de-DE', name: 'Deutsch' },
    { id: 'it-IT', name: 'Italiano' },
    { id: 'pt-PT', name: 'Português' },
    { id: 'ru-RU', name: 'Русский' },
    { id: 'zh-CN', name: '中文' },
    { id: 'ja-JP', name: '日本語' },
    { id: 'ar-SA', name: 'العربية' }
];

/**
 * Available input languages
 * @type {Array<string>}
 */
export let TLangInput = [
    'fr', 'en', 'es', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ar'
];

/**
 * Maximum number of voices to display
 * @type {number}
 */
export let maxvoices = 10;

/**
 * Current input language
 * @type {string}
 */
export let currentInputLanguage = 'fr';

/**
 * Set the current input language
 * @param {string} langId - Language ID
 */
export function setCurrentInputLanguage(langId) {
    currentInputLanguage = langId;
}

/**
 * Current output voice
 * @type {string}
 */
export let currentOutputVoice = 'fr-FR';

/**
 * Set the current output voice
 * @param {string} voiceId - Voice ID
 */
export function setCurrentOutputVoice(voiceId) {
    currentOutputVoice = voiceId;
}

/**
 * Get voice by ID
 * @param {string} id - Voice ID
 * @returns {Object|null}
 */
export function getVoiceById(id) {
    return AllOutputVoices.find(voice => voice.id === id) || null;
}

/**
 * Get input language by ID
 * @param {string} id - Language ID
 * @returns {string|null}
 */
export function getInputLanguageById(id) {
    // TLangInput is an array of strings, so just check if it exists
    return TLangInput.includes(id) ? id : null;
}


