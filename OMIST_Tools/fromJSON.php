<?php
    $filename=$argv[1];
    $paramname=$argv[2];
    $tab=json_decode(file_get_contents($filename),true);
    $value="";
    if (isset($tab[$paramname])) $value=$tab[$paramname];
    echo $value; 
?>