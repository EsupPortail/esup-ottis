<?php
ini_set ( "upload_max_filesize" , "50M" );
ini_set ( "post_max_size" , "50M" );

$tmp_file = $_FILES['ppt_presentation']['tmp_name'];

$randompath=md5(gmdate("U"));

$command = 'cd OMIST_Tools; bash ./CreateAppOnly.sh "'.$tmp_file.'" '.$randompath.' >/dev/null 2>/dev/null';
system($command);

$filename='OMIST_Tools/tmp/output_'.$randompath.'.html';


$contenttxt = file_get_contents($filename);
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
  $notes=$noteslang;
  break;
}

$n=count($notes);
$T=array();
$T["Images"]=$T_images;
$T["Slide"]=0;
$T["Notes"]=array();
$notes[$n]=$T;

//print_r($notes);
echo json_encode($notes);

unlink($filename);


?>
