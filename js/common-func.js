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

// Expose to window for access from ES modules
if (typeof window !== 'undefined') {
    window._ = _;
}

/**
 * @summary Add CSRF token to FormData object
 * 
 * @param {FormData} formData The FormData object to add CSRF token to
 * @returns {FormData} The FormData object with CSRF token added
 */
function addCsrfToken(formData) {
    let csrfToken = document.querySelector('input[name="csrf_token"]').value;
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }
    return formData;
}

/**
 * @summary Hide an HTML object by changing its "display" parameter.
 * 
 * @param {HTML div} e an HTML DOM object
 */

function hide(e) {
    let el = _(e);
    if (el) el.style.display = 'none';
}

/**
 * @summary Show an HTML object by changing its "display" parameter.
 * 
 * @param {HTML div} e an HTML DOM object
 */

function show(e) {
    let el = _(e);
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
let ColorsForCR = [];
ColorsForCR["----"] = [[255,0,0],[255,255,255],5];
ColorsForCR["Intervenant"] = [[0,0,255],[255,255,255],0];

// Global array to store chat messages
let ScreenCR = [];

// Expose to window for access from ES modules
if (typeof window !== 'undefined') {
    window.ColorsForCR = ColorsForCR;
    window.ScreenCR = ScreenCR;
    window.rgb = rgb;
    window.escapeHtml = escapeHtml;
    window.getUserColor = getUserColor;
}

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
 * Generate a random color for a user, avoiding intervenant's blue (0,0,255)
 * Persists the color in localStorage so the same user keeps the same color across sessions
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

    // Generate random RGB values in 55-255 range to avoid too dark colors
    let r, g, b;
    do {
        r = Math.floor(Math.random() * 201) + 55;
        g = Math.floor(Math.random() * 201) + 55;
        b = Math.floor(Math.random() * 201) + 55;
    } while (r === 0 && g === 0 && b === 255); // Never use intervenant's blue

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
    if (typeof TLangInput !== 'undefined' && TLangInput && TLangInput[i]) {
        builtin_language = TLangInput[i];
    } else {
        builtin_language = '';
    }
}

/**
 * @summary setter for the output language
 * 
 * @param {number} i Language index.
 */

function setLanguageOutput(i) {
    if (typeof AllOutputVoices !== 'undefined' && AllOutputVoices && AllOutputVoices[i]) {
        builtin_voice = AllOutputVoices[i];
        builtin_language = (builtin_voice && builtin_voice.lang) ? builtin_voice.lang : '';
    } else {
        builtin_voice = null;
        builtin_language = '';
    }
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
    if (typeof setInputnumberQ !== 'undefined') {
        setInputnumberQ(0);
    } else {
        inputnumberQ = 0;
    }
    if (typeof readTextQ !== 'undefined') {
        readTextQ();
    }

}

/**
 * @summary Refresh the classroom queue
 * 
 */

function RefreshCR() {
    let html = '<ul role="list" aria-live="polite" style="list-style: none; padding: 0; margin: 0;">';
    for (let i = 0; i < ScreenCR.length; i++) {
        if (ScreenCR[i] != undefined) {
            html += DOMPurify.sanitize(ScreenCR[i]);
        }
    }
    html += '</ul>';
    _('classroom').innerHTML = DOMPurify.sanitize(html);
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
        let colors = getUserColor(who);
        let bg = colors[0];
        let fg = colors[1];
        ColorsForCR[who] = [bg, fg, 20];
    }

    // Déterminer le style d'alignement : gauche pour Intervenant, droite pour les auditeurs
    let floatStyle = (who === 'Intervenant') ? 'left' : 'right';
    let marginLeft = (who === 'Intervenant') ? '0px' : '20px';

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
    let wc = _(wname);
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
    let [bgcolor, bordercolor, textcolor] = color.split('|');
    let e = _(boxname);
    if (!e) return;
    let opacityEl = _("selectopacity");
    let opacity = opacityEl ? opacityEl.value : '0.5';
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
    let e = _(boxname);
    if (!e) return;
    let color = e.style.backgroundColor;
    let [red, green, blue] = color.substring(color.indexOf('(') + 1, color.lastIndexOf(')')).split(/,\s*/);
    let bgcolor = [red, green, blue];
    e.style.backgroundColor = 'rgba(' + bgcolor + ',' + opacity + ')';
}

/**
 * @summary Detect the browser type
 * 
 * @returns The comprehensive name of the browser
 */

function whichBrowser() {
    let ua = navigator.userAgent;
    let browsers = ["Chrome","Firefox","Safari","Edge","Opera"];
    for (let i=0; i<=browsers.length; i++) {
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
    let link = document.createElement('a');
    mimeType = mimeType || 'text/plain';
    link.setAttribute('download', filename);
    link.setAttribute('href', 'data:' + mimeType  +  ';charset=utf-8,' + encodeURIComponent(elText));
    link.click();
}
/**
 * @summary Display an error message to the user
 * 
 * @param {string} message The error message to display
 * @param {string} [context] Optional context for logging
 */
function showError(message, context = null) {
    console.error(context ? `[${context}] ${message}` : message);
    // Could also display in a user-friendly way (toast, alert, etc.)
    // For now, just log to console
}

/**
 * @summary Handle XMLHttpRequest errors centrally
 * 
 * @param {XMLHttpRequest} xhr The XMLHttpRequest object
 * @param {string} context Context for error identification
 * @returns {boolean} True if error was handled, false otherwise
 */
function handleXhrError(xhr, context = 'unknown') {
    if (xhr.status === 0) {
        showError('Network error: Unable to connect to server', context);
    } else if (xhr.status >= 400 && xhr.status < 600) {
        showError(`HTTP error ${xhr.status}: ${xhr.statusText}`, context);
    } else {
        showError('An unexpected error occurred', context);
    }
    return true;
}

/**
 * @summary Create an XMLHttpRequest with centralized error handling
 * 
 * @param {string} method HTTP method (GET, POST, etc.)
 * @param {string} url The URL to request
 * @param {Object} options Options including onSuccess, onError, data, timeout, onProgress, onUploadProgress
 * @returns {XMLHttpRequest} The configured XMLHttpRequest object
 */
function createXhrRequest(method, url, options = {}) {
    const xhr = new XMLHttpRequest();
    const {
        onSuccess = null,
        onError = handleXhrError,
        data = null,
        timeout = 30000,
        contentType = 'application/x-www-form-urlencoded',
        context = url,
        onProgress = null,
        onUploadProgress = null
    } = options;

    xhr.open(method, url, true);
    xhr.timeout = timeout;
    
    if (method === 'POST' && data instanceof FormData) {
        // FormData sets its own Content-Type with boundary
    } else if (contentType) {
        xhr.setRequestHeader('Content-Type', contentType);
    }

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status >= 200 && xhr.status < 300) {
                if (onSuccess) onSuccess(xhr);
            } else {
                if (onError) onError(xhr, context);
            }
        }
    };

    xhr.ontimeout = function() {
        showError('Request timed out', context);
    };

    xhr.onerror = function() {
        showError('Network error', context);
    };

    // Add progress event listeners if provided
    if (onProgress && xhr.addEventListener) {
        xhr.addEventListener('progress', onProgress, false);
    }
    if (onUploadProgress && xhr.upload && xhr.upload.addEventListener) {
        xhr.upload.addEventListener('progress', onUploadProgress, false);
    }

    return xhr;
}

/**
 * @summary Simple fetch wrapper with centralized error handling
 * 
 * @param {string} url The URL to request
 * @param {Object} options Fetch options
 * @returns {Promise<Response>} The fetch response
 */
async function fetchWithErrorHandling(url, options = {}) {
    try {
        const response = await fetch(url, options);
        
        if (!response.ok) {
            const errorText = await response.text().catch(() => '');
            showError(`HTTP ${response.status}: ${errorText || response.statusText}`, url);
            throw new Error(`HTTP ${response.status}`);
        }
        
        return response;
    } catch (error) {
        if (error.name !== 'AbortError') {
            showError(error.message, url);
        }
        throw error;
    }
}
