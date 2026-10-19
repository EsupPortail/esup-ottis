/**
 * conference/state.js
 * Module central pour gérer l'état global de l'application conference
 * Utilise le store centralisé et réexporte les modules spécifiques
 */

// Réexporter depuis le store centralisé
export * from '../state/shared.js';
export * from '../state/auditor.js';

// state/conference.js : les anciens doublons de noms avec state/shared.js
// (roomToken, inputnumberQ) ont ete supprimes et la variante auditor de
// currentdiscours renommee (getAuditorCurrentDiscours) : il n'y a plus
// d'ambiguite ESM. Liste explicite conservee par prudence.
export {
  getClassroomId,
  setClassroomId,
  getWhichTranslator,
  setWhichTranslator,
  getTheme,
  setTheme,
  getCurrentConfig,
  setCurrentConfig,
  getNotebook,
  setNotebook,
  getNbRequests,
  setNbRequests,
  incrementNbRequests,
  getCurrentPresentation,
  setCurrentPresentation,
  incrementCurrentDiscours,
  decrementCurrentDiscours,
  getBuiltinRecognition,
  setBuiltinRecognition,
  isBuiltinRecognitionOn,
  setBuiltinRecognitionOn,
  isBuiltinExternalTranscriptionOn,
  setBuiltinExternalTranscriptionOn,
  isBuiltinMicrosoftTranscriptionOn,
  setBuiltinMicrosoftTranscriptionOn,
  getSubtitleLanguage,
  setSubtitleLanguage,
} from '../state/conference.js';

// Réexporter les modules spécifiques à la conference (sans variables d'état)
export * from './state/core.js';
export * from './state/pdf.js';
export * from './state/translation.js';
