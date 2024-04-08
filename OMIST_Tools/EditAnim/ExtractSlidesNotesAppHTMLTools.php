<?php

ini_set ( "upload_max_filesize" , "50M" );
ini_set ( "post_max_size" , "50M" );

if (isset($_POST["uncloudlink"]) && trim($_POST["uncloudlink"]) != "") {
  $tmp_file="/var/www/html/uploads/uncloudfile_".md5(gmdate("U")).".html";
  $uncloudlink = $_POST["uncloudlink"];
  if (strpos($uncloudlink,"download") === false) $uncloudlink.="/download";
  $command = $proxy.'curl -q '.$uncloudlink.' -o '.$tmp_file;
  $out=exec($command);
} else {
  $tmp_file = $_FILES['fileToUpload']['tmp_name'];
}

$flag_justif=false;
$filename = "tmpfiles/test.html";

$contenttxt = file_get_contents($tmp_file);
$content = explode("\n",$contenttxt);
$txtslidenotes="";
$T_images = array();
for($i=0;$i<count($content);$i++) {
    $txt=$content[$i];
    if (strpos("_".$txt,"var slidesnotes = ")>0) {
      $txtslidenotes=str_replace("var slidesnotes = ","",$txt);
      $txtslidenotes=substr($txtslidenotes,0,strlen($txtslidenotes)-1);
    }
    if (strpos("_".$txt,"Timages.push(")>0) {
      $txtimage=str_replace("Timages.push('","",$txt);
      $txtimage=str_replace("');","",$txtimage);
      $T_images[] = $txtimage;
    }
}

$Tnotes = (array) json_decode($txtslidenotes);
$notes=array();

foreach($Tnotes as $lang => $noteslang) {
  $notes[$lang]=$noteslang;
  // break; 
}

$n=count($notes);
$T=array();
$T["Images"]=$T_images;
$T["Slide"]=0;
$T["Notes"]=array();
$notes["Img_slide"]=$T;

//print_r($notes);
echo json_encode($notes);
//unlink($filename);

?>
