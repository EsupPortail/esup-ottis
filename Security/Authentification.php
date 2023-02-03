<?php

function readfileid($filename) {
    $fd=fopen($filename,"r");
    $T_ids=array();
    while($ligne = fgets($fd,4096)) {
        $ligne=trim($ligne);
        if (strlen($ligne)>1) $T_ids[]=strtolower($ligne);
    }
    return $T_ids;
}

function Authenticate($id,$filenameids) {
    $id=strtolower($id);
    $T_ids = readfileid($filenameids);
    if (! in_array($id,$T_ids)) {
        echo "<p>".$id.", vous n'êtes pas autorisé à consulter cette page</p>";
        exit;
    }
}

function isNumber($c) {
    return (ord($c)>=48 && ord($c)<=57);
}

function AuthenticateNotStudent($id) {
    $id=strtolower($id);
    if (substr($id,0,1) == "e" && isNumber(substr($id,1,1))) {
        echo "<p>".$id.", vous n'êtes pas autorisé à consulter cette page</p>";
        exit;
    }
}
 

function AuthenticateNotStudentorReader($id,$filenameids) {
    $id=strtolower($id);
    $T_ids = readfileid($filenameids);
    if (! in_array($id,$T_ids)) {
        if (substr($id,0,1) == "e" && isNumber(substr($id,1,1))) {
            echo "<p>".$id.", vous n'êtes pas autorisé à consulter cette page</p>";
            exit;
        }
    }
}

function IsAStudent($id) {
    $id=strtolower($id);
    return (substr($id,0,1) == "e" && isNumber(substr($id,1,1)));
}

function getPrivilege($uid,$fileid,$default=3) {
    $res=$default;
    $id=strtolower($uid);
    $T_ids = readfileid($fileid);
    for($i=0; $i<count($T_ids);$i++) {
        $Tuser=explode(";",$T_ids[$i]);
        if ($Tuser[0] == $id) $res=1*$Tuser[1];
    }
    return $res;
}

?>
