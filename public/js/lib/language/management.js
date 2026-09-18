/**
 * Language Management Module
 * ES Module for setting and managing language preferences
 */

import { 
  TLangInput, 
  AllOutputVoices, 
  currentInputLanguage, 
  currentOutputVoice,
  setCurrentInputLanguage,
  setCurrentOutputVoice 
} from './state.js';

/**
 * Set the input language
 * @param {string} langId - Language ID (e.g., 'fr', 'en')
 * @param {boolean} [updateUI=true] - Whether to update UI elements
 */
export function setLanguageInput(langId, updateUI = true) {
    // TLangInput is an array of strings, so check if it includes the langId directly
    const langExists = TLangInput.includes(langId);
    if (langExists) {
        // Update the current language using the setter function
        setCurrentInputLanguage(langId);
        
        // Store in localStorage for persistence
        localStorage.setItem('inputLanguage', langId);
        
        // Update UI if requested
        if (updateUI) {
            updateLanguageInputsUI(langId);
        }
        
        return true;
    }
    return false;
}

/**
 * Set the output voice
 * @param {string} voiceId - Voice ID (e.g., 'fr-FR', 'en-US')
 * @param {boolean} [updateUI=true] - Whether to update UI elements
 */
export function setLanguageOutput(voiceId, updateUI = true) {
    // AllOutputVoices can contain objects with id property or SpeechSynthesisVoice objects with lang property
    const voice = AllOutputVoices.find(v => (v.id && v.id === voiceId) || v.lang === voiceId);
    if (voice) {
        // Update the current voice using the setter function
        setCurrentOutputVoice(voiceId);
        
        // Store in localStorage for persistence
        localStorage.setItem('outputVoice', voiceId);
        
        // Update UI if requested
        if (updateUI) {
            updateVoiceSelectsUI(voiceId);
        }
        
        return true;
    }
    return false;
}

/**
 * Get current input language
 * @returns {string}
 */
export function getCurrentInputLanguage() {
    return currentInputLanguage;
}

/**
 * Get current output voice
 * @returns {string}
 */
export function getCurrentOutputVoice() {
    return currentOutputVoice;
}

/**
 * Load language preferences from localStorage
 */
export function loadLanguagePreferences() {
    const savedInput = localStorage.getItem('inputLanguage');
    const savedOutput = localStorage.getItem('outputVoice');
    
    // TLangInput is an array of strings
    if (savedInput && TLangInput.includes(savedInput)) {
        setCurrentInputLanguage(savedInput);
    }
    
    // AllOutputVoices can contain both SpeechSynthesisVoice objects and plain objects with id property
    if (savedOutput && AllOutputVoices.some(v => (v.id && v.id === savedOutput) || v.lang === savedOutput)) {
        setCurrentOutputVoice(savedOutput);
    }
}

/**
 * Update all input language UI elements
 * @param {string} langId - Selected language ID
 */
function updateLanguageInputsUI(langId) {
    const inputs = document.querySelectorAll('[data-language-input]');
    inputs.forEach(input => {
        if (input.value === langId) {
            input.checked = true;
        }
    });
}

/**
 * Update all voice select UI elements
 * @param {string} voiceId - Selected voice ID
 */
function updateVoiceSelectsUI(voiceId) {
    const selects = document.querySelectorAll('[data-voice-select]');
    selects.forEach(select => {
        if (select.value === voiceId) {
            select.selected = true;
        }
    });
}

/**
 * Get display name for a language ID
 * @param {string} langId - Language ID
 * @returns {string}
 */
export function getLanguageDisplayName(langId) {
    // TLangInput is an array of strings, so just return the langId
    // For display names, we'd need a mapping, but for now return the ID
    return langId;
}

/**
 * Get display name for a voice ID
 * @param {string} voiceId - Voice ID
 * @returns {string}
 */
export function getVoiceDisplayName(voiceId) {
    const voice = AllOutputVoices.find(v => (v.id && v.id === voiceId) || v.lang === voiceId);
    return voice ? (voice.name || voice.lang) : voiceId;
}
