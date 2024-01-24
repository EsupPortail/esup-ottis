<?php 

// https://sebhastian.com/pass-javascript-variables-to-php/?utm_content=cmp-true

// $data = json_decode(file_get_contents("php://input"), true);

// echo . $data;

if (($_FILES["fileToUpload"]["tmp_name"] != "") && isset($_POST['newText'])) {

    $tmp_file = $_FILES['fileToUpload']['tmp_name'];
    $new_text = $_POST['newText'];

    $contenttxt = file_get_contents($tmp_file);
    $content = explode("\n",$contenttxt);
    //var_dump($content);
    $txtslidenotes="";

    for($i=0;$i<count($content);$i++) {
        $txt=$content[$i];
        if (strpos("_".$txt,"var slidesnotes = ")>0) {
            $txt_to_replace = $txt;
        }
    }
    // echo "Texte à remplacer \n";
    // echo $txt_to_replace;

    $txt_to_insert = "var slidesnotes = {" .$new_text. "}";
    // echo "Texte à insérer \n";
    // echo $txt_to_insert;
    $new_file = str_replace($txt_to_replace, $txt_to_insert, $content);
    //var_dump($txt_to_insert);

    $new_html = implode("\n",$new_file);
    //echo $new_html;
    //file_put_contents($tmp_file, $txt_to_insert);

    // $newFile = "tmp/test.html"
    // $open = fopen($newFile,'w');
    // fwrite($open,$txt_to_insert);
    // fclose($open);

    //$new_contenttxt = file_get_contents($tmp_file);

    echo json_encode($new_html);
}

// header('Content-Type: text/html');
// header('Content-Disposition: attachment; filename="output.html"');
// readfile('tmp/test.html');
// unlink('tmp/test.html');


?>