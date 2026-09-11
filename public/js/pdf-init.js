// Forcer le nom global à pdfjsLib (compatible avec toutes les versions de PDF.js)
// PDF.js peut utiliser pdfjsLib ou pdfjsDist selon la version et le mode de build

if (typeof pdfjsDist !== 'undefined') {
    window.pdfjsLib = pdfjsDist;
} else if (typeof pdfjsLib === 'undefined') {
} else {
    // Configure PDF.js GlobalWorkerOptions
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    pdfjsLib.GlobalWorkerOptions.cMapUrl = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/cmaps/';
    pdfjsLib.GlobalWorkerOptions.cMapPacked = true;
}

// Also configure if pdfjsLib is already defined (from CDN)
if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    pdfjsLib.GlobalWorkerOptions.cMapUrl = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/cmaps/';
    pdfjsLib.GlobalWorkerOptions.cMapPacked = true;
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