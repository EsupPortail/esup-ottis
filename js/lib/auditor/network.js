/**
 * Network functions for auditor
 */

/**
 * @summary Debug function for counting the number of pending requests
 */
function increasependingrequests() {
    let nb = _('nbrequests');
    if (!nb) return;
    nb.innerHTML = nb.innerHTML * 1 + 1;
}

/**
 * @summary Debug function for counting the number of pending requests
 */
function decreasependingrequests() {
    let nb = _('nbrequests');
    if (!nb) return;
    nb.innerHTML = nb.innerHTML * 1 - 1;
}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue).
 */
function readText() {
    if (_('classroomid').value != "") {
        increasependingrequests();
        currentorder_readtext++;
        let f = new FormData();
        f.append('lineid', inputnumber);
        f.append('ORDER', currentorder_readtext);
        f.append('ROOMID', _('classroomid').value);
        f.append('room_token', window.ROOM_TOKEN);
        addCsrfToken(f);

        let request = createXhrRequest('POST', 'readtext.php', {
            onSuccess: function(xhr) {
                decreasependingrequests();
                let T = xhr.responseText.split('\n');
                let maxi = -1;
                let readedorder = ('0' + T[0]).split(';')[0] * 1;
                if (readedorder > maxorder_readtext) {
                    maxorder_readtext = readedorder;
                    for (let i = 0; i < T.length; i++) {
                        let L = T[i].split(';');
                        if (L.length > 3) {
                            if (1 * L[1] > maxi) maxi = 1 * L[1];
                            if (AllPhrases[1 * L[1]] == undefined) {
                                if (_('classroomid').value !== '' && _('classroomid').value !== 'UNKNOWN') {
                                    AllPhrases[1 * L[1]] = true;
                                    
                                    let sourceLang = L[2];
                                    let sourceText = decodeHtmlEntities(L[3]);
                                    let targetLang = L[4];
                                    let targetText = L.length >= 6 ? decodeHtmlEntities(L[5]) : null;
                                    
                                    let currentLang = '';
                                    if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang && typeof builtin_voice.lang === 'string') {
                                        currentLang = builtin_voice.lang.substr(0, 2);
                                    } else if (typeof builtin_language !== 'undefined' && builtin_language && typeof builtin_language === 'string') {
                                        currentLang = builtin_language.substr(0, 2);
                                    }
                                    
                                    if (L.length >= 3 && L[2] && currentLang === L[2].substr(0, 2)) {
                                        Ecrire(sourceText);
                                        EcrireNB(sourceText);
                                        ToSay['' + L[0]] = sourceText;
                                    } else if (targetLang && currentLang === targetLang.substr(0, 2) && targetText) {
                                        Ecrire(targetText);
                                        EcrireNB(sourceText);
                                        ToSay['' + L[0]] = targetText;
                                    } else {
                                        ToTranslate.push([L[0], sourceLang, sourceText, targetLang, targetText]);
                                        EcrireNB(sourceText);
                                    }
                                }
                            }
                        }
                    }
                }
                if (maxi > inputnumber) inputnumber = maxi;
                CompleteTranslation();
            },
            data: f,
            timeout: 10000,
            context: 'readText'
        });
        request.send(f);
    }
}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue) for subtitles application.
 */
function readTextSub() {
    if (_('classroomid').value != "") {
        increasependingrequests();
        currentorder_readtext++;
        let f = new FormData();
        f.append('lineid', inputnumber);
        f.append('ORDER', currentorder_readtext);
        f.append('ROOMID', _('classroomid').value);
        f.append('room_token', window.ROOM_TOKEN);
        addCsrfToken(f);

        let request = createXhrRequest('POST', 'readtext.php', {
            onSuccess: function(xhr) {
                decreasependingrequests();
                let T = xhr.responseText.split('\n');
                let maxi = -1;
                let readedorder = ('0' + T[0]).split(';')[0] * 1;
                if (readedorder > maxorder_readtext) {
                    maxorder_readtext = readedorder;
                    for (let i = 0; i < T.length; i++) {
                        let L = T[i].split(';');
                        if (L.length > 3) {
                            if (1 * L[1] > maxi) maxi = 1 * L[1];
                            if (AllPhrases[1 * L[1]] == undefined) {
                                if (_('classroomid').value !== '' && _('classroomid').value !== 'UNKNOWN') {
                                    AllPhrases[1 * L[1]] = true;
                                    
                                    let sourceLang = L[2];
                                    let sourceText = decodeHtmlEntities(L[3]);
                                    let targetLang = L[4];
                                    let targetText = L.length >= 6 ? decodeHtmlEntities(L[5]) : null;
                                    
                                    let currentLang = '';
                                    if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang && typeof builtin_voice.lang === 'string') {
                                        currentLang = builtin_voice.lang.substr(0, 2);
                                    } else if (typeof builtin_language !== 'undefined' && builtin_language && typeof builtin_language === 'string') {
                                        currentLang = builtin_language.substr(0, 2);
                                    }
                                    
                                    if (L.length >= 3 && L[2] && currentLang === L[2].substr(0, 2)) {
                                        EcrireNBSub(sourceText);
                                        ToSay['' + L[0]] = sourceText;
                                    } else if (targetLang && currentLang === targetLang.substr(0, 2) && targetText) {
                                        EcrireNBSub(targetText);
                                        ToSay['' + L[0]] = targetText;
                                    } else {
                                        ToTranslate.push([L[0], sourceLang, sourceText, targetLang, targetText]);
                                    }
                                }
                            }
                        }
                    }
                }
                if (maxi > inputnumber) inputnumber = maxi;
                CompleteTranslation();
            },
            data: f,
            timeout: 10000,
            context: 'readTextSub'
        });
        request.send(f);
    }
}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue) for transcribed subtitles application.
 */
function readTextSubTranscript() {
    if (_('classroomid').value != "") {
        increasependingrequests();
        currentorder_readtext++;
        let f = new FormData();
        f.append('lineid', inputnumber);
        f.append('ORDER', currentorder_readtext);
        f.append('ROOMID', _('classroomid').value);
        f.append('room_token', window.ROOM_TOKEN);
        addCsrfToken(f);

        let requestTranscriptSub = createXhrRequest('POST', 'readtextTranscriptSub.php', {
            onSuccess: function(xhr) {
                decreasependingrequests();
                let T = xhr.responseText.split('\n');
                
                // Construire le contenu complet du notebook VO
                let newContent = '';
                for (let i = 0; i < T.length; i++) {
                    let line = T[i].trim();
                    if (line) {
                        let parts = line.split(';');
                        if (parts.length > 1 && parts[1]) {
                            if (newContent !== '') {
                                newContent += '<br/>';
                            }
                            newContent += parts[1];
                        }
                    }
                }
                
                
                // Ne mettre à jour que si le contenu a changé
                if (newContent !== lastNotebookVOContent && newContent !== '') {
                    lastNotebookVOContent = newContent;
                    
                    // Mettre à jour VOBanner et notebook avec le contenu complet
                    _("VOBanner").innerHTML = DOMPurify.sanitize(newContent);
                    _('notebook').innerHTML = DOMPurify.sanitize(newContent);
                } else if (newContent === '') {
                } else {
                }
                //console.log('Readtext get : '+xhr.responseText);
            },
            data: f,
            timeout: 10000,
            context: 'readTextSubTranscript'
        });
        requestTranscriptSub.send(f);

        // Old commented code - removed for clarity
    }
}

/**
 * @summary Verify if there is something new in the IMAGE queue and if so, load the image.
 */
function readImage() {
    if (_('classroomid').value != "") {
        increasependingrequests();
        let f = new FormData();
        f.append('random', currentrandom);
        f.append('ROOMID', _('classroomid').value);
        f.append('room_token', window.ROOM_TOKEN);
        addCsrfToken(f);

        let request = createXhrRequest('POST', 'readimage.php', {
            onSuccess: function(xhr) {
                decreasependingrequests();
                if (xhr.responseText != "") {
                    let Trep = xhr.responseText.split('|||');
                    currentrandom = Trep[0];
                }
            },
            data: f,
            timeout: 10000,
            context: 'readImage'
        });
        request.send(f);
    }
}
