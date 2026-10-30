import * as logger from '../utils/logger.js';
/**
 * @module conference/microsoft
 * Gestion de la reconnaissance vocale Microsoft Azure
 */

/**
 * Clé API Microsoft Azure Speech (à configurer via .env)
 * @type {string}
 */
export const MicrosoftKey = '';

/**
 * Région Microsoft Azure (ex: 'eastus', 'westeurope')
 * @type {string}
 */
export const MicrosoftRegion = '';

/**
 * Instance du recognizer Microsoft
 * @type {Object|null}
 */
let microsoftRecognizer = null;

/**
 * Démarrer la reconnaissance vocale continue avec Microsoft Azure
 * @param {string} key - Clé API Microsoft
 * @param {string} region - Région Azure
 */
export function doContinuousRecognition(key, region) {
  if (typeof SpeechRecognition === 'undefined' && typeof webkitSpeechRecognition === 'undefined') {
    logger.warn('Speech Recognition API not available');
    return;
  }

  // Si on a une clé Microsoft, on pourrait utiliser Azure Cognitive Services
  // Pour l'instant, on utilise l'API Web Speech standard
  if (microsoftRecognizer) {
    try {
      microsoftRecognizer.stopContinuousRecognitionAsync();
    } catch (e) {
      logger.error('Error stopping Microsoft recognizer:', e);
    }
  }

  // Initialiser le recognizer Microsoft si les crédentials sont fournis
  if (key && region) {
    // Cela nécessiterait l'import de Microsoft Cognitive Services SDK
    // Pour l'instant, on utilise un fallback vers Web Speech API
    logger.debug(
      'Microsoft Azure Speech Recognition would be initialized here with key:',
      key,
      'region:',
      region
    );
  }
}

/**
 * Arrêter la reconnaissance vocale
 */
export function stopRecognizer() {
  if (microsoftRecognizer) {
    try {
      microsoftRecognizer.stopContinuousRecognitionAsync();
      microsoftRecognizer = null;
    } catch (e) {
      logger.error('Error stopping Microsoft recognizer:', e);
    }
  }

  // Fallback Web Speech API (herite du code pre-modules) : instance globale
  // speechRecognition supprimee lors de la migration, remplacer par builtin_recognition
}

/**
 * Initialiser le recognizer Microsoft avec les paramètres globaux
 */
export function initMicrosoftRecognizer() {
  if (MicrosoftKey && MicrosoftRegion) {
    doContinuousRecognition(MicrosoftKey, MicrosoftRegion, 'fr-FR');
  }
}
