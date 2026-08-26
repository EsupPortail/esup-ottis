/**
 * Voice management functions for auditor
 */

// Global variable to track notebook VO content (must be declared before use in callbacks)
if (typeof lastNotebookVOContent === 'undefined') {
    lastNotebookVOContent = '';
}

// Global variables for voice management
let AllOutputVoices = [];
let TLangInput = [];
let maxvoices = 0;

/**
 * @summary Fill the select VoiceOutput field
 * @param {string} selectname - Select element name (default: 'VoiceOutput')
 */
function PopulateVoicesOut(selectname = 'VoiceOutput') {
    let select = _(selectname);
    if (!select) return;
    
    AllOutputVoices = [];
    while (select.firstChild) {
        select.removeChild(select.firstChild);
    }
    let optgroup = document.createElement("optgroup");
    optgroup.label = "Voice and text translation";
    let voices = window.speechSynthesis.getVoices();
    // Voices in english first
    for (let i = 0; i < voices.length; i++) {
        if (voices[i].lang && typeof voices[i].lang === 'string' && voices[i].lang.substr(0, 2).toLowerCase() == 'en') { //(voices[i].localService) {
            let opt = document.createElement("option");
            opt.value = AllOutputVoices.length;
            if (voices[i].lang && typeof voices[i].lang === 'string' && voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
            voices[i].textonly = false;
            AllOutputVoices.push(voices[i]);
            opt.textContent = voices[i].lang + " " + voices[i].name + ((voices[i].localService) ? "" : " [external service]");
            optgroup.appendChild(opt);
        }
    }

    for (let i = 0; i < voices.length; i++) {
        if (voices[i].lang && typeof voices[i].lang === 'string' && voices[i].lang.substr(0, 2).toLowerCase() != 'en') { //(voices[i].localService) {
            let opt = document.createElement("option");
            opt.value = AllOutputVoices.length;
            if (voices[i].lang && typeof voices[i].lang === 'string' && voices[i].lang.substr(0, 2).toLowerCase() == navigator.language) opt.selected = true;
            voices[i].textonly = false;
            AllOutputVoices.push(voices[i]);
            opt.textContent = voices[i].lang + " " + voices[i].name + ((voices[i].localService) ? "" : " [external service]");
            optgroup.appendChild(opt);
        }
    }
    maxvoices = voices.length;
    select.appendChild(optgroup);
    let optgroup2 = document.createElement("optgroup");
    optgroup2.label = "Text translation only";
    if (typeof supportedlanguages !== 'undefined' && Array.isArray(supportedlanguages)) {
        for (let i = 0; i < supportedlanguages.length; i++) {
            if (supportedlanguages[i] && supportedlanguages[i].code) {
                let lang = supportedlanguages[i].code;
                let opt = document.createElement("option");
                opt.value = maxvoices + i;
                AllOutputVoices.push({
                    "lang": lang,
                    "textonly": true
                });

                opt.textContent = lang + " " + (supportedlanguages[i].name_fr || '') + " " + (supportedlanguages[i].name_vo || '');
                optgroup2.appendChild(opt);
            }
        }
    }
    select.appendChild(optgroup2);
}

/**
 * @summary Fill the select VoiceOutput field for the subtitle application
 * @param {string} selectname - Select element name (default: 'VoiceOutput')
 */
function PopulateVoicesOutSub(selectname = 'VoiceOutput') {
    let select = _(selectname);
    if (!select) return;
    
    AllOutputVoices = [];
    // while (select.firstChild) {
    //     select.removeChild(select.firstChild);
    // }
    
    let optgroup2 = document.createElement("optgroup");
    optgroup2.label = "Text translation only";
    if (typeof supportedlanguages !== 'undefined' && Array.isArray(supportedlanguages)) {
        for (let i = 0; i < supportedlanguages.length; i++) {
            if (supportedlanguages[i] && supportedlanguages[i].code) {
                let lang = supportedlanguages[i].code; //.substr(0,5);
                let opt = document.createElement("option");
                opt.value = maxvoices + i;
                AllOutputVoices.push({
                    "lang": lang,
                    "textonly": true
                });

                opt.textContent = lang + " " + (supportedlanguages[i].name_fr || '') + " " + (supportedlanguages[i].name_vo || '');
                optgroup2.appendChild(opt);
            }
        }
    }
    select.appendChild(optgroup2);
}

/**
 * @summary Fill the select VoiceInput field
 */
function PopulateVoicesIn() {
    if (TLangInput.length == 0) {
        let select = _('VoiceInput');
        let select2 = _('classroomlanguage');
        //let voices = window.speechSynthesis.getVoices();
        TLangInput = [];

        for (let i = 0; i < supportedlanguages.length; i++) {
            let lang = supportedlanguages[i].code; //.substr(0,5);
            if (TLangInput.indexOf(lang) < 0) {
                let opt = document.createElement("option");
                opt.value = TLangInput.length;
                TLangInput.push(lang);
                opt.textContent = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
                select.appendChild(opt);
                let opt2 = document.createElement("option");
                opt2.value = TLangInput.length;
                opt2.textContent = lang;
                select2.appendChild(opt2);
            }
        }
    }
}

/**
 * @summary Fill the select VoiceInput field for the subtitle application
 */
function PopulateVoicesInSub() {
    if (TLangInput.length == 0) {
        // let select = _('VoiceInput');
        let select = _('classroomlanguage');
        //let voices = window.speechSynthesis.getVoices();
        TLangInput = [];

        for (let i = 0; i < supportedlanguages.length; i++) {
            let lang = supportedlanguages[i].code; //.substr(0,5);
            if (TLangInput.indexOf(lang) < 0) {
                let opt = document.createElement("option");
                opt.value = TLangInput.length;
                TLangInput.push(lang);
                opt.textContent = lang + " " + supportedlanguages[i].name_fr + " " + supportedlanguages[i].name_vo;
                select.appendChild(opt);
                // let opt2 = document.createElement("option");
                // opt2.value = TLangInput.length;
                // opt2.innerHTML = lang;
                // select2.appendChild(opt2);
            }
        }
    }
}

/**
 * @summary InitVoices is executed when the HTML page is loaded.
 */
function InitVoices() {
    PopulateVoicesOut();
    PopulateVoicesOut('VoiceOutputchoose');
    // Ouvrir le pop-up de sélection de langue maintenant que les voix sont chargées
    if (typeof openLanguageDialog === 'function') {
        openLanguageDialog();
    }
    // Ne PAS définir la langue ici - cela sera fait quand l'utilisateur valide le popup
    // setLanguageOutputAndRetranslate(_("VoiceOutput").value);
}

/**
 * @summary Load the voices 
 */
function LoadVoices() {
    InitVoices();
    // Note: Lancer() is now called after language selection via startAuditorPolling()
    // to prevent polling before language is selected
}

/**
 * @summary Wait for the SpeechSynthesis Voices to be available 
 */
function loadVoicesWhenAvailable() {
    Tvoices = window.speechSynthesis.getVoices();

    if (Tvoices.length !== 0) {
        LoadVoices();
    } else {
        setTimeout(function() {
            loadVoicesWhenAvailable();
        }, 10);
    }
}
