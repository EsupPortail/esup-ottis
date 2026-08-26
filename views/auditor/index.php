<!doctype html>
<html lang="fr">
<head>
	<title>Automatic translation for teaching purposes</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <link rel="icon" type="image/png" href="./images/favicon.svg" >
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <?php foreach ($pageStyles as $style): ?>
        <link href="<?php echo $this->escape($style); ?>" rel="stylesheet">
    <?php endforeach; ?>
    
    <script>
        window.pageData = <?php echo json_encode($jsVars ?? []); ?>;
        var ROOM_TOKEN = '<?php echo htmlspecialchars($roomToken ?? '', ENT_QUOTES); ?>';
    </script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script src="/js/translatepage.js"></script>
    <script src="/js/matomo.js"></script>
    
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?php echo $this->escape($script); ?>"></script>
    <?php endforeach; ?>

</head>
<body>
    <input type="hidden" id="classroomid" value="<?php echo $this->escape($classroomid); ?>">
    <?php echo csrfField(); ?>

    <div id="auditor-container">
        <!-- Auditor interface will be initialized by JavaScript -->
    </div>

    <?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
        <script src="/js/tests/no-jquery-validation.js"></script>
    <?php endif; ?>
</body>
</html>
