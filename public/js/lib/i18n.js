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
  if (!key) return;

  // Get language from closest parent with lang attribute, default to 'fr'
  const lang = element.closest('[lang]')?.lang || 'fr';

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
 * Change the application language and reload the page
 * @param {string} lang - The target language code
 */
export function changeLanguage(lang) {
  window.location.search = `?lang=${lang}`;
}

/**
 * Get the current language from URL or default to 'fr'
 * @returns {string} The current language code
 */
export function getCurrentLanguage() {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get('lang') || 'fr';
}

/**
 * Auto-initialize translations on DOM ready
 */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    const lang = getCurrentLanguage();
    initTranslations(lang);
  });
} else {
  const lang = getCurrentLanguage();
  initTranslations(lang);
}
