<?php
include("Security/referer.php");

if (isset($_POST["from"])) $from = $_POST["from"]; else $from = "";
if (isset($_POST["to"])) $to = $_POST["to"]; else $to = "";
if (isset($_POST["subject"])) $subject = $_POST["subject"]; else $subject = "";
if (isset($_POST["body"])) $body = $_POST["body"]; else $body = "";

$body = str_replace("<p>","",$body);
$body = str_replace("</p>","",$body);

$mailapplication = "Ne pas répondre <bourdon-j@univ-nantes.fr>";

if ($to != "") {
    $body = str_replace("<br>","<br>\r\n",str_replace("<br/>","<br/>\r\n",$body));
    mail($to,$subject,$body,"From: ".$mailapplication."\r\nReply-To: ".$from."\r\n"."Content-type: text/html; charset= utf8\r\n");
}

?>
