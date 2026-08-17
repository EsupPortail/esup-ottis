// Basic functions
// NOTE: EcrireCR function is imported from chat-common.js
// All calls to EcrireCR() in this file use the version from chat-common.js

/**
 * @summary A prettier alert function
 * 
 * @param {string} ch The text shown in the alert.
 * @returns
 */

function myalert(ch) {
    ch = ch.replaceAll('\n', '<br/>');
    _('dialog-invite-p').innerHTML = ch;
    $('#dialog-invite').dialog('open');
}

var lastPartialTranslation = '';

// Global variables for user connection tracking
var NBconnectedusers = 0;
var Tconnectedusers = [];

// Global variable for tracking new questions (used by chat message handler)
var nbnewQ = 0;

// =============================================================================
// LOGGING POUR DÉBOGAGE DES NOTEBOOKS - DÉSACTIVÉ
// =============================================================================
/*
function logNotebookEvent(source, elementId, text, currentContent) {
    console.log(
        `%c[NOTEBOOK LOG] ${source} → ${elementId}`,
        'background: #ffcc00; color: #000; font-weight: bold;',
        {
            text: text,
            currentContentBefore: currentContent,
            timestamp: new Date().toLocaleTimeString()
        }
    );
}

// Fonction pour obtenir le contenu actuel d'un élément
function getNotebookContent(elementId) {
    var el = document.getElementById(elementId);
    return el ? el.innerHTML.replace(/<br\/?>$/, '').substring(0, 100) + '...' : 'N/A';
}

// Initialisation pour le monitoring
window.lastNotebookContent = '';
window.lastNotebookVOContent = '';
*/

// =============================================================================

// Student feedback

/**
 * @summary A function that simulate the pronunciation time for a given text at an average rythm in French (En France, prononcer une phrase de n caractères (environ n/5 mots) à un débit moyen (120mots/min en France) prend environ 100*n ms.
 * 
 * @param {string} txt a given text.
 * @param {function} f a feedback function.
 */

function StudentParleThen(txt, f) {
    //EcrireFeedback(txt);
    setTimeout(f, 100 * txt.length);
}

/**
 * @summary Translate a given string from a language to another
 * 
 * @deprecated
 * @param {string} ch the string to translate.
 * @param {string} l output language.
 * @param {string} linput input language.
 * @param {number} lineid unique identifier for the translation.
 */
/*
function StudentTranslate(ch, l, linput, lineid) {
    // Deprecated: disabled. Use TranslateThen or StudentTranslate2.
    console.warn('StudentTranslate() is deprecated and has been disabled.');
    return;
}
*/

/* Deprecated: StudentTranslate is kept for reference but disabled.
function StudentTranslate(ch, l, linput, lineid) {
    console.log("StudentTranslate (disabled)");
    return; // Use TranslateThen or StudentTranslate2 instead.
}
*/

/**
 * @summary Translate a given string from a language to another
 * 
 * @param {string} ch the string to translate.
 * @param {string} l output language.
 * @param {string} linput input language.
 * @param {number} lineid unique identifier for the translation.
 */

function StudentTranslate2(ch, l, linput, lineid) {

    var xhr = new XMLHttpRequest();
    lineid='DIRECT';
    xhr.open('POST', 'Translate.php', true);
    
    var formData = new FormData();
    formData.append('texte', ch);
    formData.append('lang', l);
    formData.append('langinput', linput);
    formData.append('ROOMID', _('classroomid').value || '');
    formData.append('lineid', lineid);
    
    // Ajouter whichtranslator (apikey gere via config.php)
    var whichtranslator = _('whichtranslator').value;
    if (whichtranslator) formData.append('whichtranslator', whichtranslator);
    
    xhr.onloadend = function() {
        StudentCompleteTranslation2();
    };
    xhr.send(formData);
    AbortTimeout(xhr,'StudentTranslate2');
}

/*
function StudentCompleteTranslation() {
    // Deprecated orchestration: replaced by newer StudentCompleteTranslation2 flow.
    console.warn('StudentCompleteTranslation() is deprecated and has been disabled.');
    return;
}
*/

/* Deprecated: StudentCompleteTranslation orchestration is disabled.
function StudentCompleteTranslation() {
    console.log("StudentCompleteTranslation (disabled)");
    return; // Use StudentCompleteTranslation2 and newer flows.
}
*/

/**
 * @summary Orchestrates the sequence of translations
 * 
 * @todo Comprendre pourquoi on appelle StudentCompleteTranslation dans le sinon et pas StudentCompleteTranslation2
 */

function StudentCompleteTranslation2() {

    if (StudentToTranslate2.length > 0) {
        var L = StudentToTranslate2[0];
        StudentToTranslate2.splice(0, 1);
        if (subtitle_language.substr(0, 2).toLowerCase() != L[1].substr(0, 2)) {
            // Ecrire("Push : "+L[2]);
            StudentTranslate2(L[2], subtitle_language.substr(0, 2).toLowerCase(), L[1].substr(0, 2), L[0]);
        } else StudentCompleteTranslation2();
    } else setTimeout(StudentCompleteTranslation, 10);
}

/**
 * @summary Orchestrates the sequence of speeches
 * 
 */

function StudentCompleteSpeech() {
    var nextdiscours = Studentcurrentdiscours;
    for (var i = 1; i < 1000; i++) {
        if (StudentToSay['' + (Studentcurrentdiscours + i)]) {
            nextdiscours = Studentcurrentdiscours + i;
            break;
        }
    }
    if (Studentgotolive) {
        //console.log('Restart Speech');
        //console.log('Discours : '+currentdiscours+'   '+nextdiscours)
        for (var i = 1000; i > 0; i--) {
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
        var L = StudentToSay['' + nextdiscours];
        L = L.replace('&nbsp;', ' ');
        StudentParleThen(L, StudentCompleteSpeech);
        Studentcurrentdiscours = nextdiscours;
    } else Studentcompletespeechactive = false;

}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue).
 * 
 */

function StudentreadText() {

    var synthesison = true;
    if (synthesison) {
        if (_('classroomid').value != '') {
            var request = new XMLHttpRequest();
            request.open('POST', 'readtext.php');
            AbortTimeout(request, 'StudentreadText');
            Studentcurrentorder_readtext++;
            var f = new FormData();
            f.append('lineid', Studentinputnumber); // Non utilisé dans readtextTranscriptSub.php, mais conservé pour compatibilité
            f.append('ORDER', Studentcurrentorder_readtext);
            f.append('ROOMID', _('classroomid').value);
            f.append('room_token', window.ROOM_TOKEN);
            request.send(f);
            request.onload = function(event) {
                if (request.status !== 200) {
                    console.error('Erreur HTTP:', request.status, request.statusText);
                    return;
                }
                if (!request.responseText.trim()) {
                    console.warn('Réponse vide ou erreur côté serveur.');
                    return;
                }
                // Traiter la réponse
                var T = request.responseText.split('\n');
                var maxi = -1;
                for (var i = 0; i < T.length; i++) {
                    if (T[i].trim() === '') continue;
                    var parts = T[i].split(';');
                    if (parts.length < 2) continue; // Ignorer les lignes mal formatées
                    var readedorder = parts[0] * 1; // Premier élément = ORDER
                    var lineData = parts.slice(1).join(';'); // Le reste = données de la ligne
                    var L = lineData.split(';');
                    if (L.length > 3) {
                        // Afficher le texte dans notebookVO
                        EcrireNBonlyVO(L[3]);
                        if (1 * L[1] > maxi) maxi = 1 * L[1];
                        if (StudentAllPhrases[1 * L[1]] == undefined) {
                            StudentAllPhrases[1 * L[1]] = true;
                            StudentToTranslate.push([L[1], L[2], L[3]]);
                            StudentToTranslate2.push([L[1], L[2], L[3]]);
                        }
                    }
                }
                if (maxi > Studentinputnumber) Studentinputnumber = maxi;
                StudentCompleteTranslation2();
            };
            request.onerror = function() {
                console.error('Erreur réseau ou CORS.');
            };
        } else {
            console.warn('classroomid est vide !');
        }
    }
}

/*function StudentreadText() {
    console.log("StudentreadText");
    var synthesison = true;
    if (synthesison) {
        console.log(_('classroomid').value);
        if (_('classroomid').value != "") {
            var request = new XMLHttpRequest();
            request.open("POST", "readtext.php");
            AbortTimeout(request,"StudentreadText");
            Studentcurrentorder_readtext++;
            var f = new FormData();
            f.append('lineid', Studentinputnumber);
            f.append('ORDER', Studentcurrentorder_readtext);
            f.append('ROOMID', _('classroomid').value);
            f.append('room_token', window.ROOM_TOKEN);
            request.send(f);
            request.onload = function(event) {

                var T = request.responseText.split('\n');
                var maxi = -1;
                var readedorder = ('0' + T[0]).split(';')[0] * 1;
                if (readedorder > Studentmaxorder_readtext) {
                    Studentmaxorder_readtext = readedorder;
                    for (var i = 0; i < T.length; i++) {
                        var L = T[i].split(';');
                        if (L.length > 3) {
                            if (1 * L[1] > maxi) maxi = 1 * L[1];
                            if (StudentAllPhrases[1 * L[1]] == undefined) {
                                StudentAllPhrases[1 * L[1]] = true;
                                //EcrireNB(L[3]);
                                StudentToTranslate.push([L[1], L[2], L[3]]);
                                StudentToTranslate2.push([L[1], L[2], L[3]]);
                            }
                            /*      if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2))
          Translate(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2),L[0]);
        else {
          Parle(L[2]);
          Ecrire(""+L[2]);
        }*//*
                        }
                    }
                }
                //alert(maxi);
                if (maxi > Studentinputnumber) Studentinputnumber = maxi;

                StudentCompleteTranslation2();
            };
        }
    }
}*/
// End Student Feedback





/**
 * @summary Fill the select VoiceOutput field
 * 
 */

function PopulateVoicesOut() {
    var select = _('VoiceOutput');
    if (!select) return;
    AllOutputVoices = [];
    while (select.firstChild) {
        select.removeChild(select.firstChild);
    }
    var voices = window.speechSynthesis.getVoices();
    for (var i = 0; i < voices.length; i++) {
        if (true) { //(voices[i].localService) {
            var opt = document.createElement('option');
            opt.value = AllOutputVoices.length;
            if (voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
            AllOutputVoices.push(voices[i]);
            opt.innerHTML = voices[i].lang + ' ' + voices[i].name;
            select.appendChild(opt);
        }
    }
}

/**
 * @summary Fill the select classroomlanguage field
 * 
 * @deprecated
 */

/*function PopulateVoicesIn() {
    var select = _('classroomlanguage');
    var voices = window.speechSynthesis.getVoices();
    TLangInput = [];

    for (var i = 0; i < voices.length; i++) {
        if (true) { //(voices[i].localService) {
            var lang = voices[i].lang; //.substr(0,5);
            if (TLangInput.indexOf(lang) < 0) {
                var opt = document.createElement("option");
                opt.value = TLangInput.length;
                if (voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
                TLangInput.push(lang);
                opt.innerHTML = lang;
                select.appendChild(opt);
            }
        }
    }
}*/

/**
 * @summary Fill the select classroomlanguage field
 * 
 */

function PopulateVoicesIn() {
    if (TLangInput.length == 0) {
        var select = _('classroomlanguage');
        TLangInput = [];
        //console.log(Object.keys(window.pageData.supportedlanguages).length)
        Object.entries(window.pageData.supportedlanguages).forEach(([code, lang], index) => {
            if (TLangInput.indexOf(code) === -1) {
                var opt = document.createElement('option');
                opt.value = code; // Utilise le code de la langue comme valeur

                // Sélectionne la première langue par défaut
                if (index === 0) {
                    opt.selected = true;
                    builtin_language = code;

                }

                TLangInput.push(code);
                opt.textContent = `${code} ${lang.name_fr} ${lang.name_vo}`;
                select.appendChild(opt);
            }
        });
    }
}

/**
 * @summary Fill the select subtitlelanguage field
 * 
 */


function PopulateSubtitlesLang() {

    var select = _('subtitlelanguage');
    Object.entries(window.pageData.supportedlanguages).forEach(([code, lang], index) => {
            if (TLangInput.indexOf(code) === -1) {
                var opt = document.createElement('option');
                opt.value = code; // Utilise le code de la langue comme valeur

                // Sélectionne la première langue par défaut
                if (index === 0) {
                    opt.selected = true;
                    subtitle_language = code;
                }

                //TLangInput.push(code);
                opt.textContent = `${code} ${lang.name_fr} ${lang.name_vo}`;
                select.appendChild(opt);
            }
        });
}

/**
 * @summary Fill the select speechlanguage field
 * 
 */

function PopulateSpeechLang() {
    var select = _('speechlanguage');
    for (var i = 0; i < window.pageData.supportedlanguages.length; i++) {
        var lang = window.pageData.supportedlanguages[i].code; //.substr(0,5);
        var opt = document.createElement('option');
        opt.value = lang;
        opt.innerHTML = lang + ' ' + window.pageData.supportedlanguages[i].name_fr + ' ' + window.pageData.supportedlanguages[i].name_vo;
        select.appendChild(opt);
    }
}


/**
 * @summary setter for the subtitle language
 * 
 * @param {number} i Language index.
 */


function setLanguageSubtitle(i) {
    subtitle_language = i;

}

/**
 * @summary setter for the speech language
 * 
 * @param {number} i Language index.
 */


function setLanguageSpeech(i) {
    builtin_language = i;

}

/**
 * @summary Speech synthesis of a given text
 * 
 * @param {string} txt a given text.
 */

function Parle(txt) {
    var discours = new SpeechSynthesisUtterance(txt);
    discours.voice = builtin_voice;
    discours.lang = builtin_voice.lang;
    window.speechSynthesis.speak(discours);
    discours.onend = ready;
    discours.onerror = ready;
}

/**
 * @summary Function that is fired when the Parle function has ended
 * 
 */

function ready() {
    readytotalk = true;
    _('bready').style.backgroundColor = 'green';
    StartSpeechRecognition(Traduction);
}

/**
 * @summary Function that is executed when the Parle function has begun
 * 
 */

function notready() {
    StopSpeechRecognition();
    readytotalk = false;
    _('bready').style.backgroundColor = 'red';
}

/**
 * @summary Remove the verbal tics of a given sentence
 * 
 * @param {string} ch A given sentence.
 * @returns The same sentence without the verbal tics.
 */

/**
 * @summary Main function for translating a given text, handles the synchronisation of different translations.
 * 
 * @param {string} ch a given sentence.
 * @returns
 */

function Traduction(ch) {
    var recognitionon = _('recognitionon').checked;
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
/*        var liveon = _('liveon').checked;
        if (liveon) {
            if (readytotalk) {
                notready();
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


/**
 * @summary A function that adds url arguments related to the choosen translator service.
 * 
 * @returns {string} the arguments that have tot be added to Translate.php url
 */

function TranslatorInfo() {
    var res='';
    var whichtranslator=_('whichtranslator').value;
    if (whichtranslator != '') res+='&whichtranslator='+whichtranslator;
    return res;
}

/**
 * @summary A function that translates a given text from a language to another.
 * 
 * @param {string} ch A given sentence
 * @param {string} l The output language.
 * @param {string} linput The input language.
 * @param {function} callback Optional callback function to receive translated text
 */

function Translate(ch, l, linput, callback) {
    // Use TranslateThen which handles encoding and callbacks

    TranslateThen(ch, l, linput, function(translatedText) {
        if (typeof translatedText === 'string') {
            // Only speak if voice is available
            if (typeof builtin_voice !== 'undefined' && builtin_voice) {
                Parle(translatedText);
            }
            lastSentenceTranslated = translatedText;
            Ecrire('' + translatedText);
            if (callback) callback(translatedText);
        } else {
            console.error('Translate: invalid translatedText', translatedText);
            if (callback) callback('');
        }
    }, false);
}


/**
 * @summary A function that translates a given text from a language to another, then fire a given feedback function.
 * 
 * @param {string} ch A given sentence
 * @param {string} l The output language.
 * @param {string} linput The input language.
 * @param {function} f the function that is fired when the translation has ended.
  */

function TranslateThen(ch, l, linput, f, forcelibre = false) {


    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'Translate.php', true);

    var formData = new FormData();
    formData.append('texte', ch);
    formData.append('lang', l);
    formData.append('langinput', linput);
    formData.append('ROOMID', _('classroomid').value || '');
    formData.append('lineid', 'DIRECT');

    if (forcelibre) {
        formData.append('forcelibre', 'true');
    }

    // Ajouter whichtranslator (apikey gere via config.php)
    var whichtranslator = _('whichtranslator').value;
    if (whichtranslator) formData.append('whichtranslator', whichtranslator);

    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE) {
            var d_end = Date.now();
            //console.log("Réponse reçue en " + (d_end - d_begin) + "ms");

            if (this.status === 200) {
                try {
                    var response = JSON.parse(this.responseText);

                    if (response && !response.error && response.data && response.data.translations && 
                        response.data.translations[0] && response.data.translations[0].text) {
                        lastSentenceTranslated = response.data.translations[0].text;
                        f(response.data.translations[0].text);
                    } else {
                        console.error('Erreur de traduction. Réponse :', response);
                        f('---- Translation ERROR ----');
                    }
                } catch (error) {
                    console.error('Erreur de parsing JSON :', error, 'Réponse brute :', this.responseText);
                    f('---- JSON Parse ERROR ----');
                }
            } else {
                console.error('Erreur HTTP :', this.status, this.statusText);
                f(`---- HTTP ERROR ${this.status} ----`);
            }
        }
    };

    xhr.onerror = function() {
        console.error('Erreur réseau dans TranslateThen.');
        f('---- Network ERROR ----');
    };

    var d_begin = Date.now();
    xhr.send(formData);
    AbortTimeout(xhr, 'TranslateThen');
}
/*function TranslateThen(ch, l, linput, f, forcelibre=false) {
    console.log("TranslateThen");
    var force="";
    if (forcelibre) force="&forcelibre=true";
    var xhr = new XMLHttpRequest();
    
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + _('classroomid').value+"&lineid=DIRECT"+force+TranslatorInfo(), true);

    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            var d_end = Date.now();
            //console.log("Receiving translation in " + (d_end - d_begin) + "ms for " + ch + " from " + linput + " to " + l);

            //T_durations[ch]=undefined;
            var response = JSON.parse(this.responseText);
            lastSentenceTranslated=response["data"]["translations"][0]["translatedText"];
            if ((!response["error"]) && response["data"]["translations"]) {
                f(response["data"]["translations"][0]["translatedText"]);
            } else {
                f("---- Translation ERROR ----");
            }

        }
    };
    var d_begin = Date.now();
    //console.log(d_begin+" : Sending for translation "+ch+" from "+linput+" to "+l);
    //T_durations[ch]=d;
    xhr.send();
    AbortTimeout(xhr,"TranslateThen");
}*/


/**
 * @summary Update the progress bar when a lesson is loaded
 * 
 * @param {event} event an event.
 */

function progressHandler(event) {
    var percent = (event.loaded / event.total) * 100;
    _('progressBar').value = Math.round(percent);
    //  _("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}

/**
 * @summary Add several sentences to the buffer containing all the speech
 * 
 * @deprecated ?
 * @param {string} ch a <br/> separated list of sentences.
 * @returns
 */

function addSlideToNB(ch) {
    var tobesaidbetweenslides = '';
    if (_('tobesaid').value != '') tobesaidbetweenslides = _('tobesaid').value + '.';
    addToNB(tobesaidbetweenslides, 0);
    var Tch = ch.split('<br/>');
    for (var i = 0; i < Tch.length; i++) {
        addToNB(Tch[i], 0);
    }
}

/**
 * @summary Add a single sentence to the buffer cotaining all the speech
 * 
 * @param {string} ch a sentence.
 * @param {number} before a deprecated parameter for adding debugging informations.
 */

function addToNB(ch, before) {
    var speechlanguage = _('speechlanguage').value;
    var tobeinserted = '';
    if (speechlanguage != 'same') tobeinserted = speechlanguage + ':|:';
    var tobesaidbetweenslides = '';
    if (before == 1 && _('tobesaid').value != '') tobesaidbetweenslides = tobeinserted + _('tobesaid').value + '. <br/>';

    ch = tobesaidbetweenslides + tobeinserted + ch;
    //_('notebook').innerHTML+='<br/>'+ch;
    //EcrireTitle(ch);
    //saveTextNB(_('notebook').innerHTML);
    var Tch = ch.split('<br/>');
    saveTextTable(Tch, 0);
    //for(var c in Tch) saveText(Tch[c].replace(/<br\/>/gi,"").replace(/<br>/gi,""));
    //saveremotespeech();
}

/**
 * @summary A recursive function that saves sequentially all the sentences of a table
 * 
 * @param {array} T a table of sentences.
 * @param {number} i the identifier of the sentence that is to be saved.
 */

function saveTextTable(T, i) {
    if (i < T.length) {
        saveText(T[i].replace(/<br\/>/gi, '').replace(/<br>/gi, ''));
        setTimeout(function() {
            saveTextTable(T, i + 1);
        }, 100);
    }
}

/**
 * @summary a function that convert b64 encoded data to an html blob object (useful to encode images)
 * 
 * @link adapted from https://stackoverflow.com/questions/16245767/creating-a-blob-from-a-base64-string-in-javascript
 * @param {string} b64Data contains the data encoded in binary 64.
 * @param {string} contentType prefix.
 * @param {number} sliceSize default to 512.
 * @returns the created blob object
 */

function b64toBlob(b64Data, contentType, sliceSize) {
    contentType = contentType || '';
    sliceSize = sliceSize || 512;

    var byteCharacters = atob(b64Data);
    var byteArrays = [];

    for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        var slice = byteCharacters.slice(offset, offset + sliceSize);

        var byteNumbers = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }

        var byteArray = new Uint8Array(byteNumbers);

        byteArrays.push(byteArray);
    }

    var blob = new Blob(byteArrays, {
        type: contentType
    });
    return blob;
}


/**
 * @summary Display an image on the Slide tab and send it to all the students
 * 
 * @param {number} n The image number.
 */

function ShowImage(n) {

    if (n != previousimage) {
        EcrireNBonly('---------- Slide ' + n + ' ----------');
        EcrireNBonlyVO('---------- Slide ' + n + ' ----------');
        previousimage = n;
        //console.log('IMG OBJ = '+_("img_"+n));
        //console.log('IMG URL = "'+_("img_"+n).src+'"');
        if (_('img_' + n) && _('img_' + n).src.length > 200) {
            _('img_slides').src = _('img_' + n).src;
            resizeSlides();

            // Diffusion Image
            var request = new XMLHttpRequest();
            request.open('POST', 'saveimage.php');
            var f = new FormData();
            f.append('random', Math.floor(Math.random() * 10000));
            var imageurl = _('img_' + n).src;
            /*
            var nimages=0;
            var maxcar=10000;
            for(var i=0; i*maxcar<imageurl.length;i++) {
              f.append('image'+i,imageurl.substr(i*maxcar,maxcar));
              nimages++;
            }
            f.append('nbimages',nimages);
            */
            var block = imageurl.split(';');
            var contentType = block[0].split(':')[1];
            var realData = block[1].split(',')[1];
            var blob = b64toBlob(realData, contentType);
            f.append('image', blob);
            f.append('ROOMID', _('classroomid').value);
            f.append('room_token', window.ROOM_TOKEN);
            request.send(f);
            AbortTimeout(request,'ShowImage');
            //	request.onload = function(event) {};
        }
    }
}


/**
 * @summary a function that is fired when a new lesson is loaded to prepare all the data structures.
 * 
 * @param {string} datatxt a text value contained the text of the speech and a b64 version of the images.
 * @returns an HTML encoding of what should be shown in the complete lesson tab.
 */

function formateresponse(datatxt,editable=true) {

    try {
        var data = JSON.parse(datatxt);
    } catch (e) {
        console.error('Failed to parse JSON:', e, datatxt);
        return '<pre>Error: Invalid JSON response. Check server output.</pre>';
    }
    if (data && typeof data === 'object' && !Array.isArray(data) && data.error) {
        console.error('Server returned error object:', data);
        return '<pre>Error: ' + (data.error || 'Unknown error') + (data.details ? '\nDetails: ' + data.details : '') + '</pre>';
    }
    if (!Array.isArray(data)) {
        console.error('Unexpected response format:', data);
        return '<pre>Error: Unexpected response format from server.</pre>';
    }
    currentPresentation = [];
    //alert(datatxt);
    var res = '';
    // console.log(data);
    for (var i = 0; i < data.length; i++) {
        slide = data[i];
        var txtnotes = '';
        for (var j = 0; j < slide['Notes'].length; j++) {
            if (slide['Notes'][j].indexOf('[forward]') >= 0 || slide['Notes'][j].indexOf('[backward]') >= 0 || slide['Notes'][j].indexOf('[pause') >= 0 || slide['Notes'][j].indexOf('[resource') >= 0 || slide['Notes'][j].indexOf('[mouse') >= 0 || slide['Notes'][j].indexOf('[notification') >= 0 || slide['Notes'][j].indexOf('[wait') >= 0) {
                slide['Notes'][j] = '';
            } else {
                txtnotes = txtnotes + slide['Notes'][j] + ' <br/>';
            }
        }
        res += '<dl>';
        if (slide['Images']) {
            //alert("The presentation contains "+slide["Images"].length+" images");
            var toexec = '';
            for (var j = 1; j <= slide['Images'].length; j++) {
                toexec += "_('img_" + j + "').src='" + slide['Images'][j - 1] + "'; ";
            }
            setTimeout(toexec, 10);
        } else {
            //      res = res + '<dt><span onclick="addToNB(\''+txtnotes+'\',1); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
            //      res = res + '<dt><span onclick="addSlideToNB(\''+txtnotes+'\'); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
            res = res + '<dt>&nbsp;Slide #' + slide['Slide'] + '<img id="img_' + slide['Slide'] + '" width="50" src=""/></dt>\n';
            var flag = false;
            for (var j = 0; j < slide['Notes'].length; j++) {
                if (slide['Notes'][j].trim() != '') {
                    if (editable) {
                        res = res + '<dd>&nbsp;' + '<span id="SlideText'+i+'_'+j+'" contenteditable="true">'+slide['Notes'][j] + '</span>'+'</dd>\n';
                    } else {
                        res = res + '<dd><span onclick="addToNB(\'' + slide['Notes'][j] + '\',0); ShowImage(' + slide['Slide'] + '); currentDiscours=' + currentPresentation.length + '; saveremotespeech();">▶️</span>&nbsp;' + slide['Notes'][j]+'</dd>\n';
                    }
                    currentPresentation.push({
                        'Slide': slide['Slide'],
                        'Discours': slide['Notes'][j]
                    });
                    flag = true;
                }
            }
            if (!flag) currentPresentation.push({
                'Slide': slide['Slide'],
                'Discours': ''
            });
        }
        res += '</dl>';
    }
    //  alert(res);

    return res;
}

/**
 * @summary A function that save the speech for the remote application
 * 
 */

function saveremotespeech() {
    if (currentPresentation.length > 0) {
        var currentDiscoursB = (currentDiscours - 1 + currentPresentation.length) % currentPresentation.length;
        var currentDiscoursA = (currentDiscours + 1) % currentPresentation.length;
        var txt = currentPresentation[currentDiscoursB]['Discours'] + ':|:' + currentPresentation[currentDiscours]['Discours'] + ':|:' + currentPresentation[currentDiscoursA]['Discours'];
        if (_('classroomid').value != '') {
            var request = new XMLHttpRequest();
            request.open('POST', 'savespeechforremote.php');
            var f = new FormData();
            f.append('ROOMID', _('classroomid').value);
            f.append('SPEECH', txt);
            f.append('room_token', window.ROOM_TOKEN);
            request.send(f);
            AbortTimeout(request,'saveremotespeech');
            request.onload = function(event) {
                var data = request.responseText;
            };
        }
    }
}


/**
 * @summary Go one step forward in the speech list, but do not take into account the current speech
 * 
 */

function SkipForward() {
    if (currentPresentation.length > 0) {
        currentDiscours = (currentDiscours + 1) % currentPresentation.length;
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}

/**
 * @summary Go one step backward in the speech list , but do not take into account the current speech
 * 
 */

function SkipBackward() {
    if (currentPresentation.length > 0) {
        currentDiscours = (currentDiscours - 1 + currentPresentation.length) % currentPresentation.length;
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}


/**
 * @summary Go one step forward in the speech list, and handle the speech
 * 
 */

function Forward() {
    if (currentPresentation.length > 0) {
        currentDiscours = (currentDiscours + 1) % currentPresentation.length;
        addToNB(currentPresentation[currentDiscours]['Discours'], 0);
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}

/**
 * @summary Go one step backward in the speech list, and handle the speech
 * 
 */

function Backward() {
    if (currentPresentation.length > 0) {
        currentDiscours = (currentDiscours - 1 + currentPresentation.length) % currentPresentation.length;
        addToNB(currentPresentation[currentDiscours]['Discours'], 0);
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}


/**
 * @summary Go one slide forward in the speech list, and do not handle the speech
 * 
 */

function SlideForward() {
    if (slide['Images'].length > 1) {
        var currentSlide = currentPresentation[currentDiscours]['Slide'];
        while (currentDiscours < currentPresentation.length - 1 && currentPresentation[currentDiscours]['Slide'] == currentSlide) {
            currentDiscours++;
        }
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}

/**
 * @summary Go one slide backward in the speech list, and do not handle the speech
 * 
 * @todo Actuellement, on arrive sur le slide pour la dernière phrase du discours et pas la première, c'est à revoir.
 */

function SlideBackward() {
    if (slide['Images'].length > 1) {
        var currentSlide = currentPresentation[currentDiscours]['Slide'];
        while (currentDiscours > 0 && currentPresentation[currentDiscours]['Slide'] == currentSlide) {
            currentDiscours--;
        }
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}


/**
 * @summary A function that is fired when a complete lesson is loaded.
 * 
 * @param {event} event an event.
 */

function completeHandler(event,editable=false) {

    _('notebookcompleteformatted').innerHTML = formateresponse(event.target.responseText,editable);
    _('progressBar').value = 0;

    // Re-initialize the presentation
    previousimage = -1;
    currentDiscours = 0;
    if (currentPresentation.length > 0) {
        setTimeout(function() {
            _('stillwaiting').style.display='none';
            ShowImage(currentPresentation[0]['Slide']);
        }, 1000);
    }
}

/**
 * @summary The function that is executed for reading a new presentation
 * 
 */
function SlideNotes() {

    var fileInput = _('ppt_presentation');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        alert('Please select a file first!');
        return;
    }
    var file = fileInput.files[0];
    var scriptfile = '';
    var time = 30000;

    // Determine the script based on file extension
    if (file.name.endsWith('pptx')) {
        scriptfile = typeof LoadPPTX !== 'undefined' && LoadPPTX
            ? 'ExtractSlidesNotes_PPTX_images.php'
            : 'ExtractSlidesNotes.php';
    } else if (file.name.endsWith('odp')) {
        scriptfile = 'ExtractSlidesNotes.php';
    } else if (file.name.endsWith('txt')) {
        scriptfile = 'ExtractSlidesNotesTxt.php';
    } else if (file.name.endsWith('html')) {
        scriptfile = 'ExtractSlidesNotesAppHTML.php';
        time = 300000;
    }

    if (scriptfile === '') {
        alert('Only pptx, odp, html, and txt files are allowed!');
        return;
    }

    _('stillwaiting').style.display = 'block';
    _('spinnertxt').innerHTML = 'Your presentation is not ready yet....<br/>Still working on it...';

    var formdata = new FormData();
    formdata.append('ppt_presentation', file);
    formdata.append('ACCESS', _('classroomid').value);


    var xhr = new XMLHttpRequest();
    xhr.open('POST', scriptfile);
    xhr.upload.addEventListener('progress', progressHandler, false);
    xhr.addEventListener('load', completeHandler, false);
    xhr.timeout = time;
    xhr.ontimeout = (e) => {

        _('spinnertxt').innerHTML = 'Your PPTX presentation is too long to be imported directly.<br/>Please convert it first with the OMIST Toolbox and use the generated html file instead.';
        alert('Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.');
    };
    xhr.send(formdata);
}
/*function SlideNotes() {

    var fileInput = _("ppt_presentation");
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        alert("Please select a file first!");
        return;
    }
    var file = fileInput.files[0];
    var scriptfile = "";
    var time = 30000;
    //  if (file.name.endsWith('pptx')) scriptfile = "ExtractSlidesNotes.php";
    if (file.name.endsWith('pptx'))
        if (LoadPPTX) scriptfile = "ExtractSlidesNotes_PPTX_images.php";
        else scriptfile = "ExtractSlidesNotes.php";
    if (file.name.endsWith('odp')) scriptfile = "ExtractSlidesNotes.php";
    if (file.name.endsWith('txt')) scriptfile = "ExtractSlidesNotesTxt.php";
    if (file.name.endsWith('html')){
        scriptfile = "ExtractSlidesNotesAppHTML.php";
        time = 300000;
    } 
    if (scriptfile == "") {
        alert('Only pptx, odp, html and txt files are allowed !');
        return -1;
    }
    _('stillwaiting').style.display="block";
    _('spinnertxt').innerHTML='Your presentation is not ready yet....<br/>Still working on it...';

    var formdata = new FormData();
    formdata.append("ppt_presentation", file);
    formdata.append("ACCESS", _('classroomid').value);

    var xhr = new XMLHttpRequest();

    xhr.open("POST", scriptfile);
    xhr.upload.addEventListener("progress", progressHandler, false);
    //  xhr.addEventListener("load", completeHandler, false);
    xhr.addEventListener("load", completeHandler, false);
    /*  xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
          var response = JSON.parse(this.responseText);
          _('notebookcompleteformatted').innerHTML = "<pre>"+response+"</pre>";
          alert(response);
        }
      };
      *//*
    xhr.timeout = time;
    xhr.ontimeout = (e) => {

        _('spinnertxt').innerHTML='Your PPTX presentation is too long to be imported directly.<br/>Please convert it first with the OMIST Toolbox and use the generated html file instead.';
        alert("Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.")
    };
    xhr.send(formdata);
    //AbortTimeout(xhr,"SlideNotes");
}*/

/**
 * @summary Switch the Microphone/Speechrecognition on and off
 * 
 */

function FullScreen() {
    if (!builtin_microsofttranscritionon) {
        builtin_recognition.abort();
    }
    var mybox = _('micon');
    _('recognitionon').checked = !_('recognitionon').checked;
    builtin_recognitionon = _('recognitionon').checked;
    if (_('recognitionon').checked) {
        mybox.innerHTML = '<i class=\'material-icons blink\'>record_voice_over</i>';
    } else {
        mybox.innerHTML = '<i class=\'material-icons\'>voice_over_off</i>';
    }
    if (builtin_microsofttranscritionon) {
        if (builtin_recognitionon) {
            doContinuousRecognition(MicrosoftKey, MicrosoftRegion, builtin_language);
        } else {
            stopRecognizer();
        }
    }
    if (_('recognitionon').checked && !builtin_microsofttranscritionon) {
        StartSpeechRecognition(Traduction);
    }
    /* else {
          builtin_recognition.abort();
        }*/

}

/**
 * @summary The function that is executed for opening a new window with subtitles only.
 * 
 */

function SubWindow(){
    var sublink = _('sublink');
    if (!sublink) return; // Éléments supprimés
    event.preventDefault();
    var window_width = window.screen.width;
    var windowFeatures = 'status=no,location=no,toolbar=no,menubar=no,width=1200,height=110';
    var link = sublink.innerHTML.replaceAll('&amp;','&');
    window.open(link,'_blank',windowFeatures);
}

/**
 * @summary The function that is executed for reading a new PPTX presentation stored on UNCloud
 * 
 * @param {string} uncloudlink A public link on Nantes University cloud (UNCloud) that points to a PPTX file.
 */

function SlideNotesUncloud(uncloudlink) {
    _('spinnertxt').innerHTML='Your presentation is not ready yet....<br/>Still working on it...';
    var scriptfile = 'ExtractSlidesNotes_PPTX_images.php';
    var formdata = new FormData();
    formdata.append('ACCESS', _('classroomid').value);
    formdata.append('uncloudlink', uncloudlink);
    var xhr = new XMLHttpRequest();

    xhr.open('POST', scriptfile);
    xhr.upload.addEventListener('progress', progressHandler, false);
    xhr.addEventListener('load', completeHandler, false);
    xhr.timeout = 30000;
    xhr.ontimeout = (e) => {

        _('spinnertxt').innerHTML='Your PPTX presentation is too long to be imported directly.<br/>Please convert it first with the OMIST Toolbox and use the generated html file instead.';
        alert('Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.');
    };
    xhr.send(formdata);
    //AbortTimeout(xhr,"SlideNotesUNCloud");
}

/**
 * @summary The function that is executed for reading a new HTML presentation stored on UNCloud
 * 
 * @param {string} uncloudlink A public link on Nantes University cloud (UNCloud) that points to a HTML capsule file created with the OMIST Tools.
 */

function SlideNotesUncloudHtml(uncloudhtmllink) {
    _('spinnertxt').innerHTML='Your presentation is not ready yet....<br/>Still working on it...';

    var scriptfile = 'ExtractSlidesNotesAppHTML.php';
    var formdata = new FormData();
    formdata.append('ACCESS', _('classroomid').value);
    formdata.append('uncloudlink', uncloudhtmllink);
    var xhr = new XMLHttpRequest();

    xhr.open('POST', scriptfile);
    xhr.upload.addEventListener('progress', progressHandler, false);
    xhr.addEventListener('load', completeHandler, false);
    xhr.timeout = 30000;
    xhr.ontimeout = (e) => {

        _('spinnertxt').innerHTML='No presentation yet...<br/>Please go to the complete lesson tab to import it.';
        alert('Your presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.');
    };
    xhr.send(formdata);
    //AbortTimeout(xhr,"SlideNotesUncloudHtml");
}


/**
 * @summary Function that update the "Partial" div wit translation is required.
 * 
 * @param {string} ch the text that is adding to the partial div.
 */

function EcrireTranslatePartial(ch, callback, forceTranslate = false) {
    var linput = builtin_language.substr(0, 2).toLowerCase();
    if (ch.indexOf(':|:') >= 0) { // If there are a language indication
        var Tch = ch.split(':|:');
        linput = Tch[0].substr(0, 2).toLowerCase();
        ch = Tch[1];
    }
    var loutput = subtitle_language.substr(0, 2).toLowerCase();
    if (linput == loutput) {
        EcrirePartial(ch);
        if (callback) callback(ch);
    } else {
            if (forceTranslate || LiveTranslationNum % LiveTranslationFreq == 0) {
                lastSentenceVO = ch;
                totalTranslated += ch.length;
                TranslateThen(ch, loutput, linput, function(translatedText) {
                    lastSentenceTranslated = translatedText;
                    EcrirePartial(lastSentenceTranslated);
                    if (callback) callback(lastPartialTranslation);
                },forcelibre=false);
            } else {
                EcrirePartial(lastSentenceTranslated);
                if (callback) callback(lastPartialTranslation);
            }
            LiveTranslationNum++;
    }
}


/**
 * @summary StartSpeechRecognition is executed each time a speech recognition is required. The callback function, typically a translation function is fired each time a new text is recognized.
 * 
 * @param {function} callback function that is called each time a new text is recognized.
 * @returns
 */

function StartSpeechRecognition(callback) {

    builtin_recognition.lang = builtin_language;
    builtin_recognition.onresult = function(event) {
        if (builtin_recognitionon && !builtin_externaltranscritionon && !builtin_microsofttranscritionon) {
            //console.log(event.results);
            var partial = '';
            var isFinalResult = false;
            for (var i = event.resultIndex; i < event.results.length; i++) {
                if (event.results[i].isFinal) {
                    partial = event.results[i][0].transcript; //+" > ";
                    isFinalResult = true;
                    FreeResources();
                    LiveTranslationNum = 0;
                    lastSentenceVO = '';
                } else {
                    partial += ' ' + event.results[i][0].transcript;
                }
            }
            if (partial.trim() != '') {
                if (_('recognitionon').checked) {
                    if (isFinalResult) {
                        EcrireTranslatePartial(partial, function(translatedText) {
                            // Écrire le texte original uniquement si pas de traduction nécessaire
                            if (builtin_language === subtitle_language) {
                                EcrireNBonly(partial);
                            }
                            // Sinon: la traduction sera gérée par Traduction() via callback(partial)
                        });
                        callback(partial);
                    } else {
                        EcrireTranslatePartial(partial);
                    }
                }
            }
            //builtin_recognition.stop();
        }
    };
    builtin_recognition.onend = function(event) {
        if (builtin_recognitionon) {
            StartSpeechRecognition(callback);
        }
    };
    builtin_recognitionon = true;
    try {
        builtin_recognition.start();
    } catch {}
}

/**
 * @summary StopSpeechRecognition is used to stop a running speech recognition service
 * 
 */

function StopSpeechRecognition() {
    builtin_recognitionon = false;
    builtin_recognition.stop();
}


/**
 * @summary InitVoices is executed when the HTML page is loaded.
 * 
 */

function InitVoices() {
    PopulateVoicesIn();
    PopulateVoicesOut();
    //setLanguageInput(0);
    //setLanguageOutput(0);
}

/**
 * @summary Function that update the "notebook" div.
 * 
 * @param {string} ch the text that is adding to the notebook.
 */

function EcrireNB(ch) {
    // if (_('notebook').innerHTML == "")
    //  _('notebook').innerHTML=ch;
    //else 
    _('notebook').innerHTML = _('notebook').innerHTML + ch + '<br/>';
    //EcrireTitle(ch);
}

/**
 * @summary Function that update the "notebook" div.
 * 
 * @param {string} ch the text that is adding to the notebook.
 */

function EcrireNBonly(ch) {
    // var currentContent = getNotebookContent('notebook');
    // logNotebookEvent('EcrireNBonly', 'notebook', ch, currentContent);
    _('notebook').innerHTML = _('notebook').innerHTML + ch + '<br/>';
}

/**
 * @summary Function that update the "notebook VO" div.
 * 
 * @param {string} ch the text that is adding to the notebook.
 */

function EcrireNBonlyVO(ch) {
    // var currentContent = getNotebookContent('notebookVO');
    // logNotebookEvent('EcrireNBonlyVO', 'notebookVO', ch, currentContent);
    _('notebookVO').innerHTML = _('notebookVO').innerHTML + ch + '<br/>';
}


/**
 * @summary Function that update the "title" div - REMOVED: unused dead code
 * This function was used to update #titre element which is no longer used.
 * All calls to this function are commented out in the codebase.
 */
/**
 * @summary Function that update the "partial title" div.
 * 
 * @param {string} ch the text that is adding to the title.
 */

function EcrirePartial(ch) {
    // console.log('EcrirePartial');
    // var currentContent = getNotebookContent('partialtitre');
    // logNotebookEvent('EcrirePartial', 'partialtitre', ch, currentContent);
    // Vérifier que ch est défini et non vide
    if (ch === undefined || ch === null || ch === 'undefined') {
        console.error('Texte non défini ou vide.');
        return;
    }

    // Mémoriser la dernière traduction affichée dans la zone partielle
    lastPartialTranslation = ch;

    // Mettre à jour l'interface
    if (ch != 'undefined' && ch != undefined) {
        _('partialtitre').innerHTML = ch;
    }

    // Vérifier que ROOMID est défini et non vide
    var roomId = _('classroomid').value;
    if (!roomId) {
        console.error('ROOMID est vide ou non défini.');
        return;
    }

    // Créer la requête GET avec les paramètres encodés
    var xhr = new XMLHttpRequest();
    var url = 'TranscriptionSub.php?texte=' + encodeURIComponent(ch) + '&ROOMID=' + encodeURIComponent(roomId);
    xhr.open('GET', url, true);

    // Gestion de la réponse
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                if (xhr.responseText.trim() !== '') {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.status === 'error') {
                            console.error('Erreur serveur :', data.message);
                        }
                    } catch (error) {
                        console.error('Erreur de parsing JSON. Réponse brute :', xhr.responseText);
                    }
                } else {
                    console.error('Réponse vide du serveur.');
                }
            } else {
                console.error('Erreur HTTP :', xhr.status, xhr.responseText);
            }
        }
    };
    /*if (ch != 'undefined' && ch != undefined) _('partialtitre').innerHTML = ch;
    console.log("CH : "+ch);
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "TranscriptionSub.php?texte=" + ch + "&ROOMID=" + _('classroomid').value, true);*/

    /*xhr.open("POST", "TranscriptionSub.php", true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    
    var params = `texte=${encodeURIComponent(ch)}&ROOMID=${encodeURIComponent(_('classroomid').value)}`;
    xhr.addEventListener("load", function (e) {var data=JSON.parse(e.target.responseText);}, false);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);

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


/**
 * @summary Function that update the "output" div.
 * 
 * @param {string} ch the text that is adding to the title.
 */

function Ecrire(ch) {
    var outputElement = _('output');
    if (!outputElement) return;
    if (outputElement.innerHTML == '')
        outputElement.innerHTML = ch;
    else outputElement.innerHTML = ch + '<br/>' + outputElement.innerHTML;
}

/**
 * @summary Save a given text to the text buffer that is sent to the student App.
 * 
 * @param {string} ch the text to be saved.
 */

function saveText(ch) {

    var inputlanguage = builtin_language;
    if (ch.indexOf(':|:') >= 0) { // If there are a language indication
        var Tch = ch.split(':|:');
        inputlanguage = Tch[0];
        ch = Tch[1];
    }
    var request = new XMLHttpRequest();
    request.open('POST', 'savetext.php');
    var f = new FormData();
    f.append('lineid', inputnumber);
    inputnumber++;
    f.append('text', ch);
    f.append('inputlanguage', inputlanguage);
    f.append('ROOMID', _('classroomid').value);
    f.append('LastLines', MaxSavedLines);
    f.append('room_token', window.ROOM_TOKEN);
    request.send(f);
    AbortTimeout(request,'saveText');

    //	request.onload = function(event) {};
}

/**
 * @summary Save text with source and translated versions
 * @param {string} sourceText - Text in source language
 * @param {string} translatedText - Text in target language
 */
function saveTextWithTranslation(sourceText, translatedText) {
    var inputlanguage = builtin_language || '';
    var targetLanguage = subtitle_language || '';
    
    // Ensure we have valid data before sending
    if (!inputlanguage || !targetLanguage || !sourceText) {
        console.error('saveTextWithTranslation: missing required parameters');
        return;
    }

    var request = new XMLHttpRequest();
    request.open('POST', 'savetext.php');
    var f = new FormData();
    f.append('lineid', inputnumber);
    inputnumber++;
    f.append('text', sourceText);
    f.append('inputlanguage', inputlanguage);
    f.append('targetlanguage', targetLanguage);
    f.append('translatedtext', translatedText);
    f.append('ROOMID', _('classroomid').value);
    f.append('LastLines', MaxSavedLines);
    f.append('room_token', window.ROOM_TOKEN);
    request.send(f);
    AbortTimeout(request, 'saveTextWithTranslation');
}

/**
 * @summary Save the entire notebook (several lines at a time)
 * 
 * @deprecated
 * @param {string} ch a <br/> separated list of lines.
 */

/*
function saveTextNB(ch) {
    // Deprecated: batch-save of notebook is not used in current flows.
    console.warn('saveTextNB() is deprecated and has been disabled.');
    return;
}
*/

/**
 * @summary Launch the looping function for reading the CHAT queue
 * 
 */

function LaunchReadTextQ() {
    readTextQ();
    setTimeout(LaunchReadTextQ, delayReadtextQ);
}


/**
 * @summary Launch the looping function for reading the REMOTE queue
 * 
 */

function LaunchRemote() {
    remote();
    setTimeout(LaunchRemote, delayRemote);
}


/**
 * @summary Launch the looping function for reading the STUDENT queue
 * 
 */

function LaunchStudentReadText() {
    StudentreadText();
    setTimeout(LaunchStudentReadText, delayStudentReadtext);
}

/**
 * @summary Launch the looping function that indicates when the Feedback bar is ready 
 * 
 */

function LaunchFeedbackReady() {
    //_('debugspan').innerHTML='Time remaining = '+FeedbackQueue+'ms';
    if (FeedbackQueue > 0) FeedbackQueue-=delayFeedback; else FeedbackQueue=0;
    setTimeout(LaunchFeedbackReady, delayFeedback);
}


/**
 * @summary A function that allows to stop all the loops and hence that free the ressources
 * 
 */

function FreeResources() {
    delayStudentReadtext = 1000;
    delayReadtextQ = 1000;
    delayRemote = 1000;
    setTimeout(function() {
        delayStudentReadtext = 500;
        delayReadtextQ = 100;
        delayRemote = 200;
    }, 2000);
}

/**
 * @summary A function that is launched when the HTML page is loaded
 * 
 */

function Lancer() {

    LaunchRemote();
    startChatPolling();
    //LaunchStudentReadText();
    //LaunchFeedbackReady();
    //applyConfig();
}



/**
 * @summary A function that is launched when the HTML page is loaded
 * 
 */

function Init() {
    PopulateSubtitlesLang();
    PopulateSpeechLang();

    /*  _('question').addEventListener('keyup', function (event) {
        if (event.code === 'Enter') {
            saveTextQ(_('question').value);
          event.preventDefault();
          event.stopPropagation();
        }
      },false);
    */
    //resizeSlides();

    document.onkeyup = function(event) {
        if (inSlideTab) {
            if (event.code === 'ArrowDown') {
                Forward();
            }
            if (event.code === 'ArrowUp') {
                Backward();
            }
            if (event.code === 'ArrowRight') {
                SlideForward();
            }
            if (event.code === 'ArrowLeft') {
                SlideBackward();
            }
            if (event.code === 'PageDown') {
                SlideForward();
            }
            if (event.code === 'PageUp') {
                SlideBackward();
            }
            if (event.code === 'KeyB') {
                Micro();
            }
            if (event.code === 'Space') {
                Micro();
            }

            event.preventDefault();
            event.stopPropagation();
        }
        else {
            if (event.code === 'KeyB') {
                Micro();
            }
            if (event.code === 'Space') {
                Micro();
            }

            event.preventDefault();
            event.stopPropagation();
        }
    };

    // document.onkeyup = function(event) {
    //     if (event.code === 'KeyB') {
    //         if (inSlideTab) {
    //             Micro();addEventListener
    //         }
    //     }
    //     if (event.code === 'Space') {
    //         if (inSlideTab) {
    //             Micro();
    //         }
    //     }

    //     event.preventDefault();
    //     event.stopPropagation();
    // };

    document.onkeydown = function(event) {
        if (inSlideTab) {
            event.preventDefault();
            event.stopPropagation();
        }
    };
    document.onkeypress = function(event) {
        if (inSlideTab) {
            event.preventDefault();
            event.stopPropagation();
        }
    };


    //setTimeout(Micro,1000);
    //setTimeout(Micro,1500);

    /*if (_('embedwooclapon').checked) {
        _('spanembedwooclapon').innerHTML = 'Yes !';
        show('titlewooclap');
    } else {
        _('spanembedwooclapon').innerHTML = 'No.';
        hide('titlewooclap');
    }*/
    if (zoomid != '') startZoom(zoomid);
    if (uncloudlink != '') SlideNotesUncloud(uncloudlink);
    if (uncloudhtmllink != '') SlideNotesUncloudHtml(uncloudhtmllink);
    if (targettab > 0) _('ttab-' + targettab).click();

    // Adjust a few parameters according to the url arguments
    var base = new URL(window.location.toLocaleString()).searchParams;
    if (base.has('WhichTranslator')) _('whichtranslator').value = base.get('WhichTranslator');


}

// Special ZOOM session
/**
 * @summary Adds a new tab pointing to an external tool
 * 
 * @param {string} url URL of the desired page.
 */

function opentab_url(url) {
    var tabnumber = 1;
    while (tabnumber < 4 && _('extraurl' + tabnumber).value != '') tabnumber++;
    _('extraurl' + tabnumber).value = url;
    _('embedET' + tabnumber).checked = true;
    showET(_('embedET' + tabnumber), tabnumber);
}

/**
 * @summary Open a new tab containing a Zoom session.
 * 
 * @param {string} zoomid An ID of a Nantes University Zoom session
 */

function startZoom(zoomid) {
    var lnk = 'https://univ-nantes-fr.zoom.us/wc/join/' + zoomid;
    opentab_url(lnk);
    if (window.confirm('Are you the host for this zoom session ?')) {
        window.open('https://univ-nantes-fr.zoom.us/wc/' + zoomid + '/start');
    }
}



/**
 * @summary Change the classroom language
 * 
 * @param {string} l the two letters code of the language.
 */

function setLanguageStr(l) {

    // Try to find the language in the language list
    var s = _('classroomlanguage');
    var finded = -1;
    for (var i = 0; i < s.options.length && finded == -1; i++) {
        if (l.substr(0, 2).toLowerCase() == s.options[i].innerHTML.substr(0, 2).toLowerCase()) finded = i;
    }
    if (finded != -1) {
        s.selectedIndex = finded;
        setLanguageInput(_('classroomlanguage').value);
        Micro();
        Micro();
    }
}

/**
 * @summary Read the REMOTE queue
 * 
 */

function remote() {
    if (_('classroomid').value != '') {
        // Check if ROOM_TOKEN exists and is not empty
        if (typeof window.ROOM_TOKEN === 'undefined' || window.ROOM_TOKEN === '') {

            return;
        }

        var request = new XMLHttpRequest();
        request.open('POST', 'saveremoteorder.php');
        var f = new FormData();
        f.append('ROOMID', _('classroomid').value);
        f.append('APPID', _('appid').value);
        f.append('room_token', window.ROOM_TOKEN);
        f.append('ORDER', '');
        request.send(f);
        AbortTimeout(request,'remote');

        request.onload = function(event) {
            // Check for HTTP errors
            if (request.status !== 200) {

                return;
            }

            var order = request.responseText;
            if (order != '') {

                if (order == 'forward') Forward();
                if (order == 'backward') Backward();
                if (order == 'skipforward') SkipForward();
                if (order == 'skipbackward') SkipBackward();
                if (order == 'playcurrent') {
                    SkipBackward();
                    Forward();
                }
                if (order.substr(0, 'micro'.length) == 'micro') {
                    var fromremote = order.substr('micro'.length).trim();
                    if (fromremote == _('appid').value || fromremote == '') Micro();
                }
                if (order.substr(0, 'language'.length) == 'language') {
                    var options = order.substr('language'.length).trim().split('|');
                    var to_lang = options[0].trim();
                    var fromremote = options[1].trim();

                    if (fromremote == _('appid').value || fromremote == '') setLanguageStr(to_lang);
                }
            }
        };

        // Add error handler for network errors
        request.onerror = function(event) {

        };
    }
}

/**
 * @summary Update the number of connected users
 * 
 */

function updateUsers() {
    _('connectedusers').innerHTML = (NBconnectedusers) + ' connected users';
    _('connectedusers').title = Tconnectedusers.join(', ');
}

/**
 * @summary Read the CLASSROOM queue
 * Now using centralized readTextQ from chat-common.js
 * Old implementation kept for reference (commented out)
 * 
 */

/*
function readTextQ() {
    var classroomidEl = _('classroomid');
    if (classroomidEl && classroomidEl.value != '') {
        var request = new XMLHttpRequest();
        request.open('POST', 'readtextQ.php');
        var f = new FormData();
        f.append('lineid', 0);
        f.append('ROOMID', classroomidEl.value);
        f.append('room_token', window.ROOM_TOKEN);
        request.send(f);
        AbortTimeout(request,'readTextQ');
        request.onload = function(event) {
            var Told = request.responseText.split('\n');
            var k = 0;
            var T = [];
            for (var i = 0; i < Told.length; i++) {
                if (Told[i].split(';').length > 2) T.push(Told[i]);
            }
            var nbnewQ = 0;
            for (var i = inputnumberQ; i < T.length; i++) {
                var L = T[i].split(';');
                if (L[2].indexOf('[newstudentarrived]') >= 0) {
                    NBconnectedusers++;
                    Tconnectedusers.push(L[0]);
                    updateUsers();
                } else {
                    if (L[2].indexOf('[newstudentlived]') >= 0) {
                        NBconnectedusers--;
                        Tconnectedusers.filter(student => student != L[0]);
                        updateUsers();
                    } else {

                        nbnewQ++;
                        if (builtin_language.substr(0, 2) != L[1].substr(0, 2)) {
                            TranslateQ(L[2], builtin_language.substr(0, 2), L[1].substr(0, 2), L[0], i);

                            // TranslateQ(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2), L[0],i);
                        } else {
                            EcrireCR(L[0], L[2], i);
                        }
                    }
                    }
            }
            // Is there a new question there ?
            if (nbnewQ > 0) EventNewQuestion();
            //if (inputnumberQ!=T.length) EventNewQuestion();
            inputnumberQ = T.length;
        };
    }
}
*/

/**
 * @summary Looping function that is executed to animate the question icon
 * 
 */

function draw() {
    if (timenew_question > 0) {
        timenew_question--;
        setTimeout(draw, 1000);
    } else {
        var tab = _('questionicon');
        if (tab) {
            tab.innerHTML = '<i class=\'material-icons\'>thumb_up</i>';
            tab.style.backgroundColor = 'green';
        }
    }
}


/**
 * @summary Event that is fired when a new question arrives
 * 
 */

function EventNewQuestion() {
    if (timenew_question == 0) {
        timenew_question = 10;
        var tab = _('questionicon');
        if (tab) {
            tab.innerHTML = '<i class=\'material-icons blink\'>warning</i>';
            tab.style.backgroundColor = 'orange';
            draw();
        }
    } else timenew_question = 10;
}

/**
 * @summary Save a new question in the classroom queue
 * 
 * @param {string} ch Text of the question
 * @returns
 */

function saveTextQ(ch) {
    var request = new XMLHttpRequest();
    request.open('POST', 'savetextQ.php');
    var f = new FormData();
    f.append('lineid', 'Intervenant');
    //inputnumberQ++;
    f.append('text', ch);
    f.append('inputlanguage', builtin_language);
    var classroomidEl = _('classroomid');
    f.append('ROOMID', classroomidEl ? classroomidEl.value : '');
    f.append('room_token', window.ROOM_TOKEN);
    request.send(f);
    AbortTimeout(request,'saveTextQ');
    request.onload = function(event) {
        readTextQ();
    };
}

/**
 * @summary Translate the text of a question from a language to another
 * 
 * @param {string} ch Text of the question
 * @param {string} l output language
 * @param {string} linput input language
 * @param {string} name Person who ask the question
 * @param {string} order order of the question
 * @returns
 */

function TranslateQ(ch, l, linput, name, order) {


    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'Translate.php', true);

    var formData = new FormData();
    formData.append('texte', ch);
    formData.append('lang', l);
    formData.append('langinput', linput);
    var classroomidEl = _('classroomid');
    formData.append('ROOMID', (classroomidEl && classroomidEl.value) ? classroomidEl.value : '');
    formData.append('room_token', window.ROOM_TOKEN);

    // Ajouter whichtranslator (apikey gere via config.php)
    var whichtranslatorEl = _('whichtranslator');
    var whichtranslator = (whichtranslatorEl && whichtranslatorEl.value) ? whichtranslatorEl.value : '';
    if (whichtranslator) formData.append('whichtranslator', whichtranslator);

    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE) {

            if (this.status === 200) {
                try {
                    var response = JSON.parse(this.responseText);

                    if (response && !response.error && response.data && response.data.translations) {
                        var translatedText = response.data.translations[0].text;
                        EcrireCR(name, translatedText, order);
                    } else {
                        console.error('Erreur de traduction. Réponse :', response);
                        EcrireCR(name, '---- Translation ERROR ----', order);
                    }
                } catch (error) {
                    console.error('Erreur de parsing JSON :', error);
                    EcrireCR(name, '---- JSON Parse ERROR ----', order);
                }
            } else {
                console.error('Erreur HTTP :', this.status);
                EcrireCR(name, `---- HTTP ERROR ${this.status} ----`, order);
            }
        }
    };

    xhr.onerror = function() {
        console.error('Erreur réseau dans TranslateQ.');
        EcrireCR(name, '---- Network ERROR ----', order);
    };

    xhr.send(formData);
    AbortTimeout(xhr, 'TranslateQ');
}
/*function TranslateQ(ch, l, linput, name, order) {
    console.log("TranslateQ");
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput+TranslatorInfo(), true);

    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            // console.log(this.responseText);
            var response = JSON.parse(this.responseText);
            if ((!response["error"]) && response["data"]["translations"]) {
                // Parle(response["data"]["translations"][0]["translatedText"]);
                EcrireCR(name, response["data"]["translations"][0]["translatedText"], order);
            } else {
                //EcrireCR("----","---- Translation ERROR ----")

            }

        }
    };
    xhr.send();
    AbortTimeout(xhr,"TranslateQ");
}*/

/**
 * @summary unused and obsolete function
 * 
 * @deprecated
 * @unused
 */

/*
function FromCompleteLessonToTable() {
    // Deprecated helper - not used. Kept for reference.
    console.warn('FromCompleteLessonToTable() is deprecated and has been disabled.');
    return;
}
*/

/**
 * @summary Do nothing useless ? probably unused
 * 
 * @deprecated
 * @unused
 */

/*
function playlesson() {}
*/

/**
 * @summary Switch the fullscreen mode on and off
 * 
 */

function GoFS() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        //_('bar').style.transform = "scaleY(0.1)";
        //_('bar').style.bottom="10px";
        //resizeSlides();
    } else {
        document.exitFullscreen();
        _('bar').style.transform = 'scaleY(1)';
        //resizeSlides();
    }
}

/**
 * @summary Minimize toolbar when in full screen mode
 * 
 */

function reduceToolbar() {
    if (document.fullscreenElement) {
        //_('bar').style.transform = "scaleY(0.1)";
    } else {
        _('bar').style.transform = 'scaleY(1)';
    }
}

/**
 * @summary Resize the slides window
 * 
 */

function resizeSlides() {

    var slide = _('img_slides');
    if (!slide || !slide.width || !slide.height) {
        return;
    }
    var ratio = slide.width / slide.height;
    var aggrandissmentX = (window.innerWidth - 30) / slide.width;
    var aggrandissmentY = (window.innerHeight - 80) / slide.height;
    if (aggrandissmentX > aggrandissmentY) {
        /*
        slide.width=aggrandissmentY*slide.width;
        slide.style.width=aggrandissmentY*slide.width+"px";
        slide.height=aggrandissmentY*slide.height;
        slide.style.height=aggrandissmentY*slide.height+"px";
        */
        slide.style.transform = 'scaleX(' + aggrandissmentY + ') scaleY(' + aggrandissmentY + ')';
        //    slide.style.transform='scaleX('+aggrandissmentX+') scaleY('+aggrandissmentY+')';
    } else {
        /*    slide.width=aggrandissmentX*slide.width;
            slide.style.width=aggrandissmentX*slide.width+"px";
            slide.height=aggrandissmentX*slide.height;
            slide.style.height=aggrandissmentX*slide.height+"px";
        */
        slide.style.transform = 'scaleX(' + aggrandissmentX + ') scaleY(' + aggrandissmentX + ')';

        //slide.style.transform='scale('+aggrandissmentX+')';

    }

    // For wooclap and others
    //["wooclap", "extratab1", "extratab2", "extratab3", "extratab4", "visio"].map(resizeWindow)
}
window.onresize = resizeSlides;

/**
 * @summary (Pincode system removed - using QR code/direct link instead)
 * 
 */


/**
 * @summary Switch a given extratab visible or invisible
 * 
 * @param {HTMLInput checkbox} obj a checkbox.
 * @param {number} num the tab number.
 * @returns
 */

function showET(obj, num) {
    if (obj.checked) {
        var url = _('extraurl' + num).value;
        if (url != '') {
            _('spanembedET' + num).innerHTML = 'Yes !';
            show('titleextratab' + num);
            _('extratab' + num).src = url;
        } else {
            alert('Enter your url first !');
            obj.checked = false;
            _('spanembedET' + num).innerHTML = 'No.';
            hide('titleextratab' + num);
        }
    } else {
        _('spanembedET' + num).innerHTML = 'No.';
        hide('titleextratab' + num);
    }
}

/**
 * @summary Switch the Microphone/Speechrecognition on and off
 * 
 */

function Micro() {
    if (!builtin_microsofttranscritionon) {
        builtin_recognition.abort();
    }
    var mybox = _('micon');
    _('recognitionon').checked = !_('recognitionon').checked;
    builtin_recognitionon = _('recognitionon').checked;
    if (_('recognitionon').checked) {
        mybox.innerHTML = '<i class=\'material-icons blink\'>record_voice_over</i>';
    } else {
        mybox.innerHTML = '<i class=\'material-icons\'>voice_over_off</i>';
    }
    if (builtin_microsofttranscritionon) {
        if (builtin_recognitionon) {
            doContinuousRecognition(MicrosoftKey, MicrosoftRegion, builtin_language);
        } else {
            stopRecognizer();
            
        }
    }
    if (_('recognitionon').checked && !builtin_microsofttranscritionon) {
        //LaunchStudentReadText();
        StartSpeechRecognition(Traduction);
    }
    /*else {
            StopStudentReadText();
        }*/

}



/**
 * @summary Update the scroll bar value
 * 
 * @param {number} l current value.
 * @returns
 */

function setScrollvalues(l) {
    currentscroll = 1;
    var txtlength = Math.max(20, l);
    //txtlength=Math.min(200,txtlength);
    var duration = 55 * txtlength; // estimated duration in ms
    currentindent = scrollinterval / duration;
}

/**
    // _('FSimage').style.height = size+'px';
}

/**
 * @summary Show the selection bar in the information menu
 * 
 * @deprecated ?
 */

function showEachIcon() {} // Fonction conservée pour compatibilité, mais vide (éléments supprimés)

/**
 * @summary Generate the HTML code allowing to embed a Chat window
 * 
 */

function Embedcode() {
    var url = _('chatlink').href;
    //  navigator.clipboard.writeText('<iframe style="width:100%; overflow: auto; height: 500px" src="'+url+'"></iframe>');
    //  alert("HTML code copied to clipboard !");
    _('embedcontent').value = '<iframe style="width:100%; overflow: auto; height: 500px" src="' + url + '"></iframe>';
}

/**
 * @summary Send the notebook content by mail
 * 
 */

function sendnotebook() {
    _('dialog_MailFrom').value = 'bourdon-j@univ-nantes.fr';
    _('dialog_MailTo').value = 'your.mail@univ-nantes.fr';
    _('dialog_MailSubject').value = 'Content of the lesson ' + _('classroomid').value;
    _('dialog_MailBody').innerHTML = _('notebook').innerHTML;
    $('#dialog-mail').dialog('open');
}



/**
 * @summary Open a new tab
 * 
 * @param {HTMLDiv} e an HTML div containing the url.
 * @returns
 */

function opentab(e) {
    var url = e.innerHTML;
    var tabnumber = 1;
    while (tabnumber < 4 && _('extraurl' + tabnumber).value != '') tabnumber++;
    _('extraurl' + tabnumber).value = url;
    _('embedET' + tabnumber).checked = true;
    showET(_('embedET' + tabnumber), tabnumber);
}


/**
 * @summary Update the configuration code according to the current configuration
 * 
 */

function updateConfig() {
    var newConfig = [];
    newConfig[0] = String.fromCharCode(65 + _('classroomlanguage').selectedIndex);
    newConfig[1] = String.fromCharCode(65 + _('subtitlelanguage').selectedIndex);
    currentconfig = newConfig.join('');
    // Configuration simplifiée : seuls classroomlanguage et subtitlelanguage sont conservés
}

/**
 * @summary Apply a configuration (at startup)
 * 
 */

function applyConfig() {

    var config = window.pageData.currentconfig;
    if (config.length < 2) return; // Seuls les 2 premiers caractères (langues) sont utilisés
    // classroomlanguage
    _('classroomlanguage').selectedIndex = 1 * (config.charCodeAt(0) - 65);
    //setLanguageInput(_('classroomlanguage').value);
    // subtitlelanguage
    _('subtitlelanguage').selectedIndex = 1 * (config.charCodeAt(1) - 65);
    setLanguageSubtitle(_('subtitlelanguage').value);
    // Les autres paramètres de config ne sont plus utilisés (éléments supprimés)
}

/**
 * @summary Function that fix the subtitle bar at the bottom of the page
 * 
 * @deprecated - Removed: using CSS positioning instead
 */

// function fixTitle() {
//     for (var f in {
//             'partialtitre': 1
//         }) {
//         var e = document.getElementById(f);
//         e.style.top = (window.innerHeight - e.style.height - 200 - 10) + 'px';
//     }
// 
//     var e = document.getElementById('titre');
//     e.style.top = (window.innerHeight - e.style.height - 100 - 10) + 'px';
// }

/**
 * @summary Function that allows to keep the partial title bar inside the window
 * 
 * @todo Do not work properly - Removed: using CSS positioning
 */

// function MinTitlePos() {
//     for (var f in {
//             'partialtitre': 1
//         }) {
//         var e = document.getElementById(f);
//         var postop = 1 * (e.style.top.replace('px', ''));
//         if (postop > window.innerHeight - 50) {
//             e.style.top = (window.innerHeight - 50) + 'px';
//         }
//     }
// }

/**
 * @summary Callback function when the question field (Classroom tab) has changed
 * 
 */

function sendclick() {
    var tosend = document.getElementById('question').value.replaceAll('\n', '').trim();
    //alert("Will send : ("+tosend+")");
    if (tosend != '') saveTextQ(tosend);
    document.getElementById('question').value = '';
}


// ============================================================================
// PRESENTATION DISPLAY SYSTEM
// Handles file upload and PDF display using PDF.js
// All file types are converted to PDF server-side for consistent display
// ============================================================================

// PDF.js configuration - set worker path and CMap support for better font rendering
if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    
    // Add CMap support for proper character encoding in PDFs
    // This resolves "TT: undefined function" warnings
    pdfjsLib.GlobalWorkerOptions.cMapUrl = 
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/cmaps/';
    pdfjsLib.GlobalWorkerOptions.cMapPacked = true;
}

// Keyboard navigation for PDF viewer
// Allows Left/Right arrows, Home, End for navigation
// Only active when PDF viewer is visible
// Note: Space and Backspace are intentionally not used as they are reserved for other functions
document.addEventListener('keydown', function(event) {
    const pdfViewer = document.getElementById('pdf-viewer');
    
    // Only handle keyboard events when PDF viewer is active and visible
    if (!pdfDoc || !pdfViewer || pdfViewer.style.display !== 'block') {
        return;
    }

    switch(event.key) {
        case 'ArrowLeft':
            prevPage();
            event.preventDefault();
            break;
        case 'ArrowRight':
            nextPage();
            event.preventDefault();
            break;
        case 'Home':
            goToPage(1);
            event.preventDefault();
            break;
        case 'End':
            if (pdfDoc) goToPage(pdfDoc.numPages);
            event.preventDefault();
            break;
    }
});

// Global variables for PDF viewer
let pdfDoc = null;
let pageNum = 1;
let pageRendering = false;
let pageNumPending = null;
let scale = 1.5;
let canvas = null;
let ctx = null;
let currentRenderTask = null;

/**
 * Initialize PDF.js viewer elements
 */
function initPdfViewer() {
    canvas = document.getElementById('pdf-canvas');
    if (!canvas) return;
    
    ctx = canvas.getContext('2d');
}

/**
 * Render a specific PDF page
 * @param {number} num - Page number to render
 */
function renderPage(num) {
    pageRendering = true;

    // Cancel any previous render task to avoid canvas conflicts
    if (currentRenderTask) {
        currentRenderTask.cancel();
        currentRenderTask = null;
    }

    pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({ scale: scale });
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };

        const renderTask = page.render(renderContext);
        
        // Store the current render task so it can be cancelled
        currentRenderTask = renderTask;

        renderTask.promise.then(function() {
            currentRenderTask = null;
            pageRendering = false;
            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        }).catch(function(error) {
            // Handle render errors (e.g., cancelled)
            if (error.name !== 'RenderingCancelledException') {
                console.error('Render error:', error);
            }
            currentRenderTask = null;
            pageRendering = false;
            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        });

        // Update navigation UI
        updatePdfNavigation();
    });
}

/**
 * Update PDF navigation controls
 */
function updatePdfNavigation() {
    if (!pdfDoc) return;

    document.getElementById('pdf-page-indicator').textContent = 
        `Page ${pageNum} of ${pdfDoc.numPages}`;
    document.getElementById('pdf-page-input').value = pageNum;
    document.getElementById('pdf-prev').disabled = pageNum <= 1;
    document.getElementById('pdf-next').disabled = pageNum >= pdfDoc.numPages;
}

/**
 * Navigate to previous page
 */
function prevPage() {
    if (pageNum <= 1) return;
    pageNum--;
    queueRenderPage(pageNum);
}

/**
 * Navigate to next page
 */
function nextPage() {
    if (pageNum >= pdfDoc.numPages) return;
    pageNum++;
    queueRenderPage(pageNum);
}

/**
 * Go to specific page number
 * @param {number} num - Page number
 */
function goToPage(num) {
    const pageInput = document.getElementById('pdf-page-input');
    num = parseInt(num);
    if (num >= 1 && num <= pdfDoc.numPages) {
        pageNum = num;
        queueRenderPage(pageNum);
    } else {
        pageInput.value = pageNum;
    }
}

/**
 * Queue a page for rendering
 * @param {number} num - Page number
 */
function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
}

/**
 * Load and display PDF
 * @param {string} url - Path to the PDF file
 */
function displayPDF(url) {
    // Hide text viewer, show PDF viewer
    const pdfViewer = document.getElementById('pdf-viewer');
    const textViewer = document.getElementById('text-content-viewer');
    
    if (pdfViewer) pdfViewer.style.display = 'block';
    if (textViewer) textViewer.style.display = 'none';

    pdfjsLib.getDocument(url).promise.then(function(pdf) {
        pdfDoc = pdf;
        pageNum = 1;
        renderPage(1);
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
        showPresentationError('Failed to load PDF: ' + error.message);
    });
}

/**
 * Display text content (fallback for non-PDF files)
 * @param {string} content - Text content to display
 */
function displayTextContent(content) {
    const viewer = document.getElementById('text-content-viewer');
    const pdfViewer = document.getElementById('pdf-viewer');
    
    if (viewer) {
        viewer.style.display = 'block';
        viewer.style.whiteSpace = 'pre-wrap';
        viewer.style.wordWrap = 'break-word';
        viewer.textContent = content;
    }
    if (pdfViewer) pdfViewer.style.display = 'none';
}

/**
 * Toggle fullscreen mode for PDF viewer
 * Uses Fullscreen API with browser prefixes support
 */
function togglePdfFullscreen() {
    const pdfViewer = document.getElementById('pdf-viewer');
    
    if (!pdfViewer) {
        return;
    }

    // Check if already in fullscreen
    if (document.fullscreenElement || document.webkitFullscreenElement || 
        document.mozFullScreenElement || document.msFullscreenElement) {
        // Exit fullscreen with browser prefixes
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    } else {
        // Enter fullscreen with browser prefixes
        if (pdfViewer.requestFullscreen) {
            pdfViewer.requestFullscreen();
        } else if (pdfViewer.webkitRequestFullscreen) {
            pdfViewer.webkitRequestFullscreen();
        } else if (pdfViewer.mozRequestFullScreen) {
            pdfViewer.mozRequestFullScreen();
        } else if (pdfViewer.msRequestFullscreen) {
            pdfViewer.msRequestFullscreen();
        }
    }
}

// Add ESC key handler for fullscreen exit
// Only active when in fullscreen mode
document.addEventListener('keydown', function(event) {
    // Only handle ESC key when in fullscreen
    if (event.key === 'Escape' || event.keyCode === 27) {
        const isFullscreen = document.fullscreenElement || 
                            document.webkitFullscreenElement || 
                            document.mozFullScreenElement || 
                            document.msFullscreenElement;
        
        if (isFullscreen) {
            event.preventDefault();
            // Exit fullscreen with browser prefixes
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }
});

/**
 * Display error message in notebook
 * @param {string} message - Error message
 */
function showPresentationError(message) {
    const notebook = document.getElementById('notebookcompleteformatted');
    if (notebook) {
        notebook.innerHTML = `<div style="padding: 20px; color: red; text-align: center;">${message}</div>`;
    }
}

/**
 * Handle file selection for auto-upload
 * Triggers upload immediately when a file is selected
 * @param {Event} event - Change event from file input
 */
function handleFileSelect(event) {
    const fileInput = event.target;
    const file = fileInput.files[0];
    
    if (!file) {
        return;
    }

    // Get elements
    const progressBar = document.getElementById('presentation-progress');
    const progressStatus = document.getElementById('progress-status');
    
    // Initialize progress
    if (progressBar) {
        progressBar.value = 0;
    }
    if (progressStatus) {
        progressStatus.textContent = 'Preparing upload...';
    }

    // Hide viewers
    const pdfViewer = document.getElementById('pdf-viewer');
    const textViewer = document.getElementById('text-content-viewer');
    if (pdfViewer) pdfViewer.style.display = 'none';
    if (textViewer) textViewer.style.display = 'none';

    // Create FormData and upload
    const formData = new FormData();
    formData.append('presentation_file', file);

    // Simulate progress during upload (0-50%)
    const uploadProgress = setInterval(function() {
        if (progressBar && progressBar.value < 50) {
            progressBar.value = Math.min(progressBar.value + 10, 50);
            if (progressStatus) {
                progressStatus.textContent = 'Uploading... ' + progressBar.value + '%';
            }
        }
    }, 300);

    // Send to server
    fetch('/upload_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        clearInterval(uploadProgress);
        
        // Update progress to indicate server processing has started
        if (progressBar) {
            progressBar.value = 75;
        }
        if (progressStatus) {
            progressStatus.textContent = 'Converting...';
        }
        
        return response.json();
    })
    .then(data => {
        // Update progress to 100% when conversion is complete
        if (progressBar) {
            progressBar.value = 100;
        }
        if (progressStatus) {
            progressStatus.textContent = 'Processing...';
        }

        if (data.status === 'error') {
            throw new Error(data.message);
        }

        // Display based on file type
        if (data.display_method === 'pdf') {
            if (progressStatus) {
                progressStatus.textContent = 'Displaying...';
            }
            displayPDF(data.url);
        } else {
            showPresentationError('Unexpected file type: ' + data.file_type);
        }
    })
    .catch(error => {
        clearInterval(uploadProgress);
        if (progressBar) {
            progressBar.value = 0;
        }
        if (progressStatus) {
            progressStatus.textContent = 'Error: ' + error.message;
        }
        showPresentationError('Upload failed: ' + error.message);
    })
    .finally(() => {
        // Reset progress after a delay
        setTimeout(() => {
            if (progressBar) {
                progressBar.value = 0;
            }
            if (progressStatus) {
                progressStatus.textContent = '';
            }
        }, 2000);
    });
}

// Initialize presentation system when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initPdfViewer();
        const fileInput = document.getElementById('presentation_file');
        if (fileInput) {
            fileInput.addEventListener('change', handleFileSelect);
        }
    });
} else {
    initPdfViewer();
    const fileInput = document.getElementById('presentation_file');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }
}

// =============================================================================
// SURVEILLANCE DES CHANGEMENTS DANS LES NOTEBOOKS
// =============================================================================
setInterval(function() {
    var notebookEl = document.getElementById('notebook');
    var notebookVOEl = document.getElementById('notebookVO');
    var partialtitreEl = document.getElementById('partialtitre');
    
    if (notebookEl && notebookVOEl && partialtitreEl) {
        var notebookContent = notebookEl.innerHTML;
        var notebookVOContent = notebookVOEl.innerHTML;
        var partialtitreContent = partialtitreEl.innerHTML;

        // Ne logger que si le contenu a changé depuis le dernier check
        if (notebookContent !== window.lastNotebookContent ||
            notebookVOContent !== window.lastNotebookVOContent) {

            window.lastNotebookContent = notebookContent;
            window.lastNotebookVOContent = notebookVOContent;
        }
    }
}, 5000); // Vérifie toutes les 5 secondes
