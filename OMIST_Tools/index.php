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
  var allselects=['inputlanguage1','outputlanguage1','translatelanguage1','inputlanguage2','outputlanguage2','inputlanguage3','outputlanguage3'];
  allselects.map(PopulateLanguages);
}

</script>
</head>
<body onload="Init();">
	<h1><img src="logo-next-omist.png"/> OMIST Tools</h1>
  <datalist id="listaspectratio">
    <option value="16/10"/>
    <option value="16/9"/>
    <option value="4/3"/>
    <option value="1/1"/>
  </datalist>
  <datalist id="listdelays">
    <option value="0"/>
    <option value="5"/>
    <option value="10"/>
    <option value="300"/>
    <option value="600"/>
    <option value="1200"/>
  </datalist>
 
<div id="menu">
  <h3>Create web animation from pptx (minimal App without translations)</h3>
  <div>
  <form action="CreateAppFromPPTX.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload1"/><br/>
    <input type="submit" value="Create" name="submit"/>
    </form>
  </div>
  <h3>Create web animation from pdf+txt (minimal App without translations)</h3>
  <div>
  <form action="CreateAppFromPDF.php" method="post" enctype="multipart/form-data">
    Select PDF file : 
    <input type="file" name="fileToUpload" id="fileToUpload2"/><br/>
    Select SPEECH file (.txt): 
    <input type="file" name="fileToUploadTXT" id="fileToUploadTXT2"/><br/>
    <input type="submit" value="Create" name="submit"/>
    </form>
  </div>
  <h3>Create web animation from pptx (Complete)</h3>
  <div>
  <form action="CreateAppFromPPTX_complete.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload3"><br/>
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
  <td><select id="outputlanguage1"  name="outputlanguage[]" multiple rows="5"></select>
  </td></tr>
  <tr><td colspan="2"><b>Advanced options</b></td></tr>
  <tr>
  <td style="vertical-align: top;">
    Translate slides in ? </td>
  <td>
  <select id="translatelanguage1" name="translatelanguage" value="">
  <option value="">No translation</option>
  </select>
  </td></tr>
     <tr>
  <td style="vertical-align: top;">
    Aspect ratio : </td>
  <td>
  <input id="aspectratio1" name="aspectratio" list="listaspectratio" value="16/10"/>
 </td></tr>
 <tr>
    <td style="vertical-align: top;">
    Keep Aspect ratio ? </td>
  <td>
  <input id="keepaspectratio1" name="keepaspectratio" style="width: 10px;" value="X" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"/>
  </td></tr>
    <td style="vertical-align: top;">
    Extra delay between slides (in tenth of seconds): </td>
  <td>
    <input id="extradelay1" name="extradelay" list="listdelays" value="0"/>

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


<table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage2" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><select id="outputlanguage2"  name="outputlanguage[]" multiple rows="5"></select>
  </td></tr>
  <tr><td colspan="2"><b>Advanced options</b></td></tr>
     <tr>
  <td style="vertical-align: top;">
    Aspect ratio : </td>
  <td>
  <input id="aspectratio2" name="aspectratio" list="listaspectratio" value="16/10"/>
 </td></tr>
 <tr>
    <td style="vertical-align: top;">
    Keep Aspect ratio ? </td>
  <td>
  <input id="keepaspectratio2" name="keepaspectratio"  style="width: 10px;" value="X" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"/>
  </td></tr>
    <td style="vertical-align: top;">
    Extra delay between slides (in tenth of seconds): </td>
  <td>
    <input id="extradelay2" name="extradelay" list="listdelays" value="0"/>

  </td></tr>
 </table>

    <input type="submit" value="Create" name="submit">
    </form>
  </div>
    <h3>Translate pptx</h3>
  <div>
  <form action="TranslatePPTX.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload"><br/>
    <table>
  <tr>
  <td style="vertical-align: top;">
    Input language : </td>
  <td>
  <select id="inputlanguage3" name="inputlanguage"></select>
  </td></tr>
    <tr>
  <td style="vertical-align: top;">
  Output languages : </td>
  <td><select id="outputlanguage3"  name="outputlanguage[]" multiple rows="5"></select>
  </td></tr>
  <tr>
    <td style="vertical-align: top;">
    Translate slide notes too ? </td>
  <td>
  <input id="slidenotes3" name="slidenotes"  style="width: 10px;" value="" onclick="if (this.value == 'X') this.value =''; else this.value ='X';"/>
  </td></tr>
 </table>
    <input type="submit" value="Translate" name="submit">
    </form>
  </div>
  <h3>Convert pdf+txt to pptx</h3>
  <div>
  <form action="ConvertPDFtoPPTX.php" method="post" enctype="multipart/form-data">  
    Select PDF file :   <input type="file" name="fileToUpload" id="fileToUpload"><br/>
    Select SPEECH file (.txt): <input type="file" name="fileToUploadTXT" id="fileToUploadTXT"><br/>
    <input type="submit" value="Convert" name="submit">
    </form>
  </div>
    <h3>Extract speech from pptx</h3>
  <div>
  <form action="ExtractSpeechFromPPTX.php" method="post" enctype="multipart/form-data">
    Select PPTX file : 
    <input type="file" name="fileToUpload" id="fileToUpload"><br/>
    <input type="submit" value="Extract" name="submit">
    </form>
  </div>

</div>
</body>
<script>
$("body").tooltip();
$( "#menu" ).accordion();
</script>
</html>
