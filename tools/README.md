# Outils de Qualité de Code - Projet OMIST

Ce dossier contient les scripts et configurations pour les outils de qualité de code du projet.

## 📋 Outils Configurés

### PHP
| Outil | Version | Configuration | Statut |
|-------|---------|---------------|--------|
| PHP-CS-Fixer | ^3.13 | `.php-cs-fixer.dist.php` | ✅ Configuré |
| PHPStan | ^1.10 | `phpstan.neon` | ✅ Configuré (niveau 5) |

### JavaScript
| Outil | Version | Configuration | Statut |
|-------|---------|---------------|--------|
| ESLint | - | `.eslintrc.json` | ✅ Configuré |
| Prettier | - | `.prettierrc.json` | ✅ Configuré |

## 🚀 Installation

### 1. Installer les dépendances PHP

```bash
# Depuis la racine du projet
composer install --dev

# Ou avec Make
make install
```

### 2. Installer les dépendances JavaScript

```bash
npm install
```

### 3. Installer et tester tous les outils

```bash
./tools/install-quality-tools.sh
```

## 🔧 Utilisation

### Avec Make (recommandé)

```bash
# Voir l'aide
make help

# Vérifier la qualité du code
make check

# Corriger automatiquement le code
make fix

# Vérifier seulement le PHP
make check-php

# Corriger seulement le PHP
make fix-php

# Vérifier seulement le JavaScript
make check-js

# Corriger seulement le JavaScript
make fix-js
```

### Commandes directes

#### PHP-CS-Fixer

```bash
# Vérifier le formatage (sans modifier)
vendor/bin/php-cs-fixer fix --dry-run --diff

# Corriger le formatage
vendor/bin/php-cs-fixer fix

# Corriger un fichier spécifique
vendor/bin/php-cs-fixer fix path/to/file.php
```

#### PHPStan

```bash
# Analyser tout le code
vendor/bin/phpstan analyse

# Analyser avec format tableau
vendor/bin/phpstan analyse --error-format=table

# Analyser un fichier spécifique
vendor/bin/phpstan analyse path/to/file.php
```

#### ESLint

```bash
# Vérifier tout le JavaScript
node_modules/.bin/eslint . --ext .js

# Corriger automatiquement
node_modules/.bin/eslint . --ext .js --fix
```

#### Prettier

```bash
# Vérifier le formatage
node_modules/.bin/prettier --check "js/**/*.js" "*.js"

# Formater tout le code
node_modules/.bin/prettier --write "js/**/*.js" "*.js"
```

## 📝 Configurations

### PHP-CS-Fixer (`.php-cs-fixer.dist.php`)

- **Standard**: PSR-12
- **Règles supplémentaires**:
  - `array_syntax`: Syntaxe courte pour les tableaux `[]`
  - `no_unused_imports`: Suppression des imports inutilisés
  - `ordered_imports`: Tri alphabétique des imports
  - `single_quote`: Utilisation des guillemets simples
  - `blank_line_before_statement`: Ligne vide avant les instructions de contrôle

- **Exclusions**:
  - `vendor/`
  - `node_modules/`
  - `tmp/`
  - `uploads/`
  - Fichiers legacy dans `Security/`

### PHPStan (`phpstan.neon`)

- **Niveau**: 5 (stricte)
- **Includes**: `bleedingEdge.neon`
- **Paramètres**:
  - `checkMissingIterableValueType: false`
  - `inferPrivatePropertyTypeFromConstructor: true`
  - `allowDynamicProperties: false`
  - Vérification stricte des noms de fonctions/méthodes

- **Exclusions**: Même liste que PHP-CS-Fixer

### ESLint (`.eslintrc.json`)

Configuration existante pour la qualité JavaScript.

### Prettier (`.prettierrc.json`)

Configuration existante pour le formatage JavaScript.

## 🎯 Intégration CI/CD

Pour intégrer ces outils dans votre pipeline CI/CD, ajoutez ces étapes :

```yaml
# Exemple pour GitHub Actions
name: Quality Checks

on: [push, pull_request]

jobs:
  php-quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer
      
      - name: Install dependencies
        run: composer install --no-interaction
      
      - name: Run PHP-CS-Fixer
        run: vendor/bin/php-cs-fixer fix --dry-run --diff
      
      - name: Run PHPStan
        run: vendor/bin/phpstan analyse --error-format=github

  js-quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      
      - name: Install dependencies
        run: npm install
      
      - name: Run ESLint
        run: node_modules/.bin/eslint . --ext .js
      
      - name: Run Prettier
        run: node_modules/.bin/prettier --check "js/**/*.js" "*.js"
```

## 📚 Tâches Associées

| Tâche | Description | Statut |
|-------|-------------|--------|
| 14 | Configurer PHP-CS-Fixer | ✅ Terminé |
| 15 | Configurer PHPStan (niveau 5) | ✅ Terminé |
| 20 | Configurer ESLint | ⏳ En attente |
| 21 | Configurer Prettier | ⏳ En attente |

## 🔗 Liens Utiles

- [PHP-CS-Fixer Documentation](https://cs.symfony.com/)
- [PHPStan Documentation](https://phpstan.org/)
- [ESLint Documentation](https://eslint.org/)
- [Prettier Documentation](https://prettier.io/)
