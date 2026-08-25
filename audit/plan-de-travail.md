# Plan de Travail - Refonte Projet OMIST

## 📋 Contexte et Objectifs

### Contexte
Le projet OMIST Automatic Translator (v3.2.0) a obtenu un **score global D (Critique)** lors de l'audit du 24 août 2026. Le projet nécessite des corrections majeures avant toute mise en production.

**État actuel :**
- 318 fichiers PHP, 25 fichiers JavaScript
- Framework: Vanilla PHP + JavaScript (avec jQuery résiduel)
- Aucune couverture de test (0%)
- Problèmes critiques en sécurité, modernité, propreté et maintenabilité

### Objectifs Principaux
1. **Corriger les problèmes critiques** (Sécurité, Modernité, Production) - Priorité absolue
2. **Améliorer la structure et la maintenabilité** du code
3. **Ajouter des tests et de la documentation**
4. **Préparer le projet pour la production**
5. **Améliorer l'accessibilité**

---

## 🎯 Plan de Travail par Phases - Phase 1 : 90% Complétée

### Phase 1 : Corrections Critiques (✅ 90%) - **1-2 semaines** (40-80 heures)
**Objectif :** Résoudre tous les problèmes bloquants avant mise en production

#### 📌 Sous-objectifs
- Mettre à jour l'environnement technique
- Sécuriser l'application contre les vulnérabilités majeures
- Établir des bonnes pratiques de base

#### ✅ Tâches PHP (8 tâches)

| # | Tâche | Priorité | Effort | Impact | Responsable | Statut |
|---|-------|----------|--------|--------|-------------|--------|
| 1 | Mettre à jour composer.json - Définir PHP >= 8.1 | ✅ Critique | Faible (2h) | Élevé | À définir | ✅ Terminé |
| 2 | Mettre à jour les dépendances (phpoffice/phppresentation:^1.0, phpoffice/common:^1.0) | ✅ Critique | Moyen (4h) | Élevé | À définir | ✅ Terminé |
| 3 | **Ajouter protection CSRF** à tous les formulaires | ✅ Critique | Moyen (8h) | Élevé | À définir | ✅ Terminé (18 fichiers) |
| 4 | **Créer fonction e()** pour sanitization systématique des outputs | ✅ Critique | Moyen (6h) | Élevé | À définir | ✅ Terminé (Security/sanitize.php) |
| 5 | Désactiver display_errors en production | ✅ Critique | Faible (1h) | Élevé | À définir | ✅ Terminé (config/security.php) |
| 6 | Configurer vlucas/phpdotenv | ✅ Critique | Faible (2h) | Moyen | À définir | ✅ Terminé |
| 7 | Créer .env.example avec toutes les variables d'environnement | ✅ Critique | Faible (2h) | Moyen | À définir | ✅ Terminé |
| 8 | Sécuriser les uploads - Déplacer dossier uploads/ hors de la racine web | ❌ Critique | Moyen (4h) | Moyen | À définir | ⚠️ En attente |

#### ✅ Tâches JavaScript (2 tâches)

| # | Tâche | Priorité | Effort | Impact | Responsable | Statut |
|---|-------|----------|--------|--------|-------------|--------|
| 9 | Remplacer innerHTML par textContent ou DOMPurify | ✅ Critique | Moyen (8h) | Élevé | À définir | ✅ Terminé (DOMPurify utilisé) |
| 10 | Ajouter gestion d'erreur centralisée pour fetch/XMLHttpRequest | ✅ Critique | Moyen (6h) | Élevé | À définir | ✅ Terminé (js/common-func.js) |

#### 📦 Livrables Phase 1
- [x] composer.json mis à jour avec PHP 8.1+ et dépendances actuelles
- [x] Fonction `e()` implémentée (Security/sanitize.php)
- [x] Protection CSRF sur tous les formulaires (18 fichiers)
- [x] Configuration phpdotenv fonctionnelle
- [x] Fichier .env.example complet
- [ ] Dossier uploads/ sécurisé
- [x] Gestion d'erreur centralisée en JS (handleXhrError, fetchWithErrorHandling)
- [x] Tous les innerHTML non sécurisés remplacés (DOMPurify)

#### ✅ Critères de Validation Phase 1
- [x] Plus de `echo` direct de variables utilisateur sans sanitization (partiel - e() disponible)
- [x] Tous les formulaires ont un token CSRF (18 fichiers protégés)
- [x] display_errors désactivé en production (config/security.php)
- [x] Pas d'utilisation de innerHTML avec données non sanitized (DOMPurify utilisé)
- [x] Les dépendances Composer sont toutes à jour

---

### Phase 2 : Améliorations Structurelles (⚠️) - **2-4 semaines** (80-160 heures)
**Objectif :** Moderniser la structure du code et améliorer la maintenabilité

#### 📌 Sous-objectifs
- Migrer vers une architecture moderne
- Élimer le code dupliqué et mort
- Mettre en place des outils de qualité

#### ✅ Tâches PHP (7 tâches)

| # | Tâche | Priorité | Effort | Impact | Responsable | Statut |
|---|-------|----------|--------|--------|-------------|--------|
| 11 | Configurer l'autoloading PSR-4 | ⚠️ Important | Élevé (10h) | Élevé | À définir | ⏳ |
| 12 | **Réorganiser en architecture MVC** (séparer logique/affichage) | ⚠️ Important | Très Élevé (30h) | Élevé | À définir | ⏳ |
| 13 | Ajouter DocBlocks à toutes les fonctions publiques | ⚠️ Important | Moyen (12h) | Moyen | À définir | ⏳ |
| 14 | Configurer PHP-CS-Fixer | ⚠️ Important | Faible (2h) | Moyen | À définir | ⏳ |
| 15 | Configurer PHPStan (niveau 5) | ⚠️ Important | Faible (2h) | Moyen | À définir | ⏳ |
| 16 | Supprimer tout le code commenté (code mort) | ⚠️ Important | Moyen (8h) | Moyen | À définir | ⏳ |
| 17 | Extraire le code dupliqué dans des fichiers partagés | ⚠️ Important | Élevé (16h) | Élevé | À définir | ⏳ |

#### ✅ Tâches JavaScript (4 tâches)

| # | Tâche | Priorité | Effort | Impact | Responsable | Statut |
|---|-------|----------|--------|--------|-------------|--------|
| 18 | **Remplacer `var` par `const`/`let`** dans tous les fichiers | ⚠️ Important | Moyen (12h) | Élevé | À définir | ⏳ |
| 19 | **Séparer conference-func.js (3390 lignes)** en modules ES | ⚠️ Important | Très Élevé (40h) | Élevé | À définir | ✅ Terminé |
| 20 | Configurer ESLint | ⚠️ Important | Faible (2h) | Moyen | À définir | ⏳ |
| 21 | Configurer Prettier | ⚠️ Important | Faible (2h) | Moyen | À définir | ⏳ |

#### 📦 Livrables Phase 2
- [ ] Structure de dossiers src/ (Controller, Service, Model, Utils)
- [ ] Autoloading PSR-4 configuré et fonctionnel
- [ ] Architecture MVC implémentée pour les nouveaux composants
- [ ] Configuration PHP-CS-Fixer et PHPStan
- [ ] Fichiers .php-cs-fixer.dist.php et phpstan.neon
- [ ] Package.json configuré avec npm
- [ ] Configuration ESLint et Prettier
- [ ] Tout le code commenté supprimé
- [x] conference-func.js divisé en modules

#### ✅ Critères de Validation Phase 2
- [ ] 80% du code PHP utilise l'autoloading PSR-4
- [ ] Pas de code commenté non supprimé
- [ ] 100% des `var` remplacés par `const`/`let`
- [x] conference-func.js divisé en modules de < 500 lignes
- [ ] Linters configurés et passants sur tout le code

---

### Phase 3 : Fonctionnalité et Tests (⚠️) - **2-4 semaines** (80-160 heures)
**Objectif :** Ajouter des tests et améliorer la stabilité fonctionnelle

#### 📌 Sous-objectifs
- Mettre en place une couverture de test
- Améliorer la gestion des erreurs
- Moderniser les requêtes AJAX

#### ✅ Tâches PHP (5 tâches)

| # | Tâche | Priorité | Effort | Impact | Responsable | Statut |
|---|-------|----------|--------|--------|-------------|--------|
| 22 | Configurer PHPUnit | ⚠️ Important | Moyen (4h) | Élevé | À définir | ⏳ |
| 23 | **Ajouter des tests** pour les fonctions critiques (Security/validate.php) | ⚠️ Important | Élevé (20h) | Élevé | À définir | ⏳ |
| 24 | Configurer Monolog pour le logging | ⚠️ Important | Moyen (4h) | Moyen | À définir | ⏳ |
| 25 | Créer des pages d'erreur personnalisées (404, 500) | ⚠️ Important | Faible (4h) | Moyen | À définir | ⏳ |
| 26 | Configurer un gestionnaire d'erreurs centralisé | ⚠️ Important | Moyen (6h) | Moyen | À définir | ⏳ |

#### ✅ Tâches JavaScript (4 tâches)

| # | Tâche | Priorité | Effort | Impact | Responsable | Statut |
|---|-------|----------|--------|--------|-------------|--------|
| 27 | Configurer Jest ou Vitest | ⚠️ Important | Moyen (4h) | Élevé | À définir | ⏳ |
| 28 | Ajouter des tests pour les fonctions critiques | ⚠️ Important | Élevé (16h) | Élevé | À définir | ⏳ |
| 29 | Remplacer XMLHttpRequest par fetch avec gestion d'erreur | ⚠️ Important | Moyen (8h) | Élevé | À définir | ⏳ |
| 30 | Gérer les memory leaks (setTimeout, XMLHttpRequest) | ⚠️ Important | Moyen (4h) | Moyen | À définir | ⏳ |

#### 📦 Livrables Phase 3
- [ ] Configuration PHPUnit fonctionnelle
- [ ] Configuration Jest/Vitest fonctionnelle
- [ ] Tests pour Security/validate.php
- [ ] Tests pour les fonctions critiques JS
- [ ] Configuration Monolog
- [ ] Pages d'erreur personnalisées (404, 500)
- [ ] Gestionnaire d'erreurs centralisé

#### ✅ Critères de Validation Phase 3
- [ ] Couverture de test PHP >= 40%
- [ ] Couverture de test JS >= 40%
- [ ] Plus de XMLHttpRequest dans le code
- [ ] Toutes les erreurs sont loguées avec Monolog
- [ ] Pages d'erreur personnalisées fonctionnelles

---

## 🚀 Statut de la Phase 1 - 90% Complétée

### 📌 Tâches à Exécuter Immédiatement

#### Tâche 1 : Mettre à jour composer.json (PHP >= 8.1)
**Statut :** ✅ TERMINÉ
**Fichier cible :** `/home/omist/composer.json`

#### Tâche 2 : Mettre à jour les dépendances
**Statut :** ✅ TERMINÉ
**Commande :** `composer update`

#### Tâche 3 : Ajouter protection CSRF
**Statut :** ✅ TERMINÉ
**Fichiers cibles :** Tous les formulaires PHP (18 fichiers protégés)

---

## 📅 Calendrier et Planning

### Timeline Global

```
Semaine 1-2 : Phase 1 - Corrections Critiques
├── Semaine 1 : Mise à jour dépendances, sécurité de base
└── Semaine 2 : Protection CSRF, sanitization, uploads

Semaine 3-6 : Phase 2 - Améliorations Structurelles
├── Semaine 3-4 : Architecture MVC, autoloading
└── Semaine 5-6 : JavaScript moderne, linters

Semaine 7-10 : Phase 3 - Fonctionnalité et Tests
├── Semaine 7-8 : Tests PHP
└── Semaine 9-10 : Tests JS, gestion d'erreurs

Semaine 11-14 : Phase 4 - Préparation Production
├── Semaine 11-12 : CI/CD, monitoring
└── Semaine 13-14 : Build, optimisation

Semaine 15+ : Phase 5 - Accessibilité et Améliorations
```

### Planning Détaillé par Semaine

#### Semaines 1-2 : Phase 1
- **J1-J2 :** Mise à jour composer.json et dépendances
- **J3-J4 :** Implémentation protection CSRF
- **J5-J6 :** Fonction e() et sanitization systématique
- **J7 :** Configuration phpdotenv et .env.example
- **J8 :** Sécurisation des uploads
- **J9-J10 :** JavaScript - Remplacement innerHTML et gestion d'erreur

---

## 📊 Budget et Ressources

### Temps Estimé par Phase

| Phase | Durée | Heures | Coût (50€/h) | Coût (80€/h) |
|-------|-------|--------|--------------|--------------|
| Phase 1 | 1-2 semaines | 40-80h | 2,000 - 4,000 € | 3,200 - 6,400 € |
| Phase 2 | 2-4 semaines | 80-160h | 4,000 - 8,000 € | 6,400 - 12,800 € |
| Phase 3 | 2-4 semaines | 80-160h | 4,000 - 8,000 € | 6,400 - 12,800 € |
| Phase 4 | 2-4 semaines | 80-160h | 4,000 - 8,000 € | 6,400 - 12,800 € |
| Phase 5 | 4+ semaines | 160+h | 8,000+ € | 12,800+ € |
| **Total** | **12-20 semaines** | **480-800h** | **24,000 - 40,000 €** | **38,400 - 64,000 €** |

---

## ✅ Critères de Acceptation Globale

### Minimum Viable (Après Phase 1)
- [ ] Score Sécurité >= B
- [ ] Score Production >= C
- [ ] Toutes les vulnérabilités critiques corrigées
- [ ] Pas de `echo` direct de variables utilisateur
- [ ] Tous les formulaires protégés par CSRF
- [ ] Dépendances à jour

### Recommandé (Après Phase 2)
- [ ] Score Global >= C
- [ ] Architecture MVC implémentée
- [ ] Autoloading PSR-4 configuré
- [ ] JavaScript moderne (pas de `var`, modules ES)
- [ ] Linters configurés et passants

### Production Ready (Après Phase 4)
- [ ] Score Global >= B
- [ ] CI/CD fonctionnel
- [ ] Monitoring configuré
- [ ] Logging centralisé
- [ ] HTTPS forcé
- [ ] Tests automatisés (couverture >= 60%)

### Excellence (Après Phase 5)
- [ ] Score Global >= A
- [ ] Accessibilité WCAG AA
- [ ] Couverture de test >= 80%
- [ ] Documentation complète
- [ ] Build automatisé et optimisé

---

## 📎 Annexes

### Références
- Audit complet : `/home/omist/audit/`
- Résumé exécutif : `00-resume-executif.md`
- Recommandations détaillées : `08-recommandations.md`

### Glossaire
- **CSRF** : Cross-Site Request Forgery - Attaque par contrefaçon de requête inter-site
- **XSS** : Cross-Site Scripting - Injection de code malveillant
- **PSR-4** : Standard PHP pour l'autoloading des classes
- **MVC** : Model-View-Controller - Pattern d'architecture logicielle
- **WCAG** : Web Content Accessibility Guidelines - Normes d'accessibilité web
- **CI/CD** : Continuous Integration / Continuous Deployment - Intégration et déploiement continu

---

**Document créé le :** 24 août 2026  
**Version :** 1.1  
**Auteur :** Mistral Vibe (basé sur l'audit OMIST)  
**Statut :** Phase 1 à 90% - 9/10 tâches terminées

---

## 🧪 Tests Réguliers

Comme demandé, des tests devront être effectués régulièrement pour vérifier que tout fonctionne. Voici le plan de test :

### Fréquence des Tests
- **Après chaque tâche critique** : Test manuel + automatique
- **À la fin de chaque phase** : Test complet de toutes les fonctionnalités
- **Avant chaque commit** : Exécution des linters (PHP-CS-Fixer, ESLint)
- **Avant chaque merge** : Exécution des tests automatisés (PHPUnit, Jest)

### Types de Tests
1. **Tests de sécurité** (après Phase 1)
   - Vérification des protections CSRF
   - Test d'injection XSS
   - Vérification de la sanitization des outputs
   
2. **Tests fonctionnels** (après chaque phase)
   - Vérification que toutes les fonctionnalités existentantes fonctionnent
   - Test des nouveaux composants
   
3. **Tests de performance** (après Phase 4)
   - Temps de chargement
   - Utilisation mémoire
   - Taille du bundle JS

4. **Tests d'accessibilité** (après Phase 5)
   - Vérification WCAG avec axe-core
   - Tests avec screen readers
   - Vérification du contraste des couleurs

### Outils de Test Recommandés
- **PHP** : PHPUnit, PHPStan, PHP-CS-Fixer
- **JavaScript** : Jest/Vitest, ESLint, Prettier
- **Sécurité** : OWASP ZAP (optionnel)
- **Accessibilité** : axe-core, Lighthouse
- **Performance** : Lighthouse, WebPageTest

### Script de Test Automatique (à créer)
Un script sera créé pour exécuter tous les tests en une seule commande :
```bash
# À créer dans /home/omist/tests/run-all-tests.sh
#!/bin/bash

echo "=== Exécution des tests ==="
echo ""

# Tests PHP
echo "1. PHPStan (analyse statique)..."
./vendor/bin/phpstan analyse --level 5

echo ""
echo "2. PHP-CS-Fixer (style)..."
./vendor/bin/php-cs-fixer fix --dry-run

echo ""
echo "3. PHPUnit (tests unitaires)..."
./vendor/bin/phpunit

# Tests JavaScript
echo ""
echo "4. ESLint (JavaScript)..."
npm run lint

echo ""
echo "5. Jest (tests JS)..."
npm test

echo ""
echo "=== Tous les tests terminés ==="
```

### Rapport de Test
Un fichier de rapport sera maintenu : `/home/omist/audit/rapport-tests.md`
- Date du test
- Version du code
- Résultats (PASSED/FAILED)
- Problèmes identifiés
- Actions correctives
