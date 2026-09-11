#!/bin/bash

# Script de validation de la syntaxe des fichiers JavaScript
# A exécuter après les modifications de la Tâche 29

echo "=========================================="
echo "Validation de la syntaxe JavaScript"
echo "=========================================="
echo ""

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Compter les erreurs
ERRORS=0

echo "Vérification des fichiers modifiés..."
echo ""

# Liste des fichiers JavaScript à vérifier
FILES=(
  "js/lib/xhr.js"
  "js/lib/conference/network.js"
  "js/lib/conference/translation.js"
  "js/lib/conference/utils.js"
  "js/lib/conference/config-functions.js"
  "js/lib/conference/speech.js"
  "js/lib/conference/post.js"
  "js/lib/conference/notebook.js"
  "js/lib/conference/pdf-viewer.js"
  "js/lib/conference/navigation.js"
  "js/lib/auditor/network.js"
  "js/lib/auditor/mail.js"
  "js/lib/auditor/translation.js"
  "js/lib/chat/network.js"
  "js/common-func.js"
)

echo "Vérification de l'absence de 'new XMLHttpRequest' direct..."
for file in "${FILES[@]}"; do
  if grep -q "new XMLHttpRequest()" "$file" 2>/dev/null; then
    # Vérifier si c'est dans un commentaire ou une définition de fonction
    if ! grep -q "function createXhrRequest" "$file" && ! grep -B2 "new XMLHttpRequest()" "$file" | grep -q "\/\*\|//\|function"; then
      echo -e "${RED}❌ FAIL${NC}: $file contient 'new XMLHttpRequest()' non commenté"
      ERRORS=$((ERRORS + 1))
    fi
  fi
done

if [ $ERRORS -eq 0 ]; then
  echo -e "${GREEN}✓ PASS${NC}: Aucun 'new XMLHttpRequest()' direct trouvé"
else
  echo -e "${RED}✗ FAIL${NC}: $ERRORS occurrences de 'new XMLHttpRequest()' direct trouvées"
fi

echo ""
echo "Vérification de l'utilisation de createFetchRequest..."
FETCH_COUNT=$(grep -r "createFetchRequest" js/lib --include="*.js" | grep -v ".test.js" | grep -v "function createFetchRequest" | grep -v "return createXhrRequest" | wc -l)
echo "Nombre d'appels à createFetchRequest: $FETCH_COUNT"

if [ $FETCH_COUNT -gt 0 ]; then
  echo -e "${GREEN}✓ PASS${NC}: createFetchRequest est utilisé dans le code"
else
  echo -e "${RED}✗ FAIL${NC}: Aucun appel à createFetchRequest trouvé"
  ERRORS=$((ERRORS + 1))
fi

echo ""
echo "Vérification des imports..."
for file in "${FILES[@]}"; do
  if [ -f "$file" ]; then
    # Vérifier que les fichiers qui utilisent createFetchRequest l'importent ou utilisent window
    if grep -q "createFetchRequest" "$file" && ! grep -q "import.*createFetchRequest" "$file" && ! grep -q "function createFetchRequest" "$file" && ! grep -q "window\.createFetchRequest\|const.*createFetchRequest.*window" "$file"; then
      echo -e "${RED}❌ FAIL${NC}: $file utilise createFetchRequest mais ne l'importe pas"
      ERRORS=$((ERRORS + 1))
    fi
  fi
done

if [ $ERRORS -eq 0 ]; then
  echo -e "${GREEN}✓ PASS${NC}: Tous les imports sont corrects"
fi

echo ""
echo "Vérification des fallbacks pour onUploadProgress..."
FALLBACK_COUNT=$(grep -r "Upload progress tracking requires XMLHttpRequest fallback" js/lib --include="*.js" | wc -l)
echo "Nombre de fallbacks configurés: $FALLBACK_COUNT"

if [ $FALLBACK_COUNT -eq 2 ]; then
  echo -e "${GREEN}✓ PASS${NC}: Les 2 fallbacks pour upload progress sont en place"
else
  echo -e "${YELLOW}⚠ WARNING${NC}: $FALLBACK_COUNT fallbacks trouvés (attendu: 2)"
fi

echo ""
echo "=========================================="
if [ $ERRORS -eq 0 ]; then
  echo -e "${GREEN}✅ TOUTES LES VÉRIFICTIONS ONT RÉUSSI${NC}"
  exit 0
else
  echo -e "${RED}❌ $ERRORS ÉCHECS DÉTECTÉS${NC}"
  exit 1
fi
