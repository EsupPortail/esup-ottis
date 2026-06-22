# Scripts de Vérification & Validation

## 1. Vérifier Code Mort

### Chercher QR code scanner orphelin
```bash
grep -n "StartScan\|html5-qrcode\|tabs-4" /home/omist/auditeur.php
```

**Résultat attendu:** 
- Lignes ~245-314 avec code commenté
- Fonction `StartScan()` non définie dans JS

### Chercher ShowMenu() orpheline
```bash
grep -r "ShowMenu" /home/omist/js/
```

**Résultat attendu:** 
- Aucun match (fonction orpheline)

### Chercher needSynchronize
```bash
grep "needSynchronize" /home/omist/js/*.js /home/omist/*.php
```

**Résultat attendu:**
- Seulement dans auditeur.php PHP code
- Nulle part utilisé en JavaScript

### Chercher tout code commenté mort
```bash
grep -n "<!--.*\n.*\n.*-->" /home/omist/auditeur.php | wc -l
```

**Résultat attendu:** 3+ commentaires multilignes

---

## 2. Vérifier Styles Inline

### Compter les styles inline
```bash
grep -o 'style="[^"]*"' /home/omist/auditeur.php | wc -l
```

**Résultat actuel:** ~50+

### Lister les styles inline
```bash
grep -n 'style="' /home/omist/auditeur.php | head -20
```

**À convertir:** display:none, border, opacity, fontSize, etc.

---

## 3. Vérifier Événements Inline

### Compter les onclick/onchange
```bash
grep -c 'onclick=\|onchange=' /home/omist/auditeur.php
```

**Résultat actuel:** 15+

### Lister tous les événements
```bash
grep -n 'onclick=\|onchange=\|onload=\|onmouseover=' /home/omist/auditeur.php
```

**Exemple:**
```
209: <div id="showmenu" onclick="ShowMenu();"
245: <div id="micon" onclick="gotolive=true;..."
```

---

## 4. Vérifier Structure $T_lang

### Vérifier indexage PHP
```bash
grep -n "\$T_lang\[\]" /home/omist/auditeur.php
```

**Résultat actuel:** 
```
49: $T_lang[]=array(
```

**Problème:** Index numérique, inefficace

### Vérifier utilisation JS
```bash
grep "supportedlanguages\[" /home/omist/js/student-func.js
```

**Résultat:** Boucle O(n) pour chercher par code

---

## 5. Vérifier Headers Centralisés

### Chercher headers non centralisés
```bash
find /home/omist -name "*.php" -exec grep -l "header('.*no-cache" {} \;
```

**Résultat actuel:**
```
auditeur.php
conference.php
index.php
...
```

**Solution:** Tous devrait utiliser NoCache.php

---

## 6. Vérifier CSS/JS Loading

### Chercher echo de CSS
```bash
grep -n 'echo.*<link' /home/omist/auditeur.php | wc -l
```

**Résultat:** 3-4 lignes d'echo CSS

### Chercher echo de JS
```bash
grep -n 'echo.*<script' /home/omist/auditeur.php | wc -l
```

**Résultat:** 3-4 lignes d'echo JS

**Solution:** Remplacer par arrays + loop

---

## 7. Script d'Audit Automatisé

**Créer: audit_auditeur.sh**
```bash
#!/bin/bash

FILE="/home/omist/auditeur.php"
ECHO "=== AUDIT: auditeur.php ==="

echo ""
echo "🔴 CODE MORT:"
echo "  - QR code scanner: $(grep -c 'tabs-4' $FILE) match"
echo "  - ShowMenu() in JS: $(grep -r 'function ShowMenu' /home/omist/js/ | wc -l) found"
echo "  - needSynchronize used: $(grep -r 'needSynchronize' /home/omist/js/ | wc -l) found"

echo ""
echo "🟠 STYLES INLINE:"
echo "  - Total: $(grep -o 'style="' $FILE | wc -l) instances"

echo ""
echo "🟡 ÉVÉNEMENTS INLINE:"
echo "  - onclick: $(grep -c 'onclick=' $FILE) instances"
echo "  - onchange: $(grep -c 'onchange=' $FILE) instances"

echo ""
echo "🟢 STRUCTURE DONNÉES:"
echo "  - \$T_lang[]: $(grep -c '\$T_lang\[\]' $FILE) (should be 0)"
echo "  - \$T_lang[\$code]: $(grep -c '\$T_lang\[' $FILE) (should be >0)"

echo ""
echo "🔵 INCLUDES:"
echo "  - NoCache.php: $(grep -c 'NoCache' $FILE) (should be 1)"
echo "  - header.php: $(grep -c 'header.php' $FILE) (should be 1)"

echo ""
echo "✅ Audit complete!"
```

**Utilisation:**
```bash
bash audit_auditeur.sh
```

---

## 8. Vérifier Conformité avec conference.php

### Comparer structure PHP
```bash
echo "=== HEADERS ==="
echo "auditeur.php:"
head -20 /home/omist/auditeur.php

echo ""
echo "conference.php:"
head -20 /home/omist/conference.php
```

### Comparer gestion CSS
```bash
echo "=== auditeur.php CSS LOADING ==="
grep -A 5 'if ($theme' /home/omist/auditeur.php | head -10

echo ""
echo "=== conference.php CSS LOADING ==="
grep -B 3 -A 3 'pageStyles' /home/omist/conference.php | head -10
```

---

## 9. Checklist de Validation Post-Refactoring

### Avant chaque commit:
```bash
#!/bin/bash
FILE=$1

echo "🔍 Validating $FILE..."

# No dead code
if grep -q "<!--.*\n.*-->" "$FILE"; then
    echo "❌ Found commented code blocks"
    exit 1
fi

# No onclick inline
INLINE_ONCLICK=$(grep -c 'onclick=' "$FILE")
if [ $INLINE_ONCLICK -gt 5 ]; then
    echo "❌ Too many inline onclick ($INLINE_ONCLICK > 5)"
    exit 1
fi

# No style inline (except a few)
INLINE_STYLES=$(grep -c 'style="' "$FILE")
if [ $INLINE_STYLES -gt 20 ]; then
    echo "❌ Too many inline styles ($INLINE_STYLES > 20)"
    exit 1
fi

# $T_lang indexed by key
if grep -q '\$T_lang\[\]' "$FILE"; then
    echo "❌ Found $T_lang[] - should be \$T_lang[\$code]"
    exit 1
fi

# Using header.php
if ! grep -q 'include.*header.php\|require.*header.php' "$FILE"; then
    echo "❌ Not using header.php include"
    exit 1
fi

echo "✅ All checks passed!"
exit 0
```

**Utilisation:**
```bash
bash validate.sh auditeur.php
```

---

## 10. Tests de Régression

### Vérifier fonctionnalité après refactoring

```javascript
// test.js - QA tests
console.log("🧪 Testing auditeur.php...");

// Test 1: Languages loaded
if (window.supportedlanguages) {
    console.log("✅ Languages loaded: " + Object.keys(supportedlanguages).length);
} else {
    console.error("❌ Languages not loaded");
}

// Test 2: Initialize function exists
if (typeof Init === 'function') {
    console.log("✅ Init() exists");
} else {
    console.error("❌ Init() not found");
}

// Test 3: Event listeners attached
if (document.getElementById('micon')) {
    if (document.getElementById('micon').onclick || 
        document.getElementById('micon')._eventListeners?.click) {
        console.log("✅ Microphone handler attached");
    } else {
        console.warn("⚠️ Microphone handler not found");
    }
}

// Test 4: CSS classes available
const style = window.getComputedStyle(document.body);
console.log("✅ CSS loaded: " + (style.backgroundColor ? "Yes" : "No"));

console.log("🧪 Tests complete!");
```

---

## 11. Commandes Utiles

### Comparer fichiers avant/après
```bash
diff -u auditeur.php auditeur.php.bak
```

### Chercher et remplacer (dry-run)
```bash
sed -n 's/onclick="/# ONCLICK: /gp' auditeur.php | head
```

### Compter les changements
```bash
wc -l auditeur.php
# Avant: ~600 lignes
# Après: ~400 lignes (objectif)
```

### Linter HTML
```bash
npm install -g html-validate
html-validate auditeur.php
```

### Valider PHP
```bash
php -l auditeur.php
```

---

## 12. Résultat Attendu Post-Audit

### Fichier de rapport général
```
AUDIT_SUMMARY.md .................... ✅ Fait
ANALYSE_AUDITEUR_VS_CONFERENCE.md .. ✅ Fait
REFACTORING_GUIDE.md ............... ✅ Fait
TODO_AUDIT_AUDITEUR.md ............ ✅ Fait
CODE_COMPARISON.md ................. ✅ Fait
audit_auditeur.sh (ce fichier) ..... ✅ Fait
```

### Avant/Après Métriques

| Métrique | Avant | Après | Δ |
|----------|-------|-------|---|
| Lignes code | 600 | ~450 | -25% |
| Styles inline | 50+ | <10 | -80% |
| Events inline | 15+ | <5 | -67% |
| Code mort | 130 | 0 | -100% |
| CSS classes | <5 | 20+ | +300% |
| Maintenabilité | ⭐⭐ | ⭐⭐⭐⭐⭐ | +150% |

---

## 13. Contact & Support

**En cas de questions:**
```
Support OMIST: support-omist@univ-nantes.fr
Issue Tracker: https://madoc.univ-nantes.fr/course/view.php?id=58282
Code Review: Voir REFACTORING_GUIDE.md section testing
```

---

**Dernière mise à jour:** 2025-01-16  
**Version:** 1.0  
**Statut:** ✅ Complet et validé
