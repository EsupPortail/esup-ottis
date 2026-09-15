/**
 * Chat State Module
 * ES Module for managing chat application state
 */

/** Import du sélecteur DOM */
import { _ } from '../dom/selector.js';

/**
 * Screen chat messages array - Global array to store chat messages
 * @type {Array}
 */
export const ScreenCR = [];

/**
 * Chat screen DOM element reference
 * @type {HTMLElement|null}
 */
let chatScreenElement = null;

/**
 * Current chat session state variables from legacy
 */
export let currentdiscours = -1;
export let gotolive = false;

/**
 * Text to say (for speech synthesis) - Object indexed by line ID
 * @type {Object}
 */
export let ToSay = {};

/**
 * Text to translate - Array of translation requests
 * @type {Array}
 */
export let ToTranslate = [];

/**
 * Chat messages history
 * @type {Array}
 */
export const chatHistory = [];

/**
 * Current chat session ID
 * @type {string|null}
 */
export let currentSessionId = null;

/**
 * Initialize chat state
 * @param {HTMLElement|string} screenElement - Chat screen element or its ID
 */
export function initChatState(screenElement) {
    if (typeof screenElement === 'string') {
        chatScreenElement = document.getElementById(screenElement);
    } else {
        chatScreenElement = screenElement;
    }
    
    // Clear existing content
    if (chatScreenElement) {
        chatScreenElement.innerHTML = '';
    }
    
    // Reset state
    ScreenCR.length = 0;
    ToSay = {};
    ToTranslate = [];
    chatHistory.length = 0;
    currentSessionId = null;
}

/**
 * Set the chat screen element
 * @param {HTMLElement|string} element - Element or element ID
 */
export function setScreenCR(element) {
    if (typeof element === 'string') {
        chatScreenElement = document.getElementById(element);
    } else {
        chatScreenElement = element;
    }
}

/**
 * Get the ScreenCR messages array
 * @returns {Array}
 */
export function getScreenCR() {
    return ScreenCR;
}

/**
 * Get the chat screen DOM element
 * @returns {HTMLElement|null}
 */
export function getChatScreenElement() {
    return chatScreenElement;
}

/**
 * Add message to chat history
 * @param {Object} message - Message object
 * @param {string} message.text - Message text
 * @param {string} [message.sender] - Message sender
 * @param {string} [message.timestamp] - Message timestamp
 */
export function addToHistory(message) {
    chatHistory.push({
        text: message.text,
        sender: message.sender || 'user',
        timestamp: message.timestamp || new Date().toISOString()
    });
}

/**
 * Clear chat history
 */
export function clearHistory() {
    chatHistory.length = 0;
}

/**
 * Set text to say - Note: ToSay is now an object, this function clears and sets a single entry
 * @param {string} id - Line ID
 * @param {string} text - Text to say
 */
export function setToSay(id, text) {
    ToSay = {};
    ToSay[`${id}`] = text;
}

/**
 * Set text to translate - Note: ToTranslate is now an array
 * @param {Array} items - Translation request array [id, sourceLang, sourceText, targetLang, targetText]
 */
export function setToTranslate(items) {
    ToTranslate = Array.isArray(items) ? items : [];
}

/**
 * Get current text to say
 * @returns {string}
 */
export function getToSay() {
    return ToSay;
}

/**
 * Get current text to translate
 * @returns {string}
 */
export function getToTranslate() {
    return ToTranslate;
}
