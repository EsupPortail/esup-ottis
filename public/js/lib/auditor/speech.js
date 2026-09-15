import { _ } from '../dom/selector.js';
/**
 * Speech synthesis functions for auditor
 */

/**
 * @summary Speech synthesis of a given text
 * @param {string} txt - Text to synthesize
 */
function Parle(txt) {
  if (typeof builtin_voice !== 'undefined' && builtin_voice && !builtin_voice.textonly && builtin_voice.lang) {
    const discours = new SpeechSynthesisUtterance(txt);
    discours.voice = builtin_voice;
    discours.rate = builtin_discoursrate;
    discours.lang = builtin_voice.lang;
    window.speechSynthesis.speak(discours);
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
  if (typeof builtin_voice !== 'undefined' && builtin_voice && !builtin_voice.textonly && builtin_voice.lang) {
    const discours = new SpeechSynthesisUtterance(txt);
    discours.voice = builtin_voice;
    discours.rate = builtin_discoursrate;
    discours.lang = builtin_voice.lang.replace('_', '-');
    discours.onend = f;
    discours.onerror = function () {
      f();
    };
    window.speechSynthesis.speak(discours);
  } else (typeof window.safeSetTimeout === 'function' ? window.safeSetTimeout : setTimeout)(f, 10000);
}

/**
 * @summary Orchestrates the sequence of speeches
 */
function CompleteSpeech() {
  
  try {
    let maxId = -1;
    // Last ID in ToSay
    for (const key in ToSay) {
      const id = parseInt(key);
      if (id > maxId) maxId = id;
    }


    // ONLY read the last one, if there is something new
    if (maxId > currentdiscours) {
      const L = ToSay[`${maxId}`].replace('&nbsp;', ' ');
      ParleThen(L, CompleteSpeech);
      currentdiscours = maxId;
    } else {
    }
  } catch (error) {
  }
}

export { Parle, ParleThen, CompleteSpeech };
