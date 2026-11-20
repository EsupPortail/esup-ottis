<?php
/**
 * transcription.php
 * Onglet Transcription - bloc unique avec bascule VO / traduction
 * (interface reproduite de la page auditor : fieldset.seg + .box + .dls)
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<h1 id="h-transcription" tabindex="-1" class="translate" data-key="conference.transcription.title">Transcription</h1>

<div class="ottis-alert" role="note">
    <p class="translate" data-key="conference.transcription.alert1">Une fois OTTIS ferm&eacute;, vous n'aurez plus acc&egrave;s &agrave; la transcription.</p>
    <p><strong class="translate" data-key="conference.transcription.alert2">Pensez &agrave; la t&eacute;l&eacute;charger le ou les fichiers en fin d'intervention.</strong></p>
</div>

<div class="box">
    <fieldset class="seg">
        <legend class="sr-only translate" data-key="conference.transcription.versionLegend">Version du discours</legend>
        <label>
            <input type="radio" name="ver" id="seg-vo" value="vo" checked>
            <span>
                <span class="translate" data-key="conference.transcription.voLabel">Version originale</span>
                <span id="seg-vo-lang"></span>
            </span>
        </label>
        <label>
            <input type="radio" name="ver" id="seg-tr" value="">
            <span>
                <span class="translate" data-key="conference.transcription.trLabel">Version traduite</span>
                <span id="seg-tr-lang"></span>
            </span>
        </label>
    </fieldset>
    <div id="notebookVO" role="region" tabindex="0" aria-labelledby="h-transcription" lang="fr"></div>
    <div id="notebook" role="region" tabindex="0" aria-labelledby="h-transcription" hidden></div>
</div>

<div class="dls">
    <button type="button" class="ottis-btn ottis-btn--primary"
            id="buttondownloadspeechVO"
            data-s="notebookVO" data-f="discours-original.txt">
        <span class="translate" data-key="conference.transcription.downloadOriginal">T&eacute;l&eacute;charger le discours original (.txt)</span>
    </button>
    <button type="button" class="ottis-btn ottis-btn--primary"
            id="buttondownloadspeech"
            data-s="notebook" data-f="discours-traduit.txt">
        <span class="translate" data-key="conference.transcription.downloadTranslated">T&eacute;l&eacute;charger le discours traduit (.txt)</span>
    </button>
</div>
