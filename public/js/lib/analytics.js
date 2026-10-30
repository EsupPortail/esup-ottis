/**
 * Matomo Analytics Tracking for OTTIS
 * This script initializes the Matomo (formerly Piwik) web analytics tracker.
 * It must be loaded as a classic script (not ES module) to ensure proper
 * initialization. Le script matomo.js externe reassigne _paq : tout est
 * encapsule dans une IIFE pour eviter les liaisons lexicales globales.
 *
 * Configuration : window.OTTIS_MATOMO = { url, siteId }, injectee par le
 * serveur (config/app.php via MATOMO_URL / MATOMO_SITE_ID du .env).
 * Absente ou incomplete : ce script n'emmet rien. Les vues ne l'incluent
 * meme pas (garde cote serveur).
 */

(function () {
  const matomoConfig = (typeof window !== 'undefined' && window.OTTIS_MATOMO) || null;
  if (!matomoConfig || !matomoConfig.url || !matomoConfig.siteId) {
    return;
  }

  // Initialize _paq array if it doesn't exist
  window._paq = window._paq || [];

  window._paq.push(['setTrackerUrl', matomoConfig.url]);
  window._paq.push(['setSiteId', String(matomoConfig.siteId)]);

  // Track the current page view and outbound link clicks
  window._paq.push(['trackPageView']);
  window._paq.push(['enableLinkTracking']);

  // Load Matomo script asynchronously : il est heberge au meme endroit que
  // le tracker (matomo.php -> matomo.js)
  const matomoLoaderScript = document.createElement('script');
  const firstPageScript = document.getElementsByTagName('script')[0];

  matomoLoaderScript.async = true;
  matomoLoaderScript.src = matomoConfig.url.replace(/\/matomo\.php.*$/, '/matomo.js');
  firstPageScript.parentNode.insertBefore(matomoLoaderScript, firstPageScript);
})();
