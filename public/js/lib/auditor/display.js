/**
 * Display functions for auditor
 */

import { _ } from '../dom/selector.js';

/**
 * @summary Indique si la zone de transcription suit la fin du texte
 * (l'utilisateur n'a pas remonté dans l'historique).
 * @param {HTMLElement|null} el - Zone de transcription (#notebook / #output)
 * @returns {boolean} true si un auto-scroll en bas est souhaité
 */
function isFollowedByUser(el) {
  if (!el) return true;
  return el.scrollHeight - el.scrollTop - el.clientHeight < 80;
}

/**
 * @summary Function that update the "notebook" div.
 * @param {string} ch - The text that is adding to the notebook.
 */
function EcrireNB(ch) {
  const nb = _('notebook');
  const follow = isFollowedByUser(nb);
  if (nb.innerHTML === '') {
    nb.innerHTML = window.DOMPurify.sanitize(ch);
  } else {
    nb.innerHTML = window.DOMPurify.sanitize(`${nb.innerHTML}<br/>${ch}`);
  }
  if (follow) nb.scrollTop = nb.scrollHeight;

  // VOBanner
  _('VOBanner').innerHTML = window.DOMPurify.sanitize(ch);
}

/**
 * @summary Function that update the "notebook" div for the subtitle application.
 * @param {string} ch - The text that is adding to the notebook.
 */
function EcrireNBSub(ch) {
  const nb = _('notebook');
  const follow = isFollowedByUser(nb);
  if (nb.innerHTML === '') {
    nb.innerHTML = window.DOMPurify.sanitize(ch);
  } else {
    nb.innerHTML = window.DOMPurify.sanitize(`${nb.innerHTML}<br/>${ch}`);
  }
  if (follow) nb.scrollTop = nb.scrollHeight;
}

/**
 * @summary Function that update the subtitle divs with a partial line
 * (live subtitle while the speaker is still dictating).
 * @param {string} ch - The partial text to display.
 */
function EcrirePartialSub(ch) {
  if (ch === undefined || ch === null || ch === '') return;
  const titre = _('titre');
  const fixedTitle = _('fixed-title');
  if (titre) titre.innerHTML = window.DOMPurify.sanitize(ch);
  if (fixedTitle) fixedTitle.innerHTML = window.DOMPurify.sanitize(ch);
}

/**
 * @summary Function that update the "output" div.
 * @param {string} ch - The text that is adding to the notebook.
 */
function Ecrire(ch) {
  const out = _('output');
  const follow = isFollowedByUser(out);
  if (out.innerHTML === '') out.innerHTML = window.DOMPurify.sanitize(ch);
  else out.innerHTML = window.DOMPurify.sanitize(`${out.innerHTML}<br/>${ch}`);
  if (follow) out.scrollTop = out.scrollHeight;
  _('titre').innerHTML = window.DOMPurify.sanitize(ch);
  _('fixed-title').innerHTML = window.DOMPurify.sanitize(ch);
}

/**
 * @summary Hide a HTML Div by scaling it to 0x
 * @param {string} e - HTML div element ID
 */
function hideBanner(e) {
  const el = _(e);
  if (el) el.style.transform = 'scale(0)';
}

/**
 * @summary Show a HTML Div by scaling it to 1x
 * @param {string} e - HTML div element ID
 */
function showBanner(e) {
  const el = _(e);
  if (el) el.style.transform = 'scale(1)';
}

export { EcrireNB, EcrireNBSub, EcrirePartialSub, Ecrire, hideBanner, showBanner };
