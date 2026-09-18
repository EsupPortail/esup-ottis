/**
 * @module lib/state/shared
 * État partagé entre conference et auditor
 * 
 * Re-exporte les éléments du store pour une import simplifiée.
 * 
 * Utilisation :
 *   import { getToSay, pushToSay, getToTranslate, pushToTranslate } from './lib/state/shared.js';
 */

// Importer l'API complète du store
import {
    getState,
    get,
    set,
    reset,
    subscribe,
    increment,
    push,
    setAt,
    removeAt,
    setProp,
    deleteProp
} from './store.js';

// Ré-exporter l'API complète du store
export {
    getState,
    get,
    set,
    reset,
    subscribe,
    increment,
    push,
    setAt,
    removeAt,
    setProp,
    deleteProp
};

// ============================================================================
// Getters/Setters simplifiés pour les variables les plus utilisées
// ============================================================================

// ToSay
export const getToSay = () => get('ToSay');
export const setToSay = (value) => set({ ToSay: value });
export const pushToSay = (item) => push('ToSay', item);

// ToTranslate
export const getToTranslate = () => get('ToTranslate');
export const setToTranslate = (value) => set({ ToTranslate: value });
export const pushToTranslate = (item) => push('ToTranslate', item);

// ToTranslate2
export const getToTranslate2 = () => get('ToTranslate2');
export const setToTranslate2 = (value) => set({ ToTranslate2: value });

// AllPhrases
export const getAllPhrases = () => get('AllPhrases');
export const setAllPhrases = (value) => set({ AllPhrases: value });
export const setPhrase = (key, value) => setProp('AllPhrases', key, value);

// AllLines
export const getAllLines = () => get('AllLines');
export const setAllLines = (value) => set({ AllLines: value });
export const setLine = (key, value) => setProp('AllLines', key, value);

// currentdiscours
export const getCurrentDiscours = () => get('currentdiscours');
export const setCurrentDiscours = (value) => set({ currentdiscours: value });

// gotolive
export const isGotolive = () => get('gotolive');
export const setGotolive = (value) => set({ gotolive: value });

// completespeechactive
export const isCompletespeechactive = () => get('completespeechactive');
export const setCompletespeechactive = (value) => set({ completespeechactive: value });

// currentorder_readtext
export const getCurrentOrder = () => get('currentorder_readtext');
export const setCurrentOrder = (value) => set({ currentorder_readtext: value });
export const incrementCurrentOrder = () => increment('currentorder_readtext');

// maxorder_readtext
export const getMaxOrder = () => get('maxorder_readtext');
export const setMaxOrder = (value) => set({ maxorder_readtext: value });

// inputnumber
export const getInputNumber = () => get('inputnumber');
export const setInputNumber = (value) => set({ inputnumber: value });

// builtin_discoursrate
export const getDiscoursRate = () => get('builtin_discoursrate');
export const setDiscoursRate = (value) => set({ builtin_discoursrate: value });

// ScreenCR
export const getScreenCR = () => get('ScreenCR');
export const setScreenCR = (value) => set({ ScreenCR: value });

// AllOutputVoices
export const getAllOutputVoices = () => get('AllOutputVoices');
export const setAllOutputVoices = (value) => set({ AllOutputVoices: value });

// TLangInput
export const getTLangInput = () => get('TLangInput');
export const setTLangInput = (value) => set({ TLangInput: value });

// TLangOutput
export const getTLangOutput = () => get('TLangOutput');
export const setTLangOutput = (value) => set({ TLangOutput: value });

// BASE_PATH
export const getBasePath = () => get('BASE_PATH');

// ROOM_TOKEN
export const getRoomToken = () => get('ROOM_TOKEN');

// ToSay (objet/dictionnaire) - fonctions supplémentaires
export const setToSayKey = (key, value) => setProp('ToSay', key, value);
export const getToSayKey = (key) => get('ToSay')[key];

// ToTranslate - fonctions supplémentaires pour gestion de tableau
export const unshiftToTranslate = (item) => {
  const current = get('ToTranslate') || [];
  set({ ToTranslate: [item, ...current] });
};
export const shiftToTranslate = () => {
  const current = get('ToTranslate') || [];
  if (current.length > 0) {
    const newArray = [...current];
    newArray.splice(0, 1);
    set({ ToTranslate: newArray });
    return newArray;
  }
  return [];
};

// lastNotebookVOContent
export const getLastNotebookVOContent = () => get('lastNotebookVOContent');
export const setLastNotebookVOContent = (value) => set({ lastNotebookVOContent: value });

// currentrandom
export const getCurrentRandom = () => get('currentrandom');
export const setCurrentRandom = (value) => set({ currentrandom: value });

// languageSelected
export const isLanguageSelected = () => get('languageSelected');
export const setLanguageSelected = (value) => set({ languageSelected: value });

// inputnumberQ
export const getInputNumberQ = () => get('inputnumberQ');
export const setInputNumberQ = (value) => set({ inputnumberQ: value });

// sendInProgress
export const isSendInProgress = () => get('sendInProgress');
export const setSendInProgress = (value) => set({ sendInProgress: value });

// firstclick
export const isFirstClick = () => get('firstclick');
export const setFirstClick = (value) => set({ firstclick: value });

// firsttime
export const isFirstTime = () => get('firsttime');
export const setFirstTime = (value) => set({ firsttime: value });

// chatPollingStarted
export const isChatPollingStarted = () => get('chatPollingStarted');
export const setChatPollingStarted = (value) => set({ chatPollingStarted: value });

// chatPollingIntervalId
export const getChatPollingIntervalId = () => get('chatPollingIntervalId');
export const setChatPollingIntervalId = (value) => set({ chatPollingIntervalId: value });

// supportedlanguages
export const getSupportedLanguages = () => get('supportedlanguages');
export const setSupportedLanguages = (value) => set({ supportedlanguages: value });
