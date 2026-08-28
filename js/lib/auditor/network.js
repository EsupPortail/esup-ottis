/**
 * Network functions for auditor
 */

import { decodeHtmlEntities } from './text-processing.js';
import { CompleteTranslation } from './translation.js';
import { increasependingrequests, decreasependingrequests } from './request-counter.js';
import { addCsrfToken, createXhrRequest } from '../conference/network.js';
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

    const request = createXhrRequest('POST', 'readtext.php', {
      onSuccess(xhr) {
        decreasependingrequests();
        const T = xhr.responseText.split('\n');
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
                    Ecrire(sourceText);
                    EcrireNB(sourceText);
                    window.ToSay[`${L[0]}`] = sourceText;
                  } else if (targetLang && currentLang === targetLang.substr(0, 2) && targetText) {
                    Ecrire(targetText);
                    EcrireNB(sourceText);
                    window.ToSay[`${L[0]}`] = targetText;
                  } else {
                    window.ToTranslate.push([L[0], sourceLang, sourceText, targetLang, targetText]);
                    EcrireNB(sourceText);
                  }
                }
              }
            }
          }
        }
        if (maxi > window.inputnumber) window.inputnumber = maxi;
        CompleteTranslation();
      },
      data: f,
      timeout: 10000,
      context: 'readText',
    });
    request.send(f);
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

    const request = createXhrRequest('POST', 'readtext.php', {
      onSuccess(xhr) {
        decreasependingrequests();
        const T = xhr.responseText.split('\n');
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
                    window.ToSay[`${L[0]}`] = sourceText;
                  } else if (targetLang && currentLang === targetLang.substr(0, 2) && targetText) {
                    EcrireNBSub(targetText);
                    window.ToSay[`${L[0]}`] = targetText;
                  } else {
                    window.ToTranslate.push([L[0], sourceLang, sourceText, targetLang, targetText]);
                  }
                }
              }
            }
          }
        }
        if (maxi > window.inputnumber) window.inputnumber = maxi;
        CompleteTranslation();
      },
      data: f,
      timeout: 10000,
      context: 'readTextSub',
    });
    request.send(f);
  }
}

/**
 * @summary Verify if there is something new pronounced by the teacher (ie, in the MAIN queue) for transcribed subtitles application.
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

    const requestTranscriptSub = createXhrRequest('POST', 'readtextTranscriptSub.php', {
      onSuccess(xhr) {
        decreasependingrequests();
        const T = xhr.responseText.split('\n');

        // Construire le contenu complet du notebook VO
        let newContent = '';
        for (let i = 0; i < T.length; i++) {
          const line = T[i].trim();
          if (line) {
            const parts = line.split(';');
            if (parts.length > 1 && parts[1]) {
              if (newContent !== '') {
                newContent += '<br/>';
              }
              newContent += parts[1];
            }
          }
        }

        // Ne mettre à jour que si le contenu a changé
        if (newContent !== window.lastNotebookVOContent && newContent !== '') {
          window.lastNotebookVOContent = newContent;

          // Mettre à jour VOBanner et notebook avec le contenu complet
          _('VOBanner').innerHTML = window.DOMPurify.sanitize(newContent);
          _('notebook').innerHTML = window.DOMPurify.sanitize(newContent);
        } else if (newContent === '') {
        } else {
        }
        // console.log('Readtext get : '+xhr.responseText);
      },
      data: f,
      timeout: 10000,
      context: 'readTextSubTranscript',
    });
    requestTranscriptSub.send(f);

    // Old commented code - removed for clarity
  }
}

/**
 * @summary Verify if there is something new in the IMAGE queue and if so, load the image.
 */
function readImage() {
  if (_('classroomid').value != '') {
    increasependingrequests();
    const f = new FormData();
    f.append('random', window.currentrandom);
    f.append('ROOMID', _('classroomid').value);
    f.append('room_token', window.ROOM_TOKEN);
    addCsrfToken(f);

    const request = createXhrRequest('POST', 'readimage.php', {
      onSuccess(xhr) {
        decreasependingrequests();
        if (xhr.responseText != '') {
          const Trep = xhr.responseText.split('|||');
          window.currentrandom = Trep[0];
        }
      },
      data: f,
      timeout: 10000,
      context: 'readImage',
    });
    request.send(f);
  }
}
export { readText, readTextSub, readTextSubTranscript, readImage };
