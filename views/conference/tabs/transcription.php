<?php
/**
 * transcription.php
 * Onglet Transcription - Notebook VO et traduit
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<div class="ottis-alert" role="note">
    <p class="translate" data-key="conference.transcription.alert1">Une fois OTTIS ferm&eacute;, vous n'aurez plus acc&egrave;s &agrave; la transcription.</p>
    <p><strong class="translate" data-key="conference.transcription.alert2">Pensez &agrave; t&eacute;l&eacute;charger le ou les fichiers en fin d'intervention.</strong></p>
</div>

<div class="ottis-cols">
    <!-- Discours original (txo = notebookVO) -->
    <section class="ottis-col" aria-labelledby="to">
        <h2 id="to" class="translate" data-key="conference.transcription.original">Discours original</h2>
        <div class="ottis-box ottis-box--primary" id="notebookVO"
             role="region" tabindex="0" aria-labelledby="to" lang="fr"></div>
        <button type="button" class="ottis-btn ottis-btn--primary"
                id="buttondownloadspeechVO"
                data-s="notebookVO" data-f="discours-original.txt">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true" focusable="false">
                <path d="M12 4v11M7 11l5 5 5-5M4 15v3a2 2 0 002 2h12a2 2 0 002-2v-3"/>
            </svg>
            <span class="translate" data-key="conference.transcription.downloadOriginal">T&eacute;l&eacute;charger le discours original (.txt)</span>
        </button>
    </section>

    <!-- Discours traduit (txt = notebook) -->
    <section class="ottis-col" aria-labelledby="tt">
        <h2 id="tt" class="translate" data-key="conference.transcription.translated">Discours traduit</h2>
        <div class="ottis-box" id="notebook"
             role="region" tabindex="0" aria-labelledby="tt" lang="en"></div>
        <button type="button" class="ottis-btn ottis-btn--primary"
                id="buttondownloadspeech"
                data-s="notebook" data-f="discours-traduit.txt">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true" focusable="false">
                <path d="M12 4v11M7 11l5 5 5-5M4 15v3a2 2 0 002 2h12a2 2 0 002-2v-3"/>
            </svg>
            <span class="translate" data-key="conference.transcription.downloadTranslated">T&eacute;l&eacute;charger le discours traduit (.txt)</span>
        </button>
    </section>
</div>
