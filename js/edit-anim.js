const list_keywords = ["[wait ","[mouse go ","[mouse size ","[mouse shape ","[resource "];

// Close an open dropdown menu if the user clicks outside of it
window.onclick = function(e) {
    if (!e.target.matches('.dropbtn')) {
      var dropdowns = document.getElementsByClassName("dropdown-content");
      for (var d = 0; d < dropdowns.length; d++) {
        var openDropdown = dropdowns[d];
        if (openDropdown.classList.contains('show')) {
          openDropdown.classList.remove('show');
        }
      }
    }
  }

/**
 * @summary Switch the fullscreen mode on and off.
 * 
 */

function GoFSEditAnim() {
    var elem = document.getElementById("tool12");
    if (!document.fullscreenElement) {
        elem.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
}

/**
 * @summary The function that is executed for getting the device pixel ratio. Used to position the mouse for animation.
 * 
 */

function getDevicePixelRatio() {
    var mediaQuery;
    var is_firefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
    if (window.devicePixelRatio !== undefined && !is_firefox) {
        return window.devicePixelRatio;
    } else {
        return 1;
    }
}

/**
 * @summary The function that is executed for a preview of the mouse size.
 * 
 * @param {int} mousesize the size of the mouse.
 */

function displayMouseSize(mousesize){
    document.getElementById("showMouseSize").style.transform='scale('+mousesize+') rotate(0deg)';
}

/**
 * @summary The function that is executed for displaying  a cursor that follows the mouse.
 * 
 */

function showMouse() {
    var mousesize = 1;
    var mouseangle = 0;
    document.getElementById('mouse').style.transform='scale('+mousesize+') rotate('+mouseangle+'deg)';
    document.getElementById('mouse').removeAttribute("hidden"); 
}

/**
 * @summary The function that is executed for getting the mouse position relative to the image (in percent).
 * 
 * @param {event} e a mouse event.
 */

function followmouse(e) {
    var id_image = document.getElementById("imageArea").firstElementChild.id;
    var c=document.getElementById(id_image);
    let rect = c.getBoundingClientRect();
    var pr=getDevicePixelRatio();

    var x_offset = e.offsetX;
    var y_offset = e.offsetY;
    var pourcx=Math.floor((e.offsetX)/(rect.width)*100);
    var pourcy=Math.floor((e.offsetY)/(rect.height)*100);

    var tabs_top = document.getElementById("tabs").offsetTop;
    var menu_top = document.getElementById("menu").offsetTop;
    var tool_top = document.getElementById("tool12").offsetTop;
    var block_top = document.getElementById("block12").offsetTop;
    var table_top = document.getElementById("tableEdit").offsetTop;

    var elem = document.getElementById("tool12");
    if (!document.fullscreenElement) {
        var gap_mouse_y = 12;
        var gap_mouse_x = 12;
        var x = e.pageX - gap_mouse_x;
        var y = e.pageY-tabs_top-menu_top + gap_mouse_y;
    }else{
        var gap_mouse_y = 30;
        var gap_mouse_x = 4;
        var x = e.pageX - gap_mouse_x;
        var y = e.pageY - gap_mouse_y;
    }

    document.getElementById("coord_x").innerHTML = pourcx;
    document.getElementById("coord_y").innerHTML = pourcy;

    showMouse();
	document.getElementById('mouse').style.top=y+"px"
	document.getElementById('mouse').style.left=x+"px";
}

/**
 * @summary The function that is executed for getting the mouse position for the keyword "mouse go".
 * 
 */

function getMousePosition(){
    var id_image = document.getElementById("imageArea").firstElementChild.id;
    var img=document.getElementById(id_image);
    document.getElementById("mouse-position").innerHTML = '<p id="instruction">Click on the slide image to get the mouse coordinates.</p>'+
    '<p>X coordinate : <span id="coord_x"></span></p>'+ '<p>Y coordinate : <span id="coord_y"></span></p>';
    img.setAttribute('onclick','followmouse(event); document.getElementById("instruction").innerHTML = "Move the mouse to : ";');

    $( "#mouse-position" ).dialog({
        height: 'auto',
        width: 'auto',
        modal: false,
        appendTo: "#DesiredDivID",
        buttons: {
          OK: function() {
            $( this ).dialog( "close" );
            var coord_x = document.getElementById("coord_x").innerHTML;
            var coord_x_clean = coord_x.replace(/\D/g,'');
            var coord_y = document.getElementById("coord_y").innerHTML;
            var coord_y_clean = coord_y.replace(/\D/g,'');
            updateCommand('[mouse go toReplace]', coord_x_clean+" "+coord_y_clean);
            addAllLanguages(document.getElementById("commandKeyword").innerHTML);
            document.getElementById('mouse').setAttribute("hidden", true);
            document.getElementById(id_image).removeAttribute("onclick");
          }
        },
        close: function( event, ui ) {
            document.getElementById('mouse').setAttribute("hidden", true);
        },
    });
}

/**
 * @summary The function that is executed for displaying help.
 * 
 */

function toolInformation(){
    $( "#dialog-message" ).dialog({
        height: 'auto',
        width: 'auto',
        modal: true,
        appendTo: "#DesiredDivID"
    }); 
}

/**
 * @summary The function that is executed to warn when a file is missing.
 * 
 * @param {string} warning_type a text value contained the missing file type.
 */

function warningEmpty(warning_type){
    if (warning_type === "file"){
        document.getElementById("dialog-warning").innerHTML = "You must provide a file first !";
    }else if (warning_type === "slide"){
        document.getElementById("dialog-warning").innerHTML = "You must select a slide first !";
    }

    $( "#dialog-warning" ).dialog({
        height: 80,
        width: 235,
        modal: true,
        appendTo: "#DesiredDivID"
    }); 
}

/**
 * @summary The function that is executed for initializing the animation editor when the user loads a file.
 * 
 */

function initializeEditAnim(){
    document.getElementById('imageArea').innerHTML = "";
    document.getElementById("speechArea").value = "";
    document.getElementById('tableEdit').removeAttribute('hidden');
}

/**
 * @summary The function that is executed for obtaining the list of languages used in the capsule.
 * 
 * @returns a table with all the languages used.
 */

function getListLanguages(){
    var tab_lang = [];
    var languages = document.getElementById("myLanguage");
    for (num_lang = 0; num_lang < languages.length; num_lang++) {
        tab_lang[num_lang] = languages.options[num_lang].value;
    }
    return tab_lang
}

/**
 * @summary The function used for obtaining the notes of a slide in the given language.
 * 
 * @param {string} target_language the target language.
 * @param {string} numSlide the slide number.
 * @returns an array of strings with the language slide text.
 */

function getNotesLanguages(target_language, numSlide){
    var notes = document.getElementById('SlideText_'+target_language+'_img_'+numSlide).textContent;
    var val_notes = notes.split('\n');
    return val_notes;
}

/**
 * @summary The function that is used for replacing the text for a given language and slide.
 * 
 * @param {array} val_notes an array of strings with the language slide text.
 * @param {string} target_language the target language.
 * @param {string} numSlide the slide number.
 * @returns the new text.
 */

function replaceNotes(val_notes, target_language, numSlide){
    var new_notes = val_notes.join('\n');
    document.getElementById('SlideText_'+target_language+'_img_'+numSlide).textContent = new_notes;
    return new_notes;
}

/**
 * @summary The function that is used for getting the position of the cursor in the text zone.
 * 
 * @param {object} textArea the text zone element.
 * @returns an integer which represents the line on with the cursor.
 */

function getCursorPosition(textarea){
    var cursorPos = $('#speechArea').prop('selectionStart');
    var textBeforeCursor = textarea.value.substr(0, textarea.selectionStart).split("\n");
    document.getElementById('cursor-position').innerHTML = textBeforeCursor.length;
    var pos = document.getElementById('cursor-position').innerHTML;
    return [pos, textBeforeCursor]
}

/**
 * @summary The function that is used for getting all the keyword locations in the text.
 * 
 * @param {string} searchStr the keyword to search for.
 * @param {string} str the text in which to search.
 * @param {boolean} caseSensitive case sensitive (true) or not (false).
 * @returns an array with all the location indices for the keyword.
 */

function getIndicesOf(searchStr, str, caseSensitive) {
    var searchStrLen = searchStr.length;
    if (searchStrLen == 0) {
        return [];
    }
    var startIndex = 0, index, indices = [];
    if (!caseSensitive) {
        str = str.toLowerCase();
        searchStr = searchStr.toLowerCase();
    }
    while ((index = str.indexOf(searchStr, startIndex)) > -1) {
        indices.push(index);
        startIndex = index + searchStrLen;
    }
    return indices;
}

//Pour afficher les numéros des lignes
// document.addEventListener('DOMContentLoaded', () => {
//     const textarea = document.getElementById('speechArea');
//     const lineNumbersEle = document.getElementById('line-numbers');

//     const textareaStyles = window.getComputedStyle(textarea);
//     [
//         'fontFamily',
//         'fontSize',
//         'fontWeight',
//         'letterSpacing',
//         'lineHeight',
//         'padding',
//     ].forEach((property) => {
//         lineNumbersEle.style[property] = textareaStyles[property];
//     });

//     const parseValue = (v) => v.endsWith('px') ? parseInt(v.slice(0, -2), 10) : 0;

//     const font = `${textareaStyles.fontSize} ${textareaStyles.fontFamily}`;
//     const paddingLeft = parseValue(textareaStyles.paddingLeft);
//     const paddingRight = parseValue(textareaStyles.paddingRight);

//     const canvas = document.createElement('canvas');
//     const context = canvas.getContext('2d');
//     context.font = font;

//     const calculateNumLines = (str) => {
//         const textareaWidth = textarea.getBoundingClientRect().width - paddingLeft - paddingRight;
//         const words = str.split(' ');
//         let lineCount = 0;
//         let currentLine = '';
//         for (let i = 0; i < words.length; i++) {
//             const wordWidth = context.measureText(words[i] + ' ').width;
//             const lineWidth = context.measureText(currentLine).width;

//             if (lineWidth + wordWidth > textareaWidth) {
//                 lineCount++;
//                 currentLine = words[i] + ' ';
//             } else {
//                 currentLine += words[i] + ' ';
//             }
//         }

//         if (currentLine.trim() !== '') {
//             lineCount++;
//         }

//         return lineCount;
//     };

//     const calculateLineNumbers = () => {
//         const lines = textarea.value.split('\n');
//         const numLines = lines.map((line) => calculateNumLines(line));

//         let lineNumbers = [];
//         let i = 1;
//         while (numLines.length > 0) {
//             const numLinesOfSentence = numLines.shift();
//             lineNumbers.push(i);
//             if (numLinesOfSentence > 1) {
//                 Array(numLinesOfSentence - 1)
//                     .fill('')
//                     .forEach((_) => lineNumbers.push(''));
//             }
//             i++;
//         }

//         return lineNumbers;
//     };

//     const displayLineNumbers = () => {
//         const lineNumbers = calculateLineNumbers();
//         lineNumbersEle.innerHTML = Array.from({
//             length: lineNumbers.length
//         }, (_, i) => `<div>${lineNumbers[i] || '&nbsp;'}</div>`).join('');
//     };

//     textarea.addEventListener('input', () => {
//         displayLineNumbers();
//     });

//     displayLineNumbers();

//     const ro = new ResizeObserver(() => {
//         const rect = textarea.getBoundingClientRect();
//         lineNumbersEle.style.height = `${rect.height}px`;
//         displayLineNumbers();
//     });
//     ro.observe(textarea);

//     textarea.addEventListener('scroll', () => {
//         lineNumbersEle.scrollTop = textarea.scrollTop;
//     });
// });

/**
 * @summary The function that is executed for updating the speech in a particular language when the user changes it.
 * 
 */

function updateNotes(){
    var lang = _("myLanguage").value.trim();
    var idImage = document.getElementById("imageArea").firstElementChild.id;
    var numSlide = idImage.replace('slide_img_', '');
    var new_txt = document.getElementById("speechArea").value;
    var tab_lang = getListLanguages();

    for (let keyword in list_keywords){
        if (new_txt.includes(list_keywords[keyword])){
            var indices = getIndicesOf(list_keywords[keyword], new_txt, true);
            for (var i = 0; i < indices.length; i++) {
                var new_txt_before = new_txt.substring(0,indices[i]).split("\n");
                var new_txt_compare = new_txt.substring(0).split("\n");
                var old_txt = document.getElementById('SlideText_'+lang+'_img_'+numSlide).textContent;
                var old_txt_compare = old_txt.substring(0).split("\n");
                var nb_line = new_txt_before.length;

                //On regarde s'il y a eu une modification dans les paramètres des mots-clés
                if (new_txt_compare[nb_line-1] != old_txt_compare[nb_line-1]){
                    //S'il y a une différence, on modifie le paramètre dans toutes les langues
                    for (let key in tab_lang){
                        var val_notes = getNotesLanguages(tab_lang[key], numSlide);
                        val_notes.splice(nb_line-1, 1, new_txt_compare[nb_line-1]);
                        var new_notes = val_notes.join('\n');
                        document.getElementById('SlideText_'+tab_lang[key]+'_img_'+numSlide).textContent = new_notes;
                    }
                }
            }
        }
    }
    document.getElementById('SlideText_'+lang+'_img_'+numSlide).textContent = document.getElementById("speechArea").value;
}

/**
 * @summary The function that is executed for retrieving changes in the speech area. If a line is deleted, we check that it is empty in all languages. If a line is added, it is added in all languages.
 * 
 * @param {event} event a keyboard event.
 */

function getEvent(event){
    const objtext = document.getElementById("speechArea");
    var idImage = document.getElementById("imageArea").firstElementChild.id;
    var numSlide = idImage.replace('slide_img_', '');
    var tab_lang = getListLanguages();


    $('#speechArea').bind("cut paste",function(e) {
        e.preventDefault();
        // alert("TEST");
        document.getElementById("dialog-warning").innerHTML = "Warning ! You cannot cut or paste text.";
        $( "#dialog-warning" ).dialog({
            modal: true,
            appendTo: "#DesiredDivID",
            buttons: {
                OK : function(){
                    $( this ).dialog( "close" );
                },
            }
        })
    });

    if (event.code === 'Backspace'){
        var [line, textBeforeCursor] = getCursorPosition(objtext);
        var cursorPositionLine = textBeforeCursor[line-1].length;
        const lines = event.target.value.split('\n').length;
        var lang = _("myLanguage").value.trim();
        var val_notes_lang = getNotesLanguages(lang, numSlide);
        const lines_lang = val_notes_lang.length;
        var [pos, textBeforeCursor] = getCursorPosition(objtext);
        if (lines < lines_lang){
            warningDelete(objtext, pos, lang, numSlide);
        }
    }

    if (event.code === 'Enter'){
        var textarea = document.getElementById('speechArea');
        var [line, textBeforeCursor] = getCursorPosition(textarea);

        var idImage = document.getElementById("imageArea").firstElementChild.id;
        var numSlide = idImage.replace('slide_img_', '');
        var tab_lang = getListLanguages();
        var notes_input = textarea.value;
        var val_notes_input = notes_input.split('\n');

        if (val_notes_input[line-1] != ""){
            line = line-1;            
        }

        for (let key in tab_lang){
            var val_notes = getNotesLanguages(tab_lang[key], numSlide);
            val_notes.splice(line-1, 0, '');
            var new_notes = replaceNotes(val_notes, tab_lang[key], numSlide);
            $('speechArea').val(val_notes.join('\n'));
        }
    }
}

/**
 * @summary The function that is executed for warning that a non-empty line in a language is about to be deleted.
 * 
 * @param {object} objtext the "speechArea" element.
 * @param {int} pos the line with the cursor.
 * @param {string} lang the language in which the empty line is deleted.
 * @param {string} numSlide the slide number.
 */

function warningDelete(objtext, pos, lang, numSlide){
    document.getElementById("dialog-warning").innerHTML = "Warning ! You cannot delete a line, but it can remain empty. If you delete text on a line, remember to do so for each language.";
    $( "#dialog-warning" ).dialog({
        modal: true,
        appendTo: "#DesiredDivID",
        buttons: {
            OK : function(){
                var current_lang_notes = objtext.value.split("\n")
                current_lang_notes.splice(pos, 0, '');
                var lang_new_notes = current_lang_notes.join('\n');
                document.getElementById('speechArea').value = lang_new_notes;
                document.getElementById('SlideText_'+lang+'_img_'+numSlide).textContent = lang_new_notes;

                $( this ).dialog( "close" );
            }     
        }
    })
}

/**
 * @summary The function that is executed for adding a keyword to the displayed notes. If the selected line is empty in all languages, the keyword is added to the empty line. If the chosen line is only empty for the chosen language, add an empty line before it. If you are at the beginning of the line, add the keyword above it. Otherwise, add the keyword to the line.
 * 
 * @param {string} keyword the selected keyword and its parameter.
 */

function addKeyword(keyword){
    var textarea = document.getElementById('speechArea');
    var [line, textBeforeCursor] = getCursorPosition(textarea);
    var cursorPositionLine = textBeforeCursor[line-1].length;
    var notes = textarea.value;
    var val_notes = notes.split('\n');
    var delete_ligne = false;

    if (val_notes[line-1] === ""){
        var tab_lang = getListLanguages();
        var idImage = document.getElementById("imageArea").firstElementChild.id;
        var numSlide = idImage.replace('slide_img_', '');
        var count_empty = 0;
        for (let key in tab_lang){
            var val_notes_lang = getNotesLanguages(tab_lang[key], numSlide);
            var line = document.getElementById('cursor-position').innerHTML;

            if (val_notes_lang[line-1] != ""){
                count_empty = count_empty + 1;
            };
        };

        if (count_empty > 0){
            line = line-1;
            val_notes.splice(line, 1, keyword);
            val_notes.splice(line, 0, '');
        }else{
            line = line-1;
            val_notes.splice(line, 1, keyword);
        }

    }else if (cursorPositionLine === 0){
        document.getElementById('cursor-position').innerHTML = document.getElementById('cursor-position').innerHTML - 1;
        line = line-1;
        val_notes.splice(line, 0, keyword);
    }else{
        val_notes.splice(line, 0, keyword);
    }

    var new_notes = val_notes.join('\n');
    document.getElementById("speechArea").value = new_notes;
}

/**
 * @summary the function that is used for adding the text of the keyword ‘notification’ translated into all languages.
 * 
 * @param {string} langToTranslate an array of languages into which the message has been translated.
 * @param {string} message_translated an array with the message translated into all the languages required.
 * @param {string} numSlide the slide number.
 * @param {string} lang the original language of the message.
 */

function notificationAllLanguages(langToTranslate, message_translated, numSlide, lang){
    message_translated = message_translated.split(",");
    var num_lang = 0;
    for (let message_lang in message_translated){
        message_translated[message_lang] = message_translated[message_lang]
            .replaceAll("'", "")
            .replaceAll("[", "")
            .replaceAll("]", "");
        message_translated[message_lang] = message_translated[message_lang].trim();

        var new_keyword = "[notification "+message_translated[message_lang]+"]";
        var val_notes = getNotesLanguages(langToTranslate[num_lang], numSlide);
        var line = document.getElementById('cursor-position').innerHTML;

        if (val_notes[line-1] === ""){
            line = line-1;
            val_notes.splice(line, 1, new_keyword);
        }else{
            val_notes.splice(line, 0, new_keyword);
        }
        var new_notes = replaceNotes(val_notes, langToTranslate[num_lang], numSlide);
        num_lang = num_lang+1;
    }
    $('speechArea').val(val_notes.join('\n'));
    document.getElementById('SlideText_'+lang+'_img_'+numSlide).textContent = document.getElementById("speechArea").value;
}

/**
 * @summary The function that is executed for adding a keyword to notes for one or all languages.
 * 
 * @param {string} keyword the selected keyword and its parameter.
 */

function addAllLanguages(keyword){
    _('dialog-all-lang').innerHTML = "Would you like to add the keyword "+keyword+" to your animation ? </br> Please note that this keyword will be added to all languages."
    $( "#dialog-all-lang" ).dialog({
        height: 'auto',
        width: 520,
        modal: true,
        appendTo: "#DesiredDivID",
        buttons: {
            Ok: function() {
                var lang = _("myLanguage").value.trim();
                var idImage = document.getElementById("imageArea").firstElementChild.id;
                var numSlide = idImage.replace('slide_img_', '');
                var tab_lang = getListLanguages();
                var index = tab_lang.indexOf(lang);

                addKeyword(keyword);
                if (keyword.includes('[notification')){
                    var textToTranslate = keyword.replace('[notification ','');
                    textToTranslate = textToTranslate.slice(0, -1);
                    if (index > -1) {
                        var langToTranslate = tab_lang;
                        langToTranslate.splice(index, 1);
                    }
                    var xhr = new XMLHttpRequest();
                    var formdata = new FormData();
                    var fromLanguage = lang.substring(0,2);
                    formdata.append("text", textToTranslate);
                    formdata.append("from", fromLanguage);
                    formdata.append("to", langToTranslate);

                    xhr.open("POST", "EditAnim/TranslateNotification.php");
                    xhr.send(formdata);
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            var message_translated = xhr.responseText;
                            notificationAllLanguages(langToTranslate, message_translated, numSlide, lang);
                            } else {
                            console.log(`Error: ${xhr.status}`);
                            }
                    };
                }else{
                    for (let key in tab_lang){
                        var val_notes = getNotesLanguages(tab_lang[key], numSlide);
                        var line = document.getElementById('cursor-position').innerHTML;

                        if (val_notes[line-1] === ""){
                            line = line-1;
                            val_notes.splice(line, 1, keyword);
                        }else{
                            val_notes.splice(line, 0, keyword);
                        }

                        var new_notes = replaceNotes(val_notes, tab_lang[key], numSlide);
                        $('speechArea').val(val_notes.join('\n'));
                        document.getElementById('SlideText_'+tab_lang[index]+'_img_'+numSlide).textContent = document.getElementById("speechArea").value;
                    }
                }
                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });
}

/**
 * @summary The function that is executed for creating the command with the keyword and its parameters.
 * 
 * @param {string} keyword the keyword selected.
 * @param {string} param the new parameter value.
 */

function updateCommand(keyword, param){
    var parameter = "";
    if(typeof(param) === 'string') {
        parameter = param;
    } else {
        parameter = param.toString();
    }
    command = keyword.replace("toReplace", parameter);
    document.getElementById("commandKeyword").innerHTML = command;
}

/**
 * @summary The function that is executed for for closing open dropdowns that do not match the selected dropdown.
 * 
 * @param {string} id_select the dropdown selected.
 */

function closeDropdown(id_select){
    var dropdowns = document.getElementsByClassName("dropdown-content");
    for (var d = 0; d < dropdowns.length; d++) {
      var openDropdown = dropdowns[d];
      if ((id_select != openDropdown.id) && (openDropdown.classList.contains('show'))){
        openDropdown.classList.remove('show');
      }
    }
}

/**
 * @summary The function that is executed for adding parameters to an animation's keyword.
 * 
 * @param {string} keywordToAdd the keyword selected.
 */

function addParameters(keywordToAdd){
    if (keywordToAdd=="[wait toReplace]"){
        document.getElementById("commandKeyword").innerHTML = "[wait 0]"
        document.getElementById("keywordParameter").innerHTML ='<div id="paramWait" name="param">Pause for <input onchange="updateCommand(\'[wait toReplace]\', document.getElementById(\'timeWait\').value);" type="number" style="width:5em" min="0" value="0" name="timeWait" id="timeWait"/> milliseconds </div>';
    }else if(keywordToAdd=="[mouse size toReplace]"){
        document.getElementById("commandKeyword").innerHTML = "[mouse size 1]"
        document.getElementById("keywordParameter").innerHTML ='<div id="paramMouseSize" name="param">Mouse size : <input onchange="updateCommand(\'[mouse size toReplace]\', document.getElementById(\'mouseSize\').value);" type="range" style="width:100px" value="1" min="1" max="10" step="1" oninput="sizeOutputId.value = mouseSize.value; displayMouseSize(mouseSize.value);" name="mouseSize" id="mouseSize"/><output id="sizeOutputId">1</output></div>'+
        '<br><div id="showMouseSize">+</div></div>';
    }else if(keywordToAdd=="[mouse shape toReplace]"){
        document.getElementById("commandKeyword").innerHTML = "[mouse shape 0]"
        document.getElementById("keywordParameter").innerHTML ='<div id="paramMouseShape" name="param">Mouse shape : <select onchange="updateCommand(\'[mouse shape toReplace]\', document.getElementById(\'mouseShape\').value);" id="mouseShape">'+
        '<option value="0">+</option><option value="1">X</option><option value="2">☝</option><option value="3">○</option><option value="4">⚫</option><option value="5">↖</option></select></div>';
    }else if(keywordToAdd=="[mouse go px py]"){
        document.getElementById("commandKeyword").innerHTML = "[mouse go 0 0]"
        document.getElementById("keywordParameter").innerHTML ='<div><input type="radio" id="paramMouseGo" name="param" min="0" value="[mouse go px py]"/>Mouse size : <input onchange="updateCommand(document.getElementById(\'paramMouseSize\').value);" type="number" style="width:2em" value="0" name="mouseGo" id="mouseGo"/></div>';
    }else if(keywordToAdd=="[resource toReplace]"){
        document.getElementById("commandKeyword").innerHTML = "[resource ]"
        document.getElementById("keywordParameter").innerHTML ='<div id="paramUrl" name="param">Open a window with the url <input onchange="updateCommand(\'[resource toReplace]\', document.getElementById(\'notifUrl\').value)" placeholder="Copy your URL here" type="text" name="notifUrl" id="notifUrl"/></div>';
    }else if(keywordToAdd=="[notification toReplace]"){
        document.getElementById("commandKeyword").innerHTML = "[notification ]"
        document.getElementById("keywordParameter").innerHTML ='<div id="paramMessage" name="param">Open a window with the message <input onchange="updateCommand(\'[notification toReplace]\', document.getElementById(\'notifMessage\').value)" placeholder="Copy your message here" type="text" name="notifMessage" id="notifMessage"/></div>';
    }
 
    $( "#keywordParameter" ).dialog({
        height: 'auto',
        width: 'auto',
        minWidth : 250,
        minHeight : 215,
        modal: true,
        appendTo: "#DesiredDivID",
        buttons: {
          OK: function() {
            $( this ).dialog( "close" );
            addAllLanguages(document.getElementById("commandKeyword").innerHTML);
          }
        }
    });
}

/**
 * @summary The function that is executed for displaying the dialog box corresponding to the option the user wishes to modify.
 * 
 * @param {string} keywordToAdd the option selected.
 */

function addOptions(keywordToAdd){
    if (keywordToAdd=="[ratio]"){
        $( "#dialog-ratio" ).dialog({
            height: 'auto',
            width: 'auto',
            modal: true,
            appendTo: "#DesiredDivID",
            buttons: {
              OK: function() {
                _("ratio-select").innerHTML = _("aspectratioEditAnim").value;
                _("keepratio-select").innerHTML = _("keepaspectratioEditAnim").value;
                $( this ).dialog( "close" );
              }
            },
            close: function( event, ui ) {
                _("aspectratioEditAnim").value = _("ratio-select").innerHTML;
                _("keepaspectratioEditAnim").value = _("keepratio-select").innerHTML;
                if (_("keepratio-select").innerHTML === "X") {
                    document.getElementById("check-keep-ratio").checked = true; 
                    document.getElementById('cbtextEditAnim').innerHTML ='Yes !';
                } else {
                    document.getElementById('check-keep-ratio').checked = false;
                    document.getElementById('cbtextEditAnim').innerHTML ='No.'; 
                }

            }
        });
    }else if(keywordToAdd=="[delay]"){
        $( "#dialog-delay" ).dialog({
            height: 'auto',
            width: 'auto',
            modal: true,
            appendTo: "#DesiredDivID",
            buttons: {
              OK: function() {
                _("delay-select").innerHTML = _("extradelayEditAnim").value;
                $( this ).dialog( "close" );
              }
            },
            close: function( event, ui ) {
                _("extradelayEditAnim").value = _("delay-select").innerHTML;
            }
            
        });
    }else if(keywordToAdd=="[licence]"){
        $( "#dialog-licence" ).dialog({
            height: 'auto',
            width: 'auto',
            modal: true,
            appendTo: "#DesiredDivID",
            buttons: {
              OK: function() {
                _("licence-select").innerHTML = _("licenceEditAnim").value;
                $( this ).dialog( "close" );
              }
            },
            close: function( event, ui ) {
                _("licenceEditAnim").value = _("licence-select").innerHTML;
            }
        });
    }
}

/**
 * @summary The function that is executed for displaying notes in the speech area for slides in a specific language.
 * 
 */

function notesSlideLang(){
    var idImage = document.getElementById("imageArea").firstElementChild.id;
    var numSlide = idImage.replace('slide_img_', '');
    var lang = _("myLanguage").value.trim();
    document.getElementById("speechArea").value = document.getElementById('SlideText_'+lang+'_img_'+numSlide).textContent;
}

/**
 * @summary The function that is executed for displaying a larger image.
 * 
 * @param {object} img an image to be displayed in larger size.
 */

function showSlideImage(img){
    var imgToDisplay = "";
    var lang = _("myLanguage").value.trim();
    imgToDisplay = '<img id="slide_' +img.id + '" src="'+img.src+'" />';
    document.getElementById("imageArea").innerHTML = imgToDisplay;
    document.getElementById("speechArea").value = document.getElementById('SlideText_'+lang+'_'+img.id).textContent;
}

/**
 * @summary a function that is fired when a new lesson is loaded to prepare all the data structures.
 * 
 * @param {string} datatxt a text value contained the text of the speech and a b64 version of the images.
 * @param {boolean} editable a boolean to edit note content.
 *  
 * @returns an HTML encoding of what should be shown in the complete lesson tab.
 */

function formateresponseTools(datatxt,editable=true) {
    currentPresentation = [];
    var res = '';
    var data = JSON.parse(datatxt);
    console.log(data);
    
    var selectedLang = '';

    selectedLang = '<select onchange="notesSlideLang();" id="myLanguage" class="inputsmallscreens" style="vertical-align: top;"> \n';
    for (let key in data) {
        if (key!="Img_slide") {
            data_key = data[key];
            selectedLang += '<option value="'+key+'">'+key+'</option> \n';
            nb_slides = data_key.length;
        }
    }
    selectedLang += '</select>';

    _("speechLanguage").innerHTML = selectedLang;

    for (var numero_slide=1; numero_slide <= nb_slides; numero_slide++){
        res += '<dl>';
        res = res + '<dt style="font-weight : bold">&nbsp;Slide #' + numero_slide + '<dd>&nbsp;' + '<div class="myImage"><img id="img_' +numero_slide + '"  class="img-responsive" onclick="showSlideImage(this)" role="button" alt="Slide '+numero_slide+'" width="100" src=""/></div></dd></dt>\n';
        for (let key in data) {
            if (key!="Img_slide") {
                data_key = data[key];
                slide = data_key[numero_slide-1];
                num_slide = numero_slide-1;
                res = res +'<span hidden id="SlideText_'+key+'_img_'+numero_slide+'">';
                for (numero_notes=0; numero_notes<slide["Notes"].length; numero_notes++){
                    num_slide = numero_slide-1;
                    res = res + slide["Notes"][numero_notes]+'\n';
                }
                res += '</span>'+'\n';

            }else{
                data_key = data[key];
                var toexec = "";
                for (var j = 1; j <= data_key["Images"].length; j++) {
                    toexec += "_('img_" + j + "').src='" + data_key["Images"][j - 1] + "'; ";
                }
                setTimeout(toexec, 10);
            }
        }
        res += '</dl>';
    }
    console.log("DEBUG : " + res);
    return res;
}

/**
 * @summary A function that is fired when a complete lesson is loaded.
 * 
 * @param {event} event an event.
 * @param {boolean} editable a boolean to edit note content.
 */

function completeHandlerTools(event,editable=false) {
    _("notebookcompleteformatted").innerHTML = formateresponseTools(event.target.responseText,editable);
    displayLanguage();
}

/**
 * @summary The function that is executed for uploading a new presentation.
 * 
 * @param {boolean} editable a boolean to edit note content.
 */

function SlideNotesTools(editable=false) {
    var file = _("fileToUpload12").files[0];
    var scriptfile = "";
    if (file.name.endsWith('html')) scriptfile = "EditAnim/ExtractSlidesNotesAppHTMLTools.php";
    if (scriptfile == "") {
        alert('Only html files are allowed !');
        return -1;
    }
    var formdata = new FormData();
    formdata.append("fileToUpload", file);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", scriptfile);
    xhr.addEventListener("load", function (e) {completeHandlerTools(e,editable);}, false);
    xhr.timeout = 300000;
    xhr.ontimeout = (e) => {
        console.log("Erreur Timeout !");
        alert("Your presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.")
    };
    xhr.send(formdata);
}

/**
 * @summary The function that is executed for downloading a file.
 * 
 * @param {string} filename a text value contained the file name.
 * @param {string} elText a text value contained the text to download.
 * @param {string} mimeType a text value contained the MIME type.
 */

function DownloadFile(filename, elText, mimeType) {
    var link = document.createElement('a');
    mimeType = mimeType || 'text/plain';
    link.setAttribute('download', filename);
    link.setAttribute('href', 'data:' + mimeType  +  ';charset=utf-8,' + encodeURIComponent(elText));
    link.click();
}

/**
 * @summary The function that is executed for donwloading the new speech.
 * 
 */

function DownloadNewSpeech() {
    document.getElementById("dialog-download").innerHTML = "Please note that the speech will only be downloaded in the input language.";
    $( "#dialog-download" ).dialog({
        height: 152,
        width: 507,
        modal: true,
        appendTo: "#DesiredDivID",
        buttons: {
            OK: function() {
                var data_new_pres = _("notebookcompleteformatted").innerHTML;
                var parser = new DOMParser();
                var html = parser.parseFromString(data_new_pres, 'text/html');

                //Récupérer le nombre de slides
                var k = 1;
                var nb_slides = 0;
                while (html.getElementById('img_'+k)){
                    nb_slides = k;
                    k += 1;
                }

                //Récupérer la langue d'origine
                var lang_origin = document.getElementById("myLanguage").options.item(0).value;

                //Récupérer les notes dans la langue d'origine
                var txt = "";
                for (var num_slide = 1; num_slide <= nb_slides; num_slide++) {
                    txt += "------------- Slide "+num_slide+" -------------\n";
                    txt += html.getElementById('SlideText_'+lang_origin+'_img_'+num_slide).textContent + "\n";
                }
                txt = txt.replaceAll('"', '“');
                txt = txt.replaceAll('\v', '');

                var name_file = "speech_"+lang_origin+".txt";
                DownloadFile(name_file, txt);
                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });
}

/**
 * @summary The function that is executed for creating the new presentation.
 * 
 */

function CreateNewPresentation() {    
    document.getElementById("dialog-download").innerHTML = "Please note that if you have the same language twice (if your first language in the animation editor starts with [INPUT LANGUAGE]), changes made only on [INPUT LANGUAGE] will not be taken into account in the new animation.";
    $( "#dialog-download" ).dialog({
        height: 174,
        width: 791,
        modal: true,
        appendTo: "#DesiredDivID",
        buttons: {
            OK: function() {
                //Récupérer le code html de l'encart
                var data_new_pres = _("notebookcompleteformatted").innerHTML;
                var xhr = new XMLHttpRequest();
                var parser = new DOMParser();
                var html = parser.parseFromString(data_new_pres, 'text/html');

                //Récupérer le fichier original
                var file = _("fileToUpload12").files[0];
                var formdata = new FormData();
                formdata.append("fileToUpload", file);

                //Récupérer les options
                var aspect_ratio = _("aspectratioEditAnim").value;
                var keep_ratio = _("keepaspectratioEditAnim").value;
                var extra_delay = _("extradelayEditAnim").value;
                var test_licence = _("licenceEditAnim").value;
                
                //Récupérer les langues
                var tab_lang = getListLanguages();

                //Récupérer le nombre de slides
                var k = 1;
                var nb_slides = 0;
                while (html.getElementById('img_'+k)){
                    nb_slides = k;
                    k += 1;
                }

                //Récupérer les notes
                var tab_text = [];
                var i = 0;
                for (let key in tab_lang) {
                    code_lang = tab_lang[key].trim()
                    tab_text[i] = '"'+code_lang+'": [';
                    for (var num_slide = 1; num_slide <= nb_slides; num_slide++) {
                        j = 0;
                        if (num_slide==1){
                            tab_text[i] += '{"Slide": '+num_slide+', "Notes": ["';
                        }else{
                            tab_text[i] += ', {"Slide": '+num_slide+', "Notes": ["';
                        }
                        var text = "";
                        text = html.getElementById('SlideText_'+code_lang+'_img_'+num_slide).textContent.trim();
                        text = text.replaceAll('"', '“')
                        text = text.replaceAll('\r\n', '", "');
                        text = text.replaceAll('\n', '", "');
                        text = text.replaceAll('\r', '", "');
                        text = text.replaceAll('\v', '');
                        tab_text[i] += text+'"';

                        if (extra_delay != 0){
                            tab_text[i] += ',""'.repeat(extra_delay);
                        }
                        tab_text[i] += ']}';
                    }
                    tab_text[i] += ']';
                    console.log(tab_text[i]);
                    i += 1;
                }
                formdata.append("newText", tab_text);
                formdata.append("newAspectRatio", aspect_ratio);
                formdata.append("newLicence", test_licence);

                xhr.open("POST", "EditAnim/EditSlidesNotesAppHTMLTools.php");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == XMLHttpRequest.DONE) {
                        console.log(this.responseText);
                    }
                };
                xhr.addEventListener("load", DownloadNewPresentation, false);
                xhr.send(formdata);
                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });
}

/**
 * @summary The function that is executed for downloading the new presentation.
 * 
 * @param {event} event an event.
 */

function DownloadNewPresentation(event){
    var data_raw = event.target.responseText;
    var json_data = data_raw.substring(data_raw.indexOf("\"<!doctype html>"));
    var data = JSON.parse(json_data);
    DownloadFile("presentation_modified.html", data);
    console.log(json_data);
}
