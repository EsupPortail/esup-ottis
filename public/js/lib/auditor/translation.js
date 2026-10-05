import { _ } from '../dom/selector.js';
/**
 * Translation functions for auditor
 */

import { CompleteSpeech } from './speech.js';
import { Ecrire } from './display.js';
import { increasependingrequests, decreasependingrequests } from '../utils/request-counter.js';
import { addCsrfToken, createFetchRequest } from '../conference/network.js';
import { getBuiltinVoice, getBuiltinLanguage } from '../state/auditor.js';
import {
    getBasePath, setToSayKey, unshiftToTranslate, getToTranslate,
    setToTranslate, shiftToTranslate, getAllLines
} from '../state/shared.js';

/**
 * @summary Translate a given string from a language to another
 * @param {string} ch - The string to translate.
 * @param {string} l - Output language.
 * @param {string} linput - Input language.
 * @param {number} lineid - Unique identifier for the translation.
 */
function Translate(ch, l, linput, lineid) {
  // Ne pas traduire si classroomid est vide ou inconnu
  if (_('classroomid').value === '' || _('classroomid').value === 'UNKNOWN') {
    decreasependingrequests();
    CompleteTranslation();
    return;
  }

  increasependingrequests();

  const f = new FormData();

  f.append('texte', ch);
  f.append('lang', l);
  f.append('langinput', linput);
  f.append('ROOMID', _('classroomid').value);
  f.append('lineid', lineid);
  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/text/translate', {
    onSuccess(response) {
      const parsedResponse = JSON.parse(response.responseText);
      if ((!parsedResponse.error) && parsedResponse.data.translations) {
        const translatedText = parsedResponse.data.translations[0].text;
        setToSayKey(lineid, translatedText);
        // Réinsérer dans ToTranslate pour traitement dans l'ordre
        unshiftToTranslate([lineid, linput, ch, l, translatedText]);
      } else {
        console.error('translation.js: Translation ERROR. Response:', parsedResponse, 'Raw:', response.responseText);
        console.error('Text being translated:', ch, 'From:', linput, 'To:', l);
        alert(`Translation error for: "${ch ? ch.substr(0, 100) : 'null'}..."\nCheck console for details.`);
        // Réinsérer avec un message d'erreur
        unshiftToTranslate([lineid, linput, ch, l, '---- Translation ERROR ---- (see console)']);
      }
      decreasependingrequests();
      CompleteTranslation();
    },
    onError(response, context) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('translation.js: Translation request failed. Context:', context);
      }
      decreasependingrequests();
      CompleteTranslation();
    },
    data: f,
    timeout: 9999,
    context: 'Translate',
  });
  request.send();
}

/**
 * @summary Orchestrates the sequence of translations
 * @deprecated
 */
function CompleteTranslation() {
  // Verification precoce : ne pas traduire si classroomid est invalide
  if (_('classroomid').value === '' || _('classroomid').value === 'UNKNOWN') {
    setToTranslate([]);
    if (_('synthesison').checked) {
      CompleteSpeech();
    }
    return;
  }

  const currentTranslate = getToTranslate() || [];
  if (currentTranslate.length > 0) {
    const L = currentTranslate[0];
    shiftToTranslate();

    const id = L[0];
    const sourceLang = L[1];
    const sourceText = L[2];
    const targetLang = L[3];
    const targetText = L[4];

    let currentLang = '';
    const builtinVoice = getBuiltinVoice();
    const builtinLanguage = getBuiltinLanguage();
    // Use builtin_voice.lang first (for auditor output voice), fallback to builtin_language
    if (typeof builtinVoice !== 'undefined' && builtinVoice && builtinVoice.lang && typeof builtinVoice.lang === 'string') {
      currentLang = builtinVoice.lang.substr(0, 2);
    } else if (typeof builtinLanguage !== 'undefined' && builtinLanguage && typeof builtinLanguage === 'string') {
      currentLang = builtinLanguage.substr(0, 2);
    }

    // If listener's language matches source or target language -> direct display
    if (currentLang && sourceLang && typeof sourceLang === 'string' && currentLang === sourceLang.substr(0, 2)) {
      setToSayKey(id, sourceText);
      Ecrire(sourceText);
      CompleteTranslation();
    } else if (currentLang && targetLang && typeof targetLang === 'string' && currentLang === targetLang.substr(0, 2)) {
      setToSayKey(id, targetText);
      Ecrire(targetText);
      CompleteTranslation();
    } else {
      // Translation needed (target language is different)
      if (currentLang && currentLang !== '') {
        if (sourceLang && typeof sourceLang === 'string') {
          Translate(sourceText, currentLang, sourceLang.substr(0, 2), id);
        } else {
          Translate(sourceText, currentLang, '', id);
        }
      } else {
        // No target language selected, display source text
        Ecrire(sourceText);
      }
    }
  } else {
    if (_('synthesison').checked) {
      CompleteSpeech();
    }
  }
}

/**
 * @summary Reconstruit l'output avec la nouvelle langue sans re-traduire ce qui est déjà disponible
 */
function rebuildOutputForLanguage() {
  // Vider l'output
  const outputEl = document.getElementById('output');
  if (outputEl) {
    outputEl.innerHTML = '';
  }
  
  // Vider ToTranslate pour reconstruction
  setToTranslate([]);
  
  // Reconstruire ToTranslate depuis AllLines avec la nouvelle langue
  const allLines = getAllLines() || {};
  for (const lineId in allLines) {
    const line = allLines[lineId];
    unshiftToTranslate([lineId, line.sourceLang, line.sourceText, line.targetLang, line.targetText]);
  }
  
  // Laisser CompleteTranslation traiter toutes les lignes avec la nouvelle langue
  CompleteTranslation();
}

export { Translate, CompleteTranslation, rebuildOutputForLanguage };
