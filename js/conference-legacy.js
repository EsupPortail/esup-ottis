/**
 * conference-legacy.js
 * Point d'entrée pour la compatibilité legacy avec les modules ES
 * Doit être chargé avec <script type="module">
 */

// Importer tous les modules principaux
import * as conference from './lib/conference/index.js';
import * as state from './lib/conference/state.js';
import * as domHelpers from './lib/conference/dom-helpers.js';
import * as network from './lib/conference/network.js';
import * as compat from './lib/conference/compat.js';
import * as utils from './lib/conference/utils.js';
import * as events from './lib/conference/events.js';
import * as lib from './lib/index.js';


// Tableau de tous les modules à exposer
const modules = [
    conference,
    state,
    domHelpers,
    network,
    compat,
    utils,
    events,
    lib
];

// Attacher toutes les exports à window pour la compatibilité
modules.forEach(module => {
    Object.entries(module).forEach(([key, value]) => {
        // Ne pas écraser les propriétés existantes de window
        if (!(key in window)) {
            window[key] = value;
        }
    });
});

// Appeler les fonctions d'initialisation après le chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    // Initialiser les event listeners
    if (typeof initConferenceEvents === 'function') {
        initConferenceEvents();
    }
    
    if (typeof Init === 'function') {
        Init();
    }
    if (typeof Lancer === 'function') {
        Lancer();
    }
    if (typeof InitVoices === 'function') {
        InitVoices();
    }
    if (typeof applyConfig === 'function') {
        setTimeout(applyConfig, 100);
    }
});

// Logger pour débogage
console.log('Conference ES modules loaded - legacy compatibility enabled');