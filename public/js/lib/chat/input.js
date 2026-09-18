/**
 * @module lib/chat/input
 * Chat input management - Centralized Enter key handler for chat functionality
 * Works for both conference (teacher) and auditor (student) contexts
 */

import { _ } from '../dom/selector.js';
import { saveTextQ } from './main.js';

/**
 * Setup Enter key handler for chat input field
 * - Listens for Enter key press on the question input field
 * - Sends the message via saveTextQ
 * - Clears the input field after sending
 * - Works in both conference and auditor contexts
 */
export function setupChatInput() {
  const questionEl = _('question');
  if (questionEl) {
    questionEl.addEventListener('keyup', (event) => {
      if (event.code === 'Enter') {
        const text = questionEl.value.replaceAll('\n', '').trim();
        if (text !== '') {
          saveTextQ(text);
          questionEl.value = '';
        }
        event.preventDefault();
        event.stopPropagation();
      }
    }, false);
  }
}
