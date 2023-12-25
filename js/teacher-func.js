// Student feedback

function StudentParleThen(txt, f) {
    //EcrireFeedback(txt);
    setTimeout(f, 100 * txt.length);
}

function AbortTimeout(r) {
    setTimeout(function() {
        r.abort();
    }, 5000);
}
var Studentinputnumber = -1;

function StudentTranslate(ch, l, linput, lineid) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + document.getElementById('classroomid').value + "&lineid=" + lineid, true);

    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            //console.log(this.responseText);
            var response = JSON.parse(this.responseText);
            if ((!response["error"]) && response["data"]["translations"]) {
                StudentToSay['' + lineid] = (response["data"]["translations"][0]["translatedText"]);
                //        Ecrire(lineid+"  :  "+response["data"]["translations"][0]["translatedText"]);
                EcrireFeedback(response["data"]["translations"][0]["translatedText"]);
            } else {
                EcrireFeedback("---- Translation ERROR ----")
            }

        }
    };
    xhr.onloadend = function() {
        StudentCompleteTranslation();
    };
    xhr.send();
    AbortTimeout(xhr);

}

function StudentTranslate2(ch, l, linput, lineid) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + document.getElementById('classroomid').value + "&lineid=" + lineid, true);
    xhr.onloadend = function() {
        StudentCompleteTranslation2();
    };
    xhr.send();
    AbortTimeout(xhr);
}

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

function StudentreadText() {
    var synthesison = true;
    if (synthesison) {
        if (document.getElementById('classroomid').value != "") {
            var request = new XMLHttpRequest();
            request.open("POST", "readtext.php");
            AbortTimeout(request);
            Studentcurrentorder_readtext++;
            var f = new FormData();
            f.append('lineid', Studentinputnumber);
            f.append('ORDER', Studentcurrentorder_readtext);
            f.append('ROOMID', document.getElementById('classroomid').value);
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

function myalert(ch) {
    ch = ch.replaceAll("\n", "<br/>");
    document.getElementById('dialog-invite-p').innerHTML = ch;
    $("#dialog-invite").dialog("open");
}

function InviteExternal() {
    if (document.getElementById('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "createExternal.php");
        AbortTimeout(request);
        var f = new FormData();
        f.append('ROOMID', document.getElementById('classroomid').value);
        f.append('DURATION', document.getElementById('duration').value);
        request.send(f);
        request.onload = function(event) {
            var T = request.responseText.trim();
            if (T == 'ok') {
                var link = document.getElementById('teacherlink').href.replace("index.php", "indexExternal.php");
                navigator.clipboard.writeText(link);
                myalert('External guest created !\nThe following link has been copied. It must be used in the next 7 days.\n<a href="' + link + '">' + link + '</a>');
            }
        }
    }
}




function PopulateVoicesOut() {
    var select = document.getElementById('VoiceOutput');
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

function PopulateVoicesIn() {
    var select = document.getElementById('classroomlanguage');
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

function PopulateVoicesIn() {
    if (TLangInput.length == 0) {
        var select = document.getElementById('classroomlanguage');
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

function PopulateSubtitlesLang() {
    var select = document.getElementById('subtitlelanguage');
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

function PopulateSpeechLang() {
    var select = document.getElementById('speechlanguage');
    for (var i = 0; i < supportedlanguages.length; i++) {
        var lang = supportedlanguages[i].code; //.substr(0,5);
        var opt = document.createElement("option");
        opt.value = lang;
        opt.innerHTML = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
        select.appendChild(opt);
    }
}


function setLanguageInput(i) {
    builtin_language = TLangInput[i];
    //Ecrire(builtin_language);
}

function setLanguageOutput(i) {
    builtin_voice = AllOutputVoices[i];
    //Ecrire(builtin_voice.lang);
}

function setLanguageSubtitle(i) {
    subtitle_language = i;
    //console.log("Subtitles set to "+subtitle_language+ " builtin_language is "+builtin_language);
}

function setLanguageSpeech(i) {
    speech_language = i;
    //console.log("Subtitles set to "+subtitle_language+ " builtin_language is "+builtin_language);
}

function Parle(txt) {
    var discours = new SpeechSynthesisUtterance(txt);
    discours.voice = builtin_voice;
    discours.lang = builtin_voice.lang;
    window.speechSynthesis.speak(discours);
    discours.onend = ready;
    discours.onerror = ready;
}

var readytotalk = true;

function ready() {
    readytotalk = true;
    document.getElementById('bready').style.backgroundColor = 'green';
    StartSpeechRecognition(Traduction);
}

function notready() {
    StopSpeechRecognition();
    readytotalk = false;
    document.getElementById('bready').style.backgroundColor = 'red';
}

function RemoveVerbalTics(ch) {
    var T_verbaltics = document.getElementById('verbaltics').value.trim().split("\n");
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


function Traduction(ch) {
    ch = RemoveVerbalTics(ch);
    var recognitionon = document.getElementById('recognitionon').checked;
    if (recognitionon) {
        //EcrireNB(ch);
        //EcrireTitle(ch);
        saveText(ch);
        var liveon = document.getElementById('liveon').checked;
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

function Translate(ch, l, linput) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + document.getElementById('classroomid').value, true);

    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            // console.log(this.responseText);
            var response = JSON.parse(this.responseText);
            if ((!response["error"]) && response["data"]["translations"]) {
                Parle(response["data"]["translations"][0]["translatedText"]);
                Ecrire("" + response["data"]["translations"][0]["translatedText"]);
            } else {
                Ecrire("---- Translation ERROR ----")
            }

        }
    };
    var d_begin = Date.now();
    console.log(d_begin + " : Sending for translation " + ch + " from " + linput + " to " + l)
    xhr.send();
    AbortTimeout(xhr);
    //EcrirePartial("Request sent....");
}

//var T_durations=[];

function TranslateThen(ch, l, linput, f) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + document.getElementById('classroomid').value, true);

    xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            var d_end = Date.now();
            console.log("Receiving translation in " + (d_end - d_begin) + "ms for " + ch + " from " + linput + " to " + l);
            //console.log(this.responseText);
            //T_durations[ch]=undefined;
            var response = JSON.parse(this.responseText);
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
    AbortTimeout(xhr);
}




function _(el) {
    return document.getElementById(el);
}

function progressHandler(event) {
    var percent = (event.loaded / event.total) * 100;
    _("progressBar").value = Math.round(percent);
    //  _("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}

function addSlideToNB(ch) {
    var tobesaidbetweenslides = '';
    if (document.getElementById('tobesaid').value != '') tobesaidbetweenslides = document.getElementById('tobesaid').value + '.';
    addToNB(tobesaidbetweenslides, 0);
    var Tch = ch.split('<br/>');
    for (var i = 0; i < Tch.length; i++) {
        addToNB(Tch[i], 0);
    }
}

function addToNB(ch, before) {
    var speechlanguage = document.getElementById('speechlanguage').value;
    var tobeinserted = '';
    if (speechlanguage != 'same') tobeinserted = speechlanguage + ':|:';
    var tobesaidbetweenslides = '';
    if (before == 1 && document.getElementById('tobesaid').value != '') tobesaidbetweenslides = tobeinserted + document.getElementById('tobesaid').value + '. <br/>';

    ch = tobesaidbetweenslides + tobeinserted + ch;
    //document.getElementById('notebook').innerHTML+='<br/>'+ch;
    //EcrireTitle(ch);
    //saveTextNB(document.getElementById('notebook').innerHTML);
    var Tch = ch.split('<br/>');
    saveTextTable(Tch, 0);
    //for(var c in Tch) saveText(Tch[c].replace(/<br\/>/gi,"").replace(/<br>/gi,""));
    //saveremotespeech();
}

function saveTextTable(T, i) {
    if (i < T.length) {
        saveText(T[i].replace(/<br\/>/gi, "").replace(/<br>/gi, ""));
        setTimeout(function() {
            saveTextTable(T, i + 1);
        }, 100);
    }
}

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


function ShowImage(n) {
    if (n != previousimage) {
        EcrireNBonly('---------- Slide ' + n + ' ----------');
        previousimage = n;
        //console.log('IMG OBJ = '+document.getElementById("img_"+n));
        //console.log('IMG URL = "'+document.getElementById("img_"+n).src+'"');
        document.getElementById('titreright').innerHTML = n;
        if (document.getElementById("img_" + n) && document.getElementById("img_" + n).src.length > 200) {
            document.getElementById("img_slides").src = document.getElementById("img_" + n).src;
            resizeSlides();

            // Diffusion Image
            var request = new XMLHttpRequest();
            request.open("POST", "saveimage.php");
            var f = new FormData();
            f.append('random', Math.floor(Math.random() * 10000));
            var imageurl = document.getElementById("img_" + n).src;
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
            f.append('ROOMID', document.getElementById('classroomid').value);
            request.send(f);
            AbortTimeout(request);
            //	request.onload = function(event) {};
        }
    }
}


function formateresponse(datatxt) {
    //console.log(datatxt);
    currentPresentation = [];
    //alert(datatxt);
    var res = '';
    var data = JSON.parse(datatxt);
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
                toexec += "document.getElementById('img_" + j + "').src='" + slide["Images"][j - 1] + "'; ";
            }
            setTimeout(toexec, 10);
        } else {
            //      res = res + '<dt><span onclick="addToNB(\''+txtnotes+'\',1); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
            //      res = res + '<dt><span onclick="addSlideToNB(\''+txtnotes+'\'); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
            res = res + '<dt>&nbsp;Slide #' + slide["Slide"] + '<img id="img_' + slide["Slide"] + '" width="50" src=""/></dt>\n';
            var flag = false;
            for (var j = 0; j < slide["Notes"].length; j++) {
                if (slide["Notes"][j].trim() != '') {
                    res = res + '<dd><span onclick="addToNB(\'' + slide["Notes"][j] + '\',0); ShowImage(' + slide["Slide"] + '); currentDiscours=' + currentPresentation.length + '; saveremotespeech();">▶️</span>&nbsp;' + slide["Notes"][j] + '</dd>\n';

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

function saveremotespeech() {
    if (currentPresentation.length > 0) {
        var currentDiscoursB = (currentDiscours - 1 + currentPresentation.length) % currentPresentation.length;
        var currentDiscoursA = (currentDiscours + 1) % currentPresentation.length;
        var txt = currentPresentation[currentDiscoursB]['Discours'] + ':|:' + currentPresentation[currentDiscours]['Discours'] + ':|:' + currentPresentation[currentDiscoursA]['Discours'];
        if (document.getElementById('classroomid').value != "") {
            var request = new XMLHttpRequest();
            request.open("POST", "savespeechforremote.php");
            var f = new FormData();
            f.append('ROOMID', document.getElementById('classroomid').value);
            f.append('SPEECH', txt);
            request.send(f);
            AbortTimeout(request);
            request.onload = function(event) {
                var data = request.responseText;
            };
        }
    }
}


function SkipForward() {
    if (currentPresentation.length > 0) {
        currentDiscours = (currentDiscours + 1) % currentPresentation.length;
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}

function SkipBackward() {
    if (currentPresentation.length > 0) {
        currentDiscours = (currentDiscours - 1 + currentPresentation.length) % currentPresentation.length;
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}


function Forward() {
    if (currentPresentation.length > 0) {
        currentDiscours = (currentDiscours + 1) % currentPresentation.length;
        addToNB(currentPresentation[currentDiscours]['Discours'], 0);
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}

function Backward() {
    if (currentPresentation.length > 0) {
        currentDiscours = (currentDiscours - 1 + currentPresentation.length) % currentPresentation.length;
        addToNB(currentPresentation[currentDiscours]['Discours'], 0);
        ShowImage(currentPresentation[currentDiscours]['Slide']);
        saveremotespeech();
    }
}


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


function completeHandler(event) {
    _("notebookcompleteformatted").innerHTML = formateresponse(event.target.responseText);
    _("progressBar").value = 0;

    // Re-initialize the presentation
    previousimage = -1;
    currentDiscours = 0;
    if (currentPresentation.length > 0) {
        setTimeout(function() {
            ShowImage(currentPresentation[0]['Slide']);
        }, 1000);
    }
}

function SlideNotes() {
    var file = _("ppt_presentation").files[0];
    var scriptfile = "";
    //  if (file.name.endsWith('pptx')) scriptfile = "ExtractSlidesNotes.php";
    if (file.name.endsWith('pptx'))
        if (LoadPPTX) scriptfile = "ExtractSlidesNotes_PPTX_images.php";
        else scriptfile = "ExtractSlidesNotes.php";
    if (file.name.endsWith('odp')) scriptfile = "ExtractSlidesNotes.php";
    if (file.name.endsWith('txt')) scriptfile = "ExtractSlidesNotesTxt.php";
    if (file.name.endsWith('html')) scriptfile = "ExtractSlidesNotesAppHTML.php";
    if (scriptfile == "") {
        alert('Only pptx, odp, html and txt files are allowed !');
        return -1;
    }
    var formdata = new FormData();
    formdata.append("ppt_presentation", file);
    formdata.append("ACCESS", document.getElementById('classroomid').value);
    var xhr = new XMLHttpRequest();

    xhr.open("POST", scriptfile);
    xhr.upload.addEventListener("progress", progressHandler, false);
    //  xhr.addEventListener("load", completeHandler, false);
    xhr.addEventListener("load", completeHandler, false);
    /*  xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
          var response = JSON.parse(this.responseText);
          document.getElementById('notebookcompleteformatted').innerHTML = "<pre>"+response+"</pre>";
          alert(response);
        }
      };
      */
    xhr.timeout = 30000;
    xhr.ontimeout = (e) => {
        console.log("Erreur Timeout !");
        alert("Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.")
    };
    xhr.send(formdata);
    //AbortTimeout(xhr);
}

function SlideNotesUncloud(uncloudlink) {
    var scriptfile = "ExtractSlidesNotes_PPTX_images.php";
    var formdata = new FormData();
    formdata.append("ACCESS", document.getElementById('classroomid').value);
    formdata.append("uncloudlink", uncloudlink);
    var xhr = new XMLHttpRequest();

    xhr.open("POST", scriptfile);
    xhr.upload.addEventListener("progress", progressHandler, false);
    xhr.addEventListener("load", completeHandler, false);
    xhr.timeout = 30000;
    xhr.ontimeout = (e) => {
        console.log("Erreur Timeout !");
        alert("Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.")
    };
    xhr.send(formdata);
    //AbortTimeout(xhr);
}

function SlideNotesUncloudHtml(uncloudhtmllink) {
    var scriptfile = "ExtractSlidesNotesAppHTML.php";
    var formdata = new FormData();
    formdata.append("ACCESS", document.getElementById('classroomid').value);
    formdata.append("uncloudlink", uncloudhtmllink);
    var xhr = new XMLHttpRequest();

    xhr.open("POST", scriptfile);
    xhr.upload.addEventListener("progress", progressHandler, false);
    xhr.addEventListener("load", completeHandler, false);
    xhr.timeout = 30000;
    xhr.ontimeout = (e) => {
        console.log("Erreur Timeout !");
        alert("Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.")
    };
    xhr.send(formdata);
    //AbortTimeout(xhr);
}


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
                    callback(event.results[i][0].transcript);
                } else {
                    partial += ' ' + event.results[i][0].transcript;
                }
            }
            if (partial.trim() != '') {
                if (document.getElementById('recognitionon').checked) EcrirePartial(RemoveVerbalTics(partial));
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

function StopSpeechRecognition() {
    builtin_recognitionon = false;
    builtin_recognition.stop();
}


function InitVoices() {
    PopulateVoicesIn();
    PopulateVoicesOut();
    setLanguageInput(0);
    setLanguageOutput(0);
}

function EcrireNB(ch) {
    // if (document.getElementById('notebook').innerHTML == "")
    //  document.getElementById('notebook').innerHTML=ch;
    //else 
    document.getElementById('notebook').innerHTML = document.getElementById('notebook').innerHTML + ch + "<br/>";
    //EcrireTitle(ch);
}

function EcrireNBonly(ch) {
    document.getElementById('notebook').innerHTML = document.getElementById('notebook').innerHTML + ch + "<br/>";
    //  if (document.getElementById('notebook').innerHTML == "")
    //    document.getElementById('notebook').innerHTML=ch;
    //  else document.getElementById('notebook').innerHTML=document.getElementById('notebook').innerHTML+"<br/>"+ch;
}


function EcrireTitle(ch) {
    var linput = builtin_language.substr(0, 2).toLowerCase();
    if (ch.indexOf(':|:') >= 0) { // If there are a language indication
        var Tch = ch.split(':|:');
        linput = Tch[0].substr(0, 2).toLowerCase();
        ch = Tch[1];
    }
    var loutput = subtitle_language.substr(0, 2).toLowerCase();
    if ((!document.getElementById('subtitleon').checked) || (linput == loutput)) {
        document.getElementById('titre').innerHTML = ch;
        // for scrollbar
        setScrollvalues(ch.length);
    } else {
        TranslateThen(ch, loutput, linput, function(ch) {
            document.getElementById('titre').innerHTML = ch;
            setScrollvalues(ch.length);
        });
    }
}

function EcrirePartial(ch) {
    if (ch != 'undefined' && ch != undefined) document.getElementById('partialtitre').innerHTML = ch;
}

function EcrireFeedback(ch) {
    EcrireNBonly(ch);
    document.getElementById('titre').innerHTML = ch;
    document.getElementById('studentfeedback').innerHTML = ch;
}

function Ecrire(ch) {
    if (document.getElementById('output').innerHTML == "")
        document.getElementById('output').innerHTML = ch;
    else document.getElementById('output').innerHTML = ch + "<br/>" + document.getElementById('output').innerHTML;
}

function hide(e) {
    document.getElementById(e).style.display = 'none';
}

function show(e) {
    document.getElementById(e).style.display = 'block';
}


//document.body.addEventListener('load',Init,true);

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
    f.append('ROOMID', document.getElementById('classroomid').value);
    f.append('LastLines', MaxSavedLines);
    request.send(f);
    AbortTimeout(request);

    //	request.onload = function(event) {};
}

function saveTextNB(ch) {
    var request = new XMLHttpRequest();
    request.open("POST", "savetextNB.php");
    var f = new FormData();
    //f.append('lineid',inputnumber);
    inputnumber = ch.split('<br/>').length;
    f.append('text', ch);
    f.append('inputlanguage', builtin_language);
    f.append('ROOMID', document.getElementById('classroomid').value);
    request.send(f);
    AbortTimeout(request);

    //	request.onload = function(event) {};
}

function LaunchReadTextQ() {
    readTextQ();
    setTimeout(LaunchReadTextQ, delayReadtextQ);
}

var delayRemote = 200;

function LaunchRemote() {
    remote();
    setTimeout(LaunchRemote, delayRemote);
}

var delayStudentReadtext = 500;

function LaunchStudentReadText() {
    StudentreadText();
    setTimeout(LaunchStudentReadText, delayStudentReadtext);
}

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

function Lancer() {
    LaunchRemote();
    LaunchReadTextQ();
    LaunchStudentReadText();
    setInterval(ScrollBar, scrollinterval);
    applyConfig();
}



function Init() {
    PopulateSubtitlesLang();
    PopulateSpeechLang();

    /*  document.getElementById('question').addEventListener('keyup', function (event) {
        if (event.code === 'Enter') {
            saveTextQ(document.getElementById('question').value);
          event.preventDefault();
          event.stopPropagation();
        }
      },false);
    */
    resizeSlides();

    document.getElementById('pincode').value = ("" + Math.floor(100000 + Math.random() * 100000)).substr(1);
    document.getElementById('sharedpin').innerHTML = document.getElementById('pincode').value;
    document.getElementById('sharedpin2').innerHTML = document.getElementById('pincode').value;
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

            if (event.code === 'Space') {
                Micro();
            }
            if (event.code === 'KeyB') {
                Micro();
            }
            event.preventDefault();
            event.stopPropagation();
        }
    };

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
    ChangeColor('titre', document.getElementById('ColorTitre').value);
    ChangeColor('titreleft', document.getElementById('ColorTitre').value);
    ChangeColor('titrerightbis', document.getElementById('ColorTitre').value);
    ChangeColor('partialtitre', document.getElementById('ColorPartialTitre').value);
    ChangeColor('studentfeedback', document.getElementById('Colorstudentfeedback').value);

    //setTimeout(Micro,1000);
    //setTimeout(Micro,1500);
    //version addEventListener
    window.addEventListener('offline', (event) => {
        console.log("La connexion au réseau est perdue.");
        document.getElementById('connexion').style.visibility = 'visible';
        delayStudentReadtext = 100000;
        delayReadtextQ = 100000;
        delayRemote = 100000;
    });
    window.addEventListener('online', (event) => {
        console.log("La connexion au réseau est rétablie.");
        document.getElementById('connexion').style.visibility = 'hidden';
        delayStudentReadtext = 500;
        delayReadtextQ = 100;
        delayRemote = 200;
    });

    if (document.getElementById('embedwooclapon').checked) {
        document.getElementById('spanembedwooclapon').innerHTML = 'Yes !';
        show('titlewooclap');
    } else {
        document.getElementById('spanembedwooclapon').innerHTML = 'No.';
        hide('titlewooclap');
    }
    if (zoomid != '') startZoom(zoomid);
    if (uncloudlink != '') SlideNotesUncloud(uncloudlink);
    if (uncloudhtmllink != '') SlideNotesUncloudHtml(uncloudhtmllink);
    if (targettab > 0) document.getElementById('ttab-' + targettab).click();
}

// Special ZOOM session
function opentab_url(url) {
    var tabnumber = 1;
    while (tabnumber < 4 && document.getElementById('extraurl' + tabnumber).value != '') tabnumber++;
    document.getElementById('extraurl' + tabnumber).value = url;
    document.getElementById('embedET' + tabnumber).checked = true;
    showET(document.getElementById('embedET' + tabnumber), tabnumber);
}

function startZoom(zoomid) {
    var lnk = 'https://univ-nantes-fr.zoom.us/wc/join/' + zoomid;
    opentab_url(lnk);
    if (window.confirm("Are you the host for this zoom session ?")) {
        window.open('https://univ-nantes-fr.zoom.us/wc/' + zoomid + '/start');
    }
}


function ClearCR() {
    document.getElementById('classroom').innerHTML = '';
    ScreenCR = [];
}

function rgb(T) {
    return "rgb(" + T[0] + "," + T[1] + "," + T[2] + ")";
}


function recharger() {
    ClearCR();
    inputnumberQ = 0;
    readTextQ();

}


function RefreshCR() {
    document.getElementById('classroom').innerHTML = "";
    for (var i = 0; i < ScreenCR.length; i++) {
        if (ScreenCR[i] != undefined) {
            if (document.getElementById('classroom').innerHTML == "") {
                document.getElementById('classroom').innerHTML = ScreenCR[i] + '\n';
            } else {
                document.getElementById('classroom').innerHTML = document.getElementById('classroom').innerHTML + "<br/><br/>" + ScreenCR[i] + '\n';
            }
        }
    }
}

function EcrireCR(who, ch, order) {
    if (!ColorsForCR[who]) {
        var r = Math.floor(Math.random() * 200 + 55);
        var g = Math.floor(Math.random() * 200 + 55);
        var b = Math.floor(Math.random() * 200 + 55);
        var bg = [r, g, b];
        var fg = [0, 0, 0];
        ColorsForCR[who] = [bg, fg, 20];
    }

    /*
      if (document.getElementById('classroom').innerHTML == "")
        document.getElementById('classroom').innerHTML='<span style="color:'+rgb(ColorsForCR[who][1])+'; background-color:'+rgb(ColorsForCR[who][0])+'; margin-left: '+ColorsForCR[who][2]+'px; ">'+(who+' : '+ch)+'<span>\n';
      else document.getElementById('classroom').innerHTML=document.getElementById('classroom').innerHTML+"<br/><br/>"+'<span style="display:inline;">'+order+'</span>\t<span style="color:'+rgb(ColorsForCR[who][1])+'; background-color:'+rgb(ColorsForCR[who][0])+'; margin-left: '+ColorsForCR[who][2]+'px;">'+(who+' : '+ch)+'<span>\n';
      */
    ScreenCR[order] = '<span style="color:' + rgb(ColorsForCR[who][1]) + '; background-color:' + rgb(ColorsForCR[who][0]) + '; margin-left: ' + ColorsForCR[who][2] + 'px; ">' + (who + ' : ' + ch) + '<span>';
    RefreshCR();
}

document.addEventListener('submit', event => {
    event.preventDefault();
    // actual logic, e.g. validate the form
    //alert('Form submission cancelled.');
});


function TailleCR() {
    return document.getElementById('classroom').innerHTML.split('<br/>').length;
}

function setLanguageStr(l) {
    console.log("Remote says set language to (" + l + ")");
    // Try to find the language in the language list
    var s = document.getElementById('classroomlanguage');
    var finded = -1
    for (var i = 0; i < s.options.length && finded == -1; i++) {
        if (l.substr(0, 2).toLowerCase() == s.options[i].innerHTML.substr(0, 2).toLowerCase()) finded = i;
    }
    if (finded != -1) {
        s.selectedIndex = finded;
        setLanguageInput(document.getElementById('classroomlanguage').value);
        Micro();
        Micro();
    }
}

function remote() {
    if (document.getElementById('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "saveremoteorder.php");
        var f = new FormData();
        f.append('ROOMID', document.getElementById('classroomid').value);
        f.append('APPID', document.getElementById('appid').value);
        f.append('ORDER', '');
        request.send(f);
        AbortTimeout(request);

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
                    if (fromremote == document.getElementById('appid').value || fromremote == '') Micro();
                }
                if (order.substr(0, "language".length) == "language") {
                    var options = order.substr("language".length).trim().split("|");
                    var to_lang = options[0].trim();
                    var fromremote = options[1].trim();
                    console.log('Order for (' + document.getElementById('appid').value + ') from (' + fromremote + ') for language (' + to_lang + ')');
                    if (fromremote == document.getElementById('appid').value || fromremote == '') setLanguageStr(to_lang);
                }
            }
        };
    }
}

function updateUsers() {
    document.getElementById('connectedusers').innerHTML = (NBconnectedusers) + " connected users";
    document.getElementById('connectedusers').title = Tconnectedusers.join(", ");
}

function readTextQ() {
    if (document.getElementById('classroomid').value != "") {
        var request = new XMLHttpRequest();
        request.open("POST", "readtextQ.php");
        var f = new FormData();
        f.append('lineid', 0);
        f.append('ROOMID', document.getElementById('classroomid').value);
        request.send(f);
        AbortTimeout(request);
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
                    if (document.getElementById('extraurl' + tabnumber).value != url) {
                        document.getElementById('extraurl' + tabnumber).value = url;
                        document.getElementById('embedET' + tabnumber).checked = true;
                        showET(document.getElementById('embedET' + tabnumber), tabnumber);
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

function draw() {
    if (timenew_question > 0) {
        timenew_question--;
        setTimeout(draw, 1000);
    } else {
        var tab = document.getElementById('questionicon');
        tab.innerHTML = '<i class=\'material-icons big\'>thumb_up</i>';
        tab.style.backgroundColor = 'green';
    }
}


function EventNewQuestion() {
    if (timenew_question == 0) {
        timenew_question = 10;
        var tab = document.getElementById('questionicon');
        tab.innerHTML = '<i class=\'material-icons blink\'>warning</i>';
        tab.style.backgroundColor = 'orange';
        draw();
    } else timenew_question = 10;
}

function saveTextQ(ch) {
    var request = new XMLHttpRequest();
    request.open("POST", "savetextQ.php");
    var f = new FormData();
    f.append('lineid', 'Teacher');
    //inputnumberQ++;
    f.append('text', ch);
    f.append('inputlanguage', builtin_language);
    f.append('ROOMID', document.getElementById('classroomid').value);
    request.send(f);
    AbortTimeout(request);
    request.onload = function(event) {
        readTextQ();
    };
}

function TranslateQ(ch, l, linput, name, order) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput, true);

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
    AbortTimeout(xhr);
}

function FromCompleteLessonToTable() {
    var txt = document.getElementById('notebookcompleteraw').innerHTML;
    document.getElementById('notebookcompleteformatted').innerHTML = txt;
}

function playlesson() {}

function GoFS() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        document.getElementById('bar').style.transform = "scaleY(1)";
        //document.getElementById('bar').style.bottom="10px";
        //resizeSlides();
    } else {
        document.exitFullscreen();
        document.getElementById('bar').style.transform = "scaleY(1)";
        //resizeSlides();
    }
}

function resizeWindow(wname) {
    var wc = document.getElementById(wname);
    wc.style.width = (window.innerWidth - 20) + "px";
    wc.style.height = (window.innerHeight - 48) + "px";
    wc.style.top = "48px";
    wc.style.left = "0px";
}

function resizeSlides() {
    var slide = document.getElementById('img_slides');
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

function Synchronize(pin) {
    var request = new XMLHttpRequest();
    request.open("POST", "synchronize.php");
    var f = new FormData();
    f.append('pincode', pin);
    request.send(f);
    AbortTimeout(request);
    request.onload = function(event) {
        if (request.responseText != "") {
            document.getElementById('classroomid').value = request.responseText;
            document.getElementById('pincode').value = pin;
            document.getElementById('sharedpin').innerHTML = pin;
            document.getElementById('sharedpin2').innerHTML = pin;
            updateLinks();
        } else {
            alert("ERROR : unknown pin code");
        }
    };
}

function Synchronize_save() {
    if (document.getElementById('pincode').value != "") {
        if (document.getElementById('classroomid').value != "") {
            var request = new XMLHttpRequest();
            request.open("POST", "synchronize_savepin.php");
            var f = new FormData();
            f.append('pincode', document.getElementById('pincode').value);
            f.append('roomid', document.getElementById('classroomid').value);
            request.send(f);
            AbortTimeout(request);
            request.onload = function(event) {};
        }
    }
}


function showET(obj, num) {
    if (obj.checked) {
        var url = document.getElementById('extraurl' + num).value;
        if (url != "") {
            document.getElementById('spanembedET' + num).innerHTML = 'Yes !';
            show('titleextratab' + num);
            document.getElementById('extratab' + num).src = url;
        } else {
            alert('Enter your url first !');
            obj.checked = false;
            document.getElementById('spanembedET' + num).innerHTML = 'No.';
            hide('titleextratab' + num);
        }
    } else {
        document.getElementById('spanembedET' + num).innerHTML = 'No.';
        hide('titleextratab' + num);
    }
}

function Micro() {
    if (!builtin_microsofttranscritionon) {
        builtin_recognition.abort();
    }
    var mybox = document.getElementById('micon');
    document.getElementById('recognitionon').checked = !document.getElementById('recognitionon').checked;
    builtin_recognitionon = document.getElementById('recognitionon').checked;
    if (document.getElementById('recognitionon').checked) {
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
    if (document.getElementById('recognitionon').checked && !builtin_microsofttranscritionon) {
        StartSpeechRecognition(Traduction);
    }
    /* else {
          builtin_recognition.abort();
        }*/

}


function ScrollBar() {
    currentscroll = currentscroll - currentindent;
    if (currentscroll < 0) currentscroll = 0;
    document.getElementById('scrollbar').style.transform = 'scaleX(' + currentscroll + ')';
}

function setScrollvalues(l) {
    currentscroll = 1;
    var txtlength = Math.max(20, l);
    //txtlength=Math.min(200,txtlength);
    var duration = 55 * txtlength; // estimated duration in ms
    currentindent = scrollinterval / duration;
}


function updateLinks() {
    var roomid = document.getElementById('classroomid').value;
    document.getElementById("link1").src = "https://tableaunoir.github.io/?id=" + roomid;
    document.getElementById("link1").innerHTML = document.getElementById("link1").src;
    document.getElementById("link2").src = "https://wbo.ophir.dev/boards/" + roomid;
    document.getElementById("link2").innerHTML = document.getElementById("link2").src;
    document.getElementById("link3").src = "https://meet.jit.si/" + roomid;
    document.getElementById("link3").innerHTML = document.getElementById("link3").src;
}

function ChangeColor(boxname, color) {
    var [bgcolor, bordercolor, textcolor] = color.split('|');
    var e = document.getElementById(boxname);
    var opacity = document.getElementById("selectopacity").value
    if (textcolor) e.style.color = 'rgb(' + textcolor + ')';
    else e.style.color = 'rgb(255,255,255)';
    e.style.backgroundColor = 'rgba(' + bgcolor + ',' + opacity + ')';
    e.style.boxShadow = '2px 2px 5px rgb(' + bgcolor + ')';
    e.style.textShadow = '2px 0 0 rgb(' + bordercolor + '), -2px 0 0 rgb(' + bordercolor + '), 0 2px 0 rgb(' + bordercolor + '), 0 -2px 0 rgb(' + bordercolor + '), 1px 1px rgb(' + bordercolor + '), -1px -1px 0 rgb(' + bordercolor + '), 1px -1px 0 rgb(' + bordercolor + '), -1px 1px 0 rgb(' + bordercolor + ')';
}

function ChangeOpacity(boxname, opacity) {
    var e = document.getElementById(boxname);
    var color = e.style.backgroundColor;
    var [red, green, blue] = color.substring(color.indexOf('(') + 1, color.lastIndexOf(')')).split(/,\s*/);
    var bgcolor = [red, green, blue];
    e.style.backgroundColor = 'rgba(' + bgcolor + ',' + opacity + ')';
}

function ChangeSizeIcons(size) {
    document.getElementById('pincodediv').style.fontSize = size * 0.72 + 'px';
    document.getElementById('pincodediv').style.width = size * 3 + 'px';
    document.getElementById('pincodediv').style.height = size + 'px';

    // document.getElementById('micon').style.fontSize = size*0.72+'px';
    // document.getElementById('micon').style.width = size+'px';
    // document.getElementById('micon').style.height = size+'px';

    // document.getElementById('questionicon').style.fontSize = size*0.72+'px';
    // document.getElementById('questionicon').style.width = size+'px';
    // document.getElementById('questionicon').style.height = size+'px';

    // document.getElementById('FSimage').style.width = size+'px';
    // document.getElementById('FSimage').style.height = size+'px';
}

function showEachIcon() {
    var checkBox = document.getElementById("iconon");
    var text = document.getElementById("eachicon");
    if (!checkBox.checked) {
        text.style.display = "block";
        text.style.padding = "0 0 0 20px"
    } else {
        text.style.display = "none";
    }
}

function Embedcode() {
    var url = document.getElementById('chatlink').href;
    //  navigator.clipboard.writeText('<iframe style="width:100%; overflow: auto; height: 500px" src="'+url+'"></iframe>');
    //  alert("HTML code copied to clipboard !");
    document.getElementById('embedcontent').value = '<iframe style="width:100%; overflow: auto; height: 500px" src="' + url + '"></iframe>';
}

function sendnotebook() {
    document.getElementById('dialog_MailFrom').value = 'bourdon-j@univ-nantes.fr';
    document.getElementById('dialog_MailTo').value = 'your.mail@univ-nantes.fr';
    document.getElementById('dialog_MailSubject').value = 'Content of the lesson ' + document.getElementById('classroomid').value;
    document.getElementById('dialog_MailBody').innerHTML = document.getElementById('notebook').innerHTML;
    $("#dialog-mail").dialog("open");
}



function opentab(e) {
    var url = e.innerHTML;
    var tabnumber = 1;
    while (tabnumber < 4 && document.getElementById('extraurl' + tabnumber).value != '') tabnumber++;
    document.getElementById('extraurl' + tabnumber).value = url;
    document.getElementById('embedET' + tabnumber).checked = true;
    showET(document.getElementById('embedET' + tabnumber), tabnumber);
}


function updateConfig() {
    var newConfig = [];
    newConfig[0] = String.fromCharCode(65 + document.getElementById('classroomlanguage').selectedIndex);
    newConfig[1] = String.fromCharCode(65 + document.getElementById('subtitlelanguage').selectedIndex);
    newConfig[2] = (document.getElementById('subtitlebaron').checked ? "Y" : "N");
    newConfig[3] = String.fromCharCode(65 + document.getElementById('ColorTitre').selectedIndex);
    newConfig[4] = (document.getElementById('subtitlepartialon').checked ? "Y" : "N");
    newConfig[5] = String.fromCharCode(65 + document.getElementById('ColorPartialTitre').selectedIndex);
    newConfig[6] = String.fromCharCode(65 + document.getElementById('selectfontsize').selectedIndex);
    newConfig[7] = (document.getElementById('embedwooclapon').checked ? "Y" : "N");
    newConfig[8] = (document.getElementById('embedshareon').checked ? "Y" : "N");
    newConfig[9] = String.fromCharCode(65 + document.getElementById('selectopacity').selectedIndex);
    newConfig[10] = (document.getElementById('arrowson').checked ? "Y" : "N");
    newConfig[11] = (document.getElementById('iconon').checked ? "Y" : "N");
    newConfig[12] = (document.getElementById('pincodeon').checked ? "Y" : "N");
    newConfig[13] = (document.getElementById('microon').checked ? "Y" : "N");
    newConfig[14] = (document.getElementById('questionon').checked ? "Y" : "N");
    newConfig[15] = (document.getElementById('fson').checked ? "Y" : "N");
    // newConfig[10]=(document.getElementById('pincodeon').checked?"Y":"N");
    currentconfig = newConfig.join('');
    document.getElementById("myConfig").value = currentconfig;
    if (window.location.href.indexOf('?') > 0) {
        document.getElementById("LinkConfig").href = window_location_href + '&myconfig=' + document.getElementById("myConfig").value;
    } else {
        document.getElementById("LinkConfig").href = window_location_href + '?myconfig=' + document.getElementById("myConfig").value;
    }
    document.getElementById("LinkConfig").innerHTML = document.getElementById("LinkConfig").href;
}

function applyConfig() {
    var config = currentconfig;
    if (config.length != "ABYANACNNCNNNYYY".length) return;
    // classroomlanguage
    document.getElementById('classroomlanguage').selectedIndex = 1 * (config.charCodeAt(0) - 65);
    setLanguageInput(document.getElementById('classroomlanguage').value);
    // subtitlelanguage
    document.getElementById('subtitlelanguage').selectedIndex = 1 * (config.charCodeAt(1) - 65);
    setLanguageSubtitle(document.getElementById('subtitlelanguage').value);
    // subtitlebaron
    document.getElementById('subtitlebaron').checked = (config.charAt(2) == "Y");
    if (document.getElementById('subtitlebaron').checked) {
        document.getElementById('spansubtitlebaron').innerHTML = 'Yes !';
        document.getElementById('titre').style.visibility = 'visible';
        document.getElementById('titreleft').style.visibility = 'visible';
        document.getElementById('titrerightbis').style.visibility = 'visible';
        document.getElementById('titreright').style.visibility = 'visible';
    } else {
        document.getElementById('spansubtitlebaron').innerHTML = 'No.';
        document.getElementById('titre').style.visibility = 'hidden';
        document.getElementById('titreleft').style.visibility = 'hidden';
        document.getElementById('titrerightbis').style.visibility = 'hidden';
        document.getElementById('titreright').style.visibility = 'hidden';
    }
    // pincodeon
    // document.getElementById('pincodeon').checked=(config.charAt(10) == "Y");
    // if (document.getElementById('pincodeon').checked) {
    //   document.getElementById('spanpincodeon').innerHTML='Yes !'; 
    //   document.getElementById('pincodediv').style.visibility='visible'; 
    // } else {
    //   document.getElementById('spanpincodeon').innerHTML='No.'; 
    //   document.getElementById('pincodediv').style.visibility='hidden';
    // }

    // ColorTitre
    document.getElementById('ColorTitre').selectedIndex = 1 * (config.charCodeAt(3) - 65);
    ChangeColor('titre', document.getElementById('ColorTitre').value);
    ChangeColor('titreleft', document.getElementById('ColorTitre').value);
    ChangeColor('titrerightbis', document.getElementById('ColorTitre').value);
    // subtitlepartialbaron
    document.getElementById('subtitlepartialon').checked = (config.charAt(4) == "Y");
    if (document.getElementById('subtitlepartialon').checked) {
        document.getElementById('spansubtitlepartialon').innerHTML = 'Yes !';
        document.getElementById('partialtitre').style.visibility = 'visible';
    } else {
        document.getElementById('spansubtitlepartialon').innerHTML = 'No.';
        document.getElementById('partialtitre').style.visibility = 'hidden';
    }
    // ColorPartialTitre
    document.getElementById('ColorPartialTitre').selectedIndex = 1 * (config.charCodeAt(5) - 65);
    ChangeColor('partialtitre', document.getElementById('ColorPartialTitre').value);
    // selectfontsize
    document.getElementById('selectfontsize').selectedIndex = 1 * (config.charCodeAt(6) - 65);
    document.getElementById('titre').style.fontSize = document.getElementById('selectfontsize').value;
    document.getElementById('partialtitre').style.fontSize = document.getElementById('selectfontsize').value;
    document.getElementById('studentfeedback').style.fontSize = document.getElementById('selectfontsize').value;
    // Wooclap tab
    document.getElementById('embedwooclapon').checked = (config.charAt(7) == "Y");
    if (document.getElementById('embedwooclapon').checked) {
        document.getElementById('spanembedwooclapon').innerHTML = 'Yes !';
        show('titlewooclap');
    } else {
        document.getElementById('spanembedwooclapon').innerHTML = 'No.';
        hide('titlewooclap');
    }
    // Share tab
    document.getElementById('embedshareon').checked = (config.charAt(8) == "Y");
    if (document.getElementById('embedshareon').checked) {
        document.getElementById('spanembedshareon').innerHTML = 'Yes !';
        show('titleshare');
    } else {
        document.getElementById('spanembedshareon').innerHTML = 'No.';
        hide('titleshare');
    }
    //selectopacity
    document.getElementById('selectopacity').selectedIndex = 1 * (config.charCodeAt(9) - 65);
    document.getElementById('titre').style.backgroundColor = document.getElementById('selectopacity').value;
    document.getElementById('partialtitre').style.backgroundColor = document.getElementById('selectopacity').value;
    document.getElementById('studentfeedback').style.backgroundColor = document.getElementById('selectopacity').value;

    // Arrows
    document.getElementById('arrowson').checked = (config.charAt(10) == "Y");
    if (document.getElementById('arrowson').checked) {
        document.getElementById('titreleft').style.visibility = 'visible';
        document.getElementById('titrerightbis').style.visibility = 'visible';
        document.getElementById('titreright').style.visibility = 'visible';
        document.getElementById('spanarrowson').innerHTML = 'Yes !';
    } else {
        document.getElementById('spanarrowson').innerHTML = 'No.';
        document.getElementById('titreleft').style.visibility = 'hidden';
        document.getElementById('titrerightbis').style.visibility = 'hidden';
        document.getElementById('titreright').style.visibility = 'hidden';
    }
    // Icons
    document.getElementById('iconon').checked = (config.charAt(11) == "Y");
    document.getElementById('spaniconon').innerHTML = (config.charAt(11) == "Y") ? "Yes !" : "No.";
    showEachIcon();

    // Pin code
    document.getElementById('pincodeon').checked = (config.charAt(12) == "Y");
    document.getElementById('spanpincodeon').innerHTML = (config.charAt(12) == "Y") ? "Yes !" : "No.";
    document.getElementById('pincodediv').style.visibility = (config.charAt(12) == "Y") ? "visible" : "hidden";

    // Micro
    document.getElementById('microon').checked = (config.charAt(13) == "Y");
    document.getElementById('spanmicroon').innerHTML = (config.charAt(13) == "Y") ? "Yes !" : "No.";
    document.getElementById('micon').style.visibility = (config.charAt(13) == "Y") ? "visible" : "hidden";

    // Question
    document.getElementById('questionon').checked = (config.charAt(14) == "Y");
    document.getElementById('spanquestionon').innerHTML = (config.charAt(14) == "Y") ? "Yes !" : "No.";
    document.getElementById('questionicon').style.visibility = (config.charAt(14) == "Y") ? "visible" : "hidden";

    // FS
    document.getElementById('fson').checked = (config.charAt(15) == "Y");
    document.getElementById('spanfson').innerHTML = (config.charAt(15) == "Y") ? "Yes !" : "No.";
    document.getElementById('FS').style.visibility = (config.charAt(15) == "Y") ? "visible" : "hidden";

    updateConfig();
}