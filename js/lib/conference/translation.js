import { StudentToSay, StudentToTranslate2, Studentcompletespeechactive, Studentcurrentdiscours, Studentgotolive, builtin_language, builtin_voice, currentdiscours, lastPartialTranslation, lastSentenceTranslated, readytotalk, subtitle_language, setLastSentenceTranslated, LiveTranslationNum, LiveTranslationFreq, setLiveTranslationNum } from './state.js';

import { _ } from './dom-helpers.js';
import { addCsrfToken, createXhrRequest } from './network.js';
import { Ecrire, EcrireNB, EcrireNBonly, EcrireNBonlyVO, EcrirePartial, saveText, saveTextWithTranslation } from './notebook.js';
import { Parle } from './voices.js';

// Local state variables for this module
let lastSentenceVO = '';
let totalTranslated = 0;

/** @module conference/translation */



export function StudentParleThen(txt, f) {
    //EcrireFeedback(txt);
    setTimeout(f, 100 * txt.length);
}

export function StudentTranslate2(ch, l, linput, lineid) {

    lineid='DIRECT';
    
    let formData = new FormData();
    formData.append('texte', ch);
    formData.append('lang', l);
    formData.append('langinput', linput);
    formData.append('ROOMID', _('classroomid').value || '');
    formData.append('lineid', lineid);
    
    // Ajouter whichtranslator (apikey gere via config.php)
    let whichtranslator = _('whichtranslator').value;
    if (whichtranslator) formData.append('whichtranslator', whichtranslator);
    
    // Ajouter le token CSRF
    addCsrfToken(formData);
    
    let xhr = createXhrRequest('POST', 'Translate.php', {
        onSuccess: function(xhr) {
            StudentCompleteTranslation2();
        },
        data: formData,
        timeout: 10000,
        context: 'StudentTranslate2'
    });
    xhr.send(formData);
}

export function StudentCompleteTranslation2() {

    if (StudentToTranslate2.length > 0) {
        let L = StudentToTranslate2[0];
        StudentToTranslate2.splice(0, 1);
        if (subtitle_language.substr(0, 2).toLowerCase() != L[1].substr(0, 2)) {
            // Ecrire("Push : "+L[2]);
            StudentTranslate2(L[2], subtitle_language.substr(0, 2).toLowerCase(), L[1].substr(0, 2), L[0]);
        } else StudentCompleteTranslation2();
    } else setTimeout(StudentCompleteTranslation2, 10);
}

export function StudentCompleteSpeech() {
    let nextdiscours = Studentcurrentdiscours;
    for (let i = 1; i < 1000; i++) {
        if (StudentToSay['' + (Studentcurrentdiscours + i)]) {
            nextdiscours = Studentcurrentdiscours + i;
            break;
        }
    }
    if (Studentgotolive) {
        //console.log('Restart Speech');
        //console.log('Discours : '+currentdiscours+'   '+nextdiscours)
        for (let i = 1000; i > 0; i--) {
            if (StudentToSay['' + (Studentcurrentdiscours + i)]) {
                nextdiscours = Studentcurrentdiscours + i;
                break;
            }
            //console.log('Discours next : '+currentdiscours+'   '+nextdiscours)
            Studentgotolive = false;
        }
    }
    //Ecrire(currentdiscours+'   '+nextdiscours);
    if (nextdiscours > Studentcurrentdiscours) {
        let L = StudentToSay['' + nextdiscours];
        L = L.replace('&nbsp;', ' ');
        StudentParleThen(L, StudentCompleteSpeech);
        Studentcurrentdiscours = nextdiscours;
    } else Studentcompletespeechactive = false;

}

export function Traduction(ch) {
    let recognitionon = _('recognitionon').checked;
    if (recognitionon) {
        EcrireNBonlyVO(ch);

        // Check if subtitle language is set and different from source
        if (typeof subtitle_language !== 'undefined' && subtitle_language && 
            builtin_language && builtin_language !== subtitle_language) {
            Translate(ch, subtitle_language.substr(0, 2), builtin_language.substr(0, 2), 
                function(translatedText) {
                    if (translatedText && translatedText !== '') {
                        EcrireNBonly(translatedText);
                        saveTextWithTranslation(ch, translatedText);
                    } else {
                        saveText(ch);
                    }
                }
            );
        } else {
            // No subtitle language set or same as source, save only source text
            saveText(ch);
        }
/*        let liveon = _('liveon').checked;
        if (liveon) {
            if (readytotalk) {
                setNotReadyToTalk();
                Ecrire('Input : ' + ch);
                if (builtin_voice.lang.substr(0, 2) != builtin_language.substr(0, 2)){
                    Translate(ch, builtin_voice.lang.substr(0, 2), builtin_language.substr(0, 2));
                }else {
                    Parle(ch);
                    Ecrire("Output : " + ch);
                }
            }
        }*/
    }
}

export function TranslatorInfo() {
    let res='';
    let whichtranslator=_('whichtranslator').value;
    if (whichtranslator != '') res+='&whichtranslator='+whichtranslator;
    return res;
}

export function Translate(ch, l, linput, callback) {
    // Use TranslateThen which handles encoding and callbacks

    TranslateThen(ch, l, linput, function(translatedText) {
        if (typeof translatedText === 'string') {
            // Only speak if voice is available
            if (typeof builtin_voice !== 'undefined' && builtin_voice) {
                Parle(translatedText);
            }
            setLastSentenceTranslated(translatedText);
            Ecrire('' + translatedText);
            if (callback) callback(translatedText);
        } else {
            console.error('Translate: invalid translatedText', translatedText);
            if (callback) callback('');
        }
    }, false);
}

export function TranslateThen(ch, l, linput, f, forcelibre = false) {


    let formData = new FormData();
    formData.append('texte', ch);
    formData.append('lang', l);
    formData.append('langinput', linput);
    formData.append('ROOMID', _('classroomid').value || '');
    formData.append('lineid', 'DIRECT');

    if (forcelibre) {
        formData.append('forcelibre', 'true');
    }

    // Ajouter whichtranslator (apikey gere via config.php)
    let whichtranslator = _('whichtranslator').value;
    if (whichtranslator) formData.append('whichtranslator', whichtranslator);

    let d_begin = Date.now();
    addCsrfToken(formData);

    let xhr = createXhrRequest('POST', 'Translate.php', {
        onSuccess: function(xhr) {
            let d_end = Date.now();
            try {
                let response = JSON.parse(xhr.responseText);

                if (response && !response.error && response.data && response.data.translations && 
                    response.data.translations[0] && response.data.translations[0].text) {
                    setLastSentenceTranslated(response.data.translations[0].text);
                    f(response.data.translations[0].text);
                } else {
                    console.error('Erreur de traduction. Réponse :', response);
                    f('---- Translation ERROR ----');
                }
            } catch (error) {
                console.error('Erreur de parsing JSON :', error, 'Réponse brute :', xhr.responseText);
                f('---- JSON Parse ERROR ----');
            }
        },
        onError: function(xhr, context) {
            console.error('Erreur HTTP :', xhr.status, xhr.statusText);
            f(`---- HTTP ERROR ${xhr.status} ----`);
        },
        data: formData,
        timeout: 10000,
        context: 'TranslateThen'
    });
    xhr.onerror = function() {
        console.error('Erreur réseau dans TranslateThen.');
        f('---- Network ERROR ----');
    };
    xhr.send(formData);
}

export function EcrireTranslatePartial(ch, callback, forceTranslate = false) {
    let linput = builtin_language.substr(0, 2).toLowerCase();
    if (ch.indexOf(':|:') >= 0) { // If there are a language indication
        let Tch = ch.split(':|:');
        linput = Tch[0].substr(0, 2).toLowerCase();
        ch = Tch[1];
    }
    let loutput = subtitle_language.substr(0, 2).toLowerCase();
    if (linput == loutput) {
        EcrirePartial(ch);
        if (callback) callback(ch);
    } else {
            if (forceTranslate || LiveTranslationNum % LiveTranslationFreq == 0) {
                lastSentenceVO = ch;
                totalTranslated += ch.length;
                TranslateThen(ch, loutput, linput, function(translatedText) {
                    setLastSentenceTranslated(translatedText);
                    EcrirePartial(lastSentenceTranslated);
                    if (callback) callback(lastPartialTranslation);
                }, false);
            } else {
                EcrirePartial(lastSentenceTranslated);
                if (callback) callback(lastPartialTranslation);
            }
            setLiveTranslationNum(LiveTranslationNum + 1);
    }
}