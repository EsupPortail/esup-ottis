/**
 * @module utils/notify
 * Notifications flottantes (toast) remplacement d'alert().
 * Module feuille : n'importe aucun autre module du projet (cycles).
 *
 * Usage : notify.success('...'), notify.error('...'),
 *         notify.warning('...'), notify.info('...')
 */

const CONTAINER_ID = 'ottis-notify-container';
const DEFAULT_DURATION = {
  success: 3000,
  info: 4000,
  warning: 5000,
  error: 6000,
};

const TOAST_STYLES = {
  success: 'background-color:#1a7f37;',
  error: 'background-color:#c62828;',
  warning: 'background-color:#e65100;',
  info: 'background-color:#1565c0;',
};

const TOAST_BASE = [
  'pointer-events:auto',
  'padding:12px 16px',
  'border-radius:8px',
  'color:#fff',
  'font-family:inherit',
  'font-size:14px',
  'line-height:1.4',
  'box-shadow:0 4px 12px rgba(0,0,0,0.25)',
  'opacity:0',
  'transition:opacity 0.2s ease-in',
].join(';');

const CONTAINER_STYLES = [
  'position:fixed',
  'bottom:20px',
  'right:20px',
  'z-index:10000',
  'display:flex',
  'flex-direction:column',
  'gap:8px',
  'max-width:360px',
  'pointer-events:none',
].join(';');

/**
 * Obtenir ou creer le conteneur des notifications.
 * @returns {HTMLElement}
 */
function getContainer() {
  let container = document.getElementById(CONTAINER_ID);
  if (!container) {
    container = document.createElement('div');
    container.id = CONTAINER_ID;
    container.style.cssText = CONTAINER_STYLES;
    document.body.appendChild(container);
  }
  return container;
}

/**
 * Afficher une notification toast.
 * @param {string} message - Message a afficher
 * @param {string} [type='info'] - Type : 'success', 'error', 'warning', 'info'
 * @param {number} [duration] - Duree en ms (defaut selon type)
 */
function show(message, type, duration) {
  const toastType = type || 'info';
  const container = getContainer();
  const toast = document.createElement('div');

  toast.style.cssText = `${TOAST_BASE};${TOAST_STYLES[toastType] || TOAST_STYLES.info};`;
  toast.textContent = message;
  toast.setAttribute('role', 'status');

  container.appendChild(toast);

  requestAnimationFrame(() => {
    toast.style.opacity = '1';
  });

  const ms = duration || DEFAULT_DURATION[toastType] || DEFAULT_DURATION.info;
  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 250);
  }, ms);

  toast.addEventListener('click', () => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 250);
  });
}

/**
 * Notification de succes (vert).
 * @param {string} message
 */
export function success(message) {
  show(message, 'success');
}

/**
 * Notification d'erreur (rouge).
 * @param {string} message
 */
export function error(message) {
  show(message, 'error');
}

/**
 * Notification d'avertissement (orange).
 * @param {string} message
 */
export function warning(message) {
  show(message, 'warning');
}

/**
 * Notification d'information (bleu).
 * @param {string} message
 */
export function info(message) {
  show(message, 'info');
}
