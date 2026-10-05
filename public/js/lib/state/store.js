/**
 * @module lib/state/store
 * Store centralisé pour l'application OMIST
 * 
 * Pattern : Encapsulation via closure + Publish/Subscribe
 * 
 * Utilisation :
 *   import { get, set, subscribe, getState, reset, increment, push } from './lib/state/store.js';
 * 
 * @author OMIST Team
 * @version 1.0
 */

// ============================================================================
// État initial
// ============================================================================
const initialState = {
    // --- État partagé conference ↔ auditor ---
    ToSay: [],
    ToTranslate: [],
    ToTranslate2: [],
    AllPhrases: {},
    AllLines: {},
    currentdiscours: '',
    gotolive: false,
    completespeechactive: false,
    currentorder_readtext: 0,
    maxorder_readtext: 0,
    inputnumber: 0,
    builtin_discoursrate: 1.0,
    ScreenCR: '',
    
    // --- Presentation state (conference) ---
    currentPresentation: [],
    currentDiscours: 0,
    
    // --- Speech recognition (conference) ---
    builtin_recognition: null,
    builtin_recognitionon: false,
    builtin_externaltranscritionon: false,
    builtin_microsofttranscritionon: false,
    
    // --- Conference translation state ---
    subtitle_language: '',
    
    // --- Langues ---
    AllOutputVoices: [],
    TLangInput: 'fr',
    TLangOutput: 'en',
    
    // --- Configuration ---
    BASE_PATH: '',
    ROOM_TOKEN: '',
    classroomid: '',
    roomToken: '',
    whichtranslator: '',
    theme: 'flat',
    currentconfig: 'ABYANACNNCNNNYYYYN',
    supportedlanguages: [],
    
    // --- Auditor ---
    AuditorName: 'Auditor',
    synthesison: false,
    builtin_voice: null,
    builtin_language: null,
    
    // --- Chat ---
    nbrequests: 0,
    lastNotebookVOContent: '',
    currentrandom: '',
    languageSelected: false,
    inputnumberQ: 0,
    sendInProgress: false,
    firstclick: false,
    firsttime: true,
    chatPollingStarted: false,
    chatPollingIntervalId: null
};

// ============================================================================
// État interne (privé via closure)
// ============================================================================
let state = { ...initialState };

// ============================================================================
// Abonnés (pattern Publish/Subscribe)
// ============================================================================
const subscribers = new Set();

// ============================================================================
// API Publique
// ============================================================================

/**
 * Obtenir l'état complet (copie profonde)
 * @returns {Object} Copie complète de l'état
 */
export const getState = () => JSON.parse(JSON.stringify(state));

/**
 * Obtenir une valeur spécifique de l'état
 * @param {string} key - Clé de l'état
 * @returns {*} Valeur associée à la clé
 */
export const get = (key) => state[key];

/**
 * Mettre à jour l'état (merge partiel)
 * @param {Object} updates - Objet avec les clés/valeurs à mettre à jour
 */
export const set = (updates) => {
    state = { ...state, ...updates };
    // Notifier tous les abonnés avec une copie de l'état
    subscribers.forEach(callback => callback({ ...state }));
};

/**
 * Réinitialiser l'état aux valeurs initiales
 */
export const reset = () => {
    state = { ...initialState };
    subscribers.forEach(callback => callback({ ...state }));
};

/**
 * S'abonner aux changements d'état
 * @param {Function} callback - Fonction de rappel (recevra une copie du nouvel état)
 * @returns {Function} Fonction pour se désabonner
 */
export const subscribe = (callback) => {
    subscribers.add(callback);
    // Retourner la fonction de désabonnement
    return () => subscribers.delete(callback);
};

// ============================================================================
// Utilitaires de manipulation
// ============================================================================

/**
 * Incrémenter une valeur numérique de l'état
 * @param {string} key - Clé à incrémenter
 * @param {number} [by=1] - Valeur d'incrément (défaut: 1)
 */
export const increment = (key, by = 1) => {
    if (typeof state[key] === 'number') {
        set({ [key]: state[key] + by });
    }
};

/**
 * Ajouter un élément à un tableau de l'état
 * @param {string} key - Clé du tableau
 * @param {*} item - Élément à ajouter
 */
export const push = (key, item) => {
    if (Array.isArray(state[key])) {
        set({ [key]: [...state[key], item] });
    }
};

/**
 * Définir une valeur dans un tableau à un index spécifique
 * @param {string} key - Clé du tableau
 * @param {number} index - Index
 * @param {*} value - Valeur à définir
 */
export const setAt = (key, index, value) => {
    if (Array.isArray(state[key]) && index >= 0 && index < state[key].length) {
        const newArray = [...state[key]];
        newArray[index] = value;
        set({ [key]: newArray });
    }
};

/**
 * Supprimer un élément d'un tableau
 * @param {string} key - Clé du tableau
 * @param {number} index - Index à supprimer
 */
export const removeAt = (key, index) => {
    if (Array.isArray(state[key]) && index >= 0 && index < state[key].length) {
        const newArray = [...state[key]];
        newArray.splice(index, 1);
        set({ [key]: newArray });
    }
};

// ============================================================================
// Utilitaires pour les objets
// ============================================================================

/**
 * Définir une propriété dans un objet de l'état
 * @param {string} key - Clé de l'objet
 * @param {string} prop - Propriété
 * @param {*} value - Valeur à définir
 */
export const setProp = (key, prop, value) => {
    if (state[key] && typeof state[key] === 'object' && !Array.isArray(state[key])) {
        set({ [key]: { ...state[key], [prop]: value } });
    }
};

/**
 * Supprimer une propriété d'un objet de l'état
 * @param {string} key - Clé de l'objet
 * @param {string} prop - Propriété à supprimer
 */
export const deleteProp = (key, prop) => {
    if (state[key] && typeof state[key] === 'object' && !Array.isArray(state[key])) {
        const newObj = { ...state[key] };
        delete newObj[prop];
        set({ [key]: newObj });
    }
};

// ============================================================================
// Initialisation depuis window (compatibilité temporaire)
// ============================================================================
if (typeof window !== 'undefined') {
    // Initialiser depuis les variables window existantes
    const windowVars = [
        'BASE_PATH', 'ROOM_TOKEN', 'classroomid', 'roomToken',
        'ToSay', 'ToTranslate', 'ToTranslate2',
        'AllPhrases', 'AllLines', 'currentdiscours', 'gotolive',
        'completespeechactive', 'currentorder_readtext', 'maxorder_readtext',
        'inputnumber', 'builtin_discoursrate', 'ScreenCR',
        'AllOutputVoices', 'TLangInput', 'TLangOutput',
        'whichtranslator', 'theme', 'currentconfig',
        'AuditorName', 'synthesison', 'builtin_voice', 'builtin_language',
        'nbrequests', 'lastNotebookVOContent', 'currentrandom', 'languageSelected', 'inputnumberQ', 'sendInProgress', 'firstclick', 'firsttime', 'chatPollingStarted', 'chatPollingIntervalId',
        'currentPresentation', 'currentDiscours',
        'builtin_recognition', 'builtin_recognitionon', 'builtin_externaltranscritionon', 'builtin_microsofttranscritionon',
        'subtitle_language', 'inputnumberQ'
    ];
    
    windowVars.forEach(key => {
        if (key in window) {
            state[key] = window[key];
        }
    });
    
    // Initialiser depuis pageData si disponible
    if (window.pageData) {
        Object.assign(state, window.pageData);
    }
    
    // Initialiser supportedlanguages depuis pageData si disponible
    if (window.pageData?.supportedlanguages) {
        state.supportedlanguages = window.pageData.supportedlanguages;
    }
}

// ============================================================================
// Exports pour compatibilité descendante (si nécessaire)
// ============================================================================
// Ces exports permettent une migration progressive
export default {
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
