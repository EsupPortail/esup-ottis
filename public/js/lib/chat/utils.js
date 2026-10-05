/**
 * Chat Utilities Module
 * ES Module for chat-related utility functions
 */

import { ScreenCR, ToSay, ToTranslate, chatHistory, clearHistory } from './state.js';
import { _ } from '../dom/selector.js';
import { readTextQ } from './main.js';
import { setInputNumberQ } from '../state/shared.js';

/**
 * Clear the classroom chat and reset ScreenCR array
 */
export function ClearCR() {
    const classroomEl = _('classroom');
    if (classroomEl) {
        classroomEl.innerHTML = '';
    }
    // Reset the ScreenCR array
    ScreenCR.length = 0;
    clearHistory();
}

/**
 * Refresh the classroom chat display from ScreenCR array
 */
export function RefreshCR() {
    let html = '<ul role="list" aria-live="polite" style="list-style: none; padding: 0; margin: 0;">';
    for (let i = 0; i < ScreenCR.length; i++) {
        if (ScreenCR[i] !== undefined) {
            html += typeof window !== 'undefined' && typeof window.DOMPurify !== 'undefined'
                ? window.DOMPurify.sanitize(ScreenCR[i])
                : ScreenCR[i];
        }
    }
    html += '</ul>';
    const classroomEl = _('classroom');
    if (classroomEl) {
        classroomEl.innerHTML = typeof window !== 'undefined' && typeof window.DOMPurify !== 'undefined'
            ? window.DOMPurify.sanitize(html)
            : html;
    }
}

/**
 * Reload the classroom queue - Clear and refresh
 */
export function recharger() {
    ClearCR();
    // Reset inputnumberQ to 0 to force reprocessing all messages (for language change)
    setInputNumberQ(0);
    // Call legacy functions if they exist (will be migrated later)
    if (typeof readTextQ !== 'undefined') {
        readTextQ(_('classroomid')?.value);
    }
}

/**
 * Display text in chat screen
 * @param {string} text - Text to display
 * @param {string} [className=''] - CSS class for the message
 */
export function displayInCR(text, className = '') {
    // Add to ScreenCR array
    ScreenCR.push(text);
    
    // Also add to history
    chatHistory.push({
        text: text,
        sender: 'user',
        timestamp: new Date().toISOString()
    });
    
    // Refresh display
    RefreshCR();
}

/**
 * Clear both ToSay and ToTranslate
 */
export function clearChatText() {
    ToSay = {};
    ToTranslate = [];
}

/**
 * Format chat message for display
 * @param {string} text - Message text
 * @param {string} [sender='user'] - Message sender
 * @returns {string} - Formatted HTML
 */
export function formatMessage(text, sender = 'user') {
    const timestamp = new Date().toLocaleTimeString();
    return `<div class="chat-message ${sender}"><span class="sender">${sender}</span> <span class="time">[${timestamp}]</span>: ${text}</div>`;
}

/**
 * Escape HTML in text to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string}
 */
export function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Scroll chat to bottom
 */
export function scrollToBottom() {
    const classroomEl = _('classroom');
    if (classroomEl) {
        classroomEl.scrollTop = classroomEl.scrollHeight;
    }
}
