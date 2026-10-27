/**
 * @module utils/logger
 * Logger centralise du cote client.
 * Module feuille : ne rien importer d'autres modules du projet (cycles).
 *
 * En production (sans window.pageData.debug), seuls warn et error passent.
 * debug et info sont utiles au developpement.
 */

function isDebugEnabled() {
  return !!(typeof window !== 'undefined' && window.pageData && window.pageData.debug);
}

/* eslint-disable no-console -- point de sortie unique vers la console,
   les 84 console.* du projet migrent ici */

/**
 * Message de debug (silencieux hors mode debug).
 * @param {...*} args
 */
export function debug(...args) {
  if (isDebugEnabled()) console.debug(...args);
}

/**
 * Message d'information (silencieux hors mode debug).
 * @param {...*} args
 */
export function info(...args) {
  if (isDebugEnabled()) console.info(...args);
}

/**
 * Avertissement (toujours affiche).
 * @param {...*} args
 */
export function warn(...args) {
  console.warn(...args);
}

/**
 * Erreur (toujours affichee).
 * @param {...*} args
 */
export function error(...args) {
  console.error(...args);
}
