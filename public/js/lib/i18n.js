/**
 * @module lib/i18n
 * Internationalization (i18n) utilities for OMIST
 * Handles loading translations and translating DOM elements
 */

// Store for loaded translations
const translations = {};

/**
 * Get a nested value from an object using dot notation
 * @param {Object} obj - The object to search in
 * @param {string} path - The path to the value (e.g., 'button.submit')
 * @returns {*} The value at the path, or undefined
 */
function getNestedValue(obj, path) {
  return path.split('.').reduce((o, p) => (o || {})[p], obj);
}

/**
 * Translate a single DOM element based on its data-key attribute
 * @param {HTMLElement} element - The element to translate
 */
function translateElement(element) {
  const key = element.getAttribute('data-key');
  const ariaKey = element.getAttribute('data-aria-key');
  const lang = element.closest('[lang]')?.lang || 'fr';

  if (ariaKey) {
    const ariaValue = getNestedValue(translations[lang], ariaKey);
    if (ariaValue) {
      element.setAttribute('aria-label', ariaValue);
    }
  }

  const placeholderKey = element.getAttribute('data-placeholder-key');
  if (placeholderKey) {
    const placeholderValue = getNestedValue(translations[lang], placeholderKey);
    if (placeholderValue) {
      element.setAttribute('placeholder', placeholderValue);
    }
  }

  if (!key) return;

  // Get translation value
  const value = getNestedValue(translations[lang], key);

  // Sanitize and set content
  if (window.DOMPurify && typeof window.DOMPurify.sanitize === 'function') {
    element.innerHTML = window.DOMPurify.sanitize(value || `⚠️ ${key}`);
  } else {
    element.innerHTML = value || `⚠️ ${key}`;
  }
}

/**
 * Load translations for a specific language
 * @param {string} lang - The language code to load
 * @returns {Promise<Object>} The loaded translations
 */
async function loadTranslations(lang) {
  try {
    const response = await fetch(`/lang/${lang}.json`);

    if (!response.ok) {
      throw new Error(`Language ${lang} not found (status: ${response.status}).`);
    }

    translations[lang] = await response.json();
    return translations[lang];
  } catch (error) {
    console.error('Error loading translations:', error.message);
    translations[lang] = {};
    return {};
  }
}

/**
 * Initialize translations for the current page language
 * Translates all elements with the 'translate' class
 * @param {string} lang - The language to initialize
 */
export async function initTranslations(lang) {
  await loadTranslations(lang);

  // Translate all elements with the 'translate' class
  document.querySelectorAll('.translate').forEach(translateElement);
}

/**
 * Translate a key with optional variables ({var} substitution)
 * @param {string} key - The translation key (e.g. 'conference.pdf.pageIndicator')
 * @param {Object} [vars] - Variables to substitute (e.g. { n: 1, m: 5 })
 * @returns {string} The translated string, or the key if not found
 */
export function t(key, vars = {}) {
  const lang = getCurrentLanguage();
  const value = getNestedValue(translations[lang], key);
  if (typeof value !== 'string') {
    return key;
  }
  return Object.entries(vars).reduce(
    (acc, [name, val]) => acc.replaceAll(`{${name}}`, String(val)),
    value
  );
}

/**
 * Change the application language without reloading the page
 * Updates the document language, the ?lang= URL parameter (history.replaceState)
 * and re-translates every '.translate' element in place
 * @param {string} lang - The target language code
 */
export async function changeLanguage(lang) {
  document.documentElement.lang = lang;
  const params = new URLSearchParams(window.location.search);
  params.set('lang', lang);
  window.history.replaceState(null, '', `${window.location.pathname}?${params.toString()}${window.location.hash}`);
  await initTranslations(lang);
  document.dispatchEvent(new CustomEvent('ottis:languagechange', { detail: { lang } }));
}

/**
 * Get the current language from URL or default to 'fr'
 * @returns {string} The current language code
 */
export function getCurrentLanguage() {
  const urlParams = new URLSearchParams(window.location.search);
  // Page auditeur : interface en anglais par defaut
  const defaultLang = document.body && document.body.classList.contains('ottis-page-auditor')
    ? 'en'
    : 'fr';
  return urlParams.get('lang') || defaultLang;
}

/**
 * Bind the language selector to the module-based translation logic.
 */
function bindLanguageSelector() {
  const select = document.getElementById('language-select');
  if (!select) return;

  select.addEventListener('change', (event) => {
    changeLanguage(event.target.value);
  });
}

/**
 * Auto-initialize translations on DOM ready
 */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    const lang = getCurrentLanguage();
    initTranslations(lang);
    bindLanguageSelector();
  });
} else {
  const lang = getCurrentLanguage();
  initTranslations(lang);
  bindLanguageSelector();
}
