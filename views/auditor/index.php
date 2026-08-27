<!doctype html>
<html lang="fr">
<head>
	<title><?php echo $this->escape($pageTitle); ?></title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <link rel="icon" type="image/png" href="./images/favicon.svg" >
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <?php foreach ($pageStyles as $style): ?>
        <link href="<?php echo $this->escape($style); ?>" rel="stylesheet">
    <?php endforeach; ?>
    
    <script>
        window.pageData = <?php echo json_encode($jsVars); ?>;
        var ROOM_TOKEN = '<?php echo htmlspecialchars($roomToken, ENT_QUOTES); ?>';
    </script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>

</head>
<body>
<main>
<h1 style="display:none;">Outils de traduction : application étudiante</h1>
</main>
<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'Isite NExT" style="width: 109px; height: 50px;"></div>
<div id="micon" style="background-color: #EE0000;" onclick="gotolive=true; CompleteSpeech(); this.style.backgroundColor='rgba(238,0,0,0.5)'; setTimeout(()=> {document.getElementById('micon').style.backgroundColor='rgba(238,0,0,1)';},300);"><i class='material-icons'>sensors</i><span style="font-size: 10px;color: white;"><br>LIVE</span></div>
<div id="repeat" onclick="Parle(document.getElementById('titre').innerHTML);" title="Repeat the last sentence"><i class='material-icons'>replay</i><span style="font-size: 10px;color: white;"><br>REPEAT</span></div>

<div id="titre"><h1>Automatic translation provided by NExT OMIST Project</h1></div>

<div id="VOBanner"></div>
<div id="soustitre"></div>

<form id="myform" onsubmit="return false;">
<?php echo csrfField(); ?>

<!-- Settings Section -->
<section id="section-settings">
    <h2>Settings</h2>
	<div id="tabs-0">
    <h3>Language and basic settings</h3>
    <div>
    <span style="display:none"><label for="classroomid">Classroom identifier :</label><input type="text" name="classroomid" id="classroomid" value="<?php echo $this->escape($classroomid); ?>"></span>
<span style="display:none">Pending requests : <span id="nbrequests">0</span></span>
<span style="display:none"><label for="classroomlanguage">Classroom language :</label>
  <select id="classroomlanguage" onchange="setLanguageInput(this.value);">
  </select>
</span>
<span id="lilanguage"><label for="VoiceOutput">My language :</label>
  <select id="VoiceOutput" onchange="setLanguageOutputAndRetranslate(this.value);"  onmouseout="if (firsttime && firstclick) {document.getElementById('synthesison').checked=false; firsttime=false;}" onclick="if (firsttime) {document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='visible'; document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='hithere 0.5s 5'; firstclick=true;}">
  </select><span id="arrowlanguage">&nbsp;Choose your language first</span>
</span><br>
<span><label for="AuditorName">My surname :</label>
  <input type="text" id="AuditorName" onchange="if (this.value == 'Intervenant') this.value='Auditor';" value="<?php echo $this->escape($randomname); ?>" autocomplete="given-name">
</span><br>
<span id="lisynthese"><label for="synthesison">Speech synthesis active ? </label>
<input type="checkbox" id="synthesison" onclick="document.getElementById('arrowlanguage').style.visibility='hidden'; document.getElementById('arrowsynthesis').style.visibility='hidden';  document.getElementById('lilanguage').style.animation='none'; document.getElementById('lisynthese').style.animation='none'; firsttime=false;"><span id="arrowsynthesis">&nbsp; Then launch the speech synthesis</span>
</span>

</div>
	</div>
	<section id="section-chat">
    <h2>Classroom Chat</h2>
	<section id="tabs-1" role="region" aria-labelledby="chat-section-title">
    <div>
        <?php
        renderChatComponent([
            'roomId' => $classroomid,
            'roomToken' => $roomToken,
            'userName' => $randomname,
            'isIntervenant' => false,
            'placeholder' => 'Type your message here...',
            'labelText' => 'Classroom Chat'
        ]);
        ?>
    </div>
</section>

	<section id="section-notebook">
    <h2>Notebook</h2>
	<div id="tabs-2">
    
    <input style="margin-left: 64px;" type="button" value="Download" class="button-3 tooltip" title="Download the original speech in txt format." onclick="DownloadSpeechLesson('lesson_'+_('classroomid').value+'.txt', document.getElementById('notebook').innerHTML);"><br>
    <div id="notebook" style="border: solid 1px;"></div>

	</div>
	<section id="section-output">
    <h2>Output</h2>
	<div id="tabs-3">
  <input style="margin-left: 64px;" type="button" value="Download" class="button-3 tooltip" title="Download the translated speech in txt format." onclick="DownloadSpeechLesson('translated_lesson_'+_('classroomid').value+'.txt', document.getElementById('output').innerHTML);"><br>
    <div id="output">
    </div>
  </div>



    
	<!-- Word cloud section (commented out in original) -->

</form>
<div id="FS" onclick="GoFS();"  title="Toggle fullscreen mode"><i class='material-icons' style="font-size: 41px;" >fullscreen</i></div>

<dialog id="dialog-mail" style="width: 800px; height: 500px;"> 
  <form method="dialog">
    <fieldset>
      <legend style="display: none;">Send a mail</legend>
	<?php echo csrfField(); ?>
	<label for="dialog_MailTo">Mail to : </label><input type="text" id="dialog_MailTo" ><br>
	<label style="display:none;" for="dialog_MailFrom">Mail From : </label><input type="text" id="dialog_MailFrom" style="display:none;" readonly>
	<label for="dialog_MailSubject">Subject : </label><input type="text" id="dialog_MailSubject" readonly><br>
	Body : <br>
	<div id="dialog_MailBody">
	</div>
    <menu>
      <button type="submit" onclick="EnvoiMail(); return false;">Send</button>
      <button type="reset" onclick="document.getElementById('dialog-mail').close();">Cancel</button>
    </menu>
    </fieldset> 
  </form>
</dialog>

<dialog id="dialog-choose" style="min-width: 300px;"> 
  <form method="dialog">
  <?php echo csrfField(); ?> 
  <span><label for="VoiceOutputchoose">You first have to choose your language</label><br>
  <select id="VoiceOutputchoose">
  </select>
  </span>
  <menu>
    <button type="submit" id="dialog-choose-go">Go</button>
  </menu>
  </form>
</dialog>

<?php foreach ($pageScripts as $script): ?>
    <script src="<?php echo $this->escape($script); ?>"></script>
<?php endforeach; ?>

<?php foreach ($chatScripts as $script): ?>
    <script type="module" src="<?php echo $this->escape($script); ?>"></script>
<?php endforeach; ?>

<?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
    <script src="/js/tests/no-jquery-validation.js"></script>
<?php endif; ?>
</body>
</html>
