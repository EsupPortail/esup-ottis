import { StudentToTranslate2, builtin_language, readytotalk, subtitle_language } from './state.js';

import { _ } from './dom-helpers.js';
import { addCsrfToken, createXhrRequest } from './network.js';

import { ROOM_TOKEN } from './compat.js';
import { builtin_recognition, builtin_recognitionon, setBuiltinRecognitionon } from './config.js';
import { LiveTranslationNum, setLiveTranslationNum } from './state.js';
import { EcrireNBonly, EcrireNBonlyVO } from './notebook.js';
import { EcrireTranslatePartial } from './translation.js';

// Local variable for speech module
let lastSentenceVO = '';

/** @module conference/speech */



export function StudentreadText() {

    let synthesison = true;
    if (synthesison) {
        if (_('classroomid').value != '') {
            Studentcurrentorder_readtext++;
            let f = new FormData();
            f.append('lineid', Studentinputnumber); // Non utilisé dans readtextTranscriptSub.php, mais conservé pour compatibilité
            f.append('ORDER', Studentcurrentorder_readtext);
            f.append('ROOMID', _('classroomid').value);
            f.append('room_token', ROOM_TOKEN);
            addCsrfToken(f);

            let request = createXhrRequest('POST', 'readtext.php', {
                onSuccess: function(xhr) {
                    if (!xhr.responseText.trim()) {
                        console.warn('Réponse vide ou erreur côté serveur.');
                        return;
                    }
                    // Traiter la réponse
                    let T = xhr.responseText.split('\n');
                    let maxi = -1;
                    for (let i = 0; i < T.length; i++) {
                        if (T[i].trim() === '') continue;
                        let parts = T[i].split(';');
                        if (parts.length < 2) continue; // Ignorer les lignes mal formatées
                        let readedorder = parts[0] * 1; // Premier élément = ORDER
                        let lineData = parts.slice(1).join(';'); // Le reste = données de la ligne
                        let L = lineData.split(';');
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
                },
                onError: function(xhr, context) {
                    console.error('Erreur HTTP:', xhr.status, xhr.statusText);
                },
                data: f,
                timeout: 10000,
                context: 'StudentreadText'
            });
            request.send(f);
        } else {
            console.warn('classroomid est vide !');
        }
    }
}

export function setReadyToTalk() {
    readytotalk = true;
    _('bready').style.backgroundColor = 'green';
    StartSpeechRecognition(Traduction);
}

export function setNotReadyToTalk() {
    StopSpeechRecognition();
    readytotalk = false;
    _('bready').style.backgroundColor = 'red';
}

export function ready(callback) {
    if (typeof callback === 'function') {
        callback();
    }
}

export function StartSpeechRecognition(callback) {
    // Ne pas démarrer la reconnaissance si la langue n'est pas définie
    if (!builtin_language || typeof builtin_language !== 'string') {
        console.warn('[SpeechRecognition] StartSpeechRecognition: builtin_language is not set. Language must be selected first. Current value:', builtin_language);
        return;
    }
    console.log('[SpeechRecognition] Starting with language:', builtin_language);
    builtin_recognition.lang = builtin_language;
    builtin_recognition.onresult = function(event) {
        if (builtin_recognitionon && !builtin_externaltranscritionon && !builtin_microsofttranscritionon) {
            //console.log(event.results);
            let partial = '';
            let isFinalResult = false;
            for (let i = event.resultIndex; i < event.results.length; i++) {
                if (event.results[i].isFinal) {
                    partial = event.results[i][0].transcript; //+" > ";
                    isFinalResult = true;
                    FreeResources();
                    setLiveTranslationNum(0);
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
        console.log('[SpeechRecognition] onend called, builtin_recognitionon:', builtin_recognitionon, 'builtin_language:', builtin_language);
        if (builtin_recognitionon) {
            console.log('[SpeechRecognition] Restarting recognition...');
            StartSpeechRecognition(callback);
        } else {
            console.log('[SpeechRecognition] Not restarting - builtin_recognitionon is false');
        }
    };
    setBuiltinRecognitionon(true);
    try {
        builtin_recognition.start();
    } catch {}
}

export function StopSpeechRecognition() {
    setBuiltinRecognitionon(false);
    builtin_recognition.stop();
}