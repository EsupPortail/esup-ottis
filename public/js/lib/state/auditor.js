/**
 * @module lib/state/auditor
 * État spécifique à l'interface auditor (étudiant)
 * 
 * Utilisation :
 *   import { getAuditorName, setAuditorName, isSynthesisOn } from './lib/state/auditor.js';
 */

import { get, set } from './store.js';

// Auditor name
export const getAuditorName = () => get('AuditorName') || 'Auditor';
export const setAuditorName = (name) => set({ AuditorName: name });

// Speech synthesis
export const isSynthesisOn = () => get('synthesison') || false;
export const setSynthesisOn = (value) => set({ synthesison: value });

// Built-in voice
export const getBuiltinVoice = () => get('builtin_voice');
export const setBuiltinVoice = (value) => set({ builtin_voice: value });

// Built-in language
export const getBuiltinLanguage = () => get('builtin_language');
export const setBuiltinLanguage = (value) => set({ builtin_language: value });

// Auditor configuration
export const getAuditorConfig = () => get('auditorConfig') || {};
export const setAuditorConfig = (config) => set({ auditorConfig: config });
