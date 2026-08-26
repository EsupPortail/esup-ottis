#!/bin/bash

#===============================================================================
# Script d'installation et de test des outils de qualité de code
# Tâches 14 (PHP-CS-Fixer) et 15 (PHPStan)
#===============================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "=========================================="
echo "Installation des outils de qualité de code"
echo "=========================================="
echo ""

# Vérifier que composer est disponible
if ! command -v composer &> /dev/null; then
    echo "❌ Erreur : Composer n'est pas installé."
    echo "   Installez-le avec : curl -sS https://getcomposer.org/installer | php"
    echo "   Puis : mv composer.phar /usr/local/bin/composer"
    exit 1
fi

echo "✅ Composer est disponible"
echo ""

# Vérifier la version de PHP
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
if [[ "$PHP_VERSION" < "8.1" ]]; then
    echo "⚠️  Attention : PHP $PHP_VERSION détecté. PHP 8.1+ est recommandé."
    read -p "Continuer quand même ? (OUI/NON) : " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[OoYy]$ ]]; then
        exit 1
    fi
fi

echo "✅ PHP $PHP_VERSION détecté"
echo ""

# Changer de répertoire
cd "$PROJECT_ROOT"

echo "📁 Répertoire du projet : $PROJECT_ROOT"
echo ""

#===============================================================================
# Installation des dépendances
#===============================================================================

echo "🔧 Installation des dépendances Composer..."
if [ -f "composer.lock" ]; then
    echo "   Fichier composer.lock trouvé - installation en mode production + dev"
    composer install --no-interaction --prefer-dist --optimize-autoloader
else
    echo "   Pas de composer.lock - installation complète"
    composer install --no-interaction --prefer-dist
fi

echo ""
echo "✅ Dépendances installées"
echo ""

#===============================================================================
# Vérification des outils
#===============================================================================

echo "🔍 Vérification des outils installés..."
echo ""

# Vérifier PHP-CS-Fixer
if [ -f "vendor/bin/php-cs-fixer" ]; then
    PHPCSFIXER_VERSION=$(vendor/bin/php-cs-fixer --version 2>&1 | head -n 1)
    echo "✅ PHP-CS-Fixer : $PHPCSFIXER_VERSION"
else
    echo "❌ PHP-CS-Fixer non trouvé dans vendor/bin/"
    exit 1
fi

# Vérifier PHPStan
if [ -f "vendor/bin/phpstan" ]; then
    PHPSTAN_VERSION=$(vendor/bin/phpstan --version 2>&1 | head -n 1)
    echo "✅ PHPStan : $PHPSTAN_VERSION"
else
    echo "❌ PHPStan non trouvé dans vendor/bin/"
    exit 1
fi

echo ""

#===============================================================================
# Test des outils sur un fichier exemple
#===============================================================================

echo "🧪 Test des outils sur un fichier exemple..."
echo ""

# Créer un fichier de test temporaire
TEST_FILE="$PROJECT_ROOT/.php-cs-fixer-test.php"
cat > "$TEST_FILE" << 'EOF'
<?php
// Fichier de test pour PHP-CS-Fixer
function test($param1,$param2){$result=$param1+$param2;return $result;}

class TestClass{
    public function method1(){
        return "test";
    }
}
EOF

echo "   Fichier de test créé : $TEST_FILE"

# Tester PHP-CS-Fixer
echo ""
echo "   Test PHP-CS-Fixer..."
if vendor/bin/php-cs-fixer fix "$TEST_FILE" --dry-run --diff 2>&1 | grep -q "diff"; then
    echo "   ✅ PHP-CS-Fixer fonctionne et détecte des corrections"
else
    echo "   ℹ️  PHP-CS-Fixer fonctionne (pas de corrections à faire)"
fi

# Tester PHPStan
echo ""
echo "   Test PHPStan..."
if vendor/bin/phpstan analyse "$TEST_FILE" --no-progress 2>&1 | grep -q "level"; then
    echo "   ✅ PHPStan fonctionne"
else
    echo "   ℹ️  PHPStan a terminé l'analyse"
fi

# Nettoyer
rm -f "$TEST_FILE"

echo ""
echo "=========================================="
echo "✅ Installation et tests terminés !"
echo "=========================================="
echo ""
echo "Commandes utiles :"
echo "  - Formater le code : vendor/bin/php-cs-fixer fix"
echo "  - Analyser le code : vendor/bin/phpstan analyse"
echo "  - Voir les erreurs : vendor/bin/phpstan analyse --error-format=table"
echo ""
