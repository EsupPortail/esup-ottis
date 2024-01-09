<?php
?>

<!doctype html>
<html lang="fr">
<head>
	<title>Slide Manipulation Tools</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="../jquery/jquery-ui.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../favicon.png" />
    <script src="../jquery/jquery.js"></script>
    <script src="../jquery/jquery-ui.min.js"></script>
    <script src="../js/teacher-func.js"></script>
<style type="text/css">

<?php
if (isset($_POST["submit"]) && $_FILES["fileToUpload"]["tmp_name"] != "") {
    
    // $command = 'bash ./CreateAppOnly.sh "'.$_FILES["fileToUpload"]["tmp_name"].'" '.$randompath.' >/dev/null 2>/dev/null';
    // system($command);
    
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="output.html"');
    readfile('tmp/output_'.$randompath.'.html');
    unlink('tmp/output_'.$randompath.'.html');
    }
    

?>

</html>