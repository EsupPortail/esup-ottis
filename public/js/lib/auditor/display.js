/**
 * Display functions for auditor
 */

import { _ } from '../dom/selector.js';

/**
 * @summary Function that update the "notebook" div.
 * @param {string} ch - The text that is adding to the notebook.
 */
function EcrireNB(ch) {
  if (_('notebook').innerHTML == '') {
    _('notebook').innerHTML = window.DOMPurify.sanitize(ch);
  } else _('notebook').innerHTML = window.DOMPurify.sanitize(`${_('notebook').innerHTML}<br/>${ch}`);

  // VOBanner
  _('VOBanner').innerHTML = window.DOMPurify.sanitize(ch);
}

/**
 * @summary Function that update the "notebook" div for the subtitle application.
 * @param {string} ch - The text that is adding to the notebook.
 */
function EcrireNBSub(ch) {
  if (_('notebook').innerHTML == '') {
    _('notebook').innerHTML = window.DOMPurify.sanitize(ch);
  } else _('notebook').innerHTML = window.DOMPurify.sanitize(`${_('notebook').innerHTML}<br/>${ch}`);
}

/**
 * @summary Function that update the "output" div.
 * @param {string} ch - The text that is adding to the notebook.
 */
function Ecrire(ch) {
  if (_('output').innerHTML == '') _('output').innerHTML = window.DOMPurify.sanitize(ch);
  else _('output').innerHTML = window.DOMPurify.sanitize(`${_('output').innerHTML}<br/>${ch}`);
  _('titre').innerHTML = window.DOMPurify.sanitize(ch);
  _('fixed-title').innerHTML = window.DOMPurify.sanitize(ch);
  // _('subtitles').innerHTML=ch;

  // bufferWC[placebufferWC] = ch;
  // placebufferWC = (placebufferWC + 1) % sizebufferWC;
  // buffer_has_changed = true;
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

export { EcrireNB, EcrireNBSub, Ecrire, hideBanner, showBanner };
