import { NBconnectedusers, Tconnectedusers } from './state.js';

import { _ } from '../dom/selector.js';
import { hide, show } from '../dom/visibility.js';
import { addCsrfToken, createFetchRequest } from './network.js';
import { safeSetTimeout } from '../utils/cleanup.js';

import { ROOM_TOKEN } from './compat.js';
import { zoomid, uncloudlink, uncloudhtmllink, targettab, inSlideTab, setLanguageInput } from './config.js';
import { setLanguageSubtitle, PopulateSubtitlesLang, PopulateSpeechLang } from './voices.js';
import { startChatPolling, saveTextQ } from '../chat/main.js';
import { Forward, Backward, SlideForward, SlideBackward, SkipForward, SkipBackward } from './navigation.js';
import { Micro, showET, toggleTextBandDisplay } from './ui.js';
import { togglePdfFullscreen, SlideNotesUncloud, SlideNotesUncloudHtml } from './pdf-viewer.js';
import { exitPdfFullscreen, restoreCanvasStyles, saveCurrentCanvasStyles } from './pdf-fullscreen/core.js';

/** @module conference/config */

export function Lancer() {
  startChatPolling();
  // LaunchStudentReadText();
  // LaunchFeedbackReady();
  // applyConfig();
}

export function Init() {
  if (_('subtitlelanguage')) PopulateSubtitlesLang();
  if (_('speechlanguage')) PopulateSpeechLang();

  /*  _('question').addEventListener('keyup', function (event) {
        if (event.code === 'Enter') {
            saveTextQ(_('question').value);
          event.preventDefault();
          event.stopPropagation();
        }
      },false);
    */
  // resizeSlides();

  document.addEventListener('keyup', function (event) {
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
  }, { capture: true });

  // Gestionnaire pour restaurer les styles à la sortie du plein écran
  // Cela fonctionne pour Échap, bouton, Alt+F4, etc.
  // PDF.js ne peut pas bloquer cet événement navigateur
  function handleFullscreenExit() {
    const isFullscreen = document.fullscreenElement ||
                         document.webkitFullscreenElement ||
                         document.mozFullScreenElement ||
                         document.msFullscreenElement;
    
    if (!isFullscreen) {
      // Mettre à jour le bouton
      const fullscreenBtn = _('pdf-fullscreen-btn');
      if (fullscreenBtn) {
        fullscreenBtn.textContent = 'Fullscreen';
        fullscreenBtn.title = 'Toggle fullscreen mode';
      }
      
      // Restaurer les styles
      restoreCanvasStyles(false);
      toggleTextBandDisplay();
    }
  }

  document.addEventListener('fullscreenchange', handleFullscreenExit);
  document.addEventListener('webkitfullscreenchange', handleFullscreenExit);
  document.addEventListener('mozfullscreenchange', handleFullscreenExit);
  document.addEventListener('MSFullscreenChange', handleFullscreenExit);

  // setTimeout(Micro,1000);
  // setTimeout(Micro,1500);

  /* if (_('embedwooclapon').checked) {
        _('spanembedwooclapon').innerHTML = 'Yes !';
        show('titlewooclap');
    } else {
        _('spanembedwooclapon').innerHTML = 'No.';
        hide('titlewooclap');
    } */
  if (zoomid != '') startZoom(zoomid);
  if (uncloudlink != '') SlideNotesUncloud(uncloudlink);
  if (uncloudhtmllink != '') SlideNotesUncloudHtml(uncloudhtmllink);
  if (targettab > 0) _(`ttab-${targettab}`).click();

  // Adjust a few parameters according to the url arguments
  const base = new URL(window.location.toLocaleString()).searchParams;
  if (base.has('WhichTranslator')) _('whichtranslator').value = base.get('WhichTranslator');
}

export function opentab_url(url) {
  let tabnumber = 1;
  while (tabnumber < 4 && _(`extraurl${tabnumber}`).value != '') tabnumber++;
  _(`extraurl${tabnumber}`).value = url;
  _(`embedET${tabnumber}`).checked = true;
  showET(_(`embedET${tabnumber}`), tabnumber);
}

export function startZoom(zoomid) {
  const lnk = `https://univ-nantes-fr.zoom.us/wc/join/${zoomid}`;
  opentab_url(lnk);
  if (window.confirm('Are you the host for this zoom session ?')) {
    window.open(`https://univ-nantes-fr.zoom.us/wc/${zoomid}/start`);
  }
}

export function setLanguageStr(l) {
  // Try to find the language in the language list
  const s = _('classroomlanguage');
  let finded = -1;
  for (let i = 0; i < s.options.length && finded == -1; i++) {
    if (l.substr(0, 2).toLowerCase() == s.options[i].innerHTML.substr(0, 2).toLowerCase()) finded = i;
  }
  if (finded != -1) {
    s.selectedIndex = finded;
    setLanguageInput(_('classroomlanguage').value);
    Micro();
  }
}

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

export function updateConfig() {
  const newConfig = [];
  newConfig[0] = String.fromCharCode(65 + _('classroomlanguage').selectedIndex);
  newConfig[1] = String.fromCharCode(65 + _('subtitlelanguage').selectedIndex);
  currentconfig = newConfig.join('');
  // Configuration simplifiée : seuls classroomlanguage et subtitlelanguage sont conservés
}

export function applyConfig() {
  const config = window.pageData?.currentconfig;
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
  if (tosend != '') saveTextQ(tosend);
  _('question').value = '';
}

// Expose globally for inline event handlers
if (typeof window !== 'undefined') {
  window.sendclick = sendclick;
}
