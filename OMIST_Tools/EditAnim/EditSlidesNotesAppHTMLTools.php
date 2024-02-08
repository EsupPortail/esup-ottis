<?php 

if (($_FILES["fileToUpload"]["tmp_name"] != "") && isset($_POST['newText']) && isset($_POST['newLicence'])) {
    $tmp_file = $_FILES['fileToUpload']['tmp_name'];
    $new_text = $_POST['newText'];
    $new_aspectratio = $_POST['newAspectRatio'];
    // $new_keepratio = $_POST['newKeepRatio'];
    $new_licence = $_POST['newLicence'];
    //print($new_licence);
    $contenttxt = file_get_contents($tmp_file);
    $content = explode("\n",$contenttxt);
    $txtslidenotes="";

    //Notes
    for($i=0;$i<count($content);$i++) {
        $txt=$content[$i];
        if (strpos("_".$txt,"var slidesnotes = ")>0) {
            $txt_to_replace = $txt;
        }
    }
 
    $txt_to_insert = "var slidesnotes = {" .$new_text. "};";
    $new_file = str_replace($txt_to_replace, $txt_to_insert, $content);
    //$new_html = implode("\n",$new_file);

    //Aspect ratio
    if ($new_aspectratio != "16/10"){
        for($i=0;$i<count($new_file);$i++) {
            $line_ratio=$new_file[$i];
            if (strpos("_".$line_ratio,"var aspectratio = ")>0) {
                $ratio_to_replace = $line_ratio;
            }
        }
    $ratio_to_insert = 'var aspectratio = eval("' .$new_aspectratio. '");';
    $new_file = str_replace($ratio_to_replace, $ratio_to_insert, $new_file);
    }

    // //Keep ratio -> pas utilisé ?
    // // if ($new_keepratio != "X"){
    // //     for($i=0;$i<count($new_file);$i++) {
    // //         $line_keepratio=$new_file[$i];
    // //         if (strpos("_".$line_keepratio,"var aspectratio = ")>0) {
    // //             $ratio_to_replace = $line_ratio;
    // //         }
    // //     }
    // // $ratio_to_insert = 'var aspectratio = eval("' .$new_aspectratio. '");';
    // // $new_file = str_replace($ratio_to_replace, $ratio_to_insert, $new_file);
    // // }

    //Licence
    if ($new_licence != ""){
        echo $new_licence;
        $command = 'python3 '.dirname(__FILE__).'/GetLicence.py '.$new_licence ;
        $output = system($command);
        echo $output;

        // $new_file = $test + $new_file
        // $script = __DIR__ . DIRECTORY_SEPARATOR . "GetLicence.py" ;
        // $result = shell_exec("python $script --licence $new_licence");
        // echo $result;
        // // $command = $PYTHON .dirname(__FILE__).'/GetLicence.py '.$new_licence.' >/dev/null 2>/dev/null';
        // // system($command);
        // // $filename = "../CreateApp/Templates/Complements.txt";
        // // $f = fopen($filename, "r");
        // // $f_read = fread($f, filesize($filename));
        // // $complement = join("",$f_read);

        // for($i=0;$i<count($new_file);$i++) {
        //     $line_body=$new_file[$i];
        //     if (strpos("_".$line_body,"</body>")>0) {
        //         $line_licence = $new_file[$i-1];
        //         $licence_to_replace = $line_licence;
        //     }
        // }
        // $licence_to_insert = $result;
        // $new_file = str_replace($licence_to_replace, $licence_to_insert, $new_file);
        // print($new_file);
    }
    // $test = "LICENCE : " + $new_licence;
    // $new_file += $test;
    $new_html = implode("\n",$new_file);

    //echo json_encode($new_html);
}

?>