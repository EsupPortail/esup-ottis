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
                <span class="ottis-choice-label ottis-choice-label--dark">Fond sombre — texte blanc
                    <svg class="ottis-choice-check" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M5 13l4 4L19 7" fill="none" stroke="currentColor" stroke-width="3"/></svg>
                </span>
            </label>
            <label class="ottis-choice">
                <input type="radio" name="th" value="l">
                <span class="ottis-choice-label ottis-choice-label--light">Fond blanc — texte sombre
                    <svg class="ottis-choice-check" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M5 13l4 4L19 7" fill="none" stroke="currentColor" stroke-width="3"/></svg>
                </span>
            </label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Bloc de sous-titrage flottant</legend>
        <div class="ottis-form-group">
            <label class="ottis-checkbox">
                <input type="checkbox" id="usetextband">
                <span class="ottis-checkbox-custom"></span>
                <span>Activer le bloc de sous-titrage flottant</span>
            </label>
            <p class="ottis-hint">Par défaut, les sous-titres seront statiques.</p>
        </div>
    </fieldset>
</div>
