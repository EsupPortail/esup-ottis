<div class="container">
    <h1 class="translate" data-key="index.title">OTTIS facilite l'inclusion de vos interventions</h1>

    <div class="card">
        <div>
            <h2 class="translate" data-key="index.cardTitle">Sous-titrer votre intervention en direct</h2>
            <p class="translate" data-key="index.cardText">Ajoutez des sous-titres multilingues à votre intervention (avec ou sans support).</p>
        </div>

        <a class="btn-primary" href="/conference" aria-label="Démarrer l'outil">
            <span class="translate" data-key="index.cardButton">Démarrer l'outil</span>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M3 8h10M9 4l4 4-4 4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>

    <div class="accordion">
        <button class="accordion-header" type="button" onclick="toggleAccordion(this)" aria-expanded="true">
            <div class="acc-icon">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <circle cx="8" cy="8" r="6.5" stroke="#c8692a" stroke-width="1.3"/>
                    <path d="M8 7v4" stroke="#c8692a" stroke-width="1.4" stroke-linecap="round"/>
                    <circle cx="8" cy="5" r="0.8" fill="#c8692a"/>
                </svg>
            </div>
            <span class="translate" data-key="index.accordionTitle">Conseils pour une utilisation optimale d'OTTIS</span>
            <svg class="chevron open" width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                <path d="M5 11l4-4 4 4" stroke="#1c1c2e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div class="accordion-body" id="acc-body">
            <div class="tip-card">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <rect x="7" y="2" width="6" height="10" rx="3" stroke="#1c1c2e" stroke-width="1.4"/>
                    <path d="M4 10a6 6 0 0012 0" stroke="#1c1c2e" stroke-width="1.4" stroke-linecap="round"/>
                    <path d="M10 16v2" stroke="#1c1c2e" stroke-width="1.4" stroke-linecap="round"/>
                </svg>
                <p class="translate" data-key="index.tip1">Pour une captation efficace, éviter les environnements bruyants et privilégier un micro de qualité (de préférence : casque ou micro-cravate).</p>
            </div>

            <div class="tip-card">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <circle cx="10" cy="8" r="5" stroke="#1c1c2e" stroke-width="1.4"/>
                    <path d="M6 14c0-2.2 1.8-4 4-4s4 1.8 4 4" stroke="#1c1c2e" stroke-width="1.4" stroke-linecap="round"/>
                </svg>
                <p class="translate" data-key="index.tip2">Pour une meilleure lisibilité des sous-titres, faites des phrases courtes et marquez une pause en fin de chaque phrase.</p>
            </div>
        </div>
    </div>

    <!-- Pop-up d'alerte navigateur non supporte (RGAA : dialog natif, focus gere par showModal) -->
    <dialog id="browser-alert-dialog" role="alertdialog" aria-labelledby="browser-alert-dialog-title" aria-describedby="browser-alert-dialog-message">
        <h2 id="browser-alert-dialog-title" class="translate" data-key="index.alertBrowserTitle">Navigateur non supporté</h2>
        <p id="browser-alert-dialog-message" class="translate" data-key="index.alertBrowser">Vous utilisez un navigateur non supporté. Certaines fonctionnalités peuvent ne pas s'afficher correctement. Nous vous recommandons d'utiliser <strong>Chrome ou Edge</strong> pour une expérience optimale.</p>
        <button type="button" id="browser-alert-dialog-close" class="btn-primary translate" data-key="index.alertBrowserClose">Fermer</button>
    </dialog>
</div>

<script>
    function toggleAccordion(btn) {
        const body = document.getElementById('acc-body');
        const chevron = btn.querySelector('.chevron');
        const isOpen = btn.getAttribute('aria-expanded') === 'true';

        if (isOpen) {
            body.style.display = 'none';
            chevron.classList.replace('open', 'closed');
            btn.setAttribute('aria-expanded', 'false');
        } else {
            body.style.display = 'grid';
            chevron.classList.replace('closed', 'open');
            btn.setAttribute('aria-expanded', 'true');
        }
    }
</script>
