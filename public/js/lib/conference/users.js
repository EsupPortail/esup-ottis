/**
 * @module conference/users
 * Affichage de l'etat des utilisateurs connectes (#connectedusers).
 * Module feuille : ne pas y ajouter d'import de la conference sous peine
 * de recree des cycles (notebook et chat/handlers importent ce module).
 */

import { _ } from '../dom/selector.js';
import { NBconnectedusers, Tconnectedusers } from './state/core.js';

/**
 * Met a jour l'affichage du nombre d'utilisateurs connectes.
 */
export function updateUsers() {
  const connectedEl = _('connectedusers');
  if (connectedEl) {
    connectedEl.textContent = `${NBconnectedusers} connected users`;
    if (Tconnectedusers && Array.isArray(Tconnectedusers)) {
      connectedEl.title = Tconnectedusers.join(', ');
    } else {
      connectedEl.title = '';
    }
  }
}
