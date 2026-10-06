<?php
/**
 * transcription.php
 * Onglet Transcription - Notebook VO et traduit
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<div class="ottis-alert" role="note">
    <p>Une fois OTTIS ferm&eacute;, vous n'aurez plus acc&egrave;s &agrave; la transcription.</p>
    <p><strong>Pensez &agrave; t&eacute;l&eacute;charger le ou les fichiers en fin d'intervention.</strong></p>
</div>

<div class="ottis-cols">
    <!-- Discours original (txo = notebookVO) -->
    <section class="ottis-col" aria-labelledby="to">
        <h2 id="to">Discours original</h2>
        <div class="ottis-box ottis-box--primary" id="notebookVO"
             role="region" tabindex="0" aria-labelledby="to" lang="fr"></div>
        <button type="button" class="ottis-btn ottis-btn--primary" 
                id="buttondownloadspeechVO" 
                data-s="notebookVO" data-f="discours-original.txt">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <path d="M7 10l5 5 5-5M12 15v-5"/>
            </svg>
            <span>T&eacute;l&eacute;charger le discours original</span>
        </button>
    </section>

    <!-- Discours traduit (txt = notebook) -->
    <section class="ottis-col" aria-labelledby="tt">
        <h2 id="tt">Discours traduit</h2>
        <div class="ottis-box" id="notebook"
             role="region" tabindex="0" aria-labelledby="tt" lang="en"></div>
        <button type="button" class="ottis-btn" 
                id="buttondownloadspeech" 
                data-s="notebook" data-f="discours-traduit.txt">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <path d="M7 10l5 5 5-5M12 15v-5"/>
            </svg>
            <span>T&eacute;l&eacute;charger le discours traduit</span>
        </button>
    </section>
</div>
