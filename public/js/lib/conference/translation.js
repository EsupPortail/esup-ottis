import { StudentToSay, StudentToTranslate2, Studentcompletespeechactive, Studentcurrentdiscours, Studentgotolive, builtin_language, builtin_voice, currentdiscours, lastPartialTranslation, lastSentenceTranslated, readytotalk, subtitle_language, setLastSentenceTranslated, LiveTranslationNum, LiveTranslationFreq, setLiveTranslationNum } from './state.js';

import { _ } from './dom-helpers.js';
import { addCsrfToken, createFetchRequest } from './network.js';
import { Ecrire, EcrireNB, EcrireNBonly, EcrireNBonlyVO, EcrirePartial, saveText, saveTextWithTranslation } from './notebook.js';
import { Parle } from './voices.js';
import { setNotReadyToTalk, setReadyToTalk, StartSpeechRecognition, StopSpeechRecognition } from './speech.js';
import { safeSetTimeout } from '../utils/cleanup.js';

// Local state variables for this module
let lastSentenceVO = '';
let totalTranslated = 0;

/** @module conference/translation */

export function StudentParleThen(txt, f) {
  // EcrireFeedback(txt);
  safeSetTimeout(f, 100 * txt.length);
}

export function StudentTranslate2(ch, l, linput, lineid) {
  lineid = 'DIRECT';

  const formData = new FormData();
  formData.append('texte', ch);
  formData.append('lang', l);
  formData.append('langinput', linput);
  formData.append('ROOMID', _('classroomid').value || '');
  formData.append('lineid', lineid);

  // Ajouter whichtranslator (apikey gere via config.php)
  const whichtranslator = _('whichtranslator').value;
  if (whichtranslator) formData.append('whichtranslator', whichtranslator);

  // Ajouter le token CSRF
  addCsrfToken(formData);

  console.log('[StudentTranslate2] URL:', window.BASE_PATH + '/text/translate');
  console.log('[StudentTranslate2] formData entries:', Array.from(formData.entries()));

  const request = createFetchRequest('POST', window.BASE_PATH + '/text/translate', {
    onSuccess(response) {
      StudentCompleteTranslation2();
    },
    onError(response, context) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('[StudentTranslate2] Translation request failed. Context:', context);
        console.error('[StudentTranslate2] Response:', response);
      }
    },
    data: formData,
    timeout: 10000,
    context: 'StudentTranslate2',
  });
  request.send();
}

export function StudentCompleteTranslation2() {
  if (StudentToTranslate2.length > 0) {
    const L = StudentToTranslate2[0];
    StudentToTranslate2.splice(0, 1);
    if (typeof subtitle_language === 'string' && L && L[1] && typeof L[1] === 'string'
            && subtitle_language.substr(0, 2).toLowerCase() != L[1].substr(0, 2)) {
      // Ecrire("Push : "+L[2]);
      StudentTranslate2(L[2], subtitle_language.substr(0, 2).toLowerCase(), L[1].substr(0, 2), L[0]);
    } else StudentCompleteTranslation2();
  } else safeSetTimeout(StudentCompleteTranslation2, 10);
}

export function StudentCompleteSpeech() {
  let nextdiscours = Studentcurrentdiscours;
  for (let i = 1; i < 1000; i++) {
    if (StudentToSay[`${Studentcurrentdiscours + i}`]) {
      nextdiscours = Studentcurrentdiscours + i;
      break;
    }
  }
  if (Studentgotolive) {
    // console.log('Restart Speech');
    // console.log('Discours : '+currentdiscours+'   '+nextdiscours)
    for (let i = 1000; i > 0; i--) {
      if (StudentToSay[`${Studentcurrentdiscours + i}`]) {
        nextdiscours = Studentcurrentdiscours + i;
        break;
      }
      // console.log('Discours next : '+currentdiscours+'   '+nextdiscours)
      Studentgotolive = false;
    }
  }
  // Ecrire(currentdiscours+'   '+nextdiscours);
  if (nextdiscours > Studentcurrentdiscours) {
    let L = StudentToSay[`${nextdiscours}`];
    L = L.replace('&nbsp;', ' ');
    StudentParleThen(L, StudentCompleteSpeech);
    Studentcurrentdiscours = nextdiscours;
  } else Studentcompletespeechactive = false;
}

export function Traduction(ch) {
  const recognitionon = _('recognitionon').checked;
  if (recognitionon) {
    EcrireNBonlyVO(ch);

    // Check if subtitle language is set and different from source
    if (typeof subtitle_language === 'string' && subtitle_language
            && typeof builtin_language === 'string' && builtin_language && builtin_language !== subtitle_language) {
      Translate(
        ch,
        subtitle_language.substr(0, 2),
        builtin_language.substr(0, 2),
        (translatedText) => {
          if (translatedText && translatedText !== '') {
            EcrireNBonly(translatedText);
            saveTextWithTranslation(ch, translatedText);
          } else {
            saveText(ch);
          }
        }
      );
    } else {
      // No subtitle language set or same as source, save only source text
      saveText(ch);
    }
    /*        let liveon = _('liveon').checked;
        if (liveon) {
            if (readytotalk) {
                setNotReadyToTalk();
                Ecrire('Input : ' + ch);
                if (builtin_voice.lang.substr(0, 2) != builtin_language.substr(0, 2)){
                    Translate(ch, builtin_voice.lang.substr(0, 2), builtin_language.substr(0, 2));
                }else {
                    Parle(ch);
                    Ecrire("Output : " + ch);
                }
            }
        } */
  }
}

export function TranslatorInfo() {
  let res = '';
  const whichtranslator = _('whichtranslator').value;
  if (whichtranslator != '') res += `&whichtranslator=${whichtranslator}`;
  return res;
}

export function Translate(ch, l, linput, callback) {
  TranslateThen(ch, l, linput, (translatedText) => {
    if (typeof translatedText === 'string') {
      // Only speak if voice is available
      if (typeof builtin_voice !== 'undefined' && builtin_voice) {
        Parle(translatedText);
      }
      setLastSentenceTranslated(translatedText);
      Ecrire(`${translatedText}`);
      if (callback) callback(translatedText);
    } else {
      console.error('Translate: invalid translatedText', translatedText);
      if (callback) callback('');
    }
  }, false);
}

export function TranslateThen(ch, l, linput, f, forcelibre = false) {
  const formData = new FormData();
  formData.append('texte', ch);
  formData.append('lang', l);
  formData.append('langinput', linput);
  formData.append('ROOMID', _('classroomid').value || '');
  formData.append('lineid', 'DIRECT');

  if (forcelibre) {
    formData.append('forcelibre', 'true');
  }

  // Ajouter whichtranslator (apikey gere via config.php)
  const whichtranslator = _('whichtranslator').value;
  if (whichtranslator) formData.append('whichtranslator', whichtranslator);

  const d_begin = Date.now();
  addCsrfToken(formData);

  const request = createFetchRequest('POST', window.BASE_PATH + '/text/translate', {
    onSuccess(response) {
      const d_end = Date.now();
      try {
        const parsedResponse = JSON.parse(response.responseText);

        if (parsedResponse && !parsedResponse.error && parsedResponse.data && parsedResponse.data.translations
                    && parsedResponse.data.translations[0] && parsedResponse.data.translations[0].text) {
          setLastSentenceTranslated(parsedResponse.data.translations[0].text);
          f(parsedResponse.data.translations[0].text);
        } else {
          console.error('Erreur de traduction. Réponse :', parsedResponse);
          f('---- Translation ERROR ----');
        }
      } catch (error) {
        console.error('Erreur de parsing JSON :', error, 'Réponse brute :', response.responseText);
        f('---- JSON Parse ERROR ----');
      }
    },
    onError(response, context) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('Erreur HTTP :', response.status, response.statusText);
        f(`---- HTTP ERROR ${response.status} ----`);
      }
    },
    data: formData,
    timeout: 10000,
    context: 'TranslateThen',
  });
  request.send();
}

export function EcrireTranslatePartial(ch, callback, forceTranslate = false) {
  let linput = (typeof builtin_language === 'string' && builtin_language) ? builtin_language.substr(0, 2).toLowerCase() : '';
  if (ch.indexOf(':|:') >= 0) { // If there are a language indication
    const Tch = ch.split(':|:');
    linput = (Tch[0]) ? Tch[0].substr(0, 2).toLowerCase() : '';
    ch = Tch[1];
  }
  const loutput = (typeof subtitle_language === 'string' && subtitle_language) ? subtitle_language.substr(0, 2).toLowerCase() : '';
  if (linput == loutput) {
    if (ch && ch.trim() !== '') {
      EcrirePartial(ch);
    }
    if (callback) callback(ch);
  } else {
    if (forceTranslate || LiveTranslationNum % LiveTranslationFreq == 0) {
      lastSentenceVO = ch;
      totalTranslated += ch.length;
      TranslateThen(ch, loutput, linput, (translatedText) => {
        setLastSentenceTranslated(translatedText);
        if (lastSentenceTranslated && lastSentenceTranslated.trim() !== '') {
          EcrirePartial(lastSentenceTranslated);
        }
        if (callback) callback(lastPartialTranslation);
      }, false);
    } else {
      if (lastSentenceTranslated && lastSentenceTranslated.trim() !== '') {
        EcrirePartial(lastSentenceTranslated);
      }
      if (callback) callback(lastPartialTranslation);
    }
    setLiveTranslationNum(LiveTranslationNum + 1);
  }
}
