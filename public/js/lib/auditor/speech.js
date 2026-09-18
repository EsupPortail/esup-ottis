import { _ } from '../dom/selector.js';
/**
 * Speech synthesis functions for auditor
 */
import { safeSetTimeout } from '../utils/cleanup.js';
import { getBuiltinVoice } from '../state/auditor.js';
import {
    getDiscoursRate, getToSay, getCurrentDiscours,
    setCurrentDiscours
} from '../state/shared.js';

/**
 * @summary Speech synthesis of a given text
 * @param {string} txt - Text to synthesize
 */
function Parle(txt) {
  const builtinVoice = getBuiltinVoice();
  const discoursRate = getDiscoursRate();
  if (typeof builtinVoice !== 'undefined' && builtinVoice && !builtinVoice.textonly && builtinVoice.lang) {
    const discours = new SpeechSynthesisUtterance(txt);
    discours.voice = builtinVoice;
    discours.rate = discoursRate;
    discours.lang = builtinVoice.lang;
    speechSynthesis.speak(discours);
  }
}

/**
 * @summary Speech synthesis of a given text, then execute a callback function
 * @param {string} txt - Text to synthesize
 * @param {function} f - Callback function
 */
function ParleThen(txt, f) {
  _('titre').innerHTML = window.DOMPurify.sanitize(txt);
  _('fixed-title').innerHTML = window.DOMPurify.sanitize(txt);
  const builtinVoice = getBuiltinVoice();
  const discoursRate = getDiscoursRate();
  if (typeof builtinVoice !== 'undefined' && builtinVoice && !builtinVoice.textonly && builtinVoice.lang) {
    const discours = new SpeechSynthesisUtterance(txt);
    discours.voice = builtinVoice;
    discours.rate = discoursRate;
    discours.lang = builtinVoice.lang.replace('_', '-');
    discours.onend = f;
    discours.onerror = function () {
      f();
    };
    speechSynthesis.speak(discours);
  } else (typeof safeSetTimeout === 'function' ? safeSetTimeout : setTimeout)(f, 10000);
}

/**
 * @summary Orchestrates the sequence of speeches
 */
function CompleteSpeech() {
  
  try {
    let maxId = -1;
    // Last ID in ToSay
    const toSay = getToSay() || {};
    for (const key in toSay) {
      const id = parseInt(key);
      if (id > maxId) maxId = id;
    }


    // ONLY read the last one, if there is something new
    if (maxId > getCurrentDiscours()) {
      const L = toSay[`${maxId}`].replace('&nbsp;', ' ');
      ParleThen(L, CompleteSpeech);
      setCurrentDiscours(maxId);
    } else {
    }
  } catch (error) {
  }
}

export { Parle, ParleThen, CompleteSpeech };
