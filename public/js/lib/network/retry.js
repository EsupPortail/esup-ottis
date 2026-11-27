/**
 * @module network/retry
 * Politique anti-cascade pour les erreurs transport de la traduction.
 *
 * Une erreur passagere (reseau hors timeout de polling, 429, 5xx) est
 * relancee apres un backoff exponentiel disperse. Les 4xx (hors 429)
 * sont definitives : pas de reessai. Aucun texte d'erreur n'est ecrit
 * dans la transcription par ce module : les lignes d'erreur etaient
 * dictees puis retraduites dans toutes les langues, transformant un
 * pic de charge en avalanche.
 */
import { safeSetTimeout } from '../utils/cleanup.js';

// 1 essai initial + 2 relances
const MAX_ATTEMPTS = 3;
const BASE_DELAY_MS = 1000;
const MAX_DELAY_MS = 8000;

/**
 * Erreur transport passagere ?
 * @param {Object} response - reponse transport { status, statusText, ... }
 * @returns {boolean} true si un reessai est pertinent
 */
export function isTransientTransportError(response) {
  if (!response) {
    return false;
  }
  const status = typeof response.status === 'number' ? response.status : 0;
  const statusText = typeof response.statusText === 'string' ? response.statusText : '';
  if (status === 0) {
    // Timeout de polling ou artefact d'annulation post-timeout
    // (createFetchRequest annnonce 'Timeout' PUIS 'Request aborted') :
    // pas une erreur a retryer
    return statusText !== 'Timeout' && statusText !== 'Request aborted';
  }
  return status === 429 || status >= 500;
}

/**
 * Delai de backoff borne, avec dispersion pour que tous les clients
 * ne reessaient pas au meme instant.
 * @param {number} attempt - rang du reessai (1 pour le premier)
 * @returns {number} delai en millisecondes
 */
export function backoffDelayMs(attempt) {
  const exponential = Math.min(MAX_DELAY_MS, BASE_DELAY_MS * 2 ** (attempt - 1));
  return Math.round(exponential * (0.75 + Math.random() * 0.5));
}

/**
 * Relance `send` en backoff si la derniere erreur est passagere et
 * qu'il reste des essais ; sinon appelle `onGiveUp` une seule fois.
 * @param {Function} send - relance la requete (recree FormData, requete, etc.)
 * @param {Object} lastResponse - derniere reponse transport en erreur
 * @param {number} attempt - nombre d'essais deja effectues
 * @param {Function} onGiveUp - appele si aucun reessai n'est possible
 * @returns {void}
 */
export function attemptBoundedRetry(send, lastResponse, attempt, onGiveUp) {
  if (attempt >= MAX_ATTEMPTS || !isTransientTransportError(lastResponse)) {
    onGiveUp();
    return;
  }
  safeSetTimeout(send, backoffDelayMs(attempt));
}
