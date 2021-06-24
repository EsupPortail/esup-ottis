<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');


 //   exit;
?>
<!doctype html>
<html lang="fr">
<head>
	<title>OMIST Tools</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="../jquery/jquery-ui.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../favicon.png" />
    <script src="jquery/jquery.js"></script>
    <script src="jquery/jquery-ui.min.js"></script>
</head>
<body>
	<h1>OMIST Tools</h1>
<div id="menu">
  <h3>Create web animation from pptx</h3>
  <div>
  <form action="CreateAppFromPPTX.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload"><br/>
    <input type="submit" value="Create" name="submit">
    </form>
  </div>
  <h3>Create web animation from pdf+txt</h3>
  <div>
  <form action="CreateAppFromPDF.php" method="post" enctype="multipart/form-data">
    Select PDF file : 
    <input type="file" name="fileToUpload" id="fileToUpload"><br/>
    Select SPEECH file (.txt): 
    <input type="file" name="fileToUploadTXT" id="fileToUploadTXT"><br/>
    <input type="submit" value="Create" name="submit">
    </form>
  </div>
</div>
</body>
<script>
$("body").tooltip();
$( "#menu" ).accordion();
</script>
</html>
