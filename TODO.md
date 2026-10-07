# TODO List - OMIST

## 🔥 Priorité Haute

- [ ] **Désactiver le polling du chat** (FAIT ✅)
  - [x] Modifier `/home/omist/public/js/lib/chat/main.js` pour désactiver `startChatPolling()`
  - [x] Éviter la saturation PHP-FPM par les requêtes inutiles

## 📋 Priorité Moyenne

- [ ] **Migrer vers Server-Sent Events (SSE)**
  - Remplacer le polling du chat par un système d'événements (SSE ou WebSocket)
  - Objectif : réduire la charge serveur et améliorer la réactivité
  - Fichiers à modifier :
    - `/home/omist/public/js/lib/chat/main.js` (client)
    - `/home/omist/src/Controller/TextController.php` (serveur - ajouter endpoint SSE)
  - Avantages :
    - 1 connexion persistante au lieu de 20 requêtes/minute
    - Latence quasi-nulle pour les nouveaux messages
    - Scalabilité améliorée

- [ ] **Réactiver le bouton ottis-fab** (chat flottant)
  - Actuellement caché via attribut `hidden` dans `/home/omist/views/conference/_partials/chat-floating.php`
  - À réactiver après réparation du système de chat (SSE)
  - Fichier à modifier : `/home/omist/views/conference/_partials/chat-floating.php` (supprimer `hidden`)

## ⚙️ Configuration Serveur

- [ ] **Augmenter pm.max_children PHP-FPM**
  ```bash
  sudo sed -i 's/pm.max_children = 20/pm.max_children = 100/' /etc/php/8.2/fpm/pool.d/omist.conf
  ```

- [ ] **Augmenter memory_limit PHP-FPM**
  ```bash
  sudo sed -i 's/;php_admin_value\[memory_limit\].*/php_admin_value[memory_limit] = 1024M/' /etc/php/8.2/fpm/pool.d/omist.conf
  ```

- [ ] **Configurer Apache pour servir les fichiers statiques**
  ```apache
  <LocationMatch "^/tmp/.*\.(pdf|png|jpg|jpeg|gif|webp|svg|js|css|woff2?|ttf|eot)$">
      SetHandler None
      Require all granted
  </LocationMatch>
  ```

---
*Dernière mise à jour : 2026-10-05*