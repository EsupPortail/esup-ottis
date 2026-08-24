// Stocke les traductions
let translations = {};

// Fonction pour récupérer une valeur imbriquée dans un objet
function getNestedValue(obj, path) {
    return path.split('.').reduce((o, p) => (o || {})[p], obj);
}

// Fonction pour traduire un élément
function translateElement(element) {
    const key = element.getAttribute('data-key');
    // Accède à translations[lang][clé_imbriquée]
    const lang = element.closest('[lang]')?.lang || 'fr';
    const value = getNestedValue(translations[lang], key);
    element.innerHTML = DOMPurify.sanitize(value || `⚠️ ${key}`);
}

// Fonction pour initialiser les traductions
async function initTranslations(lang) {
    try {
        const response = await fetch(`lang/${lang}.json`);
        if (!response.ok) throw new Error(`Langue ${lang} non trouvée (statut : ${response.status}).`);
        translations[lang] = await response.json(); // Stocke sous translations['fr']
        document.querySelectorAll('.translate').forEach(translateElement);
    } catch (error) {
        console.error("Erreur :", error.message);
        document.querySelectorAll('.translate').forEach(element => {
            element.innerHTML = DOMPurify.sanitize(`⚠️ ${element.getAttribute('data-key')}`);
        });
    }
}

// Change la langue et recharge la page
function changeLanguage(lang) {
    window.location.search = `?lang=${lang}`;
}

// Initialise au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    //console.log("DOMContentLoaded déclenché");
    const urlParams = new URLSearchParams(window.location.search);
    const lang = urlParams.get('lang') || 'fr';
    initTranslations(lang);
});