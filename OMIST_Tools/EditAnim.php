<?php

if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {
    
    // $command = 'bash ./CreateAppOnly.sh "'.$_FILES["fileToUpload"]["tmp_name"].'" '.$randompath.' >/dev/null 2>/dev/null';
    // system($command);
    
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="output.html"');
    readfile('tmp/output_'.$randompath.'.html');
    unlink('tmp/output_'.$randompath.'.html');
    }
    

?>
