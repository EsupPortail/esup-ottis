<?php
/**
 * index.php - SPA Auditor
 * Migration vers l'architecture SPA hash-based (comme conference)
 * Maquette : audit/maquette/auditor.html - RGAA 4.1
 *
 * Correspondances maquette -> projet :
 * dlg = dialog-choose | subs = fixed-title | sl = VoiceOutput
 * tr = synthesison | tx = notebook (VO) / output (traduction)
 * boutons telechargement = download-notebook-btn / download-output-btn
 */
?>

<!-- Inputs caches requis par le JS existant -->
<input type="hidden" id="classroomid" value="<?php echo $this->escape($classroomid); ?>">
<span id="nbrequests" hidden>0</span>
<span hidden><label for="classroomlanguage">Classroom language</label>
    <select id="classroomlanguage"></select>
</span>
<div id="notebookVO" hidden></div>
<!-- Bandeaux flottants : masques, l'affichage live se fait dans #fixed-title -->
<div id="titre" hidden></div>
<div id="VOBanner" hidden></div>
<?php echo \App\Utils\Csrf::field(); ?>

<script>
    window.pageData = <?php echo json_encode($jsVars); ?>;
    window.ROOM_TOKEN = '<?php echo htmlspecialchars($roomToken, ENT_QUOTES); ?>';
    const auditorName = <?php echo json_encode($randomname ?? ''); ?>;
    const auditorUid = <?php echo json_encode($randomname ?? 'auditor'); ?>;
    const intervenantUid = <?php echo json_encode($intervenantUid ?? null); ?>;
    globalThis.OMIST_USER = Object.assign(globalThis.OMIST_USER || {}, {
        fullName: auditorName,
        uid: auditorUid,
        isIntervenant: false
    });
    globalThis.ROOM_OWNER_UID = window.pageData?.roomOwnerUid || intervenantUid || null;
    window.pageData = Object.assign(window.pageData || {}, {
        roomOwnerUid: globalThis.ROOM_OWNER_UID,
        intervenantUid: intervenantUid,
        intervenantName: null
    });
</script>

<!-- Structure SPA (s'integre dans <main class="ottis-main"> de default.php) -->
<div class="dock" id="dock">
    <nav class="rail on-dark" aria-label="R&eacute;glages du sous-titrage">
        <ul>
            <li>
                <button type="button" id="tab-langue" aria-expanded="false" aria-controls="panel">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5h9M7.5 3v2M5 5c1 4 4 7 7 8M11 5c-1 4-4 7-8 9M13 21l4-10 4 10M14.5 17.5h5"/></svg></span>
                    <span class="translate" data-key="auditor.nav.langue">Langue</span>
                </button>
            </li>
            <li>
                <a href="#tchat" id="tab-tchat">
                    <span class="ottis-tab-bubble" id="tchat-bubble" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span class="ottis-badge" id="badge" aria-hidden="true" hidden>0</span>
                    </span>
                    <span class="translate" data-key="auditor.nav.tchat">Tchat</span>
                </a>
            </li>
            <li>
                <a href="#transcription" id="tab-transcription">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2.5"/><path d="M7 10h3M13 10h4M7 14h5M15 14h2"/></svg></span>
                    <span class="translate" data-key="auditor.nav.transcription">Transcription</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Panneau unique (Langue / Votre nom) -->
    <aside class="panel" id="panel" aria-labelledby="pt" hidden>
        <div class="panel-header">
            <h2 id="pt" tabindex="-1"></h2>
            <button type="button" class="x" id="pclose" aria-label="Fermer le panneau">
                <span aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 5l14 14M19 5L5 19"/></svg></span>
            </button>
        </div>
        <div class="panel-body">
            <!-- Langue : sl = VoiceOutput, tr = synthesison -->
            <section class="panel-content panel-langue" id="sh-langue" style="display:none">
                <div class="ottis-form-group">
                    <label for="VoiceOutput" class="translate" data-key="auditor.langue.subtitlesLabel">Langue des sous-titres</label>
                    <div class="ottis-select-wrapper">
                        <select id="VoiceOutput" class="ottis-select"></select>
                        <svg class="ottis-select-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                <label class="ottis-switch">
                    <span class="translate" data-key="auditor.langue.synthesis">Activer la synth&egrave;se vocale</span>
                    <input type="checkbox" id="synthesison" role="switch">
                    <span class="tr" aria-hidden="true"></span>
                </label>
            </section>

            <!-- Votre nom (prepare pour le tchat, en attente du passage SSE) -->
            <section class="panel-content panel-nom" id="sh-nom" style="display:none">
                <span hidden class="translate" data-key="auditor.nom.title" id="nom-sheet-title">Votre nom</span>
                <form id="nform" novalidate>
                    <p>
                        <label class="ottis-form-group-label" for="AuditorName">
                            <span class="translate" data-key="auditor.nom.label">Pr&eacute;nom Nom (visibles dans le tchat)</span>
                        </label>
                        <input type="text" id="AuditorName" class="ottis-input" autocomplete="name"
                               value="<?php echo $this->escape($randomname); ?>" aria-describedby="nerr">
                    </p>
                    <p class="err" id="nerr" role="alert"></p>
                    <p><button type="submit" class="btn btn--primary translate" data-key="auditor.nom.submit">Valider</button></p>
                </form>
            </section>
        </div>
    </aside>
</div>

<div class="content" id="content">
    <!-- Sous-titres (subs = fixed-title) -->
    <section class="m" id="v-sous-titres">
        <h1 id="h-sous-titres" tabindex="-1" class="translate" data-key="auditor.subtitles.title">Sous-titres</h1>
        <p class="live" id="live-status" role="status">
            <span id="live-on" class="translate" data-key="auditor.live.on">En direct</span>
            <span id="live-err" class="translate" data-key="auditor.live.err" hidden>En erreur</span>
        </p>
        <div class="box" id="fixed-title" role="region" tabindex="0" aria-label="Sous-titres en direct"></div>
    </section>

    <!-- Tchat : interface preparee, onglet sans effet en attente du passage au mode SSE -->
    <section class="m" id="v-tchat" hidden>
        <h1 id="h-tchat" tabindex="-1" class="translate" data-key="auditor.tchat.title">Tchat</h1>
        <p class="chat-tools">
            <button type="button" class="link-btn translate" data-key="auditor.tchat.changeName" id="nom-open-btn">Changer de nom</button>
        </p>
        <div class="chat">
            <div class="log" role="log" aria-label="Messages du tchat" tabindex="0">
                <div id="classroom" aria-live="polite" aria-atomic="true"></div>
            </div>
            <form class="send" id="cform">
                <label class="sr-only translate" for="question" data-key="auditor.tchat.inputLabel">Envoyer un message</label>
                <textarea id="question" rows="1" autocomplete="off"
                          placeholder="Envoyer un message"></textarea>
                <button type="button" id="questionsend" aria-label="Envoyer le message">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 3L10 14M21 3l-7 18-4-7-7-4z"/></svg></span>
                </button>
            </form>
        </div>
    </section>

    <!-- Transcription (tx = notebook ou output selon le seg) -->
    <section class="m" id="v-transcription" hidden>
        <h1 id="h-transcription" tabindex="-1" class="translate" data-key="auditor.transcription.title">Transcription</h1>
        <div class="ottis-alert" role="note">
            <p class="translate" data-key="auditor.transcription.alert1">Une fois OTTIS ferm&eacute;, vous n'aurez plus acc&egrave;s &agrave; la transcription.</p>
            <p><strong class="translate" data-key="auditor.transcription.alert2">Pensez &agrave; la t&eacute;l&eacute;charger en fin d'intervention, avant de fermer la fen&ecirc;tre de votre navigateur.</strong></p>
        </div>
        <div class="box">
            <fieldset class="seg">
                <legend class="sr-only translate" data-key="auditor.transcription.versionLegend">Version du discours</legend>
                <label>
                    <input type="radio" name="ver" id="seg-vo" value="vo" checked>
                    <span><span id="seg-vo-label">VO</span><span class="sr-only translate" data-key="auditor.transcription.voSr"> (version originale)</span></span>
                </label>
                <label>
                    <input type="radio" name="ver" id="seg-tr" value="">
                    <span><span id="seg-tr-label">&mdash;</span><span class="sr-only translate" data-key="auditor.transcription.trSr"> (traduction)</span></span>
                </label>
            </fieldset>
            <div id="notebook"></div>
            <div id="output" hidden></div>
        </div>
        <div class="dls">
            <button type="button" class="btn btn--primary" id="download-notebook-btn">
                <span aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v11M7 11l5 5 5-5M4 15v3a2 2 0 002 2h12a2 2 0 002-2v-3"/></svg></span>
                <span class="translate" data-key="auditor.transcription.downloadOriginal">T&eacute;l&eacute;charger le discours original (.txt)</span>
            </button>
            <button type="button" class="btn btn--primary" id="download-output-btn">
                <span aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v11M7 11l5 5 5-5M4 15v3a2 2 0 002 2h12a2 2 0 002-2v-3"/></svg></span>
                <span class="translate" data-key="auditor.transcription.downloadTranslated">T&eacute;l&eacute;charger le discours traduit (.txt)</span>
            </button>
        </div>
    </section>
</div>

<!-- Pop-up de choix de langue (dlg = dialog-choose, validation automatique au changement) -->
<dialog id="dialog-choose" aria-labelledby="dialog-choose-title">
    <form method="dialog">
        <?php echo \App\Utils\Csrf::field(); ?>
        <h2 id="dialog-choose-title" class="translate" data-key="auditor.dialog.title">R&eacute;glages de l'&eacute;coute</h2>
        <label class="ottis-form-group-label" for="VoiceOutputchoose">
            <span class="translate" data-key="auditor.dialog.label">Vous devez d'abord choisir votre langue</span>
        </label>
        <div class="ottis-select-wrapper">
            <select id="VoiceOutputchoose" class="ottis-select"></select>
            <svg class="ottis-select-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"><path d="M6 9l6 6 6-6"/></svg>
        </div>
        <p><button type="submit" id="dialog-choose-go" class="btn btn--primary translate" data-key="auditor.dialog.submit">Valider</button></p>
    </form>
</dialog>

<!-- Dialogue d'envoi par mail (fonctionnalite existante conservee) -->
<dialog id="dialog-mail" aria-labelledby="dialog-mail-title">
    <form method="dialog">
        <fieldset>
            <legend class="sr-only">Send a mail</legend>
            <?php echo \App\Utils\Csrf::field(); ?>
            <h2 id="dialog-mail-title">Send a mail</h2>
            <label for="dialog_MailTo">Mail to</label>
            <input type="text" id="dialog_MailTo">
            <label class="sr-only" for="dialog_MailFrom">Mail from</label>
            <input type="text" id="dialog_MailFrom" readonly>
            <label for="dialog_MailSubject">Subject</label>
            <input type="text" id="dialog_MailSubject" readonly>
            <label for="dialog_MailBody">Body</label>
            <div id="dialog_MailBody"></div>
            <menu>
                <button type="submit" id="send-mail-btn">Send</button>
                <button type="reset" id="cancel-mail-btn">Cancel</button>
            </menu>
        </fieldset>
    </form>
</dialog>

<script type="module">
(function () {
    'use strict';

    // Tchat prepare : bouton d'envoi (le mecanisme de polling reste desactive en attente du SSE)
    import('/js/lib/chat/handlers.js').then(({ sendclick }) => {
        const questionsend = document.getElementById('questionsend');
        if (questionsend && typeof sendclick === 'function') {
            questionsend.addEventListener('click', sendclick);
        }
    }).catch(() => {});

    /* Routage SPA par hash : sous-titres (defaut) / transcription.
       Le tchat est desactive en attente du passage au mode SSE (onglet sans effet). */
    const tabs = {
        'sous-titres': { content: 'v-sous-titres', title: 'Sous-titres' },
        tchat: { content: 'v-tchat', title: 'Tchat' },
        transcription: { content: 'v-transcription', title: 'Transcription' },
    };
    let nomSheetShown = false;

    const panel = document.getElementById('panel');
    const pt = document.getElementById('pt');
    const tabLangue = document.getElementById('tab-langue');
    let currentSheet = null;
    let sheetTrigger = null;
    let currentView = 'sous-titres';

    function showView(name) {
        const tab = tabs[name] || tabs['sous-titres'];
        currentView = name;
        Object.values(tabs).forEach((t) => {
            document.getElementById(t.content).hidden = true;
        });
        document.getElementById(tab.content).hidden = false;
        ['tchat', 'transcription'].forEach((n) => {
            const el = document.getElementById('tab-' + n);
            if (!el) return;
            if (n === name) el.setAttribute('aria-current', 'page');
            else el.removeAttribute('aria-current');
        });
        document.title = tab.title + ' \u2013 OTTIS';
    }

    function route(focus) {
        const hash = window.location.hash.replace('#', '') || 'sous-titres';
        const name = tabs[hash] ? hash : 'sous-titres';
        // Navigation vers un autre onglet : fermer le panneau ouvert (langue, nom...)
        closeSheet();
        showView(name);
        if (focus) {
            // RGAA : deplacer le focus sur le titre de la nouvelle vue
            const h = document.getElementById('h-' + name);
            if (h) h.focus();
        }
        if (name === 'tchat' && !nomSheetShown) {
            // Premiere entree dans le tchat : reglage du nom
            nomSheetShown = true;
            const nomTitle = document.getElementById('nom-sheet-title');
            openSheet('nom', nomTitle ? nomTitle.textContent : 'Votre nom',
                document.getElementById('tab-tchat'));
        }
        if (name === 'tchat') {
            // Messages lus : reinitialiser badge et clignotement
            import('/js/lib/chat/alert.js').then(({ resetChatAlert }) => {
                resetChatAlert();
            }).catch(() => {});
        }
    }

    function closeSheet() {
        if (!currentSheet) return;
        currentSheet = null;
        if (panel) panel.hidden = true;
        document.querySelectorAll('#panel .panel-content').forEach((el) => {
            el.style.display = 'none';
        });
        if (tabLangue) tabLangue.setAttribute('aria-expanded', 'false');
        // RGAA : rendre le focus au declencheur
        if (sheetTrigger && sheetTrigger.focus) sheetTrigger.focus();
        sheetTrigger = null;
    }

    function openSheet(name, title, trigger) {
        document.querySelectorAll('#panel .panel-content').forEach((el) => {
            el.style.display = 'none';
        });
        const sheet = document.getElementById('sh-' + name);
        if (sheet) sheet.style.display = 'block';
        if (panel) panel.hidden = false;
        currentSheet = name;
        sheetTrigger = trigger || tabLangue;
        if (pt) {
            pt.textContent = title;
            // RGAA : envoyer le focus sur le titre du panneau
            pt.focus();
        }
        if (tabLangue) tabLangue.setAttribute('aria-expanded', name === 'langue' ? 'true' : 'false');
    }

    if (tabLangue) {
        tabLangue.addEventListener('click', () => {
            // L'onglet Langue affiche toujours la vue Sous-titres
            if (currentView !== 'sous-titres') {
                showView('sous-titres');
                window.history.replaceState(null, '', window.location.pathname + window.location.search);
            }
            if (currentSheet === 'langue') {
                closeSheet();
            } else {
                const label = tabLangue.querySelector('.translate');
                openSheet('langue', label ? label.textContent : 'Langue', tabLangue);
            }
        });
    }

    const pclose = document.getElementById('pclose');
    if (pclose) pclose.addEventListener('click', closeSheet);

    // RGAA : Echap ferme le panneau ouvert (rendu du focus au declencheur)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && currentSheet) closeSheet();
    });

    /* Votre nom (prepare pour le tchat) : validation et maj de l'identite */
    const nform = document.getElementById('nform');
    if (nform) {
        nform.addEventListener('submit', (e) => {
            e.preventDefault();
            const nom = document.getElementById('AuditorName');
            const nerr = document.getElementById('nerr');
            const value = nom ? nom.value.trim() : '';
            if (!value) {
                if (nerr) nerr.textContent = 'Saisissez votre prenom et votre nom pour acceder au tchat.';
                if (nom) {
                    nom.setAttribute('aria-invalid', 'true');
                    nom.focus();
                }
                return;
            }
            if (value === 'Intervenant' && nom) nom.value = 'Auditor';
            if (nerr) nerr.textContent = '';
            if (nom) nom.removeAttribute('aria-invalid');
            globalThis.OMIST_USER = Object.assign(globalThis.OMIST_USER || {}, {
                fullName: value,
                uid: value,
            });
            closeSheet();
        });
    }

    // Bouton "Changer de nom" (vue tchat) : ouvre le volet Votre nom
    const nomOpenBtn = document.getElementById('nom-open-btn');
    if (nomOpenBtn) {
        nomOpenBtn.addEventListener('click', () => {
            const nomTitle = document.getElementById('nom-sheet-title');
            openSheet('nom', nomTitle ? nomTitle.textContent : 'Votre nom', nomOpenBtn);
        });
    }

    window.addEventListener('hashchange', () => route(true));
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => route(false));
    } else {
        route(false);
    }
})();
</script>
