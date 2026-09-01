/**
 * Translation functions for auditor
 */

import { CompleteSpeech } from './speech.js';
import { Ecrire } from './display.js';
import { increasependingrequests, decreasependingrequests } from './request-counter.js';

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

  const request = createFetchRequest('POST', 'Translate.php', {
    onSuccess(response) {
      const parsedResponse = JSON.parse(response.responseText);
      if ((!parsedResponse.error) && parsedResponse.data.translations) {
        const translatedText = parsedResponse.data.translations[0].text;
        window.ToSay[`${lineid}`] = translatedText;
        // Réinsérer dans ToTranslate pour traitement dans l'ordre
        window.ToTranslate.unshift([lineid, linput, ch, l, translatedText]);
      } else {
        console.error('translation.js: Translation ERROR. Response:', parsedResponse, 'Raw:', response.responseText);
        console.error('Text being translated:', ch, 'From:', linput, 'To:', l);
        alert(`Translation error for: "${ch ? ch.substr(0, 100) : 'null'}..."\nCheck console for details.`);
        // Réinsérer avec un message d'erreur
        window.ToTranslate.unshift([lineid, linput, ch, l, '---- Translation ERROR ---- (see console)']);
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
    window.ToTranslate = [];
    if (_('synthesison').checked) {
      CompleteSpeech();
    }
    return;
  }

  if (window.ToTranslate.length > 0) {
    const L = window.ToTranslate[0];
    window.ToTranslate.splice(0, 1);

    const id = L[0];
    const sourceLang = L[1];
    const sourceText = L[2];
    const targetLang = L[3];
    const targetText = L[4];

    let currentLang = '';
    if (typeof window.builtin_voice !== 'undefined' && window.builtin_voice && window.builtin_voice.lang && typeof window.builtin_voice.lang === 'string') {
      currentLang = window.builtin_voice.lang.substr(0, 2);
    } else if (typeof window.builtin_language !== 'undefined' && window.builtin_language && typeof window.builtin_language === 'string') {
      currentLang = window.builtin_language.substr(0, 2);
    }

    // If listener's language matches source or target language -> direct display
    if (currentLang && sourceLang && typeof sourceLang === 'string' && currentLang === sourceLang.substr(0, 2)) {
      window.ToSay[`${id}`] = sourceText;
      Ecrire(sourceText);
      CompleteTranslation();
    } else if (currentLang && targetLang && typeof targetLang === 'string' && currentLang === targetLang.substr(0, 2)) {
      window.ToSay[`${id}`] = targetText;
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
  } else if (_('synthesison').checked) {
    CompleteSpeech();
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
  window.ToTranslate = [];
  
  // Reconstruire ToTranslate depuis AllLines avec la nouvelle langue
  if (typeof window.AllLines !== 'undefined') {
    for (const lineId in window.AllLines) {
      const line = window.AllLines[lineId];
      window.ToTranslate.push([lineId, line.sourceLang, line.sourceText, line.targetLang, line.targetText]);
    }
  }
  
  // Laisser CompleteTranslation traiter toutes les lignes avec la nouvelle langue
  CompleteTranslation();
}

// Exposer sur window pour compatibilité avec setLanguageOutputAndRetranslate
if (typeof window !== 'undefined') {
  window.rebuildOutputForLanguage = rebuildOutputForLanguage;
}

export { Translate, CompleteTranslation, rebuildOutputForLanguage };
