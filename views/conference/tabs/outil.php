<?php
/**
 * outil.php
 * Onglet principal - Upload, Micro, Sous-titres Live
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<section class="ottis-section" aria-labelledby="h-outil">
    <!-- Checkbox caché pour la reconnaissance vocale (nécessaire pour le JS) -->
    <input type="checkbox" id="recognitionon" class="sr-only" aria-hidden="true">

    <!-- Barre de progression pour l'upload -->
    <?php include __DIR__ . '/outil-progress.php'; ?>

    <!-- Section pour l'import de fichier (à cacher après import) -->
    <section class="ottis-import-section" id="import-section">
        <h1 id="h-outil" tabindex="-1" class="translate" data-key="conference.outil.title">Sous-titrer en direct</h1>

        <!-- Zone de drop pour l'upload -->
        <div class="ottis-drop-zone" id="d-idle">
            <input type="file" id="presentation_file" name="presentation_file"
                   accept=".pptx,.odp,.pdf,.txt,.html,.htm"
                   aria-describedby="filehelp fmt anim"
                   class="ottis-drop-input">
            <div class="ottis-drop-upload" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 15V4M7 9l5-5 5 5M4 15v3a2 2 0 002 2h12a2 2 0 002-2v-3"/>
                </svg>
            </div>
            <label for="presentation_file">
                <strong class="translate" data-key="conference.outil.importLabel">Importer votre pr&eacute;sentation</strong>
            </label>
            <small id="fmt" class="translate" data-key="conference.outil.formats">PPTX, PDF, ODP, HTML &mdash; max. 20 Mo</small>
        </div>

        <p class="ottis-sr translate" id="filehelp" data-key="conference.outil.fileHelp">S&eacute;lectionnez un fichier de pr&eacute;sentation &agrave; sous-titrer (20 Mo maximum).</p>
        <p class="ottis-error" id="err" role="alert"></p>
        <p class="ottis-sr" id="imst" role="status"></p>
        <p class="ottis-note translate" id="anim" data-key="conference.outil.animNote">Si votre pr&eacute;sentation comporte des animations, elles appara&icirc;tront fig&eacute;es.</p>
    </section>

    <!-- Conteneur pour PDF et live area -->
    <div id="ottis-import" class="ottis-import-container">
        <!-- Visualiseur PDF -->
        <div id="pdf-viewer" class="ottis-pdf-viewer">
            <!-- Bouton pour fermer la présentation - caché par défaut -->
            <button type="button" class="ottis-pdf-close translate" id="pdf-close-btn" hidden data-aria-key="conference.outil.closePresentation" aria-label="Fermer la pr&eacute;sentation">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
            <!-- Canvas caché par défaut, sera affiché à l'import -->
            <canvas id="pdf-canvas" hidden></canvas>
            <div id="pdf-control" class="ottis-pdf-control" hidden>
                <div>
                    <button type="button" id="pdf-fullscreen-btn" class="translate" data-aria-key="conference.outil.fullscreen" aria-label="Plein &eacute;cran" title="Press ESC to exit fullscreen">
                        Fullscreen
                    </button>
                    <button id="pdf-prev" disabled class="translate" data-aria-key="conference.outil.prevPage" aria-label="Page pr&eacute;c&eacute;dente">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor">
                            <path d="M15 18l-6-6 6-6"/>
                        </svg>
                    </button>
                    <span id="pdf-page-indicator">Page 1 of 1</span>
                    <button id="pdf-next" disabled class="translate" data-aria-key="conference.outil.nextPage" aria-label="Page suivante">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </button>
                    <input type="number" id="pdf-page-input" min="1" aria-label="Go to page number" style="width: 40px;">
                </div>
            </div>
        </div>
        <!-- Live area déplacé en dehors de pdf-viewer pour rester visible -->
        <section class="ottis-live-area translate" id="live" data-aria-key="conference.outil.liveArea" aria-label="Bloc de sous-titrage">
                <button type="button" class="ottis-mic translate" id="micon"
                        aria-pressed="false"
                        data-aria-key="conference.outil.micStart"
                        aria-label="Lancer les sous-titres">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="3" width="6" height="11" rx="3"/>
                        <path d="M5 11a7 7 0 0014 0M12 18v3"/>
                    </svg>
                </button>
                <p id="textfixe" class="translate" data-key="conference.outil.defaultSubtitle" role="status" aria-live="polite">Cliquez sur le micro pour lancer les sous-titres&hellip;</p>
            </section>
        <!-- Bloc de sous-titrage flottant (activable depuis l'onglet Apparence) -->
        <section class="ottis-float-live-area translate" id="float-live" hidden tabindex="0"
                 data-aria-key="conference.outil.floatArea"
                 aria-label="Bloc de sous-titrage flottant, d&eacute;pla&ccedil;able &agrave; la souris, au doigt ou avec les touches fl&eacute;ch&eacute;es">
            <p id="partialtitre" class="translate" data-key="conference.outil.defaultSubtitle" role="status" aria-live="polite">Cliquez sur le micro pour lancer les sous-titres&hellip;</p>
        </section>
    </div>

    <!-- Conteneur pour text-content-viewer -->
    <div id="text-content-viewer"></div>
    </section>
