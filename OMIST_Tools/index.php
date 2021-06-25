<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

$T_lang=array();
$fd = fopen("../supportedlanguages.csv","r");
while($line=fgets($fd,4096)) {
//  echo $line."\n";
  $ligne=explode(";",$line);
//  print_r($ligne);
  if (count($ligne)>2) $T_lang[]=array("name_fr"=>trim($ligne[2]), "name_vo"=>trim($ligne[0]), "code"=>trim($ligne[1]), "voice"=>"-1");
}
fclose($fd);



?>
<!doctype html>
<html lang="fr">
<head>
	<title>OMIST Tools</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="../jquery/jquery-ui.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../favicon.png" />
    <script src="../jquery/jquery.js"></script>
    <script src="../jquery/jquery-ui.min.js"></script>


<script type="text/javascript">

<?php
echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";
?>

function PopulateLanguages(selectelement) {
  var select = document.getElementById(selectelement);
  for(var i=0; i<supportedlanguages.length; i++) {
      var lang=supportedlanguages[i].code; //.substr(0,5);
        var opt=document.createElement("option");
        opt.value=lang;
        opt.innerHTML=lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
        select.appendChild(opt);
      
    }
}

function Init() {
  PopulateLanguages('inputlanguage1');
  PopulateLanguages('outputlanguage1');

  
}

</script>
</head>
<body onload="Init();">
	<h1>OMIST Tools</h1>
<div id="menu">
  <h3>Create web animation from pptx (minimal App without translations)</h3>
  <div>
  <form action="CreateAppFromPPTX.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload"><br/>
    <input type="submit" value="Create" name="submit">
    </form>
  </div>
  <h3>Create web animation from pdf+txt (minimal App without translations)</h3>
  <div>
  <form action="CreateAppFromPDF.php" method="post" enctype="multipart/form-data">
    Select PDF file : 
    <input type="file" name="fileToUpload" id="fileToUpload"><br/>
    Select SPEECH file (.txt): 
    <input type="file" name="fileToUploadTXT" id="fileToUploadTXT"><br/>
    <input type="submit" value="Create" name="submit">
    </form>
  </div>
  <h3>Create web animation from pptx (Complete)</h3>
  <div>
  <form action="CreateAppFromPPTX_complete.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload"><br/>
    <table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage1" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><select id="outputlanguage1"  name="outputlanguage" multiple rows="5"></select>
  </td></tr>
  </table>
    <input type="submit" value="Create" name="submit">
    </form>
  </div>
  <h3>Create web animation from pdf+txt (Complete)</h3>
  <div>
  <form action="CreateAppFromPDF_complete.php" method="post" enctype="multipart/form-data">
  
    Select PDF file :   <input type="file" name="fileToUpload" id="fileToUpload"><br/>
    Select SPEECH file (.txt): <input type="file" name="fileToUploadTXT" id="fileToUploadTXT"><br/>
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
