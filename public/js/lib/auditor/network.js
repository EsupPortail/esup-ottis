import { _ } from '../dom/selector.js';
/**
 * Network functions for auditor
 */

import { decodeHtmlEntities } from './text-processing.js';
import { CompleteTranslation } from './translation.js';
import { increasependingrequests, decreasependingrequests } from '../utils/request-counter.js';
import { addCsrfToken } from '../network/utils.js';
import { createFetchRequest } from '../conference/network.js';
import { Ecrire, EcrireNB, EcrireNBSub } from './display.js';

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue).
 */
function readText() {
  if (_('classroomid').value != '') {
    increasependingrequests();
    window.currentorder_readtext++;
    const f = new FormData();
    f.append('lineid', window.inputnumber);
    f.append('ORDER', window.currentorder_readtext);
    f.append('ROOMID', _('classroomid').value);
    f.append('room_token', window.ROOM_TOKEN);
    addCsrfToken(f);

    const request = createFetchRequest('POST', window.BASE_PATH + '/text/read', {
      onSuccess(response) {
        decreasependingrequests();
        const T = response.responseText.split('\n');
        let maxi = -1;
        const readedorder = (`0${T[0]}`).split(';')[0] * 1;
        if (readedorder > window.maxorder_readtext) {
          window.maxorder_readtext = readedorder;
          for (let i = 0; i < T.length; i++) {
            const L = T[i].split(';');
            if (L.length > 3) {
              if (1 * L[1] > maxi) maxi = 1 * L[1];
              if (window.AllPhrases[1 * L[1]] == undefined) {
                if (_('classroomid').value !== '' && _('classroomid').value !== 'UNKNOWN') {
                  window.AllPhrases[1 * L[1]] = true;

                  const sourceLang = L[2];
                  const sourceText = decodeHtmlEntities(L[3]);
                  const targetLang = L[4];
                  const targetText = L.length >= 6 ? decodeHtmlEntities(L[5]) : null;

                  let currentLang = '';
                  if (typeof window.builtin_voice !== 'undefined' && window.builtin_voice && window.builtin_voice.lang && typeof window.builtin_voice.lang === 'string') {
                    currentLang = window.builtin_voice.lang.substr(0, 2);
                  } else if (typeof window.builtin_language !== 'undefined' && window.builtin_language && typeof window.builtin_language === 'string') {
                    currentLang = window.builtin_language.substr(0, 2);
                  }

                  // Toujours ajouter au notebook
                  EcrireNB(sourceText);

                  // Stocker les métadonnées pour reconstruction lors du changement de langue
                  if (typeof window.AllLines === 'undefined') window.AllLines = {};
                  window.AllLines[L[1]] = {sourceLang, sourceText, targetLang, targetText};

                  // Toujours ajouter à ToTranslate pour traitement dans l'ordre
                  // CompleteTranslation gérera l'affichage dans l'output et ne traduira que si nécessaire
                  window.ToTranslate.push([L[1], sourceLang, sourceText, targetLang, targetText]);
                }
              }
            }
          }
        }
        if (maxi > window.inputnumber) window.inputnumber = maxi;
        CompleteTranslation();
      },
      onError(response, context) {
        // Ignorer les timeouts de polling (comportement normal)
        if (response.status !== 0 || response.statusText !== 'Timeout') {
          console.error('readText request failed. Context:', context);
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
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue) for subtitles application.
 */
function readTextSub() {
  if (_('classroomid').value != '') {
    increasependingrequests();
    window.currentorder_readtext++;
    const f = new FormData();
    f.append('lineid', window.inputnumber);
    f.append('ORDER', window.currentorder_readtext);
    f.append('ROOMID', _('classroomid').value);
    f.append('room_token', window.ROOM_TOKEN);
    addCsrfToken(f);

    const request = createFetchRequest('POST', window.BASE_PATH + '/text/read', {
      onSuccess(response) {
        decreasependingrequests();
        const T = response.responseText.split('\n');
        let maxi = -1;
        const readedorder = (`0${T[0]}`).split(';')[0] * 1;
        if (readedorder > window.maxorder_readtext) {
          window.maxorder_readtext = readedorder;
          for (let i = 0; i < T.length; i++) {
            const L = T[i].split(';');
            if (L.length > 3) {
              if (1 * L[1] > maxi) maxi = 1 * L[1];
              if (window.AllPhrases[1 * L[1]] == undefined) {
                if (_('classroomid').value !== '' && _('classroomid').value !== 'UNKNOWN') {
                  window.AllPhrases[1 * L[1]] = true;

                  const sourceLang = L[2];
                  const sourceText = decodeHtmlEntities(L[3]);
                  const targetLang = L[4];
                  const targetText = L.length >= 6 ? decodeHtmlEntities(L[5]) : null;

                  let currentLang = '';
                  if (typeof window.builtin_voice !== 'undefined' && window.builtin_voice && window.builtin_voice.lang && typeof window.builtin_voice.lang === 'string') {
                    currentLang = window.builtin_voice.lang.substr(0, 2);
                  } else if (typeof window.builtin_language !== 'undefined' && window.builtin_language && typeof window.builtin_language === 'string') {
                    currentLang = window.builtin_language.substr(0, 2);
                  }

                  if (L.length >= 3 && L[2] && currentLang === L[2].substr(0, 2)) {
                    EcrireNBSub(sourceText);
                    window.ToSay[`${L[1]}`] = sourceText;
                  } else if (targetLang && currentLang === targetLang.substr(0, 2) && targetText) {
                    EcrireNBSub(targetText);
                    window.ToSay[`${L[1]}`] = targetText;
                  } else {
                    // Stocker les métadonnées pour reconstruction lors du changement de langue
                    if (typeof window.AllLines === 'undefined') window.AllLines = {};
                    window.AllLines[L[1]] = {sourceLang, sourceText, targetLang, targetText};
                    window.ToTranslate.push([L[1], sourceLang, sourceText, targetLang, targetText]);
                  }
                }
              }
            }
          }
        }
        if (maxi > window.inputnumber) window.inputnumber = maxi;
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
 * @summary Verify if there is something new in the MAIN queue for transcribed subtitles application.
 * Modifié pour gérer output selon la langue choisie
 */
function readTextSubTranscript() {
  if (_('classroomid').value != '') {
    increasependingrequests();
    window.currentorder_readtext++;
    const f = new FormData();
    f.append('lineid', window.inputnumber);
    f.append('ORDER', window.currentorder_readtext);
    f.append('ROOMID', _('classroomid').value);
    f.append('room_token', window.ROOM_TOKEN);
    addCsrfToken(f);

    const requestTranscriptSub = createFetchRequest('POST', window.BASE_PATH + '/text/read-transcript-sub', {
      onSuccess(response) {
        decreasependingrequests();
        const T = response.responseText.split('\n');

        // Construire le contenu complet du notebook Sub (sous-titres) avec tri par ordre
        let lines = [];
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
        if (notebookContent !== window.lastNotebookVOContent && notebookContent !== '') {
          window.lastNotebookVOContent = notebookContent;
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
  const roomToken = window.ROOM_TOKEN || '';
  const config = window.pageData?.currentconfig || '';
  const supportedLanguages = window.pageData?.supportedlanguages || [];
  const selectedLangSelect = _('classroomlanguage');

  if (!roomId || !config || config.length < 2 || !selectedLangSelect || !supportedLanguages.length) {
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

  const request = createFetchRequest('POST', window.BASE_PATH + '/text/read-vo', {
    onSuccess(response) {
      const T = response.responseText.split('\n');
      let lines = [];
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

  const request = createFetchRequest('POST', window.BASE_PATH + '/text/read-transcript-sub', {
    onSuccess(response) {
      const T = response.responseText.split('\n');
      let lines = [];
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
//   if (_('classroomid').value != '') {
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

export { readText, readTextSub, readTextSubTranscript /* readImage DISABLED */ };
