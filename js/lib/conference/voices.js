import {
    builtin_voice,
    builtin_language,
    subtitle_language,
    TLangInput,
    AllOutputVoices,
    setBuiltinVoice,
    setBuiltinLanguage,
    setSubtitleLanguage
} from './state.js';
import { _ } from './dom-helpers.js';



/** @module conference/voices */



export function PopulateVoicesIn() {
    if (TLangInput.length == 0) {
        let select = _('classroomlanguage');
        TLangInput.length = 0;
        
        // Ensure pageData.supportedlanguages exists
        if (!window.pageData || !window.pageData.supportedlanguages) {
            console.warn('pageData.supportedlanguages not available');
            // Set default language to en-US
            setBuiltinLanguage('en-US');
            return;
        }
        
        Object.entries(window.pageData.supportedlanguages).forEach(([code, lang], index) => {
            if (TLangInput.indexOf(code) === -1) {
                let opt = document.createElement('option');
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
}

export function PopulateSubtitlesLang() {

    let select = _('subtitlelanguage');
    Object.entries(window.pageData.supportedlanguages).forEach(([code, lang], index) => {
            if (TLangInput.indexOf(code) === -1) {
                let opt = document.createElement('option');
                opt.value = code;

                if (index === 0) {
                    opt.selected = true;
                    setSubtitleLanguage(code);
                }

                opt.textContent = `${code} ${lang.name_fr} ${lang.name_vo}`;
                select.appendChild(opt);
            }
        });
}

export function PopulateSpeechLang() {
    let select = _('speechlanguage');
    for (let i = 0; i < window.pageData.supportedlanguages.length; i++) {
        let lang = window.pageData.supportedlanguages[i].code; //.substr(0,5);
        let opt = document.createElement('option');
        opt.value = lang;
        opt.textContent = lang + ' ' + window.pageData.supportedlanguages[i].name_fr + ' ' + window.pageData.supportedlanguages[i].name_vo;
        select.appendChild(opt);
    }
}

export function setLanguageSubtitle(i) {
    setSubtitleLanguage(i);

}

export function setLanguageSpeech(i) {
    setBuiltinLanguage(i);

}

export function Parle(txt) {
    let discours = new SpeechSynthesisUtterance(txt);
    discours.voice = builtin_voice;
    discours.lang = builtin_voice ? builtin_voice.lang : '';
    window.speechSynthesis.speak(discours);
    discours.onend = ready;
    discours.onerror = ready;
}

/**
 * @summary Fill the select VoiceOutput field
 */
export function PopulateVoicesOut(selectname = 'VoiceOutput') {
    let select = _(selectname);
    if (!select) return;
    AllOutputVoices.length = 0;
    while (select.firstChild) {
        select.removeChild(select.firstChild);
    }
    let optgroup = document.createElement("optgroup");
    optgroup.label = "Voice and text translation";
    let voices = window.speechSynthesis.getVoices();
    let englishVoiceFound = false;
    for (let i = 0; i < voices.length; i++) {
        if (true) {
            let opt = document.createElement('option');
            opt.value = AllOutputVoices.length;
            // Sélectionner en-US par défaut (sans Male/Female), sinon la langue du navigateur
            let langCode = voices[i].lang.toLowerCase();
            let voiceName = voices[i].name.toLowerCase();
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
            opt.textContent = voices[i].lang + " " + voices[i].name + ((voices[i].localService) ? "" : " [external service]");
            optgroup.appendChild(opt);
        }
    }
    select.appendChild(optgroup);
    let maxvoices = voices.length;
    let optgroup2 = document.createElement("optgroup");
    optgroup2.label = "Text translation only";
    let supportedlanguages = window.pageData ? window.pageData.supportedlanguages : [];
    // Trouver l'index de en-US dans supportedlanguages
    let englishLangIndex = -1;
    for (let i = 0; i < supportedlanguages.length; i++) {
        if (supportedlanguages[i].code.toLowerCase() === 'en-us') {
            englishLangIndex = i;
            break;
        }
    }
    for (let i = 0; i < supportedlanguages.length; i++) {
        let lang = supportedlanguages[i].code;
        let opt = document.createElement('option');
        opt.value = maxvoices + i;
        // Sélectionner en-US par défaut dans Text translation only
        if (!englishVoiceFound && i === englishLangIndex) {
            opt.selected = true;
            englishVoiceFound = true; // Marquer pour éviter double sélection
        }
        AllOutputVoices.push({
            "lang": lang,
            "textonly": true
        });
        opt.textContent = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
        optgroup2.appendChild(opt);
    }
    select.appendChild(optgroup2);
}

export function InitVoices() {
    PopulateVoicesIn();
    PopulateVoicesOut();
    // Peuple aussi le select du dialog de choix de langue
    PopulateVoicesOut('VoiceOutputchoose');
    //setLanguageInput(0);
    //setLanguageOutput(0);
}

export function loadVoicesWhenAvailable() {
    let Tvoices = window.speechSynthesis.getVoices();

    if (Tvoices.length !== 0) {
        InitVoices();
        if (typeof Lancer === 'function') {
            Lancer();
        }
        // Ouvrir le pop-up de sélection de langue pour auditor
        if (typeof window.openLanguageDialog === 'function') {
            window.openLanguageDialog();
        }
    } else {
        setTimeout(function() {
            loadVoicesWhenAvailable();
        }, 10);
    }
}