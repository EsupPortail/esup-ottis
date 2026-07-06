// Basic functions

/**
 * @summary shortcut for document.getElementById
 * 
 * @param {string} el identifiant of an HTML DOM element.
 * @returns the object whose name is el
 */

function _(el) {
    return document.getElementById(el);
}

/**
 * @summary Hide an HTML object by changing its "display" parameter.
 * 
 * @param {HTML div} e an HTML DOM object
 */

function hide(e) {
    var el = _(e);
    if (el) el.style.display = 'none';
}

/**
 * @summary Show an HTML object by changing its "display" parameter.
 * 
 * @param {HTML div} e an HTML DOM object
 */

function show(e) {
    var el = _(e);
    if (el) el.style.display = 'block';
}

/**
 * @summary Array to string RGB color
 * 
 * @param {array} T an array of 3 8bits integers
 * @returns a string description of a color
 */

function rgb(T) {
    return "rgb(" + T[0] + "," + T[1] + "," + T[2] + ")";
}

// Global color mapping for chat participants
var ColorsForCR = [];
ColorsForCR["----"] = [[255,0,0],[255,255,255],5];
ColorsForCR["Intervenant"] = [[0,0,255],[255,255,255],0];

/**
 * Escape HTML special characters to prevent XSS
 * 
 * @param {string} text The text to escape
 * @returns {string} The escaped text
 */
function escapeHtml(text) {
    if (typeof text !== 'string') return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Generate a deterministic color for a user based on their name
 * Prevents the same user from having different colors across sessions
 * Uses localStorage to persist colors between page reloads
 * Returns [backgroundColor, textColor] with proper contrast
 */
function getUserColor(username) {
    // Try to get from localStorage first
    const storageKey = 'chatUserColor_' + username;
    const stored = localStorage.getItem(storageKey);
    if (stored) {
        try {
            return JSON.parse(stored);
        } catch (e) {
            // If parsing fails, regenerate
        }
    }

    // Simple hash function
    let hash = 0;
    for (let i = 0; i < username.length; i++) {
        hash = username.charCodeAt(i) + ((hash << 5) - hash);
    }
    // Generate RGB values from hash
    let r = (hash & 0xFF0000) >> 16;
    let g = (hash & 0x00FF00) >> 8;
    let b = hash & 0x0000FF;

    // Ensure values are in 55-255 range (to avoid too dark colors)
    r = Math.max(55, Math.min(255, r));
    g = Math.max(55, Math.min(255, g));
    b = Math.max(55, Math.min(255, b));

    // Prevent using intervenant's color (RGB 0,0,255)
    if (r === 0 && g === 0 && b === 255) {
        r = (r + 50) % 256;
        g = (g + 100) % 256;
        b = (b + 150) % 256;
    }

    // Calculate brightness to determine text color (black or white)
    // Using relative luminance formula (simplified)
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    const textColor = brightness > 130 ? [0, 0, 0] : [255, 255, 255];

    const colors = [[r, g, b], textColor];
    
    // Store in localStorage for persistence
    try {
        localStorage.setItem(storageKey, JSON.stringify(colors));
    } catch (e) {
        // localStorage might not be available (private browsing, etc.)
    }

    return colors;
}

/**
 * @summary Abort a given timeout. Utility function
 * 
 * @param {object} r a timeout object.
 */

function AbortTimeout(r,s) {
    r.timeout = 10000;

    r.ontimeout = (e) => {
        console.log("Timeout problem in "+s+" !")
        r.abort();
    };

    //setTimeout(function() {
    //    console.log("Timeout problem in "+s+" !")
    //    r.abort();
    //}, 10000);
}





/**
 * @summary setter for the input language
 * 
 * @param {number} i Language index.
 */

function setLanguageInput(i) {
    builtin_language = TLangInput[i];
}

/**
 * @summary setter for the output language
 * 
 * @param {number} i Language index.
 */

function setLanguageOutput(i) {
    builtin_voice = AllOutputVoices[i];
    builtin_language = (builtin_voice && builtin_voice.lang) ? builtin_voice.lang : '';
}

/**
 * @summary Set the output language and retranslate the chat
 *
 * @param {number} i Language index.
 */
function setLanguageOutputAndRetranslate(i) {
    setLanguageOutput(i);
    // Reload and retranslate all messages with the new language
    if (typeof recharger === 'function') {
        recharger();
    }
}

/**
 * @summary Clear the classroom div
 * 
 */

function ClearCR() {
    _('classroom').innerHTML = '';
    ScreenCR = [];
}

/**
 * @summary Reload the classroom queue
 * 
 */

function recharger() {
    ClearCR();
    inputnumberQ = 0;
    readTextQ();

}

/**
 * @summary Refresh the classroom queue
 * 
 */

function RefreshCR() {
    var html = '<ul role="list" aria-live="polite" style="list-style: none; padding: 0; margin: 0;">';
    for (var i = 0; i < ScreenCR.length; i++) {
        if (ScreenCR[i] != undefined) {
            html += ScreenCR[i];
        }
    }
    html += '</ul>';
    _('classroom').innerHTML = html;
}

/*
 * @summary Write something to the classroom queue - NOW DEPRECATED, use chat-common.js version
 * 
 * @param {string} who surname of the person who writes.
 * @param {string} ch text that is to be written.
 * @param {number} order synchronisation parameter.
 *
function EcrireCR(who, ch, order) {
    if (!ColorsForCR[who]) {
        var colors = getUserColor(who);
        var bg = colors[0];
        var fg = colors[1];
        ColorsForCR[who] = [bg, fg, 20];
    }

    // Déterminer le style d'alignement : gauche pour Intervenant, droite pour les auditeurs
    var floatStyle = (who === 'Intervenant') ? 'left' : 'right';
    var marginLeft = (who === 'Intervenant') ? '0px' : '20px';

    ScreenCR[order] = '<li role="listitem" style="color:' + rgb(ColorsForCR[who][1]) + '; background-color:' + rgb(ColorsForCR[who][0]) + '; float: ' + floatStyle + '; margin-left: ' + marginLeft + '; margin-top: 0; margin-bottom: 2px; margin-right: 0; padding: 5px; border-radius: 5px; display: block; clear: both;">' + escapeHtml(who + ' : ' + ch) + '</li>';
    RefreshCR();
}
*/

/**
 * @summary Computes the number of questions in the classroom queue
 * 
 * @returns Tthe number of questions in the classroom queue.
 */

function TailleCR() {
    return _('classroom').innerHTML.split('<br/>').length;
}



/**
 * @summary Resize the main windows
 * 
 * @param {string} wname an HTML DOM identifier
 */

function resizeWindow(wname) {
    var wc = _(wname);
    if (!wc) return;
    wc.style.width = (window.innerWidth - 20) + "px";
    wc.style.height = (window.innerHeight - 48) + "px";
    wc.style.top = "48px";
    wc.style.left = "0px";
}


/**
 * @summary Change the color of a HTML div
 * 
 * @param {string} boxname The div identifier.
 * @param {string} color Three colors separated by |.
 */

function ChangeColor(boxname, color) {
    var [bgcolor, bordercolor, textcolor] = color.split('|');
    var e = _(boxname);
    if (!e) return;
    var opacityEl = _("selectopacity");
    var opacity = opacityEl ? opacityEl.value : '0.5';
    if (textcolor) e.style.color = 'rgb(' + textcolor + ')';
    else e.style.color = 'rgb(255,255,255)';
    e.style.backgroundColor = 'rgba(' + bgcolor + ',' + opacity + ')';
    e.style.boxShadow = '2px 2px 5px rgb(' + bgcolor + ')';
    e.style.textShadow = '2px 0 0 rgb(' + bordercolor + '), -2px 0 0 rgb(' + bordercolor + '), 0 2px 0 rgb(' + bordercolor + '), 0 -2px 0 rgb(' + bordercolor + '), 1px 1px rgb(' + bordercolor + '), -1px -1px 0 rgb(' + bordercolor + '), 1px -1px 0 rgb(' + bordercolor + '), -1px 1px 0 rgb(' + bordercolor + ')';
}

/**
 * @summary Change the opacity of a HTML div
 * 
 * @param {string} boxname The div identifier.
 * @param {string} opacity a [0,1] real number.
 */

function ChangeOpacity(boxname, opacity) {
    var e = _(boxname);
    if (!e) return;
    var color = e.style.backgroundColor;
    var [red, green, blue] = color.substring(color.indexOf('(') + 1, color.lastIndexOf(')')).split(/,\s*/);
    var bgcolor = [red, green, blue];
    e.style.backgroundColor = 'rgba(' + bgcolor + ',' + opacity + ')';
}

/**
 * @summary Detect the browser type
 * 
 * @returns The comprehensive name of the browser
 */

function whichBrowser() {
    var ua = navigator.userAgent;
    var browsers = ["Chrome","Firefox","Safari","Edge","Opera"];
    for(var i=0; i<=browsers.length; i++) {
        if (ua.indexOf(browsers[i]) > 0) return browsers[i];
    }
    return "Unknown";
}

/**
 * @summary The function that is executed for downloading a file with the animation editor.
 * 
 * @param {string} filename a text value contained the file name.
 * @param {string} elText a text value contained the text to download.
 * @param {string} mimeType a text value contained the MIME type.
 */

function DownloadSpeechLesson(filename, elText, mimeType) {
    elText = elText.replaceAll("<br>", "\n");
    var link = document.createElement('a');
    mimeType = mimeType || 'text/plain';
    link.setAttribute('download', filename);
    link.setAttribute('href', 'data:' + mimeType  +  ';charset=utf-8,' + encodeURIComponent(elText));
    link.click();
}