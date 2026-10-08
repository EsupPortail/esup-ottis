/**
 * seg.js
 * Segmented control "Version du discours" (vue Transcription).
 * VO -> affiche #notebook ; langue choisie -> affiche #output.
 * Le libelle du second segment suit la langue choisie par l'auditeur
 * (select #VoiceOutput du panneau Langue ou pop-up #dialog-choose).
 */
import { _ } from '../dom/selector.js';

/**
 * @summary Formate un code de langue pour l'affichage ('en-GB' -> 'EN-GB')
 * @param {string} lang - Code de langue
 * @returns {string} Libelle affiche dans le segment
 */
function formatLangLabel(lang) {
  return String(lang || '').trim().toUpperCase() || '—';
}

/**
 * @summary Met a jour le libelle du segment traduction et l'attribut lang de l'output
 */
function updateSpeechVersion() {
  const classroomLanguage = _('classroomlanguage');
  const voLabel = _('seg-vo-label');
  if (voLabel) {
    // Libelle du segment VO : code de la langue originale de la conference
    voLabel.textContent = formatLangLabel(
      classroomLanguage && classroomLanguage.value ? classroomLanguage.value : 'VO'
    );
  }
  const voiceOutput = _('VoiceOutput');
  const lang = voiceOutput && voiceOutput.value ? voiceOutput.value : '';
  const label = _('seg-tr-label');
  if (label) label.textContent = formatLangLabel(lang);
  const radio = _('seg-tr');
  if (radio) radio.value = lang;
  const output = _('output');
  if (output) {
    if (lang) output.setAttribute('lang', lang);
    else output.removeAttribute('lang');
  }
}

/**
 * @summary Affiche #notebook (VO) ou #output (traduction) selon le segment coche
 * @param {string} version - 'vo' ou 'tr'
 */
function showVersion(version) {
  const notebook = _('notebook');
  const output = _('output');
  if (notebook) notebook.hidden = version !== 'vo';
  if (output) output.hidden = version !== 'tr';
}

/**
 * @summary Initialise le segmented control (auto au chargement du module)
 */
function initSpeechVersion() {
  const voRadio = _('seg-vo');
  const trRadio = _('seg-tr');
  const voiceOutput = _('VoiceOutput');
  const voiceOutputChoose = _('VoiceOutputchoose');

  if (voRadio) voRadio.addEventListener('change', () => showVersion('vo'));
  if (trRadio) trRadio.addEventListener('change', () => showVersion('tr'));
  if (voiceOutput) voiceOutput.addEventListener('change', updateSpeechVersion);
  if (voiceOutputChoose) voiceOutputChoose.addEventListener('change', updateSpeechVersion);
  const classroomLanguage = _('classroomlanguage');
  if (classroomLanguage) {
    classroomLanguage.addEventListener('change', updateSpeechVersion);
  }

  // Les selects sont peuples de maniere asynchrone (chargement des voix) :
  // observer l'ajout d'options pour rafraichir les libelles des segments.
  [voiceOutput, voiceOutputChoose, classroomLanguage].forEach((select) => {
    if (select) {
      new MutationObserver(updateSpeechVersion).observe(select, { childList: true });
    }
  });

  updateSpeechVersion();
  showVersion(voRadio && voRadio.checked ? 'vo' : 'tr');
}

initSpeechVersion();

export { formatLangLabel, updateSpeechVersion, showVersion, initSpeechVersion };
