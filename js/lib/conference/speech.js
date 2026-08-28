import { StudentToTranslate2, builtin_language, readytotalk, subtitle_language, LiveTranslationNum, setLiveTranslationNum } from './state.js';

import { _ } from './dom-helpers.js';
import { addCsrfToken, createXhrRequest } from './network.js';

import { ROOM_TOKEN } from './compat.js';
import { builtin_recognition, builtin_recognitionon, setBuiltinRecognitionon } from './config.js';
import { EcrireNBonly, EcrireNBonlyVO } from './notebook.js';
import { EcrireTranslatePartial } from './translation.js';

// Local variable for speech module
let lastSentenceVO = '';

/** @module conference/speech */

export function StudentreadText() {
  const synthesison = true;
  if (synthesison) {
    if (_('classroomid').value != '') {
      Studentcurrentorder_readtext++;
      const f = new FormData();
      f.append('lineid', Studentinputnumber); // Non utilisé dans readtextTranscriptSub.php, mais conservé pour compatibilité
      f.append('ORDER', Studentcurrentorder_readtext);
      f.append('ROOMID', _('classroomid').value);
      f.append('room_token', ROOM_TOKEN);
      addCsrfToken(f);

      const request = createXhrRequest('POST', 'readtext.php', {
        onSuccess(xhr) {
          if (!xhr.responseText.trim()) {
            return;
          }
          // Traiter la réponse
          const T = xhr.responseText.split('\n');
          let maxi = -1;
          for (let i = 0; i < T.length; i++) {
            if (T[i].trim() === '') continue;
            const parts = T[i].split(';');
            if (parts.length < 2) continue; // Ignorer les lignes mal formatées
            const readedorder = parts[0] * 1; // Premier élément = ORDER
            const lineData = parts.slice(1).join(';'); // Le reste = données de la ligne
            const L = lineData.split(';');
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
        onError(xhr, context) {

        },
        data: f,
        timeout: 10000,
        context: 'StudentreadText',
      });
      request.send(f);
    } else {

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
    return;
  }
  builtin_recognition.lang = builtin_language;
  builtin_recognition.onresult = function (event) {
    if (builtin_recognitionon && !builtin_externaltranscritionon && !builtin_microsofttranscritionon) {
      let partial = '';
      let isFinalResult = false;
      for (let i = event.resultIndex; i < event.results.length; i++) {
        if (event.results[i].isFinal) {
          partial = event.results[i][0].transcript; // +" > ";
          isFinalResult = true;
          FreeResources();
          setLiveTranslationNum(0);
          lastSentenceVO = '';
        } else {
          partial += ` ${event.results[i][0].transcript}`;
        }
      }
      if (partial.trim() != '') {
        if (_('recognitionon').checked) {
          if (isFinalResult) {
            EcrireTranslatePartial(partial, (translatedText) => {
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
      // builtin_recognition.stop();
    }
  };
  builtin_recognition.onend = function (event) {
    if (builtin_recognitionon) {
      StartSpeechRecognition(callback);
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
