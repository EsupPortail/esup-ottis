<?php 
include("Security/referer.php");

//require_once 'PHPPresentation-0.9.0/src/PhpPresentation/Autoloader.php';
//\PhpOffice\PhpPresentation\Autoloader::register();
//require_once 'Common-0.2.9/src/Common/Autoloader.php';
//\PhpOffice\Common\Autoloader::register();


//set_time_limit(20);

//use PhpOffice\PhpPresentation\IOFactory;
//use PhpOffice\PhpPresentation\Slide;
//use PhpOffice\PhpPresentation\Shape\RichText;

$tmp_file = $_FILES['ppt_presentation']['tmp_name'];

$flag_justif=false;
$filename = "tmpfiles/test.pptx";
//echo $tmp_file."\n".$filename."\n";
//print_r($_FILES);


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
//	echo "DEBUG : ".$txt."\n";
     } else {
        $notesslide[]=$txt;
     }
}

//print_r($notes);
echo json_encode($notes);
unlink($filename);

?>
