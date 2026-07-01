/**
 * chat-common.js
 * Shared chat functionality for all OMIST interfaces
 * Replaces duplicated functions in auditeur-func.js, conf-func.js, student-func.js, chat-func.js
 * 
 * Dependencies: common-func.js (for ColorsForCR, getUserColor, rgb, escapeHtml, _, ScreenCR, EcrireCR, RefreshCR, ClearCR)
 */

// Ensure common-func.js is loaded first
if (typeof ColorsForCR === 'undefined') {
    console.error('chat-common.js requires common-func.js to be loaded first');
}

// Global variables for chat state
var inputnumberQ = 0;
var ColorsForCR = ColorsForCR || [];

// Initialize default colors if not already set
if (!ColorsForCR["----"]) {
    ColorsForCR["----"] = [[255,0,0],[255,255,255],5];
}
if (!ColorsForCR["Intervenant"]) {
    ColorsForCR["Intervenant"] = [[0,0,255],[255,255,255],0];
}

/**
 * @summary Send a new question to the classroom queue
 * 
 * @param {string} ch Text of the question
 * @param {string} senderName Name of the sender (e.g., "Intervenant", "User Red Alpaca")
 * @param {string} roomId The ROOMID
 * @param {string} language The language code
 * @returns {void}
 */
function saveTextQCommon(ch, senderName, roomId, language) {
    var request = new XMLHttpRequest();
    request.open("POST", "savetextQ.php");

    // Use increasependingrequests if available
    if (typeof increasependingrequests === 'function') {
        increasependingrequests();
    }
    
    var f = new FormData();
    f.append('lineid', senderName);
    f.append('text', ch);
    f.append('inputlanguage', language || '');
    f.append('ROOMID', roomId || '');
    
    // Ajouter le token pour l'auditeur (multi-onglets)
    if (typeof window.ROOM_TOKEN !== 'undefined') {
        f.append('room_token', window.ROOM_TOKEN);
    }
    
    request.send(f);
    
    // Use AbortTimeout if available
    if (typeof AbortTimeout === 'function') {
        AbortTimeout(request, "saveTextQ");
    }
    
    request.onload = function(event) {
        // Use decreasependingrequests if available
        if (typeof decreasependingrequests === 'function') {
            decreasependingrequests();
        }
        // Reload messages
        if (typeof readTextQ === 'function') {
            readTextQ();
        }
    };
}

/**
 * @summary Read messages from the classroom queue
 * 
 * @param {string} roomId The ROOMID
 * @param {function} messageHandler Function to handle each message: messageHandler(who, text, lang, order, isSpecial)
 * @returns {void}
 */
function readTextQCommon(roomId, messageHandler) {
    if (!roomId || roomId === "") return;
    
    var request = new XMLHttpRequest();
    request.open("POST", "readtextQ.php");

    // Use increasependingrequests if available
    if (typeof increasependingrequests === 'function') {
        increasependingrequests();
    }
    
    var f = new FormData();
    f.append('lineid', 0);
    f.append('ROOMID', roomId);
    
    // Ajouter le token pour l'auditeur (multi-onglets)
    if (typeof window.ROOM_TOKEN !== 'undefined') {
        f.append('room_token', window.ROOM_TOKEN);
    }
    
    request.send(f);
    
    // Use AbortTimeout if available
    if (typeof AbortTimeout === 'function') {
        AbortTimeout(request, "readTextQ");
    }
    
    request.onload = function(event) {
        // Use decreasependingrequests if available
        if (typeof decreasependingrequests === 'function') {
            decreasependingrequests();
        }
        
        var Told = request.responseText.split('\n');
        var k = 0;
        var T = [];
        
        // Filter out empty lines
        for (var i = 0; i < Told.length; i++) {
            if (Told[i].split(';').length > 2) {
                T.push(Told[i]);
            }
        }
        
        // Process new messages
        for (var i = inputnumberQ; i < T.length; i++) {
            var L = T[i].split(';');
            if (L.length >= 3) {
                var who = L[0];
                var lang = L[1];
                var text = L[2];
                var isSpecial = false;
                var specialType = '';
                
                // Check for special messages
                if (text.indexOf("[newstudentarrived]") >= 0) {
                    specialType = "newstudentarrived";
                    isSpecial = true;
                } else if (text.indexOf("[newstudentlived]") >= 0) {
                    specialType = "newstudentlived";
                    isSpecial = true;
                } else if (text.indexOf("[extratab") >= 0) {
                    specialType = "extratab";
                    isSpecial = true;
                }
                
                // Only process non-special messages through handler
                if (!isSpecial) {
                    // Skip ROBOT messages
                    if (who !== 'ROBOT' && text.indexOf("[newstudent") < 0) {
                        if (messageHandler) {
                            messageHandler(who, text, lang, i, isSpecial, specialType);
                        }
                    }
                } else {
                    // Handle special messages
                    if (specialType === "extratab" && who === 'Intervenant' && messageHandler) {
                        // Let handler deal with extratab
                        messageHandler(who, text, lang, i, true, "extratab");
                    }
                }
            }
        }
        
        // Update the counter
        inputnumberQ = T.length;
    };
}

/**
 * @summary Default message handler for chat messages
 * Translates if needed, then displays using EcrireCR
 * 
 * @param {string} who Sender name
 * @param {string} text Message text
 * @param {string} lang Message language
 * @param {number} order Message order
 * @param {boolean} isSpecial Is this a special message
 * @param {string} specialType Type of special message
 */
function defaultMessageHandler(who, text, lang, order, isSpecial, specialType) {
    if (isSpecial) {
        // Skip processing special messages in default handler
        return;
    }
    
    // Get current language
    var currentLang = '';
    if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
        currentLang = builtin_voice.lang;
    } else if (typeof builtin_language !== 'undefined' && builtin_language) {
        currentLang = builtin_language;
    }
    
    // Only translate if we have a language selected and it's different from the message language
    if (currentLang && currentLang.substr(0, 2) !== lang.substr(0, 2)) {
        if (typeof TranslateQ === 'function') {
            TranslateQ(text, currentLang.substr(0, 2), lang.substr(0, 2), who, order);
        }
    } else {
        if (typeof EcrireCR === 'function') {
            EcrireCR(who, text, order);
        }
    }
}

/**
 * @summary Read and display messages from the classroom queue
 * 
 * @param {string} roomId The ROOMID
 * @returns {void}
 */
function readTextQ(roomId) {
    readTextQCommon(roomId, defaultMessageHandler);
}

/**
 * @summary Save a new question to the classroom queue with auto-detection of sender
 * 
 * @param {string} ch Text of the question
 * @returns {void}
 */
function saveTextQ(ch) {
    // Determine sender name based on context
    var senderName = 'User Unknown';
    var roomId = '';
    var language = '';
    
    // Try to get from different possible contexts
    if (typeof _ !== 'undefined') {
        // auditeur.php, conference.php context
        if (_('StudentName') && _('StudentName').value) {
            senderName = 'User ' + _('StudentName').value;
        }
        if (_('classroomid') && _('classroomid').value) {
            roomId = _('classroomid').value;
        }
        if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
            language = builtin_voice.lang;
        } else if (typeof builtin_language !== 'undefined' && builtin_language) {
            language = builtin_language;
        }
    } else if (document.getElementById) {
        // chat.php, Student.php context
        if (document.getElementById('StudentName') && document.getElementById('StudentName').value) {
            senderName = 'Student ' + document.getElementById('StudentName').value;
        } else if (document.getElementById('StudentName')) {
            senderName = document.getElementById('StudentName').value;
        }
        if (document.getElementById('classroomid') && document.getElementById('classroomid').value) {
            roomId = document.getElementById('classroomid').value;
        }
        if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
            language = builtin_voice.lang;
        }
    }
    
    // For conference (teacher), use Intervenant
    if (roomId && (senderName === 'User Unknown' || senderName === 'Student ')) {
        // Check if this is a conference/teacher context
        if (typeof _ !== 'undefined' && _('classroomid')) {
            // This might be conference - use Intervenant
            senderName = 'Intervenant';
        }
    }
    
    saveTextQCommon(ch, senderName, roomId, language);
}

/**
 * @summary Translate a text and display it using EcrireCR
 * 
 * @param {string} ch Text to translate
 * @param {string} targetLang Target language
 * @param {string} sourceLang Source language
 * @param {string} name Sender name
 * @param {number} order Message order
 * @returns {void}
 */
function TranslateQ(ch, targetLang, sourceLang, name, order) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Translate.php", true);
    
    var roomid = (typeof _ !== 'undefined' && _('classroomid') ? _('classroomid').value : (document.getElementById ? document.getElementById('classroomid').value : ''));
    
    var f = new FormData();
    f.append('texte', ch);
    f.append('lang', targetLang);
    f.append('langinput', sourceLang);
    f.append('ROOMID', roomid);
    f.append('room_token', window.ROOM_TOKEN);
    
    // Use increasependingrequests if available
    if (typeof increasependingrequests === 'function') {
        increasependingrequests();
    }
    
    xhr.onreadystatechange = function() {
        if (typeof decreasependingrequests === 'function') {
            decreasependingrequests();
        }
        
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            try {
                var response = JSON.parse(this.responseText);
                if (!response["error"] && response["data"] && response["data"]["translations"] && response["data"]["translations"][0]) {
                    var translation = response["data"]["translations"][0]["text"];
                    if (translation) {
                        if (typeof EcrireCR === 'function') {
                            EcrireCR(name, translation, order);
                        }
                    } else {
                        // Fallback to original text
                        if (typeof EcrireCR === 'function') {
                            EcrireCR(name, "---- Translation ERROR ---- (see console)", order);
                        }
                        console.error('Translation ERROR - No translation text found');
                    }
                } else {
                    if (typeof EcrireCR === 'function') {
                        EcrireCR(name, "---- Translation ERROR ---- (see console)", order);
                    }
                    console.error('Translation ERROR - Response:', response, 'Raw:', this.responseText);
                }
            } catch (e) {
                if (typeof EcrireCR === 'function') {
                    EcrireCR(name, "---- Translation ERROR ---- (see console)", order);
                }
                console.error('Translation ERROR - Exception:', e, 'Response:', this.responseText);
            }
        }
    };
    
    xhr.send(f);
    
    // Use AbortTimeout if available
    if (typeof AbortTimeout === 'function') {
        AbortTimeout(xhr, "TranslateQ");
    }
}

/**
 * @summary Send click handler - called when user clicks send button
 * 
 * @returns {void}
 */
function sendclick() {
    var questionElement = null;
    if (typeof _ !== 'undefined') {
        questionElement = _('question');
    } else if (document.getElementById) {
        questionElement = document.getElementById('question');
    }
    
    if (questionElement) {
        var tosend = questionElement.value.replaceAll('\n','').trim();
        if (tosend !== "") {
            saveTextQ(tosend);
            questionElement.value = '';
        }
    }
}

/**
 * @summary Clear the classroom chat
 * 
 * @returns {void}
 */
function ClearCR() {
    if (typeof _ !== 'undefined') {
        _('classroom').innerHTML = '';
    } else if (document.getElementById) {
        document.getElementById('classroom').innerHTML = '';
    }
    if (typeof ScreenCR !== 'undefined') {
        ScreenCR = [];
    }
}

/**
 * @summary Refresh the classroom chat display
 * 
 * @returns {void}
 */
function RefreshCR() {
    var html = '<ul role="list" aria-live="polite" style="list-style: none; padding: 0; margin: 0;">';
    if (typeof ScreenCR !== 'undefined') {
        for (var i = 0; i < ScreenCR.length; i++) {
            if (ScreenCR[i] !== undefined) {
                html += ScreenCR[i];
            }
        }
    }
    html += '</ul>';
    
    if (typeof _ !== 'undefined') {
        _('classroom').innerHTML = html;
    } else if (document.getElementById) {
        document.getElementById('classroom').innerHTML = html;
    }
}

/**
 * @summary Write a message to the classroom chat
 * 
 * @param {string} who Sender name
 * @param {string} ch Message text
 * @param {number} order Message order
 * @returns {void}
 */
function EcrireCR(who, ch, order) {
    // Use the centralized version from common-func.js if available
    if (typeof window !== 'undefined' && window.ColorsForCR && typeof window.getUserColor === 'function') {
        if (!ColorsForCR[who]) {
            var colors = getUserColor(who);
            var bg = colors[0];
            var fg = colors[1];
            ColorsForCR[who] = [bg, fg, 20];
        }

        if (typeof ScreenCR === 'undefined') {
            ScreenCR = [];
        }
        
        var rgbFunc = typeof rgb === 'function' ? rgb : function(T) { return "rgb(" + T[0] + "," + T[1] + "," + T[2] + ")"; };
        var escapeHtmlFunc = typeof escapeHtml === 'function' ? escapeHtml : function(text) { 
            if (typeof text !== 'string') return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };
        
        ScreenCR[order] = '<li role="listitem" style="color:' + rgbFunc(ColorsForCR[who][1]) + '; background-color:' + rgbFunc(ColorsForCR[who][0]) + '; margin-left: ' + ColorsForCR[who][2] + 'px; margin: 0 0 2px 0; padding: 5px; border-radius: 5px; display: block;">' + escapeHtmlFunc(who + ' : ' + ch) + '</li>';
        RefreshCR();
    }
}

// Initialize ScreenCR if not defined
if (typeof ScreenCR === 'undefined') {
    var ScreenCR = [];
}

/**
 * @summary Start the chat polling
 * 
 * @returns {void}
 */
function startChatPolling() {
    // Start polling every 500ms
    setInterval(function() {
        var roomId = '';
        if (typeof _ !== 'undefined' && _('classroomid')) {
            roomId = _('classroomid').value;
        } else if (document.getElementById && document.getElementById('classroomid')) {
            roomId = document.getElementById('classroomid').value;
        }
        if (roomId) {
            readTextQ(roomId);
        }
    }, 500);
}
