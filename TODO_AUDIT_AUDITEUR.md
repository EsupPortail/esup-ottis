# TODO List : Optimisation auditeur.php

## 🔴 PRIORITÉ CRITIQUE - À faire immédiatement

### 1. Code mort à supprimer (>100 lignes)

- [ ] **QR Code scanner tab** (~70 lignes commentées)
  - Lignes: ~245-314 dans auditeur.php
  - Contient: `<div id="tabs-4">`, `StartScan()`, html5-qrcode.min.js
  - Action: DELETE
  - Raison: Code obsolète, fonction orpheline, jamais utilisée

- [ ] **Visio conference tab** (~10 lignes)
  - Lignes: ~236-240
  - Contient: `<div id="tabs-6">` commenté
  - Action: DELETE
  - Raison: Code mort depuis longtemps

- [ ] **Word Cloud tab** (~50 lignes)
  - Lignes: ~320-361
  - État: `style="display:none"` (doublement caché)
  - Action: DELETE ou MOVE to feature branch
  - Raison: Feature jamais terminée

### 2. Fonctions orphelines

- [ ] **ShowMenu()** 
  - Appelée: Ligne 209 via `<div id="showmenu" onclick="ShowMenu();">`
  - Vérifier: Existe-t-elle dans js/student-func.js?
  - Si NON trouvée: DELETE onclick, utiliser popmenu existant
  - Si trouvée: DOCUMENTER sa fonction

- [ ] **needSynchronize variable**
  - Définie: Lignes 75-79 (PHP → JS)
  - Chercher utilisation: `grep "needSynchronize" js/*.js`
  - Si NON utilisée: DELETE PHP code + console log

### 3. Variables commentées inutiles

- [ ] Nettoyer onload commentée
  - Ligne: ~243
  - Avant: `onload="Init(); /*InitVoices(); setTimeout(...);*/ setTimeout(...)"`
  - Après: Array clean dans PHP

---

## 🟠 PRIORITÉ HAUTE - Sprint 1 (semaine 1)

### 4. Architecture PHP - Uniformiser avec conference.php

- [ ] **Remplacer headers inline par NoCache.php**
  - Fichier: auditeur.php
  - Avant: Lignes 2-6 (4 header calls)
  - Après: `require("./NoCache.php");`
  - Impact: -4 lignes, maintenance centralisée

- [ ] **Ajouter header.php include**
  - Ajouter après config.php:
    ```php
    include_once __DIR__ . '/includes/header.php';
    ```
  - Remplacer: `<!doctype html>...<body>` manuellement écrit
  - Fichier template: includes/header.php

- [ ] **Indexer $T_lang par code langue**
  - Fichier: auditeur.php (lignes ~46-59)
  - Avant: `$T_lang[]= array(...)`
  - Après: `$T_lang[$code] = array(...)`
  - Impact: O(n) → O(1) lookup dans JS

- [ ] **Créer tableaux config (CSS/JS/Onload)**
  - Ajouter après includes:
    ```php
    $pageStyles = [
        "jquery/flat_black/jquery-ui.min.css",
        "styles/student.css"
    ];
    $pageScripts = ["/js/student-def.js", "/js/student-func.js"];
    $onloadFunctions = ["Init()", "loadVoicesWhenAvailable()"];
    $onloadScripts = implode(";\n    ", $onloadFunctions);
    ```
  - Impact: Maintenabilité, scalabilité

### 5. Remplacer CSS/JS echo par inclusion template

- [ ] **Remplacer CSS conditional echo**
  - Ligne: ~60-73 (if theme...)
  - Avant: `<?php if ($theme != "") { echo '<link...'}`
  - Après: Utiliser header.php template avec boucle foreach
  - Fichier: includes/header.php existe déjà

- [ ] **Remplacer JS include conditional echo**
  - Ligne: ~74-85 (if theme...)
  - Avant: `<?php if ($theme != "") { echo '<script...'}`
  - Après: Utiliser header.php template avec boucle foreach

---

## 🟡 PRIORITÉ MOYENNE - Sprint 2 (semaine 2)

### 6. Remplacer événements inline par listeners JS

- [ ] **Centraliser onclick micon**
  - HTML Ligne: ~245 `<div id="micon" onclick="gotolive=true;...">`
  - Créer: `js/student-events.js` nouveau fichier
  - Remplacer: Par listener addEventListener
  ```js
  document.getElementById('micon').addEventListener('click', () => {
      gotolive = true;
      CompleteSpeech();
      // ...
  });
  ```

- [ ] **Centraliser onchange language select**
  - HTML: `<select id="VoiceOutput" onchange="setLanguageOutput(...)">`
  - Placer dans student-events.js listener

- [ ] **Centraliser onchange font/opacity**
  - HTML: `<select id="subtitlesfontsize" onchange="...">`
  - HTML: `<select id="selectopacity" onchange="...">`
  - Placer dans student-events.js listeners

- [ ] **Lister tous les onclick/onchange inline**
  - Commande: `grep -n "onchange\|onclick" auditeur.php | wc -l`
  - Objectif: < 5 handlers inline (au lieu de 15+)

### 7. Styles inline → Classes CSS

- [ ] **Identifier tous styles inline**
  - Commande: `grep -n 'style="' auditeur.php | wc -l`
  - Résultat actuel: ~50+ styles inline

- [ ] **Créer CSS classes**
  - Fichier: styles/student.css (enrichir)
  ```css
  .hidden { display: none; }
  .visible { display: block; }
  .bordered { border: solid 1px; }
  .transparent-50 { opacity: 0.5; }
  ```

- [ ] **Remplacer style=" par class=**
  - `<span style="display:none">` → `<span class="hidden">`
  - `<div style="border: solid 1px;">` → `<div class="bordered">`

---

## 🟢 PRIORITÉ BASSE - Sprint 3 (Nice to have)

### 8. Code quality improvements

- [ ] **Ajouter JSDoc comments**
  - Notamment: Init(), Lancer(), setLanguageOutput()

- [ ] **Valider HTML**
  - Utiliser: `npm install html-validate`
  - Commande: `npx html-validate auditeur.php`

- [ ] **Ajouter const vs var**
  - Dans student-def.js: Remplacer `var` par `const`

- [ ] **Consolider JavaScript**
  - Combiner: student-func.js functions redondantes
  - Ou créer: shared-student-teacher-func.js

---

## ✅ Validation & Testing

### 9. Tests à effectuer après chaque changement

- [ ] **HTML validation** - `npx html-validate auditeur.php`
- [ ] **Visual regression** - Ouvrir en navigateur, comparer layout
- [ ] **Functional test** - Synchronize avec teacher app
- [ ] **Performance** - Vérifier temps chargement JS
- [ ] **Console errors** - F12, vérifier pas de red errors
- [ ] **Mobile responsive** - Tester sur mobile (40em breakpoint)

### 10. Déploiement progressif

- [ ] **Créer branche**: `git checkout -b refactor/auditeur-modernization`
- [ ] **Étape 1**: Merge dead code removal (pas de risque)
- [ ] **Étape 2**: Merge architecture (puis test complet)
- [ ] **Étape 3**: Merge events centralization (puis test)
- [ ] **Étape 4**: Merge CSS consolidation (puis test)
- [ ] **Final**: QA avant merge to main

---

## 📊 Métriques avant/après

| Métrique | Avant | Après | Gain |
|----------|-------|-------|------|
| Lignes de code mort | 120+ | 0 | -120 |
| CSS inline styles | 50+ | <10 | -40 |
| Onclick inline | 15+ | <5 | -10 |
| Includes PHP | 0 | 3 | Meilleure arch |
| Maintenabilité | ⭐⭐ | ⭐⭐⭐⭐⭐ | +3 |

---

## 📝 Notes / Questions à clarifier

**À vérifier:**
- [ ] Is `theme` parameter still used? (ligne ~57)
- [ ] Does `$synchronize` variable get used? 
- [ ] Should WordCloud tab be kept as hidden feature?
- [ ] Are there any external dependencies on ShowMenu()?
- [ ] Is needSynchronize used anywhere?

---

## 👥 Affectation (si équipe)

- **Dev 1**: Code cleanup (sections 1-3) → Easy, low risk
- **Dev 2**: Architecture refactoring (section 4-5) → Medium difficulty  
- **Dev 3**: Events + CSS consolidation (sections 6-7) → Higher difficulty
- **QA**: Testing & validation (section 9) → Parallel

---

## 📅 Estimations (en jours)

| Tâche | Estimation | Dépendance |
|-------|-----------|-----------|
| Code mort cleanup | 0.5j | Aucune |
| Architecture refactor | 1.5j | Cleanup done |
| Events centralization | 1.5j | Architecture done |
| CSS consolidation | 1j | Events done |
| Testing & QA | 1j | Tout complet |
| **TOTAL** | **5.5 jours** | |

---

## ⚠️ Risques & Mitigations

| Risque | Impact | Mitigation |
|--------|--------|-----------|
| Breakage du layout | Critique | Test dans tous les navigateurs |
| Event handlers non attachés | Haute | Tests fonctionnels complets |
| CSS classes mal appliquées | Moyenne | Visual regression tests |
| Header.php incompatibilité | Moyenne | Test avec ancien navigateur |
| Regression en IE11 | Basse | Peut ignorer (EOL) |

---

## 🎯 Success Criteria

✅ Code merger doit passer:
- [ ] Pas de console.errors
- [ ] Layout identique à "avant"
- [ ] Tous features fonctionnent (tchat, sync, traduction)
- [ ] Performance pas dégradée (< 100ms Δ)
- [ ] Code review approuvé par 2 devs minimum
- [ ] Pas de regression QA

---

## 📞 Escalation

Si blocage: Contacter `support-omist@univ-nantes.fr` pour:
- Clarifier intention de fonctionnalités (QR scan, Visio, WordCloud)
- Approuver breaking changes
- Discuter architectural decisions

---

**Last Updated**: 2025-01-16  
**Status**: 🔵 Ready to start  
**Priority**: 🔴 CRITICAL first phase
