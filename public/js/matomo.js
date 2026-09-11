let _paq = window._paq = window._paq || [];
/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function() {
let u="https://analytics.univ-nantes.fr/";
_paq.push(['setTrackerUrl', u+'matomo.php']);
_paq.push(['setSiteId', '62']);
let d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
})();