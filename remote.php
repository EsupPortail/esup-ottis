<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
?>
<?php
if (isset($_GET["ROOMID"]))
  $classroomid = $_GET["ROOMID"];
else {
  $classroomid = md5(gmdate("Y-m-d\TH:i:s\Z"));
}

?>
<!doctype html>
<html lang="fr">
<head>
	<title>Automatic translation for teaching purposes: Remote</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="jquery/jquery-ui.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="favicon.png" />


	<style>

#micon {
  position: fixed;
  top:10px;
  right: 5%;
  font-size: 90%;
  z-index: 1000;
  background-color: red;
  border-radius: 10px;
  width: 90%;
  height: 100px;
  margin: auto;
  padding: auto;
  color: white;
  text-align: center;
  vertical-align: middle;
}


#titreleft,#titreright {
  transition: 1s ease;
  border-radius: 25px;
  position: fixed;
  bottom: 10px;
  width: 40%;
  margin-left: auto;
  margin-right: auto;
  min-height: 64px;
  background-color: rgba(0,0,255,0.5);
  color: white;
  font-size: 18px;
  font-family: helvetica, arial, sans-serif;
  font-style: italic;
  padding: 10px;
  box-shadow: 2px 2px 5px blue;
  padding:auto;
  justify-content: center;
  align-items: center;
  z-index: 20000;
  text-shadow: 2px 0 0 #00f, -2px 0 0 #00f, 0 2px 0 #00f, 0 -2px 0 #00f, 1px 1px #00f, -1px -1px 0 #00f, 1px -1px 0 #00f, -1px 1px 0 #00f;
  text-align: center;
  vertical-align: middle;
  font-size: 2em;
  font-weight: bold;
}

#titreleft {
  left: 10px;
}

#titreright {
  right: 10px;
}



#speech_before, #speech_current, #speech_after {
  width: 90%;
  height: 100px;
  border-radius: 25px;
  position: fixed;
  margin-left: auto;
  margin-right: auto;
  background-color: rgba(0,255,0,0.5);
  color: white;
  font-family: helvetica, arial, sans-serif;
  font-style: italic;
  padding: 10px;
  box-shadow: 2px 2px 5px green;
  padding:auto;
  justify-content: center;
  align-items: center;
  z-index: 20000;
  text-shadow: 2px 0 0 #00f, -2px 0 0 #00f, 0 2px 0 #00f, 0 -2px 0 #00f, 1px 1px #00f, -1px -1px 0 #00f, 1px -1px 0 #00f, -1px 1px 0 #00f;
  text-align: center;
  vertical-align: middle;
}

#speech_before {
  top: 140px;
  font-size: 1em;
}
#speech_current {
  top: 250px;
    font-size: 1.5em;
    font-weight: bold;

}
#speech_after {
  top: 360px;
    font-size: 1em;
}

	</style>


<script type="text/javascript">

function remote(myorder) {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "saveremoteorder.php");
  var f=new FormData();
  f.append('ROOMID',document.getElementById('classroomid').value);
  f.append('ORDER',myorder);
	request.send(f);
	request.onload = function(event) {
    var order=request.responseText;
    if (order != '') {
      if (order == 'forward') Forward();
      if (order == 'backward') Backward();
    }
  };
}
}


function Lancer() {
//  setInterval(remote,1000);
}
</script>


<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body onload=" setTimeout(Lancer,3000);" style="width: 100%; height: 100%;">

<div id="micon" onclick="remote('micro');"><p><i class='material-icons big' style="font-size: 5em;">record_voice_over</i></p></div>
<div id="speech_before">Before</div>
<div id="speech_current">Current</div>
<div id="speech_after">After</div>
<div id="titreleft" style="font-size: 5em;" onclick="remote('backward');">&#8592;</div>
<div id="titreright" style="font-size: 5em;" onclick="remote('forward');">&#8594;</div>
<input type="text" style="display: none;" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" />

<script src="jquery/jquery.js"></script>
<script src="jquery/jquery-ui.min.js"></script>

</body>
</html>
