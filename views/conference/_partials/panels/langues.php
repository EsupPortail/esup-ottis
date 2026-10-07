<?php
/**
 * Panel Langues - Configuration linguistique
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<div class="panel-content panel-langues">
    <!-- Checkbox pour la reconnaissance vocale (requis par Micro() JS) -->
    <input type="checkbox" id="recognitionon" class="sr-only">
    <!-- Langue du conf&eacute;rencier (l1 = classroomlanguage) -->
    <div class="ottis-form-group">
        <label for="classroomlanguage" class="translate" data-key="conference.langues.classroomLabel">Langue du conf&eacute;rencier</label>
        <div class="ottis-select-wrapper">
            <select id="classroomlanguage" class="ottis-select" aria-describedby="classroomlanguage-help">
                <!-- Options g&eacute;n&eacute;r&eacute;es par JS -->
            </select>
            <svg class="ottis-select-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </div>
        <p class="ottis-hint translate" id="classroomlanguage-help" data-key="conference.langues.classroomHelp">Langue parl&eacute;e pendant l'intervention ; la reconnaissance vocale s'y r&egrave;gle.</p>
    </div>

    <!-- Langue des sous-titres (l2 = subtitlelanguage) -->
    <div class="ottis-form-group">
        <label for="subtitlelanguage" class="translate" data-key="conference.langues.subtitleLabel">Langue des sous-titres</label>
        <div class="ottis-select-wrapper">
            <select id="subtitlelanguage" class="ottis-select" aria-describedby="subtitlelanguage-help">
                <!-- Options g&eacute;n&eacute;r&eacute;es par JS -->
            </select>
            <svg class="ottis-select-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </div>
        <p class="ottis-hint translate" id="subtitlelanguage-help" data-key="conference.langues.subtitleHelp">Les sous-titres seront traduits dans cette langue.</p>
    </div>
</div>
