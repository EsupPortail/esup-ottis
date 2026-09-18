/**
 * Request counter functions for auditor
 * Module indépendant pour éviter les dépendances circulaires
 */

import { _ } from '../dom/selector.js';

function increasependingrequests() {
  const nb = _('nbrequests');
  if (!nb) return;
  nb.innerHTML = nb.innerHTML * 1 + 1;
}

function decreasependingrequests() {
  const nb = _('nbrequests');
  if (!nb) return;
  nb.innerHTML = nb.innerHTML * 1 - 1;
}

export { increasependingrequests, decreasependingrequests };
