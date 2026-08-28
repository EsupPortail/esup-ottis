/**
 * Chat module index
 * Shared chat functionality for all OMIST interfaces
 * Replaces duplicated functions in auditor-func.js, conf-func.js, student-func.js, chat-func.js
 *
 * Dependencies: common-func.js (for ColorsForCR, getUserColor, rgb, escapeHtml, _, ScreenCR, EcrireCR, RefreshCR, ClearCR)
 */

// This file serves as documentation for the chat module
// Load the following files in order:
// 1. common-func.js (required first)
// 2. js/lib/chat/network.js
// 3. js/lib/chat/handlers.js
// 4. js/lib/chat/display.js
// 5. js/lib/chat/main.js

// From network.js:
// - saveTextQCommon(ch, senderName, roomId, language)
// - readTextQCommon(roomId, messageHandler)
// - TranslateQ(ch, targetLang, sourceLang, name, order)

// From handlers.js:
// - defaultMessageHandler(who, text, lang, order, isSpecial, specialType)
// - sendclick()
// Global variables: window.inputnumberQ, window.ColorsForCR

// From display.js:
// - ClearCR()
// - RefreshCR()
// - EcrireCR(who, ch, order)

// From main.js:
// - readTextQ(roomId)
// - saveTextQ(ch)
// - startChatPolling()
