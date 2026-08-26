<?php 

$tmp_file = $_FILES['ppt_presentation']['tmp_name'];

$flag_justif=false;
$filename = "tmpfiles/test.pptx";


if( is_uploaded_file($tmp_file) ) {
    if (!move_uploaded_file($tmp_file, $filename)) {
        echo "ERROR UPLOAD !!!!"; exit;
    }
}


$contenttxt = file_get_contents($filename);
$contenttxt = $contenttxt."\n--------------END----------";
$content = explode("\n",$contenttxt);
$notes=array();
$num=0;
$notesslide=array();
for($i=0;$i<count($content);$i++) {
    $txt=$content[$i];
    if ((strpos("_".$txt,"----------")>0) || (strpos("_".$txt,"[forward]")>0)) {
	if ($num>0) {
           $slidenotes=array();
           $slidenotes["Slide"]=$num;
           $slidenotes["Notes"]=str_replace("\r","",$notesslide);
           $notes[]=$slidenotes;
           $notesslide=array();
	}
        $num++;
     } else {
        $notesslide[]=$txt;
     }
}

echo json_encode($notes);
unlink($filename);

?>
