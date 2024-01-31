<?php 

if (($_FILES["fileToUpload"]["tmp_name"] != "") && isset($_POST['newText'])) {

    $tmp_file = $_FILES['fileToUpload']['tmp_name'];
    $new_text = $_POST['newText'];

    $contenttxt = file_get_contents($tmp_file);
    $content = explode("\n",$contenttxt);
    $txtslidenotes="";

    for($i=0;$i<count($content);$i++) {
        $txt=$content[$i];
        if (strpos("_".$txt,"var slidesnotes = ")>0) {
            $txt_to_replace = $txt;
        }
    }
 
    $txt_to_insert = "var slidesnotes = {" .$new_text. "};";
    $new_file = str_replace($txt_to_replace, $txt_to_insert, $content);
    $new_html = implode("\n",$new_file);
    echo json_encode($new_html);
}

?>