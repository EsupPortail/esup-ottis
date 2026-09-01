// Global variables for the student app

// Extract variables from window.pageData (injected from PHP)
const supportedlanguages = window.pageData ? window.pageData.supportedlanguages : [];
const classroomid = window.pageData ? window.pageData.classroomid : '';
const theme = window.pageData ? window.pageData.theme : 'flat';
const randomname = window.pageData ? window.pageData.randomname : '';

const visioactive = false;
let builtin_discoursrate = 1;
// builtin_voice and builtin_language are defined globally (without let/var/const)
// so they can be modified by common-func.js setLanguageOutput/Input functions
builtin_voice = null;
builtin_language = '';

const inputnumber = -1;

const ToTranslate = [];
const ToTranslate2 = [];
const completespeechactive = false;

const ToSay = [];
const currentdiscours = -1;
const gotolive = false;

const currentorder_readtext = 0;
const maxorder_readtext = -1;
const AllPhrases = [];

// Pour synchroniser le notebook avec notebookVO de conference
const lastNotebookVOContent = '';
window.lastNotebookVOContent = lastNotebookVOContent;

const currentrandom = 0;

let firsttime = true;
let firstclick = false;

// Expose all variables to window for ES modules compatibility
window.ToSay = ToSay;
window.ToTranslate = ToTranslate;
window.ToTranslate2 = ToTranslate2;
window.AllPhrases = AllPhrases;
window.builtin_voice = builtin_voice;
window.builtin_language = builtin_language;
window.currentorder_readtext = currentorder_readtext;
window.maxorder_readtext = maxorder_readtext;
window.inputnumber = inputnumber;
window.currentrandom = currentrandom;
window.completespeechactive = completespeechactive;
window.currentdiscours = currentdiscours;
window.gotolive = gotolive;
window.visioactive = visioactive;
window.builtin_discoursrate = builtin_discoursrate;

window.ColorsForCR = window.ColorsForCR || [];
ColorsForCR['----'] = [[255, 0, 0], [255, 255, 255], 5];
ColorsForCR.Intervenant = [[0, 0, 255], [255, 255, 255], 0];

document.addEventListener('submit', (event) => {
  event.preventDefault();
  // actual logic, e.g. validate the form
  // alert('Form submission cancelled.');
});

window.inputnumberQ = window.inputnumberQ || 0;

// Définir les fonctions EcrireNB pour le notebook
// Ces fonctions doivent être disponibles avant que auditor/network.js ne soit chargé
function EcrireNB(ch) {
  const sanitizeOptions = { ALLOWED_TAGS: ['br'] };
  _('notebook').innerHTML = window.DOMPurify.sanitize(`${_('notebook').innerHTML + ch}<br/>`, sanitizeOptions);
}

function EcrireNBSub(ch) {
  if (_('notebook').innerHTML == '') {
    _('notebook').innerHTML = window.DOMPurify.sanitize(ch);
  } else _('notebook').innerHTML = window.DOMPurify.sanitize(`${_('notebook').innerHTML}<br/>${ch}`);
}

function Ecrire(ch) {
  if (_('output').innerHTML == '') _('output').innerHTML = window.DOMPurify.sanitize(ch);
  else _('output').innerHTML = window.DOMPurify.sanitize(`${_('output').innerHTML}<br/>${ch}`);
  _('titre').innerHTML = window.DOMPurify.sanitize(ch);
  _('fixed-title').innerHTML = window.DOMPurify.sanitize(ch);
}

// Exposer au niveau window pour compatibilité
window.EcrireNB = EcrireNB;
window.EcrireNBSub = EcrireNBSub;
window.Ecrire = Ecrire;

let Tvoices;

const atab = 'tabs-0';
