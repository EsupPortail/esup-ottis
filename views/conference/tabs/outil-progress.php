<?php
/**
 * outil-progress.php
 * Barre de progression pour l'upload des présentations
 * Extraite de outil.php pour être toujours visible (hors du conteneur v-outil)
 */
?>

<!-- Progression upload -->
<div class="ottis-drop-prog" id="d-prog" hidden>
    <div class="ottis-prog-file">
        <span id="fname"></span>
        <progress id="presentation-progress" max="100" value="0"
                  aria-label="Progression de l'import"></progress>
        <span id="progress-status" class="ottis-prog-status"></span>
    </div>
</div>
