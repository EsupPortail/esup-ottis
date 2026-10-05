import {
  getBuiltinVoice,
  getBuiltinLanguage,
  setBuiltinVoice,
  setBuiltinLanguage,
} from '../state/auditor.js';
import {
  getSubtitleLanguage,
  setSubtitleLanguage,
} from '../state/conference.js';
import { AllOutputVoices, TLangInput } from '../language/state.js';
import { _ } from '../dom/selector.js';
import { safeSetTimeout } from '../utils/cleanup.js';
import { getSupportedLanguages } from '../state/shared.js';

/** @module conference/voices */

export function PopulateVoicesIn() {
  const select = _('classroomlanguage');
  if (!select) return;

  TLangInput.length = 0;

  // Get supported languages from store
  const supportedLanguages = getSupportedLanguages();
  if (!supportedLanguages) {
    // Set default language to en-US
    setBuiltinLanguage('en-US');
    return;
  }

  // supportedLanguages can be either an array or an object (from pageData)
  const languagesArray = Array.isArray(supportedLanguages) 
    ? supportedLanguages 
    : Object.entries(supportedLanguages);
  
  if (languagesArray.length === 0) {
    setBuiltinLanguage('en-US');
    return;
  }

  languagesArray.forEach((langEntry, index) => {
    // langEntry can be either {code, name_fr, name_vo} or [code, lang]
    const code = Array.isArray(langEntry) ? langEntry[0] : langEntry.code;
    const lang = Array.isArray(langEntry) ? langEntry[1] : langEntry;
    
    if (TLangInput.indexOf(code) === -1) {
      const opt = document.createElement('option');
      opt.value = code;

      if (index === 0) {
        opt.selected = true;
        setBuiltinLanguage(code);
      }

      TLangInput.push(code);
      opt.textContent = `${code} ${lang.name_fr} ${lang.name_vo}`;
      select.appendChild(opt);
    }
  });
}

export function PopulateSubtitlesLang() {
  if (!_('subtitlelanguage')) return;
  const select = _('subtitlelanguage');
  const supportedLanguages = getSupportedLanguages();
  
  if (!supportedLanguages) {
    return;
  }
  
  // supportedLanguages can be either an array or an object (from pageData)
  const languagesArray = Array.isArray(supportedLanguages) 
    ? supportedLanguages 
    : Object.entries(supportedLanguages);
  
  languagesArray.forEach((langEntry, index) => {
    const code = Array.isArray(langEntry) ? langEntry[0] : langEntry.code;
    const lang = Array.isArray(langEntry) ? langEntry[1] : langEntry;
    
    const opt = document.createElement('option');
    opt.value = code;

    if (index === 0) {
      opt.selected = true;
      setSubtitleLanguage(code);
    }

    opt.textContent = `${code} ${lang.name_fr} ${lang.name_vo}`;
    select.appendChild(opt);
  });
}

export function PopulateSpeechLang() {
  if (!_('speechlanguage')) return;
  const select = _('speechlanguage');
  const supportedLanguages = getSupportedLanguages();
  
  if (!supportedLanguages) {
    return;
  }
  
  // supportedLanguages can be either an array or an object (from pageData)
  const languagesArray = Array.isArray(supportedLanguages) 
    ? supportedLanguages 
    : Object.entries(supportedLanguages);
  
  languagesArray.forEach((langEntry) => {
    const code = Array.isArray(langEntry) ? langEntry[0] : langEntry.code;
    const lang = Array.isArray(langEntry) ? langEntry[1] : langEntry;
    const opt = document.createElement('option');
    opt.value = code;
    opt.textContent = `${code} ${lang.name_fr} ${lang.name_vo}`;
    select.appendChild(opt);
  });
}

export function setLanguageSubtitle(i) {
  setSubtitleLanguage(i);
}

export function setLanguageSpeech(i) {
  setBuiltinLanguage(i);
}

export function Parle(txt) {
  const discours = new SpeechSynthesisUtterance(txt);
  const bv = getBuiltinVoice();
  discours.voice = bv;
  discours.lang = bv ? bv.lang : '';
  window.speechSynthesis.speak(discours);
  discours.onend = ready;
  discours.onerror = ready;
}

/**
 * @summary Fill the select VoiceOutput field
 */
export function PopulateVoicesOut(selectname = 'VoiceOutput') {
  const select = _(selectname);
  if (!select) {
    return;
  }
  AllOutputVoices.length = 0;
  while (select.firstChild) {
    select.removeChild(select.firstChild);
  }
  const optgroup = document.createElement('optgroup');
  optgroup.label = 'Voice and text translation';
  const voices = window.speechSynthesis.getVoices();
  let englishVoiceFound = false;
  let maxvoices = voices.length;
  for (let i = 0; i < voices.length; i++) {
    if (true) {
      const opt = document.createElement('option');
      // Use voice.lang as the value instead of numeric index
      const voiceId = voices[i].lang;
      opt.value = voiceId;
      // Sélectionner en-US par défaut (sans Male/Female), sinon la langue du navigateur
      const langCode = voices[i].lang.toLowerCase();
      const voiceName = voices[i].name.toLowerCase();
      if (langCode.startsWith('en-us') && !voiceName.includes('male') && !voiceName.includes('female')) {
        opt.selected = true;
        englishVoiceFound = true;
      } else if (!englishVoiceFound && langCode.startsWith('en') && !voiceName.includes('male') && !voiceName.includes('female')) {
        opt.selected = true;
        englishVoiceFound = true;
      } else if (!englishVoiceFound && langCode.substr(0, 2) == navigator.language.substr(0, 2)) {
        opt.selected = true;
      }
      voices[i].textonly = false;
      AllOutputVoices.push(voices[i]);
      opt.textContent = `${voices[i].lang} ${voices[i].name}${(voices[i].localService) ? '' : ' [external service]'}`;
      optgroup.appendChild(opt);
    }
  }
  select.appendChild(optgroup);
  const optgroup2 = document.createElement('optgroup');
  optgroup2.label = 'Text translation only';
  const supportedLanguagesRaw = getSupportedLanguages();
  
  if (!supportedLanguagesRaw) {
    select.appendChild(optgroup2);
    return;
  }
  
  // supportedLanguages can be either an array or an object (from pageData)
  const supportedlanguages = Array.isArray(supportedLanguagesRaw) 
    ? supportedLanguagesRaw 
    : Object.values(supportedLanguagesRaw);
  
  // Trouver l'index de en-US dans supportedlanguages
  let englishLangIndex = -1;
  for (let i = 0; i < supportedlanguages.length; i++) {
    if (supportedlanguages[i].code.toLowerCase() === 'en-us') {
      englishLangIndex = i;
      break;
    }
  }
  for (let i = 0; i < supportedlanguages.length; i++) {
    const lang = supportedlanguages[i].code;
    const opt = document.createElement('option');
    // Use language code as the value instead of numeric index
    opt.value = lang;
    // Sélectionner en-US par défaut dans Text translation only
    if (!englishVoiceFound && i === englishLangIndex) {
      opt.selected = true;
      englishVoiceFound = true; // Marquer pour éviter double sélection
    }
    AllOutputVoices.push({
      id: lang,
      lang,
      textonly: true,
    });
    opt.textContent = `${lang} ${supportedlanguages[i].name_fr} ${supportedlanguages[i].name_vo}`;
    optgroup2.appendChild(opt);
  }
  select.appendChild(optgroup2);
}

export function InitVoices() {
  PopulateVoicesIn();
  PopulateVoicesOut();
  // Peuple aussi le select du dialog de choix de langue
  PopulateVoicesOut('VoiceOutputchoose');
  // Populate subtitle language select
  PopulateSubtitlesLang();
  // setLanguageInput(0);
  // setLanguageOutput(0);
}

export function loadVoicesWhenAvailable(attempt = 0) {
  const maxAttempts = 100; // Prevent infinite loops
  const Tvoices = window.speechSynthesis.getVoices();

  if (Tvoices.length !== 0) {
    InitVoices();
    if (typeof Lancer === 'function') {
      Lancer();
    }
    // Ouvrir le pop-up de sélection de langue pour auditor
    if (typeof window.openLanguageDialog === 'function') {
      window.openLanguageDialog();
    }
  } else if (attempt < maxAttempts) {
    safeSetTimeout(() => {
      loadVoicesWhenAvailable(attempt + 1);
    }, 10);
  } else {
    // Fallback: populate with static voices if speech synthesis voices not available
    InitVoices();
    if (typeof window.openLanguageDialog === 'function') {
      window.openLanguageDialog();
    }
  }
}
