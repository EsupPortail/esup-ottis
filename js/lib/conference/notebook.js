import {
    builtin_language,
    inputnumber,
    MaxSavedLines,
    NBconnectedusers,
    Tconnectedusers,
    inputnumberQ,
    subtitle_language,
    lastPartialTranslation,
    setNBconnectedusers,
    setInputnumberQ,
    setInputnumber,
    setNbnewQ,
    setLastPartialTranslation,
    removeFromTconnectedusers
} from './state.js';

import { getNotebookContent, EventNewQuestion } from './utils.js';
import { updateUsers } from './config.js';
import { EcrireCR, AbortTimeout, ROOM_TOKEN } from './compat.js';

import { _ } from './dom-helpers.js';
import { addCsrfToken, createXhrRequest } from './network.js';



/** @module conference/notebook */



export function addSlideToNB(ch) {
    let tobesaidbetweenslides = '';
    if (_('tobesaid').value != '') tobesaidbetweenslides = _('tobesaid').value + '.';
    addToNB(tobesaidbetweenslides, 0);
    let Tch = ch.split('<br/>');
    for (let i = 0; i < Tch.length; i++) {
        addToNB(Tch[i], 0);
    }
}

export function addToNB(ch, before) {
    let speechlanguage = _('speechlanguage').value;
    let tobeinserted = '';
    if (speechlanguage != 'same') tobeinserted = speechlanguage + ':|:';
    let tobesaidbetweenslides = '';
    if (before == 1 && _('tobesaid').value != '') tobesaidbetweenslides = tobeinserted + _('tobesaid').value + '. <br/>';

    ch = tobesaidbetweenslides + tobeinserted + ch;
    //_('notebook').innerHTML+='<br/>'+ch;
    //EcrireTitle(ch);
    //saveTextNB(_('notebook').innerHTML);
    let Tch = ch.split('<br/>');
    saveTextTable(Tch, 0);
    //for (let c in Tch) saveText(Tch[c].replace(/<br\/>/gi,"").replace(/<br>/gi,""));
    //saveremotespeech();
}

export function saveTextTable(T, i) {
    if (i < T.length) {
        saveText(T[i].replace(/<br\/>/gi, '').replace(/<br>/gi, ''));
        setTimeout(function() {
            saveTextTable(T, i + 1);
        }, 100);
    }
}

export function EcrireNB(ch) {
    // if (_('notebook').innerHTML == "")
    //  _('notebook').innerHTML=ch;
    //else 
    // Use DOMPurify with allowed br tags to preserve line breaks
    let sanitizeOptions = {ALLOWED_TAGS: ['br']};
    _('notebook').innerHTML = DOMPurify.sanitize(_('notebook').innerHTML + ch + '<br/>', sanitizeOptions);
    //EcrireTitle(ch);
}

export function EcrireNBonly(ch) {
    // let currentContent = getNotebookContent('notebook');
    // logNotebookEvent('EcrireNBonly', 'notebook', ch, currentContent);
    // Use DOMPurify with allowed br tags to preserve line breaks
    let sanitizeOptions = {ALLOWED_TAGS: ['br']};
    _('notebook').innerHTML = DOMPurify.sanitize(_('notebook').innerHTML + ch + '<br/>', sanitizeOptions);
}

export function EcrireNBonlyVO(ch) {
    // let currentContent = getNotebookContent('notebookVO');
    // logNotebookEvent('EcrireNBonlyVO', 'notebookVO', ch, currentContent);
    // Use DOMPurify with allowed br tags to preserve line breaks
    let sanitizeOptions = {ALLOWED_TAGS: ['br']};
    _('notebookVO').innerHTML = DOMPurify.sanitize(_('notebookVO').innerHTML + ch + '<br/>', sanitizeOptions);
}

export function EcrirePartial(ch) {
    // console.log('EcrirePartial');
    // let currentContent = getNotebookContent('partialtitre');
    // logNotebookEvent('EcrirePartial', 'partialtitre', ch, currentContent);
    // Vérifier que ch est défini et non vide
    if (ch === undefined || ch === null || ch === 'undefined') {
        console.error('Texte non défini ou vide.');
        return;
    }

    // Mémoriser la dernière traduction affichée dans la zone partielle
    setLastPartialTranslation(ch);

    // Mettre à jour l'interface
    if (ch != 'undefined' && ch != undefined) {
        _('textBand').innerHTML = DOMPurify.sanitize(ch);
        _('textfixe').innerHTML = DOMPurify.sanitize(ch);
        // Synchroniser avec partialtitre si la case est cochée (utilisation de partialtitre)
        if (_('usetextband') && _('usetextband').checked) {
            _('partialtitre').innerHTML = DOMPurify.sanitize(ch);
        }
    }

    // Vérifier que ROOMID est défini et non vide
    let roomId = _('classroomid').value;
    if (!roomId) {
        console.error('ROOMID est vide ou non défini.');
        return;
    }

    // Créer la requête GET avec les paramètres encodés
    let url = 'TranscriptionSub.php?texte=' + encodeURIComponent(ch) + '&ROOMID=' + encodeURIComponent(roomId);

    let xhr = createXhrRequest('GET', url, {
        onSuccess: function(xhr) {
            if (xhr.responseText.trim() !== '') {
                try {
                    let data = JSON.parse(xhr.responseText);
                    if (data.status === 'error') {
                        console.error('Erreur serveur :', data.message);
                    }
                } catch (error) {
                    console.error('Erreur de parsing JSON. Réponse brute :', xhr.responseText);
                }
            } else {
                console.error('Réponse vide du serveur.');
            }
        },
        onError: function(xhr, context) {
            console.error('Erreur HTTP :', xhr.status, xhr.responseText);
        }
    });
    /*if (ch != 'undefined' && ch != undefined) _('partialtitre').innerHTML = ch;
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "TranscriptionSub.php?texte=" + ch + "&ROOMID=" + _('classroomid').value, true);*/

    /*xhr.open("POST", "TranscriptionSub.php", true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    
    let params = `texte=${encodeURIComponent(ch)}&ROOMID=${encodeURIComponent(_('classroomid').value)}`;
    xhr.addEventListener("load", function (e) {let data=JSON.parse(e.target.responseText);}, false);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    let data = JSON.parse(xhr.responseText);

                    if (data.status === 'error') {
                        console.error("Erreur serveur :", data.message);
                    }
                } catch (error) {
                    console.error("Erreur de parsing JSON. Réponse brute :", xhr.responseText);
                }
            } else {
                console.error("Erreur HTTP :", xhr.status, xhr.responseText);
            }
        }
    };*/

    xhr.onerror = function() {
        console.error('Erreur de requête.');
    };
    xhr.send();
    AbortTimeout(xhr,'Transcription');
}

export function Ecrire(ch) {
    let outputElement = _('output');
    if (!outputElement) return;
    if (outputElement.innerHTML == '')
        outputElement.innerHTML = DOMPurify.sanitize(ch);
    else outputElement.innerHTML = DOMPurify.sanitize(ch + '<br/>' + outputElement.innerHTML);
}

export function saveText(ch) {

    let inputlanguage = builtin_language;
    if (ch.indexOf(':|:') >= 0) { // If there are a language indication
        let Tch = ch.split(':|:');
        inputlanguage = Tch[0];
        ch = Tch[1];
    }
    let f = new FormData();
    f.append('lineid', inputnumber);
    setInputnumber(inputnumber + 1);
    f.append('text', ch);
    f.append('inputlanguage', inputlanguage);
    f.append('ROOMID', _('classroomid').value);
    f.append('LastLines', MaxSavedLines);
    f.append('room_token', ROOM_TOKEN);
    addCsrfToken(f);

    let request = createXhrRequest('POST', 'savetext.php', {
        data: f,
        timeout: 10000,
        context: 'saveText'
    });
    request.send(f);

    //	request.onload = function(event) {};
}

export function saveTextWithTranslation(sourceText, translatedText) {
    let inputlanguage = builtin_language || '';
    let targetLanguage = subtitle_language || '';
    
    // Ensure we have valid data before sending
    if (!inputlanguage || !targetLanguage || !sourceText) {
        console.error('saveTextWithTranslation: missing required parameters');
        return;
    }

    let f = new FormData();
    f.append('lineid', inputnumber);
    setInputnumber(inputnumber + 1);
    f.append('text', sourceText);
    f.append('inputlanguage', inputlanguage);
    f.append('targetlanguage', targetLanguage);
    f.append('translatedtext', translatedText);
    f.append('ROOMID', _('classroomid').value);
    f.append('LastLines', MaxSavedLines);
    f.append('room_token', ROOM_TOKEN);
    addCsrfToken(f);

    let request = createXhrRequest('POST', 'savetext.php', {
        data: f,
        timeout: 10000,
        context: 'saveTextWithTranslation'
    });
    request.send(f);
}

export function saveTextNB(ch) {
    // Deprecated: batch-save of notebook is not used in current flows.
    console.warn('saveTextNB() is deprecated and has been disabled.');
    return;
}



export function readTextQ() {
    let classroomidEl = _('classroomid');
    if (classroomidEl && classroomidEl.value != '') {
        let request = new XMLHttpRequest();
        request.open('POST', 'readtextQ.php');
        let f = new FormData();
        f.append('lineid', 0);
        f.append('ROOMID', classroomidEl.value);
        f.append('room_token', ROOM_TOKEN);
        addCsrfToken(f);
        request.send(f);
        if (typeof AbortTimeout !== 'undefined') {
            AbortTimeout(request,'readTextQ');
        }
        request.onload = function(event) {
            let Told = request.responseText.split('\n');
            let k = 0;
            let T = [];
            for (let i = 0; i < Told.length; i++) {
                if (Told[i].split(';').length > 2) T.push(Told[i]);
            }
            let nbnewQ = 0;
            for (let i = inputnumberQ; i < T.length; i++) {
                let L = T[i].split(';');
                // Safety check - ensure L has enough elements
                if (L.length < 3) continue;
                
                if (L[2].indexOf('[newstudentarrived]') >= 0) {
                    setNBconnectedusers(NBconnectedusers + 1);
                    Tconnectedusers.push(L[0]);
                    updateUsers();
                } else {
                    if (L[2].indexOf('[newstudentlived]') >= 0) {
                        setNBconnectedusers(NBconnectedusers - 1);
                        removeFromTconnectedusers(L[0]);
                        updateUsers();
                    } else {
                        setNbnewQ(nbnewQ + 1);
                        // Ensure L[1] exists and builtin_language is a string before accessing substr
                        if (L.length >= 2 && typeof builtin_language === 'string' && builtin_language && L[1] && L[1].substr) {
                            if (builtin_language.substr(0, 2) != L[1].substr(0, 2)) {
                                TranslateQ(L[2], builtin_language.substr(0, 2), L[1].substr(0, 2), L[0], i);
                            } else {
                                if (true) {
                                    EcrireCR(L[0], L[2], i);
                                }
                            }
                        } else if (L.length >= 1) {
                            // Fallback if L[1] is missing
                            if (true) {
                                EcrireCR(L[0], L[2], i);
                            }
                        }
                    }
                }
            }
            if (nbnewQ > 0) EventNewQuestion();
            setInputnumberQ(T.length);
        };
    }
}

export function TranslateQ(ch, l, linput, name, order) {
    let formData = new FormData();
    formData.append('texte', ch);
    formData.append('lang', l);
    formData.append('langinput', linput);
    let classroomidEl = _('classroomid');
    formData.append('ROOMID', (classroomidEl && classroomidEl.value) ? classroomidEl.value : '');
    formData.append('room_token', ROOM_TOKEN);

    let whichtranslatorEl = _('whichtranslator');
    let whichtranslator = (whichtranslatorEl && whichtranslatorEl.value) ? whichtranslatorEl.value : '';
    if (whichtranslator) formData.append('whichtranslator', whichtranslator);

    addCsrfToken(formData);

    let xhr = createXhrRequest('POST', 'Translate.php', {
        onSuccess: function(xhr) {
            try {
                let response = JSON.parse(xhr.responseText);

                if (response && !response.error && response.data && response.data.translations) {
                    let translatedText = response.data.translations[0].text;
                    if (true) {
                        EcrireCR(name, translatedText, order);
                    }
                } else {
                    console.error('Erreur de traduction. Réponse :', response);
                    if (true) {
                        EcrireCR(name, '---- Translation ERROR ----', order);
                    }
                }
            } catch (error) {
                console.error('Erreur de parsing JSON :', error);
                if (true) {
                    EcrireCR(name, '---- JSON Parse ERROR ----', order);
                }
            }
        },
        onError: function(xhr, context) {
            console.error('Erreur HTTP :', xhr.status);
            if (true) {
                EcrireCR(name, `---- HTTP ERROR ${xhr.status} ----`, order);
            }
        },
        data: formData,
        timeout: 10000,
        context: 'TranslateQ'
    });
    xhr.onerror = function() {
        console.error('Erreur réseau dans TranslateQ.');
        if (true) {
            EcrireCR(name, '---- Network ERROR ----', order);
        }
    };
    xhr.send(formData);
}