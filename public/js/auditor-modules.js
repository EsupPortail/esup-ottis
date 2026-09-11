/**
 * Auditor Modules Loader
 * This file replaces auditor-func.js and loads all auditor modules
 *
 * To use: Include this file instead of auditor-func.js
 * <script src="js/auditor-modules.js"></script>
 */

// Load all auditor modules in the correct order

// 1. Text processing utilities (no dependencies)
//= require ./lib/auditor/text-processing.js

// 2. Voice management (depends on supportedlanguages global)
//= require ./lib/auditor/voices.js

// 3. Speech synthesis (depends on builtin_voice, builtin_discoursrate globals)
//= require ./lib/auditor/speech.js

// 4. Display functions (depends on DOMPurify)
//= require ./lib/auditor/display.js

// 5. Translation functions (depends on Translate, CompleteTranslation, ToSay, ToTranslate)
//= require ./lib/auditor/translation.js

// 6. Network functions (depends on readText, readTextSub, etc.)
//= require ./lib/auditor/network.js

// 7. Configuration functions (depends on many UI elements)
//= require ./lib/auditor/config.js

// 8. Mail functions (depends on DOMPurify)
//= require ./lib/auditor/mail.js

// 9. Main initialization (depends on all above)
//= require ./lib/auditor/main.js

// Note: In production, replace the //= require comments with actual script tags:
// <script src="js/lib/auditor/text-processing.js"></script>
// <script src="js/lib/auditor/voices.js"></script>
// etc.
