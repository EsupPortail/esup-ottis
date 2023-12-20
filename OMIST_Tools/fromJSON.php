<?php
    $filename=$argv[1];
    $paramname=$argv[2];
    $tab=array(json_decode(file_get_contents($filename)));
    $value="";
    if (isset($tab[$paramname])) $value=$tab[$paramname];


    echo $value;
?>