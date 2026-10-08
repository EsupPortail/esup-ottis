<?php
/**
 * chat-floating.php
 * Chat flottant - Adapté de la maquette
 * Migration Interface Conference - Phase 3
 */

// Récupérer les données nécessaires depuis le scope parent
// (si inclus dans un onglet, $classroomid, $roomToken, $uid sont disponibles)
?>

<!-- Container pour le chat flottant -->
<div id="chatwrap">
    <!-- Section Chat (cachée par défaut) -->
    <section class="ottis-chat" id="chat" role="dialog" aria-labelledby="ct" hidden>
        <header>
            <h2 id="ct" tabindex="-1" class="translate" data-key="conference.chat.title">Tchat</h2>
            <button type="button" class="ottis-chat-close translate" id="cclose" data-aria-key="conference.chat.close" aria-label="Fermer le tchat">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </header>
        <div class="ottis-chat-scroll translate" id="classroom" role="log" data-aria-key="conference.chat.messages" aria-label="Messages" tabindex="0"></div>
        <form id="cform" class="ottis-chat-form">
            <label for="cin" class="sr-only translate" data-key="conference.chat.inputPlaceholder">Envoyer un message</label>
            <input id="cin" class="translate" data-placeholder-key="conference.chat.inputPlaceholder" placeholder="Envoyer un message" autocomplete="off">
            <button type="submit" class="ottis-chat-send translate" data-aria-key="conference.chat.send" aria-label="Envoyer le message">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor">
                    <path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/>
                </svg>
            </button>
        </form>
    </section>

    <!-- Bouton FAB (Floating Action Button) - temporairement caché -->
    <button type="button" class="ottis-fab translate" id="fab"
            aria-expanded="false"
            aria-controls="chat"
            data-aria-key="conference.chat.open"
            aria-label="Tchat">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <span class="ottis-badge" id="badge" aria-hidden="true" hidden>0</span>
    </button>
</div>
