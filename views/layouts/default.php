<!DOCTYPE html>
<html lang="<?php echo $this->escape($language); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="lang" content="<?php echo $this->escape($language); ?>">
    <title><?php echo $this->escape($pageTitle ?? 'OTTIS'); ?></title>
    <link rel="icon" type="image/png" href="/images/favicon.svg">
    <link rel="stylesheet" href="./styles/header.css">

    <!-- CSS spécifiques à la page -->
    <?php if (isset($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $style): ?>
            <link rel="stylesheet" href="<?php echo $this->escape($style); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Injection des variables PHP dans JavaScript -->
    <script>
        window.pageData = <?php echo json_encode($jsVars ?? []); ?>;
        window.ROOM_TOKEN = '<?php echo htmlspecialchars($roomToken ?? '', ENT_QUOTES); ?>';
    </script>

    <script type="module" src="./js/lib/i18n.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script src="./js/lib/analytics.js"></script>
    <!-- Configure PDF.js worker before loading PDF.js -->
    <script>
        // Configure PDF.js GlobalWorkerOptions before PDF.js is loaded
        if (typeof pdfjsLib === 'undefined' && typeof pdfjsDist === 'undefined') {
            window.pdfjsLib = { GlobalWorkerOptions: {} };
        }
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
            pdfjsLib.GlobalWorkerOptions.cMapUrl = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/cmaps/';
            pdfjsLib.GlobalWorkerOptions.cMapPacked = true;
        }
    </script>
    <!-- JS spécifiques à la page -->
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
    <header>
        <nav>
            <a href="<?php echo $this->escape($pageDestination ?? './'); ?>"><img id="logoNU" src="./images/omist-top-logo-nav_ssfd.png" alt="Logo Nantes Université"></a>
                <menu>
                    <li><a href="mailto:support-omist@univ-nantes.fr" style="text-decoration: none" ><i class="fa fa-envelope fa-2x" id="iconeAideEmail"></i><span class="icone-menu">Contact</span></a></li>
                    <li><a href="https://madoc.univ-nantes.fr/course/view.php?id=58282" target="_blank" style="text-decoration: none" ><i class="fa fa-question-circle fa-2x" id="iconeAideMadoc"></i><span class="icone-menu translate" data-key="scenarios.aide">Aide sur Madoc</span></a></li>
                    <li id="language_element">
                        <select id="language-select" onchange="changeLanguage(this.value)">
                            <option value="fr" <?php echo isset($language) && $language === 'fr' ? 'selected' : ''; ?>>Français</option>
                            <option value="en" <?php echo isset($language) && $language === 'en' ? 'selected' : ''; ?>>English</option>
                        </select>
                    </li>
                </menu>
        </nav>
        <div id="firefox-warning" class="alert-banner">
            <p class="translate" data-key="index.alertBrowser">⚠️ Vous utilisez un navigateur non supporté. Certaines fonctionnalités peuvent ne pas s'afficher correctement. Nous vous recommandons d'utiliser <b>Chrome ou Edge</b> pour une expérience optimale.</p>
        </div>
    </header>
    <main>
        <?php echo $content; ?>
    </main>
    <footer>
        <!-- Common footer -->
    </footer>
    <?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
        <script src="/js/tests/no-jquery-validation.js"></script>
    <?php endif; ?>
</body>
</html>
