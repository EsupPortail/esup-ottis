# TODO - Nettoyage et ameliorations

## Refactorisation JavaScript

### Elements DOM a nettoyer
- recognitionon : Checkbox cachee (class="sr-only") utilisee par plusieurs fonctions JS (Micro(), translation.js, speech.js).
  A faire : Refactoriser ui.js, translation.js et speech.js pour utiliser un store global (builtin_recognitionon dans store.js) au lieu de l'element DOM.
  Priorite : Moyenne (necessite tests approfondis).

---

## Autres notes
- Verifier la coherence des IDs entre la maquette et le code actuel.
- S'assurer que tous les styles CSS sont alignes avec les exigences RGAA.
