<?php 

// https://sebhastian.com/pass-javascript-variables-to-php/?utm_content=cmp-true

// $data = json_decode(file_get_contents("php://input"), true);

// echo . $data;

if (($_FILES["fileToUpload"]["tmp_name"] != "") && isset($_POST['newText'])) {

    $tmp_file = $_FILES['fileToUpload']['tmp_name'];
    $new_text = $_POST['newText'];

    // echo $tmp_file;
    echo $new_text;

    $contenttxt = file_get_contents($tmp_file);
    //echo $contenttxt;
    $content = explode("\n",$contenttxt);
    // $content = $contenttxt;
    $txtslidenotes="";

    for($i=0;$i<count($content);$i++) {
        $txt=$content[$i];
        if (strpos("_".$txt,"var slidesnotes = ")>0) {
            echo "TEST SLIDESNOTES";
            $txt_to_replace = $txt;
            echo "\n";
            echo $txt_to_replace;
            // $long = strlen("var slidesnotes = ");
            // $txtslidenotes=substr($txt, $long);
            // $txtslidenotes = $txtslidenotes.$new_text;
            // $txtslidenotes=str_replace("var slidesnotes = ","",$txt);
            // $txtslidenotes=substr($txtslidenotes,0,strlen($txtslidenotes)-1);
        }
    }

    $txt_to_insert = str_replace($txt_to_replace, $new_text, $content);
    echo "texte à insérer \n";
    //var_dump($txt_to_insert);
    file_put_contents($tmp_file, $txt_to_insert);

    // $newFile = "test.html"
    // $open = fopen($newFile,'w');
    // fwrite($open,$txt_to_insert);
    // fclose($open);

    // echo $newFile;

    echo "\n Fichier initial \n";
    echo $tmp_file;
    echo "\n Fichier \n";
    // var_dump($tmp_file);
    $new_contenttxt = file_get_contents($tmp_file);
    echo $new_contenttxt;

    //echo $txtslidenotes;
    // var_dump($content);
    // echo "\n";
    //echo $new_text;


}else{
    echo "Test échoué";
}

// $tmp_file = $_FILES['fileToUpload']['tmp_name'];

// echo $tmp_file;


// $texte = "Hello World bis";

// var_dump($text);
// echo $input_file;

// $contenttxt = file_get_contents($input_file, true);
// echo $contenttxt;
// $content = explode("\n",$contenttxt);
// $txtslidenotes="";
// $T_images = array();
// for($i=0;$i<count($content);$i++) {
//     $txt=$content[$i];
//     if (strpos("_".$txt,"var slidesnotes = ")>0) {
//       $txtslidenotes=str_replace("var slidesnotes = ","",$txt);
//       $txtslidenotes=substr($txtslidenotes,0,strlen($txtslidenotes)-1);
//     }
// }

// echo $txtslidenotes;

// //echo $contenttxt

// // $jqueryVariable = $_POST['variable'];
// // echo $jqueryVariable;

// header('Content-Type: application/zip');
// header('Content-Disposition: attachment; filename="output.zip"');
// readfile('tmp/output_'.$randompath.'.zip');
// unlink('tmp/output_'.$randompath.'.zip');

?>


<?php

// ini_set ( "upload_max_filesize" , "50M" );
// ini_set ( "post_max_size" , "50M" );

// if (isset($argc)) {
//   $output = $argv[1];
//   $filename = $argv[2];

// $tmp_file = $_FILES['ppt_presentation']['tmp_name'];

//   // echo "Test";

// // $tmp_file =$filename

// $flag_justif=false;

// $contenttxt = file_get_contents($tmp_file);
// $content = explode("\n",$contenttxt);
// $txtslidenotes="";
// $T_images = array();
// for($i=0;$i<count($content);$i++) {
//     $txt=$content[$i];
//     if (strpos("_".$txt,"var slidesnotes = ")>0) {
//       $txtslidenotes=str_replace("var slidesnotes = ","",$txt);
//       $txtslidenotes=substr($txtslidenotes,0,strlen($txtslidenotes)-1);
//     }
//     if (strpos("_".$txt,"Timages.push(")>0) {
//       $txtimage=str_replace("Timages.push('","",$txt);
//       $txtimage=str_replace("');","",$txtimage);
//       $T_images[] = $txtimage;
//     }
// }

// echo $txtslidenotes;

// $Tnotes = (array) json_decode($txtslidenotes);
// $notes=array();

// // Récupération de la première langue des notes uniquement.
// foreach($Tnotes as $lang => $noteslang) {
//   $notes=$noteslang;
//   break; 
// }

// $n=count($notes);
// $T=array();
// $T["Images"]=$T_images;
// $T["Slide"]=0;
// $T["Notes"]=array();
// $notes[$n]=$T;

// //print_r($notes);
//  echo json_encode($notes);
// //unlink($filename);

//Test pour refaire le .html

//images
// for($i=0;$i<$n;$i++) {
//   $nom_image = $notes[$n]["Images"][$i];
//   $images_recup[$i] = "Timages.push('" . $nom_image . "');";
// }
// // echo $images_recup[0];

// //recup langues
// $lang_remake=array();
// $col = 0;
// foreach($Tnotes as $lang_cherchee => $lang_trouvee) {
//   $lang_remake[$col]=$lang_cherchee;
//   $col = $col+1;
// }

// //texte des notes
// $remake_txtslidenotes = "[";
// for($i=0;$i<$n;$i++) {
//   if ($i==0){
//     $txt_encode = json_encode($notes[$i]);
//     $remake_txtslidenotes = $remake_txtslidenotes . $txt_encode;
//   } else {
//     $txt_encode = json_encode($notes[$i]);
//     $remake_txtslidenotes = $remake_txtslidenotes . ", " . $txt_encode;
//   }
// }
// $remake_txtslidenotes = $remake_txtslidenotes . "]";

// $remake_slidesnotes = "{";
// for ($j=0;$j<count($lang_remake);$j++){
//   if ($j==0){
//     $remake_slidesnotes = $remake_slidesnotes . '"' . $lang_remake[$j] . '" : ' . $remake_txtslidenotes ;
//   } else {
//     $remake_slidesnotes = $remake_slidesnotes . ', "' . $lang_remake[$j] . '" : ' . $remake_txtslidenotes ;
//   }

// }
// $remake_slidesnotes = $remake_slidesnotes . '};' ;



// // $output = "tmp/output_" . $randompath . ".txt";
// $ftest = fopen($output, "a");

// fwrite($ftest, $remake_slidesnotes . "\n");

// for($k=0;$k<count($images_recup);$k++) {
//   fwrite($ftest, $images_recup[$k] . "\n");
// }

// fclose($ftest);
// // echo $remake_slidesnotes;

// }
?>
