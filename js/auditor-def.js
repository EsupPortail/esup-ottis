// Global variables for the student app

// Extract variables from window.pageData (injected from PHP)
let supportedlanguages = window.pageData ? window.pageData.supportedlanguages : [];
let classroomid = window.pageData ? window.pageData.classroomid : '';
let theme = window.pageData ? window.pageData.theme : 'flat';
let randomname = window.pageData ? window.pageData.randomname : '';

let visioactive=false;
let builtin_discoursrate=1;
let builtin_voice = null;
let builtin_language = '';

let inputnumber=-1;

let ToTranslate=[];
let ToTranslate2=[];
let completespeechactive=false;

let ToSay=[];
let currentdiscours=-1;
let gotolive=false;

let currentorder_readtext=0;
let maxorder_readtext=-1;
let AllPhrases=[];

// Pour synchroniser le notebook avec notebookVO de conference
let lastNotebookVOContent = '';

let currentrandom = 0;

let firsttime = true;
let firstclick = false;

window.ColorsForCR = window.ColorsForCR || [];
ColorsForCR["----"]=[[255,0,0],[255,255,255],5];
ColorsForCR["Intervenant"]=[[0,0,255],[255,255,255],0];

document.addEventListener('submit', event => {
    event.preventDefault();
    // actual logic, e.g. validate the form
    //alert('Form submission cancelled.');
  });
  
window.inputnumberQ = window.inputnumberQ || 0;

// Définir les fonctions EcrireNB pour le notebook
// Ces fonctions doivent être disponibles avant que auditor/network.js ne soit chargé
function EcrireNB(ch) {
    let sanitizeOptions = {ALLOWED_TAGS: ['br']};
    _('notebook').innerHTML = DOMPurify.sanitize(_('notebook').innerHTML + ch + '<br/>', sanitizeOptions);
}

function EcrireNBSub(ch) {
    if (_('notebook').innerHTML == "") {
        _('notebook').innerHTML = DOMPurify.sanitize(ch);
    }
    else _('notebook').innerHTML = DOMPurify.sanitize(_('notebook').innerHTML + "<br/>" + ch);
}

function Ecrire(ch) {
    if (_('output').innerHTML == "")
        _('output').innerHTML = DOMPurify.sanitize(ch);
    else _('output').innerHTML = DOMPurify.sanitize(_('output').innerHTML + "<br/>", ch);
    _('titre').innerHTML = DOMPurify.sanitize(ch);
}

// Exposer au niveau window pour compatibilité
window.EcrireNB = EcrireNB;
window.EcrireNBSub = EcrireNBSub;
window.Ecrire = Ecrire;

let Tvoices;

let atab = 'tabs-0';


