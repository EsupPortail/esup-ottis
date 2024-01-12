<?php

ini_set ( "upload_max_filesize" , "50M" );
ini_set ( "post_max_size" , "50M" );

if (isset($argc)) {
  $output = $argv[1];
  $filename = $argv[2];

  // echo "Test";

// $tmp_file =$filename

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
// // echo json_encode($notes);
// //unlink($filename);

// //Test pour refaire le .html

// //images
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



// $output = "tmp/output_" . $randompath . ".txt";
$ftest = fopen($output, "a");

fwrite($ftest, "Hello World");

// for($k=0;$k<count($images_recup);$k++) {
//   fwrite($ftest, $images_recup[$k] . "\n");
// }

// fclose($ftest);
// echo $remake_slidesnotes;

}
?>
