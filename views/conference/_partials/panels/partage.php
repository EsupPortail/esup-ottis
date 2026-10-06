<?php
/**
 * Panel Partage - Lien et QR Code
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<div class="panel-content panel-partage">
    <!-- Lien de partage (lien = sharedlink) -->
    <div class="ottis-form-group">
        <label for="sharedlink" class="ottis-label-plain">Lien de partage pour l'auditoire</label>
        <div class="ottis-input-group">
            <input type="text" id="sharedlink"
                   value="/auditor?ROOMID=<?php echo $this->escape($classroomid); ?>"
                   readonly
                   class="ottis-input">
            <button type="button" class="ottis-btn ottis-btn--primary" id="copier">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor">
                    <rect x="8" y="8" width="12" height="12" rx="2"/>
                    <path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>
                </svg>
                <span>Copier</span>
            </button>
        </div>
        <p class="ottis-status" id="cst" role="status" aria-live="polite"></p>
    </div>

    <!-- QR Code (qr1 = sharedqr) -->
    <div class="ottis-form-group">
        <h3>QR Code</h3>
        <p class="ottis-hint">
            Gr&acirc;ce au partage, chaque personne de l'auditoire pourra choisir sa langue de sous-titrage.
        </p>
        <div class="ottis-qrbox">
            <div id="qr1"></div>
            <button type="button" class="ottis-qrx" id="qrx" aria-label="Agrandir le QR code">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor">
                    <path d="M14 4h6v6M10 20H4v-6M20 4l-7 7M4 20l7-7"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Dialog pour QR code agrandi -->
    <dialog id="qrd" class="ottis-dialog" aria-labelledby="qrt">
        <h2 id="qrt">QR Code de partage</h2>
        <div id="sharedqr"></div>
        <p>
            <button type="button" class="ottis-btn ottis-btn--primary" id="qrc">
                Fermer
            </button>
        </p>
    </dialog>
</div>
