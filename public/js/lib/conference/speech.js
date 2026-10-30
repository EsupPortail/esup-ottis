// Depuis le store auditor
import { getBuiltinLanguage } from '../state/auditor.js';

// Depuis le store conference
import {
  getSubtitleLanguage,
  isBuiltinExternalTranscriptionOn,
  isBuiltinMicrosoftTranscriptionOn,
} from '../state/conference.js';

// Depuis translation.js
import { setLiveTranslationNum } from './state/translation.js';

// Depuis core.js
import { setDelayStudentReadtext, setDelayReadtextQ } from './state/core.js';

import { _ } from '../dom/selector.js';
import { safeSetTimeout } from '../utils/cleanup.js';

import {
  getBuiltinRecognition,
  getBuiltinRecognitionon,
  setBuiltinRecognitionon,
} from './config.js';
import { EcrireNBonly } from './notebook.js';
import { EcrireTranslatePartial, Traduction } from './translation.js';

/** @module conference/speech */

export function setReadyToTalk() {
  _('bready').style.backgroundColor = 'green';
  StartSpeechRecognition(Traduction);
}

export function setNotReadyToTalk() {
  StopSpeechRecognition();
  _('bready').style.backgroundColor = 'red';
}

export function StartSpeechRecognition(callback) {
  // Ne pas démarrer la reconnaissance si la langue n'est pas définie
  const builtinLang = getBuiltinLanguage();
  if (!builtinLang || typeof builtinLang !== 'string') {
    return;
  }
  try {
    getBuiltinRecognition().lang = builtinLang;
  } catch (e) {
    return;
  }
  getBuiltinRecognition().onresult = function (event) {
    if (
      getBuiltinRecognitionon() &&
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
            EcrireTranslatePartial(
              partial,
              () => {
                // Écrire le texte original uniquement si pas de traduction nécessaire
                if (getBuiltinLanguage() === getSubtitleLanguage()) {
                  EcrireNBonly(partial);
                }
                // Sinon: la traduction sera gérée par Traduction() via callback(partial)
              },
              true
            );
            callback(partial);
          } else {
            EcrireTranslatePartial(partial);
          }
        }
      }
      // builtin_recognition.stop();
    }
  };
  getBuiltinRecognition().onend = function () {
    if (getBuiltinRecognitionon()) {
      StartSpeechRecognition(callback);
    }
  };
  setBuiltinRecognitionon(true);
  try {
    getBuiltinRecognition().start();
  } catch (e) {
    // Erreur au demarrage de la reconnaissance vocale, ignoree
  }
}

export function StopSpeechRecognition() {
  setBuiltinRecognitionon(false);
  getBuiltinRecognition().stop();
}

export function FreeResources() {
  setDelayStudentReadtext(1000);
  setDelayReadtextQ(1000);
  safeSetTimeout(() => {
    setDelayStudentReadtext(500);
    setDelayReadtextQ(100);
  }, 2000);
}
