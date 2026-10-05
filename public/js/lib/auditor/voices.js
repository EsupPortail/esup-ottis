import { _ } from '../dom/selector.js';
/**
 * Voice management functions for auditor
 */

import { AllOutputVoices, TLangInput } from '../language/state.js';
import { safeSetTimeout } from '../utils/cleanup.js';
import { getLastNotebookVOContent, setLastNotebookVOContent } from '../state/shared.js';

/**
 * @summary Fill the select VoiceOutput field
 * @param {string} selectname - Select element name (default: 'VoiceOutput')
 */
function PopulateVoicesOut(selectname = 'VoiceOutput') {
  const select = _(selectname);
  if (!select) return;

  AllOutputVoices = [];
  while (select.firstChild) {
    select.removeChild(select.firstChild);
  }
  const optgroup = document.createElement('optgroup');
  optgroup.label = 'Voice and text translation';
  const voices = speechSynthesis.getVoices();
  let maxvoices = voices.length;
  // Voices in english first
  for (let i = 0; i < voices.length; i++) {
    if (voices[i].lang && typeof voices[i].lang === 'string' && voices[i].lang.substr(0, 2).toLowerCase() == 'en') { // (voices[i].localService) {
      const opt = document.createElement('option');
      opt.value = voices[i].lang; // Use voice.lang as value instead of numeric index
      if (voices[i].lang && typeof voices[i].lang === 'string' && voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
      voices[i].textonly = false;
      AllOutputVoices.push(voices[i]);
      opt.textContent = `${voices[i].lang} ${voices[i].name}${(voices[i].localService) ? '' : ' [external service]'}`;
      optgroup.appendChild(opt);
    }
  }

  for (let i = 0; i < voices.length; i++) {
    if (voices[i].lang && typeof voices[i].lang === 'string' && voices[i].lang.substr(0, 2).toLowerCase() != 'en') { // (voices[i].localService) {
      const opt = document.createElement('option');
      opt.value = voices[i].lang; // Use voice.lang as value instead of numeric index
      if (voices[i].lang && typeof voices[i].lang === 'string' && voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
      voices[i].textonly = false;
      AllOutputVoices.push(voices[i]);
      opt.textContent = `${voices[i].lang} ${voices[i].name}${(voices[i].localService) ? '' : ' [external service]'}`;
      optgroup.appendChild(opt);
    }
  }
  select.appendChild(optgroup);
  const optgroup2 = document.createElement('optgroup');
  optgroup2.label = 'Text translation only';
  if (typeof supportedlanguages !== 'undefined' && Array.isArray(supportedlanguages)) {
    for (let i = 0; i < supportedlanguages.length; i++) {
      if (supportedlanguages[i] && supportedlanguages[i].code) {
        const lang = supportedlanguages[i].code;
        const opt = document.createElement('option');
        opt.value = lang; // Use language code as value instead of numeric index
        AllOutputVoices.push({
          id: lang,
          lang,
          textonly: true,
        });

        opt.textContent = `${lang} ${supportedlanguages[i].name_fr || ''} ${supportedlanguages[i].name_vo || ''}`;
        optgroup2.appendChild(opt);
      }
    }
  }
  select.appendChild(optgroup2);
}

/**
 * @summary Fill the select VoiceOutput field for the subtitle application
 * @param {string} selectname - Select element name (default: 'VoiceOutput')
 */
function PopulateVoicesOutSub(selectname = 'VoiceOutput') {
  const select = _(selectname);
  if (!select) return;

  AllOutputVoices = [];
  // while (select.firstChild) {
  //     select.removeChild(select.firstChild);
  // }

  const optgroup2 = document.createElement('optgroup');
  optgroup2.label = 'Text translation only';
  const voices = speechSynthesis.getVoices();
  let maxvoices = voices.length;
  if (typeof supportedlanguages !== 'undefined' && Array.isArray(supportedlanguages)) {
    for (let i = 0; i < supportedlanguages.length; i++) {
      if (supportedlanguages[i] && supportedlanguages[i].code) {
        const lang = supportedlanguages[i].code; // .substr(0,5);
        const opt = document.createElement('option');
        opt.value = lang; // Use language code as value instead of numeric index
        AllOutputVoices.push({
          id: lang,
          lang,
          textonly: true,
        });

        opt.textContent = `${lang} ${supportedlanguages[i].name_fr || ''} ${supportedlanguages[i].name_vo || ''}`;
        optgroup2.appendChild(opt);
      }
    }
  }
  select.appendChild(optgroup2);
}

/**
 * @summary Fill the select VoiceInput field
 */
function PopulateVoicesIn() {
  const select = _('VoiceInput');
  const select2 = _('classroomlanguage');
  if (!select || !select2) return;

  // let voices = window.speechSynthesis.getVoices();
  TLangInput = [];

  for (let i = 0; i < supportedlanguages.length; i++) {
    const lang = supportedlanguages[i].code; // .substr(0,5);
    if (TLangInput.indexOf(lang) < 0) {
      const opt = document.createElement('option');
      opt.value = lang; // Use language code as value instead of numeric index
      TLangInput.push(lang);
      opt.textContent = `${lang} ${supportedlanguages[i].name_fr} ${supportedlanguages[i].name_vo}`;
      select.appendChild(opt);
      const opt2 = document.createElement('option');
      opt2.value = lang; // Use language code as value instead of numeric index
      opt2.textContent = lang;
      select2.appendChild(opt2);
    }
  }
}

/**
 * @summary Fill the select VoiceInput field for the subtitle application
 */
function PopulateVoicesInSub() {
  const select = _('classroomlanguage');
  if (!select) return;

  // let select = _('VoiceInput');
  // let voices = window.speechSynthesis.getVoices();
  TLangInput = [];

  for (let i = 0; i < supportedlanguages.length; i++) {
    const lang = supportedlanguages[i].code; // .substr(0,5);
    if (TLangInput.indexOf(lang) < 0) {
      const opt = document.createElement('option');
      opt.value = lang; // Use language code as value instead of numeric index
      TLangInput.push(lang);
      opt.textContent = `${lang} ${supportedlanguages[i].name_fr} ${supportedlanguages[i].name_vo}`;
      select.appendChild(opt);
      // let opt2 = document.createElement("option");
      // opt2.value = TLangInput.length;
      // opt2.innerHTML = lang;
      // select2.appendChild(opt2);
    }
  }
}

/**
 * @summary InitVoices is executed when the HTML page is loaded.
 */
function InitVoices() {
  PopulateVoicesOut();
  PopulateVoicesOut('VoiceOutputchoose');
  // Ouvrir le pop-up de sélection de langue maintenant que les voix sont chargées
  if (typeof openLanguageDialog === 'function') {
    openLanguageDialog();
  }
  // Ne PAS définir la langue ici - cela sera fait quand l'utilisateur valide le popup
  // setLanguageOutputAndRetranslate(_("VoiceOutput").value);
}

/**
 * @summary Load the voices
 */
function LoadVoices() {
  InitVoices();
  // Note: Lancer() is now called after language selection via startAuditorPolling()
  // to prevent polling before language is selected
}

/**
 * @summary Wait for the SpeechSynthesis Voices to be available
 */
function loadVoicesWhenAvailable() {
  const Tvoices = speechSynthesis.getVoices();

  if (Tvoices.length !== 0) {
    LoadVoices();
  } else {
    safeSetTimeout(() => {
      loadVoicesWhenAvailable();
    }, 10);
  }
}
