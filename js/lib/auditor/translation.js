/**
 * Translation functions for auditor
 */

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

    let f = new FormData();

    f.append('texte', ch);
    f.append('lang', l);
    f.append('langinput', linput);
    f.append('ROOMID', _('classroomid').value);
    f.append('lineid', lineid);
    addCsrfToken(f);

    let xhr = createXhrRequest('POST', 'Translate.php', {
        onSuccess: function(xhr) {
            let response = JSON.parse(xhr.responseText);
            if ((!response["error"]) && response["data"]["translations"]) {
                ToSay['' + lineid] = (response["data"]["translations"][0]["text"]);
                Ecrire(response["data"]["translations"][0]["text"]);
            } else {
                console.error('translation.js: Translation ERROR. Response:', response, 'Raw:', xhr.responseText);
                console.error('Text being translated:', ch, 'From:', linput, 'To:', l);
                alert('Translation error for: "' + (ch ? ch.substr(0, 100) : 'null') + '..."\nCheck console for details.');
                Ecrire("---- Translation ERROR ---- (see console)")
            }
        },
        data: f,
        timeout: 9999,
        context: 'Translate'
    });
    xhr.onloadend = function() {
        decreasependingrequests();
        CompleteTranslation();
    };
    xhr.ontimeout = function() {};
    xhr.send(f);
}

/**
 * @summary Orchestrates the sequence of translations
 * @deprecated
 */
function CompleteTranslation() {
    // Verification precoce : ne pas traduire si classroomid est invalide
    if (_('classroomid').value === '' || _('classroomid').value === 'UNKNOWN') {
        ToTranslate = [];
        if (_('synthesison').checked) {
            CompleteSpeech();
        }
        return;
    }

    if (ToTranslate.length > 0) {
        let L = ToTranslate[0];
        ToTranslate.splice(0, 1);
        
        let id = L[0];
        let sourceLang = L[1];
        let sourceText = L[2];
        let targetLang = L[3];
        let targetText = L[4];
        
        let currentLang = '';
        if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang && typeof builtin_voice.lang === 'string') {
            currentLang = builtin_voice.lang.substr(0, 2);
        } else if (typeof builtin_language !== 'undefined' && builtin_language && typeof builtin_language === 'string') {
            currentLang = builtin_language.substr(0, 2);
        }
        
        // If listener's language matches source or target language -> direct display
        if (currentLang && sourceLang && typeof sourceLang === 'string' && currentLang === sourceLang.substr(0, 2)) {
            ToSay['' + id] = sourceText;
            Ecrire(sourceText);
            CompleteTranslation();
        } else if (currentLang && targetLang && typeof targetLang === 'string' && currentLang === targetLang.substr(0, 2)) {
            ToSay['' + id] = targetText;
            Ecrire(targetText);
            CompleteTranslation();
        } else {
            // Translation needed (target language is different)
            if (sourceLang && typeof sourceLang === 'string') {
                Translate(sourceText, currentLang, sourceLang.substr(0, 2), id);
            } else {
                Translate(sourceText, currentLang, '', id);
            }
        }
    } else {
        if (_('synthesison').checked) {
            CompleteSpeech();
        }
    }
}
