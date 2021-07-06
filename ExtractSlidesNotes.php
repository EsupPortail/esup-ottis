<?php
ini_set ( "upload_max_filesize" , "50M" );
ini_set ( "post_max_size" , "50M" );

$tmp_file = $_FILES['ppt_presentation']['tmp_name'];

$flag_justif=false;
$filename = "tmpfiles/test.pptx";

if( is_uploaded_file($tmp_file) ) {
    if (!move_uploaded_file($tmp_file, $filename)) {
        echo "ERROR UPLOAD !!!!"; exit;
    }
} else {
    echo "ERROR PB UPLOAD !!!!"; exit;
}

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
        $textonly = trim(strip_tags(str_replace("<a:endParaRPr>","\n<a:endParaRPr>",$note)));
        zip_entry_close($entry);
//        echo "Slide #".$slidenumber."\n----------\n".$textonly."\n";
        $textNotes[] = array("Slide"=>$slidenumber,"Notes"=>explode("\n",$textonly));
    }
} while ($entry);
zip_close($fd);

function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

$textNotes=array_sort($textNotes,"Slide");

echo json_encode($textNotes);



//unlink($filename);

?>
