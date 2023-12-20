<?php
    $filename=$argv[1];
    $paramname=$argv[2];
    $tab=array(json_decode(file_get_contents($filename)));
    $value="";
    if (isset($tab[$paramname])) $value=$tab[$paramname];

    print_r($tab);
    echo "filename = ".$filename."\n";

    echo "paramname = ".$paramname."\n";

    echo $value;
?>