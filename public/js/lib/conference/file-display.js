/**
 * @module conference/file-display
 * Routeur unifié pour l'affichage des fichiers (PDF, PPTX, HTML, ODP).
 * Remplace la logique dispersée dans pdf-viewer.js et autres fichiers.
 */

import { displayPDF, initPdfViewer } from './pdf-viewer.js';
import { displayPPTXFromUrl } from './pptx-viewer.js';
import { _ } from '../dom/selector.js';

// État global pour le fichier courant
let currentFileType = null;
let currentFileUrl = null;

/**
 * Affiche un fichier selon son type.
 * @param {string} fileUrl - URL du fichier à afficher
 * @param {string} fileType - Type du fichier ('pdf', 'pptx', 'html', 'odp')
 */
export function displayFile(fileUrl, fileType) {
    currentFileType = fileType;
    currentFileUrl = fileUrl;

    // Masquer tous les conteneurs de visualisation
    hideAllViewers();

    // Afficher le conteneur approprié
    if (fileType === 'pdf') {
        showPdfViewer();
        displayPDF(fileUrl);
    } else if (fileType === 'pptx') {
        showPptxViewer();
        displayPPTXFromUrl(fileUrl, 'pptx-viewer');
    } else if (fileType === 'html' || fileType === 'odp') {
        showHtmlViewer();
        displayHTML(fileUrl);
    } else {
        showError('Type de fichier non supporté: ' + fileType);
    }
}

/**
 * Affiche un fichier HTML (ou ODP converti en HTML).
 * @param {string} fileUrl - URL du fichier HTML
 */
function displayHTML(fileUrl) {
    const htmlViewer = _('html-viewer');
    if (!htmlViewer) {
        showError('HTML Viewer container not found');
        return;
    }

    fetch(fileUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(htmlContent => {
            // Sanitize HTML content before inserting (basic protection)
            // Note: For full security, use DOMPurify (already loaded in your app)
            if (typeof window.DOMPurify !== 'undefined') {
                htmlContent = window.DOMPurify.sanitize(htmlContent);
            }
            htmlViewer.innerHTML = htmlContent;
        })
        .catch(error => {
            console.error('Erreur chargement HTML:', error);
            showError('Erreur de chargement du fichier HTML: ' + error.message);
        });
}

/**
 * Masque tous les conteneurs de visualisation.
 */
function hideAllViewers() {
    const containers = ['pdf-viewer', 'pptx-viewer', 'html-viewer'];
    containers.forEach(id => {
        const el = _(id);
        if (el) {
            el.style.display = 'none';
        }
    });
}

/**
 * Affiche le visualiseur PDF.
 */
function showPdfViewer() {
    const pdfViewer = _('pdf-viewer');
    if (pdfViewer) {
        pdfViewer.style.display = 'block';
        initPdfViewer(); // Initialise le canvas PDF.js
    }
}

/**
 * Affiche le visualiseur PPTX.
 */
function showPptxViewer() {
    const pptxViewer = _('pptx-viewer');
    if (pptxViewer) {
        pptxViewer.style.display = 'block';
    }
}

/**
 * Affiche le visualiseur HTML.
 */
function showHtmlViewer() {
    const htmlViewer = _('html-viewer');
    if (htmlViewer) {
        htmlViewer.style.display = 'block';
    }
}

/**
 * Affiche une erreur dans l'interface.
 * @param {string} message - Message d'erreur
 */
function showError(message) {
    const errorContainer = _('file-error');
    if (errorContainer) {
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
    } else {
        console.error('Erreur:', message);
        alert('Erreur: ' + message);
    }
}
