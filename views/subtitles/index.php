<!DOCTYPE html>
<html lang="<?php echo $this->escape($language ?? 'fr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->escape($pageTitle ?? 'Omist - Subtitles'); ?></title>
    <link rel="icon" type="image/png" href="./images/favicon.svg">
    
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?php echo $this->escape($style); ?>">
    <?php endforeach; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script src="/js/translatepage.js"></script>
    <script src="/js/matomo.js"></script>
    
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?php echo $this->escape($script); ?>"></script>
    <?php endforeach; ?>
</head>
<body>
    <?php echo csrfField(); ?>
    
    <div id="subtitles-container">
        <h1>Subtitles</h1>
        <div id="subtitles-display"></div>
    </div>

    <?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
        <script src="/js/tests/no-jquery-validation.js"></script>
    <?php endif; ?>
</body>
</html>
