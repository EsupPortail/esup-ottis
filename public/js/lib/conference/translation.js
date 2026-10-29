import {
  lastPartialTranslation,
  lastSentenceTranslated,
  LiveTranslationNum,
  LiveTranslationFreq,
  setLastSentenceTranslated,
  setLiveTranslationNum,
} from './state/translation.js';
import { getBuiltinLanguage, getBuiltinVoice } from '../state/auditor.js';
import { getSubtitleLanguage, getWhichTranslator } from '../state/conference.js';

import { _ } from '../dom/selector.js';
import { addCsrfToken, createFetchRequest } from './network.js';
import { getBasePath } from '../state/shared.js';
import {
  Ecrire,
  EcrireNBonly,
  EcrireNBonlyVO,
  EcrirePartial,
  saveText,
  saveTextWithTranslation,
} from './notebook.js';
import { Parle } from './voices.js';
import * as logger from '../utils/logger.js';

/** @module conference/translation */

export function Traduction(ch) {
  const recognitionon = _('recognitionon').checked;
  if (recognitionon) {
    EcrireNBonlyVO(ch);

    // Check if subtitle language is set and different from source
    const subtitleLang = getSubtitleLanguage();
    const builtinLang = getBuiltinLanguage();
    if (
      typeof subtitleLang === 'string' &&
      subtitleLang &&
      typeof builtinLang === 'string' &&
      builtinLang &&
      builtinLang !== subtitleLang
    ) {
      Translate(ch, subtitleLang.substr(0, 2), builtinLang.substr(0, 2), (translatedText) => {
        if (translatedText && translatedText !== '') {
          EcrireNBonly(translatedText);
          saveTextWithTranslation(ch, translatedText);
        } else {
          saveText(ch);
        }
      });
    } else {
      // No subtitle language set or same as source, save only source text
      saveText(ch);
    }
    /*        let liveon = _('liveon').checked;
        if (liveon) {
            if (readytotalk) {
                setNotReadyToTalk();
                Ecrire('Input : ' + ch);
                const bv = getBuiltinVoice();
            const bl = getBuiltinLanguage();
            if (bv && bv.lang && bl && bv.lang.substr(0, 2) != bl.substr(0, 2)) {
                    Translate(ch, bv.lang.substr(0, 2), bl.substr(0, 2));
                } else {
                    Parle(ch);
                    Ecrire("Output : " + ch);
                }
            }
        } */
  }
}

export function TranslatorInfo() {
  let res = '';
  const whichtranslator = getWhichTranslator();
  if (whichtranslator !== '') res += `&whichtranslator=${whichtranslator}`;
  return res;
}

export function Translate(ch, l, linput, callback) {
  TranslateThen(
    ch,
    l,
    linput,
    (translatedText) => {
      if (typeof translatedText === 'string') {
        // Only speak if voice is available
        if (typeof getBuiltinVoice() !== 'undefined' && getBuiltinVoice()) {
          Parle(translatedText);
        }
        setLastSentenceTranslated(translatedText);
        Ecrire(`${translatedText}`);
        if (callback) callback(translatedText);
      } else {
        logger.error('Translate: invalid translatedText', translatedText);
        if (callback) callback('');
      }
    },
    false
  );
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

  // Ajouter whichtranslator (apikey gere via config.php ou variable globale)
  const whichtranslator = getWhichTranslator();
  if (whichtranslator) {
    formData.append('whichtranslator', whichtranslator);
  }

  addCsrfToken(formData);

  const request = createFetchRequest('POST', `${getBasePath()}/text/translate`, {
    onSuccess(response) {
      try {
        const parsedResponse = JSON.parse(response.responseText);

        if (
          parsedResponse &&
          !parsedResponse.error &&
          parsedResponse.data &&
          parsedResponse.data.translations &&
          parsedResponse.data.translations[0] &&
          parsedResponse.data.translations[0].text
        ) {
          setLastSentenceTranslated(parsedResponse.data.translations[0].text);
          f(parsedResponse.data.translations[0].text);
        } else {
          logger.error('Erreur de traduction. Réponse :', parsedResponse);
          f('---- Translation ERROR ----');
        }
      } catch (error) {
        logger.error('Erreur de parsing JSON :', error, 'Réponse brute :', response.responseText);
        f('---- JSON Parse ERROR ----');
      }
    },
    onError(response) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        logger.error('Erreur HTTP :', response.status, response.statusText);
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
  const builtinLang = getBuiltinLanguage();
  const subtitleLang = getSubtitleLanguage();
  let linput =
    typeof builtinLang === 'string' && builtinLang ? builtinLang.substr(0, 2).toLowerCase() : '';
  if (ch.indexOf(':|:') >= 0) {
    // If there are a language indication
    const Tch = ch.split(':|:');
    linput = Tch[0] ? Tch[0].substr(0, 2).toLowerCase() : '';
    [, ch] = Tch;
  }
  const loutput =
    typeof subtitleLang === 'string' && subtitleLang ? subtitleLang.substr(0, 2).toLowerCase() : '';
  if (linput === loutput) {
    if (ch && ch.trim() !== '') {
      EcrirePartial(ch, forceTranslate, loutput);
    }
    if (callback) callback(ch);
  } else if (LiveTranslationFreq === 0 && !forceTranslate) {
    // Aucune traduction intermediaire : ne rien afficher tant que la ligne
    // n'est pas finalisee - aucun texte non traduit ne doit apparaitre -
    // les lignes finales (forceTranslate) restent traduites.
  } else {
    if (forceTranslate || LiveTranslationNum % LiveTranslationFreq === 0) {
      TranslateThen(
        ch,
        loutput,
        linput,
        (translatedText) => {
          setLastSentenceTranslated(translatedText);
          if (lastSentenceTranslated && lastSentenceTranslated.trim() !== '') {
            EcrirePartial(lastSentenceTranslated, forceTranslate, loutput);
          }
          if (callback) callback(lastPartialTranslation);
        },
        false
      );
    } else {
      if (lastSentenceTranslated && lastSentenceTranslated.trim() !== '') {
        EcrirePartial(lastSentenceTranslated, false, loutput);
      }
      if (callback) callback(lastPartialTranslation);
    }
    setLiveTranslationNum(LiveTranslationNum + 1);
  }
}
