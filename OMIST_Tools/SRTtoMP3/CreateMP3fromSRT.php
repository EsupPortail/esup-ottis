if (isset($argc)) {
    $randompath = $argv[1];
    $filename = $argv[2];
    $Voice = $argv[3];
    $Speed = $argv[4];
    $outputdir = "../tmp/mp3tosrt/".$randompath."/";
    $outputfilename = "../tmp/output_".$randompath.".mp3";
    $command = '/usr/bin/python3 '.dirname(__FILE__).'/ReadSRT.py -o '.$outputdir.' -i "'.$filename.'" -V "'.$Voice.'" -s ".$Speed." -f '".outputfilename."' >/dev/null 2>/dev/null';
    system($command);
}
