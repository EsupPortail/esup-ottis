<!DOCTYPE html>
<html lang="<?php echo $this->escape($language ?? 'fr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->escape($pageTitle ?? 'Extract Presentation Content'); ?></title>
    <link rel="icon" type="image/png" href="/images/favicon.svg">
    <link rel="stylesheet" href="./styles/index.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script type="module" src="/js/lib/i18n.js"></script>
    <script src="/js/lib/analytics.js"></script>
</head>
<body>
    <h1>Extract Content from Presentation</h1>
    <p>Format: <?php echo $this->escape($format ?? 'default'); ?></p>

    <form action="/extract/slides-notes" method="POST" enctype="multipart/form-data">
        <?php echo \App\Utils\Csrf::field(); ?>

        <input type="hidden" name="format" value="<?php echo $this->escape($format ?? 'default'); ?>">

        <div>
            <label for="file">Select file (PPTX, PPT, PDF, TXT):</label>
            <input type="file" id="file" name="file" accept=".pptx,.ppt,.pdf,.txt" required>
        </div>

        <button type="submit">Extract Content</button>
    </form>

    <div id="extraction-result"></div>

    <?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
        <script src="/js/tests/no-jquery-validation.js"></script>
    <?php endif; ?>
</body>
</html>
