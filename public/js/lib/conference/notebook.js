import { getBuiltinLanguage, getBuiltinVoice } from '../state/auditor.js';
import { getSubtitleLanguage } from '../state/conference.js';
import {
  MaxSavedLines,
  NBconnectedusers,
  Tconnectedusers,
  setNBconnectedusers,
  removeFromTconnectedusers,
  nbnewQ,
  setNbnewQ
} from './state/core.js';
import { getInputNumber, setInputNumber, getInputNumberQ, setInputNumberQ } from '../state/shared.js';
import { lastPartialTranslation, setLastPartialTranslation } from './state/translation.js';

import { getNotebookContent, EventNewQuestion } from './utils.js';
import { updateUsers } from './config-functions.js';
import { EcrireCR, AbortTimeout, getRoomToken } from './compat.js';

import { _ } from '../dom/selector.js';
import { addCsrfToken, createFetchRequest } from './network.js';
import { safeSetTimeout, registerRequest } from '../utils/cleanup.js';
import { getBasePath } from '../state/shared.js';

/** @module conference/notebook */

export function addSlideToNB(ch) {
  let tobesaidbetweenslides = '';
  if (_('tobesaid').value != '') tobesaidbetweenslides = `${_('tobesaid').value}.`;
  addToNB(tobesaidbetweenslides, 0);
  const Tch = ch.split('<br/>');
  for (let i = 0; i < Tch.length; i++) {
    addToNB(Tch[i], 0);
  }
}

export function addToNB(ch, before) {
  const speechlanguage = _('speechlanguage').value;
  let tobeinserted = '';
  if (speechlanguage != 'same') tobeinserted = `${speechlanguage}:|:`;
  let tobesaidbetweenslides = '';
  if (before == 1 && _('tobesaid').value != '') tobesaidbetweenslides = `${tobeinserted + _('tobesaid').value}. <br/>`;

  ch = tobesaidbetweenslides + tobeinserted + ch;
  // _('notebook').innerHTML+='<br/>'+ch;
  // EcrireTitle(ch);
  // saveTextNB(_('notebook').innerHTML);
  const Tch = ch.split('<br/>');
  saveTextTable(Tch, 0);
  // for (let c in Tch) saveText(Tch[c].replace(/<br\/>/gi,"").replace(/<br>/gi,""));
}

export function saveTextTable(T, i) {
  if (i < T.length) {
    saveText(T[i].replace(/<br\/>/gi, '').replace(/<br>/gi, ''));
    safeSetTimeout(() => {
      saveTextTable(T, i + 1);
    }, 100);
  }
}

export function EcrireNB(ch) {
  // if (_('notebook').innerHTML == "")
  //  _('notebook').innerHTML=ch;
  // else
  // Use DOMPurify with allowed br tags to preserve line breaks
  const sanitizeOptions = { ALLOWED_TAGS: ['br'] };
  _('notebook').innerHTML = window.DOMPurify.sanitize(`${_('notebook').innerHTML + ch}<br/>`, sanitizeOptions);
  // EcrireTitle(ch);
}

export function EcrireNBonly(ch) {
  // let currentContent = getNotebookContent('notebook');
  // logNotebookEvent('EcrireNBonly', 'notebook', ch, currentContent);
  // Use DOMPurify with allowed br tags to preserve line breaks
  const sanitizeOptions = { ALLOWED_TAGS: ['br'] };
  _('notebook').innerHTML = window.DOMPurify.sanitize(`${_('notebook').innerHTML + ch}<br/>`, sanitizeOptions);
}

export function EcrireNBonlyVO(ch) {
  // let currentContent = getNotebookContent('notebookVO');
  // logNotebookEvent('EcrireNBonlyVO', 'notebookVO', ch, currentContent);
  // Use DOMPurify with allowed br tags to preserve line breaks
  const sanitizeOptions = { ALLOWED_TAGS: ['br'] };
  _('notebookVO').innerHTML = window.DOMPurify.sanitize(`${_('notebookVO').innerHTML + ch}<br/>`, sanitizeOptions);
}

export function EcrirePartial(ch) {
  // Vérifier que ch est défini et non vide
  if (ch === undefined || ch === null || ch === 'undefined' || ch === '') {
    console.error('EcrirePartial: Texte non défini ou vide.');
    return;
  }

  // Mémoriser la dernière traduction affichée dans la zone partielle
  setLastPartialTranslation(ch);

  // Mettre à jour l'interface
  if (ch != 'undefined' && ch != undefined) {
    _('textBand').innerHTML = window.DOMPurify.sanitize(ch);
    _('textfixe').innerHTML = window.DOMPurify.sanitize(ch);
    // Synchroniser avec partialtitre si la case est cochée (utilisation de partialtitre)
    if (_('usetextband') && _('usetextband').checked) {
      _('partialtitre').innerHTML = window.DOMPurify.sanitize(ch);
    }
  }

  // Vérifier que l'élément classroomid existe et a une valeur non vide
  const classroomIdElement = _('classroomid');
  if (!classroomIdElement) {
    console.error('EcrirePartial: Éléments classroomid introuvable dans le DOM.');
    return;
  }

  const roomId = classroomIdElement.value;
  if (!roomId || roomId === '') {
    console.error('EcrirePartial: ROOMID est vide ou non défini.');
    return;
  }

  // Créer la requête POST avec FormData
  const url = `/extract/transcription-sub`;
  const formData = new FormData();
  formData.append('texte', ch);
  formData.append('ROOMID', roomId);

  const request = createFetchRequest('POST', url, {
    onSuccess(response) {
      if (response.responseText.trim() !== '') {
        try {
          const data = JSON.parse(response.responseText);
          if (data.status === 'error') {
            console.error('Erreur serveur :', data.message);
          }
        } catch (error) {
          console.error('Erreur de parsing JSON. Réponse brute :', response.responseText);
        }
      } else {
        console.error('Réponse vide du serveur.');
      }
    },
    onError(response, context) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('Erreur HTTP :', response.status, response.responseText);
      }
    },
    timeout: 10000,
    context: 'Transcription',
    data: formData,
  });
  request.send();
}

export function Ecrire(ch) {
  const outputElement = _('output');
  if (!outputElement) return;
  if (outputElement.innerHTML == '') outputElement.innerHTML = window.DOMPurify.sanitize(ch);
  else outputElement.innerHTML = window.DOMPurify.sanitize(`${ch}<br/>${outputElement.innerHTML}`);
}

export function saveText(ch) {
  // Vérifier que ch est défini et non vide
  if (ch === undefined || ch === null || ch === 'undefined' || ch === '') {
    console.error('saveText: Texte non défini ou vide.');
    return;
  }

  // Vérifier que classroomid existe et a une valeur
  const classroomIdElement = _('classroomid');
  if (!classroomIdElement || !classroomIdElement.value) {
    console.error('saveText: ROOMID non disponible.');
    return;
  }

  let inputlanguage = getBuiltinLanguage();
  if (ch.indexOf(':|:') >= 0) { // If there are a language indication
    const Tch = ch.split(':|:');
    inputlanguage = Tch[0];
    ch = Tch[1];
  }
  const f = new FormData();
  f.append('lineid', getInputNumber());
  setInputNumber(getInputNumber() + 1);
  f.append('text', ch);
  f.append('inputlanguage', inputlanguage);
  f.append('ROOMID', classroomIdElement.value);
  f.append('LastLines', MaxSavedLines);
  f.append('room_token', getRoomToken());
  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/text/save', {
    data: f,
    timeout: 10000,
    context: 'saveText',
  });
  request.send();

  //	request.onload = function(event) {};
}

export function saveTextWithTranslation(sourceText, translatedText) {
  const inputlanguage = getBuiltinLanguage() || '';
  const targetLanguage = getSubtitleLanguage() || '';

  // Ensure we have valid data before sending
  if (!inputlanguage || !targetLanguage || !sourceText) {
    console.error('saveTextWithTranslation: missing required parameters');
    return;
  }

  // Vérifier que classroomid existe et a une valeur
  const classroomIdElement = _('classroomid');
  if (!classroomIdElement || !classroomIdElement.value) {
    console.error('saveTextWithTranslation: ROOMID non disponible.');
    return;
  }

  const f = new FormData();
  f.append('lineid', getInputNumber());
  setInputNumber(getInputNumber() + 1);
  f.append('text', sourceText);
  f.append('inputlanguage', inputlanguage);
  f.append('targetlanguage', targetLanguage);
  f.append('translatedtext', translatedText);
  f.append('ROOMID', classroomIdElement.value);
  f.append('LastLines', MaxSavedLines);
  f.append('room_token', getRoomToken());
  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/text/save', {
    data: f,
    timeout: 10000,
    context: 'saveTextWithTranslation',
  });
  request.send();
}

export function saveTextNB(ch) {
  // Deprecated: batch-save of notebook is not used in current flows.
  console.warn('saveTextNB() is deprecated and has been disabled.');
}

export function readTextQ() {
  const classroomidEl = _('classroomid');
  if (classroomidEl && classroomidEl.value != '') {
    const f = new FormData();
    f.append('lineid', 0);
    f.append('ROOMID', classroomidEl.value);
    f.append('room_token', getRoomToken());
    addCsrfToken(f);

    const request = createFetchRequest('POST', getBasePath() + '/text/read-q', {
      onSuccess(response) {
        const Told = response.responseText.split('\n');
        const k = 0;
        const T = [];
        for (let i = 0; i < Told.length; i++) {
          if (Told[i].split(';').length > 2) T.push(Told[i]);
        }
        const nbnewQ = 0;
        const startIndex = getInputNumberQ();
        // Update inputnumberQ immediately to prevent overlapping requests from reprocessing messages
        if (T.length > startIndex) {
          setInputNumberQ(T.length);
        }
        for (let i = startIndex; i < T.length; i++) {
          const L = T[i].split(';');
          // Safety check - ensure L has enough elements
          if (L.length < 3) continue;

          if (L[2].indexOf('[newstudentarrived]') >= 0) {
            setNBconnectedusers(NBconnectedusers + 1);
            Tconnectedusers.push(L[0]);
            updateUsers();
          } else if (L[2].indexOf('[newstudentlived]') >= 0) {
            setNBconnectedusers(NBconnectedusers - 1);
            removeFromTconnectedusers(L[0]);
            if (typeof updateUsers === 'function') updateUsers();
          } else {
            setNbnewQ(nbnewQ + 1);
            // Use same logic as defaultMessageHandler (builtin_voice.lang first)
            const bv = getBuiltinVoice();
            const bl = getBuiltinLanguage();
            let currentLang = '';
            if (typeof bv !== 'undefined' && bv && bv.lang) {
              currentLang = bv.lang;
            } else if (typeof bl !== 'undefined' && bl) {
              currentLang = bl;
            }
            if (L.length >= 2 && currentLang && L[1] && L[1].substr && currentLang.substr(0, 2) !== L[1].substr(0, 2)) {
              TranslateQ(L[2], currentLang.substr(0, 2), L[1].substr(0, 2), L[0], i);
            } else if (L.length >= 1) {
              EcrireCR(L[0], L[2], i);
            }
          }
        }
        if (nbnewQ > 0) EventNewQuestion();
      },
      onError(response, context) {
        // Ignorer les timeouts de polling (comportement normal)
        if (response.status !== 0 || response.statusText !== 'Timeout') {
          console.error('readTextQ request failed. Context:', context);
        }
      },
      data: f,
      timeout: 10000,
      context: 'readTextQ',
    });
    request.send();
  }
}

export function TranslateQ(ch, l, linput, name, order) {
  const formData = new FormData();
  formData.append('texte', ch);
  formData.append('lang', l);
  formData.append('langinput', linput);
  const classroomidEl = _('classroomid');
  formData.append('ROOMID', (classroomidEl && classroomidEl.value) ? classroomidEl.value : '');
  formData.append('room_token', getRoomToken());

  const whichtranslatorEl = _('whichtranslator');
  const whichtranslator = (whichtranslatorEl && whichtranslatorEl.value) ? whichtranslatorEl.value : '';
  if (whichtranslator) formData.append('whichtranslator', whichtranslator);

  addCsrfToken(formData);

  const request = createFetchRequest('POST', getBasePath() + '/text/translate', {
    onSuccess(response) {
      try {
        const parsedResponse = JSON.parse(response.responseText);

        if (parsedResponse && !parsedResponse.error && parsedResponse.data && parsedResponse.data.translations) {
          const translatedText = parsedResponse.data.translations[0].text;
          if (true) {
            EcrireCR(name, translatedText, order);
          }
        } else {
          console.error('Erreur de traduction. Réponse :', parsedResponse);
          if (true) {
            EcrireCR(name, '---- Translation ERROR ----', order);
          }
        }
      } catch (error) {
        console.error('Erreur de parsing JSON :', error);
        if (true) {
          EcrireCR(name, '---- JSON Parse ERROR ----', order);
        }
      }
    },
    onError(response, context) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('Erreur HTTP :', response.status);
        if (true) {
          EcrireCR(name, `---- HTTP ERROR ${response.status} ----`, order);
        }
      }
    },
    data: formData,
    timeout: 10000,
    context: 'TranslateQ',
  });
  request.send();
}
