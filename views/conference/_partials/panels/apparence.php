<?php
/**
 * Panel Apparence - Options d'affichage
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<div class="panel-content panel-apparence">
    <fieldset aria-describedby="apparence-colors-help">
        <legend class="translate" data-key="conference.apparence.colorsLegend">Couleurs du bloc de sous-titrage</legend>
        <p class="ottis-hint translate" id="apparence-colors-help" data-key="conference.apparence.colorsHelp">Ce choix s'applique au bloc de sous-titres affiché à l'écran.</p>
        <div class="ottis-theme-choices">
            <label class="ottis-choice">
                <input type="radio" name="th" value="d" checked>
                <span class="ottis-choice-label ottis-choice-label--dark translate" data-key="conference.apparence.dark">Fond sombre — texte blanc
                    <svg class="ottis-choice-check" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M5 13l4 4L19 7" fill="none" stroke="currentColor" stroke-width="3"/></svg>
                </span>
            </label>
            <label class="ottis-choice">
                <input type="radio" name="th" value="l">
                <span class="ottis-choice-label ottis-choice-label--light translate" data-key="conference.apparence.light">Fond blanc — texte sombre
                    <svg class="ottis-choice-check" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M5 13l4 4L19 7" fill="none" stroke="currentColor" stroke-width="3"/></svg>
                </span>
            </label>
        </div>
    </fieldset>

    <fieldset>
        <legend class="translate" data-key="conference.apparence.floatLegend">Bloc de sous-titrage flottant</legend>
        <div class="ottis-form-group">
            <label class="ottis-checkbox">
                <input type="checkbox" id="usetextband" aria-describedby="apparence-float-help">
                <span class="ottis-checkbox-custom"></span>
                <span class="translate" data-key="conference.apparence.floatLabel">Activer le bloc de sous-titrage flottant</span>
            </label>
            <p class="ottis-hint translate" id="apparence-float-help" data-key="conference.apparence.floatHelp">Par défaut, les sous-titres seront statiques.</p>
        </div>
    </fieldset>
</div>
