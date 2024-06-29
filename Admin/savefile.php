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


$data = json_decode(trim(file_get_contents("php://input")),true);

$filename=$data["filename"];
$content=$data["filecontent"];


//$content="Filename=".$filename."\n\nContent : ".$content;

//$filename="../tables/test.txt";
$Authorized_files=["../tables/tokens.json","../tables/authorizedStudents.txt","../tables/listAdminID.txt","../tables/test.txt","../tables/.htpasswd","../tables/mymdp.txt"];
if (in_array($filename, $Authorized_files)) {
    $fd=fopen($filename,"w");
    fputs($fd,$content);
    fclose($fd);
} else {
    echo "Access forbidden !";
}

?>
