<html>
    <head></head>
    <body>
    <?php
    if (isset($_GET["file"])) {
    echo "<img src=\"".$_GET["file"]."#toolbar=0\" width=\"100%\" height=\"500px\"/> ";
    }
    ?>
    </body>
</html>