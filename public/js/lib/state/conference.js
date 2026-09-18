/**
 * @module lib/state/conference
 * État spécifique à l'interface conference (professeur)
 * 
 * Utilisation :
 *   import { getClassroomId, getRoomToken, getWhichTranslator } from './lib/state/conference.js';
 */

import { get, set, increment } from './store.js';

// Classroom/Room information
export const getClassroomId = () => get('classroomid') || '';
export const setClassroomId = (id) => set({ classroomid: id });

export const getRoomToken = () => get('roomToken') || '';
export const setRoomToken = (token) => set({ roomToken: token });

// Translator configuration
export const getWhichTranslator = () => get('whichtranslator') || '';
export const setWhichTranslator = (translator) => set({ whichtranslator: translator });

// Theme
export const getTheme = () => get('theme') || 'flat';
export const setTheme = (theme) => set({ theme });

// Configuration
export const getCurrentConfig = () => get('currentconfig') || 'ABYANACNNCNNNYYYYN';
export const setCurrentConfig = (config) => set({ currentconfig: config });

// Notebook content
export const getNotebook = () => get('notebook') || '';
export const setNotebook = (content) => set({ notebook: content });

// Number of pending requests
export const getNbRequests = () => get('nbrequests') || 0;
export const setNbRequests = (count) => set({ nbrequests: count });
export const incrementNbRequests = () => increment('nbrequests');

// Presentation state
export const getCurrentPresentation = () => get('currentPresentation') || [];
export const setCurrentPresentation = (presentation) => set({ currentPresentation: presentation });

export const getCurrentDiscours = () => get('currentDiscours') || 0;
export const setCurrentDiscours = (index) => set({ currentDiscours: index });
export const incrementCurrentDiscours = () => increment('currentDiscours');
export const decrementCurrentDiscours = () => increment('currentDiscours', -1);

// Speech recognition state
export const getBuiltinRecognition = () => get('builtin_recognition');
export const setBuiltinRecognition = (value) => set({ builtin_recognition: value });

export const isBuiltinRecognitionOn = () => get('builtin_recognitionon') || false;
export const setBuiltinRecognitionOn = (value) => set({ builtin_recognitionon: value });

export const isBuiltinExternalTranscriptionOn = () => get('builtin_externaltranscritionon') || false;
export const setBuiltinExternalTranscriptionOn = (value) => set({ builtin_externaltranscritionon: value });

export const isBuiltinMicrosoftTranscriptionOn = () => get('builtin_microsofttranscritionon') || false;
export const setBuiltinMicrosoftTranscriptionOn = (value) => set({ builtin_microsofttranscritionon: value });

// Conference translation state
export const getSubtitleLanguage = () => get('subtitle_language') || '';
export const setSubtitleLanguage = (lang) => set({ subtitle_language: lang });

export const getInputNumberQ = () => get('inputnumberQ') || 0;
export const setInputNumberQ = (value) => set({ inputnumberQ: value });
