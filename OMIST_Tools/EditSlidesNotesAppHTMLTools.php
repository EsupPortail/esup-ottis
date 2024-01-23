<?php 

// https://sebhastian.com/pass-javascript-variables-to-php/?utm_content=cmp-true

// $data = json_decode(file_get_contents("php://input"), true);

// echo . $data;

if (($_FILES["fileToUpload"]["tmp_name"] != "") && isset($_POST['newText'])) {

    $tmp_file = $_FILES['fileToUpload']['tmp_name'];
    $new_text = $_POST['newText'];

    // echo $tmp_file;
    //echo $new_text;

    $contenttxt = file_get_contents($tmp_file);
    //echo $contenttxt;
    $content = explode("\n",$contenttxt);
    // $content = $contenttxt;
    $txtslidenotes="";

    for($i=0;$i<count($content);$i++) {
        $txt=$content[$i];
        if (strpos("_".$txt,"var slidesnotes = ")>0) {
            //echo "TEST SLIDESNOTES";
            $txt_to_replace = $txt;
            // echo "\n";
            // echo $txt_to_replace;
            // $long = strlen("var slidesnotes = ");
            // $txtslidenotes=substr($txt, $long);
            // $txtslidenotes = $txtslidenotes.$new_text;
            // $txtslidenotes=str_replace("var slidesnotes = ","",$txt);
            // $txtslidenotes=substr($txtslidenotes,0,strlen($txtslidenotes)-1);
        }
    }

    $txt_to_insert = str_replace($txt_to_replace, $new_text, $content);
    // echo "texte à insérer \n";
    //var_dump($txt_to_insert);
    file_put_contents($tmp_file, $txt_to_insert);

    // $newFile = "tmp/test.html"
    // $open = fopen($newFile,'w');
    // fwrite($open,$txt_to_insert);
    // fclose($open);

    // echo $newFile;

    // echo "\n Fichier initial \n";
    // echo $tmp_file;
    // echo "\n Fichier \n";
    // var_dump($tmp_file);
    $new_contenttxt = file_get_contents($tmp_file);
    // echo $new_contenttxt;

    //echo $txtslidenotes;
    // var_dump($content);
    // echo "\n";
    //echo $new_text;
    echo json_encode($new_contenttxt);


}else{
    echo "Test échoué";
}

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="output.html"');
readfile('tmp/test.html');
unlink('tmp/test.html');


?>