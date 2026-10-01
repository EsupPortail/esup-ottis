<?php
/**
 * Panel Apparence - Options d'affichage
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<div class="panel-content panel-apparence">
    <fieldset>
        <legend>Couleurs du bloc de sous-titrage</legend>
        <div class="ottis-theme-choices">
            <label class="ottis-choice">
                <input type="radio" name="th" value="d" checked>
                <span class="ottis-choice-label">Fond noir — texte blanc</span>
            </label>
            <label class="ottis-choice">
                <input type="radio" name="th" value="l">
                <span class="ottis-choice-label">Fond blanc — texte noir</span>
            </label>
        </div>
    </fieldset>

    <h3>Bloc de sous-titrage flottant</h3>
    <p>Par défaut, les sous-titres seront statiques.</p>
    <div class="ottis-form-group">
        <label class="ottis-checkbox">
            <input type="checkbox" id="usetextband">
            <span class="ottis-checkbox-custom"></span>
            <span>Activer le bloc de sous-titrage flottant</span>
        </label>
    </div>
</div>
