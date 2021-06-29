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

#logonext {
  position: fixed;
  top:5px;
  right: 5px;
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
  left: 20px;
  width: 90%;
  margin-left: auto;
  margin-right: auto;
  height: 64px;
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
}


#sharedqr {
    transition: 1s ease;
  transform-origin: top left;
}
#sharedqr:hover {
  transform: scale(2);
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
var subtitle_language;

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
  console.log("Subtitles set to "+subtitle_language+ " builtin_language is "+builtin_language);
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

function TranslateThen(ch, l, linput,f) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "Translate.php?texte=" + ch + "&lang=" + l + "&langinput=" + linput, true);

  xhr.onreadystatechange = function() { // Call a function when the state changes.
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      // console.log(this.responseText);
      var response = JSON.parse(this.responseText);
      if ((! response["error"]) && response["data"]["translations"]) {
        f(response["data"]["translations"][0]["translatedText"]);
      } else {
        f("---- Translation ERROR ----");
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
  var tobesaidbetweenslides='';
  if (before == 1 && document.getElementById('tobesaid').value != '') tobesaidbetweenslides=document.getElementById('tobesaid').value+'. <br/>';

  ch=tobesaidbetweenslides+ch;
  document.getElementById('notebook').innerHTML+='<br/>'+ch;
  EcrireTitle(ch);
  //saveTextNB(document.getElementById('notebook').innerHTML);
  var Tch=ch.split('<br/>');
  saveTextTable(Tch,0);
  //for(var c in Tch) saveText(Tch[c].replace(/<br\/>/gi,"").replace(/<br>/gi,""));
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

var currentPresentation=[];
var currentDiscours=-1;

function formateresponse(datatxt) {
  currentPresentation=[];
  //alert(datatxt);
  var res = '';
  var data = JSON.parse(datatxt);
  for (var i=0; i<data.length; i++) {
    slide = data[i];
    var txtnotes = '';
    for (var j=0; j<slide["Notes"].length; j++) {
      if (slide["Notes"][j].indexOf("[forward]")>=0 || slide["Notes"][j].indexOf("[backward]")>=0 || slide["Notes"][j].indexOf("[pause]")>=0 || slide["Notes"][j].indexOf("[resource")>=0) {
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
        res = res + '<dd><span onclick="addToNB(\''+slide["Notes"][j]+'\',0); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;'+slide["Notes"][j]+'</dd>\n';
          currentPresentation.push({'Slide':slide["Slide"],'Discours':slide["Notes"][j]});
          flag = true;
        }
      }
      if (!flag) currentPresentation.push({'Slide':slide["Slide"],'Discours':''});
    }
    res+='</dl>'
  }
//  alert(res);
  return res;
}

function Forward() {
  if (currentPresentation.length>0) {
    currentDiscours=(currentDiscours+1)%currentPresentation.length;
    addToNB(currentPresentation[currentDiscours]['Discours'],0);
    ShowImage(currentPresentation[currentDiscours]['Slide']);
  }
}

function Backward() {
  if (currentPresentation.length>0) {
    currentDiscours=(currentDiscours-1+currentPresentation.length)%currentPresentation.length;
    addToNB(currentPresentation[currentDiscours]['Discours'],0);
    ShowImage(currentPresentation[currentDiscours]['Slide']);
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
var builtin_recognitionon = false;

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
  var loutput = subtitle_language.substr(0,2).toLowerCase();
  console.log("Ecrire title : "+document.getElementById('subtitleon').checked+"  "+linput+" -> "+loutput);
  if ((! document.getElementById('subtitleon').checked) || (linput == loutput)) {
    document.getElementById('titre').innerHTML=ch;
  } else {
    TranslateThen(ch,loutput,linput,function (ch){document.getElementById('titre').innerHTML=ch;});
  }
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

function Init() {
  PopulateSubtitlesLang();
  document.getElementById('question').addEventListener('keyup', function (event) {
    if (event.code === 'Enter') {
        saveTextQ(document.getElementById('question').value);
      event.preventDefault();
      event.stopPropagation();
    }
  },false);

  resizeSlides();

  document.getElementById('pincode').value=(""+Math.floor(100000+Math.random()*100000)).substr(1);
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
      //console.log('Debug -- '+T[i]+'     '+builtin_language.substr(0,2)+' -> '+L[1].substr(0,2));
      if (builtin_language.substr(0,2).toLowerCase() != L[1].substr(0,2).toLowerCase())
        TranslateQ(L[2], builtin_language.substr(0,2), L[1].substr(0,2),L[0]);
      else {
        EcrireCR(L[0],L[2]);
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
  f.append('inputlanguage',builtin_language);
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

function FromCompleteLessonToTable() {
  var txt = document.getElementById('notebookcompleteraw').innerHTML;
  document.getElementById('notebookcompleteformatted').innerHTML = txt;
}

function playlesson() {}

function GoFS() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
    document.getElementById('bar').style.transform="scaleY(0.5)";
    //document.getElementById('bar').style.bottom="10px";
    //resizeSlides();
  } else {
    document.exitFullscreen();
    document.getElementById('bar').style.transform="scaleY(1)";
    //resizeSlides();
  }
}

function resizeSlides() {
  var slide = document.getElementById('img_slides');
  var ratio = slide.width/slide.height;
  var aggrandissmentX=window.innerWidth/slide.width;
  var aggrandissmentY=(window.innerHeight-150)/slide.height;
  if (aggrandissmentX/aggrandissmentY > ratio ) {
    /*
    slide.width=aggrandissmentY*slide.width;
    slide.style.width=aggrandissmentY*slide.width+"px";
    slide.height=aggrandissmentY*slide.height;
    slide.style.height=aggrandissmentY*slide.height+"px";
    */
    slide.style.transform='scale('+aggrandissmentY+')';
  } else {
/*    slide.width=aggrandissmentX*slide.width;
    slide.style.width=aggrandissmentX*slide.width+"px";
    slide.height=aggrandissmentX*slide.height;
    slide.style.height=aggrandissmentX*slide.height+"px";
*/
slide.style.transform='scale('+aggrandissmentX+')';

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
      document.getElementById('pincode').value=pin;
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
</script>


<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body onload="Init(); InitVoices(); setTimeout(InitVoices,1000); setTimeout(InitVoices,2000); setTimeout(function () {StartSpeechRecognition(Traduction); },3000); setTimeout(Lancer,3000); ready();">


<div id="micon" onclick="document.getElementById('recognitionon').checked = !document.getElementById('recognitionon').checked; if (document.getElementById('recognitionon').checked) {this.innerHTML='<i class=\'material-icons blink big\'>record_voice_over</i>';} else {this.innerHTML='<i class=\'material-icons big\'>voice_over_off</i>';};"><i class='material-icons big'>voice_over_off</i></div>

<div id="questionicon"><i class='material-icons big'>thumb_up</i></div>

<div id="logonext"><img src="images/logo-next-omist.png" alt="NExT" style="width: 109px; height: 50px;"/></div>


<div id="titre"><h1>Automatic translation for teaching purposes: Teacher</h1></div>

<form id="myform">

<div id="tabs">
	<ul id="bar">
		<li><a href="#tabs-0" onclick="inSlideTab=false;">Informations</a></li>
		<li><a href="#tabs-1" onclick="timenew_question=0; inSlideTab=false;">Classroom</a></li>
		<li><a href="#tabs-2" onclick="inSlideTab=false;">Notebook</a></li>
		<li><a href="#tabs-5" onclick="inSlideTab=false;">Complete lesson</a></li>
		<li><a href="#tabs-6" onclick="if (!visioactive) document.getElementById('visio').src='https://meet.jit.si/Translator_'+document.getElementById('classroomid').value; visioactive=true; inSlideTab=false;">Visio</a></li>
		<li><a href="#tabs-3" onclick="inSlideTab=false;">Live translation</a></li>
		<li><a href="#tabs-4" onclick="inSlideTab=false;">Share</a></li>
    <li><a href="#tabs-7" onclick="inSlideTab=true; document.getElementById('img_slides').focus();">Slides</a></li>
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
<li>Pin code : <input type="text" id="pincode" style="width: 50px;" disabled/> &nbsp;
    <input type="button" value="Change pin" onclick="document.getElementById('pincode').value=prompt('Enter a PIN code'); Synchronize_save();"/>
    <input type="button" value="Synchronize" onclick="Synchronize(prompt('Enter a PIN code'));"/>
</li>
<li>Translate subtitles ?  <input type="checkbox" id="subtitleon"/></li>
<li>Language for subtitles ? <select id="subtitlelanguage" onchange="setLanguageSubtitle(this.value);">
  </select></li>
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
    <input type="file" name="ppt_presentation" id="ppt_presentation" onchange="SlideNotes()" accept=".pptx,.odp,.txt,.html" max-size="50000"/>
   <label for="tobesaid" title="Portion of text inserted between each slides (eq. Next slide...)">Slide transition : </label>
    <input type="text" id="tobesaid" value="">
    <br/>
    <progress id="progressBar" value="0" max="100" style="width:300px;"></progress>
    </div>
    <div id="notebookcompleteformatted" style="border: solid 1px; height: 600px; overflow: auto;">
    </div>
	</div>
	<div id="tabs-6">
    <div id="slideshow" style="border: solid 1px; height: 600px; overflow: auto;">
<iframe id="visio" src = "" width="100%" height="100%" allowfullscreen webkitallowfullscreen allow="camera;microphone;display-capture" ></iframe>
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
        <li>QR code : <div title="" id="sharedqr" style="margin-left: auto; margin-right: auto;"></div></li>
        <li>Teacher application : <a href="index.php?ROOMID=<?php echo $classroomid; ?>">index.php?ROOMID=<?php echo $classroomid; ?></a></li>
    </ul>
  </div>
  <div id="tabs-7">
      <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAkACQAAD/4QCeRXhpZgAATU0AKgAAAAgABQESAAMAAAABAAEAAAEaAAUAAAABAAAASgEbAAUAAAABAAAAUgEoAAMAAAABAAIAAIdpAAQAAAABAAAAWgAAAAAAAACQAAAAAQAAAJAAAAABAAOShgAHAAAAEgAAAISgAgAEAAAAAQAAAvugAwAEAAAAAQAAAJYAAAAAQVNDSUkAAABTY3JlZW5zaG90/+EJIWh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8APD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS40LjAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIi8+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPD94cGFja2V0IGVuZD0idyI/PgD/7QA4UGhvdG9zaG9wIDMuMAA4QklNBAQAAAAAAAA4QklNBCUAAAAAABDUHYzZjwCyBOmACZjs+EJ+/+IP8ElDQ19QUk9GSUxFAAEBAAAP4GFwcGwCEAAAbW50clJHQiBYWVogB+UABgALAAYAIgACYWNzcEFQUEwAAAAAQVBQTAAAAAAAAAAAAAAAAAAAAAAAAPbWAAEAAAAA0y1hcHBsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASZGVzYwAAAVwAAABiZHNjbQAAAcAAAASCY3BydAAABkQAAAAjd3RwdAAABmgAAAAUclhZWgAABnwAAAAUZ1hZWgAABpAAAAAUYlhZWgAABqQAAAAUclRSQwAABrgAAAgMYWFyZwAADsQAAAAgdmNndAAADuQAAAAwbmRpbgAADxQAAAA+Y2hhZAAAD1QAAAAsbW1vZAAAD4AAAAAodmNncAAAD6gAAAA4YlRSQwAABrgAAAgMZ1RSQwAABrgAAAgMYWFiZwAADsQAAAAgYWFnZwAADsQAAAAgZGVzYwAAAAAAAAAIRGlzcGxheQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAG1sdWMAAAAAAAAAJgAAAAxockhSAAAAFAAAAdhrb0tSAAAADAAAAexuYk5PAAAAEgAAAfhpZAAAAAAAEgAAAgpodUhVAAAAFAAAAhxjc0NaAAAAFgAAAjBkYURLAAAAHAAAAkZubE5MAAAAFgAAAmJmaUZJAAAAEAAAAnhpdElUAAAAFAAAAohlc0VTAAAAEgAAApxyb1JPAAAAEgAAApxmckNBAAAAFgAAAq5hcgAAAAAAFAAAAsR1a1VBAAAAHAAAAthoZUlMAAAAFgAAAvR6aFRXAAAACgAAAwp2aVZOAAAADgAAAxRza1NLAAAAFgAAAyJ6aENOAAAACgAAAwpydVJVAAAAJAAAAzhlbkdCAAAAFAAAA1xmckZSAAAAFgAAA3BtcwAAAAAAEgAAA4ZoaUlOAAAAEgAAA5h0aFRIAAAADAAAA6pjYUVTAAAAGAAAA7ZlbkFVAAAAFAAAA1xlc1hMAAAAEgAAApxkZURFAAAAEAAAA85lblVTAAAAEgAAA95wdEJSAAAAGAAAA/BwbFBMAAAAEgAABAhlbEdSAAAAIgAABBpzdlNFAAAAEAAABDx0clRSAAAAFAAABExwdFBUAAAAFgAABGBqYUpQAAAADAAABHYATABDAEQAIAB1ACAAYgBvAGoAac7st+wAIABMAEMARABGAGEAcgBnAGUALQBMAEMARABMAEMARAAgAFcAYQByAG4AYQBTAHoA7QBuAGUAcwAgAEwAQwBEAEIAYQByAGUAdgBuAP0AIABMAEMARABMAEMARAAtAGYAYQByAHYAZQBzAGsA5gByAG0ASwBsAGUAdQByAGUAbgAtAEwAQwBEAFYA5AByAGkALQBMAEMARABMAEMARAAgAGMAbwBsAG8AcgBpAEwAQwBEACAAYwBvAGwAbwByAEEAQwBMACAAYwBvAHUAbABlAHUAciAPAEwAQwBEACAGRQZEBkgGRgYpBBoEPgQ7BEwEPgRABD4EMgQ4BDkAIABMAEMARCAPAEwAQwBEACAF5gXRBeIF1QXgBdlfaYJyAEwAQwBEAEwAQwBEACAATQDgAHUARgBhAHIAZQBiAG4A/QAgAEwAQwBEBCYEMgQ1BEIEPQQ+BDkAIAQWBBoALQQ0BDgEQQQ/BDsENQQ5AEMAbwBsAG8AdQByACAATABDAEQATABDAEQAIABjAG8AdQBsAGUAdQByAFcAYQByAG4AYQAgAEwAQwBECTAJAgkXCUAJKAAgAEwAQwBEAEwAQwBEACAOKg41AEwAQwBEACAAZQBuACAAYwBvAGwAbwByAEYAYQByAGIALQBMAEMARABDAG8AbABvAHIAIABMAEMARABMAEMARAAgAEMAbwBsAG8AcgBpAGQAbwBLAG8AbABvAHIAIABMAEMARAOIA7MDxwPBA8kDvAO3ACADvwO4A8wDvQO3ACAATABDAEQARgDkAHIAZwAtAEwAQwBEAFIAZQBuAGsAbABpACAATABDAEQATABDAEQAIABhACAAQwBvAHIAZQBzMKsw6TD8AEwAQwBEAAB0ZXh0AAAAAENvcHlyaWdodCBBcHBsZSBJbmMuLCAyMDIxAABYWVogAAAAAAAA8xYAAQAAAAEWylhZWiAAAAAAAACC3QAAPVn///+8WFlaIAAAAAAAAExOAAC06AAACuxYWVogAAAAAAAAJ6sAAA2+AADIhWN1cnYAAAAAAAAEAAAAAAUACgAPABQAGQAeACMAKAAtADIANgA7AEAARQBKAE8AVABZAF4AYwBoAG0AcgB3AHwAgQCGAIsAkACVAJoAnwCjAKgArQCyALcAvADBAMYAywDQANUA2wDgAOUA6wDwAPYA+wEBAQcBDQETARkBHwElASsBMgE4AT4BRQFMAVIBWQFgAWcBbgF1AXwBgwGLAZIBmgGhAakBsQG5AcEByQHRAdkB4QHpAfIB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DQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0N/90ABAAw/9oADAMBAAIRAxEAPwD9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA//Q/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooqCW5ghUtI4UDqSaAJ6K8x1/wCMXw38MuY9a8QWFtIP4GnUv/3yCTXES/tOfCaNto1SR/dLWd1/MIRXsYbh7NMRHnoYacl3UJNfgjycRn2WUJunXxMItdHOKf3XPoWivCrL9o74RXjrG2v29sznCi6DW+f+/gWvV9K8TaFrkIn0m+gu425DQyLIP0Nc+MyrG4TXFUZQ/wAUWvzR0YTNMFim1ha0Z2/lkn+TN6ikVlYZU5pa887gooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA//R/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACopZkhUs5wBSTzJBGZHOAK+B/jx8ery+u7jwb4MuDDDETFfX8R+YsODDCR0x0dx0+6vOSPe4d4dxec4tYTCLzbe0V3f6LqeHxBxBhcnwrxWKfkkt5Psv1eyR618Uf2k9B8IyzaL4cRdY1aPKuqPtt7dsf8tZBnn/ZUFvbFfEni34o+O/G0rtrurTeQx4tLVjbW6jPA2odzeh3MQfQV54Ska8kIue5wMsfUnkkn6k+9Pxjg1/TnDnAuV5RB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aMy8ZlkU/Qxt/hX3fGNCNfIsXCW3JJ/+ArmX4pHxHCVaVHOsLOO/PFf+BPlf4Nn65wtviVvUVJVLTm3WcR/2RV2v45P62CiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/9T9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr5g/aZs3uPBF86gkxIJQP+uZDf0r6fry74q6MuseGrq3ZdweJlI9iMV3ZXi/qmNo4r+SUZfc0zizLCfWsJVw388ZR+9NH5Ingmkqe5tpbK4ls5gRJA7RNnrlDjP49fxqCv7chOM4qcHdPY/jacJRk4zVmuh7t+zT/wAlm0T/AK4X3/pO9fqUK/LX9mr/AJLNon/XC+/9J3r9Su1fzb4xf8jqn/17X/pUz+h/Cb/kT1P+vj/9JgFFFZ+p6rpujWUuo6tdQ2drCC0k07iONQPVmIFflMISnJRgrtn6bKSinKTskaFUNS1TTtHs5dR1W5itLWEFpJp3EcagerNgV8keP/2s9FsPM0/4e2n9rzjK/b7ndFZL7ouPMl9RwFPrXxf4u8b+LPHd59u8XanLqLA7o4W+S2iPX93AvyLg9CdzD1r9Q4e8K8yx1quO/c0/Ne8/+3en/b1vRn5rn/idl2CvSwX76flpBf8Ab3X/ALdT82j7S8fftZ6Hp4ksPh7a/wBsT8r/AGhcborJfdB/rJvUYAQ/3hXxh4v8deLfHl59u8WalNqDK26OJvktoT1/dQL8i47Mdz/7VcmSSck5PrSV+55BwfleTxTwlP3/AOZ6y+/p8kkfi2ecV5nmzaxdT3P5VpH7uv8A282+wpJJya9Q+Ddo118Q9NCjIiEspPpgBR/6FXl1fTn7NXh6S98QXOrsvyR7YEPuPmb9SB+Fc3HuOjhMhxM5fajyr1l7v5Nv5HTwPgpYrPMPBLRS5n6R9780l8z9F9PXbZxD/ZFXajiXZGq+gqSv5DP6sCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/9X9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigArP1S0W9s5IGGdykVoUHnigD8q/jd4Om8OeKJL+NCLe8b5iBwJB0P/Ah+oFeJ1+pnxf8Ah9beK9GnQx5YqcEdQexHvX5ma7ol94f1GXTb9Crxk7WIwHX1H9a/pTwv4shjcGsrxEv3tNWX96K2+cdn5Wfe388eJXC88Hi3mVCP7qo9f7snv8pbrzuux67+zV/yWfRP+uF9/wCk7V+nOqatpeiWEup6xdw2VpApeWe4kWKNFHOSzEAV+PngjxhqfgLxJb+KdGigmvbWKeOJbjcYgZ0MZZguC20HIGRk8ZHWo/FfjTxV45vhqPi3U59SlRt0SSHbbwn/AKZQLiNMdmwXx1Y1rxdwBXz3N4YmVRQpRgk+sm05PRbbNat/JmPC3HdHJMqlhoU3Oq5trpFK0Vq9+j0S9Wj7Q+IH7Wmj2Pmaf8O7P+1JuV/tC7DRWannmOPiWb1B+RD2Y18YeLPGvivx1e/b/Fmpzag6nMcbnbbxf9c4Vwi49cFvUmuXOe9JX1uQcIZXk8V9Tp+//M9Zff09Ekj5bPOKszzZ/wC11Pd/lWkfu6+rbYZooor6Y+dCiil96AJIIZbiaO3gXfJKwRF9WboP8fav0z+BPglfDfh63Dr+8K7mbHJZuSfxNfL3wN+GFzrGpRa5qMREa/6lWHQd2Puf0FfozpljHYWqQRjAUAV/NnilxZDMMTHLsJK9Om9X0cttPKKur9W30sz+h/DXheeAw7zDFK1SotF1Ud9fOTs7dkutzQooor8mP1EKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigD//1v38ooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigCKaJJkKOMg18yfFv4OWniO3e5to8SjLKyjBB9RX1BTJI0lUq4yDW+GxNXD1Y16EnGUXdNaNMxxGHpV6cqNaKlF6NPZn4z+IvCur+Gbp7fUIWCKSBIB8pHv6fyrm6/W3xd8NNH8RwuJIV3MD2r5E8X/s53VtI82k7kHJ2r0/Kv3ThzxepuKo5zCz/nitH6x6f9u39EfivEHhTNSdbKJ3X8knqvSXX529WfJlFeh6l8MPFmnMVa28wDuAR/jXPN4U8RIcNZSZ+lfpWG40yKvHmhi4fOSi/ulZn53iOD87oy5Z4WfyXN+Mbo52iustvBHie6YLHZOM+vH9K9D0D4HeJtVdTdAxqTyFHP5muXHcf5BhYtyxMZPtH3n+F197R04LgXPMTK0cO4rvL3fzs/uTPFI4pJpFiiUu7dFUZJ/Cvob4X/AAX1HXbuK/1eIrErBljI4+p9TX0P4D+AGmaRsnuowz8EkjJP419L6XotlpcKxW6BQoxwK/HOLPFHE5hCWFy6Lp03u/tNdtNIrva787aH63wv4a4bATWJx7VSotl9lP5/E/Wy8r6mX4Y8M2egWUdvAgXaAOBXWUUV+Tn6gFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAf/X/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAqKSGKUYdQalooAwrnw7pdz/rIFP4Vkv4G0Fzk26flXZ0UAcrB4O0WA5S3T8q3INNs7biKNR+FXqKAEAA4HFLRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAf/Q/fyiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAP/2Q==" id="img_slides" onclick="Forward();"/>
	</div>


</div>
<div id="FS" onclick="GoFS();"  title="Toggle fullscreen mode"><img src=" data:image/png;base64,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" style="width:50px; height: 50px"></div>
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

document.getElementById("sharedqr").title="";

$('#bar').on('keydown keyup keypress', function(e) {
  console.log(e.keyCode);
  e.preventDefault();
});

</script>
</body>
</html>
