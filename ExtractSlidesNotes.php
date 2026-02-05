<?php 
include("Security/referer.php");

ini_set ( "upload_max_filesize" , "50M" );
ini_set ( "post_max_size" , "50M" );

$tmp_file = $_FILES['ppt_presentation']['tmp_name'];

/*

$flag_justif=false;
$filename = "tmpfiles/test.pptx";

if( is_uploaded_file($tmp_file) ) {
    if (!move_uploaded_file($tmp_file, $filename)) {
        echo "ERROR UPLOAD !!!!"; exit;
    }
} else {
    echo "ERROR PB UPLOAD !!!!"; exit;
}
*/
if ($_FILES["ppt_presentation"]["tmp_name"] != "") {
$randompath=md5(gmdate("U"));

$command = 'cd OMIST_Tools; bash ./ExtractSpeech/ExtractSpeechFromPPTX.sh '.$randompath.' "'.$tmp_file.'" >/dev/null 2>/dev/null';//>/dev/null 2>/dev/null
system($command);

$filename='OMIST_Tools/tmp/speech_'.$randompath.'.txt';

$contenttxt = file_get_contents($filename);
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
           $slidenotes["Notes"]=$notesslide;
           $notes[]=$slidenotes;
           $notesslide=array();
	}
        $num++;
	//echo "DEBUG : ".$txt."\n";
     } else {
        $notesslide[]=$txt;
     }
}
if (count($notesslide)>0) {
    $slidenotes=array();
    $slidenotes["Slide"]=$num;
    $slidenotes["Notes"]=$notesslide;
    $notes[]=$slidenotes;
}

//print_r($notes);
echo json_encode($notes);
unlink($filename);


}

/*
$textNotes=array();
$fd=zip_open($filename);
do {
    $entry = zip_read($fd);
    if (is_resource($entry) && strpos(zip_entry_name($entry),"t/notesSlides/notes") > 0) {
        $entryname = zip_entry_name($entry);
        $number = 1*(str_replace(".xml","",str_replace("ppt/notesSlides/notesSlide","",$entryname) ))-1;
        $slidenumber = $number;
        zip_entry_open($fd, $entry, "r");
        $note = zip_entry_read($entry, zip_entry_filesize($entry));
        $note=str_replace("<a:pPr/>","\n<a:pPr/>",$note);
        $note=str_replace("<a:rPr ","\n<a:rPr ",$note);
        $note=str_replace("<a:endParaRPr>","\n<a:endParaRPr>",$note);
        $textonly = trim(strip_tags($note));

        zip_entry_close($entry);
//        echo "Slide #".$slidenumber."\n----------\n".$textonly."\n";
        $textNotes[] = array("Slide"=>$slidenumber,"Notes"=>explode("\n",$textonly));
    }
} while ($entry);
zip_close($fd);

function cmp($a, $b)
{
    return $a["Slide"]*1 - $b["Slide"]*1;
}

usort($textNotes, "cmp");

echo json_encode($textNotes);



//unlink($filename);
*/

?>
