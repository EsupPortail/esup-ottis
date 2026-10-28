// Depuis le store auditor
import { getBuiltinLanguage } from '../state/auditor.js';

// Depuis le store conference
import {
  getSubtitleLanguage,
  isBuiltinExternalTranscriptionOn,
  isBuiltinMicrosoftTranscriptionOn,
} from '../state/conference.js';

// Depuis translation.js
import { StudentToTranslate2, setLiveTranslationNum, setReadytotalk } from './state/translation.js';

// Depuis core.js
import {
  Studentinputnumber,
  setDelayStudentReadtext,
  setDelayReadtextQ,
  setStudentinputnumber,
} from './state/core.js';

import { _ } from '../dom/selector.js';
import { addCsrfToken, createFetchRequest } from './network.js';
import { safeSetTimeout } from '../utils/cleanup.js';
import { getBasePath } from '../state/shared.js';

import { getRoomToken } from './compat.js';
import { builtin_recognition, builtin_recognitionon, setBuiltinRecognitionon } from './config.js';
import { EcrireNBonly, EcrireNBonlyVO } from './notebook.js';
import { EcrireTranslatePartial, StudentCompleteTranslation2, Traduction } from './translation.js';
import * as logger from '../utils/logger.js';

// Compteurs locaux herites du code pre-modules (etaient des globales de script)
let Studentcurrentorder_readtext = 0;
const StudentAllPhrases = {};
const StudentToTranslate = [];

/** @module conference/speech */

export function StudentreadText() {
  const synthesison = true;
  if (synthesison) {
    const classroomIdElement = _('classroomid');
    if (classroomIdElement && classroomIdElement.value !== '') {
      Studentcurrentorder_readtext++;
      const f = new FormData();
      f.append('lineid', Studentinputnumber); // Non utilisé côté PHP, conservé pour compatibilité
      f.append('ORDER', Studentcurrentorder_readtext);
      f.append('ROOMID', classroomIdElement.value);
      f.append('room_token', getRoomToken());
      addCsrfToken(f);

      const request = createFetchRequest('POST', `${getBasePath()}/text/read`, {
        onSuccess(response) {
          if (!response.responseText.trim()) {
            return;
          }
          // Traiter la réponse
          const T = response.responseText.split('\n');
          let maxi = -1;
          for (let i = 0; i < T.length; i++) {
            const parts = T[i].split(';');
            if (T[i].trim() !== '' && parts.length >= 2) {
              // Ignorer les lignes mal formatées
              const lineData = parts.slice(1).join(';'); // Le reste = données de la ligne
              const L = lineData.split(';');
              if (L.length > 3) {
                // Afficher le texte dans notebookVO
                EcrireNBonlyVO(L[3]);
                if (1 * L[1] > maxi) maxi = 1 * L[1];
                if (StudentAllPhrases[1 * L[1]] === undefined) {
                  StudentAllPhrases[1 * L[1]] = true;
                  StudentToTranslate.push([L[1], L[2], L[3]]);
                  StudentToTranslate2.push([L[1], L[2], L[3]]);
                }
              }
            }
          }
          if (maxi > Studentinputnumber) setStudentinputnumber(maxi);
          StudentCompleteTranslation2();
        },
        onError(response, context) {
          // Ignorer les timeouts de polling (comportement normal)
          if (response.status !== 0 || response.statusText !== 'Timeout') {
            logger.error('StudentreadText request failed. Context:', context);
          }
        },
        data: f,
        timeout: 10000,
        context: 'StudentreadText',
      });
      request.send();
    }
  }
}

export function setReadyToTalk() {
  setReadytotalk(true);
  _('bready').style.backgroundColor = 'green';
  StartSpeechRecognition(Traduction);
}

export function setNotReadyToTalk() {
  StopSpeechRecognition();
  setReadytotalk(false);
  _('bready').style.backgroundColor = 'red';
}

export function StartSpeechRecognition(callback) {
  // Ne pas démarrer la reconnaissance si la langue n'est pas définie
  const builtinLang = getBuiltinLanguage();
  if (!builtinLang || typeof builtinLang !== 'string') {
    return;
  }
  try {
    builtin_recognition.lang = builtinLang;
  } catch (e) {
    return;
  }
  builtin_recognition.onresult = function (event) {
    if (
      builtin_recognitionon &&
      !isBuiltinExternalTranscriptionOn() &&
      !isBuiltinMicrosoftTranscriptionOn()
    ) {
      let partial = '';
      let isFinalResult = false;
      for (let i = event.resultIndex; i < event.results.length; i++) {
        if (event.results[i].isFinal) {
          partial = event.results[i][0].transcript; // +" > ";
          isFinalResult = true;
          FreeResources();
          setLiveTranslationNum(0);
        } else {
          partial += ` ${event.results[i][0].transcript}`;
        }
      }
      if (partial.trim() !== '') {
        if (_('recognitionon').checked) {
          if (isFinalResult) {
            // forceTranslate=true : la ligne finale doit etre traduite meme
            // en mode CONFERENCE_TRANSLATION_PARTIAL_FREQ=none
            EcrireTranslatePartial(partial, () => {
              // Écrire le texte original uniquement si pas de traduction nécessaire
              if (getBuiltinLanguage() === getSubtitleLanguage()) {
                EcrireNBonly(partial);
              }
              // Sinon: la traduction sera gérée par Traduction() via callback(partial)
            }, true);
            callback(partial);
          } else {
            EcrireTranslatePartial(partial);
          }
        }
      }
      // builtin_recognition.stop();
    }
  };
  builtin_recognition.onend = function () {
    if (builtin_recognitionon) {
      StartSpeechRecognition(callback);
    }
  };
  setBuiltinRecognitionon(true);
  try {
    builtin_recognition.start();
  } catch (e) {
    // Erreur au demarrage de la reconnaissance vocale, ignoree
  }
}

export function StopSpeechRecognition() {
  setBuiltinRecognitionon(false);
  builtin_recognition.stop();
}

export function FreeResources() {
  setDelayStudentReadtext(1000);
  setDelayReadtextQ(1000);
  safeSetTimeout(() => {
    setDelayStudentReadtext(500);
    setDelayReadtextQ(100);
  }, 2000);
}
