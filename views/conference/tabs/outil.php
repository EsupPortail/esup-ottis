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
    
    <!-- Section pour l'import de fichier (à cacher après import) -->
    <section class="ottis-import-section" id="import-section">
        <h1 id="h-outil" tabindex="-1">Sous-titrer en direct</h1>

        <!-- Zone de drop pour l'upload -->
        <div class="ottis-drop-zone" id="d-idle">
            <input type="file" id="presentation_file" name="presentation_file"
                   accept=".pptx,.odp,.pdf,.txt,.html,.htm"
                   aria-describedby="fmt anim"
                   class="ottis-drop-input">
            <div class="ottis-drop-upload" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 15V4M7 9l5-5 5 5M4 15v3a2 2 0 002 2h12a2 2 0 002-2v-3"/>
                </svg>
            </div>
            <label for="presentation_file">
                <strong>Importer votre pr&eacute;sentation</strong>
            </label>
            <small id="fmt">PPTX, PDF, ODP, HTML &mdash; max. 20 Mo</small>
        </div>

        <!-- Progression upload -->
        <div class="ottis-drop-prog" id="d-prog" hidden>
            <div class="ottis-prog-file">
                <span id="fname"></span>
                <progress id="presentation-progress" max="100" value="0"
                          aria-label="Progression de l'import"></progress>
            </div>
            <button type="button" class="ottis-btn ottis-btn--cancel" id="cancel"
                    aria-label="Annuler l'import">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <p class="ottis-error" id="err" role="alert"></p>
        <p class="ottis-sr" id="imst" role="status"></p>
        <p class="ottis-note" id="anim">Si votre pr&eacute;sentation comporte des animations, elles appara&icirc;tront fig&eacute;es.</p>
    </section>

    <!-- Conteneur pour PDF et live area -->
    <div id="ottis-import" class="ottis-import-container">
        <!-- Visualiseur PDF -->
        <div id="pdf-viewer" class="ottis-pdf-viewer">
            <!-- Canvas caché par défaut, sera affiché à l'import -->
            <canvas id="pdf-canvas" hidden></canvas>
            <div id="pdf-control" class="ottis-pdf-control" hidden>
                <div>
                    <button id="pdf-prev" disabled aria-label="Page pr&eacute;c&eacute;dente">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor">
                            <path d="M15 18l-6-6 6-6"/>
                        </svg>
                    </button>
                    <span id="pdf-page-indicator">Page 1 of 1</span>
                    <button id="pdf-next" disabled aria-label="Page suivante">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </button>
                    <input type="number" id="pdf-page-input" min="1" aria-label="Go to page number">
                </div>
            </div>
            <!-- ottis-live-area toujours visible, sous pdf-canvas -->
            <section class="ottis-live-area" id="live" aria-label="Bloc de sous-titrage">
                <button type="button" class="ottis-mic" id="micon"
                        aria-pressed="false"
                        aria-label="Lancer les sous-titres">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="3" width="6" height="11" rx="3"/>
                        <path d="M5 11a7 7 0 0014 0M12 18v3"/>
                    </svg>
                </button>
                <p id="textfixe" role="status" aria-live="polite">Cliquez sur le micro pour lancer les sous-titres&hellip;</p>
            </section>
        </div>
    </div>

    <!-- Conteneur pour text-content-viewer -->
    <div id="text-content-viewer"></div>

    <!-- Zone de sous-titres pour le PDF (commentée pour test) -->
    <!-- 
    <section class="ottis-textband-area" id="textband-section" hidden aria-label="Bloc de sous-titres PDF">
        <div id="textBand" class="text-band-style"></div>
    </section>
    -->
    </section>
