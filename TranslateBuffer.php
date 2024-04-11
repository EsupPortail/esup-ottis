<?php
include("Security/referer.php");

include "config.php";

$texte = html_entity_decode(strip_tags($_GET["texte"]));
$output = html_entity_decode(strip_tags($_GET["output"]));
$lang = $_GET["lang"];
$langinput = $_GET["langinput"];
if (isset($_GET["lineid"])) $lineid=$_GET["lineid"]; else $lineid="NONE";
if (isset($_GET["forcelibre"])) $WhichTranslator="LIBRETRANSLATE";

$resultjson = '{   "data": { "translations": [ {"translatedText":"'.$output.'"] } }';
echo $resultjson;
?>