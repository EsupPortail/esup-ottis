<?php
exit();
// Edit the following with your own configuration. Functionnality disabled in the public version
$mysite = "automatictranslator.sciences.univ-nantes.fr"; 
if (! isset($_SERVER['HTTP_REFERER'])) {
    header("Location: https://automatictranslator.sciences.univ-nantes.fr");
    exit();
}

$referer = $_SERVER['HTTP_REFERER'];

if ($referer == "") {
    $domain = $mysite;
} else {
    $domain = parse_url($referer);
}

if($domain['host'] != $mysite) {
    header("Location: https://automatictranslator.sciences.univ-nantes.fr");
    exit();
} 

?>