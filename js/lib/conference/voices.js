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
export function PopulateVoicesOut() {
    let select = _('VoiceOutput');
    if (!select) return;
    AllOutputVoices.length = 0;
    while (select.firstChild) {
        select.removeChild(select.firstChild);
    }
    let voices = window.speechSynthesis.getVoices();
    for (let i = 0; i < voices.length; i++) {
        if (true) {
            let opt = document.createElement('option');
            opt.value = AllOutputVoices.length;
            if (voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
            AllOutputVoices.push(voices[i]);
            opt.textContent = voices[i].lang + ' ' + voices[i].name;
            select.appendChild(opt);
        }
    }
}

export function InitVoices() {
    PopulateVoicesIn();
    PopulateVoicesOut();
    //setLanguageInput(0);
    //setLanguageOutput(0);
}