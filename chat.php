<?php
// Empêche la mise en cache 
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
?>
<?php


$synchronize = false;

if (isset($_GET["ROOMID"])) {
  $classroomid = $_GET["ROOMID"];
} else {
  $classroomid = 'TEST_CHAT';
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
	<title>Automatic translation for teaching purposes : CHAT</title>
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
  display: block;
}


@keyframes hithere {
  from, to { transform: translateX(0); }
  50% { transform: translateX(10px); }
}

#lilanguage {
  animation: none;
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

.highlight {
  color: black;
  transition: 1s ease;
}

.highlight:hover {
  color:rgb(200,200,200);
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
  /*
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
  */
  var optgroup2=document.createElement("optgroup");
  optgroup2.label="Available languages";
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

function recharger() {
  ClearCR();
  inputnumberQ=0;
  readTextQ();
  
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
  //setInterval(readText,500);
  setInterval(readTextQ,500);
  //setInterval(readImage,500);
  setLanguageInput(0);
  //resizeSlides();
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
  //resizeSlides();
  //activetab('tabs-0');

<?php
if ($synchronize) {
  echo "Synchronize(prompt('Enter the session pincode'));\n";
}
?>
  //setTimeout(function() {document.getElementById('wooclap').src='https://www.wooclap.com/';},1000);
  //setTimeout(function() {saveTextQ("[newstudentarrived]");},1000);
  //document.getElementById('iconeMenu').src=MenuOnIMG;
  //ChangeColor('titre',document.getElementById('ColorTitre').value);
}

function ClearCR() {
  document.getElementById('classroom').innerHTML='';
  ScreenCR=[];
}

function rgb(T) {
  return "rgb("+T[0]+","+T[1]+","+T[2]+")";
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
            TranslateQ(L[2], builtin_voice.lang.substr(0,2), L[1].substr(0,2), L[0],i);
          else {
            EcrireCR(L[0],L[2],i);
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
        EcrireCR("----","---- Translation ERROR ----",order)
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
  var TTabs=['tabs-0'];
  for(var i=0; i<TTabs.length; i++) {
    document.getElementById(TTabs[i]).style.transform="translateX("+(160*scalemenu)+"px)";
  }

  resizeSlides();
  
}
var atab = 'tabs-0';
function activetab(tab)  {
  var TTabs=['tabs-0'];
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

var MenuOnIMG=" data:image/png;base64,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";

//var MenuOnIMG="data:image/png;base64,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";
var MenuOffIMG="data:image/png;base64,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";

</script>



</head>
<body onload="Init(); setTimeout(loadVoicesWhenAvailable,100);">
<div id="logonext" style="display:none"><img src="images/logo-next-omist.png" alt="NExT" style="width: 109px; height: 50px;"/></div>
<div id="showmenu" onclick="ShowMenu();" style="display:none"><img src="" alt="☰" id="iconeMenu"/></div>
<div id="micon" style="display:none" onclick="gotolive=true; CompleteSpeech(); this.style.backgroundColor='rgba(255,0,0,0.5)'; setTimeout(()=> {document.getElementById('micon').style.backgroundColor='rgba(255,0,0,1)';},300);"><i class='material-icons'>sensors</i><span style="font-size: 10px;color: white;"><br/>LIVE</span></div>
<div id="repeat" style="display:none" onclick="Parle(document.getElementById('titre').innerHTML);" title="Repeat the last sentence"><i class='material-icons'>replay</i><span style="font-size: 10px;color: white;"><br/>REPEAT</span></div>

<!--
<div id="titre" onmouseover="document.getElementById('soustitre').style.transform='translateY(0px)'; document.getElementById('titre').style.transform='translateY(0px)';"><h1>Automatic translation for teaching purposes: Student</h1></div>
<div id="soustitre" onmouseover="this.style.transform='translateY(-64px)'; document.getElementById('titre').style.transform='translateY(-64px)';"></div>-->
<div style="display:none" id="titre"><h1>Automatic translation for teaching purposes: Student</h1></div>
<div style="display:none" id="soustitre"></div>

<form id="myform" onsubmit="return false;">

<div id="tabs">
	<div id="tabs-0">
  <ul style="list-style-type: none;">
<li style="display:none"><label for="classroomid">Classroom identifier :</label><input type="text" name="classroomid" id="classroomid" value="<?php echo $classroomid; ?>" /></li>
<li id="lilanguage"><label for="VoiceOutput">Student language :</label>
  <select id="VoiceOutput" onchange="setLanguageOutput(this.value);">
  </select>
</li>
<li><label for="StudentName">Student surname :</label>
  <input type="text" id="StudentName" onchange="if (this.value == 'Teacher') this.value='Student';" value="<?php echo $randomname; ?>"/>
</li>
<!-- <li><i class='material-icons' onclick="recharger();" class="tooltip" class="highlight" title="Refresh">refresh</i></li> -->
<li>
    <!-- <div style="height: 50px;"></div> -->
    <div id="classroom" style="border: solid 1px;"></div>

    <!-- <input type="text" id="questionold" placeholder="Your question here"/>-->
    <textarea id="question" placeholder="Your question here" rows="2" cols="50"></textarea><br/>
    <input type="button" value="send" class="button-3 tooltip" role="button" id="questionsend" onclick="sendclick();" title="Send your question"/>
    <input type="button" value="refresh" class="button-3 tooltip" role="button" title="Reload all the conversation and translate it if necessary." onclick="recharger();"/>
    </li>
    </ul>
	</div>
</div>

</form>



<script src="jquery/jquery.js"></script>
<script src="jquery/jquery-ui.min.js"></script>
  <style>
  .ui-tabs-vertical { width: 55em;}
  .ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 12em; }
  .ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
  .ui-tabs-vertical .ui-tabs-nav li a { display:block; }
  .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; }
  .ui-tabs-vertical .ui-tabs-panel { padding: 1em; float: right; width: 40em;}

  .tooltip {}
  </style>

<script>


function retourOK(e) {
  var res='';
  for(var p in e) res+=p+'='+e[p]+'\n';
  console.log(res);
}

function retourErreur() {}

//window.onbeforeunload = function(event) {
//  event.returnValue = "Quit";
//};

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
  $( function() {
    $( ".tooltip" ).tooltip();
  } );
  
</script>

</body>
</html>

