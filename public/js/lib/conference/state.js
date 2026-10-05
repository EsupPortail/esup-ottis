/**
 * conference/state.js
 * Module central pour gérer l'état global de l'application conference
 * Utilise le store centralisé et réexporte les modules spécifiques
 */

// Réexporter depuis le store centralisé
export * from '../state/shared.js';
export * from '../state/conference.js';
export * from '../state/auditor.js';

// Réexporter les modules spécifiques à la conference (sans variables d'état)
export * from './state/core.js';
export * from './state/pdf.js';
export * from './state/translation.js';
