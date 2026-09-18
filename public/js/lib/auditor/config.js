/**
 * Configuration functions for auditor
 */

import { _ } from '../dom/selector.js';
import { hide, show } from '../dom/visibility.js';
import { ChangeColor, ChangeOpacity } from '../dom/styles.js';
import { setLanguageInput, setLanguageOutput } from '../language/management.js';
import { setBuiltinVoice, setBuiltinLanguage } from '../state/auditor.js';
import { showBanner, hideBanner } from './display.js';

/**
 * @summary Apply a configuration (at startup) for the subtitle application
 */
export function applyConfigSub() {
  // Get config from window.pageData or fallback to global variable
  const config = (typeof window !== 'undefined' && window.pageData && window.pageData.currentconfig) 
    ? window.pageData.currentconfig 
    : (typeof currentconfig !== 'undefined' ? currentconfig : '');

  if (config.length != 'ABYANACNNCNNNYYYYN'.length) return;
  // classroomlanguage
  _('classroomlanguage').selectedIndex = 1 * (config.charCodeAt(0) - 65);
  const classroomLangValue = _('classroomlanguage').value;
  setLanguageInput(classroomLangValue);
  setBuiltinLanguage(classroomLangValue);

  // subtitlelanguage
  _('VoiceOutput').selectedIndex = 1 * (config.charCodeAt(1) - 65);
  const voiceOutputValue = _('VoiceOutput').value;
  setLanguageOutput(voiceOutputValue);
  setBuiltinVoice({ lang: voiceOutputValue });

  // subtitlebaron
  if (config.charAt(2) == 'Y') {
    _('titre').style.visibility = 'visible';
  } else {
    _('titre').style.visibility = 'hidden';
  }
  // subtitlepartialbaron
  if ((config.charAt(4) == 'Y')) {
    _('VOBanner').style.visibility = 'visible';
    showBanner('VOBanner');
  } else {
    _('VOBanner').style.visibility = 'hidden';
    hideBanner('VOBanner');
  }
  // ColorTitre
  const colorTitreEl = _('ColorTitre');
  if (colorTitreEl) {
    colorTitreEl.selectedIndex = 1 * (config.charCodeAt(3) - 65);
    ChangeColor('titre', colorTitreEl.value);
    ChangeColor('fixed-title', colorTitreEl.value);
  } else {
    ChangeColor('titre', '0,0,255|0,0,255');
    ChangeColor('fixed-title', '0,0,255|0,0,255');
  }
  // ColorPartialTitre
  _('ColorPartialTitre').selectedIndex = 1 * (config.charCodeAt(5) - 65);
  ChangeColor('VOBanner', _('ColorPartialTitre').value);
  // selectfontsize
  _('selectfontsize').selectedIndex = 1 * (config.charCodeAt(6) - 65);
  _('titre').style.fontSize = _('selectfontsize').value;
  _('fixed-title').style.fontSize = _('selectfontsize').value;
  _('VOBanner').style.fontSize = _('selectfontsize').value;
  // selectopacity
  const selectOpacityEl = _('selectopacity');
  if (selectOpacityEl) {
    selectOpacityEl.selectedIndex = 1 * (config.charCodeAt(9) - 65);
    ChangeOpacity('titre', selectOpacityEl.value);
    ChangeOpacity('fixed-title', selectOpacityEl.value);
    ChangeOpacity('VOBanner', selectOpacityEl.value);
  } else {
    ChangeOpacity('titre', '0.5');
    ChangeOpacity('fixed-title', '0.5');
    ChangeOpacity('VOBanner', '0.5');
  }

  // _('synthesison').checked=true;

  // Micro
  // _('microon').checked = (config.charAt(13) == "Y");
  // _('spanmicroon').innerHTML = (config.charAt(13) == "Y") ? "Yes !" : "No.";
  // if (_('microon').checked) {
  //     show('micon');
  // } else {
  //     hide('micon');
  // }

  // updateConfig();
}
