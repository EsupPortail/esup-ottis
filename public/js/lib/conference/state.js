/**
 * conference/state.js
 * Module central pour gérer l'état global de l'application conference
 * Utilise le store centralisé et réexporte les modules spécifiques
 */

// Réexporter depuis le store centralisé
export * from '../state/shared.js';
export * from '../state/auditor.js';

// state/conference.js fournit certains noms identiques à state/shared.js avec des
// clés de store différentes (ex : 'ROOM_TOKEN' vs 'roomToken') : un double
// export * les exclurait silencieusement (ambiguïté ESM). Réexport explicite de
// tout, sauf getRoomToken, getCurrentDiscours, setCurrentDiscours,
// getInputNumberQ et setInputNumberQ (fournis par state/shared.js ci-dessus).
export {
  getClassroomId,
  setClassroomId,
  setRoomToken,
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
