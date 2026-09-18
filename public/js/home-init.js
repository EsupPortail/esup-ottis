/**
 * Home page initialization module
 * Imports and initializes all required ES modules for the homepage
 */

import { initBrowserDetection } from './lib/browser-detection.js';
import { initCarrousel } from './lib/carroussel.js';

// Initialize all homepage modules
initBrowserDetection();
initCarrousel();
