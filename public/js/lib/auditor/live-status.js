/**
 * live-status.js
 * Indicateur d'etat de la connexion a la conference (badge "En direct").
 * RGAA 4.1 : role="status" sur le conteneur (annonces aria-live polies).
 */

/**
 * @summary Met a jour l'indicateur d'etat de la connexion
 * @param {boolean} ok - true : connexion operationnelle, false : erreur
 */
function setLiveStatus(ok) {
  const badge = document.getElementById('live-status');
  if (!badge) return;
  const on = document.getElementById('live-on');
  const err = document.getElementById('live-err');
  if (on) on.hidden = !ok;
  if (err) err.hidden = ok;
  badge.classList.toggle('live--error', !ok);
}

export { setLiveStatus };
