<?php 

// https://sebhastian.com/pass-javascript-variables-to-php/?utm_content=cmp-true

// $data = json_decode(file_get_contents("php://input"), true);

// echo . $data;

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

    $txt_to_insert = str_replace($txt_to_replace, $new_text, $content);
    file_put_contents($tmp_file, $txt_to_insert);

    // $newFile = "tmp/test.html"
    // $open = fopen($newFile,'w');
    // fwrite($open,$txt_to_insert);
    // fclose($open);

    $new_contenttxt = file_get_contents($tmp_file);

    echo json_encode($new_contenttxt);
}

// header('Content-Type: text/html');
// header('Content-Disposition: attachment; filename="output.html"');
// readfile('tmp/test.html');
// unlink('tmp/test.html');


?>