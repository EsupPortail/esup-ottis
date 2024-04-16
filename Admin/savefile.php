<?php

include("../Security/Authentification.php");
include("../Security/referer.php");

$uid="";
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="anonymous"; //$uid="e18XXX"; // en attendant de rétablir le CAS....
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

$filenameids = "../tables/authorizedStudents.txt";
AuthenticateNotStudentorReader($uid,$filenameids);

$userprivileges=getPrivilege($uid,"../tables/listAdminID.txt",2);
if ($userprivileges>1) {
    echo "Access forbidden !";
    exit;
}

if (! isset($_POST["filename"])) {
    echo "Access forbidden !";
    exit;
}

$filename=$_POST["filename"];
$content=$_POST["filecontent"];

$filename="../tables/test.txt";
$content="Test\n\nEncore\n";

$fd=fopen($filename,"w");
fputs($fd,$content);
fclose($fd);

?>
