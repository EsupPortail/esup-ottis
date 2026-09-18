#===============================================================================
# Makefile - Outils de qualité de code pour le projet OMIST
#===============================================================================

.PHONY: help install check fix lint lint-js lint-php

# Couleurs pour l'affichage
GREEN := \033[0;32m
YELLOW := \033[1;33m
NC := \033[0m

# Binaire PHP-CS-Fixer
PHPCS := vendor/bin/php-cs-fixer

# Binaire PHPStan
PHPSTAN := vendor/bin/phpstan

# Binaire PHPUnit (si disponible)
PHPUNIT := vendor/bin/phpunit

# Binaire ESLint (si disponible)
ESLINT := node_modules/.bin/eslint

# Binaire Prettier (si disponible)
PRETTIER := node_modules/.bin/prettier


help: ## Afficher l'aide
	@echo "Utilisation : make [commande]"
	@echo ""
	@echo "Commandes disponibles :"
	@echo ""
	@echo "  install           - Installer les dépendances Composer"
	@echo "  check             - Vérifier la qualité du code (PHP + JS)"
	@echo "  fix               - Corriger automatiquement le code (PHP + JS)"
	@echo ""
	@echo "  check-php         - Vérifier le code PHP (PHPStan + PHP-CS-Fixer + PHPUnit)"
	@echo "  fix-php           - Corriger automatiquement le code PHP"
	@echo "  test-php          - Exécuter les tests PHP avec PHPUnit"
	@echo ""
	@echo "  check-js          - Vérifier le code JavaScript (ESLint)"
	@echo "  fix-js            - Corriger automatiquement le code JavaScript"
	@echo "  check-format-js   - Vérifier le format JS (Prettier)"
	@echo "  fix-format-js     - Formater le code JS (Prettier)"
	@echo ""
	@echo "  tools             - Installer les outils de qualité"
	@echo "  clean             - Nettoyer les fichiers temporaires"


install: ## Installer les dépendances Composer
	@echo "$(YELLOW)Installation des dépendances Composer...$(NC)"
	composer install --no-interaction --prefer-dist --optimize-autoloader


tools: install ## Installer et tester les outils de qualité
	@echo "$(YELLOW)Installation et test des outils de qualité...$(NC)"
	./tools/install-quality-tools.sh


#===============================================================================
# Tâches PHP
#===============================================================================

check-php: ## Vérifier le code PHP (analyse statique + format + tests)
	@echo "$(YELLOW)Analyse du code PHP avec PHPStan...$(NC)"
	$(PHPSTAN) analyse --error-format=table

	@echo ""
	@echo "$(YELLOW)Vérification du format avec PHP-CS-Fixer...$(NC)"
	$(PHPCS) fix --dry-run --diff --stop-on-violation

	@echo ""
	@echo "$(YELLOW)Exécution des tests PHP avec PHPUnit...$(NC)"
	LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libstdc++.so.6 $(PHPUNIT)


fix-php: ## Corriger automatiquement le code PHP
	@echo "$(YELLOW)Correction automatique du code PHP...$(NC)"
	$(PHPCS) fix


test-php: ## Exécuter les tests PHP avec PHPUnit
	@echo "$(YELLOW)Exécution des tests PHP avec PHPUnit...$(NC)"
	LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libstdc++.so.6 $(PHPUNIT)


#===============================================================================
# Tâches JavaScript
#===============================================================================

check-js: ## Vérifier le code JavaScript (ESLint)
	@echo "$(YELLOW)Analyse du code JavaScript avec ESLint...$(NC)"
	$(ESLINT) . --ext .js


fix-js: ## Corriger automatiquement le code JavaScript
	@echo "$(YELLOW)Correction automatique du code JavaScript...$(NC)"
	$(ESLINT) . --ext .js --fix


check-format-js: ## Vérifier le format JavaScript (Prettier)
	@echo "$(YELLOW)Vérification du format JavaScript avec Prettier...$(NC)"
	$(PRETTIER) --check "js/**/*.js" "*.js"


fix-format-js: ## Formater le code JavaScript (Prettier)
	@echo "$(YELLOW)Formatage du code JavaScript avec Prettier...$(NC)"
	$(PRETTIER) --write "js/**/*.js" "*.js"


#===============================================================================
# Tâches combinées
#===============================================================================

check: check-php check-js check-format-js ## Vérifier toute la qualité du code

fix: fix-php fix-js fix-format-js ## Corriger automatiquement tout le code


#===============================================================================
# Nettoyage
#===============================================================================

clean: ## Nettoyer les fichiers temporaires
	@echo "$(YELLOW)Nettoyage des fichiers temporaires...$(NC)"
	rm -rf vendor/.cache
	rm -rf .php-cs-fixer.cache
	rm -f .php-cs-fixer-test.php


#===============================================================================
# Cibles par défaut
#===============================================================================

.DEFAULT_GOAL := help
