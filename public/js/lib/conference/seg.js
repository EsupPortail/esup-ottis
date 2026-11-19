/**
 * seg.js
 * Segmented control « Version du discours » (onglet Transcription).
 * « Version originale » -> affiche #notebookVO ; « Version traduite » -> #notebook.
 * Libellés de base : traduits par le mécanisme i18n (class translate / data-key).
 * Le code de langue courant est ajouté à côté (#seg-vo-lang / #seg-tr-lang),
 * dans des spans frères sans data-key pour ne pas être effacés par l'i18n.
 */
import { _ } from '../dom/selector.js';

/**
 * @summary Formate un code de langue pour l'affichage ('fr-FR' -> 'FR-FR')
 * @param {string} lang - Code de langue
 * @returns {string} Libelle affiche dans le segment
 */
function formatLangLabel(lang) {
  return (
    String(lang || '')
      .trim()
      .toUpperCase() || '—'
  );
}

/**
 * @summary Met a jour les codes de langue affichés à côté des libellés
 * (langue du conferencier pour l'original, langue des sous-titres pour la traduction)
 */
function updateLangLabels() {
  const classroomLanguage = _('classroomlanguage');
  const voLang = _('seg-vo-lang');
  if (voLang) {
    const code = formatLangLabel(
      classroomLanguage && classroomLanguage.value ? classroomLanguage.value : ''
    );
    voLang.textContent = code === '—' ? '' : ` (${code})`;
  }

  const subtitleLanguage = _('subtitlelanguage');
  const trLang = _('seg-tr-lang');
  if (trLang) {
    const code = formatLangLabel(
      subtitleLanguage && subtitleLanguage.value ? subtitleLanguage.value : ''
    );
    trLang.textContent = code === '—' ? '' : ` (${code})`;
  }
}

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

  // Codes de langue affichés a côté des libellés : relier aux changements et au
  // peuplement asynchrone des selects (les options sont ajoutées plus tard)
  [ _('classroomlanguage'), _('subtitlelanguage') ].forEach((select) => {
    if (select) {
      select.addEventListener('change', updateLangLabels);
      new MutationObserver(updateLangLabels).observe(select, { childList: true });
    }
  });
  updateLangLabels();
}

initSpeechVersion();

export { formatLangLabel, updateLangLabels, showVersion, initSpeechVersion };
