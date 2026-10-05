<!DOCTYPE html>
<html lang="<?php echo $this->escape($language ?? 'fr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->escape($pageTitle ?? 'Upload Presentation'); ?></title>
    <link rel="icon" type="image/png" href="/images/favicon.svg">
    <link rel="stylesheet" href="./styles/index.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script type="module" src="/js/lib/i18n.js"></script>
    <script src="/js/lib/analytics.js"></script>
</head>
<body>
    <h1>Upload Presentation File</h1>

    <form action="/upload/handle" method="POST" enctype="multipart/form-data">
        <?php echo \App\Utils\Csrf::field(); ?>

        <div>
            <label for="file">Select file (PPTX, PDF, TXT):</label>
            <input type="file" id="file" name="file" accept=".pptx,.pdf,.txt" required>
        </div>

        <button type="submit">Upload</button>
    </form>

    <div id="upload-status"></div>

    <?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
        <script src="/js/tests/no-jquery-validation.js"></script>
    <?php endif; ?>
</body>
</html>
