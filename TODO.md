# TODO List - OTTIS


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


-REtour sur l'accueil et pas sur la conference en cas de deco
-Notebook a l'envers quand changement de langue (output)