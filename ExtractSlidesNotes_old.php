<?php
ini_set ( "upload_max_filesize" , "50M" );
ini_set ( "post_max_size" , "50M" );

require_once 'PHPPresentation-0.9.0/src/PhpPresentation/Autoloader.php';
\PhpOffice\PhpPresentation\Autoloader::register();
require_once 'Common-0.2.9/src/Common/Autoloader.php';
\PhpOffice\Common\Autoloader::register();


//set_time_limit(20);

use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Shape\RichText;

$tmp_file = $_FILES['ppt_presentation']['tmp_name'];

$flag_justif=false;
$filename = "tmpfiles/test.pptx";
//echo $tmp_file."\n".$filename."\n";
//print_r($_FILES);
$realfilename = $_FILES['ppt_presentation']['name'];
$T_filename = explode(".", $realfilename);
 $ext = end($T_filename);
 
 if ($ext == "pptx") 
    $pptReader = IOFactory::createReader('PowerPoint2007'); 
else {
    $filename = "tmpfiles/test.odp";
    $pptReader = IOFactory::createReader('ODPresentation');
    
}
if( is_uploaded_file($tmp_file) ) {
    if (!move_uploaded_file($tmp_file, $filename)) {
        echo "ERROR UPLOAD !!!!"; exit;
    }
} else {
    echo "ERROR PB UPLOAD !!!!"; exit;
}







//$pptReader = IOFactory::createReader('PowerPoint97');
$oPHPPresentation = $pptReader->load($filename);

/*
// Create odp for slideshow with ViewerJS
$ACCESS = "RIEN";
if (isset($_POST['ACCESS'])) $ACCESS = $_POST['ACCESS'];
$filenameOUT = "tmpfiles/DOCUMENT_".$ACCESS.".odp";

$oWriter = IOFactory::createWriter($oPHPPresentation, 'ODPresentation');
$oWriter->save($filenameOUT);
*/
//echo "Nombre de slides = ".$oPHPPresentation->getSlideCount()."\n";
$fin = $oPHPPresentation->getSlideCount();

$slidesWidthNotes=array();

for($i=0; $i<$fin; $i++) {
    $slide = $oPHPPresentation->getSlide($i); //->getNote()->getShapeCollection()->getIterator();
    
    $notes = $slide->getNote();
    if (count($notes->getShapeCollection())>0) $slidesWidthNotes[]=$i+1;
}

$textNotes=array();
$fd=zip_open($filename);
do {
    $entry = zip_read($fd);
    if (is_resource($entry) && strpos(zip_entry_name($entry),"t/notesSlides/notes") > 0) {
        $entryname = zip_entry_name($entry);
        $number = 1*(str_replace(".xml","",str_replace("ppt/notesSlides/notesSlide","",$entryname) ))-1;
        $slidenumber = $slidesWidthNotes[$number];
        zip_entry_open($fd, $entry, "r");
        $note = zip_entry_read($entry, zip_entry_filesize($entry));
        $textonly = trim(strip_tags(str_replace("</a:t>","\n</a:t>",$note)));
        zip_entry_close($entry);
//        echo "Slide #".$slidenumber."\n----------\n".$textonly."\n";
        $textNotes[] = array("Slide"=>$slidenumber,"Notes"=>explode("\n",$textonly));
    }
} while ($entry);
zip_close($fd);

echo json_encode($textNotes);



//unlink($filename);

?>