/**
 * Chat message handlers
 * Dependencies: common-func.js (for ColorsForCR, getUserColor, rgb, escapeHtml, _)
 */

// Global variables for chat state
window.inputnumberQ = window.inputnumberQ || 0;
window.ColorsForCR = window.ColorsForCR || [];

// Initialize default colors if not already set
if (!ColorsForCR["----"]) {
    ColorsForCR["----"] = [[255,0,0],[255,255,255],5];
}
if (!ColorsForCR["Intervenant"]) {
    ColorsForCR["Intervenant"] = [[0,0,255],[255,255,255],0];
}

/**
 * @summary Default message handler for chat messages
 * Translates if needed, then displays using EcrireCR
 * Handles conference-specific features: EventNewQuestion, user connection tracking
 * 
 * @param {string} who - Sender name
 * @param {string} text - Message text
 * @param {string} lang - Message language
 * @param {number} order - Message order
 * @param {boolean} isSpecial - Is this a special message
 * @param {string} specialType - Type of special message
 */
export function defaultMessageHandler(who, text, lang, order, isSpecial, specialType) {
    // Handle special messages for conference
    if (isSpecial) {
        // Handle new student arrived
        if (specialType === "newstudentarrived" && typeof NBconnectedusers !== 'undefined') {
            NBconnectedusers++;
            if (typeof Tconnectedusers !== 'undefined') {
                Tconnectedusers.push(who);
            }
            if (typeof updateUsers === 'function') {
                updateUsers();
            }
        }
        // Handle new student lived (disconnected)
        else if (specialType === "newstudentlived" && typeof NBconnectedusers !== 'undefined') {
            NBconnectedusers--;
            if (typeof Tconnectedusers !== 'undefined') {
                Tconnectedusers = Tconnectedusers.filter(student => student !== who);
            }
            if (typeof updateUsers === 'function') {
                updateUsers();
            }
        }
        // For other special messages, skip normal processing
        return;
    }
    
    // Trigger alert for new messages from other users
    if (typeof triggerNewMessageAlert === 'function') {
        triggerNewMessageAlert(who);
    }
    
    // Track new questions for conference
    if (typeof nbnewQ !== 'undefined') {
        nbnewQ++;
    }
    
    // Get current language
    let currentLang = '';
    if (typeof builtin_voice !== 'undefined' && builtin_voice && builtin_voice.lang) {
        currentLang = builtin_voice.lang;
    } else if (typeof builtin_language !== 'undefined' && builtin_language) {
        currentLang = builtin_language;
    }
    
    // Only translate if we have a language selected and it's different from the message language
    if (currentLang && lang && typeof currentLang === 'string' && typeof lang === 'string' && 
        currentLang.substr(0, 2) !== lang.substr(0, 2)) {
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
 * @summary Send click handler - called when user clicks send button
 * 
 * @returns {void}
 */
export function sendclick() {
    // Prevent multiple rapid clicks
    if (typeof window.sendInProgress !== 'undefined' && window.sendInProgress) {
        return;
    }
    
    let questionElement = null;
    if (typeof _ !== 'undefined') {
        questionElement = window._('question');
    } else if (document.getElementById) {
        questionElement = window._('question');
    }
    
    if (questionElement) {
        let tosend = questionElement.value.replaceAll('\n','').trim();
        if (tosend !== "") {
            // Set flag to prevent multiple submissions
            window.sendInProgress = true;
            
            // Disable send button if it exists
            let sendButton = document.getElementById('questionsend');
            if (sendButton) {
                sendButton.disabled = true;
                sendButton.value = 'Sending...';
            }
            
            saveTextQ(tosend);
            questionElement.value = '';
            
            // Re-enable after a delay (in case of error, timeout will reset it)
            setTimeout(function() {
                window.sendInProgress = false;
                if (sendButton) {
                    sendButton.disabled = false;
                    sendButton.value = 'Send';
                }
            }, 2000); // 2 second cooldown
        }
    }
}
