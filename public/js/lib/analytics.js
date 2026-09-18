/**
 * Matomo Analytics Tracking for OMIST
 * This script initializes the Matomo (formerly Piwik) web analytics tracker.
 * It must be loaded as a classic script (not ES module) to ensure proper initialization.
 *
 * Tracker URL: https://analytics.univ-nantes.fr/matomo.php
 * Site ID: 62 (OMIST)
 */

// Initialize _paq array if it doesn't exist
let _paq = window._paq = window._paq || [];

// Track the current page view
_paq.push(['trackPageView']);

// Enable link tracking to track clicks on outbound links
_paq.push(['enableLinkTracking']);

// Configure Matomo tracker
(function() {
  const trackerUrl = 'https://analytics.univ-nantes.fr/';
  _paq.push(['setTrackerUrl', trackerUrl + 'matomo.php']);
  _paq.push(['setSiteId', '62']);

  // Load Matomo script asynchronously
  const doc = document;
  const script = doc.createElement('script');
  const firstScript = doc.getElementsByTagName('script')[0];

  script.async = true;
  script.src = trackerUrl + 'matomo.js';
  firstScript.parentNode.insertBefore(script, firstScript);
})();
