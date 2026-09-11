/**
 * Auditor module index - imports all auditor submodules
 * This file should be included in HTML files that need auditor functionality
 */

// Import all auditor modules
// Note: In browser environment, we use script tags in HTML to load these files
// These comments document the available functions from each module

// From text-processing.js:
// - decodeHtmlEntities(text)
// - countLines(elm)
// - auto_size_text(elm, max_lines)

// From voices.js:
// - PopulateVoicesOut(selectname)
// - PopulateVoicesOutSub(selectname)
// - PopulateVoicesIn()
// - PopulateVoicesInSub()
// - InitVoices()
// - LoadVoices()
// - loadVoicesWhenAvailable()
// Global variables: lastNotebookVOContent, AllOutputVoices, TLangInput, maxvoices

// From speech.js:
// - Parle(txt)
// - ParleThen(txt, f)
// - CompleteSpeech()

// From display.js:
// - EcrireNB(ch)
// - EcrireNBSub(ch)
// - Ecrire(ch)
// - hideBanner(e)
// - showBanner(e)

// From translation.js:
// - Translate(ch, l, linput, lineid)
// - CompleteTranslation()

// From network.js:
// - increasependingrequests()
// - decreasependingrequests()
// - readText()
// - readTextSub()
// - readTextSubTranscript()
// - readImage()

// From config.js:
// - applyConfigSub()

// From main.js:
// - Lancer()
// - Init()
// - GoFS()
// - readTextQ()
// - showET(obj, num)

// From mail.js:
// - EnvoiMail()
// - sendnotebook(content)

// Export statement for ES modules (if used in module context)
// export {
//     decodeHtmlEntities, countLines, auto_size_text,
//     PopulateVoicesOut, PopulateVoicesOutSub, PopulateVoicesIn, PopulateVoicesInSub,
//     InitVoices, LoadVoices, loadVoicesWhenAvailable,
//     Parle, ParleThen, CompleteSpeech,
//     EcrireNB, EcrireNBSub, Ecrire, hideBanner, showBanner,
//     Translate, CompleteTranslation,
//     increasependingrequests, decreasependingrequests, readText, readTextSub,
//     readTextSubTranscript, readImage,
//     applyConfigSub, Lancer, Init, GoFS, readTextQ, showET,
//     EnvoiMail, sendnotebook
// };
