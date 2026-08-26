/**
 * @module lib/pdf/init
 * PDF.js initialization and console filtering
 * Converted from pdf-init.js
 */

// Forcer le nom global à pdfjsLib (compatible avec toutes les versions de PDF.js)
// PDF.js peut utiliser pdfjsLib ou pdfjsDist selon la version et le mode de build
if (typeof window !== 'undefined') {
    if (typeof window.pdfjsDist !== 'undefined') {
        window.pdfjsLib = window.pdfjsDist;
    } else if (typeof window.pdfjsLib === 'undefined') {
        // pdfjsLib will be set by the CDN script
    }
}

// === FILTRE DES MESSAGES INUTILES DE PDF.JS ===
// Sauvegarder les fonctions originales
const originalConsoleWarn = console.warn;
const originalConsoleError = console.error;

// Filtrer les warnings "Knockout groups"
console.warn = function(...args) {
    if (!args.some(arg => typeof arg === 'string' && arg.includes('Knockout groups'))) {
        originalConsoleWarn.apply(console, args);
    }
};

// Filtrer les violations requestAnimationFrame
console.error = function(...args) {
    if (!args.some(arg => typeof arg === 'string' && arg.includes('requestAnimationFrame'))) {
        originalConsoleError.apply(console, args);
    }
};

// Export for testing purposes
export { originalConsoleWarn, originalConsoleError };
