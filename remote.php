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


.blink {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0.1;
    transform: scale(0.5);
  }
}

#micon {
  position: fixed;
  top:10px;
  right: 10px;
  font-size: 90%;
  z-index: 1000;
  background-color: red;
  border-radius: 10px;
  width: 90%;
  height: 50%;
  margin: auto;
  padding: auto;
  color: white;
  text-align: center;
  vertical-align: middle;
}

#questionicon {
  position: fixed;
  top:5px;
  right: 5px;
  font-size: 36px;
  z-index: 1000;
  background-color: green;
  border-radius: 10px;
  width: 50px;
  height: 50px;
  margin: auto;
  color: white;
  text-align: center;
  vertical-align: middle;
}

#logonext {
  position: fixed;
  top:5px;
  right: 5px;
  display:none;
  z-index: 1000;
}

.big {
    font-size: 36px;
    transform: scale(1.5) translateY(5px);
}

#titre {
  transition: 1s ease;
  border-radius: 25px;
  position: fixed;
  bottom: 10px;
  left: 5%;
  width: 90%;
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
}

#titreleft,#titreright {
  transition: 1s ease;
  border-radius: 25px;
  position: fixed;
  bottom: 10px;
  width: 45%;
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

#titre h1 {
  text-align: center;
  font-size: 1.5em;
  font-weight: bold;
}

#soustitre {
  transition: 1s ease;
  position: fixed;
  bottom: 0px;
  left: 0px;
  width: 100%;
  height: 10px;
  z-index: 20000;
}

#classroom span {
  padding: 5px;
  margin-left: 5px;
  margin-top: 0px;
  margin-right: 0px;
  margin-bottom: 15px;
  border-radius: 5px;

}

#classroom {
  padding: 15px;


}

#bar {
  transition: 1s ease;
  transform-origin: top left;

}

#FS {
  position: fixed;
  top:50px;
  right: 0px;
  font-size: 12px;
}

#tabs-7 {
}

#img_slides {
  transform-origin: top left;
  cursor: url(images/pointer.png), pointer;
}


#sharedqr {
    transition: 1s ease;
  transform-origin: top left;
}
#sharedqr:hover {
  transform: scale(2);
}

#titleextratab1,#titleextratab2,#titleextratab3,#titleextratab4,#titlelive,#titleshare,#titlevisio {
  display:none;
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

<div id="micon" onclick="remote('micro');"><i class='material-icons big'>record_voice_over</i></div>
<div id="titreleft" onclick="remote('backward');">&#8592;</div>
<div id="titreright" onclick="remote('forward');">&#8594;</div>
<input type="text" style="display: none;" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" />

<script src="jquery/jquery.js"></script>
<script src="jquery/jquery-ui.min.js"></script>

</body>
</html>
