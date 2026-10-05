/**
 * @module conference/pptx-viewer
 * Affiche les fichiers PPTX directement dans le navigateur.
 * Utilise PptxGenJS (chargé depuis CDN) ou une alternative simple.
 * 
 * NOTE: PptxGenJS v3.x API documentation:
 * - PptxGenJS.readFile(blob) returns a Promise that resolves to a Presentation object
 * - Presentation object has: slides (array), getSlide(i), slide.render()
 */

import { _ } from '../dom/selector.js';
import { safeSetTimeout } from '../utils/cleanup.js';

// État du visualiseur PPTX
let pptxLoaded = false;
let currentSlideIndex = 0;
let slidesData = [];

/**
 * Charge PptxGenJS depuis CDN
 */
async function loadPptxGenJS() {
    if (pptxLoaded) return true;

    return new Promise((resolve, reject) => {
        if (typeof PptxGenJS !== 'undefined') {
            pptxLoaded = true;
            resolve(true);
            return;
        }

        const scriptUrl = 'https://cdn.jsdelivr.net/npm/pptxgenjs@3.12.0/dist/pptxgen.bundle.min.js';
        const script = document.createElement('script');
        script.src = scriptUrl;
        script.onload = () => {
            console.log('PptxGenJS loaded successfully');
            if (typeof PptxGenJS !== 'undefined') {
                pptxLoaded = true;
                resolve(true);
            } else {
                console.error('PptxGenJS not available after loading');
                reject(new Error('PptxGenJS failed to initialize'));
            }
        };
        script.onerror = () => {
            console.error('Failed to load PptxGenJS from CDN');
            reject(new Error('PptxGenJS CDN load failed'));
        };
        document.head.appendChild(script);
    });
}

/**
 * Extrait les slides depuis un objet présentation PptxGenJS
 */
function extractSlides(presentation) {
    const slides = [];
    
    // Essayer plusieurs façons d'obtenir les slides
    if (presentation.slides && Array.isArray(presentation.slides)) {
        return presentation.slides;
    }
    
    if (presentation.getSlides && typeof presentation.getSlides === 'function') {
        return presentation.getSlides();
    }
    
    if (presentation.getSlide && typeof presentation.getSlide === 'function') {
        const count = presentation.getSlideCount ? presentation.getSlideCount() : 0;
        for (let i = 0; i < count; i++) {
            slides.push(presentation.getSlide(i));
        }
        return slides;
    }
    
    return [];
}

/**
 * Rend une slide en HTML
 */
function renderSlideToHtml(slide) {
    if (!slide) return '<div style="color: orange;">Slide vide</div>';
    
    // Essayer plusieurs méthodes de rendu
    if (typeof slide.render === 'function') {
        return slide.render();
    }
    
    if (typeof slide.getHtml === 'function') {
        return slide.getHtml();
    }
    
    if (slide.html) {
        return slide.html;
    }
    
    if (slide.content) {
        return slide.content;
    }
    
    console.error('Slide object:', slide);
    return '<div style="color: red;">Impossible de rendre la slide</div>';
}

/**
 * Affiche un fichier PPTX dans le conteneur
 */
export async function displayPPTX(pptxFile, containerId = 'pptx-viewer') {
    const container = _(containerId);
    
    if (!container) {
        console.error(`Container #${containerId} not found`);
        return;
    }

    try {
        // Afficher le chargement
        container.innerHTML = '<p style="text-align: center; padding: 20px;">Chargement de la présentation PPTX...</p>';

        // Charger PptxGenJS
        await loadPptxGenJS();

        // Lire le fichier PPTX
        // PptxGenJS v3 API: PptxGenJS.readFile() retourne une Promise
        const presentation = await PptxGenJS.readFile(pptxFile);
        
        if (!presentation) {
            throw new Error('Impossible de charger le fichier PPTX');
        }

        // Extraire les slides
        slidesData = extractSlides(presentation);
        
        if (!slidesData || slidesData.length === 0) {
            throw new Error('Aucune slide trouvée');
        }

        currentSlideIndex = 0;
        
        // Afficher la première slide
        container.innerHTML = `
            <div id="pptx-slides-container" style="width: 100%; height: 100%;">
                <div id="pptx-slide-0" style="width: 100%; height: 100%; overflow: auto;">
                    ${renderSlideToHtml(slidesData[0])}
                </div>
            </div>
        `;

        // Ajouter les contrôles
        addControls(container, slidesData.length);

    } catch (error) {
        console.error('Erreur PPTX:', error);
        container.innerHTML = `
            <div style="text-align: center; padding: 20px; color: red; background: #ffeeee;">
                <p><strong>Erreur de chargement PPTX:</strong> ${error.message}</p>
                <p style="font-size: 12px;">Vérifiez la console pour plus de détails.</p>
            </div>
        `;
    }
}

/**
 * Ajoute les boutons de navigation
 */
function addControls(container, totalSlides) {
    const controlsDiv = document.createElement('div');
    controlsDiv.style.textAlign = 'center';
    controlsDiv.style.marginTop = '10px';
    controlsDiv.style.padding = '10px';
    controlsDiv.style.backgroundColor = '#f5f5f5';
    controlsDiv.innerHTML = `
        <button id="pptx-prev-btn" style="margin: 0 10px; padding: 5px 15px;">← Précédent</button>
        <span id="pptx-page-indicator">Slide ${currentSlideIndex + 1} / ${totalSlides}</span>
        <button id="pptx-next-btn" style="margin: 0 10px; padding: 5px 15px;">Suivant →</button>
    `;
    container.appendChild(controlsDiv);

    // Navigation précédente
    _('pptx-prev-btn')?.addEventListener('click', () => {
        if (currentSlideIndex > 0) {
            currentSlideIndex--;
            container.querySelector('#pptx-slides-container').innerHTML = `
                <div id="pptx-slide-${currentSlideIndex}" style="width: 100%; height: 100%; overflow: auto;">
                    ${renderSlideToHtml(slidesData[currentSlideIndex])}
                </div>
            `;
            _('pptx-page-indicator').textContent = `Slide ${currentSlideIndex + 1} / ${totalSlides}`;
        }
    });

    // Navigation suivante
    _('pptx-next-btn')?.addEventListener('click', () => {
        if (currentSlideIndex < slidesData.length - 1) {
            currentSlideIndex++;
            container.querySelector('#pptx-slides-container').innerHTML = `
                <div id="pptx-slide-${currentSlideIndex}" style="width: 100%; height: 100%; overflow: auto;">
                    ${renderSlideToHtml(slidesData[currentSlideIndex])}
                </div>
            `;
            _('pptx-page-indicator').textContent = `Slide ${currentSlideIndex + 1} / ${totalSlides}`;
        }
    });
}

/**
 * Charge un PPTX depuis une URL
 */
export async function displayPPTXFromUrl(url, containerId = 'pptx-viewer') {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        const blob = await response.blob();
        await displayPPTX(blob, containerId);
    } catch (error) {
        console.error('Erreur chargement URL PPTX:', error);
        const container = _(containerId);
        if (container) {
            container.innerHTML = `<p style="color: red; text-align: center;">Erreur: ${error.message}</p>`;
        }
        throw error;
    }
}
