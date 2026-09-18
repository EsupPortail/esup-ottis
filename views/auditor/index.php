<!doctype html>
<html lang="fr">
<head>
	<title><?php echo $this->escape($pageTitle); ?></title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <link rel="icon" type="image/png" href="/images/favicon.svg" >
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <?php foreach ($pageStyles as $style): ?>
        <link href="<?php echo $this->escape($style); ?>" rel="stylesheet">
    <?php endforeach; ?>

    <script>
        window.pageData = <?php echo json_encode($jsVars); ?>;
        window.ROOM_TOKEN = '<?php echo htmlspecialchars($roomToken, ENT_QUOTES); ?>';
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>

</head>
<body>
<main>
<h1 style="display:none;">Outils de traduction : application étudiante</h1>
</main>
<div id="logonext"><img src="images/logo-next-omist.png" alt="Logo du projet d'Isite NExT" style="width: 109px; height: 50px;"></div>
<div id="repeat" title="Repeat the last sentence"><i class='material-icons'>replay</i><span style="font-size: 10px;color: white;"><br>REPEAT</span></div>

<div id="titre"><h1>Automatic translation provided by NExT OMIST Project</h1></div>

<div id="VOBanner"></div>
<div id="soustitre"></div>

<!-- Fixed title for display functions -->
<div id="fixed-title"></div>

<form id="myform" onsubmit="return false;">
<?php echo \App\Utils\Csrf::field(); ?>

<!-- Settings Section -->
<section id="section-settings">
    <h2>Settings</h2>
	<div id="tabs-0">
    <h3>Language and basic settings</h3>
    <div>
    <span style="display:none"><label for="classroomid">Classroom identifier :</label><input type="text" name="classroomid" id="classroomid" value="<?php echo $this->escape($classroomid); ?>"></span>
<span style="display:none">Pending requests : <span id="nbrequests">0</span></span>
<span style="display:none"><label for="classroomlanguage">Classroom language :</label>
  <select id="classroomlanguage">
  </select>
</span>
<span id="lilanguage"><label for="VoiceOutput">My language :</label>
  <select id="VoiceOutput">
  </select><span id="arrowlanguage">&nbsp;Choose your language first</span>
</span><br>
<span><label for="AuditorName">My surname :</label>
  <input type="text" id="AuditorName" onchange="if (this.value == 'Intervenant') this.value='Auditor';" value="<?php echo $this->escape($randomname); ?>" autocomplete="given-name">
</span><br>
<span id="lisynthese"><label for="synthesison">Speech synthesis active ? </label>
<input type="checkbox" id="synthesison">
<span id="arrowsynthesis">&nbsp; Then launch the speech synthesis</span>
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

    <input style="margin-left: 64px;" type="button" value="Download" class="button-3 tooltip" title="Download the original speech in txt format." id="download-notebook-btn"><br>
    <div id="notebook" style="border: solid 1px;"></div>

	</div>
	<section id="section-output">
    <h2>Output</h2>
	<div id="tabs-3">
  <input style="margin-left: 64px;" type="button" value="Download" class="button-3 tooltip" title="Download the translated speech in txt format." id="download-output-btn"><br>
    <div id="output">
    </div>
    <div id="notebookVO" style="display:none;"></div>
  </div>




	<!-- Word cloud section (commented out in original) -->

</form>
<div id="FS" title="Toggle fullscreen mode"><i class='material-icons' style="font-size: 41px;" >fullscreen</i></div>

<dialog id="dialog-mail" style="width: 800px; height: 500px;">
  <form method="dialog">
    <fieldset>
      <legend style="display: none;">Send a mail</legend>
	<?php echo \App\Utils\Csrf::field(); ?>
	<label for="dialog_MailTo">Mail to : </label><input type="text" id="dialog_MailTo" ><br>
	<label style="display:none;" for="dialog_MailFrom">Mail From : </label><input type="text" id="dialog_MailFrom" style="display:none;" readonly>
	<label for="dialog_MailSubject">Subject : </label><input type="text" id="dialog_MailSubject" readonly><br>
	Body : <br>
	<div id="dialog_MailBody">
	</div>
    <menu>
      <button type="submit" id="send-mail-btn">Send</button>
      <button type="reset" id="cancel-mail-btn">Cancel</button>
    </menu>
    </fieldset>
  </form>
</dialog>

<dialog id="dialog-choose">
  <form method="dialog">
  <?php echo \App\Utils\Csrf::field(); ?>
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
    <?php
    $isModule = (strpos($script, '/js/') !== false && strpos($script, 'analytics.js') === false && strpos($script, 'node_modules') === false);
    $typeAttr = $isModule ? 'type="module"' : '';
    ?>
    <script <?php echo $typeAttr; ?> src="<?php echo $this->escape($script); ?>"></script>
<?php endforeach; ?>

<?php foreach ($chatScripts as $script): ?>
    <script type="module" src="<?php echo $this->escape($script); ?>"></script>
<?php endforeach; ?>


<?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
    <script src="/js/tests/no-jquery-validation.js"></script>
<?php endif; ?>
</body>
</html>
