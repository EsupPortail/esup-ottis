/**
 * @module conference/errors
 * Affichage centralise des messages d'erreur de la conference.
 * Module feuille (aucune dependance vers d'autres modules conference) :
 * ne pas y ajouter d'import de la conference sous peine de recree des cycles.
 */

import { _ } from '../dom/selector.js';
import * as logger from '../utils/logger.js';
import * as notify from '../utils/notify.js';

/**
 * Affiche un message d'erreur dans le conteneur dedie (#file-error),
 * avec repli console + alert si absent.
 * @param {string} message - Message d'erreur
 */
export function showError(message) {
  const errorContainer = _('file-error');
  if (errorContainer) {
    errorContainer.textContent = message;
    errorContainer.style.display = 'block';
  } else {
    logger.error('Erreur:', message);
    notify.error(`Erreur: ${message}`);
  }
}

/**
 * Affiche une erreur de presentation dans la barre de progression
 * (#progress-status), avec repli console.
 * @param {string} message - Le message d'erreur
 */
export function showPresentationError(message) {
  const statusEl = _('progress-status');
  if (statusEl) {
    if (typeof DOMPurify !== 'undefined') {
      statusEl.innerHTML = DOMPurify.sanitize(
        `<span style="color: red; font-weight: bold;">ERREUR: ${message}</span>`
      );
    } else {
      statusEl.innerHTML = `<span style="color: red; font-weight: bold;">ERREUR: ${message}</span>`;
    }
  }
  if (typeof window !== 'undefined' && window.console && window.console.error) {
    logger.error('Présentation error', { message });
  }
}
