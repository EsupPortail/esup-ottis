/**
 * Language Management Module
 * ES Module for setting and managing language preferences
 */

import { TLangInput, AllOutputVoices, currentInputLanguage, currentOutputVoice } from './state.js';

/**
 * Set the input language
 * @param {string} langId - Language ID (e.g., 'fr', 'en')
 * @param {boolean} [updateUI=true] - Whether to update UI elements
 */
export function setLanguageInput(langId, updateUI = true) {
    const lang = TLangInput.find(l => l.id === langId);
    if (lang) {
        // Update the current language
        currentInputLanguage = langId;
        
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
    const voice = AllOutputVoices.find(v => v.id === voiceId);
    if (voice) {
        // Update the current voice
        currentOutputVoice = voiceId;
        
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
    
    if (savedInput && TLangInput.some(l => l.id === savedInput)) {
        currentInputLanguage = savedInput;
    }
    
    if (savedOutput && AllOutputVoices.some(v => v.id === savedOutput)) {
        currentOutputVoice = savedOutput;
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
    const lang = TLangInput.find(l => l.id === langId);
    return lang ? lang.name : langId;
}

/**
 * Get display name for a voice ID
 * @param {string} voiceId - Voice ID
 * @returns {string}
 */
export function getVoiceDisplayName(voiceId) {
    const voice = AllOutputVoices.find(v => v.id === voiceId);
    return voice ? voice.name : voiceId;
}
