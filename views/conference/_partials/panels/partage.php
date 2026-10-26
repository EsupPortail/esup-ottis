<?php
/**
 * Panel Partage - Lien et QR Code
 * Migration Interface Conference - Phase 5 (SPA)
 */
?>

<div class="panel-content panel-partage">
    <!-- Lien de partage (lien = sharedlink) -->
    <div class="ottis-form-group">
        <p class="ottis-label-plain translate" data-key="conference.partage.linkLabel">Lien de partage pour l'auditoire</p>
        <a id="sharedlink" href="/auditor?ROOMID=<?php echo $this->escape($classroomid); ?>" hidden></a>
        <p class="ottis-hint translate" id="sharedlink-help" data-key="conference.partage.linkHelp">Communiquez ce lien &agrave; l'auditoire pour qu'il rejoigne la session.</p>
        <div class="ottis-input-group">
            <button type="button" class="ottis-btn ottis-btn--primary" id="copier" aria-describedby="sharedlink-help">
                <svg viewBox="0 0 800 800" width="16" height="16" fill="none" aria-hidden="true">
                    <path d="M524.283 129.466C572.097 81.4692 642.03 80.4612 680.747 119.327C719.533 158.266 718.49 228.641 670.647 276.67L589.853 357.777C580.11 367.56 580.14 383.39 589.92 393.134C599.703 402.877 615.533 402.847 625.277 393.064L706.07 311.957C769.78 248 777.78 145.889 716.17 84.0402C654.487 22.1185 552.603 30.189 488.86 94.1788L327.272 256.394C263.563 320.351 255.564 422.464 317.173 484.31C326.917 494.094 342.747 494.124 352.527 484.38C362.31 474.637 362.34 458.807 352.597 449.024C313.808 410.087 314.853 339.71 362.697 291.681L524.283 129.466Z" fill="white"/>
                    <path d="M482.837 315.692C473.093 305.91 457.263 305.879 447.48 315.623C437.7 325.367 437.67 341.197 447.413 350.977C486.2 389.917 485.157 460.29 437.313 508.32L275.726 670.537C227.915 718.534 157.979 719.544 119.264 680.677C80.4747 641.737 81.5197 571.36 129.363 523.33L210.158 442.224C219.902 432.44 219.871 416.614 210.089 406.867C200.307 397.124 184.478 397.154 174.734 406.937L93.9393 488.044C30.2292 552.004 22.2305 654.114 83.8397 715.964C145.522 777.884 247.407 769.814 311.15 705.824L472.737 543.607C536.447 479.65 544.447 377.54 482.837 315.692Z" fill="white"/>
                </svg>
                <span class="translate" data-key="conference.partage.copy">Copier le lien de partage</span>
            </button>
        </div>
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
