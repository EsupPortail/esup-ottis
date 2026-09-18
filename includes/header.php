<?php
require_once __DIR__ . '/../vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language ?? 'fr') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="lang" content="<?= htmlspecialchars($language ?? 'fr') ?>">
    <title><?php echo e($pageTitle ?? 'OTTIS'); ?></title>
    <link rel="icon" type="image/png" href="./images/favicon.svg">
    <link rel="stylesheet" href="./styles/header.css">

    <!-- CSS spécifiques à la page -->
    <?php if (isset($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $style): ?>
            <link rel="stylesheet" href="<?php echo e($style); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

   <!-- Injection des variables PHP dans JavaScript -->
    <script>
        window.pageData = <?php echo json_encode($jsVars ?? []); ?>;
        var ROOM_TOKEN = '<?php echo htmlspecialchars($roomToken ?? '', ENT_QUOTES); ?>';
    </script>

    <script type="module" src="./js/lib/i18n.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script src="./js/lib/analytics.js"></script>
    <!-- JS spécifiques à la page -->
    <?php if (!empty($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <?php
            $isModule = (strpos($script, '/js/lib/') !== false && strpos($script, 'analytics.js') === false);
            $typeAttr = $isModule ? 'type="module"' : '';
            ?>
            <script <?php echo $typeAttr; ?> src="<?php echo e($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>




</head>
<body <?php echo isset($onloadScripts) ? 'onload="' . htmlspecialchars($onloadScripts, ENT_QUOTES) . '"' : ''; ?>>
    <header>
        <nav>
            <!-- <a href="https://omist-preprod.intra.univ-nantes.fr/"><img id="logoNU" src="./images/omist-top-logo-nav_ssfd.png" alt="Logo Nantes Université"></a> -->
            <a href="<?php echo e($pageDestination ?? './'); ?>"><img id="logoNU" src="./images/omist-top-logo-nav_ssfd.png" alt="Logo Nantes Université"></a>
                <menu>
                    <li><a href="mailto:support-omist@univ-nantes.fr" style="text-decoration: none" ><i class="fa fa-envelope fa-2x" id="iconeAideEmail"></i><span class="icone-menu">Contact</span></a></li>
                    <li><a href="https://madoc.univ-nantes.fr/course/view.php?id=58282" target="_blank" style="text-decoration: none" ><i class="fa fa-question-circle fa-2x" id="iconeAideMadoc"></i><span class="icone-menu translate" data-key="scenarios.aide">Aide sur Madoc</span></a></li>
                    <li id="language_element">
                        <select id="language-select" onchange="changeLanguage(this.value)">
                            <option value="fr" <?= ($language ?? 'fr') === 'fr' ? 'selected' : '' ?>>Français</option>
                            <option value="en" <?= ($language ?? 'fr') === 'en' ? 'selected' : '' ?>>English</option>
                        </select>
                    </li>
                </menu>
        </nav>
        <div id="firefox-warning" class="alert-banner">
            <p class="translate" data-key="index.alertBrowser">⚠️ Vous utilisez un navigateur non supporté. Certaines fonctionnalités peuvent ne pas s'afficher correctement. Nous vous recommandons d'utiliser <b>Chrome ou Edge</b> pour une expérience optimale.</p>
        </div>
    </header>
    <main>