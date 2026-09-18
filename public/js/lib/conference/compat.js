/** @module conference/compat */
import { EcrireCR as libEcrireCR } from '../chat/display.js';
import { AbortTimeout as xhrAbortTimeout } from '../xhr.js';

/**
 * Write with carriage return - re-export from lib
 * @param {string} who - The sender
 * @param {string} ch - The text to write
 * @param {number} order - The order number
 */
export function EcrireCR(who, ch, order) {
  return libEcrireCR(who, ch, order);
}

/**
 * Abort timeout for XHR requests - re-export from lib
 * @param {XMLHttpRequest} r - The XHR request
 * @param {string} s - The context string
 */
export function AbortTimeout(r, s) {
  return xhrAbortTimeout(r, s);
}

/**
 * Re-exports from other modules
 */
// ROOM_TOKEN est désormais géré directement via getRoomToken() du store
// Export de getRoomToken pour compatibilité
import { getRoomToken } from '../state/shared.js';
export { getRoomToken };
