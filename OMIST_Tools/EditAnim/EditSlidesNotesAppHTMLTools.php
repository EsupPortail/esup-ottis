<?php 

if (($_FILES["fileToUpload"]["tmp_name"] != "") && isset($_POST['newText'])) {

    $tmp_file = $_FILES['fileToUpload']['tmp_name'];
    $new_text = $_POST['newText'];
    $new_aspectratio = $_POST['newAspectRatio'];
    $new_keepratio = $_POST['newKeepRatio'];
    $new_delay = $_POST['newDelay'];
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
    //$new_html = implode("\n",$new_file);

    //Aspect ratio
    if ($new_aspectratio != "16/10"){
        for($i=0;$i<count($new_file);$i++) {
            $line_ratio=$new_file[$i];
            if (strpos("_".$line_ratio,"var aspectratio = ")>0) {
                $ratio_to_replace = $line_ratio;
            }
        }
    }

    $ratio_to_insert = 'var aspectratio = eval("' .$new_aspectratio. '");';
    $new_file_ratio = str_replace($ratio_to_replace, $ratio_to_insert, $new_file);
    $new_html = implode("\n",$new_file_ratio);

    echo json_encode($new_html);
}

?>