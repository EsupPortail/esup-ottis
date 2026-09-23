<!DOCTYPE html>
<html lang="<?php echo $this->escape($language ?? 'fr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="lang" content="<?php echo $this->escape($language ?? 'fr'); ?>">
    <title><?php echo $this->escape($pageTitle ?? 'OTTIS'); ?></title>
    <link rel="icon" type="image/png" href="/images/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/header.css">

    <?php if (isset($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $style): ?>
            <link rel="stylesheet" href="<?php echo $this->escape($style); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        window.pageData = <?php echo json_encode($jsVars ?? []); ?>;
        window.ROOM_TOKEN = '<?php echo htmlspecialchars($roomToken ?? '', ENT_QUOTES); ?>';
    </script>

    <script type="module" src="./js/lib/i18n.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script src="./js/lib/analytics.js"></script>

    <?php if (!empty($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <?php
            $isModule = (strpos($script, '/js/') !== false && strpos($script, 'analytics.js') === false && strpos($script, 'node_modules') === false);
            $typeAttr = $isModule ? 'type="module"' : '';
            ?>
            <script <?php echo $typeAttr; ?> src="<?php echo $this->escape($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body <?php echo isset($onloadScripts) ? 'onload="' . htmlspecialchars($onloadScripts, ENT_QUOTES) . '"' : ''; ?>>
    <header class="ottis-header">
        <nav class="ottis-nav" aria-label="Navigation principale">
            <div class="ottis-nav__brand">
                <a href="<?php echo $this->escape($pageDestination ?? './'); ?>" class="ottis-brand" aria-label="Accueil">
                    <span class="ottis-brand__logo">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                            <rect x="2" y="2" width="6" height="6" rx="1" fill="white" fill-opacity="0.9"/>
                            <rect x="10" y="2" width="6" height="6" rx="1" fill="white" fill-opacity="0.5"/>
                            <rect x="2" y="10" width="6" height="6" rx="1" fill="white" fill-opacity="0.5"/>
                            <rect x="10" y="10" width="6" height="6" rx="1" fill="white" fill-opacity="0.9"/>
                        </svg>
                    </span>
                    <span class="ottis-brand__name">OTTIS</span>
                </a>
            </div>

            <menu class="ottis-nav__menu">
                <li>
                    <a href="mailto:support-omist@univ-nantes.fr">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <rect x="1" y="3" width="14" height="10" rx="1.5" stroke="white" stroke-width="1.3"/>
                            <path d="M1 5l7 5 7-5" stroke="white" stroke-width="1.3"/>
                        </svg>
                        <span class="translate" data-key="nav.contact">Contact</span>
                    </a>
                </li>
                <li>
                    <a href="https://madoc.univ-nantes.fr/course/view.php?id=58282" target="_blank" rel="noopener noreferrer">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <circle cx="8" cy="8" r="6.5" stroke="white" stroke-width="1.3"/>
                            <path d="M8 7v5" stroke="white" stroke-width="1.4" stroke-linecap="round"/>
                            <circle cx="8" cy="5" r="0.8" fill="white"/>
                        </svg>
                        <span class="translate" data-key="nav.help">Aide sur Madoc</span>
                    </a>
                </li>
                <li id="language_element">
                    <label class="ottis-language" for="language-select">
                        <select id="language-select">
                            <option value="fr" <?php echo isset($language) && $language === 'fr' ? 'selected' : ''; ?>>FR</option>
                            <option value="en" <?php echo isset($language) && $language === 'en' ? 'selected' : ''; ?>>EN</option>
                        </select>
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M3 4.5l3 3 3-3" stroke="white" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </label>
                </li>
            </menu>
        </nav>

        <div id="firefox-warning" class="alert-banner">
            <p class="translate" data-key="index.alertBrowser">⚠️ Vous utilisez un navigateur non supporté. Certaines fonctionnalités peuvent ne pas s'afficher correctement. Nous vous recommandons d'utiliser <b>Chrome ou Edge</b> pour une expérience optimale.</p>
        </div>
    </header>

    <main class="ottis-main">
        <?php echo $content; ?>
    </main>

    <footer class="ottis-footer">
        <span class="translate" data-key="footer.copyright">© Nantes Université - 2026</span>
        <div class="ottis-footer__links">
            <span>•</span>
            <a href="#" class="translate" data-key="footer.mentions">Mentions légales</a>
            <span>•</span>
            <a href="#" class="translate" data-key="footer.accessibility">Accessibilité : partiellement conforme</a>
            <span>•</span>
            <a href="#" class="translate" data-key="footer.personalData">Données personnelles</a>
            <span>•</span>
            <a href="#" class="translate" data-key="footer.sitemap">Plan du site</a>
        </div>
    </footer>

    <?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
        <script src="/js/tests/no-jquery-validation.js"></script>
    <?php endif; ?>
</body>
</html>
