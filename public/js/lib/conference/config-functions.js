import { _ } from '../dom/selector.js';
import '../utils/cleanup.js';
import { getCurrentConfig, setCurrentConfig } from '../state/conference.js';

import { setLanguageInput } from './config.js';
import { setLanguageSubtitle, PopulateSubtitlesLang, PopulateSpeechLang } from './voices.js';
import { startChatPolling, saveTextQ } from '../chat/main.js';
import { setupChatInput } from '../chat/input.js';
import { Forward, Backward, SlideForward, SlideBackward } from './navigation.js';
import { Micro, showET, toggleTextBandDisplay } from './ui.js';

// URL parameters - moved from config.js to break circular dependency
let zoomid = '';
let targettab = 0;

/** @module conference/config */

export function Lancer() {
  startChatPolling();
  // LaunchStudentReadText();
  // LaunchFeedbackReady();
  // applyConfig();
}

export function Init() {
  // Parse URL parameters for zoomid, uncloudlink, etc.
  const urlParams = new URL(window.location.toLocaleString()).searchParams;
  if (urlParams.has('ZOOMID')) zoomid = urlParams.get('ZOOMID');
  if (urlParams.has('tab')) targettab = parseInt(urlParams.get('tab'), 10) || 0;
  if (urlParams.has('WhichTranslator')) {
    const whichtranslatorEl = _('whichtranslator');
    if (whichtranslatorEl) {
      whichtranslatorEl.value = urlParams.get('WhichTranslator');
    }
  }

  if (_('subtitlelanguage')) PopulateSubtitlesLang();
  if (_('speechlanguage')) PopulateSpeechLang();

  // Setup chat input Enter key handler
  setupChatInput();

  // resizeSlides();

  document.addEventListener(
    'keyup',
    (event) => {
      // Gestion des flèches pour la présentation (toujours active)
      if (event.code === 'ArrowDown') {
        Forward();
        event.preventDefault();
        event.stopPropagation();
        return;
      }
      if (event.code === 'ArrowUp') {
        Backward();
        event.preventDefault();
        event.stopPropagation();
        return;
      }
      if (event.code === 'ArrowRight') {
        SlideForward();
        event.preventDefault();
        event.stopPropagation();
        return;
      }
      if (event.code === 'ArrowLeft') {
        SlideBackward();
        event.preventDefault();
        event.stopPropagation();
        return;
      }
      if (event.code === 'PageDown') {
        SlideForward();
        event.preventDefault();
        event.stopPropagation();
        return;
      }
      if (event.code === 'PageUp') {
        SlideBackward();
        event.preventDefault();
        event.stopPropagation();
      }
    },
    { capture: true }
  );

  // La sortie de plein écran (Échap, bouton, Alt+F4) est gérée par
  // handleFullscreenStateChange -> ensureFullscreenExited (pdf-fullscreen) :
  // le libellé du bouton passe par setFullscreenButtonLabel (i18n) et la
  // restauration des styles par restoreCanvasStyles. L'ancien handler local
  // écrasait le libellé avec une chaîne anglaise en dur.

  // setTimeout(Micro,1000);
  // setTimeout(Micro,1500);

  /* if (_('embedwooclapon').checked) {
        _('spanembedwooclapon').innerHTML = 'Yes !';
        show('titlewooclap');
    } else {
        _('spanembedwooclapon').innerHTML = 'No.';
        hide('titlewooclap');
    } */
  if (zoomid !== '') startZoom(zoomid);
  if (targettab > 0) _(`ttab-${targettab}`).click();
}

export function opentab_url(url) {
  let tabnumber = 1;
  while (tabnumber < 4 && _(`extraurl${tabnumber}`).value !== '') tabnumber++;
  _(`extraurl${tabnumber}`).value = url;
  _(`embedET${tabnumber}`).checked = true;
  showET(_(`embedET${tabnumber}`), tabnumber);
}

export function startZoom(zoomidToJoin) {
  const lnk = `https://univ-nantes-fr.zoom.us/wc/join/${zoomidToJoin}`;
  opentab_url(lnk);
  if (window.confirm('Are you the host for this zoom session ?')) {
    window.open(`https://univ-nantes-fr.zoom.us/wc/${zoomidToJoin}/start`);
  }
}

export function setLanguageStr(l) {
  // Try to find the language in the language list
  const s = _('classroomlanguage');
  let finded = -1;
  for (let i = 0; i < s.options.length && finded === -1; i++) {
    if (l.substr(0, 2).toLowerCase() === s.options[i].innerHTML.substr(0, 2).toLowerCase()) {
      finded = i;
    }
  }
  if (finded !== -1) {
    s.selectedIndex = finded;
    setLanguageInput(_('classroomlanguage').value);
    Micro();
  }
}

export function updateConfig() {
  const newConfig = [];
  newConfig[0] = String.fromCharCode(65 + _('classroomlanguage').selectedIndex);
  newConfig[1] = String.fromCharCode(65 + _('subtitlelanguage').selectedIndex);
  setCurrentConfig(newConfig.join(''));
  // Configuration simplifiée : seuls classroomlanguage et subtitlelanguage sont conservés
}

export function applyConfig() {
  const config = getCurrentConfig();
  if (!config || config.length < 2) return; // Seuls les 2 premiers caractères (langues) sont utilisés

  // classroomlanguage
  const classroomLangEl = _('classroomlanguage');
  if (classroomLangEl) {
    classroomLangEl.selectedIndex = 1 * (config.charCodeAt(0) - 65);
  }
  // setLanguageInput(_('classroomlanguage').value);

  // subtitlelanguage
  const subtitleLangEl = _('subtitlelanguage');
  if (subtitleLangEl) {
    subtitleLangEl.selectedIndex = 1 * (config.charCodeAt(1) - 65);
    setLanguageSubtitle(subtitleLangEl.value);
  }
  // Les autres paramètres de config ne sont plus utilisés (éléments supprimés)

  // Initialiser l'affichage de textBand
  if (_('usetextband')) {
    toggleTextBandDisplay();
  }
}

export function sendclick() {
  const tosend = _('question').value.replaceAll('\n', '').trim();
  // alert("Will send : ("+tosend+")");
  if (tosend !== '') saveTextQ(tosend);
  _('question').value = '';
}

// Expose globally for inline event handlers - removed, use ES module imports instead
