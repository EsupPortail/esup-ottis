<?php
/**
 * Panel Partage - Lien et QR Code
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<div class="panel-content panel-partage">
    <!-- Lien de partage (lien = sharedlink) -->
    <div class="ottis-form-group">
        <label for="sharedlink" class="ottis-label-plain translate" data-key="conference.partage.linkLabel">Lien de partage pour l'auditoire</label>
        <div class="ottis-input-group">
            <a id="sharedlink" class="ottis-share-link" aria-describedby="sharedlink-help"
               href="/auditor?ROOMID=<?php echo $this->escape($classroomid); ?>">
                /auditor?ROOMID=<?php echo $this->escape($classroomid); ?>
            </a>
            <button type="button" class="ottis-btn ottis-btn--primary" id="copier">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor">
                    <rect x="8" y="8" width="12" height="12" rx="2"/>
                    <path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>
                </svg>
                <span class="translate" data-key="conference.partage.copy">Copier</span>
            </button>
        </div>
        <p class="ottis-hint translate" id="sharedlink-help" data-key="conference.partage.linkHelp">Communiquez ce lien &agrave; l'auditoire pour qu'il rejoigne la session.</p>
        <p class="ottis-status" id="cst" role="status" aria-live="polite"></p>
    </div>

    <!-- QR Code (qr1 = sharedqr) -->
    <div class="ottis-form-group">
        <h3 class="translate" data-key="conference.partage.qrTitle">QR Code</h3>
        <p class="ottis-hint translate" data-key="conference.partage.qrHelp">
            Gr&acirc;ce au partage, chaque personne de l'auditoire pourra choisir sa langue de sous-titrage.
        </p>
        <div class="ottis-qrbox">
            <div id="sharedqr" class="translate" role="img" data-aria-key="conference.partage.qrLabel" aria-label="QR code du lien de partage de la session"></div>
        </div>
    </div>
</div>
