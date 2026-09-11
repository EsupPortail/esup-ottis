// Fonction pour détecter si le navigateur n'est ni Chrome ni Edge
function isNotChromeOrEdge() {
  const userAgent = navigator.userAgent.toLowerCase();
  // Vérifie si c'est Chrome ou Edge
  const isChrome = /chrome|crios/.test(userAgent) && !/edge|edg|opr/.test(userAgent);
  const isEdge = /edge|edg/.test(userAgent);
  return !(isChrome || isEdge);
}

// Afficher le bandeau si l'utilisateur utilise Firefox
window.onload = function () {
  if (isNotChromeOrEdge()) {
    document.getElementById('firefox-warning').style.display = 'block';
    document.body.classList.add('firefox-warning-active');
  }
};
