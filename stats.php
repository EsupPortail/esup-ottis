<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
?>
<html lang="fr">
<head>
	<title>Automatic translation for teaching purposes: Teacher</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="favicon.png" />

</head>
<body>

<?php
include("config.php");


$res=array();
exec($proxy." curl https://api.deepl.com/v2/usage?auth_key=".$DEEPLAPITOKEN,$res);

$Stats_DEEPL = json_decode($res[0],true);
$percentage_DEEPL = ceil(10000*$Stats_DEEPL["character_count"]/$Stats_DEEPL["character_limit"])/100;


echo "<h1>Usage Statistics</h1>";
echo "<dl>";
echo "<dt>DeepL pro</dt>";
echo "<dd>".$percentage_DEEPL."% : Characters = ".$Stats_DEEPL["character_count"]." , Limit = ".$Stats_DEEPL["character_limit"]."</dd>\n";
echo "</dl>";
?>

</body>
</html>
