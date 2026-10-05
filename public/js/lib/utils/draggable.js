/**
 * @module lib/utils/draggable
 * Draggable Utilities - Native drag-and-drop replacement for jQuery draggableTouch plugin
 * Supports both mouse and touch events
 */

/**
 * Make an element draggable with mouse and touch events
 * @param {HTMLElement|string} element - Element or its ID
 * @param {Object} [options] - Configuration options
 * @param {boolean} [options.useTransform=false] - Use CSS transform for positioning
 */
export function makeDraggable(element) {
  const el = typeof element === 'string' ? document.getElementById(element) : element;
  if (!el) return;

  let pos1 = 0; let pos2 = 0; let pos3 = 0; let
    pos4 = 0;
  let moveListener = null;
  let upListener = null;

  /**
     * Get the element's current position
     * @param {HTMLElement} el - The element
     * @returns {Object} Position { left: number, top: number }
     */
  function getPosition(el) {
    const rect = el.getBoundingClientRect();
    const style = window.getComputedStyle(el);
    const { position } = style;

    // Pour position:fixed, les coordonnées sont déjà relatives au viewport
    if (position === 'fixed') {
      return { left: rect.left, top: rect.top };
    }
    return {
      left: rect.left + window.scrollX,
      top: rect.top + window.scrollY,
    };
  }

  // Mouse events
  el.onmousedown = dragMouseDown;

  // Touch events
  el.ontouchstart = dragTouchStart;

  function dragMouseDown(e) {
    e = e || window.event;
    e.preventDefault();
    const pos = getPosition(el);
    pos1 = pos.left; // Stocker la position initiale X de l'élément
    pos2 = pos.top; // Stocker la position initiale Y de l'élément
    pos3 = e.clientX;
    pos4 = e.clientY;

    // Utiliser addEventListener pour éviter les conflits avec d'autres handlers
    // et capturer les événements même hors de l'élément
    upListener = () => closeDrag();
    moveListener = (e) => elementDrag(e);
    document.addEventListener('mouseup', upListener, { once: true, capture: true });
    document.addEventListener('mousemove', moveListener, { capture: true });
  }

  function dragTouchStart(e) {
    e = e || window.event;
    e.preventDefault();
    const pos = getPosition(el);
    pos1 = pos.left; // Stocker la position initiale X de l'élément
    pos2 = pos.top; // Stocker la position initiale Y de l'élément
    pos3 = e.touches[0].clientX;
    pos4 = e.touches[0].clientY;

    // Utiliser addEventListener pour éviter les conflits
    upListener = () => closeDrag();
    moveListener = (e) => elementDrag(e);
    document.addEventListener('touchend', upListener, { once: true, capture: true });
    document.addEventListener('touchmove', moveListener, { capture: true, passive: false });
  }

  function elementDrag(e) {
    e = e || window.event;
    e.preventDefault();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;

    // Calculer le delta de déplacement
    const dx = clientX - pos3;
    const dy = clientY - pos4;
    pos3 = clientX;
    pos4 = clientY;

    el.style.left = `${pos1 + dx}px`;
    el.style.top = `${pos2 + dy}px`;

    // Mettre à jour la position de référence pour le prochain événement
    pos1 += dx;
    pos2 += dy;
  }

  function closeDrag() {
    // Supprimer les listeners
    if (upListener) {
      document.removeEventListener('mouseup', upListener, { capture: true });
      document.removeEventListener('touchend', upListener, { capture: true });
    }
    if (moveListener) {
      document.removeEventListener('mousemove', moveListener, { capture: true });
      document.removeEventListener('touchmove', moveListener, { capture: true });
    }
    upListener = null;
    moveListener = null;
  }
}

/**
 * Make multiple elements draggable
 * @param {string} selector - CSS selector to match elements
 */
export function makeAllDraggable(selector) {
  const elements = document.querySelectorAll(selector);
  elements.forEach((el) => makeDraggable(el));
}
