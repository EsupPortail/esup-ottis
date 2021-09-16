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
  border-radius: 5%;
  width: 90%;
  height: 15%;
  margin: auto;
  padding: auto;
  color: white;
  text-align: center;
  vertical-align: middle;
    box-shadow: 2px 2px 5px red;
  text-shadow: 2px 0 0 #f00, -2px 0 0 #f00, 0 2px 0 #f00, 0 -2px 0 #f00, 1px 1px #f00, -1px -1px 0 #f00, 1px -1px 0 #f00, -1px 1px 0 #f00;
}


#titreleft,#titreright {
  transition: 1s ease;
  border-radius: 5%;
  position: fixed;
  bottom: 10px;
  width: 40%;
  margin-left: auto;
  margin-right: auto;
  height: 7%;
  background-color: rgba(0,0,255,0.5);
  color: white;
  font-size: 18px;
  font-family: helvetica, arial, sans-serif;
  font-style: italic;
  padding: 0px;
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

#skipleft,#skipright {
  transition: 1s ease;
  border-radius: 5%;
  position: fixed;
  bottom: 12%;
  width: 40%;
  margin-left: auto;
  margin-right: auto;
  height: 7%;
  background-color: rgba(255,120,0,0.5);
  color: white;
  font-size: 18px;
  font-family: helvetica, arial, sans-serif;
  font-style: italic;
  padding: 0px;
  box-shadow: 2px 2px 5px #ff7f00;
  padding:auto;
  justify-content: center;
  align-items: center;
  z-index: 20000;
  text-shadow: 2px 0 0 #ff7f00, -2px 0 0 #ff7f00, 0 2px 0 #ff7f00, 0 -2px 0 #ff7f00, 1px 1px #ff7f00, -1px -1px 0 #ff7f00, 1px -1px 0 #ff7f00, -1px 1px 0 #ff7f00;
  text-align: center;
  vertical-align: middle;
  font-size: 2em;
  font-weight: bold;
}

#skipleft {
  left: 10px;
}

#skipright {
  right: 10px;
}


#speech_before, #speech_current, #speech_after {
  width: 90%;
  height: 15%;
  border-radius: 5%;
  left: 5%;
  position: fixed;
  margin: auto;
  background-color: rgba(0,0,0,0.5);
  color: white;
  font-family: helvetica, arial, sans-serif;
  font-style: italic;
  padding: 0px;
  box-shadow: 2px 2px 5px black;
  justify-content: center;
  align-items: center;
  z-index: 20000;
  text-shadow: 2px 0 0 #000, -2px 0 0 #000, 0 2px 0 #000, 0 -2px 0 #000, 1px 1px #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000;
  text-align: center;
  vertical-align: middle;
  overflow:hidden;
}

#speech_before {
  top: 20%;
  font-size: 0.8em;
  height: 10%;
  background-color: rgba(0,0,0,0.3);

}
#speech_current {
  top: 35%;
  font-size: 1em;
  font-weight: bold;
  height: 20%;

}
#speech_after {
  top: 60%;
  font-size: 0.8em;
  height: 10%;
  background-color: rgba(0,0,0,0.3);
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
  };
}
}

function remotespeech() {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "savespeechforremote.php");
  var f=new FormData();
  f.append('ROOMID',document.getElementById('classroomid').value);
  f.append('SPEECH','read');
	request.send(f);
	request.onload = function(event) {
    var data=request.responseText;
    var Tspeech = data.split(':|:');
    if (document.getElementById('speech_before').innerHTML != Tspeech[0])
      document.getElementById('speech_before').innerHTML = Tspeech[0];
    if (document.getElementById('speech_current').innerHTML != Tspeech[1])
      document.getElementById('speech_current').innerHTML = Tspeech[1];
    if (document.getElementById('speech_after').innerHTML != Tspeech[2])
      document.getElementById('speech_after').innerHTML = Tspeech[2];
  };
}
}


function Lancer() {
  setInterval(remotespeech,200);
}
</script>


<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body onload=" setTimeout(Lancer,300);" style="width: 100%; height: 100%;">

<div id="micon" onclick="remote('micro');"><p><i class='material-icons big' style="font-size: 5em;">record_voice_over</i></p></div>
<div id="speech_before" onclick="remote('backward');">Before</div>
<div id="speech_current" onclick="remote('playcurrent');">Current</div>
<div id="speech_after" onclick="remote('forward');">After</div>
<div title="Skip subtitle" id="skipleft" onclick="remote('skipbackward');"><span style="font-size: 1em;">&#8592; Skip</span></div>
<div title="Skip subtitle" id="skipright" onclick="remote('skipforward');"><span style="font-size: 1em;">Skip &#8594;</span></div>
<div id="titreleft" style="font-size: 2em;" onclick="remote('backward');">&#8592;</div>
<div id="titreright" style="font-size: 2em;" onclick="remote('forward');">&#8594;</div>
<input type="text" style="display: none;" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" />

<script src="jquery/jquery.js"></script>
<script src="jquery/jquery-ui.min.js"></script>

</body>
</html>
