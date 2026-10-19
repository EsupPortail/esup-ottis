/**
 * Matomo Analytics Tracking for OTTIS
 * This script initializes the Matomo (formerly Piwik) web analytics tracker.
 * It must be loaded as a classic script (not ES module) to ensure proper
 * initialization, and must NOT declare de const/let globaux : le script
 * matomo.js externe reassigne _paq (une liaison lexicale globale bloquerait
 * son execution avec "Assignment to constant variable").
 *
 * Tracker URL: https://analytics.univ-nantes.fr/matomo.php
 * Site ID: 62 (OTTIS)
 */

// Initialize _paq array if it doesn't exist
window._paq = window._paq || [];

// Track the current page view
window._paq.push(['trackPageView']);

// Enable link tracking to track clicks on outbound links
window._paq.push(['enableLinkTracking']);

// Configure Matomo tracker
window._paq.push(['setTrackerUrl', 'https://analytics.univ-nantes.fr/matomo.php']);
window._paq.push(['setSiteId', '62']);

// Load Matomo script asynchronously
// NB : noms uniques (pas de const script/doc globaux) car ce fichier est un
// script classique - une liaison lexicale globale casserait les autres scripts.
const matomoLoaderScript = document.createElement('script');
const firstPageScript = document.getElementsByTagName('script')[0];

matomoLoaderScript.async = true;
matomoLoaderScript.src = 'https://analytics.univ-nantes.fr/matomo.js';
firstPageScript.parentNode.insertBefore(matomoLoaderScript, firstPageScript);
