/**
 * seg.js
 * Segmented control « Version du discours » (onglet Transcription).
 * « Version originale » -> affiche #notebookVO ; « Version traduite » -> #notebook.
 * Libellés statiques : traduits par le mécanisme i18n (class translate / data-key).
 */
import { _ } from '../dom/selector.js';

/**
 * @summary Affiche #notebookVO (version originale) ou #notebook (traduite)
 * @param {string} version - 'vo' ou 'tr'
 */
function showVersion(version) {
  const notebookVO = _('notebookVO');
  const notebook = _('notebook');
  if (notebookVO) notebookVO.hidden = version !== 'vo';
  if (notebook) notebook.hidden = version !== 'tr';
}

/**
 * @summary Initialise le segmented control (auto au chargement du module)
 */
function initSpeechVersion() {
  const voRadio = _('seg-vo');
  const trRadio = _('seg-tr');
  if (voRadio) voRadio.addEventListener('change', () => showVersion('vo'));
  if (trRadio) trRadio.addEventListener('change', () => showVersion('tr'));
  showVersion(voRadio && voRadio.checked ? 'vo' : 'tr');
}

initSpeechVersion();

export { showVersion, initSpeechVersion };
