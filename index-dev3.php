<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');


    // Dans cette version, tentative d'inclusion d'un retour de type "étudiant" dans la version enseignant

?>
<?php

include("Security/Authentification.php");

$uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="e18XXX";

if ($_SERVER['PHP_AUTH_USER'] == "bourdon-j" && isset($_GET["fakeuser"])) {$fake=true; $uid=$_GET["fakeuser"];} else {$fake=false;}

//AuthenticateNotStudent($uid);

if (isset($_GET["ROOMID"]))
  $classroomid = $_GET["ROOMID"];
else {
  $classroomid = md5(gmdate("Y-m-d\TH:i:s\Z"));
}

$appid=md5(gmdate("Y-m-d\TH:i:s\Z")."XXX");

if (isset($_GET["WOOCLAPKEY"])) {
  $wooclapaddress = "https://app.wooclap.com/events/".$_GET["WOOCLAPKEY"]."/0";
} else {
  $wooclapaddress = "https://www.wooclap.com";
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

// Stats and log
$fd = fopen("tmp/StatsLogs.txt","a");
fwrite($fd,gmdate("Y-m-d\TH:i:s\Z")."|".$uid."|".$classroomid."\n");
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet"> 

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

#micon,#connexion {
  position: fixed;
  top:5px;
  right: 60px;
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

#connexion {
  top: 60px;
  right: 60px;
  visibility: hidden;
}


#pincodediv {
  position: fixed;
  transition: 1s ease;
  transform-origin: right;
  top:5px;
  right: 115px;
  font-size: 36px;
  z-index: 1000;
  background-color: rgba(128,0,128,0.5);
  border-radius: 10px;
  width: 150px;
  height: 50px;
  margin: auto;
  color: white;
  text-align: center;
  vertical-align: top;
  font-family: 'Anton', sans-serif;
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
  transform-origin: left;
  transform: scaleX(1);
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

#partialtitre, #studentfeedback {
  transition: 1s ease;
  transition-property: background-color, color, box-shadow, text-shadow, font-size;
  border-radius: 25px;
  position: fixed;
  top: 10px;
  left: 20px;
  width: 90%;
  margin-left: auto;
  margin-right: auto;
  min-height: 64px;
  background-color: rgba(0,255,0,0.5);
  color: white;
  font-size: 18px;
  font-family: helvetica, arial, sans-serif;
  font-style: italic;
  padding: 10px;
  box-shadow: 2px 2px 5px rgb(0,255,0);
  padding:auto;
  justify-content: center;
  align-items: center;
  z-index: 20000;
  text-shadow: 2px 0 0 rgb(0,100,0), -2px 0 0 rgb(0,100,0), 0 2px 0 rgb(0,100,0), 0 -2px 0 rgb(0,100,0), 1px 1px rgb(0,100,0), -1px -1px 0 rgb(0,100,0), 1px -1px 0 rgb(0,100,0), -1px 1px 0 rgb(0,100,0);
  visibility: hidden;
}

#titreleft {
  display: flex;
  transition: 1s ease;  
  border-radius: 25px;
  position: fixed;
  bottom: 10px; 
  //top: 10px;  
  left: 0.5%;
  width: 3%;
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

#titreright {
  display: flex;
  transition: 1s ease;
  border-radius: 25px;
  position: fixed;
  bottom: 10px;
  right: 0.5%;
  width: 3%;
  height: 24px;
  margin-left: auto;
  margin-right: auto;
  background-color: rgba(255,255,0,0.5);
  color: black;
  font-size: 18px;
  font-family: helvetica, arial, sans-serif;
  font-style: italic;
  box-shadow: 2px 2px 5px #ff0;
  padding:auto;
  justify-content: center;
  align-items: center;
  z-index: 20000;
  text-shadow: 2px 0 0 #ff0, -2px 0 0 #ff0, 0 2px 0 #ff0, 0 -2px 0 #ff0, 1px 1px #ff0, -1px -1px 0 #ff0, 1px -1px 0 #ff0, -1px 1px 0 #ff0;
  text-align: center;
  vertical-align: middle;
  font-size: 1em;
  font-weight: bold;
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
  top:60px;
  right: 5px;
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

#titleextratab1,#titleextratab2,#titleextratab3,#titleextratab4,#titlelive,#titlevisio {
  display:none;
}

#scrollbar {
  position: fixed;
  bottom: 0px;
  height: 5px;
  background-color: lightblue;
  width: 100%;
  transform-origin: top left;
  transform: scaleX(0.5);
  z-index: 15000;
}

#question {
  outline: none;
  font-size: 1.5em;
  font-style: italic;
}

/* CSS */
.button-3 {
  appearance: none;
  background-color: #2ea44f;
  border: 1px solid rgba(27, 31, 35, .15);
  border-radius: 6px;
  box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
  box-sizing: border-box;
  color: #fff;
  cursor: pointer;
  display: inline-block;
  font-family: -apple-system,system-ui,"Segoe UI",Helvetica,Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji";
  font-size: 14px;
  font-weight: 600;
  line-height: 20px;
  padding: 6px 16px;
  position: relative;
  text-align: center;
  text-decoration: none;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
  vertical-align: middle;
  white-space: nowrap;
}

.button-3:focus:not(:focus-visible):not(.focus-visible) {
  box-shadow: none;
  outline: none;
}

.button-3:hover {
  background-color: #2c974b;
}

.button-3:focus {
  box-shadow: rgba(46, 164, 79, .4) 0 0 0 3px;
  outline: none;
}

.button-3:disabled {
  background-color: #94d3a2;
  border-color: rgba(27, 31, 35, .1);
  color: rgba(255, 255, 255, .8);
  cursor: default;
}

.button-3:active {
  background-color: #298e46;
  box-shadow: rgba(20, 70, 32, .2) 0 1px 0 inset;
}

	</style>

<script type="text/javascript">
// Student feedback

function StudentParleThen(txt,f) {
  EcrireFeedback(txt);
  setTimeout(f,100*txt.length);
}

var Studentinputnumber=-1;

function StudentTranslate(ch, l, linput, lineid) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + document.getElementById('classroomid').value +"&lineid=" + lineid, true);

  xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      //console.log(this.responseText);
      var response = JSON.parse(this.responseText);
      if ((! response["error"]) && response["data"]["translations"]) {
        StudentToSay[''+lineid]=(response["data"]["translations"][0]["translatedText"]);
//        Ecrire(lineid+"  :  "+response["data"]["translations"][0]["translatedText"]);
        EcrireFeedback(response["data"]["translations"][0]["translatedText"]);
      } else {
        EcrireFeedback("---- Translation ERROR ----")
      }

    }
  };
  xhr.onloadend = function () {StudentCompleteTranslation();};
  xhr.send();
}

function StudentTranslate2(ch, l, linput, lineid) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + document.getElementById('classroomid').value +"&lineid=" + lineid, true);
  xhr.onloadend = function () {StudentCompleteTranslation2();};
  xhr.send();
}


var StudentToTranslate=[];
var StudentToTranslate2=[];
var Studentcompletespeechactive=false;

function StudentCompleteTranslation() {
  if (StudentToTranslate.length>0) {
    var L=StudentToTranslate[0];
    StudentToTranslate.splice(0,1);
    if (subtitle_language.substr(0,2).toLowerCase() != L[1].substr(0,2)) {
      StudentTranslate(L[2], subtitle_language.substr(0,2).toLowerCase(), L[1].substr(0,2),L[0]);
    } else {
        StudentToSay[''+L[0]]=L[2];//,CompleteTranslation);
        EcrireFeedback(""+L[2]);
        StudentCompleteTranslation();
    }
  } else {if (! Studentcompletespeechactive) {Studentcompletespeechactive=true; StudentCompleteSpeech();}}
}

function StudentCompleteTranslation2() {
  if (StudentToTranslate2.length>0) {
    var L=StudentToTranslate2[0];
    StudentToTranslate2.splice(0,1);
    if (subtitle_language.substr(0,2).toLowerCase() != L[1].substr(0,2)) {
      // Ecrire("Push : "+L[2]);
      StudentTranslate2(L[2], subtitle_language.substr(0,2).toLowerCase(), L[1].substr(0,2),L[0]);
    } else StudentCompleteTranslation2();
  } else setTimeout(StudentCompleteTranslation,10);
}

var StudentToSay=[];
var Studentcurrentdiscours=-1;
var Studentgotolive=false;

function StudentCompleteSpeech() {
  var nextdiscours = Studentcurrentdiscours;
  for(var i=1; i<1000; i++) {
    if (StudentToSay[''+(Studentcurrentdiscours+i)]) {
      nextdiscours = Studentcurrentdiscours+i;
      break;
    }
  }
  if (Studentgotolive) {
    //console.log('Restart Speech');
    //console.log('Discours : '+currentdiscours+'   '+nextdiscours)
    for(var i=1000; i>0; i--) {
    if (StudentToSay[''+(Studentcurrentdiscours+i)]) {
      nextdiscours = Studentcurrentdiscours+i;
      break;
    }
    //console.log('Discours next : '+currentdiscours+'   '+nextdiscours)
    Studentgotolive=false;
  }
  }
    //Ecrire(currentdiscours+'   '+nextdiscours);
    if (nextdiscours > Studentcurrentdiscours) {
      var L=StudentToSay[''+nextdiscours];
      L=L.replace("&nbsp;"," ");
      StudentParleThen(L,StudentCompleteSpeech);
      Studentcurrentdiscours=nextdiscours;
    } else Studentcompletespeechactive=false;
  
}

var Studentcurrentorder_readtext=0;
var Studentmaxorder_readtext=-1;
var StudentAllPhrases=[];

function StudentreadText() {
  var synthesison=true;
  if (synthesison) {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "readtext.php");
  Studentcurrentorder_readtext++;
  var f=new FormData();
  f.append('lineid',Studentinputnumber);
  f.append('ORDER',Studentcurrentorder_readtext);
  f.append('ROOMID',document.getElementById('classroomid').value);
	request.send(f);
	request.onload = function(event) {
    var T=request.responseText.split('\n');
    var maxi=-1;
    var readedorder=('0'+T[0]).split(';')[0]*1;
    if (readedorder>Studentmaxorder_readtext) {
        Studentmaxorder_readtext=readedorder;
    for(var i=0; i<T.length; i++) {
      var L=T[i].split(';');
      if (L.length>3) {
      if (1*L[1]>maxi) maxi=1*L[1];
      if (StudentAllPhrases[1*L[1]] == undefined) {
        StudentAllPhrases[1*L[1]] = true;
        //EcrireNB(L[3]);
        StudentToTranslate.push([L[1],L[2],L[3]]);
        StudentToTranslate2.push([L[1],L[2],L[3]]);
      }
        /*      if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2))
        Translate(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2),L[0]);
      else {
        Parle(L[2]);
        Ecrire(""+L[2]);
      }*/
      }
      }
    }
    //alert(maxi);
    if (maxi>Studentinputnumber) Studentinputnumber=maxi;

    StudentCompleteTranslation2();
  };
}
}
}


</script>

<script type="text/javascript">

<?php
echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";
?>

var visioactive=false;
var AllOutputVoices=[];
var TLangInput=[];
var builtin_voice;
var builtin_language;
var subtitle_language;
var speech_language='same';

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
      if (voices[i].lang.substr(0,2).toLowerCase() == navigator.language) opt.selected=true;
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
        if (voices[i].lang.substr(0,2).toLowerCase() == navigator.language) opt.selected=true;
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
        if (i == 0) {
          opt.selected=true;
          builtin_language=TLangInput[i];
        }
        TLangInput.push(lang);
        opt.innerHTML=lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
        select.appendChild(opt);
      }
    }
  }
}

function PopulateSubtitlesLang() {
  var select = document.getElementById('subtitlelanguage');
  for(var i=0; i<supportedlanguages.length; i++) {
      var lang=supportedlanguages[i].code; //.substr(0,5);
      var opt=document.createElement("option");
      opt.value=lang;
      if (i == 0) {
        opt.selected=true;
        subtitle_language=lang;
      }
      opt.innerHTML=lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
      select.appendChild(opt);
    }
}

function PopulateSpeechLang() {
  var select = document.getElementById('speechlanguage');
  for(var i=0; i<supportedlanguages.length; i++) {
      var lang=supportedlanguages[i].code; //.substr(0,5);
      var opt=document.createElement("option");
      opt.value=lang;
      opt.innerHTML=lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
      select.appendChild(opt);
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

function setLanguageSubtitle(i) {
  subtitle_language=i;
  //console.log("Subtitles set to "+subtitle_language+ " builtin_language is "+builtin_language);
}

function setLanguageSpeech(i) {
  speech_language=i;
  //console.log("Subtitles set to "+subtitle_language+ " builtin_language is "+builtin_language);
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

function RemoveVerbalTics(ch) {
  var T_verbaltics=document.getElementById('verbaltics').value.trim().split("\n");
  var ch2=ch;
  for(var i = 0; i<T_verbaltics.length; i++) {
    var fromword = T_verbaltics[i].trim();
    if (fromword.trim() != "") {
    if (fromword.indexOf("|") <= 0) {
	    ch2=(ch2+" ").replaceAll(fromword+" ","").trim();
	    ch2=ch2.replaceAll(fromword+",","");
    } else {
      var [fromword,toword]=fromword.split("|");
	    ch2=(ch2+" ").replaceAll(fromword+" ",toword+" ").trim();
	    ch2=ch2.replaceAll(fromword+",",toword+",");
	    ch2=ch2.replaceAll(fromword+".",toword+".");
    }
  }
  }
  return ch2;
}


var inputnumber=0;
function Traduction(ch) {
  ch = RemoveVerbalTics(ch);
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
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput+ "&ROOMID=" + document.getElementById('classroomid').value, true);

  xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      // console.log(this.responseText);
      var response = JSON.parse(this.responseText);
      if ((! response["error"]) && response["data"]["translations"]) {
        Parle(response["data"]["translations"][0]["translatedText"]);
        Ecrire(""+response["data"]["translations"][0]["translatedText"]);
      } else {
        Ecrire("---- Translation ERROR ----")
      }

    }
  };
  var d_begin=Date.now();
  console.log(d_begin+" : Sending for translation "+ch+" from "+linput+" to "+l)
  xhr.send();
  //EcrirePartial("Request sent....");
}

//var T_durations=[];

function TranslateThen(ch, l, linput,f) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput+ "&ROOMID=" + document.getElementById('classroomid').value, true);

  xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      var d_end=Date.now();
      console.log("Receiving translation in "+(d_end-d_begin)+"ms for "+ch+" from "+linput+" to "+l);
      //console.log(this.responseText);
      //T_durations[ch]=undefined;
      var response = JSON.parse(this.responseText);
      if ((! response["error"]) && response["data"]["translations"]) {
        f(response["data"]["translations"][0]["translatedText"]);
      } else {
        f("---- Translation ERROR ----");
      }

    }
  };
  var d_begin=Date.now();
  //console.log(d_begin+" : Sending for translation "+ch+" from "+linput+" to "+l);
  //T_durations[ch]=d;
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
function addSlideToNB(ch) {
  var tobesaidbetweenslides='';
  if (document.getElementById('tobesaid').value != '') tobesaidbetweenslides=document.getElementById('tobesaid').value+'.';
  addToNB(tobesaidbetweenslides,0);
  var Tch=ch.split('<br/>');
  for(var i=0; i<Tch.length;i++) {
    addToNB(Tch[i],0);
  }
}

function addToNB(ch,before) {
  var speechlanguage = document.getElementById('speechlanguage').value;
  var tobeinserted = '';
  if (speechlanguage != 'same') tobeinserted = speechlanguage+':|:';
  var tobesaidbetweenslides='';
  if (before == 1 && document.getElementById('tobesaid').value != '') tobesaidbetweenslides=tobeinserted+document.getElementById('tobesaid').value+'. <br/>';

  ch=tobesaidbetweenslides+tobeinserted+ch;
  document.getElementById('notebook').innerHTML+='<br/>'+ch;
  EcrireTitle(ch);
  //saveTextNB(document.getElementById('notebook').innerHTML);
  var Tch=ch.split('<br/>');
  saveTextTable(Tch,0);
  //for(var c in Tch) saveText(Tch[c].replace(/<br\/>/gi,"").replace(/<br>/gi,""));
  //saveremotespeech();
}

function saveTextTable(T,i) {
  if (i<T.length) {
    saveText(T[i].replace(/<br\/>/gi,"").replace(/<br>/gi,""));
    setTimeout(function () {saveTextTable(T,i+1);},100);
  }
}

function b64toBlob(b64Data, contentType, sliceSize) {
                contentType = contentType || '';
                sliceSize = sliceSize || 512;

                var byteCharacters = atob(b64Data);
                var byteArrays = [];

                for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                    var slice = byteCharacters.slice(offset, offset + sliceSize);

                    var byteNumbers = new Array(slice.length);
                    for (var i = 0; i < slice.length; i++) {
                        byteNumbers[i] = slice.charCodeAt(i);
                    }

                    var byteArray = new Uint8Array(byteNumbers);

                    byteArrays.push(byteArray);
                }

              var blob = new Blob(byteArrays, {type: contentType});
              return blob;
            }


function ShowImage(n) {
  //console.log('IMG OBJ = '+document.getElementById("img_"+n));
  //console.log('IMG URL = "'+document.getElementById("img_"+n).src+'"');
  document.getElementById('titreright').innerHTML=n;
  if (document.getElementById("img_"+n) && document.getElementById("img_"+n).src.length>200) {
  document.getElementById("img_slides").src=document.getElementById("img_"+n).src;
  resizeSlides();

  // Diffusion Image
  	var request = new XMLHttpRequest();
  	request.open("POST", "saveimage.php");
    var f=new FormData();
    f.append('random',Math.floor(Math.random() * 10000));
    var imageurl=document.getElementById("img_"+n).src;
    /*
    var nimages=0;
    var maxcar=10000;
    for(var i=0; i*maxcar<imageurl.length;i++) {
      f.append('image'+i,imageurl.substr(i*maxcar,maxcar));
      nimages++;
    }
    f.append('nbimages',nimages);
    */
    var block = imageurl.split(";");
    var contentType = block[0].split(":")[1];
    var realData = block[1].split(",")[1];
    var blob = b64toBlob(realData, contentType);
    f.append("image", blob);
    f.append('ROOMID',document.getElementById('classroomid').value);
  	request.send(f);
  //	request.onload = function(event) {};
}
}

var currentPresentation=[];
var currentDiscours=-1;

function formateresponse(datatxt) {
  console.log(datatxt);
  currentPresentation=[];
  //alert(datatxt);
  var res = '';
  var data = JSON.parse(datatxt);
  for (var i=0; i<data.length; i++) {
    slide = data[i];
    var txtnotes = '';
    for (var j=0; j<slide["Notes"].length; j++) {
      if (slide["Notes"][j].indexOf("[forward]")>=0 || slide["Notes"][j].indexOf("[backward]")>=0 || slide["Notes"][j].indexOf("[pause")>=0 || slide["Notes"][j].indexOf("[resource")>=0  || slide["Notes"][j].indexOf("[mouse")>=0|| slide["Notes"][j].indexOf("[notification")>=0) {
        slide["Notes"][j]='';
      } else {
      txtnotes = txtnotes + slide["Notes"][j]+' <br/>';
    }
    }
    res += '<dl>';
    if (slide["Images"]) {
      //alert("The presentation contains "+slide["Images"].length+" images");
      var toexec="";
      for(var j=1; j<=slide["Images"].length;j++) {
        toexec+="document.getElementById('img_"+j+"').src='"+slide["Images"][j-1]+"'; ";
      }
      setTimeout(toexec,10);
    } else {
//      res = res + '<dt><span onclick="addToNB(\''+txtnotes+'\',1); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
//      res = res + '<dt><span onclick="addSlideToNB(\''+txtnotes+'\'); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
      res = res + '<dt>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
      var flag=false;
      for (var j=0; j<slide["Notes"].length; j++) {
        if (slide["Notes"][j].trim() != '') {
        res = res + '<dd><span onclick="addToNB(\''+slide["Notes"][j]+'\',0); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+'; saveremotespeech();">▶️</span>&nbsp;'+slide["Notes"][j]+'</dd>\n';
        
          currentPresentation.push({'Slide':slide["Slide"],'Discours':slide["Notes"][j]});
          flag = true;
        }
      }
      if (!flag) currentPresentation.push({'Slide':slide["Slide"],'Discours':''});
    }
    res+='</dl>';
  }
//  alert(res);
console.log("DEBUG : "+res);
  return res;
}

function saveremotespeech() {
  if (currentPresentation.length>0) {
    var currentDiscoursB=(currentDiscours-1+currentPresentation.length)%currentPresentation.length;
    var currentDiscoursA=(currentDiscours+1)%currentPresentation.length;
    var txt = currentPresentation[currentDiscoursB]['Discours']+':|:'+currentPresentation[currentDiscours]['Discours']+':|:'+currentPresentation[currentDiscoursA]['Discours'];
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "savespeechforremote.php");
  var f=new FormData();
  f.append('ROOMID',document.getElementById('classroomid').value);
  f.append('SPEECH',txt);
	request.send(f);
	request.onload = function(event) {
    var data=request.responseText;
  };
}
}
}


function SkipForward() {
  if (currentPresentation.length>0) {
    currentDiscours=(currentDiscours+1)%currentPresentation.length;
    ShowImage(currentPresentation[currentDiscours]['Slide']);
    saveremotespeech();
  }
}

function SkipBackward() {
  if (currentPresentation.length>0) {
    currentDiscours=(currentDiscours-1+currentPresentation.length)%currentPresentation.length;
    ShowImage(currentPresentation[currentDiscours]['Slide']);
    saveremotespeech();
  }
}


function Forward() {
  if (currentPresentation.length>0) {
    currentDiscours=(currentDiscours+1)%currentPresentation.length;
    addToNB(currentPresentation[currentDiscours]['Discours'],0);
    ShowImage(currentPresentation[currentDiscours]['Slide']);
    saveremotespeech();
  }
}

function Backward() {
  if (currentPresentation.length>0) {
    currentDiscours=(currentDiscours-1+currentPresentation.length)%currentPresentation.length;
    addToNB(currentPresentation[currentDiscours]['Discours'],0);
    ShowImage(currentPresentation[currentDiscours]['Slide']);
    saveremotespeech();
  }
}
var inSlideTab=false;


function completeHandler(event) {
//  alert(event.target.responseText);
  _("notebookcompleteformatted").innerHTML = formateresponse(event.target.responseText);
//  _("slideshow").innerHTML = '<iframe src = "ViewerJS/#../tmpfiles/DOCUMENT_'+document.getElementById('classroomid').value+'.odp" width="400" height="300" allowfullscreen webkitallowfullscreen></iframe>';
  _("progressBar").value = 0;
  //alert(event.target.responseText);
}

function SlideNotes() {
  var file = _("ppt_presentation").files[0];
  var scriptfile = "";
  if (file.name.endsWith('pptx')) scriptfile = "ExtractSlidesNotes.php";
  if (file.name.endsWith('odp')) scriptfile = "ExtractSlidesNotes.php";
  if (file.name.endsWith('txt')) scriptfile = "ExtractSlidesNotesTxt.php";
  if (file.name.endsWith('html')) scriptfile = "ExtractSlidesNotesAppHTML.php";
  if (scriptfile == "") {alert('Only pptx, odp, html and txt files are allowed !'); return -1;}
  var formdata = new FormData();
  formdata.append("ppt_presentation", file);
  formdata.append("ACCESS", document.getElementById('classroomid').value);

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
builtin_recognition.continuous = false;
builtin_recognition.continuous = true;
builtin_recognition.interimResults = true;
builtin_recognition.enableAutomaticPunctuation= true;
builtin_recognition.maxAlternatives = 1;
var builtin_recognitionon = false;
var builtin_externaltranscritionon=false;

function StartSpeechRecognition(callback) {
  builtin_recognition.lang = builtin_language;
  builtin_recognition.onresult = function(event) {
    if (builtin_recognitionon && ! builtin_externaltranscritionon) {
      //console.log(event.results);
      var partial='';
      for (var i = event.resultIndex; i < event.results.length; i++) {
        if (event.results[i].isFinal) {
          partial=event.results[i][0].transcript; //+" > ";
          FreeResources();
          callback(event.results[i][0].transcript);
        } else {
          partial+=' '+event.results[i][0].transcript;
        }
      }
      if (partial.trim() != '') {
        if (document.getElementById('recognitionon').checked) EcrirePartial(RemoveVerbalTics(partial));
      }
      //builtin_recognition.stop();
    }
  };
  builtin_recognition.onend = function(event) {
    if (builtin_recognitionon) {
      StartSpeechRecognition(callback);
    }
  };
  builtin_recognitionon = true;
  try {
    builtin_recognition.start();
  } catch {}
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
  EcrireTitle(ch);
}

function EcrireTitle(ch) {
  var linput = builtin_language.substr(0,2).toLowerCase();
  if (ch.indexOf(':|:')>=0) { // If there are a language indication
    var Tch=ch.split(':|:');
    linput = Tch[0].substr(0,2).toLowerCase();
    ch=Tch[1];
  }
  var loutput = subtitle_language.substr(0,2).toLowerCase();
  if ((! document.getElementById('subtitleon').checked) || (linput == loutput)) {
    document.getElementById('titre').innerHTML=ch;
    // for scrollbar
    setScrollvalues(ch.length);
  } else {
    TranslateThen(ch,loutput,linput,function (ch){document.getElementById('titre').innerHTML=ch; setScrollvalues(ch.length);});
  }
}

function EcrirePartial(ch) {
  document.getElementById('partialtitre').innerHTML=ch;
}

function EcrireFeedback(ch) {
  EcrireNB(ch);
  document.getElementById('studentfeedback').innerHTML=ch;
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
  var inputlanguage = builtin_language;
  if (ch.indexOf(':|:')>=0) { // If there are a language indication
    var Tch=ch.split(':|:');
    inputlanguage = Tch[0];
    ch=Tch[1];
  }
	var request = new XMLHttpRequest();
	request.open("POST", "savetext.php");
  var f=new FormData();
  f.append('lineid',inputnumber);
  inputnumber++;
  f.append('text',ch);
  f.append('inputlanguage',inputlanguage);
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

var delayReadtextQ = 100;
function LaunchReadTextQ() {
  readTextQ();
  setTimeout(LaunchReadTextQ,delayReadtextQ);
}

var delayRemote = 200;
function LaunchRemote() {
  remote();
  setTimeout(LaunchRemote,delayRemote);
}

var delayStudentReadtext = 500;
function LaunchStudentReadText() {
  StudentreadText();
  setTimeout(LaunchStudentReadText,delayStudentReadtext);
}

function FreeResources() {
  delayStudentReadtext = 1000;
  delayReadtextQ = 1000;
  delayRemote = 1000;
  setTimeout(function () {delayStudentReadtext=500; delayReadtextQ=100; delayRemote=200;},2000);
}

function Lancer() {
  LaunchRemote();
  LaunchReadTextQ();
  LaunchStudentReadText();
  setInterval(ScrollBar,scrollinterval);
}



function Init() {
  PopulateSubtitlesLang();
  PopulateSpeechLang();

/*  document.getElementById('question').addEventListener('keyup', function (event) {
    if (event.code === 'Enter') {
        saveTextQ(document.getElementById('question').value);
      event.preventDefault();
      event.stopPropagation();
    }
  },false);
*/
  resizeSlides();

  document.getElementById('pincode').value=(""+Math.floor(100000+Math.random()*100000)).substr(1);
  document.getElementById('sharedpin').innerHTML=document.getElementById('pincode').value;
  document.getElementById('sharedpin2').innerHTML=document.getElementById('pincode').value;
  Synchronize_save();

  document.onkeyup=function (event) {
    if (inSlideTab) {
/*      if (event.code === 'ArrowRight') {
        Forward();
      }
      if (event.code === 'ArrowLeft') {
        Backward();
      }

*/
      if (event.code === 'Space') {
        Forward();
      }
      event.preventDefault();
      event.stopPropagation();
    }
  };
  document.onkeydown=function (event) {
    if (inSlideTab) {
      event.preventDefault();
      event.stopPropagation();
    }
  };
  document.onkeypress=function (event) {
    if (inSlideTab) {
      event.preventDefault();
      event.stopPropagation();
    }
  };
  ChangeColor('titre',document.getElementById('ColorTitre').value);
  ChangeColor('titreleft',document.getElementById('ColorTitre').value);
  ChangeColor('partialtitre',document.getElementById('ColorPartialTitre').value);
  ChangeColor('studentfeedback',document.getElementById('Colorstudentfeedback').value);

  //setTimeout(Micro,1000);
  //setTimeout(Micro,1500);
  //version addEventListener
  window.addEventListener('offline', (event) => {
    console.log("La connexion au réseau est perdue.");
    document.getElementById('connexion').style.visibility='visible';
    delayStudentReadtext=100000;
    delayReadtextQ=100000;
    delayRemote=100000;
  });
 window.addEventListener('online', (event) => {
    console.log("La connexion au réseau est rétablie.");
    document.getElementById('connexion').style.visibility='hidden';
    delayStudentReadtext=500;
    delayReadtextQ=100; 
    delayRemote=200;
 });

}

function ClearCR() {
  document.getElementById('classroom').innerHTML='';
  ScreenCR=[];
}

function rgb(T) {
  return "rgb("+T[0]+","+T[1]+","+T[2]+")";
}


function recharger() {
  ClearCR();
  inputnumberQ=0;
  readTextQ();
  
}

var ColorsForCR=[];
ColorsForCR["----"]=[[255,0,0],[255,255,255],5];
ColorsForCR["Teacher"]=[[0,0,255],[255,255,255],5];

var ScreenCR=[];

function RefreshCR() {
  document.getElementById('classroom').innerHTML = "";
  for(var i=0; i<ScreenCR.length; i++) {
    if (ScreenCR[i] != undefined) {
      if (document.getElementById('classroom').innerHTML == "") {
    document.getElementById('classroom').innerHTML=ScreenCR[i]+'\n';
      } else {document.getElementById('classroom').innerHTML=document.getElementById('classroom').innerHTML+"<br/><br/>"+ScreenCR[i]+'\n';}
    }
  }
}

function EcrireCR(who,ch,order) {
  if (! ColorsForCR[who]) {
    var r=Math.floor(Math.random()*200+55);
    var g=Math.floor(Math.random()*200+55);
    var b=Math.floor(Math.random()*200+55);
    var bg=[r,g,b];
    var fg=[0,0,0];
    ColorsForCR[who]=[bg,fg,20];
  }

/*
  if (document.getElementById('classroom').innerHTML == "")
    document.getElementById('classroom').innerHTML='<span style="color:'+rgb(ColorsForCR[who][1])+'; background-color:'+rgb(ColorsForCR[who][0])+'; margin-left: '+ColorsForCR[who][2]+'px; ">'+(who+' : '+ch)+'<span>\n';
  else document.getElementById('classroom').innerHTML=document.getElementById('classroom').innerHTML+"<br/><br/>"+'<span style="display:inline;">'+order+'</span>\t<span style="color:'+rgb(ColorsForCR[who][1])+'; background-color:'+rgb(ColorsForCR[who][0])+'; margin-left: '+ColorsForCR[who][2]+'px;">'+(who+' : '+ch)+'<span>\n';
  */
  ScreenCR[order] = '<span style="color:'+rgb(ColorsForCR[who][1])+'; background-color:'+rgb(ColorsForCR[who][0])+'; margin-left: '+ColorsForCR[who][2]+'px; ">'+(who+' : '+ch)+'<span>';
  RefreshCR();
}

document.addEventListener('submit', event => {
  event.preventDefault();
  // actual logic, e.g. validate the form
  //alert('Form submission cancelled.');
});

var inputnumberQ=0;
var NBconnectedusers=0;
var Tconnectedusers=[];

function TailleCR() {
  return document.getElementById('classroom').innerHTML.split('<br/>').length;
}

function setLanguageStr(l) {
  console.log("Remote says set language to ("+l+")");
  // Try to find the language in the language list
  var s=document.getElementById('classroomlanguage');
  var finded=-1
  for(var i=0; i<s.options.length && finded == -1; i++) {
    if (l.substr(0,2).toLowerCase() == s.options[i].innerHTML.substr(0,2).toLowerCase()) finded=i;
  }
  if (finded!=-1) {
    s.selectedIndex=finded;
    setLanguageInput(document.getElementById('classroomlanguage').value);
    Micro(); Micro();
  }
}

function remote() {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "saveremoteorder.php");
  var f=new FormData();
  f.append('ROOMID',document.getElementById('classroomid').value);
  f.append('APPID',document.getElementById('appid').value);
  f.append('ORDER','');
	request.send(f);
	request.onload = function(event) {
    var order=request.responseText;
    if (order != '') {
      console.log('Get order '+order);
      if (order == 'forward') Forward();
      if (order == 'backward') Backward();
      if (order == 'skipforward') SkipForward();
      if (order == 'skipbackward') SkipBackward();
      if (order == 'playcurrent') {SkipBackward(); Forward();}
      if (order.substr(0,"micro".length)=="micro") {
        var fromremote=order.substr("micro".length).trim();
        if (fromremote == document.getElementById('appid').value || fromremote=='') Micro();
      }
      if (order.substr(0,"language".length)=="language") setLanguageStr(order.substr("language".length).trim());
    }
  };
}
}

function updateUsers() {
  document.getElementById('connectedusers').innerHTML=(NBconnectedusers)+" connected users";
  document.getElementById('connectedusers').title=Tconnectedusers.join(", ");
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
    var nbnewQ=0;
    for(var i=inputnumberQ; i<T.length; i++) {
      var L=T[i].split(';');
      if (L[0] == 'Teacher' && L[2].indexOf("[extratab") >= 0) {
        console.log('Opening an extra tab');
        // feedback
        var resource = L[2].replace("[extratab","").replace("]","").split("|");
        var tabnumber=resource[0];
        var url=resource[1];
        //showET(url,tabnumber);
        // A compléter, il faut modifier les champs de l'interface et appeler la bonne fonction showET.

      } else {
        if (L[2].indexOf("[newstudentarrived]")>= 0) {
          NBconnectedusers++;
          Tconnectedusers.push(L[0]);
          updateUsers();
        } else {
          if (L[2].indexOf("[newstudentlived]")>= 0) {
          NBconnectedusers--;
          Tconnectedusers.filter(student => student != L[0]);
          updateUsers();
        } else {

        if (L[0] == 'ROBOT') {
          if (builtin_externaltranscritionon) Traduction(L[2]);
        } else {
          nbnewQ++;
          console.log('Debug -- '+T[i]+'     '+L[2]+'  :  '+L[1].substr(0,2)+'   --> '+builtin_language.substr(0,2)+' voice='+builtin_voice.lang.substr(0,2));
          if (builtin_language.substr(0,2) != L[1].substr(0,2)) {
            TranslateQ(L[2], builtin_language.substr(0,2), L[1].substr(0,2), L[0],i);

            // TranslateQ(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2), L[0],i);
          } else {
            EcrireCR(L[0],L[2],i);
          }
        }
        }
        }
      }
    }
    // Is there a new question there ?
    if (nbnewQ>0) EventNewQuestion();
    //if (inputnumberQ!=T.length) EventNewQuestion();
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
  f.append('inputlanguage',builtin_language);
  f.append('ROOMID',document.getElementById('classroomid').value);
	request.send(f);
  request.onload = function(event) {
    readTextQ();
  };
}

function TranslateQ(ch, l, linput,name,order) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput, true);

  xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      // console.log(this.responseText);
      var response = JSON.parse(this.responseText);
      if ((! response["error"]) && response["data"]["translations"]) {
        // Parle(response["data"]["translations"][0]["translatedText"]);
        EcrireCR(name,response["data"]["translations"][0]["translatedText"],order);
      } else {
        //EcrireCR("----","---- Translation ERROR ----")
        console.log("---- Translation ERROR ----");
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

function GoFS() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
    document.getElementById('bar').style.transform="scaleY(1)";
    //document.getElementById('bar').style.bottom="10px";
    //resizeSlides();
  } else {
    document.exitFullscreen();
    document.getElementById('bar').style.transform="scaleY(1)";
    //resizeSlides();
  }
}

function resizeWindow(wname) {
  var wc=document.getElementById(wname);
  wc.style.width = (window.innerWidth-20)+"px";
  wc.style.height = (window.innerHeight-48)+"px";
  wc.style.top = "48px";
  wc.style.left = "0px";
}

function resizeSlides() {
  var slide = document.getElementById('img_slides');
  var ratio = slide.width/slide.height;
  var aggrandissmentX=(window.innerWidth-30)/slide.width;
  var aggrandissmentY=(window.innerHeight-80)/slide.height;
  if (aggrandissmentX>aggrandissmentY) {
    /*
    slide.width=aggrandissmentY*slide.width;
    slide.style.width=aggrandissmentY*slide.width+"px";
    slide.height=aggrandissmentY*slide.height;
    slide.style.height=aggrandissmentY*slide.height+"px";
    */
    slide.style.transform='scaleX('+aggrandissmentY+') scaleY('+aggrandissmentY+')';
//    slide.style.transform='scaleX('+aggrandissmentX+') scaleY('+aggrandissmentY+')';
  } else {
/*    slide.width=aggrandissmentX*slide.width;
    slide.style.width=aggrandissmentX*slide.width+"px";
    slide.height=aggrandissmentX*slide.height;
    slide.style.height=aggrandissmentX*slide.height+"px";
*/
    slide.style.transform='scaleX('+aggrandissmentX+') scaleY('+aggrandissmentX+')';

//slide.style.transform='scale('+aggrandissmentX+')';

  }

// For wooclap and others
  ["wooclap","extratab1","extratab2","extratab3","extratab4","visio"].map(resizeWindow)
}

function Synchronize(pin) {
	var request = new XMLHttpRequest();
	request.open("POST", "synchronize.php");
  var f=new FormData();
  f.append('pincode',pin);
	request.send(f);
	request.onload = function(event) {
    if (request.responseText != "") {
      document.getElementById('classroomid').value=request.responseText;
      document.getElementById('pincode').value=pin;
      document.getElementById('sharedpin').innerHTML=pin;
      document.getElementById('sharedpin2').innerHTML=pin;
      updateLinks();
    } else {
      alert("ERROR : unknown pin code");
    }
  };
}

function Synchronize_save() {
  if (document.getElementById('pincode').value!="") {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "synchronize_savepin.php");
  var f=new FormData();
  f.append('pincode',document.getElementById('pincode').value);
  f.append('roomid',document.getElementById('classroomid').value);
	request.send(f);
	request.onload = function(event) {};
  }
}
}

window.onresize=resizeSlides;

function showET(obj,num) {
  if (obj.checked) {
    var url=document.getElementById('extraurl'+num).value;
    if (url!="") {
      document.getElementById('spanembedET'+num).innerHTML='Yes !'; 
      show('titleextratab'+num);
      document.getElementById('extratab'+num).src=url;
    } else {
      alert('Enter your url first !');
      obj.checked=false;
      document.getElementById('spanembedET'+num).innerHTML='No.';  
      hide('titleextratab'+num);
    }
  } else {
    document.getElementById('spanembedET'+num).innerHTML='No.';  
    hide('titleextratab'+num);
  }
}

function Micro() {
  builtin_recognition.abort();
  var mybox = document.getElementById('micon');
  document.getElementById('recognitionon').checked = !document.getElementById('recognitionon').checked;
  builtin_recognitionon=document.getElementById('recognitionon').checked;
  if (document.getElementById('recognitionon').checked) {
    mybox.innerHTML='<i class=\'material-icons blink big\'>record_voice_over</i>';
  } else {
    mybox.innerHTML='<i class=\'material-icons big\'>voice_over_off</i>';
  }
  if (document.getElementById('recognitionon').checked) {
      StartSpeechRecognition(Traduction);
  }/* else {
    builtin_recognition.abort();
  }*/
  
}

var currentscroll=0;
var currentindent=0;
var scrollinterval=50;

function ScrollBar() {
  currentscroll=currentscroll-currentindent;
  if (currentscroll<0) currentscroll=0;
  document.getElementById('scrollbar').style.transform='scaleX('+currentscroll+')';
}

function setScrollvalues(l) {
    currentscroll=1;
    var txtlength=Math.max(20,l);
		//txtlength=Math.min(200,txtlength);
		var duration=55*txtlength; // estimated duration in ms
    currentindent=scrollinterval/duration;
}


function updateLinks() {
  var roomid = document.getElementById('classroomid').value;
  document.getElementById("link1").src="https://tableaunoir.github.io/?id="+roomid;
  document.getElementById("link1").innerHTML=document.getElementById("link1").src;
  document.getElementById("link2").src="https://wbo.ophir.dev/boards/"+roomid;
  document.getElementById("link2").innerHTML=document.getElementById("link2").src;
  document.getElementById("link3").src="https://meet.jit.si/"+roomid;
  document.getElementById("link3").innerHTML=document.getElementById("link3").src;
}

function ChangeColor(boxname,color) {
  var [bgcolor,bordercolor,textcolor]=color.split('|');
  var e=document.getElementById(boxname);
  if (textcolor) e.style.color = 'rgb('+textcolor+')'; else e.style.color = 'rgb(255,255,255)';
  e.style.backgroundColor = 'rgba('+bgcolor+',0.5)';
  e.style.boxShadow = '2px 2px 5px rgb('+bgcolor+')';
  e.style.textShadow = '2px 0 0 rgb('+bordercolor+'), -2px 0 0 rgb('+bordercolor+'), 0 2px 0 rgb('+bordercolor+'), 0 -2px 0 rgb('+bordercolor+'), 1px 1px rgb('+bordercolor+'), -1px -1px 0 rgb('+bordercolor+'), 1px -1px 0 rgb('+bordercolor+'), -1px 1px 0 rgb('+bordercolor+')';
}


function Embedcode() {
  var url=document.getElementById('chatlink').href;
//  navigator.clipboard.writeText('<iframe style="width:100%; overflow: auto; height: 500px" src="'+url+'"></iframe>');
//  alert("HTML code copied to clipboard !");
document.getElementById('embedcontent').value='<iframe style="width:100%; overflow: auto; height: 500px" src="'+url+'"></iframe>';
}

function sendnotebook() {
  document.getElementById('dialog_MailFrom').value='bourdon-j@univ-nantes.fr';
  document.getElementById('dialog_MailTo').value='your.mail@univ-nantes.fr';
  document.getElementById('dialog_MailSubject').value='Content of the lesson '+document.getElementById('classroomid').value;
  document.getElementById('dialog_MailBody').innerHTML=document.getElementById('notebook').innerHTML;
  $( "#dialog-mail" ).dialog("open");
}







</script>


<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body onload="Init(); InitVoices(); setTimeout(InitVoices,1000); setTimeout(InitVoices,2000); /*setTimeout(function () {StartSpeechRecognition(Traduction); },3000); */ setTimeout(Lancer,3000); ready(); updateLinks(); Embedcode();">


<div id="connexion"><i class='material-icons big'>signal_wifi_off</i></div>

<div id="pincodediv" title="Pincode" onclick="if (sizepincodediv == 1) sizepincodediv=0.1; else sizepincodediv=1; this.style.transform='scaleX('+sizepincodediv+')';"><!--<span style="font-size: 0.5em;">Pin code</span><br/>--><span title="" id="sharedpin" style="padding: auto;"></span></div>
<div id="micon" onclick="Micro();"><i class='material-icons big'>voice_over_off</i></div>

<div id="questionicon"><i class='material-icons big'>thumb_up</i></div>

<div id="logonext"><img src="images/logo-next-omist.png" alt="NExT" style="width: 109px; height: 50px;"/></div>


<!--  // change here for touchable
<div id="titreleft" onclick="Backward();">&#9666;</div>
<div id="titreright"></div>
<div id="titre" onclick="Forward();"><h1>Automatic translation for teaching purposes: Student</h1></div>
<div id="soustitre"></div>
-->

<div id="titreleft" onmouseover="document.getElementById('soustitre').style.transform='translateY(0px)'; document.getElementById('titreleft').style.transform='translateY(0px)'; document.getElementById('titreright').style.transform='translateY(0px)'; document.getElementById('titre').style.transform='translateY(0px)';" onclick="Backward();">&#9666;</div>
<div id="titreright"></div>
<div id="titre" onclick="Forward();"  onmouseover="document.getElementById('soustitre').style.transform='translateY(0px)'; document.getElementById('titreleft').style.transform='translateY(0px)'; document.getElementById('titreright').style.transform='translateY(0px)'; document.getElementById('titre').style.transform='translateY(0px)';"><h1>Automatic translation for teaching purposes: Teacher</h1></div>
<div id="soustitre" onmouseover="this.style.transform='translateY(-64px)'; document.getElementById('titreleft').style.transform='translateY(-64px)'; document.getElementById('titreright').style.transform='translateY(-64px)'; document.getElementById('titre').style.transform='translateY(-64px)';" ></div>
<div id="partialtitre"></div>
<div id="studentfeedback"></div>


<div id="scrollbar"></div>

<form id="myform">

<div id="tabs">
	<ul id="bar">
		<li><a href="#tabs-0" onclick="inSlideTab=false;">Informations</a></li>
		<li><a href="#tabs-1" onclick="timenew_question=0; inSlideTab=false;">Classroom</a></li>
		<li><a href="#tabs-2" onclick="inSlideTab=false;">Notebook</a></li>
		<li><a href="#tabs-5" onclick="inSlideTab=false;">Complete lesson</a></li>
    <li id="titleSlides"><a href="#tabs-7" onclick="inSlideTab=true; document.getElementById('img_slides').focus();">Slides</a></li>
		<li id="titlevisio"><a href="#tabs-6" onclick="if (!visioactive) document.getElementById('visio').src='https://meet.jit.si/Translator_'+document.getElementById('classroomid').value; visioactive=true; inSlideTab=false;">Visio</a></li>
		<li id="titlelive"><a href="#tabs-3" onclick="inSlideTab=false;">Live translation</a></li>
		<li id="titleshare"><a href="#tabs-4" onclick="inSlideTab=false;">Share</a></li>
		<li id="titlewooclap"><a href="#tabs-8" onclick="inSlideTab=false;">WOOCLAP</a></li>
		<li id="titleextratab1"><a href="#tabs-9" onclick="inSlideTab=false;">1</a></li>
		<li id="titleextratab2"><a href="#tabs-10" onclick="inSlideTab=false;">2</a></li>
		<li id="titleextratab3"><a href="#tabs-11" onclick="inSlideTab=false;">3</a></li>
		<li id="titleextratab4"><a href="#tabs-12" onclick="inSlideTab=false;">4</a></li>
	</ul>
	<div id="tabs-0">
  <ul style="list-style-type: none;">
<li><label for="classroomlanguage">Speaker language :</label>
  <select id="classroomlanguage" onchange="setLanguageInput(this.value);">
  </select>
</li>
<li style="display: none;"><label for="recognitionon">Speech recognition active ? </label>
<input type="checkbox" id="recognitionon"/></li>
<li>Pin code : <input type="text" id="pincode" style="width: 50px;" disabled/> &nbsp;
    <input type="button" value="Change pin" onclick="document.getElementById('pincode').value=prompt('Enter a PIN code'); Synchronize_save();"/>
    <input type="button" value="Synchronize" onclick="Synchronize(prompt('Enter a PIN code'));"/>
</li>
  <li>&nbsp;</li>
<li>Translate subtitles ?  <input type="checkbox" id="subtitleon" onclick="if (this.checked) document.getElementById('spansubtitleon').innerHTML='Yes !'; else document.getElementById('spansubtitleon').innerHTML='No.';"/>&nbsp;<span id="spansubtitleon">No.</span></li>
<li>Language for subtitles : <select id="subtitlelanguage" onchange="setLanguageSubtitle(this.value);">
  </select></li>
<li>Show subtitles bar ?  <input type="checkbox" checked id="subtitlebaron" onclick="if (this.checked) {document.getElementById('spansubtitlebaron').innerHTML='Yes !'; document.getElementById('titre').style.visibility='visible'; } else {document.getElementById('spansubtitlebaron').innerHTML='No.'; document.getElementById('titre').style.visibility='hidden'; }"/>&nbsp;<span id="spansubtitlebaron">Yes !</span>&nbsp;Color : <select id="ColorTitre" onchange="ChangeColor('titre',this.value); ChangeColor('titreleft',this.value);">
<option value="0,0,255|0,0,255" selected>blue</option>
<option value="0,255,0|0,100,0">green</option>
<option value="255,0,0|100,0,0" >red</option>
<option value="255,0,255|100,0,255" >purple</option>
<option value="255,255,0|100,100,0" >yellow</option>
<option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
<option value="0,255,0|0,255,0|0,50,0">green inverted</option>
<option value="255,0,0|255,0,0|20,0,0">red inverted</option>
<option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
<option value="255,255,0|200,200,0|50,50,0" >yellow inverted</option>
<option value="0,0,0|0,0,0">black</option>
<option value="255,255,255|255,255,255|0,0,0">white</option>

  </select></li>
<li>Show instant transcription ?  <input type="checkbox" id="subtitlepartialon" onclick="if (this.checked) {document.getElementById('spansubtitlepartialon').innerHTML='Yes !'; document.getElementById('partialtitre').style.visibility='visible'; } else {document.getElementById('spansubtitlepartialon').innerHTML='No.'; document.getElementById('partialtitre').style.visibility='hidden'; }"/>&nbsp;<span id="spansubtitlepartialon">No.</span>&nbsp; Color : <select id="ColorPartialTitre" onchange="ChangeColor('partialtitre',this.value);">
<option value="0,0,255|0,0,255">blue</option>
<option value="0,255,0|0,100,0">green</option>
<option value="255,0,0|100,0,0" >red</option>
<option value="255,0,255|100,0,255" >purple</option>
<option value="255,255,0|100,100,0" >yellow</option>
<option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
<option value="0,255,0|0,255,0|0,50,0">green inverted</option>
<option value="255,0,0|255,0,0|20,0,0">red inverted</option>
<option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
<option value="255,255,0|200,200,0|50,50,0" >yellow inverted</option>
<option value="0,0,0|0,0,0" selected>black</option>
<option value="255,255,255|255,255,255|0,0,0">white</option>
  </select></li>
<li>Show student feedback ?  <input type="checkbox" id="subtitlestudenton" onclick="if (this.checked) {document.getElementById('spansubtitlestudenton').innerHTML='Yes !'; document.getElementById('studentfeedback').style.visibility='visible'; } else {document.getElementById('spansubtitlestudenton').innerHTML='No.'; document.getElementById('studentfeedback').style.visibility='hidden'; }"/>&nbsp;<span id="spansubtitlestudenton">No.</span>&nbsp; Color : <select id="Colorstudentfeedback" onchange="ChangeColor('studentfeedback',this.value);">
<option value="0,0,255|0,0,255">blue</option>
<option value="0,255,0|0,100,0">green</option>
<option value="255,0,0|100,0,0" >red</option>
<option value="255,0,255|100,0,255" >purple</option>
<option value="255,255,0|100,100,0" >yellow</option>
<option value="0,0,255|0,0,255|0,0,50">blue inverted</option>
<option value="0,255,0|0,255,0|0,50,0">green inverted</option>
<option value="255,0,0|255,0,0|20,0,0">red inverted</option>
<option value="255,0,255|255,0,255|25,0,50">purple inverted</option>
<option value="255,255,0|200,200,0|50,50,0" >yellow inverted</option>
<option value="0,0,0|0,0,0" selected>black</option>
<option value="255,255,255|255,255,255|0,0,0">white</option>
  </select></li>
<li>Subtitles font size : <select onchange="document.getElementById('titre').style.fontSize = this.value; document.getElementById('partialtitre').style.fontSize = this.value; document.getElementById('studentfeedback').style.fontSize = this.value;">
<option value="12px">Small</option>
<option value="18px" selected>Normal</option>
<option value="24px" >Big</option>
<option value="32px" >Huge</option>
<option value="48px" >Enormous</option>
  </select></li>
  <li>&nbsp;</li>
	    <li title="Do not change unless you know what you are doing !"><label for="classroomid">Classroom identifier :</label><input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" /></li>
	    <li style="display:none;" title="Do not change unless you know what you are doing !"><label for="appid">Application identifier :</label><input type="text" name="appid" id="appid" value="<?php echo $appid; ?>" /></li>
  <li title="Do not change unless you know what you are doing !">External transcription service ?  <input type="checkbox" id="externaltranscritionon" onclick="if (this.checked) document.getElementById('spanexternaltranscritionon').innerHTML='Yes !'; else document.getElementById('spanexternaltranscritionon').innerHTML='No.'; builtin_externaltranscritionon=this.checked"/>&nbsp;<span id="spanexternaltranscritionon">No.</span></li>
  <li>&nbsp;</li>
  <li><b>Extra tabs</b></li>
  <li>Embed WOOCLAP ?  <input type="checkbox" checked id="embedwooclapon" onclick="if (this.checked) {document.getElementById('spanembedwooclapon').innerHTML='Yes !'; show('titlewooclap');} else {document.getElementById('spanembedwooclapon').innerHTML='No.';  hide('titlewooclap');}"/>&nbsp;<span id="spanembedwooclapon">Yes !</span><button onclick="window.open('https://app.wooclap.com/auth/login?lang=en&redirectTo=/home')">Login to wooclap first</button>
    <button onclick="document.getElementById('wooclap').src='https://app.wooclap.com/auth/login?lang=en&redirectTo=/home';">Update the wooclap tab</button></li>
 <!-- <li>Show Live translation tab ?  <input type="checkbox" id="embedliveon" onclick="if (this.checked) {document.getElementById('spanembedliveon').innerHTML='Yes !'; show('titlelive');} else {document.getElementById('spanembedliveon').innerHTML='No.';  hide('titlelive');}"/>&nbsp;<span id="spanembedliveon">No.</span></li>  -->
<!--  <li>Show Visio tab ?  <input type="checkbox" id="embedvisioon" onclick="if (this.checked) {document.getElementById('spanembedvisioon').innerHTML='Yes !'; show('titlevisio');} else {document.getElementById('spanembedvisioon').innerHTML='No.';  hide('titlevisio');}"/>&nbsp;<span id="spanembedvisioon">No.</span></li> -->
  <li>Show Share tab ?  <input type="checkbox" checked id="embedshareon" onclick="if (this.checked) {document.getElementById('spanembedshareon').innerHTML='Yes !'; show('titleshare');} else {document.getElementById('spanembedshareon').innerHTML='No.';  hide('titleshare');}"/>&nbsp;<span id="spanembedshareon">Yes !</span></li>
  <li>Extra tab 1  URL : <input type="text" id="extraurl1" value="" placeholder="Copy your application url here"/>&nbsp; Show ?  <input type="checkbox" id="embedET1" onclick="showET(this,1);"/>&nbsp;<span id="spanembedET1">No.</span>&nbsp;<input type="button" value="Send to students" onclick="saveTextQ('[extratab1|'+document.getElementById('extraurl1').value+']');"/></li>
  <li>Extra tab 2  URL : <input type="text" id="extraurl2" value="" placeholder="Copy your application url here"/>&nbsp; Show ?  <input type="checkbox" id="embedET2" onclick="showET(this,2);"/>&nbsp;<span id="spanembedET2">No.</span>&nbsp;<input type="button" value="Send to students" onclick="saveTextQ('[extratab2|'+document.getElementById('extraurl2').value+']');"/></li>
  <li>Extra tab 3  URL : <input type="text" id="extraurl3" value="" placeholder="Copy your application url here"/>&nbsp; Show ?  <input type="checkbox" id="embedET3" onclick="showET(this,3);"/>&nbsp;<span id="spanembedET3">No.</span>&nbsp;<input type="button" value="Send to students" onclick="saveTextQ('[extratab3|'+document.getElementById('extraurl3').value+']');"/></li>
  <li>Extra tab 4  URL : <input type="text" id="extraurl4" value="" placeholder="Copy your application url here"/>&nbsp; Show ?  <input type="checkbox" id="embedET4" onclick="showET(this,4);"/>&nbsp;<span id="spanembedET4">No.</span>&nbsp;<input type="button" value="Send to students" onclick="saveTextQ('[extratab4|'+document.getElementById('extraurl4').value+']');"/></li>
    <li></li>
    <li><b>Extra functionalities</b></li>
    <li>Remove verbal tics (one per line in lowercase)<br/>
    <textarea id="verbaltics" rows="3" cols="20"></textarea>
    <li><span id="connectedusers"></span></li>
  </ul>
  <br/>
  <br/>
  <p>The Slide Manipulation Toolkit is accessible <a href="javascript:void(window.open('OMIST_Tools'))">here</a>.</p>
  <p>The following links to open source online tools can be useful for the extra tabs feature.
  <dl>
  <dd>Tableau noir blackboard : <a id="link1" src="https://tableaunoir.github.io/?id=test"></a></dd>
  <dd>WBO whiteboard : <a id="link2" src="https://wbo.ophir.dev/boards/test"></a></dd>
  <dd>Jitsi visioconference : <a id="link3" src="https://meet.jit.si/test"></a></dd>
  <dd>Symbolic computations (Xcas) : <a id="link4" src="https://algoscript.info/xcas">https://algoscript.info/xcas</a></dd>  
  <dd>Universal document viewer : <a id="link5" src="https://products.groupdocs.app/fr/viewer/pptx">https://products.groupdocs.app/fr/viewer/pptx</a></dd>
  </dl>
  </p>
  <br/>
  <br/>
  <br/>
  <br/>
  <br/>
  <br/>
  <br/>
  <br/>
	</div>
	<div id="tabs-1">
    <div id="classroom" style="border: solid 1px;"></div>

    <textarea id="question" placeholder="Your question here" rows="2" cols="50"></textarea><br/>
        <input type="button" value="send" class="button-3 tooltip" role="button" id="questionsend" onclick="sendclick();" title="Send your question"/>
    <input type="button" value="refresh" class="button-3 tooltip" role="button" title="Reload all the conversation and translate it if necessary." onclick="recharger();"/>
	</div>
	<div id="tabs-2">
    <input type="button" value="Send by mail" class="button-3 tooltip" role="button" title="Send the notebook by mail." onclick="sendnotebook();"/><br/>
    <div id="notebook" style="border: solid 1px;" contenteditable="true">
    </div>
    <!-- <input type="button" id="savenb" value="Save notebook" onclick="saveTextNB(document.getElementById('notebook').innerHTML);"/> -->
	</div>
	<div id="tabs-5">
    <div>
    <label for="ppt_presentation">Powerpoint presentation : </label>
    <input type="file" name="ppt_presentation" id="ppt_presentation" onchange="SlideNotes()" accept=".pptx,.odp,.txt,.html" max-size="50000"/>
    <label for="speechlanguage">Speech language : </label>
  <select id="speechlanguage" onchange="setLanguageSpeech(this.value);">
  <option value="same" selected>Same as speaker</option>
  </select>
  <span style="display:none">
   <label for="tobesaid" title="Portion of text inserted between each slides (eq. Next slide...)">Slide transition : </label>
    <input type="text" id="tobesaid" value="">
    </span>
    <br/>
    <progress id="progressBar" value="0" max="100" style="width:300px;"></progress>
    </div>
    <div id="notebookcompleteformatted" style="border: solid 1px; height: 600px; overflow: auto;">
    </div>
	</div>
	<div id="tabs-6">
    <div id="slideshow" style="border: solid 1px; height: 600px; overflow: auto;">
<iframe id="visio" src = "" width="100%" height="100%" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture"  style="position: relative; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
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
    <h3>For students</h3>
    <div>
    <ul style="list-style-type: none;">
  	    <li>Students application : <a id="sharedlink" href="Student.php?ROOMID=<?php echo $classroomid; ?>">Student.php?ROOMID=<?php echo $classroomid; ?></a></li>
        <li>Pin code : <span title="" id="sharedpin2" style="font-size: 5em;"></span></li>
        <li>QR code : <div title="" id="sharedqr" style="margin-left: auto; margin-right: auto;"></div></li>
    </ul>
    </div>
    <h3>For teachers</h3>
    <div>
    <ul style="list-style-type: none;">
        <li>Teacher's remote : <a id="remotelink" href="remote.php?ROOMID=<?php echo $classroomid; ?>&APPID=<?php echo $appid; ?>">remote.php?ROOMID=<?php echo $classroomid; ?></a>&nbsp;<input type="button" value="Language switcher" title="Add the language switcher capability to the remote (useful for multilingual speakers)." onclick="var difflang=prompt('Enter the list of languages (2 letter codes separated by commas).\n Examples : \'fr,en\' or \'fr,en,es,it\'',''); document.getElementById('remotelink').href=document.getElementById('remotelink').innerHTML+'&LANG='+difflang+'&APPID='+document.getElementById('appid').value; document.getElementById('remoteqr').innerHTML=''; new QRCode(document.getElementById('remoteqr'), {	text: document.getElementById('remotelink').href,	width: 128,	height: 128,	colorDark : '#0000ff',	colorLight : '#ffffff',	correctLevel : QRCode.CorrectLevel.L});"/></li>
        <li>QR code : <div title="" id="remoteqr" style="margin-left: auto; margin-right: auto;"></div></li>
        <li>Teacher's application : <a href="index.php?ROOMID=<?php echo $classroomid; ?>">index.php?ROOMID=<?php echo $classroomid; ?></a></li>
    </ul>
    </div>
        <h3>Chat only</h3>
    <div>
    <ul style="list-style-type: none;">
    <li>Chat only link : <a id="chatlink" href="chat.php?ROOMID=<?php echo $classroomid; ?>">chat.php?ROOMID=<?php echo $classroomid; ?></a></li>
      <li>Chat only embed code : <input type="text" id="embedcontent" value=""/>
        </li>
    </ul>
    </div>
  </div>
  <div id="tabs-7">
      <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAkACQAAD/4QCeRXhpZgAATU0AKgAAAAgABQESAAMAAAABAAEAAAEaAAUAAAABAAAASgEbAAUAAAABAAAAUgEoAAMAAAABAAIAAIdpAAQAAAABAAAAWgAAAAAAAACQAAAAAQAAAJAAAAABAAOShgAHAAAAEgAAAISgAgAEAAAAAQAAAvugAwAEAAAAAQAAAJYAAAAAQVNDSUkAAABTY3JlZW5zaG90/+EJIWh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8APD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS40LjAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIi8+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPD94cGFja2V0IGVuZD0idyI/PgD/7QA4UGhvdG9zaG9wIDMuMAA4QklNBAQAAAAAAAA4QklNBCUAAAAAABDUHYzZjwCyBOmACZjs+EJ+/+IP8ElDQ19QUk9GSUxFAAEBAAAP4GFwcGwCEAAAbW50clJHQiBYWVogB+UABgALAAYAIgACYWNzcEFQUEwAAAAAQVBQTAAAAAAAAAAAAAAAAAAAAAAAAPbWAAEAAAAA0y1hcHBsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASZGVzYwAAAVwAAABiZHNjbQAAAcAAAASCY3BydAAABkQAAAAjd3RwdAAABmgAAAAUclhZWgAABnwAAAAUZ1hZWgAABpAAAAAUYlhZWgAABqQAAAAUclRSQwAABrgAAAgMYWFyZwAADsQAAAAgdmNndAAADuQAAAAwbmRpbgAADxQAAAA+Y2hhZAAAD1QAAAAsbW1vZAAAD4AAAAAodmNncAAAD6gAAAA4YlRSQwAABrgAAAgMZ1RSQwAABrgAAAgMYWFiZwAADsQAAAAgYWFnZwAADsQAAAAgZGVzYwAAAAAAAAAIRGlzcGxheQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAG1sdWMAAAAAAAAAJgAAAAxockhSAAAAFAAAAdhrb0tSAAAADAAAAexuYk5PAAAAEgAAAfhpZAAAAAAAEgAAAgpodUhVAAAAFAAAAhxjc0NaAAAAFgAAAjBkYURLAAAAHAAAAkZubE5MAAAAFgAAAmJmaUZJAAAAEAAAAnhpdElUAAAAFAAAAohlc0VTAAAAEgAAApxyb1JPAAAAEgAAApxmckNBAAAAFgAAAq5hcgAAAAAAFAAAAsR1a1VBAAAAHAAAAthoZUlMAAAAFgAAAvR6aFRXAAAACgAAAwp2aVZOAAAADgAAAxRza1NLAAAAFgAAAyJ6aENOAAAACgAAAwpydVJVAAAAJAAAAzhlbkdCAAAAFAAAA1xmckZSAAAAFgAAA3BtcwAAAAAAEgAAA4ZoaUlOAAAAEgAAA5h0aFRIAAAADAAAA6pjYUVTAAAAGAAAA7ZlbkFVAAAAFAAAA1xlc1hMAAAAEgAAApxkZURFAAAAEAAAA85lblVTAAAAEgAAA95wdEJSAAAAGAAAA/BwbFBMAAAAEgAABAhlbEdSAAAAIgAABBpzdlNFAAAAEAAABDx0clRSAAAAFAAABExwdFBUAAAAFgAABGBqYUpQAAAADAAABHYATABDAEQAIAB1ACAAYgBvAGoAac7st+wAIABMAEMARABGAGEAcgBnAGUALQBMAEMARABMAEMARAAgAFcAYQByAG4AYQBTAHoA7QBuAGUAcwAgAEwAQwBEAEIAYQByAGUAdgBuAP0AIABMAEMARABMAEMARAAtAGYAYQByAHYAZQBzAGsA5gByAG0ASwBsAGUAdQByAGUAbgAtAEwAQwBEAFYA5AByAGkALQBMAEMARABMAEMARAAgAGMAbwBsAG8AcgBpAEwAQwBEACAAYwBvAGwAbwByAEEAQwBMACAAYwBvAHUAbABlAHUAciAPAEwAQwBEACAGRQZEBkgGRgYpBBoEPgQ7BEwEPgRABD4EMgQ4BDkAIABMAEMARCAPAEwAQwBEACAF5gXRBeIF1QXgBdlfaYJyAEwAQwBEAEwAQwBEACAATQDgAHUARgBhAHIAZQBiAG4A/QAgAEwAQwBEBCYEMgQ1BEIEPQQ+BDkAIAQWBBoALQQ0BDgEQQQ/BDsENQQ5AEMAbwBsAG8AdQByACAATABDAEQATABDAEQAIABjAG8AdQBsAGUAdQByAFcAYQByAG4AYQAgAEwAQwBECTAJAgkXCUAJKAAgAEwAQwBEAEwAQwBEACAOKg41AEwAQwBEACAAZQBuACAAYwBvAGwAbwByAEYAYQByAGIALQBMAEMARABDAG8AbABvAHIAIABMAEMARABMAEMARAAgAEMAbwBsAG8AcgBpAGQAbwBLAG8AbABvAHIAIABMAEMARAOIA7MDxwPBA8kDvAO3ACADvwO4A8wDvQO3ACAATABDAEQARgDkAHIAZwAtAEwAQwBEAFIAZQBuAGsAbABpACAATABDAEQATABDAEQAIABhACAAQwBvAHIAZQBzMKsw6TD8AEwAQwBEAAB0ZXh0AAAAAENvcHlyaWdodCBBcHBsZSBJbmMuLCAyMDIxAABYWVogAAAAAAAA8xYAAQAAAAEWylhZWiAAAAAAAACC3QAAPVn///+8WFlaIAAAAAAAAExOAAC06AAACuxYWVogAAAAAAAAJ6sAAA2+AADIhWN1cnYAAAAAAAAEAAAAAAUACgAPABQAGQAeACMAKAAtADIANgA7AEAARQBKAE8AVABZAF4AYwBoAG0AcgB3AHwAgQCGAIsAkACVAJoAnwCjAKgArQCyALcAvADBAMYAywDQANUA2wDgAOUA6wDwAPYA+wEBAQcBDQETARkBHwElASsBMgE4AT4BRQFMAVIBWQFgAWcBbgF1AXwBgwGLAZIBmgGhAakBsQG5AcEByQHRAdkB4QHpAfIB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DQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0N/90ABAAw/9oADAMBAAIRAxEAPwD9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA//Q/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooqCW5ghUtI4UDqSaAJ6K8x1/wCMXw38MuY9a8QWFtIP4GnUv/3yCTXES/tOfCaNto1SR/dLWd1/MIRXsYbh7NMRHnoYacl3UJNfgjycRn2WUJunXxMItdHOKf3XPoWivCrL9o74RXjrG2v29sznCi6DW+f+/gWvV9K8TaFrkIn0m+gu425DQyLIP0Nc+MyrG4TXFUZQ/wAUWvzR0YTNMFim1ha0Z2/lkn+TN6ikVlYZU5pa887gooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA//R/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACopZkhUs5wBSTzJBGZHOAK+B/jx8ery+u7jwb4MuDDDETFfX8R+YsODDCR0x0dx0+6vOSPe4d4dxec4tYTCLzbe0V3f6LqeHxBxBhcnwrxWKfkkt5Psv1eyR618Uf2k9B8IyzaL4cRdY1aPKuqPtt7dsf8tZBnn/ZUFvbFfEni34o+O/G0rtrurTeQx4tLVjbW6jPA2odzeh3MQfQV54Ska8kIue5wMsfUnkkn6k+9Pxjg1/TnDnAuV5RB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aMy8ZlkU/Qxt/hX3fGNCNfIsXCW3JJ/+ArmX4pHxHCVaVHOsLOO/PFf+BPlf4Nn65wtviVvUVJVLTm3WcR/2RV2v45P62CiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/9T9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr5g/aZs3uPBF86gkxIJQP+uZDf0r6fry74q6MuseGrq3ZdweJlI9iMV3ZXi/qmNo4r+SUZfc0zizLCfWsJVw388ZR+9NH5Ingmkqe5tpbK4ls5gRJA7RNnrlDjP49fxqCv7chOM4qcHdPY/jacJRk4zVmuh7t+zT/wAlm0T/AK4X3/pO9fqUK/LX9mr/AJLNon/XC+/9J3r9Su1fzb4xf8jqn/17X/pUz+h/Cb/kT1P+vj/9JgFFFZ+p6rpujWUuo6tdQ2drCC0k07iONQPVmIFflMISnJRgrtn6bKSinKTskaFUNS1TTtHs5dR1W5itLWEFpJp3EcagerNgV8keP/2s9FsPM0/4e2n9rzjK/b7ndFZL7ouPMl9RwFPrXxf4u8b+LPHd59u8XanLqLA7o4W+S2iPX93AvyLg9CdzD1r9Q4e8K8yx1quO/c0/Ne8/+3en/b1vRn5rn/idl2CvSwX76flpBf8Ab3X/ALdT82j7S8fftZ6Hp4ksPh7a/wBsT8r/AGhcborJfdB/rJvUYAQ/3hXxh4v8deLfHl59u8WalNqDK26OJvktoT1/dQL8i47Mdz/7VcmSSck5PrSV+55BwfleTxTwlP3/AOZ6y+/p8kkfi2ecV5nmzaxdT3P5VpH7uv8A282+wpJJya9Q+Ddo118Q9NCjIiEspPpgBR/6FXl1fTn7NXh6S98QXOrsvyR7YEPuPmb9SB+Fc3HuOjhMhxM5fajyr1l7v5Nv5HTwPgpYrPMPBLRS5n6R9780l8z9F9PXbZxD/ZFXajiXZGq+gqSv5DP6sCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/9X9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigArP1S0W9s5IGGdykVoUHnigD8q/jd4Om8OeKJL+NCLe8b5iBwJB0P/Ah+oFeJ1+pnxf8Ah9beK9GnQx5YqcEdQexHvX5ma7ol94f1GXTb9Crxk7WIwHX1H9a/pTwv4shjcGsrxEv3tNWX96K2+cdn5Wfe388eJXC88Hi3mVCP7qo9f7snv8pbrzuux67+zV/yWfRP+uF9/wCk7V+nOqatpeiWEup6xdw2VpApeWe4kWKNFHOSzEAV+PngjxhqfgLxJb+KdGigmvbWKeOJbjcYgZ0MZZguC20HIGRk8ZHWo/FfjTxV45vhqPi3U59SlRt0SSHbbwn/AKZQLiNMdmwXx1Y1rxdwBXz3N4YmVRQpRgk+sm05PRbbNat/JmPC3HdHJMqlhoU3Oq5trpFK0Vq9+j0S9Wj7Q+IH7Wmj2Pmaf8O7P+1JuV/tC7DRWannmOPiWb1B+RD2Y18YeLPGvivx1e/b/Fmpzag6nMcbnbbxf9c4Vwi49cFvUmuXOe9JX1uQcIZXk8V9Tp+//M9Zff09Ekj5bPOKszzZ/wC11Pd/lWkfu6+rbYZooor6Y+dCiil96AJIIZbiaO3gXfJKwRF9WboP8fav0z+BPglfDfh63Dr+8K7mbHJZuSfxNfL3wN+GFzrGpRa5qMREa/6lWHQd2Puf0FfozpljHYWqQRjAUAV/NnilxZDMMTHLsJK9Om9X0cttPKKur9W30sz+h/DXheeAw7zDFK1SotF1Ud9fOTs7dkutzQooor8mP1EKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigD//1v38ooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigCKaJJkKOMg18yfFv4OWniO3e5to8SjLKyjBB9RX1BTJI0lUq4yDW+GxNXD1Y16EnGUXdNaNMxxGHpV6cqNaKlF6NPZn4z+IvCur+Gbp7fUIWCKSBIB8pHv6fyrm6/W3xd8NNH8RwuJIV3MD2r5E8X/s53VtI82k7kHJ2r0/Kv3ThzxepuKo5zCz/nitH6x6f9u39EfivEHhTNSdbKJ3X8knqvSXX529WfJlFeh6l8MPFmnMVa28wDuAR/jXPN4U8RIcNZSZ+lfpWG40yKvHmhi4fOSi/ulZn53iOD87oy5Z4WfyXN+Mbo52iustvBHie6YLHZOM+vH9K9D0D4HeJtVdTdAxqTyFHP5muXHcf5BhYtyxMZPtH3n+F197R04LgXPMTK0cO4rvL3fzs/uTPFI4pJpFiiUu7dFUZJ/Cvob4X/AAX1HXbuK/1eIrErBljI4+p9TX0P4D+AGmaRsnuowz8EkjJP419L6XotlpcKxW6BQoxwK/HOLPFHE5hCWFy6Lp03u/tNdtNIrva787aH63wv4a4bATWJx7VSotl9lP5/E/Wy8r6mX4Y8M2egWUdvAgXaAOBXWUUV+Tn6gFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAf/X/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAqKSGKUYdQalooAwrnw7pdz/rIFP4Vkv4G0Fzk26flXZ0UAcrB4O0WA5S3T8q3INNs7biKNR+FXqKAEAA4HFLRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAf/Q/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAP/2Q==" id="img_slides" onclick="Forward();" style="cursor: url(images/pointer.png), zoom-in;"/>
	</div>
  <div id="tabs-8"><iframe id="wooclap" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="<?php echo $wooclapaddress; ?>" width="100%" height="100%" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
  </div>
  <div id="tabs-9"><iframe id="extratab1" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="" width="100%" height="100%" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
  </div>
  <div id="tabs-10"><iframe id="extratab2" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="" width="100%" height="100%" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
  </div>
  <div id="tabs-11"><iframe id="extratab3" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="" width="100%" height="100%" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
  </div>
  <div id="tabs-12"><iframe id="extratab4" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="" width="100%" height="100%" style="position: absolute; width: 95%; height: 90%; bottom:0px; left:0px"></iframe>
  </div>

</div>
<div id="FS" onclick="GoFS();"  title="Toggle fullscreen mode"><img src="data:image/png;base64,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" style="width:50px; height: 50px"></div>
</form>

<div id="dialog-mail" title="Envoi d'un mail"> 
  <form>
    <fieldset>
	<label for="dialog_MailTo">Mail to : </label><input type="text" id="dialog_MailTo" /><br/>
	<input type="text" id="dialog_MailFrom" style="display:none;" readonly/>
	<label for="dialog_MailSubject">Subject : </label><input type="text" id="dialog_MailSubject" readonly/><br/>
	<label for="dialog_MailBody">Body : </label><br/>
	<div id="dialog_MailBody">
	</div>
    </fieldset>
  </form>
</div>


<script src="jquery/jquery.js"></script>
<script src="jquery/jquery-ui.min.js"></script>
<script src="qrcode.min.js"></script>
<script>
$("body").tooltip();
$( "#tabs" ).tabs();
$("#tabs-4").accordion({
      heightStyle: "content"
    });
//$( "#savenb" ).button();

new QRCode(document.getElementById("sharedqr"), {
	text: document.getElementById("sharedlink").href,
	width: 128,
	height: 128,
	colorDark : "#0000ff",
	colorLight : "#ffffff",
	correctLevel : QRCode.CorrectLevel.L
});

document.getElementById("sharedqr").title="";
document.getElementById("sharedqr").onclick=function () {document.getElementById("sharedqr").firstChild.toBlob(function (blob) {var url = URL.createObjectURL(blob);window.open(url);});};

new QRCode(document.getElementById("remoteqr"), {
	text: document.getElementById("remotelink").href,
	width: 128,
	height: 128,
	colorDark : "#0000ff",
	colorLight : "#ffffff",
	correctLevel : QRCode.CorrectLevel.L
});

document.getElementById("remoteqr").title="";
document.getElementById("remoteqr").onclick=function () {document.getElementById("remoteqr").firstChild.toBlob(function (blob) {var url = URL.createObjectURL(blob);window.open(url);});};


$('#bar').on('keydown keyup keypress', function(e) {
  //console.log(e.keyCode);
  e.preventDefault();
});


function fixTitle() {
  for(var f in {"partialtitre":1,"studentfeedback":1}) {
  var e=document.getElementById(f);
  e.style.top=(window.innerHeight-e.style.height-200-10)+'px';
  }
}

function MinTitlePos() {
  for(var f in {"partialtitre":1,"studentfeedback":1}) {
  var e=document.getElementById(f);
  var postop=1*(e.style.top.replace('px',''));
  if (postop>window.innerHeight-50) {
    e.style.top=(window.innerHeight-50)+'px';
  }
  }
}

/**
 * jQuery Draggable Touch v0.6
 * Jonatan Heyman | http://heyman.info 
 *
 * Make HTML elements draggable by using uses touch events.
 * The plugin also has a fallback that uses mouse events, 
 * in case the device doesn't support touch events.
 * 
 * Licenced under THE BEER-WARE LICENSE (Revision 42):
 * Jonatan Heyman (http://heyman.info) wrote this file. As long as you retain this 
 * notice you can do whatever you want with this stuff. If we meet some day, and 
 * you think this stuff is worth it, you can buy me a beer in return.
 */
;(function($){
    $.fn.draggableTouch = function(actionOrSettings) {
        // check if the device has touch support, and if not, fallback to use mouse
        // draggableMouse which uses mouse events
        if (window.ontouchstart === undefined) {
            return this.draggableMouse(actionOrSettings);
        }

        if (typeof(actionOrSettings) == "string") {
            // check if we shall make it not draggable
            if (actionOrSettings == "disable") {
                this.unbind("touchstart.draggableTouch");
                this.unbind("touchmove.draggableTouch");
                this.unbind("touchend.draggableTouch");
                this.unbind("touchcancel.draggableTouch");
                
                this.trigger("dragdisabled");

                return this;
            }
        } else {
            var useTransform = actionOrSettings && actionOrSettings.useTransform;
        }
        
        this.each(function() {
            var element = $(this);
            var offset = null;
            var draggingTouchId = null;
            var end = function(e) {
                e.preventDefault();
                var orig = e.originalEvent;
                for (var i=0; i<orig.changedTouches.length; i++) {
                    var touch = orig.changedTouches[i];
                    // the only touchend/touchcancel event we care about is the touch
                    // that started the dragging
                    if (touch.identifier != draggingTouchId) {
                        continue;
                    }
                    element.trigger("dragend", {
                        top: orig.changedTouches[0].pageY - offset.y,
                        left: orig.changedTouches[0].pageX - offset.x
                    });
                    draggingTouchId = null;
                }
            };
            
            element.bind("touchstart.draggableTouch", function(e) {
                e.preventDefault();
                var orig = e.originalEvent;
                // if this element is already being dragged, we can early exit, otherwise
                // we need to store which touch started dragging the element
                if (draggingTouchId) {
                    return;
                } else {
                    draggingTouchId = orig.changedTouches[0].identifier;
                }
                var pos = $(this).position();
                offset = {
                    x: orig.changedTouches[0].pageX - pos.left,
                    y: orig.changedTouches[0].pageY - pos.top
                };
                element.trigger("dragstart", pos);
            });
            element.bind("touchmove.draggableTouch", function(e) {
                e.preventDefault();
                var orig = e.originalEvent;
                
                for (var i=0; i<orig.changedTouches.length; i++) {
                    var touch = orig.changedTouches[i];
                    // the only touchend/touchcancel event we care about is the touch
                    // that started the dragging
                    if (touch.identifier != draggingTouchId) {
                        continue;
                    }
                    if (useTransform) {
                        $(this).css({
                            "transform": "translate3d(" + (touch.pageX - offset.x) + "px, " + (touch.pageY - offset.y) + "px, 0px)",
                        });
                    } else {
                        $(this).css({
                            top: touch.pageY - offset.y,
                            left: touch.pageX - offset.x
                        });
                    }
                }
            });
            element.bind("touchend.draggableTouch touchcancel.draggableTouch", end);
        });
        return this;
    };
    
    /**
     * Draggable fallback for when touch is not available
     */
    $.fn.draggableMouse = function (actionOrSettings) {
        if (typeof(actionOrSettings) == "string") {
            // check if we shall make it not draggable
            if (actionOrSettings == "disable") {
                this.unbind("mousedown.draggableTouch");
                this.unbind("mouseup.draggableTouch");
                $(document).unbind("mousemove.draggableTouch");

                this.trigger("dragdisabled");

                return this;
            }
        } else {
            var useTransform = actionOrSettings && actionOrSettings.useTransform;
        }
        
        this.each(function() {
            var element = $(this);
            var offset = null;
            
            var move = function(e) {
                if (useTransform) {
                    element.css({
                        "transform": "translate3d(" + (e.pageX - offset.x) + "px, " + (e.pageY - offset.y) + "px, 0px)",
                    });
                } else {
                    element.css({
                        top: e.pageY - offset.y,
                        left: e.pageX - offset.x,
                    });
                }
            };
            var up = function(e) {
                element.unbind("mouseup.draggableTouch", up);
                $(document).unbind("mousemove.draggableTouch", move);
                element.trigger("dragend", {
                    top: e.pageY - offset.y,
                    left: e.pageX - offset.x
                });
            };
            element.bind("mousedown.draggableTouch", function(e) {
                var pos = element.position();
                offset = {
                    x: e.pageX - pos.left,
                    y: e.pageY - pos.top
                };
                $(document).bind("mousemove.draggableTouch", move);
                element.bind("mouseup.draggableTouch", up);
                element.trigger("dragstart", pos);
                e.preventDefault();
            });
        });
        return this;
    };
})(jQuery);


$( function() {
  fixTitle();
  //$( "#titre" ).draggable();
  $("#partialtitre").draggableTouch();
  $("#studentfeedback").draggableTouch();
} );

function sendclick() {
  var tosend = document.getElementById('question').value.replaceAll('\n','').trim();
  //alert("Will send : ("+tosend+")");
  if (tosend != "") saveTextQ(tosend); 
  document.getElementById('question').value='';
}

$("#question").keyup(function(event) {
    if (event.keyCode === 13) {
        sendclick();
    }
});

window.onbeforeunload = function(event) {
  event.returnValue = "Quit";
};

var sizepincodediv=1;




  $( function() {
    var dialog, form;
 
    function EnvoiMail() {
		var XHR = new XMLHttpRequest();
  		var FD  = new FormData();
    	FD.append('from', document.getElementById('dialog_MailFrom').value);
    	FD.append('to', document.getElementById('dialog_MailTo').value.replaceAll(' - ',','));
    	FD.append('subject', document.getElementById('dialog_MailSubject').value);
		//FD.append('body', document.getElementById('dialog_MailBody').innerHTML);
		FD.append('body', document.getElementById('dialog_MailBody').innerHTML);
		//FD.append('body', quillmail.getText());
  		XHR.addEventListener('load', function(event) {
    		alert('Mail sent.');
  		});
		XHR.addEventListener('error', function(event) {
    		alert('Error, the mail cannot be sent.');
  		});
		XHR.open('POST', 'sendamail.php');
  		XHR.send(FD);
 	    dialog.dialog( "close" );		
    }
 
    dialog = $( "#dialog-mail" ).dialog({
      autoOpen: false,
      height: 500,
      width: 800,
      modal: true,
      buttons: {
        "Send": EnvoiMail,
        Cancel: function() {
          dialog.dialog( "close" );
        }
      },
      close: function() {
      }
    });
 
    form = dialog.find( "form" ).on( "submit", function( event ) {
      event.preventDefault();
      EnvoiMail();
    });
 
  } );





</script>
</body>
</html>
