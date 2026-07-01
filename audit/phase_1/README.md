# **Phase 1 - Résumé Exécutif**

## **📌 CONTEXTE**

Ce dossier contient tous les documents relatifs à la **Phase 1** des corrections de sécurité critiques pour les applications **Conference** et **Auditeur**.

## **📁 STRUCTURE**

```
audit/
├── etape_2026-06-24.md          # Plan d'implémentation complet
├── phase_1/
│   └── README.md                 # Ce fichier
```

## **🎯 OBJECTIFS DE LA PHASE 1**

- **Durée:** 1-2 jours
- **Priorité:** 🔴 CRITIQUE
- **Objectif principal:** Éliminer toutes les vulnérabilités de sécurité critiques

### **Vulnérabilités à corriger (7 au total):**

| ID | Type | Fichiers | Statut |
|----|------|---------|--------|
| SEC-001 | XSS Stored | savetext.php | ⬜ |
| SEC-002 | Path Traversal | readtext.php, saveimage.php, etc. | ⬜ |
| SEC-003 | Validation ROOMID manquante | Tous les fichiers tmp/ | ⬜ |
| SEC-004 | Upload de fichiers non sécurisé | saveimage.php | ⬜ |
| SEC-005 | Pas de protection CSRF | savetext.php, saveimage.php, etc. | ⬜ |
| SEC-006 | display_errors activé en production | conference.php | ⬜ |
| SEC-010 | Tokens API exposés dans les logs | Translate.php | ⬜ |

### **Bugs à corriger (2 au total):**

| ID | Type | Fichiers | Statut |
|----|------|---------|--------|
| FUNC-001 | Race condition | savetext.php | ⬜ |
| FUNC-002 | Deadlock | conference-func.js, auditeur-func.js | ⬜ |

## **📋 FICHIERS À CRÉER**

1. **`Security/validate.php`** - Fonctions de validation centralisées
2. **`Security/CSRF.php`** - Protection CSRF
3. **`config/security.php`** - Configuration de sécurité centralisée

## **📝 FICHIERS À MODIFIER**

### **Backend PHP:**
- `savetext.php` - Sanitization + validation + file_lock
- `readtext.php` - Validation ROOMID + sécurité Path Traversal
- `saveimage.php` - Validation fichier + type MIME + taille
- `synchronize.php` - Validation pincode
- `Translate.php` - Validation ROOMID + masquage tokens
- `conference.php` - Désactiver display_errors en production

### **Frontend JavaScript:**
- `conference-func.js` - Corriger deadlock
- `auditeur-func.js` - Corriger deadlock

## **⚖️ CHOIX À VALIDER**

| Décision | Options | Recommandation |
|----------|---------|----------------|
| Validation ROOMID | Regex simple / stricte / Liste blanche | **Regex simple** |
| Implémentation CSRF | Double Submit / Synchronizer / SameSite | **Double Submit + SameSite** |
| Gestion erreurs prod | Désactiver / Admin-only / Page personnalisée | **Désactiver + page 500** |
| Stockage tmp | Fichiers / Base de données / Redis / SQLite | **Fichiers (Phase 1)** |
| Tokens API | JSON / Env vars / Vault | **JSON (Phase 1)** |

## **📊 SUIVI DES PROGRÈS**

| Tâche | Durée | Statut | Responsable | Date |
|-------|-------|--------|-------------|------|
| Tâche 1.1: XSS savetext.php | 30 min | ⬜ | | |
| Tâche 1.2: Path Traversal | 1h | ⬜ | | |
| Tâche 1.3: Protection CSRF | 1h | ⬜ | | |
| Tâche 1.4: display_errors & tokens | 30 min | ⬜ | | |
| Tâche 1.5: Race condition | 30 min | ⬜ | | |
| Tâche 2.1: Deadlock JS | 1h | ⬜ | | |
| Tâche 2.2: Tests finaux | 2h | ⬜ | | |

## **🚀 COMMANDES DE DÉMARRAGE**

```bash
# Créer les dossiers nécessaires
mkdir -p /home/omist/Security
mkdir -p /home/omist/config

# Sauvegarder les fichiers originaux (recommandé)
tar -czvf /home/omist/backup_phase1_$(date +%Y%m%d).tar.gz \
  savetext.php readtext.php saveimage.php synchronize.php \
  Translate.php conference.php \
  js/conference-func.js js/auditeur-func.js
```

## **✅ VALIDATION FINALE**

Exécuter les tests de validation:

```bash
# Tester XSS
curl -X POST -d "text=<script>alert(1)</script>&lang=fr&ROOMID=test123" savetext.php

# Tester Path Traversal
curl -X POST -d "ROOMID=../../../../etc/passwd" readtext.php

# Tester CSRF
curl -X POST -d "text=test&lang=fr&ROOMID=test123" savetext.php

# Tester upload de fichier
curl -X POST -F "image=@test.php" -F "ROOMID=test123" saveimage.php
```

## **📞 SUPPORT**

- **Documentation complète:** [`etape_2026-06-24.md`](./etape_2026-06-24.md)
- **Questions techniques:** Voir la section "Choix à faire" dans le document principal

---

*Dernière mise à jour: 24 juin 2026*
