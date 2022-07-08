<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
?>
<?php

if (isset($_GET["SYNC"])) {
  $synchronize = true;
} else {
  $synchronize = false;
}

if (isset($_GET["ROOMID"])) {
  $classroomid = $_GET["ROOMID"];
  $synchronize = false;
} else {
  $classroomid = '';
  $synchronize = true;
}


$wooclapkey="";
if (isset($_GET["WOOCLAPKEY"])) {
  $wooclapaddress = "https://www.wooclap.com/".$_GET["WOOCLAPKEY"];
  $wooclapkey=$_GET["WOOCLAPKEY"];
} else {
  $wooclapaddress = "https://app.wooclap.com";
}

$Tcolors=array("Red","Blue","Orange","Purple","Black","White","Green");
$Tobjects=array("Alpaca","Alligator","Dragoon", "Elephant","Lion", "Panther", "Tiger","Zebra");
$randomname=$Tcolors[mt_rand(0,count($Tcolors)-1)]." ".$Tobjects[mt_rand(0,count($Tobjects)-1)];

$T_lang=array();
$fd = fopen("supportedlanguages.csv","r");
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
	<title>Automatic translation for teaching purposes</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no" />
	<link href="jquery/jquery-ui.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="favicon.png" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<style>

body {
  top:0;
  left: 0;
}
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

#logonext {
  position: fixed;
  top:5px;
  right: 5px;
  z-index: 1000;
  display: none;
}

/*
#titre {
  transition: 1s ease;
  border-radius: 25px;
  position: fixed;
  bottom: 10px;
  left: 20px;
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
*/

#titre {
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


#soustitre {
  transition: 1s ease;
  position: fixed;
  bottom: 0px;
  left: 0px;
  width: 100%;
  height: 10px;
  z-index: 20000;
}

#titre h1 {
  text-align: center;
  font-size: 1.5em;
  font-weight: bold;
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
  list-style: none;
  margin: 0px;
  margin-top: 64px;
  padding: 0px;
  transition: 1s ease;
  transform-origin: top left;
  z-index: 11000;
  transform: scaleX(0);
  width: 150px;
}

#bar li {
  z-index: 11000;
  padding: 10px 5px 10px 5px;
  background-color: rgb(247,247,247);
  color: black;
  border: 1px solid;
  border-color: black;
  border-radius: 3px;
  font-family: arial, helvetica, sans-serif;
}

#bar li:hover {
  background-color: rgb(236,236,236);
}

#bar li.selected {
  background-color: rgb(57,199,204);
  color: white;
}


#FS {
  position: fixed;
  top:0px;
  right: 0px;
  font-size: 12px;
  z-index:15000;
}

#tabs-7 {
}

#img_slides {
  transform-origin: top left;
}

#showmenu {
  transition: 1s ease;
  //border-radius: 5px;
  position: fixed;
  top: 0px;
  left: 0px;
  //background-color: rgb(150,150,255);
  color: white;
  text-align: center;
  vertical-align: middle;
  //padding: 5px;
  font-size: 24px;
  z-index: 12000;
  display: block;
  //border: 1px solid;
  //border-color: blue;
}

#showmenu:hover {
  //background-color: rgb(200,200,255);
  transform: scale(0.9);
}

#iconeMenu {
  width: 36px;
  height: 36px;
}

#micon {
  position: fixed;
  //top:5px;
  top:0px;
  right: 60px;
  font-size: 16px;
  z-index: 15000;
  font-family: helvetica, arial, sans-serif;
  background-color: red;
  border-radius: 10px;
  width: 50px;
  height: 50px;
  margin: auto;
  color: white;
  text-align: center;
  vertical-align: middle;
}

#repeat {
  position: fixed;
  //top:5px;
  top:0px;
  right: 120px;
  font-size: 16px;
  z-index: 15000;
  font-family: helvetica, arial, sans-serif;
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

@media screen and (max-width: 500px) {
  #showmenu {
        display: block;
      }
  }
  
  #tabs-0, #tabs-1, #tabs-2, #tabs-3, #tabs-4, #tabs-6, #tabs-7, #tabs-8,#tabs-8,#tabs-9,#tabs-10,#tabs-11,#tabs-12,#tabs-13,#img_slides {
    z-index: 1000;
    position: absolute;
    top: 0px;
    left: 0px;
    width: 100%;
    height: 100%;
    transition: 1s ease;
    transform: translateX(0px);
  }

  #ltabs-9,#ltabs-10,#ltabs-11,#ltabs-12 {
  display:none;
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

      //input[type="checkbox"] {
      //  visibility: hidden;
      //}
      .checkbox-example {
        width: 45px;
        height: 15px;
        background: #aaa;
        margin: 20px 10px;
        position: relative;
        border-radius: 5px;
      }
      .checkbox-example label {
        display: inline;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        transition: all 0.5s ease;
        cursor: pointer;
        position: absolute;
        top: -2px;
        left: -3px;
        background: red;
      }
      .checkbox-example input[type="checkbox"]:checked + label {
background-color: green;
        left: 27px;
      }

.wordcloud {
  display: none;
}


@keyframes hithere {
  from, to { transform: translateX(0); }
  50% { transform: translateX(10px); }
}

#lilanguage {
  animation: hithere 0.5s infinite;
}

#lisynthese {
  animation: none;
}

#arrowlanguage, #arrowsynthesis {
  transition: 1s ease;
  display: inline;
  font-family: helvetica, arial, sans-serif;
  color: red;
}

#arrowsynthesis {
  visibility: hidden;
}

	</style>


<script type="text/javascript">

<?php
echo "var supportedlanguages=JSON.parse('".json_encode($T_lang)."');";
?>
var visioactive=false;
var AllOutputVoices=[];
var TLangInput=[];
var builtin_voice;
var builtin_language;
var maxvoices=0;

function PopulateVoicesOut() {
  var select = document.getElementById('VoiceOutput');
  AllOutputVoices=[];
  while (select.firstChild) {
    select.removeChild(select.firstChild);
  }
  var optgroup=document.createElement("optgroup");
  optgroup.label="Voice translation";
  var voices = window.speechSynthesis.getVoices();
  // Voices in english first
  for(var i=0; i<voices.length; i++) {
    if (voices[i].lang.substr(0,2).toLowerCase() == 'en') {//(voices[i].localService) {
      var opt=document.createElement("option");
      opt.value=AllOutputVoices.length;
      if (voices[i].lang.substr(0,2).toLowerCase() == navigator.language) opt.selected=true;
      voices[i].textonly=false;
      AllOutputVoices.push(voices[i]);
      opt.innerHTML=voices[i].lang+" "+voices[i].name+((voices[i].localService)?"":" [external service]");
      optgroup.appendChild(opt);
    }
  }

  for(var i=0; i<voices.length; i++) {
    if (voices[i].lang.substr(0,2).toLowerCase() != 'en') {//(voices[i].localService) {
      var opt=document.createElement("option");
      opt.value=AllOutputVoices.length;
      if (voices[i].lang.substr(0,2).toLowerCase() == navigator.language) opt.selected=true;
      voices[i].textonly=false;
      AllOutputVoices.push(voices[i]);
      opt.innerHTML=voices[i].lang+" "+voices[i].name+((voices[i].localService)?"":" [external service]");
      optgroup.appendChild(opt);
    }
  }
  maxvoices=voices.length;
  select.appendChild(optgroup);
  var optgroup2=document.createElement("optgroup");
  optgroup2.label="Text translation only";
    for(var i=0; i<supportedlanguages.length; i++) {
      var lang=supportedlanguages[i].code; //.substr(0,5);
        var opt=document.createElement("option");
        opt.value=maxvoices+i;
        AllOutputVoices.push({"lang":lang,"textonly":true});

        opt.innerHTML=lang+" "+supportedlanguages[i].name_fr+" "+supportedlanguages[i].name_vo;
        optgroup2.appendChild(opt);
      }
  select.appendChild(optgroup2);
}

function PopulateVoicesIn() {
  var select = document.getElementById('VoiceInput');
  var select2 = document.getElementById('classroomlanguage');
  var voices = window.speechSynthesis.getVoices();
  //alert(voices[0].name);
  TLangInput=[];
  for(var i=0; i<voices.length; i++) {
    if (voices[i].localService) {
      var lang=voices[i].lang; //.substr(0,5);
      if (TLangInput.indexOf(lang)<0) {
        var opt=document.createElement("option");
        opt.value=TLangInput.length;
        TLangInput.push(lang);
        opt.innerHTML=lang;
        select.appendChild(opt);
        var opt2=document.createElement("option");
        opt2.value=TLangInput.length;
        opt2.innerHTML=lang;
        select2.appendChild(opt2);
      }
    }
  }
}

function PopulateVoicesIn() {
  if (TLangInput.length == 0) {
  var select = document.getElementById('VoiceInput');
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
        var opt2=document.createElement("option");
        opt2.value=TLangInput.length;
        opt2.innerHTML=lang;
        select2.appendChild(opt2);
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
  if (! builtin_voice.textonly) {
 var discours = new SpeechSynthesisUtterance(txt);
 discours.voice = builtin_voice;
 discours.lang = builtin_voice.lang;
 window.speechSynthesis.speak(discours);
  }
}




function ParleThen(txt,f) {
  document.getElementById('titre').innerHTML=txt;
  if (! builtin_voice.textonly) {
    var discours = new SpeechSynthesisUtterance(txt);
    discours.voice = builtin_voice;
    discours.lang = builtin_voice.lang.replace('_','-')
    discours.onend=f;
    window.speechSynthesis.speak(discours);
  } else setTimeout(f,10000);
}

var inputnumber=-1;


function Translate(ch, l, linput, lineid) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + document.getElementById('classroomid').value +"&lineid=" + lineid, true);

  xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      // console.log(this.responseText);
      var response = JSON.parse(this.responseText);
      if ((! response["error"]) && response["data"]["translations"]) {
        ToSay[''+lineid]=(response["data"]["translations"][0]["translatedText"]);
//        Ecrire(lineid+"  :  "+response["data"]["translations"][0]["translatedText"]);
        Ecrire(response["data"]["translations"][0]["translatedText"]);
      } else {
        Ecrire("---- Translation ERROR ----")
      }

    }
  };
  xhr.onloadend = CompleteTranslation;
  xhr.send();
}

function Translate2(ch, l, linput, lineid) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput + "&ROOMID=" + document.getElementById('classroomid').value +"&lineid=" + lineid, true);
  xhr.onloadend = CompleteTranslation2;
  xhr.send();
}


//var SpeechRecognition = SpeechRecognition || webkitSpeechRecognition;
//var builtin_recognition = new SpeechRecognition();
//builtin_recognition.continuous = false;
//var builtin_recognitionon = false;
/*
function StartSpeechRecognition(callback) {
  builtin_recognition.lang = builtin_language;
  builtin_recognition.onresult = function(event) {
    if (builtin_recognitionon) {
      for (i = event.resultIndex; i < event.results.length; i++) {
        callback(event.results[i][0].transcript);
      }
      builtin_recognition.stop();
    }
  };
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
*/

function InitVoices() {
  PopulateVoicesOut();
  setLanguageOutput(document.getElementById("VoiceOutput").value);
}

function EcrireNB(ch) {
  if (document.getElementById('notebook').innerHTML == "")
    document.getElementById('notebook').innerHTML=ch;
  else document.getElementById('notebook').innerHTML=document.getElementById('notebook').innerHTML+"<br/>"+ch;
}



function Ecrire(ch) {
  if (document.getElementById('output').innerHTML == "")
    document.getElementById('output').innerHTML=ch;
  else document.getElementById('output').innerHTML=document.getElementById('output').innerHTML+"<br/>"+ch;
  //document.getElementById('titre').innerHTML=ch;
  //document.getElementById('subtitles').innerHTML=ch;

  bufferWC[placebufferWC]=ch;
  placebufferWC = (placebufferWC+1)%sizebufferWC;
  buffer_has_changed=true;
}

function hide(e) {
  document.getElementById(e).style.display='none';
}

function show(e) {
  document.getElementById(e).style.display='block';
}

var ToTranslate=[];
var ToTranslate2=[];
var completespeechactive=false;

function CompleteTranslation() {
  if (ToTranslate.length>0) {
    var L=ToTranslate[0];
    ToTranslate.splice(0,1);
    if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2)) {
      // Ecrire("Push : "+L[2]);
      Translate(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2),L[0]);
    } else {
        ToSay[''+L[0]]=L[2];//,CompleteTranslation);
        Ecrire(""+L[2]);
        CompleteTranslation();
    }
  } else {if (! completespeechactive) {completespeechactive=true; CompleteSpeech();}}
}

function CompleteTranslation2() {
  if (ToTranslate2.length>0) {
    var L=ToTranslate2[0];
    ToTranslate2.splice(0,1);
    if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2)) {
      // Ecrire("Push : "+L[2]);
      Translate2(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2),L[0]);
    } else CompleteTranslation2();
  } else setTimeout(CompleteTranslation,10);
}

var ToSay=[];
var currentdiscours=-1;
var gotolive=false;

function CompleteSpeech() {
  var nextdiscours = currentdiscours;
  for(var i=1; i<1000; i++) {
    if (ToSay[''+(currentdiscours+i)]) {
      nextdiscours = currentdiscours+i;
      break;
    }
  }
  if (gotolive) {
    //console.log('Restart Speech');
    //console.log('Discours : '+currentdiscours+'   '+nextdiscours)
    for(var i=1000; i>0; i--) {
    if (ToSay[''+(currentdiscours+i)]) {
      nextdiscours = currentdiscours+i;
      break;
    }
    //console.log('Discours next : '+currentdiscours+'   '+nextdiscours)
    gotolive=false;
  }
  }
    //Ecrire(currentdiscours+'   '+nextdiscours);
    if (nextdiscours > currentdiscours) {
      var L=ToSay[''+nextdiscours];
      L=L.replace("&nbsp;"," ");
      ParleThen(L,CompleteSpeech);
      currentdiscours=nextdiscours;
    } else completespeechactive=false;
  
}

function readText() {
  var synthesison=document.getElementById('synthesison').checked;
  if (synthesison) {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "readtext.php");
  var f=new FormData();
  f.append('lineid',inputnumber);
  f.append('ROOMID',document.getElementById('classroomid').value);
	request.send(f);
	request.onload = function(event) {
    var T=request.responseText.split('\n');
    //alert(request.responseText);
    var maxi=-1;
    for(var i=0; i<T.length; i++) {
      var L=T[i].split(';');
      if (L.length>2) {
      if (1*L[0]>maxi) maxi=1*L[0];
      EcrireNB(L[2]);
      ToTranslate.push(L);
      ToTranslate2.push(L);
/*      if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2))
        Translate(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2),L[0]);
      else {
        Parle(L[2]);
        Ecrire(""+L[2]);
      }*/
    }
    }
    //alert(maxi);
    if (maxi>inputnumber) inputnumber=maxi;

    CompleteTranslation2();
  };
}
}
}

var currentrandom = 0;

function readImage() {
  if (document.getElementById('classroomid').value != "") {
	var request = new XMLHttpRequest();
	request.open("POST", "readimage.php");
  var f=new FormData();
  f.append('random',currentrandom);
  f.append('ROOMID',document.getElementById('classroomid').value);
	request.send(f);
	request.onload = function(event) {
    if (request.responseText != "") {
      var Trep=request.responseText.split('|||');
      currentrandom=Trep[0];
      document.getElementById('img_slides').onload=resizeSlides;
      document.getElementById('img_slides').src=Trep[1];
    }

  };
}
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
    } else {
      alert("ERROR : unknown pin code");
    }
  };
}




function Lancer() {
  setInterval(readText,500);
  setInterval(readTextQ,500);
  setInterval(readImage,500);
  setLanguageInput(0);
  resizeSlides();
}

var firsttime = true;

function Init() {
  /*
  document.getElementById('question').addEventListener('keyup', function (event) {
    if (event.code === 'Enter') {
        saveTextQ(document.getElementById('question').value);
      event.preventDefault();
      event.stopPropagation();
    }
  },false);
  */
  resizeSlides();
  activetab('tabs-0');

<?php
if ($synchronize) {
  echo "Synchronize(prompt('Enter the session pincode'));\n";
}
?>
  setTimeout(function() {document.getElementById('wooclap').src='https://www.wooclap.com/';},1000);
  setTimeout(function() {saveTextQ("[newstudentarrived]");},1000);
  document.getElementById('iconeMenu').src=MenuOnIMG;
}

function ClearCR() {
  document.getElementById('classroom').innerHTML='';
}

function rgb(T) {
  return "rgb("+T[0]+","+T[1]+","+T[2]+")";
}
var ColorsForCR=[];
ColorsForCR["----"]=[[255,0,0],[255,255,255],5];
ColorsForCR["Teacher"]=[[0,0,255],[255,255,255],5];

function EcrireCR(who,ch) {
  if (! ColorsForCR[who]) {
    var r=Math.floor(Math.random()*200+55);
    var g=Math.floor(Math.random()*200+55);
    var b=Math.floor(Math.random()*200+55);
    var bg=[r,g,b];
    var fg=[0,0,0];
    ColorsForCR[who]=[bg,fg,20];
  }
  if (document.getElementById('classroom').innerHTML == "")
    document.getElementById('classroom').innerHTML='<span style="color:'+rgb(ColorsForCR[who][1])+'; background-color:'+rgb(ColorsForCR[who][0])+'; margin-left: '+ColorsForCR[who][2]+'px; ">'+(who+' : '+ch)+'<span>';
  else document.getElementById('classroom').innerHTML=document.getElementById('classroom').innerHTML+"<br/><br/>"+'<span style="color:'+rgb(ColorsForCR[who][1])+'; background-color:'+rgb(ColorsForCR[who][0])+'; margin-left: '+ColorsForCR[who][2]+'px;">'+(who+' : '+ch)+'<span>';
}

document.addEventListener('submit', event => {
  event.preventDefault();
  // actual logic, e.g. validate the form
  //alert('Form submission cancelled.');
});

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
      if (L[0] == 'Teacher' && L[2].indexOf("[extratab") >= 0) {
        // Add an extratab
        var resource = L[2].replace("[extratab","").replace("]","").split("|");
        var tabnumber=resource[0];
        var url=resource[1];
        showET(url,tabnumber);
      } else {
        if (L[0] != 'ROBOT' && L[2].indexOf("[newstudent")< 0) {
          console.log('Debug -- '+T[i]+'     '+L[2]+'   '+builtin_voice.lang.substr(0,2));
          if (builtin_voice.lang.substr(0,2) != L[1].substr(0,2))
            TranslateQ(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2), L[0]);
          else {
            EcrireCR(L[0],L[2]);
          }
        }
      }
    }
    inputnumberQ=T.length;
  };
}
}


function saveTextQ(ch) {
	var request = new XMLHttpRequest();
	request.open("POST", "savetextQ.php");
  var f=new FormData();
  f.append('lineid','Student '+document.getElementById('StudentName').value);
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
        EcrireCR(name,response["data"]["translations"][0]["translatedText"]);
      } else {
        EcrireCR("----","---- Translation ERROR ----")
      }

    }
  };
  xhr.send();
}

function GoFS() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
    document.getElementById('bar').style.transform="scaleY(0.5)";
    scalemenu=1;
    ShowMenu();
    //document.getElementById('bar').style.bottom="10px";
    //resizeSlides();
  } else {
    document.exitFullscreen();
    document.getElementById('bar').style.transform="scaleY(1)";
      scalemenu=1;
    ShowMenu();  
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
  }
  //slide.style.top=(48*scalemenu)+'px';

  //var TTabs=['tabs-0','tabs-1','tabs-2','tabs-3','tabs-4','tabs-6','tabs-7'];
  //TTabs.map(function (name) {document.getElementById(name).style.top=(48*scalemenu)+'px';})
  // For wooclap and others
  ["wooclap","extratab1","extratab2","extratab3","extratab4"].map(resizeWindow)
  MinTitlePos();
}


window.onresize=resizeSlides;

function showET(url,num) {
  //alert('url='+url+'    num='+num+'     tabs='+'ltabs-'+(8+1*num))
    if (url!="") {
      show('ltabs-'+(8+1*num));
      if (document.getElementById('extratab'+num).src!=url) document.getElementById('extratab'+num).src=url;
    } else {
    hide('ltabs-'+(8+1*num));
    }
}

var Tvoices;
function loadVoicesWhenAvailable() {
         Tvoices = window.speechSynthesis.getVoices();

         if (Tvoices.length !== 0) {
                LoadVoices();
            }
            else {
                setTimeout(function () { loadVoicesWhenAvailable(); }, 10)
            }
    }

function LoadVoices() {
  InitVoices();
  Lancer();
}

var scalemenu=1;
function ShowMenu() {
  scalemenu=1-scalemenu;
  if (scalemenu<0.05) {
    document.getElementById('iconeMenu').src=MenuOnIMG;
  } else {
    document.getElementById('iconeMenu').src=MenuOffIMG;
  }
  document.getElementById('bar').style.transform="scaleX("+scalemenu+")";
  var TTabs=['tabs-0','tabs-1','tabs-2','tabs-3','tabs-7','tabs-8','tabs-9','tabs-10','tabs-11','tabs-12','tabs-13'];
  for(var i=0; i<TTabs.length; i++) {
    document.getElementById(TTabs[i]).style.transform="translateX("+(160*scalemenu)+"px)";
  }

  resizeSlides();
  
}
var atab = 'tabs-0';
function activetab(tab)  {
  var TTabs=['tabs-0','tabs-1','tabs-2','tabs-3','tabs-7','tabs-8','tabs-9','tabs-10','tabs-11','tabs-12','tabs-13'];
  for(var i=0; i<TTabs.length; i++) {
    document.getElementById(TTabs[i]).style.display="none";
    document.getElementById("l"+TTabs[i]).style.color="black";
    document.getElementById("l"+TTabs[i]).style.backgroundColor="rgb(247,247,247)";   
  }
  document.getElementById(tab).style.display='block';
    document.getElementById("l"+tab).style.color="white";
    document.getElementById("l"+tab).style.backgroundColor="rgb(57,199,204)";
  ShowMenu();
}


function ChangeColor(boxname,color) {
  var [bgcolor,bordercolor,textcolor]=color.split('|');
  var e=document.getElementById(boxname);
  if (textcolor) e.style.color = 'rgb('+textcolor+')'; else e.style.color = 'rgb(255,255,255)';
  e.style.backgroundColor = 'rgba('+bgcolor+',0.5)';
  e.style.boxShadow = '2px 2px 5px rgb('+bgcolor+')';
  e.style.textShadow = '2px 0 0 rgb('+bordercolor+'), -2px 0 0 rgb('+bordercolor+'), 0 2px 0 rgb('+bordercolor+'), 0 -2px 0 rgb('+bordercolor+'), 1px 1px rgb('+bordercolor+'), -1px -1px 0 rgb('+bordercolor+'), 1px -1px 0 rgb('+bordercolor+'), -1px 1px 0 rgb('+bordercolor+')';
}

var MenuOnIMG="data:image/png;base64,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";
var MenuOffIMG="data:image/png;base64,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";

</script>



</head>
<body onload="Init(); /*InitVoices(); setTimeout(InitVoices,1000); setTimeout(Lancer,3000); */ setTimeout(loadVoicesWhenAvailable,100); /*loadVoicesWhenAvailable();*/">
<div id="logonext"><img src="images/logo-next-omist.png" alt="NExT" style="width: 109px; height: 50px;"/></div>
<div id="showmenu" onclick="ShowMenu();" title="Menu"><img src="" alt="☰" id="iconeMenu"/></div>
<div id="micon" onclick="gotolive=true; CompleteSpeech();"><i class='material-icons'>sensors</i><span style="font-size: 10px;color: white;"><br/>LIVE</span></div>
<div id="repeat" onclick="Parle(document.getElementById('titre').innerHTML);" title="Repeat the last sentence"><i class='material-icons'>replay</i><span style="font-size: 10px;color: white;"><br/>REPEAT</span></div>

<!--
<div id="titre" onmouseover="document.getElementById('soustitre').style.transform='translateY(0px)'; document.getElementById('titre').style.transform='translateY(0px)';"><h1>Automatic translation for teaching purposes: Student</h1></div>
<div id="soustitre" onmouseover="this.style.transform='translateY(-64px)'; document.getElementById('titre').style.transform='translateY(-64px)';"></div>-->
<div id="titre"><h1>Automatic translation for teaching purposes: Student</h1></div>
<div id="soustitre"></div>

<form id="myform" onsubmit="return false;">

<div id="tabs">
	<ul id="bar">
		<li id="ltabs-0" onclick="activetab('tabs-0')">Informations</li>
		<li id="ltabs-1" onclick="activetab('tabs-1')">Classroom</li>
		<li id="ltabs-2" onclick="activetab('tabs-2')">Notebook</li>
		<li id="ltabs-3" onclick="activetab('tabs-3')">Output</li>
<!--    <li><a href="#tabs-6" onclick="if (!visioactive) document.getElementById('visio').src='https://meet.jit.si/Translator_'+document.getElementById('classroomid').value; visioactive=true;">Visio</a></li>-->
<!--		<li id="ltabs-4" onclick="activetab('tabs-4')">Scan a QRCode</li>-->
    <li id="ltabs-7" onclick="activetab('tabs-7')">Slides</li>
    <li id="ltabs-8" onclick="activetab('tabs-8')">WOOCLAP</li>
    <li id="ltabs-9" onclick="activetab('tabs-9');">1</li>
		<li id="ltabs-10" onclick="activetab('tabs-10');">2</li>
		<li id="ltabs-11" onclick="activetab('tabs-11');">3</li>
		<li id="ltabs-12" onclick="activetab('tabs-12');">4</li>
		<li id="ltabs-13" onclick="activetab('tabs-13');" class="wordcloud">Word Cloud</li>

	</ul>
	<div id="tabs-0">
  <ul style="list-style-type: none;">
<li style="display:block"><label for="classroomid">Classroom identifier :</label><input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" /></li>
<li style="display:none"><label for="classroomlanguage">Classroom language :</label>
  <select id="classroomlanguage" onchange="setLanguageInput(this.value);">
  </select>
</li>
<li id="lilanguage"><label for="VoiceOutput">Student language :</label>
  <select id="VoiceOutput" onchange="setLanguageOutput(this.value);"  onclick="if (firsttime) {document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='visible'; document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='hithere 0.5s infinite'; }">
  </select><div id="arrowlanguage">&nbsp;Choose your language first</div>
</li>
<li><label for="StudentName">Student surname :</label>
  <input type="text" id="StudentName" onchange="if (this.value == 'Teacher') this.value='Student';" value="<?php echo $randomname; ?>"/>
</li>
<li id="lisynthese"><label for="synthesison">Speech synthesis active ? </label>
<!--<div class="checkbox-example">
        <input type="checkbox" value="1" id="synthesison" onclick="resizeSlides();"/>
        <label for="synthesison"></label>
      </div>-->
<input type="checkbox" id="synthesison" onclick="resizeSlides(); document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='hidden';  document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='none'; firsttime=false;"/><div id="arrowsynthesis">&nbsp; Then launch the speech synthesis</div>
</li>
<li>    <input type="button" value="Synchronize" onclick="Synchronize(prompt('Enter the session pincode'));"/>
</li>
<li>Subtitles font size ? <select onchange="document.getElementById('titre').style.fontSize = this.value;">
<option value="12px">Small</option>
<option value="18px" selected>Normal</option>
<option value="24px" >Big</option>
<option value="32px" >Huge</option>
<option value="48px" >Enormous</option>
  </select>&nbsp; Color : <select onchange="ChangeColor('titre',this.value);">
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
  <li><b>Extra tabs</b></li>
  <li title="Sometimes, for security issues, you must be logged to WOOCLAP by an external application. Click here to fix this issue.">External login to WOOCLAP : <button onclick="window.open('https://app.wooclap.com/auth/login?lang=en&redirectTo=/home')">Login to wooclap first</button></li>
  <li>WOOCLAP Key?  <input type="text" value="<?php echo $wooclapkey; ?>" id="wooclapkey" onchange="document.getElementById('wooclap').src='https://www.wooclap.com/'+document.getElementById('wooclapkey').value;"/></li>

  </ul>
    <br/>
  <br/>
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
    <div style="height: 50px;"></div>
    <div id="classroom" style="border: solid 1px;"></div>

    <!-- <input type="text" id="questionold" placeholder="Your question here"/>-->
    <textarea id="question" placeholder="Your question here" rows="2" cols="50"></textarea><br/>
    <input type="button" value="send" class="button-3" role="button" id="questionsend" onclick="sendclick();"/>
	</div>

	<div id="tabs-2">
    <!--<input type="button" value="Restart" onclick="inputnumber=-1;"/>-->
    <div id="notebook" style="border: solid 1px;" contenteditable="true"></div>

	</div>
	<div id="tabs-3">
    <div id="output">
    </div>
  </div>
	<div id="tabs-13" class="wordcloud">
    <div id="vis"></div>
    <div id="configwc" style="display: none">
    <p><textarea id="text">
Ceci est un test...
    </textarea>
    <button id="go" type="submit">Go!</button>
    <div style="float: right; text-align: right">
  <p><label for="max">Number of words:</label> <input type="number" value="250" min="1" id="max">
  <p><label for="per-line"><input type="checkbox" id="per-line"> One word per line</label>
  <!--<p><label for="colours">Colours:</label> <a href="#" id="random-palette">get random palette</a>-->
  <p><label>Download:</label>
    <button id="download-svg">SVG</button><!-- |
    <a id="download-png" href="#">PNG</a>-->
</div>

<div style="float: left">
  <p><label>Spiral:</label>
    <label for="archimedean"><input type="radio" name="spiral" id="archimedean" value="archimedean" checked="checked"> Archimedean</label>
    <label for="rectangular"><input type="radio" name="spiral" id="rectangular" value="rectangular"> Rectangular</label>
  <p><label for="scale">Scale:</label>
    <label for="scale-log"><input type="radio" name="scale" id="scale-log" value="log" checked="checked"> log n</label>
    <label for="scale-sqrt"><input type="radio" name="scale" id="scale-sqrt" value="sqrt"> √n</label>
    <label for="scale-linear"><input type="radio" name="scale" id="scale-linear" value="linear"> n</label>
  <p><label for="font">Font:</label> <input type="text" id="font" value="Impact">
</div>

<div id="angles">
  <p><input type="number" id="angle-count" value="5" min="1"> <label for="angle-count">orientations</label>
    <label for="angle-from">from</label> <input type="number" id="angle-from" value="-60" min="-90" max="90"> °
    <label for="angle-to">to</label> <input type="number" id="angle-to" value="60" min="-90" max="90"> °
</div>
</div>

  </div>
  
<!--  	<div id="tabs-6">
    <div id="slideshow" style="border: solid 1px; height: 600px; overflow: auto;">
<iframe id="visio" src = "" width="100%" height="100%" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture" ></iframe>
    </div>
	</div>-->

<!--
  <div id="tabs-4">
    <input type="button" value="Start Scanning" onclick="StartScan();"/>
    <input type="button" value="Stop Scanning" onclick="if (scanner) scanner.stop();"/>
    <select id="AllCameras" style="display:none" onchange="if (scanner) scanner.stop(); StartScan();"></select>
    <script src="html5-qrcode.min.js"></script>
    <div id="reader"></div>
    <script type="text/javascript">
      const scanner = new Html5Qrcode("reader");
      function StartScan() {
        document.getElementById('AllCameras').style.display='block';

        Html5Qrcode.getCameras().then(devices => {
          if (devices && devices.length) {
            var select = document.getElementById('AllCameras');
            if (select.length == 0) {
              for(var i=0; i<devices.length; i++) {
                var opt=document.createElement("option");
                opt.value=devices[i].id;
                opt.innerHTML=devices[i].label;
                if (i==0) opt.selected=true;
                select.appendChild(opt);
              }
            }

            var cameraId = select.value;
            scanner.start(
              cameraId,
              {
                fps: 10,
                qrbox: 250
              },
              qrCodeMessage => {
                var content=qrCodeMessage;
                var pattern='ROOMID=';
                var offset=content.length-content.indexOf(pattern)-pattern.length;
                document.getElementById('classroomid').value=content.substr(-offset);
                alert('Find Classroom id : '+content.substr(-offset));
                scanner.stop();
            },
          errorMessage => {
            console.log(`QR Code no longer in front of camera.`);
          })
          .catch(err => {
              console.log(`Unable to start scanning, error: ${err}`);
          });
          }
        }).catch(err => {
          alert('No camera found !')
        });
      }
    /*
    function StartScan() {
      let scanner = new Instascan.Scanner({ video: document.getElementById('preview'), mirror: false });
      scanner.addListener('scan', function (content) {
        document.getElementById('classroomid').value=content.substr(-32);
        alert('Find Classroom id : '+content.substr(-32));
        scanner.stop();
      });
      Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
          scanner.start(cameras[0]);
        } else {
          console.error('No cameras found.');
        }
      }).catch(function (e) {
        console.error(e);
      });
    }
    */
    </script>
  </div>
-->
    <div id="tabs-7">
        <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAkACQAAD/4QCeRXhpZgAATU0AKgAAAAgABQESAAMAAAABAAEAAAEaAAUAAAABAAAASgEbAAUAAAABAAAAUgEoAAMAAAABAAIAAIdpAAQAAAABAAAAWgAAAAAAAACQAAAAAQAAAJAAAAABAAOShgAHAAAAEgAAAISgAgAEAAAAAQAAAvugAwAEAAAAAQAAAJYAAAAAQVNDSUkAAABTY3JlZW5zaG90/+EJIWh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8APD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS40LjAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIi8+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPD94cGFja2V0IGVuZD0idyI/PgD/7QA4UGhvdG9zaG9wIDMuMAA4QklNBAQAAAAAAAA4QklNBCUAAAAAABDUHYzZjwCyBOmACZjs+EJ+/+IP8ElDQ19QUk9GSUxFAAEBAAAP4GFwcGwCEAAAbW50clJHQiBYWVogB+UABgALAAYAIgACYWNzcEFQUEwAAAAAQVBQTAAAAAAAAAAAAAAAAAAAAAAAAPbWAAEAAAAA0y1hcHBsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASZGVzYwAAAVwAAABiZHNjbQAAAcAAAASCY3BydAAABkQAAAAjd3RwdAAABmgAAAAUclhZWgAABnwAAAAUZ1hZWgAABpAAAAAUYlhZWgAABqQAAAAUclRSQwAABrgAAAgMYWFyZwAADsQAAAAgdmNndAAADuQAAAAwbmRpbgAADxQAAAA+Y2hhZAAAD1QAAAAsbW1vZAAAD4AAAAAodmNncAAAD6gAAAA4YlRSQwAABrgAAAgMZ1RSQwAABrgAAAgMYWFiZwAADsQAAAAgYWFnZwAADsQAAAAgZGVzYwAAAAAAAAAIRGlzcGxheQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAG1sdWMAAAAAAAAAJgAAAAxockhSAAAAFAAAAdhrb0tSAAAADAAAAexuYk5PAAAAEgAAAfhpZAAAAAAAEgAAAgpodUhVAAAAFAAAAhxjc0NaAAAAFgAAAjBkYURLAAAAHAAAAkZubE5MAAAAFgAAAmJmaUZJAAAAEAAAAnhpdElUAAAAFAAAAohlc0VTAAAAEgAAApxyb1JPAAAAEgAAApxmckNBAAAAFgAAAq5hcgAAAAAAFAAAAsR1a1VBAAAAHAAAAthoZUlMAAAAFgAAAvR6aFRXAAAACgAAAwp2aVZOAAAADgAAAxRza1NLAAAAFgAAAyJ6aENOAAAACgAAAwpydVJVAAAAJAAAAzhlbkdCAAAAFAAAA1xmckZSAAAAFgAAA3BtcwAAAAAAEgAAA4ZoaUlOAAAAEgAAA5h0aFRIAAAADAAAA6pjYUVTAAAAGAAAA7ZlbkFVAAAAFAAAA1xlc1hMAAAAEgAAApxkZURFAAAAEAAAA85lblVTAAAAEgAAA95wdEJSAAAAGAAAA/BwbFBMAAAAEgAABAhlbEdSAAAAIgAABBpzdlNFAAAAEAAABDx0clRSAAAAFAAABExwdFBUAAAAFgAABGBqYUpQAAAADAAABHYATABDAEQAIAB1ACAAYgBvAGoAac7st+wAIABMAEMARABGAGEAcgBnAGUALQBMAEMARABMAEMARAAgAFcAYQByAG4AYQBTAHoA7QBuAGUAcwAgAEwAQwBEAEIAYQByAGUAdgBuAP0AIABMAEMARABMAEMARAAtAGYAYQByAHYAZQBzAGsA5gByAG0ASwBsAGUAdQByAGUAbgAtAEwAQwBEAFYA5AByAGkALQBMAEMARABMAEMARAAgAGMAbwBsAG8AcgBpAEwAQwBEACAAYwBvAGwAbwByAEEAQwBMACAAYwBvAHUAbABlAHUAciAPAEwAQwBEACAGRQZEBkgGRgYpBBoEPgQ7BEwEPgRABD4EMgQ4BDkAIABMAEMARCAPAEwAQwBEACAF5gXRBeIF1QXgBdlfaYJyAEwAQwBEAEwAQwBEACAATQDgAHUARgBhAHIAZQBiAG4A/QAgAEwAQwBEBCYEMgQ1BEIEPQQ+BDkAIAQWBBoALQQ0BDgEQQQ/BDsENQQ5AEMAbwBsAG8AdQByACAATABDAEQATABDAEQAIABjAG8AdQBsAGUAdQByAFcAYQByAG4AYQAgAEwAQwBECTAJAgkXCUAJKAAgAEwAQwBEAEwAQwBEACAOKg41AEwAQwBEACAAZQBuACAAYwBvAGwAbwByAEYAYQByAGIALQBMAEMARABDAG8AbABvAHIAIABMAEMARABMAEMARAAgAEMAbwBsAG8AcgBpAGQAbwBLAG8AbABvAHIAIABMAEMARAOIA7MDxwPBA8kDvAO3ACADvwO4A8wDvQO3ACAATABDAEQARgDkAHIAZwAtAEwAQwBEAFIAZQBuAGsAbABpACAATABDAEQATABDAEQAIABhACAAQwBvAHIAZQBzMKsw6TD8AEwAQwBEAAB0ZXh0AAAAAENvcHlyaWdodCBBcHBsZSBJbmMuLCAyMDIxAABYWVogAAAAAAAA8xYAAQAAAAEWylhZWiAAAAAAAACC3QAAPVn///+8WFlaIAAAAAAAAExOAAC06AAACuxYWVogAAAAAAAAJ6sAAA2+AADIhWN1cnYAAAAAAAAEAAAAAAUACgAPABQAGQAeACMAKAAtADIANgA7AEAARQBKAE8AVABZAF4AYwBoAG0AcgB3AHwAgQCGAIsAkACVAJoAnwCjAKgArQCyALcAvADBAMYAywDQANUA2wDgAOUA6wDwAPYA+wEBAQcBDQETARkBHwElASsBMgE4AT4BRQFMAVIBWQFgAWcBbgF1AXwBgwGLAZIBmgGhAakBsQG5AcEByQHRAdkB4QHpAfIB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DQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0N/90ABAAw/9oADAMBAAIRAxEAPwD9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA//Q/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooqCW5ghUtI4UDqSaAJ6K8x1/wCMXw38MuY9a8QWFtIP4GnUv/3yCTXES/tOfCaNto1SR/dLWd1/MIRXsYbh7NMRHnoYacl3UJNfgjycRn2WUJunXxMItdHOKf3XPoWivCrL9o74RXjrG2v29sznCi6DW+f+/gWvV9K8TaFrkIn0m+gu425DQyLIP0Nc+MyrG4TXFUZQ/wAUWvzR0YTNMFim1ha0Z2/lkn+TN6ikVlYZU5pa887gooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA//R/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACopZkhUs5wBSTzJBGZHOAK+B/jx8ery+u7jwb4MuDDDETFfX8R+YsODDCR0x0dx0+6vOSPe4d4dxec4tYTCLzbe0V3f6LqeHxBxBhcnwrxWKfkkt5Psv1eyR618Uf2k9B8IyzaL4cRdY1aPKuqPtt7dsf8tZBnn/ZUFvbFfEni34o+O/G0rtrurTeQx4tLVjbW6jPA2odzeh3MQfQV54Ska8kIue5wMsfUnkkn6k+9Pxjg1/TnDnAuV5RB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aMy8ZlkU/Qxt/hX3fGNCNfIsXCW3JJ/+ArmX4pHxHCVaVHOsLOO/PFf+BPlf4Nn65wtviVvUVJVLTm3WcR/2RV2v45P62CiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/9T9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr5g/aZs3uPBF86gkxIJQP+uZDf0r6fry74q6MuseGrq3ZdweJlI9iMV3ZXi/qmNo4r+SUZfc0zizLCfWsJVw388ZR+9NH5Ingmkqe5tpbK4ls5gRJA7RNnrlDjP49fxqCv7chOM4qcHdPY/jacJRk4zVmuh7t+zT/wAlm0T/AK4X3/pO9fqUK/LX9mr/AJLNon/XC+/9J3r9Su1fzb4xf8jqn/17X/pUz+h/Cb/kT1P+vj/9JgFFFZ+p6rpujWUuo6tdQ2drCC0k07iONQPVmIFflMISnJRgrtn6bKSinKTskaFUNS1TTtHs5dR1W5itLWEFpJp3EcagerNgV8keP/2s9FsPM0/4e2n9rzjK/b7ndFZL7ouPMl9RwFPrXxf4u8b+LPHd59u8XanLqLA7o4W+S2iPX93AvyLg9CdzD1r9Q4e8K8yx1quO/c0/Ne8/+3en/b1vRn5rn/idl2CvSwX76flpBf8Ab3X/ALdT82j7S8fftZ6Hp4ksPh7a/wBsT8r/AGhcborJfdB/rJvUYAQ/3hXxh4v8deLfHl59u8WalNqDK26OJvktoT1/dQL8i47Mdz/7VcmSSck5PrSV+55BwfleTxTwlP3/AOZ6y+/p8kkfi2ecV5nmzaxdT3P5VpH7uv8A282+wpJJya9Q+Ddo118Q9NCjIiEspPpgBR/6FXl1fTn7NXh6S98QXOrsvyR7YEPuPmb9SB+Fc3HuOjhMhxM5fajyr1l7v5Nv5HTwPgpYrPMPBLRS5n6R9780l8z9F9PXbZxD/ZFXajiXZGq+gqSv5DP6sCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/9X9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigArP1S0W9s5IGGdykVoUHnigD8q/jd4Om8OeKJL+NCLe8b5iBwJB0P/Ah+oFeJ1+pnxf8Ah9beK9GnQx5YqcEdQexHvX5ma7ol94f1GXTb9Crxk7WIwHX1H9a/pTwv4shjcGsrxEv3tNWX96K2+cdn5Wfe388eJXC88Hi3mVCP7qo9f7snv8pbrzuux67+zV/yWfRP+uF9/wCk7V+nOqatpeiWEup6xdw2VpApeWe4kWKNFHOSzEAV+PngjxhqfgLxJb+KdGigmvbWKeOJbjcYgZ0MZZguC20HIGRk8ZHWo/FfjTxV45vhqPi3U59SlRt0SSHbbwn/AKZQLiNMdmwXx1Y1rxdwBXz3N4YmVRQpRgk+sm05PRbbNat/JmPC3HdHJMqlhoU3Oq5trpFK0Vq9+j0S9Wj7Q+IH7Wmj2Pmaf8O7P+1JuV/tC7DRWannmOPiWb1B+RD2Y18YeLPGvivx1e/b/Fmpzag6nMcbnbbxf9c4Vwi49cFvUmuXOe9JX1uQcIZXk8V9Tp+//M9Zff09Ekj5bPOKszzZ/wC11Pd/lWkfu6+rbYZooor6Y+dCiil96AJIIZbiaO3gXfJKwRF9WboP8fav0z+BPglfDfh63Dr+8K7mbHJZuSfxNfL3wN+GFzrGpRa5qMREa/6lWHQd2Puf0FfozpljHYWqQRjAUAV/NnilxZDMMTHLsJK9Om9X0cttPKKur9W30sz+h/DXheeAw7zDFK1SotF1Ud9fOTs7dkutzQooor8mP1EKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigD//1v38ooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigCKaJJkKOMg18yfFv4OWniO3e5to8SjLKyjBB9RX1BTJI0lUq4yDW+GxNXD1Y16EnGUXdNaNMxxGHpV6cqNaKlF6NPZn4z+IvCur+Gbp7fUIWCKSBIB8pHv6fyrm6/W3xd8NNH8RwuJIV3MD2r5E8X/s53VtI82k7kHJ2r0/Kv3ThzxepuKo5zCz/nitH6x6f9u39EfivEHhTNSdbKJ3X8knqvSXX529WfJlFeh6l8MPFmnMVa28wDuAR/jXPN4U8RIcNZSZ+lfpWG40yKvHmhi4fOSi/ulZn53iOD87oy5Z4WfyXN+Mbo52iustvBHie6YLHZOM+vH9K9D0D4HeJtVdTdAxqTyFHP5muXHcf5BhYtyxMZPtH3n+F197R04LgXPMTK0cO4rvL3fzs/uTPFI4pJpFiiUu7dFUZJ/Cvob4X/AAX1HXbuK/1eIrErBljI4+p9TX0P4D+AGmaRsnuowz8EkjJP419L6XotlpcKxW6BQoxwK/HOLPFHE5hCWFy6Lp03u/tNdtNIrva787aH63wv4a4bATWJx7VSotl9lP5/E/Wy8r6mX4Y8M2egWUdvAgXaAOBXWUUV+Tn6gFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAf/X/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAqKSGKUYdQalooAwrnw7pdz/rIFP4Vkv4G0Fzk26flXZ0UAcrB4O0WA5S3T8q3INNs7biKNR+FXqKAEAA4HFLRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAf/Q/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAP/2Q==" id="img_slides"/>

	</div>
  <div id="tabs-8"><iframe id="wooclap" src="<?php echo $wooclapaddress; ?>" width="100%" height="100%" style="width: 95%; height: 90%; bottom:0px; left:0px; z-index:1000;" webkitallowfullscreen mozallowfullscreen allowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write"></iframe>
  </div>

  <div id="tabs-9"><iframe id="extratab1" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="" width="100%" height="100%" ></iframe>
  </div>
  <div id="tabs-10"><iframe id="extratab2" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="" width="100%" height="100%" ></iframe>
  </div>
  <div id="tabs-11"><iframe id="extratab3" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="" width="100%" height="100%" ></iframe>
  </div>
  <div id="tabs-12"><iframe id="extratab4" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture;clipboard-read; clipboard-write" src="" width="100%" height="100%" ></iframe>
  </div>

</div>

</form>
<div id="FS" onclick="GoFS();"  title="Toggle fullscreen mode"><img src="data:image/png;base64,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" style="width:50px; height: 50px"></div>


<script src="jquery/jquery.js"></script>
<script src="jquery/jquery-ui.min.js"></script>
  <style>
  .ui-tabs-vertical { width: 55em;}
  .ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 12em; }
  .ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
  .ui-tabs-vertical .ui-tabs-nav li a { display:block; }
  .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; }
  .ui-tabs-vertical .ui-tabs-panel { padding: 1em; float: right; width: 40em;}
  </style>

<script>
//$( "#tabs" ).tabs();
//$( "#enregistrer" ).button();
/*
  $( function() {
    $( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
  } );
*/


function retourOK(e) {
  var res='';
  for(var p in e) res+=p+'='+e[p]+'\n';
  console.log(res);
}

function retourErreur() {}

window.onbeforeunload = function(event) {
  event.returnValue = "Quit";
};

window.addEventListener('unload', function(event) {
  saveTextQ("[newstudentlived]");
});

function fixTitle() {
  var e=document.getElementById("titre");
  e.style.top=(window.innerHeight-e.style.height-100-10)+'px';
}

function MinTitlePos() {
  var e=document.getElementById("titre");
  var postop=1*(e.style.top.replace('px',''));
  if (postop>window.innerHeight-50) {
    e.style.top=(window.innerHeight-50)+'px';
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
    $("#titre").draggableTouch();

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

  $( function() {
    $( "#showmenu" ).tooltip();
  } );
  
</script>
<script src="https://www.jasondavies.com/d3.min.js"></script>
<script src="https://www.jasondavies.com/wordcloud/cloud.min.js"></script>
<script>

var bufferWC=[];
var sizebufferWC=15;
var placebufferWC=0;
var buffer_has_changed=true;

for(var i=0; i<sizebufferWC;i++) bufferWC[i]="";

function removeTags(str) {
    if ((str===null) || (str===''))
        return false;
    else
        str = str.toString();
    return str.replace( /(<([^>]+)>)/ig, ' ');
}

function CreateString() {
  var previous = (placebufferWC -1 + sizebufferWC)%sizebufferWC;
  var pprevious = (placebufferWC -2 + sizebufferWC)%sizebufferWC;
  var ch = bufferWC.join(' ') + ' '+bufferWC[previous]+ ' '+bufferWC[previous]+ ' '+bufferWC[pprevious];
  return ch;
}

function updateWC() {
  //if (buffer_has_changed) parseText(removeTags(bufferWC.join(' ')));
  if (buffer_has_changed) {
  parseText(CreateString());
  console.log("WC with : "+CreateString());
  }
  buffer_has_changed=false;
}

function LaunchWC() {
  setInterval(updateWC,1000);
}
setTimeout(LaunchWC,3000);
</script>
</body>
</html>
