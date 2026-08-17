// Basic functions

/**
 * @summary Decode HTML entities in a string
 * @param {string} text - Text containing HTML entities
 * @returns {string} Decoded text
 */
function decodeHtmlEntities(text) {
    var textarea = document.createElement("textarea");
    textarea.innerHTML = text;
    return textarea.value;
}

/**
 * @summary Fill the select VoiceOutput field
 * 
 */

function PopulateVoicesOut(selectname='VoiceOutput') {
    var select = _(selectname);
    AllOutputVoices = [];
    while (select.firstChild) {
        select.removeChild(select.firstChild);
    }
    var optgroup = document.createElement("optgroup");
    optgroup.label = "Voice and text translation";
    var voices = window.speechSynthesis.getVoices();
    // Voices in english first
    for (var i = 0; i < voices.length; i++) {
        if (voices[i].lang.substr(0, 2).toLowerCase() == 'en') { //(voices[i].localService) {
            var opt = document.createElement("option");
            opt.value = AllOutputVoices.length;
            if (voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
            voices[i].textonly = false;
            AllOutputVoices.push(voices[i]);
            opt.innerHTML = voices[i].lang + " " + voices[i].name + ((voices[i].localService) ? "" : " [external service]");
            optgroup.appendChild(opt);
        }
    }

    for (var i = 0; i < voices.length; i++) {
        if (voices[i].lang.substr(0, 2).toLowerCase() != 'en') { //(voices[i].localService) {
            var opt = document.createElement("option");
            opt.value = AllOutputVoices.length;
            if (voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
            voices[i].textonly = false;
            AllOutputVoices.push(voices[i]);
            opt.innerHTML = voices[i].lang + " " + voices[i].name + ((voices[i].localService) ? "" : " [external service]");
            optgroup.appendChild(opt);
        }
    }
    maxvoices = voices.length;
    select.appendChild(optgroup);
    var optgroup2 = document.createElement("optgroup");
    optgroup2.label = "Text translation only";
    for (var i = 0; i < supportedlanguages.length; i++) {
        var lang = supportedlanguages[i].code; //.substr(0,5);
        var opt = document.createElement("option");
        opt.value = maxvoices + i;
        AllOutputVoices.push({
            "lang": lang,
            "textonly": true
        });

        opt.innerHTML = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
        optgroup2.appendChild(opt);
    }
    select.appendChild(optgroup2);
}

/**
 * @summary Fill the select VoiceOutput field for the subtitle application
 * 
 */

function PopulateVoicesOutSub(selectname='VoiceOutput') {
    var select = _(selectname);
    AllOutputVoices = [];
    // while (select.firstChild) {
    //     select.removeChild(select.firstChild);
    // }
    
    var optgroup2 = document.createElement("optgroup");
    optgroup2.label = "Text translation only";
    for (var i = 0; i < supportedlanguages.length; i++) {
        var lang = supportedlanguages[i].code; //.substr(0,5);
        var opt = document.createElement("option");
        opt.value = maxvoices + i;
        AllOutputVoices.push({
            "lang": lang,
            "textonly": true
        });

        opt.innerHTML = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
        optgroup2.appendChild(opt);
    }
    select.appendChild(optgroup2);
}


/**
 * @summary Fill the select VoiceInput field
 * 
 * @deprecated
 */

function PopulateVoicesIn() {
    var select = _('VoiceInput');
    var select2 = _('classroomlanguage');
    var voices = window.speechSynthesis.getVoices();
    //alert(voices[0].name);
    TLangInput = [];
    for (var i = 0; i < voices.length; i++) {
        if (voices[i].localService) {
            var lang = voices[i].lang; //.substr(0,5);
            if (TLangInput.indexOf(lang) < 0) {
                var opt = document.createElement("option");
                opt.value = TLangInput.length;
                TLangInput.push(lang);
                opt.innerHTML = lang;
                select.appendChild(opt);
                var opt2 = document.createElement("option");
                opt2.value = TLangInput.length;
                opt2.innerHTML = lang;
                select2.appendChild(opt2);
            }
        }
    }
}

/**
 * @summary Fill the select VoiceInput field
 * 
 */

function PopulateVoicesIn() {
    if (TLangInput.length == 0) {
        var select = _('VoiceInput');
        var select2 = _('classroomlanguage');
        //var voices = window.speechSynthesis.getVoices();
        TLangInput = [];

        for (var i = 0; i < supportedlanguages.length; i++) {
            var lang = supportedlanguages[i].code; //.substr(0,5);
            if (TLangInput.indexOf(lang) < 0) {
                var opt = document.createElement("option");
                opt.value = TLangInput.length;
                TLangInput.push(lang);
                opt.innerHTML = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
                select.appendChild(opt);
                var opt2 = document.createElement("option");
                opt2.value = TLangInput.length;
                opt2.innerHTML = lang;
                select2.appendChild(opt2);
            }
        }
    }
}

/**
 * @summary Fill the select VoiceInput field for the subtitle application
 * 
 */

function PopulateVoicesInSub() {
    if (TLangInput.length == 0) {
        // var select = _('VoiceInput');
        var select = _('classroomlanguage');
        //var voices = window.speechSynthesis.getVoices();
        TLangInput = [];

        for (var i = 0; i < supportedlanguages.length; i++) {
            var lang = supportedlanguages[i].code; //.substr(0,5);
            if (TLangInput.indexOf(lang) < 0) {
                var opt = document.createElement("option");
                opt.value = TLangInput.length;
                TLangInput.push(lang);
                opt.innerHTML = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
                select.appendChild(opt);
                // var opt2 = document.createElement("option");
                // opt2.value = TLangInput.length;
                // opt2.innerHTML = lang;
                // select2.appendChild(opt2);
            }
        }
    }
}

function countLines(elm) {
    var el = document.getElementById(elm);
    var divHeight = el.offsetHeight;
    var lineHeight = parseInt(el.style.lineHeight);
    var lines = divHeight / lineHeight;
    return lines;
}


function auto_size_text(elm="titre", max_lines=3) {

    var el = document.getElementById(elm);
    var lines = countLines(elm);
    var size = parseInt(el.style.fontSize.replace("px", ""));
    if(lines > max_lines) {
        size--;
        el.style.fontSize = size + "px";
        el.style.lineHeight = size + "px";
        auto_size_text(elm);
    }
}

/**
 * @summary Speech synthesis of a given text
 * 
 * @param {string} txt a given text.
 */

function Parle(txt) {
    if (typeof builtin_voice !== 'undefined' && builtin_voice && !builtin_voice.textonly) {
        var discours = new SpeechSynthesisUtterance(txt);
        discours.voice = builtin_voice;
        discours.rate = builtin_discoursrate;
        discours.lang = builtin_voice.lang;
        window.speechSynthesis.speak(discours);
    }
}


/**
 * @summary Speech synthesis of a given text, then execute a callback function
 * 
 * @param {string} txt a given text.
 * @param {function} txt a callback function.
 */

function ParleThen(txt, f) {
    _('titre').innerHTML = txt;
    if (typeof builtin_voice !== 'undefined' && builtin_voice && !builtin_voice.textonly) {
        var discours = new SpeechSynthesisUtterance(txt);
        discours.voice = builtin_voice;
        discours.rate = builtin_discoursrate;
        discours.lang = builtin_voice.lang.replace('_', '-')
        discours.onend = f;
        discours.onerror = function() {

            f();
        }
        window.speechSynthesis.speak(discours);
    } else setTimeout(f, 10000);
}

/**
 * @summary Apply a configuration (at startup) for the subtitle application
 * 
 */

function applyConfigSub() {
    var config = currentconfig;

    if (config.length != "ABYANACNNCNNNYYYYN".length) return;
    // classroomlanguage
    _('classroomlanguage').selectedIndex = 1 * (config.charCodeAt(0) - 65);
    setLanguageInput(_('classroomlanguage').value);


    // subtitlelanguage
    _('VoiceOutput').selectedIndex = 1 * (config.charCodeAt(1) - 65);
    setLanguageOutput(_('VoiceOutput').value);


    // subtitlebaron
    if (config.charAt(2) == "Y") {
        _('titre').style.visibility = 'visible';
    } else {
        _('titre').style.visibility = 'hidden';
    }
    // subtitlepartialbaron
    if ((config.charAt(4) == "Y")) {
        _('VOBanner').style.visibility = 'visible';
        showBanner('VOBanner');
    } else {
        _('VOBanner').style.visibility = 'hidden';
        hideBanner('VOBanner');
    }
    // ColorTitre
    var colorTitreEl = _('ColorTitre');
    if (colorTitreEl) {
        colorTitreEl.selectedIndex = 1 * (config.charCodeAt(3) - 65);
        ChangeColor('titre', colorTitreEl.value);
    } else {
        ChangeColor('titre', '0,0,255|0,0,255');
    }
    // ColorPartialTitre
    _('ColorPartialTitre').selectedIndex = 1 * (config.charCodeAt(5) - 65);
    ChangeColor('VOBanner', _('ColorPartialTitre').value);
    // selectfontsize
    _('selectfontsize').selectedIndex = 1 * (config.charCodeAt(6) - 65);
    _('titre').style.fontSize = _('selectfontsize').value;
    _('VOBanner').style.fontSize = _('selectfontsize').value;
    //selectopacity
    var selectOpacityEl = _('selectopacity');
    if (selectOpacityEl) {
        selectOpacityEl.selectedIndex = 1 * (config.charCodeAt(9) - 65);
        ChangeOpacity('titre', selectOpacityEl.value);
        ChangeOpacity('VOBanner', selectOpacityEl.value);
    } else {
        ChangeOpacity('titre', '0.5');
        ChangeOpacity('VOBanner', '0.5');
    }

    //document.getElementById('synthesison').checked=true;


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

/**
 * @summary Debug function for counting the number of pending requests
 * 
 */

function increasependingrequests() {
    var nb = _('nbrequests');
    if (!nb) return;
    nb.innerHTML = nb.innerHTML * 1 + 1;
}

/**
 * @summary Debug function for counting the number of pending requests
 * 
 */

function decreasependingrequests() {
    var nb = _('nbrequests');
    if (!nb) return;
    nb.innerHTML = nb.innerHTML * 1 - 1;
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

function Translate(ch, l, linput, lineid) {
    // Ne pas traduire si classroomid est vide ou inconnu
    if (_('classroomid').value === '' || _('classroomid').value === 'UNKNOWN') {

        decreasependingrequests();
        CompleteTranslation();
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php", true);
    increasependingrequests();

    var f = new FormData();

    f.append('texte', ch);
    f.append('lang', l);
    f.append('langinput', linput);
    f.append('ROOMID', _('classroomid').value);
    f.append('lineid', lineid);

    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

            var response = JSON.parse(this.responseText);
            if ((!response["error"]) && response["data"]["translations"]) {
                ToSay['' + lineid] = (response["data"]["translations"][0]["text"]);
                //        Ecrire(lineid+"  :  "+response["data"]["translations"][0]["translatedText"]);
                Ecrire(response["data"]["translations"][0]["text"]);
            } else {
                console.error('auditor-func.js:356 - Translation ERROR. Response:', response, 'Raw:', this.responseText);
                console.error('Text being translated:', ch, 'From:', linput, 'To:', l);
                alert('Translation error for: "' + ch.substr(0, 100) + '..."\nCheck console for details.');
                Ecrire("---- Translation ERROR ---- (see console)")
            }

        }
    };
    xhr.timeout = 9999;
    xhr.ontimeout = function() {

    };
    xhr.onloadend = function() {
        decreasependingrequests();
        CompleteTranslation();
    };
    xhr.send(f);
    AbortTimeout(xhr,"Translate");
}

/**
 * @summary Translate a given string from a language to another
 * 
 * @param {string} ch the string to translate.
 * @param {string} l output language.
 * @param {string} linput input language.
 * @param {number} lineid unique identifier for the translation.
 * @deprecated Use Translate() instead
 */

/*
function Translate2(ch, l, linput, lineid) {
    // Ne pas traduire si classroomid est vide ou inconnu
    if (_('classroomid').value === '' || _('classroomid').value === 'UNKNOWN') {

        decreasependingrequests();
        CompleteTranslation2();
        return;
    }

    var xhr = new XMLHttpRequest();
    var url = "Translate.php?texte=" + encodeURIComponent(ch)
        + "&lang=" + encodeURIComponent(l)
        + "&langinput=" + encodeURIComponent(linput)
        + "&ROOMID=" + encodeURIComponent(_('classroomid').value)
        + "&lineid=" + encodeURIComponent(lineid);
    xhr.open("POST", url, true);
    increasependingrequests();
    xhr.onloadend = function() {
        decreasependingrequests();
        CompleteTranslation2();
    };
    xhr.send();
    AbortTimeout(xhr,"Translate2");
}
*/

/**
 * @summary InitVoices is executed when the HTML page is loaded.
 * 
 */

function InitVoices() {
    PopulateVoicesOut();
    PopulateVoicesOut('VoiceOutputchoose');
    // Ouvrir le pop-up de sélection de langue maintenant que les voix sont chargées
    if (typeof openLanguageDialog === 'function') {
        openLanguageDialog();
    }
    //setLanguageOutput(_("VoiceOutput").value);
    setLanguageOutputAndRetranslate(_("VoiceOutput").value);
}

/**
 * @summary Function that update the "notebook" div.
 * 
 * @param {string} ch the text that is adding to the notebook.
 */

function EcrireNB(ch) {
    if (_('notebook').innerHTML == "")
        _('notebook').innerHTML = ch;
    else _('notebook').innerHTML = _('notebook').innerHTML + "<br/>" + ch;

    // VOBanner
    _('VOBanner').innerHTML = ch;
}

/**
 * @summary Function that update the "notebook" div for the subtitle application.
 * 
 * @param {string} ch the text that is adding to the notebook.
 */

function EcrireNBSub(ch) {
    if (_('notebook').innerHTML == "")
        _('notebook').innerHTML = ch;
    else _('notebook').innerHTML = _('notebook').innerHTML + "<br/>" + ch;
}

/**
 * @summary Function that update the "output" div.
 * 
 * @param {string} ch the text that is adding to the notebook.
 */

function Ecrire(ch) {

    if (_('output').innerHTML == "")
        _('output').innerHTML = ch;
    else _('output').innerHTML = _('output').innerHTML + "<br/>" + ch;
    _('titre').innerHTML=ch;
    //_('subtitles').innerHTML=ch;

    //bufferWC[placebufferWC] = ch;
    //placebufferWC = (placebufferWC + 1) % sizebufferWC;
    //buffer_has_changed = true;
}

/**
 * @summary Orchestrates the sequence of translations
 * 
 * @deprecated
 */

function CompleteTranslation() {
    // Vérification précoce : ne pas traduire si classroomid est invalide
    if (_('classroomid').value === '' || _('classroomid').value === 'UNKNOWN') {
        ToTranslate = [];
        if (document.getElementById('synthesison').checked) {
            CompleteSpeech();
        }
        return;
    }

    if (ToTranslate.length > 0) {
        var L = ToTranslate[0];
        ToTranslate.splice(0, 1);
        

        var id = L[0];
        var sourceLang = L[1];
        var sourceText = L[2];
        var targetLang = L[3];
        var targetText = L[4];
        
        var currentLang = '';
        if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
            currentLang = builtin_voice.lang.substr(0, 2);
        } else if (typeof builtin_language !== 'undefined' && builtin_language) {
            currentLang = builtin_language.substr(0, 2);
        }
        
        // If listener's language matches source or target language -> direct display
        if (currentLang === sourceLang.substr(0, 2)) {
            ToSay['' + id] = sourceText;
            Ecrire(sourceText);
            CompleteTranslation();
        } else if (currentLang === targetLang.substr(0, 2)) {
            ToSay['' + id] = targetText;
            Ecrire(targetText);
            CompleteTranslation();
        } else {
            // Translation needed (target language is different)
            Translate(sourceText, currentLang, sourceLang.substr(0, 2), id);
        }
    } else {
        if (document.getElementById('synthesison').checked) {
            CompleteSpeech();
        }
    }
}

/**
 * @summary Orchestrates the sequence of translations
 * @deprecated Use CompleteTranslation() instead
 * 
 * @todo Comprendre pourquoi on appelle StudentCompleteTranslation dans le sinon et pas StudentCompleteTranslation2
 */

/*
function CompleteTranslation2() {
    if (ToTranslate2.length > 0) {
        var L = ToTranslate2[0];
        ToTranslate2.splice(0, 1);
        var currentLang = '';
        if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
            currentLang = builtin_voice.lang;
        } else if (typeof builtin_language !== 'undefined' && builtin_language) {
            currentLang = builtin_language;
        }
        if (currentLang && currentLang.substr(0, 2) != L[1].substr(0, 2)) {
            // Ecrire("Push : "+L[2]);
            Translate2(L[2], currentLang.substr(0, 2), L[1].substr(0, 2), L[0]);
        } else CompleteTranslation2();
    } else setTimeout(CompleteTranslation, 10);
}
*/

/**
 * @summary Orchestrates the sequence of speeches
 * 
 */

function CompleteSpeech() {
    var maxId = -1;
    // Last ID in ToSay
    for (var key in ToSay) {
        var id = parseInt(key);
        if (id > maxId) maxId = id;
    }

    // ONLY read the last one, if there is something new
    if (maxId > currentdiscours) {
        var L = ToSay['' + maxId].replace("&nbsp;", " ");
        ParleThen(L, CompleteSpeech);
        currentdiscours = maxId;
    }
}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue).
 * 
 */

function readText() {
    if (_('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "readtext.php");
        increasependingrequests();
        currentorder_readtext++;
        var f = new FormData();
        f.append('lineid', inputnumber);
        f.append('ORDER', currentorder_readtext);
        f.append('ROOMID', _('classroomid').value);
        f.append('room_token', window.ROOM_TOKEN);
        request.send(f);
        AbortTimeout(request,"readText");
        request.onload = function(event) {
            decreasependingrequests();
            var T = request.responseText.split('\n');
            //console.log('Readtext get : '+request.responseText);
            var maxi = -1;
            var readedorder = ('0' + T[0]).split(';')[0] * 1;
            if (readedorder > maxorder_readtext) {
                maxorder_readtext = readedorder;
                for (var i = 0; i < T.length; i++) {
                    var L = T[i].split(';');
                    if (L.length > 3) {
                        if (1 * L[1] > maxi) maxi = 1 * L[1];
                        if (AllPhrases[1 * L[1]] == undefined) {
                            // Skip if classroomid is invalid
                            if (_('classroomid').value !== '' && _('classroomid').value !== 'UNKNOWN') {
                                AllPhrases[1 * L[1]] = true;
                                
                                // Decode HTML entities from source text
                                var sourceLang = L[2];
                                var sourceText = decodeHtmlEntities(L[3]);
                                var targetLang = L[4];
                                var targetText = L.length >= 6 ? decodeHtmlEntities(L[5]) : null;
                                
                                // Get listener's current language
                                var currentLang = builtin_voice.lang;
                               /* console.log('readText() - builtin_voice.lang:', builtin_voice.lang);
                                if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
                                    currentLang = builtin_voice.lang.substr(0, 2);
                                } else if (typeof builtin_language !== 'undefined' && builtin_language) {
                                    currentLang = builtin_language.substr(0, 2);
                                }*/
                                
                                // If listener's language matches source or target language -> direct display
                                if (currentLang === L[2].substr(0, 2)) {
                                    EcrireNB(sourceText);
                                    ToSay['' + L[0]] = sourceText;
                                } else if (targetLang && currentLang === targetLang.substr(0, 2) && targetText) {
                                    EcrireNB(targetText);
                                    ToSay['' + L[0]] = targetText;
                                } else {
                                    // Translation needed
                                    ToTranslate.push([L[0], sourceLang, sourceText, targetLang, targetText]);                                }
                            }
                        }
                        /*      if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2))
    Translate(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2),L[0]);
    else {
    Parle(L[2]);
    Ecrire(""+L[2]);
    }*/
                    }
                }
            }
            //alert(maxi);
            if (maxi > inputnumber) inputnumber = maxi;

            CompleteTranslation();
        };
    }
}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue) for subtitles application.
 * 
 */

function readTextSub() {
    if (_('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "readtext.php");
        increasependingrequests();
        currentorder_readtext++;
        var f = new FormData();
        f.append('lineid', inputnumber);
        f.append('ORDER', currentorder_readtext);
        f.append('ROOMID', _('classroomid').value);
        f.append('room_token', window.ROOM_TOKEN);
        request.send(f);
        AbortTimeout(request,"readTextSub");
        request.onload = function(event) {
            decreasependingrequests();
            var T = request.responseText.split('\n');
            var maxi = -1;
            var readedorder = ('0' + T[0]).split(';')[0] * 1;
            if (readedorder > maxorder_readtext) {
                maxorder_readtext = readedorder;
                for (var i = 0; i < T.length; i++) {
                    var L = T[i].split(';');
                    if (L.length > 3) {
                        if (1 * L[1] > maxi) maxi = 1 * L[1];
                        if (AllPhrases[1 * L[1]] == undefined) {
                            // Skip if classroomid is invalid
                            if (_('classroomid').value !== '' && _('classroomid').value !== 'UNKNOWN') {
                                AllPhrases[1 * L[1]] = true;
                                
                                // Decode HTML entities from source text
                                var sourceLang = L[2];                      // L[2] = langue_source
                                var sourceText = decodeHtmlEntities(L[3]);  // L[3] = texte_source
                                var targetLang = L[4];                      // L[4] = langue_cible
                                var targetText = L.length >= 6 ? decodeHtmlEntities(L[5]) : null;  // L[5] = texte_traduit
                                
                                // Get listener's current language
                                var currentLang = '';
                                if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
                                    currentLang = builtin_voice.lang.substr(0, 2);
                                } else if (typeof builtin_language !== 'undefined' && builtin_language) {
                                    currentLang = builtin_language.substr(0, 2);
                                }
                                
                                // If listener's language matches source or target language -> direct display
                                if (currentLang === L[2].substr(0, 2)) {
                                    EcrireNBSub(sourceText);
                                    ToSay['' + L[0]] = sourceText;
                                } else if (targetLang && currentLang === targetLang.substr(0, 2) && targetText) {
                                    EcrireNBSub(targetText);
                                    ToSay['' + L[0]] = targetText;
                                } else {
                                    // Translation needed
                                    ToTranslate.push([L[0], sourceLang, sourceText, targetLang, targetText]);                           }
                            }
                        }
                        /*      if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2))
    Translate(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2),L[0]);
    else {
    Parle(L[2]);
    Ecrire(""+L[2]);
    }*/
                    }
                }
            }
            //alert(maxi);
            if (maxi > inputnumber) inputnumber = maxi;

            CompleteTranslation();
        };
    }
}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue) for transcribed subtitles application.
 * 
 */

function readTextSubTranscript() {
    if (_('classroomid').value != "") {
        var requestTranscriptSub = new XMLHttpRequest();
        requestTranscriptSub.open("POST", "readtextTranscriptSub.php");
        increasependingrequests();
        currentorder_readtext++;
        var f = new FormData();
        f.append('lineid', inputnumber);
        f.append('ORDER', currentorder_readtext);
        f.append('ROOMID', _('classroomid').value);
        f.append('room_token', window.ROOM_TOKEN);

        requestTranscriptSub.send(f);
        AbortTimeout(requestTranscriptSub,"readTextSubTranscript");
        requestTranscriptSub.onload = function(event) {
            decreasependingrequests();
            var T = requestTranscriptSub.responseText.split('\n');
            _("VOBanner").innerHTML = T;
            //console.log('Readtext get : '+request.responseText);
//             var maxi = -1;
//             var readedorder = ('0' + T[0]).split(';')[0] * 1;
//             console.log("READEDORDER : "+readedorder);
//             if (readedorder > maxorder_readtext) {
//                 maxorder_readtext = readedorder;
//                 for (var i = 0; i < T.length; i++) {
//                     var L = T[i].split(';');
//                     console.log(L);
//                     if (L.length > 3) {
//                         if (1 * L[1] > maxi) maxi = 1 * L[1];
//                         if (AllPhrases[1 * L[1]] == undefined) {
//                             AllPhrases[1 * L[1]] = true;
//                             console.log(L[3]);
//                             console.log("Nouvelle phrase test 1: ");
//                             _("VOBanner").innerHTML = L[3];
//                         }
//                         /*      if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2))
//     Translate(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2),L[0]);
//   else {
//     Parle(L[2]);
//     Ecrire(""+L[2]);
//   }*/
//                     }else{
//                         console.log("Nouvelle phrase : ");
//                         console.log(L);
//                         _("VOBanner").innerHTML = L[1];
//                     }
//                 }
//             }
//             //alert(maxi);
//             if (maxi > inputnumber) inputnumber = maxi;

        };
    }
}

/**
 * @summary Verify if there is something new in the IMAGE queue and if so, load the image.
 * 
 */

function readImage() {
    if (_('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "readimage.php");
        increasependingrequests();
        var f = new FormData();
        f.append('random', currentrandom);
        f.append('ROOMID', _('classroomid').value);
        f.append('room_token', window.ROOM_TOKEN);
        request.send(f);
        AbortTimeout(request,"readImage");
        request.onload = function(event) {
            decreasependingrequests();
            if (request.responseText != "") {
                var Trep = request.responseText.split('|||');
                currentrandom = Trep[0];
            }

        };
    }
}



/**
 * @summary A function that is launched when the HTML page is loaded
 * 
 */

function Lancer() {
    setInterval(readText, 500);
    setInterval(readTextQ, 500);
    setInterval(readImage, 500);
    setLanguageInput(0);
}

/**
 * @summary A function that is launched when the HTML page is loaded
 * 
 */

function Init() {
    /*
    _('question').addEventListener('keyup', function (event) {
      if (event.code === 'Enter') {
          saveTextQ(_('question').value);
        event.preventDefault();
        event.stopPropagation();
      }
    },false);
    */
    startChatPolling();

    // Wooclap disabled - tabs commented out
    /*
    setTimeout(function() {
        var wooclapEl = _('wooclap');
        if (wooclapEl) {
            wooclapEl.src = 'https://app.wooclap.com/';
        }
    }, 1000);
    */
    setTimeout(function() {
        saveTextQ("[newstudentarrived]");
    }, 1000);
    var colorTitreEl = _('ColorTitre');
    ChangeColor('titre', colorTitreEl ? colorTitreEl.value : '0,0,255|0,0,255');

    // Keyboard navigation
    document.onkeyup = function(event) {
        if (["AuditorName", "dialog_MailTo", "dialog_MailFrom", "dialog_MailSubject", "question"].indexOf(document.activeElement.id)<0) {
            if (event.code === 'SemiColon' || event.code === 'KeyM') { // Show/Hide the menu
            }
            // Tab navigation removed - all sections are now displayed sequentially
            if (event.code === 'KeyL') { // go to live
                gotolive=true; CompleteSpeech();
            }
            if (event.code === 'KeyF') { // go fullscreen
                GoFS();
            }
            if (event.code === 'KeyR') { // repeat
                Parle(document.getElementById('titre').innerHTML);
            }
            if (event.code === 'ArrowUp') { // Move the subtitles UP
                document.getElementById('titre').style.top=(document.getElementById('titre').style.top.replace('px','')*1-5)+"px";
            }
            if (event.code === 'ArrowDown') { // Move the subtitles Down
                document.getElementById('titre').style.top=(document.getElementById('titre').style.top.replace('px','')*1+5)+"px";
            }
        }
    };
}


/**
 * @summary Read the CLASSROOM queue
 * 
 */


/**
 * @summary Switch the fullscreen mode on and off
 * 
 */

function GoFS() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        // Fullscreen entered
        //_('bar').style.bottom="10px";
        //resizeSlides();
    } else {
        document.exitFullscreen();
        // Fullscreen exited
        //resizeSlides();
    }
}




/**
 * @summary Switch a given extratab visible or invisible (DISABLED - tabs commented out)
 * 
 * @param {HTMLInput checkbox} obj a checkbox.
 * @param {number} num the tab number.
 * @returns
 */

/*
function showET(url, num) {
    //alert('url='+url+'    num='+num+'     tabs='+'ltabs-'+(8+1*num))
    if (url != "") {
        show('ltabs-' + (8 + 1 * num));
        if (_('extratab' + num).src != url) _('extratab' + num).src = url;
    } else {
        hide('ltabs-' + (8 + 1 * num));
    }
}
*/

/**
 * @summary Wait for the SpeechSynthesis Voices to be available 
 * 
 */

function loadVoicesWhenAvailable() {
    Tvoices = window.speechSynthesis.getVoices();

    if (Tvoices.length !== 0) {
        LoadVoices();
    } else {
        setTimeout(function() {
            loadVoicesWhenAvailable();
        }, 10)
    }
}

/**
 * @summary Load the voices 
 * 
 */

function LoadVoices() {
    InitVoices();
    Lancer();
}


/**
 * @summary Display the selected tab 
 * 
 */




/**
 * @summary Generic function for sending an email
 * 
 */

function EnvoiMail() {
    // alert("I will send the mail to "+_('dialog_MailTo').value.replaceAll(' - ',','));
    var XHR = new XMLHttpRequest();
    var FD = new FormData();
    FD.append('from', _('dialog_MailFrom').value);
    FD.append('to', _('dialog_MailTo').value.replaceAll(' - ', ','));
    FD.append('subject', _('dialog_MailSubject').value);
    //FD.append('body', _('dialog_MailBody').innerHTML);
    FD.append('body', _('dialog_MailBody').innerHTML);
    //FD.append('body', quillmail.getText());
    XHR.addEventListener('load', function(event) {
        alert('Mail sent.');
    });
    XHR.addEventListener('error', function(event) {
        alert('Error, the mail cannot be sent.');
    });
    XHR.open('POST', 'sendamail.php');
    XHR.send(FD);
    AbortTimeout(XHR,"EnvoiMail");
    // dialog.dialog( "close" );		
}

/**
 * @summary Send the notebook content by mail
 * 
 */

function sendnotebook(content) {
    _('dialog_MailFrom').value = 'bourdon-j@univ-nantes.fr';
    _('dialog_MailTo').value = 'your.mail@univ-nantes.fr';
    _('dialog_MailSubject').value = 'Content of lesson ' + _('classroomid').value;
    _('dialog_MailBody').innerHTML = content;
    //$( "#dialog-mail" ).dialog("open");
    _('dialog_MailTo').value = prompt('Enter your email', 'your.mail@univ-nantes.fr');
    EnvoiMail();
}

/**
 * @summary Hide a HTML Div by scaling it to 0x
 * 
 * @param {HTML div} e an HTML DOM object
 */

function hideBanner(e) {
    _(e).style.transform = 'scale(0)';
}

/**
 * @summary Show a HTML Div by scaling it to 1x
 * 
 * @param {HTML div} e an HTML DOM object
 */

function showBanner(e) {
    _(e).style.transform = 'scale(1)';
}

