<?php 

if (($_FILES["fileToUpload"]["tmp_name"] != "") && isset($_POST['newText']) && isset($_POST['newLicence'])) {
    $tmp_file = $_FILES['fileToUpload']['tmp_name'];
    $new_text = $_POST['newText'];
    $new_aspectratio = $_POST['newAspectRatio'];
    // $new_keepratio = $_POST['newKeepRatio'];
    $new_licence = $_POST['newLicence'];
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
        $command = 'python3 '.dirname(__FILE__).'/GetLicence.py --licence '.$new_licence ;
        $licence_to_insert = system($command);

        for($i=0;$i<count($new_file);$i++) {
            $line_body=$new_file[$i];
            if (strpos("_".$line_body,"function myalert(msg)")>0) {
                $line_licence = $new_file[$i-3];
                if ($line_licence != ""){
                    $licence_to_replace = $line_licence;
                }else{
                    $licence_to_replace = $new_file[$i-2];
                    $licence_to_insert = $licence_to_insert. "\n  <script>";
                }
            }
        }
        $new_file = str_replace($licence_to_replace, $licence_to_insert, $new_file);
    }else{
        $licence_to_insert = "";
        for($i=0;$i<count($new_file);$i++) {
            $line_body=$new_file[$i];
            if (strpos("_".$line_body,"function myalert(msg)")>0) {
                $line_licence = $new_file[$i-3];
                if ($line_licence != ""){
                    $licence_to_replace = $line_licence;
                }
            }
        $new_file = str_replace($licence_to_replace, $licence_to_insert, $new_file);
        }
    }

    $new_html = implode("\n",$new_file);
    echo json_encode($new_html);
}

?>