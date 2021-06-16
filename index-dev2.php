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

$T_lang=array();
$fd = fopen("supportedlanguages.csv","r");
while($line=fgets($fd,4096)) {
//  echo $line."\n";
  $ligne=explode(";",$line);
//  print_r($ligne);
  if (count($ligne)>2) $T_lang[]=array("name_fr"=>trim($ligne[2]), "name_vo"=>trim($ligne[0]), "code"=>trim($ligne[1]), "voice"=>"-1");
}
fclose($fd);

//print_r($T_lang);
?>
<!doctype html>
<html lang="fr">
<head>
	<title>Automatic translation for teaching purposes: Teacher</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="jquery/jquery-ui.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="favicon.png" />
	<style>

.classselect {
  width: 300px;
  height: 40px;
  font-family:Verdana,Arial,sans-serif;
	font-size:1em;
  background-color: #2191c0;
  color: #eaf5f7;
}

option{
  font-family:Verdana,Arial,sans-serif;
	font-size:1.5em;
  color: black;
  background-color: white;
}

option:hover{
  color: white;
  background-color: #acdd4a;
}

input[type=text] {
	width: 500px;
}

#tabs-5 dl:hover {
  background-color: lightblue;
}

#tabs-5 dl dd {
  background-color: white;
}

#tabs-5 dl dd:hover {
  background-color: blue;
  color: white;
}

#tabs-5 dl dt span, #tabs-5 dl dd span  {
  transition: 0.2s ease;
}

#tabs-5 dl dt span:hover {
  font-size: 1.5em;
  text-shadow: 2px 2px 5px blue;
}

#tabs-5 dl dd span:hover {
  font-size: 1.2em;
  text-shadow: 2px 2px 5px white;

}


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
  top:5px; 
  left: 5px; 
  font-size: 36px; 
  z-index: 1000; 
  background-color: red; 
  border-radius: 10px; 
  width: 50px; 
  height: 50px;
  margin: auto;
  color: white;
  text-align: center;
  vertical-align: middle;
}

#questionicon {
  position: fixed; 
  top:5px; 
  left: 60px; 
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

.big {
    font-size: 36px;
    transform: scale(1.5) translateY(5px);
}
	</style>


<script type="text/javascript">

<?php
echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";
?>

var AllOutputVoices=[];
var TLangInput=[];
var builtin_voice;
var builtin_language;

function PopulateVoicesOut() {
  var select = document.getElementById('VoiceOutput');
  AllOutputVoices=[];
  while (select.firstChild) {
    select.removeChild(select.firstChild);
  }
  var voices = window.speechSynthesis.getVoices();
  for(var i=0; i<voices.length; i++) {
    if (true) {//(voices[i].localService) {
      var opt=document.createElement("option");
      opt.value=AllOutputVoices.length;
      AllOutputVoices.push(voices[i]);
      opt.innerHTML=voices[i].lang+" "+voices[i].name;
      select.appendChild(opt);
    }
  }
}

function PopulateVoicesIn() {
  var select = document.getElementById('classroomlanguage');
  var voices = window.speechSynthesis.getVoices();
  TLangInput=[];

  for(var i=0; i<voices.length; i++) {
    if (true) {//(voices[i].localService) {
      var lang=voices[i].lang; //.substr(0,5);
      if (TLangInput.indexOf(lang)<0) {
        var opt=document.createElement("option");
        opt.value=TLangInput.length;
        TLangInput.push(lang);
        opt.innerHTML=lang;
        select.appendChild(opt);
      }
    }
  }
}

function PopulateVoicesIn() {
  if (TLangInput.length == 0) {
  var select = document.getElementById('classroomlanguage');
  //var voices = window.speechSynthesis.getVoices();
  TLangInput=[];

  for(var i=0; i<supportedlanguages.length; i++) {
      var lang=supportedlanguages[i].code; //.substr(0,5);
      if (TLangInput.indexOf(lang)<0) {
        var opt=document.createElement("option");
        opt.value=TLangInput.length;
        TLangInput.push(lang);
        opt.innerHTML=lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
        select.appendChild(opt);
      }
    }
  }
}


function setLanguageInput(i) {
  builtin_language=TLangInput[i];
  //Ecrire(builtin_language);
}

function setLanguageOutput(i) {
  builtin_voice=AllOutputVoices[i];
  //Ecrire(builtin_voice.lang);
}

function Parle(txt) {
 var discours = new SpeechSynthesisUtterance(txt);
 discours.voice = builtin_voice;
 discours.lang = builtin_voice.lang;
 window.speechSynthesis.speak(discours);
 discours.onend=ready;
 discours.onerror=ready;
}

var readytotalk=true;

function ready() {
  readytotalk=true;
  document.getElementById('bready').style.backgroundColor='green';
  StartSpeechRecognition(Traduction);
}

function notready() {
  StopSpeechRecognition();
  readytotalk=false;
  document.getElementById('bready').style.backgroundColor='red';
}

var inputnumber=0;
function Traduction(ch) {
  var recognitionon=document.getElementById('recognitionon').checked;
  if (recognitionon) {
  EcrireNB(ch);
  saveText(ch);
  var liveon=document.getElementById('liveon').checked;
  if (liveon) {
    if (readytotalk) {
      notready();
    Ecrire('Input : '+ch);
    if (builtin_voice.lang.substr(0,2) != builtin_language.substr(0,2))
      Translate(ch, builtin_voice.lang.substr(0,2), builtin_language.substr(0,2));
    else {
      Parle(ch);
      Ecrire("Output : "+ch);
    }
  }
  }
}
}

function Translate(ch, l, linput) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput, true);

  xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      // console.log(this.responseText);
      var response = JSON.parse(this.responseText);
      if ((! response["error"]) && response["data"]["translations"]) {
        Parle(response["data"]["translations"][0]["translatedText"]);
        Ecrire("Output : "+response["data"]["translations"][0]["translatedText"]);
      } else {
        Ecrire("Output : ---- Translation ERROR ----")
      }

    }
  };
  xhr.send();
}





function _(el) {
  return document.getElementById(el);
}

function progressHandler(event) {
  var percent = (event.loaded / event.total) * 100;
  _("progressBar").value = Math.round(percent);
//  _("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}

function addToNB(ch,before) {
  var tobesaidbetweenslides='';
  if (before == 1 && document.getElementById('tobesaid').value != '') tobesaidbetweenslides=document.getElementById('tobesaid').value+'. <br/>';

  ch=tobesaidbetweenslides+ch;
  document.getElementById('notebook').innerHTML+='<br/>'+ch;
  //saveTextNB(document.getElementById('notebook').innerHTML);
  saveText(ch.replace(/<br\/>/gi,"").replace(/<br>/gi,""));
}

function formateresponse(datatxt) {
  var res = '';
  var data = JSON.parse(datatxt);
  for (var i=0; i<data.length; i++) {
    slide = data[i];
    var txtnotes = '';
    for (var j=0; j<slide["Notes"].length; j++) {
      txtnotes = txtnotes + slide["Notes"][j]+' <br/>';
    }
    res += '<dl>';
    res = res + '<dt><span onclick="addToNB(\''+txtnotes+'\',1);">▶️</span>&nbsp;Slide #'+slide["Slide"]+'</dt>\n';
    for (var j=0; j<slide["Notes"].length; j++) {
      res = res + '<dd><span onclick="addToNB(\''+slide["Notes"][j]+'\',0);">▶️</span>&nbsp;'+slide["Notes"][j]+'</dd>\n';
    }
    res+='</dl>'
  }
//  alert(res);
  return res;
}

function completeHandler(event) {
  _("notebookcompleteformatted").innerHTML = formateresponse(event.target.responseText);
  _("progressBar").value = 0; 
  //alert(event.target.responseText);
}

function SlideNotes() {
  var file = _("ppt_presentation").files[0];
  var scriptfile = "";
  if (file.name.endsWith('pptx')) scriptfile = "ExtractSlidesNotes.php";
  if (file.name.endsWith('txt')) scriptfile = "ExtractSlidesNotesTxt.php";
  if (scriptfile == "") {alert('Only pptx and txt files are allowed !'); return -1;}
  var formdata = new FormData();
  formdata.append("ppt_presentation", file);
  var xhr = new XMLHttpRequest();
  
  xhr.open("POST", scriptfile);
  xhr.upload.addEventListener("progress", progressHandler, false);
//  xhr.addEventListener("load", completeHandler, false);
  xhr.addEventListener("load", completeHandler, false);
/*  xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      var response = JSON.parse(this.responseText);
      document.getElementById('notebookcompleteformatted').innerHTML = "<pre>"+response+"</pre>";
      alert(response);
    }
  };
  */
  xhr.send(formdata);
}


var SpeechRecognition = SpeechRecognition || webkitSpeechRecognition;
var builtin_recognition = new SpeechRecognition();
builtin_recognition.continuous = true;
var builtin_recognitionon = false;
let finalTranscript = '';

function StartSpeechRecognition(callback) {
  builtin_recognition.lang = builtin_language;
  builtin_recognition.onresult = function(event) {
    if (builtin_recognitionon) {
      let interimTranscript = '';
      for (let i = event.resultIndex, len = event.results.length; i < len; i++) {
        let transcript = event.results[i][0].transcript;
        if (event.results[i].isFinal) {
            finalTranscript += transcript;
            callback(transcript);
        } else {
          interimTranscript += transcript;
        }
      }
      document.getElementById('notebook').innerHTML = finalTranscript + '<i style="color:#ffd;">' + interimTranscript +'</i>';

/*      for (i = event.resultIndex; i < event.results.length; i++) {
        callback(event.results[i][0].transcript);
      }
      builtin_recognition.stop();
      */
    }
  };
  builtin_recognition.onstop = function () {builtin_recognition.start();}

  builtin_recognition.onend = function(event) {
    if (builtin_recognitionon) {
      StartSpeechRecognition(callback);
    }
  };
  builtin_recognitionon = true;
  builtin_recognition.start();
}

function StopSpeechRecognition() {
  builtin_recognitionon = false;
  builtin_recognition.stop();
}


function InitVoices() {
  PopulateVoicesIn();
  PopulateVoicesOut();
  setLanguageInput(0);
  setLanguageOutput(0);
}

function EcrireNB(ch) {
  if (document.getElementById('notebook').innerHTML == "")
    document.getElementById('notebook').innerHTML=ch;
  else document.getElementById('notebook').innerHTML=document.getElementById('notebook').innerHTML+"<br/>"+ch;
}


function Ecrire(ch) {
  if (document.getElementById('output').innerHTML == "")
    document.getElementById('output').innerHTML=ch;
  else document.getElementById('output').innerHTML=ch+"<br/>"+document.getElementById('output').innerHTML;
}
function hide(e) {
  document.getElementById(e).style.display='none';
}

function show(e) {
  document.getElementById(e).style.display='block';
}


//document.body.addEventListener('load',Init,true);

function saveText(ch) {
	var request = new XMLHttpRequest();
	request.open("POST", "savetext.php");
  var f=new FormData();
  f.append('lineid',inputnumber);
  inputnumber++;
  f.append('text',ch);
  f.append('inputlanguage',builtin_language);
  f.append('ROOMID',document.getElementById('classroomid').value);
	request.send(f);
//	request.onload = function(event) {};
}

function saveTextNB(ch) {
	var request = new XMLHttpRequest();
	request.open("POST", "savetextNB.php");
  var f=new FormData();
  //f.append('lineid',inputnumber);
  inputnumber = ch.split('<br/>').length;
  f.append('text',ch);
  f.append('inputlanguage',builtin_language);
  f.append('ROOMID',document.getElementById('classroomid').value);
	request.send(f);
//	request.onload = function(event) {};
}




function Lancer() {
  setInterval(readTextQ,1000);
}

function EcrireCR(ch) {
  if (document.getElementById('classroom').innerHTML == "")
    document.getElementById('classroom').innerHTML=ch;
  else document.getElementById('classroom').innerHTML=document.getElementById('classroom').innerHTML+"<br/>"+ch;
}

var inputnumberQ=0;

function TailleCR() {
  return document.getElementById('classroom').innerHTML.split('<br/>').length;
}

function readTextQ() {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "readtextQ.php");
  var f=new FormData();
  f.append('lineid',0);
  f.append('ROOMID',document.getElementById('classroomid').value);
	request.send(f);
	request.onload = function(event) {
    var Told=request.responseText.split('\n');
    var k=0;
    var T=[];
    for(var i=0; i<Told.length; i++) {
      if (Told[i].split(';').length>2) T.push(Told[i]);
    }
    //alert(inputnumberQ+'\n\n'+T);
    //console.log('Debug : '+inputnumberQ+T.length);
    for(var i=inputnumberQ; i<T.length; i++) {
      var L=T[i].split(';');
      //console.log('Debug -- '+T[i]+'     '+L[2]);
      if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2))
        TranslateQ(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2),L[0]);
      else {
        EcrireCR(L[0]+" : "+L[2]);
      }
    }
    // Is there a new question there ?
    if (inputnumberQ!=T.length) EventNewQuestion();
    inputnumberQ=T.length;
  };
}
}

function draw() {
  if (timenew_question>0) {
    timenew_question--;
    setTimeout(draw,1000);
  } else {
  var tab = document.getElementById('questionicon');
  tab.innerHTML='<i class=\'material-icons big\'>thumb_up</i>';
  tab.style.backgroundColor='green';
  }
}

var timenew_question=0;

function EventNewQuestion() {
  if (timenew_question == 0) {
    timenew_question=10;
    var tab = document.getElementById('questionicon');
    tab.innerHTML='<i class=\'material-icons blink big\'>warning</i>';
    tab.style.backgroundColor='orange';
    draw();
  } else timenew_question=10;
}

function saveTextQ(ch) {
	var request = new XMLHttpRequest();
	request.open("POST", "savetextQ.php");
  var f=new FormData();
  f.append('lineid','Teacher');
  //inputnumberQ++;
  f.append('text',ch);
  f.append('inputlanguage',builtin_voice.lang);
  f.append('ROOMID',document.getElementById('classroomid').value);
	request.send(f);
  request.onload = function(event) {
    readTextQ();
  };
}

function TranslateQ(ch, l, linput,name) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput, true);

  xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      // console.log(this.responseText);
      var response = JSON.parse(this.responseText);
      if ((! response["error"]) && response["data"]["translations"]) {
        // Parle(response["data"]["translations"][0]["translatedText"]);
        EcrireCR(name+" : "+response["data"]["translations"][0]["translatedText"]);
      } else {
        EcrireCR("---- Translation ERROR ----")
      }

    }
  };
  xhr.send();
}

function FromCompleteLessonToTable() {
  var txt = document.getElementById('notebookcompleteraw').innerHTML;
  document.getElementById('notebookcompleteformatted').innerHTML = txt;
}

function playlesson() {}


</script>


<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body onload="/*Init();*/ InitVoices(); setTimeout(InitVoices,1000); setTimeout(InitVoices,2000); setTimeout(function () {StartSpeechRecognition(Traduction); },3000); setTimeout(Lancer,3000); ready();">


<div id="micon" onclick="document.getElementById('recognitionon').checked = !document.getElementById('recognitionon').checked; if (document.getElementById('recognitionon').checked) {this.innerHTML='<i class=\'material-icons blink big\'>record_voice_over</i>';} else {this.innerHTML='<i class=\'material-icons big\'>voice_over_off</i>';};"><i class='material-icons big'>voice_over_off</i></div>

<div id="questionicon"><i class='material-icons big'>thumb_up</i></div>

<h1 style="text-align:center;">Automatic translation for teaching purposes: Teacher</h1>

<form id="myform">

<div id="tabs">
	<ul>
		<li><a href="#tabs-0">Informations</a></li>
		<li><a href="#tabs-1" onclick="timenew_question=0;">Classroom</a></li>
		<li><a href="#tabs-2">Notebook</a></li>
		<li><a href="#tabs-5">Complete lesson</a></li>
		<li><a href="#tabs-3">Live translation</a></li>
		<li><a href="#tabs-4">Share</a></li>
	</ul>
	<div id="tabs-0">
  <ul style="list-style-type: none;">
	    <li><label for="classroomid">Classroom identifier :</label><input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" /></li>
<li><label for="classroomlanguage">Classroom language :</label>
  <select id="classroomlanguage" onchange="setLanguageInput(this.value);">
  </select>
</li>
<li style="display: none;"><label for="recognitionon">Speech recognition active ? </label>
<input type="checkbox" id="recognitionon"/></li>
  </ul>
	</div>
	<div id="tabs-1">
    <div id="classroom" style="border: solid 1px;"></div>

    <input type="text" id="question" placeholder="Your question here"/>
    <input type="button" value="send" onclick="saveTextQ(document.getElementById('question').value);"/>
	</div>
	<div id="tabs-2">
    <div id="notebook" style="border: solid 1px;" contenteditable="true">
    </div>
    <input type="button" id="savenb" value="Save notebook" onclick="saveTextNB(document.getElementById('notebook').innerHTML);"/>
	</div>
	<div id="tabs-5">
    <div>
    <label for="ppt_presentation">Powerpoint presentation : </label>
    <input type="file" name="ppt_presentation" id="ppt_presentation" onchange="SlideNotes()">
   <label for="tobesaid" title="Portion of text inserted between each slides (eq. Next slide...)">Slide transistion : </label>
    <input type="text" id="tobesaid" value="">
    <br/>
    <progress id="progressBar" value="0" max="100" style="width:300px;"></progress>
    </div>
    <div id="notebookcompleteformatted" style="border: solid 1px; height: 600px; overflow: auto;">
    </div>
	</div>
	<div id="tabs-3">
    <ul style="list-style-type: none;">
      <li>
    <label for="liveon">Live translation active ? </label>
    <input type="checkbox" id="liveon"/></li>
    <li><button id="bready">Ready to listen ?</button></li>
    <li><label for="liveon">Output language </label>
    <select id="VoiceOutput" onchange="setLanguageOutput(this.value);">
    </select>
  </li>
</ul>
    <div id="output">
    </div>

</div>

	<div id="tabs-4">
    <ul style="list-style-type: none;">
  	    <li>Student application : <a id="sharedlink" href="Student.php?ROOMID=<?php echo $classroomid; ?>">Student.php?ROOMID=<?php echo $classroomid; ?></a></li>
        <li>QR code : <div id="sharedqr" style="margin-left: auto; margin-right: auto;"></div></li>
        <li>Teacher application : <a href="index.php?ROOMID=<?php echo $classroomid; ?>">index.php?ROOMID=<?php echo $classroomid; ?></a></li>
    </ul>

	</div>


</div>
</form>


<script src="jquery/jquery.js"></script>
<script src="jquery/jquery-ui.min.js"></script>
<script src="qrcode.min.js"></script>
<script>
$("body").tooltip();
$( "#tabs" ).tabs();

//$( "#savenb" ).button();

new QRCode(document.getElementById("sharedqr"), {
	text: document.getElementById("sharedlink").href,
	width: 128,
	height: 128,
	colorDark : "#0000ff",
	colorLight : "#ffffff",
	correctLevel : QRCode.CorrectLevel.L
});

</script>
</body>
</html>
