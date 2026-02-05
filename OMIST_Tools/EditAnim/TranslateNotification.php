<?php 

if (isset($_POST['text']) && isset($_POST['from']) && isset($_POST['to'])) {
    $textToTranslate = $_POST['text'];
    $fromLanguage = $_POST['from'];
    $toLanguage = $_POST['to'];

    $command = 'python3 '.dirname(__FILE__).'/TranslateNotification.py --message "'.$textToTranslate.'" --from '.$fromLanguage.' --to '.$toLanguage ;
    $textTranslated = system($command);
}

?>