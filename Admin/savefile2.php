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

$Authorized_files=["../tables/tokens.json","../tables/authorizedStudents.txt","../tables/listAdminID.txt","../tables/test.txt","../tables/.htpasswd","../tables/mymdp.txt"];
if (in_array($filename, $Authorized_files)) {
/*    $res="";
    $fd=fopen($filename,"r");
    while($ligne=fgets($fd,4096)) $res.=$ligne."\n";
    fclose($fd);
    echo $res;
    */
    echo file_get_contents($filename);
} else {
    echo "Access forbidden !";
}

?>