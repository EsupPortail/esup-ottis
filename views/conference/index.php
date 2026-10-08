<?php
/**
 * index.php - SPA Conference
 * Migration vers architecture SPA hash-based
 * Phase 5 : Implémentation SPA avec noms du projet
 * Structure adaptée pour s'intégrer dans default.php
 */

// Préparer les données communes pour les tabs
$commonData = [
    'classroomid' => $classroomid,
    'appid' => $appid,
    'roomToken' => $roomToken,
    'uid' => $uid,
    'currentUserDisplayName' => $currentUserDisplayName,
    'jsVars' => $jsVars,
];
?>

<!-- Inputs cachés -->
<input type="hidden" id="classroomid" value="<?php echo $this->escape($classroomid); ?>">
<input type="hidden" id="appid" value="<?php echo $this->escape($appid); ?>">
<input type="hidden" id="CurrentUserDisplayName" value="<?php echo $this->escape($currentUserDisplayName ?? $uid ?? ''); ?>">
<?php echo \App\Utils\Csrf::field(); ?>

<!-- Variables JavaScript -->
<script>
    window.pageData = <?php echo json_encode($jsVars); ?>;
    window.ROOM_TOKEN = '<?php echo htmlspecialchars($roomToken, ENT_QUOTES); ?>';
    const intervenantName = <?php echo json_encode($currentUserDisplayName ?? $uid ?? ''); ?>;
    const intervenantUid = <?php echo json_encode($uid ?? 'anonymous'); ?>;
    globalThis.OMIST_USER = Object.assign(globalThis.OMIST_USER || {}, {
        fullName: intervenantName,
        uid: intervenantUid,
        isIntervenant: true
    });
    globalThis.ROOM_OWNER_UID = intervenantUid;
    window.pageData = Object.assign(window.pageData || {}, {
        intervenantName,
        roomOwnerUid: intervenantUid
    });
</script>

<!-- Structure SPA directe (s'intègre dans <main class="ottis-main"> de default.php) -->
<div class="dock" id="dock">
    <?php include __DIR__ . '/_partials/header-nav.php'; ?>

    <!-- Panel unique (contenu dynamique) -->
    <aside class="panel" id="panel" aria-labelledby="pt" hidden>
        <div class="panel-header" id="ph">
            <h2 id="pt" tabindex="-1"></h2>
            <button type="button" class="x translate" data-aria-key="conference.nav.closePanel" id="pclose" aria-label="Fermer le panneau">
                <span class="ottis-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 5l14 14M19 5L5 19"/></svg></span>
            </button>
        </div>
        <div class="panel-body">
            <?php include __DIR__ . '/_partials/panels/langues.php'; ?>
            <?php include __DIR__ . '/_partials/panels/apparence.php'; ?>
            <?php include __DIR__ . '/_partials/panels/partage.php'; ?>
            <?php include __DIR__ . '/_partials/panels/transcription.php'; ?>
        </div>
    </aside>
</div>

<div class="content">
    <!-- Contenus principaux (2 seulement) -->
    <main class="m" id="v-outil" hidden>
        <?php
        extract($commonData);
include __DIR__ . '/tabs/outil.php';
?>
    </main>
    <main class="m" id="v-transcription" hidden>
        <?php
extract($commonData);
include __DIR__ . '/tabs/transcription.php';
?>
    </main>
</div>

<!-- Chat flottant -->
<?php include __DIR__ . '/_partials/chat-floating.php'; ?>

<!-- Script de routage SPA -->
<script type="module">
(function() {
    'use strict';

    // Nettoyer les URLs avec ?tab=X au chargement
    if (window.location.search.includes('tab=')) {
        const match = window.location.search.match(/tab=([^&]+)/);
        if (match) {
            window.history.replaceState(null, '', '#' + match[1]);
        }
    }

    const tabs = {
        outils: {
            content: 'v-outil',
            panel: '',
            title: 'Sous-titrer en direct',
        },
        langues: {
            content: 'v-outil',  // Garde le contenu outil
            panel: 'langues',
            title: 'Sous-titrer en direct',
        },
        apparence: {
            content: 'v-outil',  // Garde le contenu outil
            panel: 'apparence',
            title: 'Sous-titrer en direct',
        },
        partage: {
            content: 'v-outil',  // Garde le contenu outil
            panel: 'partage',
            title: 'Sous-titrer en direct',
        },
        transcription: {
            content: 'v-transcription',
            panel: '',
            title: 'Transcription',
        }
    };

    function route() {
        const hash = window.location.hash.replace('#', '') || 'outils';
        const tab = tabs[hash] || tabs.outils;
        const panel = document.getElementById('panel');
        const pt = document.getElementById('pt');

        // Masquer tous les contenus principaux
        document.querySelectorAll('.m').forEach(el => {
            el.hidden = true;
        });

        // Masquer tous les panels
        document.querySelectorAll('.panel-content').forEach(el => {
            el.style.display = 'none';
        });

        // Afficher le contenu actif
        if (tab.content) {
            document.getElementById(tab.content).hidden = false;
        }

        // RGAA/SPA: Assurer que seul le contenu actif est visible
        const vOutil = document.getElementById('v-outil');
        const vTranscription = document.getElementById('v-transcription');
        if (hash === 'transcription') {
            if (vOutil) vOutil.hidden = true;
            if (vTranscription) vTranscription.hidden = false;
        } else {
            if (vOutil) vOutil.hidden = false;
            if (vTranscription) vTranscription.hidden = true;
        }

        // Gérer le panel : le titre est lu depuis le libellé du lien du rail
        // (déjà traduit par le mécanisme i18n), sans dupliquer les textes ici
        if (tab.panel) {
            panel.hidden = false;
            const titleLink = document.querySelector(`.rail a[href="#${hash}"] .translate`);
            pt.textContent = titleLink ? titleLink.textContent : '';
            const activePanel = document.querySelector(`.panel-${tab.panel}`);
            if (activePanel) {
                activePanel.style.display = 'block';
            }
            // RGAA : le panneau recouvre le contenu -> envoyer le focus sur son titre
            pt.focus();
        } else {
            panel.hidden = true;
        }

        // Gérer aria-current sur le rail
        document.querySelectorAll('.rail a').forEach(a => a.removeAttribute('aria-current'));
        const activeLink = document.querySelector(`.rail a[href="#${hash}"]`);
        if (activeLink) {
            activeLink.setAttribute('aria-current', 'page');
        }

        document.title = tab.title + ' – OTTIS';
    }

    window.addEventListener('hashchange', route);
    window.addEventListener('DOMContentLoaded', route);

    // Gestion du re-clic sur un onglet déjà actif
    document.querySelectorAll('.rail a').forEach(a => {
        a.addEventListener('click', function(e) {
            const targetHash = this.getAttribute('href').replace('#', '');
            const currentHash = window.location.hash.replace('#', '');
            // Si on clique sur l'onglet actif, fermer le panneau et revenir à Outil
            if (targetHash === currentHash) {
                e.preventDefault();
                location.hash = '#outil';
            }
        });
    });

    // Gestion du bouton de fermeture du panneau
    document.getElementById('pclose')?.addEventListener('click', function() {
        location.hash = '#outil';
    });

    // RGAA : Échap ferme le panneau ouvert et rend le focus au lien du rail
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        const openPanel = document.getElementById('panel');
        if (!openPanel || openPanel.hidden) return;
        const activeLink = document.querySelector('.rail a[aria-current="page"]');
        location.hash = '#outil';
        if (activeLink) activeLink.focus();
    });
})();
</script>
