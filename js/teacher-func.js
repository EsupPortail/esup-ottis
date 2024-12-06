// Basic functions

/**
 * @summary A prettier alert function
 * 
 * @param {string} ch The text shown in the alert.
 * @returns
 */

function myalert(ch) {
    ch = ch.replaceAll("\n", "<br/>");
    _('dialog-invite-p').innerHTML = ch;
    $("#dialog-invite").dialog("open");
}


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

function StudentTranslate(ch, l, linput, lineid) {
    var xhr = new XMLHttpRequest();
    lineid='DIRECT';
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + _('classroomid').value + "&lineid=" + lineid+TranslatorInfo(), true);
    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            console.log("StudentTranslate = " + this.responseText);
            var response = JSON.parse(this.responseText);
            if ((!response["error"]) && response["data"]["translations"]) {
                StudentToSay['' + lineid] = (response["data"]["translations"][0]["translatedText"]);
                //        Ecrire(lineid+"  :  "+response["data"]["translations"][0]["translatedText"]);
                lastSentenceTranslated=response["data"]["translations"][0]["translatedText"];
                EcrireFeedback(response["data"]["translations"][0]["translatedText"]);
                EcrireNBonlyVO(ch);
            } else {
                EcrireFeedback("---- Translation ERROR ----")
            }

        }
    };
    xhr.onloadend = function() {
        StudentCompleteTranslation();
    };
    xhr.send();
    AbortTimeout(xhr,"StudentTranslate");

}

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
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + _('classroomid').value + "&lineid=" + lineid+TranslatorInfo(), true);
    xhr.onloadend = function() {
        StudentCompleteTranslation2();
    };
    xhr.send();
    AbortTimeout(xhr,"StudentTranslate2");
}

/**
 * @summary Orchestrates the sequence of translations
 * 
 * @deprecated
 */

function StudentCompleteTranslation() {
    if (StudentToTranslate.length > 0) {
        var L = StudentToTranslate[0];
        StudentToTranslate.splice(0, 1);
        if (subtitle_language.substr(0, 2).toLowerCase() != L[1].substr(0, 2)) {
            StudentTranslate(L[2], subtitle_language.substr(0, 2).toLowerCase(), L[1].substr(0, 2), L[0]);
        } else {
            StudentToSay['' + L[0]] = L[2]; //,CompleteTranslation);
            EcrireFeedback("" + L[2]);
            StudentCompleteTranslation();
        }
    } else {
        if (!Studentcompletespeechactive) {
            Studentcompletespeechactive = true;
            StudentCompleteSpeech();
        }
    }
}

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
        L = L.replace("&nbsp;", " ");
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
        if (_('classroomid').value != "") {
            var request = new XMLHttpRequest();
            request.open("POST", "readtext.php");
            AbortTimeout(request,"StudentreadText");
            Studentcurrentorder_readtext++;
            var f = new FormData();
            f.append('lineid', Studentinputnumber);
            f.append('ORDER', Studentcurrentorder_readtext);
            f.append('ROOMID', _('classroomid').value);
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
        }*/
                        }
                    }
                }
                //alert(maxi);
                if (maxi > Studentinputnumber) Studentinputnumber = maxi;

                StudentCompleteTranslation2();
            };
        }
    }
}

// End Student Feedback



/**
 * @summary Create an association link for a given course that can be shared
 * 
 */

function InviteExternal() {
    if (_('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "createExternal.php");
        AbortTimeout(request,"InviteExternal");
        var f = new FormData();
        f.append('ROOMID', _('classroomid').value);
        f.append('DURATION', _('duration').value);
        request.send(f);
        request.onload = function(event) {
            var T = request.responseText.trim();
            if (T == 'ok') {
                var link = _('teacherlink').href.replace("index.php", "indexExternal.php");
                navigator.clipboard.writeText(link);
                myalert('External guest created !\nThe following link has been copied. It must be used in the next 7 days.\n<a href="' + link + '">' + link + '</a>');
            }
        }
    }
}


/**
 * @summary Fill the select VoiceOutput field
 * 
 */

function PopulateVoicesOut() {
    var select = _('VoiceOutput');
    AllOutputVoices = [];
    while (select.firstChild) {
        select.removeChild(select.firstChild);
    }
    var voices = window.speechSynthesis.getVoices();
    for (var i = 0; i < voices.length; i++) {
        if (true) { //(voices[i].localService) {
            var opt = document.createElement("option");
            opt.value = AllOutputVoices.length;
            if (voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
            AllOutputVoices.push(voices[i]);
            opt.innerHTML = voices[i].lang + " " + voices[i].name;
            select.appendChild(opt);
        }
    }
}

/**
 * @summary Fill the select classroomlanguage field
 * 
 * @deprecated
 */

function PopulateVoicesIn() {
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
}

/**
 * @summary Fill the select classroomlanguage field
 * 
 */

function PopulateVoicesIn() {
    if (TLangInput.length == 0) {
        var select = _('classroomlanguage');
        //var voices = window.speechSynthesis.getVoices();
        TLangInput = [];

        for (var i = 0; i < supportedlanguages.length; i++) {
            var lang = supportedlanguages[i].code; //.substr(0,5);
            if (TLangInput.indexOf(lang) < 0) {
                var opt = document.createElement("option");
                opt.value = TLangInput.length;
                if (i == 0) {
                    opt.selected = true;
                    builtin_language = TLangInput[i];
                }
                TLangInput.push(lang);
                opt.innerHTML = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
                select.appendChild(opt);
            }
        }
    }
}

/**
 * @summary Fill the select subtitlelanguage field
 * 
 */


function PopulateSubtitlesLang() {
    var select = _('subtitlelanguage');
    for (var i = 0; i < supportedlanguages.length; i++) {
        var lang = supportedlanguages[i].code; //.substr(0,5);
        var opt = document.createElement("option");
        opt.value = lang;
        if (i == 0) {
            opt.selected = true;
            subtitle_language = lang;
        }
        opt.innerHTML = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
        select.appendChild(opt);
    }
}

/**
 * @summary Fill the select speechlanguage field
 * 
 */

function PopulateSpeechLang() {
    var select = _('speechlanguage');
    for (var i = 0; i < supportedlanguages.length; i++) {
        var lang = supportedlanguages[i].code; //.substr(0,5);
        var opt = document.createElement("option");
        opt.value = lang;
        opt.innerHTML = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
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
    speech_language = i;
    //console.log("Subtitles set to "+subtitle_language+ " builtin_language is "+builtin_language);
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

function RemoveVerbalTics(ch) {
    var T_verbaltics = _('verbaltics').value.trim().split("\n");
    var ch2 = ch;
    for (var i = 0; i < T_verbaltics.length; i++) {
        var fromword = T_verbaltics[i].trim();
        if (fromword.trim() != "") {
            if (fromword.indexOf("|") <= 0) {
                ch2 = (ch2 + " ").replaceAll(fromword + " ", "").trim();
                ch2 = ch2.replaceAll(fromword + ",", "");
            } else {
                var [fromword, toword] = fromword.split("|");
                ch2 = (ch2 + " ").replaceAll(fromword + " ", toword + " ").trim();
                ch2 = ch2.replaceAll(fromword + ",", toword + ",");
                ch2 = ch2.replaceAll(fromword + ".", toword + ".");
            }
        }
    }
    return ch2;
}


/**
 * @summary Main function for translating a given text, handles the synchronisation of different translations.
 * 
 * @param {string} ch a given sentence.
 * @returns
 */

function Traduction(ch) {
    ch = RemoveVerbalTics(ch);
    var recognitionon = _('recognitionon').checked;
    if (recognitionon) {
        //EcrireNB(ch);
        //EcrireTitle(ch);
        saveText(ch);
        var liveon = _('liveon').checked;
        if (liveon) {
            if (readytotalk) {
                notready();
                Ecrire('Input : ' + ch);
                if (builtin_voice.lang.substr(0, 2) != builtin_language.substr(0, 2))
                    Translate(ch, builtin_voice.lang.substr(0, 2), builtin_language.substr(0, 2));
                else {
                    Parle(ch);
                    Ecrire("Output : " + ch);
                }
            }
        }
    }
}


/**
 * @summary A function that adds url arguments related to the choosen translator service.
 * 
 * @returns {string} the arguments that have tot be added to Translate.php url
 */

function TranslatorInfo() {
    var res="";
    var whichtranslator=_('whichtranslator').value;
    var apikey=_('apikey').value;
    if (whichtranslator != '') res+='&whichtranslator='+whichtranslator;
    if (apikey != '') res+='&apikey='+apikey;
    return res;
}

/**
 * @summary A function that translates a given text from a language to another.
 * 
 * @param {string} ch A given sentence
 * @param {string} l The output language.
 * @param {string} linput The input language.
 */

function Translate(ch, l, linput) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + _('classroomid').value+TranslatorInfo(), true);

    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            // console.log(this.responseText);
            var response = JSON.parse(this.responseText);
            if ((!response["error"]) && response["data"]["translations"]) {
                Parle(response["data"]["translations"][0]["translatedText"]);
                lastSentenceTranslated=response["data"]["translations"][0]["translatedText"];
                Ecrire("" + response["data"]["translations"][0]["translatedText"]);
            } else {
                Ecrire("---- Translation ERROR ----")
            }

        }
    };
    var d_begin = Date.now();
    console.log(d_begin + " : Sending for translation " + ch + " from " + linput + " to " + l)
    xhr.send();
    AbortTimeout(xhr,"Translate");
}


/**
 * @summary A function that translates a given text from a language to another, then fire a given feedback function.
 * 
 * @param {string} ch A given sentence
 * @param {string} l The output language.
 * @param {string} linput The input language.
 * @param {function} f the function that is fired when the translation has ended.
  */

function TranslateThen(ch, l, linput, f, forcelibre=false) {
    var force="";
    if (forcelibre) force="&forcelibre=true";
    var xhr = new XMLHttpRequest();
    
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + _('classroomid').value+"&lineid=DIRECT"+force+TranslatorInfo(), true);

    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            var d_end = Date.now();
            //console.log("Receiving translation in " + (d_end - d_begin) + "ms for " + ch + " from " + linput + " to " + l);
            console.log(this.responseText);
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
}


/**
 * @summary Update the progress bar when a lesson is loaded
 * 
 * @param {event} event an event.
 */

function progressHandler(event) {
    var percent = (event.loaded / event.total) * 100;
    _("progressBar").value = Math.round(percent);
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
        saveText(T[i].replace(/<br\/>/gi, "").replace(/<br>/gi, ""));
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
        _('titreright').innerHTML = n;
        if (_("img_" + n) && _("img_" + n).src.length > 200) {
            _("img_slides").src = _("img_" + n).src;
            resizeSlides();

            // Diffusion Image
            var request = new XMLHttpRequest();
            request.open("POST", "saveimage.php");
            var f = new FormData();
            f.append('random', Math.floor(Math.random() * 10000));
            var imageurl = _("img_" + n).src;
            /*
            var nimages=0;
            var maxcar=10000;
            for(var i=0; i*maxcar<imageurl.length;i++) {
              f.append('image'+i,imageurl.substr(i*maxcar,maxcar));
              nimages++;
            }
            f.append('nbimages',nimages);
            */
            var block = imageurl.split(";");
            var contentType = block[0].split(":")[1];
            var realData = block[1].split(",")[1];
            var blob = b64toBlob(realData, contentType);
            f.append("image", blob);
            f.append('ROOMID', _('classroomid').value);
            request.send(f);
            AbortTimeout(request,"ShowImage");
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
    //console.log(datatxt);
    currentPresentation = [];
    //alert(datatxt);
    var res = '';
    var data = JSON.parse(datatxt);
    // console.log(data);
    for (var i = 0; i < data.length; i++) {
        slide = data[i];
        var txtnotes = '';
        for (var j = 0; j < slide["Notes"].length; j++) {
            if (slide["Notes"][j].indexOf("[forward]") >= 0 || slide["Notes"][j].indexOf("[backward]") >= 0 || slide["Notes"][j].indexOf("[pause") >= 0 || slide["Notes"][j].indexOf("[resource") >= 0 || slide["Notes"][j].indexOf("[mouse") >= 0 || slide["Notes"][j].indexOf("[notification") >= 0 || slide["Notes"][j].indexOf("[wait") >= 0) {
                slide["Notes"][j] = '';
            } else {
                txtnotes = txtnotes + slide["Notes"][j] + ' <br/>';
            }
        }
        res += '<dl>';
        if (slide["Images"]) {
            //alert("The presentation contains "+slide["Images"].length+" images");
            var toexec = "";
            for (var j = 1; j <= slide["Images"].length; j++) {
                toexec += "_('img_" + j + "').src='" + slide["Images"][j - 1] + "'; ";
            }
            setTimeout(toexec, 10);
        } else {
            //      res = res + '<dt><span onclick="addToNB(\''+txtnotes+'\',1); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
            //      res = res + '<dt><span onclick="addSlideToNB(\''+txtnotes+'\'); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
            res = res + '<dt>&nbsp;Slide #' + slide["Slide"] + '<img id="img_' + slide["Slide"] + '" width="50" src=""/></dt>\n';
            var flag = false;
            for (var j = 0; j < slide["Notes"].length; j++) {
                if (slide["Notes"][j].trim() != '') {
                    if (editable) {
                        res = res + '<dd>&nbsp;' + '<span id="SlideText'+i+'_'+j+'" contenteditable="true">'+slide["Notes"][j] + '</span>'+'</dd>\n';
                    } else {
                        res = res + '<dd><span onclick="addToNB(\'' + slide["Notes"][j] + '\',0); ShowImage(' + slide["Slide"] + '); currentDiscours=' + currentPresentation.length + '; saveremotespeech();">▶️</span>&nbsp;' + slide["Notes"][j]+'</dd>\n';
                    }
                    currentPresentation.push({
                        'Slide': slide["Slide"],
                        'Discours': slide["Notes"][j]
                    });
                    flag = true;
                }
            }
            if (!flag) currentPresentation.push({
                'Slide': slide["Slide"],
                'Discours': ''
            });
        }
        res += '</dl>';
    }
    //  alert(res);
    console.log("DEBUG : " + res);
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
        if (_('classroomid').value != "") {
            var request = new XMLHttpRequest();
            request.open("POST", "savespeechforremote.php");
            var f = new FormData();
            f.append('ROOMID', _('classroomid').value);
            f.append('SPEECH', txt);
            request.send(f);
            AbortTimeout(request,"saveremotespeech");
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
    if (slide["Images"].length > 1) {
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
    if (slide["Images"].length > 1) {
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
    _("notebookcompleteformatted").innerHTML = formateresponse(event.target.responseText,editable);
    _("progressBar").value = 0;

    // Re-initialize the presentation
    previousimage = -1;
    currentDiscours = 0;
    if (currentPresentation.length > 0) {
        setTimeout(function() {
            _('stillwaiting').style.display="none";
            ShowImage(currentPresentation[0]['Slide']);
        }, 1000);
    }
}

/**
 * @summary The function that is executed for reading a new presentation
 * 
 */

function SlideNotes() {
    var file = _("ppt_presentation").files[0];
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
      */
    xhr.timeout = time;
    xhr.ontimeout = (e) => {
        console.log("Erreur Timeout !");
        _('spinnertxt').innerHTML='Your PPTX presentation is too long to be imported directly.<br/>Please convert it first with the OMIST Toolbox and use the generated html file instead.';
        alert("Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.")
    };
    xhr.send(formdata);
    //AbortTimeout(xhr,"SlideNotes");
}

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
    event.preventDefault();
    var window_width = window.screen.width;
    var windowFeatures = "status=no,location=no,toolbar=no,menubar=no,width=1200,height=110";
    var link = _("sublink").innerHTML.replaceAll("&amp;","&");
    window.open(link,'_blank',windowFeatures);
}

/**
 * @summary The function that is executed for reading a new PPTX presentation stored on UNCloud
 * 
 * @param {string} uncloudlink A public link on Nantes University cloud (UNCloud) that points to a PPTX file.
 */

function SlideNotesUncloud(uncloudlink) {
    _('spinnertxt').innerHTML='Your presentation is not ready yet....<br/>Still working on it...';
    var scriptfile = "ExtractSlidesNotes_PPTX_images.php";
    var formdata = new FormData();
    formdata.append("ACCESS", _('classroomid').value);
    formdata.append("uncloudlink", uncloudlink);
    var xhr = new XMLHttpRequest();

    xhr.open("POST", scriptfile);
    xhr.upload.addEventListener("progress", progressHandler, false);
    xhr.addEventListener("load", completeHandler, false);
    xhr.timeout = 30000;
    xhr.ontimeout = (e) => {
        console.log("Erreur Timeout !");
        _('spinnertxt').innerHTML='Your PPTX presentation is too long to be imported directly.<br/>Please convert it first with the OMIST Toolbox and use the generated html file instead.';
        alert("Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.")
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

    var scriptfile = "ExtractSlidesNotesAppHTML.php";
    var formdata = new FormData();
    formdata.append("ACCESS", _('classroomid').value);
    formdata.append("uncloudlink", uncloudhtmllink);
    var xhr = new XMLHttpRequest();

    xhr.open("POST", scriptfile);
    xhr.upload.addEventListener("progress", progressHandler, false);
    xhr.addEventListener("load", completeHandler, false);
    xhr.timeout = 30000;
    xhr.ontimeout = (e) => {
        console.log("Erreur Timeout !");
        _('spinnertxt').innerHTML='No presentation yet...<br/>Please go to the complete lesson tab to import it.';
        alert("Your presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.")
    };
    xhr.send(formdata);
    //AbortTimeout(xhr,"SlideNotesUncloudHtml");
}


/**
 * @summary Function that update the "Partial" div wit translation is required.
 * 
 * @param {string} ch the text that is adding to the partial div.
 */

function EcrireTranslatePartial(ch) {
    var linput = builtin_language.substr(0, 2).toLowerCase();
    if (ch.indexOf(':|:') >= 0) { // If there are a language indication
        var Tch = ch.split(':|:');
        linput = Tch[0].substr(0, 2).toLowerCase();
        ch = Tch[1];
    }
    var loutput = subtitle_language.substr(0, 2).toLowerCase();
    if (linput == loutput) {
        EcrirePartial(ch);
    } else {
            if (LiveTranslationNum % LiveTranslationFreq == 0) {
                lastSentenceVO = ch;
                totalTranslated += ch.length;
                _('debugspan').innerHTML='Total translated = '+totalTranslated+' chars';
                TranslateThen(ch, loutput, linput, function(ch) {
                    lastSentenceTranslated = ch;
                    if (_('directMicrosoftTranslationBoth').checked) {
                        EcrirePartial(lastSentenceTranslated+"<br><span>"+lastSentenceVO+"</span>");
                    } else {
                        EcrirePartial(lastSentenceTranslated+"");
                    }
                },forcelibre=false);
            } else {
                if (_('directMicrosoftTranslationBoth').checked) {
                    EcrirePartial(lastSentenceTranslated+"<br><span>"+ch+"</span>");
                } else {
                    EcrirePartial(lastSentenceTranslated+"");
                }
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
            for (var i = event.resultIndex; i < event.results.length; i++) {
                if (event.results[i].isFinal) {
                    partial = event.results[i][0].transcript; //+" > ";
                    FreeResources();
                    LiveTranslationNum = 0;
                    lastSentenceVO = '';
                    callback(event.results[i][0].transcript);
                } else {
                    partial += ' ' + event.results[i][0].transcript;
                }
            }
            if (partial.trim() != '') {
                if (_('recognitionon').checked) {
                    if (! _('directMicrosoftTranslation').checked) {
                        EcrirePartial(RemoveVerbalTics(partial));
                    } else {
                        EcrireTranslatePartial(RemoveVerbalTics(partial));
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
    setLanguageInput(0);
    setLanguageOutput(0);
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
    _('notebook').innerHTML = _('notebook').innerHTML + ch + "<br/>";
    //EcrireTitle(ch);
}

/**
 * @summary Function that update the "notebook" div.
 * 
 * @param {string} ch the text that is adding to the notebook.
 */

function EcrireNBonly(ch) {
    _('notebook').innerHTML = _('notebook').innerHTML + ch + "<br/>";
    //  if (_('notebook').innerHTML == "")
    //    _('notebook').innerHTML=ch;
    //  else _('notebook').innerHTML=_('notebook').innerHTML+"<br/>"+ch;
}

/**
 * @summary Function that update the "notebook VO" div.
 * 
 * @param {string} ch the text that is adding to the notebook.
 */

function EcrireNBonlyVO(ch) {
    _('notebookVO').innerHTML = _('notebookVO').innerHTML + ch + "<br/>";
}


/**
 * @summary Function that update the "title" div.
 * 
 * @param {string} ch the text that is adding to the title.
 */

function EcrireTitle(ch) {
    var linput = builtin_language.substr(0, 2).toLowerCase();
    if (ch.indexOf(':|:') >= 0) { // If there are a language indication
        var Tch = ch.split(':|:');
        linput = Tch[0].substr(0, 2).toLowerCase();
        ch = Tch[1];
    }
    var loutput = subtitle_language.substr(0, 2).toLowerCase();
    if ((!_('subtitleon').checked) || (linput == loutput)) {
        _('titre').innerHTML = ch;
        // for scrollbar
        setScrollvalues(ch.length);
    } else {
        TranslateThen(ch, loutput, linput, function(ch) {
            _('titre').innerHTML = ch;
            EcrirePartial(ch);
            setScrollvalues(ch.length);
        },forcelibre=false);
    }
}

/**
 * @summary Function that update the "partial title" div.
 * 
 * @param {string} ch the text that is adding to the title.
 */

function EcrirePartial(ch) {
    if (ch != 'undefined' && ch != undefined) _('partialtitre').innerHTML = ch;
    // console.log("CH : "+ch);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "TranscriptionSub.php?texte=" + ch + "&ROOMID=" + _('classroomid').value, true);
    // console.log("Sending for transcription " + ch)
    xhr.addEventListener("load", function (e) {var data=JSON.parse(e.target.responseText);}, false);
    xhr.send();
    AbortTimeout(xhr,"Transcription");
}


/**
 * @summary Function that update the "studentfeedback" div.
 * 
 * @param {string} ch the text that is adding to the title.
 */

function EcrireFeedback(ch) {
    EcrireNBonly(ch);
    if (FeedbackQueue>0) {
        _('titre').innerHTML = _('titre').innerHTML+"<br>"+ch;
        _('studentfeedback').innerHTML = _('studentfeedback').innerHTML+"<br>"+ch;
    } else {
        _('titre').innerHTML = ch;
        _('studentfeedback').innerHTML = ch;
    }
    FeedbackQueue += ch.length*40; // Estimation of 1 character every 40ms (~ mean reading time 200 words/min of approx. 7 characters)
}

/**
 * @summary Function that update the "output" div.
 * 
 * @param {string} ch the text that is adding to the title.
 */

function Ecrire(ch) {
    if (_('output').innerHTML == "")
        _('output').innerHTML = ch;
    else _('output').innerHTML = ch + "<br/>" + _('output').innerHTML;
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
    request.open("POST", "savetext.php");
    var f = new FormData();
    f.append('lineid', inputnumber);
    inputnumber++;
    f.append('text', ch);
    f.append('inputlanguage', inputlanguage);
    f.append('ROOMID', _('classroomid').value);
    f.append('LastLines', MaxSavedLines);
    request.send(f);
    AbortTimeout(request,"saveText");

    //	request.onload = function(event) {};
}

/**
 * @summary Save the entire notebook (several lines at a time)
 * 
 * @deprecated
 * @param {string} ch a <br/> separated list of lines.
 */

function saveTextNB(ch) {
    var request = new XMLHttpRequest();
    request.open("POST", "savetextNB.php");
    var f = new FormData();
    //f.append('lineid',inputnumber);
    inputnumber = ch.split('<br/>').length;
    f.append('text', ch);
    f.append('inputlanguage', builtin_language);
    f.append('ROOMID', _('classroomid').value);
    request.send(f);
    AbortTimeout(request,"saveTextNB");

    //	request.onload = function(event) {};
}

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
    LaunchReadTextQ();
    LaunchStudentReadText();
    LaunchFeedbackReady();
    setInterval(ScrollBar, scrollinterval);
    applyConfig();
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
    resizeSlides();

    _('pincode').value = ("" + Math.floor(100000 + Math.random() * 100000)).substr(1);
    _('sharedpin').innerHTML = _('pincode').value;
    _('sharedpin2').innerHTML = _('pincode').value;
    Synchronize_save();

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
    ChangeColor('titre', _('ColorTitre').value);
    ChangeColor('titreleft', _('ColorTitre').value);
    ChangeColor('titrerightbis', _('ColorTitre').value);
    ChangeColor('partialtitre', _('ColorPartialTitre').value);
    ChangeColor('studentfeedback', _('Colorstudentfeedback').value);

    //setTimeout(Micro,1000);
    //setTimeout(Micro,1500);
    //version addEventListener
    window.addEventListener('offline', (event) => {
        console.log("La connexion au réseau est perdue.");
        _('connexion').style.visibility = 'visible';
        delayStudentReadtext = 100000;
        delayReadtextQ = 100000;
        delayRemote = 100000;
    });
    window.addEventListener('online', (event) => {
        console.log("La connexion au réseau est rétablie.");
        _('connexion').style.visibility = 'hidden';
        delayStudentReadtext = 500;
        delayReadtextQ = 100;
        delayRemote = 200;
    });

    if (_('embedwooclapon').checked) {
        _('spanembedwooclapon').innerHTML = 'Yes !';
        show('titlewooclap');
    } else {
        _('spanembedwooclapon').innerHTML = 'No.';
        hide('titlewooclap');
    }
    if (zoomid != '') startZoom(zoomid);
    if (uncloudlink != '') SlideNotesUncloud(uncloudlink);
    if (uncloudhtmllink != '') SlideNotesUncloudHtml(uncloudhtmllink);
    if (targettab > 0) _('ttab-' + targettab).click();

    // Adjust a few parameters according to the url arguments
    var base = new URL(window.location.toLocaleString()).searchParams;
    if (base.has('WhichTranslator')) _('whichtranslator').value = base.get('WhichTranslator');
    if (base.has('ApiKey')) _('apikey').value = base.get('ApiKey');
    if (base.has('Direct')) _('directMicrosoftTranslation').checked = true;


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
    if (window.confirm("Are you the host for this zoom session ?")) {
        window.open('https://univ-nantes-fr.zoom.us/wc/' + zoomid + '/start');
    }
}



/**
 * @summary Change the classroom language
 * 
 * @param {string} l the two letters code of the language.
 */

function setLanguageStr(l) {
    console.log("Remote says set language to (" + l + ")");
    // Try to find the language in the language list
    var s = _('classroomlanguage');
    var finded = -1
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
    if (_('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "saveremoteorder.php");
        var f = new FormData();
        f.append('ROOMID', _('classroomid').value);
        f.append('APPID', _('appid').value);
        f.append('ORDER', '');
        request.send(f);
        AbortTimeout(request,"remote");

        request.onload = function(event) {
            var order = request.responseText;
            if (order != '') {
                console.log('Get order ' + order);
                if (order == 'forward') Forward();
                if (order == 'backward') Backward();
                if (order == 'skipforward') SkipForward();
                if (order == 'skipbackward') SkipBackward();
                if (order == 'playcurrent') {
                    SkipBackward();
                    Forward();
                }
                if (order.substr(0, "micro".length) == "micro") {
                    var fromremote = order.substr("micro".length).trim();
                    if (fromremote == _('appid').value || fromremote == '') Micro();
                }
                if (order.substr(0, "language".length) == "language") {
                    var options = order.substr("language".length).trim().split("|");
                    var to_lang = options[0].trim();
                    var fromremote = options[1].trim();
                    console.log('Order for (' + _('appid').value + ') from (' + fromremote + ') for language (' + to_lang + ')');
                    if (fromremote == _('appid').value || fromremote == '') setLanguageStr(to_lang);
                }
            }
        };
    }
}

/**
 * @summary Update the number of connected users
 * 
 */

function updateUsers() {
    _('connectedusers').innerHTML = (NBconnectedusers) + " connected users";
    _('connectedusers').title = Tconnectedusers.join(", ");
}

/**
 * @summary Read the CLASSROOM queue
 * 
 */

function readTextQ() {
    if (_('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "readtextQ.php");
        var f = new FormData();
        f.append('lineid', 0);
        f.append('ROOMID', _('classroomid').value);
        request.send(f);
        AbortTimeout(request,"readTeextQ");
        request.onload = function(event) {
            var Told = request.responseText.split('\n');
            var k = 0;
            var T = [];
            for (var i = 0; i < Told.length; i++) {
                if (Told[i].split(';').length > 2) T.push(Told[i]);
            }
            //alert(inputnumberQ+'\n\n'+T);
            //console.log('Debug : '+inputnumberQ+T.length);
            var nbnewQ = 0;
            for (var i = inputnumberQ; i < T.length; i++) {
                var L = T[i].split(';');
                if (L[0] == 'Teacher' && L[2].indexOf("[extratab") >= 0) {
                    console.log('Opening an extra tab');
                    // feedback
                    var resource = L[2].replace("[extratab", "").replace("]", "").split("|");
                    var tabnumber = resource[0];
                    var url = resource[1];
                    if (_('extraurl' + tabnumber).value != url) {
                        _('extraurl' + tabnumber).value = url;
                        _('embedET' + tabnumber).checked = true;
                        showET(_('embedET' + tabnumber), tabnumber);
                    }

                } else {
                    if (L[2].indexOf("[newstudentarrived]") >= 0) {
                        NBconnectedusers++;
                        Tconnectedusers.push(L[0]);
                        updateUsers();
                    } else {
                        if (L[2].indexOf("[newstudentlived]") >= 0) {
                            NBconnectedusers--;
                            Tconnectedusers.filter(student => student != L[0]);
                            updateUsers();
                        } else {

                            if (L[0] == 'ROBOT') {
                                if (builtin_externaltranscritionon) Traduction(L[2]);
                            } else {
                                nbnewQ++;
                                console.log('Debug -- ' + T[i] + '     ' + L[2] + '  :  ' + L[1].substr(0, 2) + '   --> ' + builtin_language.substr(0, 2) + ' voice=' + builtin_voice.lang.substr(0, 2));
                                if (builtin_language.substr(0, 2) != L[1].substr(0, 2)) {
                                    TranslateQ(L[2], builtin_language.substr(0, 2), L[1].substr(0, 2), L[0], i);

                                    // TranslateQ(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2), L[0],i);
                                } else {
                                    EcrireCR(L[0], L[2], i);
                                }
                            }
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
        tab.innerHTML = '<i class=\'material-icons\'>thumb_up</i>';
        tab.style.backgroundColor = 'green';
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
        tab.innerHTML = '<i class=\'material-icons blink\'>warning</i>';
        tab.style.backgroundColor = 'orange';
        draw();
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
    request.open("POST", "savetextQ.php");
    var f = new FormData();
    f.append('lineid', 'Teacher');
    //inputnumberQ++;
    f.append('text', ch);
    f.append('inputlanguage', builtin_language);
    f.append('ROOMID', _('classroomid').value);
    request.send(f);
    AbortTimeout(request,"saveTextQ");
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
                console.log("---- Translation ERROR ----");
            }

        }
    };
    xhr.send();
    AbortTimeout(xhr,"TranslateQ");
}

/**
 * @summary unused and obsolete function
 * 
 * @deprecated
 * @unused
 */

function FromCompleteLessonToTable() {
    var txt = _('notebookcompleteraw').innerHTML;
    _('notebookcompleteformatted').innerHTML = txt;
}

/**
 * @summary Do nothing useless ? probably unused
 * 
 * @deprecated
 * @unused
 */

function playlesson() {}

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
        _('bar').style.transform = "scaleY(1)";
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
        _('bar').style.transform = "scaleY(1)";
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
    ["wooclap", "extratab1", "extratab2", "extratab3", "extratab4", "visio"].map(resizeWindow)
}

/**
 * @summary Synchronize the teacher App with a given PIN number
 * 
 * @deprecated
 * @unused
 * @param {number} pin a 5 digits pin code
 */

function Synchronize(pin) {
    var request = new XMLHttpRequest();
    request.open("POST", "synchronize.php");
    var f = new FormData();
    f.append('pincode', pin);
    request.send(f);
    AbortTimeout(request,"Synchronize");
    request.onload = function(event) {
        if (request.responseText != "") {
            _('classroomid').value = request.responseText;
            _('pincode').value = pin;
            _('sharedpin').innerHTML = pin;
            _('sharedpin2').innerHTML = pin;
            updateLinks();
        } else {
            alert("ERROR : unknown pin code");
        }
    };
}

/**
 * @summary Create a new PIN 5 digit code associated to a course channel
 * 
 */

function Synchronize_save() {
    if (_('pincode').value != "") {
        if (_('classroomid').value != "") {
            var request = new XMLHttpRequest();
            request.open("POST", "synchronize_savepin.php");
            var f = new FormData();
            f.append('pincode', _('pincode').value);
            f.append('roomid', _('classroomid').value);
            request.send(f);
            AbortTimeout(request,"Synchronize_save");
            request.onload = function(event) {};
        }
    }
}


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
        if (url != "") {
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
        StartSpeechRecognition(Traduction);
    }
    /* else {
          builtin_recognition.abort();
        }*/

}

/**
 * @summary Display a Scroll bar below the title bar and synchronized with the speech duration
 * 
 */

function ScrollBar() {
    currentscroll = currentscroll - currentindent;
    if (currentscroll < 0) currentscroll = 0;
    _('scrollbar').style.transform = 'scaleX(' + currentscroll + ')';
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
 * @summary Update the external tools links
 * 
 */

function updateLinks() {
    var roomid = _('classroomid').value;
    _("link1").src = "https://tableaunoir.github.io/?id=" + roomid;
    _("link1").innerHTML = _("link1").src;
    _("link1").title = 'Click here to add a link for ' + _("link1").src;
    _("link2").src = "https://wbo.ophir.dev/boards/" + roomid;
    _("link2").innerHTML = _("link2").src;
    _("link2").title = 'Click here to add a link for ' + _("link2").src;
    _("link3").src = "https://meet.jit.si/" + roomid;
    _("link3").innerHTML = _("link3").src;
    _("link3").title = 'Click here to add a link for ' + _("link3").src;
}


/**
 * @summary Change the icon size
 * 
 * @param {number} size the expected size.
 * @returns
 */

function ChangeSizeIcons(size) {
    _('pincodediv').style.fontSize = size * 0.72 + 'px';
    _('pincodediv').style.width = size * 3 + 'px';
    _('pincodediv').style.height = size + 'px';

    // _('micon').style.fontSize = size*0.72+'px';
    // _('micon').style.width = size+'px';
    // _('micon').style.height = size+'px';

    // _('questionicon').style.fontSize = size*0.72+'px';
    // _('questionicon').style.width = size+'px';
    // _('questionicon').style.height = size+'px';

    // _('FSimage').style.width = size+'px';
    // _('FSimage').style.height = size+'px';
}

/**
 * @summary Show the selection bar in the information menu
 * 
 * @deprecated ?
 */

function showEachIcon() {
    var checkBox = _("iconon");
    var text = _("eachicon");
    if (!checkBox.checked) {
        text.style.display = "block";
        text.style.padding = "0px 0px 0px 20px"
    } else {
        text.style.display = "none";
    }
}

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
    $("#dialog-mail").dialog("open");
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
    newConfig[2] = (_('subtitlebaron').checked ? "Y" : "N");
    newConfig[3] = String.fromCharCode(65 + _('ColorTitre').selectedIndex);
    newConfig[4] = (_('subtitlepartialon').checked ? "Y" : "N");
    newConfig[5] = String.fromCharCode(65 + _('ColorPartialTitre').selectedIndex);
    newConfig[6] = String.fromCharCode(65 + _('selectfontsize').selectedIndex);
    newConfig[7] = (_('embedwooclapon').checked ? "Y" : "N");
    newConfig[8] = (_('embedshareon').checked ? "Y" : "N");
    newConfig[9] = String.fromCharCode(65 + _('selectopacity').selectedIndex);
    newConfig[10] = (_('arrowson').checked ? "Y" : "N");
    newConfig[11] = (_('iconon').checked ? "Y" : "N");
    newConfig[12] = (_('pincodeon').checked ? "Y" : "N");
    newConfig[13] = (_('microon').checked ? "Y" : "N");
    newConfig[14] = (_('questionon').checked ? "Y" : "N");
    newConfig[15] = (_('fson').checked ? "Y" : "N");
    newConfig[16] = (_('directMicrosoftTranslation').checked ? "Y" : "N");
    newConfig[17] = (_('directMicrosoftTranslationBoth').checked ? "Y" : "N");
    // newConfig[10]=(_('pincodeon').checked?"Y":"N");
    currentconfig = newConfig.join('');
    _("myConfig").value = currentconfig;
    if (window.location.href.indexOf('?') > 0) {
        _("LinkConfig").href = window_location_href + '&myconfig=' + _("myConfig").value;
        // var link = "subtitles.php&myconfig=" + _("myConfig").value;
    } else {
        _("LinkConfig").href = window_location_href + '?myconfig=' + _("myConfig").value;
        // var link = "subtitles.php?myconfig=" + _("myConfig").value;
    }
    var link = _("LinkConfig").href.replace("index.php","subtitles.php");
    var room_id = _("sharedlink").innerHTML.replace("Student.php?","&");
    _("LinkConfig").innerHTML = _("LinkConfig").href;
    _("sublink").innerHTML = link+room_id;
    var link_tot = link+room_id;
    link_tot = link_tot.replaceAll("&amp;","&");
    _("sublink").href = link_tot;
}

/**
 * @summary Apply a configuration (at startup)
 * 
 */

function applyConfig() {
    var config = currentconfig;
    if (config.length != "ABYANACNNCNNNYYYYN".length) return;
    // classroomlanguage
    _('classroomlanguage').selectedIndex = 1 * (config.charCodeAt(0) - 65);
    setLanguageInput(_('classroomlanguage').value);
    // subtitlelanguage
    _('subtitlelanguage').selectedIndex = 1 * (config.charCodeAt(1) - 65);
    setLanguageSubtitle(_('subtitlelanguage').value);
    // subtitlebaron
    _('subtitlebaron').checked = (config.charAt(2) == "Y");
    if (_('subtitlebaron').checked) {
        _('spansubtitlebaron').innerHTML = 'Yes !';
        _('titre').style.visibility = 'visible';
        _('titreleft').style.visibility = 'visible';
        _('titrerightbis').style.visibility = 'visible';
        _('titreright').style.visibility = 'visible';
    } else {
        _('spansubtitlebaron').innerHTML = 'No.';
        _('titre').style.visibility = 'hidden';
        _('titreleft').style.visibility = 'hidden';
        _('titrerightbis').style.visibility = 'hidden';
        _('titreright').style.visibility = 'hidden';
    }
    // pincodeon
    // _('pincodeon').checked=(config.charAt(10) == "Y");
    // if (_('pincodeon').checked) {
    //   _('spanpincodeon').innerHTML='Yes !'; 
    //   _('pincodediv').style.visibility='visible'; 
    // } else {
    //   _('spanpincodeon').innerHTML='No.'; 
    //   _('pincodediv').style.visibility='hidden';
    // }

    // ColorTitre
    _('ColorTitre').selectedIndex = 1 * (config.charCodeAt(3) - 65);
    ChangeColor('titre', _('ColorTitre').value);
    ChangeColor('titreleft', _('ColorTitre').value);
    ChangeColor('titrerightbis', _('ColorTitre').value);
    // subtitlepartialbaron
    _('subtitlepartialon').checked = (config.charAt(4) == "Y");
    if (_('subtitlepartialon').checked) {
        _('spansubtitlepartialon').innerHTML = 'Yes !';
        _('partialtitre').style.visibility = 'visible';
    } else {
        _('spansubtitlepartialon').innerHTML = 'No.';
        _('partialtitre').style.visibility = 'hidden';
    }
    // ColorPartialTitre
    _('ColorPartialTitre').selectedIndex = 1 * (config.charCodeAt(5) - 65);
    ChangeColor('partialtitre', _('ColorPartialTitre').value);
    // selectfontsize
    _('selectfontsize').selectedIndex = 1 * (config.charCodeAt(6) - 65);
    _('titre').style.fontSize = _('selectfontsize').value;
    _('partialtitre').style.fontSize = _('selectfontsize').value;
    _('studentfeedback').style.fontSize = _('selectfontsize').value;
    // Wooclap tab
    _('embedwooclapon').checked = (config.charAt(7) == "Y");
    if (_('embedwooclapon').checked) {
        _('spanembedwooclapon').innerHTML = 'Yes !';
        show('titlewooclap');
    } else {
        _('spanembedwooclapon').innerHTML = 'No.';
        hide('titlewooclap');
    }
    // Share tab
    _('embedshareon').checked = (config.charAt(8) == "Y");
    if (_('embedshareon').checked) {
        _('spanembedshareon').innerHTML = 'Yes !';
        show('titleshare');
    } else {
        _('spanembedshareon').innerHTML = 'No.';
        hide('titleshare');
    }
    //selectopacity
    _('selectopacity').selectedIndex = 1 * (config.charCodeAt(9) - 65);
    _('titre').style.backgroundColor = _('selectopacity').value;
    _('partialtitre').style.backgroundColor = _('selectopacity').value;
    _('studentfeedback').style.backgroundColor = _('selectopacity').value;

    // Arrows
    _('arrowson').checked = (config.charAt(10) == "Y");
    if (_('arrowson').checked) {
        _('titreleft').style.visibility = 'visible';
        _('titrerightbis').style.visibility = 'visible';
        _('titreright').style.visibility = 'visible';
        _('spanarrowson').innerHTML = 'Yes !';
    } else {
        _('spanarrowson').innerHTML = 'No.';
        _('titreleft').style.visibility = 'hidden';
        _('titrerightbis').style.visibility = 'hidden';
        _('titreright').style.visibility = 'hidden';
    }
    // Icons
    _('iconon').checked = (config.charAt(11) == "Y");
    _('spaniconon').innerHTML = (config.charAt(11) == "Y") ? "Yes !" : "No.";
    showEachIcon();

    // Pin code
    _('pincodeon').checked = (config.charAt(12) == "Y");
    _('spanpincodeon').innerHTML = (config.charAt(12) == "Y") ? "Yes !" : "No.";
    _('pincodediv').style.visibility = (config.charAt(12) == "Y") ? "visible" : "hidden";

    // Micro
    _('microon').checked = (config.charAt(13) == "Y");
    _('spanmicroon').innerHTML = (config.charAt(13) == "Y") ? "Yes !" : "No.";
    if (_('microon').checked) {
        show('micon');
    } else {
        hide('micon');
    }
    //_('micon').style.visibility = (config.charAt(13) == "Y") ? "visible" : "hidden";

    // Question
    _('questionon').checked = (config.charAt(14) == "Y");
    _('spanquestionon').innerHTML = (config.charAt(14) == "Y") ? "Yes !" : "No.";
    if (_('questionon').checked) {
        show('questionicon');
    } else {
        hide('questionicon');
    }
    //_('questionicon').style.visibility = (config.charAt(14) == "Y") ? "visible" : "hidden";

    // FS
    _('fson').checked = (config.charAt(15) == "Y");
    _('spanfson').innerHTML = (config.charAt(15) == "Y") ? "Yes !" : "No.";
    //_('FS').style.visibility = (config.charAt(15) == "Y") ? "visible" : "hidden";
    if (_('fson').checked) {
        show('FS');
    } else {
        hide('FS');
    }

    _('directMicrosoftTranslation').checked = (config.charAt(16) == "Y");
    _('spanmicrosoftdirecttranscritionon').innerHTML = (config.charAt(16) == "Y") ? "Yes !" : "No.";
    //_('FS').style.visibility = (config.charAt(15) == "Y") ? "visible" : "hidden";
    if (_('directMicrosoftTranslation').checked) {
        _('subtitlepartialon').checked = true;
        _('spansubtitlepartialon').innerHTML = 'Yes !';
        _('partialtitre').style.visibility = 'visible';

        _('subtitlebaron').checked = false;
        _('spansubtitlebaron').innerHTML = 'No.';
        _('titre').style.visibility = 'hidden';
        _('titreleft').style.visibility = 'hidden';
        _('titrerightbis').style.visibility = 'hidden';
        _('titreright').style.visibility = 'hidden';
    }

    _('directMicrosoftTranslationBoth').checked = (config.charAt(17) == "Y");
    _('spanmicrosoftdirecttranscritionBothon').innerHTML = (config.charAt(17) == "Y") ? "Yes !" : "No.";
   
    updateConfig();
}

/**
 * @summary Function that fix the subtitle bar at the bottom of the page
 * 
 * @deprecated
 */

function fixTitle() {
    for (var f in {
            "partialtitre": 1,
            "studentfeedback": 1
        }) {
        var e = document.getElementById(f);
        e.style.top = (window.innerHeight - e.style.height - 200 - 10) + 'px';
    }

    var e = document.getElementById("titre");
    e.style.top = (window.innerHeight - e.style.height - 100 - 10) + 'px';
}

/**
 * @summary Function that allows to keep the partial title bar inside the window
 * 
 * @todo Do not work properly
 */

function MinTitlePos() {
    for (var f in {
            "partialtitre": 1,
            "studentfeedback": 1
        }) {
        var e = document.getElementById(f);
        var postop = 1 * (e.style.top.replace('px', ''));
        if (postop > window.innerHeight - 50) {
            e.style.top = (window.innerHeight - 50) + 'px';
        }
    }
}

/**
 * @summary Callback function when the question field (Classroom tab) has changed
 * 
 */

function sendclick() {
    var tosend = document.getElementById('question').value.replaceAll('\n', '').trim();
    //alert("Will send : ("+tosend+")");
    if (tosend != "") saveTextQ(tosend);
    document.getElementById('question').value = '';
}
