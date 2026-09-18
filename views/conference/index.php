<!doctype html>
<html lang="fr">
<head>
	<title><?php echo $this->escape($pageTitle); ?></title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link rel="icon" type="image/png" href="/images/favicon.svg" >

    <?php foreach ($pageStyles as $style): ?>
        <link href="<?php echo $this->escape($style); ?>" rel="stylesheet">
    <?php endforeach; ?>

    <script>
        window.pageData = <?php echo json_encode($jsVars); ?>;
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
</head>
<body>
<input type="hidden" id="classroomid" value="<?php echo $this->escape($classroomid); ?>">
<input type="hidden" id="appid" value="<?php echo $this->escape($appid); ?>">
<?php echo \App\Utils\Csrf::field(); ?>

<div id="conference-main" class="conference-grid">

    <!-- Language Section -->
    <section id="language-section" class="conference-card" aria-labelledby="language-section-title">
        <h2 id="language-section-title">Configuration linguistique</h2>
        <div>
            <ul class="no-bullets">
                <li>
                    <label for="classroomlanguage">Speaker language :</label>
                    <select id="classroomlanguage" class="select"></select>
                </li>
                <li class="hidden">
                    <label for="recognitionon">Speech recognition active ? </label>
                    <input type="checkbox" id="recognitionon" >
                </li>
                <li>&nbsp;</li>
                <li class="hidden">
                    Translate subtitles ?
                    <input type="checkbox" id="subtitleon">&nbsp;<span class="yesno" id="spansubtitleon">No.</span>
                </li>
                <li>
                    <label for="subtitlelanguage">Language for subtitles :</label>
                    <select id="subtitlelanguage" class="select"></select>
                </li>
                <li>
                    <label for="usetextband">Use partialtitre instead of textBand:</label>
                    <input type="checkbox" id="usetextband">
                </li>
            </ul>
        </div>
    </section>

    <!-- Chat Section -->
    <section id="chat-section" class="conference-card" aria-labelledby="chat-section-title">
        <h2 id="chat-section-title">Classroom Chat</h2>
        <div>
            <?php
            if (function_exists('renderChatComponent')) {
                renderChatComponent([
                    'roomId' => $classroomid,
                    'roomToken' => $roomToken,
                    'userName' => $uid,
                    'isIntervenant' => true,
                    'placeholder' => 'Type your message here...',
                    'labelText' => 'Classroom Chat'
                ]);
            }
	?>
        </div>
    </section>

    <!-- Notebook Section -->
    <section id="notebook-section" class="conference-card" aria-labelledby="notebook-section-title">
        <h2 id="notebook-section-title">Notebook</h2>
        <div id="notebooks_container">
            <label>NOTEBOOK</label>
            <div id="VO_container">
                <input type="button" id="buttondownloadspeechVO" value="Download the speech VO" class="button-3 tooltip" title="Download the notebook in txt format.">
                <br>
                <div id="notebookVO" contenteditable="true" aria-label="Complete speech here." title="Complete speech here."></div>
            </div>
            <div id="translated_container">
                <input type="button" id="buttondownloadspeech" value="Download the speech translated" class="button-3 tooltip" title="Download the notebook in txt format.">
                <br>
                <div id="notebook" contenteditable="true" aria-label="Complete speech here." title="Complete speech here."></div>
            </div>
        </div>
    </section>

    <div id="partialtitre" class="hidden" style="display: none;"></div>

    <h3 class="tabbed">For students</h3>
    <div>
        <ul class="no-bullets">
            <li><span id="connectedusers">connected users</span></li>
            <li>Students application : <a id="sharedlink" href="/auditor?ROOMID=<?php echo $this->escape($classroomid); ?>">/auditor?ROOMID=<?php echo $this->escape($classroomid); ?></a></li>
            <li>QR code :
                <div title="" id="sharedqr"></div>
            </li>
        </ul>
    </div>

    <!-- Presentation Upload -->
    <div>
        <label for="presentation_file">Upload Presentation:</label>
        <input type="file" id="presentation_file" name="presentation_file"
               accept=".pptx,.odp,.pdf,.txt,.html,.htm"
               aria-label="Upload presentation file"
               title="Upload PPTX, ODP, PDF, TXT, or HTML file">

        <progress id="presentation-progress" value="0" max="100" aria-label="Upload and conversion progress"></progress>
        <span id="progress-status" aria-live="polite"></span>

        <div id="pdf-viewer">
            <canvas id="pdf-canvas"></canvas>
            <div id="pdf-control">
                <div>
                    <button id="pdf-prev" disabled aria-label="Previous page" title="Go to previous page">Previous</button>
                    <span id="pdf-page-indicator">Page 1 of 1</span>
                    <button id="pdf-next" disabled aria-label="Next page" title="Go to next page">Next</button>
                    <input type="number" id="pdf-page-input" min="1" aria-label="Go to page number">
                    <button id="pdf-fullscreen-btn" aria-label="Toggle fullscreen mode" title="Press ESC to exit fullscreen">Fullscreen</button>
                </div>
                <div id="textBand" class="text-band-style" style="display: block; visibility: visible;"></div>
            </div>
        </div>

        <div id="text-content-viewer"></div>
    </div>

    <div id="textfixe" class="text-band-style" style="display: block;"></div>

    <!-- Admin Configuration -->
    <div id="admin-config">
        <h2>ADMIN - Configuration Traduction</h2>
        <label for="whichtranslator">Choix traducteur :</label>
        <select id="whichtranslator" class="select">
            <option value="" selected>Default</option>
            <option value="DEEPLPRO">DeepL Pro API</option>
            <option value="DEEPLFREE">DeepL Free API</option>
            <option value="LIBRETRANSLATE">Libre Translate</option>
        </select>
    </div>

    <!-- Microphone Button -->
    <div id="micon" title="Activer/Désactiver le microphone. Alternatives : barre d'espace, bouton rouge de la télécommande smartphone ou bouton du pointeur de présentation.">
        <i class='material-icons'>voice_over_off</i>
    </div>
</div>

<!-- Conference-specific scripts -->
<?php foreach ($pageScripts as $script): ?>
    <?php
    echo "<!-- [TEMPLATE] Chargement: $script -->\n";
    $isModule = (strpos($script, '/js/') !== false && strpos($script, 'analytics.js') === false && strpos($script, 'node_modules') === false);
    $typeAttr = $isModule ? 'type="module"' : '';
    echo "<!-- [TEMPLATE] Type: $typeAttr -->\n";
    ?>
    <script <?php echo $typeAttr; ?> src="<?php echo $this->escape($script); ?>"></script>
<?php endforeach; ?>
<!-- DOM and Network utilities - must load FIRST -->
<script type="module" src="/js/lib/dom/selector.js"></script>
<script type="module" src="/js/lib/dom/styles.js"></script>
<script type="module" src="/js/lib/dom/visibility.js"></script>
<script type="module" src="/js/lib/dom/layout.js"></script>
<script type="module" src="/js/lib/dom/events.js"></script>
<script type="module" src="/js/lib/dom/dialog.js"></script>
<script type="module" src="/js/lib/network/utils.js"></script>
<script type="module" src="/js/lib/network/xhr.js"></script>
<script type="module" src="/js/lib/network/fetch.js"></script>
<script type="module" src="/js/lib/network/counter.js"></script>
<!-- Language utilities -->
<script type="module" src="/js/lib/language/state.js"></script>
<script type="module" src="/js/lib/language/management.js"></script>
<!-- Chat utilities -->
<script type="module" src="/js/lib/chat/state.js"></script>
<script type="module" src="/js/lib/chat/utils.js"></script>
<!-- Charger les autres dépendances -->
<script type="module" src="/js/lib/utils/cleanup.js"></script>
<script type="module" src="/js/lib/index.js"></script>
<script type="module" src="/js/lib/chat/alert.js"></script>
<script type="module" src="/js/lib/chat/network.js"></script>
<script type="module" src="/js/lib/chat/main.js"></script>
<script type="module" src="/js/lib/chat/handlers.js"></script>
<script type="module" src="/js/lib/chat/display.js"></script>
<script type="module" src="/js/lib/conference/index.js"></script>
<!-- Puis le script d'initialisation -->
<script type="module" src="/js/conference-init.js"></script>

<?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
    <script src="/js/tests/no-jquery-validation.js"></script>
<?php endif; ?>
</body>
</html>
