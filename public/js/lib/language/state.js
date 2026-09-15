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
 * Current output voice
 * @type {string}
 */
export let currentOutputVoice = 'fr-FR';

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
 * @returns {Object|null}
 */
export function getInputLanguageById(id) {
    return TLangInput.find(lang => lang.id === id) || null;
}
