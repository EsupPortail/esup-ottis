#!/bin/bash

#===============================================================================
# Script de détection de code commenté et code mort
# Tâche 16 : Supprimer tout le code commenté (code mort)
#===============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fichier de rapport
REPORT_FILE="$PROJECT_ROOT/audit/rapport-dead-code-$(date +%Y%m%d-%H%M%S).md"

# Compteurs
TOTAL_FILES=0
FILE_WITH_COMMENTS=0
TOTAL_COMMENTED_LINES=0
TOTAL_DEAD_CODE_BLOCKS=0

# Créer le rapport
cat > "$REPORT_FILE" << 'EOF'
# 📊 Rapport de Détection de Code Mort et Commenté
**Date :** $(date +"%Y-%m-%d %H:%M:%S")  
**Tâche :** 16 - Supprimer tout le code commenté (code mort)  
**Projet :** OMIST Automatic Translator

---

## 📈 Résumé

| Métrique | Valeur |
|----------|--------|
| Fichiers analysés | 0 |
| Fichiers avec code commenté | 0 |
| Lignes de code commenté | 0 |
| Blocs de code mort (if(false), etc.) | 0 |

---

## 📁 Résultats par fichier

EOF

echo ""
echo "🔍 Détection du code commenté et du code mort"
echo "============================================"
echo ""
echo "📁 Répertoire : $PROJECT_ROOT"
echo "📄 Rapport : $REPORT_FILE"
echo ""

# Fonction pour ajouter une entrée au rapport
add_to_report() {
    local file="$1"
    local line="$2"
    local type="$3"
    echo "  - 📄 $file:$line ($type)" >> "$REPORT_FILE"
}

# Fonction pour analyser un fichier
analyze_file() {
    local file="$1"
    local file_type="$2"
    
    TOTAL_FILES=$((TOTAL_FILES + 1))
    
    # Compter les lignes de commentaires simples //
    local single_comments=$(grep -n "^\s*//" "$file" 2>/dev/null | wc -l)
    
    # Compter les blocs de commentaires /* */
    local block_comments=$(grep -n "/\*" "$file" 2>/dev/null | wc -l)
    
    # Compter les blocs if(false)
    local dead_blocks=$(grep -n "if\s*(\s*false\s*)" "$file" 2>/dev/null | wc -l)
    
    # Compter les fonctions commentées
    local commented_functions=$(grep -n "//\s*function\|/\*.*function" "$file" 2>/dev/null | wc -l)
    
    local total_commented=$((single_comments + block_comments + commented_functions))
    
    if [ "$total_commented" -gt 0 ] || [ "$dead_blocks" -gt 0 ]; then
        FILE_WITH_COMMENTS=$((FILE_WITH_COMMENTS + 1))
        TOTAL_COMMENTED_LINES=$((TOTAL_COMMENTED_LINES + total_commented))
        TOTAL_DEAD_CODE_BLOCKS=$((TOTAL_DEAD_CODE_BLOCKS + dead_blocks))
        
        echo "⚠️  $file_type: $file"
        echo "   - Lignes commentées : $total_commented"
        echo "   - Blocs if(false) : $dead_blocks"
        
        # Ajouter les détails au rapport
        echo "" >> "$REPORT_FILE"
        echo "### 📄 $file" >> "$REPORT_FILE"
        echo "**Type :** $file_type  " >> "$REPORT_FILE"
        echo "**Lignes commentées :** $total_commented  " >> "$REPORT_FILE"
        echo "**Blocs if(false) :** $dead_blocks  " >> "$REPORT_FILE"
        echo "" >> "$REPORT_FILE"
        
        # Afficher quelques exemples
        if [ "$single_comments" -gt 0 ]; then
            echo "   Exemples de commentaires simples :"
            grep -n "^\s*//" "$file" | head -3 | while read -r line; do
                echo "      $line"
                add_to_report "$file" "$line" "Commentaire simple"
            done
        fi
        
        if [ "$block_comments" -gt 0 ]; then
            echo "   Exemples de blocs de commentaires :"
            grep -n "/\*" "$file" | head -3 | while read -r line; do
                echo "      $line"
                add_to_report "$file" "$line" "Bloc de commentaire"
            done
        fi
        
        if [ "$dead_blocks" -gt 0 ]; then
            echo "   Exemples de code mort :"
            grep -n "if\s*(\s*false\s*)" "$file" | head -3 | while read -r line; do
                echo "      $line"
                add_to_report "$file" "$line" "Code mort (if false)"
            done
        fi
        
        echo ""
    fi
}

echo "🔎 Analyse en cours..."
echo ""

# Analyser les fichiers PHP
echo "${YELLOW}📄 Analyse des fichiers PHP...${NC}"
echo ""

# Exclure les dossiers vendor, node_modules, tmp, uploads, audit
while IFS= read -r -d '' file; do
    analyze_file "$file" "PHP"
done < <(find "$PROJECT_ROOT" -type f -name "*.php" \
    ! -path "*/vendor/*" \
    ! -path "*/node_modules/*" \
    ! -path "*/tmp/*" \
    ! -path "*/uploads/*" \
    ! -path "*/.git/*" \
    -print0 2>/dev/null)

echo ""
echo "${YELLOW}📄 Analyse des fichiers JavaScript...${NC}"
echo ""

# Analyser les fichiers JavaScript
while IFS= read -r -d '' file; do
    analyze_file "$file" "JavaScript"
done < <(find "$PROJECT_ROOT" -type f \( -name "*.js" -o -name "*.jsx" \) \
    ! -path "*/node_modules/*" \
    ! -path "*/vendor/*" \
    ! -path "*/tmp/*" \
    ! -path "*/uploads/*" \
    ! -path "*/.git/*" \
    -print0 2>/dev/null)

# Mettre à jour le résumé dans le rapport
sed -i "s/Fichiers analysés | 0 /Fichiers analysés | $TOTAL_FILES /" "$REPORT_FILE"
sed -i "s/Fichiers avec code commenté | 0 /Fichiers avec code commenté | $FILE_WITH_COMMENTS /" "$REPORT_FILE"
sed -i "s/Lignes de code commenté | 0 /Lignes de code commenté | $TOTAL_COMMENTED_LINES /" "$REPORT_FILE"
sed -i "s/Blocs de code mort .*| 0 /Blocs de code mort (if(false), etc.) | $TOTAL_DEAD_CODE_BLOCKS /" "$REPORT_FILE"

# Ajouter un résumé final au rapport
cat >> "$REPORT_FILE" << EOF

---

## 🎯 Recommandations

1. **Priorité Haute** : Supprimer le code commenté dans les fichiers avec le plus de lignes
2. **Priorité Moyenne** : Vérifier les blocs if(false) pour confirmation
3. **À faire** : Exécuter ce script après chaque modification majeure

---

## 📌 Prochaines étapes

- [ ] Passer en revue chaque fichier identifié
- [ ] Supprimer le code commenté inutiles
- [ ] Vérifier que l'application fonctionne après suppression
- [ ] Commiter les modifications avec le message : "Tâche 16 : Suppression du code commenté"

---

**Généré par :** "`basename "$0"`"  
**Date :** $(date +"%Y-%m-%d %H:%M:%S")
EOF

echo ""
echo "============================================"
echo "✅ Analyse terminée !"
echo "============================================"
echo ""
echo "📊 Statistiques :"
echo "   - Fichiers analysés : $TOTAL_FILES"
echo "   - Fichiers avec code commenté : $FILE_WITH_COMMENTS"
echo "   - Lignes de code commenté : $TOTAL_COMMENTED_LINES"
echo "   - Blocs de code mort : $TOTAL_DEAD_CODE_BLOCKS"
echo ""
echo "📄 Rapport généré : $REPORT_FILE"
echo ""
echo "Pour ouvrir le rapport :"
echo "   less $REPORT_FILE"
echo "   ou"
echo "   cat $REPORT_FILE"
echo ""
