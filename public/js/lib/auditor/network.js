import { _ } from '../dom/selector.js';
/**
 * Network functions for auditor
 */

import { decodeHtmlEntities } from './text-processing.js';
import { CompleteTranslation } from './translation.js';
import { increasependingrequests, decreasependingrequests } from '../utils/request-counter.js';
import { addCsrfToken } from '../network/utils.js';
import { createFetchRequest } from '../conference/network.js';
import { EcrireNB, EcrireNBSub, EcrirePartialSub } from './display.js';
import { setLiveStatus } from './live-status.js';
import { getBuiltinVoice, getBuiltinLanguage, isConferenceLive, setConferenceLive } from '../state/auditor.js';
import {
    getBasePath, getRoomToken, getMaxOrder, setMaxOrder,
    getAllPhrases, setPhrase, pushToTranslate, getCurrentOrder,
    incrementCurrentOrder, getInputNumber, setInputNumber,
    setLine,
    setToSayKey, getLastNotebookVOContent, setLastNotebookVOContent,
    getAllLines,
    get
} from '../state/shared.js';

/**
 * Sous-titres en direct : la conference publie le partiel en cours de dictee
 * (deja traduit dans la langue des sous-titres de la conference) dans
 * tmp/VerylastSub via /extract/transcription-sub. L'auditor affiche la
 * derniere ligne publiee sans attendre la fin de la ligne.
 */
let lastPartialSub = '';
let lastPartialSubTranslated = '';
let partialSubTranslationNum = 0;
let partialSubRequestInFlight = false;
const PARTIAL_SUB_TRANSLATION_FREQ = 5;

/**
 * @summary Langue choisie par l'auditor (pop-up Langue / panneau Langue).
 * @returns {string} Code sur 2 caracteres ('fr'), '' si inconnue.
 */
function getAuditorLanguage() {
  const builtinVoice = getBuiltinVoice();
  const builtinLanguage = getBuiltinLanguage();
  if (builtinVoice && builtinVoice.lang && typeof builtinVoice.lang === 'string') {
    return builtinVoice.lang.substr(0, 2).toLowerCase();
  }
  if (builtinLanguage && typeof builtinLanguage === 'string') {
    return builtinLanguage.substr(0, 2).toLowerCase();
  }
  return '';
}

/**
 * @summary Langue des sous-titres de la conference (index 1 de currentconfig).
 * Le partiel publie dans VerylastSub est dans cette langue.
 * @returns {string} Code sur 2 caracteres, '' si inconnue.
 */
function getConferenceSubtitleLanguage() {
  // Source de verite : la derniere ligne finalisee recue (tmp/Link_ contient
  // la langue cible = langue des sous-titres de la conference, donc celle
  // des partiels publies dans VerylastSub).
  const allLines = getAllLines() || {};
  const ids = Object.keys(allLines).sort((a, b) => (a * 1) - (b * 1));
  for (let i = ids.length - 1; i >= 0; i--) {
    const line = allLines[ids[i]];
    if (line && line.targetLang && typeof line.targetLang === 'string') {
      return line.targetLang.substr(0, 2).toLowerCase();
    }
  }

  // Repli (aucune ligne finalisee recue) : index 1 de currentconfig.
  // supportedlanguages est un objet PHP indexe par code langue (pas un
  // tableau) : numeroter par insertion (ordre du CSV preserve).
  const config = get('currentconfig') || '';
  if (typeof config !== 'string' || config.length < 2) return '';
  const supported = get('supportedlanguages') || [];
  const languages = Array.isArray(supported) ? supported : Object.values(supported);
  const index = config.charCodeAt(1) - 65;
  const lang = languages[index] && languages[index].code;
  return (typeof lang === 'string' && lang) ? lang.substr(0, 2).toLowerCase() : '';
}

/**
 * @summary Traduit un partiel via /text/translate (lineid DIRECT, comme en conference).
 */
function translatePartialSub(ch, targetLang, sourceLang, callback) {
  const f = new FormData();
  f.append('texte', ch);
  f.append('lang', targetLang);
  f.append('langinput', sourceLang);
  f.append('ROOMID', _('classroomid').value);
  f.append('lineid', 'DIRECT');
  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/text/translate', {
    onSuccess(response) {
      let translated = '';
      try {
        const parsedResponse = JSON.parse(response.responseText);
        if ((!parsedResponse.error) && parsedResponse.data && parsedResponse.data.translations
          && parsedResponse.data.translations[0] && parsedResponse.data.translations[0].text) {
          translated = parsedResponse.data.translations[0].text;
        }
      } catch (error) {
        console.error('translatePartialSub: JSON parse error:', error);
      }
      if (callback) callback(translated);
    },
    onError(response, context) {
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('translatePartialSub request failed. Context:', context);
      }
      if (callback) callback('');
    },
    data: f,
    timeout: 5000,
    context: 'translatePartialSub',
  });
  request.send();
}

/**
 * @summary Affiche le partiel recu, traduit si la langue de l'auditor
 * differe de la langue des sous-titres de la conference.
 * Throttle 1/PARTIAL_SUB_TRANSLATION_FREQ identique a conference
 * (EcrireTranslatePartial / LiveTranslationFreq).
 */
function displayPartialSub(partial) {
  const auditorLang = getAuditorLanguage();
  const subtitleLang = getConferenceSubtitleLanguage();

  // Langue inconnue ou identique : affichage direct du partiel publie
  if (!subtitleLang || !auditorLang || auditorLang === subtitleLang) {
    lastPartialSubTranslated = '';
    EcrirePartialSub(partial);
    return;
  }

  if (partialSubTranslationNum % PARTIAL_SUB_TRANSLATION_FREQ === 0) {
    translatePartialSub(partial, auditorLang, subtitleLang, (translated) => {
      if (translated) {
        lastPartialSubTranslated = translated;
        EcrirePartialSub(translated);
      }
    });
  } else if (lastPartialSubTranslated) {
    EcrirePartialSub(lastPartialSubTranslated);
  }
  partialSubTranslationNum++;
}

/**
 * @summary Poll la derniere ligne partielle publiee par la conference
 * et l'affiche immediatement dans la zone de sous-titres.
 */
function readTextPartial() {
  if (_('classroomid').value === '' || _('classroomid').value === 'UNKNOWN') return;
  if (partialSubRequestInFlight) return;
  partialSubRequestInFlight = true;

  const f = new FormData();
  f.append('ROOMID', _('classroomid').value);
  f.append('room_token', getRoomToken());
  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/text/read-last-sub', {
    onSuccess(response) {
      partialSubRequestInFlight = false;
      const partial = response.responseText.trim();
      if (partial === '' || partial === lastPartialSub) return;
      // Nouvelle phrase : le partiel ne prolonge pas le precedent,
      // reinitialiser le throttle pour traduire immediatement.
      if (!partial.startsWith(lastPartialSub)) {
        partialSubTranslationNum = 0;
      }
      lastPartialSub = partial;
      displayPartialSub(partial);
    },
    onError(response, context) {
      partialSubRequestInFlight = false;
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('readTextPartial request failed. Context:', context);
      }
    },
    data: f,
    timeout: 4000,
    context: 'readTextPartial',
  });
  request.send();
}

/**
 * @summary Verify if there is something new pronounced by the teacher (MAIN queue).
 */
function readText() {
  if (_('classroomid').value !== '') {
    increasependingrequests();
    incrementCurrentOrder();
    const f = new FormData();
    f.append('lineid', getInputNumber());
    f.append('ORDER', getCurrentOrder());
    f.append('ROOMID', _('classroomid').value);
    f.append('room_token', getRoomToken());
    addCsrfToken(f);

    const request = createFetchRequest('POST', getBasePath() + '/text/read', {
      onSuccess(response) {
        decreasependingrequests();
        // Badge "En direct" uniquement si le statut serveur dit "live"
        setLiveStatus(isConferenceLive());
        const T = response.responseText.split('\n');
        let maxi = -1;
        const readedorder = (`0${T[0]}`).split(';')[0] * 1;
        if (readedorder > getMaxOrder()) {
          setMaxOrder(readedorder);
          for (let i = 0; i < T.length; i++) {
            const L = T[i].split(';');
            if (L.length > 3) {
              if (1 * L[1] > maxi) maxi = 1 * L[1];
              if (getAllPhrases()[1 * L[1]] === undefined) {
                if (_('classroomid').value !== '' && _('classroomid').value !== 'UNKNOWN') {
                  setPhrase(1 * L[1], true);

                  const sourceLang = L[2];
                  const sourceText = decodeHtmlEntities(L[3]);
                  const targetLang = L[4];
                  const targetText = L.length >= 6 ? decodeHtmlEntities(L[5]) : null;

                  // Toujours ajouter au notebook
                  EcrireNB(sourceText);

                  // Stocker les métadonnées pour reconstruction lors du changement de langue
                  setLine(L[1], {sourceLang, sourceText, targetLang, targetText});

                  // Toujours ajouter à ToTranslate pour traitement dans l'ordre
                  // CompleteTranslation gérera l'affichage dans l'output
                  // et ne traduira que si nécessaire
                  pushToTranslate([L[1], sourceLang, sourceText, targetLang, targetText]);
                }
              }
            }
          }
        }
        if (maxi > getInputNumber()) setInputNumber(maxi);
        CompleteTranslation();
      },
      onError(response, context) {
        // Ignorer les timeouts de polling (comportement normal)
        if (response.status !== 0 || response.statusText !== 'Timeout') {
          console.error('readText request failed. Context:', context);
          setLiveStatus(false);
        }
        decreasependingrequests();
      },
      data: f,
      timeout: 10000,
      context: 'readText',
    });
    request.send();
  }
}

/**
 * @summary Verify if there is something new pronounced by the teacher (MAIN queue) for subtitles.
 */
function readTextSub() {
  if (_('classroomid').value !== '') {
    increasependingrequests();
    incrementCurrentOrder();
    const f = new FormData();
    f.append('lineid', getInputNumber());
    f.append('ORDER', getCurrentOrder());
    f.append('ROOMID', _('classroomid').value);
    f.append('room_token', getRoomToken());
    addCsrfToken(f);

    const request = createFetchRequest('POST', getBasePath() + '/text/read', {
      onSuccess(response) {
        decreasependingrequests();
        const T = response.responseText.split('\n');
        let maxi = -1;
        const readedorder = (`0${T[0]}`).split(';')[0] * 1;
        if (readedorder > getMaxOrder()) {
          setMaxOrder(readedorder);
          for (let i = 0; i < T.length; i++) {
            const L = T[i].split(';');
            if (L.length > 3) {
              if (1 * L[1] > maxi) maxi = 1 * L[1];
              if (getAllPhrases()[1 * L[1]] === undefined) {
                if (_('classroomid').value !== '' && _('classroomid').value !== 'UNKNOWN') {
                  setPhrase(1 * L[1], true);

                  const sourceLang = L[2];
                  const sourceText = decodeHtmlEntities(L[3]);
                  const targetLang = L[4];
                  const targetText = L.length >= 6 ? decodeHtmlEntities(L[5]) : null;

                  let currentLang = '';
                  const builtinVoice = getBuiltinVoice();
                  const builtinLanguage = getBuiltinLanguage();
                  // Use builtin_voice.lang first (auditor output voice),
                  // fallback to builtin_language
                  if (typeof builtinVoice !== 'undefined' && builtinVoice && builtinVoice.lang && typeof builtinVoice.lang === 'string') {
                    currentLang = builtinVoice.lang.substr(0, 2);
                  } else if (typeof builtinLanguage !== 'undefined' && builtinLanguage && typeof builtinLanguage === 'string') {
                    currentLang = builtinLanguage.substr(0, 2);
                  }

                  if (L.length >= 3 && L[2] && currentLang === L[2].substr(0, 2)) {
                    EcrireNBSub(sourceText);
                    setToSayKey(L[1], sourceText);
                  } else if (targetLang && currentLang === targetLang.substr(0, 2) && targetText) {
                    EcrireNBSub(targetText);
                    setToSayKey(L[1], targetText);
                  } else {
                    // Stocker les métadonnées pour reconstruction lors du changement de langue
                    setLine(L[1], {sourceLang, sourceText, targetLang, targetText});
                    pushToTranslate([L[1], sourceLang, sourceText, targetLang, targetText]);
                  }
                }
              }
            }
          }
        }
        if (maxi > getInputNumber()) setInputNumber(maxi);
        CompleteTranslation();
      },
      onError(response, context) {
        // Ignorer les timeouts de polling (comportement normal)
        if (response.status !== 0 || response.statusText !== 'Timeout') {
          console.error('readTextSub request failed. Context:', context);
        }
        decreasependingrequests();
      },
      data: f,
      timeout: 10000,
      context: 'readTextSub',
    });
    request.send();
  }
}

/**
 * @summary Verify if there is something new in the MAIN queue for transcribed subtitles.
 * Modifié pour gérer output selon la langue choisie
 */
function readTextSubTranscript() {
  if (_('classroomid').value !== '') {
    increasependingrequests();
    incrementCurrentOrder();
    const f = new FormData();
    f.append('lineid', getInputNumber());
    f.append('ORDER', getCurrentOrder());
    f.append('ROOMID', _('classroomid').value);
    f.append('room_token', getRoomToken());
    addCsrfToken(f);

    const requestTranscriptSub = createFetchRequest('POST', getBasePath() + '/text/read-transcript-sub', {
      onSuccess(response) {
        decreasependingrequests();
        const T = response.responseText.split('\n');

        // Construire le contenu complet du notebook Sub (sous-titres) avec tri par ordre
        const lines = [];
        for (let i = 0; i < T.length; i++) {
          const line = T[i].trim();
          if (line) {
            const parts = line.split(';');
            if (parts.length > 1) {
              lines.push({
                order: parseInt(parts[0], 10),
                content: parts.slice(1).join(';')
              });
            }
          }
        }
        // Trier par ordre numérique
        lines.sort((a, b) => a.order - b.order);
        // Concaténer dans l'ordre
        let notebookContent = '';
        for (let i = 0; i < lines.length; i++) {
          if (i > 0) {
            notebookContent += '<br/>';
          }
          notebookContent += lines[i].content;
        }

        // Mettre à jour VOBanner et notebook avec le contenu des sous-titres
        if (notebookContent !== getLastNotebookVOContent() && notebookContent !== '') {
          setLastNotebookVOContent(notebookContent);
          _('VOBanner').innerHTML = window.DOMPurify.sanitize(notebookContent);
          _('notebook').innerHTML = window.DOMPurify.sanitize(notebookContent);
        }

        // === NOUVEAU: Récupérer aussi le notebook VO et appliquer la logique pour output ===
        updateOutputBasedOnLanguage();
      },
      onError(response, context) {
        // Ignorer les timeouts de polling (comportement normal)
        if (response.status !== 0 || response.statusText !== 'Timeout') {
          console.error('readTextSubTranscript request failed. Context:', context);
        }
        decreasependingrequests();
      },
      data: f,
      timeout: 10000,
      context: 'readTextSubTranscript',
    });
    requestTranscriptSub.send();
  }
}

/**
 * Met à jour output en fonction de la langue choisie
 * - Si langue choisie == langue source de conference → output = notebookVO
 * - Si langue choisie == langue subtitle de conference → output = notebook
 * - Sinon → output = notebookVO (sera traduit par le système existant)
 */
function updateOutputBasedOnLanguage() {
  const roomId = _('classroomid')?.value;
  const roomToken = getRoomToken() || '';
  const config = get('currentconfig') || '';
  const supportedLanguages = get('supportedlanguages') || [];
  const selectedLangSelect = _('classroomlanguage');

  if (!roomId || !config || config.length < 2
    || !selectedLangSelect || !supportedLanguages.length) {
    return;
  }

  // Récupérer les codes de langue depuis les indexes
  const getLangCode = (index) => {
    if (supportedLanguages[index]) {
      return supportedLanguages[index].code;
    }
    return null;
  };

  const sourceLangIndex = config.charCodeAt(0) - 65;
  const subtitleLangIndex = config.charCodeAt(1) - 65;
  const selectedLangIndex = selectedLangSelect.selectedIndex;

  const sourceLangCode = getLangCode(sourceLangIndex);
  const subtitleLangCode = getLangCode(subtitleLangIndex);
  const selectedLangCode = getLangCode(selectedLangIndex);

  if (!sourceLangCode || !subtitleLangCode || !selectedLangCode) {
    return;
  }

  // Récupérer le contenu de Verylast (notebookVO)
  fetchVerylastContent(roomId, roomToken, (notebookVOContent) => {
    // Récupérer le contenu de VerylastSub (notebook)
    fetchVerylastSubContent(roomId, roomToken, (notebookSubContent) => {
      let outputContent = '';

      if (selectedLangCode === sourceLangCode) {
        // Langue choisie = langue source → afficher notebookVO
        outputContent = notebookVOContent;
      } else if (selectedLangCode === subtitleLangCode) {
        // Langue choisie = langue subtitle → afficher notebook
        outputContent = notebookSubContent;
      } else {
        // Langue choisie différente → afficher notebookVO (sera traduit par le système existant)
        outputContent = notebookVOContent;
      }

      // Remplir output si ce n'est pas déjà fait
      const outputEl = _('output');
      if (outputEl && outputContent && outputEl.innerHTML === '') {
        outputEl.innerHTML = window.DOMPurify.sanitize(outputContent);
      }
    });
  });
}

/**
 * Récupère le contenu de Verylast_{roomId}
 */
function fetchVerylastContent(roomId, roomToken, callback) {
  if (!roomId) {
    callback('');
    return;
  }

  const f = new FormData();
  f.append('lineid', 0);
  f.append('ORDER', 1);
  f.append('ROOMID', roomId);
  f.append('room_token', roomToken);
  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/text/read-vo', {
    onSuccess(response) {
      const T = response.responseText.split('\n');
      const lines = [];
      for (let i = 0; i < T.length; i++) {
        const line = T[i].trim();
        if (line) {
          const parts = line.split(';');
          if (parts.length > 1) {
            lines.push({
              order: parseInt(parts[0], 10),
              content: parts.slice(1).join(';')
            });
          }
        }
      }
      // Trier par ordre numérique
      lines.sort((a, b) => a.order - b.order);
      // Concaténer dans l'ordre
      let content = '';
      for (let i = 0; i < lines.length; i++) {
        if (i > 0) {
          content += '<br/>';
        }
        content += lines[i].content;
      }
      callback(content);
    },
    onError(response, context) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('fetchVerylastContent request failed. Context:', context);
      }
      callback('');
    },
    data: f,
    timeout: 5000,
    context: 'fetchVerylastContent'
  });
  request.send();
}

/**
 * Récupère le contenu de VerylastSub_{roomId}
 */
function fetchVerylastSubContent(roomId, roomToken, callback) {
  if (!roomId) {
    callback('');
    return;
  }

  const f = new FormData();
  f.append('lineid', 0);
  f.append('ORDER', 1);
  f.append('ROOMID', roomId);
  f.append('room_token', roomToken);
  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/text/read-transcript-sub', {
    onSuccess(response) {
      const T = response.responseText.split('\n');
      const lines = [];
      for (let i = 0; i < T.length; i++) {
        const line = T[i].trim();
        if (line) {
          const parts = line.split(';');
          if (parts.length > 1) {
            lines.push({
              order: parseInt(parts[0], 10),
              content: parts.slice(1).join(';')
            });
          }
        }
      }
      // Trier par ordre numérique
      lines.sort((a, b) => a.order - b.order);
      // Concaténer dans l'ordre
      let content = '';
      for (let i = 0; i < lines.length; i++) {
        if (i > 0) {
          content += '<br/>';
        }
        content += lines[i].content;
      }
      callback(content);
    },
    onError(response, context) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        console.error('fetchVerylastSubContent request failed. Context:', context);
      }
      callback('');
    },
    data: f,
    timeout: 5000,
    context: 'fetchVerylastSubContent'
  });
  request.send();
}

/**
 * @summary Verify if there is something new in the IMAGE queue and if so, load the image.
 * DISABLED - Obsolete image sharing functionality
 */
// function readImage() {
//   if (_('classroomid').value !== '') {
//     increasependingrequests();
//     const f = new FormData();
//     f.append('random', window.currentrandom);
//     f.append('ROOMID', _('classroomid').value);
//     f.append('room_token', window.ROOM_TOKEN);
//     addCsrfToken(f);
//
//     const request = createFetchRequest('POST', '/readimage', {
//       onSuccess(response) {
//         decreasependingrequests();
//         if (response.responseText != '') {
//           const Trep = response.responseText.split('|||');
//           window.currentrandom = Trep[0];
//         }
//       },
//       onError(response, context) {
//         // Ignorer les timeouts de polling (comportement normal)
//         if (response.status !== 0 || response.statusText !== 'Timeout') {
//           console.error('readImage request failed. Context:', context);
//         }
//         decreasependingrequests();
//       },
//       data: f,
//       timeout: 10000,
//       context: 'readImage',
//     });
//     request.send();
//   }
// }

/**
 * @summary Interroge le serveur pour savoir si la page conference est ouverte.
 * Met à jour le drapeau partagé et le badge "En direct" / "En erreur".
 * Sans ROOMID (page auditor ouverte hors lien conference) : erreur directe.
 */
function checkConferenceStatus() {
  const roomIdEl = _('classroomid');
  const roomId = roomIdEl ? roomIdEl.value : '';
  if (!roomId || roomId === 'UNKNOWN') {
    setConferenceLive(false);
    setLiveStatus(false);
    return;
  }

  const f = new FormData();
  f.append('ROOMID', roomId);
  f.append('room_token', getRoomToken());
  addCsrfToken(f);

  const request = createFetchRequest('POST', getBasePath() + '/auditor/status', {
    onSuccess(response) {
      let live = false;
      try {
        live = !!JSON.parse(response.responseText).live;
      } catch (err) {
        live = false;
      }
      setConferenceLive(live);
      setLiveStatus(live);
    },
    onError() {
      // Échec réseau : conserver l'état connu (pas de faux "En erreur" sur un timeout)
    },
    data: f,
    timeout: 10000,
    context: 'checkConferenceStatus',
  });
  request.send();
}

export { readText, readTextSub, readTextSubTranscript, readTextPartial, checkConferenceStatus /* readImage DISABLED */ };
