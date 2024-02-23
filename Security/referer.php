<?php
$mysite = "automatictranslator.sciences.univ-nantes.fr"; 
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