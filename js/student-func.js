// Basic functions


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
    console.log("CHANGE");
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
    if (!builtin_voice.textonly) {
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
    if (!builtin_voice.textonly) {
        var discours = new SpeechSynthesisUtterance(txt);
        discours.voice = builtin_voice;
        discours.rate = builtin_discoursrate;
        discours.lang = builtin_voice.lang.replace('_', '-')
        discours.onend = f;
        discours.onerror = function() {
            console.log('Error Speech synthesis for ' + txt);
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
    console.log(config);
    if (config.length != "ABYANACNNCNNNYYYYN".length) return;
    // classroomlanguage
    _('classroomlanguage').selectedIndex = 1 * (config.charCodeAt(0) - 65);
    setLanguageInput(_('classroomlanguage').value);
    console.log(_('classroomlanguage').options[_('classroomlanguage').value].text);

    // subtitlelanguage
    _('VoiceOutput').selectedIndex = 1 * (config.charCodeAt(1) - 65);
    setLanguageOutput(_('VoiceOutput').value);
    console.log(_('VoiceOutput').value);

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
    _('ColorTitre').selectedIndex = 1 * (config.charCodeAt(3) - 65);
    ChangeColor('titre', _('ColorTitre').value);
    // ColorPartialTitre
    _('ColorPartialTitre').selectedIndex = 1 * (config.charCodeAt(5) - 65);
    ChangeColor('VOBanner', _('ColorPartialTitre').value);
    // selectfontsize
    _('selectfontsize').selectedIndex = 1 * (config.charCodeAt(6) - 65);
    _('titre').style.fontSize = _('selectfontsize').value;
    _('VOBanner').style.fontSize = _('selectfontsize').value;
    //selectopacity
    _('selectopacity').selectedIndex = 1 * (config.charCodeAt(9) - 65);
    ChangeOpacity('titre', _('selectopacity').value)
    ChangeOpacity('VOBanner', _('selectopacity').value)

    document.getElementById('synthesison').checked=true;
    console.log(_('VoiceOutput').value.substr(0, 2));
    console.log(_('classroomlanguage').options[_('classroomlanguage').value].text.substr(0, 2));

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
    _('nbrequests').innerHTML = _('nbrequests').innerHTML * 1 + 1;
}

/**
 * @summary Debug function for counting the number of pending requests
 * 
 */

function decreasependingrequests() {
    _('nbrequests').innerHTML = _('nbrequests').innerHTML * 1 - 1;
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
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + _('classroomid').value + "&lineid=" + lineid, true);
    increasependingrequests();

    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            console.log(this.responseText);
            var response = JSON.parse(this.responseText);
            if ((!response["error"]) && response["data"]["translations"]) {
                ToSay['' + lineid] = (response["data"]["translations"][0]["translatedText"]);
                //        Ecrire(lineid+"  :  "+response["data"]["translations"][0]["translatedText"]);
                Ecrire(response["data"]["translations"][0]["translatedText"]);
            } else {
                Ecrire("---- Translation ERROR ----")
            }

        }
    };
    xhr.timeout = 9999;
    xhr.ontimeout = function() {
        console.log('ERROR TRANSLATE Timeout');
    };
    xhr.onloadend = function() {
        decreasependingrequests();
        CompleteTranslation();
    };
    xhr.send();
    AbortTimeout(xhr,"Translate");
}

/**
 * @summary Translate a given string from a language to another
 * 
 * @param {string} ch the string to translate.
 * @param {string} l output language.
 * @param {string} linput input language.
 * @param {number} lineid unique identifier for the translation.
 */

function Translate2(ch, l, linput, lineid) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + _('classroomid').value + "&lineid=" + lineid, true);
    increasependingrequests();
    xhr.onloadend = function() {
        decreasependingrequests();
        CompleteTranslation2();
    };
    xhr.send();
    AbortTimeout(xhr,"Translate2");
}

/**
 * @summary InitVoices is executed when the HTML page is loaded.
 * 
 */

function InitVoices() {
    PopulateVoicesOut();
    PopulateVoicesOut('VoiceOutputchoose');
    setLanguageOutput(_("VoiceOutput").value);
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
    //_('titre').innerHTML=ch;
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
    if (ToTranslate.length > 0) {
        var L = ToTranslate[0];
        ToTranslate.splice(0, 1);
        if (builtin_voice.lang.substr(0, 2) != L[1].substr(0, 2)) {
            // Ecrire("Push : "+L[2]);
            Translate(L[2], builtin_voice.lang.substr(0, 2), L[1].substr(0, 2), L[0]);
        } else {
            ToSay['' + L[0]] = L[2]; //,CompleteTranslation);
            Ecrire("" + L[2]);
            CompleteTranslation();
        }
    } else {
        if (!completespeechactive) {
            completespeechactive = true;
            CompleteSpeech();
        }
    }
}

/**
 * @summary Orchestrates the sequence of translations
 * 
 * @todo Comprendre pourquoi on appelle StudentCompleteTranslation dans le sinon et pas StudentCompleteTranslation2
 */

function CompleteTranslation2() {
    if (ToTranslate2.length > 0) {
        var L = ToTranslate2[0];
        ToTranslate2.splice(0, 1);
        if (builtin_voice.lang.substr(0, 2) != L[1].substr(0, 2)) {
            // Ecrire("Push : "+L[2]);
            Translate2(L[2], builtin_voice.lang.substr(0, 2), L[1].substr(0, 2), L[0]);
        } else CompleteTranslation2();
    } else setTimeout(CompleteTranslation, 10);
}

/**
 * @summary Orchestrates the sequence of speeches
 * 
 */

function CompleteSpeech() {
    var nextdiscours = currentdiscours;
    for (var i = 1; i < 1000; i++) {
        if (ToSay['' + (currentdiscours + i)]) {
            nextdiscours = currentdiscours + i;
            break;
        }
    }
    if (gotolive) {
        //console.log('Restart Speech');
        //console.log('Discours : '+currentdiscours+'   '+nextdiscours)
        for (var i = 1000; i > 0; i--) {
            if (ToSay['' + (currentdiscours + i)]) {
                nextdiscours = currentdiscours + i;
                break;
            }
            //console.log('Discours next : '+currentdiscours+'   '+nextdiscours)
            gotolive = false;
        }
    }
    //Ecrire(currentdiscours+'   '+nextdiscours);
    if (nextdiscours > currentdiscours) {
        var L = ToSay['' + nextdiscours];
        L = L.replace("&nbsp;", " ");
        ParleThen(L, CompleteSpeech);
        currentdiscours = nextdiscours;
        completespeechactive = false; // Doute ici
    } else completespeechactive = false;

}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue).
 * 
 */

function readText() {
    var synthesison = _('synthesison').checked;
    if (synthesison) {
        if (_('classroomid').value != "") {
            var request = new XMLHttpRequest();
            request.open("POST", "readtext.php");
            increasependingrequests();
            currentorder_readtext++;
            var f = new FormData();
            f.append('lineid', inputnumber);
            f.append('ORDER', currentorder_readtext);
            f.append('ROOMID', _('classroomid').value);
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
                                AllPhrases[1 * L[1]] = true;
                                EcrireNB(L[3]);
                                ToTranslate.push([L[1], L[2], L[3]]);
                                ToTranslate2.push([L[1], L[2], L[3]]);
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

                CompleteTranslation2();
            };
        }
    }
}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue) for subtitles application.
 * 
 */

function readTextSub() {
    var synthesison = _('synthesison').checked;
    if (synthesison) {
        if (_('classroomid').value != "") {
            var request = new XMLHttpRequest();
            request.open("POST", "readtext.php");
            increasependingrequests();
            currentorder_readtext++;
            var f = new FormData();
            f.append('lineid', inputnumber);
            f.append('ORDER', currentorder_readtext);
            f.append('ROOMID', _('classroomid').value);
            request.send(f);
            AbortTimeout(request,"readTextSub");
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
                                AllPhrases[1 * L[1]] = true;
                                EcrireNBSub(L[3]);
                                ToTranslate.push([L[1], L[2], L[3]]);
                                ToTranslate2.push([L[1], L[2], L[3]]);
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

                CompleteTranslation2();
            };
        }
    }
}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue) for transcribed subtitles application.
 * 
 */

function readTextSubTranscript() {
    var synthesison = _('synthesison').checked;
    if (synthesison) {
        if (_('classroomid').value != "") {
            var requestTranscriptSub = new XMLHttpRequest();
            requestTranscriptSub.open("POST", "readtextTranscriptSub.php");
            increasependingrequests();
            currentorder_readtext++;
            var f = new FormData();
            f.append('lineid', inputnumber);
            f.append('ORDER', currentorder_readtext);
            f.append('ROOMID', _('classroomid').value);

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
        request.send(f);
        AbortTimeout(request,"readImage");
        request.onload = function(event) {
            decreasependingrequests();
            if (request.responseText != "") {
                var Trep = request.responseText.split('|||');
                currentrandom = Trep[0];
                _('img_slides').src = Trep[1];
                _('img_slides').width=1280;
                _('img_slides').height=800;
                _('img_slides').onload = resizeSlides;
                _('stillwaiting').style.display="none";
                console.log('Got a new image');
            }

        };
    }
}

/**
 * @summary Synchronize the student App with a given PIN number
 * 
 * @param {number} pin a 5 digits pin code
 */

function Synchronize(pin) {
    var request = new XMLHttpRequest();
    request.open("POST", "synchronize.php");
    increasependingrequests();
    var f = new FormData();
    f.append('pincode', pin);
    request.send(f);
    AbortTimeout(request,"Synchronize");
    request.onload = function(event) {
        decreasependingrequests();
        if (request.responseText != "") {
            _('classroomid').value = request.responseText;
        } else {
            alert("ERROR : unknown pin code");
        }
    };
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
    resizeSlides();
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
    resizeSlides();
    activetab('tabs-0');

    if (needSynchronize) Synchronize(prompt('Enter the session pincode'));
    setTimeout(function() {
        _('wooclap').src = 'https://app.wooclap.com/';
    }, 1000);
    setTimeout(function() {
        saveTextQ("[newstudentarrived]");
    }, 1000);
    setTimeout(function() {
        showMessageMenu()
    }, 3000);
    _('iconeMenu').src = MenuOnIMG;
    ChangeColor('titre', _('ColorTitre').value);

    // Keyboard navigation
    document.onkeyup = function(event) {
        if (["StudentName","wooclapkey", "dialog_MailTo", "dialog_MailFrom", "dialog_MailSubject", "question"].indexOf(document.activeElement.id)<0) {
        if (event.code === 'SemiColon' || event.code === 'KeyM') { // Show/Hide the menu
        }
        if (event.code === 'KeyS') { // Show slides tab
            activetab('tabs-7');
        }
        if (event.code === 'KeyC') { // Show configuration tab
            activetab('tabs-0');
        }
        if (event.code === 'KeyT') { // Show Tchat tab
            activetab('tabs-1');
        }
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

function readTextQ() {
    if (_('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "readtextQ.php");
        increasependingrequests();
        var f = new FormData();
        f.append('lineid', 0);
        f.append('ROOMID', _('classroomid').value);
        request.send(f);
        AbortTimeout(request,"readTextQ");
        request.onload = function(event) {
            decreasependingrequests();
            var Told = request.responseText.split('\n');
            var k = 0;
            var T = [];
            for (var i = 0; i < Told.length; i++) {
                if (Told[i].split(';').length > 2) T.push(Told[i]);
            }
            //alert(inputnumberQ+'\n\n'+T);
            //console.log('Debug : '+inputnumberQ+T.length);
            for (var i = inputnumberQ; i < T.length; i++) {
                var L = T[i].split(';');
                if (L[0] == 'Teacher' && L[2].indexOf("[extratab") >= 0) {
                    // Add an extratab
                    var resource = L[2].replace("[extratab", "").replace("]", "").split("|");
                    var tabnumber = resource[0];
                    var url = resource[1];
                    showET(url, tabnumber);
                } else {
                    if (L[0] != 'ROBOT' && L[2].indexOf("[newstudent") < 0) {
                        //console.log('Debug -- '+T[i]+'     '+L[2]+'   '+builtin_voice.lang.substr(0,2));
                        if (builtin_voice.lang.substr(0, 2) != L[1].substr(0, 2))
                            TranslateQ(L[2], builtin_voice.lang.substr(0, 2), L[1].substr(0, 2), L[0], i);
                        else {
                            EcrireCR(L[0], L[2], i);
                        }
                    }
                }
            }
            inputnumberQ = T.length;
        };
    }
}

/**
 * @summary Save a new question in the classroom queue
 * 
 * @param {string} ch Text of the question
 * @returns
 */

function saveTextQ(ch) {
    var request = new XMLHttpRequest();
    request.open("POST", "savetextQ.php");
    increasependingrequests();
    var f = new FormData();
    f.append('lineid', 'User ' + _('StudentName').value);
    //inputnumberQ++;
    f.append('text', ch);
    f.append('inputlanguage', builtin_voice.lang);
    f.append('ROOMID', _('classroomid').value);
    request.send(f);
    AbortTimeout(request,"saveTextQ");
    request.onload = function(event) {
        decreasependingrequests();
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
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput, true);
    increasependingrequests();
    xhr.onreadystatechange = function() { // Call a function when the state changes.
        decreasependingrequests();
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            // console.log(this.responseText);
            var response = JSON.parse(this.responseText);
            if ((!response["error"]) && response["data"]["translations"]) {
                // Parle(response["data"]["translations"][0]["translatedText"]);
                EcrireCR(name, response["data"]["translations"][0]["translatedText"], order);
            } else {
                EcrireCR("----", "---- Translation ERROR ----", order)
            }

        }
    };
    xhr.send();
    AbortTimeout(xhr,"TranslateQ");
}

/**
 * @summary Switch the fullscreen mode on and off
 * 
 */

function GoFS() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        _('bar').style.transform = "scaleY(0.5)";
        scalemenu = 1;
        ShowMenu();
        //_('bar').style.bottom="10px";
        //resizeSlides();
    } else {
        document.exitFullscreen();
        _('bar').style.transform = "scaleY(1)";
        scalemenu = 1;
        ShowMenu();
        //resizeSlides();
    }
}


/**
 * @summary Resize the slides window
 * 
 */

function resizeSlides() {
    var slide = _('img_slides');
    var ratio = slide.width / slide.height;
    var aggrandissmentX = (window.innerWidth - 30) / slide.width;
    var aggrandissmentY = (window.innerHeight - 80) / slide.height;
    console.log("W="+slide.width+", H="+slide.height+', WW='+window.innerWidth+', WH='+window.innerHeight+', AX='+aggrandissmentX+', AY='+aggrandissmentY);
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
    }
    //slide.style.top=(48*scalemenu)+'px';

    //var TTabs=['tabs-0','tabs-1','tabs-2','tabs-3','tabs-4','tabs-6','tabs-7'];
    //TTabs.map(function (name) {_(name).style.top=(48*scalemenu)+'px';})
    // For wooclap and others
    ['tabs-0',"tabs-7","wooclap", "extratab1", "extratab2", "extratab3", "extratab4"].map(resizeWindow)
    MinTitlePos();
}


/**
 * @summary Switch a given extratab visible or invisible
 * 
 * @param {HTMLInput checkbox} obj a checkbox.
 * @param {number} num the tab number.
 * @returns
 */

function showET(url, num) {
    //alert('url='+url+'    num='+num+'     tabs='+'ltabs-'+(8+1*num))
    if (url != "") {
        show('ltabs-' + (8 + 1 * num));
        if (_('extratab' + num).src != url) _('extratab' + num).src = url;
    } else {
        hide('ltabs-' + (8 + 1 * num));
    }
}

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
 * @summary Display the menu bar
 * 
 */

function ShowMenu() {
    scalemenu = 1 - scalemenu;
    if (scalemenu < 0.05) {
        _('iconeMenu').src = MenuOnIMG;
    } else {
        _('iconeMenu').src = MenuOffIMG;
    }
    _('bar').style.transform = "scaleX(" + scalemenu + ")";
    var TTabs = ['tabs-0', 'tabs-1', 'tabs-2', 'tabs-3', 'tabs-7', 'tabs-8', 'tabs-9', 'tabs-10', 'tabs-11', 'tabs-12', 'tabs-13'];
    for (var i = 0; i < TTabs.length; i++) {
        _(TTabs[i]).style.transform = "translateX(" + (160 * scalemenu) + "px)";
    }

    resizeSlides();

}

/**
 * @summary Display the selected tab 
 * 
 */

function activetab(tab) {
    var TTabs = ['tabs-0', 'tabs-1', 'tabs-2', 'tabs-3', 'tabs-7', 'tabs-8', 'tabs-9', 'tabs-10', 'tabs-11', 'tabs-12', 'tabs-13'];
    for (var i = 0; i < TTabs.length; i++) {
        _(TTabs[i]).style.display = "none";
        _("l" + TTabs[i]).style.color = "black";
        _("l" + TTabs[i]).style.backgroundColor = "rgb(247,247,247)";
    }
    _(tab).style.display = 'block';
    _("l" + tab).style.color = "white";
    _("l" + tab).style.backgroundColor = "rgb(57,199,204)";
    ShowMenu();
    resizeSlides();
}


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

/**
 * @summary Hide the message menu div by scaling it to 0x
 * 
 */

function showMessageMenu() {
    _("tmpmessage").style.transform = 'scale(0)';
}